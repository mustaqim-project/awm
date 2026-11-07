Perbaiki tampilan antarmuka, kontrol otorisasi (RBAC), dokumentasi pemilik bisnis, dan audit kesiapan produksi pada modul `analytics`, `approvals`, dan `billing` di `resources/views/app/analytics`, `resources/views/app/approvals`, dan `resources/views/app/billing` sesuai standar sistem Cooca SaaS ERP.

Working directory: c:\laragon\www\cooca_core
Integrity mode: development

## Requirements

### R1. UI Simplification and Layout Cleanliness
Terapkan penyederhanaan antarmuka pengguna pada seluruh view Blade di `resources/views/app/analytics`, `resources/views/app/approvals`, dan `resources/views/app/billing` sesuai prinsip `/ui-simplify-layout`:
- Eliminasi tumpang tindih elemen (zero visual overlap), rapikan posisi tombol aksi (action buttons), dan pisahkan aksi utama (primary) dari aksi sekunder/tersier.
- Pastikan tata letak informasi rapi, tidak padat/cluttered, mudah dibaca, serta responsif dan berfungsi optimal baik di viewport mobile (375px-430px) maupun desktop (>= 1024px).

### R2. Role-Based Access Control (RBAC) & Controller/View Guards
Audit dan terapkan kontrol otorisasi berbasis peran (RBAC) untuk ketiga modul (`analytics`, `approvals`, `billing`) sesuai prinsip `/laravel-rbac`:
- Pastikan rute dan method controller yang menangani ketiga modul dilindungi oleh permission guard / middleware otorisasi yang sesuai di level controller.
- Amankan seluruh elemen aksi dan navigasi di dalam view Blade dengan pengecekan permission (misal `@can` atau directive otorisasi sistem), sehingga elemen aksi tidak tampak bagi pengguna tanpa izin akses.

### R3. Module Audit & Production Readiness
Lakukan audit komprehensif terhadap kesiapan produksi pada modul `analytics`, `approvals`, dan `billing` sesuai standar `/laravel-module-audit`:
- Periksa konsistensi penanganan error, validasi input, status empty state, feedback pengguna (flash alerts), dan konsistensi query data.
- Pastikan modul siap digunakan di lingkungan produksi tanpa regresi logika bisnis.

### R4. Business Owner User Documentation
Susun atau perbarui dokumentasi panduan penggunaan modul `analytics`, `approvals`, dan `billing` dari sudut pandang (POV) Business Owner / pemilik UMKM sesuai standar `/business-owner-doc-generator`:
- Tempatkan file dokumentasi pada direktori dokumentasi sistem (`resources/views/app/docs/` atau lokasi docs modul terkait).
- Tulis panduan dengan bahasa yang bersahaja dan mudah dipahami tanpa jargon teknis rumit, serta sertakan tautan langsung (link/route) ke fitur yang relevan.

## Verification Resources
- Test suite dan skrip pengujian repositori (`php artisan test`).
- Linter dan pengecekan sintaks PHP (`php -l`).

## Acceptance Criteria

### UI Simplification & Layout
- [ ] Seluruh file view Blade pada `resources/views/app/analytics`, `resources/views/app/approvals`, dan `resources/views/app/billing` bebas dari elemen yang saling bertumpuk atau overlap.
- [ ] Tombol aksi pada ketiga modul tertata dengan hierarki visual yang jelas (primary vs secondary actions) dan tidak berdesakan.
- [ ] Tampilan antarmuka pada ketiga modul tidak mengalami layout broken atau horizontal overflow yang tidak wajar pada resolusi mobile (375px) dan desktop (1280px).

### RBAC & Security
- [ ] Seluruh endpoint atau controller method terkait modul `analytics`, `approvals`, dan `billing` memiliki proteksi otorisasi / permission guard di level controller.
- [ ] Tombol aksi, link modifikasi data, dan elemen sensitif pada view Blade dibungkus pengecekan hak akses (@can / permission check) dan tersembunyi bagi pengguna yang tidak memiliki wewenang.

### Module Audit & Stability
- [ ] Seluruh file PHP dan Blade yang dimodifikasi lolos verifikasi sintaks tanpa error (`php -l`).
- [ ] Seluruh automated test suite yang relevan (`php artisan test`) berhasil dijalankan tanpa kegagalan (pass).
- [ ] Setiap view memiliki penanganan tampilan kosong (empty state) yang rapi saat data belum tersedia.

### Business Owner Documentation
- [ ] Dokumentasi panduan penggunaan untuk modul `analytics`, `approvals`, dan `billing` tersedia di direktori dokumentasi (`resources/views/app/docs/` atau lokasi docs terkait).
- [ ] Panduan ditulis dari sudut pandang pemilik bisnis dengan alur langkah yang jelas dan tautan rute ke fitur terkait.