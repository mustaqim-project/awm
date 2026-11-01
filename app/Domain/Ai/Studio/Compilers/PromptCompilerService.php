<?php

declare(strict_types=1);

namespace App\Domain\Ai\Studio\Compilers;

use App\Domain\Ai\Inspiration\RightsPolicyService;
use App\Domain\Ai\Studio\Analyzers\BusinessAssetAnalyzer;
use App\Domain\Ai\Studio\Analyzers\VisualReferenceAnalyzer;
use App\Domain\Ai\Studio\Billing\CostQuoteService;
use App\Models\AiStudio\InspirationItem;
use App\Models\Business;
use App\Models\Product;
use Illuminate\Support\Str;

class PromptCompilerService
{
    /**
     * Kamus terjemahan cerdas istilah fotografi & e-commerce (Bahasa Indonesia -> Professional Studio English).
     */
    protected const STYLE_DICTIONARY = [
        // Latar Belakang & Permukaan (Background & Podium)
        'marmer' => 'luxurious white Italian Carrara marble slab with subtle grey veins',
        'marmer putih' => 'pristine polished white Italian Carrara marble surface with subtle grey veining',
        'marmer hitam' => 'sleek polished black Marquina marble surface with golden veining',
        'meja kayu' => 'solid rustic Indonesian teakwood table with rich organic grain texture',
        'kayu jati' => 'aged solid Indonesian teakwood surface with natural satin finish',
        'kayu' => 'warm natural organic timber surface with tactile woodgrain',
        'beton' => 'minimalist raw architectural polished concrete pedestal',
        'semen' => 'smooth architectural raw concrete slab with industrial aesthetic',
        'batu' => 'natural textured volcanic stone podium',
        'batu alam' => 'chiseled natural slate stone slab with organic chipped edges',
        'travertine' => 'porous beige natural travertine stone pedestal',
        'pasir' => 'fine warm coastal white sand with soft wind ripples',
        'pantai' => 'tropical serene coastline with soft-focus ocean sunrise in the background',
        'kain' => 'draped organic linen textile with soft natural folds',
        'kain sutra' => 'flowing lustrous silk drapery catching soft specular highlights',
        'akrilik' => 'crystal-clear transparent reflective acrylic riser',
        'kaca' => 'smoked mirror glass pedestal with delicate caustic reflections',
        'hitam doff' => 'sleek obsidian matte-black minimalist background',
        'hitam' => 'deep dark moody studio backdrop with dramatic contrast',
        'putih' => 'pure clean high-key minimalist commercial studio cyclorama background',
        'pastel' => 'soft aesthetic warm pastel gradient studio backdrop',
        'terakota' => 'warm Mediterranean terracotta stucco wall with architectural arches',

        // Pencahayaan & Suasana (Lighting & Ambiance)
        'senja' => 'warm golden hour directional sunlight with long soft shadows and amber glow',
        'sore' => 'golden hour afternoon sunlight with warm amber hues',
        'pagi' => 'bright crisp natural morning daylight with soft diffused window fill',
        'siang' => 'crisp clean architectural daylight with vivid contrast',
        'malam' => 'moody nighttime commercial studio lighting with warm glowing edge lights',
        'dramatis' => 'dramatic chiaroscuro low-key studio lighting with striking rim highlights',
        'lembut' => 'soft diffused overhead beauty softbox lighting with zero harsh shadows',
        'neon' => 'subtle dual-tone cyan and magenta neon edge rim lighting with atmospheric mist',

        // Aksen & Properti Pendukung (Accents & Props)
        'percikan air' => 'high-speed frozen water splash droplets glistening in mid-air',
        'splash' => 'dynamic frozen liquid splash with sparkling micro-droplets',
        'es batu' => 'crystal-clear transparent melting ice cubes with condensation beads',
        'embun' => 'delicate sparkling condensation dew droplets covering the surface',
        'uap' => 'soft appetizing aromatic steam wisps rising naturally',
        'asap' => 'delicate wisps of authentic warm steam rising gracefully',
        'daun mint' => 'fresh organic bright green mint leaves with dew beads',
        'daun monstera' => 'tropical organic monstera and palm leaf shadows cast across the set',
        'bunga' => 'tasteful minimalist dried botanicals and floral accents',
        'bunga kering' => 'curated dried pampas grass and botanical stems in soft blur',
        'cabai' => 'scattered vibrant fresh red cayenne chilies and raw shallot slices',
        'biji kopi' => 'freshly roasted shiny Arabica coffee beans scattered gracefully',
        'jeruk nipis' => 'fresh sliced juicy lime wedges with glistening pulp',
        'lemon' => 'fresh sliced yellow lemon with translucent pulp and water spray',
        'kayu manis' => 'fragrant whole cinnamon sticks and star anise spices',

        // Multi-Industri Khusus: Otomotif & Bengkel
        'oli' => 'premium synthetic motor engine oil bottle with golden amber liquid reflection',
        'oli mesin' => 'sleek synthetic motor oil container on metallic cast-iron brake disc rotor',
        'bengkel' => 'clean modern high-end automotive service bay with polished dark epoxy floor and overhead linear LEDs',
        'kunci pas' => 'gleaming chrome vanadium mechanic wrenches and precision workshop tools',
        'velg' => 'matte-black sports racing alloy wheel rim with vivid brake caliper reflection',
        'cuci mobil' => 'thick luxurious snow foam wash cascade covering glossy vehicle paintwork',
        'coating' => 'flawless high-gloss nano ceramic coating with ultra-reflective mirror finish and water beading',

        // Multi-Industri Khusus: Jasa, Barbershop & Laundry
        'barbershop' => 'classic vintage gentleman barbershop station with antique leather chair and warm tungsten bulbs',
        'gunting' => 'precision German steel barber scissors and textured carbon comb on dark leather mat',
        'pomade' => 'vintage aluminum pomade tin with glossy amber wax styling pomade texture',
        'laundry' => 'crisp professionally steam-ironed apparel hanging on minimalist organic wooden hangers with soft rising steam',
        'handuk' => 'plush organic fluffy white folded towels stacked in natural woven Scandinavian wicker basket',
        'spa' => 'serene luxury wellness spa set with flickering soy candle, bamboo mat, and black river basalt stones',

        // Multi-Industri Khusus: Apotek & Farmasi Herbal
        'obat' => 'modern pharmaceutical blister pack capsules on pristine sterile white glass laboratory surface',
        'botol obat' => 'sleek pharmaceutical amber glass medicine bottle with minimalist white prescription label',
        'kapsul' => 'golden translucent softgel capsules scattered gracefully near clean glass bottle',
        'madu' => 'pure golden raw organic honey dripping from wooden dipper into artisanal hexagonal glass jar',
        'herbal' => 'healing natural botanical herbal ingredients, dried roots, and fresh green leaves on porcelain mortar',

        // Multi-Industri Khusus: Percetakan & Kemasan
        'kemasan' => 'luxury matte unboxing rigid paper hardbox packaging with gold foil stamped typography',
        'box' => 'minimalist premium cardboard shipping box with elegant kraft texture and custom brand tape',
        'paper bag' => 'eco-friendly recycled kraft paper shopping bag with braided handles and letterpress tag',
        'kartu nama' => 'thick textured cotton letterpress business card mockup with debossed metallic gold typography',
        'sablon' => 'vivid high-density screen-printed graphic apparel on premium combed cotton backdrop',

        // Multi-Industri Khusus: Furniture & Interior
        'mebel' => 'handcrafted solid teakwood furniture piece with natural Scandinavian minimalist joinery',
        'sofa' => 'luxurious deep forest green velvet upholstered armchair in sunlit modern minimalist living room',
        'interior' => 'warm Japandi modern interior living space with natural wood slats, terrazzo tiles, and soft cove lighting',
        'anyaman' => 'intricate handmade Indonesian natural rattan and bamboo weave texture with tactile organic detail',

        // Multi-Industri Khusus: Kontraktor & Konstruksi
        'arsitektur' => 'crisp architectural blueprint drawings, drafting tools, and laser measure on raw architectural concrete desk',
        'helm proyek' => 'clean white safety hard hat and rolled building blueprint drawings on construction site',

        // Multi-Industri Khusus: Wedding & Event
        'wedding' => 'breathtaking romantic wedding banquet table setting with ivory white floral roses, crystal glassware, and gold flatware',
        'pelaminan' => 'luxurious modern rustic wedding stage backdrop with draped chiffon, dried pampas grass, and warm fairy lights',
        'mahar' => 'hexagonal crystal-clear acrylic wedding ring box cushioned with silk velvet and delicate dried edelweiss flowers',

        // Multi-Industri Khusus: Pertanian & Agro
        'sayur' => 'fresh vibrant organic hydroponic green butterhead lettuce and heirloom cherry tomatoes with crisp water droplets',
        'telur' => 'farm-fresh organic brown omega eggs nestled gently in natural golden straw nest inside wire basket',
        'padi' => 'heavy ripe golden rice grains on the stalk beaded with sparkling morning dew against terraced paddies',
    ];

