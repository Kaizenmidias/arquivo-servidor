<?php

namespace App\Services\Images;

use App\DataTransferObjects\PropertyImageUploadData;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class PropertyImageSecurityService
{
    public function inspectUploadedFile(UploadedFile $file): PropertyImageUploadData
    {
        $originalName = $file->getClientOriginalName();
        $extension = strtolower((string) $file->getClientOriginalExtension());
        $mimeType = (string) $file->getMimeType();
        $size = (int) $file->getSize();
        $hash = hash_file('sha256', $file->getRealPath()) ?: Str::uuid()->toString();

        return new PropertyImageUploadData(
            file: $file,
            originalName: $originalName,
            sanitizedName: $this->sanitizeFileName(pathinfo($originalName, PATHINFO_FILENAME), $extension),
            extension: $extension,
            mimeType: $mimeType,
            size: $size,
            sha256: $hash,
        );
    }

    public function sanitizeFileName(string $name, string $extension): string
    {
        $slug = Str::of($name)->ascii()->lower()->replaceMatches('/[^a-z0-9]+/', '-')->trim('-')->value();
        if ($slug === '') {
            $slug = 'image';
        }

        return $slug . '.' . strtolower($extension);
    }

    public function safeWebpFileName(int $propertyId, string $uuid): string
    {
        return sprintf('property-%d-%s.webp', $propertyId, $uuid);
    }

    public function assertStoredUploadIsSafe(string $disk, string $path, string $extension): array
    {
        $storage = Storage::disk($disk);
        $absolutePath = $storage->path($path);
        if (!is_file($absolutePath)) {
            throw new RuntimeException('Arquivo original nao encontrado no armazenamento.');
        }

        $size = filesize($absolutePath) ?: 0;
        $maxBytes = (int) config('image_uploads.max_file_size_bytes', 10 * 1024 * 1024);
        if ($size > $maxBytes) {
            throw new RuntimeException('Arquivo acima do limite permitido.');
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = (string) ($finfo ? finfo_file($finfo, $absolutePath) : '');
        if ($finfo) {
            finfo_close($finfo);
        }

        $allowedMimeTypes = config('image_uploads.allowed_mime_types', []);
        $mimeIsAllowed = in_array($mimeType, $allowedMimeTypes, true)
            || $this->isCompatibleImageMime($mimeType, $extension);

        if (!$mimeIsAllowed) {
            throw new RuntimeException('Mime type real invalido para upload de imagem.');
        }

        $header = file_get_contents($absolutePath, false, null, 0, 512);
        if ($header === false || $header === '') {
            throw new RuntimeException('Nao foi possivel validar o arquivo armazenado.');
        }

        $snippet = strtolower($header);
        foreach (['<?php', '<script', '<svg', '<html', '<!doctype html'] as $needle) {
            if (str_contains($snippet, $needle)) {
                throw new RuntimeException('Conteudo potencialmente malicioso detectado no upload.');
            }
        }

        if (!$this->matchesKnownSignature($header, strtolower($extension))) {
            throw new RuntimeException('Assinatura binaria invalida para o upload.');
        }

        return [
            'size' => $size,
            'mime_type' => $mimeType,
            'sha256' => hash_file('sha256', $absolutePath) ?: Str::uuid()->toString(),
        ];
    }

    private function isCompatibleImageMime(string $mimeType, string $extension): bool
    {
        $normalizedMime = strtolower(trim($mimeType));
        $normalizedExtension = strtolower(trim($extension));

        if ($normalizedMime === 'application/octet-stream') {
            return in_array($normalizedExtension, ['avif', 'heic', 'heif'], true);
        }

        return str_starts_with($normalizedMime, 'image/');
    }

    private function matchesKnownSignature(string $binary, string $extension): bool
    {
        $header = substr($binary, 0, 64);

        return match ($extension) {
            'jpg', 'jpeg' => str_starts_with($header, "\xFF\xD8\xFF"),
            'png' => str_starts_with($header, "\x89PNG\x0D\x0A\x1A\x0A"),
            'webp' => str_starts_with($header, 'RIFF') && substr($header, 8, 4) === 'WEBP',
            'avif' => str_contains($header, 'ftypavif')
                || str_contains($header, 'ftypavis')
                || str_contains($header, 'ftypmif1')
                || str_contains($header, 'ftypmsf1'),
            'heic', 'heif' => str_contains($header, 'ftypheic')
                || str_contains($header, 'ftypheix')
                || str_contains($header, 'ftyphevc')
                || str_contains($header, 'ftyphevx')
                || str_contains($header, 'ftypmif1')
                || str_contains($header, 'ftypmsf1'),
            default => false,
        };
    }
}
