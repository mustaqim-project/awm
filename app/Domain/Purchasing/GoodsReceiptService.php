<?php

declare(strict_types=1);

namespace App\Domain\Purchasing;

use App\Domain\Calculation\HppPropagationService;
use App\Domain\Inventory\CostingEngine;
use App\Domain\Inventory\StockService;
use App\Domain\Material\UnitConversionService;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Material;
use App\Models\MaterialPrice;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\StockMovement;
use App\Support\Context;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

final class GoodsReceiptService
{
    public function __construct(
        private readonly StockService $stockService = new StockService,
        private readonly SupplierInvoiceService $supplierInvoiceService = new SupplierInvoiceService,
        private readonly HppPropagationService $hppPropagationService = new HppPropagationService,
        private readonly UnitConversionService $unitConversionService = new UnitConversionService,
        private readonly CostingEngine $costingEngine = new CostingEngine,
    ) {}

    /**
     * Post a physical receipt against a confirmed or partially received PO.
     * The operation is atomic across receipt, stock, PO state, costing, and AP.
     *
     * @param array<string, mixed> $data
     */
    public function receive(PurchaseOrder $purchaseOrder, array $data, ?string $userId = null): GoodsReceipt
    {
        return DB::transaction(function () use ($purchaseOrder, $data, $userId): GoodsReceipt {
            $purchaseOrder = PurchaseOrder::with('items')->lockForUpdate()->findOrFail($purchaseOrder->id);
            if (! in_array($purchaseOrder->status, [
                PurchaseOrder::STATUS_CONFIRMED,
                PurchaseOrder::STATUS_PARTIALLY_INVOICED,
            ], true)) {
                throw new InvalidArgumentException("Purchase Order {$purchaseOrder->po_number} belum dapat diterima karena statusnya {$purchaseOrder->status}.");
            }

            $items = collect($data['items']);
            $key = static fn (array $item): string => ! empty($item['product_id'])
                ? 'p:' . $item['product_id']
                : (! empty($item['material_id']) ? 'm:' . $item['material_id'] : 'f:' . ($item['item_name'] ?? ''));

            $received = GoodsReceiptItem::query()
                ->whereHas('goodsReceipt', fn ($query) => $query
                    ->where('purchase_order_id', $purchaseOrder->id)
                    ->where('status', 'completed'))
                ->get(['product_id', 'material_id', 'item_name', 'quantity'])
                ->groupBy(fn (GoodsReceiptItem $item): string => $key($item->toArray()))
                ->map(fn (Collection $rows): float => (float) $rows->sum('quantity'));

            foreach ($items as $item) {
                $item = (array) $item;
                $itemKey = $key($item);
                $source = $purchaseOrder->items->first(fn ($poItem): bool =>
                    ($itemKey === 'p:' . $poItem->product_id && $poItem->product_id !== null)
                    || ($itemKey === 'm:' . $poItem->material_id && $poItem->material_id !== null)
                    || ($itemKey === 'f:' . ($poItem->item_name ?? '') && str_starts_with($itemKey, 'f:'))
                );
                $quantity = (float) ($item['quantity'] ?? 0);
                if (! $source && $quantity > 0) {
                    throw ValidationException::withMessages(['items' => 'Item penerimaan tidak terdapat pada Purchase Order.']);
                }
                $remaining = $source ? (float) $source->quantity - (float) ($received[$itemKey] ?? 0) : 0.0;

                // Support unit conversion (e.g. PO 3 Dus, receiving 90 Pcs where 1 Dus = 30 Pcs)
                $convFactor = $this->resolveItemConversionFactor($source, $item, $purchaseOrder);
                $remainingMax = $convFactor > 1.0 ? max($remaining, $remaining * $convFactor) : $remaining;

                if ($source && $quantity > $remainingMax + 0.00005) {
                    throw ValidationException::withMessages(['items' => "Quantity penerimaan melebihi sisa PO untuk {$source->item_name}."]);
                }
                if ($quantity < 0) {
                    throw ValidationException::withMessages(['items' => 'Quantity penerimaan tidak boleh negatif.']);
                }
            }

            $receiptNumber = $data['receipt_number'] ?? ('GR-' . now()->format('Ym') . '-' . random_int(1000, 9999));
            $receiptNotes = $data['notes'] ?? null;
            if (! empty($data['delivery_order_number'])) {
                $receiptNotes = trim(($receiptNotes ? $receiptNotes . ' ' : '') . '[No DO/Surat Jalan: ' . $data['delivery_order_number'] . ']');
            }

            // Landed cost handling
            $landedCostAmount = (float) ($data['landed_cost_amount'] ?? 0.0);
            $landedCostMethod = (string) ($data['landed_cost_allocation_method'] ?? 'value');
            $landedCostDetails = isset($data['landed_cost_details']) ? (array) $data['landed_cost_details'] : null;

            $allocatedLandedCosts = [];
            if ($landedCostAmount > 0) {
                $preparedItemsForAllocation = [];
                foreach ($items as $idx => $it) {
                    $itArr = (array) $it;
                    $src = $purchaseOrder->items->first(fn ($poItem): bool =>
                        ($key($itArr) === 'p:' . $poItem->product_id && $poItem->product_id !== null)
                        || ($key($itArr) === 'm:' . $poItem->material_id && $poItem->material_id !== null)
                        || ($key($itArr) === 'f:' . ($poItem->item_name ?? '') && str_starts_with($key($itArr), 'f:'))
                    );
                    $cFactor = $this->resolveItemConversionFactor($src, $itArr, $purchaseOrder);
                    $q = (float) ($itArr['quantity'] ?? 0);
                    $cost = (float) ($itArr['unit_cost'] ?? ($src?->unit_price ?? 0));
                    $preparedItemsForAllocation[$idx] = [
                        'quantity' => $q,
                        'base_quantity' => $q * $cFactor,
                        'subtotal' => $q * $cost,
                        'unit_price' => $cost,
                        'weight' => (float) ($itArr['weight'] ?? 1.0),
                        'manual_cost' => (float) ($itArr['allocated_landed_cost'] ?? 0.0),
                    ];
                }
                $allocatedLandedCosts = $this->costingEngine->allocateLandedCost(
                    totalLandedCost: $landedCostAmount,
                    items: $preparedItemsForAllocation,
                    method: $landedCostMethod
                );
            }

            $receipt = GoodsReceipt::create([
                'business_id' => $purchaseOrder->business_id,
                'location_id' => $data['location_id'],
                'supplier_id' => $purchaseOrder->supplier_id,
                'purchase_order_id' => $purchaseOrder->id,
                'receipt_number' => $receiptNumber,
                'receipt_date' => $data['receipt_date'],
                'status' => 'completed',
                'landed_cost_amount' => $landedCostAmount,
                'landed_cost_allocation_method' => $landedCostMethod,
                'landed_cost_details' => $landedCostDetails,
                'notes' => $receiptNotes,
                'received_by' => $userId,
            ]);

            $consumedPoQuantities = [];

            foreach ($items as $idx => $item) {
                $item = (array) $item;
                $rawQuantity = (float) ($item['quantity'] ?? 0);
                if ($rawQuantity <= 0) {
                    continue;
                }
                $itemKey = $key($item);
                $source = $purchaseOrder->items->first(fn ($poItem): bool =>
                    ($itemKey === 'p:' . $poItem->product_id && $poItem->product_id !== null)
                    || ($itemKey === 'm:' . $poItem->material_id && $poItem->material_id !== null)
                    || ($itemKey === 'f:' . ($poItem->item_name ?? '') && str_starts_with($itemKey, 'f:'))
                );

                $convFactor = $this->resolveItemConversionFactor($source, $item, $purchaseOrder);
                $remPo = $source ? (float) $source->quantity - (float) ($received[$itemKey] ?? 0) : 0.0;

                // Determine whether input quantity is in base unit (e.g. 90 pcs) or PO unit (e.g. 3 dus)
                $providedCost = (float) ($item['unit_cost'] ?? 0);
                if ($convFactor > 1.0 && $rawQuantity > $remPo + 0.00005) {
                    // Input is in base units (e.g. 90 pcs)
                    $stockQty = $rawQuantity;
                    $poQty = min($remPo, $rawQuantity / $convFactor);
                    if ($source && abs($providedCost - (float) $source->unit_price) < 0.01) {
                        $stockUnitCost = $providedCost / $convFactor;
                    } elseif ($providedCost > 0) {
                        $stockUnitCost = $providedCost;
                    } else {
                        $stockUnitCost = (float) ($source?->unit_price ?? 0) / $convFactor;
                    }
                } elseif ($convFactor > 1.0) {
                    // Input is in PO units (e.g. 3 dus), convert to base units for inventory stock
                    $poQty = $rawQuantity;
                    $stockQty = $rawQuantity * $convFactor;
                    if ($providedCost > 0) {
                        $stockUnitCost = $providedCost / $convFactor;
                    } else {
                        $stockUnitCost = (float) ($source?->unit_price ?? 0) / $convFactor;
                    }
                } else {
                    $poQty = $rawQuantity;
                    $stockQty = $rawQuantity;
                    $stockUnitCost = $providedCost > 0 ? $providedCost : (float) ($source?->unit_price ?? 0);
                }

                $consumedPoQuantities[$itemKey] = ($consumedPoQuantities[$itemKey] ?? 0.0) + $poQty;

                $materialId = $item['material_id'] ?? $source?->material_id;
                $productId = $item['product_id'] ?? $source?->product_id;
                $itemNotes = $item['notes'] ?? null;
                $damagedQty = isset($item['damaged_quantity']) ? (float) $item['damaged_quantity'] : 0.0;
                if ($damagedQty > 0) {
                    $itemNotes = trim(($itemNotes ? $itemNotes . ' ' : '') . "[Qty Rusak: {$damagedQty}]");
                }
                if ($convFactor > 1.0) {
                    $itemNotes = trim(($itemNotes ? $itemNotes . ' ' : '') . "[Konversi: {$stockQty} base unit ({$poQty} x {$convFactor})]");
                }

                $itemAllocatedLanded = (float) ($allocatedLandedCosts[$idx] ?? 0.0);
                $finalBaseUnitCost = $stockQty > 0
                    ? ((($poQty * $providedCost) + $itemAllocatedLanded) / $stockQty)
                    : $stockUnitCost;

                GoodsReceiptItem::create([
                    'goods_receipt_id' => $receipt->id,
                    'purchase_order_item_id' => $source?->id,
                    'product_id' => $productId,
                    'material_id' => $materialId,
                    'item_name' => $item['item_name'] ?? $source?->item_name,
                    'quantity' => $poQty,
                    'received_unit_id' => $source?->unit_id,
                    'conversion_factor' => $convFactor,
                    'base_quantity' => $stockQty,
                    'unit_cost' => (float) $item['unit_cost'],
                    'allocated_landed_cost' => $itemAllocatedLanded,
                    'effective_base_cost' => $finalBaseUnitCost,
                    'batch_number' => $item['batch_number'] ?? null,
                    'expiry_date' => $item['expiry_date'] ?? null,
                    'notes' => $itemNotes,
                ]);

                if (! empty($item['batch_number']) && $productId) {
                    $batch = ProductBatch::where('business_id', $purchaseOrder->business_id)
                        ->where('location_id', $data['location_id'])
                        ->where('product_id', $productId)
                        ->where('batch_number', (string) $item['batch_number'])
                        ->first();

                    if ($batch) {
                        $batch->quantity = (float) $batch->quantity + $stockQty;
                        $batch->unit_cost = $finalBaseUnitCost;
                        if (! empty($item['expiry_date'])) {
                            $batch->expiry_date = $item['expiry_date'];
                        }
                        $batch->is_active = true;
                        $batch->save();
                    } else {
                        ProductBatch::create([
                            'business_id' => $purchaseOrder->business_id,
                            'location_id' => $data['location_id'],
                            'product_id' => $productId,
                            'batch_number' => (string) $item['batch_number'],
                            'expiry_date' => $item['expiry_date'] ?? null,
                            'quantity' => $stockQty,
                            'unit_cost' => $finalBaseUnitCost,
                            'is_active' => true,
                        ]);
                    }
                }

                if (! $productId && ! $materialId) {
                    continue;
                }

                $this->stockService->recordMovement(
                    businessId: $purchaseOrder->business_id,
                    locationId: $data['location_id'],
                    productId: $productId,
                    materialId: $materialId,
                    movementType: StockMovement::TYPE_GOODS_RECEIPT,
                    quantityChange: $stockQty,
                    unitCost: $finalBaseUnitCost,
                    referenceId: $receipt->id,
                    referenceNumber: $receiptNumber,
                    batchNumber: $item['batch_number'] ?? null,
                    expiryDate: $item['expiry_date'] ?? null,
                    notes: "Penerimaan PO {$purchaseOrder->po_number}" . ($convFactor > 1.0 ? " ({$stockQty} base unit)" : ''),
                    userId: $userId,
                    purchaseUnitId: $source?->unit_id,
                    purchaseQuantity: $poQty,
                );

                if ($materialId && $finalBaseUnitCost > 0) {
                    $material = Material::find($materialId);
                    MaterialPrice::create([
                        'business_id' => $purchaseOrder->business_id,
                        'material_id' => $materialId,
                        'supplier_id' => $purchaseOrder->supplier_id,
                        'purchase_price' => $finalBaseUnitCost,
                        'shipping_cost' => $itemAllocatedLanded,
                        'handling_cost' => 0,
                        'import_cost' => 0,
                        'discount_amount' => 0,
                        'purchase_unit_id' => $material?->unit_id,
                        'yield_percentage' => 100,
                        'waste_percentage' => 0,
                        'effective_date' => $data['receipt_date'],
                        'notes' => "Auto dari Goods Receipt {$receiptNumber}",
                    ]);
                    $this->hppPropagationService->refreshForMaterial($materialId);
                }

                if ($productId) {
                    $prod = Product::find($productId);
                    $newProdCost = $this->costingEngine->getEffectiveUnitCost($prod, $data['location_id'], $purchaseOrder->business_id);
                    if ($newProdCost > 0) {
                        $prod->update(['base_cost' => $newProdCost]);
                    }
                }
            }

            $fullyReceived = $purchaseOrder->items->every(function ($poItem) use ($received, $consumedPoQuantities, $key): bool {
                $itemKey = $poItem->product_id
                    ? 'p:' . $poItem->product_id
                    : ($poItem->material_id ? 'm:' . $poItem->material_id : 'f:' . ($poItem->item_name ?? ''));
                return (float) ($received[$itemKey] ?? 0) + (float) ($consumedPoQuantities[$itemKey] ?? 0)
                    >= (float) $poItem->quantity - 0.00005;
            });
            $purchaseOrder->update([
                'status' => $fullyReceived ? PurchaseOrder::STATUS_COMPLETED : PurchaseOrder::STATUS_PARTIALLY_INVOICED,
            ]);

            $this->supplierInvoiceService->createFromGoodsReceipt($receipt);
            return $receipt;
        });
    }

