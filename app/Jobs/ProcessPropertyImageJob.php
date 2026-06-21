<?php

namespace App\Jobs;

use App\Models\PropertyImageUpload;
use App\Models\PropertyPhoto;
use App\Services\Images\PropertyImageProcessor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProcessPropertyImageJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;
    public int $timeout = 300;

    public function __construct(
        public readonly int $photoId,
        public readonly int $uploadId,
    ) {
    }

    public function handle(PropertyImageProcessor $processor): void
    {
        /** @var PropertyPhoto $photo */
        $photo = PropertyPhoto::findOrFail($this->photoId);
        /** @var PropertyImageUpload $upload */
        $upload = PropertyImageUpload::findOrFail($this->uploadId);

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

            $photo->update(['processing_status' => 'optimizing']);
            $upload->update(['status' => 'optimizing']);

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
                'processing_status' => 'completed',
                'processing_error' => null,
            ]);

            $upload->update([
                'status' => 'completed',
                'processed_at' => now(),
                'expires_at' => null,
                'validation_error' => null,
            ]);

            gc_collect_cycles();

            Log::info('Processamento de imagem concluido.', [
                'photo_id' => $photo->id,
                'upload_id' => $upload->id,
                'property_id' => $photo->property_id,
            ]);
        } catch (Throwable $e) {
            $photo->update([
                'original_path' => $upload->temp_path,
                'source_size' => $upload->size,
                'source_mime_type' => $upload->mime_type,
                'processing_status' => 'failed',
                'processing_error' => $e->getMessage(),
            ]);
            $upload->update([
                'status' => 'failed',
                'expires_at' => null,
                'validation_error' => $e->getMessage(),
            ]);

            Log::error('Falha no processamento de imagem.', [
                'photo_id' => $photo->id,
                'upload_id' => $upload->id,
                'property_id' => $photo->property_id,
                'message' => $e->getMessage(),
            ]);

            gc_collect_cycles();

            throw $e;
        }
    }
}
