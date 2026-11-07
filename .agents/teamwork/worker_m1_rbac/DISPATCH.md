## 2026-11-07T09:38:06Z
You are Worker 1: RBAC Worker.
Your working directory is: c:\laragon\www\cooca_core\.agents\teamwork\worker_m1_rbac\

MANDATORY INTEGRITY WARNING:
DO NOT CHEAT. All implementations must be genuine. DO NOT hardcode test results, create dummy/facade implementations, or circumvent the intended task. A teamwork_auditor will independently verify your work. Integrity violations WILL be detected and your work WILL be rejected.

MANDATORY INSTRUCTIONS:
1. You MUST read the authoritative request at: c:\laragon\www\cooca_core\.agents\teamwork\ORIGINAL_REQUEST.md
2. You MUST read the project scope document at: c:\laragon\www\cooca_core\.agents\teamwork\orchestrator\PROJECT.md
3. You MUST read the explorer findings at: c:\laragon\www\cooca_core\.agents\teamwork\explorer_rbac_survey\handoff.md
4. You MUST follow the domain skill instructions at:
   - C:\Users\Ulfah Ramadhani\.gemini\config\skills\laravel-permission\SKILL.md
   - C:\Users\Ulfah Ramadhani\.gemini\config\skills\cooca-agent-directive\SKILL.md

EXCLUSIVE FILE OWNERSHIP:
You own and modify ONLY the following files:
- `database/seeders/RbacSeeder.php`
- `app/Http/Controllers/Web/AnalyticsWebController.php`
- `app/Http/Controllers/Web/Approval/ApprovalWebController.php`
- `app/Http/Controllers/Web/Billing/BillingAndLimitWebController.php`
- `app/Http/Controllers/Web/Billing/SubscriptionCheckoutWebController.php`
- `resources/views/app/approvals/inbox.blade.php`
- `resources/views/app/approvals/rules.blade.php`
- `resources/views/app/analytics/index.blade.php`
- `resources/views/app/billing/limits.blade.php`

TASK DETAILS:
1. `database/seeders/RbacSeeder.php`:
   - Ensure `analytics.view` permission is registered (with title "Lihat Analitik & Tren Bisnis", module "analytics", and description "Akses dashboard performa analitik penjualan dan pertumbuhan"), and assigned to `owner`, `manager`, `admin`. Maintain backward compatibility with `reports.view`.
   - Verify `approvals.view`, `approvals.manage`, `billing.view`, `billing.manage` are cleanly registered with appropriate roles.
2. `app/Http/Controllers/Web/AnalyticsWebController.php`:
   - In `middleware()`, configure permission requirement to accept `require.permission:analytics.view,reports.view` so both specific and legacy permissions are honored.
3. `resources/views/app/approvals/inbox.blade.php`:
   - In ticket cards (around lines 131-146): Wrap "Tolak" and "Setujui Level" action buttons with `@can('approvals.manage')` or `@if(\App\Support\Context::hasPermission('approvals.manage'))`. Ensure view-only users cannot see clickable mutation buttons.
   - In header (around line 31): Update "Kelola Aturan" link from hardcoded role check to `@can('approvals.manage')`.
4. `resources/views/app/approvals/rules.blade.php`:
   - In header (around lines 31-37): Wrap "Tambah Aturan" button with `@can('approvals.manage')`.
   - In empty state (around lines 132-139): Wrap "Buat Aturan Pertama" button with `@can('approvals.manage')`.
   - Standardize rule card edit and delete buttons to `@can('approvals.manage')`.
5. `resources/views/app/analytics/index.blade.php`:
   - Wrap "Cetak Laporan" with `@can('reports.export')` or `@can('reports.view')`.
   - Wrap formal reports and stock valuation links with `@can('reports.view')`.
   - Guard confidential gross margin / net profit KPI cards and table columns with `@can('reports.financial')` or graceful fallback.
6. `resources/views/app/billing/limits.blade.php`:
   - In 4-tier plan cards, wrap upgrade/choose buttons with `@can('billing.manage')` or render a clean read-only badge when user lacks `billing.manage`.
   - In File Manager modal, ensure delete file button is guarded with `@if(\App\Support\Context::hasPermission('billing.manage'))`.
7. VERIFICATION:
   - Run `php -l` on all modified files to ensure 0 syntax errors.
   - Run tests:
     `php artisan config:clear`
     `php artisan test tests/Feature/AnalyticsWebTest.php tests/Feature/ApprovalModuleAuditTest.php tests/Feature/DocumentApprovalWorkflowTest.php tests/Feature/BillingModuleAuditTest.php tests/Feature/BillingSubmoduleSecurityAndErgonomicsTest.php`
   - Document all changes, verification commands, and test outputs in `c:\laragon\www\cooca_core\.agents\teamwork\worker_m1_rbac\handoff.md`.
   - Send completion message back to orchestrator.