    /**
     * Resolve unit conversion factor between PO item unit and inventory base unit.
     */
    private function resolveItemConversionFactor(?PurchaseOrderItem $source, array $item, PurchaseOrder $po): float
    {
        if (isset($item['conversion_factor']) && (float) $item['conversion_factor'] > 0) {
            return (float) $item['conversion_factor'];
        }

        if ($source && (float) ($source->conversion_factor ?? 0) > 0) {
            return (float) $source->conversion_factor;
        }

        if (! $source || ! $source->unit_id) {
            return 1.0;
        }

        $matId = $item['material_id'] ?? $source->material_id;
        $prodId = $item['product_id'] ?? $source->product_id;

        $baseUnitId = null;
        if ($matId) {
            $mat = Material::find($matId);
            $baseUnitId = $mat?->unit_id;
        } elseif ($prodId) {
            $prod = Product::find($prodId);
            $baseUnitId = $prod?->output_unit_id;
        }

        if ($baseUnitId && $source->unit_id !== $baseUnitId) {
            try {
                $factor = $this->unitConversionService->getConversionFactor(
                    from: $source->unit_id,
                    to: $baseUnitId,
                    materialId: $matId,
                    supplierId: $po->supplier_id,
                    productId: $prodId
                );
                return $factor > 0 ? $factor : 1.0;
            } catch (\Throwable) {
                return 1.0;
            }
        }

        return 1.0;
    }
}
