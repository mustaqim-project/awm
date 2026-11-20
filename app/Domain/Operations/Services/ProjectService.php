<?php

declare(strict_types=1);

namespace App\Domain\Operations\Services;

use App\Domain\Operations\Models\Project;
use App\Domain\Operations\Models\ProjectStage;
use App\Models\Business;
use App\Support\Context;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class ProjectService
{
    /**
     * Generate unique project code.
     */
    public function generateCode(Business $business): string
    {
        $prefix = 'PRJ-' . date('Y');
        $count = Project::where('business_id', $business->id)
            ->where('code', 'like', "{$prefix}%")
            ->count();

        $sequence = str_pad((string) ($count + 1), 4, '0', STR_PAD_LEFT);
        return "{$prefix}-{$sequence}";
    }

    /**
     * Create a new project.
     */
    public function createProject(Business $business, array $data, ?string $userId = null): Project
    {
        if (empty($data['code'])) {
            $data['code'] = $this->generateCode($business);
        }

        $data['business_id'] = $business->id;
        $data['created_by_user_id'] = $userId ?? Context::user()?->id;

        $project = Project::create($data);

        // Assign default stages if provided
        if (! empty($data['stages']) && is_array($data['stages'])) {
            foreach ($data['stages'] as $index => $stageName) {
                ProjectStage::create([
                    'project_id' => $project->id,
                    'business_id' => $business->id,
                    'name' => trim((string) $stageName),
                    'order_column' => $index,
                ]);
            }
        }

        // Assign members if provided
        if (! empty($data['member_ids']) && is_array($data['member_ids'])) {
            $project->members()->sync($data['member_ids']);
        }

        return $project;
    }

    /**
     * Update an existing project.
     */
    public function updateProject(Project $project, array $data): Project
    {
        $project->update($data);

        if (isset($data['member_ids']) && is_array($data['member_ids'])) {
            $project->members()->sync($data['member_ids']);
        }

        $project->recalculateProgress();

        return $project->fresh(['client', 'stages', 'members', 'tasks']);
    }

    /**
     * Update project status directly (e.g. from Kanban drag-and-drop).
     */
    public function updateStatus(Project $project, string $status): Project
    {
        $project->status = $status;
        if ($status === 'completed' && ! $project->completed_at) {
            $project->completed_at = now();
            $project->progress_percentage = 100;
        }
        $project->save();

        return $project;
    }

    /**
     * Get paginated and filtered projects.
     */
    public function getProjectsQuery(Business $business, array $filters = []): Builder
    {
        $query = Project::where('business_id', $business->id)
            ->with(['client', 'creator', 'members'])
            ->withCount([
                'tasks',
                'tasks as completed_tasks_count' => function ($q): void {
                    $q->where('status', 'completed');
                },
            ]);

        if (! empty($filters['search'])) {
            $search = '%' . trim((string) $filters['search']) . '%';
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', $search)
                    ->orWhere('code', 'like', $search)
                    ->orWhere('description', 'like', $search);
            });
        }

        if (! empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['priority']) && $filters['priority'] !== 'all') {
            $query->where('priority', $filters['priority']);
        }

        if (! empty($filters['client_id'])) {
            $query->where('client_id', $filters['client_id']);
        }

        return $query->latest();
    }

    /**
     * Get summary metrics for the business projects.
     */
    public function getProjectMetrics(Business $business): array
    {
        $base = Project::where('business_id', $business->id);

        $totalActive = (clone $base)->whereIn('status', ['planning', 'in_progress', 'review'])->count();
        $totalCompleted = (clone $base)->where('status', 'completed')->count();
        $overdue = (clone $base)->whereIn('status', ['planning', 'in_progress', 'review'])
            ->whereNotNull('due_date')
            ->where('due_date', '<', today())
            ->count();

        $totalBudget = (float) (clone $base)->sum('budget');
        $totalSpent = (float) (clone $base)->sum('spent');

        return [
            'total_active' => $totalActive,
            'total_completed' => $totalCompleted,
            'overdue' => $overdue,
            'total_budget' => $totalBudget,
            'total_spent' => $totalSpent,
        ];
    }
}
