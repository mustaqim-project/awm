# Deployment and Rollback Plan - approvals

> Modul: approvals | Commit diaudit: 38cdcdf | Tanggal: 2026-10-25 | Status dokumen: Final

## Rencana Deployment (Zero-Downtime Safe)

### 1. Pra-Deployment
1. Pastikan seluruh migration database terkait `approval_requests`, `approval_rules`, dan `approval_logs` sudah ter-apply di production:
   ```bash
   php artisan migrate --status
   ```
2. Pastikan permission seeder telah termutasi di environment production:
   ```bash
   php artisan db:seed --class=RbacSeeder --force
   ```

### 2. Eksekusi Deployment
1. Tarik pembaruan kode Git commit:
   ```bash
   git pull origin main
   ```
2. Bersihkan dan segarkan cache konfigurasi serta rute:
   ```bash
   php artisan optimize:clear
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

### 3. Verifikasi Pasca-Deployment (Smoke Testing)
1. Akses halaman `/approvals` dengan akun peran Supervisor/Manager. Pastikan kotak masuk menampilkan tiket pending dan tab counts dengan benar.
2. Akses halaman `/settings/approval-rules` dengan akun Owner. Pastikan modal penambahan dan pengubahan aturan dapat terbuka dengan modal teleport yang mulus.
3. Lakukan 1 kali uji persetujuan dan 1 kali uji penolakan dokumen draf transaksi. Periksa bahwa entri tercatat di `audit_logs`.

## Rencana Rollback
Jika terdeteksi anomali kritis pada saat deployment:
1. Revert commit perubahan:
   ```bash
   git revert --no-edit HEAD
   ```
2. Segarkan cache Laravel:
   ```bash
   php artisan optimize:clear
   php artisan route:cache
   php artisan view:cache
   ```
3. Tidak diperlukan rollback skema database karena tidak ada kolom yang dihapus atau diubah secara destruktif.
