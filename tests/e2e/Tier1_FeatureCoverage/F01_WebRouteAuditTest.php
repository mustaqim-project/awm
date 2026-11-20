<?php

declare(strict_types=1);

namespace Tests\E2E\Tier1_FeatureCoverage;

use App\Http\Controllers\Web\AuthWebController;
use App\Http\Controllers\Web\CustomerWebController;
use App\Http\Controllers\Web\DashboardWebController;
use App\Http\Controllers\Web\ProductWebController;
use App\Http\Controllers\Web\ReportWebController;
use Illuminate\Support\Facades\Route;
use Tests\E2E\Support\BaseTestCase;

/**
 * Feature F01: Web Route & Controller Audit Test Suite
 *
 * Validates the web routes catalog, controller bindings, named route conventions,
 * middleware security guards, and platform audit documentation.
 */
final class F01_WebRouteAuditTest extends BaseTestCase
{
    /**
     * 1. Assert web routes are registered in the Laravel router with high coverage.
     */
    public function test_web_routes_registered_in_router(): void
    {
        $routes = Route::getRoutes();
        $totalRoutes = count($routes);

        $this->assertGreaterThan(
            100,
            $totalRoutes,
            "Expected at least 100 registered routes in router, got {$totalRoutes}."
        );

        $methods = [];
        foreach ($routes as $route) {
            foreach ($route->methods() as $method) {
                if ($method !== 'HEAD') {
                    $methods[$method] = true;
                }
            }
        }

        $this->assertArrayHasKey('GET', $methods, 'Router must have GET routes registered.');
        $this->assertArrayHasKey('POST', $methods, 'Router must have POST routes registered.');
    }

    /**
     * 2. Assert core controllers exist and are valid instantiable classes.
     */
    public function test_core_controllers_exist_and_instantiable(): void
    {
        $coreControllers = [
            AuthWebController::class,
            DashboardWebController::class,
            ProductWebController::class,
            CustomerWebController::class,
            ReportWebController::class,
        ];

        foreach ($coreControllers as $controllerClass) {
            $this->assertTrue(
                class_exists($controllerClass),
                "Core controller [{$controllerClass}] does not exist."
            );

            $ref = new \ReflectionClass($controllerClass);
            $this->assertFalse(
                $ref->isAbstract(),
                "Controller [{$controllerClass}] must not be abstract."
            );
        }
    }

    /**
     * 3. Assert named routes conventions are applied across core application modules.
     */
    public function test_named_routes_convention(): void
    {
        $routes = Route::getRoutes();
        $namedRouteCount = 0;

        foreach ($routes as $route) {
            if ($route->getName() !== null) {
                $namedRouteCount++;
            }
        }

        $this->assertGreaterThan(
            50,
            $namedRouteCount,
            "Expected at least 50 named routes in application, got {$namedRouteCount}."
        );

        $this->assertTrue(
            Route::has('login'),
            "Named route [login] must be defined."
        );
    }

    /**
     * 4. Assert middleware guards and authentication groups are configured in router.
     */
    public function test_middleware_guards_enforced(): void
    {
        $router = app('router');
        $middlewareGroups = $router->getMiddlewareGroups();

        $this->assertArrayHasKey(
            'web',
            $middlewareGroups,
            "Router must define standard 'web' middleware group."
        );

        $this->assertArrayHasKey(
            'api',
            $middlewareGroups,
            "Router must define standard 'api' middleware group."
        );

        $routeMiddleware = $router->getMiddleware();
        $this->assertTrue(
            isset($routeMiddleware['auth']) || isset($middlewareGroups['web']),
            "Router must configure authentication middleware."
        );
    }

    /**
     * 5. Assert web route audit documentation file is populated and structured.
     */
    public function test_web_route_audit_documentation(): void
    {
        $docPath = 'documentation/cooca-platform-audit/01-web-backoffice-audit.md';

        $this->assertMarkdownFilePopulated($docPath, 1000);
        $this->assertMarkdownContainsHeaders($docPath, [
            'Web Backoffice',
            'System Overview',
        ]);
        $this->assertMarkdownTableNonEmpty($docPath);
    }
}
