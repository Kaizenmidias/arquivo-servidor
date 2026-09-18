<?php

namespace App\Jobs;

use App\Models\PropertyVideo;
use App\Services\Videos\PropertyVideoTranscoder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProcessPropertyVideoJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;
    public int $timeout = 1800;

    public function __construct(public readonly int $videoId)
    {
    }

    public function handle(PropertyVideoTranscoder $transcoder): void
    {
        $video = PropertyVideo::find($this->videoId);
        if (!$video || !$video->property_id) {
            return;
        }
        if ($video->status === 'ready') {
            $this->cleanupOriginal($video);
            return;
        }

        $video->update(['status' => 'processing', 'processing_error' => null]);

        try {
            $result = $transcoder->convert($video);
            if (!PropertyVideo::query()->whereKey($video->id)->exists()) {
                Storage::disk('public')->delete($result['path']);
                return;
            }
            $video->update([
                'path' => $result['path'],
                'size' => $result['size'],
                'status' => 'ready',
                'processed_at' => now(),
            ]);

            $this->cleanupOriginal($video);
        } catch (Throwable $error) {
            $video->update(['status' => 'failed', 'processing_error' => $error->getMessage()]);
            Log::error('Falha ao otimizar vídeo de imóvel.', ['video_id' => $video->id, 'message' => $error->getMessage()]);
            throw $error;
        }
    }

    private function cleanupOriginal(PropertyVideo $video): void
    {
        if (!$video->original_path || !$video->path || !Storage::disk('public')->exists($video->path)
            || !str_starts_with($video->original_path, 'properties/videos/original/')) {
            return;
        }

        try {
            $original = $video->original_path;
            if ((!Storage::disk('local')->exists($original) || Storage::disk('local')->delete($original))
                && !Storage::disk('local')->exists($original)) {
                $video->update(['original_path' => null]);
            } else {
                Log::warning('Vídeo otimizado, mas o original não foi removido.', ['video_id' => $video->id]);
            }
        } catch (Throwable $error) {
            Log::warning('Falha ao limpar o original de um vídeo otimizado.', [
                'video_id' => $video->id,
                'message' => $error->getMessage(),
            ]);
        }
    }
}
