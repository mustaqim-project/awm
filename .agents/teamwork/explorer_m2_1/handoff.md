# Handoff Report: Standalone Marketing Views Investigation (M2)

**Agent**: `explorer_m2_1`  
**Working Directory**: `C:\laragon\www\cooca_core`  
**State Folder**: `C:\laragon\www\cooca_core\.agents\teamwork\explorer_m2_1\`  
**Assigned Scope**: 10 Standalone Marketing Views:
1. `resources/views/landing.blade.php`
2. `resources/views/public/business_landing.blade.php`
3. `resources/views/public/about.blade.php`
4. `resources/views/public/demo.blade.php`
5. `resources/views/public/pricing.blade.php`
6. `resources/views/public/privacy.blade.php`
7. `resources/views/public/security.blade.php`
8. `resources/views/public/sitemap.blade.php`
9. `resources/views/public/support.blade.php`
10. `resources/views/public/terms.blade.php`

---

## 1. Observation

Direct code observations from inspecting all 10 Blade files, language files (`lang/id/`, `lang/en/`), and routes (`routes/public.php`):

### 1.1 `resources/views/public/business_landing.blade.php`
- **Tailwind Play CDN (Lines 140–183)**:
  ```html
  <script>
      (function() {
          const _origWarn = console.warn;
          console.warn = function(...args) {
              if (args.some(a => typeof a === 'string' && a.indexOf('tailwindcss.com') !== -1)) return;
              _origWarn.apply(console, args);
          };
      })();
  </script>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
      tailwind.config = { darkMode: 'class', ... }
  </script>
  ```
  The template actively monkey-patches `console.warn` to silence the production warning and loads the runtime Play CDN (~350KB uncompressed), forcing client-side JIT compilation on initial load instead of using the compiled Vite pipeline (`@vite(['resources/css/app.css', 'resources/js/app.js'])`).
- **Translation Array TypeError (Line 8624)**:
  ```html
  8624: <span>{{ __('storefront.tracking') }}</span>
  ```
  In both `lang/id/storefront.php` (line 178) and `lang/en/storefront.php` (line 178), `'tracking'` is defined as an associative array:
  ```php
  'tracking' => [
      'title' => 'Pelacakan Pesanan', // in EN: 'Order Tracking'
      'subtitle' => 'Masukkan nomor atau token pesanan...',
      'order_number' => 'Nomor Pesanan / Token Lacak',
      'enter_token_placeholder' => '...',
      'track_button' => 'Lacak Pesanan',
      ...
  ]
  ```
  Calling `{{ __('storefront.tracking') }}` passes an array into `htmlspecialchars()`, causing a fatal PHP `TypeError: htmlspecialchars(): Argument #1 ($string) must be of type string, array given`.
- **Script Tags Optimization (Lines 1780–1781)**:
  ```html
  1780: <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  1781: <script src="https://unpkg.com/lucide@latest"></script>
  ```
  Alpine is unpinned (`3.x.x`). Lucide is unpinned (`@latest`), served from unpkg.com, and lacks `defer`, blocking parser execution mid-document at line 1781.
- **Sticky Banner H2 Preceding H1 (Lines 3386 vs 3857)**:
  ```html
  3386: <h2 class="text-[13px] sm:text-[14px] font-bold text-black dark:text-white tracking-tight"
            x-text="groupOrder.data.title || 'Pesanan Bersama'"></h2>
  ...
  3857: <h1 class="text-[34px] sm:text-[46px] lg:text-[54px] font-extrabold ...">
            {{ $landingPage->headline ?: $business->name }}
        </h1>
  ```
  An `<h2>` heading is rendered inside the sticky group-order header above the page's primary `<h1>` at line 3857.
- **Above-The-Fold Media Sizing & Touch Targets**:
  - Line 3512: Brand logo `<img src="{{ $headerBrandLogo }}" ...>` lacks `width="36" height="36" decoding="async"`.
  - Line 3969: Hero LCP image `<img src="{{ $heroDisplayImage }}" ...>` has `fetchpriority="high"`, but lacks `width="640" height="480"` (or aspect ratio dimensions) and `decoding="async"`.
  - Line 3713: Mobile hamburger toggle button `<button ... class="md:hidden w-9 h-9 ...">` is only 36x36px, failing WCAG 2.5.5 (44x44px minimum).

