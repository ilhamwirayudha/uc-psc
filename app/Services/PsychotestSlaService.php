<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingCandidate;
use App\Models\Holiday;
use Carbon\Carbon;

class PsychotestSlaService
{
    /**
     * Hitung deadline pengiriman ke klien berdasarkan hari kerja
     * (mengecualikan Sabtu, Minggu, dan tanggal di tabel holidays).
     */
    public function calculateClientDeadline(Carbon $testDate, bool $isKilat): Carbon
    {
        $requiredWorkingDays = $isKilat ? 1 : 5;
        $cursor = $testDate->copy()->startOfDay();
        $holidayDates = Holiday::whereBetween('date', [
            $cursor->toDateString(),
            $cursor->copy()->addDays(45)->toDateString(),
        ])->pluck('date')->map(fn ($d) => Carbon::parse($d)->toDateString())->all();

        $counted = 0;
        while ($counted < $requiredWorkingDays) {
            $cursor->addDay();
            if ($cursor->isWeekend()) {
                continue;
            }
            if (in_array($cursor->toDateString(), $holidayDates, true)) {
                continue;
            }
            $counted++;
        }

        return $cursor;
    }

    /**
     * Validasi kapasitas: 1 Psikolog maksimal memegang 1 peserta KILAT di tanggal yang sama.
     */
    public function countKilatForPsychologistOnDate(int $counselorId, string $scheduledDate, ?int $excludeBookingId = null): int
    {
        return BookingCandidate::where('assigned_psikolog_id', $counselorId)
            ->where('is_kilat', true)
            ->where('attendance_status', '!=', 'absen')
            ->whereHas('booking', function ($q) use ($scheduledDate, $excludeBookingId) {
                $q->where('kategori', 'psikotes')
                    ->whereDate('tanggal_dijadwalkan', $scheduledDate)
                    ->where('status', '!=', 'batal');
                if ($excludeBookingId !== null) {
                    $q->where('id', '!=', $excludeBookingId);
                }
            })
            ->count();
    }

    /**
     * Hitung ulang SLA klien dan SLA psikolog untuk seluruh kandidat pada satu booking
     * serta seluruh jadwal psikolog terkait di tanggal yang sama.
     */
    public function recalculateForBooking(Booking $booking): void
    {
        if ($booking->kategori !== 'psikotes') {
            return;
        }

        $candidates = $booking->candidates()->orderBy('id')->get();
        if ($candidates->isEmpty()) {
            return;
        }

        $scheduledDate = $booking->tanggal_dijadwalkan ? Carbon::parse($booking->tanggal_dijadwalkan) : null;

        foreach ($candidates as $candidate) {
            if ($candidate->isAbsent() || !$scheduledDate) {
                $candidate->update([
                    'psychologist_deadline_date' => null,
                    'client_deadline_date' => $scheduledDate && !$candidate->isAbsent()
                        ? $this->calculateClientDeadline($scheduledDate, $candidate->is_kilat)->toDateString()
                        : null,
                ]);
                continue;
            }

            $candidate->update([
                'client_deadline_date' => $this->calculateClientDeadline($scheduledDate, $candidate->is_kilat)->toDateString(),
            ]);
        }

        if (!$scheduledDate) {
            return;
        }

        $psychologistIds = $candidates->pluck('assigned_psikolog_id')->filter()->unique();
        foreach ($psychologistIds as $counselorId) {
            $this->recalculatePsychologistDay((int) $counselorId, $scheduledDate);
        }
    }

    /**
     * Aturan SLA Psikolog -> UC PSC:
     * 1 klien hadir = 1 hari kalender (tidak mempedulikan weekend/libur).
     * Klien KILAT diprioritaskan di slot hari ke-1 (H+1).
     */
    public function recalculatePsychologistDay(int $counselorId, Carbon $scheduledDate): void
    {
        $dayCandidates = BookingCandidate::where('assigned_psikolog_id', $counselorId)
            ->where('attendance_status', '!=', 'absen')
            ->whereHas('booking', function ($q) use ($scheduledDate) {
                $q->where('kategori', 'psikotes')
                    ->whereDate('tanggal_dijadwalkan', $scheduledDate->toDateString())
                    ->where('status', '!=', 'batal');
            })
            ->orderByDesc('is_kilat')
            ->orderBy('booking_id')
            ->orderBy('id')
            ->get();

        $offsetDays = 1;
        foreach ($dayCandidates as $candidate) {
            $deadline = $scheduledDate->copy()->addDays($offsetDays)->toDateString();
            $candidate->update(['psychologist_deadline_date' => $deadline]);
            $offsetDays++;
        }
    }
}
