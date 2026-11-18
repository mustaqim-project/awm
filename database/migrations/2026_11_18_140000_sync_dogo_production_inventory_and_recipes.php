<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            Log::info('[Migration] Running SyncDoGoProductionDataCommand on production...');
            $exitCode = Artisan::call('cooca:sync-dogo-data', [
                '--email' => 'srifennikapurba@gmail.com',
                '--force' => true,
            ]);
            $output = Artisan::output();
            Log::info('[Migration] SyncDoGoProductionDataCommand completed with exit code: ' . $exitCode . "\nOutput:\n" . $output);
        } catch (\Throwable $e) {
            Log::error('[Migration] SyncDoGoProductionDataCommand failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op for data sync migration
    }
};
