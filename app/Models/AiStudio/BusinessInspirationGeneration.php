<?php

declare(strict_types=1);

namespace App\Models\AiStudio;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class BusinessInspirationGeneration
 *
 * @property string $id
 * @property string $business_id
 * @property string $project_id
 * @property string $prompt_version_id
 * @property string|null $user_id
 * @property string $provider
 * @property string $model_name
 * @property string $status
 * @property int $credit_cost
 * @property string|null $idempotency_key
 * @property int $batch_count
 * @property string|null $error_message
 * @property string|null $provider_job_id
 * @property int|null $provider_latency_ms
 * @property array|null $generation_meta
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class BusinessInspirationGeneration extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid;

    protected $table = 'business_inspiration_generations';

    protected $fillable = [
        'business_id',
        'project_id',
        'prompt_version_id',
        'user_id',
        'provider',
        'model_name',
        'status',
        'credit_cost',
        'idempotency_key',
        'batch_count',
        'error_message',
        'provider_job_id',
        'provider_latency_ms',
        'generation_meta',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'credit_cost' => 'integer',
            'batch_count' => 'integer',
            'provider_latency_ms' => 'integer',
            'generation_meta' => 'array',
            'completed_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(BusinessInspirationProject::class, 'project_id');
    }

    public function promptVersion(): BelongsTo
    {
        return $this->belongsTo(BusinessInspirationPromptVersion::class, 'prompt_version_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assets(): HasMany
    {
        return $this->hasMany(BusinessInspirationGenerationAsset::class, 'generation_id');
    }

    public function creditTransactions(): HasMany
    {
        return $this->hasMany(AiCreditTransaction::class, 'generation_id');
    }
}
