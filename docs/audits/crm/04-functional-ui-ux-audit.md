# Functional and UI/UX Audit - crm

> Modul: CRM & Loyalty | Tanggal: 2026-10-25 | Standar: Apple Human Interface Guidelines & Responsive Bento

## 1. Evaluasi Tampilan Blade & Kepatuhan Apple HIG

### A. Tampilan Member & Loyalitas (`resources/views/app/crm/members.blade.php`)
1. **Struktur Bento Grid & Tipografi:**
   - Ringkasan statistik (Total Member, Total Poin Aktif, Total Kasbon Belum Lunas) disajikan dalam kartu bento yang bersih dengan visual hierarchy yang jelas.
   - Status badge tier member (Bronze, Silver, Gold, Platinum) menggunakan warna semantik yang kontras dan mudah dibaca tanpa emoji berlebihan.
2. **Modal Interaktif Teleportation:**
   - Modal Riwayat Poin dan Modal Bayar Kasbon dibungkus menggunakan `<template x-teleport="body">` dengan layering `z-[200]` dan `backdrop-blur-md`.
   - Mengeliminasi bug z-index clipping atau terpotong oleh parent overflow container.
3. **Ergonomi Sentuh (Touch Targets):**
   - Tombol-tombol aksi pada tabel baris member memiliki target ketuk minimum 44px (`h-11 min-h-[44px]` pada mobile).

### B. Tampilan Voucher Promosi (`resources/views/app/crm/vouchers.blade.php`)
1. **Modal Form Pembuatan Voucher:**
   - Tombol pemicu "Buat Voucher Baru" telah ditingkatkan menjadi `h-11 min-h-[44px]` memenuhi standar Apple HIG untuk perangkat layar sentuh kasir POS.
   - Form pembuatan voucher menyajikan opsi tipe diskon (Tetap Rp vs Persentase %) dengan penjelasan limit potongan yang intuitif.
2. **Kartu Voucher & Tombol Status:**
   - Tombol toggle status keaktifan voucher pada kartu voucher telah diperbaiki menjadi `w-full h-11 min-h-[44px] sm:h-10 rounded-[12px]`.
   - Memberikan umpan balik visual instan (hijau untuk Aktif, abu-abu untuk Nonaktif) dengan transisi mulus.
3. **Penyaringan & Responsivitas:**
   - Tampilan beradaptasi dari layout kartu 1 kolom pada ponsel, 2 kolom pada tablet, hingga grid 3 kolom pada layar monitor kasir/desktop.
