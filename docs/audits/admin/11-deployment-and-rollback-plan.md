# Deployment & Rollback Plan - Modul Platform Backoffice & Superadmin (`admin`)

> Modul: `admin` | Commit Target: `a17c5ae` | Prosedur: Zero-Downtime Deployment

---

## 1. Prosedur Pra-Penerapan (Pre-Deployment Checklist)

Sebelum melakukan deployment ke server produksi (`cooca.id`), pastikan langkah verifikasi lokal berikut telah terpenuhi:
1. Seluruh 14 test suite admin lulus 100% (`vendor/bin/phpunit tests/Feature/Admin*Test.php`).
2. Tidak ada perubahan yang belum di-*stage* atau file sisa scratch yang tidak dibutuhkan di repositori.
3. Cadangkan basis data produksi (snapshot/dump) sebelum menjalankan migrasi baru.

---

## 2. Langkah-Langkah Penerapan Produksi (Deployment Steps)

Eksekusi perintah berikut secara berurutan pada server produksi:

```bash
# 1. Masuk ke direktori aplikasi
cd /var/www/cooca_core # atau c:\laragon\www\cooca_core pada lingkungan staging

# 2. Ambil perubahan kode terbaru dari branch main
git pull origin main

# 3. Instal dependensi PHP (jika ada pembaruan pustaka)
composer install --no-dev --optimize-autoloader

# 4. Jalankan migrasi basis data (aman, tanpa kehilangan data)
php artisan migrate --force

# 5. Jalankan seeder RBAC jika terdapat permission baru (idempotent)
php artisan db:seed --class=AdminRbacSeeder --force

# 6. Bersihkan dan bangun ulang cache Laravel
php artisan view:clear
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 7. Restart queue worker (jika ada antrean job asinkron)
php artisan queue:restart
```

---

## 3. Verifikasi Pasca-Penerapan (Smoke Testing & Health Checks)

Segera setelah deployment selesai, lakukan pemeriksaan cepat (*smoke test*) pada endpoint berikut:

1. **Laman Login Admin:**
   - URL: `https://cooca.id/admin/login`
   - Verifikasi: Halaman merender formulir login dengan benar, anti-zoom aktif, dan logo platform tampil jelas.
2. **Dashboard Konsol Superadmin:**
   - URL: `https://cooca.id/admin/dashboard`
   - Verifikasi: Kartu metrik Bento menampilkan omzet dan tenant aktif tanpa error database.
3. **Daftar Tenant & Gerbang Impersonasi:**
   - URL: `https://cooca.id/admin/businesses`
   - Verifikasi: Tombol *Impersonate* tampil bagi superadmin dan tabel menampilkan daftar bisnis terdaftar.
4. **Pusat Pemulihan Akun:**
   - URL: `https://cooca.id/admin/account-recoveries`
   - Verifikasi: Filter antrean (pending, approved, rejected) dapat diklik tanpa galat kueri.
5. **Diagnostik Pengaturan Platform:**
   - URL: `https://cooca.id/admin/settings`
   - Verifikasi: Tombol *Test WhatsApp* dan *Test TriPay* merespons diagnostik dengan benar.

---

## 4. Rencana Pemulihan Cepat (Rollback Plan)

Jika ditemukan anomali kritis pasca-deployment yang memengaruhi operasional platform:

### Prosedur Rollback Cepat:
```bash
# 1. Kembalikan kode sumber ke commit stabil sebelumnya
git reset --hard HEAD~1 # atau git checkout <commit-stabil-hash>

# 2. Kembalikan migrasi basis data jika ada migrasi yang bermasalah
# (Hanya jika migrasi terakhir perlu dibatalkan)
php artisan migrate:rollback --step=1 --force

# 3. Bersihkan seluruh cache aplikasi seketika
php artisan view:clear
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# 4. Bangun ulang cache konfigurasi
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Notifikasi tim engineering melalui saluran darurat
```

### Kriteria Pemicu Rollback Darurat:
- Terjadi galat HTTP 500 terus-menerus saat login admin.
- Fungsi otorisasi RBAC memblokir seluruh akun superadmin secara keliru.
- Kerusakan relasi basis data pada data tenant atau transaksi keuangan.
