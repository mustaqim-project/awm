# Implementation Plan - Modul Faktur Penjualan & Piutang (Invoices)

> Modul: Invoices & Accounts Receivable | Tanggal Audit: 2026-10-26 | Target: `resources/views/app/invoices`

## 1. Tahapan Eksekusi Penguatan Modul (Phased Roadmap)

### Fase A: Penemuan & Pemindaian Dasar (Discovery & Baseline Scanning)
- Inventarisasi seluruh berkas views: `index.blade.php`, `create.blade.php`, `edit.blade.php`, `show.blade.php`, `print.blade.php`.
- Pemindaian celah keamanan Blade: sanitasi tag `{!! !!}`, verifikasi direktif `@csrf`, pemindaian emoji Unicode, dan verifikasi touch target minimum >= 36-44px.
- Audit lokalisasi multi-bahasa: pemindaian teks hardcoded menggunakan parser Python.

### Fase B: Remediasi Keamanan & Arsitektur (Security & Architecture Hardening)
- Implementasi `HasMiddleware` Controller-Level RBAC pada `InvoiceWebController.php`.
- Netralisasi Spreadsheet Formula Injection (CWE-1236) pada `InvoiceReportExport.php` dan metode `exportExcel()` fallback CSV menggunakan `CsvSanitizer`.
- Integrasi pencatatan jejak audit `AuditLog::create` pada 6 siklus mutasi faktur dengan pengelompokan tingkat risiko (LOW, MEDIUM, HIGH).

### Fase C: Lokalisasi & Standardisasi UI/UX (i18n & Interface Polishing)
- Penambahan 9 pasang kunci translasi simetris pada `lang/id/sales.php` dan `lang/en/sales.php`.
- Penggantian seluruh teks hardcoded pada berkas Blade `create.blade.php`, `edit.blade.php`, dan `show.blade.php`.
- Verifikasi ulang dengan `scan_invoices_i18n.py` hingga mencapai status 100% clean (0 issues).

### Fase D: Pengujian Otomatis & Verifikasi Regresi (Automated Testing)
- Pembuatan test suite komprehensif `tests/Feature/InvoiceModuleAuditTest.php` (9 tests, 32 assertions).
- Eksekusi kembali test suite regresi yang ada `tests/Feature/InvoiceDraftEditAndPartiesTest.php` (4 tests, 24 assertions).
- Verifikasi 100% kelulusan tanpa kegagalan (0 regressions, 0 errors).

### Fase E: Dokumentasi Audit & Panduan Bisnis Owner (Documentation & User Guides)
- Penyusunan 14 dokumen audit teknis lengkap di `docs/audits/invoices/`.
- Penyusunan 3 dokumen panduan pemilik bisnis di `docs/user-guides/invoices/` (`index.md`, `troubleshooting.md`, `faq.md`).
- Validasi seluruh tautan internal dokumen via script Python.
