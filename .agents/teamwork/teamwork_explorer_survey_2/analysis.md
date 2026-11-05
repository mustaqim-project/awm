# Comprehensive Audit & Codebase Survey: 42 Storefront Themes & Universal Blade Architecture

**Survey Date**: 2026-11-05  
**Auditor**: Teamwork Explorer (Survey Agent 2)  
**Target Repository**: `cooca_core`  
**Reference SSOT**: `STANDARISASI_DAN_IMPLEMENTASI_TEMA_UNIVERSAL.md`  

---

## 1. Executive Summary

This investigation conducted an exhaustive, code-level inspection of the storefront rendering pipeline in COOCA ERP, encompassing:
1. `resources/views/public/storefront/layouts/app.blade.php` (Core Shell)
2. `resources/views/public/storefront/home.blade.php` (Storefront Landing Controller View)
3. All **42 theme directories** under `resources/views/public/storefront/themes/` (totaling over 250 Blade partials)
4. Representative samples from each of the **6 Design Archetypes**:
   - `universal_storefront` (Clean Bento & Modern Tech)
   - `coffee_beverage` (Earthy & Warm Artisan)
   - `retail_fashion` (High-End Luxe & Editorial)
   - `service_workshop` (Dark Carbon & High Impact)
   - `food_restaurant` (Playful, Fresh & Appetite)
   - `business_corporate` (Institutional & Trust)
5. Supporting domain services, models, and localization files:
   - `app/Models/BusinessLandingPage.php`
   - `app/Domain/Storefront/StorefrontThemeService.php`
   - `lang/id/storefront.php` & `lang/en/storefront.php`
   - `resources/views/app/landing_page/edit.blade.php` (CMS Backoffice Cockpit)

