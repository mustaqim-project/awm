<?php

declare(strict_types=1);

namespace Tests\Feature\AiStudio;

use App\Models\Admin;
use App\Models\AiStudio\AiModelPricing;
use App\Models\AiStudio\AiModelRegistry;
use App\Models\Business;
use Database\Seeders\AdminRbacSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class AdminAiStudioPricingTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;
    private Business $business;
    private AiModelRegistry $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RbacSeeder::class);
        $this->seed(AdminRbacSeeder::class);

        $this->admin = Admin::create([
            'name' => 'Superadmin FinOps',
            'email' => 'superadmin@cooca.id',
            'password' => Hash::make('password123'),
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        $this->business = Business::create([
            'name' => 'Test Business',
            'ai_credits_balance' => 1000,
        ]);

        $this->model = AiModelRegistry::create([
            'provider' => 'openai',
            'model_name' => 'Fast Generation (GPT Image 2.5 Flare)',
            'exact_model_id' => 'gpt-image-2.5-flare',
            'is_default' => true,
            'is_active' => true,
        ]);

        AiModelPricing::create([
            'model_id' => $this->model->id,
            'version' => '1.0',
            'billing_mode' => 'token',
            'currency' => 'USD',
            'text_input_rate' => 5.0,
            'image_input_rate' => 8.0,
            'image_output_rate' => 30.0,
            'effective_at' => now(),
        ]);
    }

    public function test_admin_can_view_pricing_console_with_models(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.ai-studio.pricing.index'));

        $response->assertOk();
        $response->assertSee('gpt-image-2.5-flare');
        $response->assertSee('Fast Generation');
    }

    public function test_admin_can_toggle_model_active_status(): void
    {
        $this->assertTrue($this->model->is_active);

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.ai-studio.pricing.models.toggle', $this->model->id));

        $response->assertRedirect(route('admin.ai-studio.pricing.index'));

        $this->model->refresh();
        $this->assertFalse($this->model->is_active);

        // Toggle back to active
        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.ai-studio.pricing.models.toggle', $this->model->id));

        $this->model->refresh();
        $this->assertTrue($this->model->is_active);
    }

    public function test_admin_can_update_model_token_rates(): void
    {
        $payload = [
            'text_input_rate' => 6.5,
            'cached_text_rate' => 1.5,
            'image_input_rate' => 9.0,
            'image_output_rate' => 35.0,
        ];

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.ai-studio.pricing.models.update-rates', $this->model->id), $payload);

        $response->assertRedirect(route('admin.ai-studio.pricing.index'));

        $pricing = $this->model->refresh()->activePricing;
        $this->assertEquals(6.5, $pricing->text_input_rate);
        $this->assertEquals(1.5, $pricing->cached_text_rate);
        $this->assertEquals(9.0, $pricing->image_input_rate);
        $this->assertEquals(35.0, $pricing->image_output_rate);
    }

    public function test_admin_can_sync_exchange_rate(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.ai-studio.pricing.sync-exchange-rate'));

        $response->assertRedirect(route('admin.ai-studio.pricing.index'));
        $response->assertSessionHas('success');
    }
}

