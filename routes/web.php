<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CounselorController;
use App\Http\Controllers\TestResultController;
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

    // Development Quick Login Route
    if (app()->environment('local') || config('app.debug')) {
        Route::match(['get', 'post'], '/dev/quick-login', [AuthController::class, 'quickLogin'])->name('dev.quick-login');
    }
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/avatar', [\App\Http\Controllers\ProfileController::class, 'updateAvatar'])->name('profile.avatar.update');
    Route::delete('/profile/avatar', [\App\Http\Controllers\ProfileController::class, 'deleteAvatar'])->name('profile.avatar.delete');
    Route::put('/profile/email', [\App\Http\Controllers\ProfileController::class, 'updateEmail'])->name('profile.email.update');
    Route::put('/profile/password', [\App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.password.update');

    // Clients
    Route::post('clients/{client}/notes', [ClientController::class, 'storeNote'])->name('clients.notes.store');
    Route::put('clients/{client}/notes/{noteId}', [ClientController::class, 'updateNote'])->name('clients.notes.update');
    Route::delete('clients/{client}/notes/{noteId}', [ClientController::class, 'destroyNote'])->name('clients.notes.destroy');
    Route::resource('clients', ClientController::class);

    // Counselors
    Route::resource('counselors', CounselorController::class)->except(['show']);

    // Data - Client Forms Management
    Route::get('client-forms', [ClientFormManagementController::class, 'index'])->name('client-forms.index');
    Route::get('client-forms/results', [ClientFormManagementController::class, 'results'])->name('client-forms.results');
    Route::get('client-forms/{clientForm}', [ClientFormManagementController::class, 'show'])->name('client-forms.show');
    Route::get('client-forms/{clientForm}/pdf', [ClientFormManagementController::class, 'downloadPdf'])->name('client-forms.pdf');
    Route::patch('client-forms/{clientForm}/status', [ClientFormManagementController::class, 'updateStatus'])->name('client-forms.status.update');


    // Test Results / Psychological Assessment Case Management
    Route::post('test-results/{testResult}/status', [TestResultController::class, 'updateStatus'])->name('test-results.status');
    Route::post('test-results/{testResult}/assign-staff', [TestResultController::class, 'assignStaff'])->name('test-results.assign-staff');
    Route::post('test-results/{testResult}/documents', [TestResultController::class, 'uploadDocument'])->name('test-results.documents.store');
    Route::get('test-results/{testResult}/download', [TestResultController::class, 'download'])->name('test-results.download');
    Route::get('test-results/{testResult}/documents/{document}/download', [TestResultController::class, 'downloadDocument'])->name('test-results.documents.download');
    Route::post('test-results/{testResult}/deliver', [TestResultController::class, 'deliver'])->name('test-results.deliver');
    Route::resource('test-results', TestResultController::class)->except(['edit', 'update']);

    // Bookings
    Route::post('bookings/{booking}/follow-up', [BookingController::class, 'createFollowUp'])->name('bookings.follow-up');
    Route::post('bookings/{booking}/realisasi', [BookingController::class, 'storeRealisasi'])->name('bookings.realisasi');
    Route::resource('bookings', BookingController::class);

    // Operasional - Kalender
    Route::get('calendar', [OperationalCalendarController::class, 'index'])->name('calendar.index');

    // Admin-only routes
    Route::middleware(\App\Http\Middleware\EnsureAdmin::class)->group(function () {
        Route::resource('staff-management', \App\Http\Controllers\StaffManagementController::class)->except(['edit', 'update']);

        // Kelola Penugasan Staff
        Route::get('assignments', [AssignmentController::class, 'index'])->name('assignments.index');
        Route::post('assignments/{client}/assign', [AssignmentController::class, 'assign'])->name('assignments.assign');
        Route::post('assignments/{client}/unassign', [AssignmentController::class, 'unassign'])->name('assignments.unassign');
        Route::post('assignments/{client}/reassign', [AssignmentController::class, 'reassign'])->name('assignments.reassign');
    });
});
