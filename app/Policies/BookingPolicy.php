<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    /**
     * Admin & Staff melihat semua booking; Psikolog melihat booking yang ditugaskan kepadanya.
     */
    public function view(User $user, Booking $booking): bool
    {
        if ($user->isAdmin() || $user->isStaff()) {
            return true;
        }

        if ($user->isPsikolog() && $user->counselor_id) {
            $counselorId = (int) $user->counselor_id;
            return (int) $booking->counselor_id === $counselorId
                || $booking->assignees()->where('counselor_id', $counselorId)->exists()
                || $booking->candidates()->where('assigned_psikolog_id', $counselorId)->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
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
