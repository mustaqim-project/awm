# Panduan Pengguna: Master Data Pelanggan & Direktori Klien

> Modul: Pelanggan (`customers`) | Tanggal Pembaruan: 2026-10-25 | Status: Resmi

Selamat datang di panduan resmi penggunaan modul **Master Data Pelanggan (Customers Directory)** di Cooca. Modul ini berfungsi sebagai pusat pengelolaan seluruh basis data klien, pembeli ritel, pelanggan korporat B2B, pelanggan bengkel/servis kendaraan, hingga pengaturan limit kasbon dan integrasi impor/ekspor data Excel secara massal.

---

## 1. Peta Navigasi & Tautan Menu

Anda dapat mengakses fitur pelanggan dan modul terkait melalui menu navigasi utama atau tautan langsung berikut:

| Nama Layanan | Alamat Akses (URL) | Kegunaan Utama | Hak Akses Minimal |
| :--- | :--- | :--- | :--- |
| **[Direktori Pelanggan](/customers)** | `/customers` | Memantau seluruh basis data pelanggan, statistik hero bento (total klien, pelanggan korporat, rata-rata tempo, saldo piutang berjalan), serta pencarian data kontak. | `customers.view` / Owner |
| **[Ekspor Data Pelanggan](/customers/export)** | `/customers/export` | Mengunduh seluruh daftar kontak pelanggan aktif ke dalam berkas Excel (.xlsx) dengan proteksi sanitasi formula. | `customers.view` / Owner |
| **[Unduh Template Impor](/customers/template)** | `/customers/template` | Mengunduh format standar berkas Excel untuk pengisian data pelanggan massal. | `customers.view` / Owner |
| **[Program Loyalitas & Member](/crm/members)** | `/crm/members` | Mengelola tingkatan tier keanggotaan (Bronze, Silver, Gold, Platinum), mutasi saldo poin loyalitas, dan pelunasan kasbon. | `crm.view` / Owner |
| **[Katalog Voucher Promosi](/crm/vouchers)** | `/crm/vouchers` | Menerbitkan kupon diskon promosi toko berbasis nominal tetap atau persentase belanja. | `crm.view` / Owner |

---

## 2. Fitur Utama & Alur Operasional

### A. Menambahkan Pelanggan Baru Secara Manual
1. Buka menu **[Direktori Pelanggan](/customers)**.
2. Klik tombol utama **Tambah Pelanggan Baru** di pojok kanan atas.
3. Jendela formulir modal (*Apple HIG Floating Dialog*) akan muncul. Lengkapi informasi yang dibutuhkan:
   - **Nama Lengkap / Nama Kontak (Wajib):** Nama perseorangan atau penanggung jawab pembeli.
   - **Nama Perusahaan / Instansi (Opsional):** Jika pelanggan merupakan badan usaha/B2B (contoh: *PT Maju Bersama*).
   - **Kode Pelanggan (Opsional):** Kode unik penanda member toko Anda (contoh: `CUST-001`). Kode ini dijamin unik per bisnis Anda.
   - **Nomor Telepon / WhatsApp:** Nomor kontak aktif pelanggan untuk pengiriman struk digital atau notifikasi penagihan tempo.
   - **Email:** Alamat surat elektronik pelanggan.
   - **Alamat Penagihan & Pengiriman:** Alamat lengkap untuk pencetakan surat jalan dan faktur penjualan.
   - **Data Kendaraan (Khusus Bengkel/Otomotif):** Plat nomor kendaraan, model/tipe kendaraan, dan odometer jarak tempuh.
   - **Syarat Pembayaran (Payment Terms):** Standar tempo jatuh tempo pembayaran dalam satuan hari (contoh: *30 hari* untuk Net 30, atau *0 hari* untuk pembayaran tunai di tempat/Cash on Delivery).
4. Klik **Simpan Data Pelanggan**. Data pelanggan baru akan langsung tersimpan dan terekam di log audit.

