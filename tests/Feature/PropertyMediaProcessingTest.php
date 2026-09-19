<?php

namespace Tests\Feature;

use App\Actions\Properties\AttachPropertyImageUploadsAction;
use App\Http\Controllers\AdminController;
use App\Jobs\ProcessPropertyVideoJob;
use App\Jobs\ProcessPropertyImageJob;
use App\Models\Property;
use App\Models\BusinessType;
use App\Models\PropertyImageUpload;
use App\Models\PropertyPhoto;
use App\Models\PropertyVideo;
use App\Models\User;
use App\Services\Videos\PropertyVideoTranscoder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Process\Process;
use ReflectionMethod;
use Throwable;
use Tests\TestCase;

class PropertyMediaProcessingTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_unavailable_image_token_fails_instead_of_silently_losing_the_image(): void
    {
        $user = User::factory()->create();
        $property = $this->property();
        $this->expectException(ValidationException::class);
        app(AttachPropertyImageUploadsAction::class)->execute($property, $user, (string) Str::uuid(), []);
    }

    public function test_an_apartment_image_is_linked_and_queued_after_saving(): void
    {
        Storage::fake('public');
        Queue::fake();
        $user = User::factory()->create();
        $property = $this->property();
        $token = (string) Str::uuid();
        $path = "properties/original/{$user->id}/{$token}.jpg";
        Storage::disk('public')->put($path, 'image-upload');
        $upload = PropertyImageUpload::create([
            'user_id' => $user->id, 'token' => $token, 'disk' => 'public',
            'temp_path' => $path, 'original_name' => 'apartment.jpg',
            'sanitized_name' => 'apartment.jpg', 'extension' => 'jpg',
            'mime_type' => 'image/jpeg', 'size' => 12,
            'sha256' => hash('sha256', 'image-upload'), 'status' => 'uploaded',
        ]);

        app(AttachPropertyImageUploadsAction::class)->execute($property, $user, $token, []);

        $this->assertSame(1, $property->photos()->count());
        $this->assertSame($property->photos()->first()->id, $upload->fresh()->property_photo_id);
        Storage::disk('public')->assertExists($path);
        Queue::assertPushed(ProcessPropertyImageJob::class);
    }

    public function test_apartment_reference_codes_include_deleted_properties_in_the_sequence(): void
    {
        $deleted = $this->property();
        $deleted->update(['codigo_referencia' => 'AP001']);
        $deleted->delete();
        $current = $this->property();

        $method = new ReflectionMethod(AdminController::class, 'assignSequentialCodigoReferencia');
        $method->invoke(app(AdminController::class), $current);

        $this->assertSame('AP002', $current->fresh()->codigo_referencia);
    }

    public function test_staged_image_can_be_recovered_after_refresh(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['role' => 'admin', 'admin_enabled' => true]);
        $token = (string) Str::uuid();
        $path = "properties/original/{$user->id}/{$token}.jpg";
        Storage::disk('public')->put($path, 'image-upload');
        PropertyImageUpload::create([
            'user_id' => $user->id, 'token' => $token, 'disk' => 'public',
            'temp_path' => $path, 'original_name' => 'apartment.jpg',
            'sanitized_name' => 'apartment.jpg', 'extension' => 'jpg',
            'mime_type' => 'image/jpeg', 'size' => 12,
            'sha256' => hash('sha256', 'image-upload'), 'status' => 'uploaded',
        ]);

        $this->actingAs($user)->postJson('/admin/properties/uploads/recover', ['tokens' => [$token]])
            ->assertOk()
            ->assertJsonPath('uploads.0.token', $token);
    }

    public function test_admin_can_refresh_csrf_token_before_saving(): void
    {
        $user = User::factory()->create(['role' => 'admin', 'admin_enabled' => true]);

        $this->actingAs($user)->getJson('/admin/csrf-token')
            ->assertOk()
            ->assertJsonStructure(['token'])
            ->assertJson(fn ($json) => $json->whereType('token', 'string')->etc());
    }

    public function test_missing_original_photo_file_does_not_generate_a_public_url(): void
    {
        Storage::fake('public');

        $property = $this->property();
        $photo = PropertyPhoto::create([
            'property_id' => $property->id,
            'arquivo' => 'properties/original/missing.jpg',
            'url' => url('/media/properties/original/missing.jpg'),
            'original_path' => 'properties/original/missing.jpg',
            'source_mime_type' => 'image/jpeg',
            'mime_type' => 'image/jpeg',
            'principal' => true,
            'ordem' => 0,
        ]);

        $this->assertNull($photo->fresh()->original_url);
        $this->assertNull($photo->fresh()->thumb_small_url);
        $this->assertNull($photo->fresh()->medium_url);
    }

    public function test_saving_an_apartment_with_a_deleted_code_reserves_a_new_code_and_keeps_the_photo(): void
    {
        Storage::fake('public');
        Queue::fake();
        $user = User::factory()->create(['role' => 'admin', 'admin_enabled' => true]);
        $businessType = BusinessType::create(['name' => 'Comprar', 'slug' => 'comprar']);
        $deleted = $this->property();
        $deleted->update(['codigo_referencia' => 'AP001']);
        $deleted->delete();
        $property = $this->property();
        $token = (string) Str::uuid();
        $path = "properties/original/{$user->id}/{$token}.jpg";
        Storage::disk('public')->put($path, 'image-upload');
        PropertyImageUpload::create([
            'user_id' => $user->id, 'token' => $token, 'disk' => 'public',
            'temp_path' => $path, 'original_name' => 'apartment.jpg',
            'sanitized_name' => 'apartment.jpg', 'extension' => 'jpg',
            'mime_type' => 'image/jpeg', 'size' => 12,
            'sha256' => hash('sha256', 'image-upload'), 'status' => 'uploaded',
        ]);

        $this->actingAs($user)->put("/admin/properties/{$property->id}", [
            'titulo' => $property->titulo,
            'descricao' => '<p>Apartamento de teste</p>',
            'tipo_propriedade_id' => $property->tipo_propriedade_id,
            'business_type_ids' => [$businessType->id],
            'endereco' => $property->endereco,
            'bairro' => $property->bairro,
            'cidade' => $property->cidade,
            'estado' => $property->estado,
            'featured_upload_token' => $token,
            'gallery_upload_tokens' => [],
        ])->assertRedirect();

        $this->assertSame('AP002', $property->fresh()->codigo_referencia);
        $this->assertSame(1, $property->photos()->count());
        Storage::disk('public')->assertExists($path);
    }

    public function test_a_video_is_converted_and_its_original_is_removed_only_after_success(): void
    {
        try {
            $check = new Process(['ffmpeg', '-version']);
            $check->run();
        } catch (Throwable) {
            $this->markTestSkipped('FFmpeg indisponível.');
        }
        if (!$check->isSuccessful()) $this->markTestSkipped('FFmpeg indisponível.');

        Storage::fake('local');
        Storage::fake('public');
        $user = User::factory()->create();
        $property = $this->property();
        $source = "properties/videos/original/{$user->id}/sample.mp4";
        Storage::disk('local')->makeDirectory(dirname($source));

        $generate = new Process([
            'ffmpeg', '-hide_banner', '-loglevel', 'error', '-y',
            '-f', 'lavfi', '-i', 'testsrc=size=320x240:rate=10',
            '-t', '1', '-c:v', 'mpeg4', '-q:v', '5', Storage::disk('local')->path($source),
        ]);
        $generate->mustRun();

        $video = PropertyVideo::create([
            'property_id' => $property->id,
            'user_id' => $user->id,
            'token' => (string) Str::uuid(),
            'original_path' => $source,
            'original_name' => 'sample.mp4',
            'source_size' => Storage::disk('local')->size($source),
            'status' => 'uploaded',
        ]);

        (new ProcessPropertyVideoJob($video->id))->handle(app(PropertyVideoTranscoder::class));

        $video->refresh();
        $this->assertSame('ready', $video->status);
        $this->assertNull($video->original_path);
        Storage::disk('local')->assertMissing($source);
        Storage::disk('public')->assertExists($video->path);
        $this->assertGreaterThan(0, $video->size);
    }

    public function test_a_failed_video_conversion_keeps_the_original(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        $user = User::factory()->create();
        $property = $this->property();
        $source = "properties/videos/original/{$user->id}/invalid.mp4";
        Storage::disk('local')->put($source, 'invalid-video');
        $video = PropertyVideo::create([
            'property_id' => $property->id,
            'user_id' => $user->id,
            'token' => (string) Str::uuid(),
            'original_path' => $source,
            'original_name' => 'invalid.mp4',
            'source_size' => 13,
            'status' => 'uploaded',
        ]);

        try {
            (new ProcessPropertyVideoJob($video->id))->handle(app(PropertyVideoTranscoder::class));
            $this->fail('A conversão inválida deveria falhar.');
        } catch (Throwable $error) {
            $this->assertSame('failed', $video->fresh()->status);
            $this->assertSame($source, $video->fresh()->original_path);
            Storage::disk('local')->assertExists($source);
            $this->assertNull($video->fresh()->path);
        }
    }

    private function property(): Property
    {
        $typeId = DB::table('property_types')->where('slug', 'apartamento')->value('id');
        if (!$typeId) {
            $typeId = DB::table('property_types')->insertGetId([
                'nome_tipo' => 'Apartamento', 'slug' => 'apartamento',
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        return Property::create([
            'codigo_anuncio' => 'TEST-' . Str::random(6),
            'titulo' => 'Apartamento de teste',
            'slug' => 'apartamento-' . Str::random(6),
            'descricao' => 'Descricao',
            'tipo_propriedade_id' => $typeId,
            'operacao' => 'Venda', 'valor' => 100, 'moeda' => 'BRL',
            'endereco' => 'Rua Teste', 'bairro' => 'Centro',
            'cidade' => 'Sao Paulo', 'estado' => 'SP',
        ]);
    }
}
