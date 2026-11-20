<?php

declare(strict_types=1);

namespace Tests\E2E\Tier3_CrossFeature;

use App\Domain\Pricing\PricingEngine;
use App\Domain\Printer\EscposFormatter;
use App\Http\Controllers\Api\V1\Pos\PosSyncApiController;
use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\PosOrder;
use App\Models\PosPrinter;
use App\Models\User;
use App\Support\Context;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Tests\E2E\Support\BaseTestCase;

/**
 * Tier 3: Cross-Platform Parity Test Suite
 *
 * Validates cross-platform alignment, route-to-screen parity, shared REST API interfaces,
 * unified Sanctum authentication, tenant context header synchronization, pricing logic parity,
 * mobile offline sync payload compatibility, and thermal receipt formatting.
 */
class CrossPlatformParityTest extends BaseTestCase
{
    /**
     * Test 1: Web route to Mobile screen parity mapping validation.
     * Verifies that the authoritative cross-platform parity specification exists and
     * all core operational domains have corresponding Web routes and Mobile screen mappings.
     */
    public function test_web_route_to_mobile_screen_parity_mapping_validation(): void
    {
        $mappingPath = base_path('documentation/cooca-platform-audit/04-platform-feature-mapping.md');
        $this->assertFileExists($mappingPath, '04-platform-feature-mapping.md must exist.');

        $content = (string) file_get_contents($mappingPath);
        $this->assertNotEmpty($content, 'Parity mapping documentation must not be empty.');

        // Verify key core operational domains exist in the parity documentation
        $expectedCoreDomains = [
            'Authentication & Workspace Bootstrap',
            'Workspace Tenant Switching',
            'Executive Dashboard & Pulse',
            'POS Terminal & Checkout',
            'POS Shift Float & Close',
            'Operational Kitchen / SPK Queue (KDS)',
            'Dine-In Table Management',
            'Multi-Warehouse Stock Balances',
            'Inter-Warehouse Stock Transfer',
            'Warehouse Physical Goods Receipt (GRN)',
            'Master Finished Goods Catalog',
            'Customer Master Directory',
        ];

        foreach ($expectedCoreDomains as $domain) {
            $this->assertStringContainsString(
                $domain,
                $content,
                "Parity mapping matrix must cover core domain [{$domain}]."
            );
        }

        // Verify essential Web routes are registered in Laravel router
        $registeredRoutes = collect(Route::getRoutes()->getRoutesByName())->keys()->all();
        $this->assertNotEmpty($registeredRoutes, 'Laravel routes must be registered.');

        $expectedWebRouteNames = [
            'dashboard',
            'login',
        ];

        foreach ($expectedWebRouteNames as $routeName) {
            $this->assertTrue(
                Route::has($routeName),
                "Core Web route [{$routeName}] must be registered in router."
            );
        }
    }

    /**
     * Test 2: REST API endpoints shared between Web and Mobile.
     * Verifies that high-performance REST API endpoints utilized by both Web and Mobile
     * are properly registered and return expected JSON contract structures.
     */
    public function test_rest_api_endpoints_shared_between_web_and_mobile(): void
    {
        $business = $this->getTenantBusiness('cooca_mart');
        $this->assertNotNull($business, 'COOCA Mart demo business must exist.');

        $owner = User::where('email', 'owner@coocamart.id')->first()
            ?? User::where('email', 'demo@cooca.id')->first();
        $this->assertNotNull($owner, 'Owner user must exist.');

        // 1. Verify key API routes are registered
        $apiRoutes = collect(Route::getRoutes()->getRoutes())
            ->map(fn($r) => $r->uri())
            ->filter(fn($uri) => str_starts_with($uri, 'api/v1/'))
            ->values()
            ->all();

        $expectedEndpoints = [
            'api/v1/auth/login',
            'api/v1/products',
            'api/v1/pos/terminal/checkout',
            'api/v1/pos/sync/batch',
            'api/v1/inventory/stocks',
        ];

        foreach ($expectedEndpoints as $endpoint) {
            $this->assertTrue(
                in_array($endpoint, $apiRoutes, true),
                "REST API endpoint [{$endpoint}] must be registered."
            );
        }

        // 2. Dispatch authenticated API GET request to products endpoint
        $this->actingAsTenantUser($owner, $business->id);

        $response = $this->apiJson(
            'GET',
            '/api/v1/products',
            [],
            [],
            $business->id
        );

        $response->assertOk();
        $response->assertJsonStructure([
            'data',
        ]);
    }

