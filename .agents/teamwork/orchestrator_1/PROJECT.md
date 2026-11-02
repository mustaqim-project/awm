# Project: COOCA Core to Mobile Phase 1 Integration (Petty Cash & Owner Approval Center)

## Architecture
- **Backend API (`c:\laragon\www\cooca_core`)**:
  - Laravel 11, PHP 8.2+, PHPUnit 12, SQLite `:memory:` for testing.
  - Authentication: Sanctum Bearer tokens with multi-tenant context (`business.active` middleware, `X-Business-Id` header).
  - Domain Services:
    - `PosShiftService`: manages shift lifecycles, records `PosCashMovement` (`type = 'cash_out'`), computes `expected_cash = opening + cash_sales + cash_in - cash_out`.
    - `CashLedgerService`: atomic `lockForUpdate()` ledger movements, records `CashTransaction` (`type = 'out'`), tracks store cash accounts.
    - `TenantStorage`: tenant-isolated media storage in `TenantStorage::privateDir($business, 'expenses')`.
    - Approvals Aggregator: unifies `PosApprovalRequest`, `AttendanceCorrection`, and `ApprovalRequest`.
- **Mobile Client (`c:\laragon\www\cooca_my_own`)**:
  - Flutter 3.47 / Dart 3.13, Provider/Listenable (`MerchantState`), Apple HIG styling (`ios_tokens.dart`, `ios_adaptive.dart`).
  - Network: `ApiClient` (Sanctum token + `X-Business-Id`).
  - Offline sync: `PosOfflineSyncManager` with `client_uuid` idempotency.
  - Hardware: `image_picker` / `camera` for receipt capture.

## Feature Inventory
| # | Feature | Description | Milestone | Source |
|---|---------|-------------|-----------|--------|
| 1 | Petty Cash Backend API | `POST /api/v1/pos/expenses` validating active shift, recording `PosCashMovement` and `CashLedgerService::recordOutflow`, storing receipt proof | M1 | Survey (explorer_survey_1, 3) |
| 2 | Owner Approval Center API | `GET /api/v1/owner/approvals/pending` & `POST /api/v1/owner/approvals/{id}/action` for void, discount, and attendance approvals with role guard | M1 | Survey (explorer_survey_1, 3) |
| 3 | Backend Automated Tests | PHPUnit test suite covering petty cash & approval center endpoints, active shift enforcement, ledger mutations, and role authorization | M1 | Survey (explorer_survey_1, 3) |
| 4 | Mobile Petty Cash Modal | `PosExpenseModal` with camera photo capture, amount & category inputs, and offline sync support | M2 | Survey (explorer_survey_2, 3) |
| 5 | Mobile Shift & POS Integration | Adding quick expense button to POS menu & `pos_shift_modal.dart`, deducting expenses from expected cash during close shift | M2 | Survey (explorer_survey_2, 3) |
| 6 | Mobile Owner Approval Screen | `OwnerApprovalScreen` with Apple HIG swipe cards (approve/reject), haptic feedback, and rejection reason prompt | M2 | Survey (explorer_survey_2, 3) |
| 7 | Mobile Dashboard Integration | Live pending approvals badge on `dashboard_screen.dart` and operational alert banner with direct navigation | M2 | Survey (explorer_survey_2, 3) |
| 8 | Mobile Automated Tests & Lint | Unit & widget tests for modal, shift expected cash calculation, and approval screen; `flutter analyze` 0 issues, `flutter test` 100% pass | M2 | Survey (explorer_survey_2, 3) |
| 9 | Cross-System Integration & Verification | Contract verification between mobile client requests and backend API responses | M3 | Survey (explorer_survey_3) |
| 10 | Adversarial Stress & Forensic Audit | Stress testing edge cases (closed shifts, unauthorized roles, duplicate actions) & Forensic Integrity Audit (CLEAN) | M3 | Orchestrator Protocol |

## Milestones
| # | Name | Scope | Dependencies | Status |
|---|------|-------|-------------|--------|
| 1 | Backend API & Tests (`cooca_core`) | F1, F2, F3: Implement `/api/v1/pos/expenses`, `/api/v1/owner/approvals/pending`, `/api/v1/owner/approvals/{id}/action` + PHPUnit test suite | none | DONE |
| 2 | Mobile UI, State & Tests (`cooca_my_own`) | F4, F5, F6, F7, F8: Implement `PosExpenseModal`, `OwnerApprovalScreen`, dashboard badge, shift expected cash sync + Flutter tests & analyze | M1 | DONE |
| 3 | Verification, Stress Test & Forensic Audit | F9, F10, F11: Reviewers, Challengers, and Forensic Auditor verify integrity and full end-to-end integration | M1, M2 | DONE |

## Interface Contracts

### 1. Petty Cash API: `POST /api/v1/pos/expenses`
- **Headers**:
  - `Authorization: Bearer <sanctum_token>`
  - `X-Business-Id: <business_uuid>`
  - `Accept: application/json`
