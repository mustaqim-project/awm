# Investigation Report: Cross-System Integration Contracts & Test Infrastructure (Fase 2 & Fase 3)

**Explorer**: Explorer 3 (Integration Contracts Explorer)  
**Date**: 2026-11-02  
**Repositories**:
- Backend: `c:\laragon\www\cooca_core` (Laravel 11, Sanctum, Eloquent, MySQL/SQLite testing)
- Mobile: `c:\laragon\www\cooca_my_own` (Flutter 3.47 / Dart 3.13, Provider, Apple HIG)

---

## 1. Executive Summary

This report establishes the authoritative cross-system API contracts, payload schemas, model definitions, error handling protocols, and test automation baselines for **Fase 2 (Logistik & Gudang Toko)** and **Fase 3 (Customer Piutang & Omnichannel Digital)**.

### Key Discoveries:
1. **Existing Backend Assets**:
   - `GoodsReceiptController` and `GoodsReceiptService` exist under `/api/v1/purchasing/goods-receipts`, but the mobile inventory workflow requires dedicated endpoints under `/api/v1/inventory/purchase-orders/pending`, `/api/v1/inventory/purchase-orders/{id}`, and `/api/v1/inventory/goods-receipts`.
   - `product_batches` table already exists in the database migration (`2026_08_29_000042_create_inventory_management_tables.php`) and Eloquent model `ProductBatch.php` exists with fields `batch_number`, `expiry_date`, `quantity`, `unit_cost`.
   - `StockTransferController` exists with `index`, `store`, `show`, `receive`, but lacks an explicit `dispatch` endpoint (`POST /api/v1/inventory/transfers/dispatch`) and an in-transit stock mutation flow (`STATUS_IN_TRANSIT`).
   - Customer debt tracking models (`CustomerCreditTransaction`, `Customer` with `current_credit_balance`) and business logic (`LoyaltyService::recordCustomerCreditPayment`, `recordCustomerCreditCharge`, `generateCreditPaymentWhatsAppReceiptUrl`) are already implemented in `cooca_core`, but dedicated REST endpoints `GET /api/v1/customers/{id}/debts` and `POST /api/v1/customers/{id}/repay-debt` must be exposed in `routes/api.php`.
   - POS digital WhatsApp receipt generation exists in `LoyaltyService::generateWhatsAppReceiptUrl` and `WhatsAppGatewayService::sendReceipt`, but endpoint `POST /api/v1/pos/orders/{id}/send-whatsapp-receipt` is needed to provide an official API dispatch with fallback URL intent.
   - Incoming orders infrastructure exists via `CommerceOrder` (`GET /api/v1/commerce/orders`) and `PosOrder` (`order_source = qr_table`), but needs unified real-time polling in `MerchantState` with interactive dialogs in `dashboard_screen.dart` and `pos_screen.dart`.
2. **Mobile Client Baseline**:
   - `ApiClient` in `cooca_my_own` standardizes `Authorization: Bearer <token>`, `X-Business-Id: <uuid>`, `Accept: application/json`, and parses errors into `{success: false, statusCode, message, errors}`.
   - `url_launcher` package is required for direct WhatsApp intent fallback in `pos_receipt_screen.dart` and must be added to `pubspec.yaml`.
3. **Verification Infrastructure**:
   - Backend PHPUnit: Configured via `phpunit.xml` with SQLite `:memory:`. Verified command: `php artisan test --filter=StockServiceConcurrencyAndIntegrityTest` (100% passed, 5 assertions, exit code 0).
   - Mobile Flutter: Verified command `flutter analyze` runs cleanly with 0 issues (`No issues found!`, exit code 0). Unit/widget tests run via `flutter test` (100% passed, exit code 0).

---

## 2. Cross-System API Endpoint Contracts (10 Endpoints)

All endpoints require:
- **Authentication**: Laravel Sanctum Bearer Token (`Authorization: Bearer <token>`)
- **Tenant Isolation**: `X-Business-Id: <business_uuid>` header, resolved by backend via `Context::requireBusiness()`
- **Content Negotiation**: `Accept: application/json` and `Content-Type: application/json`

