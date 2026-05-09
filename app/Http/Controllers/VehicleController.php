<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VehicleController extends Controller
{
    public function index()
    {
        return Inertia::render('Vehicles/Index', [
            'vehicles' => Vehicle::latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'plate_number' => ['nullable', 'string', 'max:255'],
            'current_mileage' => ['required', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $data['status'] = 'Available';

        Vehicle::create($data);

        return redirect()->route('vehicles.index');
    }
}
