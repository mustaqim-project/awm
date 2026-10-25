# Functional and UI/UX Audit - customers

> Modul: Pelanggan (Customers Master Directory) | Tanggal: 2026-10-25 | Standar: Apple Human Interface Guidelines & Responsive Bento

## 1. Evaluasi Tampilan Blade & Kepatuhan Apple HIG

### A. Tampilan Direktori Pelanggan (`resources/views/app/customers/index.blade.php`)
1. **Hero Bento Metrics Dashboard:**
   - Bagian atas halaman menampilkan 5 kartu metrik bento yang elegan:
     - Total Pelanggan Aktif
     - Klien Korporat B2B
     - Rata-rata Tempo Pembayaran (*Net Days*)
     - Saldo Piutang Berjalan (*Receivables*)
     - Total Poin Loyalitas Terbit
   - Menggunakan warna semantik terkalibrasi tanpa emoji murahan (*anti-slop*).
2. **Pencarian & Filter Multifaktor:**
   - Kolom pencarian responsif mendukung pencarian nama, telepon, email, kode, nomor plat kendaraan, dan nama perusahaan secara terpadu.
   - Dilengkapi deep-linking tab navigasi ke modul CRM Member dan Voucher.
3. **Modal Teleportation & Stacking Context (Zero Dark Trap):**
   - Keempat modal interaktif:
     1. Modal 1: Tambah Pelanggan Baru (`showAddModal`)
     2. Modal 2: Edit Data Pelanggan (`showEditModal`)
     3. Modal 3: Detail Lengkap & Riwayat Pelanggan (`showDetailModal`)
     4. Modal 4: Smart Import Excel Preview (`showImportModal`)
   - Seluruh modal dibungkus dalam `<template x-teleport="body">` dengan kelas `fixed inset-0 z-[200]` dan kontainer dalam `relative z-10`.
   - Mengeliminasi bug backdrop gelap yang menutupi formulir dan mencegah z-index clipping.
4. **Ergonomi Sentuh & Responsivitas Mobile:**
   - Tombol-tombol utama (Tambah Pelanggan, Impor Excel, Unduh Template, dan Ekspor) memiliki target sentuh minimum 44px (`min-h-[44px]`).
   - Pada layar sentuh ponsel, modal beralih secara mulus menjadi *Apple HIG Bottom Sheet* dengan indikator drag pill, memudahkan pengoperasian satu tangan (*thumb zone*).
