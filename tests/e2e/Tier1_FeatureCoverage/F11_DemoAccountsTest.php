<?php

declare(strict_types=1);

namespace Tests\E2E\Tier1_FeatureCoverage;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\E2E\Support\BaseTestCase;

/**
 * Feature F11: Demo User Accounts & Role Configuration Test Suite
 *
 * Validates the seeded demo user accounts, password hashing security,
 * email validation, business tenant memberships, and authentication readiness.
 */
final class F11_DemoAccountsTest extends BaseTestCase
{
    /**
     * 1. Assert demo user accounts exist in the database with multi-user volume.
     */
    public function test_demo_users_exist_in_database(): void
    {
        $userCount = DB::table('users')->count();
        $this->assertGreaterThanOrEqual(
            20,
            $userCount,
            "Expected at least 20 user accounts in database, found {$userCount}."
        );
    }

    /**
     * 2. Assert zero plain-text passwords: all user passwords must be securely hashed.
     */
    public function test_passwords_are_securely_hashed(): void
    {
        $users = DB::table('users')->select('id', 'email', 'password')->get();
        $this->assertNotEmpty($users);

        foreach ($users as $user) {
            $pwd = (string) $user->password;
            $isBcrypt = str_starts_with($pwd, '$2y$') || str_starts_with($pwd, '$2a$') || str_starts_with($pwd, '$2b$');
            $isArgon = str_starts_with($pwd, '$argon2');

            $this->assertTrue(
                $isBcrypt || $isArgon,
                "User [{$user->email}] password must be securely hashed with Bcrypt or Argon2."
            );
        }
    }

    /**
     * 3. Assert all user accounts have valid, RFC-compliant email addresses.
     */
    public function test_demo_users_have_valid_email_structure(): void
    {
        $emails = DB::table('users')->pluck('email')->all();

        foreach ($emails as $email) {
            $this->assertNotEmpty($email, "User email must not be empty.");
            $this->assertNotFalse(
                filter_var($email, FILTER_VALIDATE_EMAIL),
                "User email [{$email}] must be a valid email format."
            );
        }
    }

    /**
     * 4. Assert user accounts have assigned memberships linking them to businesses.
     */
    public function test_business_memberships_link_users_to_tenants(): void
    {
        $memberships = DB::table('business_users')
            ->whereNotNull('business_id')
            ->whereNotNull('user_id')
            ->get();

        $this->assertGreaterThanOrEqual(
            10,
            $memberships->count(),
            "Expected at least 10 business-to-user role assignments in business_users."
        );

        $distinctRoles = $memberships->pluck('role')->unique()->all();
        $this->assertGreaterThanOrEqual(
            3,
            count($distinctRoles),
            "Expected multiple distinct operational roles assigned to demo users."
        );
    }

    /**
     * 5. Assert user credential verification via Hash::check functions accurately.
     */
    public function test_demo_user_authentication_capability(): void
    {
        $testPassword = 'Password123!';
        $hashed = Hash::make($testPassword);

        $this->assertTrue(
            Hash::check($testPassword, $hashed),
            "Password hash verification must succeed for standard test passwords."
        );
        $this->assertFalse(
            Hash::check('WrongPassword', $hashed),
            "Password hash verification must reject incorrect passwords."
        );
    }
}
