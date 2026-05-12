<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleInspection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DriverHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_driver_only_sees_their_own_history(): void
    {
        $driver = User::factory()->create(['name' => 'Alex Driver', 'role' => User::ROLE_DRIVER]);
        $vehicle = Vehicle::create([
            'name' => 'Truck 1',
            'current_mileage' => 100,
            'required_maintenance_mileage' => 1000,
            'status' => 'Available',
        ]);

        $this->inspection($vehicle, 'Alex Driver');
        $this->inspection($vehicle, 'Sam Driver');

        $this->actingAs($driver)
            ->get(route('driver-history.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('DriverHistory/Index')
                ->has('inspections', 1)
                ->where('inspections.0.driver_name', 'Alex Driver')
            );
    }

    public function test_manager_can_filter_driver_history(): void
    {
        $manager = User::factory()->create(['role' => User::ROLE_MANAGER]);
        $vehicle = Vehicle::create([
            'name' => 'Truck 1',
            'current_mileage' => 100,
            'required_maintenance_mileage' => 1000,
            'status' => 'Available',
        ]);

        $this->inspection($vehicle, 'Alex Driver');
        $this->inspection($vehicle, 'Sam Driver');

        $this->actingAs($manager)
            ->get(route('driver-history.index', ['driver' => 'Sam Driver']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('DriverHistory/Index')
                ->has('inspections', 1)
                ->where('inspections.0.driver_name', 'Sam Driver')
            );
    }

    private function inspection(Vehicle $vehicle, string $driverName): void
    {
        VehicleInspection::create([
            'vehicle_id' => $vehicle->id,
            'inspection_type' => 'Post Trip',
            'driver_name' => $driverName,
            'starting_mileage' => 100,
            'ending_mileage' => 150,
            'fuel_level' => 'Full',
            'tires_ok' => true,
            'lights_ok' => true,
            'brakes_ok' => true,
            'fluids_ok' => true,
            'damage_found' => false,
            'damage_notes' => null,
        ]);
    }
}
