# Deployment & Rollback Plan: Modul Inventory (Stok, Mutasi, Opname, & Transfer)

## 1. Rencana Deployment (Zero-Downtime)

| Urutan | Langkah / Perintah | Deskripsi & Tujuan | Durasi Estimasi |
| :---: | :--- | :--- | :---: |
| 1 | `git pull origin main` | Mengambil pembaruan controller, views, dan test suite inventory. | 10 detik |
| 2 | `php artisan config:cache` | Refresh cache konfigurasi dan service provider. | 5 detik |
| 3 | `php artisan route:cache` | Refresh cache rute dan controller middleware. | 5 detik |
| 4 | `php artisan view:clear && php artisan view:cache` | Kompilasi ulang template Blade inventory berstandar Apple HIG. | 10 detik |
| 5 | `php artisan test --filter InventoryModuleAuditTest` | Menjalankan smoke test otomatis di server staging/produksi. | 35 detik |

---

## 2. Rencana Rollback (Bila Ditemukan Anomali)

Jika terjadi anomali atau kegagalan tak terduga pasca deployment:
1. **Rollback Source Code:**
   ```bash
   git revert HEAD --no-edit
   ```
2. **Clear & Refresh Cache:**
   ```bash
   php artisan route:clear
   php artisan view:clear
   php artisan config:clear
   php artisan optimize
   ```
3. **Verifikasi Operasional:**
   - Buka `/inventory/stocks` dan uji penyesuaian stok kecil di outlet pengujian.