---

### 1.2 Heading Hierarchies Across Marketing Views
Detailed scanning revealed heading skips in 7 views:

1. **`resources/views/public/about.blade.php`**:
   - Single H1 at line 51: `{{ __('public_marketing.about_hero_title') }}` (Pass).
   - Line 110: `<h3>Filosofi Ekosistem Terpadu COOCA</h3>` inside hero illustration card skips H1 -> H3 without an intervening H2.
   - Lines 275, 285, 294, 303, 312, 322: Target audience industry cards use `<h4 class="text-sm font-bold ...">` directly following `<h2>` at line 266 (H2 -> H4 skip).
   - Line 347: `<h3 class="text-2xl sm:text-4xl font-extrabold ...">` for the Final CTA `<section>` (line 332); should be an `<h2>` section heading.
2. **`resources/views/public/privacy.blade.php`**:
   - Single H1 at line 36: `{{ $page->title ?? 'Kebijakan Privasi & Pelindungan Data Pribadi' }}` (Pass).
   - Line 178: `<h3 class="text-[14px] font-bold text-black dark:text-white">{{ __('public_marketing.legal_toc_title_privacy') }}</h3>` inside the sidebar TOC navigation skips H1 -> H3.
   - Line 284: `<h4 class="text-base sm:text-lg font-bold text-white">Kelola Bisnis dengan Tenang &amp; Aman</h4>` in exit ramp banner skips H2 -> H4 under section H2s (lines 239, 257).
3. **`resources/views/public/terms.blade.php`**:
   - Single H1 at line 38: `{{ $page->title ?? 'Syarat & Ketentuan Layanan (Terms of Service)' }}` (Pass).
   - Line 204: `<h3 class="text-[14px] font-bold text-black dark:text-white">{{ __('public_marketing.legal_toc_title_terms') }}</h3>` in TOC aside skips H1 -> H3.
   - Line 314: `<h4 class="text-base sm:text-lg font-bold text-white">Mulai Otomasi Bisnis Anda Hari Ini</h4>` in exit ramp banner skips H2 -> H4 under section H2s (lines 267, 286).
4. **`resources/views/public/pricing.blade.php`**:
   - Single H1 at line 133: `{{ \App\Services\Seo\SeoMetadataService::getH1() }}` (Pass).
   - Plan cards are `<h2>` (lines 295, 368, 450, 527), but the subsequent major sections are tagged as `<h3>`:
     - Line 621: `<h3 ...>{{ __('public_marketing.pricing_wizard_title') }}</h3>` (Wizard)
     - Line 835: `<h3 ...>Add-On Fleksibel Tanpa Memaksa Beli Paket Mahal</h3>` (Add-ons section)
       - Lines 855, 882, 910: Add-on card titles are `<h4>` under line 835.
     - Line 984: `<h3 ...>Pertanyaan yang Paling Sering Diajukan Pemilik Toko</h3>` (FAQ section)
     - Line 1129: `<h3 ...>{{ __('public_marketing.pricing_still_hesitant_title') }}</h3>` (Final CTA section)
5. **`resources/views/public/demo.blade.php`**:
   - Single H1 at line 51: `{{ __('public_marketing.demo_hero_title_lead') }} ...` (Pass).
   - Line 224: `<h2>` with level 3 step cards (lines 236, 249, 262, 275) (Pass).
   - Line 292: `<h2>` followed directly by `<h4 class="text-sm font-bold ...">` cards at lines 301, 308, 315 (H2 -> H4 skip).
   - Line 398: `<h3 class="text-2xl sm:text-4xl font-extrabold ...">{{ __('public_marketing.demo_bottom_cta_title') }}</h3>` for Final CTA section (should be `<h2>`).
6. **`resources/views/public/security.blade.php`**:
   - Single H1 at line 47: `{{ __('public_marketing.sec_hero_title') }}` (Pass).
   - Line 241: `<h3 class="text-xl sm:text-2xl font-bold ...">Prinsip Transparansi Keamanan COOCA</h3>` represents a top-level section; should be `<h2>`. Lines 248, 257 inside it are `<h4>`; should be `<h3>`.
