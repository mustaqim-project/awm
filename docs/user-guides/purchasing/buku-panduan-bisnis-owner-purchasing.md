# Buku Panduan Bisnis Owner: Manajemen Pengadaan, Penerimaan Barang, & Pembayaran Supplier

| Profil Dokumen | Informasi |
| :--- | :--- |
| **Modul** | Purchasing (Pengadaan, Tagihan Supplier, Penerimaan, & Retur) |
| **Target Pembaca** | Pemilik Usaha (Business Owner), Manajer Pengadaan/Purchasing, Manajer Keuangan |
| **Level Teknis** | Non-Teknis / Praktis Operasional UMKM |
| **Versi Panduan** | 2.0 (Verified for Production) |

---

## 1. Mengapa Pengadaan (Purchasing) Menentukan Kelangsungan Bisnis Anda?
Bagi pemilik usaha, laba tidak hanya ditentukan oleh seberapa banyak penjualan, melainkan seberapa cerdas dan terkontrol uang yang keluar untuk belanja barang dagangan dan bahan baku:
1. **Mencegah Kebocoran Dana & Fraud Pembelian:** Tanpa sistem PO (Purchase Order) resmi, staf dapat memesan barang fiktif atau mengubah harga kesepakatan dengan supplier secara sepihak.
2. **Pencocokan 3 Titik (3-Way Matching):** Memastikan Anda hanya membayar tagihan sesuai dengan **apa yang dipesan** dan **apa yang benar-benar diterima fisik di gudang**.
3. **Fleksibilitas UMKM (1-Klik Masuk Stok):** Untuk belanja mendadak di pasar tradisional atau toko grosir terdekat, COOCA menyediakan alur kilat tanpa birokrasi berbelit.
4. **Perhitungan HPP Otomatis:** Saat harga beli bahan baku dari supplier naik, sistem otomatis memperbarui rata-rata harga pokok (*moving average cost*) sehingga margin laba Anda tetap terjaga.

---

## 2. Navigasi Cepat & Tautan Fitur
Berikut adalah jalan pintas ke menu-menu utama pengadaan:
- **[Purchase Orders (Pesanan Pembelian)](file:///c:/laragon/www/cooca_core/resources/views/app/purchase-orders/index.blade.php)**: Buat dan pantau surat pesanan resmi ke pemasok. (Rute web: `/purchasing/purchase-orders`)
- **[Buat Purchase Order Baru](file:///c:/laragon/www/cooca_core/resources/views/app/purchase-orders/create.blade.php)**: Formulir pesanan belanja bahan baku / produk. (Rute web: `/purchasing/purchase-orders/create`)
- **[Tagihan Supplier (Bills)](file:///c:/laragon/www/cooca_core/resources/views/app/purchasing/bills/index.blade.php)**: Daftar invoice pemasok, tanggal jatuh tempo, dan pencatatan pembayaran. (Rute web: `/purchasing/bills`)
- **[Retur Pembelian (Returns)](file:///c:/laragon/www/cooca_core/resources/views/app/purchasing/returns/index.blade.php)**: Pengembalian barang rusak (*defect*) atau kedaluwarsa ke supplier. (Rute web: `/purchasing/returns`)

---

## 3. Panduan Langkah Demi Langkah (Step-by-Step)

### A. Alur Standar Pengadaan (Untuk Bisnis Berkembang & Menengah)
1. **Buat Purchase Order (PO):**
   - Buka menu **Purchasing** $\rightarrow$ klik **+ Buat Purchase Order**.
   - Pilih **Supplier**, tentukan **Gudang Tujuan** penerimaan barang, dan tanggal estimasi kedatangan.
   - Tambahkan item barang beserta jumlah dan harga beli yang disepakati.
   - Klik **Simpan Draft** atau langsung **Konfirmasi Pesanan**.
2. **Kirim PO ke Supplier:**
   - Cetak surat pesanan resmi atau unduh file PDF bebas emoji dengan kop bisnis Anda untuk dikirim via WhatsApp / Email ke supplier.
3. **Penerimaan Barang Fisik di Gudang (Goods Receipt):**
   - Saat kurir/truk supplier tiba membawa barang, staf gudang membuka detail PO tersebut.
   - Klik tombol **Terima Barang**.
   - Masukkan jumlah barang yang dalam kondisi baik. Jika ada barang pecah/rusak, kurangi jumlahnya.
   - Klik **Simpan Penerimaan**. Stok gudang bertambah otomatis seketika!
4. **Penerimaan Faktur & Pembayaran (Supplier Invoice):**
   - Dari PO yang sudah diterima barangnya, klik **Buat Tagihan Supplier**.
   - Masukkan nomor faktur resmi dari supplier dan tanggal jatuh tempo.
   - Saat Anda mentransfer uang ke rekening supplier, klik **Catat Pembayaran**, pilih metode (Transfer Bank / Kas), dan simpan. Hutang usaha lunas dan tercatat di buku kas.

---

### B. Alur Kilat Pengadaan (Instant Stock In - Untuk UMKM Cepat)
Khusus operasional UMKM ritel dan F&B yang sering belanja tunai langsung ke pasar:
1. Buka menu **Purchasing** $\rightarrow$ **+ Buat Purchase Order**.
2. Masukkan item belanjaan dan total uang yang dibayarkan.
3. Pilih opsi **1-Klik Beli & Langsung Masuk Stok**.
4. Sistem seketika mengonfirmasi PO, mencatat penerimaan fisik 100%, memasukkan barang ke stok gudang, dan membuat tagihan lunas dalam satu klik efisien!

---

### C. Alur Retur Pembelian ke Supplier (Barang Rusak / Tidak Sesuai)
1. Buka menu **Purchasing** $\rightarrow$ pilih tab **Retur Pembelian**.
2. Klik **+ Buat Retur Pembelian**.
3. Pilih PO / Supplier terkait dan pilih barang yang akan dikembalikan.
4. Masukkan alasan retur (contoh: *Kemasan bocor saat pengiriman*).
5. Setelah disetujui (Approval Supervisor), stok gudang akan otomatis dipotong dan saldo piutang / nota kredit (*debit note*) supplier tercatat di sistem keuangan.

---

## 4. Tips Keamanan & Anti-Fraud dari COOCA
1. **Gunakan Otorisasi Berjenjang:** Berikan hak akses `purchasing.view` kepada staf belanja lapangan, namun batasi hak `purchasing.manage` hanya untuk Manajer atau Pemilik Usaha.
2. **Pantau Tab Audit Log:** Setiap penerimaan barang dan pembayaran invoice supplier dicatat identitas staf yang mengeksekusi, jam eksekusi, serta alamat IP-nya.
3. **Periksa Tanggal Jatuh Tempo di Tab Bills:** Manfaatkan filter *Akan Jatuh Tempo* pada menu Tagihan Supplier untuk menjaga reputasi kredit bisnis Anda di mata para pemasok utama.
