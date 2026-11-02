# Technical Survey Report: Phase 1 Backend Architecture (Petty Cash & Owner Approval Center)

**Survey Target:** `c:\laragon\www\cooca_core`  
**Date:** 2026-11-02  
**Author:** explorer_survey_1 (teamwork_explorer subagent)  
**Task Objective:** Comprehensive backend codebase investigation for implementing Phase 1 (Petty Cash & Owner Approval Center) for integrated mobile & backend production use.

---

## 1. Executive Summary

This investigation analyzed the backend architecture of `cooca_core` to prepare for implementing Phase 1 of the integration roadmap (`plan_core_to_mobile_features.md`). The survey specifically examined:
1. **Cash Ledger & Petty Cash:** Identified models (`CashAccount`, `CashTransaction`, `PosShift`, `PosCashMovement`, `Expense`), services (`CashLedgerService`, `PosShiftService`), storage utilities (`TenantStorage`), API routes, and tenant scoping mechanisms.
2. **Owner Approval Center:** Identified existing approval models (`ApprovalRequest`, `ApprovalRule`, `PosApprovalRequest`, `AttendanceCorrection`), workflow service (`ApprovalWorkflowService`), role/permission guards (`require.role:owner,admin`, `require.permission`), and how void/discount/attendance requests are tracked.
3. **Test Suite Architecture:** Confirmed the test framework is **PHPUnit 12** (Pest is not used in this repository), utilizing in-memory SQLite (`:memory:`), `RefreshDatabase`, `Context::flush()`, and Sanctum authenticated requests (`$this->actingAs($user, 'sanctum')` / `withHeader('Authorization', 'Bearer '.$token)`). Test execution was verified running both unit and feature suites cleanly.

---

## 2. Cash Ledger & Petty Cash Architecture

### 2.1 Ledger Models & Migrations

In `cooca_core`, cash ledger transactions are modeled through `CashAccount` and `CashTransaction` (there is no separate model named `CashLedgerEntry`; `CashTransaction` is the SSOT across finance and accounting):

| Concept | File Location | Database Table |
|---|---|---|
| **Cash Account** | `app/Models/CashAccount.php` | `cash_accounts` |
| **Cash Transaction** | `app/Models/CashTransaction.php` | `cash_transactions` |
| **Migration** | `database/migrations/2026_09_04_000080_create_cash_accounts_and_transactions_tables.php` | `cash_accounts`, `cash_transactions` |
| **Domain Service** | `app/Domain/Finance/CashLedgerService.php` | N/A |
| **Web Controller** | `app/Http/Controllers/Web/Finance/CashLedgerWebController.php` | N/A |
| **API Controller** | `app/Http/Controllers/Api/V1/Finance/CashLedgerController.php` | N/A |

#### Table Schema: `cash_accounts`
- `id` (UUID, primary key)
- `business_id` (foreignUuid -> `businesses.id`, cascadeOnDelete)
- `chart_of_account_id` (foreignUuid -> `chart_of_accounts.id`, nullable, nullOnDelete)
- `name` (string 100) — e.g. `'Kas Utama'`, `'Bank BCA'`, `'QRIS / E-Wallet'`
- `type` (string 20, default `'cash'`) — constants: `TYPE_CASH = 'cash'`, `TYPE_BANK = 'bank'`, `TYPE_EWALLET = 'ewallet'`
- `current_balance` (decimal 15, 2, default `0.00`)
- `is_active` (boolean, default `true`)
- Timestamps (`created_at`, `updated_at`)
- Unique constraint: `['business_id', 'name']`

