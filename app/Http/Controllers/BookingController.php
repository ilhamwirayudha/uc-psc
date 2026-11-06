<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingParticipant;
use App\Models\Counselor;
use App\Models\Client;
use App\Models\ClientForm;
use App\Models\User;
use App\Models\Pairing;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class BookingController extends Controller
{
    use AuthorizesRequests;
    /**
     * Helper query builder untuk pemisahan layanan booking.
     */
    private function buildScopedBookingQuery(Request $request, string $kategori)
    {
        $query = Booking::with([
            'client.assignedStaff',
            'client.pairings.counselor',
            'client.clientParticipants',
            'client.bookings.counselor',
            'client.bookings.staffPenguji',
            'client.bookings.staffKoreksi',
            'client.bookings.staffPelapor',
            
            'counselor',
            'staffPenguji',
            'staffKoreksi',
            'staffPelapor',
            'followUpOf',
            'followUps',
            'participants',
            'paymentTransaction'
        ])->where('kategori', $kategori);

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

        // Filter pencarian nama klien
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('client', fn($q) => $q->where('name', 'like', "%{$search}%"));
        }

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return $query;
    }

    /**
     * Halaman Booking Layanan Konseling
     */
    public function counseling(Request $request)
    {
        $query = $this->buildScopedBookingQuery($request, 'konseling');

        $cSort = $request->input('c_sort');
        $cDir = strtolower($request->input('c_dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        if ($cSort === 'id') {
            $query->orderBy('id', $cDir);
        } elseif ($cSort === 'klien') {
            $query->join('clients', 'bookings.client_id', '=', 'clients.id')
                  ->orderBy('clients.name', $cDir)
                  ->select('bookings.*');
        } elseif ($cSort === 'tipe') {
            $query->join('clients', 'bookings.client_id', '=', 'clients.id')
                  ->orderBy('clients.counseling_type', $cDir)
                  ->select('bookings.*');
        } else {
            // Default: tanggal
            $query->orderByRaw('COALESCE(bookings.tanggal_dijadwalkan, bookings.tanggal_booking_dibuat, bookings.created_at) ' . $cDir)
                  ->orderBy('bookings.id', $cDir);
        }

        $counselingBookings = $query->paginate(15)->withQueryString();
        $clients = Client::orderBy('name')->get();
        $counselors = Counselor::where('status', 'active')->orderBy('name')->get();
        $staffs = User::whereIn('role', ['staff', 'admin'])->orderBy('name')->get();

        return view('bookings.booking-counseling-list', compact('counselingBookings', 'clients', 'counselors', 'staffs', 'cSort', 'cDir'));
    }

    /**
     * Halaman Booking Layanan Psikotes
     */
    public function psychotest(Request $request)
    {
        $query = $this->buildScopedBookingQuery($request, 'psikotes');

        $pSort = $request->input('p_sort');
        $pDir = strtolower($request->input('p_dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        if ($pSort === 'id') {
            $query->orderBy('id', $pDir);
        } elseif ($pSort === 'klien') {
            $query->join('clients', 'bookings.client_id', '=', 'clients.id')
                  ->orderBy('clients.name', $pDir)
                  ->select('bookings.*');
        } else {
            // Default: tanggal
            $query->orderByRaw('COALESCE(bookings.tanggal_dijadwalkan, bookings.tanggal_booking_dibuat, bookings.created_at) ' . $pDir)
                  ->orderBy('bookings.id', $pDir);
        }

        $psikotesBookings = $query->paginate(15)->withQueryString();
        $clients = Client::orderBy('name')->get();
        $counselors = Counselor::where('status', 'active')->orderBy('name')->get();
        $staffs = User::whereIn('role', ['staff', 'admin'])->orderBy('name')->get();

        return view('bookings.booking-psychotest-list', compact('psikotesBookings', 'clients', 'counselors', 'staffs', 'pSort', 'pDir'));
    }

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

        return view('bookings.booking-all-overview', compact('bookings', 'clients', 'counselors', 'staffs'));
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
            'tanggal_booking_dibuat' => 'nullable|date',
            'tanggal_dijadwalkan' => 'nullable|date',
            'status' => 'nullable|in:baru,lanjutan,selesai',
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
            'counseling_type' => 'nullable|string|max:255',
            'price' => 'nullable|numeric',
            'overtime_price' => 'nullable|numeric',
        ]);

        $validated['tanggal_booking_dibuat'] = $validated['tanggal_booking_dibuat'] ?? now()->toDateString();
        $validated['status'] = $validated['status'] ?? 'baru';
        if ($request->filled('counseling_type')) {
            $validated['service_package_name'] = $request->input('counseling_type');
        }

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
            $rawJ = strtolower($request->input('jenis'));
            $clientUpdates['jenis'] = in_array($rawJ, ['industri', 'company', 'perusahaan']) ? 'industri' : 'individu';
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
            'client.clientForms' => fn($q) => $q->latest(),
            'client.assignedStaff',
            'client.pairings.counselor',
            'client.clientParticipants',
            'client.bookings.counselor',
            'client.bookings.staffPenguji',
            'client.bookings.staffKoreksi',
            'client.bookings.staffPelapor',
            
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
        $startTime = $booking->session_start ? substr($booking->session_start, 0, 5) : '';
        $endTime = $booking->session_end ? substr($booking->session_end, 0, 5) : '';
        if (!empty($booking->notes) && preg_match('/(?:Waktu|Jam)\s*:\s*([0-9]{1,2}:[0-9]{2})(?:\s*-\s*([0-9]{1,2}:[0-9]{2}))?/i', $booking->notes, $matches)) {
            $jamSesi = trim($matches[0]);
            if (empty($startTime)) $startTime = $matches[1] ?? '';
            if (empty($endTime)) $endTime = $matches[2] ?? '';
        }
        $sessionType = $booking->session_type ?? 'tatap_muka';
        $location = $booking->location ?? '';
        $counselors = Counselor::where('status', 'active')->orderBy('name')->get();
        $staffs = User::whereIn('role', ['staff', 'admin'])->orderBy('name')->get();

        $clientData = $booking->client ? [
            'id' => $booking->client->id,
            'name' => $booking->client->name,
            'jenis' => $booking->client->jenis ?? 'individu',
            'pic_name' => $booking->client->pic_name,
            'phone' => $booking->client->phone ?? '-',
            'email' => $booking->client->email ?? '-',
            'gender' => $booking->client->gender_label ?? '-',
            'dob' => $booking->client->dob ? $booking->client->dob->locale('id')->isoFormat('D MMMM Y') : '-',
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
            'show_url' => route('clients.show', $booking->client),
            'edit_url' => route('clients.edit', $booking->client),
        ] : null;

        return view('bookings.booking-session-detail', compact('booking', 'jamDibuat', 'jamSesi', 'startTime', 'endTime', 'sessionType', 'location', 'counselors', 'staffs', 'clientData'));
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

        return view('bookings.booking-reschedule-form', compact('booking', 'clients', 'counselors', 'staffs'));
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
            'tanggal_booking_dibuat' => 'nullable|date',
            'tanggal_dijadwalkan' => 'nullable|date',
            'status' => 'nullable|string|max:50',
            'staff_penguji_id' => 'nullable|exists:users,id',
            'staff_koreksi_id' => 'nullable|exists:users,id',
            'staff_pelapor_id' => 'nullable|exists:users,id',
            'counselor_id' => 'nullable|exists:counselors,id',
            'payment_transaction_id' => 'nullable|exists:payment_transactions,id',
            'notes' => 'nullable|string',
            'session_notes' => 'nullable|string',
            'participants' => 'nullable|array',
            'participants.*' => 'nullable|string|max:255',
            'start_time' => 'nullable|string',
            'end_time' => 'nullable|string',
            'session_type' => 'nullable|in:tatap_muka,online,whatsapp',
            'location' => 'nullable|string|max:255',
            'counseling_type' => 'nullable|string|max:255',
            'price' => 'nullable|numeric',
            'overtime_price' => 'nullable|numeric',
            'actual_duration_minutes' => 'nullable|integer|min:0',
            'is_overtime' => 'nullable|boolean',
            'payment_workflow_status' => 'nullable|string|max:60',
            'va_number' => 'nullable|string|max:50',
        ]);

        $validated['tanggal_booking_dibuat'] = $validated['tanggal_booking_dibuat'] ?? $booking->tanggal_booking_dibuat ?? now()->toDateString();
        $validated['status'] = $validated['status'] ?? $booking->status ?? 'data_masuk';
        if ($request->filled('counseling_type')) {
            $validated['service_package_name'] = $request->input('counseling_type');
        }

        // Sinkronisasi start_time / end_time ke session_start / session_end
        if ($request->has('start_time')) {
            $validated['session_start'] = $request->input('start_time') ?: null;
        }
        if ($request->has('end_time')) {
            $validated['session_end'] = $request->input('end_time') ?: null;
        }

        // Kalkulasi otomatis durasi riil & overtime sesuai aturan 30 menit UC PSC
        $startTime = $validated['session_start'] ?? $booking->session_start;
        $endTime = $validated['session_end'] ?? $booking->session_end;
        if ($startTime && $endTime) {
            $sh = (int) substr($startTime, 0, 2);
            $sm = (int) substr($startTime, 3, 2);
            $eh = (int) substr($endTime, 0, 2);
            $em = (int) substr($endTime, 3, 2);
            $totalMins = ($eh * 60 + $em) - ($sh * 60 + $sm);
            if ($totalMins > 0) {
                $validated['actual_duration_minutes'] = $totalMins;
                // Rule UC PSC: 1 sesi = 60 mnt. Jika kelebihan >= 30 mnt (total > 90 mnt), kena overtime
                if ($totalMins > 90) {
                    $validated['is_overtime'] = true;
                    $validated['overtime_minutes'] = $totalMins - 60;
                } else {
                    $validated['is_overtime'] = false;
                    $validated['overtime_minutes'] = 0;
                }
            }
        }

        $booking->update($validated);

        if ($request->filled('counseling_type')) {
            $booking->client?->update([
                'counseling_type' => $request->input('counseling_type'),
            ]);
        }

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

        $cardMessages = [
            'card-paket-layanan' => 'Paket layanan berhasil diperbarui dan disimpan.',
            'step-1' => 'Paket layanan berhasil diperbarui dan disimpan.',
            'card-penugasan-pelaksana' => 'Penjadwalan dan penugasan psikolog berhasil diperbarui.',
            'card-jadwal-sesi' => 'Penjadwalan dan penugasan psikolog berhasil diperbarui.',
            'step-2' => 'Penjadwalan dan penugasan psikolog berhasil diperbarui.',
            'card-status-pembayaran' => 'Status pembayaran finance berhasil diperbarui.',
            'step-3' => 'Status pembayaran finance berhasil diperbarui.',
            'card-status-layanan' => 'Status layanan dan catatan booking berhasil disimpan.',
            'step-4' => 'Status layanan dan catatan booking berhasil disimpan.',
        ];
        $submittedCard = $request->input('submitted_card', '');
        $successMsg = $cardMessages[$submittedCard] ?? 'Booking berhasil diperbarui.';

        return redirect()->back()
            ->with('success', $successMsg)
            ->with('updated_card', $submittedCard);
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
     * Simpan realisasi sesi (jam masuk, jam keluar, catatan pasca-sesi).
     */
    public function storeRealisasi(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'session_start'             => 'required|date_format:H:i',
            'session_end'               => 'required|date_format:H:i|after:session_start',
            'session_duration_standard' => 'nullable|integer|min:1|max:480',
            'session_notes'             => 'nullable|string|max:2000',
            'session_type'              => 'nullable|in:tatap_muka,online,whatsapp',
            'location'                  => 'nullable|string|max:255',
            'status'                    => 'nullable|in:baru,lanjutan,selesai',
        ]);

        // Hitung overtime secara otomatis
        [$sh, $sm] = explode(':', $validated['session_start']);
        [$eh, $em] = explode(':', $validated['session_end']);
        $totalMinutes   = ((int)$eh * 60 + (int)$em) - ((int)$sh * 60 + (int)$sm);
        $standard       = (int)($validated['session_duration_standard'] ?? $booking->session_duration_standard ?? 60);
        $overtimeMinutes = max(0, $totalMinutes - $standard);

        $booking->update([
            'session_start'             => $validated['session_start'],
            'session_end'               => $validated['session_end'],
            'session_duration_standard' => $standard,
            'overtime_minutes'          => $overtimeMinutes,
            'session_notes'             => $validated['session_notes'] ?? null,
            'session_type'              => $validated['session_type'] ?? null,
            'location'                  => $validated['location'] ?? null,
            'status'                    => $validated['status'] ?? $booking->status,
        ]);

        // Jika diminta follow-up, buat booking lanjutan
        if ($request->boolean('needs_followup')) {
            Booking::create([
                'client_id'               => $booking->client_id,
                'kategori'                => $booking->kategori,
                'tanggal_booking_dibuat'  => now()->toDateString(),
                'status'                  => 'lanjutan',
                'follow_up_of_booking_id' => $booking->id,
                'counselor_id'            => $booking->counselor_id,
                'staff_penguji_id'        => $booking->staff_penguji_id,
            ]);
        }

        return redirect()->back()->with('success', 'Realisasi sesi berhasil disimpan.' .
            ($overtimeMinutes > 0 ? " Overtime: {$overtimeMinutes} menit." : ''));
    }

    /**
     * Tetapkan jadwal dan paket layanan resmi dari formulir pendaftaran klien.
     */
    public function scheduleFromForm(Request $request, ClientForm $clientForm)
    {
        $validated = $request->validate([
            'kategori' => 'required|string|in:konseling,psikotes',
            'service_tier' => 'required|string',
            'service_package_key' => 'required|string',
            'tanggal_dijadwalkan' => 'required|date',
            'session_start' => 'required|string',
            'session_end' => 'required|string',
            'session_type' => 'required|string|in:offline,online,tatap_muka',
            'location' => 'nullable|string|max:255',
            'counselor_id' => 'nullable|exists:counselors,id',
            'staff_penguji_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        $kategori = $validated['kategori'];
        $tier = $validated['service_tier'];
        $packageKey = $validated['service_package_key'];
        $catalog = config('service_catalog');

        $packageName = '';
        $price = 0;
        $overtimePrice = 0;

        if ($kategori === 'konseling') {
            $parts = explode('_', $packageKey);
            $tierKey = $tier;
            $klien = $parts[1] ?? 'wni';
            $kat = $parts[2] ?? 'umum';
            if (isset($parts[3])) {
                $kat .= '_' . $parts[3];
            }

            $pkgInfo = $catalog['konseling']['tiers'][$tierKey][$klien][$kat] ?? null;
            if ($pkgInfo) {
                $tierLabel = $catalog['konseling']['tiers'][$tierKey]['label'] ?? ucfirst($tierKey);
            $packageName = (str_starts_with($tierLabel, 'Konseling') ? $tierLabel : "Konseling {$tierLabel}") . " - " . strtoupper($klien) . " (" . ucwords(str_replace('_', ' ', $kat)) . ")";
                $price = $pkgInfo['price'] ?? 0;
                $overtimePrice = $pkgInfo['overtime'] ?? $price;
            } else {
                $packageName = "Konseling " . ucfirst($tier);
                $price = 525000;
                $overtimePrice = 525000;
            }
        } else {
            // Psikotes
            if ($tier === 'industri') {
                $pkgInfo = $catalog['psikotes']['industri']['packages'][$packageKey] ?? null;
                if ($pkgInfo) {
                    $packageName = "Psikotes Industri " . ($pkgInfo['format'] ?? '') . " " . ($pkgInfo['level'] ?? '') . " (" . ($pkgInfo['kategori'] ?? '') . ")";
                    $price = $pkgInfo['price'] ?? 0;
                }
            } elseif ($tier === 'non_industri_bakat_minat') {
                $pkgInfo = $catalog['psikotes']['non_industri_bakat_minat']['packages'][$packageKey] ?? null;
                if ($pkgInfo) {
                    $packageName = "Psikotes Non-Industri " . ($pkgInfo['format'] ?? '') . " (" . ($pkgInfo['kategori'] ?? '') . ")";
                    $price = $pkgInfo['price'] ?? 0;
                }
            } elseif ($tier === 'non_industri_khusus') {
                $pkgInfo = $catalog['psikotes']['non_industri_khusus']['packages'][$packageKey] ?? null;
                if ($pkgInfo) {
                    $packageName = "Psikotes Khusus " . ($pkgInfo['label'] ?? $packageKey);
                    $price = $pkgInfo['price'] ?? 0;
                }
            }

            if (!$packageName) {
                $packageName = "Psikotes " . ucwords(str_replace('_', ' ', $tier));
            }
        }

        // Cari atau inisialisasi Booking
        $booking = $clientForm->booking;
        if (!$booking) {
            $booking = Booking::createInitialForForm($clientForm);
        }

        $sessionTypeMapped = ($validated['session_type'] === 'offline') ? 'tatap_muka' : 'online';

        $booking->update([
            'client_form_id' => $clientForm->id,
            'kategori' => $kategori,
            'service_tier' => $tier,
            'service_package_key' => $packageKey,
            'service_package_name' => $packageName,
            'price' => $price,
            'overtime_price' => $overtimePrice,
            'tanggal_dijadwalkan' => $validated['tanggal_dijadwalkan'],
            'session_start' => $validated['session_start'],
            'session_end' => $validated['session_end'],
            'session_type' => $sessionTypeMapped,
            'location' => !empty($validated['location']) ? $validated['location'] : ($sessionTypeMapped === 'tatap_muka' ? 'Ruang Layanan UC PSC' : 'Online Video Conference'),
            'counselor_id' => !empty($validated['counselor_id']) ? $validated['counselor_id'] : null,
            'staff_penguji_id' => !empty($validated['staff_penguji_id']) ? $validated['staff_penguji_id'] : null,
            'notes' => !empty($validated['notes']) ? $validated['notes'] : null,
            'status' => 'baru',
        ]);

        if ($clientForm->booking_id !== $booking->id) {
            $clientForm->booking_id = $booking->id;
            $clientForm->save();
        }

        if ($booking->client) {
            $booking->client->update([
                'counseling_type' => $packageName,
            ]);
        }

        return redirect()->back()->with('success', "Jadwal & Paket Layanan berhasil ditetapkan: {$packageName}.");
    }
}
