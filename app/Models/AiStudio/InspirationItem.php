<?php

declare(strict_types=1);

namespace App\Models\AiStudio;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class InspirationItem
 *
 * @property string $id
 * @property string $source_id
 * @property string|null $category_id
 * @property string $external_id
 * @property string $title
 * @property string|null $caption
 * @property string|null $canonical_url
 * @property string|null $thumbnail_url
 * @property string $media_type
 * @property array|null $media_urls
 * @property array|null $tags
 * @property string|null $industry_target
 * @property int $view_count
 * @property int $like_count
 * @property int $comment_count
 * @property int $share_count
 * @property int $save_count
 * @property bool $is_curated
 * @property string|null $curated_notes
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $collected_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class InspirationItem extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'inspiration_items';

    protected $fillable = [
        'source_id',
        'category_id',
        'external_id',
        'title',
        'caption',
        'canonical_url',
        'thumbnail_url',
        'media_type',
        'media_urls',
        'tags',
        'industry_target',
        'view_count',
        'like_count',
        'comment_count',
        'share_count',
        'save_count',
        'is_curated',
        'curated_notes',
        'status',
        'collected_at',
    ];

    protected function casts(): array
    {
        return [
            'media_urls' => 'array',
            'tags' => 'array',
            'view_count' => 'integer',
            'like_count' => 'integer',
            'comment_count' => 'integer',
            'share_count' => 'integer',
            'save_count' => 'integer',
            'is_curated' => 'boolean',
            'collected_at' => 'datetime',
        ];
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(InspirationSource::class, 'source_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(InspirationCategory::class, 'category_id');
    }

    public function visualAnalysis(): HasOne
    {
        return $this->hasOne(InspirationVisualAnalysis::class, 'inspiration_item_id');
    }

    public function trendScore(): HasOne
    {
        return $this->hasOne(InspirationTrendScore::class, 'inspiration_item_id');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(BusinessInspirationProject::class, 'inspiration_item_id');
    }

    /**
     * Resolusi URL aset visual lokal yang dijamin aman terhadap Content Security Policy (CSP).
     */
    public function getDisplayImageUrlAttribute(): string
    {
        $url = (string) ($this->thumbnail_url ?: $this->canonical_url);

        if (str_starts_with($url, '/storage/')) {
            return $url;
        }

        $sourceCode = $this->source?->code ?? 'general';
        $safeId = preg_replace('/[^a-zA-Z0-9_\-]/', '_', (string) $this->external_id);

        foreach (['svg', 'jpg', 'png', 'webp'] as $ext) {
            $localRelPath = "ai-studio/inspirations/{$sourceCode}/{$safeId}.{$ext}";
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($localRelPath)) {
                return "/storage/{$localRelPath}";
            }
        }

        return ! empty($url) && str_starts_with($url, '/') ? $url : '/images/ai-studio/placeholder.svg';
    }

    public function getThumbnailUrlAttribute(?string $value): string
    {
        if (empty($value) || str_starts_with($value, '/storage/')) {
            return $value ?? '/images/ai-studio/placeholder.svg';
        }

        if (str_starts_with($value, 'http')) {
            $sourceCode = $this->source?->code ?? 'general';
            $safeId = preg_replace('/[^a-zA-Z0-9_\-]/', '_', (string) $this->external_id);
            $localPath = "ai-studio/inspirations/{$sourceCode}/{$safeId}.jpg";
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($localPath)) {
                return "/storage/{$localPath}";
            }
        }

        return $value;
    }

    public function getCanonicalUrlAttribute(?string $value): string
    {
        if (empty($value) || str_starts_with($value, '/storage/')) {
            return $value ?? ($this->thumbnail_url ?? '/images/ai-studio/placeholder.svg');
        }

        if (str_starts_with($value, 'http')) {
            return $this->thumbnail_url;
        }

        return $value;
    }
}
