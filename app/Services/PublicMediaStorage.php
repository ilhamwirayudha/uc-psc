<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class PublicMediaStorage
{
    public static function isSupabaseActive(): bool
    {
        $disk = (string) config('filesystems.media_disk', 'public');
        $url = trim((string) config('services.supabase.url', ''));
        $key = trim((string) config('services.supabase.service_key', ''));

        if ($disk === 'supabase') {
            return true;
        }

        return $disk === 'auto' && $url !== '' && $key !== '';
    }

    public static function storeUploadedFile(UploadedFile $file, string $directory): string
    {
        if (! self::isSupabaseActive()) {
            return $file->store(trim($directory, '/'), 'public');
        }

        $extension = $file->guessExtension() ?: $file->getClientOriginalExtension() ?: 'bin';
        $path = trim($directory, '/') . '/' . Str::random(40) . '.' . strtolower($extension);
        $contents = file_get_contents($file->getRealPath());

        if ($contents === false) {
            throw new RuntimeException('Gagal membaca file yang diunggah.');
        }

        $mimeType = $file->getMimeType() ?: 'application/octet-stream';

        return self::put($path, $contents, $mimeType);
    }

    public static function put(string $path, string $contents, string $mimeType = 'application/octet-stream'): string
    {
        $normalizedPath = ltrim($path, '/');

        if (! self::isSupabaseActive()) {
            Storage::disk('public')->put($normalizedPath, $contents);

            return $normalizedPath;
        }

        [$baseUrl, $serviceKey, $bucket] = self::supabaseCredentials();
        $endpoint = self::objectEndpoint($baseUrl, $bucket, $normalizedPath);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $serviceKey,
            'apikey' => $serviceKey,
            'x-upsert' => 'true',
        ])
            ->timeout(20)
            ->withBody($contents, $mimeType)
            ->post($endpoint);

        if ($response->failed() && self::isBucketNotFound($response->body())) {
            self::ensureBucketExists($baseUrl, $serviceKey, $bucket);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $serviceKey,
                'apikey' => $serviceKey,
                'x-upsert' => 'true',
            ])
                ->timeout(20)
                ->withBody($contents, $mimeType)
                ->post($endpoint);
        }

        if ($response->failed()) {
            throw new RuntimeException('Gagal mengunggah gambar ke Supabase Storage (HTTP ' . $response->status() . ').');
        }

        return $normalizedPath;
    }

    public static function delete(?string $path): void
    {
        if (! $path || str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return;
        }

        $normalizedPath = ltrim($path, '/');

        if (Storage::disk('public')->exists($normalizedPath)) {
            Storage::disk('public')->delete($normalizedPath);
        }

        if (! self::isSupabaseActive()) {
            return;
        }

        $url = trim((string) config('services.supabase.url', ''));
        $serviceKey = trim((string) config('services.supabase.service_key', ''));
        $bucket = trim((string) config('services.supabase.bucket', 'ucpsc-media'));

        if ($url === '' || $serviceKey === '' || $bucket === '') {
            return;
        }

        Http::withHeaders([
            'Authorization' => 'Bearer ' . $serviceKey,
            'apikey' => $serviceKey,
        ])
            ->timeout(15)
            ->delete(rtrim($url, '/') . '/storage/v1/object/' . rawurlencode($bucket), [
                'prefixes' => [$normalizedPath],
            ]);
    }

    public static function url(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $normalizedPath = ltrim($path, '/');

        if (self::isSupabaseActive()) {
            $url = rtrim(trim((string) config('services.supabase.url', '')), '/');
            $bucket = trim((string) config('services.supabase.bucket', 'ucpsc-media'));

            if ($url !== '' && $bucket !== '') {
                $encodedPath = implode('/', array_map('rawurlencode', explode('/', $normalizedPath)));

                return $url . '/storage/v1/object/public/' . rawurlencode($bucket) . '/' . $encodedPath;
            }
        }

        return Storage::disk('public')->url($normalizedPath);
    }

    /**
     * @return array{0: string, 1: string, 2: string}
     */
    protected static function supabaseCredentials(): array
    {
        $url = rtrim(trim((string) config('services.supabase.url', '')), '/');
        $serviceKey = trim((string) config('services.supabase.service_key', ''));
        $bucket = trim((string) config('services.supabase.bucket', 'ucpsc-media'));

        if ($url === '' || $serviceKey === '' || $bucket === '') {
            throw new RuntimeException('Konfigurasi SUPABASE_URL, SUPABASE_SERVICE_ROLE_KEY, atau SUPABASE_STORAGE_BUCKET di .env belum lengkap.');
        }

        return [$url, $serviceKey, $bucket];
    }

    protected static function objectEndpoint(string $baseUrl, string $bucket, string $path): string
    {
        $encodedPath = implode('/', array_map('rawurlencode', explode('/', ltrim($path, '/'))));

        return $baseUrl . '/storage/v1/object/' . rawurlencode($bucket) . '/' . $encodedPath;
    }

    protected static function isBucketNotFound(string $body): bool
    {
        return stripos($body, 'bucket not found') !== false;
    }

    protected static function ensureBucketExists(string $baseUrl, string $serviceKey, string $bucket): void
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $serviceKey,
            'apikey' => $serviceKey,
        ])
            ->timeout(15)
            ->post($baseUrl . '/storage/v1/bucket', [
                'id' => $bucket,
                'name' => $bucket,
                'public' => true,
            ]);

        if ($response->failed() && $response->status() !== 409) {
            throw new RuntimeException('Gagal membuat bucket Supabase Storage "' . $bucket . '".');
        }
    }
}
