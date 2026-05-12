<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VehicleController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Vehicles/Index', [
            'vehicles' => Vehicle::latest()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);

        $vehicle = Vehicle::create($this->withStatus($data));

        AuditLog::record('created', $vehicle, "Created vehicle {$vehicle->name}", [
            'vehicle' => $vehicle->only(['name', 'plate_number', 'current_mileage', 'required_maintenance_mileage', 'status']),
        ]);

        return redirect()->route('vehicles.index');
    }

    public function update(Request $request, Vehicle $vehicle): RedirectResponse
    {
        $before = $vehicle->only(['name', 'plate_number', 'current_mileage', 'required_maintenance_mileage', 'status', 'notes']);

        $vehicle->update($this->withStatus($this->validatedData($request)));

        AuditLog::record('updated', $vehicle, "Updated vehicle {$vehicle->name}", [
            'before' => $before,
            'after' => $vehicle->fresh()->only(['name', 'plate_number', 'current_mileage', 'required_maintenance_mileage', 'status', 'notes']),
        ]);

        return redirect()->route('vehicles.index');
    }

    public function destroy(Vehicle $vehicle): RedirectResponse
    {
        $snapshot = $vehicle->only(['name', 'plate_number', 'current_mileage', 'required_maintenance_mileage', 'status']);

        AuditLog::record('deleted', $vehicle, "Deleted vehicle {$vehicle->name}", [
            'vehicle' => $snapshot,
        ]);

        $vehicle->delete();

        return redirect()->route('vehicles.index');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'plate_number' => ['nullable', 'string', 'max:255'],
            'current_mileage' => ['required', 'integer', 'min:0'],
            'required_maintenance_mileage' => ['required', 'integer', 'gte:current_mileage'],
            'notes' => ['nullable', 'string'],
        ]);
    }

    private function withStatus(array $data): array
    {
        $data['status'] = $data['current_mileage'] >= $data['required_maintenance_mileage']
            ? 'Maintenance Required'
            : 'Available';

        return $data;
    }
}
