<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Business;
use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\Product;
use App\Models\Unit;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ProductStockScopeTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private Location $location;
    private Unit $unit;

    protected function setUp(): void
    {
        parent::setUp();

        $this->business = Business::create([
            'name' => 'Stock Test Merchant',
            'legal_name' => 'PT Stock Test',
            'business_type' => 'retail',
        ]);
        Context::setBusiness($this->business);

        $this->unit = Unit::create([
            'code' => 'PCS',
            'name' => 'Pcs',
            'category' => Unit::CATEGORY_QUANTITY,
            'is_base' => true,
        ]);

        $this->location = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Main Warehouse',
            'code' => 'WH-MAIN',
            'type' => 'warehouse',
            'is_active' => true,
        ]);
    }

    public function test_product_out_of_stock_scope_correctly_identifies_zero_or_missing_stock(): void
    {
        // Product 1: Has stock record with 15 units (IN STOCK)
        $inStockProduct = Product::create([
            'business_id' => $this->business->id,
            'output_unit_id' => $this->unit->id,
            'code' => 'PRD-IN-01',
            'name' => 'Produk Ready Stock',
            'selling_price' => 50000,
            'is_active' => true,
        ]);
        InventoryStock::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'product_id' => $inStockProduct->id,
            'quantity' => 15,
        ]);

        // Product 2: Has stock record with 0 units (OUT OF STOCK)
        $zeroStockProduct = Product::create([
            'business_id' => $this->business->id,
            'output_unit_id' => $this->unit->id,
            'code' => 'PRD-OUT-01',
            'name' => 'Produk Stok Habis Nol',
            'selling_price' => 75000,
            'is_active' => true,
        ]);
        InventoryStock::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'product_id' => $zeroStockProduct->id,
            'quantity' => 0,
        ]);

        // Product 3: No stock records at all (OUT OF STOCK)
        $noStockProduct = Product::create([
            'business_id' => $this->business->id,
            'output_unit_id' => $this->unit->id,
            'code' => 'PRD-OUT-02',
            'name' => 'Produk Tanpa Rekor Stok',
            'selling_price' => 30000,
            'is_active' => true,
        ]);

        // Verify outOfStock query does NOT crash with Unknown column 'stock' and returns 2 products
        $outOfStock = Product::outOfStock()->pluck('code')->toArray();
        $this->assertContains('PRD-OUT-01', $outOfStock);
        $this->assertContains('PRD-OUT-02', $outOfStock);
        $this->assertNotContains('PRD-IN-01', $outOfStock);

        // Verify inStock query returns only in-stock product
        $inStock = Product::inStock()->pluck('code')->toArray();
        $this->assertContains('PRD-IN-01', $inStock);
        $this->assertNotContains('PRD-OUT-01', $inStock);
        $this->assertNotContains('PRD-OUT-02', $inStock);

        // Verify virtual stock accessor works
        $this->assertEquals(15.0, (float) $inStockProduct->stock);
        $this->assertEquals(0.0, (float) $zeroStockProduct->stock);
        $this->assertEquals(0.0, (float) $noStockProduct->stock);
        $this->assertEquals('Pcs', $inStockProduct->unit);
    }

    public function test_product_with_direct_material_stock_resolution(): void
    {
        $material = \App\Models\Material::create([
            'business_id' => $this->business->id,
            'unit_id' => $this->unit->id,
            'code' => 'MAT-COFFEE-BEAN',
            'name' => 'Biji Kopi Arabika',
        ]);

        $materialProduct = Product::create([
            'business_id' => $this->business->id,
            'direct_material_id' => $material->id,
            'output_unit_id' => $this->unit->id,
            'code' => 'PRD-MAT-01',
            'name' => 'Kopi Arabika 250g',
            'selling_price' => 85000,
            'is_active' => true,
        ]);

        // Initially no stock for material -> outOfStock
        $this->assertTrue(Product::where('id', $materialProduct->id)->outOfStock()->exists());
        $this->assertFalse(Product::where('id', $materialProduct->id)->inStock()->exists());
        $this->assertEquals(0.0, (float) $materialProduct->stock);

        // Add 50 stock to the material
        InventoryStock::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'material_id' => $material->id,
            'quantity' => 50,
        ]);

        // Now inStock
        $this->assertFalse(Product::where('id', $materialProduct->id)->outOfStock()->exists());
        $this->assertTrue(Product::where('id', $materialProduct->id)->inStock()->exists());
        $this->assertEquals(50.0, (float) $materialProduct->fresh()->stock);
    }
}

