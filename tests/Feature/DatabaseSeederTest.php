<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\BookingCandidate;
use App\Models\ClientForm;
use App\Models\Company;
use App\Models\Holiday;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_populates_complete_prd_v2_data_idempotently(): void
    {
        $this->seed(DatabaseSeeder::class);
        // Jalankan kedua kalinya untuk memastikan seluruh seeder idempoten
        $this->seed(DatabaseSeeder::class);

        $this->assertTrue(User::where('email', 'admin@ucpsc.test')->where('role', 'admin')->exists());
        $this->assertTrue(User::where('email', 'staff@ucpsc.test')->where('role', 'staff')->exists());
        $this->assertTrue(User::where('email', 'kevin@ucpsc.test')->where('role', 'staff')->exists());
        $this->assertGreaterThanOrEqual(7, User::where('role', 'staff')->count());
        $this->assertTrue(User::where('email', 'ainun@ucpsc.test')->where('role', 'psikolog')->whereNotNull('counselor_id')->exists());

        $this->assertGreaterThanOrEqual(14, Company::count());
        $this->assertTrue(Company::where('name', 'PT AAA')->exists());
        $this->assertTrue(Company::where('name', 'PT JJJ')->exists());
        $this->assertGreaterThanOrEqual(3, Holiday::count());

        // Form psikotes publik siap dibooking (antrean Dashboard & Buat Booking Psikotes)
        $this->assertTrue(ClientForm::where('form_type', 'industri')->where('booking_status', 'belum_dibooking')->exists());
        $this->assertTrue(ClientForm::where('form_type', 'non_industri')->where('booking_status', 'belum_dibooking')->exists());

        // Booking pending (Biography WNA), Konseling Overtime, dan Psikotes KILAT + Rekap I/R tersedia
        $this->assertTrue(Booking::where('kategori', 'pending')->exists());
        $this->assertTrue(Booking::where('kategori', 'konseling')->where('status', 'perlu_bayar_overtime')->exists());
        $this->assertTrue(Booking::where('kategori', 'psikotes')->where('billing_scheme', 'R')->where('status', 'selesai')->exists());
        $this->assertTrue(Booking::where('kategori', 'psikotes')->where('billing_scheme', 'I')->where('service_tier', 'non_industri_khusus')->exists());
        $this->assertTrue(
            BookingCandidate::where('is_kilat', true)
                ->whereNotNull('client_deadline_date')
                ->whereNotNull('psychologist_deadline_date')
                ->exists()
        );
    }
}
