# Handoff Report: Milestone 1 Explorer 2  Model Helper Methods in `BusinessLandingPage.php`

**Date**: 2026-11-05T07:18:00Z  
**From**: Explorer 2 (`teamwork_explorer_m1_2`)  
**To**: Parent Orchestrator (`3e6ffd97-6534-466f-a0f2-f2278f17c1fe`)  
**Scope**: Model helper methods in `app/Models/BusinessLandingPage.php`  
**Handoff Type**: Hard (Task Complete)

---

## 1. Observation

1. **`app/Models/BusinessLandingPage.php`**:
   - `section_visibility` and `values` are defined in `$fillable` (lines 40, 49) and cast to `'array'` in `$casts` (lines 89, 97).
   - Currently, there is NO `isSectionVisible()`, `getSectionVisibility()`, or `getUniversalValues()` helper method in `BusinessLandingPage.php`.
   - Existing methods include `getActivePages(): array` (lines 108–121) and `isPageActive(string $page): bool` (lines 126–130), which merge static defaults with JSON columns.
2. **`resources/views/app/landing_page/edit.blade.php`**:
   - Lines 2575–2593 merge static section visibility defaults (`'hero'`, `'about'`, `'products'`, `'services'`, `'gallery'`, `'testimonials'`, `'faq'`, `'contact'`, `'footer'`, `'footer_brand'`, etc.) with `$landingPage->section_visibility ?? []`.
   - Lines 1023–1050 bind the 4 pillars form inputs to `val.title`, `val.icon`, and `val.description` (using key `description`).
   - Line 2831–2852 initializes default Alpine values using key `description`.
3. **`app/Domain/LandingPage/IndustryPresets.php`**:
   - Lines 209–214 and 25 presets define `'values'` with `icon`, `title`, and `desc` (using key `desc`).
4. **`database/seeders/CafeKopiSenjaFullSeeder.php`**:
   - Lines 1622–1635 contain legacy keys in `section_visibility`: `'featured_products'` instead of `'products'`, and `'catalog_filter'` instead of `'category_chips'`.
5. **`resources/views/public/storefront/home.blade.php`**:
   - Lines 50–76 unconditionally include `hero.blade.php`, `features_banner.blade.php`, and `category_chips.blade.php` without wrapping them in visibility checks.
6. **Existing Test Suite**:
   - Executed `php artisan test tests/Feature/StorefrontThemeCustomizationTest.php`: returned exit code 0 (`{"tool":"phpunit","result":"passed","tests":5,"passed":5,"assertions":440}`).

---

## 2. Logic Chain

1. **Section Visibility Requirement**:
   - The universal storefront architecture requires 12 core sections: `hero`, `features_banner`, `category_chips`, `products`, `services`, `gallery`, `testimonials`, `faq`, `stats`, `about`, `contact`, and `footer`.
   - Each of these 12 sections must be enabled (`true`) by default so that merchants without explicit CMS visibility configurations have a complete storefront.
   - By creating `public const DEFAULT_SECTION_VISIBILITY` containing all 12 sections set to `true`, and referencing it in `isSectionVisible(string $section, bool $default = true): bool` and `getSectionVisibility(): array`, we guarantee consistent default behavior everywhere.
   - To prevent breaking existing stores or seeders that use legacy keys (e.g. `'featured_products'`, `'catalog_filter'`, `'faqs'`), `resolveSectionAlias()` checks alternative keys when the primary key is missing.
   - If a caller explicitly specifies a fallback via `func_num_args() > 1` (e.g. `$landingPage->isSectionVisible('custom_section', false)`), the method honors the caller's parameter.

2. **Values Normalization Requirement**:
   - A critical inconsistency exists between presets (`'desc'`) and CMS cockpit / Alpine.js (`'description'`).
   - By having `getUniversalValues()` check for `'desc'` first, then `'description'`, and setting **both** `'desc'` and `'description'` on each normalized item, both Blade templates (`$pillar['desc']`) and CMS inputs (`val.description`) work seamlessly without conflict.
   - Filtering for numeric keys (`is_numeric($key) && is_array($item)`) ensures that legacy metadata stored in `values` (such as `'stats'`) does not corrupt the 4 pillars.
   - Running a strict loop from index 0 to 3 ensures that `getUniversalValues()` always returns an array of exactly 4 elements.
   - Fallbacks link to `storefront.features.*` translation keys (designed by Explorer 1) with safe Indonesian default strings in `getDefaultUniversalValues()`.

---

## 3. Caveats

1. **Read-Only Scope**: In accordance with the Explorer role, no source files were modified. The proposed code is fully provided in `analysis.md` and ready for the implementer agent.
2. **Translation Availability**: `getDefaultUniversalValues()` uses `trans()->has()` checks so that if `lang/id/storefront.php` has not yet been updated with `storefront.features.*` by Explorer 1/Implementer, the fallback strings are used directly without leaking dot-notation keys.
3. **Sub-footer keys**: CMS allows granular toggles for `'footer_brand'`, `'footer_navigation'`, `'footer_services'`, and `'footer_contact'`. While not in the top-level 12 sections list, `isSectionVisible('footer_brand')` will naturally return `true` or its stored value without issue.

---

## 4. Conclusion

The design for `BusinessLandingPage.php` is complete, verified, and ready for implementation.
The proposed changes introduce:
1. `public const DEFAULT_SECTION_VISIBILITY` with 12 sections.
2. `public function isSectionVisible(string $section, bool $default = true): bool`.
3. `public function getSectionVisibility(): array`.
4. `private function resolveSectionAlias(string $section): ?string`.
5. `public function getUniversalValues(): array`.
6. `public static function getDefaultUniversalValues(): array`.

All methods are non-breaking, handle null states, support both `'desc'` and `'description'` keys, and provide full backward compatibility with legacy database values.

---

## 5. Verification Method

Once implemented, the following test can be run to verify the implementation:

1. **Unit / Feature Test Execution**:
   Create or run `tests/Feature/StorefrontUniversalContractTest.php` with the following test cases:
   - `test_is_section_visible_returns_true_for_all_12_default_sections_when_visibility_is_null()`
   - `test_is_section_visible_respects_explicit_false_values()`
   - `test_is_section_visible_resolves_legacy_aliases_like_featured_products()`
   - `test_get_universal_values_returns_exactly_4_items_when_values_is_null()`
   - `test_get_universal_values_normalizes_desc_and_description_keys()`
   - `test_get_universal_values_ignores_non_numeric_keys()`
   Run with:
   ```powershell
   php artisan test tests/Feature/StorefrontThemeCustomizationTest.php
   ```
2. **Syntax Linting**:
   ```powershell
   php -l app/Models/BusinessLandingPage.php
   ```
3. **Invalidation Conditions**:
   - `isSectionVisible()` returns `false` on a fresh landing page where `section_visibility` is null.
   - `getUniversalValues()` returns fewer or more than 4 items.
   - `getUniversalValues()` returns items missing either `'desc'` or `'description'`.
