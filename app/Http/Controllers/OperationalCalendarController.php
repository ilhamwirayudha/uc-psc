<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Counselor;
use App\Models\Booking;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OperationalCalendarController extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::today();

        // 1. Ambil Konselor Aktif untuk Filter
        $counselors = Counselor::where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'specialization', 'photo']);

        // 2. Ambil Staff Aktif untuk Filter
        $staffList = User::whereIn('role', ['admin', 'staff'])
            ->orderBy('name')
            ->get(['id', 'name', 'role', 'email']);

        // 3. Sesi Konseling (Praktik Konselor)
        $counselingBookings = Booking::with(['client', 'counselor'])
            ->where('kategori', 'konseling')
            ->whereNotNull('tanggal_dijadwalkan')
            ->get();

        // 4. Sesi Pelaksanaan Psikotes (Tugas Staff Penguji)
        $psikotesBookings = Booking::with(['client', 'staffPenguji', 'staffKoreksi', 'staffPelapor'])
            ->where('kategori', 'psikotes')
            ->whereNotNull('tanggal_dijadwalkan')
            ->get();

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

        // --- Agregasi 2: Pelaksanaan Tes Psikotes (Staff Penguji) ---
        foreach ($psikotesBookings as $booking) {
            $dateStr = $booking->tanggal_dijadwalkan->format('Y-m-d');
            $staffName = $booking->staffPenguji->name ?? 'Belum Diassign';
            $clientName = $booking->client->name ?? 'Klien';

            $calendarEvents[] = [
                'id' => 'testing-booking-' . $booking->id,
                'category' => 'testing',
                'category_label' => 'Pelaksanaan Tes',
                'bg_chip' => 'bg-[#E38B2C] hover:bg-[#D97715] text-white',
                'dot_color' => 'bg-orange-500',
                'date' => $dateStr,
                'formatted_date' => $booking->tanggal_dijadwalkan->locale('id')->isoFormat('dddd, D MMMM Y'),
                'title' => 'Tes: ' . $clientName . ' (' . $staffName . ')',
                'short_title' => 'Tes Psikotes: ' . $clientName,
                'client_name' => $clientName,
                'client_id' => $booking->client_id,
                'counselor_name' => '-',
                'counselor_id' => null,
                'staff_name' => $staffName,
                'staff_id' => $booking->staff_penguji_id,
                'role_label' => 'Staff Penguji',
                'status' => $booking->status,
                'status_label' => ucfirst(str_replace('_', ' ', $booking->status)),
                'notes' => $booking->notes,
                'url' => route('bookings.show', $booking),
            ];
        }

        // Quick Stats Ringkas
        $stats = [
            'total_counselors' => $counselors->count(),
            'total_staff' => $staffList->count(),
            'total_clients' => Client::count(),
            'total_events' => count($calendarEvents),
            'counseling_count' => collect($calendarEvents)->where('category', 'counseling')->count(),
            'testing_count' => collect($calendarEvents)->where('category', 'testing')->count(),
            'correction_count' => collect($calendarEvents)->where('category', 'correction')->count(),
            'reporting_count' => collect($calendarEvents)->where('category', 'reporting')->count(),
        ];

        return view('calendar.operational-calendar-board', compact(
            'stats',
            'calendarEvents',
            'counselors',
            'staffList'
        ));
    }
}
