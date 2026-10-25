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
        // 1. Platform Chart of Accounts (COA)
        Schema::create('platform_chart_of_accounts', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('code', 30)->unique();
            $table->string('name', 150);
            $table->string('type', 30); // asset, liability, equity, revenue, expense
            $table->string('normal_balance', 10)->default('debit'); // debit, credit
            $table->string('category_group', 50)->nullable(); // kas_bank, piutang, hutang_lancar, modal, pendapatan_usaha, beban_pokok, beban_operasional
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('type');
            $table->index('is_active');
        });

        // 2. Platform Journal Entries (Double-Entry Header)
        Schema::create('platform_journal_entries', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('entry_number', 64)->unique();
            $table->date('entry_date');
            $table->string('reference_type', 50)->nullable(); // subscription_payment, payment_settlement, platform_transaction, manual_journal
            $table->string('reference_id', 64)->nullable();
            $table->string('description', 255);
            $table->decimal('total_debit', 15, 2)->default(0.00);
            $table->decimal('total_credit', 15, 2)->default(0.00);
            $table->boolean('is_balanced')->default(true);
            $table->uuid('admin_id')->nullable()->index();
            $table->softDeletes();
            $table->timestamps();

            $table->index('entry_date');
            $table->index(['reference_type', 'reference_id'], 'pje_ref_idx');
        });

        // 3. Platform Journal Entry Lines (Debit / Credit Rows)
        Schema::create('platform_journal_entry_lines', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('journal_entry_id')->constrained('platform_journal_entries')->cascadeOnDelete();
            $table->foreignUuid('account_id')->constrained('platform_chart_of_accounts')->cascadeOnDelete();
            $table->string('type', 10); // debit, credit
            $table->decimal('amount', 15, 2);
            $table->string('notes', 255)->nullable();
            $table->timestamps();

            $table->index(['journal_entry_id', 'account_id'], 'pjel_entry_acc_idx');
            $table->index('type');
        });

        // 4. Link platform_transactions to journal_entry_id if exists
        if (Schema::hasTable('platform_transactions')) {
            Schema::table('platform_transactions', function (Blueprint $table): void {
                if (! Schema::hasColumn('platform_transactions', 'journal_entry_id')) {
                    $table->uuid('journal_entry_id')->nullable()->after('admin_id')->index();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('platform_transactions') && Schema::hasColumn('platform_transactions', 'journal_entry_id')) {
            Schema::table('platform_transactions', function (Blueprint $table): void {
                $table->dropColumn('journal_entry_id');
            });
        }

        Schema::dropIfExists('platform_journal_entry_lines');
        Schema::dropIfExists('platform_journal_entries');
        Schema::dropIfExists('platform_chart_of_accounts');
    }
};
