# Implementation Plan - feedback

> Modul: Platform Feedback & Bug Support | Commit: 38cdcdf | Tanggal: 2026-10-25

## 1. Tujuan Implementasi
Meningkatkan kematangan arsitektur, keamanan, keandalan audit trail, serta kepatuhan ergonomi Apple HIG pada modul Platform Feedback & Bug Support (`resources/views/app/feedback/`).

## 2. Rencana Eksekusi Bertahap
### Tahap 1: Penguatan Controller-Level RBAC & Otorisasi
- Menambahkan kontrak `Illuminate\Routing\Controllers\HasMiddleware` dan `Illuminate\Routing\Controllers\Middleware` pada `FeedbackWebController`.
- Mendeklarasikan method statis `middleware()` dengan mengembalikan `require.role:owner`.

### Tahap 2: Integrasi Audit Trail Logging
- Menambahkan pembuatan entitas `App\Models\AuditLog` pada metode:
  - `storeBug`: Mencatat aksi `feedback.bug_reported` beserta metadata judul dan tingkat keparahan (*severity*).
  - `storeFeature`: Mencatat aksi `feedback.feature_requested` beserta metadata judul usulan.

### Tahap 3: Harmonisasi Ergonomi Antarmuka (Apple HIG Touch Targets)
- Memperbarui tombol aksi utama dan navigasi pada:
  - `resources/views/app/feedback/index.blade.php`: Tombol buat tiket disesuaikan ke `h-11 min-h-[44px]`.
  - `resources/views/app/feedback/show.blade.php`: Tombol kembali dan tombol aksi atas disesuaikan ke `h-11 min-h-[44px]`.

### Tahap 4: Pembuatan Automated Test Suite Khusus Audit
- Menuliskan `tests/Feature/FeedbackModuleAuditTest.php` untuk memvalidasi:
  1. Redireksi guest ke login.
  2. Pemblokiran peran non-owner dengan status 403 Forbidden.
  3. Akses index bugs dan features oleh owner.
  4. Penyimpanan bug report dan keberadaan record `AuditLog`.
  5. Penyimpanan feature request dan keberadaan record `AuditLog`.
  6. Proteksi multi-tenant IDOR.
  7. Penolakan file attachment script berbahaya.

### Tahap 5: Dokumentasi Lengkap & User Guides
- Menuliskan 3 panduan pengguna POV Business Owner (`index.md`, `troubleshooting.md`, `faq.md`) dan memvalidasi rute dengan `verify_links.py`.
