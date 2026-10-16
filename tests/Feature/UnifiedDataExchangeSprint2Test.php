<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Import\DataExchangeService;
use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\BusinessSubscription;
use App\Models\ChartOfAccount;
use App\Models\Customer;
use App\Models\InventoryStock;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Location;
use App\Models\Material;
use App\Models\Product;
use App\Models\Role;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use App\Models\Supplier;
use App\Models\SupplierInvoice;
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

final class UnifiedDataExchangeSprint2Test extends TestCase
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
            'name' => 'Data Exchange Sprint 2 Owner',
            'email' => 'exchange-sprint2@cooca.test',
            'password' => 'password123',
        ]);

        $this->business = Business::create([
            'name' => 'PT Manufaktur & Distribusi Maju',
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
            'name' => 'Gudang Ekspedisi',
            'code' => 'WH-EXP',
            'type' => 'warehouse',
            'is_default' => true,
        ]);

        $this->unitPcs = Unit::firstOrCreate(
            ['code' => 'pcs'],
            ['name' => 'Pieces', 'category' => 'quantity', 'is_base' => true]
        );
    }

    /**
     * Helper to write spreadsheet to temporary file for upload testing.
     */
    private function createUploadedFile(Spreadsheet $spreadsheet, string $filename = 'import_test.xlsx'): UploadedFile
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'cooca_test_') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return new UploadedFile(
            $tempPath,
            $filename,
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );
    }

    /**
     * Test 2.1: Stock Opname blank counting sheet export and physical upload cycle.
     */
    public function test_stock_opname_blank_export_and_import_cycle(): void
    {
        $this->actingAs($this->owner);

        // 1. Setup sample material and product with existing stock
        $material = Material::create([
            'business_id' => $this->business->id,
            'name' => 'Kain Katun Premium',
            'code' => 'MAT-KATUN-01',
            'unit_id' => $this->unitPcs->id,
        ]);

        $product = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Kemeja Katun Putih',
            'code' => 'PRD-KEMEJA-01',
            'output_unit_id' => $this->unitPcs->id,
            'selling_price' => 150000,
        ]);

        InventoryStock::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'material_id' => $material->id,
            'quantity' => 50,
        ]);

        InventoryStock::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'product_id' => $product->id,
            'quantity' => 20,
        ]);

        // 2. Test Blank Excel Export
        $exportResponse = $this->get(route('inventory.opnames.export-blank', ['location_id' => $this->location->id]));
        $exportResponse->assertOk();
        $this->assertStringContainsString('spreadsheetml.sheet', $exportResponse->headers->get('content-type', ''));

        // 3. Create Physical Count Excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('LEMBAR_OPNAME_FISIK');
        $sheet->fromArray([
            ['KODE/SKU', 'NAMA ITEM', 'TIPE (BAHAN/PRODUK)', 'SATUAN', 'STOK SISTEM', 'HITUNGAN FISIK (ISI DI SINI)', 'CATATAN AUDITOR'],
            ['MAT-KATUN-01', 'Kain Katun Premium', 'BAHAN', 'm', 50, 55, 'Surplus 5 meter'],
            ['PRD-KEMEJA-01', 'Kemeja Katun Putih', 'PRODUK', 'pcs', 20, 18, 'Hilang 2 pcs saat display'],
        ]);

        $uploadFile = $this->createUploadedFile($spreadsheet, 'opname_fisik_test.xlsx');

        // 4. Test Preview
        $previewResponse = $this->postJson(route('inventory.opnames.import-preview'), [
            'file' => $uploadFile,
            'location_id' => $this->location->id,
        ]);

        $previewResponse->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.total_rows', 2)
            ->assertJsonPath('data.surplus_count', 1)
            ->assertJsonPath('data.deficit_count', 1);

        $previewData = $previewResponse->json('data');

        // 5. Test Execute Commit
        $executeResponse = $this->postJson(route('inventory.opnames.import-execute'), [
            'location_id' => $this->location->id,
            'notes' => 'Opname Fisik Akhir Bulan',
            'rows' => $previewData['rows'],
        ]);

        $executeResponse->assertOk()
            ->assertJsonPath('success', true);

        // 6. Assert StockOpname and StockOpnameItem in database
        $this->assertDatabaseHas('stock_opnames', [
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'status' => 'completed',
        ]);

        $opname = StockOpname::where('business_id', $this->business->id)->firstOrFail();
        $this->assertEquals(2, $opname->items()->count());

        $this->assertDatabaseHas('stock_opname_items', [
            'stock_opname_id' => $opname->id,
            'material_id' => $material->id,
            'system_quantity' => 50,
            'physical_quantity' => 55,
            'difference_quantity' => 5,
        ]);

        $this->assertDatabaseHas('stock_opname_items', [
            'stock_opname_id' => $opname->id,
            'product_id' => $product->id,
            'system_quantity' => 20,
            'physical_quantity' => 18,
            'difference_quantity' => -2,
        ]);
    }

    /**
     * Test 2.2: Chart of Accounts (COA) template, export, preview, and execution.
     */
    public function test_coa_template_export_and_import_cycle(): void
    {
        $this->actingAs($this->owner);

        // 1. Test COA Template Download
        $templateResponse = $this->get(route('finance.coa.template'));
        $templateResponse->assertOk();

        // 2. Test COA Export
        $exportResponse = $this->get(route('finance.coa.export'));
        $exportResponse->assertOk();

        // 3. Create Sample COA Excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('BAGAN_AKUN');
        $sheet->fromArray([
            ['KODE AKUN', 'NAMA AKUN', 'KLASIFIKASI', 'POSISI NORMAL', 'KODE INDUK (PARENT)', 'KETERANGAN'],
            ['1100', 'Kas & Setara Kas', 'asset', 'debit', '', 'Header Kas'],
            ['1101', 'Kas Operasional Kantor', 'asset', 'debit', '1100', 'Sub Kas'],
            ['2100', 'Kewajiban Jangka Pendek', 'liability', 'credit', '', 'Header Kewajiban'],
        ]);

        $uploadFile = $this->createUploadedFile($spreadsheet, 'coa_test.xlsx');

        // 4. Test Preview
        $previewResponse = $this->postJson(route('finance.coa.import-preview'), [
            'file' => $uploadFile,
        ]);

        $previewResponse->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.total_rows', 3)
            ->assertJsonPath('data.valid_count', 3);

        $previewRows = $previewResponse->json('data.rows');

        // 5. Test Execute Commit
        $executeResponse = $this->postJson(route('finance.coa.import-execute'), [
            'rows' => $previewRows,
            'duplicate_strategy' => 'skip',
        ]);

        $executeResponse->assertOk()
            ->assertJsonPath('success', true);

        // 6. Assert Accounts created with hierarchy
        $this->assertDatabaseHas('chart_of_accounts', [
            'business_id' => $this->business->id,
            'code' => '1100',
            'name' => 'Kas & Setara Kas',
            'type' => 'asset',
            'normal_balance' => 'debit',
        ]);

        $parent = ChartOfAccount::where('business_id', $this->business->id)->where('code', '1100')->firstOrFail();

        $this->assertDatabaseHas('chart_of_accounts', [
            'business_id' => $this->business->id,
            'code' => '1101',
            'name' => 'Kas Operasional Kantor',
            'parent_id' => $parent->id,
        ]);
    }

    /**
     * Test 2.3: Opening Balance Sheet Journal Import with double-entry balancing validation.
     */
    public function test_opening_balance_sheet_balanced_and_imbalance_block(): void
    {
        $this->actingAs($this->owner);

        // 1. Template Download
        $templateResponse = $this->get(route('finance.balance-sheet.opening-template'));
        $templateResponse->assertOk();

        // Seed basic accounts
        $kas = ChartOfAccount::create([
            'business_id' => $this->business->id,
            'code' => '1001',
            'name' => 'Kas Besar',
            'type' => 'asset',
            'normal_balance' => 'debit',
        ]);

        $bank = ChartOfAccount::create([
            'business_id' => $this->business->id,
            'code' => '1002',
            'name' => 'Bank BCA Operasional',
            'type' => 'asset',
            'normal_balance' => 'debit',
        ]);

        $modal = ChartOfAccount::create([
            'business_id' => $this->business->id,
            'code' => '3001',
            'name' => 'Modal Disetor Pemilik',
            'type' => 'equity',
            'normal_balance' => 'credit',
        ]);

        // 2. Test Imbalanced Excel (Debet 15.000.000 vs Kredit 10.000.000 -> Diff 5.000.000)
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('SALDO_AWAL_NERACA');
        $sheet->fromArray([
            ['KODE AKUN', 'NAMA AKUN', 'KLASIFIKASI', 'DEBET (RP)', 'KREDIT (RP)', 'CATATAN'],
            ['1001', 'Kas Besar', 'asset', 5000000, 0, ''],
            ['1002', 'Bank BCA Operasional', 'asset', 10000000, 0, ''],
            ['3001', 'Modal Disetor Pemilik', 'equity', 0, 10000000, ''],
        ]);

        $uploadFileImbalanced = $this->createUploadedFile($spreadsheet, 'opening_imbalanced.xlsx');

        $imbalancePreview = $this->postJson(route('finance.balance-sheet.opening-preview'), [
            'file' => $uploadFileImbalanced,
            'as_of_date' => '2026-01-01',
        ]);

        $imbalancePreview->assertOk()
            ->assertJsonPath('data.is_balanced', false)
            ->assertJsonPath('data.can_proceed', false)
            ->assertJsonPath('data.difference', 5000000);

        // Attempting to execute with imbalanced rows should be blocked
        $blockedResponse = $this->postJson(route('finance.balance-sheet.opening-execute'), [
            'as_of_date' => '2026-01-01',
            'rows' => $imbalancePreview->json('data.rows'),
        ]);

        $blockedResponse->assertStatus(422)
            ->assertJsonPath('success', false);

        // 3. Test Balanced Excel (Debet 15.000.000 == Kredit 15.000.000)
        $sheet->getCell('E4')->setValue(15000000); // Set Modal Kredit to 15.000.000

        $uploadFileBalanced = $this->createUploadedFile($spreadsheet, 'opening_balanced.xlsx');

        $balancePreview = $this->postJson(route('finance.balance-sheet.opening-preview'), [
            'file' => $uploadFileBalanced,
            'as_of_date' => '2026-01-01',
        ]);

        $balancePreview->assertOk()
            ->assertJsonPath('data.is_balanced', true)
            ->assertJsonPath('data.can_proceed', true)
            ->assertJsonPath('data.difference', 0);

        // Execute Balanced Import
        $balancedExecute = $this->postJson(route('finance.balance-sheet.opening-execute'), [
            'as_of_date' => '2026-01-01',
            'rows' => $balancePreview->json('data.rows'),
        ]);

        $balancedExecute->assertOk()
            ->assertJsonPath('success', true);

        // Assert Opening Balance Journal Entry was created
        $this->assertDatabaseHas('journal_entries', [
            'business_id' => $this->business->id,
            'reference_type' => JournalEntry::REF_MANUAL,
        ]);

        $journal = JournalEntry::where('business_id', $this->business->id)->firstOrFail();
        $this->assertEquals(15000000, $journal->total_debit);
        $this->assertEquals(15000000, $journal->total_credit);
        $this->assertEquals(15000000, $journal->lines()->where('type', JournalEntryLine::TYPE_DEBIT)->sum('amount'));
        $this->assertEquals(15000000, $journal->lines()->where('type', JournalEntryLine::TYPE_CREDIT)->sum('amount'));
    }

    /**
     * Test 2.4: Receivables (AR) and Payables (AP) cut-off open invoice import and export.
     */
    public function test_receivables_and_payables_import_and_export(): void
    {
        $this->actingAs($this->owner);

        // === A. RECEIVABLES (AR) ===
        $this->get(route('finance.receivables.template'))->assertOk();
        $this->get(route('finance.receivables.export'))->assertOk();

        $arSpreadsheet = new Spreadsheet();
        $arSheet = $arSpreadsheet->getActiveSheet();
        $arSheet->setTitle('SALDO_AWAL_PIUTANG');
        $arSheet->fromArray([
            ['NOMOR FAKTUR', 'NAMA PELANGGAN', 'TELEPON PELANGGAN', 'TANGGAL FAKTUR', 'JATUH TEMPO', 'TOTAL TAGIHAN (RP)', 'SISA PIUTANG (RP)', 'CATATAN'],
            ['INV-AR-001', 'Toko Berkah Abadi', '08123456789', '2026-01-10', '2026-01-30', 2500000, 2500000, 'Invoice tempo 20 hari'],
            ['INV-AR-002', 'Resto Rasa Nusantara', '08198765432', '2026-01-15', '2026-02-15', 5000000, 3000000, 'DP 2jt sudah diterima'],
        ]);

        $arFile = $this->createUploadedFile($arSpreadsheet, 'ar_test.xlsx');

        $arPreview = $this->postJson(route('finance.receivables.import-preview'), ['file' => $arFile]);
        $arPreview->assertOk()
            ->assertJsonPath('data.total_rows', 2)
            ->assertJsonPath('data.valid_count', 2)
            ->assertJsonPath('data.total_balance_due', 5500000);

        $arExecute = $this->postJson(route('finance.receivables.import-execute'), [
            'rows' => $arPreview->json('data.rows'),
            'duplicate_strategy' => 'skip',
        ]);
        $arExecute->assertOk()->assertJsonPath('success', true);

        // Assert Invoices and Customers exist
        $this->assertDatabaseHas('customers', [
            'business_id' => $this->business->id,
            'name' => 'Toko Berkah Abadi',
        ]);

        $this->assertDatabaseHas('invoices', [
            'business_id' => $this->business->id,
            'invoice_number' => 'INV-AR-001',
            'balance_due' => 2500000,
            'status' => Invoice::STATUS_UNPAID,
        ]);

        $this->assertDatabaseHas('invoices', [
            'business_id' => $this->business->id,
            'invoice_number' => 'INV-AR-002',
            'balance_due' => 3000000,
            'status' => Invoice::STATUS_PARTIALLY_PAID,
        ]);

        // === B. PAYABLES (AP) ===
        $this->get(route('finance.payables.template'))->assertOk();
        $this->get(route('finance.payables.export'))->assertOk();

        $apSpreadsheet = new Spreadsheet();
        $apSheet = $apSpreadsheet->getActiveSheet();
        $apSheet->setTitle('SALDO_AWAL_HUTANG');
        $apSheet->fromArray([
            ['NOMOR TAGIHAN', 'NAMA PEMASOK', 'TELEPON PEMASOK', 'TANGGAL TAGIHAN', 'JATUH TEMPO', 'TOTAL TAGIHAN (RP)', 'SISA HUTANG (RP)', 'CATATAN'],
            ['BILL-AP-001', 'PT Benang Jaya Makmur', '08129998888', '2026-01-05', '2026-01-25', 8000000, 8000000, 'Hutang bahan benang'],
        ]);

        $apFile = $this->createUploadedFile($apSpreadsheet, 'ap_test.xlsx');

        $apPreview = $this->postJson(route('finance.payables.import-preview'), ['file' => $apFile]);
        $apPreview->assertOk()
            ->assertJsonPath('data.total_rows', 1)
            ->assertJsonPath('data.valid_count', 1)
            ->assertJsonPath('data.total_balance_due', 8000000);

        $apExecute = $this->postJson(route('finance.payables.import-execute'), [
            'rows' => $apPreview->json('data.rows'),
            'duplicate_strategy' => 'skip',
        ]);
        $apExecute->assertOk()->assertJsonPath('success', true);

        // Assert Supplier and SupplierInvoice created
        $this->assertDatabaseHas('suppliers', [
            'business_id' => $this->business->id,
            'name' => 'PT Benang Jaya Makmur',
        ]);

        $this->assertDatabaseHas('supplier_invoices', [
            'business_id' => $this->business->id,
            'invoice_number' => 'BILL-AP-001',
            'balance_due' => 8000000,
            'status' => SupplierInvoice::STATUS_UNPAID,
        ]);
    }
}
