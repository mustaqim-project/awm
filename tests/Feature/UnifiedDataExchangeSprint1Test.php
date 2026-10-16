<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Import\DataExchangeService;
use App\Domain\Import\DataImportService;
use App\Models\BomHeader;
use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\BusinessSubscription;
use App\Models\CostModel;
use App\Models\Customer;
use App\Models\Location;
use App\Models\Material;
use App\Models\MaterialCategory;
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
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

final class UnifiedDataExchangeSprint1Test extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Business $business;
    private Location $location;
    private Unit $unitPcs;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RbacSeeder::class);

        $this->owner = User::create([
            'name' => 'Data Exchange Owner',
            'email' => 'exchange-owner@cooca.test',
            'password' => 'password123',
        ]);

        $this->business = Business::create([
            'name' => 'PT Artisan Bakery & Cafe',
            'currency' => 'IDR',
        ]);

        $ownerRole = Role::where('slug', 'owner')->firstOrFail();
        $membership = BusinessMembership::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'user_id' => $this->owner->id,
            'role' => 'owner',
            'role_id' => $ownerRole->id,
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
            'name' => 'Gudang Pusat',
            'code' => 'WH-PST',
            'type' => 'warehouse',
            'is_default' => true,
        ]);

        $this->unitPcs = Unit::firstOrCreate(
            ['code' => 'pcs'],
            ['name' => 'Pieces', 'category' => 'quantity', 'is_base' => true]
        );
    }

    /**
     * 1. Test Product Template generation with BOM/Recipe dropdown and named range
     */
    public function test_product_template_generation_includes_bom_dropdown_and_master_sheets(): void
    {
        $cat = ProductCategory::create([
            'business_id' => $this->business->id,
            'name' => 'Pastry & Bakery',
            'slug' => 'pastry-bakery',
        ]);

        $prod = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Croissant Butter Premium',
            'sku' => 'CROIS-01',
            'category_id' => $cat->id,
            'output_unit_id' => $this->unitPcs->id,
            'selling_price' => 25000,
        ]);

        $costModel = CostModel::create([
            'business_id' => $this->business->id,
            'product_id' => $prod->id,
            'name' => 'Model HPP Croissant',
            'method' => CostModel::METHOD_RECIPE_BOM,
            'is_active' => true,
        ]);

        $bom = BomHeader::create([
            'cost_model_id' => $costModel->id,
            'type' => BomHeader::TYPE_RECIPE,
            'name' => 'Resep Croissant Butter Premium',
            'level' => 1,
        ]);

        $response = $this->actingAs($this->owner)->get(route('import.products.template', ['format' => 'xlsx']));
        $response->assertStatus(200);
        $this->assertStringContainsString('spreadsheetml.sheet', (string) $response->headers->get('Content-Type'));

        $path = tempnam(sys_get_temp_dir(), 'test_prod_tmpl_') . '.xlsx';
        file_put_contents($path, $response->streamedContent());
        $workbook = IOFactory::load($path);
        unlink($path);

        $this->assertSame(['INPUT_PRODUK', 'DATA_REFERENSI', 'PANDUAN_IMPORT'], $workbook->getSheetNames());
        
        $templateSheet = $workbook->getSheetByName('INPUT_PRODUK');
        $this->assertSame('Nama Produk *', $templateSheet->getCell('A1')->getValue());
        $this->assertSame('Kategori *', $templateSheet->getCell('D1')->getValue());
        $this->assertSame('Satuan Dasar *', $templateSheet->getCell('E1')->getValue());
        $this->assertSame('Pilihan Resep BOM', $templateSheet->getCell('I1')->getValue());

        // Check Named Ranges
        $definedNames = array_map(fn($nr) => $nr->getName(), $workbook->getDefinedNames());
        $this->assertContains('RefBomTersedia', $definedNames);
        $this->assertContains('RefKategori', $definedNames);
        $this->assertContains('RefSatuan', $definedNames);

        // Check BOM dropdown validation formula in Column I
        $validationI = $templateSheet->getDataValidation('I3');
        $this->assertNotNull($validationI);
        $this->assertSame('=RefBomTersedia', $validationI->getFormula1());

        // Verify master lookup sheet contains the seeded BOM
        $masterSheet = $workbook->getSheetByName('DATA_REFERENSI');
        $foundBom = false;
        for ($r = 2; $r <= 20; $r++) {
            $val = (string) $masterSheet->getCell("C{$r}")->getValue();
            if (str_contains($val, 'Croissant Butter Premium')) {
                $foundBom = true;
                break;
            }
        }
        $this->assertTrue($foundBom, 'Seeded BOM should be present in DATA_REFERENSI column C');
    }

    /**
     * 2. Test Customer Template & Export
     */
    public function test_customer_template_and_export(): void
    {
        // Download Template
        $response = $this->actingAs($this->owner)->get(route('customers.template'));
        $response->assertStatus(200);
        $this->assertStringContainsString('spreadsheetml.sheet', (string) $response->headers->get('Content-Type'));

        $path = tempnam(sys_get_temp_dir(), 'test_cust_tmpl_') . '.xlsx';
        file_put_contents($path, $response->streamedContent());
        $workbook = IOFactory::load($path);
        unlink($path);

        $this->assertSame(['INPUT_PELANGGAN', 'DATA_REFERENSI', 'PANDUAN_PELANGGAN'], $workbook->getSheetNames());

        // Seed customer and test export
        Customer::create([
            'business_id' => $this->business->id,
            'name' => 'Budi Santoso',
            'code' => 'CUST-001',
            'phone' => '081234567890',
            'email' => 'budi@santoso.id',
            'membership_tier' => 'Gold VIP',
        ]);

        $exportResponse = $this->actingAs($this->owner)->get(route('customers.export'));
        $exportResponse->assertStatus(200);
        $this->assertStringContainsString('spreadsheetml.sheet', (string) $exportResponse->headers->get('Content-Type'));

        $exportPath = tempnam(sys_get_temp_dir(), 'test_cust_exp_') . '.xlsx';
        file_put_contents($exportPath, $exportResponse->streamedContent());
        $exportWb = IOFactory::load($exportPath);
        unlink($exportPath);

        $exportSheet = $exportWb->getActiveSheet();
        $this->assertSame('Budi Santoso', $exportSheet->getCell('B2')->getValue());
        $this->assertSame('081234567890', $exportSheet->getCell('C2')->getValue());
    }

    /**
     * 3. Test Customer Import: Interactive Pre-Commit Preview & Atomic Commit with Duplicate Strategies
     */
    public function test_customer_import_preview_and_atomic_execution(): void
    {
        // Build temporary Excel file with 2 rows: 1 valid, 1 invalid (empty name)
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('INPUT_PELANGGAN');
        $sheet->fromArray([
            ['Nama Pelanggan *', 'Nomor HP / WhatsApp *', 'Email', 'Perusahaan / Instansi', 'Kategori / Tier Diskon', 'Limit Piutang (Rp)', 'Termin Pembayaran (Hari)', 'Alamat Lengkap', 'Kota', 'NPWP'],
            ['Ibu Ratna Sari', '081299887766', 'ratna@sari.co.id', 'PT Ratna Indah', 'Silver Regular', '5000000', '30', 'Jl. Merdeka No 10', 'Jakarta', '01.234.567.8-901.000'],
            ['', '081233445566', 'empty@mail.com', '', '', '0', '0', '', '', ''],
        ]);

        $tempPath = tempnam(sys_get_temp_dir(), 'test_cust_imp_') . '.xlsx';
        (new Xlsx($spreadsheet))->save($tempPath);

        $file = new UploadedFile($tempPath, 'pelanggan.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        // 1. Preview
        $previewRes = $this->actingAs($this->owner)->postJson(route('customers.import.preview'), [
            'file' => $file,
        ]);

        $previewRes->assertStatus(200);
        $previewRes->assertJson([
            'success' => true,
            'data' => [
                'total_rows' => 2,
                'valid_count' => 1,
                'duplicate_count' => 0,
                'error_count' => 1,
            ],
        ]);

        $rows = $previewRes->json('data.rows');
        $this->assertSame('valid', $rows[0]['status']);
        $this->assertSame('error', $rows[1]['status']);

        // 2. Execute Commit (with skip strategy)
        $executeRes = $this->actingAs($this->owner)->postJson(route('customers.import.execute'), [
            'rows' => $rows,
            'duplicate_strategy' => 'skip',
        ]);

        $executeRes->assertStatus(200);
        $executeRes->assertJson([
            'success' => true,
            'data' => [
                'imported' => 1,
                'updated' => 0,
                'skipped' => 1,
            ],
        ]);

        $this->assertDatabaseHas('customers', [
            'business_id' => $this->business->id,
            'name' => 'Ibu Ratna Sari',
            'phone' => '081299887766',
            'email' => 'ratna@sari.co.id',
        ]);

        // 3. Test Duplicate Detection: Re-upload the same customer phone
        $spreadsheet2 = new Spreadsheet();
        $sheet2 = $spreadsheet2->getActiveSheet();
        $sheet2->setTitle('INPUT_PELANGGAN');
        $sheet2->fromArray([
            ['Nama Pelanggan *', 'Nomor HP / WhatsApp *', 'Email', 'Perusahaan / Instansi', 'Kategori / Tier Diskon', 'Limit Piutang (Rp)', 'Termin Pembayaran (Hari)', 'Alamat Lengkap', 'Kota', 'NPWP'],
            ['Ibu Ratna Sari Updated', '081299887766', 'ratna@sari.co.id', 'PT Ratna Indah Baru', 'Gold VIP', '10000000', '45', 'Jl. Merdeka Baru No 20', 'Bandung', ''],
        ]);
        $tempPath2 = tempnam(sys_get_temp_dir(), 'test_cust_dup_') . '.xlsx';
        (new Xlsx($spreadsheet2))->save($tempPath2);

        $file2 = new UploadedFile($tempPath2, 'pelanggan_dup.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
        $previewDupRes = $this->actingAs($this->owner)->postJson(route('customers.import.preview'), [
            'file' => $file2,
        ]);

        $previewDupRes->assertStatus(200);
        $previewDupRes->assertJson([
            'success' => true,
            'data' => [
                'total_rows' => 1,
                'valid_count' => 0,
                'duplicate_count' => 1,
                'error_count' => 0,
            ],
        ]);

        // Commit with 'update' strategy
        $executeDupRes = $this->actingAs($this->owner)->postJson(route('customers.import.execute'), [
            'rows' => $previewDupRes->json('data.rows'),
            'duplicate_strategy' => 'update',
        ]);

        $executeDupRes->assertStatus(200);
        $executeDupRes->assertJson([
            'success' => true,
            'data' => [
                'imported' => 0,
                'updated' => 1,
                'skipped' => 0,
            ],
        ]);

        $this->assertDatabaseHas('customers', [
            'business_id' => $this->business->id,
            'name' => 'Ibu Ratna Sari Updated',
            'payment_terms_days' => 45,
        ]);

        unlink($tempPath);
        unlink($tempPath2);
    }

    /**
     * 4. Test Supplier Template, Export, Preview, and Execution
     */
    public function test_supplier_template_export_and_import(): void
    {
        // Template
        $templateRes = $this->actingAs($this->owner)->get(route('suppliers.template'));
        $templateRes->assertStatus(200);

        // Seed Supplier and Export
        Supplier::create([
            'business_id' => $this->business->id,
            'name' => 'PT Gandum Sejahtera',
            'contact_person' => 'Pak Hendra',
            'phone' => '081122334455',
            'payment_terms_days' => 30,
        ]);

        $exportRes = $this->actingAs($this->owner)->get(route('suppliers.export'));
        $exportRes->assertStatus(200);
        $this->assertStringContainsString('spreadsheetml.sheet', (string) $exportRes->headers->get('Content-Type'));

        // Import Supplier via Excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('INPUT_SUPPLIER');
        $sheet->fromArray([
            ['Nama Supplier / Perusahaan *', 'Kode Supplier', 'Nama Kontak PIC', 'Nomor Telepon / WA *', 'Email', 'Termin Pembayaran (Hari)', 'Nama Bank', 'Nomor Rekening', 'Atas Nama Rekening', 'Alamat Kantor / Gudang'],
            ['UD Susu Sapi Murni', 'SUP-SUSU', 'Bu Dewi', '081344556677', 'dewi@susu.com', '14', 'BCA', '1234567890', 'Dewi Susu', 'Lembang Bandung'],
            ['', '', 'Invalid', '', '', '0', '', '', '', 'Nama kosong error'],
        ]);

        $tempPath = tempnam(sys_get_temp_dir(), 'test_sup_imp_') . '.xlsx';
        (new Xlsx($spreadsheet))->save($tempPath);
        $file = new UploadedFile($tempPath, 'pemasok.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        // Preview
        $previewRes = $this->actingAs($this->owner)->postJson(route('suppliers.import.preview'), [
            'file' => $file,
        ]);
        $previewRes->assertStatus(200);
        $previewRes->assertJson([
            'success' => true,
            'data' => [
                'total_rows' => 2,
                'valid_count' => 1,
                'error_count' => 1,
            ],
        ]);

        // Commit
        $execRes = $this->actingAs($this->owner)->postJson(route('suppliers.import.execute'), [
            'rows' => $previewRes->json('data.rows'),
            'duplicate_strategy' => 'skip',
        ]);
        $execRes->assertStatus(200);
        $this->assertSame(1, $execRes->json('data.imported'));

        $this->assertDatabaseHas('suppliers', [
            'business_id' => $this->business->id,
            'name' => 'UD Susu Sapi Murni',
            'contact_person' => 'Bu Dewi',
            'payment_terms_days' => 14,
            'bank_name' => 'BCA',
        ]);

        unlink($tempPath);
    }

    /**
     * 5. Test Product Export & Material Export
     */
    public function test_product_and_material_exports(): void
    {
        $cat = ProductCategory::create([
            'business_id' => $this->business->id,
            'name' => 'Minuman Kopi',
            'slug' => 'minuman-kopi',
        ]);

        Product::create([
            'business_id' => $this->business->id,
            'name' => 'Espresso Single Shot',
            'sku' => 'ESP-01',
            'category_id' => $cat->id,
            'output_unit_id' => $this->unitPcs->id,
            'selling_price' => 22000,
            'base_cost' => 8500,
            'min_stock' => 10,
        ]);

        $matCat = MaterialCategory::create([
            'business_id' => $this->business->id,
            'name' => 'Biji Kopi',
            'slug' => 'biji-kopi',
        ]);

        Material::create([
            'business_id' => $this->business->id,
            'name' => 'Biji Kopi Arabika Gayo',
            'code' => 'MAT-GY-01',
            'category_id' => $matCat->id,
            'unit_id' => $this->unitPcs->id,
            'purchase_price' => 120000,
        ]);

        // Product Export
        $prodRes = $this->actingAs($this->owner)->get(route('products.export'));
        $prodRes->assertStatus(200);
        $this->assertStringContainsString('spreadsheetml.sheet', (string) $prodRes->headers->get('Content-Type'));

        // Material Export
        $matRes = $this->actingAs($this->owner)->get(route('materials.export'));
        $matRes->assertStatus(200);
        $this->assertStringContainsString('spreadsheetml.sheet', (string) $matRes->headers->get('Content-Type'));
    }

    /**
     * 6. Test Inventory Initial Stock Dual-Resolution (Product & Material)
     */
    public function test_inventory_import_supports_both_product_and_material(): void
    {
        $importService = app(DataImportService::class);

        // 1. Finished Product
        $product = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Roti Sourdough Country Loaf',
            'sku' => 'PRD-SRD-01',
            'output_unit_id' => $this->unitPcs->id,
            'selling_price' => 45000,
            'base_cost' => 18000,
        ]);

        // 2. Raw Material
        $material = Material::create([
            'business_id' => $this->business->id,
            'name' => 'Ragi Instan Saf-Instant',
            'code' => 'MAT-RAGI-01',
            'unit_id' => $this->unitPcs->id,
            'purchase_price' => 35000,
        ]);

        // Prepare Excel spreadsheet with official headers
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Stok Awal');
        $sheet->fromArray([
            ['Nama Bahan Baku *', 'Kode / SKU Bahan', 'Lokasi / Outlet / Gudang *', 'Jumlah Stok *', 'Satuan *'],
            ['Roti Sourdough Country Loaf', 'PRD-SRD-01', 'Gudang Pusat', 25, 'pcs'],
            ['Ragi Instan Saf-Instant', 'MAT-RAGI-01', 'Gudang Pusat', 100, 'pcs'],
        ]);

        $tempPath = tempnam(sys_get_temp_dir(), 'test_inv_dual_') . '.xlsx';
        (new Xlsx($spreadsheet))->save($tempPath);
        $file = new UploadedFile($tempPath, 'stok_awal.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        // Preview should resolve row 1 to product and row 2 to material
        $preview = $importService->parseAndPreviewInventory($this->business, $file);

        $this->assertSame(2, $preview['total_rows']);
        $this->assertSame(2, $preview['valid_count']);
        $this->assertSame(0, $preview['error_count']);

        $row1 = $preview['rows'][0];
        $this->assertSame('valid', $row1['status']);
        $this->assertSame('product', $row1['item_type']);
        $this->assertEquals($product->id, $row1['product_id']);
        $this->assertNull($row1['material_id']);

        $row2 = $preview['rows'][1];
        $this->assertSame('valid', $row2['status']);
        $this->assertSame('material', $row2['item_type']);
        $this->assertEquals($material->id, $row2['material_id']);
        $this->assertNull($row2['product_id']);

        // Execute import
        $result = $importService->executeInventoryImport($this->business, $preview['rows']);
        $this->assertSame(2, $result['imported']);
        $this->assertSame(0, $result['skipped']);

        // Assert StockMovement entries created for BOTH Product and Material with quantity_change
        $this->assertDatabaseHas('stock_movements', [
            'business_id' => $this->business->id,
            'product_id' => $product->id,
            'location_id' => $this->location->id,
            'quantity_change' => 25,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'business_id' => $this->business->id,
            'material_id' => $material->id,
            'location_id' => $this->location->id,
            'quantity_change' => 100,
        ]);

        unlink($tempPath);
    }
}
