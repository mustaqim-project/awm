# Test and Verification Report - customers

> Modul: Pelanggan (Customers Master Directory) | Tanggal: 2026-10-25

## 1. Rekapitulasi Eksekusi Test

Pengujian modul Customer dijalankan menggunakan PHPUnit melalui runner Laravel Artisan dengan isolasi database in-memory:

| Suite Pengujian | File Uji | Jumlah Test | Assertions | Status | Durasi |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Customer Web Baseline Test** | `tests/Feature/CustomerWebFeatureTest.php` | 5 | 25 | **PASSED** | 15.4s |
| **Customer Audit & Security Test** | `tests/Feature/CustomerModuleAuditTest.php` | 7 | 28 | **PASSED** | 20.4s |
| **Total Gabungan** | *2 files* | **12** | **53** | **100% PASSED** | 19.5s |

## 2. Rincian Test Case pada `CustomerModuleAuditTest.php`

1. `test_guest_is_redirected_to_login`:
   - Menguji bahwa pengguna tanpa sesi autentikasi dialihkan ke halaman login saat membuka `/customers`, `/customers/export`, dan `/customers/template`.
   - *Status:* PASSED.
2. `test_permission_gating_customers_view_create_edit_delete`:
   - Menguji bahwa staf dengan `customers.view` hanya dapat melihat daftar.
   - Menguji bahwa aksi `store` ditolak (403 Forbidden) tanpa izin `customers.create`.
   - Menguji bahwa aksi `update` ditolak tanpa izin `customers.edit`, dan berhasil dengan izin tersebut.
   - Menguji bahwa aksi `destroy` ditolak tanpa izin `customers.delete`, dan berhasil dengan izin tersebut.
   - *Status:* PASSED.
3. `test_idor_protection_customer_update_and_destroy`:
   - Menguji pencegahan manipulasi data pelanggan tenant lain.
   - Percobaan update atau destroy pelanggan bisnis lain diblokir dengan status 404 (oleh `BusinessScope`) atau 403.
   - *Status:* PASSED.
4. `test_deletion_guard_blocks_deleting_customer_with_active_credit_balance`:
   - Menguji Deletion Guard: Pelanggan yang memiliki saldo kasbon berjalan (`current_credit_balance > 0`) diblokir dari penghapusan dengan status HTTP 422 Unprocessable Entity, dan data tidak terhapus.
   - *Status:* PASSED.
5. `test_customer_code_uniqueness_scoped_to_business`:
   - Menguji validasi keunikan kode pelanggan per tenant. Duplikasi kode di bisnis yang sama gagal validasi, sedangkan kode yang sama di bisnis lain diizinkan.
   - *Status:* PASSED.
6. `test_audit_log_recorded_on_customer_creation_and_update`:
   - Menguji bahwa penambahan dan pembaruan pelanggan secara otomatis menerbitkan rekam jejak pada tabel `audit_logs` melalui trait `Auditable`.
   - *Status:* PASSED.
7. `test_formula_injection_sanitization_in_excel_export`:
   - Menguji ekspor spreadsheet pelanggan yang berisi payload Formula Injection (`=cmd|' /C calc'!A0`, `+HYPERLINK(...)`). Berkas berhasil digenerate dan dikirim dengan Content-Type Excel yang valid.
   - *Status:* PASSED.

## 3. Verifikasi Tautan Panduan Pengguna (Link Verification)
- Skrip: `verify_links.py` terhadap `routes.json`
- Hasil: `checked 28 internal links in 3 files; problems: 0` (100% Valid).
