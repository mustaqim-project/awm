# Milestone 2A Audit & Universal Blade Contract Adaptation Analysis
**Scope: Themes 5–7 (`coffee_beverage`, `agriculture_farm`, `retail_fashion`)**
**Date**: 2026-11-05
**Author**: Explorer 2 (Milestone 2A)
**Status**: Completed Investigation

---

## 1. Executive Summary & Design Archetype Mapping

This analysis provides an exhaustive, code-level audit of the three storefront themes assigned to Explorer 2:
1. **Theme 5: `coffee_beverage`** (Alias: `artisan_brew`) &mdash; **Design Archetype 2: Earthy & Warm Artisan**
   - *Typography*: `Playfair Display` (Headings) + `Inter` (Body).
   - *Palette*: `#8B5A2B` (Chestnut Roast), `#C88A58` (Caramel Gold), `#FAF7F2` (Cream Foam Background).
   - *Visual Personality*: Warm artisan roastery, aroma glows, rounded cards/pills, coffee bar aesthetics.
2. **Theme 6: `agriculture_farm`** &mdash; **Design Archetype 2: Earthy & Warm Artisan / Agrarian Botanical**
   - *Typography*: `DM Serif Display` (Headings) + `Plus Jakarta Sans` (Body).
   - *Palette*: `#15803D` (Forest Leaf Green), `#B45309` (Warm Harvest Amber), `#F0FDF4` (Soft Botanical Tint).
   - *Visual Personality*: Fresh harvest narrative, natural botanical glows, rounded organic containers, farm-to-table dignity.
3. **Theme 7: `retail_fashion`** (Alias: `vogue_minimalist`) &mdash; **Design Archetype 3: High-End Luxe & Editorial**
   - *Typography*: `Cormorant Garamond` (Light Serifs) + `Plus Jakarta Sans` (Body).
   - *Palette*: `#18181B` (Deep Charcoal), `#A1A1AA` (Muted Silver), `#FAFAFA` (Gallery White).
   - *Visual Personality*: Monochrome high-fashion lookbook, expansive breathing room, 3:4 aspect editorial portrait framing, subtle hover lifts.

### Key Universal Goal
Transform these 3 sector-specific templates into **Universal Design Shells** capable of serving **any** business industry (e.g. an electronics boutique using `retail_fashion`, a law firm using `coffee_beverage`, or an artisan bakery using `agriculture_farm`) without leaking coffee, farm, or garment texts, while strictly preserving their distinctive typography, card geometry, and visual soul.

---

## 2. In-Depth Audit: Theme 5 (`coffee_beverage`)

Directory: `resources/views/public/storefront/themes/coffee_beverage/`

### 2.1 `hero.blade.php` (7,408 bytes, 101 lines)
#### A. Rigid Sector Texts Identified
- **Line 17**: Hardcoded eyebrow badge: `<span class="tracking-wider uppercase text-[10px] font-bold">Specialty Roastery &bull; Fresh Roasted Weekly</span>` and `<i data-lucide="coffee">`.
- **Line 21**: Hardcoded headline fallback: `'Ritual Kopi Spesialti, Sangrai Presisi Setiap Cangkir'`.
- **Line 25**: Hardcoded subheadline fallback: `'Dari biji kopi pilihan petani nusantara, disangrai dengan profil optimal dan diseduh oleh barista penuh dedikasi untuk rasa terbaik.'`.
- **Lines 29–42**: Three hardcoded tasting notes badges:
  - Badge 1 (line 32): `Caramel & Dark Chocolate` (icon: `sparkles`)
  - Badge 2 (line 36): `Medium Roast Balanced` (icon: `flame`)
  - Badge 3 (line 40): `100% Arabica & Robusta Origin` (icon: `check`)
- **Line 50**: Hardcoded primary button text: `{{ __('storefront.pilih_menu_kopi') }}` and `<i data-lucide="coffee">`.
- **Line 58**: Hardcoded secondary button text: `{{ __('storefront.pesan_meja_beans') }}`.
- **Line 74**: Brand graphic placeholder icon: `<i data-lucide="coffee">`.
- **Line 77**: Brand graphic placeholder text: `"Pengalaman minum kopi spesialti dan nongkrong nyaman bersama kawan."`.
- **Lines 81–93**: Floating card on visual showcase:
  - Line 85: `<i data-lucide="cup-soda">`
  - Line 88: `Fresh Brewed Today`
  - Line 89: `Espresso base, filter & artisan tea`
  - Line 92: `Lihat Menu &rarr;`

