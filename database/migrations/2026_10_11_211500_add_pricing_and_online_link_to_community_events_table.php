<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('community_events', function (Blueprint $table): void {
            $table->string('price_type', 20)->default('free')->after('event_type'); // free | paid
            $table->decimal('price', 14, 2)->default(0)->after('price_type');
            $table->string('online_meeting_url')->nullable()->after('location_address');
        });

        Schema::table('community_event_registrations', function (Blueprint $table): void {
            $table->decimal('amount_paid', 14, 2)->default(0)->after('ticket_code');
            $table->string('payment_status', 20)->default('paid')->after('amount_paid'); // paid | pending
        });
    }

    public function down(): void
    {
        Schema::table('community_event_registrations', function (Blueprint $table): void {
            $table->dropColumn(['amount_paid', 'payment_status']);
        });

        Schema::table('community_events', function (Blueprint $table): void {
            $table->dropColumn(['price_type', 'price', 'online_meeting_url']);
        });
    }
};
