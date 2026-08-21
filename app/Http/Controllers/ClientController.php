<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Counselor;
use App\Models\CounselingRecord;
use App\Models\Pairing;
use App\Models\User;
use App\Models\ClientStaffAssignment;
use App\Models\Booking;
use App\Models\BookingParticipant;
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
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        if ($request->filled('service_type')) {
            $query->where('service_type', $request->service_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $sort = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');

        if ($sort === 'created_by') {
            $query->leftJoin('users', 'clients.created_by', '=', 'users.id')
                  ->orderBy('users.name', $direction)
                  ->select('clients.*');
        } else {
            $query->orderBy('clients.' . $sort, $direction);
        }

        $clients = $query->paginate(15)->withQueryString();

        return view('clients.index', compact('clients', 'sort', 'direction'));
    }

    public function create()
    {
        $counselors = Counselor::orderBy('name')->get();
        $staffMembers = User::where('role', 'staff')->orderBy('name')->get();
        if ($staffMembers->isEmpty()) {
            $staffMembers = User::orderBy('name')->get();
        }
        return view('clients.create', compact('counselors', 'staffMembers'));
    }

    public function store(Request $request)
    {
        if (!$request->filled('service_type')) {
            $request->merge(['service_type' => 'konseling']);
        }
        if (!$request->filled('jenis')) {
            $request->merge(['jenis' => 'individual']);
        }

        $serviceType = $request->input('service_type', 'konseling');
        $jenis = $request->input('jenis', 'individual');
        $isIndividual = $jenis === 'individual';

        $rules = [
            'service_type' => 'nullable|in:konseling,psikotes',
            'jenis' => 'nullable|in:individual,company,group',
            'name' => 'required|string|max:255',
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
            'source' => 'required|in:whatsapp,walk_in,referral,other',
            'counseling_type' => 'nullable|string|max:255',
            'status' => 'nullable|in:unassigned,assigned,ongoing,unpaid,paid,needs_followup,completed',
            'notes' => 'nullable|string',
        ];

        if ($isIndividual) {
            $rules['gender'] = 'required|in:l,p,non_binary,transgender,prefer_not_to_say,other';
            $rules['dob'] = 'required|date';
        } else {
            $rules['pic_name'] = 'required|string|max:255';
            $rules['gender'] = 'nullable|in:l,p,non_binary,transgender,prefer_not_to_say,other';
            $rules['dob'] = 'nullable|date';
            $rules['participants'] = 'nullable|array';
            $rules['participants.*'] = 'nullable|string|max:255';
        }

        $validated = $request->validate($rules);

        $validated['created_by'] = auth()->id();
        $validated['jenis'] = $validated['jenis'] ?? 'individual';
        $validated['status'] = $validated['status'] ?? 'unassigned';

        // Jika company atau group, tambahkan PIC dan daftar peserta ke notes
        if (!$isIndividual) {
            if (!empty($request->pic_name)) {
                $picLabel = $validated['jenis'] === 'company' ? 'PIC Perusahaan' : 'PIC / Perwakilan Kelompok';
                $picNote = "{$picLabel}: " . $request->pic_name;
                $validated['notes'] = !empty($validated['notes']) ? $validated['notes'] . "\n" . $picNote : $picNote;
            }

            if (!empty($request->participants)) {
                $cleanParticipants = array_filter(
                    array_map('trim', $request->input('participants', [])),
                    fn($item) => !empty($item)
                );
                if (!empty($cleanParticipants)) {
                    $partLabel = $validated['jenis'] === 'company' ? 'Daftar Karyawan / Peserta' : 'Daftar Anggota / Peserta';
                    $partList = "{$partLabel}:\n- " . implode("\n- ", $cleanParticipants);
                    $validated['notes'] = !empty($validated['notes']) ? $validated['notes'] . "\n\n" . $partList : $partList;
                }
            }
        }

        unset($validated['participants']);

        $client = Client::create($validated);

        return redirect()->route('clients.create', ['jenis' => $client->jenis])
            ->with('success', 'Data klien berhasil ditambahkan.');
    }

    public function show(Client $client)
    {
        $this->authorize('view', $client);

        $client->load([
            'creator',
            'counselingRecords.counselor',
            'pairings.counselor',
            'testResults.administrator',
            'staffAssignmentHistory.staff',
            'staffAssignmentHistory.assigner',
            'bookings.followUps',
            'bookings.participants',
            'assignedStaff'
        ]);
        $counselors = Counselor::orderBy('name')->get();
        return view('clients.show', compact('client', 'counselors'));
    }

    public function edit(Client $client)
    {
        $this->authorize('view', $client);

        $counselors = Counselor::orderBy('name')->get();
        $upcomingRecord = $client->counselingRecords()->where('status', 'scheduled')->latest('scheduled_at')->first();
        $activePairing = $client->pairings()->where('status', 'active')->latest()->first();

        return view('clients.edit', compact('client', 'counselors', 'upcomingRecord', 'activePairing'));
    }

    public function update(Request $request, Client $client)
    {
        $this->authorize('update', $client);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'gender' => 'nullable|in:l,p,non_binary,transgender,prefer_not_to_say,other',
            'dob' => 'nullable|date',
            'source' => 'sometimes|required|in:whatsapp,walk_in,referral,other',
            'service_type' => 'sometimes|required|in:konseling,psikotes',
            'counseling_type' => 'nullable|string|max:255',
            'status' => 'sometimes|required|in:unassigned,assigned,ongoing,unpaid,paid,needs_followup,completed',
            'notes' => 'nullable|string',
            // Schedule fields
            'counselor_id' => 'nullable|exists:counselors,id',
            'scheduled_at' => 'nullable|date',
            'end_time' => 'nullable|date|after:scheduled_at',
            'session_type' => 'nullable|in:tatap_muka,online,whatsapp',
            'location' => 'nullable|string|max:255',
        ]);

        if (!empty($validated['counselor_id']) || !empty($validated['scheduled_at'])) {
            if (($validated['status'] ?? $client->status) === 'unassigned') {
                $validated['status'] = 'assigned';
            }
        }

        $client->update($validated);

        if (!empty($validated['counselor_id'])) {
            Pairing::firstOrCreate([
                'client_id' => $client->id,
                'counselor_id' => $validated['counselor_id'],
            ], [
                'status' => 'active',
                'assigned_by' => auth()->id(),
            ]);
        }

        if (!empty($validated['scheduled_at']) && !empty($validated['counselor_id'])) {
            $record = $client->counselingRecords()->where('status', 'scheduled')->latest('scheduled_at')->first();
            if ($record) {
                $record->update([
                    'counselor_id' => $validated['counselor_id'],
                    'type' => $validated['session_type'] ?? $record->type ?? 'tatap_muka',
                    'scheduled_at' => $validated['scheduled_at'],
                    'end_time' => $validated['end_time'] ?? null,
                    'location' => $validated['location'] ?? null,
                ]);
            } else {
                CounselingRecord::create([
                    'client_id' => $client->id,
                    'counselor_id' => $validated['counselor_id'],
                    'type' => $validated['session_type'] ?? 'tatap_muka',
                    'scheduled_at' => $validated['scheduled_at'],
                    'end_time' => $validated['end_time'] ?? null,
                    'location' => $validated['location'] ?? null,
                    'status' => 'scheduled',
                ]);
            }
        }

        return redirect()->back()->with('success', 'Data klien berhasil diperbarui.');
    }

    public function destroy(Client $client)
    {
        $client->delete();
        return redirect()->route('clients.index')->with('success', 'Klien berhasil dihapus.');
    }

    public function restore($id)
    {
        $client = Client::withTrashed()->findOrFail($id);
        $client->restore();
        
        return redirect()->back()->with('success', 'Data klien berhasil dikembalikan.');
    }

    public function forceDelete($id)
    {
        $client = Client::withTrashed()->findOrFail($id);
        $client->forceDelete();
        
        return redirect()->back()->with('success', 'Data klien berhasil dihapus permanen.');
    }
}
