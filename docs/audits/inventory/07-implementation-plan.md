# Implementation Plan: Modul Inventory

| Metadata | Nilai |
| :--- | :--- |
| **Modul** | Inventory (Stok, Mutasi, Opname, & Transfer) |
| **Strategi** | Code-first In-place Hardening & Multi-Layer Automated Verification |
| **Status Eksekusi** | **Selesai 100% (Completed)** |

---

## 1. Rencana Tahapan Eksekusi

### Fase 1: Hardening Otorisasi Controller RBAC & Audit Trail (P0)
1. Terapkan interface `HasMiddleware` pada `InventoryWebController` dengan deklarasi izin `inventory.view` dan `inventory.manage`.
2. Perkuat proteksi IDOR tenant assertion pada `approveAdjustment` dan `rejectAdjustment` dengan `abort_unless($adjustment->business_id === $business->id, 404);`.
3. Integrasikan `AuditLogService::log` pada:
   - Pembuatan transfer stok (`STOCK_TRANSFER_CREATED`)
   - Konfirmasi penerimaan transfer stok (`STOCK_TRANSFER_RECEIVED`)
   - Rekonsiliasi fisik opname ke stok aktif (`STOCK_OPNAME_RECONCILED`).

### Fase 2: Standarisasi UI/UX & Kepatuhan Apple HIG (P1)
1. Validasi 0 Unicode Emoji di seluruh 4 file Blade inventory (`stocks`, `movements`, `opname`, `transfers`).
2. Tingkatkan seluruh tombol aksi tabel (`openReconcile`, `openAdjust`, `openReceive`) menjadi `w-9 h-9 min-h-[36px] min-w-[36px] rounded-[9px]`.
3. Standarisasi tombol tambah/hapus item baris opname dan transfer agar tetap memiliki tinggi minimal $\ge 36\text{px}$ di seluruh resolusi.

### Fase 3: Pembuatan Test Suite & Otomasi Verifikasi (P1)
1. Bangun test feature `tests/Feature/InventoryModuleAuditTest.php` (15 skenario uji menyeluruh).
2. Jalankan PHPUnit hingga 100% passed (0 error, 0 failure).

### Fase 4: Validasi Cache & Dokumentasi Lengkap (P2)
1. Kompilasi dan verifikasi cache Blade view (`php artisan view:cache`).
2. Tulis dokumentasi komprehensif (`docs/audits/inventory/`) dan panduan pemilik bisnis (`docs/user-guides/inventory/`).
