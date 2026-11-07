# BRIEFING — 2026-11-07T10:00:00Z

## Mission
UI Views Worker (Worker 2) for Milestone 2: Refactor and simplify Blade views in `analytics`, `approvals`, and `billing` per `/ui-simplify-layout` and `cooca-agent-directive` standards, eliminating double headers, adding horizontal swipe snap sliders for mobile KPIs, ensuring >= 44px tap targets, fixing semantic icons, and preserving all RBAC directives.

## 🔒 My Identity
- Archetype: implementer
- Roles: implementer, qa, specialist
- Working directory: c:\laragon\www\cooca_core\.agents\teamwork\worker_m2_ui\
- Original parent: 58fe913b-8bbe-4705-a472-1a575b67e3a5
- Milestone: Milestone 2 (UI Simplification & Layout Cleanliness)

## 🔒 Key Constraints
- Exclusive file ownership: ONLY modify the following 5 Blade views:
  - `resources/views/app/analytics/index.blade.php`
  - `resources/views/app/approvals/inbox.blade.php`
  - `resources/views/app/approvals/history.blade.php`
  - `resources/views/app/approvals/rules.blade.php`
  - `resources/views/app/billing/limits.blade.php`
- PRESERVE all RBAC guards implemented in Milestone 1 (`@can`, `@canany`, `@else`).
- Follow `/ui-simplify-layout` and `cooca-agent-directive`.
- Minimum 44px tap targets on mobile for interactive elements.
- Zero syntax errors (`php -l`), perfect Blade compilation (`php artisan view:cache`), 100% test pass.

## Current Parent
- Conversation ID: 58fe913b-8bbe-4705-a472-1a575b67e3a5
- Updated: 2026-11-07T10:00:00Z

## Task Summary
- **What to build**: UI layout improvements:
  1. `analytics/index.blade.php`: Eliminate duplicate header/subtitle card, convert 4 stacking KPI cards to responsive horizontal snap slider on mobile, ensure period segmented buttons and export buttons have min-h-[44px].
  2. `approvals/inbox.blade.php`: Eliminate redundant inner card header, format ticket actions into symmetric 2-col mobile grid with min-h-[44px].
  3. `approvals/history.blade.php`: Eliminate redundant inner header block.
  4. `approvals/rules.blade.php`: Eliminate redundant inner header block, verify semantic icon & label for "Tambah Aturan" (plus icon, no duplicate `+` character).
  5. `billing/limits.blade.php`: Replace storage topup generic icon `plus` with semantic `database` (or `hard-drive`), verify responsive layout.
- **Success criteria**: Zero syntax errors, Blade caches cleanly, zero regressions on test suites.
- **Interface contracts**: `PROJECT.md`
- **Code layout**: `PROJECT.md` § Code Layout

## Key Decisions Made
- Keep all RBAC guards from M1 completely intact while restructuring layouts and classes.
- Use established Tailwind snap-slider pattern: `flex gap-3 overflow-x-auto snap-x snap-mandatory no-scrollbar -mx-4 px-4 pb-2 sm:grid sm:grid-cols-2 lg:grid-cols-4 sm:overflow-visible sm:mx-0 sm:px-0`.

## Change Tracker
- **Files modified**: None yet.
- **Build status**: Ready for implementation.
- **Pending issues**: None.

## Quality Status
- **Build/test result**: Untested yet in M2.
- **Lint status**: Clean.
- **Tests added/modified**: Existing tests will be executed.

## Loaded Skills
- **Source**: C:\Users\Ulfah Ramadhani\.gemini\config\skills\ui-simplify-layout\SKILL.md
- **Core methodology**: Simplification of information, button ergonomics (44px tap targets, 1 primary action, semantic icons, no duplicate `+`), no overlap, horizontal snap slider for mobile KPIs.
- **Source**: C:\Users\Ulfah Ramadhani\.gemini\config\skills\cooca-agent-directive\SKILL.md
- **Core methodology**: Integrated orchestration across UI, Apple HIG macOS styling, zero feature leakage, multi-tenant RBAC.

## Artifact Index
- `.agents/teamwork/worker_m2_ui/DISPATCH.md` — Incoming dispatch from orchestrator
- `.agents/teamwork/worker_m2_ui/BRIEFING.md` — Agent working memory
- `.agents/teamwork/worker_m2_ui/progress.md` — Liveness heartbeat and step tracking
- `.agents/teamwork/worker_m2_ui/handoff.md` — Self-contained final report
