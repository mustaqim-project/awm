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
        Schema::table('social_media_comments', function (Blueprint $table) {
            if (! Schema::hasColumn('social_media_comments', 'media_url')) {
                $table->text('media_url')->nullable()->after('message');
            }
            if (! Schema::hasColumn('social_media_comments', 'media_type')) {
                $table->string('media_type', 50)->nullable()->default('image')->after('media_url');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('social_media_comments', function (Blueprint $table) {
            if (Schema::hasColumn('social_media_comments', 'media_type')) {
                $table->dropColumn('media_type');
            }
            if (Schema::hasColumn('social_media_comments', 'media_url')) {
                $table->dropColumn('media_url');
            }
        });
    }
};