7. **`resources/views/public/sitemap.blade.php`**:
   - Single H1 at line 35: `{{ __('public_marketing.sitemap_hero_lead') }} ...` (Pass).
   - Line 223: `<h3>` section header should be `<h2>`. Line 256 `<h4>` banner title should be `<h3>`.
8. **`resources/views/public/support.blade.php`**:
   - Single H1 at line 46: `Ada Pertanyaan Teknis? ...` (Pass).
   - Line 375: `<h3 class="text-2xl sm:text-4xl font-extrabold ...">{{ __('public_marketing.support_bottom_cta_title') }}</h3>` for Final CTA section (should be `<h2>`).
9. **`resources/views/landing.blade.php`**:
   - Single H1 at line 200 (Pass).
   - Line 1234: `<h3>{{ __('public_home.bottom_cta_subtitle') }}</h3>` is used as a subtitle under H2 line 1228; should be `<p>` or `<div>`.

---

### 1.3 SEO Tags: Canonical URLs & Schema.org JSON-LD
Inspection of SEO tags showed:
- **Canonical URLs Missing**:
  - `resources/views/public/pricing.blade.php`  Missing `@section('canonical', route('public.pricing'))`
  - `resources/views/public/privacy.blade.php`  Missing `@section('canonical', route('public.privacy'))`
  - `resources/views/public/terms.blade.php`  Missing `@section('canonical', route('public.terms'))`
  - `resources/views/public/sitemap.blade.php`  Missing `@section('canonical', route('sitemap.html'))`
- **Schema.org JSON-LD Missing**:
  - `resources/views/public/pricing.blade.php`  Missing `<script type="application/ld+json">` (`Product` / `OfferCatalog` / `SoftwareApplication`).
  - `resources/views/public/privacy.blade.php`  Missing Schema.org JSON-LD (`WebPage` / `AboutPage` privacy statement).
  - `resources/views/public/terms.blade.php`  Missing Schema.org JSON-LD (`WebPage` terms statement).
  - `resources/views/public/sitemap.blade.php`  Missing Schema.org JSON-LD (`SiteNavigationElement` / `CollectionPage`).

---

### 1.4 Theme Contrast (Light & Dark)
- In `resources/views/public/pricing.blade.php`:
  Lines 1296–1303: Table checkmark cells `class="p-3 sm:p-4 text-center text-emerald-600"` lack `dark:text-emerald-400`. On dark modal surfaces (`#1C1C1E`), `text-emerald-600` has lower contrast (3.8:1), whereas `text-emerald-600 dark:text-emerald-400` reaches 8.6:1 (WCAG AAA).
- Translucent hero elements (`bg-white/[0.04]`, `bg-white/[0.05]`) across `landing`, `demo`, `pricing`, `support` are placed inside permanent dark `#060B1E` / `#0A122C` backdrops and maintain safe contrast.

---

### 1.5 Touch Targets (< 44x44px)
- **`resources/views/public/business_landing.blade.php`**:
  - Line 3713: Mobile hamburger button `class="md:hidden w-9 h-9 ..."` (36x36px).
- **`resources/views/layouts/public_marketing.blade.php`**:
  - Line 669: Mobile hamburger button `class="lg:hidden w-9 h-9 sm:w-10 sm:h-10 ..."` (36x36px on mobile, 40x40px on tablet).
- **`resources/views/public/sitemap.blade.php`**:
  - Line 58: Raw XML download button `<a ... class="h-8 px-3.5 ...">` (32px height).
  - Line 202: Directory item links `class="... min-h-[40px]"` (40px height).
- **`resources/views/public/privacy.blade.php` & `resources/views/public/terms.blade.php`**:
  - Line 63 (privacy) / Line 62 (terms): Print button `<button class="h-8 px-3.5 ...">` (32px height).
  - Line 74 (privacy) / Line 73–85 (terms): Audience toggle buttons `<button class="h-8 px-3.5 ...">` (32px height).
  - Lines 181–203 (privacy) / Lines 207–230 (terms): TOC links `<a class="block p-2 ...">` (36px tap height).
- **`resources/views/public/pricing.blade.php`**:
  - Line 1514: Detail modal close button `<button class="w-9 h-9 ...">` (36x36px).

---

## 2. Logic Chain

