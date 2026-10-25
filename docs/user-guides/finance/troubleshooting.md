# Panduan Penyelesaian Masalah: Modul Keuangan & Akuntansi (Finance & Accounting)

> Modul: Finance & Accounting | Status: Final | Terverifikasi: 2026-10-25 | Target Pembaca: Pemilik Usaha & Staf Keuangan

Dokumen ini berisi panduan terstruktur langkah-demi-langkah bagi pemilik usaha (**Business Owner**) dan staf administrasi keuangan dalam mendiagnosis dan mengatasi kendala teknis maupun operasional pada modul Keuangan COOCA.

---

## 1. Matriks Masalah & Solusi Cepat

| No | Gejala Masalah | Penyebab Umum | Langkah Awal Solusi | Tautan Menu |
|---|---|---|---|---|
| 1 | Selisih saldo mutasi saat Rekonsiliasi Bank | Beda tanggal cut-off atau biaya admin bank belum dicatat | Periksa mutasi belum terikat & catat biaya bank | [/finance/reconciliations](/finance/reconciliations) |
| 2 | Peringatan Jurnal Tidak Seimbang pada Neraca Saldo | Transaksi manual atau mutasi kas tanpa lawan akun debit/kredit | Jalankan verifikasi saldo & cek entri jurnal | [/finance/trial-balance](/finance/trial-balance) |
| 3 | Gagal mengimpor Bagan Akun (COA) CSV | Format kode akun duplikat atau kolom CSV tidak sesuai template | Unduh template resmi & sesuaikan header kolom | [/finance/chart-of-accounts](/finance/chart-of-accounts) |
| 4 | Muncul pesan "Akses Ditolak" saat buka menu Neraca | Akun staf belum memiliki permission `accounting.view` | Tetapkan role Akuntan / berikan izin via Admin | [/finance/balance-sheet](/finance/balance-sheet) |
| 5 | Gagal mengunggah foto nota bukti pengeluaran | Ukuran file melebihi 5 MB atau kuota penyimpanan penuh | Kompres foto atau hapus lampiran kedaluwarsa | [/finance/expenses](/finance/expenses) |
| 6 | Penarikan dana Payout Gateway berstatus Pending | Rekening payout belum diverifikasi atau limit bank tujuan | Periksa status rekening bank payout tujuan | [/finance/payout-accounts](/finance/payout-accounts) |

---

## 2. Rincian Gejala, Akar Penyebab, & Solusi

### Masalah 1: Selisih Mutasi saat Rekonsiliasi Bank Otomatis

- **Gejala**: Saldo akhir rekening koran yang diimpor dari file CSV bank (BCA, Mandiri, BRI) tidak cocok dengan saldo buku kas di COOCA (*Unreconciled Difference*).
- **Akar Penyebab**:
  1. Ada transaksi potongan biaya administrasi bulanan atau bunga bank yang belum dimasukkan ke buku kas.
  2. Perbedaan tanggal pencatatan antara tanggal kasir menyetor uang tunai dengan tanggal bank membukukan dana (kliring).
  3. Format tanggal file CSV rekening koran berbeda dengan preferensi sistem (`DD/MM/YYYY` vs `YYYY-MM-DD`).
- **Langkah Pemecahan**:
  1. Buka menu [Rekonsiliasi Bank](/finance/reconciliations).
  2. Pilih akun bank yang bermasalah dan periksa daftar mutasi pada tab **Unmatched Transactions**.
  3. Jika terdapat selisih berupa biaya administrasi bank, klik tombol **Buat Pengeluaran Cepat** langsung dari baris rekonsiliasi tersebut, lalu arahkan ke akun beban *Beban Administrasi Bank*.
  4. Jika transaksi disetor terlambat, lakukan pencocokan manual (*Manual Match*) dengan mencari nomor transaksi buku kas terkait.
- **Eskalasi**: Jika format CSV bank berubah format (misal update struktur internet banking), hubungi dukungan teknis COOCA dengan melampirkan 3 baris teratas contoh file CSV (samarkan nomor rekening sensitif).

---

### Masalah 2: Peringatan Jurnal Tidak Seimbang (*Out-of-Balance*) pada Neraca Saldo

- **Gejala**: Banner peringatan merah muncul di bagian atas halaman [Neraca Saldo](/finance/trial-balance) yang menyatakan total Debit tidak sama dengan total Kredit.
- **Akar Penyebab**:
  1. Terjadi interupsi jaringan pada saat pemrosesan batch jurnal akuntansi.
  2. Adanya migrasi saldo awal (*Opening Balance*) akun piutang/hutang yang tidak dilengkapi dengan akun penyeimbang (*Saldo Penyeimbang Awal / Opening Equity*).
