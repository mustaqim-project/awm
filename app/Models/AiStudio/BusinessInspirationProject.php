<?php

declare(strict_types=1);

namespace App\Models\AiStudio;

use App\Models\Business;
use App\Models\Product;
use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class BusinessInspirationProject
 *
 * @property string $id
 * @property string $business_id
 * @property string|null $user_id
 * @property string|null $inspiration_item_id
 * @property string|null $product_id
 * @property string $name
 * @property string|null $description
 * @property string|null $target_industry
 * @property string $target_platform
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $last_activity_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class BusinessInspirationProject extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid;

    protected $table = 'business_inspiration_projects';

    protected $fillable = [
        'business_id',
        'user_id',
        'inspiration_item_id',
        'product_id',
        'name',
        'description',
        'target_industry',
        'target_platform',
        'status',
        'last_activity_at',
    ];

    protected function casts(): array
    {
        return [
            'last_activity_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function inspirationItem(): BelongsTo
    {
        return $this->belongsTo(InspirationItem::class, 'inspiration_item_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function assets(): HasMany
    {
        return $this->hasMany(BusinessInspirationProjectAsset::class, 'project_id');
    }

    public function promptVersions(): HasMany
    {
        return $this->hasMany(BusinessInspirationPromptVersion::class, 'project_id')->orderByDesc('version_number');
    }

    public function latestPromptVersion(): HasOne
    {
        return $this->hasOne(BusinessInspirationPromptVersion::class, 'project_id')->latestOfMany('version_number');
    }

    public function generations(): HasMany
    {
        return $this->hasMany(BusinessInspirationGeneration::class, 'project_id')->orderByDesc('created_at');
    }
}
