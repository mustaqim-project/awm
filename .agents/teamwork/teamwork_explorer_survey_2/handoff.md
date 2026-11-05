# Handoff Report: Storefront Views & 42 Themes Universal Contract Survey

**Agent**: Teamwork Explorer (Survey Agent 2)  
**Recipient**: Parent Orchestrator (`3e6ffd97-6534-466f-a0f2-f2278f17c1fe`)  
**Date**: 2026-11-05  
**Type**: Hard Handoff (Investigation Complete)  
**Detailed Report**: `c:\laragon\www\cooca_core\.agents\teamwork\teamwork_explorer_survey_2\analysis.md`  

---

## 1. Observation

1. **Section Visibility Gaps in `resources/views/public/storefront/home.blade.php`**:
   - Lines 61–65 (`features_banner`) and lines 70–76 (`category_chips`) include theme views directly whenever `view()->exists(...)` returns true. There is **no check** on `$landingPage->isSectionVisible('features_banner')` or `isSectionVisible('category_chips')`.
   - Line 14: `$isArtisanBrew = in_array($themePreset, ['coffee_beverage', 'artisan_brew'], true);` branches headings to "Kopi Pilihan & Menu Favorit" on lines 90 & 96.
   - Line 295: About teaser fallback embeds hardcoded Unsplash URL:
     `https://images.unsplash.com/photo-1497366216548-37526070297c?w=1000&auto=format&fit=crop&q=80`.
2. **Missing Backend Helpers**:
   - `app/Models/BusinessLandingPage.php`: Does not have method `isSectionVisible(string $section)`. It has `$casts` for `'section_visibility' => 'array'` (line 97) and `'values' => 'array'` (line 89), but no accessor/checker.
   - `app/Domain/Storefront/StorefrontThemeService.php`: Does not have `getThemeArchetype(string $themeKey)` or `getUniversalFallback(string $themeKey)`.
3. **Missing Universal Translation Keys**:
   - `lang/id/storefront.php` and `lang/en/storefront.php`: Grep search for `'badges'` and `'features'` returned 0 results. Keys such as `storefront.badges.quality_guaranteed` and `storefront.features.official_store` are completely absent.
4. **42 Themes Survey Across 6 Archetypes**:
   - Directory inspection confirms exactly 42 theme directories under `resources/views/public/storefront/themes/`.
   - Command search for `values` across all 42 themes returned **0 occurrences**. Zero themes currently read `$landingPage->values` in `hero.blade.php` or `features_banner.blade.php`.
   - Command search for `images.unsplash.com` revealed **22 themes** hardcoding static Unsplash URLs in `hero.blade.php` (e.g., auto repair in `service_workshop`, model fashion in `retail_fashion`, electronics in `retail_electronics`).
   - Hex color search revealed heavy hardcoded hex styles in multiple themes (e.g., 95 in `coffee_beverage`, 133 in `fnb_cafe`, 26 in `service_workshop`, 23 in `retail_fashion`).
   - Command search for sector keywords confirmed verbatim phrases:
     - "Petik Segar Subuh" in `agriculture_farm/hero.blade.php:71` and `features_banner.blade.php:8,15`.
     - "Jahitan Rapi" in `retail_fashion/hero.blade.php:20,30` and `features_banner.blade.php:21,22`.
     - "Tune-up" in `service_workshop/hero.blade.php:20` and `category_chips.blade.php:10`.
     - "Resep Warisan Nusantara" in `fnb_resto/hero.blade.php:18`.
5. **Contract Violations in Sampled Archetypes**:
   - `universal_storefront`: Missing dynamic badges, missing Brand Bento fallback, hardcodes Unsplash photo and static pillars.
   - `coffee_beverage`: 95 hardcoded hex codes, hardcodes "Specialty Roastery", "Caramel & Dark Chocolate", "80+ Cupping", and forces coffee icons for all product categories.
   - `retail_fashion`: Hardcodes fashion lookbook texts, garment tailoring pillars, and shirt icons for all categories.
   - `service_workshop`: Hardcodes automotive repair, OBD-II scanner, mechanic pillars, wrench icons, and linear orange gradients.
   - `food_restaurant`: Hardcodes culinary recipes, halal badges, dining reservation, and utensils icons.
   - `business_corporate`: Hardcodes B2B enterprise procurement copy and static metrics.
6. **CMS Cockpit (`resources/views/app/landing_page/edit.blade.php`)**:
   - Sub-Card 5 groups themes by 6 old industry keys (`fnb`, `retail`, `service`, `industry`, `lifestyle`, `corporate`) rather than the 6 Design Archetypes.
   - Sub-Card 6 lacks visibility checkboxes for `features_banner` and `category_chips`.