#### Table Schema: `cash_transactions`
- `id` (UUID, primary key)
- `business_id` (foreignUuid -> `businesses.id`, cascadeOnDelete)
- `cash_account_id` (foreignUuid -> `cash_accounts.id`, restrictOnDelete)
- `type` (string 10) — constants: `TYPE_IN = 'in'`, `TYPE_OUT = 'out'`, `TYPE_TRANSFER = 'transfer'`
- `amount` (decimal 15, 2)
- `balance_after` (decimal 15, 2)
- `reference_type` (string 50, nullable) — e.g. `'expense'`, `'pos_order'`, `'manual_cash'`, `'cash_transfer_in'`, `'cash_transfer_out'`
- `reference_id` (string 64, nullable) — UUID of the related document or external reference
- `description` (string 255)
- `transaction_date` (date)
- `created_by` (foreignUuid -> `users.id`, nullable, nullOnDelete)
- Timestamps (`created_at`, `updated_at`)
- Indexes: `['business_id', 'cash_account_id', 'transaction_date']`, `['business_id', 'reference_type', 'reference_id']`

### 2.2 Cash Flow Recording Mechanism

`CashLedgerService` (`app/Domain/Finance/CashLedgerService.php`) provides standard methods:
- `recordInflow(Business $business, float $amount, string $referenceType, string $referenceId, string $description, string $method = 'cash', ?string $userId = null, ?CashAccount $account = null): CashTransaction`
- `recordOutflow(Business $business, float $amount, string $referenceType, string $referenceId, string $description, string $method = 'cash', ?string $userId = null, ?CashAccount $account = null): CashTransaction`
- `transfer(CashAccount $from, CashAccount $to, float $amount, string $description, ?string $userId = null): array`
- `accountFor(Business $business, string $method = 'cash'): CashAccount`: Resolves or auto-creates default `CashAccount` (`'Kas Utama'` for cash/petty cash, `'QRIS / E-Wallet'` for e-wallets, or `'Bank Utama'` for bank transfers) linked with standard COA accounts (`'1-1001'` or `'1-1002'`).

**Transaction atomicity & overdraft handling:**
- All movements run in `DB::transaction()` with `lockForUpdate()` on the target `CashAccount`.
- Idempotency guard: checks if a transaction with the same `(business_id, type, reference_type, reference_id)` already exists. If found, returns the existing record without double-decrementing.
- Balances are flexible and permit negative values (overdraft / cash float funding / talangan kas).

### 2.3 POS Shifts & Shift Cash Movements

POS shifts and cashier cash drawer movements are modeled in:
- Model: `app/Models/PosShift.php` (table `pos_shifts`)
- Cash movement model: `app/Models/PosCashMovement.php` (table `pos_cash_movements`)
- Migrations: `2026_08_29_000040_create_pos_registers_and_shifts_table.php`, extended in `2026_09_25_110000_extend_pos_registers_shifts_and_orders_table.php`.
- Domain service: `app/Domain/Pos/PosShiftService.php`
- Controller: `app/Http/Controllers/Api/V1/Pos/PosShiftController.php`

#### Shift Status & Lifecycle
- `status`: `'open'` (`PosShift::STATUS_OPEN`), `'closed'` (`PosShift::STATUS_CLOSED`).
- Helper: `$posShift->isOpen(): bool { return $this->status === self::STATUS_OPEN; }`.
- Active shift resolution: `PosShiftService::getActiveShift(Business $business, User $user, ?string $locationId = null): ?PosShift` searches for active shifts for the user or business.
- Shift cash fields:
  - `opening_cash` (decimal 15,2)
  - `total_cash_sales` (decimal 15,2)
  - `total_non_cash_sales` (decimal 15,2)
  - `total_cash_in` (decimal 15,2)
  - `total_cash_out` (decimal 15,2)
  - `closing_cash_expected` (calculated: `opening_cash + cash_sales + cash_in - cash_out`)
  - `closing_cash_actual` (input count by cashier)
  - `cash_difference` (variance: `actual - expected`)

#### Shift Cash Movement (`pos_cash_movements`):
- `pos_shift_id`, `user_id`, `business_id`
- `type`: `PosCashMovement::TYPE_CASH_IN = 'cash_in'`, `PosCashMovement::TYPE_CASH_OUT = 'cash_out'`
- `amount` (float)
- `reason` (string 255)
- `notes` (text, nullable)
- Calling `PosShiftService::recordCashMovement(PosShift $shift, User $user, string $type, float $amount, string $reason, ?string $notes = null)` increments `$shift->total_cash_in` or `$shift->total_cash_out` atomically.

### 2.4 Existing API Routes for POS

