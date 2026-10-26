<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Mail\BusinessReactivatedMail;
use App\Mail\BusinessSuspendedMail;
use App\Models\Admin;
use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\AdminRbacSeeder;
use Database\Seeders\DefaultCostCategorySeeder;
use Database\Seeders\DefaultUnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class BusinessSuspensionTest extends TestCase
{
    use RefreshDatabase;

    private Admin $superAdmin;
    private User $ownerUser;
    private Business $business;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->seed(DefaultUnitSeeder::class);
        $this->seed(DefaultCostCategorySeeder::class);
        $this->seed(AdminRbacSeeder::class);

        $this->superAdmin = Admin::factory()->create([
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        $this->ownerUser = User::create([
            'name' => 'Owner Hendra',
            'email' => 'hendra@cooca.id',
            'password' => bcrypt('password123'),
        ]);

        $this->business = Business::create([
            'name' => 'Resto Sedap Rasa',
            'is_active' => true,
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
        ]);

        $this->business->users()->attach($this->ownerUser->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->ownerUser->update(['active_business_id' => $this->business->id]);
    }

    /**
     * Test 1: Admin cannot suspend business without a mandatory reason.
     */
    public function test_admin_cannot_suspend_business_without_mandatory_reason(): void
    {
        Mail::fake();

        // 1. Submit without reason
        $response = $this->actingAs($this->superAdmin, 'admin')
            ->post(route('admin.businesses.toggle-status', $this->business->id), []);

        $response->assertSessionHasErrors(['reason']);
        $this->assertTrue($this->business->fresh()->is_active);
        Mail::assertNothingSent();

        // 2. Submit with reason too short (< 5 chars)
        $responseShort = $this->actingAs($this->superAdmin, 'admin')
            ->post(route('admin.businesses.toggle-status', $this->business->id), [
                'reason' => 'bad',
            ]);

        $responseShort->assertSessionHasErrors(['reason']);
        $this->assertTrue($this->business->fresh()->is_active);
        Mail::assertNothingSent();
    }

    /**
     * Test 2: Admin can suspend business with valid reason and triggers notification email to owner.
     */
    public function test_admin_can_suspend_business_with_reason_and_sends_email(): void
    {
        Mail::fake();

        $reason = 'Terdeteksi pelanggaran kebijakan penggunaan platform dan indikasi transaksi mencurigakan.';

        $response = $this->actingAs($this->superAdmin, 'admin')
            ->post(route('admin.businesses.toggle-status', $this->business->id), [
                'reason' => $reason,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $refreshed = $this->business->fresh();
        $this->assertFalse($refreshed->is_active);
        $this->assertEquals($reason, $refreshed->suspended_reason);
        $this->assertNotNull($refreshed->suspended_at);

        // Verify email sent to the owner
        Mail::assertSent(BusinessSuspendedMail::class, function (BusinessSuspendedMail $mail) use ($reason) {
            return $mail->hasTo('hendra@cooca.id')
                && $mail->business->id === $this->business->id
                && $mail->reason === $reason;
        });
    }

    /**
     * Test 3: Suspended business cannot execute mutating transactions (POST/PUT/DELETE).
     */
    public function test_suspended_business_blocks_mutating_transactions(): void
    {
        $this->business->update([
            'is_active' => false,
            'suspended_reason' => 'Akun dibekukan sementara karena investigasi keamanan.',
            'suspended_at' => now(),
        ]);

        $membership = BusinessMembership::where('business_id', $this->business->id)
            ->where('user_id', $this->ownerUser->id)
            ->first();
        Context::setBusiness($this->business, $membership);

        // 1. JSON Request (API / POS checkout) is rejected with 403 Forbidden and BUSINESS_SUSPENDED
        $jsonResponse = $this->actingAs($this->ownerUser, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('pos.checkout'), [
                'items' => [],
            ]);

        $jsonResponse->assertStatus(403);
        $jsonResponse->assertJson([
            'success' => false,
            'error_code' => 'BUSINESS_SUSPENDED',
            'suspended_reason' => 'Akun dibekukan sementara karena investigasi keamanan.',
        ]);

        // 2. Web POST request is blocked and redirected back with error
        $webResponse = $this->actingAs($this->ownerUser, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->from(route('dashboard'))
            ->post(route('invoices.store'), [
                'invoice_number' => 'INV-TEST-001',
            ]);

        $webResponse->assertRedirect(route('dashboard'));
        $webResponse->assertSessionHas('error');
    }

    /**
     * Test 4: Suspended business redirects POS terminal screen on GET to prevent ringing sales.
     */
    public function test_suspended_business_cashier_cannot_open_pos_terminal(): void
    {
        $this->business->update([
            'is_active' => false,
            'suspended_reason' => 'Akun dibekukan sementara.',
            'suspended_at' => now(),
        ]);

        $membership = BusinessMembership::where('business_id', $this->business->id)
            ->where('user_id', $this->ownerUser->id)
            ->first();
        Context::setBusiness($this->business, $membership);

        $response = $this->actingAs($this->ownerUser, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('pos.terminal'));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('error');
    }

    /**
     * Test 5: Admin can reactivate business and triggers notification email to owner.
     */
    public function test_admin_can_reactivate_business_and_sends_email(): void
    {
        Mail::fake();

        $this->business->update([
            'is_active' => false,
            'suspended_reason' => 'Investigasi selesai.',
            'suspended_at' => now()->subDays(2),
        ]);

        $response = $this->actingAs($this->superAdmin, 'admin')
            ->post(route('admin.businesses.toggle-status', $this->business->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $refreshed = $this->business->fresh();
        $this->assertTrue($refreshed->is_active);
        $this->assertNull($refreshed->suspended_reason);
        $this->assertNull($refreshed->suspended_at);

        // Verify email sent to the owner
        Mail::assertSent(BusinessReactivatedMail::class, function (BusinessReactivatedMail $mail) {
            return $mail->hasTo('hendra@cooca.id')
                && $mail->business->id === $this->business->id;
        });
    }
}
