# Milestone 2B (Batch 2) Themes 8 to 11 Universal Audit & Refactoring Blueprint

## Executive Summary & Scope

- **Milestone**: 2B (Batch 2 Storefront Themes Refactoring)
- **Explorer**: Explorer 3 (`teamwork_explorer_m2b_3`)
- **Assigned Scope**: Audit and map refactoring changes for 4 themes (12 component files):
  1. **Theme 8**: `retail_building` ("Ironclad Builder") — Archetype: `clean_bento` (Heavy slate, industrial amber, Barlow Semi Condensed)
  2. **Theme 9**: `retail_electronics` ("Nexus Cyber") — Archetype: `clean_bento` (Dark cyber navy, neon cyan, Space Grotesk)
  3. **Theme 10**: `retail_florist` ("Flora Romance") — Archetype: `warm_artisan` (Botanical rose, olive green, Bodoni Moda / Playfair)
  4. **Theme 11**: `retail_grocery` ("Fresh Mart") — Archetype: `clean_bento` (Fresh supermarket green, citrus orange, Plus Jakarta Sans)
- **Target Files per Theme**:
  - `hero.blade.php`
  - `features_banner.blade.php`
  - `category_chips.blade.php`
- **Guiding Principles**: Universal Blade Contracts (§3.1 Hero, §3.2 Features Banner, §3.3 Category Chips) as defined in `STANDARISASI_DAN_IMPLEMENTASI_TEMA_UNIVERSAL.md`.
- **Constraint**: Strict read-only audit. ZERO modifications to existing source files during this phase.

---

## 1. Audit Matrix: Current Violations & Leakages

