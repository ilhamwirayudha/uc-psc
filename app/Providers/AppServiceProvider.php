<?php

namespace App\Providers;

use App\Models\Client;
use App\Models\Booking;
use App\Models\TestResult;
use App\Policies\ClientPolicy;
use App\Policies\BookingPolicy;
use App\Policies\TestResultPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Client::class, ClientPolicy::class);
        Gate::policy(Booking::class, BookingPolicy::class);
        Gate::policy(TestResult::class, TestResultPolicy::class);

        // Bagikan data untuk modal booking ke komponen booking-modal
        \Illuminate\Support\Facades\View::composer('components.booking-modal', function ($view) {
            $view->with('clients', \App\Models\Client::orderBy('name')->get());
            $view->with('counselors', \App\Models\Counselor::where('status', 'active')->orderBy('name')->get());
            $view->with('staffs', \App\Models\User::whereIn('role', ['staff', 'admin'])->orderBy('name')->get());
        });

        // Bagikan data klien untuk modal upload hasil psikotes
        \Illuminate\Support\Facades\View::composer('components.test-result-modal', function ($view) {
            $view->with('clients', \App\Models\Client::orderBy('name')->get());
            $view->with('psikotesBookings', \App\Models\Booking::where('kategori', 'psikotes')->with(['client', 'staffPenguji', 'staffKoreksi', 'staffPelapor'])->latest()->get());
        });
    }
}
