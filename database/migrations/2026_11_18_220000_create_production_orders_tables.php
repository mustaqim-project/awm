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
        if (! Schema::hasTable('production_orders')) {
            Schema::create('production_orders', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
                $table->string('order_number', 50);
                $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
                $table->foreignUuid('bom_header_id')->nullable()->constrained('bom_headers')->nullOnDelete();
                $table->foreignUuid('source_location_id')->constrained('locations')->cascadeOnDelete();
                $table->foreignUuid('target_location_id')->constrained('locations')->cascadeOnDelete();
                $table->decimal('planned_quantity', 15, 4);
                $table->decimal('actual_quantity', 15, 4)->default(0);
                $table->decimal('rejected_quantity', 15, 4)->default(0);
                $table->string('status', 30)->default('draft');
                $table->date('planned_start_date');
                $table->timestamp('actual_end_date')->nullable();
                $table->decimal('unit_cost_hpp', 15, 4)->default(0);
                $table->decimal('total_material_cost', 15, 4)->default(0);
                $table->decimal('total_labor_cost', 15, 4)->default(0);
                $table->decimal('total_machine_cost', 15, 4)->default(0);
                $table->decimal('total_production_cost', 15, 4)->default(0);
                $table->string('batch_number', 100)->nullable();
                $table->date('expiry_date')->nullable();
                $table->text('notes')->nullable();
                $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignUuid('completed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['business_id', 'status']);
                $table->index(['business_id', 'order_number']);
                $table->index('product_id');
                $table->index('bom_header_id');
            });
        }

        if (! Schema::hasTable('production_order_items')) {
            Schema::create('production_order_items', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('production_order_id')->constrained('production_orders')->cascadeOnDelete();
                $table->foreignUuid('material_id')->constrained('materials')->cascadeOnDelete();
                $table->foreignUuid('unit_id')->nullable()->constrained('units')->nullOnDelete();
                $table->decimal('planned_quantity', 15, 4);
                $table->decimal('actual_consumed_quantity', 15, 4)->default(0);
                $table->decimal('waste_quantity', 15, 4)->default(0);
                $table->decimal('unit_cost', 15, 4)->default(0);
                $table->decimal('total_cost', 15, 4)->default(0);
                $table->string('batch_number', 100)->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['production_order_id', 'material_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_order_items');
        Schema::dropIfExists('production_orders');
    }
};
