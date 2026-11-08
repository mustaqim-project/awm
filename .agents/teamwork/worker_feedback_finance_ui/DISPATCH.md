## 2026-11-08T14:52:43Z
[Message] timestamp=2026-11-08T14:52:43Z sender=2dc7873b-7808-466b-ace5-8c03239822ef priority=MESSAGE_PRIORITY_HIGH content=You are Worker subagent (Worker M2: UI Simplification & Layout Optimization).
Your working directory is: c:\laragon\www\cooca_core\.agents\teamwork\worker_feedback_finance_ui\

MANDATORY FIRST STEP:
Read the authoritative user request at:
c:\laragon\www\cooca_core\.agents\teamwork\ORIGINAL_REQUEST.md
And read the project blueprint and feature inventory at:
c:\laragon\www\cooca_core\.agents\teamwork\orchestrator\PROJECT.md
Also read the detailed survey findings and exact line references at:
c:\laragon\www\cooca_core\.agents\teamwork\explorer_feedback_finance_ui\ui_survey_report.md

You MUST read and follow these skill references:
- c:\Users\Ulfah Ramadhani\.gemini\config\skills\cooca-agent-directive\SKILL.md
- c:\Users\Ulfah Ramadhani\.gemini\config\skills\ui-simplify-layout\SKILL.md
- c:\Users\Ulfah Ramadhani\.gemini\config\skills\responsive-ui-ux\SKILL.md

MANDATORY INTEGRITY WARNING:
DO NOT CHEAT. All implementations must be genuine. DO NOT hardcode test results, create dummy/facade implementations, or circumvent the intended task. A teamwork_auditor will independently verify your work. Integrity violations WILL be detected and your work WILL be rejected.

EXCLUSIVE WRITE OWNERSHIP:
You own and may ONLY edit the following files:
1. resources/views/app/finance/cash-bank/index.blade.php
2. resources/views/app/feedback/create.blade.php
3. resources/views/app/feedback/show.blade.php
4. resources/views/app/feedback/index.blade.php
5. resources/views/app/finance/settlements/show.blade.php
6. resources/views/app/finance/accounting/coa.blade.php
7. resources/views/app/finance/payables.blade.php
8. resources/views/app/finance/receivables.blade.php
9. resources/views/app/finance/accounting/reconciliation.blade.php
10. resources/views/app/finance/edc-terminals/index.blade.php
11. resources/views/app/finance/payout-accounts/index.blade.php
12. resources/views/app/finance/expenses.blade.php
13. lang/id/feedback.php and lang/en/feedback.php
14. lang/id/finance.php and lang/en/finance.php

DO NOT touch any controller, route, seeder, or docs files!

TASK SPECIFICATIONS:

1. Feature F6 (Action Button Hierarchy & Anti-Clutter in `cash-bank/index.blade.php`):
   - In lines 25–40, eliminate competing solid primary buttons (green Kas Masuk, red Kas Keluar, blue Transfer Kas).
   - Establish exactly ONE clear primary solid button: "Kas Masuk" (`bg-primary` / `bg-[#007AFF] text-white`).
   - Style "Kas Keluar" and "Transfer Kas" as clean secondary actions (`bg-black/[0.04] dark:bg-white/[0.06] text-black dark:text-white border border-black/10 dark:border-white/10 hover:bg-black/[0.08]` with semantic icons).
   - Remove the redundant second "Tambah Akun" button at line 88 above the accounts grid since "Tambah Akun" is already available in the header.

2. Feature F7 (Double Title Elimination in Feedback Views):
   - In `feedback/create.blade.php` (lines 54–56), remove the redundant `<h2>` card title that duplicates `:title` in `<x-module-header>`.
   - In `feedback/show.blade.php` (lines 79–81), remove the redundant `<h1>` card title that duplicates `:title` in `<x-module-header>`.

3. Feature F8 (Header Component Standardization in `settlements/show.blade.php`):
   - In lines 9–53, replace the raw custom `<header>` and manual `<nav>` breadcrumbs with the standard `<x-module-header :breadcrumbs="...">` component used by all other finance views.

4. Feature F9 (Apple HIG Touch Targets ≥ 44px):
   - `accounting/coa.blade.php` lines 227 & 235: Upgrade row action buttons from `w-8 h-8 rounded-[8px]` (32px) to minimum 44px (`min-w-[44px] min-h-[44px]`).
   - `payables.blade.php`: Upgrade line 222 table action button to `min-w-[44px] min-h-[44px]`, and modal actions (lines 543 & 558) from `h-10` to `h-11` (44px).
   - `receivables.blade.php`: Upgrade line 228 table action button to `min-w-[44px] min-h-[44px]`, and modal actions (lines 597 & 612) from `h-10` to `h-11` (44px).
   - `accounting/reconciliation.blade.php` lines 164 & 171: Upgrade match/unmatch buttons from `min-w-[36px] min-h-[36px]` to `min-w-[44px] min-h-[44px]`.
   - `edc-terminals/index.blade.php`: Upgrade toggle (line 134) and delete button (line 143) to `min-h-[44px]` / `min-w-[44px] min-h-[44px]`.
   - `payout-accounts/index.blade.php`: Upgrade toggle (line 89) and delete button (line 99) to `min-h-[44px]` / `min-w-[44px] min-h-[44px]`.
   - `feedback/show.blade.php` line 156: Upgrade attachment link from `h-9` (36px) to `min-h-[44px]`.

5. Feature F10 (Mobile Safe Area & Empty State Polish):
   - In `settlements/show.blade.php`: Upgrade root padding to `pb-28 sm:pb-32 lg:pb-10` (currently `pb-16`) to eliminate mobile fixed bottom bar cutoff.
   - In `settlements/show.blade.php`: In lines 219–235, convert `$settlement->allocations` `@foreach` to `@forelse ... @empty` with clean empty state.

6. Feature F11 (i18n & Zero Hardcoded Text):
   - In `expenses.blade.php`: Localize category button labels in modal (lines 399–488).
   - In `feedback/create.blade.php`: Localize level and category select options using `__('feedback.severity_' . $level)`.
   - In `feedback/index.blade.php`, `create.blade.php`, `show.blade.php`: Localize `$homeLabel` breadcrumb to `__('common.dashboard')` or `__('common.portal')`.

VERIFICATION:
- Run `php -l` on all modified files.
- Run `php artisan view:clear; php artisan view:cache` to ensure all Blade views compile without errors.
- Run `php artisan test tests/Feature/FeedbackModuleAuditTest.php tests/Feature/FinanceModuleAuditTest.php` to ensure zero regressions.

DELIVERABLES:
Write a complete handoff report to:
`c:\laragon\www\cooca_core\.agents\teamwork\worker_feedback_finance_ui\handoff.md`
Report files modified, changes made, touch target measurements, syntax checks, and test results. Then send a completion message to the orchestrator.
