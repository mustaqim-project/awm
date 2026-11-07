# Panduan Pengguna - Tim AI Bisnis & Asisten Cerdas (`cooca-ai`)

> Modul: `cooca-ai` | Versi: `2.0 (Verified)` | Tanggal Verifikasi: 2026-11-07 | Status: **Resmi (Verified for Production)**

Selamat datang di Panduan Resmi Tim AI Bisnis dan Asisten Cerdas COOCA. Sistem ini menghadirkan kolaborasi otonom agen eksekutif cerdas (CEO, CFO, COO, CMO) dan Asisten Kasir POS untuk menganalisis data keuangan, memprediksi stok barang habis, mendeteksi potensi kecurangan (*fraud*), serta menyusun proposal tindakan bisnis siap eksekusi.

---

## 1. Peta Navigasi Modul & Rute Resmi Terverifikasi

Seluruh tautan fitur di bawah ini telah diverifikasi langsung terhadap rute sistem (`routes/owner.php`):

| Menu Navigasi | Rute URL | Rute Name | Hak Akses Utama | Fungsi & Kegunaan |
| :--- | :--- | :--- | :---: | :--- |
| **🏢 Markas Eksekutif (Lobby)** | [`/cooca-ai/office`](https://cooca.id/cooca-ai/office) | `cooca-ai.office.view` | `ai.access` | Cockpit komando tim AI, metrik omzet real-time, dan alur kerja strategis (/teamwork, /goal, /grill-me). |
| **💬 Asisten Kasir POS AI** | [`/cooca-ai/pos`](https://cooca.id/cooca-ai/pos) | `cooca-ai.pos` | `ai.access` | Tanya jawab intelijen transaksi kasir, analisis anomali void/diskon, dan kalkulasi HPP otomatis. |
| **👔 Ruang Direksi (Executive)** | [`/cooca-ai/office/executive`](https://cooca.id/cooca-ai/office/executive) | `cooca-ai.office.executive` | `ai.access` | Kantor CEO & CFO: perumusan target laba bersih, evaluasi cashflow, dan strategi ekspansi. |
| **📦 Ruang Operasional & Logistik** | [`/cooca-ai/office/operations`](https://cooca.id/cooca-ai/office/operations) | `cooca-ai.office.operations` | `ai.access` | Kantor COO & Gudang: peringatan stok menipis, PO supplier otomatis, dan efisiensi pengiriman. |
| **🚀 Ruang Kreatif & Pertumbuhan** | [`/cooca-ai/office/growth`](https://cooca.id/cooca-ai/office/growth) | `cooca-ai.office.growth` | `ai.access` | Kantor CMO & Sales: analisis tren produk terlaris, promo omnichannel, dan customer retention. |
| **✅ Pusat Persetujuan (Action Center)** | [`/cooca-ai/actions`](https://cooca.id/cooca-ai/actions) | `cooca-ai.actions` | `ai.approve` | Maker-checker review proposal tindakan AI: setujui, revisi nominal, atau tolak aksi bisnis. |
| **📜 Riwayat Sesi Kerja AI** | [`/cooca-ai/history`](https://cooca.id/cooca-ai/history) | `cooca-ai.history` | `ai.access` | Audit log lengkap percakapan, rapat tim, durasi eksekusi, dan token terpakai. |
| **⚙️ Pengaturan Provider AI** | [`/cooca-ai/providers`](https://cooca.id/cooca-ai/providers) | `cooca-ai.providers` | `ai.manage` | Konfigurasi multi-provider (Gemini, GPT-4o, Claude, Ollama, Platform Engine) dan uji koneksi API. |

---

## 2. Panduan Tugas Berdasarkan Peran Staf (*Role-Based SOP*)

### A. Peran: Pemilik Usaha / Direktur (Business Owner)
1. **Memulai Rapat Strategis Tim AI (/teamwork):**
   - Buka menu [`/cooca-ai/office`](https://cooca.id/cooca-ai/office).
   - Masukkan topik tantangan bisnis Anda (contoh: *"Omzet kasir turun 15% minggu ini dan stok sirup menumpuk"*).
   - Klik **Jalankan Rapat Tim**.
   - Periksa konsensus eksekutif dari CEO, CFO, COO, dan CMO beserta proposal tindakan yang diajukan.
2. **Menyetujui Proposal Tindakan di Action Center:**
   - Buka menu [`/cooca-ai/actions`](https://cooca.id/cooca-ai/actions).
   - Teliti estimasi biaya dan tingkat risiko (*Low, Medium, High*).
   - Klik **Setujui & Eksekusi** untuk menjalankan aksi (seperti pembuatan draf Purchase Order atau penyesuaian harga).

### B. Peran: Supervisor Toko & Kasir
1. **Memeriksa Anomali Transaksi via Asisten AI POS:**
   - Buka menu [`/cooca-ai/pos`](https://cooca.id/cooca-ai/pos).
   - Klik chip pertanyaan cepat: **Cek Kecurangan Kasir** atau **Prediksi Stok Habis**.
   - Sistem akan menyajikan daftar transaksi mencurigakan (banyak void/refund) dan item yang harus segera di-restock.

### C. Peran: IT & Admin Sistem
1. **Menghubungkan Kunci API AI (BYOAI):**
   - Buka menu [`/cooca-ai/providers`](https://cooca.id/cooca-ai/providers).
   - Pilih provider yang diinginkan (misal Google Gemini atau OpenAI).
   - Masukkan kunci API resmi Anda, lalu klik **Uji Koneksi**.
   - Centang **Aktifkan Provider** dan klik **Simpan Perubahan**.

---

## 3. Daftar Artikel Terkait

- [Buku Panduan Bisnis Owner: Manajemen AI & Otomasi](buku-panduan-bisnis-owner-ai.md)
- [Tanya Jawab Operasional (FAQ)](faq.md)
- [Panduan Pemecahan Masalah (Troubleshooting)](troubleshooting.md)

