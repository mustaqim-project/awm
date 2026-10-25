<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\BugReport;
use App\Models\Business;
use App\Models\FeatureRequest;
use App\Models\Role;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

final class FeedbackModuleAuditTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $owner;
    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->seed(RbacSeeder::class);

        // Bisnis Utama
        $this->business = Business::create(['name' => 'Bisnis Feedback Utama']);

        // Owner (Pemilik Usaha yang berhak memberikan masukan platform)
        $this->owner = User::create([
            'name' => 'Owner Feedback',
            'email' => 'owner.fb@audit-test.local',
            'email_verified_at' => now(),
            'phone' => '081234567990',
            'phone_verified_at' => now(),
            'password' => 'password123',
        ]);

        $ownerRole = Role::where('slug', 'owner')->first();
        $this->business->users()->attach($this->owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'role_id' => $ownerRole?->id,
        ]);
        $this->owner->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);

        // Staff (Bukan Owner, tidak boleh mengakses feedback platform)
        $this->staff = User::create([
            'name' => 'Staff Biasa',
            'email' => 'staff.fb@audit-test.local',
            'email_verified_at' => now(),
            'phone' => '081234567991',
            'phone_verified_at' => now(),
            'password' => 'password123',
        ]);
        $roleStaff = Role::create([
            'business_id' => $this->business->id,
            'name' => 'Custom Staff',
            'slug' => 'custom_staff_fb',
            'description' => 'Staff biasa',
        ]);
        $this->business->users()->attach($this->staff->id, [
            'id' => (string) Str::uuid(),
            'role' => 'custom_staff_fb',
            'role_id' => $roleStaff->id,
        ]);
        $this->staff->update(['active_business_id' => $this->business->id]);
    }

    private function createOtherTenant(): array
    {
        $otherUser = User::create([
            'name' => 'Owner Tenant Lain',
            'email' => 'other.owner@fb-test.local',
            'email_verified_at' => now(),
            'phone' => '089999999990',
            'phone_verified_at' => now(),
            'password' => 'password123',
        ]);

        $otherBiz = Business::create(['name' => 'Bisnis Tenant Lain']);
        $ownerRole = Role::where('slug', 'owner')->first();
        $otherBiz->users()->attach($otherUser->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'role_id' => $ownerRole?->id,
        ]);
        $otherUser->update(['active_business_id' => $otherBiz->id]);

        $otherBug = BugReport::create([
            'business_id' => $otherBiz->id,
            'reporter_id' => $otherUser->id,
            'title' => 'Bug Rahasia Toko Sebelah',
            'category' => 'security',
            'severity' => 'critical',
            'description' => 'Data sensitif bug sebelah',
            'status' => 'open',
            'progress_percent' => 0,
        ]);

        $otherFeature = FeatureRequest::create([
            'business_id' => $otherBiz->id,
            'requester_id' => $otherUser->id,
            'title' => 'Feature Request Toko Sebelah',
            'category' => 'finance',
            'priority' => 'high',
            'description' => 'Ide rahasia toko sebelah',
            'business_value' => 'Nilai tinggi',
            'use_case' => 'Kasus penggunaan',
            'status' => 'submitted',
            'progress_percent' => 0,
        ]);

        return [$otherUser, $otherBiz, $otherBug, $otherFeature];
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('feedback.bugs.index'))->assertRedirect(route('login'));
        $this->get(route('feedback.bugs.create'))->assertRedirect(route('login'));
        $this->get(route('feedback.features.index'))->assertRedirect(route('login'));
        $this->get(route('feedback.features.create'))->assertRedirect(route('login'));
    }

    public function test_non_owner_role_is_blocked_from_feedback_module(): void
    {
        // Staf dengan role non-owner diblokir oleh middleware require.role:owner dengan status 403 Forbidden
        $this->actingAs($this->staff)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('feedback.bugs.index'))
            ->assertForbidden();

        $this->actingAs($this->staff)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('feedback.features.index'))
            ->assertForbidden();
    }

    public function test_owner_can_view_bugs_and_features_index(): void
    {
        $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('feedback.bugs.index'))
            ->assertOk();

        $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('feedback.features.index'))
            ->assertOk();
    }

    public function test_owner_can_create_bug_report_with_audit_log(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->post(route('feedback.bugs.store'), [
                'title' => 'Gagal Cetak Struk via Bluetooth',
                'category' => 'ui',
                'severity' => 'normal',
                'description' => 'Struk tidak keluar saat menekan tombol cetak pada kasir POS tablet Android.',
                'steps_to_reproduce' => '1. Buka kasir 2. Transaksi 3. Tekan cetak',
                'expected_behavior' => 'Struk fisik tercetak',
                'actual_behavior' => 'Printer diam tidak merespon',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('bug_reports', [
            'business_id' => $this->business->id,
            'reporter_id' => $this->owner->id,
            'title' => 'Gagal Cetak Struk via Bluetooth',
            'category' => 'ui',
            'severity' => 'normal',
            'status' => 'open',
        ]);

        $bug = BugReport::where('title', 'Gagal Cetak Struk via Bluetooth')->firstOrFail();

        // Pastikan AuditLog terekam
        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $this->business->id,
            'action' => 'feedback.bug_reported',
            'auditable_type' => BugReport::class,
            'auditable_id' => $bug->id,
        ]);
    }

    public function test_owner_can_create_feature_request_with_audit_log(): void
    {
        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->post(route('feedback.features.store'), [
                'title' => 'Integrasi Pembayaran QRIS Dinamis Statis Multi-Bank',
                'category' => 'finance',
                'priority' => 'high',
                'description' => 'Menambahkan fitur QRIS langsung terhubung ke EDC perbankan.',
                'business_value' => 'Mempercepat antrean kasir hingga 50%',
                'use_case' => 'Saat jam makan siang pelanggan membayar tanpa kembalian tunai.',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('feature_requests', [
            'business_id' => $this->business->id,
            'requester_id' => $this->owner->id,
            'title' => 'Integrasi Pembayaran QRIS Dinamis Statis Multi-Bank',
            'category' => 'finance',
            'priority' => 'high',
            'status' => 'submitted',
        ]);

        $feature = FeatureRequest::where('title', 'Integrasi Pembayaran QRIS Dinamis Statis Multi-Bank')->firstOrFail();

        // Pastikan AuditLog terekam
        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $this->business->id,
            'action' => 'feedback.feature_requested',
            'auditable_type' => FeatureRequest::class,
            'auditable_id' => $feature->id,
        ]);
    }

    public function test_idor_protection_bug_report_and_feature_request_show(): void
    {
        [, , $otherBug, $otherFeature] = $this->createOtherTenant();

        // 1. Mencoba melihat detail bug report milik tenant lain -> 404 (karena BusinessScope) atau 403
        $resBug = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('feedback.bugs.show', $otherBug));
        $this->assertContains($resBug->status(), [403, 404]);

        // 2. Mencoba melihat detail feature request milik tenant lain -> 404 atau 403
        $resFeature = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('feedback.features.show', $otherFeature));
        $this->assertContains($resFeature->status(), [403, 404]);
    }

    public function test_attachment_validation_rejects_dangerous_file_types(): void
    {
        Storage::fake('public');

        $maliciousFile = UploadedFile::fake()->create('malware.php', 100, 'application/x-php');

        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->post(route('feedback.bugs.store'), [
                'title' => 'Percobaan Upload Shell',
                'category' => 'security',
                'severity' => 'critical',
                'description' => 'Test upload shell',
                'attachment' => $maliciousFile,
            ]);

        $response->assertSessionHasErrors('attachment');
        $this->assertDatabaseMissing('bug_reports', ['title' => 'Percobaan Upload Shell']);
    }
}
