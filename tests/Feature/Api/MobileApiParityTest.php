<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class MobileApiParityTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Business $business;
    private Unit $unitPcs;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->user = User::create([
            'name' => 'Owner Test',
            'email' => 'owner@cooca.test',
            'phone' => '081234567890',
            'password' => Hash::make('password123'),
        ]);

        $this->business = Business::create([
            'name' => 'Kopi Mantap',
            'slug' => 'kopi-mantap',
            'email' => 'kopi@mantap.test',
            'is_active' => true,
        ]);

        BusinessMembership::create([
            'business_id' => $this->business->id,
            'user_id' => $this->user->id,
            'role' => 'owner',
        ]);

        $this->seed(\Database\Seeders\DefaultUnitSeeder::class);

        $this->unitPcs = Unit::where('code', 'pcs')->firstOrFail();
    }

    public function test_auth_forgot_password_and_account_recovery_endpoints(): void
    {
        // 1. Forgot password
        $resForgot = $this->postJson('/api/v1/auth/forgot-password', [
            'login' => 'owner@cooca.test',
        ]);
        $resForgot->assertStatus(200)
            ->assertJson(['success' => true]);

        // 2. Account recovery submission
        $resRecovery = $this->postJson('/api/v1/account-recovery', [
            'identifier' => 'owner@cooca.test',
            'reason' => 'Lost device authenticator',
            'contact_email' => 'backup@cooca.test',
        ]);
        $resRecovery->assertStatus(201)
            ->assertJson(['success' => true]);

        $ticket = $resRecovery->json('data.ticket_number');
        $this->assertNotEmpty($ticket);

        // 3. Account recovery status check
        $resStatus = $this->getJson("/api/v1/account-recovery/status/{$ticket}");
        $resStatus->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonPath('data.ticket_number', $ticket);
    }

    public function test_authenticated_business_listing_and_switch_aliases(): void
    {
        Sanctum::actingAs($this->user);

        // GET /api/v1/businesses
        $resList = $this->getJson('/api/v1/businesses');
        $resList->assertStatus(200)
            ->assertJson(['success' => true]);

        // POST /api/v1/businesses/switch
        $resSwitch = $this->postJson('/api/v1/businesses/switch', [
            'business_id' => $this->business->id,
        ]);
        $resSwitch->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_complete_profile_onboarding_endpoint(): void
    {
        $newUser = User::create([
            'name' => 'New Merchant',
            'email' => 'new@merchant.test',
            'password' => Hash::make('secret123'),
        ]);

        Sanctum::actingAs($newUser);

        $response = $this->postJson('/api/v1/profile/complete', [
            'name' => 'Updated Merchant Name',
            'phone' => '08987654321',
            'business_name' => 'Kedai Kopi Baru',
            'business_type' => 'fnb',
            'address' => 'Jl. Merdeka No. 12',
            'city' => 'Bandung',
            'province' => 'Jawa Barat',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('users', [
            'id' => $newUser->id,
            'name' => 'Updated Merchant Name',
            'phone' => '08987654321',
        ]);

        $this->assertDatabaseHas('businesses', [
            'name' => 'Kedai Kopi Baru',
        ]);
    }

    public function test_products_crud_with_cost_and_prices(): void
    {
        Sanctum::actingAs($this->user);
        session(['active_business_id' => $this->business->id]);
        $headers = ['X-Business-Id' => $this->business->id];

        $response = $this->postJson('/api/v1/products', [
            'name' => 'Americano Hot',
            'unit' => 'pcs',
            'selling_price' => 25000,
            'base_cost' => 8000,
            'min_stock' => 10,
            'type' => 'goods',
        ], $headers);

        $response->assertStatus(201)
            ->assertJson(['success' => true])
            ->assertJsonPath('data.name', 'Americano Hot')
            ->assertJsonPath('data.selling_price', 25000)
            ->assertJsonPath('data.base_cost', 8000);

        // Check Services alias
        $resServices = $this->getJson('/api/v1/services', $headers);
        $resServices->assertStatus(200);
    }

    public function test_materials_auto_supplier_and_effective_cost(): void
    {
        Sanctum::actingAs($this->user);
        session(['active_business_id' => $this->business->id]);
        $headers = ['X-Business-Id' => $this->business->id];

        $response = $this->postJson('/api/v1/materials', [
            'name' => 'Biji Kopi Arabika Gayo',
            'unit' => 'pcs',
            'purchase_price' => 120000,
            'supplier_name' => 'CV Sumber Kopi Mandiri',
            'yield_percentage' => 95,
            'waste_percentage' => 5,
        ], $headers);

        $response->assertStatus(201)
            ->assertJson(['success' => true])
            ->assertJsonPath('data.name', 'Biji Kopi Arabika Gayo')
            ->assertJsonPath('data.purchase_price', 120000);

        $this->assertDatabaseHas('suppliers', [
            'business_id' => $this->business->id,
            'name' => 'CV Sumber Kopi Mandiri',
        ]);
    }

    public function test_pos_modifier_groups_and_options_crud(): void
    {
        Sanctum::actingAs($this->user);
        session(['active_business_id' => $this->business->id]);
        $headers = ['X-Business-Id' => $this->business->id];

        // 1. Create Modifier Group
        $resGroup = $this->postJson('/api/v1/pos/modifiers/groups', [
            'name' => 'Sugar Level',
            'min_selection' => 1,
            'max_selection' => 1,
            'is_required' => true,
        ], $headers);

        $resGroup->assertStatus(201)
            ->assertJson(['success' => true])
            ->assertJsonPath('data.name', 'Sugar Level');

        $groupId = $resGroup->json('data.id');

        // 2. Create Modifier Option
        $resOption = $this->postJson('/api/v1/pos/modifiers/options', [
            'modifier_group_id' => $groupId,
            'name' => 'Normal Sugar (100%)',
            'price' => 0,
            'is_default' => true,
        ], $headers);

        $resOption->assertStatus(201)
            ->assertJson(['success' => true])
            ->assertJsonPath('data.name', 'Normal Sugar (100%)');

        // 3. List modifiers
        $resList = $this->getJson('/api/v1/pos/modifiers', $headers);
        $resList->assertStatus(200)
            ->assertJson(['success' => true]);
    }
}
