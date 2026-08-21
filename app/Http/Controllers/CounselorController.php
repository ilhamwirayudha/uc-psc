<?php

namespace App\Http\Controllers;

use App\Models\Counselor;
use Illuminate\Http\Request;

class CounselorController extends Controller
{
    public function index(Request $request)
    {
        $query = Counselor::latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('specialization', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'trashed') {
                $query->onlyTrashed();
            } else {
                $query->where('status', $request->status);
            }
        }

        $counselors = $query->paginate(15)->withQueryString();

        return view('counselors.index', compact('counselors'));
    }

    public function create()
    {
        return view('counselors.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'specialization' => 'nullable|string|max:255',
            'sipp_number' => 'nullable|string|max:100',
            'str_number' => 'nullable|string|max:100',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string',
        ]);

        Counselor::create($validated);

        return redirect()->route('counselors.index')->with('success', 'Konselor berhasil ditambahkan.');
    }

    public function edit(Counselor $counselor)
    {
        return view('counselors.edit', compact('counselor'));
    }

    public function update(Request $request, Counselor $counselor)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'specialization' => 'nullable|string|max:255',
            'sipp_number' => 'nullable|string|max:100',
            'str_number' => 'nullable|string|max:100',
            'status' => 'sometimes|required|in:active,inactive',
            'notes' => 'nullable|string',
        ]);

        $counselor->update($validated);

        return redirect()->back()->with('success', 'Data konselor berhasil diperbarui.');
    }

    public function destroy(Counselor $counselor)
    {
        $counselor->delete();
        return redirect()->route('counselors.index')->with('success', 'Konselor berhasil dihapus.');
    }

    public function restore($id)
    {
        $counselor = Counselor::withTrashed()->findOrFail($id);
        $counselor->restore();
        
        return redirect()->back()->with('success', 'Data konselor berhasil dikembalikan.');
    }

    public function forceDelete($id)
    {
        $counselor = Counselor::withTrashed()->findOrFail($id);
        $counselor->forceDelete();
        
        return redirect()->back()->with('success', 'Data konselor berhasil dihapus permanen.');
    }
}
