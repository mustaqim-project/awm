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
 * Class Task
 *
 * Model representasi task / todo item dengan checklist terperinci dan isolasi multi-tenant.
 *
 * @property string $id
 * @property string $business_id
 * @property string|null $project_id
 * @property string|null $project_stage_id
 * @property string|null $created_by_user_id
 * @property string|null $assigned_to_user_id
 * @property string $title
 * @property string|null $description
 * @property string $status
 * @property string $priority
 * @property \Carbon\Carbon|null $due_date
 * @property \Carbon\Carbon|null $completed_at
 * @property float|null $estimated_hours
 * @property float|null $actual_hours
 * @property int $order_column
 * @property bool $is_milestone
 * @property array|null $metadata
 */
class Task extends Model
{
    use HasFactory, HasUuid, BelongsToBusiness, SoftDeletes;

    protected $table = 'tasks';

    protected $fillable = [
        'business_id',
        'project_id',
        'project_stage_id',
        'created_by_user_id',
        'assigned_to_user_id',
        'title',
        'description',
        'status',
        'priority',
        'due_date',
        'completed_at',
        'estimated_hours',
        'actual_hours',
        'order_column',
        'is_milestone',
        'metadata',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'due_date' => 'datetime',
            'completed_at' => 'datetime',
            'estimated_hours' => 'decimal:2',
            'actual_hours' => 'decimal:2',
            'order_column' => 'integer',
            'is_milestone' => 'boolean',
            'metadata' => 'array',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(ProjectStage::class, 'project_stage_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function checklists(): HasMany
    {
        return $this->hasMany(TaskChecklist::class, 'task_id')->orderBy('order_column');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TaskAttachment::class, 'task_id')->latest();
    }

    public function timeLogs(): HasMany
    {
        return $this->hasMany(TaskTimeLog::class, 'task_id')->latest();
    }

    public function calendarEvents(): HasMany
    {
        return $this->hasMany(CalendarEvent::class, 'task_id');
    }

    /**
     * Mark task as completed or toggle back to pending.
     */
    public function toggleComplete(): bool
    {
        if ($this->status === 'completed') {
            $this->status = 'pending';
            $this->completed_at = null;
        } else {
            $this->status = 'completed';
            $this->completed_at = now();
        }

        $saved = $this->save();

        if ($this->project_id && $this->project) {
            $this->project->recalculateProgress();
        }

        return $saved;
    }

    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast() && $this->status !== 'completed' && $this->status !== 'cancelled';
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->whereIn('status', ['pending', 'in_progress']);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    public function scopeDueToday(Builder $query): Builder
    {
        return $query->whereDate('due_date', today());
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->whereBetween('due_date', [today(), today()->addDays(7)]);
    }

    public function scopeHighPriority(Builder $query): Builder
    {
        return $query->whereIn('priority', ['high', 'urgent']);
    }

    public function scopeForUser(Builder $query, string $userId): Builder
    {
        return $query->where('assigned_to_user_id', $userId);
    }
}
