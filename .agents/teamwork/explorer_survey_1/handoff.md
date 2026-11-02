# Handoff Report — explorer_survey_1 (Phase 1 Backend Codebase Architecture Survey)

**Agent ID:** explorer_survey_1  
**Timestamp:** 2026-11-02T05:28:30Z  
**Type:** Hard (Task complete)  
**Deliverable File:** `c:\laragon\www\cooca_core\.agents\teamwork\explorer_survey_1\report.md`  

---

## 1. Observation

Direct observations from the `c:\laragon\www\cooca_core` codebase:

1. **Cash Ledger Models & Services:**
   - There is no model named `CashLedgerEntry`. The single source of truth for financial ledger movements is `App\Models\CashTransaction` (`app/Models/CashTransaction.php:13`) and `App\Models\CashAccount` (`app/Models/CashAccount.php`), migrated in `database/migrations/2026_09_04_000080_create_cash_accounts_and_transactions_tables.php`.
   - Cash outflow is executed via `App\Domain\Finance\CashLedgerService::recordOutflow(...)` (`app/Domain/Finance/CashLedgerService.php:24-27`), which atomically acquires `lockForUpdate()` on the target `CashAccount` (`app/Domain/Finance/CashLedgerService.php:97`), decrements the balance, and records a `CashTransaction` with `type = 'out'`.
   - `CashLedgerWebController` is located at `app/Http/Controllers/Web/Finance/CashLedgerWebController.php:28` and `CashLedgerController` (API) is at `app/Http/Controllers/Api/V1/Finance/CashLedgerController.php:18`.

2. **POS Shifts and Cash Movements:**
   - Shifts are modeled by `App\Models\PosShift` (`app/Models/PosShift.php:14`) with status constants `PosShift::STATUS_OPEN = 'open'` and `PosShift::STATUS_CLOSED = 'closed'`.
   - Cash movements during shifts are tracked via `App\Models\PosCashMovement` (`app/Models/PosCashMovement.php:13`) with `type` `'cash_in'` or `'cash_out'`.
   - `PosShiftService::recordCashMovement(...)` (`app/Domain/Pos/PosShiftService.php:95-126`) creates a `PosCashMovement` record and updates `$shift->total_cash_in` or `$shift->total_cash_out`.
   - `PosShiftService::getShiftSummary(...)` (`app/Domain/Pos/PosShiftService.php:142-200`) calculates `expected_cash` via:
     `$expectedCash = $opening + $cashSales + $cashIn - $cashOut;` (line 188).
   - Existing POS shift routes exist in `routes/api.php:1227-1234` under `Route::prefix('pos')`, guarded by `require.permission:pos.terminal,pos.orders`.

3. **Tenant & API Request Isolation:**
   - In `routes/api.php:894`, API routes are wrapped in `Route::middleware(['auth:sanctum', 'throttle:300,1'])`.
   - In `routes/api.php:924`, tenant-aware routes are wrapped in `Route::middleware('business.active')`.
   - `App\Http\Middleware\SetActiveBusinessContext` (`app/Http/Middleware/SetActiveBusinessContext.php:26-42`) inspects `X-Business-Id` header, verifies membership via `BusinessMembership::where('business_id', $businessId)->where('user_id', $user->id)->first()`, and invokes `Context::setBusiness($business, $membership)`.
   - `App\Models\Traits\BelongsToBusiness` (`app/Models/Traits/BelongsToBusiness.php:13-38`) binds `BusinessScope` to auto-filter queries and auto-populates `business_id` on model creation.

4. **Storage & Receipt Handling:**
   - `App\Domain\Storage\TenantStorage` (`app/Domain/Storage/TenantStorage.php:14-15`) defines `DISK_PUBLIC = 'public'` and `DISK_PRIVATE = 'local'`.
   - Sensitive financial expense proofs are stored in private storage: `TenantStorage::privateDir($business, TenantStorage::FOLDER_EXPENSES)` (`app/Domain/Storage/TenantStorage.php:56-62`) returning `"bisnis/{slug}/expenses"`.
   - Base64 images are supported by decoding `base64_decode(...)` and saving to `Storage::disk('local')->put(...)` as demonstrated in `app/Domain/Mcp/Tools/FinanceRecordExpenseTool.php:129-146`.

5. **Approval Models:**
   - `App\Models\PosApprovalRequest` (`app/Models/PosApprovalRequest.php:13-63`) exists for POS actions (`ACTION_VOID = 'void'`, `ACTION_REFUND = 'refund'`, `ACTION_HIGH_DISCOUNT = 'high_discount'`, `ACTION_OPEN_DRAWER = 'open_drawer'`) with `status` (`pending`, `approved`, `rejected`).
   - `App\Models\ApprovalRequest` (`app/Models/ApprovalRequest.php:15`) and `ApprovalRule` exist for document approvals (PO, expenses, bills, stock adjustments) with `ApprovalWorkflowService` (`app/Domain/Approval/ApprovalWorkflowService.php:20`).
   - `App\Models\AttendanceCorrection` (`app/Models/AttendanceCorrection.php:13`) exists for employee attendance fixes with `status` (`pending`, `approved`, `rejected`).
   - Owner/admin authorization on API routes is enforced by `require.role:owner,admin` (`app/Http/Middleware/RequireRole.php:19-30`) and `Context::isAdminOrOwner()` (`app/Support/Context.php:148-150`).

