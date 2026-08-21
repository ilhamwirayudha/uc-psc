<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingParticipant;
use App\Models\Counselor;
use App\Models\Client;
use App\Models\User;
use App\Models\Pairing;
use App\Models\CounselingRecord;
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
        $query = Booking::with(['client', 'counselor', 'staffPenguji', 'staffKoreksi', 'staffPelapor', 'followUpOf', 'participants'])
            ->latest();

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

        return view('bookings.index', compact('bookings'));
    }

    /**
     * Form tambah booking baru.
     */
    public function create(Request $request)
    {
        $clients = Client::orderBy('name')->get();
        $counselors = Counselor::where('status', 'active')->orderBy('name')->get();
        $staffs = User::whereIn('role', ['staff', 'admin'])->orderBy('name')->get();
        $selectedClient = $request->filled('client_id') ? Client::find($request->client_id) : null;

        return view('bookings.create', compact('clients', 'counselors', 'staffs', 'selectedClient'));
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

        // Jika konseling dan konselor dipilih: sinkronkan Pairing & CounselingRecord
        if ($validated['kategori'] === 'konseling' && !empty($validated['counselor_id'])) {
            Pairing::firstOrCreate([
                'client_id' => $client->id,
                'counselor_id' => $validated['counselor_id'],
            ], [
                'status' => 'active',
                'assigned_by' => auth()->id(),
            ]);

            if (!empty($validated['tanggal_dijadwalkan'])) {
                $startTime = $request->input('start_time', '09:00');
                $endTime = $request->input('end_time', '10:30');
                $scheduledAt = $validated['tanggal_dijadwalkan'] . ' ' . $startTime . ':00';
                $endAt = $validated['tanggal_dijadwalkan'] . ' ' . $endTime . ':00';

                CounselingRecord::create([
                    'client_id' => $client->id,
                    'counselor_id' => $validated['counselor_id'],
                    'admin_id' => auth()->id(),
                    'type' => $request->input('session_type', 'tatap_muka'),
                    'scheduled_at' => $scheduledAt,
                    'end_time' => $endAt,
                    'location' => $request->input('location'),
                    'status' => 'scheduled',
                ]);
            }
        }

        return redirect()->route('bookings.create')->with('success', 'Booking berhasil dibuat.');
    }

    /**
     * Detail booking.
     */
    public function show(Booking $booking)
    {
        $this->authorize('view', $booking);

        $booking->load(['client', 'counselor', 'staffPenguji', 'staffKoreksi', 'staffPelapor', 'followUpOf', 'followUps', 'participants', 'paymentTransaction']);

        return view('bookings.show', compact('booking'));
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
     * Hapus booking (soft delete).
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
