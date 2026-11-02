<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Inventory;

use App\Http\Controllers\Controller;
use App\Models\GoodsReceiptItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpFoundation\Response;

final class InventoryPurchaseOrderController extends Controller
{
    /**
     * List pending supplier purchase orders awaiting physical receipt.
     */
    public function pending(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $query = PurchaseOrder::where('business_id', $business->id)
            ->where('po_type', PurchaseOrder::TYPE_SUPPLIER)
            ->whereIn('status', [
                PurchaseOrder::STATUS_CONFIRMED,
                PurchaseOrder::STATUS_PARTIALLY_INVOICED,
            ])
            ->with([
                'supplier:id,name,phone,email,address,contact_person',
                'items.product.outputUnit',
                'items.product',
                'items.material',
                'items.unit',
            ])
            ->latest('order_date');

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->get('supplier_id'));
        }

        if ($request->filled('search')) {
            $search = (string) $request->get('search');
            $query->where(function ($q) use ($search): void {
                $q->where('po_number', 'like', "%{$search}%")
                    ->orWhere('reference_number', 'like', "%{$search}%")
                    ->orWhereHas('supplier', fn ($s) => $s->where('name', 'like', "%{$search}%"));
            });
        }

        $allOrders = $query->get();

        // Calculate item received quantities and filter POs that still have remaining items
        $pendingOrders = [];
        foreach ($allOrders as $order) {
            $formatted = $this->formatOrderWithRemainingQuantities($order);
            if ($formatted['total_remaining_qty'] > 0.00005) {
                $pendingOrders[] = $formatted;
            }
        }

        // Apply manual pagination on formatted collection
        $perPage = max(1, (int) $request->get('per_page', 20));
        $page = max(1, (int) $request->get('page', 1));
        $total = count($pendingOrders);
        $offset = ($page - 1) * $perPage;
        $itemsForCurrentPage = array_slice($pendingOrders, $offset, $perPage);
        $lastPage = (int) ceil($total / $perPage);

        return response()->json([
            'success' => true,
            'data' => $itemsForCurrentPage,
            'pagination' => [
                'current_page' => $page,
                'last_page' => max(1, $lastPage),
                'per_page' => $perPage,
                'total' => $total,
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Show detailed purchase order with remaining quantities for receiving.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $business = Context::requireBusiness();

        $purchaseOrder = PurchaseOrder::where('business_id', $business->id)
            ->where(function ($q) use ($id): void {
                $q->where('id', $id)
                    ->orWhere('po_number', $id);
            })
            ->with([
                'supplier:id,name,phone,email,address,contact_person',
                'items.product.outputUnit',
                'items.product',
                'items.material',
                'items.unit',
            ])
            ->first();

        if (! $purchaseOrder) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan pembelian tidak ditemukan.',
            ], Response::HTTP_NOT_FOUND);
        }

        $formatted = $this->formatOrderWithRemainingQuantities($purchaseOrder);

        return response()->json([
            'success' => true,
            'data' => $formatted,
        ], Response::HTTP_OK);
    }

    /**
     * Format a PurchaseOrder model with accurate received and remaining quantities.
     *
     * @return array<string, mixed>
     */
    private function formatOrderWithRemainingQuantities(PurchaseOrder $order): array
    {
        $receivedMap = GoodsReceiptItem::query()
            ->whereHas('goodsReceipt', fn ($q) => $q
                ->where('purchase_order_id', $order->id)
                ->where('status', 'completed'))
            ->get(['product_id', 'material_id', 'item_name', 'quantity'])
            ->groupBy(function ($item): string {
                return ! empty($item->product_id)
                    ? 'p:' . $item->product_id
                    : (! empty($item->material_id) ? 'm:' . $item->material_id : 'f:' . ($item->item_name ?? ''));
            })
            ->map(fn ($rows): float => (float) $rows->sum('quantity'));

        $formattedItems = [];
        $totalOrdered = 0.0;
        $totalReceived = 0.0;
        $totalRemaining = 0.0;

        foreach ($order->items as $item) {
            $key = ! empty($item->product_id)
                ? 'p:' . $item->product_id
                : (! empty($item->material_id) ? 'm:' . $item->material_id : 'f:' . ($item->item_name ?? ''));

            $qty = (float) $item->quantity;
            $receivedQty = (float) ($receivedMap[$key] ?? 0.0);
            $remainingQty = max(0.0, $qty - $receivedQty);

            $totalOrdered += $qty;
            $totalReceived += $receivedQty;
            $totalRemaining += $remainingQty;

            $sku = $item->sku ?? $item->product?->code ?? $item->material?->code ?? '';
            $barcode = $item->product?->barcode ?? $item->material?->code ?? $sku;

            $formattedItems[] = [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'material_id' => $item->material_id,
                'item_name' => $item->item_name ?? $item->product?->name ?? $item->material?->name ?? 'Item',
                'sku' => $sku,
                'barcode' => $barcode,
                'quantity' => $qty,
                'received_quantity' => $receivedQty,
                'remaining_quantity' => $remainingQty,
                'unit_price' => (float) $item->unit_price,
                'unit' => $item->unit ? [
                    'id' => $item->unit->id,
                    'name' => $item->unit->name,
                    'code' => $item->unit->code ?? $item->unit->symbol ?? '',
                ] : null,
                'product' => $item->product ? [
                    'id' => $item->product->id,
                    'name' => $item->product->name,
                    'code' => $item->product->code,
                    'barcode' => $item->product->barcode,
                    'image_url' => $item->product->image_url ?? null,
                ] : null,
            ];
        }

        return [
            'id' => $order->id,
            'po_number' => $order->po_number,
            'reference_number' => $order->reference_number,
            'status' => $order->status,
            'po_type' => $order->po_type,
            'order_date' => $order->order_date?->format('Y-m-d') ?? (string) $order->order_date,
            'expected_delivery_date' => $order->expected_delivery_date?->format('Y-m-d') ?? (string) $order->expected_delivery_date,
            'supplier' => $order->supplier ? [
                'id' => $order->supplier->id,
                'name' => $order->supplier->name,
                'phone' => $order->supplier->phone,
                'email' => $order->supplier->email,
                'address' => $order->supplier->address,
                'contact_person' => $order->supplier->contact_person,
            ] : null,
            'notes' => $order->notes,
            'items_count' => count($formattedItems),
            'total_ordered_qty' => $totalOrdered,
            'total_received_qty' => $totalReceived,
            'total_remaining_qty' => $totalRemaining,
            'items' => $formattedItems,
        ];
    }
}
