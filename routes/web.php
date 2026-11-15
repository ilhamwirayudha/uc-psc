<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CounselorController;
use App\Http\Controllers\AdminManagementController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\OperationalCalendarController;
use App\Http\Controllers\PublicClientFormController;
use App\Http\Controllers\ClientFormManagementController;

// Guest → login
Route::get('/', function () {
    return auth()->check() ? redirect('/dashboard') : redirect('/login');
});

// Public Client Form Registration Routes (No Auth Required)
Route::prefix('daftar')->name('public.client-form.')->group(function () {
    Route::get('/', function () {
        return redirect()->route('public.client-form.anak');
    })->name('index');

    Route::get('/anak', [PublicClientFormController::class, 'showAnak'])->name('anak');
    Route::post('/anak', [PublicClientFormController::class, 'storeAnak'])->name('anak.store');
    Route::get('/dewasa', [PublicClientFormController::class, 'showDewasa'])->name('dewasa');
    Route::post('/dewasa', [PublicClientFormController::class, 'storeDewasa'])->name('dewasa.store');
    Route::get('/pra-nikah', [PublicClientFormController::class, 'showPraNikah'])->name('pra-nikah');
    Route::post('/pra-nikah', [PublicClientFormController::class, 'storePraNikah'])->name('pra-nikah.store');
    Route::get('/pernikahan', [PublicClientFormController::class, 'showPernikahan'])->name('pernikahan');
    Route::post('/pernikahan', [PublicClientFormController::class, 'storePernikahan'])->name('pernikahan.store');
    Route::get('/biography-en', [PublicClientFormController::class, 'showBiographyEn'])->name('biography-en');
    Route::post('/biography-en', [PublicClientFormController::class, 'storeBiographyEn'])->name('biography-en.store');
    Route::get('/non-industri', [PublicClientFormController::class, 'showNonIndustri'])->name('non-industri');
    Route::post('/non-industri', [PublicClientFormController::class, 'storeNonIndustri'])->name('non-industri.store');
    Route::get('/industri', [PublicClientFormController::class, 'showIndustri'])->name('industri');
    Route::post('/industri', [PublicClientFormController::class, 'storeIndustri'])->name('industri.store');
    Route::get('/sukses/{ticket?}', [PublicClientFormController::class, 'success'])->name('success');
});


// Auth Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile (Profil Saya)
    Route::get('/profil-saya', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profil-saya', [\App\Http\Controllers\ProfileController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profil-saya/avatar', [\App\Http\Controllers\ProfileController::class, 'updateAvatar'])->name('profile.avatar.update');
    Route::delete('/profil-saya/avatar', [\App\Http\Controllers\ProfileController::class, 'deleteAvatar'])->name('profile.avatar.delete');
    Route::put('/profil-saya/email', [\App\Http\Controllers\ProfileController::class, 'updateEmail'])->name('profile.email.update');
    Route::put('/profil-saya/password', [\App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.password.update');

    // Daftar Klien
    Route::post('daftar-klien/{client}/catatan', [ClientController::class, 'storeNote'])->name('clients.notes.store');
    Route::put('daftar-klien/{client}/catatan/{noteId}', [ClientController::class, 'updateNote'])->name('clients.notes.update');
    Route::delete('daftar-klien/{client}/catatan/{noteId}', [ClientController::class, 'destroyNote'])->name('clients.notes.destroy');
    Route::resource('daftar-klien', ClientController::class)
        ->names('clients')
        ->parameters(['daftar-klien' => 'client']);

    // Psikolog
    Route::resource('psikolog', CounselorController::class)
        ->except(['show'])
        ->names('counselors')
        ->parameters(['psikolog' => 'counselor']);

    // Formulir Klien
    Route::get('formulir-klien', [ClientFormManagementController::class, 'index'])->name('client-forms.index');
    Route::get('formulir-klien/hasil', [ClientFormManagementController::class, 'results'])->name('client-forms.results');
    Route::get('formulir-klien/{clientForm}', [ClientFormManagementController::class, 'show'])->name('client-forms.show');
    Route::put('formulir-klien/{clientForm}', [ClientFormManagementController::class, 'update'])->name('client-forms.update');
    Route::get('formulir-klien/{clientForm}/pdf', [ClientFormManagementController::class, 'downloadPdf'])->name('client-forms.pdf');
    Route::post('formulir-klien/{clientForm}/jadwalkan', [BookingController::class, 'scheduleFromForm'])->name('bookings.schedule-form');

    // Layanan Konseling & Layanan Psikotes
    Route::get('layanan-konseling', [BookingController::class, 'counseling'])->name('bookings.counseling');
    Route::get('layanan-psikotes', [BookingController::class, 'psychotest'])->name('bookings.psychotest');
    Route::post('layanan/{booking}/tindak-lanjut', [BookingController::class, 'createFollowUp'])->name('bookings.follow-up');
    Route::post('layanan/{booking}/realisasi', [BookingController::class, 'storeRealisasi'])->name('bookings.realisasi');
    Route::resource('layanan', BookingController::class)
        ->names('bookings')
        ->parameters(['layanan' => 'booking']);

    // Kalender
    Route::get('kalender', [OperationalCalendarController::class, 'index'])->name('calendar.index');

    // Admin-only routes
    Route::middleware(\App\Http\Middleware\EnsureAdmin::class)->group(function () {
        Route::resource('kelola-staff', \App\Http\Controllers\StaffManagementController::class)
            ->except(['edit', 'update'])
            ->names('staff-management')
            ->parameters(['kelola-staff' => 'staff']);

        // Kelola Penugasan
        Route::get('kelola-penugasan', [AssignmentController::class, 'index'])->name('assignments.index');
        Route::post('kelola-penugasan/{client}/assign', [AssignmentController::class, 'assign'])->name('assignments.assign');
        Route::post('kelola-penugasan/{client}/unassign', [AssignmentController::class, 'unassign'])->name('assignments.unassign');
        Route::post('kelola-penugasan/{client}/reassign', [AssignmentController::class, 'reassign'])->name('assignments.reassign');
    });
});
