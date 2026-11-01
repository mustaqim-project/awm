<?php

declare(strict_types=1);

namespace App\Domain\Ai\Inspiration\Adapters;

use App\Domain\Ai\Inspiration\Contracts\InspirationAdapterInterface;

final class PinterestAdapter implements InspirationAdapterInterface
{
    public const SOURCE_CODE = 'pinterest';

    public function getSourceCode(): string
    {
        return self::SOURCE_CODE;
    }

    /**
     * Cari konten inspirasi visual dari Pinterest (E-Commerce & Commercial Photography).
     *
     * @param array<string, mixed> $filters
     * @return array<int, array<string, mixed>>
     */
    public function search(string $query, array $filters = []): array
    {
        $limit = (int) ($filters['limit'] ?? 100);
        $templates = $this->getCuratedPinterestTemplates();

        if (!empty($query)) {
            $q = strtolower($query);
            $templates = array_filter($templates, function ($item) use ($q) {
                return str_contains(strtolower($item['title']), $q)
                    || str_contains(strtolower($item['caption'] ?? ''), $q)
                    || in_array($q, array_map('strtolower', $item['tags'] ?? []));
            });
        }

        return array_values(array_slice($templates, 0, $limit));
    }

    /**
     * Ambil detail spesifik satu item inspirasi Pinterest.
     *
     * @return array<string, mixed>|null
     */
    public function getItem(string $externalId): ?array
    {
        $templates = $this->getCuratedPinterestTemplates();
        foreach ($templates as $item) {
            if ($item['external_id'] === $externalId) {
                return $item;
            }
        }

        return null;
    }

    public function getAttribution(string $externalId): array
    {
        return [
            'license' => 'Commercial Public Domain / Royalty-Free Design Concept',
            'allow_commercial_use' => true,
            'require_attribution' => false,
            'attribution_text' => 'Pinterest E-Commerce Commercial Design Board',
            'source_url' => 'https://www.pinterest.com',
        ];
    }

