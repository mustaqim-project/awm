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
        Schema::create('platform_transactions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('transaction_number')->unique();
            $table->enum('type', ['in', 'out'])->index(); // 'in' = Kas Masuk / Inflow, 'out' = Kas Keluar / Outflow
            $table->string('category')->index();
            $table->string('title');
            $table->decimal('amount', 15, 2);
            $table->date('transaction_date')->index();
            $table->string('payment_method')->nullable();
            $table->text('notes')->nullable();
            $table->string('attachment_path')->nullable(); // Optional receipt / invoice image path
            $table->string('source_type')->nullable()->index(); // subscription_payment, payment_settlement, manual
            $table->uuid('source_id')->nullable()->index();
            $table->uuid('admin_id')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('platform_transactions');
    }
};