    public function __construct(
        protected VisualReferenceAnalyzer $visualAnalyzer = new VisualReferenceAnalyzer,
        protected BusinessAssetAnalyzer $assetAnalyzer = new BusinessAssetAnalyzer,
        protected CostQuoteService $costQuoteService = new CostQuoteService,
        protected RightsPolicyService $rightsPolicyService = new RightsPolicyService
    ) {}

    /**
     * Kompilasi prompt rekayasa cerdas AI Studio (Autonomous Prompt Synthesizer).
     *
     * @param array<string, mixed> $brief
     * @return array{
     *     positive_prompt: string,
     *     negative_prompt: string,
     *     style_preset: string,
     *     aspect_ratio: string,
     *     dimensions: array{width: int, height: int},
     *     prompt_parameters: array<string, mixed>,
     *     guardrail_status: string,
     *     guardrail_notes: ?string,
     *     transparency_sheet: array<string, mixed>
     * }
     */
    public function compile(
        Business $business,
        ?Product $product = null,
        InspirationItem|array|null $reference = null,
        array $brief = []
    ): array {
        // 0. Hak Cipta & Kebijakan Lisensi Komersial (AC-002)
        if ($reference !== null) {
            $this->rightsPolicyService->assertCanUseCommercially($reference);
        }

        // 1. Analisis Visual Referensi
        $visual = $reference !== null
            ? $this->visualAnalyzer->analyze($reference)
            : $this->getDefaultVisualStructure();

        // 2. Analisis Aset Bisnis & Produk
        $asset = $this->assetAnalyzer->analyze($business, $product, $brief);

        // 3. Konfigurasi Brief & Style Preset
        $stylePreset = (string) ($brief['style_preset'] ?? 'minimalist_luxury');
        $aspectRatio = (string) ($brief['aspect_ratio'] ?? '1:1');
        $targetChannel = (string) ($brief['target_channel'] ?? 'instagram');
        $customNotes = trim((string) ($brief['custom_notes'] ?? $brief['custom_prompt'] ?? ''));

        // 4. Resolusi Dimensi & Biaya
        $quote = $this->costQuoteService->quote(
            provider: (string) ($brief['provider'] ?? 'ideogram'),
            modelName: (string) ($brief['model_name'] ?? 'ideogram-v2'),
            aspectRatio: $aspectRatio,
            batchCount: (int) ($brief['batch_count'] ?? 1),
            enableUpscale: (bool) ($brief['enable_upscale'] ?? false)
        );

        // 5. Sintesis Cerdas: Parse Instruksi Pengguna (Penggantian Kata, Tambahan, & Pengecualian)
        $userExclusions = [];
        $smartDirectives = $this->parseSmartUserDirectives($customNotes, $visual, $asset, $userExclusions);

        // 6. Susun Positive Master Prompt Berbasis AI Autonom
        $positivePrompt = $this->buildAutonomousMasterPrompt(
            visual: $visual,
            asset: $asset,
            stylePreset: $stylePreset,
            targetChannel: $targetChannel,
            customNotes: $customNotes,
            smartDirectives: $smartDirectives
        );

        // 7. Susun Negative Prompt & Anti-Hallucination Guardrails
        $negativePrompt = $this->buildNegativePrompt($visual, $asset, $userExclusions);

        // 8. Evaluasi Guardrail Keamanan Konten
        [$guardrailStatus, $guardrailNotes] = $this->evaluateGuardrails($positivePrompt, $customNotes);

        // 9. Parameter Teknis Eksekusi Model
        $promptParameters = [
            'provider' => $quote['provider'],
            'model_name' => $quote['model_name'],
            'aspect_ratio' => $aspectRatio,
            'width' => $quote['dimensions']['width'],
            'height' => $quote['dimensions']['height'],
            'batch_count' => $quote['batch_count'],
            'style_preset' => $stylePreset,
            'guidance_scale' => 7.5,
            'steps' => 30,
            'safety_tolerance' => 'standard',
        ];

        // 10. Lembar Transparansi untuk UI Bento Apple HIG
        $transparencySheet = [
            'inspiration_composition' => $visual['composition'],
            'inspiration_lighting' => $visual['lighting'],
            'inspiration_color_mood' => $visual['color_mood'],
            'inspiration_palette' => $visual['color_palette'],
            'negative_space' => $visual['negative_space'],
            'product_name' => $asset['product_name'],
            'brand_name' => $asset['brand_name'],
            'packaging_type' => $asset['packaging_type'],
            'target_channel' => $targetChannel,
            'estimated_credits' => $quote['credit_cost'],
            'preservation_summary' => $asset['preservation_rules'],
            'applied_substitutions' => $smartDirectives['substitutions'],
            'applied_additions' => $smartDirectives['additions'],
        ];

        return [
            'positive_prompt' => $positivePrompt,
            'negative_prompt' => $negativePrompt,
            'style_preset' => $stylePreset,
            'aspect_ratio' => $aspectRatio,
            'dimensions' => $quote['dimensions'],
            'prompt_parameters' => $promptParameters,
            'guardrail_status' => $guardrailStatus,
            'guardrail_notes' => $guardrailNotes,
            'transparency_sheet' => $transparencySheet,
        ];
    }

