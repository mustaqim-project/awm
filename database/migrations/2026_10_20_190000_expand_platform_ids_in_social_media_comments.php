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
     * Expand platform comment, post, parent, and from ID columns in social_media_comments
     * to prevent truncation on long Meta/Instagram base64 message IDs (which exceed 150 chars).
     */
    public function up(): void
    {
        if (Schema::hasTable('social_media_comments')) {
            try {
                DB::statement('ALTER TABLE social_media_comments MODIFY platform_comment_id VARCHAR(500)');
            } catch (\Throwable $e) {
                // Fallback in case of DB engine variance
                Schema::table('social_media_comments', function (Blueprint $table) {
                    $table->string('platform_comment_id', 500)->change();
                });
            }

            try {
                DB::statement('ALTER TABLE social_media_comments MODIFY platform_post_id VARCHAR(500)');
            } catch (\Throwable $e) {
                Schema::table('social_media_comments', function (Blueprint $table) {
                    $table->string('platform_post_id', 500)->change();
                });
            }

            try {
                DB::statement('ALTER TABLE social_media_comments MODIFY parent_comment_id VARCHAR(500) NULL');
            } catch (\Throwable $e) {
                Schema::table('social_media_comments', function (Blueprint $table) {
                    $table->string('parent_comment_id', 500)->nullable()->change();
                });
            }

            try {
                DB::statement('ALTER TABLE social_media_comments MODIFY from_id VARCHAR(500) NULL');
            } catch (\Throwable $e) {
                Schema::table('social_media_comments', function (Blueprint $table) {
                    $table->string('from_id', 500)->nullable()->change();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('social_media_comments')) {
            try {
                DB::statement('ALTER TABLE social_media_comments MODIFY platform_comment_id VARCHAR(150)');
                DB::statement('ALTER TABLE social_media_comments MODIFY platform_post_id VARCHAR(150)');
                DB::statement('ALTER TABLE social_media_comments MODIFY parent_comment_id VARCHAR(150) NULL');
                DB::statement('ALTER TABLE social_media_comments MODIFY from_id VARCHAR(150) NULL');
            } catch (\Throwable $e) {
                // Ignore
            }
        }
    }
};
