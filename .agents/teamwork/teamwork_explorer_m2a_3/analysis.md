# Analysis Report: Themes 8 to 10 Storefront Refactoring Audit

**Target Milestone**: Milestone 2A (Batch 1 Themes Refactoring)  
**Assigned Themes**:  
- Theme 8: `service_workshop` (Apex Velocity / Dark Carbon Archetype)  
- Theme 9: `food_restaurant` (Food & Restaurant / Playful Fresh Archetype)  
- Theme 10: `health_beauty` (Health & Beauty / High-End Luxe Editorial Archetype)  
**Investigated Files per Theme**:  
1. `hero.blade.php`  
2. `features_banner.blade.php`  
3. `category_chips.blade.php`  

---

## 1. Executive Summary

An exhaustive line-by-line inspection was conducted on `hero.blade.php`, `features_banner.blade.php`, and `category_chips.blade.php` across the three target theme directories in `resources/views/public/storefront/themes/`.

All three themes currently suffer from severe sector rigidity and zero CMS dynamic values injection:
1. **Rigid Sector Fallback Copy**: All 3 themes feature hardcoded headlines, subheadlines, announcement badges, and CTA labels ("Tune-up Mesin", "Resep Warisan", "Dermatology Science", etc.) that leak sector identity if chosen by merchants outside those sectors.
2. **Disconnected Features & Badges**: None of the 3 themes read `$landingPage->values` or `$landingPage->getUniversalValues()`. Features banners and hero badges are 100% hardcoded HTML.
3. **Missing Universal CTA Routing**: None of the secondary CTAs respect `$landingPage->cta_secondary_url` or `$landingPage->cta_secondary_text`. Primary CTAs ignore `$landingPage->cta_primary_url`.
4. **Category Chips Incomplete**: None of the 3 themes render the "Semua Produk" (All Products) chip, none highlight the active category chip via `request('category') == $cat->id`, and all use hardcoded sector titles and subtitles.
5. **Hero Image Fallback Flaws**: `service_workshop` embeds an external hardcoded Unsplash photo (`photo-1486006920555-c77dce18193b`), and none of the themes check `$landingPage->about_image_url` before falling back to a brand graphic.

The proposed adaptations strictly preserve the visual design signatures (Dark Carbon for `service_workshop`, Warm Hearth for `food_restaurant`, and Clinical Luxe for `health_beauty`) while complying 100% with Universal Blade Contracts §3.1, §3.2, and §3.3.

---

## 2. Detailed Audit per Theme

### 2.1 Theme 8: `service_workshop` (Apex Velocity)

- **Archetype**: `dark_carbon` (Dark Carbon & High Impact)
- **Primary Color**: `#EA580C` (Racing Orange) | **Accent**: `#38BDF8` (Electric Blue)
- **Background**: `#0F172A` (Slate Carbon) | **Card**: `#1E293B`
- **Typography**: Heading `Chakra Petch`, Body `Inter`

#### A. `hero.blade.php` (81 lines)
- **File Location**: `resources/views/public/storefront/themes/service_workshop/hero.blade.php`
- **Rigid Sector Findings**:
  - Line 11-13: Rigid wrench icon `<i data-lucide="wrench"></i>` and hardcoded fallback text `'Bengkel Resmi & Sparepart Otomotif'`. Ignores `$landingPage->announcement_badge`.
  - Line 16: Hardcoded headline fallback: `'Performa Mesin Prima & Servis Terpercaya'`.
  - Line 20: Hardcoded subheadline fallback: `'Servis berkala, tune-up injeksi, ganti oli original, perbaikan rem & kaki-kaki, serta ketersediaan suku cadang asli bergaransi dengan mekanik bersertifikat.'`.
  - Lines 23-36: Hardcoded 3 automotive badges:
    1. `100% Sparepart Original` (`shield-check`)
    2. `Scanner Komputer Injeksi` (`cpu`)
    3. `Booking Tanpa Antre` (`calendar`)
  - Line 41-44: Primary CTA hardcodes gradient `style="background: linear-gradient(135deg, #EA580C, #C2410C);"` and fallback text `'Cari Sparepart & Servis'`. Ignores `$landingPage->cta_primary_url`.
  - Lines 49-53: Secondary CTA ignores `$landingPage->cta_secondary_text` and `$landingPage->cta_secondary_url`. Hardcodes text: `Konsultasi Keluhan Kendaraan` and calls `$landingPage->getWhatsAppUrl()` directly.
  - Line 61: Hardcoded external Unsplash image URL: `https://images.unsplash.com/photo-1486006920555-c77dce18193b?w=1000&auto=format&fit=crop&q=80` (photo of a car mechanic). Does not check `$landingPage->about_image_url`.
  - Lines 67-73: Floating badge on hero graphic hardcodes `'Pit Stop Siap Melayani'` and `'Mekanik Handal'`.
