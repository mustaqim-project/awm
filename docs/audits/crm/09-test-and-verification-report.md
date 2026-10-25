# Test and Verification Report - crm

> Modul: CRM & Loyalty | Tanggal: 2026-10-25

## 1. Rekapitulasi Eksekusi Test

Pengujian modul CRM & Loyalty dijalankan menggunakan PHPUnit melalui runner Laravel Artisan dengan isolasi database in-memory:

| Suite Pengujian | File Uji | Jumlah Test | Assertions | Status | Durasi |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **CRM Web Baseline Test** | `tests/Feature/CrmWebFeatureTest.php` | 6 | 25 | **PASSED** | 16.5s |
| **CRM Audit & Security Test** | `tests/Feature/CrmModuleAuditTest.php` | 8 | 27 | **PASSED** | 33.7s |
| **Total Gabungan** | *2 files* | **14** | **52** | **100% PASSED** | 23.2s |

## 2. Rincian Test Case pada `CrmModuleAuditTest.php`

1. `test_guest_is_redirected_to_login`:
   - Menguji bahwa pengguna tanpa sesi autentikasi dialihkan ke rute login saat mengakses `/crm/members` dan `/crm/vouchers`.
   - *Status:* PASSED.
2. `test_permission_gating_crm_view_and_manage`:
   - Menguji bahwa staf dengan `crm.view` dapat melihat katalog member dan voucher.
   - Menguji bahwa staf tanpa `crm.manage` ditolak saat mencoba mencatat pembayaran kasbon (403 Forbidden pada JSON API) atau membuat voucher (dialihkan ke portal pada web request).
   - Menguji bahwa manajer dengan `crm.manage` berhasil membuat voucher diskon baru.
   - *Status:* PASSED.
3. `test_idor_protection_customer_points_and_credit_payment`:
   - Menguji pencegahan akses data silang tenant (IDOR).
   - Percobaan melihat riwayat poin, mencatat pembayaran kasbon, dan toggle voucher bisnis lain diblokir dengan status 404 (oleh `BusinessScope`) atau 403.
   - *Status:* PASSED.
4. `test_audit_log_created_on_credit_payment`:
   - Menguji bahwa pencatatan pelunasan kasbon pelanggan menerbitkan rekam jejak pada tabel `audit_logs` dengan aksi `customer.credit_payment_recorded`.
   - *Status:* PASSED.
5. `test_audit_log_created_on_voucher_create_and_toggle`:
   - Menguji bahwa pembuatan voucher dan toggle status voucher menerbitkan `AuditLog` dengan aksi `voucher.created` dan `voucher.status_toggled`.
   - *Status:* PASSED.
6. `test_credit_payment_cannot_exceed_current_balance`:
   - Menguji bahwa pembayaran kasbon yang melebihi sisa saldo piutang pelanggan ditolak oleh validasi sistem dan saldo tidak berubah.
   - *Status:* PASSED.
7. `test_credit_payment_fails_when_balance_zero`:
   - Menguji bahwa pelanggan dengan saldo kasbon nol tidak dapat menerima input pelunasan kasbon.
   - *Status:* PASSED.
8. `test_voucher_code_uniqueness_scoped_to_business`:
   - Menguji bahwa duplikasi kode voucher pada bisnis yang sama ditolak oleh validasi form.
   - *Status:* PASSED.

## 3. Verifikasi Tautan Panduan Pengguna (Link Verification)
- Skrip: `verify_links.py` terhadap `routes.json`
- Hasil: `checked 18 internal links in 3 files; problems: 0` (100% Valid).
