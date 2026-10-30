# Deployment & Rollback Plan   Modul Analytics
> Modul: Analytics | Baseline Commit: 38cdcdf | Tanggal: 2026-10-25 | Auditor: AI Agent (/laravel-module-audit) | Status Dokumen: Final

---

## 1. Prasyarat Deployment

1. **Akses Repositori Git:** Akses commit dan push ke branch staging/production.
2. **Lingkungan Server:**
   - PHP 8.2+ (Rekomendasi PHP 8.3)
   - MySQL 8.0+ / MariaDB 10.6+ / PostgreSQL 14+
   - Node.js & NPM untuk asset compiling jika ada perubahan CSS global (tidak diperlukan karena hanya memakai utility classes standar Tailwind).
3. **Database Migration Status:** **ZERO MIGRATION NEEDED.** Perbaikan modul Analytics murni pada layer Controller, Blade View, Language Dictionary, dan Testing. Tidak ada alterasi tabel atau perubahan skema database.

---

## 2. Urutan Langkah Deployment

```bash
# 1. Tarik perubahan terbaru dari repositori
git pull origin main

# 2. Bersihkan cache aplikasi & view
php artisan optimize:clear
php artisan view:clear
php artisan config:clear
php artisan route:clear

# 3. Cache kembali route & konfigurasi untuk performa produksi
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 4. Jalankan pengujian otomatis untuk memverifikasi integritas
php artisan test tests/Feature/AnalyticsWebTest.php

# 5. Verifikasi pendaftaran route
php artisan route:list --name=analytics
```

---

## 3. Kriteria Go / No-Go

- **GO:**
  - `php artisan test tests/Feature/AnalyticsWebTest.php` lulus 100% (9 passed).
  - Request `GET /analytics` mengembalikan status HTTP 200 dengan visualisasi Bento utuh.
  - Request `GET /analytics?format=json` mengembalikan payload JSON terstruktur.
- **NO-GO / Trigger Rollback:**
  - Muncul error 500 pada filter rentang tanggal tertentu.
  - Test otorisasi gagal mendeteksi permission.

---

## 4. Prosedur Rollback

Jika ditemukan anomali tak terduga setelah rilis:

```bash
# 1. Revert commit perubahan modul analytics
git revert <commit-hash> --no-edit

# 2. Bersihkan cache
php artisan optimize:clear

# 3. Cache ulang environment
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Waktu estimasi pemulihan (Recovery Time Objective / RTO): `< 2 Menit` tanpa risiko kehilangan data (*Zero Data Loss*).
