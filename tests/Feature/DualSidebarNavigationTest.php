<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Template\ModuleRegistry;
use App\Models\Business;
use App\Models\CashAccount;
use App\Models\Location;
use App\Models\User;
use App\Support\Context;
use App\Support\Navigation\NavigationRegistry;
use Database\Seeders\DefaultCostCategorySeeder;
use Database\Seeders\DefaultUnitSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Comprehensive Automated Verification for Dual Sidebar Layout & Sub-Navigation Migration.
 *
 * Verifies:
 * - All 15 modules compile and render routes properly without exception.
 * - Dual sidebar structure (primary sidebar on far left, secondary sidebar adjacent to it) on desktop.
 * - Distinct active state styling (active pill, number badge, chevron right) matching reference design.
 * - Absence of visible legacy horizontal top tabs across module views.
 * - Mobile and tablet drawer integration of sub-navigation without horizontal overflow.
 * - Strict multi-industry gating (Zero Feature Leakage) and RBAC permission enforcement.
 */
final class DualSidebarNavigationTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Business $business;
    private Location $location;

    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('id');
        Context::flush();

        $this->seed(DefaultUnitSeeder::class);
        $this->seed(DefaultCostCategorySeeder::class);
        $this->seed(RbacSeeder::class);

        $this->owner = User::create([
            'name'              => 'Owner Dual Sidebar Test',
            'email'             => 'owner_dual_sidebar_' . Str::random(6) . '@test.local',
            'phone'             => '62812' . rand(10000000, 99999999),
            'password'          => 'password',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);

        $this->business = Business::create([
            'name'             => 'Bisnis Dual Sidebar Testing',
            'currency'         => 'IDR',
            'currency_code'    => 'IDR',
            'currency_symbol'  => 'Rp',
            'business_scale'   => Business::SCALE_CORPORATE,
            'disabled_modules' => [],
        ]);

        $this->business->users()->attach($this->owner->id, [
            'id'   => (string) Str::uuid(),
            'role' => 'owner',
        ]);

        $this->owner->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);

        $this->location = Location::create([
            'business_id' => $this->business->id,
            'name'        => 'Gudang Pusat Dual Sidebar',
            'type'        => 'warehouse',
            'is_active'   => true,
        ]);

        (new \App\Domain\Accounting\AutoJournalService())->ensureStandardAccounts($this->business);

        CashAccount::create([
            'business_id'     => $this->business->id,
            'name'            => 'Kas Utama Toko',
            'account_number'  => 'CASH-001',
            'bank_name'       => 'Tunai',
            'current_balance' => 1000000,
            'is_active'       => true,
        ]);
    }

    /**
     * Requirement R1/R4: All 15 registered modules have valid definitions and their sub-navigation routes resolve without errors.
     */
    public function test_all_15_modules_compile_and_resolve_valid_routes(): void
    {
        $this->actingAs($this->owner, 'web');

        $allModules = NavigationRegistry::all();
        $this->assertCount(15, $allModules, 'NavigationRegistry must define exactly 15 modules.');

        $expectedModules = [
            'finance',
            'accounting',
            'purchasing',
            'products',
            'materials',
            'inventory',
            'sales',
            'crm',
            'communication',
            'storefront',
            'approvals',
            'marketplace',
            'pos',
            'billing',
            'feedback',
        ];

        foreach ($expectedModules as $moduleKey) {
            $this->assertArrayHasKey($moduleKey, $allModules, "Module {$moduleKey} must exist in NavigationRegistry.");
            $moduleDef = $allModules[$moduleKey];

            $this->assertNotEmpty($moduleDef['label'], "Module {$moduleKey} must have a label.");
            $this->assertNotEmpty($moduleDef['tabs'], "Module {$moduleKey} must define tabs.");

            $tabs = NavigationRegistry::getTabsForModule($moduleKey);
            $this->assertNotEmpty($tabs, "Module {$moduleKey} must return accessible tabs for owner.");

            foreach ($tabs as $tab) {
                $this->assertNotEmpty($tab['key'], "Tab in module {$moduleKey} must have a key.");
                $this->assertNotEmpty($tab['label'], "Tab in module {$moduleKey} must have a label.");
                $this->assertNotEmpty($tab['url'], "Tab in module {$moduleKey} must resolve to a valid URL.");
                $this->assertStringStartsWith('http', $tab['url'], "URL for tab {$tab['key']} must be an absolute URL.");
            }
        }
    }

    /**
     * Requirement R1: On desktop screens, layout displays a dual sidebar (primary on far left, secondary directly adjacent).
     */
    public function test_desktop_renders_dual_sidebar_layout_with_primary_and_secondary_sidebars(): void
    {
        $response = $this->actingAs($this->owner, 'web')->get(route('materials.index'));

        $response->assertOk();
        $content = $response->getContent();

        // 1. Primary sidebar exists on far left
        $this->assertStringContainsString('class="app-sidebar', $content);
        $this->assertStringContainsString('id="tour-nav-dashboard"', $content);

        // 2. Secondary sidebar exists directly adjacent to primary sidebar
        $this->assertStringContainsString('id="secondary-sidebar"', $content);
        $this->assertStringContainsString('data-secondary-sidebar="materials"', $content);
        $this->assertStringContainsString('hidden lg:flex fixed inset-y-0 z-30 w-[260px]', $content);

        // 3. Secondary sidebar header matches module
        $this->assertStringContainsString('BAGIAN BAHAN BAKU &amp; RESEP', $content);
        $this->assertStringContainsString('3 Bagian', $content);

        // 4. Main content has dual sidebar padding to prevent overlapping
        $this->assertStringContainsString('lg:pl-[532px]', $content);
    }

    /**
     * Requirement R1: Active sub-page has distinct active state styling (active pill, number badge, chevron).
     */
    public function test_active_sub_page_has_distinct_active_pill_styling(): void
    {
        $response = $this->actingAs($this->owner, 'web')->get(route('materials.index'));

        $response->assertOk();
        $content = $response->getContent();

        // Active item in secondary sidebar: 'Katalog Bahan Baku'
        $this->assertStringContainsString('data-subnav-key="catalog"', $content);
        $this->assertStringContainsString('data-active-secondary-nav="true"', $content);
        $this->assertStringContainsString('bg-[#007AFF] text-white font-semibold', $content);

        // Inactive items have data-active-secondary-nav="false"
        $this->assertStringContainsString('data-subnav-key="categories"', $content);
        $this->assertStringContainsString('data-active-secondary-nav="false"', $content);
    }

    /**
     * Requirement R1: Active state moves dynamically when navigating between sub-pages within the same module.
     */
    public function test_secondary_sidebar_updates_active_state_on_sub_page_navigation(): void
    {
        $response = $this->actingAs($this->owner, 'web')->get(route('material-categories.index'));

        $response->assertOk();
        $content = $response->getContent();

        // Secondary sidebar is still materials
        $this->assertStringContainsString('data-secondary-sidebar="materials"', $content);

        // Now categories is active
        preg_match('/<a[^>]*data-subnav-key="categories"[^>]*data-active-secondary-nav="([^"]+)"/s', $content, $matchCategory);
        $this->assertSame('true', $matchCategory[1] ?? null, 'Categories tab must be marked active on categories page.');

        // Catalog is now inactive
        preg_match('/<a[^>]*data-subnav-key="catalog"[^>]*data-active-secondary-nav="([^"]+)"/s', $content, $matchCatalog);
        $this->assertSame('false', $matchCatalog[1] ?? null, 'Catalog tab must be marked inactive on categories page.');
    }

    /**
     * Acceptance Criteria: No module view displays the legacy horizontal top tabs (<x-module-tabs />).
     */
    public function test_legacy_horizontal_top_tabs_are_not_displayed(): void
    {
        $response = $this->actingAs($this->owner, 'web')->get(route('materials.index'));

        $response->assertOk();
        $content = $response->getContent();

        // The legacy visible horizontal scroll container is NOT displayed
        $this->assertStringNotContainsString('overflow-x-auto no-scrollbar scrollbar-none pb-1 flex-nowrap overscroll-x-contain', $content);

        // The module-tabs container is hidden and suppressed from visual rendering
        $this->assertStringContainsString('data-module-tabs="materials"', $content);
        $this->assertStringContainsString('class="hidden" style="display: none !important;"', $content);
    }

    /**
     * Requirement R2: Mobile/Tablet drawer integrates secondary sub-navigation without horizontal overflow.
     */
    public function test_mobile_drawer_integrates_secondary_sub_navigation_without_breaking_layout(): void
    {
        $response = $this->actingAs($this->owner, 'web')->get(route('materials.index'));

        $response->assertOk();
        $content = $response->getContent();

        // Mobile drawer sub-navigation section exists and is hidden on desktop (lg:hidden)
        $this->assertStringContainsString('sidebar-subnav-mobile lg:hidden', $content);
        $this->assertStringContainsString('Sub-Menu Modul', $content);
        $this->assertStringContainsString('Bahan Baku &amp; Resep', $content);

        // Mobile links exist with active state
        $this->assertStringContainsString('data-active-mobile-subnav="true"', $content);
        $this->assertStringContainsString('data-active-mobile-subnav="false"', $content);
    }

    /**
     * Requirement R3: Multi-industry gating (Zero Feature Leakage) & RBAC rules preserved on sub-navigation.
     */
    public function test_multi_industry_and_rbac_gating_preserves_zero_feature_leakage(): void
    {
        // 1. Industry gating test: Disable CRM loyalty module for tenant
        $this->business->update([
            'disabled_modules' => [ModuleRegistry::MODULE_CRM_LOYALTY],
        ]);
        $this->business->refresh();
        Context::setBusiness($this->business);

        $resCrm = $this->actingAs($this->owner, 'web')->get(route('customers.index'));
        $resCrm->assertOk();
        $crmContent = $resCrm->getContent();

        // Secondary sidebar exists for CRM
        $this->assertStringContainsString('data-secondary-sidebar="crm"', $crmContent);
        // Base customers tab is present
        $this->assertStringContainsString('data-subnav-key="customers"', $crmContent);
        // Gated loyalty tabs (members, vouchers) are strictly hidden from DOM
        $this->assertStringNotContainsString('data-subnav-key="members"', $crmContent);
        $this->assertStringNotContainsString('data-subnav-key="vouchers"', $crmContent);

        // 2. RBAC test: Cashier user without 'sales.returns' permission
        $cashier = User::create([
            'name'              => 'Cashier Limited Dual Nav',
            'email'             => 'cashier_dual_' . Str::random(6) . '@test.local',
            'phone'             => '62812' . rand(10000000, 99999999),
            'password'          => 'password',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);

        $this->business->users()->attach($cashier->id, [
            'id'   => (string) Str::uuid(),
            'role' => 'cashier',
        ]);
        $cashier->update(['active_business_id' => $this->business->id]);

        $resSales = $this->actingAs($cashier, 'web')->get(route('sales.orders.index'));
        if ($resSales->status() === 200) {
            $salesContent = $resSales->getContent();
            // Authorized tab is present
            $this->assertStringContainsString('data-subnav-key="orders"', $salesContent);
            // Unauthorized tab 'returns' is hidden from secondary sidebar
            $this->assertStringNotContainsString('data-subnav-key="returns"', $salesContent);
        }
    }

    /**
     * Requirement: Pages without sub-navigation (e.g. Dashboard) do not render secondary sidebar and use standard layout.
     */
    public function test_pages_without_sub_navigation_do_not_render_secondary_sidebar(): void
    {
        $response = $this->actingAs($this->owner, 'web')->get(route('dashboard'));

        $response->assertOk();
        $content = $response->getContent();

        // Secondary sidebar is NOT rendered
        $this->assertStringNotContainsString('id="secondary-sidebar"', $content);
        $this->assertStringNotContainsString('data-secondary-sidebar=', $content);

        // Main content uses single-sidebar padding (76px collapsed, 272px expanded)
        $this->assertStringContainsString('lg:pl-[272px]', $content);
        $this->assertStringNotContainsString('lg:pl-[532px]', $content);
    }

    /**
     * Requirement R1/R4: NavigationRegistry defines exactly 15 unique module keys with zero duplicate definitions.
     */
    public function test_navigation_registry_contains_exactly_15_unique_modules_without_duplicate_keys(): void
    {
        $all = NavigationRegistry::all();
        $this->assertCount(15, $all, 'NavigationRegistry must have exactly 15 modules.');

        $fileContent = file_get_contents(app_path('Support/Navigation/NavigationRegistry.php'));
        preg_match_all("/'communication'\s*=>\s*\[/m", $fileContent, $matches);
        $this->assertCount(1, $matches[0], 'The communication module must only be defined once in NavigationRegistry.');
    }

    /**
     * Requirement R1/R2: Layout initializes responsive sidebar collapse logic for small desktop viewports (1024-1279px).
     */
    public function test_layout_initializes_responsive_sidebar_collapse_logic_for_small_desktop_viewports(): void
    {
        $response = $this->actingAs($this->owner, 'web')->get(route('materials.index'));
        $response->assertOk();
        $content = $response->getContent();

        $this->assertStringContainsString('window.innerWidth >= 1024 && window.innerWidth < 1280', $content);
    }

    /**
     * Requirement R1/R4: Every one of the 15 modules renders the secondary sidebar on its primary route.
     */
    public function test_all_15_modules_render_secondary_sidebar_on_http_request(): void
    {
        $primaryRoutes = [
            'finance'       => 'finance.cash-bank.index',
            'accounting'    => 'finance.coa.index',
            'purchasing'    => 'purchase-orders.index',
            'products'      => 'products.index',
            'materials'     => 'materials.index',
            'inventory'     => 'warehouse.index',
            'sales'         => 'sales.orders.index',
            'crm'           => 'customers.index',
            'communication' => 'whatsapp.index',
            'storefront'    => 'storefront.orders.index',
            'approvals'     => 'approvals.inbox',
            'marketplace'   => 'marketplace-hub.index',
            'pos'           => 'pos.orders.index',
            'billing'       => 'billing.limits',
            'feedback'      => 'feedback.bugs.index',
        ];

        foreach ($primaryRoutes as $moduleKey => $routeName) {
            $response = $this->actingAs($this->owner, 'web')->get(route($routeName));
            $this->assertSame(200, $response->status(), "Module {$moduleKey} primary route {$routeName} must return 200 OK.");

            $content = $response->getContent();
            $this->assertStringContainsString('id="secondary-sidebar"', $content, "Module {$moduleKey} must render secondary sidebar.");
            $this->assertStringContainsString("data-secondary-sidebar=\"{$moduleKey}\"", $content, "Secondary sidebar must be tagged with data-secondary-sidebar=\"{$moduleKey}\".");
            $this->assertStringContainsString('lg:pl-[532px]', $content, "Module {$moduleKey} page must have dual sidebar desktop padding lg:pl-[532px].");
        }
    }

    /**
     * Requirement: Secondary sidebar can be collapsed (hidden) and expanded (shown) like primary sidebar.
     */
    public function test_secondary_sidebar_can_be_collapsed_and_expanded(): void
    {
        $response = $this->actingAs($this->owner, 'web')->get(route('materials.index'));
        $response->assertOk();
        $content = $response->getContent();

        // 1. Alpine state management exists for secondary sidebar collapse
        $this->assertStringContainsString('secondarySidebarCollapsed:', $content);
        $this->assertStringContainsString('toggleSecondarySidebarCollapse()', $content);
        $this->assertStringContainsString('cooca-secondary-sidebar-collapsed', $content);

        // 2. Collapse button inside secondary sidebar header
        $this->assertStringContainsString('@click="toggleSecondarySidebarCollapse()"', $content);
        $this->assertStringContainsString('title="Sembunyikan Menu Bagian"', $content);

        // 3. Topbar has toggle button for secondary sidebar
        $this->assertStringContainsString('id="tour-toggle-secondary-sidebar-btn"', $content);

        // 4. Floating edge un-collapse pill exists when collapsed
        $this->assertStringContainsString('x-show="secondarySidebarCollapsed"', $content);

        // 5. Dynamic canvas padding covers all 4 collapse combinations
        $this->assertStringContainsString('lg:pl-[532px]', $content);
        $this->assertStringContainsString('lg:pl-[336px]', $content);
        $this->assertStringContainsString('lg:pl-[272px]', $content);
        $this->assertStringContainsString('lg:pl-[76px]', $content);
    }

    /**
     * Requirement: Mobile layout prevents visual overlap between bottom app bar, drawers, and banners.
     */
    public function test_mobile_layout_prevents_visual_overlap(): void
    {
        $response = $this->actingAs($this->owner, 'web')->get(route('materials.index'));
        $response->assertOk();
        $content = $response->getContent();

        // 1. Mobile bottom bar hides when sidebar drawer opens to prevent overlap
        $this->assertStringContainsString('x-show="!sidebarOpen"', $content);

        // 2. Mobile bottom bar safe area padding
        $this->assertStringContainsString('safe-area-inset-bottom', $content);

        // 3. Mobile topbar shows active sub-navigation pill
        $this->assertStringContainsString('data-mobile-subnav-trigger="true"', $content);

        // 4. Content has ample bottom padding on mobile
        $this->assertStringContainsString('pb-36 lg:pb-10', $content);
    }
}
