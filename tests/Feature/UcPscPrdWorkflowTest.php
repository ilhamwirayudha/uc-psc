<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\BookingAssignee;
use App\Models\BookingCandidate;
use App\Models\Client;
use App\Models\ClientForm;
use App\Models\Company;
use App\Models\Counselor;
use App\Models\Holiday;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        $this->get(route('companies.index'))->assertStatus(403);
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

        // 3. Jalur cepat KILAT Opsi A: kirim kilat 1 klik tanpa checklist panjang
        $this->actingAs($u['psikologUser1']);
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

        // 1. Master Perusahaan & Master Libur CRUD
        $this->post(route('companies.store'), [
            'name' => 'CV XXX Uji',
            'category' => 'umum',
            'pic_name' => 'Mimi',
        ])->assertRedirect(route('companies.index'));

        $this->post(route('holidays.store'), [
            'date' => '2026-12-25',
            'name' => 'Hari Natal',
        ])->assertRedirect(route('holidays.index'));

        $this->get(route('companies.index'))->assertStatus(200)->assertSee('CV XXX Uji');
        $this->get(route('holidays.index'))->assertStatus(200)->assertSee('Hari Natal');

        // 2. Non-Industri Khusus (Tanpa Psikolog & Tester, Hardcopy membutuhkan Cetak + Resi)
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
}
