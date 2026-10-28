<?php

declare(strict_types=1);

namespace App\Domain\Ai\Studio\Billing;

use App\Models\Business;

class CostQuoteService
{
    public function __construct(
        protected CreditLedgerService $creditLedgerService = new CreditLedgerService
    ) {}

    /**
     * Hitung estimasi kredit untuk permintaan generasi AI Studio.
     *
     * @return array{
     *     credit_cost: int,
     *     base_cost_per_image: int,
     *     upscale_cost_per_image: int,
     *     cost_per_image: int,
     *     batch_count: int,
     *     provider: string,
     *     model_name: string,
     *     aspect_ratio: string,
     *     dimensions: array{width: int, height: int},
     *     breakdown: array{generation: int, upscale: int}
     * }
     */
    public function quote(
        string $provider = 'ideogram',
        string $modelName = 'ideogram-v2',
        string $aspectRatio = '1:1',
        int $batchCount = 1,
        bool $enableUpscale = false
    ): array {
        $safeBatch = max(1, min(4, $batchCount));
        $basePerImage = $this->resolveBaseCost($provider, $modelName);
        $upscaleCostPerImage = $enableUpscale ? 1 : 0;

        $costPerImage = $basePerImage + $upscaleCostPerImage;
        $totalCost = $costPerImage * $safeBatch;

        $dimensions = $this->resolveDimensions($aspectRatio, $enableUpscale);

        return [
            'credit_cost' => $totalCost,
            'base_cost_per_image' => $basePerImage,
            'upscale_cost_per_image' => $upscaleCostPerImage,
            'cost_per_image' => $costPerImage,
            'batch_count' => $safeBatch,
            'provider' => strtolower($provider),
            'model_name' => $modelName,
            'aspect_ratio' => $aspectRatio,
            'dimensions' => $dimensions,
            'breakdown' => [
                'generation' => $basePerImage * $safeBatch,
                'upscale' => $upscaleCostPerImage * $safeBatch,
            ],
        ];
    }

    /**
     * Cek apakah saldo bisnis mencukupi untuk biaya kredit yang dibutuhkan.
     */
    public function canAfford(string|Business $business, int $requiredCredits): bool
    {
        $balance = $this->creditLedgerService->getBalance($business);

        return $balance >= $requiredCredits;
    }

    /**
     * Resolusi biaya dasar kredit per model provider.
     */
    public function resolveBaseCost(string $provider, string $modelName): int
    {
        $normalizedModel = strtolower($modelName);
        $normalizedProvider = strtolower($provider);

        // Model HD / Ultra-realistis berbiaya 2 kredit
        if (
            str_contains($normalizedModel, 'v2')
            || str_contains($normalizedModel, 'pro')
            || str_contains($normalizedModel, 'dall-e-3')
            || str_contains($normalizedModel, 'ultra')
            || str_contains($normalizedModel, 'recraft-v3')
        ) {
            return 2;
        }

        // Default standard (SDXL, Ideogram standard, fast flux): 1 kredit
        return 1;
    }

    /**
     * Resolusi dimensi piksel berdasarkan aspek rasio tujuan.
     *
     * @return array{width: int, height: int}
     */
    public function resolveDimensions(string $aspectRatio, bool $isHd = false): array
    {
        $multiplier = $isHd ? 1.5 : 1.0;

        return match ($aspectRatio) {
            '4:5' => [
                'width' => (int) round(1080 * $multiplier),
                'height' => (int) round(1350 * $multiplier),
            ],
            '9:16' => [
                'width' => (int) round(1080 * $multiplier),
                'height' => (int) round(1920 * $multiplier),
            ],
            '16:9' => [
                'width' => (int) round(1920 * $multiplier),
                'height' => (int) round(1080 * $multiplier),
            ],
            default => [ // 1:1
                'width' => (int) round(1024 * $multiplier),
                'height' => (int) round(1024 * $multiplier),
            ],
        };
    }

    /**
     * Daftar rasio aspek yang didukung beserta deskripsi kanal penggunaan.
     *
     * @return array<string, array{label: string, channel: string, icon: string}>
     */
    public function supportedAspectRatios(): array
    {
        return [
            '1:1' => [
                'label' => 'Persegi (1:1)',
                'channel' => 'Instagram Post, Katalog Produk, Toko Online',
                'icon' => 'square',
            ],
            '4:5' => [
                'label' => 'Potret Feed (4:5)',
                'channel' => 'Instagram Feed Optimal, Carousel Ads',
                'icon' => 'smartphone',
            ],
            '9:16' => [
                'label' => 'Vertikal Penuh (9:16)',
                'channel' => 'TikTok, Instagram Story / Reels, Shorts',
                'icon' => 'maximize-2',
            ],
            '16:9' => [
                'label' => 'Lanskap Banner (16:9)',
                'channel' => 'Header Web Toko, Banner Promo Marketplace',
                'icon' => 'monitor',
            ],
        ];
    }
}
