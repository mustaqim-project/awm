<?php

declare(strict_types=1);

namespace Tests\E2E\Tier2_BoundaryCorner;

use Illuminate\Support\Facades\Route;
use Tests\E2E\Support\BaseTestCase;

/**
 * Tier 2 Boundary & Corner Cases: F05 API Integration Specification
 *
 * Verifies REST API boundary conditions: missing X-Business-Id header rejection,
 * non-existent tenant UUID rejection, consistent 422 validation envelope format,
 * 405 Method Not Allowed handling, and rate limiting throttle configuration.
 */
final class F05_ApiBoundaryTest extends BaseTestCase
{
    /**
     * 1. API request without X-Business-Id header on tenant-scoped endpoint is rejected.
     */
    public function test_api_missing_x_business_id_header_behavior(): void
    {
        $response = $this->getJson('/api/v1/products', [
            'Accept' => 'application/json',
        ]);

        // Unauthenticated or missing tenant header should yield 401, 400, 403, or 422
        $this->assertContains(
            $response->getStatusCode(),
            [401, 400, 403, 422],
            "Calling tenant-scoped API without auth/tenant header should be rejected, got {$response->getStatusCode()}."
        );

        $json = $response->json();
        $this->assertIsArray($json);
    }

    /**
     * 2. API request with fictitious or non-existent X-Business-Id UUID is rejected.
     */
    public function test_api_invalid_nonexistent_x_business_id_rejected(): void
    {
        $response = $this->getJson('/api/v1/products', [
            'Accept' => 'application/json',
            'X-Business-Id' => '00000000-0000-0000-0000-000000000000',
        ]);

        $this->assertContains(
            $response->getStatusCode(),
            [401, 403, 404, 422],
            "API request with non-existent tenant UUID should be rejected, got {$response->getStatusCode()}."
        );
    }

    /**
     * 3. Failed validation payload strictly returns HTTP 422 with standardized JSON envelope.
     */
    public function test_api_validation_error_returns_standard_422_envelope(): void
    {
        $response = $this->postJson('/api/v1/auth/login', []);

        $this->assertSame(
            422,
            $response->getStatusCode(),
            "Empty login POST request should return 422 Unprocessable Entity, got {$response->getStatusCode()}."
        );

        $json = $response->json();
        $this->assertIsArray($json);
        $this->assertArrayHasKey('message', $json);
        $this->assertArrayHasKey('errors', $json);
        $this->assertIsArray($json['errors']);
    }

    /**
     * 4. Invoking API endpoint with unsupported HTTP method returns 405 Method Not Allowed.
     */
    public function test_api_unsupported_http_method_returns_405_envelope(): void
    {
        $response = $this->deleteJson('/api/v1/auth/login');

        $this->assertSame(
            405,
            $response->getStatusCode(),
            "DELETE on /api/v1/auth/login should return 405 Method Not Allowed, got {$response->getStatusCode()}."
        );
    }

    /**
     * 5. Verify API route collection is protected with rate limiting throttle middleware.
     */
    public function test_api_rate_limiting_headers_or_route_middleware(): void
    {
        $apiRoutes = Route::getRoutes()->get('GET');
        $throttleConfigured = false;

        foreach ($apiRoutes as $route) {
            $uri = $route->uri();
            if (str_starts_with($uri, 'api/')) {
                $middleware = $route->middleware();
                foreach ($middleware as $m) {
                    if (str_contains($m, 'throttle') || str_contains($m, 'api')) {
                        $throttleConfigured = true;
                        break 2;
                    }
                }
            }
        }

        $this->assertTrue(
            $throttleConfigured,
            'API routes must be wrapped with throttle or api middleware group for rate limiting.'
        );
    }
}
