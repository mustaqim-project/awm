# Milestone 2B (Batch 2 Themes Refactoring): Investigation & Audit Report
**Assigned Scope**: Themes 5 to 7 (`fnb_fastfood`, `fnb_resto`, `retail_bookstore`)  
**Investigating Agent**: `teamwork_explorer_m2b_2`  
**Date**: 2026-11-05  
**Working Directory**: `c:\laragon\www\cooca_core\.agents\teamwork\teamwork_explorer_m2b_2\`

---

## 1. Executive Summary & Scope Definition

In accordance with the Universal Storefront Blueprint (`STANDARISASI_DAN_IMPLEMENTASI_TEMA_UNIVERSAL.md`) and Milestone 2B requirements, this investigation audits 3 target themes in Batch 2 (F&B & Retail):
1. **Theme 5**: `fnb_fastfood` (Bundle: `fnb_fastfood`, Alias: `neon_crunch`) — Archetype: *Playful, Fresh & Appetite*
2. **Theme 6**: `fnb_resto` (Bundle: `fnb_resto`, Alias: `nusantara_feast`) — Archetype: *Playful, Fresh & Appetite*
3. **Theme 7**: `retail_bookstore` (Bundle: `retail_bookstore`, Alias: `bibliotheca`) — Archetype: *Playful, Fresh & Appetite*

### Core Investigation Findings
All 3 themes currently violate the Universal Storefront Directive in 3 critical areas:
1. **Sector Text Rigidity**: All 3 themes embed hardcoded sector marketing copy ("Burger daging sapi asli, ayam goreng ekstra krispi...", "Cita Rasa Otentik Rempah Warisan Tradisi...", "Jendela Ilmu, Inspirasi & Koleksi Buku Terbaik..."), preventing adoption by multi-sector businesses.
2. **Hardcoded Unsplash Media & Hex Values**:
   - `fnb_fastfood` hardcodes an Unsplash burger photo (`photo-1568901346375-23c9450c58cd`) and hex codes `#DC2626`, `#EA580C`.
   - `fnb_resto` hardcodes an Unsplash restaurant interior photo (`photo-1517248135467-4c7edcad34c4`) and hex codes `#991B1B`, `#B45309`.
   - `retail_bookstore` hardcodes an Unsplash reading photo (`photo-1512820790803-83ca734da794`) and hex codes `#1E293B`, `#0F172A`.
3. **Missing Universal Category Navigation Standards**:
   - All 3 themes lack the mandatory first "Semua Produk" (All Products) chip with `layout-grid` icon and active query check `!request()->filled('category')`.
   - All 3 themes lack active category chip highlighting (`request('category') == $cat->id`).
   - Category header text and subtitle are hardcoded to culinary/literary copy instead of dynamic CMS fallbacks.

The table below catalogs the inspected components:

| Theme Key | Bundle Dir | Heading Font | Body Font | Design Archetype | Inspected Files |
|-----------|------------|--------------|-----------|------------------|-----------------|
| `fnb_fastfood` | `fnb_fastfood` | Outfit | Inter | `playful_fresh` | `hero.blade.php`, `features_banner.blade.php`, `category_chips.blade.php` |
| `fnb_resto` | `fnb_resto` | DM Serif Display | Plus Jakarta Sans | `playful_fresh` | `hero.blade.php`, `features_banner.blade.php`, `category_chips.blade.php` |
| `retail_bookstore` | `retail_bookstore` | Merriweather | Inter | `playful_fresh` | `hero.blade.php`, `features_banner.blade.php`, `category_chips.blade.php` |

---

## 2. Theme 5: `fnb_fastfood` Detailed Audit & Blueprint

