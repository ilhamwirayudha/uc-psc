<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingAssignee;
use App\Models\BookingCandidate;
use App\Models\BookingParticipant;
use App\Models\BookingServiceItem;
use App\Models\Counselor;
use App\Models\Client;
use App\Models\ClientForm;
use App\Models\User;
use App\Models\Pairing;
use App\Services\GoogleCalendarService;
use App\Services\PsychotestSlaService;
use Carbon\Carbon;
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
            'company',
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
            'assignees.user',
            'assignees.counselor',
            'candidates.assignedPsikolog',
            'candidates.assignedEditor',
            'serviceItems',
            'followUpOf',
            'followUps',
            'participants',
            'paymentTransaction',
            'googleCalendarEvents',
        ])->where('kategori', $kategori);

        $user = auth()->user();
        if ($user->isPsikolog()) {
            $counselorId = (int) ($user->counselor_id ?? 0);
            if ($counselorId > 0) {
                $query->where(function ($q) use ($counselorId) {
                    $q->where('counselor_id', $counselorId)
                      ->orWhereHas('assignees', fn ($aq) => $aq->where('counselor_id', $counselorId))
                      ->orWhereHas('candidates', fn ($cq) => $cq->where('assigned_psikolog_id', $counselorId));
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        // Filter pencarian nama klien, perusahaan, PIC, atau kandidat
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('pic_name', 'like', "%{$search}%")
                  ->orWhereHas('client', fn ($cq) => $cq->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('company', fn ($coq) => $coq->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('candidates', fn ($kq) => $kq->where('candidate_name', 'like', "%{$search}%"));
            });
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
        } elseif ($cSort === 'kewarganegaraan') {
            $query->join('clients', 'bookings.client_id', '=', 'clients.id')
                  ->orderByRaw("CASE WHEN (UPPER(COALESCE(clients.counseling_type, '')) LIKE '%WNA%' OR UPPER(COALESCE(bookings.service_package_name, '')) LIKE '%WNA%' OR UPPER(COALESCE(bookings.notes, '')) LIKE '%WNA%' OR UPPER(COALESCE(clients.notes, '')) LIKE '%WNA%') THEN 1 ELSE 0 END {$cDir}")
                  ->orderBy('bookings.id', 'desc')
                  ->select('bookings.*');
        } elseif ($cSort === 'psikolog') {
            $query->leftJoin('counselors', 'bookings.counselor_id', '=', 'counselors.id')
                  ->orderByRaw("COALESCE(counselors.name, 'zzzz') {$cDir}")
                  ->orderBy('bookings.id', 'desc')
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
        $pendingBiographyCount = auth()->user()->isAdmin()
            ? Booking::where('kategori', 'pending')->where('status', '!=', 'batal')->count()
            : 0;

        return view('bookings.booking-counseling-list', compact('counselingBookings', 'clients', 'counselors', 'staffs', 'cSort', 'cDir', 'pendingBiographyCount'));
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
        $staffs = User::where('role', 'staff')->orderBy('name')->get();

        // Panel Prioritas KILAT (Opsi A)
        $kilatQuery = BookingCandidate::with(['booking.company', 'booking.client', 'assignedPsikolog', 'assignedEditor'])
            ->where('is_kilat', true)
            ->where('attendance_status', '!=', 'absen')
            ->whereNull('kilat_sent_at')
            ->whereNull('step_kirim_at')
            ->whereHas('booking', fn ($q) => $q->where('kategori', 'psikotes')->where('status', '!=', 'batal'))
            ->orderBy('client_deadline_date');

        $user = auth()->user();
        if ($user->isPsikolog()) {
            $kilatQuery->where('assigned_psikolog_id', (int) ($user->counselor_id ?? 0));
        }

        $kilatQueue = $kilatQuery->get();
        $unbookedFormsCount = ClientForm::whereHas('client')
            ->whereIn('form_type', ['industri', 'non_industri'])
            ->where(fn ($q) => $q->where('booking_status', 'belum_dibooking')->orWhereNull('booking_id'))
            ->count();

        return view('bookings.booking-psychotest-list', compact(
            'psikotesBookings',
            'clients',
            'counselors',
            'staffs',
            'pSort',
            'pDir',
            'kilatQueue',
            'unbookedFormsCount'
        ));
    }

    /**
     * Daftar semua booking dengan scoping akses.
     */
    public function index(Request $request)
    {
        $query = Booking::with([
            'company',
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
            'assignees.user',
            'assignees.counselor',
            'candidates',
            'followUpOf',
            'followUps',
            'participants',
            'paymentTransaction'
        ])->latest();

        $user = auth()->user();
        if ($user->isPsikolog()) {
            $counselorId = (int) ($user->counselor_id ?? 0);
            $query->where(function ($q) use ($counselorId) {
                $q->where('counselor_id', $counselorId)
                  ->orWhereHas('assignees', fn ($aq) => $aq->where('counselor_id', $counselorId))
                  ->orWhereHas('candidates', fn ($cq) => $cq->where('assigned_psikolog_id', $counselorId));
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('pic_name', 'like', "%{$search}%")
                  ->orWhereHas('client', fn ($cq) => $cq->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('company', fn ($coq) => $coq->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('candidates', fn ($kq) => $kq->where('candidate_name', 'like', "%{$search}%"));
            });
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
        $this->authorize('create', Booking::class);

        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'company_id' => 'nullable|exists:companies,id',
            'pic_name' => 'nullable|string|max:255',
            'kategori' => 'required|in:konseling,psikotes',
            'service_tier' => 'nullable|string|max:60',
            'service_package_key' => 'nullable|string|max:100',
            'billing_scheme' => 'nullable|in:v,I,R,-',
            'delivery_mode' => 'nullable|in:softcopy,hardcopy,both',
            'requires_outgoing_letter' => 'nullable|boolean',
            'is_kilat' => 'nullable|boolean',
            'tanggal_booking_dibuat' => 'nullable|date',
            'tanggal_dijadwalkan' => 'nullable|date',
            'status' => 'nullable|string|max:50',
            'staff_penguji_id' => 'nullable|exists:users,id',
            'staff_koreksi_id' => 'nullable|exists:users,id',
            'staff_koreksi_software_id' => 'nullable|exists:users,id',
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

        if (
            $validated['kategori'] === 'psikotes'
            && $request->boolean('is_kilat')
            && !empty($validated['counselor_id'])
            && !empty($validated['tanggal_dijadwalkan'])
        ) {
            $slaService = app(PsychotestSlaService::class);
            $scheduledStr = Carbon::parse($validated['tanggal_dijadwalkan'])->toDateString();
            if ($slaService->countKilatForPsychologistOnDate((int) $validated['counselor_id'], $scheduledStr) >= 1) {
                $counselorName = Counselor::find($validated['counselor_id'])?->name ?? 'Psikolog';
                return back()->withInput()->with('booking_modal_error', true)->withErrors([
                    'counselor_id' => "{$counselorName} sudah memegang peserta KILAT lain pada tanggal {$scheduledStr}.",
                ]);
            }
        }

        if (!empty($validated['company_id']) && empty($validated['pic_name'])) {
            $validated['pic_name'] = \App\Models\Company::find($validated['company_id'])?->pic_name;
        }

        $validated['tanggal_booking_dibuat'] = $validated['tanggal_booking_dibuat'] ?? now()->toDateString();
        $validated['status'] = $validated['status'] ?? ($validated['kategori'] === 'konseling' ? 'data_masuk' : 'baru');
        if ($request->filled('start_time')) {
            $validated['session_start'] = $request->input('start_time');
        }
        if ($request->filled('end_time')) {
            $validated['session_end'] = $request->input('end_time');
        }
        if ($request->filled('counseling_type')) {
            $validated['service_package_name'] = $request->input('counseling_type');
        }

        // Resolusi harga katalog resmi untuk Psikotes bila memilih service_tier + service_package_key
        $resolvedUnitPrice = (float) ($validated['price'] ?? 0);
        if ($validated['kategori'] === 'psikotes' && !empty($validated['service_tier']) && !empty($validated['service_package_key'])) {
            $pkgInfo = config("service_catalog.psikotes.{$validated['service_tier']}.packages.{$validated['service_package_key']}");
            if ($pkgInfo) {
                $validated['service_package_name'] = $validated['service_package_name'] ?? ($pkgInfo['label'] ?? $validated['service_package_key']);
                if (!$request->filled('price')) {
                    $resolvedUnitPrice = (float) ($pkgInfo['price'] ?? 0);
                    $validated['price'] = $resolvedUnitPrice;
                }
            }
        }

        $validated['billing_scheme'] = $validated['billing_scheme'] ?? 'v';
        $validated['delivery_mode'] = $validated['delivery_mode'] ?? 'softcopy';
        $validated['requires_outgoing_letter'] = $request->boolean('requires_outgoing_letter');

        if ($validated['billing_scheme'] === '-') {
            $validated['payment_workflow_status'] = 'lunas';
            $validated['paid_at'] = now();
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

        $cleanParticipants = collect($validated['participants'] ?? [])
            ->map(fn ($p) => trim((string) $p))
            ->filter(fn ($p) => $p !== '')
            ->values();

        if ($validated['kategori'] === 'psikotes' && $resolvedUnitPrice > 0 && !$request->filled('price')) {
            $qtyCount = max(1, $cleanParticipants->count());
            $validated['price'] = $resolvedUnitPrice * $qtyCount;
        }

        $booking = Booking::create($validated);

        // Simpan peserta jika ada
        foreach ($cleanParticipants as $nama) {
            BookingParticipant::create([
                'booking_id' => $booking->id,
                'nama_peserta' => $nama,
            ]);
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

        // Jika psikotes dibuat dari modal global, pastikan masuk workflow kandidat & Kelola Penugasan
        if ($validated['kategori'] === 'psikotes') {
            $candidateNames = $cleanParticipants->isNotEmpty()
                ? $cleanParticipants
                : collect([$client->name]);

            $serviceItem = null;
            if (!empty($validated['service_tier']) && !empty($validated['service_package_key'])) {
                $serviceItem = BookingServiceItem::create([
                    'booking_id' => $booking->id,
                    'service_tier' => $validated['service_tier'],
                    'service_package_key' => $validated['service_package_key'],
                    'service_package_name' => $validated['service_package_name'] ?? $validated['service_package_key'],
                    'quantity' => $candidateNames->count(),
                    'unit_price' => $resolvedUnitPrice,
                    'subtotal_price' => $resolvedUnitPrice * $candidateNames->count(),
                ]);
            }

            $isSpecial = $booking->isSpecialNonIndustri();
            $psikologId = (!$isSpecial && !empty($validated['counselor_id'])) ? (int) $validated['counselor_id'] : null;
            $editorId = !empty($validated['staff_pelapor_id']) ? (int) $validated['staff_pelapor_id'] : null;

            foreach ($candidateNames as $cName) {
                BookingCandidate::create([
                    'booking_id' => $booking->id,
                    'client_id' => $client->id,
                    'booking_service_item_id' => $serviceItem?->id,
                    'candidate_name' => $cName,
                    'is_kilat' => $request->boolean('is_kilat'),
                    'attendance_status' => 'pending',
                    'assigned_psikolog_id' => $psikologId,
                    'assigned_editor_id' => $editorId,
                ]);
            }

            if ($psikologId) {
                BookingAssignee::firstOrCreate([
                    'booking_id' => $booking->id,
                    'role_type' => 'psikolog',
                    'counselor_id' => $psikologId,
                ]);
            }
            if (!$isSpecial && !empty($validated['staff_penguji_id'])) {
                BookingAssignee::firstOrCreate([
                    'booking_id' => $booking->id,
                    'role_type' => 'tester',
                    'user_id' => (int) $validated['staff_penguji_id'],
                ]);
            }
            if (!empty($validated['staff_koreksi_id'])) {
                BookingAssignee::firstOrCreate([
                    'booking_id' => $booking->id,
                    'role_type' => 'skorer_manual',
                    'user_id' => (int) $validated['staff_koreksi_id'],
                ]);
            }
            if (!empty($validated['staff_koreksi_software_id'])) {
                BookingAssignee::firstOrCreate([
                    'booking_id' => $booking->id,
                    'role_type' => 'skorer_software',
                    'user_id' => (int) $validated['staff_koreksi_software_id'],
                ]);
            }
            if ($editorId) {
                BookingAssignee::firstOrCreate([
                    'booking_id' => $booking->id,
                    'role_type' => 'editor',
                    'user_id' => $editorId,
                ]);
            }

            app(PsychotestSlaService::class)->recalculateForBooking($booking);
        }

        app(GoogleCalendarService::class)->syncBooking($booking);

        if ($request->filled('from_modal')) {
            return redirect()->back()->with('success', 'Booking berhasil dibuat.');
        }

        return redirect()->route($validated['kategori'] === 'psikotes' ? 'bookings.psychotest' : 'bookings.counseling')
            ->with('success', 'Booking berhasil dibuat.');
    }

    /**
     * Detail booking.
     */
    public function show(Booking $booking)
    {
        if ($booking->kategori === 'psikotes') {
            return redirect()->route('psychotest-bookings.show', $booking);
        }

        $this->authorize('view', $booking);

        $booking->load([
            'client.clientForms' => fn($q) => $q->latest(),
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
            'googleCalendarEvents.user',
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
            'address' => $booking->client->address ?? '-',
            'status' => $booking->client->status ?? 'unassigned',
            'status_label' => ucfirst(str_replace('_', ' ', $booking->client->status ?? 'unassigned')),
            'service_type' => $booking->client->service_type ?? '-',
            'counseling_type' => $booking->client->counseling_type ?? '-',
            'notes' => $booking->client->notes ?? '',
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
        if ($booking->kategori === 'psikotes') {
            return redirect()->route('psychotest-bookings.show', $booking);
        }

        $this->authorize('update', $booking);

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
            'final_price' => 'nullable|numeric|min:0',
            'overtime_price' => 'nullable|numeric',
            'actual_duration_minutes' => 'nullable|integer|min:0',
            'is_overtime' => 'nullable|boolean',
            'payment_workflow_status' => 'nullable|string|max:60',
            'va_number' => 'nullable|string|max:50',
            'finance_invoice_number' => 'nullable|string|max:100',
            'payment_proof_notes' => 'nullable|string|max:2000',
            'overtime_payment_status' => 'nullable|string|max:40',
            'overtime_va_number' => 'nullable|string|max:50',
            'overtime_final_price' => 'nullable|numeric|min:0',
            'rescheduled_by' => 'nullable|in:client,psychologist',
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

        // Deteksi Reschedule Konseling: jika reschedule < 24 jam atau saat sesi dan sudah lunas => HANGUS & BAYAR ULANG
        $forfeitNotice = null;
        $wasPending = $booking->kategori === 'pending';
        $oldDateStr = $booking->tanggal_dijadwalkan ? $booking->tanggal_dijadwalkan->format('Y-m-d') : null;
        $newDateStr = !empty($validated['tanggal_dijadwalkan']) ? Carbon::parse($validated['tanggal_dijadwalkan'])->format('Y-m-d') : $oldDateStr;
        $oldStartStr = $booking->session_start ? substr($booking->session_start, 0, 5) : null;
        $newStartStr = !empty($validated['session_start']) ? substr($validated['session_start'], 0, 5) : $oldStartStr;

        $isScheduleChanged = ($oldDateStr && $newDateStr && $oldDateStr !== $newDateStr)
            || ($oldStartStr && $newStartStr && $oldStartStr !== $newStartStr);

        $isReschedulableStatus = !in_array($booking->status, ['perlu_bayar_overtime', 'perlu_sesi_lanjutan', 'selesai', 'batal'], true);
        $isNonScheduleCard = in_array($request->input('submitted_card'), ['step-1', 'card-paket-layanan', 'step-3', 'card-status-pembayaran', 'step-4', 'card-status-layanan'], true);

        if ($booking->kategori === 'konseling' && $isScheduleChanged && $oldDateStr && $isReschedulableStatus && !$isNonScheduleCard) {
            $originalSessionAt = Carbon::parse($oldDateStr . ' ' . ($oldStartStr ?: '09:00'));
            $rescheduledBy = $validated['rescheduled_by'] ?? 'client';
            $validated['rescheduled_at'] = now();
            $validated['rescheduled_by'] = $rescheduledBy;

            $hoursBeforeSession = now()->diffInMinutes($originalSessionAt, false) / 60;
            if ($hoursBeforeSession < 24 && $booking->isPaid()) {
                if ($rescheduledBy === 'psychologist') {
                    $forfeitNotice = ' Jadwal diperbarui atas permintaan Psikolog — pembayaran klien tetap aman (tidak hangus).';
                } else {
                    $validated['is_payment_forfeited'] = true;
                    $validated['forfeited_amount'] = $booking->final_price ?? $booking->price ?? 0;
                    $validated['forfeited_reason'] = 'Reschedule mendadak oleh klien (< 24 jam sebelum sesi atau saat sesi berlangsung)';
                    $validated['payment_workflow_status'] = 'draft_ke_finance';
                    $validated['va_number'] = null;
                    $validated['finance_invoice_number'] = null;
                    $validated['paid_at'] = null;
                    $forfeitNotice = ' Perhatian: Reschedule dilakukan < 24 jam oleh klien / saat sesi berlangsung, sehingga pembayaran sebelumnya HANGUS dan tagihan baru perlu diajukan ke UC Finance.';
                }
            }
        }

        // Catat paid_at otomatis bila status berubah ke lunas
        if (isset($validated['payment_workflow_status'])) {
            if (in_array($validated['payment_workflow_status'], ['lunas', 'lunas_overtime'], true) && !$booking->paid_at && empty($forfeitNotice)) {
                $validated['paid_at'] = now();
            } elseif (!in_array($validated['payment_workflow_status'], ['lunas', 'lunas_overtime'], true)) {
                $validated['paid_at'] = null;
            }
        }

        // Kalkulasi otomatis durasi riil & overtime kelipatan blok 60 mnt setelah toleransi 30 mnt (Opsi A).
        // Hanya tandai overtime jika sesi memasuki status realisasi/pasca-sesi (bukan perencanaan slot awal).
        $startTime = $validated['session_start'] ?? $booking->session_start;
        $endTime = $validated['session_end'] ?? $booking->session_end;
        $isRealizedStatus = in_array($validated['status'], ['perlu_bayar_overtime', 'perlu_sesi_lanjutan', 'selesai'], true);
        if ($startTime && $endTime) {
            $sh = (int) substr($startTime, 0, 2);
            $sm = (int) substr($startTime, 3, 2);
            $eh = (int) substr($endTime, 0, 2);
            $em = (int) substr($endTime, 3, 2);
            $totalMins = ($eh * 60 + $em) - ($sh * 60 + $sm);
            if ($totalMins > 0) {
                $validated['actual_duration_minutes'] = $totalMins;
                $standard = (int) ($booking->session_duration_standard ?: 60);
                // Rule UC PSC: 1 sesi = 60 mnt + toleransi 30 mnt (>90 mnt), lalu kelipatan blok 60 mnt (Opsi A)
                $otUnits = ($isRealizedStatus && $totalMins > ($standard + 30))
                    ? Booking::calculateOvertimeUnits($totalMins, $standard)
                    : 0;
                if ($otUnits > 0) {
                    $validated['is_overtime'] = true;
                    $validated['overtime_minutes'] = $totalMins - $standard;
                    $validated['overtime_units'] = $otUnits;
                    if (empty($validated['overtime_payment_status']) && $booking->overtime_payment_status === 'tidak_ada') {
                        $validated['overtime_payment_status'] = 'menunggu_nota';
                    }
                } else {
                    $validated['is_overtime'] = false;
                    $validated['overtime_minutes'] = 0;
                    $validated['overtime_units'] = 0;
                    $validated['overtime_payment_status'] = 'tidak_ada';
                }
            }
        }

        if ($wasPending && $validated['kategori'] === 'psikotes' && in_array($validated['status'], ['data_masuk', 'waiting_list', 'menunggu_konfirmasi'], true)) {
            $validated['status'] = 'baru';
        }

        $booking->update($validated);

        if ($booking->kategori === 'psikotes') {
            if ($wasPending && $booking->candidates()->doesntExist()) {
                $booking->loadMissing(['client', 'clientForm']);
                $candForm = $booking->clientForm;
                $booking->candidates()->create([
                    'client_id' => $booking->client_id,
                    'client_form_id' => $candForm?->id,
                    'candidate_name' => $candForm?->client_name ?? $booking->client?->name ?? 'Kandidat',
                    'target_position' => $candForm?->answers['posisi_dituju'] ?? ($candForm?->answers['tujuan_asesmen'] ?? null),
                    'attendance_status' => 'pending',
                    'assigned_psikolog_id' => (!$booking->isSpecialNonIndustri() && $booking->counselor_id) ? $booking->counselor_id : null,
                ]);
            }

            if ($booking->status === 'batal') {
                ClientForm::where('booking_id', $booking->id)->update([
                    'booking_id' => null,
                    'booking_status' => 'belum_dibooking',
                ]);
            }

            if ($isScheduleChanged || $wasPending || $booking->status === 'batal') {
                app(PsychotestSlaService::class)->recalculateForBooking(
                    $booking,
                    [],
                    $oldDateStr ? Carbon::parse($oldDateStr) : null
                );
            }
        }

        // Sinkronisasi Google Calendar jika Konseling memasuki status terkonfirmasi atau di-reschedule/batal
        app(GoogleCalendarService::class)->syncBooking($booking);

        if ($request->filled('counseling_type')) {
            $booking->client?->update([
                'counseling_type' => $request->input('counseling_type'),
            ]);
        }

        // Update peserta hanya bila field participants dikirim oleh form
        if ($request->has('participants')) {
            $booking->participants()->delete();
            if (!empty($validated['participants'])) {
                foreach ($validated['participants'] as $nama) {
                    if (trim((string) $nama) !== '') {
                        BookingParticipant::create([
                            'booking_id' => $booking->id,
                            'nama_peserta' => trim((string) $nama),
                        ]);
                    }
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
        $successMsg = ($cardMessages[$submittedCard] ?? 'Booking berhasil diperbarui.') . ($forfeitNotice ?? '');

        if ($wasPending && $booking->kategori === 'psikotes') {
            return redirect()->route('psychotest-bookings.show', $booking)
                ->with('success', 'Booking berhasil diklasifikasikan ke Layanan Psikotes.');
        }

        return redirect()->back()
            ->with('success', $successMsg)
            ->with('updated_card', $submittedCard);
    }

    /**
     * Hapus booking beserta pembersihan event GCal, relasi form, dan antrean SLA terkait.
     */
    public function destroy(Booking $booking)
    {
        $this->authorize('delete', $booking);

        $kategori = $booking->kategori;
        $oldDate = $booking->tanggal_dijadwalkan ? Carbon::parse($booking->tanggal_dijadwalkan) : null;
        $oldPsychIds = $kategori === 'psikotes'
            ? $booking->candidates()->pluck('assigned_psikolog_id')->filter()->unique()->values()->all()
            : [];

        app(GoogleCalendarService::class)->deleteBookingEvents($booking);

        ClientForm::where('booking_id', $booking->id)->update([
            'booking_id' => null,
            'booking_status' => 'belum_dibooking',
        ]);

        $booking->delete();

        if ($kategori === 'psikotes' && $oldDate && !empty($oldPsychIds)) {
            $slaService = app(PsychotestSlaService::class);
            foreach ($oldPsychIds as $counselorId) {
                $slaService->recalculatePsychologistDay((int) $counselorId, $oldDate);
            }
        }

        return redirect()->route($kategori === 'psikotes' ? 'bookings.psychotest' : 'bookings.counseling')
            ->with('success', 'Booking berhasil dihapus.');
    }

    /**
     * Tentukan status awal konseling sesuai ketersediaan jadwal dan konselor (PRD 5.1).
     */
    private function resolveFollowUpCounselingStatus(?string $scheduledDate, ?int $counselorId): string
    {
        if (!empty($scheduledDate) && !empty($counselorId)) {
            return 'menunggu_konfirmasi';
        }
        if (!empty($scheduledDate) || !empty($counselorId)) {
            return 'waiting_list';
        }

        return 'data_masuk';
    }

    /**
     * Simpan realisasi sesi (jam masuk, jam keluar, catatan pasca-sesi).
     */
    public function storeRealisasi(Request $request, Booking $booking)
    {
        $this->authorize('update', $booking);

        $validated = $request->validate([
            'session_start'             => 'required|date_format:H:i',
            'session_end'               => 'required|date_format:H:i|after:session_start',
            'session_duration_standard' => 'nullable|integer|min:1|max:480',
            'session_notes'             => 'nullable|string|max:2000',
            'session_type'              => 'nullable|in:tatap_muka,online,whatsapp',
            'location'                  => 'nullable|string|max:255',
            'status'                    => 'nullable|string|max:50',
        ]);

        // Hitung overtime kelipatan blok 60 mnt setelah masa toleransi 30 menit (total > 90 mnt untuk standar 60 mnt, Opsi A)
        [$sh, $sm] = explode(':', $validated['session_start']);
        [$eh, $em] = explode(':', $validated['session_end']);
        $totalMinutes   = ((int)$eh * 60 + (int)$em) - ((int)$sh * 60 + (int)$sm);
        $standard       = (int)($validated['session_duration_standard'] ?? $booking->session_duration_standard ?? 60);
        $overtimeUnits  = Booking::calculateOvertimeUnits(max(0, $totalMinutes), $standard);
        $isOvertime     = $overtimeUnits > 0;
        $overtimeMinutes = $isOvertime ? max(0, $totalMinutes - $standard) : 0;

        $needsFollowUp = $request->boolean('needs_followup');
        $fallbackStatus = $isOvertime ? 'perlu_bayar_overtime' : ($needsFollowUp ? 'perlu_sesi_lanjutan' : $booking->status);

        $booking->update([
            'session_start'             => $validated['session_start'],
            'session_end'               => $validated['session_end'],
            'session_duration_standard' => $standard,
            'actual_duration_minutes'   => max(0, $totalMinutes),
            'is_overtime'               => $isOvertime,
            'overtime_minutes'          => $overtimeMinutes,
            'overtime_units'            => $overtimeUnits,
            'overtime_payment_status'   => $isOvertime
                ? ($booking->overtime_payment_status === 'tidak_ada' ? 'menunggu_nota' : $booking->overtime_payment_status)
                : 'tidak_ada',
            'session_notes'             => $validated['session_notes'] ?? null,
            'session_type'              => $validated['session_type'] ?? null,
            'location'                  => $validated['location'] ?? null,
            'status'                    => $validated['status'] ?? $fallbackStatus,
        ]);

        // Jika diminta follow-up, buat booking lanjutan dengan status resmi PRD 5.1
        if ($needsFollowUp) {
            $followUp = Booking::create([
                'client_id'               => $booking->client_id,
                'kategori'                => $booking->kategori,
                'service_tier'            => $booking->service_tier,
                'service_package_key'     => $booking->service_package_key,
                'service_package_name'    => $booking->service_package_name,
                'price'                   => $booking->price,
                'overtime_price'          => $booking->overtime_price,
                'tanggal_booking_dibuat'  => now()->toDateString(),
                'session_type'            => $booking->session_type,
                'location'                => $booking->location,
                'status'                  => $booking->kategori === 'psikotes'
                    ? 'baru'
                    : $this->resolveFollowUpCounselingStatus(null, $booking->counselor_id),
                'follow_up_of_booking_id' => $booking->id,
                'counselor_id'            => $booking->counselor_id,
                'staff_penguji_id'        => $booking->staff_penguji_id,
            ]);
            app(GoogleCalendarService::class)->syncBooking($followUp);
        }

        return redirect()->back()->with('success', 'Realisasi sesi berhasil disimpan.' .
            ($overtimeMinutes > 0 ? " Overtime: {$overtimeMinutes} menit ({$overtimeUnits}x tarif)." : ''));
    }

    /**
     * Buat booking tindak lanjut (follow-up) dari sesi konseling sebelumnya.
     */
    public function createFollowUp(Request $request, Booking $booking)
    {
        $this->authorize('update', $booking);

        $validated = $request->validate([
            'tanggal_dijadwalkan' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $scheduledDate = $validated['tanggal_dijadwalkan'] ?? null;
        $followUpStatus = $booking->kategori === 'psikotes'
            ? 'baru'
            : $this->resolveFollowUpCounselingStatus($scheduledDate, $booking->counselor_id);

        if ($booking->kategori === 'konseling' && !in_array($booking->status, ['perlu_bayar_overtime', 'batal', 'perlu_sesi_lanjutan'], true)) {
            $booking->update(['status' => 'perlu_sesi_lanjutan']);
        }

        $followUp = Booking::create([
            'client_id' => $booking->client_id,
            'kategori' => $booking->kategori,
            'service_tier' => $booking->service_tier,
            'service_package_key' => $booking->service_package_key,
            'service_package_name' => $booking->service_package_name,
            'price' => $booking->price,
            'overtime_price' => $booking->overtime_price,
            'tanggal_booking_dibuat' => now()->toDateString(),
            'tanggal_dijadwalkan' => $scheduledDate,
            'session_type' => $booking->session_type,
            'location' => $booking->location,
            'status' => $followUpStatus,
            'follow_up_of_booking_id' => $booking->id,
            'counselor_id' => $booking->counselor_id,
            'notes' => $validated['notes'] ?? null,
        ]);

        app(GoogleCalendarService::class)->syncBooking($followUp);

        return redirect()->route('bookings.show', $followUp)
            ->with('success', 'Sesi lanjutan (follow-up) berhasil dibuat.');
    }

    /**
     * Tetapkan jadwal dan paket layanan resmi dari formulir pendaftaran klien.
     */
    public function scheduleFromForm(Request $request, ClientForm $clientForm)
    {
        $this->authorize('create', Booking::class);

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

        // Cari atau inisialisasi Booking dengan signature resmi
        $booking = $clientForm->booking;
        if (!$booking) {
            if (!$clientForm->client) {
                abort(404, 'Data klien untuk formulir ini tidak ditemukan.');
            }
            $booking = Booking::createInitialForForm(
                $clientForm->client,
                $clientForm->form_type,
                $clientForm->service_preference,
                $clientForm->id
            );
        }

        $sessionTypeMapped = ($validated['session_type'] === 'offline' || $validated['session_type'] === 'tatap_muka') ? 'tatap_muka' : 'online';
        $targetStatus = $kategori === 'konseling'
            ? (!empty($validated['counselor_id']) ? 'menunggu_konfirmasi' : 'waiting_list')
            : 'baru';

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
            'status' => $targetStatus,
        ]);

        $clientForm->booking_id = $booking->id;
        $clientForm->booking_status = 'sudah_dibooking';
        $clientForm->save();

        if ($booking->client) {
            $clientUpdates = ['service_type' => $kategori];
            if ($kategori === 'konseling') {
                $clientUpdates['counseling_type'] = $packageName;
            }
            if ($booking->client->status === 'unassigned') {
                $clientUpdates['status'] = (!empty($validated['counselor_id']) || !empty($validated['staff_penguji_id']) || !empty($validated['tanggal_dijadwalkan']))
                    ? 'assigned'
                    : 'unassigned';
            }
            $booking->client->update($clientUpdates);
        }

        if ($kategori === 'psikotes') {
            $serviceItem = $booking->serviceItems()->firstOrCreate(
                ['service_tier' => $tier, 'service_package_key' => $packageKey],
                [
                    'service_package_name' => $packageName,
                    'quantity' => 1,
                    'unit_price' => $price,
                    'subtotal_price' => $price,
                ]
            );

            $isSpecial = $booking->isSpecialNonIndustri();
            $assignedPsikologId = (!$isSpecial && !empty($validated['counselor_id'])) ? (int) $validated['counselor_id'] : null;

            $booking->candidates()->firstOrCreate(
                ['client_form_id' => $clientForm->id],
                [
                    'client_id' => $clientForm->client_id,
                    'booking_service_item_id' => $serviceItem->id,
                    'candidate_name' => $clientForm->client_name ?: ($booking->client?->name ?? 'Kandidat'),
                    'target_position' => $clientForm->answers['posisi_dituju'] ?? ($clientForm->answers['tujuan_asesmen'] ?? null),
                    'attendance_status' => 'pending',
                    'assigned_psikolog_id' => $assignedPsikologId,
                ]
            );

            if ($assignedPsikologId) {
                BookingAssignee::firstOrCreate([
                    'booking_id' => $booking->id,
                    'role_type' => 'psikolog',
                    'counselor_id' => $assignedPsikologId,
                ]);
            }
            if (!$isSpecial && !empty($validated['staff_penguji_id'])) {
                BookingAssignee::firstOrCreate([
                    'booking_id' => $booking->id,
                    'role_type' => 'tester',
                    'user_id' => (int) $validated['staff_penguji_id'],
                ]);
            }

            app(PsychotestSlaService::class)->recalculateForBooking($booking);
        }

        app(GoogleCalendarService::class)->syncBooking($booking);

        return redirect()->back()->with('success', "Jadwal & Paket Layanan berhasil ditetapkan: {$packageName}.");
    }
}
