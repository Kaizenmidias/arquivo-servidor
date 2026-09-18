<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\PropertyVideo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PropertyVideoController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:mp4,mov,webm', 'max:' . config('video_uploads.max_file_size_kb', 204800)],
        ]);

        $file = $request->file('file');
        $token = (string) Str::uuid();
        $extension = strtolower($file->extension() ?: 'mp4');
        $path = $file->storeAs('properties/videos/original/' . $request->user()->id, $token . '.' . $extension, 'local');
        if (!$path || !Storage::disk('local')->exists($path)) {
            throw ValidationException::withMessages(['file' => 'Não foi possível salvar o vídeo enviado.']);
        }

        $video = PropertyVideo::create([
            'user_id' => $request->user()->id,
            'token' => $token,
            'original_path' => $path,
            'original_name' => Str::limit(basename($file->getClientOriginalName()), 255, ''),
            'source_size' => Storage::disk('local')->size($path),
            'status' => 'uploaded',
        ]);

        return response()->json(['token' => $video->token, 'status' => $video->status], 201);
    }

    public function destroy(Request $request, string $token): JsonResponse
    {
        $video = PropertyVideo::query()
            ->where('token', $token)
            ->where('user_id', $request->user()->id)
            ->whereNull('property_id')
            ->firstOrFail();

        if ($video->original_path) {
            Storage::disk('local')->delete($video->original_path);
        }
        $video->delete();

        return response()->json(['deleted' => true]);
    }

    public function status(Property $property): JsonResponse
    {
        return response()->json(['videos' => $property->videos()->orderBy('id')->get()->map(fn (PropertyVideo $video) => [
            'id' => $video->id,
            'name' => $video->original_name,
            'status' => $video->status,
            'error' => $video->processing_error,
            'url' => $video->status === 'ready' && $video->path && Storage::disk('public')->exists($video->path) ? url('/media/' . $video->path) : null,
            'source_size' => $video->source_size,
            'size' => $video->size,
        ])]);
    }
}
