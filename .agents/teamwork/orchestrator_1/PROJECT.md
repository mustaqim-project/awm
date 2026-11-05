# Project: Universal Storefront Themes & CMS Integration

## Architecture
- **CMS Model & Database**: `App\Models\BusinessLandingPage`, fields: `values` (JSON 4 pillars / badges), `section_visibility` (JSON), `theme_color`, `font_family`, `headline`, `subheadline`, `announcement_badge`, `hero_image_url`, `about_image_url`, `cta_primary_text`, `cta_primary_url`, `cta_secondary_text`, `cta_secondary_url`.
- **Backend Service**: `App\Domain\Storefront\StorefrontThemeService` managing 42 theme definitions, 6 design archetypes, presets, curated fonts, and universal fallbacks.
- **Storefront View Layer**:
  - Main layout: `resources/views/public/storefront/layouts/app.blade.php`
  - Home orchestrator: `resources/views/public/storefront/home.blade.php`
  - 42 Theme directories in `resources/views/public/storefront/themes/{theme_key}/` containing standard files:
    - `hero.blade.php`
    - `features_banner.blade.php`
    - `category_chips.blade.php`
    - `featured_products.blade.php`
    - `catalog_grid.blade.php`
    - `product_card.blade.php`
- **CMS Management**: `resources/views/app/landing_page/edit.blade.php` with 6 design archetype filter tabs, theme preview cards, and section visibility toggles.
  - Controller: `App\Http\Controllers\Web\BusinessLandingPageWebController`
- **Public Controller**: `App\Http\Controllers\Web\Storefront\PublicStorefrontController`
- **Language / i18n**: `lang/id/storefront.php` and `lang/en/storefront.php`.

## Feature Inventory
| # | Feature | Description | Milestone | Source |
|---|---------|-------------|-----------|--------|
| 1 | Universal i18n Translations | Standard dictionary strings for 4 pillars, badges, empty states in ID & EN | M1 | Blueprint §3, §5 (DONE) |
| 2 | Model Visibility Helper | `isSectionVisible(string $section)` and `getUniversalValues()` in `BusinessLandingPage.php` | M1 | Blueprint §5, §6 (DONE) |
| 3 | StorefrontThemeService Enhancements | 6-archetype categorization, `getThemeArchetype()`, and `getUniversalFallback()` | M1 | Blueprint §5 (DONE) |
| 4 | Batch 1 Themes Refactoring (10) | `universal_storefront`, `modern_umkm`, `online_store`, `business_corporate`, `coffee_beverage`, `agriculture_farm`, `retail_fashion`, `service_workshop`, `food_restaurant`, `health_beauty` | M2A | Blueprint §4, §5 (DONE) |
| 5 | Batch 2 Themes Refactoring (11) | `bakery_cake`, `fnb_bakery`, `fnb_cafe`, `fnb_catering`, `fnb_fastfood`, `fnb_resto`, `retail_bookstore`, `retail_building`, `retail_electronics`, `retail_florist`, `retail_grocery` | M2B | Blueprint §4, §5 |
| 6 | Batch 3 Themes Refactoring (11) | `booking_appointment`, `event_wedding`, `hospitality_accommodation`, `service_agency`, `service_beauty`, `service_business`, `service_carwash`, `service_fitness`, `service_laundry`, `service_petshop`, `service_printing` | M2C | Blueprint §4, §5 |
| 7 | Batch 4 Themes Refactoring (10) | `construction_property`, `education_courses`, `logistics_transportation`, `manufacturing_factory`, `personal_brand`, `portfolio_creative`, `product_showcase`, `professional_services`, `retail_pharmacy`, `wholesale_distributor` | M2D | Blueprint §4, §5 |
| 8 | Home & Layout Visibility Enforcement | Enforce `isSectionVisible()` on all sections in `home.blade.php` and dynamic tokens in `app.blade.php` | M3 | Blueprint §3, §5 |
| 9 | CMS Cockpit UI Enhancement | 6 Design Archetype tabs, educational universal badges, visibility toggles in `edit.blade.php` | M4 | Blueprint §5 |
| 10 | QA, Linting & E2E Verification | Blade syntax linting (`php -l`), automated feature tests (`php artisan test`), multi-device preview testing | M5 | Blueprint §5 |

