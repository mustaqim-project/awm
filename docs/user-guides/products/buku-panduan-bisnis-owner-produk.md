# Buku Panduan Bisnis Owner: Manajemen Produk, Resep HPP, & Harga Multi-Channel

| Profil Dokumen | Informasi |
| :--- | :--- |
| **Modul** | Produk & Master Data Katalog (`/products`, `/product-categories`) |
| **Target Pembaca** | Pemilik Usaha (Business Owner), General Manager, Manajer Operasional |
| **Level Teknis** | Non-Teknis / Praktis Operasional UMKM |
| **Versi Panduan** | 2.0 (Verified for Production) |

---

## 1. Mengapa Katalog Produk Sangat Krusial Bagi Bisnis Anda?
Bagi seorang pemilik usaha, katalog produk bukan sekadar daftar nama barang. Modul Produk di COOCA adalah **jantung dari seluruh arus uang dan barang**:
1. **Mengontrol Margin Keuntungan:** Menentukan HPP (Harga Pokok Penjualan) secara akurat sehingga Anda tahu pasti laba bersih dari setiap porsi atau barang yang terjual.
2. **Koneksi Otomatis ke Kasir POS:** Barang yang Anda aktifkan seketika muncul di layar kasir cabang tanpa perlu setting ulang yang rumit.
3. **Mencegah Kebocoran Stok (Formula BOM):** Penjualan 1 porsi makanan di kasir akan langsung memotong stok bahan baku mentah (gula, kopi, packaging) di gudang secara otomatis.
4. **Strategi Harga Cabang (Multi-Branch Pricing):** Menyesuaikan harga produk berbeda di outlet mall vs outlet ruko biasa.

---

## 2. Navigasi Cepat & Tautan Fitur
Berikut adalah jalan pintas ke menu-menu utama produk:
- **[Katalog Seluruh Produk](file:///c:/laragon/www/cooca_core/resources/views/app/products/index.blade.php)**: Akses daftar SKU, harga jual, dan status stok. (Rute web: `/products`)
- **[Kategori Produk](file:///c:/laragon/www/cooca_core/resources/views/app/products/index.blade.php)**: Mengelompokkan barang dagangan agar kasir mudah mencari. (Rute web: `/product-categories`)
- **[Resep BOM & HPP](file:///c:/laragon/www/cooca_core/resources/views/app/products/bom.blade.php)**: Mengatur komposisi bahan baku per menu/produk. (Rute web: `/products/{id}/bom`)
- **[Varian & Modifiers](file:///c:/laragon/www/cooca_core/resources/views/app/products/modifiers.blade.php)**: Pilihan tambahan seperti topping, level gula, ukuran cup. (Rute web: `/products/{id}/modifiers`)

---

## 3. Panduan Langkah Demi Langkah (Step-by-Step)

### A. Cara Menambahkan Produk Baru (Barang Jadi / Retail)
1. Buka menu **Produk** lalu klik tombol **+ Tambah Produk**.
2. Masukkan **Nama Produk** (contoh: *Kemeja Katun Polos L*).
3. Masukkan **SKU / Kode Barang** (contoh: *KMT-001*). Tips: Gunakan kode yang pendek dan mudah dibaca staf gudang.
4. Tentukan **Kategori** dan **Satuan Dasar** (misal: *Pcs*).
5. Masukkan **Harga Modal (HPP Estimasi)** dan **Harga Jual**. Sistem akan menampilkan estimasi margin laba secara otomatis.
6. Centang **Tampilkan di POS Kasir** jika ingin produk langsung bisa dijual kasir.
7. Klik **Simpan**.

### B. Cara Mengatur Resep BOM (Untuk Usaha F&B, Kafe, & Bakery)
1. Pada daftar produk, cari menu minuman/makanan Anda (misal: *Es Kopi Gula Aren*).
2. Klik ikon menu aksi lalu pilih **Atur Resep (BOM)**.
3. Klik **+ Tambah Bahan Baku**:
   - Pilih Bahan Baku (misal: *Espresso Shot*, takaran *30 ml*).
   - Tambah Bahan (misal: *Susu Fresh Milk*, takaran *150 ml*).
   - Tambah Bahan (misal: *Gula Aren Cair*, takaran *20 ml*).
   - Tambah Kemasan (misal: *Cup Plastik 16oz*, takaran *1 pcs*).
4. Sistem akan otomatis menghitung total biaya HPP bahan baku per porsi.
5. Saat kasir menjual 1 cup *Es Kopi Gula Aren*, stok susu berkurang 150ml dan cup berkurang 1 pcs secara otomatis di gudang cabang tersebut!

### C. Cara Mengatur Harga Khusus Per Cabang
1. Pada baris produk yang diinginkan, klik menu **Harga Cabang**.
2. Anda akan melihat daftar semua cabang atau outlet Anda.
3. Jika outlet Bandara atau Mall memiliki harga lebih tinggi, masukkan harga baru di kolom cabang tersebut.
4. Klik **Simpan Perubahan**. Kasir di cabang tersebut otomatis mengenakan harga baru, sementara cabang lain tetap memakai harga standar.

---

## 4. Matriks Implementasi Lintas Sektor Bisnis

| Jenis Sektor Usaha | Fitur Produk Utama Yang Digunakan | Manfaat Bagi Bisnis Owner |
| :--- | :--- | :--- |
| **Retail & Toko Kelontong** | Barcode Scanner, Minimum Stock Alert, Bundel Promo | Transaksi kasir secepat kilat, alarm peringatan saat stok mendekati habis. |
| **Kafe, Resto, & Bakery (F&B)** | Resep BOM, Modifiers (Level Gula/Es/Topping) | Menghilangkan kebocoran bahan baku, margin laba per porsi terhitung presisi. |
| **Jasa & Salon / Barbershop** | Katalog Jasa / Layanan, Durasi Pengerjaan | Menjadwalkan kapster/terapis tanpa perlu mengelola stok barang jadi. |
| **Konveksi & Manufaktur UMKM** | Bundel Paket, Satuan Multi-Konversi (Lusin $\rightarrow$ Pcs) | Menghitung ongkos jahit dan pemakaian kain per kodi atau rol. |

---

## 5. Tips Best Practice Keamanan & Anti-Fraud
- **Kunci Akses Edit Harga:** Hanya berikan izin `products.edit` kepada General Manager atau Supervisor terpercaya. Kasir cukup diberikan izin `products.view`.
- **Periksa Log Audit Berkala:** Buka menu Audit Log untuk melihat siapa staf yang baru saja menaikkan atau menurunkan harga jual barang.
- **Stock Opname Rutin:** Selalu lakukan pencocokan stok fisik di gudang minimal sebulan sekali berpatokan pada kode SKU di modul produk.
