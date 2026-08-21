<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CounselorController;
use App\Http\Controllers\CounselingRecordController;
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

    // Clients
    Route::post('clients/{client}/restore', [ClientController::class, 'restore'])->name('clients.restore')->withTrashed();
    Route::delete('clients/{client}/force-delete', [ClientController::class, 'forceDelete'])->name('clients.force-delete')->withTrashed();
    Route::resource('clients', ClientController::class);

    // Counselors
    Route::post('counselors/{counselor}/restore', [CounselorController::class, 'restore'])->name('counselors.restore')->withTrashed();
    Route::delete('counselors/{counselor}/force-delete', [CounselorController::class, 'forceDelete'])->name('counselors.force-delete')->withTrashed();
    Route::resource('counselors', CounselorController::class)->except(['show']);

    // Counseling Records
    Route::resource('counseling-records', CounselingRecordController::class)->except(['show']);

    // Test Results
    Route::resource('test-results', TestResultController::class)->except(['show', 'edit', 'update']);

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
