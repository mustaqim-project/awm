# Implementation Plan — Modul Billing & Subscription
> Modul: Billing & Subscription | Baseline Commit: 38cdcdf | Tanggal: 2026-10-25 | Status: Final

---

## 1. Tahapan Eksekusi Perbaikan (Surgical Implementation)

Perbaikan modul dilakukan dalam 5 fase terukur:

### Fase 1: Identifikasi Baseline & Kegagalan Uji Awal
- Menjalankan suite pengujian `BillingSubmoduleSecurityAndErgonomicsTest.php`.
- Menganalisis kegagalan `test_dynamic_industry_auto_hiding_in_limits_view` akibat kebocoran teks Meja Kasir pada DOM dan dump translasi JavaScript global layout.
- Menyesuaikan penargetan assertion pada atribut DOM spesifik (`aria-label`).

### Fase 2: Penegakan Controller-Level RBAC
- Menambahkan `implements HasMiddleware` pada `BillingAndLimitWebController`.
- Menambahkan `implements HasMiddleware` pada `SubscriptionCheckoutWebController`.
- Mendeklarasikan pembagian izin `billing.view` untuk aksi pembacaan dan `billing.manage` untuk aksi perubahan finansial/kuota.

### Fase 3: Integrasi Anti-Fraud Audit Trail
- Menambahkan import `App\Models\AuditLog`.
- Mengintegrasikan pembuatan audit log pada:
  - `BillingAndLimitWebController@recalculateStorage` (`billing.storage.recalculated`).
  - `BillingAndLimitWebController@destroyStorageFile` (`billing.storage.file_deleted`).
  - `SubscriptionCheckoutWebController@store` (`billing.order.created`).

### Fase 4: Pembuatan Dedicated Feature Test Suite
- Membuat file pengujian baru [`tests/Feature/BillingModuleAuditTest.php`](file:///c:/laragon/www/cooca_core/tests/Feature/BillingModuleAuditTest.php).
- Menguji pengalihan tamu ke login, pemblokiran kasir tanpa permission, verifikasi akses owner, proteksi IDOR lintas-tenant pada faktur dan file storage, serta verifikasi entri `AuditLog`.

### Fase 5: Verifikasi Dokumen & Pengujian Menyeluruh
- Menjalankan kedua test suite bersamaan: 17 test lulus 100%.
- Menghasilkan seluruh dokumentasi audit dan user guide berbasis bukti kode nyata.
