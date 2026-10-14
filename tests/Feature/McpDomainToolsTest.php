<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Location;
use App\Models\Material;
use App\Models\McpAccessToken;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\SalesOrder;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class McpDomainToolsTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $user;
    private string $token;
    private Location $location;
    private Unit $unitKg;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->user = User::create([
            'name'     => 'Merchant Owner',
            'email'    => 'owner@example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->business = Business::create([
            'name' => 'Kopi Nusantara',
            'slug' => 'kopi-nusantara',
        ]);

        $this->business->users()->attach($this->user->id, [
            'id'        => Str::uuid(),
            'role'      => 'owner',
            'is_active' => true,
        ]);

        $this->user->update(['active_business_id' => $this->business->id]);
        app(\App\Domain\Billing\EntitlementService::class)->upgradeToCore($this->business, 'monthly');

        $this->location = Location::create([
            'business_id' => $this->business->id,
            'name'        => 'Gudang Pusat',
            'code'        => 'WH-01',
            'is_active'   => true,
            'is_primary'  => true,
        ]);

        $this->unitKg = Unit::create([
            'business_id' => $this->business->id,
            'name'        => 'Kilogram',
            'code'        => 'kg',
            'category'    => Unit::CATEGORY_WEIGHT,
            'is_base'     => true,
        ]);

        $generated = McpAccessToken::generateToken(
            business: $this->business,
            user: $this->user,
            name: 'Domain Tools Test Token',
            abilities: ['*'],
            providerHint: 'all'
        );

        $this->token = $generated['token'];
    }

    private function callTool(string $toolName, array $arguments = []): array
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/v1/mcp/message', [
                'jsonrpc' => '2.0',
                'method'  => 'tools/call',
                'params'  => [
                    'name'      => $toolName,
                    'arguments' => $arguments,
                ],
                'id'      => random_int(100, 999),
            ]);

        $response->assertStatus(200);
        $this->assertFalse((bool) $response->json('result.isError', false));
        $content = $response->json('result.content.0.text');
        $this->assertNotNull($content);

        return json_decode($content, true);
    }

    public function test_modifier_manage_tool_creates_group_and_option(): void
    {
        $createRes = $this->callTool('modifier_manage', [
            'action' => 'create_group',
            'name' => 'Level Gula',
            'selection_type' => 'single',
            'is_required' => true,
            'options' => [
                ['name' => 'Normal Sugar 100%', 'price_delta' => 0],
                ['name' => 'Less Sugar 50%', 'price_delta' => 0],
                ['name' => 'Extra Brown Sugar', 'price_delta' => 3000],
            ],
        ]);

        $this->assertTrue($createRes['success']);
        $groupId = $createRes['modifier_group']['id'];

        $listRes = $this->callTool('modifier_manage', ['action' => 'list']);
        $this->assertTrue($listRes['success']);
        $this->assertGreaterThanOrEqual(1, $listRes['count']);
    }

    public function test_inventory_manage_material_and_bom_tools(): void
    {
        // 1. Create Raw Material
        $matRes = $this->callTool('inventory_manage_material', [
            'action' => 'create',
            'name' => 'Biji Kopi Gayo',
            'unit_id' => $this->unitKg->id,
            'purchasing_price' => 150000,
        ]);

        $this->assertTrue($matRes['success']);
        $materialId = $matRes['material']['id'];

        // 2. Create Product
        $product = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Espresso Single Shot',
            'code' => 'ESP-01',
            'price' => 20000,
            'base_cost' => 0,
            'output_unit_id' => $this->unitKg->id,
        ]);

        // 3. Set Recipe / BOM
        $bomRes = $this->callTool('inventory_manage_bom', [
            'action' => 'set_recipe',
            'product_id' => $product->id,
            'items' => [
                [
                    'material_id' => $materialId,
                    'quantity' => 0.02, // 20 grams = 0.02 kg
                ],
            ],
        ]);

        $this->assertTrue($bomRes['success']);
        $this->assertEquals(3000, $bomRes['total_calculated_hpp']); // 0.02 * 150,000 = 3,000

        $product->refresh();
        $this->assertEquals(3000, (float) $product->base_cost);
    }

    public function test_inventory_stock_opname_tool(): void
    {
        $material = Material::create([
            'business_id' => $this->business->id,
            'name' => 'Susu Segar Pasteurisasi',
            'code' => 'SS-01',
            'unit_id' => $this->unitKg->id,
            'current_stock' => 10,
        ]);

        $res = $this->callTool('inventory_stock_opname', [
            'action' => 'adjust',
            'location_id' => $this->location->id,
            'reason' => 'opname_variance',
            'notes' => 'Fisik susut saat opname bulanan',
            'items' => [
                [
                    'material_id' => $material->id,
                    'type' => 'subtraction',
                    'quantity' => 2,
                ],
            ],
        ]);

        $this->assertTrue($res['success']);
        $this->assertNotEmpty($res['adjustment']['adjustment_number']);
    }

    public function test_purchasing_manage_supplier_and_order_tools(): void
    {
        // 1. Supplier
        $supRes = $this->callTool('purchasing_manage_supplier', [
            'action' => 'create',
            'name' => 'CV Sumber Kopi Sejahtera',
            'contact_person' => 'Budi Santoso',
            'phone' => '081299988877',
        ]);

        $this->assertTrue($supRes['success']);
        $supplierId = $supRes['supplier']['id'];

        $material = Material::create([
            'business_id' => $this->business->id,
            'name' => 'Biji Kopi Robusta',
            'unit_id' => $this->unitKg->id,
        ]);

        // 2. PO Creation
        $poRes = $this->callTool('purchasing_manage_order', [
            'action' => 'create',
            'po_type' => 'supplier',
            'supplier_id' => $supplierId,
            'items' => [
                [
                    'material_id' => $material->id,
                    'item_type' => 'material',
                    'quantity' => 50,
                    'unit_price' => 80000,
                ],
            ],
        ]);

        $this->assertTrue($poRes['success']);
        $poId = $poRes['purchase_order']['id'];

        // 3. Receive Goods
        $grRes = $this->callTool('purchasing_manage_order', [
            'action' => 'receive_goods',
            'purchase_order_id' => $poId,
            'location_id' => $this->location->id,
        ]);

        $this->assertTrue($grRes['success']);
        $this->assertNotEmpty($grRes['goods_receipt']['receipt_number']);
    }

    public function test_sales_pipeline_and_invoice_tools(): void
    {
        $customer = Customer::create([
            'business_id' => $this->business->id,
            'name' => 'PT Makmur Jaya Abadi',
            'phone' => '081122334455',
        ]);

        $product = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Paket Hampers Kopi',
            'code' => 'HAMPERS-01',
            'price' => 150000,
            'base_cost' => 80000,
            'output_unit_id' => $this->unitKg->id,
        ]);

        // 1. Quotation Tool
        $quoRes = $this->callTool('sales_manage_quotation', [
            'action' => 'create',
            'customer_id' => $customer->id,
            'discount_amount' => 50000,
            'items' => [
                [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => 10,
                    'unit_price' => 150000,
                ],
            ],
        ]);

        $this->assertTrue($quoRes['success']);
        $quotationId = $quoRes['quotation']['id'];

        // 2. Convert Quotation to Sales Order
        $soRes = $this->callTool('sales_manage_quotation', [
            'action' => 'convert_to_sales_order',
            'quotation_id' => $quotationId,
        ]);

        $this->assertTrue($soRes['success']);
        $soId = $soRes['sales_order']['id'];

        // 3. Convert Sales Order to Official Invoice
        $invConvertRes = $this->callTool('sales_manage_order', [
            'action' => 'convert_to_invoice',
            'sales_order_id' => $soId,
        ]);

        $this->assertTrue($invConvertRes['success']);
        $invoiceId = $invConvertRes['invoice']['id'];

        // 4. Record Payment on Invoice
        $payRes = $this->callTool('finance_manage_invoice', [
            'action' => 'record_payment',
            'invoice_id' => $invoiceId,
            'amount' => 500000,
            'payment_method' => 'bank_transfer',
            'reference_number' => 'MUTASI-BCA-987654',
        ]);

        $this->assertTrue($payRes['success']);
        $this->assertEquals(500000, $payRes['payment']['amount']);
        $this->assertEquals(Invoice::STATUS_PARTIALLY_PAID, $payRes['invoice']['status']);
    }

    public function test_mcp_services_manage_service_tool_lifecycle(): void
    {
        // 1. Create a new service via MCP
        $createRes = $this->callTool('services_manage_service', [
            'action'        => 'create',
            'name'          => 'Servis Laptop Komprehensif',
            'code'          => 'SRV-LAPTOP-01',
            'selling_price' => 150000,
            'base_cost'     => 30000,
            'category_name' => 'Layanan IT',
            'unit_name'     => 'Jasa',
            'description'   => 'Pembersihan debu, ganti thermal paste, dan instalasi OS.',
            'show_in_pos'   => true,
            'show_in_website' => true,
        ]);

        $this->assertTrue($createRes['success']);
        $this->assertEquals('Servis Laptop Komprehensif', $createRes['service']['name']);
        $this->assertEquals(150000, $createRes['service']['selling_price']);
        $this->assertEquals(30000, $createRes['service']['base_cost']);
        $this->assertEquals(120000, $createRes['service']['margin_amount']);
        $this->assertEquals('80%', $createRes['service']['margin_percentage']);
        $serviceId = $createRes['service']['id'];

        $this->assertDatabaseHas('products', [
            'id'            => $serviceId,
            'business_id'   => $this->business->id,
            'type'          => Product::TYPE_SERVICE,
            'name'          => 'Servis Laptop Komprehensif',
            'code'          => 'SRV-LAPTOP-01',
            'selling_price' => 150000,
            'base_cost'     => 30000,
            'is_active'     => true,
        ]);

        $this->assertDatabaseHas('cost_models', [
            'business_id' => $this->business->id,
            'product_id'  => $serviceId,
            'method'      => \App\Models\CostModel::METHOD_SERVICE,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'business_id'    => $this->business->id,
            'action'         => 'mcp.services_manage_service.created',
            'auditable_type' => Product::class,
            'auditable_id'   => $serviceId,
        ]);

        // 2. List services via MCP
        $listRes = $this->callTool('services_manage_service', [
            'action' => 'list',
            'search' => 'Laptop',
        ]);

        $this->assertTrue($listRes['success']);
        $this->assertGreaterThanOrEqual(1, $listRes['total_services']);
        $this->assertEquals('Servis Laptop Komprehensif', $listRes['services'][0]['name']);

        // 3. Update service via MCP
        $updateRes = $this->callTool('services_manage_service', [
            'action'        => 'update',
            'service_id'    => 'SRV-LAPTOP-01',
            'selling_price' => 175000,
            'base_cost'     => 35000,
        ]);

        $this->assertTrue($updateRes['success']);
        $this->assertEquals(175000, $updateRes['service']['selling_price']);
        $this->assertEquals(35000, $updateRes['service']['base_cost']);

        $this->assertDatabaseHas('products', [
            'id'            => $serviceId,
            'selling_price' => 175000,
            'base_cost'     => 35000,
        ]);

        // 4. Safe Delete (no transactions attached)
        $delRes = $this->callTool('services_manage_service', [
            'action'     => 'delete',
            'service_id' => $serviceId,
        ]);

        $this->assertTrue($delRes['success']);
        $this->assertEquals('deleted', $delRes['status']);
        $this->assertSoftDeleted('products', ['id' => $serviceId]);
    }

    public function test_mcp_services_manage_service_financial_guard_archives_with_transactions(): void
    {
        // 1. Create a service
        $createRes = $this->callTool('services_manage_service', [
            'action'        => 'create',
            'name'          => 'Ganti Oli Mesin',
            'code'          => 'SRV-OLI-01',
            'selling_price' => 50000,
            'base_cost'     => 35000,
        ]);
        $serviceId = $createRes['service']['id'];

        // 2. Attach a POS Order transaction to the service
        $posOrder = PosOrder::create([
            'business_id'      => $this->business->id,
            'location_id'      => $this->location->id,
            'user_id'          => $this->user->id,
            'order_number'     => 'POS-202610-SRV01',
            'order_date'       => now()->toDateString(),
            'total_amount'     => 50000,
            'subtotal'         => 50000,
            'discount_amount'  => 0,
            'tax_amount'       => 0,
            'paid_amount'      => 50000,
            'payment_status'   => 'paid',
            'status'           => 'completed',
        ]);

        PosOrderItem::create([
            'pos_order_id' => $posOrder->id,
            'product_id'   => $serviceId,
            'product_name' => 'Ganti Oli Mesin',
            'product_code' => 'SRV-OLI-01',
            'quantity'     => 1,
            'unit_price'   => 50000,
            'subtotal'     => 50000,
        ]);

        // 3. Attempt to delete service - should trigger financial guard and archive instead of deleting
        $delRes = $this->callTool('services_manage_service', [
            'action'     => 'delete',
            'service_id' => $serviceId,
        ]);

        $this->assertTrue($delRes['success']);
        $this->assertEquals('archived', $delRes['status']);
        $this->assertDatabaseHas('products', [
            'id'        => $serviceId,
            'is_active' => false,
        ]);
    }
}

