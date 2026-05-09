<?php

namespace Tests\Feature;

use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_maintenance_completion_returns_vehicle_to_available_and_sets_next_threshold(): void
    {
        $vehicle = Vehicle::create([
            'name' => 'Truck 1',
            'current_mileage' => 12000,
            'required_maintenance_mileage' => 12000,
            'status' => 'Maintenance Required',
        ]);

        $response = $this->withSession(['_token' => 'test-token'])
            ->post(route('work-orders.complete', $vehicle), [
                '_token' => 'test-token',
            ]);

        $response->assertRedirect(route('work-orders.index'));

        $vehicle->refresh();
        $this->assertSame('Available', $vehicle->status);
        $this->assertSame(20000, $vehicle->required_maintenance_mileage);
    }
}
