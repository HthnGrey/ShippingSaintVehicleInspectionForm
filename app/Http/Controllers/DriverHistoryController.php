<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\VehicleInspection;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DriverHistoryController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $selectedDriver = $request->query('driver');
        $canViewAll = $user->hasAnyRole([User::ROLE_ADMIN, User::ROLE_MANAGER]);

        $query = VehicleInspection::with(['vehicle', 'preTrip.vehicle'])
            ->latest();

        if ($canViewAll && $selectedDriver) {
            $query->where('driver_name', $selectedDriver);
        } elseif (! $canViewAll) {
            $query->where('driver_name', $user->name);
        }

        return Inertia::render('DriverHistory/Index', [
            'inspections' => $query->get(),
            'drivers' => $canViewAll
                ? VehicleInspection::query()->distinct()->orderBy('driver_name')->pluck('driver_name')
                : [],
            'selectedDriver' => $canViewAll ? $selectedDriver : $user->name,
            'canViewAllDrivers' => $canViewAll,
        ]);
    }
}
