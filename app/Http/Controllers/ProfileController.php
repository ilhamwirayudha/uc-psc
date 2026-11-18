<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingAssignee;
use App\Models\Client;
use App\Models\UserActivityLog;
use App\Services\ActivityLogger;
use App\Services\PublicMediaStorage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use RuntimeException;
use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyUploadedFile;

class ProfileController extends Controller
{
    /**
     * Tampilkan halaman profil pengguna beserta data tab Pengaturan & Log Aktivitas.
     */
    public function edit(Request $request)
    {
        $user = auth()->user();

        $joinedAtText = $this->formatIndonesianDateTime($user->created_at);
        $updatedAtText = $this->formatIndonesianDateTime($user->updated_at);
        $activityLogs = $this->buildUserActivityLogs($user);
        $sessionInfo = $this->buildSessionInfo($user, $request);

        return view('profile.edit', compact('user', 'joinedAtText', 'updatedAtText', 'activityLogs', 'sessionInfo'));
    }

    /**
     * Kumpulkan informasi sesi login & keamanan aktual akun.
     *
     * @return array{active_count: int, device: string, ip: string, last_password_change_text: string}
     */
    protected function buildSessionInfo($user, Request $request): array
    {
        $activeCount = (int) DB::table('sessions')
            ->where('user_id', $user->id)
            ->count();

        $lastPasswordChangedAt = UserActivityLog::where('user_id', $user->id)
            ->whereIn('action', ['password.updated', 'profile.updated'])
            ->where('description', 'like', '%Kata Sandi%')
            ->latest('created_at')
            ->value('created_at');

        return [
            'active_count' => max(1, $activeCount),
            'device' => $this->describeDevice($request->userAgent()),
            'ip' => $request->ip() ?: '-',
            'last_password_change_text' => $lastPasswordChangedAt
                ? $this->formatIndonesianDateTime($lastPasswordChangedAt)
                : 'Belum ada catatan perubahan',
        ];
    }

    /**
     * Ringkas User-Agent menjadi nama browser dan sistem operasi yang mudah dibaca.
     */
    protected function describeDevice(?string $userAgent): string
    {
        if (! $userAgent) {
            return 'Perangkat Browser';
        }

        $browser = match (true) {
            str_contains($userAgent, 'Edg/') => 'Microsoft Edge',
            str_contains($userAgent, 'OPR/') || str_contains($userAgent, 'Opera') => 'Opera',
            str_contains($userAgent, 'Firefox/') => 'Mozilla Firefox',
            str_contains($userAgent, 'Chrome/') => 'Google Chrome',
            str_contains($userAgent, 'Safari/') => 'Safari',
            default => 'Browser',
        };

        $os = match (true) {
            str_contains($userAgent, 'Windows') => 'Windows',
            str_contains($userAgent, 'Macintosh') || str_contains($userAgent, 'Mac OS') => 'macOS',
            str_contains($userAgent, 'Android') => 'Android',
            str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'iPad') => 'iOS',
            str_contains($userAgent, 'Linux') => 'Linux',
            default => 'Desktop',
        };