### 2.1 File: `resources/views/public/storefront/themes/fnb_fastfood/hero.blade.php`
- **Total Lines**: 82
- **Rigid Sector Strings Observed**:
  - Line 14: `{{ $activeTheme['industry'] ?? 'Fast Food & Burger Spesial' }}`
  - Line 17: `{{ $landingPage->headline ?: 'Renyah, Juicy & Puas di Setiap Gigitan!' }}`
  - Line 21: `{{ $landingPage->subheadline ?: 'Burger daging sapi asli, ayam goreng ekstra krispi, kentang goreng renyah bumbu gurih, dan paket combo hemat siap antar panas-panas.' }}`
  - Line 27: `<span>Ekstra Krispi & Panas</span>`
  - Line 31: `<span>Saji Kilat 15 Menit</span>`
  - Line 35: `<span>Promo Combo Hemat</span>`
  - Line 45: `<span>{{ $landingPage->cta_primary_text ?: 'Pesan Sekarang!' }}</span>`
  - Line 71: `<span class="font-black text-neutral-900 dark:text-white uppercase tracking-wider">Hot &amp; Freshly Made</span>`
  - Line 73: `<span class="text-red-600 font-black text-sm">Diskon Spesial</span>`
- **Hardcoded Media & Colors**:
  - Line 11: `style="background: {{ $activeTheme['badge_bg'] ?? '#DC262618' }}; color: {{ $activeTheme['badge_text'] ?? '#DC2626' }};"`
  - Line 43: `style="background: linear-gradient(135deg, #DC2626, #EA580C);"`
  - Line 51: `border-red-300 dark:border-red-800/50 hover:bg-red-50/50`
  - Line 62: `$heroImg = $landingPage->hero_image_url ?: 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=1000&auto=format&fit=crop&q=80';`
- **Contract Adaptations Needed (§3.1)**:
  1. Add standardized PHP initialization block resolving:
     - `$heroHeadline = $landingPage->headline ?: $business->name;`
     - `$heroSubheadline = $landingPage->subheadline ?: ($business->description ?: __('storefront.hero.tagline'));`
     - `$heroEyebrow = $landingPage->announcement_badge ?: ($activeTheme['industry'] ?? __('storefront.hero.verified_official'));`
     - `$primaryCtaText = $landingPage->cta_primary_text ?: __('storefront.hero.explore_catalog');`
     - `$primaryCtaUrl  = $landingPage->cta_primary_url ?: url('/' . $business->slug . '/katalog');`
     - `$secondaryCtaText = $landingPage->cta_secondary_text ?: __('storefront.hero.contact_us');`
     - `$secondaryCtaUrl  = $landingPage->cta_secondary_url ?: ($hasWhatsapp ? $landingPage->getWhatsAppUrl() : url('/' . $business->slug . '/kontak'));`
     - Dynamic 3-badge pills from `$cmsValues = $landingPage->getUniversalValues()`, falling back to `__('storefront.badges.*')`.
     - `$heroImage = $landingPage->hero_image_url ?: ($landingPage->about_image_url ?: null);`
  2. Implement Brand Bento Graphic Showcase fallback when `$heroImage` is null:
     - Retain Outfit high-energy styling, dark high-impact gradient `from-red-950 via-neutral-900 to-black`, business initials monogram with dynamic background `style="background-color: var(--theme-primary, #DC2626);"`.
  3. Replace the image overlay banner with dynamic HUD: `$heroEyebrow` and status `{{ __('storefront.hero.active_status') }}`.

### 2.2 File: `resources/views/public/storefront/themes/fnb_fastfood/features_banner.blade.php`
- **Total Lines**: 47
- **Rigid Sector Strings Observed**:
  - Line 11: `{{ __('storefront.saji_kilat_15_menit') }}`
  - Line 12: `Proses cepat tanpa antre lama saat lapar melanda.`
  - Line 21: `100% Real Beef & Chicken`
  - Line 22: `Daging pilihan tebal, juicy, dan bumbu meresap mantap.`
  - Line 31: `{{ __('storefront.paket_combo_lebih_hemat') }}`
  - Line 32: `Sudah lengkap burger/ayam + kentang + minuman dingin.`
  - Line 41: `Delivery Termal Khusus`
  - Line 42: `Kemasan isolasi menjaga burger & ayam tetap renyah panas.`
