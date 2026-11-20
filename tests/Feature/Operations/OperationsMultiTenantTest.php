<?php

declare(strict_types=1);

namespace Tests\Feature\Operations;

use App\Domain\Operations\Models\CalendarEvent;
use App\Domain\Operations\Models\Project;
use App\Domain\Operations\Models\Task;
use App\Models\Business;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OperationsMultiTenantTest extends TestCase
{
    use RefreshDatabase;

    private Business $businessA;
    private Business $businessB;
    private User $ownerA;
    private User $ownerB;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->seed(RbacSeeder::class);

        // Business A & Owner A
        $this->ownerA = User::create([
            'name' => 'Owner Bisnis A',
            'email' => 'ownerA_' . uniqid() . '@tenant.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'phone' => '081234567891',
        ]);
        $this->businessA = Business::create([
            'name' => 'Bisnis Tenant A',
            'slug' => 'bisnis-tenant-a-' . uniqid(),
            'email' => 'contactA_' . uniqid() . '@tenant.com',
            'phone' => '081234567891',
            'address' => 'Jakarta Selatan',
            'is_active' => true,
        ]);
        $this->businessA->users()->attach($this->ownerA->id, ['id' => (string) Str::uuid(), 'role' => 'owner', 'is_active' => true]);
        $this->ownerA->update(['active_business_id' => $this->businessA->id]);

        // Business B & Owner B
        $this->ownerB = User::create([
            'name' => 'Owner Bisnis B',
            'email' => 'ownerB_' . uniqid() . '@tenant.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'phone' => '081234567892',
        ]);
        $this->businessB = Business::create([
            'name' => 'Bisnis Tenant B',
            'slug' => 'bisnis-tenant-b-' . uniqid(),
            'email' => 'contactB_' . uniqid() . '@tenant.com',
            'phone' => '081234567892',
            'address' => 'Surabaya',
            'is_active' => true,
        ]);
        $this->businessB->users()->attach($this->ownerB->id, ['id' => (string) Str::uuid(), 'role' => 'owner', 'is_active' => true]);
        $this->ownerB->update(['active_business_id' => $this->businessB->id]);
    }

    public function test_tenant_b_cannot_view_or_access_tenant_a_project(): void
    {
        $projectA = Project::create([
            'business_id' => $this->businessA->id,
            'name' => 'Proyek Rahasia Tenant A',
            'code' => 'PRJ-A-001',
            'status' => 'in_progress',
        ]);

        // Owner B tries to access Project A -> should 404
        $response = $this->actingAs($this->ownerB)
            ->withSession(['active_business_id' => $this->businessB->id])
            ->get(route('operations.projects.show', $projectA));

        $response->assertNotFound();
    }

    public function test_tenant_b_cannot_update_or_delete_tenant_a_project(): void
    {
        $projectA = Project::create([
            'business_id' => $this->businessA->id,
            'name' => 'Proyek Asli A',
            'code' => 'PRJ-A-002',
        ]);

        // Owner B tries to update Project A
        $updateResponse = $this->actingAs($this->ownerB)
            ->withSession(['active_business_id' => $this->businessB->id])
            ->put(route('operations.projects.update', $projectA), [
                'name' => 'Hacked Project Name',
            ]);
        $updateResponse->assertNotFound();
        $this->assertEquals('Proyek Asli A', $projectA->fresh()->name);

        // Owner B tries to delete Project A
        $deleteResponse = $this->actingAs($this->ownerB)
            ->withSession(['active_business_id' => $this->businessB->id])
            ->delete(route('operations.projects.destroy', $projectA));
        $deleteResponse->assertNotFound();
        $this->assertNull($projectA->fresh()->deleted_at);
    }

    public function test_tenant_b_cannot_access_or_toggle_tenant_a_task(): void
    {
        $taskA = Task::create([
            'business_id' => $this->businessA->id,
            'title' => 'Tugas Internal A',
            'status' => 'pending',
        ]);

        // Owner B tries to toggle Task A
        $response = $this->actingAs($this->ownerB)
            ->withSession(['active_business_id' => $this->businessB->id])
            ->postJson(route('operations.tasks.toggle', $taskA));

        $response->assertNotFound();
        $this->assertEquals('pending', $taskA->fresh()->status);
    }

    public function test_tenant_b_cannot_delete_tenant_a_calendar_event(): void
    {
        $eventA = CalendarEvent::create([
            'business_id' => $this->businessA->id,
            'title' => 'Meeting Khusus A',
            'start_time' => now(),
            'end_time' => now()->addHour(),
        ]);

        $response = $this->actingAs($this->ownerB)
            ->withSession(['active_business_id' => $this->businessB->id])
            ->delete(route('operations.calendar.events.destroy', $eventA));

        $response->assertNotFound();
        $this->assertNull($eventA->fresh()->deleted_at);
    }
}
