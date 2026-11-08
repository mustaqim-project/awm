# Comprehensive RBAC & Controller Authorization Survey Report
**Target Modules**: Feedback (`app/feedback`) & Finance (`app/finance`)  
**Repository**: COOCA Core (`c:\laragon\www\cooca_core`)  
**Auditor**: Teamwork Explorer Subagent (Explorer 2: Feedback & Finance RBAC Survey)  
**Date**: 2026-11-08  
**Status**: Comprehensive Read-Only Audit Complete  

---

## 1. Executive Summary

An exhaustive investigation was conducted across all controllers, action methods, route declarations, middleware pipelines, and database seeders governing the **Feedback** and **Finance** modules within the COOCA Core multi-tenant ERP platform.

### Core Survey Verdict
1. **Finance Web Controllers (High Compliance)**:
   - All 7 Web Finance controllers (`PosFinanceWebController`, `CashLedgerWebController`, `PaymentSettlementWebController`, `MerchantPayoutAccountWebController`, `StoreEdcTerminalWebController`, `ExternalReconciliationWebController`, and `AccountingWebController`) implement Laravel 11's modern `Illuminate\Routing\Controllers\HasMiddleware` interface.
   - A total of **60 public action methods** across these 7 controllers are strictly guarded by granular permissions (`accounting.view`, `accounting.manage`, `expenses.view`, `expenses.manage`, `finance.cash_bank`, `finance.receivables`, `finance.payables`) and entitlements (`entitlement:export`).
   - Every single tenant mutation and resource view enforces strict multi-tenant scoping via `Context::requireBusiness()` and `abort_unless($model->business_id === $business->id, 403)`, providing robust IDOR defense.

2. **Feedback Web Controller (Architectural & Authorization Deficit)**:
   - `FeedbackWebController` currently implements `HasMiddleware`, but enforces a monolithic role constraint: `new Middleware('require.role:owner')`.
   - **Zero granular permissions** exist for Feedback in `database/seeders/RbacSeeder.php`. Permissions like `feedback.view` and `feedback.create` are completely missing.
   - Other legitimate operational roles (e.g. `manager`, `admin`, `finance`, or custom business roles) are strictly locked out (403 Forbidden) from submitting bug reports or viewing feedback.
   - The UI navigation links in `sidebar.blade.php` and `topbar.blade.php` hardcode `@if (\App\Support\Context::isOwner())`, ignoring potential role-based delegability.

