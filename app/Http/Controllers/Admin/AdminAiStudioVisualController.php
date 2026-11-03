<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Ai\Platform\AiPlatformEngineService;
use App\Domain\Ai\Studio\Adapters\OpenAiImageAdapter;
use App\Domain\SocialMedia\AdminSocialMediaService;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminAiVisualAsset;
use App\Models\SocialMediaAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

final class AdminAiStudioVisualController extends Controller implements HasMiddleware
{
    /**
     * Daftarkan middleware otorisasi Superadmin / Marketing admin.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('require.admin.permission:admin.social_media.view', only: ['index']),
            new Middleware('require.admin.permission:admin.social_media.post', only: [
                'generate',
                'sendToSocialMedia',
                'toggleFavorite',
                'destroy',
            ]),
        ];
    }

    /**
     * Preset template prompt kampanye resmi COOCA untuk konten media sosial platform.
     */
    public const CAMPAIGN_PRESETS = [
        [
            'id' => 'promo_saas',
            'category' => 'Promosi & Diskon',
            'title' => 'Flash Sale Diskon Langganan SaaS',
            'ratio' => '1:1',
            'model' => 'gpt-image-2.5-flare',
            'quality' => 'high',
            'prompt' => 'Clean Apple-style 3D marketing visual banner for COOCA Business OS ERP, featuring sleek glassmorphic iPad display showing sales analytics charts, glowing golden badge "PROMO 50% ANNUAL PLAN", floating minimalist isometric icons of cash register and shopping cart, studio product lighting, soft volumetric gradient background in electric royal blue and warm amber, ultra-high resolution, hyper-detailed render.',
            'negative_prompt' => 'cluttered, blurry, low resolution, watermark, pixelated, ugly font, dark gloomy artifacts',
            'caption_suggestion' => '🚀 Tingkatkan Omzet & Rapikan Operasional Bisnis Anda 10x Lebih Cepat! Dapatkan potongan diskon 50% langganan tahunan COOCA Cloud ERP hari ini. Coba gratis sekarang di cooca.id! #BisnisNaikKelas #ERPUMKM #COOCA',
        ],
        [
            'id' => 'feature_launch',
            'category' => 'Fitur Baru',
            'title' => 'Peluncuran AI Studio & Omnichannel',
            'ratio' => '9:16',
            'model' => 'gpt-image-2.5-sunburst',
            'quality' => 'high',
            'prompt' => 'Vertical 9:16 Instagram Story visual showcasing futuristic AI Photo Studio inside mobile phone screen, magical sparkling particles transforming a raw photo into high-end studio product shoot, vibrant purple and neon cyan glow, minimalist typography overlay layout space, ultra-clean commercial aesthetic.',
            'negative_prompt' => 'ugly, lowres, text artifacts, bad anatomy, noise, watermark',
            'caption_suggestion' => '✨ Bikin foto katalog produk sekelas studio profesional cuma dalam 3 detik! Fitur AI Studio kini hadir resmi di aplikasi COOCA. Cek update terbaru di bio kami! #AIStudio #KatalogProduk #UMKMGoDigital',
        ],
        [
            'id' => 'umkm_tips',
            'category' => 'Edukasi Bisnis',
            'title' => 'Tips Kelola Arus Kas & Stok Toko',
            'ratio' => '1:1',
            'model' => 'gpt-image-2.5-flare',
            'quality' => 'standard',
            'prompt' => 'Minimalist modern editorial infographic visual illustrating financial balance and inventory management for retail shops, stylized 3D wooden ledger blocks, sleek green profit growth arrow, warm Scandinavian lighting, ivory and slate grey aesthetic, professional and trustworthy commercial style.',
            'negative_prompt' => 'distorted, messy, low quality, noisy, dark shadows',
            'caption_suggestion' => '💡 3 Kebiasaan Sederhana yang Bikin Arus Kas Bisnis Tetap Sehat: 1. Pisahkan rekening pribadi & bisnis. 2. Stock opname berkala. 3. Catat transaksi otomatis pakai POS. Simpan postingan ini untuk panduan tim Anda! #TipsBisnis #ManajemenKeuangan',
        ],
        [
            'id' => 'landscape_banner',
            'category' => 'Banner & Web',
            'title' => 'Landscape Banner LinkedIn & Website',
            'ratio' => '16:9',
            'model' => 'gpt-image-2.5-sunburst',
            'quality' => 'high',
            'prompt' => 'Wide 16:9 cinematic technology hero banner, modern Indonesian business owner smiling confidently in contemporary boutique cafe, sleek tablet showing COOCA cloud POS dashboard, warm natural sunlight streaming through glass window, crisp depth of field, premium editorial corporate photography.',
            'negative_prompt' => 'grainy, lowres, bad lighting, deformed hands, plastic skin',
            'caption_suggestion' => 'Solusi all-in-one yang dirancang khusus untuk membebaskan pemilik usaha dari kerumitan administrasi manual. Akses laporan laba-rugi realtime dari mana saja bersama COOCA. #EnterpriseSaaS #SmartBusiness',
        ],
        [
            'id' => 'quote_motivasi',
            'category' => 'Motivasi & Komunitas',
            'title' => 'Kutipan Inspirasi Wirausaha',
            'ratio' => '4:5',
            'model' => 'gpt-image-2.5-flare',
            'quality' => 'standard',
            'prompt' => 'Portrait 4:5 aesthetic coffee table aesthetic with notebook, fountain pen, and artisan coffee cup beside a modern laptop, soft morning shadows, cozy cafe atmosphere, ample elegant negative space for quote text, film photography tone.',
            'negative_prompt' => 'blur, low quality, oversaturated, artificial look',
            'caption_suggestion' => '"Bisnis yang hebat tidak dibangun dalam semalam, tapi dibangun dari konsistensi menyelesaikan hal-hal kecil setiap hari." Semangat menyambut hari produktif! ☕ #QuoteBisnis #MindsetWirausaha',
        ],
    ];

