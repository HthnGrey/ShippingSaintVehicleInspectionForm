<?php

namespace App\Http\Controllers;

use App\Models\VehicleInspection;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class DriverMileageController extends Controller
{
    private const FILTERS = [
        '7_days' => '7 Days',
        '14_days' => '14 Days',
        '30_days' => '30 Days',
        '3_months' => '3 Months',
        '6_months' => '6 Months',
        '1_year' => '1 Year',
        'lifetime' => 'Lifetime',
    ];

    public function index(Request $request): Response
    {
        $data = $request->validate([
            'range' => ['nullable', Rule::in(array_keys(self::FILTERS))],
        ]);

        $range = $data['range'] ?? '30_days';
        $startDate = $this->startDateFor($range);

        $query = VehicleInspection::query()
            ->where('inspection_type', 'Post Trip')
            ->whereNotNull('ending_mileage');

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        $drivers = $query
            ->select('driver_name')
            ->selectRaw('SUM(ending_mileage - starting_mileage) as total_miles')
            ->selectRaw('COUNT(*) as trip_count')
            ->selectRaw('MAX(created_at) as last_trip_at')
            ->groupBy('driver_name')
            ->orderByDesc(DB::raw('SUM(ending_mileage - starting_mileage)'))
            ->get()
            ->map(fn (VehicleInspection $inspection) => [
                'driver_name' => $inspection->driver_name,
                'total_miles' => (int) $inspection->total_miles,
                'trip_count' => (int) $inspection->trip_count,
                'last_trip_at' => $inspection->last_trip_at,
            ]);

        return Inertia::render('DriverMileage/Index', [
            'drivers' => $drivers,
            'filters' => self::FILTERS,
            'selectedRange' => $range,
        ]);
    }

    private function startDateFor(string $range): ?Carbon
    {
        return match ($range) {
            '7_days' => now()->subDays(7),
            '14_days' => now()->subDays(14),
            '30_days' => now()->subDays(30),
            '3_months' => now()->subMonths(3),
            '6_months' => now()->subMonths(6),
            '1_year' => now()->subYear(),
            default => null,
        };
    }
}
