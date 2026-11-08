<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\Web\InvoiceWebController;
use App\Http\Controllers\Web\LaborMachineWebController;
use App\Models\AuditLog;
use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\LaborRate;
use App\Models\Machine;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Tests\TestCase;

final class LaborMachineRbacCleanRoutingEmpiricalTest extends TestCase
{
    use RefreshDatabase;

    private Business $businessA;
    private Business $businessB;
    private User $ownerA;
    private User $managerA;
    private User $viewerA;
    private User $unauthorizedA;
    private User $ownerB;

    private LaborRate $laborRateA;
    private Machine $machineA;

    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('id');
        Context::flush();

        // 1. Seed complete RBAC permission & role matrix
        $this->seed(RbacSeeder::class);

        // 2. Setup Business A
        $this->businessA = Business::create([
            'name' => 'PT Manufaktur Nusantara (Business A)',
            'currency' => 'IDR',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
        ]);

        BusinessSubscription::create([
            'business_id' => $this->businessA->id,
            'plan_code' => BusinessSubscription::PLAN_PREMIUM_MONTHLY,
            'status' => BusinessSubscription::STATUS_ACTIVE,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        // 3. Setup Business B (for cross-tenant IDOR attack simulation)
        $this->businessB = Business::create([
            'name' => 'CV Serangan Pihak Ketiga (Business B)',
            'currency' => 'IDR',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
        ]);

        BusinessSubscription::create([
            'business_id' => $this->businessB->id,
            'plan_code' => BusinessSubscription::PLAN_PREMIUM_MONTHLY,
            'status' => BusinessSubscription::STATUS_ACTIVE,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        // 4. Create Users for Business A
        $this->ownerA = $this->createUserForBusiness($this->businessA, 'owner', 'ownerA@example.com');
        $this->managerA = $this->createUserForBusiness($this->businessA, 'manager', 'managerA@example.com');
        $this->viewerA = $this->createUserForBusiness($this->businessA, 'viewer', 'viewerA@example.com');
        $this->unauthorizedA = $this->createUserForBusiness($this->businessA, 'warehouse', 'warehouseA@example.com');

        // 5. Create Owner for Business B
        $this->ownerB = $this->createUserForBusiness($this->businessB, 'owner', 'ownerB@example.com');

        // 6. Create Seed Data for Business A
        $this->laborRateA = LaborRate::create([
            'business_id' => $this->businessA->id,
            'name' => 'Operator CNC Khusus A',
            'basis' => 'hourly',
            'rate_amount' => 45000.0,
            'working_days_per_month' => 22,
            'working_hours_per_day' => 8.0,
            'utilization_rate' => 85.0,
            'is_subcontractor' => false,
        ]);

        $this->machineA = Machine::create([
            'business_id' => $this->businessA->id,
            'name' => 'Mesin Bubut CNC Model A',
            'purchase_price' => 120000000.0,
            'residual_value' => 12000000.0,
            'useful_life_hours' => 20000.0,
            'maintenance_cost_per_hour' => 15000.0,
            'electricity_cost_per_hour' => 11250.0,
        ]);
    }

    private function createUserForBusiness(Business $business, string $role, string $email): User
    {
        $user = User::create([
            'name' => 'User ' . ucfirst($role) . ' ' . $business->id,
            'email' => $email,
            'password' => 'password123',
            'active_business_id' => $business->id,
        ]);

        $business->users()->attach($user->id, [
            'id' => (string) Str::uuid(),
            'role' => $role,
        ]);

        return $user;
    }

    // =========================================================================
    // SECTION 1: HasMiddleware Contract Verification
    // =========================================================================

    public function test_labor_machine_web_controller_implements_has_middleware_and_declares_exact_contracts(): void
    {
        $controller = new LaborMachineWebController();
        $this->assertInstanceOf(HasMiddleware::class, $controller);

        $middleware = LaborMachineWebController::middleware();
        $this->assertIsArray($middleware);
        $this->assertCount(2, $middleware);

        // Find view middleware
        $viewMiddleware = null;
        $manageMiddleware = null;

        foreach ($middleware as $mw) {
            $this->assertInstanceOf(Middleware::class, $mw);
            $reflection = new \ReflectionClass($mw);
            $middlewareProperty = $reflection->getProperty('middleware');
            $middlewareProperty->setAccessible(true);
            $name = $middlewareProperty->getValue($mw);

            $onlyProperty = $reflection->getProperty('only');
            $onlyProperty->setAccessible(true);
            $only = $onlyProperty->getValue($mw);

            if ($name === 'require.permission:labor_machines.view') {
                $viewMiddleware = $only;
            } elseif ($name === 'require.permission:labor_machines.manage') {
                $manageMiddleware = $only;
            }
        }

        $this->assertNotNull($viewMiddleware, 'Missing require.permission:labor_machines.view middleware declaration');
        $this->assertEquals(['index'], $viewMiddleware);

        $this->assertNotNull($manageMiddleware, 'Missing require.permission:labor_machines.manage middleware declaration');
        $expectedManageActions = [
            'storeLabor',
            'updateLabor',
            'destroyLabor',
            'storeMachine',
            'updateMachine',
            'destroyMachine',
        ];
        sort($expectedManageActions);
        sort($manageMiddleware);
        $this->assertEquals($expectedManageActions, $manageMiddleware);
    }

    // =========================================================================
    // SECTION 2: Route Resolution & Naming Integrity
    // =========================================================================

    public function test_route_names_for_labor_machines_are_intact_and_resolve_correctly(): void
    {
        $expectedRoutes = [
            'labor-machines.index'   => ['GET', LaborMachineWebController::class, 'index'],
            'labor-rates.store'      => ['POST', LaborMachineWebController::class, 'storeLabor'],
            'labor-rates.update'     => ['PUT', LaborMachineWebController::class, 'updateLabor'],
            'labor-rates.destroy'    => ['DELETE', LaborMachineWebController::class, 'destroyLabor'],
            'machines.store'         => ['POST', LaborMachineWebController::class, 'storeMachine'],
            'machines.update'        => ['PUT', LaborMachineWebController::class, 'updateMachine'],
            'machines.destroy'       => ['DELETE', LaborMachineWebController::class, 'destroyMachine'],
        ];

        foreach ($expectedRoutes as $name => [$method, $controllerClass, $action]) {
            $this->assertTrue(Route::has($name), "Route [{$name}] is not registered in RouteCollection.");
            $route = Route::getRoutes()->getByName($name);
            $this->assertContains($method, $route->methods(), "Route [{$name}] does not support HTTP method {$method}.");
            $this->assertEquals($controllerClass . '@' . $action, $route->getActionName(), "Route [{$name}] does not point to {$controllerClass}@{$action}.");
        }
    }

    public function test_route_names_for_invoices_are_intact_and_resolve_correctly(): void
    {
        $expectedRoutes = [
            'invoices.create'         => ['GET', InvoiceWebController::class, 'create'],
            'invoices.store'          => ['POST', InvoiceWebController::class, 'store'],
            'invoices.export-excel'   => ['GET', InvoiceWebController::class, 'exportExcel'],
            'invoices.index'          => ['GET', InvoiceWebController::class, 'index'],
            'invoices.show'           => ['GET', InvoiceWebController::class, 'show'],
            'invoices.print'          => ['GET', InvoiceWebController::class, 'print'],
            'invoices.pdf'            => ['GET', InvoiceWebController::class, 'pdf'],
            'invoices.edit'           => ['GET', InvoiceWebController::class, 'edit'],
            'invoices.update'         => ['PUT', InvoiceWebController::class, 'update'],
            'invoices.payments.store' => ['POST', InvoiceWebController::class, 'recordPayment'],
            'invoices.confirm'        => ['POST', InvoiceWebController::class, 'confirm'],
            'invoices.remind'         => ['POST', InvoiceWebController::class, 'sendTermReminder'],
            'invoices.void'           => ['POST', InvoiceWebController::class, 'void'],
            'invoices.destroy'        => ['DELETE', InvoiceWebController::class, 'destroy'],
        ];

        foreach ($expectedRoutes as $name => [$method, $controllerClass, $action]) {
            $this->assertTrue(Route::has($name), "Route [{$name}] is not registered in RouteCollection.");
            $route = Route::getRoutes()->getByName($name);
            $this->assertContains($method, $route->methods(), "Route [{$name}] does not support HTTP method {$method}.");
            $this->assertEquals($controllerClass . '@' . $action, $route->getActionName(), "Route [{$name}] does not point to {$controllerClass}@{$action}.");
        }
    }

    // =========================================================================
    // SECTION 3: HTTP RBAC Enforcement (View vs Mutation Permissions)
    // =========================================================================

    public function test_unauthorized_user_lacking_labor_machines_view_cannot_access_index(): void
    {
        $response = $this->actingAs($this->unauthorizedA)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->get(route('labor-machines.index'));

        $response->assertStatus(403);
    }

    public function test_user_with_labor_machines_view_can_access_index_and_data_is_tenant_scoped(): void
    {
        // Create record belonging to Business B
        $rateB = LaborRate::create([
            'business_id' => $this->businessB->id,
            'name' => 'Tarif Rahasia Milik Business B',
            'basis' => 'hourly',
            'rate_amount' => 999999.0,
            'working_days_per_month' => 20,
            'working_hours_per_day' => 8.0,
            'utilization_rate' => 90.0,
            'is_subcontractor' => false,
        ]);

        $response = $this->actingAs($this->viewerA)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->get(route('labor-machines.index'));

        $response->assertOk();
        $response->assertSee('Operator CNC Khusus A');
        $response->assertDontSee('Tarif Rahasia Milik Business B');
    }

    public function test_user_lacking_labor_machines_manage_cannot_execute_mutations(): void
    {
        // 1. storeLabor
        $resStoreLabor = $this->actingAs($this->viewerA)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->post(route('labor-rates.store'), [
                'name' => 'Tukang Las Listrik',
                'basis' => 'daily',
                'rate_amount' => 150000,
            ]);
        $resStoreLabor->assertStatus(403);

        // 2. updateLabor
        $resUpdateLabor = $this->actingAs($this->viewerA)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->put(route('labor-rates.update', $this->laborRateA), [
                'name' => 'Operator CNC Diubah Tanpa Izin',
                'basis' => 'hourly',
                'rate_amount' => 50000,
            ]);
        $resUpdateLabor->assertStatus(403);

        // 3. destroyLabor
        $resDestroyLabor = $this->actingAs($this->viewerA)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->delete(route('labor-rates.destroy', $this->laborRateA));
        $resDestroyLabor->assertStatus(403);

        // 4. storeMachine
        $resStoreMachine = $this->actingAs($this->viewerA)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->post(route('machines.store'), [
                'name' => 'Mesin Press Otomatis',
                'purchase_price' => 50000000,
                'useful_life_hours' => 10000,
            ]);
        $resStoreMachine->assertStatus(403);

        // 5. updateMachine
        $resUpdateMachine = $this->actingAs($this->viewerA)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->put(route('machines.update', $this->machineA), [
                'name' => 'Mesin Bubut Diubah Tanpa Izin',
                'purchase_price' => 130000000,
                'useful_life_hours' => 20000,
            ]);
        $resUpdateMachine->assertStatus(403);

        // 6. destroyMachine
        $resDestroyMachine = $this->actingAs($this->viewerA)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->delete(route('machines.destroy', $this->machineA));
        $resDestroyMachine->assertStatus(403);
    }

    // =========================================================================
    // SECTION 4: Authorized Mutations and AuditLog Dispatching
    // =========================================================================

    public function test_authorized_user_can_execute_all_labor_and_machine_mutations_and_dispatches_audit_logs(): void
    {
        // 1. Store Labor Rate
        $resStoreLabor = $this->actingAs($this->ownerA)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->post(route('labor-rates.store'), [
                'name' => 'Operator Las TIG',
                'basis' => 'daily',
                'rate_amount' => 200000,
                'working_days_per_month' => 22,
                'working_hours_per_day' => 8,
                'utilization_percentage' => 85,
                'is_subcontractor' => 0,
            ]);
        $resStoreLabor->assertRedirect(route('labor-machines.index'));
        $newLaborRate = LaborRate::where('name', 'Operator Las TIG')->first();
        $this->assertNotNull($newLaborRate);
        $this->assertEquals($this->businessA->id, $newLaborRate->business_id);

        $auditCreated = AuditLog::where('auditable_type', LaborRate::class)
            ->where('auditable_id', $newLaborRate->id)
            ->where('action', 'labor_rates.created')
            ->first();
        $this->assertNotNull($auditCreated, 'AuditLog for labor_rates.created was not dispatched.');

        // 2. Update Labor Rate
        $resUpdateLabor = $this->actingAs($this->ownerA)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->put(route('labor-rates.update', $newLaborRate), [
                'name' => 'Operator Las TIG Senior',
                'basis' => 'daily',
                'rate_amount' => 250000,
                'working_days_per_month' => 22,
                'working_hours_per_day' => 8,
                'utilization_percentage' => 85,
                'is_subcontractor' => 0,
            ]);
        $resUpdateLabor->assertRedirect(route('labor-machines.index'));
        $newLaborRate->refresh();
        $this->assertEquals('Operator Las TIG Senior', $newLaborRate->name);
        $this->assertEquals(250000.0, (float) $newLaborRate->rate_amount);

        $auditUpdated = AuditLog::where('auditable_type', LaborRate::class)
            ->where('auditable_id', $newLaborRate->id)
            ->where('action', 'labor_rates.updated')
            ->first();
        $this->assertNotNull($auditUpdated, 'AuditLog for labor_rates.updated was not dispatched.');

        // 3. Delete Labor Rate
        $resDestroyLabor = $this->actingAs($this->ownerA)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->delete(route('labor-rates.destroy', $newLaborRate));
        $resDestroyLabor->assertRedirect(route('labor-machines.index'));
        $this->assertNull(LaborRate::find($newLaborRate->id));

        $auditDeleted = AuditLog::where('auditable_type', LaborRate::class)
            ->where('auditable_id', $newLaborRate->id)
            ->where('action', 'labor_rates.deleted')
            ->first();
        $this->assertNotNull($auditDeleted, 'AuditLog for labor_rates.deleted was not dispatched.');

        // 4. Store Machine
        $resStoreMachine = $this->actingAs($this->ownerA)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->post(route('machines.store'), [
                'name' => 'Laser Cutting Fiber 3000W',
                'purchase_price' => 450000000,
                'useful_life_hours' => 30000,
                'power_kw' => 15,
                'electricity_cost_per_kwh' => 1500,
                'maintenance_cost_per_hour' => 25000,
                'salvage_value' => 45000000,
            ]);
        $resStoreMachine->assertRedirect(route('labor-machines.index'));
        $newMachine = Machine::where('name', 'Laser Cutting Fiber 3000W')->first();
        $this->assertNotNull($newMachine);
        $this->assertEquals($this->businessA->id, $newMachine->business_id);

        $auditMachineCreated = AuditLog::where('auditable_type', Machine::class)
            ->where('auditable_id', $newMachine->id)
            ->where('action', 'machines.created')
            ->first();
        $this->assertNotNull($auditMachineCreated, 'AuditLog for machines.created was not dispatched.');

        // 5. Update Machine
        $resUpdateMachine = $this->actingAs($this->ownerA)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->put(route('machines.update', $newMachine), [
                'name' => 'Laser Cutting Fiber 3000W Gen 2',
                'purchase_price' => 450000000,
                'useful_life_hours' => 32000,
                'power_kw' => 15,
                'electricity_cost_per_kwh' => 1500,
                'maintenance_cost_per_hour' => 20000,
                'salvage_value' => 45000000,
            ]);
        $resUpdateMachine->assertRedirect(route('labor-machines.index'));
        $newMachine->refresh();
        $this->assertEquals('Laser Cutting Fiber 3000W Gen 2', $newMachine->name);
        $this->assertEquals(32000.0, (float) $newMachine->useful_life_hours);

        $auditMachineUpdated = AuditLog::where('auditable_type', Machine::class)
            ->where('auditable_id', $newMachine->id)
            ->where('action', 'machines.updated')
            ->first();
        $this->assertNotNull($auditMachineUpdated, 'AuditLog for machines.updated was not dispatched.');

        // 6. Delete Machine
        $resDestroyMachine = $this->actingAs($this->ownerA)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->delete(route('machines.destroy', $newMachine));
        $resDestroyMachine->assertRedirect(route('labor-machines.index'));
        $this->assertNull(Machine::find($newMachine->id));

        $auditMachineDeleted = AuditLog::where('auditable_type', Machine::class)
            ->where('auditable_id', $newMachine->id)
            ->where('action', 'machines.deleted')
            ->first();
        $this->assertNotNull($auditMachineDeleted, 'AuditLog for machines.deleted was not dispatched.');
    }

    // =========================================================================
    // SECTION 5: IDOR Defense (Cross-Tenant Isolation)
    // =========================================================================

    public function test_idor_cross_tenant_update_and_destroy_are_defensively_rejected(): void
    {
        // Attacker is Owner of Business B, holding labor_machines.manage within Business B.
        // Target records belong to Business A.

        // 1. Attempt to update LaborRate of Business A from Business B session
        $resUpdateLabor = $this->actingAs($this->ownerB)
            ->withSession(['active_business_id' => $this->businessB->id])
            ->put(route('labor-rates.update', $this->laborRateA), [
                'name' => 'Hacked Labor Rate',
                'basis' => 'hourly',
                'rate_amount' => 1000,
                'working_days_per_month' => 20,
                'working_hours_per_day' => 8,
            ]);
        $resUpdateLabor->assertStatus(403);
        $this->laborRateA->refresh();
        $this->assertEquals('Operator CNC Khusus A', $this->laborRateA->name, 'IDOR vulnerability: Business A labor rate was modified by Business B user!');

        // 2. Attempt to destroy LaborRate of Business A from Business B session
        $resDestroyLabor = $this->actingAs($this->ownerB)
            ->withSession(['active_business_id' => $this->businessB->id])
            ->delete(route('labor-rates.destroy', $this->laborRateA));
        $resDestroyLabor->assertStatus(403);
        $this->assertNotNull(LaborRate::find($this->laborRateA->id), 'IDOR vulnerability: Business A labor rate was deleted by Business B user!');

        // 3. Attempt to update Machine of Business A from Business B session
        $resUpdateMachine = $this->actingAs($this->ownerB)
            ->withSession(['active_business_id' => $this->businessB->id])
            ->put(route('machines.update', $this->machineA), [
                'name' => 'Hacked Machine',
                'purchase_price' => 1000,
                'useful_life_hours' => 100,
            ]);
        $resUpdateMachine->assertStatus(403);
        $this->machineA->refresh();
        $this->assertEquals('Mesin Bubut CNC Model A', $this->machineA->name, 'IDOR vulnerability: Business A machine was modified by Business B user!');

        // 4. Attempt to destroy Machine of Business A from Business B session
        $resDestroyMachine = $this->actingAs($this->ownerB)
            ->withSession(['active_business_id' => $this->businessB->id])
            ->delete(route('machines.destroy', $this->machineA));
        $resDestroyMachine->assertStatus(403);
        $this->assertNotNull(Machine::find($this->machineA->id), 'IDOR vulnerability: Business A machine was deleted by Business B user!');
    }

    // =========================================================================
    // SECTION 6: Invoices Clean Routing and Controller-Level RBAC
    // =========================================================================

    public function test_invoices_controller_level_rbac_enforcement(): void
    {
        // 1. User lacking invoices.view (unauthorizedA / warehouse) cannot access invoices.index
        $resIndex = $this->actingAs($this->unauthorizedA)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->get(route('invoices.index'));
        $resIndex->assertStatus(403);

        // 2. User lacking invoices.create cannot access invoices.create
        $resCreate = $this->actingAs($this->unauthorizedA)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->get(route('invoices.create'));
        $resCreate->assertStatus(403);

        // 3. User lacking invoices.export cannot export excel
        $resExport = $this->actingAs($this->unauthorizedA)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->get(route('invoices.export-excel'));
        $resExport->assertStatus(403);

        // 4. Owner has full permissions and can access invoices.index
        $resOwnerIndex = $this->actingAs($this->ownerA)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->get(route('invoices.index'));
        $resOwnerIndex->assertOk();
    }
}
