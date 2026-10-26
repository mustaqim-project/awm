# Database & Performance Audit: Modul Purchasing

| Aspek Database | Evaluasi | Status |
| :--- | :--- | :--- |
| **Indexing Coverage** | Indeks pada `business_id`, `supplier_id`, `status`, `order_date`, `receipt_number` | **Optimal** |
| **N+1 Query Prevention** | Eager loading relasi dokumen (`supplier`, `customer`, `items`, `invoices`) | **Verified** |
| **Atomic Transactions** | `DB::transaction()` membungkus penerimaan barang, stok, dan tagihan AP | **Atomic & ACID Compliant** |
| **Pessimistic Locking** | `lockForUpdate()` digunakan pada kalkulasi kuantitas PO dan Goods Receipt | **Concurrency-Safe** |

---

## 1. Audit Indeks & Integritas Relasional
Dokumen pengadaan melibatkan relasi multi-tabel (`purchase_orders` $\rightarrow$ `goods_receipts` $\rightarrow$ `supplier_invoices` $\rightarrow$ `supplier_payments`).

Evaluasi indeks kritis:
```sql
-- Indeks pencarian dan filter cepat purchasing
INDEX `po_business_order_date_idx` (`business_id`, `order_date`),
INDEX `po_business_status_idx` (`business_id`, `status`),
INDEX `gr_business_receipt_no_idx` (`business_id`, `receipt_number`),
INDEX `inv_business_invoice_no_idx` (`business_id`, `invoice_number`),
INDEX `ret_business_return_no_idx` (`business_id`, `return_number`)
```

---

## 2. Pencegahan Balapan Data (Concurrency & Locking)
Saat dua staf gudang menerima kiriman untuk PO yang sama secara bersamaan, risiko kelebihan penerimaan (*over-receipt*) dicegah melalui penguncian baris pesanan:
```php
$purchaseOrder = PurchaseOrder::with('items')->lockForUpdate()->findOrFail($purchaseOrder->id);
```
- Hal ini menjamin kalkulasi sisa barang yang belum dikirim dibaca dan diperbarui secara serial dan konsisten.

---

## 3. Optimasi Query Eager Loading
Pada controller listing (`PurchaseOrderWebController@index`, `SupplierInvoiceWebController@index`, dan `PurchaseReturnWebController@index`), pemanggilan relasi selalu dilakukan via eager loading:
- `PurchaseOrder::with(['customer', 'supplier', 'items'])->latest('order_date')`
- `SupplierInvoice::with('supplier')->latest('invoice_date')`
- `PurchaseReturn::with(['supplier', 'goodsReceipt'])->latest()`
Menghindari pembengkakan query database ke level $O(1)$.
