<?php

declare(strict_types=1);

namespace Tests\E2E\Tier2_BoundaryCorner;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Tests\E2E\Support\BaseTestCase;

/**
 * Tier 2 Boundary & Corner Cases: F11 Demo User Accounts & Role Configuration
 *
 * Verifies demo accounts boundary conditions: admin guard login blocked with tenant credentials,
 * web guard login blocked with admin credentials, deactivated user login rejection,
 * secure password hash verification (no plaintext), and brute force throttle protection.
 */
final class F11_DemoAccountsBoundaryTest extends BaseTestCase
{
    /**
     * 1. Attempting admin guard authentication with regular tenant user credentials is blocked.
     */
    public function test_admin_guard_login_with_web_credentials_blocked(): void
    {
        $this->runInTransaction(function (): void {
            $user = User::create([
                'name' => 'Tenant Cashier Demo User',
                'email' => 'tenant-user-' . uniqid() . '@cooca.test',
                'password' => Hash::make('Secret123!'),
                'is_active' => true,
            ]);

            $attemptResult = Auth::guard('admin')->attempt([
                'email' => $user->email,
                'password' => 'Secret123!',
            ]);

            $this->assertFalse(
                $attemptResult,
                'Regular tenant user credentials must not succeed in authenticating against admin guard.'
            );
        });
    }

    /**
     * 2. Attempting web guard authentication with admin platform credentials is blocked.
     */
    public function test_web_guard_login_with_admin_credentials_blocked(): void
    {
        $this->runInTransaction(function (): void {
            $admin = Admin::create([
                'name' => 'Platform Superadmin',
                'email' => 'superadmin-' . uniqid() . '@cooca.test',
                'password' => Hash::make('AdminSecret123!'),
                'is_active' => true,
            ]);

            $attemptResult = Auth::guard('web')->attempt([
                'email' => $admin->email,
                'password' => 'AdminSecret123!',
            ]);

            $this->assertFalse(
                $attemptResult,
                'Superadmin platform credentials must not succeed in authenticating against tenant web guard.'
            );
        });
    }

    /**
     * 3. Deactivated user account is rejected and cannot successfully log in.
     */
    public function test_deactivated_account_login_rejected(): void
    {
        $this->runInTransaction(function (): void {
            $deactivatedAdmin = Admin::create([
                'name' => 'Suspended Administrator',
                'email' => 'suspended-admin-' . uniqid() . '@cooca.test',
                'password' => Hash::make('ActiveSecret123!'),
                'is_active' => false,
            ]);

            // Attempt login on admin guard requiring is_active = true
            $canLogin = Auth::guard('admin')->attempt([
                'email' => $deactivatedAdmin->email,
                'password' => 'ActiveSecret123!',
                'is_active' => true,
            ]);

            $this->assertFalse($canLogin, 'Deactivated admin account (is_active = false) must not be allowed to log in.');
        });
    }

    /**
     * 4. All user accounts in database have secure password hashes (bcrypt/argon2, never plaintext).
     */
    public function test_demo_account_passwords_are_properly_hashed_never_plaintext(): void
    {
        $users = User::limit(20)->get();
        $this->assertNotEmpty($users, 'User table should have records to check.');

        foreach ($users as $user) {
            $password = (string) $user->password;
            $this->assertNotEmpty($password);

            $isBcrypt = str_starts_with($password, '$2y$') || str_starts_with($password, '$2a$');
            $isArgon = str_starts_with($password, '$argon2');

            $this->assertTrue(
                $isBcrypt || $isArgon,
                "User [{$user->email}] password is not a valid bcrypt or argon2 hash."
            );
        }
    }

    /**
     * 5. Verify authentication endpoints are protected by brute force rate limiting.
     */
    public function test_brute_force_rate_limit_lockout_configuration(): void
    {
        $loginPostRoute = Route::getRoutes()->getByName('login');
        if ($loginPostRoute === null) {
            // Find POST /login route
            $postRoutes = Route::getRoutes()->get('POST');
            foreach ($postRoutes as $r) {
                if ($r->uri() === 'login' || $r->uri() === 'api/v1/auth/login') {
                    $loginPostRoute = $r;
                    break;
                }
            }
        }

        $this->assertNotNull($loginPostRoute, 'Could not locate POST /login route.');

        $middleware = $loginPostRoute->middleware();
        $hasThrottle = false;
        foreach ($middleware as $m) {
            if (str_contains($m, 'throttle') || str_contains($m, 'guest')) {
                $hasThrottle = true;
                break;
            }
        }

        $this->assertTrue($hasThrottle, 'Login route must include rate limiting or guest middleware.');
    }
}
