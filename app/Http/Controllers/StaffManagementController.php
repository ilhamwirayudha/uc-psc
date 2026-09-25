<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Client;
use App\Models\ClientStaffAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class StaffManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'staff')->withCount('assignedClients')->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $staffs = $query->paginate(15)->withQueryString();

        return view('staff-management.index', compact('staffs'));
    }

    public function create()
    {
        return view('staff-management.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'staff',
        ]);

        return redirect()->route('staff-management.index')->with('success', 'Staff berhasil ditambahkan.');
    }

    public function show(User $staff)
    {
        if ($staff->isAdmin()) {
            return redirect()->route('staff-management.index')->with('error', 'Halaman detail hanya untuk akun staff.');
        }

        $activeClients = Client::where('assigned_staff_id', $staff->id)
            ->with(['creator', 'testResults'])
            ->latest()
            ->get();

        $historyAssignments = ClientStaffAssignment::where('staff_id', $staff->id)
            ->whereNotNull('ended_at')
            ->with(['client', 'assigner'])
            ->latest('ended_at')
            ->take(30)
            ->get();

        $unassignedClients = Client::whereNull('assigned_staff_id')->orderBy('name')->get();
        $otherStaffs = User::where('role', 'staff')->where('id', '!=', $staff->id)->orderBy('name')->get();

        return view('staff-management.show', compact('staff', 'activeClients', 'historyAssignments', 'unassignedClients', 'otherStaffs'));
    }

    public function destroy(User $staff)
    {
        if ($staff->isAdmin()) {
            return redirect()->route('staff-management.index')->with('error', 'Tidak bisa menghapus akun Admin.');
        }

        // Akhiri assignment aktif sebelum menghapus staff
        ClientStaffAssignment::where('staff_id', $staff->id)
            ->whereNull('ended_at')
            ->update(['ended_at' => now()]);

        Client::where('assigned_staff_id', $staff->id)
            ->update(['assigned_staff_id' => null]);

        $staff->delete();
        return redirect()->route('staff-management.index')->with('success', 'Staff berhasil dihapus dan penugasan telah dilepas.');
    }
}
