<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Operations;

use App\Domain\Operations\Models\Project;
use App\Domain\Operations\Services\ProjectService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Operations\StoreProjectRequest;
use App\Http\Requests\Operations\UpdateProjectRequest;
use App\Models\Customer;
use App\Models\User;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectWebController extends Controller
{
    public function __construct(
        protected ProjectService $projectService
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $business = Context::requireBusiness();
        $filters = [
            'search' => $request->query('search'),
            'status' => $request->query('status', 'all'),
            'priority' => $request->query('priority', 'all'),
            'client_id' => $request->query('client_id'),
        ];

        $projects = $this->projectService->getProjectsQuery($business, $filters)->paginate(15)->withQueryString();
        $metrics = $this->projectService->getProjectMetrics($business);
        $clients = Customer::where('business_id', $business->id)->where('is_active', true)->orderBy('name')->get();
        $teamMembers = $business->users()->orderBy('name')->get();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'projects' => $projects,
                'metrics' => $metrics,
            ]);
        }

        return view('app.operations.projects.index', compact('projects', 'metrics', 'clients', 'teamMembers', 'filters'));
    }

    public function store(StoreProjectRequest $request): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();
        $project = $this->projectService->createProject($business, $request->validated());

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => __('operations.project_created_success'),
                'project' => $project,
            ], 201);
        }

        return redirect()->route('operations.projects.show', $project)
            ->with('success', __('operations.project_created_success'));
    }

    public function show(Project $project, Request $request): View|JsonResponse
    {
        $business = Context::requireBusiness();
        if ($project->business_id !== $business->id) {
            abort(404);
        }

        $project->load([
            'client',
            'creator',
            'members',
            'stages.tasks',
            'tasks' => function ($q): void {
                $q->with(['assignee', 'checklists'])->orderBy('order_column');
            },
            'calendarEvents' => function ($q): void {
                $q->latest()->limit(10);
            },
        ]);

        $teamMembers = $business->users()->orderBy('name')->get();
        $clients = Customer::where('business_id', $business->id)->where('is_active', true)->orderBy('name')->get();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['project' => $project]);
        }

        return view('app.operations.projects.show', compact('project', 'teamMembers', 'clients'));
    }

    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();
        if ($project->business_id !== $business->id) {
            abort(404);
        }

        $updated = $this->projectService->updateProject($project, $request->validated());

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => __('operations.project_updated_success'),
                'project' => $updated,
            ]);
        }

        return back()->with('success', __('operations.project_updated_success'));
    }

    public function updateStatus(Request $request, Project $project): JsonResponse
    {
        $business = Context::requireBusiness();
        if ($project->business_id !== $business->id) {
            abort(404);
        }

        $request->validate([
            'status' => ['required', 'string', 'in:planning,in_progress,review,completed,on_hold,cancelled'],
        ]);

        $project = $this->projectService->updateStatus($project, $request->string('status')->value());

        return response()->json([
            'success' => true,
            'message' => __('operations.status_updated_success'),
            'project' => $project,
        ]);
    }

    public function destroy(Project $project, Request $request): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();
        if ($project->business_id !== $business->id) {
            abort(404);
        }

        $project->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => __('operations.project_deleted_success'),
            ]);
        }

        return redirect()->route('operations.projects.index')
            ->with('success', __('operations.project_deleted_success'));
    }
}
