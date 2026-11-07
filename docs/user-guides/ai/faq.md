# FAQ (Frequently Asked Questions) - Modul Tim AI Bisnis & Asisten Cerdas (`cooca-ai`)

> Modul: `cooca-ai` | Kategori: Pertanyaan Umum Pemilik Usaha & Operasional | Tanggal: 2026-11-07

---

### Q1: Apakah asisten AI bisa secara otomatis mengeluarkan uang atau memesan barang tanpa izin saya?
**Jawaban:**
**Sama sekali tidak bisa.** Sistem COOCA menerapkan arsitektur keamanan ketat **Maker-Checker**. AI hanya bertindak sebagai *Maker* (perancang draf proposal atau surat pesanan). Eksekusi pesanan atau pengeluaran dana hanya akan berjalan jika Anda (*Checker*) mengklik tombol **Setujui** di menu [`/cooca-ai/actions`](https://cooca.id/cooca-ai/actions).

---

### Q2: Apakah saya harus membayar biaya langganan tambahan untuk menggunakan fitur AI?
**Jawaban:**
Tergantung pada mode yang Anda pilih:
- **Mode Platform AI Token:** Kuota token AI bulanan telah disertakan dalam paket langganan SaaS COOCA aktif Anda.
- **Mode BYOAI (Bring Your Own AI):** Anda dapat memasukkan kunci API resmi milik Anda sendiri (misal dari Google Gemini atau OpenAI) pada menu [`/cooca-ai/providers`](https://cooca.id/cooca-ai/providers). Pada mode ini, Anda tidak membayar biaya token ke COOCA, melainkan ditagihkan langsung oleh Google/OpenAI sesuai pemakaian riil dengan tarif sangat terjangkau (seringkali gratis untuk kuota awal).
- **Mode Offline Fallback:** Jika koneksi internet internet atau saldo API habis, sistem otomatis beralih ke *Engine Rule-Based* lokal tanpa biaya tambahan.

---

### Q3: Bagaimana cara kerja deteksi kecurangan kasir (*fraud detection*)?
**Jawaban:**
AI membaca data transaksi kasir secara berkala dan mencari pola-pola anomali:
1. Kasir yang memiliki rasio pembatalan nota (*void*) jauh di atas rata-rata kasir lain.
2. Pemberian diskon manual berulang kali pada jam-jam sepi.
3. Pembukaan laci uang (*drawer open*) tanpa diiringi penyimpanan transaksi tunai.
Semua temuan ini dirangkum dalam kartu peringatan di menu [`/cooca-ai/pos`](https://cooca.id/cooca-ai/pos).

---

### Q4: Bisakah staf kasir saya melihat hasil rapat direksi AI di Lobi Eksekutif?
**Jawaban:**
Tidak. Akses modul AI diatur ketat oleh Controller-Level RBAC. Staf kasir hanya memiliki izin operasional kasir, sementara akses ke Lobi Eksekutif, pengaturan provider, dan persetujuan proposal hanya dapat dibuka oleh pengguna yang memiliki role Owner atau Manager dengan permission `ai.access`, `ai.approve`, dan `ai.manage`.

---

### Q5: Apakah data penjualan dan omzet bisnis saya aman dan tidak bocor ke publik?
**Jawaban:**
**Sangat Aman.** Data bisnis Anda diisolasi per penyewa (*multi-tenant isolation*). Prompt yang dikirimkan ke model AI hanya menyertakan data agregat yang dienkripsi sementara, tanpa menyimpan data pribadi pelanggan atau kredensial perbankan Anda di server publik pihak ketiga.

