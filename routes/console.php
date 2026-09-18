<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\PropertyPhoto;
use App\Services\Images\PropertyOriginalCleanup;

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
        ->with('uploads')
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
