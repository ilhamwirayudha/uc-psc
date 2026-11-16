<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ProfileUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_edit_renders_file_inputs_inside_unified_profile_form(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->get(route('profile.edit'));

        $response->assertOk();
        $html = $response->getContent();
        $this->assertMatchesRegularExpression(
            '/<form[^>]*id="unifiedProfileForm"[^>]*>.*?id="inline_avatar_input".*?id="inline_banner_input".*?<\/form>/s',
            $html
        );
    }

    public function test_update_profile_uploads_avatar_and_hero_banner_to_supabase_storage(): void
    {
        Config::set('filesystems.media_disk', 'supabase');
        Config::set('services.supabase', [
            'url' => 'https://example-project.supabase.co',
            'service_key' => 'secret-service-role-key',
            'bucket' => 'ucpsc-media',
        ]);

        Http::fake([
            'https://example-project.supabase.co/storage/v1/object/ucpsc-media*' => Http::response(['Key' => 'ok'], 200),
        ]);

        $user = User::factory()->create([
            'role' => 'admin',
            'avatar' => null,
            'hero_banner' => null,
        ]);

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'remove_avatar' => '0',
            'remove_hero_banner' => '0',
            'avatar' => UploadedFile::fake()->image('avatar.png', 200, 200),
            'hero_banner' => UploadedFile::fake()->image('banner.png', 800, 300),
        ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertNotNull($user->avatar);
        $this->assertNotNull($user->hero_banner);
        $this->assertStringStartsWith('avatars/', $user->avatar);
        $this->assertStringStartsWith('hero-banners/', $user->hero_banner);
        $this->assertStringStartsWith(
            'https://example-project.supabase.co/storage/v1/object/public/ucpsc-media/avatars/',
            $user->avatar_url
        );
        $this->assertStringStartsWith(
            'https://example-project.supabase.co/storage/v1/object/public/ucpsc-media/hero-banners/',
            $user->hero_banner_url
        );
    }

    public function test_update_profile_returns_validation_error_when_uploaded_file_has_php_upload_error(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $invalidFile = new UploadedFile(
            __FILE__,
            'huge-avatar.jpg',
            'image/jpeg',
            UPLOAD_ERR_INI_SIZE,
            false
        );

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'remove_avatar' => '0',
            'remove_hero_banner' => '0',
            'avatar' => $invalidFile,
        ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHasErrors('avatar');
        $response->assertSessionMissing('success');
    }
}
