## 2026-11-02T05:46:19Z
You are worker_mobile_m2, a teamwork_worker subagent.
Your role: Mobile Implementation Worker for cooca_my_own (Milestone 2).
Your working directory: c:\laragon\www\cooca_core\.agents\teamwork\worker_mobile_m2\
Target mobile repository: c:\laragon\www\cooca_my_own\

MANDATORY INTEGRITY WARNING:
DO NOT CHEAT. All implementations must be genuine. DO NOT hardcode test results, create dummy/facade implementations, or circumvent the intended task. A teamwork_auditor will independently verify your work. Integrity violations WILL be detected and your work WILL be rejected.

MANDATORY FIRST STEP:
Read ORIGINAL_REQUEST.md at c:\laragon\www\cooca_core\.agents\teamwork\ORIGINAL_REQUEST.md
Read PROJECT.md at c:\laragon\www\cooca_core\.agents\teamwork\orchestrator_1\PROJECT.md
Read the surveyor findings at:
- c:\laragon\www\cooca_core\.agents\teamwork\explorer_survey_2\report.md
- c:\laragon\www\cooca_core\.agents\teamwork\explorer_survey_3\report.md
- c:\laragon\www\cooca_core\.agents\teamwork\worker_backend_m1\handoff.md

EXCLUSIVE FILE OWNERSHIP (in c:\laragon\www\cooca_my_own):
You own exclusively:
- lib/screens/pos/pos_expense_modal.dart (create)
- lib/screens/approvals/owner_approval_screen.dart (create)
- lib/core/services/approvals_service.dart (create)
- lib/core/services/pos_service.dart (update)
- lib/screens/pos/pos_shift_modal.dart (update)
- lib/screens/pos_screen.dart (update)
- lib/screens/dashboard_screen.dart (update)
- lib/state/merchant_state.dart (update)
- test/pos_expense_modal_test.dart (create)
- test/owner_approval_screen_test.dart (create)
- test/pos_shift_expense_test.dart (create)

TASK SPECIFICATIONS:

1. Petty Cash / Quick Expense Kasir:
   - Create `lib/screens/pos/pos_expense_modal.dart`:
     - Built with Apple HIG design tokens (`IOSSpacing`, `IOSRadius`, `IOSColors`, `IOSText`).
     - Form contains:
       - Category dropdown (Bahan Baku / Supplies, Operasional Toko, Makan/Minum Karyawan, Transport/Bensin, Perbaikan & Servis, Refund/Pengembalian, Lain-lain).
       - Amount input with IDR currency formatting (Rp) and validation (amount > 0).
       - Notes field for description.
       - Photo receipt capture button: uses `ImagePicker().pickImage(source: ImageSource.camera)` with gallery fallback option, displays thumbnail preview, and allows removing photo.
       - Submit button: calls `PosService.recordExpense(...)` sending JSON payload with `receipt_image_base64`.
       - Shows loading indicator while submitting, handles errors cleanly, and triggers success feedback.
   - Update `lib/core/services/pos_service.dart`:
     - Add `recordExpense({required int shiftId, required double amount, required String category, required String notes, String? receiptBase64})`.
     - Calls `POST /api/v1/pos/expenses`.
   - Update `lib/screens/pos_screen.dart`:
     - In `_showMainMenu(MerchantState state)` under `"OPERASIONAL KASIR"`, add a menu item `"Catat Pengeluaran (Petty Cash)"` that opens `PosExpenseModal.show(context, state)`.
   - Update `lib/screens/pos/pos_shift_modal.dart`:
     - Add a quick access button/link to open `PosExpenseModal`.
     - Ensure `_expectedCash` calculation subtracts petty cash expenses (`opening_cash + cash_sales + cash_in - petty_cash_expenses`) so closing expected cash is immediately accurate.
     - When an expense is recorded, trigger a refresh of shift data (`_initShiftData()`).

2. Owner Approval Center:
   - Create `lib/core/services/approvals_service.dart`:
     - `getPendingApprovals()` calls `GET /api/v1/owner/approvals/pending`.
     - `takeAction({required String id, required String action, String? reason})` calls `POST /api/v1/owner/approvals/$id/action`.
   - Create `lib/screens/approvals/owner_approval_screen.dart`:
     - Cupertino/Apple HIG layout with CupertinoNavigationBar / adaptive header.
     - Category filter pills/segmented control (Semua, Void, Presensi, Dokumen).
     - Pull-to-refresh to fetch pending list.
     - Interactive swipeable cards (`Dismissible` or gesture detector):
       - Swipe right to Approve: green backdrop, checkmark icon, `HapticFeedback.mediumImpact()`.
       - Swipe left to Reject: red backdrop, close icon, `HapticFeedback.heavyImpact()`, shows prompt dialog to enter rejection reason.
       - On success: removes card with smooth animation and shows success snackbar/toast.
     - Empty state illustration/text when no approvals are pending.
   - Update `lib/state/merchant_state.dart`:
     - Track `int pendingApprovalsCount = 0`.
     - Add `Future<void> refreshPendingApprovalsCount()`.
   - Update `lib/screens/dashboard_screen.dart`:
     - Add badge counter to the notification button or top header showing `pendingApprovalsCount`.
     - Add an Apple HIG Alert Banner below the active shift bar when `pendingApprovalsCount > 0` (e.g. "Ada X Permintaan Persetujuan Tertunda", button "Tinjau Sekarang" navigating to `OwnerApprovalScreen`).

3. Automated Testing & Verification:
   - In `c:\laragon\www\cooca_my_own\test\`:
     - `test/pos_expense_modal_test.dart`: Widget test checking form fields, validation, and camera button rendering.
     - `test/owner_approval_screen_test.dart`: Widget test checking cards rendering, swipe actions, empty state.
     - `test/pos_shift_expense_test.dart`: Unit/widget test checking expected cash calculation with petty cash deduction.
   - Execute verification commands in `c:\laragon\www\cooca_my_own`:
     - Run `flutter analyze` — MUST have 0 issues!
     - Run `flutter test` — ALL tests MUST pass!
