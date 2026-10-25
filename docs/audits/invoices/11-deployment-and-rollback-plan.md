# Deployment & Rollback Plan - Modul Faktur Penjualan & Piutang

> Modul: Invoices & Accounts Receivable | Tanggal Audit: 2026-10-26 | Target Rilis: Lingkungan Staging & Production

## 1. Rencana Penerapan (Deployment Steps)

### Langkah 1: Pra-Penerapan (Pre-Deployment Checklist)
1. Pastikan seluruh branch kerja terintegrasi dan commit terbaru bersih.
2. Jalankan pengujian otomatis lokal:
   ```bash
   php artisan test tests/Feature/InvoiceModuleAuditTest.php
   php artisan test tests/Feature/InvoiceDraftEditAndPartiesTest.php
   ```
3. Pastikan tidak ada migrasi database tertunda yang memblokir skema tabel `invoices` atau `audit_logs`.

### Langkah 2: Penerapan ke Server (Deployment Execution)
1. Tarik perubahan kode terbaru (*git pull origin main*).
2. Jalankan optimasi konfigurasi dan cache Laravel:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
3. Jika terdapat penambahan permission baru pada lingkungan target, jalankan seeder RBAC:
   ```bash
   php artisan db:seed --class=RbacSeeder --force
   ```

### Langkah 3: Verifikasi Pasca-Penerapan (Smoke Testing)
1. Login sebagai pengguna dengan role `owner`, buka `/invoices` dan pastikan metrik kartu Bento tampil normal.
2. Lakukan simulasi pembuatan draf faktur, edit, dan verifikasi jejak audit di `/audit-logs`.
3. Lakukan pengujian ekspor Excel dan CSV untuk memastikan file terunduh bersih dan karakter formula tersanitasi.
4. Lakukan pengujian login sebagai akun kasir/gudang untuk memastikan pembatasan akses controller berfungsi sesuai permission.

---

## 2. Rencana Pemulihan / Pembatalan (Rollback Plan)

Jika ditemukan anomali kritis pasca-deployment:
1. **Rollback Kode:**
   Kembalikan commit ke posisi stabil sebelum deployment audit:
   ```bash
   git revert --no-commit <commit-hash>
   git commit -m "revert: rollback invoices module changes"
   git push origin main
   ```
2. **Pembersihan Cache:**
   ```bash
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   ```
3. **Pemberitahuan Tim & Investigasi:**
   Periksa log kesalahan aplikasi di `storage/logs/laravel.log` dan log web server untuk menentukan akar penyebab insiden.
