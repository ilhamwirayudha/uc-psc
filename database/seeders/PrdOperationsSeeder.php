<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\BookingAssignee;
use App\Models\BookingCandidate;
use App\Models\BookingParticipant;
use App\Models\BookingServiceItem;
use App\Models\Client;
use App\Models\ClientForm;
use App\Models\Company;
use App\Models\Counselor;
use App\Models\Holiday;
use App\Models\User;
use App\Services\PsychotestSlaService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class PrdOperationsSeeder extends Seeder
{
    public function run(): void
    {
        $slaService = app(PsychotestSlaService::class);

        // 1. Master Perusahaan (Kerja Sama & Umum)
        $mitraDigital = Company::firstOrCreate(
            ['name' => 'PT Mitra Integrasi Digital'],
            [
                'category' => 'kerja_sama',
                'pic_name' => 'Ibu Clara Wijaya',
                'pic_phone' => '081234500101',
                'pic_email' => 'hrd@mitraintegrasi.co.id',
                'address' => 'Jl. Mayjend Sungkono No. 88, Dukuh Pakis, Surabaya',
                'notes' => 'Perusahaan mitra MOU tahunan psikotes rekrutmen IT & Supervisor.',
                'is_active' => true,
            ]
        );

        $suryaLogistik = Company::firstOrCreate(
            ['name' => 'PT Surya Prima Logistik'],
            [
                'category' => 'kerja_sama',
                'pic_name' => 'Bpk. Hendra Kusuma',
                'pic_phone' => '081234500102',
                'pic_email' => 'talent@suryalogi.co.id',
                'address' => 'Kompleks Pergudangan Margomulyo Blok B-14, Surabaya',
                'notes' => 'Skema penagihan Rekap Perusahaan (R) akhir bulan dengan pengiriman hardcopy.',
                'is_active' => true,
            ]
        );

        Company::firstOrCreate(
            ['name' => 'CV Karya Mandiri Sejahtera'],
            [
                'category' => 'umum',
                'pic_name' => 'Ibu Nadia Hartono',
                'pic_phone' => '081234500103',
                'pic_email' => 'rekrutmen@karyamandiri.id',
                'address' => 'Jl. Raya Darmo Permai Selatan No. 21, Surabaya',
                'notes' => 'Perusahaan kategori tarif Umum.',
                'is_active' => true,
            ]
        );

        $ucsInternal = Company::firstOrCreate(
            ['name' => 'Universitas Ciputra Internal (UCS)'],
            [
                'category' => 'kerja_sama',
                'pic_name' => 'Unit Layanan Internal UC',
                'pic_phone' => '081234500104',
                'pic_email' => 'ucs@ciputra.ac.id',
                'address' => 'CitraLand CBD Boulevard, Made, Sambikerep, Surabaya',
                'notes' => 'Order internal Fakultas/Mahasiswa UC dengan skema Rekap Internal UCS (I).',
                'is_active' => true,
            ]
        );

        // 2. Hari Libur Nasional (untuk kalkulasi hari kerja SLA Psikotes)
        $holidays = [
            ['date' => '2026-12-25', 'name' => 'Hari Raya Natal', 'notes' => 'Libur Nasional'],
            ['date' => '2027-01-01', 'name' => 'Tahun Baru 2027 Masehi', 'notes' => 'Libur Nasional'],
            ['date' => '2027-03-29', 'name' => 'Hari Suci Nyepi Tahun Baru Saka 1949', 'notes' => 'Libur Nasional'],
        ];
        foreach ($holidays as $holiday) {
            if (!Holiday::whereDate('date', $holiday['date'])->exists()) {
                Holiday::create($holiday);
            }
        }

        // 3. Sinkronkan booking awal untuk form konseling & biography_en dari OfficialFormsSeeder
        $officialForms = ClientForm::with('client')
            ->whereIn('form_type', ['dewasa', 'anak', 'pra_nikah', 'pernikahan', 'biography_en'])
            ->whereNull('booking_id')
            ->get();

        foreach ($officialForms as $form) {
            if ($form->client) {
                $booking = Booking::createInitialForForm(
                    $form->client,
                    $form->form_type,
                    $form->service_preference,
                    $form->id
                );
                $form->update([
                    'booking_id' => $booking->id,
                    'booking_status' => 'sudah_dibooking',
                ]);
            }
        }

        // Hindari duplikasi jika seeder dipanggil ulang
        if (Booking::where('kategori', 'psikotes')->where('company_id', $mitraDigital->id)->exists()) {
            return;
        }

        $admin = User::where('role', 'admin')->first();
        $tester = User::where('email', 'staff@ucpsc.test')->first() ?? User::where('role', 'staff')->first();
        $skorerManual = User::where('email', 'ester@ucpsc.test')->first() ?? $tester;
        $skorerSoftware = User::where('email', 'putri@ucpsc.test')->first() ?? $tester;
        $editor = User::where('email', 'dayu@ucpsc.test')->first() ?? $tester;

        $ainun = Counselor::where('email', 'ainun@ucpsc.test')->first() ?? Counselor::where('status', 'active')->first();
        $budi = Counselor::where('email', 'budi@ucpsc.test')->first() ?? $ainun;
        $dian = Counselor::where('email', 'dian@ucpsc.test')->first() ?? $ainun;

        // Helper pilih tanggal hari kerja stabil
        $recentWorkday = Carbon::now()->subDays(2);
        while ($recentWorkday->isWeekend()) {
            $recentWorkday->subDay();
        }
        $upcomingWorkday = Carbon::now()->addDays(2);
        while ($upcomingWorkday->isWeekend()) {
            $upcomingWorkday->addDay();
        }

        // 4. Batch Psikotes A: Industri Multi-Paket + Peserta KILAT (Ongoing)
        $clientMitra = Client::firstOrCreate(
            ['email' => 'hrd@mitraintegrasi.co.id'],
            [
                'name' => $mitraDigital->name,
                'pic_name' => $mitraDigital->pic_name,
                'company_name' => $mitraDigital->name,
                'jenis' => 'industri',
                'phone' => $mitraDigital->pic_phone,
                'address' => $mitraDigital->address,
                'service_type' => 'psikotes',
                'source' => 'whatsapp',
                'status' => 'assigned',
                'created_by' => $admin?->id,
            ]
        );

        $batchA = Booking::create([
            'client_id' => $clientMitra->id,
            'company_id' => $mitraDigital->id,
            'pic_name' => $mitraDigital->pic_name,
            'kategori' => 'psikotes',
            'service_tier' => 'industri',
            'service_package_key' => 'long_supervisor_kerja_sama',
            'service_package_name' => 'Industri Long Supervisor (Kerja Sama) (1), Industri Short Staf (Kerja Sama) (2)',
            'price' => 1675000,
            'billing_scheme' => 'v',
            'requires_outgoing_letter' => true,
            'delivery_mode' => 'both',
            'tanggal_booking_dibuat' => Carbon::now()->subDays(3)->toDateString(),
            'tanggal_dijadwalkan' => $recentWorkday->toDateString(),
            'session_start' => '09:00',
            'session_end' => '13:00',
            'session_type' => 'tatap_muka',
            'location' => 'Lab Psikotes UC PSC Lt. 3',
            'status' => 'baru',
            'payment_workflow_status' => 'menunggu_sesi_selesai',
            'notes' => 'Batch rekrutmen kuartal IV. 1 kandidat level Supervisor dengan prioritas KILAT.',
        ]);

        $itemSupervisor = BookingServiceItem::create([
            'booking_id' => $batchA->id,
            'service_tier' => 'industri',
            'service_package_key' => 'long_supervisor_kerja_sama',
            'service_package_name' => 'Industri Long Supervisor (Kerja Sama)',
            'quantity' => 1,
            'unit_price' => 675000,
            'subtotal_price' => 675000,
        ]);

        $itemStaf = BookingServiceItem::create([
            'booking_id' => $batchA->id,
            'service_tier' => 'industri',
            'service_package_key' => 'short_staf_kerja_sama',
            'service_package_name' => 'Industri Short Staf (Kerja Sama)',
            'quantity' => 2,
            'unit_price' => 500000,
            'subtotal_price' => 1000000,
        ]);

        $batchACandidates = [
            [
                'item' => $itemSupervisor,
                'name' => 'Nadia Kartika, S.E.',
                'position' => 'Supervisor IT Operations',
                'level' => 'Supervisor',
                'is_kilat' => true,
                'attendance' => 'hadir',
                'psikolog' => $ainun?->id,
                'step_psikotes_at' => Carbon::now()->subDays(1),
                'step_skoring_at' => Carbon::now()->subHours(18),
                'step_surat_keluar_at' => Carbon::now()->subHours(12),
                'step_draft_hpp_at' => Carbon::now()->subHours(6),
            ],
            [
                'item' => $itemStaf,
                'name' => 'Bagus Wicaksono, S.T.',
                'position' => 'Staf Operasional Aplikasi',
                'level' => 'Staf',
                'is_kilat' => false,
                'attendance' => 'hadir',
                'psikolog' => $ainun?->id,
                'step_psikotes_at' => Carbon::now()->subDays(1),
                'step_skoring_at' => Carbon::now()->subHours(16),
            ],
            [
                'item' => $itemStaf,
                'name' => 'Clarissa Tan, S.Kom.',
                'position' => 'Staf Data Analyst',
                'level' => 'Staf',
                'is_kilat' => false,
                'attendance' => 'hadir',
                'psikolog' => $budi?->id,
                'step_psikotes_at' => Carbon::now()->subDays(1),
            ],
        ];

        foreach ($batchACandidates as $cand) {
            BookingCandidate::create([
                'booking_id' => $batchA->id,
                'client_id' => $clientMitra->id,
                'booking_service_item_id' => $cand['item']->id,
                'candidate_name' => $cand['name'],
                'target_position' => $cand['position'],
                'job_level' => $cand['level'],
                'is_kilat' => $cand['is_kilat'],
                'attendance_status' => $cand['attendance'],
                'assigned_psikolog_id' => $cand['psikolog'],
                'assigned_editor_id' => $editor?->id,
                'step_psikotes_at' => $cand['step_psikotes_at'] ?? null,
                'step_psikotes_by' => isset($cand['step_psikotes_at']) ? $tester?->id : null,
                'step_skoring_at' => $cand['step_skoring_at'] ?? null,
                'step_skoring_by' => isset($cand['step_skoring_at']) ? $skorerManual?->id : null,
                'step_surat_keluar_at' => $cand['step_surat_keluar_at'] ?? null,
                'step_surat_keluar_by' => isset($cand['step_surat_keluar_at']) ? $admin?->id : null,
                'step_draft_hpp_at' => $cand['step_draft_hpp_at'] ?? null,
                'step_draft_hpp_by' => isset($cand['step_draft_hpp_at']) ? $ainun?->user?->id : null,
            ]);

            BookingParticipant::create([
                'booking_id' => $batchA->id,
                'nama_peserta' => $cand['name'],
            ]);
        }

        foreach (array_unique(array_filter([$ainun?->id, $budi?->id])) as $cId) {
            BookingAssignee::create(['booking_id' => $batchA->id, 'role_type' => 'psikolog', 'counselor_id' => $cId]);
        }
        if ($tester) {
            BookingAssignee::create([
                'booking_id' => $batchA->id,
                'role_type' => 'tester',
                'user_id' => $tester->id,
                'batch_completed_at' => Carbon::now()->subDays(1),
                'completed_by_user_id' => $tester->id,
            ]);
        }
        if ($skorerManual) {
            BookingAssignee::create(['booking_id' => $batchA->id, 'role_type' => 'skorer_manual', 'user_id' => $skorerManual->id]);
        }
        if ($skorerSoftware) {
            BookingAssignee::create(['booking_id' => $batchA->id, 'role_type' => 'skorer_software', 'user_id' => $skorerSoftware->id]);
        }
        if ($editor) {
            BookingAssignee::create(['booking_id' => $batchA->id, 'role_type' => 'editor', 'user_id' => $editor->id]);
        }

        $slaService->recalculateForBooking($batchA);

        // 5. Batch Psikotes B: Rekap Perusahaan (R) Selesai + Resi Pengiriman Hardcopy
        $clientSurya = Client::firstOrCreate(
            ['email' => 'talent@suryalogi.co.id'],
            [
                'name' => $suryaLogistik->name,
                'pic_name' => $suryaLogistik->pic_name,
                'company_name' => $suryaLogistik->name,
                'jenis' => 'industri',
                'phone' => $suryaLogistik->pic_phone,
                'address' => $suryaLogistik->address,
                'service_type' => 'psikotes',
                'source' => 'whatsapp',
                'status' => 'completed',
                'created_by' => $admin?->id,
            ]
        );

        $batchBDate = Carbon::now()->subDays(10);
        while ($batchBDate->isWeekend()) {
            $batchBDate->subDay();
        }

        $batchB = Booking::create([
            'client_id' => $clientSurya->id,
            'company_id' => $suryaLogistik->id,
            'pic_name' => $suryaLogistik->pic_name,
            'kategori' => 'psikotes',
            'service_tier' => 'industri',
            'service_package_key' => 'long_staf_kerja_sama',
            'service_package_name' => 'Industri Long Staf (Kerja Sama) (2)',
            'price' => 1200000,
            'final_price' => 1200000,
            'billing_scheme' => 'R',
            'requires_outgoing_letter' => true,
            'delivery_mode' => 'hardcopy',
            'tanggal_booking_dibuat' => Carbon::now()->subDays(14)->toDateString(),
            'tanggal_dijadwalkan' => $batchBDate->toDateString(),
            'session_start' => '09:00',
            'session_end' => '12:30',
            'session_type' => 'tatap_muka',
            'location' => 'Lab Psikotes UC PSC Lt. 3',
            'status' => 'selesai',
            'payment_workflow_status' => 'draft_ke_finance',
            'finance_invoice_number' => 'INV-PSC/2026/11/0042',
            'notes' => 'Order selesai dikirim hardcopy via kurir. Masuk rekap bulanan perusahaan (R).',
        ]);

        $itemBatchB = BookingServiceItem::create([
            'booking_id' => $batchB->id,
            'service_tier' => 'industri',
            'service_package_key' => 'long_staf_kerja_sama',
            'service_package_name' => 'Industri Long Staf (Kerja Sama)',
            'quantity' => 2,
            'unit_price' => 600000,
            'subtotal_price' => 1200000,
        ]);

        $doneAt = Carbon::now()->subDays(3);
        foreach ([['Raka Putra Wijaya', 'Warehouse Coordinator'], ['Sinta Permatasari', 'Logistics Admin']] as [$candName, $pos]) {
            BookingCandidate::create([
                'booking_id' => $batchB->id,
                'client_id' => $clientSurya->id,
                'booking_service_item_id' => $itemBatchB->id,
                'candidate_name' => $candName,
                'target_position' => $pos,
                'job_level' => 'Staf',
                'is_kilat' => false,
                'attendance_status' => 'hadir',
                'assigned_psikolog_id' => $budi?->id,
                'assigned_editor_id' => $editor?->id,
                'step_psikotes_at' => $doneAt,
                'step_skoring_at' => $doneAt,
                'step_surat_keluar_at' => $doneAt,
                'step_draft_hpp_at' => $doneAt,
                'step_editing_at' => $doneAt,
                'step_cetak_at' => $doneAt,
                'step_approval_at' => $doneAt,
                'step_kirim_at' => $doneAt,
                'resi_date' => $doneAt->toDateString(),
                'resi_number' => 'JNE-SBY-20261110-089',
            ]);
            BookingParticipant::create(['booking_id' => $batchB->id, 'nama_peserta' => $candName]);
        }

        if ($budi) {
            BookingAssignee::create(['booking_id' => $batchB->id, 'role_type' => 'psikolog', 'counselor_id' => $budi->id, 'batch_completed_at' => $doneAt]);
        }
        foreach (['tester' => $tester, 'skorer_manual' => $skorerManual, 'skorer_software' => $skorerSoftware, 'editor' => $editor] as $role => $staffUser) {
            if ($staffUser) {
                BookingAssignee::create(['booking_id' => $batchB->id, 'role_type' => $role, 'user_id' => $staffUser->id, 'batch_completed_at' => $doneAt, 'completed_by_user_id' => $staffUser->id]);
            }
        }
        $slaService->recalculateForBooking($batchB);

        // 6. Batch Psikotes C: Non-Industri Khusus (UCS-MED MMPI - Rekap Internal I)
        $clientUcs = Client::firstOrCreate(
            ['email' => 'ucs@ciputra.ac.id'],
            [
                'name' => $ucsInternal->name,
                'pic_name' => $ucsInternal->pic_name,
                'company_name' => $ucsInternal->name,
                'jenis' => 'individu',
                'phone' => $ucsInternal->pic_phone,
                'address' => $ucsInternal->address,
                'service_type' => 'psikotes',
                'source' => 'referral',
                'status' => 'assigned',
                'created_by' => $admin?->id,
            ]
        );

        $batchC = Booking::create([
            'client_id' => $clientUcs->id,
            'company_id' => $ucsInternal->id,
            'pic_name' => $ucsInternal->pic_name,
            'kategori' => 'psikotes',
            'service_tier' => 'non_industri_khusus',
            'service_package_key' => 'ucs_med_mmpi',
            'service_package_name' => 'UCS-MED MMPI (2)',
            'price' => 100000,
            'billing_scheme' => 'I',
            'requires_outgoing_letter' => false,
            'delivery_mode' => 'softcopy',
            'tanggal_booking_dibuat' => Carbon::now()->toDateString(),
            'tanggal_dijadwalkan' => $upcomingWorkday->toDateString(),
            'session_start' => '13:00',
            'session_end' => '15:00',
            'session_type' => 'online',
            'location' => 'Online (UCS MNA)',
            'status' => 'baru',
            'payment_workflow_status' => 'menunggu_sesi_selesai',
            'notes' => 'Tes MMPI mahasiswa Fakultas Kedokteran UC via UCS MNA (tanpa psikolog interpreter).',
        ]);

        $itemBatchC = BookingServiceItem::create([
            'booking_id' => $batchC->id,
            'service_tier' => 'non_industri_khusus',
            'service_package_key' => 'ucs_med_mmpi',
            'service_package_name' => 'UCS-MED MMPI',
            'quantity' => 2,
            'unit_price' => 50000,
            'subtotal_price' => 100000,
        ]);

        foreach (['Kevin Gunawan', 'Felicia Santoso'] as $candName) {
            BookingCandidate::create([
                'booking_id' => $batchC->id,
                'client_id' => $clientUcs->id,
                'booking_service_item_id' => $itemBatchC->id,
                'candidate_name' => $candName,
                'target_position' => 'Mahasiswa Kedokteran',
                'job_level' => 'Non-Industri Khusus',
                'is_kilat' => false,
                'attendance_status' => 'pending',
                'assigned_psikolog_id' => null,
                'assigned_editor_id' => $editor?->id,
            ]);
            BookingParticipant::create(['booking_id' => $batchC->id, 'nama_peserta' => $candName]);
        }
        if ($skorerSoftware) {
            BookingAssignee::create(['booking_id' => $batchC->id, 'role_type' => 'skorer_software', 'user_id' => $skorerSoftware->id]);
        }
        if ($editor) {
            BookingAssignee::create(['booking_id' => $batchC->id, 'role_type' => 'editor', 'user_id' => $editor->id]);
        }
        $slaService->recalculateForBooking($batchC);

        // 7. Batch Psikotes D: Non-Industri Bakat Minat Biasa (Individu Reguler)
        $clientReza = Client::firstOrCreate(
            ['email' => 'reza.mahendra@email.com'],
            [
                'name' => 'Reza Mahendra',
                'jenis' => 'individu',
                'phone' => '081298765004',
                'gender' => 'l',
                'birth_place' => 'Surabaya',
                'dob' => '2004-07-22',
                'source' => 'whatsapp',
                'service_type' => 'psikotes',
                'status' => 'paid',
                'created_by' => $admin?->id,
            ]
        );

        $batchD = Booking::create([
            'client_id' => $clientReza->id,
            'kategori' => 'psikotes',
            'service_tier' => 'non_industri_bakat_minat',
            'service_package_key' => 'long_umum',
            'service_package_name' => 'Non-Industri Long (Umum) (1)',
            'price' => 750000,
            'final_price' => 750000,
            'billing_scheme' => 'v',
            'requires_outgoing_letter' => false,
            'delivery_mode' => 'softcopy',
            'tanggal_booking_dibuat' => Carbon::now()->subDays(1)->toDateString(),
            'tanggal_dijadwalkan' => $upcomingWorkday->toDateString(),
            'session_start' => '10:00',
            'session_end' => '12:30',
            'session_type' => 'tatap_muka',
            'location' => 'Ruang Psikotes Individu - UC PSC',
            'status' => 'baru',
            'payment_workflow_status' => 'lunas',
            'paid_at' => Carbon::now()->subHours(8),
            'notes' => 'Tes minat bakat penjurusan kuliah (individu umum).',
        ]);

        $itemBatchD = BookingServiceItem::create([
            'booking_id' => $batchD->id,
            'service_tier' => 'non_industri_bakat_minat',
            'service_package_key' => 'long_umum',
            'service_package_name' => 'Non-Industri Long (Umum)',
            'quantity' => 1,
            'unit_price' => 750000,
            'subtotal_price' => 750000,
        ]);

        BookingCandidate::create([
            'booking_id' => $batchD->id,
            'client_id' => $clientReza->id,
            'booking_service_item_id' => $itemBatchD->id,
            'candidate_name' => $clientReza->name,
            'target_position' => 'Penjurusan Kuliah',
            'job_level' => 'Non-Industri',
            'is_kilat' => false,
            'attendance_status' => 'pending',
            'assigned_psikolog_id' => $dian?->id,
            'assigned_editor_id' => $editor?->id,
        ]);
        BookingParticipant::create(['booking_id' => $batchD->id, 'nama_peserta' => $clientReza->name]);

        if ($dian) {
            BookingAssignee::create(['booking_id' => $batchD->id, 'role_type' => 'psikolog', 'counselor_id' => $dian->id]);
        }
        if ($tester) {
            BookingAssignee::create(['booking_id' => $batchD->id, 'role_type' => 'tester', 'user_id' => $tester->id]);
        }
        if ($editor) {
            BookingAssignee::create(['booking_id' => $batchD->id, 'role_type' => 'editor', 'user_id' => $editor->id]);
        }
        $slaService->recalculateForBooking($batchD);
    }
}
