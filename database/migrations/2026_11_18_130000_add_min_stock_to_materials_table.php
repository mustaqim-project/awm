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
        Schema::table('materials', function (Blueprint $table): void {
            if (! Schema::hasColumn('materials', 'min_stock')) {
                $table->decimal('min_stock', 15, 2)->default(0.00)->after('description');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('materials', function (Blueprint $table): void {
            if (Schema::hasColumn('materials', 'min_stock')) {
                $table->dropColumn('min_stock');
            }
        });
    }
};
