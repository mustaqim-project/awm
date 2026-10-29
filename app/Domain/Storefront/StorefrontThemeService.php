<?php

declare(strict_types=1);

namespace App\Domain\Storefront;

use App\Models\Business;
use App\Models\BusinessLandingPage;

final class StorefrontThemeService
{
    /**
     * Complete catalogue of 42 Distinct Storefront Landing Page Themes.
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

        // 22. FnB Bakery (Velvet Patisserie)
        'fnb_bakery' => [
            'id' => 'fnb_bakery',
            'bundle_dir' => 'fnb_bakery',
            'name' => 'Velvet Patisserie (Bakery)',
            'industry' => 'Bakery, Cake Shop, Pastry & Dessert',
            'font_heading' => 'Playfair Display',
            'font_body' => 'Plus Jakarta Sans',
            'google_fonts' => 'family=Playfair+Display:wght@600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600',
            'primary_color' => '#EA580C',
            'accent_color' => '#FBBF24',
            'bg_color' => '#FFFBF7',
            'card_bg' => '#FFFFFF',
            'header_dark' => false,
            'hero_style' => 'patisserie_luxury',
            'badge_bg' => '#EA580C15',
            'badge_text' => '#EA580C',
            'border_radius' => '16px',
            'description' => 'Pastel rose & champagne gold elegance, artisan bread hangat, kue ulang tahun custom, dan etalase dessert premium.',
        ],

        // 23. FnB Cafe (Artisan Brew)
        'fnb_cafe' => [
            'id' => 'fnb_cafe',
            'bundle_dir' => 'fnb_cafe',
            'name' => 'Artisan Brew (Cafe & Coffee)',
            'industry' => 'Coffee Shop, Cafe, Boba & Kedai Kopi',
            'font_heading' => 'Playfair Display',
            'font_body' => 'Plus Jakarta Sans',
            'google_fonts' => 'family=Playfair+Display:wght@600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600',
            'primary_color' => '#8B5A2B',
            'accent_color' => '#C88A58',
            'bg_color' => '#FAF7F2',
            'card_bg' => '#FFFFFF',
            'header_dark' => false,
            'hero_style' => 'artisan_cozy',
            'badge_bg' => '#8B5A2B15',
            'badge_text' => '#8B5A2B',
            'border_radius' => '16px',
            'description' => 'Specialty coffee roastery, aroma biji kopi nusantara, manual brew bar, QR meja dine-in, dan live roasting.',
        ],

        // 24. FnB Catering (Epicurean Box)
        'fnb_catering' => [
            'id' => 'fnb_catering',
            'bundle_dir' => 'fnb_catering',
            'name' => 'Epicurean Box (Catering & Bento)',
            'industry' => 'Catering Harian, Prasmanan & Bento Box',
            'font_heading' => 'Outfit',
            'font_body' => 'Plus Jakarta Sans',
            'google_fonts' => 'family=Outfit:wght@600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600',
            'primary_color' => '#059669',
            'accent_color' => '#F59E0B',
            'bg_color' => '#F0FDF4',
            'card_bg' => '#FFFFFF',
            'header_dark' => false,
            'hero_style' => 'healthy_bento',
            'badge_bg' => '#05966915',
            'badge_text' => '#059669',
            'border_radius' => '16px',
            'description' => 'Healthy emerald green, meal prep diet mingguan, bento meeting kantor, paket prasmanan pesta, dan higienitas bersertifikat.',
        ],

        // 25. FnB Fastfood (Neon Crunch)
        'fnb_fastfood' => [
            'id' => 'fnb_fastfood',
            'bundle_dir' => 'fnb_fastfood',
            'name' => 'Neon Crunch (Fast Food & Snack)',
            'industry' => 'Fast Food, Burger, Ayam Crispy & Snack',
            'font_heading' => 'Outfit',
            'font_body' => 'Inter',
            'google_fonts' => 'family=Outfit:wght@700;800;900&family=Inter:wght@400;500;600',
            'primary_color' => '#DC2626',
            'accent_color' => '#FBBF24',
            'bg_color' => '#FEF2F2',
            'card_bg' => '#FFFFFF',
            'header_dark' => false,
            'hero_style' => 'energy_fastfood',
            'badge_bg' => '#DC262615',
            'badge_text' => '#DC2626',
            'border_radius' => '18px',
            'description' => 'Energi tinggi neon red-yellow, paket combo hemat, ayam krispi pedas, burger keju lumer, dan pemesanan takeaway ekspres.',
        ],

        // 26. FnB Resto (Nusantara Feast)
        'fnb_resto' => [
            'id' => 'fnb_resto',
            'bundle_dir' => 'fnb_resto',
            'name' => 'Nusantara Feast (Resto & Kuliner)',
            'industry' => 'Restoran Nusantara, Seafood & Kuliner Lokal',
            'font_heading' => 'DM Serif Display',
            'font_body' => 'Plus Jakarta Sans',
            'google_fonts' => 'family=DM+Serif+Display&family=Plus+Jakarta+Sans:wght@400;500;600',
            'primary_color' => '#C2410C',
            'accent_color' => '#B45309',
            'bg_color' => '#FFF7ED',
            'card_bg' => '#FFFFFF',
            'header_dark' => false,
            'hero_style' => 'terracotta_spice',
            'badge_bg' => '#C2410C15',
            'badge_text' => '#C2410C',
            'border_radius' => '16px',
            'description' => 'Kekayaan rempah nusantara, menu signature keluarga, reservasi meja makan malam, dan suasana tradisional berkelas.',
        ],

        // 27. Retail Bookstore (Bibliotheca)
        'retail_bookstore' => [
            'id' => 'retail_bookstore',
            'bundle_dir' => 'retail_bookstore',
            'name' => 'Bibliotheca (Toko Buku & Stationery)',
            'industry' => 'Buku, Alat Tulis, Musik & Perlengkapan Sekolah',
            'font_heading' => 'Merriweather',
            'font_body' => 'Inter',
            'google_fonts' => 'family=Merriweather:wght@400;700;900&family=Inter:wght@400;500;600',
            'primary_color' => '#78350F',
            'accent_color' => '#D97706',
            'bg_color' => '#FFFBEB',
            'card_bg' => '#FFFFFF',
            'header_dark' => false,
            'hero_style' => 'parchment_library',
            'badge_bg' => '#78350F15',
            'badge_text' => '#78350F',
            'border_radius' => '12px',
            'description' => 'Parchment paper aesthetic, kurasi buku original penerbit resmi, alat seni lukis & tulis, serta santuari baca yang tenang.',
        ],

        // 28. Retail Building (Ironclad Builder)
        'retail_building' => [
            'id' => 'retail_building',
            'bundle_dir' => 'retail_building',
            'name' => 'Ironclad Builder (Bahan Bangunan)',
            'industry' => 'Toko Bangunan, Material, Besi & Perkakas',
            'font_heading' => 'Barlow Semi Condensed',
            'font_body' => 'Inter',
            'google_fonts' => 'family=Barlow+Semi+Condensed:wght@600;700;800&family=Inter:wght@400;500;600',
            'primary_color' => '#D97706',
            'accent_color' => '#334155',
            'bg_color' => '#F8FAFC',
            'card_bg' => '#FFFFFF',
            'header_dark' => false,
            'hero_style' => 'heavy_contractor',
            'badge_bg' => '#D9770615',
            'badge_text' => '#D97706',
            'border_radius' => '10px',
            'description' => 'Heavy industrial slate, semen SNI, baja ringan, keramik, cat dinding, kalkulator proyek, dan armada kirim truk material.',
        ],

        // 29. Retail Electronics (Nexus Cyber)
        'retail_electronics' => [
            'id' => 'retail_electronics',
            'bundle_dir' => 'retail_electronics',
            'name' => 'Nexus Cyber (Elektronik & Gadget)',
            'industry' => 'Gadget, Smartphone, Laptop & Komputer',
            'font_heading' => 'Space Grotesk',
            'font_body' => 'Inter',
            'google_fonts' => 'family=Space+Grotesk:wght@600;700&family=Inter:wght@400;500;600',
            'primary_color' => '#0284C7',
            'accent_color' => '#06B6D4',
            'bg_color' => '#0B0F19',
            'card_bg' => '#181E2A',
            'header_dark' => true,
            'hero_style' => 'cyber_tech',
            'badge_bg' => '#0284C720',
            'badge_text' => '#38BDF8',
            'border_radius' => '16px',
            'description' => 'Nuansa dark cyber futuristik, spesifikasi teknis mendalam, perbandingan gadget, garansi resmi distributor, dan cicilan instan.',
        ],

        // 30. Retail Fashion (Vogue Minimalist)
        'retail_fashion' => [
            'id' => 'retail_fashion',
            'bundle_dir' => 'retail_fashion',
            'name' => 'Vogue Minimalist (Fashion & Butik)',
            'industry' => 'Butik Fashion, Pakaian Pria/Wanita & Aksesoris',
            'font_heading' => 'Cormorant Garamond',
            'font_body' => 'Plus Jakarta Sans',
            'google_fonts' => 'family=Cormorant+Garamond:wght@600;700&family=Plus+Jakarta+Sans:wght@400;500;600',
            'primary_color' => '#18181B',
            'accent_color' => '#A1A1AA',
            'bg_color' => '#FAFAFA',
            'card_bg' => '#FFFFFF',
            'header_dark' => false,
            'hero_style' => 'editorial_lookbook',
            'badge_bg' => '#18181B15',
            'badge_text' => '#18181B',
            'border_radius' => '14px',
            'description' => 'Editorial lookbook monokrom, busana muslim & modern ready-to-wear, size chart presisi, dan koleksi busana musiman.',
        ],

        // 31. Retail Florist (Flora Romance)
        'retail_florist' => [
            'id' => 'retail_florist',
            'bundle_dir' => 'retail_florist',
            'name' => 'Flora Romance (Florist & Karangan Bunga)',
            'industry' => 'Florist, Bunga Segar, Buket Wisuda & Papan Bunga',
            'font_heading' => 'Bodoni Moda',
            'font_body' => 'Plus Jakarta Sans',
            'google_fonts' => 'family=Bodoni+Moda:ital,opsz,wght@0,6..96,600;0,6..96,700;1,6..96,500&family=Plus+Jakarta+Sans:wght@400;500;600',
            'primary_color' => '#E11D48',
            'accent_color' => '#059669',
            'bg_color' => '#FFF1F2',
            'card_bg' => '#FFFFFF',
            'header_dark' => false,
            'hero_style' => 'botanical_garden',
            'badge_bg' => '#E11D4815',
            'badge_text' => '#E11D48',
            'border_radius' => '16px',
            'description' => 'Keindahan botani segar, buket bunga wisuda, standing flower ucapan selamat, kartu ucapan custom, dan kirim same-day.',
        ],

        // 32. Retail Grocery (Fresh Mart)
        'retail_grocery' => [
            'id' => 'retail_grocery',
            'bundle_dir' => 'retail_grocery',
            'name' => 'Fresh Mart (Minimarket & Sembako)',
            'industry' => 'Minimarket, Sayur Buah, Sembako & Supermarket',
            'font_heading' => 'Plus Jakarta Sans',
            'font_body' => 'Inter',
            'google_fonts' => 'family=Plus+Jakarta+Sans:wght@600;700;800&family=Inter:wght@400;500;600',
            'primary_color' => '#16A34A',
            'accent_color' => '#EA580C',
            'bg_color' => '#F0FDF4',
            'card_bg' => '#FFFFFF',
            'header_dark' => false,
            'hero_style' => 'fresh_supermarket',
            'badge_bg' => '#16A34A15',
            'badge_text' => '#16A34A',
            'border_radius' => '14px',
            'description' => 'Pasar modern segar, sembako harga agen, sayuran hidroponik, kebutuhan dapur harian lengkap, dan kurir antar ke rumah.',
        ],

        // 33. Retail Pharmacy (Clinical Pure)
        'retail_pharmacy' => [
            'id' => 'retail_pharmacy',
            'bundle_dir' => 'retail_pharmacy',
            'name' => 'Clinical Pure (Apotek & Alkes)',
            'industry' => 'Apotek, Obat Resep Dokter, Vitamin & Alat Medis',
            'font_heading' => 'Plus Jakarta Sans',
            'font_body' => 'Inter',
            'google_fonts' => 'family=Plus+Jakarta+Sans:wght@600;700;800&family=Inter:wght@400;500;600',
            'primary_color' => '#0D9488',
            'accent_color' => '#0284C7',
            'bg_color' => '#F0FDFA',
            'card_bg' => '#FFFFFF',
            'header_dark' => false,
            'hero_style' => 'trusted_pharmacy',
            'badge_bg' => '#0D948815',
            'badge_text' => '#0D9488',
            'border_radius' => '14px',
            'description' => 'Kepercayaan medis teal higienis, izin BPOM & SIA resmi, upload foto resep dokter, dan konsultasi apoteker terpercaya.',
        ],

        // 34. Service Agency (Sovereign Enterprise)
        'service_agency' => [
            'id' => 'service_agency',
            'bundle_dir' => 'service_agency',
            'name' => 'Sovereign Enterprise (Konsultan & Agency)',
            'industry' => 'Creative Agency, Software House, IT & Konsultan',
            'font_heading' => 'Cormorant Garamond',
            'font_body' => 'Inter',
            'google_fonts' => 'family=Cormorant+Garamond:wght@600;700&family=Inter:wght@400;500;600',
            'primary_color' => '#1E3A8A',
            'accent_color' => '#D97706',
            'bg_color' => '#F8FAFC',
            'card_bg' => '#FFFFFF',
            'header_dark' => false,
            'hero_style' => 'agency_prestige',
            'badge_bg' => '#1E3A8A15',
            'badge_text' => '#1E3A8A',
            'border_radius' => '14px',
            'description' => 'Prestise korporat navy, portofolio proyek bergengsi, alur proposal RFQ formal, klausul NDA terjamin, dan kontrak B2B terstruktur.',
        ],

        // 35. Service Beauty (Aura Glamour)
        'service_beauty' => [
            'id' => 'service_beauty',
            'bundle_dir' => 'service_beauty',
            'name' => 'Aura Glamour (Klinik Estetika & Salon)',
            'industry' => 'Klinik Kecantikan, Estetika Kulit, Salon & Spa',
            'font_heading' => 'Playfair Display',
            'font_body' => 'Plus Jakarta Sans',
            'google_fonts' => 'family=Playfair+Display:ital,wght@0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@400;500;600',
            'primary_color' => '#BE185D',
            'accent_color' => '#D4AF37',
            'bg_color' => '#FDF2F8',
            'card_bg' => '#FFFFFF',
            'header_dark' => false,
            'hero_style' => 'rose_luxury_spa',
            'badge_bg' => '#BE185D15',
            'badge_text' => '#BE185D',
            'border_radius' => '18px',
            'description' => 'Kemewahan rose gold & berry wine, reservasi facial & perawatan kulit, dokter estetik profesional, dan hasil glowing nyata.',
        ],

        // 36. Service Carwash (Hydro Shield)
        'service_carwash' => [
            'id' => 'service_carwash',
            'bundle_dir' => 'service_carwash',
            'name' => 'Hydro Shield (Auto Detailing & Cuci Mobil)',
            'industry' => 'Cuci Mobil Hidrolik, Detailing, Coating & Salon Mobil',
            'font_heading' => 'Orbitron',
            'font_body' => 'Inter',
            'google_fonts' => 'family=Orbitron:wght@600;700;800&family=Inter:wght@400;500;600',
            'primary_color' => '#0284C7',
            'accent_color' => '#38BDF8',
            'bg_color' => '#0B0F19',
            'card_bg' => '#181E2A',
            'header_dark' => true,
            'hero_style' => 'hydro_gloss',
            'badge_bg' => '#0284C720',
            'badge_text' => '#38BDF8',
            'border_radius' => '14px',
            'description' => 'Dark glossy hydrophobic aesthetic, snow wash salju aktif, nano ceramic coating anti-gores, dan reservasi slot cuci ekspres.',
        ],

        // 37. Service Fitness (Titan Kinetic)
        'service_fitness' => [
            'id' => 'service_fitness',
            'bundle_dir' => 'service_fitness',
            'name' => 'Titan Kinetic (Gym & Fitness Center)',
            'industry' => 'Pusat Kebugaran, Gym, Yoga & Personal Trainer',
            'font_heading' => 'Bebas Neue',
            'font_body' => 'Inter',
            'google_fonts' => 'family=Bebas+Neue&family=Inter:wght@400;500;600',
            'primary_color' => '#10B981',
            'accent_color' => '#F59E0B',
            'bg_color' => '#171717',
            'card_bg' => '#262626',
            'header_dark' => true,
            'hero_style' => 'athletic_power',
            'badge_bg' => '#10B98120',
            'badge_text' => '#34D399',
            'border_radius' => '12px',
            'description' => 'Athletic power hitam & hijau neon, jadwal kelas mingguan (HIIT/Pilates), pendaftaran member online, dan bimbingan coach bersertifikat.',
        ],

        // 38. Service Laundry (Aqua Bubble)
        'service_laundry' => [
            'id' => 'service_laundry',
            'bundle_dir' => 'service_laundry',
            'name' => 'Aqua Bubble (Laundry Kiloan & Dry Clean)',
            'industry' => 'Laundry Kiloan, Dry Clean, Cuci Sepatu & Karpet',
            'font_heading' => 'Plus Jakarta Sans',
            'font_body' => 'Inter',
            'google_fonts' => 'family=Plus+Jakarta+Sans:wght@600;700;800&family=Inter:wght@400;500;600',
            'primary_color' => '#0284C7',
            'accent_color' => '#06B6D4',
            'bg_color' => '#F0F9FF',
            'card_bg' => '#FFFFFF',
            'header_dark' => false,
            'hero_style' => 'bubble_fresh',
            'badge_bg' => '#0284C715',
            'badge_text' => '#0284C7',
            'border_radius' => '16px',
            'description' => 'Nuansa air higienis, garansi 1 mesin 1 pelanggan, deterjen ramah serat kain, pelacak status cuci, dan jemput-antar laundry gratis.',
        ],

        // 39. Service Petshop (Playful Paws)
        'service_petshop' => [
            'id' => 'service_petshop',
            'bundle_dir' => 'service_petshop',
            'name' => 'Playful Paws (Pet Shop & Grooming)',
            'industry' => 'Pet Shop, Klinik Hewan, Grooming Anabul & Pet Hotel',
            'font_heading' => 'Quicksand',
            'font_body' => 'Plus Jakarta Sans',
            'google_fonts' => 'family=Quicksand:wght@600;700&family=Plus+Jakarta+Sans:wght@400;500;600',
            'primary_color' => '#D97706',
            'accent_color' => '#059669',
            'bg_color' => '#FFFBEB',
            'card_bg' => '#FFFFFF',
            'header_dark' => false,
            'hero_style' => 'playful_pets',
            'badge_bg' => '#D9770615',
            'badge_text' => '#D97706',
            'border_radius' => '18px',
            'description' => 'Karakter ceria hangat ramah anabul, booking mandi grooming anti-kutu, kamar pet hotel ber-AC, dan makanan kucing/anjing premium.',
        ],

        // 40. Service Printing (Pixel Print)
        'service_printing' => [
            'id' => 'service_printing',
            'bundle_dir' => 'service_printing',
            'name' => 'Pixel Print (Percetakan & Desain Digital)',
            'industry' => 'Percetakan Digital, Banner, Sablon Kaos & Offset',
            'font_heading' => 'Syne',
            'font_body' => 'Inter',
            'google_fonts' => 'family=Syne:wght@600;700;800&family=Inter:wght@400;500;600',
            'primary_color' => '#7C3AED',
            'accent_color' => '#EC4899',
            'bg_color' => '#FAF5FF',
            'card_bg' => '#FFFFFF',
            'header_dark' => false,
            'hero_style' => 'cmyk_creative',
            'badge_bg' => '#7C3AED15',
            'badge_text' => '#7C3AED',
            'border_radius' => '14px',
            'description' => 'Studio cetak kreatif CMYK ungu magis, print spanduk kilat, sablon DTF kaos satuan, custom stiker kemasan, dan upload file desain.',
        ],

        // 41. Service Workshop (Apex Velocity)
        'service_workshop' => [
            'id' => 'service_workshop',
            'bundle_dir' => 'service_workshop',
            'name' => 'Apex Velocity (Bengkel Mobil & Motor)',
            'industry' => 'Bengkel Mobil, Motor, Ganti Oli & Sparepart Asli',
            'font_heading' => 'Chakra Petch',
            'font_body' => 'Inter',
            'google_fonts' => 'family=Chakra+Petch:wght@600;700&family=Inter:wght@400;500;600',
            'primary_color' => '#EA580C',
            'accent_color' => '#38BDF8',
            'bg_color' => '#0F172A',
            'card_bg' => '#1E293B',
            'header_dark' => true,
            'hero_style' => 'racing_mechanic',
            'badge_bg' => '#EA580C20',
            'badge_text' => '#FB923C',
            'border_radius' => '12px',
            'description' => 'Racing mechanic carbon dark, booking tune-up berkala tanpa antre, oli & sparepart orisinil bergaransi, dan cek kelistrikan scanner.',
        ],

        // 42. Universal Storefront
        'universal_storefront' => [
            'id' => 'universal_storefront',
            'bundle_dir' => 'universal_storefront',
            'name' => 'Universal Storefront (Bento Multi-Bisnis)',
            'industry' => 'Universal Storefront untuk Segala Jenis Usaha',
            'font_heading' => 'Plus Jakarta Sans',
            'font_body' => 'Inter',
            'google_fonts' => 'family=Plus+Jakarta+Sans:wght@600;700;800&family=Inter:wght@400;500;600',
            'primary_color' => '#007AFF',
            'accent_color' => '#34C759',
            'bg_color' => '#FFFFFF',
            'card_bg' => '#F8FAFC',
            'header_dark' => false,
            'hero_style' => 'apple_bento_universal',
            'badge_bg' => '#007AFF15',
            'badge_text' => '#007AFF',
            'border_radius' => '16px',
            'description' => 'Arsitektur Bento Grid Apple HIG yang universal, adaptif, modern, dan sangat fleksibel untuk berbagai model bisnis UMKM.',
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
        'elektronik' => 'online_store',

        // 4. Product Showcase
        'product_showcase' => 'product_showcase',
        'showcase' => 'product_showcase',
        'hero_product' => 'product_showcase',
        'skincare' => 'product_showcase',
        'beauty_product' => 'product_showcase',
        'mfg_cosmetics' => 'product_showcase',
        'cosmetics' => 'product_showcase',

        // 5. Food & Restaurant
        'food_restaurant' => 'food_restaurant',
        'resto' => 'food_restaurant',
        'restaurant' => 'food_restaurant',
        'nusantara_feast' => 'food_restaurant',
        'fnb_resto' => 'food_restaurant',
        'neon_crunch' => 'food_restaurant',
        'fnb_fastfood' => 'food_restaurant',
        'kuliner' => 'food_restaurant',
        'fnb_cloud_kitchen' => 'food_restaurant',
        'fnb_frozen_food' => 'food_restaurant',
        'fnb_catering_diet' => 'food_restaurant',

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
        'service_autodetailing' => 'service_business',
        'hydro_shield' => 'service_business',
        'cleaning' => 'service_business',
        'service_petshop' => 'service_business',
        'playful_paws' => 'service_business',
        'petshop' => 'service_business',
        'mfg_tailor_custom' => 'service_business',

        // 9. Portfolio & Creative
        'portfolio_creative' => 'portfolio_creative',
        'creative' => 'portfolio_creative',
        'portfolio' => 'portfolio_creative',
        'pixel_print' => 'portfolio_creative',
        'service_printing' => 'portfolio_creative',
        'design' => 'portfolio_creative',
        'studio' => 'portfolio_creative',
        'studio_foto' => 'portfolio_creative',

        // 10. Professional Services
        'professional_services' => 'professional_services',
        'professional' => 'professional_services',
        'accounting' => 'professional_services',
        'tax' => 'professional_services',
        'legal' => 'professional_services',
        'law' => 'professional_services',
        'notaris' => 'professional_services',

        // 11. Manufacturing & Factory
        'manufacturing_factory' => 'manufacturing_factory',
        'manufacturing' => 'manufacturing_factory',
        'factory' => 'manufacturing_factory',
        'pabrik' => 'manufacturing_factory',
        'oem' => 'manufacturing_factory',
        'maklon' => 'manufacturing_factory',
        'konveksi' => 'manufacturing_factory',
        'mfg_garment' => 'manufacturing_factory',
        'mfg_precision' => 'manufacturing_factory',
        'mfg_furniture' => 'manufacturing_factory',
        'mfg_craft' => 'manufacturing_factory',
        'mfg_printing' => 'manufacturing_factory',

        // 12. Wholesale & Distributor
        'wholesale_distributor' => 'wholesale_distributor',
        'wholesale' => 'wholesale_distributor',
        'distributor' => 'wholesale_distributor',
        'grosir' => 'wholesale_distributor',
        'fresh_mart' => 'wholesale_distributor',
        'retail_grocery' => 'wholesale_distributor',
        'sembako' => 'wholesale_distributor',
        'distributor_fmcg' => 'wholesale_distributor',

        // 13. Construction & Property
        'construction_property' => 'construction_property',
        'construction' => 'construction_property',
        'property' => 'construction_property',
        'kontraktor' => 'construction_property',
        'ironclad_builder' => 'construction_property',
        'retail_building' => 'construction_property',
        'architecture' => 'construction_property',
        'service_contractor' => 'construction_property',

        // 14. Booking & Appointment
        'booking_appointment' => 'booking_appointment',
        'booking' => 'booking_appointment',
        'appointment' => 'booking_appointment',
        'service_workshop' => 'booking_appointment',
        'apex_velocity' => 'booking_appointment',
        'bengkel' => 'booking_appointment',
        'salon' => 'booking_appointment',
        'barbershop' => 'booking_appointment',
        'service_barbershop' => 'booking_appointment',

        // 15. Event & Wedding
        'event_wedding' => 'event_wedding',
        'event' => 'event_wedding',
        'wedding' => 'event_wedding',
        'epicurean_box' => 'event_wedding',
        'fnb_catering' => 'event_wedding',
        'flora_romance' => 'event_wedding',
        'retail_florist' => 'event_wedding',
        'catering' => 'event_wedding',
        'service_event' => 'event_wedding',
        'florist' => 'event_wedding',

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
        'agri_farming' => 'agriculture_farm',
        'hidroponik' => 'agriculture_farm',

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
        'gym' => 'personal_brand',
        'fitness' => 'personal_brand',
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
        'trading' => 'agriculture_farm',
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
        'agri_farming' => 'agriculture_farm',
        'distributor_fmcg' => 'wholesale_distributor',
    ];

    /**
     * Curated Google Fonts catalog for storefront identity customization.
     *
     * @var array<string, string>
     */
    public const CURATED_FONTS = [
        'Plus Jakarta Sans'     => 'family=Plus+Jakarta+Sans:wght@400;500;600;700;800',
        'Inter'                 => 'family=Inter:wght@400;500;600;700',
        'Urbanist'              => 'family=Urbanist:wght@500;600;700;800;900',
        'Syne'                  => 'family=Syne:wght@600;700;800',
        'Cormorant Garamond'    => 'family=Cormorant+Garamond:wght@400;500;600;700',
        'Playfair Display'      => 'family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,600',
        'DM Serif Display'      => 'family=DM+Serif+Display:ital@0;1',
        'Outfit'                => 'family=Outfit:wght@400;500;600;700;800',
        'Bebas Neue'            => 'family=Bebas+Neue',
        'Chakra Petch'          => 'family=Chakra+Petch:wght@500;600;700',
        'Orbitron'              => 'family=Orbitron:wght@600;700;800;900',
        'Merriweather'          => 'family=Merriweather:wght@400;700',
        'Bodoni Moda'           => 'family=Bodoni+Moda:ital,opsz,wght@0,6..96,600;0,6..96,700',
        'Quicksand'             => 'family=Quicksand:wght@500;600;700',
        'Space Grotesk'         => 'family=Space+Grotesk:wght@500;600;700',
        'Rajdhani'              => 'family=Rajdhani:wght@500;600;700',
        'Barlow Semi Condensed' => 'family=Barlow+Semi+Condensed:wght@600;700;800',
        'Archivo Black'         => 'family=Archivo+Black',
    ];

