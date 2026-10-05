<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\ClientForm;
use Illuminate\Database\Seeder;

class SampleDewasaSeeder extends Seeder
{
    public function run(): void
    {
        $client = Client::firstOrCreate(
            ['phone' => '081234567890'],
            [
                'name' => 'Budi Santoso',
                'email' => 'budi.santoso@example.com',
                'source' => 'referral',
                'status' => 'unassigned',
                'address' => 'Jl. Dharmahusada Indah No. 42, Surabaya',
            ]
        );

        ClientForm::firstOrCreate(
            ['ticket_number' => 'PSC-DWS-260925-47A1'],
            [
                'form_type' => 'dewasa',
                'client_id' => $client->id,
                'client_name' => 'Budi Santoso',
                'client_phone' => '081234567890',
                'consent_agreed' => true,
                'consent_agreed_at' => now(),
                'service_preference' => 'Online (Google Meet)',
                'status' => 'baru',
                'answers' => [
                    'jenis_kelamin' => 'Laki-Laki',
                    'tempat_lahir' => 'Surabaya',
                    'tanggal_lahir' => '1996-08-14',
                    'urutan_kelahiran' => 'Anak ke 2 dari 3 bersaudara',
                    'alamat' => 'Jl. Dharmahusada Indah No. 42, Surabaya',
                    'agama' => 'Kristen',
                    'suku_bangsa' => 'Jawa',
                    'pendidikan_terakhir' => 'S1 Teknik Informatika',
                    'pekerjaan' => 'Software Engineer',
                    'hobi' => 'Membaca, Bersepeda, Musik',
                    'alasan_konseling' => 'Mengalami burnout kerja dan kecemasan berlebih saat menghadapi deadline proyek',
                    'kondisi_saat_ini' => 'Sering sulit tidur, tegang di leher, dan merasa overthinking berulang',
                    'pikiran_negatif' => 'Takut gagal memenuhi ekspektasi tim dan kekhawatiran berlebih tentang masa depan',
                    'hal_ingin_ditingkatkan' => 'Manajemen stres, relaksasi emosi, dan komunikasi asertif di tempat kerja',
                    'karakter_kepribadian' => 'Introvert, tekun, analitis, pemikir, cenderung perfeksionis',
                    'ketakutan_phobia' => 'Ketinggian (akrofobia)',
                    'jam_tidur' => '4 - 5 jam per hari',
                    'riwayat_trauma' => 'Tidak ada trauma masa kecil yang signifikan',
                    'riwayat_kesehatan' => 'Riwayat maag / GERD jika stres meningkat',
                    'relasi_ayah' => 'Hubungan baik dan saling menghormati',
                    'relasi_ibu' => 'Sangat dekat, sering berbagi cerita',
                    'relasi_saudara' => 'Akrab dengan kakak dan adik',
                    'relasi_pasangan' => 'Belum menikah, relasi dengan pasangan baik',
                    'relasi_anak' => '-',
                    'pernah_konseling' => 'Belum pernah',
                    'kontak_darurat' => 'Siti Nurhaliza (Ibu) - 081987654321',
                    'sumber_info' => 'Instagram UC PSC (@uc.psc)',
                    'preferensi_konseling' => 'Online (Google Meet)',
                ]
            ]
        );
    }
}