- **Exact Universal Adaptation**:
  - Implement Universal Contract §3.1 PHP initialization block:
    ```blade
    @php
        $heroHeadline = $landingPage->headline ?: $business->name;
        $heroSubheadline = $landingPage->subheadline ?: ($business->description ?: __('storefront.hero.tagline'));
        $heroEyebrow = $landingPage->announcement_badge ?: ($activeTheme['industry'] ?? __('storefront.hero.verified_official'));
        $primaryCtaText = $landingPage->cta_primary_text ?: __('storefront.hero.explore_catalog');
        $primaryCtaUrl  = $landingPage->cta_primary_url ?: url('/' . $business->slug . '/katalog');
        $secondaryCtaText = $landingPage->cta_secondary_text ?: __('storefront.hero.contact_us');
        $secondaryCtaUrl  = $landingPage->cta_secondary_url ?: $landingPage->getWhatsAppUrl();

        $cmsValues = is_array($landingPage->values) ? $landingPage->values : [];
        $badgePill1 = $cmsValues[0]['title'] ?? __('storefront.badges.quality_guaranteed');
        $badgeIcon1 = $cmsValues[0]['icon'] ?? 'shield-check';
        $badgePill2 = $cmsValues[1]['title'] ?? __('storefront.badges.secure_transaction');
        $badgeIcon2 = $cmsValues[1]['icon'] ?? 'credit-card';
        $badgePill3 = $cmsValues[2]['title'] ?? __('storefront.badges.fast_service');
        $badgeIcon3 = $cmsValues[2]['icon'] ?? 'truck';

        $heroImage = $landingPage->hero_image_url ?: ($landingPage->about_image_url ?: null);
    @endphp
    ```
  - Eyebrow badge: Preserve dark carbon racing aesthetic (`bg-orange-950/60 text-orange-400 border border-orange-500/30`), replace wrench with pulsing indicator `<span class="w-2 h-2 rounded-full bg-orange-400 animate-pulse"></span>` and display `{{ $heroEyebrow }}`.
  - Typography: Keep Chakra Petch `font-heading font-extrabold text-3xl sm:text-5xl lg:text-6xl text-white tracking-tight` rendering `{{ $heroHeadline }}` and `{{ $heroSubheadline }}`.
  - 3 Badges: Render `$badgePill1`, `$badgePill2`, `$badgePill3` with their respective `$badgeIcon1..3`, wrapped in dark carbon pill cards (`px-3 py-1.5 rounded-full text-[11px] font-bold bg-slate-800 text-slate-200 border border-slate-700`).
  - CTAs: Primary CTA uses `$primaryCtaUrl` and `$primaryCtaText` with dynamic gradient `background: linear-gradient(135deg, var(--theme-primary), #C2410C)`. Secondary CTA uses `$secondaryCtaUrl` and `$secondaryCtaText`.
  - Visual Column: If `$heroImage` is set, render the image. If null, render dark carbon racing bento fallback card with business monogram, business name, `$heroSubheadline`, and HUD status pill showing `{{ __('storefront.hero.active_status') }}` and `{{ __('storefront.badges.verified_official') }}`. Eliminate hardcoded Unsplash photo.

#### B. `features_banner.blade.php` (47 lines)
- **File Location**: `resources/views/public/storefront/themes/service_workshop/features_banner.blade.php`
- **Rigid Sector Findings**:
  - Lines 6-44: 4 hardcoded automotive cards:
    1. "Onderdil 100% Asli" ("Suku cadang resmi berbarcode resmi pabrikan ATPM.")
    2. "Scanner Diagnosis OBD-II" ("Deteksi kerusakan mesin mobil/motor injeksi akurat.")
    3. "Mekanik Bersertifikat" ("Dikerjakan teknisi berpengalaman dengan SOP rapi.")
    4. "Garansi Pengerjaan" ("Garansi servis 1-4 minggu demi ketenangan berkendara.")
  - Hardcoded card accent colors (`bg-orange-500/10 text-orange-600`, `bg-blue-500/10 text-blue-600`, `bg-emerald-500/10`, `bg-amber-500/10`).