- **Hardcoded Colors**:
  - Hardcoded color classes: `bg-red-100 text-red-600 border-red-100`, `bg-amber-100 text-amber-700 border-amber-100`, `bg-emerald-100 text-emerald-700 border-emerald-100`, `bg-blue-100 text-blue-700 border-blue-100`.
- **Contract Adaptations Needed (§3.2)**:
  1. Replace static cards with dynamic loop:
     `$featureItems = $landingPage->getUniversalValues();`
  2. Iterate `@foreach($featureItems as $index => $item)` rendering dynamic `$item['icon']`, `$item['title']`, and `$item['desc'] ?? ($item['description'] ?? '')`.
  3. Retain high-energy rounded-2xl grid layout (`grid-cols-2 lg:grid-cols-4`, Outfit typography), using rotating color accents for icon containers (`red`, `amber`, `emerald`, `blue`) based on `$index % 4`.

### 2.3 File: `resources/views/public/storefront/themes/fnb_fastfood/category_chips.blade.php`
- **Total Lines**: 59
- **Rigid Sector Strings Observed**:
  - Line 8: `{{ __('storefront.kategori_menu_kilat') }}`
  - Line 10: `Pilih burger, ayam krispi, snack, atau paket hemat`
  - Line 22-35: `$catIcons` dictionary matching food keywords with fallback to `utensils`.
- **Defects & Hardcoded Styling**:
  - Missing "Semua Produk" chip.
  - Missing active category highlighting `request('category') == $cat->id`.
  - Hardcoded red classes: `text-red-600 dark:text-red-400`, `border-red-100 hover:border-red-500`, `bg-red-100 text-red-600`.
- **Contract Adaptations Needed (§3.3)**:
  1. Dynamic header: `$landingPage->services_title ?: __('storefront.kategori_pilihan')`.
  2. Dynamic subtitle: `__('storefront.pilih_kategori_filter')`.
  3. Prepend standard "Semua Produk" chip with `layout-grid` icon and active check `!request()->filled('category')`.
  4. Active category state binding on items (`theme-btn-primary shadow-md` when active).
  5. Category icon fallback changed from `utensils` to universal `tag`.
  6. Enforce touch accessibility minimum target `min-h-[46px]`.

---

## 3. Theme 6: `fnb_resto` Detailed Audit & Blueprint

### 3.1 File: `resources/views/public/storefront/themes/fnb_resto/hero.blade.php`
- **Total Lines**: 86
- **Rigid Sector Strings Observed**:
  - Line 14: `{{ $activeTheme['industry'] ?? 'Restoran & Kuliner Nusantara' }}`
  - Line 18: `{{ $landingPage->headline ?: 'Cita Rasa Otentik Rempah Warisan Tradisi' }}`
  - Line 22: `{{ $landingPage->subheadline ?: 'Nikmati hidangan istimewa racikan bumbu segar pilihan, dimasak langsung saat dipesan dengan kehangatan suasana santap bersama keluarga.' }}`
  - Line 29: `<span>Resep Otentik Turun-Temurun</span>`
  - Line 33: `<span>Bumbu Rempah Segar Asli</span>`
  - Line 37: `<span>100% Halal Certified</span>`
  - Line 47: `<span>{{ $landingPage->cta_primary_text ?: 'Buka Menu Santap' }}</span>`
  - Line 56: `<span>{{ __('storefront.reservasi_meja') }}</span>` (hardcoded reservation action)
  - Line 75: `<span class="font-semibold text-neutral-900 dark:text-white">Dine-in &amp; Takeaway Buka</span>`
  - Line 77: `<span class="text-amber-700 dark:text-amber-400 font-bold font-mono">Meja Tersedia</span>`
