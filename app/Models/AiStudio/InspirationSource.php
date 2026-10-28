<?php

declare(strict_types=1);

namespace App\Models\AiStudio;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class InspirationSource
 *
 * @property string $id
 * @property string $code
 * @property string $name
 * @property string $driver_class
 * @property bool $is_active
 * @property int $fetch_frequency_minutes
 * @property int $rate_limit_per_minute
 * @property bool $supports_image
 * @property bool $supports_video
 * @property array|null $config_schema
 * @property \Illuminate\Support\Carbon|null $last_synced_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class InspirationSource extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'inspiration_sources';

    protected $fillable = [
        'code',
        'name',
        'driver_class',
        'is_active',
        'fetch_frequency_minutes',
        'rate_limit_per_minute',
        'supports_image',
        'supports_video',
        'config_schema',
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'supports_image' => 'boolean',
            'supports_video' => 'boolean',
            'config_schema' => 'array',
            'last_synced_at' => 'datetime',
            'fetch_frequency_minutes' => 'integer',
            'rate_limit_per_minute' => 'integer',
        ];
    }

    public function credentials(): HasMany
    {
        return $this->hasMany(InspirationSourceCredential::class, 'source_id');
    }

    public function policy(): HasOne
    {
        return $this->hasOne(InspirationSourcePolicy::class, 'source_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InspirationItem::class, 'source_id');
    }
}
