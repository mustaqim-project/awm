# BRIEFING — 2026-11-02T06:14:00Z

## Mission
Implement Mobile Petty Cash / Quick Expense Modal and Owner Approval Center for cooca_my_own Flutter app, with Apple HIG design tokens, state updates, dashboard integration, and automated tests.

## 🔒 My Identity
- Archetype: teamwork_worker
- Roles: implementer, qa, specialist
- Working directory: c:\laragon\www\cooca_core\.agents\teamwork\worker_mobile_m2\
- Original parent: 1516ea67-27cb-4c71-a78a-e0325a01044c
- Milestone: Milestone 2 (Mobile App Implementation)

## 🔒 Key Constraints
- Target repository: c:\laragon\www\cooca_my_own
- Exclusive file ownership:
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
- Design language: Apple HIG (`IOSSpacing`, `IOSRadius`, `IOSColors`, `IOSText`)
- Verification: `flutter analyze` 0 issues, `flutter test` all pass.
- Genuine implementation only, no dummy/cheating.

## Current Parent
- Conversation ID: 1516ea67-27cb-4c71-a78a-e0325a01044c
- Updated: 2026-11-02T06:14:00Z

## Task Summary
- **What to build**:
  1. Petty Cash / Quick Expense Modal (`pos_expense_modal.dart`), PosService API method, POS Screen menu integration, Shift Modal quick-access & expected cash deduction.
  2. Owner Approval Center (`approvals_service.dart`, `owner_approval_screen.dart`), MerchantState integration, Dashboard alert banner & badge.
  3. Automated unit and widget tests for all new/updated components.
- **Success criteria**:
  - `flutter analyze` 0 issues.
  - `flutter test` passes (22/22 passed).
  - Smooth Apple HIG feel, swipe gestures, receipt picker support.
- **Interface contracts**:
  - Backend endpoints: `POST /api/v1/pos/expenses`, `GET /api/v1/owner/approvals/pending`, `POST /api/v1/owner/approvals/{id}/action`.

## Key Decisions Made
- `PosExpenseModal` supports both camera capture and photo gallery fallback via `ImagePicker`.
- Base64 data URI format (`data:image/jpeg;base64,...`) used for receipt images for 100% compatibility with backend `POST /api/v1/pos/expenses`.
- `PosShiftModal.calculateExpectedCash` provides authoritative formula deducting petty cash expenses from closing drawer cash.
- `OwnerApprovalScreen` uses Apple HIG `Dismissible` with haptic feedback (mediumImpact on approve, heavyImpact on reject), with a custom reason dialog on swipe-to-reject and button fallbacks for accessibility.
- Dashboard includes live pending approvals badge counter and Apple HIG operational banner navigating to `OwnerApprovalScreen`.

## Artifact Index
- `lib/screens/pos/pos_expense_modal.dart` — Petty Cash / Quick Expense Modal
- `lib/screens/approvals/owner_approval_screen.dart` — Owner Approval Center Screen
- `lib/core/services/approvals_service.dart` — Approvals API Client Service
- `test/pos_expense_modal_test.dart` — Widget test suite for Petty Cash Modal
- `test/owner_approval_screen_test.dart` — Widget test suite for Owner Approval Center
- `test/pos_shift_expense_test.dart` — Unit/widget tests for shift expected cash deduction

## Change Tracker
- **Files modified**:
  - `lib/core/services/pos_service.dart` (added recordExpense)
  - `lib/core/services/approvals_service.dart` (created approvals service)
  - `lib/screens/pos/pos_expense_modal.dart` (created petty cash modal)
  - `lib/screens/approvals/owner_approval_screen.dart` (created approvals screen)
  - `lib/state/merchant_state.dart` (added pendingApprovalsCount, refreshPendingApprovalsCount)
  - `lib/screens/dashboard_screen.dart` (added alert banner & badge counter)
  - `lib/screens/pos_screen.dart` (added menu entry in Operasional Kasir)
  - `lib/screens/pos/pos_shift_modal.dart` (added expected cash deduction & quick-access button)
  - `test/pos_expense_modal_test.dart` (created 4 widget tests)
  - `test/owner_approval_screen_test.dart` (created 4 widget tests)
  - `test/pos_shift_expense_test.dart` (created 4 unit/widget tests)
- **Build status**: PASS
- **Pending issues**: None

## Quality Status
- **Build/test result**: PASS (22/22 tests passing)
- **Lint status**: 0 issues found by `flutter analyze`
- **Tests added/modified**: 12 new test cases added across 3 test suites

## Loaded Skills
- flutter-ios-design: C:\Users\Ulfah Ramadhani\.gemini\config\skills\flutter-ios-design\SKILL.md
