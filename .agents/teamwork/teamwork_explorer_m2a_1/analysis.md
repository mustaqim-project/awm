# Detailed Audit & Universal Contract Mapping Report (Themes 1 to 4)

**Milestone:** Milestone 2A (Batch 1 Themes Refactoring)  
**Target Themes:**
1. `universal_storefront` (Archetype: Clean Bento & Modern Tech)
2. `modern_umkm` (Archetype: Clean Bento & Modern Tech)
3. `online_store` (Archetype: Clean Bento & Modern Tech)
4. `business_corporate` (Archetype: Institutional & Trust)

**Target Components per Theme:**
- `hero.blade.php`
- `features_banner.blade.php`
- `category_chips.blade.php`

---

## 1. Executive Summary & Cross-Theme Findings

Across all 4 inspected themes, three common structural defects were identified that cause feature leakage and prevent them from operating as truly universal design archetypes:

1. **Static Unsplash Images with Industry Bias:**
   - `universal_storefront/hero.blade.php` (line 53) and `modern_umkm/hero.blade.php` (line 59) fallback to a food/restaurant photo (`photo-1555396273-367ea4eb4db5`).
   - `business_corporate/hero.blade.php` (line 64) fallbacks to an office building skyscraper photo (`photo-1486406146926-c627a92ad1ab`).
   - **Resolution:** When `$landingPage->hero_image_url` and `$landingPage->about_image_url` are null, render an elegant, neutral **Brand Bento Graphic Showcase** card styled with the theme's dynamic tokens (`var(--theme-primary)`), initial brand monogram avatar, business name, subheadline, and verified status badge. Zero static sector Unsplash images!

2. **Hardcoded Sector Copy & Static Indonesian / English Strings:**
   - Hero headlines and subheadlines frequently hardcode niche sector claims ("Outlet Resmi & Siap Melayani", "100% Produk Lokal", "Belanja Sekarang", "Jelajahi Etalase", "Corporate Enterprise & B2B Solutions", "Faktur Pajak Tersedia").
   - Features banners hardcode 4 static cards with sector descriptions, completely ignoring `$landingPage->getUniversalValues()`.
   - Category chips have hardcoded helper texts ("Pilih kategori untuk memfilter etalase belanja", "Pilih departemen produk yang Anda cari", "Pilih kategori untuk memfilter spesifikasi pengadaan") instead of using `{{ __('storefront.pilih_kategori_filter') }}`.
   - **Resolution:** Full adherence to the 3-level fallback hierarchy: `$landingPage` CMS input &rarr; `$business` master profile &rarr; `__('storefront...')` universal i18n dictionary.

3. **Hardcoded Color Utilities Overriding Theme Variables:**
   - `modern_umkm` heavily hardcodes emerald colors (`bg-emerald-50`, `bg-emerald-600`, `text-emerald-700`, `border-emerald-300`, etc.).
   - `online_store` hardcodes indigo/violet utility classes (`text-indigo-600`, `bg-indigo-50`, `border-indigo-100`, etc.).
   - `business_corporate` hardcodes blue utility classes (`bg-blue-600`, `text-blue-700`, `border-blue-600/20`, etc.).
   - **Resolution:** Re-bind buttons, badges, links, and borders to dynamic theme tokens: `.theme-btn-primary`, `.theme-badge`, `.theme-link`, and CSS variable `var(--theme-primary)`.

---

## 2. Deep Dive Audit per Theme

### 2.1 Theme 1: `universal_storefront`
- **Path:** `resources/views/public/storefront/themes/universal_storefront/`
- **Archetype:** `clean_bento` (Clean Bento & Modern Tech)
- **Palette:** Primary `#007AFF`, Accent `#34C759`, Bg `#FFFFFF`, Card `#F8FAFC`, Badge `#007AFF15` / `#007AFF`, Radius `16px`.
- **Fonts:** Heading: Plus Jakarta Sans, Body: Inter.

#### File 1: `hero.blade.php`
- **Direct Observations:**
  - Line 13: `<span>{{ $activeTheme['industry'] ?? 'Toko Resmi & Terverifikasi' }}</span>` ignores `$landingPage->announcement_badge` and i18n fallback `__('storefront.hero.verified_official')`.
  - Line 21: Subheadline fallbacks to hardcoded string `'Temukan produk & layanan terbaik kami dengan kualitas terjamin, pemesanan mudah, dan transaksi aman langsung dari outlet resmi.'`.
  - Line 28: Primary CTA text fallbacks to `'Jelajahi Katalog'` instead of `__('storefront.hero.explore_catalog')`. Primary CTA URL does not check `$landingPage->cta_primary_url`.
  - Line 34-38: Secondary CTA text hardcodes `'Tanya CS WhatsApp'` instead of `$landingPage->cta_secondary_text ?: __('storefront.hero.contact_us')`. Secondary URL does not check `$landingPage->cta_secondary_url`.
  - Missing 3 Universal Badges block: No dynamic badges rendered from `$landingPage->getUniversalValues()`.
  - Line 53: Hardcoded Unsplash food image `'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=1000&auto=format&fit=crop&q=80'`.
  - Lines 63-65: Hardcoded floating status pill strings: `"Outlet Resmi &amp; Siap Melayani"` and `"100% Terverifikasi"`.
