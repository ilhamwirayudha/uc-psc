<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    /**
     * Determine whether the user can view the booking.
     */
    public function view(User $user, Booking $booking): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        // Staff hanya bisa melihat booking jika dirinya terkait
        return ($booking->client && $booking->client->assigned_staff_id === $user->id)
            || $booking->staff_penguji_id === $user->id
            || $booking->staff_koreksi_id === $user->id
            || $booking->staff_pelapor_id === $user->id;
    }

    /**
     * Determine whether the user can update the booking.
     */
    public function update(User $user, Booking $booking): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        // Staff hanya bisa mengedit booking jika dirinya terkait
        return ($booking->client && $booking->client->assigned_staff_id === $user->id)
            || $booking->staff_penguji_id === $user->id
            || $booking->staff_koreksi_id === $user->id
            || $booking->staff_pelapor_id === $user->id;
    }

    /**
     * Determine whether the user can delete the booking.
     */
    public function delete(User $user, Booking $booking): bool
    {
        return $user->role === 'admin';
    }
}
