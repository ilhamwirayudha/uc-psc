<?php

namespace App\Http\Controllers;

use App\Models\CounselingRecord;
use App\Models\Client;
use App\Models\Counselor;
use Illuminate\Http\Request;

class CounselingRecordController extends Controller
{
    public function index(Request $request)
    {
        $query = CounselingRecord::with(['client', 'counselor', 'admin'])->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('client', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $records = $query->paginate(15)->withQueryString();

        return view('counseling-records.index', compact('records'));
    }

    public function create()
    {
        $clients = Client::orderBy('name')->get();
        $counselors = Counselor::where('status', 'active')->orderBy('name')->get();

        return view('counseling-records.create', compact('clients', 'counselors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'counselor_id' => 'required|exists:counselors,id',
            'type' => 'required|in:whatsapp,tatap_muka,online',
            'scheduled_at' => 'nullable|date',
            'end_time' => 'nullable|date|after:scheduled_at',
            'location' => 'nullable|string|max:255',
            'summary' => 'nullable|string',
            'status' => 'required|in:scheduled,completed,cancelled',
        ]);

        $validated['admin_id'] = auth()->id();

        CounselingRecord::create($validated);

        return redirect()->route('counseling-records.index')->with('success', 'Catatan konseling berhasil ditambahkan.');
    }

    public function edit(CounselingRecord $counselingRecord)
    {
        $clients = Client::orderBy('name')->get();
        $counselors = Counselor::where('status', 'active')->orderBy('name')->get();

        return view('counseling-records.edit', compact('counselingRecord', 'clients', 'counselors'));
    }

    public function update(Request $request, CounselingRecord $counselingRecord)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'counselor_id' => 'required|exists:counselors,id',
            'type' => 'required|in:whatsapp,tatap_muka,online',
            'scheduled_at' => 'nullable|date',
            'end_time' => 'nullable|date|after:scheduled_at',
            'location' => 'nullable|string|max:255',
            'summary' => 'nullable|string',
            'status' => 'required|in:scheduled,completed,cancelled',
        ]);

        $counselingRecord->update($validated);

        return redirect()->route('counseling-records.index')->with('success', 'Catatan konseling berhasil diperbarui.');
    }

    public function destroy(CounselingRecord $counselingRecord)
    {
        $counselingRecord->delete();
        return redirect()->route('counseling-records.index')->with('success', 'Catatan konseling berhasil dihapus.');
    }
}
