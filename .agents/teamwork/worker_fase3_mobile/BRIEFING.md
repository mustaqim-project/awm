# BRIEFING  2026-11-02T10:33:00Z

## Mission
Implement Milestone 4: Mobile Fase 3 (Customer Piutang & Omnichannel Digital UI) in `c:\laragon\www\cooca_my_own` for production readiness with 100% tests and zero analyzer issues.

## 🔒 My Identity
- Archetype: teamwork_worker
- Roles: implementer, qa
- Working directory: c:\laragon\www\cooca_core\.agents\teamwork\worker_fase3_mobile
- Target repository: c:\laragon\www\cooca_my_own
- Original parent: 87be163a-9630-4504-a803-20f6abd1bf8c
- Milestone: Milestone 4 (Mobile Fase 3)

## 🔒 Key Constraints
- DO NOT CHEAT. All implementations must be genuine.
- DO NOT hardcode test results, expected outputs, or verification strings in source code.
- No dummy/facade implementations. Maintain real state and produce real behavior.
- Strictly adhere to Apple HIG design tokens (`ios_tokens.dart`, `ios_adaptive.dart`).
- Minimal change principle: only modify what is necessary.
- Code layout compliance: no source/tests in `.agents/teamwork/`.
- All automated tests must pass (`flutter test`), and `flutter analyze` must produce 0 issues.

## Current Parent
- Conversation ID: 87be163a-9630-4504-a803-20f6abd1bf8c
- Updated: 2026-11-02T10:33:00Z

## Task Summary
- **What to build**:
  1. Add `url_launcher: ^6.3.1` (or latest compatible) to `c:\laragon\www\cooca_my_own\pubspec.yaml`, run `flutter pub get`.
  2. Extend `PosService` with customer debts (`getCustomerDebts`), debt repayment (`repayCustomerDebt`), WhatsApp receipt (`sendWhatsAppReceipt`), and incoming orders (`getIncomingOrders`, `acceptIncomingOrder`, `rejectIncomingOrder`).
  3. In `pos_payment_sheet.dart`: Enable "Kasbon / Tempo" payment method option when a registered customer is selected; button to open `PosCustomerDebtSheet`.
  4. Create `lib/screens/pos/pos_customer_debt_sheet.dart` to display total debt, credit limit, unpaid invoice history, and repayment form.
  5. In `lib/screens/pos/pos_receipt_screen.dart`: Implement WhatsApp send button calling backend API `POST /api/v1/pos/orders/{id}/send-whatsapp-receipt` with fallback to `url_launcher` intent (`whatsapp://send?phone=...&text=...`).
  6. In `MerchantState`, `dashboard_screen.dart`, and `pos_screen.dart`: Implement listener and notification badges/dialogs for incoming storefront & QR orders with action triggers (Terima Pesanan, Cetak KDS, Panggil Kurir).
  7. Automated tests in `test/` (`test/pos_customer_debt_test.dart`, `test/pos_whatsapp_receipt_test.dart`, `test/storefront_orders_test.dart`), and ensure `flutter analyze` (0 issues) & `flutter test` (100% pass).
- **Success criteria**: Genuine implementation, zero analyzer warnings/errors, 100% test pass rate.
- **Interface contracts**: `c:\laragon\www\cooca_core\.agents\teamwork\explorer_fase23_3\report.md`
- **Code layout**: Flutter project layout at `c:\laragon\www\cooca_my_own`

## Key Decisions Made
- [TBD]

## Artifact Index
- `c:\laragon\www\cooca_core\.agents\teamwork\worker_fase3_mobile\DISPATCH.md`  Detailed task dispatch
- `c:\laragon\www\cooca_core\.agents\teamwork\worker_fase3_mobile\progress.md`  Liveness and execution tracking
- `c:\laragon\www\cooca_core\.agents\teamwork\worker_fase3_mobile\report.md`  Final implementation report
- `c:\laragon\www\cooca_core\.agents\teamwork\worker_fase3_mobile\handoff.md`  Self-contained handoff report

## Change Tracker
- **Files modified**: None yet
- **Build status**: Baseline verified (flutter analyze: 0 issues)
- **Pending issues**: None

## Quality Status
- **Build/test result**: Not yet executed
- **Lint status**: Clean (baseline)
- **Tests added/modified**: Pending implementation

## Loaded Skills
- None requested explicitly
