# Deployment & Rollback Plan   Modul Billing & Subscription
> Modul: Billing & Subscription | Baseline Commit: 38cdcdf | Tanggal: 2026-10-25 | Status: Final

---

## 1. Prosedur Deployment ke Produksi

1. **Pre-Deployment Checklist**:
   - Pastikan konfigurasi gateway TriPay di `.env` produksi telah terisi:
     - `TRIPAY_API_KEY`
     - `TRIPAY_PRIVATE_KEY`
     - `TRIPAY_MERCHANT_CODE`
     - `TRIPAY_MODE=production`
   - Pastikan worker antrean (queue worker) berjalan normal untuk pemrosesan webhook callback dan notifikasi email tagihan.
2. **Langkah Eksekusi Deployment**:
   ```bash
   git checkout main
   git pull origin main
   composer install --no-dev --optimize-autoloader
   php artisan migrate --force
   php artisan db:seed --class=RbacSeeder --force
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
3. **Post-Deployment Verification**:
   - Login sebagai akun pemilik bisnis di produksi.
   - Buka `/billing/limits` dan pastikan metrik kuota termuat tanpa error 500.
   - Lakukan uji pembuatan tagihan QRIS simulasi Rp 10.000 untuk memvalidasi gateway TriPay dan pembuatan rekaman di `AuditLog`.

---

## 2. Prosedur Rollback Cepat (Emergency Rollback)

Bila terjadi kendala kritis yang tidak terduga pada payment gateway atau controller:
1. Kembalikan kode aplikasi ke commit stabil sebelumnya:
   ```bash
   git revert HEAD --no-edit
   git push origin main
   ```
2. Bersihkan seluruh cache konfigurasi dan rute:
   ```bash
   php artisan optimize:clear
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
3. Data pada tabel `subscription_payments` dan `audit_logs` tetap aman dan tidak akan terhapus karena skema database tidak melakukan perubahan yang merusak (*zero breaking schema changes*).
