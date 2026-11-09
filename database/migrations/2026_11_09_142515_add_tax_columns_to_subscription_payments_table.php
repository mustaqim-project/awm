<?php

declare(strict_types=1);

use App\Models\PlatformChartOfAccount;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('subscription_payments', function (Blueprint $table): void {
            if (! Schema::hasColumn('subscription_payments', 'subtotal')) {
                $table->decimal('subtotal', 15, 2)->nullable()->after('amount');
            }
            if (! Schema::hasColumn('subscription_payments', 'tax_rate')) {
                $table->decimal('tax_rate', 5, 2)->default(0.00)->after('subtotal');
            }
            if (! Schema::hasColumn('subscription_payments', 'tax_amount')) {
                $table->decimal('tax_amount', 15, 2)->default(0.00)->after('tax_rate');
            }
            if (! Schema::hasColumn('subscription_payments', 'is_tax_inclusive')) {
                $table->boolean('is_tax_inclusive')->default(false)->after('tax_amount');
            }
        });

        // Ensure System COA accounts for Platform Tax exist
        if (Schema::hasTable('platform_chart_of_accounts')) {
            $taxAccounts = [
                [
                    'code' => '2140',
                    'name' => 'Hutang Pajak Penjualan Platform (PPN Keluaran SaaS & Top-Up)',
                    'type' => PlatformChartOfAccount::TYPE_LIABILITY,
                    'normal_balance' => PlatformChartOfAccount::BALANCE_CREDIT,
                    'category_group' => 'hutang_lancar',
                    'description' => 'Kewajiban PPN atas penjualan lisensi software SaaS dan kuota top-up AI/storage',
                    'is_system' => true,
                    'is_active' => true,
                ],
                [
                    'code' => '2150',
                    'name' => 'Hutang Pajak Penghasilan (PPh Platform Cooca)',
                    'type' => PlatformChartOfAccount::TYPE_LIABILITY,
                    'normal_balance' => PlatformChartOfAccount::BALANCE_CREDIT,
                    'category_group' => 'hutang_lancar',
                    'description' => 'Kewajiban PPh Badan / PPh Pasal 21/23/25 platform Cooca yang belum disetor',
                    'is_system' => true,
                    'is_active' => true,
                ],
                [
                    'code' => '1140',
                    'name' => 'Uang Muka Pajak & PPN Masukan Platform',
                    'type' => PlatformChartOfAccount::TYPE_ASSET,
                    'normal_balance' => PlatformChartOfAccount::BALANCE_DEBIT,
                    'category_group' => 'aktiva_lancar',
                    'description' => 'PPN Masukan dari biaya server/infrastruktur dan pembayaran pajak di muka',
                    'is_system' => true,
                    'is_active' => true,
                ],
            ];

            foreach ($taxAccounts as $acc) {
                PlatformChartOfAccount::firstOrCreate(
                    ['code' => $acc['code']],
                    $acc
                );
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_payments', function (Blueprint $table): void {
            $cols = [];
            if (Schema::hasColumn('subscription_payments', 'is_tax_inclusive')) {
                $cols[] = 'is_tax_inclusive';
            }
            if (Schema::hasColumn('subscription_payments', 'tax_amount')) {
                $cols[] = 'tax_amount';
            }
            if (Schema::hasColumn('subscription_payments', 'tax_rate')) {
                $cols[] = 'tax_rate';
            }
            if (Schema::hasColumn('subscription_payments', 'subtotal')) {
                $cols[] = 'subtotal';
            }
            if (! empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
