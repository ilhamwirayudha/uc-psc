<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Counselor;
use App\Models\Pairing;
use App\Models\User;
use App\Models\ClientStaffAssignment;
use App\Models\Booking;
use App\Models\BookingParticipant;
use App\Models\ClientParticipant;
use App\Models\ClientForm;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ClientController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $query = Client::with('creator');

        // Staff hanya melihat klien yang ditugaskan kepadanya
        if (auth()->user()->role === 'staff') {
            $query->where('assigned_staff_id', auth()->id());
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('pic_name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%')
                  ->orWhere('city', 'like', '%' . $request->search . '%')
                  ->orWhere('occupation', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        if ($request->filled('jenis')) {
            $j = strtolower($request->jenis);
            if (in_array($j, ['individu', 'individual'])) {
                $query->where(function($q) {
                    $q->whereIn('jenis', ['individu', 'individual', 'group', 'pasangan'])->orWhereNull('jenis');
                });
            } elseif (in_array($j, ['industri', 'company', 'perusahaan'])) {
                $query->whereIn('jenis', ['industri', 'company', 'perusahaan']);
            } else {
                $query->where('jenis', $request->jenis);
            }
        }

        if ($request->filled('service_type')) {
            $query->where('service_type', $request->service_type);
        }

        $sort = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');

        if (in_array($sort, ['creator', 'created_by'])) {
            $query->leftJoin('users', 'clients.created_by', '=', 'users.id')
                  ->orderBy('users.name', $direction)
                  ->select('clients.*');
        } elseif ($sort === 'jenis') {
            if ($direction === 'desc') {
                $query->orderByRaw("CASE 
                    WHEN clients.jenis IN ('industri', 'company', 'perusahaan') THEN 1 
                    ELSE 2 END ASC")
                    ->orderBy('clients.created_at', 'desc');
            } else {
                $query->orderByRaw("CASE 
                    WHEN clients.jenis IN ('industri', 'company', 'perusahaan') THEN 2 
                    ELSE 1 END ASC")
                    ->orderBy('clients.created_at', 'desc');
            }
        } elseif (in_array($sort, ['id', 'name', 'created_at', 'updated_at', 'service_type', 'city', 'phone', 'email'])) {
            $query->orderBy('clients.' . $sort, $direction);
        } else {
            $query->orderBy('clients.created_at', $direction);
        }

        $perPage = in_array((int) $request->get('per_page', 25), [10, 25, 50, 100])
            ? (int) $request->get('per_page', 25)
            : 25;

        $clients = $query->paginate($perPage)->withQueryString();

        return view('clients.index', compact('clients', 'sort', 'direction', 'perPage'));
    }

    public function create(Request $request)
    {
        $initialJenis = $request->get('jenis', '');
        $initialFormType = $request->get('form_type', '');
        return view('clients.create', compact('initialJenis', 'initialFormType'));
    }

    public function store(Request $request)
    {
        $formType = $request->input('form_type');

        if (empty($formType)) {
            return back()->withInput()->withErrors(['form_type' => 'Silakan pilih formulir layanan klien terlebih dahulu.']);
        }

        if ($formType === 'dewasa') {
            return $this->storeDewasaForm($request);
        } elseif ($formType === 'anak') {
            return $this->storeAnakForm($request);
        } elseif ($formType === 'pra_nikah') {
            return $this->storePraNikahForm($request);
        } elseif ($formType === 'pernikahan') {
            return $this->storePernikahanForm($request);
        } elseif ($formType === 'biography_en') {
            return $this->storeBiographyEnForm($request);
        } elseif ($formType === 'non_industri') {
            return $this->storeNonIndustriForm($request);
        } elseif ($formType === 'industri') {
            return $this->storeIndustriForm($request);
        }

        $rawJenis = $request->input('jenis', 'individu');
        $jenis = in_array(strtolower($rawJenis), ['industri', 'company', 'perusahaan']) ? 'industri' : 'individu';
        $isIndustri = $jenis === 'industri';

        $rules = [
            'service_type' => 'nullable|in:konseling,psikotes',
            'jenis' => 'nullable|in:individu,industri,individual,company,group',
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[^0-9]+$/u',
            ],
            'phone' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $digits = preg_replace('/[^0-9]/', '', $value);
                    $len = strlen($digits);
                    if ($len < 10 || $len > 13) {
                        $fail('Jumlah nomor telepon / WhatsApp harus berjumlah antara 10 hingga 13 digit angka.');
                    }
                },
            ],
            'email' => 'required|email|max:255',
            'gender' => 'required|in:l,p,non_binary,transgender,prefer_not_to_say,other',
            'dob' => 'required|date',
            'birth_place' => 'required|string|max:255',
            'religion' => 'required|string|max:100',
            'marital_status' => 'nullable|string|max:100',
            'education' => 'required|string|max:100',
            'address' => 'required|string|max:1000',
            'country' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'source' => 'nullable|string|max:255',
            'status' => 'nullable|in:unassigned,assigned,ongoing,unpaid,paid,needs_followup,completed',
            'notes' => 'nullable|string',
            'kontak_darurat' => 'nullable|string|max:255',
            'suku_bangsa' => 'nullable|string|max:100',
            'occupation' => $isIndustri ? 'required|string|max:255' : 'nullable|string|max:255',
            'posisi_dituju' => 'nullable|string|max:255',
            'pic_name' => $isIndustri
                ? ['required', 'string', 'max:255', 'regex:/^[^0-9]+$/u']
                : ['nullable', 'string', 'max:255', 'regex:/^[^0-9]+$/u'],
        ];

        $validated = $request->validate($rules, [
            'name.regex' => 'Nama tidak boleh mengandung angka. Hanya teks dan simbol yang diperbolehkan.',
            'pic_name.required' => 'Nama perusahaan wajib diisi untuk klien industri.',
            'pic_name.regex' => 'Nama perusahaan / PIC tidak boleh mengandung angka.',
            'dob.required' => 'Tanggal lahir wajib diisi.',
            'birth_place.required' => 'Tempat lahir wajib diisi.',
            'gender.required' => 'Jenis kelamin wajib dipilih.',
            'religion.required' => 'Agama wajib dipilih.',
            'education.required' => 'Pendidikan terakhir wajib dipilih.',
            'address.required' => 'Alamat lengkap wajib diisi.',
            'occupation.required' => 'Pekerjaan saat ini wajib diisi untuk klien industri.',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['jenis'] = $jenis;
        $validated['status'] = $validated['status'] ?? 'unassigned';

        if ($isIndustri && empty($validated['service_type'])) {
            $validated['service_type'] = 'psikotes';
        }

        $extraNotes = [];
        if (!empty($validated['suku_bangsa'])) {
            $extraNotes[] = "Suku Bangsa: " . $validated['suku_bangsa'];
        }
        if (!empty($validated['posisi_dituju'])) {
            $extraNotes[] = "Posisi yang Dituju: " . $validated['posisi_dituju'];
        }
        if (!empty($validated['kontak_darurat'])) {
            $extraNotes[] = "Kontak Darurat: " . $validated['kontak_darurat'];
        }

        if (!empty($extraNotes)) {
            $currentNotes = trim($validated['notes'] ?? '');
            $validated['notes'] = implode("\n\n", array_filter([$currentNotes, implode("\n", $extraNotes)]));
        }

        unset($validated['suku_bangsa'], $validated['kontak_darurat'], $validated['posisi_dituju'], $validated['participants']);

        $client = Client::create($validated);

        return redirect()->route('clients.index')
            ->with('success', 'Data klien berhasil ditambahkan.');
    }

    protected function storeDewasaForm(Request $request)
    {
        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'jenis_kelamin' => 'required|string|in:Laki-Laki,Perempuan',
            'tempat_lahir' => 'required|string|max:100',
            'tanggal_lahir' => 'required|date',
            'alamat' => 'required|string',
            'phone' => 'required|string|max:50',
            'email' => 'required|email|max:255',
            'agama' => 'required|string|max:50',
            'pendidikan_terakhir' => 'required|string|max:100',
            'consent_agree' => 'required',
            'urutan_kelahiran' => 'nullable|string|max:100',
            'suku_bangsa' => 'nullable|string|max:50',
            'pekerjaan' => 'nullable|string|max:150',
            'hobi' => 'nullable|string|max:150',
            'alasan_konseling' => 'nullable|string',
            'kondisi_saat_ini' => 'nullable|string',
            'pikiran_negatif' => 'nullable|string',
            'hal_ingin_ditingkatkan' => 'nullable|string',
            'karakter_kepribadian' => 'nullable|string',
            'ketakutan_phobia' => 'nullable|string',
            'jam_tidur' => 'nullable|string|max:50',
            'riwayat_trauma' => 'nullable|string',
            'riwayat_kesehatan' => 'nullable|string',
            'relasi_ayah' => 'nullable|string',
            'relasi_ibu' => 'nullable|string',
            'relasi_saudara' => 'nullable|string',
            'relasi_pasangan' => 'nullable|string',
            'relasi_anak' => 'nullable|string',
            'pernah_konseling' => 'nullable|string',
            'nama_konselor' => 'nullable|string|max:150',
            'kontak_darurat' => 'nullable|string|max:255',
            'sumber_info' => 'nullable|string|max:150',
            'preferensi_konseling' => 'nullable|string',
        ], [
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'tempat_lahir.required' => 'Tempat lahir wajib diisi.',
            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi.',
            'alamat.required' => 'Alamat tempat tinggal saat ini wajib diisi.',
            'phone.required' => 'Nomor WhatsApp / HP aktif wajib diisi.',
            'email.required' => 'Alamat email aktif wajib diisi.',
            'agama.required' => 'Agama / kepercayaan wajib dipilih atau diisi.',
            'pendidikan_terakhir.required' => 'Pendidikan terakhir wajib dipilih atau diisi.',
            'consent_agree.required' => 'Lembar persetujuan (informed consent) wajib disetujui.',
        ]);

        return DB::transaction(function () use ($request) {
            $gender = ($request->jenis_kelamin === 'Laki-Laki') ? 'l' : 'p';
            $isOnline = str_contains(strtolower($request->preferensi_konseling ?? ''), 'online');
            $counselingType = $isOnline ? 'online' : 'offline';

            $client = Client::firstOrNew(['phone' => trim($request->phone)]);
            $client->name = trim($request->nama_lengkap);
            $client->jenis = 'individu';
            $client->gender = $gender;
            $client->birth_place = $request->tempat_lahir;
            $client->dob = $request->tanggal_lahir;
            $client->address = $request->alamat;
            $client->religion = $request->agama;
            $client->education = $request->pendidikan_terakhir;
            $client->occupation = $request->pekerjaan;
            $client->email = $request->email;
            $client->service_type = 'Konseling Dewasa';
            $client->counseling_type = $counselingType;
            $client->source = 'other';
            $client->status = 'unassigned';
            $client->created_by = auth()->id();
            $client->save();

            $ticketNumber = ClientForm::generateTicketNumber('dewasa');
            ClientForm::create([
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
                'answers' => $request->except(['_token', 'from_client_modal', 'form_type', 'consent_agree']),
                'ip_address' => $request->ip(),
            ]);

            return redirect()->route('clients.index')
                ->with('success', 'Klien ' . $client->name . ' berhasil ditambahkan melalui Formulir Riwayat Hidup Konseling - Dewasa (Tiket: ' . $ticketNumber . ').');
        });
    }

    protected function storeAnakForm(Request $request)
    {
        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'jenis_kelamin' => 'required|string|in:Laki-Laki,Perempuan',
            'tempat_lahir' => 'required|string|max:100',
            'tanggal_lahir' => 'required|date',
            'alamat' => 'required|string',
            'phone' => 'required|string|max:50',
            'email' => 'required|email|max:255',
            'agama' => 'required|string|max:50',
            'pendidikan_terakhir' => 'required|string|max:100',
            'consent_agree' => 'required',
            'urutan_kelahiran' => 'nullable|string|max:100',
            'suku_bangsa' => 'nullable|string|max:50',
            'status_pernikahan_ortu' => 'nullable|string|max:100',
            'alasan_konseling' => 'nullable|string',
            'kondisi_anak_saat_ini' => 'nullable|string',
            'hal_ingin_ditingkatkan' => 'nullable|string',
            'karakter_anak' => 'nullable|string',
            'prestasi_anak' => 'nullable|string',
            'ketakutan_phobia' => 'nullable|string',
            'riwayat_trauma' => 'nullable|string',
            'riwayat_kesehatan' => 'nullable|string',
            'relasi_ayah' => 'nullable|string',
            'relasi_ibu' => 'nullable|string',
            'relasi_saudara' => 'nullable|string',
            'relasi_teman' => 'nullable|string',
            'nama_ayah' => 'nullable|string|max:255',
            'jenis_kelamin_ayah' => 'nullable|string|max:50',
            'usia_ayah' => 'nullable|string|max:50',
            'pendidikan_ayah' => 'nullable|string|max:100',
            'pekerjaan_ayah' => 'nullable|string|max:100',
            'agama_ayah' => 'nullable|string|max:50',
            'urutan_kelahiran_ayah' => 'nullable|string|max:100',
            'alamat_ayah' => 'nullable|string',
            'phone_ayah' => 'nullable|string|max:50',
            'pernikahan_ayah_ke' => 'nullable|string|max:50',
            'jumlah_anak_ayah' => 'nullable|string|max:50',
            'status_nikah_ayah' => 'nullable|string|max:50',
            'nama_ibu' => 'nullable|string|max:255',
            'jenis_kelamin_ibu' => 'nullable|string|max:50',
            'usia_ibu' => 'nullable|string|max:50',
            'pendidikan_ibu' => 'nullable|string|max:100',
            'pekerjaan_ibu' => 'nullable|string|max:100',
            'agama_ibu' => 'nullable|string|max:50',
            'urutan_kelahiran_ibu' => 'nullable|string|max:100',
            'alamat_ibu' => 'nullable|string',
            'phone_ibu' => 'nullable|string|max:50',
            'pernikahan_ibu_ke' => 'nullable|string|max:50',
            'jumlah_anak_ibu' => 'nullable|string|max:50',
            'status_nikah_ibu' => 'nullable|string|max:50',
            'pernah_konseling' => 'nullable|string|max:50',
            'nama_konselor_lama' => 'nullable|string|max:255',
            'kontak_darurat' => 'nullable|string|max:255',
            'sumber_info' => 'nullable|string|max:100',
            'preferensi_konseling' => 'nullable|string|max:100',
        ], [
            'nama_lengkap.required' => 'Nama lengkap anak wajib diisi.',
            'jenis_kelamin.required' => 'Pilih jenis kelamin anak.',
            'tempat_lahir.required' => 'Tempat lahir anak wajib diisi.',
            'tanggal_lahir.required' => 'Tanggal lahir anak wajib diisi.',
            'alamat.required' => 'Alamat tinggal anak wajib diisi.',
            'phone.required' => 'Nomor Telp/HP anak atau kontak utama wajib diisi.',
            'email.required' => 'Alamat email aktif wajib diisi.',
            'agama.required' => 'Agama/kepercayaan anak wajib diisi.',
            'pendidikan_terakhir.required' => 'Pendidikan terakhir anak wajib diisi.',
            'consent_agree.required' => 'Lembar persetujuan orang tua / wali wajib disetujui.',
        ]);

        return DB::transaction(function () use ($request) {
            $primaryPhone = !empty($request->phone) ? $request->phone : (!empty($request->phone_ibu) ? $request->phone_ibu : $request->phone_ayah);
            $gender = (strtolower($request->jenis_kelamin) === 'laki-laki' || strtolower($request->jenis_kelamin) === 'l') ? 'l' : 'p';
            $isOnline = str_contains(strtolower($request->preferensi_konseling ?? ''), 'online');
            $counselingType = $isOnline ? 'online' : 'offline';

            $client = Client::firstOrNew(['phone' => trim($primaryPhone)]);
            $client->name = trim($request->nama_lengkap);
            $client->jenis = 'individu';
            $client->gender = $gender;
            $client->birth_place = $request->tempat_lahir;
            $client->dob = $request->tanggal_lahir;
            $client->address = $request->alamat;
            $client->religion = $request->agama;
            $client->education = $request->pendidikan_terakhir;
            $client->email = $request->email;
            $client->service_type = 'Konseling Anak';
            $client->counseling_type = $counselingType;
            $client->source = 'other';
            $client->status = 'unassigned';
            $client->created_by = auth()->id();
            $client->save();

            $ticketNumber = ClientForm::generateTicketNumber('anak');
            ClientForm::create([
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
                'answers' => $request->except(['_token', 'from_client_modal', 'form_type', 'consent_agree']),
                'ip_address' => $request->ip(),
            ]);

            return redirect()->route('clients.index')
                ->with('success', 'Klien ' . $client->name . ' berhasil ditambahkan melalui Formulir Riwayat Hidup Konseling - Anak (Tiket: ' . $ticketNumber . ').');
        });
    }

    protected function storePraNikahForm(Request $request)
    {
        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'jenis_kelamin' => 'required|string|in:Laki-Laki,Perempuan',
            'tempat_lahir' => 'required|string|max:100',
            'tanggal_lahir' => 'required|date',
            'alamat' => 'required|string',
            'phone' => 'required|string|max:50',
            'email' => 'required|email|max:150',
            'agama' => 'required|string|max:50',
            'pendidikan_terakhir' => 'required|string|max:100',
            'consent_agree' => 'required',
            'urutan_kelahiran' => 'nullable|string|max:100',
            'suku_bangsa' => 'nullable|string|max:50',
            'pekerjaan' => 'nullable|string|max:150',
            'pernikahan_ke' => 'nullable|string|max:50',
            'jumlah_anak' => 'nullable|string|max:50',
            'alasan_konseling' => 'nullable|string',
            'nama_pasangan' => 'nullable|string|max:255',
            'jenis_kelamin_pasangan' => 'nullable|string|max:50',
            'tempat_lahir_pasangan' => 'nullable|string|max:100',
            'tanggal_lahir_pasangan' => 'nullable|date',
            'urutan_kelahiran_pasangan' => 'nullable|string|max:100',
            'alamat_pasangan' => 'nullable|string',
            'phone_pasangan' => 'nullable|string|max:50',
            'email_pasangan' => 'nullable|email|max:150',
            'agama_pasangan' => 'nullable|string|max:50',
            'suku_bangsa_pasangan' => 'nullable|string|max:50',
            'pendidikan_terakhir_pasangan' => 'nullable|string|max:100',
            'pekerjaan_pasangan' => 'nullable|string|max:150',
            'pernikahan_pasangan_ke' => 'nullable|string|max:50',
            'jumlah_anak_pasangan' => 'nullable|string|max:50',
            'tanggal_rencana_pernikahan' => 'nullable|string|max:100',
            'lama_berkenalan' => 'nullable|string|max:100',
            'lama_berpacaran' => 'nullable|string|max:100',
            'harapan_pernikahan' => 'nullable|string',
            'hal_ingin_ditingkatkan' => 'nullable|string',
            'kelebihan_peran' => 'nullable|string',
            'kekurangan_peran' => 'nullable|string',
            'keluhan_hubungan' => 'nullable|string',
            'riwayat_trauma' => 'nullable|string',
            'riwayat_kesehatan' => 'nullable|string',
            'pernah_konseling' => 'nullable|string|max:50',
            'nama_konselor' => 'nullable|string|max:150',
            'kontak_darurat' => 'nullable|string|max:255',
            'sumber_info' => 'nullable|string|max:150',
            'preferensi_konseling' => 'nullable|string|max:100',
        ], [
            'nama_lengkap.required' => 'Nama lengkap Anda wajib diisi.',
            'jenis_kelamin.required' => 'Pilih jenis kelamin Anda.',
            'tempat_lahir.required' => 'Tempat lahir wajib diisi.',
            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi.',
            'alamat.required' => 'Alamat tinggal wajib diisi.',
            'phone.required' => 'Nomor WhatsApp / HP Anda wajib diisi.',
            'email.required' => 'Alamat email aktif Anda wajib diisi.',
            'agama.required' => 'Agama wajib diisi.',
            'pendidikan_terakhir.required' => 'Pendidikan terakhir wajib diisi.',
            'consent_agree.required' => 'Lembar persetujuan (informed consent) wajib disetujui.',
        ]);

        return DB::transaction(function () use ($request) {
            $gender = ($request->jenis_kelamin === 'Laki-Laki') ? 'l' : 'p';
            $isOnline = str_contains(strtolower($request->preferensi_konseling ?? ''), 'online');
            $counselingType = $isOnline ? 'online' : 'offline';

            $client = Client::firstOrNew(['phone' => trim($request->phone)]);
            $client->name = trim($request->nama_lengkap);
            $client->jenis = 'individu';
            $client->gender = $gender;
            $client->birth_place = $request->tempat_lahir;
            $client->dob = $request->tanggal_lahir;
            $client->address = $request->alamat;
            $client->religion = $request->agama;
            $client->education = $request->pendidikan_terakhir;
            $client->occupation = $request->pekerjaan;
            $client->email = $request->email;
            $client->service_type = 'Konseling Pra-Nikah';
            $client->counseling_type = $counselingType;
            $client->source = 'other';
            $client->status = 'unassigned';
            $client->created_by = auth()->id();
            $client->save();

            $ticketNumber = ClientForm::generateTicketNumber('pra_nikah');
            ClientForm::create([
                'ticket_number' => $ticketNumber,
                'form_type' => 'pra_nikah',
                'client_id' => $client->id,
                'booking_id' => null,
                'client_name' => trim($request->nama_lengkap) . ' & ' . trim($request->nama_pasangan),
                'client_phone' => trim($request->phone),
                'consent_agreed' => true,
                'consent_agreed_at' => now(),
                'service_preference' => $counselingType,
                'status' => 'baru',
                'answers' => $request->except(['_token', 'from_client_modal', 'form_type', 'consent_agree']),
                'ip_address' => $request->ip(),
            ]);

            return redirect()->route('clients.index')
                ->with('success', 'Klien ' . $client->name . ' berhasil ditambahkan melalui Formulir Riwayat Hidup Konseling - Pra Nikah (Tiket: ' . $ticketNumber . ').');
        });
    }

    protected function storePernikahanForm(Request $request)
    {
        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'jenis_kelamin' => 'required|string|in:Laki-Laki,Perempuan',
            'tempat_lahir' => 'required|string|max:100',
            'tanggal_lahir' => 'required|date',
            'alamat' => 'required|string',
            'phone' => 'required|string|max:50',
            'email' => 'required|email|max:150',
            'agama' => 'required|string|max:50',
            'pendidikan_terakhir' => 'required|string|max:100',
            'consent_agree' => 'required',
            'urutan_kelahiran' => 'nullable|string|max:100',
            'suku_bangsa' => 'nullable|string|max:50',
            'pekerjaan' => 'nullable|string|max:150',
            'pernikahan_ke' => 'nullable|string|max:50',
            'jumlah_anak' => 'nullable|string|max:50',
            'alasan_konseling' => 'nullable|string',
            'nama_pasangan' => 'nullable|string|max:255',
            'jenis_kelamin_pasangan' => 'nullable|string|max:50',
            'tempat_lahir_pasangan' => 'nullable|string|max:100',
            'tanggal_lahir_pasangan' => 'nullable|date',
            'urutan_kelahiran_pasangan' => 'nullable|string|max:100',
            'alamat_pasangan' => 'nullable|string',
            'phone_pasangan' => 'nullable|string|max:50',
            'email_pasangan' => 'nullable|email|max:150',
            'agama_pasangan' => 'nullable|string|max:50',
            'suku_bangsa_pasangan' => 'nullable|string|max:50',
            'pendidikan_terakhir_pasangan' => 'nullable|string|max:100',
            'pekerjaan_pasangan' => 'nullable|string|max:150',
            'pernikahan_pasangan_ke' => 'nullable|string|max:50',
            'jumlah_anak_pasangan' => 'nullable|string|max:50',
            'tempat_tanggal_pernikahan' => 'nullable|string|max:150',
            'lama_pernikahan' => 'nullable|string|max:100',
            'lama_berkenalan' => 'nullable|string|max:100',
            'lama_berpacaran' => 'nullable|string|max:100',
            'keluhan_utama' => 'nullable|string',
            'kemungkinan_perbaikan' => 'nullable|string',
            'hal_ingin_ditingkatkan' => 'nullable|string',
            'kelebihan_peran' => 'nullable|string',
            'kekurangan_peran' => 'nullable|string',
            'riwayat_trauma' => 'nullable|string',
            'riwayat_kesehatan' => 'nullable|string',
            'pernah_konseling' => 'nullable|string|max:50',
            'nama_konselor' => 'nullable|string|max:150',
            'kontak_darurat' => 'nullable|string|max:255',
            'sumber_info' => 'nullable|string|max:150',
            'preferensi_konseling' => 'nullable|string|max:100',
        ], [
            'nama_lengkap.required' => 'Nama lengkap Anda wajib diisi.',
            'jenis_kelamin.required' => 'Pilih jenis kelamin Anda.',
            'tempat_lahir.required' => 'Tempat lahir wajib diisi.',
            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi.',
            'alamat.required' => 'Alamat tinggal wajib diisi.',
            'phone.required' => 'Nomor WhatsApp / HP Anda wajib diisi.',
            'email.required' => 'Alamat email aktif Anda wajib diisi.',
            'agama.required' => 'Agama wajib diisi.',
            'pendidikan_terakhir.required' => 'Pendidikan terakhir wajib diisi.',
            'consent_agree.required' => 'Lembar persetujuan (informed consent) wajib disetujui.',
        ]);

        return DB::transaction(function () use ($request) {
            $gender = ($request->jenis_kelamin === 'Laki-Laki') ? 'l' : 'p';
            $isOnline = str_contains(strtolower($request->preferensi_konseling ?? ''), 'online');
            $counselingType = $isOnline ? 'online' : 'offline';

            $client = Client::firstOrNew(['phone' => trim($request->phone)]);
            $client->name = trim($request->nama_lengkap);
            $client->jenis = 'individu';
            $client->gender = $gender;
            $client->birth_place = $request->tempat_lahir;
            $client->dob = $request->tanggal_lahir;
            $client->address = $request->alamat;
            $client->religion = $request->agama;
            $client->education = $request->pendidikan_terakhir;
            $client->occupation = $request->pekerjaan;
            $client->email = $request->email;
            $client->service_type = 'Konseling Pernikahan';
            $client->counseling_type = $counselingType;
            $client->source = 'other';
            $client->status = 'unassigned';
            $client->created_by = auth()->id();
            $client->save();

            $ticketNumber = ClientForm::generateTicketNumber('pernikahan');
            ClientForm::create([
                'ticket_number' => $ticketNumber,
                'form_type' => 'pernikahan',
                'client_id' => $client->id,
                'booking_id' => null,
                'client_name' => trim($request->nama_lengkap) . ' & ' . trim($request->nama_pasangan),
                'client_phone' => trim($request->phone),
                'consent_agreed' => true,
                'consent_agreed_at' => now(),
                'service_preference' => $counselingType,
                'status' => 'baru',
                'answers' => $request->except(['_token', 'from_client_modal', 'form_type', 'consent_agree']),
                'ip_address' => $request->ip(),
            ]);

            return redirect()->route('clients.index')
                ->with('success', 'Klien ' . $client->name . ' berhasil ditambahkan melalui Formulir Riwayat Hidup Konseling - Pernikahan (Tiket: ' . $ticketNumber . ').');
        });
    }

    protected function storeBiographyEnForm(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'gender' => 'required|string|in:Male,Female',
            'birth_place_date' => 'required|string|max:150',
            'current_address' => 'required|string',
            'phone' => 'required|string|max:50',
            'email' => 'required|email|max:150',
            'religion' => 'required|string|max:50',
            'last_education' => 'required|string|max:100',
            'consent_agree' => 'required',
            'birth_order' => 'nullable|string|max:100',
            'city_of_origin' => 'nullable|string|max:100',
            'ethnicity' => 'nullable|string|max:50',
            'school_1_name' => 'nullable|string|max:255',
            'school_1_city' => 'nullable|string|max:100',
            'school_1_year_entry' => 'nullable|string|max:50',
            'school_1_year_graduation' => 'nullable|string|max:50',
            'failed_grade_reason' => 'nullable|string',
            'personal_strength_1' => 'nullable|string|max:255',
            'personal_strength_2' => 'nullable|string|max:255',
            'personal_strength_3' => 'nullable|string|max:255',
            'personal_weakness_1' => 'nullable|string|max:255',
            'personal_weakness_2' => 'nullable|string|max:255',
            'personal_weakness_3' => 'nullable|string|max:255',
            'subject_highest_score' => 'nullable|string|max:255',
            'subject_lowest_score' => 'nullable|string|max:255',
            'subject_liked' => 'nullable|string|max:255',
            'subject_disliked' => 'nullable|string|max:255',
            'learning_style' => 'nullable|string|max:255',
            'activities_liked' => 'nullable|string|max:255',
            'activities_disliked' => 'nullable|string|max:255',
            'dream_job' => 'nullable|string|max:255',
            'efforts_for_dream_job' => 'nullable|string',
            'plans_for_dream_job' => 'nullable|string',
            'work_field_liked' => 'nullable|string|max:255',
            'work_field_disliked' => 'nullable|string|max:255',
            'activities_liked_not_skilled' => 'nullable|string|max:255',
            'activities_disliked_quite_skilled' => 'nullable|string|max:255',
            'free_self_description' => 'nullable|string',
        ], [
            'full_name.required' => 'Full name is required.',
            'gender.required' => 'Please select your gender.',
            'birth_place_date.required' => 'Place, date of birth is required.',
            'current_address.required' => 'Current address is required.',
            'phone.required' => 'Active Mobile/WhatsApp number is required.',
            'email.required' => 'Active email address is required.',
            'religion.required' => 'Religion is required.',
            'last_education.required' => 'Last education is required.',
            'consent_agree.required' => 'Declaration of authenticity is required.',
        ]);

        return DB::transaction(function () use ($request) {
            $gender = ($request->gender === 'Male') ? 'l' : 'p';

            $client = Client::firstOrNew(['phone' => trim($request->phone)]);
            $client->name = trim($request->full_name);
            $client->jenis = 'individu';
            $client->gender = $gender;
            $client->birth_place = $request->birth_place_date;
            $client->address = $request->current_address;
            $client->religion = $request->religion;
            $client->education = $request->last_education;
            $client->occupation = 'General Client';
            $client->email = $request->email;
            $client->service_type = 'Biography Form (English)';
            $client->counseling_type = 'offline';
            $client->source = 'other';
            $client->status = 'unassigned';
            $client->created_by = auth()->id();
            $client->save();

            $ticketNumber = ClientForm::generateTicketNumber('biography_en');
            ClientForm::create([
                'ticket_number' => $ticketNumber,
                'form_type' => 'biography_en',
                'client_id' => $client->id,
                'booking_id' => null,
                'client_name' => $client->name,
                'client_phone' => trim($request->phone),
                'consent_agreed' => true,
                'consent_agreed_at' => now(),
                'service_preference' => 'offline',
                'status' => 'baru',
                'answers' => $request->except(['_token', 'from_client_modal', 'form_type', 'consent_agree']),
                'ip_address' => $request->ip(),
            ]);

            return redirect()->route('clients.index')
                ->with('success', 'Client ' . $client->name . ' has been successfully added via Biography Form (Ticket: ' . $ticketNumber . ').');
        });
    }

    protected function storeNonIndustriForm(Request $request)
    {
        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'jenis_kelamin' => 'required|string|in:Laki-Laki,Perempuan',
            'tempat_tanggal_lahir' => 'required|string|max:150',
            'alamat_sekarang' => 'required|string',
            'phone' => 'required|string|max:50',
            'email' => 'required|email|max:150',
            'agama' => 'required|string|max:50',
            'pendidikan_terakhir' => 'required|string|max:100',
            'consent_agree' => 'required',
            'urutan_kelahiran' => 'nullable|string|max:100',
            'asal_kota' => 'nullable|string|max:100',
            'suku_bangsa' => 'nullable|string|max:50',
            'nama_sekolah_1' => 'nullable|string|max:255',
            'kota_sekolah_1' => 'nullable|string|max:100',
            'tahun_masuk_sekolah_1' => 'nullable|string|max:50',
            'tahun_keluar_sekolah_1' => 'nullable|string|max:50',
            'tidak_naik_kelas' => 'nullable|string',
            'kelebihan_1' => 'nullable|string|max:255',
            'kelebihan_2' => 'nullable|string|max:255',
            'kelebihan_3' => 'nullable|string|max:255',
            'kelemahan_1' => 'nullable|string|max:255',
            'kelemahan_2' => 'nullable|string|max:255',
            'kelemahan_3' => 'nullable|string|max:255',
            'mapel_nilai_tertinggi' => 'nullable|string|max:255',
            'mapel_nilai_terendah' => 'nullable|string|max:255',
            'mapel_disukai' => 'nullable|string|max:255',
            'mapel_tidak_disukai' => 'nullable|string|max:255',
            'gaya_belajar' => 'nullable|string|max:255',
            'hal_disenangi' => 'nullable|string|max:255',
            'hal_tidak_disenangi' => 'nullable|string|max:255',
            'cita_cita' => 'nullable|string|max:255',
            'usaha_cita_cita' => 'nullable|string',
            'rencana_cita_cita' => 'nullable|string',
            'pekerjaan_disukai' => 'nullable|string|max:255',
            'pekerjaan_tidak_disukai' => 'nullable|string|max:255',
            'aktivitas_disukai_kurang_mahir' => 'nullable|string|max:255',
            'aktivitas_tidak_disukai_cukup_mahir' => 'nullable|string|max:255',
            'deskripsi_diri_bebas' => 'nullable|string',
        ], [
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'jenis_kelamin.required' => 'Pilih jenis kelamin.',
            'tempat_tanggal_lahir.required' => 'Tempat, tanggal lahir wajib diisi.',
            'alamat_sekarang.required' => 'Alamat tempat tinggal sekarang wajib diisi.',
            'phone.required' => 'Nomor Telp/HP/WhatsApp aktif wajib diisi.',
            'email.required' => 'Alamat email aktif wajib diisi.',
            'agama.required' => 'Agama/kepercayaan wajib diisi.',
            'pendidikan_terakhir.required' => 'Pendidikan terakhir yang berijazah wajib diisi.',
            'consent_agree.required' => 'Pernyataan kejujuran wajib disetujui.',
        ]);

        return DB::transaction(function () use ($request) {
            $gender = ($request->jenis_kelamin === 'Laki-Laki') ? 'l' : 'p';

            $client = Client::firstOrNew(['phone' => trim($request->phone)]);
            $client->name = trim($request->nama_lengkap);
            $client->jenis = 'individu';
            $client->gender = $gender;
            $client->birth_place = $request->tempat_tanggal_lahir;
            $client->address = $request->alamat_sekarang;
            $client->religion = $request->agama;
            $client->education = $request->pendidikan_terakhir;
            $client->occupation = 'Peserta Non-Industri';
            $client->email = $request->email;
            $client->service_type = 'Layanan Non-Industri';
            $client->counseling_type = 'offline';
            $client->source = 'other';
            $client->status = 'unassigned';
            $client->created_by = auth()->id();
            $client->save();

            $ticketNumber = ClientForm::generateTicketNumber('non_industri');
            ClientForm::create([
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
                'answers' => $request->except(['_token', 'from_client_modal', 'form_type', 'consent_agree']),
                'ip_address' => $request->ip(),
            ]);

            return redirect()->route('clients.index')
                ->with('success', 'Klien ' . $client->name . ' berhasil ditambahkan melalui Formulir Riwayat Hidup - Non-Industri (Tiket: ' . $ticketNumber . ').');
        });
    }

    protected function storeIndustriForm(Request $request)
    {
        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'nama_perusahaan' => 'required|string|max:255',
            'pekerjaan_saat_ini' => 'required|string|max:150',
            'jenis_kelamin' => 'required|string|in:Laki-Laki,Perempuan',
            'tempat_tanggal_lahir' => 'required|string|max:150',
            'alamat_sekarang' => 'nullable|string',
            'agama' => 'nullable|string|max:50',
            'pendidikan_terakhir' => 'required|string|max:100',
            'phone' => 'required|string|max:50',
            'email' => 'required|email|max:150',
            'consent_agree' => 'required',
            'urutan_kelahiran' => 'nullable|string|max:100',
            'asal_kota' => 'nullable|string|max:100',
            'suku_bangsa' => 'nullable|string|max:50',
            'status_perkawinan' => 'nullable|string|max:50',
            'posisi_dituju' => 'nullable|string|max:150',
            'pss_1' => 'nullable',
            'pss_2' => 'nullable',
            'pss_3' => 'nullable',
            'pss_4' => 'nullable',
            'pss_5' => 'nullable',
            'pss_6' => 'nullable',
            'pss_7' => 'nullable',
            'pss_8' => 'nullable',
            'pss_9' => 'nullable',
            'pss_10' => 'nullable',
            'nama_sekolah_1' => 'nullable|string|max:255',
            'kota_sekolah_1' => 'nullable|string|max:100',
            'tahun_masuk_sekolah_1' => 'nullable|string|max:50',
            'tahun_keluar_sekolah_1' => 'nullable|string|max:50',
            'keterangan_sekolah_1' => 'nullable|string|max:255',
            'ekspektasi_gaji_tunjangan' => 'nullable|string',
            'kelebihan_1' => 'nullable|string|max:255',
            'kelebihan_2' => 'nullable|string|max:255',
            'kelebihan_3' => 'nullable|string|max:255',
            'kelemahan_1' => 'nullable|string|max:255',
            'kelemahan_2' => 'nullable|string|max:255',
            'kelemahan_3' => 'nullable|string|max:255',
            'hobi' => 'nullable|string|max:255',
            'deskripsi_diri_bebas' => 'nullable|string',
        ], [
            'nama_lengkap.required' => 'Nama lengkap beserta gelar (jika ada) wajib diisi.',
            'nama_perusahaan.required' => 'Nama perusahaan wajib diisi.',
            'pekerjaan_saat_ini.required' => 'Pekerjaan saat ini wajib diisi.',
            'jenis_kelamin.required' => 'Pilih jenis kelamin.',
            'tempat_tanggal_lahir.required' => 'Tempat & tanggal lahir wajib diisi.',
            'pendidikan_terakhir.required' => 'Pendidikan terakhir wajib dipilih.',
            'phone.required' => 'Nomor Telp/HP/WhatsApp aktif wajib diisi.',
            'email.required' => 'Alamat email aktif wajib diisi.',
            'consent_agree.required' => 'Pernyataan kejujuran wajib disetujui.',
        ]);

        return DB::transaction(function () use ($request) {
            $gender = ($request->jenis_kelamin === 'Laki-Laki') ? 'l' : 'p';

            $client = Client::firstOrNew(['phone' => trim($request->phone)]);
            $client->name = trim($request->nama_lengkap);
            $client->jenis = 'industri';
            $client->pic_name = trim($request->nama_perusahaan);
            $client->gender = $gender;
            $client->birth_place = $request->tempat_tanggal_lahir;
            $client->address = $request->alamat_sekarang;
            $client->religion = $request->agama;
            $client->education = $request->pendidikan_terakhir;
            $client->occupation = trim($request->pekerjaan_saat_ini) . ' - ' . trim($request->nama_perusahaan);
            $client->email = $request->email;
            $client->service_type = 'Layanan Industri';
            $client->counseling_type = 'offline';
            $client->source = 'other';
            $client->status = 'unassigned';
            $client->created_by = auth()->id();
            $client->save();

            $ticketNumber = ClientForm::generateTicketNumber('industri');
            ClientForm::create([
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
                'answers' => $request->except(['_token', 'from_client_modal', 'form_type', 'consent_agree']),
                'ip_address' => $request->ip(),
            ]);

            return redirect()->route('clients.index')
                ->with('success', 'Klien ' . $client->name . ' berhasil ditambahkan melalui Formulir Riwayat Hidup - Industri (Tiket: ' . $ticketNumber . ').');
        });
    }

    public function show(Client $client)
    {
        $this->authorize('view', $client);

        $client->load([
            'clientForms' => fn($q) => $q->latest(),
            'clientForms.booking',
            'creator',
            'pairings.counselor',
            'pairings.assigner',

            'testResults.administrator',
            'testResults.deliveredBy',
            'testResults.booking.staffPenguji',
            'testResults.booking.staffKoreksi',
            'testResults.booking.staffPelapor',
            'testResults.documents',
            'staffAssignmentHistory.staff',
            'staffAssignmentHistory.assigner',
            'bookings.followUps',
            'bookings.participants',
            'bookings.paymentTransaction',
            'bookings.counselor',
            'bookings.staffPenguji',
            'bookings.staffKoreksi',
            'bookings.staffPelapor',
            'assignedStaff',
            'clientParticipants',
        ]);

        $counselors = Counselor::orderBy('name')->get();
        $staffMembers = User::where('role', 'staff')->orderBy('name')->get();
        if ($staffMembers->isEmpty()) {
            $staffMembers = User::orderBy('name')->get();
        }

        return view('clients.show', compact('client', 'counselors', 'staffMembers'));
    }

    public function edit(Client $client)
    {
        $this->authorize('view', $client);

        $counselors = Counselor::orderBy('name')->get();
        $upcomingRecord = null;
        $activePairing = $client->pairings()->where('status', 'active')->latest()->first();

        return view('clients.edit', compact('client', 'counselors', 'upcomingRecord', 'activePairing'));
    }

    public function update(Request $request, Client $client)
    {
        $this->authorize('update', $client);

        $validated = $request->validate([
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                'regex:/^[^0-9]+$/u',
            ],
            'pic_name' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[^0-9]+$/u',
            ],
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'gender' => 'nullable|in:l,p,non_binary,transgender,prefer_not_to_say,other',
            'birth_place' => 'nullable|string|max:255',
            'dob' => 'nullable|date',
            'religion' => 'nullable|string|max:100',
            'occupation' => 'nullable|string|max:255',
            'education' => 'nullable|string|max:100',
            'marital_status' => 'nullable|string|max:100',
            'country' => 'sometimes|required|string|max:100',
            'province' => 'nullable|required_if:country,Indonesia|string|max:100',
            'city' => 'nullable|required_if:country,Indonesia|string|max:100',
            'address' => 'nullable|string|max:1000',
            'source' => 'nullable|string|max:255',
            'status' => 'sometimes|required|in:unassigned,assigned,ongoing,unpaid,paid,needs_followup,completed',
            'notes' => 'nullable|string',
            // Schedule fields
            'counselor_id' => 'nullable|exists:counselors,id',
            'scheduled_at' => 'nullable|date',
            'end_time' => 'nullable|date|after:scheduled_at',
            'session_type' => 'nullable|in:tatap_muka,online,whatsapp',
            'location' => 'nullable|string|max:255',
            'participants' => 'nullable|array|min:2',
            'participants.*' => 'required|string|max:255',
        ], [
            'name.regex' => 'Nama tidak boleh mengandung angka. Hanya teks dan simbol yang diperbolehkan.',
            'country.required' => 'Negara wajib dipilih.',
            'province.required_if' => 'Provinsi wajib dipilih untuk negara Indonesia.',
            'city.required_if' => 'Kota / Kabupaten wajib dipilih untuk negara Indonesia.',
            'address.required' => 'Alamat lengkap wajib diisi.',
            'participants.min' => 'Daftar peserta harus memiliki minimal 2 orang.',
            'participants.*.required' => 'Nama peserta tidak boleh kosong.',
        ]);

        if (!empty($validated['counselor_id']) || !empty($validated['scheduled_at'])) {
            if (($validated['status'] ?? $client->status) === 'unassigned') {
                $validated['status'] = 'assigned';
            }
        }

        $participants = $validated['participants'] ?? null;
        unset($validated['participants']);

        $client->update($validated);

        if ($request->has('participants') && is_array($participants)) {
            $cleanParticipants = array_filter(
                array_map('trim', $participants),
                fn($item) => !empty($item)
            );
            if (!empty($cleanParticipants)) {
                $client->clientParticipants()->delete();
                foreach ($cleanParticipants as $name) {
                    \App\Models\ClientParticipant::create([
                        'client_id' => $client->id,
                        'nama_peserta' => $name,
                    ]);
                }
            }
        }

        if (!empty($validated['counselor_id'])) {
            Pairing::firstOrCreate([
                'client_id' => $client->id,
                'counselor_id' => $validated['counselor_id'],
            ], [
                'status' => 'active',
                'assigned_by' => auth()->id(),
            ]);
        }


        return redirect()->back()->with('success', 'Data klien berhasil diperbarui.');
    }

    public function destroy(Client $client)
    {
        $client->clientForms()->delete();
        $client->delete();
        return redirect()->route('clients.index')->with('success', 'Klien berhasil dihapus.');
    }

    /**
     * Tambahkan catatan baru untuk klien dengan tanggal tertentu.
     */
    public function storeNote(Request $request, Client $client)
    {
        $request->validate([
            'note' => 'required|string',
            'date' => 'nullable|date',
        ], [
            'note.required' => 'Isi catatan tidak boleh kosong.',
        ]);

        $notes = $client->notes_list;
        $date = !empty($request->date) ? \Carbon\Carbon::parse($request->date) : now();

        $notes[] = [
            'id' => 'note_' . time() . '_' . substr(bin2hex(random_bytes(2)), 0, 4),
            'date' => $date->format('Y-m-d H:i:s'),
            'note' => trim($request->note),
        ];

        usort($notes, fn($a, $b) => strcmp($b['date'], $a['date']));

        $client->update([
            'notes' => json_encode($notes, JSON_UNESCAPED_UNICODE),
        ]);

        return redirect()->to(route('clients.show', $client) . '?tab=notes')
            ->with('success', 'Catatan baru berhasil ditambahkan.');
    }

    /**
     * Perbarui catatan yang sudah ada berdasarkan ID catatan.
     */
    public function updateNote(Request $request, Client $client, string $noteId)
    {
        $request->validate([
            'note' => 'required|string',
            'date' => 'nullable|date',
        ], [
            'note.required' => 'Isi catatan tidak boleh kosong.',
        ]);

        $notes = $client->notes_list;
        $found = false;

        foreach ($notes as &$item) {
            if ($item['id'] === $noteId) {
                if (!empty($request->date)) {
                    $item['date'] = \Carbon\Carbon::parse($request->date)->format('Y-m-d H:i:s');
                }
                $item['note'] = trim($request->note);
                $found = true;
                break;
            }
        }
        unset($item);

        if (!$found) {
            return redirect()->to(route('clients.show', $client) . '?tab=notes')
                ->with('error', 'Catatan tidak ditemukan.');
        }

        usort($notes, fn($a, $b) => strcmp($b['date'], $a['date']));

        $client->update([
            'notes' => json_encode($notes, JSON_UNESCAPED_UNICODE),
        ]);

        return redirect()->to(route('clients.show', $client) . '?tab=notes')
            ->with('success', 'Catatan berhasil diperbarui.');
    }

    /**
     * Hapus satu catatan berdasarkan ID catatan.
     */
    public function destroyNote(Client $client, string $noteId)
    {
        $notes = array_values(array_filter(
            $client->notes_list,
            fn($item) => $item['id'] !== $noteId
        ));

        $client->update([
            'notes' => empty($notes) ? null : json_encode($notes, JSON_UNESCAPED_UNICODE),
        ]);

        return redirect()->to(route('clients.show', $client) . '?tab=notes')
            ->with('success', 'Catatan berhasil dihapus.');
    }
}