    public function __construct(
        protected AiPlatformEngineService $engineService,
        protected OpenAiImageAdapter $imageAdapter,
        protected AdminSocialMediaService $socialMediaService,
    ) {}

    /**
     * Tampilkan Studio Visual Media Sosial Admin.
     */
    public function index(Request $request): View
    {
        $aspectRatioFilter = (string) $request->query('ratio', 'all');
        $onlyFavorite = $request->boolean('favorite', false);

        $assetsQuery = AdminAiVisualAsset::query()->with('admin')->orderByDesc('created_at');

        if ($aspectRatioFilter !== 'all' && in_array($aspectRatioFilter, ['1:1', '9:16', '16:9', '4:5'], true)) {
            $assetsQuery->where('aspect_ratio', $aspectRatioFilter);
        }

        if ($onlyFavorite) {
            $assetsQuery->where('is_favorite', true);
        }

        $assets = $assetsQuery->paginate(12)->withQueryString();

        // Metrik Galeri Visual Admin
        $stats = [
            'total_assets' => AdminAiVisualAsset::count(),
            'total_favorites' => AdminAiVisualAsset::where('is_favorite', true)->count(),
            'total_1_1' => AdminAiVisualAsset::where('aspect_ratio', '1:1')->count(),
            'total_9_16' => AdminAiVisualAsset::where('aspect_ratio', '9:16')->count(),
        ];

        // Status Pool Kunci AI Platform
        $activeKeys = $this->engineService->getActiveImageKeys();
        $isEngineReady = ! empty($activeKeys) || ! empty(config('services.openai_image.api_key', env('OPENAI_IMAGE_API_KEY', '')));

        // Akun Medsos Platform Resmi yang Terhubung
        $platformAccounts = SocialMediaAccount::query()
            ->where(function ($q) {
                $q->where('is_platform', true)->orWhereNull('business_id');
            })
            ->where('status', 'active')
            ->orderBy('platform')
            ->get();

        $campaignPresets = self::CAMPAIGN_PRESETS;

        return view('admin.ai-studio.visual', compact(
            'assets',
            'stats',
            'isEngineReady',
            'activeKeys',
            'platformAccounts',
            'campaignPresets',
            'aspectRatioFilter',
            'onlyFavorite'
        ));
    }