- **Exact Universal Adaptation**:
  - Implement Universal Contract §3.2:
    Resolve 4 pillars dynamically from `$landingPage->values` with fallback to `storefront.features.*`.
  - Preserve dark carbon styling:
    Card wrapper: `p-4 sm:p-5 rounded-2xl bg-white dark:bg-[#1E293B] border border-orange-500/20 dark:border-white/10 shadow-sm flex items-start gap-3.5`
    Icon container: `w-10 h-10 rounded-xl bg-orange-500/10 text-orange-400 dark:text-orange-400 flex items-center justify-center shrink-0`
    Heading: `font-heading font-bold text-sm text-neutral-900 dark:text-white`
    Description: `text-xs text-neutral-500 dark:text-neutral-400 mt-0.5 leading-relaxed`

#### C. `category_chips.blade.php` (62 lines)
- **File Location**: `resources/views/public/storefront/themes/service_workshop/category_chips.blade.php`
- **Rigid Sector Findings**:
  - Line 8: Header title hardcoded translation `__('storefront.kategori_servis_onderdil')`.
  - Lines 9-11: Rigid subtitle `Oli mesin, ban, aki, pengereman, tune-up & aksesoris motor/mobil`.
  - Line 14: Hardcoded color `text-orange-600 dark:text-orange-400` instead of `theme-link`.
  - Missing "Semua Produk" (All Products) chip.
  - Lines 22-37: Rigid `$catIcons` keyword mapping for automotive parts (`oli`, `ban`, `aki`, `rem`, `servis`, `busi`), with default fallback `'wrench'`.
  - Line 52: No active state indicator for `request('category') == $cat->id`.
- **Exact Universal Adaptation**:
  - Title: `{{ $landingPage->services_title ?: __('storefront.kategori_pilihan') }}`
  - Subtitle: `{{ __('storefront.pilih_kategori_filter') }}`
  - Link: `{{ __('storefront.view_all') }}`
  - Add "Semua Produk" chip as first element with `layout-grid` icon and active state checking `!request()->filled('category')`.
  - Category chips: Loop `$productCategories->take(12)`, active check `@php $isActive = request('category') == $cat->id; @endphp`.
  - Active style: `style="background: var(--theme-primary); color: #fff; border-color: var(--theme-primary);"` with `shadow-md`.
  - Inactive style: `bg-white dark:bg-[#1E293B] border border-orange-500/20 dark:border-white/10 text-neutral-800 dark:text-neutral-200 hover:border-orange-500`.
  - Icon: Clean Lucide `tag` icon (44px touch target compliant).

---

### 2.2 Theme 9: `food_restaurant` (Food & Restaurant)

- **Archetype**: `playful_fresh` (Playful, Fresh & Appetite)
- **Primary Color**: `#991B1B` (Rich Crimson) | **Accent**: `#D97706` (Warm Amber)
- **Background**: `#FFFBEB` / `#FFFDF5` (Warm Cream) | **Dark BG**: `#18110D` (Warm Roasted Espresso)
- **Typography**: Heading `DM Serif Display`, Body `Plus Jakarta Sans`