- **Proposed Refactoring:**
  ```blade
  {{-- universal_storefront/hero.blade.php --}}
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

  <section class="relative overflow-hidden pt-8 pb-16 sm:pt-16 sm:pb-24">
      <div class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8">
          <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">

              {{-- Hero Copy --}}
              <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                  <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-semibold theme-badge shadow-xs">
                      <span class="w-2 h-2 rounded-full bg-current animate-pulse"></span>
                      <span>{{ $heroEyebrow }}</span>
                  </div>

                  <h1 class="font-heading font-extrabold text-3xl sm:text-5xl lg:text-6xl text-neutral-900 dark:text-white tracking-tight leading-[1.15]">
                      {{ $heroHeadline }}
                  </h1>

                  <p class="text-base sm:text-lg text-neutral-600 dark:text-neutral-300 max-w-2xl mx-auto lg:mx-0 leading-relaxed">
                      {{ $heroSubheadline }}
                  </p>

                  {{-- 3 Universal Badges --}}
                  <div class="flex flex-wrap items-center justify-center lg:justify-start gap-2 pt-1">
                      <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold theme-badge border border-black/5 dark:border-white/10">
                          <i data-lucide="{{ $badgeIcon1 }}" class="w-3.5 h-3.5"></i>
                          <span>{{ $badgePill1 }}</span>
                      </span>
                      <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold theme-badge border border-black/5 dark:border-white/10">
                          <i data-lucide="{{ $badgeIcon2 }}" class="w-3.5 h-3.5"></i>
                          <span>{{ $badgePill2 }}</span>
                      </span>
                      <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold theme-badge border border-black/5 dark:border-white/10">
                          <i data-lucide="{{ $badgeIcon3 }}" class="w-3.5 h-3.5"></i>
                          <span>{{ $badgePill3 }}</span>
                      </span>
                  </div>

                  {{-- Action Buttons --}}
                  <div class="flex flex-wrap items-center justify-center lg:justify-start gap-3 sm:gap-4 pt-2">
                      @if ($landingPage->isPageActive('catalog'))
                          <a href="{{ $primaryCtaUrl }}"
                              class="px-7 py-3.5 text-sm sm:text-base font-semibold theme-btn-primary shadow-md flex items-center gap-2 min-h-[48px]">
                              <span>{{ $primaryCtaText }}</span>
                              <i data-lucide="arrow-right" class="w-4 h-4"></i>
                          </a>
                      @endif

                      @if ($hasWhatsapp)
                          <a href="{{ $secondaryCtaUrl }}" target="_blank" rel="noopener"
                              class="px-6 py-3.5 text-sm sm:text-base font-semibold rounded-2xl bg-white dark:bg-neutral-800 text-neutral-800 dark:text-white border border-black/10 dark:border-white/10 hover:bg-neutral-50 dark:hover:bg-neutral-700 shadow-xs transition flex items-center gap-2 min-h-[48px]">
                              <i data-lucide="message-circle" class="w-4 h-4 text-emerald-500"></i>
                              <span>{{ $secondaryCtaText }}</span>
                          </a>
                      @endif

                      <button type="button" @click="$dispatch('open-order-track-modal')"
                          class="px-4 py-3.5 text-xs sm:text-sm font-semibold text-neutral-600 dark:text-neutral-400 hover:text-theme-primary transition flex items-center gap-1.5 min-h-[48px]">
                          <i data-lucide="search" class="w-4 h-4"></i>
                          <span>{{ __('storefront.order_tracking') }}</span>
                      </button>
                  </div>
              </div>

              {{-- Hero Visual --}}
              <div class="lg:col-span-5">
                  <div class="relative mx-auto max-w-md lg:max-w-none">
                      <div class="relative rounded-3xl overflow-hidden shadow-2xl border border-black/5 dark:border-white/10 aspect-[4/3] sm:aspect-square bg-neutral-100 dark:bg-neutral-800">
                          @if ($heroImage)
                              <img src="{{ $heroImage }}" alt="{{ $business->name }}" class="w-full h-full object-cover">
                              <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>

                              {{-- Floating Status Pill --}}
                              <div class="absolute bottom-4 left-4 right-4 p-4 rounded-2xl bg-white/90 dark:bg-neutral-900/90 backdrop-blur-md border border-white/20 dark:border-black/20 text-xs shadow-lg flex items-center justify-between">
                                  <div class="flex items-center gap-2.5">
                                      <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></div>
                                      <span class="font-semibold text-neutral-900 dark:text-white">{{ __('storefront.hero.active_status') }}</span>
                                  </div>
                                  <span class="text-neutral-500 dark:text-neutral-400 font-mono text-[11px]">{{ __('storefront.hero.verified_official') }}</span>
                              </div>
                          @else
                              {{-- Brand Bento Graphic Showcase (Universal Fallback) --}}
                              <div class="w-full h-full p-8 bg-gradient-to-br from-neutral-900 to-neutral-800 text-white flex flex-col justify-between">
                                  <div class="flex items-center justify-between">
                                      <span class="w-14 h-14 rounded-2xl flex items-center justify-center font-bold text-2xl text-white shadow-md" style="background-color: var(--theme-primary);">
                                          {{ strtoupper(substr($business->name, 0, 2)) }}
                                      </span>
                                      <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider theme-badge">
                                          {{ __('storefront.hero.active_status') }}
                                      </span>
                                  </div>
                                  <div class="space-y-2 mt-auto">
                                      <h3 class="font-heading font-bold text-2xl text-white">{{ $business->name }}</h3>
                                      <p class="text-xs text-neutral-300 leading-relaxed">{{ $heroSubheadline }}</p>
                                  </div>
                              </div>
                          @endif
                      </div>
                  </div>
              </div>

          </div>
      </div>
  </section>
  ```

#### File 2: `features_banner.blade.php`
- **Direct Observations:**
  - 4 static cards with hardcoded strings:
    - Card 1: `{{ __('storefront.outlet_resmi_terverifikasi') }}`, `"Produk dan layanan 100% original dari merchant terpercaya."`
    - Card 2: `{{ __('storefront.pembayaran_mudah') }}`, `"Mendukung transfer bank, QRIS, e-wallet, dan COD."`
    - Card 3: `{{ __('storefront.pengiriman_aman') }}`, `"Ekspedisi terpercaya dengan pelacakan nomor resi otomatis."`
    - Card 4: `"Customer Support Siaga"`, `"Konsultasi langsung dengan admin via WhatsApp."`
  - Completely static; ignores `$landingPage->values` and `$landingPage->getUniversalValues()`.
- **Proposed Refactoring:**
  ```blade
  {{-- universal_storefront/features_banner.blade.php --}}
  @php
      $pillars = $landingPage->getUniversalValues();
  @endphp

  <section class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
          @foreach ($pillars as $pillar)
              <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-neutral-800/80 border border-black/5 dark:border-white/10 shadow-xs flex items-start gap-3">
                  <div class="w-10 h-10 rounded-xl theme-badge flex items-center justify-center shrink-0">
                      <i data-lucide="{{ $pillar['icon'] }}" class="w-5 h-5"></i>
                  </div>
                  <div>
                      <h4 class="text-[13px] sm:text-[14px] font-bold text-neutral-900 dark:text-white leading-snug">
                          {{ $pillar['title'] }}
                      </h4>
                      <p class="text-[11px] sm:text-[12px] text-neutral-500 dark:text-neutral-400 mt-0.5 leading-relaxed">
                          {{ $pillar['desc'] }}
                      </p>
                  </div>
              </div>
          @endforeach
      </div>
  </section>
  ```

#### File 3: `category_chips.blade.php`
- **Direct Observations:**
  - Line 9: Title does not support `$landingPage->services_title` or category title override.
  - Line 11: `"Pilih kategori untuk memfilter etalase belanja"` is hardcoded Indonesian text.
  - Missing the "Semua Produk" chip with `layout-grid` icon.
  - Does not indicate active status when a category is selected (`request('category') == $cat->id`).
  - Limits to 8 categories instead of standard 12.
- **Proposed Refactoring:**
  ```blade
  {{-- universal_storefront/category_chips.blade.php --}}
  @if ($productCategories->isNotEmpty() && $landingPage->isPageActive('catalog'))
      <section class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8 py-4">
          <div class="flex items-center justify-between gap-4 mb-4">
              <div>
                  <h2 class="font-heading font-bold text-xl sm:text-2xl text-neutral-900 dark:text-white">
                      {{ $landingPage->services_title ?: __('storefront.kategori_pilihan') }}
                  </h2>
                  <p class="text-xs sm:text-sm text-neutral-500 dark:text-neutral-400 mt-0.5">
                      {{ __('storefront.pilih_kategori_filter') }}
                  </p>
              </div>
              <a href="{{ url('/' . $business->slug . '/katalog') }}"
                  class="text-xs sm:text-sm font-semibold flex items-center gap-1 hover:underline text-theme-primary">
                  <span>{{ __('storefront.view_all') }}</span>
                  <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
              </a>
          </div>

          <div class="flex gap-2.5 overflow-x-auto pb-2 -mx-4 px-4 sm:mx-0 sm:px-0 sm:flex-wrap scrollbar-none">
              {{-- Semua Produk Chip --}}
              <a href="{{ url('/' . $business->slug . '/katalog') }}"
                  class="flex-shrink-0 inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-medium transition-all duration-200 min-h-[44px] {{ !request()->filled('category') ? 'theme-btn-primary shadow-xs' : 'bg-white dark:bg-neutral-800/90 border border-black/5 dark:border-white/10 text-neutral-800 dark:text-neutral-200 hover:shadow-xs' }}">
                  <i data-lucide="layout-grid" class="w-4 h-4"></i>
                  <span>{{ __('storefront.semua_produk') }}</span>
              </a>

              @foreach ($productCategories->take(12) as $cat)
                  @php $isActive = request('category') == $cat->id; @endphp
                  <a href="{{ url('/' . $business->slug . '/katalog?category=' . $cat->id) }}"
                      class="flex-shrink-0 inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-medium transition-all duration-200 min-h-[44px] {{ $isActive ? 'theme-btn-primary shadow-xs' : 'bg-white dark:bg-neutral-800/90 border border-black/5 dark:border-white/10 text-neutral-800 dark:text-neutral-200 hover:shadow-xs' }}">
                      <i data-lucide="tag" class="w-3.5 h-3.5"></i>
                      <span>{{ $cat->name }}</span>
                  </a>
              @endforeach
          </div>
      </section>
  @endif
  ```

