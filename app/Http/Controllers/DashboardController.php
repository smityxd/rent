<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct(private DashboardService $dashboardService) {}

    public function index(Request $request)
    {
        return view('dashboard', $this->dashboardService->obtener((int)Auth::id()));
    }

    public function datos(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->dashboardService->obtener((int)Auth::id()),
        ]);
    }
}
