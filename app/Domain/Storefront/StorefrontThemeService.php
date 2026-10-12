<?php

declare(strict_types=1);

namespace App\Domain\Storefront;

use App\Models\Business;
use App\Models\BusinessLandingPage;

final class StorefrontThemeService
{
    /**
     * Complete catalogue of 21 Distinct Storefront Landing Page Themes.
     * Each theme has unique visual identity, typography, layout archetype, and palette.
     *
     * @var array<string, array<string, mixed>>
     */
    private const THEMES = [
        // 1. Business Corporate
        'business_corporate' => [
            'id' => 'business_corporate',
            'bundle_dir' => 'business_corporate',
            'name' => 'Business Corporate',
            'industry' => 'Perusahaan, Konsultan, Supplier & B2B',
            'font_heading' => 'Plus Jakarta Sans',
            'font_body' => 'Inter',
            'google_fonts' => 'family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Inter:wght@400;500;600',
            'primary_color' => '#2563EB',
            'accent_color' => '#0F172A',
            'bg_color' => '#F8FAFC',
            'card_bg' => '#FFFFFF',
            'header_dark' => false,
            'hero_style' => 'corporate_split',
            'badge_bg' => '#2563EB15',
            'badge_text' => '#2563EB',
            'border_radius' => '12px',
            'description' => 'Arsitektur formal, statistik kredibilitas B2B, legalitas resmi, dan alur permintaan penawaran harga (RFQ).',
        ],

        // 2. Modern UMKM
        'modern_umkm' => [
            'id' => 'modern_umkm',
            'bundle_dir' => 'modern_umkm',
            'name' => 'Modern UMKM',
            'industry' => 'Usaha Kecil, Usaha Rumahan & Bisnis Umum',
            'font_heading' => 'Plus Jakarta Sans',
            'font_body' => 'Inter',
            'google_fonts' => 'family=Plus+Jakarta+Sans:wght@600;700;800&family=Inter:wght@400;500;600',
            'primary_color' => '#059669',
            'accent_color' => '#D97706',
            'bg_color' => '#F0FDF4',
            'card_bg' => '#FFFFFF',
            'header_dark' => false,
            'hero_style' => 'friendly_local',
            'badge_bg' => '#05966915',
            'badge_text' => '#059669',
            'border_radius' => '16px',
            'description' => 'Nuansa hangat, dekat dengan pelanggan, badge keaslian produk lokal, dan pemesanan cepat via WhatsApp.',
        ],

        // 3. Online Store
        'online_store' => [
            'id' => 'online_store',
            'bundle_dir' => 'online_store',
            'name' => 'Online Store',
            'industry' => 'Toko Online, Fashion, Elektronik & Retail',
            'font_heading' => 'Urbanist',
            'font_body' => 'Inter',
            'google_fonts' => 'family=Urbanist:wght@600;700;800;900&family=Inter:wght@400;500;600',
            'primary_color' => '#18181B',
            'accent_color' => '#6366F1',
            'bg_color' => '#FFFFFF',
            'card_bg' => '#FAFAFA',
            'header_dark' => false,
            'hero_style' => 'commerce_hero',
            'badge_bg' => '#18181B15',
            'badge_text' => '#18181B',
            'border_radius' => '16px',
            'description' => 'Commerce-first layout, etalase produk terstruktur, filter kategori cepat, promo banner, dan checkout instan.',
        ],

        // 4. Product Showcase
        'product_showcase' => [
            'id' => 'product_showcase',
            'bundle_dir' => 'product_showcase',
            'name' => 'Product Showcase',
            'industry' => 'Produk Unggulan, Skincare & Brand Inovatif',
            'font_heading' => 'Syne',
            'font_body' => 'Plus Jakarta Sans',
            'google_fonts' => 'family=Syne:wght@600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600',
            'primary_color' => '#BE185D',
            'accent_color' => '#D4AF37',
            'bg_color' => '#FDF8F6',
            'card_bg' => '#FFFFFF',
            'header_dark' => false,
            'hero_style' => 'luxury_spotlight',
            'badge_bg' => '#BE185D15',
            'badge_text' => '#BE185D',
            'border_radius' => '20px',
            'description' => 'Fokus sinematik pada produk hero/unggulan, visual beresolusi tinggi, rincian keunggulan formula, dan CTA konversi tinggi.',
        ],

        // 5. Food & Restaurant
        'food_restaurant' => [
            'id' => 'food_restaurant',
            'bundle_dir' => 'food_restaurant',
            'name' => 'Food & Restaurant',
            'industry' => 'Restoran, Rumah Makan, Warung & Franchise F&B',
            'font_heading' => 'DM Serif Display',
            'font_body' => 'Plus Jakarta Sans',
            'google_fonts' => 'family=DM+Serif+Display:ital@0;1&family=Plus+Jakarta+Sans:wght@400;500;600;700',
            'primary_color' => '#991B1B',
            'accent_color' => '#D97706',
            'bg_color' => '#FFFBEB',
            'card_bg' => '#FFFFFF',
            'header_dark' => false,
            'hero_style' => 'warm_editorial',
            'badge_bg' => '#991B1B15',
            'badge_text' => '#991B1B',
            'border_radius' => '16px',
            'description' => 'Visual selera makan menggugah, porsi hidangan spesial, badge kehalalan, serta alur reservasi meja keluarga.',
        ],

        // 6. Coffee & Beverage
        'coffee_beverage' => [
            'id' => 'coffee_beverage',
            'bundle_dir' => 'coffee_beverage',
            'name' => 'Coffee & Beverage',
            'industry' => 'Coffee Shop, Cafe, Bubble Tea & Kedai Minuman',
            'font_heading' => 'Playfair Display',
            'font_body' => 'Inter',
            'google_fonts' => 'family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=Inter:wght@400;500;600',
            'primary_color' => '#8B5A2B',
            'accent_color' => '#C88A58',
            'bg_color' => '#FAF7F2',
            'card_bg' => '#FFFFFF',
            'header_dark' => false,
            'hero_style' => 'roast_artisan',
            'badge_bg' => '#8B5A2B1A',
            'badge_text' => '#8B5A2B',
            'border_radius' => '18px',
            'description' => 'Aura hangat aroma biji sangrai, rekomendasi barista, tasting notes kopi, dan pemesanan meja kafe.',
        ],

        // 7. Bakery & Cake
        'bakery_cake' => [
            'id' => 'bakery_cake',
            'bundle_dir' => 'bakery_cake',
            'name' => 'Bakery & Cake',
            'industry' => 'Bakery, Cake Shop, Pastry, Dessert & Custom Cake',
            'font_heading' => 'Cormorant Garamond',
            'font_body' => 'Poppins',
            'google_fonts' => 'family=Cormorant+Garamond:ital,wght@0,600;0,700;1,600&family=Poppins:wght@400;500;600',
            'primary_color' => '#D97706',
            'accent_color' => '#BE185D',
            'bg_color' => '#FFFDF5',
            'card_bg' => '#FFFFFF',
            'header_dark' => false,
            'hero_style' => 'oven_gold',
            'badge_bg' => '#D9770615',
            'badge_text' => '#D97706',
            'border_radius' => '18px',
            'description' => 'Sentuhan kehangatan oven toko roti, etalase kue tart lapis, preorder hampers perayaan, dan pesanan custom cake.',
        ],

        // 8. Service Business
        'service_business' => [
            'id' => 'service_business',
            'bundle_dir' => 'service_business',
            'name' => 'Service Business',
            'industry' => 'Laundry, Salon, Cleaning Service & Jasa Perawatan',
            'font_heading' => 'Plus Jakarta Sans',
            'font_body' => 'Inter',
            'google_fonts' => 'family=Plus+Jakarta+Sans:wght@600;700;800&family=Inter:wght@400;500;600',
            'primary_color' => '#0284C7',
            'accent_color' => '#0D9488',
            'bg_color' => '#F0F9FF',
            'card_bg' => '#FFFFFF',
            'header_dark' => false,
            'hero_style' => 'fresh_hygiene',
            'badge_bg' => '#0284C715',
            'badge_text' => '#0284C7',
            'border_radius' => '16px',
            'description' => 'Tampilan segar higienis, estimasi tarif kiloan/satuan, pelacakan nota pengerjaan, dan garansi layanan bersih.',
        ],

        // 9. Portfolio & Creative
        'portfolio_creative' => [
            'id' => 'portfolio_creative',
            'bundle_dir' => 'portfolio_creative',
            'name' => 'Portfolio & Creative',
            'industry' => 'Agensi Kreatif, Fotografer, Desainer & Studio',
            'font_heading' => 'Syne',
            'font_body' => 'Inter',
            'google_fonts' => 'family=Syne:wght@700;800&family=Inter:wght@400;500;600',
            'primary_color' => '#0A0A0A',
            'accent_color' => '#7C3AED',
            'bg_color' => '#F8FAFC',
            'card_bg' => '#FFFFFF',
            'header_dark' => false,
            'hero_style' => 'editorial_mono',
            'badge_bg' => '#0A0A0A15',
            'badge_text' => '#0A0A0A',
            'border_radius' => '10px',
            'description' => 'Layout editorial asimetris, galeri proyek beresolusi tinggi, daftar studi kasus, dan formulir konsultasi brief kreatif.',
        ],

        // 10. Professional Services
        'professional_services' => [
            'id' => 'professional_services',
            'bundle_dir' => 'professional_services',
            'name' => 'Professional Services',
            'industry' => 'Konsultan Bisnis, Akuntansi, Pajak & Legal',
            'font_heading' => 'Merriweather',
            'font_body' => 'Plus Jakarta Sans',
            'google_fonts' => 'family=Merriweather:wght@400;700;900&family=Plus+Jakarta+Sans:wght@400;500;600',
            'primary_color' => '#1E293B',
            'accent_color' => '#B45309',
            'bg_color' => '#F8FAFC',
            'card_bg' => '#FFFFFF',
            'header_dark' => false,
            'hero_style' => 'advisory_firm',
            'badge_bg' => '#1E293B15',
            'badge_text' => '#1E293B',
            'border_radius' => '10px',
            'description' => 'Kepercayaan hukum & finansial, kerahasiaan NDA terjamin, profil partner tersumpah, dan jadwal konsultasi audit.',
        ],

        // 11. Manufacturing & Factory
        'manufacturing_factory' => [
            'id' => 'manufacturing_factory',
            'bundle_dir' => 'manufacturing_factory',
            'name' => 'Manufacturing & Factory',
            'industry' => 'Pabrik, Konveksi, Produsen, OEM & Maklon',
            'font_heading' => 'Archivo Black',
            'font_body' => 'Inter',
            'google_fonts' => 'family=Archivo+Black&family=Inter:wght@400;500;600;700',
            'primary_color' => '#334155',
            'accent_color' => '#EA580C',
            'bg_color' => '#F1F5F9',
            'card_bg' => '#FFFFFF',
            'header_dark' => false,
            'hero_style' => 'heavy_industry',
            'badge_bg' => '#33415515',
            'badge_text' => '#334155',
            'border_radius' => '8px',
            'description' => 'Kapasitas produksi skala besar (pcs/bulan), alur maklon OEM, sertifikasi mutu SNI/ISO, dan minimal order quantity (MOQ).',
        ],

        // 12. Wholesale & Distributor
        'wholesale_distributor' => [
            'id' => 'wholesale_distributor',
            'bundle_dir' => 'wholesale_distributor',
            'name' => 'Wholesale & Distributor',
            'industry' => 'Distributor, Grosir, Supplier, Importir & Eksportir',
            'font_heading' => 'Outfit',
            'font_body' => 'Inter',
            'google_fonts' => 'family=Outfit:wght@600;700;800&family=Inter:wght@400;500;600',
            'primary_color' => '#16A34A',
            'accent_color' => '#1D4ED8',
            'bg_color' => '#F8FAFC',
            'card_bg' => '#FFFFFF',
            'header_dark' => false,
            'hero_style' => 'b2b_wholesale',
            'badge_bg' => '#16A34A15',
            'badge_text' => '#16A34A',
            'border_radius' => '14px',
            'description' => 'Tier harga grosir bertingkat, syarat minimal order karton/tonase, kesiapan armada logistik, dan unduh pricelist grosir.',
        ],

        // 13. Construction & Property
        'construction_property' => [
            'id' => 'construction_property',
            'bundle_dir' => 'construction_property',
            'name' => 'Construction & Property',
            'industry' => 'Kontraktor, Renovasi, Arsitektur, Developer & Properti',
            'font_heading' => 'Rajdhani',
            'font_body' => 'Plus Jakarta Sans',
            'google_fonts' => 'family=Rajdhani:wght@600;700&family=Plus+Jakarta+Sans:wght@400;500;600',
            'primary_color' => '#B45309',
            'accent_color' => '#374151',
            'bg_color' => '#FAF5EE',
            'card_bg' => '#FFFFFF',
            'header_dark' => false,
            'hero_style' => 'blueprint_property',
            'badge_bg' => '#B4530915',
            'badge_text' => '#B45309',
            'border_radius' => '12px',
            'description' => 'Arsitektur blueprint presisi, pameran proyek terbangun, spesifikasi bahan standar teknik, dan estimasi biaya (RAB).',
        ],

        // 14. Booking & Appointment
        'booking_appointment' => [
            'id' => 'booking_appointment',
            'bundle_dir' => 'booking_appointment',
            'name' => 'Booking & Appointment',
            'industry' => 'Reservasi Salon, Klinik, Bengkel, Studio & Konsultasi',
            'font_heading' => 'Plus Jakarta Sans',
            'font_body' => 'Inter',
            'google_fonts' => 'family=Plus+Jakarta+Sans:wght@600;700;800&family=Inter:wght@400;500;600',
            'primary_color' => '#7C3AED',
            'accent_color' => '#F43F5E',
            'bg_color' => '#F5F3FF',
            'card_bg' => '#FFFFFF',
            'header_dark' => false,
            'hero_style' => 'live_booking',
            'badge_bg' => '#7C3AED15',
            'badge_text' => '#7C3AED',
            'border_radius' => '18px',
            'description' => 'Alur reservasi slot jam interaktif, pemilihan staf/ahli, durasi perlakuan, dan konfirmasi otomatis jadwal temu.',
        ],

        // 15. Event & Wedding
        'event_wedding' => [
            'id' => 'event_wedding',
            'bundle_dir' => 'event_wedding',
            'name' => 'Event & Wedding',
            'industry' => 'Event Organizer, Wedding Organizer, Dekorasi & Rental',
            'font_heading' => 'Cormorant Garamond',
            'font_body' => 'Plus Jakarta Sans',
            'google_fonts' => 'family=Cormorant+Garamond:ital,wght@0,600;0,700;1,600&family=Plus+Jakarta+Sans:wght@400;500;600',
            'primary_color' => '#BE185D',
            'accent_color' => '#D4AF37',
            'bg_color' => '#FFFDF7',
            'card_bg' => '#FFFFFF',
            'header_dark' => false,
            'hero_style' => 'romantic_celebration',
            'badge_bg' => '#BE185D15',
            'badge_text' => '#BE185D',
            'border_radius' => '20px',
            'description' => 'Sentuhan glamor romantis, paket perayaan all-in, galeri dekorasi venue megah, dan panduan konsultasi hari bahagia.',
        ],

        // 16. Hospitality & Accommodation
        'hospitality_accommodation' => [
            'id' => 'hospitality_accommodation',
            'bundle_dir' => 'hospitality_accommodation',
            'name' => 'Hospitality & Accommodation',
            'industry' => 'Hotel, Villa, Homestay, Guest House & Penginapan',
            'font_heading' => 'Bodoni Moda',
            'font_body' => 'Inter',
            'google_fonts' => 'family=Bodoni+Moda:ital,opsz,wght@0,6..96,600;0,6..96,700;1,6..96,600&family=Inter:wght@400;500;600',
            'primary_color' => '#0284C7',
            'accent_color' => '#D97706',
            'bg_color' => '#F0FDF4',
            'card_bg' => '#FFFFFF',
            'header_dark' => false,
            'hero_style' => 'resort_luxury',
            'badge_bg' => '#0284C715',
            'badge_text' => '#0284C7',
            'border_radius' => '16px',
            'description' => 'Eksplorasi panorama liburan, fasilitas kamar lengkap, lokasi strategis wisata, dan ketersediaan tanggal check-in.',
        ],

        // 17. Education & Courses
        'education_courses' => [
            'id' => 'education_courses',
            'bundle_dir' => 'education_courses',
            'name' => 'Education & Courses',
            'industry' => 'Kursus, Bimbingan Belajar, Training & Lembaga Pendidikan',
            'font_heading' => 'Plus Jakarta Sans',
            'font_body' => 'Inter',
            'google_fonts' => 'family=Plus+Jakarta+Sans:wght@600;700;800&family=Inter:wght@400;500;600',
            'primary_color' => '#4338CA',
            'accent_color' => '#EA580C',
            'bg_color' => '#EEF2FF',
            'card_bg' => '#FFFFFF',
            'header_dark' => false,
            'hero_style' => 'academy_scholar',
            'badge_bg' => '#4338CA15',
            'badge_text' => '#4338CA',
            'border_radius' => '16px',
            'description' => 'Silabus kurikulum terstruktur, profil mentor praktisi berpengalaman, sertifikasi resmi kelulusan, dan jadwal batch kelas.',
        ],

        // 18. Health & Beauty
        'health_beauty' => [
            'id' => 'health_beauty',
            'bundle_dir' => 'health_beauty',
            'name' => 'Health & Beauty',
            'industry' => 'Klinik Kecantikan, Wellness, Optik & Layanan Kesehatan',
            'font_heading' => 'Outfit',
            'font_body' => 'Plus Jakarta Sans',
            'google_fonts' => 'family=Outfit:wght@600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600',
            'primary_color' => '#0D9488',
            'accent_color' => '#EC4899',
            'bg_color' => '#F0FDFA',
            'card_bg' => '#FFFFFF',
            'header_dark' => false,
            'hero_style' => 'clinical_pure',
            'badge_bg' => '#0D948815',
            'badge_text' => '#0D9488',
            'border_radius' => '18px',
            'description' => 'Standar higienis steril faskes, dokter & terapis terlisensi, jaminan produk izin BPOM, serta konsultasi kesehatan terpercaya.',
        ],

        // 19. Agriculture & Farm
        'agriculture_farm' => [
            'id' => 'agriculture_farm',
            'bundle_dir' => 'agriculture_farm',
            'name' => 'Agriculture & Farm',
            'industry' => 'Pertanian, Peternakan, Perkebunan, Perikanan & Hasil Panen',
            'font_heading' => 'DM Serif Display',
            'font_body' => 'Plus Jakarta Sans',
            'google_fonts' => 'family=DM+Serif+Display:ital@0;1&family=Plus+Jakarta+Sans:wght@400;500;600;700',
            'primary_color' => '#15803D',
            'accent_color' => '#B45309',
            'bg_color' => '#F0FDF4',
            'card_bg' => '#FFFFFF',
            'header_dark' => false,
            'hero_style' => 'harvest_organic',
            'badge_bg' => '#15803D15',
            'badge_text' => '#15803D',
            'border_radius' => '16px',
            'description' => 'Kesegaran panen langsung dari kebun, produk organik bebas bahan berbahaya, rantai dingin pasokan, dan transparansi asal petani.',
        ],

        // 20. Logistics & Transportation
        'logistics_transportation' => [
            'id' => 'logistics_transportation',
            'bundle_dir' => 'logistics_transportation',
            'name' => 'Logistics & Transportation',
            'industry' => 'Ekspedisi, Trucking, Cargo, Freight Forwarding & Logistik',
            'font_heading' => 'Chakra Petch',
            'font_body' => 'Inter',
            'google_fonts' => 'family=Chakra+Petch:wght@600;700&family=Inter:wght@400;500;600',
            'primary_color' => '#EAB308',
            'accent_color' => '#DC2626',
            'bg_color' => '#F8FAFC',
            'card_bg' => '#FFFFFF',
            'header_dark' => false,
            'hero_style' => 'fleet_cargo',
            'badge_bg' => '#EAB30820',
            'badge_text' => '#B45309',
            'border_radius' => '10px',
            'description' => 'Formulir cek tarif rute kirim, pelacak resi pengiriman real-time, kesiapan tipe armada ekspedisi, dan garansi asuransi kargo.',
        ],

        // 21. Personal Brand
        'personal_brand' => [
            'id' => 'personal_brand',
            'bundle_dir' => 'personal_brand',
            'name' => 'Personal Brand',
            'industry' => 'Freelancer, Kreator, Trainer & Profesional Independen',
            'font_heading' => 'Syne',
            'font_body' => 'Inter',
            'google_fonts' => 'family=Syne:wght@700;800&family=Inter:wght@400;500;600',
            'primary_color' => '#18181B',
            'accent_color' => '#0891B2',
            'bg_color' => '#FAFAFA',
            'card_bg' => '#FFFFFF',
            'header_dark' => false,
            'hero_style' => 'creator_bio',
            'badge_bg' => '#18181B15',
            'badge_text' => '#18181B',
            'border_radius' => '16px',
            'description' => 'Bio profil kepemimpinan pemikiran, portofolio karya & buku, undangan sesi pembicara/trainer, dan link media sosial terintegrasi.',
        ],
    ];

