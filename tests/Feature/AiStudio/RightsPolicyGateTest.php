<?php

declare(strict_types=1);

namespace Tests\Feature\AiStudio;

use App\Domain\Ai\Inspiration\Exceptions\CommercialUseNotAllowedException;
use App\Domain\Ai\Inspiration\RightsPolicyService;
use App\Models\AiStudio\InspirationItem;
use App\Models\AiStudio\InspirationSource;
use App\Models\AiStudio\InspirationSourcePolicy;
use App\Models\Business;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class RightsPolicyGateTest extends TestCase
{
    use RefreshDatabase;

    private RightsPolicyService $policyService;
    private Business $business;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->policyService = new RightsPolicyService;
        $this->business = Business::create(['name' => 'Kopi Senja Nusantara']);
        Context::setBusiness($this->business);
    }

    public function test_commercial_licenses_pass_evaluation(): void
    {
        $this->assertTrue($this->policyService->canUseCommercially('CC0'));
        $this->assertTrue($this->policyService->canUseCommercially('Pexels License'));
        $this->assertTrue($this->policyService->canUseCommercially('Unsplash'));
        $this->assertTrue($this->policyService->canUseCommercially('Pixabay'));
        $this->assertTrue($this->policyService->canUseCommercially('commercial_allowed'));
        $this->assertTrue($this->policyService->canUseCommercially('CC-BY'));
    }

    public function test_non_commercial_licenses_are_rejected(): void
    {
        $this->assertFalse($this->policyService->canUseCommercially('CC-BY-NC'));
        $this->assertFalse($this->policyService->canUseCommercially('CC-BY-NC-SA'));
        $this->assertFalse($this->policyService->canUseCommercially('All Rights Reserved'));
        $this->assertFalse($this->policyService->canUseCommercially('restricted'));
        $this->assertFalse($this->policyService->canUseCommercially(''));
    }

    public function test_assert_can_use_commercially_throws_exception_on_restricted_license(): void
    {
        $this->expectException(CommercialUseNotAllowedException::class);
        $this->policyService->assertCanUseCommercially('CC-BY-NC');
    }

    public function test_inspiration_item_with_commercial_policy_passes(): void
    {
        $source = InspirationSource::create([
            'code' => 'pexels',
            'name' => 'Pexels Curated',
            'driver_class' => 'App\\Domain\\Ai\\Inspiration\\Adapters\\PexelsAdapter',
        ]);

        InspirationSourcePolicy::create([
            'source_id' => $source->id,
            'allow_commercial_use' => true,
            'require_attribution' => true,
        ]);

        $item = InspirationItem::create([
            'source_id' => $source->id,
            'external_id' => 'pex-12345',
            'title' => 'Minimalist Coffee Cup on Stone Podium',
            'canonical_url' => 'https://pexels.com/photo/12345',
            'curated_notes' => 'Foto oleh Budi Santoso, lisensi: Pexels License',
            'status' => 'active',
        ]);

        $this->assertTrue($this->policyService->canUseCommercially($item));
        $this->assertStringContainsString('Budi Santoso', $this->policyService->formatAttribution($item));

        $disclaimer = $this->policyService->getDisclaimerNotice($item, $this->business);
        $this->assertStringContainsString('Kopi Senja Nusantara', $disclaimer);
        $this->assertStringContainsString('Budi Santoso', $disclaimer);
    }
}
