<?php

declare(strict_types=1);

namespace App\Models\AiStudio;

use App\Models\Business;
use App\Models\Traits\HasUuid;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class InspirationFavorite
 *
 * @property string $id
 * @property string|null $user_id
 * @property string|null $business_id
 * @property string $asset_type
 * @property string|null $asset_id
 * @property string|null $external_id
 * @property array|null $metadata
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class InspirationFavorite extends Model
{
    use HasFactory, HasUuid;

    public const TYPE_INSPIRATION_ITEM = 'inspiration_item';
    public const TYPE_EXTERNAL = 'external';
    public const TYPE_GENERATION_ASSET = 'generation_asset';
    public const TYPE_USER_UPLOAD = 'user_upload';
    public const TYPE_INSPIRATION_ASSET = 'inspiration_asset';

    protected $table = 'inspiration_favorites';

    protected $fillable = [
        'user_id',
        'business_id',
        'asset_type',
        'asset_id',
        'external_id',
        'metadata',
        'title',
        'preview_url',
        'source_provider',
    ];

    public function getTitleAttribute(): string
    {
        return (string) ($this->metadata['title'] ?? $this->getDisplayTitleAttribute());
    }

    public function setTitleAttribute(string $val): void
    {
        $meta = $this->metadata ?? [];
        $meta['title'] = $val;
        $this->metadata = $meta;
    }

    public function getPreviewUrlAttribute(): ?string
    {
        return (string) ($this->metadata['preview_url'] ?? $this->metadata['image_url'] ?? $this->getDisplayImageUrlAttribute());
    }

    public function setPreviewUrlAttribute(string $val): void
    {
        $meta = $this->metadata ?? [];
        $meta['preview_url'] = $val;
        $meta['image_url'] = $val;
        $this->metadata = $meta;
    }

    public function getSourceProviderAttribute(): string
    {
        return (string) ($this->metadata['source_provider'] ?? 'cooca');
    }

    public function setSourceProviderAttribute(string $val): void
    {
        $meta = $this->metadata ?? [];
        $meta['source_provider'] = $val;
        $this->metadata = $meta;
    }

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return BelongsTo<Business, $this>
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

    /**
     * Scope query untuk bisnis aktif.
     */
    public function scopeForBusiness(Builder $query, string $businessId): Builder
    {
        return $query->where('business_id', $businessId);
    }

    /**
     * URL pratinjau gambar teresolusi.
     */
    public function getDisplayImageUrlAttribute(): ?string
    {
        if (!empty($this->metadata['image_url'])) {
            return (string) $this->metadata['image_url'];
        }

        if ($this->asset_type === self::TYPE_INSPIRATION_ITEM && !empty($this->asset_id)) {
            $item = InspirationItem::find($this->asset_id);
            return $item?->display_image_url;
        }

        if ($this->asset_type === self::TYPE_GENERATION_ASSET && !empty($this->asset_id)) {
            $asset = BusinessInspirationGenerationAsset::find($this->asset_id);
            return $asset?->output_image_url;
        }

        if ($this->asset_type === self::TYPE_INSPIRATION_ASSET && !empty($this->asset_id)) {
            $asset = InspirationAsset::find($this->asset_id);
            return $asset?->image_url;
        }

        return null;
    }

    /**
     * Judul aset teresolusi.
     */
    public function getDisplayTitleAttribute(): string
    {
        if (!empty($this->metadata['title'])) {
            return (string) $this->metadata['title'];
        }

        if ($this->asset_type === self::TYPE_INSPIRATION_ITEM && !empty($this->asset_id)) {
            $item = InspirationItem::find($this->asset_id);
            return $item?->title ?? 'Inspirasi Visual';
        }

        return 'Item Favorit';
    }
}
