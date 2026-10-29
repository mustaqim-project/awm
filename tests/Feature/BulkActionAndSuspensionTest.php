<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Business;
use App\Models\CommerceStoreSetting;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\AdminRbacSeeder;
use Database\Seeders\DefaultCostCategorySeeder;
use Database\Seeders\DefaultUnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BulkActionAndSuspensionTest extends TestCase
{
    use RefreshDatabase;

    private Admin $superAdmin;
    private User $ownerUser;
    private Business $businessA;
    private Business $businessB;
    private Unit $unitPcs;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->seed(DefaultUnitSeeder::class);
        $this->seed(DefaultCostCategorySeeder::class);
        $this->seed(AdminRbacSeeder::class);

        $this->unitPcs = Unit::firstOrCreate(
            ['code' => 'pcs'],
            ['name' => 'Pcs', 'category' => 'quantity', 'is_system' => true]
        );

        $this->superAdmin = Admin::factory()->create([
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        $this->ownerUser = User::create([
            'name' => 'Owner Utama',
            'email' => 'owner@cooca.id',
            'password' => bcrypt('password123'),
        ]);

        $this->businessA = Business::create([
            'name' => 'Toko Mantap Jaya',
            'slug' => 'toko-mantap-jaya',
            'is_active' => true,
            'is_public_listed' => true,
            'is_test' => false,
            'is_demo' => false,
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
        ]);

        CommerceStoreSetting::create([
            'business_id' => $this->businessA->id,
            'is_storefront_enabled' => true,
            'is_discoverable' => true,
        ]);

        $this->businessB = Business::create([
            'name' => 'Toko Sebelah Berkah',
            'slug' => 'toko-sebelah-berkah',
            'is_active' => true,
            'is_public_listed' => true,
            'is_test' => false,
            'is_demo' => false,
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
        ]);

        CommerceStoreSetting::create([
            'business_id' => $this->businessB->id,
            'is_storefront_enabled' => true,
            'is_discoverable' => true,
        ]);

        // Attach ownerUser to businessA as owner
        $this->businessA->users()->attach($this->ownerUser->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);
        $this->ownerUser->update(['active_business_id' => $this->businessA->id]);
    }

    /**
     * Test 1: Suspended business and its products must NOT appear on marketplace.
     */
    public function test_suspended_business_is_excluded_from_marketplace(): void
    {
        $prodA = Product::create([
            'business_id' => $this->businessA->id,
            'type' => Product::TYPE_GOODS,
            'name' => 'Kopi Gayo Istimewa',
            'slug' => 'kopi-gayo-istimewa-' . Str::random(5),
            'output_unit_id' => $this->unitPcs->id,
            'selling_price' => 50000,
            'base_cost' => 25000,
            'is_active' => true,
            'show_in_website' => true,
        ]);

        // Before suspend: businessA and prodA appear in marketplace
        $resBefore = $this->get(route('marketplace.index'));
        $resBefore->assertOk();
        $resBefore->assertSee($this->businessA->name);

        // Suspend businessA
        $this->businessA->update([
            'is_active' => false,
            'suspended_at' => now(),
            'suspended_reason' => 'Audit kepatuhan.',
        ]);

        // After suspend: businessA and prodA must NOT appear in marketplace
        $resAfter = $this->get(route('marketplace.index'));
        $resAfter->assertOk();
        $resAfter->assertDontSee($this->businessA->name);
        $resAfter->assertDontSee($prodA->name);

        // Storefront access directly must return 404
        $resStorefront = $this->get(route('public.business.landing', ['slug' => $this->businessA->slug]));
        $resStorefront->assertNotFound();
    }

    /**
     * Test 2: Admin can perform bulk suspend and bulk activate on businesses.
     */
    public function test_admin_bulk_suspend_and_activate_businesses(): void
    {
        $biz1 = Business::create([
            'name' => 'Bisnis Alpha',
            'slug' => 'bisnis-alpha',
            'is_active' => true,
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
        ]);
        $biz2 = Business::create([
            'name' => 'Bisnis Beta',
            'slug' => 'bisnis-beta',
            'is_active' => true,
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
        ]);

        // 1. Bulk suspend without reason must fail validation
        $failRes = $this->actingAs($this->superAdmin, 'admin')
            ->post(route('admin.businesses.bulk-status'), [
                'action' => 'suspend',
                'ids' => [$biz1->id, $biz2->id],
            ]);
        $failRes->assertSessionHasErrors(['reason']);

        // 2. Bulk suspend with valid reason
        $suspendRes = $this->actingAs($this->superAdmin, 'admin')
            ->post(route('admin.businesses.bulk-status'), [
                'action' => 'suspend',
                'ids' => [$biz1->id, $biz2->id],
                'reason' => 'Audit berkala kepatuhan platform.',
            ]);
        $suspendRes->assertRedirect();
        $suspendRes->assertSessionHas('success');

        $this->assertFalse($biz1->fresh()->is_active);
        $this->assertNotNull($biz1->fresh()->suspended_at);
        $this->assertSame('Audit berkala kepatuhan platform.', $biz1->fresh()->suspended_reason);

        $this->assertFalse($biz2->fresh()->is_active);
        $this->assertNotNull($biz2->fresh()->suspended_at);

        // 3. Bulk activate
        $activateRes = $this->actingAs($this->superAdmin, 'admin')
            ->post(route('admin.businesses.bulk-status'), [
                'action' => 'activate',
                'ids' => [$biz1->id, $biz2->id],
            ]);
        $activateRes->assertRedirect();
        $activateRes->assertSessionHas('success');

        $this->assertTrue($biz1->fresh()->is_active);
        $this->assertNull($biz1->fresh()->suspended_at);
        $this->assertNull($biz1->fresh()->suspended_reason);

        $this->assertTrue($biz2->fresh()->is_active);
        $this->assertNull($biz2->fresh()->suspended_at);
    }

    /**
     * Test 3: Owner can bulk update products and tenant isolation is enforced.
     */
    public function test_owner_bulk_action_products_with_tenant_isolation(): void
    {
        $prod1 = Product::create([
            'business_id' => $this->businessA->id,
            'type' => Product::TYPE_GOODS,
            'name' => 'Produk A1',
            'slug' => 'prod-a1-' . Str::random(5),
            'output_unit_id' => $this->unitPcs->id,
            'selling_price' => 10000,
            'base_cost' => 5000,
            'is_active' => true,
            'show_in_website' => true,
            'show_in_pos' => true,
        ]);

        $prod2 = Product::create([
            'business_id' => $this->businessA->id,
            'type' => Product::TYPE_GOODS,
            'name' => 'Produk A2',
            'slug' => 'prod-a2-' . Str::random(5),
            'output_unit_id' => $this->unitPcs->id,
            'selling_price' => 20000,
            'base_cost' => 10000,
            'is_active' => true,
            'show_in_website' => true,
            'show_in_pos' => true,
        ]);

        // Product belonging to Business B (different tenant)
        $prodOther = Product::create([
            'business_id' => $this->businessB->id,
            'type' => Product::TYPE_GOODS,
            'name' => 'Produk Milik B',
            'slug' => 'prod-b1-' . Str::random(5),
            'output_unit_id' => $this->unitPcs->id,
            'selling_price' => 30000,
            'base_cost' => 15000,
            'is_active' => true,
            'show_in_website' => true,
        ]);

        // Bulk deactivate prod1 and prod2, attempting to include prodOther
        $response = $this->actingAs($this->ownerUser, 'web')
            ->withSession(['active_business_id' => $this->businessA->id])
            ->post(route('products.bulk-action'), [
                'action' => 'deactivate',
                'ids' => [$prod1->id, $prod2->id, $prodOther->id],
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // prod1 and prod2 are deactivated
        $this->assertFalse($prod1->fresh()->is_active);
        $this->assertFalse($prod2->fresh()->is_active);

        // prodOther belonging to Business B MUST NOT be affected!
        $this->assertTrue($prodOther->fresh()->is_active);

        // Bulk hide from website
        $this->actingAs($this->ownerUser, 'web')
            ->withSession(['active_business_id' => $this->businessA->id])
            ->post(route('products.bulk-action'), [
                'action' => 'hide_website',
                'ids' => [$prod1->id, $prod2->id],
            ]);

        $this->assertFalse($prod1->fresh()->show_in_website);
        $this->assertFalse($prod2->fresh()->show_in_website);

        // Bulk show on website
        $this->actingAs($this->ownerUser, 'web')
            ->withSession(['active_business_id' => $this->businessA->id])
            ->post(route('products.bulk-action'), [
                'action' => 'show_website',
                'ids' => [$prod1->id, $prod2->id],
            ]);

        $this->assertTrue($prod1->fresh()->show_in_website);
        $this->assertTrue($prod2->fresh()->show_in_website);
    }

    /**
     * Test 4: Owner can bulk update services and tenant isolation is enforced.
     */
    public function test_owner_bulk_action_services_with_tenant_isolation(): void
    {
        $srv1 = Product::create([
            'business_id' => $this->businessA->id,
            'type' => Product::TYPE_SERVICE,
            'name' => 'Jasa Cuci Sepatu',
            'slug' => 'jasa-cuci-sepatu-' . Str::random(5),
            'output_unit_id' => $this->unitPcs->id,
            'selling_price' => 35000,
            'base_cost' => 10000,
            'is_active' => true,
            'show_in_pos' => true,
            'show_in_website' => true,
        ]);

        $srv2 = Product::create([
            'business_id' => $this->businessA->id,
            'type' => Product::TYPE_SERVICE,
            'name' => 'Jasa Deep Clean',
            'slug' => 'jasa-deep-clean-' . Str::random(5),
            'output_unit_id' => $this->unitPcs->id,
            'selling_price' => 60000,
            'base_cost' => 20000,
            'is_active' => true,
            'show_in_pos' => true,
            'show_in_website' => true,
        ]);

        $srvOther = Product::create([
            'business_id' => $this->businessB->id,
            'type' => Product::TYPE_SERVICE,
            'name' => 'Jasa Bengkel Milik B',
            'slug' => 'jasa-bengkel-b-' . Str::random(5),
            'output_unit_id' => $this->unitPcs->id,
            'selling_price' => 100000,
            'base_cost' => 40000,
            'is_active' => true,
        ]);

        // Bulk deactivate
        $response = $this->actingAs($this->ownerUser, 'web')
            ->withSession(['active_business_id' => $this->businessA->id])
            ->post(route('services.bulk-action'), [
                'action' => 'deactivate',
                'ids' => [$srv1->id, $srv2->id, $srvOther->id],
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertFalse($srv1->fresh()->is_active);
        $this->assertFalse($srv2->fresh()->is_active);
        // srvOther must NOT be changed
        $this->assertTrue($srvOther->fresh()->is_active);

        // Bulk activate
        $this->actingAs($this->ownerUser, 'web')
            ->withSession(['active_business_id' => $this->businessA->id])
            ->post(route('services.bulk-action'), [
                'action' => 'activate',
                'ids' => [$srv1->id, $srv2->id],
            ]);

        $this->assertTrue($srv1->fresh()->is_active);
        $this->assertTrue($srv2->fresh()->is_active);

        // Bulk hide and show POS
        $this->actingAs($this->ownerUser, 'web')
            ->withSession(['active_business_id' => $this->businessA->id])
            ->post(route('services.bulk-action'), [
                'action' => 'hide_pos',
                'ids' => [$srv1->id, $srv2->id],
            ]);

        $this->assertFalse($srv1->fresh()->show_in_pos);
        $this->assertFalse($srv2->fresh()->show_in_pos);

        $this->actingAs($this->ownerUser, 'web')
            ->withSession(['active_business_id' => $this->businessA->id])
            ->post(route('services.bulk-action'), [
                'action' => 'show_pos',
                'ids' => [$srv1->id, $srv2->id],
            ]);

        $this->assertTrue($srv1->fresh()->show_in_pos);
        $this->assertTrue($srv2->fresh()->show_in_pos);
    }
}
