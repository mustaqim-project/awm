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
        // 1. Enhance pos_printers table with station, alias, fallback, and hardware attributes
        Schema::table('pos_printers', function (Blueprint $table): void {
            if (!Schema::hasColumn('pos_printers', 'alias')) {
                $table->string('alias', 100)->nullable()->after('name');
            }
            if (!Schema::hasColumn('pos_printers', 'station')) {
                $table->string('station', 50)->default('cashier')->after('alias'); // cashier, kitchen, bar, pastry, packing, spk_service, office, checker
            }
            if (!Schema::hasColumn('pos_printers', 'fallback_printer_id')) {
                $table->foreignUuid('fallback_printer_id')->nullable()->after('station')->constrained('pos_printers')->nullOnDelete();
            }
            if (!Schema::hasColumn('pos_printers', 'copies')) {
                $table->unsignedSmallInteger('copies')->default(1)->after('paper_width');
            }
            if (!Schema::hasColumn('pos_printers', 'auto_cut')) {
                $table->boolean('auto_cut')->default(true)->after('copies');
            }
            if (!Schema::hasColumn('pos_printers', 'open_cash_drawer')) {
                $table->boolean('open_cash_drawer')->default(false)->after('auto_cut');
            }
            if (!Schema::hasColumn('pos_printers', 'print_density')) {
                $table->string('print_density', 20)->default('normal')->after('open_cash_drawer'); // light, normal, dark, extra_dark
            }
            if (!Schema::hasColumn('pos_printers', 'paper_type')) {
                $table->string('paper_type', 30)->default('thermal')->after('print_density'); // thermal, impact, dot_matrix, a4_document
            }
        });

        // 2. Create pos_printer_routing_rules table for multi-industry, multi-station routing
        if (!Schema::hasTable('pos_printer_routing_rules')) {
            Schema::create('pos_printer_routing_rules', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
                $table->foreignUuid('location_id')->nullable()->constrained('locations')->nullOnDelete();
                $table->foreignUuid('pos_register_id')->nullable()->constrained('pos_registers')->nullOnDelete();
                $table->foreignUuid('printer_id')->constrained('pos_printers')->cascadeOnDelete();
                $table->foreignUuid('fallback_printer_id')->nullable()->constrained('pos_printers')->nullOnDelete();
                $table->string('document_type', 50); // customer_receipt, kitchen_ticket, bar_ticket, pastry_ticket, spk_service, packing_slip, bill_summary, shift_report, invoice_office
                $table->foreignUuid('category_id')->nullable()->constrained('product_categories')->nullOnDelete();
                $table->string('department', 50)->nullable(); // food, beverage, service, sparepart, retail, general
                $table->string('order_type', 50)->nullable(); // dine_in, takeaway, delivery, booking, work_order
                $table->unsignedSmallInteger('copies')->default(1);
                $table->boolean('is_active')->default(true);
                $table->integer('priority')->default(0);
                $table->timestamps();

                $table->index(['business_id', 'location_id']);
                $table->index(['business_id', 'document_type']);
                $table->index(['business_id', 'is_active']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_printer_routing_rules');

        Schema::table('pos_printers', function (Blueprint $table): void {
            $table->dropForeign(['fallback_printer_id']);
            $table->dropColumn([
                'alias',
                'station',
                'fallback_printer_id',
                'copies',
                'auto_cut',
                'open_cash_drawer',
                'print_density',
                'paper_type',
            ]);
        });
    }
};