    /**
     * Map template_code to matching theme ID.
     *
     * @var array<string, string>
     */
    public const TEMPLATE_CODE_MAP = [
        // 1. Business Corporate
        'business_corporate' => 'business_corporate',
        'corporate' => 'business_corporate',
        'b2b' => 'business_corporate',
        'sovereign_enterprise' => 'business_corporate',
        'service_agency' => 'business_corporate',
        'consultant' => 'business_corporate',

        // 2. Modern UMKM
        'modern_umkm' => 'modern_umkm',
        'umkm' => 'modern_umkm',
        'universal_storefront' => 'modern_umkm',
        'universal_clean' => 'modern_umkm',
        'universal' => 'modern_umkm',
        'retail_reseller' => 'modern_umkm',
        'default' => 'modern_umkm',

        // 3. Online Store
        'online_store' => 'online_store',
        'store' => 'online_store',
        'retail_fashion' => 'online_store',
        'vogue_minimalist' => 'online_store',
        'retail_electronics' => 'online_store',
        'nexus_cyber' => 'online_store',
        'fashion' => 'online_store',
        'electronics' => 'online_store',

        // 4. Product Showcase
        'product_showcase' => 'product_showcase',
        'showcase' => 'product_showcase',
        'hero_product' => 'product_showcase',
        'skincare' => 'product_showcase',
        'beauty_product' => 'product_showcase',

        // 5. Food & Restaurant
        'food_restaurant' => 'food_restaurant',
        'resto' => 'food_restaurant',
        'restaurant' => 'food_restaurant',
        'nusantara_feast' => 'food_restaurant',
        'fnb_resto' => 'food_restaurant',
        'neon_crunch' => 'food_restaurant',
        'fnb_fastfood' => 'food_restaurant',
        'kuliner' => 'food_restaurant',

        // 6. Coffee & Beverage
        'coffee_beverage' => 'coffee_beverage',
        'coffee' => 'coffee_beverage',
        'cafe' => 'coffee_beverage',
        'artisan_brew' => 'coffee_beverage',
        'fnb_cafe' => 'coffee_beverage',
        'boba' => 'coffee_beverage',

        // 7. Bakery & Cake
        'bakery_cake' => 'bakery_cake',
        'bakery' => 'bakery_cake',
        'cake' => 'bakery_cake',
        'velvet_patisserie' => 'bakery_cake',
        'fnb_bakery' => 'bakery_cake',
        'pastry' => 'bakery_cake',

        // 8. Service Business
        'service_business' => 'service_business',
        'service' => 'service_business',
        'service_laundry' => 'service_business',
        'aqua_bubble' => 'service_business',
        'laundry' => 'service_business',
        'service_carwash' => 'service_business',
        'hydro_shield' => 'service_business',
        'cleaning' => 'service_business',
        'service_petshop' => 'service_business',
        'playful_paws' => 'service_business',

        // 9. Portfolio & Creative
        'portfolio_creative' => 'portfolio_creative',
        'creative' => 'portfolio_creative',
        'portfolio' => 'portfolio_creative',
        'pixel_print' => 'portfolio_creative',
        'service_printing' => 'portfolio_creative',
        'design' => 'portfolio_creative',
        'studio' => 'portfolio_creative',

        // 10. Professional Services
        'professional_services' => 'professional_services',
        'professional' => 'professional_services',
        'accounting' => 'professional_services',
        'tax' => 'professional_services',
        'legal' => 'professional_services',
        'law' => 'professional_services',

        // 11. Manufacturing & Factory
        'manufacturing_factory' => 'manufacturing_factory',
        'manufacturing' => 'manufacturing_factory',
        'factory' => 'manufacturing_factory',
        'pabrik' => 'manufacturing_factory',
        'oem' => 'manufacturing_factory',
        'maklon' => 'manufacturing_factory',
        'konveksi' => 'manufacturing_factory',

        // 12. Wholesale & Distributor
        'wholesale_distributor' => 'wholesale_distributor',
        'wholesale' => 'wholesale_distributor',
        'distributor' => 'wholesale_distributor',
        'grosir' => 'wholesale_distributor',
        'fresh_mart' => 'wholesale_distributor',
        'retail_grocery' => 'wholesale_distributor',
        'sembako' => 'wholesale_distributor',

        // 13. Construction & Property
        'construction_property' => 'construction_property',
        'construction' => 'construction_property',
        'property' => 'construction_property',
        'kontraktor' => 'construction_property',
        'ironclad_builder' => 'construction_property',
        'retail_building' => 'construction_property',
        'architecture' => 'construction_property',

        // 14. Booking & Appointment
        'booking_appointment' => 'booking_appointment',
        'booking' => 'booking_appointment',
        'appointment' => 'booking_appointment',
        'service_workshop' => 'booking_appointment',
        'apex_velocity' => 'booking_appointment',
        'bengkel' => 'booking_appointment',
        'salon' => 'booking_appointment',
        'barbershop' => 'booking_appointment',

        // 15. Event & Wedding
        'event_wedding' => 'event_wedding',
        'event' => 'event_wedding',
        'wedding' => 'event_wedding',
        'epicurean_box' => 'event_wedding',
        'fnb_catering' => 'event_wedding',
        'flora_romance' => 'event_wedding',
        'retail_florist' => 'event_wedding',
        'catering' => 'event_wedding',

        // 16. Hospitality & Accommodation
        'hospitality_accommodation' => 'hospitality_accommodation',
        'hospitality' => 'hospitality_accommodation',
        'hotel' => 'hospitality_accommodation',
        'villa' => 'hospitality_accommodation',
        'homestay' => 'hospitality_accommodation',
        'resort' => 'hospitality_accommodation',

        // 17. Education & Courses
        'education_courses' => 'education_courses',
        'education' => 'education_courses',
        'course' => 'education_courses',
        'training' => 'education_courses',
        'bibliotheca' => 'education_courses',
        'retail_bookstore' => 'education_courses',
        'bimbel' => 'education_courses',

        // 18. Health & Beauty
        'health_beauty' => 'health_beauty',
        'health' => 'health_beauty',
        'beauty' => 'health_beauty',
        'clinical_pure' => 'health_beauty',
        'retail_pharmacy' => 'health_beauty',
        'service_beauty' => 'health_beauty',
        'aura_glamour' => 'health_beauty',
        'klinik' => 'health_beauty',
        'apotek' => 'health_beauty',

        // 19. Agriculture & Farm
        'agriculture_farm' => 'agriculture_farm',
        'agriculture' => 'agriculture_farm',
        'farm' => 'agriculture_farm',
        'pertanian' => 'agriculture_farm',
        'peternakan' => 'agriculture_farm',
        'perikanan' => 'agriculture_farm',

        // 20. Logistics & Transportation
        'logistics_transportation' => 'logistics_transportation',
        'logistics' => 'logistics_transportation',
        'transportation' => 'logistics_transportation',
        'ekspedisi' => 'logistics_transportation',
        'cargo' => 'logistics_transportation',
        'trucking' => 'logistics_transportation',

        // 21. Personal Brand
        'personal_brand' => 'personal_brand',
        'personal' => 'personal_brand',
        'creator' => 'personal_brand',
        'trainer' => 'personal_brand',
        'freelance' => 'personal_brand',
        'titan_kinetic' => 'personal_brand',
        'service_fitness' => 'personal_brand',
    ];

