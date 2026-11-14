# Handoff Report  Explorer M2_2: Commercial Landings & Discovery

## 1. Observation

Direct investigation was conducted across all 4 scoped files:
1. `resources/views/public/landing_commercial/kasir_android.blade.php`
2. `resources/views/public/landing_commercial/kasir_gratis.blade.php`
3. `resources/views/public/landing_commercial/kasir_offline.blade.php`
4. `resources/views/public/discovery/index.blade.php`

### A. Heading Hierarchy Skips
- **`kasir_android.blade.php`**:
  - Line 118: `<h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-[2.85rem] xl:text-[3.25rem] font-extrabold text-white tracking-tight leading-[1.14] text-balance">{{ \App\Services\Seo\SeoMetadataService::getH1(__('public_commercial.android_title')) }}</h1>`
  - Line 170: In the right column Specs Card: `<h3 class="text-2xl font-bold text-white mb-2">{{ __('public_commercial.android_specs_title') }}</h3>`
    *Verbatim Observation*: There is no intervening `<h2>` between the Hero H1 (line 118) and the Specs Card H3 (line 170). The first `<h2>` only appears in Section 2 at line 220 (`android_printer_title`). This is a direct **H1 -> H3 heading skip**.
  - Line 266: Section 3 heading is `<h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-snug">{{ __('public_commercial.android_touch_title') }}</h2>`
  - Lines 279, 289, 299, 309: Feature cards within Section 3 use:
    - Line 279: `<h4 class="font-bold text-slate-900 dark:text-white">{{ __('public_commercial.android_touch_1_title') }}</h4>`
    - Line 289: `<h4 class="font-bold text-slate-900 dark:text-white">{{ __('public_commercial.android_touch_2_title') }}</h4>`
    - Line 299: `<h4 class="font-bold text-slate-900 dark:text-white">{{ __('public_commercial.android_touch_3_title') }}</h4>`
    - Line 309: `<h4 class="font-bold text-slate-900 dark:text-white">{{ __('public_commercial.android_touch_4_title') }}</h4>`
    *Verbatim Observation*: Section 3 features use `<h4>` directly beneath `<h2>`, skipping `<h3>` entirely (**H2 -> H4 skip**).

- **`kasir_offline.blade.php`**:
  - Line 118: `<h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-[2.85rem] xl:text-[3.25rem] font-extrabold text-white tracking-tight leading-[1.14] text-balance">{{ \App\Services\Seo\SeoMetadataService::getH1(__('public_commercial.offline_title')) }}</h1>`
  - Line 170: In the right column Architecture Sync Bento: `<h3 class="text-2xl font-bold text-white">{{ __('public_commercial.offline_arch_title') }}</h3>`
    *Verbatim Observation*: The Hero H1 (line 118) is immediately followed by an H3 (line 170). The next heading is an `<h2>` at line 208 (`offline_transp_title`). This is a direct **H1 -> H3 heading skip**.

- **`kasir_gratis.blade.php`**:
  - Line 119: `<h1 class="...">{{ \App\Services\Seo\SeoMetadataService::getH1(__('public_commercial.free_title')) }}</h1>`
  - Lines 166–210: Hero right column Package Highlight Card uses `<div>` and `<span class="text-4xl sm:text-5xl font-black">` for pricing and `<ul>` for feature limits. No heading tags are used.
  - Section 2 (line 220): `<h2>` with card `<h3>` (lines 232, 242, 252).
  - Section 3 (line 265): `<h2>` with comparison table.
  - Section 4 (line 345): `<h2>` with step `<h3>` (lines 355, 363, 371).
  - Section 5 (line 385): `<h2>` with FAQ `<h3>` (lines 391, 398, 405, 412, 419).
  - Section 6 (line 431): `<h2>`.
  *Verbatim Observation*: `kasir_gratis.blade.php` exhibits strict, clean semantic heading hierarchy (H1 -> H2 -> H3). No heading skips are present.

