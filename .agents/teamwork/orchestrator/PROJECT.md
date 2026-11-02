# Project: COOCA Core to Mobile Integration (Fase 2 & Fase 3)

## Architecture
- **Backend API**: `c:\laragon\www\cooca_core` (Laravel 11, Laravel Sanctum, Eloquent ORM, PHPUnit 11)
- **Mobile Client**: `c:\laragon\www\cooca_my_own` (Flutter 3.47 / Dart 3.13, Provider/Listenable `MerchantState`, Apple HIG tokens `ios_tokens.dart`, `ios_adaptive.dart`, `flutter test`, `flutter analyze`)
- **Fase 2 (Logistik & Gudang Toko)**:
  - Goods Receipt / Penerimaan Barang Supplier: PO pending list, PO detail, camera barcode scan (`mobile_scanner`), actual vs damaged received qty, batch/lot & exp date tracking in `product_batches`.
  - Stock Transfer / Mutasi Antar Cabang: Transfer list, dispatch (pengiriman barang, deduction to in-transit status), receive (penerimaan inbound scan, destination stock increment).
- **Fase 3 (Customer Piutang & Omnichannel Digital)**:
  - Customer Piutang & Kasbon: Debt history, unpaid balance tracking (`current_credit_balance`), repayment recording via `LoyaltyService`, POS "Kasbon / Tempo" payment method.
  - Digital WhatsApp Receipt: POS order WhatsApp receipt dispatch API with mobile direct WhatsApp intent fallback via `url_launcher` (`whatsapp://send?phone=...`).
  - Storefront & Standee QR Orders: Inbound order polling/events, `MerchantState` listener, dashboard & POS popup badges, order action workflows (Terima Pesanan -> Cetak KDS -> Panggil Kurir).
- **Quality Gates**:
  - Backend: 100% PHPUnit tests passing for all new/modified endpoints.
  - Mobile: `flutter analyze` with 0 issues, 100% widget & unit tests passing.
  - Hard forensic audit gate: Clean audit verdict.

## Feature Inventory
| # | Feature | Description | Milestone | Source |
|---|---------|-------------|-----------|--------|
| 1 | Pending Purchase Orders API | `GET /api/v1/inventory/purchase-orders/pending` & `GET /api/v1/inventory/purchase-orders/{id}` | M1 (Backend Fase 2) | ORIGINAL_REQUEST R1 |
| 2 | Goods Receipt Creation API | `POST /api/v1/inventory/goods-receipts` (update PO received qty, stock mutation, batch & exp date) | M1 (Backend Fase 2) | ORIGINAL_REQUEST R1 |
| 3 | Stock Transfer API (2-Step) | `GET /api/v1/inventory/transfers`, `POST /api/v1/inventory/transfers/dispatch`, `POST /api/v1/inventory/transfers/{id}/receive` | M1 (Backend Fase 2) | ORIGINAL_REQUEST R1 |
| 4 | Mobile Goods Receipt Screen | `GoodsReceiptScreen` with camera barcode scanner, actual vs damaged qty, batch & exp date | M2 (Mobile Fase 2) | ORIGINAL_REQUEST R1 |
| 5 | Mobile Stock Transfer Screen | `StockTransferScreen` with 2 tabs: Dispatch & Inbound Scan | M2 (Mobile Fase 2) | ORIGINAL_REQUEST R1 |
| 6 | Customer Debts & Kasbon API | `GET /api/v1/customers/{id}/debts` & `POST /api/v1/customers/{id}/repay-debt` | M3 (Backend Fase 3) | ORIGINAL_REQUEST R2 |
| 7 | WhatsApp Receipt API | `POST /api/v1/pos/orders/{id}/send-whatsapp-receipt` | M3 (Backend Fase 3) | ORIGINAL_REQUEST R2 |
| 8 | Storefront & QR Orders API | Storefront & table QR orders endpoint and notification support | M3 (Backend Fase 3) | ORIGINAL_REQUEST R2 |
| 9 | Mobile Kasbon Payment & Debt Sheet | POS payment "Kasbon / Tempo" option & `PosCustomerDebtSheet` modal | M4 (Mobile Fase 3) | ORIGINAL_REQUEST R2 |
| 10 | Mobile WhatsApp Receipt Fallback | `pos_receipt_screen.dart` WhatsApp send button with direct intent fallback | M4 (Mobile Fase 3) | ORIGINAL_REQUEST R2 |
| 11 | Mobile Omnichannel Orders Listener & UI | `MerchantState` listener, dashboard & POS badges/dialogs, action buttons | M4 (Mobile Fase 3) | ORIGINAL_REQUEST R2 |
| 12 | Comprehensive Test Suite & QA Gate | Backend PHPUnit tests, Mobile flutter tests & flutter analyze (0 issues) | M5 (Test & Verification) | ORIGINAL_REQUEST R3 |

