<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Client;
use App\Models\TestResult;
use App\Models\TestResultDocument;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TestResultController extends Controller
{
    use AuthorizesRequests;

    /**
     * Tampilkan daftar monitoring kasus asesmen / hasil psikotes.
     */
    public function index(Request $request)
    {
        $query = TestResult::with([
            'client',
            'administrator',
            'deliveredBy',
            'booking.staffPenguji',
            'booking.staffKoreksi',
            'booking.staffPelapor',
        ])->latest();

        // Scoping akses untuk staff
        if (auth()->user()->role === 'staff') {
            $staffId = auth()->id();
            $query->where(function ($q) use ($staffId) {
                $q->whereHas('client', fn($c) => $c->where('assigned_staff_id', $staffId))
                  ->orWhereHas('booking', function ($b) use ($staffId) {
                      $b->where('staff_penguji_id', $staffId)
                        ->orWhere('staff_koreksi_id', $staffId)
                        ->orWhere('staff_pelapor_id', $staffId);
                  })
                  ->orWhere('administered_by', $staffId);
            });
        }

        // Pencarian (klien atau nama tes)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('test_name', 'like', "%{$search}%")
                  ->orWhereHas('client', fn($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        // Filter Status Lifecycle
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter Metode (Online / Offline)
        if ($request->filled('method')) {
            $query->where('method', $request->method);
        }

        // Filter Staff (Penguji, Koreksi, atau Pelapor)
        if ($request->filled('staff_id')) {
            $staffId = $request->staff_id;
            $query->whereHas('booking', function ($b) use ($staffId) {
                $b->where('staff_penguji_id', $staffId)
                  ->orWhere('staff_koreksi_id', $staffId)
                  ->orWhere('staff_pelapor_id', $staffId);
            });
        }

        // Filter Overdue (Terlambat)
        if ($request->filled('overdue') && $request->overdue == '1') {
            $query->whereNotNull('result_due_date')
                  ->where('result_due_date', '<', Carbon::today()->toDateString())
                  ->whereNotIn('status', [TestResult::STATUS_RESULT_SENT, TestResult::STATUS_COMPLETED]);
        }

        // Filter Rentang Tanggal Tes
        if ($request->filled('date_from')) {
            $query->whereDate('tested_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('tested_at', '<=', $request->date_to);
        }

        // Hitung statistik operasional untuk header monitoring
        $baseStatQuery = clone $query;
        $stats = [
            'total' => (clone $baseStatQuery)->count(),
            'scheduled' => (clone $baseStatQuery)->where('status', TestResult::STATUS_SCHEDULED)->count(),
            'in_review' => (clone $baseStatQuery)->whereIn('status', [TestResult::STATUS_WAITING_ASSESSMENT, TestResult::STATUS_ASSIGNED, TestResult::STATUS_IN_REVIEW])->count(),
            'revision' => (clone $baseStatQuery)->where('status', TestResult::STATUS_REVISION)->count(),
            'ready' => (clone $baseStatQuery)->where('status', TestResult::STATUS_RESULT_READY)->count(),
            'overdue' => (clone $baseStatQuery)->whereNotNull('result_due_date')
                ->where('result_due_date', '<', Carbon::today()->toDateString())
                ->whereNotIn('status', [TestResult::STATUS_RESULT_SENT, TestResult::STATUS_COMPLETED])
                ->count(),
        ];

        $testResults = $query->paginate(15)->withQueryString();

        $staffList = User::whereIn('role', ['staff', 'admin'])->orderBy('name')->get();

        return view('test-results.index', compact('testResults', 'stats', 'staffList'));
    }

    public function create()
    {
        return redirect()->route('test-results.index')->with('info', 'Silakan gunakan tombol pop-up Upload Hasil Psikotes.');
    }

    /**
     * Tampilkan detail kasus asesmen / hasil psikotes.
     */
    public function show(TestResult $testResult)
    {
        $this->authorize('view', $testResult);

        $testResult->load([
            'client',
            'administrator',
            'deliveredBy',
            'booking.staffPenguji',
            'booking.staffKoreksi',
            'booking.staffPelapor',
            'documents.uploader',
            'activities.user',
        ]);

        $staffList = User::whereIn('role', ['staff', 'admin'])->orderBy('name')->get();

        return view('test-results.show', compact('testResult', 'staffList'));
    }

    /**
     * Simpan kasus / hasil psikotes baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'booking_id' => 'nullable|exists:bookings,id',
            'test_name' => 'required|string|max:255',
            'tested_at' => 'nullable|date',
            'method' => 'nullable|in:offline,online',
            'status' => 'nullable|in:scheduled,test_completed,waiting_assessment,assigned,in_review,revision,review_completed,result_ready,result_sent,completed',
            'result_summary' => 'nullable|string',
            'file' => 'nullable|file|max:10240',
            'result_due_date' => 'nullable|date',
        ]);

        $testedAt = $validated['tested_at'] ? Carbon::parse($validated['tested_at']) : Carbon::today();
        $method = $validated['method'] ?? TestResult::METHOD_OFFLINE;

        // Validasi keselarasan booking jika ada
        if (!empty($validated['booking_id'])) {
            $booking = Booking::find($validated['booking_id']);
            if ($booking && $booking->kategori !== 'psikotes') {
                return redirect()->back()->withErrors(['booking_id' => 'Booking terpilih bukan kategori psikotes.'])->withInput();
            }
        }

        // Tentukan deadline SLA jika belum diisi (default 7 hari kerja)
        if (empty($validated['result_due_date'])) {
            $validated['result_due_date'] = TestResult::calculateWorkDueDate($testedAt, 7)->toDateString();
        }

        // Tentukan status awal jika tidak disediakan secara eksplisit
        if (empty($validated['status'])) {
            $validated['status'] = $request->hasFile('file') ? TestResult::STATUS_WAITING_ASSESSMENT : TestResult::STATUS_SCHEDULED;
        }

        $validated['method'] = $method;
        $validated['administered_by'] = auth()->id();

        DB::beginTransaction();
        try {
            $filePath = null;
            $originalName = null;
            $fileSize = null;

            // Simpan file ke private storage (disk local)
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $originalName = $file->getClientOriginalName();
                $fileSize = $file->getSize();
                $filePath = $file->store('test-results', 'local');
                $validated['file_path'] = $filePath;
            }

            unset($validated['file']);
            $testResult = TestResult::create($validated);

            // Jika ada file diunggah, catat sebagai entri dokumen pertama
            if ($filePath) {
                TestResultDocument::create([
                    'test_result_id' => $testResult->id,
                    'document_type' => TestResultDocument::TYPE_FINAL_RESULT,
                    'original_name' => $originalName ?? 'Dokumen Hasil',
                    'file_path' => $filePath,
                    'file_size' => $fileSize,
                    'uploaded_by' => auth()->id(),
                ]);
            }

            // Catat audit trail
            $testResult->logActivity(
                'created',
                'Kasus asesmen psikotes didaftarkan ke sistem dengan status: ' . $testResult->status_label,
                ['status' => $testResult->status, 'method' => $method]
            );

            DB::commit();

            return redirect()->route('test-results.show', $testResult)->with('success', 'Kasus asesmen psikotes berhasil dibuat.');
        } catch (\Throwable $e) {
            DB::rollBack();

            if (isset($filePath) && Storage::disk('local')->exists($filePath)) {
                Storage::disk('local')->delete($filePath);
            }

            return redirect()->back()->withErrors(['error' => 'Gagal menyimpan hasil psikotes: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Update transisi status lifecycle asesmen.
     */
    public function updateStatus(Request $request, TestResult $testResult)
    {
        $this->authorize('update', $testResult);

        $validated = $request->validate([
            'status' => 'required|in:scheduled,test_completed,waiting_assessment,assigned,in_review,revision,review_completed,result_ready,result_sent,completed',
            'revision_notes' => 'nullable|string',
        ]);

        $newStatus = $validated['status'];

        if (!$testResult->canTransitionTo($newStatus)) {
            return redirect()->back()->with('error', "Transisi status dari '{$testResult->status_label}' ke '" . (TestResult::STATUS_LABELS[$newStatus] ?? $newStatus) . "' tidak diizinkan.");
        }

        $oldStatus = $testResult->status;
        $testResult->status = $newStatus;

        // Update timestamp kontekstual
        if ($newStatus === TestResult::STATUS_ASSIGNED && !$testResult->assigned_at) {
            $testResult->assigned_at = now();
        } elseif ($newStatus === TestResult::STATUS_IN_REVIEW && !$testResult->reviewed_at) {
            $testResult->reviewed_at = now();
        } elseif ($newStatus === TestResult::STATUS_COMPLETED && !$testResult->completed_at) {
            $testResult->completed_at = now();
        }

        if ($newStatus === TestResult::STATUS_REVISION) {
            $testResult->revision_notes = $validated['revision_notes'] ?? $testResult->revision_notes;
        }

        $testResult->save();

        $actionDesc = "Status diperbarui dari '{$testResult->status_label}' menjadi '" . (TestResult::STATUS_LABELS[$newStatus] ?? $newStatus) . "'";
        if ($newStatus === TestResult::STATUS_REVISION && !empty($validated['revision_notes'])) {
            $actionDesc .= ". Catatan: " . $validated['revision_notes'];
        }

        $testResult->logActivity('status_updated', $actionDesc, [
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'revision_notes' => $testResult->revision_notes,
        ]);

        return redirect()->back()->with('success', 'Status asesmen psikotes berhasil diperbarui.');
    }

    /**
     * Perbarui penugasan tim staff (Penguji, Koreksi, Pelapor).
     */
    public function assignStaff(Request $request, TestResult $testResult)
    {
        $this->authorize('assignStaff', $testResult);

        $validated = $request->validate([
            'staff_penguji_id' => 'nullable|exists:users,id',
            'staff_koreksi_id' => 'nullable|exists:users,id',
            'staff_pelapor_id' => 'nullable|exists:users,id',
        ]);

        if ($testResult->booking) {
            $testResult->booking->update($validated);
        }

        // Jika kasus berada dalam status waiting_assessment dan staff telah ditetapkan, otomatis ubah ke assigned
        if ($testResult->status === TestResult::STATUS_WAITING_ASSESSMENT && (!empty($validated['staff_koreksi_id']) || !empty($validated['staff_pelapor_id']))) {
            $testResult->update([
                'status' => TestResult::STATUS_ASSIGNED,
                'assigned_at' => now(),
            ]);
        }

        $pengujiName = !empty($validated['staff_penguji_id']) ? User::find($validated['staff_penguji_id'])?->name : '-';
        $koreksiName = !empty($validated['staff_koreksi_id']) ? User::find($validated['staff_koreksi_id'])?->name : '-';
        $pelaporName = !empty($validated['staff_pelapor_id']) ? User::find($validated['staff_pelapor_id'])?->name : '-';

        $testResult->logActivity(
            'staff_assigned',
            "Penugasan tim diperbarui: Penguji ({$pengujiName}), Koreksi ({$koreksiName}), Pelapor ({$pelaporName})",
            $validated
        );

        return redirect()->back()->with('success', 'Penugasan tim psikotes berhasil disimpan.');
    }

    /**
     * Upload dokumen pendukung / lembar jawaban / skoring / laporan.
     */
    public function uploadDocument(Request $request, TestResult $testResult)
    {
        $this->authorize('uploadDocument', $testResult);

        $validated = $request->validate([
            'document_type' => 'required|in:test_form,answer_sheet,scoring,psychological_report,supporting_document,final_result',
            'file' => 'required|file|max:10240',
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $fileSize = $file->getSize();
        $filePath = $file->store('test-results', 'local');

        $doc = TestResultDocument::create([
            'test_result_id' => $testResult->id,
            'document_type' => $validated['document_type'],
            'original_name' => $originalName,
            'file_path' => $filePath,
            'file_size' => $fileSize,
            'uploaded_by' => auth()->id(),
        ]);

        // Jika file_path utama masih kosong dan ini adalah final_result atau psychological_report, isi sebagai fallback
        if (!$testResult->file_path && in_array($validated['document_type'], [TestResultDocument::TYPE_FINAL_RESULT, TestResultDocument::TYPE_PSYCHOLOGICAL_REPORT])) {
            $testResult->update(['file_path' => $filePath]);
        }

        $testResult->logActivity(
            'document_uploaded',
            "Dokumen '{$originalName}' berhasil diunggah sebagai {$doc->document_type_label}",
            ['document_id' => $doc->id, 'type' => $doc->document_type]
        );

        return redirect()->back()->with('success', 'Dokumen berhasil diunggah.');
    }

    /**
     * Controlled streaming download untuk file utama test result.
     */
    public function download(TestResult $testResult)
    {
        $this->authorize('downloadDocument', $testResult);

        if (!$testResult->file_path) {
            abort(404, 'Berkas dokumen belum tersedia.');
        }

        $path = $testResult->file_path;

        // Cek disk private 'local', lalu fallback ke disk 'public' untuk file arsip lama
        if (Storage::disk('local')->exists($path)) {
            return Storage::disk('local')->download($path, basename($path));
        }

        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->download($path, basename($path));
        }

        abort(404, 'Berkas dokumen tidak ditemukan di penyimpanan server.');
    }

    /**
     * Controlled streaming download untuk dokumen spesifik.
     */
    public function downloadDocument(TestResult $testResult, TestResultDocument $document)
    {
        if ($document->test_result_id !== $testResult->id) {
            abort(404);
        }

        $this->authorize('downloadDocument', $testResult);

        $path = $document->file_path;

        if (Storage::disk('local')->exists($path)) {
            return Storage::disk('local')->download($path, $document->original_name);
        }

        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->download($path, $document->original_name);
        }

        abort(404, 'Berkas dokumen tidak ditemukan di penyimpanan server.');
    }

    /**
     * Konfirmasi penyerahan hasil kepada klien (Delivery).
     */
    public function deliver(Request $request, TestResult $testResult)
    {
        $this->authorize('deliver', $testResult);

        $validated = $request->validate([
            'delivery_method' => 'required|in:whatsapp,email,offline',
            'notes' => 'nullable|string',
        ]);

        $testResult->update([
            'delivery_method' => $validated['delivery_method'],
            'delivered_at' => now(),
            'delivered_by' => auth()->id(),
            'status' => TestResult::STATUS_RESULT_SENT,
        ]);

        $deliveryLabel = TestResult::DELIVERY_LABELS[$validated['delivery_method']] ?? $validated['delivery_method'];

        $testResult->logActivity(
            'delivered',
            "Hasil psikotes resmi diserahkan kepada klien via {$deliveryLabel} oleh " . auth()->user()->name,
            ['delivery_method' => $validated['delivery_method'], 'notes' => $validated['notes'] ?? null]
        );

        return redirect()->back()->with('success', 'Hasil psikotes berhasil ditandai telah diserahkan kepada klien.');
    }

    /**
     * Hapus kasus asesmen dan semua dokumen fisiknya.
     */
    public function destroy(TestResult $testResult)
    {
        $this->authorize('delete', $testResult);

        // Hapus file utama jika ada
        if ($testResult->file_path) {
            if (Storage::disk('local')->exists($testResult->file_path)) {
                Storage::disk('local')->delete($testResult->file_path);
            }
            if (Storage::disk('public')->exists($testResult->file_path)) {
                Storage::disk('public')->delete($testResult->file_path);
            }
        }

        // Hapus file-file dokumen terkait
        foreach ($testResult->documents as $doc) {
            if ($doc->file_path) {
                if (Storage::disk('local')->exists($doc->file_path)) {
                    Storage::disk('local')->delete($doc->file_path);
                }
                if (Storage::disk('public')->exists($doc->file_path)) {
                    Storage::disk('public')->delete($doc->file_path);
                }
            }
        }

        $testResult->delete();

        return redirect()->route('test-results.index')->with('success', 'Kasus asesmen psikotes dan seluruh dokumen terkait berhasil dihapus.');
    }
}
