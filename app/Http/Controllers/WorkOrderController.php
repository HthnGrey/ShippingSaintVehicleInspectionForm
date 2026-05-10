<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceReport;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WorkOrderController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('WorkOrders/Index', [
            'vehicles' => Vehicle::where('status', 'Maintenance Required')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function complete(Request $request, Vehicle $vehicle): RedirectResponse
    {
        $validated = $request->validate([
            'completed_by' => ['required', 'string', 'max:255'],
            'maintenance_completed' => ['required', 'string', 'max:5000'],
        ]);

        if ($vehicle->status === 'Maintenance Required') {
            MaintenanceReport::create([
                'vehicle_id' => $vehicle->id,
                'completed_by' => $validated['completed_by'],
                'maintenance_completed' => $validated['maintenance_completed'],
                'completed_at' => now(),
            ]);

            $vehicle->update([
                'status' => 'Available',
                'required_maintenance_mileage' => $vehicle->current_mileage + 8000,
            ]);
        }

        return redirect()->route('work-orders.index');
    }
}
