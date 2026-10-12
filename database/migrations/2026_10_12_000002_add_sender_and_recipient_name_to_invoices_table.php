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
            if (!Schema::hasColumn('invoices', 'sender_name')) {
                $table->string('sender_name', 150)->nullable()->after('invoice_number');
            }
            if (!Schema::hasColumn('invoices', 'recipient_name')) {
                $table->string('recipient_name', 150)->nullable()->after('customer_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            if (Schema::hasColumn('invoices', 'recipient_name')) {
                $table->dropColumn('recipient_name');
            }
            if (Schema::hasColumn('invoices', 'sender_name')) {
                $table->dropColumn('sender_name');
            }
        });
    }
};
