<?php

namespace Tests\Feature;

use App\Models\MaintenanceReport;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class WorkOrderTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'name' => 'Fleet Admin',
            'role' => User::ROLE_ADMIN,
        ]);

        $this->actingAs($this->user);
    }

    public function test_completing_a_work_order_records_a_maintenance_report(): void
    {
        Carbon::setTestNow('2026-05-10 14:30:00');
        $vehicle = Vehicle::create([
            'name' => 'Truck 1',
            'current_mileage' => 120000,
            'required_maintenance_mileage' => 120000,
            'status' => 'Maintenance Required',
        ]);

        $response = $this->withSession(['_token' => 'test-token'])
            ->post(route('work-orders.complete', $vehicle), [
                'completed_by' => 'Spoofed Mechanic',
                'maintenance_completed' => 'Changed oil and replaced front brake pads.',
                '_token' => 'test-token',
            ]);

        $response->assertRedirect(route('work-orders.index'));
        $this->assertSame('Available', $vehicle->fresh()->status);
        $this->assertDatabaseHas('maintenance_reports', [
            'vehicle_id' => $vehicle->id,
            'completed_by' => $this->user->name,
            'maintenance_completed' => 'Changed oil and replaced front brake pads.',
            'completed_at' => '2026-05-10 14:30:00',
        ]);
    }

    public function test_completing_a_work_order_requires_report_fields(): void
    {
        $vehicle = Vehicle::create([
            'name' => 'Truck 1',
            'current_mileage' => 120000,
            'required_maintenance_mileage' => 120000,
            'status' => 'Maintenance Required',
        ]);

        $response = $this->withSession(['_token' => 'test-token'])
            ->from(route('work-orders.index'))
            ->post(route('work-orders.complete', $vehicle), [
                '_token' => 'test-token',
            ]);

        $response->assertRedirect(route('work-orders.index'));
        $response->assertSessionHasErrors(['maintenance_completed']);
        $this->assertSame('Maintenance Required', $vehicle->fresh()->status);
        $this->assertSame(0, MaintenanceReport::count());
    }

    public function test_maintenance_report_can_be_updated(): void
    {
        $vehicle = Vehicle::create([
            'name' => 'Truck 1',
            'current_mileage' => 120000,
            'status' => 'Available',
        ]);
        $report = MaintenanceReport::create([
            'vehicle_id' => $vehicle->id,
            'completed_by' => 'Jane Mechanic',
            'maintenance_completed' => 'Changed oil.',
            'completed_at' => '2026-05-10 14:30:00',
        ]);

        $response = $this->withSession(['_token' => 'test-token'])
            ->patch(route('reports.update', $report), [
                'completed_by' => 'Sam Mechanic',
                'maintenance_completed' => 'Changed oil and replaced brake pads.',
                'completed_at' => '2026-05-11 09:15:00',
                '_token' => 'test-token',
            ]);

        $response->assertRedirect(route('reports.index'));
        $this->assertDatabaseHas('maintenance_reports', [
            'id' => $report->id,
            'completed_by' => 'Sam Mechanic',
            'maintenance_completed' => 'Changed oil and replaced brake pads.',
            'completed_at' => '2026-05-11 09:15:00',
        ]);
    }

    public function test_maintenance_report_can_be_deleted(): void
    {
        $vehicle = Vehicle::create([
            'name' => 'Truck 1',
            'current_mileage' => 120000,
            'status' => 'Available',
        ]);
        $report = MaintenanceReport::create([
            'vehicle_id' => $vehicle->id,
            'completed_by' => 'Jane Mechanic',
            'maintenance_completed' => 'Changed oil.',
            'completed_at' => '2026-05-10 14:30:00',
        ]);

        $response = $this->withSession(['_token' => 'test-token'])
            ->delete(route('reports.destroy', $report), [
                '_token' => 'test-token',
            ]);

        $response->assertRedirect(route('reports.index'));
        $this->assertNull($report->fresh());
    }
}
