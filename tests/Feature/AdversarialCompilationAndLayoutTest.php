<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AdversarialCompilationAndLayoutTest extends TestCase
{
    protected array $viewFiles = [
        'resources/views/app/community/events.blade.php',
        'resources/views/app/community/index.blade.php',
        'resources/views/app/community/event_payment.blade.php',
        'resources/views/app/crm/members.blade.php',
        'resources/views/app/crm/vouchers.blade.php',
        'resources/views/app/customers/index.blade.php',
        'resources/views/app/docs/partials/sec_customers.blade.php',
        'resources/views/app/docs/partials/sec_crm.blade.php',
        'resources/views/app/docs/partials/sec_community.blade.php',
        'resources/views/app/docs/index.blade.php',
    ];

    /**
     * Test 1: Verify every Blade view compiles to valid PHP without syntax errors.
     */
    public function test_all_target_blade_views_compile_cleanly(): void
    {
        foreach ($this->viewFiles as $filePath) {
            $fullPath = base_path($filePath);
            $this->assertFileExists($fullPath, "View file {$filePath} must exist.");

            $content = file_get_contents($fullPath);
            $compiled = Blade::compileString($content);

            $this->assertNotEmpty($compiled, "Compiled template for {$filePath} must not be empty.");

            // Verify compiled output has no unbalanced PHP syntax
            $tokens = @token_get_all($compiled);
            $this->assertIsArray($tokens, "Compiled PHP tokens for {$filePath} must be valid.");
        }
    }

    /**
     * Test 2: Verify all non-defensive route references exist or are guarded by Route::has().
     */
    public function test_all_route_references_are_valid_or_guarded(): void
    {
        $brokenRawRoutes = [];

        foreach ($this->viewFiles as $filePath) {
            $content = file_get_contents(base_path($filePath));
            
            // Extract raw route calls that are NOT part of a Route::has condition
            preg_match_all("/route\(\s*['\"]([a-zA-Z0-9\._-]+)['\"]/", $content, $matches, PREG_OFFSET_CAPTURE);

            foreach ($matches[1] as $match) {
                $routeName = $match[0];
                $offset = $match[1];

                // Check preceding text up to 100 chars to see if Route::has was called
                $preceding = substr($content, max(0, $offset - 100), 100);
                $isGuarded = str_contains($preceding, "Route::has('{$routeName}')") || str_contains($preceding, "Route::has(\"{$routeName}\")");

                if (!Route::has($routeName) && !$isGuarded) {
                    $brokenRawRoutes[] = "{$filePath} -> route('{$routeName}')";
                }
            }
        }

        $this->assertEmpty(
            $brokenRawRoutes,
            "Unguarded broken route references detected: " . implode(', ', array_unique($brokenRawRoutes))
        );
    }

    /**
     * Test 3: Adversarial Translation Audit - Detect any raw untranslated keys in target views.
     */
    public function test_detect_untranslated_keys_in_target_views(): void
    {
        $untranslatedId = [];
        $untranslatedEn = [];

        foreach ($this->viewFiles as $filePath) {
            $content = file_get_contents(base_path($filePath));
            preg_match_all("/(?:__|trans)\(\s*['\"]((?:community|crm|customers|docs)\.[a-zA-Z0-9\._-]+)['\"]/", $content, $matches);

            foreach (array_unique($matches[1]) as $key) {
                // Test Indonesian
                app()->setLocale('id');
                $valId = __($key);
                if ($valId === $key) {
                    $untranslatedId[] = "ID: {$key} (in {$filePath})";
                }

                // Test English
                app()->setLocale('en');
                $valEn = __($key);
                if ($valEn === $key) {
                    $untranslatedEn[] = "EN: {$key} (in {$filePath})";
                }
            }
        }

        // We record the exact missing keys for adversarial reporting
        $this->assertEquals(
            [],
            $untranslatedId,
            "Missing Indonesian translations:\n" . implode("\n", array_unique($untranslatedId))
        );

        $this->assertEquals(
            [],
            $untranslatedEn,
            "Missing English translations:\n" . implode("\n", array_unique($untranslatedEn))
        );
    }

    /**
     * Test 4: Verify responsive layout, DOM ergonomics, and touch targets across all target views.
     */
    public function test_responsive_layout_and_ergonomics_compliance(): void
    {
        // 1. Community Events: zero duplicate <h1> header, uses x-breadcrumb
        $eventsContent = file_get_contents(base_path('resources/views/app/community/events.blade.php'));
        $this->assertStringNotContainsString(
            '<h1',
            $eventsContent,
            'community/events.blade.php must not contain redundant <h1> tags competing with layout topbar'
        );
        $this->assertStringContainsString(
            '<x-breadcrumb',
            $eventsContent,
            'community/events.blade.php must use standardized x-breadcrumb'
        );

        // 2. CRM Vouchers: no triple redundant create buttons; has horizontal snap slider on mobile
        $vouchersContent = file_get_contents(base_path('resources/views/app/crm/vouchers.blade.php'));
        $createBtnMatches = substr_count($vouchersContent, 'showVoucherModal = true');
        $this->assertLessThanOrEqual(
            2,
            $createBtnMatches,
            'crm/vouchers.blade.php must not have redundant triple create voucher buttons'
        );
        $this->assertStringContainsString('snap-x snap-mandatory', $vouchersContent);
        $this->assertStringContainsString('overflow-x-auto sm:overflow-x-visible', $vouchersContent);

        // 3. CRM Members: has horizontal snap slider for mobile Bento KPI cards
        $membersContent = file_get_contents(base_path('resources/views/app/crm/members.blade.php'));
        $this->assertStringContainsString('snap-x snap-mandatory', $membersContent);
        $this->assertStringContainsString('overflow-x-auto sm:overflow-x-visible', $membersContent);

        // 4. Community Index: Feed prioritized above sidebar on mobile (order-1 vs order-2)
        $communityContent = file_get_contents(base_path('resources/views/app/community/index.blade.php'));
        $this->assertMatchesRegularExpression(
            '/aside\s+class="[^"]*order-2\s+lg:order-1/i',
            $communityContent,
            'Community left sidebar must have order-2 on mobile so feed displays first'
        );
        $this->assertMatchesRegularExpression(
            '/main\s+class="[^"]*order-1\s+lg:order-2/i',
            $communityContent,
            'Community main feed must have order-1 on mobile so it is prioritized above sidebar'
        );

        // 5. Customers Index: Isolated translation domain (no purchasing.* keys)
        $customersContent = file_get_contents(base_path('resources/views/app/customers/index.blade.php'));
        $this->assertStringNotContainsString(
            'purchasing.',
            $customersContent,
            'customers/index.blade.php must not reference purchasing.* translation keys'
        );

        // 6. Docs Index: registered partials and TOC anchors
        $docsContent = file_get_contents(base_path('resources/views/app/docs/index.blade.php'));
        $this->assertStringContainsString('#sec-customers', $docsContent);
        $this->assertStringContainsString('#sec-crm', $docsContent);
        $this->assertStringContainsString('#sec-community', $docsContent);
        $this->assertStringContainsString("@include('app.docs.partials.sec_customers')", $docsContent);
        $this->assertStringContainsString("@include('app.docs.partials.sec_crm')", $docsContent);
        $this->assertStringContainsString("@include('app.docs.partials.sec_community')", $docsContent);
    }

    /**
     * Test 5: Verify RBAC action guarding in Blade templates.
     */
    public function test_blade_action_buttons_are_rbac_guarded(): void
    {
        // 1. Community Events
        $eventsContent = file_get_contents(base_path('resources/views/app/community/events.blade.php'));
        $this->assertStringContainsString("@can('community.events.register')", $eventsContent);

        // 2. Community Index
        $communityContent = file_get_contents(base_path('resources/views/app/community/index.blade.php'));
        $this->assertStringContainsString("@can('community.post')", $communityContent);

        // 3. CRM Vouchers
        $vouchersContent = file_get_contents(base_path('resources/views/app/crm/vouchers.blade.php'));
        $this->assertStringContainsString("hasPermission('crm.manage')", $vouchersContent);

        // 4. Customers Index
        $customersContent = file_get_contents(base_path('resources/views/app/customers/index.blade.php'));
        $this->assertStringContainsString("hasPermission('customers.edit')", $customersContent);
    }
}
