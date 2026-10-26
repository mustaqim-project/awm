# Deployment & Rollback Plan: Modul Purchasing (Pengadaan)

## 1. Rencana Deployment (Zero-Downtime)

| Urutan | Langkah / Perintah | Deskripsi & Tujuan | Durasi Estimasi |
| :---: | :--- | :--- | :---: |
| 1 | `git pull origin main` | Sinkronisasi kode controller & view purchasing yang telah diperkeras. | 10 detik |
| 2 | `php artisan config:cache` | Refresh cache konfigurasi sistem dan RBAC middleware. | 5 detik |
| 3 | `php artisan route:cache` | Refresh cache routing controller-level middleware. | 5 detik |
| 4 | `php artisan view:clear && php artisan view:cache` | Membersihkan cache view lama dan melakukan pra-kompilasi Blade baru. | 10 detik |
| 5 | `php artisan test --filter PurchasingModuleAuditTest` | Smoke testing otomatis di server staging/produksi. | 25 detik |

---

## 2. Rencana Rollback (Bila Ditemukan Anomali)

Jika terjadi kegagalan atau gangguan tak terduga pasca rilis:
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
   - Cek rute `/purchasing/purchase-orders` via web browser staf.
   - Uji pembuatan draft PO baru untuk memastikan alur normal.
