<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Client;
use App\Models\Counselor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingServiceSeparationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Client $counselingClient;
    private Client $psychotestClient;
    private Counselor $counselor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin Booking Test',
            'email' => 'admin@booking.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->counselor = Counselor::create([
            'name' => 'Konselor Handal',
            'status' => 'active',
        ]);

        $this->counselingClient = Client::create([
            'name' => 'Klien Konseling Khusus',
            'service_type' => 'konseling',
            'jenis' => 'individu',
            'phone' => '081111111111',
            'email' => 'konseling@client.test',
            'source' => 'whatsapp',
            'status' => 'assigned',
            'created_by' => $this->admin->id,
        ]);

        $this->psychotestClient = Client::create([
            'name' => 'PT Klien Psikotes Maju',
            'service_type' => 'psikotes',
            'jenis' => 'industri',
            'phone' => '082222222222',
            'email' => 'psikotes@client.test',
            'source' => 'whatsapp',
            'status' => 'assigned',
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_counseling_page_loads_and_scopes_properly(): void
    {
        $this->actingAs($this->admin);

        $bookingKonseling = Booking::create([
            'client_id' => $this->counselingClient->id,
            'kategori' => 'konseling',
            'tanggal_booking_dibuat' => now()->toDateString(),
            'status' => 'baru',
            'counselor_id' => $this->counselor->id,
        ]);

        $bookingPsikotes = Booking::create([
            'client_id' => $this->psychotestClient->id,
            'kategori' => 'psikotes',
            'tanggal_booking_dibuat' => now()->toDateString(),
            'status' => 'baru',
        ]);

        $response = $this->get(route('bookings.counseling'));
        $response->assertStatus(200);
        $response->assertSee('Layanan Konseling');
        $this->assertCount(1, $response->viewData('counselingBookings'));
        $this->assertEquals($bookingKonseling->id, $response->viewData('counselingBookings')->first()->id);
    }

    public function test_psychotest_page_loads_and_scopes_properly(): void
    {
        $this->actingAs($this->admin);

        $bookingKonseling = Booking::create([
            'client_id' => $this->counselingClient->id,
            'kategori' => 'konseling',
            'tanggal_booking_dibuat' => now()->toDateString(),
            'status' => 'baru',
            'counselor_id' => $this->counselor->id,
        ]);

        $bookingPsikotes = Booking::create([
            'client_id' => $this->psychotestClient->id,
            'kategori' => 'psikotes',
            'tanggal_booking_dibuat' => now()->toDateString(),
            'status' => 'baru',
        ]);

        $response = $this->get(route('bookings.psychotest'));
        $response->assertStatus(200);
        $response->assertSee('Layanan Psikotes');
        $this->assertCount(1, $response->viewData('psikotesBookings'));
        $this->assertEquals($bookingPsikotes->id, $response->viewData('psikotesBookings')->first()->id);
    }

    public function test_legacy_bookings_index_route_serves_counseling(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('bookings.index'));
        $response->assertStatus(200);
        $response->assertSee('Layanan Konseling');
    }

    public function test_store_booking_redirects_to_correct_service_page(): void
    {
        $this->actingAs($this->admin);

        // Store konseling
        $responseKonseling = $this->post(route('bookings.store'), [
            'client_id' => $this->counselingClient->id,
            'kategori' => 'konseling',
            'tanggal_booking_dibuat' => now()->toDateString(),
            'status' => 'baru',
            'counselor_id' => $this->counselor->id,
        ]);
        $responseKonseling->assertRedirect(route('bookings.counseling'));

        // Store psikotes
        $responsePsikotes = $this->post(route('bookings.store'), [
            'client_id' => $this->psychotestClient->id,
            'kategori' => 'psikotes',
            'tanggal_booking_dibuat' => now()->toDateString(),
            'status' => 'baru',
        ]);
        $responsePsikotes->assertRedirect(route('bookings.psychotest'));
    }
}