    /**
     * Curated sector recommendations for 1-click authentic industry copywriting & trust pillars.
     *
     * @var array<string, array<string, mixed>>
     */
    public const RECOMMENDATIONS = [
        'business_corporate' => [
            'headline' => 'Solusi Terpercaya & Mitra Strategis Bisnis Anda',
            'subheadline' => 'Kami menyediakan produk dan layanan berkualitas dengan standar profesional, legalitas resmi, dan dukungan penuh untuk perusahaan Anda.',
            'announcement_badge' => 'Mitra B2B Terpercaya di Indonesia',
            'cta_primary_text' => 'Minta Penawaran Resmi',
            'trust_pillars' => ['Legalitas PT/CV Resmi', 'Faktur Pajak Standar', 'SLA Pengiriman Terjamin', 'Dukungan Dedicated Account'],
        ],
        'modern_umkm' => [
            'headline' => 'Produk Lokal Berkualitas, Dibuat Sepenuh Hati',
            'subheadline' => 'Usaha lokal yang mengutamakan mutu bahan, pelayanan ramah, dan kepuasan pelanggan setia dengan harga yang bersahabat.',
            'announcement_badge' => 'Bangga Buatan Usaha Lokal',
            'cta_primary_text' => 'Lihat Produk & Pesan Cepat',
            'trust_pillars' => ['Olahan Bahan Pilihan Lokal', 'Pembayaran QRIS & Transfer', 'Pelayanan Ramah & Cepat', 'Pemesanan Praktis via WhatsApp'],
        ],
        'online_store' => [
            'headline' => 'Koleksi Tren Terbaru, Belanja Mudah & Pengiriman Cepat',
            'subheadline' => 'Temukan produk favorit Anda dengan promo menarik, jaminan keaslian barang, serta sistem checkout yang cepat dan aman.',
            'announcement_badge' => 'Gratis Ongkir & Promo Spesial Pekan Ini',
            'cta_primary_text' => 'Mulai Belanja Sekarang',
            'trust_pillars' => ['100% Produk Original Bergaransi', 'Checkout Cepat & Aman', 'Bebas Tukar Jika Rusak', 'Pengiriman Cepat Seluruh Wilayah'],
        ],
        'product_showcase' => [
            'headline' => 'Inovasi Formulasi Terbaik untuk Hasil Nyata Maksimal',
            'subheadline' => 'Diciptakan melalui riset mendalam dengan bahan aktif bermutu tinggi untuk memberikan manfaat terbaik dan pengalaman tak tertandingi.',
            'announcement_badge' => 'Produk Unggulan Teruji Klinis BPOM',
            'cta_primary_text' => 'Miliki Sekarang Juga',
            'trust_pillars' => ['Teruji Laboratorium & BPOM', 'Hasil Nyata Terbukti Pelanggan', 'Garansi Keaslian Produk 100%', 'Pengemasan Eksklusif Aman'],
        ],
        'food_restaurant' => [
            'headline' => 'Cita Rasa Nusantara Autentik, Hangat & Menggugah Selera',
            'subheadline' => 'Nikmati aneka hidangan khas resep turun-temurun dengan bahan segar pilihan, suasana bersantap nyaman untuk keluarga dan kolega.',
            'announcement_badge' => '100% Halal & Resep Tradisi Nusantara',
            'cta_primary_text' => 'Reservasi Meja & Lihat Menu',
            'trust_pillars' => ['100% Halal Terverifikasi', 'Dapur Bersih Standar Higienis', 'Bahan Segar Setiap Hari', 'Tersedia Ruang VIP & Keluarga'],
        ],
        'coffee_beverage' => [
            'headline' => 'Seduhan Kopi Artisan dari Biji Pilihan Terbaik',
            'subheadline' => 'Eksplorasi single origin nusantara dan racikan espresso berkelas, diseduh segar setiap hari oleh barista berdedikasi.',
            'announcement_badge' => 'Freshly Roasted Beans Every Week',
            'cta_primary_text' => 'Pesan Kopi Favorit',
            'trust_pillars' => ['100% Biji Kopi Pilihan', 'Roasting Profil Konsisten', 'Barista Bersertifikasi', 'Kemasan One-Way Valve Kedap Udara'],
        ],
        'bakery_cake' => [
            'headline' => 'Kelezatan Roti & Kue Panggang Segar Setiap Pagi',
            'subheadline' => 'Dibuat dari mentega murni pilihan dan tanpa pengawet buatan. Menghadirkan tekstur lembut, aroma semerbak, dan cita rasa istimewa.',
            'announcement_badge' => 'Fresh From The Oven Setiap Pukul 07.00',
            'cta_primary_text' => 'Pesan Kue & Roti Hari Ini',
            'trust_pillars' => ['Bake Fresh Daily Tanpa Pengawet', '100% Real Butter & Bahan Premium', 'Pemesanan Custom Cake H-1', 'Pengiriman Cepat Aman'],
        ],
        'service_business' => [
            'headline' => 'Layanan Profesional, Tepat Waktu & Bergaransi Kepuasan',
            'subheadline' => 'Solusi jasa andal dengan teknisi ahli, pengerjaan rapi, dan transparansi biaya tanpa kejutan tambahan di akhir.',
            'announcement_badge' => 'Layanan Bersertifikat & Bergaransi',
            'cta_primary_text' => 'Jadwalkan Layanan',
            'trust_pillars' => ['Teknisi Berpengalaman & Terlatih', 'Peralatan Kerja Standar Modern', 'Garansi Pengerjaan Ulang', 'Biaya Transparan di Awal'],
        ],
        'portfolio_creative' => [
            'headline' => 'Karya Visual Berkarakter, Strategis & Berdampak Nyata',
            'subheadline' => 'Membantu brand Anda tampil memikat melalui desain visual, branding identitas, dan pengalaman digital yang berkesan.',
            'announcement_badge' => 'Studio Kreatif & Desain Brand',
            'cta_primary_text' => 'Lihat Portofolio & Mulai Proyek',
            'trust_pillars' => ['Konsep Unik & Tidak Pasaran', 'Strategi Desain Berbasis Riset', 'Revisi Terstruktur & Fleksibel', 'Penyerahan File Master Lengkap'],
        ],
        'professional_services' => [
            'headline' => 'Konsultasi Hukum, Pajak & Keuangan Berintegritas',
            'subheadline' => 'Memberikan nasihat strategis, pendampingan kepatuhan hukum, audit perpajakan, dan tata kelola perusahaan dengan kerahasiaan penuh.',
            'announcement_badge' => 'Praktisi Berlisensi & Kode Etik Profesional',
            'cta_primary_text' => 'Jadwalkan Konsultasi Privat',
            'trust_pillars' => ['Kerahasiaan Klien 100% Terjaga', 'Praktisi Berlisensi Resmi', 'Solusi Hukum & Finansial Akurat', 'Komunikasi Terbuka & Transparan'],
        ],
        'manufacturing_factory' => [
            'headline' => 'Kapasitas Produksi Skala Besar, Maklon & Manufaktur Presisi',
            'subheadline' => 'Mitra manufaktur pabrikasi, konveksi garmen, packaging industri, dan permesinan presisi dengan quality control ketat dan kapasitas tinggi.',
            'announcement_badge' => 'Kapasitas Pabrikasi Skala Ribuan Unit',
            'cta_primary_text' => 'Konsultasi Maklon & Produksi',
            'trust_pillars' => ['Quality Control Bertingkat', 'Kapasitas Produksi Ribuan Unit', 'Ketepatan Waktu Pengiriman (SLA)', 'Perjanjian Kerahasiaan (NDA) Terjamin'],
        ],
        'wholesale_distributor' => [
            'headline' => 'Pusat Pasokan Grosir Tangan Pertama & Stok Siap Kirim',
            'subheadline' => 'Harga partai kompetitif untuk reseller, agen, toko kelontong, dan institusi dengan jaminan ketersediaan barang dan armada logistik sendiri.',
            'announcement_badge' => 'Distributor Resmi & Harga Partai Tangan Pertama',
            'cta_primary_text' => 'Download Katalog Harga Grosir',
            'trust_pillars' => ['Harga Terbaik Langsung Pabrik', 'Kapasitas Pasokan Kontinu', 'Fasilitas Tempo Pembayaran Mitra', 'Armada Pengiriman Ekspedisi Sendiri'],
        ],
        'construction_property' => [
            'headline' => 'Membangun Hunian Nyaman & Bangunan Impian Masa Depan',
            'subheadline' => 'Jasa kontraktor bangun baru, renovasi rumah, desain arsitektur presisi, dan properti pilihan dengan legalitas sertifikat terjamin.',
            'announcement_badge' => 'Kontraktor Berpengalaman & Bergaransi Struktur',
            'cta_primary_text' => 'Konsultasi RAB & Desain Gratis',
            'trust_pillars' => ['Rencana Anggaran Biaya (RAB) Jelas', 'Garansi Pemeliharaan Struktur', 'Arsitek & Mandor Berpengalaman', 'Progress Terlaporkan Berkala'],
        ],
        'booking_appointment' => [
            'headline' => 'Booking Jadwal Praktis Tanpa Antre & Bebas Menunggu',
            'subheadline' => 'Pilih sesi perawatan favorit Anda, pilih praktisi andalan, dan nikmati waktu layanan yang terjadwal rapi sesuai waktu luang Anda.',
            'announcement_badge' => 'Sistem Reservasi Otomatis Real-Time',
            'cta_primary_text' => 'Pilih Jadwal & Reservasi',
            'trust_pillars' => ['Konfirmasi Otomatis via WhatsApp', 'Praktisi Handal Berpengalaman', 'Ruang Tunggu Bersih & Ber-AC', 'Bisa Reschedule Fleksibel'],
        ],
        'event_wedding' => [
            'headline' => 'Mewujudkan Momen Bahagia dengan Perencanaan Sempurna',
            'subheadline' => 'Layanan wedding organizer, dekorasi pesta, dokumentasi foto/video, dan manajemen acara profesional yang tenang dan berkesan.',
            'announcement_badge' => 'Organizer Acara & Pernikahan Berpengalaman',
            'cta_primary_text' => 'Konsultasi Konsep Acara',
            'trust_pillars' => ['Tim Acara Dedicated Hari-H', 'Vendor Rekanan Terkurasi', 'Manajemen Rundown Presisi', 'Paket Fleksibel Sesuai Impian'],
        ],
        'hospitality_accommodation' => [
            'headline' => 'Ketenteraman Menginap dengan Sentuhan Hangat Keramahan',
            'subheadline' => 'Kamar bersih berstandar tinggi, fasilitas lengkap, udara sejuk, dan suasana relaksasi sempurna untuk liburan keluarga maupun perjalanan bisnis.',
            'announcement_badge' => 'Jaminan Kamar Bersih & Nyaman',
            'cta_primary_text' => 'Cek Ketersediaan Kamar',
            'trust_pillars' => ['Sanitasi Kamar Standar Hotel', 'High-Speed Wi-Fi Gratis', 'Sarapan Enak Pagi Hari', 'Check-In Cepat & Ramah'],
        ],
        'education_courses' => [
            'headline' => 'Kembangkan Potensi Diri Bersama Mentor Ahli Berpengalaman',
            'subheadline' => 'Bimbingan belajar, kursus keterampilan terapan, dan sertifikasi profesional dengan kurikulum mutakhir siap kerja.',
            'announcement_badge' => 'Kurikulum Terapan & Berorientasi Praktek',
            'cta_primary_text' => 'Daftar Kelas Sekarang',
            'trust_pillars' => ['Mentor Praktisi Berpengalaman', 'Materi Praktik Langsung Kerja', 'Sertifikat Kelulusan Resmi', 'Akses Komunitas Belajar Seumur Hidup'],
        ],
        'health_beauty' => [
            'headline' => 'Pancarkan Kecantikan Alami & Kesehatan Kulit Terawat',
            'subheadline' => 'Koleksi perawatan kulit dan suplemen alami berbahan ramah lingkungan yang telah teruji aman untuk merawat tubuh Anda setiap hari.',
            'announcement_badge' => '100% Produk Bersertifikat BPOM & Halal',
            'cta_primary_text' => 'Beli Perawatan Kulit',
            'trust_pillars' => ['Bahan Alami & Bebas Merkuri', 'Terdaftar Resmi di BPOM', 'Cocok untuk Kulit Sensitif', 'Konsultasi Perawatan Kulit Gratis'],
        ],
        'agriculture_farm' => [
            'headline' => 'Panen Segar Langsung dari Petani Lokal Berkelanjutan',
            'subheadline' => 'Hasil tani organik, sayuran hidroponik, buah musiman manis, dan bibit tanaman unggul bebas pestisida kimia berbahaya.',
            'announcement_badge' => '100% Hasil Kebun Alami & Segar',
            'cta_primary_text' => 'Pesan Hasil Panen Segar',
            'trust_pillars' => ['Dipetik Pagi Saat Segar', 'Bebas Pestisida Berbahaya', 'Mendukung Petani Lokal Indonesia', 'Pengemasan Bersih Menjaga Kesegaran'],
        ],
        'logistics_transportation' => [
            'headline' => 'Kirim Cepat, Aman & Terlacak ke Seluruh Wilayah',
            'subheadline' => 'Jasa ekspedisi kargo, sewa armada truk, pengiriman paket sameday, dan logistik pergudangan dengan tarif transparan dan pelacakan real-time.',
            'announcement_badge' => 'Jaringan Logistik Aman & Terpercaya',
            'cta_primary_text' => 'Cek Ongkir & Pesan Truk',
            'trust_pillars' => ['Pelacakan Resi Real-Time', 'Armada Truk Terawat & Pengemudi Ahli', 'Opsi Asuransi Perlindungan Barang', 'Layanan Door-to-Door Service'],
        ],
        'personal_brand' => [
            'headline' => 'Tumbuh Bersama Praktisi, Trainer & Konsultan Teruji',
            'subheadline' => 'Berbagi pandangan tajam, kelas mentoring privat, buku panduan, dan jasa pembicara ahli untuk mengakselerasi karir dan bisnis Anda.',
            'announcement_badge' => 'Trainer, Author & Business Advisor',
            'cta_primary_text' => 'Jadwalkan Sesi Konsultasi',
            'trust_pillars' => ['Pengalaman Nyata Lebih dari 10 Tahun', 'Ratusan Alumni & Klien Terbantu', 'Sesi Privat One-on-One Eksklusif', 'Action Plan Praktis & Terukur'],
        ],
        'fnb_bakery' => [
            'headline' => 'Pastry Renyah & Artisan Cake Lembut Setiap Hari',
            'subheadline' => 'Croissant berlapis renyah dengan butter Perancis, tart buah segar, dan kue ulang tahun estetik yang siap memeriahkan momen Anda.',
            'announcement_badge' => 'Artisan Pastry & Premium Cake Shop',
            'cta_primary_text' => 'Pesan Pastry Favorit',
            'trust_pillars' => ['100% Pure French Butter', 'Freshly Baked Every Morning', 'Custom Cake Estetik & Rapi', 'Kemasan Box Eksklusif & Higienis'],
        ],
        'fnb_cafe' => [
            'headline' => 'Ruang Nyaman, Kopi Nikmat & Sajian Istimewa',
            'subheadline' => 'Tempat bertukar ide, bersantai, atau bekerja dengan koneksi internet kencang dan racikan kopi berkualitas tinggi.',
            'announcement_badge' => 'Suasana Nyaman & Wi-Fi Kencang',
            'cta_primary_text' => 'Pesan Menu Favorit',
            'trust_pillars' => ['QR Code Dine-In di Meja', 'Barista Berpengalaman', 'Free High-Speed Wi-Fi', 'Banyak Colokan Listrik'],
        ],
        'fnb_catering' => [
            'headline' => 'Catering Prasmanan & Bento Box Berkelas untuk Setiap Acara',
            'subheadline' => 'Solusi konsumsi acara kantor, pernikahan, syukuran, hingga meal prep harian dengan menu variatif dan cita rasa lezat.',
            'announcement_badge' => 'Penyedia Katering Berstandar Higienis',
            'cta_primary_text' => 'Konsultasi Menu & Pesan Acara',
            'trust_pillars' => ['Sertifikasi Higienis Sanitasi', 'Tepat Waktu Sebelum Acara', 'Menu Dapat Disesuaikan Budget', 'Tester Gratis untuk Acara Besar'],
        ],
        'fnb_fastfood' => [
            'headline' => 'Renyah, Juicy & Puas di Setiap Gigitan!',
            'subheadline' => 'Burger daging sapi asli, ayam goreng ekstra krispi, kentang goreng renyah bumbu gurih, dan paket combo hemat siap antar panas-panas.',
            'announcement_badge' => 'Saji Cepat & Panas Garansi 15 Menit',
            'cta_primary_text' => 'Pesan Combo Sekarang',
            'trust_pillars' => ['Minyak Goreng Higienis & Berkala', 'Daging Sapi & Ayam Pilihan Segar', 'Kemasan Eco Food Grade', 'Pengiriman Instan Cepat'],
        ],
        'fnb_resto' => [
            'headline' => 'Warisan Kuliner Rempah Nusantara yang Menggugah Selera',
            'subheadline' => 'Disajikan hangat dengan bumbu otentik warisan keluarga, hidangan laut segar, daging empuk meresap, dan sambal khas penambah nafsu makan.',
            'announcement_badge' => 'Kelezatan Rempah Tradisi Indonesia',
            'cta_primary_text' => 'Reservasi Meja Keluarga',
            'trust_pillars' => ['Bumbu Rempah Segar Asli', 'Daging & Ikan Segar Berkualitas', 'Kapasitas Meja Rombongan Luas', 'Layanan Ramah Tamah Nusantara'],
        ],
        'retail_bookstore' => [
            'headline' => 'Jendela Pengetahuan, Buku Terpilih & Perlengkapan Kreatif',
            'subheadline' => 'Koleksi buku bacaan, sastra, pengembangan diri, buku pelajaran sekolah, hingga stationery estetis untuk mendukung produktivitas Anda.',
            'announcement_badge' => '100% Buku Asli Penerbit Resmi',
            'cta_primary_text' => 'Cari Judul Buku',
            'trust_pillars' => ['100% Buku Asli Bebas Bajakan', 'Packing Bubble Tebal & Aman', 'Bisa Request Judul Langka', 'Pembatas Buku Gratis Tiap Pesanan'],
        ],
        'retail_building' => [
            'headline' => 'Material Bangunan Kokoh & Perkakas Konstruksi Berkualitas',
            'subheadline' => 'Distributor semen, besi beton SNI, keramik, cat tembok, pipa, dan perkakas tukang lengkap untuk proyek rumah maupun konstruksi besar.',
            'announcement_badge' => 'Bahan Bangunan Standar SNI Teruji',
            'cta_primary_text' => 'Minta Penawaran Material',
            'trust_pillars' => ['Besi & Semen Standar SNI', 'Armada Pengiriman Truk Siaga', 'Harga Grosir Kontraktor', 'Bisa Konsultasi Kebutuhan Volume'],
        ],
        'retail_electronics' => [
            'headline' => 'Teknologi Terdepan, Gadget Original & Bergaransi Resmi',
            'subheadline' => 'Pusat belanja smartphone, laptop, audio, dan aksesori pintar dengan jaminan produk 100% original serta layanan purna jual terpercaya.',
            'announcement_badge' => 'Garansi Resmi Distributor & Brand',
            'cta_primary_text' => 'Beli Gadget Terbaru',
            'trust_pillars' => ['100% Produk Original Segel Pabrik', 'Garansi Resmi Distributor', 'Klaim Servis Dibantu Penuh', 'Packing Kayu & Asuransi Pengiriman'],
        ],
        'retail_fashion' => [
            'headline' => 'Elegansi Abadi Siluet Mode Kontemporer',
            'subheadline' => 'Koleksi busana pilihan dengan potongan presisi, kain serat alami berstandar kurasi tinggi, dan jahitan rapi yang dirancang untuk kenyamanan sepanjang hari.',
            'announcement_badge' => 'Koleksi Edisi Terbatas Musim Ini',
            'cta_primary_text' => 'Jelajahi Lookbook',
            'trust_pillars' => ['Bahan Premium Adem & Nyaman', 'Jahitan Rapi Standar Butik', 'Garansi Tukar Ukuran 7 Hari', 'Packaging Eksklusif & Wangi'],
        ],
        'retail_florist' => [
            'headline' => 'Rangkaian Bunga Segar Penuh Makna untuk Setiap Momen',
            'subheadline' => 'Buket wisuda, standing flower pernikahan, papan ucapan duka/selamat, dan karangan bunga segar dengan perangkai profesional.',
            'announcement_badge' => 'Bunga Segar Berkualitas & Pengiriman Cepat',
            'cta_primary_text' => 'Pesan Buket Sekarang',
            'trust_pillars' => ['Bunga Segar Pilihan Setiap Pagi', 'Rangkaian Estetis Sesuai Request', 'Kartu Ucapan Gratis Dicetak Rapi', 'Pengiriman Tepat Waktu'],
        ],
        'retail_grocery' => [
            'headline' => 'Kebutuhan Harian Lengkap, Segar & Hemat Belanja Dekat',
            'subheadline' => 'Sembako berkualitas, buah & sayur segar, perlengkapan dapur, dan kebutuhan rumah tangga sehari-hari dengan harga terbaik.',
            'announcement_badge' => 'Promo Belanja Hemat Setiap Hari',
            'cta_primary_text' => 'Mulai Belanja Kebutuhan',
            'trust_pillars' => ['Harga Jujur & Bersaing', 'Sayur & Telur Segar Pilihan', 'Stok Lengkap Siap Antar', 'Melayani Grosir & Eceran'],
        ],
        'retail_pharmacy' => [
            'headline' => 'Apotek Terpercaya, Obat Lengkap & Konsultasi Apoteker',
            'subheadline' => 'Melayani resep dokter, obat bebas, suplemen kesehatan, vitamin, dan alat kesehatan bersertifikat Kemenkes dengan harga transparan.',
            'announcement_badge' => 'Apotek Resmi Berizin SIA & SIPA',
            'cta_primary_text' => 'Konsultasi & Tebus Obat',
            'trust_pillars' => ['100% Asli dari Distributor Resmi', 'Apoteker Siap Melayani', 'Penyimpanan Sesuai Suhu Standar', 'Pengiriman Obat Cepat & Privasi'],
        ],
        'service_agency' => [
            'headline' => 'Mendorong Pertumbuhan Bisnis dengan Strategi Tepat Sasaran',
            'subheadline' => 'Agensi pemasaran digital, pengembangan web korporat, dan strategi performa bisnis untuk menghasilkan konversi dan ROI maksimal.',
            'announcement_badge' => 'Mitra Pertumbuhan Digital Bisnis Anda',
            'cta_primary_text' => 'Mulai Diskusi Proyek',
            'trust_pillars' => ['Metodologi Kerja Berbasis Data', 'Tim Spesialis Berpengalaman', 'Transparansi Biaya & Laporan Berkala', 'Kemitraan Jangka Panjang Berkelanjutan'],
        ],
        'service_beauty' => [
            'headline' => 'Pancarkan Pesona Alami dengan Perawatan Kulit Terbaik',
            'subheadline' => 'Klinik estetika dan salon kecantikan dengan terapis bersertifikat, alat modern higienis, dan rangkaian skincare teruji klinis BPOM.',
            'announcement_badge' => 'Dokter & Beautician Bersertifikat',
            'cta_primary_text' => 'Jadwalkan Konsultasi Estetika',
            'trust_pillars' => ['Alat Steril Standar Medis', 'Skincare Resmi Terdaftar BPOM', 'Konsultasi Kulit Personal', 'Suasana Terapi Relaks & Nyaman'],
        ],
        'service_carwash' => [
            'headline' => 'Auto Detailing & Cuci Mobil Bersih Kinclong Luar Dalam',
            'subheadline' => 'Perawatan cuci salju nano, poles bodi 3-step, coating keramik, dan pembersihan interior menyeluruh untuk perlindungan maksimal kendaraan Anda.',
            'announcement_badge' => 'Shampoo pH Netral & Air Filtrasi Khusus',
            'cta_primary_text' => 'Booking Paket Cuci',
            'trust_pillars' => ['Shampoo pH Netral Aman untuk Cat', 'Kain Microfiber Khusus Anti-Baret', 'Tenaga Cuci Teliti & Terlatih', 'Garansi Kinclong Berkilau'],
        ],
        'service_fitness' => [
            'headline' => 'Bentuk Tubuh Impian, Tingkatkan Stamina & Hidup Sehat',
            'subheadline' => 'Pusat kebugaran dengan peralatan lengkap standar internasional, kelas olahraga dinamis, dan bimbingan pelatih (personal trainer) profesional.',
            'announcement_badge' => 'Gym Lengkap & Komunitas Positif',
            'cta_primary_text' => 'Daftar Member Hari Ini',
            'trust_pillars' => ['Peralatan Gym Standar Internasional', 'Personal Trainer Bersertifikasi', 'Loker & Shower Air Hangat Bersih', 'Kelas Olahraga Variatif Tiap Hari'],
        ],
        'service_laundry' => [
            'headline' => 'Laundry Bersih Higienis, Rapi Licin & Wangi Tahan Lama',
            'subheadline' => 'Layanan laundry kiloan, cuci setrika satuan, dry cleaning jas/gaun, cuci selimut & sepatu dengan deterjen ramah serat kain.',
            'announcement_badge' => '1 Mesin 1 Pelanggan (Tidak Dicampur)',
            'cta_primary_text' => 'Pesan Antar Jemput Laundry',
            'trust_pillars' => ['1 Mesin 1 Pelanggan Higienis', 'Air Filter Bersih Bebas Noda', 'Setrika Uap Presisi Anti-Gosong', 'Layanan Antar Jemput Tersedia'],
        ],
        'service_petshop' => [
            'headline' => 'Perawatan Kasih Sayang, Makanan Sehat & Grooming Anabul',
            'subheadline' => 'Layanan grooming kucing & anjing profesional, pet hotel nyaman ber-AC, vaksinasi dokter hewan, serta pakan berkualitas tinggi.',
            'announcement_badge' => 'Groomer Berpengalaman & Sayang Hewan',
            'cta_primary_text' => 'Booking Grooming Anabul',
            'trust_pillars' => ['Sampo Hipoalergenik Khusus Anabul', 'Groomer Sabar & Berpengalaman', 'Ruang Penitipan Steril & Bersih', 'Pakan Berkualitas & Terjamin'],
        ],
        'service_printing' => [
            'headline' => 'Cetak Cepat Kualitas Tajam, Spanduk, Brosur & Merchandise',
            'subheadline' => 'Percetakan digital printing banner, kartu nama, stiker label kemasan, buku, dan merchandise promosi dengan warna akurat serta pengerjaan kilat.',
            'announcement_badge' => 'Mesin Cetak Presisi Resolusi Tinggi',
            'cta_primary_text' => 'Kirim File & Cetak Sekarang',
            'trust_pillars' => ['Warna Akurat Mesin Industri', 'Bisa Cetak Satuan Maupun Ribuan', 'Pengerjaan Kilat Sameday', 'Bantuan Cek File Desain Gratis'],
        ],
        'service_workshop' => [
            'headline' => 'Performa Mesin Prima, Servis Ahli & Sparepart Bergaransi',
            'subheadline' => 'Bengkel perawatan berkala, tune-up komputer, ganti oli original, servis rem & suspensi dengan mekanik berpengalaman dan peralatan modern.',
            'announcement_badge' => 'Bengkel Terverifikasi & Mekanik Handal',
            'cta_primary_text' => 'Booking Servis Sekarang',
            'trust_pillars' => ['100% Sparepart Original Bergaransi', 'Scanner Komputer Canggih', 'Transparansi Biaya & Estimasi di Depan', 'Ruang Tunggu Ber-AC & Nyaman'],
        ],
        'universal_storefront' => [
            'headline' => 'Toko Resmi & Layanan Lengkap Kebutuhan Anda',
            'subheadline' => 'Jelajahi pilihan produk terbaik dan layanan unggulan kami dengan pengalaman transaksi yang mudah, aman, dan terpercaya.',
            'announcement_badge' => 'Toko Resmi & Terverifikasi',
            'cta_primary_text' => 'Buka Katalog Toko',
            'trust_pillars' => ['Kualitas Produk Terjamin', 'Kemudahan Transaksi Online', 'Dukungan Pelanggan Responsif', 'Pengiriman Aman & Terpercaya'],
        ],
    ];