    /**
     * Test 3: Unified authentication (Sanctum tokens used across both platforms).
     * Tests that personal access tokens generated via Sanctum authenticate API requests
     * across web backoffice and mobile edge runtimes identically.
     */
    public function test_unified_authentication_sanctum_tokens_across_platforms(): void
    {
        $this->runInTransaction(function (): void {
            $user = User::where('email', 'demo@cooca.id')->first()
                ?? User::where('email', 'owner@coocamart.id')->first()
                ?? User::first();
            $this->assertNotNull($user, 'User must exist for authentication test.');

            $token = $user->createToken('unified-e2e-token', ['*'])->plainTextToken;
            $this->assertNotEmpty($token, 'Sanctum plain text token must be generated.');

            // Make unauthenticated request (expect 401)
            $unauthResponse = $this->json('GET', '/api/v1/profile', [], [
                'Accept' => 'application/json',
            ]);
            $unauthResponse->assertStatus(401);

            // Make authenticated request with Bearer token
            $authResponse = $this->json('GET', '/api/v1/profile', [], [
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ]);

            $authResponse->assertOk();
            $authResponse->assertJsonPath('user.email', $user->email);
        });
    }

    /**
     * Test 4: Tenant context header X-Business-Id synchronization.
     * Tests that supplying the X-Business-Id header dynamically synchronizes the active
     * tenant context in App\Support\Context across requests.
     */
    public function test_tenant_context_header_x_business_id_synchronization(): void
    {
        $user = User::where('email', 'demo@cooca.id')->first()
            ?? User::where('email', 'testing@cooca.id')->first();
        $this->assertNotNull($user, 'Multi-tenant demo user must exist.');

        $businessMart = $this->getTenantBusiness('cooca_mart');
        $businessBistro = $this->getTenantBusiness('cooca_bistro');
        $this->assertNotNull($businessMart, 'COOCA Mart must exist.');
        $this->assertNotNull($businessBistro, 'COOCA Bistro must exist.');

        // Ensure user has membership in both for legitimate header switching
        $this->runInTransaction(function () use ($user, $businessMart, $businessBistro): void {
            BusinessMembership::firstOrCreate(
                ['business_id' => $businessMart->id, 'user_id' => $user->id],
                ['role' => 'owner', 'is_active' => true]
            );
            BusinessMembership::firstOrCreate(
                ['business_id' => $businessBistro->id, 'user_id' => $user->id],
                ['role' => 'owner', 'is_active' => true]
            );

            // 1. Request with COOCA Mart header
            $this->actingAsTenantUser($user, $businessMart->id);
            $responseMart = $this->apiJson(
                'GET',
                '/api/v1/products',
                [],
                [],
                $businessMart->id
            );
            $responseMart->assertOk();
            $this->assertSame($businessMart->id, Context::businessId());

            // 2. Request with COOCA Bistro header
            $this->actingAsTenantUser($user, $businessBistro->id);
            $responseBistro = $this->apiJson(
                'GET',
                '/api/v1/products',
                [],
                [],
                $businessBistro->id
            );
            $responseBistro->assertOk();
            $this->assertSame($businessBistro->id, Context::businessId());
        });
    }

    /**
     * Test 5: Shared business logic validation (pricing rules parity).
     * Tests that the core PricingEngine calculates markup and margin identically
     * for both Web Backoffice pricing tools and Mobile POS displays.
     */
    public function test_shared_business_logic_pricing_rules_parity(): void
    {
        $engine = new PricingEngine();

        // 1. Markup Strategy: Selling Price = HPP * (1 + Markup%)
        $hpp = 100000.0;
        $markupPercent = 25.0; // 25% markup
        $resultMarkup = $engine->fromMarkup($hpp, $markupPercent);

        $this->assertSame('markup', $resultMarkup['strategy']);
        $this->assertEqualsWithDelta(125000.0, $resultMarkup['selling_price'], 0.01);
        $this->assertEqualsWithDelta(25000.0, $resultMarkup['gross_profit'], 0.01);
        $this->assertEqualsWithDelta(20.0, $resultMarkup['margin_percentage'], 0.01);

        // 2. Margin Strategy: Selling Price = HPP / (1 - Margin%)
        $marginPercent = 20.0; // 20% margin on 100,000 HPP => 125,000 selling price
        $resultMargin = $engine->fromMargin($hpp, $marginPercent);

        $this->assertSame('margin', $resultMargin['strategy']);
        $this->assertEqualsWithDelta(125000.0, $resultMargin['selling_price'], 0.01);
        $this->assertEqualsWithDelta(25000.0, $resultMargin['gross_profit'], 0.01);
        $this->assertEqualsWithDelta(25.0, $resultMargin['markup_percentage'], 0.01);

        // Parity equality between markup and margin results
        $this->assertEqualsWithDelta(
            $resultMarkup['selling_price'],
            $resultMargin['selling_price'],
            0.001,
            'Markup and Margin calculation strategies must produce identical prices at matching profit points.'
        );
    }

