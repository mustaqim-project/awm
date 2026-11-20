<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Business;
use App\Models\Customer;
use App\Models\GoodsReceipt;
use App\Models\JournalEntry;
use App\Models\LaborRate;
use App\Models\Location;
use App\Models\Machine;
use App\Models\Material;
use App\Models\ModifierGroup;
use App\Models\PosOrder;
use App\Models\PosPrinter;
use App\Models\PosPrinterRoutingRule;
use App\Models\PosTable;
use App\Models\Product;
use App\Models\ProductionOrder;
use App\Models\PurchaseOrder;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class MultiIndustryDemoMasterSeeder extends Seeder
{
    /**
     * Run the multi-industry demo master seeders and perform automated audit verification.
     */
    public function run(): void
    {
        $this->command?->info('================================================================================');
        $this->command?->info('  COOCA ERP — MASTER SEEDER: FOUR MULTI-INDUSTRY DEMO DATASETS (MILESTONE M2)  ');
        $this->command?->info('================================================================================');

        $startTime = microtime(true);

        // 1. Execute the 4 industry seeders
        $this->call([
            CoocaMartRetailSeeder::class,
            CoocaBistroFnbSeeder::class,
            CoocaManufacturingSeeder::class,
            CoocaAutoCareSeeder::class,
        ]);

        // 2. Ensure global demo & testing accounts are attached to all 4 businesses
        $this->ensureSharedDemoAccounts();

        // 3. Automated Post-Seeder Audit & Verification
        $this->verifyDatasetIntegrityAndAccountingBalance();

        $elapsed = round(microtime(true) - $startTime, 2);
        $this->command?->info("✓ Seluruh seeder multi-industri selesai & terverifikasi dalam {$elapsed} detik.");
    }

    /**
     * Ensure shared testing accounts (demo@cooca.id & testing@cooca.id) are attached as owners to all 4 demo businesses.
     */
    private function ensureSharedDemoAccounts(): void
    {
        $ownerRole = Role::whereNull('business_id')->where('slug', 'owner')->first();
        $targetSlugs = [
            'cooca-mart-indonesia',
            'cooca-bistro-coffee',
            'cooca-manufacturing-indonesia',
            'cooca-autocare',
        ];

        $businesses = Business::whereIn('slug', $targetSlugs)->get();

        $sharedAccounts = [
            ['name' => 'Demo User (Multi-Industri)', 'email' => 'demo@cooca.id'],
            ['name' => 'Testing User (Multi-Industri)', 'email' => 'testing@cooca.id'],
        ];

        foreach ($sharedAccounts as $acc) {
            $user = User::updateOrCreate(
                ['email' => $acc['email']],
                [
                    'name' => $acc['name'],
                    'phone' => '0812' . rand(10000000, 99999999),
                    'password' => Hash::make('password123'),
                    'email_verified_at' => now(),
                    'onboarding_completed' => true,
                    'onboarding_completed_at' => now(),
                    'onboarding_current_step' => 0,
                    'onboarding_version' => 1,
                ]
            );

            foreach ($businesses as $biz) {
                $biz->users()->syncWithoutDetaching([
                    $user->id => [
                        'id' => (string) Str::uuid(),
                        'role' => 'owner',
                        'role_id' => $ownerRole?->id,
                    ],
                ]);

                if (! $user->active_business_id) {
                    $user->update(['active_business_id' => $biz->id]);
                }
            }
        }
    }

    /**
     * Verify all entity count thresholds and double-entry accounting equality.
     */
    private function verifyDatasetIntegrityAndAccountingBalance(): void
    {
        $this->command?->info('');
        $this->command?->info('--- AUTOMATED VERIFICATION MATRIX & DOUBLE-ENTRY ACCOUNTING AUDIT ---');

        $specs = [
            'cooca-mart-indonesia' => [
                'name' => 'Retail & Distribution (COOCA Mart Indonesia)',
                'min_locations' => 3,
                'min_products' => 35,
                'min_customers' => 22,
                'min_suppliers' => 12,
                'min_orders' => 5,
            ],
            'cooca-bistro-coffee' => [
                'name' => 'Food & Beverage (COOCA Bistro & Coffee)',
                'min_locations' => 3,
                'min_products' => 32,
                'min_customers' => 20,
                'min_suppliers' => 8,
                'min_orders' => 5,
            ],
            'cooca-manufacturing-indonesia' => [
                'name' => 'Manufacturing (COOCA Manufacturing Indonesia)',
                'min_locations' => 2,
                'min_products' => 16,
                'min_customers' => 12,
                'min_suppliers' => 10,
                'min_orders' => 2,
            ],
            'cooca-autocare' => [
                'name' => 'Automotive Workshop (COOCA AutoCare)',
                'min_locations' => 2,
                'min_products' => 54, // 22 services + 32 spareparts
                'min_customers' => 32,
                'min_suppliers' => 8,
                'min_orders' => 5,
            ],
        ];

        $allBalanced = true;
        $allPassed = true;

        foreach ($specs as $slug => $criteria) {
            $biz = Business::where('slug', $slug)->first();

            if (! $biz) {
                $this->command?->error("❌ Bisnis dengan slug '{$slug}' TIDAK DITEMUKAN!");
                $allPassed = false;
                continue;
            }

            \App\Support\Context::setBusiness($biz);

            $locCount = Location::withoutGlobalScopes()->where('business_id', $biz->id)->count();
            $prodCount = Product::withoutGlobalScopes()->where('business_id', $biz->id)->count();
            $custCount = Customer::withoutGlobalScopes()->where('business_id', $biz->id)->count();
            $supCount = Supplier::withoutGlobalScopes()->where('business_id', $biz->id)->count();
            $orderCount = PosOrder::withoutGlobalScopes()->where('business_id', $biz->id)->count();
            $matCount = Material::withoutGlobalScopes()->where('business_id', $biz->id)->count();

            // Journal Balance Verification
            $journalSummary = DB::table('journal_entries')
                ->where('business_id', $biz->id)
                ->selectRaw('COALESCE(SUM(total_debit), 0) as total_debit, COALESCE(SUM(total_credit), 0) as total_credit, COUNT(*) as count')
                ->first();

            $debit = (float) ($journalSummary->total_debit ?? 0);
            $credit = (float) ($journalSummary->total_credit ?? 0);
            $journalCount = (int) ($journalSummary->count ?? 0);

            $diff = abs($debit - $credit);
            $isBalanced = ($diff < 0.001) && ($debit > 0);

            if (! $isBalanced) {
                $allBalanced = false;
            }

            $countChecksPass = (
                $locCount >= $criteria['min_locations'] &&
                $prodCount >= $criteria['min_products'] &&
                $custCount >= $criteria['min_customers'] &&
                $supCount >= $criteria['min_suppliers'] &&
                $orderCount >= $criteria['min_orders']
            );

            if (! $countChecksPass) {
                $allPassed = false;
            }

            $this->command?->line(sprintf(
                "[%s] %s\n  • Locations: %d (min %d) %s\n  • Products: %d (min %d) %s\n  • Customers: %d (min %d) %s\n  • Suppliers: %d (min %d) %s\n  • POS/SPK Orders: %d (min %d) %s\n  • Materials: %d\n  • Journals: %d entries | Total Debit: Rp %s | Total Credit: Rp %s [Diff: Rp %s] %s\n",
                $isBalanced && $countChecksPass ? 'PASS' : 'FAIL',
                $criteria['name'],
                $locCount, $criteria['min_locations'], $locCount >= $criteria['min_locations'] ? '✓' : '✗',
                $prodCount, $criteria['min_products'], $prodCount >= $criteria['min_products'] ? '✓' : '✗',
                $custCount, $criteria['min_customers'], $custCount >= $criteria['min_customers'] ? '✓' : '✗',
                $supCount, $criteria['min_suppliers'], $supCount >= $criteria['min_suppliers'] ? '✓' : '✗',
                $orderCount, $criteria['min_orders'], $orderCount >= $criteria['min_orders'] ? '✓' : '✗',
                $matCount,
                $journalCount,
                number_format($debit, 2, ',', '.'),
                number_format($credit, 2, ',', '.'),
                number_format($diff, 2, ',', '.'),
                $isBalanced ? '✓ STRICT BALANCE' : '✗ UNBALANCED'
            ));
        }

        if (! $allBalanced || ! $allPassed) {
            throw new \RuntimeException('Integrity audit failed: One or more demo industries failed count thresholds or double-entry balance check.');
        }

        $this->command?->info('================================================================================');
        $this->command?->info('  ALL 4 DEMO DATASETS VERIFIED SUCCESSFULLY — 100% BALANCED DOUBLE ENTRY  ');
        $this->command?->info('================================================================================');
    }
}
