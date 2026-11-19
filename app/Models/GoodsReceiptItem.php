<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoodsReceiptItem extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'goods_receipt_id',
        'purchase_order_item_id',
        'product_id',
        'material_id',
        'item_name',
        'quantity',
        'received_unit_id',
        'conversion_factor',
        'base_quantity',
        'unit_cost',
        'allocated_landed_cost',
        'effective_base_cost',
        'batch_number',
        'expiry_date',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expiry_date' => 'date',
            'quantity' => 'float',
            'conversion_factor' => 'float',
            'base_quantity' => 'float',
            'unit_cost' => 'float',
            'allocated_landed_cost' => 'float',
            'effective_base_cost' => 'float',
        ];
    }

    public function goodsReceipt(): BelongsTo
    {
        return $this->belongsTo(GoodsReceipt::class);
    }

    public function purchaseOrderItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }

    public function receivedUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'received_unit_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }
}
