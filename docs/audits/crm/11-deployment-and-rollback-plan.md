# Deployment and Rollback Plan - crm

> Modul: CRM & Loyalty | Tanggal: 2026-10-25

## 1. Prosedur Deployment (Rilis Produksi)

1. **Pre-Deployment Check:**
   - Pastikan cabang Git berada pada posisi terbersih dari konflik:
     ```bash
     git status
     ```
   - Jalankan seluruh rangkaian test CRM untuk memastikan tidak ada regresi:
     ```bash
     php artisan test tests/Feature/CrmWebFeatureTest.php tests/Feature/CrmModuleAuditTest.php
     ```

2. **Eksekusi Deployment:**
   - Tarik perubahan kode terbaru ke server produksi (`git pull origin main`).
   - Bersihkan dan perbarui cache aplikasi:
     ```bash
     php artisan route:clear
     php artisan view:clear
     php artisan config:clear
     php artisan route:cache
     php artisan view:cache
     ```

3. **Post-Deployment Smoke Test:**
   - Masuk sebagai Owner: Kunjungi `/crm/members` dan pastikan ringkasan bento serta daftar pelanggan tampil normal.
   - Buka `/crm/vouchers` dan verifikasi formulir pembuatan voucher dapat dibuka dengan modal bento yang rapi.
   - Uji coba ubah status satu voucher (Aktif / Nonaktif) dan pastikan baris `audit_logs` baru terbentuk di database.

## 2. Prosedur Rollback (Jika Ditemukan Anomali Kritis)

1. **Rollback Kode:**
   - Kembalikan commit ke baseline sebelumnya (`git revert HEAD` atau `git reset --hard 38cdcdf`).
2. **Pembersihan Cache:**
   - Jalankan `php artisan optimize:clear` dan `php artisan route:cache`.
3. **Pemberitahuan Tim Operasional:**
   - Informasikan kasir untuk sementara melakukan pencatatan kasbon manual jika terjadi downtime sistem.
