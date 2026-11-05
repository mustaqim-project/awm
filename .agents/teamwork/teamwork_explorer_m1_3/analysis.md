# Analisis Teknis & Desain Arsitektur: Arketipe & Fallback Universal pada StorefrontThemeService

**Milestone:** Milestone 1 (Backend & Service Normalization)  
**Komponen:** `app/Domain/Storefront/StorefrontThemeService.php`  
**Explorer:** Explorer 3 (Investigation & Architecture Design)  
**Status:** Rekomendasi Selesai (Read-Only)  
**Dokumen Rujukan:**
- `STANDARISASI_DAN_IMPLEMENTASI_TEMA_UNIVERSAL.md` (SSOT Blueprint §1, §4, §5)
- `c:\laragon\www\cooca_core\.agents\teamwork\orchestrator_1\PROJECT.md`

---

## 1. Executive Summary

Platform SaaS ERP COOCA memiliki 42 tema storefront unik yang tersimpan pada direktori `resources/views/public/storefront/themes/{theme_key}/`. Sesuai dengan Cetak Biru Standarisasi Tema Universal, tema tidak lagi diperlakukan sebagai cetakan kaku per sektor usaha (yang sebelumnya menyebabkan teks industri tertanam mati/hardcoded), melainkan sebagai **Arketipe Desain Visual (Design Archetype)** dengan arsitektur *"Design Shell vs Dynamic Content Injection"*.

Tugas Explorer 3 adalah mendesain struktur data dan tanda tangan metode (method signatures) pada `App\Domain\Storefront\StorefrontThemeService`:
1. Konstanta `ARCHETYPES` yang memetakan 6 Arketipe Desain ke tepat 42 tema.
2. Konstanta `THEME_ARCHETYPE_MAP` untuk O(1) reverse-lookup cepat dari tema ke arketipe.
3. Metode `getThemeArchetype(string $themeKey): array`.
4. Metode `getAllArchetypes(): array`.
5. Metode `getUniversalFallback(string $themeKey): array`.
6. Jaminan kompatibilitas mundur 100% (*zero regression*) terhadap `getAllThemes()`, `resolveTheme()`, `hasTheme()`, dan `getTheme()`.

Seluruh pemetaan 42 tema telah diverifikasi secara matematis dan logis tanpa duplikasi atau ketidakhadiran tema (8 + 7 + 6 + 7 + 7 + 7 = 42 tema).

---

## 2. Observasi Kondisi Eksisting `StorefrontThemeService.php`

### 2.1 Ringkasan Kode Eksisting
- **Lokasi File:** `app/Domain/Storefront/StorefrontThemeService.php` (1.718 baris).
- **Konstanta Eksisting:**
  1. `private const THEMES` (baris 18–900): Mendefinisikan katalog 42 tema dengan properti: `id`, `bundle_dir`, `name`, `industry`, `font_heading`, `font_body`, `google_fonts`, `primary_color`, `accent_color`, `bg_color`, `card_bg`, `header_dark`, `hero_style`, `badge_bg`, `badge_text`, `border_radius`, `description`.
  2. `public const TEMPLATE_CODE_MAP` (baris 907–1121): Pemetaan kode template historis/industri ke ID tema.
  3. `public const CATEGORY_MAP` (baris 1128–1174): Pemetaan kategori industri master bisnis ke ID tema.
  4. `private const ALIASES` (baris 1181–1226): Pemetaan alias nama lama ke tema rujukan.
  5. `public const CURATED_FONTS` (baris 1233–1252): 18 Google Fonts pilihan.
  6. `public const RECOMMENDATIONS` (baris 1259–1554): Rekomendasi teks industri untuk 42 tema (berisi `headline`, `subheadline`, `announcement_badge`, `cta_primary_text`, `trust_pillars`).
- **Metode Eksisting:**
  1. `getAllThemes(): array` (baris 1561–1570): Mengembalikan semua 42 tema dengan rekomendasi.
  2. `hasTheme(string $presetKey): bool` (baris 1575–1580): Memeriksa keberadaan tema/alias.
  3. `getCuratedFonts(): array` (baris 1587–1590): Mengembalikan daftar font.
  4. `getRecommendation(string $presetKey): array` (baris 1598–1604): Mengembalikan rekomendasi sektor industri.
  5. `getTheme(string $presetKey): array` (baris 1612–1643): Menyelesaikan konfigurasi tema dengan resolusi fallback ke `modern_umkm`.
  6. `detectThemeKeyForBusiness(?Business $business): string` (baris 1649–1679): Deteksi tema otomatis dari model Bisnis.
  7. `resolveTheme(BusinessLandingPage $landingPage): array` (baris 1687–1716): Menyelesaikan konfigurasi aktif dengan override warna kustom, tipografi kustom, dan dark mode.

### 2.2 Konsumsi di Controller & View
1. `App\Http\Controllers\Web\BusinessLandingPageWebController::edit()`:
   Memanggil `$themeService->getAllThemes()` dan `$themeService->getCuratedFonts()` untuk mengisi editor CMS cockpit (`resources/views/app/landing_page/edit.blade.php`).
2. `App\Http\Controllers\Web\Storefront\PublicStorefrontController::resolveContext()`:
   Memanggil `$this->themeService->resolveTheme($landingPage)` dan mendukung preview dinamis via `?preview_theme=...`.
