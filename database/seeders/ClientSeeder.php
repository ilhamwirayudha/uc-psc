<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Client;
use App\Models\Counselor;
use App\Models\Pairing;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        if (Client::where('email', 'john.doe@email.com')->exists()) {
            return;
        }

        $staff = User::where('role', 'staff')->first() ?? User::first();
        $counselors = Counselor::all();

        $c1 = $counselors->get(0); // Ainun Fahma (Psikolog Klinis Dewasa)
        $c2 = $counselors->get(1); // Budi Santoso (Psikolog Anak & Remaja)
        $c3 = $counselors->get(2); // Citra Dewi (Psikolog Klinis Umum)

        // 1. John Doe - Konseling Terjadwal (Pro WNI Umum)
        $john = Client::create([
            'name' => 'John Doe',
            'phone' => '081298765001',
            'email' => 'john.doe@email.com',
            'gender' => 'l',
            'birth_place' => 'Surabaya',
            'dob' => '1998-05-14',
            'source' => 'whatsapp',
            'service_type' => 'konseling',
            'counseling_type' => 'Konseling Pro (Psikolog Senior) - WNI (Umum)',
            'status' => 'assigned',
            'notes' => 'Formulir Konseling Individu: Manajemen stres perkuliahan semester akhir dan kecemasan menghadapi tugas akhir.',
            'created_by' => $staff?->id,
            'created_at' => Carbon::now()->subDays(2),
        ]);

        if ($c1) {
            Pairing::create([
                'client_id' => $john->id,
                'counselor_id' => $c1->id,
                'assigned_by' => $staff?->id,
                'status' => 'active',
                'notes' => 'Dipasangkan dengan psikolog klinis dewasa.',
            ]);

            Booking::create([
                'client_id' => $john->id,
                'kategori' => 'konseling',
                'service_tier' => 'pro',
                'service_package_key' => 'pro_wni_umum',
                'service_package_name' => 'Konseling Pro (Psikolog Senior) - WNI (Umum)',
                'price' => 525000,
                'overtime_price' => 525000,
                'billing_scheme' => 'v',
                'session_type' => 'tatap_muka',
                'location' => 'Ruang Konseling 1 - UC PSC',
                'counselor_id' => $c1->id,
                'tanggal_booking_dibuat' => Carbon::now()->subDays(2)->toDateString(),
                'tanggal_dijadwalkan' => Carbon::now()->addDays(1)->toDateString(),
                'session_start' => '10:00',
                'session_duration_standard' => 60,
                'status' => 'terjadwal_gcal',
                'payment_workflow_status' => 'menunggu_sesi_selesai',
                'notes' => 'Sesi 1: Asesmen awal dan identifikasi sumber stres.',
            ]);
        }

        // 2. Andi Pratama - Menunggu Konfirmasi Jadwal Psikolog (Reguler WNI Umum)
        $andi = Client::create([
            'name' => 'Andi Pratama',
            'phone' => '081298765002',
            'email' => 'andi.pratama@email.com',
            'gender' => 'l',
            'birth_place' => 'Sidoarjo',
            'dob' => '1995-11-20',
            'source' => 'walk_in',
            'service_type' => 'konseling',
            'counseling_type' => 'Konseling Reguler - WNI (Umum)',
            'status' => 'assigned',
            'notes' => 'Formulir Konseling Dewasa & Karir: Konsultasi perencanaan karir dan kejenuhan kerja (burnout).',
            'created_by' => $staff?->id,
            'created_at' => Carbon::now()->subDays(3),
        ]);

        if ($c3) {
            Pairing::create([
                'client_id' => $andi->id,
                'counselor_id' => $c3->id,
                'assigned_by' => $staff?->id,
                'status' => 'active',
                'notes' => 'Menunggu konfirmasi kesediaan jadwal psikolog.',
            ]);

            Booking::create([
                'client_id' => $andi->id,
                'kategori' => 'konseling',
                'service_tier' => 'reguler',
                'service_package_key' => 'reguler_wni_umum',
                'service_package_name' => 'Konseling Reguler - WNI (Umum)',
                'price' => 275000,
                'overtime_price' => 275000,
                'billing_scheme' => 'v',
                'session_type' => 'online',
                'location' => 'Online (Google Meet UC PSC)',
                'counselor_id' => $c3->id,
                'tanggal_booking_dibuat' => Carbon::now()->subDays(3)->toDateString(),
                'tanggal_dijadwalkan' => Carbon::now()->addDays(2)->toDateString(),
                'session_start' => '14:00',
                'session_duration_standard' => 60,
                'status' => 'menunggu_konfirmasi',
                'payment_workflow_status' => 'menunggu_sesi_selesai',
                'notes' => 'Eksplorasi hambatan karir dan evaluasi keseimbangan kerja.',
            ]);
        }

        // 3. Sari Wulandari - Perlu Bayar Overtime (> 90 menit: 105 menit)
        $sari = Client::create([
            'name' => 'Sari Wulandari',
            'phone' => '081298765003',
            'email' => 'sari.wulandari@email.com',
            'gender' => 'p',
            'birth_place' => 'Malang',
            'dob' => '1992-03-08',
            'source' => 'referral',
            'service_type' => 'konseling',
            'counseling_type' => 'Konseling Pro (Psikolog Senior) - WNI (Umum)',
            'status' => 'unpaid',
            'notes' => 'Formulir Konseling Pernikahan: Hambatan komunikasi pasangan dan pembagian peran rumah tangga.',
            'created_by' => $staff?->id,
            'created_at' => Carbon::now()->subDays(4),
        ]);

        if ($c1) {
            Pairing::create([
                'client_id' => $sari->id,
                'counselor_id' => $c1->id,
                'assigned_by' => $staff?->id,
                'status' => 'active',
                'notes' => 'Menunggu pelunasan sesi dasar dan biaya overtime.',
            ]);

            Booking::create([
                'client_id' => $sari->id,
                'kategori' => 'konseling',
                'service_tier' => 'pro',
                'service_package_key' => 'pro_wni_umum',
                'service_package_name' => 'Konseling Pro (Psikolog Senior) - WNI (Umum)',
                'price' => 525000,
                'final_price' => 525000,
                'overtime_price' => 525000,
                'overtime_final_price' => 525000,
                'billing_scheme' => 'v',
                'session_type' => 'tatap_muka',
                'location' => 'Ruang Konseling 2 - UC PSC',
                'counselor_id' => $c1->id,
                'tanggal_booking_dibuat' => Carbon::now()->subDays(4)->toDateString(),
                'tanggal_dijadwalkan' => Carbon::now()->subDays(1)->toDateString(),
                'session_start' => '10:00',
                'session_end' => '11:45',
                'session_duration_standard' => 60,
                'actual_duration_minutes' => 105,
                'overtime_minutes' => 45,
                'is_overtime' => true,
                'status' => 'perlu_bayar_overtime',
                'payment_workflow_status' => 'menunggu_bayar',
                'va_number' => '880012340003',
                'finance_invoice_number' => 'INV-PSC/2026/11/0038',
                'notes' => 'Sesi berjalan 105 menit (+45 menit melewati standar 60 menit dan toleransi 30 menit).',
            ]);
        }

        // 4. Maya Anggraini - Perlu Sesi Lanjutan (Paid)
        $maya = Client::create([
            'name' => 'Maya Anggraini',
            'phone' => '081298765005',
            'email' => 'maya.anggraini@email.com',
            'gender' => 'p',
            'birth_place' => 'Surabaya',
            'dob' => '1990-09-17',
            'source' => 'walk_in',
            'service_type' => 'konseling',
            'counseling_type' => 'Konseling Reguler - WNI (Umum)',
            'status' => 'needs_followup',
            'notes' => 'Formulir Konseling Keluarga: Dinamika relasi antar generasi dan pengelolaan stres keluarga.',
            'created_by' => $staff?->id,
            'created_at' => Carbon::now()->subDays(6),
        ]);

        if ($c2) {
            Pairing::create([
                'client_id' => $maya->id,
                'counselor_id' => $c2->id,
                'assigned_by' => $staff?->id,
                'status' => 'active',
                'notes' => 'Jadwalkan follow-up sesi kedua.',
            ]);

            Booking::create([
                'client_id' => $maya->id,
                'kategori' => 'konseling',
                'service_tier' => 'reguler',
                'service_package_key' => 'reguler_wni_umum',
                'service_package_name' => 'Konseling Reguler - WNI (Umum)',
                'price' => 275000,
                'final_price' => 275000,
                'overtime_price' => 275000,
                'billing_scheme' => 'v',
                'session_type' => 'tatap_muka',
                'location' => 'Ruang Konseling Anak & Keluarga - UC PSC',
                'counselor_id' => $c2->id,
                'tanggal_booking_dibuat' => Carbon::now()->subDays(6)->toDateString(),
                'tanggal_dijadwalkan' => Carbon::now()->subDays(2)->toDateString(),
                'session_start' => '13:00',
                'session_end' => '14:15',
                'session_duration_standard' => 60,
                'actual_duration_minutes' => 75,
                'overtime_minutes' => 0,
                'is_overtime' => false,
                'status' => 'perlu_sesi_lanjutan',
                'payment_workflow_status' => 'lunas',
                'paid_at' => Carbon::now()->subDays(1),
                'notes' => 'Sesi 1 selesai dalam batas toleransi (75 menit). Perlu sesi 2 melibatkan keluarga.',
            ]);
        }

        // 5. Rina Kartika - Selesai & Lunas (Pro WNI Kerja Sama)
        $rina = Client::create([
            'name' => 'Rina Kartika',
            'phone' => '081298765008',
            'email' => 'rina.kartika@email.com',
            'gender' => 'p',
            'birth_place' => 'Gresik',
            'dob' => '2001-04-25',
            'source' => 'walk_in',
            'service_type' => 'konseling',
            'counseling_type' => 'Konseling Pro (Psikolog Senior) - WNI (Kerja Sama)',
            'status' => 'completed',
            'notes' => 'Konseling pengembangan diri & persiapan studi lanjut.',
            'created_by' => $staff?->id,
            'created_at' => Carbon::now()->subDays(8),
        ]);

        if ($c1) {
            Pairing::create([
                'client_id' => $rina->id,
                'counselor_id' => $c1->id,
                'assigned_by' => $staff?->id,
                'status' => 'completed',
                'notes' => 'Sesi selesai dan dilunasi.',
            ]);

            Booking::create([
                'client_id' => $rina->id,
                'kategori' => 'konseling',
                'service_tier' => 'pro',
                'service_package_key' => 'pro_wni_kerja_sama',
                'service_package_name' => 'Konseling Pro (Psikolog Senior) - WNI (Kerja Sama)',
                'price' => 450000,
                'final_price' => 450000,
                'overtime_price' => 450000,
                'billing_scheme' => 'v',
                'session_type' => 'tatap_muka',
                'location' => 'Ruang Konseling 1 - UC PSC',
                'counselor_id' => $c1->id,
                'tanggal_booking_dibuat' => Carbon::now()->subDays(8)->toDateString(),
                'tanggal_dijadwalkan' => Carbon::now()->subDays(5)->toDateString(),
                'session_start' => '09:00',
                'session_end' => '10:00',
                'session_duration_standard' => 60,
                'actual_duration_minutes' => 60,
                'status' => 'selesai',
                'payment_workflow_status' => 'lunas',
                'paid_at' => Carbon::now()->subDays(4),
                'notes' => 'Sesi tuntas tepat waktu (60 menit).',
            ]);
        }

        // 6. Binar Cahya - Data Masuk (Belum Terjadwal)
        $binar = Client::create([
            'name' => 'Binar Cahya',
            'phone' => '081298765007',
            'email' => 'binar.cahya@email.com',
            'gender' => 'non_binary',
            'birth_place' => 'Surabaya',
            'dob' => '2005-12-01',
            'source' => 'whatsapp',
            'service_type' => 'konseling',
            'counseling_type' => 'Konseling Remaja & Dewasa Muda',
            'status' => 'unassigned',
            'notes' => 'Formulir Konseling Remaja: Kesulitan adaptasi sosial dan manajemen waktu kuliah.',
            'created_by' => $staff?->id,
            'created_at' => Carbon::now()->subHours(2),
        ]);

        Booking::create([
            'client_id' => $binar->id,
            'kategori' => 'konseling',
            'tanggal_booking_dibuat' => Carbon::now()->toDateString(),
            'session_type' => 'tatap_muka',
            'session_duration_standard' => 60,
            'status' => 'data_masuk',
            'payment_workflow_status' => 'menunggu_sesi_selesai',
            'notes' => 'Pendaftaran masuk baru, menunggu penjadwalan psikolog oleh Admin.',
        ]);
    }
}
