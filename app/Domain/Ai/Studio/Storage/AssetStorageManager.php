<?php

declare(strict_types=1);

namespace App\Domain\Ai\Studio\Storage;

use App\Domain\Storage\TenantStorage;
use App\Models\AiStudio\BusinessInspirationGeneration;
use App\Models\AiStudio\BusinessInspirationGenerationAsset;
use App\Models\Business;
use App\Models\StorageFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

class AssetStorageManager
{
    public const DISK = 'public';

    /**
     * Simpan output gambar hasil generasi AI ke storage bisnis tenant.
     *
     * @param array<string, mixed> $metadata
     */
    public function storeGenerationOutput(
        BusinessInspirationGeneration $generation,
        string $imageUrlOrContent,
        string $aspectRatio = '1:1',
        array $metadata = []
    ): BusinessInspirationGenerationAsset {
        $binary = $this->fetchOrExtractBinary($imageUrlOrContent);

        if (empty($binary)) {
            throw new RuntimeException('Konten gambar hasil generasi AI kosong.');
        }

        // 1. Ekstraksi informasi fisik gambar
        $imageInfo = @getimagesizefromstring($binary);
        $width = $imageInfo ? (int) $imageInfo[0] : 1024;
        $height = $imageInfo ? (int) $imageInfo[1] : 1024;
        $mime = $imageInfo ? (string) $imageInfo['mime'] : 'image/png';
        $extension = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/webp' => 'webp',
            default => 'png',
        };

        // 2. Buat path penyimpanan terisolasi per tenant
        /** @var Business $business */
        $business = Business::query()->findOrFail($generation->business_id);
        $slug = TenantStorage::slugForBusiness($business);
        $uuid = (string) Str::uuid();
        $fileName = "ai_render_{$uuid}.{$extension}";
        $folderPath = "bisnis/{$slug}/ai-studio/{$generation->project_id}";
        $storagePath = "{$folderPath}/{$fileName}";

        // 3. Simpan file utama ke disk
        Storage::disk(self::DISK)->put($storagePath, $binary);
        $publicUrl = Storage::disk(self::DISK)->url($storagePath);

        // 4. Buat thumbnail ringan untuk pratinjau kartu
        $thumbName = "ai_thumb_{$uuid}.{$extension}";
        $thumbStoragePath = "{$folderPath}/{$thumbName}";
        $thumbUrl = $this->createAndSaveThumbnail($binary, $thumbStoragePath, $mime)
            ? Storage::disk(self::DISK)->url($thumbStoragePath)
            : $publicUrl;

        // 5. Daftarkan ke storage_files jika tabel tersedia
        $storageFileId = null;
        try {
            $storageFile = StorageFile::create([
                'business_id' => $business->id,
                'user_id' => $generation->user_id,
                'owner_id' => $generation->user_id ?? $business->id,
                'file_name' => $fileName,
                'file_path' => $storagePath,
                'disk' => self::DISK,
                'mime_type' => $mime,
                'file_size' => strlen($binary),
                'category' => 'ai_studio_output',
                'module' => 'ai_studio',
                'status' => 'active',
                'metadata' => [
                    'generation_id' => $generation->id,
                    'project_id' => $generation->project_id,
                    'aspect_ratio' => $aspectRatio,
                    'width' => $width,
                    'height' => $height,
                ],
            ]);
            $storageFileId = $storageFile->id;
        } catch (\Throwable $e) {
            Log::info('StorageFile registration skipped: ' . $e->getMessage());
        }

