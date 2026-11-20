<?php

declare(strict_types=1);

namespace App\Domain\Operations\Services;

use App\Domain\Operations\Models\CalendarEvent;
use App\Domain\Operations\Models\EventReminder;
use App\Domain\Operations\Models\GoogleCalendarAccount;
use App\Models\Business;
use App\Support\Context;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CalendarEventService
{
    public function __construct(
        protected GoogleCalendarSyncService $googleSyncService
    ) {}

    /**
     * Create a calendar event and configure automatic reminders.
     */
    public function createEvent(Business $business, array $data, ?string $userId = null): CalendarEvent
    {
        $data['business_id'] = $business->id;
        $data['created_by_user_id'] = $userId ?? Context::user()?->id;

        if (empty($data['color_tag'])) {
            $data['color_tag'] = '#007AFF';
        }

        $event = CalendarEvent::create($data);

        // Process Reminders
        if (! empty($data['reminders']) && is_array($data['reminders'])) {
            foreach ($data['reminders'] as $reminderData) {
                if (is_array($reminderData) && ! empty($reminderData['channel'])) {
                    $minutes = (int) ($reminderData['minutes_before'] ?? 15);
                    $remindAt = Carbon::parse($event->start_time)->subMinutes($minutes);

                    EventReminder::create([
                        'calendar_event_id' => $event->id,
                        'channel' => $reminderData['channel'], // whatsapp, email, in_app
                        'minutes_before' => $minutes,
                        'remind_at' => $remindAt,
                        'recipient_phone' => $reminderData['recipient_phone'] ?? Context::user()?->phone,
                        'recipient_email' => $reminderData['recipient_email'] ?? Context::user()?->email,
                        'recipient_user_id' => Context::user()?->id,
                        'status' => 'pending',
                    ]);
                }
            }
        }

        // Auto push to Google Calendar if requested or enabled
        if (! empty($data['sync_google'])) {
            $account = null;
            if (! empty($data['created_by_user_id'])) {
                $account = GoogleCalendarAccount::withoutGlobalScopes()
                    ->where('business_id', $business->id)
                    ->where('user_id', $data['created_by_user_id'])
                    ->where('is_active', true)
                    ->first();
            }

            if (! $account) {
                $account = GoogleCalendarAccount::withoutGlobalScopes()
                    ->where('business_id', $business->id)
                    ->where('is_active', true)
                    ->first();
            }

            if ($account) {
                $event->google_calendar_account_id = $account->id;
                $event->saveQuietly();
                $this->googleSyncService->pushEvent($event);
            }
        }

        return $event->fresh(['project', 'task', 'reminders']);
    }

    /**
     * Update an event and refresh Google sync.
     */
    public function updateEvent(CalendarEvent $event, array $data): CalendarEvent
    {
        $event->update($data);

        // Update reminders if provided
        if (isset($data['reminders']) && is_array($data['reminders'])) {
            $event->reminders()->delete();

            foreach ($data['reminders'] as $reminderData) {
                if (is_array($reminderData) && ! empty($reminderData['channel'])) {
                    $minutes = (int) ($reminderData['minutes_before'] ?? 15);
                    $remindAt = Carbon::parse($event->start_time)->subMinutes($minutes);

                    EventReminder::create([
                        'calendar_event_id' => $event->id,
                        'channel' => $reminderData['channel'],
                        'minutes_before' => $minutes,
                        'remind_at' => $remindAt,
                        'recipient_phone' => $reminderData['recipient_phone'] ?? Context::user()?->phone,
                        'recipient_email' => $reminderData['recipient_email'] ?? Context::user()?->email,
                        'recipient_user_id' => Context::user()?->id,
                        'status' => 'pending',
                    ]);
                }
            }
        }

        // Push to Google if synced or requested
        if ($event->google_event_id || ! empty($data['sync_google'])) {
            $this->googleSyncService->pushEvent($event);
        }

        return $event->fresh(['project', 'task', 'reminders']);
    }

    /**
     * Delete an event and remove from Google Calendar.
     */
    public function deleteEvent(CalendarEvent $event): bool
    {
        if ($event->google_event_id) {
            $this->googleSyncService->deleteRemoteEvent($event);
        }

        return (bool) $event->delete();
    }

    /**
     * Query events for a specific month or date range with optional scope filter.
     */
    public function getEventsForRange(Business $business, Carbon $start, Carbon $end, ?string $userId = null, string $scope = 'all'): Collection
    {
        $query = CalendarEvent::where('business_id', $business->id)
            ->where(function (Builder $query) use ($start, $end): void {
                $query->whereBetween('start_time', [$start, $end])
                    ->orWhereBetween('end_time', [$start, $end])
                    ->orWhere(function (Builder $sub) use ($start, $end): void {
                        $sub->where('start_time', '<=', $start)
                            ->where('end_time', '>=', $end);
                    });
            });

        if ($scope === 'mine' && $userId) {
            $query->where('created_by_user_id', $userId);
        } elseif ($scope === 'google') {
            $query->whereNotNull('google_event_id');
            if ($userId) {
                $query->where('created_by_user_id', $userId);
            }
        }

        return $query->with(['project', 'task', 'reminders', 'creator'])
            ->orderBy('start_time', 'asc')
            ->get();
    }
}