In `routes/api.php` (lines 1179–1257), all POS routes are grouped under:
```php
Route::prefix('v1')->middleware(['auth:sanctum', 'throttle:300,1'])->group(function (): void {
    Route::middleware('business.active')->group(function (): void {
        Route::prefix('pos')->group(function (): void {
            // Existing shift routes:
            Route::get('/shifts', [PosShiftController::class, 'index'])->middleware('require.permission:pos.terminal,pos.orders');
            Route::post('/shifts/open', [PosShiftController::class, 'open'])->middleware('require.permission:pos.terminal,pos.orders');
            Route::post('/shifts/{posShift}/close', [PosShiftController::class, 'close'])->middleware('require.permission:pos.terminal,pos.orders');
            Route::get('/shifts/{posShift}/summary', [PosShiftController::class, 'summary'])->middleware('require.permission:pos.terminal,pos.orders');
            Route::post('/shifts/{posShift}/cash-movement', [PosShiftController::class, 'recordCashMovement'])->middleware('require.permission:pos.terminal,pos.orders');
            Route::get('/shifts/active', [PosShiftController::class, 'current'])->middleware('require.permission:pos.terminal,pos.orders');
            Route::get('/shifts/current', [PosShiftController::class, 'current'])->middleware('require.permission:pos.terminal,pos.orders');
            Route::post('/shifts/{posShift}/print', [PosShiftController::class, 'printSummary'])->middleware('require.permission:pos.terminal,pos.orders');
            ...
        });
    });
});
```

### 2.5 Multi-Tenant Scoping on Sanctum API Requests

Tenant isolation is implemented through a layered architecture:
1. **Middleware `SetActiveBusinessContext` (`app/Http/Middleware/SetActiveBusinessContext.php`):**
   - Resolves active business ID with priority:
     1. Header: `X-Business-Id`
     2. Session: `active_business_id` (if session present)
     3. User attribute: `$user->active_business_id`
   - Validates user membership in the business: `BusinessMembership::where('business_id', $businessId)->where('user_id', $user->id)->first()`.
   - Binds to thread context: `Context::setBusiness($business, $membership)`.
   - Fallback: Binds first active `BusinessMembership` if context is still unbound.
2. **Middleware `EnsureActiveBusiness` (`app/Http/Middleware/EnsureActiveBusiness.php` alias `business.active`):**
   - Requires `Context::hasBusiness()`. Returns `409 Conflict` with `error_code: ACTIVE_BUSINESS_REQUIRED` if not set.
   - Enforces business suspension locks: Returns `403 Forbidden` (`BUSINESS_SUSPENDED`) on mutating requests if `$business->is_active === false`.
3. **Eloquent Global Scope `BelongsToBusiness` (`app/Models/Traits/BelongsToBusiness.php`):**
   - Automatically registers `BusinessScope` on models (`CashTransaction`, `PosShift`, `PosCashMovement`, `Expense`, etc.).
   - Automatically assigns `business_id = Context::business()->id` on model creation if not specified.

### 2.6 File and Image Upload Storage Conventions

- **Storage Disks:**
  - `TenantStorage::DISK_PUBLIC = 'public'` (`storage/app/public/`) -> Public assets (product images, company logos, community posts).
  - `TenantStorage::DISK_PRIVATE = 'local'` (`storage/app/private/` or `storage/app/`) -> Confidential financial proofs, expense receipts, supplier invoices.
- **Directory Structure:**
  - Managed by `App\Domain\Storage\TenantStorage`:
    - `TenantStorage::privateDir($business, TenantStorage::FOLDER_EXPENSES)` => `"bisnis/{slug}/expenses"`
    - `TenantStorage::publicDir($business, TenantStorage::FOLDER_RECEIPTS)` => `"bisnis/{slug}/receipts"`
- **Upload Conventions:**
  - Multipart file upload: `$file->store($dir, 'local')`.
  - Base64 upload (mobile camera compatibility):
    ```php
    $binary = base64_decode($base64Clean, true);
    $fileName = Str::uuid()->toString() . '.' . $ext;
    $filePath = "{$dir}/{$fileName}";
    Storage::disk('local')->put($filePath, $binary);
    ```
  - Quota tracking: `StorageTrackingService::recordUpload(...)` logs storage usage against the business owner's quota limit.

