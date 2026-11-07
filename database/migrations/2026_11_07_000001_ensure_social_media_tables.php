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
        if (! Schema::hasTable('social_media_accounts')) {
            Schema::create('social_media_accounts', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('business_id')->nullable()->constrained('businesses')->nullOnDelete();
                $table->boolean('is_platform')->default(false)->index();
                $table->string('provider', 30)->default('meta');
                $table->string('platform', 30);
                $table->string('account_id', 100)->index();
                $table->string('account_name');
                $table->string('username', 100)->nullable();
                $table->text('profile_picture_url')->nullable();
                $table->text('access_token')->nullable();
                $table->string('token_type', 50)->default('page_token');
                $table->timestamp('token_expires_at')->nullable();
                $table->string('status', 30)->default('active')->index();
                $table->json('settings')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('social_media_posts')) {
            Schema::create('social_media_posts', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('business_id')->nullable()->constrained('businesses')->nullOnDelete();
                $table->foreignUuid('admin_id')->nullable()->constrained('admins')->nullOnDelete();
                $table->foreignUuid('social_media_account_id')->nullable()->constrained('social_media_accounts')->nullOnDelete();
                $table->boolean('is_platform')->default(false)->index();
                $table->string('platform', 30)->comment('facebook, instagram, threads, linkedin, tiktok');
                $table->string('platform_post_id', 150)->nullable()->index();
                $table->text('content')->nullable();
                $table->string('media_type', 30)->default('text')->comment('text, image, video, carousel');
                $table->json('media_urls')->nullable();
                $table->json('local_media_paths')->nullable();
                $table->string('status', 30)->default('draft')->index();
                $table->string('approval_status', 30)->default('approved')->index();
                $table->foreignUuid('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('reviewed_at')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->json('risk_flags')->nullable();
                $table->text('error_message')->nullable();
                $table->json('metrics')->nullable();
                $table->timestamp('scheduled_at')->nullable();
                $table->timestamp('published_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('social_post_targets')) {
            Schema::create('social_post_targets', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('social_media_post_id')->constrained('social_media_posts')->cascadeOnDelete();
                $table->foreignUuid('social_media_account_id')->nullable()->constrained('social_media_accounts')->nullOnDelete();
                $table->string('provider', 30)->default('meta');
                $table->string('channel', 30)->default('instagram');
                $table->string('content_type', 30)->default('photo');
                $table->string('platform_post_id', 150)->nullable()->index();
                $table->text('custom_caption')->nullable();
                $table->string('status', 30)->default('pending')->index();
                $table->text('error_message')->nullable();
                $table->timestamp('scheduled_at')->nullable();
                $table->timestamp('published_at')->nullable();
                $table->unsignedInteger('retry_count')->default(0);
                $table->timestamp('last_attempt_at')->nullable();
                $table->json('metrics')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('social_post_media')) {
            Schema::create('social_post_media', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('social_media_post_id')->constrained('social_media_posts')->cascadeOnDelete();
                $table->unsignedSmallInteger('sort_order')->default(1);
                $table->string('media_type', 30)->default('image');
                $table->text('media_url');
                $table->string('local_path')->nullable();
                $table->unsignedBigInteger('file_size')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_post_media');
        Schema::dropIfExists('social_post_targets');
        Schema::dropIfExists('social_media_posts');
        Schema::dropIfExists('social_media_accounts');
    }
};
