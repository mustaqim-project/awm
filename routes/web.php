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

// Dev/Test Preview Routes for Session-Gated Auth Views
if (app()->environment('local', 'testing')) {
    \Illuminate\Support\Facades\Route::prefix('test-preview')->group(function (): void {
        \Illuminate\Support\Facades\Route::get('/auth-complete-profile', function () {
            $user = \App\Models\User::first() ?? new \App\Models\User([
                'id' => 1,
                'name' => 'Budi Santoso',
                'email' => 'budi@example.com',
                'phone' => '081234567890',
            ]);
            return view('auth.complete-profile', [
                'user' => $user,
                'primaryLocation' => null,
            ]);
        });

        \Illuminate\Support\Facades\Route::get('/auth-select-business', function () {
            $dummyBusinesses = collect([
                (object)[
                    'id' => 1,
                    'name' => 'Kopi Kenangan Senopati',
                    'currency_code' => 'IDR',
                    'currency_symbol' => 'Rp',
                    'is_active' => true,
                    'role_label' => 'Owner',
                    'branches_count' => 3,
                    'created_at' => now()->subMonths(6),
                ],
                (object)[
                    'id' => 2,
                    'name' => 'Laundry Express Bersih',
                    'currency_code' => 'IDR',
                    'currency_symbol' => 'Rp',
                    'is_active' => true,
                    'role_label' => 'Manager',
                    'branches_count' => 1,
                    'created_at' => now()->subMonths(2),
                ],
            ]);
            return view('auth.select-business', [
                'businesses' => $dummyBusinesses,
            ]);
        });

        \Illuminate\Support\Facades\Route::get('/auth-otp', function () {
            return view('auth.otp', [
                'phone' => '081234567890',
                'maskedEmail' => 'u***@cooca.id',
                'channel' => 'whatsapp',
                'expiresAt' => time() + 300,
                'resendIn' => 60,
            ]);
        });

        \Illuminate\Support\Facades\Route::get('/auth-register-otp', function () {
            return view('auth.register-otp', [
                'phone' => '081234567890',
                'maskedEmail' => 'u***@cooca.id',
                'channel' => 'whatsapp',
                'expiresAt' => time() + 300,
                'resendIn' => 60,
            ]);
        });

        \Illuminate\Support\Facades\Route::get('/auth-google-register', function () {
            return view('auth.google-register', [
                'pending' => [
                    'email' => 'owner@example.com',
                    'name' => 'Budi Santoso',
                    'avatar' => null,
                ],
            ]);
        });

        \Illuminate\Support\Facades\Route::get('/auth-verify-email', function () {
            if (!auth()->check()) {
                $user = \App\Models\User::first() ?? new \App\Models\User([
                    'id' => 1,
                    'name' => 'Demo User',
                    'email' => 'demo@cooca.id',
                ]);
                auth()->login($user);
            }
            return view('auth.verify-email');
        });

        \Illuminate\Support\Facades\Route::get('/auth-reset-password', function () {
            return view('auth.reset-password', [
                'token' => 'dummy-reset-token-12345',
                'email' => 'demo@cooca.id',
                'request' => request(),
            ]);
        });

        \Illuminate\Support\Facades\Route::get('/auth-recovery-status', function () {
            $recovery = \App\Models\AccountRecoveryRequest::first();
            if (!$recovery) {
                $recovery = new \App\Models\AccountRecoveryRequest([
                    'ticket_number' => 'REC-2026-0089',
                    'status' => 'pending',
                    'applicant_name' => 'Budi Santoso',
                    'business_name' => 'Kopi Maju Jaya',
                    'old_email' => 'lama@example.com',
                    'old_phone' => '081299998888',
                    'new_email' => 'baru@example.com',
                    'new_phone' => '081234567890',
                    'issue_type' => 'lost_device',
                ]);
                $recovery->created_at = now();
            }
            return view('auth.account-recovery.status', [
                'recovery' => $recovery,
            ]);
        });
    });
}

// Catch-all Canonical Redirection: Eliminate any accidental /public prefix
\Illuminate\Support\Facades\Route::any('/public/{any?}', function (?string $any = null) {
    return redirect('/' . ($any ?? ''), 301);
})->where('any', '.*');


