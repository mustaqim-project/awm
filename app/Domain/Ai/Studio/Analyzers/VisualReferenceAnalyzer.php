<?php

declare(strict_types=1);

namespace App\Domain\Ai\Studio\Analyzers;

use App\Models\AiStudio\InspirationItem;
use App\Models\AiStudio\InspirationVisualAnalysis;

class VisualReferenceAnalyzer
{
    /**
     * Analisis struktur visual dari item inspirasi atau metadata referensi.
     *
     * @return array{
     *     composition: string,
     *     lighting: string,
     *     camera_angle: string,
     *     color_palette: array<int, string>,
     *     color_mood: string,
     *     negative_space: string,
     *     style_descriptors: array<int, string>,
     *     extracted_style_prompt: string,
     *     negative_style_prompt: string,
     *     suggested_aspect_ratios: array<int, string>,
     *     safety_score: float
     * }
     */
    public function analyze(InspirationItem|array $reference): array
    {
        if ($reference instanceof InspirationItem) {
            return $this->analyzeFromModel($reference);
        }

        return $this->analyzeFromArray($reference);
    }

    /**
     * Analisis menggunakan data model InspirationItem.
     *
     * @return array<string, mixed>
     */
    protected function analyzeFromModel(InspirationItem $item): array
    {
        // 1. Jika sudah pernah dianalisis dan tersimpan di database, gunakan hasil analisis tersebut
        if ($item->relationLoaded('visualAnalysis') && $item->visualAnalysis !== null) {
            return $this->formatFromAnalysisModel($item->visualAnalysis);
        }

        $existingAnalysis = $item->visualAnalysis()->first();
        if ($existingAnalysis !== null) {
            return $this->formatFromAnalysisModel($existingAnalysis);
        }

        // 2. Ekstraksi cerdas berbasis metadata, tag, judul, dan caption referensi
        $tags = (array) ($item->tags ?? []);
        $textCorpus = strtolower(implode(' ', [
            $item->title,
            (string) ($item->caption ?? ''),
            implode(' ', $tags),
            (string) ($item->industry_target ?? ''),
        ]));

        return $this->synthesizeVisualStructure($textCorpus, $item->media_type ?? 'image');
    }

    /**
     * Analisis menggunakan array data mentah (misal hasil API pencarian eksternal).
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function analyzeFromArray(array $data): array
    {
        $tags = (array) ($data['tags'] ?? []);
        $textCorpus = strtolower(implode(' ', [
            (string) ($data['title'] ?? ''),
            (string) ($data['caption'] ?? ''),
            implode(' ', $tags),
            (string) ($data['industry'] ?? ''),
            (string) ($data['description'] ?? ''),
        ]));

        return $this->synthesizeVisualStructure($textCorpus, (string) ($data['media_type'] ?? 'image'));
    }

    /**
     * Format output dari model InspirationVisualAnalysis.
     *
     * @return array<string, mixed>
     */
    protected function formatFromAnalysisModel(InspirationVisualAnalysis $analysis): array
    {
        $meta = (array) ($analysis->metadata ?? []);

        return [
            'composition' => $analysis->composition ?? 'podium',
            'lighting' => $analysis->lighting ?? 'studio_soft',
            'camera_angle' => $meta['camera_angle'] ?? 'eye_level',
            'color_palette' => (array) ($analysis->color_palette ?? ['#FFFFFF', '#D4AF37', '#2C2C2C']),
            'color_mood' => $analysis->color_mood ?? 'clean_luxury',
            'negative_space' => $meta['negative_space'] ?? 'top_center',
            'style_descriptors' => (array) ($meta['style_descriptors'] ?? ['commercial product photography', 'studio lighting', 'crisp sharp focus']),
            'extracted_style_prompt' => (string) ($analysis->extracted_style_prompt ?? ''),
            'negative_style_prompt' => (string) ($analysis->negative_style_prompt ?? 'blurry, distorted packaging, altered brand logo, unreadable text, lowres'),
            'suggested_aspect_ratios' => (array) ($analysis->suggested_aspect_ratios ?? ['1:1', '4:5', '9:16']),
            'safety_score' => (float) ($analysis->safety_score ?? 100.0),
        ];
    }

