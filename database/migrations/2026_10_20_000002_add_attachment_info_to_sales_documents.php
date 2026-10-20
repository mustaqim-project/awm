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
        Schema::table('invoices', function (Blueprint $table): void {
            if (!Schema::hasColumn('invoices', 'attachment_info')) {
                $table->string('attachment_info', 150)->nullable()->default('-')->after('recipient_name');
            }
        });

        Schema::table('sales_orders', function (Blueprint $table): void {
            if (!Schema::hasColumn('sales_orders', 'attachment_info')) {
                $table->string('attachment_info', 150)->nullable()->default('-')->after('recipient_name');
            }
        });

        Schema::table('quotations', function (Blueprint $table): void {
            if (!Schema::hasColumn('quotations', 'attachment_info')) {
                $table->string('attachment_info', 150)->nullable()->default('-')->after('recipient_name');
            }
        });

        Schema::table('sales_returns', function (Blueprint $table): void {
            if (!Schema::hasColumn('sales_returns', 'attachment_info')) {
                $table->string('attachment_info', 150)->nullable()->default('-')->after('reason');
            }
            if (!Schema::hasColumn('sales_returns', 'sender_name')) {
                $table->string('sender_name', 150)->nullable()->after('attachment_info');
            }
            if (!Schema::hasColumn('sales_returns', 'recipient_name')) {
                $table->string('recipient_name', 150)->nullable()->after('sender_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_returns', function (Blueprint $table): void {
            if (Schema::hasColumn('sales_returns', 'recipient_name')) {
                $table->dropColumn('recipient_name');
            }
            if (Schema::hasColumn('sales_returns', 'sender_name')) {
                $table->dropColumn('sender_name');
            }
            if (Schema::hasColumn('sales_returns', 'attachment_info')) {
                $table->dropColumn('attachment_info');
            }
        });

        Schema::table('quotations', function (Blueprint $table): void {
            if (Schema::hasColumn('quotations', 'attachment_info')) {
                $table->dropColumn('attachment_info');
            }
        });

        Schema::table('sales_orders', function (Blueprint $table): void {
            if (Schema::hasColumn('sales_orders', 'attachment_info')) {
                $table->dropColumn('attachment_info');
            }
        });

        Schema::table('invoices', function (Blueprint $table): void {
            if (Schema::hasColumn('invoices', 'attachment_info')) {
                $table->dropColumn('attachment_info');
            }
        });
    }
};
