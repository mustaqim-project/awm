# Panduan Pengguna — Analitik Bisnis & Tren Pertumbuhan
> Breadcrumb: [Panduan Sistem](../../SYSTEM_GUIDE.md) › Analitik Bisnis & Tren Pertumbuhan  
> Terakhir diverifikasi terhadap route aktual: Commit `38cdcdf` | Tanggal: 2026-10-25

---

## 1. Peta Navigasi & Tautan Terverifikasi

| Fitur / Halaman | Tautan Langsung Terverifikasi | Named Route | Hak Akses (Permission) | Deskripsi |
|---|---|---|---|---|
| **Analitik Bisnis & Tren** | [/analytics](/analytics) | `analytics.index` | `reports.view` | Dashboard eksekutif Bento memantau omzet, laba kotor, jam sibuk, dan top produk. |
| **Pusat Laporan SAK EMKM** | [/reports](/reports) | `reports.index` | `reports.view` | Laporan keuangan formal (Laba Rugi, Neraca, Arus Kas). |
| **Laporan & Valuasi Stok** | [/reports?tab=stock](/reports?tab=stock) | `reports.index` | `reports.view` | Valuasi stok bahan baku & produk berdasarkan metode HPP FIFO/Average. |
| **Laba Rugi Bulanan** | [/reports?tab=income_statement](/reports?tab=income_statement) | `reports.index` | `reports.financial` | Rincian pendapatan operasional dan laba bersih resmi usaha. |
| **Dashboard Utama** | [/dashboard](/dashboard) | `dashboard` | `dashboard.view` | Ringkasan operasional harian dan pintasan aksi cepat kasir/stok. |

---

## 2. Tujuan & Sasaran Pengguna

Panduan ini ditujukan bagi **Pemilik Usaha (Business Owner)**, **General Manager**, dan **Manajer Keuangan/Operasional** UMKM Indonesia di 20 sektor industri (F&B, Ritel, Apotek, Bengkel, Laundry, Fashion, Jasa, dsb.).

Dengan modul ini, Anda dapat:
1. Memantau omzet harian/bulanan secara *real-time* dari seluruh saluran penjualan (Kasir POS, Faktur B2B, Toko Online).
2. Mengetahui margin laba kotor riil (*Gross Margin %*) dan estimasi laba bersih setelah dikurangi beban operasional.
3. Mengetahui jam sibuk antrean pelanggan di kasir (07:00–23:00) untuk optimalisasi jadwal kerja staf/barista/kasir.
4. Mengetahui 10 produk dengan volume penjualan dan sumbangan keuntungan tertinggi.
5. Membandingkan performa antar-cabang (*multi-outlet*) dan mencetak laporan eksekutif untuk rapat manajemen.

---

## 3. Langkah Cepat Menggunakan Analitik Bisnis

### Langkah 1: Memilih Periode Waktu
1. Buka menu **Laporan & Keuangan > Analitik Bisnis & Tren** atau akses langsung [/analytics](/analytics).
2. Pada bagian kanan atas, klik tombol segmented:
   - **Hari Ini:** Menampilkan data hari ini per jam (07:00–23:00) dibandingkan kemarin.
   - **Minggu Ini:** Menampilkan tren 7 hari berjalan dibandingkan minggu lalu.
   - **Bulan Ini (Default):** Menampilkan tren harian bulan berjalan dibandingkan bulan lalu.
   - **Tahun Ini:** Menampilkan performa kumulatif tahunan.
   - **Kustom:** Masukkan tanggal *Dari* dan *Sampai*, lalu klik **Terapkan Rentang**.

### Langkah 2: Menyaring Cabang Tertentu (Jika Multi-Cabang)
- Jika bisnis Anda memiliki lebih dari 1 cabang aktif, pilih cabang melalui menu *drop-down* di sebelah tombol periode.
- Seluruh metrik POS, Toko Online cabang tersebut, dan biaya operasional cabang akan otomatis tersaring.

### Langkah 3: Mencetak Laporan Eksekutif
- Klik tombol **Cetak Laporan** (ikon printer) di bilah navigasi atas.
- Kotak dialog cetak browser akan terbuka secara otomatis dengan format kertas A4/PDF yang bersih dan rapi.

---

## 4. Daftar Artikel Panduan Terkait

- [Troubleshooting & Pemecahan Masalah Analitik](troubleshooting.md)
- [FAQ & Tanya Jawab Seputar Margin dan Analitik](faq.md)
