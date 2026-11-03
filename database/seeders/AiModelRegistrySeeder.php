<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AiStudio\AiModelPricing;
use App\Models\AiStudio\AiModelRegistry;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AiModelRegistrySeeder extends Seeder
{
    public function run(): void
    {
        $models = [
            [
                'provider' => 'openai',
                'model_name' => 'Fast Generation (GPT Image 2.5 Flare)',
                'exact_model_id' => 'gpt-image-2.5-flare',
                'version' => '2026-09-08',
                'description' => 'Model generasi gambar berkecepatan tinggi OpenAI untuk pembuatan visual harian UMKM, konten feed, dan materi promosi.',
                'use_case' => 'Generate harian, konten media sosial cepat, visual produk standar',
                'is_default' => true,
                'is_active' => true,
                'capabilities' => ['text_input' => true, 'image_input' => true, 'image_edit' => false],
                'supported_sizes' => ['1024x1024', '1024x1536', '1536x1024', 'custom'],
                'supported_qualities' => ['low', 'medium', 'high', 'xhigh', 'max', 'auto'],
                'supported_output_formats' => ['png', 'jpeg', 'webp'],
                'parameters_policy' => [
                    'multiple_of' => 16,
                    'aspect_ratio_min' => '1:3',
                    'aspect_ratio_max' => '3:1',
                    'max_edge' => 3840,
                    'experimental_threshold' => '2560x1440',
                ],
                'api_endpoint' => 'https://api.openai.com/v1/images/generations',
                'rate_limit_rpm' => 60,
                'timeout_seconds' => 45,
                'official_doc_url' => 'https://platform.openai.com/docs/models/gpt-image-2.5',
                'last_verified_at' => now(),
                'pricing' => [
                    'version' => '1.0',
                    'billing_mode' => 'token',
                    'currency' => 'USD',
                    'text_input_rate' => 5.000000,
                    'cached_text_rate' => 1.250000,
                    'image_input_rate' => 8.000000,
                    'cached_image_rate' => 2.000000,
                    'image_output_rate' => 30.000000,
                    'fixed_per_image_cost_usd' => null,
                    'effective_at' => now(),
                ],
            ],
            [
                'provider' => 'openai',
                'model_name' => 'Premium Generation & Editing (GPT Image 2.5 Sunburst)',
                'exact_model_id' => 'gpt-image-2.5-sunburst',
                'version' => '2026-09-08',
                'description' => 'Model generasi dan image editing presisi tinggi OpenAI dengan kemampuan mempertahankan detail visual dan pengolahan referensi tingkat lanjut.',
                'use_case' => 'Editing visual premium, foto produk presisi tinggi, komposisi visual kompleks',
                'is_default' => false,
                'is_active' => true,
                'capabilities' => ['text_input' => true, 'image_input' => true, 'image_edit' => true],
                'supported_sizes' => ['1024x1024', '1024x1536', '1536x1024', 'custom'],
                'supported_qualities' => ['low', 'medium', 'high', 'xhigh', 'max', 'auto'],
                'supported_output_formats' => ['png', 'jpeg', 'webp'],
                'parameters_policy' => [
                    'multiple_of' => 16,
                    'aspect_ratio_min' => '1:3',
                    'aspect_ratio_max' => '3:1',
                    'max_edge' => 3840,
                    'experimental_threshold' => '2560x1440',
                ],
                'api_endpoint' => 'https://api.openai.com/v1/images/edits',
                'rate_limit_rpm' => 40,
                'timeout_seconds' => 60,
                'official_doc_url' => 'https://platform.openai.com/docs/models/gpt-image-2.5',
                'last_verified_at' => now(),
                'pricing' => [
                    'version' => '1.0',
                    'billing_mode' => 'token',
                    'currency' => 'USD',
                    'text_input_rate' => 5.000000,
                    'cached_text_rate' => 1.250000,
                    'image_input_rate' => 8.000000,
                    'cached_image_rate' => 2.000000,
                    'image_output_rate' => 30.000000,
                    'fixed_per_image_cost_usd' => null,
                    'effective_at' => now(),
                ],
            ],
            [
                'provider' => 'ideogram',
                'model_name' => 'Ideogram v2 (Typography & Labels)',
                'exact_model_id' => 'ideogram-v2',
                'version' => '2.0',
                'description' => 'Model visual spesialis tipografi, render teks bahasa Indonesia, dan poster produk komersial.',
                'use_case' => 'Poster promosi dengan tipografi tajam, desain kemasan berlabel',
                'is_default' => false,
                'is_active' => true,
                'capabilities' => ['text_input' => true, 'image_input' => false, 'image_edit' => false],
                'supported_sizes' => ['1024x1024', '1024x1792', '1792x1024'],
                'supported_qualities' => ['default'],
                'supported_output_formats' => ['png', 'jpeg'],
                'parameters_policy' => null,
                'api_endpoint' => 'https://api.ideogram.ai/generate',
                'rate_limit_rpm' => 50,
                'timeout_seconds' => 45,
                'official_doc_url' => 'https://docs.ideogram.ai',
                'last_verified_at' => now(),
                'pricing' => [
                    'version' => '1.0',
                    'billing_mode' => 'per_image',
                    'currency' => 'USD',
                    'text_input_rate' => 0.000000,
                    'cached_text_rate' => 0.000000,
                    'image_input_rate' => 0.000000,
                    'cached_image_rate' => 0.000000,
                    'image_output_rate' => 0.000000,
                    'fixed_per_image_cost_usd' => 0.080000,
                    'effective_at' => now(),
                ],
            ],
            [
                'provider' => 'stability',
                'model_name' => 'Stable Diffusion XL (Fast & Artistic)',
                'exact_model_id' => 'sdxl',
                'version' => '1.0',
                'description' => 'Model open-source cepat untuk variasi artistik dan konsep visual produk dasar.',
                'use_case' => 'Draf visual cepat, eksplorasi konsep artistik berbiaya rendah',
                'is_default' => false,
                'is_active' => true,
                'capabilities' => ['text_input' => true, 'image_input' => false, 'image_edit' => false],
                'supported_sizes' => ['1024x1024', '1024x1536', '1536x1024'],
                'supported_qualities' => ['default'],
                'supported_output_formats' => ['png', 'webp'],
                'parameters_policy' => null,
                'api_endpoint' => 'https://api.stability.ai/v1/generation/stable-diffusion-xl-1024-v1-0/text-to-image',
                'rate_limit_rpm' => 60,
                'timeout_seconds' => 30,
                'official_doc_url' => 'https://platform.stability.ai',
                'last_verified_at' => now(),
                'pricing' => [
                    'version' => '1.0',
                    'billing_mode' => 'per_image',
                    'currency' => 'USD',
                    'text_input_rate' => 0.000000,
                    'cached_text_rate' => 0.000000,
                    'image_input_rate' => 0.000000,
                    'cached_image_rate' => 0.000000,
                    'image_output_rate' => 0.000000,
                    'fixed_per_image_cost_usd' => 0.040000,
                    'effective_at' => now(),
                ],
            ],
            [
                'provider' => 'openai',
                'model_name' => 'OpenAI DALL-E 3 (Retired / Legacy)',
                'exact_model_id' => 'dall-e-3',
                'version' => '3.0',
                'description' => 'Model warisan OpenAI terdahulu (Dinonaktifkan sesuai spesifikasi PRD AI Studio v2).',
                'use_case' => 'Histori transaksi warisan saja',
                'is_default' => false,
                'is_active' => false, // Dinonaktifkan
                'capabilities' => ['text_input' => true, 'image_input' => false, 'image_edit' => false],
                'supported_sizes' => ['1024x1024', '1024x1792', '1792x1024'],
                'supported_qualities' => ['standard', 'hd'],
                'supported_output_formats' => ['png'],
                'parameters_policy' => null,
                'api_endpoint' => 'https://api.openai.com/v1/images/generations',
                'rate_limit_rpm' => 20,
                'timeout_seconds' => 60,
                'official_doc_url' => 'https://platform.openai.com/docs/models/dall-e-3',
                'last_verified_at' => now(),
                'pricing' => [
                    'version' => '1.0',
                    'billing_mode' => 'per_image',
                    'currency' => 'USD',
                    'text_input_rate' => 0.000000,
                    'cached_text_rate' => 0.000000,
                    'image_input_rate' => 0.000000,
                    'cached_image_rate' => 0.000000,
                    'image_output_rate' => 0.000000,
                    'fixed_per_image_cost_usd' => 0.080000,
                    'effective_at' => now(),
                ],
            ],
        ];

        foreach ($models as $item) {
            $pricingData = $item['pricing'];
            unset($item['pricing']);

            /** @var AiModelRegistry $registry */
            $registry = AiModelRegistry::updateOrCreate(
                ['exact_model_id' => $item['exact_model_id']],
                $item
            );

            AiModelPricing::updateOrCreate(
                [
                    'model_id' => $registry->id,
                    'version' => $pricingData['version'],
                ],
                $pricingData
            );
        }
    }
}

