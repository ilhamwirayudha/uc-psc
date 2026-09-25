<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingParticipant;
use App\Models\Counselor;
use App\Models\Client;
use App\Models\User;
use App\Models\Pairing;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class BookingController extends Controller
{
    use AuthorizesRequests;
    /**
     * Daftar semua booking dengan scoping akses.
     */
    public function index(Request $request)
    {
        $query = Booking::with([
            'client.assignedStaff',
            'client.pairings.counselor',
            'client.clientParticipants',
            'client.bookings.counselor',
            'client.bookings.staffPenguji',
            'client.bookings.staffKoreksi',
            'client.bookings.staffPelapor',
            'client.testResults',
            'counselor',
            'staffPenguji',
            'staffKoreksi',
            'staffPelapor',
            'followUpOf',
            'followUps',
            'participants',
            'paymentTransaction'
        ])->latest();

        // Staff hanya bisa lihat booking dari klien yang ditugaskan kepadanya,
        // ATAU booking di mana dirinya tercatat sebagai staff penguji/koreksi/pelapor
        if (auth()->user()->role === 'staff') {
            $staffId = auth()->id();
            $query->where(function ($q) use ($staffId) {
                $q->whereHas('client', fn($p) => $p->where('assigned_staff_id', $staffId))
                  ->orWhere('staff_penguji_id', $staffId)
                  ->orWhere('staff_koreksi_id', $staffId)
                  ->orWhere('staff_pelapor_id', $staffId);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('client', fn($q) => $q->where('name', 'like', "%{$search}%"));
        }

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $bookings = $query->paginate(15)->withQueryString();
        $clients = Client::orderBy('name')->get();
        $counselors = Counselor::where('status', 'active')->orderBy('name')->get();
        $staffs = User::whereIn('role', ['staff', 'admin'])->orderBy('name')->get();

        return view('bookings.index', compact('bookings', 'clients', 'counselors', 'staffs'));
    }

    /**
     * Form tambah booking baru.
     */
    public function create(Request $request)
    {
        return redirect()->route('dashboard')->with('error', 'Silakan gunakan tombol pop-up Booking pada halaman ini.');
    }

    /**
     * Simpan booking baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'kategori' => 'required|in:konseling,psikotes',
            'tanggal_booking_dibuat' => 'required|date',
            'tanggal_dijadwalkan' => 'nullable|date',
            'status' => 'required|in:baru,lanjutan,selesai',
            'staff_penguji_id' => 'nullable|exists:users,id',
            'staff_koreksi_id' => 'nullable|exists:users,id',
            'staff_pelapor_id' => 'nullable|exists:users,id',
            'counselor_id' => 'nullable|exists:counselors,id',
            'payment_transaction_id' => 'nullable|exists:payment_transactions,id',
            'notes' => 'nullable|string',
            'participants' => 'nullable|array',
            'participants.*' => 'nullable|string|max:255',
            'start_time' => 'nullable|string',
            'end_time' => 'nullable|string',
            'session_type' => 'nullable|in:tatap_muka,online,whatsapp',
            'location' => 'nullable|string|max:255',
        ]);

        $extraNotes = [];
        if ($request->filled('start_time') || $request->filled('end_time')) {
            $startTime = $request->input('start_time');
            $endTime = $request->input('end_time');
            $timeRange = trim(($startTime ?? '') . ($endTime ? ' - ' . $endTime : ''), ' -');
            if ($timeRange) {
                $extraNotes[] = "Waktu: " . $timeRange;
            }
        }
        if ($request->filled('session_type')) {
            $sessionTypeLabel = match($request->input('session_type')) {
                'online' => 'Online',
                'whatsapp' => 'WhatsApp',
                default => 'Tatap Muka',
            };
            $extraNotes[] = "Tipe Sesi: " . $sessionTypeLabel;
        }
        if ($request->filled('location')) {
            $extraNotes[] = "Lokasi: " . $request->input('location');
        }

        if (!empty($extraNotes)) {
            $sessionInfo = implode(' | ', $extraNotes);
            $validated['notes'] = !empty($validated['notes'])
                ? $validated['notes'] . "\n[" . $sessionInfo . "]"
                : $sessionInfo;
        }

        $booking = Booking::create($validated);

        // Simpan peserta jika ada
        if (!empty($validated['participants'])) {
            foreach ($validated['participants'] as $nama) {
                if (trim($nama) !== '') {
                    BookingParticipant::create([
                        'booking_id' => $booking->id,
                        'nama_peserta' => trim($nama),
                    ]);
                }
            }
        }

        $client = Client::find($validated['client_id']);

        $clientUpdates = ['service_type' => $validated['kategori']];
        if ($request->filled('jenis')) {
            $clientUpdates['jenis'] = $request->input('jenis');
        }
        if ($request->filled('counseling_type')) {
            $clientUpdates['counseling_type'] = $request->input('counseling_type');
        }
        if (!empty($validated['staff_penguji_id']) && empty($client->assigned_staff_id)) {
            $clientUpdates['assigned_staff_id'] = $validated['staff_penguji_id'];
            $clientUpdates['status'] = 'assigned';
        }
        $client->update($clientUpdates);

        // Jika konseling dan konselor dipilih: sinkronkan Pairing
        if ($validated['kategori'] === 'konseling' && !empty($validated['counselor_id'])) {
            Pairing::firstOrCreate([
                'client_id' => $client->id,
                'counselor_id' => $validated['counselor_id'],
            ], [
                'status' => 'active',
                'assigned_by' => auth()->id(),
            ]);
        }

        if ($request->filled('from_modal')) {
            return redirect()->back()->with('success', 'Booking berhasil dibuat.');
        }

        return redirect()->route('bookings.index')->with('success', 'Booking berhasil dibuat.');
    }

    /**
     * Detail booking.
     */
    public function show(Booking $booking)
    {
        $this->authorize('view', $booking);

        $booking->load([
            'client.assignedStaff',
            'client.pairings.counselor',
            'client.clientParticipants',
            'client.bookings.counselor',
            'client.bookings.staffPenguji',
            'client.bookings.staffKoreksi',
            'client.bookings.staffPelapor',
            'client.testResults',
            'counselor',
            'staffPenguji',
            'staffKoreksi',
            'staffPelapor',
            'followUpOf',
            'followUps',
            'participants',
            'paymentTransaction',
        ]);

        $jamDibuat = $booking->created_at ? $booking->created_at->format('H:i') . ' WIB' : '-';
        $jamSesi = null;
        if (!empty($booking->notes) && preg_match('/(?:Waktu|Jam)\s*:\s*([0-9]{1,2}:[0-9]{2}(?:\s*-\s*[0-9]{1,2}:[0-9]{2})?)/i', $booking->notes, $matches)) {
            $jamSesi = trim($matches[1]) . ' WIB';
        }

        $clientData = $booking->client ? [
            'id' => $booking->client->id,
            'name' => $booking->client->name,
            'jenis' => $booking->client->jenis ?? 'individual',
            'pic_name' => $booking->client->pic_name,
            'phone' => $booking->client->phone ?? '-',
            'email' => $booking->client->email ?? '-',
            'gender' => $booking->client->gender_label ?? '-',
            'dob' => $booking->client->dob ? $booking->client->dob->locale('id')->isoFormat('D MMMM Y') : '-',
            'age' => $booking->client->age_label,
            'occupation' => $booking->client->occupation ?? '-',
            'education' => $booking->client->education_label ?? '-',
            'marital_status' => $booking->client->marital_status ?? '-',
            'city' => $booking->client->city ?? '',
            'province' => $booking->client->province ?? '',
            'country' => $booking->client->country ?? 'Indonesia',
            'address' => $booking->client->address ?? '-',
            'status' => $booking->client->status ?? 'unassigned',
            'status_label' => ucfirst(str_replace('_', ' ', $booking->client->status ?? 'unassigned')),
            'service_type' => $booking->client->service_type ?? '-',
            'counseling_type' => $booking->client->counseling_type ?? '-',
            'notes' => $booking->client->notes ?? '',
            'assigned_staff' => $booking->client->assignedStaff ? [
                'name' => $booking->client->assignedStaff->name,
                'email' => $booking->client->assignedStaff->email,
            ] : null,
            'active_counselor' => $booking->client->pairings->where('status', 'active')->first()?->counselor ? [
                'name' => $booking->client->pairings->where('status', 'active')->first()->counselor->name,
                'specialization' => $booking->client->pairings->where('status', 'active')->first()->counselor->specialization,
            ] : null,
            'participants' => $booking->client->clientParticipants->pluck('nama_peserta')->values()->toArray(),
            'bookings' => $booking->client->bookings->map(fn($b) => [
                'id' => $b->id,
                'kategori' => $b->kategori,
                'status' => $b->status,
                'status_label' => ucfirst(str_replace('_', ' ', $b->status)),
                'tanggal_dibuat' => $b->tanggal_booking_dibuat->format('d M Y'),
                'tanggal_dijadwalkan' => $b->tanggal_dijadwalkan ? $b->tanggal_dijadwalkan->format('d M Y') : 'Belum Dijadwalkan',
                'counselor_name' => $b->counselor?->name,
                'staff_penguji_name' => $b->staffPenguji?->name,
                'notes' => $b->notes,
                'update_url' => route('bookings.update', $b),
            ])->values()->toArray(),
            'test_results' => $booking->client->testResults->map(fn($t) => [
                'id' => $t->id,
                'test_name' => $t->test_name,
                'status' => $t->status,
                'status_label' => $t->status_label ?? ucfirst(str_replace('_', ' ', $t->status)),
                'summary' => $t->result_summary,
                'url' => route('test-results.show', $t),
            ])->values()->toArray(),
            'show_url' => route('clients.show', $booking->client),
            'edit_url' => route('clients.edit', $booking->client),
        ] : null;

        return view('bookings.show', compact('booking', 'jamDibuat', 'jamSesi', 'clientData'));
    }

    /**
     * Form edit booking.
     */
    public function edit(Booking $booking)
    {
        $this->authorize('view', $booking);

        $booking->load(['participants']);
        $clients = Client::orderBy('name')->get();
        $counselors = Counselor::where('status', 'active')->orderBy('name')->get();
        $staffs = User::whereIn('role', ['staff', 'admin'])->orderBy('name')->get();

        return view('bookings.edit', compact('booking', 'clients', 'counselors', 'staffs'));
    }

    /**
     * Update booking.
     */
    public function update(Request $request, Booking $booking)
    {
        $this->authorize('update', $booking);

        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'kategori' => 'required|in:konseling,psikotes',
            'tanggal_booking_dibuat' => 'required|date',
            'tanggal_dijadwalkan' => 'nullable|date',
            'status' => 'required|in:baru,lanjutan,selesai',
            'staff_penguji_id' => 'nullable|exists:users,id',
            'staff_koreksi_id' => 'nullable|exists:users,id',
            'staff_pelapor_id' => 'nullable|exists:users,id',
            'counselor_id' => 'nullable|exists:counselors,id',
            'payment_transaction_id' => 'nullable|exists:payment_transactions,id',
            'notes' => 'nullable|string',
            'participants' => 'nullable|array',
            'participants.*' => 'nullable|string|max:255',
        ]);

        $booking->update($validated);

        // Update peserta — hapus lama, simpan baru
        $booking->participants()->delete();
        if (!empty($validated['participants'])) {
            foreach ($validated['participants'] as $nama) {
                if (trim($nama) !== '') {
                    BookingParticipant::create([
                        'booking_id' => $booking->id,
                        'nama_peserta' => trim($nama),
                    ]);
                }
            }
        }

        return redirect()->route('bookings.index')->with('success', 'Booking berhasil diperbarui.');
    }

    /**
     * Hapus booking.
     */
    public function destroy(Booking $booking)
    {
        $this->authorize('delete', $booking);

        $booking->delete();
        return redirect()->route('bookings.index')->with('success', 'Booking berhasil dihapus.');
    }

    /**
     * Buat booking follow-up dari booking yang sudah ada.
     */
    public function createFollowUp(Booking $booking)
    {
        $newBooking = Booking::create([
            'client_id' => $booking->client_id,
            'kategori' => $booking->kategori,
            'tanggal_booking_dibuat' => now()->toDateString(),
            'status' => 'lanjutan',
            'follow_up_of_booking_id' => $booking->id,
            'counselor_id' => $booking->counselor_id,
            'staff_penguji_id' => $booking->staff_penguji_id,
            'staff_koreksi_id' => $booking->staff_koreksi_id,
            'staff_pelapor_id' => $booking->staff_pelapor_id,
        ]);

        return redirect()->route('bookings.edit', $newBooking)->with('success', 'Booking lanjutan berhasil dibuat.');
    }
}