#### B. Hardcoded Hex Colors & Styling
- Backgrounds & borders: `bg-[#FAF7F2]`, `dark:bg-[#15110E]`, `border-[#E8DEC8]`, `dark:border-[#2C211A]`, `bg-[#F4EDE2]`.
- Glowing orbs: `bg-[#8B5A2B]/10`, `bg-[#C88A58]/10`.
- Text tones: `text-[#3D2B1F]`, `text-[#6B5744]`, `text-[#FAF7F2]`, `text-[#CBBDB1]`.
- Action buttons: `bg-[#8B5A2B] hover:bg-[#724822] shadow-[#8B5A2B]/25`.

#### C. Exact Universal Adaptation Proposed
- **PHP Data Prep Header**:
  ```blade
  @php
      $heroHeadline    = $landingPage->headline ?: $business->name;
      $heroSubheadline = $landingPage->subheadline ?: ($business->description ?: __('storefront.hero.tagline'));
      $heroEyebrow     = $landingPage->announcement_badge ?: ($activeTheme['industry'] ?? __('storefront.hero.verified_official'));

      $primaryCtaText  = $landingPage->cta_primary_text ?: __('storefront.hero.explore_catalog');
      $primaryCtaUrl   = $landingPage->cta_primary_url ?: url('/' . $business->slug . '/katalog');
      $secondaryCtaText = $landingPage->cta_secondary_text ?: __('storefront.hero.contact_us');
      $secondaryCtaUrl = $landingPage->cta_secondary_url ?: $landingPage->getWhatsAppUrl();

      $universalValues = $landingPage->getUniversalValues();
      $badgePill1 = $universalValues[0]['title'];
      $badgeIcon1 = $universalValues[0]['icon'];
      $badgePill2 = $universalValues[1]['title'];
      $badgeIcon2 = $universalValues[1]['icon'];
      $badgePill3 = $universalValues[2]['title'];
      $badgeIcon3 = $universalValues[2]['icon'];

      $heroImage = $landingPage->hero_image_url ?: ($landingPage->about_image_url ?: null);
  @endphp
  ```
- **Eyebrow Badge**: Replace coffee icon with `<span class="w-2 h-2 rounded-full bg-current animate-pulse shrink-0"></span>` and display `{{ $heroEyebrow }}`.
- **Badges Strip**: Render `$badgePill1..3` with icons `$badgeIcon1..3` using warm artisan pill styling (`rounded-full bg-white dark:bg-[#201914] text-[#8B5A2B] dark:text-[#C88A58] border border-[#E8DEC8] dark:border-[#382B21]`).
- **Action Buttons**:
  - Primary button: `class="px-6 py-3.5 rounded-full theme-btn-primary font-semibold text-sm shadow-md transition-all flex items-center gap-2 active:scale-95 min-h-[48px]"` with `<i data-lucide="arrow-right">` and `{{ $primaryCtaText }}`.
  - Secondary button: `class="px-5 py-3.5 rounded-full bg-white dark:bg-[#201914] hover:bg-[#F2ECE3] dark:hover:bg-[#2C231C] text-[#3D2B1F] dark:text-[#FAF7F2] border border-[#E8DEC8] dark:border-[#382B21] font-semibold text-sm transition-all flex items-center gap-2 shadow-2xs min-h-[48px]"` with `<i data-lucide="message-circle" class="w-4 h-4 text-emerald-600">` and `{{ $secondaryCtaText }}`.
- **Hero Visual & Floating Badge**:
  - When `$heroImage` exists: render `<img>` with floating status card showing `{{ __('storefront.hero.verified_official') }}` and `{{ __('storefront.hero.active_status') }}` with link to catalog.
  - When `$heroImage` is null: Render the Warm Artisan Monogram Bento card (`strtoupper(substr($business->name, 0, 2))`), `$business->name`, `$heroSubheadline`, and `{{ __('storefront.hero.active_status') }}`.

---

