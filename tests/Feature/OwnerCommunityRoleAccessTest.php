<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\CommunityEvent;
use App\Models\CommunityPost;
use App\Models\Role;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\DefaultUnitSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class OwnerCommunityRoleAccessTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $owner;
    private User $manager;
    private User $cashier;
    private User $staff;
    private User $finance;
    private User $warehouse;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->seed(DefaultUnitSeeder::class);
        $this->seed(RbacSeeder::class);

        $this->business = Business::create([
            'name' => 'Bisnis Komunitas Owner PT',
            'currency' => 'IDR',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
        ]);

        BusinessSubscription::create([
            'business_id' => $this->business->id,
            'plan_code' => BusinessSubscription::PLAN_CORE,
            'status' => BusinessSubscription::STATUS_ACTIVE,
            'starts_at' => now(),
        ]);

        // Owner
        $this->owner = User::create([
            'name' => 'Pak Owner',
            'email' => 'owner@komunitas-test.local',
            'password' => 'password123',
        ]);
        $ownerRole = Role::where('slug', 'owner')->firstOrFail();
        $this->business->users()->attach($this->owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'role_id' => $ownerRole->id,
            'is_active' => true,
        ]);
        $this->owner->update(['active_business_id' => $this->business->id]);

        // Manager
        $this->manager = User::create([
            'name' => 'Pak Manager',
            'email' => 'manager@komunitas-test.local',
            'password' => 'password123',
        ]);
        $managerRole = Role::where('slug', 'manager')->firstOrFail();
        $this->business->users()->attach($this->manager->id, [
            'id' => (string) Str::uuid(),
            'role' => 'manager',
            'role_id' => $managerRole->id,
            'is_active' => true,
        ]);
        $this->manager->update(['active_business_id' => $this->business->id]);

        // Cashier
        $this->cashier = User::create([
            'name' => 'Mbak Kasir',
            'email' => 'cashier@komunitas-test.local',
            'password' => 'password123',
        ]);
        $cashierRole = Role::where('slug', 'cashier')->firstOrFail();
        $this->business->users()->attach($this->cashier->id, [
            'id' => (string) Str::uuid(),
            'role' => 'cashier',
            'role_id' => $cashierRole->id,
            'is_active' => true,
        ]);
        $this->cashier->update(['active_business_id' => $this->business->id]);

        // Staff
        $this->staff = User::create([
            'name' => 'Mas Staff',
            'email' => 'staff@komunitas-test.local',
            'password' => 'password123',
        ]);
        $staffRole = Role::where('slug', 'staff')->firstOrFail();
        $this->business->users()->attach($this->staff->id, [
            'id' => (string) Str::uuid(),
            'role' => 'staff',
            'role_id' => $staffRole->id,
            'is_active' => true,
        ]);
        $this->staff->update(['active_business_id' => $this->business->id]);

        // Finance
        $this->finance = User::create([
            'name' => 'Ibu Finance',
            'email' => 'finance@komunitas-test.local',
            'password' => 'password123',
        ]);
        $financeRole = Role::where('slug', 'finance')->firstOrFail();
        $this->business->users()->attach($this->finance->id, [
            'id' => (string) Str::uuid(),
            'role' => 'finance',
            'role_id' => $financeRole->id,
            'is_active' => true,
        ]);
        $this->finance->update(['active_business_id' => $this->business->id]);

        // Warehouse
        $this->warehouse = User::create([
            'name' => 'Bang Gudang',
            'email' => 'warehouse@komunitas-test.local',
            'password' => 'password123',
        ]);
        $warehouseRole = Role::where('slug', 'warehouse')->firstOrFail();
        $this->business->users()->attach($this->warehouse->id, [
            'id' => (string) Str::uuid(),
            'role' => 'warehouse',
            'role_id' => $warehouseRole->id,
            'is_active' => true,
        ]);
        $this->warehouse->update(['active_business_id' => $this->business->id]);
    }

    public function test_owner_can_access_community_feed_and_events(): void
    {
        $responseFeed = $this->actingAs($this->owner, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('community.index'));
        $responseFeed->assertOk();
        $responseFeed->assertSee(__('community.header_title'));

        $responseEvents = $this->actingAs($this->owner, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('community.events.index'));
        $responseEvents->assertOk();
    }

    public function test_owner_can_post_and_comment_in_community(): void
    {
        $resPost = $this->actingAs($this->owner, 'web')
            ->from(route('community.index'))
            ->withSession(['active_business_id' => $this->business->id])
            ->post(route('community.store'), [
                'content' => 'Ide bisnis ekspansi cabang baru dari owner!',
            ]);
        $resPost->assertRedirect(route('community.index'));

        $this->assertDatabaseHas('community_posts', [
            'owner_id' => $this->owner->id,
            'content' => 'Ide bisnis ekspansi cabang baru dari owner!',
        ]);

        $post = CommunityPost::where('owner_id', $this->owner->id)->firstOrFail();

        $resComment = $this->actingAs($this->owner, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('community.comment', $post), [
                'content' => 'Komentar tindak lanjut ide bisnis.',
            ]);
        $resComment->assertOk();
        $this->assertDatabaseHas('community_comments', [
            'post_id' => $post->id,
            'user_id' => $this->owner->id,
            'content' => 'Komentar tindak lanjut ide bisnis.',
        ]);
    }

    public function test_non_owner_roles_are_blocked_from_web_community_routes(): void
    {
        $nonOwners = [
            'manager' => $this->manager,
            'cashier' => $this->cashier,
            'staff' => $this->staff,
            'finance' => $this->finance,
            'warehouse' => $this->warehouse,
        ];

        foreach ($nonOwners as $roleName => $user) {
            // GET /community -> Redirected to portal
            $resFeed = $this->actingAs($user, 'web')
                ->withSession(['active_business_id' => $this->business->id])
                ->get(route('community.index'));
            $resFeed->assertRedirect(route('portal'));

            // GET /community/events -> Redirected to portal
            $resEvents = $this->actingAs($user, 'web')
                ->withSession(['active_business_id' => $this->business->id])
                ->get(route('community.events.index'));
            $resEvents->assertRedirect(route('portal'));

            // POST /community -> Redirected to portal
            $resStore = $this->actingAs($user, 'web')
                ->withSession(['active_business_id' => $this->business->id])
                ->post(route('community.store'), [
                    'content' => "Postingan ilegal dari {$roleName}",
                ]);
            $resStore->assertRedirect(route('portal'));
        }
    }

    public function test_non_owner_roles_are_blocked_from_api_community_routes(): void
    {
        $response = $this->actingAs($this->manager, 'sanctum')
            ->withHeader('X-Business-Id', $this->business->id)
            ->getJson('/api/v1/community/feed');

        $response->assertForbidden();
        $response->assertJsonPath('error_code', 'FORBIDDEN_ROLE');

        $responsePost = $this->actingAs($this->cashier, 'sanctum')
            ->withHeader('X-Business-Id', $this->business->id)
            ->postJson('/api/v1/community/posts', [
                'content' => 'Kasir posting ke API',
            ]);
        $responsePost->assertForbidden();
        $responsePost->assertJsonPath('error_code', 'FORBIDDEN_ROLE');
    }

    public function test_sidebar_displays_community_menu_only_for_owner(): void
    {
        // Owner views dashboard -> sees #tour-nav-community
        $resOwner = $this->actingAs($this->owner, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('dashboard'));
        $resOwner->assertOk();
        $resOwner->assertSee('id="tour-nav-community"', false);

        // Manager views dashboard -> does NOT see #tour-nav-community
        $resManager = $this->actingAs($this->manager, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('dashboard'));
        $resManager->assertOk();
        $resManager->assertDontSee('id="tour-nav-community"', false);

        // Kasir views dashboard -> does NOT see #tour-nav-community
        $resKasir = $this->actingAs($this->cashier, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('dashboard'));
        $resKasir->assertOk();
        $resKasir->assertDontSee('id="tour-nav-community"', false);
    }
}
