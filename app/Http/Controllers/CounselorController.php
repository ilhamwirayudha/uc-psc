<?php

namespace App\Http\Controllers;

use App\Models\Counselor;
use Illuminate\Http\Request;

class CounselorController extends Controller
{
    public function index(Request $request)
    {
        $query = Counselor::query()->withCount(['pairings']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('specialization', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('country', 'like', "%{$search}%")
                  ->orWhere('province', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('sipp_number', 'like', "%{$search}%")
                  ->orWhere('str_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $sort = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');

        if (in_array($sort, ['id', 'name', 'specialization', 'status', 'created_at', 'phone', 'email'])) {
            $query->orderBy('counselors.' . $sort, $direction);
        } else {
            $query->orderBy('counselors.created_at', $direction);
        }

        $counselors = $query->paginate(15)->withQueryString();

        return view('counselors.index', compact('counselors', 'sort', 'direction'));
    }

    public function create()
    {
        return view('counselors.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'phone' => 'nullable|string|max:13|regex:/^[0-9]+$/',
            'email' => 'nullable|email|max:255',
            'country' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'specialization' => 'nullable|string|max:255',
            'sipp_number' => 'nullable|string|max:100',
            'str_number' => 'nullable|string|max:100',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string',
        ], [
            'phone.max' => 'Nomor telepon maksimal 13 digit.',
            'phone.regex' => 'Nomor telepon hanya boleh berisi angka.',
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('counselors/photos', 'public');
        }

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
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'phone' => 'nullable|string|max:13|regex:/^[0-9]+$/',
            'email' => 'nullable|email|max:255',
            'country' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'specialization' => 'nullable|string|max:255',
            'sipp_number' => 'nullable|string|max:100',
            'str_number' => 'nullable|string|max:100',
            'status' => 'sometimes|required|in:active,inactive',
            'notes' => 'nullable|string',
        ], [
            'phone.max' => 'Nomor telepon maksimal 13 digit.',
            'phone.regex' => 'Nomor telepon hanya boleh berisi angka.',
        ]);

        if ($request->boolean('remove_photo')) {
            if ($counselor->photo && \Illuminate\Support\Facades\Storage::disk('public')->exists($counselor->photo)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($counselor->photo);
            }
            $validated['photo'] = null;
        }

        if ($request->hasFile('photo')) {
            if ($counselor->photo && \Illuminate\Support\Facades\Storage::disk('public')->exists($counselor->photo)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($counselor->photo);
            }
            $validated['photo'] = $request->file('photo')->store('counselors/photos', 'public');
        }

        $counselor->update($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Status konselor berhasil diperbarui.',
                'status' => $counselor->status,
            ]);
        }

        return redirect()->route('counselors.index')->with('success', 'Data konselor berhasil diperbarui.');
    }

    public function destroy(Counselor $counselor)
    {
        if ($counselor->photo && \Illuminate\Support\Facades\Storage::disk('public')->exists($counselor->photo)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($counselor->photo);
        }

        $counselor->delete();
        return redirect()->route('counselors.index')->with('success', 'Konselor berhasil dihapus.');
    }
}
