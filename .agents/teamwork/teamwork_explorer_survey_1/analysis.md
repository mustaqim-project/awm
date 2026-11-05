# Analysis: Storefront Themes & CMS Landing Page Backend Architecture (Phase 1 Blueprint)

## Executive Summary
This investigation audits the backend architecture supporting COOCA's storefront themes and CMS landing page, evaluating current implementations against the universal design blueprint defined in `STANDARISASI_DAN_IMPLEMENTASI_TEMA_UNIVERSAL.md`. 

The core objective of Phase 1 is to supply helper methods, archetype classifications, fallback defaults, and universal i18n translation strings so that Blade templates across all 42 themes can render without repetitive fallback logic, sector-specific hardcoded text, or missing visibility checks.

---

## 1. File Path Mapping & Namespace Realignment

A preliminary check of the filesystem revealed differences between the prompt's nominal paths and actual project locations:

| Nominal Path in Briefing | Actual Existing Path | Namespace / Class |
|---|---|---|
| `app/Models/BusinessLandingPage.php` | `app/Models/BusinessLandingPage.php` | `App\Models\BusinessLandingPage` |
| `app/Services/StorefrontThemeService.php` | `app/Domain/Storefront/StorefrontThemeService.php` | `App\Domain\Storefront\StorefrontThemeService` |
| `app/Http/Controllers/Storefront/StorefrontController.php` | `app/Http/Controllers/Web/Storefront/PublicStorefrontController.php` | `App\Http\Controllers\Web\Storefront\PublicStorefrontController` |
| (CMS Controller) | `app/Http/Controllers/Web/BusinessLandingPageWebController.php` | `App\Http\Controllers\Web\BusinessLandingPageWebController` |
| `lang/id/storefront.php` | `lang/id/storefront.php` | PHP translation array (`id`) |
| `lang/en/storefront.php` | `lang/en/storefront.php` | PHP translation array (`en`) |

---

## 2. Analysis of `app/Models/BusinessLandingPage.php`

### 2.1 Current Implementation
`BusinessLandingPage` represents the landing page CMS configuration for each business tenant.
- **Table**: `business_landing_pages`
- **Fillable fields**: Includes `section_visibility`, `values`, `active_pages`, `custom_labels`, `operational_hours`, `testimonials`, `faqs`, `stats`, `social_links`, `custom_services`, `gallery_images`, etc.
- **Casts**:
  ```php
  'values'             => 'array',
  'operational_hours'  => 'array',
  'custom_services'    => 'array',
  'gallery_images'     => 'array',
  'testimonials'       => 'array',
  'faqs'               => 'array',
  'stats'              => 'array',
  'social_links'       => 'array',
  'section_visibility' => 'array',
  'active_pages'       => 'array',
  'custom_labels'      => 'array',
  ```
- **Existing Helper Methods**:
  1. `getActivePages(): array` (lines 108–121): Merges page visibility defaults (`home`, `catalog`, `about`, `reservation`, `contact`, `blog`, `order_tracking`).
  2. `isPageActive(string $page): bool` (lines 126–130): Checks if a standalone sub-page is active.
  3. `getNavLabel(string $page, string $default): string` (lines 135–139): Returns custom label from `custom_labels` array.
  4. `getThemePreset(): string` (lines 144–163): Detects theme from `theme_preset`, `detectThemeKeyForBusiness()`, or defaults to `'modern_umkm'`.
  5. `getWhatsAppUrl(?string $serviceName = null): string` (lines 173–192): Generates formatted WhatsApp link with optional service pre-fill.
  6. `getNormalizedOperationalHours(): array` (lines 198–252): Normalizes day names and opening hours.
  7. `isPopupActive(): bool` (lines 333–348): Evaluates schedule and pop-up enablement.
  8. Accessor attributes for normalizing URLs: `hero_image_url`, `logo_url`, `about_image_url`, `og_image_url`, `gallery_images`, `custom_services`, `popup_image_url`.

### 2.2 What is Missing for Phase 1
1. **Helper Method `isSectionVisible(string $section): bool` is completely absent**:
   Although `section_visibility` exists as an array column and is populated by the CMS via JSON input `section_visibility_json`, there is no model-level query method.
   - Currently, Blade views either do not check section visibility, or manually inspect the array without reliable default fallbacks.
   - Required method signature and logic:
     ```php
     /**
      * Determine whether a specific storefront homepage section is visible.
      */
     public function isSectionVisible(string $section): bool
     {
         $defaults = [
             'hero'              => true,
             'features_banner'   => true,
             'category_chips'    => true,
             'products'          => true,
             'services'          => true,
             'gallery'           => true,
             'testimonials'      => true,
             'faq'               => true,
             'stats'             => true,
             'about'             => true,
             'contact'           => true,
             'footer'            => true,
             'footer_brand'      => true,
             'footer_navigation' => true,
             'footer_services'   => true,
             'footer_contact'    => true,
         ];

         $visibility = array_merge($defaults, (array) ($this->section_visibility ?? []));

         return (bool) ($visibility[$section] ?? true);
     }
     ```
