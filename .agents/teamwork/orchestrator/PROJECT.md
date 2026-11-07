# Project: Analytics, Approvals, and Billing Modules Refactor, RBAC & Documentation

## Architecture
- **Framework**: Laravel 11.x multi-tenant SaaS ERP (Cooca Core).
- **Presentation Layer**: Blade templates with Tailwind CSS, Alpine.js, and Lucide icons.
- **Authorization Engine**: Spatie-based multi-tenant RBAC with `AppServiceProvider` `Gate::before` delegation to `\App\Support\Context::hasPermission(...)`, supporting native Blade `@can(...)` directives.
- **Documentation Engine**: `DocumentationWebController` and `resources/views/app/docs/` with Bento Apple HIG partials, interactive verified `route(...)` links, and 20-industry practical implementation matrices.

## Feature Inventory
| # | Feature | Description | Milestone | Source |
|---|---------|-------------|-----------|--------|
| F1 | RBAC Seeder Alignment | Ensure granular permissions for `analytics.view`, `approvals.view`, `approvals.manage`, `billing.view`, `billing.manage` in `RbacSeeder.php` | M1 | Survey (Explorer 2) |
| F2 | Controller Authorization Guards | Verify & enforce `HasMiddleware` with `require.permission:...` across `AnalyticsWebController`, `ApprovalWebController`, `BillingAndLimitWebController`, and `SubscriptionCheckoutWebController` | M1 | Survey (Explorer 2) |
| F3 | Blade Action Guarding (Approvals) | Guard "Tolak" and "Setujui" buttons in `inbox.blade.php` and "Tambah Aturan" buttons in `rules.blade.php` with `@can('approvals.manage')` | M1 | Survey (Explorers 1, 2, 3) |
| F4 | Blade Action Guarding (Analytics & Billing) | Guard export/print and report links in `analytics/index.blade.php` with `@can('reports.view')` / `@can('reports.export')`; guard upgrade CTAs and File Manager delete in `billing/limits.blade.php` with `@can('billing.manage')` | M1 | Survey (Explorers 1, 2) |
| F5 | UI Simplification & Double Header Elimination | Eliminate duplicate topbar header titles and redundant internal `<h1>` blocks in `analytics/index.blade.php`, `approvals/inbox.blade.php`, `approvals/history.blade.php`, `approvals/rules.blade.php` | M2 | Survey (Explorer 1) |
| F6 | Mobile Layout & Horizontal Snap Sliders | Convert stacking KPI cards in `analytics/index.blade.php` to responsive horizontal snap sliders on 375px-430px mobile viewports per `/ui-simplify-layout` | M2 | Survey (Explorer 1) |
| F7 | Action Button Ergonomics & Anti-Clutter | Format approval action buttons in `inbox.blade.php` into symmetric 2-column mobile grid with >= 44px tap targets; eliminate cluttered wraps | M2 | Survey (Explorer 1) |
| F8 | Billing UI Polish & Semantic Icons | Standardize storage topup button icon to semantic `database` or `hard-drive` in `billing/limits.blade.php`; verify responsive layout and zero visual overlap | M2 | Survey (Explorer 1) |
| F9 | Analytics Business Owner Guide | Create `resources/views/app/docs/partials/sec_analytics.blade.php` with POV Business Owner, 4-step workflow, 20-industry matrix, and interactive route links | M3 | Survey (Explorer 3) |
| F10 | Approvals Business Owner Guide | Create `resources/views/app/docs/partials/sec_approvals.blade.php` covering Maker-Checker workflows, fraud prevention, 20-industry matrix, and interactive route links | M3 | Survey (Explorer 3) |
| F11 | Billing Business Owner Guide | Create `resources/views/app/docs/partials/sec_billing.blade.php` covering 4 tiers, TriPay automatic activation, cloud storage, emergency buffers, 20-industry matrix, and route links | M3 | Survey (Explorer 3) |
| F12 | Docs Navigation TOC Integration | Register `sec-analytics`, `sec-approvals`, `sec-billing` in `resources/views/app/docs/index.blade.php` Left Sidebar TOC with smooth scrolling links | M3 | Survey (Explorer 3) |
| F13 | Production Readiness & Automated Test Suites | Verify all modified Blade and PHP files with `php -l`; run and expand automated test suites for Analytics, Approvals, Billing, and Documentation | M4 | Survey (Explorers 1, 3) |
| F14 | Comprehensive Quality Gate & Forensic Audit | Multi-reviewer (2), challenger (2), and forensic integrity audit (1) verification | M4 | Project Pattern |