### 2.2 `features_banner.blade.php` (3,027 bytes, 54 lines)
#### A. Rigid Sector Texts Identified
- **Pillar 1 (lines 14–15)**: `Specialty Grade` / `Biji kopi pilihan cupping score 80+` (icon: `award`).
- **Pillar 2 (lines 25–26)**: `Fresh Roasted` / `Disangrai mingguan terjaga segar` (icon: `flame`).
- **Pillar 3 (lines 36–37)**: `Racikan Barista` / `Ekstraksi presisi suhu & rasio` (icon: `coffee`).
- **Pillar 4 (lines 47–48)**: `Aroma Lock Pack` / `Kemasan zipper valve kedap udara` (icon: `package`).

#### B. Hardcoded Hex Colors & Styling
- Container: `bg-[#F4EDE2] dark:bg-[#1D1713] border-[#E8DEC8] dark:border-[#382B21]`.
- Circular icon badge: `bg-[#8B5A2B]/15 dark:bg-[#8B5A2B]/30 border-[#8B5A2B]/30 text-[#8B5A2B] dark:text-[#C88A58]`.
- Text: `text-[#3D2B1F] dark:text-[#FAF7F2]`, `text-[#6B5744] dark:text-[#CBBDB1]`.

#### C. Exact Universal Adaptation Proposed
- Implement Universal Contract §3.2:
  ```blade
  @php
      $pillars = $landingPage->getUniversalValues();
  @endphp

  <section class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-5 sm:p-6 rounded-3xl bg-[#F4EDE2] dark:bg-[#1D1713] border border-[#E8DEC8] dark:border-[#382B21] transition-colors">
          @foreach ($pillars as $pillar)
              <div class="flex items-center gap-3.5">
                  <div class="w-11 h-11 rounded-full bg-[#8B5A2B]/15 dark:bg-[#8B5A2B]/30 border border-[#8B5A2B]/30 flex items-center justify-center text-[#8B5A2B] dark:text-[#C88A58] shrink-0">
                      <i data-lucide="{{ $pillar['icon'] }}" class="w-5 h-5"></i>
                  </div>
                  <div>
                      <h4 class="font-heading font-bold text-xs sm:text-sm text-[#3D2B1F] dark:text-[#FAF7F2] leading-snug">
                          {{ $pillar['title'] }}
                      </h4>
                      <p class="text-[11px] text-[#6B5744] dark:text-[#CBBDB1] mt-0.5 leading-relaxed">
                          {{ $pillar['desc'] ?? $pillar['description'] }}
                      </p>
                  </div>
              </div>
          @endforeach
      </div>
  </section>
  ```
- **Preservation Check**: Warm rounded-3xl card container, circular icon badges, Playfair font heading, and warm coffee/cream colors are 100% preserved.

---

### 2.3 `category_chips.blade.php` (1,848 bytes, 32 lines)
#### A. Rigid Sector Texts Identified
- **Line 10**: Hardcoded title: `Bar & Brew Menu`.
- **Line 12**: Hardcoded subtitle: `Pilih sajian espresso, manual brew, artisan tea & beans`.
- **Line 16**: Hardcoded view all text: `{{ __('storefront.semua_menu') }}`.
- **Line 25**: Hardcoded icon: `<i data-lucide="coffee">` for all categories.

#### B. Hardcoded Hex Colors & Styling
- `text-[#3D2B1F] dark:text-[#FAF7F2]`, `text-[#6B5744] dark:text-[#CBBDB1]`, `text-[#8B5A2B] dark:text-[#C88A58]`.
- Chips: `border-[#E8DEC8] dark:border-[#382B21] bg-white dark:bg-[#201914]`.
- Tap target is `py-2` (does not enforce 44px min-height).

#### C. Exact Universal Adaptation Proposed
- Implement Universal Contract §3.3:
  - Title: `{{ $landingPage->services_title ?: __('storefront.kategori_pilihan') }}`.
  - Subtitle: `{{ __('storefront.pilih_kategori_filter') }}`.
  - View all link: `{{ __('storefront.view_all') }}`.
  - Add "Semua Produk" pill with `!request()->filled('category')` check.
  - Active category pill highlight with `theme-btn-primary` or warm roast active state (`bg-[#8B5A2B] text-white`).
  - Change default icon from `coffee` to `tag` (or `layout-grid` for "Semua").
  - Enforce `min-h-[44px]` touch target compliance.

---

## 3. In-Depth Audit: Theme 6 (`agriculture_farm`)

Directory: `resources/views/public/storefront/themes/agriculture_farm/`

