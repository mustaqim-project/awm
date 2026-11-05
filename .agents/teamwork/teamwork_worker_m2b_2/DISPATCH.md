## 2026-11-05T02:19:15Z
Complete Milestone 2B (Batch 2 Storefront Themes Refactoring):
1. In Batch 2 (11 F&B & Retail Themes), the first 7 themes (bakery_cake, fnb_bakery, fnb_cafe, fnb_catering, fnb_fastfood, fnb_resto, retail_bookstore) have already been refactored.
2. Refactor the remaining 4 themes to the Universal Blade Contracts:
   - Theme 8: retail_building
     * resources/views/public/storefront/themes/retail_building/hero.blade.php
     * resources/views/public/storefront/themes/retail_building/features_banner.blade.php
     * resources/views/public/storefront/themes/retail_building/category_chips.blade.php
   - Theme 9: retail_electronics
     * resources/views/public/storefront/themes/retail_electronics/hero.blade.php
     * resources/views/public/storefront/themes/retail_electronics/features_banner.blade.php
     * resources/views/public/storefront/themes/retail_electronics/category_chips.blade.php
   - Theme 10: retail_florist
     * resources/views/public/storefront/themes/retail_florist/hero.blade.php
     * resources/views/public/storefront/themes/retail_florist/features_banner.blade.php
     * resources/views/public/storefront/themes/retail_florist/category_chips.blade.php
   - Theme 11: retail_grocery
     * resources/views/public/storefront/themes/retail_grocery/hero.blade.php
     * resources/views/public/storefront/themes/retail_grocery/features_banner.blade.php
     * resources/views/public/storefront/themes/retail_grocery/category_chips.blade.php

COMPLIANCE REQUIREMENTS:
- Universal Blade Contracts:
  * Hero (§3.1): Fallbacks for headline, subheadline, eyebrow badge, dynamic CTAs ($landingPage->cta_primary_text/url, secondary WhatsApp), dynamic badges ($landingPage->getUniversalValues()), and Brand Bento Graphic Showcase when hero image is null. ZERO unsplash.com URLs!
  * Features Banner (§3.2): Dynamically iterates 4 pillars from $landingPage->getUniversalValues().
  * Category Chips (§3.3): Dynamic title ($landingPage->services_title ?: __('storefront.kategori_pilihan')), subtitle, 'Semua Produk' chip with layout-grid icon, take(12) categories, active category check (request('category') == $cat->id), touch target min-h-[44px].
- Preserve each theme's unique visual design soul (clean_bento vs warm_artisan, fonts, color tokens).
- Maintain file ownership strictly to the 12 files above plus the test file.

VERIFICATION REQUIREMENTS:
1. Syntax check: Run `php -l` on all 33 files across all 11 themes in Batch 2 (bakery_cake, fnb_bakery, fnb_cafe, fnb_catering, fnb_fastfood, fnb_resto, retail_bookstore, retail_building, retail_electronics, retail_florist, retail_grocery).
2. Unsplash check: Run grep/Select-String across all 11 themes in Batch 2 to confirm 0 unsplash occurrences.
3. Automated Test: Create `tests/Feature/StorefrontBatch2EmpiricalChallengeTest.php` testing all 11 Batch 2 themes for:
   - Rendering without error
   - 0 unsplash photos
   - Dynamic 4 pillars from getUniversalValues()
   - Dynamic badges in hero
   - Category chips contract
   Execute `php artisan test --filter=StorefrontBatch2EmpiricalChallengeTest` and document passing output.
