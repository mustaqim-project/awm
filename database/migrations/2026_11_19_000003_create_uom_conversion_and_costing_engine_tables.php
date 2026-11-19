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
        // 1. purchase_order_items: Add conversion and base unit attributes
        Schema::table('purchase_order_items', function (Blueprint $table) {
            if (! Schema::hasColumn('purchase_order_items', 'base_unit_id')) {
                $table->char('base_unit_id', 36)->nullable()->after('unit_id');
            }
            if (! Schema::hasColumn('purchase_order_items', 'conversion_factor')) {
                $table->decimal('conversion_factor', 18, 8)->default(1.0)->after('base_unit_id');
            }
            if (! Schema::hasColumn('purchase_order_items', 'base_quantity')) {
                $table->decimal('base_quantity', 15, 4)->default(0.0)->after('conversion_factor');
            }
            if (! Schema::hasColumn('purchase_order_items', 'estimated_base_cost')) {
                $table->decimal('estimated_base_cost', 15, 4)->default(0.0)->after('base_quantity');
            }
        });

        // 2. goods_receipts: Add landed cost fields
        Schema::table('goods_receipts', function (Blueprint $table) {
            if (! Schema::hasColumn('goods_receipts', 'landed_cost_amount')) {
                $table->decimal('landed_cost_amount', 15, 2)->default(0.0)->after('status');
            }
            if (! Schema::hasColumn('goods_receipts', 'landed_cost_allocation_method')) {
                $table->string('landed_cost_allocation_method', 30)->default('value')->after('landed_cost_amount');
            }
            if (! Schema::hasColumn('goods_receipts', 'landed_cost_details')) {
                $table->json('landed_cost_details')->nullable()->after('landed_cost_allocation_method');
            }
        });

        // 3. goods_receipt_items: Add landed cost and conversion tracking
        Schema::table('goods_receipt_items', function (Blueprint $table) {
            if (! Schema::hasColumn('goods_receipt_items', 'purchase_order_item_id')) {
                $table->char('purchase_order_item_id', 36)->nullable()->after('goods_receipt_id');
            }
            if (! Schema::hasColumn('goods_receipt_items', 'received_unit_id')) {
                $table->char('received_unit_id', 36)->nullable()->after('quantity');
            }
            if (! Schema::hasColumn('goods_receipt_items', 'conversion_factor')) {
                $table->decimal('conversion_factor', 18, 8)->default(1.0)->after('received_unit_id');
            }
            if (! Schema::hasColumn('goods_receipt_items', 'base_quantity')) {
                $table->decimal('base_quantity', 15, 4)->default(0.0)->after('conversion_factor');
            }
            if (! Schema::hasColumn('goods_receipt_items', 'allocated_landed_cost')) {
                $table->decimal('allocated_landed_cost', 15, 2)->default(0.0)->after('unit_cost');
            }
            if (! Schema::hasColumn('goods_receipt_items', 'effective_base_cost')) {
                $table->decimal('effective_base_cost', 15, 4)->default(0.0)->after('allocated_landed_cost');
            }
        });

        // 4. stock_movements: Add cost before/after and balance before audit trail
        Schema::table('stock_movements', function (Blueprint $table) {
            if (! Schema::hasColumn('stock_movements', 'balance_before')) {
                $table->decimal('balance_before', 15, 4)->nullable()->after('quantity_change');
            }
            if (! Schema::hasColumn('stock_movements', 'cost_before')) {
                $table->decimal('cost_before', 15, 4)->nullable()->after('unit_cost');
            }
            if (! Schema::hasColumn('stock_movements', 'cost_after')) {
                $table->decimal('cost_after', 15, 4)->nullable()->after('cost_before');
            }
            if (! Schema::hasColumn('stock_movements', 'purchase_unit_id')) {
                $table->char('purchase_unit_id', 36)->nullable()->after('total_cost');
            }
            if (! Schema::hasColumn('stock_movements', 'purchase_quantity')) {
                $table->decimal('purchase_quantity', 15, 4)->nullable()->after('purchase_unit_id');
            }
        });

        // 5. product_unit_conversions: Parity with material_unit_conversions for trading products
        if (! Schema::hasTable('product_unit_conversions')) {
            Schema::create('product_unit_conversions', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->char('business_id', 36)->nullable()->index();
                $table->char('product_id', 36)->index();
                $table->char('from_unit_id', 36)->index();
                $table->char('to_unit_id', 36)->index();
                $table->decimal('factor', 18, 8);
                $table->boolean('is_default')->default(false);
                $table->timestamps();

                $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
                $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
                $table->foreign('from_unit_id')->references('id')->on('units')->onDelete('cascade');
                $table->foreign('to_unit_id')->references('id')->on('units')->onDelete('cascade');
            });
        }

        // 6. periodic_inventory_closings: Track monthly average cost closings
        if (! Schema::hasTable('periodic_inventory_closings')) {
            Schema::create('periodic_inventory_closings', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->char('business_id', 36)->index();
                $table->unsignedSmallInteger('period_year');
                $table->unsignedTinyInteger('period_month');
                $table->string('item_type', 20); // 'material' or 'product'
                $table->char('item_id', 36)->index();
                $table->char('location_id', 36)->nullable()->index();
                $table->decimal('opening_quantity', 15, 4)->default(0.0);
                $table->decimal('opening_value', 15, 2)->default(0.0);
                $table->decimal('received_quantity', 15, 4)->default(0.0);
                $table->decimal('received_cost', 15, 2)->default(0.0);
                $table->decimal('closing_average_cost', 15, 4)->default(0.0);
                $table->decimal('closing_quantity', 15, 4)->default(0.0);
                $table->decimal('closing_value', 15, 2)->default(0.0);
                $table->boolean('is_locked')->default(true);
                $table->char('closed_by', 36)->nullable();
                $table->timestamp('closed_at')->nullable();
                $table->timestamps();

                $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
                $table->unique(['business_id', 'period_year', 'period_month', 'item_type', 'item_id', 'location_id'], 'uq_periodic_closing_item');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('periodic_inventory_closings');
        Schema::dropIfExists('product_unit_conversions');

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropColumn(['balance_before', 'cost_before', 'cost_after', 'purchase_unit_id', 'purchase_quantity']);
        });

        Schema::table('goods_receipt_items', function (Blueprint $table) {
            $table->dropColumn(['purchase_order_item_id', 'received_unit_id', 'conversion_factor', 'base_quantity', 'allocated_landed_cost', 'effective_base_cost']);
        });

        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->dropColumn(['landed_cost_amount', 'landed_cost_allocation_method', 'landed_cost_details']);
        });

        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropColumn(['base_unit_id', 'conversion_factor', 'base_quantity', 'estimated_base_cost']);
        });
    }
};
