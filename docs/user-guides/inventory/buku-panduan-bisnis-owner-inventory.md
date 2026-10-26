# Buku Panduan Bisnis Owner: Manajemen Stok Real-Time, Stock Opname, & Transfer Antar-Gudang

| Profil Dokumen | Informasi |
| :--- | :--- |
| **Modul** | Inventory (Stok Real-Time, Mutasi, Stock Opname, & Transfer Gudang) |
| **Target Pembaca** | Pemilik Usaha (Business Owner), Manajer Gudang, Manajer Logistik & Operasional |
| **Level Teknis** | Non-Teknis / Praktis Operasional UMKM |
| **Versi Panduan** | 2.0 (Verified for Production) |

---

## 1. Mengapa Kontrol Stok Adalah Kunci Utama Menjaga Keuangan Bisnis Anda?
Bagi seorang pemilik usaha, **stok barang di gudang adalah uang tunai yang berwujud barang**. Kehilangan stok sama persis dengan kehilangan uang dari brankas:
1. **Mencegah Kebocoran Stok & Fraud Staf:** Tanpa pencatatan mutasi yang ketat, staf dapat mengklaim barang rusak (*damaged*) atau hilang padahal dibawa pulang.
2. **Menghindari Kerugian Akibat Barang Kadaluwarsa / Mati (*Dead Stock*):** Pantau barang yang lambat berputar (*slow-moving*) dan nilai valuasi aset yang tertahan di gudang.
3. **Mencegah Kasir Kehabisan Barang (*Stockout*):** Indikator stok menipis otomatis memperingatkan Anda sebelum pelanggan kecewa karena menu atau barang dagangan habis.
4. **Kepastian Multi-Cabang:** Mengetahui dengan pasti berapa sisa stok di Gudang Utama, Outlet 1, dan Outlet 2 tanpa harus menelepon staf satu per satu.

---