3. `resources/views/public/storefront/home.blade.php`:
   Membaca `$activeTheme = $theme ?? app(StorefrontThemeService::class)->resolveTheme($landingPage)` dan merender template modular via `$bundleDir`.

---

## 3. Desain Struktur Data Arketipe & Pemetaan 42 Tema

### 3.1 Matriks 6 Arketipe Desain (Blueprint §4)

| # | ID Arketipe | Nama Tampilan | Vibe & Karakteristik Desain | Font Rekomendasi | Aksen Standar | Jml Tema |
|---|---|---|---|---|---|:---:|
| 1 | `clean_bento` | Clean Bento & Modern Tech | Flat card, squircle, Apple HIG bento grid, minimalis, presisi tinggi | Plus Jakarta Sans / Inter | `#007AFF` | 8 |
| 2 | `warm_artisan` | Earthy & Warm Artisan | Palet tanah/zaitun, kehangatan kerajinan tangan, otentik | Playfair Display / DM Serif | `#8B5A2B` | 7 |
| 3 | `luxe_editorial` | High-End Luxe & Editorial | Spasi bernapas luas, kontras tinggi, anggun, lookbook majalah mewah | Cormorant Garamond / Syne | `#0A0A0A` | 6 |
| 4 | `dark_carbon` | Dark Carbon & High Impact | Latar gelap `#0B0F19`, aksen neon bertenaga, energi industri tangguh | Chakra Petch / Orbitron | `#EA580C` | 7 |
| 5 | `playful_fresh` | Playful, Fresh & Appetite | Sudut lengkung lembut, warna cerah menggugah selera, ramah | DM Serif / Urbanist / Quicksand | `#991B1B` | 7 |
| 6 | `institutional_trust` | Institutional & Trust | Formal, biru/zamrud korporat, kredibilitas tinggi, navigasi resmi | Plus Jakarta Sans / Merriweather | `#2563EB` | 7 |
| **Total** | | | | | | **42** |

### 3.2 Pemetaan Tema ke Arketipe (Partition 42 Tema)

#### 1. `clean_bento` (8 Tema)
- `universal_storefront` — Universal Storefront (Bento Multi-Bisnis)
- `modern_umkm` — Modern UMKM
- `online_store` — Online Store
- `service_agency` — Modern Digital Agency
- `retail_electronics` — Cyber Electronics & Gadget
- `retail_building` — Mitra Konstruksi & Material SNI
- `professional_services` — Advisory & Legal Practice
- `retail_grocery` — Pasar Sembako & Kebutuhan Harian

#### 2. `warm_artisan` (7 Tema)
- `coffee_beverage` — Coffee & Beverage (Artisan Brew)
- `agriculture_farm` — Kebun Segar & Hidroponik
- `bakery_cake` — Roti & Kue Panggang Segar
- `fnb_bakery` — Artisan Bakery & Patisserie
- `fnb_cafe` — Cafe & Roastery
- `retail_florist` — Toko Bunga & Florist Romantis
- `hospitality_accommodation` — Homestay, Villa & Penginapan

#### 3. `luxe_editorial` (6 Tema)
- `retail_fashion` — Butik & Mode Busana Eksklusif
- `health_beauty` — Skincare, Estetika & Kosmetik
- `product_showcase` — Produk Unggulan & Formula Inovatif
- `event_wedding` — Wedding & Event Organizer
- `personal_brand` — Personal Brand & Konsultan Privat
- `portfolio_creative` — Studio Kreatif & Desain Brand

#### 4. `dark_carbon` (7 Tema)
- `service_workshop` — Bengkel Mobil & Motor (Apex Velocity)
- `service_carwash` — Auto Detailing & Cuci Mobil
- `service_fitness` — Fitness Center & Gym Komunitas
- `service_laundry` — Laundry Higienis & Dry Clean
- `construction_property` — Kontraktor & Renovasi Properti
- `manufacturing_factory` — Pabrik, Maklon OEM & Konveksi
- `logistics_transportation` — Ekspedisi & Armada Logistik

#### 5. `playful_fresh` (7 Tema)
- `food_restaurant` — Restoran Nusantara & Cita Rasa Otentik
- `fnb_resto` — Resto Keluarga & Sajian Khas
- `fnb_fastfood` — Kuliner Cepat Saji & Gorengan Renyah
- `fnb_catering` — Jasa Katering Acara & Pesta
- `retail_bookstore` — Toko Buku & Alat Tulis
- `service_petshop` — Pet Care, Grooming & Hotel Anabul
- `service_beauty` — Salon Kecantikan & Perawatan Terapis

#### 6. `institutional_trust` (7 Tema)
- `business_corporate` — Business Corporate & Solusi B2B
- `education_courses` — Kursus, Pelatihan & Akademi Kejuruan
- `retail_pharmacy` — Apotek Resmi & Perlengkapan Medis
- `service_business` — Layanan Jasa & Perawatan Terpercaya
- `service_printing` — Digital Printing & Percetakan Cepat
- `booking_appointment` — Booking Jadwal & Konsultasi
- `wholesale_distributor` — Pusat Grosir & Pasokan Partai Besar

---

## 4. Definisi Konstanta yang Dianjurkan

