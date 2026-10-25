# Implementation Plan - crm

> Modul: CRM & Loyalty | Tanggal: 2026-10-25

## 1. Rencana Tindakan Perbaikan (Action Plan)

### Langkah 1: Penguatan Controller-Level RBAC
- File: `app/Http/Controllers/Web/Crm/CrmWebController.php`
- Tindakan:
  - Mengimplementasikan interface `Illuminate\Routing\Controllers\HasMiddleware`.
  - Mendefinisikan static method `middleware()` untuk memetakan permission `crm.view` pada method baca dan `crm.manage` pada method mutasi.

### Langkah 2: Integrasi Audit Trail Voucher
- File: `app/Http/Controllers/Web/Crm/CrmWebController.php`
- Tindakan:
  - Menyematkan `AuditLog::create` pada method `storeVoucher` (`voucher.created`).
  - Menambahkan dependency `Request $request` pada signature method `toggleVoucher`.
  - Menyematkan `AuditLog::create` pada method `toggleVoucher` (`voucher.status_toggled`).

### Langkah 3: Standardisasi Ergonomi Sentuh Apple HIG
- File: `resources/views/app/crm/vouchers.blade.php`
- Tindakan:
  - Mengubah tombol pemicu "Buat Voucher Baru" menjadi `h-11 min-h-[44px]`.
  - Mengubah tombol toggle status kartu voucher menjadi `w-full h-11 min-h-[44px] sm:h-10 rounded-[12px]`.

### Langkah 4: Pembuatan Test Suite Audit Komprehensif
- File: `tests/Feature/CrmModuleAuditTest.php`
- Tindakan:
  - Menguji proteksi guest redirect login.
  - Menguji permission gating `crm.view` vs `crm.manage` untuk web dan JSON API.
  - Menguji perlindungan multi-tenant IDOR (404/403).
  - Menguji penerbitan `AuditLog` pada pelunasan kasbon dan siklus hidup voucher.
  - Menguji batasan saldo kasbon (tidak boleh bayar melebihi saldo atau saat saldo nol).
  - Menguji keunikan kode voucher per tenant.

### Langkah 5: Dokumentasi Audit & Panduan Pengguna
- Penyusunan 13 berkas audit komprehensif di `docs/audits/crm/`.
- Penyusunan 3 berkas panduan pengguna di `docs/user-guides/crm/` dengan tautan rute yang terverifikasi.
