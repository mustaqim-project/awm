# Panduan Troubleshooting: Master Data Pelanggan

> Modul: Pelanggan (`customers`) | Tanggal Pembaruan: 2026-10-25 | Status: Resmi

Dokumen ini berisi panduan pemecahan kendala teknis dan penanganan error yang sering dialami pengguna saat mengelola data pelanggan, melakukan impor file Excel, atau menghapus kontak di Cooca.

---

## 1. Masalah Penghapusan Pelanggan

### A. Muncul Peringatan "Tidak dapat menghapus pelanggan yang masih memiliki saldo piutang/kasbon berjalan"
- **Penyebab:** Pelanggan yang bersangkutan masih tercatat memiliki saldo piutang kasbon belum lunas pada kolom `current_credit_balance`.
- **Mengapa Diblokir:** Sistem Cooca menerapkan *Deletion Guard* untuk melindungi pemilik usaha dari potensi kehilangan jejak piutang atau pembukuan yang tidak seimbang (*unbalanced ledger*).
- **Solusi:**
  1. Arahkan pelanggan ke menu **[Program Loyalitas & Member](/crm/members)**.
  2. Klik tombol **Bayar Kasbon** pada baris pelanggan tersebut dan lakukan pelunasan penuh sesuai nilai kasbon berjalan.
  3. Setelah saldo kasbon bernilai Rp 0, buka kembali menu **[Direktori Pelanggan](/customers)** dan lakukan penghapusan data.

---

## 2. Masalah Kode Pelanggan & Validasi Duplikat

### A. Muncul Pesan "Kode pelanggan sudah digunakan di bisnis Anda"
- **Penyebab:** Kode pelanggan (kolom `code`) bersifat unik per bisnis (*tenant*). Anda tidak dapat mendaftarkan dua pelanggan berbeda dengan kode yang sama.
- **Solusi:**
  1. Periksa apakah kode tersebut sudah terpakai pada halaman **[Direktori Pelanggan](/customers)** dengan mengetikkan kode pada kolom pencarian.
  2. Jika kode sudah ada, Anda dapat memperbarui pelanggan yang sudah ada alih-alih membuat data baru.
  3. Jika ingin membuat pelanggan baru, gunakan akhiran nomor urut berikutnya (misal: `CUST-002` alih-alih `CUST-001`).
  4. Perlu dicatat: kode pelanggan unik hanya berlaku di dalam bisnis Anda. Bisnis lain di Cooca bebas memakai kode yang sama tanpa saling mengganggu.

---

## 3. Masalah Impor File Excel

### A. Pratinjau Menampilkan "Tidak ada data yang valid untuk diimpor"
- **Kemungkinan Penyebab:**
  1. Anda menggunakan format berkas yang salah atau memodifikasi susunan judul kolom template.
  2. Berkas Excel yang diunggah kosong atau baris pertama bukan baris data.
  3. Ukuran berkas melebihi batas maksimal 5 MB.
- **Solusi:**
  1. Unduh template resmi terbaru di tautan **[Unduh Template Impor](/customers/template)**.
  2. Salin data pelanggan ke dalam template tersebut tanpa mengubah posisi kolom header baris 1.
  3. Pastikan kolom **Nama Pelanggan** tidak kosong.
  4. Simpan dalam format `.xlsx` atau `.csv` dan unggah kembali di **[Direktori Pelanggan](/customers)**.

### B. Notifikasi Format Telepon Tidak Sesuai
- **Solusi:** Gunakan nomor telepon berawalan `08...` atau `628...` tanpa spasi atau tanda baca khusus agar sistem dapat mengirimkan pesan WhatsApp dan SMS struk digital dengan lancar.

---

## 4. Masalah Hak Akses Karyawan (Error 403 / Dialihkan ke Portal)

### A. Karyawan Hanya Bisa Melihat Daftar tapi Tidak Bisa Menambah atau Mengedit
- **Penyebab:** Akun staf hanya memiliki hak akses `customers.view` dan belum diberikan izin `customers.create` atau `customers.edit`.
- **Solusi:**
  1. Masuk menggunakan akun **Owner**.
  2. Masuk ke menu pengaturan peran karyawan (*Staff Roles*).
  3. Centang izin **Tambah Pelanggan (`customers.create`)** atau **Edit Pelanggan (`customers.edit`)** untuk staf tersebut.

---

## 5. Tautan Terkait Terverifikasi
- **[Direktori Pelanggan](/customers)**
- **[Ekspor Data Pelanggan](/customers/export)**
- **[Unduh Template Impor](/customers/template)**
- **[Program Loyalitas & Member](/crm/members)**
