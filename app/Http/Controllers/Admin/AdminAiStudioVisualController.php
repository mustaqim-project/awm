<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Ai\Platform\AiPlatformEngineService;
use App\Domain\Ai\Studio\Adapters\OpenAiImageAdapter;
use App\Domain\SocialMedia\AdminSocialMediaService;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminAiVisualAsset;
use App\Models\AiStudio\InspirationCategory;
use App\Models\AiStudio\InspirationItem;
use App\Models\AiStudio\InspirationSource;
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
            new Middleware('require.admin.permission:admin.social_media.view', only: ['index', 'inspirations']),
            new Middleware('require.admin.permission:admin.social_media.post', only: [
                'generate',
                'sendToSocialMedia',
                'toggleFavorite',
                'destroy',
            ]),
        ];
    }

    /**
     * Preset Template Kampanye Siap Pakai Platform COOCA.
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
            'prompt' => 'Wide 16:9 modern corporate announcement visual banner, floating 3D glass megaphone with subtle golden audio waves, polished dark slate podium, professional ambient lighting, deep navy blue background with soft geometric patterns, elegant ample space for title and schedule text.',
            'negative_prompt' => 'blurry, noise, low quality, ugly artifacts, watermark',
            'caption_suggestion' => '🚀 Solusi all-in-one ERP cloud multi-tenant untuk ribuan wirausaha modern Indonesia. Jelajahi ekosistem COOCA di cooca.id! #SaaSIndonesia #EnterpriseERP',
        ],
    ];

    /**
     * Kategori & Template Berbagai Macam Konten Resmi Platform COOCA.
     */
    public const CONTENT_TYPES = [
        'saas_promo' => [
            'id' => 'saas_promo',
            'label' => 'Promo & Diskon SaaS',
            'badge' => 'Promosi',
            'icon' => 'percent',
            'desc' => 'Banner promosi langganan ERP, flash sale, paket tahunan & penawaran spesial.',
            'default_ratio' => '1:1',
            'model' => 'gpt-image-2.5-flare',
            'quality' => 'high',
            'default_prompt' => 'Clean Apple-style 3D marketing visual banner for COOCA Business OS ERP, featuring sleek glassmorphic iPad display showing sales analytics charts, glowing golden badge "PROMO 50% ANNUAL PLAN", floating minimalist isometric icons of cash register and shopping cart, studio product lighting, soft volumetric gradient background in electric royal blue and warm amber, ultra-high resolution, hyper-detailed render.',
            'default_negative' => 'cluttered, blurry, low resolution, watermark, pixelated, ugly font, dark gloomy artifacts',
            'caption_suggestion' => '🚀 Tingkatkan Omzet & Rapikan Operasional Bisnis Anda 10x Lebih Cepat! Dapatkan potongan diskon 50% langganan tahunan COOCA Cloud ERP hari ini. Coba gratis sekarang di cooca.id! #BisnisNaikKelas #ERPUMKM #COOCA',
        ],
        'feature_launch' => [
            'id' => 'feature_launch',
            'label' => 'Peluncuran Fitur Baru',
            'badge' => 'Update Sistem',
            'icon' => 'sparkles',
            'desc' => 'Visual rilis fitur baru COOCA: AI Studio, POS Kasir, Omnichannel & Multi-Cabang.',
            'default_ratio' => '9:16',
            'model' => 'gpt-image-2.5-sunburst',
            'quality' => 'high',
            'default_prompt' => 'Vertical 9:16 Instagram Story visual showcasing futuristic AI Photo Studio inside mobile phone screen, magical sparkling particles transforming a raw product photo into high-end studio commercial shoot, vibrant purple and neon cyan glow, minimalist typography overlay layout space, ultra-clean commercial aesthetic.',
            'default_negative' => 'ugly, lowres, text artifacts, bad anatomy, noise, watermark',
            'caption_suggestion' => '✨ Bikin foto katalog produk sekelas studio profesional cuma dalam 3 detik! Fitur AI Studio kini hadir resmi di aplikasi COOCA. Cek update terbaru di bio kami! #AIStudio #KatalogProduk #UMKMGoDigital',
        ],
        'umkm_tips' => [
            'id' => 'umkm_tips',
            'label' => 'Tips & Edukasi Bisnis',
            'badge' => 'Edukasi',
            'icon' => 'book-open',
            'desc' => 'Infografis dan visual tips pengelolaan arus kas, stok opname, dan strategi harga.',
            'default_ratio' => '1:1',
            'model' => 'gpt-image-2.5-flare',
            'quality' => 'standard',
            'default_prompt' => 'Minimalist modern editorial infographic visual illustrating financial balance and inventory management for retail shops, stylized 3D wooden ledger blocks, sleek green profit growth arrow, warm Scandinavian lighting, ivory and slate grey aesthetic, professional and trustworthy commercial style.',
            'default_negative' => 'distorted, messy, low quality, noisy, dark shadows',
            'caption_suggestion' => '💡 3 Kebiasaan Sederhana yang Bikin Arus Kas Bisnis Tetap Sehat: 1. Pisahkan rekening pribadi & bisnis. 2. Stock opname berkala. 3. Catat transaksi otomatis pakai POS. Simpan postingan ini untuk panduan tim Anda! #TipsBisnis #ManajemenKeuangan',
        ],
        'announcement' => [
            'id' => 'announcement',
            'label' => 'Pengumuman & Event',
            'badge' => 'Info Resmi',
            'icon' => 'megaphone',
            'desc' => 'Poster pengumuman webinar, maintenance sistem, jam operasional, atau event wirausaha.',
            'default_ratio' => '16:9',
            'model' => 'gpt-image-2.5-sunburst',
            'quality' => 'high',
            'default_prompt' => 'Wide 16:9 modern corporate announcement visual banner, floating 3D glass megaphone with subtle golden audio waves, polished dark slate podium, professional ambient lighting, deep navy blue background with soft geometric patterns, elegant ample space for title and schedule text.',
            'default_negative' => 'blurry, noise, low quality, ugly artifacts, watermark',
            'caption_suggestion' => '📢 PENGUMUMAN PENTING: Ikuti Live Webinar Eksklusif "Strategi Otomasi Kasir & Pembukuan UMKM 2026" bersama tim COOCA. Daftar gratis melalui link di bio sekarang! #WebinarBisnis #COOCAEvent',
        ],
        'testimonial' => [
            'id' => 'testimonial',
            'label' => 'Testimoni & Kisah Sukses',
            'badge' => 'Social Proof',
            'icon' => 'award',
            'desc' => 'Review bintang 5, kisah sukses toko mitra UMKM, dan pencapaian omzet.',
            'default_ratio' => '4:5',
            'model' => 'gpt-image-2.5-flare',
            'quality' => 'high',
            'default_prompt' => 'Portrait 4:5 authentic warm portrait of an Indonesian retail shop owner smiling warmly behind clean contemporary cashier counter, glowing 3D golden five-star rating badge floating above tablet screen, natural warm morning sunlight, bokeh store background, uplifting entrepreneurial vibe.',
            'default_negative' => 'deformed face, unnatural plastic skin, blur, dark gloom, lowres',
            'caption_suggestion' => '⭐ "Sejak pakai COOCA, rekap penjualan harian dari 3 cabang toko selesai cuma dalam 5 menit tanpa selisih!" Terima kasih atas kepercayaan Toko Berkah Mandiri. Saatnya bisnis Anda juga naik kelas! #CeritaMitraCOOCA #TestimoniBisnis',
        ],
        'culture' => [
            'id' => 'culture',
            'label' => 'Kultur Tim & Lowongan',
            'badge' => 'Internal',
            'icon' => 'users',
            'desc' => 'Behind the scenes, rekrutmen talenta teknologi, perayaan tim, dan profil tim COOCA.',
            'default_ratio' => '16:9',
            'model' => 'gpt-image-2.5-flare',
            'quality' => 'standard',
            'default_prompt' => 'Wide 16:9 creative tech startup workspace visual, collaborative meeting area with modern standing desks, plants, sticky notes on glass partition, warm Scandinavian daylight, floating 3D neon badge "WE ARE HIRING", modern Indonesian developer team atmosphere, friendly and vibrant culture.',
            'default_negative' => 'messy, dark, low resolution, distorted anatomy, grainy',
            'caption_suggestion' => '🚀 Kami Sedang Mencari Talenta Terbaik untuk Bergabung Membangun Sistem Operasi Bisnis UMKM Indonesia! Cek posisi terbuka di cooca.id/careers dan kirimkan portfolio Anda sekarang. #Hiring #TechJobs #LifeAtCOOCA',
        ],
        'motivation' => [
            'id' => 'motivation',
            'label' => 'Motivasi & Komunitas',
            'badge' => 'Kutipan Harian',
            'icon' => 'coffee',
            'desc' => 'Quotes motivasi bisnis, mindset wirausaha, etos kerja, dan inspirasi harian.',
            'default_ratio' => '4:5',
            'model' => 'gpt-image-2.5-flare',
            'quality' => 'standard',
            'default_prompt' => 'Portrait 4:5 aesthetic coffee table aesthetic with notebook, fountain pen, and artisan coffee cup beside a modern laptop, soft morning shadows, cozy cafe atmosphere, ample elegant negative space for quote text, film photography tone.',
            'default_negative' => 'blur, low quality, oversaturated, artificial look',
            'caption_suggestion' => '"Bisnis yang hebat tidak dibangun dalam semalam, tapi dibangun dari konsistensi menyelesaikan hal-hal kecil setiap hari." Semangat menyambut hari produktif! ☕ #QuoteBisnis #MindsetWirausaha',
        ],
        'custom' => [
            'id' => 'custom',
            'label' => 'Kustom Bebas (Full Custom)',
            'badge' => 'Fleksibel',
            'icon' => 'palette',
            'desc' => 'Tulis prompt bebas tanpa batasan template, dengan dukungan panduan gaya & referensi.',
            'default_ratio' => '1:1',
            'model' => 'gpt-image-2.5-sunburst',
            'quality' => 'high',
            'default_prompt' => 'Commercial packshot banner visual for Indonesian business software, sleek 3D minimalist podium with metallic accents, volumetric softbox studio lighting, electric blue and amber glow, hyper-detailed rendering.',
            'default_negative' => 'blurry, low resolution, artifacts, distorted, noisy',
            'caption_suggestion' => 'Solusi cerdas untuk automasi dan pertumbuhan bisnis Anda bersama COOCA Business OS. Cek informasi selengkapnya di cooca.id! #COOCA',
        ],
    ];

    /**
     * Opsi Gaya Visual Populer.
     */
    public const VISUAL_STYLES = [
        '3d_apple_glass' => [
            'label' => 'Apple Clean 3D Glassmorphism',
            'prompt_fragment' => 'Ultra-clean modern Apple-inspired 3D glassmorphic render, smooth frosted glass translucent panels, polished metallic chrome bevels, floating isometric UI elements, crisp octane render 8k.',
        ],
        'photorealistic_commercial' => [
            'label' => 'Fotografi Komersial Studio Premium',
            'prompt_fragment' => 'High-end commercial studio product photography, shot on 85mm f/1.4 lens, ultra-sharp focus, shallow depth of field, authentic material textures, crisp reflections.',
        ],
        'bento_infographic' => [
            'label' => 'Modern Bento Grid Infografis',
            'prompt_fragment' => 'Sleek bento grid layout composition with rounded card containers, clean typographic hierarchy, stylized 3D data visualization charts, modern tech editorial aesthetic.',
        ],
        'cyberpunk_neon' => [
            'label' => 'Futuristic Cyberpunk Neon Tech',
            'prompt_fragment' => 'Dark sleek futuristic obsidian background, volumetric electric cyan and magenta neon edge highlights, holographic particle glow, high-tech industrial aesthetic.',
        ],
        'warm_scandinavian' => [
            'label' => 'Scandinavian Warm Editorial',
            'prompt_fragment' => 'Warm contemporary Scandinavian minimalist aesthetic, natural light oak wood textures, soft natural daylight, cozy indoor boutique atmosphere, muted earthy pastels.',
        ],
        'bold_pop_art' => [
            'label' => 'Bold & Vibrant Commercial Pop',
            'prompt_fragment' => 'High-energy bold pop commercial visual, punchy saturated colors, dynamic angled perspective, high visual contrast designed for maximum social media thumb-stopping engagement.',
        ],
        'claymorphism_cute' => [
            'label' => 'Soft Matte 3D Claymorphism',
            'prompt_fragment' => 'Friendly soft matte 3D claymorphic figures and objects, smooth rounded corners, gentle pastel clay textures, warm soft diffused shadows, approachable and welcoming.',
        ],
    ];

    /**
     * Opsi Skema Pencahayaan.
     */
    public const LIGHTING_STYLES = [
        'softbox_studio' => [
            'label' => 'Dual Studio Softbox Diffused',
            'prompt_fragment' => 'Professional dual studio softbox lighting with gentle wrap-around illumination and controlled soft shadows.',
        ],
        'morning_sunlight' => [
            'label' => 'Golden Hour Natural Sunlight',
            'prompt_fragment' => 'Warm natural golden hour morning sunlight streaming through window, creating soft cinematic long shadows and golden rim glow.',
        ],
        'dramatic_dark' => [
            'label' => 'Moody Dark Dramatic Contrast',
            'prompt_fragment' => 'Dramatic chiaroscuro studio lighting, deep rich shadow contrast with focused key light highlighting the subject.',
        ],
        'neon_rim' => [
            'label' => 'Volumetric Neon Rim Edge',
            'prompt_fragment' => 'Vibrant volumetric neon rim lighting creating glowing colored edge outlines around main elements in darkness.',
        ],
        'ambient_cozy' => [
            'label' => 'Warm Ambient Indoor Cafe',
            'prompt_fragment' => 'Warm 3000K ambient interior lighting with soft bokeh background and welcoming gentle highlights.',
        ],
    ];

    /**
     * Opsi Palet Warna Brand COOCA.
     */
    public const COLOR_PALETTES = [
        'cooca_signature' => [
            'label' => 'COOCA Royal Blue & Amber',
            'colors' => ['#007AFF', '#FF9500', '#F2F2F7', '#1C1C1E'],
            'prompt_fragment' => 'Dominant color palette of COOCA electric royal blue (#007AFF) accented with energetic amber gold (#FF9500) and clean neutral white.',
        ],
        'growth_emerald' => [
            'label' => 'Growth & Profit Emerald Green',
            'colors' => ['#34C759', '#059669', '#10B981', '#F0FDF4'],
            'prompt_fragment' => 'Vibrant financial growth color palette of fresh emerald green (#34C759) and mint accents on clean ivory background.',
        ],
        'luxury_midnight' => [
            'label' => 'Midnight Obsidian & Gold',
            'colors' => ['#09090B', '#F59E0B', '#27272A', '#D97706'],
            'prompt_fragment' => 'Sophisticated dark luxury palette of deep obsidian black, dark carbon grey, and refined metallic champagne gold highlights.',
        ],
        'creative_violet' => [
            'label' => 'Creative AI Electric Violet',
            'colors' => ['#5856D6', '#AF52DE', '#8B5CF6', '#FAF5FF'],
            'prompt_fragment' => 'Futuristic creative palette of deep electric violet (#5856D6) and magenta neon gradient glow.',
        ],
        'clean_minimal' => [
            'label' => 'Clean Ivory & Slate Grey',
            'colors' => ['#F8FAFC', '#64748B', '#0F172A', '#E2E8F0'],
            'prompt_fragment' => 'Monochrome minimalist palette of pure linen white, warm ivory, slate grey, and sharp dark typography tones.',
        ],
    ];

    /**
     * Opsi Nada Komunikasi & Persona Brand.
     */
    public const BRAND_TONES = [
        'modern_tech' => 'Modern Tech Clean (Apple HIG Aesthetic)',
        'friendly_umkm' => 'Ramah, Bersahaja & Dekat dengan UMKM Indonesia',
        'corporate' => 'Professional Corporate, Terpercaya & Elegan',
        'bold_promo' => 'Bold, Energik & High-Converting Call-To-Action',
        'fun_playful' => 'Kreatif, Segar & Menyenangkan',
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

        // Filter & Koleksi Galeri Gambar Inspirasi Visual COOCA (460+ Item Lokal)
        $inspSource = $request->query('insp_source', 'all');
        $inspCategory = $request->query('insp_category', 'all');
        $inspQuery = trim((string) $request->query('insp_q', ''));

        $inspirationQuery = InspirationItem::query()
            ->with(['source', 'category', 'visualAnalysis'])
            ->where('status', 'active');

        if (! empty($inspQuery)) {
            $inspirationQuery->where(function ($q) use ($inspQuery) {
                $q->where('title', 'LIKE', "%{$inspQuery}%")
                    ->orWhere('caption', 'LIKE', "%{$inspQuery}%")
                    ->orWhereJsonContains('tags', $inspQuery);
            });
        }

        if ($inspCategory !== 'all') {
            $inspirationQuery->whereHas('category', function ($q) use ($inspCategory) {
                $q->where('slug', $inspCategory);
            });
        }

        if ($inspSource !== 'all') {
            $inspirationQuery->whereHas('source', function ($q) use ($inspSource) {
                $q->where('code', $inspSource);
            });
        }

        $inspirationReferences = $inspirationQuery
            ->orderByDesc('view_count')
            ->paginate(24, ['*'], 'insp_page')
            ->withQueryString();

        // 8 Item Referensi Cepat untuk Strip di Form Generator
        $quickReferences = InspirationItem::query()
            ->with(['source', 'category', 'visualAnalysis'])
            ->where('status', 'active')
            ->orderByDesc('view_count')
            ->take(8)
            ->get()
            ->map(function (InspirationItem $item) {
                return [
                    'id'                => $item->id,
                    'title'             => $item->title,
                    'caption'           => $item->caption,
                    'display_image_url' => $item->display_image_url,
                    'thumbnail_url'     => $item->display_image_url,
                    'source_name'       => $item->source?->name ?? 'Katalog Visual',
                    'source_code'       => $item->source?->code ?? 'cooca',
                    'category_name'     => $item->category?->name ?? 'Umum',
                    'prompt_hint'       => $item->visualAnalysis?->extracted_style_prompt ?? $item->caption,
                ];
            })
            ->values()
            ->toArray();

        $sources = InspirationSource::where('is_active', true)->orderBy('name')->get();
        $categories = InspirationCategory::orderBy('sort_order')->get();
        $totalInspirationsCount = InspirationItem::where('status', 'active')->count();

        $contentTypes = self::CONTENT_TYPES;
        $campaignPresets = self::CAMPAIGN_PRESETS;
        $visualStyles = self::VISUAL_STYLES;
        $lightingStyles = self::LIGHTING_STYLES;
        $colorPalettes = self::COLOR_PALETTES;
        $brandTones = self::BRAND_TONES;

        return view('admin.ai-studio.visual', compact(
            'assets',
            'stats',
            'isEngineReady',
            'activeKeys',
            'platformAccounts',
            'contentTypes',
            'campaignPresets',
            'visualStyles',
            'lightingStyles',
            'colorPalettes',
            'brandTones',
            'inspirationReferences',
            'quickReferences',
            'sources',
            'categories',
            'totalInspirationsCount',
            'inspSource',
            'inspCategory',
            'inspQuery',
            'aspectRatioFilter',
            'onlyFavorite'
        ));
    }

    /**
     * API Live Search & Filter Galeri Inspirasi Visual untuk Modal Picker Admin.
     */
    public function inspirations(Request $request): JsonResponse
    {
        $query = trim((string) $request->input('q', ''));
        $categorySlug = trim((string) $request->input('category', 'all'));
        $sourceCode = trim((string) $request->input('source', 'all'));
        $limit = min(60, max(12, (int) $request->input('limit', 24)));

        $itemsQuery = InspirationItem::query()
            ->with(['source', 'category', 'visualAnalysis'])
            ->where('status', 'active');

        if (! empty($query)) {
            $itemsQuery->where(function ($q) use ($query) {
                $q->where('title', 'LIKE', "%{$query}%")
                    ->orWhere('caption', 'LIKE', "%{$query}%")
                    ->orWhereJsonContains('tags', $query);
            });
        }

        if ($categorySlug !== 'all') {
            $itemsQuery->whereHas('category', function ($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            });
        }

        if ($sourceCode !== 'all') {
            $itemsQuery->whereHas('source', function ($q) use ($sourceCode) {
                $q->where('code', $sourceCode);
            });
        }

        $items = $itemsQuery->orderByDesc('view_count')->take($limit)->get();

        $data = $items->map(function (InspirationItem $item) {
            $analysis = $item->visualAnalysis;

            return [
                'id'              => $item->id,
                'external_id'     => $item->external_id,
                'title'           => $item->title,
                'caption'         => $item->caption,
                'image_url'       => $item->display_image_url,
                'display_image_url' => $item->display_image_url,
                'preview_url'     => $item->display_image_url,
                'thumbnail_url'   => $item->thumbnail_url,
                'aspect_ratio'    => $item->visualAnalysis?->aspect_ratio ?? '1:1',
                'source_name'     => $item->source?->name ?? 'Katalog Visual',
                'source_code'     => $item->source?->code ?? 'cooca',
                'category_name'   => $item->category?->name ?? 'Umum',
                'tags'            => $item->tags ?? [],
                'color_palette'   => $analysis?->color_palette ?? [],
                'color_mood'      => $analysis?->color_mood,
                'lighting'        => $analysis?->lighting,
                'composition'     => $analysis?->composition,
                'prompt_hint'     => $analysis?->extracted_style_prompt ?? $item->caption,
                'style_prompt'    => $analysis?->extracted_style_prompt ?? $item->caption,
                'negative_prompt' => $analysis?->negative_style_prompt,
            ];
        });

        return response()->json([
            'success' => true,
            'total'   => $items->count(),
            'data'    => $data,
        ]);
    }

    /**
     * Eksekusi render gambar visual AI menggunakan API Key Platform pool,
     * mendukung gambar referensi (katalog / upload) dan kompilasi ketentuan khusus.
     */
    public function generate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'prompt'             => ['required', 'string', 'max:4000'],
            'negative_prompt'    => ['nullable', 'string', 'max:1000'],
            'title'              => ['nullable', 'string', 'max:191'],
            'aspect_ratio'       => ['required', 'string', 'in:1:1,9:16,16:9,4:5'],
            'model_name'         => ['required', 'string', 'in:gpt-image-2.5-flare,gpt-image-2.5-sunburst,gpt-image-2'],
            'quality'            => ['required', 'string', 'in:standard,high,xhigh,low'],
            'content_type'             => ['nullable', 'string', 'max:50'],
            'reference_id'             => ['nullable', 'string', 'max:100'],
            'reference_inspiration_id' => ['nullable', 'string', 'max:100'],
            'reference_image'          => ['nullable', 'file', 'image', 'max:10240'],
            'brand_tone'               => ['nullable', 'string', 'max:50'],
            'visual_style'             => ['nullable', 'string', 'max:50'],
            'lighting_style'           => ['nullable', 'string', 'max:50'],
            'color_scheme'             => ['nullable', 'string', 'max:50'],
            'color_palette'            => ['nullable', 'string', 'max:50'],
            'custom_directives'        => ['nullable', 'array'],
            'custom_directives.*'      => ['string', 'max:100'],
            'leave_space_for_text'     => ['nullable', 'string', 'max:10'],
            'include_ui_mockup'        => ['nullable', 'string', 'max:10'],
            'floating_3d_badge'        => ['nullable', 'string', 'max:10'],
            'indonesian_msme_context'  => ['nullable', 'string', 'max:10'],
            'headline_text'            => ['nullable', 'string', 'max:255'],
            'banner_headline'          => ['nullable', 'string', 'max:255'],
            'additional_rules'         => ['nullable', 'string', 'max:1000'],
        ]);

        /** @var Admin|null $admin */
        $admin = auth('admin')->user();
        if (! $admin) {
            return response()->json(['success' => false, 'message' => 'Otorisasi admin tidak valid.'], 403);
        }

        // Normalisasi alias input parameter
        if (empty($validated['reference_id']) && ! empty($validated['reference_inspiration_id'])) {
            $validated['reference_id'] = $validated['reference_inspiration_id'];
        }
        if (empty($validated['headline_text']) && ! empty($validated['banner_headline'])) {
            $validated['headline_text'] = $validated['banner_headline'];
        }
        if (empty($validated['color_scheme']) && ! empty($validated['color_palette'])) {
            $validated['color_scheme'] = $validated['color_palette'];
        }

        $customDirectives = (array) ($validated['custom_directives'] ?? []);
        foreach (['leave_space_for_text', 'include_ui_mockup', 'floating_3d_badge', 'indonesian_msme_context'] as $dirKey) {
            if ($request->boolean($dirKey)) {
                $customDirectives[$dirKey] = true;
            }
        }
        $validated['custom_directives'] = $customDirectives;

        try {
            // 1. Ekstraksi Gambar Referensi (Upload Manual atau Pilih Katalog)
            $referenceImagePath = null;
            $referenceImageUrl = null;
            $referenceTitle = null;
            $referenceStylePrompt = null;

            if ($request->hasFile('reference_image')) {
                $file = $request->file('reference_image');
                $refExt = $file->getClientOriginalExtension() ?: 'png';
                $refName = 'manual_ref_' . Str::uuid() . '.' . $refExt;
                $refStoragePath = 'admin/ai-studio/references/' . $refName;

                Storage::disk('public')->putFileAs('admin/ai-studio/references', $file, $refName);
                $referenceImagePath = storage_path('app/public/' . $refStoragePath);
                $referenceImageUrl = Storage::disk('public')->url($refStoragePath);
                $referenceTitle = 'Upload Referensi: ' . $file->getClientOriginalName();
            } elseif (! empty($validated['reference_id'])) {
                /** @var InspirationItem|null $refItem */
                $refItem = InspirationItem::with(['source', 'category', 'visualAnalysis'])->find($validated['reference_id']);
                if ($refItem) {
                    $referenceTitle = $refItem->title;
                    $referenceImageUrl = $refItem->display_image_url;
                    $referenceStylePrompt = $refItem->visualAnalysis?->extracted_style_prompt ?? $refItem->caption;

                    // Cari berkas lokal fisik
                    $rel = ltrim(str_replace('/storage/', '', $refItem->canonical_url), '/');
                    $absAppPath = storage_path('app/public/' . $rel);
                    $absPubPath = public_path('storage/' . $rel);

                    if (file_exists($absAppPath) && filesize($absAppPath) > 0) {
                        $referenceImagePath = $absAppPath;
                    } elseif (file_exists($absPubPath) && filesize($absPubPath) > 0) {
                        $referenceImagePath = $absPubPath;
                    }
                }
            }

            // 2. Susun Prompt Berdasarkan Ketentuan Khusus & Gaya
            $compiledPrompt = $this->compileEnrichedPrompt(
                rawPrompt: $validated['prompt'],
                contentType: $validated['content_type'] ?? null,
                visualStyle: $validated['visual_style'] ?? null,
                lightingStyle: $validated['lighting_style'] ?? null,
                colorScheme: $validated['color_scheme'] ?? null,
                brandTone: $validated['brand_tone'] ?? null,
                customDirectives: $validated['custom_directives'] ?? [],
                headlineText: $validated['headline_text'] ?? null,
                additionalRules: $validated['additional_rules'] ?? null,
                referenceTitle: $referenceTitle,
                referenceStylePrompt: $referenceStylePrompt
            );

            $payload = [
                'positive_prompt' => $compiledPrompt,
                'negative_prompt' => $validated['negative_prompt'] ?? null,
                'aspect_ratio'    => $validated['aspect_ratio'],
                'model_name'      => $validated['model_name'],
                'quality'         => $validated['quality'],
            ];

            // Pasang berkas gambar referensi jika tersedia
            if ($referenceImagePath && file_exists($referenceImagePath)) {
                $payload['reference_image_path'] = $referenceImagePath;
                $payload['reference_images'] = [$referenceImagePath];
            }

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

            if (! str_starts_with($publicUrl, 'http://') && ! str_starts_with($publicUrl, 'https://')) {
                $publicUrl = url($publicUrl);
            }

            $contentTypeMeta = self::CONTENT_TYPES[$validated['content_type'] ?? ''] ?? null;
            $defaultTitle = $contentTypeMeta
                ? ($contentTypeMeta['label'] . ' - ' . now()->translatedFormat('d M H:i'))
                : ('Materi Visual ' . strtoupper($validated['aspect_ratio']) . ' - ' . now()->translatedFormat('d M H:i'));

            $title = ! empty($validated['title']) ? trim($validated['title']) : $defaultTitle;

            $asset = AdminAiVisualAsset::create([
                'admin_id'        => $admin->id,
                'title'           => $title,
                'prompt'          => $compiledPrompt,
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
                    'latency_ms'            => $latencyMs,
                    'provider'              => $generationResult['provider'] ?? 'openai',
                    'usage'                 => $generationResult['usage'] ?? null,
                    'created_by'            => $admin->name,
                    'content_type'          => $validated['content_type'] ?? 'custom',
                    'brand_tone'            => $validated['brand_tone'] ?? null,
                    'visual_style'          => $validated['visual_style'] ?? null,
                    'lighting_style'        => $validated['lighting_style'] ?? null,
                    'color_scheme'          => $validated['color_scheme'] ?? null,
                    'custom_directives'     => $validated['custom_directives'] ?? [],
                    'headline_text'         => $validated['headline_text'] ?? null,
                    'reference_id'          => $validated['reference_id'] ?? null,
                    'reference_title'       => $referenceTitle,
                    'reference_image_url'   => $referenceImageUrl,
                    'original_user_prompt'  => $validated['prompt'],
                ],
                'is_favorite'     => false,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Visual materi promosi multi-konten berhasil dibuat dengan AI Studio!',
                'asset'   => [
                    'id'           => $asset->id,
                    'title'        => $asset->title,
                    'image_url'    => $asset->image_url,
                    'aspect_ratio' => $asset->aspect_ratio,
                    'width'        => $asset->width,
                    'height'       => $asset->height,
                    'model_name'   => $asset->model_name,
                    'created_at'   => $asset->created_at?->translatedFormat('d M Y, H:i'),
                    'metadata'     => $asset->metadata,
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
     * Kompilasi prompt yang diperkaya dengan ketentuan khusus, gaya visual, dan inspirasi.
     */
    protected function compileEnrichedPrompt(
        string $rawPrompt,
        ?string $contentType,
        ?string $visualStyle,
        ?string $lightingStyle,
        ?string $colorScheme,
        ?string $brandTone,
        array $customDirectives,
        ?string $headlineText,
        ?string $additionalRules,
        ?string $referenceTitle,
        ?string $referenceStylePrompt
    ): string {
        $parts = [];
        $parts[] = trim($rawPrompt);

        // Tambahkan gaya visual jika dipilih
        if (! empty($visualStyle) && isset(self::VISUAL_STYLES[$visualStyle])) {
            $parts[] = self::VISUAL_STYLES[$visualStyle]['prompt_fragment'];
        }

        // Tambahkan gaya pencahayaan jika dipilih
        if (! empty($lightingStyle) && isset(self::LIGHTING_STYLES[$lightingStyle])) {
            $parts[] = self::LIGHTING_STYLES[$lightingStyle]['prompt_fragment'];
        }

        // Tambahkan skema warna jika dipilih
        if (! empty($colorScheme) && isset(self::COLOR_PALETTES[$colorScheme])) {
            $parts[] = self::COLOR_PALETTES[$colorScheme]['prompt_fragment'];
        }

        // Tambahkan ketentuan khusus (Directives)
        $directiveInstructions = [];
        if (in_array('negative_space', $customDirectives, true) || ! empty($customDirectives['leave_space_for_text']) || in_array('leave_space_for_text', $customDirectives, true)) {
            $directiveInstructions[] = 'Composition must feature ample clean negative space in the upper or side third, specifically preserved for overlaid typography headline.';
        }
        if (in_array('mockup_device', $customDirectives, true) || ! empty($customDirectives['include_ui_mockup']) || in_array('include_ui_mockup', $customDirectives, true)) {
            $directiveInstructions[] = 'Feature sleek modern floating iPad or smartphone mockup displaying authentic contemporary COOCA cloud ERP dashboard UI.';
        }
        if (in_array('promo_badge', $customDirectives, true) || ! empty($customDirectives['floating_3d_badge']) || in_array('floating_3d_badge', $customDirectives, true)) {
            $directiveInstructions[] = 'Include a polished glossy 3D floating commercial badge or discount emblem with subtle metallic gold rim.';
        }
        if (in_array('indonesian_context', $customDirectives, true) || ! empty($customDirectives['indonesian_msme_context']) || in_array('indonesian_msme_context', $customDirectives, true)) {
            $directiveInstructions[] = 'Authentic modern Indonesian business context, friendly and approachable local entrepreneurial vibe.';
        }
        if (! empty($directiveInstructions)) {
            $parts[] = implode(' ', $directiveInstructions);
        }

        // Tambahkan headline teks jika ada
        if (! empty($headlineText)) {
            $parts[] = "Layout space tailored for commercial headline text: \"{$headlineText}\".";
        }

        // Tambahkan inspirasi gaya referensi jika ada
        if (! empty($referenceTitle) || ! empty($referenceStylePrompt)) {
            $refText = 'Visual aesthetics, lighting mood, and composition inspired by';
            if ($referenceTitle) {
                $refText .= " \"{$referenceTitle}\"";
            }
            if ($referenceStylePrompt) {
                $refText .= " ({$referenceStylePrompt})";
            }
            $parts[] = $refText . '.';
        }

        // Tambahkan aturan tambahan bebas dari admin
        if (! empty($additionalRules)) {
            $parts[] = trim($additionalRules);
        }

        return implode(' ', array_filter($parts));
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

