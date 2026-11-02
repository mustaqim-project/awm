<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Inventory;

use App\Domain\Inventory\StockService;
use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class StockTransferController extends Controller
{
    public function __construct(
        private readonly StockService $stockService = new StockService
    ) {}

    /**
     * List stock transfers between locations.
     */
    public function index(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $query = StockTransfer::where('business_id', $business->id)
            ->with([
                'sourceLocation:id,name',
                'destinationLocation:id,name',
                'creator:id,name',
                'receiver:id,name',
                'items.product:id,name,code,barcode',
                'items.material:id,name,code',
            ])
            ->withCount('items')
            ->latest('transfer_date');

        if ($request->filled('status') && $request->get('status') !== 'all') {
            $query->where('status', $request->get('status'));
        }

        $type = $request->get('type', 'all');
        $locationId = $request->get('location_id');

        if ($locationId) {
            if ($type === 'inbound') {
                $query->where('destination_location_id', $locationId);
            } elseif ($type === 'outbound') {
                $query->where('source_location_id', $locationId);
            } else {
                $query->where(function ($q) use ($locationId): void {
                    $q->where('source_location_id', $locationId)
                        ->orWhere('destination_location_id', $locationId);
                });
            }
        }

        if ($request->filled('source_location_id')) {
            $query->where('source_location_id', $request->get('source_location_id'));
        }

        if ($request->filled('destination_location_id')) {
            $query->where('destination_location_id', $request->get('destination_location_id'));
        }

        if ($request->filled('search')) {
            $search = (string) $request->get('search');
            $query->where(function ($q) use ($search): void {
                $q->where('transfer_number', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        $perPage = max(1, (int) $request->get('per_page', 20));
        $transfers = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'transfers' => $transfers->items(),
            'pagination' => [
                'current_page' => $transfers->currentPage(),
                'last_page' => $transfers->lastPage(),
                'per_page' => $transfers->perPage(),
                'total' => $transfers->total(),
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Create a new stock transfer request in pending status.
     */
    public function store(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $user = $request->user();

        $validated = $request->validate([
            'source_location_id' => ['required', 'string', 'exists:locations,id', 'different:destination_location_id'],
            'destination_location_id' => ['required', 'string', 'exists:locations,id'],
            'transfer_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'string', 'exists:products,id', 'required_without:items.*.material_id'],
            'items.*.material_id' => ['nullable', 'string', 'exists:materials,id', 'required_without:items.*.product_id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.0001'],
        ]);

        $transferNumber = 'TRF-' . date('Ymd') . '-' . rand(100, 999);

        $transfer = StockTransfer::create([
            'business_id' => $business->id,
            'source_location_id' => $validated['source_location_id'],
            'destination_location_id' => $validated['destination_location_id'],
            'transfer_number' => $transferNumber,
            'transfer_date' => $validated['transfer_date'],
            'status' => StockTransfer::STATUS_PENDING,
            'notes' => $validated['notes'] ?? null,
            'created_by' => $user?->id,
            'sent_at' => null,
        ]);

        foreach ($validated['items'] as $itemData) {
            $product = ! empty($itemData['product_id']) ? Product::find($itemData['product_id']) : null;
            $material = ! empty($itemData['material_id']) ? Material::find($itemData['material_id']) : null;
            $cost = $product ? (float) $product->base_cost : (float) ($material?->effective_cost ?? 0.0);
            $qty = (float) $itemData['quantity'];

            StockTransferItem::create([
                'stock_transfer_id' => $transfer->id,
                'product_id' => $itemData['product_id'] ?? null,
                'material_id' => $itemData['material_id'] ?? null,
                'quantity' => $qty,
                'unit_cost' => $cost,
                'total_cost' => $qty * $cost,
            ]);
        }

        $transfer->load(['sourceLocation', 'destinationLocation', 'creator', 'items.product.outputUnit']);

        return response()->json([
            'success' => true,
            'message' => "Transfer Stok #{$transferNumber} berhasil dibuat.",
            'transfer' => $transfer,
        ], Response::HTTP_CREATED);
    }

    /**
     * Dispatch a stock transfer: marks in_transit and immediately deducts stock from source location.
     * Supports dispatching an existing transfer or creating and dispatching a new transfer.
     */
    public function dispatchTransfer(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $user = $request->user();

        $validated = $request->validate([
            'transfer_id' => ['nullable', 'string', 'exists:stock_transfers,id'],
            'source_location_id' => ['required_without:transfer_id', 'string', 'exists:locations,id', 'different:destination_location_id'],
            'destination_location_id' => ['required_without:transfer_id', 'string', 'exists:locations,id'],
            'transfer_date' => ['required_without:transfer_id', 'date'],
            'notes' => ['nullable', 'string', 'max:255'],
            'items' => ['required_without:transfer_id', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'string', 'exists:products,id', 'required_without:items.*.material_id'],
            'items.*.material_id' => ['nullable', 'string', 'exists:materials,id', 'required_without:items.*.product_id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.0001'],
        ]);

        if (! empty($validated['transfer_id'])) {
            $transfer = StockTransfer::where('business_id', $business->id)
                ->where('id', $validated['transfer_id'])
                ->first();

            if (! $transfer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transfer stok tidak ditemukan.',
                ], Response::HTTP_NOT_FOUND);
            }
        } else {
            // Create a new transfer in pending state first, then dispatch
            $transferNumber = 'TRF-' . date('Ymd') . '-' . rand(100, 999);
            $transfer = StockTransfer::create([
                'business_id' => $business->id,
                'source_location_id' => $validated['source_location_id'],
                'destination_location_id' => $validated['destination_location_id'],
                'transfer_number' => $transferNumber,
                'transfer_date' => $validated['transfer_date'],
                'status' => StockTransfer::STATUS_PENDING,
                'notes' => $validated['notes'] ?? null,
                'created_by' => $user?->id,
            ]);

            foreach ($validated['items'] as $itemData) {
                $product = ! empty($itemData['product_id']) ? Product::find($itemData['product_id']) : null;
                $material = ! empty($itemData['material_id']) ? Material::find($itemData['material_id']) : null;
                $cost = $product ? (float) $product->base_cost : (float) ($material?->effective_cost ?? 0.0);
                $qty = (float) $itemData['quantity'];

                StockTransferItem::create([
                    'stock_transfer_id' => $transfer->id,
                    'product_id' => $itemData['product_id'] ?? null,
                    'material_id' => $itemData['material_id'] ?? null,
                    'quantity' => $qty,
                    'unit_cost' => $cost,
                    'total_cost' => $qty * $cost,
                ]);
            }
        }

        // Execute dispatch: deduct source stock and update status to in_transit
        $this->stockService->dispatchStockTransfer($transfer, $user);

        $transfer = $transfer->fresh([
            'sourceLocation:id,name',
            'destinationLocation:id,name',
            'creator:id,name',
            'items.product:id,name,code,barcode',
            'items.material:id,name,code',
        ]);

        return response()->json([
            'success' => true,
            'message' => "Transfer Stok #{$transfer->transfer_number} berhasil diberangkatkan (in-transit). Stok gudang asal telah berkurang.",
            'transfer' => $transfer,
        ], Response::HTTP_CREATED);
    }

    /**
     * Show Stock Transfer details.
     */
    public function show(Request $request, StockTransfer|string $stockTransfer): JsonResponse
    {
        $business = Context::requireBusiness();

        if (is_string($stockTransfer)) {
            $stockTransfer = StockTransfer::where('business_id', $business->id)
                ->where('id', $stockTransfer)
                ->first();
        }

        if (! $stockTransfer || $stockTransfer->business_id !== $business->id) {
            return response()->json([
                'success' => false,
                'message' => 'Transfer stok tidak ditemukan.',
            ], Response::HTTP_NOT_FOUND);
        }

        $stockTransfer->load([
            'sourceLocation:id,name',
            'destinationLocation:id,name',
            'creator:id,name',
            'receiver:id,name',
            'items.product:id,name,code,barcode',
            'items.material:id,name,code',
        ]);

        return response()->json([
            'success' => true,
            'transfer' => $stockTransfer,
        ], Response::HTTP_OK);
    }

    /**
     * Receive and complete a Stock Transfer at destination location.
     */
    public function receive(Request $request, StockTransfer|string $stockTransfer): JsonResponse
    {
        $business = Context::requireBusiness();

        if (is_string($stockTransfer)) {
            $stockTransfer = StockTransfer::where('business_id', $business->id)
                ->where('id', $stockTransfer)
                ->first();
        }

        if (! $stockTransfer || $stockTransfer->business_id !== $business->id) {
            return response()->json([
                'success' => false,
                'message' => 'Transfer stok tidak ditemukan.',
            ], Response::HTTP_NOT_FOUND);
        }

        $user = $request->user();

        // Idempotent: if already received, return current transfer state
        if ($stockTransfer->status === StockTransfer::STATUS_RECEIVED) {
            return response()->json([
                'success' => true,
                'message' => "Transfer Stok #{$stockTransfer->transfer_number} telah diterima & stok cabang tujuan berhasil ditambahkan.",
                'transfer' => $stockTransfer->fresh([
                    'sourceLocation:id,name',
                    'destinationLocation:id,name',
                    'creator:id,name',
                    'receiver:id,name',
                    'items.product:id,name,code,barcode',
                ]),
            ], Response::HTTP_OK);
        }

        $this->stockService->completeStockTransfer($stockTransfer, $user);

        return response()->json([
            'success' => true,
            'message' => "Transfer Stok #{$stockTransfer->transfer_number} telah diterima & stok cabang tujuan berhasil ditambahkan.",
            'transfer' => $stockTransfer->fresh([
                'sourceLocation:id,name',
                'destinationLocation:id,name',
                'creator:id,name',
                'receiver:id,name',
                'items.product:id,name,code,barcode',
            ]),
        ], Response::HTTP_OK);
    }
}
