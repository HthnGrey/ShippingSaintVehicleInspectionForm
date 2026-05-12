<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_update_and_delete_vehicle(): void
    {
        $manager = User::factory()->create(['role' => User::ROLE_MANAGER]);
        $vehicle = Vehicle::create([
            'name' => 'Truck 1',
            'current_mileage' => 100,
            'required_maintenance_mileage' => 1000,
            'status' => 'Available',
        ]);

        $this->actingAs($manager)
            ->patch(route('vehicles.update', $vehicle), [
                'name' => 'Truck 1 Updated',
                'plate_number' => 'ABC123',
                'current_mileage' => 150,
                'required_maintenance_mileage' => 1000,
                'notes' => 'Updated notes',
            ])
            ->assertRedirect(route('vehicles.index'));

        $this->assertSame('Truck 1 Updated', $vehicle->fresh()->name);

        $this->actingAs($manager)
            ->delete(route('vehicles.destroy', $vehicle))
            ->assertRedirect(route('vehicles.index'));

        $this->assertNull($vehicle->fresh());
        $this->assertGreaterThanOrEqual(2, AuditLog::count());
    }

    public function test_driver_cannot_update_or_delete_vehicle(): void
    {
        $driver = User::factory()->create(['role' => User::ROLE_DRIVER]);
        $vehicle = Vehicle::create([
            'name' => 'Truck 1',
            'current_mileage' => 100,
            'required_maintenance_mileage' => 1000,
            'status' => 'Available',
        ]);

        $this->actingAs($driver)
            ->patch(route('vehicles.update', $vehicle), [
                'name' => 'Truck 1 Updated',
                'current_mileage' => 150,
                'required_maintenance_mileage' => 1000,
            ])
            ->assertForbidden();

        $this->actingAs($driver)
            ->delete(route('vehicles.destroy', $vehicle))
            ->assertForbidden();
    }
}
