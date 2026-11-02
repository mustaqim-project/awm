# Specification & Contract Mining Report: Phase 1 Integration (cooca_core & cooca_my_own)

**Mining Agent:** `explorer_survey_3` (teamwork_spec_miner)  
**Target Milestone:** Phase 1 — Anti-Bocor Kasir (Petty Cash / Quick Expense & Owner Approval Center)  
**Date:** 2026-11-02  
**Repositories:**  
- Backend API: `c:\laragon\www\cooca_core` (Laravel 11, Sanctum, Eloquent, Context Tenancy)  
- Mobile Client: `c:\laragon\www\cooca_my_own` (Flutter 3.47 / Dart 3.13, Provider/Listenable, Apple HIG)  

---

## Executive Summary

This report establishes the complete, authoritative specification contracts for **Phase 1** of the Core-to-Mobile feature integration plan. It formalizes:
1. **Petty Cash / Quick Expense Kasir API Specification** (`POST /api/v1/pos/expenses`).
2. **Owner Approval Center API Specification** (`GET /api/v1/owner/approvals/pending` & `POST /api/v1/owner/approvals/{id}/action`).
3. **Mobile Component Contracts & UI/UX State Architecture** (`PosExpenseModal`, `OwnerApprovalScreen`, `MerchantState`, and `pos_shift_modal.dart` integration).
4. **Comprehensive Automated Test Matrices** for both Backend (PHPUnit/Pest) and Mobile (Flutter Widget & Unit Tests).

---

## 1. Feature Discovery Matrix

| # | Category | Feature | Description | Inputs | Outputs | Error Behavior | Discovered Via |
|---|----------|---------|-------------|--------|---------|----------------|----------------|
| 1 | POS / Finance | Petty Cash Quick Expense | Records cashier quick out-of-drawer expenses with category & receipt photo. Directly links to active POS shift and Cash Ledger. | `pos_shift_id`, `amount`, `category`, `notes`, `receipt_image` (file/base64) | `201 Created` with expense, shift summary & receipt URL | `401` Unauth, `403` Forbidden, `422` Unprocessable (closed shift, invalid amount/category) | `plan_core_to_mobile_features.md`, `PosShiftService.php`, `CashLedgerService.php` |
| 2 | Shift Reconciliation | Expected Cash Formula Update | Dynamic cash drawer reconciliation deducting petty cash expenses from closing expected cash. | `opening_cash`, `cash_sales`, `cash_in`, `cash_out` (petty cash) | Recalculated `expected_cash` and `cash_difference` | Over/Under variance detection (`isShort`, `isOver`, `isBalanced`) | `PosShift.php`, `pos_shift_modal.dart` |
| 3 | Approvals | Owner Pending Approvals API | Unified retrieval of pending operational approval requests across POS (void, high discount), HR (attendance correction), and documents. | Optional query filters: `type`, `date` | `200 OK` with normalized pending items array & counts | `401` Unauth, `403` Forbidden (requires owner/admin role) | `pos_approval_requests`, `attendance_corrections`, `ApprovalRequest.php` |
| 4 | Approvals | Owner Approval Action API | Executes approval or rejection of pending item with idempotency and side-effect dispatch. | `id` (route param), `action` ('approve'\|'reject'), `reason` (string) | `200 OK` with updated item state | `422` if already resolved or missing reject reason, `404` if not found | `PosApprovalRequest.php`, `AttendanceApiController.php` |
| 5 | Mobile UI | PosExpenseModal Component | Apple HIG modal sheet with currency input, category picker, camera/gallery receipt capture & offline resilience. | User form inputs, device camera stream | Dismiss modal, trigger shift refresh, local sync queue if offline | Shows user-friendly error toast/snackbars | `pos_shift_modal.dart`, `ios_tokens.dart` |
| 6 | Mobile UI | OwnerApprovalScreen Component | Dedicated Apple HIG screen with pull-to-refresh, filter segments, swipe-to-approve (right), swipe-to-reject (left with dialog). | Swipe gestures, tap actions | Instant optimistic card removal, network dispatch, haptic feedback | Haptic heavy impact & error alert on failure | Apple HIG Cupertino Guidelines, `ios_adaptive.dart` |
| 7 | Mobile UX | Dashboard Approval Badge | Live badge indicating pending approvals count on the executive mobile dashboard. | `state.pendingApprovalsCount` | Visible badge pill on header and launcher dock | Silently hides if count is 0 | `dashboard_screen.dart`, `MerchantState.dart` |

