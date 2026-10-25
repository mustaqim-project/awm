# Audit Fungsional, UI/UX, & Ergonomi - HRM

> Modul: Sumber Daya Manusia & Penggajian (HRM & Payroll) | Tanggal: 2026-10-25

## 1. Kepatuhan Desain Apple HIG (Human Interface Guidelines)
Antarmuka modul HRM didesain mengikuti prinsip *clean, purposeful, and content-first* khas Apple HIG:
- **Bento Grid KPI Cards:** Empat kartu ringkasan eksekutif di bagian atas menampilkan metrik kunci (Total Staf Aktif & Status Biometrik, Total Gaji Pokok & Kepesertaan BPJS, Sisa Saldo Kasbon Berjalan, dan Riwayat Penggajian Terakhir) dengan tipografi SF Pro/Inter yang terbaca jelas.
- **Glassmorphism & Surface Elevation:** Menggunakan `backdrop-blur-md`, border halus `border-black/5 dark:border-white/10`, serta bayangan lembut `shadow-[0_4px_20px_rgba(0,0,0,0.03)]` yang tidak berlebihan.
- **Micro-Interactions & State Styling:** Setiap tombol memiliki efek *active press* `active:scale-[0.98]`, transisi hover yang halus, dan kontras warna yang nyaman untuk penggunaan intensif harian.

---

## 2. Standar Ukuran Sasaran Sentuh (*Touch Target Ergonomics*)
Sesuai panduan Apple HIG untuk perangkat layar sentuh (iPad/Tablet Kasir dan Smartphone Staf), ukuran minimum area interaktif yang dapat dijangkau jari tanpa salah pencet adalah 36px - 44px:
- **Hasil Pemindaian Script `scan_hrm_touch_targets.py`:**
  - `resources/views/app/hrm/index.blade.php`: 0 sub-36px touch targets. Tombol aksi baris tabel memiliki kelas `min-w-[36px] min-h-[36px] sm:min-w-[40px] sm:min-h-[40px]`.
  - `resources/views/app/hrm/payroll/create.blade.php`: Seluruh input gaji dan checkbox tunjangan memiliki tinggi minimum >= 42px.
  - `resources/views/app/hrm/payroll/payslip.blade.php`: Tombol kirim pesan WhatsApp dan cetak PDF memiliki tinggi `min-h-[44px]` dengan bantalan jari memadai.
  - `resources/views/app/hrm/payroll/show.blade.php`: Tombol approval dan pembayaran batch berukuran `min-h-[44px]`.

---

## 3. Kebijakan Zero Unicode Emoji (100% SVG Lucide Icons)
Emoji Unicode platform (seperti Android, iOS, Windows) memiliki render grafis yang berbeda-beda, berpotensi pecah, atau terlihat tidak profesional pada laporan bisnis.
- **Hasil Pemindaian Script `scan_hrm_emojis.py`:**
  - 4 dari 4 berkas Blade HRM diuji: **0 Unicode emoji ditemukan**.
  - Seluruh ikon visual menggunakan komponen SVG Lucide terstandarisasi (`users`, `camera`, `map-pin`, `dollar-sign`, `file-text`, `shield-check`, `calendar`, `clock`, `share-2`, `download`, `trash-2`, `edit-3`).

---

## 4. Lokalisasi Bahasa Penuh (100% i18n Localization)
Untuk mendukung bisnis UMKM multinasional dan kepatuhan sistem SaaS modern, antarmuka HRM mendukung peralihan bahasa (Bahasa Indonesia & English) tanpa ada teks yang terlewat:
- **Temuan Sebelumnya (F-HRM-004):** Terdapat 14 teks statis pada placeholder modal form staf di `index.blade.php`.
- **Tindakan Perbaikan:** Ditambahkan 14 kunci terjemahan simetris pada `lang/id/hrm.php` dan `lang/en/hrm.php`:
  - `placeholder_lat`: contoh `-6.2088`
  - `placeholder_lng`: contoh `106.8456`
  - `placeholder_email`: contoh `staf@usaha.com`
  - `placeholder_wa`: contoh `08123456789`
  - `placeholder_nik`: contoh `327101...`
  - `placeholder_npwp`: contoh `09.123...`
  - `placeholder_salary`: contoh `4500000`
  - `placeholder_daily_rate`: contoh `150000`
  - `placeholder_bank_name`: contoh `BCA / Mandiri / BRI / BNI`
- **Verifikasi Scanner `scan_i18n.py`:**
  - 4 / 4 berkas Blade bersih.
  - 1053 pemanggilan helper terjemahan (`__('hrm...')` dan `__('common...')`).
  - 0 masalah string hardcoded tersisa.

---

## 5. Responsivitas Tata Letak Multi-Breakpoint
| Breakpoint | Penyesuaian Antarmuka |
|---|---|
| **Mobile (< 640px)** | Kartu Bento tersusun vertikal 1 kolom; Tab navigasi menggunakan scrollbar horizontal; Modal form menggunakan *bottom-sheet view* penuh; Baris tabel bertransformasi menjadi kartu data ringkas. |
| **Tablet (640px - 1024px)** | Kartu Bento tersusun 2x2 kolom; Tombol aksi tabel teroptimasi untuk ketukan jari; Form wizard penggajian menampilkan dua kolom input berdampingan. |
| **Desktop (> 1024px)** | Kartu Bento 4 kolom penuh; Tabel data komprehensif dengan sticky header; Modal form terpusat dengan efek latar belakang gelap *glassmorphism*. |
