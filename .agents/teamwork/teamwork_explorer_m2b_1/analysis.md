# Milestone 2B — Explorer 1 Detailed Audit & Change Mapping (Themes 1–4)

**Audit Target Themes:**
1. `bakery_cake` (Artisan Bakery & Cake Shop)
2. `fnb_bakery` (Velvet Patisserie)
3. `fnb_cafe` (Artisan Brew / Coffee Shop)
4. `fnb_catering` (Epicurean Box / Catering & Bento)

**Target View Files per Theme:**
- `resources/views/public/storefront/themes/{theme_key}/hero.blade.php`
- `resources/views/public/storefront/themes/{theme_key}/features_banner.blade.php`
- `resources/views/public/storefront/themes/{theme_key}/category_chips.blade.php`

**Reference SSOT Standards:**
- `STANDARISASI_DAN_IMPLEMENTASI_TEMA_UNIVERSAL.md` (§1 Principles, §2 Do's & Don'ts, §3 Universal Blade Contracts)
- `StorefrontThemeService.php` (Archetypes, Palette, Fallback values)
- Universal i18n dictionaries: `lang/id/storefront.php` & `lang/en/storefront.php`

---

## 1. Executive Summary

Milestone 2B commences the refactoring of Batch 2 storefront themes (F&B and Retail themes). Historically, these themes were built as rigid sector-specific templates with hardcoded industry texts ("roti hangat", "sourdough", "biji kopi sangrai", "katering bento"), hardcoded Unsplash stock photos, and static feature pillars. When a business owner operating outside these specific industries (e.g. an electronics store, dental clinic, law office, or craft workshop) selected one of these beautifully designed themes, the storefront leaked jarring sector-specific phrases, broken badges, or food photos.

This audit maps **every hardcoded phrase, stock photo URL, and rigid contract violation** across the first 4 themes of Batch 2 (`bakery_cake`, `fnb_bakery`, `fnb_cafe`, `fnb_catering`), and provides exact, line-by-line adaptation plans to implement Universal Blade Contracts (§3.1 Hero, §3.2 Features Banner, §3.3 Category Chips) while preserving each theme's unique visual personality, typography, color harmony, and animations.

### High-Level Vulnerability & Leakage Matrix

| Theme Key | Design Archetype | Hero Sector Leakage | Unsplash Photo Leakage | Features Banner Dynamic Values | Category Chips Universal Contract |
|---|---|---|---|---|---|
| `bakery_cake` | `warm_artisan` (Warm Oven Gold) | **CRITICAL** (18+ baking phrases, oven metrics, patisserie bento) | Clean (No Unsplash, but hardcoded fallback bento) | **VIOLATION** (Static 4 baking pillars, ignores CMS values) | **VIOLATION** (Hardcoded bakery headline, cake-slice icon for all categories) |
| `fnb_bakery` | `warm_artisan` (Velvet Patisserie) | **CRITICAL** (Bakery copy, croissant icon, cake CTA) | **CRITICAL LEAKAGE** (`photo-1509440159596` bread stock photo) | **VIOLATION** (Static 4 baking pillars, ignores CMS values) | **VIOLATION** (Missing "Semua" chip, hardcoded bakery copy, no active highlight) |
| `fnb_cafe` | `warm_artisan` (Artisan Brew) | **CRITICAL** (Coffee copy, tasting notes, roast level indicator) | **CRITICAL LEAKAGE** (`photo-1495474472287` coffee cup stock photo) | **VIOLATION** (Static 4 roasting pillars, ignores CMS values) | **VIOLATION** (Missing "Semua" chip, coffee icon fallback, inline JS event handlers) |
| `fnb_catering` | `playful_fresh` (Epicurean Box) | **CRITICAL** (Bento & catering copy, salad icon, BPOM badge) | **CRITICAL LEAKAGE** (`photo-1546069901` salad bowl stock photo) | **VIOLATION** (Static 4 nutrition pillars, ignores CMS values) | **VIOLATION** (Missing "Semua" chip, utensils icon fallback, rigid catering headline) |

---

## 2. Deep-Dive Audit: Theme 1 — `bakery_cake`

### 2.1 Visual Archetype & Personality
- **Archetype**: `warm_artisan` (Earthy & Warm Artisan)
- **Palette**: Primary `#D97706` (Oven Amber), Accent `#BE185D` (Velvet Rose), Background `#FFFDF5`, Card `#FFFFFF`.
- **Typography**: Heading `Cormorant Garamond` (editorial serif with warm luxury), Body `Poppins`.
- **Aesthetic Essence**: Warm oven glow, pure butter artisanal craft, delicate borders, elegant golden card gradients.

