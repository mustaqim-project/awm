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
        Schema::table('products', function (Blueprint $table): void {
            if (!Schema::hasColumn('products', 'wholesale_min_qty')) {
                $col = $table->decimal('wholesale_min_qty', 15, 2)->nullable();
                if (Schema::hasColumn('products', 'selling_price')) {
                    $col->after('selling_price');
                }
            }
            if (!Schema::hasColumn('products', 'wholesale_price')) {
                $col = $table->decimal('wholesale_price', 15, 2)->nullable();
                if (Schema::hasColumn('products', 'wholesale_min_qty')) {
                    $col->after('wholesale_min_qty');
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $columns = [];
            if (Schema::hasColumn('products', 'wholesale_min_qty')) {
                $columns[] = 'wholesale_min_qty';
            }
            if (Schema::hasColumn('products', 'wholesale_price')) {
                $columns[] = 'wholesale_price';
            }
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
