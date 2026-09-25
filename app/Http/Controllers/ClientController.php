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
            if ($request->jenis === 'individual') {
                $query->where(function($q) {
                    $q->where('jenis', 'individual')->orWhereNull('jenis');
                });
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
            if ($direction === 'group') {
                $query->orderByRaw("CASE 
                    WHEN clients.jenis = 'group' THEN 1 
                    WHEN clients.jenis = 'individual' OR clients.jenis IS NULL THEN 2 
                    WHEN clients.jenis = 'company' THEN 3 
                    ELSE 4 END ASC")
                    ->orderBy('clients.created_at', 'desc');
            } elseif ($direction === 'company' || $direction === 'desc') {
                $query->orderByRaw("CASE 
                    WHEN clients.jenis = 'company' THEN 1 
                    WHEN clients.jenis = 'group' THEN 2 
                    WHEN clients.jenis = 'individual' OR clients.jenis IS NULL THEN 3 
                    ELSE 4 END ASC")
                    ->orderBy('clients.created_at', 'desc');
            } else {
                $query->orderByRaw("CASE 
                    WHEN clients.jenis = 'individual' OR clients.jenis IS NULL THEN 1 
                    WHEN clients.jenis = 'group' THEN 2 
                    WHEN clients.jenis = 'company' THEN 3 
                    ELSE 4 END ASC")
                    ->orderBy('clients.created_at', 'desc');
            }
        } elseif (in_array($sort, ['id', 'name', 'created_at', 'updated_at', 'service_type', 'city', 'phone', 'email'])) {
            $query->orderBy('clients.' . $sort, $direction);
        } else {
            $query->orderBy('clients.created_at', $direction);
        }

        $clients = $query->paginate(15)->withQueryString();

        return view('clients.index', compact('clients', 'sort', 'direction'));
    }

    public function create()
    {
        return redirect()->route('clients.index');
    }

    public function store(Request $request)
    {
        if (!$request->filled('jenis')) {
            $request->merge(['jenis' => 'individual']);
        }

        $jenis = $request->input('jenis', 'individual');
        $isIndividual = $jenis === 'individual';

        $rules = [
            'service_type' => 'nullable|in:konseling,psikotes',
            'jenis' => 'nullable|in:individual,company,group',
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
            'birth_place' => 'nullable|string|max:255',
            'religion' => 'nullable|string|max:100',
            'occupation' => 'nullable|string|max:255',
            'education' => 'nullable|string|max:100',
            'marital_status' => 'nullable|string|max:100',
            'country' => 'required|string|max:100',
            'province' => 'nullable|required_if:country,Indonesia|string|max:100',
            'city' => 'nullable|required_if:country,Indonesia|string|max:100',
            'address' => 'required|string|max:1000',
            'source' => 'nullable|string|max:255',
            'status' => 'nullable|in:unassigned,assigned,ongoing,unpaid,paid,needs_followup,completed',
            'notes' => 'nullable|string',
        ];

        if ($isIndividual) {
            $rules['gender'] = 'required|in:l,p,non_binary,transgender,prefer_not_to_say,other';
            $rules['dob'] = 'required|date';
            $rules['religion'] = 'required|string|max:100';
            $rules['marital_status'] = 'required|string|max:100';
            $rules['education'] = 'required|string|max:100';
        } else {
            $rules['pic_name'] = [
                'required',
                'string',
                'max:255',
                'regex:/^[^0-9]+$/u',
            ];
            $rules['gender'] = 'nullable|in:l,p,non_binary,transgender,prefer_not_to_say,other';
            $rules['dob'] = 'nullable|date';
            $rules['religion'] = 'nullable|string|max:100';
            $rules['marital_status'] = 'nullable|string|max:100';
            $rules['education'] = 'nullable|string|max:100';
            $rules['participants'] = 'required|array|min:2';
            $rules['participants.*'] = 'required|string|max:255';
        }

        $validated = $request->validate($rules, [
            'name.regex' => 'Nama tidak boleh mengandung angka. Hanya teks dan simbol yang diperbolehkan.',
            'pic_name.regex' => 'Nama PIC tidak boleh mengandung angka. Hanya teks dan simbol yang diperbolehkan.',
            'religion.required' => 'Agama wajib dipilih.',
            'marital_status.required' => 'Status perkawinan wajib dipilih.',
            'education.required' => 'Pendidikan terakhir wajib dipilih.',
            'country.required' => 'Negara wajib dipilih.',
            'province.required_if' => 'Provinsi wajib dipilih untuk negara Indonesia.',
            'city.required_if' => 'Kota / Kabupaten wajib dipilih untuk negara Indonesia.',
            'address.required' => 'Alamat lengkap wajib diisi.',
            'participants.required' => 'Daftar anggota wajib diisi untuk klien kelompok / perusahaan.',
            'participants.min' => 'Klien kelompok / perusahaan harus memiliki minimal 2 anggota. Jika hanya 1, gunakan jenis Individu.',
            'participants.*.required' => 'Nama anggota tidak boleh kosong.',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['jenis'] = $validated['jenis'] ?? 'individual';
        $validated['status'] = $validated['status'] ?? 'unassigned';

        if (!$isIndividual) {
            $validated['pic_name'] = $request->pic_name;
        }

        $participants = $validated['participants'] ?? [];
        unset($validated['participants']);

        $client = Client::create($validated);

        // Simpan anggota ke tabel client_participants
        if (!$isIndividual && !empty($participants)) {
            $cleanParticipants = array_filter(
                array_map('trim', $participants),
                fn($item) => !empty($item)
            );
            foreach ($cleanParticipants as $name) {
                ClientParticipant::create([
                    'client_id'    => $client->id,
                    'nama_peserta' => $name,
                ]);
            }
        }

        return redirect()->route('clients.index')
            ->with('success', 'Data klien berhasil ditambahkan.');
    }

    public function show(Client $client)
    {
        $this->authorize('view', $client);

        $client->load([
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
            'address' => 'sometimes|required|string|max:1000',
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
        $client->delete();
        return redirect()->route('clients.index')->with('success', 'Klien berhasil dihapus.');
    }
}

