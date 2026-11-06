<?php

namespace App\Providers;

use App\Models\Client;
use App\Models\Booking;
use App\Policies\ClientPolicy;
use App\Policies\BookingPolicy;
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

        // Bagikan data untuk modal booking ke komponen booking-modal
        \Illuminate\Support\Facades\View::composer('components.booking-modal', function ($view) {
            $view->with('clients', \App\Models\Client::with(['clientForms' => fn($q) => $q->latest()])->latest()->get()->map(function ($client) {
                $latestForm = $client->clientForms->first();
                $client->setAttribute('latest_form_type_label', $latestForm ? $latestForm->form_type_label : null);
                $client->setAttribute('latest_form_date', $latestForm ? ($latestForm->created_at ? $latestForm->created_at->locale('id')->isoFormat('D MMMM Y, HH:mm') : null) : null);
                $client->setAttribute('latest_form_raw_type', $latestForm ? $latestForm->form_type : null);

                // Field Biodata Klien lengkap (10 field vertikal)
                $jobTitle = $client->occupation;
                if ($jobTitle && str_contains($jobTitle, ' - ')) {
                    $parts = explode(' - ', $jobTitle);
                    $jobTitle = trim($parts[0]);
                }
                $client->setAttribute('company_name_display', $client->company_name ?? ($client->pic_name ?? '-'));
                $client->setAttribute('occupation_display', $jobTitle ?: '-');
                $client->setAttribute('gender_display', $client->gender_label ?: '-');
                $formattedDob = ($client->birth_place ?? '-') . ($client->dob ? ', ' . $client->dob->locale('id')->isoFormat('D MMMM Y') : '');
                $client->setAttribute('formatted_dob', $formattedDob ?: '-');
                $client->setAttribute('education_display', $client->education_label ?? ($client->education ?? '-'));
                $client->setAttribute('religion_display', $client->religion ?? '-');
                $client->setAttribute('address_display', $client->address ?? '-');

                return $client;
            }));
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
