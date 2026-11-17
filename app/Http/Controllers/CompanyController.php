<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        $query = Company::query()->withCount('bookings');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('pic_name', 'like', "%{$search}%")
                    ->orWhere('pic_email', 'like', "%{$search}%")
                    ->orWhere('pic_phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $sort = $request->get('sort');
        $dir = strtolower((string) $request->get('dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        if ($sort === 'name') {
            $query->orderBy('name', $dir);
        } elseif ($sort === 'category') {
            $query->orderBy('category', $dir);
        } elseif ($sort === 'created_at') {
            $query->orderBy('created_at', $dir);
        } else {
            $query->orderBy('name');
        }

        $companies = $query->paginate(15)->withQueryString();

        return view('master.companies-index', compact('companies', 'sort', 'dir'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:kerja_sama,umum',
            'pic_name' => 'nullable|string|max:255',
            'pic_phone' => 'nullable|string|max:50',
            'pic_email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $company = Company::create($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'company' => $company]);
        }

        return redirect()->route('companies.index')->with('success', "Perusahaan {$company->name} berhasil ditambahkan.");
    }

    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:kerja_sama,umum',
            'pic_name' => 'nullable|string|max:255',
            'pic_phone' => 'nullable|string|max:50',
            'pic_email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $company->update($validated);

        return redirect()->route('companies.index')->with('success', "Data perusahaan {$company->name} berhasil diperbarui.");
    }

    public function destroy(Company $company)
    {
        $name = $company->name;
        $company->delete();

        return redirect()->route('companies.index')->with('success', "Perusahaan {$name} berhasil dihapus.");
    }
}