### 2.2 File 1: `hero.blade.php` (`resources/views/public/storefront/themes/bakery_cake/hero.blade.php`)
- **Total Lines**: 146
- **Direct Observations & Violations**:
  1. **Line 19**: Hardcoded Eyebrow:
     ```blade
     <span class="text-[11px] font-bold tracking-wide">🥖 Dipanggang Pagi Ini &bull; 100% Pure Butter &amp; Ragi Alami</span>
     ```
     *Impact*: Any merchant using this theme gets bread and sourdough emoji/text.
  2. **Line 24**: Hardcoded Headline fallback:
     ```blade
     {{ $landingPage->headline ?: 'Roti Hangat & Kue Istimewa Dipanggang Setiap Pagi' }}
     ```
     *Impact*: Fallback text leaks baking copy when CMS headline is empty.
  3. **Line 29**: Hardcoded Subheadline fallback:
     ```blade
     {{ $landingPage->subheadline ?: 'Dibuat dengan ragi alami sourdough, tepung terigu pilihan, dan mentega murni berkualitas tinggi tanpa pengawet buatan. Nikmati aneka roti artisan lembut, pastry renyah, dan kue perayaan custom.' }}
     ```
  4. **Lines 34–45**: Static Badges Strip:
     - Badge 1 (line 36): `100% Mentega Murni` (icon: `award`)
     - Badge 2 (line 40): `Pre-Order Custom Cake` (icon: `cake`)
     - Badge 3 (line 44): `100% Halal & Tanpa Pengawet` (icon: `shield-check`)
     *Impact*: Fails to bind to `$landingPage->getUniversalValues()`.
  5. **Lines 51–64**: Rigid CTA Actions:
     - Primary button (line 54): uses `{{ __('storefront.pilih_roti_pastry') }}` with icon `cake-slice`.
     - Secondary button (line 62): hardcoded text `Konsultasi Custom Cake`.
  6. **Lines 68–81**: Rigid Transparency Metrics Strip:
     ```blade
     <span class="...">06:00</span>
     <span class="...">Batch Pertama Oven</span>
     <span class="...">48 Jam</span>
     <span class="...">Fermentasi Sourdough</span>
     <span class="...">100%</span>
     <span class="...">Mentega Alami Murni</span>
     ```
     *Impact*: If a boutique or consulting service uses this theme, it shows "Batch Pertama Oven 06:00" and "48 Jam Fermentasi Sourdough".
  7. **Lines 88–138**: Rigid Hero Visual Showcase:
     - Floating badge on image (lines 94–95): hardcoded `Dapur Pembuat Terverifikasi` & `Dipanggang segar tiap pagi dengan bahan-bahan premium`.
     - Fallback Bento Card (lines 100–138): Hardcoded baking copy: `Hangat Dari Oven`, `Sentuhan Keahlian Patisserie`, `Tekstur Lembut, Renyah & Istimewa`, and 3 checkmarks referencing sourdough, cake custom fruit decoration, and anti-shock cake boxes.

### 2.3 File 2: `features_banner.blade.php` (`resources/views/public/storefront/themes/bakery_cake/features_banner.blade.php`)
- **Total Lines**: 70
- **Direct Observations & Violations**:
  - The entire file is hardcoded static HTML with zero PHP logic. It does not call `$landingPage->getUniversalValues()`.
  - Pillar 1 (lines 15–18): `Dipanggang Segar Subuh` / `Batch roti & pastry hangat siap santap setiap pagi mulai pukul 06:00 WIB.` (icon: `sun`)
  - Pillar 2 (lines 29–33): `100% Pure Butter Murni` / `Mentega berkualitas tanpa lemak trans menghasilkan aroma wangi menggoda alami.` (icon: `award`)
  - Pillar 3 (lines 44–48): `Bebas Pengawet Kimia` / `Fermentasi alami sourdough ramah pencernaan, aman untuk seluruh anggota keluarga.` (icon: `shield-check`)
  - Pillar 4 (lines 59–63): `Kotak Kue Anti-Guncang` / `Kemasan protektif struktural menjaga bentuk cake & pastry tetap indah saat tiba.` (icon: `package`)

### 2.4 File 3: `category_chips.blade.php` (`resources/views/public/storefront/themes/bakery_cake/category_chips.blade.php`)
- **Total Lines**: 55
- **Direct Observations & Violations**:
  - **Line 11**: Hardcoded category overline: `Etalase Roti &amp; Patisserie`.
  - **Line 14**: Hardcoded heading: `Pilihan Roti Artisan, Pastry &amp; Celebration Cake`.
  - **Line 17**: Hardcoded subtitle: `Jelajahi sourdough renyah, croissant mentega, kue tart ulang tahun, dan dessert lembut favorit Anda`.
  - **Line 23**: Hardcoded view all text: `Seluruh Etalase Toko`.
  - **Line 34**: Uses `{{ __('storefront.semua_etalase') }}` instead of standard `{{ __('storefront.semua_produk') }}`.
  - **Line 43**: Rigid icon: `<i data-lucide="cake-slice" class="..."></i>` rendered for EVERY category chip regardless of category name!
  - **Button classes**: uses static `bg-amber-600 text-white` instead of dynamic `theme-btn-primary`.

