<?php

declare(strict_types=1);

namespace App\Domain\Mcp\Tools;

use App\Domain\Storage\StorageTrackingService;
use App\Domain\Storage\TenantStorage;
use App\Models\AuditLog;
use App\Models\Business;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
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
        return 'Mengunggah berkas media (gambar atau video) langsung ke cloud storage COOCA untuk mendapatkan URL media publik yang valid. Menerima string data base64 utuh sekaligus dalam 1 panggilan (single upload tanpa dipecah) atau unduh otomatis dari URL publik internet (file_url). URL yang dihasilkan siap digunakan untuk postingan media sosial, produk, atau layanan.';
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
                    'description' => 'String data berkas format Base64 utuh sekaligus dalam 1 panggilan (bisa menyertakan header data URI seperti "data:image/png;base64,..." atau base64 murni). JANGAN DIPECAH / JANGAN DI-CHUNK, kirim seluruh data base64 secara utuh sekaligus.',
                ],
                'file_url' => [
                    'type' => 'string',
                    'description' => 'URL berkas gambar/video di internet (misal Cloudinary, Imgur, PostImages, Google Drive direct link, atau CDN) yang ingin diunduh dan disimpan otomatis ke server storage COOCA.',
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

        $uploadId = trim((string) ($arguments['upload_id'] ?? ''));
        $chunkIndex = isset($arguments['chunk_index']) ? (int) $arguments['chunk_index'] : null;
        $totalChunks = isset($arguments['total_chunks']) ? (int) $arguments['total_chunks'] : null;

        // Backward-compatibility: jika ada client lama yang masih mengirim chunking
        if ($uploadId !== '' && $chunkIndex !== null && $totalChunks !== null && $totalChunks > 1) {
            if ($base64 !== '') {
                Cache::put("mcp_upload_chunk:{$business->id}:{$uploadId}:{$chunkIndex}", $base64, now()->addHours(2));
                $allReceived = true;
                $combined = '';
                for ($i = 0; $i < $totalChunks; $i++) {
                    $chk = Cache::get("mcp_upload_chunk:{$business->id}:{$uploadId}:{$i}");
                    if ($chk === null) {
                        $allReceived = false;
                        break;
                    }
                    $combined .= $chk;
                }
                if ($allReceived) {
                    $base64 = $combined;
                } else {
                    return [
                        'success'      => true,
                        'status'       => 'chunk_received',
                        'upload_id'    => $uploadId,
                        'chunk_index'  => $chunkIndex,
                        'total_chunks' => $totalChunks,
                        'message'      => "Potongan chunk {$chunkIndex}/{$totalChunks} tersimpan.",
                    ];
                }
            }
        }

        if ($base64 === '' && $fileUrl === '') {
            throw new InvalidArgumentException('Wajib menyertakan salah satu dari parameter: file_base64 atau file_url.');
        }

        $binaryData = '';
        $mimeType = 'image/jpeg';
        $extension = 'jpg';

        if ($base64 !== '') {
            // Bersihkan spasi dan baris baru yang mungkin disisipkan oleh AI
            $cleanBase64 = preg_replace('/\s+/', '', $base64);

            // Check for Data URI prefix: data:image/png;base64,....
            if (preg_match('/^data:([^;]+);base64,(.+)$/is', $cleanBase64, $matches)) {
                $mimeType = strtolower($matches[1]);
                $binaryData = base64_decode($matches[2], true);
            } else {
                $binaryData = base64_decode($cleanBase64, true);
            }

            if ($binaryData === false || strlen($binaryData) === 0) {
                throw new InvalidArgumentException('Data file_base64 tidak valid atau gagal didekode.');
            }

            // Deteksi MIME type riil dari buffer biner
            try {
                $finfo = new \finfo(FILEINFO_MIME_TYPE);
                $detected = $finfo->buffer($binaryData);
                if (! empty($detected) && $detected !== 'application/octet-stream') {
                    $mimeType = strtolower($detected);
                }
            } catch (\Throwable) {
                // Gunakan default mimeType
            }

            $extension = match ($mimeType) {
                'image/png'       => 'png',
                'image/webp'      => 'webp',
                'image/gif'       => 'gif',
                'image/svg+xml'   => 'svg',
                'video/mp4'       => 'mp4',
                'video/quicktime' => 'mov',
                'video/webm'      => 'webm',
                default           => 'jpg',
            };
        } else {
            // Download from external URL dengan proteksi SSRF ketat
            try {
                $downloadUrl = $fileUrl;

                // 1. Validasi URL sumber awal bebas dari SSRF (loopback/private IP/metadata IP)
                $this->assertSafePublicUrl($fileUrl);

                // Auto-konversi link Google Drive sharing agar langsung mengunduh biner gambar
                if (preg_match('#drive\.google\.com/(?:file/d/|open\?id=|uc\?(?:export=download&)?id=)([a-zA-Z0-9_-]+)#i', $fileUrl, $driveMatch)) {
                    $driveId = $driveMatch[1];
                    // lh3.googleusercontent.com/d/{id} mengembalikan raw biner gambar publik tanpa redirect HTML
                    $downloadUrl = "https://lh3.googleusercontent.com/d/{$driveId}";
                } elseif (preg_match('#dropbox\.com/#i', $fileUrl)) {
                    // Auto-konversi tautan Dropbox agar langsung mengunduh raw biner
                    $downloadUrl = preg_replace('/(\?|&)dl=0/i', '', $fileUrl);
                    $downloadUrl .= (str_contains($downloadUrl, '?') ? '&' : '?') . 'raw=1';
                }

                // 2. Validasi URL hasil konversi jika berbeda
                if ($downloadUrl !== $fileUrl) {
                    $this->assertSafePublicUrl($downloadUrl);
                }

                $response = Http::timeout(30)
                    ->withOptions([
                        'allow_redirects' => [
                            'max'       => 5,
                            'protocols' => ['http', 'https'],
                        ],
                    ])
                    ->withHeaders([
                        'User-Agent' => 'COOCA-Media-Importer/1.0',
                    ])
                    ->get($downloadUrl);

                // Fallback untuk Google Drive jika lh3 gagal
                if (! $response->successful() && isset($driveId)) {
                    $fallbackDriveUrl = "https://drive.google.com/uc?export=download&id={$driveId}&confirm=t";
                    $this->assertSafePublicUrl($fallbackDriveUrl);

                    $response = Http::timeout(30)
                        ->withOptions([
                            'allow_redirects' => ['max' => 5, 'protocols' => ['http', 'https']],
                        ])
                        ->withHeaders([
                            'User-Agent' => 'COOCA-Media-Importer/1.0',
                        ])
                        ->get($fallbackDriveUrl);
                }

                if (! $response->successful()) {
                    throw new InvalidArgumentException("Gagal mengunduh berkas dari URL (HTTP {$response->status()}). Pastikan URL dapat diakses publik.");
                }

                $binaryData = $response->body();
                if (strlen($binaryData) === 0) {
                    throw new InvalidArgumentException('Berkas yang diunduh dari URL berukuran 0 byte.');
                }

                // Cek apakah response berupa laman HTML (misal link privat atau halaman login)
                if (str_starts_with(trim($binaryData), '<!DOCTYPE html') || str_starts_with(trim($binaryData), '<html')) {
                    throw new InvalidArgumentException('Tautan yang diberikan mengarah ke halaman web login/HTML, bukan berkas gambar langsung. Pastikan akses Google Drive diatur ke "Siapa saja yang memiliki link" (Anyone with the link).');
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

        // Analisis dimensi dan rasio aspek gambar untuk panduan media sosial (Instagram/Facebook)
        $width = null;
        $height = null;
        $aspectRatioLabel = null;
        $isInstagramFeedReady = true;
        $socialAdvisory = null;

        if (str_starts_with($mimeType, 'image/')) {
            $imgInfo = @getimagesizefromstring($binaryData);
            if ($imgInfo !== false && ! empty($imgInfo[0]) && ! empty($imgInfo[1])) {
                $width = (int) $imgInfo[0];
                $height = (int) $imgInfo[1];
                $ratio = round($width / $height, 2);

                if (abs($ratio - 1.0) <= 0.05) {
                    $aspectRatioLabel = '1:1 (Square)';
                } elseif (abs($ratio - 0.8) <= 0.05) {
                    $aspectRatioLabel = '4:5 (Portrait Feed)';
                } elseif (abs($ratio - 0.56) <= 0.05) {
                    $aspectRatioLabel = '9:16 (Stories / Reels)';
                } elseif (abs($ratio - 1.78) <= 0.05) {
                    $aspectRatioLabel = '16:9 (Landscape)';
                } elseif (abs($ratio - 1.91) <= 0.05) {
                    $aspectRatioLabel = '1.91:1 (Landscape Feed)';
                } else {
                    $aspectRatioLabel = "{$ratio}:1";
                }

                // Standar Meta Instagram Feed: 4:5 (0.8) s/d 1.91:1 (1.91)
                if ($ratio < 0.79 || $ratio > 1.93) {
                    $isInstagramFeedReady = false;
                    $socialAdvisory = "Rasio {$aspectRatioLabel} berada di luar batas Feed Instagram (4:5 s/d 1.91:1). Jika ingin diposting ke Feed, disarankan rasio 1:1 atau 4:5. Untuk Stories/Reels cocok 9:16.";
                }
            }
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
                'file_name'    => $fileName,
                'file_path'    => $relativePath,
                'public_url'   => $publicUrl,
                'file_size'    => $fileSize,
                'mime_type'    => $mimeType,
                'folder'       => $folder,
                'width'        => $width,
                'height'       => $height,
                'aspect_ratio' => $aspectRatioLabel,
            ],
            'ip_address'     => request()->ip(),
            'user_agent'     => request()->userAgent() ?: 'COOCA-MCP-Agent',
        ]);

        $formattedSize = $fileSize > 1048576
            ? round($fileSize / 1048576, 2) . ' MB'
            : round($fileSize / 1024, 1) . ' KB';

        $output = [
            'success'               => true,
            'media_url'             => $publicUrl,
            'media_path'            => $relativePath,
            'file_name'             => $fileName,
            'mime_type'             => $mimeType,
            'file_size'             => $fileSize,
            'size_formatted'        => $formattedSize,
            'width'                 => $width,
            'height'                => $height,
            'aspect_ratio'          => $aspectRatioLabel,
            'is_instagram_ready'    => $isInstagramFeedReady,
            'social_advisory'       => $socialAdvisory,
            'folder'                => $folder,
            'message'               => "Berkas media '{$fileName}' ({$formattedSize}) berhasil diunggah ke storage COOCA. URL media publik siap digunakan untuk postingan media sosial, produk, atau layanan.",
        ];

        return array_filter($output, fn ($val) => $val !== null);
    }

    /**
     * Validasi URL eksternal agar aman dari serangan Server-Side Request Forgery (SSRF).
     * Mencegah akses ke localhost, private IP, cloud metadata IP, dan skema selain http/https.
     *
     * @throws InvalidArgumentException
     */
    private function assertSafePublicUrl(string $url): void
    {
        $parts = parse_url($url);
        if ($parts === false || empty($parts['scheme']) || empty($parts['host'])) {
            throw new InvalidArgumentException('Format tautan file_url tidak valid.');
        }

        $scheme = strtolower((string) $parts['scheme']);
        if (! in_array($scheme, ['http', 'https'], true)) {
            throw new InvalidArgumentException("Skema protokol '{$scheme}' tidak diizinkan. Hanya http dan https yang didukung.");
        }

        $host = strtolower((string) $parts['host']);

        // Blokir hostname lokal/internal secara ketat
        $blockedHosts = ['localhost', '127.0.0.1', '::1', '0.0.0.0', 'metadata.google.internal'];
        if (in_array($host, $blockedHosts, true)
            || str_ends_with($host, '.localhost')
            || str_ends_with($host, '.test')
            || str_ends_with($host, '.local')
            || str_ends_with($host, '.internal')
        ) {
            throw new InvalidArgumentException('Tautan ke host lokal atau jaringan privat dilarang demi keamanan sistem (SSRF Protection).');
        }

        // Dapatkan resolusi IP dari host
        $resolvedIps = [];

        if (filter_var($host, FILTER_VALIDATE_IP)) {
            $resolvedIps[] = $host;
        } else {
            $dnsRecords = @dns_get_record($host, DNS_A + DNS_AAAA);
            if (is_array($dnsRecords) && ! empty($dnsRecords)) {
                foreach ($dnsRecords as $rec) {
                    if (! empty($rec['ip'])) {
                        $resolvedIps[] = $rec['ip'];
                    } elseif (! empty($rec['ipv6'])) {
                        $resolvedIps[] = $rec['ipv6'];
                    }
                }
            }

            if (empty($resolvedIps)) {
                $fallbackIp = @gethostbyname($host);
                if (! empty($fallbackIp) && $fallbackIp !== $host) {
                    $resolvedIps[] = $fallbackIp;
                }
            }
        }

        if (empty($resolvedIps)) {
            throw new InvalidArgumentException("Host '{$host}' tidak dapat dihubungi atau tidak memiliki catatan DNS publik yang valid.");
        }

        foreach ($resolvedIps as $ip) {
            // Validasi apakah IP berada pada rentang private / reserved
            $isPublic = filter_var(
                $ip,
                FILTER_VALIDATE_IP,
                FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
            );

            if ($isPublic === false) {
                throw new InvalidArgumentException("Alamat IP target ({$ip}) mengarah ke jaringan privat/lokal dan diblokir demi keamanan (SSRF Protection).");
            }

            // Blokir eksplisit cloud metadata IP (169.254.169.254) dan loopback
            if (str_starts_with($ip, '127.') || str_starts_with($ip, '169.254.') || $ip === '0.0.0.0' || $ip === '::1') {
                throw new InvalidArgumentException("Alamat IP target ({$ip}) diblokir demi keamanan server.");
            }
        }
    }
}