## Milestones
| # | Name | Scope | Dependencies | Status |
|---|------|-------|-------------|--------|
| 0 | Phase 0: Survey & Technical Discovery | Survey codebase schemas, existing routes, Flutter widgets, and test setups | none | DONE |
| 1 | Milestone 1: Backend Fase 2 (Logistics & Stock) | PO pending, PO detail, Goods Receipt, Stock Transfer dispatch & receive | M0 | IN_PROGRESS |
| 2 | Milestone 2: Mobile Fase 2 (Logistics UI) | `GoodsReceiptScreen` & `StockTransferScreen` with camera & scanner | M1 | PLANNED |
| 3 | Milestone 3: Backend Fase 3 (Debts & Omnichannel) | Customer debt/repay, WhatsApp receipt, Storefront/QR orders API | M0 | PLANNED |
| 4 | Milestone 4: Mobile Fase 3 (Debts & Omnichannel UI) | Kasbon POS payment, `PosCustomerDebtSheet`, WA receipt fallback, Omnichannel order alerts | M3 | PLANNED |
| 5 | Milestone 5: Full E2E & Automated Test Gate | PHPUnit tests, Flutter widget/unit tests, `flutter analyze`, forensic audit | M1, M2, M3, M4 | PLANNED |

## Interface Contracts
### General Standards
- Authentication: `Bearer <sanctum_token>` via `auth:sanctum` middleware.
- Tenant Context: `X-Business-Id: <business_uuid>` header, mapped via `SetActiveBusinessContext` middleware.
- Response Envelope: `{ "success": true, "data": ..., "message": "..." }` or standard Laravel resources.
- Error Format: HTTP 422 `{ "success": false, "message": "Validation error", "errors": { ... } }`.

### 10 Authoritative Endpoints
1. `GET /api/v1/inventory/purchase-orders/pending?location_id={uuid}`: Returns confirmed POs awaiting physical receipt.
2. `GET /api/v1/inventory/purchase-orders/{id}`: Returns PO details, ordered qty, received qty, remaining qty per item.
3. `POST /api/v1/inventory/goods-receipts`: Creates goods receipt, updates PO items, records stock movement (`TYPE_GOODS_RECEIPT`), creates `product_batches` if batch/expiry provided.
4. `GET /api/v1/inventory/transfers?location_id={uuid}&type={all|inbound|outbound}`: Lists stock transfers.
5. `POST /api/v1/inventory/transfers/dispatch`: Deducts source stock (`TYPE_TRANSFER_OUT`), sets `STATUS_IN_TRANSIT`.
6. `POST /api/v1/inventory/transfers/{id}/receive`: Adds destination stock (`TYPE_TRANSFER_IN`), sets `STATUS_RECEIVED`.
7. `GET /api/v1/customers/{id}/debts`: Returns customer credit summary, `credit_limit`, `current_credit_balance`, unpaid invoices.
8. `POST /api/v1/customers/{id}/repay-debt`: Records repayment via `LoyaltyService::recordCustomerCreditPayment`, creates `CustomerCreditTransaction::TYPE_PAYMENT`, records cash entry.
9. `POST /api/v1/pos/orders/{id}/send-whatsapp-receipt`: Sends WhatsApp receipt via Meta API / Gateway and returns fallback `whatsapp_url`.
10. `GET /api/v1/commerce/orders/incoming` (or `/api/v1/pos/incoming-orders`): Returns pending web storefront & QR table orders.

