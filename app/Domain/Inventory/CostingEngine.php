<?php

declare(strict_types=1);

namespace App\Domain\Inventory;

use App\Domain\Material\UnitConversionService;
use App\Models\Business;
use App\Models\GoodsReceiptItem;
use App\Models\InventoryStock;
use App\Models\Material;
use App\Models\PeriodicInventoryClosing;
use App\Models\Product;
use App\Models\StockMovement;
use App\Support\Context;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class CostingEngine
{
    public const METHOD_MOVING_AVERAGE = 'moving_average';
    public const METHOD_PERIODIC_AVERAGE = 'periodic_average';
    public const METHOD_FIFO = 'fifo';

    public function __construct(
        private readonly UnitConversionService $unitConversionService = new UnitConversionService()
    ) {}

    /**
     * Calculate New Moving Weighted Average Cost.
     * Formula: New Avg = ((Q_old * C_old) + (Q_in * C_in)) / (Q_old + Q_in)
     *
     * @param float $oldQty Current stock quantity before receipt
     * @param float $oldAvgCost Current average cost before receipt
     * @param float $receivedQty Newly received quantity in base unit
     * @param float $receivedUnitCost Landed cost per base unit of newly received goods
     */
    public function calculateMovingAverage(
        float $oldQty,
        float $oldAvgCost,
        float $receivedQty,
        float $receivedUnitCost
    ): float {
        if ($receivedQty <= 0) {
            return $oldAvgCost > 0 ? $oldAvgCost : 0.0;
        }

        // If old stock was depleted or zero/negative, new average becomes the receipt unit cost
        if ($oldQty <= 0) {
            return max(0.0, $receivedUnitCost);
        }

        $oldValue = $oldQty * max(0.0, $oldAvgCost);
        $receivedValue = $receivedQty * max(0.0, $receivedUnitCost);
        $totalQuantity = $oldQty + $receivedQty;

        if ($totalQuantity <= 0) {
            return max(0.0, $receivedUnitCost);
        }

        $newAverage = ($oldValue + $receivedValue) / $totalQuantity;

        return round($newAverage, 4);
    }

    /**
     * Get Effective Unit Cost for an item (Material or Product).
     * Single Source of Truth for HPP across all modules (POS, BOM, Production, Inventory, Reports).
     *
     * @param Material|Product|string $item Material/Product instance or UUID
     * @param string|null $locationId Specific warehouse location (optional)
     * @param string|null $businessId Specific business tenant (optional)
     * @param string|null $targetUnitId Convert cost to specific target unit (e.g. ml for recipe)
     */
    public function getEffectiveUnitCost(
        Material|Product|string $item,
        ?string $locationId = null,
        ?string $businessId = null,
        ?string $targetUnitId = null
    ): float {
        $resolved = $this->resolveItem($item, $businessId);
        $businessId = $resolved['business_id'];
        $model = $resolved['model'];
        $isMaterial = $resolved['is_material'];

        $business = Business::find($businessId);
        $costingMethod = $business?->settings['costing_method'] ?? self::METHOD_MOVING_AVERAGE;

        $baseUnitCost = 0.0;
        $baseUnitId = null;

        if ($costingMethod === self::METHOD_PERIODIC_AVERAGE) {
            $now = Carbon::now();
            $periodic = $this->calculatePeriodicMonthlyAverage(
                businessId: $businessId,
                year: (int) $now->year,
                month: (int) $now->month,
                materialId: $isMaterial ? $model->id : null,
                productId: ! $isMaterial ? $model->id : null,
                locationId: $locationId
            );
            $baseUnitCost = (float) ($periodic['closing_average_cost'] ?? 0.0);
        }

        // Default or Fallback: Moving Weighted Average from InventoryStock
        if ($baseUnitCost <= 0.0) {
            $stockQuery = InventoryStock::withoutGlobalScopes()->where('business_id', $businessId);
            if ($isMaterial) {
                $stockQuery->where('material_id', $model->id);
                $baseUnitId = $model->unit_id;
            } else {
                $stockQuery->where('product_id', $model->id);
                $baseUnitId = $model->output_unit_id;
            }

            if ($locationId) {
                $stockQuery->where('location_id', $locationId);
            }

            $stocks = $stockQuery->get();
            $totalQty = (float) $stocks->sum('quantity');

            if ($stocks->isNotEmpty() && $totalQty > 0) {
                // Weighted average across matching locations
                $totalVal = 0.0;
                foreach ($stocks as $stk) {
                    $qty = max(0.0, (float) $stk->quantity);
                    $c = (float) ($stk->avg_purchase_cost > 0 ? $stk->avg_purchase_cost : $stk->last_cost);
                    $totalVal += ($qty * $c);
                }
                $baseUnitCost = $totalQty > 0 ? ($totalVal / $totalQty) : 0.0;
            } elseif ($stocks->isNotEmpty()) {
                $firstWithCost = $stocks->first(fn ($s) => (float) $s->avg_purchase_cost > 0 || (float) $s->last_cost > 0);
                if ($firstWithCost) {
                    $baseUnitCost = (float) ($firstWithCost->avg_purchase_cost > 0 ? $firstWithCost->avg_purchase_cost : $firstWithCost->last_cost);
                }
            }
        }

        // Master Catalog Fallback if no inventory stock history
        if ($baseUnitCost <= 0.0) {
            if ($isMaterial) {
                /** @var Material $model */
                $baseUnitId = $model->unit_id;
                $baseUnitCost = (float) (MaterialPrice::withoutGlobalScopes()->where('business_id', $businessId)->where('material_id', $model->id)->orderByDesc('effective_date')->value('purchase_price') ?? 0.0);
            } else {
                /** @var Product $model */
                $baseUnitId = $model->output_unit_id;
                $baseUnitCost = (float) ($model->base_cost ?? 0.0);
                if ($baseUnitCost <= 0.0 && $model->activeCostModel) {
                    $latestRun = $model->activeCostModel->costingRuns()->with('result')->latest()->first();
                    $baseUnitCost = (float) ($latestRun?->result?->hpp_per_unit ?? 0.0);
                }
            }
        }

        // Convert to target unit if target unit differs from base unit
        if ($targetUnitId && $baseUnitId && $targetUnitId !== $baseUnitId && $baseUnitCost > 0.0) {
            try {
                // E.g. base unit = Liter (Rp 12.500/Liter), target unit = ml (1 Liter = 1000 ml)
                // 1 ml = 0.001 Liter -> cost per ml = 12.500 * 0.001 = 12.50
                $ratio = $this->unitConversionService->convert(
                    qty: 1.0,
                    from: $targetUnitId,
                    to: $baseUnitId,
                    materialId: $isMaterial ? $model->id : null,
                    productId: ! $isMaterial ? $model->id : null
                );
                return round($baseUnitCost * $ratio, 4);
            } catch (\Throwable) {
                return $baseUnitCost;
            }
        }

        return round($baseUnitCost, 4);
    }

    /**
     * Calculate Periodic Weighted Average for a monthly period.
     * Formula: Monthly Average = (Opening Inventory Value + Eligible Purchase Costs) / (Opening Qty + Eligible Received Qty)
     *
     * @return array{
     *     period_year: int,
     *     period_month: int,
     *     opening_quantity: float,
     *     opening_value: float,
     *     received_quantity: float,
     *     received_cost: float,
     *     closing_average_cost: float,
     *     closing_quantity: float,
     *     closing_value: float,
     *     is_locked: bool
     * }
     */
    public function calculatePeriodicMonthlyAverage(
        string $businessId,
        int $year,
        int $month,
        ?string $materialId = null,
        ?string $productId = null,
        ?string $locationId = null
    ): array {
        // 1. Check if period is already closed and locked
        $existing = PeriodicInventoryClosing::where('business_id', $businessId)
            ->where('period_year', $year)
            ->where('period_month', $month)
            ->when($materialId, fn ($q) => $q->where('item_type', 'material')->where('item_id', $materialId))
            ->when($productId, fn ($q) => $q->where('item_type', 'product')->where('item_id', $productId))
            ->when($locationId, fn ($q) => $q->where('location_id', $locationId))
            ->first();

        if ($existing && $existing->is_locked) {
            return [
                'period_year' => $year,
                'period_month' => $month,
                'opening_quantity' => (float) $existing->opening_quantity,
                'opening_value' => (float) $existing->opening_value,
                'received_quantity' => (float) $existing->received_quantity,
                'received_cost' => (float) $existing->received_cost,
                'closing_average_cost' => (float) $existing->closing_average_cost,
                'closing_quantity' => (float) $existing->closing_quantity,
                'closing_value' => (float) $existing->closing_value,
                'is_locked' => true,
            ];
        }

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // 2. Resolve opening stock from previous month closing or stock ledger prior to start date
        $prevMonth = $startDate->copy()->subMonth();
        $prevClosing = PeriodicInventoryClosing::withoutGlobalScopes()->where('business_id', $businessId)
            ->where('period_year', (int) $prevMonth->year)
            ->where('period_month', (int) $prevMonth->month)
            ->when($materialId, fn ($q) => $q->where('item_type', 'material')->where('item_id', $materialId))
            ->when($productId, fn ($q) => $q->where('item_type', 'product')->where('item_id', $productId))
            ->when($locationId, fn ($q) => $q->where('location_id', $locationId))
            ->first();

        $openingQty = 0.0;
        $openingVal = 0.0;

        if ($prevClosing) {
            $openingQty = (float) $prevClosing->closing_quantity;
            $openingVal = (float) $prevClosing->closing_value;
        } else {
            // Aggregate from stock movements prior to start date
            $priorMovements = StockMovement::withoutGlobalScopes()->where('business_id', $businessId)
                ->where('created_at', '<', $startDate)
                ->when($materialId, fn ($q) => $q->where('material_id', $materialId))
                ->when($productId, fn ($q) => $q->where('product_id', $productId))
                ->when($locationId, fn ($q) => $q->where('location_id', $locationId))
                ->get();

            $openingQty = max(0.0, (float) $priorMovements->sum('quantity_change'));
            $lastPriorCost = (float) ($priorMovements->last()?->unit_cost ?? 0.0);
            $openingVal = $openingQty * $lastPriorCost;
        }

        // 3. Aggregate eligible receipts during the month (Goods Receipt + Production Receipt)
        $receiptMovements = StockMovement::withoutGlobalScopes()->where('business_id', $businessId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('movement_type', [
                StockMovement::TYPE_GOODS_RECEIPT,
                StockMovement::TYPE_PRODUCTION_RECEIPT,
                StockMovement::TYPE_INITIAL,
            ])
            ->where('quantity_change', '>', 0)
            ->when($materialId, fn ($q) => $q->where('material_id', $materialId))
            ->when($productId, fn ($q) => $q->where('product_id', $productId))
            ->when($locationId, fn ($q) => $q->where('location_id', $locationId))
            ->get();

        $receivedQty = (float) $receiptMovements->sum('quantity_change');
        $receivedCost = (float) $receiptMovements->sum('total_cost');

        // 4. Calculate monthly weighted average
        $denominator = $openingQty + $receivedQty;
        $closingAverageCost = 0.0;

        if ($denominator > 0) {
            $closingAverageCost = ($openingVal + $receivedCost) / $denominator;
        } elseif ($openingVal > 0 && $openingQty > 0) {
            $closingAverageCost = $openingVal / $openingQty;
        }

        // 5. Compute current net quantity at end of month
        $allMonthMovements = StockMovement::where('business_id', $businessId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->when($materialId, fn ($q) => $q->where('material_id', $materialId))
            ->when($productId, fn ($q) => $q->where('product_id', $productId))
            ->when($locationId, fn ($q) => $q->where('location_id', $locationId))
            ->sum('quantity_change');

        $closingQty = max(0.0, $openingQty + (float) $allMonthMovements);
        $closingVal = $closingQty * $closingAverageCost;

        return [
            'period_year' => $year,
            'period_month' => $month,
            'opening_quantity' => round($openingQty, 4),
            'opening_value' => round($openingVal, 2),
            'received_quantity' => round($receivedQty, 4),
            'received_cost' => round($receivedCost, 2),
            'closing_average_cost' => round($closingAverageCost, 4),
            'closing_quantity' => round($closingQty, 4),
            'closing_value' => round($closingVal, 2),
            'is_locked' => false,
        ];
    }

    /**
     * Lock and close an inventory period.
     */
    public function closePeriod(
        string $businessId,
        int $year,
        int $month,
        string $itemType,
        string $itemId,
        ?string $locationId = null,
        ?string $userId = null
    ): PeriodicInventoryClosing {
        return DB::transaction(function () use ($businessId, $year, $month, $itemType, $itemId, $locationId, $userId) {
            $metrics = $this->calculatePeriodicMonthlyAverage(
                businessId: $businessId,
                year: $year,
                month: $month,
                materialId: $itemType === 'material' ? $itemId : null,
                productId: $itemType === 'product' ? $itemId : null,
                locationId: $locationId
            );

            return PeriodicInventoryClosing::updateOrCreate(
                [
                    'business_id' => $businessId,
                    'period_year' => $year,
                    'period_month' => $month,
                    'item_type' => $itemType,
                    'item_id' => $itemId,
                    'location_id' => $locationId,
                ],
                [
                    'opening_quantity' => $metrics['opening_quantity'],
                    'opening_value' => $metrics['opening_value'],
                    'received_quantity' => $metrics['received_quantity'],
                    'received_cost' => $metrics['received_cost'],
                    'closing_average_cost' => $metrics['closing_average_cost'],
                    'closing_quantity' => $metrics['closing_quantity'],
                    'closing_value' => $metrics['closing_value'],
                    'is_locked' => true,
                    'closed_by' => $userId,
                    'closed_at' => now(),
                ]
            );
        });
    }

    /**
     * Allocate Landed Costs (shipping, customs, handling) across purchase items.
     * Supported methods: 'value' (default), 'quantity', 'weight', 'manual'.
     *
     * @param float $totalLandedCost Total additional cost to distribute
     * @param array<int, array{
     *     id?: string,
     *     subtotal?: float,
     *     quantity?: float,
     *     base_quantity?: float,
     *     weight?: float,
     *     manual_cost?: float
     * }> $items List of receipt line items
     * @param string $method 'value' | 'quantity' | 'weight' | 'manual'
     * @return array<int, float> Allocated cost per line item indexed identically to input items
     */
    public function allocateLandedCost(
        float $totalLandedCost,
        array $items,
        string $method = 'value'
    ): array {
        if ($totalLandedCost <= 0.0 || empty($items)) {
            return array_fill(0, count($items), 0.0);
        }

        $allocated = [];
        $totalBasis = 0.0;

        foreach ($items as $idx => $item) {
            $basis = match ($method) {
                'quantity' => (float) ($item['base_quantity'] ?? $item['quantity'] ?? 1.0),
                'weight' => max(0.001, (float) ($item['weight'] ?? 1.0)),
                'manual' => (float) ($item['manual_cost'] ?? 0.0),
                default => (float) ($item['subtotal'] ?? ((float) ($item['quantity'] ?? 1.0) * (float) ($item['unit_price'] ?? 0.0))),
            };
            $totalBasis += max(0.0, $basis);
        }

        if ($method === 'manual') {
            $allocatedSum = 0.0;
            foreach ($items as $idx => $item) {
                $amt = (float) ($item['manual_cost'] ?? 0.0);
                $allocated[$idx] = round($amt, 2);
                $allocatedSum += $allocated[$idx];
            }
            return $allocated;
        }

        if ($totalBasis <= 0.0) {
            // Equal distribution fallback
            $perItem = round($totalLandedCost / count($items), 2);
            $allocated = array_fill(0, count($items), $perItem);
            return $allocated;
        }

        $runningSum = 0.0;
        foreach ($items as $idx => $item) {
            $basis = match ($method) {
                'quantity' => (float) ($item['base_quantity'] ?? $item['quantity'] ?? 1.0),
                'weight' => max(0.001, (float) ($item['weight'] ?? 1.0)),
                default => (float) ($item['subtotal'] ?? ((float) ($item['quantity'] ?? 1.0) * (float) ($item['unit_price'] ?? 0.0))),
            };
            $share = ($basis / $totalBasis) * $totalLandedCost;
            $roundedShare = round($share, 2);
            $allocated[$idx] = $roundedShare;
            $runningSum += $roundedShare;
        }

        // Adjust cents difference on the first/largest item to guarantee exact sum
        $difference = round($totalLandedCost - $runningSum, 2);
        if (abs($difference) > 0.0001 && ! empty($allocated)) {
            $allocated[0] = round($allocated[0] + $difference, 2);
        }

        return $allocated;
    }

    /**
     * Resolve item model and tenant context.
     *
     * @return array{business_id: string, model: Material|Product, is_material: bool}
     */
    private function resolveItem(Material|Product|string $item, ?string $businessId = null): array
    {
        if ($item instanceof Material) {
            return [
                'business_id' => $item->business_id,
                'model' => $item,
                'is_material' => true,
            ];
        }

        if ($item instanceof Product) {
            return [
                'business_id' => $item->business_id,
                'model' => $item,
                'is_material' => false,
            ];
        }

        // String UUID lookup
        $bizId = $businessId ?? (Context::hasBusiness() ? Context::business()?->id : null);

        $matQuery = Material::query()->where('id', $item);
        if ($bizId) {
            $matQuery->where('business_id', $bizId);
        }
        $mat = $matQuery->first();
        if ($mat) {
            return [
                'business_id' => $mat->business_id,
                'model' => $mat,
                'is_material' => true,
            ];
        }

        $prodQuery = Product::query()->where('id', $item);
        if ($bizId) {
            $prodQuery->where('business_id', $bizId);
        }
        $prod = $prodQuery->first();
        if ($prod) {
            return [
                'business_id' => $prod->business_id,
                'model' => $prod,
                'is_material' => false,
            ];
        }

        throw new InvalidArgumentException("Item [{$item}] was not found in materials or products.");
    }
}
