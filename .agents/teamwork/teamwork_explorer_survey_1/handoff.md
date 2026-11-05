# Handoff Report: Storefront Themes & CMS Landing Page Backend Architecture (Phase 1 Blueprint)

## 1. Observation

Direct code inspections and filesystem queries yielded the following verified facts:

### 1.1 File Locations & Namespaces
- `BusinessLandingPage.php`: Located at `c:\laragon\www\cooca_core\app\Models\BusinessLandingPage.php`, namespace `App\Models`. Total 362 lines.
- `StorefrontThemeService.php`: Located at `c:\laragon\www\cooca_core\app\Domain\Storefront\StorefrontThemeService.php` (not `app/Services/StorefrontThemeService.php`), namespace `App\Domain\Storefront`. Total 1,718 lines.
- Storefront Controller: Located at `c:\laragon\www\cooca_core\app\Http\Controllers\Web\Storefront\PublicStorefrontController.php` (not `app/Http/Controllers/Storefront/StorefrontController.php`), namespace `App\Http\Controllers\Web\Storefront`. Total 676 lines.
- CMS Controller: Located at `c:\laragon\www\cooca_core\app\Http\Controllers\Web\BusinessLandingPageWebController.php`. Total 492 lines.
- Translation Files: Located at `c:\laragon\www\cooca_core\lang\id\storefront.php` and `c:\laragon\www\cooca_core\lang\en\storefront.php`. Total 1,714 lines each.

### 1.2 `BusinessLandingPage.php` State
- Columns `section_visibility` and `values` are in `$fillable` (lines 40, 49) and cast to `'array'` (lines 89, 97).
- Existing helper methods:
  - `getActivePages(): array` (lines 108–121)
  - `isPageActive(string $page): bool` (lines 126–130)
  - `getNavLabel(string $page, string $default): string` (lines 135–139)
  - `getThemePreset(): string` (lines 144–163)
  - `getWhatsAppUrl(?string $serviceName = null): string` (lines 173–192)
  - `getNormalizedOperationalHours(): array` (lines 198–252)
  - `isPopupActive(): bool` (lines 333–348)
- **Verbatim Finding**: Method `isSectionVisible(string $section)` is **NOT defined** anywhere in `BusinessLandingPage.php` (or elsewhere in `app/Models/`).
- In `resources/views/app/landing_page/edit.blade.php` (lines 2575–2592), `$sectionVisibilityDefaults` initializes:
  ```php
  $sectionVisibilityDefaults = array_merge(
      [
          'hero' => true,
          'about' => true,
          'products' => true,
          'services' => true,
          'gallery' => true,
          'testimonials' => true,
          'faq' => true,
          'contact' => true,
          'footer' => true,
          'footer_brand' => true,
          'footer_navigation' => true,
          'footer_services' => true,
          'footer_contact' => true,
      ],
      $landingPage->section_visibility ?? [],
  );
  ```
  Neither `'features_banner'`, `'category_chips'`, nor `'stats'` are present in these defaults or in the toggle UI.

### 1.3 `StorefrontThemeService.php` State
- Defines 42 themes in `private const THEMES` (lines 18–900).
- Defines maps: `TEMPLATE_CODE_MAP` (lines 907–1121), `CATEGORY_MAP` (lines 1128–1174), `ALIASES` (lines 1181–1226), `CURATED_FONTS` (lines 1233–1252), `RECOMMENDATIONS` (lines 1259–1554).
- Methods implemented:
  - `getAllThemes(): array` (lines 1561–1570)
  - `hasTheme(string $presetKey): bool` (lines 1575–1580)
  - `getCuratedFonts(): array` (lines 1587–1590)
  - `getRecommendation(string $presetKey): array` (lines 1598–1604)
  - `getTheme(string $presetKey): array` (lines 1612–1643)
  - `detectThemeKeyForBusiness(?Business $business): string` (lines 1649–1679)
  - `resolveTheme(BusinessLandingPage $landingPage): array` (lines 1687–1716)