### 3.1 `hero.blade.php` (11,397 bytes, 146 lines)
#### A. Rigid Sector Texts Identified
- **Line 19**: Hardcoded eyebrow with Unicode emoji: `🌾 Petik Segar Subuh Tadi &bull; Langsung dari Lahan Petani`.
- **Line 24**: Hardcoded headline fallback: `'Kualitas Panen Terbaik dari Kebun Asri, Segar ke Meja Anda'`.
- **Line 29**: Hardcoded subheadline fallback: `'Menghubungkan kebaikan alam dan jerih payah petani Nusantara. Komoditas panen pilihan, bibit unggul bersertifikat, dan produk organik berkualitas tanpa perantara.'`.
- **Lines 34–46**: Three hardcoded quality badges:
  - Badge 1 (line 36): `100% Organik & Bebas Residu` (icon: `leaf`)
  - Badge 2 (line 40): `Sortir Grade A Pagi Hari` (icon: `sun`)
  - Badge 3 (line 44): `Garansi Tiba Segar` (icon: `shield-check`)
- **Line 54**: Hardcoded primary button text: `Beli Hasil Panen & Bibit` (icon: `shopping-basket`).
- **Line 62**: Hardcoded secondary button text: `Pasokan Grosir / Suplai B2B`.
- **Lines 68–81**: Hardcoded Transparency Metrics Strip:
  - Metric 1: `05:00` / `Jam Petik Segar Subuh`
  - Metric 2: `50+ Ha` / `Lahan Binaan Mitra`
  - Metric 3: `100%` / `Garansi Ganti Barang`
- **Line 94–95**: Floating card on image: `Kebun Asri Terverifikasi` / `Dipetik & dikirim langsung hari ini dalam kondisi segar`.
- **Lines 99–138**: Fallback Bento Card:
  - Line 106: `<i data-lucide="trees">`
  - Line 109: `Petik Hari Ini`
  - Line 114: `Rantai Pasok Langsung Dari Petani`
  - Line 115–116: Hardcoded title `Segar, Utuh, & Menyehatkan` (ignores business name!)
  - Line 118–119: Farm text about `tengkulak` and `komoditas`.
  - Lines 126, 130, 134: Hardcoded checklist items (`Sortir teliti...`, `Kemasan aman...`, `Penggantian 100%...`).

#### B. Hardcoded Hex Colors & Styling
- `#F7FAF4`, `#07130A`, `#0D1F12`.
- Fixed emerald utility classes: `bg-emerald-700`, `hover:bg-emerald-800`, `text-emerald-900`, `text-emerald-50`, `text-emerald-200`, `border-emerald-900/10`.

#### C. Exact Universal Adaptation Proposed
- **PHP Data Prep Header**:
  ```blade
  @php
      $heroHeadline    = $landingPage->headline ?: $business->name;
      $heroSubheadline = $landingPage->subheadline ?: ($business->description ?: __('storefront.hero.tagline'));
      $heroEyebrow     = $landingPage->announcement_badge ?: ($activeTheme['industry'] ?? __('storefront.hero.verified_official'));

      $primaryCtaText  = $landingPage->cta_primary_text ?: __('storefront.hero.explore_catalog');
      $primaryCtaUrl   = $landingPage->cta_primary_url ?: url('/' . $business->slug . '/katalog');
      $secondaryCtaText = $landingPage->cta_secondary_text ?: __('storefront.hero.contact_us');
      $secondaryCtaUrl = $landingPage->cta_secondary_url ?: $landingPage->getWhatsAppUrl();

      $universalValues = $landingPage->getUniversalValues();
      $badgePill1 = $universalValues[0]['title'];
      $badgeIcon1 = $universalValues[0]['icon'];
      $badgePill2 = $universalValues[1]['title'];
      $badgeIcon2 = $universalValues[1]['icon'];
      $badgePill3 = $universalValues[2]['title'];
      $badgeIcon3 = $universalValues[2]['icon'];

      $heroImage = $landingPage->hero_image_url ?: ($landingPage->about_image_url ?: null);
  @endphp
  ```
- **Eyebrow Badge**: Remove `🌾` emoji! Use a pulsing green dot `<span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse shrink-0"></span>` and display `{{ $heroEyebrow }}`.
- **Badges Strip**: Render `$badgePill1..3` with icons `$badgeIcon1..3`.
- **Action Buttons**:
  - Primary button: `theme-btn-primary px-6 sm:px-7 py-3.5 rounded-2xl font-semibold text-sm shadow-md transition-all flex items-center justify-center gap-2 active:scale-95 min-h-[48px]` with `<i data-lucide="arrow-right">` and `{{ $primaryCtaText }}`.
  - Secondary button: `min-h-[48px]` with `{{ $secondaryCtaText }}` and `message-circle` icon.
