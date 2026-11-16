<?php

namespace Tests\Unit;

use App\Services\PublicMediaStorage;
use Illuminate\Http\Client\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class PublicMediaStorageTest extends TestCase
{
    public function test_stores_urls_and_deletes_via_supabase_storage_when_enabled(): void
    {
        Storage::fake('public');

        Config::set('filesystems.media_disk', 'supabase');
        Config::set('services.supabase', [
            'url' => 'https://example-project.supabase.co',
            'service_key' => 'secret-service-role-key',
            'bucket' => 'ucpsc-media',
        ]);

        Http::fake([
            'https://example-project.supabase.co/storage/v1/object/ucpsc-media*' => Http::sequence()
                ->push(['error' => 'Bucket not found'], 404)
                ->push(['Key' => 'ucpsc-media/avatars/sample.webp'], 200)
                ->push([], 200),
            'https://example-project.supabase.co/storage/v1/bucket' => Http::response(['name' => 'ucpsc-media'], 200),
        ]);

        $path = PublicMediaStorage::put('avatars/sample.webp', 'binary-image-data', 'image/webp');

        $this->assertSame('avatars/sample.webp', $path);
        $this->assertSame(
            'https://example-project.supabase.co/storage/v1/object/public/ucpsc-media/avatars/sample.webp',
            PublicMediaStorage::url($path)
        );

        PublicMediaStorage::delete($path);

        Http::assertSent(function (Request $request) {
            return $request->method() === 'POST'
                && $request->url() === 'https://example-project.supabase.co/storage/v1/bucket'
                && $request['id'] === 'ucpsc-media'
                && $request['public'] === true;
        });

        Http::assertSent(function (Request $request) {
            return $request->method() === 'DELETE'
                && $request->url() === 'https://example-project.supabase.co/storage/v1/object/ucpsc-media'
                && $request['prefixes'] === ['avatars/sample.webp'];
        });
    }

    public function test_throws_clear_exception_when_supabase_selected_without_service_key(): void
    {
        Config::set('filesystems.media_disk', 'supabase');
        Config::set('services.supabase', [
            'url' => 'https://example-project.supabase.co',
            'service_key' => '',
            'bucket' => 'ucpsc-media',
        ]);

        $this->expectException(RuntimeException::class);

        PublicMediaStorage::put('avatars/sample.webp', 'binary-image-data', 'image/webp');
    }

    public function test_falls_back_to_local_public_disk_when_media_disk_is_public(): void
    {
        Storage::fake('public');

        Config::set('filesystems.media_disk', 'public');
        Config::set('services.supabase', [
            'url' => '',
            'service_key' => '',
            'bucket' => 'ucpsc-media',
        ]);

        $file = UploadedFile::fake()->image('avatar.jpg', 200, 200);
        $path = PublicMediaStorage::storeUploadedFile($file, 'avatars');

        Storage::disk('public')->assertExists($path);
        $this->assertStringContainsString('/storage/avatars/', (string) PublicMediaStorage::url($path));

        PublicMediaStorage::delete($path);
        Storage::disk('public')->assertMissing($path);
    }
}