---

## 3. Owner Approval Center Architecture

### 3.1 Existing Approval Models and Tables

The codebase has three primary models for handling approvals:

1. **`App\Models\ApprovalRequest` (`approval_requests` table):**
   - Migration: `2026_09_20_000005_create_document_approval_tables.php`
   - Fields: `id`, `business_id`, `document_type`, `document_id`, `requester_id`, `rule_id`, `amount`, `current_level`, `total_levels`, `status` (`pending`, `approved`, `rejected`), `rejection_reason`, `approved_at`, `rejected_at`.
   - Used for document approvals:
     - `ApprovalRule::DOC_PURCHASE_ORDER` (`'purchase_order'`)
     - `ApprovalRule::DOC_EXPENSE` (`'expense'`)
     - `ApprovalRule::DOC_SUPPLIER_INVOICE` (`'supplier_invoice'`)
     - `ApprovalRule::DOC_STOCK_ADJUSTMENT` (`'stock_adjustment'`)
   - Supported by `ApprovalLog` (`approval_logs` table) for multi-level approval history and audit trail.

2. **`App\Models\PosApprovalRequest` (`pos_approval_requests` table):**
   - Migration: `2026_08_29_000045_create_pos_security_and_approval_tables.php`
   - Fields:
     - `id` (UUID)
     - `business_id` (foreignUuid)
     - `requested_by` (foreignUuid -> `users.id`)
     - `action_type` (string 40) — constants:
       - `ACTION_VOID = 'void'`
       - `ACTION_REFUND = 'refund'`
       - `ACTION_HIGH_DISCOUNT = 'high_discount'`
       - `ACTION_OPEN_DRAWER = 'open_drawer'`
     - `reference_id` (string 64, e.g. order_id)
     - `payload` (JSON) — stores details such as items, void reason, requested discount percentage, etc.
     - `status` (`pending`, `approved`, `rejected`)
     - `approved_by` (foreignUuid -> `users.id`, nullable)
     - `rejection_reason` (string 255, nullable)
     - `approved_at` (timestamp, nullable)
     - Timestamps (`created_at`, `updated_at`)

3. **`App\Models\AttendanceCorrection` (`attendance_corrections` table):**
   - Migration: `2026_09_21_000002_create_hrm_attendances_and_corrections_tables.php`
   - Fields:
     - `id` (UUID)
     - `business_id` (foreignUuid)
     - `user_id` (foreignUuid -> employee)
     - `attendance_id` (foreignUuid -> `attendances.id`, nullable)
     - `correction_number` (string 64)
     - `target_date` (date)
     - `correction_type` (`clock_in_only`, `clock_out_only`, `full_day`, `status_only`)
     - `proposed_clock_in`, `proposed_clock_out`, `proposed_status`
     - `reason` (text)
     - `attachment_path` (string 255, nullable)
     - `status` (`pending`, `approved`, `rejected`, `revision_requested`)
     - `reviewed_by`, `reviewed_at`, `review_notes`

### 3.2 Representation of Specific Approval Items

- **Void Transactions:**
  - Currently synchronous in POS: Cashier provides supervisor PIN to call `POST /api/v1/pos/orders/{posOrder}/void`.
  - For asynchronous Owner Approval: A `PosApprovalRequest` with `action_type = 'void'`, `reference_id = $order->id`, `payload = ['reason' => $reason, 'order_number' => $order->order_number, 'total_amount' => $order->total_amount]` can be created in `pending` status. Upon approval, `$orderService->voidOrder($order, $approver, $reason)` is executed.
- **Above-Limit Discounts:**
  - `businesses.pos_max_cashier_discount_percent` defines the cashier's discount limit (default: 10%).
  - If a cashier applies a discount exceeding this threshold, a `PosApprovalRequest` with `action_type = 'high_discount'`, `reference_id = $order->id`, `payload = ['discount_percent' => $pct, 'discount_amount' => $amt]` is submitted.
