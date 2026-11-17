<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    /**
     * Admin melihat semua booking; Staff & Psikolog melihat booking yang ditugaskan kepada mereka.
     */
    public function view(User $user, Booking $booking): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isPsikolog() && $user->counselor_id) {
            $counselorId = (int) $user->counselor_id;
            return (int) $booking->counselor_id === $counselorId
                || $booking->assignees()->where('counselor_id', $counselorId)->exists()
                || $booking->candidates()->where('assigned_psikolog_id', $counselorId)->exists();
        }

        if ($user->isStaff()) {
            return (int) $booking->staff_penguji_id === (int) $user->id
                || (int) $booking->staff_koreksi_id === (int) $user->id
                || (int) $booking->staff_pelapor_id === (int) $user->id
                || $booking->assignees()->where('user_id', $user->id)->exists()
                || $booking->candidates()->where('assigned_editor_id', $user->id)->exists();
        }

        return false;
    }

    /**
     * Hanya Admin yang mengubah struktur booking/paket/jadwal/pembayaran.
     */
    public function update(User $user, Booking $booking): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Booking $booking): bool
    {
        return $user->isAdmin();
    }
}
