<?php

namespace App\Http\Controllers;

use App\Models\TestResult;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TestResultController extends Controller
{
    public function index(Request $request)
    {
        $query = TestResult::with(['client', 'administrator'])->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('test_name', 'like', "%{$search}%")
                  ->orWhereHas('client', fn($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        $testResults = $query->paginate(15)->withQueryString();

        return view('test-results.index', compact('testResults'));
    }

    public function create()
    {
        $clients = Client::orderBy('name')->get();
        return view('test-results.create', compact('clients'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'test_name' => 'required|string|max:255',
            'result_summary' => 'nullable|string',
            'file' => 'nullable|file|max:10240',
            'tested_at' => 'nullable|date',
        ]);

        if ($request->hasFile('file')) {
            $validated['file_path'] = $request->file('file')->store('test-results', 'public');
        }

        unset($validated['file']);
        $validated['administered_by'] = auth()->id();

        TestResult::create($validated);

        return redirect()->route('test-results.index')->with('success', 'Hasil tes berhasil ditambahkan.');
    }

    public function destroy(TestResult $testResult)
    {
        if ($testResult->file_path) {
            Storage::disk('public')->delete($testResult->file_path);
        }

        $testResult->delete();
        return redirect()->route('test-results.index')->with('success', 'Hasil tes berhasil dihapus.');
    }
}
