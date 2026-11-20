<?php

declare(strict_types=1);

namespace App\Domain\Operations\Models;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class ProjectStage
 *
 * Tahapan atau milestone dalam sebuah proyek.
 *
 * @property string $id
 * @property string $project_id
 * @property string $business_id
 * @property string $name
 * @property int $order_column
 * @property string $status
 * @property \Carbon\Carbon|null $due_date
 */
class ProjectStage extends Model
{
    use HasFactory, HasUuid, BelongsToBusiness;

    protected $table = 'project_stages';

    protected $fillable = [
        'project_id',
        'business_id',
        'name',
        'order_column',
        'status',
        'due_date',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'order_column' => 'integer',
            'due_date' => 'date',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'project_stage_id')->orderBy('order_column');
    }
}