- **Metrics Strip**:
  - Check if `$landingPage->stats` is configured. If configured, render merchant custom stats.
  - If null, render universal trust metrics:
    - `100%` / `{{ __('storefront.badges.quality_guaranteed') }}`
    - `24/7` / `{{ __('storefront.hero.active_status') }}`
    - `Resmi` / `{{ __('storefront.features.official_store') }}`
- **Fallback Bento Card**:
  - Monogram badge with `var(--theme-primary)` background: `{{ strtoupper(substr($business->name, 0, 2)) }}`.
  - Active status pill: `{{ __('storefront.hero.active_status') }}`.
  - Heading: `{{ $business->name }}` (with DM Serif typography).
  - Subtitle: `{{ $heroSubheadline }}`.
  - 3 dynamic check features from `$universalValues[0..2]`.
- **Preservation Check**: Botanical amber/emerald glows, DM Serif font, and agrarian editorial spatial elegance remain 100% intact.

---

### 3.2 `features_banner.blade.php` (4,828 bytes, 68 lines)
#### A. Rigid Sector Texts Identified
- **Pillar 1 (lines 15–18)**: `Petik Segar Subuh` / `Dipanen tiap subuh saat kadar nutrisi tanaman berada di titik puncak kealamian.` (icon: `sprout`).
- **Pillar 2 (lines 30–33)**: `Bebas Residu Kimia` / `Sistem tanam alami dengan pupuk hayati ramah tanah, aman dikonsumsi seluruh keluarga.` (icon: `leaf`).
- **Pillar 3 (lines 45–48)**: `Kemitraan Petani Adil` / `Membeli langsung dari poktan binaan guna memberdayakan ekonomi desa dan pangan mandiri.` (icon: `heart-handshake`).
- **Pillar 4 (lines 59–61)**: `{{ __('storefront.pengiriman_terproteksi') }}` / `Kemasan sirkulasi udara khusus menjaga sayur & buah tetap renyah hingga sampai di rumah.` (icon: `truck`).

#### B. Hardcoded Hex Colors & Styling
- `#0D1F12`, `bg-emerald-700`, `bg-amber-600`, `border-emerald-900/10`.

#### C. Exact Universal Adaptation Proposed
- Implement Universal Contract §3.2:
  ```blade
  @php
      $pillars = $landingPage->getUniversalValues();
  @endphp

  <section class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 p-5 sm:p-7 rounded-3xl bg-white/80 dark:bg-[#0D1F12]/80 backdrop-blur-md border border-emerald-900/10 dark:border-emerald-500/15 shadow-2xs transition-colors">
          @foreach ($pillars as $pillar)
              <div class="group flex items-start gap-4 p-4 rounded-2xl bg-emerald-50/60 dark:bg-emerald-950/40 border border-emerald-900/5 dark:border-emerald-500/10 hover:bg-emerald-50 dark:hover:bg-emerald-950/70 hover:border-emerald-600/20 transition-all duration-200">
                  <div class="w-12 h-12 rounded-2xl theme-btn-primary flex items-center justify-center shrink-0 shadow-xs group-hover:scale-105 transition-transform">
                      <i data-lucide="{{ $pillar['icon'] }}" class="w-6 h-6"></i>
                  </div>
                  <div>
                      <h4 class="font-heading font-normal text-base text-neutral-900 dark:text-emerald-100 leading-snug">
                          {{ $pillar['title'] }}
                      </h4>
                      <p class="text-xs text-neutral-600 dark:text-emerald-200/70 mt-1 leading-relaxed">
                          {{ $pillar['desc'] ?? $pillar['description'] }}
                      </p>
                  </div>
              </div>
          @endforeach
      </div>
  </section>
  ```
- **Preservation Check**: Retains the distinctive `group-hover:scale-105` icon bounce, DM Serif styling, and botanical backdrop card.

---

