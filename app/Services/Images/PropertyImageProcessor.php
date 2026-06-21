<?php

namespace App\Services\Images;

use App\Models\PropertyImageUpload;
use App\Models\PropertyPhoto;
use Imagick;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class PropertyImageProcessor
{
    public function __construct(
        private readonly PropertyImageSecurityService $securityService,
    ) {
    }

    public function process(PropertyPhoto $photo, PropertyImageUpload $upload): array
    {
        $validated = $this->securityService->assertStoredUploadIsSafe(
            $upload->disk,
            $upload->temp_path,
            $upload->extension
        );

        if (!class_exists(Imagick::class)) {
            Log::error('Processamento de imagem indisponivel: extensao Imagick ausente.', [
                'photo_id' => $photo->id,
                'upload_id' => $upload->id,
            ]);

            throw new RuntimeException('O servidor precisa da extensao Imagick para processar as imagens.');
        }

        $disk = Storage::disk((string) config('image_uploads.final_disk', 'public'));
        $sourcePath = Storage::disk($upload->disk)->path($upload->temp_path);
        if (!is_file($sourcePath)) {
            throw new RuntimeException('O arquivo original da imagem nao foi encontrado no storage definitivo.');
        }

        $quality = (int) config('image_uploads.processing.webp_quality', 85);

        $hero = $this->buildWebpVersion($sourcePath, (int) config('image_uploads.processing.hero_max_width', 1920), $quality);
        $gallery = $this->buildWebpVersion($sourcePath, (int) config('image_uploads.processing.gallery_max_width', 1600), $quality);
        $thumb = $this->buildWebpVersion($sourcePath, (int) config('image_uploads.processing.thumb_max_width', 400), $quality);

        $fullPath = $this->webpOutputPath($photo, 'hero');
        $mediumPath = $this->webpOutputPath($photo, 'gallery');
        $thumbPath = $this->thumbOutputPath($photo);

        $this->saveBinary($disk, $fullPath, $hero['binary']);
        $this->saveBinary($disk, $mediumPath, $gallery['binary']);
        $this->saveBinary($disk, $thumbPath, $thumb['binary']);

        gc_collect_cycles();

        return [
            'original_path' => $upload->temp_path,
            'path' => $fullPath,
            'url' => url('/media/' . ltrim($fullPath, '/')),
            'medium_path' => $mediumPath,
            'thumb_path' => $thumbPath,
            'width' => $hero['width'],
            'height' => $hero['height'],
            'size' => $disk->size($fullPath),
            'mime_type' => 'image/webp',
            'source_size' => (int) ($validated['size'] ?? 0),
            'source_mime_type' => (string) ($validated['mime_type'] ?? $upload->mime_type),
        ];
    }

    public function deleteDerivedFiles(PropertyPhoto $photo): void
    {
        $disk = Storage::disk((string) config('image_uploads.final_disk', 'public'));
        $disk->delete(array_filter([
            $photo->arquivo,
            $photo->thumb_small_path,
            $photo->thumb_medium_path,
        ]));
    }

    private function buildWebpVersion(string $path, int $maxWidth, int $quality): array
    {
        $image = new Imagick();
        $image->readImage($path);

        if ($image->getNumberImages() > 1) {
            throw new RuntimeException('Animacoes nao sao permitidas no upload de imagens.');
        }

        $image->autoOrient();
        $image->stripImage();

        [$targetWidth, $targetHeight] = $this->targetDimensions(
            $image->getImageWidth(),
            $image->getImageHeight(),
            $maxWidth
        );

        if ($targetWidth !== $image->getImageWidth() || $targetHeight !== $image->getImageHeight()) {
            $image->resizeImage($targetWidth, $targetHeight, Imagick::FILTER_LANCZOS, 1, true);
        }

        $image->setImageFormat('webp');
        $image->setImageCompressionQuality($quality);

        $binary = (string) $image->getImagesBlob();
        $width = $image->getImageWidth();
        $height = $image->getImageHeight();

        $image->clear();
        $image->destroy();

        return [
            'binary' => $binary,
            'width' => $width,
            'height' => $height,
        ];
    }

    private function targetDimensions(int $width, int $height, int $maxWidth): array
    {
        if ($width <= 0 || $height <= 0 || $width <= $maxWidth) {
            return [$width, $height];
        }

        $ratio = $maxWidth / $width;

        return [
            max(1, (int) round($width * $ratio)),
            max(1, (int) round($height * $ratio)),
        ];
    }

    private function saveBinary($disk, string $path, string $binary): void
    {
        $absolutePath = $disk->path($path);
        $this->ensureDirectory($absolutePath);
        $disk->put($path, $binary);
    }

    private function webpOutputPath(PropertyPhoto $photo, string $version): string
    {
        return sprintf(
            '%s/%d/%d-%s.webp',
            trim((string) config('image_uploads.webp_directory', 'properties/webp'), '/'),
            $photo->property_id,
            $photo->id,
            $version
        );
    }

    private function thumbOutputPath(PropertyPhoto $photo): string
    {
        return sprintf(
            '%s/%d/%d-thumb.webp',
            trim((string) config('image_uploads.thumb_directory', 'properties/thumb'), '/'),
            $photo->property_id,
            $photo->id
        );
    }

    private function ensureDirectory(string $absolutePath): void
    {
        $directory = dirname($absolutePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }
    }
}
