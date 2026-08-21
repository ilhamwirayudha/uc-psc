<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;

class ClientPolicy
{
    /**
     * Determine whether the user can view the client.
     */
    public function view(User $user, Client $client): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        // Staff hanya bisa melihat klien yang ditugaskan kepadanya
        return $client->assigned_staff_id === $user->id;
    }

    /**
     * Determine whether the user can update the client.
     */
    public function update(User $user, Client $client): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        // Staff hanya bisa mengedit klien yang ditugaskan kepadanya
        return $client->assigned_staff_id === $user->id;
    }
}