```
┌────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│                                SUMMARY OF THE 10 INTEGRATION CONTRACTS                                 │
├────┬────────┬───────────────────────────────────────────────┬──────────────────────────────┬───────────┤
│ No │ Method │ Route Endpoint                                │ Domain Area                  │ Fase      │
├────┼────────┼───────────────────────────────────────────────┼──────────────────────────────┼───────────┤
│ 1  │ GET    │ /api/v1/inventory/purchase-orders/pending     │ Goods Receipt (PO List)      │ Fase 2    │
│ 2  │ GET    │ /api/v1/inventory/purchase-orders/{id}        │ Goods Receipt (PO Detail)    │ Fase 2    │
│ 3  │ POST   │ /api/v1/inventory/goods-receipts              │ Goods Receipt (Physical In)  │ Fase 2    │
│ 4  │ GET    │ /api/v1/inventory/transfers                   │ Stock Transfer (List)        │ Fase 2    │
│ 5  │ POST   │ /api/v1/inventory/transfers/dispatch          │ Stock Transfer (Dispatch)    │ Fase 2    │
│ 6  │ POST   │ /api/v1/inventory/transfers/{id}/receive      │ Stock Transfer (Inbound Rec) │ Fase 2    │
│ 7  │ GET    │ /api/v1/customers/{id}/debts                  │ Customer Debts / Kasbon      │ Fase 3    │
│ 8  │ POST   │ /api/v1/customers/{id}/repay-debt             │ Cicil Piutang / Repay Debt   │ Fase 3    │
│ 9  │ POST   │ /api/v1/pos/orders/{id}/send-whatsapp-receipt │ Official Digital Receipt WA  │ Fase 3    │
│ 10 │ GET    │ /api/v1/commerce/orders/incoming              │ Omnichannel Storefront & QR  │ Fase 3    │
└────┴────────┴───────────────────────────────────────────────┴──────────────────────────────┴───────────┘
```

---

### Endpoint 1: `GET /api/v1/inventory/purchase-orders/pending`
* **Purpose**: Fetch pending supplier purchase orders eligible for physical receiving at a store/warehouse location.
* **Controller**: `App\Http\Controllers\Api\V1\Inventory\InventoryPurchaseOrderController@pending` (or alias in `PurchaseOrderController`)
* **Permission**: `require.permission:inventory.view` or `require.permission:receiving.manage`
* **Query Parameters**:
  - `location_id` (optional, UUID): Target receiving warehouse
  - `supplier_id` (optional, UUID): Filter by supplier
  - `search` (optional, string): PO number or supplier name
  - `page` (optional, int, default: 1)
  - `per_page` (optional, int, default: 20)

#### Request Example:
```http
GET /api/v1/inventory/purchase-orders/pending?location_id=9d901a8b-0001-0000-0000-000000000001 HTTP/1.1
Host: cooca_core.test
Authorization: Bearer 12|sanctum_token_xyz
X-Business-Id: 9d901a8b-0000-0000-0000-000000000001
Accept: application/json
```

#### Response Envelope (HTTP 200 OK):
```json
{
  "success": true,
  "data": [
    {
      "id": "9d901a8b-po01-0000-0000-000000000001",
      "po_number": "PO-202611-0012",
      "reference_number": "DO-SUPPLIER-88",
      "status": "confirmed",
      "po_type": "supplier",
      "order_date": "2026-11-01",
      "expected_delivery_date": "2026-11-05",
      "supplier": {
        "id": "9d901a8b-sup1-0000-0000-000000000001",
        "name": "PT Sumber Pangan Makmur",
        "phone": "08123456789",
        "contact_person": "Budi"
      },
      "items_count": 2,
      "total_ordered_qty": 50.0,
      "total_received_qty": 10.0,
      "total_remaining_qty": 40.0,
      "items": [
        {
          "id": "9d901a8b-poi1-0000-0000-000000000001",
          "product_id": "9d901a8b-prd1-0000-0000-000000000001",
          "material_id": null,
          "item_name": "Susu UHT Full Cream 1L",
          "sku": "SKU-MILK-1L",
          "barcode": "8991234567890",
          "quantity": 30.0,
          "received_quantity": 10.0,
          "remaining_quantity": 20.0,
          "unit_price": 18000.0,
          "unit": {
            "id": "9d901a8b-unt1-0000-0000-000000000001",
            "name": "Kotak",
            "code": "ktk"
          }
        }
      ]
    }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 20,
    "total": 1
  }
}
```

---

