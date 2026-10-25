# Deployment and Rollback Plan - customers

> Modul: Pelanggan (Customers Master Directory) | Tanggal: 2026-10-25

## 1. Prosedur Deployment (Rilis Produksi)

1. **Pre-Deployment Check:**
   - Pastikan cabang Git berada pada posisi terbersih dari konflik:
     ```bash
     git status
     ```
   - Jalankan seluruh rangkaian test Customer untuk memastikan tidak ada regresi:
     ```bash
     php artisan test tests/Feature/CustomerWebFeatureTest.php tests/Feature/CustomerModuleAuditTest.php
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
   - Kunjungi `/customers` dan pastikan ringkasan hero bento serta tabel pelanggan tampil normal.
   - Buka modal Tambah Pelanggan dan pastikan formulir dapat dibuka dengan lancar.
   - Uji coba ekspor file Excel melalui `/customers/export` dan pastikan file `.xlsx` dapat diunduh tanpa error.

## 2. Prosedur Rollback (Jika Ditemukan Anomali Kritis)

1. **Rollback Kode:**
   - Kembalikan commit ke baseline sebelumnya (`git revert HEAD` atau `git reset --hard 38cdcdf`).
2. **Pembersihan Cache:**
   - Jalankan `php artisan optimize:clear` dan `php artisan route:cache`.
3. **Pemberitahuan Tim Operasional:**
   - Informasikan kasir dan staf sales untuk sementara menggunakan pencatatan pelanggan manual jika terjadi downtime sistem.
