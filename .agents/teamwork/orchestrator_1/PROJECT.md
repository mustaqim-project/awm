# Project: COOCA Public Marketing Frontend Audit & High-Impact Optimization

## Architecture
- **Framework**: Laravel 11.x, Blade Templates, Tailwind CSS, Alpine.js, Lucide Icons, Vite Asset Pipeline.
- **Master Layout**: `resources/views/layouts/public_marketing.blade.php` (extended by 20+ public marketing views and 10 feature directories).
- **Core Partials & Widgets**:
  - `resources/views/layouts/partials/help_chatbot_widget.blade.php`
  - `resources/views/layouts/partials/typography.blade.php`
  - `resources/views/public/partials/live_chat_widget.blade.php`
  - `resources/views/public/partials/cookie_consent_banner.blade.php`
  - `resources/views/public/partials/seo.blade.php`
  - `resources/views/public/partials/subpage_layout.blade.php`
  - `resources/views/public/partials/tracking_pixels.blade.php`
- **Public Marketing Scope**:
  - Standalone Pages: `landing.blade.php`, `about`, `demo`, `pricing`, `privacy`, `security`, `sitemap`, `support`, `terms`, `business_landing`.
  - Solutions: `fnb`, `laundry`, `manufacturing`, `retail`, `services`, `show`, `workshop`.
  - Commercial Landings: `kasir_android`, `kasir_gratis`, `kasir_offline`, `discovery/index`.
  - Feature Directories (43 Blade files + 111 articles): `blog`, `business-operating-system`, `calculators`, `contact`, `content-automation`, `marketplace`, `omnichannel`, `omnichannel-erp`, `resources`, `templates`.
- **Localization**: 10 bilingual translation modules in `lang/id/` and `lang/en/` (`public_blog`, `public_bos`, `public_calculators`, `public_contact`, `public_content`, `public_erp`, `public_marketplace`, `public_omnichannel`, `public_resources`, `public_templates`, plus `common.php`).
- **Core Web Vitals Targets**: LCP <= 2.5s, TBT <= 200ms, CLS <= 0.1, INP Good.

## Feature Inventory
| # | Feature | Description | Milestone | Source |
|---|---------|-------------|-----------|--------|
| 1 | Master Layout Asset Pipeline & CDN Elimination | Eliminate client-side runtime Tailwind Play CDN JIT compilation; connect to precompiled production Vite CSS bundle / optimized asset pipeline; defer non-critical JS | M1 | Survey Explorer 1, 2 |
| 2 | Chatbot & Live Chat Lazy Deferral | Defer Alpine initialization, lazy mount DOM, eliminate initial background polling intervals on page load to eliminate TBT penalty | M1 | Survey Explorer 1 |
| 3 | Mobile Chat Window Viewport Trap & Floating Collision Fix | Fix 748px mobile height overflow trap (ensure header & close button accessible on mobile < 740px), safe-area insets, resolve floating dock collisions | M1 | Survey Explorer 1 |
| 4 | Mobile Theme Switch Availability & Contrast | Expose theme toggle in mobile navigation drawer/dock; ensure accessible contrast in both themes without FOUC | M1 | Survey Explorer 1 |
| 5 | Blog Show Inline CSS Optimization | Extract and optimize ~17.8 KB unminified inline `<style>` CSS in `blog/show.blade.php` | M1 | Survey Explorer 3 |
| 6 | Cookie Banner Touch Target Remediation | Upgrade cookie consent banner action buttons from 34px to WCAG compliant >=44px touch targets | M1 | Survey Explorer 1, 2 |
| 7 | Business Landing CDN & Asset Optimization | Eliminate runtime Tailwind Play CDN in `business_landing.blade.php`, pin/defer CDN scripts (`alpinejs`, `lucide`) | M2 | Survey Explorer 2 |
| 8 | Marketing Pages Heading Hierarchy Remediation | Fix heading skips (H1->H3) in `kasir_android`, `kasir_offline`, `about`, `privacy`, `terms`, and solution bottom CTAs to establish strict semantic hierarchy | M2 | Survey Explorer 2 |
| 9 | Explicit Media Dimensions for CLS Prevention | Add explicit `width`, `height`, and `decoding="async"` to hero images, logos, and catalog thumbnails across standalone marketing pages | M2 | Survey Explorer 2 |
| 10 | Mobile Navigation & Touch Target Remediation | Enforce minimum 44x44px touch targets on mobile hamburger, sitemap links, and legal navigation | M2 | Survey Explorer 2 |
| 11 | SEO Canonicals & Structured Data on Marketing Pages | Add explicit canonical URLs and Schema.org JSON-LD structured data for pricing, privacy, terms, security, and sitemap | M2 | Survey Explorer 2 |
| 12 | Marketplace Media Sizing & Layout Stability | Add explicit `width`, `height`, and `loading="lazy"` attributes on dynamic product and store cards in `marketplace` views | M3 | Survey Explorer 3 |
| 13 | Calculator Redundant DOM Icon Scan Elimination | Remove duplicate `lucide.createIcons()` scans in Alpine `x-init` across all 9 calculator views | M3 | Survey Explorer 3 |
| 14 | Business Operating System (BOS) Canonical Tags | Add explicit canonical URL sections to `overview`, `how-it-works`, and `why-cooca` | M3 | Survey Explorer 3 |
| 15 | Feature Templates Semantic & Responsive Hardening | Ensure responsive table/card layouts without horizontal overflow across all feature and tool views | M3 | Survey Explorer 3 |
| 16 | Critical Translation Key Parity & Missing Keys Fix | Fix 48 missing keys in `public_erp.php`, 2 in `public_marketplace.php`, and breadcrumb keys in `common.php` (`common.home`, `common.details`) to eliminate raw key rendering | M4 | Survey Explorer 1, 3 |
| 17 | Zero Hardcoded Text Localization | Localize hardcoded Indonesian strings across public marketing views and feature directories into `lang/id/` and `lang/en/` | M4 | Survey Explorer 2, 3 |
| 18 | Multilingual Layout Stability Verification | Ensure layout stability and prevent text truncation when switching between ID and EN | M4 | Survey Explorer 3 |
| 19 | Structured Audit Findings Report Compilation | Compile formal audit findings report across 7 dimensions (up to 10 findings per file with significant issues) specifying Priority, Category, Affected File, Location, Root Cause, Impact, Fix, Risk | M5 | Survey Explorers 1, 2, 3 |
| 20 | Build, Syntax & Automated Test Verification Gate | Verify `php -l` on all modified Blade/PHP files (0 errors), `npm run build` clean exit (0 errors), automated test suite execution without regressions | M5 | Survey Explorers 1, 2, 3 |

