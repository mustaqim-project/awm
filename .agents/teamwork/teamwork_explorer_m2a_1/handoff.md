# Handoff Report  Milestone 2A: Audit Themes 1 to 4

**Agent:** Explorer 1 (`teamwork_explorer_m2a_1`)  
**Scope:** Themes 1 to 4 (`universal_storefront`, `modern_umkm`, `online_store`, `business_corporate`)  
**Target Files:** `hero.blade.php`, `features_banner.blade.php`, `category_chips.blade.php` (12 files total)

---

## 1. Observation

### Theme 1: `universal_storefront`
- `resources/views/public/storefront/themes/universal_storefront/hero.blade.php`:
  - Line 13: `<span>{{ $activeTheme['industry'] ?? 'Toko Resmi & Terverifikasi' }}</span>` bypasses `$landingPage->announcement_badge`.
  - Line 21: `{{ $landingPage->subheadline ?: 'Temukan produk & layanan terbaik kami dengan kualitas terjamin, pemesanan mudah, dan transaksi aman langsung dari outlet resmi.' }}` hardcodes Indonesian sector copy.
  - Line 28: `<span>{{ $landingPage->cta_primary_text ?: 'Jelajahi Katalog' }}</span>` hardcodes text fallback instead of `__('storefront.hero.explore_catalog')`.
  - Line 37: `<span>Tanya CS WhatsApp</span>` bypasses `$landingPage->cta_secondary_text` and `__('storefront.hero.contact_us')`.
  - Line 53: `$heroImg = $landingPage->hero_image_url ?: 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=1000&auto=format&fit=crop&q=80';` sets a hardcoded food/restaurant Unsplash photo.
  - Lines 63, 65: Hardcoded floating status pill strings `"Outlet Resmi &amp; Siap Melayani"` and `"100% Terverifikasi"`.
  - Missing 3 universal badge pills from `$landingPage->getUniversalValues()`.
- `resources/views/public/storefront/themes/universal_storefront/features_banner.blade.php`:
  - Lines 7-46: 4 completely static cards with hardcoded descriptions (e.g. Line 13: `"Produk dan layanan 100% original dari merchant terpercaya."`, Line 23: `"Mendukung transfer bank, QRIS, e-wallet, dan COD."`, Line 33: `"Ekspedisi terpercaya dengan pelacakan nomor resi otomatis."`, Line 42: `"Customer Support Siaga"`). Completely ignores `$landingPage->values` / `$landingPage->getUniversalValues()`.
- `resources/views/public/storefront/themes/universal_storefront/category_chips.blade.php`:
  - Line 11: `<p class="text-xs sm:text-sm text-neutral-500 dark:text-neutral-400">Pilih kategori untuk memfilter etalase belanja</p>` hardcodes Indonesian helper text.
  - Lines 21-31: Missing "Semua Produk" chip with `layout-grid` icon and active state checking (`!request()->filled('category')`). Missing active category chip styling (`theme-btn-primary shadow-xs`).

### Theme 2: `modern_umkm`
- `resources/views/public/storefront/themes/modern_umkm/hero.blade.php`:
  - Line 5: Section has hardcoded emerald classes `bg-emerald-50/50 dark:bg-emerald-950/20 border-b border-emerald-900/10`.
  - Line 11: Eyebrow has hardcoded emerald classes `bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300`.
  - Line 13: Hardcoded text fallback `{{ $activeTheme['industry'] ?? 'Produk & Usaha UMKM Pilihan' }}`.
  - Line 17: Headline fallbacks to `'Kualitas Terbaik dari ' . $business->name . ' untuk Kebutuhan Anda'`.
  - Line 21: Subheadline hardcodes `'Dibuat dengan sepenuh hati, harga ramah di kantong, dan siap dikirim langsung dari outlet kami ke tempat Anda.'`.
  - Line 27: Primary CTA has hardcoded emerald button classes `bg-emerald-600 hover:bg-emerald-700` and text `'Belanja Sekarang'`.
  - Line 35: Secondary CTA has hardcoded emerald classes `text-emerald-700 dark:text-emerald-300 border border-emerald-300` and text `'Chat Pemilik Toko'`.
  - Lines 43-52: Hardcoded static highlights `"100% Produk Lokal"` and `"Terverifikasi COOCA"`.
  - Line 59: Hardcoded food/restaurant Unsplash photo `'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=1000&auto=format&fit=crop&q=80'`.
  - Lines 68, 70: Hardcoded floating pill texts `"Outlet Resmi Buka Hari Ini"` and `"Siap Kirim"`.