- **`discovery/index.blade.php`**:
  - Line 66: `<h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-[2.65rem] xl:text-[3rem] font-extrabold tracking-tight text-white leading-[1.15] text-balance break-words max-w-[22rem] sm:max-w-xl lg:max-w-none mx-auto lg:mx-0">{{ __('public_discovery.disc_title') }}</h1>`
  - Line 252: Empty state heading: `<h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ __('public_discovery.disc_empty_title') }}</h3>`
    *Verbatim Observation*: When search returns zero businesses, the page skips directly from H1 (line 66) to H3 (line 252) with zero `<h2>` elements present (**H1 -> H3 skip on empty state**).
  - Line 385: Merchant onboarding banner heading: `<h3 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">{{ __('public_discovery.disc_banner_title') }}</h3>`
    *Verbatim Observation*: The final conversion banner is a top-level landmark section, but uses `<h3>` instead of `<h2>` (inconsistent with final CTA sections on commercial landings).

### B. Media Dimensions & CLS Audit
- **`discovery/index.blade.php`**:
  - Line 278:
    ```blade
    @if ($store->logo_path)
        <img src="{{ Storage::url($store->logo_path) }}" alt="{{ $store->name }}"
            class="w-12 h-12 rounded-[14px] object-cover border border-black/[0.06] dark:border-white/[0.08] shrink-0">
    @else
    ```
    *Verbatim Observation*: The `<img>` tag specifies Tailwind classes `w-12 h-12` (48x48px) but omits explicit HTML attributes `width="48"`, `height="48"`, `loading="lazy"`, and `decoding="async"`. Browsers cannot calculate aspect ratios before CSS parses, producing Cumulative Layout Shift (CLS) when dynamic store cards load.
- **`kasir_android.blade.php`, `kasir_gratis.blade.php`, `kasir_offline.blade.php`**:
  - *Verbatim Observation*: Across all 3 commercial landing files, there are **0 bitmap `<img>` tags**, 0 PNG/JPEG screenshots, and 0 external badge images. All device mockups, specs panels, and app store CTA buttons (`/download/apk`) are built with inline Tailwind CSS and Lucide SVG icons (`<i data-lucide="...">`). CLS risk from images on these three pages is 0.

### C. Touch Targets & Mobile Responsiveness (320px–414px)
- **`discovery/index.blade.php`**:
  - Lines 209 & 214: Category filter pills use `class="h-10 px-4 rounded-[12px] text-xs font-semibold ..."` -> Height is 40px, failing the WCAG 2.5.5 / 2.5.8 minimum touch target requirement of 44x44px.
  - Line 238: Service capabilities filters use `class="h-8 px-3 rounded-[8px] border ..."` -> Height is 32px, severely failing the 44px minimum tap target.
  - Line 363: Store Card Visit button ("Kunjungi Toko") uses `class="h-9 px-4 rounded-[10px] ..."` -> Height is 36px, failing 44px minimum tap target on touch screens.
  - Line 356: Card Footer (`flex items-center justify-between gap-3`): On 320px viewport (288px container minus 48px padding = 240px available width), English text ("12 Active Products" + "Visit Store") can induce button crowding. Adding `shrink-0` to the visit button guarantees layout stability.

### D. Theme Contrast & Accessibility
- **`discovery/index.blade.php`**:
  - Line 223: `<span class="text-slate-400 dark:text-slate-500 font-semibold uppercase tracking-wider text-[11px] shrink-0 mr-1">`
    *Verbatim Observation*: In Light mode, `#94A3B8` (slate-400) on `#F2F2F7` background yields a **2.43:1** contrast ratio. In Dark mode, `#64748B` (slate-500) on `#000000` background yields a **3.9:1** contrast ratio. Both fail the WCAG AA minimum 4.5:1 ratio for 11px uppercase text.
- **`kasir_gratis.blade.php`**:
  - Line 322: `<td class="p-4 sm:p-5 bg-emerald-500/5 text-slate-400">{{ __('public_commercial.free_comp_val_roles_free', ['user' => $tierLimits['free']['user'] ?? 1]) }}</td>`
    *Verbatim Observation*: In Light mode, `text-slate-400` on near-white background yields a **2.6:1** contrast ratio, failing WCAG AA (minimum 4.5:1). It also lacks a dark mode variant.

### E. SEO, Metadata & Structured Data
- **`discovery/index.blade.php`**:
  - Line 66 uses `{{ __('public_discovery.disc_title') }}` without wrapping with `\App\Services\Seo\SeoMetadataService::getH1()`. As a result, the centralized H1 defined in `config/seo.php` (`'/jelajah' => ['h1' => 'Jelajahi Direktori Toko & Produk UMKM Indonesia']`) is bypassed.
  - Lines 11–26: Declares Schema.org `CollectionPage` in `@push('seo')`, but omits `BreadcrumbList` structured data despite having a visual breadcrumb at lines 50–54.
