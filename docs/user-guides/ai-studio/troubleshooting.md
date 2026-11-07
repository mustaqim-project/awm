# Panduan Pemecahan Masalah (Troubleshooting) - Modul AI Studio Visual (`ai-studio`)

> Modul: `ai-studio` | Fokus: Kendala Render Gambar, Kegagalan Saldo Kredit, & Solusi Mandiri | Tanggal: 2026-11-07

---

## 1. Matriks Kendala Operasional & Solusi Cepat

| Gejala Masalah | Penyebab Utama | Langkah Pemulihan Mandiri |
| :--- | :--- | :--- |
| **"Saldo Kredit Tidak Mencukupi (Insufficient Credits)"** | Kuota kredit bulanan telah habis terpakai untuk sesi render sebelumnya. | Klik tombol **Top Up Kredit** pada header halaman atau buka modal top up. Pilih paket kredit (Starter, Growth, atau Pro) dan selesaikan pembayaran QRIS atau VA. Kredit akan bertambah otomatis dalam hitungan detik. |
| **"Proses Render Berjalan Terus Tanpa Henti (*Loading Spinner Loop*)"** | Koneksi internet terputus saat sinkronisasi status render atau beban antrean server AI sedang tinggi. | Sistem dilengkapi batas waktu (*timeout*) otomatis 3 menit. Tunggu hingga status selesai, atau muat ulang halaman (*refresh browser*). Jika proses dinyatakan gagal, sistem akan otomatis mengembalikan saldo kredit Anda. |
| **"Hasil Gambar Terdistorsi atau Produk Berubah Bentuk"** | Foto produk mentah yang diunggah buram, terpotong, atau prompt terlalu rumit. | Gunakan foto produk dengan pencahayaan terang dan latar belakang kontras. Hindari instruksi prompt yang saling bertentangan; gunakan fitur **Sempurnakan Prompt (Enhance Prompt)** untuk komposisi optimal. |
| **"Berkas Foto Gagal Diunggah (File Upload Error)"** | Ukuran berkas foto melebihi batas 10MB atau format berkas tidak didukung. | Pastikan foto berformat JPG, PNG, atau WEBP dan memiliki ukuran di bawah 10MB sebelum mengunggah. |
| **"Gagal Menyimpan ke Katalog Produk Kasir POS"** | Master produk di kasir POS sedang dibuka/diedit oleh kasir lain atau koneksi terputus. | Buka menu *Manajemen Produk*, pastikan produk aktif dan tidak berstatus draft, lalu coba klik tombol **Simpan ke Katalog** sekali lagi. |

---

## 2. Prosedur Klaim Pengembalian Kredit Otomatis

Jika terjadi kegagalan sistem saat merender visual:
1. Buka menu **Riwayat Saldo Kredit** ([`/ai-studio/credits`](https://cooca.id/ai-studio/credits)).
2. Periksa tabel transaksi kredit. Setiap kali render gagal, sistem otomatis membuat entri mutasi pengembalian dengan deskripsi:
   `"Refund: Render gagal [ID Proyek]"` senilai kredit yang sebelumnya dipotong.
3. Jika kredit belum kembali dalam 5 menit, buat laporan kendala melalui menu bantuan sistem.

---

## 3. Kontak Dukungan Pengguna

Jika Anda mengalami kendala pembayaran paket kredit top up yang belum masuk atau hasil render tidak muncul di galeri, silakan buat tiket bantuan resmi melalui menu [`/feedback/bugs/create`](https://cooca.id/feedback/bugs/create) dengan mencantumkan ID transaksi top up atau ID proyek terkait.