---

### 2.2 Theme 2: `modern_umkm`
- **Path:** `resources/views/public/storefront/themes/modern_umkm/`
- **Archetype:** `clean_bento` (Clean Bento & Modern Tech)
- **Palette:** Primary `#059669`, Accent `#D97706`, Bg `#F0FDF4`, Card `#FFFFFF`, Badge `#05966915` / `#059669`, Radius `16px`.
- **Fonts:** Heading: Plus Jakarta Sans, Body: Inter.

#### File 1: `hero.blade.php`
- **Direct Observations:**
  - Section header and eyebrow have hardcoded emerald classes:
    - Line 5: `bg-emerald-50/50 dark:bg-emerald-950/20 border-b border-emerald-900/10`
    - Line 11: `bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300`
  - Line 13: `{{ $activeTheme['industry'] ?? 'Produk & Usaha UMKM Pilihan' }}` ignores `$landingPage->announcement_badge` and `__('storefront.hero.verified_official')`.
  - Line 17: `{{ $landingPage->headline ?: 'Kualitas Terbaik dari ' . $business->name . ' untuk Kebutuhan Anda' }}` -> standardize fallback to `$business->name`.
  - Line 21: Subheadline hardcodes `'Dibuat dengan sepenuh hati, harga ramah di kantong, dan siap dikirim langsung dari outlet kami ke tempat Anda.'`.
  - Line 27: Primary CTA has hardcoded emerald button: `bg-emerald-600 hover:bg-emerald-700` and text `'Belanja Sekarang'` instead of `__('storefront.hero.explore_catalog')`.
  - Line 35: Secondary CTA has hardcoded emerald text and border: `text-emerald-700 dark:text-emerald-300 border border-emerald-300` and hardcoded text `'Chat Pemilik Toko'`.
  - Lines 43-52: Hardcoded static highlights:
    - `"100% Produk Lokal"` with rose heart icon.
    - `"Terverifikasi COOCA"` with badge-check icon.
  - Line 59: Hardcoded Unsplash food image `'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=1000&auto=format&fit=crop&q=80'`.
  - Line 68 & 70: Hardcoded floating pill texts: `"Outlet Resmi Buka Hari Ini"` and `"Siap Kirim"`.
- **Proposed Refactoring:**
  ```blade
  {{-- modern_umkm/hero.blade.php --}}
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

  <section class="relative overflow-hidden pt-8 pb-14 sm:pt-14 sm:pb-20 border-b border-black/[0.06] dark:border-white/[0.06]">
      <div class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8">
          <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">

              {{-- Copy --}}
              <div class="lg:col-span-7 space-y-5 text-center lg:text-left">
                  <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-bold theme-badge shadow-2xs">
                      <span class="w-2 h-2 rounded-full bg-current animate-pulse"></span>
                      <span>{{ $heroEyebrow }}</span>
                  </div>

                  <h1 class="font-heading font-extrabold text-3xl sm:text-5xl text-neutral-900 dark:text-white tracking-tight leading-tight">
                      {{ $heroHeadline }}
                  </h1>

                  <p class="text-base sm:text-lg text-neutral-600 dark:text-neutral-300 max-w-2xl mx-auto lg:mx-0 leading-relaxed">
                      {{ $heroSubheadline }}
                  </p>

                  {{-- Action Row --}}
                  <div class="flex flex-wrap items-center justify-center lg:justify-start gap-3 sm:gap-4 pt-2">
                      @if ($landingPage->isPageActive('catalog'))
                          <a href="{{ $primaryCtaUrl }}"
                              class="px-6 py-3.5 text-sm sm:text-base font-bold theme-btn-primary shadow-md flex items-center gap-2 min-h-[48px]">
                              <span>{{ $primaryCtaText }}</span>
                              <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                          </a>
                      @endif

                      @if ($hasWhatsapp)
                          <a href="{{ $secondaryCtaUrl }}" target="_blank" rel="noopener"
                              class="px-6 py-3.5 text-sm sm:text-base font-bold rounded-xl bg-white dark:bg-neutral-800 text-neutral-800 dark:text-white border border-black/10 dark:border-white/10 hover:bg-neutral-50 dark:hover:bg-neutral-700 shadow-xs transition flex items-center gap-2 min-h-[48px]">
                              <i data-lucide="message-circle" class="w-4 h-4 text-emerald-500"></i>
                              <span>{{ $secondaryCtaText }}</span>
                          </a>
                      @endif
                  </div>

                  {{-- Dynamic UMKM Universal Highlights (Contract §3.1) --}}
                  <div class="pt-4 flex flex-wrap items-center justify-center lg:justify-start gap-4 sm:gap-6 text-xs text-neutral-600 dark:text-neutral-400 font-medium">
                      <div class="flex items-center gap-1.5">
                          <i data-lucide="{{ $badgeIcon1 }}" class="w-4 h-4 text-theme-primary"></i>
                          <span>{{ $badgePill1 }}</span>
                      </div>
                      <div class="flex items-center gap-1.5">
                          <i data-lucide="{{ $badgeIcon2 }}" class="w-4 h-4 text-theme-primary"></i>
                          <span>{{ $badgePill2 }}</span>
                      </div>
                      <div class="flex items-center gap-1.5">
                          <i data-lucide="{{ $badgeIcon3 }}" class="w-4 h-4 text-theme-primary"></i>
                          <span>{{ $badgePill3 }}</span>
                      </div>
                  </div>
              </div>

              {{-- Visual --}}
              <div class="lg:col-span-5">
                  <div class="relative mx-auto max-w-md lg:max-w-none">
                      <div class="relative rounded-2xl overflow-hidden shadow-xl border border-black/5 dark:border-white/10 aspect-square bg-neutral-100 dark:bg-neutral-800">
                          @if ($heroImage)
                              <img src="{{ $heroImage }}" alt="{{ $business->name }}" class="w-full h-full object-cover">
                              <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>

                              <div class="absolute bottom-4 left-4 right-4 p-3.5 rounded-xl bg-white/95 dark:bg-neutral-900/95 backdrop-blur-md text-xs shadow-md flex items-center justify-between">
                                  <div class="flex items-center gap-2">
                                      <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                      <span class="font-bold text-neutral-900 dark:text-white">{{ __('storefront.hero.active_status') }}</span>
                                  </div>
                                  <span class="font-bold text-theme-primary">{{ __('storefront.hero.verified_official') }}</span>
                              </div>
                          @else
                              {{-- Brand Bento Graphic Showcase (Universal Fallback) --}}
                              <div class="w-full h-full p-8 bg-gradient-to-br from-neutral-900 to-neutral-800 text-white flex flex-col justify-between">
                                  <div class="flex items-center justify-between">
                                      <span class="w-14 h-14 rounded-2xl flex items-center justify-center font-bold text-2xl text-white shadow-md" style="background-color: var(--theme-primary);">
                                          {{ strtoupper(substr($business->name, 0, 2)) }}
                                      </span>
                                      <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider theme-badge">
                                          {{ __('storefront.hero.active_status') }}
                                      </span>
                                  </div>
                                  <div class="space-y-2 mt-auto">
                                      <h3 class="font-heading font-bold text-2xl text-white">{{ $business->name }}</h3>
                                      <p class="text-xs text-neutral-300 leading-relaxed">{{ $heroSubheadline }}</p>
                                  </div>
                              </div>
                          @endif
                      </div>
                  </div>
              </div>

          </div>
      </div>
  </section>
  ```

