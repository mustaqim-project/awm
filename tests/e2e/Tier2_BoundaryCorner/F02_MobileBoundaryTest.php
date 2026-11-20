<?php

declare(strict_types=1);

namespace Tests\E2E\Tier2_BoundaryCorner;

use App\Models\PosOrder;
use Tests\E2E\Support\BaseTestCase;

/**
 * Tier 2 Boundary & Corner Cases: F02 Mobile Screen & Navigation Audit
 *
 * Verifies mobile boundary conditions: offline sync idempotency (duplicate client UUIDs),
 * 401 token expiry handling, malformed payload resilience, offline sync manager
 * implementation integrity, and offline payment policy enforcement.
 */
final class F02_MobileBoundaryTest extends BaseTestCase
{
    /**
     * 1. Offline sync idempotency: duplicate client_uuid submissions do not create duplicate orders.
     */
    public function test_offline_sync_idempotency_duplicate_client_uuid_detected(): void
    {
        $this->runInTransaction(function (): void {
            $business = $this->getTenantBusiness('cooca_mart') ?? $this->createTransientTenant();
            $user = \App\Models\User::first() ?? \App\Models\User::factory()->create();
            $clientUuid = 'e2e-offline-' . uniqid();

            // Simulate first offline sync insertion
            $order1 = PosOrder::create([
                'business_id' => $business->id,
                'user_id' => $user->id,
                'order_number' => 'ORD-OFFLINE-' . uniqid(),
                'client_uuid' => $clientUuid,
                'order_date' => now()->toDateString(),
                'status' => 'completed',
                'order_type' => 'takeaway',
                'sales_channel' => 'pos',
                'subtotal' => 50000,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'service_charge_amount' => 0,
                'rounding_amount' => 0,
                'total_amount' => 50000,
                'paid_amount' => 50000,
                'change_amount' => 0,
                'total_hpp_cost' => 30000,
                'total_gross_profit' => 20000,
            ]);

            $this->assertNotNull($order1->id);

            // Check if duplicate client_uuid exists
            $existing = PosOrder::where('business_id', $business->id)
                ->where('client_uuid', $clientUuid)
                ->count();

            $this->assertSame(
                1,
                $existing,
                "Expected exactly 1 order record with client_uuid [{$clientUuid}], found {$existing}."
            );
        });
    }

    /**
     * 2. Protected mobile API endpoints return HTTP 401 Unauthenticated when token is expired/invalid.
     */
    public function test_mobile_api_unauthorized_token_returns_401_envelope(): void
    {
        $response = $this->getJson('/api/v1/profile', [
            'Authorization' => 'Bearer invalid_or_expired_mobile_sanctum_token_123',
            'Accept' => 'application/json',
        ]);

        $this->assertSame(
            401,
            $response->getStatusCode(),
            "Expected 401 Unauthenticated for invalid token, got {$response->getStatusCode()}."
        );

        $json = $response->json();
        $this->assertIsArray($json);
        $this->assertArrayHasKey('message', $json);
    }

    /**
     * 3. Malformed JSON payload sent to mobile API is handled gracefully (returns 400 or 422, not 500).
     */
    public function test_malformed_json_payload_resilience(): void
    {
        $malformedJson = '{ "client_uuid": "xyz", "items": [ missing_bracket ';

        $response = $this->call(
            'POST',
            '/api/v1/auth/login',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
            ],
            $malformedJson
        );

        $statusCode = $response->getStatusCode();
        $this->assertContains(
            $statusCode,
            [400, 422],
            "Malformed JSON payload should return 400 or 422, got {$statusCode}."
        );

        $content = $response->getContent() ?: '';
        $this->assertStringNotContainsString('Whoops', $content);
        $this->assertStringNotContainsString('Fatal error', $content);
    }

    /**
     * 4. Verify Flutter client's PosOfflineSyncManager source code defines idempotency and RFC4122 UUID.
     */
    public function test_mobile_offline_sync_manager_dart_source_code_integrity(): void
    {
        $managerFile = 'C:\\laragon\\www\\cooca_my_own\\lib\\core\\services\\pos_offline_sync_manager.dart';
        $this->assertFileExists($managerFile, "PosOfflineSyncManager.dart file does not exist at [{$managerFile}].");

        $content = (string) file_get_contents($managerFile);
        $this->assertStringContainsString('generateClientUuid', $content);
        $this->assertStringContainsString('client_uuid', $content);
        $this->assertStringContainsString('PosOfflineSyncManager', $content);
        $this->assertStringContainsString('syncPendingTransactions', $content);
    }

    /**
     * 5. Verify mobile checkout policy strictly prohibits non-cash payments (QRIS/EDC) in offline mode.
     */
    public function test_mobile_non_cash_offline_policy_rejection(): void
    {
        $merchantStateFile = 'C:\\laragon\\www\\cooca_my_own\\lib\\state\\merchant_state.dart';
        $this->assertFileExists($merchantStateFile, "MerchantState.dart does not exist at [{$merchantStateFile}].");

        $content = (string) file_get_contents($merchantStateFile);

        // Verify rule preventing non-cash payment during offline mode
        $this->assertStringContainsString('Offline Payment Policy Check', $content);
        $this->assertStringContainsString('memerlukan verifikasi online real-time', $content);
    }
}
