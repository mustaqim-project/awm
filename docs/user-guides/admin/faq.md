# FAQ (Frequently Asked Questions) - Modul Platform Backoffice & Superadmin (`admin`)

> Modul: `admin` | Pertanyaan Umum Staf Operasional & Jawaban Solutif | Tanggal: 2026-10-26

---

### Q1: Apakah fitur impersonasi ke tenant akan mengubah atau merusak data operasional bisnis pengguna?
**Jawaban:**
Tidak. Fitur impersonasi hanya mengalihkan sudut pandang penglihatan (*view session*) administrator agar dapat melihat antarmuka dan laporan persis seperti yang dilihat oleh pemilik bisnis terkait. Namun demikian, administrator dihimbau untuk hanya melakukan tindakan investigasi (*read-only*) dan dilarang membuat transaksi fiktif atau menghapus produk milik tenant tanpa persetujuan tertulis dari pemilik bisnis.

---

### Q2: Bagaimana cara menambahkan nomor rekening bank transfer manual baru untuk pembayaran langganan?
**Jawaban:**
1. Masuk ke menu [`/admin/payment-accounts`](https://cooca.id/admin/payment-accounts).
2. Klik tombol **Tambah Rekening Baru**.
3. Pilih nama bank (misal: BCA, Mandiri, BRI, BNI), masukkan nomor rekening resmi PT/organisasi, dan nama pemilik rekening.
4. Pastikan opsi status dicentang **Aktif**.
5. Klik **Simpan**. Nomor rekening tersebut seketika akan muncul pada daftar pilihan metode transfer manual di halaman checkout langganan pengguna ([`/billing/checkout`](https://cooca.id/billing/checkout)).

---

### Q3: Berapa lama berkas dokumen identitas KTP dan NIB pemulihan akun disimpan di sistem?
**Jawaban:**
Berkas identitas disimpan pada direktori penyimpanan privat terlindungi selama tiket berstatus `pending`. Setelah permohonan disetujui (*approved*) atau ditolak (*rejected*), berkas akan diarsipkan dan dapat dibersihkan secara aman sesuai kebijakan retensi data 90 hari guna mematuhi prinsip privasi data dan perlindungan data pribadi (UU PDP).

---

### Q4: Apakah Superadmin bisa membatalkan persetujuan pemulihan akun jika ternyata pemohon adalah penipu?
**Jawaban:**
Ya. Jika terindikasi adanya sengketa kepemilikan akun atau kesalahan verifikasi:
1. Superadmin dapat langsung membuka menu [`/admin/businesses`](https://cooca.id/admin/businesses) dan mengklik tombol **Nonaktifkan Bisnis (Suspend)** untuk membekukan akses sementara.
2. Buka menu [`/admin/users`](https://cooca.id/admin/users), cari akun terkait, dan lakukan pergantian email/sandi darurat.

---

### Q5: Bagaimana cara membuat paket promo uji coba gratis (*Free Trial Promo*) untuk UMKM baru?
**Jawaban:**
1. Buka menu [`/admin/billing-packages`](https://cooca.id/admin/billing-packages).
2. Klik **Tambah Paket Baru**.
3. Beri nama paket (misal: *Promo Spesial UMKM Juara*), masukkan harga `0` (nol rupiah), dan atur durasi hari (misal `30` hari).
4. Simpan paket. Sistem secara otomatis mengenali paket berharga nol sebagai paket promo gratis dan menampilkannya di laman checkout tanpa mewajibkan pembayaran gateway.

---

### Q6: Apa perbedaan kuota token AI Platform dengan sistem BYOAI (Bring Your Own AI)?
**Jawaban:**
- **Token AI Platform:** Disediakan dan ditagihkan melalui paket berlangganan COOCA (dapat dipantau di menu [`/admin/ai-tokens`](https://cooca.id/admin/ai-tokens)).
- **BYOAI:** Pengguna memasukkan kunci API resmi milik mereka sendiri (misal dari Google Gemini atau OpenAI) pada modul Cooca AI. Pada skema BYOAI, pemakaian token sepenuhnya gratis di platform COOCA karena biaya token ditagihkan langsung oleh Google/OpenAI ke kartu kredit pemilik bisnis.

---

### Q7: Apakah staf admin biasa bisa mengubah peran (*role*) administrator lainnya?
**Jawaban:**
Tidak. Pengelolaan hak akses, pembuatan role baru, dan penetapan izin 92 permission di menu [`/admin/roles`](https://cooca.id/admin/roles) secara ketat dibatasi hanya untuk Super Administrator (`isSuperAdmin() === true`). Hal ini mencegah terjadinya eskalasi hak akses (*Privilege Escalation*) oleh staf non-pimpinan.

---

### Q8: Bagaimana cara mengaktifkan Mode Pemeliharaan (*Maintenance Mode*) pada platform secara aman?
**Jawaban:**
1. Masuk ke menu [`/admin/settings`](https://cooca.id/admin/settings) tab **Pengaturan Sistem**.
2. Aktifkan toggle **Mode Pemeliharaan (Maintenance Mode)** dan cantumkan pesan estimasi waktu selesai.
3. Klik **Simpan Pengaturan**. Seluruh pengguna publik dan tenant yang mengakses situs akan melihat laman informasi pemeliharaan yang bersahabat (*No-Panic UI*), sementara administrator yang telah terotentikasi di `/admin` tetap dapat mengakses backoffice secara normal.

---

### Q9: Mengapa tombol Ekspor Data Pengguna ke CSV tidak menghasilkan unduhan?
**Jawaban:**
Pastikan peramban (*browser*) Anda tidak memblokir jendela *pop-up* otomatis dari domain `cooca.id`. Fitur ekspor CSV di [`/admin/users/export`](https://cooca.id/admin/users/export) menyajikan berkas *streamed response* langsung. Jika data pengguna sangat besar (>10.000 user), tunggu 3-5 detik hingga proses streaming data selesai dikompilasi oleh server.

---

### Q10: Bagaimana cara membersihkan log galat server yang menumpuk di `/admin/error-logs`?
**Jawaban:**
Buka menu [`/admin/error-logs`](https://cooca.id/admin/error-logs). Administrator yang memiliki izin `admin.error_logs.delete` dapat mengklik tombol **Bersihkan Semua Log Error**. Tindakan ini akan mengosongkan berkas log yang sudah terselesaikan tanpa memengaruhi kueri basis data atau data transaksi bisnis.
