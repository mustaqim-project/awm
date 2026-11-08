# Original User Request

## Approved Task Prompt — 2026-11-08T22:56:13Z

Perbaiki dan audit modul resources\views\app\invoices dan resources\views\app\labor-machines pada aplikasi COOCA untuk kesiapan produksi (production readiness), menerapkan panduan /ui-simplify-layout, /laravel-rbac, /business-owner-doc-generator, dan /laravel-module-audit.

Working directory: c:\laragon\www\cooca_core
Integrity mode: development

## Requirements

### R1. UI Simplification and Layout Optimization
Sederhanakan antarmuka pengguna pada view modul invoices (resources\views\app\invoices) dan labor-machines (resources\views\app\labor-machines). Rapikan tata letak tombol, perbaiki hierarki visual, hilangkan tumpang tindih elemen (no-overlap), dan pastikan layout responsif dan bersih sesuai panduan ui-simplify-layout.

### R2. Controller-Level RBAC and Permission Enforcement
Audit dan perketat sistem role & permission (RBAC) untuk modul invoices dan labor-machines sesuai panduan laravel-rbac. Pastikan hak akses dideklarasikan secara eksplisit di level controller (Controller-Level RBAC), seluruh permission terdaftar pada database/seeder, dan view hanya menampilkan aksi yang diizinkan untuk peran pengguna.

### R3. Module Production Readiness Audit
Lakukan audit kesiapan produksi menyeluruh untuk modul invoices dan labor-machines sesuai panduan laravel-module-audit. Pastikan tidak ada kebocoran fitur, kode bersih dari error statis, dan seluruh relasi data serta alur kerja modul berfungsi dengan benar.

### R4. Business Owner User Documentation
Buat dan perbarui panduan penggunaan sistem (user guideline) dari sudut pandang (POV) Business Owner untuk modul invoices dan labor-machines sesuai panduan business-owner-doc-generator, dengan bahasa yang jelas, mudah dipahami UMKM, serta menyertakan tautan/route fitur terkait.

## Acceptance Criteria

### UI and Layout
- [ ] Seluruh file view pada resources\views\app\invoices dan resources\views\app\labor-machines bebas dari elemen atau tombol yang tumpang tindih (overlapping).
- [ ] Tata letak tombol aksi terorganisir dengan hierarki yang jelas (primary action jelas, secondary actions terkelompok).

### RBAC and Authorization
- [ ] Seluruh aksi controller untuk modul invoices dan labor-machines memiliki pengecekan permission yang dideklarasikan di level controller.
- [ ] Seluruh permission baru atau yang diperbarui telah didaftarkan dalam seeder/konfigurasi hak akses aplikasi.
- [ ] Tombol aksi pada antarmuka (tambah, edit, hapus, cetak) dibatasi menggunakan direktif permission (@can / gate) sesuai peran pengguna.

### Verification and Documentation
- [ ] Sintaks file Blade dan PHP pada kedua modul valid tanpa syntax error saat dicek melalui linter/parser PHP (php -l).
- [ ] Test suite dan static check yang relevan di repository berjalan tanpa kegagalan.
- [ ] File panduan pengguna sudut pandang Business Owner untuk modul invoices dan labor-machines telah dibuat/diperbarui di folder dokumentasi sistem (resources/views/app/docs/ atau lokasi panduan proyek terkait).
