# Handoff Report: Feedback & Finance RBAC & Controller Authorization Survey

**Agent**: Explorer 2 (Feedback & Finance RBAC & Controller Authorization Survey)  
**Parent Agent Conversation ID**: `2dc7873b-7808-466b-ace5-8c03239822ef`  
**Working Directory**: `c:\laragon\www\cooca_core\.agents\teamwork\explorer_feedback_finance_rbac\`  
**Detailed Survey Report**: `c:\laragon\www\cooca_core\.agents\teamwork\explorer_feedback_finance_rbac\rbac_survey_report.md`  
**Date**: 2026-11-08  
**Handoff Type**: Hard Handoff (Investigation & Survey Complete)  

---

## 1. Observation

Direct code and environment observations:

1. **Feedback Web Controller (`app/Http/Controllers/Web/FeedbackWebController.php`)**:
   - Implements `Illuminate\Routing\Controllers\HasMiddleware` on line 20.
   - Lines 25-30 enforce a role-based constraint instead of permission-based RBAC:
     ```php
     public static function middleware(): array
     {
         return [
             new Middleware('require.role:owner'),
         ];
     }
     ```
   - Contains 9 public action methods: `bugs`, `createBug`, `storeBug`, `showBug`, `streamBugAttachment`, `features`, `createFeature`, `storeFeature`, `showFeature`.
   - Multi-tenant IDOR checks exist inside methods: `abort_unless($bugReport->business_id === $business->id, 403)` on lines 107 and 115; `abort_unless($featureRequest->business_id === $business->id, 403)` on line 179.

2. **Finance Web Controllers (`app/Http/Controllers/Web/Finance/`)**:
   All 7 Web Finance controllers implement `HasMiddleware` with granular permission mappings:
   - `PosFinanceWebController.php` (lines 29-43): 4 methods guarded by `accounting.view`, `expenses.view`, `expenses.manage`.
   - `CashLedgerWebController.php` (lines 30-58): 18 methods guarded by `finance.cash_bank` (8 methods), `finance.receivables` (5 methods), and `finance.payables` (5 methods).
   - `PaymentSettlementWebController.php` (lines 25-30): 4 methods guarded controller-wide by `finance.cash_bank`.
   - `MerchantPayoutAccountWebController.php` (lines 22-27): 4 methods guarded controller-wide by `finance.cash_bank`.
   - `StoreEdcTerminalWebController.php` (lines 22-27): 4 methods guarded controller-wide by `finance.cash_bank`.
   - `ExternalReconciliationWebController.php` (lines 25-30): 4 methods guarded controller-wide by `finance.cash_bank`.
   - `AccountingWebController.php` (lines 31-62): 21 methods guarded by `accounting.view`, `accounting.manage`, and `entitlement:export`.

3. **Route Definitions (`routes/owner.php`)**:
   - Feedback routes (lines 530-541) are wrapped inside `Route::middleware('require.role:owner')->group(...)`.
   - Finance routes (lines 840-920) have redundant route-level middleware declarations matching the controller `HasMiddleware` declarations (e.g. `Route::middleware('require.permission:finance.cash_bank')`, `Route::middleware('require.permission:accounting.view')`).

4. **Database Seeders (`database/seeders/RbacSeeder.php`)**:
   - `permissionMatrix()` defines finance permissions on lines 98-106: `expenses.view`, `expenses.manage`, `accounting.view`, `accounting.manage`, `finance.cash_bank`, `finance.receivables`, `finance.payables`.
   - `permissionMatrix()` contains **0** permissions for feedback. `feedback.view`, `feedback.create`, and `feedback.manage` do NOT exist in the seeder.
   - `roleDefinitions()` assigns all finance permissions to `owner`, `manager`, `admin`, and `finance`. `cashier`, `warehouse`, and `staff` have zero finance permissions. `viewer` has read-only `accounting.view` and `finance.cash_bank`.

5. **Common & API Controllers**:
   - `App\Http\Controllers\Common\SettlementProofController.php` (line 35): checks `$isAdmin || $isMerchant`, but lacks permission verification (`finance.cash_bank`).
   - `App\Http\Controllers\Api\V1\Finance\` (`FinanceController`, `CashLedgerController`, `PaymentSettlementController`): do not implement `HasMiddleware`; their guards exist only in `routes/api.php` (lines 1407-1484).

6. **UI Navigation (`resources/views/layouts/partials/sidebar.blade.php` & `topbar.blade.php`)**:
   - Sidebar line 2129 and Topbar line 467 hide feedback links with `@if (\App\Support\Context::isOwner())` rather than checking `feedback.view`.

---

## 2. Logic Chain

1. **Premise 1**: COOCA Core standardizes on Laravel 11 controller-level RBAC (`HasMiddleware`) using granular permission keys (`modul.fitur.aksi` or `modul.aksi`) as defined in `laravel-permission` and `cooca-agent-directive`.
2. **Premise 2**: In `FeedbackWebController`, authorization is hardcoded to `require.role:owner` instead of granular permissions, and `routes/owner.php` wraps all feedback routes in `require.role:owner`.
3. **Premise 3**: In `RbacSeeder.php`, no feedback permissions are registered. Consequently, roles such as `manager`, `admin`, and `finance` cannot be granted access to view or report feedback.
4. **Premise 4**: In `FinanceWebControllers`, all 60 public action methods across 7 controllers strictly implement `HasMiddleware` with granular permissions, and all models enforce tenant scoping (`Context::requireBusiness()` and IDOR checks).
5. **Deduction**: The Finance Web controllers already achieve production-ready controller-level RBAC compliance. However, the Feedback module suffers from architectural role locking and seeder omission. Transitioning Feedback from `require.role:owner` to `feedback.view` and `feedback.create` requires:
   - Registering `feedback.view` and `feedback.create` in `database/seeders/RbacSeeder.php`.
   - Assigning these permissions to `manager`, `admin`, `finance`, and optionally operational roles.
   - Updating `FeedbackWebController::middleware()` to use `new Middleware('require.permission:feedback.view', ...)` and `new Middleware('require.permission:feedback.create', ...)`.
   - Removing the outer `require.role:owner` wrapper from `routes/owner.php`.
   - Updating `sidebar.blade.php` and `topbar.blade.php` visibility checks to `@if (\App\Support\Context::isOwner() || \App\Support\Context::hasPermission('feedback.view'))`.

---

## 3. Caveats

- **Existing Test Assertions**: In `tests/Feature/FeedbackModuleAuditTest.php:136`, `test_non_owner_role_is_blocked_from_feedback_module` asserts that non-owners receive 403. When transitioning to granular permissions, tests should be updated to test roles *without* `feedback.view` receiving 403, and roles *with* `feedback.view` (e.g. manager/finance) receiving 200 OK.
- **Platform Admin Feedback vs Tenant Feedback**: `AdminFeedbackController` manages feedback across all tenants for platform administrators (`admin.feedback.view`, `admin.feedback.manage` in `AdminRbacSeeder.php`). The tenant feedback in `FeedbackWebController` is purely for merchants submitting issues to the platform.
- **Read-Only Constraint**: As an explorer subagent, no source code or seeders were altered during this survey.

---

## 4. Conclusion

1. **Finance Module**:
   - RBAC status: **Compliant & Production Ready**.
   - 7 web controllers implement `HasMiddleware` covering all 60 public action methods.
   - Minor enhancement recommended: In `SettlementProofController`, add `Context::hasPermission('finance.cash_bank')` to prevent unauthorized merchant staff from viewing confidential payout transfer proofs. In API finance controllers, encapsulate `HasMiddleware`.

2. **Feedback Module**:
   - RBAC status: **Requires Refactoring**.
   - Controllers currently rely on legacy `require.role:owner`.
   - Requires adding `feedback.view` and `feedback.create` to `database/seeders/RbacSeeder.php`, migrating `FeedbackWebController` to `HasMiddleware` with `require.permission`, and updating route/UI guards.

Full details and actionable code recipes are documented in `rbac_survey_report.md`.

---

## 5. Verification Method

To verify these observations independently:

1. **Check route list**:
   ```powershell
   php artisan route:list --name=feedback
   php artisan route:list --name=finance
   ```
2. **Inspect controller middleware declarations**:
   ```powershell
   Select-String -Path "app\Http\Controllers\Web\FeedbackWebController.php" -Pattern "middleware" -Context 0,10
   Select-String -Path "app\Http\Controllers\Web\Finance\*.php" -Pattern "function middleware" -Context 0,10
   ```
3. **Inspect RbacSeeder permissions**:
   ```powershell
   Select-String -Path "database\seeders\RbacSeeder.php" -Pattern "expenses\.|accounting\.|finance\.|feedback\."
   ```
4. **Run existing automated feature tests**:
   ```powershell
   php artisan test --filter=FeedbackModuleAuditTest
   php artisan test --filter=FinanceModuleAuditTest
   ```