---

## 2. API Contract Specification: Petty Cash / Quick Expense Kasir

### 2.1. Route Definition & Security
- **Method:** `POST`
- **Path:** `/api/v1/pos/expenses`
- **Route Name:** `api.v1.pos.expenses.store`
- **Authentication:** `auth:sanctum`
- **Context Guard:** `business.active`
- **Permission Required:** `require.permission:pos.terminal` (or `require.permission:pos.orders`)
- **Rate Limit:** `throttle:300,1`

### 2.2. Request Headers
```http
Authorization: Bearer <sanctum_token>
X-Business-Id: <business_uuid>
Content-Type: multipart/form-data (or application/json for base64)
Accept: application/json
```

### 2.3. Request Payload Specification
| Field Name | Type | Presence | Validation Rules | Description |
|---|---|---|---|---|
| `pos_shift_id` | String (UUID) | **Required** | `required`, `uuid`, `exists:pos_shifts,id` | ID of the active POS shift under the current business. |
| `amount` | Numeric (Float) | **Required** | `required`, `numeric`, `gt:0`, `max:999999999` | Amount in Rupiah spent from the cash drawer. |
| `category` | String | **Required** | `required`, `string`, `in:supplies,operational,refund,other` | Standard expense classification. |
| `notes` | String | **Optional** / Conditional | `nullable`, `string`, `max:255`, `required_if:category,other` | Purpose of the expense (e.g. "Beli es batu kristal 2 bal"). Required if category is `other`. |
| `receipt_image` | File / Binary | **Optional** | `nullable`, `file`, `mimes:jpeg,png,jpg,webp`, `max:5120` | Physical receipt photo from camera/gallery (up to 5MB). |
| `receipt_image_base64` | String | **Optional** | `nullable`, `string` | Base64-encoded receipt string (alternative for offline sync). |
| `client_uuid` | String (UUID) | **Optional** | `nullable`, `uuid` | Client-generated RFC4122 v4 idempotency token from offline sync manager. |

### 2.4. Business Rules & Side-Effects
1. **Tenant & Shift Integrity:**
   - Verify `pos_shift.business_id === Context::businessId()`. Abort `403` if cross-tenant.
   - Verify `pos_shift.status === PosShift::STATUS_OPEN`. If closed, reject with `422 Unprocessable Content` ("Shift kasir telah ditutup. Pengeluaran kas kecil hanya dapat dicatat pada shift aktif.").
2. **Shift Balance & Cash Movement:**
   - Record a new `PosCashMovement`:
     - `business_id`: `$shift->business_id`
     - `pos_shift_id`: `$shift->id`
     - `user_id`: `$user->id`
     - `type`: `PosCashMovement::TYPE_CASH_OUT`
     - `amount`: `$amount`
     - `reason`: `"[Kas Kecil - {$categoryLabel}] " . ($notes ?: 'Pengeluaran Kasir')`
   - Atomically increment `$shift->total_cash_out` by `$amount`.
3. **Cash Ledger Integration:**
   - Dispatch to `CashLedgerService::recordOutflow()`:
     - `business`: `$business`
     - `amount`: `$amount`
     - `referenceType`: `'pos_expense'`
     - `referenceId`: `$expense->id` (or `$movement->id`)
     - `description`: `"[Kasir - {$categoryLabel}] " . ($notes ?: 'Pengeluaran Kasir')`
     - `method`: `'cash'`
     - `userId`: `$user->id`
   - Deducts from active `CashAccount` of type `cash` ("Kas Utama").
4. **Receipt File Storage:**
   - Store photo using `TenantStorage::publicDir($business, TenantStorage::FOLDER_EXPENSES)`.
   - Generate accessible public URL via `Storage::disk('public')->url($storedPath)`.
5. **Audit Logging:**
   - Write to `AuditLog`: action `pos.petty_cash_expense`, risk `LOW`.

### 2.5. Response Formats

