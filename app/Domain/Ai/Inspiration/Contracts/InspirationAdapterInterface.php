<?php

declare(strict_types=1);

namespace App\Domain\Ai\Inspiration\Contracts;

interface InspirationAdapterInterface
{
    /**
     * Cari konten inspirasi visual dari sumber eksternal.
     *
     * @param array<string, mixed> $filters
     * @return array<int, array{
     *     external_id: string,
     *     title: string,
     *     caption: ?string,
     *     canonical_url: ?string,
     *     thumbnail_url: ?string,
     *     media_type: string,
     *     media_urls: array<int, string>,
     *     aspect_ratio: string,
     *     tags: array<int, string>,
     *     license: string,
     *     photographer_name: ?string,
     *     allow_commercial_use: bool
     * }>
     */
    public function search(string $query, array $filters = []): array;

    /**
     * Ambil detail spesifik satu item inspirasi berdasarkan ID eksternal.
     *
     * @return array<string, mixed>|null
     */
    public function getItem(string $externalId): ?array;

    /**
     * Dapatkan format atribusi legal dan status lisensi komersial.
     *
     * @return array{
     *     license: string,
     *     allow_commercial_use: bool,
     *     require_attribution: bool,
     *     attribution_text: string,
     *     source_url: ?string
     * }
     */
    public function getAttribution(string $externalId): array;

    /**
     * Kode unik pengenal sumber (misal: 'openverse', 'pexels', 'curated', 'pinterest').
     */
    public function getSourceCode(): string;
}
