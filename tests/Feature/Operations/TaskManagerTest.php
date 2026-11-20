<?php

declare(strict_types=1);

namespace Tests\Feature\Operations;

use App\Domain\Operations\Models\Project;
use App\Domain\Operations\Models\Task;
use App\Domain\Operations\Models\TaskChecklist;
use App\Models\Business;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TaskManagerTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $owner;
    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->seed(RbacSeeder::class);

        $this->owner = User::create([
            'name' => 'Owner Task Test',
            'email' => 'taskowner_' . uniqid() . '@test.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'phone' => '081234567890',
        ]);

        $this->business = Business::create([
            'name' => 'Bisnis Todo Test',
            'slug' => 'bisnis-todo-test-' . uniqid(),
            'email' => 'contact_' . uniqid() . '@test.com',
            'phone' => '081234567890',
            'address' => 'Jl. Kebon Jeruk No. 5 Jakarta',
            'is_active' => true,
        ]);

        $this->business->users()->attach($this->owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->owner->update(['active_business_id' => $this->business->id]);

        $this->project = Project::create([
            'business_id' => $this->business->id,
            'name' => 'Proyek Tugas Inti',
            'code' => 'PRJ-2026-TASK',
            'status' => 'in_progress',
        ]);
    }

    public function test_can_view_tasks_index(): void
    {
        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('operations.tasks.index'));

        $response->assertOk();
        $response->assertSee(__('operations.tasks_title'));
    }

    public function test_can_create_task_inline_and_detailed(): void
    {
        $payload = [
            'title' => 'Menyusun Laporan Keuangan Bulanan',
            'description' => 'Merekapitulasi seluruh bukti potong pajak dan nota kas',
            'project_id' => $this->project->id,
            'priority' => 'high',
            'status' => 'pending',
            'due_date' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'estimated_hours' => 4.5,
            'checklists' => [
                'Kumpulkan mutasi rekening koran',
                'Rekonsiliasi jurnal pengeluaran',
            ],
        ];

        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->post(route('operations.tasks.store'), $payload);

        $response->assertRedirect();

        $this->assertDatabaseHas('tasks', [
            'business_id' => $this->business->id,
            'title' => 'Menyusun Laporan Keuangan Bulanan',
            'project_id' => $this->project->id,
            'priority' => 'high',
        ]);

        $task = Task::where('title', 'Menyusun Laporan Keuangan Bulanan')->first();
        $this->assertNotNull($task);
        $this->assertCount(2, $task->checklists);
    }

    public function test_can_toggle_task_completion_status(): void
    {
        $task = Task::create([
            'business_id' => $this->business->id,
            'project_id' => $this->project->id,
            'title' => 'Review Desain Spanduk Promo',
            'status' => 'pending',
        ]);

        // Toggle to completed
        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('operations.tasks.toggle', $task));

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertEquals('completed', $task->fresh()->status);
        $this->assertNotNull($task->fresh()->completed_at);

        // Toggle back to pending
        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('operations.tasks.toggle', $task));

        $response->assertOk();
        $this->assertEquals('pending', $task->fresh()->status);
        $this->assertNull($task->fresh()->completed_at);
    }

    public function test_can_add_subtask_checklist_and_toggle(): void
    {
        $task = Task::create([
            'business_id' => $this->business->id,
            'title' => 'Tugas Utama',
            'status' => 'pending',
        ]);

        // Add checklist item
        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('operations.tasks.checklists.store', $task), [
                'title' => 'Subtask A',
            ]);

        $response->assertCreated();
        $this->assertDatabaseHas('task_checklists', [
            'task_id' => $task->id,
            'title' => 'Subtask A',
            'is_completed' => false,
        ]);

        $checklist = TaskChecklist::where('task_id', $task->id)->first();
        $this->assertNotNull($checklist);

        // Toggle checklist item
        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('operations.tasks.checklists.toggle', $checklist));

        $response->assertOk();
        $this->assertTrue($checklist->fresh()->is_completed);
    }

    public function test_can_log_work_time_on_task(): void
    {
        $task = Task::create([
            'business_id' => $this->business->id,
            'title' => 'Pekerjaan Desain Kemasan',
            'status' => 'in_progress',
        ]);

        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('operations.tasks.time-logs.store', $task), [
                'duration_minutes' => 90,
                'notes' => 'Mengerjakan mockup 3D botol kemasan',
                'is_billable' => true,
            ]);

        $response->assertCreated();
        $this->assertDatabaseHas('task_time_logs', [
            'task_id' => $task->id,
            'duration_minutes' => 90,
            'is_billable' => true,
        ]);

        $this->assertEquals(1.5, (float) $task->fresh()->actual_hours);
    }

    public function test_can_delete_task(): void
    {
        $task = Task::create([
            'business_id' => $this->business->id,
            'title' => 'Tugas yang Dihapus',
        ]);

        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->delete(route('operations.tasks.destroy', $task));

        $response->assertRedirect();
        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
    }
}
