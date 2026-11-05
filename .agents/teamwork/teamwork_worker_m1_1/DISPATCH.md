## 2026-11-05T00:22:49Z
You are the Worker for Milestone 1: Backend & Service Normalization.
Read `c:\laragon\www\cooca_core\.agents\teamwork\ORIGINAL_REQUEST.md`, `C:\Users\Ulfah Ramadhani\.gemini\antigravity\brain\5699ea93-e022-47d8-82fe-e47b3a657b52\STANDARISASI_DAN_IMPLEMENTASI_TEMA_UNIVERSAL.md`, and `c:\laragon\www\cooca_core\.agents\teamwork\orchestrator_1\PROJECT.md`.

Read the detailed handoff and analysis reports from the 3 Explorers:
- Explorer 1 (Translations): `c:\laragon\www\cooca_core\.agents\teamwork\teamwork_explorer_m1_1\handoff.md` and `analysis.md`
- Explorer 2 (Model helpers): `c:\laragon\www\cooca_core\.agents\teamwork\teamwork_explorer_m1_2\handoff.md` and `analysis.md`
- Explorer 3 (Theme Service): `c:\laragon\www\cooca_core\.agents\teamwork\teamwork_explorer_m1_3\handoff.md` and `analysis.md`

Your exclusive write ownership covers ONLY these 4 files:
1. `lang/id/storefront.php`
2. `lang/en/storefront.php`
3. `app/Models/BusinessLandingPage.php`
4. `app/Domain/Storefront/StorefrontThemeService.php`

DO NOT modify any other files.

TASKS TO EXECUTE:
1. Update `lang/id/storefront.php` and `lang/en/storefront.php`:
   - Add root-level `'badges'` array and `'features'` array with Indonesian and English keys as specified in Explorer 1's report.
   - Add missing keys to `'catalog'` (`pilih_kategori_filter`, `semua_produk`, `kategori_pilihan`) and root-level alias `'pilih_kategori_filter'`.
   - Add `'empty'` dictionary supporting both flat and nested dot notations for catalog, products, and cart.
2. Update `app/Models/BusinessLandingPage.php`:
   - Add `public const DEFAULT_SECTION_VISIBILITY` (12 sections defaulting to true).
   - Implement `isSectionVisible(string $section, bool $default = true): bool` with legacy alias resolution (`featured_products` -> `products`, `catalog_filter` -> `category_chips`, `faqs` -> `faq`).
   - Implement `getSectionVisibility(): array`.
   - Implement `getUniversalValues(): array` normalizing both `desc` and `description` keys, filtering numeric keys, ensuring strictly 4 items.
   - Implement `public static function getDefaultUniversalValues(): array`.
3. Update `app/Domain/Storefront/StorefrontThemeService.php`:
   - Add `public const ARCHETYPES` mapping all 42 themes across the 6 Design Archetypes (`clean_bento` 8, `warm_artisan` 7, `luxe_editorial` 6, `dark_carbon` 7, `playful_fresh` 7, `institutional_trust` 7).
   - Add `public const THEME_ARCHETYPE_MAP` for O(1) reverse lookup.
   - Implement `getThemeArchetype(string $themeKey): array`.
   - Implement `getAllArchetypes(): array`.
   - Implement `getUniversalFallback(string $themeKey): array`.
   - Enrich `getAllThemes()`, `getTheme()`, and `resolveTheme()` with `'archetype'` and `'archetype_name'`.

VERIFICATION TO RUN:
1. Run syntax check `php -l` on all 4 modified files.
2. Run test verification:
   `php artisan test --filter=StorefrontThemeCustomizationTest`
3. Test key resolutions and helper outputs using `php -r` or `php artisan tinker --execute="..."`.

MANDATORY INTEGRITY WARNING:
DO NOT CHEAT. All implementations must be genuine. DO NOT hardcode test results, create dummy/facade implementations, or circumvent the intended task. A teamwork_auditor will independently verify your work. Integrity violations WILL be detected and your work WILL be rejected.

Working directory: `c:\laragon\www\cooca_core\.agents\teamwork\teamwork_worker_m1_1\`
Write your change details in `changes.md` and your final report in `handoff.md`.
Send a message to parent when completed.
