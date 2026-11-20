<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Operations;

use App\Domain\Operations\Models\CalendarEvent;
use App\Domain\Operations\Models\GoogleCalendarAccount;
use App\Domain\Operations\Models\Project;
use App\Domain\Operations\Models\Task;
use App\Domain\Operations\Services\CalendarEventService;
use App\Domain\Operations\Services\GoogleCalendarSyncService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Operations\StoreCalendarEventRequest;
use App\Http\Requests\Operations\UpdateCalendarEventRequest;
use App\Support\Context;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CalendarWebController extends Controller
{
    public function __construct(
        protected CalendarEventService $eventService,
        protected GoogleCalendarSyncService $googleSyncService
    ) {}

    public function index(Request $request): View
    {
        $business = Context::requireBusiness();
        $user = Context::user();

        // Personal Google Account of currently logged-in user
        $myGoogleAccount = $user ? GoogleCalendarAccount::where('business_id', $business->id)
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->first() : null;

        // Business/Shared Google Account (or fallback)
        $businessGoogleAccount = GoogleCalendarAccount::where('business_id', $business->id)
            ->where('is_active', true)
            ->when($myGoogleAccount, fn($q) => $q->where('id', '!=', $myGoogleAccount->id))
            ->first();

        $googleAccount = $myGoogleAccount ?? $businessGoogleAccount;

        $projects = Project::where('business_id', $business->id)->active()->orderBy('name')->get();
        $tasks = Task::where('business_id', $business->id)->pending()->orderBy('title')->get();
        $teamMembers = $business->users()->orderBy('name')->get();

        return view('app.operations.calendar.index', compact(
            'googleAccount',
            'myGoogleAccount',
            'businessGoogleAccount',
            'projects',
            'tasks',
            'teamMembers'
        ));
    }

    public function eventsJson(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $user = Context::user();

        $startStr = $request->query('start');
        $endStr = $request->query('end');
        $scope = (string) $request->query('scope', 'all'); // 'all', 'mine', 'google'

        $start = $startStr ? Carbon::parse($startStr) : now()->startOfMonth()->subDays(7);
        $end = $endStr ? Carbon::parse($endStr) : now()->endOfMonth()->addDays(7);

        $events = $this->eventService->getEventsForRange($business, $start, $end, $user?->id, $scope);

        $formatted = $events->map(function (CalendarEvent $event) use ($user) {
            $isMine = ($user && $event->created_by_user_id === $user->id);
            return [
                'id' => $event->id,
                'title' => $event->title,
                'description' => $event->description,
                'location' => $event->location,
                'meeting_link' => $event->meeting_link,
                'start' => $event->start_time->toIso8601String(),
                'end' => $event->end_time->toIso8601String(),
                'is_all_day' => (bool) $event->is_all_day,
                'color' => $event->color_tag ?: '#007AFF',
                'status' => $event->status,
                'project_name' => $event->project?->name,
                'project_id' => $event->project_id,
                'task_title' => $event->task?->title,
                'task_id' => $event->task_id,
                'is_google_synced' => ! empty($event->google_event_id),
                'is_mine' => $isMine,
                'creator_name' => $event->creator?->name ?? 'Tim',
                'google_html_link' => $event->google_html_link,
                'reminders' => $event->reminders->map(fn($r) => [
                    'id' => $r->id,
                    'channel' => $r->channel,
                    'minutes_before' => $r->minutes_before,
                    'status' => $r->status,
                ]),
            ];
        });

        return response()->json($formatted);
    }

    public function store(StoreCalendarEventRequest $request): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();
        $event = $this->eventService->createEvent($business, $request->validated());

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => __('operations.event_created_success'),
                'event' => $event,
            ], 201);
        }

        return back()->with('success', __('operations.event_created_success'));
    }

    public function update(UpdateCalendarEventRequest $request, CalendarEvent $event): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();
        if ($event->business_id !== $business->id) {
            abort(404);
        }

        $updated = $this->eventService->updateEvent($event, $request->validated());

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => __('operations.event_updated_success'),
                'event' => $updated,
            ]);
        }

        return back()->with('success', __('operations.event_updated_success'));
    }

    public function destroy(CalendarEvent $event, Request $request): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();
        if ($event->business_id !== $business->id) {
            abort(404);
        }

        $this->eventService->deleteEvent($event);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => __('operations.event_deleted_success'),
            ]);
        }

        return back()->with('success', __('operations.event_deleted_success'));
    }

    public function settings(Request $request): View
    {
        $business = Context::requireBusiness();
        $user = Context::user();

        // 1. My personal account
        $myGoogleAccount = $user ? GoogleCalendarAccount::where('business_id', $business->id)
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->first() : null;

        // 2. All team accounts in this business
        $allAccounts = GoogleCalendarAccount::where('business_id', $business->id)
            ->where('is_active', true)
            ->with('user')
            ->get();

        $googleAccount = $myGoogleAccount ?? $allAccounts->first();
        $authUrl = $this->googleSyncService->getAuthUrl($business);

        return view('app.operations.calendar.settings', compact('googleAccount', 'myGoogleAccount', 'allAccounts', 'authUrl'));
    }
}
