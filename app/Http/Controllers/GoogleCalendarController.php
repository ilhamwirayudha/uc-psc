<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\GoogleCalendarService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GoogleCalendarController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly GoogleCalendarService $gcal)
    {
    }

    public function redirect(Request $request)
    {
        if (!$this->gcal->isConfigured()) {
            return redirect()->route('profile.edit', ['tab' => 'pengaturan'])
                ->with('error', 'Konfigurasi Google OAuth (GOOGLE_CLIENT_ID / GOOGLE_CLIENT_SECRET) belum diisi pada .env.');
        }

        $state = Str::random(40);
        $request->session()->put('gcal_oauth_state', $state);

        return redirect()->away($this->gcal->getAuthorizationUrl($state));
    }

    public function callback(Request $request)
    {
        $expectedState = $request->session()->pull('gcal_oauth_state');
        if (!$expectedState || $request->get('state') !== $expectedState || !$request->filled('code')) {
            return redirect()->route('profile.edit', ['tab' => 'pengaturan'])
                ->with('error', 'Otorisasi Google Calendar dibatalkan atau state tidak valid.');
        }

        $connected = $this->gcal->exchangeCodeAndConnect(auth()->user(), (string) $request->get('code'));

        if (!$connected) {
            return redirect()->route('profile.edit', ['tab' => 'pengaturan'])
                ->with('error', 'Gagal menghubungkan akun Google Calendar.');
        }

        return redirect()->route('profile.edit', ['tab' => 'pengaturan'])
            ->with('success', 'Akun Google Calendar pribadi berhasil dihubungkan.');
    }

    public function disconnect()
    {
        $this->gcal->disconnect(auth()->user());

        return redirect()->route('profile.edit', ['tab' => 'pengaturan'])
            ->with('success', 'Koneksi Google Calendar pribadi telah diputuskan.');
    }

    public function syncBooking(Booking $booking)
    {
        $this->authorize('view', $booking);

        $status = $this->gcal->syncBooking($booking);

        if ($status === 'synced') {
            return back()->with('success', 'Jadwal berhasil disinkronkan ke Google Calendar seluruh petugas terkait.');
        }

        return back()->with('error', 'Sinkronisasi belum penuh: pastikan seluruh petugas terkait sudah menghubungkan Google Calendar di Profil Saya dan kredensial Google terkonfigurasi.');
    }
}
