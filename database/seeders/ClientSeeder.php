<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\User;
use App\Models\Counselor;
use App\Models\Pairing;
use App\Models\CounselingRecord;
use App\Models\TestResult;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $staff = User::where('role', 'staff')->first() ?? User::first();
        $counselors = Counselor::all();

        $c1 = $counselors->get(0); // Ainun Fahma (Psikolog Klinis Dewasa)
        $c2 = $counselors->get(1); // Budi Santoso (Psikolog Anak & Remaja)
        $c3 = $counselors->get(2); // Citra Dewi (Psikolog Klinis Umum)
        $c4 = $counselors->get(3); // Dian Kusuma (Psikolog Pendidikan)

        // 1. John Doe - ASSIGNED (Telah Di-assign)
        $john = Client::create([
            'name' => 'John Doe',
            'phone' => '081298765001',
            'email' => 'john.doe@email.com',
            'gender' => 'l',
            'dob' => '1998-05-14',
            'source' => 'whatsapp',
            'service_type' => 'konseling',
            'counseling_type' => 'Konseling Individu',
            'status' => 'assigned',
            'notes' => 'Klien mendaftar untuk konseling manajemen stres perkuliahan semester akhir. Sudah dijadwalkan sesi besok.',
            'created_by' => $staff->id,
            'created_at' => Carbon::now()->subDays(2),
        ]);

        if ($c1) {
            Pairing::create([
                'client_id' => $john->id,
                'counselor_id' => $c1->id,
                'assigned_by' => $staff->id,
                'status' => 'active',
                'notes' => 'Dipasangkan dengan psikolog klinis dewasa.',
            ]);

            CounselingRecord::create([
                'client_id' => $john->id,
                'counselor_id' => $c1->id,
                'admin_id' => $staff->id,
                'type' => 'tatap_muka',
                'scheduled_at' => Carbon::now()->addDays(1)->setTime(10, 0, 0),
                'end_time' => Carbon::now()->addDays(1)->setTime(11, 30, 0),
                'location' => 'Ruang Konseling A - Gedung Utama UC',
                'summary' => 'Sesi 1: Asesmen awal dan identifikasi sumber stres.',
                'status' => 'scheduled',
            ]);
        }

        // 2. Andi Pratama - ONGOING (Sedang Konseling)
        $andi = Client::create([
            'name' => 'Andi Pratama',
            'phone' => '081298765002',
            'email' => 'andi.pratama@email.com',
            'gender' => 'l',
            'dob' => '1995-11-20',
            'source' => 'walk_in',
            'service_type' => 'konseling',
            'counseling_type' => 'Konseling Dewasa & Karir',
            'status' => 'ongoing',
            'notes' => 'Konsultasi perencanaan karir dan penanganan burn-out pekerjaan.',
            'created_by' => $staff->id,
            'created_at' => Carbon::now()->subDays(3),
        ]);

        if ($c3) {
            Pairing::create([
                'client_id' => $andi->id,
                'counselor_id' => $c3->id,
                'assigned_by' => $staff->id,
                'status' => 'active',
                'notes' => 'Sesi konseling sedang berlangsung aktif.',
            ]);

            CounselingRecord::create([
                'client_id' => $andi->id,
                'counselor_id' => $c3->id,
                'admin_id' => $staff->id,
                'type' => 'tatap_muka',
                'scheduled_at' => Carbon::now()->subMinutes(25),
                'end_time' => Carbon::now()->addMinutes(35),
                'location' => 'Ruang Konseling B - Ruang Tenang UC PSC',
                'summary' => 'Sesi berlangsung: Eksplorasi hambatan karir dan relaksasi progresif.',
                'status' => 'scheduled',
            ]);
        }

        // 3. Sari Wulandari - UNPAID (Belum Membayar)
        $sari = Client::create([
            'name' => 'Sari Wulandari',
            'phone' => '081298765003',
            'email' => 'sari.wulandari@email.com',
            'gender' => 'p',
            'dob' => '1992-03-08',
            'source' => 'referral',
            'service_type' => 'konseling',
            'counseling_type' => 'Konseling Pernikahan & Pasangan',
            'status' => 'unpaid',
            'notes' => 'Sesi konseling telah selesai tadi pagi, menunggu konfirmasi bukti transfer pembayaran dari klien.',
            'created_by' => $staff->id,
            'created_at' => Carbon::now()->subDays(4),
        ]);

        if ($c1) {
            Pairing::create([
                'client_id' => $sari->id,
                'counselor_id' => $c1->id,
                'assigned_by' => $staff->id,
                'status' => 'active',
                'notes' => 'Menunggu bukti bayar untuk sesi 1.',
            ]);

            CounselingRecord::create([
                'client_id' => $sari->id,
                'counselor_id' => $c1->id,
                'admin_id' => $staff->id,
                'type' => 'tatap_muka',
                'scheduled_at' => Carbon::now()->subHours(4),
                'end_time' => Carbon::now()->subHours(2)->subMinutes(30),
                'location' => 'Ruang Konseling A - Gedung Utama UC',
                'summary' => 'Sesi telah selesai. Klien dan pasangan sepakat membuat kesepakatan pola komunikasi harian.',
                'status' => 'completed',
            ]);
        }

        // 4. Reza Mahendra - PAID (Lunas)
        $reza = Client::create([
            'name' => 'Reza Mahendra',
            'phone' => '081298765004',
            'email' => 'reza.mahendra@email.com',
            'gender' => 'l',
            'dob' => '2004-07-22',
            'source' => 'whatsapp',
            'service_type' => 'psikotes',
            'counseling_type' => 'Tes Minat Bakat (Holland/RIASEC)',
            'status' => 'paid',
            'notes' => 'Biaya asesmen psikotes telah dibayar lunas via transfer bank. Menunggu laporan hasil psikogram keluar.',
            'created_by' => $staff->id,
            'created_at' => Carbon::now()->subDays(5),
        ]);

        if ($c4) {
            Pairing::create([
                'client_id' => $reza->id,
                'counselor_id' => $c4->id,
                'assigned_by' => $staff->id,
                'status' => 'active',
                'notes' => 'Tester psikolog pendidikan.',
            ]);

            CounselingRecord::create([
                'client_id' => $reza->id,
                'counselor_id' => $c4->id,
                'admin_id' => $staff->id,
                'type' => 'tatap_muka',
                'scheduled_at' => Carbon::now()->subDays(1)->setTime(13, 0, 0),
                'end_time' => Carbon::now()->subDays(1)->setTime(15, 0, 0),
                'location' => 'Lab Psikodiagnostik UC Lantai 3',
                'summary' => 'Pelaksanaan tes minat bakat RIASEC dan inventori kepribadian 16PF berjalan lancar.',
                'status' => 'completed',
            ]);

            TestResult::create([
                'client_id' => $reza->id,
                'test_name' => 'Tes Minat Bakat & Jurusan (RIASEC)',
                'result_summary' => 'Dominan tipe Investigative dan Artistic. Rekomendasi bidang studi: Desain Komunikasi Visual atau Informatika.',
                'file_path' => null,
                'administered_by' => $staff->id,
                'tested_at' => Carbon::now()->subDays(1),
            ]);
        }

        // 5. Maya Anggraini - NEEDS_FOLLOWUP (Butuh Sesi Lanjutan)
        $maya = Client::create([
            'name' => 'Maya Anggraini',
            'phone' => '081298765005',
            'email' => 'maya.anggraini@email.com',
            'gender' => 'p',
            'dob' => '1990-09-17',
            'source' => 'walk_in',
            'service_type' => 'konseling',
            'counseling_type' => 'Konseling Keluarga',
            'status' => 'needs_followup',
            'notes' => 'Konselor merekomendasikan sesi tindak lanjut minggu depan untuk memantau evaluasi dinamika keluarga.',
            'created_by' => $staff->id,
            'created_at' => Carbon::now()->subDays(6),
        ]);

        if ($c2) {
            Pairing::create([
                'client_id' => $maya->id,
                'counselor_id' => $c2->id,
                'assigned_by' => $staff->id,
                'status' => 'active',
                'notes' => 'Jadwalkan follow-up sesi kedua.',
            ]);

            CounselingRecord::create([
                'client_id' => $maya->id,
                'counselor_id' => $c2->id,
                'admin_id' => $staff->id,
                'type' => 'online',
                'scheduled_at' => Carbon::now()->subDays(2)->setTime(14, 0, 0),
                'end_time' => Carbon::now()->subDays(2)->setTime(15, 30, 0),
                'location' => 'https://meet.google.com/uc-family-counseling',
                'summary' => 'Sesi 1 selesai. Perlu sesi 2 untuk melibatkan anggota keluarga lainnya.',
                'status' => 'completed',
            ]);
        }

        // 6. Fajar Nugroho - COMPLETED (Selesai)
        $fajar = Client::create([
            'name' => 'Fajar Nugroho',
            'phone' => '081298765006',
            'email' => 'fajar.nugroho@email.com',
            'gender' => 'l',
            'dob' => '2018-02-10',
            'source' => 'referral',
            'service_type' => 'psikotes',
            'counseling_type' => 'Tes Kesiapan Masuk Sekolah (TK/SD)',
            'status' => 'completed',
            'notes' => 'Seluruh rangkaian asesmen kesiapan sekolah, laporan psikologis, dan pembayaran telah tuntas.',
            'created_by' => $staff->id,
            'created_at' => Carbon::now()->subDays(8),
        ]);

        if ($c2) {
            Pairing::create([
                'client_id' => $fajar->id,
                'counselor_id' => $c2->id,
                'assigned_by' => $staff->id,
                'status' => 'completed',
                'notes' => 'Kasus asesmen tuntas diserahkan ke orang tua.',
            ]);

            CounselingRecord::create([
                'client_id' => $fajar->id,
                'counselor_id' => $c2->id,
                'admin_id' => $staff->id,
                'type' => 'tatap_muka',
                'scheduled_at' => Carbon::now()->subDays(5)->setTime(9, 0, 0),
                'end_time' => Carbon::now()->subDays(5)->setTime(11, 0, 0),
                'location' => 'Ruang Asesmen Tumbuh Kembang Anak',
                'summary' => 'Asesmen NST (Nijmeegse Schoolbekwaamheids Test) dan observasi motorik halus/kasar selesai.',
                'status' => 'completed',
            ]);

            TestResult::create([
                'client_id' => $fajar->id,
                'test_name' => 'Laporan Hasil Tes Kesiapan Sekolah (NST)',
                'result_summary' => 'Kematangan motorik, kognitif, dan sosial-emosional sangat baik dan siap masuk jenjang SD.',
                'file_path' => null,
                'administered_by' => $staff->id,
                'tested_at' => Carbon::now()->subDays(5),
            ]);
        }

        // 7. Binar Cahya - UNASSIGNED (Belum Di-assign)
        Client::create([
            'name' => 'Binar Cahya',
            'phone' => '081298765007',
            'email' => 'binar.cahya@email.com',
            'gender' => 'non_binary',
            'dob' => '2005-12-01',
            'source' => 'whatsapp',
            'service_type' => 'konseling',
            'counseling_type' => 'Konseling Remaja & Anak',
            'status' => 'unassigned',
            'notes' => 'Klien baru mendaftar mandiri via WhatsApp, membutuhkan konsultasi terkait adaptasi perkuliahan baru. Belum dipasangkan konselor.',
            'created_by' => $staff->id,
            'created_at' => Carbon::now()->subHours(2),
        ]);

        // 8. Rina Kartika - UNASSIGNED (Belum Di-assign)
        Client::create([
            'name' => 'Rina Kartika',
            'phone' => '081298765008',
            'email' => 'rina.kartika@email.com',
            'gender' => 'p',
            'dob' => '2001-04-25',
            'source' => 'walk_in',
            'service_type' => 'psikotes',
            'counseling_type' => 'Tes IQ / Kecerdasan (WISC / WAIS / CPM)',
            'status' => 'unassigned',
            'notes' => 'Pendaftaran walk-in untuk tes inteligensi kebutuhan persyaratan beasiswa S2. Menunggu penugasan tester.',
            'created_by' => $staff->id,
            'created_at' => Carbon::now()->subHours(5),
        ]);
    }
}