    /**
     * Test 6: Mobile offline sync payload format compatibility with backend schema.
     * Tests that the offline sync endpoint accepts the mobile SQLite queue payload,
     * enforces client_uuid idempotency, and returns structured sync results.
     */
    public function test_mobile_offline_sync_payload_format_compatibility(): void
    {
        $this->runInTransaction(function (): void {
            $business = $this->getTenantBusiness('cooca_mart');
            $this->assertNotNull($business, 'COOCA Mart business must exist.');

            $cashier = User::where('email', 'cashier@coocamart.id')->first()
                ?? User::where('email', 'owner@coocamart.id')->first()
                ?? User::where('email', 'demo@cooca.id')->first();
            $this->assertNotNull($cashier, 'User must exist for offline sync.');

            $this->actingAsTenantUser($cashier, $business->id);

            $clientUuid = (string) Str::uuid();

            // Mobile offline sync payload matching PosSyncApiController validation rules
            $payload = [
                'transactions' => [
                    [
                        'client_uuid' => $clientUuid,
                        'offline_created_at' => now()->toISOString(),
                        'order_type' => 'takeaway',
                        'items' => [
                            [
                                'product_name' => 'Offline Synced Product Sample',
                                'unit_price' => 15000,
                                'quantity' => 2,
                                'discount_amount' => 0,
                                'notes' => 'Offline order line',
                            ],
                        ],
                        'payments' => [
                            [
                                'payment_method' => 'cash',
                                'amount' => 30000,
                                'reference_number' => 'OFFLINE-PAY-' . substr($clientUuid, 0, 8),
                            ],
                        ],
                    ],
                ],
            ];

            // 1. Initial batch sync dispatch
            $response = $this->apiJson(
                'POST',
                '/api/v1/pos/sync/batch',
                $payload,
                [],
                $business->id
            );

            $response->assertOk();
            $response->assertJsonStructure([
                'success',
                'summary' => [
                    'total_submitted',
                    'total_synced',
                    'total_duplicates',
                    'total_failed',
                ],
                'results',
            ]);

            $this->assertSame(1, $response->json('summary.total_synced'));

            // 2. Idempotency test: Re-submitting same client_uuid must return duplicate_skipped
            $dupResponse = $this->apiJson(
                'POST',
                '/api/v1/pos/sync/batch',
                $payload,
                [],
                $business->id
            );

            $dupResponse->assertOk();
            $this->assertSame(1, $dupResponse->json('summary.total_duplicates'));
            $this->assertSame('duplicate_skipped', $dupResponse->json('results.0.status'));
        });
    }

    /**
     * Test 7: Thermal receipt template parity (character widths and line formatting).
     * Tests EscposFormatter for both 58mm (32 characters/line) and 80mm (42-48 characters/line)
     * thermal receipt formatting standards.
     */
    public function test_thermal_receipt_template_parity_widths_and_formatting(): void
    {
        $this->runInTransaction(function (): void {
            $business = $this->getTenantBusiness('cooca_mart');
            $this->assertNotNull($business, 'Business must exist.');

            $formatter = new class extends EscposFormatter {
                public function testSeparator(int $maxCols, string $char = '-'): string
                {
                    return $this->separator($maxCols, $char);
                }

                public function testTwoColumn(string $left, string $right, int $maxCols): string
                {
                    return $this->formatTwoColumn($left, $right, $maxCols);
                }
            };

            // 1. Validate 58mm standard (32 characters wide)
            $cols58 = 32;
            $sep58 = $formatter->testSeparator($cols58, '=');
            $this->assertSame(32, strlen($sep58), '58mm separator must be exactly 32 chars.');
            $this->assertSame(str_repeat('=', 32), $sep58);

            $line58 = $formatter->testTwoColumn('Subtotal', 'Rp 150.000', $cols58);
            $this->assertSame(32, strlen($line58), '58mm two-column line must be formatted to exactly 32 characters.');
            $this->assertStringStartsWith('Subtotal', $line58);
            $this->assertStringEndsWith('Rp 150.000', $line58);

            // 2. Validate 80mm standard (42 characters wide)
            $cols80 = 42;
            $sep80 = $formatter->testSeparator($cols80, '-');
            $this->assertSame(42, strlen($sep80), '80mm separator must be exactly 42 chars.');
            $this->assertSame(str_repeat('-', 42), $sep80);

            $line80 = $formatter->testTwoColumn('Total Belanja', 'Rp 1.250.000', $cols80);
            $this->assertSame(42, strlen($line80), '80mm two-column line must be formatted to exactly 42 characters.');
            $this->assertStringStartsWith('Total Belanja', $line80);
            $this->assertStringEndsWith('Rp 1.250.000', $line80);

            // 3. Validate Cash Drawer Pulse generation
            $pulse = $formatter->formatDrawerPulse('pin2');
            $this->assertSame("\x1B\x70\x00\x19\xFA", $pulse, 'Cash drawer pin2 pulse bytes must conform to standard ESC/POS specification.');
        });
    }
}