    /**
     * Parser Bahasa Alami Cerdas untuk Instruksi Pengguna (Penggantian Kata, Tambahan, & Pengecualian).
     *
     * @param array<string, mixed> $visual
     * @param array<string, mixed> $asset
     * @param array<int, string> $userExclusions
     * @return array{
     *     substitutions: array<int, string>,
     *     additions: array<int, string>,
     *     environment_override: ?string,
     *     lighting_override: ?string
     * }
     */
    protected function parseSmartUserDirectives(
        string $customNotes,
        array &$visual,
        array &$asset,
        array &$userExclusions
    ): array {
        $result = [
            'substitutions' => [],
            'additions' => [],
            'environment_override' => null,
            'lighting_override' => null,
        ];

        if ($customNotes === '') {
            return $result;
        }

        $notesLower = strtolower($customNotes);

        // 1. Pola Penggantian Elemen: "ganti X jadi Y", "ubah X menjadi Y", "replace X with Y"
        // Contoh: "ganti background jadi marmer putih", "ubah meja jadi semen ekspos", "ganti lampu jadi senja"
        $replacePattern = '/(?:ganti|ubah|tukar|replace)\s+([a-zA-Z0-9\s]+?)\s+(?:jadi|menjadi|dengan|ke|with)\s+([a-zA-Z0-9\s,]+?)(?=\s*(?:,|\.|$|dan\s|tambah|tanpa))/i';
        if (preg_match_all($replacePattern, $customNotes, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $rawTarget = trim(strtolower($match[1]));
                $rawReplacement = trim(strtolower($match[2]));

                $translatedReplacement = $this->translateTerm($rawReplacement);

                // Cek apakah mengganti background / alas / lantai / meja / podium
                if (str_contains($rawTarget, 'background') || str_contains($rawTarget, 'latar') || str_contains($rawTarget, 'meja') || str_contains($rawTarget, 'alas') || str_contains($rawTarget, 'lantai') || str_contains($rawTarget, 'podium')) {
                    $result['environment_override'] = "resting on {$translatedReplacement}";
                    $visual['composition'] = "podium featuring {$translatedReplacement}";
                    $result['substitutions'][] = "Mengganti {$rawTarget} menjadi {$rawReplacement}";
                }
                // Cek apakah mengganti cahaya / suasana / lighting
                elseif (str_contains($rawTarget, 'cahaya') || str_contains($rawTarget, 'lighting') || str_contains($rawTarget, 'suasana') || str_contains($rawTarget, 'lampu')) {
                    $result['lighting_override'] = "illuminated by {$translatedReplacement}";
                    $visual['lighting'] = $translatedReplacement;
                    $result['substitutions'][] = "Mengubah pencahayaan menjadi {$rawReplacement}";
                } else {
                    $result['substitutions'][] = "Mengganti {$rawTarget} dengan {$rawReplacement}";
                    $result['additions'][] = "accented with {$translatedReplacement}";
                }
            }
        }

        // 2. Pola Penambahan Properti: "tambahkan X", "tambah X", "kasih X", "add X"
        // Contoh: "tambahkan percikan air segar", "tambahkan es batu", "beri daun mint"
        $addPattern = '/(?:tambah|tambahkan|beri|kasih|add)\s+([a-zA-Z0-9\s,]+?)(?=\s*(?:,|\.|$|dan\s|ganti|tanpa))/i';
        if (preg_match_all($addPattern, $customNotes, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $rawItem = trim(strtolower($match[1]));
                $translatedItem = $this->translateTerm($rawItem);
                $result['additions'][] = "featuring {$translatedItem}";
                $asset['accent_elements'][] = $translatedItem;
            }
        }

        // 3. Pola Pengecualian / Penghapusan: "tanpa X", "hilangkan X", "jangan ada X", "no X"
        // Contoh: "tanpa bayangan keras", "hilangkan sendok", "jangan ada watermark"
        $excludePattern = '/(?:tanpa|hilangkan|hapus|jangan ada|no|without)\s+([a-zA-Z0-9\s,]+?)(?=\s*(?:,|\.|$|dan\s|ganti|tambah))/i';
        if (preg_match_all($excludePattern, $customNotes, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $rawExclude = trim(strtolower($match[1]));
                $translatedExclude = $this->translateTerm($rawExclude);
                $userExclusions[] = $translatedExclude;
                $userExclusions[] = $rawExclude;
            }
        }

        return $result;
    }