1. **Tailwind Play CDN**:
   - `business_landing.blade.php` was created as an isolated storefront template with an inline `<script src="https://cdn.tailwindcss.com"></script>` and custom `tailwind.config` script (lines 140–183).
   - In production, `@tailwindcss/vite` compiles `resources/css/app.css` into `public/build/assets/app-*.css`, which includes all Tailwind v4 utilities.
   - Therefore, replacing the CDN script and warning silencer with `@vite(['resources/css/app.css', 'resources/js/app.js'])` eliminates ~350KB of client-side JS and stops client JIT compilation. Dynamic tenant colors (`--primary-color`, `--font-storefront`) are already handled by the existing `<style>` block (lines 191–204).

2. **Translation Array TypeError**:
   - Line 8624 uses `<span>{{ __('storefront.tracking') }}</span>`.
   - `lang/id/storefront.php` and `lang/en/storefront.php` define `'tracking'` as an array containing keys `'title'`, `'subtitle'`, `'order_number'`, etc.
   - When Blade evaluates `{{ ... }}`, Laravel passes the return value of `__()` to `htmlspecialchars()`. Passing an array to `htmlspecialchars()` throws a fatal `TypeError`.
   - Modifying line 8624 to `<span>{{ __('storefront.tracking.title') }}</span>` resolves the array to the string "Pelacakan Pesanan" (ID) or "Order Tracking" (EN), preventing the runtime crash.

3. **Heading Skips**:
   - WCAG 2.1 Success Criterion 1.3.1 (Info and Relationships) and Section 508 require logical sequential heading levels (no H1 -> H3 or H2 -> H4 skips).
   - In `about.blade.php`, replacing `<h3>` at line 110 (which is an internal card label in the hero illustration) with `<p class="font-bold ...">`, updating lines 275–322 from `<h4>` to `<h3>`, and changing the bottom CTA heading (line 347) from `<h3>` to `<h2>` establishes an unbroken H1 -> H2 -> H3 hierarchy.
   - Applying corresponding adjustments to `privacy.blade.php`, `terms.blade.php`, `pricing.blade.php`, `demo.blade.php`, `security.blade.php`, `sitemap.blade.php`, `support.blade.php`, and `landing.blade.php` ensures 100% heading compliance across all 10 marketing templates.

4. **SEO Canonicals & Schema.org JSON-LD**:
   - `public_marketing.blade.php` uses `public.partials.seo.blade.php` to resolve canonical tags via `View::yieldContent('canonical')` and JSON-LD structured data.
   - Four pages (`pricing`, `privacy`, `terms`, `sitemap`) omitted `@section('canonical')` and lacked dedicated `<script type="application/ld+json">` schemas.
   - Adding explicit `@section('canonical', route(...))` and rich JSON-LD data blocks (e.g. `Product`/`OfferCatalog` for Pricing, `WebPage`/`AboutPage` for Legal, `SiteNavigationElement` for Sitemap) completes organic search metadata.

5. **Touch Targets (WCAG 2.5.5)**:
   - Elements with `h-8` (32px), `min-h-[40px]`, `w-9 h-9` (36px), and `p-2` on 13px text (<36px) fail the 44x44px minimum touch target size.
   - Increasing heights to `h-11` or adding `min-h-[44px] min-w-[44px]` ensures all interactive controls can be reliably tapped on mobile touchscreens without mis-clicks.

---

## 3. Caveats

1. **Tenant Color Classes in `business_landing.blade.php`**:
   The Play CDN allowed arbitrary class evaluation like `bg-brand-500` or `text-brand-600`. In Vite Tailwind v4, classes must be statically generated or driven via CSS variables. The template already defines `--primary`, `--primary-color`, `--primary-rgb`, `.bg-brand-primary`, and `.text-brand-primary` in its `<style>` block (lines 191–220). The Worker should ensure any references to dynamic brand colors utilize `var(--primary)` or existing `.bg-brand-primary` / `.text-brand-primary` classes.
2. **`config/seo.php` vs Blade `@section`**:
   `pricing`, `about`, `demo`, and `support` have route entries in `config/seo.php`, but `privacy`, `terms`, `security`, and `sitemap` do not. Supplying `@section('title')`, `@section('description')`, and `@section('canonical')` in each Blade file guarantees fallback resilience regardless of `config/seo.php`.

---

