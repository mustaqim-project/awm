# Handoff Report — Milestone 2B (Batch 2 Themes 1–4 Audit)

## 1. Observation
Across the 4 themes (`bakery_cake`, `fnb_bakery`, `fnb_cafe`, `fnb_catering`), direct inspection of `hero.blade.php`, `features_banner.blade.php`, and `category_chips.blade.php` in `resources/views/public/storefront/themes/{theme_key}/` revealed the following concrete evidence:

1. **Hardcoded Unsplash Photos**:
   - `fnb_bakery/hero.blade.php:66`: `$heroImg = $landingPage->hero_image_url ?: 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=1000&auto=format&fit=crop&q=80';`
   - `fnb_cafe/hero.blade.php:97`: `$heroImg = $landingPage->hero_image_url ?: 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?w=1000&auto=format&fit=crop&q=80';`
   - `fnb_catering/hero.blade.php:62`: `$heroImg = $landingPage->hero_image_url ?: 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=1000&auto=format&fit=crop&q=80';`
   - None of these 3 themes implement an image fallback to a Brand Bento Graphic Showcase card; they unconditionally render food photos from Unsplash when merchant hero image is null.

2. **Rigid Sector Fallback Texts**:
   - `bakery_cake/hero.blade.php:19`: `🥖 Dipanggang Pagi Ini &bull; 100% Pure Butter &amp; Ragi Alami`
   - `bakery_cake/hero.blade.php:24`: `{{ $landingPage->headline ?: 'Roti Hangat & Kue Istimewa Dipanggang Setiap Pagi' }}`
   - `bakery_cake/hero.blade.php:29`: `{{ $landingPage->subheadline ?: 'Dibuat dengan ragi alami sourdough, tepung terigu pilihan, dan mentega murni...' }}`
   - `bakery_cake/hero.blade.php:68-81`: `06:00 Batch Pertama Oven`, `48 Jam Fermentasi Sourdough`, `100% Mentega Alami Murni`
   - `fnb_bakery/hero.blade.php:18`: `{{ $landingPage->headline ?: 'Roti Hangat & Kue Istimewa Setiap Hari' }}`
   - `fnb_bakery/hero.blade.php:22`: `{{ $landingPage->subheadline ?: 'Dipanggang segar setiap pagi dengan mentega Eropa murni, ragi alami...' }}`
   - `fnb_cafe/hero.blade.php:33`: `{{ $landingPage->subheadline ?: 'Nikmati ritual kopi spesialti dari biji terpilih, roasting presisi, dan penyeduhan penuh cinta...' }}`
   - `fnb_cafe/hero.blade.php:38-54`: Hardcoded coffee tasting notes (`Fruity`, `Nutty`, `Chocolaty`, `Floral`) with icons `cherry`, `nut`, `bean`, `flower-2`
   - `fnb_cafe/hero.blade.php:77-89`: Hardcoded 5-dot `Roast Level` widget with text `Medium`
   - `fnb_catering/hero.blade.php:17`: `{{ $landingPage->headline ?: 'Menu Sehat Harian & Katering Acara Terpercaya' }}`
   - `fnb_catering/hero.blade.php:21`: `{{ $landingPage->subheadline ?: 'Paket katering harian bergizi seimbang, nasi kotak bento higienis untuk kantor...' }}`

3. **Complete Violation of Features Banner Dynamic Contract (§3.2)**:
   - In ALL 4 themes (`bakery_cake`, `fnb_bakery`, `fnb_cafe`, `fnb_catering`), `features_banner.blade.php` is 100% static HTML. None of them call `$landingPage->getUniversalValues()`, completely ignoring any custom value propositions entered by merchant in CMS Tab 3 (*About / Keunggulan*).

4. **Category Chips Contract Deficiencies (§3.3)**:
   - `bakery_cake/category_chips.blade.php:14`: Hardcoded heading `Pilihan Roti Artisan, Pastry & Celebration Cake` and static icon `cake-slice` on line 43 for all categories.
   - `fnb_bakery/category_chips.blade.php`: Missing "Semua Produk" pill; missing active state highlight; default fallback icon is `cake-slice`.
   - `fnb_cafe/category_chips.blade.php`: Missing "Semua Produk" pill; missing active state highlight; default fallback icon is `coffee`; contains brittle inline JS event handlers (`onmouseover`/`onmouseout` on lines 53–54).
   - `fnb_catering/category_chips.blade.php`: Missing "Semua Produk" pill; missing active state highlight; default fallback icon is `utensils`.

---

