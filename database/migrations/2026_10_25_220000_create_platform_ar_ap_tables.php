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
        // 1. Platform Accounts Receivable (Piutang Usaha & Tagihan Tenant)
        Schema::create('platform_receivables', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('invoice_number', 64)->unique();
            $table->string('client_name', 150);
            $table->string('client_email', 150)->nullable();
            $table->string('client_phone', 50)->nullable();
            $table->foreignUuid('business_id')->nullable()->constrained('businesses')->nullOnDelete();
            $table->date('invoice_date');
            $table->date('due_date');
            $table->decimal('subtotal', 15, 2)->default(0.00);
            $table->decimal('tax_amount', 15, 2)->default(0.00);
            $table->decimal('total_amount', 15, 2);
            $table->decimal('paid_amount', 15, 2)->default(0.00);
            $table->decimal('balance_due', 15, 2);
            $table->string('status', 30)->default('unpaid'); // unpaid, partially_paid, paid, overdue, cancelled
            $table->string('category', 50)->default('subscription'); // subscription, custom_setup, enterprise_annual, consulting, other
            $table->string('title', 255);
            $table->text('notes')->nullable();
            $table->string('attachment_path')->nullable(); // Contract / PO document
            $table->uuid('journal_entry_id')->nullable()->index();
            $table->uuid('admin_id')->nullable()->index();
            $table->softDeletes();
            $table->timestamps();

            $table->index('status');
            $table->index('due_date');
            $table->index('invoice_date');
        });

        // 2. Platform AR Payments (Penerimaan Pembayaran Piutang)
        Schema::create('platform_receivable_payments', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('receivable_id')->constrained('platform_receivables')->cascadeOnDelete();
            $table->string('payment_number', 64)->unique();
            $table->date('payment_date');
            $table->decimal('amount', 15, 2);
            $table->string('payment_method', 50)->default('bank_transfer');
            $table->foreignUuid('account_id')->nullable()->constrained('platform_chart_of_accounts')->nullOnDelete();
            $table->string('attachment_path')->nullable(); // Bukti transfer / slip setoran
            $table->string('reference_number', 100)->nullable();
            $table->text('notes')->nullable();
            $table->uuid('journal_entry_id')->nullable()->index();
            $table->uuid('admin_id')->nullable()->index();
            $table->timestamps();

            $table->index('payment_date');
        });

        // 3. Platform Accounts Payable (Hutang Usaha & Tagihan Vendor)
        Schema::create('platform_payables', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('bill_number', 64)->unique();
            $table->string('vendor_name', 150);
            $table->string('vendor_category', 50)->default('infrastructure'); // infrastructure, ai_api, whatsapp_gateway, office_rent, legal_tax, salary, marketing, other
            $table->string('reference_number', 100)->nullable(); // Vendor's original invoice number
            $table->date('bill_date');
            $table->date('due_date');
            $table->decimal('subtotal', 15, 2)->default(0.00);
            $table->decimal('tax_amount', 15, 2)->default(0.00);
            $table->decimal('total_amount', 15, 2);
            $table->decimal('paid_amount', 15, 2)->default(0.00);
            $table->decimal('balance_due', 15, 2);
            $table->string('status', 30)->default('unpaid'); // unpaid, partially_paid, paid, overdue, cancelled
            $table->string('title', 255);
            $table->foreignUuid('expense_account_id')->nullable()->constrained('platform_chart_of_accounts')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->string('attachment_path')->nullable(); // Original vendor bill PDF / image
            $table->uuid('journal_entry_id')->nullable()->index();
            $table->uuid('admin_id')->nullable()->index();
            $table->softDeletes();
            $table->timestamps();

            $table->index('status');
            $table->index('due_date');
            $table->index('vendor_category');
        });

        // 4. Platform AP Payments (Pelunasan Pembayaran Tagihan Vendor)
        Schema::create('platform_payable_payments', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('payable_id')->constrained('platform_payables')->cascadeOnDelete();
            $table->string('payment_number', 64)->unique();
            $table->date('payment_date');
            $table->decimal('amount', 15, 2);
            $table->string('payment_method', 50)->default('bank_transfer');
            $table->foreignUuid('account_id')->nullable()->constrained('platform_chart_of_accounts')->nullOnDelete();
            $table->string('attachment_path')->nullable(); // Bukti transfer bank kuitansi keluar
            $table->string('reference_number', 100)->nullable();
            $table->text('notes')->nullable();
            $table->uuid('journal_entry_id')->nullable()->index();
            $table->uuid('admin_id')->nullable()->index();
            $table->timestamps();

            $table->index('payment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('platform_payable_payments');
        Schema::dropIfExists('platform_payables');
        Schema::dropIfExists('platform_receivable_payments');
        Schema::dropIfExists('platform_receivables');
    }
};