## 4. Conclusion & Actionable Fix Inventory for Worker

### C1. Fixes for `resources/views/public/business_landing.blade.php`
1. **Remove Play CDN & Replace with Vite Pipeline**:
   - **Target**: Lines 140–183:
     Delete the `console.warn` suppression block, `<script src="https://cdn.tailwindcss.com"></script>`, and `tailwind.config = { ... }`.
   - **Replacement**: Insert in `<head>` (e.g. line 139):
     ```blade
     @vite(['resources/css/app.css', 'resources/js/app.js'])
     ```
   - In the `<style>` block (line 191), ensure dynamic font mapping is present:
     ```css
     :root {
         --font-storefront: "{{ $configuredFont }}", -apple-system, BlinkMacSystemFont, "SF Pro Display", "SF Pro Text", "Inter", system-ui, sans-serif;
     }
     body {
         font-family: var(--font-storefront);
     }
     ```
2. **Fix Translation Array TypeError on Line 8624**:
   - **Target**:
     ```blade
     <span>{{ __('storefront.tracking') }}</span>
     ```
   - **Replacement**:
     ```blade
     <span>{{ __('storefront.tracking.title') }}</span>
     ```
3. **Pin and Defer Scripts (Lines 1780–1781)**:
   - **Target**:
     ```html
     <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
     <script src="https://unpkg.com/lucide@latest"></script>
     ```
   - **Replacement**:
     ```html
     <script defer src="https://cdn.jsdelivr.net/npm/lucide@0.469.0/dist/umd/lucide.min.js"></script>
     <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
     ```
4. **Fix Sticky Banner H2 Preceding H1 (Line 3386)**:
   - Change line 3386 from:
     ```blade
     <h2 class="text-[13px] sm:text-[14px] font-bold text-black dark:text-white tracking-tight"
         x-text="groupOrder.data.title || 'Pesanan Bersama'"></h2>
     ```
     to:
     ```blade
     <p class="text-[13px] sm:text-[14px] font-bold text-black dark:text-white tracking-tight"
        x-text="groupOrder.data.title || 'Pesanan Bersama'"></p>
     ```
5. **Add Explicit Media Dimensions & Async Decoding**:
   - Line 3512 (Logo): Add `width="36" height="36" decoding="async"`.
   - Line 3969 (Hero LCP image): Add `width="640" height="480" decoding="async" fetchpriority="high"`.
   - Line 4689 (Catalog image): Add `width="300" height="300" decoding="async"`.
6. **Mobile Hamburger Button Touch Target (Line 3713)**:
   - Change `w-9 h-9` to `w-11 h-11 min-w-[44px] min-h-[44px]`.

---

### C2. Heading Hierarchy Remediations
1. **`resources/views/public/about.blade.php`**:
   - Line 110: Replace `<h3>Filosofi Ekosistem Terpadu COOCA</h3>` with `<p class="text-sm sm:text-base font-bold text-white mt-0.5 leading-snug">Filosofi Ekosistem Terpadu COOCA</p>`.
   - Lines 275, 285, 294, 303, 312, 322: Change `<h4 class="text-sm font-bold ...">` to `<h3 class="text-sm font-bold text-slate-900 dark:text-white leading-snug">`.
   - Line 347: Change `<h3 class="text-2xl sm:text-4xl font-extrabold ...">` to `<h2 class="text-2xl sm:text-4xl font-extrabold text-white tracking-tight leading-[1.2] text-balance break-words">`.
2. **`resources/views/public/privacy.blade.php`**:
   - Line 178: Replace `<h3 class="text-[14px] ...">` with `<p class="text-[14px] font-bold text-black dark:text-white">{{ __('public_marketing.legal_toc_title_privacy') }}</p>`.
   - Line 284: Replace `<h4 class="text-base sm:text-lg ...">` with `<h3 class="text-base sm:text-lg font-bold text-white">Kelola Bisnis dengan Tenang &amp; Aman</h3>`.
3. **`resources/views/public/terms.blade.php`**:
   - Line 204: Replace `<h3 class="text-[14px] ...">` with `<p class="text-[14px] font-bold text-black dark:text-white">{{ __('public_marketing.legal_toc_title_terms') }}</p>`.
   - Line 314: Replace `<h4 class="text-base sm:text-lg ...">` with `<h3 class="text-base sm:text-lg font-bold text-white">Mulai Otomasi Bisnis Anda Hari Ini</h3>`.
