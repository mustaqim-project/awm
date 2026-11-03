<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Domain\Pos\PosShiftService;
use App\Models\Business;
use App\Models\Location;
use App\Models\Permission;
use App\Models\PosRegister;
use App\Models\PosShift;
use App\Models\Role;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

final class PosShiftRemediationTest extends TestCase
{
    use RefreshDatabase;

    private Business $businessA;
    private Business $businessB;
    private Location $locationA;
    private Location $locationB;
    private PosRegister $registerA1;
    private PosRegister $registerA2;
    private PosRegister $registerB1;
    private User $ownerA;
    private User $cashier1;
    private User $cashier2;
    private Role $roleCashierA;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        // 1. Permissions & Roles
        $permTerminal = Permission::firstOrCreate(['slug' => 'pos.terminal'], ['name' => 'POS Terminal', 'module' => 'pos']);
        $permOrders = Permission::firstOrCreate(['slug' => 'pos.orders'], ['name' => 'POS Orders', 'module' => 'pos']);

        // 2. Businesses
        $this->businessA = Business::create([
            'id' => (string) Str::uuid(),
            'name' => 'Kopi Nusantara Prima',
            'slug' => 'kopi-nusantara-prima',
            'is_active' => true,
        ]);

        $this->businessB = Business::create([
            'id' => (string) Str::uuid(),
            'name' => 'Resto Bintang Lima',
            'slug' => 'resto-bintang-lima',
            'is_active' => true,
        ]);

