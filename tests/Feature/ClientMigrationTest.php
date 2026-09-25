<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Client;
use App\Models\Counselor;
use App\Models\Pairing;
use App\Models\TestResult;
use App\Models\Booking;
use App\Models\BookingParticipant;
use App\Models\ClientStaffAssignment;

class ClientMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_relations_and_models(): void
    {
        $admin = User::create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->actingAs($admin);

        $client = Client::create([
            'name' => 'Client Test',
            'phone' => '08123456789',
            'email' => 'client@test.com',
            'gender' => 'l',
            'source' => 'whatsapp',
            'service_type' => 'konseling',
            'status' => 'assigned',
            'created_by' => $admin->id,
        ]);

        $counselor = Counselor::create([
            'name' => 'Counselor Test',
            'status' => 'active',
        ]);

        $pairing = Pairing::create([
            'client_id' => $client->id,
            'counselor_id' => $counselor->id,
            'assigned_by' => $admin->id,
            'status' => 'active',
        ]);


        $booking = Booking::create([
            'client_id' => $client->id,
            'kategori' => 'konseling',
            'tanggal_booking_dibuat' => now()->toDateString(),
            'status' => 'baru',
        ]);

        // 1. Check Clients index page
        $response = $this->get(route('clients.index'));
        $response->assertStatus(200);
        $response->assertSee('Client Test');

        // 2. Check Client show page
        $showResponse = $this->get(route('clients.show', $client));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('Client Test');

        // Test relations
        $this->assertEquals(1, $client->pairings()->count());
        $this->assertEquals(1, $client->bookings()->count());
        $this->assertEquals($client->id, $booking->client->id);
        $this->assertEquals($client->id, $pairing->client->id);

        // 3. Check Dashboard page
        $dashResponse = $this->get(route('dashboard'));
        $dashResponse->assertStatus(200);
        $dashResponse->assertSee('Client Test');

        // 4. Check Bookings index page
        $bookingResponse = $this->get(route('bookings.index'));
        $bookingResponse->assertStatus(200);

        // 5. Check Counseling records index page
        $recordResponse = $this->get(route('counseling-records.index'));
        $recordResponse->assertStatus(200);

        // 6. Check Assignments page
        $assignmentResponse = $this->get(route('assignments.index'));
        $assignmentResponse->assertStatus(200);
    }

    public function test_create_client_konseling_flow(): void
    {
        $admin = User::create([
            'name' => 'Admin Test 2',
            'email' => 'admin2@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $counselor = Counselor::create([
            'name' => 'Dr. Psikolog Konseling',
            'status' => 'active',
        ]);

        $this->actingAs($admin);

        // 1. Simpan Klien (Registrasi data diri saja, tanpa pemilihan service)
        $response = $this->post(route('clients.store'), [
            'name' => 'Ahmad Konseling',
            'jenis' => 'individual',
            'phone' => '081299991111',
            'email' => 'ahmad@konseling.com',
            'gender' => 'l',
            'birth_place' => 'Surabaya',
            'dob' => '2000-01-01',
            'religion' => 'Islam',
            'marital_status' => 'belum_menikah',
            'education' => 's1',
            'occupation' => 'Mahasiswa',
            'country' => 'Indonesia',
            'province' => 'Jawa Timur',
            'city' => 'Kota Surabaya',
            'address' => 'Jl. Mayjend Sungkono No. 10',
            'status' => 'unassigned',
            'notes' => 'Catatan identitas awal klien konseling.',
        ]);

        $client = Client::where('name', 'Ahmad Konseling')->first();
        $this->assertNotNull($client);
        $this->assertNull($client->service_type);
        $this->assertEquals(0, $client->bookings()->count());
        $response->assertRedirect(route('clients.create', ['jenis' => 'individual']));
        $response->assertSessionHas('success', 'Data klien berhasil ditambahkan.');

        // Klien baru yang belum memiliki booking melihat onboarding banner
        $showBeforeBooking = $this->get(route('clients.show', $client));
        $showBeforeBooking->assertStatus(200);
        $showBeforeBooking->assertSee('Klien Baru Terdaftar — Belum Memiliki Layanan');
        $showBeforeBooking->assertSee('Daftarkan Layanan / Buat Booking');

        // 2. Buat Booking Sesi untuk Klien (Mengikat layanan Konseling ke Klien)
        $bookingResponse = $this->post(route('bookings.store'), [
            'client_id' => $client->id,
            'kategori' => 'konseling',
            'tanggal_booking_dibuat' => now()->toDateString(),
            'tanggal_dijadwalkan' => now()->addDay()->toDateString(),
            'start_time' => '10:00',
            'end_time' => '11:30',
            'counselor_id' => $counselor->id,
            'session_type' => 'tatap_muka',
            'location' => 'Ruang Konseling A',
            'status' => 'baru',
        ]);
        $bookingResponse->assertRedirect(route('bookings.create'));

        $this->assertEquals(1, $client->pairings()->count());
        $this->assertEquals(1, $client->bookings()->count());

        // Verify show page renders Konseling layout & record
        $showResponse = $this->get(route('clients.show', $client));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('Konselor yang Dipasangkan');
        $showResponse->assertSee('Daftar Sesi Konseling');
    }

    public function test_create_client_konseling_group_flow(): void
    {
        $admin = User::create([
            'name' => 'Admin Test Konseling Group',
            'email' => 'admingroup@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $counselor = Counselor::create([
            'name' => 'Dr. Psikolog Keluarga & Kelompok',
            'status' => 'active',
        ]);

        $this->actingAs($admin);

        // 1. Simpan Klien Group (Data Kelompok & PIC)
        $response = $this->post(route('clients.store'), [
            'name' => 'Keluarga Bpk. Hendra Wijaya',
            'jenis' => 'group',
            'pic_name' => 'Bpk. Hendra',
            'phone' => '081233445566',
            'email' => 'hendra@keluarga.com',
            'country' => 'Indonesia',
            'province' => 'Jawa Timur',
            'city' => 'Kota Surabaya',
            'address' => 'Jl. Citraland Utama No. 8',
            'status' => 'unassigned',
            'notes' => 'Sesi konseling keluarga berkala.',
        ]);

        $client = Client::where('name', 'Keluarga Bpk. Hendra Wijaya')->first();
        $this->assertNotNull($client);
        $this->assertEquals('group', $client->jenis);

        // 2. Buat Booking Group dengan Peserta
        $bookingResponse = $this->post(route('bookings.store'), [
            'client_id' => $client->id,
            'kategori' => 'konseling',
            'tanggal_booking_dibuat' => now()->toDateString(),
            'tanggal_dijadwalkan' => now()->addDay()->toDateString(),
            'start_time' => '13:00',
            'end_time' => '15:00',
            'counselor_id' => $counselor->id,
            'session_type' => 'tatap_muka',
            'location' => 'Ruang Konseling Keluarga 1',
            'status' => 'baru',
            'participants' => [
                'Hendra Wijaya (Ayah)',
                'Ratna Dewi (Ibu)',
                'Kevin Wijaya (Anak)',
            ],
        ]);
        $bookingResponse->assertRedirect(route('bookings.create'));

        // Verify booking & participants created
        $booking = Booking::where('client_id', $client->id)->first();
        $this->assertNotNull($booking);
        $this->assertEquals('konseling', $booking->kategori);
        $this->assertEquals(3, $booking->participants()->count());

        // Verify show page renders Konseling Berkelompok layout
        $showResponse = $this->get(route('clients.show', $client));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('Hendra Wijaya (Ayah)');
    }

    public function test_create_client_psikotes_individual_flow(): void
    {
        $admin = User::create([
            'name' => 'Admin Test 4',
            'email' => 'admin4@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->actingAs($admin);

        // 1. Simpan Klien Psikotes (Tanpa service_type pada registrasi klien)
        $response = $this->post(route('clients.store'), [
            'name' => 'Budi Santoso',
            'jenis' => 'individual',
            'phone' => '081234567890',
            'email' => 'budi@psikotes.com',
            'gender' => 'l',
            'birth_place' => 'Sidoarjo',
            'dob' => '1998-05-12',
            'religion' => 'Islam',
            'marital_status' => 'belum_menikah',
            'education' => 's1',
            'occupation' => 'Karyawan Swasta',
            'country' => 'Indonesia',
            'province' => 'Jawa Timur',
            'city' => 'Kabupaten Sidoarjo',
            'address' => 'Jl. Pahlawan No. 45',
            'status' => 'unassigned',
            'notes' => 'Permintaan tes intelegensi untuk syarat pendidikan.',
        ]);

        $client = Client::where('name', 'Budi Santoso')->first();
        $this->assertNotNull($client);
        $this->assertEquals('individual', $client->jenis);

        // 2. Buat Booking Psikotes (Mengikat layanan Psikotes ke Klien)
        $bookingResponse = $this->post(route('bookings.store'), [
            'client_id' => $client->id,
            'kategori' => 'psikotes',
            'tanggal_booking_dibuat' => now()->toDateString(),
            'tanggal_dijadwalkan' => now()->addDays(2)->toDateString(),
            'status' => 'baru',
            'location' => 'Lab Psikodiagnostik R.302',
            'notes' => 'Sesi tes IQ',
        ]);
        $bookingResponse->assertRedirect(route('bookings.create'));

        // Verify booking created
        $booking = Booking::where('client_id', $client->id)->first();
        $this->assertNotNull($booking);
        $this->assertEquals('psikotes', $booking->kategori);

        // Verify show page renders Psikotes Individu layout
        $showResponse = $this->get(route('clients.show', $client));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('Hasil Tes Psikologi');
        $showResponse->assertSee('Budi Santoso');
    }

    public function test_create_client_psikotes_company_flow(): void
    {
        $admin = User::create([
            'name' => 'Admin Test 3',
            'email' => 'admin3@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->actingAs($admin);

        // 1. Simpan Klien Company (Data Perusahaan & PIC)
        $response = $this->post(route('clients.store'), [
            'name' => 'PT Ciputra Mitra Tbk',
            'jenis' => 'company',
            'pic_name' => 'Ibu Maria (HR Director)',
            'phone' => '0317654321',
            'email' => 'hrd@ciputra.com',
            'country' => 'Indonesia',
            'province' => 'Jawa Timur',
            'city' => 'Kota Surabaya',
            'address' => 'UC Tower Lt. 12, CitraLand Surabaya',
            'status' => 'unassigned',
            'notes' => 'Batch 1 asesmen calon supervisor.',
        ]);

        $client = Client::where('name', 'PT Ciputra Mitra Tbk')->first();
        $this->assertNotNull($client);
        $this->assertEquals('company', $client->jenis);
        $this->assertStringContainsString('PIC Perusahaan: Ibu Maria', $client->notes);

        // 2. Buat Booking Company dengan Peserta
        $bookingResponse = $this->post(route('bookings.store'), [
            'client_id' => $client->id,
            'kategori' => 'psikotes',
            'tanggal_booking_dibuat' => now()->toDateString(),
            'tanggal_dijadwalkan' => now()->addDays(3)->toDateString(),
            'status' => 'baru',
            'participants' => [
                'Karyawan 1 - Denny',
                'Karyawan 2 - Jessica',
                'Karyawan 3 - Michael',
            ],
            'notes' => 'Batch 1 asesmen supervisor',
        ]);
        $bookingResponse->assertRedirect(route('bookings.create'));

        // Verify booking & participants created
        $booking = Booking::where('client_id', $client->id)->first();
        $this->assertNotNull($booking);
        $this->assertEquals('psikotes', $booking->kategori);
        $this->assertEquals(3, $booking->participants()->count());

        // Verify show page renders Psikotes Perusahaan layout
        $showResponse = $this->get(route('clients.show', $client));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('Daftar Peserta / Anggota Asesmen');
        $showResponse->assertSee('Karyawan 1 - Denny');
        $showResponse->assertSee('Perusahaan');
    }

    public function test_staff_management_detail_and_assignment_management_flow(): void
    {
        $admin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin_super@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $staffA = User::create([
            'name' => 'Staff Alpha',
            'email' => 'staff_a@test.com',
            'password' => bcrypt('password'),
            'role' => 'staff',
        ]);

        $staffB = User::create([
            'name' => 'Staff Beta',
            'email' => 'staff_b@test.com',
            'password' => bcrypt('password'),
            'role' => 'staff',
        ]);

        $client = Client::create([
            'name' => 'Klien Untuk Ditugaskan',
            'service_type' => 'konseling',
            'jenis' => 'individual',
            'phone' => '081233334444',
            'email' => 'klien_tugas@test.com',
            'gender' => 'l',
            'dob' => '1995-03-20',
            'source' => 'whatsapp',
            'status' => 'unassigned',
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin);

        // 1. Visit staff detail page
        $showResponse = $this->get(route('staff-management.show', $staffA));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('Staff Alpha');

        // 2. Assign client to Staff A
        $assignResponse = $this->post(route('assignments.assign', $client), [
            'staff_id' => $staffA->id,
            'notes' => 'Penugasan pertama ke Staff A',
        ]);
        $assignResponse->assertSessionHas('success');

        $client->refresh();
        $this->assertEquals($staffA->id, $client->assigned_staff_id);

        // 3. Reassign client to Staff B
        $reassignResponse = $this->post(route('assignments.reassign', $client), [
            'staff_id' => $staffB->id,
            'notes' => 'Dialihkan ke Staff B',
        ]);
        $reassignResponse->assertSessionHas('success');

        $client->refresh();
        $this->assertEquals($staffB->id, $client->assigned_staff_id);

        // 4. Unassign client from Staff B
        $unassignResponse = $this->post(route('assignments.unassign', $client), [
            'notes' => 'Selesai pendampingan',
        ]);
        $unassignResponse->assertSessionHas('success');

        $client->refresh();
        $this->assertNull($client->assigned_staff_id);
    }

    public function test_create_client_with_assigned_staff_flow(): void
    {
        $admin = User::create([
            'name' => 'Admin Creator',
            'email' => 'creator@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $staff = User::create([
            'name' => 'Staff Handayani',
            'email' => 'handayani@test.com',
            'password' => bcrypt('password'),
            'role' => 'staff',
        ]);

        $this->actingAs($admin);

        $response = $this->post(route('clients.store'), [
            'name' => 'Klien Ditugaskan Awal',
            'service_type' => 'psikotes',
            'jenis' => 'individual',
            'phone' => '081234567890',
            'email' => 'tugas.awal@test.com',
            'gender' => 'p',
            'birth_place' => 'Surabaya',
            'dob' => '2001-04-15',
            'religion' => 'Islam',
            'marital_status' => 'belum_menikah',
            'education' => 's1',
            'country' => 'Indonesia',
            'province' => 'Jawa Timur',
            'city' => 'Kota Surabaya',
            'address' => 'Jl. Dharmahusada No. 12',
            'counseling_type' => 'Tes Kepribadian (Personality Profile)',
            'source' => 'whatsapp',
            'status' => 'unassigned',
        ]);

        $client = Client::where('name', 'Klien Ditugaskan Awal')->first();
        $this->assertNotNull($client);

        // Assign staff via assignment controller
        $this->post(route('assignments.assign', $client), [
            'staff_id' => $staff->id,
        ]);

        $client->refresh();
        $this->assertEquals($staff->id, $client->assigned_staff_id);
        $this->assertEquals(1, $client->staffAssignmentHistory()->count());
    }

    public function test_phone_number_length_validation(): void
    {
        $admin = User::create([
            'name' => 'Admin Validator',
            'email' => 'validator@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->actingAs($admin);

        // 1. Too short (< 10 digits): 8 digits -> Should fail
        $responseShort = $this->post(route('clients.store'), [
            'name' => 'Klien Short Phone',
            'service_type' => 'psikotes',
            'jenis' => 'individual',
            'phone' => '08123456',
            'email' => 'short@test.com',
            'gender' => 'l',
            'birth_place' => 'Surabaya',
            'dob' => '2000-01-01',
            'religion' => 'Islam',
            'marital_status' => 'belum_menikah',
            'education' => 's1',
            'country' => 'Indonesia',
            'province' => 'Jawa Timur',
            'city' => 'Kota Surabaya',
            'address' => 'Jl. Mayjend Sungkono No. 10',
            'counseling_type' => 'Tes IQ',
            'source' => 'whatsapp',
            'status' => 'unassigned',
        ]);
        $responseShort->assertSessionHasErrors('phone');

        // 2. Too long (> 13 digits): 14 digits -> Should fail
        $responseLong = $this->post(route('clients.store'), [
            'name' => 'Klien Long Phone',
            'service_type' => 'psikotes',
            'jenis' => 'individual',
            'phone' => '0812345678901234',
            'email' => 'long@test.com',
            'gender' => 'l',
            'birth_place' => 'Surabaya',
            'dob' => '2000-01-01',
            'religion' => 'Islam',
            'marital_status' => 'belum_menikah',
            'education' => 's1',
            'country' => 'Indonesia',
            'province' => 'Jawa Timur',
            'city' => 'Kota Surabaya',
            'address' => 'Jl. Mayjend Sungkono No. 10',
            'counseling_type' => 'Tes IQ',
            'source' => 'whatsapp',
            'status' => 'unassigned',
        ]);
        $responseLong->assertSessionHasErrors('phone');

        // 3. Valid: 12 digits -> Should pass
        $responseValid = $this->post(route('clients.store'), [
            'name' => 'Klien Valid Phone',
            'service_type' => 'psikotes',
            'jenis' => 'individual',
            'phone' => '081234567890',
            'email' => 'valid@test.com',
            'gender' => 'l',
            'birth_place' => 'Surabaya',
            'dob' => '2000-01-01',
            'religion' => 'Islam',
            'marital_status' => 'belum_menikah',
            'education' => 's1',
            'country' => 'Indonesia',
            'province' => 'Jawa Timur',
            'city' => 'Kota Surabaya',
            'address' => 'Jl. Mayjend Sungkono No. 10',
            'counseling_type' => 'Tes IQ',
            'source' => 'whatsapp',
            'status' => 'unassigned',
        ]);
        $responseValid->assertSessionHasNoErrors();
    }

    public function test_booking_scoping_and_authorization_policy(): void
    {
        $admin = User::create([
            'name' => 'Admin Policy Test',
            'email' => 'adminpolicy@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $staff1 = User::create([
            'name' => 'Staff Assigned',
            'email' => 'staff1@test.com',
            'password' => bcrypt('password'),
            'role' => 'staff',
        ]);

        $staff2 = User::create([
            'name' => 'Staff Other',
            'email' => 'staff2@test.com',
            'password' => bcrypt('password'),
            'role' => 'staff',
        ]);

        $client = Client::create([
            'name' => 'Klien Booking Test',
            'service_type' => 'konseling',
            'jenis' => 'individual',
            'phone' => '081234567890',
            'email' => 'bookingclient@test.com',
            'source' => 'whatsapp',
            'status' => 'assigned',
            'assigned_staff_id' => $staff1->id,
            'created_by' => $admin->id,
        ]);

        $booking = Booking::create([
            'client_id' => $client->id,
            'kategori' => 'konseling',
            'tanggal_booking_dibuat' => now()->toDateString(),
            'status' => 'baru',
        ]);

        // 1. Admin can view and edit
        $this->actingAs($admin);
        $this->get(route('bookings.show', $booking))->assertStatus(200);
        $this->get(route('bookings.edit', $booking))->assertStatus(200);

        // 2. Assigned staff can view and edit
        $this->actingAs($staff1);
        $this->get(route('bookings.show', $booking))->assertStatus(200);
        $this->get(route('bookings.edit', $booking))->assertStatus(200);

        // 3. Other unassigned staff is blocked with 403 Forbidden
        $this->actingAs($staff2);
        $this->get(route('bookings.show', $booking))->assertStatus(403);
        $this->get(route('bookings.edit', $booking))->assertStatus(403);
        $this->put(route('bookings.update', $booking), [
            'client_id' => $client->id,
            'kategori' => 'konseling',
            'tanggal_booking_dibuat' => now()->toDateString(),
            'status' => 'baru',
        ])->assertStatus(403);
    }

    public function test_assignment_routes_blocked_for_staff(): void
    {
        $admin = User::create([
            'name' => 'Admin Assign Test',
            'email' => 'adminassign@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $staff = User::create([
            'name' => 'Staff Hacker',
            'email' => 'staffhacker@test.com',
            'password' => bcrypt('password'),
            'role' => 'staff',
        ]);

        $client = Client::create([
            'name' => 'Klien Assignment Test',
            'service_type' => 'konseling',
            'jenis' => 'individual',
            'phone' => '081234567890',
            'email' => 'clientassign@test.com',
            'source' => 'whatsapp',
            'status' => 'unassigned',
            'created_by' => $admin->id,
        ]);

        // Acting as staff -> all assignment routes must return 403
        $this->actingAs($staff);

        $this->get(route('assignments.index'))->assertStatus(403);

        $this->post(route('assignments.assign', $client), [
            'staff_id' => $staff->id,
        ])->assertStatus(403);

        $this->post(route('assignments.reassign', $client), [
            'staff_id' => $staff->id,
        ])->assertStatus(403);

        $this->post(route('assignments.unassign', $client))->assertStatus(403);
    }

    public function test_update_client_without_source_flow(): void
    {
        $admin = User::create([
            'name' => 'Admin Update Client',
            'email' => 'adminupdate@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $client = Client::create([
            'name' => 'Klien Update Test',
            'service_type' => 'konseling',
            'jenis' => 'individual',
            'phone' => '081234567890',
            'email' => 'clientupdate@test.com',
            'country' => 'Indonesia',
            'province' => 'Jawa Timur',
            'city' => 'Kota Surabaya',
            'address' => 'Jl. Mayjend Sungkono No. 10',
            'status' => 'unassigned',
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin);

        // Edit page loads without Sumber Rujukan
        $editResponse = $this->get(route('clients.edit', $client));
        $editResponse->assertStatus(200);
        $editResponse->assertDontSee('Sumber / Rujukan');

        // Update client without source field
        $updateResponse = $this->from(route('clients.edit', $client))->put(route('clients.update', $client), [
            'name' => 'Klien Update Sukses',
            'service_type' => 'konseling',
            'phone' => '081234567890',
            'country' => 'Indonesia',
            'province' => 'Jawa Timur',
            'city' => 'Kota Surabaya',
            'address' => 'Jl. Mayjend Sungkono No. 10',
            'status' => 'ongoing',
        ]);

        $updateResponse->assertRedirect(route('clients.edit', $client));
        $updateResponse->assertSessionHas('success', 'Data klien berhasil diperbarui.');
        $client->refresh();
        $this->assertEquals('Klien Update Sukses', $client->name);
        $this->assertEquals('ongoing', $client->status);

        // Show page loads without Sumber Rujukan
        $showResponse = $this->get(route('clients.show', $client));
        $showResponse->assertStatus(200);
        $showResponse->assertDontSee('Sumber Rujukan:');
    }
}
