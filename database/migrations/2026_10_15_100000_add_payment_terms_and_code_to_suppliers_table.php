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
        Schema::table('suppliers', function (Blueprint $table): void {
            if (! Schema::hasColumn('suppliers', 'code')) {
                $table->string('code', 50)->nullable()->after('slug');
            }
            if (! Schema::hasColumn('suppliers', 'payment_terms_days')) {
                $table->unsignedInteger('payment_terms_days')->default(0)->after('address');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table): void {
            if (Schema::hasColumn('suppliers', 'code')) {
                $table->dropColumn('code');
            }
            if (Schema::hasColumn('suppliers', 'payment_terms_days')) {
                $table->dropColumn('payment_terms_days');
            }
        });
    }
};