#### 2.5.1. Success Response (201 Created)
```json
{
  "success": true,
  "message": "Pengeluaran kas kecil sebesar Rp 25.000 berhasil dicatat.",
  "data": {
    "id": "7b8e192a-3c4d-4e5f-9a1b-0c2d3e4f5a6b",
    "pos_shift_id": "9b1a5b82-628d-4252-9653-61b47b2c0199",
    "amount": 25000.0,
    "category": "supplies",
    "category_label": "Bahan Baku Darurat",
    "notes": "Beli es batu kristal 2 bal",
    "receipt_path": "bisnis/kopi-kenangan/expenses/expense_1714651200_abc123.jpg",
    "receipt_url": "https://cooca.id/storage/bisnis/kopi-kenangan/expenses/expense_1714651200_abc123.jpg",
    "cash_movement_id": "8a7b6c5d-4e3f-2a1b-0c9d-8e7f6a5b4c3d",
    "cash_transaction_id": "1a2b3c4d-5e6f-7a8b-9c0d-1e2f3a4b5c6d",
    "created_at": "2026-11-02T12:00:00.000000Z"
  }
}
```

#### 2.5.2. Error Responses
- **401 Unauthorized:**
  ```json
  { "message": "Unauthenticated." }
  ```
- **403 Forbidden:**
  ```json
  {
    "success": false,
    "message": "Anda tidak memiliki hak otorisasi untuk mencatat pengeluaran kasir pada outlet ini."
  }
  ```
- **422 Unprocessable Content (Shift Closed):**
  ```json
  {
    "success": false,
    "message": "Shift kasir telah ditutup. Pengeluaran kas kecil hanya dapat dicatat pada shift aktif.",
    "errors": {
      "pos_shift_id": ["Shift kasir yang dipilih sudah tidak aktif atau ditutup."]
    }
  }
  ```
- **422 Validation Error (Invalid Amount / Missing Notes for 'other'):**
  ```json
  {
    "success": false,
    "message": "Data pengeluaran tidak valid.",
    "errors": {
      "amount": ["Nominal kas harus lebih besar dari nol."],
      "notes": ["Keterangan rincian wajib diisi ketika kategori Lainnya dipilih."]
    }
  }
  ```

---

## 3. API Contract Specification: Owner Approval Center

### 3.1. Route Definitions & Security
- **Index Pending:** `GET /api/v1/owner/approvals/pending`
- **Execute Action:** `POST /api/v1/owner/approvals/{id}/action`
- **Authentication:** `auth:sanctum`
- **Context Guard:** `business.active`
- **Role Guard:** `require.role:owner,admin`
- **Rate Limit:** `throttle:180,1`

---

### 3.2. Endpoint: `GET /api/v1/owner/approvals/pending`

#### 3.2.1. Query Parameters
| Parameter | Type | Required | Default | Description |
|---|---|---|---|---|
| `type` | String | No | `all` | Filter by approval type: `all`, `void`, `discount`, `attendance`, `document`. |
| `date` | String | No | `null` | Filter by ISO date format (`YYYY-MM-DD`). |
| `limit` | Integer | No | `50` | Pagination limit (max 100). |

#### 3.2.2. Success Response (200 OK)
```json
{
  "success": true,
  "total_pending": 3,
  "counts": {
    "all": 3,
    "void": 1,
    "discount": 1,
    "attendance": 1
  },
  "data": [
    {
      "id": "pos_appr_9c1a5b82-628d-4252-9653-61b47b2c0199",
      "type": "void",
      "source": "pos_approval",
      "title": "Pembatalan Pesanan #ORD-20261102-0012",
      "description": "Kasir meminta void karena salah input meja dan item",
      "status": "pending",
      "requester": {
        "id": "11111111-1111-1111-1111-111111111111",
        "name": "Budi Santoso",
        "role": "cashier"
      },
      "meta": {
        "order_id": "22222222-2222-2222-2222-222222222222",
        "order_number": "ORD-20261102-0012",
        "original_amount": 125000.0,
        "discount_amount": 0.0,
        "reason": "Pelanggan membatalkan pesanan sebelum dimasak",
        "location_name": "Outlet Utama"
      },
      "created_at": "2026-11-02T11:45:00.000000Z"
    },
    {
      "id": "pos_appr_9c1a5b82-628d-4252-9653-61b47b2c0200",
      "type": "discount",
      "source": "pos_approval",
      "title": "Diskon Khusus Kasir 25% (Plafon Max 10%)",
      "description": "Diskon di atas wewenang kasir untuk relasi owner",
      "status": "pending",
      "requester": {
        "id": "11111111-1111-1111-1111-111111111111",
        "name": "Budi Santoso",
        "role": "cashier"
      },
      "meta": {
        "order_id": "33333333-3333-3333-3333-333333333333",
        "order_number": "ORD-20261102-0015",
        "original_amount": 200000.0,
        "discount_percent": 25.0,
        "discount_amount": 50000.0,
        "reason": "Tamu VIP rekanan Direksi",
        "location_name": "Outlet Utama"
      },
      "created_at": "2026-11-02T11:50:00.000000Z"
    },
    {
      "id": "att_corr_9c1a5b82-628d-4252-9653-61b47b2c0201",
      "type": "attendance",
      "source": "attendance_correction",
      "title": "Koreksi Absensi: Siti Rahma (Lupa Clock-In)",
      "description": "Pengajuan jam hadir 08:00 (Router wifi outlet sempat offline)",
      "status": "pending",
      "requester": {
        "id": "44444444-4444-4444-4444-444444444444",
        "name": "Siti Rahma",
        "role": "barista"
      },
      "meta": {
        "correction_number": "CORR-202611-0004",
        "target_date": "2026-11-02",
        "correction_type": "clock_in_only",
        "proposed_clock_in": "08:00:00",
        "proposed_clock_out": null,
        "reason": "Sudah hadir tepat waktu namun router wifi sedang restart",
        "attachment_url": null
      },
      "created_at": "2026-11-02T11:55:00.000000Z"
    }
  ]
}
```

