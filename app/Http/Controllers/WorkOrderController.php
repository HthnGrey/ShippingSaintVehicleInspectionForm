<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
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

    public function complete(Vehicle $vehicle): RedirectResponse
    {
        if ($vehicle->status === 'Maintenance Required') {
            $vehicle->update([
                'status' => 'Available',
                'required_maintenance_mileage' => $vehicle->current_mileage + 8000,
            ]);
        }

        return redirect()->route('work-orders.index');
    }
}