#### File 2: `features_banner.blade.php`
- **Direct Observations:**
  - Card container has hardcoded emerald border: `border-emerald-900/10 dark:border-emerald-500/20`.
  - Icon boxes have hardcoded emerald colors: `bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300`.
  - 4 static cards with hardcoded texts:
    - `"100% Asli & Terjamin"`, `"Kualitas terjaga dari tangan pertama pemilik usaha."`
    - `{{ __('storefront.harga_ramah_kantong') }}`, `"Harga hemat terjangkau tanpa perantara berlebihan."`
    - `{{ __('storefront.kirim_cepat_aman') }}`, `"Packing rapi dengan opsi kurir instan dan reguler."`
    - `"Pelayanan Ramah"`, `"Siap melayani tanya jawab pesanan kapan saja."`
- **Proposed Refactoring:**
  ```blade
  {{-- modern_umkm/features_banner.blade.php --}}
  @php
      $pillars = $landingPage->getUniversalValues();
  @endphp

  <section class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-6 sm:p-7 rounded-2xl bg-white dark:bg-neutral-900 border border-black/5 dark:border-white/10 shadow-xs">
          @foreach ($pillars as $pillar)
              <div class="flex items-start gap-3">
                  <div class="w-10 h-10 rounded-xl theme-badge flex items-center justify-center shrink-0">
                      <i data-lucide="{{ $pillar['icon'] }}" class="w-5 h-5"></i>
                  </div>
                  <div>
                      <h4 class="font-heading font-bold text-xs sm:text-sm text-neutral-900 dark:text-white">
                          {{ $pillar['title'] }}
                      </h4>
                      <p class="text-[11px] text-neutral-500 dark:text-neutral-400 mt-0.5 line-clamp-2 leading-relaxed">
                          {{ $pillar['desc'] }}
                      </p>
                  </div>
              </div>
          @endforeach
      </div>
  </section>
  ```

#### File 3: `category_chips.blade.php`
- **Direct Observations:**
  - Line 9: Hardcoded `bg-emerald-500` dot.
  - Line 10: `{{ __('storefront.pilihan_produk_layanan') }}` lacks subtitle and override support.
  - Line 13: Hardcoded `text-emerald-600 dark:text-emerald-400 hover:text-emerald-700`.
  - Line 22: Hardcoded emerald border, hover background, and hover text.
  - Line 23: Hardcoded `bg-emerald-400` indicator dot.
  - Missing the "Semua Produk" chip.
  - Does not support selected/active category state.
- **Proposed Refactoring:**
  ```blade
  {{-- modern_umkm/category_chips.blade.php --}}
  @if ($productCategories->isNotEmpty() && $landingPage->isPageActive('catalog'))
      <section class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-4">
          <div class="flex items-center justify-between gap-4 mb-4">
              <div class="flex items-center gap-2">
                  <span class="w-2.5 h-2.5 rounded-full bg-theme-primary animate-pulse"></span>
                  <div>
                      <h2 class="font-heading font-bold text-lg sm:text-xl text-neutral-900 dark:text-white">
                          {{ $landingPage->services_title ?: __('storefront.pilihan_produk_layanan') }}
                      </h2>
                      <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">
                          {{ __('storefront.pilih_kategori_filter') }}
                      </p>
                  </div>
              </div>
              <a href="{{ url('/' . $business->slug . '/katalog') }}"
                  class="text-xs font-semibold text-theme-primary hover:underline flex items-center gap-1 group">
                  <span>{{ __('storefront.view_all') }}</span>
                  <i data-lucide="arrow-right" class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform"></i>
              </a>
          </div>

          <div class="flex gap-2.5 overflow-x-auto pb-2 scrollbar-none">
              {{-- Semua Produk Chip --}}
              <a href="{{ url('/' . $business->slug . '/katalog') }}"
                  class="flex-shrink-0 inline-flex items-center gap-2 px-4 py-2.5 rounded-full text-xs font-medium transition-all duration-200 min-h-[44px] {{ !request()->filled('category') ? 'theme-btn-primary shadow-xs' : 'border border-black/10 dark:border-white/10 bg-white dark:bg-neutral-900 text-neutral-700 dark:text-neutral-200 hover:border-theme-primary/40' }}">
                  <i data-lucide="layout-grid" class="w-3.5 h-3.5"></i>
                  <span>{{ __('storefront.semua_produk') }}</span>
              </a>

              @foreach ($productCategories->take(12) as $cat)
                  @php $isActive = request('category') == $cat->id; @endphp
                  <a href="{{ url('/' . $business->slug . '/katalog?category=' . $cat->id) }}"
                      class="flex-shrink-0 inline-flex items-center gap-2 px-4 py-2.5 rounded-full text-xs font-medium transition-all duration-200 min-h-[44px] {{ $isActive ? 'theme-btn-primary shadow-xs' : 'border border-black/10 dark:border-white/10 bg-white dark:bg-neutral-900 text-neutral-700 dark:text-neutral-200 hover:border-theme-primary/40' }}">
                      <span class="w-2 h-2 rounded-full {{ $isActive ? 'bg-white' : 'bg-theme-primary' }}"></span>
                      <span>{{ $cat->name }}</span>
                  </a>
              @endforeach
          </div>
      </section>
  @endif
  ```

---

### 2.3 Theme 3: `online_store`
- **Path:** `resources/views/public/storefront/themes/online_store/`
- **Archetype:** `clean_bento` (Clean Bento & Modern Tech)
- **Palette:** Primary `#18181B`, Accent `#6366F1`, Bg `#FFFFFF`, Card `#FAFAFA`, Badge `#18181B15` / `#18181B`, Radius `16px`.
- **Fonts:** Heading: Urbanist, Body: Inter.

