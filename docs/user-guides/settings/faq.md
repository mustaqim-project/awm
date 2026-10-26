# Tanya Jawab Umum (FAQ) - Pengaturan Bisnis & MCP

> Modul: Pengaturan Bisnis & Ekosistem AI MCP | Target Pembaca: Pemilik Usaha (Business Owner)

Berikut adalah kumpulan pertanyaan yang paling sering diajukan oleh pemilik bisnis mengenai fitur Pengaturan Bisnis dan Integrasi Kecerdasan Buatan (AI MCP) di COOCA.

---

### Q1: Apa itu Model Context Protocol (MCP) dan apa keuntungannya bagi bisnis saya?
**Jawaban:**
Model Context Protocol (MCP) adalah standar komunikasi terbuka internasional yang memungkinkan asisten AI seperti Claude, ChatGPT, dan Gemini untuk terhubung secara langsung dan aman ke basis data bisnis Anda.

Dengan MCP, Anda tidak perlu lagi mengunduh laporan Excel lalu mengunggahnya secara manual ke chat AI. Anda cukup bertanya langsung di aplikasi Claude atau ChatGPT Anda:
- *"Berapa laba bersih kafe saya minggu ini?"*
- *"Produk pakaian apa yang stoknya menipis dan harus segera saya restock?"*
- *"Buatkan draf promo WhatsApp untuk pelanggan yang sudah 30 hari tidak berbelanja."*
Asisten AI akan membaca data terkini secara *real-time* dari COOCA dan memberikan analisis bisnis instan.

---

### Q2: Apakah aman menghubungkan data usaha saya ke Claude, ChatGPT, atau Gemini?
**Jawaban:**
Sangat aman. COOCA menerapkan standar keamanan enterprise berlapis:
1. **Hash Satu Arah (SHA-256):** Token akses AI Anda disimpan dalam bentuk acak terenkripsi yang tidak dapat dibaca oleh siapa pun.
2. **Pembatasan Hak Akses (*Abilities*):** Anda memiliki kendali penuh untuk menentukan data apa saja yang boleh dilihat oleh AI. Jika Anda hanya memberi izin membaca katalog produk, AI sama sekali tidak dapat melihat data rekening atau pelanggan Anda.
3. **Master Kill Switch:** Anda dapat memutus seluruh koneksi AI dalam 1 detik dengan satu klik saklar darurat.
4. **Jejak Audit Permanen:** Setiap kali AI memanggil data toko Anda, sistem mencatat waktu, nama perkakas yang dipanggil, dan statusnya di [Log Aktivitas MCP](/settings/integrations/mcp/logs).

---

### Q3: Provider AI apa saja yang resmi didukung oleh COOCA?
**Jawaban:**
Untuk menjamin keandalan data dan privasi bisnis Anda, COOCA membatasi integrasi hanya pada 3 raksasa AI paling tepercaya dan teruji di dunia:
1. **Claude (Anthropic):** Didukung penuh untuk Claude Desktop dan Claude Code.
2. **ChatGPT (OpenAI):** Didukung penuh via Custom GPT Actions dan OAuth 2.0 Connectors.
3. **Gemini (Google):** Didukung penuh via Google Antigravity dan Gemini Extensions.

Provider eksperimental atau skrip pihak ketiga yang tidak aman telah dibersihkan secara tuntas dari sistem.

---

### Q4: Siapa saja anggota tim saya yang dapat mengakses menu Pengaturan Bisnis?
**Jawaban:**
Hanya pengguna dengan izin khusus:
- **Owner (Pemilik Usaha):** Memiliki akses penuh tanpa batas ke seluruh menu pengaturan, rekening bank, aktivasi modul, dan integrasi AI.
- **Manajer dengan Izin `settings.edit`:** Dapat mengelola jam operasional cabang dan profil toko, namun **tidak dapat** mengubah template modul inti atau menghapus akun pemilik.
- **Staf Kasir / Staf Biasa:** Secara ketat **dilarang masuk** ke menu Pengaturan demi menjaga kerahasiaan keuangan dan PIN supervisor toko.

