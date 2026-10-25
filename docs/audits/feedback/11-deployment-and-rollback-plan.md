# Deployment and Rollback Plan - feedback

> Modul: Platform Feedback & Bug Support | Commit: 38cdcdf | Tanggal: 2026-10-25

## 1. Prosedur Rilis ke Lingkungan Produksi (Deployment Steps)
Perubahan modul feedback berfokus pada lapisan Controller, View, dan Test Suite tanpa perubahan skema database migration baru:

1. **Pre-Deployment Check:**
   - Jalankan seluruh automated test suite:
     ```bash
     php artisan test tests/Feature/FeedbackWorkflowTest.php tests/Feature/FeedbackModuleAuditTest.php
     ```
   - Pastikan status 100% green (11 passed).
2. **Deploy Codebase:**
   - Tarik perubahan kode terbaru dari branch `main`.
3. **Optimasi Cache Laravel:**
   - Jalankan perintah pembersihan dan kompilasi ulang konfigurasi:
     ```bash
     php artisan config:cache
     php artisan route:cache
     php artisan view:cache
     ```
4. **Post-Deployment Verification:**
   - Lakukan uji coba login sebagai akun Owner dan akses `/feedback/bugs` serta `/feedback/features`.
   - Pastikan tiket yang diunggah tercatat di tabel `audit_logs`.

## 2. Prosedur Rollback (Rollback Steps)
Jika terjadi kendala tak terduga di lingkungan produksi:
1. Revert commit perubahan terkait `FeedbackWebController.php` dan tampilan view.
2. Bersihkan cache:
   ```bash
   php artisan route:clear
   php artisan view:clear
   php artisan config:clear
   ```
3. Verifikasi kembali alur sistem normal.