#### A. `hero.blade.php` (101 lines)
- **File Location**: `resources/views/public/storefront/themes/food_restaurant/hero.blade.php`
- **Rigid Sector Findings**:
  - Lines 15-18: Rigid crossed utensils icon `<i data-lucide="utensils-crossed"></i>` and hardcoded uppercase text: `Cita Rasa Otentik &bull; Masak Segar Tiap Hari`. Ignores `$landingPage->announcement_badge`.
  - Line 21: Rigid headline fallback: `'Kelezatan Resep Asli, Dimasak Hangat Dari Dapur Kami'`.
  - Line 25: Rigid subheadline fallback: `'Nikmati hidangan istimewa racikan bumbu rempah pilihan, disajikan dengan kehangatan suasana santap bersama keluarga dan kerabat.'`.
  - Lines 29-42: Rigid culinary badges:
    1. `100% Halal Certified` (`shield-check`)
    2. `Bumbu Rempah Alami` (`flame`)
    3. `Higienis & Segar` (`sparkles`)
  - Lines 48-51: Primary CTA hardcodes `bg-red-700 hover:bg-red-800`, icon `book-open`, rigid text `__('storefront.open_menu_book')`, and ignores `$landingPage->cta_primary_url`.
  - Lines 55-60: Secondary CTA hardcodes icon `calendar`, rigid text `__('storefront.reservasi_meja_order')`, and ignores `$landingPage->cta_secondary_text` & `$landingPage->cta_secondary_url`.
  - Lines 68-79: Visual hero image only checks `$landingPage->hero_image_url` (omits `$landingPage->about_image_url`). Fallback block hardcodes utensils icon and culinary copy: `Pengalaman kuliner penuh selera dengan cita rasa hangat keluarga.`.
  - Lines 82-93: Floating card hardcodes `chef-hat` icon, `Menu Spesial Hari Ini`, `Disediakan terbatas & fresh`, and `Pesan →`.
- **Exact Universal Adaptation**:
  - Implement Universal Contract §3.1 initialization.
  - Eyebrow badge: Preserve warm hearth pill (`bg-red-100/70 dark:bg-red-950/60 border border-red-200/80 dark:border-red-900/60 text-xs font-semibold text-red-800 dark:text-red-300`), with animated pulse indicator and `{{ $heroEyebrow }}`.
  - Typography: Keep DM Serif Display `font-heading font-normal text-3xl sm:text-5xl lg:text-6xl text-slate-900 dark:text-amber-50 tracking-tight leading-[1.15]` with `{{ $heroHeadline }}` and `{{ $heroSubheadline }}`.
  - 3 Badges: Dynamic badges from `$cmsValues` (`$badgePill1..3` and `$badgeIcon1..3`) styled in warm appetite rounded pills (`px-3 py-1 rounded-full text-[11px] font-semibold bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 border border-amber-200/60`).
  - Primary CTA: Dynamic text `{{ $primaryCtaText }}`, dynamic URL `{{ $primaryCtaUrl }}`, styled with `theme-btn-primary min-h-[48px]` (or `style="background-color: var(--theme-primary);"`), with `arrow-right` icon.
  - Secondary CTA: Dynamic text `{{ $secondaryCtaText }}`, dynamic URL `{{ $secondaryCtaUrl }}`, with `message-circle` icon and touch-friendly 48px height.
  - Visual Column: `$heroImage = $landingPage->hero_image_url ?: ($landingPage->about_image_url ?: null);`. If null, display warm gradient brand bento card with business monogram, business name, and `$heroSubheadline`.
  - Floating card: Replace chef-hat with `sparkles`, display `{{ $heroEyebrow }}` or `{{ __('storefront.hero.active_status') }}` and `{{ __('storefront.badges.verified_official') }}`.

#### B. `features_banner.blade.php` (54 lines)
- **File Location**: `resources/views/public/storefront/themes/food_restaurant/features_banner.blade.php`
- **Rigid Sector Findings**:
  - Lines 6-51: 4 completely rigid culinary pillars:
    1. "Rempah Otentik" ("Racikan resep turun-temurun") (`flame`)
    2. "100% Halal" ("Bahan segar & terjamin berkah") (`shield-check`)
    3. "Masak Hangat" ("Dimasak langsung saat dipesan") (`soup`)
    4. "Standar Higienis" ("Dapur bersih & terinspeksi") (`sparkles`)
- **Exact Universal Adaptation**:
  - Implement Universal Contract §3.2:
    Loop through 4 dynamic pillars `$pillars` mapped from `$landingPage->values` with universal fallbacks.
  - Preserve warm container & friendly rounded style:
    Section wrapper: `p-5 sm:p-6 rounded-3xl bg-[#FFF9ED] dark:bg-[#1D140F] border border-amber-200/70 dark:border-amber-950/70 transition-colors`
    Icon badge container: `w-11 h-11 rounded-2xl bg-amber-100 dark:bg-amber-950/60 border border-amber-200/60 dark:border-amber-900/60 flex items-center justify-center text-amber-800 dark:text-amber-300 shrink-0`
    Heading: `font-heading font-bold text-xs sm:text-sm text-slate-900 dark:text-amber-100`
    Description: `text-[11px] text-slate-500 dark:text-amber-300/60 leading-relaxed`

