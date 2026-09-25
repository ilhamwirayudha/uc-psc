<?php

namespace App\Policies;

use App\Models\TestResult;
use App\Models\User;

class TestResultPolicy
{
    /**
     * Determine whether the user can view any test results.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the specific test result.
     */
    public function view(User $user, TestResult $testResult): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        // Staff hanya bisa melihat kasus jika dirinya terkait secara penugasan
        return ($testResult->client && $testResult->client->assigned_staff_id === $user->id)
            || ($testResult->booking && $testResult->booking->staff_penguji_id === $user->id)
            || ($testResult->booking && $testResult->booking->staff_koreksi_id === $user->id)
            || ($testResult->booking && $testResult->booking->staff_pelapor_id === $user->id)
            || $testResult->administered_by === $user->id;
    }

    /**
     * Determine whether the user can create test results.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the test result.
     */
    public function update(User $user, TestResult $testResult): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return ($testResult->client && $testResult->client->assigned_staff_id === $user->id)
            || ($testResult->booking && $testResult->booking->staff_penguji_id === $user->id)
            || ($testResult->booking && $testResult->booking->staff_koreksi_id === $user->id)
            || ($testResult->booking && $testResult->booking->staff_pelapor_id === $user->id)
            || $testResult->administered_by === $user->id;
    }

    /**
     * Determine whether the user can upload documents.
     */
    public function uploadDocument(User $user, TestResult $testResult): bool
    {
        return $this->update($user, $testResult);
    }

    /**
     * Determine whether the user can download documents.
     */
    public function downloadDocument(User $user, TestResult $testResult): bool
    {
        return $this->view($user, $testResult);
    }

    /**
     * Determine whether the user can mark result as delivered.
     */
    public function deliver(User $user, TestResult $testResult): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return ($testResult->booking && $testResult->booking->staff_pelapor_id === $user->id)
            || ($testResult->client && $testResult->client->assigned_staff_id === $user->id);
    }

    /**
     * Determine whether the user can assign staff.
     */
    public function assignStaff(User $user, TestResult $testResult): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can delete the test result.
     */
    public function delete(User $user, TestResult $testResult): bool
    {
        return $user->role === 'admin';
    }
}