- **`tests/Feature/PublicBusinessDiscoveryTest.php` Test Discrepancy**:
  - Lines 172, 186, 197 assert: `->assertSee('Toko Tidak Ditemukan')`.
  - `lang/id/public_discovery.php` line 47 defines `'disc_empty_title' => 'Toko Belum Ditemukan'`.
  - View `discovery/index.blade.php` line 252 outputs `{{ __('public_discovery.disc_empty_title') }}`.
  - This discrepancy triggers 3 test assertion failures in the test suite.

---

## 2. Logic Chain

1. **Heading Hierarchy (Observation A)**:
   - Screen readers and search engine crawlers require a logical, step-by-step document outline (`H1 -> H2 -> H3 -> H4`) to interpret page content semantics.
   - In `kasir_android.blade.php` line 170 and `kasir_offline.blade.php` line 170, the hero right column contains a card with an `<h3>` heading immediately below `<h1>`, violating WCAG 1.3.1 (Info and Relationships) and HTML5 outline specifications.
   - In `kasir_android.blade.php` line 266, Section 3 has an `<h2>` heading, but its sub-items at lines 279, 289, 299, 309 jump directly to `<h4>`, bypassing `<h3>`.
   - In `discovery/index.blade.php`, when empty search results are rendered, line 252 outputs `<h3>` without any `<h2>` on the page.
   - Upgrading hero cards and empty state headings to `<h2>` and Section 3 cards to `<h3>` establishes an unbroken semantic hierarchy:
     `H1 (Hero Title) -> H2 (Section Title / Hero Secondary Landmark) -> H3 (Sub-items / FAQs / Features)`.

2. **Media Dimensions & CLS (Observation B)**:
   - When a browser renders an `<img>` tag without explicit `width` and `height` attributes, it cannot compute the aspect ratio before downloading the asset header or evaluating CSS.
   - When store logos in `discovery/index.blade.php` line 278 load asynchronously from storage, the card layout expands dynamically, causing Cumulative Layout Shift (CLS).
   - Adding `width="48" height="48" loading="lazy" decoding="async"` reserves exact 48x48px layout boxes prior to rendering, preventing CLS while offloading image decoding from the main thread.

3. **Touch Targets (Observation C)**:
   - On viewports 320px–414px, fingers require touch target dimensions of at least 44x44 CSS pixels (WCAG 2.5.5 / 2.5.8).
   - Category filter pills (40px, `h-10`), capability pills (32px, `h-8`), and store visit buttons (36px, `h-9`) fail these criteria.
   - Increasing container min-height to `min-h-[44px]` eliminates missed taps and accidental adjacent activations on mobile devices.

4. **Theme Contrast (Observation D)**:
   - `text-slate-400` on light backgrounds (contrast 2.43:1 - 2.6:1) and `dark:text-slate-500` on `#000000` (contrast 3.9:1) fail the WCAG 1.4.3 AA minimum threshold of 4.5:1.
   - Replacing them with `text-slate-600 dark:text-slate-400` restores contrast ratios to >= 5.7:1 in light mode and >= 6.5:1 in dark mode.

5. **SEO & Structured Data (Observation E)**:
   - Integrating `\App\Services\Seo\SeoMetadataService::getH1()` into `discovery/index.blade.php` line 66 aligns discovery with `config/seo.php` architecture.
   - Adding `BreadcrumbList` JSON-LD schema into `discovery/index.blade.php` enables Google Rich Snippets for breadcrumbs, matching the other commercial landing pages.
   - Aligning `lang/id/public_discovery.php` line 47 `'disc_empty_title'` with `'Toko Tidak Ditemukan'` eliminates test failures in `PublicBusinessDiscoveryTest.php`.

---

## 3. Caveats

