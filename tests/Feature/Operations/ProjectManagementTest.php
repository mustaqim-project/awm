<?php

declare(strict_types=1);

namespace Tests\Feature\Operations;

use App\Domain\Operations\Models\Project;
use App\Domain\Operations\Models\Task;
use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\Customer;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProjectManagementTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $owner;
    private Customer $client;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->seed(RbacSeeder::class);

        $this->owner = User::create([
            'name' => 'Owner Bisnis',
            'email' => 'owner@testoperations.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'phone' => '081234567890',
        ]);

        $this->business = Business::create([
            'name' => 'PT Operasional Sukses',
            'slug' => 'pt-operasional-sukses',
            'email' => 'contact@testoperations.com',
            'phone' => '081234567890',
            'address' => 'Jl. Gatot Subroto No. 10 Jakarta',
            'is_active' => true,
        ]);

        $this->business->users()->attach($this->owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->owner->update(['active_business_id' => $this->business->id]);

        $this->client = Customer::create([
            'business_id' => $this->business->id,
            'name' => 'PT Klien Utama',
            'slug' => 'pt-klien-utama',
            'email' => 'klien@utama.com',
            'phone' => '081987654321',
            'is_active' => true,
        ]);
    }

    public function test_can_view_projects_index(): void
    {
        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('operations.projects.index'));

        $response->assertOk();
        $response->assertSee(__('operations.projects_title'));
    }

    public function test_can_create_project_with_stages_and_client(): void
    {
        $payload = [
            'name' => 'Pengembangan Website E-Commerce',
            'description' => 'Membuat platform toko online UMKM',
            'client_id' => $this->client->id,
            'priority' => 'high',
            'status' => 'planning',
            'budget' => 50000000,
            'start_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'stages' => ['Desain UI/UX', 'Coding Frontend & Backend', 'Uji Coba'],
        ];

        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->post(route('operations.projects.store'), $payload);

        $response->assertRedirect();

        $this->assertDatabaseHas('projects', [
            'business_id' => $this->business->id,
            'name' => 'Pengembangan Website E-Commerce',
            'client_id' => $this->client->id,
            'priority' => 'high',
            'budget' => 50000000,
        ]);

        $project = Project::where('name', 'Pengembangan Website E-Commerce')->first();
        $this->assertNotNull($project);
        $this->assertCount(3, $project->stages);
        $this->assertTrue(str_starts_with($project->code, 'PRJ-'));
    }

    public function test_can_view_project_show_page(): void
    {
        $project = Project::create([
            'business_id' => $this->business->id,
            'name' => 'Project Alpha',
            'code' => 'PRJ-2026-0001',
            'status' => 'in_progress',
            'priority' => 'medium',
            'budget' => 20000000,
        ]);

        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('operations.projects.show', $project));

        $response->assertOk();
        $response->assertSee('Project Alpha');
    }

    public function test_can_update_project_status_via_api(): void
    {
        $project = Project::create([
            'business_id' => $this->business->id,
            'name' => 'Project Beta',
            'code' => 'PRJ-2026-0002',
            'status' => 'planning',
            'priority' => 'medium',
        ]);

        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->patchJson(route('operations.projects.status', $project), [
                'status' => 'in_progress',
            ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->assertEquals('in_progress', $project->fresh()->status);
    }

    public function test_progress_recalculates_when_tasks_are_completed(): void
    {
        $project = Project::create([
            'business_id' => $this->business->id,
            'name' => 'Project Gamma',
            'code' => 'PRJ-2026-0003',
            'status' => 'in_progress',
            'priority' => 'high',
        ]);

        $task1 = Task::create([
            'business_id' => $this->business->id,
            'project_id' => $project->id,
            'title' => 'Tugas 1',
            'status' => 'pending',
        ]);

        $task2 = Task::create([
            'business_id' => $this->business->id,
            'project_id' => $project->id,
            'title' => 'Tugas 2',
            'status' => 'pending',
        ]);

        $project->recalculateProgress();
        $this->assertEquals(0, $project->fresh()->progress_percentage);

        // Mark task1 completed
        $task1->toggleComplete();
        $this->assertEquals(50, $project->fresh()->progress_percentage);

        // Mark task2 completed
        $task2->toggleComplete();
        $this->assertEquals(100, $project->fresh()->progress_percentage);
        $this->assertEquals('completed', $project->fresh()->status);
    }

    public function test_can_delete_project(): void
    {
        $project = Project::create([
            'business_id' => $this->business->id,
            'name' => 'Project to Delete',
            'code' => 'PRJ-2026-0004',
        ]);

        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->delete(route('operations.projects.destroy', $project));

        $response->assertRedirect(route('operations.projects.index'));
        $this->assertSoftDeleted('projects', ['id' => $project->id]);
    }
}