### 2.5 Adaptation Strategy for `bakery_cake`
1. **Hero**:
   - Inject Universal Blade header:
     ```blade
     @php
         $heroHeadline = $landingPage->headline ?: $business->name;
         $heroSubheadline = $landingPage->subheadline ?: ($business->description ?: __('storefront.hero.tagline'));
         $heroEyebrow = $landingPage->announcement_badge ?: ($activeTheme['industry'] ?? __('storefront.hero.verified_official'));
         $primaryCtaText = $landingPage->cta_primary_text ?: __('storefront.hero.explore_catalog');
         $primaryCtaUrl  = $landingPage->cta_primary_url ?: url('/' . $business->slug . '/katalog');
         $secondaryCtaText = $landingPage->cta_secondary_text ?: __('storefront.hero.contact_us');
         $secondaryCtaUrl  = $landingPage->cta_secondary_url ?: $landingPage->getWhatsAppUrl();

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
   - Convert eyebrow to dynamic badge with animated pulse dot.
   - Replace 3 rigid pills with `$badgePill1..3` and `$badgeIcon1..3`.
   - Update CTAs to dynamic `$primaryCtaText` (icon: `arrow-right`) and `$secondaryCtaText` (icon: `message-circle`).
   - Transform the transparency strip: use universal metrics / trust signals (e.g. `100%` `{{ __('storefront.badges.quality_guaranteed') }}`, `24/7` `{{ __('storefront.badges.fast_service') }}`, `Verified` `{{ __('storefront.badges.verified_official') }}` or dynamic trust indicators).
   - In the visual column: when `$heroImage` is null, render the Patisserie Hearth Bento Showcase with `$business->name`, initial monogram, `$heroSubheadline`, active status badge, and dynamic values from `$universalValues`.
2. **Features Banner**:
   - Replace static HTML with `@php $pillars = $landingPage->getUniversalValues(); @endphp` and `@foreach ($pillars as $pillar)` loop.
   - Retain the warm backdrop-blur container (`bg-white/80 dark:bg-[#221810]/80 ... bg-amber-50/60 dark:bg-amber-950/40`).
3. **Category Chips**:
   - Heading: `{{ $landingPage->services_title ?: __('storefront.kategori_pilihan') }}`.
   - Subtitle: `{{ __('storefront.pilih_kategori_filter') }}`.
   - View All link: `{{ __('storefront.view_all') }}`.
   - All chip: `{{ __('storefront.semua_produk') }}` with `layout-grid` icon.
   - Chip icon: change from static `cake-slice` to neutral `tag`.
   - Active state: use `theme-btn-primary shadow-xs` when active, and warm cream/amber bordered background when inactive.

---

## 3. Deep-Dive Audit: Theme 2 — `fnb_bakery`

### 3.1 Visual Archetype & Personality
- **Archetype**: `warm_artisan` (Earthy & Warm Artisan)
- **Palette**: Primary `#EA580C` / `#BE185D` (Velvet Rose), Accent `#FBBF24` (Champagne Gold), Background `#FFFBF7`, Card `#FFFFFF`.
- **Typography**: Heading `Playfair Display` (high-contrast luxury serif), Body `Plus Jakarta Sans`.
- **Aesthetic Essence**: Soft rose pastels, gold touches, rounded-2xl & rounded-3xl cards, gentle hover transitions.

### 3.2 File 1: `hero.blade.php` (`resources/views/public/storefront/themes/fnb_bakery/hero.blade.php`)
- **Total Lines**: 86
- **Direct Observations & Violations**:
  1. **Lines 11–15**: Rigid Eyebrow:
     ```blade
     <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-xs font-semibold"
         style="background: {{ $activeTheme['badge_bg'] ?? '#BE185D15' }}; color: {{ $activeTheme['badge_text'] ?? '#BE185D' }};">
         <i data-lucide="croissant" class="w-4 h-4"></i>
         <span>{{ $activeTheme['industry'] ?? 'Artisan Bakery & Patisserie' }}</span>
     </div>
     ```
     *Impact*: Croissant icon hardcoded in the eyebrow badge.
  2. **Line 18**: Hardcoded Headline fallback:
     ```blade
     {{ $landingPage->headline ?: 'Roti Hangat & Kue Istimewa Setiap Hari' }}
     ```
  3. **Line 22**: Hardcoded Subheadline fallback:
     ```blade
     {{ $landingPage->subheadline ?: 'Dipanggang segar setiap pagi dengan mentega Eropa murni, ragi alami tanpa pengawet buatan, dan kreasi kue ulang tahun berdesain elegan.' }}
     ```
  4. **Lines 27–39**: Rigid Badges:
     - Badge 1: `Freshly Baked 06:00 WIB` (icon: `sun`)
     - Badge 2: `Custom Birthday Cake` (icon: `cake`)
     - Badge 3: `100% Halal & Pure Butter` (icon: `heart`)
  5. **Lines 44–58**: Rigid CTA Actions:
     - Line 46: Hardcoded hex gradient `background: linear-gradient(135deg, #BE185D, #9D174D);`.
     - Line 47: Fallback text `'Pilih Roti & Pastry'`.
     - Line 56: Secondary button hardcoded text `<span>Pesan Cake Kustom</span>`.
  6. **CRITICAL LEAKAGE — Line 66: Hardcoded Unsplash Photo**:
     ```blade
     $heroImg = $landingPage->hero_image_url ?: 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=1000&auto=format&fit=crop&q=80';
     ```
     *Impact*: Unsplash bakery bread image loaded for EVERY business that selects this theme unless they upload a hero image.
  7. **Lines 75–77**: Hardcoded floating badge on image:
     ```blade
     <span class="font-semibold text-neutral-900 dark:text-white">Batch Pagi Sudah Siap Saji</span>
     <span class="text-rose-600 dark:text-rose-400 font-bold font-mono">100% Fresh</span>
     ```
  8. **Missing Fallback Card**: If no image is provided, it does not render a brand bento card; it forces the Unsplash stock photo.

### 3.3 File 2: `features_banner.blade.php` (`resources/views/public/storefront/themes/fnb_bakery/features_banner.blade.php`)
- **Total Lines**: 47
- **Direct Observations & Violations**:
  - Static HTML grid with hardcoded bakery pillars:
    - Pillar 1 (lines 11–12): `Panggang Setiap Pagi` / `Suhu oven presisi, tanpa bahan pengawet buatan.` (icon: `sun`)
    - Pillar 2 (lines 21–22): `Pure European Butter` / `Aroma gurih wangi mentega asli berkualitas tinggi.` (icon: `award`)
    - Pillar 3 (lines 31–32): `Kemasan &amp; Hampers Mewah` / `Kotak cantik berpita untuk bingkisan istimewa.` (icon: `gift`)
    - Pillar 4 (lines 41–42): `{{ __('storefront.pengiriman_instan_aman') }}` / `Kue tart &amp; roti diantar aman sampai ke meja Anda.` (icon: `truck`)
  - Does not read `$landingPage->getUniversalValues()`.

### 3.4 File 3: `category_chips.blade.php` (`resources/views/public/storefront/themes/fnb_bakery/category_chips.blade.php`)
- **Total Lines**: 61
- **Direct Observations & Violations**:
  - **Lines 8–13**: Hardcoded heading & subtitle:
    - Heading: `Pilihan Roti &amp; Kue`
    - Subtitle: `Koleksi sajian manis &amp; gurih dipanggang segar`
  - **MISSING ALL CHIP**: Does not provide a "Semua Produk" chip. Customers cannot clear category filters or view all products.
  - **Lines 23–49**: Sector-specific `$catIcons` array (`roti`, `bread`, `pastry`, `cake`, `tart`, `cookies`, `donut`, `pie`) with fallback icon `cake-slice`.
  - **Line 51**: Missing active state highlight (`request('category') == $cat->id`).

### 3.5 Adaptation Strategy for `fnb_bakery`
1. **Hero**:
   - Apply Universal Blade contract §3.1 header variables.
   - Replace croissant icon with animated pulse dot.
   - Replace 3 rigid pills with `$badgePill1..3` and `$badgeIcon1..3`.
   - Dynamic CTAs: `$primaryCtaText` / `$primaryCtaUrl`, `$secondaryCtaText` / `$secondaryCtaUrl`.
   - Remove Unsplash URL. If `$heroImage` exists, render image with universal floating status card (`__('storefront.hero.active_status')` & `__('storefront.hero.verified_official')`).
   - If `$heroImage` is null, render a luxury velvet rose brand bento card with monogram, business name, `$heroSubheadline`, and active badge.
2. **Features Banner**:
   - Loop over `$pillars = $landingPage->getUniversalValues();`.
   - Keep the clean rounded-2xl cards with rose/amber/pink/emerald pastel icon wrappers.
3. **Category Chips**:
   - Dynamic heading: `{{ $landingPage->services_title ?: __('storefront.kategori_pilihan') }}`.
   - Dynamic subtitle: `{{ __('storefront.pilih_kategori_filter') }}`.
   - Add "Semua Produk" chip with `layout-grid` icon and active state detection.
   - Replace `cake-slice` default fallback icon with neutral `tag`.
   - Add active chip styling (`$isActive ? 'theme-btn-primary shadow-xs' : '...'`).

---

## 4. Deep-Dive Audit: Theme 3 — `fnb_cafe`

### 4.1 Visual Archetype & Personality
- **Archetype**: `warm_artisan` (Earthy & Warm Artisan)
- **Palette**: Primary `#8B5A2B` (Roast Coffee), Accent `#C88A58` (Caramel Crema), Background `#FAF7F2`, Card `#FFFFFF`.
- **Typography**: Heading `Playfair Display`, Body `Plus Jakarta Sans`.
- **Aesthetic Essence**: Warm coffee aroma, steam animations, organic rounded-[18px] & rounded-[24px] contours.

### 4.2 File 1: `hero.blade.php` (`resources/views/public/storefront/themes/fnb_cafe/hero.blade.php`)
- **Total Lines**: 173
- **Direct Observations & Violations**:
  1. **Line 11**: Hardcoded SVG Coffee Bean Texture:
     ```svg
     <ellipse cx="40" cy="40" rx="18" ry="12" fill="#8B5A2B" .../><path d="M28 38 Q40 30 52 38" fill="none" stroke="#8B5A2B" .../>
     ```
  2. **Lines 20–24**: Eyebrow:
     - Fallback text: `'Kafe & Kopi Spesialis'`.
     - Static inline hexes: `#8B5A2B1A` and `#8B5A2B`.
  3. **Lines 26–34**: Typography & Subheadline:
     - Line 27: hardcoded `style="color: #3D2B1F;"`.
     - Line 32: hardcoded `style="color: #6B5744;"`.
     - Line 33: hardcoded coffee subheadline: `'Nikmati ritual kopi spesialti dari biji terpilih, roasting presisi, dan penyeduhan penuh cinta. Single Origin hingga House Blend   setiap cangkir punya cerita.'`.
  4. **Lines 37–54**: Hardcoded Tasting Notes Pills:
     - `Fruity` (icon: `cherry`), `Nutty` (icon: `nut`), `Chocolaty` (icon: `bean`), `Floral` (icon: `flower-2`).
     - Fails to render universal value badges.
  5. **Lines 57–75**: Rigid CTA Actions:
     - Line 61: hardcoded gradient `linear-gradient(135deg, #8B5A2B, #6B4423)`.
     - Line 62: fallback text `'Jelajahi Menu Kopi'` with icon `coffee`.
     - Line 70: hardcoded border and background hexes.
     - Line 73: hardcoded text `Pesan via WhatsApp`.
  6. **Lines 77–89**: Rigid "Roast Level" Visual Indicator:
     ```blade
     <span ...>Roast Level</span>
     @for ($i = 1; $i <= 5; $i++)
         <div class="w-5 h-2 rounded-full ..." style="background: {{ $i <= 3 ? '#8B5A2B' : '#8B5A2B25' }};"></div>
     @endfor
     <span ...>Medium</span>
     ```
     *Impact*: Coffee roast level widget displayed on the storefront.
  7. **CRITICAL LEAKAGE — Line 97: Hardcoded Unsplash Photo**:
     ```blade
     $heroImg = $landingPage->hero_image_url ?: 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?w=1000&auto=format&fit=crop&q=80';
     ```
     *Impact*: Unsplash coffee cup photo hardcoded as default.
  8. **Lines 115–129**: Hardcoded floating badges on image:
     - `coffee` icon with `Freshly Brewed`.
     - Floating badge `Single Origin`.

### 4.3 File 2: `features_banner.blade.php` (`resources/views/public/storefront/themes/fnb_cafe/features_banner.blade.php`)
- **Total Lines**: 48
- **Direct Observations & Violations**:
  - Static HTML grid with coffee roasting pillars:
    - Pillar 1 (lines 12–13): `Freshly Roasted` / `Disangrai berkala dalam batch kecil untuk aroma optimal.` (icon: `flame`)
    - Pillar 2 (lines 22–23): `Single Origin & Blend` / `Biji kopi pilihan Nusantara & mancanegara terverifikasi.` (icon: `globe`)
    - Pillar 3 (lines 32–33): `Pilihan Gilingan` / `Biji utuh atau digiling sesuai metode seduh favorit Anda.` (icon: `coffee`)
    - Pillar 4 (lines 42–43): `Dine-in & Delivery` / `Pesan langsung ke meja atau antar ke alamat Anda.` (icon: `sparkles`)
  - Does not read `$landingPage->getUniversalValues()`.

### 4.4 File 3: `category_chips.blade.php` (`resources/views/public/storefront/themes/fnb_cafe/category_chips.blade.php`)
- **Total Lines**: 62
- **Direct Observations & Violations**:
  - **Line 11**: Title uses `{{ __('storefront.menu_kategori') }}` with hardcoded inline hex `style="color: #3D2B1F;"`.
  - **Line 13**: Subtitle hardcoded: `Pilih kategori favorit Anda` with hardcoded inline hex `style="color: #8B5A2B99;"`.
  - **MISSING ALL CHIP**: Does not have "Semua Produk" chip.
  - **Lines 28–36**: Coffee-specific `$catIcons` array with hardcoded fallback icon `'coffee'`.
  - **Lines 52–54**: Clunky inline JS:
    ```blade
    onmouseover="this.style.borderColor='#8B5A2B'; this.style.background='linear-gradient(135deg, #FAF7F2, #F5EDE0)';"
    onmouseout="this.style.borderColor='rgba(139, 90, 43, 0.1)'; this.style.background='#FFFFFF';"
    ```
  - **Missing active state**: Does not style active category `request('category') == $cat->id`.

### 4.5 Adaptation Strategy for `fnb_cafe`
1. **Hero**:
   - Neutralize coffee bean SVG texture to subtle dot grid or theme-accented grain.
   - Bind eyebrow to `$heroEyebrow = $landingPage->announcement_badge ?: ($activeTheme['industry'] ?? __('storefront.hero.verified_official'));`.
   - Replace tasting notes with 3 Universal Badges (`$badgePill1..3`, `$badgeIcon1..3`).
   - Dynamic CTAs: `$primaryCtaText` (icon: `arrow-right`) and `$secondaryCtaText` (icon: `message-circle`).
   - Remove/neutralize the "Roast Level" widget.
   - Remove Unsplash photo URL. Render image if `$heroImage` exists, or an artisan warm roast brand bento card with initial monogram, `$business->name`, `$heroSubheadline`, and active status badge.
   - Preserve decorative steam animations (they provide subtle ambient delight).
2. **Features Banner**:
   - Loop over `$pillars = $landingPage->getUniversalValues();`.
   - Preserve rounded-[18px] card aesthetic and amber/emerald/blue/purple pastel icon containers.
3. **Category Chips**:
   - Dynamic heading: `{{ $landingPage->services_title ?: __('storefront.kategori_pilihan') }}`.
   - Dynamic subtitle: `{{ __('storefront.pilih_kategori_filter') }}`.
   - Add "Semua Produk" chip with `layout-grid` icon and active state.
   - Replace inline JS mouse events with Tailwind hover/active utilities.
   - Default fallback icon: `tag`.
   - Add active category highlight (`$isActive ? 'theme-btn-primary shadow-xs' : '...'`).

---

## 5. Deep-Dive Audit: Theme 4 — `fnb_catering`

### 5.1 Visual Archetype & Personality
- **Archetype**: `playful_fresh` (Playful, Fresh & Appetite)
- **Palette**: Primary `#059669` (Fresh Emerald), Accent `#F59E0B` (Warm Amber), Background `#F0FDF4`, Card `#FFFFFF`.
- **Typography**: Heading `Outfit` (clean geometric sans with modern warmth), Body `Plus Jakarta Sans`.
- **Aesthetic Essence**: Vibrant healthy green, friendly cards, clear clean layout, trustworthy badges.

### 5.2 File 1: `hero.blade.php` (`resources/views/public/storefront/themes/fnb_catering/hero.blade.php`)
- **Total Lines**: 82
- **Direct Observations & Violations**:
  1. **Lines 10–14**: Rigid Eyebrow:
     - Icon: `<i data-lucide="salad" class="w-4 h-4"></i>`.
     - Fallback text: `'Katering Sehat & Bento Box'`.
     - Static inline hexes: `#05966915` and `#059669`.
  2. **Line 17**: Hardcoded Headline fallback:
     ```blade
     {{ $landingPage->headline ?: 'Menu Sehat Harian & Katering Acara Terpercaya' }}
     ```
  3. **Line 21**: Hardcoded Subheadline fallback:
     ```blade
     {{ $landingPage->subheadline ?: 'Paket katering harian bergizi seimbang, nasi kotak bento higienis untuk kantor, dan prasmanan pesta lezat dengan bahan segar bebas MSG berlebih.' }}
     ```
  4. **Lines 25–37**: Rigid Badges:
     - Badge 1: `Higienis &amp; Terhitung Kalori` (icon: `check`)
     - Badge 2: `Langganan Mingguan / Bulanan` (icon: `calendar`)
     - Badge 3: `Antar Tepat Jam Makan Siang` (icon: `truck`)
  5. **Lines 41–56**: Rigid CTA Actions:
     - Line 43: hardcoded hex gradient `linear-gradient(135deg, #059669, #047857)`.
     - Line 44: fallback text `'Pilih Paket Katering'`.
     - Line 53: hardcoded secondary CTA text `<span>Konsultasi Menu Acara</span>`.
  6. **CRITICAL LEAKAGE — Line 62: Hardcoded Unsplash Photo**:
     ```blade
     $heroImg = $landingPage->hero_image_url ?: 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=1000&auto=format&fit=crop&q=80';
     ```
     *Impact*: Unsplash salad bowl photo hardcoded as default.
  7. **Lines 71–73**: Hardcoded floating badge on image:
     ```blade
     <span class="font-semibold text-neutral-900 dark:text-white">Dapur Bersih Berstandar BPOM</span>
     <span class="text-emerald-700 dark:text-emerald-400 font-bold font-mono">100% Halal</span>
     ```
  8. **Missing Fallback Card**: Forces Unsplash photo if no merchant image is uploaded.

### 5.3 File 2: `features_banner.blade.php` (`resources/views/public/storefront/themes/fnb_catering/features_banner.blade.php`)
- **Total Lines**: 47
- **Direct Observations & Violations**:
  - Static HTML grid with catering & meal prep pillars:
    - Pillar 1 (lines 11–12): `Gizi Seimbang Terkontrol` / `Komposisi protein, serat &amp; karbohidrat dihitung cermat.` (icon: `check-circle`)
    - Pillar 2 (lines 21–22): `Dapur Standar Sanitasi` / `Pengolahan makanan bersih, higienis, dan teruji.` (icon: `shield-check`)
    - Pillar 3 (lines 31–32): `Packaging Ramah Lingkungan` / `Bento box kokoh, anti tumpah, dan aman microwave.` (icon: `box`)
    - Pillar 4 (lines 41–42): `Antar Tepat Waktu` / `Katering tiba sebelum jam istirahat makan siang kantor.` (icon: `truck`)
  - Does not read `$landingPage->getUniversalValues()`.

### 5.4 File 3: `category_chips.blade.php` (`resources/views/public/storefront/themes/fnb_catering/category_chips.blade.php`)
- **Total Lines**: 58
- **Direct Observations & Violations**:
  - **Line 8**: Hardcoded heading: `{{ __('storefront.pilihan_paket_katering') }}`.
  - **Line 10**: Hardcoded subtitle: `Bento box harian, diet sehat, prasmanan acara &amp; snack box`.
  - **MISSING ALL CHIP**: Does not have a "Semua Produk" chip.
  - **Lines 22–34**: Catering-specific `$catIcons` array (`harian`, `diet`, `sehat`, `bento`, `box`, `prasmanan`, `acara`, `snack`, `tumpeng`, `kantor`) with default fallback icon `'utensils'`.
  - **Line 48**: Missing active state styling (`request('category') == $cat->id`).

### 5.5 Adaptation Strategy for `fnb_catering`
1. **Hero**:
   - Apply Universal Blade contract §3.1 header variables.
   - Replace salad icon with animated pulse dot.
   - Replace 3 rigid pills with `$badgePill1..3` and `$badgeIcon1..3`.
   - Dynamic CTAs: `$primaryCtaText` (icon: `arrow-right`) and `$secondaryCtaText` (icon: `message-circle`).
   - Remove Unsplash photo URL. If `$heroImage` exists, render image with universal floating status card (`__('storefront.hero.active_status')` & `__('storefront.hero.verified_official')`).
   - If `$heroImage` is null, render a fresh emerald brand bento card with monogram, business name, `$heroSubheadline`, and active status badge.
2. **Features Banner**:
   - Loop over `$pillars = $landingPage->getUniversalValues();`.
   - Retain the fresh rounded-2xl cards with emerald/teal/amber/blue pastel icon wrappers.
3. **Category Chips**:
   - Dynamic heading: `{{ $landingPage->services_title ?: __('storefront.kategori_pilihan') }}`.
   - Dynamic subtitle: `{{ __('storefront.pilih_kategori_filter') }}`.
   - Add "Semua Produk" chip with `layout-grid` icon and active state detection.
   - Replace `utensils` default fallback icon with neutral `tag`.
   - Add active chip styling (`$isActive ? 'theme-btn-primary shadow-xs' : '...'`).

---

## 6. Implementation Reference: Before vs. After Code Specifications

To facilitate direct execution by the developer agent, the following exact code patterns are prescribed for all 4 themes:

### 6.1 Hero Header Contract Block (§3.1)
```blade
@php
    // 1. Fallback Teks & Headline Universal
    $heroHeadline = $landingPage->headline ?: $business->name;
    $heroSubheadline = $landingPage->subheadline ?: ($business->description ?: __('storefront.hero.tagline'));
    $heroEyebrow = $landingPage->announcement_badge ?: ($activeTheme['industry'] ?? __('storefront.hero.verified_official'));

    // 2. Fallback Aksi Tombol CTA
    $primaryCtaText = $landingPage->cta_primary_text ?: __('storefront.hero.explore_catalog');
    $primaryCtaUrl  = $landingPage->cta_primary_url ?: url('/' . $business->slug . '/katalog');
    $secondaryCtaText = $landingPage->cta_secondary_text ?: __('storefront.hero.contact_us');
    $secondaryCtaUrl  = $landingPage->cta_secondary_url ?: $landingPage->getWhatsAppUrl();

    // 3. Fallback Nilai Keunggulan (Dynamic Badges dari values CMS)
    $universalValues = $landingPage->getUniversalValues();
    $badgePill1 = $universalValues[0]['title'];
    $badgeIcon1 = $universalValues[0]['icon'];
    $badgePill2 = $universalValues[1]['title'];
    $badgeIcon2 = $universalValues[1]['icon'];
    $badgePill3 = $universalValues[2]['title'];
    $badgeIcon3 = $universalValues[2]['icon'];

    // 4. Hero Visual / Image Fallback
    $heroImage = $landingPage->hero_image_url ?: ($landingPage->about_image_url ?: null);
@endphp
```

### 6.2 Universal Features Banner Contract Block (§3.2)
```blade
@php
    $pillars = $landingPage->getUniversalValues();
@endphp
```
Iterate via `@foreach ($pillars as $pillar)`:
- Icon: `<i data-lucide="{{ $pillar['icon'] }}" class="..."></i>`
- Title: `{{ $pillar['title'] }}`
- Description: `{{ $pillar['desc'] }}`

### 6.3 Universal Category Chips Contract Block (§3.3)
```blade
@if ($productCategories->isNotEmpty() && $landingPage->isPageActive('catalog'))
    <section class="...">
        <div class="flex items-center justify-between gap-4 mb-4 sm:mb-5">
            <div>
                <h2 class="...">
                    {{ $landingPage->services_title ?: __('storefront.kategori_pilihan') }}
                </h2>
                <p class="...">
                    {{ __('storefront.pilih_kategori_filter') }}
                </p>
            </div>
            <a href="{{ url('/' . $business->slug . '/katalog') }}" class="...">
                <span>{{ __('storefront.view_all') }}</span>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <div class="flex gap-2.5 sm:gap-3 overflow-x-auto pb-2 scrollbar-none">
            {{-- All Products Chip --}}
            <a href="{{ url('/' . $business->slug . '/katalog') }}"
               class="shrink-0 inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl text-xs font-semibold border min-h-[44px] transition-all {{ !request()->filled('category') ? 'theme-btn-primary shadow-xs' : 'bg-white dark:bg-neutral-800 border-black/10 dark:border-white/10' }}">
                <i data-lucide="layout-grid" class="w-4 h-4"></i>
                <span>{{ __('storefront.semua_produk') }}</span>
            </a>

            @foreach ($productCategories->take(12) as $cat)
                @php $isActive = request('category') == $cat->id; @endphp
                <a href="{{ url('/' . $business->slug . '/katalog?category=' . $cat->id) }}"
                   class="shrink-0 inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl text-xs font-semibold border min-h-[44px] transition-all {{ $isActive ? 'theme-btn-primary shadow-xs' : 'bg-white dark:bg-neutral-800 border-black/10 dark:border-white/10' }}">
                    <i data-lucide="tag" class="w-3.5 h-3.5"></i>
                    <span>{{ $cat->name }}</span>
                    @if (isset($cat->products_count))
                        <span class="text-[10px] px-2 py-0.5 rounded-full font-mono tabular-nums opacity-80">
                            {{ $cat->products_count }}
                        </span>
                    @endif
                </a>
            @endforeach
        </div>
    </section>
@endif
```

---

## 7. Verification & Testing Requirements

For empirical verification of Batch 2 Themes 1–4, an adversarial PHPUnit test suite must verify:
1. **Zero Forbidden Sector Phrases**:
   - `bakery_cake`: No "Dipanggang Pagi Ini", "Pure Butter", "Ragi Alami", "Roti Hangat", "sourdough", "Pre-Order Custom Cake", "pilih_roti_pastry", "Konsultasi Custom Cake", "Batch Pertama Oven", "Fermentasi Sourdough", "Dapur Pembuat Terverifikasi", "Hangat Dari Oven", "Sentuhan Keahlian Patisserie".
   - `fnb_bakery`: No "Roti Hangat & Kue Istimewa Setiap Hari", "Dipanggang segar setiap pagi", "Freshly Baked 06:00 WIB", "Custom Birthday Cake", "Pesan Cake Kustom", "Batch Pagi Sudah Siap Saji", "Panggang Setiap Pagi", "Pure European Butter", "Pilihan Roti & Kue".
   - `fnb_cafe`: No "Specialty coffee roastery", "Kafe & Kopi Spesialis", "Nikmati ritual kopi spesialti", "Single Origin hingga House Blend", "Tasting Notes", "Fruity", "Nutty", "Chocolaty", "Jelajahi Menu Kopi", "Roast Level", "Freshly Brewed", "Freshly Roasted", "Single Origin & Blend", "Pilihan Gilingan".
   - `fnb_catering`: No "Katering Sehat & Bento Box", "Menu Sehat Harian & Katering Acara Terpercaya", "Paket katering harian bergizi seimbang", "nasi kotak bento higienis", "Higienis & Terhitung Kalori", "Langganan Mingguan / Bulanan", "Antar Tepat Jam Makan Siang", "Pilih Paket Katering", "Konsultasi Menu Acara", "Dapur Bersih Berstandar BPOM", "Gizi Seimbang Terkontrol", "pilihan_paket_katering".
2. **Zero Hardcoded Unsplash Photos**:
   - `photo-1509440159596` (`fnb_bakery`) MUST NOT appear.
   - `photo-1495474472287` (`fnb_cafe`) MUST NOT appear.
   - `photo-1546069901` (`fnb_catering`) MUST NOT appear.
   - `images.unsplash.com` MUST NOT appear across any of the 4 themes.
3. **Dynamic CMS Values Injection**:
   - When a business has custom values in `$landingPage->values`, all 4 custom titles and icons MUST be rendered in `features_banner.blade.php`.
   - The first 3 custom titles and icons MUST be rendered as the 3 badges in `hero.blade.php`.
4. **Brand Bento Fallback When Hero Image is Null**:
   - When `$landingPage->hero_image_url` is null and `$landingPage->about_image_url` is null, the view MUST render the brand bento showcase containing `$business->name`, initial monogram, `$heroSubheadline`, and `__('storefront.hero.active_status')`.
5. **Ergonomic Category Navigation**:
   - The "Semua Produk" pill MUST exist and link to `/{slug}/katalog`.
   - Active category filtering MUST highlight the active chip with `theme-btn-primary`.
   - All category chips MUST meet minimum touch target height (`min-h-[44px]`).
