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
        // 1. Master Sumber Inspirasi Eksternal (TikTok, Instagram, Pinterest, Google Trends, Marketplace)
        if (!Schema::hasTable('inspiration_sources')) {
            Schema::create('inspiration_sources', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->string('code', 50)->unique(); // 'tiktok', 'instagram', 'pinterest', 'google_trends', 'shopee', 'tokopedia', 'manual'
                $table->string('name', 100);
                $table->string('driver_class', 255);
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('fetch_frequency_minutes')->default(60);
                $table->unsignedInteger('rate_limit_per_minute')->default(30);
                $table->boolean('supports_image')->default(true);
                $table->boolean('supports_video')->default(false);
                $table->json('config_schema')->nullable();
                $table->timestamp('last_synced_at')->nullable();
                $table->timestamps();

                $table->index('is_active');
            });
        }

        // 2. Kredensial & Autentikasi Sumber Inspirasi (Admin Managed, Encrypted)
        if (!Schema::hasTable('inspiration_source_credentials')) {
            Schema::create('inspiration_source_credentials', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('source_id')->constrained('inspiration_sources')->cascadeOnDelete();
                $table->string('credential_type', 50); // 'api_key', 'oauth2', 'session_cookie'
                $table->text('credentials_encrypted');
                $table->string('status', 30)->default('active'); // 'active', 'expired', 'revoked'
                $table->timestamp('expires_at')->nullable();
                $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['source_id', 'status']);
                $table->index('expires_at');
            });
        }

        // 3. Kebijakan Lisensi, Retensi & Disclaimer Legal Sumber
        if (!Schema::hasTable('inspiration_source_policies')) {
            Schema::create('inspiration_source_policies', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('source_id')->constrained('inspiration_sources')->cascadeOnDelete();
                $table->boolean('allow_commercial_use')->default(false);
                $table->boolean('require_attribution')->default(true);
                $table->unsignedInteger('retention_days')->default(90);
                $table->text('disclaimer_template')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index('allow_commercial_use');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspiration_source_policies');
        Schema::dropIfExists('inspiration_source_credentials');
        Schema::dropIfExists('inspiration_sources');
    }
};
