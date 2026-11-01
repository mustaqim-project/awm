<?php

declare(strict_types=1);

use App\Domain\Billing\EntitlementService;
use App\Models\BillingPackage;
use App\Models\SystemSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations to synchronize official single-source tier quotas and package descriptions.
     */
    public function up(): void
    {
        $now = now();

        // 1. Ensure official descriptions and attributes in BillingPackage table
        if (Schema::hasTable('billing_packages')) {
            $packagesToSync = [
                'standard-monthly' => [
                    'name' => 'Standard Bulanan',
                    'description' => 'Paket Standard 30 hari untuk UMKM pemula (100 produk, 20 resep, 1.000 transaksi kasir POS, 3 karyawan, 3 GB storage).',
                    'price' => 39000,
                    'duration_days' => 30,
                    'is_active' => true,
                    'sort_order' => 1,
                ],
                'standard-annual' => [
                    'name' => 'Standard Tahunan',
                    'description' => 'Paket Standard 365 hari hemat 2 bulan (100 produk, 20 resep, 1.000 transaksi kasir POS, 3 karyawan, 3 GB storage).',
                    'price' => 390000,
                    'duration_days' => 365,
                    'is_active' => true,
                    'sort_order' => 2,
                ],
                'premium-monthly' => [
                    'name' => 'Premium Bulanan',
                    'description' => 'Paket Premium 30 hari (Produk, Resep & Kasir Unlimited, 3 Bisnis, 5 Cabang & Gudang, 10 Staf, KDS, Transfer Stok, 200 WA).',
                    'price' => 89000,
                    'duration_days' => 30,
                    'is_active' => true,
                    'sort_order' => 3,
                ],
                'premium-annual' => [
                    'name' => 'Premium Tahunan',
                    'description' => 'Paket Premium 365 hari hemat 2 bulan (Produk, Resep & Kasir Unlimited, 3 Bisnis, 5 Cabang & Gudang, 10 Staf, KDS, Transfer Stok, 200 WA).',
                    'price' => 890000,
                    'duration_days' => 365,
                    'is_active' => true,
                    'sort_order' => 4,
                ],
                'prestige-monthly' => [
                    'name' => 'Prestige Bulanan',
                    'description' => 'Paket Prestige 30 hari Enterprise UMKM (Bisnis & Lokasi Unlimited, PPh 21 TER, Auto Slip Gaji WA, 1.000 WA, 30 GB Storage).',
                    'price' => 199000,
                    'duration_days' => 30,
                    'is_active' => true,
                    'sort_order' => 5,
                ],
                'prestige-annual' => [
                    'name' => 'Prestige Tahunan',
                    'description' => 'Paket Prestige 365 hari hemat 2 bulan Enterprise UMKM (Bisnis & Lokasi Unlimited, PPh 21 TER, Auto Slip Gaji WA, 1.000 WA, 30 GB Storage).',
                    'price' => 1990000,
                    'duration_days' => 365,
                    'is_active' => true,
                    'sort_order' => 6,
                ],
            ];

            foreach ($packagesToSync as $code => $pkgData) {
                DB::table('billing_packages')
                    ->where('type', BillingPackage::TYPE_SUBSCRIPTION)
                    ->where('code', $code)
                    ->update(array_merge($pkgData, ['updated_at' => $now]));
            }
        }

        // 2. Synchronize official tier limit matrix in SystemSetting if missing or set to -1 on Standard POS
        if (Schema::hasTable('system_settings')) {
            $baselineLimits = [
                // Free Tier Limits
                'tier_limit_free_product' => '10',
                'tier_limit_free_recipe' => '3',
                'tier_limit_free_material' => '10',
                'tier_limit_free_customer' => '15',
                'tier_limit_free_supplier' => '2',
                'tier_limit_free_outlet' => '1',
                'tier_limit_free_warehouse' => '1',
                'tier_limit_free_total_location' => '2',
                'tier_limit_free_user' => '1',
                'tier_limit_free_business' => '1',
                'tier_limit_free_pos' => '30',
                'tier_limit_free_invoice' => '3',
                'tier_limit_free_po' => '3',
                'tier_limit_free_whatsapp' => '10',
                'tier_limit_free_social' => '3',
                'tier_limit_free_table' => '0',
                'tier_limit_free_storage_gb' => '1',

                // Standard Tier Limits (100 produk, 20 resep, 1.000 trx kasir POS, 3 karyawan, 2 cabang, 3 GB)
                'tier_limit_standard_product' => '100',
                'tier_limit_standard_recipe' => '20',
                'tier_limit_standard_material' => '30',
                'tier_limit_standard_customer' => '100',
                'tier_limit_standard_supplier' => '5',
                'tier_limit_standard_outlet' => '2',
                'tier_limit_standard_warehouse' => '2',
                'tier_limit_standard_total_location' => '2',
                'tier_limit_standard_user' => '3',
                'tier_limit_standard_business' => '1',
                'tier_limit_standard_pos' => '1000',
                'tier_limit_standard_invoice' => '15',
                'tier_limit_standard_po' => '15',
                'tier_limit_standard_whatsapp' => '50',
                'tier_limit_standard_social' => '10',
                'tier_limit_standard_table' => '5',
                'tier_limit_standard_storage_gb' => '3',

                // Premium Tier Limits (Unlimited items/pos, 5 cabang, 10 user, 10 GB storage, 200 WA)
                'tier_limit_premium_product' => '-1',
                'tier_limit_premium_recipe' => '-1',
                'tier_limit_premium_material' => '-1',
                'tier_limit_premium_customer' => '1000',
                'tier_limit_premium_supplier' => '-1',
                'tier_limit_premium_outlet' => '5',
                'tier_limit_premium_warehouse' => '5',
                'tier_limit_premium_total_location' => '5',
                'tier_limit_premium_user' => '10',
                'tier_limit_premium_business' => '3',
                'tier_limit_premium_pos' => '-1',
                'tier_limit_premium_invoice' => '-1',
                'tier_limit_premium_po' => '-1',
                'tier_limit_premium_whatsapp' => '200',
                'tier_limit_premium_social' => '30',
                'tier_limit_premium_table' => '-1',
                'tier_limit_premium_storage_gb' => '10',

                // Prestige Tier Limits (Unlimited everything, 30 GB storage, 1.000 WA)
                'tier_limit_prestige_product' => '-1',
                'tier_limit_prestige_recipe' => '-1',
                'tier_limit_prestige_material' => '-1',
                'tier_limit_prestige_customer' => '-1',
                'tier_limit_prestige_supplier' => '-1',
                'tier_limit_prestige_outlet' => '-1',
                'tier_limit_prestige_warehouse' => '-1',
                'tier_limit_prestige_total_location' => '-1',
                'tier_limit_prestige_user' => '-1',
                'tier_limit_prestige_business' => '-1',
                'tier_limit_prestige_pos' => '-1',
                'tier_limit_prestige_invoice' => '-1',
                'tier_limit_prestige_po' => '-1',
                'tier_limit_prestige_whatsapp' => '1000',
                'tier_limit_prestige_social' => '-1',
                'tier_limit_prestige_table' => '-1',
                'tier_limit_prestige_storage_gb' => '30',
            ];

            foreach ($baselineLimits as $key => $val) {
                // If setting doesn't exist, or if standard_pos was set to -1, seed the official value
                $existing = DB::table('system_settings')->where('key', $key)->first();
                if ($existing === null || ($key === 'tier_limit_standard_pos' && $existing->value === '-1')) {
                    if ($existing) {
                        DB::table('system_settings')->where('id', $existing->id)->update([
                            'value' => $val,
                            'group' => 'billing',
                            'updated_at' => $now,
                        ]);
                    } else {
                        DB::table('system_settings')->insert([
                            'id' => (string) \Illuminate\Support\Str::uuid(),
                            'key' => $key,
                            'value' => $val,
                            'group' => 'billing',
                            'is_secret' => false,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    }
                    Cache::forget("system_setting_{$key}");
                    Cache::forever("system_setting_fallback_{$key}", $val);
                }
            }

            Cache::forget('system_settings');
            Cache::forget('billing_all_tier_prices');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reversible if needed
    }
};