- **Attendance Corrections:**
  - Employee requests clock-in/out fixes via HRM; saved in `attendance_corrections` with `status = 'pending'`.
  - Handled by `AttendanceService::reviewCorrection(AttendanceCorrection $ticket, User $reviewer, string $action, ?string $notes)`.

### 3.3 Owner/Admin Authorization on API Endpoints

1. **Role Guard Middleware (`App\Http\Middleware\RequireRole`, alias `'require.role'`):**
   - Usage: `->middleware('require.role:owner,admin')`.
   - Reads `$currentRole = Context::role()`. If not in the whitelist, immediately returns HTTP 403:
     ```json
     {
         "message": "You do not have the required role to perform this action.",
         "error_code": "FORBIDDEN_ROLE",
         "required_roles": ["owner", "admin"],
         "current_role": "cashier"
     }
     ```
2. **Permission Guard Middleware (`App\Http\Middleware\RequirePermission`, alias `'require.permission'`):**
   - `Context::isAdminOrOwner()` automatically grants bypass for all permission checks on enabled business modules.
3. **Domain Authority Check (`ApprovalWorkflowService::canUserApprove`):**
   - Owners (`$membership->role === 'owner'`) have omnipotent approval authority across all levels.
   - Separation of concerns: Maker cannot approve their own document unless they are the business owner.

---

## 4. Test Suite Architecture

### 4.1 Framework and Test Conventions

- **Test Runner:** **PHPUnit 12** (`vendor/bin/phpunit`, package: `"phpunit/phpunit": "^12.5.12"`). Pest is NOT used.
- **Base Class:** All tests extend `Tests\TestCase` (`tests/TestCase.php`), which extends `Illuminate\Foundation\Testing\TestCase` and includes `Context::flush()` inside `tearDown()`.
- **Database Configuration:** In-memory SQLite (`:memory:`) configured via `phpunit.xml` (`<env name="DB_CONNECTION" value="sqlite"/>`, `<env name="DB_DATABASE" value=":memory:"/>`).
- **Database Refresh:** Feature tests use `Illuminate\Foundation\Testing\RefreshDatabase`.

### 4.2 API Testing Setup & Helper Patterns

Existing feature tests (such as `tests/Feature/ActiveBusinessContextTest.php` and `tests/Feature/CashLedgerIntegrationTest.php`) establish testing environments using the following standard pattern:

```php
protected function setUp(): void
{
    parent::setUp();
    Context::flush();

    $this->owner = User::create([
        'name' => 'Owner Test',
        'email' => 'owner@example.com',
        'password' => bcrypt('password123'),
    ]);

    $this->business = Business::create(['name' => 'Bisnis Test']);
    $this->business->users()->attach($this->owner->id, [
        'id' => (string) Str::uuid(),
        'role' => 'owner',
        'is_active' => true,
    ]);
    $this->owner->update(['active_business_id' => $this->business->id]);
    Context::setBusiness($this->business);
}
```

For authenticated API calls:
- Using Sanctum guard: `$this->actingAs($this->owner, 'sanctum')->postJson(...)`
- Or using Sanctum personal access tokens:
  ```php
  $token = $this->owner->createToken('test-token')->plainTextToken;
  $this->withHeaders([
      'Authorization' => 'Bearer ' . $token,
      'X-Business-Id' => $this->business->id,
  ])->postJson('/api/v1/...', $payload);
  ```

### 4.3 Test Execution Verification

Execution was verified by running PHPUnit directly:
- `php vendor/phpunit/phpunit/phpunit tests/Feature/ActiveBusinessContextTest.php`:
  **Result:** 4 tests passed, 13 assertions (0 failures).
- `php vendor/phpunit/phpunit/phpunit tests/Feature/CashLedgerIntegrationTest.php`:
  **Result:** 9 tests passed, 50 assertions (0 failures).

---

## 5. Implementation Recommendations for Phase 1

### 5.1 Recommended Architecture for R1 (Petty Cash API `POST /api/v1/pos/expenses`)

