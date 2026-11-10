<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessLandingPage;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductWebsiteLandingPageRequirementTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Business $business;
    private Unit $unit;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->business = Business::create([
            'name' => 'Usaha Test Web ' . uniqid(),
            'slug' => 'usaha-test-web-' . uniqid(),
            'is_active' => true,
        ]);
        $this->business->users()->attach($this->user->id, ['role' => 'owner']);
        $this->user->update(['active_business_id' => $this->business->id]);

        $this->unit = Unit::firstOrCreate(
            ['business_id' => $this->business->id, 'code' => 'pcs'],
            ['name' => 'Pcs', 'category' => 'quantity', 'is_base' => true]
        );
    }

    public function test_new_product_defaults_to_show_in_website_false(): void
    {
        $product = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Kopi Robusta Asli',
            'output_unit_id' => $this->unit->id,
            'selling_price' => 25000,
        ]);

        $this->assertFalse($product->show_in_website);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'show_in_website' => false,
        ]);
    }

    public function test_cannot_create_product_with_show_in_website_true_if_landing_page_not_published(): void
    {
        // Business has no landing page or is_published is false
        $response = $this->actingAs($this->user)
            ->post(route('products.store'), [
                'name' => 'Produk Baru Tanpa Landing Page',
                'output_unit_id' => $this->unit->id,
                'costing_method' => 'simple',
                'selling_price' => 50000,
                'show_in_website' => '1',
            ]);

        $response->assertSessionHasErrors(['show_in_website']);
        $this->assertDatabaseMissing('products', [
            'name' => 'Produk Baru Tanpa Landing Page',
        ]);
    }

    public function test_can_create_product_with_show_in_website_true_if_landing_page_is_published(): void
    {
        BusinessLandingPage::create([
            'business_id' => $this->business->id,
            'is_published' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('products.store'), [
                'name' => 'Produk Baru Dengan Landing Page',
                'output_unit_id' => $this->unit->id,
                'costing_method' => 'simple',
                'selling_price' => 50000,
                'show_in_website' => '1',
            ]);

        $response->assertRedirect(route('products.index'));
        $this->assertDatabaseHas('products', [
            'name' => 'Produk Baru Dengan Landing Page',
            'show_in_website' => true,
        ]);
    }

    public function test_cannot_toggle_show_in_website_to_true_if_landing_page_not_published(): void
    {
        $product = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Produk Toggle Test',
            'output_unit_id' => $this->unit->id,
            'selling_price' => 15000,
            'show_in_website' => false,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/products/{$product->id}/toggle-setting", [
                'field' => 'show_in_website',
                'value' => true,
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'status' => 'error',
            'requires_landing_page' => true,
        ]);

        $product->refresh();
        $this->assertFalse($product->show_in_website);
    }

    public function test_can_toggle_show_in_website_to_true_if_landing_page_is_published(): void
    {
        BusinessLandingPage::create([
            'business_id' => $this->business->id,
            'is_published' => true,
        ]);

        $product = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Produk Toggle Berhasil',
            'output_unit_id' => $this->unit->id,
            'selling_price' => 15000,
            'show_in_website' => false,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/products/{$product->id}/toggle-setting", [
                'field' => 'show_in_website',
                'value' => true,
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'field' => 'show_in_website',
            'value' => true,
        ]);

        $product->refresh();
        $this->assertTrue($product->show_in_website);
    }

    public function test_cannot_bulk_activate_website_channel_if_landing_page_not_published(): void
    {
        $product = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Produk Bulk 1',
            'output_unit_id' => $this->unit->id,
            'show_in_website' => false,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('products.bulk-action'), [
                'ids' => [$product->id],
                'action' => 'show_website',
            ]);

        $response->assertStatus(422);
        $product->refresh();
        $this->assertFalse($product->show_in_website);
    }

    public function test_can_bulk_activate_website_channel_if_landing_page_is_published(): void
    {
        BusinessLandingPage::create([
            'business_id' => $this->business->id,
            'is_published' => true,
        ]);

        $product = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Produk Bulk 2',
            'output_unit_id' => $this->unit->id,
            'show_in_website' => false,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('products.bulk-action'), [
                'ids' => [$product->id],
                'action' => 'show_website',
            ]);

        $response->assertStatus(200);
        $product->refresh();
        $this->assertTrue($product->show_in_website);
    }
}
