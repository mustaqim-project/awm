# DISPATCH LOG

## 2026-11-08T14:32:58Z
You are Worker subagent (Worker M1: RBAC Enforcement & Controller-Level Guards).
Your working directory is: c:\laragon\www\cooca_core\.agents\teamwork\worker_feedback_finance_rbac\

MANDATORY FIRST STEP:
Read the authoritative user request at:
c:\laragon\www\cooca_core\.agents\teamwork\ORIGINAL_REQUEST.md
And read the project blueprint and feature inventory at:
c:\laragon\www\cooca_core\.agents\teamwork\orchestrator\PROJECT.md
Also read the detailed survey findings at:
c:\laragon\www\cooca_core\.agents\teamwork\explorer_feedback_finance_rbac\rbac_survey_report.md
and the view survey at:
c:\laragon\www\cooca_core\.agents\teamwork\explorer_feedback_finance_ui\ui_survey_report.md

You MUST read and follow these skill references:
- c:\Users\Ulfah Ramadhani\.gemini\config\skills\cooca-agent-directive\SKILL.md
- c:\Users\Ulfah Ramadhani\.gemini\config\skills\laravel-permission\SKILL.md

MANDATORY INTEGRITY WARNING:
DO NOT CHEAT. All implementations must be genuine. DO NOT hardcode test results, create dummy/facade implementations, or circumvent the intended task. A teamwork_auditor will independently verify your work. Integrity violations WILL be detected and your work WILL be rejected.

EXCLUSIVE WRITE OWNERSHIP:
You own and may ONLY edit the following files:
1. database/seeders/RbacSeeder.php
2. app/Http/Controllers/Web/FeedbackWebController.php
3. routes/owner.php (feedback routes section)
4. app/Http/Controllers/Common/SettlementProofController.php
5. resources/views/layouts/partials/sidebar.blade.php (feedback link)
6. resources/views/layouts/partials/topbar.blade.php (feedback link)
7. Specific view guard checks in:
   - resources/views/app/finance/payables.blade.php (wrap import button in @can('finance.payables'))
   - resources/views/app/finance/receivables.blade.php (wrap import button in @can('finance.receivables'))
   - resources/views/app/finance/edc-terminals/index.blade.php (wrap add modal button in @can('finance.cash_bank'))
   - resources/views/app/finance/payout-accounts/index.blade.php (wrap add modal button in @can('finance.cash_bank'))
   - resources/views/app/finance/settlements/index.blade.php (wrap settlement form in @can('finance.cash_bank'))
   - resources/views/app/finance/accounting/balance-sheet.blade.php (wrap export link in @can('accounting.view'))
   - resources/views/app/finance/accounting/trial-balance.blade.php (wrap export link in @can('accounting.view'))
   - resources/views/app/finance/accounting/general-ledger.blade.php (wrap export link in @can('accounting.view'))

DO NOT touch any docs files or other UI files.

TASK SPECIFICATIONS:
1. In `database/seeders/RbacSeeder.php`:
   - In `permissionMatrix()`, register:
     `feedback.view` => ['name' => 'feedback.view', 'label' => 'Lihat Tiket Masalah & Feedback', 'group' => 'Feedback', 'module' => 'feedback', 'action' => 'view'],
     `feedback.create` => ['name' => 'feedback.create', 'label' => 'Kirim Laporan Bug & Request Fitur', 'group' => 'Feedback', 'module' => 'feedback', 'action' => 'create'],
   - In `roleDefinitions()`, assign `feedback.view` and `feedback.create` to: `owner`, `manager`, `admin`, `finance`.
2. In `app/Http/Controllers/Web/FeedbackWebController.php`:
   - Update `middleware()` method:
     Replace `new Middleware('require.role:owner')` with granular permission middleware:
     - `new Middleware('require.permission:feedback.view', only: ['bugs', 'features', 'showBug', 'showFeature', 'streamBugAttachment'])`
     - `new Middleware('require.permission:feedback.create', only: ['createBug', 'storeBug', 'createFeature', 'storeFeature'])`
3. In `routes/owner.php`:
   - Remove the outer `Route::middleware('require.role:owner')->group(...)` constraint wrapping feedback routes so that users with `feedback.view` / `feedback.create` can access them without being strictly role `owner`.
4. In `app/Http/Controllers/Common/SettlementProofController.php`:
   - In `show()` method, add permission verification: if not superadmin and is merchant, ensure user has permission `finance.cash_bank` (`\App\Support\Context::hasPermission('finance.cash_bank')`), aborting 403 otherwise.
5. In navigation views:
   - `sidebar.blade.php` and `topbar.blade.php`: change `@if (\App\Support\Context::isOwner())` for feedback links to `@if (\App\Support\Context::isOwner() || \App\Support\Context::hasPermission('feedback.view'))`.
6. In the finance Blade views listed above:
   - Wrap the sensitive action buttons / links in `@can(...) ... @endcan` as described.

VERIFICATION:
- Run `php -l` on all modified files.
- Run `php artisan route:list --path=feedback`.
- Run `php artisan test tests/Feature/FeedbackModuleAuditTest.php tests/Feature/FinanceModuleAuditTest.php`.

DELIVERABLES:
Write a complete handoff report to:
`c:\laragon\www\cooca_core\.agents\teamwork\worker_feedback_finance_rbac\handoff.md`
Report files changed, line diffs, syntax checks, test outputs, and verification results. Then send a completion message to the orchestrator.
