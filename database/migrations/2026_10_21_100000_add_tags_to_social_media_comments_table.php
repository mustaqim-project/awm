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
        if (Schema::hasTable('social_media_comments')) {
            Schema::table('social_media_comments', function (Blueprint $table): void {
                if (! Schema::hasColumn('social_media_comments', 'tags')) {
                    $table->json('tags')->nullable()->after('status');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('social_media_comments')) {
            Schema::table('social_media_comments', function (Blueprint $table): void {
                if (Schema::hasColumn('social_media_comments', 'tags')) {
                    $table->dropColumn('tags');
                }
            });
        }
    }
};
