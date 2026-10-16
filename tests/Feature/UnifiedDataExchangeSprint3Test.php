<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Import\DataExchangeService;
use App\Models\Attendance;
use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\BusinessSubscription;
use App\Models\CommerceOrder;
use App\Models\CommerceOrderItem;
use App\Models\Location;
use App\Models\MarketplaceAccount;
use App\Models\MarketplaceOrder;
use App\Models\Product;
use App\Models\Role;
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

final class UnifiedDataExchangeSprint3Test extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Business $business;
    private Location $location;
    private BusinessMembership $ownerMembership;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RbacSeeder::class);

        $this->owner = User::create([
            'name' => 'Data Exchange Sprint 3 Owner',
            'email' => 'exchange-sprint3@cooca.test',
            'password' => 'password123',
            'phone' => '081122334455',
        ]);

        $this->business = Business::create([
            'name' => 'PT Cooca Retail Multi-Channel',
            'currency' => 'IDR',
        ]);

        $ownerRole = Role::where('slug', 'owner')->firstOrFail();
        $this->ownerMembership = BusinessMembership::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'user_id' => $this->owner->id,
            'role' => 'owner',
            'role_id' => $ownerRole->id,
            'job_title' => 'Direktur Utama',
            'base_salary' => 15000000,
        ]);

        $this->owner->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business, $this->ownerMembership);

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
            'name' => 'Kantor Pusat Jakarta',
            'code' => 'HQ-JKT',
            'type' => 'office',
            'is_default' => true,
        ]);
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
     * Test 3.1: Employee template generation and export structure.
     */
    public function test_employee_template_generation_and_export_structure(): void
    {
        $this->actingAs($this->owner);

        // 1. Template generation
        $resTemplate = $this->get(route('hrm.employees.template'));
        $resTemplate->assertStatus(200);
        $resTemplate->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        // 2. Export employees
        $resExport = $this->get(route('hrm.employees.export'));
        $resExport->assertStatus(200);
        $resExport->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    /**
     * Test 3.2: Employee import preview validation and duplicate detection.
     */
    public function test_employee_import_preview_validation_and_duplicate_detection(): void
    {
        $this->actingAs($this->owner);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Master Karyawan');

        // Headers matching DataExchangeService::generateEmployeeTemplate
        $headers = [
            'Nama Lengkap*',
            'Email*',
            'No. WhatsApp / HP',
            'Role (staff/cashier/manager/accountant/warehouse/kitchen/operator)',
            'Jabatan / Posisi',
            'Tipe Kerja (full_time/part_time/contract/intern)',
            'Gaji Pokok (IDR)',
            'Status PTKP (TK/0, K/0, K/1, K/2, K/3)',
            'Lokasi / Cabang Penempatan',
            'Nama Bank',
            'Nomor Rekening',
            'NIK KTP (16 Digit)',
        ];
        $sheet->fromArray($headers, null, 'A1');

        $rows = [
            // Row 2: Valid new staff
            [
                'Budi Santoso',
                'budi.santoso@cooca.test',
                '081234567890',
                'staff',
                'Senior Barista',
                'full_time',
                4500000,
                'TK/0',
                'Kantor Pusat Jakarta',
                'BCA',
                '1234567890',
                '3171012345670001',
            ],
            // Row 3: Duplicate (owner email already registered in system)
            [
                'Duplikat Owner',
                'exchange-sprint3@cooca.test',
                '081122334455',
                'manager',
                'Operational Lead',
                'full_time',
                8000000,
                'K/1',
                'Kantor Pusat Jakarta',
                'Mandiri',
                '9876543210',
                '',
            ],
            // Row 4: Error (missing name, empty email)
            [
                '',
                '',
                '08999999999',
                'invalid_role',
                'Staff Magang',
                'intern',
                1500000,
                'TK/0',
                '',
                '',
                '',
                '',
            ],
        ];
        $sheet->fromArray($rows, null, 'A2');

        $file = $this->createUploadedFile($spreadsheet, 'employees_test.xlsx');

        $response = $this->post(route('hrm.employees.import-preview'), [
            'file' => $file,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'total_rows' => 3,
                'valid_count' => 1,
                'duplicate_count' => 1,
                'error_count' => 1,
            ],
        ]);
    }

    /**
     * Test 3.3: Employee import execute with skip and update strategies.
     */
    public function test_employee_import_execute_with_skip_and_update_strategies(): void
    {
        $this->actingAs($this->owner);

        // Rows simulating parsed preview data
        $rows = [
            [
                'line_number' => 2,
                'status' => 'valid',
                'name' => 'Citra Lestari',
                'email' => 'citra@cooca.test',
                'phone' => '081399887766',
                'role' => 'cashier',
                'job_title' => 'Kasir Utama',
                'employment_type' => 'full_time',
                'base_salary' => 4200000,
                'tax_ptkp_status' => 'TK/0',
                'location_name' => 'Kantor Pusat Jakarta',
                'bank_name' => 'BCA',
                'bank_account_number' => '5544332211',
                'nik_ktp' => '3276012345670002',
                'messages' => [],
            ],
            [
                'line_number' => 3,
                'status' => 'duplicate',
                'name' => 'Updated Sprint 3 Owner',
                'email' => 'exchange-sprint3@cooca.test',
                'phone' => '081122334455',
                'role' => 'owner',
                'job_title' => 'Chief Executive Officer',
                'employment_type' => 'full_time',
                'base_salary' => 25000000,
                'tax_ptkp_status' => 'K/2',
                'location_name' => 'Kantor Pusat Jakarta',
                'bank_name' => 'BCA',
                'bank_account_number' => '111222333',
                'nik_ktp' => '3171011111110001',
                'messages' => [],
            ],
        ];

        // 1. Execute with 'skip' strategy -> Duplicate row (owner) should NOT be modified
        $resSkip = $this->postJson(route('hrm.employees.import-execute'), [
            'rows' => $rows,
            'duplicate_strategy' => 'skip',
        ]);

        $resSkip->assertStatus(200);
        $resSkip->assertJson([
            'success' => true,
            'data' => [
                'imported' => 1,
                'updated' => 0,
                'skipped' => 1,
            ],
        ]);

        // Verify Citra was created
        $userCitra = User::where('email', 'citra@cooca.test')->firstOrFail();
        $membershipCitra = BusinessMembership::where('business_id', $this->business->id)
            ->where('user_id', $userCitra->id)
            ->firstOrFail();

        $this->assertEquals('Kasir Utama', $membershipCitra->job_title);
        $this->assertEquals(4200000, (float) $membershipCitra->base_salary);
        $this->assertEquals($this->location->id, $membershipCitra->primary_location_id);

        // Verify owner was NOT changed
        $this->ownerMembership->refresh();
        $this->assertEquals('Direktur Utama', $this->ownerMembership->job_title);
        $this->assertEquals(15000000, (float) $this->ownerMembership->base_salary);

        // 2. Execute with 'update' strategy -> Duplicate row should be updated
        $resUpdate = $this->postJson(route('hrm.employees.import-execute'), [
            'rows' => $rows,
            'duplicate_strategy' => 'update',
        ]);

        $resUpdate->assertStatus(200);
        $resUpdate->assertJson([
            'success' => true,
            'data' => [
                'imported' => 0,
                'updated' => 2, // Both Citra and Owner are updated
                'skipped' => 0,
            ],
        ]);

        $this->ownerMembership->refresh();
        $this->assertEquals('Chief Executive Officer', $this->ownerMembership->job_title);
        $this->assertEquals(25000000, (float) $this->ownerMembership->base_salary);
    }

    /**
     * Test 3.4: Fingerprint attendance template and export.
     */
    public function test_fingerprint_attendance_template_and_export(): void
    {
        $this->actingAs($this->owner);

        // Template download
        $resTemplate = $this->get(route('hrm.attendance.template'));
        $resTemplate->assertStatus(200);
        $resTemplate->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        // Export existing attendance
        $resExport = $this->get(route('hrm.attendance.export'));
        $resExport->assertStatus(200);
        $resExport->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    /**
     * Test 3.5: Fingerprint attendance preview and execution with late calculation.
     */
    public function test_fingerprint_attendance_preview_and_execution_with_late_calculation(): void
    {
        $this->actingAs($this->owner);

        // Create staff user
        $staffUser = User::create([
            'name' => 'Dimas Prakoso',
            'email' => 'dimas@cooca.test',
            'password' => 'password123',
            'phone' => '085566778899',
        ]);
        $staffRole = Role::where('slug', 'staff')->firstOrFail();
        BusinessMembership::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'user_id' => $staffUser->id,
            'role' => 'staff',
            'role_id' => $staffRole->id,
            'job_title' => 'Staff Gudang',
        ]);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Log Mesin Presensi');

        // Headers matching DataExchangeService::generateFingerprintTemplate
        $headers = [
            'ID Mesin / PIN / NIK / Email *',
            'Nama Karyawan (Referensi)',
            'Tanggal * (YYYY-MM-DD)',
            'Jam Masuk * (HH:MM / HH:MM:SS)',
            'Jam Keluar (HH:MM / HH:MM:SS)',
            'Status Kehadiran',
            'Nama Shift',
            'Catatan Mesin / Lokasi',
        ];
        $sheet->fromArray($headers, null, 'A1');

        $rows = [
            // Row 2: Valid on-time attendance (08:00 - 17:00)
            [
                'dimas@cooca.test',
                'Dimas Prakoso',
                '2026-10-10',
                '08:00',
                '17:00',
                'Hadir',
                'Shift Normal',
                'Log Mesin ZKTeco',
            ],
            // Row 3: Valid late attendance (08:45 - 17:00) -> 45 mins late
            [
                'dimas@cooca.test',
                'Dimas Prakoso',
                '2026-10-11',
                '08:45',
                '17:00',
                'Terlambat',
                'Shift Normal',
                'Macet tol',
            ],
            // Row 4: Error (unknown identifier)
            [
                'unknown.person@cooca.test',
                'Orang Tak Dikenal',
                '2026-10-12',
                '08:00',
                '17:00',
                'Hadir',
                'Shift Normal',
                '',
            ],
        ];
        $sheet->fromArray($rows, null, 'A2');

        $file = $this->createUploadedFile($spreadsheet, 'attendance_log.xlsx');

        // 1. Preview
        $resPreview = $this->post(route('hrm.attendance.import-preview'), [
            'file' => $file,
        ]);

        $resPreview->assertStatus(200);
        $resPreview->assertJson([
            'success' => true,
            'data' => [
                'total_rows' => 3,
                'valid_count' => 2,
                'error_count' => 1,
            ],
        ]);

        $previewData = $resPreview->json('data.rows');
        $this->assertEquals(0, $previewData[0]['late_minutes']);
        $this->assertEquals('present', $previewData[0]['attendance_status']);
        $this->assertEquals(45, $previewData[1]['late_minutes']);
        $this->assertEquals('late', $previewData[1]['attendance_status']);

        // 2. Execute import
        $resExecute = $this->postJson(route('hrm.attendance.import-execute'), [
            'rows' => $previewData,
            'duplicate_strategy' => 'skip',
        ]);

        $resExecute->assertStatus(200);
        $resExecute->assertJson([
            'success' => true,
            'data' => [
                'imported' => 2,
                'updated' => 0,
            ],
        ]);

        // Verify Attendance in database
        $attOnTime = Attendance::withoutGlobalScopes()
            ->where('business_id', $this->business->id)
            ->where('user_id', $staffUser->id)
            ->whereDate('date', '2026-10-10')
            ->firstOrFail();

        $this->assertEquals('present', $attOnTime->status);
        $this->assertEquals(0, $attOnTime->late_minutes);
        $this->assertEquals(540, $attOnTime->work_duration_minutes); // 9 hours = 540 mins

        $attLate = Attendance::withoutGlobalScopes()
            ->where('business_id', $this->business->id)
            ->where('user_id', $staffUser->id)
            ->whereDate('date', '2026-10-11')
            ->firstOrFail();

        $this->assertEquals('late', $attLate->status);
        $this->assertEquals(45, $attLate->late_minutes);
    }

    /**
     * Test 3.6: Storefront orders dispatch manifest Excel export.
     */
    public function test_storefront_orders_dispatch_manifest_export(): void
    {
        $this->actingAs($this->owner);

        // Create Unit & Product for CommerceOrder
        $unit = Unit::firstOrCreate(
            ['code' => 'pcs'],
            ['name' => 'Pieces', 'category' => 'quantity', 'is_base' => true]
        );

        $product = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Kopi Arabika Gayo 250g',
            'code' => 'KOP-GAYO-250',
            'output_unit_id' => $unit->id,
            'selling_price' => 85000,
        ]);

        // Create sample CommerceOrder
        $order = CommerceOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'order_number' => 'ORD-202610-001',
            'tracking_token' => (string) Str::uuid(),
            'order_type' => CommerceOrder::TYPE_DIRECT_CHECKOUT,
            'fulfillment_type' => CommerceOrder::FULFILLMENT_COURIER_MANUAL,
            'status' => CommerceOrder::STATUS_PROCESSING,
            'payment_status' => CommerceOrder::PAYMENT_PAID,
            'customer_name' => 'Rian Hidayat',
            'customer_phone' => '081299887766',
            'customer_email' => 'rian@gmail.com',
            'shipping_address' => 'Jl. Sudirman Kav 20, Jakarta Pusat',
            'destination_postal_code' => '10220',
            'shipping_notes' => 'Tolong titip di pos sekuriti',
            'subtotal' => 170000,
            'shipping_fee' => 15000,
            'total_amount' => 185000,
        ]);

        CommerceOrderItem::create([
            'commerce_order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_sku' => $product->code,
            'quantity' => 2,
            'unit_price' => 85000,
            'subtotal' => 170000,
        ]);

        $response = $this->get(route('storefront.orders.export-dispatch'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        // Read exported spreadsheet in memory to verify row content
        $stream = $response->streamedContent();
        $this->assertNotEmpty($stream);

        $tempPath = tempnam(sys_get_temp_dir(), 'export_test_') . '.xlsx';
        file_put_contents($tempPath, $stream);

        $spreadsheet = IOFactory::load($tempPath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        // Check header title and data rows
        $this->assertStringContainsString('ORD-202610-001', (string) $rows[1][0]);
        $this->assertEquals('Rian Hidayat', $rows[1][2]);
        $this->assertEquals('PROCESSING', (string) $rows[1][10]);
        $this->assertStringContainsString('Kopi Arabika Gayo 250g', (string) $rows[1][15]);

        unlink($tempPath);
    }

    /**
     * Test 3.7: Marketplace multi-channel orders consolidated export.
     */
    public function test_marketplace_orders_consolidated_export(): void
    {
        $this->actingAs($this->owner);

        // Create MarketplaceAccount for Shopee and TikTok Shop
        $shopeeAccount = MarketplaceAccount::create([
            'business_id' => $this->business->id,
            'channel' => 'shopee',
            'shop_id' => 'shopee_store_1',
            'shop_name' => 'Shopee Official Store',
            'status' => MarketplaceAccount::STATUS_CONNECTED,
            'is_active' => true,
        ]);

        $tiktokAccount = MarketplaceAccount::create([
            'business_id' => $this->business->id,
            'channel' => 'tiktok_shop',
            'shop_id' => 'tiktok_store_1',
            'shop_name' => 'TikTok Shop Official',
            'status' => MarketplaceAccount::STATUS_CONNECTED,
            'is_active' => true,
        ]);

        // Create sample MarketplaceOrders
        MarketplaceOrder::create([
            'business_id' => $this->business->id,
            'marketplace_account_id' => $shopeeAccount->id,
            'channel' => 'shopee',
            'external_order_id' => 'SHP-998877',
            'external_order_sn' => '241015SHPEE9988',
            'buyer_name' => 'Siti Rahma',
            'buyer_phone' => '081233445566',
            'total_amount' => 250000,
            'channel_fee' => 15000,
            'order_status' => 'paid',
            'items_summary' => [
                ['name' => 'Kemeja Katun Putih M', 'qty' => 1, 'price' => 250000],
            ],
            'shipping_provider' => 'J&T Express',
            'tracking_number' => 'JT1234567890',
            'placed_at' => now()->subHours(2),
        ]);

        MarketplaceOrder::create([
            'business_id' => $this->business->id,
            'marketplace_account_id' => $tiktokAccount->id,
            'channel' => 'tiktok_shop',
            'external_order_id' => 'TTS-112233',
            'external_order_sn' => '578912345678',
            'buyer_name' => 'Andi Wijaya',
            'buyer_phone' => '087788990011',
            'total_amount' => 120000,
            'channel_fee' => 7500,
            'order_status' => 'shipped',
            'items_summary' => [
                ['name' => 'Kaos Polos Combed 30s L', 'qty' => 2, 'price' => 60000],
            ],
            'shipping_provider' => 'SiCepat',
            'tracking_number' => '004123456789',
            'placed_at' => now()->subHours(5),
        ]);

        // 1. Export all channels
        $responseAll = $this->get(route('marketplace-hub.orders.export'));
        $responseAll->assertStatus(200);
        $responseAll->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $streamAll = $responseAll->streamedContent();
        $tempPathAll = tempnam(sys_get_temp_dir(), 'mp_export_all_') . '.xlsx';
        file_put_contents($tempPathAll, $streamAll);

        $sheetAll = IOFactory::load($tempPathAll)->getActiveSheet();
        $rowsAll = $sheetAll->toArray();

        // 1 header row + 2 data rows = 3 rows total
        $this->assertCount(3, $rowsAll);
        $this->assertEquals('Shopee', $rowsAll[1][0]);
        $this->assertEquals('TikTok Shop', $rowsAll[2][0]);
        unlink($tempPathAll);

        // 2. Export filtered by channel 'shopee'
        $responseShopee = $this->get(route('marketplace-hub.orders.export', ['channel' => 'shopee']));
        $responseShopee->assertStatus(200);

        $tempPathShopee = tempnam(sys_get_temp_dir(), 'mp_export_shp_') . '.xlsx';
        file_put_contents($tempPathShopee, $responseShopee->streamedContent());

        $sheetShopee = IOFactory::load($tempPathShopee)->getActiveSheet();
        $rowsShopee = $sheetShopee->toArray();

        // 1 header row + 1 data row = 2 rows
        $this->assertCount(2, $rowsShopee);
        $this->assertEquals('Shopee', $rowsShopee[1][0]);
        $this->assertEquals('Siti Rahma', $rowsShopee[1][3]);
        unlink($tempPathShopee);
    }
}
