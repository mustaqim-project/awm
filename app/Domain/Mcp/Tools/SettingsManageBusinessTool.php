<?php

declare(strict_types=1);

namespace App\Domain\Mcp\Tools;

use App\Domain\Mcp\Tools\Traits\HandlesCrudPermissions;
use App\Models\AuditLog;
use App\Models\Business;
use App\Models\User;
use InvalidArgumentException;

final class SettingsManageBusinessTool implements McpToolInterface
{
    use HandlesCrudPermissions;

    public function getName(): string
    {
        return 'settings_manage_business';
    }

    public function getDescription(): string
    {
        return 'Mengelola profil usaha, identitas toko, pengaturan pajak PPN, dan catatan struk kasir di menu Pengaturan COOCA: melihat konfigurasi usaha saat ini atau memperbarui informasi profil dan pajak.';
    }

    public function getRequiredAbility(): string
    {
        return 'mcp:settings:read';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'action' => [
                    'type' => 'string',
                    'enum' => ['get', 'update'],
                    'default' => 'get',
                    'description' => 'Aksi: get (lihat profil & setting bisnis), update (perbarui profil & setting).',
                ],
                'name' => [
                    'type' => 'string',
                    'description' => 'Nama brand atau badan usaha.',
                ],
                'phone' => [
                    'type' => 'string',
                    'description' => 'Nomor WhatsApp / kontak resmi usaha.',
                ],
                'email' => [
                    'type' => 'string',
                    'description' => 'Email kontak usaha.',
                ],
                'address' => [
                    'type' => 'string',
                    'description' => 'Alamat pusat bisnis.',
                ],
                'city' => [
                    'type' => 'string',
                    'description' => 'Kota usaha.',
                ],
                'pos_enable_tax' => [
                    'type' => 'boolean',
                    'description' => 'Aktifkan pemungutan pajak penjualan (PPN) di kasir POS.',
                ],
                'pos_tax_percent' => [
                    'type' => 'number',
                    'description' => 'Persentase pajak PPN (misal: 11 atau 12 persen).',
                ],
                'pos_receipt_footer_note' => [
                    'type' => 'string',
                    'description' => 'Catatan pesan footer pada struk belanja kasir (misal: "Terima kasih atas kunjungan Anda").',
                ],
            ],
        ];
    }

    public function execute(Business $business, ?User $user, array $arguments = []): array
    {
        $action = trim((string) ($arguments['action'] ?? 'get'));

        return match ($action) {
            'update' => $this->updateSettings($business, $user, $arguments),
            default  => $this->getSettings($business, $user),
        };
    }

    private function getSettings(Business $business, ?User $user): array
    {
        $this->assertCan('settings', 'read', $business, $user);

        return [
            'success' => true,
            'business' => [
                'id' => $business->id,
                'name' => $business->name,
                'email' => $business->email,
                'phone' => $business->phone,
                'address' => $business->address,
                'city' => $business->city,
                'province' => $business->province,
                'currency' => $business->currency ?? 'IDR',
                'currency_symbol' => $business->currency_symbol ?? 'Rp',
                'timezone' => $business->timezone ?? 'Asia/Jakarta',
                'tax' => [
                    'enabled' => (bool) $business->pos_enable_tax,
                    'tax_percent' => (float) ($business->pos_tax_percent ?? 0),
                ],
                'receipt' => [
                    'footer_note' => $business->pos_receipt_footer_note,
                ],
            ],
        ];
    }

    private function updateSettings(Business $business, ?User $user, array $args): array
    {
        $this->assertCan('settings', 'update', $business, $user);

        $oldValues = $business->toArray();

        if (isset($args['name']) && trim((string) $args['name']) !== '') {
            $business->name = trim((string) $args['name']);
        }
        if (isset($args['phone'])) {
            $business->phone = trim((string) $args['phone']);
        }
        if (isset($args['email'])) {
            $business->email = trim((string) $args['email']);
        }
        if (isset($args['address'])) {
            $business->address = trim((string) $args['address']);
        }
        if (isset($args['city'])) {
            $business->city = trim((string) $args['city']);
        }
        if (array_key_exists('pos_enable_tax', $args)) {
            $business->pos_enable_tax = (bool) $args['pos_enable_tax'];
        }
        if (isset($args['pos_tax_percent'])) {
            $business->pos_tax_percent = (float) $args['pos_tax_percent'];
        }
        if (isset($args['pos_receipt_footer_note'])) {
            $business->pos_receipt_footer_note = (string) $args['pos_receipt_footer_note'];
        }

        $business->save();

        AuditLog::create([
            'business_id' => $business->id,
            'user_id' => $user?->id,
            'action' => 'mcp_settings_updated',
            'auditable_type' => Business::class,
            'auditable_id' => $business->id,
            'old_values' => $oldValues,
            'new_values' => $business->toArray(),
            'ip_address' => '127.0.0.1',
            'user_agent' => 'COOCA-MCP-Agent/1.0',
        ]);

        return [
            'success' => true,
            'message' => "Pengaturan profil bisnis [{$business->name}] berhasil diperbarui.",
            'business' => [
                'name' => $business->name,
                'phone' => $business->phone,
                'email' => $business->email,
                'pos_enable_tax' => (bool) $business->pos_enable_tax,
                'pos_tax_percent' => (float) $business->pos_tax_percent,
                'pos_receipt_footer_note' => $business->pos_receipt_footer_note,
                'tax' => [
                    'enabled' => (bool) $business->pos_enable_tax,
                    'tax_percent' => (float) ($business->pos_tax_percent ?? 0),
                ],
                'receipt' => [
                    'footer_note' => $business->pos_receipt_footer_note,
                ],
            ],
        ];
    }
}