- **Hardcoded Media & Colors**:
  - Line 12: `style="background: {{ $activeTheme['badge_bg'] ?? '#991B1B15' }}; color: {{ $activeTheme['badge_text'] ?? '#991B1B' }};"`
  - Line 46: `style="background: linear-gradient(135deg, #991B1B, #B45309);"`
  - Line 54: `border-amber-300 dark:border-amber-800/50 hover:bg-amber-50/50`
  - Line 66: `$heroImg = $landingPage->hero_image_url ?: 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=1000&auto=format&fit=crop&q=80';`
- **Contract Adaptations Needed (§3.1)**:
  1. Add standardized PHP initialization block resolving:
     - `$heroHeadline = $landingPage->headline ?: $business->name;`
     - `$heroSubheadline = $landingPage->subheadline ?: ($business->description ?: __('storefront.hero.tagline'));`
     - `$heroEyebrow = $landingPage->announcement_badge ?: ($activeTheme['industry'] ?? __('storefront.hero.verified_official'));`
     - `$primaryCtaText = $landingPage->cta_primary_text ?: __('storefront.hero.explore_catalog');`
     - `$primaryCtaUrl  = $landingPage->cta_primary_url ?: url('/' . $business->slug . '/katalog');`
     - `$secondaryCtaText = $landingPage->cta_secondary_text ?: __('storefront.hero.contact_us');`
     - `$secondaryCtaUrl  = $landingPage->cta_secondary_url ?: ($hasWhatsapp ? $landingPage->getWhatsAppUrl() : url('/' . $business->slug . '/kontak'));`
     - Dynamic 3-badge pills from `$cmsValues = $landingPage->getUniversalValues()`.
     - `$heroImage = $landingPage->hero_image_url ?: ($landingPage->about_image_url ?: null);`
  2. Implement warm DM Serif Display Brand Bento Showcase card when `$heroImage` is null:
     - Rich gradient `from-red-950 via-amber-950 to-neutral-900`, DM Serif Display typography, warm terracotta monogram `style="background-color: var(--theme-primary, #991B1B);"`.
  3. Unbind hardcoded `/reservasi` button: allow universal secondary CTA (WhatsApp or custom URL) with fallback text `$secondaryCtaText`.
  4. Floating overlay card: Replace "Dine-in Buka / Meja Tersedia" with `$heroEyebrow` and status indicators.

### 3.2 File: `resources/views/public/storefront/themes/fnb_resto/features_banner.blade.php`
- **Total Lines**: 47
- **Rigid Sector Strings Observed**:
  - Line 11: `{{ __('storefront.dimasak_langsung_segar') }}`
  - Line 12: `Sajian panas mengepul, dibuat saat pesanan masuk.`
  - Line 21: `Rempah Alamiah Pilihan`
  - Line 22: `Kaya cita rasa asli nusantara tanpa perisa berlebihan.`
  - Line 31: `Ruang Nyaman Keluarga`
  - Line 32: `Tersedia area ber-AC, saung lesehan & parkir luas.`
  - Line 41: `{{ __('storefront.reservasi_meja_acara') }}`
  - Line 42: `Booking meja online bebas antre untuk arisan & reuni.`
- **Hardcoded Icons & Colors**:
  - Sector icons: `chef-hat`, `flame`, `users`, `calendar`.
  - Icon backgrounds: `bg-red-500/10 text-red-700`, `bg-amber-500/10 text-amber-700`, `bg-emerald-500/10 text-emerald-700`, `bg-blue-500/10 text-blue-700`.
- **Contract Adaptations Needed (§3.2)**:
  1. Replace static cards with dynamic loop:
     `$featureItems = $landingPage->getUniversalValues();`
  2. Iterate `@foreach($featureItems as $index => $item)` with dynamic `$item['icon']`, `$item['title']`, `$item['desc']`.
  3. Retain warm DM Serif card geometry: `rounded-2xl bg-white dark:bg-[#181E2A] border border-amber-100 dark:border-white/10 shadow-sm`.
  4. Maintain warm palette icon accents using rotating classes (`red-500/10`, `amber-500/10`, `emerald-500/10`, `blue-500/10`).

