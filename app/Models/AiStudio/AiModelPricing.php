<?php

declare(strict_types=1);

namespace App\Models\AiStudio;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class AiModelPricing
 *
 * @property string $id
 * @property string $model_id
 * @property string $version
 * @property string $billing_mode
 * @property string $currency
 * @property float $text_input_rate
 * @property float $cached_text_rate
 * @property float $image_input_rate
 * @property float $cached_image_rate
 * @property float $image_output_rate
 * @property float|null $fixed_per_image_cost_usd
 * @property \Illuminate\Support\Carbon|null $effective_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class AiModelPricing extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'ai_model_pricings';

    protected $fillable = [
        'model_id',
        'version',
        'billing_mode',
        'currency',
        'text_input_rate',
        'cached_text_rate',
        'image_input_rate',
        'cached_image_rate',
        'image_output_rate',
        'fixed_per_image_cost_usd',
        'effective_at',
    ];

    protected function casts(): array
    {
        return [
            'text_input_rate' => 'float',
            'cached_text_rate' => 'float',
            'image_input_rate' => 'float',
            'cached_image_rate' => 'float',
            'image_output_rate' => 'float',
            'fixed_per_image_cost_usd' => 'float',
            'effective_at' => 'datetime',
        ];
    }

    public function model(): BelongsTo
    {
        return $this->belongsTo(AiModelRegistry::class, 'model_id');
    }
}