#### File 1: `hero.blade.php`
- **Direct Observations:**
  - Lines 18-20: Hardcoded retail eyebrow: `"Official Store"` and `"Koleksi Terbaru & Ready Stock"` instead of `$heroEyebrow`.
  - Line 25: Headline hardcodes `'Belanja Produk Pilihan, Kualitas Premium & Bergaransi'`.
  - Line 29: Subheadline hardcodes `'Temukan beragam pilihan produk terbaik dengan jaminan originalitas, proses transaksi aman, dan pengiriman kilat ke seluruh nusantara.'`.
  - Line 36-39: Primary CTA has hardcoded zinc background and text `'Jelajahi Etalase'` instead of `__('storefront.hero.explore_catalog')`.
  - Line 44-47: Secondary CTA has hardcoded emerald background and text `'Chat Admin Toko'` instead of `__('storefront.hero.contact_us')`.
  - Lines 52-65: Hardcoded static retail trust pillars:
    - `"100% Ori"`, `"Jaminan Asli"`
    - `"Fast Pack"`, `"Kirim Hari Ini"`
    - `"Aman"`, `"Garansi Retur"`
  - Line 81: Brand fallback has hardcoded promo text: `"Destinasi belanja pilihan dengan promo spesial setiap minggu."`.
  - Lines 85-97: Floating Flash Badge has hardcoded promo text: `"Promo Flash Order"`, `"Nikmati subsidi ongkir hari ini"`, `"Cek Produk &rarr;"` with hardcoded indigo classes (`bg-indigo-50`, `text-indigo-600`).
- **Proposed Refactoring:**
  ```blade
  {{-- online_store/hero.blade.php --}}
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

  <section class="relative overflow-hidden bg-white dark:bg-zinc-950 border-b border-zinc-200/80 dark:border-zinc-800 transition-colors">
      {{-- Background Glow --}}
      <div class="absolute -top-32 right-0 w-96 h-96 bg-theme-primary/5 rounded-full blur-3xl pointer-events-none"></div>
      <div class="absolute bottom-0 left-10 w-72 h-72 bg-theme-accent/5 rounded-full blur-2xl pointer-events-none"></div>

      <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 lg:py-20 relative">
          <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">

              {{-- Text & CTA Column --}}
              <div class="lg:col-span-7 space-y-6">
                  {{-- Announcement Chip --}}
                  <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-zinc-100 dark:bg-zinc-800/80 border border-zinc-200 dark:border-zinc-700/80 text-xs font-semibold text-zinc-900 dark:text-zinc-100 shadow-2xs">
                      <span class="flex h-2 w-2 rounded-full bg-theme-primary animate-ping"></span>
                      <span class="text-theme-primary uppercase tracking-wider text-[10px] font-black">{{ __('storefront.hero.verified_official') }}</span>
                      <span class="text-zinc-300 dark:text-zinc-600">|</span>
                      <span class="font-medium text-zinc-600 dark:text-zinc-300">{{ $heroEyebrow }}</span>
                  </div>

                  {{-- Headline --}}
                  <h1 class="font-heading font-extrabold text-3xl sm:text-4xl lg:text-5xl text-zinc-950 dark:text-white tracking-tight leading-[1.15]">
                      {{ $heroHeadline }}
                  </h1>

                  <p class="text-sm sm:text-base text-zinc-600 dark:text-zinc-400 max-w-xl leading-relaxed">
                      {{ $heroSubheadline }}
                  </p>

                  {{-- Action Buttons --}}
                  <div class="flex flex-wrap items-center gap-3 pt-2">
                      @if ($landingPage->isPageActive('catalog'))
                          <a href="{{ $primaryCtaUrl }}"
                              class="px-6 py-3.5 rounded-xl theme-btn-primary font-semibold text-sm shadow-md hover:shadow-lg transition-all flex items-center gap-2 min-h-[48px]">
                              <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                              <span>{{ $primaryCtaText }}</span>
                          </a>
                      @endif

                      @if ($hasWhatsapp)
                          <a href="{{ $secondaryCtaUrl }}" target="_blank" rel="noopener"
                              class="px-5 py-3.5 rounded-xl bg-white dark:bg-zinc-900 text-zinc-800 dark:text-zinc-100 border border-zinc-300 dark:border-zinc-700 font-semibold text-sm shadow-xs hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-all flex items-center gap-2 min-h-[48px]">
                              <i data-lucide="message-circle" class="w-4 h-4 text-emerald-500"></i>
                              <span>{{ $secondaryCtaText }}</span>
                          </a>
                      @endif
                  </div>

                  {{-- Dynamic Trust Pillars (Contract §3.1) --}}
                  <div class="grid grid-cols-3 gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-800 text-left">
                      <div class="flex items-start gap-2">
                          <i data-lucide="{{ $badgeIcon1 }}" class="w-4 h-4 text-theme-primary shrink-0 mt-0.5"></i>
                          <div>
                              <span class="block font-heading font-bold text-xs sm:text-sm text-zinc-900 dark:text-white">{{ $badgePill1 }}</span>
                              <span class="text-[11px] text-zinc-500 dark:text-zinc-400">{{ __('storefront.hero.verified_official') }}</span>
                          </div>
                      </div>
                      <div class="flex items-start gap-2">
                          <i data-lucide="{{ $badgeIcon2 }}" class="w-4 h-4 text-theme-primary shrink-0 mt-0.5"></i>
                          <div>
                              <span class="block font-heading font-bold text-xs sm:text-sm text-zinc-900 dark:text-white">{{ $badgePill2 }}</span>
                              <span class="text-[11px] text-zinc-500 dark:text-zinc-400">{{ __('storefront.hero.active_status') }}</span>
                          </div>
                      </div>
                      <div class="flex items-start gap-2">
                          <i data-lucide="{{ $badgeIcon3 }}" class="w-4 h-4 text-theme-primary shrink-0 mt-0.5"></i>
                          <div>
                              <span class="block font-heading font-bold text-xs sm:text-sm text-zinc-900 dark:text-white">{{ $badgePill3 }}</span>
                              <span class="text-[11px] text-zinc-500 dark:text-zinc-400">{{ __('storefront.badges.quality_guaranteed') }}</span>
                          </div>
                      </div>
                  </div>
              </div>

              {{-- Image / Visual Card Column --}}
              <div class="lg:col-span-5">
                  <div class="relative mx-auto max-w-md lg:max-w-none">
                      <div class="relative rounded-2xl overflow-hidden bg-zinc-100 dark:bg-zinc-900 border border-zinc-200/80 dark:border-zinc-800 shadow-xl aspect-[4/3] sm:aspect-[16/11]">
                          @if ($heroImage)
                              <img src="{{ $heroImage }}" alt="{{ $business->name }}" class="w-full h-full object-cover">
                          @else
                              <div class="w-full h-full flex flex-col items-center justify-center p-6 text-center bg-gradient-to-br from-zinc-900 to-zinc-950 text-white">
                                  <div class="w-16 h-16 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center mb-4 border border-white/20">
                                      <span class="font-heading font-bold text-2xl text-white">{{ strtoupper(substr($business->name, 0, 2)) }}</span>
                                  </div>
                                  <h3 class="font-heading font-bold text-lg text-white mb-1">{{ $business->name }}</h3>
                                  <p class="text-xs text-zinc-300 max-w-xs">{{ $heroSubheadline }}</p>
                              </div>
                          @endif

                          {{-- Floating Status Badge --}}
                          <div class="absolute bottom-4 left-4 right-4 p-3 rounded-xl bg-white/95 dark:bg-zinc-900/95 backdrop-blur-md border border-zinc-200/80 dark:border-zinc-800 shadow-lg flex items-center justify-between">
                              <div class="flex items-center gap-2.5">
                                  <div class="w-8 h-8 rounded-lg theme-badge flex items-center justify-center">
                                      <i data-lucide="shield-check" class="w-4 h-4"></i>
                                  </div>
                                  <div>
                                      <span class="text-[11px] font-bold text-zinc-900 dark:text-white block">{{ __('storefront.hero.active_status') }}</span>
                                      <span class="text-[10px] text-zinc-500">{{ __('storefront.hero.verified_official') }}</span>
                                  </div>
                              </div>
                              <span class="text-xs font-bold text-theme-primary">{{ __('storefront.features.official_store') }}</span>
                          </div>
                      </div>
                  </div>
              </div>

          </div>
      </div>
  </section>
  ```

