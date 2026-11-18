<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Mcp\Protocol\McpProtocolEngine;
use App\Domain\Mcp\Tools\McpToolRegistry;
use App\Domain\Mcp\Tools\SettingsManageBusinessTool;
use App\Models\AuditLog;
use App\Models\Business;
use App\Models\McpAccessToken;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class McpSettingsUpdateDataTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $user;
    private McpToolRegistry $registry;
    private McpProtocolEngine $engine;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->user = User::create([
            'name' => 'Owner Settings Tester',
            'email' => 'owner_settings@example.com',
            'password' => 'secret123',
        ]);

        $this->business = Business::create([
            'name' => 'Warung Kopi Nusantara',
            'phone' => '08111222333',
            'email' => 'halo@kopinusantara.id',
            'address' => 'Jl. Veteran No. 10',
            'tax_percentage' => 11,
            'receipt_footer_note' => 'Terima kasih telah berkunjung!',
        ]);

        $this->business->users()->attach($this->user->id, [
            'id' => Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);
        $this->user->update(['active_business_id' => $this->business->id]);

        $this->actingAs($this->user);
        session(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);

        $this->registry = app(McpToolRegistry::class);
        $this->engine = app(McpProtocolEngine::class);
    }

    public function test_settings_manage_business_tool_is_registered_with_correct_metadata(): void
    {
        $tool = $this->registry->getTool('settings_manage_business');
        $this->assertInstanceOf(SettingsManageBusinessTool::class, $tool);
        $this->assertEquals('settings_manage_business', $tool->getName());
        $this->assertEquals(['mcp:settings:read', 'mcp:settings:update'], $tool->getRequiredAbilities());

        $schema = $tool->getParameterSchema();
        $this->assertEquals('object', $schema['type']);
        $this->assertArrayHasKey('action', $schema['properties']);
        $this->assertContains('update', $schema['properties']['action']['enum']);
        $this->assertArrayHasKey('pos_supervisor_pin', $schema['properties']);
        $this->assertArrayHasKey('tax_percentage', $schema['properties']);
        $this->assertArrayHasKey('bank_name', $schema['properties']);
        $this->assertArrayHasKey('operating_hours', $schema['properties']);
        $this->assertArrayHasKey('enable_module', $schema['properties']);
        $this->assertArrayHasKey('disable_module', $schema['properties']);
    }

    public function test_settings_manage_business_get_action(): void
    {
        $tool = $this->registry->getTool('settings_manage_business');
        $result = $tool->execute($this->business, $this->user, ['action' => 'get']);

        $this->assertTrue($result['success']);
        $this->assertEquals('Warung Kopi Nusantara', $result['business']['name']);
        $this->assertEquals(11, $result['business']['tax']['tax_percent']);
        $this->assertEquals('Terima kasih telah berkunjung!', $result['business']['receipt']['receipt_footer_note']);
        $this->assertArrayHasKey('profile', $result['business']);
        $this->assertArrayHasKey('bank_accounts', $result['business']);
        $this->assertArrayHasKey('pos', $result['business']);
        $this->assertArrayHasKey('timezone', $result['business']);
        $this->assertArrayHasKey('modules', $result['business']);
    }

    public function test_settings_manage_business_update_action_updates_all_fields(): void
    {
        $tool = $this->registry->getTool('settings_manage_business');

        $payload = [
            'action' => 'update',
            'name' => 'Kopi Nusantara Modern',
            'phone' => '081234567899',
            'email' => 'kontak@kopimodern.id',
            'description' => 'Kedai Kopi Spesialis Espresso & Pastry',
            'address' => 'Jl. Malioboro No. 45',
            'city' => 'Yogyakarta',
            'province' => 'DI Yogyakarta',
            'postal_code' => '55271',
            'official_whatsapp' => '081234567899',

            // Legal & Tax
            'tax_identification_number' => '01.234.567.8-901.000',
            'tax_entity_type' => 'pt',
            'tax_percentage' => 12.0,
            'pos_enable_tax' => true,

            // Bank Accounts
            'bank_name' => 'Bank Mandiri',
            'bank_account_number' => '1370012345678',
            'bank_account_holder' => 'PT Kopi Nusantara Modern',

            // POS & Cashier
            'pos_supervisor_pin' => '889900',
            'pos_max_cashier_discount_percent' => 20.0,
            'pos_require_pin_for_void' => true,
            'pos_require_pin_for_refund' => true,
            'pos_auto_send_kds' => true,
            'pos_show_product_images' => true,

            // Receipt
            'receipt_header' => 'KOPI NUSANTARA MODERN',
            'receipt_footer_note' => 'Terima kasih atas kunjungan Anda!',
            'auto_send_receipt' => true,
            'pos_receipt_wa_template' => 'Terima kasih *{{customer_name}}*, struk pesanan: {{receipt_url}}',

            // Timezone & Operating Hours
            'timezone' => 'Asia/Jakarta',
            'operating_hours' => [
                'monday' => ['open' => '08:00', 'close' => '22:00', 'is_closed' => false],
                'sunday' => ['open' => '09:00', 'close' => '23:00', 'is_closed' => false],
            ],

            // Modules
            'disable_module' => 'hrm',
        ];

        $result = $tool->execute($this->business, $this->user, $payload);

        $this->assertTrue($result['success']);
        $this->assertEquals('Kopi Nusantara Modern', $result['business']['name']);
        $this->assertEquals(12.0, $result['business']['tax']['tax_percent']);
        $this->assertTrue($result['business']['tax']['pos_enable_tax']);
        $this->assertEquals('Bank Mandiri', $result['business']['bank_accounts']['bank_name']);
        $this->assertEquals('1370012345678', $result['business']['bank_accounts']['bank_account_number']);
        $this->assertEquals(20.0, $result['business']['pos']['max_cashier_discount_percent']);
        $this->assertTrue($result['business']['pos']['require_pin_for_void']);
        $this->assertTrue($result['business']['pos']['auto_send_kds']);
        $this->assertContains('hrm', $result['business']['modules']['disabled_modules']);

        // Verify changes persisted to Database
        $this->business->refresh();
        $this->assertEquals('Kopi Nusantara Modern', $this->business->name);
        $this->assertEquals(12.0, (float) $this->business->tax_percentage);
        $this->assertEquals('Bank Mandiri', $this->business->bank_name);
        $this->assertEquals('1370012345678', $this->business->bank_account_number);
        $this->assertEquals('889900', $this->business->pos_supervisor_pin);
        $this->assertTrue((bool) $this->business->pos_require_pin_for_void);
        $this->assertEquals('081234567899', $this->business->official_whatsapp);

        // Verify Audit Logs
        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $this->business->id,
            'action' => 'mcp_settings_updated',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $this->business->id,
            'action' => 'settings.security_updated',
        ]);
    }

    public function test_protocol_engine_blocks_update_when_token_lacks_settings_update_ability(): void
    {
        // 1. Create a token with READ-ONLY scope (mcp:settings:read)
        $readToken = McpAccessToken::create([
            'business_id' => $this->business->id,
            'user_id' => $this->user->id,
            'name' => 'Read Only Settings Assistant',
            'token_hash' => hash('sha256', 'cooca_mcp_live_readonly_test_token'),
            'abilities' => ['mcp:settings:read'],
            'is_active' => true,
        ]);

        // Executing 'get' with readToken MUST SUCCEED
        $getRequest = [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'tools/call',
            'params' => [
                'name' => 'settings_manage_business',
                'arguments' => ['action' => 'get'],
            ],
        ];
        $getResponse = $this->engine->handleRequest($getRequest, $readToken);
        $this->assertArrayNotHasKey('error', $getResponse);
        $this->assertArrayHasKey('result', $getResponse);
        $content = json_decode($getResponse['result']['content'][0]['text'], true);
        $this->assertTrue($content['success']);

        // Executing 'update' with readToken MUST BE REJECTED with -32001
        $updateRequest = [
            'jsonrpc' => '2.0',
            'id' => 2,
            'method' => 'tools/call',
            'params' => [
                'name' => 'settings_manage_business',
                'arguments' => [
                    'action' => 'update',
                    'tax_percentage' => 15,
                ],
            ],
        ];
        $updateResponse = $this->engine->handleRequest($updateRequest, $readToken);
        $this->assertArrayHasKey('error', $updateResponse);
        $this->assertEquals(-32001, $updateResponse['error']['code']);
        $this->assertStringContainsString('mcp:settings:update', $updateResponse['error']['message']);

        // 2. Create a token with UPDATE scope (mcp:settings:update)
        $updateToken = McpAccessToken::create([
            'business_id' => $this->business->id,
            'user_id' => $this->user->id,
            'name' => 'Full Update Assistant',
            'token_hash' => hash('sha256', 'cooca_mcp_live_update_test_token'),
            'abilities' => ['mcp:settings:update', 'mcp:settings:read'],
            'is_active' => true,
        ]);

        $validUpdateResponse = $this->engine->handleRequest($updateRequest, $updateToken);
        $this->assertArrayNotHasKey('error', $validUpdateResponse);
        $this->assertArrayHasKey('result', $validUpdateResponse);
        $validContent = json_decode($validUpdateResponse['result']['content'][0]['text'], true);
        $this->assertTrue($validContent['success']);
        $this->assertEquals(15, $validContent['business']['tax']['tax_percent']);
    }

    public function test_mcp_settings_view_and_cockpit_contain_update_data_capabilities(): void
    {
        $response = $this->get('/settings/integrations/mcp');
        $response->assertStatus(200);

        // Verify tools catalog has settings_manage_business tool card
        $response->assertSee('settings_manage_business');
        $response->assertSee('mcp:settings:update');

        // Verify zero unicode emojis in response
        $content = $response->getContent();
        $this->assertFalse(
            (bool) preg_match('/[\x{1F300}-\x{1F9FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u', $content),
            'Halaman MCP integrasi tidak boleh mengandung emoji Unicode visual.'
        );

        // Verify settings main page has the fast link button to MCP cockpit
        $settingsResponse = $this->get('/settings');
        $settingsResponse->assertStatus(200);
        $settingsResponse->assertSee('integrations/mcp');
    }
}
