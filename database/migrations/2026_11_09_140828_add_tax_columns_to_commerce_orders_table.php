<?php

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
        if (Schema::hasTable('commerce_orders')) {
            Schema::table('commerce_orders', function (Blueprint $table): void {
                if (! Schema::hasColumn('commerce_orders', 'tax_percentage')) {
                    $table->decimal('tax_percentage', 5, 2)->default(0.00)->after('discount_amount');
                }
                if (! Schema::hasColumn('commerce_orders', 'tax_amount')) {
                    $table->decimal('tax_amount', 15, 2)->default(0.00)->after('tax_percentage');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('commerce_orders')) {
            Schema::table('commerce_orders', function (Blueprint $table): void {
                if (Schema::hasColumn('commerce_orders', 'tax_amount')) {
                    $table->dropColumn('tax_amount');
                }
                if (Schema::hasColumn('commerce_orders', 'tax_percentage')) {
                    $table->dropColumn('tax_percentage');
                }
            });
        }
    }
};