## Milestones
| # | Name | Scope | Dependencies | Status |
|---|------|-------|-------------|--------|
| 1 | RBAC Enforcement & Controller/View Guards | F1, F2, F3, F4 | none | PLANNED |
| 2 | UI Simplification & Layout Cleanliness | F5, F6, F7, F8 | M1 | PLANNED |
| 3 | Business Owner Documentation | F9, F10, F11, F12 | none | PLANNED |
| 4 | Test Suites, Production Readiness & Forensic Audit Gate | F13, F14 | M1, M2, M3 | PLANNED |

## Interface Contracts

### Multi-Tenant Authorization Contract
- Native Laravel Blade directives `@can('permission.name')` and `@cannot('permission.name')` delegate via `Gate::before` in `AppServiceProvider.php` to `\App\Support\Context::hasPermission('permission.name')`.
- All controller actions enforce authorization via `HasMiddleware` and `Middleware('require.permission:...')`.
- Read-only users (`*.view`) can view listings and records, but action buttons (approve, reject, create, edit, delete, upgrade, export) MUST be hidden in Blade templates using `@can(...)`.

### Business Owner Documentation Contract
- Documentation partials located in `resources/views/app/docs/partials/sec_{module}.blade.php`.
- Each partial MUST contain:
  1. Header Bento with semantic icon and verified direct route link (`route('...')`).
  2. Executive Summary written in conversational Indonesian from Business Owner POV.
  3. 4-step practical workflow.
  4. 20-Industry Application Matrix across 6 standard clusters (F&B, Manufaktur, Ritel, Jasa Harian, Jasa Proyek, Distribusi/Agro).
  5. Security, authorization, and fraud prevention section.
  6. Practical FAQ.
- All links must use valid route names: `route('analytics.index')`, `route('approvals.inbox')`, `route('approvals.history')`, `route('approval-rules.index')`, `route('billing.limits')`, `route('billing.checkout')`, `route('billing.history')`.

## Code Layout
- Controllers:
  - `app/Http/Controllers/Web/AnalyticsWebController.php`
  - `app/Http/Controllers/Web/Approval/ApprovalWebController.php`
  - `app/Http/Controllers/Web/Billing/BillingAndLimitWebController.php`
  - `app/Http/Controllers/Web/Billing/SubscriptionCheckoutWebController.php`
  - `app/Http/Controllers/Web/DocumentationWebController.php`
- Seeders:
  - `database/seeders/RbacSeeder.php`
- Blade Views:
  - `resources/views/app/analytics/index.blade.php`
  - `resources/views/app/approvals/inbox.blade.php`
  - `resources/views/app/approvals/history.blade.php`
  - `resources/views/app/approvals/rules.blade.php`
  - `resources/views/app/billing/limits.blade.php`
  - `resources/views/app/billing/checkout.blade.php`
  - `resources/views/app/billing/payment.blade.php`
  - `resources/views/app/billing/history.blade.php`
  - `resources/views/app/billing/invoice.blade.php`
- Documentation Views:
  - `resources/views/app/docs/index.blade.php`
  - `resources/views/app/docs/partials/sec_analytics.blade.php` (new)
  - `resources/views/app/docs/partials/sec_approvals.blade.php` (new)
  - `resources/views/app/docs/partials/sec_billing.blade.php` (new)
- Tests:
  - `tests/Feature/AnalyticsWebTest.php`
  - `tests/Feature/ApprovalModuleAuditTest.php`
  - `tests/Feature/DocumentApprovalWorkflowTest.php`
  - `tests/Feature/BillingModuleAuditTest.php`
  - `tests/Feature/BillingSubmoduleSecurityAndErgonomicsTest.php`
  - `tests/Feature/ModuleAuditAndDocsTest.php` (new/extended)
