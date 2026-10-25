# Residual Risks — Modul Analytics
> Modul: Analytics | Baseline Commit: 38cdcdf | Tanggal: 2026-10-25 | Auditor: AI Agent (/laravel-module-audit) | Status Dokumen: Final

---

## 1. Risiko Residual yang Masih Ada

| ID Risiko | Deskripsi Risiko | Tingkat Risiko | Kontrol Kompensasi yang Ada | Rekomendasi Pemilik Bisnis |
|---|---|:---:|---|---|
| **RES-ANA-001** | Volume Data Historis Sangat Besar (>500.000 Transaksi POS) | **Low** | Kueri telah dioptimasi dengan direct JOIN dan indeks komposit `['business_id', 'status']` serta filter rentang tanggal `whereDate`. | Jika bisnis berskala konglomerasi/jutaan transaksi per bulan, pertimbangkan job agregasi batch malam hari (*nightly summary table*). |
| **RES-ANA-002** | Master Produk Tanpa Modal HPP | **Low** | Jika produk belum diinput HPP dasarnya, kalkulasi otomatis menetapkan HPP = 0, sehingga gross profit = omzet. | Pemilik bisnis perlu melengkapi HPP pada menu *Master Data > Produk & Bahan* agar margin laba 100% akurat. |
| **RES-ANA-003** | Kegagalan Jaringan CDN Chart.js di Browser Klien | **Low** | Layout Bento tetap tampil utuh dan tabel produk terlaris tetap terbaca dengan angka tabular. | Untuk lingkungan intranet tertutup, pertimbangkan meng-host aset Chart.js secara lokal di direktori `public/vendor/`. |

---

## 2. Tindakan yang Memerlukan Akses / Intervensi Manusia

1. **Konfigurasi Role & Hak Akses:**
   - Pemilik bisnis menentukan siapa staf yang berhak mengakses analitik melalui *Pengaturan > Izin Akses & Peran Karyawan* (permission `reports.view`).
2. **Review Berkala Laporan Pajak & Akuntansi:**
   - Halaman analitik ini ditujukan untuk *monitoring operasional riil cepat*. Untuk pelaporan resmi pajak atau SPT tahunan, pemilik bisnis diarahkan menggunakan menu formal *Laporan SAK EMKM* (`/reports`).
