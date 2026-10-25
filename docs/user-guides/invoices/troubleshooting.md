# Panduan Penyelesaian Masalah (Troubleshooting) - Modul Faktur Penjualan

Dokumen ini berisi panduan teknis dan operasional untuk menyelesaikan kendala yang sering ditemui saat mengelola faktur penjualan dan piutang dagang di COOCA.

---

## 1. Kendala Penerbitan & Penyuntingan Faktur

### Gejala: Tombol "Edit Faktur" Tidak Muncul atau Muncul Peringatan "Faktur tidak dapat diedit"
- **Penyebab:**
  1. Faktur telah memiliki riwayat pembayaran yang tercatat (meskipun hanya sebagian / DP).
  2. Faktur telah berstatus `PAID` (Lunas) atau `VOID` (Dibatalkan).
- **Penjelasan Sistem:** Untuk menjaga keabsahan pembukuan dan mencegah ketidakcocokan saldo buku kas, sistem melarang pengubahan nilai faktur yang sudah memiliki mutasi uang masuk.
- **Solusi:**
  - Jika terjadi kesalahan barang atau harga pada faktur yang telah berbayar sebagian, batalkan pembayaran yang keliru terlebih dahulu di menu pembukuan keuangan (oleh pengguna berizin khusus), atau gunakan opsi pembatalan **VOID** dan terbitkan faktur revisi baru.
  - Untuk draf yang belum memiliki pembayaran, pastikan status faktur masih berupa `DRAFT` atau `UNPAID` murni.

### Gejala: Muncul Kesalahan "InsufficientStockException: Stok produk tidak mencukupi" saat Rilis Faktur
- **Penyebab:** Kuantitas produk yang tertera pada faktur melebihi sisa fisik barang di lokasi gudang yang dipilih.
- **Solusi:**
  1. Periksa ketersediaan stok produk di menu **Inventori > Mutasi Stok**.
  2. Lakukan penyesuaian stok (*Stock Adjustment*) atau catat penerimaan barang baru dari pesanan pembelian supplier (*Goods Receipt*).
  3. Atau ubah lokasi gudang pengeluaran pada faktur jika barang tersedia di cabang/gudang penyimpanan lain.

---

## 2. Kendala Pembayaran & Pelunasan

### Gejala: Status Faktur Tidak Berubah Menjadi "Lunas" (PAID) Padahal Uang Sudah Ditransfer
- **Penyebab:** Nominal pembayaran yang dicatat lebih kecil daripada nilai `balance_due` faktur, atau pembayaran baru dicatat di mutasi kas umum tanpa ditautkan ke ID faktur terkait.
- **Solusi:**
  1. Buka halaman detail faktur bersangkutan melalui [/invoices](/invoices).
  2. Periksa baris riwayat pembayaran di tabel bawah:
     - Jika status masih `PARTIALLY_PAID`, periksa selisih sisa tagihan. Catat sisa pelunasan melalui tombol **Catat Pembayaran** pada halaman faktur tersebut.
     - Jangan mencatat pelunasan faktur secara manual di luar tombol **Catat Pembayaran** faktur agar kalkulasi saldo terupdate otomatis.

### Gejala: Pelanggan Salah Bayar / Kelebihan Bayar (Overpayment)
- **Solusi:**
  1. Catat pembayaran maksimal sebesar nilai sisa saldo tagihan faktur tersebut hingga statusnya menjadi `PAID`.
  2. Catat kelebihan dana sebagai deposit pelanggan di modul CRM / Customer Ledger, atau kembalikan kelebihan dana via mutasi pengeluaran kas dengan referensi nota pelanggan.

---

## 3. Kendala Ekspor Laporan & Spreadsheet

### Gejala: Rumus atau Angka Faktur Diawali Tanda Petik Tunggal (') pada Berkas Excel / CSV
- **Penyebab:** Fitur keamanan **Formula Injection Defense (CWE-1236)** mendeteksi karakter pemicu formula pada nomor faktur (misal `+INV-01`), nama pelanggan (misal `@CV Abadi`), atau catatan termin (`-Net 30`).
- **Penjelasan:** Karakter petik tunggal sengaja disisipkan oleh sistem COOCA demi melindungi komputer Anda dari eksekusi kode macro berbahaya saat membuka laporan spreadsheet.
- **Solusi:** Hal ini normal dan merupakan standar keamanan enterprise perbankan. Sel teks tetap dapat dibaca dengan jelas dan dapat diolah untuk kalkulasi angka tanpa risiko serangan cyber.

---

## 4. Tautan Terkait
- [Kembali ke Panduan Utama Pengguna Invoices](index.md)
- [Pertanyaan yang Sering Diajukan (FAQ)](faq.md)