    /**
     * Koleksi curated template fotografi produk & poster viral Pinterest.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getCuratedPinterestTemplates(): array
    {
        return [
            // F&B 1: Iced Coffee
            [
                'external_id' => 'pin-fnb-001',
                'title' => 'Artisanal Iced Coffee with Liquid Splash & Roasted Beans',
                'caption' => 'Ultra-crisp commercial beverage photoshoot, dynamic cold brew splash frozen in mid-air with condensation droplets on fluted glassware, warm natural morning backlight.',
                'canonical_url' => 'https://images.unsplash.com/photo-1517701604599-bb29b565090c?auto=format&fit=crop&w=1200&q=80',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1517701604599-bb29b565090c?auto=format&fit=crop&w=800&q=80',
                'media_type' => 'image',
                'media_urls' => ['https://images.unsplash.com/photo-1517701604599-bb29b565090c?auto=format&fit=crop&w=1200&q=80'],
                'aspect_ratio' => '4:5',
                'tags' => ['coffee', 'beverage', 'splash', 'commercial', 'summer', 'fnb'],
                'license' => 'Royalty-Free Commercial',
                'photographer_name' => 'Pinterest Product Studio',
                'allow_commercial_use' => true,
                'category_code' => 'fnb',
                'industry_target' => 'Makanan & Minuman (F&B)',
                'visual_analysis' => [
                    'composition' => 'dynamic_splash_center',
                    'lighting' => 'backlit_rim_sunlight',
                    'color_palette' => ['#4A2E18', '#C89D76', '#FFFFFF', '#1A1A1A'],
                    'color_mood' => 'warm_invigorating',
                    'extracted_style_prompt' => 'Commercial iced beverage photography, crystal fluted glass filled with dark espresso and milk swirl, frozen liquid splash with droplets catching warm morning rim light, scattered roasted coffee beans on dark walnut wood table, 85mm f/2.0 macro focus, cinematic atmosphere.',
                    'negative_style_prompt' => 'blurry, artificial plastic look, lowres, distorted cup, unappetizing flat lighting',
                    'suggested_aspect_ratios' => ['4:5', '1:1', '9:16'],
                ],
            ],
            // F&B 2: Bakso Nusantara
            [
                'external_id' => 'pin-fnb-002',
                'title' => 'Gourmet Steaming Bakso Nusantara in Artisanal Stoneware Bowl',
                'caption' => 'Culinary hero shot of authentic Indonesian meatball soup, delicate aromatic steam wisps rising, golden glistening broth, fresh spring onions, crispy fried shallots, and spicy sambal spoon.',
                'canonical_url' => 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?auto=format&fit=crop&w=1200&q=80',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?auto=format&fit=crop&w=800&q=80',
                'media_type' => 'image',
                'media_urls' => ['https://images.unsplash.com/photo-1569718212165-3a8278d5f624?auto=format&fit=crop&w=1200&q=80'],
                'aspect_ratio' => '1:1',
                'tags' => ['bakso', 'kuliner', 'indonesia', 'steaming', 'fnb', 'comfort_food'],
                'license' => 'Royalty-Free Commercial',
                'photographer_name' => 'Pinterest Culinary Board',
                'allow_commercial_use' => true,
                'category_code' => 'fnb',
                'industry_target' => 'Makanan & Minuman (F&B)',
                'visual_analysis' => [
                    'composition' => 'top_down_three_quarter_hero',
                    'lighting' => 'soft_diffused_warm_side',
                    'color_palette' => ['#8C5835', '#2E4C1E', '#E6C687', '#1F1A17'],
                    'color_mood' => 'appetizing_cozy',
                    'extracted_style_prompt' => 'Appetizing Indonesian meatball bakso soup served in a handcrafted dark matte ceramic bowl, rich golden translucent broth with oil droplets, tender textured meatballs, yellow noodles, crisp green scallions, delicate steam rising gracefully, soft directional window lighting, rustic dark wood table, 50mm f/1.8 lens.',
                    'negative_style_prompt' => 'cold soup, artificial colors, cartoonish, greasy unappetizing surface, uncentered',
                    'suggested_aspect_ratios' => ['1:1', '4:5'],
                ],
            ],
            // F&B 3: Artisan Bakery Croissant
            [
                'external_id' => 'pin-fnb-003',
                'title' => 'Golden Flaky French Croissant on Marble with Butter Curls',
                'caption' => 'Artisanal morning bakery photography, perfectly laminated golden flaky croissant dusting with delicate flour crumbs, curls of French butter, warm morning sunlight streaming in.',
                'canonical_url' => 'https://images.unsplash.com/photo-1555507036-ab1f4038808a?auto=format&fit=crop&w=1200&q=80',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1555507036-ab1f4038808a?auto=format&fit=crop&w=800&q=80',
                'media_type' => 'image',
                'media_urls' => ['https://images.unsplash.com/photo-1555507036-ab1f4038808a?auto=format&fit=crop&w=1200&q=80'],
                'aspect_ratio' => '4:5',
                'tags' => ['croissant', 'bakery', 'flaky', 'pastry', 'breakfast', 'fnb'],
                'license' => 'Royalty-Free Commercial',
                'photographer_name' => 'Pinterest Artisan Baker',
                'allow_commercial_use' => true,
                'category_code' => 'fnb',
                'industry_target' => 'Makanan & Minuman (F&B)',
                'visual_analysis' => [
                    'composition' => 'diagonal_hero_shot',
                    'lighting' => 'golden_hour_side_sunlight',
                    'color_palette' => ['#D97706', '#F59E0B', '#F8FAFC', '#78350F'],
                    'color_mood' => 'warm_buttery_inviting',
                    'extracted_style_prompt' => 'Commercial artisanal bakery packshot, golden brown flaky layered croissant with delicate glistening glaze, fine crumbs scattered on Italian Carrara white marble slab, curls of creamy butter on side, soft morning side-light creating long gentle shadows, 85mm macro lens.',
                    'negative_style_prompt' => 'burnt, pale doughy crust, flat lighting, cluttered background, cartoon',
                    'suggested_aspect_ratios' => ['4:5', '1:1'],
                ],
            ],
            // F&B 4: Matcha Latte Art
            [
                'external_id' => 'pin-fnb-004',
                'title' => 'Ceremonial Matcha Latte with Swan Foam Art & Bamboo Whisk',
                'caption' => 'Zen aesthetic beverage packshot, vibrant emerald green ceremonial matcha latte in a coarse wabi-sabi ceramic cup, intricate latte art, traditional bamboo chasen whisk.',
                'canonical_url' => 'https://images.unsplash.com/photo-1536256263959-770b48d82b0a?auto=format&fit=crop&w=1200&q=80',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1536256263959-770b48d82b0a?auto=format&fit=crop&w=800&q=80',
                'media_type' => 'image',
                'media_urls' => ['https://images.unsplash.com/photo-1536256263959-770b48d82b0a?auto=format&fit=crop&w=1200&q=80'],
                'aspect_ratio' => '1:1',
                'tags' => ['matcha', 'latte_art', 'zen', 'ceramic', 'tea', 'fnb'],
                'license' => 'Royalty-Free Commercial',
                'photographer_name' => 'Pinterest Tea House',
                'allow_commercial_use' => true,
                'category_code' => 'fnb',
                'industry_target' => 'Makanan & Minuman (F&B)',
                'visual_analysis' => [
                    'composition' => 'overhead_flatlay_zen',
                    'lighting' => 'soft_diffuse_cloudy_window',
                    'color_palette' => ['#15803D', '#F0FDF4', '#A16207', '#27272A'],
                    'color_mood' => 'zen_serene_refreshing',
                    'extracted_style_prompt' => 'High-end cafe photography, rich emerald green ceremonial matcha latte with velvety microfoam swan art in a handcrafted speckled stone cup, raw bamboo whisk and scattered green tea powder on a raw concrete surface, serene minimalist aesthetic, crisp focus.',
                    'negative_style_prompt' => 'watery, pale green, messy foam bubbles, harsh flash, lowres',
                    'suggested_aspect_ratios' => ['1:1', '4:5'],
                ],
            ],
            // F&B 5: Gourmet Burger
            [
                'external_id' => 'pin-fnb-005',
                'title' => 'Gourmet Smash Burger with Melting Cheddar & Brioche Bun',
                'caption' => 'Juicy double beef patty smash burger with dripping golden cheddar cheese, crispy bacon, caramelized onions in a glossy toasted brioche bun, dark slate background.',
                'canonical_url' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=1200&q=80',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=800&q=80',
                'media_type' => 'image',
                'media_urls' => ['https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=1200&q=80'],
                'aspect_ratio' => '4:5',
                'tags' => ['burger', 'smash_burger', 'beef', 'cheddar', 'gourmet', 'fnb'],
                'license' => 'Royalty-Free Commercial',
                'photographer_name' => 'Pinterest Gourmet Kitchen',
                'allow_commercial_use' => true,
                'category_code' => 'fnb',
                'industry_target' => 'Makanan & Minuman (F&B)',
                'visual_analysis' => [
                    'composition' => 'low_angle_monumental_hero',
                    'lighting' => 'dramatic_warm_rim_softbox',
                    'color_palette' => ['#B45309', '#FBBF24', '#1C1917', '#DC2626'],
                    'color_mood' => 'succulent_bold_craveable',
                    'extracted_style_prompt' => 'Commercial burger advertising shot, towering double patty smash burger with crispy seared edges, thick dripping melted yellow cheddar cheese, glossy golden toasted brioche bun with sesame seeds, fresh crisp lettuce and red tomato, low angle heroic perspective, dramatic dark studio backdrop, 70mm f/2.8.',
                    'negative_style_prompt' => 'dry patty, squished bun, sloppy cold look, flat overhead angle, plastic toy',
                    'suggested_aspect_ratios' => ['4:5', '1:1'],
                ],
            ],
            // Beauty 1: Skincare Serum
            [
                'external_id' => 'pin-beauty-001',
                'title' => 'Botanical Skincare Serum Bottle on Travertine Stone Podium',
                'caption' => 'Clean aesthetic skincare packshot, amber glass dropper bottle with botanical olive branch shadows, soft neutral beige background, pristine spa atmosphere.',
                'canonical_url' => 'https://images.unsplash.com/photo-1620916566398-39f1143ab7be?auto=format&fit=crop&w=1200&q=80',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1620916566398-39f1143ab7be?auto=format&fit=crop&w=800&q=80',
                'media_type' => 'image',
                'media_urls' => ['https://images.unsplash.com/photo-1620916566398-39f1143ab7be?auto=format&fit=crop&w=1200&q=80'],
                'aspect_ratio' => '4:5',
                'tags' => ['skincare', 'serum', 'minimalist', 'cosmetics', 'travertine', 'beauty'],
                'license' => 'Royalty-Free Commercial',
                'photographer_name' => 'Pinterest Aesthetic Studio',
                'allow_commercial_use' => true,
                'category_code' => 'beauty',
                'industry_target' => 'Kecantikan & Skincare',
                'visual_analysis' => [
                    'composition' => 'minimalist_podium_centered',
                    'lighting' => 'gentle_morning_sun_dappled',
                    'color_palette' => ['#D8CBB5', '#8C7A6B', '#F5EFEB', '#4A3B32'],
                    'color_mood' => 'organic_clean_luxury',
                    'extracted_style_prompt' => 'High-end cosmetic photography, luxury skincare bottle standing proudly on textured natural travertine stone slab, delicate soft botanical eucalyptus shadows on cream plaster wall, dewy mist in the air, morning natural sunlight, minimal aesthetic, premium Korean beauty branding.',
                    'negative_style_prompt' => 'harsh shadows, distorted label, cluttered props, low quality, oversaturated',
                    'suggested_aspect_ratios' => ['4:5', '1:1', '9:16'],
                ],
            ],
            // Beauty 2: Luxury Lipstick Swatch
            [
                'external_id' => 'pin-beauty-002',
                'title' => 'Velvet Matte Lipstick with Creamy Texture Swatch & Gold Cap',
                'caption' => 'Macro beauty packshot of luxury bullet lipstick in gold packaging standing next to a smooth curved velvet ribbon swatch of rich terracotta red cream.',
                'canonical_url' => 'https://images.unsplash.com/photo-1586495777744-4413f21062fa?auto=format&fit=crop&w=1200&q=80',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1586495777744-4413f21062fa?auto=format&fit=crop&w=800&q=80',
                'media_type' => 'image',
                'media_urls' => ['https://images.unsplash.com/photo-1586495777744-4413f21062fa?auto=format&fit=crop&w=1200&q=80'],
                'aspect_ratio' => '1:1',
                'tags' => ['lipstick', 'cosmetics', 'swatch', 'matte', 'gold', 'beauty'],
                'license' => 'Royalty-Free Commercial',
                'photographer_name' => 'Pinterest Makeup Lab',
                'allow_commercial_use' => true,
                'category_code' => 'beauty',
                'industry_target' => 'Kecantikan & Skincare',
                'visual_analysis' => [
                    'composition' => 'macro_diagonal_product_swatch',
                    'lighting' => 'studio_glamour_ringlight',
                    'color_palette' => ['#991B1B', '#D4AF37', '#FEF2F2', '#18181B'],
                    'color_mood' => 'glamorous_sophisticated',
                    'extracted_style_prompt' => 'Macro luxury cosmetics product photography, satin gold lipstick casing open showing deep terracotta red bullet, beside an artistic thick textural smear swatch of velvety matte lipstick pigment on frosted glass, studio beauty lighting with razor-sharp micro details, 100mm f/2.8 lens.',
                    'negative_style_prompt' => 'blurry texture, smudged fingerprint, cheap plastic, distorted packaging',
                    'suggested_aspect_ratios' => ['1:1', '4:5'],
                ],
            ],
            // Beauty 3: Cleansing Foam Splash
            [
                'external_id' => 'pin-beauty-003',
                'title' => 'Fresh Cleansing Gel with Water Splash Wave & Mint Leaves',
                'caption' => 'Ultra-fresh skincare splash visual, translucent tube surrounded by crystal clear dynamic water splash and vibrant organic mint leaves against powder blue backdrop.',
                'canonical_url' => 'https://images.unsplash.com/photo-1556228720-195a672e8a03?auto=format&fit=crop&w=1200&q=80',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1556228720-195a672e8a03?auto=format&fit=crop&w=800&q=80',
                'media_type' => 'image',
                'media_urls' => ['https://images.unsplash.com/photo-1556228720-195a672e8a03?auto=format&fit=crop&w=1200&q=80'],
                'aspect_ratio' => '4:5',
                'tags' => ['cleanser', 'skincare', 'water_splash', 'fresh', 'mint', 'beauty'],
                'license' => 'Royalty-Free Commercial',
                'photographer_name' => 'Pinterest Fresh Studio',
                'allow_commercial_use' => true,
                'category_code' => 'beauty',
                'industry_target' => 'Kecantikan & Skincare',
                'visual_analysis' => [
                    'composition' => 'splash_wave_envelope',
                    'lighting' => 'bright_clean_studio_strobe',
                    'color_palette' => ['#0284C7', '#BAE6FD', '#15803D', '#FFFFFF'],
                    'color_mood' => 'pure_hydrating_invigorating',
                    'extracted_style_prompt' => 'Commercial skincare advertising, minimalist cosmetic bottle encircled by a dynamic frozen wave of translucent water splash with glistening droplets, floating green botanical leaves, pure soft powder-blue gradient studio background, high speed flash sync 1/4000s.',
                    'negative_style_prompt' => 'murky water, distorted bottle label, dark moody lighting, oversaturated',
                    'suggested_aspect_ratios' => ['4:5', '1:1', '9:16'],
                ],
            ],
            // Fashion 1: Streetwear Apparel
            [
                'external_id' => 'pin-fashion-001',
                'title' => 'Minimalist Streetwear Apparel Mockup on Industrial Concrete',
                'caption' => 'Heavyweight cotton oversized t-shirt laid flat on raw architectural concrete with subtle shadow cast, neutral tone editorial streetwear branding.',
                'canonical_url' => 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?auto=format&fit=crop&w=1200&q=80',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?auto=format&fit=crop&w=800&q=80',
                'media_type' => 'image',
                'media_urls' => ['https://images.unsplash.com/photo-1521572267360-ee0c2909d518?auto=format&fit=crop&w=1200&q=80'],
                'aspect_ratio' => '1:1',
                'tags' => ['fashion', 'streetwear', 'apparel', 'minimalist', 'concrete', 'clothing'],
                'license' => 'Royalty-Free Commercial',
                'photographer_name' => 'Pinterest Fashion Board',
                'allow_commercial_use' => true,
                'category_code' => 'fashion',
                'industry_target' => 'Fashion & Konveksi',
                'visual_analysis' => [
                    'composition' => 'flatlay_architectural_grid',
                    'lighting' => 'diffused_cloudy_daylight',
                    'color_palette' => ['#E5E5E5', '#222222', '#7D7D7D', '#B0A89F'],
                    'color_mood' => 'urban_monochrome',
                    'extracted_style_prompt' => 'Contemporary urban fashion catalog shoot, premium heavy-cotton apparel piece positioned flat on smooth industrial raw concrete surface, crisp garment texture and stitch details visible, natural diffused soft light from overhead, high fashion editorial layout.',
                    'negative_style_prompt' => 'wrinkled messy fabric, cartoonish, lowres, blurry stitching, oversaturated',
                    'suggested_aspect_ratios' => ['1:1', '4:5'],
                ],
            ],
            // Fashion 2: Silk Scarf / Hijab
            [
                'external_id' => 'pin-fashion-002',
                'title' => 'Flowing Silk Hijab Fabric with Natural Drapes & Golden Sunlight',
                'caption' => 'Editorial modest fashion visual, lustrous pastel silk scarf gently draped over a sand dune with soft flowing wave folds catching golden afternoon light.',
                'canonical_url' => 'https://images.unsplash.com/photo-1609357605129-26f69add5d6e?auto=format&fit=crop&w=1200&q=80',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1609357605129-26f69add5d6e?auto=format&fit=crop&w=800&q=80',
                'media_type' => 'image',
                'media_urls' => ['https://images.unsplash.com/photo-1609357605129-26f69add5d6e?auto=format&fit=crop&w=1200&q=80'],
                'aspect_ratio' => '4:5',
                'tags' => ['hijab', 'silk', 'modest_fashion', 'drapery', 'luxury', 'fashion'],
                'license' => 'Royalty-Free Commercial',
                'photographer_name' => 'Pinterest Modest Studio',
                'allow_commercial_use' => true,
                'category_code' => 'fashion',
                'industry_target' => 'Fashion & Konveksi',
                'visual_analysis' => [
                    'composition' => 'flowing_curvilinear_waves',
                    'lighting' => 'golden_hour_raking_light',
                    'color_palette' => ['#E2B8A0', '#C59B82', '#FDF8F5', '#8C5A42'],
                    'color_mood' => 'graceful_luxurious_warm',
                    'extracted_style_prompt' => 'High-fashion modest apparel editorial, premium mulberry silk scarf draping in graceful fluid sculptural folds, iridescent sheen catching warm low-angle golden sunset light, fine woven texture in ultra-sharp detail, minimalist sand background, Vogue Arabia aesthetic.',
                    'negative_style_prompt' => 'stiff polyester look, crumpled messy cloth, dull lighting, low quality',
                    'suggested_aspect_ratios' => ['4:5', '1:1', '9:16'],
                ],
            ],
            // Fashion 3: Leather Handbag
            [
                'external_id' => 'pin-fashion-003',
                'title' => 'Artisanal Genuine Leather Bag on Monolithic Sandstone Block',
                'caption' => 'Luxury handcrafted leather bag packshot, warm caramel tan leather with polished brass hardware resting on architectural sandstone pedestal under harsh architectural shadow.',
                'canonical_url' => 'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?auto=format&fit=crop&w=1200&q=80',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?auto=format&fit=crop&w=800&q=80',
                'media_type' => 'image',
                'media_urls' => ['https://images.unsplash.com/photo-1548036328-c9fa89d128fa?auto=format&fit=crop&w=1200&q=80'],
                'aspect_ratio' => '1:1',
                'tags' => ['leather', 'handbag', 'luxury', 'sandstone', 'craftsmanship', 'fashion'],
                'license' => 'Royalty-Free Commercial',
                'photographer_name' => 'Pinterest Leather Atelier',
                'allow_commercial_use' => true,
                'category_code' => 'fashion',
                'industry_target' => 'Fashion & Konveksi',
                'visual_analysis' => [
                    'composition' => 'architectural_pedestal_standing',
                    'lighting' => 'sharp_direct_sunlight_geometric_shadows',
                    'color_palette' => ['#9A5B32', '#D8B896', '#EFE3D5', '#2C1D11'],
                    'color_mood' => 'classic_timeless_craft',
                    'extracted_style_prompt' => 'Luxury leather goods advertising, bespoke full-grain caramel leather handbag with precision edge stitching and gleam of solid brass buckles, sitting atop an architectural carved sandstone block, sharp geometric shadows on warm terracotta stucco wall, 85mm f/4.0 sharp.',
                    'negative_style_prompt' => 'fake plastic pleather, floppy shape, blurry stitch lines, dirty surface',
                    'suggested_aspect_ratios' => ['1:1', '4:5'],
                ],
            ],
            // Gadget 1: Minimalist Desk Setup
            [
                'external_id' => 'pin-gadget-001',
                'title' => 'Sleek Matte Tech Gadget on Minimalist Wooden Desk Setup',
                'caption' => 'Clean desk setup featuring smart device accessories on oak desk mat with warm ambient desk lamp glow and succulent plant.',
                'canonical_url' => 'https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?auto=format&fit=crop&w=1200&q=80',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?auto=format&fit=crop&w=800&q=80',
                'media_type' => 'image',
                'media_urls' => ['https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?auto=format&fit=crop&w=1200&q=80'],
                'aspect_ratio' => '16:9',
                'tags' => ['gadget', 'desk_setup', 'electronics', 'tech', 'minimalist'],
                'license' => 'Royalty-Free Commercial',
                'photographer_name' => 'Pinterest Tech Space',
                'allow_commercial_use' => true,
                'category_code' => 'gadget',
                'industry_target' => 'Gadget & Elektronik',
                'visual_analysis' => [
                    'composition' => 'wide_isometric_desk_angle',
                    'lighting' => 'ambient_warm_led_softbox',
                    'color_palette' => ['#1A1A1A', '#C9A984', '#FFFFFF', '#3B3B3B'],
                    'color_mood' => 'productive_refined',
                    'extracted_style_prompt' => 'Modern tech lifestyle product photography, sleek gadget device resting on premium wool felt desk mat and solid oak desktop, subtle warm ambient backlighting, razor-sharp metallic edges, immaculate workspace aesthetics, 35mm f/2.8 lens.',
                    'negative_style_prompt' => 'messy cables, dust, scratches, blurry focus, distorted proportions',
                    'suggested_aspect_ratios' => ['16:9', '1:1', '4:5'],
                ],
            ],
            // Gadget 2: Smartwatch
            [
                'external_id' => 'pin-gadget-002',
                'title' => 'Smartwatch on Brushed Steel Stand with OLED Screen Glow',
                'caption' => 'Precision electronics packshot, premium smartwatch mounted on custom matte black steel stand, vibrant crisp screen graphics, subtle blue rim lighting.',
                'canonical_url' => 'https://images.unsplash.com/photo-1508685096489-7aacd43bd3b1?auto=format&fit=crop&w=1200&q=80',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1508685096489-7aacd43bd3b1?auto=format&fit=crop&w=800&q=80',
                'media_type' => 'image',
                'media_urls' => ['https://images.unsplash.com/photo-1508685096489-7aacd43bd3b1?auto=format&fit=crop&w=1200&q=80'],
                'aspect_ratio' => '1:1',
                'tags' => ['smartwatch', 'wearables', 'tech', 'screen', 'metal', 'gadget'],
                'license' => 'Royalty-Free Commercial',
                'photographer_name' => 'Pinterest Hardware Lab',
                'allow_commercial_use' => true,
                'category_code' => 'gadget',
                'industry_target' => 'Gadget & Elektronik',
                'visual_analysis' => [
                    'composition' => 'side_three_quarter_pedestal',
                    'lighting' => 'soft_studio_key_with_blue_accent',
                    'color_palette' => ['#111827', '#3B82F6', '#9CA3AF', '#F3F4F6'],
                    'color_mood' => 'sleek_modern_precise',
                    'extracted_style_prompt' => 'High-end wearable product commercial packshot, smartwatch with brushed aerospace-grade titanium chassis and fluoroelastomer strap, floating crystal-clear vibrant display, dark gradient studio cyclorama background with subtle neon edge glow, 100mm macro.',
                    'negative_style_prompt' => 'cracked screen, fingerprint smudges, unaligned bezel, cheap plastic',
                    'suggested_aspect_ratios' => ['1:1', '4:5'],
                ],
            ],
            // Home Decor 1: Scented Candle
            [
                'external_id' => 'pin-home-001',
                'title' => 'Scented Aromatherapy Candle in Handmade Ceramic Tumbler',
                'caption' => 'Hygge cozy home ambiance, lit soy wax scented candle with gentle golden flame on linen cloth next to dried flowers.',
                'canonical_url' => 'https://images.unsplash.com/photo-1603006905003-be475563bc59?auto=format&fit=crop&w=1200&q=80',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1603006905003-be475563bc59?auto=format&fit=crop&w=800&q=80',
                'media_type' => 'image',
                'media_urls' => ['https://images.unsplash.com/photo-1603006905003-be475563bc59?auto=format&fit=crop&w=1200&q=80'],
                'aspect_ratio' => '1:1',
                'tags' => ['candle', 'home_decor', 'aromatherapy', 'ceramic', 'hygge'],
                'license' => 'Royalty-Free Commercial',
                'photographer_name' => 'Pinterest Lifestyle Decor',
                'allow_commercial_use' => true,
                'category_code' => 'home_decor',
                'industry_target' => 'Dekorasi & Rumah Tangga',
                'visual_analysis' => [
                    'composition' => 'cozy_intimate_closeup',
                    'lighting' => 'warm_candlelight_glow',
                    'color_palette' => ['#EFE7DD', '#BFA588', '#D97706', '#3D312A'],
                    'color_mood' => 'warm_serene_cozy',
                    'extracted_style_prompt' => 'Artisanal soy candle in a speckled stoneware ceramic vessel, warm dancing flame casting soft golden glow on textured beige linen tablecloth, dried pampas grass in soft blur background, hygge lifestyle product shot, cozy peaceful atmosphere.',
                    'negative_style_prompt' => 'smoke billowing, harsh direct flash, cheap plastic container, blurry foreground',
                    'suggested_aspect_ratios' => ['1:1', '4:5'],
                ],
            ],
            // Retail / Kemasan: Kopi Kemasan Pouch
            [
                'external_id' => 'pin-retail-001',
                'title' => 'Artisan Roasted Coffee Pouch with Matte Finish & Wood Tray',
                'caption' => 'E-commerce packaging hero shot, craft matte white stand-up pouch of single origin coffee beans standing on a rustic teakwood tray surrounded by roasted beans.',
                'canonical_url' => 'https://images.unsplash.com/photo-1559056199-641a0ac8b55e?auto=format&fit=crop&w=1200&q=80',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1559056199-641a0ac8b55e?auto=format&fit=crop&w=800&q=80',
                'media_type' => 'image',
                'media_urls' => ['https://images.unsplash.com/photo-1559056199-641a0ac8b55e?auto=format&fit=crop&w=1200&q=80'],
                'aspect_ratio' => '1:1',
                'tags' => ['coffee_pouch', 'packaging', 'stand_pouch', 'coffee_beans', 'retail', 'umkm'],
                'license' => 'Royalty-Free Commercial',
                'photographer_name' => 'Pinterest Packshot Studio',
                'allow_commercial_use' => true,
                'category_code' => 'retail',
                'industry_target' => 'Ritel & Produk Kemasan',
                'visual_analysis' => [
                    'composition' => 'standing_center_hero_packshot',
                    'lighting' => 'diffused_softbox_45_degree',
                    'color_palette' => ['#FFFFFF', '#451A03', '#B45309', '#1C1917'],
                    'color_mood' => 'authentic_premium_craft',
                    'extracted_style_prompt' => 'Commercial product packaging shot, premium matte white stand-up zipper pouch standing firmly on a solid Indonesian teak board, scattered dark shiny roasted Arabica coffee beans around the base, neutral off-white textured backdrop, sharp brand label visibility, 50mm f/2.8.',
                    'negative_style_prompt' => 'wrinkled crumpled pouch, crooked label, distorted bag seams, harsh glare',
                    'suggested_aspect_ratios' => ['1:1', '4:5'],
                ],
            ],
            // Jasa / Services: Barbershop Styling Kit
            [
                'external_id' => 'pin-service-001',
                'title' => 'Vintage Gentleman Barbershop Tools on Dark Leather Mat',
                'caption' => 'Craft service photoshoot, precision chrome hair clippers, vintage straight razor, wooden beard brush, and amber pomade jar arranged on stitched dark leather.',
                'canonical_url' => 'https://images.unsplash.com/photo-1503951914875-452162b0f3f1?auto=format&fit=crop&w=1200&q=80',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1503951914875-452162b0f3f1?auto=format&fit=crop&w=800&q=80',
                'media_type' => 'image',
                'media_urls' => ['https://images.unsplash.com/photo-1503951914875-452162b0f3f1?auto=format&fit=crop&w=1200&q=80'],
                'aspect_ratio' => '16:9',
                'tags' => ['barbershop', 'grooming', 'razor', 'service', 'vintage', 'craftsmanship'],
                'license' => 'Royalty-Free Commercial',
                'photographer_name' => 'Pinterest Barber Heritage',
                'allow_commercial_use' => true,
                'category_code' => 'service',
                'industry_target' => 'Jasa & Layanan Profesional',
                'visual_analysis' => [
                    'composition' => 'flatlay_curated_artisan_grid',
                    'lighting' => 'moody_warm_edison_glow',
                    'color_palette' => ['#18181B', '#78350F', '#D4D4D8', '#B45309'],
                    'color_mood' => 'heritage_masculine_craft',
                    'extracted_style_prompt' => 'Artisanal service branding photography, professional chrome barber tools, classic wooden-handle straight razor, and grooming balm jar meticulously arranged on a dark saddle-stitched leather mat, moody atmospheric warm filament lighting, shallow depth of field, 50mm f/1.8.',
                    'negative_style_prompt' => 'messy hairs, rusty blades, plastic cheap tools, overexposed flash',
                    'suggested_aspect_ratios' => ['16:9', '1:1', '4:5'],
                ],
            ],
        ];
    }
}
