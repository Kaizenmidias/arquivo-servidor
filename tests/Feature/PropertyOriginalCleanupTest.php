<?php

namespace Tests\Feature;

use App\Models\PropertyImageUpload;
use App\Models\PropertyPhoto;
use App\Models\User;
use App\Services\Images\PropertyOriginalCleanup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class PropertyOriginalCleanupTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_removes_only_the_original_after_all_webp_versions_are_ready(): void
    {
        [$photo, $upload] = $this->readyPhoto();
        $cleanup = app(PropertyOriginalCleanup::class);
        $originalPath = $photo->original_path;

        $preview = $cleanup->clean($photo, true);
        $this->assertSame('would_clean', $preview['status']);
        $this->assertSame(14, $preview['bytes']);
        Storage::disk('public')->assertExists($photo->original_path);
        $this->assertDatabaseHas('property_image_uploads', ['id' => $upload->id]);

        $result = $cleanup->clean($photo);
        $this->assertSame('cleaned', $result['status']);
        Storage::disk('public')->assertMissing($originalPath);
        Storage::disk('public')->assertExists([$photo->arquivo, $photo->thumb_medium_path, $photo->thumb_small_path]);
        $this->assertNull($photo->fresh()->original_path);
        $this->assertDatabaseMissing('property_image_uploads', ['id' => $upload->id]);
    }

    public function test_it_keeps_the_original_when_a_published_version_is_missing(): void
    {
        [$photo, $upload] = $this->readyPhoto();
        Storage::disk('public')->delete($photo->thumb_small_path);

        $result = app(PropertyOriginalCleanup::class)->clean($photo);

        $this->assertSame('skipped', $result['status']);
        Storage::disk('public')->assertExists($photo->original_path);
        $this->assertDatabaseHas('property_image_uploads', ['id' => $upload->id]);
    }

    public function test_it_keeps_an_original_referenced_by_another_photo(): void
    {
        [$photo] = $this->readyPhoto();
        PropertyPhoto::create([
            'property_id' => $photo->property_id,
            'arquivo' => $photo->original_path,
            'url' => '/storage/' . $photo->original_path,
            'original_path' => $photo->original_path,
            'principal' => false,
            'ordem' => 2,
            'processing_status' => 'uploaded',
        ]);

        $result = app(PropertyOriginalCleanup::class)->clean($photo);

        $this->assertSame('skipped', $result['status']);
        Storage::disk('public')->assertExists($photo->original_path);
    }

    private function readyPhoto(): array
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $typeId = DB::table('property_types')->insertGetId([
            'nome_tipo' => 'Casa',
            'slug' => 'casa',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $propertyId = DB::table('properties')->insertGetId([
            'codigo_anuncio' => 'TEST-001',
            'titulo' => 'Imovel de teste',
            'slug' => 'imovel-de-teste',
            'descricao' => 'Descricao',
            'tipo_propriedade_id' => $typeId,
            'operacao' => 'Venda',
            'valor' => 100,
            'moeda' => 'BRL',
            'endereco' => 'Rua Teste',
            'bairro' => 'Centro',
            'cidade' => 'Sao Paulo',
            'estado' => 'SP',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $photo = PropertyPhoto::create([
            'property_id' => $propertyId,
            'arquivo' => "properties/webp/{$propertyId}/1-hero.webp",
            'url' => "/storage/properties/webp/{$propertyId}/1-hero.webp",
            'thumb_medium_path' => "properties/webp/{$propertyId}/1-gallery.webp",
            'thumb_small_path' => "properties/thumb/{$propertyId}/1-thumb.webp",
            'original_path' => "properties/original/{$propertyId}/photo.jpg",
            'principal' => true,
            'ordem' => 0,
            'optimized' => true,
            'processing_status' => 'ready',
        ]);

        Storage::disk('public')->put($photo->original_path, 'original-image');
        foreach ([$photo->arquivo, $photo->thumb_medium_path, $photo->thumb_small_path] as $path) {
            Storage::disk('public')->put($path, 'webp');
        }

        $upload = PropertyImageUpload::create([
            'user_id' => $user->id,
            'property_id' => $propertyId,
            'property_photo_id' => $photo->id,
            'token' => (string) Str::uuid(),
            'disk' => 'public',
            'temp_path' => $photo->original_path,
            'original_name' => 'photo.jpg',
            'sanitized_name' => 'photo.jpg',
            'extension' => 'jpg',
            'mime_type' => 'image/jpeg',
            'size' => 14,
            'sha256' => hash('sha256', 'original-image'),
            'status' => 'ready',
        ]);

        return [$photo, $upload];
    }
}