3. **API & Ancillary Controllers (Encapsulation Gaps)**:
   - API controllers under `App\Http\Controllers\Api\V1\Finance\` (`FinanceController`, `CashLedgerController`, `PaymentSettlementController`) rely exclusively on route-level middleware in `routes/api.php` and do **not** implement `HasMiddleware`.
   - `SettlementProofController` (`routes/web.php:32`) allows any authenticated user belonging to the tenant business to stream payout proof documents without verifying `finance.cash_bank` permission.

---

## 2. Controller Inventory & Action Method Matrix

### 2.1 Feedback Controllers

#### `App\Http\Controllers\Web\FeedbackWebController`
- **File**: `app/Http/Controllers/Web/FeedbackWebController.php`
- **Interface**: `HasMiddleware` implemented.
- **Current Middleware**: `new Middleware('require.role:owner')` (Applied to all actions).
- **Public Action Methods**: 9 methods.

| # | Action Method | HTTP Verb | Route Name | Current Guard | Target Granular Permission | Tenant IDOR Check |
|---|---|---|---|---|---|---|
| 1 | `bugs(Request $request)` | `GET` | `feedback.bugs.index` | `require.role:owner` | `feedback.view` | `where('business_id', $business->id)` |
| 2 | `createBug()` | `GET` | `feedback.bugs.create` | `require.role:owner` | `feedback.create` | N/A (Static view) |
| 3 | `storeBug(Request $request)` | `POST` | `feedback.bugs.store` | `require.role:owner` | `feedback.create` | `$validated['business_id'] = $business->id` |
| 4 | `showBug(BugReport $bugReport)` | `GET` | `feedback.bugs.show` | `require.role:owner` | `feedback.view` | `abort_unless($bugReport->business_id === $business->id, 403)` |
| 5 | `streamBugAttachment(BugReport $bugReport)` | `GET` | `feedback.bugs.attachment` | `require.role:owner` | `feedback.view` | `abort_unless($bugReport->business_id === $business->id, 403)` |
| 6 | `features(Request $request)` | `GET` | `feedback.features.index` | `require.role:owner` | `feedback.view` | `where('business_id', $business->id)` |
| 7 | `createFeature()` | `GET` | `feedback.features.create` | `require.role:owner` | `feedback.create` | N/A (Static view) |
| 8 | `storeFeature(Request $request)` | `POST` | `feedback.features.store` | `require.role:owner` | `feedback.create` | `$validated['business_id'] = $business->id` |
| 9 | `showFeature(FeatureRequest $featureRequest)` | `GET` | `feedback.features.show` | `require.role:owner` | `feedback.view` | `abort_unless($featureRequest->business_id === $business->id, 403)` |

---

### 2.2 Finance Web Controllers

#### 1. `App\Http\Controllers\Web\Finance\PosFinanceWebController`
- **File**: `app/Http/Controllers/Web/Finance/PosFinanceWebController.php`
- **Interface**: `HasMiddleware` implemented.
- **Public Action Methods**: 4 methods.

| # | Action Method | HTTP Verb | Route Name | Controller Middleware Guard | Tenant Scoping / IDOR |
|---|---|---|---|---|---|
| 1 | `journals(Request $request)` | `GET` | `finance.journals.index` | `require.permission:accounting.view` | `where('business_id', $business->id)` |
| 2 | `expenses(Request $request)` | `GET` | `finance.expenses.index` | `require.permission:expenses.view` | `where('business_id', $business->id)` |
| 3 | `storeExpense(Request $request)` | `POST` | `finance.expenses.store` | `require.permission:expenses.manage` | Scoped insertion + auto-journal |
| 4 | `viewReceipt(Expense $expense)` | `GET` | `finance.expenses.receipt` | `require.permission:expenses.view` | `abort_unless($expense->business_id === $business->id, 403)` |

#### 2. `App\Http\Controllers\Web\Finance\CashLedgerWebController`
- **File**: `app/Http/Controllers/Web/Finance/CashLedgerWebController.php`
- **Interface**: `HasMiddleware` implemented.
- **Public Action Methods**: 18 methods.

| # | Action Method | HTTP Verb | Route Name | Controller Middleware Guard | Tenant Scoping / IDOR |
|---|---|---|---|---|---|
| 1 | `index(Request $request)` | `GET` | `finance.cash-bank.index` | `require.permission:finance.cash_bank` | `where('business_id', $business->id)` |
| 2 | `ledger(Request $request)` | `GET` | `finance.cash-bank.ledger` | `require.permission:finance.cash_bank` | `where('business_id', $business->id)` |
| 3 | `storeInflow(Request $request)` | `POST` | `finance.cash-bank.inflow` | `require.permission:finance.cash_bank` | Scoped via `CashLedgerService` |
| 4 | `storeOutflow(Request $request)` | `POST` | `finance.cash-bank.outflow` | `require.permission:finance.cash_bank` | Scoped via `CashLedgerService` |
| 5 | `transfer(Request $request)` | `POST` | `finance.cash-bank.transfer` | `require.permission:finance.cash_bank` | Scoped accounts verification |
| 6 | `storeAccount(Request $request)` | `POST` | `finance.cash-bank.accounts.store` | `require.permission:finance.cash_bank` | `where('business_id', $business->id)` |
| 7 | `updateAccount(Request $request, CashAccount $account)` | `PUT` | `finance.cash-bank.accounts.update` | `require.permission:finance.cash_bank` | `abort_if($account->business_id !== $business->id, 403)` |
| 8 | `toggleAccount(CashAccount $account)` | `POST` | `finance.cash-bank.accounts.toggle` | `require.permission:finance.cash_bank` | `abort_if($account->business_id !== $business->id, 403)` |
| 9 | `receivables(Request $request)` | `GET` | `finance.receivables` | `require.permission:finance.receivables` | `where('business_id', $business->id)` |
| 10 | `exportReceivables()` | `GET` | `finance.receivables.export` | `require.permission:finance.receivables` | Scoped `DataExchangeService` |
| 11 | `receivablesTemplate()` | `GET` | `finance.receivables.template` | `require.permission:finance.receivables` | Template generation |
| 12 | `previewReceivables(Request $request)` | `POST` | `finance.receivables.import-preview` | `require.permission:finance.receivables` | Scoped parsing |
| 13 | `executeReceivables(Request $request)` | `POST` | `finance.receivables.import-execute` | `require.permission:finance.receivables` | Scoped execution |
| 14 | `payables(Request $request)` | `GET` | `finance.payables` | `require.permission:finance.payables` | `where('business_id', $business->id)` |
| 15 | `exportPayables()` | `GET` | `finance.payables.export` | `require.permission:finance.payables` | Scoped `DataExchangeService` |
| 16 | `payablesTemplate()` | `GET` | `finance.payables.template` | `require.permission:finance.payables` | Template generation |
| 17 | `previewPayables(Request $request)` | `POST` | `finance.payables.import-preview` | `require.permission:finance.payables` | Scoped parsing |
| 18 | `executePayables(Request $request)` | `POST` | `finance.payables.import-execute` | `require.permission:finance.payables` | Scoped execution |

#### 3. `App\Http\Controllers\Web\Finance\PaymentSettlementWebController`
- **File**: `app/Http/Controllers/Web/Finance/PaymentSettlementWebController.php`
- **Interface**: `HasMiddleware` implemented.
- **Public Action Methods**: 4 methods.

| # | Action Method | HTTP Verb | Route Name | Controller Middleware Guard | Tenant Scoping / IDOR |
|---|---|---|---|---|---|
| 1 | `index(Request $request)` | `GET` | `finance.settlements.index` | `require.permission:finance.cash_bank` (controller-wide) | `where('business_id', $business->id)` |
| 2 | `getUnsettled()` | `GET` | `finance.settlements.unsettled` | `require.permission:finance.cash_bank` (controller-wide) | Scoped via service |
| 3 | `reconcile(Request $request)` | `POST` | `finance.settlements.reconcile` | `require.permission:finance.cash_bank` (controller-wide) | Verified account validation |
| 4 | `show(PaymentSettlement $settlement)` | `GET` | `finance.settlements.show` | `require.permission:finance.cash_bank` (controller-wide) | `abort_if($settlement->business_id !== $business->id, 403)` |

#### 4. `App\Http\Controllers\Web\Finance\MerchantPayoutAccountWebController`
- **File**: `app/Http/Controllers/Web/Finance/MerchantPayoutAccountWebController.php`
- **Interface**: `HasMiddleware` implemented.
- **Public Action Methods**: 4 methods.

| # | Action Method | HTTP Verb | Route Name | Controller Middleware Guard | Tenant Scoping / IDOR |
|---|---|---|---|---|---|
| 1 | `index(Request $request)` | `GET` | `finance.payout-accounts.index` | `require.permission:finance.cash_bank` (controller-wide) | `where('business_id', $business->id)` |
| 2 | `store(Request $request)` | `POST` | `finance.payout-accounts.store` | `require.permission:finance.cash_bank` (controller-wide) | Scoped registration |
| 3 | `setPrimary(MerchantPayoutBankAccount $account)` | `PATCH` | `finance.payout-accounts.set-primary` | `require.permission:finance.cash_bank` (controller-wide) | `abort_if($account->business_id !== $business->id, 403)` |
| 4 | `destroy(MerchantPayoutBankAccount $account)` | `DELETE` | `finance.payout-accounts.destroy` | `require.permission:finance.cash_bank` (controller-wide) | `abort_if($account->business_id !== $business->id, 403)` |

#### 5. `App\Http\Controllers\Web\Finance\StoreEdcTerminalWebController`
- **File**: `app/Http/Controllers/Web/Finance/StoreEdcTerminalWebController.php`
- **Interface**: `HasMiddleware` implemented.
- **Public Action Methods**: 4 methods.

| # | Action Method | HTTP Verb | Route Name | Controller Middleware Guard | Tenant Scoping / IDOR |
|---|---|---|---|---|---|
| 1 | `index(Request $request)` | `GET` | `finance.edc-terminals.index` | `require.permission:finance.cash_bank` (controller-wide) | `where('business_id', $business->id)` |
| 2 | `store(Request $request)` | `POST` | `finance.edc-terminals.store` | `require.permission:finance.cash_bank` (controller-wide) | Scoped registration |
| 3 | `toggle(StoreEdcTerminal $terminal)` | `PATCH` | `finance.edc-terminals.toggle` | `require.permission:finance.cash_bank` (controller-wide) | `abort_if($terminal->business_id !== $business->id, 403)` |
| 4 | `destroy(StoreEdcTerminal $terminal)` | `DELETE` | `finance.edc-terminals.destroy` | `require.permission:finance.cash_bank` (controller-wide) | `abort_if($terminal->business_id !== $business->id, 403)` |

#### 6. `App\Http\Controllers\Web\Finance\ExternalReconciliationWebController`
- **File**: `app/Http/Controllers/Web/Finance/ExternalReconciliationWebController.php`
- **Interface**: `HasMiddleware` implemented.
- **Public Action Methods**: 4 methods.

| # | Action Method | HTTP Verb | Route Name | Controller Middleware Guard | Tenant Scoping / IDOR |
|---|---|---|---|---|---|
| 1 | `index(Request $request)` | `GET` | `finance.external-recon.index` | `require.permission:finance.cash_bank` (controller-wide) | `where('business_id', $business->id)` |
| 2 | `store(Request $request)` | `POST` | `finance.external-recon.store` | `require.permission:finance.cash_bank` (controller-wide) | Scoped upsert |
| 3 | `dashboard(Request $request)` | `GET` | `finance.liquidity-dashboard` | `require.permission:finance.cash_bank` (controller-wide) | Scoped calculation |
| 4 | `discrepancies(Request $request)` | `GET` | `finance.discrepancies` | `require.permission:finance.cash_bank` (controller-wide) | Scoped discrepancy report |

#### 7. `App\Http\Controllers\Web\Finance\AccountingWebController`
- **File**: `app/Http/Controllers/Web/Finance/AccountingWebController.php`
- **Interface**: `HasMiddleware` implemented.
- **Public Action Methods**: 21 methods.

| # | Action Method | HTTP Verb | Route Name | Controller Middleware Guard | Tenant Scoping / IDOR |
|---|---|---|---|---|---|
| 1 | `coaIndex(Request $request)` | `GET` | `finance.coa.index` | `require.permission:accounting.view` | `where('business_id', $business->id)` |
| 2 | `coaStore(Request $request)` | `POST` | `finance.coa.store` | `require.permission:accounting.manage` | Scoped creation |
| 3 | `coaUpdate(Request $request, ChartOfAccount $account)` | `PUT` | `finance.coa.update` | `require.permission:accounting.manage` | `abort_if($account->business_id !== $business->id, 403)` |
| 4 | `coaDestroy(ChartOfAccount $account)` | `DELETE` | `finance.coa.destroy` | `require.permission:accounting.manage` | `abort_if($account->business_id !== $business->id, 403)` |
| 5 | `balanceSheet(Request $request)` | `GET` | `finance.balance-sheet` | `require.permission:accounting.view` | Scoped report calculation |
| 6 | `trialBalance(Request $request)` | `GET` | `finance.trial-balance` | `require.permission:accounting.view` | Scoped report calculation |
| 7 | `generalLedger(Request $request)` | `GET` | `finance.general-ledger` | `require.permission:accounting.view` | Scoped report calculation |
| 8 | `balanceSheetExportExcel(Request $request)` | `GET` | `finance.balance-sheet.export-excel` | `['require.permission:accounting.view', 'entitlement:export']` | Scoped export |
| 9 | `trialBalanceExportExcel(Request $request)` | `GET` | `finance.trial-balance.export-excel` | `['require.permission:accounting.view', 'entitlement:export']` | Scoped export |
| 10 | `generalLedgerExportExcel(Request $request)` | `GET` | `finance.general-ledger.export-excel` | `['require.permission:accounting.view', 'entitlement:export']` | Scoped export |
| 11 | `reconciliationIndex(Request $request)` | `GET` | `finance.reconciliations.index` | `require.permission:accounting.view` | `where('business_id', $business->id)` |
| 12 | `reconciliationUpload(Request $request)` | `POST` | `finance.reconciliations.upload` | `require.permission:accounting.manage` | Scoped cash account check |
| 13 | `reconciliationMatch(Request $request, BankStatementLine $line)` | `POST` | `finance.reconciliations.match` | `require.permission:accounting.manage` | `abort_if($line->business_id !== $business->id, 403)` |
| 14 | `reconciliationUnmatch(Request $request, BankStatementLine $line)` | `POST` | `finance.reconciliations.unmatch` | `require.permission:accounting.manage` | `abort_if($line->business_id !== $business->id, 403)` |
| 15 | `coaExport()` | `GET` | `finance.coa.export` | `require.permission:accounting.view` | Scoped export |
| 16 | `coaTemplate()` | `GET` | `finance.coa.template` | `require.permission:accounting.view` | Template generation |
| 17 | `coaImportPreview(Request $request)` | `POST` | `finance.coa.import-preview` | `require.permission:accounting.view` | Scoped preview |
| 18 | `coaImportExecute(Request $request)` | `POST` | `finance.coa.import-execute` | `require.permission:accounting.manage` | Scoped import |
| 19 | `openingBalanceTemplate()` | `GET` | `finance.balance-sheet.opening-template` | `require.permission:accounting.view` | Template generation |
| 20 | `openingBalancePreview(Request $request)` | `POST` | `finance.balance-sheet.opening-preview` | `require.permission:accounting.view` | Scoped preview |
| 21 | `openingBalanceExecute(Request $request)` | `POST` | `finance.balance-sheet.opening-execute` | `require.permission:accounting.manage` | Scoped import |

---

### 2.3 Common & API Controllers

#### 1. `App\Http\Controllers\Common\SettlementProofController`
- **File**: `app/Http/Controllers/Common/SettlementProofController.php`
- **Route**: `GET /settlements/{settlement}/proof` (`settlements.proof` in `routes/web.php:32`)
- **Status**: Does NOT implement `HasMiddleware`.
- **Authorization**: Custom in-method logic:
  ```php
  $isAdmin = Auth::guard('admin')->check();
  $isMerchant = false;
  if (! $isAdmin && Auth::guard('web')->check()) {
      $user = Auth::guard('web')->user();
      if ($user && ($user->current_business_id === $settlement->business_id || $user->business_id === $settlement->business_id)) {
          $isMerchant = true;
      }
  }
  abort_unless($isAdmin || $isMerchant, 403);
  ```
- **Finding**: While cross-tenant IDOR is prevented, **any tenant user** (even a cashier or staff member) can view sensitive bank transfer payout documents because no permission check (such as `finance.cash_bank`) is performed.

#### 2. API Finance Controllers
- `App\Http\Controllers\Api\V1\Finance\FinanceController` (5 actions: `dashboard`, `expenses`, `storeExpense`, `chartOfAccounts`, `journalEntries`)
- `App\Http\Controllers\Api\V1\Finance\CashLedgerController` (5 actions: `accounts`, `transactions`, `inflow`, `outflow`, `transfer`)
- `App\Http\Controllers\Api\V1\Finance\PaymentSettlementController` (3 actions: `index`, `show`, `reconcile`)
- **Status**: None of these API controllers implement `HasMiddleware`. Protection is currently applied exclusively at route level in `routes/api.php` (lines 1407-1484).

---

## 3. RBAC & Controller-Level Authorization Audit Findings

### Finding 1: Monolithic `require.role:owner` in `FeedbackWebController`
- **Observation**:
  `FeedbackWebController::middleware()` declares:
  ```php
  public static function middleware(): array
  {
      return [
          new Middleware('require.role:owner'),
      ];
  }
  ```
  In `routes/owner.php` lines 530-541:
  ```php
  Route::middleware('require.role:owner')->group(function (): void {
      Route::get('/feedback/bugs', [FeedbackWebController::class, 'bugs'])->name('feedback.bugs.index');
      ...
  });
  ```
- **Risk & Flaw**:
  Hardcoding `require.role:owner` violates the COOCA modular permission architecture. Legitimate business administrators (`admin`), store managers (`manager`), and accounting heads (`finance`) who observe system defects or require feature improvements cannot submit tickets directly.
- **Recommended Remedy**:
  Migrate `FeedbackWebController` to use standard permission guards:
  ```php
  public static function middleware(): array
  {
      return [
          new Middleware('require.permission:feedback.view', only: [
              'bugs',
              'showBug',
              'streamBugAttachment',
              'features',
              'showFeature',
          ]),
          new Middleware('require.permission:feedback.create', only: [
              'createBug',
              'storeBug',
              'createFeature',
              'storeFeature',
          ]),
      ];
  }
  ```

---

### Finding 2: Missing Permissions in `database/seeders/RbacSeeder.php`
- **Observation**:
  Inspection of `RbacSeeder.php` reveals **zero** permissions defined for the `feedback` module in `permissionMatrix()`.
- **Existing Permissions in RbacSeeder**:
  - `expenses.view`
  - `expenses.manage`
  - `accounting.view`
  - `accounting.manage`
  - `finance.cash_bank`
  - `finance.receivables`
  - `finance.payables`
- **Missing Required Permissions**:
  ```php
  // ─── Feedback & Dukungan Platform ─────────────────────────
  'feedback.view'   => ['Lihat Umpan Balik & Masukan', 'support', 'Melihat daftar laporan bug dan usulan fitur ke tim COOCA.'],
  'feedback.create' => ['Kirim Laporan Bug & Fitur',   'support', 'Mengirimkan laporan kendala sistem, bug, dan usulan fitur baru.'],
  ```
- **Role Parity Impact**:
  Once registered in `permissionMatrix()`, default role assignments in `roleDefinitions()` should be updated:
  - `owner`: Automatically inherits all permission slugs (`$ownerSlugs = $allSlugs`).
  - `manager`: Add `feedback.view` and `feedback.create`.
  - `admin`: Inherits from `managerSlugs`, so will automatically receive `feedback.view` and `feedback.create`.
  - `finance`: Add `feedback.view` and `feedback.create`.
  - `cashier`, `warehouse`, `staff`: Add `feedback.create` (allowing employees to report operational POS or inventory bugs directly).
  - `viewer`: Add `feedback.view`.

---

### Finding 3: Route Middleware Redundancy vs Encapsulation (Pilar 2)
- **Observation**:
  In `routes/owner.php`, routes for Finance and Feedback specify route-level middleware that duplicates the controllers' internal `HasMiddleware` declarations:
  - `Route::get('/finance/journals', ...)->middleware('require.permission:accounting.view')`
  - `Route::get('/finance/expenses', ...)->middleware('require.permission:expenses.view')`
  - `Route::post('/finance/expenses', ...)->middleware('require.permission:expenses.manage')`
  - `Route::middleware('require.permission:finance.cash_bank')->group(...)`
  - `Route::middleware('require.permission:accounting.view')->group(...)`
- **Architectural Assessment**:
  Per the `laravel-permission` skill and Laravel 11 clean routing standard, once controllers encapsulate their authorization rules via `HasMiddleware`, redundant route-level middleware declarations can be simplified to maintain DRY routing.

---

### Finding 4: UI Navigation Hiding via `Context::isOwner()`
- **Observation**:
  In `resources/views/layouts/partials/sidebar.blade.php` (line 2129) and `resources/views/layouts/partials/topbar.blade.php` (line 467):
  ```blade
  @if (\App\Support\Context::isOwner())
      <a href="{{ route('feedback.bugs.index') }}" ...>
  @endif
  ```
- **Impact**:
  Even after controller-level RBAC is migrated to `feedback.view`, non-owner staff will still have the navigation link hidden in the sidebar and profile dropdown.
- **Remedy**:
  Update Blade conditional to:
  ```blade
  @if (\App\Support\Context::isOwner() || \App\Support\Context::hasPermission('feedback.view'))
  ```

---

### Finding 5: `SettlementProofController` Missing Permission Check
- **Observation**:
  In `app/Http/Controllers/Common/SettlementProofController.php`:
  ```php
  abort_unless($isAdmin || $isMerchant, 403);
  ```
  `$isMerchant` is evaluated purely on whether the user belongs to the business tenant (`$user->current_business_id === $settlement->business_id`).
- **Risk**:
  A cashier or junior staff member knowing the settlement ID can view confidential bank transfer slips uploaded by platform administrators.
- **Remedy**:
  Add permission verification:
  ```php
  $hasPermission = Context::isAdminOrOwner() || Context::hasPermission('finance.cash_bank');
  abort_unless($isAdmin || ($isMerchant && $hasPermission), 403, 'Akses bukti transfer ditolak.');
  ```

---

## 4. Route Alignment Matrix

| Route URL | Method | Controller & Action | Route Middleware | Controller `HasMiddleware` Guard | Status |
|---|---|---|---|---|---|
| `/feedback/bugs` | GET | `FeedbackWebController@bugs` | `require.role:owner` | `require.role:owner` | Misaligned with Granular RBAC |
| `/feedback/bugs/create` | GET | `FeedbackWebController@createBug` | `require.role:owner` | `require.role:owner` | Misaligned with Granular RBAC |
| `/feedback/bugs` | POST | `FeedbackWebController@storeBug` | `require.role:owner` | `require.role:owner` | Misaligned with Granular RBAC |
| `/feedback/bugs/{bugReport}` | GET | `FeedbackWebController@showBug` | `require.role:owner` | `require.role:owner` | Misaligned with Granular RBAC |
| `/feedback/bugs/{bugReport}/attachment` | GET | `FeedbackWebController@streamBugAttachment` | `require.role:owner` | `require.role:owner` | Misaligned with Granular RBAC |
| `/feedback/features` | GET | `FeedbackWebController@features` | `require.role:owner` | `require.role:owner` | Misaligned with Granular RBAC |
| `/feedback/features/create` | GET | `FeedbackWebController@createFeature` | `require.role:owner` | `require.role:owner` | Misaligned with Granular RBAC |
| `/feedback/features` | POST | `FeedbackWebController@storeFeature` | `require.role:owner` | `require.role:owner` | Misaligned with Granular RBAC |
| `/feedback/features/{featureRequest}` | GET | `FeedbackWebController@showFeature` | `require.role:owner` | `require.role:owner` | Misaligned with Granular RBAC |
| `/finance/journals` | GET | `PosFinanceWebController@journals` | `require.permission:accounting.view` | `require.permission:accounting.view` | Fully Aligned |
| `/finance/expenses` | GET | `PosFinanceWebController@expenses` | `require.permission:expenses.view` | `require.permission:expenses.view` | Fully Aligned |
| `/finance/expenses` | POST | `PosFinanceWebController@storeExpense` | `require.permission:expenses.manage` | `require.permission:expenses.manage` | Fully Aligned |
| `/finance/expenses/{expense}/receipt` | GET | `PosFinanceWebController@viewReceipt` | `require.permission:expenses.view` | `require.permission:expenses.view` | Fully Aligned |
| `/finance/cash-bank` | GET | `CashLedgerWebController@index` | `require.permission:finance.cash_bank` | `require.permission:finance.cash_bank` | Fully Aligned |
| `/finance/cash-bank/ledger` | GET | `CashLedgerWebController@ledger` | `require.permission:finance.cash_bank` | `require.permission:finance.cash_bank` | Fully Aligned |
| `/finance/cash-bank/inflow` | POST | `CashLedgerWebController@storeInflow` | `require.permission:finance.cash_bank` | `require.permission:finance.cash_bank` | Fully Aligned |
| `/finance/cash-bank/outflow` | POST | `CashLedgerWebController@storeOutflow` | `require.permission:finance.cash_bank` | `require.permission:finance.cash_bank` | Fully Aligned |
| `/finance/cash-bank/transfer` | POST | `CashLedgerWebController@transfer` | `require.permission:finance.cash_bank` | `require.permission:finance.cash_bank` | Fully Aligned |
| `/finance/settlements` | GET | `PaymentSettlementWebController@index` | `require.permission:finance.cash_bank` | `require.permission:finance.cash_bank` | Fully Aligned |
| `/finance/settlements/reconcile` | POST | `PaymentSettlementWebController@reconcile` | `require.permission:finance.cash_bank` | `require.permission:finance.cash_bank` | Fully Aligned |
| `/finance/settlements/{settlement}` | GET | `PaymentSettlementWebController@show` | `require.permission:finance.cash_bank` | `require.permission:finance.cash_bank` | Fully Aligned |
| `/finance/payout-accounts` | GET | `MerchantPayoutAccountWebController@index` | `require.permission:finance.cash_bank` | `require.permission:finance.cash_bank` | Fully Aligned |
| `/finance/payout-accounts` | POST | `MerchantPayoutAccountWebController@store` | `require.permission:finance.cash_bank` | `require.permission:finance.cash_bank` | Fully Aligned |
| `/finance/payout-accounts/{account}/primary` | PATCH | `MerchantPayoutAccountWebController@setPrimary` | `require.permission:finance.cash_bank` | `require.permission:finance.cash_bank` | Fully Aligned |
| `/finance/payout-accounts/{account}` | DELETE | `MerchantPayoutAccountWebController@destroy` | `require.permission:finance.cash_bank` | `require.permission:finance.cash_bank` | Fully Aligned |
| `/finance/edc-terminals` | GET | `StoreEdcTerminalWebController@index` | `require.permission:finance.cash_bank` | `require.permission:finance.cash_bank` | Fully Aligned |
| `/finance/edc-terminals` | POST | `StoreEdcTerminalWebController@store` | `require.permission:finance.cash_bank` | `require.permission:finance.cash_bank` | Fully Aligned |
| `/finance/edc-terminals/{terminal}/toggle` | PATCH | `StoreEdcTerminalWebController@toggle` | `require.permission:finance.cash_bank` | `require.permission:finance.cash_bank` | Fully Aligned |
| `/finance/edc-terminals/{terminal}` | DELETE | `StoreEdcTerminalWebController@destroy` | `require.permission:finance.cash_bank` | `require.permission:finance.cash_bank` | Fully Aligned |
| `/finance/external-reconciliation` | GET | `ExternalReconciliationWebController@index` | `require.permission:finance.cash_bank` | `require.permission:finance.cash_bank` | Fully Aligned |
| `/finance/external-reconciliation` | POST | `ExternalReconciliationWebController@store` | `require.permission:finance.cash_bank` | `require.permission:finance.cash_bank` | Fully Aligned |
| `/finance/liquidity-dashboard` | GET | `ExternalReconciliationWebController@dashboard` | `require.permission:finance.cash_bank` | `require.permission:finance.cash_bank` | Fully Aligned |
| `/finance/discrepancies` | GET | `ExternalReconciliationWebController@discrepancies` | `require.permission:finance.cash_bank` | `require.permission:finance.cash_bank` | Fully Aligned |
| `/finance/receivables` | GET | `CashLedgerWebController@receivables` | `require.permission:finance.receivables` | `require.permission:finance.receivables` | Fully Aligned |
| `/finance/receivables/export` | GET | `CashLedgerWebController@exportReceivables` | `require.permission:finance.receivables` | `require.permission:finance.receivables` | Fully Aligned |
| `/finance/payables` | GET | `CashLedgerWebController@payables` | `require.permission:finance.payables` | `require.permission:finance.payables` | Fully Aligned |
| `/finance/payables/export` | GET | `CashLedgerWebController@exportPayables` | `require.permission:finance.payables` | `require.permission:finance.payables` | Fully Aligned |
| `/finance/chart-of-accounts` | GET | `AccountingWebController@coaIndex` | `require.permission:accounting.view` | `require.permission:accounting.view` | Fully Aligned |
| `/finance/chart-of-accounts` | POST | `AccountingWebController@coaStore` | `require.permission:accounting.manage` | `require.permission:accounting.manage` | Fully Aligned |
| `/finance/chart-of-accounts/{account}` | PUT | `AccountingWebController@coaUpdate` | `require.permission:accounting.manage` | `require.permission:accounting.manage` | Fully Aligned |
| `/finance/chart-of-accounts/{account}` | DELETE | `AccountingWebController@coaDestroy` | `require.permission:accounting.manage` | `require.permission:accounting.manage` | Fully Aligned |
| `/finance/balance-sheet` | GET | `AccountingWebController@balanceSheet` | `require.permission:accounting.view` | `require.permission:accounting.view` | Fully Aligned |
| `/finance/balance-sheet/export-excel` | GET | `AccountingWebController@balanceSheetExportExcel` | `require.permission:accounting.view`, `entitlement:export` | `require.permission:accounting.view`, `entitlement:export` | Fully Aligned |
| `/finance/trial-balance` | GET | `AccountingWebController@trialBalance` | `require.permission:accounting.view` | `require.permission:accounting.view` | Fully Aligned |
| `/finance/trial-balance/export-excel` | GET | `AccountingWebController@trialBalanceExportExcel` | `require.permission:accounting.view`, `entitlement:export` | `require.permission:accounting.view`, `entitlement:export` | Fully Aligned |
| `/finance/general-ledger` | GET | `AccountingWebController@generalLedger` | `require.permission:accounting.view` | `require.permission:accounting.view` | Fully Aligned |
| `/finance/general-ledger/export-excel` | GET | `AccountingWebController@generalLedgerExportExcel` | `require.permission:accounting.view`, `entitlement:export` | `require.permission:accounting.view`, `entitlement:export` | Fully Aligned |
| `/finance/reconciliations` | GET | `AccountingWebController@reconciliationIndex` | `require.permission:accounting.view` | `require.permission:accounting.view` | Fully Aligned |
| `/finance/reconciliations/upload` | POST | `AccountingWebController@reconciliationUpload` | `require.permission:accounting.manage` | `require.permission:accounting.manage` | Fully Aligned |
| `/finance/reconciliations/lines/{line}/match` | POST | `AccountingWebController@reconciliationMatch` | `require.permission:accounting.manage` | `require.permission:accounting.manage` | Fully Aligned |
| `/finance/reconciliations/lines/{line}/unmatch` | POST | `AccountingWebController@reconciliationUnmatch` | `require.permission:accounting.manage` | `require.permission:accounting.manage` | Fully Aligned |

---

## 5. Seeders & Role Mapping Matrix

### 5.1 Existing vs Missing Permissions

| Permission Slug | Module Category | Display Label | Status in `RbacSeeder.php` | Proposed Action |
|---|---|---|---|---|
| `expenses.view` | `expenses` | Lihat Beban Operasional | **Present** (line 98) | Retain |
| `expenses.manage` | `expenses` | Catat Beban / Biaya | **Present** (line 99) | Retain |
| `accounting.view` | `accounting` | Lihat Buku Besar & Jurnal | **Present** (line 102) | Retain |
| `accounting.manage` | `accounting` | Kelola Bagan Akun & Rekonsiliasi | **Present** (line 103) | Retain |
| `finance.cash_bank` | `accounting` | Kas & Rekening Bank | **Present** (line 104) | Retain |
| `finance.receivables` | `accounting` | Piutang Usaha (AR Aging) | **Present** (line 105) | Retain |
| `finance.payables` | `accounting` | Hutang Usaha (AP Aging) | **Present** (line 106) | Retain |
| `feedback.view` | `support` | Lihat Umpan Balik & Masukan | **MISSING** | **Register in `RbacSeeder`** |
| `feedback.create` | `support` | Kirim Laporan Bug & Fitur | **MISSING** | **Register in `RbacSeeder`** |

### 5.2 System Role Assignment Matrix

| System Role | `expenses.*` | `accounting.*` | `finance.*` | `feedback.view` (Proposed) | `feedback.create` (Proposed) |
|---|:---:|:---:|:---:|:---:|:---:|
| **Owner** | Yes (all) | Yes (all) | Yes (all) | Yes | Yes |
| **Manager** | Yes (all) | Yes (all) | Yes (all) | Yes | Yes |
| **Admin** | Yes (all) | Yes (all) | Yes (all) | Yes | Yes |
| **Finance** | Yes (all) | Yes (all) | Yes (all) | Yes | Yes |
| **Cashier** | No | No | No | No | Optional (Yes) |
| **Warehouse** | No | No | No | No | Optional (Yes) |
| **Staff** | No | No | No | No | Optional (Yes) |
| **Viewer** | No | `accounting.view` only | `finance.cash_bank` only | Yes | No |

---

## 6. Actionable Implementation Plan (For Implementation Phase)

### Step 1: Update `database/seeders/RbacSeeder.php`
1. In `permissionMatrix()`:
   ```php
   // ─── Feedback & Dukungan Platform ─────────────────────────
   'feedback.view'   => ['Lihat Umpan Balik & Masukan', 'support', 'Melihat daftar laporan bug dan usulan fitur ke tim COOCA.'],
   'feedback.create' => ['Kirim Laporan Bug & Fitur',   'support', 'Mengirimkan laporan kendala sistem, bug, dan usulan fitur baru.'],
   ```
2. In `roleDefinitions()`:
   - Add `'feedback.view'`, `'feedback.create'` to `$managerSlugs`.
   - Add `'feedback.view'`, `'feedback.create'` to `'finance'['permissions']`.
   - Add `'feedback.create'` to `'cashier'['permissions']`, `'warehouse'['permissions']`, and `'staff'['permissions']`.
   - Add `'feedback.view'` to `'viewer'['permissions']`.

### Step 2: Refactor `FeedbackWebController.php`
Replace `require.role:owner` with granular permissions:
```php
public static function middleware(): array
{
    return [
        new Middleware('require.permission:feedback.view', only: [
            'bugs',
            'showBug',
            'streamBugAttachment',
            'features',
            'showFeature',
        ]),
        new Middleware('require.permission:feedback.create', only: [
            'createBug',
            'storeBug',
            'createFeature',
            'storeFeature',
        ]),
    ];
}
```

### Step 3: Update `routes/owner.php`
Remove the restrictive `Route::middleware('require.role:owner')->group(...)` wrapping feedback routes, or change it to align with controller encapsulation:
```php
// Owner & Authorized Staff Platform Feedback
Route::get('/feedback/bugs', [FeedbackWebController::class, 'bugs'])->name('feedback.bugs.index');
Route::get('/feedback/bugs/create', [FeedbackWebController::class, 'createBug'])->name('feedback.bugs.create');
Route::post('/feedback/bugs', [FeedbackWebController::class, 'storeBug'])->name('feedback.bugs.store');
Route::get('/feedback/bugs/{bugReport}/attachment', [FeedbackWebController::class, 'streamBugAttachment'])->name('feedback.bugs.attachment');
Route::get('/feedback/bugs/{bugReport}', [FeedbackWebController::class, 'showBug'])->name('feedback.bugs.show');
Route::get('/feedback/features', [FeedbackWebController::class, 'features'])->name('feedback.features.index');
Route::get('/feedback/features/create', [FeedbackWebController::class, 'createFeature'])->name('feedback.features.create');
Route::post('/feedback/features', [FeedbackWebController::class, 'storeFeature'])->name('feedback.features.store');
Route::get('/feedback/features/{featureRequest}', [FeedbackWebController::class, 'showFeature'])->name('feedback.features.show');
```

### Step 4: Synchronize Sidebar & Topbar Blade
In `resources/views/layouts/partials/sidebar.blade.php` and `topbar.blade.php`:
```blade
{{-- Replace --}}
@if (\App\Support\Context::isOwner())