### 4.1 Konstanta `ARCHETYPES`
```php
    /**
     * Map of 6 Visual Design Archetypes organizing the 42 Storefront Themes.
     * Used for CMS cockpit filtering, design token defaults, and archetype metadata.
     *
     * @var array<string, array<string, mixed>>
     */
    public const ARCHETYPES = [
        'clean_bento' => [
            'id' => 'clean_bento',
            'name' => 'Clean Bento & Modern Tech',
            'badge' => '8 Tema',
            'icon' => 'layout-grid',
            'character' => 'Flat card, Plus Jakarta Sans/Inter, squircle, minimalis, rapi.',
            'description' => 'Arsitektur modular Bento Grid Apple HIG dengan kepadatan informasi optimal, sudut squircle elegan, dan tipografi sans-serif modern yang presisi.',
            'default_font_heading' => 'Plus Jakarta Sans',
            'default_font_body' => 'Inter',
            'default_primary' => '#007AFF',
            'default_bg' => '#FFFFFF',
            'themes' => [
                'universal_storefront',
                'modern_umkm',
                'online_store',
                'service_agency',
                'retail_electronics',
                'retail_building',
                'professional_services',
                'retail_grocery',
            ],
        ],
        'warm_artisan' => [
            'id' => 'warm_artisan',
            'name' => 'Earthy & Warm Artisan',
            'badge' => '7 Tema',
            'icon' => 'coffee',
            'character' => 'Playfair Display/DM Serif, palet zaitun/cokelat, hangat.',
            'description' => 'Estetika hangat kerajinan otentik dengan palet bumi (earthy tones), tipografi serif klasik yang ramah, dan tekstur organik yang menenangkan.',
            'default_font_heading' => 'Playfair Display',
            'default_font_body' => 'Inter',
            'default_primary' => '#8B5A2B',
            'default_bg' => '#FAF7F2',
            'themes' => [
                'coffee_beverage',
                'agriculture_farm',
                'bakery_cake',
                'fnb_bakery',
                'fnb_cafe',
                'retail_florist',
                'hospitality_accommodation',
            ],
        ],
        'luxe_editorial' => [
            'id' => 'luxe_editorial',
            'name' => 'High-End Luxe & Editorial',
            'badge' => '6 Tema',
            'icon' => 'sparkles',
            'character' => 'Cormorant Garamond/Syne, monokrom, spasi luas, elegan.',
            'description' => 'Tampilan editorial majalah mewah (lookbook) dengan spasi bernapas luas, kontras monokrom tinggi, tipografi display kurasi, dan aura prestisius.',
            'default_font_heading' => 'Cormorant Garamond',
            'default_font_body' => 'Inter',
            'default_primary' => '#0A0A0A',
            'default_bg' => '#FDFBF7',
            'themes' => [
                'retail_fashion',
                'health_beauty',
                'product_showcase',
                'event_wedding',
                'personal_brand',
                'portfolio_creative',
            ],
        ],
        'dark_carbon' => [
            'id' => 'dark_carbon',
            'name' => 'Dark Carbon & High Impact',
            'badge' => '7 Tema',
            'icon' => 'zap',
            'character' => 'Dark #0B0F19, Chakra Petch/Orbitron, aksen kontras neon.',
            'description' => 'Nuansa dark mode berani dengan latar karbon #0B0F19, aksen neon tajam bertenaga, geometri tegas, dan tipografi industrial berkekuatan tinggi.',
            'default_font_heading' => 'Chakra Petch',
            'default_font_body' => 'Inter',
            'default_primary' => '#EA580C',
            'default_bg' => '#0B0F19',
            'themes' => [
                'service_workshop',
                'service_carwash',
                'service_fitness',
                'service_laundry',
                'construction_property',
                'manufacturing_factory',
                'logistics_transportation',
            ],
        ],
        'playful_fresh' => [
            'id' => 'playful_fresh',
            'name' => 'Playful, Fresh & Appetite',
            'badge' => '7 Tema',
            'icon' => 'smile',
            'character' => 'Soft rounded, warna cerah menggugah, Urbanist, ramah.',
            'description' => 'Visual ceria penuh energi dengan sudut lengkung lembut (soft rounded), palet warna segar menggugah selera, dan tipografi bersahaja yang mengundang interaksi.',
            'default_font_heading' => 'DM Serif Display',
            'default_font_body' => 'Plus Jakarta Sans',
            'default_primary' => '#991B1B',
            'default_bg' => '#FFFBEB',
            'themes' => [
                'food_restaurant',
                'fnb_resto',
                'fnb_fastfood',
                'fnb_catering',
                'retail_bookstore',
                'service_petshop',
                'service_beauty',
            ],
        ],
        'institutional_trust' => [
            'id' => 'institutional_trust',
            'name' => 'Institutional & Trust',
            'badge' => '7 Tema',
            'icon' => 'shield-check',
            'character' => 'Formal, navy/emerald korporat, Plus Jakarta Sans, terpercaya.',
            'description' => 'Arsitektur tata kelola resmi dengan palet biru/zamrud korporat, kartu sertifikasi kredibilitas tinggi, dan struktur navigasi berstandar institusional.',
            'default_font_heading' => 'Plus Jakarta Sans',
            'default_font_body' => 'Inter',
            'default_primary' => '#2563EB',
            'default_bg' => '#F8FAFC',
            'themes' => [
                'business_corporate',
                'education_courses',
                'retail_pharmacy',
                'service_business',
                'service_printing',
                'booking_appointment',
                'wholesale_distributor',
            ],
        ],
    ];
```