    /**
     * Menerjemahkan istilah lokal atau bebas ke terminologi fotografi komersial profesional.
     */
    protected function translateTerm(string $term): string
    {
        $termLower = trim(strtolower($term));

        foreach (self::STYLE_DICTIONARY as $key => $expansion) {
            if (str_contains($termLower, $key)) {
                return $expansion;
            }
        }

        // Fallback: jika istilah dalam bahasa inggris atau nama umum, format rapi
        return "authentic {$termLower}";
    }

    /**
     * Sintesis Master Positive Prompt Berbasis Rekayasa AI Otonom.
     *
     * @param array<string, mixed> $visual
     * @param array<string, mixed> $asset
     * @param array<string, mixed> $smartDirectives
     */
    protected function buildAutonomousMasterPrompt(
        array $visual,
        array $asset,
        string $stylePreset,
        string $targetChannel,
        string $customNotes,
        array $smartDirectives
    ): string {
        $parts = [];

        // 1. Standar Fotografi Komersial Tertinggi (Preset Master Tone)
        $presetTone = match ($stylePreset) {
            'cinematic' => 'Award-winning cinematic advertising photography, shallow depth of field, anamorphic lens flare',
            'editorial' => 'High-fashion editorial commercial photoshoot, avant-garde studio set, Vogue aesthetic',
            'vibrant_pop' => 'Bold commercial pop advertising, energetic saturated colors, high contrast dynamic lighting',
            'hyperreal_fnb' => 'Ultra-crisp food & beverage culinary commercial packshot, appetizing textures, mouthwatering droplets',
            default => 'Pristine minimalist luxury commercial product photography, immaculate studio lighting, Apple product design aesthetic',
        };
        $parts[] = $presetTone;

        // 2. Subjek Utama: Fisik Objek Riil yang Di-upload atau Dipilih Merchant
        $parts[] = "Hero centerpiece showcasing authentic {$asset['physical_descriptor']}";

        // 3. Tata Letak, Podium & Lingkungan (Memperhitungkan Override Pengguna)
        if (!empty($smartDirectives['environment_override'])) {
            $parts[] = $smartDirectives['environment_override'];
        } else {
            $parts[] = "carefully positioned in {$visual['composition']} arrangement with {$visual['negative_space']} composition";
        }

        // 4. Pencahayaan & Suasana Warna (Memperhitungkan Override Pengguna)
        if (!empty($smartDirectives['lighting_override'])) {
            $parts[] = $smartDirectives['lighting_override'];
        } else {
            $paletteStr = implode(', ', (array) ($visual['color_palette'] ?? []));
            $parts[] = "illuminated by {$visual['lighting']} lighting, refined {$visual['color_mood']} atmosphere, harmonious color grading ({$paletteStr})";
        }

        // 5. Elemen Aksen Tambahan dari AI & Hasil Parser Pengguna
        $accentPool = array_unique(array_merge(
            (array) ($asset['accent_elements'] ?? []),
            $smartDirectives['additions']
        ));

        if (!empty($accentPool)) {
            $accentsStr = implode(', ', array_slice($accentPool, 0, 4));
            $parts[] = "tastefully accented with {$accentsStr}";
        }

        // 6. Gaya Spesifik Ekstraksi Template Referensi (jika ada)
        if (!empty($visual['extracted_style_prompt']) && strlen($visual['extracted_style_prompt']) > 15) {
            // Bersihkan placeholder generic seperti [Nama Produk]
            $cleanRefPrompt = str_ireplace(
                ['[nama produk]', '[product name]', 'product name', 'nama produk'],
                $asset['product_name'],
                $visual['extracted_style_prompt']
            );
            $parts[] = "artistic visual influence: {$cleanRefPrompt}";
        }

        // 7. Pengoptimalan Kanal Tujuan (Instagram, Shopee, TikTok, dsb)
        $channelEnhancement = match ($targetChannel) {
            'tiktok' => 'optimized for vertical 9:16 smartphone screen, eye-catching visual hook',
            'shopee', 'tokopedia' => 'clean e-commerce hero image, clear crisp product visibility, zero background clutter',
            default => 'premium social media showcase, highly shareable aesthetic',
        };
        $parts[] = $channelEnhancement;

        // 8. Kualitas Fotografi & Realisme Taktil (Camera Science)
        $parts[] = 'Hasselblad H6D-100c medium format camera, 85mm f/2.8 macro lens, hyper-realistic material textures, razor sharp focus, 8k resolution, commercial advertising masterpiece';

        return implode(', ', $parts);
    }