---

### Q5: Berapa banyak cabang outlet yang bisa saya buat di COOCA?
**Jawaban:**
Jumlah cabang yang dapat Anda buat bergantung pada paket langganan aktif bisnis Anda:
- **Paket Solo (Gratis):** Mendukung 1 outlet utama.
- **Paket Pro / Growth:** Mendukung hingga 3 sampai 5 cabang outlet.
- **Paket Corporate / Enterprise:** Mendukung cabang tanpa batas (*unlimited branches*).
Jika kuota cabang Anda telah habis, sistem akan memberikan notifikasi ramah untuk melakukan upgrade paket langganan di menu Billing.

---

### Q6: Apa perbedaan antara tipe lokasi Outlet, Toko Fisik, dan Dapur Sentral?
**Jawaban:**
- **Outlet / Toko Fisik:** Tempat bertemunya kasir dengan pelanggan langsung. Memiliki kasir POS aktif, melayani pembayaran tunai/QRIS, dan menjadi titik presensi staf kasir.
- **Dapur Sentral (Central Kitchen / Gudang):** Tempat pengolahan bahan baku atau penyimpanan stok grosir. Tidak melayani transaksi kasir eceran, namun dapat mendistribusikan barang ke outlet cabang via fitur Transfer Stok.

---

### Q7: Mengapa perubahan rekening bank dan PIN supervisor kasir dicatat secara khusus?
**Jawaban:**
Rekening bank dan PIN kasir adalah area paling rawan terhadap kecurangan (*fraud* internal). COOCA menerapkan perlindungan ketat: setiap kali ada perubahan nomor rekening atau PIN, sistem otomatis membuat rekaman *Audit Log* tingkat tinggi (`RISK_HIGH`) yang mencatat siapa pelakunya, alamat IP, waktu tepatnya, dan perbandingan nilai lama vs nilai baru. Hal ini melindungi pemilik usaha dari potensi pengalihan uang toko oleh oknum nakal.

---

### Q8: Apakah COOCA mengenakan biaya langganan tambahan untuk fitur MCP?
**Jawaban:**
Tidak ada biaya tambahan dari COOCA untuk menggunakan fitur Cockpit Integrasi AI MCP. Fitur ini disertakan secara gratis bagi pengguna COOCA. Namun, Anda mungkin memerlukan akun berlangganan langsung dari penyedia AI (seperti Anthropic Claude Pro atau OpenAI ChatGPT Plus) sesuai kebijakan masing-masing vendor AI tersebut.

---

### Q9: Apa yang harus saya lakukan jika laptop staf yang terhubung ke AI hilang atau dicuri?
**Jawaban:**
Lakukan 3 langkah cepat:
1. Buka [Cockpit Integrasi AI MCP](/settings/integrations/mcp) dari smartphone Anda.
2. Geser **Master Switch** ke posisi Nonaktif untuk memutus seluruh koneksi seketika.
3. Cari token yang terpasang di laptop tersebut pada tabel, lalu klik tombol **Hapus / Cabut (Revoke)**.
Setelah token dicabut, laptop yang hilang tersebut tidak akan pernah bisa mengakses data toko Anda lagi. Anda dapat mengaktifkan kembali Master Switch untuk perangkat lainnya yang aman.

---

### Q10: Ke mana saya dapat menghubungi tim bantuan jika mengalami kesulitan?
**Jawaban:**
Tim bantuan COOCA siap membantu Anda:
- **Pusat Bantuan & Tiket:** Buka menu Dukungan di portal dashboard COOCA.
- **WhatsApp Customer Support:** Hubungi nomor resmi customer success COOCA di jam kerja operasional (08:00 - 20:00 WIB).
- **Panduan Terkait:** Baca juga [Panduan Pengguna Utama](./index.md) dan [Panduan Troubleshooting](./troubleshooting.md).