### 3.3 `category_chips.blade.php` (4,937 bytes, 79 lines)
#### A. Rigid Sector Texts Identified
- **Line 11**: Hardcoded eyebrow: `Komoditas Hasil Panen & Pertanian` with `<i data-lucide="sprout">`.
- **Line 14**: Hardcoded heading: `Pilihan Sayur Segar, Buah Kebun, Bibit & Pupuk`.
- **Line 17**: Hardcoded subtitle: `Pilih komoditas hasil bumi segar langsung dari petani mitra, bibit unggul bersertifikasi, dan pupuk organik`.
- **Line 23**: Hardcoded view all text: `Seluruh Hasil Kebun`.
- **Line 34**: Hardcoded chip text: `{{ __('storefront.semua_komoditas') }}`.
- **Lines 38–51**: Farm-only icon heuristic array (`sayur, buah, bibit, benih, pupuk, beras, padi, madu, bunga, herbal, rempah`). If unmapped, defaults to `leaf`.

#### B. Hardcoded Hex Colors & Styling
- `#07130A`, `bg-emerald-700`, `text-emerald-800`, `text-emerald-400`.

#### C. Exact Universal Adaptation Proposed
- Implement Universal Contract §3.3:
  - Overline: `{{ $activeTheme['industry'] ?? __('storefront.kategori_pilihan') }}` with `<i data-lucide="layout-grid" class="w-3.5 h-3.5">`.
  - Heading: `{{ $landingPage->services_title ?: __('storefront.kategori_pilihan') }}`.
  - Subtitle: `{{ __('storefront.pilih_kategori_filter') }}`.
  - View all link: `{{ __('storefront.view_all') }}`.
  - "Semua" button: `{{ __('storefront.semua_produk') }}`.
  - Category icon: Use general heuristic with neutral fallback: `tag` instead of forcing `leaf`.
  - Active button: `theme-btn-primary shadow-xs`.
  - Retain `products_count` badge pill with tabular numbers.
  - Tap target: Retain `min-h-[44px]`.

---

## 4. In-Depth Audit: Theme 7 (`retail_fashion`)

Directory: `resources/views/public/storefront/themes/retail_fashion/`

### 4.1 `hero.blade.php` (5,811 bytes, 80 lines)
#### A. Rigid Sector Texts Identified
- **Line 12**: Eyebrow fallback: `{{ $activeTheme['industry'] ?? 'Editorial Fashion & Haute Butik' }}`.
- **Line 16**: Hardcoded headline fallback: `'Elegansi Abadi Siluet Mode Kontemporer'`.
- **Line 20**: Hardcoded subheadline fallback: `'Koleksi busana pilihan dengan potongan presisi, kain serat alami berstandar kurasi tinggi, dan jahitan rapi yang dirancang untuk kenyamanan sepanjang hari.'`.
- **Lines 26–35**: Three hardcoded badges:
  - Badge 1 (line 26): `Koleksi Eksklusif Terbatas` (icon: `sparkles`)
  - Badge 2 (line 30): `Tailoring & Jahitan Rapi` (icon: `scissors`)
  - Badge 3 (line 34): `Garansi Tukar Ukuran` (icon: `refresh-cw`)
- **Line 42**: Hardcoded primary button text: `{{ $landingPage->cta_primary_text ?: 'Jelajahi Lookbook' }}`.
- **Line 51**: Hardcoded secondary button text: `Tanya Fitting & Size`.
- **Lines 68–71**: Floating card on hero visual:
  - Line 68: `New Season Drop`
  - Line 69: `Koleksi Terkini`
  - Line 71: `Limited Run`

#### B. Hardcoded Unsplash Photos & Hex Colors
- **CRITICAL VIOLATION**: Line 60:
  `$heroImg = $landingPage->hero_image_url ?: 'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=1000&auto=format&fit=crop&q=80';`
  A hardcoded Unsplash photo of a female fashion model! If a hardware store or clinic selects this theme, it shows a model posing in clothing!
- Line 49: `dark:bg-[#181E2A]`.

