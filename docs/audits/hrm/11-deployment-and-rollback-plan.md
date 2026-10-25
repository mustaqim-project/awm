# Prosedur Rilis & Rencana Rollback - HRM

> Modul: Sumber Daya Manusia & Penggajian (HRM & Payroll) | Tanggal: 2026-10-25

## 1. Prosedur Rilis Produksi (Deployment Steps)
Perubahan modul HRM bersifat *code-level hardening* (penguatan kontroler, proteksi formula CSV, jejak audit, dan lokalisasi view) tanpa mengubah struktur migrasi skema database yang sudah ada.

### Langkah Rilis Standar:
1. **Pull Code Terbaru:**
   ```bash
   git checkout main
   git pull origin main
   ```
2. **Optimasi Composer & Autoloader:**
   ```bash
   composer dump-autoload --optimize --no-dev
   ```
3. **Penyegaran Cache Konfigurasi & Rute:**
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
4. **Verifikasi Smoke Test:**
   ```bash
   php artisan test tests/Feature/HrmModuleAuditTest.php
   ```

---

## 2. Checklist Pengujian Cepat Pasca-Rilis (Smoke Test)
- [ ] Login sebagai Bisnis Owner: Akses rute `/hrm` dan pastikan halaman utama terbuka normal dengan kartu Bento KPI.
- [ ] Akses Uji Staf Non-Manajer: Login sebagai staf kasir dan pastikan pengalihan ke `/portal` terjadi saat mencoba membuka rute `/hrm`.
- [ ] Pengujian Ekspor CSV: Unduh berkas CSV Rekapitulasi Gaji dan pastikan berkas terunduh dengan BOM UTF-8 yang rapi dan karakter formula diawali kutip (`'`).
- [ ] Pengecekan Audit Log: Buka tabel `audit_logs` dan pastikan entri mutasi baru tercatat dengan benar.

---

## 3. Rencana Rollback (Rollback Procedure)
### Kondisi Pemicu Rollback (*Rollback Triggers*):
- Terjadinya error HTTP 500 saat owner membuka halaman `/hrm` atau memproses batch penggajian.
- Gagalnya middleware `require.permission` mengenali user yang berwenang.
- Terhentinya proses ekspor berkas Excel atau CSV.

### Langkah-Langkah Rollback Cepat:
1. Kembalikan branch ke commit stabil sebelumnya:
   ```bash
   git revert HEAD --no-edit
   git push origin main
   ```
2. Bersihkan cache aplikasi:
   ```bash
   php artisan route:clear
   php artisan view:clear
   php artisan config:clear
   ```
3. Jalankan pengujian otomatis untuk memverifikasi pemulihan:
   ```bash
   php artisan test tests/Feature/HrmAndMonthlyPayrollTest.php
   ```
