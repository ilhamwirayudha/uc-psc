<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientStaffAssignment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssignmentController extends Controller
{
    /**
     * Tampilkan daftar klien belum ditugaskan dan ringkasan jumlah klien per staff.
     */
    public function index(Request $request)
    {
        $unassignedClients = Client::whereNull('assigned_staff_id')
            ->with('creator')
            ->latest()
            ->get();

        $assignedClients = Client::whereNotNull('assigned_staff_id')
            ->with(['assignedStaff', 'creator'])
            ->latest()
            ->get();

        // Ringkasan jumlah klien aktif per staff
        $staffSummary = User::where('role', 'staff')
            ->withCount(['assignedClients as active_clients_count'])
            ->orderBy('name')
            ->get();

        $allStaff = User::where('role', 'staff')->orderBy('name')->get();

        return view('assignments.index', compact('unassignedClients', 'assignedClients', 'staffSummary', 'allStaff'));
    }

    /**
     * Tugaskan staff ke klien.
     */
    public function assign(Request $request, Client $client)
    {
        $validated = $request->validate([
            'staff_id' => 'required|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($client, $validated) {
            // Akhiri assignment aktif sebelumnya (jika ada)
            ClientStaffAssignment::where('client_id', $client->id)
                ->whereNull('ended_at')
                ->update(['ended_at' => now()]);

            // Buat baris assignment baru
            ClientStaffAssignment::create([
                'client_id' => $client->id,
                'staff_id' => $validated['staff_id'],
                'assigned_by' => auth()->id(),
                'assigned_at' => now(),
                'notes' => $validated['notes'] ?? null,
            ]);

            // Update kolom assigned_staff_id di tabel clients
            $client->update([
                'assigned_staff_id' => $validated['staff_id'],
            ]);
        });

        $staff = User::find($validated['staff_id']);
        return back()->with('success', "Klien {$client->name} berhasil ditugaskan ke staff {$staff->name}.");
    }

    /**
     * Lepaskan (unassign) staff dari klien.
     */
    public function unassign(Request $request, Client $client)
    {
        DB::transaction(function () use ($client, $request) {
            $notes = $request->input('notes');
            
            ClientStaffAssignment::where('client_id', $client->id)
                ->whereNull('ended_at')
                ->update([
                    'ended_at' => now(),
                    'notes' => $notes ? DB::raw("CONCAT(COALESCE(notes, ''), ' [Catatan pelepasan: {$notes}]')") : DB::raw("notes")
                ]);

            $client->update([
                'assigned_staff_id' => null,
            ]);
        });

        return back()->with('success', "Klien {$client->name} berhasil dilepaskan dari penugasan staff.");
    }

    /**
     * Pindahkan (reassign) klien ke staff lain.
     */
    public function reassign(Request $request, Client $client)
    {
        $validated = $request->validate([
            'staff_id' => 'required|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($client, $validated) {
            // Akhiri assignment aktif sebelumnya
            ClientStaffAssignment::where('client_id', $client->id)
                ->whereNull('ended_at')
                ->update(['ended_at' => now()]);

            // Buat assignment baru
            ClientStaffAssignment::create([
                'client_id' => $client->id,
                'staff_id' => $validated['staff_id'],
                'assigned_by' => auth()->id(),
                'assigned_at' => now(),
                'notes' => $validated['notes'] ?? null,
            ]);

            $client->update([
                'assigned_staff_id' => $validated['staff_id'],
            ]);
        });

        $newStaff = User::find($validated['staff_id']);
        return back()->with('success', "Klien {$client->name} berhasil dialihkan ke staff {$newStaff->name}.");
    }
}
