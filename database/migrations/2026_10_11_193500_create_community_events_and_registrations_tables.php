<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('community_events', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('banner_image')->nullable();
            $table->string('event_type', 20)->default('offline'); // offline | online
            $table->string('location_name');
            $table->text('location_address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->unsignedInteger('checkin_radius_meters')->default(200);
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->dateTime('registration_deadline');
            $table->unsignedInteger('quota')->nullable();
            $table->string('checkin_code', 64)->unique();
            $table->string('status', 20)->default('published'); // draft | published | cancelled | completed
            $table->foreignUuid('created_by_admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'start_time']);
            $table->index(['status', 'end_time']);
            $table->index(['registration_deadline']);
        });

        Schema::create('community_event_registrations', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('event_id')->constrained('community_events')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->string('ticket_code', 32)->unique();
            $table->dateTime('registered_at');
            $table->string('status', 20)->default('registered'); // registered | attended | cancelled
            $table->dateTime('checked_in_at')->nullable();
            $table->string('checkin_method', 20)->nullable(); // location | barcode | manual
            $table->decimal('checkin_latitude', 10, 8)->nullable();
            $table->decimal('checkin_longitude', 11, 8)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['event_id', 'user_id']);
            $table->index(['ticket_code']);
            $table->index(['event_id', 'status']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_event_registrations');
        Schema::dropIfExists('community_events');
    }
};