#### File 2: `features_banner.blade.php`
- **Direct Observations:**
  - Icon boxes have hardcoded indigo utility classes: `bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-100 dark:border-indigo-800/60 text-indigo-600 dark:text-indigo-400`.
  - 4 static cards with hardcoded texts:
    - `"100% Original"`, `"Garansi keaslian setiap produk"`
    - `{{ __('storefront.transaksi_aman') }}`, `"Verifikasi pesanan otomatis"`
    - `{{ __('storefront.pengiriman_cepat') }}`, `"Resi terbit hari kerja sama"`
    - `"Garansi Retur"`, `"Mudah tukar jika cacat pabrik"`
- **Proposed Refactoring:**
  ```blade
  {{-- online_store/features_banner.blade.php --}}
  @php
      $pillars = $landingPage->getUniversalValues();
  @endphp

  <section class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-5 sm:p-6 rounded-2xl bg-zinc-50 dark:bg-zinc-900/60 border border-zinc-200/80 dark:border-zinc-800 transition-colors">
          @foreach ($pillars as $pillar)
              <div class="flex items-center gap-3.5">
                  <div class="w-11 h-11 rounded-xl theme-badge flex items-center justify-center shrink-0">
                      <i data-lucide="{{ $pillar['icon'] }}" class="w-5 h-5"></i>
                  </div>
                  <div>
                      <h4 class="font-heading font-bold text-xs sm:text-sm text-zinc-900 dark:text-white">
                          {{ $pillar['title'] }}
                      </h4>
                      <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5 leading-relaxed">
                          {{ $pillar['desc'] }}
                      </p>
                  </div>
              </div>
          @endforeach
      </div>
  </section>
  ```

#### File 3: `category_chips.blade.php`
- **Direct Observations:**
  - Line 10: Subtitle has hardcoded text `"Pilih departemen produk yang Anda cari"`.
  - Line 14: Uses `'storefront.semua_departemen'` instead of standard `'storefront.view_all'`.
  - Line 13: Hardcoded `text-indigo-600 dark:text-indigo-400`.
  - Line 22: Hardcoded `hover:border-indigo-500 hover:text-indigo-600`.
  - Missing "Semua Produk" chip with active check.
  - Missing active state when `$cat->id == request('category')`.
- **Proposed Refactoring:**
  ```blade
  {{-- online_store/category_chips.blade.php --}}
  @if ($productCategories->isNotEmpty() && $landingPage->isPageActive('catalog'))
      <section class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-4">
          <div class="flex items-center justify-between gap-4 mb-4">
              <div>
                  <h2 class="font-heading font-bold text-lg sm:text-xl text-zinc-950 dark:text-white">
                      {{ $landingPage->services_title ?: __('storefront.kategori_pilihan') }}
                  </h2>
                  <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                      {{ __('storefront.pilih_kategori_filter') }}
                  </p>
              </div>
              <a href="{{ url('/' . $business->slug . '/katalog') }}"
                  class="text-xs font-semibold text-theme-primary hover:underline flex items-center gap-1">
                  <span>{{ __('storefront.view_all') }}</span>
                  <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
              </a>
          </div>

          <div class="flex gap-2.5 overflow-x-auto pb-2 scrollbar-none">
              {{-- Semua Produk Chip --}}
              <a href="{{ url('/' . $business->slug . '/katalog') }}"
                  class="flex-shrink-0 inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold transition min-h-[44px] {{ !request()->filled('category') ? 'theme-btn-primary shadow-xs' : 'border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 text-zinc-800 dark:text-zinc-200 hover:border-theme-primary shadow-2xs' }}">
                  <i data-lucide="layout-grid" class="w-3.5 h-3.5"></i>
                  <span>{{ __('storefront.semua_produk') }}</span>
              </a>

              @foreach ($productCategories->take(12) as $cat)
                  @php $isActive = request('category') == $cat->id; @endphp
                  <a href="{{ url('/' . $business->slug . '/katalog?category=' . $cat->id) }}"
                      class="flex-shrink-0 inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold transition min-h-[44px] {{ $isActive ? 'theme-btn-primary shadow-xs' : 'border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 text-zinc-800 dark:text-zinc-200 hover:border-theme-primary shadow-2xs' }}">
                      <i data-lucide="tag" class="w-3.5 h-3.5 text-zinc-400"></i>
                      <span>{{ $cat->name }}</span>
                  </a>
              @endforeach
          </div>
      </section>
  @endif
  ```

---

### 2.4 Theme 4: `business_corporate`
- **Path:** `resources/views/public/storefront/themes/business_corporate/`
- **Archetype:** `institutional_trust` (Institutional & Trust)
- **Palette:** Primary `#2563EB`, Accent `#0F172A`, Bg `#F8FAFC`, Card `#FFFFFF`, Badge `#2563EB15` / `#2563EB`, Radius `12px`.
- **Fonts:** Heading: Plus Jakarta Sans, Body: Inter.

#### File 1: `hero.blade.php`
- **Direct Observations:**
  - Line 11-13: Eyebrow has hardcoded blue utilities: `bg-blue-600/10 text-blue-700 dark:text-blue-400 border border-blue-600/20` and text `{{ $activeTheme['industry'] ?? 'Corporate Enterprise & B2B Solutions' }}`.
  - Line 17: Headline hardcodes `{{ $landingPage->headline ?: $business->name . ' - Mitra Solusi & Suplai Bisnis Terpercaya' }}`.
  - Line 21: Subheadline hardcodes `'Menyediakan produk dan layanan berstandar industri dengan jaminan mutu, kontrak kerja transparan, dan keandalan operasional jangka panjang.'`.
  - Line 28: Primary CTA has hardcoded blue background `bg-blue-600 hover:bg-blue-700` and text `'Katalog Solusi & Produk'`.
  - Line 37-38: Secondary CTA has hardcoded blue icon `text-blue-600` and text `'Hubungi Account Representative'`.
  - Lines 44-57: Hardcoded B2B trust metrics strip:
    - `"100%"`, `"Entitas Legal & Resmi"`
    - `"SLA 99.9%"`, `"Komitmen Layanan"` (with hardcoded `text-blue-600`)
    - `"B2B & Retail"`, `"Faktur Pajak Tersedia"`
  - Line 64: Hardcoded Unsplash office building image `'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=1000&auto=format&fit=crop&q=80'`.
  - Lines 71-77: Floating card has hardcoded strings: `"Operasional Aktif &amp; Terverifikasi"`, `"COOCA Verified"` and hardcoded `bg-blue-600` pulse dot.