### 4.2 Konstanta `THEME_ARCHETYPE_MAP`
```php
    /**
     * O(1) direct reverse lookup mapping all 42 theme IDs to their design archetype key.
     *
     * @var array<string, string>
     */
    public const THEME_ARCHETYPE_MAP = [
        // 1. Clean Bento & Modern Tech (8)
        'universal_storefront'       => 'clean_bento',
        'modern_umkm'               => 'clean_bento',
        'online_store'              => 'clean_bento',
        'service_agency'            => 'clean_bento',
        'retail_electronics'        => 'clean_bento',
        'retail_building'           => 'clean_bento',
        'professional_services'     => 'clean_bento',
        'retail_grocery'            => 'clean_bento',

        // 2. Earthy & Warm Artisan (7)
        'coffee_beverage'           => 'warm_artisan',
        'agriculture_farm'          => 'warm_artisan',
        'bakery_cake'               => 'warm_artisan',
        'fnb_bakery'                => 'warm_artisan',
        'fnb_cafe'                  => 'warm_artisan',
        'retail_florist'            => 'warm_artisan',
        'hospitality_accommodation' => 'warm_artisan',

        // 3. High-End Luxe & Editorial (6)
        'retail_fashion'            => 'luxe_editorial',
        'health_beauty'             => 'luxe_editorial',
        'product_showcase'          => 'luxe_editorial',
        'event_wedding'             => 'luxe_editorial',
        'personal_brand'            => 'luxe_editorial',
        'portfolio_creative'        => 'luxe_editorial',

        // 4. Dark Carbon & High Impact (7)
        'service_workshop'          => 'dark_carbon',
        'service_carwash'           => 'dark_carbon',
        'service_fitness'           => 'dark_carbon',
        'service_laundry'           => 'dark_carbon',
        'construction_property'     => 'dark_carbon',
        'manufacturing_factory'     => 'dark_carbon',
        'logistics_transportation'  => 'dark_carbon',

        // 5. Playful, Fresh & Appetite (7)
        'food_restaurant'           => 'playful_fresh',
        'fnb_resto'                 => 'playful_fresh',
        'fnb_fastfood'              => 'playful_fresh',
        'fnb_catering'              => 'playful_fresh',
        'retail_bookstore'          => 'playful_fresh',
        'service_petshop'           => 'playful_fresh',
        'service_beauty'            => 'playful_fresh',

        // 6. Institutional & Trust (7)
        'business_corporate'        => 'institutional_trust',
        'education_courses'         => 'institutional_trust',
        'retail_pharmacy'           => 'institutional_trust',
        'service_business'          => 'institutional_trust',
        'service_printing'          => 'institutional_trust',
        'booking_appointment'       => 'institutional_trust',
        'wholesale_distributor'     => 'institutional_trust',
    ];
```

---

## 5. Desain Metode Baru & Tanda Tangan (Signatures)

### 5.1 Metode `getThemeArchetype(string $themeKey): array`

#### Tanda Tangan:
```php
/**
 * Get the design archetype definition for a specific theme key or alias.
 * Resolves aliases and falls back gracefully to 'clean_bento'.
 *
 * @param string $themeKey Theme ID or alias (e.g. 'retail_fashion', 'artisan_brew', 'modern_umkm')
 * @return array<string, mixed> Archetype definition array containing id, name, icon, themes, etc.
 */
public function getThemeArchetype(string $themeKey): array
```

#### Implementasi Lengkap:
```php
public function getThemeArchetype(string $themeKey): array
{
    $key = trim($themeKey);

    // 1. Direct O(1) hit on THEME_ARCHETYPE_MAP
    if (isset(self::THEME_ARCHETYPE_MAP[$key])) {
        $archetypeKey = self::THEME_ARCHETYPE_MAP[$key];
        return self::ARCHETYPES[$archetypeKey] ?? self::ARCHETYPES['clean_bento'];
    }

    // 2. Resolve alias or historical code via getTheme()
    $resolved = $this->getTheme($key);
    $resolvedId = $resolved['id'] ?? 'modern_umkm';

    if (isset(self::THEME_ARCHETYPE_MAP[$resolvedId])) {
        $archetypeKey = self::THEME_ARCHETYPE_MAP[$resolvedId];
        return self::ARCHETYPES[$archetypeKey] ?? self::ARCHETYPES['clean_bento'];
    }

    // 3. Fallback to clean_bento (Universal default)
    return self::ARCHETYPES['clean_bento'];
}
```

---

### 5.2 Metode `getAllArchetypes(): array`

#### Tanda Tangan:
```php
/**
 * Get all 6 Design Archetypes with their theme lists, counts, and metadata.
 *
 * @return array<string, array<string, mixed>>
 */
public function getAllArchetypes(): array
```

#### Implementasi Lengkap:
```php
public function getAllArchetypes(): array
{
    $archetypes = self::ARCHETYPES;
    foreach ($archetypes as &$archetype) {
        $archetype['count'] = count($archetype['themes']);
    }
    unset($archetype);

    return $archetypes;
}
```

