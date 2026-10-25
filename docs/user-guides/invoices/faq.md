# Pertanyaan Umum (FAQ) - Modul Faktur Penjualan & Piutang

Berikut adalah jawaban atas pertanyaan yang paling sering diajukan mengenai modul Faktur Penjualan (*Invoices*) di COOCA:

---

### 1. Apa perbedaan antara Faktur Draf (DRAFT) dan Faktur Terbit (SENT / UNPAID)?
- **Faktur Draf:** Dokumen tagihan yang masih dalam tahap persiapan atau penawaran. Stok barang di gudang **belum terpotong**, dan jurnal piutang **belum dicatat**. Draf dapat disunting bebas atau dihapus jika transaksi batal.
- **Faktur Terbit:** Dokumen tagihan resmi yang telah disahkan dan dikirim ke pelanggan. Stok barang **sudah terpotong** dari lokasi gudang terpilih, dan jurnal piutang dagang sudah aktif tercatat di buku besar akuntansi.

### 2. Apakah saya bisa menghapus faktur yang sudah terbit?
**Tidak bisa.** Demi mematuhi prinsip akuntansi dan kepatuhan perpajakan (*audit compliance*), faktur yang telah dirilis atau telah memiliki catatan pembayaran dilarang dihapus secara permanen. Jika transaksi dibatalkan, Anda wajib menggunakan fitur **Batalkan Faktur (VOID)**. Tindakan VOID akan mengembalikan stok barang ke gudang secara otomatis dan menolkan nilai piutang dengan bukti catatan audit yang jelas.

### 3. Mengapa tombol hapus hanya ada pada faktur draf?
Penghapusan fisik faktur terbit akan menyebabkan nomor faktur melompat (*missing sequence*), selisih mutasi stok gudang, dan ketidakcocokan saldo neraca keuangan. Oleh karena itu, tombol **Hapus Draf** hanya disediakan untuk draf yang belum pernah diterbitkan ke pelanggan.

### 4. Bagaimana cara membuat faktur dari Pesanan Pelanggan (Purchase Order / PO)?
1. Masuk ke halaman [Buat Faktur Baru](/invoices/create).
2. Pada bagian atas formulir, pilih nomor referensi PO pada dropdown **Purchase Order Pelanggan**.
3. Sistem otomatis mengisi nama pelanggan, daftar item barang, kuantitas yang dipesan, dan harga satuan dari dokumen PO tersebut.

### 5. Bagaimana cara mengirimkan tagihan faktur ke WhatsApp pelanggan?
1. Buka halaman detail faktur bersangkutan melalui [/invoices](/invoices).
2. Klik tombol **Kirim Pengingat Termin**.
3. Pilih saluran **WhatsApp** dan klik **Kirim Pengingat**.
4. Sistem membuka jendela WhatsApp dengan pesan penagihan resmi yang memuat nomor faktur, nama bisnis Anda, rincian sisa tagihan, tanggal jatuh tempo, dan informasi nomor rekening tujuan transfer.

### 6. Apakah COOCA mendukung faktur dengan PPN dan diskon bertingkat?
**Ya.** Setiap baris item barang dapat diberikan diskon individual (nominal atau persentase). Selain itu, pada level subtotal faktur, Anda dapat menerapkan diskon faktur global, menentukan tarif pajak PPN (misal 11%), serta menambahkan ongkos kirim. Seluruh kalkulasi dihitung otomatis secara presisi.

### 7. Bagaimana jika pelanggan membayar tagihan secara mencicil (pembayaran bertahap)?
Gunakan tombol **Catat Pembayaran** setiap kali pelanggan menyetor dana. Masukkan nominal yang diterima, tanggal transfer, dan nama bank penerima. Sistem akan otomatis menghitung sisa saldo tagihan (`balance_due`) dan mengubah status faktur menjadi `PARTIALLY_PAID` hingga pelunasan penuh tercapai.

---

## Tautan Terkait
- [Kembali ke Panduan Utama Pengguna Invoices](index.md)
- [Panduan Penyelesaian Masalah (Troubleshooting)](troubleshooting.md)
