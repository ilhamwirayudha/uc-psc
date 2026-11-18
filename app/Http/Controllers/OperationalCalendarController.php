<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Client;
use App\Models\Counselor;
use App\Models\User;
use Illuminate\Http\Request;

class OperationalCalendarController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // 1. Ambil Konselor Aktif untuk Filter
        $counselors = Counselor::where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'specialization', 'photo']);

        // 2. Ambil Staff & Psikolog Login Aktif untuk Filter
        $staffList = User::whereIn('role', ['admin', 'staff', 'psikolog'])
            ->orderBy('name')
            ->get(['id', 'name', 'role', 'email']);

        // 3. Sesi Konseling (Praktik Konselor)
        $counselingQuery = Booking::with(['client', 'counselor'])
            ->where('kategori', 'konseling')
            ->whereNotNull('tanggal_dijadwalkan')
            ->where('status', '!=', 'batal');

        if ($user->isPsikolog()) {
            $counselingQuery->where('counselor_id', (int) ($user->counselor_id ?? 0));
        }

        $counselingBookings = $counselingQuery->get();

        // 4. Sesi Pelaksanaan Psikotes (Multi-Assignee: Psikolog, Tester, Skorer, Editor)
        $psikotesQuery = Booking::with(['company', 'client', 'assignees.user', 'assignees.counselor', 'candidates.assignedPsikolog', 'staffPenguji'])
            ->where('kategori', 'psikotes')
            ->whereNotNull('tanggal_dijadwalkan')
            ->where('status', '!=', 'batal');

        if ($user->isPsikolog()) {
            $cId = (int) ($user->counselor_id ?? 0);
            $psikotesQuery->where(function ($q) use ($cId) {
                $q->whereHas('assignees', fn ($aq) => $aq->where('counselor_id', $cId))
                  ->orWhereHas('candidates', fn ($cq) => $cq->where('assigned_psikolog_id', $cId));
            });
        }

        $psikotesBookings = $psikotesQuery->get();

        $calendarEvents = [];

        // --- Agregasi 1: Praktik Konseling (Konselor) ---
        foreach ($counselingBookings as $booking) {
            $dateStr = $booking->tanggal_dijadwalkan->format('Y-m-d');
            $counselorName = $booking->counselor->name ?? 'Belum Ditentukan';
            $clientName = $booking->client->name ?? 'Klien';

            $calendarEvents[] = [
                'id' => 'counseling-' . $booking->id,
                'category' => 'counseling',
                'category_label' => 'Praktik Konseling',
                'bg_chip' => 'bg-[#3D1D66] hover:bg-[#4A2380] text-white',
                'dot_color' => 'bg-purple-600',
                'date' => $dateStr,
                'formatted_date' => $booking->tanggal_dijadwalkan->locale('id')->isoFormat('dddd, D MMMM Y'),
                'title' => $counselorName . ' — ' . $clientName,
                'short_title' => 'Konseling: ' . $counselorName,
                'client_name' => $clientName,
                'client_id' => $booking->client_id,
                'counselor_name' => $counselorName,
                'counselor_id' => $booking->counselor_id,
                'staff_name' => '-',
                'staff_id' => null,
                'role_label' => 'Konselor Praktik',
                'status' => $booking->status,
                'status_label' => ucfirst(str_replace('_', ' ', $booking->status)),
                'notes' => $booking->notes,
                'url' => route('bookings.show', $booking),
            ];
        }

        // --- Agregasi 2: Pelaksanaan Tes Psikotes (Multi-Role Team) ---
        foreach ($psikotesBookings as $booking) {
            $dateStr = $booking->tanggal_dijadwalkan->format('Y-m-d');
            $clientName = $booking->company?->name ?? $booking->client?->name ?? 'Klien';

            $testerNames = $booking->assignees->where('role_type', 'tester')->map(fn ($a) => $a->user?->name)->filter()->implode(', ');
            $psychNames = $booking->formatPsychologistQuotaSummary();
            $staffName = $testerNames ?: ($booking->staffPenguji?->name ?? ($psychNames !== '-' ? $psychNames : 'Tim Psikotes'));
            $firstTesterId = $booking->assignees->where('role_type', 'tester')->first()?->user_id ?? $booking->staff_penguji_id;
            $firstPsychId = $booking->candidates->whereNotNull('assigned_psikolog_id')->first()?->assigned_psikolog_id;
            $allStaffIds = $booking->assignees->pluck('user_id')
                ->merge($booking->candidates->pluck('assigned_editor_id'))
                ->push($booking->staff_penguji_id)
                ->filter()
                ->unique()
                ->values()
                ->all();
            $allCounselorIds = $booking->assignees->pluck('counselor_id')
                ->merge($booking->candidates->pluck('assigned_psikolog_id'))
                ->filter()
                ->unique()
                ->values()
                ->all();

            $calendarEvents[] = [
                'id' => 'testing-booking-' . $booking->id,
                'category' => 'testing',
                'category_label' => 'Pelaksanaan Psikotes',
                'bg_chip' => 'bg-[#E38B2C] hover:bg-[#D97715] text-white',
                'dot_color' => 'bg-orange-500',
                'date' => $dateStr,
                'formatted_date' => $booking->tanggal_dijadwalkan->locale('id')->isoFormat('dddd, D MMMM Y'),
                'title' => 'Psikotes: ' . $clientName . ' (' . $staffName . ')',
                'short_title' => 'Psikotes: ' . $clientName,
                'client_name' => $clientName,
                'client_id' => $booking->client_id,
                'counselor_name' => $psychNames,
                'counselor_id' => $firstPsychId,
                'counselor_ids' => $allCounselorIds,
                'staff_name' => $staffName,
                'staff_id' => $firstTesterId,
                'staff_ids' => $allStaffIds,
                'role_label' => 'Tim Psikotes',
                'status' => $booking->status,
                'status_label' => ucfirst(str_replace('_', ' ', $booking->status)),
                'notes' => $booking->notes,
                'url' => route('psychotest-bookings.show', $booking),
            ];
        }

        $stats = [
            'total_counselors' => $counselors->count(),
            'total_staff' => $staffList->count(),
            'total_clients' => Client::count(),
            'total_events' => count($calendarEvents),
            'counseling_count' => collect($calendarEvents)->where('category', 'counseling')->count(),
            'testing_count' => collect($calendarEvents)->where('category', 'testing')->count(),
            'correction_count' => 0,
            'reporting_count' => 0,
        ];

        return view('calendar.operational-calendar-board', compact(
            'stats',
            'calendarEvents',
            'counselors',
            'staffList'
        ));
    }
}
