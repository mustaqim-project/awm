# Deployment & Rollback Plan - community

> Modul: Community & Networking UMKM | Commit diaudit: 38cdcdf | Tanggal: 2026-10-25 | Status dokumen: Final

## Prasyarat & Otorisasi
- **Otorisasi:** Disetujui untuk rilis produksi oleh Tim Lead Engineering & Product Owner.
- **Environment Target:** Production (Hostinger Cloud / Shared VPS).
- **Prasyarat Server:**
  - PHP 8.2+ dengan ekstensi `gd` atau `imagick` aktif (untuk rendering gambar & barcode).
  - Kredensial Tripay Production (`TRIPAY_API_KEY`, `TRIPAY_PRIVATE_KEY`, `TRIPAY_MERCHANT_CODE`) terkonfigurasi di `.env`.

## Prosedur Backup
Sebelum memulai proses deployment rilis:
```bash
# 1. Snapshot database PostgreSQL
pg_dump -U postgres cooca_prod > backup_pre_community_$(date +%Y%m%d_%H%M%S).sql

# 2. Backup storage direktori komunitas (jika lokal)
tar -czf storage_community_backup.tar.gz storage/app/public/businesses/*/community
```

## Langkah Deployment
1. **Mode Pemeliharaan (Opsional):**
   ```bash
   php artisan down --secret="cooca-deploy-2026"
   ```
2. **Tarik Kode Terbaru:**
   ```bash
   git pull origin main
   ```
3. **Instal Dependensi:**
   ```bash
   composer install --no-dev --optimize-autoloader
   ```
4. **Migrasi Database:**
   ```bash
   php artisan migrate --force
   ```
5. **Clear & Cache Konfigurasi:**
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
6. **Aktifkan Aplikasi Kembali:**
   ```bash
   php artisan up
   ```

## Migration Plan
- Tidak ada migrasi destruktif. Seluruh tabel modul (`community_posts`, `community_events`, `community_event_registrations`, dll.) kompatibel dengan skema eksisting.

## Health Check & Go/No-Go
1. **Verifikasi Web Hook & Payment:**
   - Buka `/community/events` via browser, pastikan halaman merender daftar event tanpa error 500.
   - Buka tiket event via akun pengujian, pastikan SVG QR code dan barcode muncul sempurna.
2. **Verifikasi Keamanan:**
   - Lakukan percobaan request `POST /community/events/registrations/{id}/simulate-pay` di production; pastikan sistem mengembalikan respons `403 Forbidden`.

## Prosedur Rollback
Jika ditemukan kendala kritis tak terduga pasca-deployment:
1. Revert commit rilis:
   ```bash
   git revert HEAD --no-edit
   git push origin main
   ```
2. Re-cache konfigurasi:
   ```bash
   php artisan route:cache && php artisan view:cache
   ```
3. Pulihkan database dari snapshot jika terjadi inkonsistensi data.