- **Langkah Pemecahan**:
  1. Buka [Neraca Saldo](/finance/trial-balance) dan perhatikan baris dengan selisih nominal.
  2. Masuk ke menu [Jurnal Akuntansi](/finance/journals) lalu filter berdasarkan tanggal transaksi yang mengalami selisih.
  3. Cari nomor referensi transaksi dengan status *Unbalanced*.
  4. Lakukan penyesuaian (*Adjustment Entry*) melalui tombol **Tambah Jurnal Penyesuaian** dengan memasukkan akun debit dan kredit yang sesuai standar akuntansi.
- **Eskalasi**: Jika ketidakseimbangan bersumber dari integrasi otomatis sistem POS, eskalasikan ke Tim Database Administrator COOCA untuk memicu sinkronisasi ulang *ledger pipeline*.

---

### Masalah 3: Gagal Mengimpor File COA (Bagan Akun) via CSV

- **Gejala**: Sistem menampilkan pesan error *"Gagal mengimpor file: Kode akun sudah digunakan atau format kolom tidak valid"*.
- **Akar Penyebab**:
  1. Pengguna memodifikasi susunan nama kolom pada file CSV template.
  2. Terdapat kode akun yang identik dengan akun bawaan sistem (*system-reserved COA*).
  3. Karakter pemisah CSV menggunakan titik koma (`;`) padahal sistem memerlukan koma (`,`).
- **Langkah Pemecahan**:
  1. Buka menu [Bagan Akun (COA)](/finance/chart-of-accounts).
  2. Klik tombol **Unduh Template CSV** untuk mendapatkan struktur tabel terbaru.
  3. Pastikan tidak ada karakter aneh pada baris kode akun (hanya angka dan titik, misal `1110.01`).
  4. Simpan file dengan format `CSV (Comma Delimited) (*.csv)`.
  5. Unggah kembali file melalui form impor COA.
- **Eskalasi**: Apabila sistem tetap menolak, kirimkan file CSV Anda ke tim implementor akuntansi COOCA untuk diperiksa kompatibilitasnya.

---

### Masalah 4: Akses Ditolak (HTTP 403 / Dialihkan ke Portal)

- **Gejala**: Staf kasir atau supervisor diarahkan ke halaman portal utama saat mencoba membuka menu laporan keuangan atau buku kas.
- **Akar Penyebab**:
  1. Controller-Level RBAC mengisolasi izin akses laporan. Menu akuntansi membutuhkan izin `accounting.view`, buku kas membutuhkan `finance.cash_bank`, dan pengelolaan pengeluaran membutuhkan `expenses.view`.
  2. Role pengguna bersangkutan belum didaftarkan pada matriks RBAC bisnis Anda.
- **Langkah Pemecahan**:
  1. Masuk sebagai **Owner** atau akun dengan hak akses **Administrator**.
  2. Buka menu **Pengaturan Tim & Role**.
  3. Pilih profil pengguna yang bersangkutan, kemudian centang izin:
     - `finance.cash_bank` untuk akses [Buku Kas & Bank](/finance/cash-bank).
     - `accounting.view` untuk akses [Neraca Keuangan](/finance/balance-sheet) dan [Bagan Akun](/finance/chart-of-accounts).
     - `expenses.manage` untuk membuat dan menyetujui biaya di [Beban Operasional](/finance/expenses).
  4. Minta pengguna melakukan *logout* dan *login* ulang agar token otorisasi diperbarui.
- **Eskalasi**: Jika izin sudah dicentang namun tetap terjadi pengalihan, hubungi tim support COOCA.

---

### Masalah 5: Gagal Mengunggah Foto Bukti Nota Pengeluaran

- **Gejala**: Tombol simpan pengeluaran gagal diproses dan muncul notifikasi *"Ukuran file terlalu besar atau format gambar tidak didukung"*.
- **Akar Penyebab**:
  1. Foto bukti nota yang diambil dari kamera resolusi tinggi menghasilkan file di atas 5 MB.
  2. Format file selain `.jpg`, `.jpeg`, `.png`, atau `.webp`.
- **Langkah Pemecahan**:
  1. Sebelum mengunggah, kurangi resolusi foto atau gunakan fitur kompresi bawaan kamera ponsel.
  2. Pastikan file tersimpan dalam format standar gambar.
  3. Jika nota berbentuk dokumen digital, simpan dalam format PDF atau tangkapan layar (screenshot).
  4. Lanjutkan pengisian form di [Beban Operasional](/finance/expenses).
- **Eskalasi**: Jika ukuran di bawah 2 MB tetap gagal diunggah, periksa sisa kuota penyimpanan cloud bisnis Anda pada Pengaturan Akun.

---

## 3. Navigasi Dokumen Terkait

- [Kembali ke Panduan Utama Modul Keuangan](index.md)
- [Lihat Pertanyaan Umum (FAQ)](faq.md)