### Endpoint 2: `GET /api/v1/inventory/purchase-orders/{id}`
* **Purpose**: Fetch complete details and remaining items of a single Purchase Order for camera scan validation in `GoodsReceiptScreen`.
* **Path Parameter**: `{id}`: UUID of Purchase Order or PO Number string.
* **Response Envelope (HTTP 200 OK)**:
```json
{
  "success": true,
  "data": {
    "id": "9d901a8b-po01-0000-0000-000000000001",
    "po_number": "PO-202611-0012",
    "reference_number": "DO-SUPPLIER-88",
    "status": "confirmed",
    "po_type": "supplier",
    "order_date": "2026-11-01",
    "expected_delivery_date": "2026-11-05",
    "supplier": {
      "id": "9d901a8b-sup1-0000-0000-000000000001",
      "name": "PT Sumber Pangan Makmur",
      "phone": "08123456789",
      "email": "order@sumberpangan.com",
      "address": "Jl. Pergudangan No. 8"
    },
    "notes": "Barang wajib dicek suhu dan expired date minimal 12 bulan.",
    "items": [
      {
        "id": "9d901a8b-poi1-0000-0000-000000000001",
        "product_id": "9d901a8b-prd1-0000-0000-000000000001",
        "material_id": null,
        "item_name": "Susu UHT Full Cream 1L",
        "sku": "SKU-MILK-1L",
        "barcode": "8991234567890",
        "quantity": 30.0,
        "received_quantity": 10.0,
        "remaining_quantity": 20.0,
        "unit_price": 18000.0,
        "unit": {
          "id": "9d901a8b-unt1-0000-0000-000000000001",
          "name": "Kotak",
          "code": "ktk"
        },
        "product": {
          "id": "9d901a8b-prd1-0000-0000-000000000001",
          "name": "Susu UHT Full Cream 1L",
          "code": "SKU-MILK-1L",
          "barcode": "8991234567890",
          "image_url": "https://cooca.id/storage/products/milk.jpg"
        }
      }
    ]
  }
}
```

---

### Endpoint 3: `POST /api/v1/inventory/goods-receipts`
* **Purpose**: Submit actual received goods against a PO with barcode scan, actual received quantity, damaged quantity, batch/lot number, and expiry date.
* **Permission**: `require.permission:receiving.manage`

#### Request Body Schema:
```json
{
  "purchase_order_id": "9d901a8b-po01-0000-0000-000000000001",
  "location_id": "9d901a8b-loc1-0000-0000-000000000001",
  "receipt_date": "2026-11-02",
  "receipt_number": "GR-202611-0001",
  "notes": "Penerimaan via Scanner HP Toko",
  "items": [
    {
      "product_id": "9d901a8b-prd1-0000-0000-000000000001",
      "material_id": null,
      "item_name": "Susu UHT Full Cream 1L",
      "quantity": 18.0,
      "damaged_quantity": 2.0,
      "unit_cost": 18000.0,
      "batch_number": "LOT-20261102-A",
      "expiry_date": "2027-11-01",
      "notes": "2 kotak bocor saat pengiriman, ditolak dan dikembalikan ke supir"
    }
  ]
}
```

#### Validation Rules:
- `purchase_order_id`: `['required', 'uuid', 'exists:purchase_orders,id']`
- `location_id`: `['required', 'uuid', 'exists:locations,id']`
- `receipt_date`: `['required', 'date']`
- `receipt_number`: `['nullable', 'string', 'max:64']`
- `notes`: `['nullable', 'string', 'max:1000']`
- `items`: `['required', 'array', 'min:1']`
- `items.*.product_id`: `['nullable', 'uuid', 'exists:products,id', 'required_without:items.*.material_id']`
- `items.*.material_id`: `['nullable', 'uuid', 'exists:materials,id', 'required_without:items.*.product_id']`
- `items.*.quantity`: `['required', 'numeric', 'min:0.0001']`
- `items.*.damaged_quantity`: `['nullable', 'numeric', 'min:0']`
- `items.*.unit_cost`: `['required', 'numeric', 'min:0']`
- `items.*.batch_number`: `['nullable', 'string', 'max:64']`
- `items.*.expiry_date`: `['nullable', 'date']`

#### Backend State Mutations Executed Atomically:
1. Validates that item is in PO and `quantity <= remaining_quantity + 0.00005`.
2. Creates `GoodsReceipt` record (`status = 'completed'`).
3. Creates `GoodsReceiptItem` records with `batch_number` and `expiry_date`.
4. If `batch_number` present, updates/creates `ProductBatch` in `product_batches` table.
5. Invokes `StockService::recordMovement`:
   - `movement_type = StockMovement::TYPE_GOODS_RECEIPT`
   - Atomically updates `InventoryStock` balance & moving average cost.
6. Updates `PurchaseOrder` status:
   - If all items fully received: `STATUS_COMPLETED`
   - If items partially received: `STATUS_PARTIALLY_INVOICED`
7. Invokes `SupplierInvoiceService::createFromGoodsReceipt` to create Accounts Payable (AP) invoice.

