<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingAssignee;
use App\Models\BookingCandidate;
use App\Models\User;
use Carbon\Carbon;

class PsychotestWorkflowService
{
    public const ORDERED_STEPS = [
        'psikotes',
        'skoring',
        'surat_keluar',
        'draft_hpp',
        'editing',
        'cetak',
        'approval',
        'kirim',
    ];

    public function __construct(private readonly PsychotestSlaService $slaService)
    {
    }

    /**
     * Cek apakah user berhak menandai penyelesaian peran batch (Tester / Skorer Manual / Skorer Software).
     */
    public function canUserToggleAssignee(User $user, BookingAssignee $assignee): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isStaff() && $assignee->user_id && (int) $assignee->user_id === (int) $user->id) {
            return true;
        }

        if ($user->isPsikolog() && $user->counselor_id && $assignee->counselor_id && (int) $assignee->counselor_id === (int) $user->counselor_id) {
            return true;
        }

        return false;
    }

    /**
     * Cek apakah user berhak mencentang step kandidat tertentu sesuai role & penugasan.
     */
    public function canUserToggleCandidateStep(User $user, Booking $booking, BookingCandidate $candidate, string $step): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // Kolom administratif & approval hanya boleh dicentang Admin
        if (in_array($step, ['surat_keluar', 'cetak', 'approval', 'kirim'], true)) {
            return false;
        }

        $assignees = $booking->relationLoaded('assignees') ? $booking->assignees : $booking->assignees()->get();

        if ($step === 'psikotes') {
            if ($booking->isSpecialNonIndustri()) {
                return $user->isStaff() && $assignees->where('user_id', $user->id)->isNotEmpty();
            }

            if ($user->isPsikolog() && $user->counselor_id && (int) $candidate->assigned_psikolog_id === (int) $user->counselor_id) {
                return true;
            }

            return $user->isStaff() && $assignees->where('role_type', 'tester')->where('user_id', $user->id)->isNotEmpty();
        }

        if ($step === 'skoring') {
            return $user->isStaff()
                && $assignees->whereIn('role_type', ['skorer_manual', 'skorer_software'])->where('user_id', $user->id)->isNotEmpty();
        }

        if ($step === 'draft_hpp') {
            if ($booking->isSpecialNonIndustri()) {
                return false; // Khusus paket Non-Industri Khusus: Admin saja
            }

            return $user->isPsikolog()
                && $user->counselor_id
                && (int) $candidate->assigned_psikolog_id === (int) $user->counselor_id;
        }

        if ($step === 'editing') {
            return $user->isStaff() && (int) $candidate->assigned_editor_id === (int) $user->id;
        }

        return false;
    }

    /**
     * Validasi aturan Sequential Gate dan Payment Gate sebelum mencentang step kandidat.
     *
     * @return array{0: bool, 1: ?string}
     */
    public function validateStepTransition(Booking $booking, BookingCandidate $candidate, string $step, array $extraData = []): array
    {
        if ($candidate->isAbsent() && $step !== 'psikotes') {
            return [false, 'Peserta berstatus tidak hadir (absen) sehingga tidak memerlukan tahapan laporan lanjutan.'];
        }

        $assignees = $booking->relationLoaded('assignees') ? $booking->assignees : $booking->assignees()->get();

        switch ($step) {
            case 'psikotes':
                return [true, null];

            case 'skoring':
                if (!$candidate->step_psikotes_at) {
                    return [false, 'Tahap Psikotes wajib diselesaikan terlebih dahulu sebelum Skoring.'];
                }

                $requiredScorers = $assignees->whereIn('role_type', ['skorer_manual', 'skorer_software']);
                if ($requiredScorers->isNotEmpty() && $requiredScorers->contains(fn ($a) => is_null($a->batch_completed_at))) {
                    return [false, 'Seluruh Skorer Manual dan Skorer Software yang ditugaskan wajib mengonfirmasi selesai terlebih dahulu.'];
                }

                return [true, null];

            case 'surat_keluar':
                if (!$booking->requires_outgoing_letter) {
                    return [false, 'Booking ini tidak memerlukan Surat Keluar.'];
                }
                if (!$candidate->step_skoring_at) {
                    return [false, 'Tahap Skoring wajib diselesaikan terlebih dahulu sebelum Surat Keluar.'];
                }
                return [true, null];

            case 'draft_hpp':
                if (!$candidate->step_skoring_at) {
                    return [false, 'Tahap Skoring wajib diselesaikan terlebih dahulu sebelum Draft HPP.'];
                }
                if ($booking->requires_outgoing_letter && !$candidate->step_surat_keluar_at) {
                    return [false, 'Tahap Surat Keluar wajib diselesaikan terlebih dahulu sebelum Draft HPP.'];
                }
                return [true, null];

            case 'editing':
                if (!$candidate->step_draft_hpp_at) {
                    return [false, 'Tahap Draft HPP wajib diselesaikan terlebih dahulu sebelum Editing.'];
                }
                return [true, null];

            case 'cetak':
                if ($booking->delivery_mode === 'softcopy') {
                    return [false, 'Pengiriman Softcopy tidak membutuhkan tahap Cetak.'];
                }
                if (!$candidate->step_editing_at) {
                    return [false, 'Tahap Editing wajib diselesaikan terlebih dahulu sebelum Cetak.'];
                }
                return [true, null];

            case 'approval':
                if (!$candidate->step_editing_at) {
                    return [false, 'Tahap Editing wajib diselesaikan terlebih dahulu sebelum Approval.'];
                }
                if (in_array($booking->delivery_mode, ['hardcopy', 'both'], true) && !$candidate->step_cetak_at) {
                    return [false, 'Tahap Cetak wajib diselesaikan terlebih dahulu sebelum Approval pada pengiriman Hardcopy.'];
                }
                return [true, null];

            case 'kirim':
                if (!$candidate->step_approval_at) {
                    return [false, 'Tahap Approval Admin wajib diselesaikan terlebih dahulu sebelum Kirim.'];
                }
                if (!$booking->allowsDeliveryBeforePayment() && !$booking->isPaid()) {
                    return [false, 'Skema penagihan Reguler (v) mewajibkan pembayaran lunas sebelum laporan dikirim.'];
                }
                if (in_array($booking->delivery_mode, ['hardcopy', 'both'], true)) {
                    $resiDate = $extraData['resi_date'] ?? $candidate->resi_date;
                    $resiNumber = trim((string) ($extraData['resi_number'] ?? $candidate->resi_number ?? ''));
                    if (empty($resiDate) || $resiNumber === '') {
                        return [false, 'Pengiriman Hardcopy mewajibkan pengisian Tanggal Resi dan No. Resi sebelum menandai Kirim.'];
                    }
                }
                return [true, null];
        }

        return [false, 'Tahapan workflow tidak dikenal.'];
    }

    /**
     * Toggle konfirmasi kolektif untuk Skorer / Tester pada batch booking.
     */
    public function toggleBatchAssigneeCompletion(Booking $booking, BookingAssignee $assignee, User $actor): void
    {
        $isCompleted = !is_null($assignee->batch_completed_at);
        if ($isCompleted) {
            $assignee->update([
                'batch_completed_at' => null,
                'completed_by_user_id' => null,
            ]);
        } else {
            $assignee->update([
                'batch_completed_at' => now(),
                'completed_by_user_id' => $actor->id,
            ]);
        }

        // Jika Skorer Manual & Skorer Software seluruhnya sudah selesai, otomatis tandai Skoring untuk kandidat yang sudah Psikotes
        if (in_array($assignee->role_type, ['skorer_manual', 'skorer_software'], true)) {
            $scorers = $booking->assignees()->whereIn('role_type', ['skorer_manual', 'skorer_software'])->get();
            $allDone = $scorers->isNotEmpty() && $scorers->every(fn ($s) => !is_null($s->batch_completed_at));

            if ($allDone) {
                $booking->candidates()
                    ->where('attendance_status', '!=', 'absen')
                    ->whereNotNull('step_psikotes_at')
                    ->whereNull('step_skoring_at')
                    ->update([
                        'step_skoring_at' => now(),
                        'step_skoring_by' => $actor->id,
                    ]);
            }
        }
    }

    /**
     * Tandai step kandidat selesai atau batalkan centang (jika diizinkan).
     */
    public function toggleCandidateStep(
        Booking $booking,
        BookingCandidate $candidate,
        string $step,
        User $actor,
        array $extraData = []
    ): array {
        if (!$this->canUserToggleCandidateStep($actor, $booking, $candidate, $step)) {
            return [false, 'Anda tidak memiliki hak akses untuk mencentang tahapan ini.'];
        }

        $atField = 'step_' . $step . '_at';
        $byField = 'step_' . $step . '_by';

        // Uncheck hanya diizinkan oleh Admin atau petugas terkait jika belum dilanjutkan tahap berikutnya
        if (!is_null($candidate->{$atField})) {
            $candidate->update([
                $atField => null,
                $byField => null,
            ]);
            $booking->syncOverallCompletionStatus();
            return [true, 'Status tahapan ' . strtoupper(str_replace('_', ' ', $step)) . ' dibatalkan.'];
        }

        [$allowed, $reason] = $this->validateStepTransition($booking, $candidate, $step, $extraData);
        if (!$allowed) {
            return [false, $reason];
        }

        $updates = [
            $atField => now(),
            $byField => $actor->id,
        ];

        if ($step === 'psikotes' && $candidate->attendance_status === 'pending') {
            $updates['attendance_status'] = 'hadir';
        }

        if ($step === 'kirim' && in_array($booking->delivery_mode, ['hardcopy', 'both'], true)) {
            $updates['resi_date'] = $extraData['resi_date'] ?? $candidate->resi_date;
            $updates['resi_number'] = trim((string) ($extraData['resi_number'] ?? $candidate->resi_number));
        }

        $candidate->update($updates);
        $booking->syncOverallCompletionStatus();

        return [true, 'Tahapan ' . strtoupper(str_replace('_', ' ', $step)) . ' berhasil dikonfirmasi selesai.'];
    }

    /**
     * Jalur cepat Opsi A: Tandai kandidat KILAT sudah dikirim tanpa melewati checklist panjang.
     */
    public function markKilatSent(Booking $booking, BookingCandidate $candidate, User $actor): array
    {
        if (!$candidate->is_kilat) {
            return [false, 'Hanya kandidat berstatus KILAT yang dapat menggunakan tombol kirim cepat.'];
        }

        if ($candidate->isAbsent()) {
            return [false, 'Kandidat berstatus tidak hadir (absen).'];
        }

        $isAssigned = $actor->isAdmin()
            || ($actor->isPsikolog() && $actor->counselor_id && (int) $candidate->assigned_psikolog_id === (int) $actor->counselor_id)
            || ($actor->isStaff() && (
                (int) $candidate->assigned_editor_id === (int) $actor->id
                || $booking->assignees()->where('user_id', $actor->id)->exists()
            ));

        if (!$isAssigned) {
            return [false, 'Anda tidak ditugaskan pada kandidat KILAT ini.'];
        }

        if ($candidate->kilat_sent_at) {
            $candidate->update(['kilat_sent_at' => null, 'kilat_sent_by' => null]);
            $booking->syncOverallCompletionStatus();
            return [true, 'Status pengiriman KILAT dibatalkan.'];
        }

        $candidate->update([
            'attendance_status' => $candidate->attendance_status === 'pending' ? 'hadir' : $candidate->attendance_status,
            'kilat_sent_at' => Carbon::now(),
            'kilat_sent_by' => $actor->id,
        ]);

        $booking->syncOverallCompletionStatus();

        return [true, "Laporan KILAT untuk {$candidate->candidate_name} berhasil ditandai terkirim."];
    }

    /**
     * Perbarui status kehadiran kandidat (absen menggugurkan kuota deadline psikolog tetapi tetap ditagih penuh).
     */
    public function updateAttendance(Booking $booking, BookingCandidate $candidate, string $status): void
    {
        $candidate->update(['attendance_status' => $status]);
        $this->slaService->recalculateForBooking($booking);
        $booking->syncOverallCompletionStatus();
    }
}
