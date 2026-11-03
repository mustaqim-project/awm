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
        if (Schema::hasTable('ai_credit_transactions')) {
            Schema::table('ai_credit_transactions', function (Blueprint $table): void {
                if (!Schema::hasColumn('ai_credit_transactions', 'balance_before')) {
                    $table->integer('balance_before')->nullable()->after('amount');
                }
                if (!Schema::hasColumn('ai_credit_transactions', 'exchange_rate')) {
                    $table->decimal('exchange_rate', 14, 4)->nullable()->after('balance_after');
                }
                if (!Schema::hasColumn('ai_credit_transactions', 'api_cost_usd')) {
                    $table->decimal('api_cost_usd', 10, 6)->nullable()->after('exchange_rate');
                }
                if (!Schema::hasColumn('ai_credit_transactions', 'tokens_usage')) {
                    $table->json('tokens_usage')->nullable()->after('api_cost_usd');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('ai_credit_transactions')) {
            Schema::table('ai_credit_transactions', function (Blueprint $table): void {
                $columns = ['balance_before', 'exchange_rate', 'api_cost_usd', 'tokens_usage'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('ai_credit_transactions', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};

