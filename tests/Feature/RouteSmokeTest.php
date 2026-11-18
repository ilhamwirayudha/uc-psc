<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Client;
use App\Models\ClientForm;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RouteSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_load_all_internal_pages_with_seeded_prd_data(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('role', 'admin')->firstOrFail();
        $staff = User::where('role', 'staff')->firstOrFail();
        $psikolog = User::where('role', 'psikolog')->firstOrFail();
        $client = Client::firstOrFail();
        $clientForm = ClientForm::firstOrFail();
        $counselingBooking = Booking::where('kategori', 'konseling')->firstOrFail();
        $psychotestBooking = Booking::where('kategori', 'psikotes')->firstOrFail();

        $routesToCheck = [
            route('dashboard'),
            route('profile.edit'),
            route('clients.index'),
            route('clients.create'),
            route('clients.show', $client),
            route('clients.edit', $client),
            route('counselors.index'),
            route('counselors.create'),
            route('client-forms.index'),
            route('client-forms.results'),
            route('client-forms.show', $clientForm),
            route('bookings.counseling'),
            route('bookings.psychotest'),
            route('psychotest-bookings.create'),
            route('psychotest-bookings.monthly-recap'),
            route('psychotest-bookings.show', $psychotestBooking),
            route('bookings.index'),
            route('bookings.show', $counselingBooking),
            route('calendar.index'),
            route('staff-management.index'),
            route('staff-management.index', ['role' => 'staff']),
            route('staff-management.index', ['role' => 'psikolog']),
            route('staff-management.create'),
            route('staff-management.show', $staff),
            route('staff-management.show', $psikolog),
            route('assignments.index'),
        ];

        $this->actingAs($admin);
        foreach ($routesToCheck as $url) {
            $response = $this->get($url);
            $response->assertStatus(200);
        }
    }

    public function test_staff_and_psikolog_can_load_scoped_pages(): void
    {
        $this->seed(DatabaseSeeder::class);

        $staff = User::where('email', 'staff@ucpsc.test')->firstOrFail();
        $psikolog = User::where('email', 'ainun@ucpsc.test')->firstOrFail();

        $this->actingAs($staff);
        foreach ([route('dashboard'), route('bookings.counseling'), route('bookings.psychotest'), route('bookings.index'), route('calendar.index'), route('counselors.index'), route('profile.edit')] as $url) {
            $this->get($url)->assertStatus(200);
        }

        $this->actingAs($psikolog);
        foreach ([route('dashboard'), route('bookings.counseling'), route('bookings.psychotest'), route('calendar.index'), route('counselors.index'), route('profile.edit')] as $url) {
            $this->get($url)->assertStatus(200);
        }
    }
}
