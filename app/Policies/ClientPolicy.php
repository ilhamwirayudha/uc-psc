<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;

class ClientPolicy
{
    /**
     * Admin & Staff melihat seluruh klien (Opsi 2); Psikolog hanya melihat klien yang ditangani.
     */
    public function view(User $user, Client $client): bool
    {
        if ($user->isAdmin() || $user->isStaff()) {
            return true;
        }

        if ($user->isPsikolog()) {
            return $client->isHandledByUser($user);
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isStaff();
    }

    /**
     * Admin & Staff dapat memperbarui biodata klien.
     */
    public function update(User $user, Client $client): bool
    {
        return $user->isAdmin() || $user->isStaff();
    }

    public function delete(User $user, Client $client): bool
    {
        return $user->isAdmin();
    }
}
