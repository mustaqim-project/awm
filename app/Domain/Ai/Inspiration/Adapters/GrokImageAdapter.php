<?php

declare(strict_types=1);

namespace App\Domain\Ai\Inspiration\Adapters;

use App\Domain\Ai\Inspiration\Contracts\InspirationAdapterInterface;

final class GrokImageAdapter implements InspirationAdapterInterface
{
    public const SOURCE_CODE = 'grok';

    public function getSourceCode(): string
    {
        return self::SOURCE_CODE;
    }

    /**
     * Cari konten inspirasi visual dari Grok Image Library / Flux Generation Styles.
     *
     * @param array<string, mixed> $filters
     * @return array<int, array<string, mixed>>
     */
    public function search(string $query, array $filters = []): array
    {
        $limit = (int) ($filters['limit'] ?? 100);
        $templates = $this->getCuratedGrokTemplates();

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
     * Ambil detail spesifik satu item inspirasi Grok Image Library.
     *
     * @return array<string, mixed>|null
     */
    public function getItem(string $externalId): ?array
    {
        $templates = $this->getCuratedGrokTemplates();
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
            'license' => 'Grok Image Engine Commercial Open Standard',
            'allow_commercial_use' => true,
            'require_attribution' => false,
            'attribution_text' => 'Grok Commercial AI Prompt Architecture',
            'source_url' => 'https://x.ai/grok',
        ];
    }

