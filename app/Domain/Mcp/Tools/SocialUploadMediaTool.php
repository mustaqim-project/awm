<?php

declare(strict_types=1);

namespace App\Domain\Mcp\Tools;

use App\Domain\Storage\StorageTrackingService;
use App\Domain\Storage\TenantStorage;
use App\Models\AuditLog;
use App\Models\Business;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

final class SocialUploadMediaTool implements McpToolInterface
{
    public function getName(): string
    {
        return 'social_upload_media';
    }

    public function getDescription(): string
    {
        return 'Mengunggah berkas media (gambar atau video) ke cloud storage COOCA untuk mendapatkan URL media publik yang valid. Mendukung upload via data base64 atau download otomatis dari URL eksternal (misal hasil generate AI / DALL-E / Canva / CDN). URL yang dihasilkan siap digunakan untuk publikasi media sosial, katalog produk, atau layanan.';
    }

    public function getRequiredAbility(): string
    {
        return 'mcp:social:manage';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'file_base64' => [
                    'type' => 'string',
                    'description' => 'String data berkas format Base64 (bisa menyertakan header data URI seperti "data:image/png;base64,..." atau base64 murni). Cocok untuk mengunggah gambar hasil kreasi AI.',
                ],
                'file_url' => [
                    'type' => 'string',
                    'description' => 'URL berkas gambar/video di internet yang ingin diunduh dan disimpan permanen ke server storage COOCA.',
                ],
                'file_name' => [
                    'type' => 'string',
                    'description' => 'Nama file yang diinginkan (misal: "promo-kopi-weekend.png"). Jika kosong, sistem membuat nama otomatis yang unik.',
                ],
                'folder' => [
                    'type' => 'string',
                    'enum' => ['social-media', 'products', 'services', 'general'],
                    'default' => 'social-media',
                    'description' => 'Folder penyimpanan tujuan di tenant bisnis Anda (default: social-media).',
                ],
            ],
        ];
    }

    public function execute(Business $business, ?User $user, array $arguments = []): array
    {
        $base64 = trim((string) ($arguments['file_base64'] ?? ''));
        $fileUrl = trim((string) ($arguments['file_url'] ?? ''));
        $folder = trim((string) ($arguments['folder'] ?? 'social-media'));
        if (! in_array($folder, ['social-media', 'products', 'services', 'general'], true)) {
            $folder = 'social-media';
        }

        if ($base64 === '' && $fileUrl === '') {
            throw new InvalidArgumentException('Wajib menyertakan salah satu dari parameter: file_base64 atau file_url.');
        }

        $binaryData = '';
        $mimeType = 'image/jpeg';
        $extension = 'jpg';

        if ($base64 !== '') {
            // Check for Data URI prefix: data:image/png;base64,....
            if (preg_match('/^data:([^;]+);base64,(.+)$/is', $base64, $matches)) {
                $mimeType = strtolower($matches[1]);
                $binaryData = base64_decode($matches[2], true);
            } else {
                $binaryData = base64_decode($base64, true);
            }

            if ($binaryData === false || strlen($binaryData) === 0) {
                throw new InvalidArgumentException('Data file_base64 tidak valid atau gagal didekode.');
            }

            $extension = match ($mimeType) {
                'image/png'       => 'png',
                'image/webp'      => 'webp',
                'image/gif'       => 'gif',
                'image/svg+xml'   => 'svg',
                'video/mp4'       => 'mp4',
                'video/quicktime' => 'mov',
                default           => 'jpg',
            };
        } else {
            // Download from external URL
            try {
                $response = Http::timeout(30)->withHeaders([
                    'User-Agent' => 'COOCA-Media-Importer/1.0',
                ])->get($fileUrl);

                if (! $response->successful()) {
                    throw new InvalidArgumentException("Gagal mengunduh berkas dari URL (HTTP {$response->status()}). Pastikan URL publik dapat diakses.");
                }

                $binaryData = $response->body();
                if (strlen($binaryData) === 0) {
                    throw new InvalidArgumentException('Berkas yang diunduh dari URL berukuran 0 byte.');
                }

                $detectedMime = $response->header('Content-Type');
                if (! empty($detectedMime)) {
                    $mimeType = strtolower(explode(';', $detectedMime)[0]);
                }

                $urlPath = parse_url($fileUrl, PHP_URL_PATH);
                $extCandidate = strtolower(pathinfo((string) $urlPath, PATHINFO_EXTENSION));
                if (in_array($extCandidate, ['png', 'jpg', 'jpeg', 'webp', 'gif', 'mp4', 'mov'], true)) {
                    $extension = $extCandidate;
                } else {
                    $extension = match ($mimeType) {
                        'image/png'       => 'png',
                        'image/webp'      => 'webp',
                        'image/gif'       => 'gif',
                        'video/mp4'       => 'mp4',
                        'video/quicktime' => 'mov',
                        default           => 'jpg',
                    };
                }
            } catch (\Exception $e) {
                throw new InvalidArgumentException('Gagal mengambil media dari file_url: ' . $e->getMessage());
            }
        }

        // Limit file size (max 25MB for videos/images)
        $fileSize = strlen($binaryData);
        if ($fileSize > 26214400) {
            throw new InvalidArgumentException('Ukuran berkas melebihi batas maksimal 25 MB.');
        }

        // Generate filename
        $customName = ! empty($arguments['file_name']) ? trim((string) $arguments['file_name']) : '';
        if ($customName !== '') {
            $baseName = pathinfo($customName, PATHINFO_FILENAME);
            $cleanBase = Str::slug($baseName);
            $cleanExt = pathinfo($customName, PATHINFO_EXTENSION) ?: $extension;
            $fileName = $cleanBase . '-' . Str::random(5) . '.' . strtolower($cleanExt);
        } else {
            $fileName = 'media-' . date('Ymd-His') . '-' . Str::random(6) . '.' . $extension;
        }

        // Tenant storage directory
        $tenantDir = TenantStorage::publicDir($business, $folder);
        $relativePath = $tenantDir . '/' . $fileName;

        // Save binary to public storage
        Storage::disk(TenantStorage::DISK_PUBLIC)->put($relativePath, $binaryData);

        // Record to StorageTrackingService
        try {
            $owner = $business->owner ?? $user ?? User::where('id', $business->user_id)->first();
            if ($owner !== null) {
                app(StorageTrackingService::class)->recordUpload(
                    file: $relativePath,
                    filePath: $relativePath,
                    category: $folder,
                    module: 'social-media',
                    owner: $owner,
                    business: $business,
                    uploader: $user,
                    disk: TenantStorage::DISK_PUBLIC,
                    fileName: $fileName,
                    mimeType: $mimeType,
                    fileSize: $fileSize
                );
            }
        } catch (\Throwable) {
            // Soft fail tracking if table schema differences exist
        }

        $publicUrl = TenantStorage::url($relativePath);

        // Audit log
        AuditLog::create([
            'business_id'    => $business->id,
            'user_id'        => $user?->id,
            'action'         => 'mcp.social_upload_media',
            'auditable_type' => Business::class,
            'auditable_id'   => $business->id,
            'old_values'     => null,
            'new_values'     => [
                'file_name'  => $fileName,
                'file_path'  => $relativePath,
                'public_url' => $publicUrl,
                'file_size'  => $fileSize,
                'mime_type'  => $mimeType,
                'folder'     => $folder,
            ],
            'ip_address'     => request()->ip(),
            'user_agent'     => request()->userAgent() ?: 'COOCA-MCP-Agent',
        ]);

        $formattedSize = $fileSize > 1048576
            ? round($fileSize / 1048576, 2) . ' MB'
            : round($fileSize / 1024, 1) . ' KB';

        return [
            'success'        => true,
            'media_url'      => $publicUrl,
            'media_path'     => $relativePath,
            'file_name'      => $fileName,
            'mime_type'      => $mimeType,
            'file_size'      => $fileSize,
            'size_formatted' => $formattedSize,
            'folder'         => $folder,
            'message'        => "Berkas media '{$fileName}' ({$formattedSize}) berhasil diunggah ke storage COOCA. URL media publik siap digunakan untuk postingan media sosial, produk, atau layanan.",
        ];
    }
}
