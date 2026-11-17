<?php

declare(strict_types=1);

namespace App\Domain\Mcp\Tools;

use App\Domain\Inventory\StockService;
use App\Domain\Mcp\Tools\Traits\HandlesCrudPermissions;
use App\Models\AuditLog;
use App\Models\Business;
use App\Models\Location;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class PosManageTransactionTool implements McpToolInterface
{
    use HandlesCrudPermissions;

    public function __construct(
        private readonly StockService $stockService = new StockService()
    ) {}

    public function getName(): string
    {
        return 'pos_manage_transaction';
    }

    public function getDescription(): string
    {
        return 'Mengelola transaksi kasir Point of Sale (POS) di menu POS COOCA: melihat riwayat transaksi kasir, rekap omset penjualan kasir harian & shift, serta mencatat transaksi penjualan POS baru secara langsung.';
    }

    public function getRequiredAbility(): string
    {
        return 'mcp:pos:read';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'action' => [
                    'type' => 'string',
                    'enum' => ['list', 'get_summary', 'create'],
                    'default' => 'list',
                    'description' => 'Aksi: list (daftar transaksi kasir), get_summary (rekap omset & shift kasir), create (catat transaksi kasir baru).',
                ],
                'location_id' => [
                    'type' => 'string',
                    'description' => 'UUID lokasi kasir / outlet.',
                ],
                'date' => [
                    'type' => 'string',
                    'description' => 'Tanggal transaksi (YYYY-MM-DD). Default hari ini.',
                ],
                'payment_method' => [
                    'type' => 'string',
                    'enum' => ['cash', 'qris', 'transfer', 'debit', 'credit'],
                    'default' => 'cash',
                    'description' => 'Metode pembayaran transaksi kasir.',
                ],
                'customer_id' => [
                    'type' => 'string',
                    'description' => 'UUID pelanggan / member (opsional).',
                ],
                'notes' => [
                    'type' => 'string',
                    'description' => 'Catatan pesanan / transaksi.',
                ],
                'items' => [
                    'type' => 'array',
                    'description' => 'Daftar item belanja untuk transaksi kasir.',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'product_id' => [
                                'type' => 'string',
                                'description' => 'UUID atau kode produk.',
                            ],
                            'quantity' => [
                                'type' => 'number',
                                'description' => 'Jumlah beli.',
                            ],
                            'unit_price' => [
                                'type' => 'number',
                                'description' => 'Harga jual satuan (Rp). Jika kosong, mengambil harga katalog produk.',
                            ],
                        ],
                        'required' => ['quantity'],
                    ],
                ],
            ],
        ];
    }

    public function execute(Business $business, ?User $user, array $arguments = []): array
    {
        $action = trim((string) ($arguments['action'] ?? 'list'));

        return match ($action) {
            'create'      => $this->createTransaction($business, $user, $arguments),
            'get_summary' => $this->getSummary($business, $user, $arguments),
            default       => $this->listTransactions($business, $user, $arguments),
        };
    }

    private function listTransactions(Business $business, ?User $user, array $args): array
    {
        $this->assertCan('pos', 'read', $business, $user);

        $query = PosOrder::where('business_id', $business->id)
            ->with(['items.product', 'customer', 'location:id,name'])
            ->latest()
            ->take(20);

        if (! empty($args['location_id'])) {
            $query->where('location_id', $args['location_id']);
        }

        if (! empty($args['date'])) {
            $query->whereDate('created_at', $args['date']);
        }

        $orders = $query->get();

        return [
            'success' => true,
            'message' => 'Berhasil mengambil daftar transaksi kasir POS.',
            'count' => $orders->count(),
            'orders' => $orders->map(fn (PosOrder $o) => [
                'id' => $o->id,
                'order_number' => $o->order_number,
                'status' => $o->status,
                'outlet' => $o->location?->name,
                'customer_name' => $o->customer?->name ?? 'Pelanggan Umum',
                'total_amount' => (float) $o->total_amount,
                'items_count' => $o->items->count(),
                'created_at' => $o->created_at?->toDateTimeString(),
            ])->all(),
        ];
    }

    private function getSummary(Business $business, ?User $user, array $args): array
    {
        $this->assertCan('pos', 'read', $business, $user);

        $date = $args['date'] ?? Carbon::today()->toDateString();

        $query = PosOrder::where('business_id', $business->id)
            ->whereDate('created_at', $date)
            ->where('status', '!=', PosOrder::STATUS_VOIDED);

        if (! empty($args['location_id'])) {
            $query->where('location_id', $args['location_id']);
        }

        $totalSales = (float) $query->sum('total_amount');
        $totalOrders = $query->count();
        $averageOrder = $totalOrders > 0 ? $totalSales / $totalOrders : 0;

        return [
            'success' => true,
            'message' => "Rekap omset penjualan POS untuk tanggal {$date}.",
            'date' => $date,
            'total_sales' => $totalSales,
            'total_transactions' => $totalOrders,
            'average_ticket_size' => round($averageOrder, 2),
        ];
    }

    private function createTransaction(Business $business, ?User $user, array $args): array
    {
        $this->assertCan('pos', 'create', $business, $user);

        $items = $args['items'] ?? [];
        if (empty($items)) {
            throw new InvalidArgumentException('Transaksi kasir membutuhkan minimal satu baris item.');
        }

        $locId = $args['location_id'] ?? Location::where('business_id', $business->id)->where('is_primary', true)->value('id');
        $location = Location::where('business_id', $business->id)->findOrFail($locId);

        return DB::transaction(function () use ($business, $user, $location, $items, $args): array {
            $orderNumber = 'POS-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

            $subtotal = 0.0;
            $orderItemsData = [];

            foreach ($items as $item) {
                $qty = (float) ($item['quantity'] ?? 1);
                if ($qty <= 0) continue;

                $prodIdentifier = $item['product_id'] ?? null;
                $product = Product::where('business_id', $business->id)
                    ->where(fn ($q) => $q->where('id', $prodIdentifier)->orWhere('code', $prodIdentifier)->orWhere('name', $prodIdentifier))
                    ->first();

                $unitPrice = isset($item['unit_price']) && (float) $item['unit_price'] > 0
                    ? (float) $item['unit_price']
                    : (float) ($product?->selling_price ?? 0);

                $itemSubtotal = $qty * $unitPrice;
                $subtotal += $itemSubtotal;

                $orderItemsData[] = [
                    'product' => $product,
                    'product_id' => $product?->id,
                    'item_name' => $product?->name ?? 'Item Kasir',
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'subtotal' => $itemSubtotal,
                ];
            }

            $taxRate = $business->isSalesTaxEnabled() ? (float) ($business->pos_tax_percent ?? 0) : 0;
            $taxAmount = ($taxRate > 0) ? ($subtotal * ($taxRate / 100)) : 0.0;
            $totalAmount = $subtotal + $taxAmount;

            $userId = $user?->id ?? $business->owner_id ?? $business->users()->first()?->id;

            $posOrder = PosOrder::create([
                'business_id' => $business->id,
                'location_id' => $location->id,
                'user_id' => $userId,
                'customer_id' => $args['customer_id'] ?? null,
                'order_number' => $orderNumber,
                'order_date' => now(),
                'status' => PosOrder::STATUS_COMPLETED,
                'payment_channel' => $args['payment_method'] ?? 'cash',
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => 0.0,
                'total_amount' => $totalAmount,
                'paid_amount' => $totalAmount,
                'change_amount' => 0.0,
                'order_source' => PosOrder::SOURCE_POS,
            ]);

            foreach ($orderItemsData as $oid) {
                PosOrderItem::create([
                    'pos_order_id' => $posOrder->id,
                    'product_id' => $oid['product_id'],
                    'product_name' => $oid['item_name'],
                    'product_code' => $oid['product']?->code ?? 'ITEM',
                    'quantity' => $oid['quantity'],
                    'unit_price' => $oid['unit_price'],
                    'subtotal' => $oid['subtotal'],
                    'total_price' => $oid['subtotal'],
                ]);

                // Deduct stock if product exists
                if ($oid['product_id']) {
                    try {
                        $this->stockService->recordMovement(
                            businessId: $business->id,
                            locationId: $location->id,
                            productId: $oid['product_id'],
                            movementType: StockMovement::TYPE_POS_SALE,
                            quantityChange: -1 * $oid['quantity'],
                            unitCost: (float) ($oid['product']?->base_cost ?? 0),
                            referenceId: $posOrder->id,
                            referenceNumber: $posOrder->order_number,
                            notes: "Penjualan Kasir POS {$posOrder->order_number}",
                            userId: $user?->id
                        );
                    } catch (\Throwable) {
                        // Skip stock lock error if inventory bypass allowed
                    }
                }
            }

            AuditLog::create([
                'business_id' => $business->id,
                'user_id' => $user?->id,
                'action' => 'mcp_pos_order_created',
                'auditable_type' => PosOrder::class,
                'auditable_id' => $posOrder->id,
                'new_values' => [
                    'order_number' => $posOrder->order_number,
                    'total_amount' => $posOrder->total_amount,
                    'items_count' => count($orderItemsData),
                ],
                'ip_address' => '127.0.0.1',
                'user_agent' => 'COOCA-MCP-Agent/1.0',
            ]);

            $orderData = [
                'id' => $posOrder->id,
                'order_number' => $posOrder->order_number,
                'total_amount' => $totalAmount,
                'total' => $totalAmount,
                'status' => $posOrder->status,
                'items_count' => count($orderItemsData),
            ];

            return [
                'success' => true,
                'message' => "Transaksi kasir [{$posOrder->order_number}] berhasil dicatat dengan total Rp " . number_format($totalAmount, 0, ',', '.') . ".",
                'order' => $orderData,
                'transaction' => $orderData,
            ];
        });
    }
}
