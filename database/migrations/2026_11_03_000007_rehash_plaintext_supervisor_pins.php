<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Migrate any legacy or unhashed plaintext supervisor PINs into standard Bcrypt hashes.
     */
    public function up(): void
    {
        if (! Schema::hasTable('businesses') || ! Schema::hasColumn('businesses', 'pos_supervisor_pin')) {
            return;
        }

        $businesses = DB::table('businesses')
            ->whereNotNull('pos_supervisor_pin')
            ->where('pos_supervisor_pin', '!=', '')
            ->get(['id', 'pos_supervisor_pin']);

        foreach ($businesses as $business) {
            $pin = (string) $business->pos_supervisor_pin;
            $info = password_get_info($pin);
            $isBcrypt = ($info['algoName'] ?? '') === 'bcrypt'
                || str_starts_with($pin, '$2y$')
                || str_starts_with($pin, '$2a$')
                || str_starts_with($pin, '$2b$');

            if (! $isBcrypt) {
                DB::table('businesses')
                    ->where('id', $business->id)
                    ->update([
                        'pos_supervisor_pin' => Hash::make($pin),
                    ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // One-way migration: Bcrypt hashes cannot be converted back to plaintext.
    }
};
