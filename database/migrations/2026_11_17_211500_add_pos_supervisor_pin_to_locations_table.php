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
        if (Schema::hasTable('locations') && ! Schema::hasColumn('locations', 'pos_supervisor_pin')) {
            Schema::table('locations', function (Blueprint $table): void {
                $table->string('pos_supervisor_pin', 60)->nullable()->after('is_active');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('locations') && Schema::hasColumn('locations', 'pos_supervisor_pin')) {
            Schema::table('locations', function (Blueprint $table): void {
                $table->dropColumn('pos_supervisor_pin');
            });
        }
    }
};