    /**
     * Map industry categories to theme ID.
     *
     * @var array<string, string>
     */
    public const CATEGORY_MAP = [
        'corporate' => 'business_corporate',
        'umkm' => 'modern_umkm',
        'general' => 'modern_umkm',
        'retail' => 'online_store',
        'ecommerce' => 'online_store',
        'fashion' => 'online_store',
        'electronics' => 'online_store',
        'showcase' => 'product_showcase',
        'fnb' => 'food_restaurant',
        'restaurant' => 'food_restaurant',
        'cafe' => 'coffee_beverage',
        'coffee' => 'coffee_beverage',
        'bakery' => 'bakery_cake',
        'service' => 'service_business',
        'laundry' => 'service_business',
        'creative' => 'portfolio_creative',
        'design' => 'portfolio_creative',
        'consulting' => 'professional_services',
        'accounting' => 'professional_services',
        'legal' => 'professional_services',
        'manufacturing' => 'manufacturing_factory',
        'factory' => 'manufacturing_factory',
        'wholesale' => 'wholesale_distributor',
        'distributor' => 'wholesale_distributor',
        'grocery' => 'wholesale_distributor',
        'construction' => 'construction_property',
        'property' => 'construction_property',
        'booking' => 'booking_appointment',
        'workshop' => 'booking_appointment',
        'event' => 'event_wedding',
        'wedding' => 'event_wedding',
        'hospitality' => 'hospitality_accommodation',
        'hotel' => 'hospitality_accommodation',
        'education' => 'education_courses',
        'course' => 'education_courses',
        'health' => 'health_beauty',
        'beauty' => 'health_beauty',
        'pharmacy' => 'health_beauty',
        'agriculture' => 'agriculture_farm',
        'farm' => 'agriculture_farm',
        'logistics' => 'logistics_transportation',
        'transport' => 'logistics_transportation',
        'personal' => 'personal_brand',
        'fitness' => 'personal_brand',
    ];