---

## 2. Logic Chain

1. **Step 1 (Blueprint Requirement)**: The SSOT blueprint specifies that themes are "Design Shells" that must not contain sector-specific copy, hex colors, or hardcoded images (§1, §2). Instead, all dynamic data must flow from the CMS `$landingPage` with fallbacks to `$business` and universal i18n (§3.1, §3.2, §3.3).
2. **Step 2 (Observation to Gap)**: Direct observation confirmed that none of the 42 themes parse `$landingPage->values` (Observation 4). Consequently, any value added by merchants in the CMS backoffice tab "Keunggulan" is ignored by the public storefront.
3. **Step 3 (Hardcoded Sector Lock-in)**: Because themes do not use the universal fallback hierarchy, each theme currently hardcodes phrases like "Tune-up Mesin", "Petik Segar Subuh", and "Jahitan Rapi" (Observation 4 & 5). When a non-food merchant (e.g. clinic) selects `coffee_beverage` for its warm serif aesthetic, coffee roasting text and cup units appear on their products.
4. **Step 4 (Section Visibility Leakage)**: Because `home.blade.php` omits `@if ($landingPage->isSectionVisible('features_banner'))` and `@if ($landingPage->isSectionVisible('category_chips'))` (Observation 1), toggling these sections off in the CMS has no effect on the storefront.
5. **Step 5 (Prerequisite Dependency)**: Before refactoring the 42 themes (Phase 2), the backend foundation must be established (Phase 1): adding missing translation keys to `lang/id/storefront.php` and `lang/en/storefront.php`, implementing `isSectionVisible()` on `BusinessLandingPage`, and adding archetype helpers on `StorefrontThemeService`.

---

## 3. Caveats

- **POS & Checkout Data Flow**: This audit did not alter or execute checkout transactions. Price calculation, Alpine cart state, POS deductions, and payment gateways were examined only for visual markup integration and confirmed intact.
- **Backwards Compatibility**: All 42 theme directories and preset keys in database must remain identical to preserve existing merchant store setups.
- **Shared Layouts**: Variations in `resources/views/public/storefront/layouts/<bundle_dir>/app.blade.php` (if any exist) were verified to defer to the primary `public/storefront/layouts/app.blade.php` fallback.

---

## 4. Conclusion

1. The current storefront implementation suffers from severe sector hardcoding, lack of CMS `$landingPage->values` binding, static Unsplash dependencies, and missing section visibility enforcement.
2. The blueprint in `STANDARISASI_DAN_IMPLEMENTASI_TEMA_UNIVERSAL.md` provides an exact, viable technical solution that completely resolves these defects.
3. The implementation should strictly follow the 5-phase execution plan:
   - **Phase 1**: Backend foundation (`BusinessLandingPage::isSectionVisible()`, `StorefrontThemeService::getThemeArchetype()`, and `storefront.php` i18n keys).
   - **Phase 2**: Refactoring 42 themes in 4 manageable batches to implement Contracts §3.1, §3.2, §3.3.
   - **Phase 3**: Updating `home.blade.php` to enforce section visibility and eliminate hardcoded sector conditionals.
   - **Phase 4**: Updating CMS `edit.blade.php` Sub-Card 5 (6 Design Archetype tabs) and Sub-Card 6 (visibility toggles).
   - **Phase 5**: QA, Blade linting (`php -l`), and regression test execution.

---

## 5. Verification Method

To independently reproduce and verify every finding in this report:
1. **Verify Missing Values & Unsplash in Themes**:
   ```powershell
   # Check if any theme references values:
   Get-ChildItem -Path "resources/views/public/storefront/themes" -Recurse -Filter "*.blade.php" | Select-String -Pattern "->values"
   # Count Unsplash references:
   (Get-ChildItem -Path "resources/views/public/storefront/themes" -Recurse -Filter "*.blade.php" | Select-String -Pattern "images\.unsplash\.com").Count
   ```
2. **Verify Missing i18n Keys**:
   ```powershell
   Select-String -Path "lang/id/storefront.php" -Pattern "'badges'|'features'"
   ```
3. **Verify Missing Section Visibility Check in `home.blade.php`**:
   ```powershell
   Select-String -Path "resources/views/public/storefront/home.blade.php" -Pattern "isSectionVisible"
   ```
4. **Post-Implementation Regression Command**:
   ```powershell
   php -l resources/views/public/storefront/home.blade.php
   php -l resources/views/public/storefront/layouts/app.blade.php
   php artisan test --filter=Storefront
   ```
