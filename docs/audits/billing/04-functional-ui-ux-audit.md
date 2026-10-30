# Functional & UI/UX Audit   Modul Billing & Subscription
> Modul: Billing & Subscription | Baseline Commit: 38cdcdf | Tanggal: 2026-10-25 | Status: Final

---

## 1. Ergonomi Antarmuka & Kepatuhan Bento Apple HIG

Modul billing dievaluasi terhadap prinsip desain *Bento Apple High Interface Guidelines (HIG)*:
1. **Hierarki Visual 10 Pilar Kapasitas**:
   - Dasbor `/billing/limits` menyajikan kartu bento modular beradius sudut melengkung halus (`rounded-[20px]`).
   - Angka metrik kuota ditampilkan dengan tipografi monospaced tabular (`font-mono tabular-nums`), memastikan keterbacaan tinggi saat angka bertambah.
   - Tidak ada badge berbentuk kapsul (pill) yang membungkus nilai persentase pemakaian secara berlebihan.
2. **Touch Targets & Ergonomi Mobile**:
   - Seluruh tombol aksi utama (Upgrade Paket, Hitung Ulang Penyimpanan, Bayar Tagihan, Batalkan) memiliki tinggi sentuh minimal 44px (`min-h-[44px]`).
   - Tombol mudah dijangkau oleh ibu jari pengguna pada perangkat layar sentuh smartphone maupun tablet.
3. **Teleportasi Modal Dialog ke Body**:
   - Modal manajemen berkas penyimpanan (`Storage File Manager`) dan modal peringatan kuota (`open-quota-modal`) mengimplementasikan `<template x-teleport="body">` dengan indeks tumpukan tinggi (`z-[220]`), mencegah dialog terpotong oleh `overflow: hidden` pada container induk.

---

## 2. Dynamic Context-Aware Industry Auto-Hiding

### Permasalahan Sebelumnya:
Pemilik bisnis non-F&B (ritel kelontong, butik pakaian, bengkel motor, salon kecantikan, dan jasa laundry) mengeluhkan keberadaan indikator "Meja Kasir POS (Dine-In)" di dasbor kuota mereka. Hal ini menimbulkan kebingungan seolah sistem salah mendeteksi jenis usaha mereka.

### Remediasi yang Diterapkan:
1. **Kondisional Penuh pada View**:
   - Blok baris fitur Meja Kasir pada daftar fitur paket standard (`limits.blade.php:423-425`), tabel perbandingan matriks fitur (`limits.blade.php:851-858`), dan kartu ringkasan kuota (`limits.blade.php:1981-2008`) kini dibungkus dengan klausul:
     ```blade
     @if($business->hasDineInFeature())
     ...
     @endif
     ```
2. **Hasil Verifikasi**:
   - Ketika jenis usaha adalah `retail` atau industri jasa, indikator meja kasir hilang 100% dari antarmuka visual.
   - Ketika jenis usaha adalah `fnb_resto` dengan modul dine-in aktif, kartu meja kasir ditampilkan secara elegan dan proporsional.
   - Terbukti lulus dalam pengujian otomatis `test_dynamic_industry_auto_hiding_in_limits_view`.

---

## 3. Alur Pembayaran Tanpa Panik (*No-Panic Payment Flow*)

1. **Countdown Timer Interaktif**: Menampilkan sisa waktu 15 menit dengan warna kontras yang jelas tanpa menimbulkan kepanikan berlebih.
2. **Kemudahan Salin Nomor Pembayaran**: Tombol *One-Click Copy* dengan konfirmasi visual ("Tersalin!") untuk nomor Virtual Account bank dan kode bayar.
3. **Polling Status Realtime**: Halaman pembayaran secara otomatis memperbarui status menjadi lunas begitu dana diterima dari gateway TriPay, tanpa mengharuskan pengguna me-refresh halaman secara manual.
