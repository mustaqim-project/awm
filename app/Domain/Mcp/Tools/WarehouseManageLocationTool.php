<?php

declare(strict_types=1);

namespace App\Domain\Mcp\Tools;

use App\Domain\Mcp\Tools\Traits\HandlesCrudPermissions;
use App\Models\AuditLog;
use App\Models\Business;
use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Str;
use InvalidArgumentException;

final class WarehouseManageLocationTool implements McpToolInterface
{
    use HandlesCrudPermissions;

    public function getName(): string
    {
        return 'warehouse_manage_location';
    }

    public function getDescription(): string
    {
        return 'Mengelola outlet, gudang utama, dan sub-gudang (multi-gudang dalam 1 lokasi): melihat daftar lokasi dan hierarki sub-gudang, membuat outlet atau gudang baru dengan relasi lokasi induk (parent_id), serta mengubah rincian gudang.';
    }

    public function getRequiredAbility(): string
    {
        return 'mcp:warehouse:read';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'action' => [
                    'type' => 'string',
                    'enum' => ['list', 'create', 'update', 'get', 'delete'],
                    'default' => 'list',
                    'description' => 'Aksi: list (daftar lokasi & sub-gudang), create (tambah gudang/outlet baru), update (ubah gudang/outlet), get (detail lokasi & saldo stok), delete (hapus gudang).',
                ],
                'location_id' => [
                    'type' => 'string',
                    'description' => 'UUID atau kode lokasi (wajib untuk action update dan get).',
                ],
                'name' => [
                    'type' => 'string',
                    'description' => 'Nama gudang atau outlet (misal: "Outlet Sudirman", "Gudang Belakang - Sudirman", "Chiller Bahan Baku").',
                ],
                'type' => [
                    'type' => 'string',
                    'enum' => ['warehouse', 'outlet', 'store', 'central_kitchen'],
                    'default' => 'warehouse',
                    'description' => 'Tipe lokasi: warehouse (gudang/ruang simpan), outlet (toko penjualan), store, atau central_kitchen.',
                ],
                'parent_id' => [
                    'type' => 'string',
                    'description' => 'UUID lokasi induk. Jika diisi, gudang ini menjadi sub-gudang di dalam lokasi fisik induk tersebut (multi-gudang dalam 1 lokasi).',
                ],
                'parent_name' => [
                    'type' => 'string',
                    'description' => 'Nama lokasi induk untuk pencarian otomatis jika parent_id tidak diketahui.',
                ],
                'code' => [
                    'type' => 'string',
                    'description' => 'Kode unik lokasi/gudang (misal: WH-SDR-01). Otomatis dibuat jika dikosongkan.',
                ],
                'phone' => [
                    'type' => 'string',
                    'description' => 'Nomor telepon operasional gudang atau outlet.',
                ],
                'address' => [
                    'type' => 'string',
                    'description' => 'Alamat fisik lokasi/gudang.',
                ],
                'province' => [
                    'type' => 'string',
                    'description' => 'Provinsi lokasi.',
                ],
                'city' => [
                    'type' => 'string',
                    'description' => 'Kota/Kabupaten lokasi.',
                ],
                'district' => [
                    'type' => 'string',
                    'description' => 'Kecamatan lokasi.',
                ],
                'postal_code' => [
                    'type' => 'string',
                    'description' => 'Kode pos.',
                ],
                'is_primary' => [
                    'type' => 'boolean',
                    'description' => 'Apakah lokasi ini merupakan lokasi utama bisnis.',
                ],
                'is_active' => [
                    'type' => 'boolean',
                    'description' => 'Status aktif operasional lokasi.',
                ],
                'search' => [
                    'type' => 'string',
                    'description' => 'Kata kunci pencarian nama atau kode lokasi saat list.',
                ],
            ],
        ];
    }

    public function execute(Business $business, ?User $user, array $arguments = []): array
    {
        $action = trim((string) ($arguments['action'] ?? 'list'));

        return match ($action) {
            'create' => $this->createLocationWithPermission($business, $user, $arguments),
            'update' => $this->updateLocationWithPermission($business, $user, $arguments),
            'delete' => $this->deleteLocationWithPermission($business, $user, $arguments),
            'get'    => $this->getLocationWithPermission($business, $user, $arguments),
            default  => $this->listLocationsWithPermission($business, $user, $arguments),
        };
    }

    private function listLocationsWithPermission(Business $business, ?User $user, array $args): array
    {
        $this->assertCan('warehouse', 'read', $business, $user);
        return $this->listLocations($business, $args);
    }

    private function getLocationWithPermission(Business $business, ?User $user, array $args): array
    {
        $this->assertCan('warehouse', 'read', $business, $user);
        return $this->getLocation($business, $args);
    }

    private function createLocationWithPermission(Business $business, ?User $user, array $args): array
    {
        $this->assertCan('warehouse', 'create', $business, $user);
        return $this->createLocation($business, $user, $args);
    }

    private function updateLocationWithPermission(Business $business, ?User $user, array $args): array
    {
        $this->assertCan('warehouse', 'update', $business, $user);
        return $this->updateLocation($business, $user, $args);
    }

    private function deleteLocationWithPermission(Business $business, ?User $user, array $args): array
    {
        $this->assertCan('warehouse', 'delete', $business, $user);

        $id = $args['location_id'] ?? null;
        if (! $id) {
            throw new InvalidArgumentException('location_id wajib diisi untuk menghapus lokasi/gudang.');
        }

        $location = Location::where('business_id', $business->id)
            ->where(function ($q) use ($id): void {
                $q->where('id', $id)->orWhere('code', $id);
            })
            ->first();

        if (! $location) {
            throw new InvalidArgumentException("Lokasi [{$id}] tidak ditemukan.");
        }

        if ($location->is_primary) {
            throw new InvalidArgumentException('Lokasi utama (primary) tidak dapat dihapus.');
        }

        $locName = $location->name;
        $location->delete();

        AuditLog::create([
            'business_id' => $business->id,
            'user_id' => $user?->id,
            'action' => 'mcp_delete_location',
            'auditable_type' => Location::class,
            'auditable_id' => $id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'COOCA-MCP-Agent/1.0',
        ]);

        return [
            'success' => true,
            'message' => "Lokasi [{$locName}] berhasil dihapus.",
        ];
    }

    private function listLocations(Business $business, array $args): array
    {
        $query = Location::where('business_id', $business->id)
            ->with(['parent:id,name,type,code', 'childWarehouses:id,parent_id,name,type,code,is_active']);

        if (! empty($args['search'])) {
            $s = trim((string) $args['search']);
            $query->where(function ($q) use ($s): void {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('code', 'like', "%{$s}%");
            });
        }

        if (! empty($args['type'])) {
            $query->where('type', $args['type']);
        }

        $locations = $query->orderBy('is_primary', 'desc')->orderBy('name')->get();

        return [
            'success' => true,
            'message' => 'Berhasil mengambil daftar gudang dan outlet.',
            'count' => $locations->count(),
            'locations' => $locations->map(fn (Location $loc) => [
                'id' => $loc->id,
                'name' => $loc->name,
                'code' => $loc->code,
                'type' => $loc->type,
                'is_primary' => (bool) $loc->is_primary,
                'is_active' => (bool) $loc->is_active,
                'parent_id' => $loc->parent_id,
                'parent_name' => $loc->parent?->name,
                'is_sub_warehouse' => $loc->isSubWarehouse(),
                'is_central_warehouse' => $loc->isCentralWarehouse(),
                'address' => $loc->address,
                'city' => $loc->city,
                'child_warehouses_count' => $loc->childWarehouses->count(),
                'child_warehouses' => $loc->childWarehouses->map(fn (Location $cw) => [
                    'id' => $cw->id,
                    'name' => $cw->name,
                    'code' => $cw->code,
                    'is_active' => (bool) $cw->is_active,
                ])->all(),
            ])->all(),
        ];
    }

    private function createLocation(Business $business, ?User $user, array $args): array
    {
        $name = trim((string) ($args['name'] ?? ''));
        if (empty($name)) {
            throw new InvalidArgumentException('Nama gudang/outlet wajib diisi.');
        }

        $type = $args['type'] ?? 'warehouse';
        $parentId = $args['parent_id'] ?? null;

        // Auto-resolve parent_id by parent_name if provided
        if (empty($parentId) && ! empty($args['parent_name'])) {
            $parent = Location::where('business_id', $business->id)
                ->where('name', 'like', '%' . trim((string) $args['parent_name']) . '%')
                ->first();
            $parentId = $parent?->id;
        }

        if ($parentId) {
            $parentExists = Location::where('business_id', $business->id)->where('id', $parentId)->exists();
            if (! $parentExists) {
                throw new InvalidArgumentException("Lokasi induk dengan ID [{$parentId}] tidak ditemukan pada bisnis ini.");
            }
        }

        $code = ! empty($args['code'])
            ? trim((string) $args['code'])
            : ($type === 'warehouse' ? 'WH-' : 'OUT-') . strtoupper(Str::random(5));

        $slugBase = Str::slug($name);
        $slug = $slugBase;
        $counter = 1;
        while (Location::where('business_id', $business->id)->where('slug', $slug)->exists()) {
            $slug = "{$slugBase}-{$counter}";
            $counter++;
        }

        $isPrimary = (bool) ($args['is_primary'] ?? false);
        if ($isPrimary) {
            Location::where('business_id', $business->id)->update(['is_primary' => false]);
        }

        $location = Location::create([
            'business_id' => $business->id,
            'parent_id' => $parentId,
            'name' => $name,
            'slug' => $slug,
            'type' => $type,
            'code' => $code,
            'phone' => $args['phone'] ?? null,
            'address' => $args['address'] ?? null,
            'province' => $args['province'] ?? null,
            'city' => $args['city'] ?? null,
            'district' => $args['district'] ?? null,
            'postal_code' => $args['postal_code'] ?? null,
            'is_primary' => $isPrimary,
            'is_active' => $args['is_active'] ?? true,
        ]);

        AuditLog::create([
            'business_id' => $business->id,
            'user_id' => $user?->id,
            'action' => 'mcp_create_location',
            'auditable_type' => Location::class,
            'auditable_id' => $location->id,
            'old_values' => null,
            'new_values' => [
                'name' => $location->name,
                'code' => $location->code,
                'type' => $location->type,
                'parent_id' => $location->parent_id,
                'is_sub_warehouse' => $location->isSubWarehouse(),
            ],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'COOCA-MCP-Agent/1.0',
        ]);

        $location->load('parent');

        return [
            'success' => true,
            'message' => "Lokasi [{$location->name}] ({$location->type}) berhasil dibuat." . ($location->parent ? " Terhubung sebagai sub-gudang pada lokasi induk [{$location->parent->name}]." : ''),
            'location' => [
                'id' => $location->id,
                'name' => $location->name,
                'code' => $location->code,
                'type' => $location->type,
                'parent_id' => $location->parent_id,
                'parent_name' => $location->parent?->name,
                'is_sub_warehouse' => $location->isSubWarehouse(),
                'is_primary' => (bool) $location->is_primary,
                'is_active' => (bool) $location->is_active,
            ],
        ];
    }

    private function updateLocation(Business $business, ?User $user, array $args): array
    {
        $id = $args['location_id'] ?? null;
        if (! $id) {
            throw new InvalidArgumentException('location_id wajib diisi untuk mengubah data lokasi.');
        }

        /** @var Location|null $location */
        $location = Location::where('business_id', $business->id)
            ->where(function ($q) use ($id): void {
                $q->where('id', $id)->orWhere('code', $id);
            })
            ->first();

        if (! $location) {
            throw new InvalidArgumentException("Lokasi dengan ID/Kode [{$id}] tidak ditemukan.");
        }

        $oldValues = $location->toArray();

        if (isset($args['name']) && trim((string) $args['name']) !== '') {
            $location->name = trim((string) $args['name']);
        }

        if (isset($args['type'])) {
            $location->type = (string) $args['type'];
        }

        if (array_key_exists('parent_id', $args)) {
            $parentId = $args['parent_id'];
            if (! empty($parentId)) {
                $parentExists = Location::where('business_id', $business->id)->where('id', $parentId)->exists();
                if (! $parentExists) {
                    throw new InvalidArgumentException("Lokasi induk [{$parentId}] tidak ditemukan.");
                }
            }
            $location->parent_id = $parentId ?: null;
        }

        if (isset($args['code'])) {
            $location->code = trim((string) $args['code']);
        }

        if (isset($args['phone'])) {
            $location->phone = trim((string) $args['phone']);
        }

        if (isset($args['address'])) {
            $location->address = trim((string) $args['address']);
        }

        if (isset($args['city'])) {
            $location->city = trim((string) $args['city']);
        }

        if (isset($args['province'])) {
            $location->province = trim((string) $args['province']);
        }

        if (isset($args['is_active'])) {
            $location->is_active = (bool) $args['is_active'];
        }

        if (isset($args['is_primary']) && (bool) $args['is_primary']) {
            Location::where('business_id', $business->id)->where('id', '!=', $location->id)->update(['is_primary' => false]);
            $location->is_primary = true;
        }

        $location->save();

        AuditLog::create([
            'business_id' => $business->id,
            'user_id' => $user?->id,
            'action' => 'mcp_update_location',
            'auditable_type' => Location::class,
            'auditable_id' => $location->id,
            'old_values' => $oldValues,
            'new_values' => $location->toArray(),
            'ip_address' => '127.0.0.1',
            'user_agent' => 'COOCA-MCP-Agent/1.0',
        ]);

        $location->load('parent');

        return [
            'success' => true,
            'message' => "Lokasi [{$location->name}] berhasil diperbarui.",
            'location' => [
                'id' => $location->id,
                'name' => $location->name,
                'code' => $location->code,
                'type' => $location->type,
                'parent_id' => $location->parent_id,
                'parent_name' => $location->parent?->name,
                'is_sub_warehouse' => $location->isSubWarehouse(),
                'is_primary' => (bool) $location->is_primary,
                'is_active' => (bool) $location->is_active,
            ],
        ];
    }

    private function getLocation(Business $business, array $args): array
    {
        $id = $args['location_id'] ?? null;
        if (! $id) {
            throw new InvalidArgumentException('location_id wajib diisi untuk melihat detail lokasi.');
        }

        /** @var Location|null $location */
        $location = Location::where('business_id', $business->id)
            ->where(function ($q) use ($id): void {
                $q->where('id', $id)->orWhere('code', $id);
            })
            ->with(['parent', 'childWarehouses', 'stocks.product', 'stocks.material.unit'])
            ->first();

        if (! $location) {
            throw new InvalidArgumentException("Lokasi dengan ID/Kode [{$id}] tidak ditemukan.");
        }

        return [
            'success' => true,
            'location' => [
                'id' => $location->id,
                'name' => $location->name,
                'code' => $location->code,
                'type' => $location->type,
                'parent_id' => $location->parent_id,
                'parent_name' => $location->parent?->name,
                'is_sub_warehouse' => $location->isSubWarehouse(),
                'is_central_warehouse' => $location->isCentralWarehouse(),
                'address' => $location->address,
                'city' => $location->city,
                'is_primary' => (bool) $location->is_primary,
                'is_active' => (bool) $location->is_active,
                'sub_warehouses' => $location->childWarehouses->map(fn ($cw) => [
                    'id' => $cw->id,
                    'name' => $cw->name,
                    'code' => $cw->code,
                    'type' => $cw->type,
                    'is_active' => (bool) $cw->is_active,
                ])->all(),
                'stocks_summary' => [
                    'total_items_in_stock' => $location->stocks->where('quantity', '>', 0)->count(),
                    'stocks' => $location->stocks->where('quantity', '>', 0)->take(30)->map(fn ($s) => [
                        'item_name' => $s->product?->name ?? $s->material?->name,
                        'item_type' => $s->material_id ? 'material' : 'product',
                        'quantity' => (float) $s->quantity,
                        'unit' => $s->material?->unit?->code ?? 'PCS',
                        'avg_purchase_cost' => (float) $s->avg_purchase_cost,
                    ])->values()->all(),
                ],
            ],
        ];
    }
}
