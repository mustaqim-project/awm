<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menambahkan composite index pada journal_entry_lines untuk optimasi query Buku Besar (General Ledger).
     */
    public function up(): void
    {
        if (Schema::hasTable('journal_entry_lines')) {
            Schema::table('journal_entry_lines', function (Blueprint $table): void {
                $table->index(['account_id', 'created_at'], 'jel_account_created_idx');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('journal_entry_lines')) {
            Schema::table('journal_entry_lines', function (Blueprint $table): void {
                $table->dropIndex('jel_account_created_idx');
            });
        }
    }
};
