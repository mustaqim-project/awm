<?php

declare(strict_types=1);

namespace App\Domain\Mcp\Tools;

use App\Domain\Mcp\Tools\Traits\HandlesCrudPermissions;
use App\Models\AuditLog;
use App\Models\Business;
use App\Models\BusinessLandingPage;
use App\Models\User;
use App\Models\WhatsAppSession;
use App\Support\TimezoneHelper;
use Illuminate\Support\Facades\Hash;
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
        return 'Mengelola dan melakukan UPDATE DATA atau edit konfigurasi bisnis di menu Pengaturan COOCA (resources/views/app/settings): profil usaha, identitas toko, nomor rekening bank, NPWP & bentuk badan usaha, aturan pajak PPN kasir, PIN supervisor void/refund POS, limit diskon kasir, template struk fisik & WhatsApp, zona waktu operasional, jam kerja, serta aktivasi modul.';
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
                    'description' => 'Aksi: get (lihat profil & seluruh data setting bisnis), update (lakukan UPDATE DATA atau edit setting bisnis).',
                ],
                // 1. Profil & Identitas Bisnis
                'name' => [
                    'type' => 'string',
                    'description' => 'Nama brand atau badan usaha.',
                ],
                'description' => [
                    'type' => 'string',
                    'description' => 'Deskripsi profil bisnis atau slogan usaha.',
                ],
                'phone' => [
                    'type' => 'string',
                    'description' => 'Nomor WhatsApp / kontak resmi usaha (misal: 081234567890).',
                ],
                'email' => [
                    'type' => 'string',
                    'description' => 'Email resmi kontak usaha.',
                ],
                'address' => [
                    'type' => 'string',
                    'description' => 'Alamat pusat bisnis / kantor usaha.',
                ],
                'city' => [
                    'type' => 'string',
                    'description' => 'Kota/Kabupaten domisili usaha.',
                ],
                'province' => [
                    'type' => 'string',
                    'description' => 'Provinsi domisili usaha.',
                ],
                'postal_code' => [
                    'type' => 'string',
                    'description' => 'Kode pos alamat usaha.',
                ],
                // 2. Pajak & Badan Usaha
                'tax_identification_number' => [
                    'type' => 'string',
                    'description' => 'Nomor Pokok Wajib Pajak (NPWP) usaha.',
                ],
                'tax_entity_type' => [
                    'type' => 'string',
                    'enum' => ['individual', 'pt_individual', 'corporate'],
                    'description' => 'Bentuk badan usaha: individual (Orang Pribadi / UMKM), pt_individual (PT Perorangan), atau corporate (PT / CV / Korporasi).',
                ],
                'pos_enable_tax' => [
                    'type' => 'boolean',
                    'description' => 'Aktifkan pemungutan pajak penjualan (PPN / PB1) di kasir POS.',
                ],
                'pos_tax_percent' => [
                    'type' => 'number',
                    'description' => 'Persentase tarif pajak PPN/PB1 (misal: 11 atau 12 persen).',
                ],
                // 3. Rekening Bank & Pembayaran
                'bank_name' => [
                    'type' => 'string',
                    'description' => 'Nama bank transfer resmi bisnis (misal: Bank Central Asia / BCA, Bank Mandiri, BRI).',
                ],
                'bank_account_number' => [
                    'type' => 'string',
                    'description' => 'Nomor rekening bank bisnis.',
                ],
                'bank_account_holder' => [
                    'type' => 'string',
                    'description' => 'Nama lengkap pemilik rekening bank (Atas Nama).',
                ],
                // 4. Mata Uang & Pembulatan Kasir
                'currency' => [
                    'type' => 'string',
                    'description' => 'Kode mata uang (misal: IDR, USD).',
                ],
                'currency_symbol' => [
                    'type' => 'string',
                    'description' => 'Simbol mata uang (misal: Rp, $).',
                ],
                'rounding_strategy' => [
                    'type' => 'string',
                    'enum' => ['ROUND', 'CEIL', 'FLOOR', 'ROUND_50', 'ROUND_100', 'ROUND_500', 'ROUND_1000'],
                    'description' => 'Strategi pembulatan kembalian kasir POS: ROUND (Normal), CEIL (Bulatkan ke Atas), FLOOR (Bulatkan ke Bawah), ROUND_100 (Ke 100 terdekat), ROUND_500 (Ke 500 terdekat), ROUND_1000 (Ke 1.000 terdekat).',
                ],
                // 5. Keamanan Kasir POS & Supervisor PIN
                'pos_supervisor_pin' => [
                    'type' => 'string',
                    'description' => 'PIN Supervisor Kasir POS (4-8 digit angka numerik) untuk otorisasi void transaksi dan refund belanja.',
                ],
                'pos_max_cashier_discount_percent' => [
                    'type' => 'number',
                    'description' => 'Batas maksimal persentase diskon yang boleh diberikan kasir tanpa PIN supervisor (0-100%).',
                ],
                'pos_require_pin_for_void' => [
                    'type' => 'boolean',
                    'description' => 'Wajibkan PIN supervisor saat kasir membatalkan/void item belanja.',
                ],
                'pos_require_pin_for_refund' => [
                    'type' => 'boolean',
                    'description' => 'Wajibkan PIN supervisor saat kasir memproses pengembalian dana/refund pelanggan.',
                ],
                'pos_auto_send_kds' => [
                    'type' => 'boolean',
                    'description' => 'Kirim pesanan secara otomatis ke layar Kitchen Display System (KDS) saat transaksi kasir tersimpan.',
                ],
                'pos_show_product_images' => [
                    'type' => 'boolean',
                    'description' => 'Tampilkan thumbnail foto produk pada katalog kasir POS.',
                ],
                // 6. Struk Belanja & WhatsApp Auto-Send
                'pos_receipt_footer_note' => [
                    'type' => 'string',
                    'description' => 'Catatan pesan footer pada struk belanja kasir (misal: "Terima kasih atas kunjungan Anda").',
                ],
                'pos_receipt_wa_template' => [
                    'type' => 'string',
                    'description' => 'Template pesan struk belanja digital yang dikirimkan via WhatsApp ke pelanggan.',
                ],
                'auto_send_receipt' => [
                    'type' => 'boolean',
                    'description' => 'Kirim struk belanja digital WhatsApp secara otomatis setelah transaksi kasir selesai.',
                ],
                'receipt_template' => [
                    'type' => 'string',
                    'description' => 'Format template cetak struk kasir printer thermal.',
                ],
                // 7. Zona Waktu & Jam Operasional
                'timezone' => [
                    'type' => 'string',
                    'description' => 'Zona waktu resmi operasional usaha (misal: Asia/Jakarta untuk WIB, Asia/Makassar untuk WITA, Asia/Jayapura untuk WIT).',
                ],
                'operating_hours' => [
                    'type' => 'object',
                    'description' => 'Jadwal jam buka & tutup operasional toko per hari (format object hari monday-sunday dengan open, close, is_closed).',
                ],
                // 8. Modul Usaha
                'disabled_modules' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                    'description' => 'Daftar kode modul yang dinonaktifkan untuk tenant ini.',
                ],
                'enable_module' => [
                    'type' => 'string',
                    'description' => 'Nama/kode modul yang ingin diaktifkan (misal: channels_marketing, inventory_raw_materials, table_management).',
                ],
                'disable_module' => [
                    'type' => 'string',
                    'description' => 'Nama/kode modul yang ingin dinonaktifkan.',
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

        $waSession = WhatsAppSession::where('business_id', $business->id)->first();

        return [
            'success' => true,
            'business' => [
                'id' => $business->id,
                'name' => $business->name,
                'slug' => $business->slug,
                'description' => $business->description,
                'email' => $business->email,
                'phone' => $business->phone,
                'address' => $business->address,
                'city' => $business->city,
                'province' => $business->province,
                'postal_code' => $business->postal_code,
                'tax' => [
                    'tax_identification_number' => $business->tax_identification_number,
                    'tax_entity_type' => $business->tax_entity_type ?? 'individual',
                    'enabled' => (bool) $business->pos_enable_tax,
                    'tax_percent' => (float) ($business->pos_tax_percent ?? 0),
                ],
                'bank' => [
                    'name' => $business->bank_name,
                    'account_number' => $business->bank_account_number,
                    'account_holder' => $business->bank_account_holder,
                ],
                'currency' => [
                    'code' => $business->currency ?? 'IDR',
                    'symbol' => $business->currency_symbol ?? 'Rp',
                    'rounding_strategy' => $business->rounding_strategy ?? 'ROUND',
                ],
                'pos_security' => [
                    'has_supervisor_pin' => ! empty($business->pos_supervisor_pin),
                    'max_cashier_discount_percent' => $business->pos_max_cashier_discount_percent !== null ? (float) $business->pos_max_cashier_discount_percent : null,
                    'require_pin_for_void' => (bool) ($business->pos_require_pin_for_void ?? false),
                    'require_pin_for_refund' => (bool) ($business->pos_require_pin_for_refund ?? false),
                    'auto_send_kds' => (bool) ($business->pos_auto_send_kds ?? false),
                    'show_product_images' => (bool) ($business->pos_show_product_images ?? true),
                ],
                'receipt' => [
                    'footer_note' => $business->pos_receipt_footer_note,
                    'wa_template' => $business->pos_receipt_wa_template,
                    'auto_send_receipt' => (bool) ($waSession?->auto_send_receipt ?? false),
                    'receipt_template' => $waSession?->receipt_template,
                ],
                'operations' => [
                    'timezone' => $business->getTimezone(),
                    'operating_hours' => $business->getOperatingHours(),
                ],
                'modules' => [
                    'disabled' => $business->disabled_modules ?? [],
                ],
            ],
        ];
    }

    private function updateSettings(Business $business, ?User $user, array $args): array
    {
        $this->assertCan('settings', 'update', $business, $user);

        $oldValues = $business->toArray();
        $isSensitiveUpdated = false;

        // 1. Profil & Kontak
        if (isset($args['name']) && trim((string) $args['name']) !== '') {
            $business->name = trim((string) $args['name']);
        }
        if (array_key_exists('description', $args)) {
            $business->description = $args['description'] !== null ? trim((string) $args['description']) : null;
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
        if (isset($args['province'])) {
            $business->province = trim((string) $args['province']);
        }
        if (isset($args['postal_code'])) {
            $business->postal_code = trim((string) $args['postal_code']);
        }

        // 2. Pajak & Badan Usaha
        if (array_key_exists('tax_identification_number', $args)) {
            $business->tax_identification_number = $args['tax_identification_number'] !== null ? trim((string) $args['tax_identification_number']) : null;
            $isSensitiveUpdated = true;
        }
        if (isset($args['tax_entity_type']) && in_array($args['tax_entity_type'], ['individual', 'pt_individual', 'corporate'], true)) {
            $business->tax_entity_type = (string) $args['tax_entity_type'];
        }
        if (array_key_exists('pos_enable_tax', $args)) {
            $business->pos_enable_tax = (bool) $args['pos_enable_tax'];
        }
        if (isset($args['pos_tax_percent'])) {
            $business->pos_tax_percent = (float) $args['pos_tax_percent'];
        }

        // 3. Rekening Bank & Finansial Sensitif
        if (array_key_exists('bank_name', $args)) {
            $business->bank_name = $args['bank_name'] !== null ? trim((string) $args['bank_name']) : null;
            $isSensitiveUpdated = true;
        }
        if (array_key_exists('bank_account_number', $args)) {
            $business->bank_account_number = $args['bank_account_number'] !== null ? trim((string) $args['bank_account_number']) : null;
            $isSensitiveUpdated = true;
        }
        if (array_key_exists('bank_account_holder', $args)) {
            $business->bank_account_holder = $args['bank_account_holder'] !== null ? trim((string) $args['bank_account_holder']) : null;
            $isSensitiveUpdated = true;
        }

        // 4. Mata Uang & Pembulatan Kasir
        if (isset($args['currency']) && trim((string) $args['currency']) !== '') {
            $business->currency = strtoupper(trim((string) $args['currency']));
        }
        if (isset($args['currency_symbol'])) {
            $business->currency_symbol = trim((string) $args['currency_symbol']);
        }
        if (isset($args['rounding_strategy']) && in_array($args['rounding_strategy'], ['ROUND', 'CEIL', 'FLOOR', 'ROUND_50', 'ROUND_100', 'ROUND_500', 'ROUND_1000'], true)) {
            $business->rounding_strategy = (string) $args['rounding_strategy'];
            $isSensitiveUpdated = true;
        }

        // 5. Keamanan Kasir POS & Supervisor PIN
        if (isset($args['pos_supervisor_pin'])) {
            $rawPin = trim((string) $args['pos_supervisor_pin']);
            if ($rawPin !== '') {
                if (strlen($rawPin) < 4 || strlen($rawPin) > 8 || ! ctype_digit($rawPin)) {
                    throw new InvalidArgumentException('PIN supervisor harus berupa 4 sampai 8 digit angka numerik.');
                }
                $business->pos_supervisor_pin = $rawPin; // auto-hashed via mutator setPosSupervisorPinAttribute
                $isSensitiveUpdated = true;
            }
        }
        if (array_key_exists('pos_max_cashier_discount_percent', $args)) {
            $val = $args['pos_max_cashier_discount_percent'];
            $business->pos_max_cashier_discount_percent = ($val !== null && $val !== '') ? max(0, min(100, (float) $val)) : null;
            $isSensitiveUpdated = true;
        }
        if (array_key_exists('pos_require_pin_for_void', $args)) {
            $business->pos_require_pin_for_void = (bool) $args['pos_require_pin_for_void'];
            $isSensitiveUpdated = true;
        }
        if (array_key_exists('pos_require_pin_for_refund', $args)) {
            $business->pos_require_pin_for_refund = (bool) $args['pos_require_pin_for_refund'];
            $isSensitiveUpdated = true;
        }
        if (array_key_exists('pos_auto_send_kds', $args)) {
            $business->pos_auto_send_kds = (bool) $args['pos_auto_send_kds'];
            $isSensitiveUpdated = true;
        }
        if (array_key_exists('pos_show_product_images', $args)) {
            $business->pos_show_product_images = (bool) $args['pos_show_product_images'];
        }

        // 6. Struk Kasir & WhatsApp Struk
        if (isset($args['pos_receipt_footer_note'])) {
            $business->pos_receipt_footer_note = (string) $args['pos_receipt_footer_note'];
        }
        if (isset($args['pos_receipt_wa_template'])) {
            $business->pos_receipt_wa_template = (string) $args['pos_receipt_wa_template'];
        }

        // Sync WhatsApp Session receipt settings
        if (array_key_exists('auto_send_receipt', $args) || array_key_exists('receipt_template', $args) || isset($args['pos_receipt_wa_template'])) {
            $waSession = WhatsAppSession::firstOrCreate(
                ['business_id' => $business->id],
                [
                    'session_id' => 'wa_' . $business->id,
                    'provider' => 'meta_cloud',
                    'is_active' => true,
                ]
            );
            if ($waSession) {
                if (array_key_exists('auto_send_receipt', $args)) {
                    $waSession->auto_send_receipt = (bool) $args['auto_send_receipt'];
                }
                if (isset($args['receipt_template'])) {
                    $waSession->receipt_template = (string) $args['receipt_template'];
                } elseif (isset($args['pos_receipt_wa_template'])) {
                    $waSession->receipt_template = (string) $args['pos_receipt_wa_template'];
                }
                $waSession->save();
            }
        }

        // 7. Zona Waktu & Jam Operasional
        if (isset($args['timezone']) && trim((string) $args['timezone']) !== '') {
            $tz = trim((string) $args['timezone']);
            if (TimezoneHelper::isValid($tz)) {
                $business->timezone = $tz;
            }
        }

        if (isset($args['operating_hours']) && is_array($args['operating_hours'])) {
            $normalizedHours = TimezoneHelper::normalizeOperatingHours($args['operating_hours']);
            $business->operating_hours = $normalizedHours;
            BusinessLandingPage::where('business_id', $business->id)->update([
                'operational_hours' => $normalizedHours,
            ]);
        }

        // 8. Modul Usaha
        if (isset($args['disabled_modules']) && is_array($args['disabled_modules'])) {
            $business->disabled_modules = array_values(array_unique(array_filter($args['disabled_modules'], 'is_string')));
        }
        if (isset($args['enable_module']) && trim((string) $args['enable_module']) !== '') {
            $mod = trim((string) $args['enable_module']);
            $currentDisabled = is_array($business->disabled_modules) ? $business->disabled_modules : [];
            $business->disabled_modules = array_values(array_diff($currentDisabled, [$mod]));
        }
        if (isset($args['disable_module']) && trim((string) $args['disable_module']) !== '') {
            $mod = trim((string) $args['disable_module']);
            $currentDisabled = is_array($business->disabled_modules) ? $business->disabled_modules : [];
            if (! in_array($mod, $currentDisabled, true)) {
                $currentDisabled[] = $mod;
            }
            $business->disabled_modules = array_values($currentDisabled);
        }

        $business->save();

        // Audit Trail Logging: Log security updated if sensitive fields changed, and log mcp_settings_updated
        $newValues = $business->toArray();

        AuditLog::create([
            'business_id' => $business->id,
            'user_id' => $user?->id,
            'action' => 'mcp_settings_updated',
            'auditable_type' => Business::class,
            'auditable_id' => $business->id,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'COOCA-MCP-Agent/1.0',
        ]);

        if ($isSensitiveUpdated) {
            AuditLog::create([
                'business_id' => $business->id,
                'user_id' => $user?->id,
                'action' => 'settings.security_updated',
                'auditable_type' => Business::class,
                'auditable_id' => $business->id,
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'COOCA-MCP-Agent/1.0',
                'risk_level' => AuditLog::RISK_HIGH,
            ]);
        }

        return [
            'success' => true,
            'message' => "Pengaturan dan profil bisnis [{$business->name}] berhasil diperbarui via MCP.",
            'business' => [
                'name' => $business->name,
                'description' => $business->description,
                'phone' => $business->phone,
                'email' => $business->email,
                'address' => $business->address,
                'city' => $business->city,
                'province' => $business->province,
                'postal_code' => $business->postal_code,
                'pos_enable_tax' => (bool) $business->pos_enable_tax,
                'pos_tax_percent' => (float) ($business->pos_tax_percent ?? 0),
                'pos_receipt_footer_note' => $business->pos_receipt_footer_note,
                'tax' => [
                    'tax_identification_number' => $business->tax_identification_number,
                    'tax_entity_type' => $business->tax_entity_type ?? 'individual',
                    'enabled' => (bool) $business->pos_enable_tax,
                    'tax_percent' => (float) ($business->pos_tax_percent ?? 0),
                ],
                'bank' => [
                    'name' => $business->bank_name,
                    'account_number' => $business->bank_account_number,
                    'account_holder' => $business->bank_account_holder,
                ],
                'currency' => [
                    'code' => $business->currency ?? 'IDR',
                    'symbol' => $business->currency_symbol ?? 'Rp',
                    'rounding_strategy' => $business->rounding_strategy ?? 'ROUND',
                ],
                'pos_security' => [
                    'has_supervisor_pin' => ! empty($business->pos_supervisor_pin),
                    'max_cashier_discount_percent' => $business->pos_max_cashier_discount_percent !== null ? (float) $business->pos_max_cashier_discount_percent : null,
                    'require_pin_for_void' => (bool) ($business->pos_require_pin_for_void ?? false),
                    'require_pin_for_refund' => (bool) ($business->pos_require_pin_for_refund ?? false),
                    'auto_send_kds' => (bool) ($business->pos_auto_send_kds ?? false),
                    'show_product_images' => (bool) ($business->pos_show_product_images ?? true),
                ],
                'receipt' => [
                    'footer_note' => $business->pos_receipt_footer_note,
                    'wa_template' => $business->pos_receipt_wa_template,
                ],
                'operations' => [
                    'timezone' => $business->getTimezone(),
                    'operating_hours' => $business->getOperatingHours(),
                ],
            ],
        ];
    }
}
