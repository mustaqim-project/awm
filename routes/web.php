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
    \Illuminate\Support\Facades\Route::match(['GET', 'POST', 'OPTIONS', 'HEAD'], '', [\App\Http\Controllers\Api\V1\Mcp\McpSseController::class, 'handleEndpoint'])->middleware(['mcp.auth', 'throttle:120,1']);
    \Illuminate\Support\Facades\Route::match(['GET', 'POST', 'OPTIONS', 'HEAD'], '/', [\App\Http\Controllers\Api\V1\Mcp\McpSseController::class, 'handleEndpoint'])->middleware(['mcp.auth', 'throttle:120,1']);
    \Illuminate\Support\Facades\Route::match(['GET', 'POST', 'OPTIONS', 'HEAD'], '/sse', [\App\Http\Controllers\Api\V1\Mcp\McpSseController::class, 'handleEndpoint'])->middleware(['mcp.auth', 'throttle:120,1']);
    \Illuminate\Support\Facades\Route::match(['GET', 'POST', 'OPTIONS', 'HEAD'], '/message', [\App\Http\Controllers\Api\V1\Mcp\McpSseController::class, 'message'])->middleware(['mcp.auth', 'throttle:120,1']);
    \Illuminate\Support\Facades\Route::match(['GET', 'OPTIONS', 'HEAD'], '/openapi.json', [\App\Http\Controllers\Api\V1\Mcp\McpSseController::class, 'openapi'])->middleware(['mcp.auth', 'throttle:120,1']);
    \Illuminate\Support\Facades\Route::match(['POST', 'OPTIONS'], '/tools/{tool}/execute', [\App\Http\Controllers\Api\V1\Mcp\McpSseController::class, 'executeDirect'])->middleware(['mcp.auth', 'throttle:120,1']);
});

// Direct Storage File Server Fallback: Prevents 404s when storage symlink is absent on shared hosting (Hostinger)
\Illuminate\Support\Facades\Route::get('/storage/{path}', function (string $path) {
    $cleanPath = ltrim($path, '/');
    $disk = \Illuminate\Support\Facades\Storage::disk('public');
    if ($disk->exists($cleanPath)) {
        return response()->file($disk->path($cleanPath));
    }
    if (file_exists(public_path('storage/' . $cleanPath))) {
        return response()->file(public_path('storage/' . $cleanPath));
    }
    // Safe placeholder fallback for AI Studio assets
    if (str_starts_with($cleanPath, 'ai-studio/')) {
        $placeholder = public_path('images/ai-studio/placeholder.svg');
        if (file_exists($placeholder)) {
            return response()->file($placeholder, [
                'Content-Type' => 'image/svg+xml',
                'Cache-Control' => 'public, max-age=86400',
            ]);
        }
    }
    abort(404);
})->where('path', '.*');

// ──────────────────────────────────────────────────────────────────
// Mobile App In-App OTA Update & APK Gateway (Web Fallback)
// Handles direct calls without /api/v1 prefix from installed mobile apps
// ──────────────────────────────────────────────────────────────────
\Illuminate\Support\Facades\Route::get('/app/version', function () {
    $apkFile = public_path('cooca.apk');
    $hasApk = file_exists($apkFile);
    $fileSize = $hasApk ? filesize($apkFile) : 0;
    $fileModTime = $hasApk ? filemtime($apkFile) : 0;

    return response()->json([
        'success' => true,
        'app_name' => 'COOCA My Own',
        'package_name' => 'com.cooca.myown.cooca_my_own',
        'latest_version' => '1.0.1',
        'latest_build' => 2,
        'min_supported_version' => '1.0.0',
        'force_update' => false,
        'download_url' => url('/cooca.apk'),
        'file_size' => $fileSize,
        'updated_at' => $fileModTime ? date('c', $fileModTime) : null,
        'release_notes' => [
            'Perbaikan layout kasir POS bebas tumpang tindih dan responsif untuk mobile.',
            'Kategori produk horizontal yang lebih luas dan mudah dioperasikan.',
            'Pembaruan alur Google Sign-In Play Services yang cepat dan stabil.',
            'Pembaruan sistem in-app langsung tanpa download manual dari browser.',
        ],
    ]);
});

\Illuminate\Support\Facades\Route::get('/download/apk', function () {
    $apkFile = public_path('cooca.apk');
    if (!file_exists($apkFile)) {
        abort(404, 'File APK belum tersedia di server.');
    }
    return response()->file($apkFile, [
        'Content-Type' => 'application/vnd.android.package-archive',
        'Content-Disposition' => 'attachment; filename="cooca.apk"',
    ]);
});

\Illuminate\Support\Facades\Route::get('/download/cooca.apk', function () {
    return redirect('/download/apk');
});

// Catch-all Canonical Redirection: Eliminate any accidental /public prefix
\Illuminate\Support\Facades\Route::any('/public/{any?}', function (?string $any = null) {
    return redirect('/' . ($any ?? ''), 301);
})->where('any', '.*');


