<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Inventory;

use App\Domain\Purchasing\GoodsReceiptService;
use App\Http\Controllers\Controller;
use App\Models\GoodsReceipt;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class InventoryGoodsReceiptController extends Controller
{
    public function __construct(
        private readonly GoodsReceiptService $goodsReceiptService = new GoodsReceiptService
    ) {}

    /**
     * Submit physical goods receipt against a purchase order.
     */
    public function store(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $user = $request->user();

        $validated = $request->validate([
            'purchase_order_id' => ['required', 'string', 'exists:purchase_orders,id'],
            'location_id' => ['required', 'string', 'exists:locations,id'],
            'receipt_date' => ['required', 'date'],
            'receipt_number' => ['nullable', 'string', 'max:64'],
            'delivery_order_number' => ['nullable', 'string', 'max:64'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.purchase_order_item_id' => ['nullable', 'string'],
            'items.*.product_id' => ['nullable', 'string', 'exists:products,id'],
            'items.*.material_id' => ['nullable', 'string', 'exists:materials,id'],
            'items.*.item_name' => ['nullable', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'items.*.damaged_quantity' => ['nullable', 'numeric', 'min:0'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
            'items.*.batch_number' => ['nullable', 'string', 'max:64'],
            'items.*.expiry_date' => ['nullable', 'date'],
            'items.*.notes' => ['nullable', 'string', 'max:500'],
        ]);

        $purchaseOrder = PurchaseOrder::where('business_id', $business->id)
            ->where('id', $validated['purchase_order_id'])
            ->first();

        if (! $purchaseOrder) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan pembelian tidak ditemukan.',
            ], Response::HTTP_NOT_FOUND);
        }

        // Auto-resolve item product_id/material_id from purchase_order_item_id if omitted
        foreach ($validated['items'] as $idx => $item) {
            if (empty($item['product_id']) && empty($item['material_id']) && ! empty($item['purchase_order_item_id'])) {
                $poItem = PurchaseOrderItem::find($item['purchase_order_item_id']);
                if ($poItem) {
                    $validated['items'][$idx]['product_id'] = $poItem->product_id;
                    $validated['items'][$idx]['material_id'] = $poItem->material_id;
                    $validated['items'][$idx]['item_name'] = $item['item_name'] ?? $poItem->item_name;
                }
            }
        }

        $goodsReceipt = $this->goodsReceiptService->receive($purchaseOrder, $validated, $user?->id);
        $goodsReceipt->load(['items.product', 'location']);

        $receiptItems = $goodsReceipt->items->map(fn ($item) => [
            'id' => $item->id,
            'product_id' => $item->product_id,
            'material_id' => $item->material_id,
            'item_name' => $item->item_name,
            'quantity' => (float) $item->quantity,
            'unit_cost' => (float) $item->unit_cost,
            'batch_number' => $item->batch_number,
            'expiry_date' => $item->expiry_date?->format('Y-m-d') ?? (string) $item->expiry_date,
            'notes' => $item->notes,
        ])->all();

        return response()->json([
            'success' => true,
            'message' => 'Penerimaan barang fisik berhasil dicatat dan stok gudang telah diperbarui.',
            'data' => [
                'goods_receipt' => [
                    'id' => $goodsReceipt->id,
                    'receipt_number' => $goodsReceipt->receipt_number,
                    'receipt_date' => $goodsReceipt->receipt_date?->format('Y-m-d') ?? (string) $goodsReceipt->receipt_date,
                    'status' => $goodsReceipt->status,
                    'po_status' => $purchaseOrder->fresh()->status,
                    'items' => $receiptItems,
                ],
            ],
        ], Response::HTTP_CREATED);
    }
}
