<?php

namespace App\Services\Images;

use App\Models\PropertyImageUpload;
use App\Models\PropertyPhoto;
use Illuminate\Support\Facades\Storage;

class PropertyOriginalCleanup
{
    /**
     * Remove originals only after every published WEBP version is available.
     * A dry run returns the space that can be reclaimed without changing files or records.
     *
     * @return array{status: string, bytes: int, reason: string}
     */
    public function clean(PropertyPhoto $photo, bool $dryRun = false): array
    {
        if ($photo->processing_status !== 'ready' || !$photo->optimized) {
            return $this->skipped('A imagem ainda nao foi otimizada.');
        }

        $finalDiskName = (string) config('image_uploads.final_disk', 'public');
        $finalDisk = Storage::disk($finalDiskName);
        $derivedPaths = array_map(
            fn ($path) => trim((string) $path, '/'),
            [$photo->arquivo, $photo->thumb_medium_path, $photo->thumb_small_path]
        );

        if (in_array('', $derivedPaths, true)) {
            return $this->skipped('Falta uma das versoes WEBP.');
        }

        foreach ($derivedPaths as $path) {
            if (!$finalDisk->exists($path)) {
                return $this->skipped('Uma das versoes WEBP nao existe no disco.');
            }
        }

        $uploads = $photo->relationLoaded('uploads') ? $photo->uploads : $photo->uploads()->get();
        if ($uploads->contains(fn (PropertyImageUpload $upload) => in_array($upload->status, ['uploaded', 'processing'], true))) {
            return $this->skipped('Ainda existe um upload em processamento.');
        }

        $originalDiskName = (string) config('image_uploads.original_disk', $finalDiskName);
        $candidates = [];
        $addCandidate = function (string $diskName, ?string $path) use (&$candidates, $finalDiskName, $derivedPaths): bool {
            $normalizedPath = trim((string) $path, '/');
            if ($normalizedPath === '') {
                return true;
            }

            if ($diskName === $finalDiskName && in_array($normalizedPath, $derivedPaths, true)) {
                return true;
            }

            if (!$this->isOriginalPath($normalizedPath)) {
                return false;
            }

            $candidates[$diskName . '|' . $normalizedPath] = [$diskName, $normalizedPath];
            return true;
        };

        $photoOriginalDisk = $uploads->firstWhere('temp_path', $photo->original_path)?->disk ?: $originalDiskName;
        if (!$addCandidate($photoOriginalDisk, $photo->original_path)) {
            return $this->skipped('O caminho do original nao pertence ao diretorio de originais.');
        }

        foreach ($uploads as $upload) {
            if (!$addCandidate((string) $upload->disk, $upload->temp_path)) {
                return $this->skipped('Um upload vinculado aponta para outro diretorio.');
            }
        }

        if ($candidates === [] && $photo->original_path === null && $uploads->isEmpty()) {
            return $this->skipped('Nao ha original vinculado.');
        }

        $bytes = 0;
        foreach ($candidates as [$diskName, $path]) {
            if (!in_array($diskName, [$originalDiskName, $finalDiskName], true)
                || $this->isReferencedElsewhere($photo, $diskName, $path)) {
                return $this->skipped('O original tambem esta referenciado por outro registro.');
            }

            $disk = Storage::disk($diskName);
            if ($disk->exists($path)) {
                $bytes += (int) $disk->size($path);
            }
        }

        if ($dryRun) {
            return ['status' => 'would_clean', 'bytes' => $bytes, 'reason' => ''];
        }

        foreach ($candidates as [$diskName, $path]) {
            $disk = Storage::disk($diskName);
            if ($disk->exists($path) && (!$disk->delete($path) || $disk->exists($path))) {
                return $this->skipped('Nao foi possivel apagar o arquivo original.');
            }
        }

        $photo->update(['original_path' => null]);
        $photo->uploads()->delete();

        return ['status' => 'cleaned', 'bytes' => $bytes, 'reason' => ''];
    }

    private function isOriginalPath(string $path): bool
    {
        if (str_contains($path, '..') || str_contains($path, '\\')) {
            return false;
        }

        $directories = array_unique([
            trim((string) config('image_uploads.original_directory', 'properties/original'), '/'),
            'property-uploads/originals',
        ]);

        foreach ($directories as $directory) {
            if ($directory !== '' && str_starts_with($path, $directory . '/')) {
                return true;
            }
        }

        return false;
    }

    private function isReferencedElsewhere(PropertyPhoto $photo, string $diskName, string $path): bool
    {
        if (PropertyPhoto::query()
            ->whereKeyNot($photo->id)
            ->where(function ($query) use ($path): void {
                $query->where('original_path', $path)
                    ->orWhere('arquivo', $path)
                    ->orWhere('thumb_medium_path', $path)
                    ->orWhere('thumb_small_path', $path);
            })
            ->exists()) {
            return true;
        }

        return PropertyImageUpload::query()
            ->where('disk', $diskName)
            ->where('temp_path', $path)
            ->where(function ($query) use ($photo): void {
                $query->whereNull('property_photo_id')
                    ->orWhere('property_photo_id', '!=', $photo->id);
            })
            ->exists();
    }

    private function skipped(string $reason): array
    {
        return ['status' => 'skipped', 'bytes' => 0, 'reason' => $reason];
    }
}
