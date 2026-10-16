<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Import\DataExchangeService;
use App\Domain\Import\DataImportService;
use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\BusinessSubscription;
use App\Models\Category;
use App\Models\ChartOfAccount;
use App\Models\Customer;
use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\Material;
use App\Models\MaterialCategory;
use App\Models\MaterialPrice;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Role;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

final class UnifiedDataExchangeSprint4Test extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Business $business;
    private Location $location;
    private Unit $unitPcs;
    private Unit $unitKg;
    private ProductCategory $productCategory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RbacSeeder::class);

        $this->owner = User::create([
            'name' => 'Data Exchange Sprint 4 Owner',
            'email' => 'exchange-sprint4@cooca.test',
            'password' => 'password123',
        ]);

        $this->business = Business::create([
            'name' => 'PT Cooca Retail & F&B Hub',
            'currency' => 'IDR',
        ]);

        $ownerRole = Role::where('slug', 'owner')->firstOrFail();

        $membership = BusinessMembership::create([
            'business_id' => $this->business->id,
            'user_id' => $this->owner->id,
            'role' => 'owner',
            'role_id' => $ownerRole->id,
            'status' => 'active',
        ]);

        $this->owner->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business, $membership);

        BusinessSubscription::updateOrCreate(
            ['business_id' => $this->business->id],
            [
                'plan_code' => BusinessSubscription::PLAN_PREMIUM_ANNUAL,
                'status' => BusinessSubscription::STATUS_ACTIVE,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addYear(),
            ]
        );

        $this->location = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Gudang Pusat Logistik',
            'code' => 'WH-LOG-01',
            'is_active' => true,
        ]);

        $this->unitPcs = Unit::firstOrCreate(
            ['code' => 'pcs'],
            ['name' => 'Pcs', 'category' => 'quantity']
        );

        $this->unitKg = Unit::firstOrCreate(
            ['code' => 'kg'],
            ['name' => 'Kilogram', 'category' => 'weight']
        );

        $this->productCategory = ProductCategory::create([
            'business_id' => $this->business->id,
            'name' => 'Kategori Utama',
            'slug' => 'kategori-utama',
        ]);
    }

    public function test_import_hub_index_renders_with_five_domain_kpis(): void
    {
        // Create sample data across domains
        Product::create([
            'business_id' => $this->business->id,
            'category_id' => $this->productCategory->id,
            'output_unit_id' => $this->unitPcs->id,
            'name' => 'Kopi Latte Special',
            'code' => 'PRD-LATTE',
            'base_cost' => 12000,
            'selling_price' => 25000,
            'unit' => 'cup',
        ]);

        Material::create([
            'business_id' => $this->business->id,
            'name' => 'Biji Kopi Toraja',
            'code' => 'MAT-TRJ',
            'unit_id' => $this->unitKg->id,
        ]);

        Customer::create([
            'business_id' => $this->business->id,
            'name' => 'Pelanggan VIP Budi',
            'phone' => '081234567890',
        ]);

        Supplier::create([
            'business_id' => $this->business->id,
            'name' => 'Supplier Gayo Lestari',
            'phone' => '089876543210',
        ]);

        $response = $this->actingAs($this->owner)
            ->withSession(['current_business_id' => $this->business->id])
            ->get('/import');

        $response->assertStatus(200);
        $response->assertViewHas('totalProducts', 1);
        $response->assertViewHas('totalMaterials', 1);
        $response->assertViewHas('totalCustomers', 1);
        $response->assertSeeText('Unified Data Exchange Hub');
        $response->assertSeeText('Direktori Pertukaran Data 12 Modul (5 Domain Bisnis)');
        $response->assertSeeText('Katalog & Master Data');
        $response->assertSeeText('Inventori & Gudang');
        $response->assertSeeText('Keuangan & Akuntansi');
        $response->assertSeeText('SDM & Penggajian (HRM)');
        $response->assertSeeText('Omnichannel & Ekspor Logistik');
        $response->assertSeeText('Pusat Eksekusi Impor Langsung (Pre-Commit Guaranteed)');
    }

    public function test_material_template_download_generates_living_excel_with_dropdowns(): void
    {
        $response = $this->actingAs($this->owner)
            ->withSession(['current_business_id' => $this->business->id])
            ->get('/import/materials/template?format=xlsx');

        $response->assertStatus(200);
        $this->assertStringContainsString('Template_Import_Bahan_Baku_', (string) $response->headers->get('content-disposition'));

        ob_start();
        $response->sendContent();
        $binary = ob_get_clean();

        $tmpPath = tempnam(sys_get_temp_dir(), 'test_mat_') . '.xlsx';
        file_put_contents($tmpPath, $binary);

        $spreadsheet = IOFactory::load($tmpPath);
        $sheetNames = $spreadsheet->getSheetNames();

        $this->assertContains('INPUT_BAHAN_BAKU', $sheetNames);
        $this->assertContains('DATA_REFERENSI', $sheetNames);
        $this->assertContains('PANDUAN_IMPORT', $sheetNames);

        $sheet = $spreadsheet->getSheetByName('INPUT_BAHAN_BAKU');
        $this->assertStringContainsString('Nama Bahan Baku', (string) $sheet->getCell('A1')->getValue());
        $this->assertStringContainsString('Kode / SKU', (string) $sheet->getCell('B1')->getValue());
        $this->assertStringContainsString('Kategori Bahan', (string) $sheet->getCell('C1')->getValue());

        @unlink($tmpPath);
    }

    public function test_recipe_template_download_generates_living_excel_with_bom_lookups(): void
    {
        $response = $this->actingAs($this->owner)
            ->withSession(['current_business_id' => $this->business->id])
            ->get('/import/recipes/template?format=xlsx');

        $response->assertStatus(200);
        $this->assertStringContainsString('Template_Import_Resep_BOM_', (string) $response->headers->get('content-disposition'));

        ob_start();
        $response->sendContent();
        $binary = ob_get_clean();

        $tmpPath = tempnam(sys_get_temp_dir(), 'test_rcp_') . '.xlsx';
        file_put_contents($tmpPath, $binary);

        $spreadsheet = IOFactory::load($tmpPath);
        $sheetNames = $spreadsheet->getSheetNames();

        $this->assertContains('INPUT_RESEP_BOM', $sheetNames);
        $this->assertContains('DATA_REFERENSI', $sheetNames);
        $this->assertContains('PANDUAN_IMPORT', $sheetNames);

        $sheet = $spreadsheet->getSheetByName('INPUT_RESEP_BOM');
        $this->assertStringContainsString('Produk Jadi', (string) $sheet->getCell('A1')->getValue());
        $this->assertStringContainsString('Bahan Baku', (string) $sheet->getCell('B1')->getValue());

        @unlink($tmpPath);
    }

    public function test_inventory_template_download_generates_dual_resolution_references(): void
    {
        // Seed 1 product and 1 material
        Product::create([
            'business_id' => $this->business->id,
            'category_id' => $this->productCategory->id,
            'output_unit_id' => $this->unitPcs->id,
            'name' => 'Croissant Almond',
            'code' => 'CR-ALM-01',
            'base_cost' => 15000,
            'selling_price' => 32000,
            'unit' => 'pcs',
        ]);

        Material::create([
            'business_id' => $this->business->id,
            'name' => 'Mentega Premium French',
            'code' => 'MAT-MNT-01',
            'unit_id' => $this->unitKg->id,
        ]);

        $response = $this->actingAs($this->owner)
            ->withSession(['current_business_id' => $this->business->id])
            ->get('/import/inventory/template?format=xlsx');

        $response->assertStatus(200);
        $this->assertStringContainsString('Template_Import_Saldo_Stok_', (string) $response->headers->get('content-disposition'));

        ob_start();
        $response->sendContent();
        $binary = ob_get_clean();

        $tmpPath = tempnam(sys_get_temp_dir(), 'test_inv_') . '.xlsx';
        file_put_contents($tmpPath, $binary);

        $spreadsheet = IOFactory::load($tmpPath);
        $sheetNames = $spreadsheet->getSheetNames();

        $this->assertContains('INPUT_SALDO_STOK', $sheetNames);
        $this->assertContains('DATA_REFERENSI', $sheetNames);
        $this->assertContains('PANDUAN_IMPORT', $sheetNames);

        $refSheet = $spreadsheet->getSheetByName('DATA_REFERENSI');
        $refItems = [];
        for ($r = 2; $r <= 20; $r++) {
            $val = (string) $refSheet->getCell("A{$r}")->getValue();
            if ($val !== '') {
                $refItems[] = $val;
            }
        }

        $this->assertTrue(collect($refItems)->contains(fn ($str) => str_contains($str, '[PRODUK] Croissant Almond')));
        $this->assertTrue(collect($refItems)->contains(fn ($str) => str_contains($str, '[BAHAN] Mentega Premium French')));

        @unlink($tmpPath);
    }

    public function test_inventory_import_dual_resolution_preview_and_execute(): void
    {
        $product = Product::create([
            'business_id' => $this->business->id,
            'category_id' => $this->productCategory->id,
            'output_unit_id' => $this->unitPcs->id,
            'name' => 'Kopi Cold Brew Botol',
            'code' => 'CLD-BRW-01',
            'base_cost' => 12000,
            'selling_price' => 28000,
            'unit' => 'pcs',
        ]);

        $material = Material::create([
            'business_id' => $this->business->id,
            'name' => 'Susu Segar UHT Barista',
            'code' => 'MAT-SSU-01',
            'unit_id' => $this->unitPcs->id,
        ]);

        // Create spreadsheet with 2 rows (1 Product, 1 Material)
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('INPUT_STOK');

        $sheet->setCellValue('A1', 'Nama Item');
        $sheet->setCellValue('B1', 'Kode / SKU');
        $sheet->setCellValue('C1', 'Lokasi / Gudang');
        $sheet->setCellValue('D1', 'Jumlah Stok');
        $sheet->setCellValue('E1', 'Satuan');
        $sheet->setCellValue('F1', 'HPP per Unit');
        $sheet->setCellValue('G1', 'Catatan');

        // Row 2: Product
        $sheet->setCellValue('A2', 'Kopi Cold Brew Botol');
        $sheet->setCellValue('B2', 'CLD-BRW-01');
        $sheet->setCellValue('C2', $this->location->name);
        $sheet->setCellValue('D2', 30);
        $sheet->setCellValue('E2', 'pcs');
        $sheet->setCellValue('F2', 12000);
        $sheet->setCellValue('G2', 'Saldo Awal Produk');

        // Row 3: Material
        $sheet->setCellValue('A3', 'Susu Segar UHT Barista');
        $sheet->setCellValue('B3', 'MAT-SSU-01');
        $sheet->setCellValue('C3', $this->location->name);
        $sheet->setCellValue('D3', 15);
        $sheet->setCellValue('E3', 'pcs');
        $sheet->setCellValue('F3', 18500);
        $sheet->setCellValue('G3', 'Saldo Awal Bahan Baku');

        $tmpFile = tempnam(sys_get_temp_dir(), 'inv_dual_') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tmpFile);

        $uploadedFile = new UploadedFile(
            $tmpFile,
            'inventory_dual_test.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        $importService = app(DataImportService::class);
        $preview = $importService->parseAndPreviewInventory($this->business, $uploadedFile);

        $this->assertEquals(2, $preview['total_rows']);
        $this->assertEquals(2, $preview['valid_count']);
        $this->assertEquals(0, $preview['error_count']);

        $row0 = $preview['rows'][0];
        $this->assertEquals('product', $row0['item_type']);
        $this->assertEquals($product->id, $row0['product_id']);
        $this->assertNull($row0['material_id']);

        $row1 = $preview['rows'][1];
        $this->assertEquals('material', $row1['item_type']);
        $this->assertEquals($material->id, $row1['material_id']);
        $this->assertNull($row1['product_id']);

        // Execute Import
        $result = $importService->executeInventoryImport(
            $this->business,
            $preview['rows'],
            'skip',
            (string) $this->owner->id
        );

        $this->assertEquals(2, $result['imported']);
        $this->assertEquals(0, $result['skipped']);

        // Verify Database stocks created
        $stockProduct = InventoryStock::where('business_id', $this->business->id)
            ->where('product_id', $product->id)
            ->first();
        $this->assertNotNull($stockProduct);
        $this->assertEquals(30, (float) $stockProduct->quantity);

        $stockMaterial = InventoryStock::where('business_id', $this->business->id)
            ->where('material_id', $material->id)
            ->first();
        $this->assertNotNull($stockMaterial);
        $this->assertEquals(15, (float) $stockMaterial->quantity);

        // Verify StockMovements created
        $movements = StockMovement::where('business_id', $this->business->id)
            ->where('movement_type', StockMovement::TYPE_INITIAL)
            ->get();
        $this->assertCount(2, $movements);

        @unlink($tmpFile);
    }

    public function test_inventory_import_compound_ident_resolution(): void
    {
        $product = Product::create([
            'business_id' => $this->business->id,
            'category_id' => $this->productCategory->id,
            'output_unit_id' => $this->unitPcs->id,
            'name' => 'Matcha Latte Ice',
            'code' => 'MTC-ICE-01',
            'base_cost' => 14000,
            'selling_price' => 30000,
            'unit' => 'pcs',
        ]);

        // Spreadsheet with compound item name format e.g. "[PRODUK] Matcha Latte Ice (MTC-ICE-01)"
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('INPUT_STOK');

        $sheet->setCellValue('A1', 'Nama Item');
        $sheet->setCellValue('B1', 'Kode / SKU');
        $sheet->setCellValue('C1', 'Lokasi / Gudang');
        $sheet->setCellValue('D1', 'Jumlah Stok');
        $sheet->setCellValue('E1', 'Satuan');

        $sheet->setCellValue('A2', '[PRODUK] Matcha Latte Ice (MTC-ICE-01)');
        $sheet->setCellValue('B2', ''); // Empty SKU, should resolve from compound string!
        $sheet->setCellValue('C2', $this->location->name);
        $sheet->setCellValue('D2', 40);
        $sheet->setCellValue('E2', 'pcs');

        $tmpFile = tempnam(sys_get_temp_dir(), 'inv_cmp_') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tmpFile);

        $uploadedFile = new UploadedFile(
            $tmpFile,
            'inventory_cmp_test.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        $importService = app(DataImportService::class);
        $preview = $importService->parseAndPreviewInventory($this->business, $uploadedFile);

        $this->assertEquals(1, $preview['valid_count']);
        $this->assertEquals('valid', $preview['rows'][0]['status']);
        $this->assertEquals('product', $preview['rows'][0]['item_type']);
        $this->assertEquals($product->id, $preview['rows'][0]['product_id']);

        @unlink($tmpFile);
    }
}