#### C. `category_chips.blade.php` (30 lines)
- **File Location**: `resources/views/public/storefront/themes/food_restaurant/category_chips.blade.php`
- **Rigid Sector Findings**:
  - Line 9: Title hardcoded `__('storefront.daftar_hidangan')`.
  - Line 10: Rigid subtitle `Pilih jenis sajian untuk melihat menu lengkap`.
  - Line 14: Hardcoded text `Seluruh Menu`.
  - Missing "Semua Produk" chip.
  - Line 23: Hardcoded icon `<i data-lucide="utensils">` for all categories.
  - Line 22: Hardcoded hover colors (`hover:border-red-500 hover:text-red-700`) without active indicator for `request('category') == $cat->id`.
- **Exact Universal Adaptation**:
  - Title: `{{ $landingPage->services_title ?: __('storefront.kategori_pilihan') }}`
  - Subtitle: `{{ __('storefront.pilih_kategori_filter') }}`
  - Link: `{{ __('storefront.view_all') }}`
  - Add "Semua Produk" chip with `layout-grid` icon and active state checking `!request()->filled('category')`.
  - Render category chips with `tag` icon, 44px min-height, and active indicator:
    Active: `theme-btn-primary shadow-xs` (crimson/warm tone).
    Inactive: `border border-amber-200 dark:border-amber-900/60 bg-white dark:bg-[#1D140F] text-slate-800 dark:text-amber-100 hover:border-amber-400`.

---

### 2.3 Theme 10: `health_beauty` (Health & Beauty)

- **Archetype**: `luxe_editorial` (High-End Luxe & Editorial)
- **Primary Color**: `#0D9488` (Teal Medical/Spa) | **Accent**: `#EC4899` (Rose Luxe)
- **Background**: `#F0FDFA` (Soft Pale Teal) | **Dark BG**: `#0F171A` (Deep Slate Teal)
- **Typography**: Heading `Outfit`, Body `Plus Jakarta Sans`

#### A. `hero.blade.php` (130 lines)
- **File Location**: `resources/views/public/storefront/themes/health_beauty/hero.blade.php`
- **Rigid Sector Findings**:
  - Lines 15-18: Eyebrow badge hardcodes clinical text: `Teruji Klinis &bull; Bersertifikat BPOM &amp; Halal`. Ignores `$landingPage->announcement_badge`.
  - Line 21: Rigid headline fallback: `'Pancarkan Pesona Alami dengan Sentuhan Perawatan Medis & Estetika'`.
  - Line 25: Rigid subheadline fallback: `'Formula dermatologi terbaik dan rangkaian treatment profesional oleh tenaga medis ahli untuk merawat kulit sehat, cerah alami, dan awet muda.'`.
  - Lines 29-42: Rigid medical/skincare badges:
    1. `100% Terdaftar BPOM` (`shield-check`)
    2. `Pengawasan Dokter Spesialis` (`user-check`)
    3. `Steril & Higienis Standar Medis` (`check`)
  - Lines 48-51: Primary CTA hardcodes `bg-teal-600 hover:bg-teal-700`, rigid text `__('storefront.pilih_produk_treatment')`, icon `sparkles`, and ignores `$landingPage->cta_primary_url`.
  - Lines 54-61: Secondary CTA hardcodes text: `Konsultasi Dokter Kulit Online` and ignores `$landingPage->cta_secondary_text` & `$landingPage->cta_secondary_url`.
  - Lines 64-77: Metric Badges grid hardcodes clinic statistics:
    1. `10k+ Pasien Terlayani`
    2. `100% Bebas Merkuri`
    3. `Resmi Sertifikasi BPOM & Halal`
    (Severe feature leakage if used by any merchant outside beauty/skincare!)
  - Lines 84-123: Hero visual column only checks `$landingPage->hero_image_url` (omits `$landingPage->about_image_url`). Fallback card hardcodes `flask-conical` icon, `Dermatology Science`, `Perawatan Personalisasi Kulit`, and 3 rigid clinical checklist items:
    1. `Protokol Sterilisasi Standar Bedah Medis`
    2. `Bahan Aktif Premium Teruji Klinis Dermatologi`
    3. `Konsultasi Pasca Treatment Berkelanjutan`
