# Panduan Troubleshooting: Pusat Masukan & Bantuan Produk

> Modul: Masukan & Bantuan Produk (`feedback`) | Tanggal Pembaruan: 2026-10-25 | Status: Resmi

Dokumen ini berisi solusi pemecahan kendala yang sering ditemui saat mengirimkan tiket kendala teknis atau mengunggah lampiran gambar di Cooca.

---

## 1. Masalah Pengunggahan Berkas Lampiran (Attachment)

### A. Muncul Pesan "File attachment melebihi batas ukuran maksimal"
- **Penyebab:** Ukuran berkas foto atau tangkapan layar yang Anda unggah melebihi batas maksimal sistem (5 MB / 5.120 KB).
- **Solusi:**
  1. Kompres gambar tangkapan layar Anda atau ubah resolusi foto kamera ponsel sebelum mengunggah.
  2. Gunakan format file yang efisien seperti `.jpg`, `.jpeg`, `.png`, atau `.webp`.

### B. Notifikasi "Tipe file attachment tidak diizinkan"
- **Penyebab:** Anda mencoba mengunggah jenis berkas biner, arsip zip, atau script yang tidak didukung.
- **Solusi:**
  1. Sistem Cooca hanya mengizinkan berkas dokumen dan gambar standar: `.jpg`, `.jpeg`, `.png`, `.webp`, `.pdf`, `.txt`, dan `.log`.
  2. Jika Anda memiliki rekaman video kendala, Anda dapat mengunggahnya ke Google Drive / YouTube (unlisted) dan menyematkan tautannya pada kolom deskripsi laporan.

---

## 2. Masalah Kuota Ruang Penyimpanan (*Storage Quota Exceeded*)

### A. Muncul Pesan "Kapasitas penyimpanan akun Anda telah penuh"
- **Penyebab:** Kuota ruang penyimpanan lampiran bisnis Anda pada paket aktif saat ini telah mencapai batas maksimum (*Storage Quota Limit*).
- **Solusi:**
  1. Buka menu pengaturan akun untuk memeriksa penggunaan ruang penyimpanan berkas Anda.
  2. Anda tetap dapat mengirimkan laporan kendala tanpa melampirkan berkas fisik dengan menuliskan kronologi teks secara lengkap.
  3. Lakukan upgrade paket langganan untuk menambah kapasitas penyimpanan cloud bisnis Anda.

---

## 3. Masalah Akses Menu (Error 403 / Dialihkan ke Portal)

### A. Karyawan atau Kasir Tidak Dapat Membuka Menu Feedback
- **Gejala:** Muncul pesan kesalahan otorisasi atau otomatis dialihkan saat staf mencoba mengakses halaman laporan bug.
- **Penyebab:** Modul masukan platform dirancang khusus dengan proteksi `require.role:owner` demi kerahasiaan operasional bisnis. Hanya pengguna berstatus **Owner (Pemilik Usaha)** yang memiliki kewenangan menyampaikan masukan dan laporan teknis langsung kepada tim Cooca.
- **Solusi:**
  1. Masuk menggunakan akun pemilik usaha (*Owner*) yang terdaftar.
  2. Karyawan yang menemukan kendala kasir cukup melaporkannya kepada pemilik toko untuk diteruskan ke tim pengembang Cooca.

---

## 4. Tautan Menu Terverifikasi
- **[Daftar Laporan Bug Kendala](/feedback/bugs)**
- **[Laporkan Kendala Bug Baru](/feedback/bugs/create)**
- **[Daftar Usulan Fitur Baru](/feedback/features)**
- **[Ajukan Usulan Fitur Baru](/feedback/features/create)**
