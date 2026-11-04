<?php

declare(strict_types=1);

namespace App\Domain\Ai\Inspiration\Contracts;

/**
 * Kontrak standar adapter provider gambar eksternal COOCA AI Studio.
 */
interface ExternalProviderAdapterInterface
{
    /**
     * Dapatkan kode unik pengenal provider (e.g. 'pexels', 'pixabay', 'unsplash', 'pinterest').
     */
    public function getProviderKey(): string;

    /**
     * Dapatkan nama tampilan resmi provider.
     */
    public function getDisplayName(): string;

    /**
     * Apakah kredensial API resmi provider sudah dikonfigurasi dan valid.
     */
    public function isConfigured(): bool;

    /**
     * Cari gambar dari provider eksternal resmi.
     *
     * @param array<string, mixed> $filters
     * @return array{
     *     success: bool,
     *     provider: string,
     *     total_results: int,
     *     page: int,
     *     per_page: int,
     *     items: array<int, array{
     *         external_id: string,
     *         title: string,
     *         caption: ?string,
     *         image_url: string,
     *         thumbnail_url: string,
     *         width: int,
     *         height: int,
     *         aspect_ratio: string,
     *         author_name: ?string,
     *         author_url: ?string,
     *         source_url: string,
     *         license_name: string,
     *         commercial_use_allowed: bool,
     *         require_attribution: bool,
     *         attribution_text: string,
     *         tags: array<int, string>,
     *         raw_metadata?: array<string, mixed>
     *     }>,
     *     error_message?: ?string
     * }
     */
    public function searchImages(string $query, array $filters = []): array;

    /**
     * Cari video jika provider mendukung.
     *
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function searchVideos(string $query, array $filters = []): array;

    /**
     * Ambil rincian spesifik satu aset.
     *
     * @return array<string, mixed>|null
     */
    public function getAssetDetails(string $assetId): ?array;

    /**
     * Uji koneksi dan keabsahan kredensial API ke endpoint resmi provider.
     *
     * @return array{
     *     success: bool,
     *     status_code: int,
     *     message: string,
     *     latency_ms: int
     * }
     */
    public function validateConnection(): array;

    /**
     * Normalisasi payload mentah dari provider menjadi format internal seragam.
     *
     * @param mixed $response
     * @return array<string, mixed>
     */
    public function normalizeResponse(mixed $response): array;

    /**
     * Format atribusi legal wajib sesuai ketentuan hak cipta provider.
     *
     * @return array{
     *     photographer_name: ?string,
     *     photographer_url: ?string,
     *     source_name: string,
     *     source_url: string,
     *     attribution_text: string,
     *     license_name: string,
     *     license_url: ?string
     * }
     */
    public function getAttribution(string $assetId): array;

    /**
     * Metadata lisensi legal dan kebijakan penggunaan komersial.
     *
     * @return array{
     *     license: string,
     *     license_url: ?string,
     *     allow_commercial_use: bool,
     *     allow_redistribution: bool,
     *     require_attribution: bool,
     *     disclaimer: string
     * }
     */
    public function getLicenseMetadata(string $assetId): array;

    /**
     * Catat pelacakan unduhan jika diwajibkan oleh terms provider (seperti download endpoint Unsplash).
     */
    public function recordDownload(string $assetId): bool;

    /**
     * Validasi kepatuhan kebijakan penggunaan konten sebelum aset diproses.
     *
     * @return array{
     *     allowed: bool,
     *     reason?: string
     * }
     */
    public function validateUsagePolicy(): array;
}

