# Security Audit - customers

> Modul: Pelanggan (Customers Master Directory) | Tanggal: 2026-10-25 | Auditor: Anti-Fraud & AppSec Engine

## 1. Evaluasi Otorisasi & Kontrol Akses (RBAC)
- **Controller-Level Enforcement:**
  - `CustomerWebController` menerapkan interface `Illuminate\Routing\Controllers\HasMiddleware`.
  - Static method `middleware()` secara eksplisit membatasi:
    - `require.permission:customers.view` untuk method: `index`, `export`, `downloadTemplate`.
    - `require.permission:customers.create` untuk method: `store`, `previewImport`, `executeImport`.
    - `require.permission:customers.edit` untuk method: `update`.
    - `require.permission:customers.delete` untuk method: `destroy`.
    - `entitlement:customer` untuk method kuota: `store`, `executeImport`.
  - Hasil Pengujian: Permintaan web tanpa permission dialihkan ke portal, sedangkan permintaan AJAX/JSON menerima status `403 Forbidden` (`MODULE_FORBIDDEN`).

## 2. Multi-Tenant Isolation & Perlindungan IDOR
- Model `Customer` menerapkan trait `BelongsToBusiness` dengan `BusinessScope` global.
- Jika pengguna mencoba memodifikasi atau menghapus ID pelanggan milik tenant lain via route parameter `{customer}`:
  - `BusinessScope` secara otomatis menyaring `WHERE business_id = Context::business()->id`.
  - Laravel melempar `ModelNotFoundException` (HTTP 404), mencegah *data reconnaissance*.
  - Sebagai lapisan *defense-in-depth*, controller menyematkan `abort_unless($customer->business_id === $business->id, 403)` pada `update` dan `destroy`.
- Terverifikasi 100% pada automated test `test_idor_protection_customer_update_and_destroy`.

## 3. Perlindungan Terhadap Formula / CSV Injection (CWE-1236)
- File ekspor spreadsheet rentan terhadap eksekusi formula DDE / hyperlink berbahaya jika penyerang mendaftarkan nama pelanggan berawalan `=`, `+`, `-`, atau `@`.
- **Mitigasi:** `DataExchangeService::exportCustomers` telah disisipkan `CsvSanitizer::sanitize` pada seluruh atribut teks (`name`, `company_name`, `city`, `address`).
- Nilai berbahaya otomatis diberi prefix petik tunggal (`'`), menetralkan eksekusi formula di Microsoft Excel, Apple Numbers, dan LibreOffice Calc.
- Terverifikasi 100% pada test `test_formula_injection_sanitization_in_excel_export`.

## 4. Deletion Guard (Anti-Loss Shield)
- Pelanggan yang masih memiliki sisa saldo kasbon atau piutang berjalan (`current_credit_balance > 0`) dilarang keras dihapus.
- Jika kasir atau staf mencoba menghapus pelanggan yang masih memiliki hutang, sistem mengembalikan respon 422 Unprocessable Entity dan membatalkan proses penghapusan.
- Mencegah penghapusan piutang secara sepihak dan ketidakseimbangan buku besar.
