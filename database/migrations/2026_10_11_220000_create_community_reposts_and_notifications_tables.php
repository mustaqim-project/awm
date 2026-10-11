<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('community_reposts', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('post_id')->constrained('community_posts')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['post_id', 'user_id']);
            $table->index(['user_id', 'created_at']);
        });

        Schema::create('community_notifications', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('actor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('post_id')->constrained('community_posts')->cascadeOnDelete();
            $table->string('type', 32); // 'like', 'comment', 'repost'
            $table->string('message', 255)->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'is_read', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_notifications');
        Schema::dropIfExists('community_reposts');
    }
};
