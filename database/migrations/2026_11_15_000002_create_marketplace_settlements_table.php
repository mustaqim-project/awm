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
        if (! Schema::hasTable('marketplace_settlements')) {
            Schema::create('marketplace_settlements', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('business_id')->constrained()->cascadeOnDelete();
                $table->foreignUuid('marketplace_account_id')->constrained()->cascadeOnDelete();
                $table->string('channel', 50)->index();
                $table->string('settlement_sn', 100);
                $table->timestamp('payout_date')->nullable()->index();
                $table->integer('total_orders_count')->default(0);
                $table->decimal('gross_sales', 15, 2)->default(0);
                $table->decimal('total_discounts', 15, 2)->default(0);
                $table->decimal('total_fees', 15, 2)->default(0);
                $table->decimal('total_shipping_charges', 15, 2)->default(0);
                $table->decimal('total_refunds', 15, 2)->default(0);
                $table->decimal('total_adjustments', 15, 2)->default(0);
                $table->decimal('net_payout_amount', 15, 2)->default(0);
                $table->string('bank_name', 100)->nullable();
                $table->string('bank_account_number', 100)->nullable();
                $table->string('status', 50)->default('completed'); // pending, processing, completed, reconciled, failed
                $table->foreignUuid('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
                $table->foreignUuid('cash_account_id')->nullable()->constrained('cash_accounts')->nullOnDelete();
                $table->timestamp('reconciled_at')->nullable();
                $table->json('raw_payload')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->unique(['business_id', 'channel', 'settlement_sn'], 'mp_settlements_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketplace_settlements');
    }
};
