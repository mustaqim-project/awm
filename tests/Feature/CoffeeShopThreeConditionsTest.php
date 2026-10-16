<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Material;
use App\Models\MaterialPrice;
use App\Models\PosOrder;
use App\Models\Product;
use Database\Seeders\CoffeeShopThreeConditionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CoffeeShopThreeConditionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CoffeeShopThreeConditionsSeeder::class);
    }

    public function test_condition_1_standalone_product_stock_and_no_recipe(): void
    {
        $biz = Business::where('slug', 'kopi-senja-utama')->firstOrFail();
        $standalone = Product::where('business_id', $biz->id)
            ->where('code', 'CFE-BEAN-ARA-1KG')
            ->firstOrFail();

        $this->assertEquals('Biji Kopi Arabica A 1 kg', $standalone->name);
        $this->assertEquals(165000.0, (float) $standalone->selling_price);
        $this->assertEquals(110000.0, (float) $standalone->base_cost);

        // Standalone product must have 25 pcs remaining (30 initial - 5 sold)
        $this->assertEquals(25.0, $standalone->calculateEffectiveStock());

        // Must not have BOM recipe
        $activeModel = $standalone->costModels()->where('is_active', true)->first();
        $this->assertNotNull($activeModel);
        $this->assertEquals('retail', $activeModel->method);
        $this->assertEmpty($standalone->getMaterialDeductions(1.0));
    }

    public function test_condition_2_recipe_bom_and_effective_stock_calculation(): void
    {
        $biz = Business::where('slug', 'kopi-senja-utama')->firstOrFail();
        $latte = Product::where('business_id', $biz->id)
            ->where('code', 'CFE-ICED-LATTE')
            ->firstOrFail();

        $this->assertEquals('Iced Latte Single Origin', $latte->name);
        $this->assertEquals(25000.0, (float) $latte->selling_price);

        // Recipe items check: exactly 4 items (Kopi 20g, Susu 100ml, Gula 15ml, Cup 1pcs)
        $deductions = $latte->getMaterialDeductions(1.0);
        $this->assertCount(4, $deductions);

        // Producible cups must be 125 based on bottleneck (Susu 12.500ml / 100ml = 125, Cup 125 pcs / 1 = 125)
        $this->assertEquals(125.0, $latte->calculateEffectiveStock());
    }

    public function test_condition_3_fluctuating_material_prices_and_weighted_average(): void
    {
        $biz = Business::where('slug', 'kopi-senja-utama')->firstOrFail();
        $matKopi = Material::where('business_id', $biz->id)
            ->where('code', 'CFE-MAT-BEANS-ARA')
            ->firstOrFail();

        $priceHistories = MaterialPrice::where('material_id', $matKopi->id)
            ->orderBy('sequence')
            ->get();

        $this->assertCount(3, $priceHistories);
        $this->assertEquals(150000.0, (float) $priceHistories[0]->purchase_price);
        $this->assertEquals(160000.0, (float) $priceHistories[1]->purchase_price);
        $this->assertEquals(170000.0, (float) $priceHistories[2]->purchase_price);

        // Sisa stok kopi: 40.000g (40kg) - 1.500g (terpakai untuk 75 cup Latte) = 38.500g
        $this->assertEquals(38500.0, (float) $matKopi->stocks()->sum('quantity'));

        // Valuasi Moving Average Cost di persediaan
        $stockRecord = $matKopi->stocks()->firstOrFail();
        $this->assertEquals(170.0, (float) $stockRecord->last_cost); // Rp 170 / gram
        $this->assertEquals(160.144, round((float) $stockRecord->avg_purchase_cost, 3));
    }

    public function test_pos_orders_aggregation_revenue_and_profit(): void
    {
        $biz = Business::where('slug', 'kopi-senja-utama')->firstOrFail();
        $orders = PosOrder::where('business_id', $biz->id)
            ->where('order_number', 'like', 'POS-SENJA-202610%')
            ->get();

        $this->assertCount(3, $orders);
        $this->assertEquals(2700000.0, (float) $orders->sum('total_amount'));
        $this->assertEquals(1043215.0, (float) $orders->sum('total_hpp_cost'));
        $this->assertEquals(1656785.0, (float) $orders->sum('total_gross_profit'));
    }
}
