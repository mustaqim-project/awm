<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use App\Domain\Security\BruteForceProtectionService;
use App\Models\Admin;
use App\Models\Business;
use App\Models\GlobalCustomer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class BruteForceProtectionTest extends TestCase
{
    use RefreshDatabase;

    private BruteForceProtectionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->service = new BruteForceProtectionService();
    }

    public function test_consecutive_failed_logins_trigger_account_lockout_and_ip_block(): void
    {
        $testIp = '198.51.100.42';
        $email = 'target@example.com';

        // 4 failed attempts should not trigger block yet
        for ($i = 1; $i <= 4; $i++) {
            $res = $this->service->recordFailedAttempt($testIp, $email, 'test');
            $this->assertFalse($res['is_blocked']);
            $this->assertFalse($res['is_account_locked']);
            $this->assertSame($i, $res['attempts_on_account']);
        }

        // 5th failed attempt on the same account triggers account lockout and IP block!
        $res5 = $this->service->recordFailedAttempt($testIp, $email, 'test');
        $this->assertTrue($res5['is_account_locked']);
        $this->assertTrue($res5['is_blocked']);
        $this->assertTrue($this->service->isIpBlocked($testIp));
        $this->assertTrue($this->service->isAccountLocked($testIp, $email));
        $this->assertGreaterThan(0, $this->service->getRemainingBlockMinutes($testIp));
    }

    public function test_blocked_ip_is_rejected_on_web_login(): void
    {
        $testIp = '203.0.113.88';
        $user = User::factory()->create([
            'email' => 'victim@cooca.id',
            'password' => Hash::make('CorrectPassword123!'),
        ]);

        // Manually block the IP
        $this->service->blockIp($testIp, 3600, 'Test ban');

        // Request from blocked IP should be rejected with lockout message
        $response = $this->withServerVariables(['REMOTE_ADDR' => $testIp])
            ->from(route('login'))
            ->post(route('login'), [
                'email' => 'victim@cooca.id',
                'password' => 'WrongPassword',
            ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        $this->assertStringContainsString('diblokir sementara', session('errors')->first('email'));
    }

    public function test_blocked_ip_receives_429_on_api_login(): void
    {
        $testIp = '198.51.100.99';
        $this->service->blockIp($testIp, 3600, 'API ban');

        $response = $this->withServerVariables(['REMOTE_ADDR' => $testIp])
            ->postJson('/api/v1/auth/login', [
                'email' => 'api.user@cooca.id',
                'password' => 'Secret123',
            ]);

        $response->assertStatus(429);
        $response->assertJson([
            'success' => false,
            'error' => 'IP_TEMPORARILY_BLOCKED',
        ]);
    }

    public function test_successful_login_resets_failed_attempt_counters(): void
    {
        $testIp = '192.0.2.15';
        $email = 'store.owner@cooca.id';

        // 3 failed attempts
        for ($i = 0; $i < 3; $i++) {
            $this->service->recordFailedAttempt($testIp, $email);
        }

        $this->assertSame(3, (int) Cache::get("security:failed_login:ip:{$testIp}:account:{$email}"));

        // Reset attempts
        $this->service->resetAttempts($testIp, $email);

        $this->assertFalse($this->service->isAccountLocked($testIp, $email));
        $this->assertNull(Cache::get("security:failed_login:ip:{$testIp}:account:{$email}"));
    }

    public function test_unblock_ip_restores_access(): void
    {
        $testIp = '198.51.100.77';
        $this->service->blockIp($testIp, 3600, 'Test lockout');

        $this->assertTrue($this->service->isIpBlocked($testIp));

        // Unblock
        $this->service->unblockIp($testIp);

        $this->assertFalse($this->service->isIpBlocked($testIp));
    }

    public function test_repeated_admin_login_failures_block_ip(): void
    {
        $testIp = '198.51.100.11';
        $admin = Admin::create([
            'name' => 'Admin Test',
            'email' => 'admin.target@cooca.id',
            'password' => Hash::make('AdminSecret123!'),
            'role' => 'superadmin',
            'is_active' => true,
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->service->recordFailedAttempt($testIp, 'admin.target@cooca.id', 'admin');
        }

        $this->assertTrue($this->service->isIpBlocked($testIp));

        // When IP is blocked, visiting admin login portal is blocked with 429
        $response = $this->withServerVariables(['REMOTE_ADDR' => $testIp])
            ->get(route('admin.login'));

        $response->assertStatus(429);
    }

    public function test_repeated_customer_login_failures_block_ip(): void
    {
        $testIp = '198.51.100.22';

        for ($i = 0; $i < 5; $i++) {
            $this->service->recordFailedAttempt($testIp, '081299998888', 'customer');
        }

        $this->assertTrue($this->service->isIpBlocked($testIp));

        // When IP is blocked, visiting customer portal returns 429
        $response = $this->withServerVariables(['REMOTE_ADDR' => $testIp])
            ->get(route('customer.login'));

        $response->assertStatus(429);
    }
}

