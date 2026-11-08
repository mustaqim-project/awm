# Original User Request

## Initial Request — 2026-11-08T18:53:48Z

Perbaiki modul HRM, Import, dan Inventory pada views c:\laragon\www\cooca_core\resources\views\app\hrm, c:\laragon\www\cooca_core\resources\views\app\import, dan c:\laragon\www\cooca_core\resources\views\app\inventory dengan penyederhanaan tata letak UI, penegakan kontrol akses RBAC di tingkat controller, penyusunan dokumentasi Business Owner, serta audit modul menyeluruh.

Working directory: c:\laragon\www\cooca_core
Integrity mode: development

## Requirements

### R1. UI Layout Simplification and Decluttering
Sederhanakan tata letak UI dan rapikan penataan komponen pada views modul HRM (resources/views/app/hrm), Import (resources/views/app/import), dan Inventory (resources/views/app/inventory). Hilangkan elemen atau tombol yang bertabrakan/tumpang tindih, optimalkan hierarki tombol aksi, dan pastikan layout bersih serta responsif pada tampilan mobile dan desktop.

### R2. Controller-Level RBAC Enforcement
Audit dan tegakkan kontrol akses berbasis peran (RBAC) pada controller terkait modul HRM, Import, dan Inventory sehingga setiap method dan endpoint terlindungi hak akses yang sesuai. Daftarkan atau sinkronkan permission baru ke seeder/database bila terdapat permission yang belum terdaftar.

### R3. Module Audit & Production Readiness
Lakukan audit komprehensif terhadap fungsionalitas, alur data, routing, dan penanganan error pada modul HRM, Import, dan Inventory untuk memastikan kestabilan dan kesiapan produksi tanpa celah kebocoran fitur.

### R4. Business Owner Documentation
Susun atau perbarui dokumentasi panduan penggunaan (user guideline) dari sudut pandang (POV) Business Owner untuk modul HRM, Import, dan Inventory dengan bahasa yang lugas tanpa jargon teknis, mencakup panduan fitur utama dan disimpan di direktori dokumentasi terkait (resources/views/app/docs/).

## Acceptance Criteria

### UI Layout & Usability
- [ ] Halaman views pada modul HRM, Import, dan Inventory tidak lagi memiliki layout berantakan, tombol tumpang tindih, atau elemen yang saling menutupi.
- [ ] Tata letak form, tabel, dan tombol aksi tertata secara konsisten dan responsif di seluruh resolusi layar.

### Access Control & Security
- [ ] Controller yang menangani modul HRM, Import, dan Inventory memiliki deklarasi guard permission RBAC yang lengkap pada setiap aksi.
- [ ] Seluruh permission terkait terdaftar pada seeder/tabel permission, dan pengujian akses tidak menghasilkan otorisasi yang bocor atau 403/500 tidak semestinya bagi pengguna berhak.

### Documentation & Verification
- [ ] Dokumen panduan Business Owner untuk modul HRM, Import, dan Inventory tersedia di resources/views/app/docs/ dengan penjelasan alur kerja yang jelas.
- [ ] Pemeriksaan sintaks Blade dan eksekusi test suite Laravel (`php artisan test`) berhasil tanpa error fatal atau regresi.
