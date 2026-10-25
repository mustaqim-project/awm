# Tanya Jawab Sering Ditanyakan (FAQ): CRM & Loyalitas Pelanggan

> Modul: CRM & Loyalty (`crm`) | Tanggal Pembaruan: 2026-10-25 | Status: Resmi

Berikut adalah kumpulan pertanyaan umum yang sering ditanyakan oleh pemilik bisnis (Business Owner) dan manajer operasional seputar modul CRM & Loyalty di Cooca.

---

### Q1: Bagaimana cara pelanggan memperoleh poin belanja (loyalty points)?
**Jawab:**
Pelanggan secara otomatis mendapatkan poin saat melakukan transaksi belanja di kasir (POS) Cooca, selama data pelanggan dipilih pada nota transaksi kasir. Rasio perolehan poin ditentukan berdasarkan aturan program loyalitas yang berlaku di pengaturan bisnis Anda. Seluruh penambahan poin dapat dipantau di halaman **[Daftar Member & Loyalitas](/crm/members)** melalui tombol **Riwayat Poin**.

---

### Q2: Apakah batas kasbon (credit limit) pelanggan bisa diubah?
**Jawab:**
Ya, bisa. Batas plafon kasbon maksimal pelanggan dapat ditentukan dan diubah kapan saja melalui menu **[Master Data Pelanggan](/customers)**. Jika pelanggan sudah mencapai batas maksimal limit kreditnya, kasir POS secara otomatis akan memblokir opsi metode pembayaran kasbon (*utang*) sampai pelanggan melakukan pelunasan di menu **[Daftar Member & Loyalitas](/crm/members)**.

---

### Q3: Bagaimana Cooca mencegah kasir menggelapkan setoran kasbon pelanggan (Anti-Lapping Shield)?
**Jawab:**
Modul CRM Cooca menerapkan perlindungan *Anti-Lapping*:
1. Setiap pencatatan pelunasan kasbon wajib diverifikasi secara atomik ke akun kas toko yang sah (`CashAccount`).
2. Sistem otomatis menerbitkan mutasi kas masuk (`CashTransaction`) bertipe `IN`.
3. Rekaman transaksi diverifikasi dan dicatat permanen ke `AuditLog` dengan detail ID kasir yang bertugas, tanggal transaksi, nominal, dan sisa saldo pelanggan.
4. Pemilik usaha dapat mencocokkan total uang di laci kasir dengan riwayat audit penerimaan kasbon secara transparan.

---

### Q4: Apakah kode voucher bisa digunakan berkali-kali oleh orang yang berbeda?
**Jawab:**
Bisa, selama kuota total penggunaan (`usage_limit`) yang Anda atur pada saat pembuatan voucher belum habis dan tanggal transaksi belum melewati masa berlaku voucher. Anda dapat memantau jumlah pemakaian terkini langsung pada kartu voucher di halaman **[Katalog Voucher Promosi](/crm/vouchers)**.

---

### Q5: Bisakah voucher diskon hanya berlaku untuk member tier tertentu (misal khusus Gold dan Platinum)?
**Jawab:**
Ya. Kolom `tier_eligibility` pada data voucher memungkinkan Anda membatasi kupon diskon eksklusif bagi pelanggan dengan tier tertentu, misalnya hanya member Gold atau Platinum, guna meningkatkan retensi pelanggan VIP toko Anda.

---

### Q6: Bagaimana jika toko sebelah menggunakan kode voucher yang persis sama dengan toko saya?
**Jawab:**
Tidak ada masalah. Setiap voucher di Cooca diisolasi secara ketat berdasarkan identitas bisnis Anda (*multi-tenant isolation*). Kode voucher yang dibuat oleh bisnis lain tidak akan pernah terlihat, tertukar, atau bisa diklaim oleh pelanggan bisnis Anda.

---

## Tautan Terkait
- **[Daftar Member & Loyalitas](/crm/members)**
- **[Katalog Voucher Promosi](/crm/vouchers)**
- **[Master Data Pelanggan](/customers)**
