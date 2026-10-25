# Deployment & Rollback Plan - finance

> Modul: Keuangan & Akuntansi Korporasi | Commit diaudit: 38cdcdf | Tanggal: 2026-10-25 | Status dokumen: Final

## 1. Strategi Rilis Produksi (Zero-Downtime Deployment)

Seluruh perubahan kode pada audit ini berfokus pada penguatan layer Controller, Service, dan Sanitasi Data tanpa membutuhkan perubahan skema tabel database (*Zero Migration Requirement*). Oleh karena itu, deployment dapat dilakukan secara langsung tanpa waktu henti (*zero-downtime*).

### Tahapan Deployment:
1. **Pull Kode Terbaru:**
   ```bash
   git pull origin main
   ```
2. **Optimasi Autoload Composer:**
   ```bash
   composer dump-autoload --optimize
   ```
3. **Pembersihan Cache Aplikasi:**
   ```bash
   php artisan route:clear
   php artisan config:clear
   php artisan view:clear
   php artisan cache:clear
   ```
4. **Validasi Test Suite Otomatis:**
   ```bash
   php artisan test tests/Feature/FinanceModuleAuditTest.php
   ```

---

## 2. Checklist Pengujian Pasca-Rilis (*Smoke Testing*)

Lakukan pengujian cepat pada lingkungan staging/produksi menggunakan akun Owner:
- [ ] Buka halaman [Buku Kas & Bank](/finance/cash-bank) — pastikan saldo ter-render dengan benar.
- [ ] Buka halaman [Bagan Akun (COA)](/finance/chart-of-accounts) — klik tombol **Export Bagan Akun**, periksa file CSV hasil unduhan dan pastikan karakter formula aman ter-quote.
- [ ] Buat satu catatan pengeluaran kas kecil uji coba di [Beban Operasional](/finance/expenses) — pastikan saldo akun kas terpotong dan entri audit log terbit di tabel `audit_logs`.
- [ ] Uji coba akses menggunakan akun staf kasir — pastikan menu akuntansi dibatasi dan dialihkan dengan aman.

---

## 3. Rencana Rollback (Kembali ke Kondisi Sebelumnya)

Jika ditemukan anomali tak terduga pasca rilis, jalankan prosedur rollback berikut:
1. **Revert Commit:**
   ```bash
   git revert HEAD --no-edit
   git push origin main
   ```
2. **Pembersihan Ulang Cache:**
   ```bash
   php artisan route:clear
   php artisan view:clear
   php artisan config:clear
   ```
3. Karena tidak ada migrasi skema database yang dieksekusi, rollback kode akan langsung mengembalikan sistem ke kondisi stabil sebelumnya tanpa risiko hilangnya data transaksi finansial.