    /**
     * Direct alias map between historical/alternate codes and 21 official themes.
     *
     * @var array<string, string>
     */
    private const ALIASES = [
        'artisan_brew' => 'coffee_beverage',
        'fnb_cafe' => 'coffee_beverage',
        'aqua_bubble' => 'service_business',
        'service_laundry' => 'service_business',
        'velvet_patisserie' => 'bakery_cake',
        'fnb_bakery' => 'bakery_cake',
        'nusantara_feast' => 'food_restaurant',
        'fnb_resto' => 'food_restaurant',
        'flora_romance' => 'event_wedding',
        'retail_florist' => 'event_wedding',
        'neon_crunch' => 'food_restaurant',
        'fnb_fastfood' => 'food_restaurant',
        'epicurean_box' => 'event_wedding',
        'fnb_catering' => 'event_wedding',
        'vogue_minimalist' => 'online_store',
        'retail_fashion' => 'online_store',
        'nexus_cyber' => 'online_store',
        'retail_electronics' => 'online_store',
        'fresh_mart' => 'wholesale_distributor',
        'retail_grocery' => 'wholesale_distributor',
        'apex_velocity' => 'booking_appointment',
        'service_workshop' => 'booking_appointment',
        'clinical_pure' => 'health_beauty',
        'retail_pharmacy' => 'health_beauty',
        'aura_glamour' => 'health_beauty',
        'service_beauty' => 'health_beauty',
        'ironclad_builder' => 'construction_property',
        'retail_building' => 'construction_property',
        'pixel_print' => 'portfolio_creative',
        'service_printing' => 'portfolio_creative',
        'bibliotheca' => 'education_courses',
        'retail_bookstore' => 'education_courses',
        'playful_paws' => 'service_business',
        'service_petshop' => 'service_business',
        'hydro_shield' => 'service_business',
        'service_carwash' => 'service_business',
        'titan_kinetic' => 'personal_brand',
        'service_fitness' => 'personal_brand',
        'sovereign_enterprise' => 'business_corporate',
        'service_agency' => 'business_corporate',
        'universal_storefront' => 'modern_umkm',
        'universal_clean' => 'modern_umkm',
    ];