1. **Route Definition:**
   In `routes/api.php` under `Route::prefix('pos')->group(...)`:
   ```php
   Route::post('/expenses', [\App\Http\Controllers\Api\V1\Pos\PosExpenseApiController::class, 'store'])
       ->middleware('require.permission:pos.terminal,pos.orders');
   ```
2. **Payload Structure:**
   - `pos_shift_id` (required, UUID, must exist in `pos_shifts` and belong to active business and have `status = 'open'`)
   - `amount` (required, numeric, min: 1)
   - `category` (required, string, e.g. `'operational'`, `'supplies'`, `'gas_lpg'`, `'water'`, `'parking'`, `'other'`)
   - `description` or `notes` (required, string, max: 255)
   - `receipt_image` (nullable, file, image, max: 5120) OR `receipt_image_base64` (nullable, string)
3. **Execution Logic:**
   - Validate shift is active (`$shift->isOpen()`).
   - Store receipt proof in private storage using `TenantStorage::privateDir($business, TenantStorage::FOLDER_EXPENSES)` on `'local'` disk.
   - Create `Expense` record (`receipt_image_path`, `payment_method = 'cash'`, etc.).
   - Call `PosShiftService::recordCashMovement($shift, $user, PosCashMovement::TYPE_CASH_OUT, $amount, $category, $description)`.
   - Call `CashLedgerService::recordOutflow($business, $amount, 'pos_expense', $expense->id, "[Petty Cash - {$category}] {$description}", 'cash', $user->id)`.
   - Return HTTP 201 Created with JSON response containing expense details, shift balance summary, and calculated `expected_cash`.

### 5.2 Recommended Architecture for R2 (Owner Approval Center API)

1. **Route Definitions:**
   In `routes/api.php` under `Route::middleware('business.active')->group(...)`:
   ```php
   Route::prefix('owner/approvals')->middleware('require.role:owner,admin')->group(function (): void {
       Route::get('/pending', [\App\Http\Controllers\Api\V1\Owner\OwnerApprovalApiController::class, 'pending']);
       Route::post('/{id}/action', [\App\Http\Controllers\Api\V1\Owner\OwnerApprovalApiController::class, 'action']);
   });
   ```
2. **Unified Pending Aggregator (`GET /api/v1/owner/approvals/pending`):**
   Aggregates pending approval requests across domains for the current business:
   - **POS Approvals:** `PosApprovalRequest::where('business_id', $business->id)->where('status', 'pending')->with('requester:id,name')->get()`
   - **Attendance Corrections:** `AttendanceCorrection::where('business_id', $business->id)->where('status', 'pending')->with('user:id,name')->get()`
   - **Document Approvals:** `ApprovalRequest::where('business_id', $business->id)->where('status', 'pending')->with('requester:id,name')->get()`
   Returns a standardized unified array with uniform attributes: `{ id, type, title, subtitle, requester_name, amount, created_at, metadata }` and total pending count badge.
3. **Action Handler (`POST /api/v1/owner/approvals/{id}/action`):**
   - Payload: `{ action: 'approve' | 'reject', reason?: string }`
   - Validates existence in `PosApprovalRequest`, `AttendanceCorrection`, or `ApprovalRequest`.
   - Executes domain-specific resolution:
     - If `PosApprovalRequest` (e.g. void order): invokes `PosOrderService::voidOrder(...)` or updates status to approved/rejected.
     - If `AttendanceCorrection`: invokes `AttendanceService::reviewCorrection(...)`.
     - If `ApprovalRequest`: invokes `ApprovalWorkflowService::approve(...)` or `reject(...)`.
   - Records `AuditLog` entry.

### 5.3 Test Suite Strategy for Phase 1
- Create `tests/Feature/Api/PosExpenseApiTest.php` to verify shift validation, cash ledger outflow, storage receipt saving, and shift cash calculation.
- Create `tests/Feature/Api/OwnerApprovalApiTest.php` to verify role gating (`require.role:owner,admin`), pending list fetching, and approve/reject actions across POS voids, high discounts, and attendance corrections.
- Run tests via `php vendor/phpunit/phpunit/phpunit tests/Feature/Api/PosExpenseApiTest.php`.
