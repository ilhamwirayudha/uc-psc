<?php

namespace App\Http\Controllers;

use App\Models\Counselor;
use App\Services\PublicMediaStorage;
use Illuminate\Http\Request;
use RuntimeException;

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

        $sort = $request->get('sort');
        $direction = $request->get('direction', 'desc');

        if (empty($sort)) {
            $query->orderBy('counselors.created_at', 'desc');
        } elseif (in_array($sort, ['id', 'name', 'specialization', 'status', 'created_at', 'phone', 'email'])) {
            $query->orderBy('counselors.' . $sort, $direction);
        } else {
            $query->orderBy('counselors.created_at', $direction);
        }

        $counselors = $query->paginate(15)->withQueryString();

        return view('counselors.counselor-directory-table', compact('counselors', 'sort', 'direction'));
    }

    public function create()
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Hanya Admin yang dapat menambah data Psikolog.');
        }

        return view('counselors.counselor-create-form');
    }

    public function store(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Hanya Admin yang dapat menambah data Psikolog.');
        }
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
            try {
                $validated['photo'] = PublicMediaStorage::storeUploadedFile($request->file('photo'), 'counselors/photos');
            } catch (RuntimeException $e) {
                return back()->withInput()->withErrors(['photo' => $e->getMessage()]);
            }
        }

        Counselor::create($validated);

        return redirect()->route('counselors.index')->with('success', 'Konselor berhasil ditambahkan.');
    }

    public function edit(Counselor $counselor)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Hanya Admin yang dapat mengubah data Psikolog.');
        }

        return view('counselors.counselor-edit-form', compact('counselor'));
    }

    public function update(Request $request, Counselor $counselor)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Hanya Admin yang dapat mengubah data Psikolog.');
        }
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
            PublicMediaStorage::delete($counselor->photo);
            $validated['photo'] = null;
        }

        if ($request->hasFile('photo')) {
            try {
                $newPhotoPath = PublicMediaStorage::storeUploadedFile($request->file('photo'), 'counselors/photos');
            } catch (RuntimeException $e) {
                return back()->withInput()->withErrors(['photo' => $e->getMessage()]);
            }

            PublicMediaStorage::delete($counselor->photo);
            $validated['photo'] = $newPhotoPath;
        }

        $counselor->update($validated);

        if ($counselor->user) {
            $userUpdates = array_filter([
                'name' => $validated['name'] ?? null,
                'phone' => $validated['phone'] ?? null,
            ], fn ($v) => !is_null($v));

            if (!empty($validated['email']) && !\App\Models\User::where('email', $validated['email'])->where('id', '!=', $counselor->user->id)->exists()) {
                $userUpdates['email'] = $validated['email'];
            }

            if (!empty($userUpdates)) {
                $counselor->user->update($userUpdates);
            }
        }

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
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Hanya Admin yang dapat menghapus data Psikolog.');
        }

        $activeTasks = $counselor->countActiveTasks();
        if ($activeTasks > 0) {
            return redirect()->route('counselors.index')
                ->with('error', "Psikolog {$counselor->name} masih memiliki {$activeTasks} tugas aktif. Alihkan tugas di Kelola Penugasan atau nonaktifkan statusnya terlebih dahulu.");
        }

        PublicMediaStorage::delete($counselor->photo);
        \App\Models\User::where('counselor_id', $counselor->id)->update(['counselor_id' => null]);

        $counselor->delete();
        return redirect()->route('counselors.index')->with('success', 'Konselor berhasil dihapus.');
    }
}
