<?php

declare(strict_types=1);

namespace Tests\Feature\WhatsApp;

use App\Domain\WhatsApp\WhatsAppGatewayService;
use App\Models\Business;
use App\Models\Role;
use App\Models\User;
use App\Models\WhatsAppAccount;
use App\Models\WhatsAppBroadcastCampaign;
use App\Models\WhatsAppMessageLog;
use App\Support\Context;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;

class WhatsAppRbacSecurityTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $owner;
    private User $manager;
    private User $viewer;
    private User $cashier;
    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->seed(RbacSeeder::class);

        Config::set('services.meta_whatsapp.app_id', '123456789012345');
        Config::set('services.meta_whatsapp.app_secret', 'test_meta_app_secret_12345');
        Config::set('services.meta_whatsapp.config_id', 'test_embedded_config_id_99');
        Config::set('services.meta_whatsapp.webhook_verify_token', 'test_verify_token_secure');

        app()->setLocale('id');

        // 1. Owner (Omnipotent in tenant context)
        $this->owner = User::factory()->create([
            'name'  => 'Owner WA User',
            'email' => 'owner.wa@example.test',
        ]);

        $this->business = Business::create([
            'user_id'  => $this->owner->id,
            'name'     => 'Kopi Kenangan Sejahtera',
            'status'   => 'active',
            'currency' => 'IDR',
        ]);

        $roleOwner = Role::where('slug', 'owner')->first();
        $this->business->users()->attach($this->owner->id, [
            'id'      => (string) Str::uuid(),
            'role'    => 'owner',
            'role_id' => $roleOwner?->id,
        ]);
        $this->owner->update(['active_business_id' => $this->business->id]);

        // 2. Manager (Possesses whatsapp.view and whatsapp.manage)
        $roleManager = Role::where('slug', 'manager')->first();
        $this->manager = User::factory()->create([
            'name'  => 'Manager WA User',
            'email' => 'manager.wa@example.test',
        ]);
        $this->business->users()->attach($this->manager->id, [
            'id'      => (string) Str::uuid(),
            'role'    => 'manager',
            'role_id' => $roleManager?->id,
        ]);
        $this->manager->update(['active_business_id' => $this->business->id]);

        // 3. Viewer (Possesses whatsapp.view only, lacks whatsapp.manage & reports.export)
        $roleViewer = Role::where('slug', 'viewer')->first();
        $this->viewer = User::factory()->create([
            'name'  => 'Viewer WA User',
            'email' => 'viewer.wa@example.test',
        ]);
        $this->business->users()->attach($this->viewer->id, [
            'id'      => (string) Str::uuid(),
            'role'    => 'viewer',
            'role_id' => $roleViewer?->id,
        ]);
        $this->viewer->update(['active_business_id' => $this->business->id]);

        // 4. Cashier (POS role, lacks whatsapp.view and whatsapp.manage)
        $roleCashier = Role::where('slug', 'cashier')->first();
        $this->cashier = User::factory()->create([
            'name'  => 'Kasir WA User',
            'email' => 'cashier.wa@example.test',
        ]);
        $this->business->users()->attach($this->cashier->id, [
            'id'      => (string) Str::uuid(),
            'role'    => 'cashier',
            'role_id' => $roleCashier?->id,
        ]);
        $this->cashier->update(['active_business_id' => $this->business->id]);

        // 5. Staff (General employee, lacks whatsapp.view and whatsapp.manage)
        $roleStaff = Role::where('slug', 'staff')->first();
        $this->staff = User::factory()->create([
            'name'  => 'Staff WA User',
            'email' => 'staff.wa@example.test',
        ]);
        $this->business->users()->attach($this->staff->id, [
            'id'      => (string) Str::uuid(),
            'role'    => 'staff',
            'role_id' => $roleStaff?->id,
        ]);
        $this->staff->update(['active_business_id' => $this->business->id]);
    }

    private function createWhatsAppAccount(string $status = 'active'): WhatsAppAccount
    {
        return WhatsAppAccount::create([
            'business_id'          => $this->business->id,
            'waba_id'              => '109876543210',
            'phone_number_id'      => '100012345678',
            'phone_number'         => '6281234567890',
            'display_phone_number' => '+62 852-8786-4176',
            'verified_name'        => 'Kopi Kenangan Sejahtera Official',
            'quality_rating'       => 'GREEN',
            'messaging_limit_tier' => 'TIER_1K',
            'access_token'         => 'EAABwzLixnjYBA_test_token',
            'status'               => $status,
        ]);
    }

    /**
     * Unauthenticated guest is redirected to login on protected web routes.
     */
    public function test_unauthenticated_guest_redirected_to_login_on_protected_web_routes(): void
    {
        $this->get(route('whatsapp.index'))->assertRedirect(route('login'));
        $this->get(route('whatsapp.logs.index'))->assertRedirect(route('login'));
        $this->get(route('whatsapp.broadcast.index'))->assertRedirect(route('login'));
    }

    /**
     * Unauthenticated guest receives HTTP 401 Unauthorized on JSON endpoints.
     */
    public function test_unauthenticated_guest_receives_unauthorized_on_json_endpoints(): void
    {
        $this->getJson(route('whatsapp.index'))->assertUnauthorized();
        $this->getJson(route('whatsapp.meta.config'))->assertUnauthorized();
        $this->getJson(route('whatsapp.logs.index'))->assertUnauthorized();
        $this->postJson(route('whatsapp.test'))->assertUnauthorized();
        $this->postJson(route('whatsapp.disconnect'))->assertUnauthorized();
        $this->postJson(route('whatsapp.broadcast.store'))->assertUnauthorized();
    }

    /**
     * Unauthorized users (cashier / staff without whatsapp.view) are redirected to portal on web requests.
     */
    public function test_unauthorized_users_redirected_to_portal_on_standard_web_requests(): void
    {
        // Cashier redirected to portal
        $this->actingAs($this->cashier)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('whatsapp.index'))
            ->assertRedirect(route('portal'))
            ->assertSessionHas('error');

        $this->actingAs($this->cashier)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('whatsapp.logs.index'))
            ->assertRedirect(route('portal'))
            ->assertSessionHas('error');

        $this->actingAs($this->cashier)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('whatsapp.broadcast.index'))
            ->assertRedirect(route('portal'))
            ->assertSessionHas('error');

        // Staff redirected to portal
        $this->actingAs($this->staff)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('whatsapp.index'))
            ->assertRedirect(route('portal'))
            ->assertSessionHas('error');

        $this->actingAs($this->staff)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('whatsapp.logs.index'))
            ->assertRedirect(route('portal'))
            ->assertSessionHas('error');

        $this->actingAs($this->staff)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('whatsapp.broadcast.index'))
            ->assertRedirect(route('portal'))
            ->assertSessionHas('error');
    }

    /**
     * Unauthorized users receive HTTP 403 Forbidden with FORBIDDEN_PERMISSION on JSON endpoints.
     */
    public function test_unauthorized_cashier_receives_403_forbidden_on_json_endpoints(): void
    {
        // Read endpoints
        $this->actingAs($this->cashier)
            ->withSession(['active_business_id' => $this->business->id])
            ->getJson(route('whatsapp.index'))
            ->assertForbidden()
            ->assertJsonFragment(['error_code' => 'FORBIDDEN_PERMISSION']);

        $this->actingAs($this->cashier)
            ->withSession(['active_business_id' => $this->business->id])
            ->getJson(route('whatsapp.meta.config'))
            ->assertForbidden()
            ->assertJsonFragment(['error_code' => 'FORBIDDEN_PERMISSION']);

        $this->actingAs($this->cashier)
            ->withSession(['active_business_id' => $this->business->id])
            ->getJson(route('whatsapp.logs.index'))
            ->assertForbidden()
            ->assertJsonFragment(['error_code' => 'FORBIDDEN_PERMISSION']);

        // Write / Mutating endpoints
        $this->actingAs($this->cashier)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('whatsapp.test'), [
                'phone'   => '081234567890',
                'message' => 'Tes dari kasir',
            ])
            ->assertForbidden()
            ->assertJsonFragment(['error_code' => 'FORBIDDEN_PERMISSION']);

        $this->actingAs($this->cashier)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('whatsapp.disconnect'))
            ->assertForbidden()
            ->assertJsonFragment(['error_code' => 'FORBIDDEN_PERMISSION']);

        $this->actingAs($this->cashier)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('whatsapp.meta.disconnect'))
            ->assertForbidden()
            ->assertJsonFragment(['error_code' => 'FORBIDDEN_PERMISSION']);

        $this->actingAs($this->cashier)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('whatsapp.broadcast.store'), [
                'title'         => 'Kampanye Kasir',
                'message'       => 'Pesan dari kasir',
                'target_filter' => 'all',
            ])
            ->assertForbidden()
            ->assertJsonFragment(['error_code' => 'FORBIDDEN_PERMISSION']);
    }

    /**
     * Read-only user (viewer with whatsapp.view only) can view index and logs successfully.
     */
    public function test_read_only_viewer_can_view_index_and_logs_endpoints(): void
    {
        $this->actingAs($this->viewer)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('whatsapp.index'))
            ->assertOk();

        $this->actingAs($this->viewer)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('whatsapp.logs.index'))
            ->assertOk();

        $this->actingAs($this->viewer)
            ->withSession(['active_business_id' => $this->business->id])
            ->getJson(route('whatsapp.meta.config'))
            ->assertOk()
            ->assertJsonStructure(['config_id', 'app_id']);

        $this->actingAs($this->viewer)
            ->withSession(['active_business_id' => $this->business->id])
            ->getJson(route('whatsapp.meta.status'))
            ->assertOk()
            ->assertJsonStructure(['success', 'connected', 'account']);
    }

    /**
     * Read-only user receives HTTP 403 Forbidden when attempting manage actions.
     */
    public function test_read_only_viewer_receives_403_forbidden_when_attempting_manage_actions(): void
    {
        // Broadcast index requires whatsapp.manage
        $this->actingAs($this->viewer)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('whatsapp.broadcast.index'))
            ->assertRedirect(route('portal'));

        $this->actingAs($this->viewer)
            ->withSession(['active_business_id' => $this->business->id])
            ->getJson(route('whatsapp.broadcast.index'))
            ->assertForbidden()
            ->assertJsonFragment(['error_code' => 'FORBIDDEN_PERMISSION']);

        // Mutating actions require whatsapp.manage
        $this->actingAs($this->viewer)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('whatsapp.test'), [
                'phone'   => '081234567890',
                'message' => 'Tes pesan dari viewer',
            ])
            ->assertForbidden()
            ->assertJsonFragment(['error_code' => 'FORBIDDEN_PERMISSION']);

        $this->actingAs($this->viewer)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('whatsapp.disconnect'))
            ->assertForbidden()
            ->assertJsonFragment(['error_code' => 'FORBIDDEN_PERMISSION']);

        $this->actingAs($this->viewer)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('whatsapp.meta.disconnect'))
            ->assertForbidden()
            ->assertJsonFragment(['error_code' => 'FORBIDDEN_PERMISSION']);

        $this->actingAs($this->viewer)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('whatsapp.meta.exchange-code'), [
                'code' => 'sample_authorization_code_123',
            ])
            ->assertForbidden()
            ->assertJsonFragment(['error_code' => 'FORBIDDEN_PERMISSION']);

        $this->actingAs($this->viewer)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('whatsapp.broadcast.store'), [
                'title'         => 'Siaran Ilegal Viewer',
                'message'       => 'Pesan blast tidak diizinkan',
                'target_filter' => 'all',
            ])
            ->assertForbidden()
            ->assertJsonFragment(['error_code' => 'FORBIDDEN_PERMISSION']);

        $this->actingAs($this->viewer)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('whatsapp.settings'), [
                'receipt_enabled' => true,
            ])
            ->assertForbidden()
            ->assertJsonFragment(['error_code' => 'FORBIDDEN_PERMISSION']);
    }

    /**
     * Read-only user cannot trigger Excel export without reports.export permission.
     */
    public function test_read_only_viewer_forbidden_on_logs_export_without_reports_export_perm(): void
    {
        $this->actingAs($this->viewer)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('whatsapp.logs.export'))
            ->assertRedirect(route('portal'));

        $this->actingAs($this->viewer)
            ->withSession(['active_business_id' => $this->business->id])
            ->getJson(route('whatsapp.logs.export'))
            ->assertForbidden()
            ->assertJsonFragment(['error_code' => 'FORBIDDEN_PERMISSION']);
    }

    /**
     * Read-only user UI hides restricted action buttons and renders disabled controls.
     */
    public function test_read_only_viewer_ui_hides_restricted_action_buttons_and_disables_controls(): void
    {
        $this->createWhatsAppAccount('active');
        app()->setLocale('id');

        // 1. WhatsApp Index View
        $responseIndex = $this->actingAs($this->viewer)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('whatsapp.index'));

        $responseIndex->assertOk();
        // Mutating action buttons must be hidden via @can('whatsapp.manage')
        $responseIndex->assertDontSee('@click="disconnectMeta()"', false);
        $responseIndex->assertDontSee(__('whatsapp.save_receipt_settings_btn'));
        $responseIndex->assertDontSee(__('whatsapp.test_send_btn'));
        // Form controls must have disabled attribute
        $responseIndex->assertSee('disabled', false);

        // 2. WhatsApp Logs View
        $responseLogs = $this->actingAs($this->viewer)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('whatsapp.logs.index'));

        $responseLogs->assertOk();
        // Storage pruning button must be hidden via @can('whatsapp.manage')
        $responseLogs->assertDontSee(__('whatsapp.storage_pruning_btn'));
        // Excel export link must be hidden via @can('reports.export')
        $responseLogs->assertDontSee(route('whatsapp.logs.export'), false);
    }

    /**
     * Disconnected state hides Embedded Signup connect button for read-only user.
     */
    public function test_disconnected_view_hides_connect_button_for_viewer_but_visible_for_owner(): void
    {
        app()->setLocale('id');

        // Viewer should NOT see the connect button
        $responseViewer = $this->actingAs($this->viewer)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('whatsapp.index'));
        $responseViewer->assertOk();
        $responseViewer->assertDontSee('@click="launchEmbeddedSignup()"', false);

        // Owner MUST see the connect button
        $responseOwner = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('whatsapp.index'));
        $responseOwner->assertOk();
        $responseOwner->assertSee('@click="launchEmbeddedSignup()"', false);
    }

    /**
     * Privileged owner can view all pages and see all action buttons.
     */
    public function test_privileged_owner_can_access_all_pages_and_see_action_buttons(): void
    {
        $this->createWhatsAppAccount('active');
        app()->setLocale('id');

        // 1. Index
        $resIndex = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('whatsapp.index'));
        $resIndex->assertOk();
        $resIndex->assertSee('@click="disconnectMeta()"', false);
        $resIndex->assertSee(__('whatsapp.save_receipt_settings_btn'));
        $resIndex->assertSee(__('whatsapp.test_send_btn'));

        // 2. Broadcast Index
        $resBroadcast = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('whatsapp.broadcast.index'));
        $resBroadcast->assertOk();
        $resBroadcast->assertSee(__('whatsapp.new_broadcast_btn'));

        // 3. Logs Index
        $resLogs = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('whatsapp.logs.index'));
        $resLogs->assertOk();
        $resLogs->assertSee(__('whatsapp.storage_pruning_btn'));
        $resLogs->assertSee(__('whatsapp.export_excel'));
    }

    /**
     * Privileged owner can execute mutating actions successfully.
     */
    public function test_privileged_owner_can_execute_mutating_actions_successfully(): void
    {
        $account = $this->createWhatsAppAccount('active');
        Queue::fake();

        $gatewayMock = $this->mock(WhatsAppGatewayService::class);
        $gatewayMock->shouldReceive('sendMessage')
            ->once()
            ->andReturn(['success' => true]);

        // 1. Test Send
        $resTest = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('whatsapp.test'), [
                'phone'   => '081234567890',
                'message' => 'Tes pesan uji coba owner',
            ]);
        $resTest->assertOk();
        $resTest->assertJson(['success' => true]);

        // 2. Broadcast Store (while account is active)
        $resBroadcast = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->post(route('whatsapp.broadcast.store'), [
                'title'         => 'Promo Spesial Owner',
                'message'       => 'Halo {nama}, nikmati penawaran eksklusif di {bisnis}!',
                'target_filter' => 'all',
            ]);
        $resBroadcast->assertRedirect();
        $campaign = WhatsAppBroadcastCampaign::where('business_id', $this->business->id)->first();
        $this->assertNotNull($campaign);
        $this->assertSame('Promo Spesial Owner', $campaign->title);

        // 3. Disconnect Meta
        $resDisconnect = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('whatsapp.meta.disconnect'));
        $resDisconnect->assertOk();
        $resDisconnect->assertJson(['success' => true]);
        $this->assertSame('disconnected', $account->fresh()->status);
    }

    /**
     * Privileged manager with whatsapp.manage can access pages and execute mutating actions.
     */
    public function test_manager_with_manage_perm_can_access_pages_and_execute_actions(): void
    {
        $this->createWhatsAppAccount('active');
        Queue::fake();

        $gatewayMock = $this->mock(WhatsAppGatewayService::class);
        $gatewayMock->shouldReceive('sendMessage')
            ->once()
            ->andReturn(['success' => true]);

        // 1. Can view index and see manage actions
        $this->actingAs($this->manager)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('whatsapp.index'))
            ->assertOk()
            ->assertSee(__('whatsapp.test_send_btn'));

        // 2. Can view broadcast index and see create CTA
        $this->actingAs($this->manager)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('whatsapp.broadcast.index'))
            ->assertOk()
            ->assertSee(__('whatsapp.new_broadcast_btn'));

        // 3. Can view logs and see storage pruning
        $this->actingAs($this->manager)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('whatsapp.logs.index'))
            ->assertOk()
            ->assertSee(__('whatsapp.storage_pruning_btn'));

        // 4. Can execute test send
        $resTest = $this->actingAs($this->manager)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('whatsapp.test'), [
                'phone'   => '081234567890',
                'message' => 'Tes pengiriman manager',
            ]);
        $resTest->assertOk();
        $resTest->assertJson(['success' => true]);

        // 5. Can store broadcast campaign
        $resBroadcast = $this->actingAs($this->manager)
            ->withSession(['active_business_id' => $this->business->id])
            ->post(route('whatsapp.broadcast.store'), [
                'title'         => 'Siaran Promosi Manager',
                'message'       => 'Halo {nama}, promo weekend dari Manager!',
                'target_filter' => 'all',
            ]);
        $resBroadcast->assertRedirect();
        $campaign = WhatsAppBroadcastCampaign::where('title', 'Siaran Promosi Manager')->first();
        $this->assertNotNull($campaign);
    }

    /**
     * Blade views compile and render without missing variable exceptions in both ID and EN locales.
     */
    public function test_blade_views_render_without_missing_variables_for_owner_and_viewer_in_both_locales(): void
    {
        $this->createWhatsAppAccount('active');

        foreach (['id', 'en'] as $locale) {
            app()->setLocale($locale);

            // Owner views
            $this->actingAs($this->owner)
                ->withSession(['active_business_id' => $this->business->id])
                ->get(route('whatsapp.index'))
                ->assertOk();

            $this->actingAs($this->owner)
                ->withSession(['active_business_id' => $this->business->id])
                ->get(route('whatsapp.broadcast.index'))
                ->assertOk();

            $this->actingAs($this->owner)
                ->withSession(['active_business_id' => $this->business->id])
                ->get(route('whatsapp.logs.index'))
                ->assertOk();

            // Viewer views
            $this->actingAs($this->viewer)
                ->withSession(['active_business_id' => $this->business->id])
                ->get(route('whatsapp.index'))
                ->assertOk();

            $this->actingAs($this->viewer)
                ->withSession(['active_business_id' => $this->business->id])
                ->get(route('whatsapp.logs.index'))
                ->assertOk();
        }
    }
}
