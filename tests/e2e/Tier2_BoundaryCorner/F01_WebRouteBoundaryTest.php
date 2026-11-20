<?php

declare(strict_types=1);

namespace Tests\E2E\Tier2_BoundaryCorner;

use Tests\E2E\Support\BaseTestCase;

/**
 * Tier 2 Boundary & Corner Cases: F01 Web Route & Controller Audit
 *
 * Verifies route boundary conditions: 404 for non-existent routes, SQL injection
 * rejection/sanitization, guest redirects on protected routes, invalid HTTP method
 * rejection (405), and query parameter XSS injection protection.
 */
final class F01_WebRouteBoundaryTest extends BaseTestCase
{
    /**
     * 1. Requesting non-existent fictitious URLs returns HTTP 404 Not Found.
     */
    public function test_non_existent_route_returns_404(): void
    {
        $fictitiousUris = [
            '/system-fictitious-probe-' . uniqid(),
            '/admin/non-existent-secret-area-' . uniqid(),
            '/app/unknown-module-' . uniqid(),
        ];

        foreach ($fictitiousUris as $uri) {
            $response = $this->get($uri);
            $this->assertSame(
                404,
                $response->getStatusCode(),
                "Non-existent route [{$uri}] should return 404 Not Found, got {$response->getStatusCode()}."
            );
        }
    }

    /**
     * 2. SQL injection patterns in route parameters are safely rejected or sanitized
     * without leaking database exceptions or syntax errors.
     */
    public function test_sql_injection_in_route_param_rejected_or_sanitized(): void
    {
        $sqliPayloads = [
            "/products/1'%20OR%20'1'='1",
            "/app/orders/999999%20UNION%20SELECT%20null,null,null--",
            "/customers/1;%20DROP%20TABLE%20users--",
        ];

        foreach ($sqliPayloads as $payloadUri) {
            $response = $this->get($payloadUri);

            // Must NOT crash with 500 containing raw SQL error/syntax dump
            $statusCode = $response->getStatusCode();
            $this->assertContains(
                $statusCode,
                [302, 400, 403, 404, 405],
                "SQLi payload [{$payloadUri}] resulted in unexpected status {$statusCode}."
            );

            $content = $response->getContent() ?: '';
            $this->assertStringNotContainsStringIgnoringCase('SQLSTATE', $content);
            $this->assertStringNotContainsStringIgnoringCase('syntax error', $content);
            $this->assertStringNotContainsStringIgnoringCase('mysqli_', $content);
        }
    }

    /**
     * 3. Unauthenticated guest access to protected backoffice routes strictly redirects to login.
     */
    public function test_guest_unauthenticated_access_to_protected_routes_redirects_to_login(): void
    {
        $protectedRoutes = [
            '/dashboard',
            '/profile',
            '/finance/chart-of-accounts',
        ];

        foreach ($protectedRoutes as $route) {
            $response = $this->get($route);
            $statusCode = $response->getStatusCode();

            $this->assertTrue(
                in_array($statusCode, [302, 401, 403], true),
                "Protected route [{$route}] allowed guest access with status {$statusCode}."
            );

            if ($statusCode === 302) {
                $location = (string) $response->headers->get('Location');
                $this->assertStringContainsString(
                    'login',
                    strtolower($location),
                    "Redirect from protected route [{$route}] did not redirect to a login destination."
                );
            }
        }
    }

    /**
     * 4. Invoking defined routes with unsupported HTTP methods returns 405 Method Not Allowed or redirects.
     */
    public function test_invalid_http_method_on_defined_route_returns_405(): void
    {
        // Route::get('login') should not accept DELETE or PUT
        $response = $this->delete('/login');
        $this->assertTrue(
            in_array($response->getStatusCode(), [404, 405], true),
            "DELETE /login should return 405 Method Not Allowed or 404, got {$response->getStatusCode()}."
        );

        // Route::post('logout') should not accept GET without CSRF/auth
        $logoutGetResponse = $this->get('/logout');
        $this->assertTrue(
            in_array($logoutGetResponse->getStatusCode(), [404, 405, 302], true),
            "GET /logout should not succeed with 200, got {$logoutGetResponse->getStatusCode()}."
        );
    }

    /**
     * 5. Malicious XSS script injection in query parameters is escaped in rendered output.
     */
    public function test_xss_script_injection_in_query_parameters_sanitized(): void
    {
        $xssVector = '<script>alert("COOCA_XSS_PROBE")</script>';
        $response = $this->get('/login?ref=' . urlencode($xssVector));

        $content = $response->getContent() ?: '';
        $this->assertStringNotContainsString(
            $xssVector,
            $content,
            "Unescaped executable script tag detected in response for query parameter with XSS vector."
        );
    }
}
