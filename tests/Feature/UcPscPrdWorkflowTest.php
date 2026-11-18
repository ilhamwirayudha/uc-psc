<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\BookingAssignee;
use App\Models\BookingCandidate;
use App\Models\BookingServiceItem;
use App\Models\Client;
use App\Models\ClientForm;
use App\Models\Company;
use App\Models\Counselor;
use App\Models\GoogleCalendarEvent;
use App\Models\Holiday;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class UcPscPrdWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function createUsers(): array
    {
        $admin = User::create([
            'name' => 'Admin UC PSC',
            'email' => 'admin@ucpsc.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $counselor1 = Counselor::create([
            'name' => 'Psikolog Sasa',
            'email' => 'sasa@ucpsc.test',
            'status' => 'active',
        ]);
        $counselor2 = Counselor::create([
            'name' => 'Psikolog Tata',
            'email' => 'tata@ucpsc.test',
            'status' => 'active',
        ]);

        $psikologUser1 = User::create([
            'name' => 'Psikolog Sasa',
            'email' => 'sasa@ucpsc.test',
            'password' => bcrypt('password'),
            'role' => 'psikolog',
            'counselor_id' => $counselor1->id,
        ]);
        $psikologUser2 = User::create([
            'name' => 'Psikolog Tata',
            'email' => 'tata@ucpsc.test',
            'password' => bcrypt('password'),
            'role' => 'psikolog',
            'counselor_id' => $counselor2->id,
        ]);

        $tester = User::create([
            'name' => 'Staff Tester Ira',
            'email' => 'ira@ucpsc.test',
            'password' => bcrypt('password'),
            'role' => 'staff',
        ]);
        $skorerManual = User::create([
            'name' => 'Staff Skorer Manual Ester',
            'email' => 'ester@ucpsc.test',
            'password' => bcrypt('password'),
            'role' => 'staff',
        ]);
        $skorerSoftware = User::create([
            'name' => 'Staff Skorer Software Putri',
            'email' => 'putri@ucpsc.test',
            'password' => bcrypt('password'),
            'role' => 'staff',
        ]);
        $editor = User::create([
            'name' => 'Staff Editor Dayu',
            'email' => 'dayu@ucpsc.test',
            'password' => bcrypt('password'),
            'role' => 'staff',
        ]);

        return compact(
            'admin',
            'counselor1',
            'counselor2',
            'psikologUser1',
            'psikologUser2',
            'tester',
            'skorerManual',
            'skorerSoftware',
            'editor'
        );
    }

    public function test_public_psychotest_forms_queue_without_auto_booking(): void
    {
        $response = $this->post(route('public.client-form.non-industri.store'), [
            'consent_agree' => 'Setuju',
            'nama_lengkap' => 'Ananda Tama',
            'jenis_kelamin' => 'Laki-Laki',
            'tempat_lahir' => 'Surabaya',
            'tanggal_lahir' => '2001-05-10',
            'urutan_kelahiran' => '1 dari 2',
            'alamat_sekarang' => 'Jl. CitraLand No. 1',
            'asal_kota' => 'Surabaya',
            'phone' => '081234567890',
            'agama' => 'Islam',
            'suku_bangsa' => 'Jawa',
            'pendidikan_terakhir' => 'S1',
            'riwayat_pendidikan_formal' => 'Universitas Ciputra',
            'kelebihan_diri' => 'Disiplin',
            'kekurangan_diri' => 'Perfeksionis',
            'cita_cita' => 'Pengusaha',
            'tujuan_asesmen' => 'Penelusuran Bakat Minat',
            'kontak_darurat' => 'Ibu - 081200001111',
        ]);

        $response->assertRedirect();
        $form = ClientForm::where('form_type', 'non_industri')->first();
        $this->assertNotNull($form);
        $this->assertNull($form->booking_id);
        $this->assertEquals('belum_dibooking', $form->booking_status);
        $this->assertEquals(0, Booking::count());

        // Konseling form tetap membuat booking otomatis
        $this->post(route('public.client-form.dewasa.store'), [
            'consent_agree' => 'Setuju',
            'nama_lengkap' => 'Bintang Ramadan',
            'jenis_kelamin' => 'Laki-Laki',
            'tempat_lahir' => 'Surabaya',
            'tanggal_lahir' => '1998-03-10',
            'urutan_kelahiran' => '1 dari 1',
            'alamat' => 'Jl. Raya Darmo',
            'phone' => '081299998888',
            'agama' => 'Islam',
            'suku_bangsa' => 'Jawa',
            'pendidikan_terakhir' => 'S1',
            'pekerjaan' => 'Karyawan',
            'hobi' => 'Membaca',
            'alasan_konseling' => 'Manajemen stres kerja',
            'kondisi_saat_ini' => 'Cukup berat',
            'pikiran_negatif' => 'Khawatir berlebih',
            'hal_ingin_ditingkatkan' => 'Regulasi emosi',
            'karakter_kepribadian' => 'Introvert',
            'ketakutan_phobia' => '-',
            'jam_tidur' => '6 jam',
            'riwayat_trauma' => '-',
            'riwayat_kesehatan' => 'Sehat',
            'relasi_ayah' => 'Baik',
            'relasi_ibu' => 'Baik',
            'relasi_saudara' => 'Baik',
            'pernah_konseling' => 'Tidak',
            'kontak_darurat' => '0811111111',
            'sumber_info' => 'Instagram',
            'preferensi_konseling' => 'Offline',
        ])->assertRedirect();

        $dewasaForm = ClientForm::where('form_type', 'dewasa')->first();
        $this->assertNotNull($dewasaForm->booking_id);
        $this->assertEquals('sudah_dibooking', $dewasaForm->booking_status);
    }

    public function test_opsi2_visibility_for_staff_and_psikolog(): void
    {
        $u = $this->createUsers();

        $handledClient = Client::create([
            'name' => 'Klien Ditangani Sasa',
            'jenis' => 'individu',
            'phone' => '081111111111',
            'status' => 'assigned',
            'created_by' => $u['admin']->id,
        ]);
        $otherClient = Client::create([
            'name' => 'Klien Lain',
            'jenis' => 'individu',
            'phone' => '082222222222',
            'status' => 'unassigned',
            'created_by' => $u['admin']->id,
        ]);

        Booking::create([
            'client_id' => $handledClient->id,
            'kategori' => 'konseling',
            'counselor_id' => $u['counselor1']->id,
            'tanggal_booking_dibuat' => now()->toDateString(),
            'status' => 'baru',
        ]);

        // Staff (Opsi 2): dapat melihat seluruh daftar klien, namun terblokir pada rute admin
        $this->actingAs($u['tester']);
        $this->get(route('clients.index'))->assertStatus(200)->assertSee('Klien Lain');
        $this->get(route('clients.show', $otherClient))->assertStatus(200);
        $this->get(route('assignments.index'))->assertStatus(403);

        // Psikolog Sasa: hanya dapat melihat klien yang ia tangani
        $this->actingAs($u['psikologUser1']);
        $this->get(route('clients.index'))
            ->assertStatus(200)
            ->assertSee('Klien Ditangani Sasa')
            ->assertDontSee('Klien Lain');
        $this->get(route('clients.show', $handledClient))->assertStatus(200);
        $this->get(route('clients.show', $otherClient))->assertStatus(403);
    }

    public function test_psychotest_creation_sla_kilat_limit_and_sequential_workflow(): void
    {
        $u = $this->createUsers();
        $this->actingAs($u['admin']);

        $company = Company::create([
            'name' => 'PT WWW Maju',
            'category' => 'kerja_sama',
            'pic_name' => 'Ibu Caca',
        ]);

        // Hari libur nasional pada Jumat 18 Sep 2026
        Holiday::create([
            'date' => '2026-09-18',
            'name' => 'Libur Khusus Uji SLA',
        ]);

        $c1 = Client::create(['name' => 'Kandidat Kilat', 'jenis' => 'industri', 'company_name' => 'PT WWW Maju', 'phone' => '081111111001']);
        $c2 = Client::create(['name' => 'Kandidat Reguler', 'jenis' => 'industri', 'company_name' => 'PT WWW Maju', 'phone' => '081111111002']);

        $f1 = ClientForm::create([
            'ticket_number' => 'PSC-IND-260915-AA01',
            'form_type' => 'industri',
            'client_id' => $c1->id,
            'client_name' => 'Kandidat Kilat',
            'client_phone' => '081111111001',
            'company_name' => 'PT WWW Maju',
            'booking_status' => 'belum_dibooking',
        ]);
        $f2 = ClientForm::create([
            'ticket_number' => 'PSC-IND-260915-AA02',
            'form_type' => 'industri',
            'client_id' => $c2->id,
            'client_name' => 'Kandidat Reguler',
            'client_phone' => '081111111002',
            'company_name' => 'PT WWW Maju',
            'booking_status' => 'belum_dibooking',
        ]);

        // 1. Cek validasi anti-bentrok KILAT (>1 kilat ke psikolog yang sama)
        $badResp = $this->post(route('psychotest-bookings.store'), [
            'mode' => 'industri',
            'company_id' => $company->id,
            'tanggal_dijadwalkan' => '2026-09-15',
            'session_type' => 'online',
            'billing_scheme' => 'v',
            'delivery_mode' => 'softcopy',
            'items' => [
                ['service_tier' => 'industri', 'service_package_key' => 'long_staf_kerja_sama', 'quantity' => 2],
            ],
            'candidates' => [
                ['client_form_id' => $f1->id, 'candidate_name' => 'Kandidat Kilat', 'service_item_index' => 0, 'is_kilat' => 1, 'assigned_psikolog_id' => $u['counselor1']->id, 'assigned_editor_id' => $u['editor']->id],
                ['client_form_id' => $f2->id, 'candidate_name' => 'Kandidat Reguler', 'service_item_index' => 0, 'is_kilat' => 1, 'assigned_psikolog_id' => $u['counselor1']->id, 'assigned_editor_id' => $u['editor']->id],
            ],
        ]);
        $badResp->assertSessionHasErrors('candidates');

        // 2. Buat booking valid (1 KILAT + 1 NON-KILAT dipegang Sasa pada Selasa 15 Sep 2026)
        $okResp = $this->post(route('psychotest-bookings.store'), [
            'mode' => 'industri',
            'company_id' => $company->id,
            'pic_name' => 'Ibu Caca',
            'tanggal_dijadwalkan' => '2026-09-15',
            'session_start' => '09:00',
            'session_end' => '12:00',
            'session_type' => 'online',
            'billing_scheme' => 'v',
            'requires_outgoing_letter' => 0,
            'delivery_mode' => 'softcopy',
            'items' => [
                ['service_tier' => 'industri', 'service_package_key' => 'long_staf_kerja_sama', 'quantity' => 1],
                ['service_tier' => 'industri', 'service_package_key' => 'long_supervisor_kerja_sama', 'quantity' => 1],
            ],
            'tester_ids' => [$u['tester']->id],
            'skorer_manual_ids' => [$u['skorerManual']->id],
            'skorer_software_ids' => [$u['skorerSoftware']->id],
            'candidates' => [
                ['client_form_id' => $f1->id, 'candidate_name' => 'Kandidat Kilat', 'target_position' => 'Staf', 'service_item_index' => 0, 'is_kilat' => 1, 'assigned_psikolog_id' => $u['counselor1']->id, 'assigned_editor_id' => $u['editor']->id],
                ['client_form_id' => $f2->id, 'candidate_name' => 'Kandidat Reguler', 'target_position' => 'Supervisor', 'service_item_index' => 1, 'is_kilat' => 0, 'assigned_psikolog_id' => $u['counselor1']->id, 'assigned_editor_id' => $u['editor']->id],
            ],
        ]);

        $booking = Booking::where('kategori', 'psikotes')->firstOrFail();
        $okResp->assertRedirect(route('psychotest-bookings.show', $booking));

        // Total harga dasar = 600.000 + 675.000 = 1.275.000
        $this->assertEquals(1275000.0, (float) $booking->price);
        $this->assertEquals('sudah_dibooking', $f1->fresh()->booking_status);
        $this->assertEquals('sudah_dibooking', $f2->fresh()->booking_status);

        $candKilat = $booking->candidates()->where('is_kilat', true)->firstOrFail();
        $candReg = $booking->candidates()->where('is_kilat', false)->firstOrFail();

        // Cek SLA Psikolog (15 Sep +1 hari = 16 Sep untuk KILAT, +2 hari = 17 Sep untuk ke-2)
        $this->assertEquals('2026-09-16', $candKilat->psychologist_deadline_date->format('Y-m-d'));
        $this->assertEquals('2026-09-17', $candReg->psychologist_deadline_date->format('Y-m-d'));

        // Cek SLA Klien (mengecualikan 18 Sep Libur & 19-20 Sep Weekend)
        // 15 Sep + 1 kerja = 16 Sep; +5 kerja = 16(1), 17(2), [18 libur, 19-20 weekend], 21(3), 22(4), 23(5)
        $this->assertEquals('2026-09-16', $candKilat->client_deadline_date->format('Y-m-d'));
        $this->assertEquals('2026-09-23', $candReg->client_deadline_date->format('Y-m-d'));

        // 3. Jalur cepat KILAT Opsi A (Q5: khusus Admin saja yang menandai KILAT terkirim)
        $this->actingAs($u['admin']);
        $this->post(route('psychotest-bookings.candidate.kilat-sent', [$booking, $candKilat]))
            ->assertSessionHas('success');
        $this->assertNotNull($candKilat->fresh()->kilat_sent_at);
        $this->assertEquals('baru', $booking->fresh()->status); // Masih berjalan sampai Non-Kilat beres

        // 4. Sequential Gate & Collective Scoring untuk Kandidat Reguler
        $this->actingAs($u['tester']);
        $this->post(route('psychotest-bookings.candidate.step', [$booking, $candReg]), ['step' => 'psikotes'])
            ->assertSessionHas('success');

        // Skorer Manual selesai, tapi Skorer Software belum => Skoring belum selesai!
        $manualAssignee = $booking->assignees()->where('role_type', 'skorer_manual')->firstOrFail();
        $softwareAssignee = $booking->assignees()->where('role_type', 'skorer_software')->firstOrFail();

        $this->actingAs($u['skorerManual']);
        $this->post(route('psychotest-bookings.assignee.toggle', [$booking, $manualAssignee]))->assertSessionHas('success');
        $this->assertNull($candReg->fresh()->step_skoring_at);

        // Skorer Software selesai => Skoring otomatis tuntas!
        $this->actingAs($u['skorerSoftware']);
        $this->post(route('psychotest-bookings.assignee.toggle', [$booking, $softwareAssignee]))->assertSessionHas('success');
        $this->assertNotNull($candReg->fresh()->step_skoring_at);

        // Editor coba lompat ke Editing sebelum Draft HPP => ditolak!
        $this->actingAs($u['editor']);
        $this->post(route('psychotest-bookings.candidate.step', [$booking, $candReg]), ['step' => 'editing'])
            ->assertSessionHas('error');
        $this->assertNull($candReg->fresh()->step_editing_at);

        // Psikolog centang Draft HPP => Editor centang Editing => Psikolog coba Approval (403 / error!)
        $this->actingAs($u['psikologUser1']);
        $this->post(route('psychotest-bookings.candidate.step', [$booking, $candReg]), ['step' => 'draft_hpp'])
            ->assertSessionHas('success');

        $this->actingAs($u['editor']);
        $this->post(route('psychotest-bookings.candidate.step', [$booking, $candReg]), ['step' => 'editing'])
            ->assertSessionHas('success');
        $this->post(route('psychotest-bookings.candidate.step', [$booking, $candReg]), ['step' => 'approval'])
            ->assertSessionHas('error');

        // Admin centang Approval (karena softcopy, Cetak dilewati otomatis)
        $this->actingAs($u['admin']);
        $this->post(route('psychotest-bookings.candidate.step', [$booking, $candReg]), ['step' => 'approval'])
            ->assertSessionHas('success');

        // Skema Reguler (v): Kirim ditolak sebelum Bayar lunas!
        $this->post(route('psychotest-bookings.candidate.step', [$booking, $candReg]), ['step' => 'kirim'])
            ->assertSessionHas('error');
        $this->assertNull($candReg->fresh()->step_kirim_at);

        // Admin input nominal final Finance & tandai Lunas -> Kirim sukses & Booking selesai!
        $this->put(route('psychotest-bookings.update-meta', $booking), [
            'section' => 'finance',
            'billing_scheme' => 'v',
            'payment_workflow_status' => 'lunas',
            'final_price' => 1415250,
            'finance_invoice_number' => 'INV-UC-2026-001',
            'va_number' => '9880011223344',
        ]);
        $this->post(route('psychotest-bookings.candidate.step', [$booking, $candReg]), ['step' => 'kirim'])
            ->assertSessionHas('success');

        $this->assertNotNull($candReg->fresh()->step_kirim_at);
        $this->assertEquals('selesai', $booking->fresh()->status);
    }

    public function test_absent_candidate_drops_psychologist_sla_quota_and_counseling_reschedule_forfeits_payment(): void
    {
        $u = $this->createUsers();
        $this->actingAs($u['admin']);

        // Absen menggugurkan deadline psikolog
        $client = Client::create(['name' => 'PT Absen', 'jenis' => 'industri', 'phone' => '081300001111']);
        $booking = Booking::create([
            'client_id' => $client->id,
            'kategori' => 'psikotes',
            'tanggal_booking_dibuat' => '2026-09-14',
            'tanggal_dijadwalkan' => '2026-09-15',
            'price' => 1200000,
            'billing_scheme' => 'R', // Rekap perusahaan boleh kirim sebelum bayar
            'delivery_mode' => 'softcopy',
            'status' => 'baru',
        ]);
        $candA = BookingCandidate::create([
            'booking_id' => $booking->id,
            'candidate_name' => 'Absen Satu',
            'assigned_psikolog_id' => $u['counselor1']->id,
            'attendance_status' => 'hadir',
        ]);
        $candB = BookingCandidate::create([
            'booking_id' => $booking->id,
            'candidate_name' => 'Hadir Dua',
            'assigned_psikolog_id' => $u['counselor1']->id,
            'attendance_status' => 'hadir',
        ]);

        $this->post(route('psychotest-bookings.candidate.attendance', [$booking, $candA]), [
            'attendance_status' => 'absen',
        ]);

        $this->assertNull($candA->fresh()->psychologist_deadline_date);
        $this->assertEquals('2026-09-16', $candB->fresh()->psychologist_deadline_date->format('Y-m-d'));
        $this->assertEquals(1200000.0, (float) $booking->fresh()->price);

        // Rekap bulanan R menampilkan booking ini
        $this->get(route('psychotest-bookings.monthly-recap', ['month' => '2026-09', 'scheme' => 'R']))
            ->assertStatus(200)
            ->assertSee('PT Absen');

        // Konseling reschedule < 24 jam => pembayaran lunas hangus & tagihan baru
        $cClient = Client::create(['name' => 'Klien Konseling Hangus', 'jenis' => 'individu', 'phone' => '081400001111']);
        $cBooking = Booking::create([
            'client_id' => $cClient->id,
            'kategori' => 'konseling',
            'counselor_id' => $u['counselor1']->id,
            'tanggal_booking_dibuat' => now()->subDays(2)->toDateString(),
            'tanggal_dijadwalkan' => now()->addHours(6)->toDateString(),
            'session_start' => now()->addHours(6)->format('H:i'),
            'session_end' => now()->addHours(7)->format('H:i'),
            'price' => 525000,
            'final_price' => 525000,
            'payment_workflow_status' => 'lunas',
            'va_number' => '9880099999',
            'paid_at' => now()->subDay(),
            'status' => 'terjadwal_gocal',
        ]);

        $this->put(route('bookings.update', $cBooking), [
            'client_id' => $cClient->id,
            'kategori' => 'konseling',
            'tanggal_dijadwalkan' => now()->addDays(3)->toDateString(),
            'start_time' => '14:00',
            'end_time' => '15:00',
            'status' => 'terjadwal_gocal',
        ]);

        $cBooking->refresh();
        $this->assertTrue($cBooking->is_payment_forfeited);
        $this->assertEquals('draft_ke_finance', $cBooking->payment_workflow_status);
        $this->assertNull($cBooking->paid_at);
        $this->assertEquals('pending_auth', $cBooking->gcal_sync_status);

        // Kelola Penugasan & Dashboard tampil tanpa error
        $this->get(route('assignments.index'))->assertStatus(200)->assertSee('Psikolog Sasa');
        $this->get(route('dashboard'))->assertStatus(200)->assertSee('Prioritas KILAT');
    }

    public function test_hardcopy_requires_cetak_and_resi_plus_non_industri_khusus_and_reassign_tracker(): void
    {
        $u = $this->createUsers();
        $this->actingAs($u['admin']);

        // 1. Non-Industri Khusus (Tanpa Psikolog & Tester, Hardcopy membutuhkan Cetak + Resi)
        $resp = $this->post(route('psychotest-bookings.store'), [
            'mode' => 'non_industri',
            'tanggal_dijadwalkan' => '2026-09-16',
            'session_type' => 'online',
            'billing_scheme' => '-', // Gratis / Deal khusus
            'requires_outgoing_letter' => 1,
            'delivery_mode' => 'hardcopy',
            'items' => [
                ['service_tier' => 'non_industri_khusus', 'service_package_key' => 'ucs_med_mmpi', 'quantity' => 1],
            ],
            'skorer_manual_ids' => [$u['skorerManual']->id],
            'candidates' => [
                ['candidate_name' => 'Peserta MMPI Khusus', 'service_item_index' => 0, 'assigned_editor_id' => $u['editor']->id],
            ],
        ]);
        $khususBooking = Booking::where('service_tier', 'non_industri_khusus')->firstOrFail();
        $resp->assertRedirect(route('psychotest-bookings.show', $khususBooking));

        $cand = $khususBooking->candidates()->firstOrFail();
        $this->assertNull($cand->assigned_psikolog_id);

        // Urutan wajib: Psikotes -> Skoring -> Surat Keluar -> Draft HPP (Admin) -> Editing -> Cetak -> Approval -> Kirim (+ Resi)
        $this->post(route('psychotest-bookings.candidate.step', [$khususBooking, $cand]), ['step' => 'psikotes'])->assertSessionHas('success');
        $manualA = $khususBooking->assignees()->where('role_type', 'skorer_manual')->firstOrFail();
        $this->actingAs($u['skorerManual']);
        $this->post(route('psychotest-bookings.assignee.toggle', [$khususBooking, $manualA]))->assertSessionHas('success');
        $this->actingAs($u['admin']);
        $this->post(route('psychotest-bookings.candidate.step', [$khususBooking, $cand]), ['step' => 'surat_keluar'])->assertSessionHas('success');
        $this->post(route('psychotest-bookings.candidate.step', [$khususBooking, $cand]), ['step' => 'draft_hpp'])->assertSessionHas('success');
        $this->actingAs($u['editor']);
        $this->post(route('psychotest-bookings.candidate.step', [$khususBooking, $cand]), ['step' => 'editing'])->assertSessionHas('success');
        $this->actingAs($u['admin']);
        // Approval sebelum Cetak ditolak untuk Hardcopy
        $this->post(route('psychotest-bookings.candidate.step', [$khususBooking, $cand]), ['step' => 'approval'])->assertSessionHas('error');
        $this->post(route('psychotest-bookings.candidate.step', [$khususBooking, $cand]), ['step' => 'cetak'])->assertSessionHas('success');
        $this->post(route('psychotest-bookings.candidate.step', [$khususBooking, $cand]), ['step' => 'approval'])->assertSessionHas('success');

        // Kirim tanpa No. Resi ditolak untuk Hardcopy
        $this->post(route('psychotest-bookings.candidate.step', [$khususBooking, $cand]), ['step' => 'kirim'])->assertSessionHas('error');
        $this->post(route('psychotest-bookings.candidate.step', [$khususBooking, $cand]), [
            'step' => 'kirim',
            'resi_date' => '2026-09-20',
            'resi_number' => 'JD01999888',
        ])->assertSessionHas('success');
        $this->assertEquals('selesai', $khususBooking->fresh()->status);

        // 3. Reassign dari Kelola Penugasan
        $b2 = Booking::create([
            'client_id' => $khususBooking->client_id,
            'kategori' => 'psikotes',
            'tanggal_booking_dibuat' => '2026-09-14',
            'tanggal_dijadwalkan' => '2026-09-18',
            'status' => 'baru',
        ]);
        $cSwap = BookingCandidate::create([
            'booking_id' => $b2->id,
            'candidate_name' => 'Kandidat Reassign',
            'assigned_psikolog_id' => $u['counselor1']->id,
        ]);
        BookingAssignee::create([
            'booking_id' => $b2->id,
            'role_type' => 'psikolog',
            'counselor_id' => $u['counselor1']->id,
        ]);

        $this->post(route('assignments.reassign-task'), [
            'task_type' => 'candidate_psikolog',
            'target_id' => $cSwap->id,
            'new_assignee_id' => $u['counselor2']->id,
        ])->assertSessionHas('success');

        $this->assertEquals($u['counselor2']->id, $cSwap->fresh()->assigned_psikolog_id);
        $this->assertEquals('2026-09-19', $cSwap->fresh()->psychologist_deadline_date->format('Y-m-d'));
    }

    public function test_industri_form_calendar_profile_gcal_and_psikolog_account_creation(): void
    {
        $u = $this->createUsers();

        // 1. Public Industri form saves to queue without auto-creating Booking
        $this->post(route('public.client-form.industri.store'), [
            'consent_agree' => 'Setuju',
            'nama_lengkap' => 'Karyawan Industri Satu',
            'nama_perusahaan' => 'PT Maju Industri',
            'jenis_kelamin' => 'Perempuan',
            'tempat_lahir' => 'Surabaya',
            'tanggal_lahir' => '1997-08-17',
            'urutan_kelahiran' => '2 dari 3',
            'alamat_sekarang' => 'Jl. Raya Industri',
            'asal_kota' => 'Surabaya',
            'phone' => '081233445566',
            'agama' => 'Islam',
            'suku_bangsa' => 'Jawa',
            'status_perkawinan' => 'Belum Menikah',
            'pendidikan_terakhir' => 'S1',
            'pekerjaan_saat_ini' => 'Staff QA',
            'posisi_dituju' => 'QA Supervisor',
            'nama_sekolah_1' => 'SMA 1',
            'kota_sekolah_1' => 'Surabaya',
            'tahun_masuk_sekolah_1' => '2012',
            'tahun_keluar_sekolah_1' => '2015',
            'keterangan_sekolah_1' => 'IPA',
            'ekspektasi_gaji_tunjangan' => 'Rp 7.000.000',
            'kelebihan_1' => 'Teliti',
            'kelebihan_2' => 'Jujur',
            'kelebihan_3' => 'Komunikatif',
            'kelemahan_1' => 'Tergesa-gesa',
            'kelemahan_2' => 'Kurang sabar',
            'kelemahan_3' => 'Grogi presentasi',
            'hobi' => 'Membaca',
            'deskripsi_diri_bebas' => 'Saya pekerja keras.',
            'pss_1' => '1', 'pss_2' => '1', 'pss_3' => '1', 'pss_4' => '2', 'pss_5' => '2',
            'pss_6' => '1', 'pss_7' => '2', 'pss_8' => '2', 'pss_9' => '1', 'pss_10' => '1',
        ])->assertRedirect();

        $indForm = ClientForm::where('form_type', 'industri')->firstOrFail();
        $this->assertNull($indForm->booking_id);
        $this->assertEquals('belum_dibooking', $indForm->booking_status);
        $this->assertEquals('PT Maju Industri', $indForm->company_name);

        // 2. Admin routes: Create Psikolog account + Create Psychotest page + Calendar + Profile GCal UI
        $this->actingAs($u['admin']);
        $this->get(route('psychotest-bookings.create'))->assertStatus(200)->assertSee('Karyawan Industri Satu');
        $this->get(route('calendar.index'))->assertStatus(200);
        $this->get(route('profile.edit', ['tab' => 'pengaturan']))->assertStatus(200)->assertSee('Hubungkan Google Calendar');

        $this->post(route('staff-management.store'), [
            'name' => 'Psikolog Baru',
            'email' => 'psikolog.baru@ucpsc.test',
            'role' => 'psikolog',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ])->assertRedirect(route('staff-management.index'));

        $newPsychUser = User::where('email', 'psikolog.baru@ucpsc.test')->firstOrFail();
        $this->assertEquals('psikolog', $newPsychUser->role);
        $this->assertNotNull($newPsychUser->counselor_id);
    }

    public function test_alignment_fixes_schedule_from_form_overtime_uncheck_auth_and_sync(): void
    {
        $u = $this->createUsers();

        // 1. scheduleFromForm pada ClientForm belum_dibooking tidak crash dan membuat Booking + Candidate
        $this->actingAs($u['admin']);
        $clientInd = Client::create([
            'name' => 'Peserta Non Industri',
            'jenis' => 'individu',
            'phone' => '081900001234',
            'address' => 'Jl. Lama No. 1',
            'education' => 'S1',
        ]);
        $unbookedForm = ClientForm::create([
            'ticket_number' => 'PSC-NIN-261118-AA01',
            'form_type' => 'non_industri',
            'client_id' => $clientInd->id,
            'booking_id' => null,
            'client_name' => 'Peserta Non Industri',
            'client_phone' => '081900001234',
            'booking_status' => 'belum_dibooking',
            'answers' => ['alamat_sekarang' => 'Jl. Lama No. 1', 'pendidikan_terakhir' => 'S1'],
        ]);

        $this->post(route('bookings.schedule-form', $unbookedForm), [
            'kategori' => 'psikotes',
            'service_tier' => 'non_industri_bakat_minat',
            'service_package_key' => 'short_umum',
            'tanggal_dijadwalkan' => '2026-11-20',
            'session_start' => '09:00',
            'session_end' => '11:00',
            'session_type' => 'offline',
            'counselor_id' => $u['counselor1']->id,
            'staff_penguji_id' => $u['tester']->id,
        ])->assertRedirect();

        $unbookedForm->refresh();
        $this->assertNotNull($unbookedForm->booking_id);
        $this->assertEquals('sudah_dibooking', $unbookedForm->booking_status);
        $this->assertEquals(1, BookingCandidate::where('client_form_id', $unbookedForm->id)->count());

        // 2. Client::syncToClientForms menyinkronkan alamat_sekarang
        $clientInd->update(['address' => 'Jl. Baru Terupdate No. 99']);
        $this->assertEquals('Jl. Baru Terupdate No. 99', $unbookedForm->fresh()->answers['alamat_sekarang']);

        // 3. Booking::createInitialForForm menggunakan status data_masuk dan toleransi overtime 90 menit
        $counselingBooking = Booking::createInitialForForm($clientInd, 'dewasa', 'offline');
        $this->assertEquals('data_masuk', $counselingBooking->status);

        \App\Models\BookingParticipant::create([
            'booking_id' => $counselingBooking->id,
            'nama_peserta' => 'Peserta Tetap Ada',
        ]);

        // Update parsial tanpa participants tidak menghapus participants; 75 menit tidak overtime
        $this->put(route('bookings.update', $counselingBooking), [
            'client_id' => $clientInd->id,
            'kategori' => 'konseling',
            'tanggal_dijadwalkan' => '2026-11-22',
            'start_time' => '10:00',
            'end_time' => '11:15',
            'status' => 'terjadwal_gocal',
        ])->assertRedirect();

        $counselingBooking->refresh();
        $this->assertFalse($counselingBooking->is_overtime);
        $this->assertEquals(0, $counselingBooking->overtime_minutes);
        $this->assertEquals(1, $counselingBooking->participants()->count());

        // 4. Sequential gate uncheck ditolak bila tahap selanjutnya sudah selesai + KILAT sent tidak overdue
        $pBooking = Booking::create([
            'client_id' => $clientInd->id,
            'kategori' => 'psikotes',
            'tanggal_booking_dibuat' => '2026-11-10',
            'tanggal_dijadwalkan' => '2026-11-11',
            'billing_scheme' => '-',
            'delivery_mode' => 'softcopy',
            'status' => 'baru',
        ]);
        $cand = BookingCandidate::create([
            'booking_id' => $pBooking->id,
            'candidate_name' => 'Kandidat Gate',
            'is_kilat' => true,
            'assigned_psikolog_id' => $u['counselor1']->id,
            'psychologist_deadline_date' => '2026-11-12',
            'step_psikotes_at' => now(),
            'step_skoring_at' => now(),
        ]);

        // Coba uncheck psikotes saat skoring sudah selesai -> gagal!
        $this->post(route('psychotest-bookings.candidate.step', [$pBooking, $cand]), ['step' => 'psikotes'])
            ->assertSessionHas('error');
        $this->assertNotNull($cand->fresh()->step_psikotes_at);

        // Tandai KILAT terkirim -> tidak lagi terhitung overdue
        $this->assertTrue($cand->isPsychologistReportOverdue());
        $this->post(route('psychotest-bookings.candidate.kilat-sent', [$pBooking, $cand]))
            ->assertSessionHas('success');
        $this->assertFalse($cand->fresh()->isPsychologistReportOverdue());

        // 5. Rekap bulanan mengecualikan status batal & sort konseling tidak error
        $batalRecap = Booking::create([
            'client_id' => $clientInd->id,
            'kategori' => 'psikotes',
            'tanggal_booking_dibuat' => '2026-11-10',
            'tanggal_dijadwalkan' => '2026-11-15',
            'billing_scheme' => 'I',
            'price' => 9876543,
            'status' => 'batal',
        ]);
        $this->get(route('psychotest-bookings.monthly-recap', ['month' => '2026-11']))
            ->assertStatus(200)
            ->assertSee('0 Order')
            ->assertDontSee('9.876.543');

        $this->get(route('bookings.counseling', ['c_sort' => 'psikolog', 'c_dir' => 'asc']))->assertStatus(200);
        $this->get(route('bookings.counseling', ['c_sort' => 'kewarganegaraan', 'c_dir' => 'desc']))->assertStatus(200);

        // 6. Validasi mismatch quantity item paket vs jumlah kandidat pada store psikotes
        $this->post(route('psychotest-bookings.store'), [
            'mode' => 'non_industri',
            'tanggal_dijadwalkan' => '2026-11-25',
            'session_type' => 'online',
            'billing_scheme' => 'v',
            'delivery_mode' => 'softcopy',
            'items' => [
                ['service_tier' => 'non_industri_bakat_minat', 'service_package_key' => 'short_umum', 'quantity' => 1],
            ],
            'candidates' => [
                ['candidate_name' => 'A', 'service_item_index' => 0],
                ['candidate_name' => 'B', 'service_item_index' => 0],
            ],
        ])->assertSessionHasErrors('items');

        // 7. Otorisasi non-admin & calendar scope: Staff melihat semua jadwal (Q3), Psikolog hanya jadwalnya sendiri
        $this->actingAs($u['tester']);
        $this->post(route('bookings.store'), ['client_id' => $clientInd->id, 'kategori' => 'konseling'])->assertStatus(403);
        $this->post(route('counselors.store'), ['name' => 'Hack', 'status' => 'active'])->assertStatus(403);
        $this->delete(route('clients.destroy', $clientInd))->assertStatus(403);
        $calResp = $this->get(route('calendar.index'))->assertStatus(200);
        $this->assertEquals(1, collect($calResp->viewData('calendarEvents'))->where('category', 'counseling')->count());

        $this->actingAs($u['psikologUser2']);
        $psychCalResp = $this->get(route('calendar.index'))->assertStatus(200);
        $this->assertEquals(0, collect($psychCalResp->viewData('calendarEvents'))->where('category', 'counseling')->count());

        $this->actingAs($u['psikologUser1']);
        $this->get(route('clients.create'))->assertStatus(403);
        $this->post(route('bookings.schedule-form', $unbookedForm), [])->assertStatus(403);
    }

    public function test_post_audit_all_13_alignment_fixes(): void
    {
        $u = $this->createUsers();
        $this->actingAs($u['admin']);

        // 1 & 2. Pembatalan batch psikotes membebaskan form, menghapus deadline SLA, memblokir langkah & toggle batch, serta destroy membersihkan relasi
        $company = Company::create(['name' => 'PT Sinar Alignment', 'status' => 'active']);
        $candClient = Client::create([
            'name' => 'Kandidat Industri Alpha',
            'jenis' => 'industri',
            'company_name' => 'PT Sinar Alignment',
            'phone' => '081200011122',
            'address' => 'Surabaya Barat',
            'education' => 'S1',
        ]);
        $indForm = ClientForm::create([
            'ticket_number' => 'PSC-IND-261118-X001',
            'form_type' => 'industri',
            'client_id' => $candClient->id,
            'company_name' => 'PT Sinar Alignment',
            'client_name' => 'Kandidat Industri Alpha',
            'client_phone' => '081200011122',
            'booking_status' => 'belum_dibooking',
            'answers' => ['nama_lengkap' => 'Kandidat Industri Alpha'],
        ]);

        // Booking batch perusahaan dengan 2 kandidat reguler di tanggal sama + 1 khusus
        $batchBooking = Booking::create([
            'client_id' => $candClient->id,
            'company_id' => $company->id,
            'pic_name' => 'HR Sinar',
            'kategori' => 'psikotes',
            'tanggal_booking_dibuat' => '2026-12-01',
            'tanggal_dijadwalkan' => '2026-12-07',
            'billing_scheme' => 'I',
            'delivery_mode' => 'both',
            'requires_outgoing_letter' => true,
            'status' => 'baru',
        ]);
        $indItem = BookingServiceItem::create([
            'booking_id' => $batchBooking->id,
            'service_tier' => 'industri',
            'service_package_key' => 'staf_kecil',
            'service_package_name' => 'Staf Kecil',
            'quantity' => 1,
            'unit_price' => 300000,
            'subtotal_price' => 300000,
        ]);
        $specialItem = BookingServiceItem::create([
            'booking_id' => $batchBooking->id,
            'service_tier' => 'non_industri_khusus',
            'service_package_key' => 'khusus',
            'service_package_name' => 'Paket Khusus',
            'quantity' => 1,
            'unit_price' => 500000,
            'subtotal_price' => 500000,
        ]);
        $batchCand = BookingCandidate::create([
            'booking_id' => $batchBooking->id,
            'booking_service_item_id' => $indItem->id,
            'client_id' => $candClient->id,
            'client_form_id' => $indForm->id,
            'candidate_name' => 'Kandidat Industri Alpha',
            'assigned_psikolog_id' => $u['counselor1']->id,
            'step_psikotes_at' => now(),
        ]);
        $specialCand = BookingCandidate::create([
            'booking_id' => $batchBooking->id,
            'booking_service_item_id' => $specialItem->id,
            'candidate_name' => 'Kandidat Paket Khusus',
            'assigned_psikolog_id' => $u['counselor1']->id,
        ]);
        $indForm->update(['booking_id' => $batchBooking->id, 'booking_status' => 'sudah_dibooking']);

        // Regular candidate di booking lain setelah batchBooking pada counselor1 tanggal 2026-12-07 (Senin)
        $otherClient = Client::create([
            'name' => 'Peserta Lain H1',
            'jenis' => 'individu',
            'phone' => '081200099988',
            'address' => 'Surabaya',
            'education' => 'S1',
        ]);
        $otherBooking = Booking::create([
            'client_id' => $otherClient->id,
            'kategori' => 'psikotes',
            'tanggal_booking_dibuat' => '2026-12-01',
            'tanggal_dijadwalkan' => '2026-12-07',
            'billing_scheme' => 'v',
            'delivery_mode' => 'softcopy',
            'status' => 'baru',
        ]);
        $otherCand = BookingCandidate::create([
            'booking_id' => $otherBooking->id,
            'candidate_name' => 'Peserta Lain H1',
            'is_kilat' => false,
            'assigned_psikolog_id' => $u['counselor1']->id,
        ]);

        $testerAssignee = BookingAssignee::create([
            'booking_id' => $batchBooking->id,
            'role_type' => 'tester',
            'user_id' => $u['tester']->id,
            'assignee_name' => $u['tester']->name,
        ]);
        $skorerAssignee = BookingAssignee::create([
            'booking_id' => $batchBooking->id,
            'role_type' => 'skorer_manual',
            'user_id' => $u['skorerManual']->id,
            'assignee_name' => $u['skorerManual']->name,
        ]);

        // Kandidat perusahaan muncul di allRelatedBookings(), profil klien, dan pencarian /layanan-psikotes
        $this->assertTrue($candClient->allRelatedBookings()->contains('id', $batchBooking->id));
        $this->get(route('clients.show', $candClient))->assertStatus(200)->assertSee('PT Sinar Alignment');
        $this->get(route('bookings.psychotest', ['q' => 'Sinar Alignment']))->assertStatus(200)->assertSee('Sinar Alignment');

        // Reassign candidate_psikolog menghitung ulang SLA psikolog lama (counselor1) maupun baru (counselor2)
        app(\App\Services\PsychotestSlaService::class)->recalculateForBooking($batchBooking);
        $this->assertEquals('2026-12-10', $otherCand->fresh()->psychologist_deadline_date?->toDateString()); // Urutan ke-3 pada counselor1 (12-07) -> H+3

        $this->post(route('assignments.reassign-task'), [
            'task_type' => 'candidate_psikolog',
            'target_id' => $batchCand->id,
            'new_assignee_id' => $u['counselor2']->id,
        ])->assertSessionHas('success');
        $this->assertEquals($u['counselor2']->id, $batchCand->fresh()->assigned_psikolog_id);
        $this->assertEquals('2026-12-09', $otherCand->fresh()->psychologist_deadline_date?->toDateString()); // Urutan ke-2 pada counselor1 -> H+2

        // Paket khusus per-item: Staff ter-assign dapat centang Psikotes, sedangkan Psikolog ditolak untuk Psikotes maupun Draft HPP
        $this->actingAs($u['psikologUser1']);
        $this->post(route('psychotest-bookings.candidate.step', [$batchBooking, $specialCand]), ['step' => 'psikotes'])
            ->assertSessionHas('error');
        $this->actingAs($u['tester']);
        $this->post(route('psychotest-bookings.candidate.step', [$batchBooking, $specialCand]), ['step' => 'psikotes'])
            ->assertSessionHas('success');
        $this->assertNotNull($specialCand->fresh()->step_psikotes_at);

        // Toggle skorer kolektif & blokir uncheck jika kandidat sudah masuk draft_hpp
        $this->actingAs($u['skorerManual']);
        $this->post(route('psychotest-bookings.assignee.toggle', [$batchBooking, $skorerAssignee]))
            ->assertSessionHas('success');
        $this->assertNotNull($batchCand->fresh()->step_skoring_at);

        // Sebelum ada downstream step, uncheck Skorer diizinkan dan mengosongkan step_skoring_at
        $this->post(route('psychotest-bookings.assignee.toggle', [$batchBooking, $skorerAssignee]))
            ->assertSessionHas('success');
        $this->assertNull($batchCand->fresh()->step_skoring_at);

        // Centang kembali Skorer, lalu tandai surat_keluar / draft_hpp -> uncheck Skorer ditolak
        $this->post(route('psychotest-bookings.assignee.toggle', [$batchBooking, $skorerAssignee]))->assertSessionHas('success');
        $this->actingAs($u['admin']);
        $this->post(route('psychotest-bookings.candidate.step', [$batchBooking, $batchCand]), ['step' => 'surat_keluar'])->assertSessionHas('success');
        $this->post(route('psychotest-bookings.candidate.step', [$batchBooking, $batchCand]), ['step' => 'draft_hpp'])->assertSessionHas('success');

        $this->actingAs($u['skorerManual']);
        $this->post(route('psychotest-bookings.assignee.toggle', [$batchBooking, $skorerAssignee]))
            ->assertSessionHas('error');

        // Mid-flight: menonaktifkan requires_outgoing_letter membersihkan step_surat_keluar_at, lalu mengaktifkannya lagi saat draft_hpp sudah lewat ditolak
        $this->actingAs($u['admin']);
        $this->put(route('psychotest-bookings.update-meta', $batchBooking), [
            'section' => 'schedule',
            'tanggal_dijadwalkan' => '2026-12-07',
            'session_type' => 'tatap_muka',
            'billing_scheme' => 'I',
            'delivery_mode' => 'both',
            'requires_outgoing_letter' => 0,
            'status' => 'baru',
        ])->assertSessionHas('success');
        $this->assertNull($batchCand->fresh()->step_surat_keluar_at);

        $this->put(route('psychotest-bookings.update-meta', $batchBooking), [
            'section' => 'schedule',
            'tanggal_dijadwalkan' => '2026-12-07',
            'session_type' => 'tatap_muka',
            'billing_scheme' => 'I',
            'delivery_mode' => 'both',
            'requires_outgoing_letter' => 1,
            'status' => 'baru',
        ])->assertSessionHas('error');

        // Pembatalan batch psikotes membersihkan deadline, membebaskan form, dan memblokir toggle selanjutnya
        $this->put(route('psychotest-bookings.update-meta', $batchBooking), [
            'section' => 'schedule',
            'tanggal_dijadwalkan' => '2026-12-07',
            'session_type' => 'tatap_muka',
            'billing_scheme' => 'I',
            'delivery_mode' => 'both',
            'requires_outgoing_letter' => 0,
            'status' => 'batal',
        ])->assertSessionHas('success');

        $this->assertEquals('batal', $batchBooking->fresh()->status);
        $this->assertNull($batchCand->fresh()->psychologist_deadline_date);
        $this->assertNull($indForm->fresh()->booking_id);
        $this->assertEquals('belum_dibooking', $indForm->fresh()->booking_status);
        $this->assertEquals('2026-12-08', $otherCand->fresh()->psychologist_deadline_date?->toDateString()); // Hanya 1 peserta tersisa di counselor1 -> kembali H+1

        $this->post(route('psychotest-bookings.candidate.kilat-sent', [$batchBooking, $batchCand]))
            ->assertSessionHas('error');
        $this->post(route('psychotest-bookings.assignee.toggle', [$batchBooking, $skorerAssignee]))
            ->assertSessionHas('error');

        // 3 & 4. GoogleCalendarService menghapus event usang saat counselor berubah / status mundur + BookingController::destroy membersihkan event & form
        config()->set('services.google.client_id', 'test-client-id');
        config()->set('services.google.client_secret', 'test-client-secret');
        Http::fake([
            'https://oauth2.googleapis.com/*' => Http::response(['access_token' => 'fake-access-token', 'expires_in' => 3600], 200),
            'https://www.googleapis.com/calendar/*' => Http::response(['id' => 'gcal-new-evt'], 200),
        ]);
        $u['psikologUser1']->update([
            'google_access_token' => 'tok1',
            'google_refresh_token' => 'ref1',
            'google_token_expires_at' => now()->addHour(),
            'google_calendar_id' => 'primary',
            'google_connected_at' => now(),
        ]);
        $u['psikologUser2']->update([
            'google_access_token' => 'tok2',
            'google_refresh_token' => 'ref2',
            'google_token_expires_at' => now()->addHour(),
            'google_calendar_id' => 'primary',
            'google_connected_at' => now(),
        ]);
        $u['admin']->update([
            'google_access_token' => 'tok-admin',
            'google_refresh_token' => 'ref-admin',
            'google_token_expires_at' => now()->addHour(),
            'google_calendar_id' => 'primary',
            'google_connected_at' => now(),
        ]);

        $counselingForm = ClientForm::create([
            'ticket_number' => 'PSC-DWS-261118-GC01',
            'form_type' => 'dewasa',
            'client_id' => $otherClient->id,
            'client_name' => 'Peserta Lain H1',
            'client_phone' => '081200099988',
            'booking_status' => 'sudah_dibooking',
            'answers' => [],
        ]);
        $gcBooking = Booking::create([
            'client_id' => $otherClient->id,
            'client_form_id' => $counselingForm->id,
            'counselor_id' => $u['counselor1']->id,
            'kategori' => 'konseling',
            'tanggal_booking_dibuat' => '2026-12-01',
            'tanggal_dijadwalkan' => '2026-12-10',
            'session_start' => '09:00',
            'session_end' => '10:00',
            'session_type' => 'offline',
            'status' => 'terjadwal_gocal',
        ]);
        $counselingForm->update(['booking_id' => $gcBooking->id]);

        app(\App\Services\GoogleCalendarService::class)->syncBooking($gcBooking);
        $this->assertEquals(2, GoogleCalendarEvent::where('booking_id', $gcBooking->id)->count());

        // Ganti psikolog -> event Google Calendar milik psikolog lama (psikologUser1) harus terhapus
        $gcBooking->update(['counselor_id' => $u['counselor2']->id]);
        app(\App\Services\GoogleCalendarService::class)->syncBooking($gcBooking);
        $this->assertEquals(0, GoogleCalendarEvent::where('booking_id', $gcBooking->id)->where('user_id', $u['psikologUser1']->id)->count());
        $this->assertEquals(1, GoogleCalendarEvent::where('booking_id', $gcBooking->id)->where('user_id', $u['psikologUser2']->id)->count());

        // Mundurkan status konseling ke waiting_list -> seluruh event GCal harus terhapus
        $gcBooking->update(['status' => 'waiting_list']);
        app(\App\Services\GoogleCalendarService::class)->syncBooking($gcBooking);
        $this->assertEquals(0, GoogleCalendarEvent::where('booking_id', $gcBooking->id)->count());

        // Sinkronisasi ulang lalu test follow-up, realisasi & destroy()
        $gcBooking->update(['status' => 'terjadwal_gocal']);
        app(\App\Services\GoogleCalendarService::class)->syncBooking($gcBooking);
        $this->assertGreaterThan(0, GoogleCalendarEvent::where('booking_id', $gcBooking->id)->count());

        // Test follow-up & realisasi membuat status resmi PRD 5.1 (waiting_list / menunggu_konfirmasi, bukan 'lanjutan')
        $this->post(route('bookings.realisasi', $gcBooking), [
            'session_start' => '09:00',
            'session_end' => '10:00',
            'session_duration_standard' => 60,
            'needs_followup' => 1,
        ])->assertRedirect();
        $realisasiFollowUp = Booking::where('follow_up_of_booking_id', $gcBooking->id)->firstOrFail();
        $this->assertEquals('waiting_list', $realisasiFollowUp->status);

        $this->post(route('bookings.follow-up', $gcBooking), [
            'tanggal_dijadwalkan' => '2026-12-18',
        ])->assertRedirect();
        $scheduledFollowUp = Booking::where('follow_up_of_booking_id', $gcBooking->id)
            ->where('id', '!=', $realisasiFollowUp->id)
            ->firstOrFail();
        $this->assertEquals('menunggu_konfirmasi', $scheduledFollowUp->status);
        $this->assertEquals('perlu_sesi_lanjutan', $gcBooking->fresh()->status);

        // Update slot rencana (status 'terjadwal_gocal') berdurasi panjang tidak memicu false overtime
        $scheduledFollowUp->update(['status' => 'terjadwal_gocal']);
        $this->put(route('bookings.update', $scheduledFollowUp), [
            'client_id' => $otherClient->id,
            'kategori' => 'konseling',
            'tanggal_dijadwalkan' => '2026-12-18',
            'start_time' => '09:00',
            'end_time' => '11:30',
            'status' => 'terjadwal_gocal',
        ])->assertRedirect();
        $this->assertFalse($scheduledFollowUp->fresh()->is_overtime);

        // Hapus $gcBooking via destroy() -> redirect konseling, form kembali belum_dibooking, GoogleCalendarEvent terhapus
        $this->delete(route('bookings.destroy', $gcBooking))
            ->assertRedirect(route('bookings.counseling'));
        $this->assertEquals(0, GoogleCalendarEvent::where('booking_id', $gcBooking->id)->count());
        $this->assertNull($counselingForm->fresh()->booking_id);
        $this->assertEquals('belum_dibooking', $counselingForm->fresh()->booking_status);

        // 7 & 12. Phone collision & trailing '& ' fix & biography_en publik
        $this->post(route('clients.store'), [
            'form_type' => 'pra_nikah',
            'consent_agree' => 'Setuju',
            'nama_lengkap' => 'Calon Mempelai Satu',
            'jenis_kelamin' => 'Perempuan',
            'tempat_lahir' => 'Surabaya',
            'tanggal_lahir' => '1999-03-10',
            'phone' => '081377776666',
            'email' => 'calon@ucpsc.test',
            'agama' => 'Islam',
            'pendidikan_terakhir' => 'S1',
            'pekerjaan' => 'Desainer',
            'alamat' => 'Surabaya',
            'nama_pasangan' => '',
            'preferensi_konseling' => 'Offline',
        ])->assertRedirect();

        $this->post(route('public.client-form.biography-en.store'), [
            'consent_agree' => 'Agree',
            'full_name' => 'Second Person Same Phone',
            'gender' => 'Male',
            'birth_place_date' => 'Jakarta, 10 May 1998',
            'birth_order' => '1st of 2',
            'current_address' => 'Surabaya Timur',
            'phone' => '081377776666',
            'email' => 'second@ucpsc.test',
            'religion' => 'Islam',
            'ethnicity' => 'Javanese',
            'last_education' => "Bachelor's Degree (S1)",
            'school_history' => 'Universitas Ciputra',
            'strengths_weaknesses' => 'Organized and analytical',
            'dream_job' => 'Product Lead',
            'emergency_contact' => 'Parent 08120000000',
            'preferred_counseling' => 'Online via Zoom',
        ])->assertRedirect();

        $this->assertEquals(1, Client::where('phone', '081377776666')->where('name', 'Calon Mempelai Satu')->count());
        $this->assertEquals(1, Client::where('phone', '081377776666')->where('name', 'Second Person Same Phone')->count());
        $praNikahForm = ClientForm::where('form_type', 'pra_nikah')->where('client_phone', '081377776666')->firstOrFail();
        $this->assertEquals('Calon Mempelai Satu', $praNikahForm->client_name);

        // 9. RBAC Psikolog tidak dapat akses clients.edit/update + label profil akurat
        $this->actingAs($u['psikologUser1']);
        $this->get(route('clients.edit', $otherClient))->assertStatus(403);
        $this->put(route('clients.update', $otherClient), ['phone' => '081200099988', 'email' => 'x@ucpsc.test'])->assertStatus(403);
        $this->get(route('profile.edit'))->assertStatus(200)->assertSee('Psikolog');

        $this->actingAs($u['tester']);
        $this->get(route('profile.edit'))->assertStatus(200)->assertSee('Staff Layanan');

        // 10. BookingController::store() psikotes KILAT capacity & company/software fields
        $this->actingAs($u['admin']);
        $kilat1Booking = Booking::create([
            'client_id' => $otherClient->id,
            'kategori' => 'psikotes',
            'tanggal_booking_dibuat' => '2026-12-01',
            'tanggal_dijadwalkan' => '2026-12-22',
            'status' => 'baru',
        ]);
        BookingCandidate::create([
            'booking_id' => $kilat1Booking->id,
            'candidate_name' => 'Kilat Existing',
            'is_kilat' => true,
            'assigned_psikolog_id' => $u['counselor1']->id,
        ]);

        $this->post(route('bookings.store'), [
            'client_id' => $otherClient->id,
            'kategori' => 'psikotes',
            'tanggal_dijadwalkan' => '2026-12-22',
            'session_type' => 'tatap_muka',
            'counselor_id' => $u['counselor1']->id,
            'is_kilat' => 1,
            'status' => 'baru',
        ])->assertSessionHasErrors('counselor_id');

        // 11. Validasi jumlah kandidat per-item paket campuran pada PsychotestBookingController::store()
        $this->post(route('psychotest-bookings.store'), [
            'mode' => 'non_industri',
            'tanggal_dijadwalkan' => '2026-12-23',
            'session_type' => 'tatap_muka',
            'billing_scheme' => 'v',
            'delivery_mode' => 'softcopy',
            'items' => [
                ['service_tier' => 'non_industri_bakat_minat', 'service_package_key' => 'short_umum', 'quantity' => 1],
                ['service_tier' => 'non_industri_khusus', 'service_package_key' => 'khusus', 'quantity' => 1],
            ],
            'candidates' => [
                ['candidate_name' => 'Mix A', 'service_item_index' => 0],
                ['candidate_name' => 'Mix B', 'service_item_index' => 0],
            ],
        ])->assertSessionHasErrors('items');

        // 13. Command app:update-client-status mengecek booking aktif PRD (bukan hanya 'baru'/'lanjutan')
        $pastClient = Client::create([
            'name' => 'Klien Telat Auto Ongoing',
            'jenis' => 'individu',
            'phone' => '081355554444',
            'address' => 'Surabaya',
            'education' => 'S1',
            'status' => 'assigned',
        ]);
        $pastBooking = Booking::create([
            'client_id' => $pastClient->id,
            'kategori' => 'konseling',
            'tanggal_booking_dibuat' => '2026-10-01',
            'tanggal_dijadwalkan' => now()->subDays(3)->toDateString(),
            'status' => 'menunggu_konfirmasi',
        ]);
        $this->artisan('app:update-client-status')->assertExitCode(0);
        $this->assertEquals('ongoing', $pastClient->fresh()->status);
    }

    public function test_post_audit_round2_all_8_alignment_fixes(): void
    {
        $u = $this->createUsers();
        $this->actingAs($u['admin']);

        // Setup perusahaan + lead client perusahaan + kandidat B terpisah di batch
        $company = Company::create(['name' => 'PT Round Dua Corp', 'status' => 'active']);
        $leadClient = Client::create([
            'name' => 'PT Round Dua Corp',
            'jenis' => 'industri',
            'company_name' => 'PT Round Dua Corp',
            'phone' => '081111112222',
            'address' => 'Surabaya',
            'education' => 'S1',
        ]);
        $candBClient = Client::create([
            'name' => 'Kandidat Batch Beta',
            'jenis' => 'industri',
            'company_name' => 'PT Round Dua Corp',
            'phone' => '081111113333',
            'address' => 'Sidoarjo',
            'education' => 'S1',
        ]);
        $candBForm = ClientForm::create([
            'ticket_number' => 'PSC-IND-261118-R2B1',
            'form_type' => 'industri',
            'client_id' => $candBClient->id,
            'company_name' => 'PT Round Dua Corp',
            'client_name' => 'Kandidat Batch Beta',
            'client_phone' => '081111113333',
            'booking_status' => 'sudah_dibooking',
            'answers' => ['nama_lengkap' => 'Kandidat Batch Beta'],
        ]);

        $batch = Booking::create([
            'client_id' => $leadClient->id,
            'company_id' => $company->id,
            'pic_name' => 'Ibu Rina PIC Unik',
            'kategori' => 'psikotes',
            'tanggal_booking_dibuat' => '2026-12-01',
            'tanggal_dijadwalkan' => '2026-12-28',
            'billing_scheme' => 'R',
            'payment_workflow_status' => 'menunggu_bayar',
            'service_package_name' => 'Mix Paket',
            'status' => 'baru',
        ]);
        $candBForm->update(['booking_id' => $batch->id]);

        $indItem = BookingServiceItem::create([
            'booking_id' => $batch->id,
            'service_tier' => 'industri',
            'service_package_key' => 'short_staf_umum',
            'service_package_name' => 'Industri Short Staf',
            'quantity' => 1,
            'unit_price' => 575000,
            'subtotal_price' => 575000,
        ]);
        $specialItem = BookingServiceItem::create([
            'booking_id' => $batch->id,
            'service_tier' => 'non_industri_khusus',
            'service_package_key' => 'ucs_med_mmpi',
            'service_package_name' => 'UCS-MED MMPI',
            'quantity' => 1,
            'unit_price' => 50000,
            'subtotal_price' => 50000,
        ]);

        $candB = BookingCandidate::create([
            'booking_id' => $batch->id,
            'booking_service_item_id' => $indItem->id,
            'client_id' => $candBClient->id,
            'client_form_id' => $candBForm->id,
            'candidate_name' => 'Kandidat Batch Beta',
            'assigned_psikolog_id' => $u['counselor1']->id,
        ]);
        $specialCand = BookingCandidate::create([
            'booking_id' => $batch->id,
            'booking_service_item_id' => $specialItem->id,
            'candidate_name' => 'Peserta Khusus Tanpa Psikolog',
        ]);

        BookingAssignee::create([
            'booking_id' => $batch->id,
            'role_type' => 'psikolog',
            'counselor_id' => $u['counselor1']->id,
        ]);
        BookingAssignee::create([
            'booking_id' => $batch->id,
            'role_type' => 'skorer_manual',
            'user_id' => $u['skorerManual']->id,
        ]);

        // Fix 3: Pencarian /layanan-psikotes & /layanan berdasarkan candidate_name maupun pic_name
        $this->get(route('bookings.psychotest', ['search' => 'Kandidat Batch Beta']))
            ->assertStatus(200)
            ->assertSee('PT Round Dua Corp');
        $this->get(route('bookings.psychotest', ['search' => 'Rina PIC Unik']))
            ->assertStatus(200)
            ->assertSee('PT Round Dua Corp');
        $this->get(route('bookings.index', ['search' => 'Kandidat Batch Beta']))
            ->assertStatus(200)
            ->assertSee('PT Round Dua Corp');

        // Fix 6: Rekap bulanan menampilkan progres 'Menunggu VA' + Fix 8: mixed package menampilkan 'Khusus (Admin)'
        $this->get(route('psychotest-bookings.monthly-recap', ['month' => '2026-12']))
            ->assertStatus(200)
            ->assertSee('Menunggu VA');
        $this->get(route('psychotest-bookings.show', $batch))
            ->assertStatus(200)
            ->assertSee('Khusus (Admin)');

        // Fix 1 & 7: Psikolog melihat Kandidat Batch Beta di /daftar-klien & /formulir-klien/hasil, dan dashboard tidak menampilkan kartu unbooked forms
        $this->actingAs($u['psikologUser1']);
        $this->get(route('clients.index'))->assertStatus(200)->assertSee('Kandidat Batch Beta');
        $this->get(route('client-forms.results', ['category' => 'industri']))
            ->assertStatus(200)
            ->assertSee('Kandidat Batch Beta');
        $this->get(route('dashboard'))
            ->assertStatus(200)
            ->assertDontSee('Formulir Belum Dibooking');

        // Fix 2: Psikolog tidak boleh mengubah kehadiran kandidat non_industri_khusus; Staff yang di-assign boleh
        $this->post(route('psychotest-bookings.candidate.attendance', [$batch, $specialCand]), [
            'attendance_status' => 'hadir',
        ])->assertStatus(403);

        $this->actingAs($u['skorerManual']);
        $this->post(route('psychotest-bookings.candidate.attendance', [$batch, $specialCand]), [
            'attendance_status' => 'hadir',
        ])->assertSessionHas('success');
        $this->assertEquals('hadir', $specialCand->fresh()->attendance_status);

        // Fix 4: scheduleFromForm() untuk psikotes menjaga counseling_type tetap kosong & menyetel service_type = 'psikotes'
        $this->actingAs($u['admin']);
        $unbookedClient = Client::create([
            'name' => 'Klien Jadwal Psikotes',
            'jenis' => 'individu',
            'phone' => '081111114444',
            'address' => 'Surabaya',
            'education' => 'S1',
            'status' => 'unassigned',
        ]);
        $unbookedNin = ClientForm::create([
            'ticket_number' => 'PSC-NIN-261118-R201',
            'form_type' => 'non_industri',
            'client_id' => $unbookedClient->id,
            'client_name' => 'Klien Jadwal Psikotes',
            'client_phone' => '081111114444',
            'booking_status' => 'belum_dibooking',
            'answers' => [],
        ]);

        $this->post(route('bookings.schedule-form', $unbookedNin), [
            'kategori' => 'psikotes',
            'service_tier' => 'non_industri_bakat_minat',
            'service_package_key' => 'short_umum',
            'tanggal_dijadwalkan' => '2026-12-29',
            'session_start' => '09:00',
            'session_end' => '11:30',
            'session_type' => 'offline',
            'staff_penguji_id' => $u['tester']->id,
        ])->assertRedirect();

        $unbookedClient->refresh();
        $this->assertEquals('psikotes', $unbookedClient->service_type);
        $this->assertNull($unbookedClient->counseling_type);
        $this->assertEquals('assigned', $unbookedClient->status);

        // Fix 5: Accessor is_overtime tetap false untuk jadwal perencanaan (>90 menit), tapi true untuk sesi konseling selesai (>90 menit)
        $plannedCounseling = Booking::create([
            'client_id' => $unbookedClient->id,
            'kategori' => 'konseling',
            'tanggal_booking_dibuat' => '2026-12-01',
            'tanggal_dijadwalkan' => '2026-12-30',
            'session_start' => '09:00',
            'session_end' => '11:30',
            'status' => 'terjadwal_gocal',
        ]);
        $this->assertFalse($plannedCounseling->is_overtime);

        $realizedCounseling = Booking::create([
            'client_id' => $unbookedClient->id,
            'kategori' => 'konseling',
            'tanggal_booking_dibuat' => '2026-12-01',
            'tanggal_dijadwalkan' => '2026-12-30',
            'session_start' => '13:00',
            'session_end' => '15:00',
            'status' => 'selesai',
        ]);
        $this->assertTrue($realizedCounseling->is_overtime);
        $this->get(route('bookings.show', $plannedCounseling))
            ->assertStatus(200)
            ->assertSee('isRealizedOvertimeEligible');
    }

    public function test_post_audit_round3_edge_case_and_housekeeping_fixes(): void
    {
        $u = $this->createUsers();
        $this->actingAs($u['admin']);

        // 1. Parameter ?month= tidak valid pada monthly-recap fallback aman ke bulan berjalan (tidak 500 error)
        $this->get(route('psychotest-bookings.monthly-recap', ['month' => 'invalid-99']))
            ->assertStatus(200);
        $this->get(route('psychotest-bookings.monthly-recap', ['month' => '2026-13']))
            ->assertStatus(200);

        // 2. Menghapus akun Psikolog saat masih punya tugas aktif ditolak; setelah selesai, Counselor otomatis menjadi inactive
        $client = Client::create([
            'name' => 'Klien Guard Psikolog',
            'jenis' => 'individu',
            'phone' => '081500001122',
            'address' => 'Surabaya',
            'education' => 'S1',
        ]);
        $activeBooking = Booking::create([
            'client_id' => $client->id,
            'counselor_id' => $u['counselor2']->id,
            'kategori' => 'konseling',
            'tanggal_booking_dibuat' => '2026-12-01',
            'status' => 'terjadwal_gocal',
        ]);

        $this->delete(route('staff-management.destroy', $u['psikologUser2']))
            ->assertRedirect(route('staff-management.index'))
            ->assertSessionHas('error');
        $this->assertNotNull(User::find($u['psikologUser2']->id));
        $this->assertEquals('active', $u['counselor2']->fresh()->status);

        // Tandai sesi selesai -> penghapusan berhasil dan Counselor terkait menjadi inactive (riwayat tetap aman)
        $activeBooking->update(['status' => 'selesai']);
        $this->delete(route('staff-management.destroy', $u['psikologUser2']))
            ->assertRedirect(route('staff-management.index'))
            ->assertSessionHas('success');
        $this->assertNull(User::find($u['psikologUser2']->id));
        $this->assertEquals('inactive', $u['counselor2']->fresh()->status);
        $this->assertEquals($u['counselor2']->id, $activeBooking->fresh()->counselor_id);
    }

    public function test_comprehensive_code_review_and_business_alignment_fixes(): void
    {
        $u = $this->createUsers();

        // 1. Public form tidak menimpa status aktif klien lama atau menghapus email yang sudah tersimpan
        $existingClient = Client::create([
            'name' => 'Budi Santoso',
            'jenis' => 'individu',
            'phone' => '081900001122',
            'email' => 'budi.lama@ucpsc.test',
            'address' => 'Surabaya',
            'education' => 'S1',
            'status' => 'ongoing',
        ]);

        $this->post(route('public.client-form.non-industri.store'), [
            'consent_agree' => 'Setuju',
            'nama_lengkap' => 'Budi Santoso',
            'jenis_kelamin' => 'Laki-Laki',
            'tempat_lahir' => 'Surabaya',
            'tanggal_lahir' => '1995-05-10',
            'urutan_kelahiran' => '1 dari 2',
            'alamat_sekarang' => 'Jl. Dharmahusada No. 10',
            'asal_kota' => 'Surabaya',
            'phone' => '081900001122',
            'agama' => 'Islam',
            'suku_bangsa' => 'Jawa',
            'pendidikan_terakhir' => 'S1',
            'riwayat_pendidikan_formal' => 'Universitas Airlangga',
            'kelebihan_diri' => 'Disiplin',
            'kekurangan_diri' => 'Perfeksionis',
            'cita_cita' => 'Psikolog',
            'tujuan_asesmen' => 'Evaluasi Karir',
            'kontak_darurat' => 'Ibu Budi 0812222333',
        ])->assertRedirect();

        $existingClient->refresh();
        $this->assertEquals('ongoing', $existingClient->status);
        $this->assertEquals('budi.lama@ucpsc.test', $existingClient->email);

        $queuedNonIndustriForm = ClientForm::where('client_id', $existingClient->id)
            ->where('form_type', 'non_industri')
            ->firstOrFail();

        // 2. Biography EN (pending) tampil di Dashboard dan bisa diklasifikasikan ke Psikotes via BookingController::update
        $this->post(route('public.client-form.biography-en.store'), [
            'consent_agree' => '1',
            'full_name' => 'John Expat',
            'gender' => 'Male',
            'birth_place_date' => 'London, 1992-03-14',
            'birth_order' => '1st of 2',
            'current_address' => 'CitraLand Surabaya',
            'phone' => '081900002233',
            'email' => 'john.expat@example.com',
            'religion' => 'Christian',
            'ethnicity' => 'British',
            'last_education' => 'S1',
            'school_history' => 'University of London',
            'strengths_weaknesses' => 'Analytical / Impatient',
            'dream_job' => 'Product Lead',
            'emergency_contact' => 'Jane 081900002244',
            'preferred_counseling' => 'Offline at UC PSC',
        ])->assertRedirect();

        $pendingBioBooking = Booking::where('kategori', 'pending')->firstOrFail();

        $this->actingAs($u['admin']);

        // Search Daftar Klien (tanpa kolom city yang di-drop), edit klien (tanpa country/province/city), & akses non-AJAX client-forms.show
        $this->get(route('clients.index', ['search' => 'Surabaya']))->assertStatus(200)->assertSee('Budi Santoso');
        $this->put(route('clients.update', $existingClient), [
            'name' => 'Budi Santoso Updated',
            'phone' => '081900001122',
            'email' => 'budi.baru@ucpsc.test',
            'address' => 'Jl. Dharmahusada Indah 99, Surabaya 60115',
        ])->assertRedirect();
        $this->assertEquals('Jl. Dharmahusada Indah 99, Surabaya, 60115', $existingClient->fresh()->full_address);

        $this->get(route('client-forms.show', $queuedNonIndustriForm))
            ->assertStatus(200)
            ->assertSee($queuedNonIndustriForm->ticket_number);

        $this->actingAs($u['psikologUser1'])
            ->get(route('client-forms.show', $queuedNonIndustriForm))
            ->assertStatus(403);

        $this->actingAs($u['admin'])
            ->get(route('dashboard'))
            ->assertStatus(200)
            ->assertSee('John Expat')
            ->assertSee('Pending Klasifikasi');

        // Klasifikasi booking pending ke psikotes membuat kandidat otomatis dan mengarahkan ke halaman detail psikotes
        $classifyResp = $this->put(route('bookings.update', $pendingBioBooking), [
            'client_id' => $pendingBioBooking->client_id,
            'kategori' => 'psikotes',
            'status' => 'data_masuk',
            'submitted_card' => 'step-1',
        ]);
        $classifyResp->assertRedirect(route('psychotest-bookings.show', $pendingBioBooking));
        $pendingBioBooking->refresh();
        $this->assertEquals('psikotes', $pendingBioBooking->kategori);
        $this->assertEquals('baru', $pendingBioBooking->status);
        $this->assertEquals(1, $pendingBioBooking->candidates()->count());

        // 3. Batch campuran KILAT + Reguler tidak boleh jadi 'selesai' jika kandidat KILAT belum dikirim
        $company = Company::create(['name' => 'PT Kilat Campuran', 'category' => 'kerja_sama', 'pic_name' => 'HRD']);
        $this->post(route('psychotest-bookings.store'), [
            'mode' => 'industri',
            'company_id' => $company->id,
            'tanggal_dijadwalkan' => '2026-10-12',
            'session_type' => 'online',
            'billing_scheme' => '-',
            'delivery_mode' => 'softcopy',
            'skorer_manual_ids' => [$u['skorerManual']->id],
            'items' => [
                ['service_tier' => 'industri', 'service_package_key' => 'short_staf_kerja_sama', 'quantity' => 2],
            ],
            'candidates' => [
                [
                    'client_form_id' => $queuedNonIndustriForm->id,
                    'candidate_name' => 'Kandidat Kilat',
                    'service_item_index' => 0,
                    'is_kilat' => 1,
                    'assigned_psikolog_id' => $u['counselor1']->id,
                    'assigned_editor_id' => $u['editor']->id,
                ],
                [
                    'candidate_name' => 'Kandidat Reguler',
                    'service_item_index' => 0,
                    'is_kilat' => 0,
                    'assigned_psikolog_id' => $u['counselor2']->id,
                    'assigned_editor_id' => $u['editor']->id,
                ],
            ],
        ])->assertRedirect();

        $mixBooking = Booking::where('company_id', $company->id)->firstOrFail();

        // Double submit client_form_id yang sama harus ditolak validasi
        $this->post(route('psychotest-bookings.store'), [
            'mode' => 'industri',
            'company_id' => $company->id,
            'tanggal_dijadwalkan' => '2026-10-15',
            'session_type' => 'online',
            'billing_scheme' => 'v',
            'delivery_mode' => 'softcopy',
            'items' => [
                ['service_tier' => 'industri', 'service_package_key' => 'short_staf_kerja_sama', 'quantity' => 1],
            ],
            'candidates' => [
                ['client_form_id' => $queuedNonIndustriForm->id, 'candidate_name' => 'Duplikat', 'service_item_index' => 0],
            ],
        ])->assertSessionHasErrors('candidates');

        $kilatCand = $mixBooking->candidates()->where('is_kilat', true)->firstOrFail();
        $regCand = $mixBooking->candidates()->where('is_kilat', false)->firstOrFail();

        // Selesaikan kandidat reguler sampai kirim penuh; status booking harus tetap 'baru' karena kandidat KILAT belum dikirim
        $this->post(route('psychotest-bookings.candidate.step', [$mixBooking, $regCand]), ['step' => 'psikotes'])
            ->assertSessionHas('success');
        $mixScorer = $mixBooking->assignees()->where('role_type', 'skorer_manual')->firstOrFail();
        $this->actingAs($u['skorerManual'])
            ->post(route('psychotest-bookings.assignee.toggle', [$mixBooking, $mixScorer]))
            ->assertSessionHas('success');
        $this->actingAs($u['admin']);
        foreach (['draft_hpp', 'editing', 'approval', 'kirim'] as $step) {
            $this->post(route('psychotest-bookings.candidate.step', [$mixBooking, $regCand]), ['step' => $step])
                ->assertSessionHas('success');
        }
        $this->assertEquals('baru', $mixBooking->fresh()->status);

        // Setelah kandidat KILAT dikirim, status batch baru otomatis menjadi 'selesai'
        $this->post(route('psychotest-bookings.candidate.kilat-sent', [$mixBooking, $kilatCand]))
            ->assertSessionHas('success');
        $this->assertEquals('selesai', $mixBooking->fresh()->status);

        // 4. Penambahan skorer batch mereset step_skoring_at jika kandidat belum lanjut downstream, & pelepasan psikolog mengosongkan deadline SLA
        $scoringBooking = Booking::create([
            'client_id' => $existingClient->id,
            'kategori' => 'psikotes',
            'service_tier' => 'industri',
            'tanggal_booking_dibuat' => '2026-10-01',
            'tanggal_dijadwalkan' => '2026-10-14',
            'billing_scheme' => 'v',
            'delivery_mode' => 'softcopy',
            'status' => 'baru',
        ]);
        $skorerAssignee = BookingAssignee::create([
            'booking_id' => $scoringBooking->id,
            'role_type' => 'skorer_manual',
            'user_id' => $u['skorerManual']->id,
        ]);
        $scCand = BookingCandidate::create([
            'booking_id' => $scoringBooking->id,
            'candidate_name' => 'Peserta Resync',
            'assigned_psikolog_id' => $u['counselor1']->id,
            'attendance_status' => 'hadir',
            'step_psikotes_at' => now(),
            'psychologist_deadline_date' => '2026-10-15',
        ]);
        $this->actingAs($u['skorerManual'])
            ->post(route('psychotest-bookings.assignee.toggle', [$scoringBooking, $skorerAssignee]))
            ->assertSessionHas('success');
        $this->assertNotNull($scCand->fresh()->step_skoring_at);

        // Admin menambah Skorer Software baru + melepaskan psikolog kandidat -> step_skoring_at & psychologist_deadline_date otomatis null
        $this->actingAs($u['admin'])
            ->put(route('psychotest-bookings.update-meta', $scoringBooking), [
                'section' => 'assignees',
                'skorer_manual_ids' => [$u['skorerManual']->id],
                'skorer_software_ids' => [$u['skorerSoftware']->id],
                'candidate_psikolog' => [$scCand->id => ''],
                'candidate_editor' => [$scCand->id => ''],
            ])->assertSessionHas('success');

        $scCand->refresh();
        $this->assertNull($scCand->step_skoring_at);
        $this->assertNull($scCand->psychologist_deadline_date);

        // Guard hapus Staff aktif pada StaffManagementController::destroy
        $this->delete(route('staff-management.destroy', $u['skorerSoftware']))
            ->assertRedirect(route('staff-management.index'))
            ->assertSessionHas('error');
        $this->assertNotNull(User::find($u['skorerSoftware']->id));

        // Guard hapus Psikolog aktif pada CounselorController::destroy & sinkronisasi profil dua arah
        $activeCounseling = Booking::create([
            'client_id' => $existingClient->id,
            'counselor_id' => $u['counselor1']->id,
            'kategori' => 'konseling',
            'tanggal_booking_dibuat' => '2026-10-01',
            'tanggal_dijadwalkan' => now()->subDays(2)->toDateString(),
            'session_start' => '10:00',
            'session_end' => '11:00',
            'price' => 275000,
            'final_price' => 275000,
            'payment_workflow_status' => 'lunas',
            'paid_at' => now()->subDays(3),
            'status' => 'selesai',
        ]);
        // Update sesi konseling yang SUDAH SELESAI tidak boleh menghanguskan pembayaran lunas
        $this->put(route('bookings.update', $activeCounseling), [
            'client_id' => $existingClient->id,
            'kategori' => 'konseling',
            'tanggal_dijadwalkan' => $activeCounseling->tanggal_dijadwalkan->format('Y-m-d'),
            'start_time' => '10:15',
            'end_time' => '11:15',
            'status' => 'selesai',
        ]);
        $this->assertFalse($activeCounseling->fresh()->is_payment_forfeited);

        // Quick Booking Psikotes skema '-' langsung lunas
        $this->post(route('bookings.store'), [
            'client_id' => $existingClient->id,
            'kategori' => 'psikotes',
            'billing_scheme' => '-',
            'delivery_mode' => 'softcopy',
        ])->assertRedirect();
        $quickFreeBooking = Booking::where('client_id', $existingClient->id)->where('billing_scheme', '-')->latest('id')->firstOrFail();
        $this->assertEquals('lunas', $quickFreeBooking->payment_workflow_status);
        $this->assertNotNull($quickFreeBooking->paid_at);

        // Sinkronisasi dua arah profil User Psikolog <-> Counselor & guard hapus Counselor
        $this->actingAs($u['psikologUser1'])
            ->put(route('profile.update'), [
                'name' => 'Psikolog Sasa Synced',
                'email' => 'sasa.synced@ucpsc.test',
                'phone' => '081333444555',
            ])->assertRedirect(route('profile.edit'));
        $this->assertEquals('Psikolog Sasa Synced', $u['counselor1']->fresh()->name);
        $this->assertEquals('081333444555', $u['counselor1']->fresh()->phone);

        // Buat tugas aktif untuk counselor1 agar delete ditolak
        Booking::create([
            'client_id' => $existingClient->id,
            'counselor_id' => $u['counselor1']->id,
            'kategori' => 'konseling',
            'tanggal_booking_dibuat' => '2026-10-01',
            'status' => 'terjadwal_gocal',
        ]);
        $this->actingAs($u['admin'])
            ->delete(route('counselors.destroy', $u['counselor1']))
            ->assertRedirect(route('counselors.index'))
            ->assertSessionHas('error');
        $this->assertNotNull(Counselor::find($u['counselor1']->id));

        // Sinkron SLA saat Holiday ditambah/dihapus di database
        // $mixBooking tanggal 2026-10-12 (Senin); 5 hari kerja normal = 2026-10-19
        $regCand->refresh();
        $this->assertEquals('2026-10-19', $regCand->client_deadline_date->format('Y-m-d'));

        // Tambah Holiday 2026-10-14 -> client_deadline_date otomatis bergeser menjadi 2026-10-20
        Holiday::create([
            'date' => '2026-10-14',
            'name' => 'Hari Libur Uji SLA',
        ]);
        $this->assertEquals('2026-10-20', $regCand->fresh()->client_deadline_date->format('Y-m-d'));
    }

    public function test_stakeholder_refinements_staff_visibility_kilat_admin_both_delivery_reschedule_overtime_and_company_backfill(): void
    {
        $u = $this->createUsers();
        $this->actingAs($u['admin']);

        // Q3 & Poin B: Staff melihat semua jadwal konseling & psikotes (termasuk yang tidak di-assign kepadanya) + backfill company_name
        $company = Company::create(['name' => 'PT Surat Pengantar Jaya', 'category' => 'kerja_sama', 'pic_name' => 'Ibu Lina']);
        $counselingClient = Client::create(['name' => 'Klien Konseling Global', 'jenis' => 'individu', 'phone' => '081500001111']);
        $counselingOther = Booking::create([
            'client_id' => $counselingClient->id,
            'counselor_id' => $u['counselor2']->id,
            'kategori' => 'konseling',
            'tanggal_booking_dibuat' => now()->toDateString(),
            'tanggal_dijadwalkan' => now()->addDay()->toDateString(),
            'session_start' => '10:00',
            'session_end' => '11:00',
            'status' => 'terjadwal_gocal',
            'payment_workflow_status' => 'menunggu_bayar',
        ]);

        $indClient = Client::create(['name' => 'Peserta Backfill Company', 'jenis' => 'industri', 'phone' => '081500002222']);
        $indForm = ClientForm::create([
            'ticket_number' => 'PSC-IND-261118-SP01',
            'form_type' => 'industri',
            'client_id' => $indClient->id,
            'client_name' => 'Peserta Backfill Company',
            'client_phone' => '081500002222',
            'company_name' => null,
            'booking_status' => 'belum_dibooking',
            'answers' => [],
        ]);

        // Q9: delivery_mode = 'both' + Poin B company_name backfill
        $this->post(route('psychotest-bookings.store'), [
            'mode' => 'industri',
            'company_id' => $company->id,
            'tanggal_dijadwalkan' => '2026-11-20',
            'session_type' => 'tatap_muka',
            'billing_scheme' => 'R',
            'delivery_mode' => 'both',
            'items' => [
                ['service_tier' => 'industri', 'service_package_key' => 'long_staf_kerja_sama', 'quantity' => 1],
            ],
            'candidates' => [
                [
                    'client_form_id' => $indForm->id,
                    'candidate_name' => 'Peserta Backfill Company',
                    'service_item_index' => 0,
                    'is_kilat' => 1,
                    'assigned_psikolog_id' => $u['counselor2']->id,
                ],
            ],
        ])->assertRedirect();

        $this->assertEquals('PT Surat Pengantar Jaya', $indForm->fresh()->company_name);
        $this->assertEquals('PT Surat Pengantar Jaya', $indClient->fresh()->company_name);

        $bothBooking = Booking::where('company_id', $company->id)->firstOrFail();
        $bothCand = $bothBooking->candidates()->firstOrFail();

        // Q3: Staff ($u['tester'] yang tidak di-assign pada booking manapun) melihat semua jadwal & kalender & pengingat H-1
        $this->actingAs($u['tester']);
        $this->get(route('bookings.counseling'))->assertStatus(200)->assertSee('Klien Konseling Global');
        $this->get(route('bookings.psychotest'))->assertStatus(200)->assertSee('Peserta Backfill Company');
        $this->get(route('bookings.index'))->assertStatus(200)->assertSee('Klien Konseling Global');
        $this->get(route('bookings.show', $counselingOther))->assertStatus(200);
        $this->get(route('psychotest-bookings.show', $bothBooking))->assertStatus(200)->assertDontSee('Tandai Kilat Terkirim');
        $this->get(route('calendar.index'))->assertStatus(200)->assertSee('Klien Konseling Global')->assertSee('PT Surat Pengantar Jaya');
        $this->get(route('dashboard'))->assertStatus(200)->assertSee('Klien Konseling Global')->assertDontSee('Tandai Kilat Terkirim');

        // Psikolog 1 tetap terblokir pada jadwal Psikolog 2
        $this->actingAs($u['psikologUser1']);
        $this->get(route('bookings.show', $counselingOther))->assertStatus(403);

        // Q5: Non-Admin ditolak menandai KILAT terkirim & Softcopy terkirim
        $this->actingAs($u['tester'])
            ->post(route('psychotest-bookings.candidate.kilat-sent', [$bothBooking, $bothCand]))
            ->assertStatus(403);
        $this->actingAs($u['psikologUser2'])
            ->post(route('psychotest-bookings.candidate.kilat-sent', [$bothBooking, $bothCand]))
            ->assertStatus(403);
        $this->actingAs($u['tester'])
            ->post(route('psychotest-bookings.candidate.softcopy-sent', [$bothBooking, $bothCand]))
            ->assertStatus(403);
        $this->assertNull($bothCand->fresh()->kilat_sent_at);

        // Q9: Order 'both' -> centang Softcopy terpisah setelah Editing, status batch tetap 'baru' sampai Kirim Hardcopy + Resi
        $this->actingAs($u['admin']);
        foreach (['psikotes', 'skoring', 'draft_hpp', 'editing', 'cetak', 'approval'] as $step) {
            $this->post(route('psychotest-bookings.candidate.step', [$bothBooking, $bothCand]), ['step' => $step])
                ->assertSessionHas('success');
        }
        $this->post(route('psychotest-bookings.candidate.softcopy-sent', [$bothBooking, $bothCand]))
            ->assertSessionHas('success');
        $bothCand->refresh();
        $this->assertNotNull($bothCand->softcopy_sent_at);
        $this->assertNull($bothCand->step_kirim_at);
        $this->assertEquals('baru', $bothBooking->fresh()->status);

        $this->post(route('psychotest-bookings.candidate.step', [$bothBooking, $bothCand]), [
            'step' => 'kirim',
            'resi_date' => '2026-11-23',
            'resi_number' => 'INV-RESI-001',
        ])->assertSessionHas('success');
        $bothCand->refresh();
        $this->assertNotNull($bothCand->step_kirim_at);
        $this->assertEquals('2026-11-23', $bothCand->resi_date->format('Y-m-d'));
        $this->assertEquals('selesai', $bothBooking->fresh()->status);

        // Q11: Reschedule < 24 jam oleh pihak Psikolog tidak menghanguskan pembayaran
        $psychReschedBooking = Booking::create([
            'client_id' => $counselingClient->id,
            'counselor_id' => $u['counselor1']->id,
            'kategori' => 'konseling',
            'tanggal_booking_dibuat' => now()->subDays(2)->toDateString(),
            'tanggal_dijadwalkan' => now()->addHours(4)->toDateString(),
            'session_start' => now()->addHours(4)->format('H:i'),
            'session_end' => now()->addHours(5)->format('H:i'),
            'price' => 450000,
            'final_price' => 450000,
            'payment_workflow_status' => 'lunas',
            'va_number' => '9880055555',
            'paid_at' => now()->subDay(),
            'status' => 'terjadwal_gocal',
        ]);

        $this->put(route('bookings.update', $psychReschedBooking), [
            'client_id' => $counselingClient->id,
            'kategori' => 'konseling',
            'tanggal_dijadwalkan' => now()->addDays(4)->toDateString(),
            'start_time' => '15:00',
            'end_time' => '16:00',
            'rescheduled_by' => 'psychologist',
            'status' => 'terjadwal_gocal',
        ])->assertRedirect();

        $psychReschedBooking->refresh();
        $this->assertFalse($psychReschedBooking->is_payment_forfeited);
        $this->assertEquals('lunas', $psychReschedBooking->payment_workflow_status);
        $this->assertNotNull($psychReschedBooking->paid_at);
        $this->assertEquals('psychologist', $psychReschedBooking->rescheduled_by);

        // Q12: Overtime kelipatan Opsi A (09:00-11:30 = 150 mnt => 2x tarif overtime)
        $this->assertEquals(0, Booking::calculateOvertimeUnits(90, 60));
        $this->assertEquals(1, Booking::calculateOvertimeUnits(91, 60));
        $this->assertEquals(1, Booking::calculateOvertimeUnits(120, 60));
        $this->assertEquals(2, Booking::calculateOvertimeUnits(121, 60));
        $this->assertEquals(2, Booking::calculateOvertimeUnits(150, 60));
        $this->assertEquals(2, Booking::calculateOvertimeUnits(180, 60));
        $this->assertEquals(3, Booking::calculateOvertimeUnits(181, 60));

        $otBooking = Booking::create([
            'client_id' => $counselingClient->id,
            'counselor_id' => $u['counselor1']->id,
            'kategori' => 'konseling',
            'service_package_name' => 'Konseling Pro - Offline - WNI - Umum',
            'price' => 525000,
            'overtime_price' => 525000,
            'tanggal_booking_dibuat' => '2026-11-18',
            'tanggal_dijadwalkan' => '2026-11-20',
            'status' => 'terjadwal_gocal',
        ]);

        $this->post(route('bookings.realisasi', $otBooking), [
            'session_start' => '09:00',
            'session_end' => '11:30',
            'session_duration_standard' => 60,
        ])->assertRedirect();

        $otBooking->refresh();
        $this->assertTrue($otBooking->is_overtime);
        $this->assertEquals(150, $otBooking->actual_duration_minutes);
        $this->assertEquals(90, $otBooking->overtime_minutes);
        $this->assertEquals(2, $otBooking->overtime_units);
        $this->assertEquals(1050000.0, $otBooking->overtime_total_amount);
    }
}
