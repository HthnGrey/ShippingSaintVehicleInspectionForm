<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceReport;
use App\Models\VehicleInspection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function reports(): StreamedResponse
    {
        return $this->csv('maintenance-reports.csv', [
            'Vehicle',
            'Completed By',
            'Completed At',
            'Maintenance Completed',
        ], MaintenanceReport::with('vehicle')->latest('completed_at')->get()->map(fn (MaintenanceReport $report) => [
            $report->vehicle?->name,
            $report->completed_by,
            $report->completed_at?->toDateTimeString(),
            $report->maintenance_completed,
        ]));
    }

    public function inspections(): StreamedResponse
    {
        return $this->csv('inspections.csv', [
            'Vehicle',
            'Type',
            'Driver',
            'Starting Mileage',
            'Ending Mileage',
            'Damage Found',
            'Damage Notes',
            'Submitted At',
        ], VehicleInspection::with('vehicle')->latest()->get()->map(fn (VehicleInspection $inspection) => [
            $inspection->vehicle?->name,
            $inspection->inspection_type,
            $inspection->driver_name,
            $inspection->starting_mileage,
            $inspection->ending_mileage,
            $inspection->damage_found ? 'Yes' : 'No',
            $inspection->damage_notes,
            $inspection->created_at?->toDateTimeString(),
        ]));
    }

    public function driverMileage(Request $request): StreamedResponse
    {
        $range = $request->query('range', 'lifetime');
        $startDate = match ($range) {
            '7_days' => now()->subDays(7),
            '14_days' => now()->subDays(14),
            '30_days' => now()->subDays(30),
            '3_months' => now()->subMonths(3),
            '6_months' => now()->subMonths(6),
            '1_year' => now()->subYear(),
            default => null,
        };

        $query = VehicleInspection::query()
            ->where('inspection_type', 'Post Trip')
            ->whereNotNull('ending_mileage');

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        return $this->csv('driver-mileage.csv', [
            'Driver',
            'Miles Driven',
            'Trips',
            'Range',
        ], $query
            ->select('driver_name')
            ->selectRaw('SUM(ending_mileage - starting_mileage) as total_miles')
            ->selectRaw('COUNT(*) as trip_count')
            ->groupBy('driver_name')
            ->orderByDesc(DB::raw('SUM(ending_mileage - starting_mileage)'))
            ->get()
            ->map(fn (VehicleInspection $inspection) => [
                $inspection->driver_name,
                (int) $inspection->total_miles,
                (int) $inspection->trip_count,
                $range,
            ]));
    }

    private function csv(string $filename, array $headers, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
