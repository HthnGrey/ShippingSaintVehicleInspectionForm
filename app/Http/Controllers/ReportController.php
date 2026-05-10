<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceReport;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Reports/Index', [
            'maintenanceReports' => MaintenanceReport::with('vehicle')
                ->latest('completed_at')
                ->get(),
        ]);
    }
}
