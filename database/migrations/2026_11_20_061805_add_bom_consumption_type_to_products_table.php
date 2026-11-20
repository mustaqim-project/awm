<?php

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
        Schema::table('products', function (Blueprint $table) {
            $table->string('bom_consumption_type', 30)
                ->default('no_bom')
                ->after('type')
                ->comment('no_bom (retail/trading), consumption_on_sale (F&B recipe on POS sale), consumption_on_production (manufacturing via SPKP)');

            $table->index(['business_id', 'bom_consumption_type'], 'products_bom_consumption_index');
        });

        // Backfill existing products with recipe BOM or direct material to 'consumption_on_sale'
        try {
            DB::table('products')
                ->whereNotNull('direct_material_id')
                ->update(['bom_consumption_type' => 'consumption_on_sale']);

            if (Schema::hasTable('cost_models') && Schema::hasTable('bom_headers')) {
                $bomProductIds = DB::table('cost_models')
                    ->join('bom_headers', 'cost_models.id', '=', 'bom_headers.cost_model_id')
                    ->whereNotNull('cost_models.product_id')
                    ->pluck('cost_models.product_id')
                    ->unique();

                if ($bomProductIds->isNotEmpty()) {
                    DB::table('products')
                        ->whereIn('id', $bomProductIds)
                        ->update(['bom_consumption_type' => 'consumption_on_sale']);
                }
            }
        } catch (\Throwable $e) {
            // Safe fallback
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_bom_consumption_index');
            $table->dropColumn('bom_consumption_type');
        });
    }
};