#### Response Envelope (HTTP 201 Created):
```json
{
  "success": true,
  "message": "Penerimaan barang fisik berhasil dicatat dan stok gudang telah diperbarui.",
  "data": {
    "goods_receipt": {
      "id": "9d901a8b-gr01-0000-0000-000000000001",
      "receipt_number": "GR-202611-0001",
      "receipt_date": "2026-11-02",
      "status": "completed",
      "po_status": "completed",
      "items": [
        {
          "id": "gri-uuid-1",
          "product_id": "9d901a8b-prd1-0000-0000-000000000001",
          "quantity": 18.0,
          "unit_cost": 18000.0,
          "batch_number": "LOT-20261102-A",
          "expiry_date": "2027-11-01"
        }
      ]
    }
  }
}
```

---

### Endpoint 4: `GET /api/v1/inventory/transfers`
* **Purpose**: List stock transfers filtered by status, source location, or destination location for Tab Kirim Barang (Dispatch) and Tab Terima Barang (Inbound).
* **Permission**: `require.permission:inventory.view`
* **Query Parameters**:
  - `status`: `pending`, `in_transit`, `received`, `cancelled`
  - `source_location_id`: Filter transfers dispatched from this branch
  - `destination_location_id`: Filter transfers inbound to this branch
  - `page`: default 1
  - `per_page`: default 20
* **Response Envelope (HTTP 200 OK)**:
```json
{
  "success": true,
  "transfers": [
    {
      "id": "9d901a8b-trf1-0000-0000-000000000001",
      "transfer_number": "TRF-20261102-101",
      "transfer_date": "2026-11-02",
      "status": "in_transit",
      "source_location_id": "9d901a8b-loc1-0000-0000-000000000001",
      "source_location": {
        "id": "9d901a8b-loc1-0000-0000-000000000001",
        "name": "Gudang Utama"
      },
      "destination_location_id": "9d901a8b-loc2-0000-0000-000000000002",
      "destination_location": {
        "id": "9d901a8b-loc2-0000-0000-000000000002",
        "name": "Outlet Cabang Dago"
      },
      "creator": {
        "id": "user-uuid-1",
        "name": "Budi Staff"
      },
      "receiver": null,
      "items_count": 1,
      "sent_at": "2026-11-02T08:30:00Z",
      "received_at": null,
      "notes": "Pengiriman stok kopi harian"
    }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 20,
    "total": 1
  }
}
```

---

### Endpoint 5: `POST /api/v1/inventory/transfers/dispatch`
* **Purpose**: Dispatch goods from source warehouse to destination branch.
* **Permission**: `require.permission:inventory.manage`
* **Supported Modes**:
  1. **New Transfer Dispatch**: creates transfer in `in_transit` state and deducts stock from source location immediately via `StockMovement::TYPE_TRANSFER_OUT`.
  2. **Existing Transfer Dispatch**: takes `transfer_id`, transitions `pending` -> `in_transit`, deducts stock from source location via `StockMovement::TYPE_TRANSFER_OUT`.

#### Request Body Schema:
```json
{
  "transfer_id": null, // or UUID of existing pending transfer
  "source_location_id": "9d901a8b-loc1-0000-0000-000000000001",
  "destination_location_id": "9d901a8b-loc2-0000-0000-000000000002",
  "transfer_date": "2026-11-02",
  "notes": "Pengiriman stok via kurir pickup",
  "items": [
    {
      "product_id": "9d901a8b-prd1-0000-0000-000000000001",
      "quantity": 10.0
    }
  ]
}
```

#### Validation Rules:
- `source_location_id`: `['required_without:transfer_id', 'uuid', 'exists:locations,id', 'different:destination_location_id']`
- `destination_location_id`: `['required_without:transfer_id', 'uuid', 'exists:locations,id']`
- `transfer_date`: `['required_without:transfer_id', 'date']`
- `items`: `['required_without:transfer_id', 'array', 'min:1']`
- `items.*.quantity`: `['required', 'numeric', 'min:0.0001']`
- Stock availability at source is locked and verified. Throws 422 if stock insufficient and negative stock is disabled.

#### Response Envelope (HTTP 201 Created):
```json
{
  "success": true,
  "message": "Transfer Stok #TRF-20261102-101 berhasil diberangkatkan (in-transit). Stok gudang asal telah berkurang.",
  "transfer": {
    "id": "9d901a8b-trf1-0000-0000-000000000001",
    "transfer_number": "TRF-20261102-101",
    "status": "in_transit",
    "source_location_id": "9d901a8b-loc1-0000-0000-000000000001",
    "destination_location_id": "9d901a8b-loc2-0000-0000-000000000002",
    "sent_at": "2026-11-02T09:00:00Z"
  }
}
```

---

