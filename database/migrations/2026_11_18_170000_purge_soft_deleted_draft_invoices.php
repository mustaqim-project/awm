<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('invoices')) {
            return;
        }

        // Hard delete all draft invoices that were previously soft deleted,
        // freeing their invoice numbers so unique constraints never collide.
        $trashedDraftIds = DB::table('invoices')
            ->whereNotNull('deleted_at')
            ->where('status', 'draft')
            ->pluck('id');

        if ($trashedDraftIds->isNotEmpty()) {
            if (Schema::hasTable('invoice_items')) {
                DB::table('invoice_items')->whereIn('invoice_id', $trashedDraftIds)->delete();
            }
            DB::table('invoices')->whereIn('id', $trashedDraftIds)->delete();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Irreversible cleanup of deleted draft invoices.
    }
};
