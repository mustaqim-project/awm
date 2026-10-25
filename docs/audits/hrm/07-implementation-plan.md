# Rencana Implementasi & Remediasi - HRM

> Modul: Sumber Daya Manusia & Penggajian (HRM & Payroll) | Tanggal: 2026-10-25

## 1. Ikhtisar Rencana Kerja
Rencana remedi modul HRM berfokus pada penguatan keamanan (*Security Hardening*), pencegahan injeksi formula spreadsheet (*Formula Injection Defense*), pencatatan jejak audit (*Audit Trail Logging*), dan penyempurnaan lokalisasi (*i18n Localization*) tanpa mengubah alur logika bisnis atau event handler yang sudah berjalan.

## 2. Tahapan Eksekusi
### Fase 1: Penguatan Keamanan & Otorisasi Controller-Level RBAC
- Mengimplementasikan `HasMiddleware` pada `HrmWebController.php`.
- Memetakan permission `require.permission:users.view` untuk seluruh aksi pembacaan dan ekspor data.
- Memetakan permission `require.permission:users.manage` untuk seluruh aksi mutasi (staf, kasbon, penggajian, lokasi, shift).

### Fase 2: Pertahanan Formula Injection (CWE-1236)
- Menambahkan sanitasi `CsvSanitizer::sanitize()` pada metode `exportPayrollCsv` dan `exportPayrollBankCsv` di `HrmWebController.php`.
- Menambahkan sanitasi pada metode `exportEmployees` dan `exportAttendance` di `DataExchangeService.php`.
- Menambahkan sanitasi pada lembar kerja Excel di `PayrollTwoPartExcelExport.php`.

### Fase 3: Pencatatan Jejak Audit Ketenagakerjaan
- Mengintegrasikan `AuditLog::create` pada 9 titik mutasi data sensitif di `HrmWebController.php`:
  - Pendaftaran staf, pembaruan profil staf, penghapusan staf.
  - Pencatatan kasbon, pembatalan kasbon.
  - Pembuatan batch penggajian, persetujuan batch, pembayaran batch, penghapusan draf.

### Fase 4: Lokalisasi & Kepatuhan Apple HIG
- Mengekstraksi 14 string hardcoded placeholder modal form di `resources/views/app/hrm/index.blade.php`.
- Menambahkan kunci terjemahan simetris pada `lang/id/hrm.php` dan `lang/en/hrm.php`.
- Memverifikasi touch targets (>= 36-44px) dan kebijakan Zero Emoji (100% Lucide SVG icons).

### Fase 5: Pengujian Otomatis & Verifikasi Menyeluruh
- Mengembangkan test suite komprehensif `tests/Feature/HrmModuleAuditTest.php` (7 tests, 29 assertions).
- Menjalankan kembali seluruh test suite HRM yang sudah ada (`HrmTwoPartExcelExportTest` dan `HrmAndMonthlyPayrollTest`) untuk memastikan tidak terjadi regresi (*zero regression*).
- Menjalankan skrip validasi statis (`scan_i18n.py`, `scan_hrm_emojis.py`, `scan_hrm_touch_targets.py`, `scan_hrm_security.py`).
