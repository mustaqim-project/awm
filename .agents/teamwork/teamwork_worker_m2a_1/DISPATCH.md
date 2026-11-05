## 2026-11-05T00:58:34Z
You are the Worker for Milestone 2A: Batch 1 Themes Refactoring (10 Themes).
Read `c:\laragon\www\cooca_core\.agents\teamwork\ORIGINAL_REQUEST.md`, `C:\Users\Ulfah Ramadhani\.gemini\antigravity\brain\5699ea93-e022-47d8-82fe-e47b3a657b52\STANDARISASI_DAN_IMPLEMENTASI_TEMA_UNIVERSAL.md`, and `c:\laragon\www\cooca_core\.agents\teamwork\orchestrator_1\PROJECT.md`.

Read the detailed handoff and analysis reports from the 3 M2A Explorers:
- Explorer 1 (Themes 1 to 4: `universal_storefront`, `modern_umkm`, `online_store`, `business_corporate`): `c:\laragon\www\cooca_core\.agents\teamwork\teamwork_explorer_m2a_1\handoff.md` and `analysis.md`
- Explorer 2 (Themes 5 to 7: `coffee_beverage`, `agriculture_farm`, `retail_fashion`): `c:\laragon\www\cooca_core\.agents\teamwork\teamwork_explorer_m2a_2\handoff.md` and `analysis.md`
- Explorer 3 (Themes 8 to 10: `service_workshop`, `food_restaurant`, `health_beauty`): `c:\laragon\www\cooca_core\.agents\teamwork\teamwork_explorer_m2a_3\handoff.md` and `analysis.md`

Your exclusive write ownership covers ONLY the following 30 files across the 10 themes in `resources/views/public/storefront/themes/`:
1. `universal_storefront`: `hero.blade.php`, `features_banner.blade.php`, `category_chips.blade.php`
2. `modern_umkm`: `hero.blade.php`, `features_banner.blade.php`, `category_chips.blade.php`
3. `online_store`: `hero.blade.php`, `features_banner.blade.php`, `category_chips.blade.php`
4. `business_corporate`: `hero.blade.php`, `features_banner.blade.php`, `category_chips.blade.php`
5. `coffee_beverage`: `hero.blade.php`, `features_banner.blade.php`, `category_chips.blade.php`
6. `agriculture_farm`: `hero.blade.php`, `features_banner.blade.php`, `category_chips.blade.php`
7. `retail_fashion`: `hero.blade.php`, `features_banner.blade.php`, `category_chips.blade.php`
8. `service_workshop`: `hero.blade.php`, `features_banner.blade.php`, `category_chips.blade.php`
9. `food_restaurant`: `hero.blade.php`, `features_banner.blade.php`, `category_chips.blade.php`
10. `health_beauty`: `hero.blade.php`, `features_banner.blade.php`, `category_chips.blade.php`

DO NOT modify any other files.

TASKS TO EXECUTE:
Apply the exact adaptations formulated by Explorers 1, 2, and 3:
1. `hero.blade.php` (All 10 themes):
   - Implement Universal Blade Contract §3.1.
   - Use dynamic fallback hierarchy: `$landingPage` CMS -> `$business` profile -> universal i18n (`storefront.hero.*`).
   - Dynamically bind badges from `$landingPage->getUniversalValues()`, falling back to `__('storefront.badges.*')`.
   - Remove all hardcoded sector copy (e.g. "Tune-up Mesin", "Petik Segar Subuh", "Jahitan Rapi", "Specialty Roastery", "80+ Cupping", "Bongkar Pasang", "100% Produk Lokal", "Corporate Enterprise").
   - Replace hardcoded Unsplash image fallbacks with the Brand Bento Graphic Showcase card (when `$heroImage` is null).
   - Replace hardcoded hex colors with dynamic theme tokens (`var(--theme-primary)` etc.).
   - Preserve each theme's unique typography and visual design archetype layout!
2. `features_banner.blade.php` (All 10 themes):
   - Implement Universal Blade Contract §3.2.
   - Loop over `$landingPage->getUniversalValues()`, falling back to `__('storefront.features.*')`.
   - Remove all static sector-specific cards and hardcoded color utilities.
3. `category_chips.blade.php` (All 10 themes):
   - Implement Universal Blade Contract §3.3.
   - Include standard "Semua Produk" chip with `layout-grid` icon and active state indicator (`!request()->filled('category')`).
   - Standardize category chip styling with `min-h-[44px]` (thumb-zone compliant) and active highlight.
   - Use `$landingPage->services_title ?: __('storefront.kategori_pilihan')` and `__('storefront.pilih_kategori_filter')`.

VERIFICATION TO RUN:
1. Run syntax check `php -l` on all 30 modified Blade files.
2. Run test verification: `php artisan test --filter=StorefrontThemeCustomizationTest`.
3. Check for zero remaining hardcoded sector strings or static Unsplash URLs in these 10 themes.

MANDATORY INTEGRITY WARNING:
DO NOT CHEAT. All implementations must be genuine. DO NOT hardcode test results, create dummy/facade implementations, or circumvent the intended task. A teamwork_auditor will independently verify your work. Integrity violations WILL be detected and your work WILL be rejected.

Working directory: `c:\laragon\www\cooca_core\.agents\teamwork\teamwork_worker_m2a_1\`
Write your change details in `changes.md` and your final report in `handoff.md`.
Send a message to parent when completed.
