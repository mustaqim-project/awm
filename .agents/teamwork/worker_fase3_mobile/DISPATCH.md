# Dispatch for Worker Fase 3 Mobile: Customer Debts & Omnichannel Digital UI

## Working Directory
`c:\laragon\www\cooca_core\.agents\teamwork\worker_fase3_mobile`

## Target Repository
`c:\laragon\www\cooca_my_own`

## Mission
Implement Milestone 4: Mobile Fase 3 (Customer Piutang & Omnichannel Digital) on `c:\laragon\www\cooca_my_own` for production use.

## Authoritative Inputs
- `c:\laragon\www\cooca_core\.agents\teamwork\ORIGINAL_REQUEST.md` (Read first)
- `c:\laragon\www\cooca_core\.agents\teamwork\orchestrator\PROJECT.md`
- `c:\laragon\www\cooca_core\.agents\teamwork\explorer_fase23_2\report.md` (Mobile exploration report)
- `c:\laragon\www\cooca_core\.agents\teamwork\explorer_fase23_3\report.md` (Cross-system API contracts)

## Mandatory Integrity Warning
DO NOT CHEAT. All implementations must be genuine. DO NOT hardcode test results, create dummy/facade implementations, or circumvent the intended task. A teamwork_auditor will independently verify your work. Integrity violations WILL be detected and your work WILL be rejected.

## Detailed Scope of Work
1. **Dependencies (`pubspec.yaml`)**:
   - Add `url_launcher: ^6.3.1` (or latest compatible) to `dependencies` in `pubspec.yaml`.
   - Run `flutter pub get`.
2. **Service Layer (`PosService` & `ApiClient`)**:
   - In `c:\laragon\www\cooca_my_own\lib\core\services\pos_service.dart`, add:
     - `getCustomerDebts(String customerId)`
     - `repayCustomerDebt(String customerId, Map<String, dynamic> payload)`
     - `sendWhatsAppReceipt(String orderId, {String? phone})`
     - `getIncomingOrders()`
     - `acceptIncomingOrder(String orderId)`
     - `rejectIncomingOrder(String orderId, {String? reason})`
3. **Kasbon in POS Payment (`pos_payment_sheet.dart`) & Modal Piutang (`pos_customer_debt_sheet.dart`)**:
   - In `pos_payment_sheet.dart`: Enable "Kasbon / Tempo" payment method option when a registered customer is selected; validate credit limit; show button to view debt history.
   - Create `c:\laragon\www\cooca_my_own\lib\screens\pos\pos_customer_debt_sheet.dart`:
     - Show total debt (`current_credit_balance`), credit limit, and unpaid invoice history.
     - Form to pay installment (cicilan hutang): nominal, quick chips, payment method, notes, and submit button.
4. **Digital WhatsApp Receipt Fallback (`pos_receipt_screen.dart`)**:
   - Button kirim struk WhatsApp:
     - Calls backend API `POST /api/v1/pos/orders/{id}/send-whatsapp-receipt`.
     - Direct fallback with `url_launcher` using `whatsapp://send?phone=...&text=...` (or web `https://api.whatsapp.com/send?...`).
     - Clipboard fallback if device does not support launching URL.
5. **Omnichannel Storefront & QR Orders (`MerchantState`, `dashboard_screen.dart`, `pos_screen.dart`)**:
   - In `MerchantState`: Add listener / polling for incoming online/QR orders and update pending badges.
   - In `dashboard_screen.dart`: Display badge and alert card for incoming storefront orders.
   - In `pos_screen.dart`: Display top-bar badge and popup dialog with 3 actions:
     1. "Terima Pesanan" (calls `acceptIncomingOrder`)
     2. "Cetak KDS" (calls kitchen dispatch / printing)
     3. "Panggil Kurir" (triggers courier dispatch)
6. **Automated Verification**:
   - Create automated widget/unit tests in `test/pos_customer_debt_test.dart`, `test/pos_whatsapp_receipt_test.dart`, and `test/storefront_orders_test.dart`.
   - Run `flutter analyze` (must be 0 issues found).
   - Run `flutter test` (100% passed).

## Deliverable
Write implementation report and handoff to `c:\laragon\www\cooca_core\.agents\teamwork\worker_fase3_mobile\handoff.md` and notify parent when complete.
