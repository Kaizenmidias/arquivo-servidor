<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class SafeImageUpload implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$value instanceof UploadedFile || !$value->isValid()) {
            $fail('Arquivo de imagem invalido.');
            return;
        }

        $maxBytes = (int) config('image_uploads.max_file_size_bytes', 10 * 1024 * 1024);
        if ($value->getSize() > $maxBytes) {
            $maxSizeMb = max(1, (int) round($maxBytes / 1024 / 1024));
            $fail("Cada imagem deve ter no maximo {$maxSizeMb}MB.");
            return;
        }

        $allowedExtensions = config('image_uploads.allowed_extensions', []);
        $extension = strtolower((string) $value->getClientOriginalExtension());
        if (!in_array($extension, $allowedExtensions, true)) {
            $fail('Formato de imagem nao permitido.');
            return;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $realMime = (string) ($finfo ? finfo_file($finfo, $value->getRealPath()) : $value->getMimeType());
        if ($finfo) {
            finfo_close($finfo);
        }
        $allowedMimes = config('image_uploads.allowed_mime_types', []);
        $mimeIsAllowed = in_array($realMime, $allowedMimes, true)
            || ($realMime === 'application/octet-stream' && in_array($extension, ['heic', 'heif'], true));

        if (!$mimeIsAllowed) {
            $fail('Mime type de imagem invalido.');
            return;
        }

        $binary = @file_get_contents($value->getRealPath(), false, null, 0, 512);
        if ($binary === false || $binary === '') {
            $fail('Nao foi possivel ler o arquivo enviado.');
            return;
        }

        if ($this->containsMaliciousPayload($binary)) {
            $fail('O arquivo enviado possui conteudo potencialmente malicioso.');
            return;
        }

        if (!$this->matchesKnownSignature($binary, $extension)) {
            $fail('A assinatura binaria do arquivo nao corresponde a uma imagem valida.');
        }
    }

    private function containsMaliciousPayload(string $binary): bool
    {
        $snippet = strtolower($binary);

        foreach (['<?php', '<script', '<svg', '<html', '<!doctype html'] as $needle) {
            if (str_contains($snippet, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function matchesKnownSignature(string $binary, string $extension): bool
    {
        $header = substr($binary, 0, 64);

        return match ($extension) {
            'jpg', 'jpeg' => str_starts_with($header, "\xFF\xD8\xFF"),
            'png' => str_starts_with($header, "\x89PNG\x0D\x0A\x1A\x0A"),
            'webp' => str_starts_with($header, 'RIFF') && substr($header, 8, 4) === 'WEBP',
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
