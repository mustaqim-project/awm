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
        Schema::table('businesses', function (Blueprint $table): void {
            if (! Schema::hasColumn('businesses', 'tax_entity_type')) {
                $table->string('tax_entity_type', 30)
                    ->default('individual')
                    ->after('tax_identification_number')
                    ->comment('Jenis entitas pajak: individual (Usaha Perorangan), pt_individual (PT Perorangan), corporate (PT Badan/CV)');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table): void {
            if (Schema::hasColumn('businesses', 'tax_entity_type')) {
                $table->dropColumn('tax_entity_type');
            }
        });
    }
};
