<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Mcp;

use App\Domain\Mcp\Tools\McpToolRegistry;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\McpAccessToken;
use App\Models\McpActivityLog;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

final class McpIntegrationWebController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('require.permission:settings.view', only: [
                'index',
                'logs',
            ]),
            new Middleware('require.permission:settings.edit', only: [
                'storeToken',
                'updateToken',
                'revokeToken',
                'toggleMaster',
            ]),
        ];
    }

    public function __construct(
        private readonly McpToolRegistry $toolRegistry = new McpToolRegistry()
    ) {}

    /**
     * Tampilkan halaman Cockpit Integrasi MCP & AI Assistant.
     */
    public function index(Request $request): View
    {
        $business = Context::requireBusiness();

        $tokens = McpAccessToken::where('business_id', $business->id)
            ->with(['user'])
            ->latest()
            ->get();

        $tools = $this->toolRegistry->getAllTools();

        $logs = McpActivityLog::where('business_id', $business->id)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $totalCallsThisMonth = McpActivityLog::where('business_id', $business->id)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $activeTokensCount = $tokens->where('is_active', true)->count();
        $isMcpActive = $activeTokensCount > 0;

        $apiBaseUrl = url('/api/v1/mcp');
        $sseEndpoint = url('/api/v1/mcp/sse');
        $openApiEndpoint = url('/api/v1/mcp/openapi.json');
        $topLevelMcpUrl = url('/mcp');

        // OAuth 2.0 & OIDC Endpoints for ChatGPT Apps SDK / Connectors
        $oauthAuthorizeUrl = url('/api/v1/mcp/oauth/authorize');
        $oauthTokenUrl = url('/api/v1/mcp/oauth/token');
        $oauthRegisterUrl = url('/api/v1/mcp/oauth/register');
        $oauthUserInfoUrl = url('/api/v1/mcp/oauth/userinfo');
        $oauthIssuerUrl = url('/');
        $oidcConfigUrl = url('/.well-known/openid-configuration');
        $oauthMetadataUrl = url('/.well-known/oauth-authorization-server');

        return view('app.settings.integrations.mcp', compact(
            'business',
            'tokens',
            'tools',
            'logs',
            'totalCallsThisMonth',
            'activeTokensCount',
            'isMcpActive',
            'apiBaseUrl',
            'sseEndpoint',
            'openApiEndpoint',
            'topLevelMcpUrl',
            'oauthAuthorizeUrl',
            'oauthTokenUrl',
            'oauthRegisterUrl',
            'oauthUserInfoUrl',
            'oauthIssuerUrl',
            'oidcConfigUrl',
            'oauthMetadataUrl'
        ));
    }

    /**
     * Buat Kunci Akses MCP baru untuk AI Client / Provider.
     */
    public function storeToken(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();
        $user = auth()->user() ?? Context::user();

        $entitlementService = app(\App\Domain\Billing\EntitlementService::class);
        if (! $entitlementService->canAccessMcp($business)) {
            return redirect()->route('billing.limits')->with('error', 'Fitur Integrasi AI Gateway (MCP) memerlukan langganan paket aktif (Standard, Premium, atau Prestige). Silakan tingkatkan paket bisnis Anda.');
        }

        $sub = $entitlementService->getSubscription($business);
        $tier = $sub->getTier();
        $activeTokensCount = McpAccessToken::where('business_id', $business->id)->where('is_active', true)->count();
        if ($tier === \App\Models\BusinessSubscription::TIER_STANDARD && $activeTokensCount >= 1) {
            return back()->with('error', 'Paket Standard terbatas maksimal 1 token MCP aktif. Tingkatkan ke Paket Premium untuk token MCP tak terbatas (multi-client).');
        }

        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:100'],
            'provider_hint' => ['required', 'string', 'in:claude,openai,gemini,cursor,ollama,langchain,dola,all,custom'],
            'abilities'     => ['required', 'array', 'min:1'],
            'abilities.*'   => ['string', 'max:50'],
            'expires_in'    => ['nullable', 'string', 'in:never,30_days,90_days,1_year'],
        ]);

        $expiresAt = match ($validated['expires_in'] ?? 'never') {
            '30_days' => now()->addDays(30),
            '90_days' => now()->addDays(90),
            '1_year'  => now()->addYear(),
            default   => null,
        };

        $result = McpAccessToken::generateToken(
            business: $business,
            user: $user,
            name: $validated['name'],
            abilities: $validated['abilities'],
            providerHint: $validated['provider_hint'],
            expiresAt: $expiresAt
        );

        AuditLog::create([
            'business_id' => $business->id,
            'user_id' => $user?->id,
            'action' => 'mcp.token_created',
            'auditable_type' => McpAccessToken::class,
            'auditable_id' => $result['model']->id,
            'old_values' => null,
            'new_values' => [
                'name' => $result['model']->name,
                'provider_hint' => $result['model']->provider_hint,
                'abilities' => $result['model']->abilities,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent() ?: 'COOCA-Web',
        ]);

        return redirect()
            ->route('settings.integrations.mcp.index')
            ->with('success', __('mcp.token_created_success'))
            ->with('plain_token', $result['token'])
            ->with('token_name', $result['model']->name);
    }

    /**
     * Perbarui konfigurasi & hak akses (abilities) token MCP.
     */
    public function updateToken(Request $request, McpAccessToken $token): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless($token->business_id === $business->id, 403);

        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:100'],
            'provider_hint' => ['required', 'string', 'in:claude,openai,gemini,cursor,ollama,langchain,dola,all,custom'],
            'abilities'     => ['required', 'array', 'min:1'],
            'abilities.*'   => ['string', 'max:50'],
            'is_active'     => ['nullable', 'boolean'],
            'expires_in'    => ['nullable', 'string', 'in:keep,never,30_days,90_days,1_year'],
        ]);

        $oldValues = [
            'name'          => $token->name,
            'provider_hint' => $token->provider_hint,
            'abilities'     => $token->abilities,
            'is_active'     => $token->is_active,
            'expires_at'    => $token->expires_at?->toIso8601String(),
        ];

        $expiresOption = $validated['expires_in'] ?? 'keep';
        $expiresAt = $token->expires_at;

        if ($expiresOption === 'never') {
            $expiresAt = null;
        } elseif ($expiresOption === '30_days') {
            $expiresAt = now()->addDays(30);
        } elseif ($expiresOption === '90_days') {
            $expiresAt = now()->addDays(90);
        } elseif ($expiresOption === '1_year') {
            $expiresAt = now()->addYear();
        }

        $isActive = $request->has('is_active') ? (bool) $request->input('is_active') : $token->is_active;

        $token->update([
            'name'          => $validated['name'],
            'provider_hint' => $validated['provider_hint'],
            'abilities'     => array_values(array_unique($validated['abilities'])),
            'is_active'     => $isActive,
            'expires_at'    => $expiresAt,
        ]);

        AuditLog::create([
            'business_id'    => $business->id,
            'user_id'        => auth()->id(),
            'action'         => 'mcp.token_updated',
            'auditable_type' => McpAccessToken::class,
            'auditable_id'   => $token->id,
            'old_values'     => $oldValues,
            'new_values'     => [
                'name'          => $token->name,
                'provider_hint' => $token->provider_hint,
                'abilities'     => $token->abilities,
                'is_active'     => $token->is_active,
                'expires_at'    => $token->expires_at?->toIso8601String(),
            ],
            'ip_address'     => $request->ip(),
            'user_agent'     => $request->userAgent() ?: 'COOCA-Web',
        ]);

        return redirect()
            ->route('settings.integrations.mcp.index')
            ->with('success', __('mcp.token_updated_success', ['name' => $token->name]));
    }

    /**
     * Cabut / Hapus token akses MCP.
     */
    public function revokeToken(McpAccessToken $token): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless($token->business_id === $business->id, 403);

        $name = $token->name;
        $token->delete();

        AuditLog::create([
            'business_id' => $business->id,
            'user_id' => auth()->id(),
            'action' => 'mcp.token_revoked',
            'auditable_type' => McpAccessToken::class,
            'auditable_id' => $token->id,
            'old_values' => ['name' => $name],
            'new_values' => null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent() ?: 'COOCA-Web',
        ]);

        return redirect()
            ->route('settings.integrations.mcp.index')
            ->with('success', __('mcp.token_revoked_success', ['name' => $name]));
    }

    /**
     * Master switch toggle: Nonaktifkan atau aktifkan semua token MCP bisnis.
     */
    public function toggleMaster(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();
        $targetState = (bool) $request->input('is_active', false);

        McpAccessToken::where('business_id', $business->id)->update([
            'is_active' => $targetState,
        ]);

        AuditLog::create([
            'business_id' => $business->id,
            'user_id' => auth()->id(),
            'action' => 'mcp.master_toggle',
            'auditable_type' => McpAccessToken::class,
            'auditable_id' => $business->id,
            'old_values' => ['master_active' => ! $targetState],
            'new_values' => ['master_active' => $targetState],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent() ?: 'COOCA-Web',
        ]);

        $message = $targetState ? __('mcp.master_enabled') : __('mcp.master_disabled');

        return redirect()->route('settings.integrations.mcp.index')->with('success', $message);
    }

    /**
     * AJAX endpoint untuk live activity log filter.
     */
    public function logs(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $logs = McpActivityLog::where('business_id', $business->id)
            ->latest()
            ->take(20)
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $logs,
        ]);
    }
}