---

### 3.3. Endpoint: `POST /api/v1/owner/approvals/{id}/action`

#### 3.3.1. Request Payload Specification
| Field Name | Type | Presence | Validation Rules | Description |
|---|---|---|---|---|
| `action` | String | **Required** | `required`, `in:approve,reject` | The action to execute. |
| `reason` | String | **Conditional** | `nullable`, `string`, `max:500`, `required_if:action,reject`, `min:3` | Justification note. Required when `action === 'reject'`. |
| `source` | String | **Optional** | `nullable`, `string`, `in:pos_approval,attendance_correction,document_approval` | Target entity namespace disambiguator. |

#### 3.3.2. Idempotency & Concurrency Rules
- Requests are handled inside database transactions with row-level locking (`lockForUpdate()`).
- If target approval record is not in `status === 'pending'`, the endpoint aborts with `422 Unprocessable Content`:
  ```json
  {
    "success": false,
    "message": "Permintaan persetujuan ini sudah selesai diproses sebelumnya (Status: approved)."
  }
  ```

#### 3.3.3. Side-Effect Execution Rules
1. **For Void Order Approvals (`type === 'void'`):**
   - On `approve`:
     - Updates `PosApprovalRequest`: `status = 'approved'`, `approved_by = auth()->id()`, `approved_at = now()`.
     - Finds corresponding `PosOrder`: updates status to `PosOrder::STATUS_VOIDED`, sets `voided_at = now()`, `voided_by = auth()->id()`, and records `void_reason`.
     - Releases table if dine-in order.
     - Restores stock inventory if inventory movement was previously booked.
     - Logs `AuditLog` action `pos.order_voided_by_owner` (Medium Risk).
   - On `reject`:
     - Updates `PosApprovalRequest`: `status = 'rejected'`, `rejection_reason = $reason`, `approved_by = auth()->id()`, `approved_at = now()`.
     - `PosOrder` remains in its current status.
2. **For High Discount Approvals (`type === 'discount'`):**
   - On `approve`:
     - Updates `PosApprovalRequest`: `status = 'approved'`, `approved_by = auth()->id()`.
     - Authorizes the discount override on the order cart.
   - On `reject`:
     - Updates `PosApprovalRequest`: `status = 'rejected'`, `rejection_reason = $reason`.
3. **For Attendance Corrections (`type === 'attendance'`):**
   - On `approve`:
     - Dispatches `AttendanceService::approveCorrection($business, $correction, $user, $reason)`.
     - Synchronizes employee's `Attendance` record with proposed clock-in / clock-out times.
   - On `reject`:
     - Dispatches `AttendanceService::rejectCorrection($business, $correction, $user, $reason)`.

