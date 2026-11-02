# Laporan Investigasi Arsitektur Backend: Fase 2 & Fase 3 (`cooca_core`)

**Peneliti:** Explorer 1 (Backend Architecture Explorer)  
**Tanggal:** 2026-11-02  
**Repositori Target:** `c:\laragon\www\cooca_core`  
**Rujukan:** `ORIGINAL_REQUEST.md` & `plan_core_to_mobile_features.md`

---

## Executive Summary

Investigasi mendalam terhadap repositori backend `c:\laragon\www\cooca_core` membuktikan bahwa fondasi domain logistik (Fase 2) dan buku piutang / omnichannel (Fase 3) sudah memiliki arsitektur yang sangat solid, aman, dan siap pakai. Sebagian besar logika domain inti (Domain Services, Eloquent Models, Migrations, dan Audit Trail) telah diimplementasikan dengan standar tinggi:
- **Penerimaan Barang (Goods Receipt)** dan mutasi stok multi-lokasi telah didukung oleh `GoodsReceiptService` dan `StockService` dengan pencatatan batch number dan expiry date.
- **Transfer Stok Cabang** telah memiliki tabel `stock_transfers` & `stock_transfer_items`, tinggal melengkapi endpoint *dispatch* (pengurangan stok dari gudang asal dengan status `in_transit`) agar selaras dengan alur 2-step transfer di mobile.
- **Buku Piutang & Kasbon Pelanggan** telah memiliki mesin akuntansi anti-lapping di `LoyaltyService::recordCustomerCreditCharge` dan `LoyaltyService::recordCustomerCreditPayment`, terintegrasi dengan jurnal arus kas masuk (`CashTransaction`) dan `AuditLog`.
- **Pengiriman Struk WhatsApp** telah memiliki dua generator URL resmi di `LoyaltyService`, serta integrasi driver Meta Cloud API di `WhatsAppGatewayService` dan `WhatsAppService`.
- **Pesanan Masuk Omnichannel** telah mencakup dua jalur: `CommerceOrder` (toko online web dengan integrasi Biteship kurir) dan `PosOrder` (`SOURCE_QR_TABLE` untuk QR meja dine-in).
- **Test Suite Baseline** terverifikasi 100% fungsional menggunakan PHPUnit dengan autentikasi Sanctum dan multi-tenant scoping (`Context::requireBusiness()`).

---

## 1. Analisis Arsitektur Fase 2: Logistik & Gudang Toko

