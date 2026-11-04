<?php

declare(strict_types=1);

namespace App\Models\AiStudio;

use App\Models\Business;
use App\Models\StorageFile;
use App\Models\Traits\HasUuid;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class InspirationAsset
 *
 * @property string $id
 * @property string $source_type
 * @property string|null $source_id
 * @property string|null $external_asset_id
 * @property string|null $media_id
 * @property string|null $business_id
 * @property string|null $user_id
 * @property string $title
 * @property string|null $description
 * @property string|null $source_url
 * @property string|null $image_url
 * @property string|null $thumbnail_url
 * @property int|null $width
 * @property int|null $height
 * @property string $aspect_ratio
 * @property string|null $mime_type
 * @property int $file_size
 * @property string|null $author_name
 * @property string|null $author_profile_url
 * @property string|null $source_name
 * @property string $license_name
 * @property string|null $license_url
 * @property string|null $attribution_text
 * @property string|null $usage_policy
 * @property bool $commercial_usage_allowed
 * @property bool $redistribution_allowed
 * @property string $storage_policy
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property array|null $metadata
 * @property string $moderation_status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class InspirationAsset extends Model
{
    use HasFactory, HasUuid;

    public const SOURCE_CURATED = 'curated';
    public const SOURCE_COOCA_CURATED = 'curated';
    public const SOURCE_EXTERNAL = 'external';
    public const SOURCE_GENERATED = 'generated';
    public const SOURCE_AI_GENERATED = 'generated';
    public const SOURCE_UPLOADED = 'uploaded';
    public const SOURCE_BUSINESS_UPLOAD = 'uploaded';

    public const MODERATION_PENDING = 'pending';
    public const MODERATION_APPROVED = 'approved';
    public const MODERATION_REJECTED = 'rejected';
    public const MODERATION_FLAGGED = 'flagged';

    protected $table = 'inspiration_assets';

    protected $fillable = [
        'source_type',
        'source_id',
        'external_asset_id',
        'media_id',
        'business_id',
        'user_id',
        'title',
        'description',
        'source_url',
        'image_url',
        'thumbnail_url',
        'width',
        'height',
        'aspect_ratio',
        'mime_type',
        'file_size',
        'author_name',
        'author_profile_url',
        'source_name',
        'license_name',
        'license_url',
        'attribution_text',
        'usage_policy',
        'commercial_usage_allowed',
        'redistribution_allowed',
        'storage_policy',
        'expires_at',
        'metadata',
        'moderation_status',
    ];

    protected $casts = [
        'commercial_usage_allowed' => 'boolean',
        'redistribution_allowed' => 'boolean',
        'file_size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'metadata' => 'array',
        'expires_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<StorageFile, $this>
     */
    public function media(): BelongsTo
    {
        return $this->belongsTo(StorageFile::class, 'media_id');
    }

    /**
     * @return BelongsTo<Business, $this>
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return BelongsTo<InspirationSource, $this>
     */
    public function source(): BelongsTo
    {
        return $this->belongsTo(InspirationSource::class, 'source_id');
    }

    /**
     * Scope untuk aset yang disetujui dalam kurasi.
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('moderation_status', self::MODERATION_APPROVED);
    }

    /**
     * Scope untuk bisnis aktif atau aset global.
     */
    public function scopeAccessibleFor(Builder $query, ?string $businessId = null): Builder
    {
        return $query->where(function (Builder $q) use ($businessId): void {
            $q->whereNull('business_id');
            if ($businessId !== null) {
                $q->orWhere('business_id', $businessId);
            }
        });
    }

    /**
     * Dapatkan URL gambar yang siap ditampilkan dengan domain resmi COOCA.
     */
    public function getResolvedImageUrlAttribute(): string
    {
        if ($this->media !== null) {
            return $this->media->file_url ?? ('https://cooca.id/storage/' . ltrim($this->media->file_path, '/'));
        }

        $url = (string) ($this->image_url ?? $this->thumbnail_url ?? '');
        if (str_starts_with($url, 'https://cooca.id') || str_starts_with($url, 'http://cooca.id')) {
            return $url;
        }

        if (str_starts_with($url, '/storage/') || str_starts_with($url, 'storage/')) {
            return 'https://cooca.id/storage/' . ltrim(preg_replace('#^/?storage/#', '', $url), '/');
        }

        return $url ?: 'https://cooca.id/images/placeholder-image.svg';
    }

    public function getDisplayUrlAttribute(): string
    {
        return $this->getResolvedImageUrlAttribute();
    }
}
