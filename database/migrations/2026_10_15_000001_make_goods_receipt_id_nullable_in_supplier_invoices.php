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
        if (Schema::hasTable('supplier_invoices')) {
            Schema::table('supplier_invoices', function (Blueprint $table): void {
                $table->uuid('goods_receipt_id')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('supplier_invoices')) {
            Schema::table('supplier_invoices', function (Blueprint $table): void {
                $table->uuid('goods_receipt_id')->nullable(false)->change();
            });
        }
    }
};
