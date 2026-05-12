<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_delete_another_user(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $driver = User::factory()->create(['role' => User::ROLE_DRIVER]);

        $response = $this->actingAs($admin)
            ->delete(route('users.destroy', $driver));

        $response->assertRedirect(route('users.index'));
        $this->assertNull($driver->fresh());
    }

    public function test_admin_cannot_delete_their_own_account(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $response = $this->actingAs($admin)
            ->from(route('users.index'))
            ->delete(route('users.destroy', $admin));

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHasErrors('user');
        $this->assertNotNull($admin->fresh());
    }

    public function test_manager_cannot_delete_users(): void
    {
        $manager = User::factory()->create(['role' => User::ROLE_MANAGER]);
        $driver = User::factory()->create(['role' => User::ROLE_DRIVER]);

        $this->actingAs($manager)
            ->delete(route('users.destroy', $driver))
            ->assertForbidden();

        $this->assertNotNull($driver->fresh());
    }
}