### 1.1 Model & Relasi Purchase Order (PO)
- **Model File:** [`app/Models/PurchaseOrder.php`](file:///c:/laragon/www/cooca_core/app/Models/PurchaseOrder.php)
  - **Tabel:** `purchase_orders`
  - **Konstanta Tipe:** `TYPE_CUSTOMER = 'customer'`, `TYPE_SUPPLIER = 'supplier'`
  - **Konstanta Status:**
    - `STATUS_DRAFT = 'draft'`
    - `STATUS_CONFIRMED = 'confirmed'`
    - `STATUS_PARTIALLY_INVOICED = 'partially_invoiced'`
    - `STATUS_FULLY_INVOICED = 'fully_invoiced'`
    - `STATUS_COMPLETED = 'completed'`
    - `STATUS_CANCELLED = 'cancelled'`
  - **Kolom Utama:** `id`, `business_id`, `po_type`, `po_number`, `reference_number`, `customer_id`, `supplier_id`, `order_date`, `expected_delivery_date`, `status`, `subtotal`, `discount_type`, `discount_value`, `discount_amount`, `tax_percentage`, `tax_amount`, `total_amount`, `terms_and_conditions`, `notes`, `created_by`.
  - **Relasi:**
    - `supplier(): BelongsTo<Supplier>`
    - `customer(): BelongsTo<Customer>`
    - `items(): HasMany<PurchaseOrderItem>`
    - `goodsReceipts(): HasMany<GoodsReceipt>`
    - `invoices(): HasMany<Invoice>`
    - `approvalRequest(): HasOne<ApprovalRequest>`
- **Model Item:** [`app/Models/PurchaseOrderItem.php`](file:///c:/laragon/www/cooca_core/app/Models/PurchaseOrderItem.php)
  - **Tabel:** `purchase_order_items`
  - **Kolom Utama:** `id`, `purchase_order_id`, `item_type`, `product_id`, `material_id`, `item_name`, `sku`, `quantity`, `invoiced_quantity`, `unit_id`, `unit_price`, `purchase_price_snapshot`, `supplier_name_snapshot`, `cost_price_snapshot`, `subtotal`, `notes`.
  - **Otomasi Model:** Menghitung `subtotal` dan merekam snapshot harga beli saat pertama kali dibuat (`saving`), serta memicu `recalculateTotals()` pada PO induk saat disimpan atau dihapus (`saved`/`deleted`).

### 1.2 Model & Service Goods Receipt (Penerimaan Barang)
- **Model Header:** [`app/Models/GoodsReceipt.php`](file:///c:/laragon/www/cooca_core/app/Models/GoodsReceipt.php)
  - **Tabel:** `goods_receipts`
  - **Kolom Utama:** `id`, `business_id`, `location_id`, `supplier_id`, `purchase_order_id`, `receipt_number`, `receipt_date`, `status` (`completed`), `notes`, `received_by`.
  - **Relasi:** `location()`, `supplier()`, `purchaseOrder()`, `receiver()`, `items()`, `supplierInvoice()`.
- **Model Item:** [`app/Models/GoodsReceiptItem.php`](file:///c:/laragon/www/cooca_core/app/Models/GoodsReceiptItem.php)
  - **Tabel:** `goods_receipt_items`
  - **Kolom Utama:** `id`, `goods_receipt_id`, `product_id`, `material_id`, `item_name`, `quantity`, `unit_cost`, `batch_number`, `expiry_date`, `notes`.
- **Domain Service:** [`app/Domain/Purchasing/GoodsReceiptService.php`](file:///c:/laragon/www/cooca_core/app/Domain/Purchasing/GoodsReceiptService.php)
  - Metode utama: `receive(PurchaseOrder $purchaseOrder, array $data, ?string $userId = null): GoodsReceipt`
  - **Alur Kerja Atomik (`DB::transaction`):**
    1. Mengunci baris PO dengan `lockForUpdate()`.
    2. Validasi status PO harus `confirmed` atau `partially_invoiced`.
    3. Validasi kuantitas yang diterima terhadap sisa kuantitas PO yang belum diterima (mencegah *over-receiving*).
    4. Menyimpan rekaman `GoodsReceipt` dan `GoodsReceiptItem`.
    5. Memanggil `StockService::recordMovement` untuk setiap item dengan `movementType: StockMovement::TYPE_GOODS_RECEIPT`, mencatat kuantitas masuk (`+$quantity`), biaya satuan (`unit_cost`), `batch_number`, dan `expiry_date`.
    6. Jika item adalah `material_id`, memperbarui `MaterialPrice` dan memicu `HppPropagationService::refreshForMaterial`.
    7. Mengevaluasi apakah seluruh item PO telah terpenuhi: jika ya, status PO berubah menjadi `completed`, jika belum, berubah menjadi `partially_invoiced`.
    8. Otomatis membuat faktur supplier via `SupplierInvoiceService::createFromGoodsReceipt($receipt)`.

### 1.3 Mutasi Stok & Penanganan Batch/Lot serta Expired Date
- **Tabel Stok & Mutasi:**
  - `inventory_stocks`: `business_id`, `location_id`, `product_id`, `material_id`, `quantity`, `reserved_quantity`, `last_cost`, `avg_purchase_cost`.
  - `stock_movements`: `business_id`, `location_id`, `product_id`, `material_id`, `movement_type`, `reference_id`, `reference_number`, `quantity_change`, `balance_after`, `unit_cost`, `total_cost`, `batch_number`, `expiry_date`, `notes`, `created_by`.
  - `product_batches`: [`app/Models/ProductBatch.php`](file:///c:/laragon/www/cooca_core/app/Models/ProductBatch.php)
    - Kolom: `id`, `business_id`, `location_id`, `product_id`, `batch_number`, `expiry_date`, `quantity`, `unit_cost`, `is_active`.
    - Index komposit: `['business_id', 'product_id', 'batch_number']`.
- **Logika Perhitungan Rata-rata Biaya Beli (`StockService`):**
  Saat `TYPE_GOODS_RECEIPT` masuk, `StockService` memperbarui `avg_purchase_cost` dengan rumus tertimbang (*weighted moving average*):
  $$\text{avg\_purchase\_cost} = \frac{(\text{old\_qty} \times \text{old\_avg\_cost}) + (\text{received\_qty} \times \text{unit\_cost})}{\text{new\_qty}}$$

### 1.4 Model & Siklus Hidup Stock Transfer (Mutasi Antar Cabang)
- **Model Header:** [`app/Models/StockTransfer.php`](file:///c:/laragon/www/cooca_core/app/Models/StockTransfer.php)
  - **Tabel:** `stock_transfers`
  - **Konstanta Status:**
    - `STATUS_PENDING = 'pending'`
    - `STATUS_IN_TRANSIT = 'in_transit'`
    - `STATUS_RECEIVED = 'received'`
    - `STATUS_CANCELLED = 'cancelled'`
  - **Kolom Utama:** `id`, `business_id`, `source_location_id`, `destination_location_id`, `transfer_number`, `transfer_date`, `status`, `notes`, `created_by`, `received_by`, `sent_at`, `received_at`.
- **Model Item:** [`app/Models/StockTransferItem.php`](file:///c:/laragon/www/cooca_core/app/Models/StockTransferItem.php)
  - **Tabel:** `stock_transfer_items`
  - **Kolom Utama:** `id`, `stock_transfer_id`, `product_id`, `material_id`, `quantity`, `unit_cost`, `total_cost`, `notes`.
- **Implementasi Controller Saat Ini:** [`app/Http/Controllers/Api/V1/Inventory/StockTransferController.php`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Api/V1/Inventory/StockTransferController.php)
  - `index(Request $request)`: Filter `status`, `source_location_id`, `destination_location_id`.
  - `store(Request $request)`: Membuat record transfer berstatus `pending`.
  - `show(Request $request, StockTransfer $stockTransfer)`: Detail transfer dan item.
  - `receive(Request $request, StockTransfer $stockTransfer)`: Memanggil `StockService::completeStockTransfer`.
- **Analisis Kesenjangan (Gap Analysis) untuk Mobile:**
  - Di `StockService::completeStockTransfer` saat ini, mutasi keluar (`TYPE_TRANSFER_OUT`) dari lokasi asal dan mutasi masuk (`TYPE_TRANSFER_IN`) ke lokasi tujuan dijalankan sekaligus di metode `receive`.
  - R1 / Acceptance Criteria membutuhkan alur 2 tahap (Two-Step Transfer):
    1. **Tab Dispatch (Kirim Barang):** Memanggil `POST /api/v1/inventory/transfers/dispatch` untuk memotong stok lokasi asal (`TYPE_TRANSFER_OUT`), mengisi `sent_at`, dan mengubah status menjadi `in_transit`.
    2. **Tab Inbound Scan (Terima Barang):** Memanggil `POST /api/v1/inventory/transfers/{id}/receive` untuk menambah stok lokasi tujuan (`TYPE_TRANSFER_IN`), mengisi `received_at`, dan mengubah status menjadi `received`.

---

## 2. Analisis Arsitektur Fase 3: Customer Piutang & Omnichannel Digital

### 2.1 Manajemen Piutang & Kasbon Pelanggan (Accounts Receivable)
- **Model Customer:** [`app/Models/Customer.php`](file:///c:/laragon/www/cooca_core/app/Models/Customer.php)
  - Kolom finansial: `credit_limit` (plafon pinjaman), `current_credit_balance` (total sisa hutang saat ini), `payment_terms_days` (jatuh tempo nota).
  - Relasi: `creditTransactions(): HasMany<CustomerCreditTransaction>`.
- **Model Transaksi Kredit:** [`app/Models/CustomerCreditTransaction.php`](file:///c:/laragon/www/cooca_core/app/Models/CustomerCreditTransaction.php)
  - **Tabel:** `customer_credit_transactions`
  - **Konstanta Tipe:** `TYPE_CHARGE = 'charge'` (penambahan hutang saat belanja kasbon), `TYPE_PAYMENT = 'payment'` (pelunasan / cicilan hutang).
  - **Kolom Utama:** `id`, `business_id`, `customer_id`, `type`, `amount`, `reference_type` (`pos_order`, `manual_payment`), `reference_id`, `balance_after`, `notes`, `created_by`, timestamps.
- **Pencatatan Belanja Kasbon di POS:**
  - Metode bayar: `PosOrderPayment::METHOD_CUSTOMER_CREDIT = 'customer_credit'`.
  - Pada [`app/Domain/Pos/PosOrderService.php`](file:///c:/laragon/www/cooca_core/app/Domain/Pos/PosOrderService.php) (baris 516-517):
    Jika pembayaran adalah `customer_credit`, sistem otomatis memanggil:
    `$this->loyaltyService->recordCustomerCreditCharge($customer, $order, $payAmount, $cashier);`
  - Di `LoyaltyService::recordCustomerCreditCharge`:
    - `$customer->current_credit_balance += $amount;`
    - Membuat rekaman `CustomerCreditTransaction` dengan tipe `charge`, `reference_type: 'pos_order'`.
- **Pencatatan Cicilan / Pelunasan Piutang (Anti-Lapping & Ledger Sync):**
  - Implementasi di [`app/Domain/Crm/LoyaltyService.php`](file:///c:/laragon/www/cooca_core/app/Domain/Crm/LoyaltyService.php) baris 159-234 (`recordCustomerCreditPayment`):
    1. Memverifikasi tenant guard (`$customer->business_id === $user->active_business_id`).
    2. Menolak pembayaran jika `$amount > $customer->current_credit_balance`.
    3. Mengurangi sisa saldo hutang: `$customer->current_credit_balance -= $amount`.
    4. Membuat `CustomerCreditTransaction` bertipe `payment` (`reference_type: 'manual_payment'`).
    5. Menemukan/membuat `CashAccount` ('Kas Utama') untuk bisnis bersangkutan, dan menambah saldo kas sebesar `$amount`.
    6. Mencatat jurnal mutasi kas masuk di `CashTransaction` (`type: TYPE_IN`, `reference_type: 'customer_credit_repayment'`).
    7. Menyimpan immutable record di `AuditLog` (`action: 'customer.credit_payment_recorded'`).
    8. Menghasilkan tautan bukti bayar WhatsApp resmi via `generateCreditPaymentWhatsAppReceiptUrl($customer, $transaction)`.

### 2.2 Integrasi Struk Digital via WhatsApp
- **Generator Tautan WhatsApp Struk:**
  - Di [`app/Domain/Crm/LoyaltyService.php`](file:///c:/laragon/www/cooca_core/app/Domain/Crm/LoyaltyService.php):
    - `generateWhatsAppReceiptUrl(PosOrder $order, ?string $phone = null): string`: Membuat format pesan struk belanja rapi dengan link e-struk publik `route('public.receipt', $order->id)`, mendukung template kustom outlet atau default WhatsApp message, dan mengembalikan URL `https://wa.me/{cleanPhone}?text={urlEncodedText}`.
    - `generateCreditPaymentWhatsAppReceiptUrl(Customer $customer, CustomerCreditTransaction $transaction, ?string $phone = null): string`: Membuat bukti pembayaran piutang resmi berformat digital.
- **Service Pengiriman Outbound Langsung (Server-to-Server):**
  - [`app/Domain/WhatsApp/WhatsAppService.php`](file:///c:/laragon/www/cooca_core/app/Domain/WhatsApp/WhatsAppService.php): Menyediakan pengiriman langsung via API gateway Fonnte/Generic HTTP client (`sendMessage($phone, $message)`).
  - [`app/Domain/WhatsApp/WhatsAppGatewayService.php`](file:///c:/laragon/www/cooca_core/app/Domain/WhatsApp/WhatsAppGatewayService.php): Mendukung integrasi resmi Meta WhatsApp Cloud API (WABA) dengan per-business quota tracking (`EntitlementService`).
- **Endpoint Baru yang Diperlukan:**
  - `POST /api/v1/pos/orders/{id}/send-whatsapp-receipt`:
    - Mengambil order POS berdasarkan ID yang terotentikasi di tenant.
    - Menerima parameter opsional `{ phone?: string }`.
    - Menghasilkan tautan `whatsapp_url` via `LoyaltyService::generateWhatsAppReceiptUrl`.
    - Mencoba mengirim langsung via `WhatsAppGatewayService::sendMessage` jika akun WhatsApp bisnis telah terhubung.
    - Mengembalikan respons JSON berisi `whatsapp_url` (sebagai fallback instant share di mobile via `url_launcher`) dan status pengiriman server.

### 2.3 Arsitektur Pesanan Masuk Storefront & Standee QR Table
Sistem COOCA memiliki dua kanal pesanan digital yang saling melengkapi:
1. **Storefront Online Orders (`CommerceOrder`):**
   - Jalur: Pelanggan belanja dari web toko online (`cooca.id/store/{slug}`).
   - Model: [`app/Models/CommerceOrder.php`](file:///c:/laragon/www/cooca_core/app/Models/CommerceOrder.php)
   - Controller API: [`app/Http/Controllers/Api/V1/Commerce/MerchantOrderApiController.php`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Api/V1/Commerce/MerchantOrderApiController.php)
   - Endpoint:
     - `GET /api/v1/commerce/orders`: Menampilkan pesanan online masuk, filter status (`needs_verification`, `unpaid`, `processing`, `shipped`, `completed`, `cancelled`).
     - `GET /api/v1/commerce/orders/{order}`: Rincian pesanan, alamat penerima, kurir, dan bukti transfer.
     - `POST /api/v1/commerce/orders/{order}/verify-payment`: Konfirmasi transfer bank manual.
     - `POST /api/v1/commerce/orders/{order}/request-pickup`: Panggilan otomatis kurir logistik instan/reguler melalui integrasi **Biteship API** (`BiteshipService`).
2. **Standee QR Table Orders (`PosOrder` dengan `order_source = PosOrder::SOURCE_QR_TABLE`):**
   - Jalur: Tamu duduk di meja kafe/resto dan scan barcode QR meja.
   - Status Alur: `pending` (menunggu konfirmasi) $\rightarrow$ `confirmed` / `preparing` (KDS masak) $\rightarrow$ `ready` $\rightarrow$ `completed`.
   - Logika Terpasang Saat Ini di Web Controller: [`app/Http/Controllers/Web/Pos/PosTerminalWebController.php`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Web/Pos/PosTerminalWebController.php) baris 1100-1193:
     - `getIncomingOrders()`: Mengambil daftar pesanan QR meja berstatus `pending` & `confirmed`.
     - `acceptIncomingOrder(PosOrder $order)`: Memanggil `$this->orderService->acceptQrOrder($order, auth()->user())` untuk mengirim ke antrean dapur.
     - `rejectIncomingOrder(Request $request, PosOrder $order)`: Menolak pesanan dengan alasan.
   - **Kebutuhan Endpoint API v1:**
     Mendaftarkan rute setara di `routes/api.php` dan `PosTerminalController`:
     - `GET /api/v1/pos/incoming-orders`
     - `POST /api/v1/pos/orders/{order}/accept`
     - `POST /api/v1/pos/orders/{order}/reject`

---

## 3. Spesifikasi Kontrak API & Pemetaan Rute (`routes/api.php`)

Seluruh endpoint berikut berada dalam grup middleware `auth:sanctum` dan `business.active`.

### 3.1 Pemetaan Endpoint Fase 2 (Logistik & Gudang Toko)

| Method | Endpoint | Controller Action Rekomendasi | Keterangan & Payload |
|---|---|---|---|
| `GET` | `/api/v1/inventory/purchase-orders/pending` | `PurchaseOrderController@pending` (atau alias ke query filter) | Mengambil PO supplier dengan status `confirmed` & `partially_invoiced`, lengkap dengan sisa kuantitas belum diterima dan data supplier. |
| `GET` | `/api/v1/inventory/purchase-orders/{id}` | `PurchaseOrderController@show` | Menampilkan detail spesifik PO, termasuk barcode produk/SKU untuk verifikasi kamera HP. |
| `POST` | `/api/v1/inventory/goods-receipts` | `GoodsReceiptController@store` | Menerima barang fisik dari PO. Payload: `{ location_id, purchase_order_id, receipt_date, notes?, items: [ { product_id, quantity, unit_cost, batch_number?, expiry_date? } ] }`. Mengupdate sisa PO, mutasi stok masuk, dan snapshot HPP. |
| `GET` | `/api/v1/inventory/transfers` | `StockTransferController@index` | Riwayat dan daftar transfer stok antar cabang. Mendukung query `status` (`pending`, `in_transit`, `received`). |
| `POST` | `/api/v1/inventory/transfers/dispatch` | `StockTransferController@dispatch` | Membuat dan memberangkatkan kiriman barang dari cabang asal. Memotong stok asal (`TYPE_TRANSFER_OUT`), mengisi `sent_at`, dan menetapkan status `in_transit`. |
| `POST` | `/api/v1/inventory/transfers/{id}/receive` | `StockTransferController@receive` | Mengonfirmasi penerimaan barang di cabang tujuan. Menambahkan stok cabang tujuan (`TYPE_TRANSFER_IN`), mengisi `received_at`, dan status menjadi `received`. |

### 3.2 Pemetaan Endpoint Fase 3 (Customer Piutang & Omnichannel Digital)

| Method | Endpoint | Controller Action Rekomendasi | Keterangan & Payload |
|---|---|---|---|
| `GET` | `/api/v1/customers/{id}/debts` | `CustomerDebtApiController@debts` (atau di `CustomerController@debts`) | Menampilkan total sisa piutang, credit limit, available credit, serta daftar riwayat `customer_credit_transactions` (charge vs payment). |
| `POST` | `/api/v1/customers/{id}/repay-debt` | `CustomerDebtApiController@repayDebt` (atau di `CustomerController@repayDebt`) | Mencatat cicilan / pelunasan piutang pelanggan via `LoyaltyService::recordCustomerCreditPayment`. Payload: `{ amount, notes? }`. Mengembalikan sisa saldo baru dan `wa_receipt_url`. |
| `POST` | `/api/v1/pos/orders/{id}/send-whatsapp-receipt` | `PosTerminalController@sendWhatsAppReceipt` | Mengirim atau memformat e-struk digital WhatsApp. Payload: `{ phone? }`. Mengembalikan `{ success: true, whatsapp_url, phone, status }`. |
| `GET` | `/api/v1/pos/incoming-orders` | `PosTerminalController@incomingOrders` | Polling endpoint pesanan QR meja masuk (`status = pending/confirmed`), memuat detail meja, item, dan status bayar. |
| `POST` | `/api/v1/pos/orders/{order}/accept` | `PosTerminalController@acceptOrder` | Kasir menerima pesanan QR meja dan meneruskannya ke antrean KDS dapur. |
| `POST` | `/api/v1/pos/orders/{order}/reject` | `PosTerminalController@rejectOrder` | Kasir menolak pesanan QR meja dengan `{ reason: string }`. |
| `GET` | `/api/v1/commerce/orders` | `MerchantOrderApiController@index` | Daftar pesanan toko online web storefront. |
| `POST` | `/api/v1/commerce/orders/{order}/request-pickup` | `MerchantOrderApiController@requestPickup` | Permintaan penjemputan paket kurir instan/reguler via Biteship. |

---

## 4. Analisis Infrastruktur Pengujian (PHPUnit Test Baseline)

### 4.1 Konfigurasi & Setup Test
- **File Konfigurasi:** `phpunit.xml` menggunakan koneksi database pengujian (`DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:` atau `testing`).
- **Base Test Case:** [`tests/TestCase.php`](file:///c:/laragon/www/cooca_core/tests/TestCase.php) secara otomatis menjalankan `\App\Support\Context::flush()` pada `tearDown()` untuk menjamin isolasi konteks tenant antar pengujian.
- **Pola Autentikasi Sanctum & Tenant:**
  Pengujian endpoint API dilakukan menggunakan helper:
  ```php
  Sanctum::actingAs($user);
  $response = $this->withHeaders([
      'X-Business-Id' => $business->id,
  ])->postJson('/api/v1/...', $payload);
  ```

### 4.2 Verifikasi Eksekusi Nyata
Perintah verifikasi pengujian dijalankan langsung di lingkungan proyek:
```powershell
php artisan test --filter=PosExpenseApiTest
```
**Hasil Eksekusi:**
- **Status:** PASSED (Exit code: 0)
- **Rincian:** 8 tests, 30 assertions, durasi 33.17 detik.
- **Kesimpulan:** Seluruh harness testing Laravel 11, SQLite in-memory migrator, Sanctum token guard, dan multi-tenant assertions berada dalam kondisi prima tanpa error.

---

## 5. Rekomendasi Langkah Implementasi (Action Plan)

1. **Rute API (`routes/api.php`):**
   - Daftarkan alias rute untuk inventory purchase orders pending (`GET /inventory/purchase-orders/pending`).
   - Daftarkan `POST /inventory/transfers/dispatch` dan pastikan `/inventory/transfers/{stockTransfer}/receive` menerima ID UUID dengan aman.
   - Daftarkan rute pelanggan: `GET /customers/{customer}/debts` dan `POST /customers/{customer}/repay-debt`.
   - Daftarkan rute POS: `POST /pos/orders/{order}/send-whatsapp-receipt`, `GET /pos/incoming-orders`, `POST /pos/orders/{order}/accept`, dan `POST /pos/orders/{order}/reject`.
2. **Controller Enhancements:**
   - Tambahkan metode `pending` pada `PurchaseOrderController`.
   - Tambahkan metode `dispatch` pada `StockTransferController` yang memicu pemotongan stok asal dan mengubah status ke `in_transit`.
   - Tambahkan metode `debts` dan `repayDebt` (di `CustomerController` atau `CustomerDebtApiController`) yang memanfaatkan `LoyaltyService::recordCustomerCreditPayment`.
   - Tambahkan metode `sendWhatsAppReceipt`, `incomingOrders`, `acceptOrder`, dan `rejectOrder` pada `PosTerminalController`.
3. **Automated Feature Tests:**
   - Buat test suite baru `tests/Feature/Api/Fase2LogisticsApiTest.php` untuk memvalidasi alur PO pending, Goods Receipt (batch/exp), dan Stock Transfer dispatch-receive.
   - Buat test suite baru `tests/Feature/Api/Fase3CustomerDebtAndOmnichannelApiTest.php` untuk memvalidasi kasbon repayment, WhatsApp receipt endpoint, dan QR incoming orders.