- **Proposed Refactoring:**
  ```blade
  {{-- business_corporate/hero.blade.php --}}
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

  <section class="relative overflow-hidden pt-8 pb-16 sm:pt-14 sm:pb-20 border-b border-black/[0.06] dark:border-white/[0.06] bg-slate-50/60 dark:bg-slate-950/40">
      <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
          <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">

              {{-- Col 1: Corporate Copy --}}
              <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                  <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-md text-xs font-semibold tracking-wide uppercase theme-badge border border-black/5 dark:border-white/10">
                      <span class="w-2 h-2 rounded-full bg-current animate-pulse"></span>
                      <span>{{ $heroEyebrow }}</span>
                  </div>

                  <h1 class="font-heading font-extrabold text-3xl sm:text-5xl lg:text-6xl text-slate-900 dark:text-white tracking-tight leading-[1.12]">
                      {{ $heroHeadline }}
                  </h1>

                  <p class="text-base sm:text-lg text-slate-600 dark:text-slate-300 max-w-2xl mx-auto lg:mx-0 leading-relaxed font-normal">
                      {{ $heroSubheadline }}
                  </p>

                  {{-- Action Row --}}
                  <div class="flex flex-wrap items-center justify-center lg:justify-start gap-3 sm:gap-4 pt-2">
                      @if ($landingPage->isPageActive('catalog'))
                          <a href="{{ $primaryCtaUrl }}"
                              class="px-6 py-3.5 text-sm sm:text-base font-semibold theme-btn-primary shadow-sm flex items-center gap-2 min-h-[48px]">
                              <span>{{ $primaryCtaText }}</span>
                              <i data-lucide="arrow-right" class="w-4 h-4"></i>
                          </a>
                      @endif

                      @if ($hasWhatsapp)
                          <a href="{{ $secondaryCtaUrl }}" target="_blank" rel="noopener"
                              class="px-6 py-3.5 text-sm sm:text-base font-semibold rounded-lg bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 shadow-xs transition flex items-center gap-2 min-h-[48px]">
                              <i data-lucide="message-circle" class="w-4 h-4 text-emerald-500"></i>
                              <span>{{ $secondaryCtaText }}</span>
                          </a>
                      @endif
                  </div>

                  {{-- Dynamic B2B Trust Metrics Strip (Preserving Institutional Archetype) --}}
                  <div class="pt-6 border-t border-slate-200 dark:border-slate-800 grid grid-cols-3 gap-4 text-left">
                      <div>
                          <div class="font-heading font-extrabold text-sm sm:text-base text-slate-900 dark:text-white flex items-center gap-1.5">
                              <i data-lucide="{{ $badgeIcon1 }}" class="w-4 h-4 text-theme-primary"></i>
                              <span>{{ $badgePill1 }}</span>
                          </div>
                          <div class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 mt-1">{{ __('storefront.hero.verified_official') }}</div>
                      </div>
                      <div>
                          <div class="font-heading font-extrabold text-sm sm:text-base text-theme-primary flex items-center gap-1.5">
                              <i data-lucide="{{ $badgeIcon2 }}" class="w-4 h-4"></i>
                              <span>{{ $badgePill2 }}</span>
                          </div>
                          <div class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 mt-1">{{ __('storefront.hero.active_status') }}</div>
                      </div>
                      <div>
                          <div class="font-heading font-extrabold text-sm sm:text-base text-slate-900 dark:text-white flex items-center gap-1.5">
                              <i data-lucide="{{ $badgeIcon3 }}" class="w-4 h-4 text-theme-primary"></i>
                              <span>{{ $badgePill3 }}</span>
                          </div>
                          <div class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 mt-1">{{ __('storefront.badges.quality_guaranteed') }}</div>
                      </div>
                  </div>
              </div>

              {{-- Col 2: Corporate Visual --}}
              <div class="lg:col-span-5">
                  <div class="relative mx-auto max-w-md lg:max-w-none">
                      <div class="relative rounded-xl overflow-hidden shadow-xl border border-slate-200 dark:border-slate-800 aspect-[4/3] sm:aspect-square bg-slate-100 dark:bg-slate-900">
                          @if ($heroImage)
                              <img src="{{ $heroImage }}" alt="{{ $business->name }}" class="w-full h-full object-cover">
                              <div class="absolute inset-0 bg-gradient-to-t from-slate-950/70 via-slate-950/20 to-transparent"></div>

                              {{-- Corporate Verification Badge on Image --}}
                              <div class="absolute bottom-4 left-4 right-4 p-4 rounded-lg bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border border-slate-200/50 dark:border-slate-700/50 text-xs shadow-md flex items-center justify-between">
                                  <div class="flex items-center gap-2.5">
                                      <div class="w-2.5 h-2.5 rounded-full bg-theme-primary animate-pulse"></div>
                                      <span class="font-semibold text-slate-900 dark:text-white">{{ __('storefront.hero.active_status') }}</span>
                                  </div>
                                  <span class="text-slate-500 dark:text-slate-400 font-mono text-[11px]">{{ __('storefront.hero.verified_official') }}</span>
                              </div>
                          @else
                              {{-- Brand Bento Graphic Showcase (Universal Fallback) --}}
                              <div class="w-full h-full p-8 bg-gradient-to-br from-slate-900 to-slate-800 text-white flex flex-col justify-between">
                                  <div class="flex items-center justify-between">
                                      <span class="w-14 h-14 rounded-xl flex items-center justify-center font-bold text-2xl text-white shadow-md" style="background-color: var(--theme-primary);">
                                          {{ strtoupper(substr($business->name, 0, 2)) }}
                                      </span>
                                      <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider theme-badge">
                                          {{ __('storefront.hero.active_status') }}
                                      </span>
                                  </div>
                                  <div class="space-y-2 mt-auto">
                                      <h3 class="font-heading font-bold text-2xl text-white">{{ $business->name }}</h3>
                                      <p class="text-xs text-slate-300 leading-relaxed">{{ $heroSubheadline }}</p>
                                  </div>
                              </div>
                          @endif
                      </div>
                  </div>
              </div>

          </div>
      </div>
  </section>
  ```

#### File 2: `features_banner.blade.php`
- **Direct Observations:**
  - Icon boxes have hardcoded blue utility classes: `bg-blue-600/10 text-blue-600 dark:text-blue-400`.
  - 4 static cards with hardcoded B2B copy:
    - `{{ __('storefront.sla_garansi_resmi') }}`, `"Jaminan mutu kerja dan dukungan teknis berstandar SLA."`
    - `{{ __('storefront.faktur_pajak_ppn') }}`, `"Kelengkapan dokumen perpajakan resmi untuk pembukuan entitas."`
    - `{{ __('storefront.quotation_po_resmi') }}`, `"Skema pembayaran bertahap dan termin sesuai kesepakatan PO."`
    - `"Dedicated PIC"`, `"Pendampingan langsung oleh Account Manager profesional."`
