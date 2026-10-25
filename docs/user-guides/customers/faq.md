# Tanya Jawab Sering Ditanyakan (FAQ): Master Data Pelanggan

> Modul: Pelanggan (`customers`) | Tanggal Pembaruan: 2026-10-25 | Status: Resmi

Berikut adalah kumpulan pertanyaan umum yang sering diajukan oleh pemilik bisnis (Business Owner) dan tim administrasi terkait pengelolaan data pelanggan di Cooca.

---

### Q1: Apa perbedaan antara menu Pelanggan dengan menu Member CRM?
**Jawab:**
- **[Direktori Pelanggan](/customers):** Merupakan buku kontak induk (*master directory*) untuk seluruh klien, mencakup kontak personal, profil perusahaan B2B, alamat penagihan/pengiriman, NPWP, dan data armada kendaraan.
- **[Program Loyalitas & Member](/crm/members):** Merupakan subsistem keanggotaan promosi (*CRM & Loyalty*) yang berfokus pada akumulasi saldo poin belanja, tingkatan tier keanggotaan (Bronze, Silver, Gold, Platinum), dan penerimaan pembayaran kasbon.

Setiap pelanggan yang Anda buat di menu Pelanggan otomatis dapat diberikan poin dan voucher di menu CRM.

---

### Q2: Mengapa sistem melarang saya menghapus pelanggan yang masih memiliki hutang?
**Jawab:**
Cooca menerapkan fitur perlindungan finansial (*Deletion Guard*). Menghapus pelanggan yang masih memiliki sisa saldo piutang (`current_credit_balance > 0`) dapat menyebabkan ketidakseimbangan pada neraca akuntansi toko dan hilangnya catatan siapa yang berhutang. Anda wajib melunasi kasbon pelanggan terlebih dahulu di menu **[Program Loyalitas & Member](/crm/members)** sebelum dapat menghapus data pelanggan tersebut.

---

### Q3: Berapa jumlah maksimal pelanggan yang dapat disimpan di Cooca?
**Jawab:**
Jumlah pelanggan disesuaikan dengan paket langganan aktif bisnis Anda. Paket standar bisnis UMKM dapat menampung ribuan pelanggan, sedangkan paket Korporat/Enterprise mendukung penambahan data pelanggan tanpa batas (*unlimited*). Anda dapat memeriksa kuota pemakaian pelanggan kapan saja di dashboard.

---

### Q4: Apakah aman membuka file ekspor data pelanggan di Microsoft Excel?
**Jawab:**
Sangat aman. Seluruh data teks pelanggan yang diunduh melalui tautan **[Ekspor Data Pelanggan](/customers/export)** telah diproteksi dengan mekanisme **Sanitasi Formula Spreadsheet (CsvSanitizer)**. Karakter berbahaya seperti `=`, `+`, `-`, atau `@` yang dimasukkan pada nama pelanggan otomatis dinetralkan sehingga file Excel tidak akan mengeksekusi makro atau perintah berbahaya saat dibuka.

---

### Q5: Bagaimana cara mengatur tempo pembayaran (misal 14 hari atau 30 hari) untuk pelanggan grosir?
**Jawab:**
Saat menambah atau mengedit pelanggan di menu **[Direktori Pelanggan](/customers)**, masukkan angka hari pada kolom **Syarat Pembayaran (Payment Terms)** (contoh: `14` untuk tempo dua minggu, atau `30` untuk satu bulan). Ketika Anda menerbitkan faktur penjualan untuk pelanggan tersebut, sistem akan otomatis menghitung tanggal jatuh tempo faktur sesuai jumlah hari yang telah disetel.

---

## Tautan Terkait
- **[Direktori Pelanggan](/customers)**
- **[Ekspor Data Pelanggan](/customers/export)**
- **[Unduh Template Impor](/customers/template)**
- **[Program Loyalitas & Member](/crm/members)**
- **[Katalog Voucher Promosi](/crm/vouchers)**
