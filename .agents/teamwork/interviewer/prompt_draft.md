Perbaiki dan sempurnakan modul AI dan AI Studio pada `c:\laragon\www\cooca_core\resources\views\app\ai` dan `c:\laragon\www\cooca_core\resources\views\app\ai-studio` untuk kebutuhan production sistem Cooca SaaS ERP menggunakan panduan `/ui-simplify-layout`, `/laravel-rbac`, `/business-owner-doc-generator`, dan `/laravel-module-audit`.

Working directory: c:\laragon\www\cooca_core
Integrity mode: development

## Requirements

### R1. UI Simplification and Layout Cleanliness
Sederhanakan antarmuka pengguna pada seluruh view di `resources/views/app/ai` dan `resources/views/app/ai-studio` dengan menata hierarki visual, merapikan letak tombol aksi, dan mengeliminasi elemen layout yang saling tumpang tindih (overlap) sesuai prinsip `/ui-simplify-layout`. Tampilan harus rapi, terstruktur, dan responsif pada perangkat mobile maupun desktop.

### R2. Controller-Level RBAC and Authorization Enforcement
Terapkan kontrol akses berbasis peran (RBAC) pada controller terkait modul AI dan AI Studio serta sembunyikan atau amankan tombol aksi dan elemen view dari pengguna tanpa hak akses sesuai prinsip `/laravel-rbac`.

### R3. Business Owner User Documentation
Buat dokumentasi panduan penggunaan modul AI dan AI Studio dari sudut pandang (POV) pemilik bisnis / UMKM tanpa jargon rumit dan sertakan tautan ke rute/fitur terkait di direktori dokumentasi sesuai pedoman `/business-owner-doc-generator`.

### R4. Module Audit and Production Readiness
Lakukan audit komprehensif terhadap kesiapan produksi modul AI dan AI Studio sesuai standar `/laravel-module-audit`, termasuk pengecekan konsistensi alur, validasi, penanganan error, dan checklist kesiapan release.

## Acceptance Criteria

### UI and Layout
- [ ] Seluruh file view Blade pada `resources/views/app/ai` dan `resources/views/app/ai-studio` tidak memiliki tombol atau komponen antarmuka yang saling tumpang tindih (zero overlap).
- [ ] Tombol aksi utama (primary action) terpisah jelas dari aksi sekunder dan tersusun rapi pada setiap halaman view AI dan AI Studio.
- [ ] Tampilan antarmuka dapat diakses dan tertata rapi tanpa layout broken atau horizontal scrolling tidak wajar pada viewport mobile (375px-430px) dan desktop (>= 1024px).

### RBAC and Access Control
- [ ] Seluruh endpoint atau controller method terkait fitur AI dan AI Studio dilindungi oleh middleware/guard permission yang sesuai di level controller.
- [ ] Elemen aksi dan navigasi di dalam view AI dan AI Studio dibungkus dengan pengecekan permission (misal `@can` / directive otorisasi), sehingga tidak tampak oleh user yang tidak memiliki izin.

### Documentation
- [ ] File panduan pengguna untuk Business Owner untuk fitur AI dan AI Studio telah dibuat di `resources/views/app/docs/` (atau direktori dokumentasi sistem terkait).
- [ ] Panduan mendeskripsikan alur kerja penggunaan fitur dalam bahasa yang mudah dipahami serta memuat tautan rute ke fitur terkait.

### Verification and Quality
- [ ] Seluruh sintaks file PHP dan Blade yang dimodifikasi lolos verifikasi sintaks tanpa syntax error (`php -l`).
- [ ] Test suite Laravel yang relevan (`php artisan test`) berjalan dan lulus tanpa regresi.
