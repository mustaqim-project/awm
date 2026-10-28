<?php

declare(strict_types=1);

namespace App\Domain\Ai\Studio\Compilers;

use App\Domain\Ai\Inspiration\RightsPolicyService;
use App\Domain\Ai\Studio\Analyzers\BusinessAssetAnalyzer;
use App\Domain\Ai\Studio\Analyzers\VisualReferenceAnalyzer;
use App\Domain\Ai\Studio\Billing\CostQuoteService;
use App\Models\AiStudio\InspirationItem;
use App\Models\Business;
use App\Models\Product;

class PromptCompilerService
{
    public function __construct(
        protected VisualReferenceAnalyzer $visualAnalyzer = new VisualReferenceAnalyzer,
        protected BusinessAssetAnalyzer $assetAnalyzer = new BusinessAssetAnalyzer,
        protected CostQuoteService $costQuoteService = new CostQuoteService,
        protected RightsPolicyService $rightsPolicyService = new RightsPolicyService
    ) {}

    /**
     * Kompilasi prompt rekayasa cerdas AI Studio yang menggabungkan struktur visual referensi,
     * identitas produk fisik riil merchant, dan brief kanal media sosial.
     *
     * @param array<string, mixed> $brief
     * @return array{
     *     positive_prompt: string,
     *     negative_prompt: string,
     *     style_preset: string,
     *     aspect_ratio: string,
     *     dimensions: array{width: int, height: int},
     *     prompt_parameters: array<string, mixed>,
     *     guardrail_status: string,
     *     guardrail_notes: ?string,
     *     transparency_sheet: array<string, mixed>
     * }
     */
    public function compile(
        Business $business,
        ?Product $product = null,
        InspirationItem|array|null $reference = null,
        array $brief = []
    ): array {
        // 0. Hak Cipta & Kebijakan Lisensi Komersial (AC-002)
        if ($reference !== null) {
            $this->rightsPolicyService->assertCanUseCommercially($reference);
        }

        // 1. Analisis Visual Referensi
        $visual = $reference !== null
            ? $this->visualAnalyzer->analyze($reference)
            : $this->getDefaultVisualStructure();

        // 2. Analisis Aset Bisnis & Produk
        $asset = $this->assetAnalyzer->analyze($business, $product, $brief);

        // 3. Konfigurasi Brief & Style Preset
        $stylePreset = (string) ($brief['style_preset'] ?? 'minimalist_luxury');
        $aspectRatio = (string) ($brief['aspect_ratio'] ?? '1:1');
        $targetChannel = (string) ($brief['target_channel'] ?? 'instagram');
        $customNotes = trim((string) ($brief['custom_notes'] ?? ''));

        // 4. Resolusi Dimensi & Biaya
        $quote = $this->costQuoteService->quote(
            provider: (string) ($brief['provider'] ?? 'ideogram'),
            modelName: (string) ($brief['model_name'] ?? 'ideogram-v2'),
            aspectRatio: $aspectRatio,
            batchCount: (int) ($brief['batch_count'] ?? 1),
            enableUpscale: (bool) ($brief['enable_upscale'] ?? false)
        );

        // 5. Susun Positive Prompt Terpadu
        $positivePrompt = $this->buildPositivePrompt(
            visual: $visual,
            asset: $asset,
            stylePreset: $stylePreset,
            targetChannel: $targetChannel,
            customNotes: $customNotes
        );

        // 6. Susun Negative Prompt & Anti-Hallucination Guardrails
        $negativePrompt = $this->buildNegativePrompt($visual, $asset);

        // 7. Evaluasi Guardrail Keamanan Konten
        [$guardrailStatus, $guardrailNotes] = $this->evaluateGuardrails($positivePrompt, $customNotes);

        // 8. Parameter Teknis Eksekusi Model
        $promptParameters = [
            'provider' => $quote['provider'],
            'model_name' => $quote['model_name'],
            'aspect_ratio' => $aspectRatio,
            'width' => $quote['dimensions']['width'],
            'height' => $quote['dimensions']['height'],
            'batch_count' => $quote['batch_count'],
            'style_preset' => $stylePreset,
            'guidance_scale' => 7.5,
            'steps' => 30,
            'safety_tolerance' => 'standard',
        ];

        // 9. Lembar Transparansi untuk Apple HIG Sheet di UI
        $transparencySheet = [
            'inspiration_composition' => $visual['composition'],
            'inspiration_lighting' => $visual['lighting'],
            'inspiration_color_mood' => $visual['color_mood'],
            'inspiration_palette' => $visual['color_palette'],
            'negative_space' => $visual['negative_space'],
            'product_name' => $asset['product_name'],
            'brand_name' => $asset['brand_name'],
            'packaging_type' => $asset['packaging_type'],
            'target_channel' => $targetChannel,
            'estimated_credits' => $quote['credit_cost'],
            'preservation_summary' => $asset['preservation_rules'],
        ];

        return [
            'positive_prompt' => $positivePrompt,
            'negative_prompt' => $negativePrompt,
            'style_preset' => $stylePreset,
            'aspect_ratio' => $aspectRatio,
            'dimensions' => $quote['dimensions'],
            'prompt_parameters' => $promptParameters,
            'guardrail_status' => $guardrailStatus,
            'guardrail_notes' => $guardrailNotes,
            'transparency_sheet' => $transparencySheet,
        ];
    }