6. **Test Suite Framework:**
   - `composer.json:28` lists `"phpunit/phpunit": "^12.5.12"`. Pest is NOT installed or used.
   - `phpunit.xml:28-29` defines `<env name="DB_CONNECTION" value="sqlite"/>` and `<env name="DB_DATABASE" value=":memory:"/>`.
   - Running `php vendor/phpunit/phpunit/phpunit tests/Feature/ActiveBusinessContextTest.php` executed with code 0 (`{"tool":"phpunit","result":"passed","tests":4,"passed":4,"assertions":13}`).
   - Running `php vendor/phpunit/phpunit/phpunit tests/Feature/CashLedgerIntegrationTest.php` executed with code 0 (`{"tool":"phpunit","result":"passed","tests":9,"passed":9,"assertions":50}`).

---

## 2. Logic Chain

1. **Premise:** The user requested implementation of Phase 1 (Petty Cash & Owner Approval Center) for `cooca_core` and `cooca_my_own`.
2. **Analysis of R1 (Petty Cash):**
   - Kasir petty cash recording requires an endpoint `POST /api/v1/pos/expenses`.
   - Observations show that `PosShift` tracks shift expected cash through `pos_cash_movements` (`type = 'cash_out'`), while the general financial ledger tracks outflow through `cash_transactions` (`type = 'out'`).
   - Therefore, the implementation of `POST /api/v1/pos/expenses` must perform an atomic two-way synchronization:
     (a) Record `PosCashMovement` (`type = 'cash_out'`, reason/notes) linked to the active `PosShift` so that `PosShiftService::getShiftSummary()` automatically decrements `expected_cash`.
     (b) Create an `Expense` record and call `CashLedgerService::recordOutflow()` so that the business-wide cash ledger and financial accounts are updated.
     (c) Store the receipt proof on `'local'` private disk in `TenantStorage::privateDir($business, TenantStorage::FOLDER_EXPENSES)` supporting both multipart `file` and `receipt_image_base64`.
3. **Analysis of R2 (Owner Approval Center):**
   - The user requires `GET /api/v1/owner/approvals/pending` and `POST /api/v1/owner/approvals/{id}/action`.
   - Observations reveal three distinct existing models representing pending requests:
     - `PosApprovalRequest` for POS voids, refunds, and above-limit discounts.
     - `AttendanceCorrection` for HRM attendance correction requests.
     - `ApprovalRequest` for operational/purchase/expense approvals.
   - Therefore, `GET /api/v1/owner/approvals/pending` should aggregate pending items from these models into a unified response schema suitable for the mobile cards, and `POST /api/v1/owner/approvals/{id}/action` should delegate to the respective domain service (`PosOrderService::voidOrder`, `AttendanceService::reviewCorrection`, or `ApprovalWorkflowService::approve/reject`) while enforcing `require.role:owner,admin`.
4. **Analysis of R3 (Automated Testing):**
   - Tests in `cooca_core` must use PHPUnit 12 classes extending `Tests\TestCase` with `RefreshDatabase`.
   - Sanctum authentication in tests should use `$this->actingAs($user, 'sanctum')` and pass `X-Business-Id` header.

---

## 3. Caveats

1. **Web vs API Approval Routes:** The existing `ApprovalWebController` is web-only (`routes/owner.php:271-287`). The `/api/v1/owner/approvals/*` routes are new endpoints to be created in `routes/api.php`.
2. **PosApprovalRequest Service:** Although the `PosApprovalRequest` model and table were migrated in August 2026, there was no dedicated API controller for it yet. The Phase 1 implementation can either route POS void requests directly through `PosApprovalRequest` or connect it to `PosOrderService`.
3. **Pest vs PHPUnit:** The prompt mentioned Pest or PHPUnit. As observed in `composer.json`, only PHPUnit 12 is installed; therefore, all new tests must be written as PHPUnit test classes (`tests/Feature/Api/...Test.php`).

---

## 4. Conclusion

The `cooca_core` backend architecture is mature, well-structured, and ready for implementing Phase 1:
- `CashLedgerService`, `PosShiftService`, `TenantStorage`, and `Context` provide complete domain primitives for the Petty Cash API.
- `ApprovalRequest`, `PosApprovalRequest`, `AttendanceCorrection`, and `require.role:owner,admin` provide clear domain patterns for the Owner Approval Center.
- PHPUnit 12 with SQLite `:memory:` provides fast, reliable test execution.
- All detailed findings and blueprint recommendations have been documented in `c:\laragon\www\cooca_core\.agents\teamwork\explorer_survey_1\report.md`.

---

## 5. Verification Method

To independently verify the observations and test environment:
1. Verify PHPUnit execution on the surveyed tests:
   ```powershell
   php vendor/phpunit/phpunit/phpunit tests/Feature/ActiveBusinessContextTest.php
   php vendor/phpunit/phpunit/phpunit tests/Feature/CashLedgerIntegrationTest.php
   ```
2. Verify models and schema files:
   - `c:\laragon\www\cooca_core\app\Models\CashTransaction.php`
   - `c:\laragon\www\cooca_core\app\Models\PosShift.php`
   - `c:\laragon\www\cooca_core\app\Models\PosCashMovement.php`
   - `c:\laragon\www\cooca_core\app\Models\PosApprovalRequest.php`
   - `c:\laragon\www\cooca_core\app\Models\AttendanceCorrection.php`
   - `c:\laragon\www\cooca_core\app\Domain\Storage\TenantStorage.php`
3. Verify report deliverable:
   - View `c:\laragon\www\cooca_core\.agents\teamwork\explorer_survey_1\report.md`.