### Key Verdict
Currently, **0 out of 42 themes** comply with the Universal Blade Contracts defined in §3.1, §3.2, and §3.3 of the blueprint:
- **Zero themes** read the merchant's configured `$landingPage->values` JSON for dynamic hero badges or the 4 feature/trust pillars.
- **22 themes** hardcode external Unsplash stock photos directly in `hero.blade.php` (e.g. car mechanics, women's dresses, coffee cups, hamburgers, salads), while the remaining 20 themes hardcode industry-locked text or icons into their fallback graphic containers.
- **42 out of 42 themes** contain rigid hardcoded sector text (e.g. *"Tune-up Mesin"*, *"Petik Segar Subuh"*, *"Resep Warisan Nusantara"*, *"Jahitan Rapi & Fitting"*, *"Sewa Kamar"*, *"Bongkar Pasang"*).
- Multiple themes suffer from severe hardcoded hex color proliferation (e.g., `coffee_beverage` has 95 hardcoded hex codes, `fnb_cafe` has 133, and automotive/fashion themes have 21–27 hex codes each), bypassing the dynamic CSS variables (`--theme-primary`, `--theme-accent`, etc.).
- `home.blade.php` renders `features_banner` and `category_chips` unconditionally without checking `$landingPage->isSectionVisible()`.
- The `BusinessLandingPage` model lacks the `isSectionVisible()` helper method.
- The localization dictionaries (`lang/id/storefront.php` and `lang/en/storefront.php`) lack universal keys for `badges` and `features`.

---

## 2. Core Layout & Home View Audit

### 2.1 `resources/views/public/storefront/layouts/app.blade.php`

#### Positive Implementations:
- **CSS Custom Properties**: `:root` properly defines `--theme-primary`, `--theme-accent`, `--theme-bg`, `--theme-card`, `--theme-radius`, `--font-heading`, and `--font-body` (lines 131–139).
- **Tailwind Extension**: Dynamically mounts `theme.primary`, `theme.accent`, `theme.bg`, `theme.card` to the active theme palette.
- **Dark Mode Support**: Properly handles `html.dark` switching and syncs with `localStorage` (lines 25–48).
- **Apple HIG / PWA Headers**: Includes `viewport-fit=cover`, mobile web app meta tags, and loads Google Fonts dynamically per theme (lines 78–80).

#### Violations & Deficiencies:
1. **Hardcoded Hex Colors in Form Controls & Banners**:
   - Line 182: `html.dark select option:checked { background-color: #007AFF !important; }` &mdash; hardcoded iOS system blue instead of `var(--theme-primary)`.
   - Line 223: `.dark .theme-surface { background-color: #181E2A; }` &mdash; hardcoded hex instead of `var(--theme-card)`.
   - Line 265: Sticky draft preview banner uses hardcoded `bg-[#FF9500]`.
2. **Hardcoded Fallbacks in `<meta name="theme-color">`**:
   - Line 20: `content="{{ $initialDarkMode ? '#0B0F19' : ($activeTheme['bg_color'] ?? '#FAF7F2') }}"` &mdash; hardcodes `#FAF7F2` if `bg_color` is absent.
   - Line 41: JS toggle hardcodes `'#0B0F19'` and `'#FAF7F2'`.

---

### 2.2 `resources/views/public/storefront/home.blade.php`

#### Violations & Deficiencies:
1. **Absence of Section Visibility Enforcement**:
   - **Features Banner** (lines 61–65):
     ```blade
     @if (view()->exists("public.storefront.themes.{$bundleDir}.features_banner"))
         @include("public.storefront.themes.{$bundleDir}.features_banner")
     ...
     @endif
     ```
     *Defect*: Renders whenever the Blade file exists. Does **not** check `@if ($landingPage->isSectionVisible('features_banner'))`.
   - **Category Shortcut Chips** (lines 70–76):
     *Defect*: Does **not** check `@if ($landingPage->isSectionVisible('category_chips'))`.
   - **Featured Products** (line 81): Only checks `@if ($featuredProducts->isNotEmpty())`, ignores `isSectionVisible('products')`.
   - **Services Section** (line 121): Only checks `@if ($services->isNotEmpty())`, ignores `isSectionVisible('services')`.
   - **About Section** (line 237): Only checks `@if ($landingPage->isPageActive('about') || !empty($normalizedHours))`, ignores `isSectionVisible('about')`.
2. **Hardcoded Sector Conditionals**:
   - Line 14: `$isArtisanBrew = in_array($themePreset, ['coffee_beverage', 'artisan_brew'], true);`
   - Lines 90 & 96:
     ```blade
     {{ $isArtisanBrew ? 'Kopi Pilihan & Menu Favorit' : 'Produk Terlaris & Rekomendasi' }}
     <span>{{ $isArtisanBrew ? 'Lihat Semua Menu' : 'Buka Katalog Lengkap' }}</span>
     ```
     *Defect*: Rigid sector branching inside universal controller view.
   - Line 176: `@if ($bundleDir === 'fnb_cafe' && view()->exists('public.storefront.themes.fnb_cafe.qr_dinein'))` &mdash; hardcoded industry branch.
3. **Hardcoded Static Unsplash Photo**:
   - Line 295 (About Teaser Fallback):
     ```blade
     <img src="{{ $landingPage->about_image_url ?: ($landingPage->hero_image_url ?: 'https://images.unsplash.com/photo-1497366216548-37526070297c?w=1000&auto=format&fit=crop&q=80') }}"
     ```
     *Defect*: If the merchant has not uploaded an image, it renders a stock Unsplash image of an office room (`photo-1497366216548-37526070297c`).
4. **Hardcoded Indonesian Strings**:
   - Lines 126–132: "Layanan Profesional", "Jasa & Perawatan Spesialis", "Dikerjakan oleh teknisi andal dan profesional di bidangnya."
   - Line 145: "Tanya Jadwal"
   - Line 164: "Konsultasi CS"
   - Line 217: "Kapasitas {{ $tbl->capacity }} Orang", "Tersedia"
   - Line 241: "Cerita Brand"
   - Line 269–271: "Jadwal Operasional", "Waktu pelayanan resmi outlet", "Hari", "Buka"
   - Line 311–314: "Edukasi & Tips", "Artikel Terbaru"

---

## 3. Survey of 42 Themes Across 6 Design Archetypes

### 3.1 Directory & File Structure Verification
Every theme directory under `resources/views/public/storefront/themes/` contains standard component partials:
1. `hero.blade.php`
2. `features_banner.blade.php`
3. `category_chips.blade.php`
4. `product_card.blade.php`
5. `navbar.blade.php`
6. `footer.blade.php`
*(Note: `fnb_cafe` also includes `catalog_filter.blade.php` and `qr_dinein.blade.php`)*.

---

### 3.2 Deep-Dive Sample Analysis per Design Archetype

#### Archetype 1: Clean Bento & Modern Tech (Sample: `universal_storefront`)
- **Theme Characteristics**: Modern rounded squircle cards, Plus Jakarta Sans, neutral grayscale with dynamic primary accent.
- **`hero.blade.php`**:
  - *Hardcoded Sector Text*: Line 13 `<span>{{ $activeTheme['industry'] ?? 'Toko Resmi & Terverifikasi' }}</span>`, Line 28 `'Jelajahi Katalog'`, Line 37 `'Tanya CS WhatsApp'`, Line 63 `'Outlet Resmi & Siap Melayani'`, Line 65 `'100% Terverifikasi'`.
  - *Unsplash Stock Image*: Line 53 `'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=1000&auto=format&fit=crop&q=80'` (restaurant food photo!).
  - *Contract Violations*: Does not parse `$landingPage->values` for the 3 dynamic badges; does not render the Universal Bento Brand card fallback when image is missing.
- **`features_banner.blade.php`**:
  - *Contract Violations*: All 4 pillars are static HTML blocks. Does not read `$landingPage->values`. Pillar 4 title is hardcoded: "Customer Support Siaga". Pillar descriptions are hardcoded Indonesian text.
- **`category_chips.blade.php`**:
  - *Contract Violations*: Does not read `$landingPage->services_title`. Hardcodes subtitle "Pilih kategori untuk memfilter etalase belanja". Lacks active state checking (`request('category') == $cat->id`) and lacks "Semua Produk" reset chip.

---

#### Archetype 2: Earthy & Warm Artisan (Sample: `coffee_beverage`)
- **Theme Characteristics**: Warm parchment `#FAF7F2`, roastery dark `#15110E`, Playfair Display & DM Serif, amber/coffee tones `#8B5A2B` and `#C88A58`.
- **`hero.blade.php`**:
  - *Hardcoded Hex Colors (95 occurrences across theme)*: `bg-[#FAF7F2]`, `dark:bg-[#15110E]`, `border-[#E8DEC8]`, `bg-[#8B5A2B]/10`, `text-[#3D2B1F]`, `text-[#6B5744]`, `text-[#CBBDB1]`, `bg-[#201914]`, `bg-[#8B5A2B]`, `hover:bg-[#724822]`.
  - *Hardcoded Sector Texts*:
    - "Specialty Roastery • Fresh Roasted Weekly"
    - "Ritual Kopi Spesialti, Sangrai Presisi Setiap Cangkir"
    - "Dari biji kopi pilihan petani nusantara, disangrai dengan profil optimal dan diseduh oleh barista penuh dedikasi untuk rasa terbaik."
    - Badges: "Caramel & Dark Chocolate", "Medium Roast Balanced", "100% Arabica & Robusta Origin"
    - "Pengalaman minum kopi spesialti dan nongkrong nyaman bersama kawan."
    - "Fresh Brewed Today", "Espresso base, filter & artisan tea", "Lihat Menu →"
  - *Contract Violations*: Does not read `$landingPage->values`. If a florist or bookstore selects this theme for its warm serif aesthetic, it displays coffee roasting badges and roast notes!
- **`features_banner.blade.php`**:
  - *Hardcoded Sector Copy*:
    - 1: "Specialty Grade" / "Biji kopi pilihan cupping score 80+"
    - 2: "Fresh Roasted" / "Disangrai mingguan terjaga segar"
    - 3: "Racikan Barista" / "Ekstraksi presisi suhu & rasio"
    - 4: "Aroma Lock Pack" / "Kemasan zipper valve kedap udara"
  - *Contract Violations*: Completely ignores CMS `$landingPage->values`.
- **`category_chips.blade.php`**:
  - *Hardcoded Copy*: Title hardcoded to "Bar & Brew Menu"; subtitle "Pilih sajian espresso, manual brew, artisan tea & beans".
  - *Hardcoded Sector Icons*: Every category is forced to display `<i data-lucide="coffee">`.
- **`product_card.blade.php`**:
  - Every card forcibly renders a "Roastery" badge and "Per Cup / Pack" unit! Price fallback says "Tanya Barista".
- **`navbar.blade.php` & `footer.blade.php`**:
  - Hardcodes "Coffee Roastery & Bar", "Menu Kopi & Minuman", "Kisah Biji Kopi", "Kafe & Lokasi", and hardcoded hours "Senin - Minggu: 07:30 - 23:00 WIB".

---

#### Archetype 3: High-End Luxe & Editorial (Sample: `retail_fashion`)
- **Theme Characteristics**: Cormorant Garamond serif, minimalist monochrome, ample whitespace, high-fashion editorial mood.
- **`hero.blade.php`**:
  - *Hardcoded Sector Text*:
    - "Editorial Fashion & Haute Butik"
    - "Elegansi Abadi Siluet Mode Kontemporer"
    - "Koleksi busana pilihan dengan potongan presisi, kain serat alami berstandar kurasi tinggi, dan jahitan rapi yang dirancang untuk kenyamanan sepanjang hari."
    - Badges: "Koleksi Eksklusif Terbatas", "Tailoring & Jahitan Rapi", "Garansi Tukar Ukuran"
    - CTAs: "Jelajahi Lookbook", "Tanya Fitting & Size"
    - Floating pill: "New Season Drop", "Koleksi Terkini", "Limited Run"
  - *Unsplash Stock Image*: Line 60 `'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=1000&auto=format&fit=crop&q=80'` (high-fashion woman model).
  - *Contract Violations*: Fails §3.1. Cannot be used by high-end jewelry, cosmetics, or art galleries without leaking tailoring/clothing terms.
- **`features_banner.blade.php`**:
  - *Hardcoded Sector Pillars*:
    - 1: "Material Serat Alami" / "Katun premium, linen & sutra sejuk di kulit tropis."
    - 2: "Jahitan Presisi Halus" / "Kualitas butik dengan kerapatan jahitan standar tinggi."
    - 3: "Garansi Tukar Ukuran" / "Bebas tukar ukuran 7 hari bila tidak pas di badan."
    - 4: "Packaging Kotak Mewah" / "Dikemas rapi siap pakai atau dikirim sebagai kado."
  - *Contract Violations*: Fails §3.2.
- **`category_chips.blade.php`**:
  - *Hardcoded Copy*: Subtitle "Jelajahi lini busana pria, wanita, kasual, dan formal".
  - *Hardcoded Icon Map*: Categorizes keywords ('atasan', 'celana', 'dress', 'hijab', etc.) with fallback `'shirt'`, forcing non-clothing categories into shirt icons.

---

#### Archetype 4: Dark Carbon & High Impact (Sample: `service_workshop`)
- **Theme Characteristics**: Dark slate `#0F172A` / `#1E293B`, Chakra Petch / Orbitron, mechanical racing orange `#EA580C`.
- **`hero.blade.php`**:
  - *Hardcoded Sector Text*:
    - "Bengkel Resmi & Sparepart Otomotif"
    - "Performa Mesin Prima & Servis Terpercaya"
    - "Servis berkala, tune-up injeksi, ganti oli original, perbaikan rem & kaki-kaki, serta ketersediaan suku cadang asli bergaransi dengan mekanik bersertifikat."
    - Badges: "100% Sparepart Original", "Scanner Komputer Injeksi", "Booking Tanpa Antre"
    - CTAs: "Cari Sparepart & Servis", "Konsultasi Keluhan Kendaraan"
    - Visual badge: "Pit Stop Siap Melayani", "Mekanik Handal"
  - *Unsplash Stock Image*: Line 61 `'https://images.unsplash.com/photo-1486006920555-c77dce18193b?w=1000&auto=format&fit=crop&q=80'` (mechanic repairing car engine).
  - *Contract Violations*: Fails §3.1. A dark-mode developer tool, gym, or hardware shop using this theme displays engine tune-up copy.
- **`features_banner.blade.php`**:
  - *Hardcoded Sector Pillars*:
    - 1: "Onderdil 100% Asli" / "Suku cadang resmi berbarcode resmi pabrikan ATPM."
    - 2: "Scanner Diagnosis OBD-II" / "Deteksi kerusakan mesin mobil/motor injeksi akurat."
    - 3: "Mekanik Bersertifikat" / "Dikerjakan teknisi berpengalaman dengan SOP rapi."
    - 4: "Garansi Pengerjaan" / "Garansi servis 1-4 minggu demi ketenangan berkendara."
- **`category_chips.blade.php`**:
  - Hardcodes subtitle: "Oli mesin, ban, aki, pengereman, tune-up & aksesoris motor/mobil".
  - Icon mapping defaults all categories to `'wrench'`.

---

#### Archetype 5: Playful, Fresh & Appetite (Sample: `food_restaurant`)
- **Theme Characteristics**: Soft warm ceramic `#FFFDF5`, `#FFF9ED`, `#18110D`, red/amber accent palette, inviting friendly curve radius.
- **`hero.blade.php`**:
  - *Hardcoded Sector Text*:
    - "Cita Rasa Otentik • Masak Segar Tiap Hari"
    - "Kelezatan Resep Asli, Dimasak Hangat Dari Dapur Kami"
    - "Nikmati hidangan istimewa racikan bumbu rempah pilihan, disajikan dengan kehangatan suasana santap bersama keluarga dan kerabat."
    - Badges: "100% Halal Certified", "Bumbu Rempah Alami", "Higienis & Segar"
    - CTAs: "Buka Buku Menu", "Reservasi Meja & Order"
    - Visual badge: "Menu Spesial Hari Ini", "Disediakan terbatas & fresh", "Pesan →"
- **`features_banner.blade.php`**:
  - *Hardcoded Sector Pillars*:
    - 1: "Rempah Otentik" / "Racikan resep turun-temurun"
    - 2: "100% Halal" / "Bahan segar & terjamin berkah"
    - 3: "Masak Hangat" / "Dimasak langsung saat dipesan"
    - 4: "Standar Higienis" / "Dapur bersih & terinspeksi"
- **`category_chips.blade.php`**:
  - Subtitle: "Pilih jenis sajian untuk melihat menu lengkap".
  - Forces `<i data-lucide="utensils">` across all product categories.

---

#### Archetype 6: Institutional & Trust (Sample: `business_corporate`)
- **Theme Characteristics**: Navy/slate corporate geometry, formal Plus Jakarta Sans, B2B enterprise credibility.
- **`hero.blade.php`**:
  - *Hardcoded Sector Text*:
    - "Corporate Enterprise & B2B Solutions"
    - "Mitra Solusi & Suplai Bisnis Terpercaya"
    - "Menyediakan produk dan layanan berstandar industri dengan jaminan mutu, kontrak kerja transparan, dan keandalan operasional jangka panjang."
    - CTAs: "Katalog Solusi & Produk", "Hubungi Account Representative"
    - Hardcoded Trust Metrics: "100% Entitas Legal & Resmi", "SLA 99.9% Komitmen Layanan", "B2B & Retail Faktur Pajak Tersedia"
  - *Unsplash Stock Image*: Line 64 `'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=1000&auto=format&fit=crop&q=80'` (glass corporate skyscraper).
- **`features_banner.blade.php`**:
  - *Hardcoded Sector Pillars*:
    - 1: "SLA Garansi Resmi" / "Jaminan mutu kerja dan dukungan teknis berstandar SLA."
    - 2: "Faktur Pajak & PPN" / "Kelengkapan dokumen perpajakan resmi untuk pembukuan entitas."
    - 3: "Quotation & PO Resmi" / "Skema pembayaran bertahap dan termin sesuai kesepakatan PO."
    - 4: "Dedicated PIC" / "Pendampingan langsung oleh Account Manager profesional."
- **`category_chips.blade.php`**:
  - Hardcodes subtitle: "Pilih kategori untuk memfilter spesifikasi pengadaan".

---

## 4. Contract Compliance Audit Matrix

| Contract Section | Specification in Blueprint | Current State Across 42 Themes | Non-Compliance Risk |
|---|---|---|---|
| **§3.1 Hero Fallback** | `$headline ?: $business->name`, `$subheadline ?: ($business->description ?: __('storefront.hero.tagline'))`, `$eyebrow ?: ($activeTheme['industry'] ?? __('storefront.hero.verified_official'))` | 42 themes have static sector strings hardcoded inside Blade templates. | Non-sector merchants get irrelevant copy (e.g., shoe store gets "Ritual Kopi"). |
| **§3.1 Hero Badges** | 3 dynamic pills parsed from `$landingPage->values[0..2]` with icon and title, falling back to universal i18n (`storefront.badges.*`). | **0 out of 42** themes read `$landingPage->values`. All hardcode static badges. | Merchant edits in Tab 3 CMS ("Keunggulan") are completely ignored by the theme! |
| **§3.1 Hero Visual Fallback** | Fallback to Elegant Brand Bento Graphic Showcase (`var(--theme-primary)` badge, merchant initials, verified badge, no external stock photos). | **22 themes** load external Unsplash images; 20 themes load static sector graphics. | Data leakage, external network dependency, and sector misalignment. |
| **§3.2 Features Banner** | 4 pillars loop over `$landingPage->values[0..3]` with dynamic icons and copy, falling back to 4 universal pillars (`storefront.features.*`). | **0 out of 42** themes read `$landingPage->values`. All hardcode 4 sector pillars. | Feature values saved in CMS are never displayed in the store. |
| **§3.2 CSS Variables** | Uses dynamic classes `theme-badge`, `var(--theme-primary)`, and semantic card tokens. | Dozens of themes use hardcoded hex colors (`#8B5A2B`, `#EA580C`, etc.). | Changing theme color in CMS does not update the badges or borders. |
| **§3.3 Category Chips** | `$landingPage->services_title ?: __('storefront.kategori_pilihan')`, "Semua Produk" button with active state, dynamic chip highlighting, `min-h-[44px]`. | Most lack "Semua Produk", lack active state highlight, hardcode sector subtitles, or force `wrench`/`shirt`/`coffee` icons. | Poor UX, category filter state invisible, touch targets too small. |
| **§home Section Visibility** | Wraps sections in `@if($landingPage->isSectionVisible('...'))`. | `features_banner` and `category_chips` have **NO visibility checks**. | Toggling section off in CMS has zero effect on public site. |

---

## 5. Backend & CMS Infrastructure Audit

### 5.1 `BusinessLandingPage` Model (`app/Models/BusinessLandingPage.php`)
- **Status of `values`**: Cast to `'array'` in `$casts` (line 89). Properly stored in database.
- **Status of `section_visibility`**: Cast to `'array'` in `$casts` (line 97).
- **Missing Helper**: The method `isSectionVisible(string $section): bool` **does not exist**.
  - Must be implemented as:
    ```php
    public function isSectionVisible(string $section, bool $default = true): bool
    {
        $visibility = (array) ($this->section_visibility ?? []);
        return array_key_exists($section, $visibility) ? (bool) $visibility[$section] : $default;
    }
    ```

### 5.2 `StorefrontThemeService` (`app/Domain/Storefront/StorefrontThemeService.php`)
- **Themes Registered**: All 42 themes are defined in `private const THEMES`.
- **Missing Archetype Classification**: Themes are mapped to `industry` strings, but have no programmatic archetype mapping.
- **Missing Methods**:
  - `getThemeArchetype(string $themeKey): string`
  - `getUniversalFallback(string $themeKey): array`

### 5.3 Translation Files (`lang/id/storefront.php` & `lang/en/storefront.php`)
- **Missing Keys**:
  - `badges`: `quality_guaranteed`, `secure_transaction`, `fast_service`, `verified_official`, `active_status`.
  - `features`: `official_store`, `official_desc`, `easy_payment`, `payment_desc`, `secure_delivery`, `delivery_desc`, `customer_care`, `care_desc`.
  - `kategori_pilihan`, `pilih_kategori_filter`, `semua_produk`, `view_all`.

### 5.4 CMS Backoffice Cockpit (`resources/views/app/landing_page/edit.blade.php`)
- Sub-Card 5 currently groups themes into 6 outdated industry categories (`fnb`, `retail`, `service`, `industry`, `lifestyle`, `corporate`) rather than the 6 Design Archetypes (`modern_bento`, `warm_artisan`, `luxe_editorial`, `dark_carbon`, `playful_fresh`, `institutional_trust`).
- Sub-Card 6 (Section Visibility) lacks toggle inputs for `features_banner` and `category_chips`.

---

## 6. Implementation Action Plan for Downstream Agents

Based on these verified observations, the refactoring roadmap must proceed in strictly sequenced phases:

```
┌─────────────────────────────────────────────────────────────┐
│ FASE 1: Foundation (Backend & i18n)                         │
│ - Add universal keys in lang/id & lang/en                   │
│ - Add isSectionVisible() in BusinessLandingPage.php         │
│ - Add getThemeArchetype() in StorefrontThemeService.php     │
└──────────────────────────────┬──────────────────────────────┘
                               │
                               ▼
┌─────────────────────────────────────────────────────────────┐
│ FASE 2: 42 Themes Refactor (4 Batches)                      │
│ - Batch 1 (10 Core & Universal Themes)                      │
│ - Batch 2 (11 F&B & Retail Themes)                          │
│ - Batch 3 (11 Service Themes)                               │
│ - Batch 4 (10 Industry & Corporate Themes)                  │
│ Enforce Universal Contracts §3.1, §3.2, §3.3                │
│ Eliminate hardcoded Unsplash, hex colors & sector texts     │
└──────────────────────────────┬──────────────────────────────┘
                               │
                               ▼
┌─────────────────────────────────────────────────────────────┐
│ FASE 3: Storefront Controller & Layout                      │
│ - Wrap features_banner & category_chips with visibility     │
│ - Eliminate $isArtisanBrew hardcoded sector conditionals   │
│ - Clean up app.blade.php hex colors                         │
└──────────────────────────────┬──────────────────────────────┘
                               │
                               ▼
┌─────────────────────────────────────────────────────────────┐
│ FASE 4: CMS Landing Page Cockpit (edit.blade.php)           │
│ - Update Sub-Card 5 with 6 Design Archetype Tabs            │
│ - Add Section Visibility Toggles for Banner & Category      │
│ - Add educational copy linking Values to Badges & Pillars   │
└──────────────────────────────┬──────────────────────────────┘
                               │
                               ▼
┌─────────────────────────────────────────────────────────────┐
│ FASE 5: QA & Verification                                   │
│ - php -l syntax linting across all modified views           │
│ - Automated test suite run                                  │
│ - Update AiWorkHistory.md                                   │
└─────────────────────────────────────────────────────────────┘
```
