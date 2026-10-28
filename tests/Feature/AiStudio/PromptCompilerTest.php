<?php

declare(strict_types=1);

namespace Tests\Feature\AiStudio;

use App\Domain\Ai\Studio\Adapters\OpenAiImageAdapter;
use App\Domain\Ai\Studio\Compilers\PromptCompilerService;
use App\Models\AiStudio\InspirationItem;
use App\Models\AiStudio\InspirationSource;
use App\Models\Business;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PromptCompilerTest extends TestCase
{
    use RefreshDatabase;

    private PromptCompilerService $compilerService;
    private Business $business;
    private Product $product;
    private InspirationItem $inspirationItem;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->compilerService = new PromptCompilerService;

        $this->business = Business::create([
            'name' => 'Glow Botanical Skincare',
            'ai_credits_balance' => 10,
        ]);
        Context::setBusiness($this->business);

        $category = ProductCategory::create([
            'business_id' => $this->business->id,
            'name' => 'Serum Wajah',
        ]);

        $unit = \App\Models\Unit::create([
            'business_id' => $this->business->id,
            'name' => 'Pieces',
            'code' => 'pcs',
            'symbol' => 'pcs',
            'category' => \App\Models\Unit::CATEGORY_QUANTITY,
        ]);

        $this->product = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Hydrating Glow Facial Serum',
            'category_id' => $category->id,
            'output_unit_id' => $unit->id,
            'description' => 'Serum botol kaca dengan pipet kaca dan ekstrak niacinamide murni',
            'price' => 125000,
            'stock' => 50,
        ]);

        $source = InspirationSource::create([
            'code' => 'curated',
            'name' => 'Cooca Curated Studio',
            'driver_class' => 'App\\Domain\\Ai\\Inspiration\\Adapters\\CuratedLibraryAdapter',
        ]);

        $this->inspirationItem = InspirationItem::create([
            'source_id' => $source->id,
            'external_id' => 'cur-serum-01',
            'title' => 'Minimalist Podium Glass Bottle with Water Ripples',
            'caption' => 'Luxury cosmetic product photography on travertine stone podium with gentle water reflection',
            'media_type' => 'image',
            'tags' => ['podium', 'studio lighting', 'luxury', 'water drops'],
            'industry_target' => 'beauty',
            'status' => 'active',
        ]);
    }

    public function test_compiles_prompt_preserving_real_product_and_inspiration_composition(): void
    {
        $result = $this->compilerService->compile(
            business: $this->business,
            product: $this->product,
            reference: $this->inspirationItem,
            brief: [
                'target_channel' => 'instagram',
                'aspect_ratio' => '4:5',
                'style_preset' => 'minimalist_luxury',
                'custom_notes' => 'highlight fresh organic texture',
            ]
        );

        $this->assertNotEmpty($result['positive_prompt']);
        $this->assertNotEmpty($result['negative_prompt']);
        $this->assertSame('minimalist_luxury', $result['style_preset']);
        $this->assertSame('4:5', $result['aspect_ratio']);
        $this->assertSame('passed', $result['guardrail_status']);

        // Must preserve real brand name and product name
        $this->assertStringContainsString('Hydrating Glow Facial Serum', $result['positive_prompt']);
        $this->assertStringContainsString('Glow Botanical Skincare', $result['positive_prompt']);
        $this->assertStringContainsString('podium', strtolower($result['positive_prompt']));

        // Transparency sheet assertions
        $sheet = $result['transparency_sheet'];
        $this->assertSame('Hydrating Glow Facial Serum', $sheet['product_name']);
        $this->assertSame('Glow Botanical Skincare', $sheet['brand_name']);
        $this->assertSame('podium', $sheet['inspiration_composition']);
        $this->assertSame(2, $sheet['estimated_credits']);
    }

    public function test_guardrail_blocks_prohibited_terms(): void
    {
        $result = $this->compilerService->compile(
            business: $this->business,
            product: $this->product,
            reference: $this->inspirationItem,
            brief: [
                'custom_notes' => 'Include fake bpom guarantee on packaging label',
            ]
        );

        $this->assertSame('blocked', $result['guardrail_status']);
        $this->assertStringContainsString('fake bpom', (string) $result['guardrail_notes']);
    }

    public function test_openai_image_adapter_mock_generation_returns_valid_output(): void
    {
        $adapter = new OpenAiImageAdapter(forceMock: true);

        $this->assertSame('openai', $adapter->getProviderName());
        $this->assertTrue($adapter->supportsAspectRatio('1:1'));
        $this->assertTrue($adapter->supportsAspectRatio('9:16'));

        $compiled = $this->compilerService->compile(
            business: $this->business,
            product: $this->product,
            reference: $this->inspirationItem,
            brief: ['aspect_ratio' => '9:16']
        );

        $generationResult = $adapter->generate([
            'positive_prompt' => $compiled['positive_prompt'],
            'aspect_ratio' => $compiled['aspect_ratio'],
        ]);

        $this->assertSame('succeeded', $generationResult['status']);
        $this->assertSame('openai', $generationResult['provider']);
        $this->assertNotEmpty($generationResult['images']);
        $this->assertSame(1024, $generationResult['images'][0]['width']);
        $this->assertSame(1792, $generationResult['images'][0]['height']);
        $this->assertNotEmpty($generationResult['images'][0]['url']);
    }
}
