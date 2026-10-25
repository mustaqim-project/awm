<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminActiveStatusMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(bool $isActive = true): Admin
    {
        return Admin::factory()->create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => Hash::make('secret123'),
            'role' => 'super_admin',
            'is_active' => $isActive,
        ]);
    }

    public function test_active_admin_can_access_admin_portal(): void
    {
        $admin = $this->makeAdmin(isActive: true);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.profile.index'));

        $response->assertStatus(200);
        $this->assertAuthenticatedAs($admin, 'admin');
    }

    public function test_deactivated_admin_session_is_terminated_immediately(): void
    {
        $admin = $this->makeAdmin(isActive: false);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.profile.index'));

        $response->assertRedirect(route('admin.login'));
        $response->assertSessionHas('error');
        $this->assertGuest('admin');
    }
}
