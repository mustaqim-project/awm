## 2026-10-31T20:42:20Z
You are worker_phase4 (Phase 4 Monolith Modularization Worker).
Working directory: c:\laragon\www\cooca_core\.agents\teamwork\worker_phase4\
Parent: Orchestrator (Conversation ID: a75db325-cf9d-49ca-b5e1-728a0b485275)

MANDATORY INTEGRITY WARNING:
DO NOT CHEAT. All implementations must be genuine. DO NOT hardcode test results, create dummy/facade implementations, or circumvent the intended task. A teamwork_auditor will independently verify your work. Integrity violations WILL be detected and your work WILL be rejected.

Read the authoritative user request at: c:\laragon\www\cooca_core\.agents\teamwork\ORIGINAL_REQUEST.md
Read the Phase 4 blueprint authored by explorer_rem_3 at: c:\laragon\www\cooca_core\.agents\teamwork\explorer_rem_3\handoff.md

Your mission is to execute Phase 4 Monolith Modularization across POS and HRM modules:
1. POS Terminal Monolith (`resources/views/app/pos/terminal.blade.php`, 7,158 lines, 20 embedded modals):
   - Extract the 20 embedded modal dialogs (lines 2139–4629) into isolated sub-views under `resources/views/app/pos/modals/*.blade.php` as mapped in `explorer_rem_3/handoff.md`:
     `customer-modal`, `payment-modal`, `qris-modal`, `success-modal`, `settings-modal`, `open-shift-modal`, `close-shift-modal`, `held-orders-modal`, `hold-prompt-modal`, `cash-movement-modal`, `supervisor-pin-modal`, `scanner-permission-modal`, `barcode-scanner-modal`, `modifier-modal`, `service-vertical-modal`, `item-detail-modal`, `incoming-orders-modal`, `reject-reason-modal`, `tables-modal`, `table-payment-modal`.
   - In `resources/views/app/pos/terminal.blade.php`, replace each inline modal block with clean `@include('app.pos.modals.<name>')`.
   - Preserve 100% of Alpine.js state (`posApp()`), event listeners, and industry gating conditions (`hasDineInFeature()`, `isWorkshop()`, `isLaundry()`, etc.).
2. HRM Index Monolith (`resources/views/app/hrm/index.blade.php`, 5,705 lines, 10 data tables, 15 modals):
   - Extract the 15 modals (lines 2984–5703) into `resources/views/app/hrm/modals/*.blade.php` and the tab sub-panels (lines 1042–2983) into `resources/views/app/hrm/partials/tab-*.blade.php` as mapped in `explorer_rem_3/handoff.md`.
   - In `resources/views/app/hrm/index.blade.php`, replace the inline blocks with `@include(...)`.
   - Preserve 100% of Alpine.js state (`hrmApp()`), data tables, form submissions, and routes.
3. Verification:
   - Run `php artisan view:clear; php artisan view:cache` using run_command to confirm 100% clean Blade compilation.
   - Run `php artisan test tests/Feature/Pos/PosTerminalIndustryGatingTest.php` to confirm zero regression on POS functionality.
   - Verify that all newly created sub-view files exist and are clean.

Write a complete report to `c:\laragon\www\cooca_core\.agents\teamwork\worker_phase4\handoff.md`.
When finished, send a message to the Orchestrator (a75db325-cf9d-49ca-b5e1-728a0b485275) reporting completion.
