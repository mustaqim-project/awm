<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Inventory\CostingEngine;
use App\Domain\Inventory\StockService;
use App\Domain\Manufacturing\ProductionService;
use App\Domain\Material\UnitConversionService;
use App\Domain\Pos\PosOrderService;
use App\Models\BomHeader;
use App\Models\BomItem;
use App\Models\Business;
use App\Models\CostModel;
use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\Material;
use App\Models\MaterialPrice;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductionOrder;
use App\Models\StockMovement;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProductBomConsumptionSchemesIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $user;
    private Location $warehouseLocation;
    private Location $kitchenLocation;
    private Unit $unitPcs;
    private Unit $unitGram;
    private Unit $unitMl;
    private StockService $stockService;
    private CostingEngine $costingEngine;
    private ProductionService $productionService;
    private PosOrderService $posOrderService;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->user = User::create([
            'name' => 'Owner Kuliner Nusantara',
            'email' => 'owner_kuliner@example.com',
            'password' => 'password123',
        ]);

        $this->business = Business::create([
            'name' => 'PT COOCA Kuliner Nusantara',
            'costing_method' => CostingEngine::METHOD_MOVING_AVERAGE,
            'currency_symbol' => 'Rp',
            'currency_code' => 'IDR',
        ]);

        $this->business->users()->attach($this->user->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->user->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);

        $this->warehouseLocation = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Gudang Utama Persediaan',
            'type' => 'warehouse',
            'is_active' => true,
        ]);

        $this->kitchenLocation = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Dapur & Outlet Kasir POS',
            'type' => 'store',
            'is_active' => true,
        ]);

        $this->unitPcs = Unit::create([
            'business_id' => $this->business->id,
            'name' => 'Pcs',
            'code' => 'pcs',
            'symbol' => 'pcs',
            'category' => Unit::CATEGORY_QUANTITY,
            'is_base' => true,
            'is_active' => true,
        ]);

        $this->unitGram = Unit::create([
            'business_id' => $this->business->id,
            'name' => 'Gram',
            'code' => 'g',
            'symbol' => 'g',
            'category' => Unit::CATEGORY_WEIGHT,
            'is_base' => true,
            'is_active' => true,
        ]);

        $this->unitMl = Unit::create([
            'business_id' => $this->business->id,
            'name' => 'Mililiter',
            'code' => 'ml',
            'symbol' => 'ml',
            'category' => Unit::CATEGORY_VOLUME,
            'is_base' => true,
            'is_active' => true,
        ]);

        $conversionService = new UnitConversionService();
        $this->costingEngine = new CostingEngine($conversionService);
        $this->stockService = new StockService(costingEngine: $this->costingEngine);
        $this->productionService = new ProductionService($this->stockService);
        $this->posOrderService = new PosOrderService(
            stockService: $this->stockService,
            costingEngine: $this->costingEngine
        );
    }

    /**
     * SKEMA 1: PRODUK DENGAN BOM UNTUK PENJUALAN LANGSUNG (F&B / Coffee Shop / Consumption on Sale)
     * - Produk Kopi Americano
     * - Bahan: Biji Kopi (18 gram), Air Bersih (200 ml)
     * - Tidak ada proses SPKP terpisah.
     * - Stok produk jadi tidak bertambah sebelum pesanan.
     * - Penjualan di POS otomatis memotong bahan baku dan menghitung HPP on-the-fly.
     */
    public function test_scheme_1_fb_consumption_on_sale(): void
    {
        // 1. Setup Bahan Baku
        $coffeeBeans = Material::create([
            'business_id' => $this->business->id,
            'name' => 'Espresso Beans Arabica',
            'code' => 'MAT-COFFEE-01',
            'unit_id' => $this->unitGram->id,
            'is_active' => true,
        ]);
        MaterialPrice::create([
            'business_id' => $this->business->id,
            'material_id' => $coffeeBeans->id,
            'purchase_price' => 300.0,
            'purchase_unit_id' => $this->unitGram->id,
            'effective_date' => now()->toDateString(),
        ]);

        $water = Material::create([
            'business_id' => $this->business->id,
            'name' => 'Air RO Filtrasi',
            'code' => 'MAT-WATER-01',
            'unit_id' => $this->unitMl->id,
            'is_active' => true,
        ]);
        MaterialPrice::create([
            'business_id' => $this->business->id,
            'material_id' => $water->id,
            'purchase_price' => 5.0,
            'purchase_unit_id' => $this->unitMl->id,
            'effective_date' => now()->toDateString(),
        ]);

        // Initial Raw Material Stocks: Beans = 180g (cukup 10 cup), Water = 2000ml (cukup 10 cup)
        $this->stockService->recordMovement(
            businessId: $this->business->id,
            locationId: $this->kitchenLocation->id,
            productId: null,
            movementType: StockMovement::TYPE_INITIAL,
            quantityChange: 180.0,
            unitCost: 300.0,
            userId: $this->user->id,
            materialId: $coffeeBeans->id
        );
        $this->stockService->recordMovement(
            businessId: $this->business->id,
            locationId: $this->kitchenLocation->id,
            productId: null,
            movementType: StockMovement::TYPE_INITIAL,
            quantityChange: 2000.0,
            unitCost: 5.0,
            userId: $this->user->id,
            materialId: $water->id
        );

        // 2. Setup Produk Americano dengan Skema consumption_on_sale
        $americano = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Iced Americano Single Origin',
            'code' => 'PRD-AMR-01',
            'type' => Product::TYPE_GOODS,
            'bom_consumption_type' => Product::BOM_CONSUMPTION_ON_SALE,
            'output_unit_id' => $this->unitPcs->id,
            'selling_price' => 22000.0,
            'base_cost' => 6400.0, // 5.400 + 1.000
            'is_active' => true,
        ]);

        $costModel = CostModel::create([
            'business_id' => $this->business->id,
            'product_id' => $americano->id,
            'name' => 'Resep Standar Americano',
            'method' => CostModel::METHOD_RECIPE_BOM,
            'is_active' => true,
        ]);
        $bomHeader = BomHeader::create([
            'cost_model_id' => $costModel->id,
            'name' => 'Formula 1 Cup Americano',
            'type' => BomHeader::TYPE_RECIPE,
            'output_quantity' => 1.0,
        ]);
        BomItem::create([
            'bom_header_id' => $bomHeader->id,
            'material_id' => $coffeeBeans->id,
            'quantity' => 18.0,
            'unit_id' => $this->unitGram->id,
        ]);
        BomItem::create([
            'bom_header_id' => $bomHeader->id,
            'material_id' => $water->id,
            'quantity' => 200.0,
            'unit_id' => $this->unitMl->id,
        ]);

        // Verifikasi model helper
        $this->assertTrue($americano->isConsumptionOnSale());
        $this->assertFalse($americano->isConsumptionOnProduction());
        $this->assertFalse($americano->isNoBom());

        // Verifikasi kalkulasi effective stock: 180g beans / 18g = 10, 2000ml water / 200ml = 10 => 10 cup
        $effectiveStock = $americano->calculateEffectiveStock($this->kitchenLocation->id);
        $this->assertEquals(10.0, $effectiveStock);

        // 3. Transaksi Penjualan Kasir POS: Jual 3 Cup Americano
        $posOrder = $this->posOrderService->checkout(
            $this->business,
            $this->user,
            [
                [
                    'product_id' => $americano->id,
                    'quantity' => 3.0,
                    'unit_price' => 22000.0,
                ],
            ],
            [
                [
                    'payment_method' => 'cash',
                    'amount' => 66000.0,
                ],
            ],
            [
                'location_id' => $this->kitchenLocation->id,
                'notes' => 'Kasir Meja 05',
            ]
        );

        // 4. Verifikasi Mutasi Persediaan
        // Stok produk jadi TIDAK dipotong/dibuat karena On-Demand
        $productStock = InventoryStock::where('business_id', $this->business->id)
            ->where('product_id', $americano->id)
            ->sum('quantity');
        $this->assertEquals(0.0, (float) $productStock);

        // Stok Bahan Baku berkurang persis: Beans 180 - (3 * 18) = 126g, Water 2000 - (3 * 200) = 1400ml
        $beansStock = InventoryStock::where('business_id', $this->business->id)
            ->where('material_id', $coffeeBeans->id)
            ->sum('quantity');
        $this->assertEquals(126.0, (float) $beansStock);

        $waterStock = InventoryStock::where('business_id', $this->business->id)
            ->where('material_id', $water->id)
            ->sum('quantity');
        $this->assertEquals(1400.0, (float) $waterStock);

        // Effective stock produk sekarang berkurang menjadi 7 cup (126/18 = 7)
        $this->assertEquals(7.0, $americano->calculateEffectiveStock($this->kitchenLocation->id));

        // HPP transaksi POS tercatat akurat
        $orderItem = $posOrder->items->first();
        $this->assertEquals(3.0, (float) $orderItem->quantity);
        $this->assertEquals(6400.0, (float) $orderItem->unit_cost_hpp);
        $this->assertEquals(19200.0, (float) $orderItem->total_hpp);
    }

    /**
     * SKEMA 2: PRODUK DENGAN BOM DAN PROSES PRODUKSI (Manufaktur / Frozen Food / Consumption on Production)
     * - Produk Tahu Bakso Frozen
     * - Komposisi: Daging (500g), Tepung (200g), Tahu (100 pcs) untuk target 100 pcs
     * - Wajib melalui SPKP (Surat Perintah Kerja Produksi)
     * - Bahan baku dipotong saat proses produksi dimulai (issueMaterials)
     * - Penyelesaian produksi: 95 pcs good output, 5 pcs reject
     * - Hanya 95 pcs masuk ke stok barang jadi (finished goods)
     * - 5 pcs reject dicatat sebagai kerugian produksi (production loss/scrap)
     * - Unit HPP menyerap biaya bahan baku + biaya proses produksi
     * - Saat penjualan di POS: HANYA potong stok barang jadi, TIDAK PERNAH memotong bahan baku lagi (Anti-Double Deduction)
     */
    public function test_scheme_2_manufacturing_consumption_on_production(): void
    {
        // 1. Setup Bahan Baku
        $meat = Material::create([
            'business_id' => $this->business->id,
            'name' => 'Daging Sapi Giling Super',
            'code' => 'MAT-MEAT-01',
            'unit_id' => $this->unitGram->id,
            'is_active' => true,
        ]);
        MaterialPrice::create([
            'business_id' => $this->business->id,
            'material_id' => $meat->id,
            'purchase_price' => 120.0,
            'purchase_unit_id' => $this->unitGram->id,
            'effective_date' => now()->toDateString(),
        ]);

        $flour = Material::create([
            'business_id' => $this->business->id,
            'name' => 'Tepung Tapioka Khusus',
            'code' => 'MAT-FLOUR-01',
            'unit_id' => $this->unitGram->id,
            'is_active' => true,
        ]);
        MaterialPrice::create([
            'business_id' => $this->business->id,
            'material_id' => $flour->id,
            'purchase_price' => 20.0,
            'purchase_unit_id' => $this->unitGram->id,
            'effective_date' => now()->toDateString(),
        ]);

        $tofu = Material::create([
            'business_id' => $this->business->id,
            'name' => 'Tahu Kulit Segitiga',
            'code' => 'MAT-TOFU-01',
            'unit_id' => $this->unitPcs->id,
            'is_active' => true,
        ]);
        MaterialPrice::create([
            'business_id' => $this->business->id,
            'material_id' => $tofu->id,
            'purchase_price' => 500.0,
            'purchase_unit_id' => $this->unitPcs->id,
            'effective_date' => now()->toDateString(),
        ]);

        // Total Material Cost = 60.000 + 4.000 + 50.000 = Rp 114.000

        // Masukkan Stok Bahan Baku ke Gudang Bahan
        $this->stockService->recordMovement(
            businessId: $this->business->id,
            locationId: $this->warehouseLocation->id,
            productId: null,
            movementType: StockMovement::TYPE_INITIAL,
            quantityChange: 1000.0, // 1.000 gram daging
            unitCost: 120.0,
            userId: $this->user->id,
            materialId: $meat->id
        );
        $this->stockService->recordMovement(
            businessId: $this->business->id,
            locationId: $this->warehouseLocation->id,
            productId: null,
            movementType: StockMovement::TYPE_INITIAL,
            quantityChange: 500.0, // 500 gram tepung
            unitCost: 20.0,
            userId: $this->user->id,
            materialId: $flour->id
        );
        $this->stockService->recordMovement(
            businessId: $this->business->id,
            locationId: $this->warehouseLocation->id,
            productId: null,
            movementType: StockMovement::TYPE_INITIAL,
            quantityChange: 200.0, // 200 pcs tahu
            unitCost: 500.0,
            userId: $this->user->id,
            materialId: $tofu->id
        );

        // 2. Setup Produk Tahu Bakso dengan Skema consumption_on_production
        $tahuBakso = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Tahu Bakso Frozen Pack',
            'code' => 'PRD-THB-01',
            'type' => Product::TYPE_GOODS,
            'bom_consumption_type' => Product::BOM_CONSUMPTION_ON_PRODUCTION,
            'output_unit_id' => $this->unitPcs->id,
            'selling_price' => 3500.0,
            'base_cost' => 1140.0,
            'is_active' => true,
        ]);

        $costModel = CostModel::create([
            'business_id' => $this->business->id,
            'product_id' => $tahuBakso->id,
            'name' => 'BOM Standar Tahu Bakso',
            'method' => CostModel::METHOD_RECIPE_BOM,
            'is_active' => true,
        ]);
        $bomHeader = BomHeader::create([
            'cost_model_id' => $costModel->id,
            'name' => 'Formula Batch 100 pcs Tahu Bakso',
            'type' => BomHeader::TYPE_RECIPE,
            'output_quantity' => 100.0,
        ]);
        BomItem::create([
            'bom_header_id' => $bomHeader->id,
            'material_id' => $meat->id,
            'quantity' => 5.0, // 5g per pcs -> 100 pcs = 500g
            'unit_id' => $this->unitGram->id,
        ]);
        BomItem::create([
            'bom_header_id' => $bomHeader->id,
            'material_id' => $flour->id,
            'quantity' => 2.0, // 2g per pcs -> 100 pcs = 200g
            'unit_id' => $this->unitGram->id,
        ]);
        BomItem::create([
            'bom_header_id' => $bomHeader->id,
            'material_id' => $tofu->id,
            'quantity' => 1.0, // 1 pcs per pcs -> 100 pcs = 100 pcs
            'unit_id' => $this->unitPcs->id,
        ]);

        // Verifikasi skema manufaktur
        $this->assertTrue($tahuBakso->isConsumptionOnProduction());
        $this->assertFalse($tahuBakso->isConsumptionOnSale());
        $this->assertFalse($tahuBakso->isNoBom());

        // Verifikasi bahwa SEBELUM diproduksi, effective stock barang jadi adalah 0
        // (Bahan baku di gudang belum diproduksi menjadi Tahu Bakso)
        $this->assertEquals(0.0, $tahuBakso->calculateEffectiveStock($this->warehouseLocation->id));

        // 3. Pembuatan SPKP (Surat Perintah Kerja Produksi) untuk 100 pcs
        $spkp = $this->productionService->createOrder(
            business: $this->business,
            data: [
                'product_id' => $tahuBakso->id,
                'bom_header_id' => $bomHeader->id,
                'planned_quantity' => 100.0,
                'source_location_id' => $this->warehouseLocation->id,
                'target_location_id' => $this->kitchenLocation->id,
                'total_labor_cost' => 15000.0, // Biaya tenaga kerja
                'total_machine_cost' => 6000.0, // Biaya gas & listrik kukusan
                'batch_number' => 'BATCH-THB-202611',
            ],
            user: $this->user
        );

        $this->assertEquals(ProductionOrder::STATUS_DRAFT, $spkp->status);

        // 4. Proses Pengeluaran Bahan Baku Produksi (issueMaterials)
        $spkp = $this->productionService->issueMaterials($spkp, [], $this->user);
        $this->assertEquals(ProductionOrder::STATUS_IN_PROGRESS, $spkp->status);

        // Bahan baku terpotong dari gudang bahan
        $this->assertEquals(500.0, (float) InventoryStock::where('material_id', $meat->id)->sum('quantity'));
        $this->assertEquals(300.0, (float) InventoryStock::where('material_id', $flour->id)->sum('quantity'));
        $this->assertEquals(100.0, (float) InventoryStock::where('material_id', $tofu->id)->sum('quantity'));

        // Total biaya bahan aktual yang dicatat pada SPKP = 60.000 + 4.000 + 50.000 = 114.000
        $this->assertEquals(114000.0, (float) $spkp->total_material_cost);

        // 5. Penyelesaian Produksi (completeOrder)
        // Simulasi: Target 100 pcs -> Berhasil 95 pcs, Reject 5 pcs
        $spkp = $this->productionService->completeOrder(
            order: $spkp,
            data: [
                'actual_quantity' => 95.0,
                'rejected_quantity' => 5.0,
                'total_labor_cost' => 15000.0,
                'total_machine_cost' => 6000.0,
            ],
            user: $this->user
        );

        $this->assertEquals(ProductionOrder::STATUS_COMPLETED, $spkp->status);
        $this->assertEquals(95.0, (float) $spkp->actual_quantity);
        $this->assertEquals(5.0, (float) $spkp->rejected_quantity);

        // Total Cost Produksi = Biaya Bahan (114.000) + Labor (15.000) + Machine (6.000) = Rp 135.000
        // HPP per unit = Rp 135.000 / 95 pcs good output = Rp 1.421,05
        $expectedHpp = 135000.0 / 95.0;
        $this->assertEqualsWithDelta($expectedHpp, (float) $spkp->unit_cost_hpp, 0.01);

        // Stok Barang Jadi (Finished Goods) yang masuk ke kitchenLocation bertambah persis 95 pcs
        $finishedStock = InventoryStock::where('business_id', $this->business->id)
            ->where('location_id', $this->kitchenLocation->id)
            ->where('product_id', $tahuBakso->id)
            ->value('quantity');
        $this->assertEquals(95.0, (float) $finishedStock);

        // 5 pcs reject TIDAK masuk stok barang jadi
        $this->assertEquals(95.0, $tahuBakso->calculateEffectiveStock($this->kitchenLocation->id));

        // Verifikasi mutasi scrap tercatat
        $hasScrapMovement = StockMovement::where('business_id', $this->business->id)
            ->where('reference_id', $spkp->id)
            ->where('movement_type', StockMovement::TYPE_PRODUCTION_SCRAP)
            ->exists();
        $this->assertTrue($hasScrapMovement);

        // 6. PENJUALAN DI KASIR POS & PEMBUKTIAN ANTI-DOUBLE DEDUCTION
        // Kasir menjual 15 pcs Tahu Bakso
        $meatStockBeforePos = (float) InventoryStock::where('material_id', $meat->id)->sum('quantity');
        $flourStockBeforePos = (float) InventoryStock::where('material_id', $flour->id)->sum('quantity');
        $tofuStockBeforePos = (float) InventoryStock::where('material_id', $tofu->id)->sum('quantity');

        $posOrder = $this->posOrderService->checkout(
            $this->business,
            $this->user,
            [
                [
                    'product_id' => $tahuBakso->id,
                    'quantity' => 15.0,
                    'unit_price' => 3500.0,
                ],
            ],
            [
                [
                    'payment_method' => 'cash',
                    'amount' => 52500.0,
                ],
            ],
            [
                'location_id' => $this->kitchenLocation->id,
                'notes' => 'Kasir POS Frozen Food',
            ]
        );

        // A. Stok barang jadi terpotong dari 95 pcs menjadi 80 pcs
        $finishedStockAfter = (float) InventoryStock::where('business_id', $this->business->id)
            ->where('location_id', $this->kitchenLocation->id)
            ->where('product_id', $tahuBakso->id)
            ->value('quantity');
        $this->assertEquals(80.0, $finishedStockAfter);
        $this->assertEquals(80.0, $tahuBakso->calculateEffectiveStock($this->kitchenLocation->id));

        // B. CRITICAL ASSERTION: BAHAN BAKU TIDAK PERNAH DIPOTONG LAGI!
        $this->assertEquals($meatStockBeforePos, (float) InventoryStock::where('material_id', $meat->id)->sum('quantity'));
        $this->assertEquals($flourStockBeforePos, (float) InventoryStock::where('material_id', $flour->id)->sum('quantity'));
        $this->assertEquals($tofuStockBeforePos, (float) InventoryStock::where('material_id', $tofu->id)->sum('quantity'));

        // C. Mutasi POS yang tercatat adalah mutasi pada produk barang jadi (material_id NULL)
        $posMovement = StockMovement::where('business_id', $this->business->id)
            ->where('reference_id', $posOrder->id)
            ->where('movement_type', StockMovement::TYPE_POS_SALE)
            ->first();

        $this->assertNotNull($posMovement);
        $this->assertEquals($tahuBakso->id, $posMovement->product_id);
        $this->assertNull($posMovement->material_id);
        $this->assertEquals(-15.0, (float) $posMovement->quantity_change);
    }

    /**
     * SKEMA 3: PRODUK TANPA BOM (Retail / Trading Goods)
     * - Produk Air Mineral Botol 600ml
     * - Tanpa BOM, tanpa proses produksi.
     * - Stok dikelola langsung: Masuk via PO/Goods Receipt, Keluar saat Penjualan.
     */
    public function test_scheme_3_retail_no_bom(): void
    {
        // 1. Setup Produk Retail
        $mineralWater = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Air Mineral Botol 600ml',
            'code' => 'PRD-AQUA-600',
            'type' => Product::TYPE_GOODS,
            'bom_consumption_type' => Product::BOM_NO_BOM,
            'output_unit_id' => $this->unitPcs->id,
            'selling_price' => 5000.0,
            'base_cost' => 3000.0,
            'is_active' => true,
        ]);

        $this->assertTrue($mineralWater->isNoBom());
        $this->assertFalse($mineralWater->isConsumptionOnSale());
        $this->assertFalse($mineralWater->isConsumptionOnProduction());

        // 2. Stok Awal Pembelian / Penerimaan Barang: 50 botol
        $this->stockService->recordMovement(
            businessId: $this->business->id,
            locationId: $this->kitchenLocation->id,
            productId: $mineralWater->id,
            movementType: StockMovement::TYPE_GOODS_RECEIPT,
            quantityChange: 50.0,
            unitCost: 3000.0,
            userId: $this->user->id
        );

        $this->assertEquals(50.0, $mineralWater->calculateEffectiveStock($this->kitchenLocation->id));

        // 3. Penjualan di POS: Jual 5 botol
        $posOrder = $this->posOrderService->checkout(
            $this->business,
            $this->user,
            [
                [
                    'product_id' => $mineralWater->id,
                    'quantity' => 5.0,
                    'unit_price' => 5000.0,
                ],
            ],
            [
                [
                    'payment_method' => 'cash',
                    'amount' => 25000.0,
                ],
            ],
            [
                'location_id' => $this->kitchenLocation->id,
                'notes' => 'Kasir Retail',
            ]
        );

        // 4. Verifikasi Mutasi Persediaan
        $stockAfter = (float) InventoryStock::where('business_id', $this->business->id)
            ->where('location_id', $this->kitchenLocation->id)
            ->where('product_id', $mineralWater->id)
            ->value('quantity');

        $this->assertEquals(45.0, $stockAfter);
        $this->assertEquals(45.0, $mineralWater->calculateEffectiveStock($this->kitchenLocation->id));

        // Tidak ada mutasi material apa pun
        $materialMovements = StockMovement::where('business_id', $this->business->id)
            ->where('reference_id', $posOrder->id)
            ->whereNotNull('material_id')
            ->count();
        $this->assertEquals(0, $materialMovements);
    }
}
