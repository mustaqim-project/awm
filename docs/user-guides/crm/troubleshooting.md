# Panduan Troubleshooting: CRM & Program Loyalitas Pelanggan

> Modul: CRM & Loyalty (`crm`) | Tanggal Pembaruan: 2026-10-25 | Status: Resmi

Dokumen ini berisi panduan pemecahan masalah teknis dan operasional yang sering ditemui saat menggunakan fitur keanggotaan member, penukaran poin, dan penggunaan kupon voucher di Cooca.

---

## 1. Masalah Pembayaran Kasbon & Piutang Pelanggan

### A. Muncul Pesan "Nominal pembayaran melebihi sisa kasbon"
- **Penyebab:** Kasir memasukkan nominal pembayaran yang lebih besar daripada nilai saldo kredit (*current credit balance*) yang tercatat di sistem Cooca.
- **Solusi:**
  1. Periksa kembali sisa kasbon terkini pelanggan di kolom **Sisa Piutang / Kasbon** pada halaman **[Daftar Member & Loyalitas](/crm/members)**.
  2. Masukkan nominal yang sama persis untuk pelunasan penuh, atau lebih kecil jika pelanggan membayar secara cicilan/sebagian.
  3. Sistem sengaja membatasi ini untuk mencegah kekeliruan pencatatan saldo negatif yang membingungkan laporan keuangan.

### B. Saldo Kas Toko Belum Bertambah Setelah Kasir Menerima Pelunasan Kasbon
- **Penyebab:** Kasir memilih akun kas yang berbeda saat mengonfirmasi setoran kasbon.
- **Solusi:**
  1. Buka menu keuangan dan periksa mutasi kas masuk pada akun kas yang dipilih saat form pelunasan dikirim.
  2. Transaksi otomatis dicatat dengan referensi kode penerimaan kasbon pelanggan dan dapat dilacak di log audit.

---

## 2. Masalah Kode Voucher & Kupon Promosi

### A. Muncul Notifikasi "Kode voucher sudah digunakan di bisnis Anda"
- **Penyebab:** Kode voucher bersifat unik per bisnis (*tenant*). Anda tidak dapat membuat dua voucher aktif atau arsip dengan kombinasi kode yang sama.
- **Solusi:**
  1. Buka menu **[Katalog Voucher Promosi](/crm/vouchers)**.
  2. Cari apakah kode tersebut sudah pernah dibuat sebelumnya.
  3. Jika sudah ada tetapi berstatus nonaktif, Anda cukup menekan tombol **Aktifkan** kembali daripada membuat duplikat baru.
  4. Jika ingin membuat promo baru, gunakan variasi kode yang berbeda (misal: `DISKON2026B` alih-alih `DISKON2026`).

### B. Voucher Tidak Dapat Digunakan Kasir di Aplikasi Kasir (POS)
- **Kemungkinan Penyebab:**
  1. **Masa Berlaku Kedaluwarsa:** Tanggal transaksi saat ini berada di luar rentang `valid_from` hingga `valid_until`.
  2. **Kuota Pemakaian Habis:** Total pemakaian kupon telah mencapai batas `usage_limit`.
  3. **Belanja Kurang dari Nilai Minimum:** Nilai belanja keranjang kasir belum memenuhi `min_order_amount`.
  4. **Status Nonaktif:** Status voucher sedang dinonaktifkan oleh Manager Toko.
- **Solusi:**
  - Buka halaman **[Katalog Voucher Promosi](/crm/vouchers)**, periksa detail kartu voucher, lalu pastikan kuota dan statusnya masih aktif.

---

## 3. Masalah Hak Akses Karyawan (Error 403 / Dialihkan ke Portal)

### A. Kasir atau Staf Tidak Bisa Mengakses Halaman CRM
- **Gejala:** Saat staf mengklik menu CRM, sistem menampilkan pesan error otorisasi atau otomatis mengalihkan staf kembali ke halaman beranda/portal.
- **Penyebab:** Akun staf yang bersangkutan belum diberikan izin `crm.view` pada pengaturan peran karyawan (*Role & Permission*).
- **Solusi:**
  1. Masuk menggunakan akun **Owner** bisnis.
  2. Buka pengaturan peran pengguna (*Staff Roles*).
  3. Pastikan izin centang **Lihat CRM (`crm.view`)** telah diaktifkan untuk peran kasir/staf tersebut.

### B. Staf Bisa Melihat Member tapi Tombol Buat Voucher / Bayar Kasbon Ditolak
- **Gejala:** Staf dapat membuka halaman member dan voucher, namun gagal saat menyimpan data baru.
- **Penyebab:** Staf hanya memiliki izin lihat (`crm.view`) dan belum memiliki izin kelola (`crm.manage`).
- **Solusi:**
  - Hubungi Owner atau Manager untuk menambahkan izin **Kelola CRM (`crm.manage`)** jika staf memang ditugaskan mengelola kasbon dan promosi toko.

---

## 4. Tautan Menu Terverifikasi
- **[Daftar Member & Loyalitas](/crm/members)**
- **[Katalog Voucher Promosi](/crm/vouchers)**