- **Verbatim Finding**: Neither `getThemeArchetype(string $themeKey)` nor `getUniversalFallback(string $themeKey)` exists in `StorefrontThemeService.php`.

### 1.4 `lang/id/storefront.php` & `lang/en/storefront.php` State
- `Select-String` search for `'badges'` and `'features'` returned 0 results.
- Keys such as `storefront.badges.quality_guaranteed`, `storefront.badges.secure_transaction`, `storefront.badges.fast_service`, `storefront.features.official_store`, `storefront.features.easy_payment`, `storefront.features.secure_delivery`, `storefront.features.customer_care` do not exist.
- Search for `pilih_kategori_filter` returned 0 results.
- `storefront.kategori_pilihan` exists at line 1562 (`'kategori_pilihan' => 'Kategori Pilihan'`).
- `storefront.semua_produk` exists at line 1565 (`'semua_produk' => 'Semua Produk'`).
- `storefront.view_all` exists at line 1275 (`'view_all' => 'Lihat Semua'`).

### 1.5 Theme Partial Inspection (Sector Hardcoding)
- In `resources/views/public/storefront/themes/service_workshop/hero.blade.php`:
  - Lines 26, 30, 34 hardcode: "100% Sparepart Original", "Scanner Komputer Injeksi", "Booking Tanpa Antre".
  - Line 43 hardcodes: `'Cari Sparepart & Servis'`.
  - Line 52 hardcodes: `'Konsultasi Keluhan Kendaraan'`.
  - Line 61 hardcodes: `'https://images.unsplash.com/photo-1486006920555-c77dce18193b?w=1000&auto=format&fit=crop&q=80'`.
  - Lines 70, 72 hardcode: "Pit Stop Siap Melayani", "Mekanik Handal".
- In `resources/views/public/storefront/themes/service_workshop/features_banner.blade.php`:
  - Lines 11, 21, 31, 41 hardcode: "Onderdil 100% Asli", "Scanner Diagnosis OBD-II", "Mekanik Bersertifikat", "Garansi Pengerjaan".
- In `resources/views/public/storefront/home.blade.php`:
  - Lines 50, 61, 70: `hero`, `features_banner`, and `category_chips` are included without checking `isSectionVisible()`.

---

## 2. Logic Chain

1. **Premise 1 (Section Visibility)**: The blueprint requires that each section on the storefront homepage can be toggled by the business owner via CMS (`$landingPage->isSectionVisible(...)`).
   - *Observation*: `isSectionVisible()` does not exist in `BusinessLandingPage.php`.
   - *Deduction*: Calling `$landingPage->isSectionVisible('features_banner')` currently throws an `Error: Call to undefined method App\Models\BusinessLandingPage::isSectionVisible()`. Therefore, implementing `isSectionVisible(string $section): bool` on `BusinessLandingPage.php` with robust defaults is a mandatory prerequisite before refactoring `home.blade.php`.

2. **Premise 2 (Universal Blade Contracts)**: In Section 3 of `STANDARISASI_DAN_IMPLEMENTASI_TEMA_UNIVERSAL.md`, the contracts for `hero.blade.php` and `features_banner.blade.php` use:
   - `__('storefront.badges.quality_guaranteed')`
   - `__('storefront.badges.secure_transaction')`
   - `__('storefront.badges.fast_service')`
   - `__('storefront.features.official_store')`
   - `__('storefront.features.official_desc')`
   - `__('storefront.features.easy_payment')`
   - `__('storefront.features.payment_desc')`
   - `__('storefront.features.secure_delivery')`
   - `__('storefront.features.delivery_desc')`
   - `__('storefront.features.customer_care')`
   - `__('storefront.features.care_desc')`
   - `__('storefront.pilih_kategori_filter')`
   - *Observation*: None of these keys exist in `lang/id/storefront.php` or `lang/en/storefront.php`.
   - *Deduction*: If Blade files are refactored before updating translation files, Laravel will render raw translation key strings (e.g. `storefront.badges.quality_guaranteed`) on public storefront pages. Hence, Task 1.1 must be executed prior to or alongside theme refactoring.