        return $browser . ' · ' . $os;
    }

    /**
     * Format tanggal & waktu Bahasa Indonesia baku untuk card/daftar tanpa imbuhan WIB.
     */
    protected function formatIndonesianDateTime($value): string
    {
        if (!$value) {
            return '-';
        }

        return Carbon::parse($value)
            ->timezone(config('app.timezone', 'Asia/Jakarta'))
            ->locale('id')
            ->translatedFormat('j F Y, H:i');
    }

    /**
     * Susun daftar log aktivitas pengguna dari histori persisten (`user_activity_logs`)
     * serta data historis pra-migrasi pada PostgreSQL.
     */
    protected function buildUserActivityLogs($user): \Illuminate\Support\Collection
    {
        $categoryLabels = [
            'akun' => 'Akun',
            'klien' => 'Klien',
            'penugasan' => 'Penugasan',
            'layanan' => 'Layanan',
        ];

        $persistedLogs = UserActivityLog::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        $earliestPersistedByCategory = $persistedLogs
            ->groupBy('category')
            ->map(fn ($items) => $items->min(fn ($item) => Carbon::parse($item->created_at)->timestamp));

        $logs = collect();

        foreach ($persistedLogs as $entry) {
            $logs->push([
                'id' => 'log-' . $entry->id,
                'category' => $entry->category,
                'category_label' => $categoryLabels[$entry->category] ?? Str::title($entry->category),
                'title' => $entry->title,
                'description' => $entry->description,
                'meta' => $entry->meta ?: ($categoryLabels[$entry->category] ?? 'Sistem'),
                'timestamp_text' => $this->formatIndonesianDateTime($entry->created_at),
                'ts' => Carbon::parse($entry->created_at)->timestamp,
                'sort_id' => (int) $entry->id,
            ]);
        }

        if ($user->created_at) {
            $roleLabel = $user->isAdmin() ? 'Administrator' : ($user->isPsikolog() ? 'Psikolog' : 'Staff Layanan');
            $logs->push([
                'id' => 'user-created-' . $user->id,
                'category' => 'akun',
                'category_label' => 'Akun',
                'title' => 'Akun Terdaftar di Sistem',
                'description' => 'Akun ' . $roleLabel . ' dibuat untuk ' . $user->name . '.',
                'meta' => $user->email,
                'timestamp_text' => $this->formatIndonesianDateTime($user->created_at),
                'ts' => Carbon::parse($user->created_at)->timestamp,
                'sort_id' => 0,
            ]);
        }

        if (
            ! $persistedLogs->contains('category', 'akun')
            && $user->updated_at
            && (! $user->created_at || $user->updated_at->gt($user->created_at))
        ) {
            $roleLabel = $user->isAdmin() ? 'Administrator' : ($user->isPsikolog() ? 'Psikolog' : 'Staff Layanan');
            $logs->push([
                'id' => 'user-updated-' . $user->id,
                'category' => 'akun',
                'category_label' => 'Akun',
                'title' => 'Pembaruan Data Akun',
                'description' => 'Data biodata, foto profil, background hero, atau kata sandi akun diperbarui.',
                'meta' => $user->position ?: $roleLabel,
                'timestamp_text' => $this->formatIndonesianDateTime($user->updated_at),
                'ts' => Carbon::parse($user->updated_at)->timestamp,
                'sort_id' => 0,
            ]);
        }

        $createdClients = Client::where('created_by', $user->id)
            ->latest('created_at')
            ->limit(10)
            ->get(['id', 'name', 'service_type', 'created_at']);

        foreach ($createdClients as $client) {
            $ts = $client->created_at ? Carbon::parse($client->created_at)->timestamp : 0;
            if (isset($earliestPersistedByCategory['klien']) && $ts >= $earliestPersistedByCategory['klien']) {
                continue;
            }

            $serviceLabel = match ($client->service_type) {
                'konseling' => 'Layanan Konseling',
                'psikotes' => 'Layanan Psikotes',
                default => Str::title(str_replace('_', ' ', (string) ($client->service_type ?? 'Umum'))),
            };

            $logs->push([
                'id' => 'client-' . $client->id,
                'category' => 'klien',
                'category_label' => 'Klien',
                'title' => 'Mendaftarkan Klien Baru',
                'description' => 'Klien ' . $client->name . ' ditambahkan ke sistem.',
                'meta' => $serviceLabel,
                'timestamp_text' => $this->formatIndonesianDateTime($client->created_at),
                'ts' => $ts,
                'sort_id' => 0,
            ]);
        }

        $assignees = BookingAssignee::with(['booking.client:id,name', 'booking.company:id,name'])
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id);
                if ($user->counselor_id) {
                    $query->orWhere('counselor_id', $user->counselor_id);
                }
            })
            ->latest('created_at')
            ->limit(10)
            ->get();

        foreach ($assignees as $assignment) {
            $eventTime = $assignment->created_at;
            $ts = $eventTime ? Carbon::parse($eventTime)->timestamp : 0;
            if (isset($earliestPersistedByCategory['penugasan']) && $ts >= $earliestPersistedByCategory['penugasan']) {
                continue;
            }

            $clientName = $assignment->booking?->company?->name ?? $assignment->booking?->client?->name ?? 'Klien';

            $logs->push([
                'id' => 'assign-' . $assignment->id,
                'category' => 'penugasan',
                'category_label' => 'Penugasan',
                'title' => 'Ditugaskan sebagai ' . $assignment->role_label,
                'description' => 'Anda ditugaskan sebagai ' . $assignment->role_label . ' pada layanan untuk ' . $clientName . '.',
                'meta' => $assignment->batch_completed_at ? 'Tugas Selesai' : 'Tugas Aktif',
                'timestamp_text' => $this->formatIndonesianDateTime($eventTime),
                'ts' => $ts,
                'sort_id' => 0,
            ]);
        }

        $bookings = Booking::with('client:id,name')
            ->where(function ($query) use ($user) {
                $query->where('staff_penguji_id', $user->id)
                    ->orWhere('staff_koreksi_id', $user->id)
                    ->orWhere('staff_pelapor_id', $user->id);
            })
            ->latest('updated_at')
            ->limit(10)
            ->get();

        foreach ($bookings as $booking) {
            $eventTime = $booking->updated_at ?? $booking->created_at;
            $ts = $eventTime ? Carbon::parse($eventTime)->timestamp : 0;
            if (isset($earliestPersistedByCategory['layanan']) && $ts >= $earliestPersistedByCategory['layanan']) {
                continue;
            }

            $roles = [];
            if ((int) $booking->staff_penguji_id === (int) $user->id) {
                $roles[] = 'Penguji';
            }
            if ((int) $booking->staff_koreksi_id === (int) $user->id) {
                $roles[] = 'Korektor';
            }
            if ((int) $booking->staff_pelapor_id === (int) $user->id) {
                $roles[] = 'Pelapor';
            }

            $kategoriLabel = $booking->kategori === 'psikotes' ? 'Psikotes' : 'Konseling';
            $clientName = $booking->client?->name ?? 'Klien';

            $logs->push([
                'id' => 'booking-' . $booking->id,
                'category' => 'layanan',
                'category_label' => 'Layanan',
                'title' => 'Penanganan Layanan ' . $kategoriLabel,
                'description' => 'Terlibat sebagai ' . implode(', ', $roles) . ' untuk klien ' . $clientName . '.',
                'meta' => $booking->status ? Str::title(str_replace('_', ' ', $booking->status)) : $kategoriLabel,
                'timestamp_text' => $this->formatIndonesianDateTime($eventTime),
                'ts' => $ts,
                'sort_id' => 0,
            ]);
        }

        return $logs
            ->sortByDesc(fn (array $item) => sprintf('%012d-%012d', max(0, (int) ($item['ts'] ?? 0)), max(0, (int) ($item['sort_id'] ?? 0))))
            ->take(50)
            ->values();
    }

    /**
     * Perbarui foto profil (Profile Picture).
     */
    public function updateAvatar(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ], [
            'avatar.required' => 'Silakan pilih file gambar untuk foto profil.',
            'avatar.image' => 'File harus berupa gambar.',
            'avatar.mimes' => 'Format gambar harus JPEG, PNG, JPG, atau WEBP.',
            'avatar.max' => 'Ukuran gambar maksimal 5MB.',
        ]);

        try {
            $path = $this->storeOptimizedImage($request->file('avatar'), 'avatars', 400, 400);
        } catch (RuntimeException $e) {
            return redirect()->route('profile.edit')->withErrors(['avatar' => $e->getMessage()]);
        }

        PublicMediaStorage::delete($user->avatar);
        $user->update(['avatar' => $path]);

        UserActivityLog::record(
            $user->id,
            'akun',
            'avatar.updated',
            'Mengganti Foto Profil',
            'Foto profil akun diperbarui.',
            $user->email
        );

        return redirect()->route('profile.edit')->with('success', 'Foto profil berhasil diperbarui.');
    }

    /**
     * Hapus foto profil (kembali ke inisial).
     */
    public function deleteAvatar()
    {
        $user = auth()->user();

        PublicMediaStorage::delete($user->avatar);

        $user->update(['avatar' => null]);

        UserActivityLog::record(
            $user->id,
            'akun',
            'avatar.deleted',
            'Menghapus Foto Profil',
            'Foto profil akun dihapus dan dikembalikan ke inisial nama.',
            $user->email
        );

        return redirect()->route('profile.edit')->with('success', 'Foto profil berhasil dihapus.');
    }

    /**
     * Hapus gambar background hero (kembali ke gradien default).
     */
    public function deleteHeroBanner()
    {
        $user = auth()->user();

        PublicMediaStorage::delete($user->hero_banner);

        $user->update(['hero_banner' => null]);

        UserActivityLog::record(
            $user->id,
            'akun',
            'banner.deleted',
            'Menghapus Background Hero',
            'Background hero profil dikembalikan ke gradien bawaan.',
            $user->email
        );

        return redirect()->route('profile.edit')->with('success', 'Background hero berhasil dihapus.');
    }

    /**
     * Perbarui biodata, foto profil (opsional), dan kata sandi (opsional) pengguna.
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'employee_id' => ['nullable', 'string', 'max:60'],
            'position' => ['nullable', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9]+$/'],
            'gender' => ['nullable', 'in:l,p'],
            'remove_avatar' => ['nullable', 'boolean'],
            'remove_hero_banner' => ['nullable', 'boolean'],
        ];

        $messages = [
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.max' => 'Nama lengkap maksimal 255 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan oleh akun lain.',
            'employee_id.max' => 'NIP maksimal 60 karakter.',
            'position.max' => 'Jabatan maksimal 120 karakter.',
            'phone.max' => 'Nomor telepon maksimal 30 digit.',
            'phone.regex' => 'Nomor telepon hanya boleh berisi angka.',
            'gender.in' => 'Pilihan jenis kelamin tidak valid.',
        ];

        $wantsPasswordChange = $request->filled('current_password')
            || $request->filled('password')
            || $request->filled('password_confirmation');

        $rawUploadErrors = [];
        foreach (['avatar' => 'foto profil', 'hero_banner' => 'background hero'] as $field => $label) {
            $rawFile = $request->files->get($field);
            if ($rawFile instanceof SymfonyUploadedFile && ! $rawFile->isValid()) {
                $rawUploadErrors[$field] = 'Upload ' . $label . ' gagal: ' . $rawFile->getErrorMessage();
            }
        }
        if ($rawUploadErrors !== []) {
            return redirect()->route('profile.edit')->withInput()->withErrors($rawUploadErrors);
        }

        if ($request->hasFile('avatar')) {
            $rules['avatar'] = ['image', 'mimes:jpeg,png,jpg,webp', 'max:5120'];
            $messages['avatar.image'] = 'File harus berupa gambar.';
            $messages['avatar.mimes'] = 'Format gambar harus JPEG, PNG, JPG, atau WEBP.';
            $messages['avatar.max'] = 'Ukuran gambar maksimal 5MB.';
        }

        if ($request->hasFile('hero_banner')) {
            $rules['hero_banner'] = ['image', 'mimes:jpeg,png,jpg,webp', 'max:5120'];
            $messages['hero_banner.image'] = 'Background hero harus berupa gambar.';
            $messages['hero_banner.mimes'] = 'Format gambar background hero harus JPEG, PNG, JPG, atau WEBP.';
            $messages['hero_banner.max'] = 'Ukuran gambar background hero maksimal 5MB.';
        }

        if ($wantsPasswordChange) {
            $rules['current_password'] = ['required', 'current_password'];
            $rules['password'] = ['required', 'confirmed', Password::min(6)];
            $messages['current_password.required'] = 'Kata sandi saat ini wajib diisi.';
            $messages['current_password.current_password'] = 'Kata sandi saat ini tidak sesuai.';
            $messages['password.required'] = 'Kata sandi baru wajib diisi.';
            $messages['password.confirmed'] = 'Konfirmasi kata sandi baru tidak cocok.';
            $messages['password.min'] = 'Kata sandi baru minimal 6 karakter.';
        }

        $validated = $request->validate($rules, $messages);

        $before = $user->only(['name', 'email', 'employee_id', 'position', 'phone', 'gender']);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'employee_id' => $validated['employee_id'] ?? null,
            'position' => $validated['position'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'gender' => $validated['gender'] ?? null,
        ];

        $mediaChanges = [];

        try {
            if ($request->hasFile('avatar')) {
                $newAvatarPath = $this->storeOptimizedImage($request->file('avatar'), 'avatars', 400, 400);
                PublicMediaStorage::delete($user->avatar);
                $data['avatar'] = $newAvatarPath;
                $mediaChanges[] = 'Foto Profil diperbarui';
            } elseif ($request->boolean('remove_avatar') && $user->avatar) {
                PublicMediaStorage::delete($user->avatar);
                $data['avatar'] = null;
                $mediaChanges[] = 'Foto Profil dihapus';
            }

            if ($request->hasFile('hero_banner')) {
                $newBannerPath = $this->storeOptimizedImage($request->file('hero_banner'), 'hero-banners', 1600, null);
                PublicMediaStorage::delete($user->hero_banner);
                $data['hero_banner'] = $newBannerPath;
                $mediaChanges[] = 'Background Hero diperbarui';
            } elseif ($request->boolean('remove_hero_banner') && $user->hero_banner) {
                PublicMediaStorage::delete($user->hero_banner);
                $data['hero_banner'] = null;
                $mediaChanges[] = 'Background Hero dihapus';
            }
        } catch (RuntimeException $e) {
            return redirect()->route('profile.edit')->withInput()->withErrors(['avatar' => $e->getMessage()]);
        }

        if ($wantsPasswordChange) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        if ($user->isPsikolog() && $user->counselor) {
            $user->counselor->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? $user->counselor->phone,
            ]);
        }

        ActivityLogger::logUserProfileUpdate($user, $before, $data, $mediaChanges, $wantsPasswordChange);

        return redirect()->route('profile.edit')->with('success', 'Profil akun berhasil diperbarui.');
    }

    /**
     * Perbarui alamat email pengguna.
     */
    public function updateEmail(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.unique' => 'Alamat email sudah digunakan oleh akun lain.',
        ]);

        $oldEmail = $user->email;
        $user->update($validated);

        if ($user->isPsikolog() && $user->counselor) {
            $user->counselor->update(['email' => $validated['email']]);
        }

        if ($oldEmail !== $validated['email']) {
            UserActivityLog::record(
                $user->id,
                'akun',
                'email.updated',
                'Mengubah Alamat Email',
                'Email diperbarui ("' . $oldEmail . '" → "' . $validated['email'] . '").',
                'Email'
            );
        }

        return redirect()->route('profile.edit')->with('success', 'Alamat email berhasil diperbarui.');
    }

    /**
     * Perbarui kata sandi pengguna.
     */
    public function updatePassword(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(6)],
        ], [
            'current_password.required' => 'Kata sandi saat ini wajib diisi.',
            'current_password.current_password' => 'Kata sandi saat ini tidak sesuai.',
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
            'password.min' => 'Kata sandi baru minimal 6 karakter.',
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        UserActivityLog::record(
            $user->id,
            'akun',
            'password.updated',
            'Mengubah Kata Sandi',
            'Kata sandi akun berhasil diperbarui.',
            'Keamanan Akun'
        );

        return redirect()->route('profile.edit', ['tab' => 'pengaturan'])->with('success', 'Kata sandi berhasil diperbarui.');
    }

    /**
     * Proses dan simpan gambar dengan resampling bicubic/area-averaged berkualitas tinggi
     * agar tampilan avatar & banner tetap halus dan tajam di berbagai ukuran.
     */
    protected function storeOptimizedImage(
        UploadedFile $file,
        string $directory,
        int $maxWidth,
        ?int $squareHeight = null
    ): string {
        if (!extension_loaded('gd')) {
            return PublicMediaStorage::storeUploadedFile($file, $directory);
        }

        $raw = @file_get_contents($file->getRealPath());
        $src = $raw !== false ? @imagecreatefromstring($raw) : false;
        if (!$src) {
            return PublicMediaStorage::storeUploadedFile($file, $directory);
        }

        $src = $this->applyExifOrientation($file, $src);
        $srcW = imagesx($src);
        $srcH = imagesy($src);

        if ($srcW <= 0 || $srcH <= 0) {
            imagedestroy($src);
            return PublicMediaStorage::storeUploadedFile($file, $directory);
        }

        if ($squareHeight !== null) {
            // Crop 1:1 dengan fokus area atas (15%) agar kepala/wajah tidak terpotong
            $cropSize = min($srcW, $srcH);
            $srcX = (int) floor(($srcW - $cropSize) / 2);
            $srcY = (int) floor(($srcH - $cropSize) * 0.15);
            $cropW = $cropSize;
            $cropH = $cropSize;
            $dstW = min($cropSize, $maxWidth);
            $dstH = min($cropSize, $squareHeight);
        } else {
            $srcX = 0;
            $srcY = 0;
            $cropW = $srcW;
            $cropH = $srcH;
            $dstW = $srcW > $maxWidth ? $maxWidth : $srcW;
            $dstH = $srcW > $maxWidth ? (int) max(1, round($srcH * ($maxWidth / $srcW))) : $srcH;
        }

        $dst = imagecreatetruecolor($dstW, $dstH);
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        $transparent = imagecolorallocatealpha($dst, 255, 255, 255, 127);
        imagefilledrectangle($dst, 0, 0, $dstW, $dstH, $transparent);

        imagecopyresampled($dst, $src, 0, 0, $srcX, $srcY, $dstW, $dstH, $cropW, $cropH);
        imagedestroy($src);

        ob_start();
        if (function_exists('imagewebp')) {
            imagewebp($dst, null, 92);
            $ext = 'webp';
            $mimeType = 'image/webp';
        } else {
            imagejpeg($dst, null, 92);
            $ext = 'jpg';
            $mimeType = 'image/jpeg';
        }
        $binary = ob_get_clean();
        imagedestroy($dst);

        if ($binary === false || $binary === '') {
            return PublicMediaStorage::storeUploadedFile($file, $directory);
        }

        $path = trim($directory, '/') . '/' . Str::random(40) . '.' . $ext;

        return PublicMediaStorage::put($path, $binary, $mimeType);
    }

    /**
     * Koreksi orientasi EXIF kamera ponsel agar foto potret tidak miring.
     */
    protected function applyExifOrientation(UploadedFile $file, \GdImage $image): \GdImage
    {
        if (!function_exists('exif_read_data')) {
            return $image;
        }

        $exif = @exif_read_data($file->getRealPath());
        $orientation = $exif['Orientation'] ?? null;

        $angle = match ($orientation) {
            3 => 180,
            6 => -90,
            8 => 90,
            default => 0,
        };

        if ($angle === 0) {
            return $image;
        }

        $rotated = imagerotate($image, $angle, 0);
        if ($rotated instanceof \GdImage) {
            imagedestroy($image);
            return $rotated;
        }

        return $image;
    }
}
