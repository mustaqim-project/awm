<?php

declare(strict_types=1);

namespace App\Models\AiStudio;

use App\Models\Business;
use App\Models\Traits\HasUuid;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class InspirationUsageLog
 *
 * @property string $id
 * @property string|null $business_id
 * @property string|null $user_id
 * @property string $action
 * @property string $source
 * @property string|null $asset_reference
 * @property array|null $metadata
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class InspirationUsageLog extends Model
{
    use HasFactory, HasUuid;

    public const ACTION_SEARCH = 'search';
    public const ACTION_VIEW = 'view';
    public const ACTION_FAVORITE = 'favorite';
    public const ACTION_ADD_TO_COLLECTION = 'add_to_collection';
    public const ACTION_USE_AS_REFERENCE = 'use_as_reference';
    public const ACTION_GENERATE_CONVERSION = 'generate_conversion';

    protected $table = 'inspiration_usage_logs';

    protected $fillable = [
        'business_id',
        'user_id',
        'action',
        'source',
        'asset_reference',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

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
     * Catat telemetri tindakan secara cepat dan aman.
     *
     * @param array<string, mixed> $metadata
     */
    public static function record(
        ?string $businessId,
        ?string $userId,
        string $action,
        string $source,
        ?string $assetReference = null,
        array $metadata = []
    ): self {
        return self::create([
            'business_id' => $businessId,
            'user_id' => $userId,
            'action' => $action,
            'source' => $source,
            'asset_reference' => $assetReference,
            'metadata' => $metadata,
        ]);
    }
}

