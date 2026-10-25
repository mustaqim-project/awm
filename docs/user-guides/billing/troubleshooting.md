# Panduan Pemecahan Masalah (Troubleshooting) Billing

Panduan ini membantu pemilik usaha dan manajer operasional menyelesaikan kendala seputar transaksi pembayaran, verifikasi tagihan, serta sinkronisasi kuota pemakaian di COOCA.

---

## 1. Pembayaran Sudah Ditransfer, Namun Status Masih "Menunggu Pembayaran"

### Kemungkinan Penyebab:
- Pembayaran via Virtual Account atau QRIS sedang dalam antrean rekonsiliasi gateway perbankan.
- Pembayaran dilakukan mendekati atau setelah batas waktu 15 menit berakhir.

### Solusi Praktis:
1. Masuk ke halaman [Riwayat Tagihan & Faktur](/billing/history).
2. Temukan pesanan dengan nomor order terkait, lalu klik tombol **"Cek Status Pembayaran"**.
3. Sistem akan langsung melakukan pengecekan ulang ke server perbankan. Jika dana telah tercatat, status tagihan otomatis berubah menjadi **Lunas (Approved)** dan paket langsung aktif.
4. Jika transfer berhasil dipotong dari rekening Anda namun tagihan terlanjur kedaluwarsa, hubungi tim dukungan resmi COOCA dengan menyertakan Nomor Order dan bukti mutasi bank Anda untuk dibantu aktivasi manual.

---

## 2. Kuota Penyimpanan Cloud Terbaca Penuh Padahal Gambar Sudah Dihapus

### Kemungkinan Penyebab:
- Tampilan dasbor masih menyimpan data cache sementara agar halaman memuat lebih cepat.
- Berkas fisik telah terhapus namun ukuran agregat di database belum direkonsiliasi.

### Solusi Praktis:
1. Masuk ke halaman [Batas Kuota & Pemakaian](/billing/limits).
2. Temukan kartu **Penyimpanan Cloud (Storage)**.
3. Klik tombol **"Hitung Ulang Penyimpanan"**.
4. Sistem akan secara otomatis memindai seluruh direktori bisnis Anda, membersihkan data sisa, dan memperbarui angka kapasitas terpakai secara akurat dalam beberapa detik.

---

## 3. Pesan Error "Batas Kuota Tercapai" Saat Menambah Produk atau Transaksi Kasir

### Kemungkinan Penyebab:
- Kapasitas produk atau volume transaksi bulanan pada paket yang aktif saat ini telah mencapai batas limit maksimal.

### Solusi Praktis:
1. Buka [Batas Kuota & Pemakaian](/billing/limits) untuk melihat pilar mana yang telah mencapai 100%.
2. Jika Anda masih menggunakan paket Solo atau Standard, klik [Upgrade & Perpanjangan Langganan](/billing/checkout) untuk meningkatkan paket ke **Premium** atau **Prestige** dengan batas transaksi tanpa batas (*unlimited*).
3. Untuk kuota transaksi bulanan, kuota akan otomatis di-reset menjadi 0 pada tanggal 1 setiap awal bulan baru tanpa menghapus data transaksi historis Anda.

---

## 4. Staf Kasir Tidak Dapat Membuka Menu Billing

### Kemungkinan Penyebab:
- Kebijakan keamanan COOCA membatasi akses menu finansial dan penagihan hanya untuk pengguna dengan izin khusus.

### Solusi Praktis:
- Ini adalah perilaku normal sistem demi menjaga kerahasiaan tagihan dan mencegah perubahan paket tanpa izin Pemilik Bisnis.
- Hanya akun bertipe **Owner** atau karyawan yang diberikan izin khusus `billing.view` dan `billing.manage` yang dapat membuka menu billing.