    /**
     * Get all available theme presets (42 distinct themes with recommendations).
     *
     * @return array<string, array<string, mixed>>
     */
    public function getAllThemes(): array
    {
        $themes = self::THEMES;
        foreach ($themes as $key => &$theme) {
            $theme['recommendation'] = self::RECOMMENDATIONS[$key] ?? self::RECOMMENDATIONS['modern_umkm'];
        }
        unset($theme);

        return $themes;
    }

    /**
     * Check if a theme preset or alias exists in catalogue.
     */
    public function hasTheme(string $presetKey): bool
    {
        return isset(self::THEMES[$presetKey])
            || isset(self::ALIASES[$presetKey])
            || isset(self::TEMPLATE_CODE_MAP[$presetKey]);
    }

    /**
     * Get curated Google fonts map.
     *
     * @return array<string, string>
     */
    public function getCuratedFonts(): array
    {
        return self::CURATED_FONTS;
    }

    /**
     * Get industry recommendation for a theme.
     *
     * @param string $presetKey
     * @return array<string, mixed>
     */
    public function getRecommendation(string $presetKey): array
    {
        $theme = $this->getTheme($presetKey);
        $key = $theme['id'] ?? $presetKey;

        return self::RECOMMENDATIONS[$key] ?? self::RECOMMENDATIONS['modern_umkm'];
    }

