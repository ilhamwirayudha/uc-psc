<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\ClientForm;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AddDewasaDummyClientSeeder extends Seeder
{
    public function run(): void
    {
        $phone = '081388992211';
        $email = 'anissa.larasati@gmail.com';
        $name = 'Anissa Larasati, S.Psi.';

        $client = Client::firstOrCreate(
            ['phone' => $phone],
            [
                'name' => $name,
                'email' => $email,
                'jenis' => 'individu',
                'source' => 'referral',
                'status' => 'unassigned',
                'address' => 'Jl. Bukit Darmo Boulevard No. 88, Surabaya Barat',
            ]
        );

        // Pastikan jenis individu
        $client->update(['jenis' => 'individu']);

        $ticket = 'PSC-DWS-' . date('ymd') . '-' . strtoupper(Str::random(4));

        ClientForm::create([
            'ticket_number' => $ticket,
            'form_type' => 'dewasa',
            'client_id' => $client->id,
            'client_name' => $name,
            'client_phone' => $phone,
            'consent_agreed' => true,
            'consent_agreed_at' => now(),
            'service_preference' => 'Tatap Muka (Offline di UC PSC)',
            'answers' => [
                'nama_lengkap' => $name,
                'jenis_kelamin' => 'Perempuan',
                'tempat_tanggal_lahir' => 'Surabaya, 14 Mei 1998',
                'urutan_kelahiran' => 'Anak ke-1 dari 2 bersaudara',
                'alamat' => 'Jl. Bukit Darmo Boulevard No. 88, Surabaya Barat',
                'phone' => $phone,
                'email' => $email,
                'agama' => 'Islam',
                'suku_bangsa' => 'Jawa',
                'pendidikan_terakhir' => 'S1 Psikologi',
                'pekerjaan_saat_ini' => 'HR Talent Development Specialist',
                'hobi' => 'Menulis jurnal, yoga, dan membaca literatur psikologi',
                'alasan_konseling' => 'Mengalami work-life imbalance dan kecemasan terkait career progression serta sering merasa overthinking terhadap ekspektasi keluarga.',
                'kondisi_saat_ini' => 'Kualitas tidur menurun, sering merasa lelah mental di sore hari, dan kesulitan mengambil keputusan personal penting.',
                'pikiran_negatif' => 'Merasa belum cukup berprestasi dibandingkan rekan sebaya dan cemas mengecewakan orang tua.',
                'hal_ingin_ditingkatkan' => 'Regulasi emosi, cognitive reframing untuk meredakan overthinking, dan penetapan boundary kerja-pribadi yang sehat.',
                'karakter_kepribadian' => 'Empatik, reflektif, bertanggung jawab tinggi, namun cenderung perfeksionis dan self-critical.',
                'ketakutan_phobia' => 'Claustrophobia ringan pada ruang sempit tertutup tanpa ventilasi.',
                'jam_tidur' => '5 - 6 jam per hari (sering terbangun tengah malam)',
                'riwayat_trauma' => 'Tidak ada riwayat trauma berat; ada pengalaman perundungan verbal saat SMP yang sudah terselesaikan.',
                'riwayat_kesehatan' => 'Kondisi fisik umum sehat, tidak ada riwayat rawat inap atau penyakit kronis.',
                'relasi_ayah' => 'Harmonis namun komunikasi cenderung formal dan minim diskusi emosional.',
                'relasi_ibu' => 'Sangat dekat dan hangat, menjadi tempat utama bercerita.',
                'relasi_saudara' => 'Akrab dan suportif dengan adik laki-laki yang sedang kuliah.',
                'relasi_pasangan' => 'Sedang dalam hubungan pacaran sehat selama 2 tahun.',
                'relasi_anak' => 'Belum memiliki anak (belum menikah).',
                'pernah_konseling' => 'Pernah sesi konseling konseling karir 1 kali saat masa tugas akhir kuliah.',
                'nama_konselor' => 'Dra. Endang Sulistyo, M.Psi., Psikolog',
                'kontak_darurat' => 'Rangga Pratama (Pasangan) - 081233445566',
                'sumber_info' => 'Rekomendasi Rekan Kerja & Media Sosial UC PSC',
                'preferensi_konseling' => 'Tatap Muka (Offline di UC PSC)',
            ]
        ]);
    }
}

