<?php

declare(strict_types=1);

namespace App\Models\AiStudio;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class InspirationVisualAnalysis
 *
 * @property string $id
 * @property string $inspiration_item_id
 * @property string|null $composition
 * @property string|null $lighting
 * @property array|null $color_palette
 * @property string|null $color_mood
 * @property string|null $visual_hook
 * @property string|null $cta_pattern
 * @property string|null $extracted_style_prompt
 * @property string|null $negative_style_prompt
 * @property array|null $suggested_aspect_ratios
 * @property float $safety_score
 * @property array|null $metadata
 * @property \Illuminate\Support\Carbon|null $analyzed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class InspirationVisualAnalysis extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'inspiration_visual_analyses';

    protected $fillable = [
        'inspiration_item_id',
        'composition',
        'lighting',
        'color_palette',
        'color_mood',
        'visual_hook',
        'cta_pattern',
        'extracted_style_prompt',
        'negative_style_prompt',
        'suggested_aspect_ratios',
        'safety_score',
        'metadata',
        'analyzed_at',
    ];

    protected function casts(): array
    {
        return [
            'color_palette' => 'array',
            'suggested_aspect_ratios' => 'array',
            'metadata' => 'array',
            'safety_score' => 'float',
            'analyzed_at' => 'datetime',
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(InspirationItem::class, 'inspiration_item_id');
    }
}