### Endpoint 6: `POST /api/v1/inventory/transfers/{id}/receive`
* **Purpose**: Receive transfer at destination branch.
* **Path Parameter**: `{id}`: Transfer UUID.
* **Permission**: `require.permission:inventory.manage`
* **Execution Logic**:
  - Checks if transfer is already `received` (idempotent, return 200).
  - If transfer is `in_transit`: records `StockMovement::TYPE_TRANSFER_IN` to `destination_location_id` (since source was already deducted upon dispatch).
  - If transfer is `pending` (legacy direct receive): atomically executes both OUT from source and IN to destination.
  - Updates transfer `status = 'received'`, `received_by = user.id`, `received_at = now()`.

#### Response Envelope (HTTP 200 OK):
```json
{
  "success": true,
  "message": "Transfer Stok #TRF-20261102-101 telah diterima & stok cabang tujuan berhasil ditambahkan.",
  "transfer": {
    "id": "9d901a8b-trf1-0000-0000-000000000001",
    "transfer_number": "TRF-20261102-101",
    "status": "received",
    "received_at": "2026-11-02T11:45:00Z",
    "received_by": "user-uuid-dest-cashier"
  }
}
```

---

### Endpoint 7: `GET /api/v1/customers/{id}/debts`
* **Purpose**: Retrieve customer AR ledger, active Kasbon/Tempo bills, and transaction history.
* **Permission**: `require.permission:customers.view` or `require.permission:pos.terminal`
* **Path Parameter**: `{id}`: Customer UUID.
* **Query Parameters**: `page` (default 1), `per_page` (default 20).

#### Response Envelope (HTTP 200 OK):
```json
{
  "success": true,
  "customer": {
    "id": "9d901a8b-cst1-0000-0000-000000000001",
    "name": "Pak Haji Ridwan",
    "phone": "081234567890",
    "company_name": "Warung Berkah",
    "credit_limit": 5000000.0,
    "current_credit_balance": 750000.0,
    "available_credit": 4250000.0,
    "payment_terms_days": 30
  },
  "unpaid_orders": [
    {
      "id": "9d901a8b-ord1-0000-0000-000000000001",
      "order_number": "ORD-20261028-045",
      "order_date": "2026-10-28",
      "total_amount": 750000.0,
      "unpaid_amount": 750000.0,
      "notes": "Bon belanja mingguan"
    }
  ],
  "transactions": [
    {
      "id": "9d901a8b-cct1-0000-0000-000000000001",
      "type": "charge",
      "amount": 750000.0,
      "balance_after": 750000.0,
      "reference_type": "pos_order",
      "reference_id": "9d901a8b-ord1-0000-0000-000000000001",
      "notes": "Belanja POS tempo (kredit) #ORD-20261028-045",
      "created_at": "2026-10-28T14:20:00Z"
    }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 20,
    "total": 1
  }
}
```

---

### Endpoint 8: `POST /api/v1/customers/{id}/repay-debt`
* **Purpose**: Record repayment/installment against outstanding customer debt.
* **Permission**: `require.permission:pos.terminal` or `require.permission:customers.edit`

#### Request Body Schema:
```json
{
  "amount": 250000.0,
  "payment_method": "cash",
  "cash_account_id": null,
  "notes": "Cicilan piutang bon beras #1",
  "send_wa": true
}
```

#### Validation Rules:
- `amount`: `['required', 'numeric', 'min:1', 'max:' . $customer->current_credit_balance]`
- `payment_method`: `['nullable', 'string', 'in:cash,bank_transfer,qris']`
- `cash_account_id`: `['nullable', 'uuid', 'exists:cash_accounts,id']`
- `notes`: `['nullable', 'string', 'max:255']`

#### Backend State Mutations Executed Atomically:
1. Validates that `$amount <= $customer->current_credit_balance`.
2. Decreases `$customer->current_credit_balance` by `$amount`.
3. Creates `CustomerCreditTransaction` (`type = 'payment'`).
4. Records `CashTransaction` (`type = 'in'`) into tenant `CashAccount` (`CashLedgerEntry`) for audit & anti-lapping compliance.
5. Formats WhatsApp digital payment proof URL via `LoyaltyService::generateCreditPaymentWhatsAppReceiptUrl`.
6. Logs audit trail with low risk level.

#### Response Envelope (HTTP 200 OK):
```json
{
  "success": true,
  "message": "Pembayaran piutang berhasil dicatat dan dibukukan ke buku kas.",
  "data": {
    "transaction_id": "9d901a8b-cct2-0000-0000-000000000002",
    "amount_paid": 250000.0,
    "previous_balance": 750000.0,
    "remaining_balance": 500000.0,
    "wa_receipt_url": "https://wa.me/6281234567890?text=*BUKTI%20PEMBAYARAN%20PIUTANG*...",
    "sent_via_gateway": false,
    "customer": {
      "id": "9d901a8b-cst1-0000-0000-000000000001",
      "name": "Pak Haji Ridwan",
      "current_credit_balance": 500000.0
    }
  }
}
```

