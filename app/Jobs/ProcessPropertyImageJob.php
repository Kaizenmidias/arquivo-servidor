<?php

namespace App\Jobs;

use App\Models\PropertyImageUpload;
use App\Models\PropertyPhoto;
use App\Services\Images\PropertyImageProcessor;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessPropertyImageJob implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    public int $tries = 3;
    public int $timeout = 120;
    public int $uniqueFor = 900;

    public function __construct(
        public readonly int $photoId,
        public readonly int $uploadId,
    ) {
    }

    public function uniqueId(): string
    {
        return 'property-photo-process:' . $this->photoId;
    }

    public function handle(PropertyImageProcessor $processor): void
    {
        /** @var PropertyPhoto $photo */
        $photo = PropertyPhoto::find($this->photoId);
        /** @var PropertyImageUpload $upload */
        $upload = PropertyImageUpload::find($this->uploadId);

        if (!$photo || !$upload) {
            Log::warning('Job de processamento descartado por registro ausente.', [
                'photo_id' => $this->photoId,
                'upload_id' => $this->uploadId,
            ]);

            return;
        }

        if (env('TRAE_DEBUG_PROPERTY_IMAGE_REBUILD')) {
            // #region debug-point D:job-handle-enter
            rescue(function () use ($photo, $upload): void {
                Http::timeout(1)->post('http://127.0.0.1:7777/event', [
                    'sessionId' => 'property-image-rebuild',
                    'runId' => 'pre-fix',
                    'hypothesisId' => 'D',
                    'location' => 'app/Jobs/ProcessPropertyImageJob.php:handle:enter',
                    'msg' => '[DEBUG] Process image job entered handle',
                    'data' => [
                        'job_photo_id' => $this->photoId,
                        'job_upload_id' => $this->uploadId,
                        'photo_status_before' => $photo->processing_status,
                        'upload_status_before' => $upload->status,
                        'attempt' => method_exists($this, 'attempts') ? $this->attempts() : null,
                    ],
                    'ts' => (int) round(microtime(true) * 1000),
                ]);
            }, report: false);
            // #endregion
        }

        if ($photo->processing_status === 'ready' && $upload->status === 'ready') {
            Log::info('Job de processamento ignorado porque a imagem ja esta pronta.', [
                'photo_id' => $photo->id,
                'upload_id' => $upload->id,
                'property_id' => $photo->property_id,
            ]);

            return;
        }

        $lock = Cache::lock($this->processingLockKey(), $this->timeout + 30);
        if (!$lock->get()) {
            Log::warning('Job de processamento duplicado ignorado por lock ativo.', [
                'photo_id' => $photo->id,
                'upload_id' => $upload->id,
                'property_id' => $photo->property_id,
            ]);

            return;
        }

        try {
            $upload->update(['status' => 'processing']);
            $photo->update([
                'original_path' => $upload->temp_path,
                'source_size' => $upload->size,
                'source_mime_type' => $upload->mime_type,
                'processing_status' => 'processing',
                'processing_error' => null,
            ]);

            Log::info('Processamento de imagem iniciado.', [
                'photo_id' => $photo->id,
                'upload_id' => $upload->id,
                'property_id' => $photo->property_id,
            ]);

            $result = $processor->process($photo, $upload);

            $photo->update([
                'arquivo' => $result['path'],
                'url' => $result['url'],
                'original_path' => $result['original_path'],
                'thumb_medium_path' => $result['medium_path'],
                'thumb_small_path' => $result['thumb_path'],
                'width' => $result['width'],
                'height' => $result['height'],
                'size' => $result['size'],
                'source_size' => $result['source_size'],
                'source_mime_type' => $result['source_mime_type'],
                'mime_type' => $result['mime_type'],
                'optimized' => true,
                'processed_at' => now(),
                'processing_status' => 'ready',
                'processing_error' => null,
            ]);

            $upload->update([
                'status' => 'ready',
                'processed_at' => now(),
                'expires_at' => null,
                'validation_error' => null,
            ]);

            if (env('TRAE_DEBUG_PROPERTY_IMAGE_REBUILD')) {
                // #region debug-point D:job-handle-success
                rescue(function () use ($photo, $upload): void {
                    Http::timeout(1)->post('http://127.0.0.1:7777/event', [
                        'sessionId' => 'property-image-rebuild',
                        'runId' => 'pre-fix',
                        'hypothesisId' => 'D',
                        'location' => 'app/Jobs/ProcessPropertyImageJob.php:handle:success',
                        'msg' => '[DEBUG] Process image job completed',
                        'data' => [
                            'photo_id' => $photo->id,
                            'upload_id' => $upload->id,
                            'photo_status_after' => $photo->fresh()?->processing_status,
                            'upload_status_after' => $upload->fresh()?->status,
                        ],
                        'ts' => (int) round(microtime(true) * 1000),
                    ]);
                }, report: false);
                // #endregion
            }

            gc_collect_cycles();

            Log::info('Processamento de imagem concluido.', [
                'photo_id' => $photo->id,
                'upload_id' => $upload->id,
                'property_id' => $photo->property_id,
            ]);
        } catch (Throwable $e) {
            if (env('TRAE_DEBUG_PROPERTY_IMAGE_REBUILD')) {
                // #region debug-point D:job-handle-failure
                rescue(function () use ($photo, $upload, $e): void {
                    Http::timeout(1)->post('http://127.0.0.1:7777/event', [
                        'sessionId' => 'property-image-rebuild',
                        'runId' => 'pre-fix',
                        'hypothesisId' => 'D',
                        'location' => 'app/Jobs/ProcessPropertyImageJob.php:handle:failure',
                        'msg' => '[DEBUG] Process image job failed',
                        'data' => [
                            'photo_id' => $photo->id,
                            'upload_id' => $upload->id,
                            'photo_status_after' => $photo->fresh()?->processing_status,
                            'upload_status_after' => $upload->fresh()?->status,
                            'error' => $e->getMessage(),
                        ],
                        'ts' => (int) round(microtime(true) * 1000),
                    ]);
                }, report: false);
                // #endregion
            }

            Log::error('Falha no processamento de imagem.', [
                'photo_id' => $photo->id,
                'upload_id' => $upload->id,
                'property_id' => $photo->property_id,
                'message' => $e->getMessage(),
                'attempt' => method_exists($this, 'attempts') ? $this->attempts() : null,
                'max_tries' => $this->tries,
            ]);

            gc_collect_cycles();

            throw $e;
        } finally {
            rescue(static fn () => $lock->release(), report: false);
        }
    }

    public function failed(Throwable $e): void
    {
        $photo = PropertyPhoto::find($this->photoId);
        $upload = PropertyImageUpload::find($this->uploadId);

        if ($photo) {
            $photo->update([
                'processing_status' => 'failed',
                'processing_error' => $e->getMessage(),
            ]);
        }

        if ($upload) {
            $upload->update([
                'status' => 'failed',
                'expires_at' => null,
                'validation_error' => $e->getMessage(),
            ]);
        }

        Log::error('Processamento de imagem finalizado com falha apos retries.', [
            'photo_id' => $this->photoId,
            'upload_id' => $this->uploadId,
            'message' => $e->getMessage(),
        ]);
    }

    private function processingLockKey(): string
    {
        return 'property-photo-processing-lock:' . $this->photoId;
    }
}
