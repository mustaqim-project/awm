<?php

declare(strict_types=1);

use App\Models\BillingPackage;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Seed official 4-Tier AI Studio visual credit packages into billing_packages catalog.
     * Managed via Superadmin at /admin/billing-packages/ai_token.
     */
    public function up(): void
    {
        if (!Schema::hasTable('billing_packages')) {
            return;
        }

        $now = now();
        $packages = [
            [
                'type' => BillingPackage::TYPE_AI_TOKEN,
                'code' => 'starter',
                'name' => 'Starter Pack',
                'description' => 'Uji coba 3-5 visual katalog produk & materi promosi sosmed.',
                'price' => 25000,
                'duration_days' => null,
                'token_quantity' => 50,
                'storage_bytes' => null,
                'token_expiry_days' => 30,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'type' => BillingPackage::TYPE_AI_TOKEN,
                'code' => 'growth',
                'name' => 'Growth Pack',
                'description' => 'Photoshoot katalog mingguan & kampanye visual omnichannel.',
                'price' => 65000,
                'duration_days' => null,
                'token_quantity' => 150,
                'storage_bytes' => null,
                'token_expiry_days' => 30,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'type' => BillingPackage::TYPE_AI_TOKEN,
                'code' => 'pro',
                'name' => 'Pro Studio',
                'description' => 'Photoshoot katalog lengkap, multi-variasi rasio, & seasonal campaign.',
                'price' => 120000,
                'duration_days' => null,
                'token_quantity' => 300,
                'storage_bytes' => null,
                'token_expiry_days' => 30,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'type' => BillingPackage::TYPE_AI_TOKEN,
                'code' => 'sultan',
                'name' => 'Sultan Pack',
                'description' => 'Kapasitas maksimal untuk studio foto produk e-commerce & agensi.',
                'price' => 350000,
                'duration_days' => null,
                'token_quantity' => 1000,
                'storage_bytes' => null,
                'token_expiry_days' => 30,
                'is_active' => true,
                'sort_order' => 4,
            ],
        ];

        foreach ($packages as $pkg) {
            $existing = DB::table('billing_packages')
                ->where('type', $pkg['type'])
                ->where('code', $pkg['code'])
                ->first();

            if ($existing) {
                DB::table('billing_packages')
                    ->where('id', $existing->id)
                    ->update(array_merge($pkg, ['updated_at' => $now]));
            } else {
                DB::table('billing_packages')->insert(array_merge($pkg, [
                    'id' => (string) Str::uuid(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]));
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('billing_packages')) {
            DB::table('billing_packages')
                ->where('type', BillingPackage::TYPE_AI_TOKEN)
                ->whereIn('code', ['starter', 'growth', 'pro', 'sultan'])
                ->delete();
        }
    }
};
