<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    public function index(Request $request)
    {
        $query = Holiday::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $sort = $request->get('sort');
        $dir = strtolower((string) $request->get('dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        if ($sort === 'date') {
            $query->orderBy('date', $dir);
        } elseif ($sort === 'name') {
            $query->orderBy('name', $dir);
        } else {
            $query->orderBy('date', 'asc');
        }

        $holidays = $query->paginate(20)->withQueryString();

        return view('master.holidays-index', compact('holidays', 'sort', 'dir'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date|unique:holidays,date',
            'name' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ], [
            'date.unique' => 'Tanggal libur nasional ini sudah terdaftar.',
        ]);

        Holiday::create($validated);

        return redirect()->route('holidays.index')->with('success', 'Hari libur nasional berhasil ditambahkan.');
    }

    public function update(Request $request, Holiday $holiday)
    {
        $validated = $request->validate([
            'date' => 'required|date|unique:holidays,date,' . $holiday->id,
            'name' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $holiday->update($validated);

        return redirect()->route('holidays.index')->with('success', 'Hari libur nasional berhasil diperbarui.');
    }

    public function destroy(Holiday $holiday)
    {
        $holiday->delete();

        return redirect()->route('holidays.index')->with('success', 'Hari libur nasional berhasil dihapus.');
    }
}
