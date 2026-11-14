# Scope: Milestone 2 (M2) — Marketing Pages, Solutions & Commercial Views Optimization

## Architecture
- Framework: Laravel 11 Blade, Tailwind CSS (via Vite bundle), Alpine.js, Lucide Icons
- Scope: 21 Public Marketing Blade views + shared partial and translations
  - Standalone Marketing Pages (10 files):
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
  - Commercial Landings (4 files):
    11. `resources/views/public/landing_commercial/kasir_android.blade.php`
    12. `resources/views/public/landing_commercial/kasir_gratis.blade.php`
    13. `resources/views/public/landing_commercial/kasir_offline.blade.php`
    14. `resources/views/public/discovery/index.blade.php`
  - Solutions Templates (7 files):
    15. `resources/views/public/solutions/fnb.blade.php`
    16. `resources/views/public/solutions/laundry.blade.php`
    17. `resources/views/public/solutions/manufacturing.blade.php`
    18. `resources/views/public/solutions/retail.blade.php`
    19. `resources/views/public/solutions/services.blade.php`
    20. `resources/views/public/solutions/show.blade.php`
    21. `resources/views/public/solutions/workshop.blade.php`
  - Shared & Translations:
    22. `resources/views/layouts/public_marketing.blade.php` (hamburger touch target >= 44x44px)
    23. `lang/id/public_discovery.php` ('Toko Tidak Ditemukan' alignment)
    24. `lang/id/storefront.php` & `lang/en/storefront.php` (fallback keys defined)

## Feature Inventory (M2 Scope)
| # | Feature | Target Files | Requirements | Status |
|---|---------|--------------|--------------|--------|
| 1 | Runtime Play CDN Elimination & TypeError Fix | `resources/views/public/business_landing.blade.php` | Remove Play CDN on line 149; connect to `@vite(['resources/css/app.css', 'resources/js/app.js'])`; fix `{{ __('storefront.tracking.title') }}` | DONE |
| 2 | Heading Hierarchy Skip Elimination | `kasir_android`, `kasir_offline`, `about`, `privacy`, `terms`, solutions bottom CTAs | Eliminate H1 -> H3 skips; enforce logical H1 -> H2 -> H3 semantic hierarchy | DONE |
| 3 | Explicit Media Dimensions for CLS Reduction | All 21 scoped views | Explicit `width`, `height`, `decoding="async"`, and `fetchpriority="high"` on LCP hero images; keep CLS <= 0.1 | DONE |
| 4 | SEO Canonicals, Meta & Schema.org JSON-LD | `/pricing`, `/privacy`, `/terms`, `/security`, `/sitemap`, `/about`, `/demo`, solutions, commercial landings | Add canonical URLs, OpenGraph/Twitter cards, and Schema.org JSON-LD structured data with escaped `@@context` | DONE |
| 5 | Theme Contrast & Dark Mode Hardening | All 21 scoped views | Harden Light and Dark theme contrast on badges, cards, comparison tables, and buttons (WCAG AA >= 4.5:1 text, >= 3:1 non-text) | DONE |
| 6 | Touch Target & Responsive Overflow Remediation | All 21 scoped views | Touch targets >= 44x44px; responsive grid `grid-cols-1 sm:grid-cols-2 lg:grid-cols-4` on screens <= 390px; zero horizontal overflow | DONE |
| 7 | Bilingual Localization Stability | All 21 scoped views | 0 uncompiled raw translation keys rendered in both Indonesian ('id') and English ('en') | DONE |

## Milestones / Iteration Workflow
| Iteration | Stage | Description | Status |
|-----------|-------|-------------|--------|
| Iteration 1 | Exploration | 3 parallel Explorers: Standalone, Commercial, Solutions | DONE |
| Iteration 1 | Implementation | Worker executes all high-impact fixes | DONE |
| Iteration 1 | Review & Verification | 2 Reviewers APPROVE, Auditor CLEAN, Challenger REJECT (4 blockers) | DONE |
| Iteration 1 | Gate 2 | Gate Result: FAIL | DONE |
| Iteration 2 | Remediation | Worker resolves 4 blockers; Adversarial test passes 4/4 | DONE |
| Iteration 2 | Gate 2 Re-check | Gate Result: PASS | DONE |

## Code Layout
- Marketing Views: `resources/views/landing.blade.php`, `resources/views/public/*.blade.php`
- Commercial Landings: `resources/views/public/landing_commercial/*.blade.php`, `resources/views/public/discovery/index.blade.php`
- Solutions: `resources/views/public/solutions/*.blade.php`
