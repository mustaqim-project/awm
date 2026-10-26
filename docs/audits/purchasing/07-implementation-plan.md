# Implementation Plan: Modul Purchasing

| Metadata | Nilai |
| :--- | :--- |
| **Modul** | Purchasing |
| **Strategi** | Code-first In-place Hardening & Multi-Layer Automated Verification |
| **Status Eksekusi** | **Selesai 100% (Completed)** |

---

## 1. Rencana Tahapan Eksekusi

### Fase 1: Hardening Otorisasi Controller RBAC & Audit Trail (P0)
1. Terapkan `HasMiddleware` pada `PurchaseOrderWebController`, `GoodsReceiptWebController`, `SupplierInvoiceWebController`, dan `PurchaseReturnWebController`.
2. Integrasikan `AuditLog::create` pada:
   - Penerimaan fisik barang di gudang (`goods_receipt.created`)
   - Pembayaran faktur tagihan supplier (`supplier_invoice.payment_recorded`)
   - Pembuatan, persetujuan, dan penyelesaian retur pembelian (`purchase_return.*`).
3. Pastikan proteksi PIN Supervisor tetap aktif untuk pembayaran kas $\ge \text{Rp } 5.000.000$.

### Fase 2: Standarisasi UI/UX & Kepatuhan Apple HIG (P1)
1. Bersihkan karakter Unicode centang `✓` pada `resources/views/app/purchase-orders/print.blade.php`.
2. Tingkatkan seluruh tombol aksi baris, filter segmented control, dan tombol tambah/hapus baris menjadi `min-h-[36px]`.

### Fase 3: Pembuatan Test Suite & Otomasi Verifikasi (P1)
1. Bangun test feature `tests/Feature/PurchasingModuleAuditTest.php` (14 skenario uji).
2. Jalankan PHPUnit hingga 100% passed (0 error, 0 failure).

### Fase 4: Validasi Cache & Dokumentasi Lengkap (P2)
1. Kompilasi dan verifikasi cache Blade view (`php artisan view:cache`).
2. Tulis dokumentasi komprehensif (`docs/audits/purchasing/`) dan panduan pemilik bisnis (`docs/user-guides/purchasing/`).
