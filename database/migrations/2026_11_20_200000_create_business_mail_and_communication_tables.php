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
        // 1. Business SMTP & Inbound IMAP Accounts (1 Master for all, or up to 3 Department Overrides)
        Schema::create('business_smtp_accounts', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->string('channel_type', 30)->default('default'); // default, quotation, invoice, purchase_order
            $table->boolean('is_active')->default(true);
            $table->string('mail_driver', 20)->default('smtp');
            $table->string('mail_host');
            $table->unsignedInteger('mail_port')->default(587);
            $table->string('mail_encryption', 10)->nullable()->default('tls'); // tls, ssl, null
            $table->string('mail_username');
            $table->text('mail_password'); // encrypted
            $table->string('from_name');
            $table->string('from_address');
            $table->string('reply_to_address')->nullable();
            
            // Inbound Reading (IMAP)
            $table->boolean('enable_inbound_reading')->default(false);
            $table->string('imap_host')->nullable();
            $table->unsignedInteger('imap_port')->default(993);
            $table->string('imap_encryption', 10)->nullable()->default('ssl');
            $table->string('imap_username')->nullable();
            $table->text('imap_password')->nullable(); // encrypted
            $table->timestamp('last_inbound_check_at')->nullable();
            
            // Health & Diagnosis
            $table->string('last_status', 20)->nullable()->default('untested'); // untested, connected, error
            $table->text('last_error_message')->nullable();
            $table->timestamp('last_tested_at')->nullable();

            $table->timestamps();

            $table->unique(['business_id', 'channel_type']);
            $table->index(['business_id', 'is_active']);
            $table->index(['enable_inbound_reading', 'is_active']);
        });

        // 2. Business Email Templates (Editable per Business)
        Schema::create('business_email_templates', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->string('template_key', 50); // quotation_sent, invoice_sent, invoice_reminder_upcoming, invoice_reminder_due, invoice_reminder_overdue, purchase_order_sent
            $table->string('subject');
            $table->longText('body_html');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['business_id', 'template_key']);
        });

        // 3. Business Auto Reminder Settings for Invoices
        Schema::create('business_reminder_settings', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->boolean('auto_remind_invoices')->default(true);
            $table->json('channels')->nullable(); // ['email', 'whatsapp']
            $table->boolean('remind_h_minus_3')->default(true);
            $table->boolean('remind_due_date')->default(true);
            $table->boolean('remind_h_plus_3')->default(true);
            $table->boolean('remind_h_plus_7')->default(true);
            $table->boolean('remind_weekly_overdue')->default(true);
            $table->unsignedTinyInteger('preferred_send_hour')->default(9); // 09:00 WIB
            $table->timestamps();

            $table->unique('business_id');
        });

        // 4. Inbound Received Emails (Captured client replies & PO submissions)
        Schema::create('inbound_emails', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('business_smtp_account_id')->nullable()->constrained('business_smtp_accounts')->nullOnDelete();
            $table->string('message_id')->nullable()->index();
            $table->string('from_email');
            $table->string('from_name')->nullable();
            $table->string('to_email')->nullable();
            $table->string('subject');
            $table->text('body_text')->nullable();
            $table->longText('body_html')->nullable();
            $table->string('related_type', 30)->nullable(); // quotation, invoice, purchase_order
            $table->uuid('related_id')->nullable();
            $table->boolean('has_attachments')->default(false);
            $table->json('attachments_info')->nullable();
            $table->string('processing_status', 30)->default('received'); // received, matched, action_taken, ignored
            $table->timestamp('received_at')->index();
            $table->timestamps();

            $table->index(['business_id', 'related_type', 'related_id']);
            $table->index(['business_id', 'processing_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inbound_emails');
        Schema::dropIfExists('business_reminder_settings');
        Schema::dropIfExists('business_email_templates');
        Schema::dropIfExists('business_smtp_accounts');
    }
};