#### 3.3.4. Success Response (200 OK)
```json
{
  "success": true,
  "message": "Permintaan persetujuan pembatalan pesanan berhasil disetujui.",
  "data": {
    "id": "pos_appr_9c1a5b82-628d-4252-9653-61b47b2c0199",
    "type": "void",
    "status": "approved",
    "action": "approve",
    "reviewed_by": "1516ea67-...",
    "reviewed_at": "2026-11-02T12:05:00.000000Z"
  }
}
```

---

## 4. Mobile Component Contracts (Flutter `cooca_my_own`)

### 4.1. `PosExpenseModal` (`lib/screens/pos/pos_expense_modal.dart`)

#### 4.1.1. Visual & Interaction Architecture (Apple HIG)
- **Container:** Cupertino Bottom Sheet modal (`CupertinoColors.systemBackground`, 22dp top border radius, drag grabber 36x4.5dp).
- **Header:**
  - Icon pill: `CupertinoIcons.money_dollar_circle_fill` in amber background (`#FF9500`).
  - Title: *"Catat Biaya Kas Kecil (Petty Cash)"*.
  - Subtitle: *"Pengeluaran langsung memotong saldo laci kasir shift aktif"*.
- **Category Selector:**
  - Dropdown / segmented chips:
    - 🛒 `supplies`: Bahan Baku Darurat (Es batu, galon, LPG)
    - ⚡ `operational`: Operasional Toko (Parkir, plastik, kresek, sabun cuci)
    - 🔄 `refund`: Pengembalian Tunai Manual
    - 📝 `other`: Lain-lain (Wajib isi rincian)
- **Amount Field:**
  - Big bold typography (22pt, `FontWeight.w800`, `FontFeature.tabularFigures()`).
  - IDR formatted (`Rp ` prefix, automatic thousand separators `Rp 25.000`).
- **Notes Field:**
  - Multiline text area with subtle border (`border: 0.8dp #E2E8F0` / dark `#38383A`).
- **Photo Receipt Button & Preview:**
  - Camera & Gallery Action Sheet using `image_picker`.
  - Preview card with thumbnail (64x64dp rounded 10dp) and prominent remove button (`CupertinoIcons.xmark_circle_fill`).
- **Submit Button:**
  - Full-width Cupertino button in warning orange (`#FF9500`), minimum 48dp touch target.
  - Loading spinner (`CupertinoActivityIndicator`) during network submission.

#### 4.1.2. Offline Resilience Contract
- Upon pressing **"Simpan Pengeluaran"**:
  1. Inspect network connectivity using `PosOfflineSyncManager().isOnline`.
  2. If online:
     - Execute HTTP multipart request to `POST /api/v1/pos/expenses`.
  3. If offline:
     - Generate RFC4122 v4 UUID `client_uuid = PosOfflineSyncManager.generateClientUuid()`.
     - Convert receipt image to base64.
     - Store payload into `LocalStorageService.queueOfflineExpense({ client_uuid, pos_shift_id, amount, category, notes, receipt_base64 })`.
     - Show informational notification: *"Tersimpan di antrean offline. Akan disinkronkan otomatis saat online."*
     - Optimistically deduct expected cash in local state.

#### 4.1.3. Integration with `pos_shift_modal.dart`
- In `_buildShiftActiveView()` of `pos_shift_modal.dart`:
  - Add button *"Catat Kas Kecil"* alongside Mutasi Kas.
  - Update expected cash formula calculation:
    $$\text{Expected Cash} = \text{Opening Cash} + \text{Cash Sales} + \text{Cash In} - \text{Cash Out (Petty Cash)}$$
  - Add line item in financial summary breakdown:
    - *Kas Masuk (Inflow): Rp XX.XXX*
    - *Kas Kecil Kasir (Petty Cash): -Rp XX.XXX*
    - *Kas Seharusnya di Laci: Rp XX.XXX*

---

### 4.2. `OwnerApprovalScreen` (`lib/screens/approvals/owner_approval_screen.dart`)

#### 4.2.1. Visual & Interaction Architecture (Apple HIG)
- **Navigation Bar:** Large title *"Pusat Persetujuan"*, trailing refresh icon button.
- **Segmented Control:** Apple HIG pill switcher:
  - `[0] Semua (3)` | `[1] Void (1)` | `[2] Diskon (1)` | `[3] Absensi (1)`
