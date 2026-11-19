<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\Auditable;
use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use App\Support\Context;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductUnitConversion extends Model
{
    use Auditable, BelongsToBusiness, HasFactory, HasUuid;

    protected $fillable = [
        'business_id',
        'product_id',
        'from_unit_id',
        'to_unit_id',
        'factor',
        'is_default',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'factor' => 'float',
            'is_default' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Business, $this>
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return BelongsTo<Unit, $this>
     */
    public function fromUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'from_unit_id');
    }

    /**
     * @return BelongsTo<Unit, $this>
     */
    public function toUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'to_unit_id');
    }

    /**
     * Scope query to active tenant plus defaults.
     *
     * @param Builder<ProductUnitConversion> $query
     * @return Builder<ProductUnitConversion>
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where(function (Builder $q): void {
            $q->whereNull('business_id');

            if (Context::hasBusiness()) {
                $q->orWhere('business_id', Context::business()?->id);
            }
        });
    }
}
