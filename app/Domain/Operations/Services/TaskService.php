<?php

declare(strict_types=1);

namespace App\Domain\Operations\Services;

use App\Domain\Operations\Models\CalendarEvent;
use App\Domain\Operations\Models\Project;
use App\Domain\Operations\Models\Task;
use App\Domain\Operations\Models\TaskChecklist;
use App\Domain\Operations\Models\TaskTimeLog;
use App\Models\Business;
use App\Support\Context;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class TaskService
{
    /**
     * Create a task (quick inline or detailed).
     */
    public function createTask(Business $business, array $data, ?string $userId = null): Task
    {
        $data['business_id'] = $business->id;
        $data['created_by_user_id'] = $userId ?? Context::user()?->id;

        if (! empty($data['assigned_to_user_id']) && $data['assigned_to_user_id'] === 'self') {
            $data['assigned_to_user_id'] = $data['created_by_user_id'];
        }

        $task = Task::create($data);

        // Add checklists if provided
        if (! empty($data['checklists']) && is_array($data['checklists'])) {
            foreach ($data['checklists'] as $idx => $title) {
                if (filled($title)) {
                    TaskChecklist::create([
                        'task_id' => $task->id,
                        'title' => trim((string) $title),
                        'order_column' => $idx,
                    ]);
                }
            }
        }

        if ($task->project_id && $task->project) {
            $task->project->recalculateProgress();
        }

        // Auto create calendar event and sync to user's Google Calendar if requested
        if (! empty($data['sync_to_calendar']) && $task->due_date) {
            try {
                $start = Carbon::parse($task->due_date);
                $end = $start->copy()->addHour();

                $calService = app(CalendarEventService::class);
                $calService->createEvent($business, [
                    'project_id' => $task->project_id,
                    'task_id' => $task->id,
                    'title' => 'Deadline: ' . $task->title,
                    'description' => $task->description ?? 'Tenggat waktu pengerjaan tugas: ' . $task->title,
                    'start_time' => $start,
                    'end_time' => $end,
                    'color_tag' => '#EF4444',
                    'sync_google' => true,
                    'reminders' => [
                        [
                            'channel' => 'in_app',
                            'minutes_before' => 30,
                        ],
                    ],
                ], $data['created_by_user_id']);
            } catch (\Throwable $e) {
                Log::warning('Failed auto-syncing task to calendar', ['error' => $e->getMessage()]);
            }
        }

        return $task->fresh(['project', 'assignee', 'checklists']);
    }

    /**
     * Update task details.
     */
    public function updateTask(Task $task, array $data): Task
    {
        $task->update($data);

        if ($task->project_id && $task->project) {
            $task->project->recalculateProgress();
        }

        return $task->fresh(['project', 'assignee', 'checklists']);
    }

    /**
     * Toggle task completion.
     */
    public function toggleComplete(Task $task): Task
    {
        $task->toggleComplete();

        // Update status on any linked calendar events
        $targetStatus = ($task->status === 'completed') ? 'completed' : 'scheduled';
        CalendarEvent::where('task_id', $task->id)->update(['status' => $targetStatus]);

        return $task;
    }

    /**
     * Add checklist item to task.
     */
    public function addChecklistItem(Task $task, string $title): TaskChecklist
    {
        $maxOrder = $task->checklists()->max('order_column') ?? 0;

        return TaskChecklist::create([
            'task_id' => $task->id,
            'title' => trim($title),
            'order_column' => $maxOrder + 1,
        ]);
    }

    /**
     * Log time for task.
     */
    public function logTime(Task $task, int $minutes, ?string $notes = null, bool $isBillable = false, ?string $userId = null): TaskTimeLog
    {
        $log = TaskTimeLog::create([
            'task_id' => $task->id,
            'user_id' => $userId ?? Context::user()?->id,
            'duration_minutes' => $minutes,
            'notes' => $notes,
            'is_billable' => $isBillable,
        ]);

        // Update actual_hours on task
        $totalMinutes = (int) $task->timeLogs()->sum('duration_minutes');
        $task->actual_hours = round($totalMinutes / 60, 2);
        $task->saveQuietly();

        return $log;
    }

    /**
     * Query tasks with smart filters.
     */
    public function getTasksQuery(Business $business, array $filters = []): Builder
    {
        $query = Task::where('business_id', $business->id)
            ->with(['project', 'assignee', 'creator', 'checklists'])
            ->withCount([
                'checklists',
                'checklists as completed_checklists_count' => function ($q): void {
                    $q->where('is_completed', true);
                },
            ]);

        // Filter tab
        $tab = $filters['tab'] ?? 'all';
        $currentUserId = Context::user()?->id;

        match ($tab) {
            'today' => $query->dueToday()->where('status', '!=', 'completed'),
            'upcoming' => $query->upcoming()->where('status', '!=', 'completed'),
            'high_priority' => $query->highPriority()->where('status', '!=', 'completed'),
            'my_tasks' => $currentUserId ? $query->forUser($currentUserId)->where('status', '!=', 'completed') : $query,
            'completed' => $query->completed(),
            'pending' => $query->pending(),
            default => null, // all
        };

        if (! empty($filters['search'])) {
            $search = '%' . trim((string) $filters['search']) . '%';
            $query->where(function ($q) use ($search): void {
                $q->where('title', 'like', $search)
                    ->orWhere('description', 'like', $search);
            });
        }

        if (! empty($filters['project_id']) && $filters['project_id'] !== 'all') {
            $query->where('project_id', $filters['project_id']);
        }

        if (! empty($filters['priority']) && $filters['priority'] !== 'all') {
            $query->where('priority', $filters['priority']);
        }

        if (! empty($filters['assigned_to']) && $filters['assigned_to'] !== 'all') {
            $query->where('assigned_to_user_id', $filters['assigned_to']);
        }

        return $query->orderBy('due_date', 'asc')->orderBy('priority', 'desc');
    }

    /**
     * Get task metrics.
     */
    public function getTaskMetrics(Business $business): array
    {
        $base = Task::where('business_id', $business->id);

        $totalPending = (clone $base)->whereIn('status', ['pending', 'in_progress'])->count();
        $totalCompleted = (clone $base)->where('status', 'completed')->count();
        $dueToday = (clone $base)->dueToday()->whereIn('status', ['pending', 'in_progress'])->count();
        $overdue = (clone $base)->whereIn('status', ['pending', 'in_progress'])
            ->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->count();

        $currentUserId = Context::user()?->id;
        $myTasks = $currentUserId
            ? (clone $base)->forUser($currentUserId)->whereIn('status', ['pending', 'in_progress'])->count()
            : 0;

        return [
            'total_pending' => $totalPending,
            'total_completed' => $totalCompleted,
            'due_today' => $dueToday,
            'overdue' => $overdue,
            'my_tasks' => $myTasks,
        ];
    }
}
