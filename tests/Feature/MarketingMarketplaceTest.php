<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessLandingPage;
use App\Models\CommerceStoreSetting;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketingMarketplaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_marketplace_and_homepage_excludes_test_stores(): void
    {
        // 1. Create a test store (e.g. meta-reviewer-store)
        $testBusiness = Business::create([
            'name' => 'Meta Reviewer Store',
            'slug' => 'meta-reviewer-store',
            'is_active' => true,
            'is_test' => true,
            'is_public_listed' => false,
        ]);

        $testUnit = Unit::create([
            'business_id' => $testBusiness->id,
            'code' => 'PCS',
            'name' => 'Pieces',
            'category' => Unit::CATEGORY_QUANTITY,
        ]);

        CommerceStoreSetting::create([
            'business_id' => $testBusiness->id,
            'is_storefront_enabled' => true,
            'is_discoverable' => true,
        ]);

        Product::create([
            'business_id' => $testBusiness->id,
            'output_unit_id' => $testUnit->id,
            'name' => 'Kopi Arabika Gayo Single Origin 250g (Tester TikTok)',
            'slug' => 'kopi-arabika-tester-tiktok',
            'selling_price' => 75000,
            'is_active' => true,
            'show_in_website' => true,
        ]);

        // 2. Create a verified real store
        $realBusiness = Business::create([
            'name' => 'Kopi Asli Nusantara',
            'slug' => 'kopi-asli-nusantara',
            'is_active' => true,
            'is_test' => false,
            'is_public_listed' => true,
        ]);

        $realUnit = Unit::create([
            'business_id' => $realBusiness->id,
            'code' => 'PCS',
            'name' => 'Pieces',
            'category' => Unit::CATEGORY_QUANTITY,
        ]);

        CommerceStoreSetting::create([
            'business_id' => $realBusiness->id,
            'is_storefront_enabled' => true,
            'is_discoverable' => true,
        ]);

        Product::create([
            'business_id' => $realBusiness->id,
            'output_unit_id' => $realUnit->id,
            'name' => 'Kopi Robusta Asli 500g',
            'slug' => 'kopi-robusta-asli',
            'selling_price' => 60000,
            'is_active' => true,
            'show_in_website' => true,
        ]);

        // Assert Homepage does not show test product
        $homeResponse = $this->get('/');
        $homeResponse->assertStatus(200);
        $homeResponse->assertDontSee('Tester TikTok');

        // Assert Marketplace listing does not show test product
        $marketResponse = $this->get('/marketplace');
        $marketResponse->assertStatus(200);
        $marketResponse->assertDontSee('Tester TikTok');
        $marketResponse->assertSee('Kopi Robusta Asli 500g');

        // Assert Search does not show test store or product
        $searchResponse = $this->get('/marketplace/cari?q=Tester');
        $searchResponse->assertStatus(200);
        $searchResponse->assertDontSee('meta-reviewer-store');
        $searchResponse->assertDontSee('Tester TikTok');
    }

    public function test_test_store_has_noindex_nofollow_meta_tag(): void
    {
        $testBusiness = Business::create([
            'name' => 'Meta Reviewer Store',
            'slug' => 'meta-reviewer-store',
            'is_active' => true,
            'is_test' => true,
            'is_public_listed' => false,
        ]);

        BusinessLandingPage::create([
            'business_id' => $testBusiness->id,
            'headline' => 'Meta Reviewer Official Storefront',
            'is_published' => true,
        ]);

        CommerceStoreSetting::create([
            'business_id' => $testBusiness->id,
            'is_storefront_enabled' => true,
            'is_discoverable' => true,
        ]);

        // Store is accessible directly
        $response = $this->get('/meta-reviewer-store');
        $response->assertStatus(200);

        // But contains robots noindex, nofollow
        $response->assertSee('<meta name="robots" content="noindex, nofollow">', false);
    }

    public function test_test_store_is_excluded_from_sitemap(): void
    {
        $testBusiness = Business::create([
            'name' => 'Meta Reviewer Store',
            'slug' => 'meta-reviewer-store',
            'is_active' => true,
            'is_test' => true,
            'is_public_listed' => false,
        ]);

        BusinessLandingPage::create([
            'business_id' => $testBusiness->id,
            'headline' => 'Test Store',
            'is_published' => true,
        ]);

        CommerceStoreSetting::create([
            'business_id' => $testBusiness->id,
            'is_storefront_enabled' => true,
            'is_discoverable' => true,
        ]);

        $realBusiness = Business::create([
            'name' => 'Toko Resmi Nyata',
            'slug' => 'toko-resmi-nyata',
            'is_active' => true,
            'is_test' => false,
            'is_public_listed' => true,
        ]);

        BusinessLandingPage::create([
            'business_id' => $realBusiness->id,
            'headline' => 'Toko Resmi Nyata',
            'is_published' => true,
        ]);

        $response = $this->get('/sitemap.xml');
        $response->assertStatus(200);
        $response->assertSee('toko-resmi-nyata');
        $response->assertDontSee('meta-reviewer-store');
    }

    public function test_empty_state_rendered_when_less_than_three_real_products(): void
    {
        // When database has no products or < 3 products, empty state should invite merchants
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Jadilah merchant pertama di kota Anda!');
        $response->assertDontSee('Nasi Bebek Madura');
    }
}
