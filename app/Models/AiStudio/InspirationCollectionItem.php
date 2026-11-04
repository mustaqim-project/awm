<?php

declare(strict_types=1);

namespace App\Models\AiStudio;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class InspirationCollectionItem
 *
 * @property string $id
 * @property string $collection_id
 * @property string $asset_type
 * @property string|null $asset_id
 * @property string|null $external_asset_id
 * @property int $sort_order
 * @property array|null $metadata
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class InspirationCollectionItem extends Model
{
    use HasFactory, HasUuid;

    public const TYPE_INSPIRATION_ITEM = 'inspiration_item';
    public const TYPE_EXTERNAL = 'external';
    public const TYPE_GENERATION_ASSET = 'generation_asset';
    public const TYPE_USER_UPLOAD = 'user_upload';
    public const TYPE_INSPIRATION_ASSET = 'inspiration_asset';

    protected $table = 'inspiration_collection_items';

    protected $fillable = [
        'collection_id',
        'asset_type',
        'asset_id',
        'external_asset_id',
        'sort_order',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'sort_order' => 'integer',
    ];

    /**
     * @return BelongsTo<InspirationCollection, $this>
     */
    public function collection(): BelongsTo
    {
        return $this->belongsTo(InspirationCollection::class, 'collection_id');
    }

    /**
     * Resolusi URL gambar untuk pratinjau kartu.
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

        return 'Item Koleksi';
    }
}

