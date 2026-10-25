<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Billing\EntitlementService;
use App\Models\AuditLog;
use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\BusinessSubscription;
use App\Models\StorageFile;
use App\Models\SubscriptionPayment;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

final class BillingModuleAuditTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private User $staffWithoutPerms;
    private Business $businessA;
    private Business $businessB;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create Business Owner
        $this->owner = User::create([
            'name' => 'Owner Bisnis Billing',
            'email' => 'owner.billing@cooca.id',
            'password' => 'password123',
            'email_verified_at' => now(),
            'phone' => '628111111111',
        ]);

        // 2. Create Staff without Billing permissions
        $this->staffWithoutPerms = User::create([
            'name' => 'Staff Biasa',
            'email' => 'staff.nobilling@cooca.id',
            'password' => 'password123',
            'email_verified_at' => now(),
            'phone' => '628222222222',
        ]);

        // 3. Create Primary Business A
        $this->businessA = Business::create([
            'name' => 'Bisnis Tenant A',
            'slug' => 'bisnis-tenant-a',
            'industry_category' => 'retail',
        ]);

        // 4. Create Secondary Business B (For IDOR Cross-Tenant Testing)
        $this->businessB = Business::create([
            'name' => 'Bisnis Tenant B',
            'slug' => 'bisnis-tenant-b',
            'industry_category' => 'fnb_resto',
        ]);

        // Attach owner to Business A
        $this->businessA->users()->attach($this->owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

        // Attach staff to Business A with 'cashier' role (no billing permissions)
        $this->businessA->users()->attach($this->staffWithoutPerms->id, [
            'id' => (string) Str::uuid(),
            'role' => 'cashier',
            'is_active' => true,
        ]);

        $this->owner->update(['active_business_id' => $this->businessA->id]);
        $this->staffWithoutPerms->update(['active_business_id' => $this->businessA->id]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('billing.limits'));
        $response->assertRedirect(route('login'));
    }

    public function test_user_without_billing_permission_is_blocked(): void
    {
        $response = $this->actingAs($this->staffWithoutPerms)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->get(route('billing.limits'));

        // Standard require.permission redirects web unauthorized users to portal with warning
        $response->assertRedirect(route('portal'));
    }

    public function test_user_with_billing_permission_can_access_limits_and_history(): void
    {
        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->get(route('billing.limits'));

        $response->assertStatus(200);
        $response->assertSee('Bisnis Tenant A');

        $responseHistory = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->get(route('billing.history'));

        $responseHistory->assertStatus(200);
    }

    public function test_cross_tenant_idor_protection_on_invoice_and_payment(): void
    {
        // Payment belonging to Business B
        $paymentB = SubscriptionPayment::create([
            'business_id' => $this->businessB->id,
            'user_id' => $this->owner->id,
            'order_number' => 'ORD-TEST-B-001',
            'payment_method' => SubscriptionPayment::METHOD_QRIS,
            'amount' => 49000,
            'status' => SubscriptionPayment::STATUS_PENDING,
            'cycle' => 'monthly',
            'subscription_tier' => 'standard',
        ]);

        // Owner of Business A attempts to access Business B's invoice
        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->get(route('billing.payment.invoice', $paymentB));

        $response->assertStatus(403);
    }

    public function test_cross_tenant_idor_protection_on_storage_file_deletion(): void
    {
        $fileB = StorageFile::create([
            'business_id' => $this->businessB->id,
            'owner_id' => $this->owner->id,
            'user_id' => $this->owner->id,
            'file_name' => 'secret_document_b.pdf',
            'file_path' => 'businesses/' . $this->businessB->id . '/secret_document_b.pdf',
            'disk' => 'local',
            'file_size' => 2048000,
            'mime_type' => 'application/pdf',
            'category' => StorageFile::CATEGORY_DOCUMENT,
            'module' => 'finance',
            'status' => StorageFile::STATUS_ACTIVE,
        ]);

        // Owner of Business A attempts to delete Business B's file
        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->delete(route('billing.storage.files.destroy', $fileB));

        $response->assertStatus(403);
        $this->assertDatabaseHas('storage_files', ['id' => $fileB->id]);
    }

    public function test_storage_file_deletion_and_audit_logging(): void
    {
        Storage::fake('local');
        $filePath = 'businesses/' . $this->businessA->id . '/product_catalog.jpg';
        Storage::disk('local')->put($filePath, 'fake image content');

        $fileA = StorageFile::create([
            'business_id' => $this->businessA->id,
            'owner_id' => $this->owner->id,
            'user_id' => $this->owner->id,
            'file_name' => 'product_catalog.jpg',
            'file_path' => $filePath,
            'disk' => 'local',
            'file_size' => 1048576,
            'mime_type' => 'image/jpeg',
            'category' => StorageFile::CATEGORY_PRODUCT_IMAGE,
            'module' => 'catalog',
            'status' => StorageFile::STATUS_ACTIVE,
        ]);

        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->delete(route('billing.storage.files.destroy', $fileA));

        $response->assertRedirect(route('billing.limits'));
        $this->assertSoftDeleted('storage_files', ['id' => $fileA->id]);

        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $this->businessA->id,
            'action' => 'billing.storage.file_deleted',
            'auditable_type' => StorageFile::class,
            'auditable_id' => $fileA->id,
        ]);
    }

    public function test_storage_recalculation_and_audit_logging(): void
    {
        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->post(route('billing.storage.recalculate'));

        $response->assertRedirect(route('billing.limits'));
        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $this->businessA->id,
            'action' => 'billing.storage.recalculated',
            'auditable_type' => Business::class,
            'auditable_id' => $this->businessA->id,
        ]);
    }

    public function test_order_store_creates_audit_log(): void
    {
        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->post(route('billing.order.store'), [
                'type' => 'subscription',
                'tier' => 'standard',
                'cycle' => 'monthly',
                'payment_method' => SubscriptionPayment::METHOD_QRIS,
            ]);

        $payment = SubscriptionPayment::where('business_id', $this->businessA->id)->latest()->first();
        $this->assertNotNull($payment);

        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $this->businessA->id,
            'action' => 'billing.order.created',
            'auditable_type' => SubscriptionPayment::class,
            'auditable_id' => $payment->id,
        ]);
    }
}
