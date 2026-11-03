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
        Schema::table('attendances', function (Blueprint $table): void {
            if (! Schema::hasColumn('attendances', 'device_id')) {
                $table->string('device_id', 255)->nullable()->after('user_id');
            }
            if (! Schema::hasColumn('attendances', 'is_mock')) {
                $table->boolean('is_mock')->default(false)->after('is_geofenced');
            }
            if (! Schema::hasColumn('attendances', 'is_dev_mode')) {
                $table->boolean('is_dev_mode')->default(false)->after('is_mock');
            }
            if (! Schema::hasColumn('attendances', 'is_rooted')) {
                $table->boolean('is_rooted')->default(false)->after('is_dev_mode');
            }
            if (! Schema::hasColumn('attendances', 'accuracy')) {
                $table->decimal('accuracy', 8, 2)->nullable()->after('is_rooted');
            }
            if (! Schema::hasColumn('attendances', 'security_flags')) {
                $table->json('security_flags')->nullable()->after('accuracy');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table): void {
            $table->dropColumn([
                'device_id',
                'is_mock',
                'is_dev_mode',
                'is_rooted',
                'accuracy',
                'security_flags',
            ]);
        });
    }
};
