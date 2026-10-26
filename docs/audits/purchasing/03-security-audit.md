# Security & Access Control Audit: Modul Purchasing

| Aspek Keamanan | Status | Penilaian |
| :--- | :--- | :--- |
| **Controller-Level RBAC** | **Enforced** | Keempat controller web mengimplementasikan kontrak `HasMiddleware` |
| **Multi-Tenant IDOR Isolation** | **Hardened** | `BelongsToBusiness` scope + verifikasi `business_id` eksplisit di setiap method |
| **Anti-Fraud Proteksi Pengeluaran Kas** | **Enforced** | Transaksi pembayaran tagihan $\ge \text{Rp } 5.000.000$ wajib PIN Supervisor |
| **Audit Trail Logging** | **Active** | Transaksi kritis (PO, Goods Receipt, Pembayaran Hutang, Retur) mencatat `AuditLog` |

---

## 1. Controller-Level RBAC Enforcement
Sesuai arahan `/laravel-rbac`, deklarasi otorisasi telah dipindahkan ke level Controller:

```php
// PurchaseOrderWebController
public static function middleware(): array
{
    return [
        new Middleware('require.permission:purchasing.view', only: ['index', 'create', 'show', 'print']),
        new Middleware('require.permission:purchasing.manage', only: ['store', 'confirm', 'cancel', 'generateInvoice', 'destroy']),
    ];
}

// GoodsReceiptWebController
public static function middleware(): array
{
    return [
        new Middleware('require.permission:receiving.manage', only: ['create', 'store', 'instantStockIn']),
    ];
}

// SupplierInvoiceWebController
public static function middleware(): array
{
    return [
        new Middleware('require.permission:purchasing.bills', only: ['index', 'show', 'recordPayment']),
    ];
}

// PurchaseReturnWebController
public static function middleware(): array
{
    return [
        new Middleware('require.permission:purchase.returns', only: ['index', 'create', 'store', 'show', 'approve', 'complete']),
    ];
}
```

---

## 2. Proteksi Anti-Fraud & Otorisasi Pengeluaran Kas
Pengadaan adalah area paling rentan terhadap kolusi dan pengeluaran fiktif. Sistem menerapkan proteksi berlapis:
1. **Pemisahan Tugas (Segregation of Duties):**
   - Staf gudang yang memiliki izin `receiving.manage` hanya bisa mencatat barang masuk fisik. Mereka tidak memiliki izin `purchasing.bills` untuk mencatat pembayaran kas.
   - Staf keuangan yang membayar tagihan tidak bisa mengubah kuantitas penerimaan barang di gudang.
2. **Supervisor PIN Guard untuk Pengeluaran Kas $\ge \text{Rp } 5.000.000$:**
   - Pada `SupplierInvoiceWebController@recordPayment`, jika nominal pembayaran $\ge 5.000.000$ dan bisnis telah mengatur `pos_supervisor_pin`, controller memvalidasi hash PIN sebelum transaksi kas dicatat.
   - Pengujian otomatis membuktikan bahwa request tanpa PIN atau PIN salah ditolak dengan pesan kesalahan validasi.

---

## 3. Pencegahan IDOR & Isolasi Data Multi-Tenant
1. Model `PurchaseOrder`, `GoodsReceipt`, `SupplierInvoice`, dan `PurchaseReturn` menggunakan trait `BelongsToBusiness`.
2. Setiap controller melakukan verifikasi kepemilikan tenant:
   ```php
   $business = Context::requireBusiness();
   abort_unless($document->business_id === $business->id, 403);
   ```
3. Uji penetrasi otomatis pada `PurchasingModuleAuditTest` memvalidasi bahwa akses cross-tenant dicegah (mengembalikan status 403 / 404).

---

## 4. Pencatatan Jejak Audit (Audit Trail Events)

| Aksi | Level Risiko | Informasi Yang Dicatat |
| :--- | :--- | :--- |
| `po.created` | `LOW` | Nomor PO, tipe pesanan, supplier/customer, total nilai |
| `goods_receipt.created` | `LOW` | Nomor penerimaan, lokasi gudang, ID PO rujukan, jumlah item |
| `supplier_invoice.payment_recorded` | `MEDIUM` | Jumlah uang keluar, metode pembayaran, nomor faktur supplier |
| `purchase_return.created` | `LOW` | Nomor retur, alasan retur, ID penerimaan barang asal |
| `purchase_return.approved` | `MEDIUM` | Otorisasi persetujuan retur fisik |
| `purchase_return.completed` | `MEDIUM` | Penyelesaian retur dan pemotongan stok |
