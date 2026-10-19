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
        Schema::table('sales_orders', function (Blueprint $table): void {
            if (!Schema::hasColumn('sales_orders', 'sender_name')) {
                $table->string('sender_name', 150)->nullable()->after('so_number');
            }
            if (!Schema::hasColumn('sales_orders', 'recipient_name')) {
                $table->string('recipient_name', 150)->nullable()->after('customer_id');
            }
            if (!Schema::hasColumn('sales_orders', 'created_by')) {
                $table->foreignUuid('created_by')->nullable()->after('notes')->constrained('users')->nullOnDelete();
            }
        });

        Schema::table('quotations', function (Blueprint $table): void {
            if (!Schema::hasColumn('quotations', 'sender_name')) {
                $table->string('sender_name', 150)->nullable()->after('quotation_number');
            }
            if (!Schema::hasColumn('quotations', 'recipient_name')) {
                $table->string('recipient_name', 150)->nullable()->after('customer_id');
            }
            if (!Schema::hasColumn('quotations', 'created_by')) {
                $table->foreignUuid('created_by')->nullable()->after('terms_and_conditions')->constrained('users')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table): void {
            if (Schema::hasColumn('quotations', 'created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }
            if (Schema::hasColumn('quotations', 'recipient_name')) {
                $table->dropColumn('recipient_name');
            }
            if (Schema::hasColumn('quotations', 'sender_name')) {
                $table->dropColumn('sender_name');
            }
        });

        Schema::table('sales_orders', function (Blueprint $table): void {
            if (Schema::hasColumn('sales_orders', 'created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }
            if (Schema::hasColumn('sales_orders', 'recipient_name')) {
                $table->dropColumn('recipient_name');
            }
            if (Schema::hasColumn('sales_orders', 'sender_name')) {
                $table->dropColumn('sender_name');
            }
        });
    }
};
