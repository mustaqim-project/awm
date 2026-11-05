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
        Schema::create('ai_chatbot_conversations', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 30)->default('help_center'); // help_center, business_assistant
            $table->string('title', 150)->nullable();
            $table->timestamps();

            $table->index(['business_id', 'user_id', 'type']);
        });

        Schema::create('ai_chatbot_messages', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('conversation_id')->constrained('ai_chatbot_conversations')->cascadeOnDelete();
            $table->string('sender', 20); // user, assistant, system
            $table->longText('content');
            $table->string('intent', 60)->nullable();
            $table->boolean('is_data_query')->default(false);
            $table->boolean('permission_granted')->default(true);
            $table->string('status', 30)->default('completed'); // pending, processing, completed, failed
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['conversation_id', 'created_at']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_chatbot_messages');
        Schema::dropIfExists('ai_chatbot_conversations');
    }
};