## Milestones
| # | Name | Scope | Dependencies | Status |
|---|------|-------|-------------|--------|
| M0 | Survey & Scope Mapping | Investigate existing codebase, theme structure, services, models, and tests | None | DONE |
| M1 | Backend & Service Normalization | Add i18n keys, `isSectionVisible()` helper, and service archetypes | M0 | DONE |
| M2A | Batch 1 Themes Refactoring | Standardize first 10 core & universal themes | M1 | DONE |
| M2B | Batch 2 Themes Refactoring | Standardize 11 F&B & retail themes | M2A | IN_PROGRESS |
| M2C | Batch 3 Themes Refactoring | Standardize 11 service & automotive themes | M2B | PLANNED |
| M2D | Batch 4 Themes Refactoring | Standardize 10 industry & corporate themes | M2C | PLANNED |
| M3 | Home & Layout Visibility Enforcement | Refactor `home.blade.php` & `app.blade.php` | M2A, M2B, M2C, M2D | PLANNED |
| M4 | CMS Cockpit Enhancement | Refactor `resources/views/app/landing_page/edit.blade.php` | M1, M3 | PLANNED |
| M5 | QA, Linting & E2E Verification | Run full blade lint, automated test suite, regression checks | M3, M4 | PLANNED |

## Interface Contracts
### `BusinessLandingPage` ↔ Blade Views
- `isSectionVisible(string $section, bool $default = true): bool`
  - Input: `'hero'`, `'features_banner'`, `'category_chips'`, `'products'`, `'services'`, `'gallery'`, `'testimonials'`, `'faq'`, `'stats'`, `'about'`, `'contact'`, `'footer'`
  - Logic: checks `section_visibility[$section] ?? $default` with legacy alias resolution.
- `getUniversalValues(): array`
  - Strictly 4 items with both `'desc'` and `'description'` set, fallback to `storefront.features.*`.

### `StorefrontThemeService` ↔ Theme Selector & Home
- `ARCHETYPES`: array of 6 archetypes:
  1. `clean_bento` (Clean Bento & Modern Tech - 8 themes)
  2. `warm_artisan` (Earthy & Warm Artisan - 7 themes)
  3. `luxe_editorial` (High-End Luxe & Editorial - 6 themes)
  4. `dark_carbon` (Dark Carbon & High Impact - 7 themes)
  5. `playful_fresh` (Playful, Fresh & Appetite - 7 themes)
  6. `institutional_trust` (Institutional & Trust - 7 themes)
- `getThemeArchetype(string $themeKey): array`
- `getAllArchetypes(): array`
- `getUniversalFallback(string $themeKey): array`

## Code Layout
- `app/Models/BusinessLandingPage.php`: Model with visibility and fallback helpers.
- `app/Domain/Storefront/StorefrontThemeService.php`: Theme catalog and archetype mappings.
- `lang/id/storefront.php`: Indonesian translations for universal fallbacks.
- `lang/en/storefront.php`: English translations for universal fallbacks.
- `resources/views/public/storefront/`: Public customer-facing storefront views.
  - `layouts/app.blade.php`: Base layout with CSS variables and fonts.
  - `home.blade.php`: Home view composing theme sections.
  - `themes/{theme_key}/*.blade.php`: 42 theme subdirectories.
- `resources/views/app/landing_page/`: Merchant CMS admin views.
  - `edit.blade.php`: Merchant CMS cockpit.
- `tests/Feature/StorefrontUniversalContractTest.php`: Automated contract verification suite.
- `tests/Unit/StorefrontEmpiricalTranslationChallengeTest.php`: Translation empirical test suite.
- `tests/Feature/StorefrontBatch1EmpiricalChallengeTest.php`: Batch 1 Themes 1-5 challenge suite.
- `tests/Feature/StorefrontThemes6To10EmpiricalChallengeTest.php`: Batch 1 Themes 6-10 challenge suite.
