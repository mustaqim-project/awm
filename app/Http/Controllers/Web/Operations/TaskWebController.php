<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Operations;

use App\Domain\Operations\Models\Project;
use App\Domain\Operations\Models\Task;
use App\Domain\Operations\Models\TaskChecklist;
use App\Domain\Operations\Services\TaskService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Operations\StoreTaskRequest;
use App\Http\Requests\Operations\UpdateTaskRequest;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskWebController extends Controller
{
    public function __construct(
        protected TaskService $taskService
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $business = Context::requireBusiness();
        $filters = [
            'tab' => $request->query('tab', 'all'),
            'search' => $request->query('search'),
            'project_id' => $request->query('project_id', 'all'),
            'priority' => $request->query('priority', 'all'),
            'assigned_to' => $request->query('assigned_to', 'all'),
        ];

        $tasks = $this->taskService->getTasksQuery($business, $filters)->paginate(25)->withQueryString();
        $metrics = $this->taskService->getTaskMetrics($business);
        $projects = Project::where('business_id', $business->id)->active()->orderBy('name')->get();
        $teamMembers = $business->users()->orderBy('name')->get();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'tasks' => $tasks,
                'metrics' => $metrics,
            ]);
        }

        return view('app.operations.tasks.index', compact('tasks', 'metrics', 'projects', 'teamMembers', 'filters'));
    }

    public function store(StoreTaskRequest $request): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();
        $task = $this->taskService->createTask($business, $request->validated());

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => __('operations.task_created_success'),
                'task' => $task,
            ], 201);
        }

        return back()->with('success', __('operations.task_created_success'));
    }

    public function show(Task $task, Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        if ($task->business_id !== $business->id) {
            abort(404);
        }

        $task->load(['project', 'assignee', 'creator', 'checklists', 'timeLogs.user', 'attachments']);

        return response()->json([
            'success' => true,
            'task' => $task,
        ]);
    }

    public function update(UpdateTaskRequest $request, Task $task): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();
        if ($task->business_id !== $business->id) {
            abort(404);
        }

        $updated = $this->taskService->updateTask($task, $request->validated());

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => __('operations.task_updated_success'),
                'task' => $updated,
            ]);
        }

        return back()->with('success', __('operations.task_updated_success'));
    }

    public function toggleComplete(Task $task, Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        if ($task->business_id !== $business->id) {
            abort(404);
        }

        $updated = $this->taskService->toggleComplete($task);

        return response()->json([
            'success' => true,
            'message' => $updated->status === 'completed'
                ? __('operations.task_completed_success')
                : __('operations.task_reopened_success'),
            'task' => $updated->fresh(['project']),
        ]);
    }

    public function storeChecklist(Request $request, Task $task): JsonResponse
    {
        $business = Context::requireBusiness();
        if ($task->business_id !== $business->id) {
            abort(404);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

        $item = $this->taskService->addChecklistItem($task, $validated['title']);

        return response()->json([
            'success' => true,
            'checklist' => $item,
        ], 201);
    }

    public function toggleChecklist(TaskChecklist $checklist): JsonResponse
    {
        $task = $checklist->task;
        $business = Context::requireBusiness();
        if (! $task || $task->business_id !== $business->id) {
            abort(404);
        }

        $checklist->toggle();

        return response()->json([
            'success' => true,
            'checklist' => $checklist,
        ]);
    }

    public function storeTimeLog(Request $request, Task $task): JsonResponse
    {
        $business = Context::requireBusiness();
        if ($task->business_id !== $business->id) {
            abort(404);
        }

        $validated = $request->validate([
            'duration_minutes' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
            'is_billable' => ['nullable', 'boolean'],
        ]);

        $log = $this->taskService->logTime(
            $task,
            (int) $validated['duration_minutes'],
            $validated['notes'] ?? null,
            (bool) ($validated['is_billable'] ?? false)
        );

        return response()->json([
            'success' => true,
            'time_log' => $log->load('user'),
            'actual_hours' => $task->fresh()->actual_hours,
        ], 201);
    }

    public function destroy(Task $task, Request $request): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();
        if ($task->business_id !== $business->id) {
            abort(404);
        }

        $project = $task->project;
        $task->delete();

        if ($project) {
            $project->recalculateProgress();
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => __('operations.task_deleted_success'),
            ]);
        }

        return back()->with('success', __('operations.task_deleted_success'));
    }
}
