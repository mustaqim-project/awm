<?php

declare(strict_types=1);

namespace Tests\E2E\Tier1_FeatureCoverage;

use Illuminate\Support\Facades\Route;
use Tests\E2E\Support\BaseTestCase;

/**
 * Feature F05: API Integration Specification Test Suite
 *
 * Validates the REST API v1 catalog, Sanctum authentication guard enforcement,
 * JSON error envelope contracts, and multi-tenant header handling.
 */
final class F05_ApiIntegrationTest extends BaseTestCase
{
    private string $apiDoc = 'documentation/cooca-platform-audit/06-api-integration.md';

    /**
     * 1. Assert API integration specification document exists and is populated.
     */
    public function test_api_integration_documentation_exists(): void
    {
        $this->assertMarkdownFilePopulated($this->apiDoc, 1000);
        $this->assertMarkdownContainsHeaders($this->apiDoc, [
            'API Integration Specification',
            'Integration Architecture',
        ]);
    }

    /**
     * 2. Assert API v1 routes are registered in the Laravel route collection.
     */
    public function test_api_v1_routes_registered(): void
    {
        $routes = Route::getRoutes();
        $apiV1Routes = [];

        foreach ($routes as $route) {
            $uri = $route->uri();
            if (str_starts_with($uri, 'api/v1') || str_starts_with($uri, 'v1')) {
                $apiV1Routes[] = $uri;
            }
        }

        $this->assertGreaterThan(
            20,
            count($apiV1Routes),
            'Expected at least 20 API v1 routes registered in router.'
        );
    }

    /**
     * 3. Assert Sanctum authentication middleware is bound to protected API routes.
     */
    public function test_sanctum_auth_middleware_configured(): void
    {
        $routes = Route::getRoutes();
        $sanctumRouteCount = 0;

        foreach ($routes as $route) {
            $middleware = $route->gatherMiddleware();
            foreach ($middleware as $m) {
                if (is_string($m) && str_starts_with($m, 'auth:sanctum')) {
                    $sanctumRouteCount++;
                    break;
                }
            }
        }

        $this->assertGreaterThan(
            10,
            $sanctumRouteCount,
            'Expected at least 10 routes protected by auth:sanctum middleware.'
        );
    }

    /**
     * 4. Assert unauthenticated requests to protected endpoints return 401 with standard error envelope.
     */
    public function test_api_unauthorized_envelope_structure(): void
    {
        $response = $this->apiJson('GET', '/api/v1/auth/me');

        $response->assertStatus(401);
        $json = $response->json();

        $this->assertIsArray($json, 'API 401 response must return JSON structure.');
        $this->assertTrue(
            isset($json['message']) || isset($json['error']) || isset($json['success']),
            'API error envelope must contain message, error, or success indicator.'
        );
    }

    /**
     * 5. Assert API client helper and contract accept X-Business-Id multi-tenant header.
     */
    public function test_api_multi_tenant_header_handling(): void
    {
        $businessId = $this->getTenantBusinessId('cooca_mart');
        $this->assertNotNull($businessId, 'Tenant business [cooca_mart] must be resolved.');

        // Dispatch unauthenticated request with business ID header
        $response = $this->apiJson('GET', '/api/v1/context/current', [], [], $businessId);

        // Even if 401 unauthorized, response must be JSON, confirming header did not crash gateway
        $this->assertContains(
            $response->status(),
            [200, 401, 403, 404],
            'API request with X-Business-Id must return valid HTTP status code.'
        );
        $response->assertHeader('content-type', 'application/json');
    }
}
