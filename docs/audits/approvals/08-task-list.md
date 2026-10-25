# Task List - approvals

> Modul: approvals | Commit diaudit: 38cdcdf | Tanggal: 2026-10-25 | Status dokumen: Final

- [x] **TSK-APP-01:** Scaffold folder dokumentasi dan panduan user menggunakan `scaffold_docs.py`.
- [x] **TSK-APP-02:** Investigasi dan perbaiki NOT NULL constraint violation `audit_logs.auditable_type` pada `PurchaseOrderWebController.php:270`.
- [x] **TSK-APP-03:** Jalankan ulang `tests/Feature/DocumentApprovalWorkflowTest.php` untuk memastikan baseline lulus 100%.
- [x] **TSK-APP-04:** Tambahkan interface `HasMiddleware` dan mapping RBAC controller-level pada `ApprovalWebController.php`.
- [x] **TSK-APP-05:** Optimasi query `ApprovalWebController::inbox()` dari 5 query menjadi 1 query in-memory collection filter.
- [x] **TSK-APP-06:** Tambahkan pencatatan `AuditLog` pada aksi approve, reject, dan mutasi approval rules (create, update, destroy).
- [x] **TSK-APP-07:** Perkuat validasi request pada `rulesStore` dan `rulesUpdate` untuk role level 1, 2, dan 3.
- [x] **TSK-APP-08:** Audit views (`inbox.blade.php`, `history.blade.php`, `rules.blade.php`) terhadap Apple HIG Bento Grid, modal teleportation (`<template x-teleport="body">`), touch targets >= 44px, dan ketiadaan emoji.
- [x] **TSK-APP-09:** Verifikasi ketersediaan dan keselarasan berkas bilingual `lang/id/approvals.php` dan `lang/en/approvals.php`.
- [x] **TSK-APP-10:** Bangun suite test dedikasi `tests/Feature/ApprovalModuleAuditTest.php` untuk RBAC, multi-tenant 404, dan audit logs.
- [x] **TSK-APP-11:** Jalankan `tests/Feature/ApprovalModuleAuditTest.php` dan verifikasi 100% kelulusan (7 tests, 29 assertions).
- [x] **TSK-APP-12:** Tulis seluruh dokumen audit teknis (00-12) dan panduan pengguna (index, troubleshooting, faq).
- [x] **TSK-APP-13:** Verifikasi keabsahan route links pada dokumentasi menggunakan `verify_links.py`.
- [x] **TSK-APP-14:** Perbarui catatan kerja di `docs/AiWorkHistory.md`.
