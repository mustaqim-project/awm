# Implementation Plan - finance

> Modul: Keuangan & Akuntansi Korporasi | Commit diaudit: 38cdcdf | Tanggal: 2026-10-25 | Status dokumen: Final

## 1. Rencana Strategis Audit & Perbaikan (Mode: audit+fix)

Proses audit dan penguatan modul Keuangan & Akuntansi dieksekusi melalui 7 fase bertahap dengan prinsip non-destruktif (*Zero Regression Rule*):

### Fase 1: Discovery & Inventarisasi Sistem
- Mengidentifikasi seluruh 7 Web Controller di `app/Http/Controllers/Web/Finance/`.
- Memetakan rute keuangan pada `routes/web.php` dan `routes/owner.php`.
- Menelaah model data Eloquent, relasi database, dan cakupan test suite yang ada.

### Fase 2: Penguatan Otorisasi Controller-Level RBAC
- Mengimplementasikan `HasMiddleware` pada:
  1. `AccountingWebController`
  2. `CashLedgerWebController`
  3. `PosFinanceWebController`
  4. `PaymentSettlementWebController`
  5. `StoreEdcTerminalWebController`
  6. `MerchantPayoutAccountWebController`
  7. `ExternalReconciliationWebController`
- Memetakan permission granular sesuai `RbacSeeder` (`accounting.view`, `accounting.manage`, `finance.cash_bank`, `finance.receivables`, `finance.payables`, `expenses.view`, `expenses.manage`, `entitlement:export`).

### Fase 3: Sanitasi Formula Injection Spreadsheet (CWE-1236)
- Memodifikasi `app/Domain/Import/DataExchangeService.php`.
- Mengintegrasikan `CsvSanitizer::sanitize()` pada metode `exportCoa()`, `exportReceivables()`, dan `exportPayables()`.

### Fase 4: Integrasi Jejak Audit Finansial (*Audit Trail*)
- Menambahkan pencatatan `AuditLog::create` pada seluruh aksi mutasi master akun dan pencatatan beban.
- Menjamin pencatatan menangkap `business_id`, `user_id`, `ip_address`, `user_agent`, dan payload transaksi.

### Fase 5: Penyelarasan & Pembuatan Test Suite Otomatis
- Memperbaiki string assertion lama pada `FinanceAndAccountingNavigationTest.php`.
- Mengembangkan test suite komprehensif `tests/Feature/FinanceModuleAuditTest.php` yang menguji:
  - Guest authentication enforcement.
  - Role & permission blocking (403 / redirect portal).
  - Akses sah pemilik bisnis (Owner).
  - Pencatatan AuditLog pada pembuatan COA, akun kas, dan beban toko.
  - Isolasi data multi-tenant (mencegah IDOR).
  - Sanitasi formula injection pada ekspor CSV/Excel.

### Fase 6: Dokumentasi Panduan Pengguna POV Business Owner
- Menyusun panduan pengguna terpadu di `docs/user-guides/finance/`:
  - `index.md`: Panduan operasional dan peta rute.
  - `troubleshooting.md`: Diagnosis kendala dan eskalasi.
  - `faq.md`: Tanya jawab seputar SAK EMKM dan keamanan.
- Memverifikasi seluruh tautan rute via `verify_links.py` (0 error).

### Fase 7: Evaluasi Kesiapan Produksi (Gate Review)
- Menjalankan seluruh test suite terkait (42 tests, 339 assertions).
- Mengupdate checklist kesiapan rilis dan status gate menjadi **PASSED**.
