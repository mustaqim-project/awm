# Implementation Plan - approvals

> Modul: approvals | Commit diaudit: 38cdcdf | Tanggal: 2026-10-25 | Status dokumen: Final

## Rencana Implementasi Perbaikan & Hardening

### Tahap 1: Stabilisasi Baseline & Perbaikan Bug Relasi
- Menganalisis log error `tests/Feature/DocumentApprovalWorkflowTest.php`.
- Menyelaraskan struktur data `AuditLog::create` di `app/Http/Controllers/Web/PurchaseOrderWebController.php:270`.
- Memastikan suite baseline kembali lulus 100%.

### Tahap 2: Penguatan Keamanan & Controller-Level RBAC
- Mengimplementasikan `Illuminate\Routing\Controllers\HasMiddleware` pada `ApprovalWebController`.
- Mendaftarkan pemetaan permission `approvals.view` dan `approvals.manage` pada aksi controller.
- Mengamankan validasi request pada input aturan plafon dan alasan penolakan dokumen.
- Mengintegrasikan pencatatan mutasi aturan dan persetujuan ke dalam `audit_logs`.

### Tahap 3: Optimasi Kinerja Database & UI
- Merefaktor metode `inbox()` untuk meminimalkan beban query database dengan single-fetch collection filtering.
- Memverifikasi kepatuhan Bento Apple HIG, modal teleportation, touch targets, dan ketiadaan emoji pada seluruh view.

### Tahap 4: Penulisan Test Hardening Dedikasi
- Membangun file test baru `tests/Feature/ApprovalModuleAuditTest.php`.
- Menguji skenario 403/Redirect izin akses, skenario isolasi multi-tenant 404, emisi audit logs, dan validasi alasan penolakan.

### Tahap 5: Dokumentasi Lengkap & Verifikasi Akhir
- Menyusun 13 dokumen audit teknis dan 3 panduan pengguna.
- Memverifikasi tautan internal route menggunakan `verify_links.py`.
- Mencatat log pekerjaan ke `docs/AiWorkHistory.md`.
