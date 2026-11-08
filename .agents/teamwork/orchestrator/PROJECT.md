# Project: Feedback & Finance Modules Production Readiness

## Architecture
- **Framework**: Laravel 11.x multi-tenant SaaS ERP (COOCA Core).
- **Presentation Layer**: Blade templates with Tailwind CSS, Alpine.js, Lucide SVG icons, and Apple HIG responsive design principles (zero overlapping elements, single primary action per view, touch targets ≥ 44px).
- **Authorization Engine**: Spatie-based multi-tenant RBAC with `AppServiceProvider` `Gate::before` delegation to `\App\Support\Context::hasPermission(...)`, supporting native Blade `@can(...)` directives and controller `HasMiddleware` with `Middleware('require.permission:...')`.
- **Documentation Engine**: `DocumentationWebController` and `resources/views/app/docs/` with Bento Apple HIG partials, interactive verified `route(...)` links, and 20-industry practical implementation matrices across 6 standard clusters (F&B, Ritel, Jasa Harian, Manufaktur, Jasa Proyek, Distribusi/Agro).

## Feature Inventory
| # | Feature | Description | Milestone | Source |
|---|---------|-------------|-----------|--------|
| F1 | RBAC Seeder Registration (Feedback) | Register `feedback.view` and `feedback.create` permissions in `database/seeders/RbacSeeder.php` with role mappings (`owner`, `manager`, `admin`, `finance`, etc.) | M1 | Survey (Explorer 2) |
| F2 | Controller Authorization Guards (Feedback) | Refactor `app/Http/Controllers/Web/FeedbackWebController.php` to implement `HasMiddleware` with granular permissions (`feedback.view`, `feedback.create`) instead of `require.role:owner` | M1 | Survey (Explorer 2) |
| F3 | Route & Navigation Alignment (Feedback) | Remove outer `require.role:owner` wrapper from `routes/owner.php` for feedback routes; update `sidebar.blade.php` and `topbar.blade.php` visibility checks to `@if (\App\Support\Context::isOwner() || \App\Support\Context::hasPermission('feedback.view'))` | M1 | Survey (Explorer 2) |
| F4 | Settlement Proof Permission Guard | Add `Context::hasPermission('finance.cash_bank')` authorization check in `app/Http/Controllers/Common/SettlementProofController.php` | M1 | Survey (Explorer 2) |
| F5 | Blade Action Permission Guards | Wrap unguarded action buttons in `@can(...)`: Payables & Receivables import (`finance.payables` / `finance.receivables`), EDC & Payout Account creation (`finance.cash_bank`), Settlement form (`finance.cash_bank`), Accounting export links (`accounting.view`) | M1 | Survey (Explorer 1, 2) |
| F6 | Action Button Hierarchy & Anti-Clutter | In `cash-bank/index.blade.php`, eliminate competing solid primary buttons (Kas Masuk, Kas Keluar, Transfer Kas) into 1 primary action with secondary variants; remove redundant "Tambah Akun" button | M2 | Survey (Explorer 1) |
| F7 | Double Title Elimination | In `feedback/create.blade.php` and `feedback/show.blade.php`, remove redundant `<h2>` and `<h1>` card titles that duplicate the `<x-module-header :title="...">` | M2 | Survey (Explorer 1) |
| F8 | Header Component Standardization | In `finance/settlements/show.blade.php`, replace raw `<header>` and manual breadcrumb markup with standard `<x-module-header :breadcrumbs="...">` | M2 | Survey (Explorer 1) |
| F9 | Apple HIG Touch Targets (≥ 44px) | Enforce minimum 44px touch targets across 9 views: `accounting/coa.blade.php` (convert 32px buttons to 44px), `payables.blade.php`, `receivables.blade.php`, `accounting/reconciliation.blade.php`, `edc-terminals/index.blade.php`, `payout-accounts/index.blade.php`, `feedback/show.blade.php` | M2 | Survey (Explorer 1) |
| F10 | Mobile Safe Area & Empty State Polish | In `settlements/show.blade.php`, upgrade bottom padding to `pb-28 sm:pb-32 lg:pb-10` and add `@empty` fallback for allocations loop | M2 | Survey (Explorer 1, 3) |
| F11 | i18n & Zero Hardcoded Text | Translate hardcoded Indonesian expense category labels in `expenses.blade.php`; localize raw enum strings and breadcrumb home labels in feedback views | M2 | Survey (Explorer 1) |
| F12 | Feedback Business Owner Guide | Create `resources/views/app/docs/partials/sec_feedback.blade.php` with POV Business Owner, 4-step workflow, 20-industry matrix, privacy/sanitization guidelines, FAQ, and verified interactive route links | M3 | Survey (Explorer 3) |
| F13 | Finance Business Owner Guide | Create `resources/views/app/docs/partials/sec_finance.blade.php` replacing the obsolete 35-line stub with full Bento Apple HIG, 4-step workflow, 20-industry matrix across 6 clusters, fraud prevention, and verified interactive route links to 15 sub-modules | M3 | Survey (Explorer 3) |
| F14 | Docs TOC & Navigation Integration | Wire `#sec-feedback` and `#sec-finance` into the Left Sidebar TOC and include directives in `resources/views/app/docs/index.blade.php` | M3 | Survey (Explorer 3) |
| F15 | RBAC & 403 Forbidden Comprehensive Tests | Create `tests/Feature/FeedbackAndFinanceComprehensiveSecurityTest.php` testing 403 Forbidden for unauthorized roles and 200 OK for authorized roles across Feedback and Finance endpoints | M4 | Survey (Explorer 2, 3) |
| F16 | UI Zero Overlap & View Compilation Test | Add automated tests verifying all modified Blade views compile cleanly with zero errors and conform to responsive structural guidelines | M4 | Survey (Explorer 1, 3) |
| F17 | Syntax & Route Verification | Validate all modified and newly created Blade and PHP files with `php -l` and ensure `php artisan route:list` runs without error | M4 | Survey (Explorer 1, 2, 3) |
| F18 | Multi-Perspective Quality Gate | Run the complete verification gate: 2 Reviewers (RBAC/Security, UI/UX/Docs), 2 Challengers (Adversarial Security & Compilation), and 1 Forensic Auditor | M4 | Project Pattern |

