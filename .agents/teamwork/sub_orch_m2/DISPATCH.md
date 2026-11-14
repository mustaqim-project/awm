## 2026-11-14T00:44:18Z
You are sub_orch_m2, a sub-orchestrator agent running the Project Pattern for Milestone 2.
Your working directory is: C:\laragon\www\cooca_core
Your dedicated state folder is: C:\laragon\www\cooca_core\.agents\teamwork\sub_orch_m2\
Your parent orchestrator is: 4e686b8a-2e28-4b82-9b39-8b7a237105e3

You MUST read the original request and upstream documents before starting work:
- ORIGINAL_REQUEST.md: C:\laragon\www\cooca_core\.agents\teamwork\ORIGINAL_REQUEST.md (contains verbatim user requirements and additional quality requirements from 2026-11-14T00:13:11Z)
- Global PROJECT.md: C:\laragon\www\cooca_core\.agents\teamwork\orchestrator_1\PROJECT.md
- Audit Inventory: C:\laragon\www\cooca_core\.agents\teamwork\orchestrator_1\AUDIT_INVENTORY.md
- Gate Status: C:\laragon\www\cooca_core\.agents\teamwork\orchestrator_1\GATE_STATUS.md

Your Assigned Scope: Milestone 2 (M2)  Marketing Pages, Solutions & Commercial Views Optimization
Audited paths and files (Items 10–30 in AUDIT_INVENTORY.md):
- Standalone Marketing Pages:
  `resources/views/landing.blade.php`
  `resources/views/public/business_landing.blade.php`
  `resources/views/public/about.blade.php`
  `resources/views/public/demo.blade.php`
  `resources/views/public/pricing.blade.php`
  `resources/views/public/privacy.blade.php`
  `resources/views/public/security.blade.php`
  `resources/views/public/sitemap.blade.php`
  `resources/views/public/support.blade.php`
  `resources/views/public/terms.blade.php`
- Commercial Landings:
  `resources/views/public/landing_commercial/kasir_android.blade.php`
  `resources/views/public/landing_commercial/kasir_gratis.blade.php`
  `resources/views/public/landing_commercial/kasir_offline.blade.php`
  `resources/views/public/discovery/index.blade.php`
- Solutions Templates:
  `resources/views/public/solutions/fnb.blade.php`
  `resources/views/public/solutions/laundry.blade.php`
  `resources/views/public/solutions/manufacturing.blade.php`
  `resources/views/public/solutions/retail.blade.php`
  `resources/views/public/solutions/services.blade.php`
  `resources/views/public/solutions/show.blade.php`
  `resources/views/public/solutions/workshop.blade.php`

Your Workflow and Mandatory Quality Requirements:
1. Create your `BRIEFING.md`, `progress.md`, `SCOPE.md`, and `GATE_STATUS.md` in your state folder.
2. Execute the iteration loop: Explorer -> Worker -> Reviewers (2) -> Challengers (2) -> Auditor (`teamwork_auditor`) -> Gate evaluation.
   - MANDATORY INTEGRITY WARNING for Worker:
     DO NOT CHEAT. All implementations must be genuine. DO NOT hardcode test results, create dummy/facade implementations, or circumvent the intended task. A teamwork_auditor will independently verify your work. Integrity violations WILL be detected and your work WILL be rejected.
3. Core High-Impact Optimizations for M2:
   - Remove runtime Tailwind Play CDN from `resources/views/public/business_landing.blade.php` (line 149) and fix line 8624 `__('storefront.tracking')` array TypeError -> `{{ __('storefront.tracking.title') }}`.
   - Eliminate heading hierarchy skips (H1 -> H3) in `kasir_android`, `kasir_offline`, `about`, `privacy`, `terms`.
   - Add explicit dimensions (`width`, `height`, `decoding="async"`, `fetchpriority="high"` on LCP hero image, modern aspect ratios) on hero images, store badges, and logos to achieve CLS <= 0.1.
   - Add missing canonical URLs, OpenGraph metadata, and Schema.org JSON-LD structured data on `/pricing`, `/privacy`, `/terms`, `/security`, `/sitemap`.
   - Harden Light and Dark theme contrast on badges, cards, comparison tables, and buttons across all 21 scoped views.
4. Mandatory Quality & Testing Enforcements (per ORIGINAL_REQUEST.md):
   - Measure actual frontend performance using Lighthouse or equivalent tools before-and-after (LCP <= 2.5s, TBT <= 200ms, CLS <= 0.1, Speed Index, overall score).
   - Test across all 8 mandatory viewports: 320px, 360px, 390px, 414px, 768px, 1024px, 1440px, and 1920px.
   - Test in both Light Theme and Dark Theme, and in Indonesian and English.
   - Zero horizontal overflow across all viewports.
   - PHP syntax validation: `php -l` 0 errors on all modified files.
   - Asset compilation: `npm run build` 0 errors.
   - Regression prevention: Run PHPUnit test suites and E2E test suites without failure.
   - Forensic Auditor verdict must be CLEAN (binary veto).
5. When Gate 2 passes: Write your comprehensive handoff report to:
   `C:\laragon\www\cooca_core\.agents\teamwork\sub_orch_m2\handoff.md`
   And send a completion message to the parent orchestrator (`4e686b8a-2e28-4b82-9b39-8b7a237105e3`).
