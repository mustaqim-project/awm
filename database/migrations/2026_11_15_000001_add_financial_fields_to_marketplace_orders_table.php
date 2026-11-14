<?php

declare(strict_types=1);

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
        Schema::table('marketplace_orders', function (Blueprint $table): void {
            if (! Schema::hasColumn('marketplace_orders', 'gross_amount')) {
                $table->decimal('gross_amount', 15, 2)->default(0)->after('buyer_phone');
            }
            if (! Schema::hasColumn('marketplace_orders', 'seller_discount')) {
                $table->decimal('seller_discount', 15, 2)->default(0)->after('gross_amount');
            }
            if (! Schema::hasColumn('marketplace_orders', 'platform_discount')) {
                $table->decimal('platform_discount', 15, 2)->default(0)->after('seller_discount');
            }
            if (! Schema::hasColumn('marketplace_orders', 'voucher_code')) {
                $table->string('voucher_code', 100)->nullable()->after('platform_discount');
            }
            if (! Schema::hasColumn('marketplace_orders', 'voucher_amount')) {
                $table->decimal('voucher_amount', 15, 2)->default(0)->after('voucher_code');
            }

            // Itemized Platform Fees
            if (! Schema::hasColumn('marketplace_orders', 'commission_fee')) {
                $table->decimal('commission_fee', 15, 2)->default(0)->after('channel_fee');
            }
            if (! Schema::hasColumn('marketplace_orders', 'service_fee')) {
                $table->decimal('service_fee', 15, 2)->default(0)->after('commission_fee');
            }
            if (! Schema::hasColumn('marketplace_orders', 'transaction_fee')) {
                $table->decimal('transaction_fee', 15, 2)->default(0)->after('service_fee');
            }

            // Shipping & Logistics breakdown
            if (! Schema::hasColumn('marketplace_orders', 'shipping_fee_buyer')) {
                $table->decimal('shipping_fee_buyer', 15, 2)->default(0)->after('transaction_fee');
            }
            if (! Schema::hasColumn('marketplace_orders', 'shipping_fee_seller')) {
                $table->decimal('shipping_fee_seller', 15, 2)->default(0)->after('shipping_fee_buyer');
            }
            if (! Schema::hasColumn('marketplace_orders', 'shipping_subsidy')) {
                $table->decimal('shipping_subsidy', 15, 2)->default(0)->after('shipping_fee_seller');
            }

            // Adjustments & Refunds
            if (! Schema::hasColumn('marketplace_orders', 'refund_amount')) {
                $table->decimal('refund_amount', 15, 2)->default(0)->after('shipping_subsidy');
            }
            if (! Schema::hasColumn('marketplace_orders', 'adjustment_amount')) {
                $table->decimal('adjustment_amount', 15, 2)->default(0)->after('refund_amount');
            }

            // Net Escrow & Settlement Lifecycle
            if (! Schema::hasColumn('marketplace_orders', 'net_settlement_amount')) {
                $table->decimal('net_settlement_amount', 15, 2)->default(0)->after('adjustment_amount');
            }
            if (! Schema::hasColumn('marketplace_orders', 'settlement_status')) {
                $table->string('settlement_status', 50)->default('pending')->index()->after('net_settlement_amount');
                // pending, escrow_holding, settled, payout_completed, disputed, refunded
            }
            if (! Schema::hasColumn('marketplace_orders', 'settled_at')) {
                $table->timestamp('settled_at')->nullable()->after('settlement_status');
            }
            if (! Schema::hasColumn('marketplace_orders', 'payout_reference')) {
                $table->string('payout_reference', 100)->nullable()->index()->after('settled_at');
            }
            if (! Schema::hasColumn('marketplace_orders', 'marketplace_settlement_id')) {
                $table->uuid('marketplace_settlement_id')->nullable()->index()->after('payout_reference');
            }
            if (! Schema::hasColumn('marketplace_orders', 'journal_entry_id')) {
                $table->foreignUuid('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete()->after('marketplace_settlement_id');
            }
            if (! Schema::hasColumn('marketplace_orders', 'settlement_journal_entry_id')) {
                $table->foreignUuid('settlement_journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete()->after('journal_entry_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketplace_orders', function (Blueprint $table): void {
            $table->dropForeign(['journal_entry_id']);
            $table->dropForeign(['settlement_journal_entry_id']);
            $table->dropColumn([
                'gross_amount',
                'seller_discount',
                'platform_discount',
                'voucher_code',
                'voucher_amount',
                'commission_fee',
                'service_fee',
                'transaction_fee',
                'shipping_fee_buyer',
                'shipping_fee_seller',
                'shipping_subsidy',
                'refund_amount',
                'adjustment_amount',
                'net_settlement_amount',
                'settlement_status',
                'settled_at',
                'payout_reference',
                'marketplace_settlement_id',
                'journal_entry_id',
                'settlement_journal_entry_id',
            ]);
        });
    }
};
