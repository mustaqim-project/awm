# Panduan Pemecahan Masalah (Troubleshooting) - approvals

> Modul: approvals | Tanggal Pembaruan: 2026-10-25 | Status: Resmi

Panduan ini membantu pemilik bisnis dan staf dalam mendiagnosis dan mengatasi kendala umum yang terjadi seputar persetujuan dan otorisasi dokumen transaksi di Cooca.

---

## Matriks Gejala, Penyebab, dan Solusi

### 1. Muncul Pesan "Anda tidak memiliki hak otorisasi untuk menyetujui dokumen pada level ini"
- **Gejala:** Pengguna mengklik tombol "Setujui" namun sistem menampilkan peringatan merah atau status ditolak (HTTP 403).
- **Penyebab:** 
  1. Pengguna yang login adalah staf pembuat draf (*Maker*). Sistem sengaja melarang pembuat draf menyetujui dokumennya sendiri demi mencegah fraud.
  2. Peran akun Anda (misal Kasir/Staf) tidak sesuai dengan tingkatan otorisasi yang disyaratkan (misal dokumen sedang menunggu otorisasi Level 2: Manager).
- **Langkah Penyelesaian:**
  1. Minta rekan kerja yang menjabat peran setingkat (Supervisor/Manager) untuk membuka **[Kotak Masuk Persetujuan](/approvals)**.
  2. Jika dalam keadaan mendesak, Pemilik Bisnis (*Owner*) dapat langsung menyetujui dokumen tersebut karena Owner memiliki hak override penuh.

---

### 2. Transaksi Terhenti dan Tidak Dapat Dikonfirmasi
- **Gejala:** Tombol konfirmasi pesanan pengadaan (*Purchase Order*) atau pembayaran tagihan supplier tidak dapat diklik atau muncul pesan *"Pesanan masih menunggu otorisasi persetujuan"*.
- **Penyebab:** Transaksi tersebut bernilai di atas ambang batas plafon yang ditentukan di **[Aturan Plafon Approval](/settings/approval-rules)** dan belum disetujui hingga tingkat terakhir.
- **Langkah Penyelesaian:**
  1. Periksa status tiket di **[Kotak Masuk Persetujuan](/approvals)**.
  2. Pastikan seluruh tingkatan (Level 1, Level 2, atau Level 3) telah membubuhkan tanda persetujuan.
  3. Setelah status tiket berubah menjadi **Disetujui Sepenuhnya (Approved)**, tombol konfirmasi pada dokumen asli akan otomatis aktif kembali.

---

### 3. Kotak Masuk Persetujuan Kosong Padahal Staf Sudah Membuat Pengajuan
- **Gejala:** Pemilik toko membuka `/approvals` namun tidak menemukan tiket permohonan baru.
- **Penyebab:**
  1. Skala usaha bisnis Anda disetel sebagai UMKM (`umkm`) dan Anda belum mendaftarkan aturan plafon di **[Aturan Plafon Approval](/settings/approval-rules)**. Pada kondisi ini, Cooca secara cerdas meloloskan transaksi secara langsung agar bisnis Anda tidak terbebani birokrasi.
  2. Nilai transaksi draf berada di bawah nominal minimal aturan plafon aktif.
  3. Dokumen tersebut telah ditolak atau telah disetujui sebelumnya.
- **Langkah Penyelesaian:**
  1. Cek menu **[Riwayat Otorisasi](/approvals/history)** untuk melihat apakah dokumen sudah selesai diproses.
  2. Jika Anda ingin semua transaksi di atas nominal tertentu wajib persetujuan, daftarkan aturan baru di **[Aturan Plafon Approval](/settings/approval-rules)**.

---

### 4. Gagal Menolak Dokumen Karena Catatan Kosong
- **Gejala:** Pengguna mengklik tombol "Tolak Dokumen" tetapi form tidak terkirim atau muncul pesan *"Catatan alasan penolakan wajib diisi untuk rekam jejak audit pimpinan"*.
- **Penyebab:** Kolom alasan penolakan belum diisi atau kurang dari 3 karakter.
- **Langkah Penyelesaian:**
  1. Ketikkan penjelasan yang jelas mengapa dokumen ditolak (contoh: *"Kelebihan kuota stok gudang"*).
  2. Minimal teks adalah 3 karakter. Setelah terisi, tombol penolakan akan aktif.

---

## Saluran Eskalasi Dukungan
Jika kendala teknis berlanjut, hubungi tim dukungan Cooca dengan menyertakan:
1. ID Dokumen atau Nomor Tiket Otorisasi (contoh: `PO-202610-001` atau UUID Tiket).
2. Nama dan email akun yang digunakan saat mencoba menyetujui dokumen.
3. Tangkapan layar (*screenshot*) pesan error yang ditampilkan.