## 2. Logic Chain
1. *Premise*: A storefront theme in COOCA is a "Design Shell" (layout, typography, geometry, color palette) and must not function as a rigid industry lock-in. Any business sector (e.g. pharmacy, electronics, consulting) can select any theme.
2. *Observation Reference (1 & 2)*: When a non-food merchant chooses `fnb_bakery`, `fnb_cafe`, or `fnb_catering`, the theme renders Unsplash photos of bread, coffee cups, or salads, and displays static text about sourdough fermentation or coffee roasting levels.
3. *Deduction*: This violates Do's and Don'ts §2 (Don't 1: Zero Hardcoded Sector Text; Don't 3: Zero Unsplash Photo Placeholders).
4. *Observation Reference (3)*: In all 4 themes, `features_banner.blade.php` does not read `$landingPage->getUniversalValues()`. When merchants customize their 4 pillars in the CMS admin cockpit, the storefront ignores their input and renders static baking/coffee/catering pillars.
5. *Deduction*: This violates Universal Blade Contract §3.2 (Features Banner) and breaks the single-source-of-truth CMS model.
6. *Observation Reference (4)*: `fnb_bakery`, `fnb_cafe`, and `fnb_catering` lack the "Semua Produk" chip, lack active category highlighting, and have rigid sector default icons (`cake-slice`, `coffee`, `utensils`), while `fnb_cafe` contains fragile inline JS mouse events.
7. *Deduction*: This violates Universal Blade Contract §3.3 and breaks mobile thumb-zone navigation.
8. *Conclusion*: All 12 view files across the 4 themes require systematic standardization according to Universal Blade Contracts §3.1, §3.2, and §3.3.

---

## 3. Caveats
- **Files Outside Scope**: `featured_products.blade.php`, `catalog_grid.blade.php`, and `product_card.blade.php` in these 4 themes were not in scope for Milestone 2B Themes 1–4, as they generally consume dynamic `$products` collection and do not hold static hero/banner copy.
- **Theme Visual Distinction**: While standardizing data contracts, each theme's unique visual character (warm oven hearth in `bakery_cake`, velvet rose elegance in `fnb_bakery`, cozy steam animations in `fnb_cafe`, and fresh emerald cards in `fnb_catering`) must be strictly preserved.
- **No Source Modifications Made**: This investigation strictly obeyed the read-only directive. No production code files have been modified.

---

## 4. Conclusion
Themes 1 to 4 of Batch 2 (`bakery_cake`, `fnb_bakery`, `fnb_cafe`, `fnb_catering`) have clear, actionable refactoring paths:
1. **Hero Sections (`hero.blade.php`)**:
   - Adopt the standardized `@php` block declaring `$heroHeadline`, `$heroSubheadline`, `$heroEyebrow`, CTAs, `$universalValues`, and `$heroImage`.
   - Purge all 3 hardcoded Unsplash photo URLs (`photo-1509440159596`, `photo-1495474472287`, `photo-1546069901`).
   - Implement elegant Brand Bento Graphic Showcase fallback cards when `$heroImage` is null, matching each theme's color personality.
   - Replace rigid sector badges with dynamic values from `$universalValues`.
2. **Features Banners (`features_banner.blade.php`)**:
   - Replace static HTML with a single `@foreach ($landingPage->getUniversalValues() as $pillar)` loop while maintaining unique card geometry and icon containers.
3. **Category Chips (`category_chips.blade.php`)**:
   - Bind headings to `$landingPage->services_title ?: __('storefront.kategori_pilihan')`.
   - Prepend the standardized "Semua Produk" chip with `layout-grid` icon and active state check.
   - Replace rigid default icons with `tag` and enforce active category highlighting.
   - Eliminate inline JS event handlers in `fnb_cafe`.

Detailed file-by-file audit, line citations, and before-and-after implementation code blocks are recorded in `analysis.md`.

---

## 5. Verification Method
To independently verify this audit and subsequent developer implementation:
1. **File Inspection**:
   - Inspect `resources/views/public/storefront/themes/{theme_key}/{hero,features_banner,category_chips}.blade.php` for `bakery_cake`, `fnb_bakery`, `fnb_cafe`, and `fnb_catering`.
   - Confirm presence of `getUniversalValues()`, absence of `images.unsplash.com`, and presence of "Semua Produk" chips.
2. **Syntax Validation**:
   - Run PHP syntax check on each view:
     ```powershell
     php -l resources/views/public/storefront/themes/bakery_cake/hero.blade.php
     php -l resources/views/public/storefront/themes/bakery_cake/features_banner.blade.php
     php -l resources/views/public/storefront/themes/bakery_cake/category_chips.blade.php
     php -l resources/views/public/storefront/themes/fnb_bakery/hero.blade.php
     php -l resources/views/public/storefront/themes/fnb_bakery/features_banner.blade.php
     php -l resources/views/public/storefront/themes/fnb_bakery/category_chips.blade.php
     php -l resources/views/public/storefront/themes/fnb_cafe/hero.blade.php
     php -l resources/views/public/storefront/themes/fnb_cafe/features_banner.blade.php
     php -l resources/views/public/storefront/themes/fnb_cafe/category_chips.blade.php
     php -l resources/views/public/storefront/themes/fnb_catering/hero.blade.php
     php -l resources/views/public/storefront/themes/fnb_catering/features_banner.blade.php
     php -l resources/views/public/storefront/themes/fnb_catering/category_chips.blade.php
     ```
3. **Automated Challenge Test**:
   - Execute an empirical test suite modeled after Batch 1:
     ```powershell
     php artisan test --filter=StorefrontBatch2EmpiricalChallengeTest
     ```
   - Assert zero occurrences of forbidden sector phrases, zero Unsplash URLs, dynamic CMS value injection, and valid category chip navigation.
