# Changes Made - Milestone 1: Backend & Service Normalization

**Worker**: `teamwork_worker_m1_1`  
**Date**: 2026-11-05  
**Scope**: Milestone 1 (Backend & Service Normalization)

---

## 1. Summary of Modifications

### File 1: `lang/id/storefront.php`
- **Added root-level `'badges'` array**:
  - `quality_guaranteed` => `'Kualitas Terjamin'`
  - `secure_transaction` => `'Transaksi Aman'`
  - `fast_service` => `'Layanan Cepat'`
  - `verified_official` => `'Toko Resmi Terverifikasi'`
  - `active_status` => `'Aktif Beroperasi'`
- **Added root-level `'features'` array**:
  - `official_store` => `'Outlet Resmi Terverifikasi'`
  - `official_desc` => `'Produk dan layanan 100% original langsung dari merchant resmi terpercaya.'`
  - `easy_payment` => `'Pembayaran Mudah & Aman'`
  - `payment_desc` => `'Mendukung QRIS instan, transfer bank, e-wallet, serta metode pembayaran aman lainnya.'`
  - `secure_delivery` => `'Pengiriman Cepat & Aman'`
  - `delivery_desc` => `'Layanan pengantaran andal dengan nomor resi dan pelacakan status pesanan real-time.'`
  - `customer_care` => `'Layanan Pelanggan Siaga'`
  - `care_desc` => `'Konsultasi ramah dan respon cepat langsung bersama tim kami melalui WhatsApp resmi.'`
- **Updated `'catalog'` array**:
  - Added `'pilih_kategori_filter'` => `'Pilih kategori untuk memfilter etalase belanja'`
  - Added `'semua_produk'` => `'Semua Produk'`
  - Added `'kategori_pilihan'` => `'Kategori Pilihan'`
- **Added root-level `'empty'` dictionary** supporting both flat keys and nested dot notation for catalog, products, and cart:
  - `catalog_title`, `catalog_desc`, `catalog_action`
  - `products_title`, `products_desc`, `products_action`
  - `cart_title`, `cart_desc`, `cart_action`
  - Nested: `catalog.title`, `products.title`, `cart.title`, etc.
- **Added root-level alias**:
  - `'pilih_kategori_filter'` => `'Pilih kategori untuk memfilter etalase belanja'`

---

### File 2: `lang/en/storefront.php`
- **Added root-level `'badges'` array**:
  - `quality_guaranteed` => `'Quality Guaranteed'`
  - `secure_transaction` => `'Secure Transaction'`
  - `fast_service` => `'Fast Service'`
  - `verified_official` => `'Verified Official Store'`
  - `active_status` => `'Operating Actively'`
- **Added root-level `'features'` array**:
  - `official_store` => `'Verified Official Store'`
  - `official_desc` => `'100% authentic products and dependable services directly from verified merchants.'`
  - `easy_payment` => `'Seamless & Secure Payment'`
  - `payment_desc` => `'Supports instant QRIS, bank transfers, e-wallets, and trusted payment methods.'`
  - `secure_delivery` => `'Fast & Reliable Delivery'`
  - `delivery_desc` => `'Dependable courier and logistics services with automated real-time order tracking.'`
  - `customer_care` => `'Dedicated Customer Support'`
  - `care_desc` => `'Friendly assistance and prompt responses directly from our team via official WhatsApp.'`
- **Updated `'catalog'` array**:
  - Added `'pilih_kategori_filter'` => `'Select a category to filter the store catalog'`
  - Added `'semua_produk'` => `'All Products'`
  - Added `'kategori_pilihan'` => `'Featured Categories'`
- **Added root-level `'empty'` dictionary** supporting both flat keys and nested dot notation for catalog, products, and cart.
- **Added root-level alias**:
  - `'pilih_kategori_filter'` => `'Select a category to filter the store catalog'`

---

### File 3: `app/Models/BusinessLandingPage.php`
- **Added Constant `DEFAULT_SECTION_VISIBILITY`**:
  - Contains all 12 core sections (`hero`, `features_banner`, `category_chips`, `products`, `services`, `gallery`, `testimonials`, `faq`, `stats`, `about`, `contact`, `footer`), each defaulting to `true`.
- **Implemented `isSectionVisible(string $section, bool $default = true): bool`**:
  - Transparently decodes JSON strings or reads arrays.
  - Checks direct key match.
  - Checks legacy aliases:
    - `products` <-> `featured_products`
    - `category_chips` <-> `catalog_filter`
    - `faq` <-> `faqs`
    - `testimonials` <-> `reviews`
    - `about` <-> `about_story`
  - Honors caller-provided override if `func_num_args() > 1`, else falls back to `DEFAULT_SECTION_VISIBILITY`.
- **Implemented `getSectionVisibility(): array`**:
  - Merges `DEFAULT_SECTION_VISIBILITY` with stored `section_visibility`.
- **Implemented `getUniversalValues(): array`**:
  - Filters numeric keys to ignore legacy metadata (e.g. `stats`).
  - Strict loop guaranteeing exactly 4 items (index 0 to 3).
  - Reconciles key differences between presets (`desc`) and Alpine CMS inputs (`description`), returning both `'desc'` and `'description'` with the exact same value.
- **Implemented `getDefaultUniversalValues(): array`**:
  - Provides default 4 pillars with i18n translation resolution (`storefront.features.*`) and graceful fallback strings.

---

### File 4: `app/Domain/Storefront/StorefrontThemeService.php`
- **Added Constant `ARCHETYPES`**:
  - Organizes the 42 themes across the 6 Design Archetypes:
    1. `clean_bento` (8 themes)
    2. `warm_artisan` (7 themes)
    3. `luxe_editorial` (6 themes)
    4. `dark_carbon` (7 themes)
    5. `playful_fresh` (7 themes)
    6. `institutional_trust` (7 themes)
- **Added Constant `THEME_ARCHETYPE_MAP`**:
  - Provides O(1) reverse lookup from any of the 42 theme IDs to their archetype key.
- **Implemented `getThemeArchetype(string $themeKey): array`**:
  - Direct O(1) lookup on `THEME_ARCHETYPE_MAP`.
  - Fallback checks on `ALIASES`, `TEMPLATE_CODE_MAP`, and `THEMES` bundle_dir.
  - Universal fallback to `clean_bento`.
- **Implemented `getAllArchetypes(): array`**:
  - Returns all 6 archetypes with precomputed `'count'` of themes.
- **Implemented `getUniversalFallback(string $themeKey): array`**:
  - Returns standardized universal content contract with zero sector leakage:
    - `headline`, `subheadline`, `announcement_badge`, `active_status`, `cta_primary_text`, `cta_secondary_text`
    - `hero_badges` (3 items) & `badges` alias
    - `features` (4 items) & `pillars` alias (including both `desc` and `description`)
    - `catalog` strings (`kategori_pilihan`, `pilih_kategori_filter`, `semua_produk`, `view_all`)
    - `empty_catalog_title` and `empty_catalog_desc`
- **Enriched `getAllThemes()`**:
  - Injects `'archetype'` and `'archetype_name'` into every theme array.
- **Enriched `getTheme()`**:
  - Injects `'archetype'` and `'archetype_name'` into resolved theme array.
- **Enriched `resolveTheme()`**:
  - Inherits `'archetype'` and `'archetype_name'` transparently through `getTheme()`.
