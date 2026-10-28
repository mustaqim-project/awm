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
        // 1. Kategori Inspirasi & Taksonomi Industri
        Schema::create('inspiration_categories', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('slug', 100)->unique();
            $table->string('name', 150);
            $table->string('industry', 100)->nullable(); // 'fashion', 'fnb', 'beauty', 'retail', 'services'
            $table->foreignUuid('parent_id')->nullable()->constrained('inspiration_categories')->nullOnDelete();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['industry', 'is_active']);
            $table->index('sort_order');
        });

        // 2. Katalog Konten Inspirasi Viral & Referensi Visual
        Schema::create('inspiration_items', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('source_id')->constrained('inspiration_sources')->cascadeOnDelete();
            $table->foreignUuid('category_id')->nullable()->constrained('inspiration_categories')->nullOnDelete();
            $table->string('external_id', 150);
            $table->string('title', 255);
            $table->text('caption')->nullable();
            $table->text('canonical_url')->nullable();
            $table->text('thumbnail_url')->nullable();
            $table->string('media_type', 30)->default('image'); // 'image', 'video', 'carousel'
            $table->json('media_urls')->nullable();
            $table->json('tags')->nullable();
            $table->string('industry_target', 100)->nullable();
            $table->unsignedBigInteger('view_count')->default(0);
            $table->unsignedBigInteger('like_count')->default(0);
            $table->unsignedBigInteger('comment_count')->default(0);
            $table->unsignedBigInteger('share_count')->default(0);
            $table->unsignedBigInteger('save_count')->default(0);
            $table->boolean('is_curated')->default(false);
            $table->text('curated_notes')->nullable();
            $table->string('status', 30)->default('active'); // 'active', 'archived', 'hidden'
            $table->timestamp('collected_at')->nullable();
            $table->timestamps();

            $table->unique(['source_id', 'external_id'], 'insp_item_source_ext_uniq');
            $table->index(['status', 'is_curated']);
            $table->index(['industry_target', 'status']);
            $table->index('collected_at');
        });

        // 3. Ekstraksi Analisis Visual Multimodal AI (Komposisi, Tata Cahaya, Style Prompt)
        Schema::create('inspiration_visual_analyses', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('inspiration_item_id')->constrained('inspiration_items')->cascadeOnDelete();
            $table->string('composition', 100)->nullable(); // 'flatlay', 'podium', 'lifestyle', 'close_up', 'macro', 'split_screen'
            $table->string('lighting', 100)->nullable(); // 'studio_soft', 'dramatic_shadow', 'golden_hour', 'neon_cyberpunk'
            $table->json('color_palette')->nullable(); // array of hex colors
            $table->string('color_mood', 100)->nullable(); // 'earthy', 'vibrant', 'minimalist_luxury', 'pastel_warm'
            $table->string('visual_hook', 255)->nullable(); // psychological trigger / angle
            $table->string('cta_pattern', 150)->nullable(); // call-to-action layout placement
            $table->text('extracted_style_prompt')->nullable();
            $table->text('negative_style_prompt')->nullable();
            $table->json('suggested_aspect_ratios')->nullable(); // ['1:1', '4:5', '9:16', '16:9']
            $table->decimal('safety_score', 5, 2)->default(100.00);
            $table->json('metadata')->nullable();
            $table->timestamp('analyzed_at')->nullable();
            $table->timestamps();

            $table->index('composition');
            $table->index('color_mood');
            $table->index('analyzed_at');
        });

        // 4. Trend Score & Algoritma Kecepatan Viralitas
        Schema::create('inspiration_trend_scores', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('inspiration_item_id')->constrained('inspiration_items')->cascadeOnDelete();
            $table->decimal('trend_score', 8, 2)->default(0.00);
            $table->decimal('virality_velocity', 8, 2)->default(0.00);
            $table->decimal('engagement_rate', 6, 4)->default(0.0000);
            $table->decimal('recency_weight', 6, 4)->default(1.0000);
            $table->decimal('source_credibility_weight', 6, 4)->default(1.0000);
            $table->timestamp('scored_at')->nullable();
            $table->timestamps();

            $table->index('trend_score');
            $table->index('virality_velocity');
            $table->index('scored_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspiration_trend_scores');
        Schema::dropIfExists('inspiration_visual_analyses');
        Schema::dropIfExists('inspiration_items');
        Schema::dropIfExists('inspiration_categories');
    }
};