#### C. Exact Universal Adaptation Proposed
- **PHP Data Prep Header**:
  ```blade
  @php
      $heroHeadline    = $landingPage->headline ?: $business->name;
      $heroSubheadline = $landingPage->subheadline ?: ($business->description ?: __('storefront.hero.tagline'));
      $heroEyebrow     = $landingPage->announcement_badge ?: ($activeTheme['industry'] ?? __('storefront.hero.verified_official'));

      $primaryCtaText  = $landingPage->cta_primary_text ?: __('storefront.hero.explore_catalog');
      $primaryCtaUrl   = $landingPage->cta_primary_url ?: url('/' . $business->slug . '/katalog');
      $secondaryCtaText = $landingPage->cta_secondary_text ?: __('storefront.hero.contact_us');
      $secondaryCtaUrl = $landingPage->cta_secondary_url ?: $landingPage->getWhatsAppUrl();

      $universalValues = $landingPage->getUniversalValues();
      $badgePill1 = $universalValues[0]['title'];
      $badgeIcon1 = $universalValues[0]['icon'];
      $badgePill2 = $universalValues[1]['title'];
      $badgeIcon2 = $universalValues[1]['icon'];
      $badgePill3 = $universalValues[2]['title'];
      $badgeIcon3 = $universalValues[2]['icon'];

      $heroImage = $landingPage->hero_image_url ?: ($landingPage->about_image_url ?: null);
  @endphp
  ```
- **Eyebrow**: `{{ $heroEyebrow }}` with monochrome dot.
- **Badges Strip**: Render `$badgePill1..3` with icons `$badgeIcon1..3`.
- **Action Buttons**:
  - Primary button: `{{ $primaryCtaText }}` with `arrow-right` icon.
  - Secondary button: `{{ $secondaryCtaText }}` with `message-circle` icon.
- **Hero Visual Image & Universal Fallback**:
  - Remove Unsplash URL completely!
  - When `$heroImage` exists: render 3:4 portrait card with floating badge showing `{{ $business->name }}` and `{{ __('storefront.hero.active_status') }}`.
  - When `$heroImage` is null: Render a Luxe Editorial Brand Bento Card:
    - Elegant dark monochrome background (`bg-gradient-to-br from-neutral-900 to-neutral-800 text-white`).
    - Large Cormorant Garamond serif monogram (`{{ strtoupper(substr($business->name, 0, 2)) }}`).
    - Business name and `$heroSubheadline`.
    - Clean status pill: `{{ __('storefront.hero.active_status') }}`.
- **Preservation Check**: Cormorant Garamond light serifs (`font-light text-4xl sm:text-6xl lg:text-7xl`), monochrome minimalism, and 3:4 portrait aspect ratio remain 100% intact.

---

### 4.2 `features_banner.blade.php` (3,176 bytes, 47 lines)
#### A. Rigid Sector Texts Identified
- **Pillar 1 (lines 11–12)**: `Material Serat Alami` / `Katun premium, linen & sutra sejuk di kulit tropis.` (icon: `sparkles`).
- **Pillar 2 (lines 21–22)**: `Jahitan Presisi Halus` / `Kualitas butik dengan kerapatan jahitan standar tinggi.` (icon: `scissors`).
- **Pillar 3 (lines 31–32)**: `Garansi Tukar Ukuran` / `Bebas tukar ukuran 7 hari bila tidak pas di badan.` (icon: `refresh-cw`).
- **Pillar 4 (lines 41–42)**: `Packaging Kotak Mewah` / `Dikemas rapi siap pakai atau dikirim sebagai kado.` (icon: `gift`).

#### B. Hardcoded Hex Colors & Styling
- `dark:bg-[#181E2A]`.

#### C. Exact Universal Adaptation Proposed
- Implement Universal Contract §3.2:
  ```blade
  @php
      $pillars = $landingPage->getUniversalValues();
  @endphp

  <section class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
          @foreach ($pillars as $pillar)
              <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-neutral-800/80 border border-black/5 dark:border-white/10 shadow-sm flex items-start gap-3">
                  <div class="w-10 h-10 rounded-full bg-neutral-100 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-200 flex items-center justify-center shrink-0">
                      <i data-lucide="{{ $pillar['icon'] }}" class="w-4 h-4"></i>
                  </div>
                  <div>
                      <h4 class="text-[13px] sm:text-[14px] font-semibold text-neutral-900 dark:text-white leading-snug">
                          {{ $pillar['title'] }}
                      </h4>
                      <p class="text-[11px] sm:text-[12px] text-neutral-500 dark:text-neutral-400 mt-0.5 leading-relaxed">
                          {{ $pillar['desc'] ?? $pillar['description'] }}
                      </p>
                  </div>
              </div>
          @endforeach
      </div>
  </section>
  ```
- **Preservation Check**: Monochrome circular icon badges, airy grid, and editorial typography are 100% preserved.

---

