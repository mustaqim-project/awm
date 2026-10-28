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
        // 1. Proyek Merchant Studio Inspirasi & Produk
        if (!Schema::hasTable('business_inspiration_projects')) {
            Schema::create('business_inspiration_projects', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
                $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignUuid('inspiration_item_id')->nullable()->constrained('inspiration_items')->nullOnDelete();
                $table->foreignUuid('product_id')->nullable()->constrained('products')->nullOnDelete();
                $table->string('name', 200);
                $table->text('description')->nullable();
                $table->string('target_industry', 100)->nullable();
                $table->string('target_platform', 50)->default('instagram'); // 'instagram', 'tiktok', 'facebook', 'shopee', 'tokopedia', 'banner'
                $table->string('status', 30)->default('draft'); // 'draft', 'generating', 'completed', 'archived'
                $table->timestamp('last_activity_at')->nullable();
                $table->timestamps();

                $table->index(['business_id', 'status']);
                $table->index(['business_id', 'product_id']);
                $table->index('last_activity_at');
            });
        }

        // 2. Aset Foto Produk, Packshot & Logo Referensi Proyek Merchant
        if (!Schema::hasTable('business_inspiration_project_assets')) {
            Schema::create('business_inspiration_project_assets', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
                $table->foreignUuid('project_id')->constrained('business_inspiration_projects')->cascadeOnDelete();
                $table->string('asset_type', 50); // 'original_product', 'product_packshot', 'reference_style', 'logo_overlay', 'background'
                $table->text('file_path');
                $table->string('original_filename', 255)->nullable();
                $table->string('mime_type', 100)->nullable();
                $table->unsignedBigInteger('file_size_bytes')->default(0);
                $table->integer('width')->nullable();
                $table->integer('height')->nullable();
                $table->boolean('is_primary')->default(false);
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['business_id', 'project_id']);
                $table->index(['project_id', 'asset_type']);
            });
        }

        // 3. Versi Prompt Rekayasa AI, Parameter Model & Guardrail
        if (!Schema::hasTable('business_inspiration_prompt_versions')) {
            Schema::create('business_inspiration_prompt_versions', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
                $table->foreignUuid('project_id')->constrained('business_inspiration_projects')->cascadeOnDelete();
                $table->integer('version_number')->default(1);
                $table->text('positive_prompt');
                $table->text('negative_prompt')->nullable();
                $table->string('style_preset', 100)->nullable(); // 'cinematic', 'minimalist_luxury', 'hyperreal_fnb', 'studio_clean'
                $table->string('aspect_ratio', 20)->default('1:1'); // '1:1', '4:5', '9:16', '16:9'
                $table->json('prompt_parameters')->nullable(); // guidance_scale, seed, steps, lora_weights
                $table->boolean('is_approved')->default(true);
                $table->string('guardrail_status', 30)->default('passed'); // 'passed', 'flagged', 'blocked'
                $table->text('guardrail_notes')->nullable();
                $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->unique(['project_id', 'version_number'], 'insp_project_version_uniq');
                $table->index(['business_id', 'guardrail_status'], 'bip_vers_biz_guardrail_idx');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_inspiration_prompt_versions');
        Schema::dropIfExists('business_inspiration_project_assets');
        Schema::dropIfExists('business_inspiration_projects');
    }
};
