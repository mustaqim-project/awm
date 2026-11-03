<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class UserAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_receives_uuid_and_token(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'password' => 'secret12345',
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'message',
                'user' => ['id', 'name', 'email'],
                'token',
            ]);

        $userId = $response->json('user.id');
        $this->assertTrue(Str::isUuid($userId));

        $this->assertDatabaseHas('users', [
            'id' => $userId,
            'email' => 'budi@example.com',
        ]);
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::create([
            'name' => 'Siti Aminah',
            'email' => 'siti@example.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'siti@example.com',
            'password' => 'password123',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'user' => ['id', 'name', 'email'],
                'token',
            ]);

        $this->assertSame($user->id, $response->json('user.id'));
    }

    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        User::create([
            'name' => 'Siti Aminah',
            'email' => 'siti@example.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'siti@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertUnprocessable()
            ->assertJson([
                'message' => 'Invalid email or password.',
            ]);
    }

    public function test_authenticated_user_can_get_profile_and_logout(): void
    {
        $user = User::create([
            'name' => 'Ahmad Dahlan',
            'email' => 'ahmad@example.com',
            'password' => 'password123',
        ]);

        $token = $user->createToken('test_token')->plainTextToken;

        $profileResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/auth/me');

        $profileResponse->assertOk()
            ->assertJsonPath('user.email', 'ahmad@example.com');

        $logoutResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/auth/logout');

        $logoutResponse->assertOk();
        $this->assertCount(0, $user->fresh()->tokens);
    }

    public function test_google_login_provisions_business_and_returns_full_business_context(): void
    {
        $response = $this->postJson('/api/v1/auth/google', [
            'google_id' => 'google_test_123456789',
            'email' => 'owner.google@example.com',
            'name' => 'Owner Google Test',
            'avatar_url' => 'https://lh3.googleusercontent.com/a/test-avatar',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'message',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'active_business_id',
                    'business' => [
                        'id',
                        'name',
                        'slug',
                        'is_service',
                    ],
                    'businesses',
                ],
                'token',
            ]);

        $token = $response->json('token');
        $businessId = $response->json('user.active_business_id');
        $this->assertNotNull($businessId);
        $this->assertNotEmpty($response->json('user.businesses'));

        // Verify /api/v1/me/businesses returns the business
        $bizResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/me/businesses');

        $bizResponse->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'businesses');

        // Verify /api/v1/context/current returns active business context
        $contextResponse = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
            'X-Business-Id' => $businessId,
        ])->getJson('/api/v1/context/current');

        $contextResponse->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('business.id', $businessId)
            ->assertJsonPath('is_owner', true);
    }

    public function test_store_business_without_slug_auto_generates_slug(): void
    {
        $user = User::create([
            'name' => 'Mitra Baru',
            'email' => 'mitra.baru@example.com',
            'password' => 'secret12345',
        ]);
        $token = $user->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/businesses', [
                'name' => 'Kedai Kopi Nusantara',
                'description' => 'Kedai kopi modern dan roastery lokal',
            ]);

        $response->assertCreated()
            ->assertJsonPath('business.name', 'Kedai Kopi Nusantara');

        $this->assertNotNull($response->json('business.slug'));
        $this->assertStringContainsString('kedai-kopi-nusantara', $response->json('business.slug'));
    }
}