- **Pull-to-Refresh:** `CupertinoSliverRefreshControl` triggering `state.refreshPendingApprovals()`.
- **Card Swipe Gesture Contract (`Dismissible`):**
  - **Swipe Right (`startToEnd`):**
    - Background: Emerald green (`#34C759`), left-aligned `CupertinoIcons.checkmark_alt_circle_fill`, label *"Setujui"*.
    - Haptic: `HapticFeedback.mediumImpact()`.
    - Action: Executes `POST /api/v1/owner/approvals/{id}/action` with `action: 'approve'`.
    - UI: Smooth card dismiss animation; decrement pending badge.
  - **Swipe Left (`endToStart`):**
    - Background: Crimson red (`#FF3B30`), right-aligned `CupertinoIcons.xmark_circle_fill`, label *"Tolak"*.
    - Haptic: `HapticFeedback.heavyImpact()`.
    - Action: Pops a Cupertino modal dialog requesting mandatory reason (`"Masukkan Alasan Penolakan"`).
    - If confirmed: Executes `POST /api/v1/owner/approvals/{id}/action` with `action: 'reject'`, `reason: userInput`.
    - If cancelled: Dismissible snaps back into place (`confirmDismiss` returns `false`).
- **Accessibility Fallback:** Every card includes explicit secondary buttons: `[Tolak]` (outline red) and `[Setujui]` (solid green) for users unable to swipe.

#### 4.2.2. Badge Integration on `dashboard_screen.dart`
- In `_buildExecutiveHeader` and `_buildModuleLauncherSection`:
  - Track `state.pendingApprovalsCount`.
  - If `pendingApprovalsCount > 0`:
    - Display an animated red badge (`#FF3B30`) with white text and glow shadow.
    - Add *"Persetujuan"* launcher card in the module section with the pending count badge.
    - Tap event routes directly to `OwnerApprovalScreen`.

---

## 5. Comprehensive Test Matrices

### 5.1. Backend Automated Test Matrix (`cooca_core` PHPUnit/Pest)

| Test ID | Test Case | Target Endpoint / Method | Expected Result |
|---|---|---|---|
| `TEST-BE-01` | Cashier can record valid petty cash expense with receipt upload | `POST /api/v1/pos/expenses` | `201 Created`, database record created in `pos_expenses`, file stored in tenant folder. |
| `TEST-BE-02` | Unauthenticated request rejected | `POST /api/v1/pos/expenses` | `401 Unauthorized`. |
| `TEST-BE-03` | Cashier from different business cannot post expense (cross-tenant attack) | `POST /api/v1/pos/expenses` | `403 Forbidden` / `404 Not Found`. |
| `TEST-BE-04` | Attempting to record expense on closed shift fails | `POST /api/v1/pos/expenses` | `422 Unprocessable Content` with message indicating shift is closed. |
| `TEST-BE-05` | Negative or zero amount rejected | `POST /api/v1/pos/expenses` | `422 Validation Error` on `amount`. |
| `TEST-BE-06` | Expense updates POS shift's `total_cash_out` | `PosShiftService::recordCashMovement` | `$shift->total_cash_out` increments exactly by expense amount. |
| `TEST-BE-07` | Expense decrements expected cash in shift summary | `PosShiftService::getShiftSummary` | `expected_cash` equals `opening + cashSales + cashIn - cashOut`. |
| `TEST-BE-08` | CashLedger records outflow transaction | `CashLedgerService::recordOutflow` | `CashTransaction` created with type `out`, balance deducted from cash account. |
| `TEST-BE-09` | Owner can fetch pending approval requests | `GET /api/v1/owner/approvals/pending` | `200 OK` returning unified list of POS voids, discounts, and attendance corrections. |
| `TEST-BE-10` | Non-owner/non-admin staff cannot view pending approvals | `GET /api/v1/owner/approvals/pending` | `403 Forbidden`. |
| `TEST-BE-11` | Owner approves void request and order status becomes voided | `POST /api/v1/owner/approvals/{id}/action` | `200 OK`, `PosApprovalRequest` status `approved`, `PosOrder` status `voided`. |
| `TEST-BE-12` | Rejecting approval requires mandatory reason | `POST /api/v1/owner/approvals/{id}/action` | `422 Unprocessable Content` if reason is empty or `< 3` chars. |
| `TEST-BE-13` | Duplicate approval action prevented (Idempotency) | `POST /api/v1/owner/approvals/{id}/action` | `422 Unprocessable Content` if already approved or rejected. |
| `TEST-BE-14` | Approving attendance correction synchronizes employee attendance | `POST /api/v1/owner/approvals/{id}/action` | `200 OK`, `Attendance` updated with proposed clock-in timestamp. |

