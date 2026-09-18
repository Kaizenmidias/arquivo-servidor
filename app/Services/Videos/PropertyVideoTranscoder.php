<?php

namespace App\Services\Videos;

use App\Models\PropertyVideo;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\Process\Process;

class PropertyVideoTranscoder
{
    public function convert(PropertyVideo $video): array
    {
        $source = (string) $video->original_path;
        if (!str_starts_with($source, 'properties/videos/original/') || !Storage::disk('local')->exists($source)) {
            throw new RuntimeException('O vídeo original não foi encontrado.');
        }

        $path = "properties/videos/webm/{$video->property_id}/{$video->id}.webm";
        $temporaryPath = "properties/videos/webm/{$video->property_id}/{$video->id}.pending.webm";
        $disk = Storage::disk('public');
        $disk->makeDirectory(dirname($path));
        $disk->delete($temporaryPath);

        $output = $disk->path($temporaryPath);
        $maxWidth = max(320, (int) config('video_uploads.max_width', 1280));
        $filter = "scale=w='min(iw,{$maxWidth})':h=-2";
        $command = [
            (string) config('video_uploads.ffmpeg', 'ffmpeg'), '-hide_banner', '-loglevel', 'error',
            '-y', '-i', Storage::disk('local')->path($source),
            '-map', '0:v:0', '-map', '0:a:0?', '-vf', $filter,
            '-c:v', 'libvpx-vp9', '-b:v', '0', '-crf', (string) config('video_uploads.crf', 36),
            '-deadline', 'good', '-cpu-used', '4', '-row-mt', '1',
            '-c:a', 'libopus', '-b:a', '96k', '-ac', '2',
            '-f', 'webm', $output,
        ];

        try {
            $process = new Process($command);
            $process->setTimeout(1600);
            $process->mustRun();

            if (!$disk->exists($temporaryPath) || $disk->size($temporaryPath) === 0) {
                throw new RuntimeException('A conversão não gerou um vídeo válido.');
            }

            $probe = new Process([
                (string) config('video_uploads.ffprobe', 'ffprobe'), '-v', 'error',
                '-select_streams', 'v:0', '-show_entries', 'stream=codec_name',
                '-of', 'default=noprint_wrappers=1:nokey=1', $output,
            ]);
            $probe->setTimeout(30);
            $probe->mustRun();
            if (trim($probe->getOutput()) !== 'vp9') {
                throw new RuntimeException('O vídeo convertido não passou na validação.');
            }

            if (!$disk->move($temporaryPath, $path) || !$disk->exists($path)) {
                throw new RuntimeException('Não foi possível publicar o vídeo convertido.');
            }

            return ['path' => $path, 'size' => (int) $disk->size($path)];
        } finally {
            $disk->delete($temporaryPath);
        }
    }
}
