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
        Schema::table('community_event_registrations', function (Blueprint $table): void {
            $table->string('payment_method', 20)->default('qris')->after('payment_status');
            $table->string('payment_gateway', 20)->default('tripay')->after('payment_method');
            $table->string('gateway_reference')->nullable()->after('payment_gateway');
            $table->string('gateway_pay_code')->nullable()->after('gateway_reference');
            $table->string('gateway_qr_url', 1000)->nullable()->after('gateway_pay_code');
            $table->text('gateway_qr_string')->nullable()->after('gateway_qr_url');
            $table->decimal('gateway_fee', 12, 2)->default(0)->after('gateway_qr_string');
            $table->dateTime('gateway_expired_at')->nullable()->after('gateway_fee');
            $table->dateTime('paid_at')->nullable()->after('gateway_expired_at');
        });
    }

    public function down(): void
    {
        Schema::table('community_event_registrations', function (Blueprint $table): void {
            $table->dropColumn([
                'payment_method',
                'payment_gateway',
                'gateway_reference',
                'gateway_pay_code',
                'gateway_qr_url',
                'gateway_qr_string',
                'gateway_fee',
                'gateway_expired_at',
                'paid_at',
            ]);
        });
    }
};
