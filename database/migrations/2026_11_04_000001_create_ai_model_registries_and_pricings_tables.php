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
        if (!Schema::hasTable('ai_model_registries')) {
            Schema::create('ai_model_registries', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->string('provider', 50)->default('openai');
                $table->string('model_name', 100);
                $table->string('exact_model_id', 100)->unique();
                $table->string('version', 50)->nullable();
                $table->text('description')->nullable();
                $table->string('use_case', 150)->nullable();
                $table->boolean('is_default')->default(false);
                $table->boolean('is_active')->default(true);
                $table->json('capabilities')->nullable();
                $table->json('supported_sizes')->nullable();
                $table->json('supported_qualities')->nullable();
                $table->json('supported_output_formats')->nullable();
                $table->json('parameters_policy')->nullable();
                $table->string('api_endpoint', 255)->nullable();
                $table->integer('rate_limit_rpm')->default(60);
                $table->integer('timeout_seconds')->default(45);
                $table->json('retry_policy')->nullable();
                $table->string('official_doc_url', 255)->nullable();
                $table->timestamp('last_verified_at')->nullable();
                $table->timestamps();

                $table->index(['provider', 'is_active']);
                $table->index(['is_default', 'is_active']);
            });
        }

        if (!Schema::hasTable('ai_model_pricings')) {
            Schema::create('ai_model_pricings', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('model_id')->constrained('ai_model_registries')->cascadeOnDelete();
                $table->string('version', 50)->default('1.0');
                $table->string('billing_mode', 30)->default('token'); // 'token' atau 'per_image'
                $table->string('currency', 10)->default('USD');
                $table->decimal('text_input_rate', 12, 6)->default(5.000000); // USD per 1M tokens
                $table->decimal('cached_text_rate', 12, 6)->default(1.250000); // USD per 1M tokens
                $table->decimal('image_input_rate', 12, 6)->default(8.000000); // USD per 1M tokens
                $table->decimal('cached_image_rate', 12, 6)->default(2.000000); // USD per 1M tokens
                $table->decimal('image_output_rate', 12, 6)->default(30.000000); // USD per 1M tokens
                $table->decimal('fixed_per_image_cost_usd', 10, 6)->nullable();
                $table->timestamp('effective_at')->nullable();
                $table->timestamps();

                $table->index(['model_id', 'effective_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_model_pricings');
        Schema::dropIfExists('ai_model_registries');
    }
};

