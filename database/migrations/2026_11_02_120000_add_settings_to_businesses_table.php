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
        Schema::table('businesses', function (Blueprint $table): void {
            if (! Schema::hasColumn('businesses', 'settings')) {
                $table->json('settings')->nullable()->after('disabled_modules');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table): void {
            if (Schema::hasColumn('businesses', 'settings')) {
                $table->dropColumn('settings');
            }
        });
    }
};

