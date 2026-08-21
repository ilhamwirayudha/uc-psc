<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Counselor;
use App\Models\CounselingRecord;
use App\Models\Pairing;
use App\Models\TestResult;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_clients' => Client::count(),
            'total_counselors' => Counselor::where('status', 'active')->count(),
            'total_sessions' => CounselingRecord::count(),
            'total_pairings' => Pairing::where('status', 'active')->count(),
            'assigned_clients' => Client::whereIn('status', ['assigned', 'ongoing'])->count(),
            'scheduled_sessions' => CounselingRecord::where('status', 'scheduled')->count(),
            'completed_sessions' => CounselingRecord::where('status', 'completed')->count(),
            'unassigned_clients' => Client::whereDoesntHave('pairings', function ($query) {
                $query->where('status', 'active');
            })->count(),
        ];

        $recentRecords = CounselingRecord::with(['client', 'counselor', 'admin'])
            ->latest()
            ->take(50)
            ->get();

        $recentClients = Client::with('creator')
            ->latest()
            ->take(50)
            ->get();

        return view('dashboard.index', compact('stats', 'recentRecords', 'recentClients'));
    }
}