## Milestones
| # | Name | Scope | Dependencies | Status |
|---|------|-------|-------------|--------|
| 1 | RBAC Enforcement & Controller-Level Guards | F1, F2, F3, F4, F5 | none | DONE |
| 2 | UI Simplification & Layout Optimization | F6, F7, F8, F9, F10, F11 | M1 | DONE |
| 3 | Business Owner Documentation | F12, F13, F14 | none | DONE |
| 4 | Test Suites, Production Readiness & Forensic Audit Gate | F15, F16, F17, F18 | M1, M2, M3 | DONE |

## Interface Contracts

### Multi-Tenant Authorization Contract
- Permissions in `database/seeders/RbacSeeder.php`:
  - `feedback.view`: View feedback bugs and features listings, view individual tickets, stream attachments.
  - `feedback.create`: Submit new bug reports and feature requests.
  - `expenses.view`, `expenses.manage`: Manage operational expenses.
  - `accounting.view`, `accounting.manage`: Chart of Accounts, Journals, Balance Sheet, Trial Balance, General Ledger, Reconciliation.
  - `finance.cash_bank`: Cash & bank accounts, cash ledger, settlements, EDC terminals, payout accounts, external reconciliation.
  - `finance.receivables`: Accounts receivable and customer debt tracking.
  - `finance.payables`: Accounts payable and supplier bills.
- Controllers implement `Illuminate\Routing\Controllers\HasMiddleware` with `new Middleware('require.permission:...')`.
- Blade templates hide unauthorized buttons using `@can('permission.name')` / `@cannot('permission.name')`.
- Unauthorized requests must receive `403 Forbidden`.

### Business Owner Documentation Contract
- Documentation partials located in `resources/views/app/docs/partials/sec_{module}.blade.php`.
- Each partial MUST contain:
  1. Header Bento with semantic icon, badge, and verified direct route link (`route('...')`).
  2. Executive Summary written in conversational Indonesian from Business Owner (UMKM) POV.
  3. 4-step practical workflow.
  4. 20-Industry Application Matrix across 6 standard clusters (F&B, Manufaktur, Ritel, Jasa Harian, Jasa Proyek, Distribusi/Agro).
  5. Security, authorization, and fraud prevention section.
  6. Practical FAQ.
