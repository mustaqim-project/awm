<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\CommunityComment;
use App\Models\CommunityEvent;
use App\Models\CommunityEventRegistration;
use App\Models\CommunityPost;
use App\Models\Customer;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\Voucher;
use App\Support\Context;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class AdversarialCommunityCrmCustomerTest extends TestCase
{
    use RefreshDatabase;

    private Business $businessA;
    private Business $businessB;

    private User $ownerA;
    private User $managerA;
    private User $kasirA;
    private User $staffA;
    private User $customerViewOnlyUser;

    private User $ownerB;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->seed(RbacSeeder::class);

        // 1. Setup Business A
        $this->businessA = Business::create(['name' => 'Bisnis Alpha PT']);
        BusinessSubscription::create([
            'business_id' => $this->businessA->id,
            'plan_code' => BusinessSubscription::PLAN_CORE,
            'status' => BusinessSubscription::STATUS_ACTIVE,
            'starts_at' => now(),
        ]);

        // Owner A
        $this->ownerA = User::create([
            'name' => 'Owner Alpha',
            'email' => 'owner.a@adversarial-test.local',
            'password' => 'password123',
        ]);
        $ownerRole = Role::where('slug', 'owner')->firstOrFail();
        $this->businessA->users()->attach($this->ownerA->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'role_id' => $ownerRole->id,
        ]);
        $this->ownerA->update(['active_business_id' => $this->businessA->id]);

        // Manager A (has community.manage, crm.manage, customers.*)
        $this->managerA = User::create([
            'name' => 'Manager Alpha',
            'email' => 'manager.a@adversarial-test.local',
            'password' => 'password123',
        ]);
        $managerRole = Role::where('slug', 'manager')->firstOrFail();
        $this->businessA->users()->attach($this->managerA->id, [
            'id' => (string) Str::uuid(),
            'role' => 'manager',
            'role_id' => $managerRole->id,
        ]);
        $this->managerA->update(['active_business_id' => $this->businessA->id]);

        // Kasir A (cashier role: community.view, community.events.register, customers.view, customers.create, NO community.post, NO community.manage)
        $this->kasirA = User::create([
            'name' => 'Kasir Alpha',
            'email' => 'kasir.a@adversarial-test.local',
            'password' => 'password123',
        ]);
        $cashierRole = Role::where('slug', 'cashier')->firstOrFail();
        $this->businessA->users()->attach($this->kasirA->id, [
            'id' => (string) Str::uuid(),
            'role' => 'cashier',
            'role_id' => $cashierRole->id,
        ]);
        $this->kasirA->update(['active_business_id' => $this->businessA->id]);

        // Staff A (staff role: community.view, community.post, community.events.register, NO community.manage)
        $this->staffA = User::create([
            'name' => 'Staff Alpha',
            'email' => 'staff.a@adversarial-test.local',
            'password' => 'password123',
        ]);
        $staffRole = Role::where('slug', 'staff')->firstOrFail();
        $this->businessA->users()->attach($this->staffA->id, [
            'id' => (string) Str::uuid(),
            'role' => 'staff',
            'role_id' => $staffRole->id,
        ]);
        $this->staffA->update(['active_business_id' => $this->businessA->id]);

        // Customer View-Only User (only customers.view permission)
        $this->customerViewOnlyUser = User::create([
            'name' => 'Viewer Alpha',
            'email' => 'viewer.a@adversarial-test.local',
            'password' => 'password123',
        ]);
        $customViewRole = Role::create([
            'business_id' => $this->businessA->id,
            'name' => 'Custom View Only',
            'slug' => 'role_cust_view_only_' . Str::random(5),
            'description' => 'View only customers and crm',
        ]);
        $custViewPerms = Permission::whereIn('slug', ['customers.view', 'crm.view', 'community.view'])->pluck('id');
        $customViewRole->permissions()->sync($custViewPerms);
        $this->businessA->users()->attach($this->customerViewOnlyUser->id, [
            'id' => (string) Str::uuid(),
            'role' => $customViewRole->slug,
            'role_id' => $customViewRole->id,
        ]);
        $this->customerViewOnlyUser->update(['active_business_id' => $this->businessA->id]);

        // 2. Setup Business B (for cross-tenant attacks)
        $this->businessB = Business::create(['name' => 'Bisnis Beta CV']);
        BusinessSubscription::create([
            'business_id' => $this->businessB->id,
            'plan_code' => BusinessSubscription::PLAN_CORE,
            'status' => BusinessSubscription::STATUS_ACTIVE,
            'starts_at' => now(),
        ]);

        $this->ownerB = User::create([
            'name' => 'Owner Beta',
            'email' => 'owner.b@adversarial-test.local',
            'password' => 'password123',
        ]);
        $this->businessB->users()->attach($this->ownerB->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'role_id' => $ownerRole->id,
        ]);
        $this->ownerB->update(['active_business_id' => $this->businessB->id]);
    }

    /**
     * CHALLENGE 1: Unauthenticated guests must be strictly redirected to login.
     */
    public function test_guest_access_is_blocked_with_redirect_to_login(): void
    {
        $this->get(route('community.index'))->assertRedirect(route('login'));
        $this->get(route('community.events.index'))->assertRedirect(route('login'));
        $this->post(route('community.store'), ['content' => 'Tamu'])->assertRedirect(route('login'));
        $this->get(route('customers.index'))->assertRedirect(route('login'));
        $this->get(route('crm.members.index'))->assertRedirect(route('login'));
        $this->get(route('crm.vouchers.index'))->assertRedirect(route('login'));
    }

    /**
     * CHALLENGE 2: Kasir (without community.post or community.manage) CANNOT perform community actions.
     */
    public function test_kasir_role_cannot_post_comment_or_delete_community_posts(): void
    {
        $post = CommunityPost::create([
            'owner_id' => $this->ownerA->id,
            'business_id' => $this->businessA->id,
            'content' => 'Postingan pengumuman Owner',
        ]);

        // 1. Kasir attempting POST /community (create post) -> must be blocked
        $resStore = $this->actingAs($this->kasirA)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->post(route('community.store'), [
                'content' => 'Kasir mencoba posting',
            ]);
        $resStore->assertRedirect(route('portal'));

        // 2. Kasir attempting POST /community/{post}/comment (JSON) -> 403 Forbidden
        $resComment = $this->actingAs($this->kasirA)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->postJson(route('community.comment', $post), [
                'content' => 'Komentar kasir ilegal',
            ]);
        $resComment->assertForbidden();

        // 3. Kasir attempting POST /community/{post}/like (JSON) -> 403 Forbidden
        $resLike = $this->actingAs($this->kasirA)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->postJson(route('community.like', $post));
        $resLike->assertForbidden();

        // 4. Kasir attempting DELETE /community/{post} (JSON) -> 403 Forbidden
        $resDelete = $this->actingAs($this->kasirA)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->deleteJson(route('community.destroy', $post));
        $resDelete->assertForbidden();

        // Ensure post was NOT deleted
        $this->assertDatabaseHas('community_posts', ['id' => $post->id]);
    }

    /**
     * CHALLENGE 3: Staff (has community.post, but NO community.manage) CANNOT delete another user's post.
     */
    public function test_staff_cannot_delete_other_users_post_even_within_same_tenant(): void
    {
        $ownerPost = CommunityPost::create([
            'owner_id' => $this->ownerA->id,
            'business_id' => $this->businessA->id,
            'content' => 'Postingan milik Owner Alpha',
        ]);

        // Staff has community.post, so passes middleware, but must be rejected by controller authorization
        $res = $this->actingAs($this->staffA)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->deleteJson(route('community.destroy', $ownerPost));

        $res->assertForbidden();
        $this->assertDatabaseHas('community_posts', ['id' => $ownerPost->id]);
    }

    /**
     * CHALLENGE 4: Staff CAN delete their OWN post.
     */
    public function test_staff_can_delete_their_own_post(): void
    {
        $staffPost = CommunityPost::create([
            'owner_id' => $this->staffA->id,
            'business_id' => $this->businessA->id,
            'content' => 'Postingan milik Staff Alpha',
        ]);

        $res = $this->actingAs($this->staffA)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->delete(route('community.destroy', $staffPost));

        $res->assertRedirect();
        $this->assertDatabaseMissing('community_posts', ['id' => $staffPost->id]);
    }

    /**
     * CHALLENGE 5: Cross-Tenant Isolation: User with community.manage in Tenant B CANNOT delete Tenant A post.
     */
    public function test_cross_tenant_moderator_cannot_delete_another_business_post(): void
    {
        $postA = CommunityPost::create([
            'owner_id' => $this->ownerA->id,
            'business_id' => $this->businessA->id,
            'content' => 'Postingan rahasia Bisnis Alpha',
        ]);

        // Owner B has community.manage in Business B, but attempts to delete Post A from Business A
        $res = $this->actingAs($this->ownerB)
            ->withSession(['active_business_id' => $this->businessB->id])
            ->deleteJson(route('community.destroy', $postA));

        $res->assertForbidden();
        $this->assertDatabaseHas('community_posts', ['id' => $postA->id]);
    }

    /**
     * CHALLENGE 6: Intra-Tenant Moderation: Manager with community.manage in Tenant A CAN moderate/delete Staff post in Tenant A.
     */
    public function test_intra_tenant_manager_can_moderate_and_delete_staff_post(): void
    {
        $staffPost = CommunityPost::create([
            'owner_id' => $this->staffA->id,
            'business_id' => $this->businessA->id,
            'content' => 'Postingan spam staf yang perlu dimoderasi',
        ]);

        $res = $this->actingAs($this->managerA)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->delete(route('community.destroy', $staffPost));

        $res->assertRedirect();
        $this->assertDatabaseMissing('community_posts', ['id' => $staffPost->id]);

        // Verify Audit Log was generated
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'community.post.deleted',
            'business_id' => $this->businessA->id,
            'user_id' => $this->managerA->id,
            'auditable_type' => CommunityPost::class,
            'auditable_id' => $staffPost->id,
        ]);
    }

    /**
     * CHALLENGE 7: Read-only Customer user (customers.view only) CANNOT see edit controls in HTML.
     */
    public function test_read_only_customer_user_cannot_see_edit_controls_in_view(): void
    {
        $customer = Customer::create([
            'business_id' => $this->businessA->id,
            'name' => 'Pelanggan Uji UI',
            'code' => 'CUST-UI-001',
            'phone' => '081234567899',
            'current_credit_balance' => 0,
        ]);

        $response = $this->actingAs($this->customerViewOnlyUser)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->get(route('customers.index'));

        $response->assertOk();

        // 1. Modal 2 (Edit Pelanggan template & form) MUST NOT be rendered
        $response->assertDontSee(':action="\'/customers/\'', false);
        $response->assertDontSee('x-model="editCustomer.name"', false);
        $response->assertDontSee('x-model="editCustomer.phone"', false);
        $response->assertDontSee('x-model="editCustomer.company_name"', false);

        // 2. Table row edit button MUST NOT be rendered
        $response->assertDontSee('openEditModal(\'' . $customer->id . '\')', false);

        // 3. Detail modal edit button MUST NOT be rendered
        $response->assertDontSee('openEditModal(selectedCustomer.id)', false);
    }

    /**
     * CHALLENGE 8: Read-only Customer user CANNOT mutate customer data via HTTP.
     */
    public function test_read_only_customer_user_cannot_update_store_or_delete_customer(): void
    {
        $customer = Customer::create([
            'business_id' => $this->businessA->id,
            'name' => 'Data Asli Pelanggan',
            'code' => 'CUST-ORIG-001',
            'phone' => '081111111111',
            'current_credit_balance' => 0,
        ]);

        // 1. Attempt UPDATE via JSON -> 403 Forbidden
        $resUpdate = $this->actingAs($this->customerViewOnlyUser)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->putJson(route('customers.update', $customer), [
                'name' => 'Nama Diubah Pembajak',
            ]);
        $resUpdate->assertForbidden();
        $this->assertEquals('Data Asli Pelanggan', $customer->fresh()->name);

        // 2. Attempt STORE via JSON -> 403 Forbidden
        $resStore = $this->actingAs($this->customerViewOnlyUser)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->postJson(route('customers.store'), [
                'name' => 'Pelanggan Baru Ilegal',
                'phone' => '082222222222',
            ]);
        $resStore->assertForbidden();

        // 3. Attempt DESTROY via JSON -> 403 Forbidden
        $resDestroy = $this->actingAs($this->customerViewOnlyUser)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->deleteJson(route('customers.destroy', $customer));
        $resDestroy->assertForbidden();
        $this->assertDatabaseHas('customers', ['id' => $customer->id, 'deleted_at' => null]);
    }

    /**
     * CHALLENGE 9: Read-only CRM user (crm.view only) CANNOT create vouchers or process credit payments.
     */
    public function test_read_only_crm_user_cannot_mutate_vouchers_or_credit(): void
    {
        $customer = Customer::create([
            'business_id' => $this->businessA->id,
            'name' => 'Pelanggan CRM Test',
            'phone' => '083333333333',
            'current_credit_balance' => 200000,
        ]);

        // 1. View listing -> 200 OK
        $this->actingAs($this->customerViewOnlyUser)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->get(route('crm.vouchers.index'))
            ->assertOk();

        // 2. Attempt POST /crm/vouchers (create voucher) -> blocked (portal redirect or 403)
        $resVoucher = $this->actingAs($this->customerViewOnlyUser)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->post(route('crm.vouchers.store'), [
                'code' => 'ILEGALVOUCHER',
                'name' => 'Voucher Ilegal',
                'discount_type' => 'fixed',
                'discount_value' => 10000,
            ]);
        $resVoucher->assertRedirect(route('portal'));
        $this->assertDatabaseMissing('vouchers', ['code' => 'ILEGALVOUCHER']);

        // 3. Attempt POST /crm/customers/{customer}/credit-payment -> 403 Forbidden
        $resPay = $this->actingAs($this->customerViewOnlyUser)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->postJson(route('crm.customers.credit-payment', $customer), [
                'amount' => 50000,
            ]);
        $resPay->assertForbidden();
        $this->assertEquals(200000, $customer->fresh()->current_credit_balance);
    }

    /**
     * CHALLENGE 10: Community View Action Guarding: Delete button visibility across roles.
     */
    public function test_community_view_delete_button_visibility(): void
    {
        $ownerPost = CommunityPost::create([
            'owner_id' => $this->ownerA->id,
            'business_id' => $this->businessA->id,
            'content' => 'Postingan Owner Alpha Unik',
        ]);

        $staffPost = CommunityPost::create([
            'owner_id' => $this->staffA->id,
            'business_id' => $this->businessA->id,
            'content' => 'Postingan Staff Alpha Unik',
        ]);

        // 1. Kasir views community feed:
        // Kasir cannot manage or delete, does not own either post.
        // Neither post delete button should be visible to Kasir.
        $resKasir = $this->actingAs($this->kasirA)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->get(route('community.index'));
        $resKasir->assertOk();
        $resKasir->assertDontSee(route('community.destroy', $ownerPost));
        $resKasir->assertDontSee(route('community.destroy', $staffPost));

        // 2. Staff views community feed:
        // Staff owns staffPost -> delete button visible for staffPost.
        // Staff does NOT own ownerPost, and has NO community.manage -> delete button MUST NOT be visible for ownerPost.
        $resStaff = $this->actingAs($this->staffA)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->get(route('community.index'));
        $resStaff->assertOk();
        $resStaff->assertSee(route('community.destroy', $staffPost));
        $resStaff->assertDontSee(route('community.destroy', $ownerPost));

        // 3. Manager views community feed:
        // Manager has community.manage in Business A -> delete button is visible for staffPost within Business A.
        $resManager = $this->actingAs($this->managerA)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->get(route('community.index'));
        $resManager->assertOk();
        $resManager->assertSee(route('community.destroy', $staffPost));

        // 4. Cross-tenant post view observation:
        // When postB belongs to Business B, what happens if Manager A views it?
        $postB = CommunityPost::create([
            'owner_id' => $this->ownerB->id,
            'business_id' => $this->businessB->id,
            'content' => 'Postingan Publik Bisnis Beta',
        ]);
        // Controller strictly protects this: if Manager A tries to submit DELETE /community/{postB}, it aborts 403.
        $resCrossDelete = $this->actingAs($this->managerA)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->deleteJson(route('community.destroy', $postB));
        $resCrossDelete->assertForbidden();
    }

    /**
     * CHALLENGE 11: User without community.events.register cannot register or cancel events.
     */
    public function test_user_without_community_events_register_is_blocked(): void
    {
        $event = CommunityEvent::create([
            'title' => 'Webinar UMKM Ekspor',
            'slug' => 'webinar-umkm-ekspor-' . Str::random(6),
            'description' => 'Panduan ekspor ke mancanegara.',
            'event_type' => CommunityEvent::TYPE_ONLINE,
            'price_type' => CommunityEvent::PRICE_TYPE_FREE,
            'price' => 0,
            'location_name' => 'Online Zoom',
            'checkin_code' => 'EXP-' . Str::upper(Str::random(6)),
            'start_time' => now()->addDays(3),
            'end_time' => now()->addDays(3)->addHours(2),
            'registration_deadline' => now()->addDays(2),
            'quota' => 50,
            'status' => CommunityEvent::STATUS_PUBLISHED,
        ]);

        // CustomerViewOnlyUser has only [customers.view, crm.view, community.view]
        // They do NOT have community.events.register
        $resReg = $this->actingAs($this->customerViewOnlyUser)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->post(route('community.events.register', $event));

        $resReg->assertRedirect(route('portal'));
        $this->assertDatabaseMissing('community_event_registrations', [
            'event_id' => $event->id,
            'user_id' => $this->customerViewOnlyUser->id,
        ]);
    }
}