1. **Storefront Business Landing Scope**: `tests/Feature/PublicBusinessDiscoveryTest.php` contains tests for `business_landing.blade.php` (such as `test_business_landing_supports_light_and_dark_mode_and_live_catalog_search`). That file is assigned to Explorer M2_1; our scope covers only `discovery/index.blade.php` and the 3 commercial landing views.
2. **Dynamic Store Content**: In `discovery/index.blade.php`, merchant logos are user-uploaded (`$store->logo_path`). While `width="48" height="48"` guarantees a 1:1 aspect box, `object-cover` prevents non-square store logos from stretching.
3. **No External App Store Images**: Commercial landing pages purposely use CSS/Lucide DOM buttons (`/download/apk`) instead of Google Play raster badge images. This is an intentional design choice that prevents external image requests and CLS.

---

## 4. Conclusion & Actionable Worker Specifications

### File 1: `resources/views/public/landing_commercial/kasir_android.blade.php`
- **Fix 1.1 (Line 170)**: Upgrade Hero Specs Card heading:
  ```blade
  {{-- Before --}}
  <h3 class="text-2xl font-bold text-white mb-2">{{ __('public_commercial.android_specs_title') }}</h3>
  {{-- After --}}
  <h2 class="text-xl sm:text-2xl font-bold text-white mb-2">{{ __('public_commercial.android_specs_title') }}</h2>
  ```
- **Fix 1.2 (Lines 279, 289, 299, 309)**: Upgrade Section 3 touch feature headings from `<h4>` to `<h3>`:
  ```blade
  {{-- Before --}}
  <h4 class="font-bold text-slate-900 dark:text-white">{{ __('public_commercial.android_touch_1_title') }}</h4>
  ...
  <h4 class="font-bold text-slate-900 dark:text-white">{{ __('public_commercial.android_touch_4_title') }}</h4>
  {{-- After --}}
  <h3 class="font-bold text-slate-900 dark:text-white">{{ __('public_commercial.android_touch_1_title') }}</h3>
  ...
  <h3 class="font-bold text-slate-900 dark:text-white">{{ __('public_commercial.android_touch_4_title') }}</h3>
  ```

### File 2: `resources/views/public/landing_commercial/kasir_gratis.blade.php`
- **Fix 2.1 (Line 322)**: Fix contrast in comparison table:
  ```blade
  {{-- Before --}}
  <td class="p-4 sm:p-5 bg-emerald-500/5 text-slate-400">{{ __('public_commercial.free_comp_val_roles_free', ['user' => $tierLimits['free']['user'] ?? 1]) }}</td>
  {{-- After --}}
  <td class="p-4 sm:p-5 bg-emerald-500/5 text-slate-600 dark:text-slate-400">{{ __('public_commercial.free_comp_val_roles_free', ['user' => $tierLimits['free']['user'] ?? 1]) }}</td>
  ```

### File 3: `resources/views/public/landing_commercial/kasir_offline.blade.php`
- **Fix 3.1 (Line 170)**: Upgrade Hero Architecture Sync Card heading:
  ```blade
  {{-- Before --}}
  <h3 class="text-2xl font-bold text-white">{{ __('public_commercial.offline_arch_title') }}</h3>
  {{-- After --}}
  <h2 class="text-xl sm:text-2xl font-bold text-white">{{ __('public_commercial.offline_arch_title') }}</h2>
  ```

### File 4: `resources/views/public/discovery/index.blade.php`
- **Fix 4.1 (Line 66)**: Connect H1 to SEO service:
  ```blade
  {{-- Before --}}
  <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-[2.65rem] xl:text-[3rem] font-extrabold tracking-tight text-white leading-[1.15] text-balance break-words max-w-[22rem] sm:max-w-xl lg:max-w-none mx-auto lg:mx-0">
      {{ __('public_discovery.disc_title') }}
  </h1>
  {{-- After --}}
  <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-[2.65rem] xl:text-[3rem] font-extrabold tracking-tight text-white leading-[1.15] text-balance break-words max-w-[22rem] sm:max-w-xl lg:max-w-none mx-auto lg:mx-0">
      {{ \App\Services\Seo\SeoMetadataService::getH1(__('public_discovery.disc_title')) }}
  </h1>
  ```
- **Fix 4.2 (Line 252)**: Fix empty state heading:
  ```blade
  {{-- Before --}}
  <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ __('public_discovery.disc_empty_title') }}</h3>
  {{-- After --}}
  <h2 class="text-lg font-bold text-slate-900 dark:text-white">{{ __('public_discovery.disc_empty_title') }}</h2>
  ```
- **Fix 4.3 (Line 385)**: Fix merchant onboarding banner heading:
  ```blade
  {{-- Before --}}
  <h3 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
  {{-- After --}}
  <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
  ```
