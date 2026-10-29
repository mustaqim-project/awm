<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AttendanceCorrection;
use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\CommerceOrder;
use App\Models\GlobalCustomer;
use App\Models\MarketplaceAccount;
use App\Models\User;
use Database\Seeders\DefaultUnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

final class SecurityHardeningAuditTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DefaultUnitSeeder::class);
    }

    public function test_marketplace_account_hides_oauth_tokens_on_serialization(): void
    {
        $business = Business::create([
            'name' => 'Toko Shopee Test',
            'slug' => 'toko-shopee-test',
            'currency' => 'IDR',
            'currency_code' => 'IDR',
            'is_active' => true,
        ]);

        $account = MarketplaceAccount::create([
            'business_id' => $business->id,
            'channel' => MarketplaceAccount::CHANNEL_SHOPEE,
            'shop_id' => 'shop_12345',
            'shop_name' => 'Toko Shopee Berkah',
            'status' => MarketplaceAccount::STATUS_CONNECTED,
            'access_token' => 'secret_oauth_access_token_xyz',
            'refresh_token' => 'secret_oauth_refresh_token_abc',
            'is_active' => true,
        ]);

        $serialized = $account->toArray();
        $json = $account->toJson();

        $this->assertArrayNotHasKey('access_token', $serialized, 'access_token must be hidden on model serialization');
        $this->assertArrayNotHasKey('refresh_token', $serialized, 'refresh_token must be hidden on model serialization');
        $this->assertStringNotContainsString('secret_oauth_access_token_xyz', $json);
        $this->assertStringNotContainsString('secret_oauth_refresh_token_abc', $json);
    }

    public function test_web_security_headers_contains_cdn_whitelisted_csp(): void
    {
        $response = $this->get('/');

        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy');

        $csp = (string) $response->headers->get('Content-Security-Policy');
        $this->assertNotEmpty($csp, 'Content-Security-Policy header must be present on web responses');
        $this->assertStringContainsString('https://cdn.tailwindcss.com', $csp);
        $this->assertStringContainsString('https://unpkg.com', $csp);
        $this->assertStringContainsString('https://cdn.jsdelivr.net', $csp);
        $this->assertStringContainsString('https://fonts.googleapis.com', $csp);
        $this->assertStringContainsString('https://fonts.gstatic.com', $csp);
    }

    public function test_social_media_test_suite_status_is_gated(): void
    {
        // Without authentication or valid token, must be unauthorized (401)
        $response = $this->get('/api/v1/social-media/test-suite/status');
        $response->assertStatus(401);

        $fakeTokenResponse = $this->get('/api/v1/social-media/test-suite/status?token=invalid_guess_123');
        $fakeTokenResponse->assertStatus(401);
    }

    public function test_customer_checkout_api_uploads_proof_to_private_storage_and_rejects_svg(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $business = Business::create([
            'name' => 'Toko Storefront Test',
            'slug' => 'toko-storefront-test',
            'currency' => 'IDR',
            'currency_code' => 'IDR',
            'is_active' => true,
        ]);

        $customer = GlobalCustomer::create([
            'name' => 'Pembeli Terverifikasi',
            'email' => 'pembeli@cooca.test',
            'phone' => '081299887766',
            'password' => Hash::make('password123'),
            'phone_verified_at' => now(),
            'email_verified_at' => now(),
        ]);

        $order = CommerceOrder::create([
            'business_id' => $business->id,
            'global_customer_id' => $customer->id,
            'order_number' => 'ORD-TEST-' . rand(1000, 9999),
            'customer_name' => 'Pembeli Terverifikasi',
            'customer_phone' => '081299887766',
            'total_amount' => 150000,
            'status' => CommerceOrder::STATUS_PENDING_PAYMENT,
            'payment_status' => CommerceOrder::PAYMENT_UNPAID,
            'tracking_token' => Str::random(40),
        ]);

        // 1. SVG file upload must be rejected
        $svgFile = UploadedFile::fake()->create('exploit.svg', 10, 'image/svg+xml');
        $rejectResponse = $this->actingAs($customer, 'sanctum')
            ->postJson("/api/v1/customer/orders/{$order->id}/upload-proof", [
                'proof_image' => $svgFile,
            ]);
        $rejectResponse->assertStatus(422);

        // 2. Valid JPG file must be stored in private storage ('local'), NOT 'public'
        $jpgFile = UploadedFile::fake()->image('bukti_transfer.jpg');
        $successResponse = $this->actingAs($customer, 'sanctum')
            ->postJson("/api/v1/customer/orders/{$order->id}/upload-proof", [
                'proof_image' => $jpgFile,
                'sender_bank' => 'BCA',
                'sender_account_name' => 'Budi Utomo',
            ]);

        $successResponse->assertStatus(200);

        // Verify storage: local disk must have the file, public disk must NOT have it
        $this->assertDatabaseHas('commerce_payment_proofs', [
            'commerce_order_id' => $order->id,
            'business_id' => $business->id,
            'sender_bank' => 'BCA',
        ]);

        $proof = \App\Models\CommercePaymentProof::where('commerce_order_id', $order->id)->firstOrFail();
        $this->assertNotEmpty($proof->file_path);
        Storage::disk('local')->assertExists($proof->file_path);
        Storage::disk('public')->assertMissing($proof->file_path);
    }

    public function test_attendance_correction_stream_attachment_authorization(): void
    {
        Storage::fake('local');

        $businessA = Business::create([
            'name' => 'Bisnis Alpha',
            'slug' => 'bisnis-alpha',
            'currency' => 'IDR',
            'currency_code' => 'IDR',
            'is_active' => true,
        ]);
        $businessB = Business::create([
            'name' => 'Bisnis Beta',
            'slug' => 'bisnis-beta',
            'currency' => 'IDR',
            'currency_code' => 'IDR',
            'is_active' => true,
        ]);

        $userA = User::create([
            'name' => 'Karyawan A',
            'email' => 'karyawan_a@cooca.test',
            'phone' => '08123456781',
            'password' => Hash::make('secret123'),
            'current_business_id' => $businessA->id,
            'active_business_id' => $businessA->id,
        ]);
        $userA->forceFill(['email_verified_at' => now()])->save();
        BusinessMembership::create([
            'business_id' => $businessA->id,
            'user_id' => $userA->id,
            'role' => 'staff',
            'is_active' => true,
        ]);

        $userB = User::create([
            'name' => 'Karyawan B',
            'email' => 'karyawan_b@cooca.test',
            'phone' => '08123456782',
            'password' => Hash::make('secret123'),
            'current_business_id' => $businessB->id,
            'active_business_id' => $businessB->id,
        ]);
        $userB->forceFill(['email_verified_at' => now()])->save();
        BusinessMembership::create([
            'business_id' => $businessB->id,
            'user_id' => $userB->id,
            'role' => 'staff',
            'is_active' => true,
        ]);

        $filePath = "hrm/{$businessA->id}/corrections/surat_dokter.pdf";
        Storage::disk('local')->put($filePath, 'fake-medical-pdf-content');

        $correction = AttendanceCorrection::create([
            'id' => (string) Str::uuid(),
            'business_id' => $businessA->id,
            'user_id' => $userA->id,
            'correction_number' => 'COR-202610-TEST1',
            'target_date' => now()->toDateString(),
            'correction_type' => 'present',
            'reason' => 'Sakit demam surat dokter terlampir',
            'attachment_path' => $filePath,
            'status' => AttendanceCorrection::STATUS_PENDING,
        ]);

        // User B with active Business B cannot access correction attachment from Business A (404/403 due to BusinessScope)
        \App\Support\Context::flush();
        $userBResponse = $this->actingAs($userB, 'web')
            ->withSession(['active_business_id' => $businessB->id])
            ->get("/hrm/attendance/corrections/{$correction->id}/attachment");
        $this->assertTrue(in_array($userBResponse->status(), [403, 404], true), 'Cross-tenant access must be denied (403 or 404)');

        // User A (the ticket owner) can access the attachment securely
        \App\Support\Context::flush();
        $response = $this->actingAs($userA, 'web')
            ->withSession(['active_business_id' => $businessA->id])
            ->get("/hrm/attendance/corrections/{$correction->id}/attachment");

        $response->assertStatus(200);
        $this->assertEquals('fake-medical-pdf-content', $response->streamedContent());
    }

    public function test_bug_report_attachment_private_storage_and_access_control(): void
    {
        $this->seed(\Database\Seeders\RbacSeeder::class);
        $ownerRole = \App\Models\Role::where('slug', 'owner')->first();

        Storage::fake('local');
        Storage::fake('public');

        $businessA = Business::create([
            'name' => 'Bisnis Alpha Feedback',
            'slug' => 'bisnis-alpha-fb',
            'currency' => 'IDR',
            'currency_code' => 'IDR',
            'is_active' => true,
        ]);
        $businessB = Business::create([
            'name' => 'Bisnis Beta Feedback',
            'slug' => 'bisnis-beta-fb',
            'currency' => 'IDR',
            'currency_code' => 'IDR',
            'is_active' => true,
        ]);

        $ownerA = User::create([
            'name' => 'Owner Alpha',
            'email' => 'owner.alpha@audit-test.local',
            'email_verified_at' => now(),
            'phone' => '081234567990',
            'phone_verified_at' => now(),
            'password' => 'password123',
        ]);
        $businessA->users()->attach($ownerA->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'role_id' => $ownerRole?->id,
        ]);
        $ownerA->update(['active_business_id' => $businessA->id]);

        $ownerB = User::create([
            'name' => 'Owner Beta',
            'email' => 'owner.beta@audit-test.local',
            'email_verified_at' => now(),
            'phone' => '081234567991',
            'phone_verified_at' => now(),
            'password' => 'password123',
        ]);
        $businessB->users()->attach($ownerB->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'role_id' => $ownerRole?->id,
        ]);
        $ownerB->update(['active_business_id' => $businessB->id]);

        \App\Support\Context::setBusiness($businessA);

        $fakeLog = UploadedFile::fake()->create('error.log', 20, 'text/plain');

        // Submit bug report with attachment by Owner A
        $response = $this->actingAs($ownerA, 'web')
            ->withSession(['active_business_id' => $businessA->id])
            ->post(route('feedback.bugs.store'), [
                'title' => 'Error 500 saat ekspor transaksi',
                'category' => 'bug',
                'severity' => 'high',
                'description' => 'Ada stacktrace terlampir di file log.',
                'attachment' => $fakeLog,
            ]);

        $response->assertSessionHas('success');
        $bug = \App\Models\BugReport::where('business_id', $businessA->id)->firstOrFail();
        $this->assertNotNull($bug->attachment_path);

        // Assert file stored in 'local' private disk, NOT 'public'
        Storage::disk('local')->assertExists($bug->attachment_path);
        Storage::disk('public')->assertMissing($bug->attachment_path);

        // Owner A can stream the attachment
        \App\Support\Context::flush();
        \App\Support\Context::setBusiness($businessA);
        $streamA = $this->actingAs($ownerA, 'web')
            ->withSession(['active_business_id' => $businessA->id])
            ->get(route('feedback.bugs.attachment', $bug));
        $streamA->assertStatus(200);

        // Owner B (different tenant) is rejected with 403 or 404
        \App\Support\Context::flush();
        \App\Support\Context::setBusiness($businessB);
        $streamB = $this->actingAs($ownerB, 'web')
            ->withSession(['active_business_id' => $businessB->id])
            ->get(route('feedback.bugs.attachment', $bug));
        $this->assertTrue(in_array($streamB->status(), [403, 404], true));

        // Guest is rejected with 403 Forbidden or redirected
        \App\Support\Context::flush();
        $streamGuest = $this->get(route('feedback.bugs.attachment', $bug));
        $this->assertTrue(in_array($streamGuest->status(), [401, 403, 302], true), 'Guest must be denied');
    }
}
