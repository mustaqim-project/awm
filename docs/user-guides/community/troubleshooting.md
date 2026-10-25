# Panduan Pemecahan Masalah (Troubleshooting) - community

> Modul: community | Tanggal Pembaruan: 2026-10-25 | Status: Resmi

Panduan ini membantu pemilik usaha dan staf dalam mengatasi berbagai kendala teknis dan operasional yang mungkin ditemui saat berinteraksi di feed komunitas atau saat mengikuti event di Cooca.

---

## Matriks Gejala, Penyebab, dan Solusi

### 1. Muncul Pesan "Akses partisipasi komunitas khusus untuk member yang telah berlangganan (subscribe)"
- **Gejala:** Tombol posting atau form komentar terkunci, atau muncul notifikasi pengingat berlangganan saat mencoba mengirim postingan di **[Feed Komunitas](/community)**.
- **Penyebab:** Bisnis Anda saat ini menggunakan paket *Free Tier* (Uji Coba Gratis). Pengguna paket free dapat membaca seluruh feed dan mendaftar event, namun hak berdiskusi aktif dibatasi demi mencegah spam akun bot.
- **Langkah Penyelesaian:**
  1. Kunjungi halaman **[Peningkatan Paket Langganan](/billing/limits)**.
  2. Pilih paket berbayar yang sesuai (misal: *Core*, *Standard*, atau *Premium*).
  3. Setelah langganan aktif, hak membuat postingan, komentar, dan me-repost akan terbuka secara otomatis.

---

### 2. Gagal Mengunggah Foto pada Postingan ("Kuota Penyimpanan Penuh")
- **Gejala:** Postingan teks berhasil dikirim, namun ketika melampirkan foto muncul pesan kesalahan bahwa kuota penyimpanan telah mencapai batas maksimal.
- **Penyebab:** Total akumulasi berkas gambar bisnis Anda telah melebihi kuota paket langganan yang tersedia.
- **Langkah Penyelesaian:**
  1. Periksa penggunaan media di **[Peningkatan Paket Langganan](/billing/limits)** pada widget *Storage Quota*.
  2. Hapus foto produk lama yang tidak digunakan atau tingkatkan paket penyimpanan bisnis Anda.
  3. Pastikan ukuran file foto baru tidak melebihi batas 5 MB per gambar dengan format yang didukung (JPEG, PNG, WebP).

---

### 3. Muncul Pesan "Konten mengandung kata yang dilarang"
- **Gejala:** Pengiriman postingan atau komentar dibatalkan dan form menampilkan kotak merah peringatan kata terlarang.
- **Penyebab:** Teks postingan atau komentar memuat kata yang masuk dalam daftar sensor kata terlarang (*Content Moderation*), seperti kata kasar, promosi pinjol ilegal, atau promosi judi online.
- **Langkah Penyelesaian:**
  1. Periksa kembali isi tulisan Anda.
  2. Hindari penggunaan singkatan atau istilah yang berpotensi menyerupai nama situs judi atau promosi tidak resmi.
  3. Perbaiki susunan kalimat Anda dan kirimkan kembali postingan.

---

### 4. Gagal Melakukan Check-in Presensi Event via GPS ("Di Luar Radius")
- **Gejala:** Tombol *Check-in via GPS* menampilkan pesan *"Anda berada di luar radius venue acara"* saat diklik di lokasi acara.
- **Penyebab:**
  1. Perangkat ponsel Anda belum mengaktifkan layanan lokasi presisi tinggi (*High Accuracy Location*).
  2. Peramban (*browser*) Anda belum diberi izin mengakses lokasi GPS.
  3. Sinyal GPS ponsel terhalang struktur beton bangunan bertingkat atau basement gedung.
- **Langkah Penyelesaian:**
  1. Buka pengaturan ponsel, pastikan opsi **GPS / Lokasi** aktif dan berikan izin lokasi ke browser Anda.
  2. Muat ulang halaman **[Event & Seminar Bisnis](/community/events)** lalu coba kembali.
  3. Jika GPS ponsel tetap tidak akurat, mintalah panitia di meja registrasi untuk memindai **Barcode Tiket Digital** Anda secara langsung.

---

### 5. Tiket Event Berbayar Masih Berstatus "Menunggu Pembayaran" Padahal Sudah Membayar QRIS
- **Gejala:** Anda telah menyelesaikan transfer QRIS di mobile banking, namun tombol tiket di halaman event belum aktif.
- **Penyebab:** Webhook notifikasi pelunasan dari jaringan perbankan sedang mengalami antrean pemrosesan selama beberapa detik.
- **Langkah Penyelesaian:**
  1. Buka halaman pembayaran event Anda, lalu klik tombol **Cek Status Pembayaran**.
  2. Sistem akan menyinkronkan status mutasi ke gateway QRIS seketika.
  3. Jika status tetap belum berubah setelah 5 menit dan saldo rekening Anda sudah terpotong, hubungi tim dukungan pelanggan Cooca dengan melampirkan kode tiket dan bukti struk transfer.
