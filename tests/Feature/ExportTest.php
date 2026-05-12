<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleInspection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_export_driver_mileage_csv(): void
    {
        $manager = User::factory()->create(['role' => User::ROLE_MANAGER]);
        $vehicle = Vehicle::create([
            'name' => 'Truck 1',
            'current_mileage' => 100,
            'required_maintenance_mileage' => 1000,
            'status' => 'Available',
        ]);

        VehicleInspection::create([
            'vehicle_id' => $vehicle->id,
            'inspection_type' => 'Post Trip',
            'driver_name' => 'Alex Driver',
            'starting_mileage' => 100,
            'ending_mileage' => 150,
            'tires_ok' => true,
            'lights_ok' => true,
            'brakes_ok' => true,
            'fluids_ok' => true,
            'damage_found' => false,
        ]);

        $response = $this->actingAs($manager)->get(route('exports.driver-mileage'));

        $response->assertOk();
        $this->assertStringContainsString('driver-mileage.csv', $response->headers->get('content-disposition'));
    }
}
