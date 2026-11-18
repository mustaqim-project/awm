<?php

declare(strict_types=1);

namespace App\Domain\Manufacturing;

use App\Domain\Accounting\AutoJournalService;
use App\Domain\Inventory\StockService;
use App\Domain\Material\UnitConversionService;
use App\Models\AuditLog;
use App\Models\BomHeader;
use App\Models\Business;
use App\Models\InventoryStock;
use App\Models\Material;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\ProductionOrder;
use App\Models\ProductionOrderItem;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

final class ProductionService
{
    public function __construct(
        private readonly StockService $stockService = new StockService,
        private readonly UnitConversionService $unitConversionService = new UnitConversionService,
        private readonly AutoJournalService $journalService = new AutoJournalService,
    ) {}

    /**
     * Create a new draft Production Order (SPKP) populated with BOM recipe items.
     *
     * @param array<string, mixed> $data
     */
    public function createOrder(Business $business, array $data, ?User $user): ProductionOrder
    {
        return DB::transaction(function () use ($business, $data, $user): ProductionOrder {
            $productId = (string) $data['product_id'];
            $product = Product::where('business_id', $business->id)->findOrFail($productId);

            $plannedQty = max(0.0001, (float) ($data['planned_quantity'] ?? 1.0));

            // Resolve BOM Header (explicit or primary active recipe)
            $bomHeaderId = ! empty($data['bom_header_id']) ? (string) $data['bom_header_id'] : null;
            $bomHeader = null;
            if ($bomHeaderId) {
                $bomHeader = BomHeader::whereHas('costModel', fn ($q) => $q->where('business_id', $business->id))
                    ->findOrFail($bomHeaderId);
            } else {
                $costModel = $product->activeCostModel ?? $product->costModels()->where('is_active', true)->first();
                $bomHeader = $costModel?->bomHeaders()->where('type', BomHeader::TYPE_RECIPE)->first()
                    ?? $costModel?->bomHeaders()->first();
            }

            if (! $bomHeader) {
                throw ValidationException::withMessages([
                    'product_id' => "Produk '{$product->name}' belum memiliki formula Resep / BOM aktif. Harap buat resep terlebih dahulu.",
                ]);
            }

            // Generate Sequential Order Number: SPKP-YYYYMM-XXXX
            $orderNumber = $this->generateOrderNumber($business);

            $order = ProductionOrder::create([
                'business_id' => $business->id,
                'order_number' => $orderNumber,
                'product_id' => $product->id,
                'bom_header_id' => $bomHeader->id,
                'source_location_id' => $data['source_location_id'],
                'target_location_id' => $data['target_location_id'],
                'planned_quantity' => $plannedQty,
                'actual_quantity' => 0,
                'rejected_quantity' => 0,
                'status' => ProductionOrder::STATUS_DRAFT,
                'planned_start_date' => $data['planned_start_date'] ?? now()->toDateString(),
                'unit_cost_hpp' => 0,
                'total_material_cost' => 0,
                'total_labor_cost' => (float) ($data['total_labor_cost'] ?? 0),
                'total_machine_cost' => (float) ($data['total_machine_cost'] ?? 0),
                'total_production_cost' => 0,
                'batch_number' => $data['batch_number'] ?? null,
                'expiry_date' => $data['expiry_date'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => $user?->id,
            ]);

            // Populate Production Order Items from BOM
            $bomItems = $bomHeader->items()->with('material.prices')->get();
            $batchOutput = max(0.0001, (float) ($bomHeader->output_quantity ?? 1.0));
            $multiplier = $plannedQty / $batchOutput;

            $totalEstimatedMaterialCost = 0.0;

            foreach ($bomItems as $bItem) {
                $wasteMultiplier = 1.0 + ((float) ($bItem->waste_percentage ?? 0) / 100.0);
                $itemPlannedQty = (float) $bItem->quantity * $multiplier * $wasteMultiplier;

                $mat = $bItem->material;
                $unitCost = (float) ($mat?->latestPrice?->purchase_price ?? $mat?->price ?? 0);
                $totalItemCost = $itemPlannedQty * $unitCost;
                $totalEstimatedMaterialCost += $totalItemCost;

                ProductionOrderItem::create([
                    'production_order_id' => $order->id,
                    'material_id' => $bItem->material_id,
                    'unit_id' => $bItem->unit_id ?? $mat?->unit_id,
                    'planned_quantity' => $itemPlannedQty,
                    'actual_consumed_quantity' => 0,
                    'waste_quantity' => 0,
                    'unit_cost' => $unitCost,
                    'total_cost' => $totalItemCost,
                    'notes' => $bItem->notes,
                ]);
            }

            $order->update([
                'total_material_cost' => $totalEstimatedMaterialCost,
                'total_production_cost' => $totalEstimatedMaterialCost + (float) $order->total_labor_cost + (float) $order->total_machine_cost,
                'unit_cost_hpp' => $plannedQty > 0 ? (($totalEstimatedMaterialCost + (float) $order->total_labor_cost + (float) $order->total_machine_cost) / $plannedQty) : 0,
            ]);

            AuditLog::create([
                'business_id' => $business->id,
                'user_id' => $user?->id,
                'auditable_type' => ProductionOrder::class,
                'auditable_id' => $order->id,
                'action' => 'production_order.created',
                'risk_level' => AuditLog::RISK_LOW,
                'new_values' => [
                    'order_number' => $order->order_number,
                    'product_name' => $product->name,
                    'planned_quantity' => $plannedQty,
                ],
                'created_at' => now(),
            ]);

            return $order->load(['items.material.unit', 'items.unit', 'product', 'bomHeader', 'sourceLocation', 'targetLocation']);
        });
    }

    /**
     * Release production order and issue raw materials from source warehouse into floor/WIP.
     *
     * @param array<string, mixed> $options
     */
    public function issueMaterials(ProductionOrder $order, array $options = [], ?User $user = null): ProductionOrder
    {
        return DB::transaction(function () use ($order, $options, $user): ProductionOrder {
            $order = ProductionOrder::where('business_id', $order->business_id)->lockForUpdate()->findOrFail($order->id);

            if (! $order->canIssueMaterials()) {
                throw new InvalidArgumentException("Perintah produksi {$order->order_number} tidak dapat mengeluarkan bahan karena statusnya '{$order->status}'.");
            }

            $business = $order->business;
            $allowNegativeStock = (bool) ($business->allow_negative_stock ?? false);

            $order->loadMissing('items.material');

            // 1. Pre-flight check stock for all required materials
            if (! $allowNegativeStock) {
                foreach ($order->items as $item) {
                    $requiredQty = (float) $item->planned_quantity;
                    $available = (float) (InventoryStock::where('business_id', $business->id)
                        ->where('location_id', $order->source_location_id)
                        ->where('material_id', $item->material_id)
                        ->value('quantity') ?? 0.0);

                    if ($available < $requiredQty - 0.00005) {
                        $matName = $item->material?->name ?? 'Bahan Baku';
                        $uom = $item->unit?->name ?? 'Unit';
                        throw ValidationException::withMessages([
                            'materials' => "Stok bahan baku '{$matName}' tidak mencukupi di gudang asal. Dibutuhkan: {$requiredQty} {$uom}, Tersedia: {$available} {$uom}.",
                        ]);
                    }
                }
            }

            // 2. Deduct materials from source warehouse and record StockMovement
            $totalActualMaterialCost = 0.0;

            foreach ($order->items as $item) {
                $qtyToIssue = (float) $item->planned_quantity;
                $unitCost = (float) $item->unit_cost;
                $lineCost = $qtyToIssue * $unitCost;
                $totalActualMaterialCost += $lineCost;

                $this->stockService->recordMovement(
                    businessId: $business->id,
                    locationId: $order->source_location_id,
                    productId: null,
                    movementType: StockMovement::TYPE_PRODUCTION_ISSUE,
                    quantityChange: -abs($qtyToIssue),
                    unitCost: $unitCost,
                    referenceId: $order->id,
                    referenceNumber: $order->order_number,
                    notes: "Pengeluaran Bahan Baku ke Lantai Produksi #{$order->order_number}",
                    userId: $user?->id,
                    materialId: $item->material_id
                );

                $item->update([
                    'actual_consumed_quantity' => $qtyToIssue,
                    'total_cost' => $lineCost,
                ]);
            }

            $order->update([
                'status' => ProductionOrder::STATUS_IN_PROGRESS,
                'total_material_cost' => $totalActualMaterialCost,
                'total_production_cost' => $totalActualMaterialCost + (float) $order->total_labor_cost + (float) $order->total_machine_cost,
            ]);

            // 3. Post Accounting Journal: Dr. WIP / Cr. Raw Materials
            try {
                $this->journalService->recordProductionIssueJournal($order);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Production issue journal failed for {$order->order_number}: {$e->getMessage()}");
            }

            AuditLog::create([
                'business_id' => $business->id,
                'user_id' => $user?->id,
                'auditable_type' => ProductionOrder::class,
                'auditable_id' => $order->id,
                'action' => 'production_order.materials_issued',
                'risk_level' => AuditLog::RISK_MEDIUM,
                'risk_reason' => 'Pengeluaran bahan baku produksi pabrik',
                'new_values' => [
                    'order_number' => $order->order_number,
                    'status' => ProductionOrder::STATUS_IN_PROGRESS,
                    'total_material_cost' => $totalActualMaterialCost,
                ],
                'created_at' => now(),
            ]);

            return $order->fresh(['items.material.unit', 'items.unit', 'product', 'sourceLocation', 'targetLocation']);
        });
    }

    /**
     * Complete production order: record actual yield, receive finished goods into target warehouse, and finalize unit HPP.
     *
     * @param array<string, mixed> $data
     */
    public function completeOrder(ProductionOrder $order, array $data, ?User $user = null): ProductionOrder
    {
        return DB::transaction(function () use ($order, $data, $user): ProductionOrder {
            $order = ProductionOrder::where('business_id', $order->business_id)->lockForUpdate()->findOrFail($order->id);

            if (! $order->canComplete()) {
                throw new InvalidArgumentException("Perintah produksi {$order->order_number} tidak dapat diselesaikan karena statusnya '{$order->status}'.");
            }

            $actualQty = max(0.0, (float) ($data['actual_quantity'] ?? $order->planned_quantity));
            $rejectedQty = max(0.0, (float) ($data['rejected_quantity'] ?? 0.0));
            $actualLabor = isset($data['total_labor_cost']) ? (float) $data['total_labor_cost'] : (float) $order->total_labor_cost;
            $actualMachine = isset($data['total_machine_cost']) ? (float) $data['total_machine_cost'] : (float) $order->total_machine_cost;

            $batchNumber = ! empty($data['batch_number']) ? trim((string) $data['batch_number']) : $order->batch_number;
            $expiryDate = ! empty($data['expiry_date']) ? (string) $data['expiry_date'] : $order->expiry_date?->toDateString();

            $totalProductionCost = (float) $order->total_material_cost + $actualLabor + $actualMachine;
            $unitCostHpp = $actualQty > 0 ? ($totalProductionCost / $actualQty) : 0.0;

            // 1. Receive Finished Goods into Target Warehouse
            if ($actualQty > 0) {
                $this->stockService->recordMovement(
                    businessId: $order->business_id,
                    locationId: $order->target_location_id,
                    productId: $order->product_id,
                    movementType: StockMovement::TYPE_PRODUCTION_RECEIPT,
                    quantityChange: abs($actualQty),
                    unitCost: $unitCostHpp,
                    referenceId: $order->id,
                    referenceNumber: $order->order_number,
                    batchNumber: $batchNumber,
                    expiryDate: $expiryDate,
                    notes: "Penerimaan Barang Jadi Hasil Produksi #{$order->order_number}" . ($rejectedQty > 0 ? " (Reject: {$rejectedQty})" : ""),
                    userId: $user?->id,
                    materialId: null
                );

                // Create or update ProductBatch for trace & expiry
                if (! empty($batchNumber)) {
                    $batch = ProductBatch::where('business_id', $order->business_id)
                        ->where('location_id', $order->target_location_id)
                        ->where('product_id', $order->product_id)
                        ->where('batch_number', $batchNumber)
                        ->first();

                    if ($batch) {
                        $batch->quantity = (float) $batch->quantity + $actualQty;
                        $batch->unit_cost = $unitCostHpp;
                        if (! empty($expiryDate)) {
                            $batch->expiry_date = $expiryDate;
                        }
                        $batch->is_active = true;
                        $batch->save();
                    } else {
                        ProductBatch::create([
                            'business_id' => $order->business_id,
                            'location_id' => $order->target_location_id,
                            'product_id' => $order->product_id,
                            'batch_number' => $batchNumber,
                            'expiry_date' => $expiryDate ?: null,
                            'quantity' => $actualQty,
                            'unit_cost' => $unitCostHpp,
                            'is_active' => true,
                        ]);
                    }
                }

                // Automatically synchronize product base_cost if requested or if zero
                $product = $order->product;
                if ($product && ((float) $product->base_cost <= 0 || ! empty($data['update_product_base_cost']))) {
                    $product->update([
                        'base_cost' => $unitCostHpp,
                    ]);
                }
            }

            // 2. Record Scrap / Loss Movement if defective units occurred
            if ($rejectedQty > 0) {
                $scrapCost = $rejectedQty * $unitCostHpp;
                $this->stockService->recordMovement(
                    businessId: $order->business_id,
                    locationId: $order->target_location_id,
                    productId: $order->product_id,
                    movementType: StockMovement::TYPE_PRODUCTION_SCRAP,
                    quantityChange: 0, // Recorded as cost loss annotation
                    unitCost: $unitCostHpp,
                    referenceId: $order->id,
                    referenceNumber: $order->order_number,
                    notes: "Produk Cacat / Scrap Produksi #{$order->order_number} ({$rejectedQty} Unit)",
                    userId: $user?->id
                );
            }

            // 3. Finalize Order
            $order->update([
                'actual_quantity' => $actualQty,
                'rejected_quantity' => $rejectedQty,
                'total_labor_cost' => $actualLabor,
                'total_machine_cost' => $actualMachine,
                'total_production_cost' => $totalProductionCost,
                'unit_cost_hpp' => $unitCostHpp,
                'status' => ProductionOrder::STATUS_COMPLETED,
                'actual_end_date' => now(),
                'completed_by' => $user?->id,
                'batch_number' => $batchNumber,
                'expiry_date' => $expiryDate ?: null,
            ]);

            // 4. Post Accounting Journal: Dr. Finished Goods / Cr. WIP
            try {
                $this->journalService->recordProductionCompletionJournal($order);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Production completion journal failed for {$order->order_number}: {$e->getMessage()}");
            }

            AuditLog::create([
                'business_id' => $order->business_id,
                'user_id' => $user?->id,
                'auditable_type' => ProductionOrder::class,
                'auditable_id' => $order->id,
                'action' => 'production_order.completed',
                'risk_level' => AuditLog::RISK_LOW,
                'new_values' => [
                    'order_number' => $order->order_number,
                    'actual_quantity' => $actualQty,
                    'rejected_quantity' => $rejectedQty,
                    'unit_cost_hpp' => $unitCostHpp,
                ],
                'created_at' => now(),
            ]);

            return $order->fresh(['items.material.unit', 'items.unit', 'product', 'sourceLocation', 'targetLocation']);
        });
    }

    /**
     * Cancel production order and return any previously issued materials back to source warehouse.
     */
    public function cancelOrder(ProductionOrder $order, ?User $user = null): ProductionOrder
    {
        return DB::transaction(function () use ($order, $user): ProductionOrder {
            $order = ProductionOrder::where('business_id', $order->business_id)->lockForUpdate()->findOrFail($order->id);

            if (! $order->canCancel()) {
                throw new InvalidArgumentException("Perintah produksi {$order->order_number} tidak dapat dibatalkan.");
            }

            // If materials were already issued, restore them back to source warehouse
            if ($order->status === ProductionOrder::STATUS_IN_PROGRESS) {
                $order->loadMissing('items');
                foreach ($order->items as $item) {
                    $qtyToReturn = (float) $item->actual_consumed_quantity;
                    if ($qtyToReturn > 0) {
                        $this->stockService->recordMovement(
                            businessId: $order->business_id,
                            locationId: $order->source_location_id,
                            productId: null,
                            movementType: StockMovement::TYPE_PRODUCTION_RETURN,
                            quantityChange: abs($qtyToReturn),
                            unitCost: (float) $item->unit_cost,
                            referenceId: $order->id,
                            referenceNumber: $order->order_number,
                            notes: "Pengembalian Bahan Baku karena Pembatalan SPKP #{$order->order_number}",
                            userId: $user?->id,
                            materialId: $item->material_id
                        );

                        $item->update([
                            'actual_consumed_quantity' => 0,
                        ]);
                    }
                }
            }

            $order->update([
                'status' => ProductionOrder::STATUS_CANCELLED,
            ]);

            AuditLog::create([
                'business_id' => $order->business_id,
                'user_id' => $user?->id,
                'auditable_type' => ProductionOrder::class,
                'auditable_id' => $order->id,
                'action' => 'production_order.cancelled',
                'risk_level' => AuditLog::RISK_MEDIUM,
                'risk_reason' => 'Pembatalan perintah kerja produksi pabrik',
                'new_values' => ['order_number' => $order->order_number, 'status' => ProductionOrder::STATUS_CANCELLED],
                'created_at' => now(),
            ]);

            return $order->fresh();
        });
    }

    /**
     * Generate unique sequential order number: SPKP-YYYYMM-XXXX
     */
    private function generateOrderNumber(Business $business): string
    {
        $prefix = 'SPKP-' . now()->format('Ym') . '-';
        $lastOrder = ProductionOrder::where('business_id', $business->id)
            ->where('order_number', 'like', "{$prefix}%")
            ->orderByDesc('order_number')
            ->first();

        $nextSeq = 1;
        if ($lastOrder && preg_match('/-(\d+)$/', (string) $lastOrder->order_number, $matches)) {
            $nextSeq = ((int) $matches[1]) + 1;
        }

        return $prefix . str_pad((string) $nextSeq, 4, '0', STR_PAD_LEFT);
    }
}