- **Exact Universal Adaptation**:
  - Implement Universal Contract §3.1 initialization block.
  - Eyebrow badge: Preserve editorial rounded-full pill (`bg-teal-100/80 dark:bg-teal-950/60 border border-teal-200 dark:border-teal-800/60 text-xs font-semibold text-teal-800 dark:text-teal-300`), pulsing indicator, and `{{ $heroEyebrow }}`.
  - Typography: Keep Outfit `font-heading font-extrabold text-4xl sm:text-5xl lg:text-6xl text-slate-900 dark:text-white tracking-tight leading-[1.15]` with `{{ $heroHeadline }}` and `{{ $heroSubheadline }}`.
  - 3 Badges: Dynamic badges from `$cmsValues` (`$badgePill1..3` and `$badgeIcon1..3`) in elegant rounded pills (`inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-white dark:bg-slate-900 text-teal-800 dark:text-teal-300 border border-teal-200 dark:border-teal-900/60 shadow-2xs`).
  - Primary CTA: Dynamic text `{{ $primaryCtaText }}`, dynamic URL `{{ $primaryCtaUrl }}`, styled with `theme-btn-primary rounded-full min-h-[48px]`, with `arrow-right` icon.
  - Secondary CTA: Dynamic text `{{ $secondaryCtaText }}`, dynamic URL `{{ $secondaryCtaUrl }}`, with `message-circle text-emerald-500` icon.
  - Metric Badges / Trust Row: Replace hardcoded clinic metrics with universal trust metrics:
    1. `100%` - `{{ __('storefront.badges.quality_guaranteed') }}`
    2. `Resmi` - `{{ __('storefront.badges.verified_official') }}`
    3. `24/7` - `{{ __('storefront.badges.fast_service') }}`
  - Visual Column: `$heroImage = $landingPage->hero_image_url ?: ($landingPage->about_image_url ?: null);`.
  - Fallback graphic: Preserve the opulent frosted card with glowing sphere blurs (`bg-gradient-to-br from-teal-900 via-teal-950 to-slate-950 border border-teal-800`), but display business monogram, business name, `$heroSubheadline`, and 3 dynamic value badges from `$cmsValues` / universal fallbacks with check icons.

#### B. `features_banner.blade.php` (54 lines)
- **File Location**: `resources/views/public/storefront/themes/health_beauty/features_banner.blade.php`
- **Rigid Sector Findings**:
  - Lines 6-51: 4 completely rigid medical/clinical pillars:
    1. Title: `{{ __('storefront.resmi_bpom_halal') }}`, Desc: "100% aman tanpa merkuri" (`shield-check`)
    2. Title: "Dokter Ahli Medis", Desc: "Konsultasi & protokol tepat" (`user-check`)
    3. Title: "Hypoallergenic", Desc: "Lembut untuk kulit sensitif" (`flask-conical`)
    4. Title: "Ruang Steril & Higienis", Desc: "Peralatan sekali pakai & UV" (`sparkles`)
- **Exact Universal Adaptation**:
  - Implement Universal Contract §3.2:
    Loop through 4 dynamic pillars `$pillars` mapped from `$landingPage->values` with universal fallbacks.
  - Preserve luxury circular badge styling:
    Section wrapper: `p-5 sm:p-6 rounded-3xl bg-white dark:bg-[#111C20] border border-teal-100 dark:border-teal-950/70 shadow-2xs transition-colors`
    Icon badge container: `w-11 h-11 rounded-full bg-teal-50 dark:bg-teal-950/60 border border-teal-200/60 dark:border-teal-900/60 flex items-center justify-center text-teal-700 dark:text-teal-300 shrink-0`
    Heading: `font-heading font-bold text-xs sm:text-sm text-slate-900 dark:text-white`
    Description: `text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 leading-relaxed`

#### C. `category_chips.blade.php` (32 lines)
- **File Location**: `resources/views/public/storefront/themes/health_beauty/category_chips.blade.php`
- **Rigid Sector Findings**:
  - Line 10: Hardcoded title: `Koleksi Skincare & Menu Treatment`.
  - Line 12: Hardcoded subtitle: `Pilih formulasi aktif atau rangkaian treatment sesuai kebutuhan kondisi kulit Anda`.
  - Line 15: Hardcoded `text-teal-700 dark:text-teal-300` link.
  - Missing "Semua Produk" chip.
  - Line 25: Hardcoded `sparkles` icon with `text-teal-500` for all categories.
  - Line 24: Hardcoded colors (`border-teal-200 dark:border-teal-900/60 bg-white dark:bg-[#111C20] hover:border-teal-500`) without active state indicator.