- All links must use valid route names:
  - Feedback: `route('feedback.bugs.index')`, `route('feedback.bugs.create')`, `route('feedback.features.index')`, `route('feedback.features.create')`
  - Finance: `route('finance.cash-bank.index')`, `route('finance.expenses.index')`, `route('finance.payables.index')`, `route('finance.receivables.index')`, `route('finance.accounting.coa')`, `route('finance.accounting.balance-sheet')`, `route('finance.accounting.trial-balance')`, `route('finance.accounting.general-ledger')`, `route('finance.accounting.reconciliation')`, `route('finance.journals.index')`, `route('finance.settlements.index')`, `route('finance.external-reconciliation.index')`, `route('finance.edc-terminals.index')`, `route('finance.payout-accounts.index')`

## Code Layout
- Controllers:
  - `app/Http/Controllers/Web/FeedbackWebController.php`
  - `app/Http/Controllers/Web/Finance/AccountingWebController.php`
  - `app/Http/Controllers/Web/Finance/CashLedgerWebController.php`
  - `app/Http/Controllers/Web/Finance/ExternalReconciliationWebController.php`
  - `app/Http/Controllers/Web/Finance/MerchantPayoutAccountWebController.php`
  - `app/Http/Controllers/Web/Finance/PaymentSettlementWebController.php`
  - `app/Http/Controllers/Web/Finance/PosFinanceWebController.php`
  - `app/Http/Controllers/Web/Finance/StoreEdcTerminalWebController.php`
  - `app/Http/Controllers/Common/SettlementProofController.php`
  - `app/Http/Controllers/Web/DocumentationWebController.php`
- Seeders:
  - `database/seeders/RbacSeeder.php`
- Routes:
  - `routes/owner.php`
  - `routes/web.php`
- Navigation Views:
  - `resources/views/layouts/partials/sidebar.blade.php`
  - `resources/views/layouts/partials/topbar.blade.php`
- Feedback Views:
  - `resources/views/app/feedback/index.blade.php`
  - `resources/views/app/feedback/create.blade.php`
  - `resources/views/app/feedback/show.blade.php`
- Finance Views:
  - `resources/views/app/finance/expenses.blade.php`
  - `resources/views/app/finance/journals.blade.php`
  - `resources/views/app/finance/payables.blade.php`
  - `resources/views/app/finance/receivables.blade.php`
  - `resources/views/app/finance/cash-bank/index.blade.php`
  - `resources/views/app/finance/cash-bank/ledger.blade.php`
  - `resources/views/app/finance/edc-terminals/index.blade.php`
  - `resources/views/app/finance/external-reconciliation/index.blade.php`
  - `resources/views/app/finance/payout-accounts/index.blade.php`
  - `resources/views/app/finance/settlements/index.blade.php`
  - `resources/views/app/finance/settlements/show.blade.php`
  - `resources/views/app/finance/accounting/coa.blade.php`
  - `resources/views/app/finance/accounting/balance-sheet.blade.php`
  - `resources/views/app/finance/accounting/trial-balance.blade.php`
  - `resources/views/app/finance/accounting/general-ledger.blade.php`
  - `resources/views/app/finance/accounting/reconciliation.blade.php`
- Documentation Views:
  - `resources/views/app/docs/index.blade.php`
  - `resources/views/app/docs/partials/sec_feedback.blade.php` (new)
  - `resources/views/app/docs/partials/sec_finance.blade.php` (new)
- Tests:
  - `tests/Feature/FeedbackModuleAuditTest.php`
  - `tests/Feature/FeedbackWorkflowTest.php`
  - `tests/Feature/FinanceModuleAuditTest.php`
  - `tests/Feature/FinanceAndAccountingNavigationTest.php`
  - `tests/Feature/FinanceApiIntegrationTest.php`
  - `tests/Feature/FeedbackAndFinanceComprehensiveSecurityTest.php` (new)
  - `tests/Feature/DocumentationFeatureTest.php`
