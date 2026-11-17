<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ActivityLogger
{
    /**
     * Catat aktivitas untuk user yang sedang login jika sesi autentikasi aktif.
     */
    public static function recordForCurrentUser(
        string $category,
        string $action,
        string $title,
        string $description,
        ?string $meta = null
    ): void {
        $userId = auth()->id();
        if (! $userId) {
            return;
        }

        UserActivityLog::record((int) $userId, $category, $action, $title, $description, $meta);
    }

    /**
     * Bandingkan perubahan field profil user sekecil apa pun dan catat entri log terbaru.
     *
     * @param  array<string, mixed>  $before
     * @param  array<string, mixed>  $after
     * @param  array<string, string>  $mediaChanges
     */
    public static function logUserProfileUpdate(
        User $user,
        array $before,
        array $after,
        array $mediaChanges = [],
        bool $passwordChanged = false
    ): void {
        $labels = [
            'name' => 'Nama Lengkap',
            'email' => 'Email',
            'employee_id' => 'NIP',
            'position' => 'Jabatan',
            'phone' => 'No. Telepon / WhatsApp',
            'gender' => 'Jenis Kelamin',
        ];

        $changes = [];

        foreach ($labels as $field => $label) {
            $oldVal = self::normalizeProfileValue($field, $before[$field] ?? null);
            $newVal = self::normalizeProfileValue($field, $after[$field] ?? null);

            if ($oldVal !== $newVal) {
                $changes[] = $label . ' ("' . $oldVal . '" → "' . $newVal . '")';
            }
        }

        foreach ($mediaChanges as $mediaNote) {
            $changes[] = $mediaNote;
        }

        if ($passwordChanged) {
            $changes[] = 'Kata Sandi diperbarui';
        }

        if ($changes === []) {
            UserActivityLog::record(
                $user->id,
                'akun',
                'profile.saved',
                'Menyimpan Profil Akun',
                'Formulir profil akun disimpan ulang tanpa perubahan nilai field.',
                $user->email
            );

            return;
        }

        UserActivityLog::record(
            $user->id,
            'akun',
            'profile.updated',
            'Memperbarui Profil Akun',
            'Perubahan tersimpan pada: ' . implode(', ', $changes) . '.',
            implode(', ', array_slice($changes, 0, 2))
        );
    }

    /**
     * Format daftar atribut model yang berubah pada event Eloquent `updated`.
     *
     * @param  array<string, string>  $fieldLabels
     */
    public static function summarizeDirtyFields(Model $model, array $fieldLabels = []): string
    {
        $dirtyKeys = array_keys(array_diff_key(
            $model->getChanges(),
            array_flip(['updated_at', 'created_at', 'remember_token', 'password'])
        ));

        if ($dirtyKeys === []) {
            return '';
        }

        $names = array_map(
            fn (string $key) => $fieldLabels[$key] ?? Str::title(str_replace('_', ' ', $key)),
            $dirtyKeys
        );

        return implode(', ', $names);
    }

    protected static function normalizeProfileValue(string $field, mixed $value): string
    {
        $str = trim((string) ($value ?? ''));
        if ($str === '') {
            return '-';
        }

        if ($field === 'gender') {
            return match ($str) {
                'l' => 'Laki-laki',
                'p' => 'Perempuan',
                default => $str,
            };
        }

        return Str::limit($str, 40);
    }
}
