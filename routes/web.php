<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Web Routes Registry
|--------------------------------------------------------------------------
|
| Routes are cleanly segregated into dedicated domain modules for maximum
| security, clear role boundaries, and ease of maintenance:
|
| 1. admin.php    - Platform Superadmin authentication and backoffice panel
| 2. auth.php     - Business owner & employee authentication (login, register, OTP)
| 3. customer.php - Customer Google-only OAuth, customer portal & checkout gates
| 4. owner.php    - Business owner workspace (ERP, POS, Inventory, Finance, Billing)
| 5. public.php   - Public marketing, calculators, blog, sitemap, and storefront landing
|
*/

require __DIR__ . '/admin.php';
require __DIR__ . '/auth.php';
require __DIR__ . '/owner.php';
require __DIR__ . '/customer.php';
require __DIR__ . '/public.php';

// Global Multi-Language Locale Switcher (ID / EN)
\Illuminate\Support\Facades\Route::match(['GET', 'POST'], '/locale/{locale}', [\App\Http\Controllers\Web\LocaleController::class, 'switch'])
    ->name('locale.switch');

// Protected Payout Transfer Proof Document (Authorized Superadmin or Merchant Owner Only)
\Illuminate\Support\Facades\Route::get('/settlements/{settlement}/proof', [\App\Http\Controllers\Common\SettlementProofController::class, 'show'])
    ->name('settlements.proof');

// Direct Webhook Fallbacks (in case configured in TriPay without /api prefix)
\Illuminate\Support\Facades\Route::match(['GET', 'POST', 'HEAD'], '/payments/tripay/callback', [\App\Http\Controllers\Api\V1\Payment\TripayCallbackController::class, 'handle']);
\Illuminate\Support\Facades\Route::match(['GET', 'POST', 'HEAD'], '/tripay/callback', [\App\Http\Controllers\Api\V1\Payment\TripayCallbackController::class, 'handle']);

// RFC 8414 & OpenID Connect Discovery for ChatGPT Apps SDK / AI MCP Gateways
\Illuminate\Support\Facades\Route::match(['GET', 'OPTIONS', 'HEAD'], '/.well-known/oauth-authorization-server', [\App\Http\Controllers\Api\V1\Mcp\McpOAuthController::class, 'wellKnownOAuth']);
\Illuminate\Support\Facades\Route::match(['GET', 'OPTIONS', 'HEAD'], '/.well-known/openid-configuration', [\App\Http\Controllers\Api\V1\Mcp\McpOAuthController::class, 'wellKnownOpenId']);

// Top-Level Model Context Protocol (MCP) Gateway & OAuth Endpoints (e.g. https://domain.com/mcp)
\Illuminate\Support\Facades\Route::prefix('mcp')->group(function (): void {
    \Illuminate\Support\Facades\Route::match(['GET', 'OPTIONS', 'HEAD'], '/.well-known/oauth-authorization-server', [\App\Http\Controllers\Api\V1\Mcp\McpOAuthController::class, 'wellKnownOAuth']);
    \Illuminate\Support\Facades\Route::match(['GET', 'OPTIONS', 'HEAD'], '/.well-known/openid-configuration', [\App\Http\Controllers\Api\V1\Mcp\McpOAuthController::class, 'wellKnownOpenId']);
    \Illuminate\Support\Facades\Route::match(['GET', 'OPTIONS', 'HEAD'], '/oauth/authorize', [\App\Http\Controllers\Api\V1\Mcp\McpOAuthController::class, 'authorize']);
    \Illuminate\Support\Facades\Route::post('/oauth/authorize', [\App\Http\Controllers\Api\V1\Mcp\McpOAuthController::class, 'authorize']);
    \Illuminate\Support\Facades\Route::match(['POST', 'OPTIONS'], '/oauth/token', [\App\Http\Controllers\Api\V1\Mcp\McpOAuthController::class, 'token']);
    \Illuminate\Support\Facades\Route::match(['POST', 'OPTIONS'], '/oauth/register', [\App\Http\Controllers\Api\V1\Mcp\McpOAuthController::class, 'register']);
    \Illuminate\Support\Facades\Route::match(['GET', 'OPTIONS', 'HEAD'], '/oauth/userinfo', [\App\Http\Controllers\Api\V1\Mcp\McpOAuthController::class, 'userinfo']);

    // Core MCP Streamable HTTP & SSE Handlers (Direct /mcp)
    \Illuminate\Support\Facades\Route::match(['GET', 'POST', 'OPTIONS', 'HEAD'], '/', [\App\Http\Controllers\Api\V1\Mcp\McpSseController::class, 'handleEndpoint'])->middleware(['mcp.auth', 'throttle:120,1']);
    \Illuminate\Support\Facades\Route::match(['GET', 'POST', 'OPTIONS', 'HEAD'], '/sse', [\App\Http\Controllers\Api\V1\Mcp\McpSseController::class, 'handleEndpoint'])->middleware(['mcp.auth', 'throttle:120,1']);
    \Illuminate\Support\Facades\Route::match(['GET', 'POST', 'OPTIONS', 'HEAD'], '/message', [\App\Http\Controllers\Api\V1\Mcp\McpSseController::class, 'message'])->middleware(['mcp.auth', 'throttle:120,1']);
    \Illuminate\Support\Facades\Route::match(['GET', 'OPTIONS', 'HEAD'], '/openapi.json', [\App\Http\Controllers\Api\V1\Mcp\McpSseController::class, 'openapi'])->middleware(['mcp.auth', 'throttle:120,1']);
    \Illuminate\Support\Facades\Route::match(['POST', 'OPTIONS'], '/tools/{tool}/execute', [\App\Http\Controllers\Api\V1\Mcp\McpSseController::class, 'executeDirect'])->middleware(['mcp.auth', 'throttle:120,1']);
});

// Catch-all Canonical Redirection: Eliminate any accidental /public prefix
\Illuminate\Support\Facades\Route::any('/public/{any?}', function (?string $any = null) {
    return redirect('/' . ($any ?? ''), 301);
})->where('any', '.*');


