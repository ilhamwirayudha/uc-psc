<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'redirect' => url('/dashboard'),
                ]);
            }

            return redirect('/dashboard')->with('show_splash', true);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Email atau password yang Anda masukkan salah.',
                'errors' => [
                    'email' => ['Email atau password yang Anda masukkan salah.']
                ]
            ], 422);
        }

        return back()->withErrors([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'redirect' => route('login')])
                ->header('Clear-Site-Data', '"cache", "storage"')
                ->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate');
        }

        return redirect('/login')
            ->with('show_splash', true)
            ->header('Clear-Site-Data', '"cache", "storage"');
    }

    /**
     * Quick login for local development / testing.
     */
    public function quickLogin(Request $request)
    {
        if (!app()->environment('local') && !config('app.debug')) {
            abort(403, 'Akses quick login dinonaktifkan di luar lingkungan development.');
        }

        $role = $request->input('role', 'admin');
        $defaultEmail = $role === 'admin' ? 'admin@ucpsc.test' : 'staff@ucpsc.test';

        // Cari user sesuai role (utamakan email default seeder)
        $user = User::where('role', $role)
            ->orderByRaw("CASE WHEN email = ? THEN 0 ELSE 1 END", [$defaultEmail])
            ->first();

        if (!$user) {
            return back()->withErrors([
                'email' => "Akun dengan role '{$role}' tidak ditemukan dalam database.",
            ]);
        }

        Auth::login($user, false);
        $request->session()->regenerate();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'redirect' => url('/dashboard'),
            ]);
        }

        return redirect('/dashboard')->with('show_splash', true);
    }
}
