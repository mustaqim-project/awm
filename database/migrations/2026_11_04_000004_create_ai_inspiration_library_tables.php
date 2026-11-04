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
        // 1. Tag & Taksonomi Inspirasi Visual
        if (!Schema::hasTable('inspiration_tags')) {
            Schema::create('inspiration_tags', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->string('name', 100);
                $table->string('slug', 120)->unique();
                $table->timestamps();

                $table->index('name');
            });
        }

        // 2. Koleksi Inspirasi (Global Curated COOCA, Personal Merchant, & User Saved)
        if (!Schema::hasTable('inspiration_collections')) {
            Schema::create('inspiration_collections', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->string('owner_type', 30)->default('business'); // 'global', 'business', 'user'
                $table->foreignUuid('business_id')->nullable()->constrained('businesses')->cascadeOnDelete();
                $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('title', 200);
                $table->string('slug', 250);
                $table->text('description')->nullable();
                $table->foreignUuid('cover_media_id')->nullable()->constrained('storage_files')->nullOnDelete();
                $table->text('cover_image_url')->nullable();
                $table->string('visibility', 30)->default('public'); // 'public', 'private', 'unlisted'
                $table->string('status', 30)->default('active'); // 'active', 'archived'
                $table->boolean('is_featured')->default(false);
                $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['business_id', 'status', 'visibility'], 'insp_coll_biz_stat_vis_idx');
                $table->index(['owner_type', 'is_featured', 'status'], 'insp_coll_own_feat_stat_idx');
                $table->index('slug');
            });
        }

        // 3. Item Relasi Koleksi Inspirasi
        if (!Schema::hasTable('inspiration_collection_items')) {
            Schema::create('inspiration_collection_items', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('collection_id')->constrained('inspiration_collections')->cascadeOnDelete();
                $table->string('asset_type', 50)->default('inspiration_item'); // 'inspiration_item', 'external', 'generation_asset', 'user_upload', 'inspiration_asset'
                $table->uuid('asset_id')->nullable();
                $table->string('external_asset_id', 255)->nullable();
                $table->integer('sort_order')->default(0);
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['collection_id', 'sort_order'], 'insp_coll_item_order_idx');
                $table->index(['asset_type', 'asset_id'], 'insp_coll_item_type_id_idx');
                $table->index(['asset_type', 'external_asset_id'], 'insp_coll_item_ext_idx');
            });
        }

        // 4. Favorit Inspirasi Multi-Tenant
        if (!Schema::hasTable('inspiration_favorites')) {
            Schema::create('inspiration_favorites', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('user_id')->nullable()->constrained('users')->cascadeOnDelete();
                $table->foreignUuid('business_id')->nullable()->constrained('businesses')->cascadeOnDelete();
                $table->string('asset_type', 50)->default('inspiration_item'); // 'inspiration_item', 'external', 'generation_asset', 'user_upload', 'inspiration_asset'
                $table->uuid('asset_id')->nullable();
                $table->string('external_id', 255)->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['business_id', 'user_id', 'asset_type'], 'insp_fav_biz_usr_type_idx');
                $table->index(['asset_type', 'asset_id'], 'insp_fav_type_asset_idx');
                $table->index(['asset_type', 'external_id'], 'insp_fav_type_ext_idx');
            });
        }

        // 5. Aset Inspirasi Sah & Permanen
        if (!Schema::hasTable('inspiration_assets')) {
            Schema::create('inspiration_assets', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->string('source_type', 50)->default('curated'); // 'curated', 'external', 'generated', 'uploaded'
                $table->foreignUuid('source_id')->nullable()->constrained('inspiration_sources')->nullOnDelete();
                $table->string('external_asset_id', 255)->nullable();
                $table->foreignUuid('media_id')->nullable()->constrained('storage_files')->nullOnDelete();
                $table->foreignUuid('business_id')->nullable()->constrained('businesses')->cascadeOnDelete();
                $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('title', 255);
                $table->text('description')->nullable();
                $table->text('source_url')->nullable();
                $table->text('image_url')->nullable();
                $table->text('thumbnail_url')->nullable();
                $table->integer('width')->nullable();
                $table->integer('height')->nullable();
                $table->string('aspect_ratio', 20)->default('1:1');
                $table->string('mime_type', 100)->nullable();
                $table->unsignedBigInteger('file_size')->default(0);
                $table->string('author_name', 150)->nullable();
                $table->text('author_profile_url')->nullable();
                $table->string('source_name', 100)->nullable();
                $table->string('license_name', 100)->default('Commercial');
                $table->text('license_url')->nullable();
                $table->text('attribution_text')->nullable();
                $table->text('usage_policy')->nullable();
                $table->boolean('commercial_usage_allowed')->default(true);
                $table->boolean('redistribution_allowed')->default(false);
                $table->string('storage_policy', 50)->default('permanent'); // 'permanent', 'temporary', 'hotlink'
                $table->timestamp('expires_at')->nullable();
                $table->json('metadata')->nullable();
                $table->string('moderation_status', 30)->default('approved'); // 'pending', 'approved', 'rejected', 'flagged'
                $table->timestamps();

                $table->index(['business_id', 'source_type', 'moderation_status'], 'insp_asset_biz_src_mod_idx');
                $table->index(['source_type', 'moderation_status'], 'insp_asset_src_mod_idx');
                $table->index('external_asset_id');
            });
        }

        // 6. Log Sinkronisasi Sumber Eksternal
        if (!Schema::hasTable('inspiration_sync_logs')) {
            Schema::create('inspiration_sync_logs', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('source_id')->nullable()->constrained('inspiration_sources')->nullOnDelete();
                $table->string('provider_code', 50);
                $table->string('operation', 50)->default('sync');
                $table->string('status', 30)->default('success'); // 'success', 'failed', 'partial'
                $table->integer('request_count')->default(0);
                $table->integer('result_count')->default(0);
                $table->text('error_message')->nullable();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('finished_at')->nullable();
                $table->timestamps();

                $table->index(['provider_code', 'status', 'created_at'], 'insp_sync_code_stat_time_idx');
            });
        }

        // 7. Log Pemakaian & Telemetri Inspirasi AI
        if (!Schema::hasTable('inspiration_usage_logs')) {
            Schema::create('inspiration_usage_logs', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('business_id')->nullable()->constrained('businesses')->cascadeOnDelete();
                $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('action', 60); // 'search', 'view', 'favorite', 'add_to_collection', 'use_as_reference', 'generate_conversion'
                $table->string('source', 60); // 'curated', 'pexels', 'pixabay', 'unsplash', 'pinterest', 'generated', 'personal'
                $table->string('asset_reference', 255)->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['business_id', 'action', 'created_at'], 'insp_usage_biz_act_time_idx');
                $table->index(['source', 'action'], 'insp_usage_src_act_idx');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspiration_usage_logs');
        Schema::dropIfExists('inspiration_sync_logs');
        Schema::dropIfExists('inspiration_assets');
        Schema::dropIfExists('inspiration_favorites');
        Schema::dropIfExists('inspiration_collection_items');
        Schema::dropIfExists('inspiration_collections');
        Schema::dropIfExists('inspiration_tags');
    }
};
