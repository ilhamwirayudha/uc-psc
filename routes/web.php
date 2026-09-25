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

// Guest → login
Route::get('/', function () {
    return auth()->check() ? redirect('/dashboard') : redirect('/login');
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

    // Profile
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/avatar', [\App\Http\Controllers\ProfileController::class, 'updateAvatar'])->name('profile.avatar.update');
    Route::delete('/profile/avatar', [\App\Http\Controllers\ProfileController::class, 'deleteAvatar'])->name('profile.avatar.delete');
    Route::put('/profile/email', [\App\Http\Controllers\ProfileController::class, 'updateEmail'])->name('profile.email.update');
    Route::put('/profile/password', [\App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.password.update');

    // Clients
    Route::resource('clients', ClientController::class);

    // Counselors
    Route::resource('counselors', CounselorController::class)->except(['show']);


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
    Route::resource('bookings', BookingController::class);

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
