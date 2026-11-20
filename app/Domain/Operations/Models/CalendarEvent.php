<?php

declare(strict_types=1);

namespace App\Domain\Operations\Models;

use App\Models\Business;
use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class CalendarEvent
 *
 * Model representasi kegiatan kalender, jadwal, meeting, pengingat, dan sinkronisasi Google Calendar.
 *
 * @property string $id
 * @property string $business_id
 * @property string|null $created_by_user_id
 * @property string|null $project_id
 * @property string|null $task_id
 * @property string|null $google_calendar_account_id
 * @property string $title
 * @property string|null $description
 * @property string|null $location
 * @property string|null $meeting_link
 * @property \Carbon\Carbon $start_time
 * @property \Carbon\Carbon $end_time
 * @property bool $is_all_day
 * @property string $color_tag
 * @property string $status
 * @property string|null $google_event_id
 * @property string|null $google_calendar_id
 * @property string|null $google_etag
 * @property string|null $google_html_link
 * @property \Carbon\Carbon|null $last_synced_at
 * @property string $sync_status
 * @property string|null $sync_error_message
 * @property array|null $metadata
 */
class CalendarEvent extends Model
{
    use HasFactory, HasUuid, BelongsToBusiness, SoftDeletes;

    protected $table = 'calendar_events';

    protected $fillable = [
        'business_id',
        'created_by_user_id',
        'project_id',
        'task_id',
        'google_calendar_account_id',
        'title',
        'description',
        'location',
        'meeting_link',
        'start_time',
        'end_time',
        'is_all_day',
        'color_tag',
        'status',
        'google_event_id',
        'google_calendar_id',
        'google_etag',
        'google_html_link',
        'last_synced_at',
        'sync_status',
        'sync_error_message',
        'metadata',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'last_synced_at' => 'datetime',
            'is_all_day' => 'boolean',
            'metadata' => 'array',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    public function googleAccount(): BelongsTo
    {
        return $this->belongsTo(GoogleCalendarAccount::class, 'google_calendar_account_id');
    }

    public function reminders(): HasMany
    {
        return $this->hasMany(EventReminder::class, 'calendar_event_id');
    }

    public function scopeBetween(Builder $query, $start, $end): Builder
    {
        return $query->where(function (Builder $q) use ($start, $end): void {
            $q->whereBetween('start_time', [$start, $end])
                ->orWhereBetween('end_time', [$start, $end])
                ->orWhere(function (Builder $sub) use ($start, $end): void {
                    $sub->where('start_time', '<=', $start)
                        ->where('end_time', '>=', $end);
                });
        });
    }

    public function scopeScheduled(Builder $query): Builder
    {
        return $query->where('status', 'scheduled');
    }
}