### B. Mengubah Data & Kontak Pelanggan
1. Pada tabel daftar pelanggan di **[Direktori Pelanggan](/customers)**, temukan pelanggan yang ingin diperbarui.
2. Klik tombol aksi **Edit** (ikon pensil).
3. Perbarui informasi yang diperlukan pada modal formulir edit.
4. Klik **Perbarui Pelanggan**. Seluruh perubahan data kontak dan alamat langsung tersinkronisasi ke seluruh faktur yang akan diterbitkan.

### C. Impor Pelanggan Massal via Excel (*Smart Import Engine*)
1. Masuk ke halaman **[Direktori Pelanggan](/customers)** lalu klik tombol **Impor Excel**.
2. Unduh template resmi jika belum memilikinya melalui tautan **[Unduh Template Impor](/customers/template)**.
3. Buka file template di Microsoft Excel atau Google Sheets, isi data pelanggan sesuai kolom yang tersedia, lalu simpan file.
4. Unggah berkas Excel tersebut ke area dropzone jendela impor Cooca.
5. Sistem akan menampilkan **Pratinjau Impor (Preview)**:
   - Menghitung jumlah data valid.
   - Menandai baris yang memiliki format salah atau duplikasi nomor telepon/kode.
6. Pilih strategi penanganan duplikat:
   - **Lewati (Skip):** Data yang sudah ada tidak akan diubah.
   - **Perbarui (Update):** Data pelanggan yang cocok akan diperbarui informasinya.
7. Klik **Jalankan Impor**. Pelanggan berhasil ditambahkan secara massal dalam hitungan detik.

### D. Menghapus Data Pelanggan & Perlindungan Kasbon (*Deletion Guard*)
1. Pelanggan dapat dihapus dengan menekan tombol **Hapus** pada baris pelanggan terkait.
2. **Proteksi Keamanan Finansial Cooca (*Anti-Loss Shield*):**
   - Sistem Cooca melarang keras penghapusan pelanggan yang masih memiliki sisa saldo kasbon atau piutang berjalan (`current_credit_balance > 0`).
   - Jika pengguna mencoba menghapus pelanggan yang masih berhutang, sistem akan menolak permintaan dengan pesan peringatan: *"Tidak dapat menghapus pelanggan yang masih memiliki saldo piutang/kasbon berjalan sebesar Rp ... Selesaikan pelunasan terlebih dahulu."*
   - Pelanggan hanya dapat dihapus jika kewajiban kasbon telah lunas 100%.

---

## 3. Matriks Hak Akses Karyawan (RBAC)

| Peran (Role) | Hak Akses Pelanggan | Tindakan yang Diizinkan |
| :--- | :--- | :--- |
| **Owner / Pemilik Usaha** | Penuh (*Omnipotent*) | Melihat, menambah, mengedit, menghapus pelanggan, ekspor Excel, dan impor massal. |
| **Manager Penjualan** | `customers.view`, `customers.create`, `customers.edit` | Melihat direktori, menambah pelanggan baru, mengupdate data kontak, dan ekspor data klien. |
| **Kasir / Staf Administrasi** | `customers.view`, `customers.create` | Mencari data pelanggan di kasir POS dan mendaftarkan pembeli baru saat transaksi. |
| **Staf Gudang / Logistik** | `customers.view` | Memeriksa alamat pengiriman dan plat nomor armada kendaraan untuk pengantaran pesanan. |

---

## 4. Tautan Menu Terverifikasi
- `GET /customers` -> `route('customers.index')` (Terverifikasi aktif)
- `GET /customers/export` -> `route('customers.export')` (Terverifikasi aktif)
- `GET /customers/template` -> `route('customers.template')` (Terverifikasi aktif)
- `GET /crm/members` -> `route('crm.members.index')` (Terverifikasi aktif)
- `GET /crm/vouchers` -> `route('crm.vouchers.index')` (Terverifikasi aktif)
