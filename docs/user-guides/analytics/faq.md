# FAQ (Tanya Jawab)   Modul Analitik Bisnis
> Breadcrumb: [Panduan Analitik](index.md) › FAQ  
> Terakhir diverifikasi terhadap route aktual: Commit `38cdcdf` | Tanggal: 2026-10-25

---

### Q1: Apa perbedaan halaman Analitik Bisnis (/analytics) dengan Pusat Laporan (/reports)?
**Jawab:**
- **Analitik Bisnis ([/analytics](/analytics)):** Dirancang untuk *monitoring eksekutif harian & strategis cepat*. Berisi visualisasi grafik Bento modern (Chart.js), pergerakan laba kotor riil, jam sibuk antrean kasir, perbandingan cabang, dan 10 produk terlaris.
- **Pusat Laporan ([/reports](/reports)):** Dirancang untuk *kebutuhan pembukuan dan kepatuhan akuntansi resmi SAK EMKM*, seperti Laporan Laba Rugi resmi per akun COA, Neraca Saldo, Arus Kas, dan ekspor data buku besar ke Excel/PDF.

---

### Q2: Mengapa Laba Bersih di Analitik disebut "Estimasi Laba Bersih"?
**Jawab:**
Laba Bersih dihitung dari `Total Laba Kotor Penjualan - Beban Operasional yang Dicatat di Menu Keuangan`. Jika bisnis Anda memiliki transaksi non-operasional (seperti penyusutan aset bulanan atau bunga pinjaman bank) yang belum dicatat dalam jurnal beban, angka akhir pada laporan keuangan formal SAK EMKM di [/reports](/reports) yang menjadi rujukan audit akuntansi resmi.

---

### Q3: Bagaimana rumus perhitungan persentase pertumbuhan (+/- % vs periode lalu)?
**Jawab:**
Sistem membandingkan periode berjalan dengan periode yang setara sebelumnya:
- **Hari Ini:** Dibandingkan dengan kemarin.
- **Minggu Ini:** Dibandingkan dengan 7 hari pada minggu lalu.
- **Bulan Ini:** Dibandingkan dengan bulan lalu pada rentang hari yang sama.
- **Kustom:** Dibandingkan dengan rentang hari setara sebelumnya (misal memilih rentang 10 hari, maka pembandingnya adalah 10 hari persis sebelum tanggal awal).
Rumus: `((Nilai Periode Ini - Nilai Periode Lalu) / |Nilai Periode Lalu|) * 100%`.

---

### Q4: Apakah data analitik bisa diakses oleh kasir cabang?
**Jawab:**
Secara default **TIDAK**. Hanya pengguna dengan hak akses (*permission*) `reports.view` yang dapat melihat halaman ini. Pemilik bisnis memiliki wewenang penuh untuk mengatur siapa staf yang boleh melihat data omzet dan laba di menu *Pengaturan > Izin Akses & Peran Karyawan*.

---

### Q5: Bisakah laporan analitik ini dicetak atau disimpan sebagai file PDF?
**Jawab:**
**Bisa.** Klik tombol **Cetak Laporan** (ikon printer) di bilah navigasi atas halaman [/analytics](/analytics). Browser Anda akan membuka jendela cetak resmi yang telah dioptimalkan (tombol filter, navigasi sidebar, dan footer web disembunyikan otomatis untuk hasil cetak A4/PDF yang bersih dan rapi).
