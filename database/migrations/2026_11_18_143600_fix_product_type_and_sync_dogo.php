<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            Log::info('[Migration] Updating products type to goods...');
            DB::table('products')
                ->where('type', 'finished_goods')
                ->update(['type' => 'goods']);

            // Re-run sync with type=goods
            Artisan::call('cooca:sync-dogo-data', [
                '--email' => 'srifennikapurba@gmail.com',
                '--force' => true,
            ]);

            Log::info('[Migration] Products type updated to goods and re-synced successfully.');
        } catch (\Throwable $e) {
            Log::error('[Migration] Failed updating product type: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op
    }
};
