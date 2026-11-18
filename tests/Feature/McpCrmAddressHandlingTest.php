<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Mcp\Tools\CrmManageCustomerTool;
use App\Domain\Mcp\Tools\CrmSearchCustomerTool;
use App\Domain\Mcp\Tools\McpToolRegistry;
use App\Models\Business;
use App\Models\Customer;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class McpCrmAddressHandlingTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $user;
    private McpToolRegistry $registry;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->seed(RbacSeeder::class);

        $this->user = User::create([
            'name' => 'Owner CRM Tester',
            'email' => 'owner_crm@example.com',
            'password' => 'secret123',
        ]);

        $this->business = Business::create([
            'name' => 'Toko Retail Sukses',
            'phone' => '081234567890',
            'email' => 'halo@tokosukses.id',
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

        $this->registry = app(McpToolRegistry::class);
    }

    public function test_customer_model_address_accessor_and_mutator_work_seamlessly(): void
    {
        // 1. Create customer with 'address' via Eloquent
        $customer = Customer::create([
            'business_id' => $this->business->id,
            'name' => 'Budi Santoso',
            'phone' => '081299998888',
            'address' => 'Jl. Diponegoro No. 15, Surabaya',
            'points' => 150,
        ]);

        $this->assertEquals('Jl. Diponegoro No. 15, Surabaya', $customer->address);
        $this->assertEquals('Jl. Diponegoro No. 15, Surabaya', $customer->billing_address);
        $this->assertEquals('Jl. Diponegoro No. 15, Surabaya', $customer->shipping_address);
        $this->assertEquals(150, $customer->points_balance);
        $this->assertEquals(150, $customer->points);

        // Verify array and JSON serialization contains 'address' and 'points'
        $array = $customer->toArray();
        $this->assertArrayHasKey('address', $array);
        $this->assertEquals('Jl. Diponegoro No. 15, Surabaya', $array['address']);
        $this->assertArrayHasKey('points', $array);
        $this->assertEquals(150, $array['points']);

        // 2. Update address directly via Eloquent - MUST NOT fail with SQL 'Unknown column address'
        $customer->address = 'Jl. Malioboro No. 20, Yogyakarta';
        $customer->save();

        $customer->refresh();
        $this->assertEquals('Jl. Malioboro No. 20, Yogyakarta', $customer->address);
        $this->assertEquals('Jl. Malioboro No. 20, Yogyakarta', $customer->billing_address);
        $this->assertEquals('Jl. Malioboro No. 20, Yogyakarta', $customer->shipping_address);
    }

    public function test_customer_model_gracefully_falls_back_to_notes_if_address_was_saved_there(): void
    {
        $customer = Customer::create([
            'business_id' => $this->business->id,
            'name' => 'Siti Aisyah',
            'phone' => '081377776666',
            'notes' => 'Pelanggan VIP. Alamat: Jl. Sudirman No. 88, Jakarta Selatan',
        ]);

        $this->assertNull($customer->billing_address);
        $this->assertNull($customer->shipping_address);
        $this->assertEquals('Jl. Sudirman No. 88, Jakarta Selatan', $customer->address);
    }

    public function test_crm_manage_customer_tool_create_with_address(): void
    {
        $tool = $this->registry->getTool('crm_manage_customer');
        $this->assertInstanceOf(CrmManageCustomerTool::class, $tool);

        $result = $tool->execute($this->business, $this->user, [
            'action' => 'create',
            'name' => 'Dewi Lestari',
            'phone' => '081234567800',
            'email' => 'dewi@example.com',
            'address' => 'Jl. Gatot Subroto No. 45, Bandung',
            'points' => 250,
            'notes' => 'Pelanggan loyal kopi arabika',
        ]);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('customer', $result);
        $this->assertEquals('Dewi Lestari', $result['customer']['name']);
        $this->assertEquals('Jl. Gatot Subroto No. 45, Bandung', $result['customer']['address']);
        $this->assertEquals('Jl. Gatot Subroto No. 45, Bandung', $result['customer']['billing_address']);
        $this->assertEquals('Jl. Gatot Subroto No. 45, Bandung', $result['customer']['shipping_address']);
        $this->assertEquals(250, $result['customer']['points']);

        // Assert DB
        $this->assertDatabaseHas('customers', [
            'business_id' => $this->business->id,
            'name' => 'Dewi Lestari',
            'phone' => '081234567800',
            'billing_address' => 'Jl. Gatot Subroto No. 45, Bandung',
            'shipping_address' => 'Jl. Gatot Subroto No. 45, Bandung',
            'points_balance' => 250,
        ]);
    }

    public function test_crm_manage_customer_tool_update_name_address_and_notes(): void
    {
        // 1. Initial customer created with address in notes (as reported by user)
        $customer = Customer::create([
            'business_id' => $this->business->id,
            'name' => 'Rudi Lama',
            'phone' => '081988887777',
            'notes' => 'Alamat: Jl. Veteran No. 5',
        ]);

        $tool = $this->registry->getTool('crm_manage_customer');

        // 2. Perform update: rename, set real address, and update notes
        $result = $tool->execute($this->business, $this->user, [
            'action' => 'update',
            'customer_id' => $customer->id,
            'name' => 'Rudi Pratama',
            'address' => 'Jl. Thamrin No. 99, Lantai 5, Jakarta Pusat',
            'notes' => 'Catatan diperbarui: Pelanggan B2B Corporate',
            'points' => 500,
        ]);

        $this->assertTrue($result['success']);
        $this->assertEquals('Rudi Pratama', $result['customer']['name']);
        $this->assertEquals('Jl. Thamrin No. 99, Lantai 5, Jakarta Pusat', $result['customer']['address']);
        $this->assertEquals('Jl. Thamrin No. 99, Lantai 5, Jakarta Pusat', $result['customer']['billing_address']);
        $this->assertEquals('Jl. Thamrin No. 99, Lantai 5, Jakarta Pusat', $result['customer']['shipping_address']);
        $this->assertEquals(500, $result['customer']['points']);
        $this->assertEquals('Catatan diperbarui: Pelanggan B2B Corporate', $result['customer']['notes']);

        // Verify DB persistence
        $customer->refresh();
        $this->assertEquals('Rudi Pratama', $customer->name);
        $this->assertEquals('Jl. Thamrin No. 99, Lantai 5, Jakarta Pusat', $customer->billing_address);
        $this->assertEquals('Jl. Thamrin No. 99, Lantai 5, Jakarta Pusat', $customer->shipping_address);
        $this->assertEquals('Jl. Thamrin No. 99, Lantai 5, Jakarta Pusat', $customer->address);
        $this->assertEquals(500, $customer->points_balance);
        $this->assertEquals('Catatan diperbarui: Pelanggan B2B Corporate', $customer->notes);
    }

    public function test_crm_manage_customer_tool_get_and_list_contain_address(): void
    {
        $customer = Customer::create([
            'business_id' => $this->business->id,
            'name' => 'Hendra Wijaya',
            'phone' => '081555554444',
            'billing_address' => 'Ruko Grand Wisata Blok A-10',
            'shipping_address' => 'Ruko Grand Wisata Blok A-10',
            'points_balance' => 80,
            'notes' => 'Member regular',
        ]);

        $tool = $this->registry->getTool('crm_manage_customer');

        // Test get
        $getResult = $tool->execute($this->business, $this->user, [
            'action' => 'get',
            'customer_id' => $customer->id,
        ]);
        $this->assertTrue($getResult['success']);
        $this->assertEquals('Ruko Grand Wisata Blok A-10', $getResult['customer']['address']);
        $this->assertEquals('Ruko Grand Wisata Blok A-10', $getResult['customer']['billing_address']);

        // Test list
        $listResult = $tool->execute($this->business, $this->user, ['action' => 'list']);
        $this->assertTrue($listResult['success']);
        $this->assertGreaterThanOrEqual(1, $listResult['count']);
        $found = collect($listResult['customers'])->firstWhere('id', $customer->id);
        $this->assertNotNull($found);
        $this->assertEquals('Ruko Grand Wisata Blok A-10', $found['address']);
    }

    public function test_crm_search_customer_tool_returns_and_finds_by_address(): void
    {
        $customer = Customer::create([
            'business_id' => $this->business->id,
            'name' => 'Farhan Kholil',
            'phone' => '087711223344',
            'billing_address' => 'Komplek Permata Hijau Kavling 7, Jakarta',
            'shipping_address' => 'Komplek Permata Hijau Kavling 7, Jakarta',
            'notes' => 'Alamat pengiriman prioritas',
        ]);

        $tool = $this->registry->getTool('crm_search_customer');
        $this->assertInstanceOf(CrmSearchCustomerTool::class, $tool);

        // Search by address keyword 'Permata Hijau'
        $result = $tool->execute($this->business, $this->user, [
            'query' => 'Permata Hijau',
        ]);

        $this->assertEquals('success', $result['status']);
        $this->assertGreaterThanOrEqual(1, $result['total_found']);
        $found = collect($result['customers'])->firstWhere('id', $customer->id);
        $this->assertNotNull($found);
        $this->assertEquals('Farhan Kholil', $found['name']);
        $this->assertEquals('Komplek Permata Hijau Kavling 7, Jakarta', $found['address']);
    }
}
