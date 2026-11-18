<?php

use App\Models\Business;
use App\Models\Unit;
use App\Models\User;
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
            Log::info('[Migration] Ensuring units exist for D0&GO...');

            // Pastikan unit dasar tersedia
            Unit::firstOrCreate(['code' => 'g'], [
                'name'              => 'Gram',
                'category'          => Unit::CATEGORY_WEIGHT,
                'is_base'           => true,
                'default_precision' => 2,
            ]);
            Unit::firstOrCreate(['code' => 'ml'], [
                'name'              => 'Mililiter',
                'category'          => Unit::CATEGORY_VOLUME,
                'is_base'           => true,
                'default_precision' => 2,
            ]);
            Unit::firstOrCreate(['code' => 'pcs'], [
                'name'              => 'Pieces',
                'category'          => Unit::CATEGORY_QUANTITY,
                'is_base'           => true,
                'default_precision' => 0,
            ]);
            Unit::firstOrCreate(['code' => 'cup'], [
                'name'              => 'Cup',
                'category'          => Unit::CATEGORY_QUANTITY,
                'is_base'           => false,
                'default_precision' => 0,
            ]);

            Log::info('[Migration] Triggering cooca:sync-dogo-data with forced clean sync...');
            Artisan::call('cooca:sync-dogo-data', [
                '--email' => 'srifennikapurba@gmail.com',
                '--force' => true,
            ]);
            Log::info('[Migration] cooca:sync-dogo-data completed. Output: ' . Artisan::output());
        } catch (\Throwable $e) {
            Log::error('[Migration] Failed in ensure_units_and_resync_dogo: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
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