### 3.3 File: `resources/views/public/storefront/themes/fnb_resto/category_chips.blade.php`
- **Total Lines**: 60
- **Rigid Sector Strings Observed**:
  - Line 8: `{{ __('storefront.pilihan_daftar_menu') }}`
  - Line 10: `Aneka lauk pauk, nasi spesial, sup berkuah & minuman segar`
  - Line 15: `{{ __('storefront.view_all_menus') }}`
  - Lines 21-36: Food keywords matching dictionary with fallback to `utensils`.
- **Defects & Hardcoded Styling**:
  - Missing "Semua Produk" chip.
  - Missing active category highlighting `request('category') == $cat->id`.
  - Hardcoded classes: `text-red-700 dark:text-red-400`, `border-red-100 hover:border-red-400`, `bg-red-50 text-red-700`.
- **Contract Adaptations Needed (§3.3)**:
  1. Dynamic header: `$landingPage->services_title ?: __('storefront.kategori_pilihan')`.
  2. Dynamic subtitle: `__('storefront.pilih_kategori_filter')`.
  3. Standardized view-all link: `{{ __('storefront.view_all') }}`.
  4. Prepend standard "Semua Produk" chip with `layout-grid` icon and active check `!request()->filled('category')`.
  5. Active category state binding on items (`theme-btn-primary shadow-xs` when active).
  6. Category icon fallback changed from `utensils` to universal `tag`.
  7. Enforce touch accessibility minimum target `min-h-[46px]`.

---

## 4. Theme 7: `retail_bookstore` Detailed Audit & Blueprint

### 4.1 File: `resources/views/public/storefront/themes/retail_bookstore/hero.blade.php`
- **Total Lines**: 82
- **Rigid Sector Strings Observed**:
  - Line 14: `{{ $activeTheme['industry'] ?? 'Toko Buku & Literasi Original' }}`
  - Line 17: `{{ $landingPage->headline ?: 'Jendela Ilmu, Inspirasi & Koleksi Buku Terbaik' }}`
  - Line 21: `{{ $landingPage->subheadline ?: 'Koleksi buku fiksi best seller, literatur bisnis, pengembangan diri, buku anak berilustrasi, alat tulis premium dan piringan hitam musik original.' }}`
  - Line 27: `<span>100% Buku Original Penerbit</span>`
  - Line 31: `<span>Gratis Pembatas Buku Eksklusif</span>`
  - Line 35: `<span>Packing Kardus & Bubble Tebal</span>`
  - Line 44: `<span>{{ $landingPage->cta_primary_text ?: 'Jelajahi Rak Buku' }}</span>`
  - Line 53: `<span>Tanya Ketersediaan Judul</span>`
  - Line 71: `<span class="font-bold text-neutral-900 dark:text-white">Buku Segel Penerbit Resmi</span>`
  - Line 73: `<span class="text-slate-800 dark:text-slate-200 font-bold font-mono">100% Ori</span>`
- **Hardcoded Media & Colors**:
  - Line 11: `style="background: {{ $activeTheme['badge_bg'] ?? '#1E293B10' }}; color: {{ $activeTheme['badge_text'] ?? '#1E293B' }};"`
  - Line 43: `style="background: linear-gradient(135deg, #1E293B, #0F172A);"`
  - Line 51: `border-slate-300 dark:border-slate-700 hover:bg-slate-50/50`
  - Line 62: `$heroImg = $landingPage->hero_image_url ?: 'https://images.unsplash.com/photo-1512820790803-83ca734da794?w=1000&auto=format&fit=crop&q=80';`
