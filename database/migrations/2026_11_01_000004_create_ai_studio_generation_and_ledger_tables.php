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
        // 1. Eksekusi Generasi Gambar AI Multi-Provider
        if (!Schema::hasTable('business_inspiration_generations')) {
            Schema::create('business_inspiration_generations', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
                $table->foreignUuid('project_id')->constrained('business_inspiration_projects')->cascadeOnDelete();
                $table->foreignUuid('prompt_version_id')->constrained('business_inspiration_prompt_versions')->cascadeOnDelete();
                $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('provider', 50)->default('ideogram'); // 'ideogram', 'fal_ai', 'replicate', 'recraft', 'gemini'
                $table->string('model_name', 100);
                $table->string('status', 30)->default('queued'); // 'queued', 'processing', 'completed', 'failed', 'refunded'
                $table->integer('credit_cost')->default(1);
                $table->string('idempotency_key', 100)->nullable();
                $table->integer('batch_count')->default(1);
                $table->text('error_message')->nullable();
                $table->string('provider_job_id', 150)->nullable()->index();
                $table->integer('provider_latency_ms')->nullable();
                $table->json('generation_meta')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();

                $table->index(['business_id', 'status']);
                $table->index(['project_id', 'status']);
                $table->index('idempotency_key');
                $table->index('created_at');
            });
        }

        // 2. Aset Output Gambar Hasil Generasi AI & Upscale
        if (!Schema::hasTable('business_inspiration_generation_assets')) {
            Schema::create('business_inspiration_generation_assets', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
                $table->foreignUuid('generation_id')->constrained('business_inspiration_generations')->cascadeOnDelete();
                $table->text('output_image_url');
                $table->text('storage_path')->nullable();
                $table->text('thumbnail_url')->nullable();
                $table->string('aspect_ratio', 20)->default('1:1');
                $table->integer('width')->nullable();
                $table->integer('height')->nullable();
                $table->boolean('is_watermarked')->default(false);
                $table->boolean('is_selected_favorite')->default(false);
                $table->boolean('is_upscaled')->default(false);
                $table->foreignUuid('storage_file_id')->nullable()->constrained('storage_files')->nullOnDelete();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['business_id', 'generation_id'], 'insp_gen_asset_biz_gen_idx');
                $table->index('is_selected_favorite', 'insp_gen_asset_fav_idx');
            });
        } else {
            try {
                Schema::table('business_inspiration_generation_assets', function (Blueprint $table): void {
                    $table->index('is_selected_favorite', 'insp_gen_asset_fav_idx');
                });
            } catch (\Throwable $e) {
                // Abaikan jika indeks sudah terpasang
            }
        }

        // 3. Ledger Transaksi Saldo Kredit AI (Append-Only, Anti Double Spending)
        if (!Schema::hasTable('ai_credit_transactions')) {
            Schema::create('ai_credit_transactions', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
                $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignUuid('generation_id')->nullable()->constrained('business_inspiration_generations')->nullOnDelete();
                $table->string('type', 30); // 'topup', 'usage', 'refund', 'bonus', 'adjustment'
                $table->integer('amount'); // positif untuk topup/refund, negatif untuk pemakaian
                $table->integer('balance_after');
                $table->string('idempotency_key', 100);
                $table->string('description', 255);
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->unique(['business_id', 'idempotency_key'], 'ai_credit_biz_idemp_uniq');
                $table->index(['business_id', 'type']);
                $table->index(['business_id', 'created_at']);
            });
        }

        // 4. Tambahkan saldo cache ai_credits_balance ke tabel businesses jika belum ada
        if (Schema::hasTable('businesses') && !Schema::hasColumn('businesses', 'ai_credits_balance')) {
            Schema::table('businesses', function (Blueprint $table): void {
                $table->integer('ai_credits_balance')->default(0);
                $table->index('ai_credits_balance');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('businesses') && Schema::hasColumn('businesses', 'ai_credits_balance')) {
            Schema::table('businesses', function (Blueprint $table): void {
                $table->dropIndex(['ai_credits_balance']);
                $table->dropColumn('ai_credits_balance');
            });
        }

        Schema::dropIfExists('ai_credit_transactions');
        Schema::dropIfExists('business_inspiration_generation_assets');
        Schema::dropIfExists('business_inspiration_generations');
    }
};
