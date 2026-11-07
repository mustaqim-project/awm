<?php

declare(strict_types=1);

namespace Tests\Feature\WhatsApp;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Location;
use App\Models\Role;
use App\Models\User;
use App\Models\WhatsAppAccount;
use App\Models\WhatsAppBroadcastCampaign;
use App\Models\WhatsAppBroadcastRecipient;
use App\Models\WhatsAppMessageLog;
use App\Support\Context;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Tests\TestCase;

class WhatsAppChallengerAdversarialTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $owner;
    private User $viewer;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->seed(RbacSeeder::class);

        Config::set('services.meta_whatsapp.app_id', '123456789012345');
        Config::set('services.meta_whatsapp.app_secret', 'test_meta_app_secret_12345');
        Config::set('services.meta_whatsapp.config_id', 'test_embedded_config_id_99');
        Config::set('services.meta_whatsapp.webhook_verify_token', 'test_verify_token_secure');

        $this->owner = User::factory()->create([
            'name'  => 'Owner Challenger',
            'email' => 'owner.challenger@example.test',
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

        $roleViewer = Role::where('slug', 'viewer')->first();
        $this->viewer = User::factory()->create([
            'name'  => 'Viewer Challenger',
            'email' => 'viewer.challenger@example.test',
        ]);
        $this->business->users()->attach($this->viewer->id, [
            'id'      => (string) Str::uuid(),
            'role'    => 'viewer',
            'role_id' => $roleViewer?->id,
        ]);
        $this->viewer->update(['active_business_id' => $this->business->id]);
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
     * EMPIRICAL CHALLENGE 1: Detect missing translation keys (e.g. "whatsapp.", "social_media.")
     * in rendered HTML for both locales ('id' and 'en') across all 4 views.
     */
    public function test_adversarial_check_for_untranslated_keys_in_rendered_html(): void
    {
        $this->createWhatsAppAccount('active');

        // Create campaign and recipients
        $campaign = WhatsAppBroadcastCampaign::create([
            'business_id'      => $this->business->id,
            'title'            => 'Diskon Ramadhan',
            'message'          => 'Halo {nama}, promo kopi 50%!',
            'target_filter'    => 'all',
            'total_recipients' => 2,
            'total_sent'       => 1,
            'total_failed'     => 1,
            'status'           => 'completed',
        ]);

        WhatsAppBroadcastRecipient::create([
            'campaign_id'   => $campaign->id,
            'customer_name' => 'Budi Santoso',
            'phone_number'  => '081234567890',
            'status'        => 'sent',
            'sent_at'       => now(),
        ]);

        WhatsAppBroadcastRecipient::create([
            'campaign_id'   => $campaign->id,
            'customer_name' => 'Siti Aminah',
            'phone_number'  => '089876543210',
            'status'        => 'failed',
            'error_message' => 'Invalid phone number format',
        ]);

        // Create message logs
        WhatsAppMessageLog::create([
            'business_id'     => $this->business->id,
            'recipient_name'  => 'Pelanggan Setia',
            'recipient_phone' => '08111222333',
            'type'            => 'receipt',
            'message'         => 'Struk belanja Rp 50.000',
            'status'          => 'sent',
        ]);

        WhatsAppMessageLog::create([
            'business_id'     => $this->business->id,
            'recipient_name'  => 'Pelanggan Gagal',
            'recipient_phone' => '08555666777',
            'type'            => 'broadcast',
            'message'         => 'Pesan promosi blast',
            'status'          => 'failed',
            'error_message'   => 'Rate limit exceeded',
        ]);

        $routesToTest = [
            'whatsapp.index' => fn() => route('whatsapp.index'),
            'whatsapp.broadcast.index' => fn() => route('whatsapp.broadcast.index'),
            'whatsapp.broadcast.show' => fn() => route('whatsapp.broadcast.show', $campaign),
            'whatsapp.logs.index' => fn() => route('whatsapp.logs.index'),
        ];

        $untranslatedFindings = [];

        foreach (['id', 'en'] as $locale) {
            app()->setLocale($locale);

            foreach (['owner' => $this->owner, 'viewer' => $this->viewer] as $roleName => $user) {
                foreach ($routesToTest as $routeName => $routeGenerator) {
                    // Viewer doesn't have access to broadcast routes
                    if ($roleName === 'viewer' && str_starts_with($routeName, 'whatsapp.broadcast')) {
                        continue;
                    }

                    $response = $this->actingAs($user)
                        ->withSession(['active_business_id' => $this->business->id])
                        ->get($routeGenerator());

                    $this->assertSame(200, $response->getStatusCode(), "Route {$routeName} failed with {$roleName} in {$locale}");
                    $html = $response->getContent();

                    // Search for untranslated keys from whatsapp domain
                    if (preg_match_all('/whatsapp\.[a-z0-9_]+/i', $html, $matches)) {
                        foreach ($matches[0] as $untranslatedKey) {
                            $untranslatedFindings[] = "[{$locale}][{$roleName}][{$routeName}] Untranslated key found: {$untranslatedKey}";
                        }
                    }
                }
            }
        }

        $this->assertEmpty($untranslatedFindings, "Found untranslated keys in rendered HTML:\n" . implode("\n", $untranslatedFindings));
    }

    /**
     * EMPIRICAL CHALLENGE 2: Inspect interactive controls for touch target compliance (>= 44px).
     */
    public function test_adversarial_check_for_touch_target_heights_on_interactive_elements(): void
    {
        $this->createWhatsAppAccount('active');

        $campaign = WhatsAppBroadcastCampaign::create([
            'business_id'      => $this->business->id,
            'title'            => 'Touch Target Campaign',
            'message'          => 'Target test',
            'target_filter'    => 'all',
            'total_recipients' => 1,
            'total_sent'       => 1,
            'total_failed'     => 0,
            'status'           => 'completed',
        ]);

        WhatsAppMessageLog::create([
            'business_id'     => $this->business->id,
            'recipient_name'  => 'Pelanggan Target',
            'recipient_phone' => '081234567890',
            'type'            => 'receipt',
            'message'         => 'Target test',
            'status'          => 'sent',
        ]);

        $routesToTest = [
            'whatsapp.index' => route('whatsapp.index'),
            'whatsapp.broadcast.index' => route('whatsapp.broadcast.index'),
            'whatsapp.broadcast.show' => route('whatsapp.broadcast.show', $campaign),
            'whatsapp.logs.index' => route('whatsapp.logs.index'),
        ];

        foreach ($routesToTest as $routeName => $url) {
            $response = $this->actingAs($this->owner)
                ->withSession(['active_business_id' => $this->business->id])
                ->get($url);

            $this->assertSame(200, $response->getStatusCode());
            $html = $response->getContent();

            // Check that all primary CTA buttons and icon action buttons define min-h-[44px]
            // Specifically, table eye buttons should have min-w-[44px] and min-h-[44px]
            if ($routeName === 'whatsapp.broadcast.index' || $routeName === 'whatsapp.logs.index') {
                $this->assertStringContainsString('min-w-[44px]', $html, "Table action buttons in {$routeName} should have min-w-[44px]");
                $this->assertStringContainsString('min-h-[44px]', $html, "Table action buttons in {$routeName} should have min-h-[44px]");
            }
        }
    }

    /**
     * EMPIRICAL CHALLENGE 3: Check edge cases for edge models, empty states, and location variations.
     */
    public function test_adversarial_check_empty_and_edge_states(): void
    {
        // 1. Disconnected account state
        $resDisconnected = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('whatsapp.index'));
        $resDisconnected->assertOk();
        $resDisconnected->assertSee(__('whatsapp.disconnected'));

        // 2. Multi-outlet location filtering test
        Location::create([
            'business_id' => $this->business->id,
            'name'        => 'Cabang Sudirman Premium',
            'type'        => 'branch',
        ]);

        $resBroadcastWithLocation = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('whatsapp.broadcast.index'));
        $resBroadcastWithLocation->assertOk();
        $resBroadcastWithLocation->assertSee('Cabang Sudirman Premium');

        // 3. Campaign with long title and media_url
        $campaignEdge = WhatsAppBroadcastCampaign::create([
            'business_id'      => $this->business->id,
            'title'            => 'Super Ultra Mega Long Campaign Title That Tests Layout Overflow And Collision In Mobile Viewport Without Breaking Container Boundaries',
            'message'          => str_repeat("Ini pesan broadcast panjang yang menguji batas tampilan pesan tanpa overflow horizontal. \n", 10),
            'media_url'        => 'https://example.com/banner-promo.jpg',
            'target_filter'    => 'all',
            'total_recipients' => 5000,
            'total_sent'       => 4950,
            'total_failed'     => 50,
            'status'           => 'processing',
        ]);

        $resDetailEdge = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('whatsapp.broadcast.show', $campaignEdge));
        $resDetailEdge->assertOk();
        $resDetailEdge->assertSee('https://example.com/banner-promo.jpg');
        $resDetailEdge->assertSee('Super Ultra Mega Long Campaign Title');
    }
}
