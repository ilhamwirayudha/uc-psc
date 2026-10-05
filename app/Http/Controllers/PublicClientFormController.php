<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Client;
use App\Models\ClientForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PublicClientFormController extends Controller
{
    /**
     * Display the Child Counseling registration form.
     */
    public function showAnak()
    {
        return view('public.client-forms.anak');
    }

    /**
     * Store a new Child Counseling registration form submission.
     */
    public function storeAnak(Request $request)
    {
        $validated = $request->validate([
            // 1. Informed Consent
            'consent_agree' => 'required|in:Setuju,setuju,1',
            
            // 2. Data Diri Anak
            'nama_lengkap' => 'required|string|max:255',
            'jenis_kelamin' => 'required|string|in:Laki-Laki,Perempuan',
            'tempat_lahir' => 'required|string|max:100',
            'tanggal_lahir' => 'required|date',
            'urutan_kelahiran' => 'required|string|max:100',
            'alamat' => 'required|string',
            'phone' => 'required|string|max:50',
            'agama' => 'required|string|max:50',
            'suku_bangsa' => 'required|string|max:50',
            'pendidikan_terakhir' => 'required|string|max:100',
            'status_pernikahan_ortu' => 'required|string|in:Menikah,Bercerai',
            'alasan_konseling' => 'required|string',

            // 3. Data Kondisi Psikologis Anak
            'kondisi_anak_saat_ini' => 'required|string',
            'hal_ingin_ditingkatkan' => 'required|string',
            'karakter_anak' => 'required|string',
            'prestasi_anak' => 'nullable|string',
            'ketakutan_phobia' => 'nullable|string',
            'riwayat_trauma' => 'nullable|string',
            'riwayat_kesehatan' => 'nullable|string',

            // 4. Gambaran Relasi Anak
            'relasi_ayah' => 'required|string',
            'relasi_ibu' => 'required|string',
            'relasi_saudara' => 'nullable|string',
            'relasi_teman' => 'nullable|string',

            // 5. Data Orang Tua (Ayah)
            'nama_ayah' => 'required|string|max:255',
            'jenis_kelamin_ayah' => 'required|string|in:Laki-Laki,Perempuan',
            'usia_ayah' => 'required|string|max:50',
            'pendidikan_ayah' => 'required|string|max:100',
            'pekerjaan_ayah' => 'required|string|max:100',
            'agama_ayah' => 'required|string|max:50',
            'urutan_kelahiran_ayah' => 'required|string|max:100',
            'alamat_ayah' => 'required|string',
            'phone_ayah' => 'required|string|max:50',
            'pernikahan_ayah_ke' => 'required|string|max:50',
            'jumlah_anak_ayah' => 'required|string|max:50',
            'status_nikah_ayah' => 'required|string|in:Menikah,Bercerai',

            // 5. Data Orang Tua (Ibu)
            'nama_ibu' => 'required|string|max:255',
            'jenis_kelamin_ibu' => 'required|string|in:Laki-Laki,Perempuan',
            'usia_ibu' => 'required|string|max:50',
            'pendidikan_ibu' => 'required|string|max:100',
            'pekerjaan_ibu' => 'required|string|max:100',
            'agama_ibu' => 'required|string|max:50',
            'urutan_kelahiran_ibu' => 'required|string|max:100',
            'alamat_ibu' => 'required|string',
            'phone_ibu' => 'required|string|max:50',
            'pernikahan_ibu_ke' => 'required|string|max:50',
            'jumlah_anak_ibu' => 'required|string|max:50',
            'status_nikah_ibu' => 'required|string|in:Menikah,Bercerai',

            // 6. Lain-Lain
            'pernah_konseling' => 'required|string|in:Ya,Tidak',
            'nama_konselor_lama' => 'nullable|string|max:255',
            'kontak_darurat' => 'required|string|max:255',
            'sumber_info' => 'required|string|max:100',
            'preferensi_konseling' => 'required|string|in:Online melalui Zoom,Offline di UCPSC',
        ], [
            'consent_agree.required' => 'Anda wajib menyetujui Lembar Persetujuan (Informed Consent) untuk melanjutkan.',
            'consent_agree.in' => 'Anda wajib memilih opsi Setuju pada Lembar Persetujuan.',
            'nama_lengkap.required' => 'Nama lengkap anak wajib diisi.',
            'jenis_kelamin.required' => 'Pilih jenis kelamin anak.',
            'tempat_lahir.required' => 'Tempat lahir anak wajib diisi.',
            'tanggal_lahir.required' => 'Tanggal lahir anak wajib diisi.',
            'urutan_kelahiran.required' => 'Urutan kelahiran anak wajib diisi.',
            'alamat.required' => 'Alamat tinggal anak wajib diisi.',
            'phone.required' => 'Nomor Telp/HP anak atau kontak utama wajib diisi.',
            'agama.required' => 'Agama/kepercayaan anak wajib diisi.',
            'suku_bangsa.required' => 'Suku bangsa anak wajib diisi.',
            'pendidikan_terakhir.required' => 'Pendidikan terakhir anak wajib diisi.',
            'status_pernikahan_ortu.required' => 'Status pernikahan orang tua wajib diisi.',
            'alasan_konseling.required' => 'Alasan anak membutuhkan konseling wajib diisi.',
            'kondisi_anak_saat_ini.required' => 'Gambaran kondisi anak saat ini wajib diisi.',
            'hal_ingin_ditingkatkan.required' => 'Hal-hal yang ingin ditingkatkan wajib diisi.',
            'karakter_anak.required' => 'Karakter kepribadian anak wajib diisi.',
            'relasi_ayah.required' => 'Gambaran relasi anak dengan ayah wajib diisi.',
            'relasi_ibu.required' => 'Gambaran relasi anak dengan ibu wajib diisi.',
            'nama_ayah.required' => 'Nama ayah wajib diisi.',
            'usia_ayah.required' => 'Usia ayah wajib diisi.',
            'pendidikan_ayah.required' => 'Pendidikan ayah wajib diisi.',
            'pekerjaan_ayah.required' => 'Pekerjaan ayah wajib diisi.',
            'agama_ayah.required' => 'Agama ayah wajib diisi.',
            'urutan_kelahiran_ayah.required' => 'Urutan kelahiran ayah wajib diisi.',
            'alamat_ayah.required' => 'Alamat ayah wajib diisi.',
            'phone_ayah.required' => 'Nomor HP ayah wajib diisi.',
            'pernikahan_ayah_ke.required' => 'Pernikahan ayah ke- wajib diisi.',
            'jumlah_anak_ayah.required' => 'Jumlah anak ayah wajib diisi.',
            'status_nikah_ayah.required' => 'Status pernikahan ayah dengan ibu wajib diisi.',
            'nama_ibu.required' => 'Nama ibu wajib diisi.',
            'usia_ibu.required' => 'Usia ibu wajib diisi.',
            'pendidikan_ibu.required' => 'Pendidikan ibu wajib diisi.',
            'pekerjaan_ibu.required' => 'Pekerjaan ibu wajib diisi.',
            'agama_ibu.required' => 'Agama ibu wajib diisi.',
            'urutan_kelahiran_ibu.required' => 'Urutan kelahiran ibu wajib diisi.',
            'alamat_ibu.required' => 'Alamat ibu wajib diisi.',
            'phone_ibu.required' => 'Nomor HP ibu wajib diisi.',
            'pernikahan_ibu_ke.required' => 'Pernikahan ibu ke- wajib diisi.',
            'jumlah_anak_ibu.required' => 'Jumlah anak ibu wajib diisi.',
            'status_nikah_ibu.required' => 'Status pernikahan ibu dengan ayah wajib diisi.',
            'kontak_darurat.required' => 'Kontak darurat beserta relasinya wajib diisi.',
            'sumber_info.required' => 'Silakan pilih dari mana Anda mengetahui tentang UC PSC.',
            'preferensi_konseling.required' => 'Silakan pilih preferensi proses konseling (Online / Offline).',
        ]);

        try {
            return DB::transaction(function () use ($request, $validated) {
                // Determine contact phone for the child (use child's phone if any, or mother's/father's phone)
                $primaryPhone = !empty($request->phone) ? $request->phone : (!empty($request->phone_ibu) ? $request->phone_ibu : $request->phone_ayah);

                // Convert gender to single char
                $gender = (strtolower($request->jenis_kelamin) === 'laki-laki' || strtolower($request->jenis_kelamin) === 'l') ? 'l' : 'p';

                // Determine counseling type
                $isOnline = str_contains(strtolower($request->preferensi_konseling), 'online');
                $counselingType = $isOnline ? 'online' : 'offline';

                // Map source to valid enum ['whatsapp', 'walk_in', 'referral', 'other']
                $sourceEnum = match(strtolower($request->sumber_info)) {
                    'referensi dari kerabat', 'rujukan' => 'referral',
                    'walk_in', 'langsung' => 'walk_in',
                    'media sosial', 'website', 'instagram' => 'other',
                    default => 'other',
                };

                // 1. Create or Find Client
                $client = Client::firstOrNew([
                    'name' => trim($request->nama_lengkap),
                    'phone' => trim($primaryPhone),
                ]);

                $client->jenis = 'individu';
                $client->gender = $gender;
                $client->birth_place = $request->tempat_lahir;
                $client->dob = $request->tanggal_lahir;
                $client->address = $request->alamat;
                $client->religion = $request->agama;
                $client->education = $request->pendidikan_terakhir;
                $client->service_type = 'Konseling Anak';
                $client->counseling_type = $counselingType;
                $client->source = $sourceEnum;
                $client->status = 'unassigned';
                
                // Add rich intake note
                $notePrefix = "[Pendaftaran Mandiri Web - Konseling Anak]\n";
                $noteContent = "Nama Orang Tua: Ayah ({$request->nama_ayah} - {$request->phone_ayah}), Ibu ({$request->nama_ibu} - {$request->phone_ibu})\nAlasan Konseling: {$request->alasan_konseling}";
                $client->notes = $notePrefix . $noteContent;
                $client->save();

                // 2. Create ClientForm Record with JSON answers (Tanpa Booking Otomatis)
                $ticketNumber = ClientForm::generateTicketNumber('anak');
                $clientForm = ClientForm::create([
                    'ticket_number' => $ticketNumber,
                    'form_type' => 'anak',
                    'client_id' => $client->id,
                    'booking_id' => null,
                    'client_name' => $client->name,
                    'client_phone' => $primaryPhone,
                    'consent_agreed' => true,
                    'consent_agreed_at' => now(),
                    'service_preference' => $counselingType,
                    'status' => 'baru',
                    'answers' => $request->except(['_token', 'consent_agree']),
                    'ip_address' => $request->ip(),
                ]);

                return redirect()->route('public.client-form.success', ['ticket' => $ticketNumber]);
            });
        } catch (\Throwable $e) {
            Log::error('Client Form Anak submission error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withInput()->with('error', 'Terjadi kesalahan saat memproses formulir pendaftaran. Silakan coba kembali atau hubungi admin UC PSC: ' . $e->getMessage());
        }
    }

    /**
     * Display the Adult Counseling registration form.
     */
    public function showDewasa()
    {
        return view('public.client-forms.dewasa');
    }

    /**
     * Store a new Adult Counseling registration form submission.
     */
    public function storeDewasa(Request $request)
    {
        $validated = $request->validate([
            // 1. Informed Consent
            'consent_agree' => 'required|in:Setuju,setuju,1',

            // 2. Data Diri
            'nama_lengkap' => 'required|string|max:255',
            'jenis_kelamin' => 'required|string|in:Laki-Laki,Perempuan',
            'tempat_lahir' => 'required|string|max:100',
            'tanggal_lahir' => 'required|date',
            'urutan_kelahiran' => 'required|string|max:100',
            'alamat' => 'required|string',
            'phone' => 'required|string|max:50',
            'agama' => 'required|string|max:50',
            'suku_bangsa' => 'required|string|max:50',
            'pendidikan_terakhir' => 'required|string|max:100',
            'pekerjaan' => 'required|string|max:150',
            'hobi' => 'required|string|max:150',
            'alasan_konseling' => 'required|string',

            // 3. Data Kondisi Psikologis
            'kondisi_saat_ini' => 'required|string',
            'pikiran_negatif' => 'required|string',
            'hal_ingin_ditingkatkan' => 'required|string',
            'karakter_kepribadian' => 'required|string',
            'ketakutan_phobia' => 'nullable|string',
            'jam_tidur' => 'required|string|max:50',
            'riwayat_trauma' => 'nullable|string',
            'riwayat_kesehatan' => 'nullable|string',

            // 4. Gambaran Relasi
            'relasi_ayah' => 'required|string',
            'relasi_ibu' => 'required|string',
            'relasi_saudara' => 'nullable|string',
            'relasi_pasangan' => 'nullable|string',
            'relasi_anak' => 'nullable|string',

            // 5. Lain-lain & Preferensi
            'pernah_konseling' => 'required|string|in:Ya,Tidak',
            'nama_konselor' => 'nullable|string|max:150',
            'kontak_darurat' => 'required|string|max:255',
            'sumber_info' => 'nullable|string|max:150',
            'preferensi_konseling' => 'required|string',
        ], [
            'consent_agree.required' => 'Anda wajib menyetujui lembar Informed Consent untuk melanjutkan.',
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'tempat_lahir.required' => 'Tempat lahir wajib diisi.',
            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi.',
            'urutan_kelahiran.required' => 'Urutan kelahiran wajib diisi (contoh: Anak ke-1 dari 3 bersaudara).',
            'alamat.required' => 'Alamat tempat tinggal saat ini wajib diisi.',
            'phone.required' => 'Nomor WhatsApp / HP aktif wajib diisi.',
            'agama.required' => 'Agama / kepercayaan wajib dipilih atau diisi.',
            'suku_bangsa.required' => 'Suku bangsa wajib diisi.',
            'pendidikan_terakhir.required' => 'Pendidikan terakhir wajib dipilih atau diisi.',
            'pekerjaan.required' => 'Pekerjaan saat ini wajib diisi.',
            'hobi.required' => 'Hobi atau minat kegemaran wajib diisi.',
            'alasan_konseling.required' => 'Alasan ingin melakukan konseling wajib diisi.',
            'kondisi_saat_ini.required' => 'Gambaran kondisi saat ini wajib diisi.',
            'pikiran_negatif.required' => 'Pikiran-pikiran negatif yang sering muncul wajib diisi.',
            'hal_ingin_ditingkatkan.required' => 'Hal-hal yang ingin ditingkatkan dalam hidup wajib diisi.',
            'karakter_kepribadian.required' => 'Karakter kepribadian Anda wajib diisi.',
            'jam_tidur.required' => 'Rata-rata jam tidur per malam wajib diisi.',
            'relasi_ayah.required' => 'Gambaran relasi dengan Ayah wajib diisi.',
            'relasi_ibu.required' => 'Gambaran relasi dengan Ibu wajib diisi.',
            'pernah_konseling.required' => 'Pilihan riwayat konseling sebelumnya wajib diisi.',
            'kontak_darurat.required' => 'Kontak darurat wajib diisi untuk keselamatan & protokol darurat.',
            'preferensi_konseling.required' => 'Preferensi proses konseling wajib dipilih.',
        ]);

        try {
            return DB::transaction(function () use ($request) {
                $gender = ($request->jenis_kelamin === 'Laki-Laki') ? 'l' : 'p';
                $isOnline = str_contains(strtolower($request->preferensi_konseling), 'online');
                $counselingType = $isOnline ? 'online' : 'offline';

                // Map source to valid enum ['whatsapp', 'walk_in', 'referral', 'other']
                $sourceEnum = match(strtolower($request->sumber_info ?? '')) {
                    'referensi dari kerabat', 'rujukan', 'teman' => 'referral',
                    'walk_in', 'langsung' => 'walk_in',
                    'media sosial', 'website', 'instagram' => 'other',
                    default => 'other',
                };

                // 1. Create or Find Client
                $client = Client::firstOrNew([
                    'name' => trim($request->nama_lengkap),
                    'phone' => trim($request->phone),
                ]);

                $client->jenis = 'individu';
                $client->gender = $gender;
                $client->birth_place = $request->tempat_lahir;
                $client->dob = $request->tanggal_lahir;
                $client->address = $request->alamat;
                $client->religion = $request->agama;
                $client->education = $request->pendidikan_terakhir;
                $client->occupation = $request->pekerjaan;
                $client->service_type = 'Konseling Dewasa';
                $client->counseling_type = $counselingType;
                $client->source = $sourceEnum;
                $client->status = 'unassigned';

                // Add rich intake note
                $notePrefix = "[Pendaftaran Mandiri Web - Konseling Dewasa]\n";
                $noteContent = "Pekerjaan: {$request->pekerjaan}\nAlasan Konseling: {$request->alasan_konseling}\nKontak Darurat: {$request->kontak_darurat}";
                $client->notes = $notePrefix . $noteContent;
                $client->save();

                // 2. Create ClientForm Record
                $ticketNumber = ClientForm::generateTicketNumber('dewasa');
                $clientForm = ClientForm::create([
                    'ticket_number' => $ticketNumber,
                    'form_type' => 'dewasa',
                    'client_id' => $client->id,
                    'booking_id' => null,
                    'client_name' => $client->name,
                    'client_phone' => trim($request->phone),
                    'consent_agreed' => true,
                    'consent_agreed_at' => now(),
                    'service_preference' => $counselingType,
                    'status' => 'baru',
                    'answers' => $request->except(['_token', 'consent_agree']),
                    'ip_address' => $request->ip(),
                ]);

                return redirect()->route('public.client-form.success', ['ticket' => $ticketNumber]);
            });
        } catch (\Throwable $e) {
            Log::error('Client Form Dewasa submission error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withInput()->with('error', 'Terjadi kesalahan saat memproses formulir pendaftaran. Silakan coba kembali atau hubungi admin UC PSC: ' . $e->getMessage());
        }
    }

    /**
     * Display the Pre-Marital Counseling registration form.
     */
    public function showPraNikah()
    {
        return view('public.client-forms.pra-nikah');
    }

    /**
     * Store a new Pre-Marital Counseling registration form submission.
     */
    public function storePraNikah(Request $request)
    {
        $validated = $request->validate([
            // 1. Informed Consent
            'consent_agree' => 'required|in:Setuju,setuju,1',

            // 2. Data Diri Anda
            'nama_lengkap' => 'required|string|max:255',
            'jenis_kelamin' => 'required|string|in:Laki-Laki,Perempuan',
            'tempat_lahir' => 'required|string|max:100',
            'tanggal_lahir' => 'required|date',
            'urutan_kelahiran' => 'required|string|max:100',
            'alamat' => 'required|string',
            'phone' => 'required|string|max:50',
            'email' => 'nullable|email|max:150',
            'agama' => 'required|string|max:50',
            'suku_bangsa' => 'required|string|max:50',
            'pendidikan_terakhir' => 'required|string|max:100',
            'pekerjaan' => 'required|string|max:150',
            'pernikahan_ke' => 'required|string|max:50',
            'jumlah_anak' => 'required|string|max:50',
            'alasan_konseling' => 'required|string',

            // 3. Data Diri Pasangan
            'nama_pasangan' => 'required|string|max:255',
            'jenis_kelamin_pasangan' => 'required|string|in:Laki-Laki,Perempuan',
            'tempat_lahir_pasangan' => 'required|string|max:100',
            'tanggal_lahir_pasangan' => 'required|date',
            'urutan_kelahiran_pasangan' => 'required|string|max:100',
            'alamat_pasangan' => 'required|string',
            'phone_pasangan' => 'required|string|max:50',
            'email_pasangan' => 'nullable|email|max:150',
            'agama_pasangan' => 'required|string|max:50',
            'suku_bangsa_pasangan' => 'required|string|max:50',
            'pendidikan_terakhir_pasangan' => 'required|string|max:100',
            'pekerjaan_pasangan' => 'required|string|max:150',
            'pernikahan_pasangan_ke' => 'required|string|max:50',
            'jumlah_anak_pasangan' => 'required|string|max:50',

            // 4. Data Pra Nikah
            'tanggal_rencana_pernikahan' => 'required|string|max:100',
            'lama_berkenalan' => 'required|string|max:100',
            'lama_berpacaran' => 'required|string|max:100',

            // 5. Data Kondisi Pra Nikah
            'harapan_pernikahan' => 'required|string',
            'hal_ingin_ditingkatkan' => 'required|string',
            'kelebihan_peran' => 'required|string',
            'kekurangan_peran' => 'required|string',
            'keluhan_hubungan' => 'nullable|string',
            'riwayat_trauma' => 'nullable|string',
            'riwayat_kesehatan' => 'nullable|string',

            // 6. Lain-Lain
            'pernah_konseling' => 'required|string|in:Ya,Tidak',
            'nama_konselor' => 'nullable|string|max:150',
            'kontak_darurat' => 'required|string|max:255',
            'sumber_info' => 'nullable|string|max:150',
            'preferensi_konseling' => 'required|string',
        ], [
            'consent_agree.required' => 'Anda wajib menyetujui Lembar Persetujuan (Informed Consent).',
            'nama_lengkap.required' => 'Nama lengkap Anda wajib diisi.',
            'jenis_kelamin.required' => 'Pilih jenis kelamin Anda.',
            'phone.required' => 'Nomor WhatsApp / HP Anda wajib diisi.',
            'nama_pasangan.required' => 'Nama lengkap calon pasangan wajib diisi.',
            'phone_pasangan.required' => 'Nomor WhatsApp / HP calon pasangan wajib diisi.',
            'tanggal_rencana_pernikahan.required' => 'Tanggal rencana pernikahan wajib diisi.',
            'harapan_pernikahan.required' => 'Harapan pernikahan wajib diisi.',
            'kelebihan_peran.required' => 'Kelebihan peran Anda wajib diisi.',
            'kekurangan_peran.required' => 'Kekurangan peran Anda wajib diisi.',
            'kontak_darurat.required' => 'Kontak darurat wajib diisi.',
            'preferensi_konseling.required' => 'Preferensi proses konseling wajib dipilih.',
        ]);

        try {
            return DB::transaction(function () use ($request) {
                $gender = ($request->jenis_kelamin === 'Laki-Laki') ? 'l' : 'p';
                $isOnline = str_contains(strtolower($request->preferensi_konseling), 'online');
                $counselingType = $isOnline ? 'online' : 'offline';

                $client = Client::firstOrNew([
                    'name' => trim($request->nama_lengkap) . ' & ' . trim($request->nama_pasangan),
                    'phone' => trim($request->phone),
                ]);

                $client->jenis = 'individu';
                $client->gender = $gender;
                $client->birth_place = $request->tempat_lahir;
                $client->dob = $request->tanggal_lahir;
                $client->address = $request->alamat;
                $client->religion = $request->agama;
                $client->education = $request->pendidikan_terakhir;
                $client->occupation = $request->pekerjaan;
                $client->service_type = 'Konseling Pra-Nikah';
                $client->counseling_type = $counselingType;
                $client->source = 'other';
                $client->status = 'unassigned';

                $notePrefix = "[Pendaftaran Mandiri Web - Konseling Pra-Nikah]\n";
                $noteContent = "Pasangan: {$request->nama_pasangan} ({$request->phone_pasangan})\nRencana Nikah: {$request->tanggal_rencana_pernikahan}\nAlasan: {$request->alasan_konseling}\nKontak Darurat: {$request->kontak_darurat}";
                $client->notes = $notePrefix . $noteContent;
                $client->save();

                $ticketNumber = ClientForm::generateTicketNumber('pra_nikah');
                $clientForm = ClientForm::create([
                    'ticket_number' => $ticketNumber,
                    'form_type' => 'pra_nikah',
                    'client_id' => $client->id,
                    'booking_id' => null,
                    'client_name' => $client->name,
                    'client_phone' => trim($request->phone),
                    'consent_agreed' => true,
                    'consent_agreed_at' => now(),
                    'service_preference' => $counselingType,
                    'status' => 'baru',
                    'answers' => $request->except(['_token', 'consent_agree']),
                    'ip_address' => $request->ip(),
                ]);

                return redirect()->route('public.client-form.success', ['ticket' => $ticketNumber]);
            });
        } catch (\Throwable $e) {
            Log::error('Client Form Pra-Nikah submission error: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Terjadi kesalahan saat memproses formulir: ' . $e->getMessage());
        }
    }

    /**
     * Display the Marriage Counseling registration form.
     */
    public function showPernikahan()
    {
        return view('public.client-forms.pernikahan');
    }

    /**
     * Store a new Marriage Counseling registration form submission.
     */
    public function storePernikahan(Request $request)
    {
        $validated = $request->validate([
            // 1. Informed Consent
            'consent_agree' => 'required|in:Setuju,setuju,1',

            // 2. Data Diri Anda
            'nama_lengkap' => 'required|string|max:255',
            'jenis_kelamin' => 'required|string|in:Laki-Laki,Perempuan',
            'tempat_lahir' => 'required|string|max:100',
            'tanggal_lahir' => 'required|date',
            'urutan_kelahiran' => 'required|string|max:100',
            'alamat' => 'required|string',
            'phone' => 'required|string|max:50',
            'email' => 'nullable|email|max:150',
            'agama' => 'required|string|max:50',
            'suku_bangsa' => 'required|string|max:50',
            'pendidikan_terakhir' => 'required|string|max:100',
            'pekerjaan' => 'required|string|max:150',
            'pernikahan_ke' => 'required|string|max:50',
            'jumlah_anak' => 'required|string|max:50',
            'alasan_konseling' => 'required|string',

            // 3. Data Diri Pasangan
            'nama_pasangan' => 'required|string|max:255',
            'jenis_kelamin_pasangan' => 'required|string|in:Laki-Laki,Perempuan',
            'tempat_lahir_pasangan' => 'required|string|max:100',
            'tanggal_lahir_pasangan' => 'required|date',
            'urutan_kelahiran_pasangan' => 'required|string|max:100',
            'alamat_pasangan' => 'required|string',
            'phone_pasangan' => 'required|string|max:50',
            'email_pasangan' => 'nullable|email|max:150',
            'agama_pasangan' => 'required|string|max:50',
            'suku_bangsa_pasangan' => 'required|string|max:50',
            'pendidikan_terakhir_pasangan' => 'required|string|max:100',
            'pekerjaan_pasangan' => 'required|string|max:150',
            'pernikahan_pasangan_ke' => 'required|string|max:50',
            'jumlah_anak_pasangan' => 'required|string|max:50',

            // 4. Data Pernikahan
            'tempat_tanggal_pernikahan' => 'required|string|max:150',
            'lama_pernikahan' => 'required|string|max:100',
            'lama_berkenalan' => 'required|string|max:100',
            'lama_berpacaran' => 'required|string|max:100',

            // 5. Data Kondisi Pernikahan
            'keluhan_utama' => 'required|string',
            'kemungkinan_perbaikan' => 'nullable|string',
            'hal_ingin_ditingkatkan' => 'required|string',
            'kelebihan_peran' => 'required|string',
            'kekurangan_peran' => 'required|string',
            'riwayat_trauma' => 'nullable|string',
            'riwayat_kesehatan' => 'nullable|string',

            // 6. Lain-Lain
            'pernah_konseling' => 'required|string|in:Ya,Tidak',
            'nama_konselor' => 'nullable|string|max:150',
            'kontak_darurat' => 'required|string|max:255',
            'sumber_info' => 'nullable|string|max:150',
            'preferensi_konseling' => 'required|string',
        ], [
            'consent_agree.required' => 'Anda wajib menyetujui Lembar Persetujuan (Informed Consent).',
            'nama_lengkap.required' => 'Nama lengkap Anda wajib diisi.',
            'phone.required' => 'Nomor WhatsApp / HP Anda wajib diisi.',
            'nama_pasangan.required' => 'Nama lengkap pasangan wajib diisi.',
            'phone_pasangan.required' => 'Nomor WhatsApp / HP pasangan wajib diisi.',
            'tempat_tanggal_pernikahan.required' => 'Tempat & tanggal pernikahan wajib diisi.',
            'lama_pernikahan.required' => 'Lama usia pernikahan wajib diisi.',
            'keluhan_utama.required' => 'Keluhan utama dalam pernikahan wajib diisi.',
            'kontak_darurat.required' => 'Kontak darurat wajib diisi.',
            'preferensi_konseling.required' => 'Preferensi proses konseling wajib dipilih.',
        ]);

        try {
            return DB::transaction(function () use ($request) {
                $gender = ($request->jenis_kelamin === 'Laki-Laki') ? 'l' : 'p';
                $isOnline = str_contains(strtolower($request->preferensi_konseling), 'online');
                $counselingType = $isOnline ? 'online' : 'offline';

                $client = Client::firstOrNew([
                    'name' => trim($request->nama_lengkap) . ' & ' . trim($request->nama_pasangan),
                    'phone' => trim($request->phone),
                ]);

                $client->jenis = 'individu';
                $client->gender = $gender;
                $client->birth_place = $request->tempat_lahir;
                $client->dob = $request->tanggal_lahir;
                $client->address = $request->alamat;
                $client->religion = $request->agama;
                $client->education = $request->pendidikan_terakhir;
                $client->occupation = $request->pekerjaan;
                $client->service_type = 'Konseling Pernikahan';
                $client->counseling_type = $counselingType;
                $client->source = 'other';
                $client->status = 'unassigned';

                $notePrefix = "[Pendaftaran Mandiri Web - Konseling Pernikahan]\n";
                $noteContent = "Pasangan: {$request->nama_pasangan}\nLama Pernikahan: {$request->lama_pernikahan}\nKeluhan: {$request->keluhan_utama}\nKontak Darurat: {$request->kontak_darurat}";
                $client->notes = $notePrefix . $noteContent;
                $client->save();

                $ticketNumber = ClientForm::generateTicketNumber('pernikahan');
                $clientForm = ClientForm::create([
                    'ticket_number' => $ticketNumber,
                    'form_type' => 'pernikahan',
                    'client_id' => $client->id,
                    'booking_id' => null,
                    'client_name' => $client->name,
                    'client_phone' => trim($request->phone),
                    'consent_agreed' => true,
                    'consent_agreed_at' => now(),
                    'service_preference' => $counselingType,
                    'status' => 'baru',
                    'answers' => $request->except(['_token', 'consent_agree']),
                    'ip_address' => $request->ip(),
                ]);

                return redirect()->route('public.client-form.success', ['ticket' => $ticketNumber]);
            });
        } catch (\Throwable $e) {
            Log::error('Client Form Pernikahan submission error: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Terjadi kesalahan saat memproses formulir: ' . $e->getMessage());
        }
    }

    /**
     * Display the English Biography registration form.
     */
    public function showBiographyEn()
    {
        return view('public.client-forms.biography-en');
    }

    /**
     * Store a new English Biography registration form submission.
     */
    public function storeBiographyEn(Request $request)
    {
        $validated = $request->validate([
            'consent_agree' => 'required',
            'full_name' => 'required|string|max:255',
            'gender' => 'required|string|in:Male,Female',
            'birth_place_date' => 'required|string|max:150',
            'birth_order' => 'required|string|max:100',
            'current_address' => 'required|string',
            'phone' => 'required|string|max:50',
            'email' => 'nullable|email|max:150',
            'religion' => 'required|string|max:50',
            'ethnicity' => 'required|string|max:50',
            'last_education' => 'required|string|max:100',
        ], [
            'consent_agree.required' => 'You must agree to the Informed Consent to continue.',
            'full_name.required' => 'Full name is required.',
            'gender.required' => 'Please select your gender.',
            'phone.required' => 'Active WhatsApp/Phone number is required.',
            'current_address.required' => 'Current address is required.',
        ]);

        try {
            return DB::transaction(function () use ($request) {
                $gender = ($request->gender === 'Male') ? 'l' : 'p';
                $pref = strtolower($request->preferred_counseling ?? 'offline');
                $counselingType = str_contains($pref, 'online') ? 'online' : 'offline';

                $client = Client::firstOrNew([
                    'name' => trim($request->full_name),
                    'phone' => trim($request->phone),
                ]);

                $client->jenis = 'individu';
                $client->gender = $gender;
                $client->birth_place = $request->birth_place_date;
                $client->address = $request->current_address;
                $client->religion = $request->religion;
                $client->education = $request->last_education;
                $client->occupation = $request->occupation ?? 'General Client';
                $client->service_type = 'Biography Form (English)';
                $client->counseling_type = $counselingType;
                $client->source = 'other';
                $client->status = 'unassigned';

                $client->notes = "[Web Intake - English Biography Form]\nEducation: {$request->last_education}\nDream Job: " . ($request->dream_job ?? '-');
                $client->save();

                $ticketNumber = ClientForm::generateTicketNumber('biography_en');
                $clientForm = ClientForm::create([
                    'ticket_number' => $ticketNumber,
                    'form_type' => 'biography_en',
                    'client_id' => $client->id,
                    'booking_id' => null,
                    'client_name' => $client->name,
                    'client_phone' => trim($request->phone),
                    'consent_agreed' => true,
                    'consent_agreed_at' => now(),
                    'service_preference' => $counselingType,
                    'status' => 'baru',
                    'answers' => $request->except(['_token', 'consent_agree']),
                    'ip_address' => $request->ip(),
                ]);

                return redirect()->route('public.client-form.success', ['ticket' => $ticketNumber]);
            });
        } catch (\Throwable $e) {
            Log::error('Biography EN submission error: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Error processing submission: ' . $e->getMessage());
        }
    }

    /**
     * Display the Non-Industrial Curriculum Vitae registration form.
     */
    public function showNonIndustri()
    {
        return view('public.client-forms.non-industri');
    }

    /**
     * Store a new Non-Industrial Curriculum Vitae registration form submission.
     */
    public function storeNonIndustri(Request $request)
    {
        $validated = $request->validate([
            'consent_agree' => 'required|in:Setuju,setuju,1',
            'nama_lengkap' => 'required|string|max:255',
            'jenis_kelamin' => 'required|string|in:Laki-Laki,Perempuan',
            'tempat_tanggal_lahir' => 'required|string|max:150',
            'urutan_kelahiran' => 'required|string|max:100',
            'alamat_sekarang' => 'required|string',
            'asal_kota' => 'required|string|max:100',
            'phone' => 'required|string|max:50',
            'agama' => 'required|string|max:50',
            'suku_bangsa' => 'required|string|max:50',
            'pendidikan_terakhir' => 'required|string|max:100',
        ], [
            'consent_agree.required' => 'Anda wajib menyetujui pernyataan kejujuran untuk melanjutkan.',
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'jenis_kelamin.required' => 'Pilih jenis kelamin.',
            'tempat_tanggal_lahir.required' => 'Tempat & tanggal lahir wajib diisi.',
            'alamat_sekarang.required' => 'Alamat tempat tinggal sekarang wajib diisi.',
            'asal_kota.required' => 'Asal kota wajib diisi.',
            'phone.required' => 'Nomor Telp/HP/WhatsApp aktif wajib diisi.',
            'pendidikan_terakhir.required' => 'Pendidikan terakhir berijazah wajib diisi.',
        ]);

        try {
            return DB::transaction(function () use ($request) {
                $gender = ($request->jenis_kelamin === 'Laki-Laki') ? 'l' : 'p';

                $client = Client::firstOrNew([
                    'name' => trim($request->nama_lengkap),
                    'phone' => trim($request->phone),
                ]);

                $client->jenis = 'individu';
                $client->gender = $gender;
                $client->birth_place = $request->tempat_tanggal_lahir;
                $client->address = $request->alamat_sekarang;
                $client->religion = $request->agama;
                $client->education = $request->pendidikan_terakhir;
                $client->occupation = 'Peserta Non-Industri';
                $client->service_type = 'Layanan Non-Industri';
                $client->counseling_type = 'offline';
                $client->source = 'other';
                $client->status = 'unassigned';

                $client->notes = "[Asesmen Non-Industri]\nAsal Kota: {$request->asal_kota}\nPendidikan: {$request->pendidikan_terakhir}\nCita-cita: " . ($request->cita_cita ?? '-');
                $client->save();

                $ticketNumber = ClientForm::generateTicketNumber('non_industri');
                $clientForm = ClientForm::create([
                    'ticket_number' => $ticketNumber,
                    'form_type' => 'non_industri',
                    'client_id' => $client->id,
                    'booking_id' => null,
                    'client_name' => $client->name,
                    'client_phone' => trim($request->phone),
                    'consent_agreed' => true,
                    'consent_agreed_at' => now(),
                    'service_preference' => 'offline',
                    'status' => 'baru',
                    'answers' => $request->except(['_token', 'consent_agree']),
                    'ip_address' => $request->ip(),
                ]);

                return redirect()->route('public.client-form.success', ['ticket' => $ticketNumber]);
            });
        } catch (\Throwable $e) {
            Log::error('Non-Industri submission error: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Terjadi kesalahan saat memproses formulir: ' . $e->getMessage());
        }
    }

    /**
     * Display the Industrial Curriculum Vitae registration form.
     */
    public function showIndustri()
    {
        return view('public.client-forms.industri');
    }

    /**
     * Store a new Industrial Curriculum Vitae registration form submission.
     */
    public function storeIndustri(Request $request)
    {
        $validated = $request->validate([
            'consent_agree' => 'required|in:Setuju,setuju,1',
            'nama_lengkap' => 'required|string|max:255',
            'jenis_kelamin' => 'required|string|in:Laki-Laki,Perempuan',
            'tempat_tanggal_lahir' => 'required|string|max:150',
            'urutan_kelahiran' => 'required|string|max:100',
            'alamat_sekarang' => 'required|string',
            'asal_kota' => 'required|string|max:100',
            'phone' => 'required|string|max:50',
            'agama' => 'required|string|max:50',
            'suku_bangsa' => 'required|string|max:50',
            'status_perkawinan' => 'required|string|max:50',
            'pendidikan_terakhir' => 'required|string|max:100',
            'pekerjaan_saat_ini' => 'required|string|max:150',
            'posisi_dituju' => 'required|string|max:150',
        ], [
            'consent_agree.required' => 'Anda wajib menyetujui pernyataan kejujuran untuk melanjutkan.',
            'nama_lengkap.required' => 'Nama lengkap beserta gelar (jika ada) wajib diisi.',
            'jenis_kelamin.required' => 'Pilih jenis kelamin.',
            'tempat_tanggal_lahir.required' => 'Tempat & tanggal lahir wajib diisi.',
            'alamat_sekarang.required' => 'Alamat tempat tinggal sekarang wajib diisi.',
            'phone.required' => 'Nomor Telp/HP/WhatsApp aktif wajib diisi.',
            'posisi_dituju.required' => 'Posisi pekerjaan yang dituju wajib diisi.',
            'pekerjaan_saat_ini.required' => 'Pekerjaan saat ini wajib diisi.',
        ]);

        try {
            return DB::transaction(function () use ($request) {
                $gender = ($request->jenis_kelamin === 'Laki-Laki') ? 'l' : 'p';

                $client = Client::firstOrNew([
                    'name' => trim($request->nama_lengkap),
                    'phone' => trim($request->phone),
                ]);

                $client->jenis = 'industri';
                $client->gender = $gender;
                $client->birth_place = $request->tempat_tanggal_lahir;
                $client->address = $request->alamat_sekarang;
                $client->religion = $request->agama;
                $client->education = $request->pendidikan_terakhir;
                $client->occupation = $request->pekerjaan_saat_ini;
                $client->service_type = 'Layanan Industri';
                $client->counseling_type = 'offline';
                $client->source = 'other';
                $client->status = 'unassigned';

                $client->notes = "[Asesmen Industri & Korporat]\nPosisi Dituju: {$request->posisi_dituju}\nKota: {$request->asal_kota}\nPekerjaan Saat Ini: {$request->pekerjaan_saat_ini}\nEkspektasi Gaji: " . ($request->ekspektasi_gaji ?? '-');
                $client->save();

                $ticketNumber = ClientForm::generateTicketNumber('industri');
                $clientForm = ClientForm::create([
                    'ticket_number' => $ticketNumber,
                    'form_type' => 'industri',
                    'client_id' => $client->id,
                    'booking_id' => null,
                    'client_name' => $client->name,
                    'client_phone' => trim($request->phone),
                    'consent_agreed' => true,
                    'consent_agreed_at' => now(),
                    'service_preference' => 'offline',
                    'status' => 'baru',
                    'answers' => $request->except(['_token', 'consent_agree']),
                    'ip_address' => $request->ip(),
                ]);

                return redirect()->route('public.client-form.success', ['ticket' => $ticketNumber]);
            });
        } catch (\Throwable $e) {
            Log::error('Industri submission error: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Terjadi kesalahan saat memproses formulir: ' . $e->getMessage());
        }
    }

    /**
     * Display the submission success page with Ticket Number.
     * Supports direct preview mode without having to fill out forms.
     */
    public function success(?string $ticket = null)
    {
        $clientForm = null;

        if ($ticket && !in_array(strtolower($ticket), ['preview', 'demo', 'test'])) {
            $clientForm = ClientForm::where('ticket_number', $ticket)->first();
        }

        // Ambil data submission terbaru atau fallback dummy jika preview
        if (!$clientForm) {
            $clientForm = ClientForm::latest()->first();
        }

        if (!$clientForm) {
            $clientForm = new ClientForm([
                'ticket_number' => 'PSC-ANK-260926-DEMO',
                'form_type' => 'anak',
                'client_name' => 'Ananda Bintang Pratama',
                'client_phone' => '081234567890',
                'service_preference' => 'Offline di UCPSC',
                'status' => 'baru',
            ]);
        }

        return view('public.client-forms.success', compact('clientForm'));
    }
}
