<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('subscription_pricing_batches') && ! Schema::hasColumn('subscription_pricing_batches', 'hybrid_operator')) {
            Schema::table('subscription_pricing_batches', function (Blueprint $table): void {
                $table->string('hybrid_operator', 16)->default('either')->after('trigger_mode');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('subscription_pricing_batches') && Schema::hasColumn('subscription_pricing_batches', 'hybrid_operator')) {
            Schema::table('subscription_pricing_batches', function (Blueprint $table): void {
                $table->dropColumn('hybrid_operator');
            });
        }
    }
};