| Theme Key | Component | Hardcoded Sector Text Violations | Hardcoded External Photos (Unsplash) | Hardcoded Hex Colors / Static Gradients | Broken Fallback Hierarchy / Missing Features |
|---|---|---|---|---|---|
| `retail_building` | `hero.blade.php` | - "Bahan Bangunan & Alat Konstruksi SNI"<br>- "Material Konstruksi Kokoh & Perkakas Terlengkap"<br>- "Distributor semen, besi beton SNI..."<br>- "Besi & Semen Berstandar SNI"<br>- "Armada Truk & Pickup Proyek"<br>- "Harga Kontraktor & Proyek"<br>- "Cari Material Bangunan"<br>- "Minta Penawaran RAB Proyek"<br>- "Gudang & Armada Siap Kirim"<br>- "SNI Pasti" | `photo-1504307651254-35680f356dfd` | `linear-gradient(135deg, #475569, #334155)` | - Headline does not fallback to `$business->name`<br>- Subheadline does not fallback to `$business->description` or `storefront.hero.tagline`<br>- Hardcoded Badges (ignores `$landingPage->getUniversalValues()`)<br>- Missing `$landingPage->cta_primary_url`<br>- Missing `$landingPage->cta_secondary_text` & URL<br>- Missing Brand Bento Graphic Showcase when hero image is null |
| `retail_building` | `features_banner.blade.php` | - "Besi beton bersertifikat, semen murni..."<br>- "Armada Truk Dump & Pick-up"<br>- "Pengiriman cepat langsung turun di titik proyek Anda"<br>- "Bantu hitung kebutuhan sak semen & meteran keramik"<br>- "Retur Barang Rusak"<br>- "Kemudahan tukar bila keramik pecah atau sak semen sobek" | None | `bg-amber-500/10`, `bg-blue-500/10`, `bg-emerald-500/10`, `bg-purple-500/10` | - Completely static cards (does not call `$landingPage->getUniversalValues()`)<br>- Violates dynamic 4-pillar contract |
| `retail_building` | `category_chips.blade.php` | - "Semen, pasir, besi, cat, sanitari, keramik & alat listrik"<br>- Hardcoded icon mapping (`semen`, `besi`, `cat`, `keramik`, `pipa`, `sanitari`, `perkakas`, `tools`, `listrik`, `kayu`) with fallback `hammer` | None | Hardcoded hover borders | - Header hardcoded to `storefront.kategori_material_perkakas` ignoring `$landingPage->services_title`<br>- Subtitle hardcoded<br>- Missing standard "Semua Produk" chip<br>- Missing active chip highlighting (`request('category') == $cat->id`)<br>- Takes only 8 categories instead of 12 |
| `retail_electronics` | `hero.blade.php` | - "Gadget & Komputer Resmi"<br>- "Performa Teknologi Masa Depan di Genggaman"<br>- "Pusat belanja smartphone resmi, laptop..."<br>- "100% IMEI Terdaftar & Resmi"<br>- "Garansi Resmi 1-2 Tahun"<br>- "Packing Kayu & Asuransi"<br>- "Cari Gadget & Laptop"<br>- "Konsultasi Spesifikasi"<br>- "Unit Segel Box Original"<br>- "100% Brand New" | `photo-1519389950473-47ba0277781c` | `linear-gradient(135deg, #06B6D4, #22D3EE)` | - Headline does not fallback to `$business->name`<br>- Subheadline does not fallback to `$business->description`<br>- Hardcoded Badges (ignores `$landingPage->getUniversalValues()`)<br>- Missing Brand Bento Graphic Showcase fallback when hero image is null<br>- CTAs ignore CMS customizable fields |
| `retail_electronics` | `features_banner.blade.php` | - "Klaim servis mudah di seluruh service center resmi di Indonesia"<br>- "IMEI Terdaftar Kemenperin"<br>- "Semua jaringan seluler aman permanen seumur hidup"<br>- "Packing Kayu & Bubble Tebal"<br>- "Pengiriman laptop & gadget dijamin aman..."<br>- "Rekomendasi device sesuai budget kerja, kuliah atau gaming" | None | `bg-cyan-500/10`, `bg-purple-500/10`, `bg-blue-500/10`, `bg-emerald-500/10` | - Static 4 cards, does not read `$landingPage->getUniversalValues()` |
| `retail_electronics` | `category_chips.blade.php` | - "Smartphone, laptop, audio, gaming gear, dan aksesoris"<br>- Hardcoded icon map (`hp`, `smartphone`, `laptop`, `komputer`, `audio`, etc.) with fallback `cpu` | None | Hardcoded cyan hover styling | - Header hardcoded to `storefront.kategori_perangkat_komponen` ignoring `$landingPage->services_title`<br>- Subtitle hardcoded<br>- Missing "Semua Produk" chip<br>- Missing active filter state check<br>- Takes only 8 categories |
| `retail_florist` | `hero.blade.php` | - "Artisan Florist & Bunga Segar"<br>- "Rangkai Kasih & Ungkapkan Rasa Lewat Bunga Segar"<br>- "Buket bunga segar impor & lokal premium, standing flower..."<br>- "Bunga Segar Garansi Mekar"<br>- "Gratis Kartu Ucapan Custom"<br>- "Same-Day Delivery Siap Kirim"<br>- "Pilih Koleksi Buket"<br>- "Konsultasi Buket Custom"<br>- "Bunga Dipetik Hari Ini"<br>- "100% Bloom" | `photo-1563241527-3004b7be0ffd` | `linear-gradient(135deg, #4D7C0F, #3F6212)` | - Headline ignores `$business->name`<br>- Subheadline ignores `$business->description`<br>- Hardcoded badges (ignores `$landingPage->getUniversalValues()`)<br>- Missing Brand Bento Graphic Showcase fallback<br>- CTAs ignore CMS input |
| `retail_florist` | `features_banner.blade.php` | - "Bunga Segar Terawat"<br>- "Dipilih tangkai terbaik dengan nutrisi agar tahan lama"<br>- "Tulis pesan manis Anda dan kami cetak eksklusif"<br>- "Same-Day Delivery"<br>- "Kurir khusus bunga menjamin buket tidak layu di jalan"<br>- "Kami kirim foto hasil rangkaian untuk persetujuan Anda" | None | `bg-lime-500/10`, `bg-pink-500/10`, `bg-amber-500/10`, `bg-blue-500/10` | - Completely hardcoded static cards<br>- Does not read `$landingPage->getUniversalValues()` |
| `retail_florist` | `category_chips.blade.php` | - "Pilihan Rangkaian Bunga"<br>- "Buket meja, hand bouquet, standing flower & papan ucapan"<br>- Hardcoded icon map (`buket`, `bouquet`, `mawar`, `standing`, `duka`, etc.) with fallback `flower-2` | None | Hardcoded lime hover styling | - Static non-i18n header text ignoring `$landingPage->services_title`<br>- Hardcoded subtitle<br>- Missing "Semua Produk" chip<br>- Missing active filter state check<br>- Takes only 8 categories |
| `retail_grocery` | `hero.blade.php` | - "Minimarket & Belanja Harian Keluarga"<br>- "Sembako Lengkap, Sayur Segar & Belanja Hemat"<br>- "Penuhi kebutuhan dapur harian mulai dari beras, minyak goreng..."<br>- "Sayur & Buah Segar Subuh"<br>- "Harga Grosir & Eceran"<br>- "Kirim Cepat ke Rumah"<br>- "Mulai Belanja Kebutuhan"<br>- "storefront.kirim_list_belanja_wa"<br>- "Stok Lengkap & Siap Kirim"<br>- "Pasti Segar" | `photo-1542838132-92c53300491e` | `linear-gradient(135deg, #16A34A, #15803D)` | - Headline ignores `$business->name`<br>- Subheadline ignores `$business->description`<br>- Badges completely hardcoded<br>- Missing Brand Bento Graphic Showcase fallback<br>- Secondary CTA hardcodes grocery translation |
| `retail_grocery` | `features_banner.blade.php` | - "Pasokan Segar Harian"<br>- "Sayur & buah dipanen langsung dari mitra petani lokal"<br>- "Beli kartonan atau eceran tetap dapat harga terbaik"<br>- "Kurir Instan Kilat"<br>- "Pesanan belanja dapur tiba dalam hitungan jam"<br>- "Timbangan Presisi Pas"<br>- "Timbangan digital akurat berstandar tera resmi" | None | `bg-green-500/10`, `bg-amber-500/10`, `bg-blue-500/10`, `bg-emerald-500/10` | - Static 4 cards, does not read `$landingPage->getUniversalValues()` |
| `retail_grocery` | `category_chips.blade.php` | - "Lorong Belanja Sembako"<br>- "Beras, minyak, bumbu, sayuran, telur & produk rumah tangga"<br>- Hardcoded icon map (`beras`, `sembako`, `minyak`, `sayur`, `buah`, etc.) with fallback `shopping-bag` | None | Hardcoded green hover styling | - Header hardcoded static text ignoring `$landingPage->services_title`<br>- Subtitle hardcoded static text<br>- Missing "Semua Produk" chip<br>- Missing active filter indicator<br>- Takes only 8 categories |