- `resources/views/public/storefront/themes/modern_umkm/features_banner.blade.php`:
  - Line 6: Hardcoded emerald border `border-emerald-900/10 dark:border-emerald-500/20`.
  - Lines 9, 23, 35, 47: Icon containers hardcode `bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300`.
  - Lines 14, 17, 29, 41, 52, 55: Hardcoded static sector copy ("100% Asli & Terjamin", "Pelayanan Ramah", etc.), ignoring `$landingPage->getUniversalValues()`.
- `resources/views/public/storefront/themes/modern_umkm/category_chips.blade.php`:
  - Lines 9, 13, 22, 23: Hardcoded emerald colors (`bg-emerald-500`, `text-emerald-600`, `border-emerald-100`, `bg-emerald-400`).
  - Missing "Semua Produk" chip, missing subtitle `__('storefront.pilih_kategori_filter')`, missing active category indicator.

### Theme 3: `online_store`
- `resources/views/public/storefront/themes/online_store/hero.blade.php`:
  - Lines 18-20: Hardcoded retail eyebrow texts: `<span ...>Official Store</span>` and `<span ...>Koleksi Terbaru &amp; Ready Stock</span>`.
  - Line 25: Headline hardcodes `'Belanja Produk Pilihan, Kualitas Premium & Bergaransi'`.
  - Line 29: Subheadline hardcodes `'Temukan beragam pilihan produk terbaik dengan jaminan originalitas, proses transaksi aman, dan pengiriman kilat ke seluruh nusantara.'`.
  - Line 36-39: Primary CTA has hardcoded zinc styling `bg-zinc-900 hover:bg-zinc-800` and text `'Jelajahi Etalase'`.
  - Line 44-47: Secondary CTA has hardcoded emerald styling `bg-emerald-500 hover:bg-emerald-600` and text `'Chat Admin Toko'`.
  - Lines 52-65: Hardcoded retail trust metrics strip: `"100% Ori"`, `"Jaminan Asli"`, `"Fast Pack"`, `"Kirim Hari Ini"`, `"Aman"`, `"Garansi Retur"`.
  - Line 81: Graphic fallback card has hardcoded retail copy: `"Destinasi belanja pilihan dengan promo spesial setiap minggu."`.
  - Lines 85-97: Floating flash badge has hardcoded copy: `"Promo Flash Order"`, `"Nikmati subsidi ongkir hari ini"` and hardcoded indigo utility classes (`bg-indigo-50`, `text-indigo-600`).
- `resources/views/public/storefront/themes/online_store/features_banner.blade.php`:
  - Lines 10, 21, 32, 43: Hardcoded indigo styling `bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-100 text-indigo-600`.
  - Lines 14, 15, 26, 37, 47, 48: Hardcoded static copy ("100% Original", "Garansi keaslian setiap produk", "Garansi Retur", etc.), ignoring `$landingPage->getUniversalValues()`.
- `resources/views/public/storefront/themes/online_store/category_chips.blade.php`:
  - Line 10: Hardcoded retail subtitle `"Pilih departemen produk yang Anda cari"`.
  - Line 14: Non-standard key `{{ __('storefront.semua_departemen') }}` instead of `{{ __('storefront.view_all') }}`.
  - Lines 13, 22: Hardcoded indigo classes `text-indigo-600`, `hover:border-indigo-500`.
  - Missing "Semua Produk" chip and active category styling.

