<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Ai\Inspiration\Adapters\PinterestAdapter;
use App\Models\AiStudio\InspirationCategory;
use App\Models\AiStudio\InspirationItem;
use App\Models\AiStudio\InspirationSource;
use App\Models\AiStudio\InspirationSourcePolicy;
use App\Models\AiStudio\InspirationTrendScore;
use App\Models\AiStudio\InspirationVisualAnalysis;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PinterestSaasPromotionInspirationSeeder extends Seeder
{
    /**
     * Jalankan seeder untuk menambahkan inspirasi SaaS Promotion dari Pinterest ke database AI Studio.
     */
    public function run(): void
    {
        // 1. Pastikan Sumber Pinterest Terdaftar
        /** @var InspirationSource $source */
        $source = InspirationSource::firstOrCreate(
            ['code' => 'pinterest'],
            [
                'name' => 'Pinterest',
                'driver_class' => PinterestAdapter::class,
                'is_active' => true,
                'fetch_frequency_minutes' => 60,
                'rate_limit_per_minute' => 30,
                'supports_image' => true,
                'supports_video' => false,
            ]
        );

        if (! $source->policy) {
            InspirationSourcePolicy::firstOrCreate(
                ['source_id' => $source->id],
                [
                    'allow_commercial_use' => true,
                    'require_attribution' => false,
                    'retention_days' => 365,
                    'disclaimer_template' => 'Referensi visual kurasi Pinterest untuk inspirasi rekayasa prompt AI Studio.',
                    'notes' => 'Curated Pinterest SaaS & Software Promotion Board',
                ]
            );
        }

        // 2. Pastikan Kategori SaaS Terdaftar
        /** @var InspirationCategory $category */
        $category = InspirationCategory::firstOrCreate(
            ['slug' => 'saas-software-promotion'],
            [
                'name' => 'SaaS & Software Promotion',
                'industry' => 'services',
                'sort_order' => 6,
                'is_active' => true,
            ]
        );

        // 3. Koleksi 12 Item Inspirasi SaaS Promotion Pinterest
        $saasItems = $this->getSaasPromotionItems();

        foreach ($saasItems as $itemData) {
            /** @var InspirationItem $item */
            $item = InspirationItem::updateOrCreate(
                [
                    'source_id' => $source->id,
                    'external_id' => $itemData['external_id'],
                ],
                [
                    'category_id' => $category->id,
                    'title' => $itemData['title'],
                    'caption' => $itemData['caption'],
                    'canonical_url' => $itemData['canonical_url'],
                    'thumbnail_url' => $itemData['thumbnail_url'],
                    'media_type' => 'image',
                    'media_urls' => $itemData['media_urls'],
                    'tags' => $itemData['tags'],
                    'industry_target' => $itemData['industry_target'],
                    'view_count' => $itemData['view_count'] ?? rand(12500, 85000),
                    'like_count' => $itemData['like_count'] ?? rand(1400, 9200),
                    'comment_count' => $itemData['comment_count'] ?? rand(120, 680),
                    'share_count' => $itemData['share_count'] ?? rand(340, 2100),
                    'save_count' => $itemData['save_count'] ?? rand(850, 4300),
                    'is_curated' => true,
                    'curated_notes' => 'Kurasi resmi Pinterest SaaS Promotion Board untuk inspirasi marketing AI Studio COOCA.',
                    'status' => 'active',
                    'collected_at' => now(),
                ]
            );

            // Analisis Visual Multimodal AI
            if (! empty($itemData['visual_analysis'])) {
                $va = $itemData['visual_analysis'];
                InspirationVisualAnalysis::updateOrCreate(
                    ['inspiration_item_id' => $item->id],
                    [
                        'composition' => $va['composition'] ?? 'isometric_glassmorphic_ui',
                        'lighting' => $va['lighting'] ?? 'studio_soft',
                        'color_palette' => $va['color_palette'] ?? ['#007AFF', '#5856D6', '#1C1C1E', '#FFFFFF'],
                        'color_mood' => $va['color_mood'] ?? 'modern_tech_luxury',
                        'visual_hook' => $va['visual_hook'] ?? 'High-converting SaaS promotional UI layout',
                        'cta_pattern' => $va['cta_pattern'] ?? 'bottom_right_pill',
                        'extracted_style_prompt' => $va['extracted_style_prompt'],
                        'negative_style_prompt' => $va['negative_style_prompt'] ?? 'blurry, low quality, distorted text, ugly artifacts',
                        'suggested_aspect_ratios' => $va['suggested_aspect_ratios'] ?? ['1:1', '16:9', '9:16', '4:5'],
                        'safety_score' => 100.00,
                        'analyzed_at' => now(),
                    ]
                );
            }

            // Skor Trend & Algoritma Kecepatan Viral
            InspirationTrendScore::updateOrCreate(
                ['inspiration_item_id' => $item->id],
                [
                    'trend_score' => $itemData['trend_score']['trend_score'] ?? 95.50,
                    'virality_velocity' => $itemData['trend_score']['virality_velocity'] ?? 9.20,
                    'engagement_rate' => $itemData['trend_score']['engagement_rate'] ?? 0.0880,
                    'recency_weight' => 1.0000,
                    'source_credibility_weight' => 1.0000,
                    'scored_at' => now(),
                ]
            );
        }
    }

    /**
     * Data master 12 inspirasi visual SaaS Promotion Pinterest.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getSaasPromotionItems(): array
    {
        return [
            // 1. SaaS B2B Analytics & Revenue Dashboard
            [
                'external_id' => 'pin-saas-001',
                'title' => 'B2B SaaS Analytics & Revenue Growth Dashboard Banner',
                'caption' => 'Visual inspirasi banner promosi SaaS B2B dengan komposisi 3D glassmorphic tablet & laptop menampilkan sales analytics, kurva pertumbuhan omzet, badge MRR/ARR, dan tata cahaya royal blue.',
                'canonical_url' => 'https://id.pinterest.com/search/pins/?q=saas%20promotion&rs=typed#pin-saas-001',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=800&q=80',
                'media_urls' => ['https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=1200&q=80'],
                'aspect_ratio' => '16:9',
                'tags' => ['saas', 'promotion', 'analytics', 'dashboard', 'b2b', 'revenue', 'finops', 'cloud'],
                'industry_target' => 'SaaS, Software & Digital Business',
                'visual_analysis' => [
                    'composition' => 'isometric_glassmorphic_ui',
                    'lighting' => 'soft_studio_volumetric_rim',
                    'color_palette' => ['#007AFF', '#5856D6', '#34C759', '#1C1C1E', '#FFFFFF'],
                    'color_mood' => 'modern_tech_luxury',
                    'visual_hook' => '3D layered dashboard evoking high growth and enterprise reliability',
                    'cta_pattern' => 'bottom_right_pill',
                    'extracted_style_prompt' => 'High-converting modern B2B SaaS marketing banner, floating isometric glassmorphic iPad and MacBook display showcasing clean sales analytics dashboard with vibrant green revenue growth curve, floating 3D glossy metric badges for MRR and ARR, dark titanium desk, soft studio volumetric backlighting in electric royal blue and warm amber, ultra-clean Apple HIG corporate aesthetic, 8k resolution, cinematic commercial render.',
                    'negative_style_prompt' => 'cluttered, blurry, pixelated graphs, ugly fonts, dark gloomy artifacts, amateur render',
                    'suggested_aspect_ratios' => ['16:9', '1:1', '4:5'],
                ],
                'trend_score' => [
                    'trend_score' => 98.50,
                    'virality_velocity' => 9.40,
                    'engagement_rate' => 0.0980,
                ],
            ],

            // 2. Generative AI Platform & Feature Launch
            [
                'external_id' => 'pin-saas-002',
                'title' => 'Generative AI Platform & Intelligent Workflow Automation Story',
                'caption' => 'Materi promosi peluncuran fitur baru AI Studio berbasis format vertikal 9:16 untuk Instagram Reels & TikTok dengan aura neon cyan dan partikel ungu futuristik.',
                'canonical_url' => 'https://id.pinterest.com/search/pins/?q=saas%20promotion&rs=typed#pin-saas-002',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?auto=format&fit=crop&w=800&q=80',
                'media_urls' => ['https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?auto=format&fit=crop&w=1200&q=80'],
                'aspect_ratio' => '9:16',
                'tags' => ['saas', 'promotion', 'ai', 'generative-ai', 'launch', 'story', 'reels', 'automation'],
                'industry_target' => 'SaaS, Software & Digital Business',
                'visual_analysis' => [
                    'composition' => 'vertical_smartphone_portal',
                    'lighting' => 'neon_cyberpunk_glow',
                    'color_palette' => ['#AF52DE', '#5856D6', '#00F0FF', '#0D0D11'],
                    'color_mood' => 'futuristic_electric_vibrant',
                    'visual_hook' => 'Magical AI spark particle transformation conveying instant creativity',
                    'cta_pattern' => 'floating_bottom_sheet',
                    'extracted_style_prompt' => 'Vertical 9:16 mobile promotional story for Generative AI SaaS app, floating bezel-less smartphone mockup with glowing ethereal purple and neon cyan particles emitting from prompt search bar, transforming raw sketch into pristine 3D rendered product, sleek dark-mode glassmorphic interface, cyberpunk ambient rim light, hyper-detailed commercial visual.',
                    'negative_style_prompt' => 'dull, noisy, lowres, deformed phone, artificial plastic texture, bad anatomy',
                    'suggested_aspect_ratios' => ['9:16', '4:5', '1:1'],
                ],
                'trend_score' => [
                    'trend_score' => 97.80,
                    'virality_velocity' => 9.60,
                    'engagement_rate' => 0.1120,
                ],
            ],

            // 3. Omnichannel Cloud POS & Retail Inventory
            [
                'external_id' => 'pin-saas-003',
                'title' => 'Omnichannel Cloud POS & Retail Inventory Management Banner',
                'caption' => 'Foto komersial aplikasi kasir cloud modern POS di kafe kekinian dengan sinkronisasi stok realtime, tablet kasir elegan, dan pencahayaan alami kafe.',
                'canonical_url' => 'https://id.pinterest.com/search/pins/?q=saas%20promotion&rs=typed#pin-saas-003',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1556742049-0a67c5574f73?auto=format&fit=crop&w=800&q=80',
                'media_urls' => ['https://images.unsplash.com/photo-1556742049-0a67c5574f73?auto=format&fit=crop&w=1200&q=80'],
                'aspect_ratio' => '1:1',
                'tags' => ['saas', 'promotion', 'pos', 'retail', 'fnb', 'inventory', 'omnichannel', 'kasir'],
                'industry_target' => 'SaaS, Software & Digital Business',
                'visual_analysis' => [
                    'composition' => 'lifestyle_checkout_point',
                    'lighting' => 'natural_warm_morning_sunlight',
                    'color_palette' => ['#F59E0B', '#10B981', '#374151', '#FFFFFF'],
                    'color_mood' => 'inviting_reliable_warmth',
                    'visual_hook' => 'Seamless digital tablet blending into real-world business operation',
                    'cta_pattern' => 'bottom_badge_action',
                    'extracted_style_prompt' => 'Commercial retail tech photography, sleek white modern tablet POS terminal on solid oak counter of artisan specialty coffee shop, displaying real-time inventory sync and sales summary, gentle morning natural sunlight through window, blurred cozy barista background, high-end lifestyle SaaS branding, 50mm f/1.8 lens.',
                    'negative_style_prompt' => 'messy counter, chaotic wires, glare on screen, blurry app UI',
                    'suggested_aspect_ratios' => ['1:1', '4:5', '16:9'],
                ],
                'trend_score' => [
                    'trend_score' => 96.20,
                    'virality_velocity' => 8.90,
                    'engagement_rate' => 0.0890,
                ],
            ],

            // 4. SaaS Subscription Flash Sale 50% Off
            [
                'external_id' => 'pin-saas-004',
                'title' => 'SaaS Annual Plan Subscription Flash Sale 50% Discount Card',
                'caption' => 'Desain materi iklan promosi flash sale diskon langganan tahunan SaaS dengan kartu harga 3D melayang, stempel garansi emas, dan palet warna midnight blue.',
                'canonical_url' => 'https://id.pinterest.com/search/pins/?q=saas%20promotion&rs=typed#pin-saas-004',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=800&q=80',
                'media_urls' => ['https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=1200&q=80'],
                'aspect_ratio' => '1:1',
                'tags' => ['saas', 'promotion', 'pricing', 'discount', 'flash-sale', 'annual-plan', 'marketing'],
                'industry_target' => 'SaaS, Software & Digital Business',
                'visual_analysis' => [
                    'composition' => 'hero_pricing_card_center',
                    'lighting' => 'dramatic_studio_golden_glow',
                    'color_palette' => ['#0A192F', '#F59E0B', '#3B82F6', '#FFFFFF'],
                    'color_mood' => 'exclusive_high_conversion',
                    'visual_hook' => 'Bold 50% discount badge and 3D metallic toggle providing irresistible value',
                    'cta_pattern' => 'center_pill_primary',
                    'extracted_style_prompt' => 'Eye-catching SaaS promotion social media feed banner, stylized 3D floating pricing card with glossy golden badge "PROMO 50% ANNUAL PLAN", glowing toggle switch, floating 3D checkmark icons and credit card chip, deep midnight blue background with subtle grid lines, studio commercial advertising quality, ultra-sharp detail.',
                    'negative_style_prompt' => 'cheap banner look, noisy texture, unreadable text, low resolution',
                    'suggested_aspect_ratios' => ['1:1', '4:5', '16:9'],
                ],
                'trend_score' => [
                    'trend_score' => 99.10,
                    'virality_velocity' => 9.80,
                    'engagement_rate' => 0.1240,
                ],
            ],

            // 5. Omnichannel Customer Support & Helpdesk
            [
                'external_id' => 'pin-saas-005',
                'title' => 'Omnichannel Customer Support & AI Helpdesk Social Media Post',
                'caption' => 'Visual editorial promosi helpdesk customer service multi-kanal dengan gelembung chat 3D transparan, notifikasi WhatsApp/Email, dan senyum ramah CS profesional.',
                'canonical_url' => 'https://id.pinterest.com/search/pins/?q=saas%20promotion&rs=typed#pin-saas-005',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=800&q=80',
                'media_urls' => ['https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=1200&q=80'],
                'aspect_ratio' => '4:5',
                'tags' => ['saas', 'promotion', 'crm', 'helpdesk', 'customer-service', 'omnichannel', 'chat'],
                'industry_target' => 'SaaS, Software & Digital Business',
                'visual_analysis' => [
                    'composition' => 'editorial_human_centric',
                    'lighting' => 'bright_airy_corporate_daylight',
                    'color_palette' => ['#2563EB', '#10B981', '#F3F4F6', '#1E293B'],
                    'color_mood' => 'trustworthy_collaborative',
                    'visual_hook' => 'Human connection augmented by instant AI omnichannel assistance',
                    'cta_pattern' => 'bottom_banner_pill',
                    'extracted_style_prompt' => 'Portrait 4:5 modern B2B software promotion visual, confident friendly female customer success manager wearing wireless headset with laptop, surrounded by floating glassmorphic 3D chat bubbles and WhatsApp notification pills with lightning speed response badges, warm sunlit contemporary office, editorial corporate photography style.',
                    'negative_style_prompt' => 'deformed facial features, artificial smile, cluttered desk, dark lighting',
                    'suggested_aspect_ratios' => ['4:5', '1:1', '9:16'],
                ],
                'trend_score' => [
                    'trend_score' => 95.70,
                    'virality_velocity' => 8.80,
                    'engagement_rate' => 0.0910,
                ],
            ],

            // 6. Agile Project Management & Team Collaboration Kanban
            [
                'external_id' => 'pin-saas-006',
                'title' => 'Agile Project Management & Team Collaboration Kanban Banner',
                'caption' => 'Visual lanskap 16:9 banner modern yang menampilkan kartu task manajemen proyek 3D melayang dengan status warna-warni dan alur kolaborasi tim.',
                'canonical_url' => 'https://id.pinterest.com/search/pins/?q=saas%20promotion&rs=typed#pin-saas-006',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1531403009284-440f080d1e12?auto=format&fit=crop&w=800&q=80',
                'media_urls' => ['https://images.unsplash.com/photo-1531403009284-440f080d1e12?auto=format&fit=crop&w=1200&q=80'],
                'aspect_ratio' => '16:9',
                'tags' => ['saas', 'promotion', 'project-management', 'kanban', 'collaboration', 'agile', 'productivity'],
                'industry_target' => 'SaaS, Software & Digital Business',
                'visual_analysis' => [
                    'composition' => 'layered_task_flow_wide',
                    'lighting' => 'diffused_soft_studio_lighting',
                    'color_palette' => ['#6366F1', '#EC4899', '#10B981', '#F8FAFC'],
                    'color_mood' => 'creative_dynamic_organized',
                    'visual_hook' => 'Tactile floating 3D cards showing satisfying task completion flow',
                    'cta_pattern' => 'right_action_cluster',
                    'extracted_style_prompt' => 'Cinematic wide 16:9 tech banner, modern creative agency workspace with floating translucent 3D Kanban task cards, colorful status tags in mint green, coral, and violet, team avatar bubbles connecting via glowing subtle bezier curves, sleek Scandinavian oak desk, crisp studio depth of field, 8k resolution.',
                    'negative_style_prompt' => 'disorganized visual, messy text, harsh shadows, grainy background',
                    'suggested_aspect_ratios' => ['16:9', '1:1', '4:5'],
                ],
                'trend_score' => [
                    'trend_score' => 96.80,
                    'virality_velocity' => 9.10,
                    'engagement_rate' => 0.0940,
                ],
            ],

            // 7. Fintech Automated Invoicing & Reconciliation
            [
                'external_id' => 'pin-saas-007',
                'title' => 'Fintech Automated Invoicing & Payment Reconciliation Visual',
                'caption' => 'Visual minimalis elegan materi promosi modul keuangan SaaS dengan kartu pembayaran korporat matte black, rekonsiliasi bank otomatis, dan aksen hijau emerald.',
                'canonical_url' => 'https://id.pinterest.com/search/pins/?q=saas%20promotion&rs=typed#pin-saas-007',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1559526324-4b87b5e36e44?auto=format&fit=crop&w=800&q=80',
                'media_urls' => ['https://images.unsplash.com/photo-1559526324-4b87b5e36e44?auto=format&fit=crop&w=1200&q=80'],
                'aspect_ratio' => '1:1',
                'tags' => ['saas', 'promotion', 'fintech', 'invoice', 'payment', 'finance', 'accounting'],
                'industry_target' => 'SaaS, Software & Digital Business',
                'visual_analysis' => [
                    'composition' => 'monolithic_fintech_still_life',
                    'lighting' => 'brushed_specular_edge_highlight',
                    'color_palette' => ['#0F172A', '#059669', '#E2E8F0', '#10B981'],
                    'color_mood' => 'sophisticated_swiss_minimalist',
                    'visual_hook' => 'Matte black premium card and zero-error digital ledger reconciliation',
                    'cta_pattern' => 'bottom_subtle_button',
                    'extracted_style_prompt' => 'Minimalist commercial fintech software visual, matte black corporate credit card hovering above iPad showing automatic invoice generator and QRIS instant settlement status, subtle metallic reflections, emerald green financial growth accents, clean slate marble surface, premium Swiss graphic design aesthetic, Hasselblad medium format render.',
                    'negative_style_prompt' => 'cheap plastic texture, oversaturated colors, blurry numbers, cartoon style',
                    'suggested_aspect_ratios' => ['1:1', '4:5', '16:9'],
                ],
                'trend_score' => [
                    'trend_score' => 97.40,
                    'virality_velocity' => 9.30,
                    'engagement_rate' => 0.1010,
                ],
            ],

            // 8. Enterprise Cybersecurity & Cloud Guard
            [
                'external_id' => 'pin-saas-008',
                'title' => 'Enterprise Cybersecurity & Zero-Trust Cloud Guard Banner',
                'caption' => 'Banner promosi keamanan data cloud SaaS dengan perisai holografik bercahaya 3D, kunci biometrik, dan aliran data terenkripsi sapphire blue.',
                'canonical_url' => 'https://id.pinterest.com/search/pins/?q=saas%20promotion&rs=typed#pin-saas-008',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1563986768609-322da13575f3?auto=format&fit=crop&w=800&q=80',
                'media_urls' => ['https://images.unsplash.com/photo-1563986768609-322da13575f3?auto=format&fit=crop&w=1200&q=80'],
                'aspect_ratio' => '16:9',
                'tags' => ['saas', 'promotion', 'security', 'cybersecurity', 'cloud', 'encryption', 'enterprise'],
                'industry_target' => 'SaaS, Software & Digital Business',
                'visual_analysis' => [
                    'composition' => 'center_shield_hologram',
                    'lighting' => 'neon_sapphire_ambient_rim',
                    'color_palette' => ['#0284C7', '#0369A1', '#0C4A6E', '#030712'],
                    'color_mood' => 'impenetrable_secure_elite',
                    'visual_hook' => 'Luminescent shield giving absolute confidence in enterprise data privacy',
                    'cta_pattern' => 'right_secure_seal',
                    'extracted_style_prompt' => 'High-tech cybersecurity SaaS promotional banner, glowing 3D holographic digital shield with interlocking biometric lock rings protecting cloud server nodes, streams of encrypted binary data in sapphire blue and electric teal, dark futuristic command center backdrop, hyper-realistic commercial render, octane render 8k.',
                    'negative_style_prompt' => 'low resolution, green matrix cliches, cartoon shield, pixelation',
                    'suggested_aspect_ratios' => ['16:9', '1:1'],
                ],
                'trend_score' => [
                    'trend_score' => 96.50,
                    'virality_velocity' => 9.00,
                    'engagement_rate' => 0.0870,
                ],
            ],

            // 9. Mobile-First Micro-SaaS Productivity
            [
                'external_id' => 'pin-saas-009',
                'title' => 'Mobile-First Micro-SaaS Productivity & Habit Tracker Story',
                'caption' => 'Visual vertikal promosi aplikasi SaaS mobile-first dengan mockup smartphone, widget performa harian, bola kaca frosted, dan latar pastel modern.',
                'canonical_url' => 'https://id.pinterest.com/search/pins/?q=saas%20promotion&rs=typed#pin-saas-009',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1512941937669-90a1b58e7e9c?auto=format&fit=crop&w=800&q=80',
                'media_urls' => ['https://images.unsplash.com/photo-1512941937669-90a1b58e7e9c?auto=format&fit=crop&w=1200&q=80'],
                'aspect_ratio' => '9:16',
                'tags' => ['saas', 'promotion', 'mobile-app', 'productivity', 'habits', 'story', 'reels', 'ios'],
                'industry_target' => 'SaaS, Software & Digital Business',
                'visual_analysis' => [
                    'composition' => 'floating_device_minimal',
                    'lighting' => 'soft_diffuse_gradient_studio',
                    'color_palette' => ['#EC4899', '#8B5CF6', '#F3E8FF', '#FFFFFF'],
                    'color_mood' => 'fresh_playful_inspirational',
                    'visual_hook' => 'Addictive streak badges motivating instant daily user retention',
                    'cta_pattern' => 'bottom_swipe_indicator',
                    'extracted_style_prompt' => 'Vertical 9:16 smartphone mockup for TikTok and Reels, floating iPhone 16 Pro displaying vibrant modern productivity dashboard with daily streak flame badges and progress circular meters, soft pastel gradient backdrop with frosted glass spheres, clean aesthetic typography space, studio lighting, hyper-realistic.',
                    'negative_style_prompt' => 'bent phone, blurry screen icons, heavy shadow, dark gloomy colors',
                    'suggested_aspect_ratios' => ['9:16', '4:5', '1:1'],
                ],
                'trend_score' => [
                    'trend_score' => 98.10,
                    'virality_velocity' => 9.70,
                    'engagement_rate' => 0.1180,
                ],
            ],

            // 10. Automated HR & Payroll Cloud Solution
            [
                'external_id' => 'pin-saas-010',
                'title' => 'Automated HR & Payroll Slip Gaji Cloud Solution Visual',
                'caption' => 'Visual editorial promosi platform HR & absensi digital dengan tablet absensi GPS, slip gaji otomatis berstempel digital, dan suasana kantor cerah.',
                'canonical_url' => 'https://id.pinterest.com/search/pins/?q=saas%20promotion&rs=typed#pin-saas-010',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=800&q=80',
                'media_urls' => ['https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=1200&q=80'],
                'aspect_ratio' => '4:5',
                'tags' => ['saas', 'promotion', 'hrm', 'payroll', 'absensi', 'slip-gaji', 'karyawan', 'hrd'],
                'industry_target' => 'SaaS, Software & Digital Business',
                'visual_analysis' => [
                    'composition' => 'collaborative_boardroom_scene',
                    'lighting' => 'crisp_natural_window_light',
                    'color_palette' => ['#1E40AF', '#0D9488', '#F1F5F9', '#1E293B'],
                    'color_mood' => 'professional_warm_harmonious',
                    'visual_hook' => 'Empowered workplace with automated payroll removing administrative friction',
                    'cta_pattern' => 'bottom_badge_action',
                    'extracted_style_prompt' => 'Professional HR tech promotion image, sleek tablet displaying automated payroll and GPS employee attendance dashboard, floating salary slip cards with verified digital seal, bright airy corporate boardroom background with diverse happy team, natural morning light, trustworthy commercial look, 85mm f/1.8 lens.',
                    'negative_style_prompt' => 'artificial poses, distorted hands, cold clinical lighting, bad focus',
                    'suggested_aspect_ratios' => ['4:5', '1:1', '16:9'],
                ],
                'trend_score' => [
                    'trend_score' => 95.30,
                    'virality_velocity' => 8.70,
                    'engagement_rate' => 0.0860,
                ],
            ],

            // 11. Supply Chain & Wholesale Distribution Portal
            [
                'external_id' => 'pin-saas-011',
                'title' => 'Supply Chain & B2B Wholesale Distribution Portal Banner',
                'caption' => 'Visual 16:9 sistem logistik & gudang pintar dengan armada pengiriman otomatis, peta rute GPS realtime, dan pencahayaan industri berteknologi tinggi.',
                'canonical_url' => 'https://id.pinterest.com/search/pins/?q=saas%20promotion&rs=typed#pin-saas-011',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?auto=format&fit=crop&w=800&q=80',
                'media_urls' => ['https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?auto=format&fit=crop&w=1200&q=80'],
                'aspect_ratio' => '16:9',
                'tags' => ['saas', 'promotion', 'supply-chain', 'logistics', 'warehouse', 'wholesale', 'b2b', 'distribution'],
                'industry_target' => 'SaaS, Software & Digital Business',
                'visual_analysis' => [
                    'composition' => 'isometric_smart_warehouse_hub',
                    'lighting' => 'high_contrast_industrial_ambient',
                    'color_palette' => ['#D97706', '#2563EB', '#1E293B', '#F8FAFC'],
                    'color_mood' => 'robust_efficient_speed',
                    'visual_hook' => 'Precision tracking reducing stockout and route transit delays',
                    'cta_pattern' => 'right_action_cluster',
                    'extracted_style_prompt' => 'Modern logistics tech banner, 3D isometric warehouse hub with automated delivery fleet trucks, tablet displaying real-time route optimization map with glowing GPS waypoints, high-contrast industrial lighting, amber hazard accents against cool slate grey, cutting-edge B2B distribution software visual, 8k render.',
                    'negative_style_prompt' => 'messy warehouse, cartoon truck, bad isometric angle, dull lighting',
                    'suggested_aspect_ratios' => ['16:9', '1:1'],
                ],
                'trend_score' => [
                    'trend_score' => 96.10,
                    'virality_velocity' => 8.90,
                    'engagement_rate' => 0.0890,
                ],
            ],

            // 12. E-Commerce Conversion & Omnichannel Sales Booster
            [
                'external_id' => 'pin-saas-012',
                'title' => 'E-Commerce Conversion & Omnichannel Sales Booster Graphic',
                'caption' => 'Grafis promosi booster penjualan omnichannel dengan troli belanja 3D dinamis, grafik lonjakan konversi 300%, dan badge bintang kepuasan pelanggan.',
                'canonical_url' => 'https://id.pinterest.com/search/pins/?q=saas%20promotion&rs=typed#pin-saas-012',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1556740758-90de374c12ad?auto=format&fit=crop&w=800&q=80',
                'media_urls' => ['https://images.unsplash.com/photo-1556740758-90de374c12ad?auto=format&fit=crop&w=1200&q=80'],
                'aspect_ratio' => '1:1',
                'tags' => ['saas', 'promotion', 'ecommerce', 'conversion', 'sales-boost', 'cart', 'marketing', 'omnichannel'],
                'industry_target' => 'SaaS, Software & Digital Business',
                'visual_analysis' => [
                    'composition' => 'dynamic_exponential_growth_surge',
                    'lighting' => 'vibrant_gradient_studio_spotlight',
                    'color_palette' => ['#4F46E5', '#F97316', '#10B981', '#FFFFFF'],
                    'color_mood' => 'exhilarating_high_velocity',
                    'visual_hook' => 'Rocketing conversion curve proving 3x sales growth for merchants',
                    'cta_pattern' => 'center_pill_primary',
                    'extracted_style_prompt' => 'High-energy e-commerce SaaS promotion graphic, 3D shopping cart accelerating with fiery trail beside dynamic sales funnel chart showing 300% conversion boost, floating order notification bells and star rating badges, vibrant electric indigo and sunset orange lighting, modern SaaS marketing design, ultra-crisp detail.',
                    'negative_style_prompt' => 'cheap vector clip art, pixelated chart, dark gloomy background',
                    'suggested_aspect_ratios' => ['1:1', '4:5', '16:9'],
                ],
                'trend_score' => [
                    'trend_score' => 98.90,
                    'virality_velocity' => 9.60,
                    'engagement_rate' => 0.1190,
                ],
            ],
        ];
    }
}

