<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commerce_reservations', function (Blueprint $table): void {
            $table->foreignUuid('location_id')
                ->nullable()
                ->after('business_id')
                ->constrained('locations')
                ->nullOnDelete();

            $table->foreignUuid('staff_id')
                ->nullable()
                ->after('product_id')
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignUuid('created_by')
                ->nullable()
                ->after('staff_id')
                ->constrained('users')
                ->nullOnDelete();

            $table->string('vehicle_plate', 30)->nullable()->after('guest_count');
            $table->string('vehicle_model', 100)->nullable()->after('vehicle_plate');
            $table->unsignedInteger('service_duration_minutes')->nullable()->after('vehicle_model');
            $table->string('event_venue', 255)->nullable()->after('service_duration_minutes');

            $table->decimal('estimated_total', 15, 2)->default(0)->after('event_venue');
            $table->decimal('dp_amount', 15, 2)->default(0)->after('estimated_total');
            $table->string('dp_payment_method', 50)->nullable()->after('dp_amount');
            $table->timestamp('dp_paid_at')->nullable()->after('dp_payment_method');
            $table->string('dp_payment_reference', 100)->nullable()->after('dp_paid_at');
            $table->string('payment_status', 32)->default('unpaid')->after('dp_payment_reference');

            $table->string('source', 32)->default('online')->after('status');

            $table->index(['business_id', 'payment_status'], 'cr_biz_payment_idx');
            $table->index(['business_id', 'location_id'], 'cr_biz_loc_idx');
        });
    }

    public function down(): void
    {
        Schema::table('commerce_reservations', function (Blueprint $table): void {
            $table->dropIndex('cr_biz_payment_idx');
            $table->dropIndex('cr_biz_loc_idx');

            $table->dropForeign(['location_id']);
            $table->dropForeign(['staff_id']);
            $table->dropForeign(['created_by']);

            $table->dropColumn([
                'location_id',
                'staff_id',
                'created_by',
                'vehicle_plate',
                'vehicle_model',
                'service_duration_minutes',
                'event_venue',
                'estimated_total',
                'dp_amount',
                'dp_payment_method',
                'dp_paid_at',
                'dp_payment_reference',
                'payment_status',
                'source',
            ]);
        });
    }
};