### Theme 4: `business_corporate`
- `resources/views/public/storefront/themes/business_corporate/hero.blade.php`:
  - Line 11-13: Hardcoded blue styling `bg-blue-600/10 text-blue-700 border-blue-600/20` and hardcoded text `'Corporate Enterprise & B2B Solutions'`.
  - Line 17: Headline hardcodes `{{ $landingPage->headline ?: $business->name . ' - Mitra Solusi & Suplai Bisnis Terpercaya' }}`.
  - Line 21: Subheadline hardcodes `'Menyediakan produk dan layanan berstandar industri dengan jaminan mutu, kontrak kerja transparan, dan keandalan operasional jangka panjang.'`.
  - Line 28: Primary CTA has hardcoded blue background `bg-blue-600 hover:bg-blue-700` and text `'Katalog Solusi & Produk'`.
  - Line 37-38: Secondary CTA has hardcoded blue icon `text-blue-600` and text `'Hubungi Account Representative'`.
  - Lines 44-57: Hardcoded B2B trust metrics: `"100%"`, `"Entitas Legal & Resmi"`, `"SLA 99.9%"`, `"Komitmen Layanan"`, `"B2B & Retail"`, `"Faktur Pajak Tersedia"`.
  - Line 64: Hardcoded office building skyscraper Unsplash photo `'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=1000&auto=format&fit=crop&q=80'`.
  - Lines 71-77: Floating card has hardcoded strings `"Operasional Aktif &amp; Terverifikasi"`, `"COOCA Verified"` and hardcoded `bg-blue-600` dot.
- `resources/views/public/storefront/themes/business_corporate/features_banner.blade.php`:
  - Lines 10, 23, 36, 49: Icon containers hardcode `bg-blue-600/10 text-blue-600 dark:text-blue-400`.
  - Lines 14, 16, 27, 29, 40, 42, 54, 57: Hardcoded B2B copy ("SLA Garansi Resmi", "Faktur Pajak PPN", "Quotation PO Resmi", "Dedicated PIC"), ignoring `$landingPage->getUniversalValues()`.
- `resources/views/public/storefront/themes/business_corporate/category_chips.blade.php`:
  - Line 9: Non-standard title `{{ __('storefront.divisi_produk_solusi') }}` without fallback to `$landingPage->services_title ?: __('storefront.kategori_pilihan')`.
  - Line 10: Hardcoded B2B helper text `"Pilih kategori untuk memfilter spesifikasi pengadaan"`.
  - Line 14: Hardcoded text `"Seluruh Katalog"` instead of `__('storefront.view_all')`.
  - Lines 13, 22: Hardcoded blue styling `text-blue-600 dark:text-blue-400`, `hover:border-blue-500`.
  - Line 22: Touch target is `py-2` without `min-h-[44px]`.
  - Missing "Semua Produk" chip and active category styling.

---

## 2. Logic Chain

1. **Premise:** Blueprint §1 & §2 establishes that themes are visual design archetypes (shells), and must never leak hardcoded sector-specific content, specific industry photos, or rigid hex colors.
2. **From Observation §1 (Unsplash Photos):** `universal_storefront`, `modern_umkm`, and `business_corporate` contain static Unsplash URLs that force food or corporate skyscraper photos onto merchants in other industries (e.g. fashion, clinic, automotive).
   - **Deduction:** Replacing these static Unsplash URLs with the dynamic Brand Bento Graphic Showcase card (when `$heroImage` is null) fulfills Don't #3 and provides a clean, neutral brand identity presentation for any merchant.
3. **From Observation §1 (Features Banners):** In all 4 themes, `features_banner.blade.php` is written as 4 hardcoded static cards that ignore the merchant's CMS configuration (`$landingPage->values`).
   - **Deduction:** Milestone 1 introduced `$landingPage->getUniversalValues()`, which guarantees 4 normalized items with `icon`, `title`, and `desc`. Refactoring `features_banner.blade.php` to loop over `$landingPage->getUniversalValues()` makes the banner 100% dynamic, eliminates hardcoded sector copy, and preserves each archetype's distinctive container grid.
4. **From Observation §1 (Category Chips):** In all 4 themes, `category_chips.blade.php` lacks the default "Semua Produk" chip, lacks active state styling (`theme-btn-primary shadow-xs`), hardcodes subtitle copy, and in `business_corporate` violates the 44px touch target requirement (`py-2`).
   - **Deduction:** Adapting all 4 files to Universal Blade Contract §3.3 enforces thumb-zone compliance (`min-h-[44px]`), provides unified catalog filter navigation, and supports category title customization via `$landingPage->services_title`.
