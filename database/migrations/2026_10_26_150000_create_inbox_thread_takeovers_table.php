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
        if (! Schema::hasTable('inbox_thread_takeovers')) {
            Schema::create('inbox_thread_takeovers', function (Blueprint $table): void {
                $table->id();
                $table->string('thread_key', 255)->unique()->index();
                $table->string('channel', 50)->index();
                $table->string('customer_id', 255)->index();
                $table->boolean('is_human_takeover')->default(false)->index();
                $table->string('takeover_by_id', 64)->nullable()->index();
                $table->string('takeover_by_name', 150)->nullable();
                $table->timestamp('takeover_at')->nullable();
                $table->timestamp('released_at')->nullable();
                $table->timestamp('last_ai_reply_at')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('social_media_comments')) {
            Schema::table('social_media_comments', function (Blueprint $table): void {
                if (! Schema::hasColumn('social_media_comments', 'is_human_takeover')) {
                    $table->boolean('is_human_takeover')->default(false)->after('status')->index();
                }
                if (! Schema::hasColumn('social_media_comments', 'takeover_by_id')) {
                    $table->string('takeover_by_id', 64)->nullable()->after('is_human_takeover');
                }
                if (! Schema::hasColumn('social_media_comments', 'takeover_at')) {
                    $table->timestamp('takeover_at')->nullable()->after('takeover_by_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('inbox_thread_takeovers')) {
            Schema::dropIfExists('inbox_thread_takeovers');
        }

        if (Schema::hasTable('social_media_comments')) {
            Schema::table('social_media_comments', function (Blueprint $table): void {
                $columns = [];
                if (Schema::hasColumn('social_media_comments', 'is_human_takeover')) {
                    $columns[] = 'is_human_takeover';
                }
                if (Schema::hasColumn('social_media_comments', 'takeover_by_id')) {
                    $columns[] = 'takeover_by_id';
                }
                if (Schema::hasColumn('social_media_comments', 'takeover_at')) {
                    $columns[] = 'takeover_at';
                }
                if (! empty($columns)) {
                    $table->dropColumn($columns);
                }
            });
        }
    }
};