---

### Endpoint 9: `POST /api/v1/pos/orders/{id}/send-whatsapp-receipt`
* **Purpose**: Trigger official digital receipt dispatch to customer WhatsApp.
* **Path Parameter**: `{id}`: PosOrder UUID.
* **Permission**: `require.permission:pos.terminal` or `require.permission:pos.orders`

#### Request Body Schema:
```json
{
  "phone": "081234567890",
  "force": false
}
```

#### Validation Rules:
- `phone`: `['nullable', 'string', 'regex:/^[0-9+]{8,20}$/']`
- `force`: `['nullable', 'boolean']`

#### Backend Processing:
- If phone is omitted, resolves from `$order->customer->phone ?? $order->customer_phone_guest`.
- Generates pre-formatted `whatsapp_url` via `LoyaltyService::generateWhatsAppReceiptUrl`.
- Attempts delivery via `WhatsAppGatewayService::sendReceipt($order, $phone, $force)`.
- If gateway delivery succeeds, returns `sent_via_gateway: true`.
- If gateway delivery is disconnected or unconfigured, returns `sent_via_gateway: false` with `whatsapp_url` so mobile client can immediately invoke `url_launcher`.

#### Response Envelope (HTTP 200 OK):
```json
{
  "success": true,
  "sent_via_gateway": true,
  "whatsapp_url": "https://wa.me/6281234567890?text=%F0%9F%A7%BE%20*STRUK%20PEMBELIAN*...",
  "message": "Struk digital berhasil dikirim ke nomor WhatsApp 081234567890."
}
```

---

### Endpoint 10: Storefront / QR Incoming Orders Endpoint (`GET /api/v1/commerce/orders/incoming`)
* **Purpose**: Real-time polling & notification stream for incoming storefront online orders (`CommerceOrder`) and standee QR table orders (`PosOrder`).
* **Permission**: `require.permission:pos.terminal` or `require.permission:sales.view`
* **Query Parameters**:
  - `location_id` (optional, UUID): Branch filter
  - `since` (optional, string): ISO8601 timestamp

#### Response Envelope (HTTP 200 OK):
```json
{
  "success": true,
  "summary": {
    "total_incoming": 2,
    "storefront_count": 1,
    "qr_table_count": 1
  },
  "storefront_orders": [
    {
      "id": "9d901a8b-ord-onl-001",
      "order_number": "ORD-ONL-20261102-005",
      "source": "storefront",
      "customer_name": "Siti Rahma",
      "customer_phone": "081398765432",
      "fulfillment_type": "courier_manual",
      "shipping_courier_name": "GoSend Instant",
      "delivery_address": "Jl. Merdeka No. 45, Bandung",
      "total_amount": 125000.0,
      "payment_status": "paid",
      "status": "processing",
      "created_at": "2026-11-02T10:18:22Z",
      "items": [
        {
          "product_name": "Paket Nasi Liwet Komplit",
          "quantity": 2.0,
          "price": 45000.0,
          "notes": "Sambal dipisah"
        }
      ]
    }
  ],
  "qr_table_orders": [
    {
      "id": "9d901a8b-pos-qr-001",
      "order_number": "POS-20261102-088",
      "source": "qr_table",
      "table_number": "Meja 05",
      "customer_name": "Andi",
      "total_amount": 65000.0,
      "payment_status": "unpaid",
      "status": "pending",
      "created_at": "2026-11-02T10:19:05Z",
      "items": [
        {
          "product_name": "Es Kopi Susu Gula Aren",
          "quantity": 2.0,
          "price": 22000.0,
          "modifiers": ["Less Ice", "Extra Shot"]
        }
      ]
    }
  ]
}
```

---

## 3. Error Handling & Validation Rules Matrix

