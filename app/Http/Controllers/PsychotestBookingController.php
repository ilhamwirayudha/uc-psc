<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingAssignee;
use App\Models\BookingCandidate;
use App\Models\BookingParticipant;
use App\Models\BookingServiceItem;
use App\Models\Client;
use App\Models\ClientForm;
use App\Models\Company;
use App\Models\Counselor;
use App\Models\User;
use App\Services\GoogleCalendarService;
use App\Services\PsychotestSlaService;
use App\Services\PsychotestWorkflowService;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PsychotestBookingController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly PsychotestSlaService $slaService,
        private readonly PsychotestWorkflowService $workflowService,
        private readonly GoogleCalendarService $gcalService
    ) {
    }

    public function create(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Hanya Admin yang dapat membuat booking psikotes.');
        }

        $companies = Company::where('is_active', true)->orderBy('name')->get();
        $unbookedForms = ClientForm::with('client')
            ->whereIn('form_type', ['industri', 'non_industri'])
            ->where(function ($q) {
                $q->where('booking_status', 'belum_dibooking')
                    ->orWhereNull('booking_id');
            })
            ->latest()
            ->get();

        $counselors = Counselor::where('status', 'active')->orderBy('name')->get();
        $staffs = User::where('role', 'staff')->orderBy('name')->get();
        $catalog = config('service_catalog.psikotes');

        return view('bookings.psychotest-create', compact(
            'companies',
            'unbookedForms',
            'counselors',
            'staffs',
            'catalog'
        ));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Hanya Admin yang dapat membuat booking psikotes.');
        }

        $validated = $request->validate([
            'mode' => 'required|in:industri,non_industri',
            'company_id' => 'nullable|required_if:mode,industri|exists:companies,id',
            'pic_name' => 'nullable|string|max:255',
            'tanggal_dijadwalkan' => 'required|date',
            'session_start' => 'nullable|string',
            'session_end' => 'nullable|string',
            'session_type' => 'required|in:tatap_muka,online',
            'location' => 'nullable|string|max:255',
            'billing_scheme' => 'required|in:v,I,R,-',
            'requires_outgoing_letter' => 'nullable|boolean',
            'delivery_mode' => 'required|in:softcopy,hardcopy,both',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.service_tier' => 'required|in:industri,non_industri_bakat_minat,non_industri_khusus',
            'items.*.service_package_key' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'candidates' => 'required|array|min:1',
            'candidates.*.client_form_id' => 'nullable|exists:client_forms,id',
            'candidates.*.candidate_name' => 'required|string|max:255',
            'candidates.*.target_position' => 'nullable|string|max:255',
            'candidates.*.job_level' => 'nullable|string|max:100',
            'candidates.*.service_item_index' => 'nullable|integer|min:0',
            'candidates.*.is_kilat' => 'nullable|boolean',
            'candidates.*.assigned_psikolog_id' => 'nullable|exists:counselors,id',
            'candidates.*.assigned_editor_id' => 'nullable|exists:users,id',
            'tester_ids' => 'nullable|array',
            'tester_ids.*' => 'exists:users,id',
            'skorer_manual_ids' => 'nullable|array',
            'skorer_manual_ids.*' => 'exists:users,id',
            'skorer_software_ids' => 'nullable|array',
            'skorer_software_ids.*' => 'exists:users,id',
        ], [
            'items.required' => 'Minimal pilih 1 paket layanan psikotes.',
            'candidates.required' => 'Minimal pilih atau tambahkan 1 kandidat peserta.',
        ]);

        // Validasi Anti-Bentrok KILAT: 1 Psikolog maks 1 peserta KILAT di tanggal yang sama
        $kilatByPsych = [];
        foreach ($validated['candidates'] as $cand) {
            if (!empty($cand['is_kilat']) && !empty($cand['assigned_psikolog_id'])) {
                $pId = (int) $cand['assigned_psikolog_id'];
                $kilatByPsych[$pId] = ($kilatByPsych[$pId] ?? 0) + 1;
            }
        }

        foreach ($kilatByPsych as $pId => $incomingCount) {
            $counselorName = Counselor::find($pId)?->name ?? "Psikolog #{$pId}";
            if ($incomingCount > 1) {
                return back()->withInput()->withErrors([
                    'candidates' => "{$counselorName} tidak dapat memegang lebih dari 1 peserta KILAT pada tanggal yang sama.",
                ]);
            }
            $existingCount = $this->slaService->countKilatForPsychologistOnDate($pId, $validated['tanggal_dijadwalkan']);
            if ($existingCount >= 1) {
                return back()->withInput()->withErrors([
                    'candidates' => "{$counselorName} sudah memegang peserta KILAT lain pada tanggal {$validated['tanggal_dijadwalkan']}.",
                ]);
            }
        }

        $catalog = config('service_catalog.psikotes');

        $booking = DB::transaction(function () use ($validated, $catalog, $request) {
            $company = !empty($validated['company_id']) ? Company::find($validated['company_id']) : null;
            $picName = trim((string) ($validated['pic_name'] ?? $company?->pic_name ?? ''));

            // Tentukan Client representatif untuk kompatibilitas modul lain
            if ($validated['mode'] === 'industri' && $company) {
                $client = Client::firstOrCreate(
                    ['name' => $company->name, 'jenis' => 'industri'],
                    [
                        'company_name' => $company->name,
                        'pic_name' => $picName ?: null,
                        'phone' => $company->pic_phone ?: '-',
                        'email' => $company->pic_email,
                        'address' => $company->address,
                        'service_type' => 'psikotes',
                        'source' => 'whatsapp',
                        'status' => 'assigned',
                        'created_by' => auth()->id(),
                    ]
                );
                if ($picName && !$client->pic_name) {
                    $client->update(['pic_name' => $picName]);
                }
            } else {
                $firstFormId = collect($validated['candidates'])->pluck('client_form_id')->filter()->first();
                $firstForm = $firstFormId ? ClientForm::find($firstFormId) : null;
                $client = $firstForm?->client;
                if (!$client) {
                    $firstCandidateName = $validated['candidates'][0]['candidate_name'] ?? 'Klien Psikotes';
                    $client = Client::create([
                        'name' => $firstCandidateName,
                        'jenis' => 'individu',
                        'service_type' => 'psikotes',
                        'source' => 'whatsapp',
                        'status' => 'assigned',
                        'created_by' => auth()->id(),
                    ]);
                }
            }

            // Susun item paket & hitung harga dasar pricelist
            $itemsPayload = [];
            $totalPrice = 0;
            $summaryParts = [];
            $allSpecial = true;

            foreach ($validated['items'] as $idx => $item) {
                $tier = $item['service_tier'];
                $pkgKey = $item['service_package_key'];
                $qty = (int) $item['quantity'];

                if ($tier !== 'non_industri_khusus') {
                    $allSpecial = false;
                }

                $pkgInfo = $catalog[$tier]['packages'][$pkgKey] ?? null;
                $unitPrice = (float) ($pkgInfo['price'] ?? 0);
                $pkgName = $pkgInfo['label'] ?? ($tier . ' - ' . $pkgKey);
                if ($tier === 'industri' && $pkgInfo) {
                    $pkgName = "Industri {$pkgInfo['format']} {$pkgInfo['level']} ({$pkgInfo['kategori']})";
                } elseif ($tier === 'non_industri_bakat_minat' && $pkgInfo) {
                    $pkgName = "Non-Industri {$pkgInfo['format']} ({$pkgInfo['kategori']})";
                } elseif ($tier === 'non_industri_khusus' && $pkgInfo) {
                    $pkgName = "{$pkgInfo['format']} {$pkgInfo['kategori']}";
                }

                $subtotal = $unitPrice * $qty;
                $totalPrice += $subtotal;
                $summaryParts[] = "{$pkgName} ({$qty})";
                $itemsPayload[$idx] = [
                    'service_tier' => $tier,
                    'service_package_key' => $pkgKey,
                    'service_package_name' => $pkgName,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'subtotal_price' => $subtotal,
                ];
            }

            $firstItem = reset($itemsPayload);
            $billingScheme = $validated['billing_scheme'];

            $booking = Booking::create([
                'client_id' => $client->id,
                'company_id' => $company?->id,
                'pic_name' => $picName ?: null,
                'kategori' => 'psikotes',
                'service_tier' => $allSpecial ? 'non_industri_khusus' : $firstItem['service_tier'],
                'service_package_key' => $firstItem['service_package_key'],
                'service_package_name' => implode(', ', $summaryParts),
                'price' => $totalPrice,
                'billing_scheme' => $billingScheme,
                'requires_outgoing_letter' => $request->boolean('requires_outgoing_letter'),
                'delivery_mode' => $validated['delivery_mode'],
                'tanggal_booking_dibuat' => now()->toDateString(),
                'tanggal_dijadwalkan' => $validated['tanggal_dijadwalkan'],
                'session_start' => $validated['session_start'] ?? null,
                'session_end' => $validated['session_end'] ?? null,
                'session_type' => $validated['session_type'],
                'location' => ($validated['location'] ?? null) ?: ($validated['session_type'] === 'online' ? 'Online' : 'UC PSC'),
                'status' => 'baru',
                'payment_workflow_status' => $billingScheme === '-' ? 'lunas' : 'menunggu_sesi_selesai',
                'paid_at' => $billingScheme === '-' ? now() : null,
                'notes' => $validated['notes'] ?? null,
            ]);

            $createdItems = [];
            foreach ($itemsPayload as $idx => $payload) {
                $createdItems[$idx] = BookingServiceItem::create(array_merge($payload, [
                    'booking_id' => $booking->id,
                ]));
            }

            $psikologIds = collect();
            $editorIds = collect();

            foreach ($validated['candidates'] as $cand) {
                $itemIdx = (int) ($cand['service_item_index'] ?? 0);
                $serviceItem = $createdItems[$itemIdx] ?? reset($createdItems);
                $form = !empty($cand['client_form_id']) ? ClientForm::find($cand['client_form_id']) : null;

                $assignedPsikologId = $allSpecial ? null : (!empty($cand['assigned_psikolog_id']) ? (int) $cand['assigned_psikolog_id'] : null);
                $assignedEditorId = !empty($cand['assigned_editor_id']) ? (int) $cand['assigned_editor_id'] : null;

                if ($assignedPsikologId) {
                    $psikologIds->push($assignedPsikologId);
                }
                if ($assignedEditorId) {
                    $editorIds->push($assignedEditorId);
                }

                BookingCandidate::create([
                    'booking_id' => $booking->id,
                    'client_id' => $form?->client_id ?? $client->id,
                    'client_form_id' => $form?->id,
                    'booking_service_item_id' => $serviceItem?->id,
                    'candidate_name' => trim($cand['candidate_name']),
                    'target_position' => $cand['target_position'] ?? ($form?->answers['posisi_dituju'] ?? null),
                    'job_level' => $cand['job_level'] ?? null,
                    'is_kilat' => !empty($cand['is_kilat']),
                    'attendance_status' => 'pending',
                    'assigned_psikolog_id' => $assignedPsikologId,
                    'assigned_editor_id' => $assignedEditorId,
                ]);

                BookingParticipant::create([
                    'booking_id' => $booking->id,
                    'nama_peserta' => trim($cand['candidate_name']),
                ]);

                if ($form) {
                    $form->update([
                        'booking_id' => $booking->id,
                        'booking_status' => 'sudah_dibooking',
                    ]);
                }
            }

            // Simpan 5 peran penugasan ke booking_assignees
            foreach ($psikologIds->unique() as $cId) {
                BookingAssignee::create([
                    'booking_id' => $booking->id,
                    'role_type' => 'psikolog',
                    'counselor_id' => $cId,
                ]);
            }
            foreach ($editorIds->unique() as $uId) {
                BookingAssignee::create([
                    'booking_id' => $booking->id,
                    'role_type' => 'editor',
                    'user_id' => $uId,
                ]);
            }
            if (!$allSpecial) {
                foreach (collect($validated['tester_ids'] ?? [])->unique() as $uId) {
                    BookingAssignee::create([
                        'booking_id' => $booking->id,
                        'role_type' => 'tester',
                        'user_id' => (int) $uId,
                    ]);
                }
            }
            foreach (collect($validated['skorer_manual_ids'] ?? [])->unique() as $uId) {
                BookingAssignee::create([
                    'booking_id' => $booking->id,
                    'role_type' => 'skorer_manual',
                    'user_id' => (int) $uId,
                ]);
            }
            foreach (collect($validated['skorer_software_ids'] ?? [])->unique() as $uId) {
                BookingAssignee::create([
                    'booking_id' => $booking->id,
                    'role_type' => 'skorer_software',
                    'user_id' => (int) $uId,
                ]);
            }

            return $booking;
        });

        $this->slaService->recalculateForBooking($booking);
        $this->gcalService->syncBooking($booking);

        return redirect()->route('psychotest-bookings.show', $booking)
            ->with('success', 'Booking Layanan Psikotes berhasil dibuat dengan pelacakan per kandidat.');
    }

    public function show(Booking $booking)
    {
        if ($booking->kategori !== 'psikotes') {
            return redirect()->route('bookings.show', $booking);
        }

        $this->authorize('view', $booking);

        $booking->load([
            'company',
            'client',
            'serviceItems',
            'assignees.user',
            'assignees.counselor',
            'candidates.assignedPsikolog',
            'candidates.assignedEditor',
            'candidates.clientForm',
            'candidates.serviceItem',
            'googleCalendarEvents.user',
        ]);

        $counselors = Counselor::where('status', 'active')->orderBy('name')->get();
        $staffs = User::where('role', 'staff')->orderBy('name')->get();

        return view('bookings.psychotest-detail', compact('booking', 'counselors', 'staffs'));
    }

    public function updateMeta(Request $request, Booking $booking)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Hanya Admin yang dapat mengubah detail jadwal, penugasan, atau pembayaran.');
        }

        $section = $request->input('section', 'all');

        if ($section === 'finance') {
            $validated = $request->validate([
                'billing_scheme' => 'required|in:v,I,R,-',
                'payment_workflow_status' => 'required|string|max:60',
                'final_price' => 'nullable|numeric|min:0',
                'finance_invoice_number' => 'nullable|string|max:100',
                'va_number' => 'nullable|string|max:50',
                'payment_proof_notes' => 'nullable|string|max:2000',
            ]);

            if (in_array($validated['payment_workflow_status'], ['lunas', 'lunas_overtime'], true) && !$booking->paid_at) {
                $validated['paid_at'] = now();
            } elseif (!in_array($validated['payment_workflow_status'], ['lunas', 'lunas_overtime'], true)) {
                $validated['paid_at'] = null;
            }

            $booking->update($validated);
            return back()->with('success', 'Data invoice, nominal final UC Finance, dan status pembayaran berhasil diperbarui.');
        }

        if ($section === 'schedule') {
            $validated = $request->validate([
                'tanggal_dijadwalkan' => 'required|date',
                'session_start' => 'nullable|string',
                'session_end' => 'nullable|string',
                'session_type' => 'required|in:tatap_muka,online',
                'location' => 'nullable|string|max:255',
                'requires_outgoing_letter' => 'nullable|boolean',
                'delivery_mode' => 'required|in:softcopy,hardcopy,both',
                'notes' => 'nullable|string',
            ]);
            $validated['requires_outgoing_letter'] = $request->boolean('requires_outgoing_letter');

            $booking->update($validated);
            $this->slaService->recalculateForBooking($booking);
            $this->gcalService->syncBooking($booking);

            return back()->with('success', 'Jadwal & konfigurasi pengiriman psikotes diperbarui.');
        }

        if ($section === 'assignees') {
            $validated = $request->validate([
                'tester_ids' => 'nullable|array',
                'tester_ids.*' => 'exists:users,id',
                'skorer_manual_ids' => 'nullable|array',
                'skorer_manual_ids.*' => 'exists:users,id',
                'skorer_software_ids' => 'nullable|array',
                'skorer_software_ids.*' => 'exists:users,id',
                'candidate_psikolog' => 'nullable|array',
                'candidate_psikolog.*' => 'nullable|exists:counselors,id',
                'candidate_editor' => 'nullable|array',
                'candidate_editor.*' => 'nullable|exists:users,id',
                'candidate_kilat' => 'nullable|array',
            ]);

            // Validasi limit KILAT 1 per psikolog di tanggal yang sama
            if ($booking->tanggal_dijadwalkan) {
                $dateStr = $booking->tanggal_dijadwalkan->toDateString();
                $kilatCounts = [];
                foreach ($booking->candidates as $cand) {
                    $isKilat = isset($validated['candidate_kilat'][$cand->id]);
                    $pId = $validated['candidate_psikolog'][$cand->id] ?? $cand->assigned_psikolog_id;
                    if ($isKilat && $pId && !$cand->isAbsent()) {
                        $kilatCounts[(int) $pId] = ($kilatCounts[(int) $pId] ?? 0) + 1;
                    }
                }
                foreach ($kilatCounts as $pId => $cnt) {
                    $pName = Counselor::find($pId)?->name ?? "Psikolog #{$pId}";
                    if ($cnt > 1 || $this->slaService->countKilatForPsychologistOnDate($pId, $dateStr, $booking->id) >= 1) {
                        return back()->with('error', "{$pName} sudah memegang peserta KILAT pada tanggal {$dateStr}.");
                    }
                }
            }

            DB::transaction(function () use ($booking, $validated) {
                $psikologIds = collect();
                $editorIds = collect();

                foreach ($booking->candidates as $cand) {
                    $pId = $booking->isSpecialNonIndustri() ? null : ($validated['candidate_psikolog'][$cand->id] ?? null);
                    $eId = $validated['candidate_editor'][$cand->id] ?? null;
                    $isKilat = isset($validated['candidate_kilat'][$cand->id]);

                    $cand->update([
                        'assigned_psikolog_id' => $pId ?: null,
                        'assigned_editor_id' => $eId ?: null,
                        'is_kilat' => $isKilat,
                    ]);

                    if ($pId) {
                        $psikologIds->push((int) $pId);
                    }
                    if ($eId) {
                        $editorIds->push((int) $eId);
                    }
                }

                // Sinkronkan ulang booking_assignees sambil mempertahankan status batch_completed_at untuk skorer yang tetap
                $existingCompleted = $booking->assignees()
                    ->whereNotNull('batch_completed_at')
                    ->get()
                    ->keyBy(fn ($a) => $a->role_type . ':' . ($a->user_id ?? $a->counselor_id));

                $booking->assignees()->delete();

                foreach ($psikologIds->unique() as $cId) {
                    BookingAssignee::create([
                        'booking_id' => $booking->id,
                        'role_type' => 'psikolog',
                        'counselor_id' => $cId,
                    ]);
                }
                foreach ($editorIds->unique() as $uId) {
                    BookingAssignee::create([
                        'booking_id' => $booking->id,
                        'role_type' => 'editor',
                        'user_id' => $uId,
                    ]);
                }

                $batchRoleMap = [
                    'tester' => $booking->isSpecialNonIndustri() ? [] : ($validated['tester_ids'] ?? []),
                    'skorer_manual' => $validated['skorer_manual_ids'] ?? [],
                    'skorer_software' => $validated['skorer_software_ids'] ?? [],
                ];

                foreach ($batchRoleMap as $roleType => $userIds) {
                    foreach (collect($userIds)->unique() as $uId) {
                        $prev = $existingCompleted->get($roleType . ':' . (int) $uId);
                        BookingAssignee::create([
                            'booking_id' => $booking->id,
                            'role_type' => $roleType,
                            'user_id' => (int) $uId,
                            'batch_completed_at' => $prev?->batch_completed_at,
                            'completed_by_user_id' => $prev?->completed_by_user_id,
                        ]);
                    }
                }
            });

            $this->slaService->recalculateForBooking($booking);
            $this->gcalService->syncBooking($booking);

            return back()->with('success', 'Penugasan Psikolog dan Staff berhasil diperbarui dan tersinkron ke Kelola Penugasan.');
        }

        return back();
    }

    public function updateAttendance(Request $request, Booking $booking, BookingCandidate $candidate)
    {
        if ($candidate->booking_id !== $booking->id) {
            abort(404);
        }

        $this->authorize('view', $booking);

        $user = auth()->user();
        $canManage = $user->isAdmin()
            || ($user->isPsikolog() && $user->counselor_id && (int) $candidate->assigned_psikolog_id === (int) $user->counselor_id)
            || ($user->isStaff() && $booking->assignees()->where('role_type', 'tester')->where('user_id', $user->id)->exists());

        if (!$canManage) {
            abort(403, 'Anda tidak berhak mengubah status kehadiran kandidat ini.');
        }

        $validated = $request->validate([
            'attendance_status' => 'required|in:pending,hadir,absen',
        ]);

        $this->workflowService->updateAttendance($booking, $candidate, $validated['attendance_status']);

        return back()->with('success', "Status kehadiran {$candidate->candidate_name} diperbarui.");
    }

    public function toggleAssigneeBatch(Booking $booking, BookingAssignee $assignee)
    {
        if ($assignee->booking_id !== $booking->id) {
            abort(404);
        }

        $actor = auth()->user();
        if (!$this->workflowService->canUserToggleAssignee($actor, $assignee)) {
            abort(403, 'Anda hanya dapat mengonfirmasi tugas yang di-assign kepada Anda.');
        }

        $this->workflowService->toggleBatchAssigneeCompletion($booking, $assignee, $actor);

        return back()->with('success', "Status pengerjaan {$assignee->role_label} ({$assignee->assignee_name}) diperbarui.");
    }

    public function toggleCandidateStep(Request $request, Booking $booking, BookingCandidate $candidate)
    {
        if ($candidate->booking_id !== $booking->id) {
            abort(404);
        }

        $validated = $request->validate([
            'step' => 'required|in:psikotes,skoring,surat_keluar,draft_hpp,editing,cetak,approval,kirim',
            'resi_date' => 'nullable|date',
            'resi_number' => 'nullable|string|max:100',
        ]);

        [$ok, $message] = $this->workflowService->toggleCandidateStep(
            $booking,
            $candidate,
            $validated['step'],
            auth()->user(),
            [
                'resi_date' => $validated['resi_date'] ?? null,
                'resi_number' => $validated['resi_number'] ?? null,
            ]
        );

        return back()->with($ok ? 'success' : 'error', $message);
    }

    public function toggleKilatSent(Booking $booking, BookingCandidate $candidate)
    {
        if ($candidate->booking_id !== $booking->id) {
            abort(404);
        }

        [$ok, $message] = $this->workflowService->markKilatSent($booking, $candidate, auth()->user());

        return back()->with($ok ? 'success' : 'error', $message);
    }

    public function monthlyRecap(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Hanya Admin yang dapat mengakses Rekap Tagihan Akhir Bulan.');
        }

        $month = $request->input('month', now()->format('Y-m'));
        $scheme = $request->input('scheme');

        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $query = Booking::with(['company', 'client', 'candidates', 'serviceItems'])
            ->where('kategori', 'psikotes')
            ->whereIn('billing_scheme', ['I', 'R'])
            ->whereBetween('tanggal_dijadwalkan', [$start->toDateString(), $end->toDateString()])
            ->orderBy('tanggal_dijadwalkan');

        if (in_array($scheme, ['I', 'R'], true)) {
            $query->where('billing_scheme', $scheme);
        }

        $recapBookings = $query->get();

        return view('bookings.psychotest-monthly-recap', compact('recapBookings', 'month', 'scheme', 'start'));
    }
}
