<?php

declare(strict_types=1);

namespace App\Domain\Mcp\Tools;

use App\Models\AuditLog;
use App\Models\Business;
use App\Models\CostModel;
use App\Models\InvoiceItem;
use App\Models\PosOrderItem;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

final class ServicesManageServiceTool implements McpToolInterface
{
    public function getName(): string
    {
        return 'services_manage_service';
    }

    public function getDescription(): string
    {
        return 'Mengelola katalog jasa dan layanan bisnis (https://cooca.id/services): melihat daftar jasa & tarif, mendaftarkan layanan baru beserta harga jual & modal HPP, memperbarui tarif/status aktif, serta mengarsipkan atau menghapus jasa.';
    }

    public function getRequiredAbility(): string
    {
        return 'mcp:services:manage';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'action' => [
                    'type' => 'string',
                    'enum' => ['list', 'create', 'update', 'delete'],
                    'default' => 'list',
                    'description' => 'Aksi pengelolaan jasa: list (lihat daftar layanan), create (daftarkan jasa baru), update (perbarui tarif/data jasa), delete (hapus atau arsipkan jasa).',
                ],
                'search' => [
                    'type' => 'string',
                    'description' => 'Kata kunci pencarian nama, kode SKU, atau deskripsi jasa.',
                ],
                'service_id' => [
                    'type' => 'string',
                    'description' => 'UUID, kode SKU, atau nama persis jasa (diperlukan untuk update atau delete).',
                ],
                'name' => [
                    'type' => 'string',
                    'description' => 'Nama lengkap jasa/layanan (misal: "Servis Ringan Motor Matic", "Cuci Mobil Hidrolik", "Paket Foto Studio 1 Jam").',
                ],
                'code' => [
                    'type' => 'string',
                    'description' => 'Kode unik SKU layanan. Jika kosong pada create, sistem COOCA membuatkan kode otomatis (misal: SRV-XXXXXX).',
                ],
                'selling_price' => [
                    'type' => 'number',
                    'minimum' => 0,
                    'description' => 'Tarif resmi atau harga jual jasa ke konsumen dalam Rupiah.',
                ],
                'base_cost' => [
                    'type' => 'number',
                    'minimum' => 0,
                    'default' => 0,
                    'description' => 'Biaya dasar/HPP operasional per pengerjaan jasa dalam Rupiah.',
                ],
                'category_name' => [
                    'type' => 'string',
                    'description' => 'Nama kategori jasa (misal: "Perawatan Kendaraan", "Reparasi Elektronik", "Jasa Treatment").',
                ],
                'unit_name' => [
                    'type' => 'string',
                    'default' => 'jasa',
                    'description' => 'Satuan hitungan layanan (jasa, sesi, jam, paket, unit, org, pcs).',
                ],
                'description' => [
                    'type' => 'string',
                    'description' => 'Deskripsi lengkap atau cakupan pekerjaan layanan.',
                ],
                'is_active' => [
                    'type' => 'boolean',
                    'default' => true,
                    'description' => 'Status aktif atau nonaktif layanan dalam sistem.',
                ],
                'show_in_pos' => [
                    'type' => 'boolean',
                    'default' => true,
                    'description' => 'Tampilkan layanan di katalog terminal kasir POS.',
                ],
                'show_in_website' => [
                    'type' => 'boolean',
                    'default' => true,
                    'description' => 'Tampilkan layanan di website booking/toko online publik.',
                ],
                'show_in_sales_order' => [
                    'type' => 'boolean',
                    'default' => true,
                    'description' => 'Tampilkan layanan saat membuat dokumen penawaran dan Sales Order B2B.',
                ],
                'limit' => [
                    'type' => 'integer',
                    'minimum' => 1,
                    'maximum' => 100,
                    'default' => 20,
                    'description' => 'Maksimal jumlah layanan yang diambil pada aksi list.',
                ],
            ],
        ];
    }

    public function execute(Business $business, ?User $user, array $arguments = []): array
    {
        $action = trim((string) ($arguments['action'] ?? 'list'));

        return match ($action) {
            'create' => $this->createService($business, $user, $arguments),
            'update' => $this->updateService($business, $user, $arguments),
            'delete' => $this->deleteService($business, $user, $arguments),
            default  => $this->listServices($business, $arguments),
        };
    }

    /**
     * Tampilkan daftar jasa & layanan terdaftar.
     *
     * @param array<string, mixed> $args
     * @return array<string, mixed>
     */
    private function listServices(Business $business, array $args): array
    {
        $query = Product::services()
            ->where('business_id', $business->id)
            ->with(['category', 'outputUnit'])
            ->latest();

        if (! empty($args['search'])) {
            $search = trim((string) $args['search']);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (! empty($args['category_name'])) {
            $catName = trim((string) $args['category_name']);
            $query->whereHas('category', function ($q) use ($catName) {
                $q->where('name', 'like', "%{$catName}%");
            });
        }

        if (isset($args['is_active'])) {
            $query->where('is_active', (bool) $args['is_active']);
        }

        $limit = isset($args['limit']) ? max(1, min(100, (int) $args['limit'])) : 20;
        $totalCount = (clone $query)->count();
        $services = $query->take($limit)->get();

        $items = $services->map(function (Product $service) {
            $sellingPrice = (float) $service->selling_price;
            $baseCost = (float) $service->base_cost;
            $margin = $sellingPrice - $baseCost;
            $marginPct = $sellingPrice > 0 ? round(($margin / $sellingPrice) * 100, 1) : 0.0;

            return [
                'id'                  => (string) $service->id,
                'code'                => $service->code ?? '-',
                'name'                => $service->name,
                'category'            => $service->category?->name ?? 'Tanpa Kategori',
                'unit'                => $service->outputUnit?->name ?? 'Jasa',
                'selling_price'       => $sellingPrice,
                'selling_price_fmt'   => 'Rp ' . number_format($sellingPrice, 0, ',', '.'),
                'base_cost'           => $baseCost,
                'base_cost_fmt'       => 'Rp ' . number_format($baseCost, 0, ',', '.'),
                'margin_amount'       => $margin,
                'margin_percentage'   => $marginPct . '%',
                'is_active'           => (bool) $service->is_active,
                'show_in_pos'         => (bool) $service->show_in_pos,
                'show_in_website'     => (bool) $service->show_in_website,
                'show_in_sales_order' => (bool) $service->show_in_sales_order,
                'description'         => $service->description,
            ];
        })->all();

        return [
            'success'          => true,
            'module'           => 'Services & Layanan',
            'management_url'   => url('/services'),
            'total_services'   => $totalCount,
            'returned_count'   => count($items),
            'services'         => $items,
        ];
    }

    /**
     * Daftarkan jasa/layanan baru ke katalog bisnis.
     *
     * @param array<string, mixed> $args
     * @return array<string, mixed>
     */
    private function createService(Business $business, ?User $user, array $args): array
    {
        $name = trim((string) ($args['name'] ?? ''));
        if ($name === '') {
            throw new InvalidArgumentException('Nama jasa/layanan tidak boleh kosong.');
        }

        if (! isset($args['selling_price'])) {
            throw new InvalidArgumentException('Tarif harga jual (selling_price) jasa wajib diisi.');
        }

        $sellingPrice = (float) $args['selling_price'];
        if ($sellingPrice < 0) {
            throw new InvalidArgumentException('Tarif harga jual jasa tidak boleh bernilai negatif.');
        }

        $baseCost = max(0.0, (float) ($args['base_cost'] ?? 0));

        // Generate or normalize SKU code
        $code = ! empty($args['code']) ? trim((string) $args['code']) : 'SRV-' . strtoupper(Str::random(6));

        // Ensure unique code in business tenant
        $existingCodeCount = Product::where('business_id', $business->id)->where('code', $code)->count();
        if ($existingCodeCount > 0) {
            $code .= '-' . strtoupper(Str::random(3));
        }

        // Resolve or auto-create Category
        $categoryId = null;
        if (! empty($args['category_name'])) {
            $catName = trim((string) $args['category_name']);
            $cat = ProductCategory::firstOrCreate(
                ['business_id' => $business->id, 'name' => $catName],
                ['business_id' => $business->id, 'name' => $catName, 'slug' => Str::slug($catName)]
            );
            $categoryId = $cat->id;
        }

        // Resolve or create Unit
        $unitName = ! empty($args['unit_name']) ? strtolower(trim((string) $args['unit_name'])) : 'jasa';
        $unit = Unit::where('business_id', $business->id)->where('name', $unitName)->first()
            ?? Unit::whereNull('business_id')->where('name', $unitName)->first()
            ?? Unit::whereIn('code', ['jasa', 'sesi', 'jam', 'paket', 'unit', 'pcs'])->first()
            ?? Unit::firstOrCreate(
                ['business_id' => $business->id, 'name' => $unitName],
                [
                    'code'              => strtoupper(substr($unitName, 0, 10)),
                    'category'          => 'quantity',
                    'is_base'           => true,
                    'default_precision' => 0,
                ]
            );

        $description = ! empty($args['description']) ? trim((string) $args['description']) : null;
        $isActive = isset($args['is_active']) ? (bool) $args['is_active'] : true;
        $showInPos = isset($args['show_in_pos']) ? (bool) $args['show_in_pos'] : true;
        $showInWebsite = isset($args['show_in_website']) ? (bool) $args['show_in_website'] : true;
        $showInSalesOrder = isset($args['show_in_sales_order']) ? (bool) $args['show_in_sales_order'] : true;

        $service = DB::transaction(function () use (
            $business,
            $user,
            $name,
            $code,
            $categoryId,
            $unit,
            $sellingPrice,
            $baseCost,
            $description,
            $isActive,
            $showInPos,
            $showInWebsite,
            $showInSalesOrder
        ) {
            /** @var Product $service */
            $service = Product::create([
                'business_id'         => $business->id,
                'type'                => Product::TYPE_SERVICE,
                'category_id'         => $categoryId,
                'output_unit_id'      => $unit?->id,
                'code'                => $code,
                'name'                => $name,
                'description'         => $description,
                'business_type_hint'  => 'service',
                'selling_price'       => $sellingPrice,
                'base_cost'           => $baseCost,
                'min_stock'           => 0,
                'is_active'           => $isActive,
                'show_in_website'     => $showInWebsite,
                'show_in_pos'         => $showInPos,
                'show_in_sales_order' => $showInSalesOrder,
                'show_price_on_web'   => true,
            ]);

            // Auto create CostModel for Service Pricing
            CostModel::firstOrCreate(
                ['business_id' => $business->id, 'product_id' => $service->id],
                [
                    'name'      => 'Model Tarif ' . $service->name,
                    'method'    => CostModel::METHOD_SERVICE,
                    'is_active' => true,
                ]
            );

            // Audit log immutable
            AuditLog::create([
                'business_id'    => $business->id,
                'user_id'        => $user?->id,
                'action'         => 'mcp.services_manage_service.created',
                'auditable_type' => Product::class,
                'auditable_id'   => $service->id,
                'payload_after'  => [
                    'name'          => $name,
                    'code'          => $code,
                    'selling_price' => $sellingPrice,
                    'base_cost'     => $baseCost,
                    'category_id'   => $categoryId,
                ],
            ]);

            return $service;
        });

        $margin = $sellingPrice - $baseCost;
        $marginPct = $sellingPrice > 0 ? round(($margin / $sellingPrice) * 100, 1) : 0.0;

        return [
            'success'        => true,
            'message'        => "Jasa '{$service->name}' berhasil didaftarkan ke katalog layanan COOCA.",
            'management_url' => url('/services'),
            'service'        => [
                'id'                => (string) $service->id,
                'code'              => $service->code,
                'name'              => $service->name,
                'category'          => $service->category?->name ?? 'Tanpa Kategori',
                'unit'              => $unit?->name ?? 'Jasa',
                'selling_price'     => $sellingPrice,
                'selling_price_fmt' => 'Rp ' . number_format($sellingPrice, 0, ',', '.'),
                'base_cost'         => $baseCost,
                'base_cost_fmt'     => 'Rp ' . number_format($baseCost, 0, ',', '.'),
                'margin_amount'     => $margin,
                'margin_percentage' => $marginPct . '%',
                'is_active'         => (bool) $service->is_active,
                'show_in_pos'       => (bool) $service->show_in_pos,
                'show_in_website'   => (bool) $service->show_in_website,
            ],
        ];
    }

    /**
     * Perbarui data atau tarif jasa yang sudah ada.
     *
     * @param array<string, mixed> $args
     * @return array<string, mixed>
     */
    private function updateService(Business $business, ?User $user, array $args): array
    {
        $identifier = trim((string) ($args['service_id'] ?? ''));
        if ($identifier === '') {
            throw new InvalidArgumentException('Parameter service_id (UUID, kode SKU, atau nama jasa) wajib diisi untuk melakukan update.');
        }

        $service = $this->findService($business, $identifier);
        if ($service === null) {
            throw new InvalidArgumentException("Jasa/layanan dengan pengenal '{$identifier}' tidak ditemukan dalam katalog bisnis Anda.");
        }

        $updates = [];
        $payloadBefore = [
            'name'          => $service->name,
            'selling_price' => (float) $service->selling_price,
            'base_cost'     => (float) $service->base_cost,
            'is_active'     => (bool) $service->is_active,
        ];

        if (! empty($args['name'])) {
            $updates['name'] = trim((string) $args['name']);
        }

        if (isset($args['selling_price'])) {
            $price = (float) $args['selling_price'];
            if ($price < 0) {
                throw new InvalidArgumentException('Tarif harga jual jasa tidak boleh negatif.');
            }
            $updates['selling_price'] = $price;
        }

        if (isset($args['base_cost'])) {
            $updates['base_cost'] = max(0.0, (float) $args['base_cost']);
        }

        if (! empty($args['code'])) {
            $newCode = trim((string) $args['code']);
            $existing = Product::where('business_id', $business->id)
                ->where('code', $newCode)
                ->where('id', '!=', $service->id)
                ->exists();
            if ($existing) {
                throw new InvalidArgumentException("Kode SKU layanan '{$newCode}' sudah digunakan oleh produk/jasa lain.");
            }
            $updates['code'] = $newCode;
        }

        if (! empty($args['category_name'])) {
            $catName = trim((string) $args['category_name']);
            $cat = ProductCategory::firstOrCreate(
                ['business_id' => $business->id, 'name' => $catName],
                ['slug' => Str::slug($catName)]
            );
            $updates['category_id'] = $cat->id;
        }

        if (! empty($args['unit_name'])) {
            $unitName = strtolower(trim((string) $args['unit_name']));
            $unit = Unit::where('business_id', $business->id)->where('name', $unitName)->first()
                ?? Unit::whereNull('business_id')->where('name', $unitName)->first()
                ?? Unit::firstOrCreate(
                    ['business_id' => $business->id, 'name' => $unitName],
                    ['code' => strtoupper(substr($unitName, 0, 10)), 'category' => 'quantity', 'is_base' => true]
                );
            $updates['output_unit_id'] = $unit->id;
        }

        if (isset($args['description'])) {
            $updates['description'] = trim((string) $args['description']);
        }

        if (isset($args['is_active'])) {
            $updates['is_active'] = (bool) $args['is_active'];
        }

        if (isset($args['show_in_pos'])) {
            $updates['show_in_pos'] = (bool) $args['show_in_pos'];
        }

        if (isset($args['show_in_website'])) {
            $updates['show_in_website'] = (bool) $args['show_in_website'];
        }

        if (isset($args['show_in_sales_order'])) {
            $updates['show_in_sales_order'] = (bool) $args['show_in_sales_order'];
        }

        if (empty($updates)) {
            return [
                'success' => true,
                'message' => "Tidak ada perubahan data pada layanan '{$service->name}'.",
                'service' => [
                    'id'            => (string) $service->id,
                    'name'          => $service->name,
                    'selling_price' => (float) $service->selling_price,
                ],
            ];
        }

        DB::transaction(function () use ($service, $updates, $business, $user, $payloadBefore) {
            $service->update($updates);

            AuditLog::create([
                'business_id'    => $business->id,
                'user_id'        => $user?->id,
                'action'         => 'mcp.services_manage_service.updated',
                'auditable_type' => Product::class,
                'auditable_id'   => $service->id,
                'payload_before' => $payloadBefore,
                'payload_after'  => $updates,
            ]);
        });

        $service->refresh()->load(['category', 'outputUnit']);
        $sellingPrice = (float) $service->selling_price;
        $baseCost = (float) $service->base_cost;
        $margin = $sellingPrice - $baseCost;
        $marginPct = $sellingPrice > 0 ? round(($margin / $sellingPrice) * 100, 1) : 0.0;

        return [
            'success'        => true,
            'message'        => "Data jasa '{$service->name}' berhasil diperbarui.",
            'management_url' => url('/services'),
            'service'        => [
                'id'                => (string) $service->id,
                'code'              => $service->code,
                'name'              => $service->name,
                'category'          => $service->category?->name ?? 'Tanpa Kategori',
                'unit'              => $service->outputUnit?->name ?? 'Jasa',
                'selling_price'     => $sellingPrice,
                'selling_price_fmt' => 'Rp ' . number_format($sellingPrice, 0, ',', '.'),
                'base_cost'         => $baseCost,
                'base_cost_fmt'     => 'Rp ' . number_format($baseCost, 0, ',', '.'),
                'margin_amount'     => $margin,
                'margin_percentage' => $marginPct . '%',
                'is_active'         => (bool) $service->is_active,
                'show_in_pos'       => (bool) $service->show_in_pos,
                'show_in_website'   => (bool) $service->show_in_website,
            ],
        ];
    }

    /**
     * Hapus atau arsipkan jasa dengan guard transaksi finansial.
     *
     * @param array<string, mixed> $args
     * @return array<string, mixed>
     */
    private function deleteService(Business $business, ?User $user, array $args): array
    {
        $identifier = trim((string) ($args['service_id'] ?? ''));
        if ($identifier === '') {
            throw new InvalidArgumentException('Parameter service_id (UUID, kode SKU, atau nama jasa) wajib diisi untuk menghapus/mengarsipkan.');
        }

        $service = $this->findService($business, $identifier);
        if ($service === null) {
            throw new InvalidArgumentException("Jasa/layanan dengan pengenal '{$identifier}' tidak ditemukan dalam katalog bisnis Anda.");
        }

        $name = $service->name;
        $code = $service->code;

        // Check if service has existing transactions in POS or Invoices
        $hasPosOrders = PosOrderItem::where('product_id', $service->id)->exists();
        $hasInvoiceItems = InvoiceItem::where('product_id', $service->id)->exists();

        if ($hasPosOrders || $hasInvoiceItems) {
            // Financial History Guard: Gracefully archive instead of hard delete
            $service->update(['is_active' => false]);

            AuditLog::create([
                'business_id'    => $business->id,
                'user_id'        => $user?->id,
                'action'         => 'mcp.services_manage_service.archived',
                'auditable_type' => Product::class,
                'auditable_id'   => $service->id,
                'payload_after'  => ['is_active' => false, 'reason' => 'has_financial_transactions'],
            ]);

            return [
                'success' => true,
                'status'  => 'archived',
                'message' => "Jasa '{$name}' ({$code}) memiliki riwayat transaksi di kasir POS atau faktur invoice. Demi integritas pelaporan keuangan, jasa otomatis diarsipkan (dinonaktifkan) dan tidak dihapus permanen.",
                'service' => [
                    'id'        => (string) $service->id,
                    'name'      => $name,
                    'code'      => $code,
                    'is_active' => false,
                ],
            ];
        }

        // Safe to permanently delete
        DB::transaction(function () use ($service, $business, $user, $name, $code) {
            CostModel::where('business_id', $business->id)->where('product_id', $service->id)->delete();
            $service->delete();

            AuditLog::create([
                'business_id'    => $business->id,
                'user_id'        => $user?->id,
                'action'         => 'mcp.services_manage_service.deleted',
                'auditable_type' => Product::class,
                'auditable_id'   => $service->id,
                'payload_before' => ['name' => $name, 'code' => $code],
            ]);
        });

        return [
            'success' => true,
            'status'  => 'deleted',
            'message' => "Jasa '{$name}' ({$code}) berhasil dihapus permanen dari katalog bisnis.",
        ];
    }

    /**
     * Temukan entitas jasa dalam isolasi tenant bisnis.
     */
    private function findService(Business $business, string $identifier): ?Product
    {
        // 1. Coba cari via UUID ID
        $service = Product::services()
            ->where('business_id', $business->id)
            ->where('id', $identifier)
            ->first();

        // 2. Coba cari via Code/SKU
        if ($service === null) {
            $service = Product::services()
                ->where('business_id', $business->id)
                ->where('code', $identifier)
                ->first();
        }

        // 3. Coba cari via exact name
        if ($service === null) {
            $service = Product::services()
                ->where('business_id', $business->id)
                ->where('name', $identifier)
                ->first();
        }

        // 4. Fallback like search
        if ($service === null) {
            $service = Product::services()
                ->where('business_id', $business->id)
                ->where('name', 'like', "%{$identifier}%")
                ->first();
        }

        return $service;
    }
}
