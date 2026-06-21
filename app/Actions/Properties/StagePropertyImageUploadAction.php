<?php

namespace App\Actions\Properties;

use App\Models\PropertyImageUpload;
use App\Models\User;
use App\Services\Images\PropertyImageSecurityService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
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
        $diskName = (string) config('image_uploads.original_disk', config('image_uploads.final_disk', 'public'));
        $directory = trim((string) config('image_uploads.original_directory', 'properties/original'), '/');
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
            'status' => 'uploaded',
            'expires_at' => null,
        ]);

        if (env('TRAE_DEBUG_PROPERTY_IMAGE_REBUILD')) {
            // #region debug-point A:stage-upload-created
            rescue(function () use ($user, $upload, $path): void {
                Http::timeout(1)->post('http://127.0.0.1:7777/event', [
                    'sessionId' => 'property-image-rebuild',
                    'runId' => 'pre-fix',
                    'hypothesisId' => 'A',
                    'location' => 'app/Actions/Properties/StagePropertyImageUploadAction.php:execute',
                    'msg' => '[DEBUG] Stored original upload',
                    'data' => [
                        'user_id' => $user->id,
                        'upload_id' => $upload->id,
                        'token' => $upload->token,
                        'status' => $upload->status,
                        'disk' => $upload->disk,
                        'path' => $path,
                        'mime_type' => $upload->mime_type,
                        'size' => $upload->size,
                    ],
                    'ts' => (int) round(microtime(true) * 1000),
                ]);
            }, report: false);
            // #endregion
        }

        $pendingUploadsCount = PropertyImageUpload::query()
            ->where('user_id', $user->id)
            ->whereIn('status', ['uploaded', 'processing'])
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