2. **Helper Method for Standardized Values / Badges**:
   The blueprint specifies that both the 3 hero badges and the 4 features banner cards consume `$landingPage->values`.
   Currently, `$landingPage->values` can be null, empty array, or contain items with differing key names (`desc` in `IndustryPresets.php` vs `description` in `edit.blade.php`).
   Providing a helper method or accessor on `BusinessLandingPage` (e.g. `getUniversalValues(): array` or `getNormalizedValues(): array`) will ensure consistent output structure:
   `[['icon' => string, 'title' => string, 'desc' => string], ...]`.

---

## 3. Analysis of `app/Domain/Storefront/StorefrontThemeService.php`

### 3.1 Current Implementation
`StorefrontThemeService` is a final service handling cataloguing, discovery, and runtime configuration for all 42 distinct storefront themes.
- **Constants**:
  - `THEMES` (lines 18–900): Complete array of 42 themes containing `id`, `bundle_dir`, `name`, `industry`, `font_heading`, `font_body`, `google_fonts`, `primary_color`, `accent_color`, `bg_color`, `card_bg`, `header_dark`, `hero_style`, `badge_bg`, `badge_text`, `border_radius`, `description`.
  - `TEMPLATE_CODE_MAP` (lines 907–1121): Maps business template codes to theme IDs.
  - `CATEGORY_MAP` (lines 1128–1174): Maps industry categories to theme IDs.
  - `ALIASES` (lines 1181–1226): Maps legacy theme aliases.
  - `CURATED_FONTS` (lines 1233–1252): Curated font definitions.
  - `RECOMMENDATIONS` (lines 1259–1554): Industry-specific marketing copy recommendations for headline, subheadline, badges, and trust pillars.
- **Existing Methods**:
  - `getAllThemes(): array` (lines 1561–1570)
  - `hasTheme(string $presetKey): bool` (lines 1575–1580)
  - `getCuratedFonts(): array` (lines 1587–1590)
  - `getRecommendation(string $presetKey): array` (lines 1598–1604)
  - `getTheme(string $presetKey): array` (lines 1612–1643)
  - `detectThemeKeyForBusiness(?Business $business): string` (lines 1649–1679)
  - `resolveTheme(BusinessLandingPage $landingPage): array` (lines 1687–1716)

### 3.2 What is Missing for Phase 1
In `STANDARISASI_DAN_IMPLEMENTASI_TEMA_UNIVERSAL.md` (Fase 1, Tindakan 2):
1. **Method `getThemeArchetype(string $themeKey): array`**:
   The blueprint establishes 6 Universal Design Archetypes:
   - `clean_bento` (8 themes): `universal_storefront`, `modern_umkm`, `online_store`, `service_agency`, `retail_electronics`, `retail_building`, `professional_services`, `retail_grocery`.
   - `warm_artisan` (7 themes): `coffee_beverage`, `agriculture_farm`, `bakery_cake`, `fnb_bakery`, `fnb_cafe`, `retail_florist`, `hospitality_accommodation`.
   - `luxe_editorial` (6 themes): `retail_fashion`, `health_beauty`, `product_showcase`, `event_wedding`, `personal_brand`, `portfolio_creative`.
   - `dark_carbon` (7 themes): `service_workshop`, `service_carwash`, `service_fitness`, `service_laundry`, `construction_property`, `manufacturing_factory`, `logistics_transportation`.
   - `playful_fresh` (7 themes): `food_restaurant`, `fnb_resto`, `fnb_fastfood`, `fnb_catering`, `retail_bookstore`, `service_petshop`, `service_beauty`.
   - `institutional_trust` (7 themes): `business_corporate`, `education_courses`, `retail_pharmacy`, `service_business`, `service_printing`, `booking_appointment`, `wholesale_distributor`.
   Currently, neither the archetype mapping constant nor `getThemeArchetype(string $themeKey)` exists in `StorefrontThemeService`.
2. **Method `getAllArchetypes(): array`**:
   Required by Phase 4 for tab filtering in `edit.blade.php` (Sub-Card 5) to display the 6 archetype tabs:
   *Semua, Modern Bento, Warm Artisan, Luxe Editorial, Dark Carbon, Playful Fresh, Institutional*.
