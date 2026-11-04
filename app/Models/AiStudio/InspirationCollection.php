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
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Class InspirationCollection
 *
 * @property string $id
 * @property string $owner_type
 * @property string|null $business_id
 * @property string|null $user_id
 * @property string $title
 * @property string $slug
 * @property string|null $description
 * @property string|null $cover_media_id
 * @property string|null $cover_image_url
 * @property string $visibility
 * @property string $status
 * @property bool $is_featured
 * @property string|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class InspirationCollection extends Model
{
    use HasFactory, HasUuid;

    public const OWNER_GLOBAL = 'global';
    public const OWNER_BUSINESS = 'business';
    public const OWNER_USER = 'user';

    public const VISIBILITY_PUBLIC = 'public';
    public const VISIBILITY_PRIVATE = 'private';
    public const VISIBILITY_UNLISTED = 'unlisted';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_ARCHIVED = 'archived';

    protected $table = 'inspiration_collections';

    protected $fillable = [
        'owner_type',
        'business_id',
        'user_id',
        'title',
        'slug',
        'description',
        'cover_media_id',
        'cover_image_url',
        'visibility',
        'status',
        'is_featured',
        'created_by',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
    ];

    protected $appends = [
        'name',
    ];

    public function getNameAttribute(): string
    {
        return (string) ($this->attributes['title'] ?? '');
    }

    public function setNameAttribute(string $value): void
    {
        $this->attributes['title'] = $value;
    }

    protected static function booted(): void
    {
        static::creating(function (self $collection): void {
            if (empty($collection->slug)) {
                $baseSlug = Str::slug($collection->title);
                $collection->slug = $baseSlug . '-' . Str::lower(Str::random(6));
            }
        });
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
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return BelongsTo<StorageFile, $this>
     */
    public function coverMedia(): BelongsTo
    {
        return $this->belongsTo(StorageFile::class, 'cover_media_id');
    }

    /**
     * @return HasMany<InspirationCollectionItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(InspirationCollectionItem::class, 'collection_id')->orderBy('sort_order');
    }

    /**
     * Scope untuk koleksi global COOCA.
     */
    public function scopeGlobal(Builder $query): Builder
    {
        return $query->where('owner_type', self::OWNER_GLOBAL);
    }

    /**
     * Scope untuk koleksi milik bisnis tertentu.
     */
    public function scopeForBusiness(Builder $query, string $businessId): Builder
    {
        return $query->where('business_id', $businessId);
    }

    /**
     * Scope untuk koleksi unggulan.
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope untuk koleksi aktif.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * URL Cover yang aman ditampilkan.
     */
    public function getResolvedCoverUrlAttribute(): ?string
    {
        if (!empty($this->cover_image_url)) {
            return $this->cover_image_url;
        }

        if ($this->coverMedia !== null) {
            return $this->coverMedia->file_url ?? asset('storage/' . $this->coverMedia->file_path);
        }

        $firstItem = $this->items()->first();
        if ($firstItem !== null && !empty($firstItem->metadata['image_url'])) {
            return (string) $firstItem->metadata['image_url'];
        }

        return null;
    }
}