## 2. Navigasi Cepat & Tautan Fitur
Berikut adalah jalan pintas ke menu-menu utama inventori:
- **[Daftar Stok Real-Time](file:///c:/laragon/www/cooca_core/resources/views/app/inventory/stocks.blade.php)**: Pantau jumlah stok, status aman/menipis, dan valuasi aset. (Rute web: `/inventory/stocks`)
- **[Buku Besar Mutasi Stok](file:///c:/laragon/www/cooca_core/resources/views/app/inventory/movements.blade.php)**: Riwayat lengkap keluar-masuk barang fisik. (Rute web: `/inventory/movements`)
- **[Stock Opname (Hitung Fisik)](file:///c:/laragon/www/cooca_core/resources/views/app/inventory/opname.blade.php)**: Penghitungan fisik berkala, lembar hitung kosong, dan rekonsiliasi selisih. (Rute web: `/inventory/opnames`)
- **[Transfer Stok Antar-Gudang](file:///c:/laragon/www/cooca_core/resources/views/app/inventory/transfers.blade.php)**: Distribusi pasokan barang dari gudang pusat ke cabang. (Rute web: `/inventory/transfers`)

---

## 3. Panduan Langkah Demi Langkah (Step-by-Step)

### A. Cara Memantau Stok & Mengatasi Peringatan Stok Menipis
1. Buka menu **Inventory** $\rightarrow$ pilih tab **Stok**.
2. Perhatikan kartu metrik di bagian atas:
   - **Total Valuasi Stok:** Total nilai aset modal yang tersimpan di gudang dalam Rupiah.
   - **Peringatan Stok Menipis:** Jumlah item yang stoknya sudah berada di bawah batas minimal (`min_stock`).
3. Pada tabel stok:
   - Badge **Aman (Hijau)**: Kuantitas stok mencukupi untuk operasional harian.
   - Badge **Menipis (Oranye)**: Kuantitas stok segera habis. Segera buat **Purchase Order (PO)** ke supplier melalui modul Purchasing!

---

### B. Cara Melakukan Stock Opname (Hitung Fisik Berkala Tanpa Stres)
Untuk memastikan catatan komputer sama persis dengan barang nyata di rak gudang:
1. **Cetak Lembar Hitung Kosong (*Blank Counting Sheet*):**
   - Pada tab **Stock Opname**, klik tombol **Unduh Lembar Kosong**.
   - Berikan lembar cetak ini kepada staf gudang. Staf tidak melihat angka sistem sehingga mereka wajib menghitung fisik secara jujur.
2. **Input Hasil Hitungan Fisik:**
   - Setelah staf selesai menghitung rak, klik **+ Buat Stock Opname**.
   - Pilih **Lokasi Gudang**, masukkan tanggal, dan tambahkan item barang beserta angka fisik yang ditemukan.
   - Anda juga dapat menggunakan opsi **Unggah Excel** jika varian barang sangat banyak.
   - Klik **Simpan Data Opname**.
3. **Review Selisih & Rekonsiliasi Otomatis:**
   - Sistem akan otomatis menghitung selisih kuantitas dan nilai kerugian/kelebihan uang.
   - Jika selisih telah diverifikasi dan wajar, klik tombol centang hijau **Rekonsiliasi**.
   - Seketika stok sistem diperbarui agar sama dengan fisik, dan selisihnya dicatat otomatis ke laporan akuntansi laba-rugi!

---

### C. Cara Transfer Stok dari Gudang Pusat ke Outlet Cabang
1. Buka menu **Inventory** $\rightarrow$ pilih tab **Transfer Stok**.
2. Klik tombol **+ Buat Transfer Stok**.
3. Pilih **Gudang Asal** (misal: *Gudang Pusat Cikarang*) dan **Gudang Tujuan** (misal: *Outlet Braga Bandung*).
4. Masukkan tanggal pengiriman dan pilih produk serta jumlah yang dimuat ke truk/kurir.
5. Klik **Kirim Transfer**.
   - Stok di gudang asal langsung terpotong.
   - Status transfer menjadi **Pending** (barang sedang dalam perjalanan / *in-transit*).
6. Saat truk tiba di outlet cabang, supervisor outlet membuka aplikasi dan mengklik tombol **Konfirmasi Terima**.
   - Stok di cabang tujuan bertambah otomatis dan siap dijual oleh kasir!

---

### D. Cara Koreksi Stok Cepat (Quick Adjust) & Proteksi PIN Supervisor
Jika terjadi botol sirup pecah atau kemasan bocor di toko:
1. Pada daftar stok, klik ikon geser/slider **Sesuaikan Stok**.
2. Masukkan jumlah stok fisik yang tersisa.
3. Pilih alasan (contoh: *Rusak / Pecah*, *Kadaluwarsa*, *Selisih Opname*).
4. **Proteksi Anti-Kecurangan:**
   - Jika kerugian melebihi ambang batas (contoh: pengurangan di atas 10 unit atau nilai di atas Rp 100.000), sistem otomatis meminta **PIN Supervisor 6-digit**.
   - Jika nilai kerugian sangat besar, sistem akan menahannya menjadi status **Menunggu Persetujuan (Pending Approval)** sehingga kasir tidak dapat memanipulasi stok tanpa persetujuan Anda!

---

## 4. Tips Mengamankan Gudang dari COOCA
1. **Lakukan Opname Parsial Mingguan:** Jangan menunggu akhir tahun untuk opname. Hitung 5 barang paling laris (*fast-moving*) setiap hari Senin pagi sebelum toko buka.
2. **Jangan Berikan Hak `inventory.manage` ke Sembarang Staf:** Staf lapangan cukup diberikan hak baca (`inventory.view`). Hak mutasi dan rekonsiliasi opname hanya untuk Kepala Gudang atau Owner.
3. **Cek Buku Besar Mutasi (*Stock Movements*):** Jika ada kejanggalan stok, filter berdasarkan nama produk di menu Mutasi untuk melihat siapa staf yang login, kapan barang keluar, dan dari transaksi apa barang tersebut berkurang.
