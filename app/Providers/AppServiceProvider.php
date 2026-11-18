<?php

namespace App\Providers;

use App\Models\Booking;
use App\Models\Client;
use App\Models\ClientForm;
use App\Models\PaymentTransaction;
use App\Policies\BookingPolicy;
use App\Policies\ClientPolicy;
use App\Services\ActivityLogger;
use Illuminate\Foundation\Console\ServeCommand;
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
        if ($this->app->runningInConsole() && class_exists(ServeCommand::class)) {
            ServeCommand::$passthroughVariables = array_values(array_unique(array_merge(
                ServeCommand::$passthroughVariables,
                ['TEMP', 'TMP', 'LOCALAPPDATA', 'USERPROFILE']
            )));
        }

        Gate::policy(Client::class, ClientPolicy::class);
        Gate::policy(Booking::class, BookingPolicy::class);

        // Bagikan data untuk modal booking ke komponen booking-modal
        \Illuminate\Support\Facades\View::composer('components.booking-modal', function ($view) {
            $clientQuery = \App\Models\Client::with(['clientForms' => fn($q) => $q->latest()])->latest();
            if (auth()->check() && auth()->user()->isPsikolog()) {
                $counselorId = (int) (auth()->user()->counselor_id ?? 0);
                $clientQuery->where(function ($q) use ($counselorId) {
                    $q->whereHas('bookings', fn ($bq) => $bq->where('counselor_id', $counselorId))
                      ->orWhereHas('pairings', fn ($pq) => $pq->where('counselor_id', $counselorId))
                      ->orWhereHas('bookings.candidates', fn ($cq) => $cq->where('assigned_psikolog_id', $counselorId));
                });
            }
            $view->with('clients', $clientQuery->get()->map(function ($client) {
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
            $view->with('companies', \App\Models\Company::where('is_active', true)->orderBy('name')->get());
        });

        $this->registerActivityObservers();
    }

    /**
     * Catat setiap perubahan sekecil apa pun pada entitas utama saat pengguna sedang login.
     */
    protected function registerActivityObservers(): void
    {
        Client::created(function (Client $client) {
            ActivityLogger::recordForCurrentUser(
                'klien',
                'client.created',
                'Mendaftarkan Klien Baru',
                'Klien ' . $client->name . ' ditambahkan ke sistem.',
                $client->service_type ? 'Layanan ' . ucfirst(str_replace('_', ' ', $client->service_type)) : 'Data Klien'
            );
        });

        Client::updated(function (Client $client) {
            $changed = ActivityLogger::summarizeDirtyFields($client, [
                'name' => 'Nama Lengkap',
                'phone' => 'No. Telepon',
                'email' => 'Email',
                'gender' => 'Jenis Kelamin',
                'birth_place' => 'Tempat Lahir',
                'dob' => 'Tanggal Lahir',
                'address' => 'Alamat',
                'service_type' => 'Jenis Layanan',
                'counseling_type' => 'Jenis Konseling',
                'status' => 'Status',
                'notes' => 'Catatan',
            ]);

            if ($changed === '') {
                return;
            }

            ActivityLogger::recordForCurrentUser(
                'klien',
                'client.updated',
                'Memperbarui Data Klien',
                'Data klien ' . $client->name . ' diperbarui pada bagian: ' . $changed . '.',
                $changed
            );
        });

        Client::deleted(function (Client $client) {
            ActivityLogger::recordForCurrentUser(
                'klien',
                'client.deleted',
                'Menghapus Data Klien',
                'Data klien ' . $client->name . ' dihapus dari sistem.',
                'Hapus Klien'
            );
        });

        Booking::created(function (Booking $booking) {
            $clientName = $booking->client?->name ?? 'Klien';
            $kategori = $booking->kategori === 'psikotes' ? 'Psikotes' : 'Konseling';

            ActivityLogger::recordForCurrentUser(
                'layanan',
                'booking.created',
                'Membuat Jadwal Layanan ' . $kategori,
                'Jadwal layanan ' . strtolower($kategori) . ' dibuat untuk klien ' . $clientName . '.',
                $kategori
            );
        });

        Booking::updated(function (Booking $booking) {
            $changed = ActivityLogger::summarizeDirtyFields($booking, [
                'kategori' => 'Kategori Layanan',
                'status' => 'Status Layanan',
                'tanggal_dijadwalkan' => 'Jadwal Pelaksanaan',
                'counselor_id' => 'Konselor / Psikolog',
                'notes' => 'Catatan',
                'price' => 'Tarif Dasar',
                'final_price' => 'Nominal Final',
                'billing_scheme' => 'Skema Penagihan',
                'payment_workflow_status' => 'Status Pembayaran Finance',
                'is_overtime' => 'Status Overtime',
                'overtime_payment_status' => 'Status Pembayaran Overtime',
            ]);

            if ($changed === '') {
                return;
            }

            $clientName = $booking->client?->name ?? 'Klien';
            $kategori = $booking->kategori === 'psikotes' ? 'Psikotes' : 'Konseling';

            ActivityLogger::recordForCurrentUser(
                'layanan',
                'booking.updated',
                'Memperbarui Layanan ' . $kategori,
                'Layanan ' . strtolower($kategori) . ' klien ' . $clientName . ' diperbarui pada: ' . $changed . '.',
                $changed
            );
        });

        ClientForm::updated(function (ClientForm $form) {
            $changed = ActivityLogger::summarizeDirtyFields($form, [
                'answers' => 'Isian Formulir',
                'form_type' => 'Jenis Formulir',
            ]);

            if ($changed === '') {
                return;
            }

            $clientName = $form->client?->name ?? 'Klien';
            ActivityLogger::recordForCurrentUser(
                'klien',
                'client_form.updated',
                'Memperbarui Berkas Formulir Klien',
                'Berkas formulir (' . ($form->form_type_label ?? $form->form_type) . ') milik ' . $clientName . ' diperbarui.',
                $changed
            );
        });

        PaymentTransaction::created(function (PaymentTransaction $trx) {
            ActivityLogger::recordForCurrentUser(
                'layanan',
                'payment.created',
                'Mencatat Transaksi Pembayaran',
                'Transaksi pembayaran baru sebesar Rp ' . number_format((float) ($trx->amount ?? 0), 0, ',', '.') . ' dicatat.',
                'Pembayaran'
            );
        });
    }
}
