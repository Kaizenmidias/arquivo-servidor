<?php

namespace App\Actions\Properties;

use App\Jobs\ProcessPropertyImageJob;
use App\Models\Property;
use App\Models\PropertyImageUpload;
use App\Models\PropertyPhoto;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class AttachPropertyImageUploadsAction
{
    public function execute(
        Property $property,
        User $user,
        ?string $featuredUploadToken,
        array $galleryUploadTokens,
    ): void {
        $startedAt = microtime(true);
        $userId = $user->id;
        $rawGalleryCount = count($galleryUploadTokens);
        $normalizedGalleryTokens = collect($galleryUploadTokens)
            ->filter(fn (mixed $token) => is_string($token) && trim($token) !== '')
            ->map(fn (string $token) => trim($token))
            ->values();
        $uniqueGalleryTokens = $normalizedGalleryTokens->unique()->values();

        $tokensToResolve = $uniqueGalleryTokens
            ->when(
                !empty($featuredUploadToken),
                fn (Collection $collection) => $collection->prepend(trim((string) $featuredUploadToken))
            )
            ->filter()
            ->unique()
            ->values();

        $resolvedUploads = $this->resolveUploads($tokensToResolve, $userId);
        $featuredUpload = $featuredUploadToken
            ? $resolvedUploads->get(trim((string) $featuredUploadToken))
            : null;
        $galleryUploads = $uniqueGalleryTokens
            ->map(fn (string $token) => $resolvedUploads->get($token))
            ->filter()
            ->unique('id')
            ->values();

        $invalidTokens = $tokensToResolve
            ->reject(fn (string $token) => $resolvedUploads->has($token))
            ->values();

        $this->safeInfo('Anexando uploads ao imovel.', [
            'property_id' => $property->id,
            'user_id' => $userId,
            'featured_upload_present' => (bool) $featuredUpload,
            'gallery_tokens_received' => $rawGalleryCount,
            'gallery_tokens_unique' => $uniqueGalleryTokens->count(),
            'gallery_uploads_resolved' => $galleryUploads->count(),
            'featured_token_received' => !empty($featuredUploadToken),
            'tokens_total_to_resolve' => $tokensToResolve->count(),
            'tokens_invalid' => $invalidTokens->all(),
        ]);

        $createdPhotos = DB::transaction(function () use ($property, $featuredUpload, $galleryUploads): int {
            $createdPhotos = 0;

            if ($featuredUpload) {
                $stagedPreview = $this->stagedPreview($featuredUpload);
                $featuredPhoto = PropertyPhoto::create([
                    'property_id' => $property->id,
                    'arquivo' => $stagedPreview['path'],
                    'url' => $stagedPreview['url'],
                    'original_path' => $featuredUpload->temp_path,
                    'principal' => true,
                    'ordem' => 0,
                    'size' => $featuredUpload->size,
                    'source_size' => $featuredUpload->size,
                    'source_mime_type' => $featuredUpload->mime_type,
                    'mime_type' => $featuredUpload->mime_type,
                    'optimized' => false,
                    'processing_status' => 'uploaded',
                ]);

                $this->dispatchProcessJob($featuredPhoto, $featuredUpload);
                $createdPhotos++;
            }

            $currentMaxOrder = (int) ($property->photos()->where('principal', false)->max('ordem') ?? 0);
            foreach ($galleryUploads as $index => $upload) {
                $stagedPreview = $this->stagedPreview($upload);
                $photo = PropertyPhoto::create([
                    'property_id' => $property->id,
                    'arquivo' => $stagedPreview['path'],
                    'url' => $stagedPreview['url'],
                    'original_path' => $upload->temp_path,
                    'principal' => false,
                    'ordem' => $currentMaxOrder + $index + 1,
                    'size' => $upload->size,
                    'source_size' => $upload->size,
                    'source_mime_type' => $upload->mime_type,
                    'mime_type' => $upload->mime_type,
                    'optimized' => false,
                    'processing_status' => 'uploaded',
                ]);

                $this->dispatchProcessJob($photo, $upload);
                $createdPhotos++;
            }

            return $createdPhotos;
        });

        $this->safeInfo('Uploads anexados ao imovel com sucesso.', [
            'property_id' => $property->id,
            'user_id' => auth()->id() ?? $userId,
            'property_photos_created' => $createdPhotos,
            'gallery_tokens_received' => $rawGalleryCount,
            'gallery_uploads_resolved' => $galleryUploads->count(),
            'tokens_invalid_count' => $invalidTokens->count(),
            'tokens_invalid' => $invalidTokens->all(),
            'processing_time_ms' => (int) round((microtime(true) - $startedAt) * 1000),
        ]);
    }

    private function resolveUploads(Collection $tokens, int $userId): Collection
    {
        if ($tokens->isEmpty()) {
            return collect();
        }

        return PropertyImageUpload::query()
            ->whereIn('token', $tokens->all())
            ->where('user_id', $userId)
            ->whereIn('status', ['uploaded', 'failed'])
            ->get()
            ->keyBy('token');
    }

    private function dispatchProcessJob(PropertyPhoto $photo, PropertyImageUpload $upload): void
    {
        $upload->update([
            'property_id' => $photo->property_id,
            'property_photo_id' => $photo->id,
            'status' => 'uploaded',
            'attached_at' => now(),
            'validation_error' => null,
        ]);

        if (env('TRAE_DEBUG_PROPERTY_IMAGE_REBUILD')) {
            // #region debug-point B:attach-dispatch-job
            rescue(function () use ($photo, $upload): void {
                Http::timeout(1)->post('http://127.0.0.1:7777/event', [
                    'sessionId' => 'property-image-rebuild',
                    'runId' => 'pre-fix',
                    'hypothesisId' => 'B',
                    'location' => 'app/Actions/Properties/AttachPropertyImageUploadsAction.php:dispatchProcessJob',
                    'msg' => '[DEBUG] Dispatching process image job',
                    'data' => [
                        'property_id' => $photo->property_id,
                        'photo_id' => $photo->id,
                        'upload_id' => $upload->id,
                        'upload_status' => $upload->status,
                        'photo_status' => $photo->processing_status,
                    ],
                    'ts' => (int) round(microtime(true) * 1000),
                ]);
            }, report: false);
            // #endregion
        }

        if (!in_array($photo->processing_status, ['processing', 'ready'], true)) {
            ProcessPropertyImageJob::dispatch($photo->id, $upload->id);
        }
    }

    private function stagedPreview(PropertyImageUpload $upload): array
    {
        $path = (string) $upload->temp_path;
        $url = '';

        try {
            if ($this->isBrowserRenderableMime($upload->mime_type)) {
                $url = Storage::disk($upload->disk)->url($path);
            }
        } catch (Throwable) {
            $url = '';
        }

        return [
            'path' => $path,
            'url' => $url,
        ];
    }

    private function isBrowserRenderableMime(?string $mimeType): bool
    {
        return in_array((string) $mimeType, [
            'image/jpeg',
            'image/png',
            'image/webp',
        ], true);
    }

    private function safeInfo(string $message, array $context = []): void
    {
        try {
            Log::info($message, $context);
        } catch (Throwable) {
            // Nao interrompe o fluxo principal se houver erro apenas no logger.
        }
    }
}