4. **`resources/views/public/pricing.blade.php`**:
   - Line 621 (Wizard header): Change `<h3>` to `<h2>`.
   - Line 835 (Add-ons header): Change `<h3>` to `<h2>`.
   - Lines 855, 882, 910 (Add-on card titles): Change `<h4>` to `<h3>`.
   - Line 984 (FAQ header): Change `<h3>` to `<h2>`.
   - Line 1129 (Final CTA header): Change `<h3>` to `<h2>`.
5. **`resources/views/public/demo.blade.php`**:
   - Lines 301, 308, 315: Change `<h4 class="text-sm font-bold ...">` to `<h3 class="text-sm font-bold text-slate-900 dark:text-white leading-snug">`.
   - Line 398: Change `<h3 class="text-2xl sm:text-4xl ...">` to `<h2 class="text-2xl sm:text-4xl font-extrabold text-white tracking-tight leading-[1.2] ...">`.
6. **`resources/views/public/security.blade.php`**:
   - Line 241: Change `<h3 class="text-xl sm:text-2xl font-bold ...">` to `<h2>`.
   - Lines 248, 257: Change `<h4 ...>` to `<h3>`.
7. **`resources/views/public/sitemap.blade.php`**:
   - Line 223: Change `<h3>` to `<h2>`.
   - Line 256: Change `<h4>` to `<h3>`.
8. **`resources/views/public/support.blade.php`**:
   - Line 375: Change `<h3>` to `<h2>`.
9. **`resources/views/landing.blade.php`**:
   - Line 1234: Change `<h3>` subtitle to `<p class="text-base sm:text-lg lg:text-xl font-semibold text-slate-200 tracking-tight">`.

---

### C3. SEO Canonicals and Structured Data Remediations
1. **`resources/views/public/pricing.blade.php`**:
   - Add `@section('canonical', route('public.pricing'))`.
   - Add Schema.org JSON-LD structured data for pricing plans:
     ```html
     @section('schema')
     <script type="application/ld+json">
     {
         "@context": "https://schema.org",
         "@type": "Product",
         "name": "COOCA Omnichannel ERP",
         "description": "{{ __('public_marketing.pricing_seo_desc') }}",
         "offers": [
             {
                 "@type": "Offer",
                 "name": "Gratis",
                 "price": "0",
                 "priceCurrency": "IDR",
                 "url": "{{ route('public.pricing') }}"
             },
             {
                 "@type": "Offer",
                 "name": "Standard",
                 "price": "49000",
                 "priceCurrency": "IDR",
                 "url": "{{ route('public.pricing') }}"
             },
             {
                 "@type": "Offer",
                 "name": "Premium",
                 "price": "99000",
                 "priceCurrency": "IDR",
                 "url": "{{ route('public.pricing') }}"
             },
             {
                 "@type": "Offer",
                 "name": "Prestige",
                 "price": "199000",
                 "priceCurrency": "IDR",
                 "url": "{{ route('public.pricing') }}"
             }
         ]
     }
     </script>
     @endsection
     ```
2. **`resources/views/public/privacy.blade.php`**:
   - Add `@section('canonical', route('public.privacy'))`.
   - Add Schema.org JSON-LD:
     ```html
     @section('schema')
     <script type="application/ld+json">
     {
         "@context": "https://schema.org",
         "@type": "WebPage",
         "name": "{{ $page->title ?? 'Kebijakan Privasi' }}",
         "description": "{{ $page->meta_description ?? 'Kebijakan privasi resmi COOCA.' }}",
         "url": "{{ route('public.privacy') }}",
         "publisher": {
             "@type": "Organization",
             "name": "PT Cooca Digital Teknologi",
             "url": "{{ url('/') }}"
         }
     }
     </script>
     @endsection
     ```
3. **`resources/views/public/terms.blade.php`**:
   - Add `@section('canonical', route('public.terms'))`.
   - Add Schema.org JSON-LD:
     ```html
     @section('schema')
     <script type="application/ld+json">
     {
         "@context": "https://schema.org",
         "@type": "WebPage",
         "name": "{{ $page->title ?? 'Syarat & Ketentuan Layanan' }}",
         "description": "{{ $page->meta_description ?? 'Syarat dan Ketentuan resmi COOCA.' }}",
         "url": "{{ route('public.terms') }}",
         "publisher": {
             "@type": "Organization",
             "name": "PT Cooca Digital Teknologi",
             "url": "{{ url('/') }}"
         }
     }
     </script>
     @endsection
     ```
