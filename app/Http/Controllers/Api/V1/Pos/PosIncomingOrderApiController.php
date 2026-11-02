<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Pos;

use App\Domain\Pos\PosOrderService;
use App\Http\Controllers\Controller;
use App\Models\CommerceOrder;
use App\Models\PosOrder;
use App\Support\Context;
use Carbon\Carbon;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class PosIncomingOrderApiController extends Controller
{
    public function __construct(
        private readonly PosOrderService $orderService = new PosOrderService()
    ) {}

    /**
     * Polling stream of incoming omnichannel orders from web storefront & standee QR tables.
     * GET /api/v1/pos/incoming-orders
     * GET /api/v1/commerce/orders/incoming
     */
    public function incomingOrders(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $locationId = $request->query('location_id');
        $since = $request->query('since');

        // 1. Standee QR Table Orders (PosOrder with order_source = qr_table)
        $qrQuery = PosOrder::where('business_id', $business->id)
            ->where('order_source', PosOrder::SOURCE_QR_TABLE)
            ->whereIn('status', [PosOrder::STATUS_PENDING, PosOrder::STATUS_CONFIRMED])
            ->with(['items.modifiers', 'items.product.outputUnit', 'posTable', 'customer'])
            ->latest();

        if (! empty($locationId)) {
            $qrQuery->where('location_id', $locationId);
        }

        if (! empty($since)) {
            try {
                $qrQuery->where('created_at', '>=', Carbon::parse($since));
            } catch (Throwable) {
                // Ignore unparseable since
            }
        }

        $qrOrders = $qrQuery->get()->map(fn (PosOrder $o): array => [
            'id' => $o->id,
            'order_number' => $o->order_number,
            'source' => 'qr_table',
            'order_source' => 'qr_table',
            'table_number' => $o->posTable?->table_number ?? $o->table_or_reference ?? '-',
            'customer_name' => $o->customer?->name ?? $o->customer_name_guest ?? 'Tamu Meja',
            'customer_phone' => $o->customer?->phone ?? $o->customer_phone_guest ?? '',
            'status' => $o->status,
            'is_paid' => $o->isPaid(),
            'payment_status' => $o->isPaid() ? 'paid' : 'unpaid',
            'payment_gateway' => $o->payment_gateway,
            'payment_channel' => $o->payment_channel ?? ($o->isPaid() ? 'QRIS' : null),
            'paid_amount' => (float) $o->paid_amount,
            'gateway_reference' => $o->gateway_reference,
            'total_amount' => (float) $o->total_amount,
            'created_at' => $o->created_at?->toIso8601String(),
            'created_at_time' => $o->created_at?->format('H:i:s') ?? '',
            'notes' => $o->notes,
            'items' => $o->items->map(fn ($item): array => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product_name,
                'name' => $item->product_name,
                'quantity' => (float) $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'total_price' => (float) $item->total_price,
                'unit_symbol' => $item->product?->outputUnit?->symbol ?? '',
                'notes' => $item->notes,
                'modifiers' => $item->modifiers_display_text,
                'modifiers_summary' => $item->modifiers_display_text,
                'selected_modifiers' => $item->modifiers->pluck('modifier_option_id')->filter()->values()->all(),
            ])->values()->all(),
        ]);

        // 2. Web Storefront Online Orders (CommerceOrder)
        $storefrontQuery = CommerceOrder::where('business_id', $business->id)
            ->whereIn('status', [
                CommerceOrder::STATUS_PROOF_SUBMITTED,
                CommerceOrder::STATUS_PAID,
                CommerceOrder::STATUS_PROCESSING,
                CommerceOrder::STATUS_PENDING_REVIEW,
                CommerceOrder::STATUS_PENDING_PAYMENT,
            ])
            ->with(['items.product'])
            ->latest();

        if (! empty($locationId)) {
            $storefrontQuery->where('location_id', $locationId);
        }

        if (! empty($since)) {
            try {
                $storefrontQuery->where('created_at', '>=', Carbon::parse($since));
            } catch (Throwable) {
                // Ignore unparseable since
            }
        }

        $storefrontOrders = $storefrontQuery->get()->map(fn (CommerceOrder $o): array => [
            'id' => $o->id,
            'order_number' => $o->order_number,
            'source' => 'storefront',
            'order_source' => 'storefront',
            'customer_name' => $o->customer_name ?? 'Pelanggan Online',
            'customer_phone' => $o->customer_phone ?? '',
            'fulfillment_type' => $o->fulfillment_type ?? 'delivery',
            'shipping_courier_name' => $o->shipping_courier_name ?? 'Kurir Toko',
            'delivery_address' => $o->shipping_address ?? '',
            'total_amount' => (float) $o->total_amount,
            'payment_status' => $o->payment_status ?? 'unpaid',
            'status' => $o->status,
            'created_at' => $o->created_at?->toIso8601String(),
            'created_at_time' => $o->created_at?->format('H:i:s') ?? '',
            'notes' => $o->shipping_notes ?? $o->notes ?? null,
            'items' => $o->items->map(fn ($item): array => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product_name ?? $item->product?->name ?? 'Produk',
                'name' => $item->product_name ?? $item->product?->name ?? 'Produk',
                'quantity' => (float) $item->quantity,
                'unit_price' => (float) ($item->unit_price ?? $item->price ?? 0),
                'total_price' => (float) ($item->total_price ?? $item->subtotal ?? 0),
                'notes' => $item->notes ?? null,
            ])->values()->all(),
        ]);

        return response()->json([
            'success' => true,
            'summary' => [
                'total_incoming' => $qrOrders->count() + $storefrontOrders->count(),
                'qr_table_count' => $qrOrders->count(),
                'storefront_count' => $storefrontOrders->count(),
            ],
            'qr_table_orders' => $qrOrders->values()->all(),
            'storefront_orders' => $storefrontOrders->values()->all(),
            'orders' => $qrOrders->concat($storefrontOrders)->values()->all(),
        ], Response::HTTP_OK);
    }

    /**
     * Cashier accepts an incoming QR order or Storefront order.
     * POST /api/v1/pos/orders/{id}/accept
     */
    public function acceptOrder(Request $request, string $id): JsonResponse
    {
        $business = Context::requireBusiness();
        $user = $request->user();

        // 1. Try finding PosOrder
        $posOrder = PosOrder::where('business_id', $business->id)->find($id);
        if ($posOrder) {
            try {
                $accepted = $this->orderService->acceptQrOrder($posOrder, $user);

                return response()->json([
                    'success' => true,
                    'message' => "Pesanan #{$posOrder->order_number} diterima dan diteruskan ke dapur.",
                    'order' => $accepted,
                ], Response::HTTP_OK);
            } catch (DomainException $e) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } catch (Throwable $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menerima pesanan: ' . $e->getMessage(),
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        // 2. Try finding CommerceOrder
        $commerceOrder = CommerceOrder::where('business_id', $business->id)->find($id);
        if ($commerceOrder) {
            $commerceOrder->update([
                'status' => CommerceOrder::STATUS_PROCESSING,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Pesanan toko online #{$commerceOrder->order_number} berhasil diterima.",
                'order' => $commerceOrder->fresh(),
            ], Response::HTTP_OK);
        }

        return response()->json([
            'success' => false,
            'message' => 'Pesanan tidak ditemukan di bisnis ini.',
        ], Response::HTTP_NOT_FOUND);
    }

    /**
     * Cashier rejects an incoming QR order or Storefront order with mandatory reason.
     * POST /api/v1/pos/orders/{id}/reject
     */
    public function rejectOrder(Request $request, string $id): JsonResponse
    {
        $business = Context::requireBusiness();
        $user = $request->user();

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:255'],
        ], [
            'reason.required' => 'Alasan penolakan pesanan wajib diisi.',
        ]);

        $reason = trim($validated['reason']);

        // 1. Try finding PosOrder
        $posOrder = PosOrder::where('business_id', $business->id)->find($id);
        if ($posOrder) {
            try {
                $rejected = $this->orderService->rejectQrOrder($posOrder, $user, $reason);

                return response()->json([
                    'success' => true,
                    'message' => "Pesanan #{$posOrder->order_number} ditolak.",
                    'order' => $rejected,
                ], Response::HTTP_OK);
            } catch (DomainException $e) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } catch (Throwable $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menolak pesanan: ' . $e->getMessage(),
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        // 2. Try finding CommerceOrder
        $commerceOrder = CommerceOrder::where('business_id', $business->id)->find($id);
        if ($commerceOrder) {
            if ($commerceOrder->payment_status === CommerceOrder::PAYMENT_PAID) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pesanan online sudah lunas. Harap lakukan proses refund terlebih dahulu.',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $commerceOrder->update([
                'status' => CommerceOrder::STATUS_CANCELLED,
                'shipping_notes' => 'Ditolak: ' . $reason,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Pesanan toko online #{$commerceOrder->order_number} ditolak.",
                'order' => $commerceOrder->fresh(),
            ], Response::HTTP_OK);
        }

        return response()->json([
            'success' => false,
            'message' => 'Pesanan tidak ditemukan di bisnis ini.',
        ], Response::HTTP_NOT_FOUND);
    }
}
