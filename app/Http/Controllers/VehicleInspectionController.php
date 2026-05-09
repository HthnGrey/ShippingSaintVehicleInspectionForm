<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\VehicleInspection;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class VehicleInspectionController extends Controller
{
    public function index()
    {
        return Inertia::render('Inspections/Index', [
            'inspections' => VehicleInspection::with(['vehicle', 'preTrip.vehicle', 'pairedPostTrip'])
                ->latest()
                ->get(),
        ]);
    }

    public function createPreTrip()
    {
        return Inertia::render('Inspections/PreTrip', [
            'vehicles' => Vehicle::orderBy('name')->get(),
        ]);
    }

    public function storePreTrip(Request $request)
    {
        $data = $request->validate([
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'driver_name' => ['required', 'string', 'max:255'],
            'starting_mileage' => ['required', 'integer', 'min:0'],
            'fuel_level' => ['nullable', 'string', 'max:255'],
            'tires_ok' => ['required', 'boolean'],
            'lights_ok' => ['required', 'boolean'],
            'brakes_ok' => ['required', 'boolean'],
            'fluids_ok' => ['required', 'boolean'],
            'damage_found' => ['required', 'boolean'],
            'damage_notes' => ['nullable', 'string'],
        ]);

        $vehicle = $this->vehicleForInspection($data['vehicle_id'], 'Pre Trip');

        $data['inspection_type'] = 'Pre Trip';

        VehicleInspection::create($data);

        $vehicle->update([
            'status' => $data['damage_found'] ? 'Needs Maintenance' : 'In Use',
        ]);

        return redirect()->route('dashboard');
    }

    public function createPostTrip()
    {
        return Inertia::render('Inspections/PostTrip', [
            'vehicles' => Vehicle::orderBy('name')->get(),
            'preTripInspections' => VehicleInspection::where('inspection_type', 'Pre Trip')
                ->whereDoesntHave('pairedPostTrip')
                ->latest()
                ->get(),
        ]);
    }

    public function storePostTrip(Request $request)
    {
        $data = $request->validate([
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'driver_name' => ['required', 'string', 'max:255'],
            'starting_mileage' => ['required', 'integer', 'min:0'],
            'ending_mileage' => ['required', 'integer', 'gte:starting_mileage'],
            'fuel_level' => ['nullable', 'string', 'max:255'],
            'tires_ok' => ['required', 'boolean'],
            'lights_ok' => ['required', 'boolean'],
            'brakes_ok' => ['required', 'boolean'],
            'fluids_ok' => ['required', 'boolean'],
            'damage_found' => ['required', 'boolean'],
            'damage_notes' => ['nullable', 'string'],
        ]);

        $vehicle = $this->vehicleForInspection($data['vehicle_id'], 'Post Trip');
        $preTrip = $this->preTripForPostTrip($vehicle);

        $data['inspection_type'] = 'Post Trip';
        $data['pre_trip_inspection_id'] = $preTrip->id;
        $data['driver_name'] = $preTrip->driver_name;

        VehicleInspection::create($data);

        $vehicle->update([
            'current_mileage' => $data['ending_mileage'],
            'status' => $this->statusAfterPostTrip($vehicle, $data),
        ]);

        return redirect()->route('dashboard');
    }

    private function vehicleForInspection(int|string $vehicleId, string $inspectionType): Vehicle
    {
        $vehicle = Vehicle::findOrFail($vehicleId);

        if ($inspectionType === 'Pre Trip' && $vehicle->status === 'In Use') {
            throw ValidationException::withMessages([
                'vehicle_id' => 'You cannot create a pre trip inspection for a vehicle that is in use.',
            ]);
        }

        if ($inspectionType === 'Pre Trip' && $vehicle->status === 'Maintenance Required') {
            throw ValidationException::withMessages([
                'vehicle_id' => 'Maintenance must be completed before this vehicle can be used.',
            ]);
        }

        if ($inspectionType === 'Post Trip' && $vehicle->status === 'Available') {
            throw ValidationException::withMessages([
                'vehicle_id' => 'You cannot create a post trip inspection for a vehicle that is available.',
            ]);
        }

        return $vehicle;
    }

    private function statusAfterPostTrip(Vehicle $vehicle, array $data): string
    {
        if ($data['damage_found']) {
            return 'Needs Maintenance';
        }

        if ($data['ending_mileage'] >= $vehicle->required_maintenance_mileage) {
            return 'Maintenance Required';
        }

        return 'Available';
    }

    private function preTripForPostTrip(Vehicle $vehicle): VehicleInspection
    {
        $preTrip = VehicleInspection::where('vehicle_id', $vehicle->id)
            ->where('inspection_type', 'Pre Trip')
            ->whereDoesntHave('pairedPostTrip')
            ->latest()
            ->first();

        if (! $preTrip) {
            throw ValidationException::withMessages([
                'vehicle_id' => 'No open pre trip inspection was found for this vehicle.',
            ]);
        }

        return $preTrip;
    }
}
