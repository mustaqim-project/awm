<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionOrderItem extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'production_order_id',
        'material_id',
        'unit_id',
        'planned_quantity',
        'actual_consumed_quantity',
        'waste_quantity',
        'unit_cost',
        'total_cost',
        'batch_number',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'planned_quantity' => 'float',
            'actual_consumed_quantity' => 'float',
            'waste_quantity' => 'float',
            'unit_cost' => 'float',
            'total_cost' => 'float',
        ];
    }

    /**
     * @return BelongsTo<ProductionOrder, $this>
     */
    public function productionOrder(): BelongsTo
    {
        return $this->belongsTo(ProductionOrder::class);
    }

    /**
     * @return BelongsTo<Material, $this>
     */
    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    /**
     * @return BelongsTo<Unit, $this>
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
