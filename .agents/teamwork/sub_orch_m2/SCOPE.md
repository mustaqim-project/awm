# Scope: Milestone 2 (M2) — Marketing Pages, Solutions & Commercial Views Optimization

## Architecture
- Framework: Laravel 11 Blade, Tailwind CSS (via Vite bundle), Alpine.js, Lucide Icons
- Scope: 21 Public Marketing Blade views
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

## Feature Inventory (M2 Scope)
| # | Feature | Target Files | Requirements | Source |
|---|---------|--------------|--------------|--------|
| 1 | Runtime Play CDN Elimination & TypeError Fix | `resources/views/public/business_landing.blade.php` | Remove Play CDN on line 149; fix line 8624 `__('storefront.tracking')` array to string TypeError `{{ __('storefront.tracking.title') }}` | AUDIT_INVENTORY Item 11 |
| 2 | Heading Hierarchy Skip Elimination | `kasir_android`, `kasir_offline`, `about`, `privacy`, `terms`, solution bottom CTAs | Eliminate H1 -> H3 skips; enforce logical H1 -> H2 -> H3 semantic hierarchy | AUDIT_INVENTORY Items 12, 15, 19, 20, 22, 24-30 |
| 3 | Explicit Media Dimensions for CLS Reduction | All 21 scoped views (hero images, app badges, logos, solution graphics) | Add explicit `width`, `height`, `decoding="async"`, and `fetchpriority="high"` on LCP hero images; keep CLS <= 0.1 | AUDIT_INVENTORY Items 10, 20-22 |
| 4 | SEO Canonicals, Meta & Schema.org JSON-LD | `/pricing`, `/privacy`, `/terms`, `/security`, `/sitemap`, `/about`, `/demo`, commercial landings | Add canonical URLs, OpenGraph/Twitter cards, and Schema.org JSON-LD structured data | AUDIT_INVENTORY Items 12, 14-17 |
| 5 | Theme Contrast & Dark Mode Hardening | All 21 scoped views | Harden Light and Dark theme contrast on badges, cards, comparison tables, and buttons | AUDIT_INVENTORY Items 10-30 |
| 6 | Touch Target & Responsive Overflow Remediation | All 21 scoped views | Touch targets >= 44x44px; zero horizontal overflow across 8 viewports (320px–1920px) | AUDIT_INVENTORY Items 10-30 |
| 7 | Performance Benchmarking & Cross-Viewport Quality | All 21 scoped views | Measure frontend performance (LCP <= 2.5s, TBT <= 200ms, CLS <= 0.1, Speed Index); test Light/Dark themes and ID/EN locales | ORIGINAL_REQUEST Follow-up |

## Milestones / Iteration Workflow
| Iteration | Stage | Description | Status |
|-----------|-------|-------------|--------|
| Iteration 1 | Exploration | 3 parallel Explorers: Standalone, Commercial, Solutions | IN_PROGRESS |
| Iteration 1 | Implementation | Worker executes all high-impact fixes | PLANNED |
| Iteration 1 | Review & Verification | 2 Reviewers + 2 Challengers + 1 Forensic Auditor | PLANNED |
| Iteration 1 | Gate 2 | Comprehensive Gate evaluation | PLANNED |

## Code Layout
- Marketing Views: `resources/views/landing.blade.php`, `resources/views/public/*.blade.php`
- Commercial Landings: `resources/views/public/landing_commercial/*.blade.php`, `resources/views/public/discovery/index.blade.php`
- Solutions: `resources/views/public/solutions/*.blade.php`
