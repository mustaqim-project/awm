## 2026-11-07T09:56:35Z
You are Worker 2: UI Views Worker.
Your working directory is: c:\laragon\www\cooca_core\.agents\teamwork\worker_m2_ui\

MANDATORY INTEGRITY WARNING:
DO NOT CHEAT. All implementations must be genuine. DO NOT hardcode test results, create dummy/facade implementations, or circumvent the intended task. A teamwork_auditor will independently verify your work. Integrity violations WILL be detected and your work WILL be rejected.

MANDATORY INSTRUCTIONS:
1. You MUST read the authoritative request at: c:\laragon\www\cooca_core\.agents\teamwork\ORIGINAL_REQUEST.md
2. You MUST read the project scope document at: c:\laragon\www\cooca_core\.agents\teamwork\orchestrator\PROJECT.md
3. You MUST read the explorer findings at: c:\laragon\www\cooca_core\.agents\teamwork\explorer_ui_survey\handoff.md
4. You MUST follow the domain skill instructions at:
   - C:\Users\Ulfah Ramadhani\.gemini\config\skills\ui-simplify-layout\SKILL.md
   - C:\Users\Ulfah Ramadhani\.gemini\config\skills\cooca-agent-directive\SKILL.md

EXCLUSIVE FILE OWNERSHIP:
You own and modify ONLY the following Blade views:
- `resources/views/app/analytics/index.blade.php`
- `resources/views/app/approvals/inbox.blade.php`
- `resources/views/app/approvals/history.blade.php`
- `resources/views/app/approvals/rules.blade.php`
- `resources/views/app/billing/limits.blade.php`

TASK DETAILS:
1. `resources/views/app/analytics/index.blade.php`:
   - Eliminate Double Header: Remove internal redundant `<h1>{{ __('analytics.growth_trends') }}</h1>` and duplicate subtitle card block in view body (lines 214-229) since `$headerTitle` and `$headerSubtitle` are already rendered in the global macOS topbar. Keep period controls and location filter cleanly styled.
   - Mobile KPI Horizontal Snap Slider: In lines 316-397, the 4 KPI cards (`Total Omzet`, `Total Transaksi`, `Laba Kotor`, `Estimasi Laba Bersih`) currently stack vertically on mobile (375px-430px), consuming >750px of vertical space. Convert the container to a responsive horizontal swipe snap slider:
     `flex gap-3 overflow-x-auto snap-x snap-mandatory no-scrollbar -mx-4 px-4 pb-2 sm:grid sm:grid-cols-2 lg:grid-cols-4 sm:overflow-visible sm:mx-0 sm:px-0`
     and give each card `min-w-[260px] sm:min-w-0 snap-start flex-1` so users on mobile can smoothly swipe through KPIs without infinite vertical scroll.
   - Button Hierarchy & Ergonomics: Ensure period selector segmented buttons and print/export buttons have >= 44px tap targets on mobile (`min-h-[44px]`).
   - CRITICAL: PRESERVE all RBAC guards implemented in Milestone 1 (`@can('reports.financial')`, `@can('reports.view')`, `@canany(...)`).
2. `resources/views/app/approvals/inbox.blade.php`:
   - Eliminate Double Header: Remove the redundant internal card header with `<h1>{{ __('approvals.inbox_title') }}</h1>` (lines 11-24) to avoid duplicating the topbar title.
   - Mobile Action Button Ergonomics: Reorganize ticket action buttons ("Tolak" and "Setujui Level X") for mobile viewports (375px). Replace cramped flex buttons with a symmetric 2-column mobile grid (`grid grid-cols-2 sm:flex sm:w-auto gap-2.5 w-full sm:w-auto`) with minimum 44px tap targets (`min-h-[44px] px-4`).
   - CRITICAL: PRESERVE all RBAC guards from Milestone 1 (`@can('approvals.manage')`).
3. `resources/views/app/approvals/history.blade.php`:
   - Eliminate Double Header: Remove redundant internal `<h1>{{ __('approvals.history_title') }}</h1>` header block. Keep filters and table/card list clean.
4. `resources/views/app/approvals/rules.blade.php`:
   - Eliminate Double Header: Remove redundant internal `<h1>{{ __('approvals.rules_title') }}</h1>` header block.
   - Semantic Icon & Label Rule: Verify "Tambah Aturan" button uses `plus` icon and no duplicate `+` character in text.
   - CRITICAL: PRESERVE all RBAC guards from Milestone 1 (`@can('approvals.manage')`).
5. `resources/views/app/billing/limits.blade.php`:
   - Storage Topup Semantic Icon: In line 1400, replace generic `<i data-lucide="plus"></i>` on the storage top-up CTA with semantic `<i data-lucide="database" class="w-3.5 h-3.5"></i>` or `hard-drive`.
   - Verify layout spacing, zero visual overlap across viewports, and clean responsive display.
   - CRITICAL: PRESERVE all RBAC guards from Milestone 1 (`@can('billing.manage')`).

VERIFICATION:
- Run `php -l` on all 5 modified files (0 syntax errors).
- Run `php artisan view:clear; php artisan view:cache; php artisan view:clear` to ensure perfect Blade compilation.
- Run tests:
  `php artisan config:clear`
  `php artisan test tests/Feature/AnalyticsWebTest.php tests/Feature/ApprovalModuleAuditTest.php tests/Feature/BillingModuleAuditTest.php`
- Document all changes and test outputs in `c:\laragon\www\cooca_core\.agents\teamwork\worker_m2_ui\handoff.md`.
- Send completion message back to orchestrator.