    /**
     * Membangun kalimat positive prompt yang menggabungkan subjek nyata, pencahayaan, dan latar belakang.
     *
     * @param array<string, mixed> $visual
     * @param array<string, mixed> $asset
     */
    protected function buildPositivePrompt(
        array $visual,
        array $asset,
        string $stylePreset,
        string $targetChannel,
        string $customNotes
    ): string {
        $parts = [];

        // 1. Gaya Preset & Tone Awal
        $presetTone = match ($stylePreset) {
            'cinematic' => 'Award-winning cinematic advertising photography, shallow depth of field, anamorphic lens flare',
            'editorial' => 'High-fashion editorial commercial photoshoot, avant-garde studio set, Vogue aesthetic',
            'vibrant_pop' => 'Bold commercial pop advertising, energetic saturated colors, high contrast dynamic lighting',
            'hyperreal_fnb' => 'Ultra-crisp food & beverage culinary commercial packshot, appetizing textures, mouthwatering droplets',
            default => 'Pristine minimalist luxury commercial product photography, immaculate studio lighting, Apple product design aesthetic',
        };
        $parts[] = $presetTone;

        // 2. Subjek Utama: Fisik Produk Riil Merchant
        $parts[] = "Hero centerpiece featuring authentic {$asset['physical_descriptor']}";

        // 3. Tata Letak & Komposisi dari Inspirasi
        $parts[] = "carefully positioned in {$visual['composition']} arrangement with {$visual['negative_space']} composition";

        // 4. Pencahayaan & Suasana Warna
        $paletteStr = implode(', ', (array) ($visual['color_palette'] ?? []));
        $parts[] = "illuminated by {$visual['lighting']} lighting, refined {$visual['color_mood']} atmosphere, harmonious color grading ({$paletteStr})";

        // 5. Elemen Aksen Pendukung
        if (! empty($asset['accent_elements'])) {
            $accentsStr = implode(', ', array_slice($asset['accent_elements'], 0, 3));
            $parts[] = "tastefully accented with {$accentsStr}";
        }

        // 6. Catatan Kustom Merchant (jika ada)
        if ($customNotes !== '') {
            $parts[] = "special artistic direction: {$customNotes}";
        }

        // 7. Pengoptimalan Kanal Tujuan
        $channelEnhancement = match ($targetChannel) {
            'tiktok' => 'optimized for vertical 9:16 smartphone screen, eye-catching visual hook',
            'shopee', 'tokopedia' => 'clean e-commerce hero image, clear crisp product visibility, zero background clutter',
            default => 'premium social media showcase, highly shareable aesthetic',
        };
        $parts[] = $channelEnhancement;

        // 8. Kualitas Fotografi & Realisme Taktil
        $parts[] = 'hyper-realistic material textures, 8k resolution, photorealistic masterpiece, professional color science';

        return implode(', ', $parts);
    }

    /**
     * Membangun negative prompt standar anti-distorsi.
     *
     * @param array<string, mixed> $visual
     * @param array<string, mixed> $asset
     */
    protected function buildNegativePrompt(array $visual, array $asset): string
    {
        $baseNegative = [
            'distorted packaging',
            'warped bottle silhouette',
            'mutated product logo',
            'illegible gibberish text',
            'fake certification stamps',
            'deformed hands',
            'extra fingers',
            'blurry',
            'low resolution',
            'oversaturated cheap filter',
            'unnatural artifacts',
            'amateur snapshot',
            'muddy shadows',
            'watermark',
            'copyright notice',
        ];

        if (! empty($visual['negative_style_prompt'])) {
            $baseNegative[] = $visual['negative_style_prompt'];
        }

        return implode(', ', array_unique($baseNegative));
    }

    /**
     * Evaluasi guardrail keamanan konten (mencegah klaim palsu, konten sensitif).
     *
     * @return array{0: string, 1: ?string}
     */
    protected function evaluateGuardrails(string $positivePrompt, string $customNotes): array
    {
        $corpus = strtolower("{$positivePrompt} {$customNotes}");

        $blockedTerms = [
            'porn', 'nudity', 'weapon', 'gun', 'fake bpom', 'fake halal',
            'cure cancer', 'guaranteed cure', 'nazi', 'racist',
        ];

        foreach ($blockedTerms as $term) {
            if (str_contains($corpus, $term)) {
                return ['blocked', "Prompt terdeteksi memuat istilah terlarang: '{$term}'."];
            }
        }

        // Flagging ringan untuk kata klaim medis
        $flaggedTerms = ['obat dokter', '100% sembuh', 'tanpa izin'];
        foreach ($flaggedTerms as $flag) {
            if (str_contains($corpus, $flag)) {
                return ['flagged', "Prompt mengandung klaim sensitif ('{$flag}'), disarankan untuk ditinjau ulang."];
            }
        }

        return ['passed', null];
    }

    /**
     * Struktur visual default jika pengguna memulai dari nol tanpa gambar referensi.
     *
     * @return array<string, mixed>
     */
    protected function getDefaultVisualStructure(): array
    {
        return [
            'composition' => 'minimalist_podium',
            'lighting' => 'studio_soft',
            'camera_angle' => 'eye_level',
            'color_palette' => ['#FFFFFF', '#D4AF37', '#2C2C2C'],
            'color_mood' => 'clean_luxury',
            'negative_space' => 'top_third_clean',
            'style_descriptors' => ['commercial product photography', 'studio lighting', 'crisp sharp focus'],
            'extracted_style_prompt' => 'clean commercial product photo on stone podium with diffuse lighting',
            'negative_style_prompt' => 'blurry, distorted packaging, altered brand logo, unreadable text, lowres',
            'suggested_aspect_ratios' => ['1:1', '4:5', '9:16'],
            'safety_score' => 100.0,
        ];
    }
}
