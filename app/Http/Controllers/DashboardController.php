<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingCandidate;
use App\Models\ClientForm;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Dashboard Operasional UC PSC sesuai peran pengguna.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // 1. Panel Prioritas KILAT
        $kilatQuery = BookingCandidate::with(['booking.company', 'booking.client', 'assignedPsikolog', 'assignedEditor'])
            ->where('is_kilat', true)
            ->where('attendance_status', '!=', 'absen')
            ->whereNull('kilat_sent_at')
            ->whereNull('step_kirim_at')
            ->whereHas('booking', fn ($q) => $q->where('kategori', 'psikotes')->where('status', '!=', 'batal'))
            ->orderBy('client_deadline_date');

        if ($user->isPsikolog()) {
            $kilatQuery->where('assigned_psikolog_id', (int) ($user->counselor_id ?? 0));
        }
        $kilatCandidates = $kilatQuery->take(10)->get();

        // 2. Pemantauan SLA Laporan Psikolog (mendekati deadline H/H+1 atau sudah terlambat)
        $slaQuery = BookingCandidate::with(['booking.company', 'booking.client', 'assignedPsikolog'])
            ->where('attendance_status', '!=', 'absen')
            ->whereNull('step_draft_hpp_at')
            ->whereNull('kilat_sent_at')
            ->whereNotNull('psychologist_deadline_date')
            ->whereHas('booking', fn ($q) => $q->where('kategori', 'psikotes')->where('status', '!=', 'batal'))
            ->orderBy('psychologist_deadline_date');

        if ($user->isPsikolog()) {
            $slaQuery->where('assigned_psikolog_id', (int) ($user->counselor_id ?? 0));
        }
        $slaCandidates = $slaQuery->take(12)->get();

        // 3. Antrean Formulir Psikotes Belum Dibooking & Booking Pending (Biography EN) (Admin & Staff)
        $unbookedForms = collect();
        $unbookedFormsCount = 0;
        $pendingBiographyBookings = collect();
        $pendingBiographyCount = 0;
        if ($user->isAdmin() || $user->isStaff()) {
            $unbookedQuery = ClientForm::with('client')
                ->whereHas('client')
                ->whereIn('form_type', ['industri', 'non_industri'])
                ->where(fn ($q) => $q->where('booking_status', 'belum_dibooking')->orWhereNull('booking_id'))
                ->latest();
            $unbookedFormsCount = (clone $unbookedQuery)->count();
            $unbookedForms = $unbookedQuery->take(10)->get();

            $pendingQuery = Booking::with(['client', 'clientForm'])
                ->where('kategori', 'pending')
                ->where('status', '!=', 'batal')
                ->latest();
            $pendingBiographyCount = (clone $pendingQuery)->count();
            $pendingBiographyBookings = $pendingQuery->take(10)->get();
        }

        // 4. Pengingat Pembayaran H-1 & Overtime Konseling
        $counselingReminders = collect();
        if ($user->isAdmin() || $user->isStaff() || $user->isPsikolog()) {
            $reminderQuery = Booking::with(['client', 'counselor'])
                ->where('kategori', 'konseling')
                ->whereNotIn('status', ['selesai', 'batal'])
                ->where(function ($q) {
                    $q->where('is_overtime', true)
                      ->where('overtime_payment_status', '!=', 'lunas')
                      ->orWhere(function ($sub) {
                          $sub->whereNotNull('tanggal_dijadwalkan')
                              ->whereDate('tanggal_dijadwalkan', '<=', now()->addDays(2)->toDateString())
                              ->whereNotIn('payment_workflow_status', ['lunas', 'lunas_overtime']);
                      });
                })
                ->orderBy('tanggal_dijadwalkan');

            if ($user->isPsikolog()) {
                $reminderQuery->where('counselor_id', (int) ($user->counselor_id ?? 0));
            }
            $counselingReminders = $reminderQuery->take(10)->get();
        }

        // 5. Status Sinkronisasi Google Calendar
        $gcalPendingQuery = Booking::with(['client', 'company'])
            ->whereIn('gcal_sync_status', ['pending_auth', 'failed'])
            ->whereNotNull('tanggal_dijadwalkan')
            ->where('status', '!=', 'batal')
            ->latest('tanggal_dijadwalkan');

        if ($user->isPsikolog()) {
            $cId = (int) ($user->counselor_id ?? 0);
            $gcalPendingQuery->where(function ($q) use ($cId) {
                $q->where('counselor_id', $cId)
                  ->orWhereHas('assignees', fn ($aq) => $aq->where('counselor_id', $cId))
                  ->orWhereHas('candidates', fn ($cq) => $cq->where('assigned_psikolog_id', $cId));
            });
        }
        $gcalPendingBookings = $gcalPendingQuery->take(10)->get();

        // Statistik ringkas
        $stats = [
            'kilat_active' => $kilatCandidates->count(),
            'sla_overdue' => $slaCandidates->filter(fn ($c) => $c->isPsychologistReportOverdue())->count(),
            'unbooked_forms' => $unbookedFormsCount + $pendingBiographyCount,
            'pending_biography' => $pendingBiographyCount,
            'gcal_pending' => $gcalPendingBookings->count(),
        ];

        return view('dashboard.main-overview', compact(
            'stats',
            'kilatCandidates',
            'slaCandidates',
            'unbookedForms',
            'pendingBiographyBookings',
            'counselingReminders',
            'gcalPendingBookings'
        ));
    }
}
