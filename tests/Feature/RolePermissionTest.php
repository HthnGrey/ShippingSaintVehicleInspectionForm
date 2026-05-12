<?php

namespace Tests\Feature;

use App\Models\MaintenanceReport;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_users_are_redirected_to_login_for_protected_pages(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_admin_can_access_everything(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $vehicle = Vehicle::create([
            'name' => 'Truck 1',
            'current_mileage' => 100,
            'required_maintenance_mileage' => 1000,
            'status' => 'Maintenance Required',
        ]);
        $report = MaintenanceReport::create([
            'vehicle_id' => $vehicle->id,
            'completed_by' => 'Jane Mechanic',
            'maintenance_completed' => 'Changed oil.',
            'completed_at' => now(),
        ]);

        $this->actingAs($admin);

        $this->get(route('dashboard'))->assertOk();
        $this->get(route('users.index'))->assertOk();
        $this->get(route('vehicles.index'))->assertOk();
        $this->get(route('work-orders.index'))->assertOk();
        $this->get(route('driver-mileage.index'))->assertOk();
        $this->get(route('driver-history.index'))->assertOk();
        $this->get(route('audit-logs.index'))->assertOk();
        $this->get(route('inspections.index'))->assertOk();
        $this->get(route('inspections.pre'))->assertOk();
        $this->get(route('inspections.post'))->assertOk();
        $this->get(route('reports.index'))->assertOk();
        $this->patch(route('reports.update', $report), [
            'completed_by' => 'Sam Mechanic',
            'maintenance_completed' => 'Changed oil and filters.',
            'completed_at' => now()->format('Y-m-d H:i:s'),
        ])->assertRedirect(route('reports.index'));
    }

    public function test_manager_can_read_reports_create_and_read_vehicles_and_create_inspections(): void
    {
        $manager = User::factory()->create(['role' => User::ROLE_MANAGER]);
        $vehicle = Vehicle::create([
            'name' => 'Truck 1',
            'current_mileage' => 100,
            'required_maintenance_mileage' => 1000,
            'status' => 'Available',
        ]);
        $report = MaintenanceReport::create([
            'vehicle_id' => $vehicle->id,
            'completed_by' => 'Jane Mechanic',
            'maintenance_completed' => 'Changed oil.',
            'completed_at' => now(),
        ]);

        $this->actingAs($manager);

        $this->get(route('dashboard'))->assertOk();
        $this->get(route('vehicles.index'))->assertOk();
        $this->post(route('vehicles.store'), [
            'name' => 'Truck 2',
            'plate_number' => 'ABC123',
            'current_mileage' => 100,
            'required_maintenance_mileage' => 1000,
            'notes' => null,
        ])->assertRedirect(route('vehicles.index'));
        $this->get(route('inspections.pre'))->assertOk();
        $this->get(route('inspections.post'))->assertOk();
        $this->get(route('reports.index'))->assertOk();

        $this->get(route('users.index'))->assertForbidden();
        $this->get(route('work-orders.index'))->assertOk();
        $this->get(route('driver-mileage.index'))->assertOk();
        $this->get(route('driver-history.index'))->assertOk();
        $this->get(route('audit-logs.index'))->assertForbidden();
        $this->get(route('inspections.index'))->assertForbidden();
        $this->patch(route('reports.update', $report), [
            'completed_by' => 'Sam Mechanic',
            'maintenance_completed' => 'Changed oil and filters.',
            'completed_at' => now()->format('Y-m-d H:i:s'),
        ])->assertForbidden();
    }

    public function test_driver_can_only_create_pre_and_post_trip_inspections(): void
    {
        $driver = User::factory()->create(['role' => User::ROLE_DRIVER]);

        $this->actingAs($driver);

        $this->get(route('dashboard'))->assertOk();
        $this->get(route('driver-history.index'))->assertOk();
        $this->get(route('inspections.pre'))->assertOk();
        $this->get(route('inspections.post'))->assertOk();

        $this->get(route('users.index'))->assertForbidden();
        $this->get(route('vehicles.index'))->assertForbidden();
        $this->get(route('work-orders.index'))->assertForbidden();
        $this->get(route('driver-mileage.index'))->assertForbidden();
        $this->get(route('audit-logs.index'))->assertForbidden();
        $this->get(route('inspections.index'))->assertForbidden();
        $this->get(route('reports.index'))->assertForbidden();
    }
}
