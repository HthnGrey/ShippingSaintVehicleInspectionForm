<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\MaintenanceReport;
use App\Models\Vehicle;
use App\Models\VehicleInspection;
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
                ->with(['inspections' => function ($query) {
                    $query->where(function ($query) {
                        $query->where('damage_found', true)
                            ->orWhere('tires_ok', false)
                            ->orWhere('lights_ok', false)
                            ->orWhere('brakes_ok', false)
                            ->orWhere('fluids_ok', false);
                    })->latest();
                }])
                ->orderBy('name')
                ->get()
                ->map(fn (Vehicle $vehicle) => [
                    ...$vehicle->toArray(),
                    'maintenance_issues' => $vehicle->inspections->map(fn (VehicleInspection $inspection) => [
                        'id' => $inspection->id,
                        'type' => $inspection->inspection_type,
                        'driver_name' => $inspection->driver_name,
                        'submitted_at' => $inspection->created_at,
                        'failed_items' => $this->failedItems($inspection),
                        'damage_notes' => $inspection->damage_notes,
                        'damage_photo_url' => $inspection->damage_photo_url,
                    ]),
                ]),
        ]);
    }

    public function complete(Request $request, Vehicle $vehicle): RedirectResponse
    {
        $validated = $request->validate([
            'maintenance_completed' => ['required', 'string', 'max:5000'],
        ]);

        if ($vehicle->status === 'Maintenance Required') {
            $report = MaintenanceReport::create([
                'vehicle_id' => $vehicle->id,
                'completed_by' => $request->user()->name,
                'maintenance_completed' => $validated['maintenance_completed'],
                'completed_at' => now(),
            ]);

            $vehicle->update([
                'status' => 'Available',
                'required_maintenance_mileage' => $vehicle->current_mileage + 8000,
            ]);

            AuditLog::record('created', $report, "Completed work order for {$vehicle->name}");
        }

        return redirect()->route('work-orders.index');
    }

    private function failedItems(VehicleInspection $inspection): array
    {
        $items = [];

        foreach ([
            'tires_ok' => 'Tires',
            'lights_ok' => 'Lights',
            'brakes_ok' => 'Brakes',
            'fluids_ok' => 'Fluids',
        ] as $field => $label) {
            if (! $inspection->{$field}) {
                $items[] = $label;
            }
        }

        if ($inspection->damage_found) {
            $items[] = 'Damage';
        }

        return $items;
    }
}
