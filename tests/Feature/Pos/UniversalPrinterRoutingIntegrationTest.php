<?php

declare(strict_types=1);

namespace Tests\Feature\Pos;

use App\Domain\Printer\EscposFormatter;
use App\Domain\Printer\UniversalPrintRoutingService;
use App\Models\Business;
use App\Models\Location;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\PosPrinter;
use App\Models\PosPrinterRoutingRule;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class UniversalPrinterRoutingIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Business $business;
    private Location $location;
    private Unit $unit;
    private PosPrinter $cashierPrinter;
    private PosPrinter $kitchenPrinter;
    private PosPrinter $barPrinter;
    private PosPrinter $workshopPrinter;
    private ProductCategory $foodCat;
    private ProductCategory $drinkCat;
    private ProductCategory $serviceCat;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->owner = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi.printer@example.com',
            'phone' => '081211112222',
            'password' => 'password123',
        ]);
        $this->owner->forceFill(['email_verified_at' => now()])->save();

        $this->business = Business::create([
            'name' => 'COOCA Universal Hub',
            'address' => 'Jl. Sudirman 45, Jakarta',
            'phone' => '0217778889',
            'pos_receipt_footer_note' => 'Terima Kasih Atas Kunjungan Anda!',
        ]);

        $this->business->users()->attach($this->owner->id, [
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'role' => 'owner',
        ]);

        $this->location = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Outlet Pusat',
            'code' => 'OUT-01',
            'is_active' => true,
        ]);

        $this->unit = Unit::create([
            'business_id' => $this->business->id,
            'name' => 'Porsi',
            'code' => 'PRS',
            'category' => 'quantity',
            'is_base' => true,
        ]);

        $this->foodCat = ProductCategory::create([
            'business_id' => $this->business->id,
            'name' => 'Makanan Utama',
        ]);

        $this->drinkCat = ProductCategory::create([
            'business_id' => $this->business->id,
            'name' => 'Minuman & Kopi',
        ]);

        $this->serviceCat = ProductCategory::create([
            'business_id' => $this->business->id,
            'name' => 'Jasa Servis Bengkel',
        ]);

        // Configure 4 Station Printers
        $this->cashierPrinter = PosPrinter::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'name' => 'EPSON-TM-T82-KASIR',
            'alias' => 'Kasir Depan',
            'station' => PosPrinter::STATION_CASHIER,
            'connection_type' => PosPrinter::TYPE_AGENT,
            'interface_address' => '127.0.0.1',
            'port' => 9100,
            'paper_width' => '80mm',
            'is_default' => true,
            'is_active' => true,
            'assigned_usages' => [PosPrinter::USAGE_CASHIER_RECEIPT],
        ]);

        $this->kitchenPrinter = PosPrinter::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'name' => 'STAR-TSP-KITCHEN',
            'alias' => 'Dapur Makanan',
            'station' => PosPrinter::STATION_KITCHEN,
            'fallback_printer_id' => $this->cashierPrinter->id,
            'connection_type' => PosPrinter::TYPE_AGENT,
            'interface_address' => '192.168.1.101',
            'port' => 9100,
            'paper_width' => '80mm',
            'is_active' => true,
            'assigned_usages' => [PosPrinter::USAGE_KITCHEN_ORDER],
        ]);

        $this->barPrinter = PosPrinter::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'name' => 'SUNMI-BAR-PRINTER',
            'alias' => 'Barista Bar',
            'station' => PosPrinter::STATION_BAR,
            'fallback_printer_id' => $this->cashierPrinter->id,
            'connection_type' => PosPrinter::TYPE_AGENT,
            'interface_address' => '192.168.1.102',
            'port' => 9100,
            'paper_width' => '58mm',
            'is_active' => true,
            'assigned_usages' => [PosPrinter::USAGE_BAR_ORDER],
        ]);

        $this->workshopPrinter = PosPrinter::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'name' => 'EPSON-SPK-WORKSHOP',
            'alias' => 'Bengkel Pit Stop',
            'station' => PosPrinter::STATION_SPK,
            'fallback_printer_id' => $this->cashierPrinter->id,
            'connection_type' => PosPrinter::TYPE_AGENT,
            'interface_address' => '192.168.1.103',
            'port' => 9100,
            'paper_width' => '80mm',
            'is_active' => true,
            'assigned_usages' => [PosPrinter::USAGE_SPK_SERVICE],
        ]);
    }

    public function test_multi_station_intelligent_routing_partitions_order_properly(): void
    {
        $order = PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->owner->id,
            'order_number' => 'POS-20261118-001',
            'order_date' => now(),
            'order_type' => 'dine_in',
            'table_or_reference' => 'Meja 12',
            'status' => 'completed',
            'payment_status' => 'paid',
            'subtotal' => 150000,
            'total_amount' => 150000,
            'vehicle_license_plate' => 'B 1234 CO',
            'vehicle_model' => 'Honda Civic',
        ]);

        // Food item
        $foodProd = Product::create([
            'business_id' => $this->business->id,
            'category_id' => $this->foodCat->id,
            'output_unit_id' => $this->unit->id,
            'name' => 'Nasi Goreng Wagyu',
            'sku' => 'FOOD-01',
            'price' => 75000,
        ]);
        PosOrderItem::create([
            'pos_order_id' => $order->id,
            'product_id' => $foodProd->id,
            'product_name' => 'Nasi Goreng Wagyu',
            'quantity' => 1,
            'unit_price' => 75000,
            'total_price' => 75000,
        ]);

        // Beverage item
        $drinkProd = Product::create([
            'business_id' => $this->business->id,
            'category_id' => $this->drinkCat->id,
            'output_unit_id' => $this->unit->id,
            'name' => 'Kopi Latte Aren',
            'sku' => 'DRK-01',
            'price' => 35000,
        ]);
        PosOrderItem::create([
            'pos_order_id' => $order->id,
            'product_id' => $drinkProd->id,
            'product_name' => 'Kopi Latte Aren',
            'quantity' => 2,
            'unit_price' => 35000,
            'total_price' => 70000,
        ]);

        // Workshop Service item
        $serviceProd = Product::create([
            'business_id' => $this->business->id,
            'category_id' => $this->serviceCat->id,
            'output_unit_id' => $this->unit->id,
            'name' => 'Servis Tune Up Mesin',
            'sku' => 'SRV-01',
            'price' => 150000,
            'is_service' => true,
        ]);
        PosOrderItem::create([
            'pos_order_id' => $order->id,
            'product_id' => $serviceProd->id,
            'product_name' => 'Servis Tune Up Mesin',
            'quantity' => 1,
            'unit_price' => 150000,
            'total_price' => 150000,
        ]);

        $service = new UniversalPrintRoutingService();
        $jobs = $service->planPrintJobs($order, $this->location->id, [
            'print_receipt' => true,
            'print_stations' => true,
        ]);

        $this->assertCount(4, $jobs);

        $docTypes = array_column($jobs, 'document_type');
        $this->assertContains(PosPrinterRoutingRule::DOC_CUSTOMER_RECEIPT, $docTypes);
        $this->assertContains(PosPrinterRoutingRule::DOC_SPK_SERVICE, $docTypes);
        $this->assertContains(PosPrinterRoutingRule::DOC_KITCHEN_TICKET, $docTypes);
        $this->assertContains(PosPrinterRoutingRule::DOC_BAR_TICKET, $docTypes);

        $receiptJob = collect($jobs)->firstWhere('document_type', PosPrinterRoutingRule::DOC_CUSTOMER_RECEIPT);
        $this->assertEquals($this->cashierPrinter->id, $receiptJob['printer_id']);

        $kitchenJob = collect($jobs)->firstWhere('document_type', PosPrinterRoutingRule::DOC_KITCHEN_TICKET);
        $this->assertEquals($this->kitchenPrinter->id, $kitchenJob['printer_id']);

        $barJob = collect($jobs)->firstWhere('document_type', PosPrinterRoutingRule::DOC_BAR_TICKET);
        $this->assertEquals($this->barPrinter->id, $barJob['printer_id']);

        $spkJob = collect($jobs)->firstWhere('document_type', PosPrinterRoutingRule::DOC_SPK_SERVICE);
        $this->assertEquals($this->workshopPrinter->id, $spkJob['printer_id']);
    }

    public function test_single_printer_mode_routes_all_documents_to_cashier_printer(): void
    {
        $order = PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->owner->id,
            'order_number' => 'POS-20261118-002',
            'order_date' => now(),
            'order_type' => 'dine_in',
            'table_or_reference' => 'Meja 5',
            'status' => 'completed',
            'payment_status' => 'paid',
            'subtotal' => 50000,
            'total_amount' => 50000,
        ]);

        $prod = Product::create([
            'business_id' => $this->business->id,
            'category_id' => $this->foodCat->id,
            'output_unit_id' => $this->unit->id,
            'name' => 'Bakmi Ayam Jamur',
            'sku' => 'F-02',
            'price' => 50000,
        ]);
        PosOrderItem::create([
            'pos_order_id' => $order->id,
            'product_id' => $prod->id,
            'product_name' => 'Bakmi Ayam Jamur',
            'quantity' => 1,
            'unit_price' => 50000,
            'total_price' => 50000,
        ]);

        $service = new UniversalPrintRoutingService();
        $jobs = $service->planPrintJobs($order, $this->location->id, [
            'single_printer_mode' => true,
            'print_receipt' => true,
            'print_stations' => true,
        ]);

        $this->assertCount(2, $jobs);
        foreach ($jobs as $job) {
            $this->assertEquals($this->cashierPrinter->id, $job['printer_id']);
        }
    }

    public function test_fallback_printer_applies_when_station_printer_is_unconfigured(): void
    {
        $this->kitchenPrinter->update(['is_active' => false]);

        $order = PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->owner->id,
            'order_number' => 'POS-20261118-003',
            'order_date' => now(),
            'order_type' => 'dine_in',
            'status' => 'completed',
            'payment_status' => 'paid',
            'subtotal' => 45000,
            'total_amount' => 45000,
        ]);

        $prod = Product::create([
            'business_id' => $this->business->id,
            'category_id' => $this->foodCat->id,
            'output_unit_id' => $this->unit->id,
            'name' => 'Sop Buntut Spesial',
            'sku' => 'F-03',
            'price' => 45000,
        ]);
        PosOrderItem::create([
            'pos_order_id' => $order->id,
            'product_id' => $prod->id,
            'product_name' => 'Sop Buntut Spesial',
            'quantity' => 1,
            'unit_price' => 45000,
            'total_price' => 45000,
        ]);

        $service = new UniversalPrintRoutingService();
        $jobs = $service->planPrintJobs($order, $this->location->id, [
            'single_printer_mode' => false,
            'print_receipt' => true,
            'print_stations' => true,
        ]);

        $kitchenJob = collect($jobs)->firstWhere('document_type', PosPrinterRoutingRule::DOC_KITCHEN_TICKET);
        $this->assertNotNull($kitchenJob);
        $this->assertEquals($this->cashierPrinter->id, $kitchenJob['printer_id']);
        $this->assertTrue($kitchenJob['is_fallback']);
    }

    public function test_spk_escpos_formatter_contains_automotive_details(): void
    {
        $order = PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->owner->id,
            'order_number' => 'SPK-2026-999',
            'order_date' => now(),
            'order_type' => 'work_order',
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'vehicle_license_plate' => 'B 9999 PRO',
            'vehicle_model' => 'Toyota Fortuner GR',
            'vehicle_mileage' => 45200,
            'service_notes' => 'Ganti oli transmisi, cek rem depan berdecit',
            'subtotal' => 250000,
            'total_amount' => 250000,
        ]);

        PosOrderItem::create([
            'pos_order_id' => $order->id,
            'product_name' => 'Jasa Tune Up & Cek Rem',
            'quantity' => 1,
            'unit_price' => 250000,
            'total_price' => 250000,
        ]);

        $formatter = new EscposFormatter();
        $rawBytes = $formatter->formatSpkServiceOrder($order, $this->workshopPrinter, $order->items->all());

        $this->assertNotEmpty($rawBytes);
        $this->assertStringContainsString('SURAT PERINTAH KERJA', $rawBytes);
        $this->assertStringContainsString('B 9999 PRO', $rawBytes);
        $this->assertStringContainsString('Toyota Fortuner GR', $rawBytes);
        $this->assertStringContainsString('45.200 KM', $rawBytes);
        $this->assertStringContainsString('Jasa Tune Up & Cek Rem', $rawBytes);
    }

    public function test_route_order_print_jobs_endpoint_returns_json(): void
    {
        $this->actingAs($this->owner);
        Context::setBusiness($this->business);
        Context::setLocation($this->location);

        $order = PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->owner->id,
            'order_number' => 'POS-API-001',
            'order_date' => now(),
            'order_type' => 'dine_in',
            'status' => 'completed',
            'payment_status' => 'paid',
            'subtotal' => 60000,
            'total_amount' => 60000,
        ]);

        $prod = Product::create([
            'business_id' => $this->business->id,
            'category_id' => $this->drinkCat->id,
            'output_unit_id' => $this->unit->id,
            'name' => 'Ice Americano',
            'sku' => 'DRK-02',
            'price' => 30000,
        ]);
        PosOrderItem::create([
            'pos_order_id' => $order->id,
            'product_id' => $prod->id,
            'product_name' => 'Ice Americano',
            'quantity' => 2,
            'unit_price' => 30000,
            'total_price' => 60000,
        ]);

        $response = $this->withSession([
            'active_business_id' => $this->business->id,
            'active_location_id' => $this->location->id,
        ])->postJson("/pos/orders/{$order->id}/route-print-jobs", [
            'single_printer_mode' => false,
            'dispatch_hardware' => false,
            'print_receipt' => true,
            'print_stations' => true,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Multi-station print jobs planned successfully',
        ]);
        $response->assertJsonStructure([
            'success',
            'message',
            'jobs_count',
            'jobs' => [
                '*' => [
                    'job_id',
                    'document_type',
                    'station',
                    'printer_id',
                    'printer_name',
                    'connection_type',
                    'base64_payload',
                ],
            ],
        ]);
    }
}
