<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\GoogleCalendarEvent;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleCalendarService
{
    public function isConfigured(): bool
    {
        return !empty(config('services.google.client_id')) && !empty(config('services.google.client_secret'));
    }

    public function getRedirectUri(): string
    {
        $configured = (string) config('services.google.redirect');
        return str_starts_with($configured, 'http') ? $configured : url($configured);
    }

    public function getAuthorizationUrl(string $state): string
    {
        $params = http_build_query([
            'client_id' => config('services.google.client_id'),
            'redirect_uri' => $this->getRedirectUri(),
            'response_type' => 'code',
            'scope' => implode(' ', [
                'https://www.googleapis.com/auth/calendar.events',
                'https://www.googleapis.com/auth/userinfo.email',
            ]),
            'access_type' => 'offline',
            'prompt' => 'consent',
            'state' => $state,
        ]);

        return 'https://accounts.google.com/o/oauth2/v2/auth?' . $params;
    }

    public function exchangeCodeAndConnect(User $user, string $code): bool
    {
        if (!$this->isConfigured()) {
            return false;
        }

        $tokenResponse = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'client_id' => config('services.google.client_id'),
            'client_secret' => config('services.google.client_secret'),
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->getRedirectUri(),
        ]);

        if (!$tokenResponse->successful()) {
            Log::warning('Google OAuth token exchange failed', ['body' => $tokenResponse->body()]);
            return false;
        }

        $tokenData = $tokenResponse->json();
        $accessToken = $tokenData['access_token'] ?? null;
        if (!$accessToken) {
            return false;
        }

        $profileResponse = Http::withToken($accessToken)->get('https://www.googleapis.com/oauth2/v2/userinfo');
        $googleEmail = $profileResponse->successful() ? ($profileResponse->json('email') ?? $user->email) : $user->email;

        $user->update([
            'google_email' => $googleEmail,
            'google_access_token' => $accessToken,
            'google_refresh_token' => $tokenData['refresh_token'] ?? $user->google_refresh_token,
            'google_token_expires_at' => now()->addSeconds((int) ($tokenData['expires_in'] ?? 3500)),
            'google_calendar_id' => $user->google_calendar_id ?: 'primary',
            'google_connected_at' => now(),
        ]);

        return true;
    }

    public function disconnect(User $user): void
    {
        $user->update([
            'google_email' => null,
            'google_access_token' => null,
            'google_refresh_token' => null,
            'google_token_expires_at' => null,
            'google_connected_at' => null,
        ]);
    }

    /**
     * Kumpulkan seluruh user (Admin, Staff, Psikolog) yang terlibat dalam jadwal booking.
     */
    public function resolveTargetUsersForBooking(Booking $booking): Collection
    {
        $userIds = collect();

        // Admin aktif yang bertugas (atau admin pembuat/penjadwal saat ini)
        if (auth()->check() && auth()->user()->isAdmin()) {
            $userIds->push(auth()->id());
        } else {
            User::where('role', 'admin')->pluck('id')->each(fn ($id) => $userIds->push($id));
        }

        if ($booking->kategori === 'konseling') {
            if ($booking->counselor_id) {
                $psychUser = User::where('counselor_id', $booking->counselor_id)->first();
                if ($psychUser) {
                    $userIds->push($psychUser->id);
                }
            }
        } elseif ($booking->kategori === 'psikotes') {
            $assignees = $booking->assignees()->get();
            foreach ($assignees as $assignee) {
                if ($assignee->user_id) {
                    $userIds->push($assignee->user_id);
                }
                if ($assignee->counselor_id) {
                    $psychUser = User::where('counselor_id', $assignee->counselor_id)->first();
                    if ($psychUser) {
                        $userIds->push($psychUser->id);
                    }
                }
            }

            $candidatePsychIds = $booking->candidates()->pluck('assigned_psikolog_id')->filter()->unique();
            if ($candidatePsychIds->isNotEmpty()) {
                User::whereIn('counselor_id', $candidatePsychIds)->pluck('id')->each(fn ($id) => $userIds->push($id));
            }
            $candidateEditorIds = $booking->candidates()->pluck('assigned_editor_id')->filter()->unique();
            $candidateEditorIds->each(fn ($id) => $userIds->push($id));
        }

        return User::whereIn('id', $userIds->unique()->values())->get();
    }

    /**
     * Sinkronkan jadwal booking ke Google Calendar pribadi tiap petugas.
     * Non-blocking: bila user belum connect OAuth, status tercatat pending_auth.
     */
    public function syncBooking(Booking $booking): string
    {
        if (!$booking->tanggal_dijadwalkan || $booking->status === 'batal') {
            if ($booking->status === 'batal') {
                $this->deleteBookingEvents($booking);
            }
            $booking->update(['gcal_sync_status' => 'not_applicable']);
            return 'not_applicable';
        }

        // Untuk Konseling, hanya sinkron otomatis saat status terjadwal_gocal / reminder_h1 atau setelah dikonfirmasi
        if ($booking->kategori === 'konseling' && !in_array($booking->status, ['terjadwal_gocal', 'reminder_h1', 'perlu_bayar_overtime', 'perlu_sesi_lanjutan', 'selesai'], true)) {
            $booking->update(['gcal_sync_status' => 'not_applicable']);
            return 'not_applicable';
        }

        $targetUsers = $this->resolveTargetUsersForBooking($booking);
        if ($targetUsers->isEmpty()) {
            $booking->update(['gcal_sync_status' => 'pending_auth']);
            return 'pending_auth';
        }

        $allSynced = true;
        $hasFailure = false;
        $eventPayload = $this->buildEventPayload($booking);

        foreach ($targetUsers as $user) {
            $record = GoogleCalendarEvent::firstOrNew([
                'booking_id' => $booking->id,
                'user_id' => $user->id,
            ]);

            if (!$this->isConfigured() || !$user->hasGoogleCalendarConnected()) {
                $record->sync_status = 'pending_auth';
                $record->last_error = !$this->isConfigured()
                    ? 'Kredensial Google Calendar belum dikonfigurasi pada .env.'
                    : 'Akun Google Calendar belum dihubungkan oleh ' . $user->name . '.';
                $record->save();
                $allSynced = false;
                continue;
            }

            $accessToken = $this->ensureValidAccessToken($user);
            if (!$accessToken) {
                $record->sync_status = 'failed';
                $record->last_error = 'Token OAuth Google tidak valid atau gagal diperbarui.';
                $record->save();
                $allSynced = false;
                $hasFailure = true;
                continue;
            }

            $calendarId = urlencode($user->google_calendar_id ?: 'primary');
            if ($record->google_event_id) {
                $response = Http::withToken($accessToken)
                    ->patch("https://www.googleapis.com/calendar/v3/calendars/{$calendarId}/events/" . urlencode($record->google_event_id), $eventPayload);
            } else {
                $response = Http::withToken($accessToken)
                    ->post("https://www.googleapis.com/calendar/v3/calendars/{$calendarId}/events", $eventPayload);
            }

            if ($response->successful()) {
                $record->google_event_id = $response->json('id') ?: $record->google_event_id;
                $record->google_calendar_id = $user->google_calendar_id ?: 'primary';
                $record->sync_status = 'synced';
                $record->last_error = null;
                $record->last_synced_at = now();
                $record->save();
            } else {
                $record->sync_status = 'failed';
                $record->last_error = 'Google API Error: ' . $response->status();
                $record->save();
                $allSynced = false;
                $hasFailure = true;
            }
        }

        $overallStatus = $allSynced ? 'synced' : ($hasFailure ? 'failed' : 'pending_auth');
        $booking->update([
            'gcal_sync_status' => $overallStatus,
            'last_gcal_synced_at' => $allSynced ? now() : $booking->last_gcal_synced_at,
        ]);

        return $overallStatus;
    }

    public function deleteBookingEvents(Booking $booking): void
    {
        $events = $booking->googleCalendarEvents()->with('user')->get();
        foreach ($events as $event) {
            if ($event->google_event_id && $event->user && $event->user->hasGoogleCalendarConnected() && $this->isConfigured()) {
                $token = $this->ensureValidAccessToken($event->user);
                if ($token) {
                    $calendarId = urlencode($event->google_calendar_id ?: 'primary');
                    Http::withToken($token)->delete(
                        "https://www.googleapis.com/calendar/v3/calendars/{$calendarId}/events/" . urlencode($event->google_event_id)
                    );
                }
            }
            $event->delete();
        }
    }

    protected function ensureValidAccessToken(User $user): ?string
    {
        if ($user->google_access_token && $user->google_token_expires_at && $user->google_token_expires_at->gt(now()->addMinutes(2))) {
            return $user->google_access_token;
        }

        if (!$user->google_refresh_token) {
            return $user->google_access_token;
        }

        $resp = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'client_id' => config('services.google.client_id'),
            'client_secret' => config('services.google.client_secret'),
            'refresh_token' => $user->google_refresh_token,
            'grant_type' => 'refresh_token',
        ]);

        if (!$resp->successful()) {
            return null;
        }

        $newToken = $resp->json('access_token');
        if ($newToken) {
            $user->update([
                'google_access_token' => $newToken,
                'google_token_expires_at' => now()->addSeconds((int) ($resp->json('expires_in') ?? 3500)),
            ]);
        }

        return $newToken;
    }

    protected function buildEventPayload(Booking $booking): array
    {
        $date = Carbon::parse($booking->tanggal_dijadwalkan)->format('Y-m-d');
        $startStr = $booking->session_start ? substr($booking->session_start, 0, 5) : '09:00';
        $endStr = $booking->session_end ? substr($booking->session_end, 0, 5) : Carbon::createFromFormat('H:i', $startStr)->addHour()->format('H:i');

        $clientName = $booking->company?->name ?? $booking->client?->name ?? 'Klien UC PSC';
        $prefix = $booking->kategori === 'psikotes' ? 'Psikotes' : 'Konseling';

        return [
            'summary' => "[UC PSC] {$prefix} — {$clientName}",
            'location' => $booking->location ?: ($booking->session_type === 'online' ? 'Online' : 'UC PSC'),
            'description' => trim(implode("\n", array_filter([
                'Layanan: ' . ($booking->service_package_name ?: ucfirst($booking->kategori)),
                'Klien: ' . $clientName,
                $booking->pic_name ? 'PIC: ' . $booking->pic_name : null,
                $booking->notes ? 'Catatan: ' . $booking->notes : null,
            ]))),
            'start' => [
                'dateTime' => "{$date}T{$startStr}:00+07:00",
                'timeZone' => 'Asia/Jakarta',
            ],
            'end' => [
                'dateTime' => "{$date}T{$endStr}:00+07:00",
                'timeZone' => 'Asia/Jakarta',
            ],
        ];
    }
}
