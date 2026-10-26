<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Billing\EntitlementService;
use App\Models\AuditLog;
use App\Models\BomHeader;
use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\CostModel;
use App\Models\Location;
use App\Models\Material;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductImage;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class ProductsModuleAuditTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private Business $otherBusiness;
    private User $owner;
    private User $otherOwner;
    private User $staffWithoutPerms;
    private User $staffWithView;
    private User $staffWithManage;
    private Unit $unit;
    private ProductCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RbacSeeder::class);

        $this->owner = User::create([
            'name' => 'Owner Products',
            'email' => 'owner.products@cooca.test',
            'phone' => '6281288881111',
            'password' => 'password',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);

        $this->business = Business::create([
            'name' => 'PT Pangan Nusantara Raya',
            'slug' => 'pt-pangan-nusantara-raya',
            'currency' => 'IDR',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'business_scale' => Business::SCALE_CORPORATE,
            'disabled_modules' => [],
        ]);

        $this->business->users()->attach($this->owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);
        $this->owner->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);

        $entitlement = app(EntitlementService::class);
        $sub = $entitlement->getSubscription($this->business);
        $sub->update([
            'plan_code' => BusinessSubscription::PLAN_PREMIUM_MONTHLY,
            'status' => BusinessSubscription::STATUS_ACTIVE,
        ]);

        // Second business for IDOR verification
        $this->otherOwner = User::create([
            'name' => 'Owner Lain',
            'email' => 'owner.lain@cooca.test',
            'phone' => '6281288882222',
            'password' => 'password',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);

        $this->otherBusiness = Business::create([
            'name' => 'CV Sejahtera Abadi',
            'slug' => 'cv-sejahtera-abadi',
            'currency' => 'IDR',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'business_scale' => Business::SCALE_CORPORATE,
            'disabled_modules' => [],
        ]);

        $this->otherBusiness->users()->attach($this->otherOwner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);
        $this->otherOwner->update(['active_business_id' => $this->otherBusiness->id]);

        // Staff users with different roles
        $this->staffWithoutPerms = User::create([
            'name' => 'Staf Tanpa Izin',
            'email' => 'staf.none@cooca.test',
            'phone' => '6281288883333',
            'password' => 'password',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);
        $this->business->users()->attach($this->staffWithoutPerms->id, [
            'id' => (string) Str::uuid(),
            'role' => 'staff',
        ]);
        $this->staffWithoutPerms->update(['active_business_id' => $this->business->id]);

        $this->staffWithView = User::create([
            'name' => 'Staf View Produk',
            'email' => 'staf.view@cooca.test',
            'phone' => '6281288884444',
            'password' => 'password',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);
        $this->business->users()->attach($this->staffWithView->id, [
            'id' => (string) Str::uuid(),
            'role' => 'staff',
        ]);
        $this->staffWithView->givePermissionTo('products.view');
        $this->staffWithView->givePermissionTo('master_data.product_categories.view');
        $this->staffWithView->update(['active_business_id' => $this->business->id]);

        $this->staffWithManage = User::create([
            'name' => 'Staf Kelola Produk',
            'email' => 'staf.manage@cooca.test',
            'phone' => '6281288885555',
            'password' => 'password',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);
        $this->business->users()->attach($this->staffWithManage->id, [
            'id' => (string) Str::uuid(),
            'role' => 'staff',
        ]);
        $this->staffWithManage->givePermissionTo('products.view');
        $this->staffWithManage->givePermissionTo('products.create');
        $this->staffWithManage->givePermissionTo('products.edit');
        $this->staffWithManage->givePermissionTo('products.delete');
        $this->staffWithManage->givePermissionTo('master_data.product_categories.view');
        $this->staffWithManage->givePermissionTo('master_data.product_categories.manage');
        $this->staffWithManage->update(['active_business_id' => $this->business->id]);

        // Default Unit & Category
        $this->unit = Unit::create([
            'name' => 'Pcs',
            'symbol' => 'pcs',
            'business_id' => null, // Global system unit
        ]);

        $this->category = ProductCategory::create([
            'business_id' => $this->business->id,
            'name' => 'Minuman Rasa',
            'description' => 'Aneka minuman rasa',
        ]);
    }

    public function test_products_index_requires_authentication(): void
    {
        $response = $this->get(route('products.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_products_index_requires_permission(): void
    {
        $response = $this->actingAs($this->staffWithoutPerms)->get(route('products.index'));
        $response->assertStatus(403);
    }

    public function test_user_with_permission_can_view_products_index(): void
    {
        Product::create([
            'business_id' => $this->business->id,
            'name' => 'Kopi Susu Gula Aren',
            'code' => 'KOP-001',
            'output_unit_id' => $this->unit->id,
            'category_id' => $this->category->id,
            'selling_price' => 22000,
            'base_cost' => 11000,
        ]);

        $response = $this->actingAs($this->staffWithView)->get(route('products.index'));
        $response->assertStatus(200);
        $response->assertSee('Kopi Susu Gula Aren');
    }

    public function test_product_store_creates_product_and_audit_log(): void
    {
        $payload = [
            'name' => 'Teh Melati Premium',
            'sku' => 'TEH-MEL-01',
            'category_id' => $this->category->id,
            'output_unit_id' => $this->unit->id,
            'selling_price' => 15000,
            'base_cost' => 6000,
            'min_stock' => 10,
            'costing_method' => 'simple',
            'show_in_pos' => '1',
            'show_in_website' => '1',
        ];

        $response = $this->actingAs($this->staffWithManage)->post(route('products.store'), $payload);
        $response->assertRedirect(route('products.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('products', [
            'business_id' => $this->business->id,
            'name' => 'Teh Melati Premium',
            'code' => 'TEH-MEL-01',
        ]);

        $product = Product::where('code', 'TEH-MEL-01')->firstOrFail();

        // Verify CostModel auto creation
        $this->assertDatabaseHas('cost_models', [
            'business_id' => $this->business->id,
            'product_id' => $product->id,
            'method' => 'simple',
        ]);

        // Verify Audit Log
        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $this->business->id,
            'user_id' => $this->staffWithManage->id,
            'auditable_type' => Product::class,
            'auditable_id' => $product->id,
            'action' => 'product.created',
            'risk_level' => AuditLog::RISK_LOW,
        ]);
    }

    public function test_product_update_price_triggers_medium_risk_audit_log(): void
    {
        $product = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Roti Bakar Cokelat',
            'code' => 'ROT-001',
            'output_unit_id' => $this->unit->id,
            'selling_price' => 20000,
            'base_cost' => 9000,
        ]);

        $updatePayload = [
            'name' => 'Roti Bakar Cokelat Keju',
            'sku' => 'ROT-001',
            'output_unit_id' => $this->unit->id,
            'selling_price' => 25000, // Price increased
            'base_cost' => 12000,    // Cost increased
            'is_active' => '1',
        ];

        $response = $this->actingAs($this->staffWithManage)->put(route('products.update', $product), $updatePayload);
        $response->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $this->business->id,
            'user_id' => $this->staffWithManage->id,
            'auditable_type' => Product::class,
            'auditable_id' => $product->id,
            'action' => 'product.price_updated',
            'risk_level' => AuditLog::RISK_MEDIUM,
        ]);
    }

    public function test_product_update_tenant_isolation_prevents_idor(): void
    {
        // Product belongs to other business
        $otherProduct = Product::create([
            'business_id' => $this->otherBusiness->id,
            'name' => 'Produk Milik Bisnis Lain',
            'code' => 'LAIN-001',
            'output_unit_id' => $this->unit->id,
            'selling_price' => 50000,
        ]);

        $payload = [
            'name' => 'Hacked Name',
            'output_unit_id' => $this->unit->id,
            'selling_price' => 1000,
        ];

        $response = $this->actingAs($this->owner)->put(route('products.update', $otherProduct), $payload);
        $response->assertStatus(403);
    }

    public function test_product_destroy_soft_deletes_and_logs_audit(): void
    {
        $product = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Produk Untuk Dihapus',
            'code' => 'DEL-001',
            'output_unit_id' => $this->unit->id,
            'selling_price' => 30000,
        ]);

        $response = $this->actingAs($this->owner)->delete(route('products.destroy', $product));
        $response->assertRedirect(route('products.index'));

        $this->assertSoftDeleted('products', [
            'id' => $product->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $this->business->id,
            'user_id' => $this->owner->id,
            'auditable_type' => Product::class,
            'auditable_id' => $product->id,
            'action' => 'product.deleted',
            'risk_level' => AuditLog::RISK_MEDIUM,
        ]);
    }

    public function test_product_toggle_setting_quick_switch(): void
    {
        $product = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Produk Toggle',
            'output_unit_id' => $this->unit->id,
            'show_in_website' => true,
        ]);

        $response = $this->actingAs($this->staffWithManage)->postJson(route('products.toggle-setting', $product), [
            'field' => 'show_in_website',
            'value' => false,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'field' => 'show_in_website',
            'value' => false,
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'show_in_website' => false,
        ]);
    }

    public function test_product_branch_pricing_override_and_audit(): void
    {
        $location = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Cabang Mall Senayan',
            'code' => 'LOC-SNY',
            'type' => 'branch',
            'is_active' => true,
        ]);

        $product = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Kopi Latte',
            'output_unit_id' => $this->unit->id,
            'selling_price' => 30000,
            'base_cost' => 15000,
        ]);

        $payload = [
            'prices' => [
                [
                    'location_id' => $location->id,
                    'price' => 35000,
                    'cost_price' => 16000,
                    'is_available' => true,
                ],
            ],
        ];

        $response = $this->actingAs($this->owner)->postJson(route('products.branch_prices.update', $product), $payload);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('branch_product_prices', [
            'business_id' => $this->business->id,
            'product_id' => $product->id,
            'location_id' => $location->id,
            'price' => 35000,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $this->business->id,
            'action' => 'product.branch_prices_updated',
            'risk_level' => AuditLog::RISK_MEDIUM,
        ]);
    }

    public function test_bom_recipe_tenant_isolation_prevents_idor(): void
    {
        $otherProduct = Product::create([
            'business_id' => $this->otherBusiness->id,
            'name' => 'Produk Lain BOM',
            'output_unit_id' => $this->unit->id,
        ]);

        $costModel = CostModel::create([
            'business_id' => $this->otherBusiness->id,
            'product_id' => $otherProduct->id,
            'name' => 'HPP Lain',
            'method' => CostModel::METHOD_RECIPE_BOM,
        ]);

        $bomHeader = BomHeader::create([
            'cost_model_id' => $costModel->id,
            'name' => 'Header Lain',
            'type' => BomHeader::TYPE_RECIPE,
            'level' => 1,
        ]);

        $material = Material::create([
            'business_id' => $this->business->id,
            'name' => 'Gula Pasir',
            'unit_id' => $this->unit->id,
        ]);

        $payload = [
            'material_id' => $material->id,
            'quantity' => 2,
            'unit_id' => $this->unit->id,
        ];

        // Current tenant user attempts to insert item into another tenant's BOM header
        $response = $this->actingAs($this->owner)->post(route('bom.items.store', $bomHeader), $payload);
        $response->assertStatus(403);
    }

    public function test_product_category_controller_has_middleware_and_tenant_isolation(): void
    {
        // 1. Staff without master_data.product_categories.manage cannot store
        $response = $this->actingAs($this->staffWithoutPerms)->post(route('product-categories.store'), [
            'name' => 'Kategori Ilegal',
        ]);
        $response->assertStatus(403);

        // 2. Staff with permission can store
        $response = $this->actingAs($this->staffWithManage)->post(route('product-categories.store'), [
            'name' => 'Kategori Baru Valid',
            'description' => 'Deskripsi valid',
        ]);
        $response->assertStatus(302);
        $this->assertDatabaseHas('product_categories', [
            'business_id' => $this->business->id,
            'name' => 'Kategori Baru Valid',
        ]);

        // 3. IDOR Guard: User cannot update category of another business
        $otherCategory = ProductCategory::create([
            'business_id' => $this->otherBusiness->id,
            'name' => 'Kategori Bisnis Lain',
        ]);

        $response = $this->actingAs($this->owner)->put(route('product-categories.update', $otherCategory), [
            'name' => 'Kategori Dirampas',
        ]);
        $response->assertStatus(403);

        // 4. IDOR Guard: User cannot delete category of another business
        $response = $this->actingAs($this->owner)->delete(route('product-categories.destroy', $otherCategory));
        $response->assertStatus(403);
    }

    public function test_product_category_crud_logs_audit_trail(): void
    {
        $cat = ProductCategory::create([
            'business_id' => $this->business->id,
            'name' => 'Kategori Audit',
        ]);

        $this->actingAs($this->staffWithManage)->put(route('product-categories.update', $cat), [
            'name' => 'Kategori Audit Updated',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $this->business->id,
            'user_id' => $this->staffWithManage->id,
            'auditable_type' => ProductCategory::class,
            'auditable_id' => $cat->id,
            'action' => 'product_category.updated',
        ]);

        $this->actingAs($this->staffWithManage)->delete(route('product-categories.destroy', $cat));

        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $this->business->id,
            'user_id' => $this->staffWithManage->id,
            'auditable_type' => ProductCategory::class,
            'auditable_id' => $cat->id,
            'action' => 'product_category.deleted',
        ]);
    }

    public function test_master_data_product_categories_requires_permission(): void
    {
        $response = $this->actingAs($this->staffWithoutPerms)->get(route('product-categories.index'));
        $response->assertStatus(403);

        $response = $this->actingAs($this->staffWithView)->get(route('product-categories.index'));
        $response->assertStatus(200);
    }

    public function test_product_gallery_image_deletion_scoped_to_tenant(): void
    {
        $product = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Produk Bergaleri',
            'output_unit_id' => $this->unit->id,
        ]);

        $image = ProductImage::create([
            'business_id' => $this->business->id,
            'product_id' => $product->id,
            'image_path' => 'products/gallery/sample.jpg',
            'sort_order' => 1,
        ]);

        // Other user attempts to delete image
        $response = $this->actingAs($this->otherOwner)->delete(route('products.gallery.destroy', [$product, $image]));
        $response->assertStatus(403);

        // Owner can delete
        $response = $this->actingAs($this->owner)->delete(route('products.gallery.destroy', [$product, $image]));
        $response->assertStatus(302);
        $this->assertDatabaseMissing('product_images', ['id' => $image->id]);
    }

    public function test_product_bundle_item_creation_and_sync(): void
    {
        $child1 = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Burger Sapi',
            'output_unit_id' => $this->unit->id,
            'selling_price' => 25000,
        ]);

        $child2 = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Kentang Goreng',
            'output_unit_id' => $this->unit->id,
            'selling_price' => 15000,
        ]);

        $bundlePayload = [
            'name' => 'Paket Hemat Kenyang',
            'sku' => 'PKT-001',
            'output_unit_id' => $this->unit->id,
            'selling_price' => 35000,
            'base_cost' => 20000,
            'costing_method' => 'simple',
            'is_bundle' => '1',
            'bundle_items' => [
                ['child_product_id' => $child1->id, 'quantity' => 1],
                ['child_product_id' => $child2->id, 'quantity' => 1],
            ],
        ];

        $response = $this->actingAs($this->staffWithManage)->post(route('products.store'), $bundlePayload);
        $response->assertRedirect(route('products.index'));

        $bundle = Product::where('code', 'PKT-001')->firstOrFail();
        $this->assertTrue((bool) $bundle->is_bundle);

        $this->assertDatabaseHas('product_bundle_items', [
            'business_id' => $this->business->id,
            'parent_product_id' => $bundle->id,
            'child_product_id' => $child1->id,
            'quantity' => 1,
        ]);
        $this->assertDatabaseHas('product_bundle_items', [
            'business_id' => $this->business->id,
            'parent_product_id' => $bundle->id,
            'child_product_id' => $child2->id,
            'quantity' => 1,
        ]);
    }
}