    /**
     * Sintesis struktur visual secara deterministik dan berbasis heuristik visual profesional.
     *
     * @return array<string, mixed>
     */
    protected function synthesizeVisualStructure(string $corpus, string $mediaType): array
    {
        // 1. Komposisi
        $composition = 'podium';
        if (str_contains($corpus, 'podium') || str_contains($corpus, 'pedestal') || str_contains($corpus, 'platform')) {
            $composition = 'podium';
        } elseif (str_contains($corpus, 'flatlay') || str_contains($corpus, 'flat lay') || str_contains($corpus, 'overhead') || str_contains($corpus, 'top down')) {
            $composition = 'flatlay';
        } elseif (str_contains($corpus, 'lifestyle') || str_contains($corpus, 'hand') || str_contains($corpus, 'person') || str_contains($corpus, 'wearing')) {
            $composition = 'lifestyle_hands';
        } elseif (str_contains($corpus, 'macro') || str_contains($corpus, 'close up') || str_contains($corpus, 'texture') || str_contains($corpus, 'detail')) {
            $composition = 'macro_details';
        } elseif (str_contains($corpus, 'minimal') || str_contains($corpus, 'clean') || str_contains($corpus, 'white space') || str_contains($corpus, 'isolated')) {
            $composition = 'minimalist_isolated';
        }

        // 2. Pencahayaan
        $lighting = 'studio_soft';
        if (str_contains($corpus, 'dramatic') || str_contains($corpus, 'shadow') || str_contains($corpus, 'contrast') || str_contains($corpus, 'dark')) {
            $lighting = 'dramatic_shadows';
        } elseif (str_contains($corpus, 'golden hour') || str_contains($corpus, 'sunlight') || str_contains($corpus, 'natural') || str_contains($corpus, 'outdoor')) {
            $lighting = 'golden_hour';
        } elseif (str_contains($corpus, 'neon') || str_contains($corpus, 'cyber') || str_contains($corpus, 'glow')) {
            $lighting = 'neon_ambient';
        } elseif (str_contains($corpus, 'bright') || str_contains($corpus, 'high key') || str_contains($corpus, 'white')) {
            $lighting = 'clean_commercial_highkey';
        }

        // 3. Sudut Kamera
        $cameraAngle = match ($composition) {
            'flatlay' => 'top_down_90deg',
            'macro_details' => 'macro_close_up',
            'lifestyle_hands' => 'three_quarter_perspective',
            default => 'eye_level',
        };

        // 4. Palet Warna & Mood
        $colorMood = 'clean_luxury';
        $palette = ['#FFFFFF', '#D4AF37', '#2C2C2C'];

        if (str_contains($corpus, 'earth') || str_contains($corpus, 'wood') || str_contains($corpus, 'organic') || str_contains($corpus, 'natural')) {
            $colorMood = 'earthy_warm';
            $palette = ['#FAF7F2', '#C69C6D', '#4A3B32', '#7E8D70'];
        } elseif (str_contains($corpus, 'vibrant') || str_contains($corpus, 'color') || str_contains($corpus, 'pop') || str_contains($corpus, 'fun')) {
            $colorMood = 'vibrant_playful';
            $palette = ['#FF4E50', '#FC913A', '#F9D423', '#EDE574'];
        } elseif (str_contains($corpus, 'pastel') || str_contains($corpus, 'soft') || str_contains($corpus, 'cute') || str_contains($corpus, 'pink')) {
            $colorMood = 'soft_pastel';
            $palette = ['#FAD0C4', '#FFD1FF', '#E0C3FC', '#8EC5FC'];
        } elseif (str_contains($corpus, 'dark') || str_contains($corpus, 'black') || str_contains($corpus, 'night')) {
            $colorMood = 'moody_editorial';
            $palette = ['#0F0F0F', '#1A1A1A', '#333333', '#C5A059'];
        }

        // 5. Negative Space (Ruang Bebas untuk Teks / Logo Toko)
        $negativeSpace = match ($composition) {
            'flatlay' => 'center_clearance',
            'macro_details' => 'left_third',
            'minimalist_isolated' => 'top_center',
            default => 'top_third_clean',
        };

        // 6. Style Descriptors & Prompt Styles
        $descriptors = [
            'commercial product photography',
            'studio lighting with soft rim reflections',
            'crisp tactile texture',
            'shot on 85mm prime lens f/2.8',
            'sharp detailed focus',
            'award winning advertising aesthetic',
        ];

        $extractedStylePrompt = "commercial advertising photography, {$composition} arrangement, {$lighting} lighting, {$colorMood} color grading, crisp clean background, tactile product realism";
        $negativeStylePrompt = 'blurry, low resolution, distorted packaging, warped label text, altered brand logo, amateur lighting, messy composition, extra limbs, grain, artifacts';

        return [
            'composition' => $composition,
            'lighting' => $lighting,
            'camera_angle' => $cameraAngle,
            'color_palette' => $palette,
            'color_mood' => $colorMood,
            'negative_space' => $negativeSpace,
            'style_descriptors' => $descriptors,
            'extracted_style_prompt' => $extractedStylePrompt,
            'negative_style_prompt' => $negativeStylePrompt,
            'suggested_aspect_ratios' => ['1:1', '4:5', '9:16', '16:9'],
            'safety_score' => 100.0,
        ];
    }
}
