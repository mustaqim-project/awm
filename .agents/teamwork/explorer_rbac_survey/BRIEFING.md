# BRIEFING — 2026-11-07T09:32:00Z

## Mission
Investigate Role-Based Access Control (RBAC) across analytics, approvals, and billing modules in Cooca codebase.

## 🔒 My Identity
- Archetype: explorer
- Roles: investigation, synthesis
- Working directory: c:\laragon\www\cooca_core\.agents\teamwork\explorer_rbac_survey
- Original parent: 58fe913b-8bbe-4705-a472-1a575b67e3a5
- Milestone: RBAC Authorization Survey for Analytics, Approvals, Billing

## 🔒 Key Constraints
- Read-only investigation — do NOT implement
- Investigate routes, controllers, guards, seeders, and blade views for analytics, approvals, and billing
- Deliver findings in handoff.md

## Current Parent
- Conversation ID: 58fe913b-8bbe-4705-a472-1a575b67e3a5
- Updated: not yet

## Investigation State
- **Explored paths**:
  - `routes/owner.php`, `routes/api.php`, `routes/web.php`
  - `app/Http/Controllers/Web/AnalyticsWebController.php`
  - `app/Http/Controllers/Web/Approval/ApprovalWebController.php`
  - `app/Http/Controllers/Web/Billing/BillingAndLimitWebController.php`
  - `app/Http/Controllers/Web/Billing/SubscriptionCheckoutWebController.php`
  - `app/Providers/AppServiceProvider.php` (Gate::before integration)
  - `app/Support/Context.php` (Context::hasPermission & tenant isolation)
  - `app/Http/Middleware/RequirePermission.php`
  - `database/seeders/RbacSeeder.php`
  - `resources/views/app/analytics/index.blade.php`
  - `resources/views/app/approvals/inbox.blade.php`, `history.blade.php`, `rules.blade.php`
  - `resources/views/app/billing/limits.blade.php`, `checkout.blade.php`, `payment.blade.php`, `history.blade.php`, `invoice.blade.php`
  - `resources/views/layouts/partials/sidebar.blade.php`
  - `tests/Feature/AnalyticsWebTest.php`, `ApprovalModuleAuditTest.php`, `BillingModuleAuditTest.php`
- **Key findings**:
  - `Gate::before` in `AppServiceProvider.php` automatically connects `@can(...)` in Blade to `Context::hasPermission(...)`.
  - Analytics lacks dedicated `analytics.view` / `analytics.export` permissions in `RbacSeeder.php`; currently borrows `reports.view`. Margin/profit calculations are displayed without checking financial/margin permissions (`costing.view_margin` or `reports.financial`).
  - Approvals bundles both ticket approval/rejection and rule management under `approvals.manage`. In `inbox.blade.php`, Approve and Reject buttons lack any view guards (`@can`), causing unauthorized users to see actionable buttons that throw 403 upon submission.
  - Billing controllers implement clean `HasMiddleware` and strict tenant IDOR checks (`abort_unless($payment->business_id === $business->id, 403)`). In `limits.blade.php`, pricing tier selection buttons in the 4-tier comparison grid lack `@can('billing.manage')` guards, exposing broken upgrade links to read-only viewers.
- **Unexplored areas**: None, full survey complete.

## Key Decisions Made
- Recommending granular permissions: `analytics.view`, `analytics.export`, `approvals.action` (or granular separation of `approvals.manage` vs `approvals.rules.manage`).
- Recommending standardization of Blade guards to native `@can` directive which works via `Gate::before`.

## Artifact Index
- handoff.md — Comprehensive RBAC survey report for analytics, approvals, billing
- progress.md — Heartbeat and progress log
- DISPATCH.md — Received directives