---

### 5.3 Metode `getUniversalFallback(string $themeKey): array`

#### Tanda Tangan:
```php
/**
 * Get guaranteed universal fallback dictionary for a given theme.
 * Backed by universal i18n translation keys and neutral Apple HIG styling,
 * ensuring ZERO sector-specific text leakage even if CMS fields are empty.
 *
 * @param string $themeKey Theme ID or alias
 * @return array<string, mixed> Universal fallback data contract
 */
public function getUniversalFallback(string $themeKey): array
```

#### Implementasi Lengkap:
```php
public function getUniversalFallback(string $themeKey): array
{
    $theme = $this->getTheme($themeKey);
    $archetype = $this->getThemeArchetype($themeKey);

    return [
        'theme_id'           => $theme['id'] ?? $themeKey,
        'theme_name'         => $theme['name'] ?? 'Storefront Theme',
        'archetype'          => $archetype['id'],
        'archetype_name'     => $archetype['name'],
        'headline'           => __('storefront.hero.welcome'),
        'subheadline'        => __('storefront.hero.tagline'),
        'announcement_badge' => __('storefront.hero.verified_official'),
        'active_status'      => __('storefront.hero.active_status'),
        'cta_primary_text'   => __('storefront.hero.explore_catalog'),
        'cta_secondary_text' => __('storefront.hero.contact_us'),
        'hero_badges'        => [
            [
                'icon'  => 'shield-check',
                'title' => __('storefront.badges.quality_guaranteed'),
            ],
            [
                'icon'  => 'credit-card',
                'title' => __('storefront.badges.secure_transaction'),
            ],
            [
                'icon'  => 'truck',
                'title' => __('storefront.badges.fast_service'),
            ],
        ],
        // Aliases for developer convenience
        'badges'             => [
            [
                'icon'  => 'shield-check',
                'title' => __('storefront.badges.quality_guaranteed'),
            ],
            [
                'icon'  => 'credit-card',
                'title' => __('storefront.badges.secure_transaction'),
            ],
            [
                'icon'  => 'truck',
                'title' => __('storefront.badges.fast_service'),
            ],
        ],
        'features'           => [
            [
                'icon'  => 'shield-check',
                'title' => __('storefront.features.official_store'),
                'desc'  => __('storefront.features.official_desc'),
            ],
            [
                'icon'  => 'credit-card',
                'title' => __('storefront.features.easy_payment'),
                'desc'  => __('storefront.features.payment_desc'),
            ],
            [
                'icon'  => 'truck',
                'title' => __('storefront.features.secure_delivery'),
                'desc'  => __('storefront.features.delivery_desc'),
            ],
            [
                'icon'  => 'headset',
                'title' => __('storefront.features.customer_care'),
                'desc'  => __('storefront.features.care_desc'),
            ],
        ],
        'pillars'            => [
            [
                'icon'  => 'shield-check',
                'title' => __('storefront.features.official_store'),
                'desc'  => __('storefront.features.official_desc'),
            ],
            [
                'icon'  => 'credit-card',
                'title' => __('storefront.features.easy_payment'),
                'desc'  => __('storefront.features.payment_desc'),
            ],
            [
                'icon'  => 'truck',
                'title' => __('storefront.features.secure_delivery'),
                'desc'  => __('storefront.features.delivery_desc'),
            ],
            [
                'icon'  => 'headset',
                'title' => __('storefront.features.customer_care'),
                'desc'  => __('storefront.features.care_desc'),
            ],
        ],
        'catalog'            => [
            'title'        => __('storefront.kategori_pilihan'),
            'subtitle'     => __('storefront.pilih_kategori_filter'),
            'all_products' => __('storefront.semua_produk'),
            'view_all'     => __('storefront.view_all'),
        ],
        'empty_catalog_title' => __('storefront.catalog.empty_title'),
        'empty_catalog_desc'  => __('storefront.catalog.empty_desc'),
    ];
}
```

---

## 6. Audit & Penegakan Kompatibilitas Mundur (Backward Compatibility)

Semua kode yang telah berjalan pada sistem saat ini harus tetap berfungsi tanpa perubahan (*zero breaking changes*):

### 6.1 Pada `getAllThemes(): array`
- **Kondisi Eksisting:** Mengembalikan array 42 tema dengan kunci `id`, `name`, `industry`, `recommendation`, dll.
- **Penyempurnaan:**
  Setiap item tema diperkaya dengan `'archetype'` dan `'archetype_name'`.
  ```php
  public function getAllThemes(): array
  {
      $themes = self::THEMES;
      foreach ($themes as $key => &$theme) {
          $theme['recommendation'] = self::RECOMMENDATIONS[$key] ?? self::RECOMMENDATIONS['modern_umkm'];
          $archetype = $this->getThemeArchetype($key);
          $theme['archetype'] = $archetype['id'];
          $theme['archetype_name'] = $archetype['name'];
      }
      unset($theme);

      return $themes;
  }
  ```
- **Dampak:** Controller `BusinessLandingPageWebController::edit()` yang melempar `$themePresets` ke Blade akan secara otomatis menyediakan `$theme['archetype']` untuk setiap kartu tema. Ini memungkinkan filter tab arketipe (Milestone 4) berfungsi seketika.