    /**
     * Get configuration for a specific theme key or alias.
     *
     * @param string $presetKey
     * @return array<string, mixed>
     */
    public function getTheme(string $presetKey): array
    {
        $resolved = null;

        // 1. Direct match on key
        if (isset(self::THEMES[$presetKey])) {
            $resolved = self::THEMES[$presetKey];
        } elseif (isset(self::ALIASES[$presetKey]) && isset(self::THEMES[self::ALIASES[$presetKey]])) {
            // 2. Direct match on alias / bundle_dir
            $resolved = self::THEMES[self::ALIASES[$presetKey]];
        } elseif (isset(self::TEMPLATE_CODE_MAP[$presetKey]) && isset(self::THEMES[self::TEMPLATE_CODE_MAP[$presetKey]])) {
            // 3. Match on TEMPLATE_CODE_MAP
            $resolved = self::THEMES[self::TEMPLATE_CODE_MAP[$presetKey]];
        } else {
            // 4. Scan bundle_dir inside THEMES
            foreach (self::THEMES as $t) {
                if (($t['bundle_dir'] ?? null) === $presetKey) {
                    $resolved = $t;
                    break;
                }
            }
        }

        if (! $resolved) {
            $resolved = self::THEMES['modern_umkm'];
        }

        $id = $resolved['id'] ?? $presetKey;
        $resolved['recommendation'] = self::RECOMMENDATIONS[$id] ?? self::RECOMMENDATIONS['modern_umkm'];

        return $resolved;
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
        if (! empty($landingPage->font_family)) {
            $fontName = $landingPage->font_family;
            $theme['font_heading'] = $fontName;
            $theme['font_body'] = $fontName;
            if (isset(self::CURATED_FONTS[$fontName])) {
                $theme['google_fonts'] = self::CURATED_FONTS[$fontName] . '&family=Inter:wght@400;500;600';
            }
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
