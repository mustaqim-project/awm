# Frequently Asked Questions (FAQ) - Platform Feedback & Bug Support

Panduan jawaban atas pertanyaan yang sering diajukan seputar pelaporan kendala teknis (Bug Report) dan pengajuan ide pengembangan fitur (Feature Request) di platform COOCA.

---

## 1. Umum & Aksesibilitas

### Siapa saja yang berhak mengakses dan mengirimkan feedback?
Modul Feedback dirancang khusus untuk pemilik usaha (**Role: Owner**) atau pengguna dengan hak akses administratif penuh. Hal ini memastikan bahwa laporan kendala operasional dan ide pengembangan fitur berasal langsung dari pengambil keputusan bisnis dan terhubung secara valid dengan kuota serta profil tenant usaha Anda.

### Di mana saya dapat melihat status laporan yang pernah saya buat?
Anda dapat meninjau seluruh tiket kendala teknis melalui daftar [Laporan Bug](/feedback/bugs) dan ide perbaikan melalui daftar [Ide Fitur](/feedback/features). Setiap tiket dilengkapi penanda status interaktif (*Open*, *Under Review*, *In Progress*, *Resolved*, atau *Closed*).

---

## 2. Pelaporan Bug (Kendala Teknis)

### Kapan saya harus melaporkan kendala sebagai "Bug"?
Laporkan sebagai bug jika:
1. Terjadi galat (*error*) berupa layar kosong, peringatan sistem 500, atau transaksi gagal diproses.
2. Fitur aplikasi tidak berfungsi sebagaimana mestinya (misalnya: cetak struk POS macet, total diskon tidak terpotong, sinkronisasi marketplace gagal).
3. Terjadi inkonsistensi data pada laporan keuangan atau stok barang.

Silakan buka formulir [Laporkan Kendala Sistem](/feedback/bugs/create) untuk mulai membuat laporan.

### Mengapa bukti tangkapan layar (screenshot) sangat penting?
Tangkapan layar membantu tim insinyur COOCA melihat tampilan persis galat yang Anda alami beserta pesan error teknisnya. Sertakan juga URL halaman atau langkah-langkah (*steps to reproduce*) sebelum galat terjadi.

### Format dan batasan ukuran berkas apa yang diizinkan untuk lampiran?
Sistem menerima berkas gambar dan dokumen dengan format **JPG, JPEG, PNG, dan PDF** dengan ukuran maksimal **5 MB** per berkas. Sistem keamanan kami secara otomatis memblokir file script berbahaya seperti `.php`, `.exe`, `.js`, atau `.sh`.

---

## 3. Pengajuan Ide Fitur (Feature Request)

### Apa bedanya Laporan Bug dengan Usulan Fitur Baru?
- **Bug Report:** Digunakan untuk melaporkan kegagalan pada fitur yang **sudah ada** agar segera diperbaiki.
- **Feature Request:** Digunakan untuk mengajukan modul, tombol, alur kerja, atau integrasi baru yang **belum ada** di COOCA guna menunjang produktivitas bisnis Anda. Buka formulir pengajuan di [Ajukan Fitur Baru](/feedback/features/create).

### Bagaimana COOCA memprioritaskan fitur yang diajukan?
Setiap usulan fitur dievaluasi oleh Product Team berdasarkan:
1. Tingkat urgensi dan dampak operasional bagi efisiensi UMKM.
2. Frekuensi permintaan fitur serupa dari berbagai pemilik usaha lainnya.
3. Kesesuaian dengan peta jalan (*roadmap*) inovasi jangka panjang COOCA.

---

## 4. Keamanan, Kuota & Privasi Data

### Apakah lampiran tiket saya menghitung kuota penyimpanan tenant?
Ya, berkas gambar atau PDF yang diunggah akan tercatat dalam kuota penyimpanan berkas tenant bisnis Anda melalui modul Storage Tracking. Pastikan kuota penyimpanan bisnis Anda mencukupi sebelum mengunggah dokumen bervolume besar.

### Apakah laporan saya dapat dibaca oleh tenant atau toko lain?
**Tidak.** Seluruh data tiket laporan bug, ide fitur, dan riwayat diskusinya diproteksi ketat menggunakan arsitektur *Multi-Tenant Isolation* dengan `business_id` scoping dan Controller-Level Authorization. Bisnis lain tidak memiliki akses apa pun terhadap data operasional Anda.

### Apakah setiap aktivitas pelaporan tercatat dalam audit log?
Ya. Setiap pembuatan tiket laporan bug (`feedback.bug_reported`) maupun ide fitur (`feedback.feature_requested`) secara otomatis tercatat permanen di tabel `audit_logs` lengkap dengan ID pemilik, stempel waktu, dan metadata pelaporan untuk transparansi kepatuhan bisnis.

---

## 5. Bantuan Cepat Tambahan

Jika Anda mengalami kendala saat mengirim tiket atau mendapati pesan kesalahan kuota, silakan pelajari panduan pemecahan masalah di [Panduan Troubleshooting Feedback](troubleshooting.md) atau kembali ke [Ringkasan Modul Feedback](index.md).
