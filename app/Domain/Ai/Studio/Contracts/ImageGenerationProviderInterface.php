<?php

declare(strict_types=1);

namespace App\Domain\Ai\Studio\Contracts;

interface ImageGenerationProviderInterface
{
    /**
     * Eksekusi generasi gambar ke AI provider.
     *
     * @param array{
     *     positive_prompt: string,
     *     negative_prompt?: ?string,
     *     aspect_ratio?: string,
     *     width?: int,
     *     height?: int,
     *     batch_count?: int,
     *     seed?: ?int,
     *     style_preset?: ?string,
     *     reference_images?: array<int, string>,
     *     guidance_scale?: ?float,
     *     steps?: ?int
     * } $payload
     *
     * @return array{
     *     status: string,
     *     provider: string,
     *     model_name: string,
     *     provider_job_id: ?string,
     *     images: array<int, array{
     *         url: string,
     *         width: int,
     *         height: int,
     *         content_type?: string,
     *         seed?: int
     *     }>,
     *     latency_ms: int,
     *     error_message: ?string,
     *     raw_response?: array<string, mixed>
     * }
     */
    public function generate(array $payload): array;

    /**
     * Nama unik provider (misal: 'openai', 'ideogram', 'fal_ai', 'replicate', 'recraft').
     */
    public function getProviderName(): string;

    /**
     * Cek apakah rasio aspek didukung oleh provider ini.
     */
    public function supportsAspectRatio(string $aspectRatio): bool;
}
