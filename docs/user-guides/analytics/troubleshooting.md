# Panduan Troubleshooting — Modul Analitik Bisnis
> Breadcrumb: [Panduan Analitik](index.md) › Troubleshooting  
> Terakhir diverifikasi terhadap route aktual: Commit `38cdcdf` | Tanggal: 2026-10-25

---

## Tabel Diagnosa Cepat: Gejala → Penyebab → Solusi

| Gejala Masalah | Kemungkinan Penyebab | Langkah Solusi Mandiri | Kapan Perlu Eskalasi |
|---|---|---|---|
| **Pesan "Anda tidak memiliki izin akses untuk halaman tersebut" saat membuka /analytics** | Akun pengguna Anda (mis. staf kasir/gudang) belum diberikan izin `reports.view`. | Masuk menggunakan akun Pemilik Usaha (*Owner*), buka menu **Pengaturan > Izin Akses & Peran Karyawan**, lalu centang hak akses *Lihat Laporan* untuk peran Anda. | Jika Anda adalah Pemilik Usaha tetapi tetap ditolak, hubungi admin sistem COOCA. |
| **Gross Margin produk terlaris tercatat 0% atau Gross Profit sama persis dengan Omzet** | Produk tersebut belum diisi modal HPP-nya di master data bahan baku atau master katalog produk. | Buka menu **Master Data > Produk & Menu**, pilih produk tersebut, klik Edit, lalu isi modal HPP dasar produk atau susun resep/BOM bahan bakunya. | - |
| **Pesanan Toko Online belum masuk ke total omzet** | Pesanan online masih berstatus *draft*, *pending_payment*, atau *cancelled*. | Omzet analitik hanya menghitung pesanan online yang telah lunas (*paid*) atau sedang diproses/dikirim. Konfirmasi pembayaran pelanggan di menu **Toko Online > Pesanan**. | Jika pesanan sudah berstatus Paid namun tidak muncul, periksa keselarasan tanggal transaksi pesanan. |
| **Grafik jam sibuk (heatmap) kosong atau datar** | Belum ada transaksi kasir berstatus Selesai (*completed*) pada rentang jam operasional 07:00–23:00. | Pastikan kasir telah menyelesaikan transaksi dengan menekan tombol Bayar di POS. Transaksi yang masih ditahan (*Hold*) tidak masuk perhitungan antrean. | - |
| **Data omzet antar cabang tampak berbeda dengan laporan kasir fisik** | Filter cabang belum dipilih (masih dalam mode konsolidasi *Semua Cabang / Outlet*). | Pilih cabang spesifik yang ingin diperiksa melalui menu dropdown cabang di bagian atas halaman [/analytics](/analytics). | - |
| **Rentang tanggal kustom otomatis kembali ke tanggal bulan ini** | Format tanggal yang dimasukkan tidak valid atau terjadi kesalahan input manual. | Gunakan pemilih tanggal (*calendar picker*) bawaan sistem agar format tanggal selalu sesuai standar (`YYYY-MM-DD`). | - |
