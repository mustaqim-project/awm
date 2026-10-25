# Task List — Modul Billing & Subscription
> Modul: Billing & Subscription | Baseline Commit: 38cdcdf | Tanggal: 2026-10-25 | Status: Final

---

## 1. Daftar Tugas Audit & Remediasi

- [x] **TSK-01**: Analisis rute, controller, service, dan blade view modul billing.
- [x] **TSK-02**: Jalankan baseline automated test `BillingSubmoduleSecurityAndErgonomicsTest.php`.
- [x] **TSK-03**: Investigasi root cause kegagalan `test_dynamic_industry_auto_hiding_in_limits_view`.
- [x] **TSK-04**: Selesaikan perbaikan `aria-label` penargetan DOM pada `BillingSubmoduleSecurityAndErgonomicsTest.php` untuk memvalidasi auto-hiding meja kasir.
- [x] **TSK-05**: Implementasikan `HasMiddleware` pada `BillingAndLimitWebController` (`billing.view` & `billing.manage`).
- [x] **TSK-06**: Implementasikan `HasMiddleware` pada `SubscriptionCheckoutWebController` (`billing.view` & `billing.manage`).
- [x] **TSK-07**: Integrasikan pencatatan `AuditLog` pada aksi `recalculateStorage` dan `destroyStorageFile`.
- [x] **TSK-08**: Integrasikan pencatatan `AuditLog` pada pembuatan order langganan `store`.
- [x] **TSK-09**: Buat dedicated feature test suite `tests/Feature/BillingModuleAuditTest.php` (8 skenario uji).
- [x] **TSK-10**: Jalankan kedua test suite bersamaan: 17 test lulus 100% (64 assertions).
- [x] **TSK-11**: Tulis dokumentasi audit lengkap (00 s/d 12 dan `audit-state.json`).
- [x] **TSK-12**: Tulis buku panduan pengguna (User Guide, FAQ, Troubleshooting) dengan verifikasi rute internal.
- [x] **TSK-13**: Catat jejak audit pada `docs/AiWorkHistory.md`.
