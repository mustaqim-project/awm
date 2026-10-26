<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Billing\EntitlementService;
use App\Models\AuditLog;
use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\Location;
use App\Models\McpAccessToken;
use App\Models\Role;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class SettingsModuleAuditTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $owner;
    private User $staffWithoutPerms;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RbacSeeder::class);

        $this->owner = User::create([
            'name' => 'Owner Settings',
            'email' => 'owner.settings@cooca.test',
            'phone' => '6281299993333',
            'password' => 'password',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);

        $this->business = Business::create([
            'name' => 'PT Makmur Sentosa Bersama',
            'slug' => 'pt-makmur-sentosa-bersama',
            'currency' => 'IDR',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'business_scale' => Business::SCALE_CORPORATE,
            'disabled_modules' => [],
        ]);

        $this->business->users()->attach($this->owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);
        $this->owner->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);

        $entitlement = app(EntitlementService::class);
        $sub = $entitlement->getSubscription($this->business);
        $sub->update([
            'plan_code' => BusinessSubscription::PLAN_PREMIUM_MONTHLY,
            'status' => BusinessSubscription::STATUS_ACTIVE,
        ]);

        $this->staffWithoutPerms = User::create([
            'name' => 'Staf Tanpa Izin Pengaturan',
            'email' => 'staf.settings@cooca.test',
            'phone' => '6281299994444',
            'password' => 'password',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);

        $customRole = Role::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'name' => 'Kasir Magang',
            'slug' => 'kasir-magang',
        ]);

        $this->business->users()->attach($this->staffWithoutPerms->id, [
            'id' => (string) Str::uuid(),
            'role' => 'staff',
            'role_id' => $customRole->id,
        ]);
        $this->staffWithoutPerms->update(['active_business_id' => $this->business->id]);
    }

    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $response = $this->get(route('settings.index'));
        $response->assertRedirect(route('login'));

        $mcpResponse = $this->get(route('settings.integrations.mcp.index'));
        $mcpResponse->assertRedirect(route('login'));
    }

    public function test_staff_without_settings_view_permission_is_forbidden_403(): void
    {
        $response = $this->actingAs($this->staffWithoutPerms)->get(route('settings.index'));
        $response->assertRedirect(route('portal'));

        $jsonResponse = $this->actingAs($this->staffWithoutPerms)->getJson(route('settings.index'));
        $jsonResponse->assertStatus(403);

        $mcpResponse = $this->actingAs($this->staffWithoutPerms)->get(route('settings.integrations.mcp.index'));
        $mcpResponse->assertRedirect(route('portal'));

        $mcpJsonResponse = $this->actingAs($this->staffWithoutPerms)->getJson(route('settings.integrations.mcp.index'));
        $mcpJsonResponse->assertStatus(403);
    }

    public function test_owner_can_access_settings_index_successfully(): void
    {
        $response = $this->actingAs($this->owner)->get(route('settings.index'));
        $response->assertStatus(200);
        $response->assertViewIs('app.settings.index');
        $response->assertViewHasAll(['business', 'templates', 'currencies', 'branches', 'allModules']);
    }

    public function test_settings_update_saves_company_profile_and_logs_audit(): void
    {
        $payload = [
            'name' => 'PT Makmur Sentosa Reborn',
            'phone' => '6281299998888',
            'email' => 'contact@makmursentosa.test',
            'address' => 'Gedung Cyber Lt 12, Jl HR Rasuna Said, Jakarta',
            'pos_sales_tax_enabled' => '1',
            'pos_sales_tax_rate' => '11',
            'cogs_rounding_method' => 'round_100',
        ];

        $response = $this->actingAs($this->owner)->put(route('settings.update'), $payload);
        $response->assertRedirect(route('settings.index', ['tab' => 'general']));

        $this->assertDatabaseHas('businesses', [
            'id' => $this->business->id,
            'name' => 'PT Makmur Sentosa Reborn',
            'phone' => '6281299998888',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $this->business->id,
            'user_id' => $this->owner->id,
            'action' => 'updated',
        ]);
    }

    public function test_settings_update_sensitive_fields_logs_high_risk_audit(): void
    {
        $payload = [
            'name' => $this->business->name,
            'pos_supervisor_pin' => '889900',
            'bank_account_number' => '1234567890',
        ];

        $response = $this->actingAs($this->owner)->put(route('settings.update'), $payload);
        $response->assertRedirect(route('settings.index', ['tab' => 'general']));

        $audit = AuditLog::where('business_id', $this->business->id)
            ->where('action', 'settings.security_updated')
            ->latest('id')
            ->first();

        $this->assertNotNull($audit);
        $this->assertEquals(AuditLog::RISK_HIGH, $audit->risk_level);
    }

    public function test_store_branch_creates_location_and_logs_audit(): void
    {
        $payload = [
            'name' => 'Cabang Tebet Jakarta',
            'type' => 'outlet',
            'address' => 'Jl. Tebet Raya No. 10, Jakarta Selatan',
            'is_primary' => '0',
            'is_online_fulfillment' => '1',
            'allow_storefront_pickup' => '1',
            'latitude' => '-6.2255',
            'longitude' => '106.8550',
            'is_active' => '1',
        ];

        $response = $this->actingAs($this->owner)->post(route('settings.branches.store'), $payload);
        $response->assertRedirect(route('settings.index', ['tab' => 'branches']));

        $this->assertDatabaseHas('locations', [
            'business_id' => $this->business->id,
            'name' => 'Cabang Tebet Jakarta',
            'is_online_fulfillment' => 1,
            'allow_storefront_pickup' => 1,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $this->business->id,
            'action' => 'settings.branch_created',
        ]);
    }

    public function test_update_branch_modifies_location_and_logs_audit(): void
    {
        $location = Location::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'name' => 'Cabang Awal',
            'type' => 'outlet',
            'is_active' => true,
        ]);

        $payload = [
            'name' => 'Cabang Awal Diperbarui',
            'type' => 'outlet',
            'address' => 'Jl. Margonda Raya No. 50, Depok',
            'is_primary' => '0',
            'is_online_fulfillment' => '0',
            'allow_storefront_pickup' => '1',
            'is_active' => '1',
        ];

        $response = $this->actingAs($this->owner)->put(route('settings.branches.update', $location), $payload);
        $response->assertRedirect(route('settings.index', ['tab' => 'branches']));

        $this->assertDatabaseHas('locations', [
            'id' => $location->id,
            'name' => 'Cabang Awal Diperbarui',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $this->business->id,
            'action' => 'settings.branch_updated',
        ]);
    }

    public function test_destroy_branch_removes_or_deactivates_and_logs_audit(): void
    {
        $location = Location::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'name' => 'Cabang Hapus',
            'type' => 'outlet',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->owner)->delete(route('settings.branches.destroy', $location));
        $response->assertRedirect(route('settings.index', ['tab' => 'branches']));

        $this->assertDatabaseMissing('locations', [
            'id' => $location->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $this->business->id,
            'action' => 'settings.branch_deleted',
            'risk_level' => AuditLog::RISK_HIGH,
        ]);
    }

    public function test_mcp_cockpit_viewable_by_authorized_user(): void
    {
        $response = $this->actingAs($this->owner)->get(route('settings.integrations.mcp.index'));
        $response->assertStatus(200);
        $response->assertViewIs('app.settings.integrations.mcp');
        $response->assertViewHasAll(['tokens', 'isMcpActive', 'tools', 'logs']);
    }

    public function test_mcp_token_creation_stores_token_and_logs_audit(): void
    {
        $payload = [
            'name' => 'Gemini Assistant Production',
            'provider_hint' => 'gemini',
            'expires_in' => 'never',
            'abilities' => ['mcp:finance:read', 'mcp:products:read'],
        ];

        $response = $this->actingAs($this->owner)->post(route('settings.integrations.mcp.tokens.store'), $payload);
        $response->assertRedirect(route('settings.integrations.mcp.index'));
        $response->assertSessionHas('plain_token');

        $this->assertDatabaseHas('mcp_access_tokens', [
            'business_id' => $this->business->id,
            'name' => 'Gemini Assistant Production',
            'provider_hint' => 'gemini',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $this->business->id,
            'action' => 'mcp.token_created',
        ]);
    }

    public function test_mcp_token_update_modifies_abilities_and_logs_audit(): void
    {
        $token = McpAccessToken::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'user_id' => $this->owner->id,
            'name' => 'Token Claude',
            'token_hash' => hash('sha256', 'plain_test_token'),
            'abilities' => ['mcp:finance:read'],
            'is_active' => true,
            'provider_hint' => 'claude',
        ]);

        $payload = [
            'name' => 'Token Claude Extended',
            'provider_hint' => 'claude',
            'is_active' => '1',
            'expires_in' => 'keep',
            'abilities' => ['mcp:finance:read', 'mcp:expenses:write'],
        ];

        $response = $this->actingAs($this->owner)->put(route('settings.integrations.mcp.tokens.update', $token), $payload);
        $response->assertRedirect(route('settings.integrations.mcp.index'));

        $this->assertDatabaseHas('mcp_access_tokens', [
            'id' => $token->id,
            'name' => 'Token Claude Extended',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $this->business->id,
            'action' => 'mcp.token_updated',
        ]);
    }

    public function test_mcp_token_revocation_deletes_token_and_logs_audit(): void
    {
        $token = McpAccessToken::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'user_id' => $this->owner->id,
            'name' => 'Token Mau Dihapus',
            'token_hash' => hash('sha256', 'plain_test_token_del'),
            'abilities' => ['*'],
            'is_active' => true,
            'provider_hint' => 'openai',
        ]);

        $response = $this->actingAs($this->owner)->delete(route('settings.integrations.mcp.tokens.revoke', $token));
        $response->assertRedirect(route('settings.integrations.mcp.index'));

        $this->assertDatabaseMissing('mcp_access_tokens', [
            'id' => $token->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $this->business->id,
            'action' => 'mcp.token_revoked',
        ]);
    }

    public function test_mcp_toggle_master_switches_all_tokens_and_logs_audit(): void
    {
        McpAccessToken::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'user_id' => $this->owner->id,
            'name' => 'Token 1',
            'token_hash' => hash('sha256', 'token_1'),
            'abilities' => ['*'],
            'is_active' => true,
        ]);

        McpAccessToken::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'user_id' => $this->owner->id,
            'name' => 'Token 2',
            'token_hash' => hash('sha256', 'token_2'),
            'abilities' => ['*'],
            'is_active' => true,
        ]);

        // Toggle master to inactive
        $response = $this->actingAs($this->owner)->post(route('settings.integrations.mcp.toggle'), [
            'is_active' => '0',
        ]);
        $response->assertRedirect(route('settings.integrations.mcp.index'));

        $this->assertEquals(0, McpAccessToken::where('business_id', $this->business->id)->where('is_active', true)->count());

        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $this->business->id,
            'action' => 'mcp.master_toggle',
        ]);
    }

    public function test_mcp_blade_contains_zero_unicode_emojis_and_only_allowed_providers(): void
    {
        $bladePath = resource_path('views/app/settings/integrations/mcp.blade.php');
        $content = file_get_contents($bladePath);

        // Check for unicode emoji ranges
        $emojiPattern = '/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F1E0}-\x{1F1FF}\x{2702}-\x{27B0}\x{24C2}-\x{1F251}\x{1F900}-\x{1F9FF}\x{1FA70}-\x{1FAFF}]/u';
        preg_match_all($emojiPattern, $content, $matches);
        $this->assertEmpty($matches[0], 'Found unicode emojis in mcp.blade.php which violates Zero Unicode Emoji policy');

        // Check that non-allowed providers were purged from tab navigation
        $this->assertStringNotContainsString("activeProviderTab = 'cursor'", $content);
        $this->assertStringNotContainsString("activeProviderTab = 'ollama'", $content);
        $this->assertStringNotContainsString("activeProviderTab = 'n8n'", $content);
        $this->assertStringNotContainsString("activeProviderTab = 'langchain'", $content);
        $this->assertStringNotContainsString("activeProviderTab = 'dola'", $content);

        // Allowed providers are present
        $this->assertStringContainsString("activeProviderTab = 'claude'", $content);
        $this->assertStringContainsString("activeProviderTab = 'chatgpt'", $content);
        $this->assertStringContainsString("activeProviderTab = 'gemini'", $content);
    }

    public function test_all_translation_keys_are_present_in_id_and_en(): void
    {
        $settingsDir = resource_path('views/app/settings');
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($settingsDir));

        $missingId = [];
        $missingEn = [];

        foreach ($files as $file) {
            if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
                $content = file_get_contents($file->getPathname());
                preg_match_all("/__\(['\"]([a-zA-Z0-9_\-\.]+)['\"]/", $content, $matches);
                if (!empty($matches[1])) {
                    foreach ($matches[1] as $k) {
                        if (trans($k, [], 'id') === $k) {
                            $missingId[] = "$k in {$file->getFilename()}";
                        }
                        if (trans($k, [], 'en') === $k) {
                            $missingEn[] = "$k in {$file->getFilename()}";
                        }
                    }
                }
            }
        }

        $this->assertEmpty($missingId, 'Missing ID translations: ' . implode(', ', array_unique($missingId)));
        $this->assertEmpty($missingEn, 'Missing EN translations: ' . implode(', ', array_unique($missingEn)));
    }
}
