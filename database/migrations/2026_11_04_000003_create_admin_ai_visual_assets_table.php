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
        Schema::create('admin_ai_visual_assets', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('title')->default('Visual Promosi Platform');
            $table->text('prompt');
            $table->text('negative_prompt')->nullable();
            $table->string('model_name', 64)->default('gpt-image-2.5-flare');
            $table->string('aspect_ratio', 16)->default('1:1');
            $table->string('quality', 32)->default('standard');
            $table->string('file_path', 500);
            $table->string('image_url', 1000);
            $table->unsignedInteger('width')->default(1024);
            $table->unsignedInteger('height')->default(1024);
            $table->unsignedBigInteger('file_size')->default(0);
            $table->json('metadata')->nullable();
            $table->boolean('is_favorite')->default(false);
            $table->timestamps();

            $table->index(['admin_id', 'created_at']);
            $table->index('is_favorite');
            $table->index('aspect_ratio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_ai_visual_assets');
    }
};