## Code Layout
- Backend (`cooca_core`):
  - Routes: `c:\laragon\www\cooca_core\routes\api.php`
  - Controllers:
    - `c:\laragon\www\cooca_core\app\Http\Controllers\Api\V1\Inventory\InventoryPurchaseOrderController.php`
    - `c:\laragon\www\cooca_core\app\Http\Controllers\Api\V1\Inventory\InventoryGoodsReceiptController.php`
    - `c:\laragon\www\cooca_core\app\Http\Controllers\Api\V1\Inventory\StockTransferController.php`
    - `c:\laragon\www\cooca_core\app\Http\Controllers\Api\V1\Crm\CustomerDebtApiController.php`
    - `c:\laragon\www\cooca_core\app\Http\Controllers\Api\V1\Pos\PosReceiptApiController.php`
  - Domain Services:
    - `c:\laragon\www\cooca_core\app\Domain\Purchasing\GoodsReceiptService.php`
    - `c:\laragon\www\cooca_core\app\Domain\Inventory\StockService.php`
    - `c:\laragon\www\cooca_core\app\Domain\Crm\LoyaltyService.php`
    - `c:\laragon\www\cooca_core\app\Domain\WhatsApp\WhatsAppGatewayService.php`
  - Tests:
    - `c:\laragon\www\cooca_core\tests\Feature\Inventory\GoodsReceiptAndPoReceiveTest.php`
    - `c:\laragon\www\cooca_core\tests\Feature\Inventory\StockTransferDispatchAndReceiveTest.php`
    - `c:\laragon\www\cooca_core\tests\Feature\Crm\CustomerDebtAndRepayTest.php`
    - `c:\laragon\www\cooca_core\tests\Feature\Pos\PosWhatsAppReceiptApiTest.php`
- Mobile (`cooca_my_own`):
  - Dependecies: `c:\laragon\www\cooca_my_own\pubspec.yaml` (`url_launcher: ^6.3.1`)
  - Inventory Screens:
    - `c:\laragon\www\cooca_my_own\lib\screens\inventory\goods_receipt_screen.dart`
    - `c:\laragon\www\cooca_my_own\lib\screens\inventory\stock_transfer_screen.dart`
  - POS Screens & Modals:
    - `c:\laragon\www\cooca_my_own\lib\screens\pos\pos_payment_sheet.dart`
    - `c:\laragon\www\cooca_my_own\lib\screens\pos\pos_customer_debt_sheet.dart`
    - `c:\laragon\www\cooca_my_own\lib\screens\pos\pos_receipt_screen.dart`
    - `c:\laragon\www\cooca_my_own\lib\screens\pos\pos_screen.dart`
    - `c:\laragon\www\cooca_my_own\lib\screens\dashboard_screen.dart`
  - Services & State:
    - `c:\laragon\www\cooca_my_own\lib\core\services\inventory_service.dart`
    - `c:\laragon\www\cooca_my_own\lib\core\services\pos_service.dart`
    - `c:\laragon\www\cooca_my_own\lib\state\merchant_state.dart`
  - Tests:
    - `c:\laragon\www\cooca_my_own\test\goods_receipt_screen_test.dart`
    - `c:\laragon\www\cooca_my_own\test\stock_transfer_screen_test.dart`
    - `c:\laragon\www\cooca_my_own\test\pos_customer_debt_test.dart`
    - `c:\laragon\www\cooca_my_own\test\pos_whatsapp_receipt_test.dart`
    - `c:\laragon\www\cooca_my_own\test\storefront_orders_test.dart`