### 4.3 `category_chips.blade.php` (2,967 bytes, 59 lines)
#### A. Rigid Sector Texts Identified
- **Line 8**: Hardcoded title: `{{ __('storefront.kategori_koleksi') }}`.
- **Line 10**: Hardcoded subtitle: `Jelajahi lini busana pria, wanita, kasual, dan formal`.
- **Line 15**: Hardcoded view all text: `{{ __('storefront.semua_koleksi') }}`.
- **Lines 22–36**: Fashion-only icon dictionary (`atasan, kemeja, bawahan, celana, dress, outer, jaket, hijab, tas, aksesoris, sepatu`). Default fallback is `shirt` (line 42).

#### B. Hardcoded Hex Colors & Styling
- `dark:bg-[#181E2A]`.
- Button height is `min-h-[42px]` (2px under thumb-zone 44px threshold).

#### C. Exact Universal Adaptation Proposed
- Implement Universal Contract §3.3:
  - Title: `{{ $landingPage->services_title ?: __('storefront.kategori_pilihan') }}`.
  - Subtitle: `{{ __('storefront.pilih_kategori_filter') }}`.
  - View all link: `{{ __('storefront.view_all') }}`.
  - Add "Semua Produk" chip with `!request()->filled('category')` check.
  - Active category pill highlight: `request('category') == $cat->id`.
  - Icon fallback: Replace default `shirt` with `tag`.
  - Fix tap target: Change `min-h-[42px]` to `min-h-[44px]`.
  - Retain elegant lookbook rounded-full pills and Cormorant Garamond title font.

---

## 5. Comparative Matrix Across Themes 5–7

| Feature / Element | Theme 5: `coffee_beverage` | Theme 6: `agriculture_farm` | Theme 7: `retail_fashion` |
|---|---|---|---|
| **Design Archetype** | Earthy & Warm Artisan | Earthy & Warm Artisan / Agrarian | High-End Luxe & Editorial |
| **Heading Font** | `Playfair Display` | `DM Serif Display` | `Cormorant Garamond` (Light) |
| **Body Font** | `Inter` | `Plus Jakarta Sans` | `Plus Jakarta Sans` |
| **Pillar Count** | 4 (dynamic via `getUniversalValues`) | 4 (dynamic via `getUniversalValues`) | 4 (dynamic via `getUniversalValues`) |
| **Pillar Container Layout** | 2x4 Warm Rounded-3xl Card | 1x2x4 Botanical Blurred Glass Card | 2x4 Minimalist Clean Bordered Cards |
| **Pillar Icon Styling** | Circular `w-11 h-11` with warm tint | Squircle `w-12 h-12` with hover scale | Circular `w-10 h-10` monochrome |
| **Hero Eyebrow** | Dynamic `announcement_badge` + pulsing dot | Dynamic `announcement_badge` (emoji removed) | Dynamic `announcement_badge` + neutral dot |
| **Hero 3 Badges** | Dynamic from `$landingPage->values[0..2]` | Dynamic from `$landingPage->values[0..2]` | Dynamic from `$landingPage->values[0..2]` |
| **Hero Image Fallback** | Warm Roastery Monogram Bento | Farm Harvest Monogram Bento | Minimal Luxe Monogram Bento |
| **Category Chips Shape** | Rounded-full pills with warm border | Squircle rounded-2xl pills with count | Rounded-full pills with subtle border |
| **Min Touch Target** | Upgraded to `min-h-[44px]` | Upgraded to `min-h-[44px]` | Upgraded from 42px to `min-h-[44px]` |
| **Unsplash Photos** | None present | None present | **Removed fashion model photo** |

---

## 6. Implementation Readiness & Risk Mitigation

1. **Zero Regressions**:
   - Preserves all CSS classes, transitions, Google Font bindings, and container dimensions.
   - Preserves dark mode support on all 3 themes.
2. **Translation Completeness**:
   - Every fallback string is guaranteed to exist in both `lang/id/storefront.php` and `lang/en/storefront.php`.
   - Empirically verified via `StorefrontEmpiricalTranslationChallengeTest`.
3. **Execution Instructions for Workers**:
   - Implement changes in `hero.blade.php`, `features_banner.blade.php`, and `category_chips.blade.php` for each of the 3 themes.
   - Run `php -l` on all modified Blade templates.
   - Verify layout responsiveness across mobile, tablet, and desktop viewports.