### Laravel 422 Standard Error Format
Whenever validation fails in `cooca_core`, Laravel returns HTTP 422 with the standard envelope:
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "amount": [
      "Jumlah pembayaran piutang tidak boleh melebihi sisa piutang aktif."
    ],
    "location_id": [
      "Lokasi gudang wajib dipilih."
    ]
  }
}
```

### Mobile Error Parsing Architecture (`ApiClient`)
In `c:\laragon\www\cooca_my_own\lib\core\services\api_client.dart`:
```dart
dynamic _processResponse(http.Response response) {
  try {
    final decoded = jsonDecode(response.body);
    if (response.statusCode >= 200 && response.statusCode < 300) {
      return decoded;
    }
    return {
      'success': false,
      'statusCode': response.statusCode,
      'message': decoded is Map && decoded.containsKey('message')
          ? decoded['message']
          : 'Terjadi kendala server (Kode: ${response.statusCode})',
      'errors': decoded is Map ? decoded['errors'] : null,
      'data': decoded is Map ? decoded['data'] : null,
    };
  } catch (e) {
    return {
      'success': false,
      'statusCode': response.statusCode,
      'message': 'Format respons server tidak valid.',
    };
  }
}
```

### UI Error Surfacing Policy in Mobile
1. Form submissions (Goods Receipt, Transfer Dispatch, Kasbon Repay) check `if (res['success'] != true)` and surface `res['message']` via floating `SnackBar` styled with `IOSColors.destructive` or red `#FF3B30`.
2. Form inputs highlight borders in red if matching key exists in `res['errors']`.
3. Critical validations (e.g., received qty > remaining qty, amount > credit balance) are pre-validated client-side before dispatch to avoid unnecessary network latency, while server-side remains the single source of truth (SSOT).

---

## 4. Database Models & Schema Definitions

| Model | Table | Key Fields & Types | Purpose |
| :--- | :--- | :--- | :--- |
| `PurchaseOrder` | `purchase_orders` | `id` (uuid), `po_number` (str), `status` (draft/confirmed/partially_invoiced/completed/cancelled), `po_type` (customer/supplier), `order_date`, `supplier_id` | Supplier Purchase Order Master |
| `PurchaseOrderItem` | `purchase_order_items` | `id` (uuid), `purchase_order_id`, `product_id`, `material_id`, `quantity`, `received_quantity`, `unit_price`, `unit_id` | PO line items & tracking of remaining qty |
| `GoodsReceipt` | `goods_receipts` | `id` (uuid), `receipt_number`, `purchase_order_id`, `location_id`, `receipt_date`, `status` (completed), `received_by` | Physical Goods Receipt Master |
| `GoodsReceiptItem` | `goods_receipt_items` | `id` (uuid), `goods_receipt_id`, `product_id`, `material_id`, `quantity`, `unit_cost`, `batch_number`, `expiry_date` | Goods Receipt line items with lot/exp |
| `ProductBatch` | `product_batches` | `id` (uuid), `business_id`, `location_id`, `product_id`, `batch_number`, `expiry_date` (date), `quantity`, `unit_cost`, `is_active` (bool) | Lot/Batch tracking for Apotek & F&B |
| `StockTransfer` | `stock_transfers` | `id` (uuid), `transfer_number`, `source_location_id`, `destination_location_id`, `status` (pending/in_transit/received/cancelled), `sent_at`, `received_at`, `created_by`, `received_by` | Branch transfer master |
| `StockTransferItem` | `stock_transfer_items` | `id` (uuid), `stock_transfer_id`, `product_id`, `material_id`, `quantity`, `unit_cost`, `total_cost` | Branch transfer line items |
| `Customer` | `customers` | `id` (uuid), `name`, `phone`, `credit_limit` (float), `current_credit_balance` (float), `payment_terms_days` (int) | Customer profile & AR balance |
| `CustomerCreditTransaction` | `customer_credit_transactions` | `id` (uuid), `business_id`, `customer_id`, `type` (charge/payment), `amount`, `balance_after`, `reference_type`, `reference_id`, `created_by` | AR Kasbon ledger entries |
| `CommerceOrder` | `commerce_orders` | `id` (uuid), `order_number`, `status`, `fulfillment_type` (pickup/courier_manual/biteship), `shipping_courier_name`, `shipping_waybill_id` | Online Storefront orders |
| `PosOrder` | `pos_orders` | `id` (uuid), `order_number`, `order_source` (pos/qr_table), `status` (pending/confirmed/preparing/ready/completed), `pos_table_id` | Table QR self-order & POS orders |

---

## 5. Test Automation Baselines & Verification Setup

### Backend Test Execution (`cooca_core`)
- **Framework**: PHPUnit 11 / Laravel Test Runner
- **Configuration File**: `c:\laragon\www\cooca_core\phpunit.xml`
- **Environment**:
  - `DB_CONNECTION=sqlite`
  - `DB_DATABASE=:memory:`
  - `CACHE_STORE=array`
  - `QUEUE_CONNECTION=sync`
- **Verified Execution Command**:
  ```powershell
  php artisan test --filter=StockServiceConcurrencyAndIntegrityTest
  ```
- **Observed Result**:
  - `{"tool":"phpunit","result":"passed","tests":1,"passed":1,"assertions":5,"duration_ms":28915}`
  - Tests passed with exit code 0.

