<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Drop partial table if failed previously
        Schema::dropIfExists('marketplace_messages');
        Schema::dropIfExists('marketplace_conversations');

        // 1. Marketplace Conversations (Threads per shop)
        Schema::create('marketplace_conversations', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('marketplace_account_id')->constrained('marketplace_accounts')->cascadeOnDelete();
            $table->string('channel', 50)->index(); // tiktok_shop, shopee, tokopedia
            $table->string('shop_id', 100)->index();
            $table->string('shop_cipher', 255)->nullable();
            $table->string('external_conversation_id', 150)->index();
            $table->string('participant_id', 150)->nullable(); // Buyer IM user ID
            $table->string('participant_name', 255)->default('Marketplace Buyer');
            $table->string('participant_avatar', 500)->nullable();
            $table->integer('unread_count')->default(0);
            $table->text('last_message_content')->nullable();
            $table->string('last_message_type', 50)->default('TEXT');
            $table->timestamp('last_message_at')->nullable()->index();
            $table->string('status', 50)->default('open'); // open, resolved, pending
            $table->json('raw_metadata')->nullable();
            $table->timestamps();

            // Explicit short index name to avoid MySQL 64-char limit
            $table->unique(['channel', 'external_conversation_id'], 'mkt_conv_channel_ext_id_uniq');
        });

        // 2. Marketplace Messages (Individual chat items with idempotency guarantee)
        Schema::create('marketplace_messages', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('marketplace_conversation_id')->constrained('marketplace_conversations')->cascadeOnDelete();
            $table->string('external_message_id', 150)->unique('mkt_msg_ext_id_uniq'); // Primary idempotency key
            $table->string('sender_role', 50)->index(); // BUYER, SELLER, SYSTEM, AGENT
            $table->string('sender_user_id', 150)->nullable();
            $table->string('message_type', 50)->default('TEXT'); // TEXT, IMAGE, PRODUCT_CARD, ORDER_CARD
            $table->longText('raw_content')->nullable(); // Original JSON/string
            $table->text('content_text')->nullable(); // Extracted readable message
            $table->string('media_url', 1000)->nullable(); // Media URL if IMAGE
            $table->boolean('is_read_by_agent')->default(false);
            $table->boolean('is_synced_to_marketplace')->default(true);
            $table->timestamp('marketplace_created_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_messages');
        Schema::dropIfExists('marketplace_conversations');
    }
};