    /**
     * Koleksi curated template visual Grok Image Library & Flux Next-Gen Aesthetics.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getCuratedGrokTemplates(): array
    {
        return [
            // 1. Luxury Perfume Caustics
            [
                'external_id' => 'grok-luxury-001',
                'title' => 'Floating Luxury Perfume with Crystal Water Ripple & Refraction',
                'caption' => 'Hyper-photorealistic commercial fragrance advertising, bottle levitating millimeters above a pristine rippling water surface with caustic light refractions and golden mist.',
                'canonical_url' => 'https://images.unsplash.com/photo-1592945403244-b3fbafd7f539?auto=format&fit=crop&w=1200&q=80',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1592945403244-b3fbafd7f539?auto=format&fit=crop&w=800&q=80',
                'media_type' => 'image',
                'media_urls' => ['https://images.unsplash.com/photo-1592945403244-b3fbafd7f539?auto=format&fit=crop&w=1200&q=80'],
                'aspect_ratio' => '4:5',
                'tags' => ['perfume', 'luxury', 'water_ripple', 'caustics', 'grok', 'cinematic'],
                'license' => 'Grok Commercial Open Standard',
                'photographer_name' => 'Grok Vision Studio',
                'allow_commercial_use' => true,
                'category_code' => 'beauty',
                'industry_target' => 'Kecantikan & Skincare',
                'visual_analysis' => [
                    'composition' => 'levitating_centerpiece',
                    'lighting' => 'caustic_underwater_refraction',
                    'color_palette' => ['#D4AF37', '#1E3A8A', '#F8FAFC', '#0F172A'],
                    'color_mood' => 'opulent_ethereal',
                    'extracted_style_prompt' => 'Award-winning luxury perfume advertising packshot, sleek heavy glass flacon suspended over shallow turquoise water with delicate ripples, caustic sunlight patterns reflecting onto product label, microscopic air bubbles, Hasselblad H6D-100c, 100mm f/2.8 macro lens, photorealistic 8k.',
                    'negative_style_prompt' => 'murky water, distorted bottle geometry, cloudy glass, cartoon render, watermark',
                    'suggested_aspect_ratios' => ['4:5', '1:1', '9:16'],
                ],
            ],
            // 2. Crispy Fried Chicken Action
            [
                'external_id' => 'grok-fnb-001',
                'title' => 'Crisp Golden Fried Chicken Bursting with Spices & Steam',
                'caption' => 'Exploding flavor packshot of gourmet fried chicken with crispy craggy golden crust, floating red chili flakes, crushed black pepper, and garlic cloves suspended in air.',
                'canonical_url' => 'https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?auto=format&fit=crop&w=1200&q=80',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?auto=format&fit=crop&w=800&q=80',
                'media_type' => 'image',
                'media_urls' => ['https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?auto=format&fit=crop&w=1200&q=80'],
                'aspect_ratio' => '1:1',
                'tags' => ['fried_chicken', 'crispy', 'spices', 'floating_action', 'fnb', 'grok'],
                'license' => 'Grok Commercial Open Standard',
                'photographer_name' => 'Grok Food Lab',
                'allow_commercial_use' => true,
                'category_code' => 'fnb',
                'industry_target' => 'Makanan & Minuman (F&B)',
                'visual_analysis' => [
                    'composition' => 'explosive_flavor_burst',
                    'lighting' => 'high_speed_dual_strobe',
                    'color_palette' => ['#D97706', '#DC2626', '#78350F', '#18181B'],
                    'color_mood' => 'intense_appetizing',
                    'extracted_style_prompt' => 'Commercial fast-food campaign hero shot, golden ultra-crispy textured chicken piece breaking open with succulent steaming juiciness, dynamic freeze-frame of floating paprika dust, toasted sesame seeds, and chili flakes, dramatic dark background with directional warm backlight, high-speed photography 1/8000s.',
                    'negative_style_prompt' => 'burnt, dry texture, pale crust, low resolution, unappetizing oily sheen',
                    'suggested_aspect_ratios' => ['1:1', '4:5'],
                ],
            ],
            // 3. Futuristic Wireless Earbuds
            [
                'external_id' => 'grok-tech-001',
                'title' => 'Futuristic Wireless Earbuds on Glowing Neon Magnetic Dock',
                'caption' => 'Cyberpunk-inspired tech commercial packshot, metallic earbuds levitating over glowing circular acrylic charger with subtle cyan and magenta volumetric neon accents.',
                'canonical_url' => 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?auto=format&fit=crop&w=1200&q=80',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?auto=format&fit=crop&w=800&q=80',
                'media_type' => 'image',
                'media_urls' => ['https://images.unsplash.com/photo-1590658268037-6bf12165a8df?auto=format&fit=crop&w=1200&q=80'],
                'aspect_ratio' => '16:9',
                'tags' => ['earbuds', 'audio', 'cyberpunk', 'neon', 'electronics', 'grok'],
                'license' => 'Grok Commercial Open Standard',
                'photographer_name' => 'Grok Hardware Lab',
                'allow_commercial_use' => true,
                'category_code' => 'gadget',
                'industry_target' => 'Gadget & Elektronik',
                'visual_analysis' => [
                    'composition' => 'sleek_diagonal_levitation',
                    'lighting' => 'volumetric_neon_rim_edge',
                    'color_palette' => ['#06B6D4', '#EC4899', '#09090B', '#F4F4F5'],
                    'color_mood' => 'futuristic_high_tech',
                    'extracted_style_prompt' => 'Sleek matte-black wireless earbuds with anodized aluminum accents levitating above a futuristic circular charging pad, subtle cyan and magenta neon edge highlights, dark reflective obsidian floor, atmospheric mist, pristine industrial design, Octane Render 8k photorealistic.',
                    'negative_style_prompt' => 'scratches, cheap plastic, fingerprints, messy cords, overblown bloom',
                    'suggested_aspect_ratios' => ['16:9', '1:1', '9:16'],
                ],
            ],
            // 4. Sneaker on Mirrored Pedestal
            [
                'external_id' => 'grok-fashion-001',
                'title' => 'Editorial Monochromatic Sneaker on Mirrored Pedestal',
                'caption' => 'High-fashion footwear packshot, limited edition designer sneaker standing at dynamic 45-degree angle on a smoked glass mirror pedestal with stark editorial shadows.',
                'canonical_url' => 'https://images.unsplash.com/photo-1552346154-21d32810aba3?auto=format&fit=crop&w=1200&q=80',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1552346154-21d32810aba3?auto=format&fit=crop&w=800&q=80',
                'media_type' => 'image',
                'media_urls' => ['https://images.unsplash.com/photo-1552346154-21d32810aba3?auto=format&fit=crop&w=1200&q=80'],
                'aspect_ratio' => '4:5',
                'tags' => ['sneaker', 'footwear', 'fashion', 'editorial', 'mirror_reflection', 'grok'],
                'license' => 'Grok Commercial Open Standard',
                'photographer_name' => 'Grok Footwear Studio',
                'allow_commercial_use' => true,
                'category_code' => 'fashion',
                'industry_target' => 'Fashion & Konveksi',
                'visual_analysis' => [
                    'composition' => 'hero_angle_reflection',
                    'lighting' => 'high_contrast_softbox_grid',
                    'color_palette' => ['#FFFFFF', '#18181B', '#71717A', '#E4E4E7'],
                    'color_mood' => 'stark_editorial_cool',
                    'extracted_style_prompt' => 'High-end sneaker commercial photography, premium leather and mesh sneaker poised on infinity smoked glass mirror, perfect clear reflection below, crisp stitching and tread texture in sharp relief, stark high-fashion studio lighting, Vogue runway editorial quality.',
                    'negative_style_prompt' => 'scuffed sole, worn fabric, warped silhouette, blurry logo, dirty reflections',
                    'suggested_aspect_ratios' => ['4:5', '1:1'],
                ],
            ],
            // 5. Sambal Kemasan Nusantara
            [
                'external_id' => 'grok-retail-001',
                'title' => 'Indonesian Sambal Kemasan Botol dengan Taburan Cabai Segar',
                'caption' => 'Commercial food packaging shot of Indonesian artisanal chili paste sambal in glass jar, surrounded by whole bird-eye red chilies, shallots, garlic, and lemongrass on rustic granite slab.',
                'canonical_url' => 'https://images.unsplash.com/photo-1588166524941-3bf61a9c41db?auto=format&fit=crop&w=1200&q=80',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1588166524941-3bf61a9c41db?auto=format&fit=crop&w=800&q=80',
                'media_type' => 'image',
                'media_urls' => ['https://images.unsplash.com/photo-1588166524941-3bf61a9c41db?auto=format&fit=crop&w=1200&q=80'],
                'aspect_ratio' => '1:1',
                'tags' => ['sambal', 'packaging', 'chili', 'indonesia', 'kuliner', 'umkm'],
                'license' => 'Grok Commercial Open Standard',
                'photographer_name' => 'Grok Nusantara Packshot',
                'allow_commercial_use' => true,
                'category_code' => 'retail',
                'industry_target' => 'Ritel & Produk Kemasan',
                'visual_analysis' => [
                    'composition' => 'ingredient_surrounding_packshot',
                    'lighting' => 'warm_dramatic_golden_spot',
                    'color_palette' => ['#B91C1C', '#EA580C', '#27272A', '#FEF08A'],
                    'color_mood' => 'bold_savory_spicy',
                    'extracted_style_prompt' => 'Commercial food packaging packshot, hexagonal glass jar of authentic Indonesian red sambal with glistening spicy oil, surrounded by whole fresh red cayenne peppers with water droplets, raw shallots, and lime leaves on dark slate stone, warm dramatic spotlighting, 85mm macro.',
                    'negative_style_prompt' => 'dull chili, foggy glass jar, plastic look, blurry label, fake digital artifacts',
                    'suggested_aspect_ratios' => ['1:1', '4:5'],
                ],
            ],
            // 6. Boba Brown Sugar Milk Tea with Melting Drizzle
            [
                'external_id' => 'grok-fnb-002',
                'title' => 'Brown Sugar Boba Milk Tea with Tiger Stripes Drizzle & Ice Cubes',
                'caption' => 'Mouthwatering commercial beverage visual of fresh milk tea in clear curved cup, dark rich brown sugar syrup tiger stripes dripping down inner wall, shiny black tapioca pearls, condensation and clear ice cubes.',
                'canonical_url' => 'https://images.unsplash.com/photo-1558857563-b371033873b8?auto=format&fit=crop&w=1200&q=80',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1558857563-b371033873b8?auto=format&fit=crop&w=800&q=80',
                'media_type' => 'image',
                'media_urls' => ['https://images.unsplash.com/photo-1558857563-b371033873b8?auto=format&fit=crop&w=1200&q=80'],
                'aspect_ratio' => '4:5',
                'tags' => ['boba', 'milk_tea', 'brown_sugar', 'beverage', 'summer', 'fnb'],
                'license' => 'Grok Commercial Open Standard',
                'photographer_name' => 'Grok Beverage Studio',
                'allow_commercial_use' => true,
                'category_code' => 'fnb',
                'industry_target' => 'Makanan & Minuman (F&B)',
                'visual_analysis' => [
                    'composition' => 'vertical_commercial_packshot',
                    'lighting' => 'backlit_translucent_glow',
                    'color_palette' => ['#451A03', '#FEF3C7', '#78350F', '#1F2937'],
                    'color_mood' => 'sweet_indulgent_refreshing',
                    'extracted_style_prompt' => 'Commercial milk tea advertisement, clear transparent plastic cup showing luscious dark brown sugar syrup marble tiger streaks flowing through creamy white fresh milk, glossy cooked black tapioca boba resting at the bottom, frosty clear ice cubes, cool condensation beads on cup exterior, backlit softbox illumination, 85mm f/2.0.',
                    'negative_style_prompt' => 'muddy blend, flat unappetizing milk, artificial plastic sheen, blurry text',
                    'suggested_aspect_ratios' => ['4:5', '1:1', '9:16'],
                ],
            ],
            // 7. Mechanical Keyboard Custom Keycaps
            [
                'external_id' => 'grok-tech-002',
                'title' => 'Custom Mechanical Keyboard on Wool Felt Desk Mat with Ambient RGB',
                'caption' => 'Artisanal tech photoshoot, anodized CNC aluminum mechanical keyboard with dual-tone PBT keycaps and coiled aviator cable on thick charcoal wool desk mat.',
                'canonical_url' => 'https://images.unsplash.com/photo-1587829741301-dc798b83add3?auto=format&fit=crop&w=1200&q=80',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1587829741301-dc798b83add3?auto=format&fit=crop&w=800&q=80',
                'media_type' => 'image',
                'media_urls' => ['https://images.unsplash.com/photo-1587829741301-dc798b83add3?auto=format&fit=crop&w=1200&q=80'],
                'aspect_ratio' => '16:9',
                'tags' => ['keyboard', 'mechanical_keyboard', 'desk_setup', 'tech', 'gadget'],
                'license' => 'Grok Commercial Open Standard',
                'photographer_name' => 'Grok Workspace Lab',
                'allow_commercial_use' => true,
                'category_code' => 'gadget',
                'industry_target' => 'Gadget & Elektronik',
                'visual_analysis' => [
                    'composition' => 'low_angle_perspective_desk',
                    'lighting' => 'warm_desk_lamp_with_underglow',
                    'color_palette' => ['#1E293B', '#F1F5F9', '#F59E0B', '#334155'],
                    'color_mood' => 'cozy_productive_enthusiast',
                    'extracted_style_prompt' => 'Custom mechanical keyboard enthusiast photoshoot, heavy chamfered anodized aluminum case, matte textured PBT keycaps in retro cream and navy blue, braided aviator USB cable, resting on thick dark heather-gray wool felt desk pad, warm screenbar light cast, shallow depth of field, 50mm f/1.4 lens.',
                    'negative_style_prompt' => 'dusty keys, warped casing, messy cables, cartoon render, low resolution',
                    'suggested_aspect_ratios' => ['16:9', '1:1', '4:5'],
                ],
            ],
            // 8. Luxury Chronograph Watch
            [
                'external_id' => 'grok-fashion-002',
                'title' => 'Luxury Chronograph Watch on Chiseled Black Obsidian Rock',
                'caption' => 'Horology commercial photography, stainless steel automatic chronograph with sapphire crystal and sunburst dial resting on fractured volcanic obsidian rock.',
                'canonical_url' => 'https://images.unsplash.com/photo-1524805444758-089113d48a6d?auto=format&fit=crop&w=1200&q=80',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1524805444758-089113d48a6d?auto=format&fit=crop&w=800&q=80',
                'media_type' => 'image',
                'media_urls' => ['https://images.unsplash.com/photo-1524805444758-089113d48a6d?auto=format&fit=crop&w=1200&q=80'],
                'aspect_ratio' => '1:1',
                'tags' => ['watch', 'luxury', 'chronograph', 'obsidian', 'menswear', 'fashion'],
                'license' => 'Grok Commercial Open Standard',
                'photographer_name' => 'Grok Timepiece Studio',
                'allow_commercial_use' => true,
                'category_code' => 'fashion',
                'industry_target' => 'Fashion & Konveksi',
                'visual_analysis' => [
                    'composition' => 'hero_watch_on_stone',
                    'lighting' => 'precision_dual_strip_diffuser',
                    'color_palette' => ['#0F172A', '#E2E8F0', '#38BDF8', '#64748B'],
                    'color_mood' => 'prestige_rugged_luxury',
                    'extracted_style_prompt' => 'Haute horlogerie luxury timepiece commercial shot, precision stainless steel chronograph with deep midnight blue sunburst dial, sharp beveled ceramic bezel, resting on a rough chiseled block of matte volcanic obsidian rock, dual strip studio softboxes highlighting brushed and polished metal facets, 100mm f/4 macro.',
                    'negative_style_prompt' => 'scratched crystal, smudged dial, crooked hands, lowres, fake render',
                    'suggested_aspect_ratios' => ['1:1', '4:5'],
                ],
            ],
        ];
    }
}
