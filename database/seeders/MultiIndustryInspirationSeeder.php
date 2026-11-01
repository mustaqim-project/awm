<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AiStudio\InspirationCategory;
use App\Models\AiStudio\InspirationItem;
use App\Models\AiStudio\InspirationSource;
use App\Models\AiStudio\InspirationSourcePolicy;
use App\Models\AiStudio\InspirationTrendScore;
use App\Models\AiStudio\InspirationVisualAnalysis;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MultiIndustryInspirationSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ensure source exists
        $source = InspirationSource::firstOrCreate(
            ['code' => 'cooca_curated'],
            [
                'name' => 'Cooca Multi-Industry Studio',
                'driver_class' => 'App\\Domain\\Ai\\Inspiration\\Adapters\\GrokImageAdapter',
                'is_active' => true,
                'fetch_frequency_minutes' => 60,
                'rate_limit_per_minute' => 60,
                'supports_image' => true,
                'supports_video' => false,
            ]
        );

        if (! $source->policy) {
            InspirationSourcePolicy::create([
                'source_id' => $source->id,
                'allow_commercial_use' => true,
                'require_attribution' => false,
                'retention_days' => 365,
                'disclaimer_template' => 'Referensi visual komersial multi-industri resmi Cooca.',
            ]);
        }

        // 2. Comprehensive Multi-Industry Categories
        $categoriesData = [
            ['slug' => 'makanan-minuman', 'name' => 'Makanan & Minuman (F&B)', 'industry' => 'fnb', 'sort_order' => 1],
            ['slug' => 'fashion-tekstil', 'name' => 'Fashion & Konveksi', 'industry' => 'fashion', 'sort_order' => 2],
            ['slug' => 'kecantikan-skincare', 'name' => 'Kecantikan & Skincare', 'industry' => 'beauty', 'sort_order' => 3],
            ['slug' => 'jasa-otomotif', 'name' => 'Bengkel & Otomotif', 'industry' => 'automotive', 'sort_order' => 4],
            ['slug' => 'jasa-lifestyle', 'name' => 'Jasa, Salon & Laundry', 'industry' => 'service', 'sort_order' => 5],
            ['slug' => 'ritel-kelontong', 'name' => 'Ritel & Toko Kelontong', 'industry' => 'retail', 'sort_order' => 6],
            ['slug' => 'kesehatan-farmasi', 'name' => 'Apotek, Farmasi & Herbal', 'industry' => 'pharmacy', 'sort_order' => 7],
            ['slug' => 'manufaktur-percetakan', 'name' => 'Percetakan & Kemasan', 'industry' => 'printing', 'sort_order' => 8],
            ['slug' => 'furniture-craft', 'name' => 'Furniture & Kerajinan', 'industry' => 'furniture', 'sort_order' => 9],
            ['slug' => 'kontraktor-interior', 'name' => 'Kontraktor & Bangunan', 'industry' => 'contractor', 'sort_order' => 10],
            ['slug' => 'event-wedding', 'name' => 'Event & Wedding Organizer', 'industry' => 'event', 'sort_order' => 11],
            ['slug' => 'distribusi-fmcg', 'name' => 'Distributor FMCG & Grosir', 'industry' => 'distributor', 'sort_order' => 12],
            ['slug' => 'pertanian-agro', 'name' => 'Pertanian & Peternakan', 'industry' => 'agriculture', 'sort_order' => 13],
            ['slug' => 'viral-trending', 'name' => 'Viral & Trending Multi-Industri', 'industry' => 'general', 'sort_order' => 14],
            ['slug' => 'poster-komersial', 'name' => 'Poster & Iklan Komersial', 'industry' => 'general', 'sort_order' => 15],
        ];

        $categories = [];
        foreach ($categoriesData as $c) {
            $cat = InspirationCategory::updateOrCreate(
                ['slug' => $c['slug']],
                [
                    'name' => $c['name'],
                    'industry' => $c['industry'],
                    'sort_order' => $c['sort_order'],
                    'is_active' => true,
                ]
            );
            $categories[$c['slug']] = $cat;
        }

        // 3. Multi-Industry Curated Items Dataset
        $multiIndustryItems = [
            // --- BENGKEL & OTOMOTIF ---
            [
                'external_id' => 'curated_auto_01',
                'title' => 'Botol Oli Mesin Sintetis di Meja Bengkel Mekanik',
                'caption' => 'Fotografi komersial botol oli mesin mobil di atas piringan rem cakram dengan aksen percikan cairan emas dan kunci pas chrome.',
                'industry' => 'automotive',
                'category_slug' => 'jasa-otomotif',
                'tags' => ['otomotif', 'bengkel', 'oli mesin', 'sparepart', 'commercial', 'studio lighting'],
                'gradient_from' => '#0F172A',
                'gradient_to' => '#EA580C',
                'icon_type' => 'oil_bottle',
                'prompt' => 'Commercial studio photography of a premium sleek synthetic motor engine oil bottle resting on a chiseled cast-iron brake disc rotor, golden amber oil droplets suspended, flanked by chrome vanadium wrenches and mechanical workshop textures, dramatic low-angle rim lighting, deep charcoal metallic background, high contrast, 85mm f/1.8 lens, Hasselblad H6D-100c detail, hyper-realistic 8k.',
                'composition' => 'Low-angle dynamic hero shot with mechanical tools framing',
                'lighting' => 'Dual-rim amber and cyan specular highlights with low-key studio backdrop',
                'color_palette' => ['#0F172A', '#EA580C', '#F59E0B', '#334155'],
                'color_mood' => 'Industrial Precision & High Performance',
                'visual_hook' => 'Golden liquid refraction and gleaming chrome reflections',
                'cta_pattern' => 'Ganti Oli Berkala, Mesin Awet Bertenaga',
            ],
            [
                'external_id' => 'curated_auto_02',
                'title' => 'Velg Mobil Sport Hitam Doff dengan Busa Snow Wash',
                'caption' => 'Inspirasi promosi jasa cuci mobil hidrolik & salon detailing dengan limpahan busa salju tebal dan kilap velg alloy.',
                'industry' => 'automotive',
                'category_slug' => 'jasa-otomotif',
                'tags' => ['carwash', 'detailing', 'cuci mobil', 'snow wash', 'velg racing', 'salon mobil'],
                'gradient_from' => '#0284C7',
                'gradient_to' => '#0369A1',
                'icon_type' => 'car_wheel',
                'prompt' => 'Macro commercial photography of a luxury matte-black concave sports car wheel rim dripping with dense ultra-white snow foam shampoo, high-gloss brake caliper peeking through, clean wet reflections on dark epoxy garage floor, sharp water beading droplets, bright studio overhead softbox, 100mm macro lens, ultra-clean commercial look.',
                'composition' => 'Tight macro 45-degree angle showcasing texture and water droplets',
                'lighting' => 'High-key overhead diffused softbox with crisp specular reflections on wet paintwork',
                'color_palette' => ['#0284C7', '#0F172A', '#FFFFFF', '#38BDF8'],
                'color_mood' => 'Pristine Clean & Refreshing High-Gloss',
                'visual_hook' => 'Dense snowy foam cascading across sharp metallic edges',
                'cta_pattern' => 'Cuci Mobil Kinclong Bebas Jamur Mulai 40rb',
            ],
            [
                'external_id' => 'curated_auto_03',
                'title' => 'Botol Serum Nano Ceramic Coating Kap Mesin Mengkilap',
                'caption' => 'Promosi paket salon mobil coating keramik dengan efek hydrofobik butiran air bulat sempurna di atas cat hitam cermin.',
                'industry' => 'automotive',
                'category_slug' => 'jasa-otomotif',
                'tags' => ['auto detailing', 'nano ceramic', 'coating', 'salon mobil', 'paint protection'],
                'gradient_from' => '#1E1B4B',
                'gradient_to' => '#4338CA',
                'icon_type' => 'spray_bottle',
                'prompt' => 'Luxury product advertising photography of a small frosted glass nano-ceramic coating dropper bottle standing proudly on an impeccably polished mirror-finish black car hood, perfect spherical water beads rolling off the surface, deep infinity reflection, subtle blue and violet neon edge lighting, ultra-premium cinematic aesthetic.',
                'composition' => 'Centered minimalist podium reflection with negative space for branding',
                'lighting' => 'Cinematic strip softbox creating a flawless linear reflection line',
                'color_palette' => ['#1E1B4B', '#4338CA', '#818CF8', '#000000'],
                'color_mood' => 'Ultra-Luxury Automotive Protection',
                'visual_hook' => 'Crystal-clear mirror reflections and hydrophobic water beads',
                'cta_pattern' => 'Proteksi Cat Mobil Tahan Gores & Cuaca Ekstrem',
            ],

            // --- JASA, BARBERSHOP, SALON & LAUNDRY ---
            [
                'external_id' => 'curated_serv_01',
                'title' => 'Peralatan Barbershop Vintage di Matras Kulit Cokelat',
                'caption' => 'Gunting rambut stainless steel klasik, sisir kayu, dan pisau cukur lipat di atas meja kayu barber shop bertekstur.',
                'industry' => 'service',
                'category_slug' => 'jasa-lifestyle',
                'tags' => ['barbershop', 'pangkas rambut', 'salon pria', 'gentleman grooming', 'vintage tools'],
                'gradient_from' => '#451A03',
                'gradient_to' => '#78350F',
                'icon_type' => 'scissors',
                'prompt' => 'Flatlay editorial photography of authentic German stainless steel barber scissors, hand-carved sandalwood comb, classic straight razor with wooden handle, and a vintage aluminum pomade tin neatly arranged on aged distressed dark brown saddle leather, warm golden tungsten studio lighting, soft shadows, masculine artisan vibe.',
                'composition' => 'Geometric flatlay with clean alignment and textured negative space',
                'lighting' => 'Warm 3200K vintage tungsten side lighting with rich shadow gradation',
                'color_palette' => ['#451A03', '#78350F', '#D97706', '#E2E8F0'],
                'color_mood' => 'Classic Heritage & Gentleman Craftsmanship',
                'visual_hook' => 'Gleaming steel scissor blades against weathered natural leather',
                'cta_pattern' => 'Potong Rambut Presisi & Hair Spa Pria',
            ],
            [
                'external_id' => 'curated_serv_02',
                'title' => 'Tumpukan Handuk Putih Bersih di Keranjang Laundry Segar',
                'caption' => 'Display higienis tumpukan handuk katun putih bersih lembut dengan uap halus dan tangkai bunga lavender relaksasi.',
                'industry' => 'service',
                'category_slug' => 'jasa-lifestyle',
                'tags' => ['laundry', 'laundry kiloan', 'cuci setrika', 'linen bersih', 'parfum laundry'],
                'gradient_from' => '#065F46',
                'gradient_to' => '#047857',
                'icon_type' => 'laundry_basket',
                'prompt' => 'Bright high-key lifestyle photography of a tall stack of freshly laundered, ultra-plush fluffy white cotton towels nestled in a light woven Scandinavian wicker basket, soft natural morning sunlight streaming from a sheer curtain window, delicate sprig of dried French lavender resting on top, pristine white minimalist laundry room background, crisp fresh aroma aesthetic.',
                'composition' => 'Eye-level organic stack with warm cozy window backlighting',
                'lighting' => 'Diffused natural morning window light with bright airy shadows',
                'color_palette' => ['#065F46', '#10B981', '#FFFFFF', '#F1F5F9'],
                'color_mood' => 'Crisp Freshness & Ultimate Hygiene',
                'visual_hook' => 'Fluffy fibrous towel textures and calming lavender accent',
                'cta_pattern' => 'Laundry Kiloan Bersih, Rapi & Wangi 3 Hari',
            ],
            [
                'external_id' => 'curated_serv_03',
                'title' => 'Kemeja Formal Menggantung dengan Uap Setrika Halus',
                'caption' => 'Kemeja katun premium licin tanpa kusut tergantung di gantungan kayu dengan kepulan uap setrika profesional.',
                'industry' => 'service',
                'category_slug' => 'jasa-lifestyle',
                'tags' => ['laundry satuan', 'dry clean', 'steam iron', 'setrika uap', 'pakaian rapi'],
                'gradient_from' => '#1E293B',
                'gradient_to' => '#475569',
                'icon_type' => 'shirt_steam',
                'prompt' => 'Commercial studio photography of an impeccably pressed sky-blue Egyptian cotton dress shirt on an elegant dark walnut wooden hanger, subtle wisps of fine warm steam rising around the collar, minimalist grey textured fabric background, crisp studio softbox lighting highlighting the razor-sharp crease lines, luxury valet service aesthetic.',
                'composition' => 'Centered vertical hero with delicate floating steam effect',
                'lighting' => 'Soft overhead commercial softbox with subtle backlight separating the fabric',
                'color_palette' => ['#1E293B', '#38BDF8', '#E0F2FE', '#94A3B8'],
                'color_mood' => 'Executive Neatness & Impeccable Care',
                'visual_hook' => 'Crisp collar lines and delicate atmospheric steam wisps',
                'cta_pattern' => 'Dry Clean & Setrika Uap Express 24 Jam',
            ],

            // --- APOTEK, FARMASI & HERBAL ---
            [
                'external_id' => 'curated_pharma_01',
                'title' => 'Botol Kapsul Herbal Amber Glass dengan Daun Alami Segar',
                'caption' => 'Produk suplemen herbal alami dalam botol kaca gelap farmasi dengan daun ginkgo, habbatussauda, dan mortar keramik putih.',
                'industry' => 'pharmacy',
                'category_slug' => 'kesehatan-farmasi',
                'tags' => ['apotek', 'obat herbal', 'suplemen', 'farmasi', 'vitamin', 'amber glass'],
                'gradient_from' => '#14532D',
                'gradient_to' => '#15803D',
                'icon_type' => 'pill_bottle',
                'prompt' => 'Pharmaceutical commercial product photography of an amber glass supplement bottle with a clean white minimalist label, resting on a chiseled white marble slab, golden translucent omega-3 softgel capsules scattered artfully, fresh green medicinal botanicals and white porcelain apothecary mortar in soft background blur, crisp clean lab lighting, depth of field.',
                'composition' => 'Clean rule-of-thirds product hero with natural organic props',
                'lighting' => 'Clinical soft daylight balanced with warm amber glass transmittance',
                'color_palette' => ['#14532D', '#B45309', '#FDE68A', '#FFFFFF'],
                'color_mood' => 'Scientific Purity & Natural Holistic Health',
                'visual_hook' => 'Luminous glowing translucent softgels and amber reflections',
                'cta_pattern' => 'Suplemen Alami BPOM & Halal untuk Imun Tubuh',
            ],
            [
                'external_id' => 'curated_pharma_02',
                'title' => 'Toples Madu Murni Alami dengan Sendok Ulir Kayu Menetes',
                'caption' => 'Madu hutan murni keemasan kental menetes dari sendok kayu dengan sarang lebah asli dan bunga liar di meja kayu.',
                'industry' => 'pharmacy',
                'category_slug' => 'kesehatan-farmasi',
                'tags' => ['madu murni', 'herbal', 'sarang lebah', 'kesehatan', 'organik'],
                'gradient_from' => '#78350F',
                'gradient_to' => '#D97706',
                'icon_type' => 'honey_jar',
                'prompt' => 'Commercial food and health photography of an artisanal hexagonal glass jar filled with rich golden raw honey, a carved wooden honey dipper held above with a thick viscous golden stream dripping down, real geometric honeycomb chunk and wild yellow field flowers beside it, warm sunset backlight catching the amber liquid, macro 90mm.',
                'composition' => 'Dynamic vertical action drip frozen in mid-air',
                'lighting' => 'Strong warm backlight making the viscous honey glow intensely like liquid amber',
                'color_palette' => ['#78350F', '#D97706', '#FBBF24', '#FEF3C7'],
                'color_mood' => 'Warm Organic Wholesomeness & Vitality',
                'visual_hook' => 'Golden glowing honey ribbon suspended in motion',
                'cta_pattern' => 'Madu Hutan 100% Murni Tanpa Campuran Gula',
            ],

            // --- PERCETAKAN, PACKAGING & MERCHANDISE ---
            [
                'external_id' => 'curated_print_01',
                'title' => 'Hardbox Kemasan Mewah dengan Hot Stamping Foil Emas',
                'caption' => 'Mockup kemasan kotak kaku eksklusif dengan efek deboss foil emas mengkilap di atas meja abu-abu minimalis.',
                'industry' => 'printing',
                'category_slug' => 'manufaktur-percetakan',
                'tags' => ['percetakan', 'packaging', 'hardbox', 'foil emas', 'box hampers', 'cetak kemasan'],
                'gradient_from' => '#18181B',
                'gradient_to' => '#CA8A04',
                'icon_type' => 'package_box',
                'prompt' => 'Luxury packaging design mockup photography of a matte-black rigid gift hardbox with metallic reflective gold foil debossed typography on the lid, lid slightly ajar revealing premium interior velvet lining, standing on a minimalist architectural concrete plinth, sharp linear studio lighting catching the metallic shimmer of the gold foil, ultra-sharp detail.',
                'composition' => 'Architectural isometric 3D angle with sharp lines',
                'lighting' => 'Chiaroscuro directional studio light highlighting debossed metallic foil',
                'color_palette' => ['#18181B', '#CA8A04', '#FDE047', '#3F3F46'],
                'color_mood' => 'Exclusive Luxury & Premium Unboxing Experience',
                'visual_hook' => 'Brilliant metallic foil gleam against deep matte black cardboard',
                'cta_pattern' => 'Cetak Box & Kemasan Produk Custom Mulai 100 Pcs',
            ],
            [
                'external_id' => 'curated_print_02',
                'title' => 'Paper Bag Kraft Cokelat Ramah Lingkungan & Hangtag',
                'caption' => 'Paper bag berbahan kraft tebal alami dengan tali rami jinjing dan hangtag kartu nama gantung estetik.',
                'industry' => 'printing',
                'category_slug' => 'manufaktur-percetakan',
                'tags' => ['paper bag', 'kraft', 'eco friendly', 'cetak paperbag', 'hangtag'],
                'gradient_from' => '#713F12',
                'gradient_to' => '#A16207',
                'icon_type' => 'shopping_bag',
                'prompt' => 'Editorial commercial photography of a premium ribbed brown kraft paper shopping bag with sturdy braided rope handles and an attached letterpress gift tag, standing on a raw limestone block, dried eucalyptus sprig leaning gently, clean soft afternoon shadow patterns, warm eco-conscious sustainable branding aesthetic.',
                'composition' => 'Clean Scandinavian minimalist product showcase',
                'lighting' => 'Diffused warm daylight with soft organic leaf shadow casting',
                'color_palette' => ['#713F12', '#A16207', '#CA8A04', '#FEF08A'],
                'color_mood' => 'Earth-Friendly Sustainability & Warm Craft',
                'visual_hook' => 'Tactile organic paper grain texture and woven handle fibers',
                'cta_pattern' => 'Paper Bag Ramah Lingkungan untuk Butik & Cafe',
            ],

            // --- FURNITURE, MEBEL & KERAJINAN ---
            [
                'external_id' => 'curated_furn_01',
                'title' => 'Kursi Armchair Kayu Jati Minimalis di Ruang Tamu Sunlit',
                'caption' => 'Mebel kursi santai kombinasi kayu jati solid dan kain linen krem di ruang keluarga bernuansa Japandi minimalis.',
                'industry' => 'furniture',
                'category_slug' => 'furniture-craft',
                'tags' => ['furniture', 'mebel jepara', 'kayu jati', 'japandi', 'armchair', 'interior'],
                'gradient_from' => '#3F2C1D',
                'gradient_to' => '#854D0E',
                'icon_type' => 'armchair',
                'prompt' => 'Architectural Digest style interior photography of a handcrafted solid Indonesian teakwood lounge armchair with natural woven cane backing and beige textured linen cushion, positioned in a serene sunlit living room with herringbone oak parquet floor, fiddle-leaf fig tree in ceramic pot, warm architectural shadows, 50mm f/2.8.',
                'composition' => 'Spacious eye-level architectural room composition',
                'lighting' => 'Soft golden afternoon sunlight streaming across wooden floor grain',
                'color_palette' => ['#3F2C1D', '#854D0E', '#D97706', '#F5F5F4'],
                'color_mood' => 'Timeless Serenity & Natural Organic Warmth',
                'visual_hook' => 'Rich woodgrain joinery curves and tactile linen weaves',
                'cta_pattern' => 'Mebel Kayu Jati Solid Garansi Kualitas Ekspor',
            ],
            [
                'external_id' => 'curated_furn_02',
                'title' => 'Lampu Meja Keramik Handmade dengan Cahaya Hangat',
                'caption' => 'Lampu meja keramik gerabah artistik bertekstur dengan kap kain linen memancarkan cahaya warm white temaram.',
                'industry' => 'furniture',
                'category_slug' => 'furniture-craft',
                'tags' => ['kerajinan', 'handmade', 'lampu meja', 'keramik', 'home decor'],
                'gradient_from' => '#7C2D12',
                'gradient_to' => '#C2410C',
                'icon_type' => 'lamp',
                'prompt' => 'Cozy twilight interior commercial photography of an artisan hand-thrown terracotta ceramic table lamp glowing with warm ambient light, sitting on a floating wooden nightstand next to open hardcover book and ceramic mug, dark moody bedroom backdrop with soft acoustic panel texture, serene Scandinavian hygge atmosphere.',
                'composition' => 'Intimate diagonal angle focusing on warm luminous lamp glow',
                'lighting' => 'Luminous 2700K warm incandescent glow contrasting against cool twilight shadows',
                'color_palette' => ['#7C2D12', '#C2410C', '#FB923C', '#1C1917'],
                'color_mood' => 'Intimate Hygge & Artisanal Serenity',
                'visual_hook' => 'Tactile chipped terracotta texture illuminated by soft interior bulb',
                'cta_pattern' => 'Dekorasi Rumah Artistik Karya Pengrajin Lokal',
            ],

            // --- KONTRAKTOR, BANGUNAN & INTERIOR ---
            [
                'external_id' => 'curated_bld_01',
                'title' => 'Helm Proyek Putih & Gambar Arsitektur Blueprint di Meja Beton',
                'caption' => 'Simbol profesionalisme kontraktor: helm proyek safety putih, gulungan blueprint arsitektur, dan meteran laser di lokasi proyek.',
                'industry' => 'contractor',
                'category_slug' => 'kontraktor-interior',
                'tags' => ['kontraktor', 'arsitek', 'renovasi rumah', 'blueprint', 'proyek bangunan'],
                'gradient_from' => '#1E3A8A',
                'gradient_to' => '#2563EB',
                'icon_type' => 'hard_hat',
                'prompt' => 'Commercial engineering photography of a pristine white construction safety hard hat resting beside detailed architectural blueprint technical floor plans, high-precision digital laser distance meter with glowing red beam, rolled tracing paper, placed on a smooth raw architectural concrete slab, crisp industrial studio spotlighting, corporate construction branding.',
                'composition' => 'Dynamic geometric flatlay with precision drafting instruments',
                'lighting' => 'Overhead clean commercial spotlight with cool daylight temperature',
                'color_palette' => ['#1E3A8A', '#2563EB', '#60A5FA', '#F8FAFC'],
                'color_mood' => 'Engineering Excellence & Structural Trust',
                'visual_hook' => 'Crisp blue drafting lines and high-gloss safety helmet reflection',
                'cta_pattern' => 'Jasa Bangun & Renovasi Rumah Bergaransi Konstruksi',
            ],

            // --- EVENT & WEDDING ORGANIZER ---
            [
                'external_id' => 'curated_wed_01',
                'title' => 'Jamuan Pernikahan Romantis dengan Buket Bunga Mawar & Lilin Kaca',
                'caption' => 'Set meja perjamuan makan malam pernikahan mewah dengan buket mawar putih, gelas kristal, sendok garpu emas, dan lilin temaram.',
                'industry' => 'event',
                'category_slug' => 'event-wedding',
                'tags' => ['wedding organizer', 'event organizer', 'dekorasi pernikahan', 'pelaminan', 'meja jamuan'],
                'gradient_from' => '#831843',
                'gradient_to' => '#BE185D',
                'icon_type' => 'wedding_glass',
                'prompt' => 'Luxury wedding editorial photography of an opulent banquet reception table setting, featuring overflowing fresh white garden roses, eucalyptus garlands, glowing crystal candle hurricanes, brushed gold cutlery, fine bone china plateware, sheer ivory silk chiffon table runner, soft romantic fairy lights bokeh in dark background, 85mm f/1.4 lens, magical dreamy ambiance.',
                'composition' => 'Shallow depth of field leading line down the banquet table',
                'lighting' => 'Warm romantic candle glow blended with soft twinkling bokeh lights',
                'color_palette' => ['#831843', '#BE185D', '#F472B6', '#FEF08A'],
                'color_mood' => 'Romantic Opulence & Enchanting Celebration',
                'visual_hook' => 'Twinkling crystal candle reflections and delicate white rose petals',
                'cta_pattern' => 'Wujudkan Pernikahan Impian Tanpa Beban Stres',
            ],

            // --- PERTANIAN & AGRO ---
            [
                'external_id' => 'curated_agro_01',
                'title' => 'Sayuran Hijau Segar Selada Hidroponik Berembun Baru Panen',
                'caption' => 'Display selada hidroponik hijau segar royan dengan butiran embun air bersih dalam wadah kayu organik hasil tani modern.',
                'industry' => 'agriculture',
                'category_slug' => 'pertanian-agro',
                'tags' => ['pertanian', 'hidroponik', 'sayur segar', 'organik', 'hasil tani', 'agro'],
                'gradient_from' => '#14532D',
                'gradient_to' => '#16A34A',
                'icon_type' => 'leaf_lettuce',
                'prompt' => 'Commercial agricultural photography of crisp vibrant green butterhead hydroponic lettuce heads, glistening with fresh fine water spray mist beads, arranged in a rustic cedar wooden harvesting crate, accompanied by sweet red cherry tomatoes on the vine, morning sun flare in background, clean natural organic farm freshness, macro detail.',
                'composition' => 'Overhead 45-degree angle bursting with fresh farm produce',
                'lighting' => 'Golden morning sunlight with translucent green backlighting through leaves',
                'color_palette' => ['#14532D', '#16A34A', '#4ADE80', '#DC2626'],
                'color_mood' => 'Pure Farm Freshness & Organic Vitality',
                'visual_hook' => 'Translucent emerald green leaves dripping with crystal-clear dew',
                'cta_pattern' => 'Sayur Segar Petik Pagi Langsung dari Petani Mitra',
            ],
            [
                'external_id' => 'curated_agro_02',
                'title' => 'Telur Ayam Omega Organik di Sarang Jerami Tradisional',
                'caption' => 'Telur ayam omega cokelat segar mulus bersinar tersusun rapi di atas sarang jerami kering alami.',
                'industry' => 'agriculture',
                'category_slug' => 'pertanian-agro',
                'tags' => ['peternakan', 'telur omega', 'telur ayam', 'organik', 'fresh farm'],
                'gradient_from' => '#78350F',
                'gradient_to' => '#B45309',
                'icon_type' => 'egg_nest',
                'prompt' => 'Warm rustic product photography of six organic free-range brown omega eggs with smooth satin shells, nestled snugly in a natural golden straw nest inside a wire basket, golden morning sunlight grazing across the shell curves, subtle wooden barnwood backdrop, clean healthy ethical poultry farming concept.',
                'composition' => 'Centered nest hero with warm textured straw surround',
                'lighting' => 'Warm directional golden hour sun casting soft oval shadows',
                'color_palette' => ['#78350F', '#B45309', '#FDE68A', '#FEF3C7'],
                'color_mood' => 'Nurturing Organic Wholesomeness & Quality',
                'visual_hook' => 'Smooth glowing eggshells nestled in coarse golden straw fibers',
                'cta_pattern' => 'Telur Ayam Omega Segar Tiap Hari untuk Keluarga',
            ],

            // --- DISTRIBUTOR & FMCG ---
            [
                'external_id' => 'curated_dist_01',
                'title' => 'Display Rak Minimarket Rapi dengan Kemasan Produk Berjajar',
                'caption' => 'Inspirasi etalase toko retail modern dan grosir sembako dengan penataan rapi berstandar visual merchandising.',
                'industry' => 'retail',
                'category_slug' => 'ritel-kelontong',
                'tags' => ['minimarket', 'toko kelontong', 'retail', 'sembako', 'display produk'],
                'gradient_from' => '#0F172A',
                'gradient_to' => '#0284C7',
                'icon_type' => 'shelf_display',
                'prompt' => 'Clean architectural commercial photography of a modern bright minimarket shelf display, featuring neatly aligned packaged goods with clear shelf edge price tags, spotless white LED linear lighting, glossy polished white floor reflections, deep symmetrical leading lines, professional visual merchandising aesthetic.',
                'composition' => 'Symmetrical one-point perspective down the grocery aisle',
                'lighting' => 'Bright even 5000K commercial retail linear LED lighting',
                'color_palette' => ['#0F172A', '#0284C7', '#38BDF8', '#F8FAFC'],
                'color_mood' => 'Modern Retail Abundance & Impeccable Order',
                'visual_hook' => 'Satisfying geometric alignment and bright pristine aisles',
                'cta_pattern' => 'Belanja Hemat Sembako & Kebutuhan Rumah Tangga',
            ],

            // --- TAMBAHAN BENGKEL & OTOMOTIF ---
            [
                'external_id' => 'curated_auto_04',
                'title' => 'Mesin Motor Sport V-Twin dengan Knalpot Titanium Biru Terbakar',
                'caption' => 'Detail mekanis presisi mesin motor performa tinggi dengan aksen las knalpot titanium burnt-blue dan pencahayaan dramatis bengkel pit stop.',
                'industry' => 'automotive',
                'category_slug' => 'jasa-otomotif',
                'tags' => ['bengkel motor', 'knalpot titanium', 'mesin balap', 'service motor', 'sparepart racing'],
                'gradient_from' => '#0F172A',
                'gradient_to' => '#2563EB',
                'icon_type' => 'oil_bottle',
                'prompt' => 'Macro studio photography of a precision motorcycle V-Twin engine block with polished burnt-blue titanium exhaust headers, intricate cooling fins, braided stainless steel fuel lines, dark moody garage background with subtle neon amber reflections, high contrast chiaroscuro, 85mm f/2.',
                'composition' => 'Low-angle diagonal hero macro of metallic mechanical craftsmanship',
                'lighting' => 'Chiaroscuro rim lighting emphasizing the gradient heat-treated titanium colors',
                'color_palette' => ['#0F172A', '#2563EB', '#38BDF8', '#D97706'],
                'color_mood' => 'Raw Mechanical Power & Precision Engineering',
                'visual_hook' => 'Luminescent rainbow-blue heat tint on polished titanium metal',
                'cta_pattern' => 'Tune-Up & Servis Performa Motor Terpercaya',
            ],

            // --- TAMBAHAN BARBER & SPA ---
            [
                'external_id' => 'curated_serv_04',
                'title' => 'Kursi Pangkas Rambut Vintage Kulit Marun di Hadapan Cermin Klasik',
                'caption' => 'Suasana interior barbershop mewah dengan kursi pangkas rambut kulit merah marun antik, rangka besi krom, dan cermin berbingkai kayu jati.',
                'industry' => 'service',
                'category_slug' => 'jasa-lifestyle',
                'tags' => ['barbershop', 'interior salon', 'kursi barber', 'pangkas rambut premium', 'gentleman club'],
                'gradient_from' => '#450A0A',
                'gradient_to' => '#7F1D1D',
                'icon_type' => 'scissors',
                'prompt' => 'Atmospheric architectural photography of an authentic heavy-duty vintage Belmont barber chair upholstered in oxblood red tufted leather with gleaming chrome hydraulic base, positioned in front of a grand arched antique mirror, warm Edison bulbs glowing, exposed red brick wall, masculine luxury barbershop ambiance.',
                'composition' => 'Centered regal hero portrait of the classic barber chair',
                'lighting' => 'Warm incandescent Edison bulb glow balanced with soft directional daylight',
                'color_palette' => ['#450A0A', '#7F1D1D', '#F59E0B', '#1C1917'],
                'color_mood' => 'Nostalgic Luxury & Timeless Gentleman Heritage',
                'visual_hook' => 'Rich oxblood leather tufting and mirror reflections',
                'cta_pattern' => 'Rasakan Pengalaman Cukur Rambut Klasik Bintang Lima',
            ],
            [
                'external_id' => 'curated_serv_05',
                'title' => 'Botol Minyak Esensial & Lilin Aromaterapi Spa di Meja Relaksasi',
                'caption' => 'Produk relaksasi spa dan salon: botol pipet minyak aromaterapi dengan lilin kedelai menyala, kelopak bunga melati, dan batu kali hangat.',
                'industry' => 'service',
                'category_slug' => 'jasa-lifestyle',
                'tags' => ['salon kecantikan', 'spa', 'aromaterapi', 'massage', 'minyak esensial'],
                'gradient_from' => '#064E3B',
                'gradient_to' => '#047857',
                'icon_type' => 'spray_bottle',
                'prompt' => 'Serene luxury wellness spa photography of an amber glass essential oil dropper bottle standing next to a flickering soy wax candle in ceramic vessel, smooth black river basalt massage stones, fresh jasmine blossoms, dark rustic bamboo mat, soft steam wisps, calming zen meditation mood.',
                'composition' => 'Intimate zen triangle composition with flickering flame focal point',
                'lighting' => 'Soft golden candlelight balanced with soothing dim ambient green fill',
                'color_palette' => ['#064E3B', '#047857', '#FDE68A', '#1C1917'],
                'color_mood' => 'Deep Zen Relaxation & Holistic Rejuvenation',
                'visual_hook' => 'Warm flickering candle flame and glossy basalt stone reflections',
                'cta_pattern' => 'Paket Treatment Spa & Pijat Relaksasi Tubuh',
            ],

            // --- TAMBAHAN APOTEK & FARMASI ---
            [
                'external_id' => 'curated_pharma_03',
                'title' => 'Minyak Kayu Putih Alami Botol Kaca Hijau & Daun Segar Berembun',
                'caption' => 'Botol kaca hijau minyak kayu putih alami tradisional dengan tetesan embun, daun kayu putih asli, dan nuansa herbal alami.',
                'industry' => 'pharmacy',
                'category_slug' => 'kesehatan-farmasi',
                'tags' => ['minyak kayu putih', 'herbal', 'minyak atsiri', 'apotek', 'obat gosok'],
                'gradient_from' => '#064E3B',
                'gradient_to' => '#0D9488',
                'icon_type' => 'pill_bottle',
                'prompt' => 'Refreshing commercial photography of a heritage emerald green glass essential oil bottle with a natural cork stopper, sitting on a wet slate stone podium with fresh eucalyptus leaves and aromatic cloves, crystal-clear water droplets sprayed across the set, bright crisp morning lighting, pure herbal aroma aesthetic.',
                'composition' => 'Vertical hero on textured dark slate with dewy botanicals',
                'lighting' => 'Crisp morning daylight catching the translucent green glass glow',
                'color_palette' => ['#064E3B', '#0D9488', '#5EEAD4', '#FFFFFF'],
                'color_mood' => 'Crisp Herbal Healing & Soothing Freshness',
                'visual_hook' => 'Glowing emerald glass clarity and glistening dew droplets',
                'cta_pattern' => 'Minyak Kayu Putih Murni Hangat & Melegakan',
            ],

            // --- TAMBAHAN PERCETAKAN & SABLON ---
            [
                'external_id' => 'curated_print_03',
                'title' => 'Kaos Polos Cotton Combed Sablon Plastisol Cerah di Gantungan',
                'caption' => 'Mockup kaos distro premium dengan cetak sablon presisi detail tinggi, serat kain katun combed 30s lembut, dan pencahayaan studio konveksi.',
                'industry' => 'printing',
                'category_slug' => 'manufaktur-percetakan',
                'tags' => ['konveksi', 'sablon kaos', 't-shirt distro', 'plastisol', 'cetak baju'],
                'gradient_from' => '#1E1B4B',
                'gradient_to' => '#4338CA',
                'icon_type' => 'shirt_steam',
                'prompt' => 'Commercial apparel photography of a jet-black heavy cotton t-shirt hanging on an industrial matte-steel pipe rack, featuring a vivid multi-color screen-printed graphic art on the chest, tactile cotton knit weave texture clearly visible, clean architectural concrete background, sharp directional softbox lighting.',
                'composition' => 'Centered eye-level apparel showcase with sharp fabric draping',
                'lighting' => 'High-contrast studio lighting highlighting screen print ink density',
                'color_palette' => ['#1E1B4B', '#4338CA', '#F43F5E', '#FFFFFF'],
                'color_mood' => 'Urban Streetwear Boldness & Premium Material Quality',
                'visual_hook' => 'Vibrant textured screen print ink standing out on deep black cotton',
                'cta_pattern' => 'Jasa Sablon Kaos & Konveksi Partai Besar / Kecil',
            ],

            // --- TAMBAHAN FURNITURE & INTERIOR ---
            [
                'external_id' => 'curated_furn_03',
                'title' => 'Meja Makan Kayu Trembesi Solid Utuh dengan Kaki Besi Hitam',
                'caption' => 'Meja makan slab kayu utuh natural edge dengan keindahan serat kayu alami, kursi modern, dan lampu gantung estetik di ruang makan.',
                'industry' => 'furniture',
                'category_slug' => 'furniture-craft',
                'tags' => ['meja kayu', 'trembesi', 'suar wood', 'dining table', 'mebel jepara'],
                'gradient_from' => '#292524',
                'gradient_to' => '#78350F',
                'icon_type' => 'armchair',
                'prompt' => 'Architectural Digest dining room photography featuring a monumental single-slab natural live-edge solid Suar wood dining table with heavy industrial matte-black steel legs, modern matte-black chairs, warm pendant lighting hanging overhead, terrazzo floor, warm evening sunset light streaming through floor-to-ceiling glass doors.',
                'composition' => 'Dramatic perspective showcasing the undulating organic wood slab edges',
                'lighting' => 'Warm overhead pendant lighting balanced with golden hour window glow',
                'color_palette' => ['#292524', '#78350F', '#B45309', '#F5F5F4'],
                'color_mood' => 'Monumental Organic Elegance & Warm Hospitality',
                'visual_hook' => 'Dramatic natural curves and rich swirling woodgrain textures',
                'cta_pattern' => 'Meja Kayu Solid Custom Ukuran Langsung Pengrajin',
            ],

            // --- TAMBAHAN KONTRAKTOR & ARSITEKTUR ---
            [
                'external_id' => 'curated_bld_02',
                'title' => 'Sampel Ubin Marmer Carrara & Palet Cat Interior Minimalis',
                'caption' => 'Koleksi moodboard arsitektur: potongan marmer putih urat abu-abu, sampel kain sofa linen, dan kartu warna cat interior rumah.',
                'industry' => 'contractor',
                'category_slug' => 'kontraktor-interior',
                'tags' => ['desain interior', 'marmer', 'moodboard arsitek', 'renovasi', 'material bangunan'],
                'gradient_from' => '#0F172A',
                'gradient_to' => '#334155',
                'icon_type' => 'hard_hat',
                'prompt' => 'Designer flatlay moodboard photography of a genuine polished Italian Carrara white marble tile, natural oak wood veneer swatch, brushed brass cabinet pull handle, textured beige bouclé fabric sample, and designer interior paint chips, precisely arranged on a smooth grey microcement surface, soft natural studio light.',
                'composition' => 'Precision architectural flatlay with tactile materials',
                'lighting' => 'Soft diffused northern studio daylight with delicate natural shadows',
                'color_palette' => ['#0F172A', '#334155', '#D97706', '#FFFFFF'],
                'color_mood' => 'Refined Architectural Taste & Curated Material Luxury',
                'visual_hook' => 'Interplay of tactile textures: cool marble, warm wood, and brushed brass',
                'cta_pattern' => 'Konsultasi Desain Interior & Renovasi Rumah Impian',
            ],

            // --- TAMBAHAN WEDDING & EVENT ---
            [
                'external_id' => 'curated_wed_02',
                'title' => 'Kotak Mahar Cincin Akrilik Transparan Berhias Bunga Edelweiss',
                'caption' => 'Kotak mahar pernikahan mewah dengan tutup akrilik transparan, bantalan beludru putih, sepasang cincin emas, dan dried flower edelweiss.',
                'industry' => 'event',
                'category_slug' => 'event-wedding',
                'tags' => ['mahar pernikahan', 'kotak cincin', 'wedding gift', 'hantaran', 'seserahan'],
                'gradient_from' => '#701A75',
                'gradient_to' => '#A21CAF',
                'icon_type' => 'wedding_glass',
                'prompt' => 'Macro luxury wedding detail photography of a hexagonal crystal-clear acrylic wedding ring box with engraved golden calligraphy, cushioned with ivory silk velvet holding two gleaming platinum and gold wedding bands, surrounded by delicate white dried edelweiss and baby breath flowers, soft warm fairy lights sparkling in background.',
                'composition' => 'Close-up macro hero highlighting fine ring details and delicate flowers',
                'lighting' => 'Dreamy soft-focus lighting with twinkling golden specular bokeh orbs',
                'color_palette' => ['#701A75', '#A21CAF', '#F472B6', '#FEF08A'],
                'color_mood' => 'Sacred Romance & Cherished Eternal Commitment',
                'visual_hook' => 'Sparkling diamond highlights and pristine acrylic transparency',
                'cta_pattern' => 'Kotak Cincin & Jasa Hias Mahar Seserahan Eksklusif',
            ],

            // --- TAMBAHAN PERTANIAN & HASIL TANI ---
            [
                'external_id' => 'curated_agro_03',
                'title' => 'Bulir Padi Menguning Emas dengan Latar Sawah Terasering Pagi',
                'caption' => 'Inspirasi beras premium dan hasil panen tani: tangkai bulir padi bernas menguning di sawah terasering berembun pagi.',
                'industry' => 'agriculture',
                'category_slug' => 'pertanian-agro',
                'tags' => ['beras premium', 'padi', 'hasil tani', 'pertanian organik', 'panen sawah'],
                'gradient_from' => '#713F12',
                'gradient_to' => '#CA8A04',
                'icon_type' => 'leaf_lettuce',
                'prompt' => 'Breathtaking agricultural landscape macro of heavy ripe golden rice grains on the stalk, delicate morning dew drops glistening like pearls on the husks, lush stepped green terraced rice paddies in misty soft sunrise background blur, warm golden hour sunburst flare, authentic Indonesian agrarian beauty.',
                'composition' => 'Close-up macro stalk with expansive terraced farm depth of field',
                'lighting' => 'Stunning sunrise backlighting turning every dew droplet into a golden prism',
                'color_palette' => ['#713F12', '#CA8A04', '#FDE047', '#15803D'],
                'color_mood' => 'Abundant Harvest & Grateful Agrarian Earthiness',
                'visual_hook' => 'Golden glowing rice grains beaded with sparkling morning dew',
                'cta_pattern' => 'Beras Organik Berkualitas Langsung dari Petani Lokal',
            ],
        ];

        $storageDisk = Storage::disk('public');
        $storageDir = 'ai-studio/inspirations/cooca_curated';
        $storageDisk->makeDirectory($storageDir);

        foreach ($multiIndustryItems as $itemData) {
            $cat = $categories[$itemData['category_slug']] ?? $categories['viral-trending'];

            // Generate Local SVG Image file
            $svgContent = $this->generateCuratedSvg(
                title: $itemData['title'],
                industry: $itemData['industry'],
                gradFrom: $itemData['gradient_from'],
                gradTo: $itemData['gradient_to'],
                iconType: $itemData['icon_type']
            );

            $svgFileName = "{$storageDir}/{$itemData['external_id']}.svg";
            $storageDisk->put($svgFileName, $svgContent);
            $localUrl = "/storage/{$svgFileName}";

            /** @var InspirationItem $item */
            $item = InspirationItem::updateOrCreate(
                [
                    'source_id' => $source->id,
                    'external_id' => $itemData['external_id'],
                ],
                [
                    'category_id' => $cat->id,
                    'title' => $itemData['title'],
                    'caption' => $itemData['caption'],
                    'canonical_url' => $localUrl,
                    'thumbnail_url' => $localUrl,
                    'media_type' => 'image',
                    'tags' => $itemData['tags'],
                    'industry_target' => $itemData['industry'],
                    'view_count' => rand(2500, 9500),
                    'like_count' => rand(450, 1850),
                    'comment_count' => rand(50, 320),
                    'share_count' => rand(80, 540),
                    'save_count' => rand(150, 890),
                    'is_curated' => true,
                    'curated_notes' => 'Kurasi resmi tim AI Studio Cooca untuk sektor ' . strtoupper($itemData['industry']),
                    'status' => 'active',
                    'collected_at' => now(),
                ]
            );

            // Visual Analysis
            InspirationVisualAnalysis::updateOrCreate(
                ['inspiration_item_id' => $item->id],
                [
                    'composition' => $itemData['composition'],
                    'lighting' => $itemData['lighting'],
                    'color_palette' => $itemData['color_palette'],
                    'color_mood' => $itemData['color_mood'],
                    'visual_hook' => $itemData['visual_hook'],
                    'cta_pattern' => $itemData['cta_pattern'],
                    'extracted_style_prompt' => $itemData['prompt'],
                    'negative_style_prompt' => 'low quality, blurry, deformed, cartoon, 3d render plastic, watermark, text signature, overexposed, distorted geometry, amateur snapshot',
                    'suggested_aspect_ratios' => ['1:1', '4:5', '9:16', '16:9'],
                    'safety_score' => 0.99,
                    'metadata' => [
                        'industry_sector' => $itemData['industry'],
                        'curated_edition' => '2026 Multi-Industry Standard',
                    ],
                    'analyzed_at' => now(),
                ]
            );

            // Trend Score
            InspirationTrendScore::updateOrCreate(
                ['inspiration_item_id' => $item->id],
                [
                    'trend_score' => (float) rand(88, 99) + (rand(1, 9) / 10),
                    'virality_velocity' => (float) rand(85, 98),
                    'engagement_rate' => 0.08,
                    'recency_weight' => 1.0,
                    'source_credibility_weight' => 0.95,
                    'scored_at' => now(),
                ]
            );
        }

        // 4. Update legacy 'general' items with intelligent industry mapping
        $this->smartReclassifyLegacyItems();
    }

    /**
     * Reclassify existing legacy items based on keywords in title/caption.
     */
    private function smartReclassifyLegacyItems(): void
    {
        $rules = [
            'fnb' => ['kopi', 'coffee', 'cafe', 'croissant', 'makanan', 'minuman', 'roti', 'cake', 'bakery', 'kuliner', 'ayam', 'teh', 'boba'],
            'fashion' => ['sepatu', 'sneaker', 'baju', 'hijab', 'kaos', 'fashion', 't-shirt', 'clothing', 'pakaian', 'tas', 'jaket'],
            'beauty' => ['perfume', 'parfum', 'skincare', 'lipstik', 'serum', 'lotion', 'cream', 'beauty', 'kosmetik'],
            'service' => ['barber', 'barbershop', 'laundry', 'salon', 'cukur', 'rambut'],
            'retail' => ['gadget', 'keyboard', 'tech', 'mouse', 'elektronik', 'retail', 'toko'],
            'automotive' => ['motor', 'mobil', 'bengkel', 'oli', 'ban', 'velg'],
        ];

        $items = InspirationItem::where('industry_target', 'general')->get();
        foreach ($items as $item) {
            $text = strtolower($item->title . ' ' . ($item->caption ?? '') . ' ' . json_encode($item->tags ?? []));
            foreach ($rules as $target => $keywords) {
                foreach ($keywords as $kw) {
                    if (str_contains($text, $kw)) {
                        $item->industry_target = $target;
                        $item->save();
                        break 2;
                    }
                }
            }
        }
    }

    /**
     * Generate elegant SVG visual asset for curated inspiration card.
     */
    private function generateCuratedSvg(
        string $title,
        string $industry,
        string $gradFrom,
        string $gradTo,
        string $iconType
    ): string {
        $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $safeInd = strtoupper(htmlspecialchars($industry, ENT_QUOTES, 'UTF-8'));

        $iconPaths = [
            'oil_bottle' => '<path d="M12 2v4M8 6h8a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2zM9 14h6" stroke="#FFFFFF" stroke-width="2.5" stroke-linecap="round"/>',
            'car_wheel' => '<circle cx="12" cy="12" r="9" stroke="#FFFFFF" stroke-width="2.5"/><circle cx="12" cy="12" r="3" stroke="#FFFFFF" stroke-width="2.5"/><path d="M12 3v6M12 15v6M3 12h6M15 12h6" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round"/>',
            'spray_bottle' => '<path d="M10 2h4M12 2v4M7 8h10l-1 13H8L7 8z" stroke="#FFFFFF" stroke-width="2.5" stroke-linecap="round"/><path d="M17 5l4 2" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round"/>',
            'scissors' => '<circle cx="6" cy="6" r="3" stroke="#FFFFFF" stroke-width="2.5"/><circle cx="6" cy="18" r="3" stroke="#FFFFFF" stroke-width="2.5"/><line x1="8.5" y1="8.5" x2="20" y2="20" stroke="#FFFFFF" stroke-width="2.5" stroke-linecap="round"/><line x1="8.5" y1="15.5" x2="20" y2="4" stroke="#FFFFFF" stroke-width="2.5" stroke-linecap="round"/>',
            'laundry_basket' => '<path d="M4 8h16l-2 12H6L4 8z" stroke="#FFFFFF" stroke-width="2.5" stroke-linecap="round"/><path d="M9 12v4M15 12v4M12 11v6" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round"/>',
            'shirt_steam' => '<path d="M6 4l4 2 2-2 2 2 4-2v16H6V4z" stroke="#FFFFFF" stroke-width="2.5" stroke-linecap="round"/><path d="M10 20v-8M14 20v-8" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round"/>',
            'pill_bottle' => '<rect x="6" y="6" width="12" height="15" rx="2" stroke="#FFFFFF" stroke-width="2.5"/><path d="M9 2h6v4H9z" stroke="#FFFFFF" stroke-width="2" fill="#FFFFFF" fill-opacity="0.3"/><line x1="12" y1="10" x2="12" y2="17" stroke="#FFFFFF" stroke-width="2.5" stroke-linecap="round"/><line x1="8.5" y1="13.5" x2="15.5" y2="13.5" stroke="#FFFFFF" stroke-width="2.5" stroke-linecap="round"/>',
            'honey_jar' => '<path d="M6 7h12l-1 13H7L6 7z" stroke="#FFFFFF" stroke-width="2.5" stroke-linecap="round"/><path d="M8 3h8v4H8z" stroke="#FFFFFF" stroke-width="2" fill="#FFFFFF" fill-opacity="0.3"/><path d="M10 11c1-1 3-1 4 0s1 3 0 4-3 1-4 0" stroke="#FFFFFF" stroke-width="2"/>',
            'package_box' => '<path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke="#FFFFFF" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>',
            'shopping_bag' => '<path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z" stroke="#FFFFFF" stroke-width="2.5" stroke-linecap="round"/><line x1="3" y1="6" x2="21" y2="6" stroke="#FFFFFF" stroke-width="2"/><path d="M16 10a4 4 0 0 1-8 0" stroke="#FFFFFF" stroke-width="2.5" stroke-linecap="round"/>',
            'armchair' => '<path d="M7 11V7a5 5 0 0 1 10 0v4" stroke="#FFFFFF" stroke-width="2.5"/><path d="M5 11h14v7a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-7z" stroke="#FFFFFF" stroke-width="2.5"/><path d="M6 20v2M18 20v2" stroke="#FFFFFF" stroke-width="2.5" stroke-linecap="round"/>',
            'lamp' => '<path d="M9 2h6l2 8H7l2-8z" stroke="#FFFFFF" stroke-width="2.5"/><line x1="12" y1="10" x2="12" y2="18" stroke="#FFFFFF" stroke-width="2.5"/><line x1="8" y1="18" x2="16" y2="18" stroke="#FFFFFF" stroke-width="2.5" stroke-linecap="round"/>',
            'hard_hat' => '<path d="M2 14h20M4 14a8 8 0 0 1 16 0" stroke="#FFFFFF" stroke-width="2.5" stroke-linecap="round"/><path d="M12 6v8" stroke="#FFFFFF" stroke-width="2.5"/>',
            'wedding_glass' => '<path d="M8 22h8M12 17v5M7 4h10l-2 9a3 3 0 0 1-6 0L7 4z" stroke="#FFFFFF" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>',
            'leaf_lettuce' => '<path d="M12 2C7 2 3 7 3 12c0 4 3 8 9 10 6-2 9-6 9-10 0-5-4-10-9-10z" stroke="#FFFFFF" stroke-width="2.5"/><path d="M12 2v20M7 8c2 2 5 2 7 0M6 14c3 2 7 2 10 0" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round"/>',
            'egg_nest' => '<ellipse cx="12" cy="11" rx="6" ry="8" stroke="#FFFFFF" stroke-width="2.5"/><path d="M4 18c3 3 13 3 16 0" stroke="#FFFFFF" stroke-width="2.5" stroke-linecap="round"/>',
            'shelf_display' => '<line x1="3" y1="6" x2="21" y2="6" stroke="#FFFFFF" stroke-width="2.5"/><line x1="3" y1="12" x2="21" y2="12" stroke="#FFFFFF" stroke-width="2.5"/><line x1="3" y1="18" x2="21" y2="18" stroke="#FFFFFF" stroke-width="2.5"/><rect x="5" y="8" width="3" height="4" rx="1" fill="#FFFFFF" fill-opacity="0.4"/><rect x="10" y="8" width="4" height="4" rx="1" fill="#FFFFFF" fill-opacity="0.4"/><rect x="16" y="8" width="3" height="4" rx="1" fill="#FFFFFF" fill-opacity="0.4"/>',
        ];

        $iconSvg = $iconPaths[$iconType] ?? $iconPaths['package_box'];

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 1000" width="100%" height="100%">
    <defs>
        <linearGradient id="bgGrad" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" stop-color="{$gradFrom}"/>
            <stop offset="100%" stop-color="{$gradTo}"/>
        </linearGradient>
        <radialGradient id="spotlight" cx="50%" cy="35%" r="60%">
            <stop offset="0%" stop-color="#FFFFFF" stop-opacity="0.22"/>
            <stop offset="60%" stop-color="#FFFFFF" stop-opacity="0.03"/>
            <stop offset="100%" stop-color="#000000" stop-opacity="0.6"/>
        </radialGradient>
        <linearGradient id="cardGloss" x1="0%" y1="0%" x2="0%" y2="100%">
            <stop offset="0%" stop-color="#FFFFFF" stop-opacity="0.15"/>
            <stop offset="100%" stop-color="#FFFFFF" stop-opacity="0.02"/>
        </linearGradient>
    </defs>
    <!-- Background Canvas -->
    <rect width="800" height="1000" fill="url(#bgGrad)"/>
    <rect width="800" height="1000" fill="url(#spotlight)"/>

    <!-- Subtle Grid Architecture -->
    <g opacity="0.08" stroke="#FFFFFF" stroke-width="1">
        <line x1="0" y1="200" x2="800" y2="200"/>
        <line x1="0" y1="400" x2="800" y2="400"/>
        <line x1="0" y1="600" x2="800" y2="600"/>
        <line x1="0" y1="800" x2="800" y2="800"/>
        <line x1="200" y1="0" x2="200" y2="1000"/>
        <line x1="400" y1="0" x2="400" y2="1000"/>
        <line x1="600" y1="0" x2="600" y2="1000"/>
    </g>

    <!-- Ambient Glow Orb -->
    <circle cx="400" cy="420" r="180" fill="#FFFFFF" opacity="0.06"/>

    <!-- Central Icon Container -->
    <g transform="translate(300, 320)">
        <rect width="200" height="200" rx="40" fill="url(#cardGloss)" stroke="#FFFFFF" stroke-opacity="0.25" stroke-width="2"/>
        <g transform="translate(60, 60) scale(3.3)">
            {$iconSvg}
        </g>
    </g>

    <!-- Top Badge Sector -->
    <g transform="translate(48, 48)">
        <rect width="160" height="36" rx="10" fill="#000000" fill-opacity="0.45" stroke="#FFFFFF" stroke-opacity="0.2" stroke-width="1"/>
        <text x="80" y="23" font-family="-apple-system, BlinkMacSystemFont, 'SF Pro Display', Inter, sans-serif" font-size="12" font-weight="700" fill="#FFFFFF" text-anchor="middle" letter-spacing="1.5">{$safeInd}</text>
    </g>

    <g transform="translate(630, 48)">
        <rect width="122" height="36" rx="10" fill="#007AFF" fill-opacity="0.3" stroke="#007AFF" stroke-width="1.2"/>
        <text x="61" y="23" font-family="-apple-system, BlinkMacSystemFont, 'SF Pro Display', Inter, sans-serif" font-size="12" font-weight="700" fill="#60A5FA" text-anchor="middle">COOCA AI</text>
    </g>

    <!-- Bottom Typography Panel -->
    <g transform="translate(48, 760)">
        <rect width="704" height="192" rx="24" fill="#000000" fill-opacity="0.55" stroke="#FFFFFF" stroke-opacity="0.12" stroke-width="1.5"/>
        <text x="32" y="44" font-family="-apple-system, BlinkMacSystemFont, 'SF Pro Display', Inter, sans-serif" font-size="13" font-weight="600" fill="#007AFF" letter-spacing="1">STUDIO COMMERCIAL LIGHTING</text>
        <text x="32" y="84" font-family="-apple-system, BlinkMacSystemFont, 'SF Pro Display', Inter, sans-serif" font-size="24" font-weight="800" fill="#FFFFFF">{$safeTitle}</text>
        <text x="32" y="120" font-family="-apple-system, BlinkMacSystemFont, 'SF Pro Text', Inter, sans-serif" font-size="14" font-weight="500" fill="#CBD5E1">Kompilasi Prompt Otomatis · Hasselblad 85mm f/1.8 · Multi-Channel Ready</text>
        <g transform="translate(32, 142)">
            <rect width="90" height="26" rx="8" fill="#FFFFFF" fill-opacity="0.1"/>
            <text x="45" y="17" font-family="-apple-system, BlinkMacSystemFont, sans-serif" font-size="11" font-weight="700" fill="#F8FAFC" text-anchor="middle">8K STUDIO</text>
            <rect x="100" width="80" height="26" rx="8" fill="#FFFFFF" fill-opacity="0.1"/>
            <text x="140" y="17" font-family="-apple-system, BlinkMacSystemFont, sans-serif" font-size="11" font-weight="700" fill="#F8FAFC" text-anchor="middle">1:1 / 9:16</text>
            <rect x="190" width="120" height="26" rx="8" fill="#34C759" fill-opacity="0.2"/>
            <text x="250" y="17" font-family="-apple-system, BlinkMacSystemFont, sans-serif" font-size="11" font-weight="700" fill="#4ADE80" text-anchor="middle">SIAP REPLIKASI</text>
        </g>
    </g>
</svg>
SVG;
    }
}