- **Proposed Refactoring:**
  ```blade
  {{-- business_corporate/features_banner.blade.php --}}
  @php
      $pillars = $landingPage->getUniversalValues();
  @endphp

  <section class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6 p-6 sm:p-8 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
          @foreach ($pillars as $pillar)
              <div class="flex items-start gap-3">
                  <div class="w-10 h-10 rounded-lg theme-badge flex items-center justify-center shrink-0">
                      <i data-lucide="{{ $pillar['icon'] }}" class="w-5 h-5"></i>
                  </div>
                  <div>
                      <h4 class="font-heading font-bold text-xs sm:text-sm text-slate-900 dark:text-white leading-snug">
                          {{ $pillar['title'] }}
                      </h4>
                      <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 mt-0.5 line-clamp-2 leading-relaxed">
                          {{ $pillar['desc'] }}
                      </p>
                  </div>
              </div>
          @endforeach
      </div>
  </section>
  ```

#### File 3: `category_chips.blade.php`
- **Direct Observations:**
  - Line 9: `{{ __('storefront.divisi_produk_solusi') }}` without fallback to `$landingPage->services_title ?: __('storefront.kategori_pilihan')`.
  - Line 10: Subtitle has hardcoded text `"Pilih kategori untuk memfilter spesifikasi pengadaan"`.
  - Line 14: `"Seluruh Katalog"` is hardcoded Indonesian text instead of `__('storefront.view_all')`.
  - Line 13 & 22: Hardcoded blue colors `text-blue-600 dark:text-blue-400` and `hover:border-blue-500 hover:text-blue-600`.
  - Missing "Semua Produk" chip.
  - Missing active category check.
  - Touch target is `py-2` without `min-h-[44px]`.
- **Proposed Refactoring:**
  ```blade
  {{-- business_corporate/category_chips.blade.php --}}
  @if ($productCategories->isNotEmpty() && $landingPage->isPageActive('catalog'))
      <section class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-4">
          <div class="flex items-center justify-between gap-4 mb-4 pb-2 border-b border-slate-200 dark:border-slate-800">
              <div>
                  <h2 class="font-heading font-bold text-lg sm:text-xl text-slate-900 dark:text-white">
                      {{ $landingPage->services_title ?: __('storefront.kategori_pilihan') }}
                  </h2>
                  <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                      {{ __('storefront.pilih_kategori_filter') }}
                  </p>
              </div>
              <a href="{{ url('/' . $business->slug . '/katalog') }}"
                  class="text-xs font-semibold text-theme-primary hover:underline flex items-center gap-1">
                  <span>{{ __('storefront.view_all') }}</span>
                  <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
              </a>
          </div>

          <div class="flex gap-2 overflow-x-auto pb-2 scrollbar-none">
              {{-- Semua Produk Tab --}}
              <a href="{{ url('/' . $business->slug . '/katalog') }}"
                  class="flex-shrink-0 inline-flex items-center gap-2 px-3.5 py-2.5 rounded-lg text-xs font-semibold transition min-h-[44px] {{ !request()->filled('category') ? 'theme-btn-primary shadow-xs' : 'border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 hover:border-theme-primary shadow-2xs' }}">
                  <i data-lucide="layout-grid" class="w-3.5 h-3.5"></i>
                  <span>{{ __('storefront.semua_produk') }}</span>
              </a>

              @foreach ($productCategories->take(12) as $cat)
                  @php $isActive = request('category') == $cat->id; @endphp
                  <a href="{{ url('/' . $business->slug . '/katalog?category=' . $cat->id) }}"
                      class="flex-shrink-0 inline-flex items-center gap-2 px-3.5 py-2.5 rounded-lg text-xs font-semibold transition min-h-[44px] {{ $isActive ? 'theme-btn-primary shadow-xs' : 'border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 hover:border-theme-primary shadow-2xs' }}">
                      <i data-lucide="folder" class="w-3.5 h-3.5 text-slate-400"></i>
                      <span>{{ $cat->name }}</span>
                  </a>
              @endforeach
          </div>
      </section>
  @endif
  ```

---

## 3. Comparative Matrix: Changes Required across 12 Files

| Theme | File | Hardcoded Sector Text | Static Unsplash Photo | Hardcoded Hex / Palette Utility | Proposed Action |
|---|---|---|---|---|---|
| `universal_storefront` | `hero.blade.php` | Line 13, 21, 28, 37, 63, 65 | Line 53 (`photo-1555396273...`) | `text-emerald-500` (acceptable for WA), missing `theme-btn-secondary` | Apply Contract §3.1: `@php` hierarchy, 3 dynamic badges, Bento brand fallback card |
| `universal_storefront` | `features_banner.blade.php` | Lines 13, 23, 33, 42, 43 | None | None | Apply Contract §3.2: Iterate `$landingPage->getUniversalValues()` |
| `universal_storefront` | `category_chips.blade.php` | Line 11 | None | Minor link styling | Apply Contract §3.3: Add "Semua Produk", active state, `pilih_kategori_filter` |
| `modern_umkm` | `hero.blade.php` | Lines 13, 17, 21, 28, 37, 46, 50, 68, 70 | Line 59 (`photo-1555396273...`) | `bg-emerald-50`, `bg-emerald-600`, `text-emerald-700`, `border-emerald-300` | Apply Contract §3.1: Replace emerald classes with `theme-btn-primary`, 3 dynamic badges, Bento fallback |
| `modern_umkm` | `features_banner.blade.php` | Lines 14, 17, 27, 29, 39, 41, 52, 55 | None | `border-emerald-900/10`, `bg-emerald-100 text-emerald-700` | Apply Contract §3.2: Iterate `$landingPage->getUniversalValues()`, use `theme-badge` |
| `modern_umkm` | `category_chips.blade.php` | Lines 10, missing subtitle | None | `bg-emerald-500`, `text-emerald-600`, `border-emerald-100` | Apply Contract §3.3: Add "Semua Produk", active state, dynamic theme tokens |
| `online_store` | `hero.blade.php` | Lines 18, 20, 25, 29, 38, 46, 54-64, 81, 92-96 | None (uses graphic fallback with hardcoded retail text) | `text-indigo-600`, `bg-zinc-900`, `bg-emerald-500`, `bg-indigo-50` | Apply Contract §3.1: `@php` hierarchy, 3 dynamic badges, clean brand fallback, `theme-btn-primary` |
| `online_store` | `features_banner.blade.php` | Lines 14, 15, 25, 26, 36, 37, 47, 48 | None | `bg-indigo-50 text-indigo-600 border-indigo-100` | Apply Contract §3.2: Iterate `$landingPage->getUniversalValues()`, use `theme-badge` |
| `online_store` | `category_chips.blade.php` | Lines 10, 14 | None | `text-indigo-600`, `hover:border-indigo-500` | Apply Contract §3.3: Add "Semua Produk", active state, dynamic theme tokens |
| `business_corporate` | `hero.blade.php` | Lines 13, 17, 21, 29, 38, 47-56, 74, 76 | Line 64 (`photo-1486406146...`) | `bg-blue-600/10 text-blue-700`, `bg-blue-600`, `text-blue-600` | Apply Contract §3.1: Dynamic metrics strip from `$landingPage->getUniversalValues()`, Bento brand fallback |
| `business_corporate` | `features_banner.blade.php` | Lines 14, 16, 27, 29, 40, 42, 54, 57 | None | `bg-blue-600/10 text-blue-600` on 4 cards | Apply Contract §3.2: Iterate `$landingPage->getUniversalValues()`, use `theme-badge` |
| `business_corporate` | `category_chips.blade.php` | Lines 9, 10, 14 | None | `text-blue-600`, `hover:border-blue-500` | Apply Contract §3.3: Add "Semua Produk", active state, dynamic theme tokens |