    /**
     * Eksekusi render gambar visual AI menggunakan API Key Platform pool.
     */
    public function generate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'prompt'          => ['required', 'string', 'max:4000'],
            'negative_prompt' => ['nullable', 'string', 'max:1000'],
            'title'           => ['nullable', 'string', 'max:191'],
            'aspect_ratio'    => ['required', 'string', 'in:1:1,9:16,16:9,4:5'],
            'model_name'      => ['required', 'string', 'in:gpt-image-2.5-flare,gpt-image-2.5-sunburst,gpt-image-2'],
            'quality'         => ['required', 'string', 'in:standard,high,xhigh,low'],
        ]);

        /** @var Admin|null $admin */
        $admin = auth('admin')->user();
        if (! $admin) {
            return response()->json(['success' => false, 'message' => 'Otorisasi admin tidak valid.'], 403);
        }

        try {
            $payload = [
                'positive_prompt' => $validated['prompt'],
                'negative_prompt' => $validated['negative_prompt'] ?? null,
                'aspect_ratio'    => $validated['aspect_ratio'],
                'model_name'      => $validated['model_name'],
                'quality'         => $validated['quality'],
            ];

            $startTime = microtime(true);
            $generationResult = $this->imageAdapter->generate($payload);
            $latencyMs = (int) round((microtime(true) - $startTime) * 1000);

            if (($generationResult['status'] ?? '') !== 'completed' || empty($generationResult['images'])) {
                $errorMessage = $generationResult['error_message'] ?? 'Generasi visual AI gagal mengembalikan output gambar.';
                return response()->json(['success' => false, 'message' => $errorMessage], 422);
            }

            $primaryImage = $generationResult['images'][0];
            $imageUrlOrContent = $primaryImage['url'] ?? '';

            // Ekstraksi binary gambar
            $binary = $this->extractImageBinary($imageUrlOrContent);
            if (empty($binary)) {
                return response()->json(['success' => false, 'message' => 'Gagal membaca isi berkas gambar yang dihasilkan AI.'], 500);
            }

            // Simpan gambar ke storage public admin
            $uuid = (string) Str::uuid();
            $imageInfo = @getimagesizefromstring($binary);
            $width = $imageInfo ? (int) $imageInfo[0] : ($primaryImage['width'] ?? 1024);
            $height = $imageInfo ? (int) $imageInfo[1] : ($primaryImage['height'] ?? 1024);
            $mime = $imageInfo ? (string) $imageInfo['mime'] : 'image/png';
            $ext = match ($mime) {
                'image/jpeg' => 'jpg',
                'image/webp' => 'webp',
                default => 'png',
            };

            $fileName = "visual_{$uuid}.{$ext}";
            $folderPath = 'admin/ai-studio';
            $storagePath = "{$folderPath}/{$fileName}";

            Storage::disk('public')->put($storagePath, $binary);
            $publicUrl = Storage::disk('public')->url($storagePath);

            // Pastikan URL publik adalah URL absolut (HTTPS jika request menggunakan HTTPS)
            if (! str_starts_with($publicUrl, 'http://') && ! str_starts_with($publicUrl, 'https://')) {
                $publicUrl = url($publicUrl);
            }

            $title = ! empty($validated['title'])
                ? trim($validated['title'])
                : 'Materi Visual ' . strtoupper($validated['aspect_ratio']) . ' - ' . now()->translatedFormat('d M H:i');

            $asset = AdminAiVisualAsset::create([
                'admin_id'        => $admin->id,
                'title'           => $title,
                'prompt'          => $validated['prompt'],
                'negative_prompt' => $validated['negative_prompt'] ?? null,
                'model_name'      => $validated['model_name'],
                'aspect_ratio'    => $validated['aspect_ratio'],
                'quality'         => $validated['quality'],
                'file_path'       => $storagePath,
                'image_url'       => $publicUrl,
                'width'           => $width,
                'height'          => $height,
                'file_size'       => strlen($binary),
                'metadata'        => [
                    'latency_ms'  => $latencyMs,
                    'provider'    => $generationResult['provider'] ?? 'openai',
                    'usage'       => $generationResult['usage'] ?? null,
                    'created_by'  => $admin->name,
                ],
                'is_favorite'     => false,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Visual materi promosi berhasil dibuat dengan AI Studio!',
                'asset'   => [
                    'id'           => $asset->id,
                    'title'        => $asset->title,
                    'image_url'    => $asset->image_url,
                    'aspect_ratio' => $asset->aspect_ratio,
                    'width'        => $asset->width,
                    'height'       => $asset->height,
                    'model_name'   => $asset->model_name,
                    'created_at'   => $asset->created_at?->translatedFormat('d M Y, H:i'),
                ],
            ]);
        } catch (Throwable $e) {
            Log::error('AdminAiStudioVisualController::generate error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem saat memproses generasi gambar: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Kirim aset gambar langsung ke antrean postingan Social Media resmi platform COOCA.
     */
    public function sendToSocialMedia(Request $request, string $assetId): JsonResponse
    {
        $validated = $request->validate([
            'platforms'    => ['required', 'array', 'min:1'],
            'platforms.*'  => ['string', 'in:instagram,facebook,threads,tiktok,linkedin'],
            'content'      => ['required', 'string', 'max:5000'],
            'timing_mode'  => ['required', 'string', 'in:now,schedule'],
            'scheduled_at' => ['nullable', 'date'],
        ]);

        /** @var Admin|null $admin */
        $admin = auth('admin')->user();
        if (! $admin) {
            return response()->json(['success' => false, 'message' => 'Otorisasi admin tidak valid.'], 403);
        }

        $asset = AdminAiVisualAsset::findOrFail($assetId);

        try {
            $selectedPlatforms = array_values(array_unique($validated['platforms']));

            // Persiapkan payload postingan platform
            $postData = [
                'platforms'         => $selectedPlatforms,
                'platform'          => $selectedPlatforms[0] ?? 'instagram',
                'timing_mode'       => $validated['timing_mode'] === 'schedule' ? 'schedule_all' : 'now',
                'content'           => $validated['content'],
                'media_type'        => 'image',
                'media_urls'        => [$asset->image_url],
                'local_media_paths' => [$asset->file_path],
                'scheduled_at'      => $validated['timing_mode'] === 'schedule' ? $validated['scheduled_at'] : null,
            ];

            $post = $this->socialMediaService->createAndPublishPlatformPost($admin, $postData);

            // Simpan jejak publikasi di metadata aset
            $metadata = $asset->metadata ?? [];
            $publishedHistory = $metadata['published_posts'] ?? [];
            $publishedHistory[] = [
                'post_id'      => $post->id,
                'platforms'    => $selectedPlatforms,
                'published_at' => now()->toIso8601String(),
                'status'       => $post->status,
            ];
            $metadata['published_posts'] = $publishedHistory;
            $asset->update(['metadata' => $metadata]);

            $platformListStr = implode(', ', array_map('ucfirst', $selectedPlatforms));
            $successMsg = $validated['timing_mode'] === 'now'
                ? "Visual berhasil dikirim dan dipublikasikan ke {$platformListStr}!"
                : "Visual berhasil dijadwalkan untuk tayang di {$platformListStr}!";

            return response()->json([
                'success'  => true,
                'message'  => $successMsg,
                'post_id'  => $post->id,
                'status'   => $post->status,
                'redirect' => route('admin.social-media.index', ['tab' => 'posts']),
            ]);
        } catch (Throwable $e) {
            Log::error('AdminAiStudioVisualController::sendToSocialMedia error', [
                'asset_id' => $assetId,
                'error'    => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim visual ke media sosial: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Tandai atau hapus status favorit pada aset visual.
     */
    public function toggleFavorite(string $assetId): JsonResponse
    {
        $asset = AdminAiVisualAsset::findOrFail($assetId);
        $asset->is_favorite = ! $asset->is_favorite;
        $asset->save();

        return response()->json([
            'success'     => true,
            'is_favorite' => $asset->is_favorite,
            'message'     => $asset->is_favorite ? 'Visual ditambahkan ke daftar favorit.' : 'Visual dihapus dari favorit.',
        ]);
    }

    /**
     * Hapus berkas gambar dan data aset visual admin.
     */
    public function destroy(string $assetId): JsonResponse|RedirectResponse
    {
        $asset = AdminAiVisualAsset::findOrFail($assetId);

        if (! empty($asset->file_path) && Storage::disk('public')->exists($asset->file_path)) {
            Storage::disk('public')->delete($asset->file_path);
        }

        $asset->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Aset visual materi promosi berhasil dihapus.',
            ]);
        }

        return redirect()->route('admin.ai-studio.visual.index')->with('success', 'Aset visual berhasil dihapus.');
    }

    /**
     * Ekstraksi byte array binary dari data URL atau URL publik gambar.
     */
    protected function extractImageBinary(string $imageUrlOrContent): ?string
    {
        if (str_starts_with($imageUrlOrContent, 'data:image/')) {
            $parts = explode(',', $imageUrlOrContent, 2);
            return isset($parts[1]) ? base64_decode($parts[1]) : null;
        }

        if (str_starts_with($imageUrlOrContent, 'http://') || str_starts_with($imageUrlOrContent, 'https://')) {
            try {
                $response = Http::timeout(25)->get($imageUrlOrContent);
                if ($response->successful()) {
                    return $response->body();
                }
            } catch (Throwable $e) {
                Log::warning('Failed downloading image from URL: ' . $e->getMessage());
            }
        }

        return null;
    }
}

