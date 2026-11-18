<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingAssignee;
use App\Models\BookingCandidate;
use App\Models\Counselor;
use App\Models\User;
use App\Services\GoogleCalendarService;
use App\Services\PsychotestSlaService;
use App\Services\PsychotestWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssignmentController extends Controller
{
    public function __construct(
        private readonly PsychotestSlaService $slaService,
        private readonly GoogleCalendarService $gcalService,
        private readonly PsychotestWorkflowService $workflowService
    ) {
    }

    /**
     * Pusat Rekap & Pengelolaan Beban Tugas (Workload & Task Tracker)
     * Tersinkron otomatis dari Layanan Psikotes dan Layanan Konseling.
     */
    public function index(Request $request)
    {
        $counselors = Counselor::where('status', 'active')
            ->with('user')
            ->orderBy('name')
            ->get();

        $staffUsers = User::where('role', 'staff')
            ->orderBy('name')
            ->get();

        $activeCounselingBookings = Booking::with(['client', 'counselor'])
            ->where('kategori', 'konseling')
            ->whereNotIn('status', ['selesai', 'batal'])
            ->orderBy('tanggal_dijadwalkan')
            ->get();

        $activeCandidates = BookingCandidate::with(['booking.company', 'booking.client', 'assignedPsikolog', 'assignedEditor'])
            ->where('attendance_status', '!=', 'absen')
            ->whereHas('booking', fn ($q) => $q->where('kategori', 'psikotes')->where('status', '!=', 'batal'))
            ->orderBy('psychologist_deadline_date')
            ->get();

        $activeBatchAssignees = BookingAssignee::with(['booking.company', 'booking.client', 'booking.candidates'])
            ->whereIn('role_type', ['tester', 'skorer_manual', 'skorer_software'])
            ->whereHas('booking', fn ($q) => $q->where('kategori', 'psikotes')->whereNotIn('status', ['selesai', 'batal']))
            ->latest()
            ->get();

        // Bangun ringkasan beban per Psikolog
        $psychologistWorkloads = $counselors->map(function (Counselor $counselor) use ($activeCounselingBookings, $activeCandidates) {
            $counselingList = $activeCounselingBookings->where('counselor_id', $counselor->id)->values();
            $candidateList = $activeCandidates->where('assigned_psikolog_id', $counselor->id)
                ->filter(fn ($c) => is_null($c->step_draft_hpp_at) && !($c->is_kilat && $c->kilat_sent_at))
                ->values();

            $kilatCount = $candidateList->where('is_kilat', true)->count();
            $overdueCount = $candidateList->filter(fn ($c) => $c->isPsychologistReportOverdue())->count();

            return [
                'counselor' => $counselor,
                'counseling_tasks' => $counselingList,
                'psychotest_candidates' => $candidateList,
                'active_counseling_count' => $counselingList->count(),
                'active_candidate_count' => $candidateList->count(),
                'kilat_count' => $kilatCount,
                'overdue_count' => $overdueCount,
                'total_active' => $counselingList->count() + $candidateList->count(),
            ];
        });

        // Bangun ringkasan beban per Staff
        $staffWorkloads = $staffUsers->map(function (User $staff) use ($activeBatchAssignees, $activeCandidates) {
            $batchTasks = $activeBatchAssignees->where('user_id', $staff->id)
                ->filter(fn ($a) => is_null($a->batch_completed_at))
                ->values();

            $editorTasks = $activeCandidates->where('assigned_editor_id', $staff->id)
                ->filter(fn ($c) => is_null($c->step_editing_at) && !($c->is_kilat && $c->kilat_sent_at))
                ->values();

            $kilatCount = $editorTasks->where('is_kilat', true)->count()
                + $batchTasks->filter(fn ($b) => $b->booking && $b->booking->candidates->where('is_kilat', true)->isNotEmpty())->count();

            return [
                'staff' => $staff,
                'batch_tasks' => $batchTasks,
                'editor_candidates' => $editorTasks,
                'batch_count' => $batchTasks->count(),
                'editor_count' => $editorTasks->count(),
                'kilat_count' => $kilatCount,
                'total_active' => $batchTasks->count() + $editorTasks->count(),
            ];
        });

        $activeTab = in_array($request->get('tab'), ['psikolog', 'staff'], true) ? $request->get('tab') : 'psikolog';

        return view('assignments.staff-assignment-board', compact(
            'psychologistWorkloads',
            'staffWorkloads',
            'counselors',
            'staffUsers',
            'activeTab'
        ));
    }

    /**
     * Reassign tugas Psikolog atau Staff langsung dari Kelola Penugasan,
     * sinkron otomatis ke Booking, hak centang, SLA, dan Google Calendar.
     */
    public function reassignTask(Request $request)
    {
        $validated = $request->validate([
            'task_type' => 'required|in:counseling_counselor,candidate_psikolog,candidate_editor,batch_assignee',
            'target_id' => 'required|integer',
            'new_assignee_id' => 'required|integer',
        ]);

        $type = $validated['task_type'];
        $targetId = (int) $validated['target_id'];
        $newId = (int) $validated['new_assignee_id'];
        $affectedBooking = null;
        $extraPsychologistIds = [];

        if ($type === 'counseling_counselor') {
            $booking = Booking::findOrFail($targetId);
            $newCounselor = Counselor::findOrFail($newId);
            $booking->update(['counselor_id' => $newCounselor->id]);
            $affectedBooking = $booking;
        } elseif ($type === 'candidate_psikolog') {
            $candidate = BookingCandidate::with('booking')->findOrFail($targetId);
            $newCounselor = Counselor::findOrFail($newId);
            $booking = $candidate->booking;
            if ($candidate->assigned_psikolog_id) {
                $extraPsychologistIds[] = (int) $candidate->assigned_psikolog_id;
            }

            if ($candidate->is_kilat && $booking?->tanggal_dijadwalkan) {
                $existingKilat = $this->slaService->countKilatForPsychologistOnDate(
                    $newCounselor->id,
                    $booking->tanggal_dijadwalkan->toDateString(),
                    $booking->id
                );
                $sameBookingKilat = $booking->candidates()
                    ->where('id', '!=', $candidate->id)
                    ->where('assigned_psikolog_id', $newCounselor->id)
                    ->where('is_kilat', true)
                    ->where('attendance_status', '!=', 'absen')
                    ->count();
                if ($existingKilat + $sameBookingKilat >= 1) {
                    return back()->with('error', "{$newCounselor->name} sudah memegang peserta KILAT pada tanggal tes tersebut.");
                }
            }

            DB::transaction(function () use ($candidate, $booking, $newCounselor) {
                $oldPsychId = $candidate->assigned_psikolog_id;
                $candidate->update(['assigned_psikolog_id' => $newCounselor->id]);

                if ($booking) {
                    if ($oldPsychId && !$booking->candidates()->where('assigned_psikolog_id', $oldPsychId)->exists()) {
                        $booking->assignees()->where('role_type', 'psikolog')->where('counselor_id', $oldPsychId)->delete();
                    }
                    BookingAssignee::firstOrCreate([
                        'booking_id' => $booking->id,
                        'role_type' => 'psikolog',
                        'counselor_id' => $newCounselor->id,
                    ]);
                }
            });
            $affectedBooking = $booking;
        } elseif ($type === 'candidate_editor') {
            $candidate = BookingCandidate::with('booking')->findOrFail($targetId);
            $newStaff = User::where('role', 'staff')->findOrFail($newId);
            $booking = $candidate->booking;

            DB::transaction(function () use ($candidate, $booking, $newStaff) {
                $oldEditorId = $candidate->assigned_editor_id;
                $candidate->update(['assigned_editor_id' => $newStaff->id]);

                if ($booking) {
                    if ($oldEditorId && !$booking->candidates()->where('assigned_editor_id', $oldEditorId)->exists()) {
                        $booking->assignees()->where('role_type', 'editor')->where('user_id', $oldEditorId)->delete();
                    }
                    BookingAssignee::firstOrCreate([
                        'booking_id' => $booking->id,
                        'role_type' => 'editor',
                        'user_id' => $newStaff->id,
                    ]);
                }
            });
            $affectedBooking = $booking;
        } elseif ($type === 'batch_assignee') {
            $assignee = BookingAssignee::with('booking')->findOrFail($targetId);
            $newStaff = User::where('role', 'staff')->findOrFail($newId);
            $assignee->update([
                'user_id' => $newStaff->id,
                'batch_completed_at' => null,
                'completed_by_user_id' => null,
            ]);
            $affectedBooking = $assignee->booking;
            if ($affectedBooking && in_array($assignee->role_type, ['skorer_manual', 'skorer_software'], true)) {
                $this->workflowService->syncScorerCompletionState($affectedBooking, auth()->user());
            }
        }

        if ($affectedBooking) {
            if ($affectedBooking->kategori === 'psikotes') {
                $this->slaService->recalculateForBooking($affectedBooking, $extraPsychologistIds);
            }
            $this->gcalService->syncBooking($affectedBooking);
        }

        return back()->with('success', 'Penugasan berhasil dialihkan dan tersinkron ke jadwal layanan.');
    }
}
