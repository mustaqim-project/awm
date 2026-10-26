# System Baseline: Modul Purchasing (Pengadaan)

| Detail | Deskripsi |
| :--- | :--- |
| **Modul** | Purchasing (Purchase Orders, Penerimaan Barang Fisik, Tagihan Supplier, & Retur) |
| **Namespace Controller** | `App\Http\Controllers\Web\PurchaseOrderWebController`<br>`App\Http\Controllers\Web\Purchasing\GoodsReceiptWebController`<br>`App\Http\Controllers\Web\Purchasing\SupplierInvoiceWebController`<br>`App\Http\Controllers\Web\PurchaseReturnWebController` |
| **Model Terkait** | `PurchaseOrder`, `PurchaseOrderItem`, `GoodsReceipt`, `GoodsReceiptItem`, `SupplierInvoice`, `SupplierPayment`, `PurchaseReturn`, `PurchaseReturnItem`, `Supplier`, `Location`, `StockMovement`, `CashAccount`, `AuditLog` |
| **Blade Views** | `resources/views/app/purchasing/bills/index.blade.php`, `show.blade.php`<br>`resources/views/app/purchasing/receipts/create.blade.php`<br>`resources/views/app/purchasing/returns/create.blade.php`, `index.blade.php`, `show.blade.php`<br>`resources/views/app/purchase-orders/index.blade.php`, `create.blade.php`, `show.blade.php`, `print.blade.php` |
| **File Bahasa** | `lang/id/purchasing.php`<br>`lang/en/purchasing.php` |
| **Test Suite** | `tests/Feature/PurchasingModuleAuditTest.php` |

---

## 1. Pemetaan Rute Sistem (Route Inventory)

| Nama Rute | HTTP Method | URI | Controller Action | Middleware Guard |
| :--- | :--- | :--- | :--- | :--- |
| `purchase-orders.index` | `GET` | `/purchase-orders` | `PurchaseOrderWebController@index` | `require.permission:purchasing.view` |
| `purchase-orders.create` | `GET` | `/purchase-orders/create` | `PurchaseOrderWebController@create` | `require.permission:purchasing.view` |
| `purchase-orders.show` | `GET` | `/purchase-orders/{purchaseOrder}` | `PurchaseOrderWebController@show` | `require.permission:purchasing.view` |
| `purchase-orders.print` | `GET` | `/purchase-orders/{purchaseOrder}/print` | `PurchaseOrderWebController@print` | `require.permission:purchasing.view` |
| `purchase-orders.store` | `POST` | `/purchase-orders` | `PurchaseOrderWebController@store` | `require.permission:purchasing.manage`, `entitlement:po` |
| `purchase-orders.confirm` | `POST` | `/purchase-orders/{purchaseOrder}/confirm` | `PurchaseOrderWebController@confirm` | `require.permission:purchasing.manage` |
| `purchase-orders.cancel` | `POST` | `/purchase-orders/{purchaseOrder}/cancel` | `PurchaseOrderWebController@cancel` | `require.permission:purchasing.manage` |
| `purchase-orders.generate-invoice` | `POST` | `/purchase-orders/{purchaseOrder}/generate-invoice` | `PurchaseOrderWebController@generateInvoice` | `require.permission:purchasing.manage` |
| `purchase-orders.destroy` | `DELETE` | `/purchase-orders/{purchaseOrder}` | `PurchaseOrderWebController@destroy` | `require.permission:purchasing.manage` |
| `purchasing.receipts.create` | `GET` | `/purchasing/receipts/{purchaseOrder}/create` | `GoodsReceiptWebController@create` | `require.permission:receiving.manage` |
| `purchasing.receipts.store` | `POST` | `/purchasing/receipts/{purchaseOrder}` | `GoodsReceiptWebController@store` | `require.permission:receiving.manage` |
| `purchasing.instant-stock-in` | `POST` | `/purchasing/instant-stock-in` | `GoodsReceiptWebController@instantStockIn` | `require.permission:receiving.manage` |
| `purchasing.bills.index` | `GET` | `/purchasing/bills` | `SupplierInvoiceWebController@index` | `require.permission:purchasing.bills` |
| `purchasing.bills.show` | `GET` | `/purchasing/bills/{invoice}` | `SupplierInvoiceWebController@show` | `require.permission:purchasing.bills` |
| `purchasing.bills.payments.store` | `POST` | `/purchasing/bills/{invoice}/payments` | `SupplierInvoiceWebController@recordPayment` | `require.permission:purchasing.bills` |
| `purchase.returns.index` | `GET` | `/purchasing/returns` | `PurchaseReturnWebController@index` | `require.permission:purchase.returns` |
| `purchase.returns.create` | `GET` | `/purchasing/returns/create` | `PurchaseReturnWebController@create` | `require.permission:purchase.returns` |
| `purchase.returns.store` | `POST` | `/purchasing/returns` | `PurchaseReturnWebController@store` | `require.permission:purchase.returns` |
| `purchase.returns.show` | `GET` | `/purchasing/returns/{return}` | `PurchaseReturnWebController@show` | `require.permission:purchase.returns` |
| `purchase.returns.approve` | `POST` | `/purchasing/returns/{return}/approve` | `PurchaseReturnWebController@approve` | `require.permission:purchase.returns` |
| `purchase.returns.complete` | `POST` | `/purchasing/returns/{return}/complete` | `PurchaseReturnWebController@complete` | `require.permission:purchase.returns` |

---

## 2. Struktur Skema Database Terkait
1. **`purchase_orders` & `purchase_order_items`**:
   - Menampung data PO (Vendor Order atau Client Order), termin pembayaran, diskon, pajak, dan rincian item produk/bahan baku.
2. **`goods_receipts` & `goods_receipt_items`**:
   - Menampung dokumen fisik penerimaan barang di lokasi gudang (`location_id`), kuantitas diterima, unit cost, batch number, dan tanggal kadaluarsa (*expiry date*).
3. **`supplier_invoices` & `supplier_payments`**:
   - Mengelola hutang usaha (*Accounts Payable*), tanggal jatuh tempo, sisa hutang (*balance due*), metode pembayaran, dan akun kas pengeluaran.
4. **`purchase_returns` & `purchase_return_items`**:
   - Mengelola retur barang fisik ke vendor, alasan retur, otorisasi PIN supervisor, dan pemotongan stok otomatis di gudang asal.