- **Contract Adaptations Needed (§3.1)**:
  1. Add standardized PHP initialization block resolving:
     - `$heroHeadline = $landingPage->headline ?: $business->name;`
     - `$heroSubheadline = $landingPage->subheadline ?: ($business->description ?: __('storefront.hero.tagline'));`
     - `$heroEyebrow = $landingPage->announcement_badge ?: ($activeTheme['industry'] ?? __('storefront.hero.verified_official'));`
     - `$primaryCtaText = $landingPage->cta_primary_text ?: __('storefront.hero.explore_catalog');`
     - `$primaryCtaUrl  = $landingPage->cta_primary_url ?: url('/' . $business->slug . '/katalog');`
     - `$secondaryCtaText = $landingPage->cta_secondary_text ?: __('storefront.hero.contact_us');`
     - `$secondaryCtaUrl  = $landingPage->cta_secondary_url ?: ($hasWhatsapp ? $landingPage->getWhatsAppUrl() : url('/' . $business->slug . '/kontak'));`
     - Dynamic 3-badge pills from `$cmsValues = $landingPage->getUniversalValues()`.
     - `$heroImage = $landingPage->hero_image_url ?: ($landingPage->about_image_url ?: null);`
  2. Implement classic Merriweather Bibliotheca Brand Bento Graphic Showcase card when `$heroImage` is null:
     - Parchment-tinted dark aesthetic `bg-gradient-to-br from-slate-900 via-neutral-900 to-[#1E293B]`, Merriweather font, scholarly monogram `style="background-color: var(--theme-primary, #1E293B);"`.
  3. Secondary CTA: Replace hardcoded "Tanya Ketersediaan Judul" with dynamic `$secondaryCtaText`.
  4. Floating overlay card: Replace "Buku Segel Penerbit Resmi / 100% Ori" with dynamic `$heroEyebrow` and verified indicators.

### 4.2 File: `resources/views/public/storefront/themes/retail_bookstore/features_banner.blade.php`
- **Total Lines**: 47
- **Rigid Sector Strings Observed**:
  - Line 11: `100% Original Penerbit`
  - Line 12: `Buku resmi bebas bajakan dengan royalti resmi penulis.`
  - Line 21: `Free Bookmark & Stiker`
  - Line 22: `Bonus pembatas buku cantik di setiap pesanan.`
  - Line 31: `Packing Sudut Kardus`
  - Line 32: `Ujung buku anti penyok terlindung bubble wrap rapat.`
  - Line 41: `Request Buku Langka`
  - Line 42: `Bisa titip cari judul buku klasik atau terbitan lama.`
- **Hardcoded Icons & Colors**:
  - Icons: `shield-check`, `bookmark`, `package`, `sparkles`.
  - Backgrounds: `bg-slate-100`, `bg-amber-500/10`, `bg-blue-500/10`, `bg-emerald-500/10`.
- **Contract Adaptations Needed (§3.2)**:
  1. Replace static cards with dynamic loop:
     `$featureItems = $landingPage->getUniversalValues();`
  2. Iterate `@foreach($featureItems as $index => $item)` with dynamic `$item['icon']`, `$item['title']`, `$item['desc']`.
  3. Retain scholarly card geometry: `rounded-2xl bg-white dark:bg-[#181E2A] border border-slate-200 dark:border-white/10 shadow-sm`.
  4. Maintain subtle rotating accents (`slate-100`, `amber-500/10`, `blue-500/10`, `emerald-500/10`).

### 4.3 File: `resources/views/public/storefront/themes/retail_bookstore/category_chips.blade.php`
- **Total Lines**: 61
- **Rigid Sector Strings Observed**:
  - Line 9: `Klasifikasi Rak Buku`
  - Line 12: `Fiksi, non-fiksi, pengembangan diri, komik & stationery`
  - Lines 24-37: Bookstore/stationery keyword matching dictionary with fallback to `book-open`.
- **Defects & Hardcoded Styling**:
  - Missing "Semua Produk" chip.
  - Missing active category highlighting `request('category') == $cat->id`.
  - Hardcoded classes: `border-slate-200 hover:border-slate-800`, `bg-amber-50 text-amber-800`.