{{-- With --}}
@if (\App\Support\Context::isOwner() || \App\Support\Context::hasPermission('feedback.view'))
```

### Step 5: Secure `SettlementProofController.php`
Add permission check in `app/Http/Controllers/Common/SettlementProofController.php`:
```php
$isMerchantAuthorized = $isMerchant && (\App\Support\Context::isAdminOrOwner() || \App\Support\Context::hasPermission('finance.cash_bank'));
abort_unless($isAdmin || $isMerchantAuthorized, 403, 'Akses bukti transfer ditolak. Dokumen rahasia keuangan hanya dapat diakses oleh Admin atau Merchant berwenang.');
```

---

## 7. Verification Method

To verify these findings and future fixes independently:
1. **PHP Syntax Check**:
   ```powershell
   php -l app/Http/Controllers/Web/FeedbackWebController.php
   php -l app/Http/Controllers/Web/Finance/*.php
   php -l database/seeders/RbacSeeder.php
   ```
2. **Route List Verification**:
   ```powershell
   php artisan route:list --name=feedback
   php artisan route:list --name=finance
   ```
3. **Automated Feature Test**:
   ```powershell
   php artisan test --filter=FeedbackModuleAuditTest
   php artisan test --filter=FinanceModuleAuditTest
   ```
4. **Idempotent RBAC Seeding Test**:
   ```powershell
   php artisan db:seed --class=RbacSeeder --force
   ```
