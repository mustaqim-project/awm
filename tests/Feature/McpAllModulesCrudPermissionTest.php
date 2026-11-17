<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Mcp\Protocol\McpProtocolEngine;
use App\Domain\Mcp\Tools\ApprovalManageWorkflowTool;
use App\Domain\Mcp\Tools\CrmManageCustomerTool;
use App\Domain\Mcp\Tools\HrmManageEmployeeTool;
use App\Domain\Mcp\Tools\InventoryTransferStockTool;
use App\Domain\Mcp\Tools\MasterDataManageUnitConversionTool;
use App\Domain\Mcp\Tools\McpToolRegistry;
use App\Domain\Mcp\Tools\PosManageTransactionTool;
use App\Domain\Mcp\Tools\SettingsManageBusinessTool;
use App\Domain\Mcp\Tools\WarehouseManageLocationTool;
use App\Models\Business;
use App\Models\Customer;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\Material;
use App\Models\MaterialCategory;
use App\Models\McpAccessToken;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class McpAllModulesCrudPermissionTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $user;
    private Location $mainWarehouse;
    private Location $secondaryWarehouse;
    private Material $material;
    private McpToolRegistry $registry;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->user = User::create([
            'name' => 'Owner All Modules',
            'email' => 'owner_mcp_all@example.com',
            'password' => 'secret123',
        ]);

        $this->business = Business::create([
            'name' => 'PT COOCA Super Mandiri',
            'tax_percentage' => 11,
            'receipt_footer_note' => 'Terima kasih atas kunjungan Anda!',
        ]);

        $this->business->users()->attach($this->user->id, [
            'id' => Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);
        $this->user->update(['active_business_id' => $this->business->id]);

        $this->actingAs($this->user);
        session(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);

        $this->mainWarehouse = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Gudang Pusat',
            'slug' => 'gudang-pusat',
            'type' => 'warehouse',
            'code' => 'GD-01',
            'is_primary' => true,
            'is_active' => true,
        ]);

        $this->secondaryWarehouse = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Gudang Cabang',
            'slug' => 'gudang-cabang',
            'type' => 'warehouse',
            'code' => 'GD-02',
            'is_primary' => false,
            'is_active' => true,
        ]);

        $unitPcs = Unit::create([
            'business_id' => $this->business->id,
            'code' => 'PCS',
            'name' => 'Pcs',
            'category' => Unit::CATEGORY_QUANTITY,
        ]);

        $cat = MaterialCategory::create([
            'business_id' => $this->business->id,
            'name' => 'Bahan Baku',
            'slug' => 'bahan-baku',
        ]);

        $this->material = Material::create([
            'business_id' => $this->business->id,
            'code' => 'MAT-KP-01',
            'name' => 'Biji Kopi Arabika',
            'category_id' => $cat->id,
            'unit_id' => $unitPcs->id,
            'cost_price' => 5000,
            'is_active' => true,
        ]);

        InventoryStock::create([
            'business_id' => $this->business->id,
            'material_id' => $this->material->id,
            'location_id' => $this->mainWarehouse->id,
            'quantity' => 100,
        ]);

        $this->registry = app(McpToolRegistry::class);
    }

    public function test_all_owner_panel_tools_are_registered(): void
    {
        $this->assertTrue($this->registry->hasTool('warehouse_manage_location'));
        $this->assertTrue($this->registry->hasTool('master_data_manage_unit_conversion'));
        $this->assertTrue($this->registry->hasTool('inventory_transfer_stock'));
        $this->assertTrue($this->registry->hasTool('hrm_manage_employee'));
        $this->assertTrue($this->registry->hasTool('pos_manage_transaction'));
        $this->assertTrue($this->registry->hasTool('approval_manage_workflowTool') || $this->registry->hasTool('approval_manage_workflow'));
        $this->assertTrue($this->registry->hasTool('settings_manage_business'));
        $this->assertTrue($this->registry->hasTool('crm_manage_customer'));

        $this->assertCount(33, $this->registry->getAllTools());
    }

    public function test_granular_crud_permission_blocks_unauthorized_action(): void
    {
        // Token has ONLY mcp:hrm:read
        $tokenData = McpAccessToken::generateToken(
            $this->business,
            $this->user,
            'HRM Read Only Token',
            ['mcp:hrm:read']
        );
        $readToken = $tokenData['model'];

        Context::setMcpToken($readToken);
        $hrmTool = $this->registry->getTool('hrm_manage_employee');

        // Reading (list) should succeed
        $listRes = $hrmTool->execute($this->business, $this->user, ['action' => 'list']);
        $this->assertTrue($listRes['success']);

        // Creating (create) should be blocked with RuntimeException code -32001
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(-32001);
        $hrmTool->execute($this->business, $this->user, [
            'action' => 'create',
            'name' => 'Budi Staff',
            'email' => 'budi@example.com',
            'job_title' => 'Barista',
            'base_salary' => 3000000,
        ]);
    }

    public function test_granular_crud_permission_allows_authorized_write_and_blocks_read(): void
    {
        // Token has ONLY mcp:pos:create
        $tokenData = McpAccessToken::generateToken(
            $this->business,
            $this->user,
            'POS Create Only Token',
            ['mcp:pos:create']
        );
        $writeToken = $tokenData['model'];

        Context::setMcpToken($writeToken);
        $posTool = $this->registry->getTool('pos_manage_transaction');

        // Reading (list) should be blocked
        $blocked = false;
        try {
            $posTool->execute($this->business, $this->user, ['action' => 'list']);
        } catch (\RuntimeException $e) {
            $blocked = true;
            $this->assertEquals(-32001, $e->getCode());
            $this->assertStringContainsString('mcp:pos:read', $e->getMessage());
        }
        $this->assertTrue($blocked, 'Token without mcp:pos:read must be blocked from list action');

        $createRes = $posTool->execute($this->business, $this->user, [
            'action' => 'create',
            'location_id' => $this->mainWarehouse->id,
            'payment_method' => 'cash',
            'customer_name' => 'Walk-in Customer',
            'items' => [
                [
                    'name' => 'Kopi Tubruk',
                    'quantity' => 2,
                    'unit_price' => 15000,
                ],
            ],
            'paid_amount' => 30000,
        ]);

        $this->assertTrue($createRes['success']);
        $this->assertEquals(30000, $createRes['transaction']['total']);
    }

    public function test_full_access_wildcard_token_allows_all_operations(): void
    {
        $tokenData = McpAccessToken::generateToken(
            $this->business,
            $this->user,
            'Owner Full Access Token',
            ['*']
        );
        $wildcardToken = $tokenData['model'];

        Context::setMcpToken($wildcardToken);

        // 1. Settings Tool Get & Update
        $settingsTool = $this->registry->getTool('settings_manage_business');
        $getSettings = $settingsTool->execute($this->business, $this->user, ['action' => 'get']);
        $this->assertTrue($getSettings['success']);
        $this->assertEquals('PT COOCA Super Mandiri', $getSettings['business']['name']);

        $updateSettings = $settingsTool->execute($this->business, $this->user, [
            'action' => 'update',
            'pos_enable_tax' => true,
            'pos_tax_percent' => 12,
            'pos_receipt_footer_note' => 'Selamat Menikmati!',
        ]);
        $this->assertTrue($updateSettings['success']);
        $this->assertEquals(12, $updateSettings['business']['tax']['tax_percent']);

        // 2. CRM Tool Create & List
        $crmTool = $this->registry->getTool('crm_manage_customer');
        $createCust = $crmTool->execute($this->business, $this->user, [
            'action' => 'create',
            'name' => 'Siti Khadijah',
            'phone' => '081234567890',
            'email' => 'siti@example.com',
            'loyalty_points' => 150,
        ]);
        $this->assertTrue($createCust['success']);
        $this->assertEquals('Siti Khadijah', $createCust['customer']['name']);

        $listCust = $crmTool->execute($this->business, $this->user, ['action' => 'list', 'search' => 'Siti']);
        $this->assertTrue($listCust['success']);
        $this->assertCount(1, $listCust['customers']);

        // 3. HRM Tool Create & List & Attendance
        $hrmTool = $this->registry->getTool('hrm_manage_employee');
        $createEmp = $hrmTool->execute($this->business, $this->user, [
            'action' => 'create',
            'name' => 'Ahmad Rasyid',
            'email' => 'ahmad@example.com',
            'phone' => '08987654321',
            'job_title' => 'Supervisor Toko',
            'base_salary' => 4500000,
        ]);
        $this->assertTrue($createEmp['success']);
        $empId = $createEmp['employee']['id'];

        $clockIn = $hrmTool->execute($this->business, $this->user, [
            'action' => 'record_attendance',
            'employee_id' => $empId,
            'status' => 'present',
            'notes' => 'Tepat waktu',
        ]);
        $this->assertTrue($clockIn['success']);

        // 4. Inventory Stock Transfer Tool
        $transferTool = $this->registry->getTool('inventory_transfer_stock');
        $createTransfer = $transferTool->execute($this->business, $this->user, [
            'action' => 'create',
            'source_location_id' => $this->mainWarehouse->id,
            'destination_location_id' => $this->secondaryWarehouse->id,
            'items' => [
                [
                    'material_id' => $this->material->id,
                    'quantity' => 20,
                ],
            ],
            'notes' => 'Pengiriman stok mingguan ke cabang',
        ]);
        $this->assertTrue($createTransfer['success']);
        $this->assertContains($createTransfer['transfer']['status'], ['in_transit', 'received', 'completed']);

        // 5. Approval Workflow Tool
        $approvalTool = $this->registry->getTool('approval_manage_workflow');
        $approvalsList = $approvalTool->execute($this->business, $this->user, ['action' => 'list']);
        $this->assertTrue($approvalsList['success']);
    }
}
