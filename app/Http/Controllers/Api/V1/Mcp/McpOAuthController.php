<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Mcp;

use App\Domain\Mcp\Auth\McpTokenAuthenticator;
use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\McpAccessToken;
use App\Models\User;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class McpOAuthController extends Controller
{
    public function __construct(
        private readonly McpTokenAuthenticator $tokenAuthenticator = new McpTokenAuthenticator()
    ) {}

    /**
     * Standard CORS headers for OpenAI ChatGPT Apps SDK and OAuth clients.
     *
     * @return array<string, string>
     */
    public static function corsHeaders(): array
    {
        return [
            'Access-Control-Allow-Origin'  => '*',
            'Access-Control-Allow-Methods' => 'GET, POST, OPTIONS, HEAD',
            'Access-Control-Allow-Headers' => 'Authorization, Content-Type, X-Requested-With, mcp-session-id, Accept, Last-Event-ID, x-mcp-token',
            'Access-Control-Max-Age'       => '86400',
        ];
    }

    /**
     * CORS Preflight Options handler for OAuth endpoints.
     */
    public function options(): Response
    {
        return response('', Response::HTTP_NO_CONTENT, self::corsHeaders());
    }

    /**
     * RFC 8414: OAuth 2.0 Authorization Server Metadata.
     * Requested by ChatGPT upon adding a custom MCP server with OAuth.
     */
    public function wellKnownOAuth(): JsonResponse
    {
        $baseUrl = url('/');
        $mcpBase = url('/api/v1/mcp');

        return response()->json([
            'issuer'                                => $baseUrl,
            'authorization_endpoint'                => "{$mcpBase}/oauth/authorize",
            'token_endpoint'                        => "{$mcpBase}/oauth/token",
            'registration_endpoint'                 => "{$mcpBase}/oauth/register",
            'userinfo_endpoint'                     => "{$mcpBase}/oauth/userinfo",
            'response_types_supported'              => ['code'],
            'grant_types_supported'                 => ['authorization_code', 'client_credentials', 'refresh_token'],
            'token_endpoint_auth_methods_supported' => ['client_secret_post', 'client_secret_basic', 'none'],
            'scopes_supported'                      => ['mcp:tools', 'openid', 'profile', 'email'],
            'code_challenge_methods_supported'      => ['S256', 'plain'],
            'service_documentation'                 => url('/settings/integrations/mcp'),
            'ui_locales_supported'                  => ['id', 'en'],
        ], Response::HTTP_OK, self::corsHeaders());
    }

    /**
     * OpenID Connect (OIDC) Discovery Configuration.
     */
    public function wellKnownOpenId(): JsonResponse
    {
        $baseUrl = url('/');
        $mcpBase = url('/api/v1/mcp');

        return response()->json([
            'issuer'                                => $baseUrl,
            'authorization_endpoint'                => "{$mcpBase}/oauth/authorize",
            'token_endpoint'                        => "{$mcpBase}/oauth/token",
            'userinfo_endpoint'                     => "{$mcpBase}/oauth/userinfo",
            'registration_endpoint'                 => "{$mcpBase}/oauth/register",
            'response_types_supported'              => ['code'],
            'subject_types_supported'               => ['public'],
            'id_token_signing_alg_values_supported' => ['HS256', 'none'],
            'scopes_supported'                      => ['openid', 'profile', 'email', 'mcp:tools'],
            'token_endpoint_auth_methods_supported' => ['client_secret_post', 'client_secret_basic', 'none'],
            'claims_supported'                      => ['sub', 'name', 'email', 'email_verified', 'business_id', 'business_name'],
            'code_challenge_methods_supported'      => ['S256', 'plain'],
        ], Response::HTTP_OK, self::corsHeaders());
    }

    /**
     * RFC 7591: Dynamic Client Registration (DCR).
     * Enables ChatGPT to dynamically register its OAuth client without manual API keys.
     */
    public function register(Request $request): JsonResponse
    {
        $payload = $request->all();
        if (empty($payload)) {
            $raw = (string) $request->getContent();
            $payload = json_decode($raw, true) ?: [];
        }

        $clientName = trim((string) ($payload['client_name'] ?? 'ChatGPT MCP Connector'));
        $redirectUris = (array) ($payload['redirect_uris'] ?? ['https://chatgpt.com/aip/oauth/callback']);
        $authMethod = (string) ($payload['token_endpoint_auth_method'] ?? 'client_secret_post');

        $clientId = 'cooca_chatgpt_' . Str::lower(Str::random(16));
        $clientSecret = 'cooca_sec_' . Str::random(36);

        // Store client metadata in Cache for 90 days
        Cache::put("mcp_dcr_client_{$clientId}", [
            'client_id'                  => $clientId,
            'client_secret'              => $clientSecret,
            'client_name'                => $clientName,
            'redirect_uris'              => $redirectUris,
            'token_endpoint_auth_method' => $authMethod,
            'created_at'                 => time(),
        ], now()->addDays(90));

        return response()->json([
            'client_id'                  => $clientId,
            'client_secret'              => $clientSecret,
            'client_id_issued_at'        => time(),
            'client_secret_expires_at'   => 0,
            'client_name'                => $clientName,
            'redirect_uris'              => $redirectUris,
            'grant_types'                => ['authorization_code', 'client_credentials', 'refresh_token'],
            'response_types'             => ['code'],
            'token_endpoint_auth_method' => $authMethod,
        ], Response::HTTP_CREATED, self::corsHeaders());
    }

    /**
     * OAuth 2.0 Authorization Endpoint.
     * Displays consent page or redirects with authorization code back to ChatGPT.
     */
    public function authorize(Request $request): View|RedirectResponse
    {
        $clientId = (string) $request->input('client_id', '');
        $redirectUri = (string) $request->input('redirect_uri', 'https://chatgpt.com');
        $state = (string) $request->input('state', '');
        $scope = (string) $request->input('scope', 'mcp:tools');
        $codeChallenge = (string) $request->input('code_challenge', '');
        $codeChallengeMethod = (string) $request->input('code_challenge_method', 'S256');

        // Require user authentication
        if (! auth()->check()) {
            session()->put('mcp_oauth_request', [
                'client_id'             => $clientId,
                'redirect_uri'          => $redirectUri,
                'state'                 => $state,
                'scope'                 => $scope,
                'code_challenge'        => $codeChallenge,
                'code_challenge_method' => $codeChallengeMethod,
            ]);

            return redirect()->guest(route('login'));
        }

        /** @var \App\Models\User $user */
        $user = auth()->user();

        // Resolve active business
        $business = Context::business()
            ?? $user->businesses()->first()
            ?? Business::first();

        if (! $business) {
            abort(403, 'Anda belum memiliki bisnis aktif di COOCA ID.');
        }

        // 1. Process Approval Form Submission
        if ($request->isMethod('POST') && $request->input('action') === 'approve') {
            // Generate or fetch an active MCP Access Token for ChatGPT
            $tokenData = McpAccessToken::generateToken(
                $business,
                $user,
                'ChatGPT MCP Connector (OAuth)',
                ['*'],
                'openai',
                now()->addYear()
            );

            $authCode = 'cooca_code_' . Str::random(36);

            // Store code in Cache for 10 minutes (RFC 6749)
            Cache::put("mcp_auth_code_{$authCode}", [
                'client_id'             => $clientId,
                'redirect_uri'          => $redirectUri,
                'code_challenge'        => $codeChallenge,
                'code_challenge_method' => $codeChallengeMethod,
                'user_id'               => $user->id,
                'business_id'           => $business->id,
                'plain_token'           => $tokenData['token'],
                'token_id'              => $tokenData['model']->id,
                'scopes'                => $scope,
            ], now()->addMinutes(10));

            $separator = str_contains($redirectUri, '?') ? '&' : '?';
            $callbackUrl = $redirectUri . $separator . http_build_query([
                'code'  => $authCode,
                'state' => $state,
            ]);

            return redirect()->away($callbackUrl);
        }

        // 2. Process Deny Form Submission
        if ($request->isMethod('POST') && $request->input('action') === 'deny') {
            $separator = str_contains($redirectUri, '?') ? '&' : '?';
            $callbackUrl = $redirectUri . $separator . http_build_query([
                'error'             => 'access_denied',
                'error_description' => 'Pengguna menolak otorisasi koneksi ChatGPT.',
                'state'             => $state,
            ]);

            return redirect()->away($callbackUrl);
        }

        // 3. Render OAuth Consent Dialog View
        return view('app.mcp.oauth.consent', [
            'user'                => $user,
            'business'            => $business,
            'clientId'            => $clientId,
            'redirectUri'         => $redirectUri,
            'state'               => $state,
            'scope'               => $scope,
            'codeChallenge'       => $codeChallenge,
            'codeChallengeMethod' => $codeChallengeMethod,
        ]);
    }

    /**
     * OAuth 2.0 Token Endpoint.
     * Exchanges authorization code or client credentials for a valid MCP Bearer Token.
     */
    public function token(Request $request): JsonResponse
    {
        // 1. CORS Preflight
        if ($request->isMethod('OPTIONS')) {
            return response()->json([], Response::HTTP_NO_CONTENT, self::corsHeaders());
        }

        $payload = $request->all();
        if (empty($payload)) {
            $raw = (string) $request->getContent();
            $payload = json_decode($raw, true) ?: [];
        }

        $grantType = (string) ($payload['grant_type'] ?? 'authorization_code');

        // Extract Client ID & Secret from body or HTTP Basic Auth header
        $clientId = (string) ($payload['client_id'] ?? '');
        $clientSecret = (string) ($payload['client_secret'] ?? '');

        if ($request->header('Authorization') && str_starts_with($request->header('Authorization'), 'Basic ')) {
            $decoded = base64_decode(substr($request->header('Authorization'), 6));
            if (str_contains($decoded, ':')) {
                [$basicId, $basicSec] = explode(':', $decoded, 2);
                $clientId = $clientId ?: $basicId;
                $clientSecret = $clientSecret ?: $basicSec;
            }
        }

        // A. GRANT TYPE: authorization_code (Standard ChatGPT Flow)
        if ($grantType === 'authorization_code') {
            $code = (string) ($payload['code'] ?? '');
            $redirectUri = (string) ($payload['redirect_uri'] ?? '');
            $codeVerifier = (string) ($payload['code_verifier'] ?? '');

            if (empty($code)) {
                return response()->json([
                    'error'             => 'invalid_request',
                    'error_description' => 'Missing parameter: code.',
                ], Response::HTTP_BAD_REQUEST, self::corsHeaders());
            }

            $cacheKey = "mcp_auth_code_{$code}";
            $codeData = Cache::get($cacheKey);

            if (! $codeData || ! is_array($codeData)) {
                return response()->json([
                    'error'             => 'invalid_grant',
                    'error_description' => 'The authorization code is invalid or has expired.',
                ], Response::HTTP_BAD_REQUEST, self::corsHeaders());
            }

            // Verify PKCE if code_challenge was provided
            if (! empty($codeData['code_challenge'])) {
                $challenge = $codeData['code_challenge'];
                $method = $codeData['code_challenge_method'] ?? 'S256';

                if ($method === 'S256') {
                    $computed = rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '=');
                    if (! hash_equals($challenge, $computed)) {
                        return response()->json([
                            'error'             => 'invalid_grant',
                            'error_description' => 'PKCE verification failed: code_verifier does not match code_challenge.',
                        ], Response::HTTP_BAD_REQUEST, self::corsHeaders());
                    }
                } elseif (! hash_equals($challenge, $codeVerifier)) {
                    return response()->json([
                        'error'             => 'invalid_grant',
                        'error_description' => 'PKCE verification failed.',
                    ], Response::HTTP_BAD_REQUEST, self::corsHeaders());
                }
            }

            // Code is single-use: invalidate from Cache
            Cache::forget($cacheKey);

            $accessToken = $codeData['plain_token'];

            // Buat refresh token terenkripsi secara kriptografis (tahan banting meski Cache di-clear)
            $refreshTokenPayload = [
                'token_id'    => (string) $codeData['token_id'],
                'user_id'     => (string) $codeData['user_id'],
                'business_id' => (string) $codeData['business_id'],
                'scopes'      => (string) ($codeData['scopes'] ?? 'mcp:tools openid profile email'),
                'created_at'  => time(),
            ];

            try {
                $encryptedRef = Crypt::encryptString(json_encode($refreshTokenPayload, JSON_THROW_ON_ERROR));
                $refreshToken = 'cooca_ref_' . rtrim(strtr(base64_encode($encryptedRef), '+/', '-_'), '=');
            } catch (\Throwable) {
                $refreshToken = 'cooca_mcp_ref_' . Str::random(40);
            }

            // Simpan ke Cache untuk fast lookup (180 hari)
            Cache::put("mcp_refresh_token_{$refreshToken}", [
                'token_id'    => $codeData['token_id'],
                'user_id'     => $codeData['user_id'],
                'business_id' => $codeData['business_id'],
                'plain_token' => $accessToken,
            ], now()->addDays(180));

            return response()->json([
                'access_token'  => $accessToken,
                'token_type'    => 'Bearer',
                'expires_in'    => 31536000, // 1 Year
                'scope'         => $codeData['scopes'] ?? 'mcp:tools openid profile email',
                'refresh_token' => $refreshToken,
            ], Response::HTTP_OK, self::corsHeaders());
        }

        // B. GRANT TYPE: client_credentials (Direct Machine-to-Machine)
        if ($grantType === 'client_credentials') {
            $candidateToken = $clientSecret ?: $clientId;

            // 1. Direct validation if client passed existing COOCA MCP token
            $mcpToken = $this->tokenAuthenticator->authenticate($candidateToken);
            if ($mcpToken !== null && $mcpToken->isValid()) {
                return response()->json([
                    'access_token' => $candidateToken,
                    'token_type'   => 'Bearer',
                    'expires_in'   => 31536000,
                    'scope'        => 'mcp:tools openid profile email',
                ], Response::HTTP_OK, self::corsHeaders());
            }

            // 2. Validate DCR client credentials
            $dcr = Cache::get("mcp_dcr_client_{$clientId}");
            if ($dcr && is_array($dcr) && hash_equals($dcr['client_secret'], $clientSecret)) {
                $business = Business::first();
                $user = $business?->owner() ?? \App\Models\User::first();

                if ($business && $user) {
                    $tokenData = McpAccessToken::generateToken(
                        $business,
                        $user,
                        'ChatGPT Client Credentials',
                        ['*'],
                        'openai',
                        now()->addYear()
                    );

                    return response()->json([
                        'access_token' => $tokenData['token'],
                        'token_type'   => 'Bearer',
                        'expires_in'   => 31536000,
                        'scope'        => 'mcp:tools openid profile email',
                    ], Response::HTTP_OK, self::corsHeaders());
                }
            }

            return response()->json([
                'error'             => 'invalid_client',
                'error_description' => 'Invalid client credentials or MCP Token.',
            ], Response::HTTP_UNAUTHORIZED, self::corsHeaders());
        }

        // C. GRANT TYPE: refresh_token
        if ($grantType === 'refresh_token') {
            $refreshToken = (string) ($payload['refresh_token'] ?? '');
            $refData = Cache::get("mcp_refresh_token_{$refreshToken}");

            if ($refData && is_array($refData) && ! empty($refData['plain_token'])) {
                // Pastikan token akses masih aktif di database
                $existingToken = McpAccessToken::find($refData['token_id'] ?? '');
                if ($existingToken && $existingToken->isValid()) {
                    return response()->json([
                        'access_token'  => $refData['plain_token'],
                        'token_type'    => 'Bearer',
                        'expires_in'    => 31536000,
                        'scope'         => 'mcp:tools openid profile email',
                        'refresh_token' => $refreshToken,
                    ], Response::HTTP_OK, self::corsHeaders());
                }
            }

            // Fallback: Jika cache terhapus (php artisan cache:clear), dekripsi token refresh kriptografis
            if (str_starts_with($refreshToken, 'cooca_ref_')) {
                try {
                    $rawEncrypted = base64_decode(strtr(substr($refreshToken, 10), '-_', '+/'));
                    $decryptedJson = Crypt::decryptString($rawEncrypted);
                    $meta = json_decode($decryptedJson, true, 512, JSON_THROW_ON_ERROR);

                    $existingToken = McpAccessToken::find($meta['token_id'] ?? '');
                    $business = Business::find($meta['business_id'] ?? '');
                    $user = User::find($meta['user_id'] ?? '');

                    if ($existingToken && $existingToken->is_active && $business && $user) {
                        // Terbitkan token akses baru untuk menggantikan yang terhapus di cache
                        $renewed = McpAccessToken::generateToken(
                            $business,
                            $user,
                            'ChatGPT MCP Connector (Renewed via Refresh Token)',
                            ['*'],
                            'openai',
                            now()->addYear()
                        );

                        // Simpan kembali ke Cache
                        Cache::put("mcp_refresh_token_{$refreshToken}", [
                            'token_id'    => $renewed['model']->id,
                            'user_id'     => $user->id,
                            'business_id' => $business->id,
                            'plain_token' => $renewed['token'],
                        ], now()->addDays(180));

                        return response()->json([
                            'access_token'  => $renewed['token'],
                            'token_type'    => 'Bearer',
                            'expires_in'    => 31536000,
                            'scope'         => $meta['scopes'] ?? 'mcp:tools openid profile email',
                            'refresh_token' => $refreshToken,
                        ], Response::HTTP_OK, self::corsHeaders());
                    }
                } catch (\Throwable) {
                    // Gagal dekripsi atau token dicabut
                }
            }

            return response()->json([
                'error'             => 'invalid_grant',
                'error_description' => 'Invalid or expired refresh token.',
            ], Response::HTTP_BAD_REQUEST, self::corsHeaders());
        }

        return response()->json([
            'error'             => 'unsupported_grant_type',
            'error_description' => "Grant type '{$grantType}' is not supported.",
        ], Response::HTTP_BAD_REQUEST, self::corsHeaders());
    }

    /**
     * OpenID Connect (OIDC) UserInfo Endpoint.
     * Returns claims for the authenticated user and their active business.
     */
    public function userinfo(Request $request): JsonResponse
    {
        // 1. CORS Preflight
        if ($request->isMethod('OPTIONS')) {
            return response()->json([], Response::HTTP_NO_CONTENT, self::corsHeaders());
        }

        $rawToken = $request->bearerToken()
            ?: (string) $request->header('x-mcp-token', '')
            ?: (string) $request->query('token', '');

        $token = $this->tokenAuthenticator->authenticate($rawToken);

        if ($token === null || ! $token->isValid()) {
            return response()->json([
                'error'             => 'invalid_token',
                'error_description' => 'The access token is invalid or expired.',
            ], Response::HTTP_UNAUTHORIZED, self::corsHeaders());
        }

        $user = $token->user;
        $business = $token->business;

        return response()->json([
            'sub'            => (string) ($user?->id ?? $token->id),
            'name'           => $user?->name ?? 'Pengguna COOCA ID',
            'email'          => $user?->email ?? 'user@cooca.id',
            'email_verified' => true,
            'business_id'    => (string) ($business?->id ?? ''),
            'business_name'  => $business?->name ?? 'Bisnis COOCA ID',
            'mcp_token_id'   => (string) $token->id,
            'provider'       => 'openai',
        ], Response::HTTP_OK, self::corsHeaders());
    }
}
