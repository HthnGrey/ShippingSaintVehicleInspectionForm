<?php

namespace Tests\Feature;

use App\Models\Vehicle;
use App\Models\VehicleInspection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleInspectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_pre_trip_inspection_cannot_be_created_for_vehicle_in_use(): void
    {
        $vehicle = Vehicle::create([
            'name' => 'Truck 1',
            'current_mileage' => 100,
            'status' => 'In Use',
        ]);

        $response = $this->withSession(['_token' => 'test-token'])
            ->from(route('inspections.pre'))
            ->post(route('inspections.pre.store'), [
                ...$this->preTripPayload($vehicle),
                '_token' => 'test-token',
            ]);

        $response->assertRedirect(route('inspections.pre'));
        $response->assertSessionHasErrors('vehicle_id');
        $this->assertSame(0, VehicleInspection::count());
        $this->assertSame('In Use', $vehicle->fresh()->status);
    }

    public function test_post_trip_inspection_cannot_be_created_for_available_vehicle(): void
    {
        $vehicle = Vehicle::create([
            'name' => 'Truck 1',
            'current_mileage' => 100,
            'status' => 'Available',
        ]);

        $response = $this->withSession(['_token' => 'test-token'])
            ->from(route('inspections.post'))
            ->post(route('inspections.post.store'), [
                ...$this->postTripPayload($vehicle),
                '_token' => 'test-token',
            ]);

        $response->assertRedirect(route('inspections.post'));
        $response->assertSessionHasErrors('vehicle_id');
        $this->assertSame(0, VehicleInspection::count());
        $this->assertSame('Available', $vehicle->fresh()->status);
    }

    public function test_post_trip_inspection_is_paired_with_open_pre_trip(): void
    {
        $vehicle = Vehicle::create([
            'name' => 'Truck 1',
            'current_mileage' => 100,
            'status' => 'In Use',
        ]);
        $preTrip = VehicleInspection::create([
            ...$this->preTripPayload($vehicle),
            'inspection_type' => 'Pre Trip',
        ]);

        $response = $this->withSession(['_token' => 'test-token'])
            ->post(route('inspections.post.store'), [
                ...$this->postTripPayload($vehicle),
                '_token' => 'test-token',
            ]);

        $response->assertRedirect(route('dashboard'));

        $postTrip = VehicleInspection::where('inspection_type', 'Post Trip')->firstOrFail();
        $this->assertSame($preTrip->id, $postTrip->pre_trip_inspection_id);
        $this->assertSame('Available', $vehicle->fresh()->status);
    }

    public function test_post_trip_driver_name_comes_from_paired_pre_trip(): void
    {
        $vehicle = Vehicle::create([
            'name' => 'Truck 1',
            'current_mileage' => 100,
            'status' => 'In Use',
        ]);
        VehicleInspection::create([
            ...$this->preTripPayload($vehicle),
            'inspection_type' => 'Pre Trip',
            'driver_name' => 'Pre Trip Driver',
        ]);

        $response = $this->withSession(['_token' => 'test-token'])
            ->post(route('inspections.post.store'), [
                ...$this->postTripPayload($vehicle),
                'driver_name' => 'Different Driver',
                '_token' => 'test-token',
            ]);

        $response->assertRedirect(route('dashboard'));

        $postTrip = VehicleInspection::where('inspection_type', 'Post Trip')->firstOrFail();
        $this->assertSame('Pre Trip Driver', $postTrip->driver_name);
    }

    public function test_post_trip_moves_vehicle_to_maintenance_required_when_mileage_threshold_is_reached(): void
    {
        $vehicle = Vehicle::create([
            'name' => 'Truck 1',
            'current_mileage' => 100,
            'required_maintenance_mileage' => 120,
            'status' => 'In Use',
        ]);
        VehicleInspection::create([
            ...$this->preTripPayload($vehicle),
            'inspection_type' => 'Pre Trip',
        ]);

        $response = $this->withSession(['_token' => 'test-token'])
            ->post(route('inspections.post.store'), [
                ...$this->postTripPayload($vehicle),
                'ending_mileage' => 120,
                '_token' => 'test-token',
            ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertSame('Maintenance Required', $vehicle->fresh()->status);
    }

    public function test_post_trip_inspection_requires_an_open_pre_trip(): void
    {
        $vehicle = Vehicle::create([
            'name' => 'Truck 1',
            'current_mileage' => 100,
            'status' => 'In Use',
        ]);

        $response = $this->withSession(['_token' => 'test-token'])
            ->from(route('inspections.post'))
            ->post(route('inspections.post.store'), [
                ...$this->postTripPayload($vehicle),
                '_token' => 'test-token',
            ]);

        $response->assertRedirect(route('inspections.post'));
        $response->assertSessionHasErrors('vehicle_id');
        $this->assertSame(0, VehicleInspection::count());
        $this->assertSame('In Use', $vehicle->fresh()->status);
    }

    private function preTripPayload(Vehicle $vehicle): array
    {
        return [
            'vehicle_id' => $vehicle->id,
            'driver_name' => 'Test Driver',
            'starting_mileage' => 100,
            'fuel_level' => 'Full',
            'tires_ok' => true,
            'lights_ok' => true,
            'brakes_ok' => true,
            'fluids_ok' => true,
            'damage_found' => false,
            'damage_notes' => null,
        ];
    }

    private function postTripPayload(Vehicle $vehicle): array
    {
        return [
            ...$this->preTripPayload($vehicle),
            'ending_mileage' => 120,
        ];
    }
}
