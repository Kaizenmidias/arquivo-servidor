<?php

namespace App\Actions\Properties;

use App\Models\PropertyImageUpload;
use App\Models\User;
use App\Services\Images\PropertyImageSecurityService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StagePropertyImageUploadAction
{
    public function __construct(
        private readonly PropertyImageSecurityService $securityService,
    ) {
    }

    public function execute(User $user, UploadedFile $file): PropertyImageUpload
    {
        $data = $this->securityService->inspectUploadedFile($file);
        $diskName = (string) config('image_uploads.staging_disk', config('image_uploads.final_disk', 'public'));
        $directory = trim((string) config('image_uploads.staging_directory', 'property-uploads/originals'), '/');
        $token = (string) Str::uuid();
        $path = $file->storeAs(
            sprintf('%s/%d/%s/%s', $directory, $user->id, now()->format('Y/m/d'), $token),
            $token . '.' . $data->extension,
            $diskName
        );

        $upload = PropertyImageUpload::create([
            'user_id' => $user->id,
            'token' => $token,
            'disk' => $diskName,
            'temp_path' => $path,
            'original_name' => $data->originalName,
            'sanitized_name' => $data->sanitizedName,
            'extension' => $data->extension,
            'mime_type' => $data->mimeType,
            'size' => $data->size,
            'sha256' => $data->sha256,
            'status' => 'stored',
            'expires_at' => null,
        ]);

        $pendingUploadsCount = PropertyImageUpload::query()
            ->where('user_id', $user->id)
            ->whereIn('status', ['stored', 'attached', 'processing', 'optimizing'])
            ->count();

        Log::info('Upload definitivo de imagem salvo.', [
            'upload_id' => $upload->id,
            'user_id' => $user->id,
            'mime_type' => $upload->mime_type,
            'size' => $upload->size,
            'storage_disk' => $upload->disk,
            'storage_path' => $upload->temp_path,
            'active_uploads_for_user' => $pendingUploadsCount,
        ]);

        return $upload;
    }

    public function destroy(PropertyImageUpload $upload): void
    {
        Storage::disk($upload->disk)->delete($upload->temp_path);
        $upload->delete();
    }
}
