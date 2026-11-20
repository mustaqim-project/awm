<?php

declare(strict_types=1);

namespace App\Domain\Operations\Models;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Project
 *
 * Model representasi manajemen proyek berskala enterprise dengan multi-tenant isolation.
 *
 * @property string $id
 * @property string $business_id
 * @property string|null $created_by_user_id
 * @property string|null $client_id
 * @property string $name
 * @property string $code
 * @property string|null $description
 * @property string $status
 * @property string $priority
 * @property float $budget
 * @property float $spent
 * @property int $progress_percentage
 * @property \Carbon\Carbon|null $start_date
 * @property \Carbon\Carbon|null $due_date
 * @property \Carbon\Carbon|null $completed_at
 * @property string $color
 * @property array|null $metadata
 */
class Project extends Model
{
    use HasFactory, HasUuid, BelongsToBusiness, SoftDeletes;

    protected $table = 'projects';

    protected $fillable = [
        'business_id',
        'created_by_user_id',
        'client_id',
        'name',
        'code',
        'description',
        'status',
        'priority',
        'budget',
        'spent',
        'progress_percentage',
        'start_date',
        'due_date',
        'completed_at',
        'color',
        'metadata',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'budget' => 'decimal:2',
            'spent' => 'decimal:2',
            'progress_percentage' => 'integer',
            'start_date' => 'date',
            'due_date' => 'date',
            'completed_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'client_id');
    }

    public function stages(): HasMany
    {
        return $this->hasMany(ProjectStage::class, 'project_id')->orderBy('order_column');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_members', 'project_id', 'user_id')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'project_id')->orderBy('order_column');
    }

    public function calendarEvents(): HasMany
    {
        return $this->hasMany(CalendarEvent::class, 'project_id');
    }

    /**
     * Recalculate and update the project's progress percentage automatically based on completed tasks.
     */
    public function recalculateProgress(): void
    {
        $total = $this->tasks()->count();
        if ($total === 0) {
            return;
        }

        $completed = $this->tasks()->where('status', 'completed')->count();
        $this->progress_percentage = (int) round(($completed / $total) * 100);

        if ($this->progress_percentage === 100 && $this->status !== 'completed') {
            $this->status = 'completed';
            $this->completed_at = now();
        }

        $this->saveQuietly();
    }

    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast() && $this->status !== 'completed' && $this->status !== 'cancelled';
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', ['planning', 'in_progress', 'review']);
    }
}
