# Panduan Pengguna: CRM & Program Loyalitas Pelanggan

> Modul: CRM & Loyalty (`crm`) | Tanggal Pembaruan: 2026-10-25 | Status: Resmi

Selamat datang di panduan resmi penggunaan modul **Customer Relationship Management (CRM) & Loyalty Program** di Cooca. Modul ini dirancang khusus untuk membantu pemilik bisnis (Business Owner) dan tim operasional mengelola keanggotaan pelanggan, program loyalitas poin belanja, penerbitan voucher diskon promosi, hingga pelunasan kasbon/piutang pelanggan secara tertib dan transparan.

---

## 1. Peta Navigasi & Tautan Menu

Anda dapat mengakses fitur CRM dan promosi melalui tautan langsung berikut:

| Nama Layanan | Alamat Akses (URL) | Kegunaan Utama | Hak Akses Minimal |
| :--- | :--- | :--- | :--- |
| **[Daftar Member & Loyalitas](/crm/members)** | `/crm/members` | Memantau tingkatan tier member pelanggan, saldo poin loyalitas, riwayat transaksi poin, dan penerimaan kasbon. | `crm.view` / Owner |
| **[Katalog Voucher Promosi](/crm/vouchers)** | `/crm/vouchers` | Menerbitkan kupon promosi diskon (nominal tetap atau persentase), membatasi kuota pakai, dan mengaktifkan/menonaktifkan voucher. | `crm.view` (Lihat) / `crm.manage` (Buat & Toggle) |
| **[Master Data Pelanggan](/customers)** | `/customers` | Mengelola data kontak pelanggan, alamat penagihan, plat nomor kendaraan, dan batas kredit maksimal (*credit limit*). | `customers.view` / Owner |

---

## 2. Fitur Utama & Alur Operasional

### A. Pengelolaan Member & Tingkatan Tier Loyalitas
1. Buka menu **[Daftar Member & Loyalitas](/crm/members)**.
2. Di halaman ini, Anda dapat memfilter pelanggan berdasarkan tingkatan keanggotaan:
   - **Bronze:** Member pemula dengan transaksi dasar.
   - **Silver:** Member reguler dengan akumulasi belanja menengah.
   - **Gold:** Member prioritas dengan volume transaksi tinggi.
   - **Platinum:** Member VIP utama dengan fasilitas diskon atau poin khusus.
3. Anda dapat mencari pelanggan berdasarkan Nama, Nomor Telepon/WhatsApp, atau Kode Pelanggan.

### B. Memeriksa Riwayat Poin Pelanggan (*Point History*)
1. Pada tabel member di **[Daftar Member & Loyalitas](/crm/members)**, cari pelanggan yang diinginkan.
2. Klik tombol **Riwayat Poin** pada baris pelanggan terkait.
3. Jendela modal interaktif akan menampilkan seluruh mutasi poin:
   - Tanggal dan jam perolehan/penukaran poin.
   - Jenis mutasi: Poin Masuk (*Earned* dari transaksi POS) atau Poin Keluar (*Redeemed* sebagai potongan belanja).
   - Referensi nomor struk/transaksi dan catatan mutasi.

### C. Pembayaran Kasbon & Piutang Pelanggan (*Anti-Lapping Shield*)
1. Jika pelanggan memiliki saldo kasbon (*credit balance*), tombol **Bayar Kasbon** akan aktif pada baris pelanggan.
2. Klik tombol **Bayar Kasbon**.
3. Masukkan nominal uang yang diserahkan pelanggan:
   - Nominal pembayaran tidak boleh bernilai nol atau melebihi sisa kasbon yang tercatat.
4. Pilih metode pembayaran / akun kas penerima (*Cash Account*) dan masukkan catatan (opsional, contoh: *"Titip bayar via transfer kasir siang"*).
5. Klik **Konfirmasi Pembayaran**.
6. **Perlindungan Otomatis Sistem Cooca:**
   - Saldo piutang pelanggan langsung berkurang secara akurat.
   - Sistem otomatis menjurnal penerimaan kas masuk (*CashTransaction IN*) ke akun kas toko yang dipilih.
   - Seluruh riwayat pembayaran tersimpan permanen di `AuditLog` untuk mencegah penggelapan dana kasir (*anti-lapping fraud*).

### D. Penerbitan & Pengaturan Voucher Promosi
1. Buka menu **[Katalog Voucher Promosi](/crm/vouchers)**.
2. Klik tombol **Buat Voucher Baru** di pojok kanan atas.
3. Lengkapi formulir pembuatan voucher:
   - **Kode Voucher:** Kode unik huruf kapital tanpa spasi (contoh: `PROMOHEMAT10`). Kode wajib unik untuk bisnis Anda.
   - **Nama Promo:** Judul promosi yang mudah dipahami pelanggan (contoh: *"Diskon Pembukaan Toko 10%"*).
   - **Tipe Diskon:** Pilih antara **Nominal Tetap (Rp)** atau **Persentase (%)**.
   - **Nilai Diskon:** Masukkan angka diskon (misal `10` untuk 10%, atau `25000` untuk diskon Rp 25.000).
   - **Syarat Minimum Belanja:** (Opsional) Nilai belanja terendah agar kupon dapat digunakan di kasir POS.
   - **Maksimal Potongan:** (Opsional, khusus tipe persentase) Plafon rupiah potongan tertinggi.
   - **Batas Kuota Pemakaian:** (Opsional) Jumlah maksimum kupon dapat diklaim seluruh pelanggan.
   - **Masa Berlaku:** Tanggal mulai berlaku dan batas tanggal kedaluwarsa kupon.
4. Klik **Simpan Voucher**.
5. Untuk menghentikan promosi sewaktu-waktu, Anda cukup menekan tombol status **Aktif / Nonaktif** pada kartu voucher terkait.

---

## 3. Matriks Hak Akses Karyawan (RBAC)

| Peran (Role) | Hak Akses CRM | Tindakan yang Diizinkan |
| :--- | :--- | :--- |
| **Owner / Pemilik Usaha** | Penuh (*Omnipotent*) | Melihat seluruh data, mencatat pembayaran kasbon, membuat voucher, dan mengubah status voucher. |
| **Supervisor / Manager Toko** | `crm.view` & `crm.manage` | Melihat poin member, menerima setoran kasbon, menerbitkan voucher promo, dan mengatur kuota voucher. |
| **Kasir / Staf Frontliner** | `crm.view` | Memeriksa riwayat poin member dan memeriksa status keaktifan voucher pelanggan di kasir. |

---

## 4. Terverifikasi Terhadap Route Sistem
- `GET /crm/members` -> `route('crm.members.index')` (Terverifikasi aktif)
- `GET /crm/vouchers` -> `route('crm.vouchers.index')` (Terverifikasi aktif)
- `GET /customers` -> `route('customers.index')` (Terverifikasi aktif)
