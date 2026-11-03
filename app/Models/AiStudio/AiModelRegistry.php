<?php

declare(strict_types=1);

namespace App\Models\AiStudio;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class AiModelRegistry
 *
 * @property string $id
 * @property string $provider
 * @property string $model_name
 * @property string $exact_model_id
 * @property string|null $version
 * @property string|null $description
 * @property string|null $use_case
 * @property bool $is_default
 * @property bool $is_active
 * @property array|null $capabilities
 * @property array|null $supported_sizes
 * @property array|null $supported_qualities
 * @property array|null $supported_output_formats
 * @property array|null $parameters_policy
 * @property string|null $api_endpoint
 * @property int $rate_limit_rpm
 * @property int $timeout_seconds
 * @property array|null $retry_policy
 * @property string|null $official_doc_url
 * @property \Illuminate\Support\Carbon|null $last_verified_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class AiModelRegistry extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'ai_model_registries';

    protected $fillable = [
        'provider',
        'model_name',
        'exact_model_id',
        'version',
        'description',
        'use_case',
        'is_default',
        'is_active',
        'capabilities',
        'supported_sizes',
        'supported_qualities',
        'supported_output_formats',
        'parameters_policy',
        'api_endpoint',
        'rate_limit_rpm',
        'timeout_seconds',
        'retry_policy',
        'official_doc_url',
        'last_verified_at',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'capabilities' => 'array',
            'supported_sizes' => 'array',
            'supported_qualities' => 'array',
            'supported_output_formats' => 'array',
            'parameters_policy' => 'array',
            'rate_limit_rpm' => 'integer',
            'timeout_seconds' => 'integer',
            'retry_policy' => 'array',
            'last_verified_at' => 'datetime',
        ];
    }

    public function pricings(): HasMany
    {
        return $this->hasMany(AiModelPricing::class, 'model_id');
    }

    public function activePricing(): HasOne
    {
        return $this->hasOne(AiModelPricing::class, 'model_id')
            ->latestOfMany('effective_at');
    }
}