    /**
     * Membangun negative prompt standar anti-distorsi dan menyerap pengecualian kustom pengguna.
     *
     * @param array<string, mixed> $visual
     * @param array<string, mixed> $asset
     * @param array<int, string> $userExclusions
     */
    protected function buildNegativePrompt(array $visual, array $asset, array $userExclusions = []): string
    {
        $baseNegative = [
            'distorted packaging',
            'warped bottle silhouette',
            'mutated product logo',
            'illegible gibberish text',
            'fake certification stamps',
            'deformed hands',
            'extra fingers',
            'blurry',
            'low resolution',
            'oversaturated cheap filter',
            'unnatural artifacts',
            'amateur snapshot',
            'muddy shadows',
            'watermark',
            'copyright notice',
        ];

        if (! empty($visual['negative_style_prompt'])) {
            $baseNegative[] = $visual['negative_style_prompt'];
        }

        // Gabungkan elemen yang diminta oleh pengguna untuk dihilangkan
        if (! empty($userExclusions)) {
            foreach ($userExclusions as $exclusion) {
                $baseNegative[] = trim($exclusion);
            }
        }

        return implode(', ', array_unique($baseNegative));
    }

    /**
     * Evaluasi guardrail keamanan konten (mencegah klaim palsu, konten sensitif).
     *
     * @return array{0: string, 1: ?string}
     */
    protected function evaluateGuardrails(string $positivePrompt, string $customNotes): array
    {
        $corpus = strtolower("{$positivePrompt} {$customNotes}");

        $blockedTerms = [
            'porn', 'nudity', 'weapon', 'gun', 'fake bpom', 'fake halal',
            'cure cancer', 'guaranteed cure', 'nazi', 'racist',
        ];

        foreach ($blockedTerms as $term) {
            if (str_contains($corpus, $term)) {
                return ['blocked', "Prompt terdeteksi memuat istilah terlarang: '{$term}'."];
            }
        }

        // Flagging ringan untuk kata klaim medis
        $flaggedTerms = ['obat dokter', '100% sembuh', 'tanpa izin'];
        foreach ($flaggedTerms as $flag) {
            if (str_contains($corpus, $flag)) {
                return ['flagged', "Prompt mengandung klaim sensitif ('{$flag}'), disarankan untuk ditinjau ulang."];
            }
        }

        return ['passed', null];
    }

    /**
     * Struktur visual default jika pengguna memulai dari nol tanpa gambar referensi.
     *
     * @return array<string, mixed>
     */
    protected function getDefaultVisualStructure(): array
    {
        return [
            'composition' => 'minimalist_podium',
            'lighting' => 'studio_soft',
            'camera_angle' => 'eye_level',
            'color_palette' => ['#FFFFFF', '#D4AF37', '#2C2C2C'],
            'color_mood' => 'clean_luxury',
            'negative_space' => 'top_third_clean',
            'style_descriptors' => ['commercial product photography', 'studio lighting', 'crisp sharp focus'],
            'extracted_style_prompt' => 'clean commercial product photo on stone podium with diffuse lighting',
            'negative_style_prompt' => 'blurry, distorted packaging, altered brand logo, unreadable text, lowres',
            'suggested_aspect_ratios' => ['1:1', '4:5', '9:16'],
            'safety_score' => 100.0,
        ];
    }
}