5. **From Observation §1 (Color Utility Classes):** `modern_umkm`, `online_store`, and `business_corporate` use hardcoded Tailwind color utilities (`emerald-600`, `indigo-600`, `blue-600`) that prevent merchant-selected theme accents (`var(--theme-primary)`) from taking effect.
   - **Deduction:** Swapping hardcoded color utilities for `.theme-btn-primary`, `.theme-badge`, and `.theme-link` ensures full synchronization with the merchant's CMS theme color customization.

---

## 3. Caveats

- **No Caveats.**
- The investigation was purely read-only; no source files have been modified.
- Full proposed code implementations for all 12 target files have been produced and verified against the Blueprint in `analysis.md`.
- All translation keys referenced (`storefront.hero.*`, `storefront.badges.*`, `storefront.features.*`, `storefront.kategori_pilihan`, `storefront.semua_produk`, `storefront.pilih_kategori_filter`, `storefront.view_all`) have been verified to exist in both `lang/id/storefront.php` and `lang/en/storefront.php`.

---

## 4. Conclusion

All 12 files across Themes 1 to 4 have been audited and mapped to Universal Blade Contracts §3.1, §3.2, and §3.3.
- All hardcoded sector copy, static Unsplash image fallbacks, and rigid color utility classes have been cataloged with line numbers and exact before/after code snippets in `analysis.md`.
- The architectural identity and design taste of each theme archetype (`clean_bento` for Themes 1-3, `institutional_trust` for Theme 4) are strictly preserved while decoupling them from specific business sectors.
- Milestone 2A implementation for Themes 1-4 can proceed immediately with zero ambiguity.

---

## 5. Verification Method

To independently verify this audit:
1. **Inspect Target Files:**
   - Review `resources/views/public/storefront/themes/universal_storefront/{hero,features_banner,category_chips}.blade.php`
   - Review `resources/views/public/storefront/themes/modern_umkm/{hero,features_banner,category_chips}.blade.php`
   - Review `resources/views/public/storefront/themes/online_store/{hero,features_banner,category_chips}.blade.php`
   - Review `resources/views/public/storefront/themes/business_corporate/{hero,features_banner,category_chips}.blade.php`
   - Verify the line references and hardcoded text/image citations recorded in Section 1.
2. **Translation Key Integrity:**
   - Inspect `lang/id/storefront.php` lines 24-54, 82-84, 1322, 1609-1612.
   - Inspect `lang/en/storefront.php` lines 24-54, 82-84, 1322, 1609-1612.
3. **Model Helper Verification:**
   - Inspect `app/Models/BusinessLandingPage.php` lines 226-276 to confirm `getUniversalValues()` returns 4 items with `icon`, `title`, and `desc`.
4. **Post-Implementation Test Command:**
   - Once the implementer applies the proposed changes, verify syntax and contracts using:
     ```powershell
     php -l resources/views/public/storefront/themes/universal_storefront/hero.blade.php
     php -l resources/views/public/storefront/themes/universal_storefront/features_banner.blade.php
     php -l resources/views/public/storefront/themes/universal_storefront/category_chips.blade.php
     php -l resources/views/public/storefront/themes/modern_umkm/hero.blade.php
     php -l resources/views/public/storefront/themes/modern_umkm/features_banner.blade.php
     php -l resources/views/public/storefront/themes/modern_umkm/category_chips.blade.php
     php -l resources/views/public/storefront/themes/online_store/hero.blade.php
     php -l resources/views/public/storefront/themes/online_store/features_banner.blade.php
     php -l resources/views/public/storefront/themes/online_store/category_chips.blade.php
     php -l resources/views/public/storefront/themes/business_corporate/hero.blade.php
     php -l resources/views/public/storefront/themes/business_corporate/features_banner.blade.php
     php -l resources/views/public/storefront/themes/business_corporate/category_chips.blade.php
     php artisan test --filter=Storefront
     ```