- **Payload** (`multipart/form-data` or `application/json`):
  - `pos_shift_id`: required integer (must belong to active business and have status `'open'`)
  - `amount`: required numeric, min: 1
  - `category`: required string (in: `supplies`, `operational`, `meal`, `transport`, `repair`, `refund`, `other`)
  - `notes`: required string, max: 500
  - `receipt_image`: optional file (mimes: jpeg, png, jpg, webp, max: 5120)
  - `receipt_image_base64`: optional string (base64 encoded data URI)
- **Response `201 Created`**:
  ```json
  {
    "success": true,
    "message": "Pengeluaran kas kecil berhasil dicatat.",
    "data": {
      "id": 1,
      "pos_shift_id": 10,
      "amount": 25000,
      "category": "supplies",
      "notes": "Beli es batu dan kantong kresek",
      "receipt_url": "/storage/bisnis/slug/expenses/uuid.jpg",
      "cash_movement_id": 45,
      "cash_transaction_id": 89,
      "created_at": "2026-11-02T05:30:00Z"
    }
  }
  ```
- **Error Responses**:
  - `401 Unauthenticated`: Missing/invalid token.
  - `403 Forbidden`: User lacks `pos.terminal` permission or business context.
  - `422 Unprocessable Entity`: Validation failure or shift is closed (`"Shift kasir telah ditutup atau tidak aktif"`).

### 2. Owner Approvals: `GET /api/v1/owner/approvals/pending`
- **Headers**:
  - `Authorization: Bearer <sanctum_token>`
  - `X-Business-Id: <business_uuid>`
- **Response `200 OK`**:
  ```json
  {
    "success": true,
    "count": 2,
    "data": [
      {
        "id": "pos_approval_12",
        "type": "void",
        "source": "pos",
        "reference_id": 12,
        "title": "Pembatalan Transaksi POS #TRX-9821",
        "description": "Pelanggan salah pesan menu",
        "amount": 145000,
        "requester": {
          "id": 5,
          "name": "Budi Kasir",
          "role": "cashier"
        },
        "meta": {
          "order_id": 12,
          "order_number": "TRX-9821",
          "reason": "Salah input menu oleh kasir"
        },
        "created_at": "2026-11-02T05:10:00Z"
      },
      {
        "id": "attendance_correction_4",
        "type": "attendance",
        "source": "hrm",
        "reference_id": 4,
        "title": "Koreksi Presensi: Siti Rahma",
        "description": "Lupa clock-out karena listrik padam",
        "amount": null,
        "requester": {
          "id": 8,
          "name": "Siti Rahma",
          "role": "staff"
        },
        "meta": {
          "correction_type": "clock_out",
          "proposed_time": "17:00:00"
        },
        "created_at": "2026-11-02T05:15:00Z"
      }
    ]
  }
  ```

### 3. Owner Approval Action: `POST /api/v1/owner/approvals/{id}/action`
- **URL Param `{id}`**: compound or integer ID (e.g. `pos_approval_12` or numeric ID with type header/field)
- **Payload**:
  - `action`: required string (in: `approve`, `reject`)
  - `reason`: string (required if action is `reject`, optional if `approve`)
- **Response `200 OK`**:
  ```json
  {
    "success": true,
    "message": "Permintaan persetujuan berhasil diproses.",
    "data": {
      "id": "pos_approval_12",
      "status": "approved",
      "action_taken": "approve",
      "reviewed_by": "Owner Utama",
      "reviewed_at": "2026-11-02T05:32:00Z"
    }
  }
  ```

### 4. Mobile Shift Expected Cash Formula
- In `pos_shift_modal.dart` and `PosShift`:
  `expectedCash = openingCash + totalCashSales + totalCashIn - totalCashOut (pettyCashExpenses)`

## Code Layout & Boundaries
- **Backend `c:\laragon\www\cooca_core`**:
  - Controller: `app/Http/Controllers/Api/V1/Pos/PosExpenseApiController.php`
  - Controller: `app/Http/Controllers/Api/V1/Owner/OwnerApprovalApiController.php`
  - Route additions: `routes/api.php`
  - Tests: `tests/Feature/Api/PosExpenseApiTest.php`, `tests/Feature/Api/OwnerApprovalApiTest.php`
- **Mobile `c:\laragon\www\cooca_my_own`**:
  - Modal: `lib/screens/pos/pos_expense_modal.dart`
  - Approvals screen: `lib/screens/approvals/owner_approval_screen.dart`
  - Service: `lib/core/services/approvals_service.dart`
  - Screen updates: `lib/screens/pos/pos_shift_modal.dart`, `lib/screens/pos_screen.dart`, `lib/screens/dashboard_screen.dart`, `lib/state/merchant_state.dart`
  - Tests: `test/pos_expense_modal_test.dart`, `test/owner_approval_screen_test.dart`, `test/pos_shift_expense_test.dart`
