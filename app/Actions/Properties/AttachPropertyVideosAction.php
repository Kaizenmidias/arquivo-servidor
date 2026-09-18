<?php

namespace App\Actions\Properties;

use App\Jobs\ProcessPropertyVideoJob;
use App\Models\Property;
use App\Models\PropertyVideo;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AttachPropertyVideosAction
{
    public function assertAvailable(User $user, array $tokens, ?Property $property = null, array $removeIds = []): void
    {
        $tokens = array_values(array_unique($tokens));
        $videos = PropertyVideo::query()
            ->where('user_id', $user->id)
            ->whereNull('property_id')
            ->where('status', 'uploaded')
            ->whereIn('token', $tokens)
            ->get();

        if ($videos->count() !== count($tokens) || $videos->contains(fn (PropertyVideo $video) =>
            !$video->original_path || !Storage::disk('local')->exists($video->original_path))) {
            throw ValidationException::withMessages([
                'video_upload_tokens' => 'Um ou mais vídeos não estão disponíveis. Reenvie os vídeos antes de salvar.',
            ]);
        }

        $existing = $property ? $property->videos()->whereNotIn('id', $removeIds)->count() : 0;
        if ($existing + count($tokens) > (int) config('video_uploads.max_per_property', 5)) {
            throw ValidationException::withMessages([
                'video_upload_tokens' => 'O imóvel excede o limite de vídeos permitido.',
            ]);
        }
    }

    public function execute(Property $property, User $user, array $tokens, array $removeIds = []): void
    {
        $this->assertAvailable($user, $tokens, $property, $removeIds);

        foreach ($property->videos()->whereIn('id', $removeIds)->get() as $video) {
            if ($video->original_path) {
                Storage::disk('local')->delete($video->original_path);
            }
            if ($video->path) {
                Storage::disk('public')->delete($video->path);
            }
            $video->delete();
        }

        foreach (array_values(array_unique($tokens)) as $token) {
            $video = PropertyVideo::query()->where('user_id', $user->id)->whereNull('property_id')->where('token', $token)->firstOrFail();
            $video->update(['property_id' => $property->id]);
            ProcessPropertyVideoJob::dispatch($video->id)->afterCommit();
        }
    }
}
