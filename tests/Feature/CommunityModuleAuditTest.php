<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Billing\EntitlementService;
use App\Models\AuditLog;
use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\CommunityComment;
use App\Models\CommunityEvent;
use App\Models\CommunityEventRegistration;
use App\Models\CommunityPost;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CommunityModuleAuditTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->owner = User::create([
            'name' => 'Owner Utama',
            'email' => 'owner.utama@community-audit.test',
            'password' => 'password',
        ]);

        $this->business = Business::create(['name' => 'Bisnis Utama Community']);
        $this->business->users()->attach($this->owner->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $this->owner->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);

        BusinessSubscription::create([
            'business_id' => $this->business->id,
            'plan_code' => BusinessSubscription::PLAN_CORE,
            'status' => BusinessSubscription::STATUS_ACTIVE,
            'starts_at' => now(),
        ]);
    }

    private function createOtherUser(): array
    {
        $otherUser = User::create([
            'name' => 'User Kedua',
            'email' => 'user.kedua@community-audit.test',
            'password' => 'password',
        ]);

        $otherBiz = Business::create(['name' => 'Bisnis Lain']);
        $otherBiz->users()->attach($otherUser->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $otherUser->update(['active_business_id' => $otherBiz->id]);

        BusinessSubscription::create([
            'business_id' => $otherBiz->id,
            'plan_code' => BusinessSubscription::PLAN_CORE,
            'status' => BusinessSubscription::STATUS_ACTIVE,
            'starts_at' => now(),
        ]);

        return [$otherUser, $otherBiz];
    }

    private function createEvent(array $overrides = []): CommunityEvent
    {
        return CommunityEvent::create(array_merge([
            'title' => 'Event Komunitas Test',
            'slug' => 'event-komunitas-test-' . Str::random(8),
            'description' => 'Deskripsi event audit test.',
            'event_type' => CommunityEvent::TYPE_ONLINE,
            'price_type' => CommunityEvent::PRICE_TYPE_FREE,
            'price' => 0,
            'location_name' => 'Online Google Meet / Zoom',
            'checkin_code' => 'COOCA-EVT-' . Str::upper(Str::random(8)),
            'start_time' => now()->addDays(5),
            'end_time' => now()->addDays(5)->addHours(2),
            'registration_deadline' => now()->addDays(4),
            'quota' => 100,
            'status' => CommunityEvent::STATUS_PUBLISHED,
        ], $overrides));
    }

    public function test_guest_is_redirected_to_login_on_community_endpoints(): void
    {
        $this->get(route('community.index'))->assertRedirect(route('login'));
        $this->get(route('community.events.index'))->assertRedirect(route('login'));
        $this->post(route('community.store'), ['content' => 'Halo tamu'])->assertRedirect(route('login'));
    }

    public function test_event_registration_creates_audit_log(): void
    {
        $event = $this->createEvent([
            'title' => 'Webinar UMKM Skala Nasional',
            'description' => 'Strategi Scale Up UMKM 2026',
        ]);

        $response = $this->actingAs($this->owner)
            ->post(route('community.events.register', $event));

        $response->assertRedirect();

        $this->assertDatabaseHas('community_event_registrations', [
            'event_id' => $event->id,
            'user_id' => $this->owner->id,
            'status' => CommunityEventRegistration::STATUS_REGISTERED,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'community.event.registered',
            'user_id' => $this->owner->id,
            'auditable_type' => CommunityEventRegistration::class,
        ]);
    }

    public function test_event_registration_cancellation_creates_audit_log(): void
    {
        $event = $this->createEvent([
            'title' => 'Workshop Finansial UMKM',
        ]);

        CommunityEventRegistration::create([
            'event_id' => $event->id,
            'user_id' => $this->owner->id,
            'business_id' => $this->business->id,
            'ticket_code' => 'TICKET-TEST-CANCEL-001',
            'status' => CommunityEventRegistration::STATUS_REGISTERED,
            'amount_paid' => 0,
            'registered_at' => now(),
        ]);

        $response = $this->actingAs($this->owner)
            ->post(route('community.events.cancel', $event));

        $response->assertRedirect();

        $this->assertDatabaseHas('community_event_registrations', [
            'event_id' => $event->id,
            'user_id' => $this->owner->id,
            'status' => CommunityEventRegistration::STATUS_CANCELLED,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'community.event.registration_cancelled',
            'user_id' => $this->owner->id,
            'auditable_type' => CommunityEventRegistration::class,
        ]);
    }

    public function test_post_deletion_creates_audit_log_and_deletes_post(): void
    {
        $post = CommunityPost::create([
            'owner_id' => $this->owner->id,
            'business_id' => $this->business->id,
            'content' => 'Postingan ini akan segera dihapus untuk uji audit logging.',
        ]);

        $response = $this->actingAs($this->owner)
            ->delete(route('community.destroy', $post));

        $response->assertRedirect();
        $this->assertDatabaseMissing('community_posts', ['id' => $post->id]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'community.post.deleted',
            'user_id' => $this->owner->id,
            'auditable_type' => CommunityPost::class,
            'auditable_id' => $post->id,
        ]);
    }

    public function test_idor_protection_user_cannot_delete_another_users_post(): void
    {
        [$otherUser] = $this->createOtherUser();

        $post = CommunityPost::create([
            'owner_id' => $this->owner->id,
            'business_id' => $this->business->id,
            'content' => 'Postingan milik Owner Utama.',
        ]);

        $response = $this->actingAs($otherUser)
            ->delete(route('community.destroy', $post));

        $response->assertStatus(403);
        $this->assertDatabaseHas('community_posts', ['id' => $post->id]);
    }

    public function test_idor_protection_user_cannot_view_or_simulate_payment_of_another_users_registration(): void
    {
        [$otherUser, $otherBiz] = $this->createOtherUser();

        $event = $this->createEvent([
            'title' => 'Masterclass Bisnis Kuliner',
            'event_type' => CommunityEvent::TYPE_OFFLINE,
            'price_type' => CommunityEvent::PRICE_TYPE_PAID,
            'price' => 75000,
            'quota' => 30,
        ]);

        $registration = CommunityEventRegistration::create([
            'event_id' => $event->id,
            'user_id' => $this->owner->id,
            'business_id' => $this->business->id,
            'ticket_code' => 'TICKET-IDOR-TEST-123',
            'status' => CommunityEventRegistration::STATUS_REGISTERED,
            'payment_status' => CommunityEventRegistration::PAYMENT_STATUS_PENDING,
            'amount_paid' => 75000,
            'payment_expires_at' => now()->addDay(),
            'registered_at' => now(),
        ]);

        // User lain mencoba mengakses payment page registration milik Owner Utama
        $responseView = $this->actingAs($otherUser)
            ->get(route('community.events.payment.show', $registration));
        $responseView->assertStatus(403);

        // User lain mencoba simulate pay registration milik Owner Utama
        $responsePay = $this->actingAs($otherUser)
            ->post(route('community.events.payment.simulate', $registration));
        $responsePay->assertStatus(403);
    }

    public function test_simulate_payment_forbidden_in_production_environment(): void
    {
        $event = $this->createEvent([
            'title' => 'Masterclass Investasi UMKM',
            'event_type' => CommunityEvent::TYPE_ONLINE,
            'price_type' => CommunityEvent::PRICE_TYPE_PAID,
            'price' => 50000,
            'quota' => 50,
        ]);

        $registration = CommunityEventRegistration::create([
            'event_id' => $event->id,
            'user_id' => $this->owner->id,
            'business_id' => $this->business->id,
            'ticket_code' => 'TICKET-PROD-TEST-999',
            'status' => CommunityEventRegistration::STATUS_REGISTERED,
            'payment_status' => CommunityEventRegistration::PAYMENT_STATUS_PENDING,
            'amount_paid' => 50000,
            'payment_expires_at' => now()->addDay(),
            'registered_at' => now(),
        ]);

        // Simulasikan app environment production
        $this->app->detectEnvironment(fn () => 'production');

        $this->withoutMiddleware();

        $response = $this->actingAs($this->owner)
            ->post(route('community.events.payment.simulate', $registration));

        $response->assertStatus(403);
        $this->assertFalse($registration->fresh()->isPaid());
    }

    public function test_content_moderation_blocks_toxic_and_gambling_keywords_in_post(): void
    {
        $response = $this->actingAs($this->owner)
            ->from(route('community.index'))
            ->post(route('community.store'), [
                'content' => 'Daftar sekarang di slot gacor maxwin deposit pulsa tanpa potongan!',
            ]);

        $response->assertSessionHasErrors('content');
        $this->assertDatabaseMissing('community_posts', [
            'owner_id' => $this->owner->id,
        ]);
    }

    public function test_content_moderation_blocks_toxic_words_in_comment(): void
    {
        $post = CommunityPost::create([
            'owner_id' => $this->owner->id,
            'business_id' => $this->business->id,
            'content' => 'Diskusi seputar cara promosi produk kuliner UMKM.',
        ]);

        $response = $this->actingAs($this->owner)
            ->postJson(route('community.comment', $post), [
                'content' => 'Info link gacor terbaru kawan langsung klik slot disini',
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);

        $this->assertDatabaseMissing('community_comments', [
            'post_id' => $post->id,
        ]);
    }
}