3. **Premise 3 (Archetype Classification & Universal Fallbacks)**: Section 4 and Section 5 of the blueprint establish 6 visual design archetypes (`clean_bento`, `warm_artisan`, `luxe_editorial`, `dark_carbon`, `playful_fresh`, `institutional_trust`) for the 42 themes.
   - *Observation*: `StorefrontThemeService.php` contains the full 42 theme definitions but lacks archetype classifications and lacks `getThemeArchetype(string $themeKey)` and `getUniversalFallback(string $themeKey)`.
   - *Deduction*: Adding archetype mappings and fallback resolution to `StorefrontThemeService.php` centralizes business logic, allowing both the CMS editor (Sub-Card 5 tabs) and the public storefront to reliably categorize themes and retrieve universal fallback copy.

---

## 3. Caveats

- **Scope boundary**: This investigation is strictly read-only. No code changes have been applied in this turn.
- **Frontend CMS Alignment**: While Phase 1 focuses on backend models, services, and language files, `resources/views/app/landing_page/edit.blade.php` will subsequently need updates in Phase 4 to expose UI controls for `features_banner` and `category_chips`.
- **Database Schema**: No migration is needed. The `business_landing_pages` table already has JSON columns `section_visibility` and `values`, which are cast to arrays.

---

## 4. Conclusion

Phase 1 of the Universal Storefront Blueprint is well-defined and requires three discrete, non-breaking modifications:

1. **`lang/id/storefront.php` & `lang/en/storefront.php`**:
   Add nested dictionary entries for `badges`, `features`, `catalog.pilih_kategori_filter`, and `empty`.
2. **`app/Models/BusinessLandingPage.php`**:
   Add `isSectionVisible(string $section): bool` with default fallbacks covering all standard storefront sections (`hero`, `features_banner`, `category_chips`, `products`, `services`, `gallery`, `testimonials`, `faq`, `stats`, `about`, `contact`, `footer`, and footer sub-sections). Additionally add a helper `getUniversalValues(): array` to normalize `$this->values` across `desc`/`description` variants.
3. **`app/Domain/Storefront/StorefrontThemeService.php`**:
   Add archetype mapping constants (`ARCHETYPES`, `THEME_ARCHETYPE_MAP`) and public methods `getThemeArchetype(string $themeKey): array`, `getAllArchetypes(): array`, and `getUniversalFallback(string $themeKey): array`.

These three changes will satisfy all backend prerequisites for Phase 2 (refactoring the 42 themes) and Phase 3 (enforcing visibility in `home.blade.php`).

---

## 5. Verification Method

Once implemented by the designated builder agent, the changes can be independently verified via the following steps:

1. **PHP Syntax & Linter Check**:
   ```powershell
   php -l app/Models/BusinessLandingPage.php
   php -l app/Domain/Storefront/StorefrontThemeService.php
   php -l lang/id/storefront.php
   php -l lang/en/storefront.php
   ```
2. **Unit / Tinker Verification**:
   Execute via `php artisan tinker --execute="..."`:
   ```php
   $lp = new \App\Models\BusinessLandingPage();
   echo $lp->isSectionVisible('features_banner') ? 'OK: Visible' : 'FAIL';
   $lp->section_visibility = ['features_banner' => false];
   echo ! $lp->isSectionVisible('features_banner') ? 'OK: Hidden' : 'FAIL';
   
   $service = app(\App\Domain\Storefront\StorefrontThemeService::class);
   $arch = $service->getThemeArchetype('service_workshop');
   echo ($arch['key'] === 'dark_carbon') ? 'OK: Archetype' : 'FAIL';
   
   echo __('storefront.badges.quality_guaranteed');
   echo __('storefront.features.official_store');
   ```
3. **Automated Test Suite**:
   ```powershell
   php artisan test --filter=Storefront
   ```