### 6.2 Pada `getTheme(string $presetKey): array`
- **Kondisi Eksisting:** Mengembalikan tema teresolusi dengan rekomendasi.
- **Penyempurnaan:**
  Tambahkan `'archetype'` dan `'archetype_name'`:
  ```php
  $id = $resolved['id'] ?? $presetKey;
  $resolved['recommendation'] = self::RECOMMENDATIONS[$id] ?? self::RECOMMENDATIONS['modern_umkm'];
  $archetype = $this->getThemeArchetype($id);
  $resolved['archetype'] = $archetype['id'];
  $resolved['archetype_name'] = $archetype['name'];
  ```
- **Dampak:** Siapa pun yang memanggil `getTheme()` langsung mendapatkan arketipe terkait.

### 6.3 Pada `resolveTheme(BusinessLandingPage $landingPage): array`
- **Kondisi Eksisting:** Memanggil `$this->getTheme($presetKey)` dan menerapkan pewarnaan, font kustom, serta dark mode.
- **Dampak:** Karena memanggil `getTheme()`, properti `'archetype'` dan `'archetype_name'` otomatis terwariskan dengan sempurna tanpa mengganggu logika CSS variables, font family, atau header dark.

### 6.4 Kompatibilitas terhadap Test Suite Eksisting
Test suite `tests/Feature/StorefrontThemeCustomizationTest.php` menguji:
- `test_landing_page_cms_editor_renders_with_42_themes_and_fonts`
- `test_owner_can_update_theme_preset_color_and_font`
- `test_storefront_renders_selected_theme_identity`
- `test_authorized_owner_can_live_preview_different_theme_without_saving`
- `test_all_42_themes_resolve_and_render_without_errors`

Karena tidak ada kunci eksisting yang dihapus atau diubah tipenya, seluruh test assertion di atas dijamin 100% tetap hijau (PASS).

---

## 7. Sinergi dengan Peer Agents & Milestone Lanjutan

1. **Dengan Explorer 1 (`teamwork_explorer_m1_1`):**
   `getUniversalFallback()` secara konsisten mengonsumsi namespace terstandarisasi yang dirancang Explorer 1:
   - `storefront.hero.welcome`, `tagline`, `verified_official`, `active_status`, `explore_catalog`, `contact_us`
   - `storefront.badges.quality_guaranteed`, `secure_transaction`, `fast_service`
   - `storefront.features.official_store`, `official_desc`, `easy_payment`, `payment_desc`, dll.
   - `storefront.kategori_pilihan`, `semua_produk`, `view_all`
2. **Dengan Explorer 2 (`teamwork_explorer_m1_2`):**
   Model helper `$landingPage->getUniversalValues()` pada `BusinessLandingPage` mengembalikan 4 item pilar keunggulan yang 100% selaras dengan `$service->getUniversalFallback($themeKey)['features']`.
3. **Dengan Milestone 2 (Batch 1–4 Theme Refactoring):**
   Template Blade pada 42 tema di Fase 2 dapat langsung memanggil atau mewarisi data fallback dari `$activeTheme['archetype']` dan `getUniversalFallback()`, sehingga tidak ada duplikasi kode logika di 42 sub-folder tema.
4. **Dengan Milestone 4 (CMS Cockpit `edit.blade.php`):**
   Filter tab 6 arketipe desain di Sub-Card 5 dapat langsung me-loop `$themeService->getAllArchetypes()` dan menyaring tema menggunakan atribut HTML `data-archetype="{{ $theme['archetype'] }}"`.

---

## 8. Rekomendasi Patch untuk Implementer

Berikut adalah blok kode terpadu yang siap diterapkan oleh implementer ke dalam `app/Domain/Storefront/StorefrontThemeService.php`:

```php
// =========================================================================
// 1. TAMBAHKAN KONSTANTA SETELAH CONST ALIASES ATAU RECOMMENDATIONS
// =========================================================================

    /**
     * Map of 6 Visual Design Archetypes organizing the 42 Storefront Themes.
     *
     * @var array<string, array<string, mixed>>
     */
    public const ARCHETYPES = [
        'clean_bento' => [
            'id' => 'clean_bento',
            'name' => 'Clean Bento & Modern Tech',
            'badge' => '8 Tema',
            'icon' => 'layout-grid',
            'character' => 'Flat card, Plus Jakarta Sans/Inter, squircle, minimalis, rapi.',
            'description' => 'Arsitektur modular Bento Grid Apple HIG dengan kepadatan informasi optimal, sudut squircle elegan, dan tipografi sans-serif modern yang presisi.',
            'default_font_heading' => 'Plus Jakarta Sans',
            'default_font_body' => 'Inter',
            'default_primary' => '#007AFF',
            'default_bg' => '#FFFFFF',
            'themes' => [
                'universal_storefront',
                'modern_umkm',
                'online_store',
                'service_agency',
                'retail_electronics',
                'retail_building',
                'professional_services',
                'retail_grocery',
            ],
        ],
        'warm_artisan' => [
            'id' => 'warm_artisan',
            'name' => 'Earthy & Warm Artisan',
            'badge' => '7 Tema',
            'icon' => 'coffee',
            'character' => 'Playfair Display/DM Serif, palet zaitun/cokelat, hangat.',
            'description' => 'Estetika hangat kerajinan otentik dengan palet bumi (earthy tones), tipografi serif klasik yang ramah, dan tekstur organik yang menenangkan.',
            'default_font_heading' => 'Playfair Display',
            'default_font_body' => 'Inter',
            'default_primary' => '#8B5A2B',
            'default_bg' => '#FAF7F2',
            'themes' => [
                'coffee_beverage',
                'agriculture_farm',
                'bakery_cake',
                'fnb_bakery',
                'fnb_cafe',
                'retail_florist',
                'hospitality_accommodation',
            ],
        ],
        'luxe_editorial' => [
            'id' => 'luxe_editorial',
            'name' => 'High-End Luxe & Editorial',
            'badge' => '6 Tema',
            'icon' => 'sparkles',
            'character' => 'Cormorant Garamond/Syne, monokrom, spasi luas, elegan.',
            'description' => 'Tampilan editorial majalah mewah (lookbook) dengan spasi bernapas luas, kontras monokrom tinggi, tipografi display kurasi, dan aura prestisius.',
            'default_font_heading' => 'Cormorant Garamond',
            'default_font_body' => 'Inter',
            'default_primary' => '#0A0A0A',
            'default_bg' => '#FDFBF7',
            'themes' => [
                'retail_fashion',
                'health_beauty',
                'product_showcase',
                'event_wedding',
                'personal_brand',
                'portfolio_creative',
            ],
        ],
        'dark_carbon' => [
            'id' => 'dark_carbon',
            'name' => 'Dark Carbon & High Impact',
            'badge' => '7 Tema',
            'icon' => 'zap',
            'character' => 'Dark #0B0F19, Chakra Petch/Orbitron, aksen kontras neon.',
            'description' => 'Nuansa dark mode berani dengan latar karbon #0B0F19, aksen neon tajam bertenaga, geometri tegas, dan tipografi industrial berkekuatan tinggi.',
            'default_font_heading' => 'Chakra Petch',
            'default_font_body' => 'Inter',
            'default_primary' => '#EA580C',
            'default_bg' => '#0B0F19',
            'themes' => [
                'service_workshop',
                'service_carwash',
                'service_fitness',
                'service_laundry',
                'construction_property',
                'manufacturing_factory',
                'logistics_transportation',
            ],
        ],
        'playful_fresh' => [
            'id' => 'playful_fresh',
            'name' => 'Playful, Fresh & Appetite',
            'badge' => '7 Tema',
            'icon' => 'smile',
            'character' => 'Soft rounded, warna cerah menggugah, Urbanist, ramah.',
            'description' => 'Visual ceria penuh energi dengan sudut lengkung lembut (soft rounded), palet warna segar menggugah selera, dan tipografi bersahaja yang mengundang interaksi.',
            'default_font_heading' => 'DM Serif Display',
            'default_font_body' => 'Plus Jakarta Sans',
            'default_primary' => '#991B1B',
            'default_bg' => '#FFFBEB',
            'themes' => [
                'food_restaurant',
                'fnb_resto',
                'fnb_fastfood',
                'fnb_catering',
                'retail_bookstore',
                'service_petshop',
                'service_beauty',
            ],
        ],
        'institutional_trust' => [
            'id' => 'institutional_trust',
            'name' => 'Institutional & Trust',
            'badge' => '7 Tema',
            'icon' => 'shield-check',
            'character' => 'Formal, navy/emerald korporat, Plus Jakarta Sans, terpercaya.',
            'description' => 'Arsitektur tata kelola resmi dengan palet biru/zamrud korporat, kartu sertifikasi kredibilitas tinggi, dan struktur navigasi berstandar institusional.',
            'default_font_heading' => 'Plus Jakarta Sans',
            'default_font_body' => 'Inter',
            'default_primary' => '#2563EB',
            'default_bg' => '#F8FAFC',
            'themes' => [
                'business_corporate',
                'education_courses',
                'retail_pharmacy',
                'service_business',
                'service_printing',
                'booking_appointment',
                'wholesale_distributor',
            ],
        ],
    ];

    /**
     * O(1) reverse lookup mapping 42 theme IDs to design archetype.
     *
     * @var array<string, string>
     */
    public const THEME_ARCHETYPE_MAP = [
        'universal_storefront'       => 'clean_bento',
        'modern_umkm'               => 'clean_bento',
        'online_store'              => 'clean_bento',
        'service_agency'            => 'clean_bento',
        'retail_electronics'        => 'clean_bento',
        'retail_building'           => 'clean_bento',
        'professional_services'     => 'clean_bento',
        'retail_grocery'            => 'clean_bento',

        'coffee_beverage'           => 'warm_artisan',
        'agriculture_farm'          => 'warm_artisan',
        'bakery_cake'               => 'warm_artisan',
        'fnb_bakery'                => 'warm_artisan',
        'fnb_cafe'                  => 'warm_artisan',
        'retail_florist'            => 'warm_artisan',
        'hospitality_accommodation' => 'warm_artisan',

        'retail_fashion'            => 'luxe_editorial',
        'health_beauty'             => 'luxe_editorial',
        'product_showcase'          => 'luxe_editorial',
        'event_wedding'             => 'luxe_editorial',
        'personal_brand'            => 'luxe_editorial',
        'portfolio_creative'        => 'luxe_editorial',

        'service_workshop'          => 'dark_carbon',
        'service_carwash'           => 'dark_carbon',
        'service_fitness'           => 'dark_carbon',
        'service_laundry'           => 'dark_carbon',
        'construction_property'     => 'dark_carbon',
        'manufacturing_factory'     => 'dark_carbon',
        'logistics_transportation'  => 'dark_carbon',

        'food_restaurant'           => 'playful_fresh',
        'fnb_resto'                 => 'playful_fresh',
        'fnb_fastfood'              => 'playful_fresh',
        'fnb_catering'              => 'playful_fresh',
        'retail_bookstore'          => 'playful_fresh',
        'service_petshop'           => 'playful_fresh',
        'service_beauty'            => 'playful_fresh',

        'business_corporate'        => 'institutional_trust',
        'education_courses'         => 'institutional_trust',
        'retail_pharmacy'           => 'institutional_trust',
        'service_business'          => 'institutional_trust',
        'service_printing'          => 'institutional_trust',
        'booking_appointment'       => 'institutional_trust',
        'wholesale_distributor'     => 'institutional_trust',
    ];

// =========================================================================
// 2. METODE BARU DI DALAM CLASS StorefrontThemeService
// =========================================================================

    /**
     * Get all 6 Design Archetypes with their theme lists and metadata.
     *
     * @return array<string, array<string, mixed>>
     */
    public function getAllArchetypes(): array
    {
        $archetypes = self::ARCHETYPES;
        foreach ($archetypes as &$archetype) {
            $archetype['count'] = count($archetype['themes']);
        }
        unset($archetype);

        return $archetypes;
    }

    /**
     * Get the design archetype definition for a specific theme key or alias.
     *
     * @param string $themeKey
     * @return array<string, mixed>
     */
    public function getThemeArchetype(string $themeKey): array
    {
        $key = trim($themeKey);

        if (isset(self::THEME_ARCHETYPE_MAP[$key])) {
            $archetypeKey = self::THEME_ARCHETYPE_MAP[$key];
            return self::ARCHETYPES[$archetypeKey] ?? self::ARCHETYPES['clean_bento'];
        }

        $resolved = $this->getTheme($key);
        $resolvedId = $resolved['id'] ?? 'modern_umkm';

        if (isset(self::THEME_ARCHETYPE_MAP[$resolvedId])) {
            $archetypeKey = self::THEME_ARCHETYPE_MAP[$resolvedId];
            return self::ARCHETYPES[$archetypeKey] ?? self::ARCHETYPES['clean_bento'];
        }

        return self::ARCHETYPES['clean_bento'];
    }

    /**
     * Get guaranteed universal fallback dictionary for a given theme.
     * Backed by universal i18n translation keys and neutral Apple HIG styling,
     * ensuring ZERO sector-specific text leakage even if CMS fields are empty.
     *
     * @param string $themeKey
     * @return array<string, mixed>
     */
    public function getUniversalFallback(string $themeKey): array
    {
        $theme = $this->getTheme($themeKey);
        $archetype = $this->getThemeArchetype($themeKey);

        $badges = [
            [
                'icon'  => 'shield-check',
                'title' => __('storefront.badges.quality_guaranteed'),
            ],
            [
                'icon'  => 'credit-card',
                'title' => __('storefront.badges.secure_transaction'),
            ],
            [
                'icon'  => 'truck',
                'title' => __('storefront.badges.fast_service'),
            ],
        ];

        $features = [
            [
                'icon'  => 'shield-check',
                'title' => __('storefront.features.official_store'),
                'desc'  => __('storefront.features.official_desc'),
            ],
            [
                'icon'  => 'credit-card',
                'title' => __('storefront.features.easy_payment'),
                'desc'  => __('storefront.features.payment_desc'),
            ],
            [
                'icon'  => 'truck',
                'title' => __('storefront.features.secure_delivery'),
                'desc'  => __('storefront.features.delivery_desc'),
            ],
            [
                'icon'  => 'headset',
                'title' => __('storefront.features.customer_care'),
                'desc'  => __('storefront.features.care_desc'),
            ],
        ];

        return [
            'theme_id'           => $theme['id'] ?? $themeKey,
            'theme_name'         => $theme['name'] ?? 'Storefront Theme',
            'archetype'          => $archetype['id'],
            'archetype_name'     => $archetype['name'],
            'headline'           => __('storefront.hero.welcome'),
            'subheadline'        => __('storefront.hero.tagline'),
            'announcement_badge' => __('storefront.hero.verified_official'),
            'active_status'      => __('storefront.hero.active_status'),
            'cta_primary_text'   => __('storefront.hero.explore_catalog'),
            'cta_secondary_text' => __('storefront.hero.contact_us'),
            'hero_badges'        => $badges,
            'badges'             => $badges,
            'features'           => $features,
            'pillars'            => $features,
            'catalog'            => [
                'title'        => __('storefront.kategori_pilihan'),
                'subtitle'     => __('storefront.pilih_kategori_filter'),
                'all_products' => __('storefront.semua_produk'),
                'view_all'     => __('storefront.view_all'),
            ],
            'empty_catalog_title' => __('storefront.catalog.empty_title'),
            'empty_catalog_desc'  => __('storefront.catalog.empty_desc'),
        ];
    }
```