    /**
     * Get all available theme presets (21 distinct themes).
     *
     * @return array<string, array<string, mixed>>
     */
    public function getAllThemes(): array
    {
        return self::THEMES;
    }

    /**
     * Get configuration for a specific theme key or alias.
     *
     * @param string $presetKey
     * @return array<string, mixed>
     */
    public function getTheme(string $presetKey): array
    {
        // 1. Direct match on key
        if (isset(self::THEMES[$presetKey])) {
            return self::THEMES[$presetKey];
        }

        // 2. Direct match on alias / bundle_dir
        if (isset(self::ALIASES[$presetKey]) && isset(self::THEMES[self::ALIASES[$presetKey]])) {
            return self::THEMES[self::ALIASES[$presetKey]];
        }

        // 3. Scan bundle_dir inside THEMES
        foreach (self::THEMES as $theme) {
            if (($theme['bundle_dir'] ?? null) === $presetKey) {
                return $theme;
            }
        }

        // 4. Default fallback: Modern UMKM
        return self::THEMES['modern_umkm'];
    }

    /**
     * Detect matching theme key automatically from Business model.
     * Fallback to 'modern_umkm' if no match found.
     */
    public function detectThemeKeyForBusiness(?Business $business): string
    {
        if (! $business) {
            return 'modern_umkm';
        }

        $templateCode = strtolower((string) ($business->template_code ?? ''));
        $industryCategory = strtolower((string) ($business->industry_category ?? ''));

        // 1. Direct match on template_code
        if ($templateCode && isset(self::TEMPLATE_CODE_MAP[$templateCode])) {
            return self::TEMPLATE_CODE_MAP[$templateCode];
        }

        // 2. Partial prefix match on template_code
        if ($templateCode) {
            foreach (self::TEMPLATE_CODE_MAP as $code => $themeKey) {
                if (str_starts_with($templateCode, $code) || str_starts_with($code, $templateCode)) {
                    return $themeKey;
                }
            }
        }

        // 3. Industry category match
        if ($industryCategory && isset(self::CATEGORY_MAP[$industryCategory])) {
            return self::CATEGORY_MAP[$industryCategory];
        }

        // 4. Modern UMKM fallback
        return 'modern_umkm';
    }

    /**
     * Resolve effective theme configuration for a landing page.
     *
     * @param BusinessLandingPage $landingPage
     * @return array<string, mixed>
     */
    public function resolveTheme(BusinessLandingPage $landingPage): array
    {
        $presetKey = $landingPage->getThemePreset();
        $theme = $this->getTheme($presetKey);

        // Dynamic Accent Color configuration (defaults to #8B5A2B or merchant's chosen color)
        $accentColor = ! empty($landingPage->theme_color) ? $landingPage->theme_color : ($theme['primary_color'] ?? '#8B5A2B');
        $theme['primary_color'] = $accentColor;
        $theme['badge_text'] = $accentColor;
        $theme['badge_bg'] = $accentColor . '18';

        // Font override if explicitly specified
        if (! empty($landingPage->font_family) && $landingPage->font_family !== 'Plus Jakarta Sans') {
            $theme['font_heading'] = $landingPage->font_family;
            $theme['font_body'] = $landingPage->font_family;
        }

        // Elegant Dark Mode override
        if (! empty($landingPage->dark_mode)) {
            $theme['header_dark'] = true;
            $theme['bg_color'] = '#0B0F19';
            $theme['card_bg'] = '#181E2A';
        }

        return $theme;
    }
}
