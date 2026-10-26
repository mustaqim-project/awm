<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Sales;

use App\Domain\Sales\SalesPipelineService;
use App\Http\Controllers\Controller;
use App\Models\SalesOrder;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class SalesOrderController extends Controller
{
    public function __construct(
        private readonly SalesPipelineService $salesPipelineService = new SalesPipelineService
    ) {}

    /**
     * List sales orders with filters.
     */
    public function index(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $query = SalesOrder::with(['customer:id,name,company_name', 'items'])
            ->where('business_id', $business->id)
            ->latest('order_date');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('so_number', 'LIKE', "%{$search}%")
                    ->orWhereHas('customer', fn($c) => $c->where('name', 'LIKE', "%{$search}%"));
            });
        }

        $salesOrders = $query->paginate(20);

        return response()->json([
            'sales_orders' => $salesOrders->items(),
            'pagination' => [
                'current_page' => $salesOrders->currentPage(),
                'last_page' => $salesOrders->lastPage(),
                'per_page' => $salesOrders->perPage(),
                'total' => $salesOrders->total(),
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Show sales order detail.
     */
    public function show(Request $request, SalesOrder $salesOrder): JsonResponse
    {
        $business = Context::requireBusiness();
        if ($salesOrder->business_id !== $business->id) {
            return response()->json(['message' => 'Pesanan penjualan tidak ditemukan.'], Response::HTTP_NOT_FOUND);
        }

        $salesOrder->load(['customer', 'items.product.outputUnit', 'quotation', 'invoices']);

        return response()->json([
            'sales_order' => $salesOrder,
        ], Response::HTTP_OK);
    }

    /**
     * Generate invoice from sales order.
     */
    public function generateInvoice(Request $request, SalesOrder $salesOrder): JsonResponse
    {
        $business = Context::requireBusiness();
        if ($salesOrder->business_id !== $business->id) {
            return response()->json(['message' => 'Pesanan penjualan tidak ditemukan.'], Response::HTTP_NOT_FOUND);
        }

        $invoice = $this->salesPipelineService->generateInvoiceFromSalesOrder($salesOrder);

        return response()->json([
            'message' => "Faktur Penjualan {$invoice->invoice_number} berhasil diterbitkan dari Pesanan {$salesOrder->so_number}.",
            'invoice' => $invoice->load(['customer', 'items']),
        ], Response::HTTP_CREATED);
    }

    /**
     * Create new sales order.
     */
    public function store(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'customer_id' => ['required', 'string', 'exists:customers,id'],
            'so_number' => ['nullable', 'string', 'max:64'],
            'order_date' => ['required', 'date'],
            'expected_delivery_date' => ['nullable', 'date'],
            'shipping_address' => ['nullable', 'string', 'max:500'],
            'quotation_id' => ['nullable', 'string', 'exists:quotations,id'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'string', 'exists:products,id'],
            'items.*.product_name' => ['required_without:items.*.product_id', 'nullable', 'string'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $salesOrder = $this->salesPipelineService->createSalesOrder($business, $validated);

        return response()->json([
            'success' => true,
            'message' => "Pesanan Penjualan {$salesOrder->so_number} berhasil dibuat.",
            'sales_order' => $salesOrder->load(['customer', 'items.product']),
            'data' => $salesOrder,
        ], Response::HTTP_CREATED);
    }

    /**
     * Update sales order.
     */
    public function update(Request $request, SalesOrder $salesOrder): JsonResponse
    {
        $business = Context::requireBusiness();
        if ($salesOrder->business_id !== $business->id) {
            return response()->json(['message' => 'Pesanan penjualan tidak ditemukan.'], Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate([
            'customer_id' => ['string', 'exists:customers,id'],
            'order_date' => ['date'],
            'expected_delivery_date' => ['nullable', 'date'],
            'shipping_address' => ['nullable', 'string', 'max:500'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['nullable', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'string', 'exists:products,id'],
            'items.*.product_name' => ['nullable', 'string'],
            'items.*.unit_price' => ['required_with:items', 'numeric', 'min:0'],
            'items.*.quantity' => ['required_with:items', 'numeric', 'min:0.01'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $updated = $this->salesPipelineService->updateSalesOrder($salesOrder, $validated);

        return response()->json([
            'success' => true,
            'message' => "Pesanan Penjualan {$updated->so_number} berhasil diperbarui.",
            'sales_order' => $updated->load(['customer', 'items.product']),
            'data' => $updated,
        ], Response::HTTP_OK);
    }

    /**
     * Delete / cancel sales order.
     */
    public function destroy(Request $request, SalesOrder $salesOrder): JsonResponse
    {
        $business = Context::requireBusiness();
        if ($salesOrder->business_id !== $business->id) {
            return response()->json(['message' => 'Pesanan penjualan tidak ditemukan.'], Response::HTTP_NOT_FOUND);
        }

        $salesOrder->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pesanan penjualan berhasil dihapus.',
        ], Response::HTTP_OK);
    }
}