---

### 5.2. Mobile Automated Test Matrix (`cooca_my_own` Flutter Test)

| Test ID | Test Case | Target Screen / Component | Expected Result |
|---|---|---|---|
| `TEST-FE-01` | Render PosExpenseModal with all form fields | `PosExpenseModal` | Finds category dropdown, amount input with `Rp` prefix, notes field, photo button. |
| `TEST-FE-02` | Validate empty amount triggers error message | `PosExpenseModal` | Error toast/validation warning shown when submitting empty/0 amount. |
| `TEST-FE-03` | Selecting category updates form state | `PosExpenseModal` | Category dropdown reflects user selection (`supplies`, `operational`, `refund`, `other`). |
| `TEST-FE-04` | Image preview appears when photo is chosen | `PosExpenseModal` | Thumbnail and remove icon displayed in modal. |
| `TEST-FE-05` | Expected cash calculation formula in PosShiftModal | `PosShiftModal` | Verify `_expectedCash` reflects `opening + cash_sales - petty_cash_expenses`. |
| `TEST-FE-06` | Offline expense queueing when network disconnected | `PosExpenseModal` & `PosOfflineSyncManager` | Expense saved to local storage with `client_uuid` and pending status. |
| `TEST-FE-07` | Render OwnerApprovalScreen with pending cards | `OwnerApprovalScreen` | Cards render with appropriate type badges (`Void`, `Diskon`, `Absensi`). |
| `TEST-FE-08` | Empty state displayed when zero approvals pending | `OwnerApprovalScreen` | Apple HIG empty state illustration and text displayed. |
| `TEST-FE-09` | Swipe right triggers approval callback | `OwnerApprovalScreen` | Dismissible triggers approve action with medium haptic feedback. |
| `TEST-FE-10` | Swipe left opens rejection reason dialog | `OwnerApprovalScreen` | Prompt dialog displayed with text field and submit button. |
| `TEST-FE-11` | Dashboard reflects pending approvals badge count | `DashboardScreen` | Red badge with count `3` rendered on header and launcher. |

---

## 6. Implementation Checklist & File Modification Plan

### 6.1. Backend (`c:\laragon\www\cooca_core`)
1. Create Migration: `database/migrations/2026_11_02_000001_create_pos_expenses_table.php`
2. Create Model: `app/Models/PosExpense.php` (BelongsToBusiness, HasUuid, Casts, Relations to Shift, User, CashTransaction, PosCashMovement)
3. Create API Controller: `app/Http/Controllers/Api/V1/Pos/PosExpenseApiController.php`
4. Create Owner API Controller: `app/Http/Controllers/Api/V1/Owner/OwnerApprovalApiController.php`
5. Update Routes: `routes/api.php`
   - Add `POST /v1/pos/expenses`
   - Add `GET /v1/owner/approvals/pending`
   - Add `POST /v1/owner/approvals/{id}/action`
6. Add Feature Tests:
   - `tests/Feature/Api/V1/PosExpenseApiTest.php`
   - `tests/Feature/Api/V1/OwnerApprovalCenterApiTest.php`

### 6.2. Mobile Client (`c:\laragon\www\cooca_my_own`)
1. Create Screen/Modal: `lib/screens/pos/pos_expense_modal.dart`
2. Create Screen: `lib/screens/approvals/owner_approval_screen.dart`
3. Update `lib/models/order.dart`: add `PosShift` support for petty cash out & `PosExpenseItem` model.
4. Update `lib/core/services/pos_service.dart`: add `recordPettyCashExpense()` and `fetchPendingApprovals()` & `actionApproval()`.
5. Update `lib/state/merchant_state.dart`: add `pendingApprovalsCount`, `refreshPendingApprovals()`, `recordPettyCashExpense()`.
6. Update `lib/screens/pos/pos_shift_modal.dart`: trigger button for `PosExpenseModal` & expected cash formula update.
7. Update `lib/screens/dashboard_screen.dart`: pending approval badge indicator on executive header & launcher dock.
8. Add Flutter Tests:
   - `test/pos_expense_modal_test.dart`
   - `test/owner_approval_screen_test.dart`

---
*Report certified by `explorer_survey_3` for Phase 1 Implementation.*
