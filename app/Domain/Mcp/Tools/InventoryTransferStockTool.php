<?php

declare(strict_types=1);

namespace App\Domain\Mcp\Tools;

use App\Domain\Inventory\StockService;
use App\Domain\Mcp\Tools\Traits\HandlesCrudPermissions;
use App\Models\AuditLog;
use App\Models\Business;
use App\Models\Location;
use App\Models\Material;
use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class InventoryTransferStockTool implements McpToolInterface
{
    use HandlesCrudPermissions;

    public function __construct(
        private readonly StockService $stockService = new StockService()
    ) {}

    public function getName(): string
    {
        return 'inventory_transfer_stock';
    }

    public function getDescription(): string
    {
        return 'Mengelola mutasi dan transfer stok antar-gudang atau antar-cabang (misal dari Outlet ke Sub-Gudang): melihat riwayat transfer stok, membuat dokumen transfer stok, dan menyelesaikan penerimaan transfer barang masuk.';
    }

    public function getRequiredAbility(): string
    {
        return 'mcp:inventory:read';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'action' => [
                    'type' => 'string',
                    'enum' => ['list', 'create', 'complete'],
                    'default' => 'list',
                    'description' => 'Aksi: list (daftar mutasi transfer), create (buat transfer baru & kirim barang), complete (selesaikan penerimaan barang di gudang tujuan).',
                ],
                'source_location_id' => [
                    'type' => 'string',
                    'description' => 'UUID atau kode lokasi/gudang asal pengirim (wajib untuk create).',
                ],
                'destination_location_id' => [
                    'type' => 'string',
                    'description' => 'UUID atau kode lokasi/gudang tujuan penerima (wajib untuk create).',
                ],
                'transfer_id' => [
                    'type' => 'string',
                    'description' => 'UUID atau nomor transfer stok (wajib untuk action complete).',
                ],
                'notes' => [
                    'type' => 'string',
                    'description' => 'Catatan transfer stok.',
                ],
                'items' => [
                    'type' => 'array',
                    'description' => 'Daftar item produk atau bahan baku yang dipindahkan.',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'item_type' => [
                                'type' => 'string',
                                'enum' => ['product', 'material'],
                                'default' => 'product',
                            ],
                            'product_id' => [
                                'type' => 'string',
                                'description' => 'UUID atau SKU produk.',
                            ],
                            'material_id' => [
                                'type' => 'string',
                                'description' => 'UUID atau kode bahan baku.',
                            ],
                            'quantity' => [
                                'type' => 'number',
                                'description' => 'Jumlah barang yang dipindahkan.',
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
            'create'   => $this->createTransfer($business, $user, $arguments),
            'complete' => $this->completeTransfer($business, $user, $arguments),
            default    => $this->listTransfers($business, $user, $arguments),
        };
    }

    private function listTransfers(Business $business, ?User $user, array $args): array
    {
        $this->assertCan('inventory', 'read', $business, $user);

        $query = StockTransfer::where('business_id', $business->id)
            ->with(['sourceLocation:id,name,code', 'destinationLocation:id,name,code', 'items'])
            ->latest('created_at')
            ->take(20);

        $transfers = $query->get();

        return [
            'success' => true,
            'message' => 'Berhasil mengambil daftar transfer stok antar-gudang.',
            'count' => $transfers->count(),
            'transfers' => $transfers->map(fn (StockTransfer $t) => [
                'id' => $t->id,
                'transfer_number' => $t->transfer_number,
                'status' => $t->status,
                'source' => $t->sourceLocation?->name,
                'destination' => $t->destinationLocation?->name,
                'transfer_date' => $t->transfer_date?->format('Y-m-d'),
                'notes' => $t->notes,
                'items_count' => $t->items->count(),
                'items' => $t->items->map(fn (StockTransferItem $item) => [
                    'product_id' => $item->product_id,
                    'material_id' => $item->material_id,
                    'quantity' => (float) $item->quantity,
                ])->all(),
            ])->all(),
        ];
    }

    private function createTransfer(Business $business, ?User $user, array $args): array
    {
        $this->assertCan('inventory', 'create', $business, $user);

        $srcId = $args['source_location_id'] ?? null;
        $dstId = $args['destination_location_id'] ?? null;

        if (empty($srcId) || empty($dstId)) {
            throw new InvalidArgumentException('source_location_id dan destination_location_id wajib diisi.');
        }

        if ($srcId === $dstId) {
            throw new InvalidArgumentException('Gudang asal dan gudang tujuan tidak boleh sama.');
        }

        $source = Location::where('business_id', $business->id)
            ->where(fn ($q) => $q->where('id', $srcId)->orWhere('code', $srcId))
            ->first();

        $destination = Location::where('business_id', $business->id)
            ->where(fn ($q) => $q->where('id', $dstId)->orWhere('code', $dstId))
            ->first();

        if (! $source || ! $destination) {
            throw new InvalidArgumentException('Gudang asal atau tujuan tidak ditemukan pada bisnis ini.');
        }

        $items = $args['items'] ?? [];
        if (empty($items)) {
            throw new InvalidArgumentException('Transfer stok membutuhkan minimal satu baris item.');
        }

        return DB::transaction(function () use ($business, $user, $source, $destination, $items, $args): array {
            $transferNum = 'TRF-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

            $transfer = StockTransfer::create([
                'business_id' => $business->id,
                'source_location_id' => $source->id,
                'destination_location_id' => $destination->id,
                'transfer_number' => $transferNum,
                'transfer_date' => Carbon::today(),
                'status' => StockTransfer::STATUS_PENDING,
                'notes' => $args['notes'] ?? 'Transfer stok via MCP AI Agent',
                'created_by' => $user?->id,
            ]);

            foreach ($items as $item) {
                $qty = (float) ($item['quantity'] ?? 0);
                if ($qty <= 0) continue;

                $prodId = $item['product_id'] ?? null;
                $matId = $item['material_id'] ?? null;

                if ($prodId) {
                    $prod = Product::where('business_id', $business->id)->where(fn ($q) => $q->where('id', $prodId)->orWhere('code', $prodId))->first();
                    $prodId = $prod?->id;
                }
                if ($matId) {
                    $mat = Material::where('business_id', $business->id)->where(fn ($q) => $q->where('id', $matId)->orWhere('code', $matId))->first();
                    $matId = $mat?->id;
                }

                StockTransferItem::create([
                    'stock_transfer_id' => $transfer->id,
                    'product_id' => $prodId,
                    'material_id' => $matId,
                    'quantity' => $qty,
                ]);
            }

            // Auto-dispatch and complete directly for seamless AI execution if user is available
            $activeUser = $user ?? User::where('active_business_id', $business->id)->first() ?? User::first();
            if ($activeUser) {
                $this->stockService->dispatchStockTransfer($transfer, $activeUser);
                $this->stockService->completeStockTransfer($transfer, $activeUser);
            }

            AuditLog::create([
                'business_id' => $business->id,
                'user_id' => $user?->id,
                'action' => 'mcp_stock_transfer_created',
                'auditable_type' => StockTransfer::class,
                'auditable_id' => $transfer->id,
                'new_values' => [
                    'transfer_number' => $transfer->transfer_number,
                    'source' => $source->name,
                    'destination' => $destination->name,
                    'items_count' => count($items),
                    'status' => $transfer->fresh()->status,
                ],
                'ip_address' => '127.0.0.1',
                'user_agent' => 'COOCA-MCP-Agent/1.0',
            ]);

            return [
                'success' => true,
                'message' => "Transfer stok [{$transfer->transfer_number}] dari [{$source->name}] ke [{$destination->name}] berhasil diproses dan diselesaikan.",
                'transfer' => [
                    'id' => $transfer->id,
                    'transfer_number' => $transfer->transfer_number,
                    'source' => $source->name,
                    'destination' => $destination->name,
                    'status' => $transfer->fresh()->status,
                    'items_count' => count($items),
                ],
            ];
        });
    }

    private function completeTransfer(Business $business, ?User $user, array $args): array
    {
        $this->assertCan('inventory', 'update', $business, $user);

        $id = $args['transfer_id'] ?? null;
        if (! $id) {
            throw new InvalidArgumentException('transfer_id wajib diisi untuk menyelesaikan transfer stok.');
        }

        $transfer = StockTransfer::where('business_id', $business->id)
            ->where(fn ($q) => $q->where('id', $id)->orWhere('transfer_number', $id))
            ->firstOrFail();

        $activeUser = $user ?? User::where('active_business_id', $business->id)->first() ?? User::first();
        if (! $activeUser) {
            throw new InvalidArgumentException('User otorisasi tidak ditemukan.');
        }

        if ($transfer->status === StockTransfer::STATUS_PENDING) {
            $this->stockService->dispatchStockTransfer($transfer, $activeUser);
        }

        if ($transfer->status !== StockTransfer::STATUS_RECEIVED) {
            $this->stockService->completeStockTransfer($transfer, $activeUser);
        }

        return [
            'success' => true,
            'message' => "Transfer stok [{$transfer->transfer_number}] berhasil diterima di gudang tujuan.",
            'status' => $transfer->fresh()->status,
        ];
    }
}