4. **`resources/views/public/sitemap.blade.php`**:
   - Add `@section('canonical', route('sitemap.html'))`.
   - Add Schema.org JSON-LD:
     ```html
     @section('schema')
     <script type="application/ld+json">
     {
         "@context": "https://schema.org",
         "@type": "CollectionPage",
         "name": "Peta Situs COOCA",
         "description": "{{ __('public_marketing.sitemap_seo_desc') }}",
         "url": "{{ route('sitemap.html') }}",
         "publisher": {
             "@type": "Organization",
             "name": "COOCA",
             "url": "{{ url('/') }}"
         }
     }
     </script>
     @endsection
     ```

---

### C4. Theme Contrast and Touch Target Remediations
1. **Pricing Comparison Modal Checkmark Contrast (`pricing.blade.php`)**:
   - At lines 1296–1303, update checkmark cells to:
     ```html
     class="p-3 sm:p-4 text-center text-emerald-600 dark:text-emerald-400"
     ```
2. **Touch Targets Upgrade (>= 44x44px)**:
   - **`resources/views/layouts/public_marketing.blade.php` (Line 669)**:
     Change `lg:hidden w-9 h-9 sm:w-10 sm:h-10` to `lg:hidden w-11 h-11 min-w-[44px] min-h-[44px]`.
   - **`resources/views/public/sitemap.blade.php`**:
     - Line 58 (XML link): Update `h-8` to `min-h-[44px] h-11`.
     - Line 202 (Directory link): Update `min-h-[40px]` to `min-h-[44px]`.
   - **`resources/views/public/privacy.blade.php` & `terms.blade.php`**:
     - Line 63 / Line 62 (Print button): Update `h-8` to `min-h-[44px] h-11`.
     - Line 74 / Line 73–85 (Audience filter buttons): Update `h-8` to `min-h-[44px] h-11`.
     - Lines 181–203 / Lines 207–230 (TOC links): Update `p-2` to `py-3 px-3 min-h-[44px] flex items-center`.
   - **`resources/views/public/pricing.blade.php` (Line 1514)**:
     - Update detail modal close button from `w-9 h-9` to `w-11 h-11 min-w-[44px] min-h-[44px]`.

---

## 5. Verification Method

To independently verify all findings and validate implementation by the Worker:

1. **Syntax Check**:
   ```powershell
   php -l resources/views/landing.blade.php
   php -l resources/views/public/business_landing.blade.php
   php -l resources/views/public/about.blade.php
   php -l resources/views/public/demo.blade.php
   php -l resources/views/public/pricing.blade.php
   php -l resources/views/public/privacy.blade.php
   php -l resources/views/public/security.blade.php
   php -l resources/views/public/sitemap.blade.php
   php -l resources/views/public/support.blade.php
   php -l resources/views/public/terms.blade.php
   ```
   **Expected**: 0 syntax errors.

2. **Vite Production Asset Build**:
   ```powershell
   npm run build
   ```
   **Expected**: Clean build exit (code 0) without missing Tailwind directives or invalid CSS variables.

3. **Heading Hierarchy Verification**:
   Execute `.agents/teamwork/explorer_m2_1/inspect_views.py` or `.agents/teamwork/explorer_m2_1/detailed_audit.py`.
   **Expected**: 0 heading skips (no H1 -> H3, no H2 -> H4) and exactly 1 H1 per page.

4. **Translation Key Verification**:
   Run a test rendering of `business_landing` with group order data or evaluate `__('storefront.tracking.title')` in tinker:
   ```powershell
   php artisan tinker --execute="echo __('storefront.tracking.title');"
   ```
   **Expected**: Outputs "Pelacakan Pesanan" (or "Order Tracking") with 0 type errors.

5. **Touch Target Verification**:
   Run `.agents/teamwork/explorer_m2_1/detailed_audit.py` to confirm 0 buttons/links with heights < 44px on mobile viewports.
