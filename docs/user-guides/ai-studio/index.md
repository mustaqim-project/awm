# Panduan Pengguna - AI Studio Visual & Inspirasi Viral (`ai-studio`)

> Modul: `ai-studio` | Versi: `2.0 (Verified)` | Tanggal Verifikasi: 2026-11-07 | Status: **Resmi (Verified for Production)**

Selamat datang di Panduan Resmi AI Studio Visual dan Generator Materi Promosi COOCA. Modul ini dirancang bagi pemilik usaha (*Business Owner*), tim kreatif, dan pengelola toko untuk merancang visual katalog produk beresolusi tinggi, banner promosi omnichannel, serta materi media sosial viral tanpa memerlukan sesi photoshoot studio yang mahal.

---

## 1. Peta Navigasi Modul & Rute Resmi Terverifikasi

Seluruh tautan fitur di bawah ini telah diverifikasi langsung terhadap rute sistem (`routes/owner.php`):

| Menu Navigasi | Rute URL | Rute Name | Hak Akses Utama | Fungsi & Kegunaan |
| :--- | :--- | :--- | :---: | :--- |
| **🎨 Galeri & Proyek Visual** | [`/ai-studio`](https://cooca.id/ai-studio) | `ai-studio.index` | `ai_studio.generate` / `ai_studio.manage` | Dashboard utama proyek visual, ringkasan saldo kredit, dan katalog hasil render. |
| **✨ Buat Proyek Visual Baru** | [`/ai-studio/create`](https://cooca.id/ai-studio/create) | `ai-studio.create` | `ai_studio.generate` | Form inisiasi proyek foto produk, pemilihan rasio aspek (1:1, 9:16, 16:9), dan unggah foto referensi. |
| **💡 Hub Inspirasi Komersial** | [`/ai-studio/inspiration`](https://cooca.id/ai-studio/inspiration) | `ai-studio.inspiration.index` | `ai_studio.generate` | Galeri kurasi visual komersial lintas industri; salin prompt gaya pencahayaan dan konsep foto viral. |
| **📁 Papan Koleksi Pribadi** | [`/ai-studio/inspiration/collections`](https://cooca.id/ai-studio/inspiration/collections) | `ai-studio.inspiration.collections.index` | `ai_studio.generate` | Kelola moodboard ide kampanye visual produk per tema atau per musim promo. |
| **🖼️ Pustaka Referensi Saya** | [`/ai-studio/inspiration/library`](https://cooca.id/ai-studio/inspiration/library) | `ai-studio.inspiration.my-library` | `ai_studio.generate` | Arsip aset foto mentah produk dan foto packaging yang diunggah langsung oleh bisnis Anda. |
| **❤️ Aset Favorit Tersimpan** | [`/ai-studio/inspiration/favorites`](https://cooca.id/ai-studio/inspiration/favorites) | `ai-studio.inspiration.favorites` | `ai_studio.generate` | Daftar gaya visual dan prompt inspirasi yang Anda tandai dengan bintang/hati. |
| **🪙 Riwayat & Saldo Kredit** | [`/ai-studio/credits`](https://cooca.id/ai-studio/credits) | `ai-studio.credit-history` | `ai_studio.manage` | Buku besar pemakaian kredit render, pengembalian otomatis (*refund*), dan riwayat top up. |

---

## 2. Panduan Tugas Berdasarkan Peran Staf (*Role-Based SOP*)

### A. Peran: Pemilik Usaha (Business Owner)
1. **Mengontrol Anggaran & Saldo Kredit Visual:**
   - Pantau saldo kredit di topbar kanan atau buka [`/ai-studio/credits`](https://cooca.id/ai-studio/credits).
   - Lakukan Top Up paket kredit hemat saat promo musiman (Ramadhan, Harbolnas, Natal/Tahun Baru).
2. **Menyetujui & Menyimpan Foto Resmi ke Kasir POS:**
   - Buka proyek di canvas editor [`/ai-studio/projects/{id}`](https://cooca.id/ai-studio).
   - Pilih variasi gambar terbaik yang telah selesai dirender.
   - Klik tombol **Simpan ke Katalog** untuk menggantikan foto produk lama di sistem kasir kasir POS secara seketika.

### B. Peran: Staf Pemasaran & Konten Kreator
1. **Merender Foto Produk Baru:**
   - Foto produk asli Anda di meja bersih menggunakan kamera ponsel biasa.
   - Buka menu [`/ai-studio/create`](https://cooca.id/ai-studio/create).
   - Pilih rasio *Square 1:1* untuk katalog POS/Instagram atau *Vertical 9:16* untuk TikTok/Reels.
   - Pilih tema gaya visual (misal: *Minimalist Studio* atau *Sunlit Marble*).
   - Klik **Mulai Render Visual**.
2. **Publikasi ke Media Sosial Bisnis:**
   - Pada halaman detail hasil render, klik **Posting ke Media Sosial**.
   - Tambahkan caption yang telah disarankan oleh sistem dan jadwalkan publikasi ke akun Instagram atau WhatsApp status toko.

---

## 3. Daftar Artikel Terkait

- [Buku Panduan Bisnis Owner: Visual Studio AI & 20 Sektor Industri](buku-panduan-bisnis-owner-ai-studio.md)
- [Tanya Jawab Operasional (FAQ)](faq.md)
- [Panduan Pemecahan Masalah (Troubleshooting)](troubleshooting.md)