## Milestones
| # | Name | Scope | Dependencies | Status |
|---|------|-------|-------------|--------|
| 1 | M1: Core Layouts, Asset Pipeline & Chatbot Optimization | `resources/views/layouts/public_marketing.blade.php`, `help_chatbot_widget.blade.php`, `typography.blade.php`, `live_chat_widget.blade.php`, `cookie_consent_banner.blade.php`, `blog/show.blade.php` | none | DONE (Gate 1 Passed, 427 assertions, 0px dock collision across 8 viewports, Vite bundle connected) |
| 2 | M2: Marketing Pages, Solutions & Commercial Views Optimization | `landing.blade.php`, `business_landing.blade.php`, `kasir_*.blade.php`, `discovery/`, `solutions/*.blade.php`, `about`, `demo`, `pricing`, `privacy`, `security`, `sitemap`, `support`, `terms` | M1 | PLANNED |
| 3 | M3: Feature Directories, Deep Content & Tools Optimization | `blog/`, `business-operating-system/`, `calculators/`, `contact/`, `content-automation/`, `marketplace/`, `omnichannel/`, `omnichannel-erp/`, `resources/`, `templates/` | M1 | PLANNED |
| 4 | M4: Localization (i18n), Missing Keys Repair & SEO Hardening | `lang/id/*.php`, `lang/en/*.php`, `common.php`, and localized views across marketing & feature templates | M2, M3 | PLANNED |
| 5 | M5: Structured Audit Findings Report & Final Verification Gate | Structured audit findings report (`AUDIT_REPORT.md`), `php -l`, `npm run build`, regression test suites, forensic integrity audit | M1, M2, M3, M4 | PLANNED |

## Code Layout
- Public Layouts & Core Partials: `resources/views/layouts/`, `resources/views/layouts/partials/`, `resources/views/public/partials/`
- Public Marketing & Solutions: `resources/views/public/`, `resources/views/landing.blade.php`
- Translations: `lang/id/`, `lang/en/`
- Compiled Assets: `public/build/`, `resources/css/`, `resources/js/`
- Test Suites: `tests/Feature/`, `tests/Unit/`
