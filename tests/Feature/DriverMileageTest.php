<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleInspection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DriverMileageTest extends TestCase
{
    use RefreshDatabase;

    public function test_driver_mileage_totals_are_grouped_by_driver_for_selected_range(): void
    {
        $manager = User::factory()->create(['role' => User::ROLE_MANAGER]);
        $vehicle = Vehicle::create([
            'name' => 'Truck 1',
            'current_mileage' => 100,
            'required_maintenance_mileage' => 1000,
            'status' => 'Available',
        ]);

        $this->postTrip($vehicle, 'Alex Driver', 100, 150, now()->subDays(3));
        $this->postTrip($vehicle, 'Alex Driver', 200, 260, now()->subDays(5));
        $this->postTrip($vehicle, 'Sam Driver', 300, 340, now()->subDays(4));
        $this->postTrip($vehicle, 'Alex Driver', 400, 500, now()->subDays(20));

        $response = $this->actingAs($manager)
            ->get(route('driver-mileage.index', ['range' => '7_days']));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('DriverMileage/Index')
            ->where('selectedRange', '7_days')
            ->where('drivers.0.driver_name', 'Alex Driver')
            ->where('drivers.0.total_miles', 110)
            ->where('drivers.0.trip_count', 2)
            ->where('drivers.1.driver_name', 'Sam Driver')
            ->where('drivers.1.total_miles', 40)
            ->where('drivers.1.trip_count', 1)
        );
    }

    public function test_lifetime_filter_includes_all_completed_post_trips(): void
    {
        $manager = User::factory()->create(['role' => User::ROLE_MANAGER]);
        $vehicle = Vehicle::create([
            'name' => 'Truck 1',
            'current_mileage' => 100,
            'required_maintenance_mileage' => 1000,
            'status' => 'Available',
        ]);

        $this->postTrip($vehicle, 'Alex Driver', 100, 150, now()->subYears(2));
        $this->postTrip($vehicle, 'Alex Driver', 200, 260, now()->subDays(5));

        $response = $this->actingAs($manager)
            ->get(route('driver-mileage.index', ['range' => 'lifetime']));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('DriverMileage/Index')
            ->where('selectedRange', 'lifetime')
            ->where('drivers.0.driver_name', 'Alex Driver')
            ->where('drivers.0.total_miles', 110)
            ->where('drivers.0.trip_count', 2)
        );
    }

    private function postTrip(Vehicle $vehicle, string $driverName, int $startingMileage, int $endingMileage, mixed $createdAt): void
    {
        $inspection = VehicleInspection::create([
            'vehicle_id' => $vehicle->id,
            'inspection_type' => 'Post Trip',
            'driver_name' => $driverName,
            'starting_mileage' => $startingMileage,
            'ending_mileage' => $endingMileage,
            'fuel_level' => 'Full',
            'tires_ok' => true,
            'lights_ok' => true,
            'brakes_ok' => true,
            'fluids_ok' => true,
            'damage_found' => false,
            'damage_notes' => null,
        ]);

        $inspection->forceFill([
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ])->save();
    }
}
