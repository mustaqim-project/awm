<?php

declare(strict_types=1);

namespace App\Domain\Operations\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class TaskChecklist
 *
 * Subtask / checklist item di dalam task.
 *
 * @property string $id
 * @property string $task_id
 * @property string $title
 * @property bool $is_completed
 * @property \Carbon\Carbon|null $completed_at
 * @property int $order_column
 */
class TaskChecklist extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'task_checklists';

    protected $fillable = [
        'task_id',
        'title',
        'is_completed',
        'completed_at',
        'order_column',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_completed' => 'boolean',
            'completed_at' => 'datetime',
            'order_column' => 'integer',
        ];
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    public function toggle(): bool
    {
        $this->is_completed = ! $this->is_completed;
        $this->completed_at = $this->is_completed ? now() : null;
        return $this->save();
    }
}
