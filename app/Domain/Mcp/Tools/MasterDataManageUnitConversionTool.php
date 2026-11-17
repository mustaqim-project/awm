<?php

declare(strict_types=1);

namespace App\Domain\Mcp\Tools;

use App\Domain\Material\UnitConversionService;
use App\Domain\Mcp\Tools\Traits\HandlesCrudPermissions;
use App\Models\AuditLog;
use App\Models\Business;
use App\Models\Material;
use App\Models\MaterialUnitConversion;
use App\Models\Unit;
use App\Models\UnitConversion;
use App\Models\User;
use InvalidArgumentException;

final class MasterDataManageUnitConversionTool implements McpToolInterface
{
    use HandlesCrudPermissions;

    public function __construct(
        private readonly UnitConversionService $conversionService = new UnitConversionService()
    ) {}

    public function getName(): string
    {
        return 'master_data_manage_unit_conversion';
    }

    public function getDescription(): string
    {
        return 'Mengelola satuan ukur (Unit) dan aturan konversi satuan (misal 1 DUS = 30 PCS): melihat daftar satuan & konversi, membuat satuan baru, mendaftarkan rasio konversi global atau per bahan baku, dan menghitung konversi kuantitas secara live.';
    }

    public function getRequiredAbility(): string
    {
        return 'mcp:master_data:read';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'action' => [
                    'type' => 'string',
                    'enum' => ['list_conversions', 'create_conversion', 'convert', 'list_units', 'create_unit', 'delete_conversion'],
                    'default' => 'list_conversions',
                    'description' => 'Aksi: list_conversions (daftar konversi), create_conversion (buat aturan konversi satuan, misal 1 DUS = 30 PCS), convert (hitung live nilai konversi), list_units (daftar satuan), create_unit (buat satuan baru), delete_conversion (hapus aturan konversi).',
                ],
                'from_unit' => [
                    'type' => 'string',
                    'description' => 'Kode, ID, atau nama satuan asal (misal: "DUS", "KARTON", "BALL", "SAK").',
                ],
                'to_unit' => [
                    'type' => 'string',
                    'description' => 'Kode, ID, atau nama satuan tujuan (misal: "PCS", "KG", "GRAM").',
                ],
                'factor' => [
                    'type' => 'number',
                    'description' => 'Nilai faktor perbandingan konversi. Contoh: 30 artinya 1 satuan asal = 30 satuan tujuan (1 DUS = 30 PCS).',
                ],
                'material_id' => [
                    'type' => 'string',
                    'description' => 'UUID atau nama bahan baku jika aturan konversi ini KHUSUS berlaku untuk bahan baku tertentu (Material-Specific Conversion). Kosongkan jika berlaku global untuk seluruh bisnis.',
                ],
                'quantity' => [
                    'type' => 'number',
                    'description' => 'Jumlah kuantitas yang ingin dihitung nilai konversinya pada action convert.',
                ],
                'conversion_id' => [
                    'type' => 'string',
                    'description' => 'UUID aturan konversi untuk dihapus (pada action delete_conversion).',
                ],
                'unit_name' => [
                    'type' => 'string',
                    'description' => 'Nama lengkap satuan saat create_unit (misal: "Dus", "Karton", "Pcs").',
                ],
                'unit_code' => [
                    'type' => 'string',
                    'description' => 'Kode unik satuan saat create_unit (misal: "DUS", "KRT", "PCS").',
                ],
                'unit_category' => [
                    'type' => 'string',
                    'enum' => ['quantity', 'weight', 'volume', 'length', 'area', 'time', 'custom'],
                    'default' => 'quantity',
                    'description' => 'Kategori satuan: quantity (cacah/pcs/dus), weight (berat/kg/gram), volume (cair/liter/ml), length, area, time, custom.',
                ],
            ],
        ];
    }

    public function execute(Business $business, ?User $user, array $arguments = []): array
    {
        $action = trim((string) ($arguments['action'] ?? 'list_conversions'));

        return match ($action) {
            'create_conversion' => $this->createConversionWithPermission($business, $user, $arguments),
            'create_unit'       => $this->createUnitWithPermission($business, $user, $arguments),
            'delete_conversion' => $this->deleteConversionWithPermission($business, $user, $arguments),
            'convert'           => $this->convertLiveWithPermission($business, $user, $arguments),
            'list_units'        => $this->listUnitsWithPermission($business, $user),
            default             => $this->listConversionsWithPermission($business, $user, $arguments),
        };
    }

    private function listUnitsWithPermission(Business $business, ?User $user): array
    {
        $this->assertCan('master_data', 'read', $business, $user);
        return $this->listUnits($business);
    }

    private function listConversionsWithPermission(Business $business, ?User $user, array $args): array
    {
        $this->assertCan('master_data', 'read', $business, $user);
        return $this->listConversions($business, $args);
    }

    private function convertLiveWithPermission(Business $business, ?User $user, array $args): array
    {
        $this->assertCan('master_data', 'read', $business, $user);
        return $this->convertLive($business, $args);
    }

    private function createConversionWithPermission(Business $business, ?User $user, array $args): array
    {
        $this->assertCan('master_data', 'create', $business, $user);
        return $this->createConversion($business, $user, $args);
    }

    private function createUnitWithPermission(Business $business, ?User $user, array $args): array
    {
        $this->assertCan('master_data', 'create', $business, $user);
        return $this->createUnit($business, $user, $args);
    }

    private function deleteConversionWithPermission(Business $business, ?User $user, array $args): array
    {
        $this->assertCan('master_data', 'delete', $business, $user);
        return $this->deleteConversion($business, $user, $args);
    }

    private function listUnits(Business $business): array
    {
        $units = Unit::available()->orderBy('category')->orderBy('name')->get();

        return [
            'success' => true,
            'message' => 'Berhasil mengambil daftar satuan ukur.',
            'count' => $units->count(),
            'units' => $units->map(fn (Unit $u) => [
                'id' => $u->id,
                'name' => $u->name,
                'code' => $u->code,
                'category' => $u->category,
                'is_system' => empty($u->business_id),
            ])->all(),
        ];
    }

    private function createUnit(Business $business, ?User $user, array $args): array
    {
        $name = trim((string) ($args['unit_name'] ?? ''));
        $code = strtoupper(trim((string) ($args['unit_code'] ?? '')));

        if (empty($name) || empty($code)) {
            throw new InvalidArgumentException('Nama satuan (unit_name) dan kode satuan (unit_code) wajib diisi.');
        }

        $existing = Unit::available()
            ->where(function ($q) use ($code, $name): void {
                $q->where('code', $code)->orWhere('name', $name);
            })
            ->first();

        if ($existing) {
            return [
                'success' => true,
                'message' => "Satuan [{$existing->name}] ({$existing->code}) sudah tersedia.",
                'unit' => [
                    'id' => $existing->id,
                    'name' => $existing->name,
                    'code' => $existing->code,
                    'category' => $existing->category,
                ],
            ];
        }

        $unit = Unit::create([
            'business_id' => $business->id,
            'name' => $name,
            'code' => $code,
            'category' => $args['unit_category'] ?? Unit::CATEGORY_QUANTITY,
        ]);

        AuditLog::create([
            'business_id' => $business->id,
            'user_id' => $user?->id,
            'action' => 'mcp_create_unit',
            'auditable_type' => Unit::class,
            'auditable_id' => $unit->id,
            'new_values' => ['name' => $unit->name, 'code' => $unit->code, 'category' => $unit->category],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'COOCA-MCP-Agent/1.0',
        ]);

        return [
            'success' => true,
            'message' => "Satuan baru [{$unit->name}] ({$unit->code}) berhasil didaftarkan.",
            'unit' => [
                'id' => $unit->id,
                'name' => $unit->name,
                'code' => $unit->code,
                'category' => $unit->category,
            ],
        ];
    }

    private function listConversions(Business $business, array $args): array
    {
        $global = UnitConversion::where('business_id', $business->id)
            ->with(['fromUnit', 'toUnit'])
            ->get();

        $materialSpecific = MaterialUnitConversion::where('business_id', $business->id)
            ->with(['fromUnit', 'toUnit', 'material:id,name,code'])
            ->get();

        return [
            'success' => true,
            'message' => 'Berhasil mengambil daftar aturan konversi satuan.',
            'global_conversions_count' => $global->count(),
            'global_conversions' => $global->map(fn (UnitConversion $c) => [
                'id' => $c->id,
                'from_unit_id' => $c->from_unit_id,
                'from_unit_code' => $c->fromUnit?->code,
                'to_unit_id' => $c->to_unit_id,
                'to_unit_code' => $c->toUnit?->code,
                'factor' => (float) $c->factor,
                'formula' => "1 {$c->fromUnit?->code} = {$c->factor} {$c->toUnit?->code}",
            ])->all(),
            'material_conversions_count' => $materialSpecific->count(),
            'material_conversions' => $materialSpecific->map(fn (MaterialUnitConversion $mc) => [
                'id' => $mc->id,
                'material_id' => $mc->material_id,
                'material_name' => $mc->material?->name,
                'from_unit_code' => $mc->fromUnit?->code,
                'to_unit_code' => $mc->toUnit?->code,
                'factor' => (float) $mc->factor,
                'formula' => "Khusus {$mc->material?->name}: 1 {$mc->fromUnit?->code} = {$mc->factor} {$mc->toUnit?->code}",
            ])->all(),
        ];
    }

    private function createConversion(Business $business, ?User $user, array $args): array
    {
        $fromInput = trim((string) ($args['from_unit'] ?? ''));
        $toInput = trim((string) ($args['to_unit'] ?? ''));
        $factor = (float) ($args['factor'] ?? 0);

        if (empty($fromInput) || empty($toInput)) {
            throw new InvalidArgumentException('from_unit (satuan asal) dan to_unit (satuan tujuan) wajib diisi.');
        }

        if ($factor <= 0) {
            throw new InvalidArgumentException('factor (faktor konversi) harus bernilai lebih dari 0. Contoh: 30 untuk 1 Dus = 30 Pcs.');
        }

        $fromUnit = $this->resolveOrCreateUnit($business, $fromInput);
        $toUnit = $this->resolveOrCreateUnit($business, $toInput);

        if ($fromUnit->id === $toUnit->id) {
            throw new InvalidArgumentException('Satuan asal dan satuan tujuan tidak boleh sama.');
        }

        $materialId = $args['material_id'] ?? null;
        if (! empty($materialId)) {
            $material = Material::where('business_id', $business->id)
                ->where(function ($q) use ($materialId): void {
                    $q->where('id', $materialId)->orWhere('name', 'like', "%{$materialId}%")->orWhere('code', $materialId);
                })
                ->first();

            if (! $material) {
                throw new InvalidArgumentException("Bahan baku [{$materialId}] tidak ditemukan.");
            }

            $conversion = MaterialUnitConversion::updateOrCreate(
                [
                    'business_id' => $business->id,
                    'material_id' => $material->id,
                    'from_unit_id' => $fromUnit->id,
                    'to_unit_id' => $toUnit->id,
                ],
                [
                    'factor' => $factor,
                    'is_default' => true,
                ]
            );

            AuditLog::create([
                'business_id' => $business->id,
                'user_id' => $user?->id,
                'action' => 'mcp_create_material_unit_conversion',
                'auditable_type' => MaterialUnitConversion::class,
                'auditable_id' => $conversion->id,
                'new_values' => [
                    'material' => $material->name,
                    'formula' => "1 {$fromUnit->code} = {$factor} {$toUnit->code}",
                ],
                'ip_address' => '127.0.0.1',
                'user_agent' => 'COOCA-MCP-Agent/1.0',
            ]);

            return [
                'success' => true,
                'message' => "Aturan konversi khusus bahan baku [{$material->name}] berhasil dibuat: 1 {$fromUnit->code} = {$factor} {$toUnit->code}.",
                'conversion' => [
                    'id' => $conversion->id,
                    'type' => 'material_specific',
                    'material_name' => $material->name,
                    'from_unit' => $fromUnit->code,
                    'to_unit' => $toUnit->code,
                    'factor' => $factor,
                    'formula' => "1 {$fromUnit->code} = {$factor} {$toUnit->code}",
                ],
            ];
        }

        // Global conversion
        $conversion = UnitConversion::updateOrCreate(
            [
                'business_id' => $business->id,
                'from_unit_id' => $fromUnit->id,
                'to_unit_id' => $toUnit->id,
            ],
            [
                'factor' => $factor,
            ]
        );

        AuditLog::create([
            'business_id' => $business->id,
            'user_id' => $user?->id,
            'action' => 'mcp_create_unit_conversion',
            'auditable_type' => UnitConversion::class,
            'auditable_id' => $conversion->id,
            'new_values' => [
                'formula' => "1 {$fromUnit->code} = {$factor} {$toUnit->code}",
            ],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'COOCA-MCP-Agent/1.0',
        ]);

        return [
            'success' => true,
            'message' => "Aturan konversi satuan global berhasil dibuat: 1 {$fromUnit->code} = {$factor} {$toUnit->code}.",
            'conversion' => [
                'id' => $conversion->id,
                'type' => 'global',
                'from_unit' => $fromUnit->code,
                'to_unit' => $toUnit->code,
                'factor' => $factor,
                'formula' => "1 {$fromUnit->code} = {$factor} {$toUnit->code}",
            ],
        ];
    }

    private function convertLive(Business $business, array $args): array
    {
        $qty = (float) ($args['quantity'] ?? 1);
        $from = trim((string) ($args['from_unit'] ?? ''));
        $to = trim((string) ($args['to_unit'] ?? ''));
        $materialId = $args['material_id'] ?? null;

        if (empty($from) || empty($to)) {
            throw new InvalidArgumentException('from_unit dan to_unit wajib diisi untuk menghitung konversi.');
        }

        $fromUnit = $this->resolveOrCreateUnit($business, $from);
        $toUnit = $this->resolveOrCreateUnit($business, $to);

        $convertedQty = $this->conversionService->convert(
            qty: $qty,
            from: $fromUnit,
            to: $toUnit,
            materialId: $materialId
        );

        return [
            'success' => true,
            'message' => "Hasil konversi: {$qty} {$fromUnit->code} = {$convertedQty} {$toUnit->code}.",
            'original_quantity' => $qty,
            'original_unit' => $fromUnit->code,
            'converted_quantity' => $convertedQty,
            'target_unit' => $toUnit->code,
            'formula' => "{$qty} {$fromUnit->code} => {$convertedQty} {$toUnit->code}",
        ];
    }

    private function deleteConversion(Business $business, ?User $user, array $args): array
    {
        $id = $args['conversion_id'] ?? null;
        if (! $id) {
            throw new InvalidArgumentException('conversion_id wajib diisi untuk menghapus aturan konversi.');
        }

        $deleted = UnitConversion::where('business_id', $business->id)->where('id', $id)->delete();
        if (! $deleted) {
            $deleted = MaterialUnitConversion::where('business_id', $business->id)->where('id', $id)->delete();
        }

        if (! $deleted) {
            throw new InvalidArgumentException("Aturan konversi [{$id}] tidak ditemukan.");
        }

        AuditLog::create([
            'business_id' => $business->id,
            'user_id' => $user?->id,
            'action' => 'mcp_delete_unit_conversion',
            'auditable_type' => UnitConversion::class,
            'auditable_id' => $id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'COOCA-MCP-Agent/1.0',
        ]);

        return [
            'success' => true,
            'message' => 'Aturan konversi berhasil dihapus.',
        ];
    }

    private function resolveOrCreateUnit(Business $business, string $identifier): Unit
    {
        $found = Unit::available()
            ->where(function ($q) use ($identifier): void {
                $q->where('id', $identifier)
                    ->orWhere('code', strtoupper($identifier))
                    ->orWhere('name', $identifier);
            })
            ->first();

        if ($found) {
            return $found;
        }

        // Auto-create tenant custom unit if missing
        $code = strtoupper(substr($identifier, 0, 10));
        return Unit::create([
            'business_id' => $business->id,
            'name' => ucfirst($identifier),
            'code' => $code,
            'category' => Unit::CATEGORY_QUANTITY,
        ]);
    }
}
