# Test Infrastructure Specification: COOCA Public Marketing Frontend

## 1. Test Runner Command & Architecture
- **Framework**: PHPUnit 11.x / Laravel Testing Suite (PHP 8.3+)
- **Test Runner Command**:
  ```bash
  php artisan test tests/Feature/PublicMarketingFrontendE2ETest.php
  # Direct PHPUnit execution alternative:
  php vendor/phpunit/phpunit/phpunit tests/Feature/PublicMarketingFrontendE2ETest.php
  ```
- **Configuration**: `phpunit.xml` with `APP_ENV=testing`, in-memory SQLite database (`:memory:`), `CACHE_STORE=array`, and `SESSION_DRIVER=array`.
- **Target Suite Location**: `tests/Feature/PublicMarketingFrontendE2ETest.php`
- **Isolation Principle**: Each test case leverages `Illuminate\Foundation\Testing\RefreshDatabase` to ensure zero state cross-contamination, predictable seed states, and independent execution.

---

## 2. 4-Tier Test Methodology

### Tier 1: Feature Coverage (Sanity & Component Presence)
Verifies that all 22 primary standalone pages, commercial landings, and vertical solution pages return HTTP 200 without unhandled server exceptions, and that critical layout partials are rendered:
- **Scope**:
  1. Main Homepage (`/`)
  2. About Us (`/about`)
  3. Live Demo (`/demo`)
  4. Pricing (`/pricing`)
  5. Privacy Policy (`/privacy`)
  6. Security & OWASP (`/security`)
  7. HTML Sitemap (`/sitemap`)
  8. Customer Support (`/support`)
  9. Terms & Conditions (`/terms`)
  10. Business Landing Page (`/b/{slug}`)
  11. Kasir Gratis Commercial Landing (`/aplikasi-kasir-gratis`)
  12. Kasir Android Commercial Landing (`/aplikasi-kasir-android`)
  13. Kasir Offline Commercial Landing (`/aplikasi-kasir-offline`)
  14. Discovery Directory (`/jelajah`)
  15. Public Marketplace (`/marketplace`)
  16. Contact & Inquiry (`/kontak`)
  17. Solution: Food & Beverage (`/solutions/fnb`)
  18. Solution: Retail & Minimarket (`/solutions/retail`)
  19. Solution: Workshop & Bengkel (`/solutions/workshop`)
  20. Solution: Laundry Service (`/solutions/laundry`)
  21. Solution: Manufacturing (`/solutions/manufacturing`)
  22. Solution: Professional Services (`/solutions/services`)
- **Assertions**:
  - HTTP 200 response on all pages.
  - Presence of core namespace markers: `cooca-public`, `data-theme-system="cooca-public"`.
  - SEO metadata integration: meta charset `utf-8`, title tag populated, canonical URL link element present, viewport configuration.

### Tier 2: Boundary & Corner Cases (Structural Invariants & Safety Gates)
Validates strict structural invariants, fault-tolerant configurations, and layout integrity under boundary constraints:
- **Heading Hierarchy Integrity**:
  - Exactly one `<h1>` tag per page (avoiding multiple competing H1 tags or missing H1 tags).
  - Non-empty heading contents.
- **Localization & Missing Translation Keys Gate**:
  - Scanning rendered HTML for uncompiled translation key strings (e.g. `public_erp.*`, `public_marketing.*`, `common.*`, `public_bos.*`, `public_marketplace.*`).
  - Guaranteeing zero raw string leakage across Indonesian (`id`) and English (`en`) query locales.
- **Mobile Responsive Safeguards**:
  - Validation of `<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">`.
  - Absence of hardcoded wide elements causing horizontal scroll (e.g. fixed widths > 100vw or improper overflow).
- **Asynchronous & Deferral Integrity**:
  - Chatbot widget and Live Chat widget deferred loading (presence of `x-cloak`, non-blocking script loading).
  - Proper deferral of client scripts (Tailwind CDN / precompiled assets, Alpine.js, Lucide icons).

### Tier 3: Cross-Feature Combinations & State Transitions
Tests interactions between simultaneous independent features and stateful mutations:
- **Theme Switching & Contrast Integrity**:
  - Dark mode (`.dark` class and `data-theme="dark"` attribute) configuration in CSS variables.
  - Verification that `--apple-bg`, `--apple-card`, `--apple-text`, and `--apple-card-border` are properly defined for both light (`#F5F5F7` / `#1D1D1F`) and dark (`#000000` / `#F5F5F7`) themes.
  - No-flash theme bootstrap script verified in layout `<head>`.
- **Floating Element Coexistence**:
  - Coexistence of Floating WhatsApp Button (`floating-wa-fab`), Live Chat FAB (`floating-live-chat-fab`), and Apple Floating Dock Bottom Bar (`fixed bottom-0`).
  - Safe-area insets (`env(safe-area-inset-bottom, 0px)`) and z-index hierarchy (`z-40`, `z-50`) without clipping or click trapping.
- **Bilingual & Hreflang State Transitions**:
  - Hreflang alternates for `id`, `en`, and `x-default` dynamically populated.
  - Language toggle switches preserving active URL parameters while switching between Indonesian and English.

### Tier 4: Real-World User Journeys (End-to-End Simulation)
Simulates end-to-end visitor and customer conversion journeys across multiple interconnected features:
- **Journey 1: Discovery & Lead Capture Journey**:
  - Visitor lands on Homepage (`/`), browses Solutions F&B (`/solutions/fnb`), opens Business Calculator (`/kalkulator/hpp`), navigates to Free Templates (`/template-pembukuan-gratis`), and submits a lead capture form.
  - Asserts database storage of captured lead (`template_leads` table) with proper CSRF/data validation.
- **Journey 2: Commercial Acquisition & Pricing Evaluation Journey**:
  - Visitor searches and arrives at Kasir Gratis (`/aplikasi-kasir-gratis`), reviews free-tier limits (synced dynamically from `EntitlementService`), compares tiers on `/pricing`, and verifies call-to-action routing to registration.
- **Journey 3: Tenant Security & Zero Context Leakage Journey**:
  - Logged-in tenant business owner browses public marketing pages.
  - Asserts that private tenant inventory, store names, or customized demo badges never leak into public marketing views.