        // 3. Locations
        $this->locationA = Location::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->businessA->id,
            'name' => 'Outlet Jakarta Pusat',
            'is_primary' => true,
            'is_active' => true,
        ]);

        $this->locationB = Location::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->businessB->id,
            'name' => 'Outlet Bandung',
            'is_primary' => true,
            'is_active' => true,
        ]);

        // 4. POS Registers
        $this->registerA1 = PosRegister::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->businessA->id,
            'location_id' => $this->locationA->id,
            'name' => 'POS Register 01 (Front)',
            'is_active' => true,
        ]);

        $this->registerA2 = PosRegister::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->businessA->id,
            'location_id' => $this->locationA->id,
            'name' => 'POS Register 02 (Drive Thru)',
            'is_active' => true,
        ]);

        $this->registerB1 = PosRegister::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->businessB->id,
            'location_id' => $this->locationB->id,
            'name' => 'POS Register Tenant B',
            'is_active' => true,
        ]);

        // 5. Cashier Role with POS permissions
        $this->roleCashierA = Role::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->businessA->id,
            'name' => 'Kasir POS',
            'slug' => 'cashier',
        ]);
        $this->roleCashierA->permissions()->attach([$permTerminal->id, $permOrders->id]);

        // 6. Users
        $this->ownerA = User::create([
            'name' => 'Owner Bisnis A',
            'email' => 'owner.a@example.com',
            'password' => bcrypt('password123'),
        ]);
        $this->businessA->users()->attach($this->ownerA->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);
        $this->businessA->update(['owner_id' => $this->ownerA->id]);
        $this->ownerA->update(['active_business_id' => $this->businessA->id]);

        $this->cashier1 = User::create([
            'name' => 'Budi Kasir 1',
            'email' => 'budi@example.com',
            'password' => bcrypt('password123'),
        ]);
        $this->businessA->users()->attach($this->cashier1->id, [
            'id' => (string) Str::uuid(),
            'role' => 'cashier',
            'role_id' => $this->roleCashierA->id,
            'primary_location_id' => $this->locationA->id,
            'is_active' => true,
        ]);
        $this->cashier1->update(['active_business_id' => $this->businessA->id]);

        $this->cashier2 = User::create([
            'name' => 'Siti Kasir 2',
            'email' => 'siti@example.com',
            'password' => bcrypt('password123'),
        ]);
        $this->businessA->users()->attach($this->cashier2->id, [
            'id' => (string) Str::uuid(),
            'role' => 'cashier',
            'role_id' => $this->roleCashierA->id,
            'primary_location_id' => $this->locationA->id,
            'is_active' => true,
        ]);
        $this->cashier2->update(['active_business_id' => $this->businessA->id]);
    }

    /**
     * Requirement 3: Cashier shift isolation prevents leaking another cashier's shift.
     */
    public function test_cashier_shift_isolation_regular_cashier_does_not_see_another_cashiers_open_shift(): void
    {
        // 1. Cashier 1 opens shift on Register 1
        $shift1 = PosShift::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->businessA->id,
            'location_id' => $this->locationA->id,
            'pos_register_id' => $this->registerA1->id,
            'user_id' => $this->cashier1->id,
            'opened_at' => now(),
            'opening_cash' => 100000.0,
            'status' => PosShift::STATUS_OPEN,
        ]);

        // 2. Cashier 2 queries active shift via API
        Sanctum::actingAs($this->cashier2, ['*']);
        Context::setBusiness($this->businessA);

        $responseCashier2 = $this->withHeaders([
            'X-Business-Id' => $this->businessA->id,
            'Accept' => 'application/json',
        ])->getJson('/api/v1/pos/shifts/active?location_id=' . $this->locationA->id);

        $responseCashier2->assertStatus(Response::HTTP_OK);
        $responseCashier2->assertJson(['success' => true]);
        $this->assertNull($responseCashier2->json('active_shift'), 'Regular Cashier 2 must NOT receive Cashier 1 open shift');

        // 3. Cashier 1 queries active shift via API
        Sanctum::actingAs($this->cashier1, ['*']);
        Context::setBusiness($this->businessA);

        $responseCashier1 = $this->withHeaders([
            'X-Business-Id' => $this->businessA->id,
            'Accept' => 'application/json',
        ])->getJson('/api/v1/pos/shifts/active?location_id=' . $this->locationA->id);

        $responseCashier1->assertStatus(Response::HTTP_OK);
        $responseCashier1->assertJson(['success' => true]);
        $this->assertNotNull($responseCashier1->json('active_shift'));
        $this->assertEquals($shift1->id, $responseCashier1->json('active_shift.id'));
    }

    /**
     * Requirement 3: Supervisor / Owner can monitor active shifts when they do not have a personal open shift.
     */
    public function test_supervisor_can_inspect_active_shift_when_personal_shift_is_absent(): void
    {
        $shift1 = PosShift::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->businessA->id,
            'location_id' => $this->locationA->id,
            'pos_register_id' => $this->registerA1->id,
            'user_id' => $this->cashier1->id,
            'opened_at' => now(),
            'opening_cash' => 150000.0,
            'status' => PosShift::STATUS_OPEN,
        ]);

        Sanctum::actingAs($this->ownerA, ['*']);
        Context::setBusiness($this->businessA);

        $responseOwner = $this->withHeaders([
            'X-Business-Id' => $this->businessA->id,
            'Accept' => 'application/json',
        ])->getJson('/api/v1/pos/shifts/active?location_id=' . $this->locationA->id);

        $responseOwner->assertStatus(Response::HTTP_OK);
        $responseOwner->assertJson(['success' => true]);
        $this->assertNotNull($responseOwner->json('active_shift'));
        $this->assertEquals($shift1->id, $responseOwner->json('active_shift.id'));
    }

    /**
     * Requirement 4: Register concurrency guard blocks second cashier from hijacking same register.
     */
    public function test_register_concurrency_guard_rejects_second_cashier_on_same_register(): void
    {
        // 1. Cashier 1 opens Register 1
        PosShift::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->businessA->id,
            'location_id' => $this->locationA->id,
            'pos_register_id' => $this->registerA1->id,
            'user_id' => $this->cashier1->id,
            'opened_at' => now(),
            'opening_cash' => 100000.0,
            'status' => PosShift::STATUS_OPEN,
        ]);

        // 2. Cashier 2 tries to open same Register 1
        Sanctum::actingAs($this->cashier2, ['*']);
        Context::setBusiness($this->businessA);

        $responseCashier2 = $this->withHeaders([
            'X-Business-Id' => $this->businessA->id,
            'Accept' => 'application/json',
        ])->postJson('/api/v1/pos/shifts/open', [
            'opening_cash' => 200000,
            'pos_register_id' => $this->registerA1->id,
            'location_id' => $this->locationA->id,
            'notes' => 'Mencoba hijack register 1',
        ]);

        $responseCashier2->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $responseCashier2->assertJson([
            'success' => false,
        ]);
        $this->assertStringContainsString('sedang digunakan', $responseCashier2->json('message'));
    }

    /**
     * Requirement 4: Opening shift is idempotent for the same cashier on the same register.
     */
    public function test_register_concurrency_idempotent_for_same_cashier(): void
    {
        Sanctum::actingAs($this->cashier1, ['*']);
        Context::setBusiness($this->businessA);

        // First open
        $res1 = $this->withHeaders([
            'X-Business-Id' => $this->businessA->id,
            'Accept' => 'application/json',
        ])->postJson('/api/v1/pos/shifts/open', [
            'opening_cash' => 150000,
            'pos_register_id' => $this->registerA1->id,
            'location_id' => $this->locationA->id,
            'notes' => 'Buka kasir pagi',
        ]);
        $res1->assertStatus(Response::HTTP_CREATED);
        $shiftId1 = $res1->json('data.id');

        // Second call with same cashier
        $res2 = $this->withHeaders([
            'X-Business-Id' => $this->businessA->id,
            'Accept' => 'application/json',
        ])->postJson('/api/v1/pos/shifts/open', [
            'opening_cash' => 150000,
            'pos_register_id' => $this->registerA1->id,
            'location_id' => $this->locationA->id,
        ]);
        $res2->assertStatus(Response::HTTP_CREATED);
        $shiftId2 = $res2->json('data.id');

        $this->assertEquals($shiftId1, $shiftId2);
    }

    /**
     * Tenant integrity: Opening shift with cross-tenant location or register returns HTTP 422.
     */
    public function test_open_shift_validates_tenant_ownership_of_location_and_register(): void
    {
        Sanctum::actingAs($this->cashier1, ['*']);
        Context::setBusiness($this->businessA);

        // Cross-tenant location (Location B belongs to Business B)
        $resLocation = $this->withHeaders([
            'X-Business-Id' => $this->businessA->id,
            'Accept' => 'application/json',
        ])->postJson('/api/v1/pos/shifts/open', [
            'opening_cash' => 100000,
            'location_id' => $this->locationB->id,
        ]);
        $resLocation->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $resLocation->assertJson([
            'success' => false,
            'message' => 'Lokasi yang dipilih tidak valid untuk bisnis ini.',
        ]);

        // Cross-tenant register (Register B1 belongs to Business B)
        $resRegister = $this->withHeaders([
            'X-Business-Id' => $this->businessA->id,
            'Accept' => 'application/json',
        ])->postJson('/api/v1/pos/shifts/open', [
            'opening_cash' => 100000,
            'location_id' => $this->locationA->id,
            'pos_register_id' => $this->registerB1->id,
        ]);
        $resRegister->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $resRegister->assertJson([
            'success' => false,
            'message' => 'Register / Terminal POS tidak valid untuk bisnis ini.',
        ]);
    }

    /**
     * Requirement 6: Denominations breakdown auto-calculates opening float.
     */
    public function test_open_shift_with_denominations_auto_calculates_opening_cash(): void
    {
        Sanctum::actingAs($this->cashier1, ['*']);
        Context::setBusiness($this->businessA);

        $response = $this->withHeaders([
            'X-Business-Id' => $this->businessA->id,
            'Accept' => 'application/json',
        ])->postJson('/api/v1/pos/shifts/open', [
            'opening_cash' => 0,
            'pos_register_id' => $this->registerA1->id,
            'location_id' => $this->locationA->id,
            'opening_denominations' => [
                '100000' => 2, // 200.000
                '50000' => 1,  // 50.000
                'coins' => 5000, // 5.000
            ],
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertJson(['success' => true]);
        $this->assertEquals(255000.0, (float) $response->json('data.opening_cash'));
    }

    /**
     * Shift close calculates difference and updates status to closed.
     */
    public function test_close_shift_calculates_variance_and_updates_status(): void
    {
        $shift = PosShift::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->businessA->id,
            'location_id' => $this->locationA->id,
            'pos_register_id' => $this->registerA1->id,
            'user_id' => $this->cashier1->id,
            'opened_at' => now(),
            'opening_cash' => 200000.0,
            'status' => PosShift::STATUS_OPEN,
        ]);

        Sanctum::actingAs($this->cashier1, ['*']);
        Context::setBusiness($this->businessA);

        $response = $this->withHeaders([
            'X-Business-Id' => $this->businessA->id,
            'Accept' => 'application/json',
        ])->postJson("/api/v1/pos/shifts/{$shift->id}/close", [
            'closing_cash_actual' => 205000,
            'cashier_notes' => 'Lebih 5.000 tip kasir',
        ]);

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson(['success' => true]);
        $this->assertEquals('closed', $response->json('data.status'));
        $this->assertEquals(205000.0, (float) $response->json('data.closing_cash_actual'));

        $shift->refresh();
        $this->assertEquals(PosShift::STATUS_CLOSED, $shift->status);
    }
}