- **Fix 4.4 (Line 278)**: Explicit image dimensions and lazy decoding:
  ```blade
  {{-- Before --}}
  <img src="{{ Storage::url($store->logo_path) }}" alt="{{ $store->name }}"
      class="w-12 h-12 rounded-[14px] object-cover border border-black/[0.06] dark:border-white/[0.08] shrink-0">
  {{-- After --}}
  <img src="{{ Storage::url($store->logo_path) }}" alt="{{ $store->name }}" width="48" height="48" loading="lazy" decoding="async"
      class="w-12 h-12 rounded-[14px] object-cover border border-black/[0.06] dark:border-white/[0.08] shrink-0">
  ```
- **Fix 4.5 (Lines 209, 214, 238, 363)**: Touch target remediation (>= 44x44px):
  - Line 209 & 214 (Category filter pills): Change `h-10` to `min-h-[44px] h-11 px-4`.
  - Line 238 (Capabilities filter pills): Change `h-8 px-3` to `min-h-[44px] py-2 px-3`.
  - Line 363 (Store card visit button): Change `h-9 px-4` to `min-h-[44px] h-11 px-4 shrink-0`.
- **Fix 4.6 (Line 223)**: Accessibility contrast fix on filter label:
  ```blade
  {{-- Before --}}
  <span class="text-slate-400 dark:text-slate-500 font-semibold uppercase tracking-wider text-[11px] shrink-0 mr-1">
  {{-- After --}}
  <span class="text-slate-600 dark:text-slate-400 font-semibold uppercase tracking-wider text-[11px] shrink-0 mr-1">
  ```
- **Fix 4.7 (Lines 11–26)**: Add Schema.org `BreadcrumbList` in `@push('seo')`:
  ```html
  <script type="application/ld+json">
  {
    "@@context": "https://schema.org",
    "@type": "BreadcrumbList",
    "itemListElement": [
      {
        "@type": "ListItem",
        "position": 1,
        "name": "{{ __('public_discovery.disc_breadcrumb_home') }}",
        "item": "{{ url('/') }}"
      },
      {
        "@type": "ListItem",
        "position": 2,
        "name": "{{ __('public_discovery.disc_breadcrumb') }}",
        "item": "{{ route('public.discovery.index') }}"
      }
    ]
  }
  </script>
  ```

### File 5 (Localization Alignment): `lang/id/public_discovery.php`
- **Fix 5.1 (Line 47)**: Align empty search title with test expectations:
  ```php
  // Before
  'disc_empty_title' => 'Toko Belum Ditemukan',
  // After
  'disc_empty_title' => 'Toko Tidak Ditemukan',
  ```

---

## 5. Verification Method

1. **PHP Syntax Validation**:
   ```powershell
   php -l resources/views/public/landing_commercial/kasir_android.blade.php
   php -l resources/views/public/landing_commercial/kasir_gratis.blade.php
   php -l resources/views/public/landing_commercial/kasir_offline.blade.php
   php -l resources/views/public/discovery/index.blade.php
   php -l lang/id/public_discovery.php
   ```
   *Expected Result*: "No syntax errors detected" across all 5 files.

2. **Automated Heading Hierarchy & Commercial Routes Test**:
   ```powershell
   php artisan test tests/Feature/PublicMarketingFrontendE2ETest.php
   ```
   *Expected Result*: All 18 tests in `PublicMarketingFrontendE2ETest` pass, verifying single H1 per page, strict semantic hierarchy, and zero uncompiled translation keys.

3. **Discovery Directory Feature Test**:
   ```powershell
   php artisan test --filter="test_can_search_by_store_name_and_product_name|test_can_filter_by_category_and_capabilities" tests/Feature/PublicBusinessDiscoveryTest.php
   ```
   *Expected Result*: All search and filter assertions pass once empty state copy is aligned.

4. **DOM Inspection & Touch Target Verification**:
   - Inspect `discovery/index.blade.php` at viewports `320px`, `360px`, `390px`, `414px` in Chrome DevTools.
   - Verify filter pill bounding boxes measure `>= 44px` in computed height.
   - Verify store logo `<img>` tags possess `width="48"` and `height="48"`.
   - Invalidate fix if any H1 -> H3 skip persists or if filter button height is `< 44px`.
