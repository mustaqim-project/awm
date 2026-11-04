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
     * Resolusi URL aset visual lokal yang dijamin aman terhadap Content Security Policy (CSP) dan 404.
     */
    public function getDisplayImageUrlAttribute(): string
    {
        $url = (string) ($this->thumbnail_url ?: $this->canonical_url);

        // 1. Cek jika URL menunjuk ke file storage lokal
        if (str_starts_with($url, '/storage/') || str_starts_with($url, 'storage/')) {
            $relPath = ltrim(preg_replace('#^/?storage/#', '', $url), '/');
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($relPath) || file_exists(public_path('storage/' . $relPath))) {
                return asset('storage/' . $relPath);
            }

            // Jika file lokal tidak ada, gunakan URL canonical jika berupa http/https eksternal
            if (!empty($this->canonical_url) && str_starts_with($this->canonical_url, 'http')) {
                return $this->canonical_url;
            }
        }

        // 2. Cari file lokal terunduh berdasarkan source & external_id
        $sourceCode = $this->source?->code ?? 'general';
        $safeId = preg_replace('/[^a-zA-Z0-9_\-]/', '_', (string) $this->external_id);

        foreach (['webp', 'jpg', 'jpeg', 'png', 'svg'] as $ext) {
            $localRelPath = "ai-studio/inspirations/{$sourceCode}/{$safeId}.{$ext}";
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($localRelPath) || file_exists(public_path('storage/' . $localRelPath))) {
                return asset("storage/{$localRelPath}");
            }
        }

        // 3. Jika URL berupa tautan eksternal (Unsplash / CDN komersial yang terdaftar di CSP)
        if (!empty($url) && (str_starts_with($url, 'http://') || str_starts_with($url, 'https://'))) {
            return $url;
        }

        // 4. Jika URL path relatif (/images/...)
        if (!empty($url) && str_starts_with($url, '/')) {
            return asset(ltrim($url, '/'));
        }

        return asset('images/ai-studio/placeholder.svg');
    }

    public function getThumbnailUrlAttribute(?string $value): string
    {
        if (empty($value)) {
            return asset('images/ai-studio/placeholder.svg');
        }

        if (str_starts_with($value, '/storage/') || str_starts_with($value, 'storage/')) {
            $relPath = ltrim(preg_replace('#^/?storage/#', '', $value), '/');
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($relPath) || file_exists(public_path('storage/' . $relPath))) {
                return asset('storage/' . $relPath);
            }
            if (!empty($this->canonical_url) && str_starts_with($this->canonical_url, 'http')) {
                return $this->canonical_url;
            }
            return asset('images/ai-studio/placeholder.svg');
        }

        if (str_starts_with($value, 'http')) {
            $sourceCode = $this->source?->code ?? 'general';
            $safeId = preg_replace('/[^a-zA-Z0-9_\-]/', '_', (string) $this->external_id);
            foreach (['webp', 'jpg', 'jpeg', 'png', 'svg'] as $ext) {
                $localPath = "ai-studio/inspirations/{$sourceCode}/{$safeId}.{$ext}";
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($localPath) || file_exists(public_path('storage/' . $localPath))) {
                    return asset("storage/{$localPath}");
                }
            }
            return $value;
        }

        return str_starts_with($value, '/') ? asset(ltrim($value, '/')) : asset($value);
    }

    public function getCanonicalUrlAttribute(?string $value): string
    {
        if (empty($value)) {
            return $this->thumbnail_url ?? '/images/ai-studio/placeholder.svg';
        }

        if (str_starts_with($value, '/storage/')) {
            $relPath = ltrim(substr($value, 9), '/');
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($relPath) || file_exists(public_path('storage/' . $relPath))) {
                return $value;
            }
            return $this->thumbnail_url ?? '/images/ai-studio/placeholder.svg';
        }

        return $value;
    }
}
