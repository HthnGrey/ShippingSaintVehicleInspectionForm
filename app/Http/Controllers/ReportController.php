<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    public function update(Request $request, MaintenanceReport $maintenanceReport): RedirectResponse
    {
        $data = $request->validate([
            'completed_by' => ['required', 'string', 'max:255'],
            'maintenance_completed' => ['required', 'string', 'max:5000'],
            'completed_at' => ['required', 'date'],
        ]);

        $maintenanceReport->update($data);

        return redirect()->route('reports.index');
    }

    public function destroy(MaintenanceReport $maintenanceReport): RedirectResponse
    {
        $maintenanceReport->delete();

        return redirect()->route('reports.index');
    }
}
