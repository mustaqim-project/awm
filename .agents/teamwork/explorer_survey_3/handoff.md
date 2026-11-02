# Handoff Report: Phase 1 Specification Mining & Contract Formalization

**Agent:** `explorer_survey_3`  
**Role:** Specification & Contract Miner  
**Date:** 2026-11-02  
**Working Directory:** `c:\laragon\www\cooca_core\.agents\teamwork\explorer_survey_3\`  
**Handoff Type:** Hard (Task Complete)  

---

## 1. Observation

1. **POS Shift Service & Cash Drawer Management:**
   - In `c:\laragon\www\cooca_core\app\Models\PosShift.php` (lines 35-62), `PosShift` possesses attributes `opening_cash`, `total_cash_sales`, `total_non_cash_sales`, `total_cash_in`, `total_cash_out`, `closing_cash_actual`, `closing_cash_expected`, and `cash_difference`.
   - In `c:\laragon\www\cooca_core\app\Domain\Pos\PosShiftService.php` (lines 95-126), `recordCashMovement()` records a `PosCashMovement` (`TYPE_CASH_IN` or `TYPE_CASH_OUT`) and updates `$shift->total_cash_out`.
   - In `c:\laragon\www\cooca_core\app\Domain\Pos\PosShiftService.php` (lines 184-189), the shift financial summary computes expected cash via:
     `$expectedCash = $opening + $cashSales + $cashIn - $cashOut;`
   - In `c:\laragon\www\cooca_core\routes\api.php` (lines 1226-1235), existing shift endpoints are:
     `GET /shifts/active`, `POST /shifts/open`, `POST /shifts/{posShift}/close`, `GET /shifts/{posShift}/summary`, `POST /shifts/{posShift}/cash-movement`.
     No dedicated endpoint currently exists for `POST /api/v1/pos/expenses`.

2. **Cash Ledger Service:**
   - In `c:\laragon\www\cooca_core\app\Domain\Finance\CashLedgerService.php` (lines 19-27), `recordOutflow(Business $business, float $amount, string $referenceType, string $referenceId, string $description, string $method = 'cash', ?string $userId = null, ?CashAccount $account = null): CashTransaction` records ledger movements and maintains active `CashAccount` balances.
   - In `c:\laragon\www\cooca_core\app\Domain\Storage\TenantStorage.php` (lines 23-50), tenant-safe subfolders are defined: `FOLDER_EXPENSES = 'expenses'`, with path resolution `TenantStorage::publicDir($business, TenantStorage::FOLDER_EXPENSES)`.

3. **Approval Infrastructure in Backend:**
   - In `c:\laragon\www\cooca_core\app\Models\PosApprovalRequest.php` (lines 17-41), the model supports `status` (`pending`, `approved`, `rejected`), `action_type` (`void`, `refund`, `high_discount`, `open_drawer`), `reference_id`, `payload`, `approved_by`, `rejection_reason`, and `approved_at`.
   - In `c:\laragon\www\cooca_core\app\Models\AttendanceCorrection.php` (lines 22-47), the model supports `status` (`pending`, `approved`, `rejected`), `correction_type`, `proposed_clock_in`, `proposed_clock_out`, `reason`, `reviewed_by`, and `review_notes`.
   - In `c:\laragon\www\cooca_core\routes\api.php`, no unified endpoint exists currently under `/api/v1/owner/approvals/pending` or `/api/v1/owner/approvals/{id}/action`.

4. **Mobile POS & Dashboard Codebase:**
   - In `c:\laragon\www\cooca_my_own\lib\screens\pos\pos_shift_modal.dart` (lines 109-114), expected cash is derived from `_liveShiftSummary['expected_cash']` or `widget.state.activeShift?.expectedCash`. Segment 1 provides basic cash movements but lacks structured category selection, photo receipt capture, and offline queueing.
   - In `c:\laragon\www\cooca_my_own\lib\screens\dashboard_screen.dart` (lines 330-390), header contains user profile greeting, store selector pill, notification button, and settings button. Lines 1514-1594 contain core module launchers (Kasir POS, Dapur KDS, Presensi Tim, HR & Staf, Laporan, Inventori). No `OwnerApprovalScreen` launcher or pending approvals badge exists yet.
   - In `c:\laragon\www\cooca_my_own\lib\core\services\pos_offline_sync_manager.dart` (lines 37-133), offline-resilient pattern exists using `client_uuid` and RFC4122 v4 UUID generation with server reconciliation.

---

## 2. Logic Chain

1. **Petty Cash Integration:**
   - The user request states: *"mencatat mutasi pengeluaran kas kecil pada cash ledger (CashLedgerWebController / CashLedgerEntry), serta mendukung upload foto/bukti nota struk"*.
   - Because `PosShift` already tracks `total_cash_out` and `PosShiftService::getShiftSummary()` computes expected cash as `$opening + $cashSales + $cashIn - $cashOut`, recording a petty cash expense must both:
     a) Insert a record in `pos_expenses` and increment `$shift->total_cash_out` (via `PosCashMovement` of type `cash_out`).
     b) Call `CashLedgerService::recordOutflow()` to reflect the outflow in the store's primary cash ledger account (`CashTransaction`).
   - For file upload, using `TenantStorage::publicDir($business, TenantStorage::FOLDER_EXPENSES)` maintains multi-tenant isolation, storing the physical receipt in `storage/app/public/bisnis/{slug}/expenses/{filename}` and returning its public URL.
   - On the mobile client (`cooca_my_own`), `PosExpenseModal` connects to this endpoint. If offline, `PosOfflineSyncManager` buffers the payload with `client_uuid` and base64 image data, ensuring zero data loss.

2. **Owner Approval Center Integration:**
   - The user request states: *"Owner Approval Center terintegrasi pada backend cooca_core dan mobile cooca_my_own: Buat endpoint API GET /api/v1/owner/approvals/pending ... dan POST /api/v1/owner/approvals/{id}/action ... untuk persetujuan pembatalan transaksi (void), diskon di atas batas, dan koreksi absensi"*.
   - In `cooca_core`, approval requests currently reside across `PosApprovalRequest` (void, high_discount) and `AttendanceCorrection` (attendance time fixes).
   - The new `GET /api/v1/owner/approvals/pending` API unifies and normalizes these heterogeneous models into a single uniform payload schema (`id`, `type`, `title`, `description`, `requester`, `meta`, `created_at`).
   - The action endpoint `POST /api/v1/owner/approvals/{id}/action` takes `{ action: 'approve' | 'reject', reason: string }`, updates the status, executes the side-effect (e.g. marking `PosOrder` voided, updating attendance timestamps), and enforces idempotency so already-processed approvals return `422`.
   - On `cooca_my_own`, `OwnerApprovalScreen` implements Apple HIG swipe cards (`Dismissible`: right = approve with medium haptic, left = reject with prompt dialog and heavy haptic). The dashboard displays an alert badge when `pendingApprovalsCount > 0`.

---

## 3. Caveats

- **Tenancy Context:** Both `POST /api/v1/pos/expenses` and `/api/v1/owner/approvals/*` strictly require `auth:sanctum` and `business.active` middleware. Requests without active business context or from unauthorized roles will be rejected (401/403).
- **Offline Receipt Media:** Offline sync stores receipt images as base64 in local SQLite/preferences. Very large images could consume memory; client-side image compression (quality: 75%, max width: 1024px) should be enforced prior to base64 encoding.
- **Side-Effect Scope for Void:** When an order is voided upon approval, if table seating or inventory reservation was active, it should be released/restored safely within a DB transaction.

---

## 4. Conclusion

The specification contracts for Phase 1 are thoroughly researched, formalized, and documented in `c:\laragon\www\cooca_core\.agents\teamwork\explorer_survey_3\report.md`. Both repositories (`cooca_core` and `cooca_my_own`) possess clean foundational services (`PosShiftService`, `CashLedgerService`, `PosOfflineSyncManager`, `MerchantState`) ready for direct implementation according to this specification.

---

## 5. Verification Method

To verify the specification and prepare for implementation:
1. **Inspect Report:**
   Read `c:\laragon\www\cooca_core\.agents\teamwork\explorer_survey_3\report.md`.
2. **Backend Route & Model Verification:**
   - Verify `pos_shifts` table exists via `php artisan migrate:status`.
   - Check Sanctum authentication in `routes/api.php` line 894.
3. **Mobile Screen Verification:**
   - Verify `pos_shift_modal.dart` line 109 for expected cash derivation.
   - Run `flutter analyze` inside `c:\laragon\www\cooca_my_own` to ensure the mobile base is currently clean.