3. **Method `getUniversalFallback(string $themeKey): array`**:
   Required to supply universal, sector-agnostic copy fallbacks when a merchant has empty headline/subheadline/badges/values. This replaces hardcoded sector strings like "Tune-up Mesin" or "Onderdil 100% Asli" with neutral business language:
   - `headline`: `$business->name` or "Toko Resmi & Layanan Terpercaya"
   - `subheadline`: `$business->description` or `__('storefront.hero.tagline')`
   - `announcement_badge`: `__('storefront.hero.verified_official')`
   - `cta_primary_text`: `__('storefront.hero.explore_catalog')`
   - `cta_secondary_text`: `__('storefront.hero.contact_us')`
   - `badges`: Universal badges referencing `storefront.badges.*`
   - `features`: Universal 4 pillars referencing `storefront.features.*`

---

## 4. Analysis of Controller Integration (`PublicStorefrontController` & `BusinessLandingPageWebController`)

### 4.1 Public Storefront Execution Flow (`PublicStorefrontController.php`)
- `resolveContext(string $slug, ?string $requiredPage = null)` (lines 403–533):
  1. Finds `Business` by slug or name slug.
  2. Sets locale (`id` or `en`) from query parameter `?lang=` or session.
  3. Loads or creates `BusinessLandingPage`.
  4. Resolves theme via `$this->themeService->resolveTheme($landingPage)`.
  5. Supports authorized live preview via `?preview=1&preview_theme=...&preview_color=...`.
  6. Passes `$landingPage`, `$business`, `$theme`, `$storeSetting`, `$availableBatchDates` to view context.
- In `home(string $slug)` (lines 38–90):
  Renders `public.storefront.home` with `$featuredProducts`, `$services`, `$productCategories`, `$recentArticles`, `$posTables`.
- **Observation on `public.storefront.home.blade.php`**:
  - Lines 50–56: Hero is included without checking `isSectionVisible('hero')`.
  - Lines 61–65: Features banner is included without checking `isSectionVisible('features_banner')`.
  - Lines 70–76: Category chips is included without checking `isSectionVisible('category_chips')`.
  - Lines 121–171: Services section checks `$services->isNotEmpty()`, but ignores `isSectionVisible('services')`.
  - Lines 237–302: About section checks `$landingPage->isPageActive('about')`, but ignores `isSectionVisible('about')`.
  This validates the necessity of Phase 3 task 3.1.

### 4.2 CMS Cockpit Execution Flow (`BusinessLandingPageWebController.php` & `edit.blade.php`)
- In `edit()` (lines 28–106):
  Loads `$landingPage` and passes `$themePresets = $themeService->getAllThemes()` and `$curatedFonts`.
- In `update(Request $request)` (lines 111–320):
  Accepts `section_visibility_json` and `values_json`, decodes them, and saves them into the Eloquent model attributes `$landingPage->section_visibility` and `$landingPage->values`.
- In `edit.blade.php`:
  - Lines 2575–2592 define `$sectionVisibilityDefaults`:
    ```php
    $sectionVisibilityDefaults = array_merge(
        [
            'hero' => true,
            'about' => true,
            'products' => true,
            'services' => true,
            'gallery' => true,
            'testimonials' => true,
            'faq' => true,
            'contact' => true,
            'footer' => true,
            'footer_brand' => true,
            'footer_navigation' => true,
            'footer_services' => true,
            'footer_contact' => true,
        ],
        $landingPage->section_visibility ?? [],
    );
    ```
  - `features_banner` and `category_chips` are currently **absent** from `$sectionVisibilityDefaults` and lack checkbox controls in `edit.blade.php`.
  - `values` array binds `val.title`, `val.icon`, and `val.description`.

---

## 5. Analysis of Language Translation Files (`lang/id/storefront.php` & `lang/en/storefront.php`)

### 5.1 Existing Keys
Both files contain comprehensive translations for navigation, checkout, tracking, and reservation:
- `storefront.nav.*`
- `storefront.hero.explore_catalog` ('Jelajahi Katalog' / 'Explore Catalog')
- `storefront.hero.contact_us` ('Hubungi Kami' / 'Contact Us')
- `storefront.hero.verified_official` ('Toko Resmi Terverifikasi' / 'Verified Official Store')
- `storefront.hero.tagline` ('Belanja aneka produk dan layanan berkualitas...')
- `storefront.hero.active_status` ('Aktif Beroperasi' / 'Active & Operating')
- `storefront.kategori_pilihan` ('Kategori Pilihan' / 'Category Selection')
- `storefront.semua_produk` ('Semua Produk' / 'All Products')
- `storefront.view_all` ('Lihat Semua' / 'View All')

