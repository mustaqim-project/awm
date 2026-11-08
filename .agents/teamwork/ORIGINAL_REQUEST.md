# Original User Request

## Initial Request — 2026-11-08T06:39:01Z

Audit, perbaiki, dan standardisasi modul Feedback (c:\laragon\www\cooca_core\resources\views\app\feedback) dan Finance (c:\laragon\www\cooca_core\resources\views\app\finance) pada aplikasi COOCA Core untuk kesiapan production, mencakup penyederhanaan tata letak antarmuka (UI simplification), penegakan otorisasi role & permission (RBAC) pada level controller, dan penyusunan panduan penggunaan untuk Business Owner.

Working directory: c:\laragon\www\cooca_core
Integrity mode: development

## Requirements

### R1. Module Audit & Production Readiness
Lakukan audit komprehensif pada modul Feedback dan Finance untuk mengidentifikasi celah fungsional, kepatuhan arsitektur, dan potensi error sebelum dan sesudah perbaikan, serta dokumentasikan temuan audit tersebut.

### R2. UI Layout Simplification & Clutter Elimination
Sederhanakan tata letak UI pada seluruh tampilan views modul Feedback dan Finance: hilangkan elemen yang bertumpuk atau tumpang tindih (zero overlapping elements), rapikan hierarki tombol aksi (maksimal 1 aksi primer per konteks), rapikan spasi dan padding antar komponen, serta pastikan antarmuka bersih dan mudah digunakan.

### R3. Controller-Level RBAC & Authorization
Terapkan dan pastikan seluruh controller dan rute yang menangani modul Feedback dan Finance memiliki proteksi Role-Based Access Control (RBAC) yang ketat pada level controller sesuai permission yang berlaku, serta pastikan hak akses tersebut tersinkronisasi pada seeder atau konfigurasi hak akses aplikasi.

### R4. Business Owner User Guide Documentation
Susun dokumentasi panduan penggunaan (user guideline) dari sudut pandang (POV) Business Owner untuk modul Feedback dan Finance dengan bahasa yang lugas dan mudah dipahami UMKM tanpa jargon rumit, disimpan di direktori dokumentasi standar proyek (`resources/views/app/docs/` atau `docs/`).

## Verification Resources
Gunakan test suite yang ada di proyek (`php artisan test` atau PHPUnit/Pest jika tersedia), serta jalankan pengecekan sintaks PHP (`php -l`) dan verifikasi rute (`php artisan route:list`) untuk memastikan tidak ada syntax error atau rute rusak akibat perubahan.

## Acceptance Criteria

### UI Layout Simplification
- [ ] Seluruh file views di `resources/views/app/feedback` dan `resources/views/app/finance` tidak memiliki elemen tombol atau card yang tumpang tindih (zero overlapping elements)
- [ ] Tombol tindakan pada setiap halaman modul memiliki hierarki visual yang terstruktur (aksi primer, sekunder, dan destruktif terbedakan dengan rapi)

### Authorization & RBAC
- [ ] Seluruh method controller untuk modul Feedback dan Finance memverifikasi permission pada level controller (menggunakan middleware permission atau authorization gates/policies)
- [ ] Pengguna tanpa hak akses yang sesuai (unauthorized) menerima respons 403 Forbidden ketika mencoba mengakses aksi pada modul Feedback dan Finance
- [ ] Seluruh nama permission yang digunakan terdaftar pada seeder atau konfigurasi hak akses aplikasi

### Business Owner Documentation
- [ ] Dokumen panduan pengguna untuk modul Feedback dan Finance tersedia di direktori dokumentasi proyek (`resources/views/app/docs/` atau `docs/`)
- [ ] Dokumen memuat alur kerja praktis dan instruksi penggunaan modul Feedback dan Finance yang ditulis dari sudut pandang Business Owner

### Code Quality & Verification
- [ ] Semua file PHP dan Blade yang dimodifikasi atau dibuat lolos pemeriksaan sintaks (`php -l`) tanpa error
- [ ] Perintah `php artisan route:list` berjalan sukses tanpa error rute rusak


## Follow-up — 2026-11-08T14:18:28Z

Server telah di-restart. Silakan lanjutkan pengerjaan tugas Feedback dan Finance modules production readiness sesuai rencana (Phase 0 Discovery -> Phase 1 -> Milestones). Lakukan pengecekan status dan resume/re-dispatch subagents sesuai kebutuhan.
