<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\VehicleInspection;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VehicleInspectionController extends Controller
{
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

        $data['inspection_type'] = 'Pre Trip';

        VehicleInspection::create($data);

        Vehicle::where('id', $data['vehicle_id'])->update([
            'status' => $data['damage_found'] ? 'Needs Maintenance' : 'In Use',
        ]);

        return redirect()->route('dashboard');
    }

    public function createPostTrip()
    {
        return Inertia::render('Inspections/PostTrip', [
            'vehicles' => Vehicle::orderBy('name')->get(),
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

        $data['inspection_type'] = 'Post Trip';

        VehicleInspection::create($data);

        Vehicle::where('id', $data['vehicle_id'])->update([
            'current_mileage' => $data['ending_mileage'],
            'status' => $data['damage_found'] ? 'Needs Maintenance' : 'Available',
        ]);

        return redirect()->route('dashboard');
    }
}
