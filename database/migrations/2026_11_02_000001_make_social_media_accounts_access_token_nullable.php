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
        if (Schema::hasTable('social_media_accounts')) {
            Schema::table('social_media_accounts', function (Blueprint $table) {
                $table->text('access_token')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('social_media_accounts')) {
            Schema::table('social_media_accounts', function (Blueprint $table) {
                $table->text('access_token')->nullable(false)->change();
            });
        }
    }
};