- **Exact Universal Adaptation**:
  - Title: `{{ $landingPage->services_title ?: __('storefront.kategori_pilihan') }}`
  - Subtitle: `{{ __('storefront.pilih_kategori_filter') }}`
  - Link: `{{ __('storefront.view_all') }}`
  - Add "Semua Produk" chip with `layout-grid` icon and active state checking `!request()->filled('category')`.
  - Render category chips with `tag` icon, rounded-full geometry, 44px min-height, and active state indicator:
    Active: `theme-btn-primary rounded-full shadow-xs` (teal luxury tone).
    Inactive: `border border-teal-200 dark:border-teal-900/60 bg-white dark:bg-[#111C20] text-slate-800 dark:text-teal-100 hover:border-teal-400`.

---

## 3. Cross-Theme Comparison & Common Invariants

| Attribute | Theme 8 (`service_workshop`) | Theme 9 (`food_restaurant`) | Theme 10 (`health_beauty`) |
|---|---|---|---|
| **Design Archetype** | `dark_carbon` | `playful_fresh` | `luxe_editorial` |
| **Theme Aesthetic** | High Impact Dark Carbon | Warm Hearth & Appetite | High-End Luxe & Pure Editorial |
| **Current Headline Rigidity** | "Performa Mesin Prima..." | "Kelezatan Resep Asli..." | "Pancarkan Pesona Alami..." |
| **Current Values CMS Integration** | None (100% hardcoded) | None (100% hardcoded) | None (100% hardcoded) |
| **Current CTA Parameters** | Hardcoded text & WhatsApp | Hardcoded text & WhatsApp | Hardcoded text & WhatsApp |
| **Category Chips "All" Pill** | Missing | Missing | Missing |
| **Category Active State** | Missing | Missing | Missing |
| **Hero Image Fallback** | Hardcoded Unsplash photo | Hardcoded food card | Hardcoded clinic card |
| **Hero Fallback Image Check** | Omits `about_image_url` | Omits `about_image_url` | Omits `about_image_url` |
| **Visual Element Preserved** | Racing HUD & Carbon Bento | Warm Blurs & Soft Rounded Card | Pastel Gradient Glows & Round Pills |

---

## 4. Implementation Readiness & Exact Code Blueprints

Implementers will be able to apply changes surgically to each of the 9 files with zero guesswork. The exact contracts to be placed in each file are structured as follows:

1. **`hero.blade.php` Pattern**:
   - Universal PHP initialization block at the top resolving 8 standard variables (`$heroHeadline`, `$heroSubheadline`, `$heroEyebrow`, `$primaryCtaText`, `$primaryCtaUrl`, `$secondaryCtaText`, `$secondaryCtaUrl`, `$cmsValues`, `$badgePill1..3`, `$badgeIcon1..3`, `$heroImage`).
   - Preservation of layout grid, container classes, and aesthetic atmosphere (glows, carbon panels, font families).
   - Dynamic injection of variables into eyebrow, headline, subheadline, 3 badges, primary CTA, secondary CTA, and image fallback card.
2. **`features_banner.blade.php` Pattern**:
   - Resolution of `$cmsValues` and 4 universal pillars array.
   - Dynamic `@foreach ($pillars as $pillar)` loop rendering the 4 cards while retaining container classes, borders, and theme badge styling.
3. **`category_chips.blade.php` Pattern**:
   - Wrap with `@if ($productCategories->isNotEmpty() && $landingPage->isPageActive('catalog'))`.
   - Title fallback: `{{ $landingPage->services_title ?: __('storefront.kategori_pilihan') }}`.
   - Subtitle fallback: `{{ __('storefront.pilih_kategori_filter') }}`.
   - "Semua Produk" chip with `layout-grid` icon and active state check `!request()->filled('category')`.
   - Category chips with active state check `request('category') == $cat->id`, 44px min touch target, and theme button highlight.

This ensures zero regressions, 100% backward compatibility, and full compliance with the Universal Storefront Blueprint.
