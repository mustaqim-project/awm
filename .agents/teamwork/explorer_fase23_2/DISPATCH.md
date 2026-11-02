# Dispatch for Explorer 2: Mobile Architecture & UI/UX Survey

## Working Directory
`c:\laragon\www\cooca_core\.agents\teamwork\explorer_fase23_2`

## Mission
Survey the mobile repository `c:\laragon\www\cooca_my_own` for Fase 2 (Logistik & Gudang Toko) and Fase 3 (Customer Piutang & Omnichannel Digital).

## Scope & Target Investigation
1. **Repository & Dependencies**:
   - Inspect `c:\laragon\www\cooca_my_own\pubspec.yaml` (packages for camera/barcode scanning like `mobile_scanner`, `url_launcher`, network client/dio/http).
   - Inspect state management architecture: `MerchantState` (or similar providers in `lib/`), how API calls are made, token storage, and offline sync.
2. **Fase 2: Logistik & Gudang Toko UI**:
   - Inspect `lib/screens/inventory/` and determine where `GoodsReceiptScreen` (`lib/screens/inventory/goods_receipt_screen.dart`) and `StockTransferScreen` (`lib/screens/inventory/stock_transfer_screen.dart`) should be created/placed.
   - Inspect existing barcode scanning implementation or camera utilities across the mobile app.
   - Inspect inventory navigation and how staff transitions into Goods Receipt or Stock Transfer.
   - Design component layout for:
     - `GoodsReceiptScreen`: PO selection, camera barcode scanner, actual vs damaged qty input, batch/lot and expired date fields.
     - `StockTransferScreen`: 2 tabs (Tab Kirim Barang / Dispatch and Tab Terima Barang / Inbound Scan).
3. **Fase 3: Customer Piutang & Omnichannel Digital UI**:
   - Inspect `lib/screens/pos/pos_payment_sheet.dart`: how payment methods are displayed, customer selection state, and how "Kasbon / Tempo" payment option should be hooked up.
   - Inspect `lib/screens/pos/pos_customer_debt_sheet.dart` (needs to be created): total piutang, debt history, repayment modal/sheet.
   - Inspect `lib/screens/pos/pos_receipt_screen.dart`: send receipt to WhatsApp button, WhatsApp API integration, and fallback to `url_launcher` (`whatsapp://send?phone=...&text=...`).
   - Inspect `MerchantState`, `lib/screens/dashboard_screen.dart`, and `lib/screens/pos/pos_screen.dart`: how storefront online & QR table orders are polled/listened, popup dialogs, notification badges, and action triggers (Terima Pesanan -> Cetak KDS -> Panggil Kurir).
4. **Mobile Testing & Analysis**:
   - Inspect `test/` directory in `c:\laragon\www\cooca_my_own` to see how widget tests and unit tests are configured and run.

## Deliverable
Write your comprehensive investigation report to `c:\laragon\www\cooca_core\.agents\teamwork\explorer_fase23_2\report.md` and deliver `handoff.md`.