### 5.2 Missing Keys for Phase 1 Universal Contracts
The Universal Blade Contracts defined in Section 3 of the blueprint require the following translation keys that are currently missing in both `lang/id/storefront.php` and `lang/en/storefront.php`:

1. **`storefront.badges.*`**:
   | Translation Key | Indonesian (`id`) | English (`en`) |
   |---|---|---|
   | `badges.quality_guaranteed` | Kualitas Terjamin | Quality Guaranteed |
   | `badges.secure_transaction` | Transaksi Aman | Secure Transaction |
   | `badges.fast_service` | Layanan Cepat & Responsif | Fast & Responsive Service |
   | `badges.official_verified` | Toko Resmi Terverifikasi | Verified Official Store |
   | `badges.best_price` | Harga Terbaik & Transparan | Best & Transparent Price |
   | `badges.satisfaction_guaranteed` | Garansi Kepuasan Pelanggan | Satisfaction Guaranteed |

2. **`storefront.features.*`**:
   | Translation Key | Indonesian (`id`) | English (`en`) |
   |---|---|---|
   | `features.official_store` | Toko Resmi Terverifikasi | Verified Official Store |
   | `features.official_desc` | Jaminan produk berkualitas langsung dari pemilik usaha terpercaya. | Guaranteed quality products directly from trusted business owner. |
   | `features.easy_payment` | Pembayaran Mudah | Easy Payment |
   | `features.payment_desc` | Mendukung QRIS realtime dan transfer bank yang aman. | Realtime QRIS support and secure bank transfers. |
   | `features.secure_delivery` | Pengiriman Cepat & Aman | Fast & Secure Delivery |
   | `features.delivery_desc` | Pesanan dikemas rapi dan dikirim dengan kurir terpercaya. | Orders neatly packed and delivered with trusted couriers. |
   | `features.customer_care` | Layanan Pelanggan | Customer Care |
   | `features.care_desc` | Konsultasi ramah dan bantuan pemesanan langsung via WhatsApp. | Friendly consultation and direct ordering assistance via WhatsApp. |

3. **`storefront.catalog.*`**:
   | Translation Key | Indonesian (`id`) | English (`en`) |
   |---|---|---|
   | `catalog.pilih_kategori_filter` | Pilih kategori untuk memfilter etalase | Select a category to filter catalog |

4. **`storefront.empty.*`**:
   | Translation Key | Indonesian (`id`) | English (`en`) |
   |---|---|---|
   | `empty.no_products` | Belum ada produk yang ditampilkan saat ini. | No products available at the moment. |
   | `empty.no_services` | Belum ada layanan yang ditampilkan saat ini. | No services available at the moment. |
   | `empty.no_categories` | Belum ada kategori yang dibuat. | No categories created yet. |

---

## 6. Actionable Implementation Specifications for Phase 1

To achieve full compliance with Phase 1 of the blueprint, the following changes are specified:

### Task 1.1: Language Dictionaries Update
- File targets:
  - `lang/id/storefront.php`
  - `lang/en/storefront.php`
- Add nested groups:
  - `'badges' => [...]`
  - `'features' => [...]`
  - `'catalog.pilih_kategori_filter'` (and top-level alias `'pilih_kategori_filter'` for backward compatibility)
  - `'empty' => [...]`

### Task 1.2: Model Extension on `BusinessLandingPage.php`
- File target:
  - `app/Models/BusinessLandingPage.php`
- Add method `isSectionVisible(string $section): bool`:
  Implements complete default visibility mapping (`hero`, `features_banner`, `category_chips`, `products`, `services`, `gallery`, `testimonials`, `faq`, `stats`, `about`, `contact`, `footer`, `footer_brand`, `footer_navigation`, `footer_services`, `footer_contact`).
- Add method `getUniversalValues(): array`:
  Safely extracts `$this->values`, normalizing item keys (`icon`, `title`, `desc`), and filling missing slots (up to 4 items) with universal defaults derived from `__('storefront.features.*')`.

### Task 1.3: Service Extension on `StorefrontThemeService.php`
- File target:
  - `app/Domain/Storefront/StorefrontThemeService.php`
- Add constants:
  - `ARCHETYPES`: Definitive mapping of 6 archetypes to theme IDs and metadata.
  - `THEME_ARCHETYPE_MAP`: Reverse lookup from theme ID to archetype key.
- Add public methods:
  - `getThemeArchetype(string $themeKey): array`
  - `getAllArchetypes(): array`
  - `getUniversalFallback(string $themeKey): array`
