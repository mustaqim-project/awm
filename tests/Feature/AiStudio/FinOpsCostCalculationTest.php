<?php

declare(strict_types=1);

namespace Tests\Feature\AiStudio;

use App\Domain\Ai\Studio\Billing\CostQuoteService;
use App\Domain\Currency\ExchangeRateSyncService;
use App\Models\AiStudio\AiModelPricing;
use App\Models\AiStudio\AiModelRegistry;
use App\Models\Business;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class FinOpsCostCalculationTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private CostQuoteService $quoteService;
    private ExchangeRateSyncService $exchangeRateService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RbacSeeder::class);

        $this->business = Business::create([
            'name' => 'FinOps Testing Business',
            'ai_credits_balance' => 50000,
        ]);

        $this->exchangeRateService = app(ExchangeRateSyncService::class);
        $this->exchangeRateService->setManualRate(16000.0);

        $this->quoteService = new CostQuoteService(
            exchangeRateService: $this->exchangeRateService
        );
    }

    public function test_gpt_image_25_flare_cost_quote_calculation(): void
    {
        $registry = AiModelRegistry::create([
            'provider' => 'openai',
            'model_name' => 'Fast Generation (GPT Image 2.5 Flare)',
            'exact_model_id' => 'gpt-image-2.5-flare',
            'is_default' => true,
            'is_active' => true,
        ]);

        AiModelPricing::create([
            'model_id' => $registry->id,
            'version' => '1.0',
            'billing_mode' => 'token',
            'currency' => 'USD',
            'text_input_rate' => 5.000000,
            'cached_text_rate' => 1.250000,
            'image_input_rate' => 8.000000,
            'cached_image_rate' => 2.000000,
            'image_output_rate' => 30.000000,
            'effective_at' => now(),
        ]);

        // Quote dengan 250 text tokens, 0 image in, 1000 completion tokens (medium quality)
        // Text USD: 250 * 5 / 1.000.000 = 0.00125
        // Output USD: 1000 * 30 / 1.000.000 = 0.03000
        // Total USD = 0.03125
        // IDR at 16,000 = 500 IDR
        // With 10% reserve = 550 IDR
        // With 35% margin = ceil(550 / 0.65) = ceil(846.15) = 847 Kredit
        $quote = $this->quoteService->quote(
            provider: 'openai',
            modelName: 'gpt-image-2.5-flare',
            aspectRatio: '1:1',
            batchCount: 1,
            quality: 'medium',
            promptTokens: 250,
            imageInTokens: 0
        );

        $this->assertSame(847, $quote['credit_cost']);
        $this->assertSame(847, $quote['base_cost_per_image']);
        $this->assertEquals(0.03125, $quote['finops']['api_cost_usd']);
        $this->assertEquals(16000.0, $quote['finops']['exchange_rate']);
    }

    public function test_gpt_image_25_sunburst_cost_quote_with_reference_image(): void
    {
        $registry = AiModelRegistry::create([
            'provider' => 'openai',
            'model_name' => 'Premium Generation & Editing (GPT Image 2.5 Sunburst)',
            'exact_model_id' => 'gpt-image-2.5-sunburst',
            'is_default' => false,
            'is_active' => true,
        ]);

        AiModelPricing::create([
            'model_id' => $registry->id,
            'version' => '1.0',
            'billing_mode' => 'token',
            'currency' => 'USD',
            'text_input_rate' => 5.000000,
            'cached_text_rate' => 1.250000,
            'image_input_rate' => 8.000000,
            'cached_image_rate' => 2.000000,
            'image_output_rate' => 30.000000,
            'effective_at' => now(),
        ]);

        // Quote dengan 300 text tokens, 1000 image input tokens, 1500 completion tokens (high quality)
        // Text USD: 300 * 5 / 1M = 0.00150
        // Image in USD: 1000 * 8 / 1M = 0.00800
        // Output USD: 1500 * 30 / 1M = 0.04500
        // Total USD = 0.05450
        // IDR at 16,000 = 872 IDR
        // With 10% reserve = 959.20 IDR
        // With 35% margin = ceil(959.20 / 0.65) = ceil(1475.69) = 1476 Kredit
        $quote = $this->quoteService->quote(
            provider: 'openai',
            modelName: 'gpt-image-2.5-sunburst',
            aspectRatio: '1:1',
            batchCount: 1,
            quality: 'high',
            promptTokens: 300,
            imageInTokens: 1000
        );

        $this->assertSame(1476, $quote['credit_cost']);
        $this->assertEquals(0.05450, $quote['finops']['api_cost_usd']);
    }

    public function test_batch_count_multiplies_quote_cost(): void
    {
        $registry = AiModelRegistry::create([
            'provider' => 'openai',
            'model_name' => 'Fast Generation (GPT Image 2.5 Flare)',
            'exact_model_id' => 'gpt-image-2.5-flare',
            'is_default' => true,
            'is_active' => true,
        ]);

        AiModelPricing::create([
            'model_id' => $registry->id,
            'version' => '1.0',
            'billing_mode' => 'token',
            'currency' => 'USD',
            'text_input_rate' => 5.000000,
            'image_output_rate' => 30.000000,
            'effective_at' => now(),
        ]);

        $singleQuote = $this->quoteService->quote(
            provider: 'openai',
            modelName: 'gpt-image-2.5-flare',
            batchCount: 1,
            quality: 'medium',
            promptTokens: 250,
            imageInTokens: 0
        );

        $batchQuote = $this->quoteService->quote(
            provider: 'openai',
            modelName: 'gpt-image-2.5-flare',
            batchCount: 3,
            quality: 'medium',
            promptTokens: 250,
            imageInTokens: 0
        );

        $this->assertSame(847 * 3, $batchQuote['credit_cost']);
        $this->assertSame(3, $batchQuote['batch_count']);
    }
}