---

## 2. In-Depth Theme Refactoring Specifications

### 2.1 Theme 8: `retail_building` ("Ironclad Builder")

- **Archetype**: `clean_bento`
- **Visual Soul**: Heavy industrial slate (`#334155`), rugged steel & amber safety accent (`#D97706`), `Barlow Semi Condensed` typography, squircle geometry (`border_radius: 10px`), clean bento grid.

#### Component 1: `hero.blade.php`
- **Initial Logic Block (§3.1)**:
  ```blade
  @php
      $heroHeadline = $landingPage->headline ?: $business->name;
      $heroSubheadline = $landingPage->subheadline ?: ($business->description ?: __('storefront.hero.tagline'));
      $heroEyebrow = $landingPage->announcement_badge ?: ($activeTheme['industry'] ?? __('storefront.hero.verified_official'));

      $primaryCtaText = $landingPage->cta_primary_text ?: __('storefront.hero.explore_catalog');
      $primaryCtaUrl  = $landingPage->cta_primary_url ?: url('/' . $business->slug . '/katalog');
      $secondaryCtaText = $landingPage->cta_secondary_text ?: __('storefront.hero.contact_us');
      $secondaryCtaUrl  = $landingPage->cta_secondary_url ?: ($hasWhatsapp ? $landingPage->getWhatsAppUrl() : url('/' . $business->slug . '/kontak'));

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
- **Eyebrow**:
  ```blade
  <div class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-bold tracking-wider uppercase bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-500/30">
      <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
      <span>{{ $heroEyebrow }}</span>
  </div>
  ```
- **Headline & Subheadline**:
  ```blade
  <h1 class="font-heading font-extrabold text-3xl sm:text-5xl lg:text-6xl text-neutral-900 dark:text-white tracking-tight leading-[1.12]">
      {{ $heroHeadline }}
  </h1>
  <p class="text-base sm:text-lg text-neutral-600 dark:text-neutral-300 max-w-2xl mx-auto lg:mx-0 leading-relaxed font-normal">
      {{ $heroSubheadline }}
  </p>
  ```
- **3 Badges**:
  Display `$badgePill1..3` with `$badgeIcon1..3` with slate industrial card styling:
  - Badge 1: `bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 border border-slate-300 dark:border-slate-700`
  - Badge 2: `bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 border border-amber-200/60 dark:border-amber-800/40`
  - Badge 3: `bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800/40`
- **CTAs**:
  - Primary CTA: uses dynamic `$primaryCtaText` & `$primaryCtaUrl`, gradient with `var(--theme-primary, #D97706)` and `#B45309`.
  - Secondary CTA: uses dynamic `$secondaryCtaText` & `$secondaryCtaUrl`.
- **Visual Image & Bento Showcase Fallback**:
  - Delete `photo-1504307651254-35680f356dfd`.
  - If `$heroImage` exists: Render `<img>` + floating badge with dynamic text:
    `{{ __('storefront.hero.active_status') }}` and `{{ __('storefront.hero.verified_official') }}`.
  - If `$heroImage` is null: Render Ironclad Slate Brand Bento Showcase:
    ```blade
    <div class="w-full h-full p-8 bg-gradient-to-br from-slate-900 via-slate-800 to-zinc-900 text-white flex flex-col justify-between">
        <div class="flex items-center justify-between">
            <span class="w-14 h-14 rounded-xl flex items-center justify-center font-heading font-extrabold text-2xl text-white shadow-md" style="background-color: var(--theme-primary, #D97706);">
                {{ strtoupper(substr($business->name, 0, 2)) }}
            </span>
            <span class="px-3 py-1 rounded-lg text-xs font-bold uppercase tracking-wider bg-amber-500/20 text-amber-300 border border-amber-500/40">
                {{ __('storefront.hero.active_status') }}
            </span>
        </div>
        <div class="space-y-2 mt-auto">
            <h3 class="font-heading font-extrabold text-2xl text-white">{{ $business->name }}</h3>
            <p class="text-xs text-slate-300 leading-relaxed">{{ $heroSubheadline }}</p>
        </div>
    </div>
    ```

#### Component 2: `features_banner.blade.php`
- **Dynamic Logic (§3.2)**:
  ```blade
  @php
      $featureItems = $landingPage->getUniversalValues();
  @endphp
  <section class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
          @foreach($featureItems as $item)
              <div class="p-4 sm:p-5 rounded-xl bg-white dark:bg-[#1E293B] border border-slate-200 dark:border-white/10 shadow-sm flex items-start gap-3 transition-colors">
                  <div class="w-10 h-10 rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                      <i data-lucide="{{ $item['icon'] ?? 'shield-check' }}" class="w-5 h-5"></i>
                  </div>
                  <div>
                      <h4 class="font-heading text-[13px] sm:text-[14px] font-bold text-neutral-900 dark:text-white">{{ $item['title'] }}</h4>
                      <p class="text-[11px] sm:text-[12px] text-neutral-500 dark:text-neutral-400 mt-0.5 leading-relaxed">{{ $item['desc'] ?? ($item['description'] ?? '') }}</p>
                  </div>
              </div>
          @endforeach
      </div>
  </section>
  ```

#### Component 3: `category_chips.blade.php`
- **Universal Chips (§3.3)**:
  - Header: `$landingPage->services_title ?: __('storefront.kategori_pilihan')`
  - Subtitle: `__('storefront.pilih_kategori_filter')`
  - First Chip: "Semua Produk" with `layout-grid` icon, active when `!request()->filled('category')`, `min-h-[44px]`.
  - Loop: `$productCategories->take(12)`, active check `request('category') == $cat->id`, icon `tag` or `layers`, `min-h-[44px]`.
  - Active color: `style="background: var(--theme-primary, #D97706); color: #fff; border: 1px solid var(--theme-primary, #D97706);"`

---

### 2.2 Theme 9: `retail_electronics` ("Nexus Cyber")

- **Archetype**: `clean_bento`
- **Visual Soul**: Dark futuristic cyber tech (`#0B0F19`), neon cyan highlights (`#06B6D4`), sky blue accents (`#0284C7`), `Space Grotesk` typography, subtle 40px cyber grid pattern, `border_radius: 16px`.

#### Component 1: `hero.blade.php`
- **Initial Logic Block (§3.1)**:
  ```blade
  @php
      $heroHeadline = $landingPage->headline ?: $business->name;
      $heroSubheadline = $landingPage->subheadline ?: ($business->description ?: __('storefront.hero.tagline'));
      $heroEyebrow = $landingPage->announcement_badge ?: ($activeTheme['industry'] ?? __('storefront.hero.verified_official'));

      $primaryCtaText = $landingPage->cta_primary_text ?: __('storefront.hero.explore_catalog');
      $primaryCtaUrl  = $landingPage->cta_primary_url ?: url('/' . $business->slug . '/katalog');
      $secondaryCtaText = $landingPage->cta_secondary_text ?: __('storefront.hero.contact_us');
      $secondaryCtaUrl  = $landingPage->cta_secondary_url ?: ($hasWhatsapp ? $landingPage->getWhatsAppUrl() : url('/' . $business->slug . '/kontak'));

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
- **Preserve Cyber Atmosphere**:
  - `bg-[#0B0F19] text-white`
  - Absolute grid background pattern:
    ```blade
    <div class="absolute inset-0 opacity-[0.07]"
        style="background-image: linear-gradient(#06B6D4 1px, transparent 1px), linear-gradient(90deg, #06B6D4 1px, transparent 1px); background-size: 40px 40px;">
    </div>
    ```
- **Eyebrow**:
  ```blade
  <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-xs font-bold tracking-wider uppercase bg-cyan-950/60 text-cyan-400 border border-cyan-500/30">
      <span class="w-2 h-2 rounded-full bg-cyan-400 animate-pulse"></span>
      <span>{{ $heroEyebrow }}</span>
  </div>
  ```
- **Headline & Subheadline**:
  ```blade
  <h1 class="font-heading font-extrabold text-3xl sm:text-5xl lg:text-6xl text-white tracking-tight leading-[1.12]">
      {{ $heroHeadline }}
  </h1>
  <p class="text-base sm:text-lg text-neutral-300 max-w-2xl mx-auto lg:mx-0 leading-relaxed font-normal">
      {{ $heroSubheadline }}
  </p>
  ```
- **3 Badges**:
  - Badge 1: `bg-cyan-950/80 text-cyan-300 border border-cyan-800/60`
  - Badge 2: `bg-slate-800 text-slate-200 border border-slate-700`
  - Badge 3: `bg-emerald-950/80 text-emerald-300 border border-emerald-800/60`
- **CTAs**:
  - Primary CTA: uses `$primaryCtaText` & `$primaryCtaUrl`, styled with cyber cyan gradient:
    `style="background: linear-gradient(135deg, var(--theme-accent, #06B6D4), var(--theme-primary, #0284C7)); color: #000; font-weight: 700;"`
  - Secondary CTA: uses dynamic `$secondaryCtaText` & `$secondaryCtaUrl`.
- **Visual Image & Bento Showcase Fallback**:
  - Delete `photo-1519389950473-47ba0277781c`.
  - If `$heroImage` exists: Render image + cyber HUD badge (`__('storefront.hero.active_status')` & `__('storefront.hero.verified_official')`).
  - If `$heroImage` is null: Nexus Cyber Brand Bento Showcase:
    ```blade
    <div class="w-full h-full p-8 bg-gradient-to-br from-[#0B0F19] via-[#111827] to-[#181E2A] text-white flex flex-col justify-between border border-cyan-500/30">
        <div class="flex items-center justify-between">
            <span class="w-14 h-14 rounded-2xl flex items-center justify-center font-heading font-bold text-2xl text-black shadow-lg" style="background: linear-gradient(135deg, var(--theme-accent, #06B6D4), var(--theme-primary, #0284C7));">
                {{ strtoupper(substr($business->name, 0, 2)) }}
            </span>
            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-cyan-950 text-cyan-400 border border-cyan-500/40">
                {{ __('storefront.hero.active_status') }}
            </span>
        </div>
        <div class="space-y-2 mt-auto">
            <h3 class="font-heading font-extrabold text-2xl text-white">{{ $business->name }}</h3>
            <p class="text-xs text-neutral-300 leading-relaxed">{{ $heroSubheadline }}</p>
        </div>
    </div>
    ```

#### Component 2: `features_banner.blade.php`
- Loop over `$landingPage->getUniversalValues()`.
- Card style: `p-4 sm:p-5 rounded-2xl bg-white dark:bg-[#111827] border border-cyan-100 dark:border-cyan-900/40 shadow-sm flex items-start gap-3`
- Icon wrapper: `w-10 h-10 rounded-xl bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 flex items-center justify-center shrink-0`

#### Component 3: `category_chips.blade.php`
- Header: `$landingPage->services_title ?: __('storefront.kategori_pilihan')`
- Subtitle: `__('storefront.pilih_kategori_filter')`
- "Semua Produk" chip with `layout-grid` icon, active highlight with cyan theme token `var(--theme-accent, #06B6D4)`, `min-h-[44px]`.
- Categories loop with `take(12)`, active check, fallback icon `cpu` or `tag`, `min-h-[44px]`.

---

### 2.3 Theme 10: `retail_florist` ("Flora Romance")

- **Archetype**: `warm_artisan`
- **Visual Soul**: Botanical elegance, warm blush background (`#FFF1F2`), deep rose crimson (`#E11D48`), botanical olive/emerald accents (`#059669`), `Bodoni Moda` serif headings, soft `Plus Jakarta Sans` body, rounded-2xl & 3xl geometry.

#### Component 1: `hero.blade.php`
- **Initial Logic Block (§3.1)**:
  ```blade
  @php
      $heroHeadline = $landingPage->headline ?: $business->name;
      $heroSubheadline = $landingPage->subheadline ?: ($business->description ?: __('storefront.hero.tagline'));
      $heroEyebrow = $landingPage->announcement_badge ?: ($activeTheme['industry'] ?? __('storefront.hero.verified_official'));

      $primaryCtaText = $landingPage->cta_primary_text ?: __('storefront.hero.explore_catalog');
      $primaryCtaUrl  = $landingPage->cta_primary_url ?: url('/' . $business->slug . '/katalog');
      $secondaryCtaText = $landingPage->cta_secondary_text ?: __('storefront.hero.contact_us');
      $secondaryCtaUrl  = $landingPage->cta_secondary_url ?: ($hasWhatsapp ? $landingPage->getWhatsAppUrl() : url('/' . $business->slug . '/kontak'));

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
- **Eyebrow**:
  ```blade
  <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-xs font-semibold bg-rose-50 dark:bg-rose-950/50 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800/40">
      <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
      <span>{{ $heroEyebrow }}</span>
  </div>
  ```
- **Headline & Subheadline**:
  ```blade
  <h1 class="font-heading font-extrabold text-3xl sm:text-5xl lg:text-6xl text-neutral-900 dark:text-white tracking-tight leading-[1.12]">
      {{ $heroHeadline }}
  </h1>
  <p class="text-base sm:text-lg text-neutral-600 dark:text-neutral-300 max-w-2xl mx-auto lg:mx-0 leading-relaxed font-normal">
      {{ $heroSubheadline }}
  </p>
  ```
- **3 Badges**:
  - Badge 1: `bg-rose-50 dark:bg-rose-950/40 text-rose-800 dark:text-rose-300 border border-rose-200/60 dark:border-rose-800/40`
  - Badge 2: `bg-pink-50 dark:bg-pink-950/40 text-pink-700 dark:text-pink-300 border border-pink-200/60 dark:border-pink-800/40`
  - Badge 3: `bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800/40`
- **CTAs**:
  - Primary CTA: uses `$primaryCtaText` & `$primaryCtaUrl`, styled with rose/crimson gradient:
    `style="background: linear-gradient(135deg, var(--theme-primary, #E11D48), #BE123C); color: #fff;"`
  - Secondary CTA: uses dynamic `$secondaryCtaText` & `$secondaryCtaUrl`.
- **Visual Image & Bento Showcase Fallback**:
  - Delete `photo-1563241527-3004b7be0ffd`.
  - If `$heroImage` exists: Render image + botanical floating badge (`__('storefront.hero.active_status')` & `__('storefront.hero.verified_official')`).
  - If `$heroImage` is null: Botanical Artisan Brand Bento Showcase:
    ```blade
    <div class="w-full h-full p-8 bg-gradient-to-br from-rose-950 via-neutral-900 to-emerald-950 text-white flex flex-col justify-between border border-rose-200/20 shadow-2xl">
        <div class="flex items-center justify-between">
            <span class="w-14 h-14 rounded-2xl flex items-center justify-center font-heading font-serif text-2xl text-white shadow-md" style="background-color: var(--theme-primary, #E11D48);">
                {{ strtoupper(substr($business->name, 0, 2)) }}
            </span>
            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-rose-900/60 text-rose-300 border border-rose-500/30">
                {{ __('storefront.hero.active_status') }}
            </span>
        </div>
        <div class="space-y-2 mt-auto">
            <h3 class="font-heading font-serif text-2xl text-white">{{ $business->name }}</h3>
            <p class="text-xs text-rose-100/80 leading-relaxed">{{ $heroSubheadline }}</p>
        </div>
    </div>
    ```

#### Component 2: `features_banner.blade.php`
- Loop over `$landingPage->getUniversalValues()`.
- Card style: `p-4 sm:p-5 rounded-2xl bg-white dark:bg-[#181E2A] border border-rose-100/80 dark:border-white/10 shadow-sm flex items-start gap-3`
- Icon wrapper: `w-10 h-10 rounded-xl bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0`

#### Component 3: `category_chips.blade.php`
- Header: `$landingPage->services_title ?: __('storefront.kategori_pilihan')`
- Subtitle: `__('storefront.pilih_kategori_filter')`
- "Semua Produk" chip with `layout-grid` icon, active highlight with rose token `var(--theme-primary, #E11D48)`, `min-h-[44px]`.
- Categories loop with `take(12)`, active check, fallback icon `sparkles` or `tag`, `min-h-[44px]`.

---

### 2.4 Theme 11: `retail_grocery` ("Fresh Mart")

- **Archetype**: `clean_bento`
- **Visual Soul**: Vibrant fresh market (`#F0FDF4`), fresh emerald green (`#16A34A`), warm citrus orange (`#EA580C`), `Plus Jakarta Sans` typography, rounded-2xl squircle cards.

#### Component 1: `hero.blade.php`
- **Initial Logic Block (§3.1)**:
  ```blade
  @php
      $heroHeadline = $landingPage->headline ?: $business->name;
      $heroSubheadline = $landingPage->subheadline ?: ($business->description ?: __('storefront.hero.tagline'));
      $heroEyebrow = $landingPage->announcement_badge ?: ($activeTheme['industry'] ?? __('storefront.hero.verified_official'));

      $primaryCtaText = $landingPage->cta_primary_text ?: __('storefront.hero.explore_catalog');
      $primaryCtaUrl  = $landingPage->cta_primary_url ?: url('/' . $business->slug . '/katalog');
      $secondaryCtaText = $landingPage->cta_secondary_text ?: __('storefront.hero.contact_us');
      $secondaryCtaUrl  = $landingPage->cta_secondary_url ?: ($hasWhatsapp ? $landingPage->getWhatsAppUrl() : url('/' . $business->slug . '/kontak'));

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
- **Eyebrow**:
  ```blade
  <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-xs font-bold bg-green-500/10 text-green-700 dark:text-green-400 border border-green-500/30">
      <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
      <span>{{ $heroEyebrow }}</span>
  </div>
  ```
- **Headline & Subheadline**:
  ```blade
  <h1 class="font-heading font-extrabold text-3xl sm:text-5xl lg:text-6xl text-neutral-900 dark:text-white tracking-tight leading-[1.12]">
      {{ $heroHeadline }}
  </h1>
  <p class="text-base sm:text-lg text-neutral-600 dark:text-neutral-300 max-w-2xl mx-auto lg:mx-0 leading-relaxed font-normal">
      {{ $heroSubheadline }}
  </p>
  ```
- **3 Badges**:
  - Badge 1: `bg-green-50 dark:bg-green-950/40 text-green-800 dark:text-green-300 border border-green-200/60 dark:border-green-800/40`
  - Badge 2: `bg-orange-50 dark:bg-orange-950/40 text-orange-800 dark:text-orange-300 border border-orange-200/60 dark:border-orange-800/40`
  - Badge 3: `bg-blue-50 dark:bg-blue-950/40 text-blue-800 dark:text-blue-300 border border-blue-200/60 dark:border-blue-800/40`
- **CTAs**:
  - Primary CTA: uses `$primaryCtaText` & `$primaryCtaUrl`, styled with emerald gradient:
    `style="background: linear-gradient(135deg, var(--theme-primary, #16A34A), #15803D); color: #fff;"`
  - Secondary CTA: uses dynamic `$secondaryCtaText` & `$secondaryCtaUrl` (eliminates hardcoded `storefront.kirim_list_belanja_wa`).
- **Visual Image & Bento Showcase Fallback**:
  - Delete `photo-1542838132-92c53300491e`.
  - If `$heroImage` exists: Render image + fresh market floating badge (`__('storefront.hero.active_status')` & `__('storefront.hero.verified_official')`).
  - If `$heroImage` is null: Fresh Mart Brand Bento Showcase:
    ```blade
    <div class="w-full h-full p-8 bg-gradient-to-br from-emerald-950 via-neutral-900 to-green-950 text-white flex flex-col justify-between border border-emerald-500/20 shadow-2xl">
        <div class="flex items-center justify-between">
            <span class="w-14 h-14 rounded-2xl flex items-center justify-center font-heading font-extrabold text-2xl text-white shadow-md" style="background-color: var(--theme-primary, #16A34A);">
                {{ strtoupper(substr($business->name, 0, 2)) }}
            </span>
            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-emerald-900/60 text-emerald-300 border border-emerald-500/30">
                {{ __('storefront.hero.active_status') }}
            </span>
        </div>
        <div class="space-y-2 mt-auto">
            <h3 class="font-heading font-extrabold text-2xl text-white">{{ $business->name }}</h3>
            <p class="text-xs text-neutral-300 leading-relaxed">{{ $heroSubheadline }}</p>
        </div>
    </div>
    ```

#### Component 2: `features_banner.blade.php`
- Loop over `$landingPage->getUniversalValues()`.
- Card style: `p-4 sm:p-5 rounded-2xl bg-white dark:bg-[#181E2A] border border-green-100 dark:border-white/10 shadow-sm flex items-start gap-3`
- Icon wrapper: `w-10 h-10 rounded-xl bg-green-500/10 text-green-700 dark:text-green-400 flex items-center justify-center shrink-0`

#### Component 3: `category_chips.blade.php`
- Header: `$landingPage->services_title ?: __('storefront.kategori_pilihan')`
- Subtitle: `__('storefront.pilih_kategori_filter')`
- "Semua Produk" chip with `layout-grid` icon, active highlight with emerald token `var(--theme-primary, #16A34A)`, `min-h-[44px]`.
- Categories loop with `take(12)`, active check, fallback icon `tag` or `layout-grid`, `min-h-[44px]`.

---

## 3. Universal Blade Contract Compliance Checklist

Each refactored file MUST satisfy these strict empirical rules:

1. **`hero.blade.php`**:
   - [x] Includes standardized §3.1 `@php` initialization block.
   - [x] Fallback hierarchy: `$heroHeadline = $landingPage->headline ?: $business->name;`.
   - [x] Subheadline fallback: `$landingPage->subheadline ?: ($business->description ?: __('storefront.hero.tagline'));`.
   - [x] Badges: derived exclusively from `$landingPage->getUniversalValues()` (no hardcoded sector badges).
   - [x] CTAs: support `$primaryCtaText`, `$primaryCtaUrl`, `$secondaryCtaText`, `$secondaryCtaUrl`.
   - [x] Monogram fallback: dynamic `substr($business->name, 0, 2)` when hero image is null.
   - [x] ZERO Unsplash image URLs (`images.unsplash.com`).

2. **`features_banner.blade.php`**:
   - [x] Reads `$featureItems = $landingPage->getUniversalValues();`.
   - [x] Loops with `@foreach($featureItems as $item)`.
   - [x] Uses `$item['icon']`, `$item['title']`, `$item['desc']`.
   - [x] ZERO static sector cards (no hardcoded SNI, Kemenperin, florist delivery, fresh market harvest text).

3. **`category_chips.blade.php`**:
   - [x] Dynamic header: `$landingPage->services_title ?: __('storefront.kategori_pilihan')`.
   - [x] Dynamic subtitle: `__('storefront.pilih_kategori_filter')`.
   - [x] First item is "Semua Produk" chip linked to catalog root.
   - [x] Active state indicated on "Semua Produk" when `!request()->filled('category')`.
   - [x] Active state indicated on category chips when `request('category') == $cat->id`.
   - [x] Touch target height `min-h-[44px]` thumb-zone compliant.
   - [x] Takes up to 12 categories (`take(12)`).
   - [x] Neutral / universal fallback icon (`tag`, `layout-grid`, `layers`).

---

## 4. Quality Assurance & Test Mapping

The implementer should write and run a feature challenge test for Themes 8 to 11 (e.g. `tests/Feature/StorefrontThemes8To11EmpiricalChallengeTest.php`):
1. **PHP Syntax Linting**: `php -l` on all 12 files must return returncode 0.
2. **Static Regex Grep**:
   - Zero occurrences of `unsplash.com` and `images.unsplash`.
   - Zero occurrences of forbidden phrases in active Blade code:
     `Bahan Bangunan`, `Alat Konstruksi SNI`, `Besi beton`, `Dump & Pick-up`, `Perangkat Komponen`, `IMEI Terdaftar`, `Garansi Resmi`, `Packing Kayu`, `Bunga Segar`, `Rangkaian Bunga`, `Buket`, `Standing flower`, `Lorong Belanja`, `Sembako Lengkap`, `Sayur Segar`.
3. **Dynamic Render Integrity**:
   - Renders cleanly with empty/null `$landingPage->headline`, `$subheadline`, `$hero_image_url`, and `$values`.
   - Monogram displays uppercase 2 letters of `$business->name`.
   - Renders cleanly with full CMS custom values without leakage.
