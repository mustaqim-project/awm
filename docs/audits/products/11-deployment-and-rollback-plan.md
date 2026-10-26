# Deployment & Rollback Plan: Modul Produk

| Metadata | Spesifikasi |
| :--- | :--- |
| **Target Lingkungan** | Staging / Production |
| **Downtime Diperlukan** | **0 Detik (Zero Downtime Deployment)** |
| **Tipe Perubahan** | Kode PHP Controller, Template Blade, & Test Suite (Tidak ada perubahan skema database) |

---

## 1. Prosedur Deployment
1. **Pull Code Terverifikasi:**
   ```bash
   git pull origin main
   ```
2. **Bersihkan dan Segarkan Cache Aplikasi:**
   ```bash
   php artisan route:clear
   php artisan config:clear
   php artisan view:clear
   php artisan view:cache
   php artisan route:cache
   ```
3. **Verifikasi Smoke Test:**
   - Buka URL `/products` dan pastikan daftar katalog tampil normal.
   - Buka modal varian dan pastikan ikon Lucide SVG tampil rapi.
   - Buka `/product-categories` dan pastikan staf non-manajer tidak dapat menambah/mengubah kategori.

---

## 2. Prosedur Rollback (Jika Ditemukan Anomali)
Karena seluruh perubahan bersifat non-destruktif dan tidak mengubah struktur tabel/kolom:
1. Revert commit terakhir:
   ```bash
   git revert <commit-hash>
   ```
2. Hapus cache view dan route:
   ```bash
   php artisan view:clear
   php artisan route:clear
   php artisan view:cache
   ```
3. Pastikan aplikasi kembali ke status baseline `a17c5ae`.
