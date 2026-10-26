<?php

declare(strict_types=1);

use App\Models\BillingPackage;
use App\Models\SystemSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        // 1. Clean legacy subscription packages (e.g. core-monthly, core-annual)
        DB::table('billing_packages')
            ->where('type', BillingPackage::TYPE_SUBSCRIPTION)
            ->whereNotIn('code', [
                'standard-monthly',
                'standard-annual',
                'premium-monthly',
                'premium-annual',
                'prestige-monthly',
                'prestige-annual',
            ])
            ->delete();

        // 2. Ensure only the official 3 plans (6 tiers) exist and have exact official prices
        $tiers = [
            [
                'type' => BillingPackage::TYPE_SUBSCRIPTION,
                'code' => 'standard-monthly',
                'name' => 'Standard Bulanan',
                'description' => 'Paket Standard 30 hari untuk UMKM pemula (100 produk, 20 resep, 1.000 transaksi kasir POS).',
                'price' => 39000,
                'duration_days' => 30,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'type' => BillingPackage::TYPE_SUBSCRIPTION,
                'code' => 'standard-annual',
                'name' => 'Standard Tahunan',
                'description' => 'Paket Standard 365 hari hemat 2 bulan (100 produk, 20 resep, 1.000 transaksi kasir POS).',
                'price' => 390000,
                'duration_days' => 365,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'type' => BillingPackage::TYPE_SUBSCRIPTION,
                'code' => 'premium-monthly',
                'name' => 'Premium Bulanan',
                'description' => 'Paket Premium 30 hari (Produk, Resep & Kasir Unlimited, 5 Lokasi, 10 Staf, KDS, Transfer Stok, 200 WA).',
                'price' => 89000,
                'duration_days' => 30,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'type' => BillingPackage::TYPE_SUBSCRIPTION,
                'code' => 'premium-annual',
                'name' => 'Premium Tahunan',
                'description' => 'Paket Premium 365 hari hemat 2 bulan (Produk, Resep & Kasir Unlimited, 5 Lokasi, 10 Staf, KDS, Transfer Stok, 200 WA).',
                'price' => 890000,
                'duration_days' => 365,
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'type' => BillingPackage::TYPE_SUBSCRIPTION,
                'code' => 'prestige-monthly',
                'name' => 'Prestige Bulanan',
                'description' => 'Paket Prestige 30 hari Enterprise UMKM (Bisnis & Lokasi Unlimited, PPh 21 TER, Auto Slip Gaji WA, 1.000 WA).',
                'price' => 199000,
                'duration_days' => 30,
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'type' => BillingPackage::TYPE_SUBSCRIPTION,
                'code' => 'prestige-annual',
                'name' => 'Prestige Tahunan',
                'description' => 'Paket Prestige 365 hari hemat 2 bulan Enterprise UMKM (Bisnis & Lokasi Unlimited, PPh 21 TER, Auto Slip Gaji WA, 1.000 WA).',
                'price' => 1990000,
                'duration_days' => 365,
                'is_active' => true,
                'sort_order' => 6,
            ],
        ];

        foreach ($tiers as $tier) {
            $existing = DB::table('billing_packages')
                ->where('type', $tier['type'])
                ->where('code', $tier['code'])
                ->first();

            if ($existing) {
                DB::table('billing_packages')
                    ->where('id', $existing->id)
                    ->update(array_merge($tier, ['updated_at' => $now]));
            } else {
                DB::table('billing_packages')->insert(array_merge($tier, [
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]));
            }
        }

        // 3. Save official pricing & promo settings in SystemSetting
        $settings = [
            'subscription_price_standard_monthly' => '39000',
            'subscription_price_standard_annual' => '390000',
            'subscription_price_monthly' => '39000',
            'subscription_price_annual' => '390000',
            'subscription_price_premium_monthly' => '89000',
            'subscription_price_premium_annual' => '890000',
            'subscription_price_prestige_monthly' => '199000',
            'subscription_price_prestige_annual' => '1990000',
            'subscription_annual_discount_badge' => 'Hemat 2 Bulan',
        ];

        foreach ($settings as $key => $val) {
            SystemSetting::set($key, $val, 'billing');
        }
    }

    public function down(): void
    {
        // Revert prices if necessary
    }
};
