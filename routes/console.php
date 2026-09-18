<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\PropertyPhoto;
use App\Models\PropertyImageUpload;
use App\Models\PropertyVideo;
use App\Services\Images\PropertyOriginalCleanup;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('properties:prune-original-images {--execute : Apaga os originais elegiveis}', function (PropertyOriginalCleanup $cleanup): int {
    $execute = (bool) $this->option('execute');
    $checked = 0;
    $eligible = 0;
    $bytes = 0;
    $skipped = 0;

    PropertyPhoto::query()
        ->where('processing_status', 'ready')
        ->where('optimized', true)
        ->chunkById(100, function ($photos) use ($cleanup, $execute, &$checked, &$eligible, &$bytes, &$skipped): void {
            foreach ($photos as $photo) {
                $checked++;
                $result = $cleanup->clean($photo, !$execute);
                if (in_array($result['status'], ['cleaned', 'would_clean'], true)) {
                    $eligible++;
                    $bytes += $result['bytes'];
                } else {
                    $skipped++;
                    if ($this->output->isVerbose()) {
                        $this->line("Foto {$photo->id}: {$result['reason']}");
                    }
                }
            }
        });

    $amount = number_format($bytes / 1024 / 1024, 2, ',', '.');
    $this->info("Fotos verificadas: {$checked}. Elegiveis: {$eligible}. Ignoradas: {$skipped}.");
    $this->info($execute ? "Espaco removido: {$amount} MB." : "Espaco que pode ser liberado: {$amount} MB. Execute com --execute para apagar.");

    return 0;
})->purpose('Remove originais de imagens de imoveis com versoes WEBP prontas');

Artisan::command('properties:prune-original-videos {--execute : Apaga os originais de videos otimizados}', function (): int {
    $execute = (bool) $this->option('execute');
    $count = 0;
    $bytes = 0;
    $sourceDisk = Storage::disk('local');
    $finalDisk = Storage::disk('public');

    PropertyVideo::query()->where('status', 'ready')->whereNotNull('original_path')->chunkById(100, function ($videos) use ($execute, $sourceDisk, $finalDisk, &$count, &$bytes): void {
        foreach ($videos as $video) {
            if (!$video->path || !$finalDisk->exists($video->path) || !str_starts_with($video->original_path, 'properties/videos/original/')) {
                continue;
            }
            $size = $sourceDisk->exists($video->original_path) ? (int) $sourceDisk->size($video->original_path) : 0;
            if ($execute && $sourceDisk->exists($video->original_path) && (!$sourceDisk->delete($video->original_path) || $sourceDisk->exists($video->original_path))) {
                $this->warn("Original do video {$video->id} nao pode ser removido.");
                continue;
            }
            if ($execute) $video->update(['original_path' => null]);
            $count++;
            $bytes += $size;
        }
    });

    $amount = number_format($bytes / 1024 / 1024, 2, ',', '.');
    $this->info($execute ? "Originais removidos: {$count}. Espaco liberado: {$amount} MB." : "Originais elegiveis: {$count}. Espaco recuperavel: {$amount} MB. Execute com --execute para apagar.");
    return 0;
})->purpose('Remove originais de videos cujas versoes WebM estao prontas');

Artisan::command('properties:prune-staged-media', function (): int {
    $threshold = now()->subDays(2);
    $images = 0;
    $videos = 0;

    PropertyImageUpload::query()->whereNull('property_photo_id')->whereIn('status', ['uploaded', 'failed'])
        ->where('created_at', '<', $threshold)->chunkById(100, function ($uploads) use (&$images): void {
            foreach ($uploads as $upload) {
                if (PropertyPhoto::query()->where('original_path', $upload->temp_path)->exists()) continue;
                Storage::disk($upload->disk)->delete($upload->temp_path);
                $upload->delete();
                $images++;
            }
        });

    PropertyVideo::query()->whereNull('property_id')->where('created_at', '<', $threshold)
        ->chunkById(100, function ($stagedVideos) use (&$videos): void {
            foreach ($stagedVideos as $video) {
                if ($video->original_path) Storage::disk('local')->delete($video->original_path);
                $video->delete();
                $videos++;
            }
        });

    $this->info("Uploads abandonados removidos: {$images} imagens, {$videos} videos.");
    return 0;
})->purpose('Apaga uploads de imoveis abandonados ha mais de dois dias');

Schedule::command('properties:prune-staged-media')->dailyAt('03:00');
