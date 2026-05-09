<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\VehicleInspection;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('Dashboard', [
            'vehicles' => Vehicle::latest()->get(),
            'recentInspections' => VehicleInspection::with('vehicle')
                ->latest()
                ->take(10)
                ->get(),

        ]);
    }
}
