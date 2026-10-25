# Implementation Plan - customers

> Modul: Pelanggan (Customers Master Directory) | Tanggal: 2026-10-25

## 1. Rencana Tindakan Perbaikan (Action Plan)

### Langkah 1: Penguatan Controller-Level RBAC
- File: `app/Http/Controllers/Web/CustomerWebController.php`
- Tindakan:
  - Mengimplementasikan interface `Illuminate\Routing\Controllers\HasMiddleware`.
  - Mendefinisikan static method `middleware()` untuk memetakan permission `customers.view`, `customers.create`, `customers.edit`, `customers.delete`, dan kuota `entitlement:customer`.

### Langkah 2: Penerapan Deletion Guard pada Penghapusan Pelanggan
- File: `app/Http/Controllers/Web/CustomerWebController.php`
- Tindakan:
  - Memeriksa sisa saldo kasbon `(float) ($customer->current_credit_balance ?? 0) > 0`.
  - Mengembalikan respon HTTP 422 dengan pesan edukatif jika masih ada piutang yang belum dilunasi.
  - Mendukung respons ganda (JSON untuk AJAX/Fetch dan Redirect untuk form konvensional).

### Langkah 3: Sanitasi Formula Spreadsheet (CWE-1236)
- File: `app/Domain/Import/DataExchangeService.php`
- Tindakan:
  - Menyematkan `\App\Support\Security\CsvSanitizer::sanitize` pada nilai teks pelanggan saat diekspor ke Excel (`name`, `company_name`, `city`, `address`).

### Langkah 4: Pembuatan Dedicated Test Suite Audit
- File: `tests/Feature/CustomerModuleAuditTest.php`
- Tindakan:
  - Menguji redirect guest login.
  - Menguji permission gating `customers.view`, `create`, `edit`, `delete` (web & JSON API).
  - Menguji multi-tenant IDOR (404/403).
  - Menguji penolakan penghapusan pelanggan yang memiliki kasbon aktif (Deletion Guard).
  - Menguji keunikan kode pelanggan per bisnis.
  - Menguji rekam jejak `AuditLog` pada pembuatan dan pembaruan pelanggan.
  - Menguji pencegahan Formula Injection pada ekspor Excel.

### Langkah 5: Dokumentasi Audit & Panduan Pengguna
- Penyusunan 13 berkas audit komprehensif di `docs/audits/customers/`.
- Penyusunan 3 berkas panduan pengguna di `docs/user-guides/customers/` dengan tautan rute internal terverifikasi.
