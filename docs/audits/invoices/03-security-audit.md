# Security Audit - Modul Faktur Penjualan & Piutang (Invoices)

> Modul: Invoices & Accounts Receivable | Tanggal Audit: 2026-10-26 | Target: `resources/views/app/invoices`

## 1. Ikhtisar Keamanan & Postur Pertahanan
Audit keamanan mencakup 6 domain pertahanan:
1. **Otorisasi Controller-Level RBAC (Role-Based Access Control):** Menjamin setiap pemanggilan endpoint controller melewati validasi izin pengguna yang terdaftar di `database/seeders/RbacSeeder.php`.
2. **Perlindungan Injeksi Formula Spreadsheet (CWE-1236):** Netralisasi karakter formula dinamis pada dokumen Excel dan CSV yang diekspor.
3. **Perekaman Jejak Audit (Audit Trail & Anti-Fraud Logging):** Pencatatan permanen setiap operasi CRUD, pelunasan, konfirmasi, pembatalan, dan penghapusan ke tabel `audit_logs`.
4. **Isolasi Multi-Tenant & Perlindungan IDOR (Insecure Direct Object Reference):** Penguncian query terhadap `business_id` melalui trait `BelongsToBusiness` dan `Context::requireBusiness()`.
5. **Keamanan Formulir & Proteksi XSS (Cross-Site Scripting):** Tidak ada raw `{!! !!}` yang mengekspos data pengguna, seluruh form menggunakan `@csrf`.
6. **Integritas Pembukuan & Guard Pembatalan:** Larangan penghapusan fisik pada faktur yang telah berstatus aktif atau memiliki riwayat pembayaran.

---

## 2. Analisis Temuan Keamanan & Remediasi

### F-INV-001: Missing Controller-Level RBAC Enforcement (Severity: HIGH)
- **Kondisi Awal:** `InvoiceWebController` hanya mengandalkan route-level middleware di `routes/owner.php`. Jika rute diakses melalui grup rute lain atau di-refactor, controller rentan terpapar tanpa proteksi hak akses.
- **Dampak:** Pengguna dengan role staf tanpa izin faktur berpotensi membuat, mengubah, atau membatalkan faktur penjualan.
- **Remediasi:** Menambahkan `implements HasMiddleware` pada `InvoiceWebController` dan mendefinisikan metode `public static function middleware(): array` dengan 6 permission terperinci (`invoices.view`, `invoices.create`, `invoices.edit`, `invoices.record_payment`, `invoices.export`, `invoices.delete`).
- **Verifikasi:** Diuji via `InvoiceModuleAuditTest::test_user_without_permission_is_blocked` (berhasil diblokir dan diredirect ke portal).

### F-INV-002: Spreadsheet Formula Injection / CSV Injection CWE-1236 (Severity: HIGH)
- **Kondisi Awal:** Pada `app/Exports/InvoiceReportExport.php` dan metode `exportExcel()` fallback CSV, string masukan pengguna (seperti nomor faktur `+INV-001`, nama pelanggan `=CMD('calc.exe')`, dan termin pembayaran `-DDE(...)`) ditulis langsung ke dalam lembar kerja spreadsheet.
- **Dampak:** Saat pemilik bisnis atau akuntan membuka berkas Excel/CSV di komputer lokal, spreadsheet dapat mengeksekusi perintah eksternal (*Remote Code Execution / DDE attack*).
- **Remediasi:** Menerapkan `CsvSanitizer::sanitize()` pada setiap sel teks di `InvoiceReportExport.php` dan `CsvSanitizer::sanitizeRow()` pada baris CSV. Karakter pemicu formula (`=`, `+`, `-`, `@`, `\t`, `\r`, `\n`, `%`) otomatis diberi awalan petik tunggal (`'`).
- **Verifikasi:** Diuji via `InvoiceModuleAuditTest::test_formula_injection_defense_sanitizes_csv_export` (karakter formula diverifikasi diawali petik tunggal).

### F-INV-003: Missing Workforce & Invoice Audit Trail Logging (Severity: MEDIUM)
- **Kondisi Awal:** Operasi mutasi data pada faktur komersial (penerbitan, pembaruan draf, pelunasan, konfirmasi rilis stok, pembatalan void, dan penghapusan) tidak tercatat di tabel `audit_logs`.
- **Dampak:** Tidak ada bukti forensik jika terjadi manipulasi data piutang, pelunasan fiktif, atau pembatalan sepihak oleh kasir/staf.
- **Remediasi:** Mengintegrasikan `AuditLog::create` pada 6 titik mutasi:
  1. `store()`: `invoices.invoice_created` (Risk: LOW)
  2. `update()`: `invoices.invoice_updated` (Risk: LOW)
  3. `recordPayment()`: `invoices.payment_recorded` (Risk: MEDIUM)
  4. `confirm()`: `invoices.invoice_confirmed` (Risk: MEDIUM)
  5. `void()`: `invoices.invoice_voided` (Risk: HIGH)
  6. `destroy()`: `invoices.invoice_deleted` (Risk: HIGH)
- **Verifikasi:** Diuji via `InvoiceModuleAuditTest::test_invoice_creation_emits_audit_log`, `test_recording_payment_emits_audit_log`, dan `test_voiding_and_deleting_draft_invoice_emit_audit_logs`.

### F-INV-004: Incomplete i18n Localization pada Blade Views (Severity: MEDIUM)
- **Kondisi Awal:** Scanner mendeteksi 11 teks bahasa Indonesia yang di-hardcode tanpa fungsi translasi `__('sales.invoices.*')`.
- **Dampak:** Pengguna berbahasa Inggris melihat teks tercampur yang menurunkan reputasi platform dan membingungkan operator ekspatriat.
- **Remediasi:** Menambahkan kunci kamus simetris di `lang/id/sales.php` dan `lang/en/sales.php` serta memperbarui berkas Blade.
- **Verifikasi:** Scanner `scan_invoices_i18n.py` mengonfirmasi 5/5 berkas clean, 358 i18n calls, 0 issues.

---

## 3. Matriks Pengujian Keamanan & Validasi Otomatis
| Skenario Uji | Target Endpoint | Ekspektasi | Hasil Pengujian |
|---|---|---|---|
| Tamu tanpa login | `GET /invoices` | Redirect ke `/login` (302) | PASSED |
| Staf tanpa izin `invoices.view` | `GET /invoices` | Redirect ke `/portal` (302) | PASSED |
| Staf tanpa izin `invoices.create` | `GET /invoices/create` | Redirect ke `/portal` (302) | PASSED |
| Akses lintas tenant (IDOR) | `GET /invoices/{other_id}` | Abort 404 (Scoping `BelongsToBusiness`) | PASSED |
| Sunting faktur tenant lain | `GET /invoices/{other_id}/edit` | Abort 404 | PASSED |
| Hapus faktur tenant lain | `DELETE /invoices/{other_id}` | Abort 404 | PASSED |
| Payload Formula Injection | `GET /invoices/export-excel?format=csv` | Disanitasi dengan awalan `'` | PASSED |
| Hapus faktur non-draf | `DELETE /invoices/{issued_id}` | Ditolak dengan pesan kesalahan | PASSED |
| Pencatatan AuditLog mutasi | Semua aksi CRUD & Void | Tercatat di `audit_logs` | PASSED |
