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
        Schema::create('google_calendar_accounts', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('google_id')->index();
            $table->string('email');
            $table->string('name')->nullable();
            $table->string('avatar_url')->nullable();
            $table->text('access_token');
            $table->text('refresh_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->string('calendar_id')->default('primary');
            $table->string('calendar_name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('auto_sync')->default(true);
            $table->string('sync_token')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'is_active']);
        });

        Schema::create('calendar_events', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignUuid('task_id')->nullable()->constrained('tasks')->nullOnDelete();
            $table->foreignUuid('google_calendar_account_id')->nullable()->constrained('google_calendar_accounts')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->string('meeting_link')->nullable();
            $table->dateTime('start_time')->index();
            $table->dateTime('end_time')->index();
            $table->boolean('is_all_day')->default(false);
            $table->string('color_tag', 20)->default('#007AFF');
            $table->string('status', 30)->default('scheduled')->index(); // scheduled, in_progress, completed, cancelled
            $table->string('google_event_id')->nullable()->index();
            $table->string('google_calendar_id')->nullable();
            $table->string('google_etag')->nullable();
            $table->string('google_html_link')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->string('sync_status', 30)->default('none'); // none, synced, pending_sync, sync_error
            $table->text('sync_error_message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['business_id', 'start_time', 'end_time']);
            $table->index(['business_id', 'project_id']);
        });

        Schema::create('event_reminders', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('calendar_event_id')->constrained('calendar_events')->cascadeOnDelete();
            $table->string('channel', 30)->default('in_app'); // whatsapp, email, in_app
            $table->unsignedInteger('minutes_before')->default(15);
            $table->dateTime('remind_at')->index();
            $table->string('recipient_phone', 50)->nullable();
            $table->string('recipient_email', 150)->nullable();
            $table->foreignUuid('recipient_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 30)->default('pending')->index(); // pending, sent, failed
            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['status', 'remind_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_reminders');
        Schema::dropIfExists('calendar_events');
        Schema::dropIfExists('google_calendar_accounts');
    }
};
