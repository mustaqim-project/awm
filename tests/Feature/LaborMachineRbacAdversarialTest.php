<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Business;
use App\Models\LaborRate;
use App\Models\Machine;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class LaborMachineRbacAdversarialTest extends TestCase
{
    use RefreshDatabase;

    private Business $businessA;
    private Business $businessB;
    private User $ownerA;
    private User $ownerB;
    private User $unauthorizedStaff;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->seed(RbacSeeder::class);

        // Business A
        $this->businessA = Business::create([
            'name' => 'PT Manufaktur Maju A',
            'slug' => 'pt-manufaktur-maju-a',
            'currency' => 'IDR',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'disabled_modules' => [],
        ]);

        $this->ownerA = User::create([
            'name' => 'Owner A',
            'email' => 'owner_a@cooca.test',
            'phone' => '628111111111',
            'password' => 'password',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);
        $this->businessA->users()->attach($this->ownerA->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);
        $this->ownerA->update(['active_business_id' => $this->businessA->id]);

        // Staff in Business A with warehouse role (has NO labor_machines.manage or labor_machines.view)
        $this->unauthorizedStaff = User::create([
            'name' => 'Staff Warehouse',
            'email' => 'staff_wh@cooca.test',
            'phone' => '628222222222',
            'password' => 'password',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);
        $this->businessA->users()->attach($this->unauthorizedStaff->id, [
            'id' => (string) Str::uuid(),
            'role' => 'warehouse',
        ]);
        $this->unauthorizedStaff->update(['active_business_id' => $this->businessA->id]);

        // Business B
        $this->businessB = Business::create([
            'name' => 'PT Manufaktur Maju B',
            'slug' => 'pt-manufaktur-maju-b',
            'currency' => 'IDR',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'disabled_modules' => [],
        ]);

        $this->ownerB = User::create([
            'name' => 'Owner B',
            'email' => 'owner_b@cooca.test',
            'phone' => '628333333333',
            'password' => 'password',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);
        $this->businessB->users()->attach($this->ownerB->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);
        $this->ownerB->update(['active_business_id' => $this->businessB->id]);
    }

    public function test_unauthenticated_requests_are_rejected(): void
    {
        // 1. HTML requests redirect to login
        $this->get('/labor-machines')->assertRedirect(route('login'));
        $this->post('/labor-rates', [])->assertRedirect(route('login'));
        $this->put('/labor-rates/1', [])->assertRedirect(route('login'));
        $this->delete('/labor-rates/1')->assertRedirect(route('login'));

        $this->post('/machines', [])->assertRedirect(route('login'));
        $this->put('/machines/1', [])->assertRedirect(route('login'));
        $this->delete('/machines/1')->assertRedirect(route('login'));

        $this->get('/invoices')->assertRedirect(route('login'));
        $this->get('/invoices/create')->assertRedirect(route('login'));
        $this->post('/invoices', [])->assertRedirect(route('login'));
        $this->put('/invoices/1', [])->assertRedirect(route('login'));
        $this->delete('/invoices/1')->assertRedirect(route('login'));

        // 2. JSON requests receive 401 Unauthorized
        $this->withHeader('Accept', 'application/json')->get('/labor-machines')->assertUnauthorized();
        $this->withHeader('Accept', 'application/json')->post('/labor-rates', [])->assertUnauthorized();
        $this->withHeader('Accept', 'application/json')->put('/labor-rates/1', [])->assertUnauthorized();
        $this->withHeader('Accept', 'application/json')->delete('/labor-rates/1')->assertUnauthorized();

        $this->withHeader('Accept', 'application/json')->post('/machines', [])->assertUnauthorized();
        $this->withHeader('Accept', 'application/json')->put('/machines/1', [])->assertUnauthorized();
        $this->withHeader('Accept', 'application/json')->delete('/machines/1')->assertUnauthorized();

        $this->withHeader('Accept', 'application/json')->get('/invoices')->assertUnauthorized();
        $this->withHeader('Accept', 'application/json')->post('/invoices', [])->assertUnauthorized();
    }

    public function test_user_without_labor_machines_view_cannot_view_index(): void
    {
        Context::setBusiness($this->businessA);

        // HTML request redirects to portal
        $this->actingAs($this->unauthorizedStaff, 'web')
            ->get('/labor-machines')
            ->assertRedirect(route('portal'));

        // JSON request returns 403 Forbidden
        $this->actingAs($this->unauthorizedStaff, 'web')
            ->withHeader('Accept', 'application/json')
            ->get('/labor-machines')
            ->assertForbidden();
    }

    public function test_user_without_labor_machines_manage_cannot_post_put_delete_labor_rates(): void
    {
        Context::setBusiness($this->businessA);

        $laborRate = LaborRate::create([
            'business_id' => $this->businessA->id,
            'name' => 'Operator Jahit Senior',
            'basis' => LaborRate::BASIS_MONTHLY,
            'rate_amount' => 3500000,
            'working_days_per_month' => 22,
            'working_hours_per_day' => 8,
            'utilization_rate' => 85,
        ]);

        // Attempt POST /labor-rates as unauthorized staff
        $postRes = $this->actingAs($this->unauthorizedStaff, 'web')
            ->withHeader('Accept', 'application/json')
            ->post('/labor-rates', [
                'name' => 'Operator Baru Hacked',
                'basis' => 'monthly',
                'rate_amount' => 5000000,
                'working_days_per_month' => 20,
                'working_hours_per_day' => 8,
                'utilization_rate' => 80,
            ]);
        $postRes->assertForbidden();
        $this->assertDatabaseMissing('labor_rates', ['name' => 'Operator Baru Hacked']);

        // Attempt PUT /labor-rates/{id}
        $putRes = $this->actingAs($this->unauthorizedStaff, 'web')
            ->withHeader('Accept', 'application/json')
            ->put("/labor-rates/{$laborRate->id}", [
                'name' => 'Operator Jahit Modified',
                'basis' => 'monthly',
                'rate_amount' => 9999999,
                'working_days_per_month' => 22,
                'working_hours_per_day' => 8,
                'utilization_rate' => 85,
            ]);
        $putRes->assertForbidden();
        $this->assertDatabaseHas('labor_rates', [
            'id' => $laborRate->id,
            'name' => 'Operator Jahit Senior',
            'rate_amount' => 3500000,
        ]);

        // Attempt DELETE /labor-rates/{id}
        $delRes = $this->actingAs($this->unauthorizedStaff, 'web')
            ->withHeader('Accept', 'application/json')
            ->delete("/labor-rates/{$laborRate->id}");
        $delRes->assertForbidden();
        $this->assertDatabaseHas('labor_rates', ['id' => $laborRate->id]);
    }

    public function test_user_without_labor_machines_manage_cannot_post_put_delete_machines(): void
    {
        Context::setBusiness($this->businessA);

        $machine = Machine::create([
            'business_id' => $this->businessA->id,
            'name' => 'Mesin Jahit Obras Brother',
            'purchase_price' => 12000000,
            'residual_value' => 2000000,
            'useful_life_hours' => 5000,
            'electricity_cost_per_hour' => 1125,
            'maintenance_cost_per_hour' => 500,
        ]);

        // Attempt POST /machines
        $postRes = $this->actingAs($this->unauthorizedStaff, 'web')
            ->withHeader('Accept', 'application/json')
            ->post('/machines', [
                'name' => 'Mesin Laser CNC Hacked',
                'purchase_price' => 80000000,
                'useful_life_hours' => 6000,
                'salvage_value' => 10000000,
            ]);
        $postRes->assertForbidden();
        $this->assertDatabaseMissing('machines', ['name' => 'Mesin Laser CNC Hacked']);

        // Attempt PUT /machines/{id}
        $putRes = $this->actingAs($this->unauthorizedStaff, 'web')
            ->withHeader('Accept', 'application/json')
            ->put("/machines/{$machine->id}", [
                'name' => 'Mesin Jahit Obras Modified',
                'purchase_price' => 99999999,
                'useful_life_hours' => 5000,
                'salvage_value' => 2000000,
            ]);
        $putRes->assertForbidden();
        $this->assertDatabaseHas('machines', [
            'id' => $machine->id,
            'name' => 'Mesin Jahit Obras Brother',
            'purchase_price' => 12000000,
        ]);

        // Attempt DELETE /machines/{id}
        $delRes = $this->actingAs($this->unauthorizedStaff, 'web')
            ->withHeader('Accept', 'application/json')
            ->delete("/machines/{$machine->id}");
        $delRes->assertForbidden();
        $this->assertDatabaseHas('machines', ['id' => $machine->id]);
    }

    public function test_cross_tenant_idor_is_strictly_blocked(): void
    {
        Context::setBusiness($this->businessA);

        // Record belongs to Business B
        $rateB = LaborRate::create([
            'business_id' => $this->businessB->id,
            'name' => 'Tukang Pola B',
            'basis' => LaborRate::BASIS_HOURLY,
            'rate_amount' => 45000,
            'working_days_per_month' => 20,
            'working_hours_per_day' => 8,
            'utilization_rate' => 80,
        ]);

        $machineB = Machine::create([
            'business_id' => $this->businessB->id,
            'name' => 'Mesin Sablon Digital B',
            'purchase_price' => 25000000,
            'residual_value' => 5000000,
            'useful_life_hours' => 5000,
        ]);

        // Owner A (from Business A) attempts to PUT or DELETE records belonging to Business B
        // First line of defense (BusinessScope) returns 404; if bypassed, second line in controller returns 403.
        // In either case, the request is blocked and never succeeds (200/302).
        $putLaborRes = $this->actingAs($this->ownerA, 'web')
            ->put("/labor-rates/{$rateB->id}", [
                'name' => 'Cross-Tenant Hijack Attempt',
                'basis' => 'hourly',
                'rate_amount' => 999999,
                'working_days_per_month' => 20,
                'working_hours_per_day' => 8,
                'utilization_percentage' => 80,
            ]);
        $this->assertContains($putLaborRes->getStatusCode(), [403, 404]);

        $delLaborRes = $this->actingAs($this->ownerA, 'web')
            ->delete("/labor-rates/{$rateB->id}");
        $this->assertContains($delLaborRes->getStatusCode(), [403, 404]);

        $putMachineRes = $this->actingAs($this->ownerA, 'web')
            ->put("/machines/{$machineB->id}", [
                'name' => 'Cross-Tenant Hijack Attempt',
                'purchase_price' => 9999999,
                'useful_life_hours' => 5000,
                'salvage_value' => 5000000,
            ]);
        $this->assertContains($putMachineRes->getStatusCode(), [403, 404]);

        $delMachineRes = $this->actingAs($this->ownerA, 'web')
            ->delete("/machines/{$machineB->id}");
        $this->assertContains($delMachineRes->getStatusCode(), [403, 404]);

        // Verify records in Business B remain untouched
        $this->assertDatabaseHas('labor_rates', [
            'id' => $rateB->id,
            'name' => 'Tukang Pola B',
            'rate_amount' => 45000,
            'business_id' => $this->businessB->id,
        ]);
        $this->assertDatabaseHas('machines', [
            'id' => $machineB->id,
            'name' => 'Mesin Sablon Digital B',
            'business_id' => $this->businessB->id,
        ]);
    }

    public function test_authorized_user_with_manage_permission_can_crud_and_emits_audit_logs(): void
    {
        Context::setBusiness($this->businessA);

        // 1. Create Labor Rate
        $createLabor = $this->actingAs($this->ownerA, 'web')
            ->post('/labor-rates', [
                'name' => 'Kepala Produksi Pabrik',
                'basis' => 'monthly',
                'rate_amount' => 8000000,
                'working_days_per_month' => 22,
                'working_hours_per_day' => 8,
                'utilization_percentage' => 85,
                'is_subcontractor' => false,
            ]);
        $createLabor->assertRedirect(route('labor-machines.index'));
        $createLabor->assertSessionHas('success');

        $laborRate = LaborRate::where('name', 'Kepala Produksi Pabrik')->firstOrFail();
        $this->assertEquals($this->businessA->id, $laborRate->business_id);

        $auditCreateLabor = AuditLog::where('auditable_type', LaborRate::class)
            ->where('auditable_id', $laborRate->id)
            ->where('action', 'labor_rates.created')
            ->first();
        $this->assertNotNull($auditCreateLabor);

        // 2. Update Labor Rate
        $updateLabor = $this->actingAs($this->ownerA, 'web')
            ->put("/labor-rates/{$laborRate->id}", [
                'name' => 'Kepala Produksi Senior Pabrik',
                'basis' => 'monthly',
                'rate_amount' => 9000000,
                'working_days_per_month' => 22,
                'working_hours_per_day' => 8,
                'utilization_percentage' => 85,
                'is_subcontractor' => false,
            ]);
        $updateLabor->assertRedirect(route('labor-machines.index'));
        $updateLabor->assertSessionHas('success');

        $laborRate->refresh();
        $this->assertEquals('Kepala Produksi Senior Pabrik', $laborRate->name);
        $this->assertEquals(9000000, (int) $laborRate->rate_amount);

        $auditUpdateLabor = AuditLog::where('auditable_type', LaborRate::class)
            ->where('auditable_id', $laborRate->id)
            ->where('action', 'labor_rates.updated')
            ->first();
        $this->assertNotNull($auditUpdateLabor);

        // 3. Delete Labor Rate
        $deleteLabor = $this->actingAs($this->ownerA, 'web')
            ->delete("/labor-rates/{$laborRate->id}");
        $deleteLabor->assertRedirect(route('labor-machines.index'));
        $deleteLabor->assertSessionHas('success');

        $this->assertSoftDeleted('labor_rates', ['id' => $laborRate->id]);

        $auditDeleteLabor = AuditLog::where('auditable_type', LaborRate::class)
            ->where('auditable_id', $laborRate->id)
            ->where('action', 'labor_rates.deleted')
            ->first();
        $this->assertNotNull($auditDeleteLabor);

        // 4. Create Machine
        $createMachine = $this->actingAs($this->ownerA, 'web')
            ->post('/machines', [
                'name' => 'Mesin Bordir Komputer Tajima',
                'purchase_price' => 150000000,
                'salvage_value' => 20000000,
                'useful_life_hours' => 17500,
                'power_kw' => 2.5,
                'electricity_cost_per_kwh' => 1500,
                'maintenance_cost_per_hour' => 2000,
            ]);
        $createMachine->assertRedirect(route('labor-machines.index'));
        $createMachine->assertSessionHas('success');

        $machine = Machine::where('name', 'Mesin Bordir Komputer Tajima')->firstOrFail();
        $this->assertEquals($this->businessA->id, $machine->business_id);

        $auditCreateMachine = AuditLog::where('auditable_type', Machine::class)
            ->where('auditable_id', $machine->id)
            ->where('action', 'machines.created')
            ->first();
        $this->assertNotNull($auditCreateMachine);

        // 5. Update Machine
        $updateMachine = $this->actingAs($this->ownerA, 'web')
            ->put("/machines/{$machine->id}", [
                'name' => 'Mesin Bordir Komputer Tajima 12 Head',
                'purchase_price' => 160000000,
                'salvage_value' => 20000000,
                'useful_life_hours' => 17500,
                'power_kw' => 2.5,
                'electricity_cost_per_kwh' => 1500,
                'maintenance_cost_per_hour' => 2000,
            ]);
        $updateMachine->assertRedirect(route('labor-machines.index'));
        $updateMachine->assertSessionHas('success');

        $machine->refresh();
        $this->assertEquals('Mesin Bordir Komputer Tajima 12 Head', $machine->name);

        $auditUpdateMachine = AuditLog::where('auditable_type', Machine::class)
            ->where('auditable_id', $machine->id)
            ->where('action', 'machines.updated')
            ->first();
        $this->assertNotNull($auditUpdateMachine);

        // 6. Delete Machine
        $deleteMachine = $this->actingAs($this->ownerA, 'web')
            ->delete("/machines/{$machine->id}");
        $deleteMachine->assertRedirect(route('labor-machines.index'));
        $deleteMachine->assertSessionHas('success');

        $this->assertSoftDeleted('machines', ['id' => $machine->id]);

        $auditDeleteMachine = AuditLog::where('auditable_type', Machine::class)
            ->where('auditable_id', $machine->id)
            ->where('action', 'machines.deleted')
            ->first();
        $this->assertNotNull($auditDeleteMachine);
    }
}
