<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('Dashboard', [
            'vehicles' => Vehicle::latest()->get(),
        ]);
    }
}
