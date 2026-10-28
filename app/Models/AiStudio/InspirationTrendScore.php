<?php

declare(strict_types=1);

namespace App\Models\AiStudio;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class InspirationTrendScore
 *
 * @property string $id
 * @property string $inspiration_item_id
 * @property float $trend_score
 * @property float $virality_velocity
 * @property float $engagement_rate
 * @property float $recency_weight
 * @property float $source_credibility_weight
 * @property \Illuminate\Support\Carbon|null $scored_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class InspirationTrendScore extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'inspiration_trend_scores';

    protected $fillable = [
        'inspiration_item_id',
        'trend_score',
        'virality_velocity',
        'engagement_rate',
        'recency_weight',
        'source_credibility_weight',
        'scored_at',
    ];

    protected function casts(): array
    {
        return [
            'trend_score' => 'float',
            'virality_velocity' => 'float',
            'engagement_rate' => 'float',
            'recency_weight' => 'float',
            'source_credibility_weight' => 'float',
            'scored_at' => 'datetime',
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(InspirationItem::class, 'inspiration_item_id');
    }
}
