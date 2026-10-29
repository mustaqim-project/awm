<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table): void {
            if (!Schema::hasColumn('businesses', 'is_test')) {
                $table->boolean('is_test')->default(false);
            }
            if (!Schema::hasColumn('businesses', 'is_demo')) {
                $table->boolean('is_demo')->default(false);
            }
            if (!Schema::hasColumn('businesses', 'is_public_listed')) {
                $table->boolean('is_public_listed')->default(true);
            }
        });

        // Mark known test and review stores (Prompt 3 compliance)
        if (Schema::hasColumn('businesses', 'is_test') && Schema::hasColumn('businesses', 'slug')) {
            DB::table('businesses')
                ->whereIn('slug', ['meta-reviewer-store', 'tester-tiktok', 'kopi-senja-utama'])
                ->update([
                    'is_test' => true,
                    'is_public_listed' => false,
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table): void {
            if (Schema::hasColumn('businesses', 'is_public_listed')) {
                $table->dropColumn('is_public_listed');
            }
            if (Schema::hasColumn('businesses', 'is_demo')) {
                $table->dropColumn('is_demo');
            }
            if (Schema::hasColumn('businesses', 'is_test')) {
                $table->dropColumn('is_test');
            }
        });
    }
};
