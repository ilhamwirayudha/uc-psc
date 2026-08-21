<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AdminManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'admin')->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $admins = $query->paginate(15)->withQueryString();

        return view('admin-management.index', compact('admins'));
    }

    public function create()
    {
        return view('admin-management.create');
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
            'role' => 'admin',
        ]);

        return redirect()->route('admin-management.index')->with('success', 'Admin berhasil ditambahkan.');
    }

    public function destroy(User $admin)
    {
        if ($admin->isMaster()) {
            return redirect()->route('admin-management.index')->with('error', 'Tidak bisa menghapus akun Master.');
        }

        $admin->delete();
        return redirect()->route('admin-management.index')->with('success', 'Admin berhasil dihapus.');
    }
}