        // 6. Buat entri BusinessInspirationGenerationAsset
        return BusinessInspirationGenerationAsset::create([
            'business_id' => $business->id,
            'generation_id' => $generation->id,
            'output_image_url' => $publicUrl,
            'storage_path' => $storagePath,
            'thumbnail_url' => $thumbUrl,
            'aspect_ratio' => $aspectRatio,
            'width' => $width,
            'height' => $height,
            'is_watermarked' => false,
            'is_selected_favorite' => false,
            'is_upscaled' => false,
            'storage_file_id' => $storageFileId,
            'metadata' => array_merge($metadata, [
                'file_size_bytes' => strlen($binary),
                'mime_type' => $mime,
            ]),
        ]);
    }

    /**
     * Ambil konten biner gambar dari URL (dengan SSRF guard) atau base64 string.
     */
    protected function fetchOrExtractBinary(string $imageUrlOrContent): string
    {
        // 1. Cek jika data URI / base64
        if (str_starts_with($imageUrlOrContent, 'data:image')) {
            $parts = explode(',', $imageUrlOrContent);
            return base64_decode(end($parts));
        }

        // 2. Jika merupakan URL eksternal, lakukan pemeriksaan keamanan SSRF
        if (filter_var($imageUrlOrContent, FILTER_VALIDATE_URL)) {
            $this->validateSafeUrl($imageUrlOrContent);

            $response = Http::timeout(45)->get($imageUrlOrContent);
            if ($response->successful()) {
                return $response->body();
            }

            throw new RuntimeException("Gagal mengunduh gambar dari provider (HTTP {$response->status()}).");
        }

        // 3. Diasumsikan biner mentah jika bukan URL
        return $imageUrlOrContent;
    }

    /**
     * SSRF Filter: Mencegah akses ke IP lokal, subnet privat, atau cloud metadata endpoint.
     */
    protected function validateSafeUrl(string $url): void
    {
        $parsed = parse_url($url);
        $host = $parsed['host'] ?? '';

        if ($host === '') {
            throw new InvalidArgumentException('URL gambar tidak valid.');
        }

        // Cek nama host terlarang
        if (in_array(strtolower($host), ['localhost', 'metadata.google.internal', 'instance-data'], true)) {
            throw new InvalidArgumentException('Akses ke host privat diblokir oleh sistem keamanan.');
        }

        $ip = gethostbyname($host);

        if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            // Jika IP adalah loopback atau private
            if (in_array($ip, ['127.0.0.1', '::1', '0.0.0.0', '169.254.169.254'], true)
                || str_starts_with($ip, '10.')
                || str_starts_with($ip, '192.168.')
                || str_starts_with($ip, '172.')
            ) {
                throw new InvalidArgumentException("Akses ke IP privat/lokal ({$ip}) diblokir.");
            }
        }
    }

    /**
     * Buat thumbnail terkompresi dengan lebar maksimal 320px.
     */
    protected function createAndSaveThumbnail(string $binary, string $thumbPath, string $mime): bool
    {
        if (! extension_loaded('gd')) {
            return false;
        }

        try {
            $src = @imagecreatefromstring($binary);
            if (! $src) {
                return false;
            }

            $origW = imagesx($src);
            $origH = imagesy($src);

            $targetW = min(360, $origW);
            $targetH = (int) round($origH * ($targetW / $origW));

            $thumb = imagecreatetruecolor($targetW, $targetH);

            if ($mime === 'image/png') {
                imagealphablending($thumb, false);
                imagesavealpha($thumb, true);
            }

            imagecopyresampled($thumb, $src, 0, 0, 0, 0, $targetW, $targetH, $origW, $origH);

            ob_start();
            if ($mime === 'image/jpeg') {
                imagejpeg($thumb, null, 80);
            } elseif ($mime === 'image/webp') {
                imagewebp($thumb, null, 80);
            } else {
                imagepng($thumb, 7);
            }
            $thumbBinary = ob_get_clean();

            imagedestroy($thumb);
            imagedestroy($src);

            if (! empty($thumbBinary)) {
                Storage::disk(self::DISK)->put($thumbPath, $thumbBinary);
                return true;
            }
        } catch (\Throwable $e) {
            Log::warning('Failed generating image thumbnail: ' . $e->getMessage());
        }

        return false;
    }
}
