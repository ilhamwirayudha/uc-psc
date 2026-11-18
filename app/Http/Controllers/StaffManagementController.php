<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingAssignee;
use App\Models\BookingCandidate;
use App\Models\Counselor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class StaffManagementController extends Controller
{
    public function index(Request $request)
    {
        $roleFilter = $request->get('role');
        $query = User::whereIn('role', ['staff', 'psikolog'])
            ->with('counselor')
            ->latest();

        if (in_array($roleFilter, ['staff', 'psikolog'], true)) {
            $query->where('role', $roleFilter);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $staffs = $query->paginate(15)->withQueryString();
        $counselors = Counselor::where('status', 'active')->orderBy('name')->get();

        // Hitung tugas aktif per user untuk ditampilkan di tabel
        $taskCounts = [];
        foreach ($staffs as $u) {
            if ($u->isPsikolog()) {
                $taskCounts[$u->id] = $u->counselor?->countActiveTasks() ?? 0;
            } else {
                $taskCounts[$u->id] = $this->countActiveStaffTasks($u);
            }
        }

        return view('staff-management.staff-directory-table', compact('staffs', 'counselors', 'roleFilter', 'taskCounts'));
    }

    public function create()
    {
        $linkedCounselorIds = User::whereNotNull('counselor_id')->pluck('counselor_id')->all();
        $availableCounselors = Counselor::where('status', 'active')
            ->whereNotIn('id', $linkedCounselorIds)
            ->orderBy('name')
            ->get();

        return view('staff-management.staff-create-form', compact('availableCounselors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'role' => 'nullable|in:staff,psikolog',
            'counselor_id' => 'nullable|exists:counselors,id|unique:users,counselor_id',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $role = $validated['role'] ?? 'staff';
        $counselorId = null;
        if ($role === 'psikolog') {
            $counselorId = $validated['counselor_id'] ?? null;
            if (!$counselorId) {
                // Otomatis buat entri master Counselor bila belum dipilih
                $counselor = Counselor::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'status' => 'active',
                ]);
                $counselorId = $counselor->id;
            }
        }

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $role,
            'counselor_id' => $counselorId,
        ]);

        return redirect()->route('staff-management.index')->with('success', 'Akun ' . ($role === 'psikolog' ? 'Psikolog' : 'Staff') . ' berhasil ditambahkan.');
    }

    public function show(User $staff)
    {
        if ($staff->isAdmin()) {
            return redirect()->route('staff-management.index')->with('error', 'Halaman detail hanya untuk akun Staff atau Psikolog.');
        }

        $staff->load('counselor');

        if ($staff->isPsikolog() && $staff->counselor_id) {
            $counselingTasks = Booking::with(['client'])
                ->where('kategori', 'konseling')
                ->where('counselor_id', $staff->counselor_id)
                ->latest('tanggal_dijadwalkan')
                ->get();

            $psychotestCandidates = BookingCandidate::with(['booking.company', 'booking.client'])
                ->where('assigned_psikolog_id', $staff->counselor_id)
                ->latest()
                ->get();

            return view('staff-management.staff-workload-detail', compact('staff', 'counselingTasks', 'psychotestCandidates'));
        }

        $batchAssignments = BookingAssignee::with(['booking.company', 'booking.client', 'booking.candidates'])
            ->where('user_id', $staff->id)
            ->latest()
            ->get();

        $editorCandidates = BookingCandidate::with(['booking.company', 'booking.client'])
            ->where('assigned_editor_id', $staff->id)
            ->latest()
            ->get();

        return view('staff-management.staff-workload-detail', compact('staff', 'batchAssignments', 'editorCandidates'));
    }

    public function destroy(User $staff)
    {
        if ($staff->isAdmin()) {
            return redirect()->route('staff-management.index')->with('error', 'Tidak bisa menghapus akun Admin.');
        }

        if ($staff->isPsikolog() && $staff->counselor_id) {
            $activeTasks = $staff->counselor?->countActiveTasks() ?? 0;
            if ($activeTasks > 0) {
                return redirect()->route('staff-management.index')
                    ->with('error', "Akun Psikolog {$staff->name} masih memiliki {$activeTasks} tugas aktif. Alihkan tugas di Kelola Penugasan terlebih dahulu sebelum menghapus akun.");
            }

            Counselor::where('id', $staff->counselor_id)->update(['status' => 'inactive']);
        } elseif ($staff->isStaff()) {
            $activeStaffTasks = $this->countActiveStaffTasks($staff);
            if ($activeStaffTasks > 0) {
                return redirect()->route('staff-management.index')
                    ->with('error', "Akun Staff {$staff->name} masih memiliki {$activeStaffTasks} tugas aktif pada batch psikotes. Alihkan tugas di Kelola Penugasan terlebih dahulu sebelum menghapus akun.");
            }
        }

        BookingAssignee::where('user_id', $staff->id)->delete();
        BookingCandidate::where('assigned_editor_id', $staff->id)->update(['assigned_editor_id' => null]);

        $staff->delete();

        return redirect()->route('staff-management.index')->with('success', 'Akun berhasil dihapus dan status penugasan aktif telah disesuaikan.');
    }

    private function countActiveStaffTasks(User $staff): int
    {
        $batchTaskCount = BookingAssignee::where('user_id', $staff->id)
            ->whereNull('batch_completed_at')
            ->whereHas('booking', fn ($q) => $q->where('kategori', 'psikotes')->whereNotIn('status', ['selesai', 'batal']))
            ->count();

        $editorCandidateCount = BookingCandidate::where('assigned_editor_id', $staff->id)
            ->where('attendance_status', '!=', 'absen')
            ->whereNull('step_editing_at')
            ->where(fn ($q) => $q->where('is_kilat', false)->orWhereNull('kilat_sent_at'))
            ->whereHas('booking', fn ($q) => $q->where('kategori', 'psikotes')->whereNotIn('status', ['selesai', 'batal']))
            ->count();

        return $batchTaskCount + $editorCandidateCount;
    }
}
