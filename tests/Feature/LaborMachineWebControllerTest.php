<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\LaborRate;
use App\Models\Machine;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class LaborMachineWebControllerTest extends TestCase
{
    use RefreshDatabase;

    private Business $businessA;
    private Business $businessB;
    private User $ownerA;
    private User $ownerB;

    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('id');
        Context::flush();

        $this->seed(RbacSeeder::class);

        $this->businessA = Business::create([
            'name' => 'PT Manufaktur Usaha A',
            'slug' => 'pt-manufaktur-usaha-a',
            'currency' => 'IDR',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'disabled_modules' => [],
        ]);

        $this->businessB = Business::create([
            'name' => 'PT Manufaktur Usaha B',
            'slug' => 'pt-manufaktur-usaha-b',
            'currency' => 'IDR',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'disabled_modules' => [],
        ]);

        BusinessSubscription::create([
            'business_id' => $this->businessA->id,
            'plan_code' => BusinessSubscription::PLAN_PREMIUM_MONTHLY,
            'status' => BusinessSubscription::STATUS_ACTIVE,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        BusinessSubscription::create([
            'business_id' => $this->businessB->id,
            'plan_code' => BusinessSubscription::PLAN_PREMIUM_MONTHLY,
            'status' => BusinessSubscription::STATUS_ACTIVE,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        $this->ownerA = $this->createOwner($this->businessA, 'owner.a@cooca.test');
        $this->ownerB = $this->createOwner($this->businessB, 'owner.b@cooca.test');
    }

    private function createOwner(Business $business, string $email): User
    {
        $user = User::create([
            'name' => 'Owner ' . $business->name,
            'email' => $email,
            'password' => 'password123',
            'active_business_id' => $business->id,
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);

        $business->users()->attach($user->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);

        return $user;
    }

    public function test_guest_cannot_access_labor_machines_index(): void
    {
        $response = $this->get(route('labor-machines.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_owner_can_view_labor_machines_index(): void
    {
        LaborRate::create([
            'business_id' => $this->businessA->id,
            'name' => 'Tukang Jahit Obras',
            'basis' => 'hourly',
            'rate_amount' => 25000,
        ]);

        Machine::create([
            'business_id' => $this->businessA->id,
            'name' => 'Mesin Obras Juki',
            'purchase_price' => 12000000,
            'residual_value' => 2000000,
            'useful_life_hours' => 10000,
            'power_kw' => 0.75,
            'electricity_cost_per_kwh' => 1500,
            'maintenance_cost_per_hour' => 1000,
        ]);

        $response = $this->actingAs($this->ownerA, 'web')
            ->withSession(['active_business_id' => $this->businessA->id])
            ->get(route('labor-machines.index'));

        $response->assertStatus(200);
        $response->assertSee('Tukang Jahit Obras');
        $response->assertSee('Mesin Obras Juki');
    }

    public function test_owner_can_create_update_and_delete_labor_rate(): void
    {
        // 1. Create
        $response = $this->actingAs($this->ownerA, 'web')
            ->withSession(['active_business_id' => $this->businessA->id])
            ->post(route('labor-rates.store'), [
                'name' => 'Operator Sablon Manual',
                'basis' => 'daily',
                'rate_amount' => 150000,
                'working_days_per_month' => 25,
                'working_hours_per_day' => 8,
                'utilization_rate' => 85,
            ]);

        $response->assertRedirect(route('labor-machines.index'));
        $this->assertDatabaseHas('labor_rates', [
            'business_id' => $this->businessA->id,
            'name' => 'Operator Sablon Manual',
            'basis' => 'daily',
        ]);

        $rate = LaborRate::where('business_id', $this->businessA->id)->where('name', 'Operator Sablon Manual')->firstOrFail();

        // 2. Update
        $responseUpdate = $this->actingAs($this->ownerA, 'web')
            ->withSession(['active_business_id' => $this->businessA->id])
            ->put(route('labor-rates.update', $rate->id), [
                'name' => 'Operator Sablon Senior',
                'basis' => 'daily',
                'rate_amount' => 175000,
                'working_days_per_month' => 25,
                'working_hours_per_day' => 8,
                'utilization_rate' => 90,
            ]);

        $responseUpdate->assertRedirect(route('labor-machines.index'));
        $this->assertDatabaseHas('labor_rates', [
            'id' => $rate->id,
            'name' => 'Operator Sablon Senior',
            'rate_amount' => 175000,
        ]);

        // 3. Delete
        $responseDelete = $this->actingAs($this->ownerA, 'web')
            ->withSession(['active_business_id' => $this->businessA->id])
            ->delete(route('labor-rates.destroy', $rate->id));

        $responseDelete->assertRedirect(route('labor-machines.index'));
        $this->assertSoftDeleted('labor_rates', ['id' => $rate->id]);
    }

    public function test_owner_can_create_update_and_delete_machine(): void
    {
        // 1. Create
        $response = $this->actingAs($this->ownerA, 'web')
            ->withSession(['active_business_id' => $this->businessA->id])
            ->post(route('machines.store'), [
                'name' => 'Mesin Press Sablon Hidrolik',
                'purchase_price' => 35000000,
                'residual_value' => 5000000,
                'useful_life_hours' => 15000,
                'power_kw' => 2.2,
                'electricity_cost_per_kwh' => 1600,
                'maintenance_cost_per_hour' => 2500,
            ]);

        $response->assertRedirect(route('labor-machines.index'));
        $this->assertDatabaseHas('machines', [
            'business_id' => $this->businessA->id,
            'name' => 'Mesin Press Sablon Hidrolik',
        ]);

        $machine = Machine::where('business_id', $this->businessA->id)->where('name', 'Mesin Press Sablon Hidrolik')->firstOrFail();

        // 2. Update
        $responseUpdate = $this->actingAs($this->ownerA, 'web')
            ->withSession(['active_business_id' => $this->businessA->id])
            ->put(route('machines.update', $machine->id), [
                'name' => 'Mesin Press Sablon Hidrolik V2',
                'purchase_price' => 38000000,
                'residual_value' => 6000000,
                'useful_life_hours' => 16000,
                'power_kw' => 2.5,
                'electricity_cost_per_kwh' => 1600,
                'maintenance_cost_per_hour' => 3000,
            ]);

        $responseUpdate->assertRedirect(route('labor-machines.index'));
        $this->assertDatabaseHas('machines', [
            'id' => $machine->id,
            'name' => 'Mesin Press Sablon Hidrolik V2',
        ]);

        // 3. Delete
        $responseDelete = $this->actingAs($this->ownerA, 'web')
            ->withSession(['active_business_id' => $this->businessA->id])
            ->delete(route('machines.destroy', $machine->id));

        $responseDelete->assertRedirect(route('labor-machines.index'));
        $this->assertSoftDeleted('machines', ['id' => $machine->id]);
    }

    public function test_cross_tenant_idor_isolation_blocked_with_403(): void
    {
        $rateB = LaborRate::create([
            'business_id' => $this->businessB->id,
            'name' => 'Operator Rahasia Tenant B',
            'basis' => 'hourly',
            'rate_amount' => 50000,
        ]);

        $machineB = Machine::create([
            'business_id' => $this->businessB->id,
            'name' => 'Mesin Rahasia Tenant B',
            'purchase_price' => 50000000,
            'useful_life_hours' => 10000,
        ]);

        // Owner A attempts to update Tenant B's labor rate
        $responseUpdate = $this->actingAs($this->ownerA, 'web')
            ->withSession(['active_business_id' => $this->businessA->id])
            ->put(route('labor-rates.update', $rateB->id), [
                'name' => 'Hacked Name',
                'basis' => 'hourly',
                'rate_amount' => 999999,
            ]);

        $this->assertContains($responseUpdate->status(), [403, 404]);

        // Owner A attempts to delete Tenant B's machine
        $responseDelete = $this->actingAs($this->ownerA, 'web')
            ->withSession(['active_business_id' => $this->businessA->id])
            ->delete(route('machines.destroy', $machineB->id));

        $this->assertContains($responseDelete->status(), [403, 404]);

        // Verify data intact
        $this->assertDatabaseHas('labor_rates', [
            'id' => $rateB->id,
            'name' => 'Operator Rahasia Tenant B',
        ]);

        $this->assertDatabaseHas('machines', [
            'id' => $machineB->id,
            'name' => 'Mesin Rahasia Tenant B',
        ]);
    }
}

