<?php

declare(strict_types=1);

namespace Tests\E2E\Support\Helpers;

use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\User;
use App\Support\Context;
use Illuminate\Testing\TestResponse;
use Laravel\Sanctum\Sanctum;
use Tests\E2E\Support\BaseTestCase;

final class ApiClientHelper
{
    /**
     * Create a plain text Sanctum access token for the given user.
     *
     * @param array<int, string> $abilities
     */
    public static function createSanctumToken(User $user, string $name = 'e2e-token', array $abilities = ['*']): string
    {
        return $user->createToken($name, $abilities)->plainTextToken;
    }

    /**
     * Build the standard header array for COOCA REST API requests.
     *
     * @param array<string, string> $additionalHeaders
     * @return array<string, string>
     */
    public static function getHeaders(?string $businessId = null, ?string $token = null, array $additionalHeaders = []): array
    {
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];

        if ($businessId !== null) {
            $headers['X-Business-Id'] = $businessId;
        }

        if ($token !== null) {
            $headers['Authorization'] = "Bearer {$token}";
        }

        return array_merge($headers, $additionalHeaders);
    }

    /**
     * Authenticate a user via Sanctum and optionally bind their business context.
     *
     * @param array<int, string> $abilities
     */
    public static function actingAsTenantUser(BaseTestCase $testCase, User $user, ?string $businessId = null, array $abilities = ['*']): void
    {
        Sanctum::actingAs($user, $abilities);

        if ($businessId !== null) {
            $business = Business::find($businessId);
            if ($business !== null) {
                $membership = BusinessMembership::where('business_id', $businessId)
                    ->where('user_id', $user->id)
                    ->first();

                Context::setBusiness($business, $membership);
                session(['active_business_id' => $businessId]);
            }
        }
    }

    /**
     * Dispatch an internal JSON API request via Laravel test client with appropriate headers.
     *
     * @param array<string, mixed> $data
     * @param array<string, string> $headers
     */
    public static function dispatchApiRequest(
        BaseTestCase $testCase,
        string $method,
        string $uri,
        array $data = [],
        array $headers = [],
        ?string $businessId = null,
        ?string $token = null
    ): TestResponse {
        $finalHeaders = self::getHeaders($businessId, $token, $headers);

        return $testCase->json($method, $uri, $data, $finalHeaders);
    }

    /**
     * Dispatch a GET JSON request.
     *
     * @param array<string, string> $headers
     */
    public static function get(
        BaseTestCase $testCase,
        string $uri,
        array $headers = [],
        ?string $businessId = null,
        ?string $token = null
    ): TestResponse {
        return self::dispatchApiRequest($testCase, 'GET', $uri, [], $headers, $businessId, $token);
    }

    /**
     * Dispatch a POST JSON request.
     *
     * @param array<string, mixed> $data
     * @param array<string, string> $headers
     */
    public static function post(
        BaseTestCase $testCase,
        string $uri,
        array $data = [],
        array $headers = [],
        ?string $businessId = null,
        ?string $token = null
    ): TestResponse {
        return self::dispatchApiRequest($testCase, 'POST', $uri, $data, $headers, $businessId, $token);
    }
}
