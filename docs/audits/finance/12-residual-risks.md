# Residual Risks - finance

> Modul: Keuangan & Akuntansi Korporasi | Commit diaudit: 38cdcdf | Tanggal: 2026-10-25 | Status dokumen: Final

## Analisis Risiko Residual Pasca-Penguatan

Meskipun seluruh kerentanan utama telah diperbaiki dan lulus pengujian otomatis, terdapat beberapa risiko residual operasional yang perlu diperhatikan:

### 1. Perubahan Format Berkas Rekening Koran Internet Banking Pihak Ketiga
- **Deskripsi:** Bank komersial (seperti BCA, Mandiri, BRI, BNI) sewaktu-waktu dapat memperbarui susunan kolom atau format tanggal pada fitur ekspor CSV mutasi internet banking mereka.
- **Tingkat Risiko:** Rendah (*Low*).
- **Strategi Mitigasi Berjalan:**
  - Parser rekonsiliasi telah dirancang untuk mengenali header kolom fleksibel.
  - Sediakan panduan pada [Panduan Troubleshooting Keuangan](../../user-guides/finance/troubleshooting.md) untuk mendeteksi anomali kolom.
  - Layanan dukungan teknis siap merilis pembaruan adapter parser jika terdapat perubahan skema CSV bank resmi.

### 2. Beban Kueri Laporan pada Tenant Bervolume Transaksi Sangat Besar
- **Deskripsi:** Bisnis ritel atau F&B dengan frekuensi transaksi ribuan penjualan per hari dapat mengalami penurunan responsivitas saat membuka Laporan Buku Besar (*General Ledger*) dengan rentang waktu multi-tahun.
- **Tingkat Risiko:** Rendah (*Low*).
- **Strategi Mitigasi Berjalan:**
  - Form pencarian Buku Besar membatasi rentang tanggal pencarian maksimal 12 bulan per kueri.
  - Pagination server-side aktif secara konsisten membatasi beban memory RAM PHP.
  - Database telah dilengkapi indeks komposit `(business_id, entry_date)`.

### 3. Human Error Pemilihan Akun pada Jurnal Penyesuaian Manual
- **Deskripsi:** Pengguna yang tidak memahami akuntansi secara keliru memilih akun modal untuk mencatat beban operasional saat membuat jurnal penyesuaian (*Adjustment Journal*).
- **Tingkat Risiko:** Rendah (*Low*).
- **Strategi Mitigasi Berjalan:**
  - Hak akses pembuatan jurnal penyesuaian dikunci ketat pada permission `accounting.manage`.
  - Sistem menyediakan akun default terstandardisasi SAK EMKM sehingga staf operasional tidak perlu membuat akun baru secara manual.