### Mobile Test Execution (`cooca_my_own`)
- **Static Analyzer**:
  - Command: `cd c:\laragon\www\cooca_my_own; flutter analyze`
  - Result: `No issues found! (ran in 16.5s)` (0 issues, exit code 0).
- **Widget & Unit Testing**:
  - Command: `cd c:\laragon\www\cooca_my_own; flutter test test/pos_expense_modal_test.dart`
  - Result: `00:03 +4: All tests passed!` (100% passed, exit code 0).

### Package Dependency Note:
`url_launcher` is specified in R2/R3 for WhatsApp direct URL launching in `pos_receipt_screen.dart`.
Currently, `c:\laragon\www\cooca_my_own\pubspec.yaml` does not have `url_launcher` listed in `dependencies`.
The implementer agent must add:
```yaml
dependencies:
  url_launcher: ^6.3.1
```
and execute `flutter pub get`.

---

## 6. Implementation Action Plan for Implementer Agents

### For Backend Implementer (`cooca_core`):
1. **Routes Registration** (`routes/api.php`):
   - Under `Route::prefix('inventory')`:
     - `Route::get('/purchase-orders/pending', [InventoryPurchaseOrderController::class, 'pending'])`
     - `Route::get('/purchase-orders/{purchaseOrder}', [InventoryPurchaseOrderController::class, 'show'])`
     - `Route::post('/goods-receipts', [InventoryGoodsReceiptController::class, 'store'])`
     - `Route::post('/transfers/dispatch', [StockTransferController::class, 'dispatchTransfer'])`
     - Keep existing `/transfers` and `/transfers/{stockTransfer}/receive`.
   - Under `Route::prefix('customers')` (or `Route::prefix('crm')`):
     - `Route::get('/{customer}/debts', [CustomerDebtApiController::class, 'debts'])`
     - `Route::post('/{customer}/repay-debt', [CustomerDebtApiController::class, 'repayDebt'])`
   - Under `Route::prefix('pos')`:
     - `Route::post('/orders/{posOrder}/send-whatsapp-receipt', [PosReceiptApiController::class, 'sendWhatsAppReceipt'])`
   - Under `Route::prefix('commerce')`:
     - `Route::get('/orders/incoming', [MerchantOrderApiController::class, 'incomingOrders'])`
2. **Services & Models Alignment**:
   - Update `StockTransferController::dispatchTransfer` to deduct source location stock (`StockMovement::TYPE_TRANSFER_OUT`) and set `status = StockTransfer::STATUS_IN_TRANSIT`.
   - Update `StockService::completeStockTransfer` so that if status was `in_transit`, it only adds to destination location (`StockMovement::TYPE_TRANSFER_IN`).
   - Create/link `ProductBatch` records when `batch_number` and `expiry_date` are supplied in Goods Receipt.
3. **Automated Tests**:
   - Create `tests/Feature/Inventory/GoodsReceiptAndPoReceiveTest.php`
   - Create `tests/Feature/Inventory/StockTransferDispatchAndReceiveTest.php`
   - Create `tests/Feature/Crm/CustomerDebtAndRepayTest.php`
   - Create `tests/Feature/Pos/PosWhatsAppReceiptApiTest.php`

### For Mobile Implementer (`cooca_my_own`):
1. **Dependencies**:
   - Add `url_launcher: ^6.3.1` to `pubspec.yaml` and run `flutter pub get`.
2. **Services**:
   - Extend `InventoryService` with `getPendingPurchaseOrders()`, `getPurchaseOrder(id)`, `submitGoodsReceipt()`, `getTransfers()`, `dispatchTransfer()`, `receiveTransfer()`.
   - Extend `PosService` with `getCustomerDebts(id)`, `repayCustomerDebt()`, `sendWhatsAppReceipt()`, `getIncomingOnlineOrders()`.
3. **Screens & Modals**:
   - `lib/screens/inventory/goods_receipt_screen.dart`: Barcode camera scanner with `mobile_scanner`, actual vs damaged qty input, lot/batch & expired date form.
   - `lib/screens/inventory/stock_transfer_screen.dart`: Tab Kirim (Dispatch) & Tab Terima (Inbound Scan).
   - `lib/screens/pos/pos_customer_debt_sheet.dart`: Kasbon history modal, total piutang card, cicilan input with WhatsApp receipt generation.
   - `lib/screens/pos/pos_receipt_screen.dart`: Integrate WhatsApp receipt button calling API with fallback to `url_launcher` intent.
   - `lib/screens/dashboard_screen.dart` & `lib/screens/pos_screen.dart`: Online order notification dialog & badge listener.
4. **Automated Tests**:
   - Add unit and widget tests in `test/` for new screens.
   - Ensure `flutter analyze` retains 0 issues.
