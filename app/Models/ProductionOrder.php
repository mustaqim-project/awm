<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\Auditable;
use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductionOrder extends Model
{
    use Auditable, BelongsToBusiness, HasFactory, HasUuid;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_RELEASED = 'released';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'business_id',
        'order_number',
        'product_id',
        'bom_header_id',
        'source_location_id',
        'target_location_id',
        'planned_quantity',
        'actual_quantity',
        'rejected_quantity',
        'status',
        'planned_start_date',
        'actual_end_date',
        'unit_cost_hpp',
        'total_material_cost',
        'total_labor_cost',
        'total_machine_cost',
        'total_production_cost',
        'batch_number',
        'expiry_date',
        'notes',
        'created_by',
        'completed_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'planned_quantity' => 'float',
            'actual_quantity' => 'float',
            'rejected_quantity' => 'float',
            'unit_cost_hpp' => 'float',
            'total_material_cost' => 'float',
            'total_labor_cost' => 'float',
            'total_machine_cost' => 'float',
            'total_production_cost' => 'float',
            'planned_start_date' => 'date',
            'actual_end_date' => 'datetime',
            'expiry_date' => 'date',
        ];
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return BelongsTo<BomHeader, $this>
     */
    public function bomHeader(): BelongsTo
    {
        return $this->belongsTo(BomHeader::class);
    }

    /**
     * @return BelongsTo<Location, $this>
     */
    public function sourceLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'source_location_id');
    }

    /**
     * @return BelongsTo<Location, $this>
     */
    public function targetLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'target_location_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function completer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    /**
     * @return HasMany<ProductionOrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(ProductionOrderItem::class);
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isReleased(): bool
    {
        return $this->status === self::STATUS_RELEASED;
    }

    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function canIssueMaterials(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_RELEASED], true);
    }

    public function canComplete(): bool
    {
        return in_array($this->status, [self::STATUS_RELEASED, self::STATUS_IN_PROGRESS], true);
    }

    public function canCancel(): bool
    {
        return ! in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_CANCELLED], true);
    }

    /**
     * Yield percentage calculation.
     */
    public function getYieldPercentageAttribute(): float
    {
        if ($this->planned_quantity <= 0) {
            return 100.0;
        }

        return round(((float) $this->actual_quantity / (float) $this->planned_quantity) * 100, 2);
    }
}