- **Contract Adaptations Needed (§3.3)**:
  1. Dynamic header: `$landingPage->services_title ?: __('storefront.kategori_pilihan')`.
  2. Dynamic subtitle: `__('storefront.pilih_kategori_filter')`.
  3. Standardized view-all link: `{{ __('storefront.view_all') }}`.
  4. Prepend standard "Semua Produk" chip with `layout-grid` icon and active check `!request()->filled('category')`.
  5. Active category state binding on items (`theme-btn-primary shadow-xs` when active).
  6. Category icon fallback changed from `book-open` to universal `tag`.
  7. Enforce touch accessibility minimum target `min-h-[46px]`.

---

## 5. Architectural Synthesis & Design Personality Preservation

All 3 audited themes are grouped under Archetype 5: **`playful_fresh` (Playful, Fresh & Appetite)**. However, each theme features a distinct visual persona that must be preserved:

| Feature / Dimension | Theme 5: `fnb_fastfood` | Theme 6: `fnb_resto` | Theme 7: `retail_bookstore` |
|---------------------|------------------------|----------------------|-----------------------------|
| **Theme Aesthetic** | High-energy Neon Crunch | Nusantara Feast Warm Dining | Bibliotheca Scholarly Sanctuary |
| **Heading Font** | `Outfit` (Black 800/900) | `DM Serif Display` (Serif) | `Merriweather` (Classic Serif) |
| **Body Font** | `Inter` | `Plus Jakarta Sans` | `Inter` |
| **Border Radius** | `18px` (`rounded-2xl`, `rounded-3xl`) | `16px` (`rounded-2xl`) | `16px` (`rounded-2xl`) |
| **Vibe & Atmosphere** | Punchy, dynamic, high-voltage glow | Warm, hearth-cooked, fragrant spice | Quiet sanctuary, literary parchment |
| **Primary Button** | `theme-btn-primary hover:scale-105 active:scale-95` | `theme-btn-primary hover:-translate-y-0.5` | `theme-btn-primary hover:-translate-y-0.5` |
| **Secondary Button** | High-contrast border, rounded-2xl | Amber warm border, rounded-2xl | Slate refined border, rounded-2xl |
| **Brand Bento Showcase** | Bold initial monogram, high-contrast HUD | Rich terracotta/amber gradient, serif monogram | Deep slate/parchment gradient, classic monogram |

---

## 6. Implementation Action Plan for Worker

When the implementer worker undertakes Milestone 2B for Themes 5 to 7, the following sequence should be followed for each theme:
1. **Apply §3.1 Hero Contract (`hero.blade.php`)**:
   - Paste standardized 3-tier fallback PHP block at top.
   - Replace static eyebrow, headline, subheadline, 3 badges, and CTAs.
   - Replace static Unsplash photo fallback with the theme-specific Brand Bento Graphic Showcase card.
   - Replace static overlay pill with dynamic `$heroEyebrow` & active status indicator.
2. **Apply §3.2 Features Banner Contract (`features_banner.blade.php`)**:
   - Loop dynamically over `$featureItems = $landingPage->getUniversalValues()`.
   - Ensure clean 4-card grid retaining specific theme border, background, and icon geometries.
3. **Apply §3.3 Category Chips Contract (`category_chips.blade.php`)**:
   - Set dynamic title (`services_title ?: __('storefront.kategori_pilihan')`) and subtitle.
   - Add first "Semua Produk" chip with `layout-grid` icon and `!request()->filled('category')` check.
   - Add active styling to items when `request('category') == $cat->id`.
   - Replace sector-specific fallback icons (`utensils`, `book-open`) with `tag`.
   - Ensure `min-h-[44px]` (or `min-h-[46px]`) touch target.
4. **Syntax & Lint Verification**:
   - Run `php -l` on all 9 modified files.
   - Run existing feature tests: `php artisan test --filter=StorefrontUniversalContractTest`.
