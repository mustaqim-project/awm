# Panduan Penyelesaian Masalah (Troubleshooting) - HRM & Penggajian

Dokumen ini memuat solusi langkah-demi-langkah atas kendala operasional yang paling sering dialami pemilik usaha (**Business Owner**) dan staf dalam penggunaan modul HRM, presensi biometrik wajah, dan penggajian di COOCA.

---

## 1. Staf Mengalami Notifikasi "Di Luar Radius Lokasi Toko" Saat Presensi
### Penyebab:
Sistem geofencing COOCA menghitung jarak antara posisi GPS ponsel karyawan dengan koordinat fisik outlet toko Anda menggunakan formula Haversine. Jika ponsel karyawan berada di dalam ruangan tertutup (misal mall, ruko berdinding tebal, atau basement), sinyal satelit GPS dapat mengalami deviasi (*drift*) sehingga ponsel mendeteksi lokasi beberapa puluh meter lebih jauh dari titik sebenarnya.

### Solusi Pemilik Usaha:
1. Buka tab [Lokasi & Geofence GPS](/hrm?tab=locations).
2. Periksa titik latitude dan longitude toko Anda. Pastikan pin peta tepat berada di pintu masuk toko.
3. Tingkatkan nilai **Radius Toleransi**:
   - Untuk toko di pinggir jalan raya terbuka: radius **30 s/d 50 meter** sudah cukup.
   - Untuk outlet di dalam pusat perbelanjaan (mall) atau gedung bertingkat: disarankan mengatur radius **75 s/d 100 meter**.
4. Minta staf mengaktifkan mode GPS "Akurasi Tinggi" (*High Accuracy Location*) dan menyalakan Wi-Fi ponsel untuk membantu kalibrasi posisi.

---

## 2. Verifikasi Wajah Gagal / Notifikasi "Wajah Tidak Cocok"
### Penyebab:
Sistem pencocokan biometrik wajah mendeteksi kemiripan vektor (*cosine similarity*) di bawah ambang batas validitas, atau sistem *anti-spoofing* mendeteksi pantulan cahaya layar ponsel lain.

### Solusi:
1. Pastikan staf mengambil foto di area dengan pencahayaan yang cukup (tidak membelakangi lampu/backlight).
2. Lepaskan kacamata hitam, topi, atau masker yang menutupi area mata, hidung, dan mulut.
3. Hindari mengambil foto dari layar perangkat lain atau foto cetak, karena algoritma liveness COOCA akan otomatis menolak upaya tersebut.
4. Jika staf melakukan perubahan penampilan drastis (misal potongan rambut atau bentuk wajah), pemilik usaha dapat melakukan pendaftaran ulang template wajah staf melalui menu **Daftarkan Wajah Baru** di tab [Karyawan & Profil Gaji](/hrm?tab=employees).

---

## 3. Notifikasi "Batas Kuota Anggota Staf Tercapai" Saat Menambah Karyawan
### Penyebab:
Paket langganan gratis (**Free Solo**) memiliki batas kapasitas jumlah anggota tim per bisnis. Jika kapasitas telah tercapai, sistem akan memblokir penambahan karyawan baru demi menjaga performa akun.

### Solusi:
1. Tingkatkan paket toko Anda ke paket **Cooca Pro** atau **Corporate** untuk menikmati kuota staf tanpa batas.
2. Jika ada staf lama yang sudah tidak lagi bekerja di toko Anda, Anda dapat menghapus atau menonaktifkan akun staf tersebut dari tab [Karyawan & Profil Gaji](/hrm?tab=employees) untuk mengosongkan slot kuota.

---

## 4. Potongan Kasbon Tidak Muncul Pada Slip Gaji Bulanan
### Penyebab:
- Kasbon belum dalam status **Aktif (Disetujui)**.
- Tenor cicilan telah lunas pada bulan sebelumnya.
- Pembuatan batch penggajian dilakukan sebelum transaksi kasbon dicatat.

### Solusi:
1. Buka tab [Buku Besar Kasbon Staf](/hrm?tab=loans).
2. Pastikan pinjaman staf bersangkutan berstatus **Aktif** dan memiliki sisa saldo yang belum lunas.
3. Jika draf penggajian sudah terlanjur dibuat sebelum kasbon dicatat, hapus draf penggajian tersebut di menu [Penggajian Bulanan](/hrm?tab=payrolls) dan klik **Buat Penggajian Baru** ulang agar kalkulator membaca potongan kasbon terbaru.

---

## 5. Batch Penggajian Sudah Terlanjur Ditandai "DIBAYAR" Tapi Ada Kesalahan Nominal
### Penjelasan Sistem:
Demi menjaga integritas pembukuan akuntansi dan mencegah kekacauan saldo kasbon, batch penggajian yang sudah berstatus **DIBAYAR (Paid)** dikunci secara permanen dan tidak dapat diedit atau dihapus secara langsung.

### Solusi:
1. Jangan menghapus data secara paksa di basis data.
2. Jika terjadi kelebihan bayar (*overpaid*): Catat selisih lebih tersebut sebagai pinjaman kasbon baru pada tab [Buku Besar Kasbon Staf](/hrm?tab=loans) agar otomatis dipotong pada gaji bulan berikutnya.
3. Jika terjadi kekurangan bayar (*underpaid*): Lakukan transfer susulan ke rekening staf dan catat pembayarannya melalui menu [Beban Operasional](/finance/expenses) kategori "Beban Gaji Karyawan".

---

## 6. Tautan Slip Gaji Digital di WhatsApp Tidak Dapat Dibuka oleh Karyawan
### Penyebab:
- Koneksi internet karyawan terputus.
- Tautan terpapar spasi atau terpotong saat disalin manual dari aplikasi chat.

### Solusi:
1. Buka detail penggajian di [Penggajian Bulanan](/hrm?tab=payrolls).
2. Klik nama karyawan untuk membuka slip digital.
3. Klik tombol resmi **Kirim via WhatsApp**. Gunakan pesan otomatis yang digenerate oleh sistem tanpa mengubah URL token yang terlampir.

---

## 7. Muncul Tanda Kutip Tunggal (') di Depan Nomor Rekening atau Nama di Excel
### Penjelasan:
Ini adalah fitur keamanan resmi COOCA bernama **Spreadsheet Formula Injection Defense (CWE-1236)**. Tanda kutip tunggal (`'`) otomatis ditambahkan di depan data yang berawalan karakter formula seperti `+62...`, `=`, `-`, atau `@` agar aplikasi Excel tidak mengeksekusi data tersebut sebagai rumus komputasi berbahaya. Tanda kutip ini hanya terlihat saat sel diedit dan tidak mempengaruhi nilai transfer perbankan.

---

## 8. Navigasi & Panduan Lainnya
- Ingin melihat panduan alur kerja lengkap? Buka [Panduan Utama HRM](index.md).
- Memiliki pertanyaan umum lainnya? Buka [Pertanyaan Umum (FAQ)](faq.md).
