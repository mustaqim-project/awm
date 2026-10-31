# Original User Request

## Initial Request — 2026-10-31T14:29:43Z

Lakukan perbaikan UI/UX dan arsitektur antarmuka pada view di resources/views/app berdasarkan hasil temuan audit dan roadmap remediasi 4 fase pada docs/UI_UX_AUDIT_REPORT.md.

Working directory: c:\laragon\www\cooca_core
Integrity mode: development

## Requirements

### R1. Remediation Scope and Execution
Laksanakan seluruh 4 fase remediasi Bab 7 pada `docs/UI_UX_AUDIT_REPORT.md` secara menyeluruh pada target view di `resources/views/app`:
- **Fase 1 (Quick Wins)**: Eliminasi redundansi Double Header (44 views) antara `headerTitle` sticky topbar dan komponen `<x-module-header>`, serta perbaiki arsitektur modal backdrop wrapper (76 lokasi di 21 views) agar tidak memicu pemotongan dialog dan kebocoran z-index.
- **Fase 2 (Modal Standardization & Table Action Consolidation)**: Standarisasi formulir modal berdesakan (127 lokasi) menjadi layout responsif terstruktur (Bento 2-kolom pada layar lebar), serta konsolidasi tombol aksi baris tabel yang menumpuk menjadi dropdown aksi terpadu.
- **Fase 3 (Mobile Ergonomics & Touch Targets)**: Tingkatkan ukuran touch target tabel menjadi minimal 44px sesuai Apple HIG, tambahkan mobile bottom padding (32 views) untuk mencegah elemen dan tombol submit terpotong oleh fixed mobile bottom tab bar, serta perbaiki hardcoded unresponsive multi-column grids (23 views) dan ganti raw unicode emojis (13 views) dengan ikon sistem SVG.
- **Fase 4 (Monolith Modularization)**: Lakukan dekomposisi modular pada view monolith POS (`resources/views/app/pos/terminal.blade.php`) dan HRM (`resources/views/app/hrm/index.blade.php`) menjadi sub-komponen terpisah yang terisolasi dan mudah dipelihara.

### R2. Preserving Existing Functionality and Scope
Terapkan perubahan pada seluruh target view di `resources/views/app` yang tercatat dalam laporan audit tanpa pengecualian file. Pertahankan seluruh fungsionalitas bisnis, route bindings, event handlers (Alpine.js / JavaScript), dan alur form submission yang ada.

### R3. Verification
Verifikasi integritas seluruh file view yang dimodifikasi menggunakan kompilasi programatik Blade (`php artisan view:cache`) dan pengecekan sintaks PHP/Blade untuk memastikan tidak ada kesalahan sintaks atau rendering template yang rusak.

## Acceptance Criteria

### Header and Modal Architecture (Fase 1)
- [ ] Seluruh 44 view yang memiliki redundansi Double Header telah dibersihkan sehingga judul hanya dirender sekali secara konsisten
- [ ] Seluruh 76 lokasi modal backdrop wrapper pada 21 view telah direfaktor dengan memisahkan overlay backdrop gelap dari container dialog (dialog tidak terpotong dan z-index tidak bocor)

### Form Modals and Table Actions (Fase 2)
- [ ] Seluruh 127 modal formulir berdesakan telah distandarisasi menggunakan layout responsif (Bento 2-kolom pada desktop/tablet)
- [ ] Tombol aksi baris tabel yang menumpuk telah dikonsolidasikan ke dalam menu dropdown aksi terpadu

### Mobile Ergonomics and Grids (Fase 3)
- [ ] Semua touch target aksi tabel memenuhi standar ukuran minimum 44px
- [ ] Seluruh 32 view yang teridentifikasi memiliki class padding bawah untuk fixed mobile bottom tab bar (misalnya `pb-20` atau `pb-24`) sehingga konten dan tombol aksi tidak tertutup navigasi bawah
- [ ] Seluruh 23 view dengan grid kolom tidak responsif telah dikonversi menjadi grid adaptif (`grid-cols-1 sm:grid-cols-2 lg:grid-cols-...`)
- [ ] Seluruh 13 view dengan raw unicode emoji telah digantikan dengan ikon SVG standar sistem

### Monolith Modularization (Fase 4)
- [ ] View monolith POS (`terminal.blade.php`) dan HRM (`hrm/index.blade.php`) telah didekomposisi menjadi sub-view modular di bawah direktori komponen masing-masing dengan alur passing data yang utuh

### Build and Integrity Verification
- [ ] Perintah `php artisan view:cache` berhasil dijalankan tanpa error sintaks maupun kompilasi Blade
- [ ] Semua interaksi Alpine.js, event click modal, submit formulir, dan parameter route tetap berfungsi normal
