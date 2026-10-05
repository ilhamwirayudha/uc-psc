<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the dashboard view.
     * Tampilan dashboard dikosongkan sementara dan akan didesain pada tahap akhir pengembangan.
     */
    public function index(Request $request)
    {
        return view('dashboard.index');
    }
}
