# MASTER TASK: COOCA EVERGREEN SEO CONTENT ENGINE
## 40 High-Quality Articles Daily | Topical Authority | High-CTR Headlines | SEO | AI Image Generation | Automated Publishing

---

# MISSION & OBJECTIVES

Bangun topical authority COOCA sebagai platform Business Operating System dan pusat edukasi operasional UMKM Indonesia melalui sistem riset keyword, produksi konten evergreen, optimasi SEO, visual asset acquisition, AI image generation, dan publikasi otomatis.

Target produksi maksimal 40 artikel per hari, dengan distribusi ideal 8 artikel pada masing-masing dari 5 pilar utama.

Seluruh artikel wajib memenuhi standar berikut:

1. Evergreen content yang relevan dalam jangka panjang.
2. Judul clickbait berkualitas tinggi dengan daya tarik CTR kuat.
3. Primary keyword terintegrasi secara natural dalam judul dan konten.
4. Konten edukatif, faktual, mendalam, dan memberikan solusi praktis.
5. Tidak memiliki keyword cannibalization.
6. Memiliki cover image terbaru, relevan, dan sesuai primary keyword.
7. Jika gambar yang sesuai tidak ditemukan, wajib membuat gambar menggunakan AI Image Generator.
8. Seluruh cover image tersimpan secara mandiri di repositori COOCA.
9. Lulus seluruh validasi otomatis dengan target 0 FAIL dan 0 WARN.
10. Memiliki struktur internal linking yang relevan.
11. Tidak mengandung AI slop, repetisi, keyword stuffing, atau konten tipis.
12. Memiliki nilai informasi yang lebih baik daripada artikel kompetitor pada search intent yang sama.

**PRINSIP UTAMA:**

Prioritaskan kualitas informasi, search intent, manfaat pembaca, kredibilitas, relevansi visual, dan pengalaman pengguna.

Target 40 artikel bukan alasan untuk menurunkan standar editorial maupun kualitas gambar.

---

# PROJECT CONFIGURATION

**Direktori proyek:**

`C:\laragon\www\cooca_core\`

**Direktori artikel:**

`database/data/articles/{slug}.md`

**Metadata artikel:**

`database/data/articles/{slug}.json`

**Direktori cover image:**

`public/images/blog/{slug}.jpg`

**Master keyword registry:**

`database/data/keyword-registry.json`

**Riset keyword harian:**

`database/data/articles/keyword-research-{YYYY-MM-DD}.json`

**Progress produksi:**

`database/data/articles/.progress-{YYYY-MM-DD}.json`

**Internal link registry:**

`database/data/blog_links.json`

**Target publikasi:**

- `https://cooca.id/admin/posts`
- `https://cooca.id/blog`

**Target utama:** Indonesia.

**Target pembaca:** Pemilik UMKM, pengusaha F&B, pemilik toko retail, online seller, reseller, distributor, dan penyedia jasa.

**Brand positioning:** COOCA adalah Business Operating System dan Omnichannel ERP untuk UMKM.

---

# FASE 0: PROJECT AUDIT & PRE-PRODUCTION

Sebelum memulai produksi:

1. Periksa seluruh file pada `database/data/articles/`.
2. Baca `database/data/keyword-registry.json`.
3. Periksa seluruh artikel yang sudah tersimpan di database lokal.
4. Periksa `database/data/blog_links.json`.
5. Identifikasi kategori, slug, keyword, dan search intent yang sudah pernah digunakan.
6. Periksa implementasi importer `blog:import-articles`.
7. Periksa format frontmatter, JSON-LD, metadata, dan ketentuan validator.
8. Periksa konfigurasi sitemap dan mekanisme publikasi produksi.
9. Periksa struktur direktori `public/images/blog/`.
10. Audit gambar yang sudah digunakan untuk menghindari duplikasi visual.
11. Periksa implementasi image processing, kompresi, dan format gambar.
12. Periksa ketersediaan Image Generator yang dapat digunakan dalam workflow.
13. Periksa `git status` dan pastikan tidak ada perubahan pengguna yang akan tertimpa.
14. Sinkronkan branch dengan `git pull --rebase origin main` hanya setelah memastikan perubahan lokal aman.

## 0.1. Duplicate Prevention

Jangan membuat artikel baru jika:

- Primary keyword sudah terdaftar dan memiliki search intent identik.
- Artikel sebelumnya sudah menjawab kebutuhan pencarian yang sama.
- Sudut pandang hanya berbeda pada susunan kata.
- Artikel baru tidak memberikan informasi atau manfaat tambahan.
- Artikel berpotensi bersaing dengan artikel COOCA yang sudah ada.

Jika ditemukan topik serupa, evaluasi kemungkinan memperbarui artikel lama daripada membuat artikel baru.

## 0.2. Image Inventory Audit

Periksa seluruh cover image yang sudah ada.

Identifikasi:

- Nama file.
- Artikel yang menggunakan gambar.
- Primary keyword.
- Topik visual.
- Resolusi.
- Format.
- Ukuran file.
- Sumber gambar.
- Lisensi atau status hak penggunaan.
- Apakah gambar masih relevan.
- Apakah gambar pernah digunakan pada artikel lain.

Jangan menggunakan satu gambar untuk banyak artikel yang berbeda hanya untuk mempercepat produksi.

---

# FASE 1: EVERGREEN KEYWORD RESEARCH

Lakukan riset keyword berdasarkan kebutuhan pencarian jangka panjang pelaku UMKM Indonesia.

Gunakan sumber dan metode riset berikut:

- Google Autocomplete.
- Google Search.
- People Also Ask.
- Related Searches.
- Keyword variations.
- Search intent analysis.
- Google Trends sebagai indikator tambahan.
- Dokumentasi resmi pemerintah dan penyedia layanan terkait untuk topik regulasi.
- Analisis konten kompetitor berdasarkan kualitas dan kelengkapan informasi.

Jangan mengarang search volume, keyword difficulty, atau tingkat persaingan apabila tidak tersedia data yang dapat diverifikasi.

## 1.1. Lima Pilar Utama

Distribusikan 40 artikel dengan alokasi berikut.

### Pilar 1: Manajemen Keuangan & Pembukuan (8 Artikel)

Topik:

- Perhitungan HPP kuliner, retail, dan jasa.
- Food cost ratio.
- Break Even Point.
- Margin dan markup.
- Arus kas harian.
- Pemisahan rekening pribadi dan bisnis.
- Pencatatan kasbon karyawan.
- Laporan laba rugi UMKM.

### Pilar 2: Inventori, Stok & Manajemen Gudang (8 Artikel)

Topik:

- SOP stock opname.
- Selisih stok kasir dan gudang.
- FIFO dan FEFO.
- Safety stock.
- Reorder point.
- Pengelolaan multi gudang.
- Barang rusak dan kedaluwarsa.
- Pengendalian persediaan.

### Pilar 3: Kasir, POS & Kontrol Fraud (8 Artikel)

Topik:

- SOP buka dan tutup shift.
- Rekonsiliasi kas.
- Blind cash drop.
- Role-Based Access Control.
- Pencegahan fraud void dan retur.
- Integrasi QRIS.
- Sistem kasir offline.
- Audit transaksi kasir.

### Pilar 4: Legalitas, Perizinan & Kepatuhan Pajak (8 Artikel)

Topik:

- NIB dan OSS RBA.
- Sertifikasi halal.
- PIRT dan BPOM.
- SAK EMKM.
- PPh Final UMKM.
- Faktur pajak dan Coretax.
- Legalitas usaha patungan.
- Administrasi dan kepatuhan usaha.

### Pilar 5: CRM, Retensi Pelanggan & Omnichannel Marketing (8 Artikel)

Topik:

- Repeat order.
- Customer retention.
- Membership dan loyalty points.
- WhatsApp Business.
- Katalog online.
- Marketplace management.
- Sinkronisasi pesanan.
- Pengelolaan pelanggan omnichannel.

## 1.2. Evergreen Qualification

| Kriteria | Bobot |
|---|---:|
| Relevansi jangka panjang | 25% |
| Kebutuhan nyata pelaku UMKM | 25% |
| Search intent yang jelas | 20% |
| Potensi pengembangan topical authority | 15% |
| Potensi solusi praktis | 15% |

Gunakan skor 1-5.

Minimal skor 4/5 untuk setiap artikel yang dipilih.

## 1.3. Evergreen Content Classification

**A. Core Evergreen**

Topik yang tidak bergantung pada perubahan waktu.

Contoh:

- Cara Menghitung HPP.
- Cara Mengelola Arus Kas.
- Cara Melakukan Stock Opname.
- Cara Menghitung Margin Keuntungan.
- Cara Mengelola Stok Barang.

**B. Seasonal Evergreen**

Topik yang berulang pada periode tertentu dan tetap relevan setiap tahun.

Contoh:

- Persiapan Stok Ramadan.
- Manajemen Persediaan Saat Harbolnas.
- Pengelolaan Arus Kas Menjelang Lebaran.
- Strategi Penjualan Saat Tahun Baru.

**C. Regulatory Evergreen**

Topik regulasi yang memiliki kebutuhan pencarian berulang tetapi berpotensi mengalami perubahan.

Contoh:

- Cara Mengurus NIB.
- Cara Menghitung Pajak UMKM.
- Persyaratan Sertifikasi Halal.

Wajib menyertakan tanggal pemeriksaan informasi, sumber resmi, cakupan aturan, dan mekanisme pembaruan.

**D. Trend-Driven Content**

Konten berdasarkan fenomena atau tren aktual.

Jenis ini tidak otomatis dikategorikan evergreen.

Buat artikel hanya jika memiliki nilai edukasi jangka panjang atau relevansi aktual yang cukup kuat.

Hindari mencampurkan berita sesaat ke dalam cluster evergreen hanya demi memenuhi kuota.

## 1.4. Evergreen Durability Test

Sebelum memilih topik, lakukan pengujian:

1. Apakah masalah tersebut masih mungkin dialami UMKM 3-5 tahun mendatang?
2. Apakah artikel tetap berguna tanpa menyebut tahun tertentu?
3. Apakah solusi yang diberikan dapat diterapkan berulang kali?
4. Apakah artikel memiliki nilai edukasi di luar momentum berita?
5. Apakah perubahan teknologi atau regulasi dapat diperbarui tanpa menulis ulang seluruh artikel?

Jika sebagian besar jawaban tidak, jangan masukkan ke kategori evergreen utama.

## 1.5. Keyword Registry

Simpan informasi berikut dalam `keyword-registry.json`:

- Primary keyword.
- Secondary keywords.
- Long-tail keywords.
- Search intent.
- Topical cluster.
- Content pillar.
- Evergreen classification.
- Evergreen score.
- Target audience.
- Problem statement.
- Content angle.
- Existing competing articles.
- Target slug.
- Article status.

Pastikan setiap keyword memiliki identitas unik dan tidak menyebabkan cannibalization.

---

# FASE 2: ADVANCED CLICKBAIT HEADLINE ENGINE

## 2.1. Tujuan Strategi Judul

Seluruh judul artikel wajib memiliki daya tarik klik tinggi, tetapi tetap profesional, faktual, dan relevan dengan kebutuhan pencarian.

Terapkan prinsip:

**High CTR + SEO Relevance + Curiosity Gap + Honest Promise.**

Judul harus membuat pembaca merasa ada informasi penting yang perlu diketahui, kesalahan yang harus dihindari, risiko yang perlu dipahami, atau peluang yang dapat dimanfaatkan.

Jangan menghasilkan judul generik yang hanya mendeskripsikan topik.

Contoh:

Judul generik:

- Cara Menghitung HPP UMKM.
- Tips Mengelola Stok Barang.
- Pengertian Arus Kas Bisnis.
- Panduan Menggunakan Aplikasi Kasir.

Judul yang lebih menarik:

- HPP UMKM Sering Salah Hitung? Ini Penyebab Keuntungan Bisnis Terus Menipis
- Stok Barang Sering Hilang Tanpa Disadari? Periksa Kesalahan Pengelolaan Ini
- Omzet Terus Naik tetapi Uang Tidak Terkumpul? Kenali Masalah Arus Kas Bisnis
- Aplikasi Kasir UMKM: Kesalahan Memilih Sistem yang Bisa Mengganggu Operasional Toko

## 2.2. Psikologi Clickbait

Variasikan trigger:

1. Curiosity Gap.
2. Loss Aversion.
3. Fear of Missing Out.
4. Unexpected Facts.
5. Contrarian Angle.
6. Specificity.
7. Urgency.
8. Problem Recognition.
9. Transformation.
10. Comparison.

## 2.3. Bank Formula Clickbait

Gunakan variasi formula berikut.

**A. Curiosity Gap**

- Ternyata Ini Penyebab [Primary Keyword] Sering Menimbulkan Masalah pada Bisnis.
- Mengapa [Primary Keyword] Tidak Berjalan Sesuai Harapan? Ini Faktanya.
- Ada yang Salah dengan [Primary Keyword]? Periksa Hal yang Sering Terlewat Ini.

**B. Financial Loss**

- Omzet Besar tetapi Keuntungan Tipis? Periksa [Primary Keyword] Ini.
- Modal Terus Berkurang? Bisa Jadi [Primary Keyword] Belum Dikelola dengan Benar.
- Jangan Sampai Boncos! Kesalahan [Primary Keyword] yang Merugikan UMKM.

**C. Hidden Mistakes**

- Kesalahan [Primary Keyword] Ini Sering Tidak Disadari Pemilik Usaha.
- Banyak Pengusaha Keliru Mengelola [Primary Keyword], Ini Dampaknya.
- Jangan Ulangi Kesalahan Ini Saat Menggunakan [Primary Keyword].

**D. Unexpected Facts**

- Ternyata [Primary Keyword] Tidak Selalu Menguntungkan, Ini Alasannya.
- Banyak yang Salah Paham tentang [Primary Keyword], Begini Faktanya.
- [Primary Keyword] Tidak Sesederhana yang Dibayangkan, Ini Penjelasannya.

**E. Business Warning**

- Awas, Kesalahan [Primary Keyword] Bisa Membuat Operasional Bisnis Terganggu.
- Sebelum Menggunakan [Primary Keyword], Pahami Risiko yang Sering Terlewat.
- Jangan Abaikan [Primary Keyword] Jika Tidak Ingin Mengalami Masalah Ini.

**F. Opportunity & FOMO**

- Peluang [Primary Keyword] Ini Bisa Membantu UMKM Mengembangkan Bisnis.
- Ingin Bisnis Lebih Efisien? Kenali Cara Memanfaatkan [Primary Keyword].
- [Primary Keyword] Bisa Membuka Peluang Baru, Ini Cara Memulainya.

**G. Comparison**

- [Metode A] vs [Metode B]: Mana yang Lebih Menguntungkan untuk UMKM?
- Lebih Baik [Pilihan A] atau [Pilihan B]? Perhatikan Perbedaan Ini.
- Jangan Salah Memilih [Primary Keyword], Ini Perbandingan yang Perlu Diketahui.

**H. Myth Busting**

- Benarkah [Mitos Bisnis]? Ini Fakta yang Sering Terlewat Pengusaha.
- Jangan Mudah Percaya Anggapan tentang [Primary Keyword] Ini.
- [Primary Keyword] Tidak Selalu Seperti yang Dibayangkan, Ini Faktanya.

**I. Practical Guide**

- Cara Mengelola [Primary Keyword] agar Bisnis Tidak Terus Mengalami Kerugian.
- [Primary Keyword]: Langkah Penting yang Sering Terlewat Pemilik UMKM.
- Begini Cara Menghitung [Primary Keyword] agar Tidak Salah Mengambil Keputusan.

**J. Case Study**

- Simulasi [Primary Keyword]: Berapa Sebenarnya Keuntungan yang Bisa Didapat?
- Dari Omzet hingga Laba Bersih: Begini Perhitungan [Primary Keyword].
- Belajar dari Kesalahan Bisnis: Cara Memperbaiki [Primary Keyword].

**K. Operational Problem**

- Toko Ramai tetapi Pelayanan Kacau? Periksa Sistem Operasional Ini.
- Stok Sering Tidak Sesuai? Begini Cara Memperbaiki Pengelolaan Persediaan.
- Kasir Sering Selisih? Ini Bagian SOP yang Wajib Diperiksa.

**L. Evergreen Authority**

- Panduan [Primary Keyword]: Kesalahan Penting yang Perlu Dihindari UMKM.
- Memahami [Primary Keyword]: Hal yang Sering Menentukan Keuntungan Bisnis.
- [Primary Keyword] untuk UMKM: Cara Kerja, Risiko, dan Solusi Praktis.

## 2.4. Ketentuan Khusus Judul Evergreen

- Hindari ketergantungan pada kata hari ini, minggu ini, terbaru, viral, dan sekarang jika tidak diperlukan.
- Jangan memasukkan tahun ke dalam judul jika tidak memberikan manfaat pencarian.
- Utamakan keyword yang tetap relevan dalam jangka panjang.
- Gunakan situasi bisnis yang nyata dan berulang.
- Gunakan angka jika isi artikel benar-benar mendukungnya.
- Hindari klaim bahwa suatu metode merupakan solusi terbaik untuk seluruh jenis bisnis.
- Gunakan konteks UMKM, F&B, retail, online shop, atau jasa jika memperjelas relevansi.

## 2.5. Primary Keyword Placement

Setiap H1 wajib memiliki primary keyword secara utuh dan natural.

1. Prioritaskan penempatan keyword pada bagian awal atau tengah awal judul.
2. Jangan memaksakan exact match jika menyebabkan tata bahasa buruk.
3. Jangan mengulang keyword secara berlebihan.
4. Jangan mengorbankan keterbacaan demi SEO.
5. Pastikan judul mencerminkan search intent yang benar.

## 2.6. Panjang Judul

| Elemen | Ketentuan |
|---|---|
| H1 / Article Title | 65-95 karakter |
| Target ideal H1 | 70-85 karakter |
| Meta Title | Maksimal 60 karakter |
| Meta Description | 140-160 karakter |
| Slug | Lowercase, ringkas, mengandung keyword |
| Primary Keyword | Wajib dalam H1 |

## 2.7. Headline Generation & Scoring

Untuk setiap artikel:

1. Hasilkan minimal 10 alternatif judul.
2. Gunakan minimal 5 jenis formula berbeda.
3. Hindari menghasilkan 10 judul yang hanya mengganti beberapa kata.
4. Berikan skor setiap alternatif.

| Penilaian | Bobot |
|---|---:|
| Search intent relevance | 25% |
| Curiosity dan click appeal | 25% |
| Primary keyword relevance | 20% |
| Value proposition | 15% |
| Accuracy dan credibility | 15% |

Pilih judul dengan nilai terbaik dan lakukan pemeriksaan editorial.

Skor internal bukan jaminan CTR aktual. Evaluasi CTR sesungguhnya menggunakan data Search Console apabila tersedia.

## 2.8. Honest Clickbait Policy

Dilarang:

- Mengarang skandal, penipuan, atau modus kejahatan.
- Mengklaim bisnis pasti bangkrut akibat satu kesalahan.
- Menggunakan ancaman denda tanpa dasar hukum.
- Mengarang angka kerugian dan persentase.
- Menggunakan kata rahasia, terbongkar, atau mengejutkan tanpa substansi.
- Mengklaim fitur wajib dimiliki seluruh UMKM jika sebenarnya opsional.
- Menggunakan judul yang menjanjikan sesuatu yang tidak dibahas dalam artikel.

**Golden Rule:** Judul harus lebih menarik daripada judul deskriptif biasa, tetapi isi artikel harus mampu membuktikan seluruh janji judul.

---

# FASE 3: EVERGREEN CONTENT PRODUCTION

## 3.1. Standar Penulisan

Setiap artikel:

- Panjang 1.500-2.000 kata, di luar frontmatter dan JSON-LD.
- Tepat 1 H1.
- 4-8 H2.
- H3 digunakan secara proporsional.
- 4-6 FAQ.
- Minimal 3-5 internal link unik.
- Memiliki contoh penerapan dan simulasi angka.
- Menggunakan bahasa Indonesia profesional dan natural.

## 3.2. Struktur Konten Wajib

**1. Opening Hook**

Mulai dengan situasi yang benar-benar dialami pelaku UMKM.

Hindari:

- Di era digital saat ini.
- Dalam lanskap bisnis modern.
- Seiring perkembangan teknologi yang semakin pesat.

Gunakan pendekatan masalah nyata, ilustrasi singkat, atau pertanyaan relevan.

**2. Problem Explanation**

Jelaskan:

- Permasalahan utama.
- Penyebab.
- Dampak finansial dan operasional.
- Mengapa masalah sering tidak disadari.

**3. Core Educational Explanation**

Jelaskan konsep utama secara sistematis.

Gunakan definisi, rumus, proses kerja, tabel, atau perbandingan apabila diperlukan.

**4. Practical Step-by-Step Solution**

Berikan langkah yang dapat diterapkan oleh pembaca.

Jangan hanya menjelaskan teori.

**5. Realistic Business Simulation**

Setiap artikel wajib menyertakan tabel atau simulasi angka.

Ketentuan:

- Gunakan nominal rupiah.
- Jelaskan asumsi.
- Tampilkan rumus jika ada.
- Pastikan perhitungan benar.
- Beri label simulasi apabila bukan data kasus nyata.
- Jangan mengklaim simulasi sebagai hasil bisnis sungguhan.

**6. Common Mistakes**

Jelaskan kesalahan yang umum terjadi dan cara menghindarinya.

**7. COOCA Practical Application**

Jelaskan bagaimana sistem manajemen bisnis, pencatatan, POS, inventori, CRM, atau omnichannel dapat membantu menyelesaikan masalah terkait.

Ketentuan:

- Penyebutan COOCA natural.
- Maksimal 1-2 bagian.
- Tidak melakukan hard-selling.
- Tidak mengarang fitur yang belum tersedia.
- Tidak mengklaim COOCA sebagai solusi universal.

**8. Conclusion**

Berikan ringkasan dan rekomendasi tindakan.

**9. FAQ**

Buat 4-6 pertanyaan berdasarkan kebutuhan pencarian pembaca.

**10. JSON-LD**

Sertakan Article dan FAQPage sesuai validitas implementasi schema.

Pastikan informasi schema sama dengan konten yang terlihat pembaca.

---

# FASE 4: EVERGREEN CONTENT QUALITY & ANTI-AI SLOP

Artikel tidak boleh hanya menjadi kumpulan definisi dan paragraf generik.

## 4.1. Depth of Information

Setiap artikel harus memiliki:

- Penjelasan konsep yang lengkap.
- Contoh yang relevan dengan bisnis Indonesia.
- Perhitungan atau ilustrasi praktis.
- Langkah implementasi.
- Risiko dan keterbatasan.
- Kesalahan yang harus dihindari.
- Rekomendasi berdasarkan kondisi bisnis.

## 4.2. Anti-AI Footprint

Dilarang:

- Pembuka artikel yang berulang.
- Kesimpulan yang hanya mengulang pendahuluan.
- Pengulangan kata transisi secara berlebihan.
- Paragraf yang hanya memperpanjang jumlah kata.
- Kalimat generik tanpa informasi.
- Daftar poin yang tidak memberikan penjelasan.
- Klaim tanpa bukti.
- Penggunaan kata promosi berlebihan.
- Pola struktur identik pada seluruh 40 artikel.

Setiap artikel wajib memiliki karakter penulisan yang disesuaikan dengan topik.

## 4.3. Originality

Jangan menyalin artikel kompetitor.

Gunakan sumber sebagai referensi fakta, kemudian susun penjelasan, struktur, contoh, dan analisis secara mandiri.

Jangan mengarang pengalaman pribadi, testimoni pelanggan, atau studi kasus nyata.

## 4.4. Evergreen Maintenance

Setiap artikel harus memiliki metadata pemeliharaan:

- `content_type`
- `evergreen_score`
- `last_verified_at`
- `next_review_at`
- `source_urls`
- `regulatory_dependency`
- `update_notes`

Jadwal pemeriksaan:

- Core Evergreen: review setiap 12 bulan.
- Seasonal Evergreen: review sebelum musim terkait.
- Regulatory Evergreen: review setiap terjadi perubahan aturan dan secara berkala.
- Technology Evergreen: review ketika terjadi perubahan signifikan pada layanan atau teknologi.

Artikel evergreen bukan berarti tidak perlu diperbarui.

---

# FASE 5: ADVANCED IMAGE ACQUISITION & AI IMAGE GENERATION ENGINE

## MANDATORY REQUIREMENT

Setiap artikel WAJIB memiliki cover image yang:

1. Sesuai primary keyword.
2. Mewakili isi dan search intent artikel.
3. Menggunakan visual yang relevan dengan kondisi bisnis Indonesia.
4. Mengutamakan gambar aktual dan terbaru apabila topik membutuhkan representasi kondisi terkini.
5. Tidak menggunakan gambar generik yang tidak berhubungan dengan topik.
6. Memiliki hak penggunaan yang jelas.
7. Tersimpan sebagai aset mandiri dalam repositori COOCA.
8. Dapat diakses melalui URL internal COOCA.

**DILARANG menyelesaikan artikel tanpa cover image yang telah diverifikasi.**

## 5.1. IMAGE ACQUISITION PRIORITY

Gunakan urutan pengadaan gambar berikut.

**PRIORITY 1: CURRENT AND RELEVANT REAL-WORLD IMAGES**

Lakukan pencarian gambar berdasarkan:

- Primary keyword.
- Secondary keyword.
- Long-tail keyword.
- Konteks visual artikel.
- Objek utama yang dibahas.
- Aktivitas bisnis yang relevan.
- Kondisi atau teknologi terbaru apabila topik membutuhkannya.

Gunakan pencarian gambar terbaru dari sumber visual yang kredibel.

Contoh:

| Keyword Artikel | Target Visual |
|---|---|
| HPP UMKM | Bahan baku, timbangan digital, proses produksi kuliner |
| Stock Opname | Petugas menghitung persediaan di rak gudang |
| Sistem Kasir POS | Terminal kasir, barcode scanner, transaksi toko |
| FIFO dan FEFO | Rak penyimpanan produk dengan label tanggal |
| Arus Kas UMKM | Pembukuan, laporan keuangan, kalkulator |
| WhatsApp Business | Smartphone dengan tampilan komunikasi bisnis |
| CRM UMKM | Interaksi pelanggan dan pengelolaan database pelanggan |
| Omnichannel | Operasional penjualan melalui beberapa kanal digital |

Jangan hanya mencocokkan nama file atau alt text. Periksa konten visual yang sebenarnya.

**PRIORITY 2: VERIFIED LICENSED STOCK IMAGE**

Jika gambar relevan tersedia dari sumber gambar berlisensi:

1. Periksa lisensi penggunaan.
2. Pastikan penggunaan untuk website komersial diperbolehkan.
3. Pastikan tidak terdapat watermark.
4. Periksa apakah atribusi diperlukan.
5. Unduh gambar resolusi tinggi.
6. Simpan salinan lokal di repositori COOCA.
7. Catat sumber dan lisensi gambar.

Jangan menganggap gambar yang dapat diunduh otomatis bebas hak cipta.

**PRIORITY 3: AI IMAGE GENERATION**

Jika gambar yang sesuai tidak ditemukan, tidak cukup aktual, tidak relevan, memiliki lisensi yang tidak jelas, atau kualitasnya tidak memenuhi standar, WAJIB gunakan AI Image Generator.

Jangan:

- Menggunakan gambar yang tidak relevan sebagai pengganti.
- Menggunakan gambar dari artikel lain.
- Mengambil screenshot Google Images sebagai cover.
- Menggunakan thumbnail ber-watermark.
- Menggunakan URL gambar eksternal sebagai cover final.
- Membiarkan cover image kosong.
- Menunda pembuatan gambar tanpa alasan teknis yang jelas.

AI Image Generator harus menjadi fallback otomatis, bukan opsi yang boleh dilewati.

## 5.2. IMAGE FRESHNESS POLICY

Kata terbaru tidak berarti harus selalu menggunakan foto yang baru diunggah.

Yang wajib adalah kesesuaian waktu dan konteks visual.

Terapkan ketentuan:

**A. Topik yang Membutuhkan Visual Aktual**

Contoh:

- Perubahan teknologi POS.
- Perangkat kasir terbaru.
- Marketplace dan fitur baru.
- Perubahan sistem pembayaran.
- Perubahan tampilan aplikasi.
- Kebijakan atau infrastruktur digital terbaru.

Gunakan gambar yang merepresentasikan versi atau kondisi terkini.

Jika gambar aktual tidak ditemukan, gunakan AI Image Generator untuk membuat visual ilustratif yang relevan.

Jangan menggambarkan hasil generasi AI sebagai screenshot atau dokumentasi resmi produk.

**B. Topik Evergreen**

Contoh:

- HPP.
- Arus kas.
- Stock opname.
- FIFO.
- Margin keuntungan.
- Pembukuan.

Gunakan gambar yang masih relevan secara visual dan tidak memiliki unsur usang yang menyesatkan.

Tidak perlu mengganti gambar hanya karena tanggal unggahnya lama apabila konten visual masih akurat.

**C. Topik Regulasi**

Gunakan visual pendukung yang relevan dengan proses administrasi atau konteks layanan.

Jangan menggunakan gambar dokumen palsu, sertifikat palsu, logo pemerintah yang tidak berizin, atau tampilan portal resmi hasil rekayasa.

Jika menggunakan AI, buat ilustrasi konseptual tanpa mengklaimnya sebagai dokumen resmi.

## 5.3. AI IMAGE GENERATION PROMPT ENGINE

Jika masuk tahap AI generation, buat prompt khusus berdasarkan artikel.

Prompt wajib mempertimbangkan:

1. Primary keyword.
2. Secondary keyword.
3. Artikel title.
4. Konteks bisnis Indonesia.
5. Objek utama.
6. Aktivitas atau interaksi.
7. Lingkungan.
8. Komposisi.
9. Sudut kamera.
10. Pencahayaan.
11. Warna.
12. Negative prompt.

Contoh prompt untuk artikel HPP:

"Create a premium photorealistic editorial photograph illustrating HPP calculation for an Indonesian small food business. Show a small business owner carefully weighing fresh ingredients on a digital kitchen scale beside organized ingredient containers, a calculator, and a simple cost notebook. Authentic Indonesian small business kitchen environment, realistic food ingredients, natural lighting, professional commercial photography, clear focal point, realistic human hands, detailed textures, clean composition, landscape 16:9 framing, no watermark, no logos, no readable text, no fake UI, no artificial infographic elements."

Contoh prompt untuk artikel Stock Opname:

"Create a highly realistic editorial photograph illustrating stock opname in an Indonesian retail warehouse. Show a warehouse employee scanning product barcodes and checking inventory on organized shelves, with realistic packaging, handheld barcode scanner, and inventory management workflow. Modern small business warehouse, natural professional lighting, authentic operational environment, landscape 16:9, sharp focus, realistic details, no watermark, no logos, no fabricated readable text."

Prompt harus dibuat spesifik untuk setiap artikel.

DILARANG menggunakan satu prompt generik untuk seluruh gambar.

## 5.4. IMAGE GENERATION VISUAL STANDARDS

Semua hasil AI harus mengikuti standar:

- Photorealistic editorial photography atau visual illustration sesuai kebutuhan artikel.
- Landscape 16:9.
- Resolusi ideal 1600 x 900 px atau lebih tinggi.
- Komposisi bersih.
- Objek utama mudah dikenali.
- Tidak menggunakan elemen dekoratif yang berlebihan.
- Tidak menggunakan teks acak hasil AI.
- Tidak menggunakan logo COOCA palsu.
- Tidak menggunakan watermark.
- Tidak menampilkan angka atau data yang tidak dapat dibaca dengan benar.
- Tidak menggunakan anatomi manusia yang rusak.
- Tidak menampilkan objek yang tidak sesuai konteks.

Jika artikel membutuhkan diagram, alur kerja, atau infografik, buat sebagai visual terpisah yang memiliki akurasi informasi. Jangan memaksakan diagram sebagai cover fotografis.

## 5.5. IMAGE SELECTION SCORING

Setiap gambar yang ditemukan atau dihasilkan harus melewati evaluasi.

| Kriteria | Bobot |
|---|---:|
| Relevansi dengan primary keyword | 30% |
| Relevansi dengan isi artikel | 25% |
| Akurasi objek dan konteks | 15% |
| Kualitas visual | 15% |
| Aktual dan sesuai konteks waktu | 10% |
| Komposisi dan keterbacaan | 5% |

Minimal skor kelayakan: 85/100.

Jika di bawah 85, cari alternatif atau generate ulang.

Skor ini merupakan pemeriksaan editorial internal, bukan ukuran performa SEO aktual.

## 5.6. IMAGE RIGHTS & OWNERSHIP

Bedakan dua jenis aset:

**A. Licensed Third-Party Image**

- Simpan sumber URL.
- Simpan nama penyedia.
- Simpan informasi lisensi.
- Simpan informasi atribusi jika diwajibkan.
- Jangan mengklaim hak cipta eksklusif COOCA.

**B. COOCA Commissioned / AI-Generated Image**

- Simpan prompt generasi.
- Simpan tanggal pembuatan.
- Simpan model atau penyedia generator jika tersedia.
- Simpan informasi lisensi dan hak penggunaan sesuai ketentuan penyedia.
- Catat status sebagai aset visual yang dibuat khusus untuk COOCA.

Jangan menyatakan semua gambar milik COOCA secara hukum hanya karena disimpan di repositori sendiri.

## 5.7. IMAGE STORAGE & NAMING

Simpan seluruh cover image pada:

`public/images/blog/{slug}.jpg`

Gunakan:

- Filename berdasarkan slug artikel.
- Format JPG.
- Resolusi minimal 1200 px pada sisi horizontal.
- Target maksimal 300 KB setelah optimasi jika kualitas tetap terjaga.
- Rasio 16:9.
- Kompresi yang tidak merusak kualitas.
- Metadata file yang konsisten.

Jika gambar sumber berupa PNG atau WebP, lakukan konversi yang benar ke JPG apabila sesuai dan tidak menghilangkan detail penting.

Jangan sekadar mengganti ekstensi file tanpa konversi format sebenarnya.

## 5.8. IMAGE METADATA REGISTRY

Simpan metadata gambar pada JSON pendamping artikel.

Contoh:

```json
{
  "image": {
    "path": "public/images/blog/hpp-umkm-kesalahan-perhitungan-keuntungan.jpg",
    "public_url": "https://cooca.id/images/blog/hpp-umkm-kesalahan-perhitungan-keuntungan.jpg",
    "alt": "Pemilik usaha kuliner menimbang bahan baku untuk menghitung HPP",
    "source_type": "ai_generated",
    "source_url": null,
    "provider": "image_generator",
    "prompt": "Prompt lengkap yang digunakan untuk membuat gambar",
    "generated_at": "2026-11-19",
    "license_status": "verified_under_provider_terms",
    "width": 1600,
    "height": 900,
    "format": "jpg",
    "file_size_kb": 245,
    "relevance_score": 96,
    "verification_status": "verified"
  }
}
```

Sesuaikan struktur dengan implementasi metadata proyek.

## 5.9. IMAGE FINAL VALIDATION

Sebelum artikel dianggap selesai, pastikan:

- [ ] Gambar ditemukan atau berhasil dihasilkan.
- [ ] Gambar sesuai primary keyword.
- [ ] Gambar sesuai isi artikel.
- [ ] Gambar tidak usang untuk topik yang membutuhkan visual aktual.
- [ ] Gambar tidak memiliki watermark.
- [ ] Hak penggunaan telah diperiksa.
- [ ] Resolusi memenuhi standar.
- [ ] Rasio gambar sesuai.
- [ ] File benar-benar JPG.
- [ ] Ukuran file telah dioptimasi.
- [ ] Nama file sesuai slug.
- [ ] File tersimpan di repositori COOCA.
- [ ] Alt text deskriptif.
- [ ] Frontmatter menggunakan URL internal COOCA.
- [ ] JSON-LD menggunakan URL gambar yang sama.
- [ ] Gambar dapat dibuka melalui URL publik setelah deployment.

**HARD STOP:** Jika gambar tidak tersedia, tidak relevan, gagal dihasilkan, atau tidak lolos validasi, artikel belum boleh dinyatakan selesai dan tidak boleh dipublikasikan.

---

# FASE 6: INTERNAL LINKING & TOPICAL AUTHORITY

Gunakan hanya link dari:

`database/data/blog_links.json`

Setiap artikel wajib memiliki 3-5 internal link unik yang relevan.

Strategi:

1. Hubungkan artikel dalam satu pilar.
2. Hubungkan artikel dengan topik pendukung.
3. Bangun hubungan antarartikel berdasarkan konteks.
4. Hindari internal link yang tidak relevan.
5. Hindari penggunaan anchor text yang sama secara berlebihan.
6. Jangan mengarang slug atau URL.
7. Hindari linking ke artikel yang belum tersedia atau tidak aktif.

Bangun struktur topical cluster dengan artikel utama dan artikel pendukung.

Targetnya adalah membangun hubungan semantik antarkonten, bukan sekadar memenuhi jumlah link.

---

# FASE 7: DISTRIBUSI PUBLIKASI DAN TANGGAL ARTIKEL

Jangan memanipulasi histori publikasi.

Untuk 40 artikel yang dibuat pada hari yang sama:

- Gunakan tanggal publikasi aktual.
- Distribusikan jam publikasi secara bertahap apabila menggunakan fitur scheduled publishing.
- Jangan mengklaim artikel sudah tersedia sejak periode sebelumnya apabila baru dibuat.
- Jangan memundurkan `published_at` hanya untuk membuat histori website terlihat natural.
- Simpan tanggal pembuatan, tanggal publikasi, dan tanggal pembaruan secara terpisah.

Gunakan tanggal aktual sesuai waktu eksekusi dan zona waktu Asia/Jakarta.

Format:

`published_at: "2026-11-19T09:00:00+07:00"`

Jika mendukung penjadwalan, gunakan status scheduled dan pastikan importer menangani status tersebut dengan benar.

---

# FASE 8: FRONTMATTER DAN METADATA

Format frontmatter:

```yaml
---
title: "HPP UMKM Sering Salah Hitung? Ini Penyebab Keuntungan Bisnis Terus Menipis"
meta_title: "HPP UMKM: Kesalahan Hitung yang Mengurangi Laba"
meta_description: "Pelajari cara menghitung HPP UMKM, kesalahan yang sering terjadi, dan langkah memperbaikinya agar keuntungan bisnis lebih terkontrol."
slug: "hpp-umkm-kesalahan-perhitungan-keuntungan"
primary_keyword: "HPP UMKM"
secondary_keywords:
  - "cara menghitung HPP"
  - "rumus HPP usaha"
  - "perhitungan harga pokok penjualan"
search_intent: "Informational"
category: "Manajemen Keuangan"
content_type: "evergreen"
evergreen_score: 5
reading_time: "9 menit"
cover_image: "https://cooca.id/images/blog/hpp-umkm-kesalahan-perhitungan-keuntungan.jpg"
image_alt: "Pemilik usaha kuliner menimbang bahan baku untuk menghitung HPP"
published_at: "2026-11-19T09:00:00+07:00"
last_verified_at: "2026-11-19"
next_review_at: "2027-11-19"
---
```

Sesuaikan field dengan schema importer yang benar-benar digunakan proyek.

Jangan mengubah format produksi sebelum memeriksa implementasi importer.

Setiap file JSON pendamping harus mengikuti struktur metadata yang konsisten.

---

# FASE 9: KEYWORD REGISTRY & PROGRESS TRACKING

Perbarui registry setelah setiap artikel selesai.

Progress file:

`database/data/articles/.progress-{YYYY-MM-DD}.json`

Simpan:

- Target produksi.
- Artikel selesai.
- Artikel gagal.
- Artikel pending.
- Keyword yang telah digunakan.
- Slug yang telah dibuat.
- Status gambar.
- Image source type.
- Image generation status.
- Image verification status.
- Status linting.
- Status import.
- Status Git.
- Error dan tindakan perbaikan.

Status produksi artikel:

1. `researched`
2. `keyword_approved`
3. `headline_approved`
4. `content_drafted`
5. `image_searching`
6. `image_generated`
7. `image_verified`
8. `content_validated`
9. `imported`
10. `published`
11. `deployment_verified`

Jangan menandai artikel selesai sebelum seluruh validasi berhasil.

Pastikan proses dapat dilanjutkan tanpa mengulang artikel yang sudah selesai.

---

# FASE 10: STRICT CONTENT VALIDATION

Jalankan:

```bash
python scripts/lint_content.py database/data/articles/{slug}.md --links database/data/blog_links.json
```

Target:

**0 FAIL dan 0 WARN.**

Validasi mencakup:

1. Panjang artikel.
2. Struktur H1, H2, dan H3.
3. Primary keyword dalam H1.
4. Meta title dan description.
5. Keyword stuffing.
6. Em dash dan en dash.
7. Internal link.
8. Broken link.
9. Cover image lokal.
10. Format frontmatter.
11. JSON-LD.
12. Duplikasi judul.
13. Duplikasi slug.
14. Metadata evergreen.
15. Konsistensi kategori.
16. Kesesuaian image URL.
17. Keberadaan file gambar.
18. Validitas format gambar.
19. Resolusi dan rasio gambar.
20. Alt text.
21. Metadata sumber dan lisensi gambar.

Tambahkan pemeriksaan editorial manual untuk:

- Akurasi informasi.
- Relevansi judul.
- Kualitas clickbait.
- Kualitas solusi.
- Keaslian konten.
- Validitas sumber.
- Kelayakan evergreen.
- Relevansi visual.
- Kualitas gambar.

Jangan memanipulasi validator atau menghapus aturan agar artikel terlihat lulus.

Jika ditemukan FAIL atau WARN, perbaiki sumber masalahnya.

---

# FASE 11: DATABASE IMPORT & VERIFICATION

Setelah seluruh artikel lulus:

```bash
php artisan blog:import-articles --force
```

Verifikasi:

- Seluruh artikel masuk database.
- Status sesuai jadwal publikasi.
- Tidak ada duplikasi record.
- Kategori benar.
- Slug benar.
- Cover image terbaca.
- Metadata SEO tersimpan.
- `published_at` sesuai.
- Konten lengkap.
- JSON-LD tersedia.
- Internal link berfungsi.

Pastikan importer menggunakan mekanisme upsert atau pencegahan duplikasi yang sesuai.

Jangan melanjutkan deployment jika import mengalami kegagalan.

---

# FASE 12: GIT COMMIT & PRODUCTION DEPLOYMENT

Sebelum commit:

```bash
git status
git diff --check
```

Stage file yang memang menjadi bagian pekerjaan:

```bash
git add database/data/articles/
git add database/data/keyword-registry.json
git add public/images/blog/
```

Jangan stage perubahan pengguna lain atau file sensitif yang tidak berkaitan.

Commit:

```bash
git commit -m "feat(blog): publish evergreen umkm educational articles [YYYY-MM-DD]"
```

Sebelum push, periksa apakah ada commit remote baru dan pastikan seluruh perubahan lokal aman.

```bash
git pull --rebase origin main
git push origin main
```

Jika terjadi konflik, periksa dan selesaikan secara hati-hati.

Jangan melakukan force push.

Server produksi COOCA menggunakan mekanisme deployment otomatis melalui scheduler yang menjalankan Git pull dan importer.

Setelah push:

1. Verifikasi commit berada pada remote branch.
2. Verifikasi scheduler berjalan.
3. Periksa log deployment jika tersedia.
4. Periksa hasil import di server.
5. Verifikasi halaman artikel publik.
6. Verifikasi setiap cover image melalui URL publik.
7. Verifikasi sitemap.
8. Pastikan gambar yang baru dibuat benar-benar ikut ter-deploy.

Jangan mengklaim deployment berhasil hanya karena Git push berhasil.

---

# FASE 13: SEO POST-PUBLICATION VALIDATION

Setelah publikasi, lakukan pemeriksaan:

- URL artikel dapat diakses.
- HTTP response normal.
- Meta title dan description tersedia.
- Canonical URL benar.
- Structured data valid.
- Cover image dapat diakses.
- Cover image sesuai artikel.
- Internal link bekerja.
- Tidak ada halaman duplikat.
- Sitemap memuat artikel yang seharusnya sudah terbit.
- Tampilan mobile dan desktop layak.

Jika tersedia akses Google Search Console, pantau:

- Indexing status.
- Impressions.
- Clicks.
- CTR.
- Average position.
- Search queries.

Jangan mengklaim artikel telah terindeks Google hanya karena URL sudah dapat diakses.

Gunakan data Search Console untuk evaluasi judul dan strategi konten berikutnya.

---

# FASE 14: EXECUTION REPORT

Buat laporan eksekutif dengan format berikut.

## A. Production Summary

- Tanggal produksi.
- Target artikel: 40.
- Artikel selesai.
- Artikel gagal.
- Artikel pending.
- Artikel berhasil diimpor.
- Artikel berhasil dipublikasikan.
- Total keyword terdaftar.
- Total aset gambar.
- Gambar hasil pencarian.
- Gambar hasil AI generation.
- Status deployment.

## B. Article Production Table

| No | Judul Clickbait | Primary Keyword | Pilar | Evergreen Score | Word Count | Image Source | Image Status | Lint | Production |
|---|---|---|---|---:|---:|---|---|---|---|
| 1 | Judul artikel | Keyword | Keuangan | 5 | 1.750 | AI Generated | Verified | PASS | Published |

## C. Headline Evaluation

Tampilkan:

- Primary keyword.
- 10 alternatif judul.
- Judul terpilih.
- Skor evaluasi.
- Formula yang digunakan.
- Alasan pemilihan.
- Hasil pemeriksaan duplikasi.

## D. Evergreen Distribution

Tampilkan jumlah artikel berdasarkan:

- Core Evergreen.
- Seasonal Evergreen.
- Regulatory Evergreen.
- Trend-Driven.

## E. Pillar Distribution

Target distribusi:

- Keuangan: 8 artikel.
- Inventori: 8 artikel.
- Kasir dan POS: 8 artikel.
- Legalitas dan Pajak: 8 artikel.
- CRM dan Omnichannel: 8 artikel.

Jika jumlah artikel berkualitas tidak mencukupi, laporkan kekurangannya secara transparan.

## F. Image Production Report

Tampilkan:

| No | Slug | Primary Keyword | Image Source | Freshness | Relevance Score | Resolution | File Size | Verification |
|---|---|---|---|---|---:|---|---:|---|
| 1 | article-slug | HPP UMKM | AI Generated | Current | 96 | 1600x900 | 245 KB | PASS |

Jelaskan:

- Jumlah gambar hasil pencarian.
- Jumlah gambar hasil AI generation.
- Jumlah gambar yang ditolak.
- Alasan gambar ditolak.
- Status lisensi.
- Status penyimpanan lokal.
- Status URL publik.

## G. Quality Assurance

Tampilkan:

- Hasil linting.
- Jumlah FAIL.
- Jumlah WARN.
- Pemeriksaan editorial.
- Validasi internal link.
- Validasi gambar.
- Validasi JSON-LD.
- Validasi keyword cannibalization.

## H. Deployment Verification

Berikan bukti:

- Git commit hash.
- Git push status.
- Production import status.
- URL artikel yang berhasil diterbitkan.
- Status cover image.
- Status sitemap.

Target dashboard:

`https://cooca.id/admin/posts`

Blog publik:

`https://cooca.id/blog`

---

# FASE 15: CONTINUOUS IMPROVEMENT

Setelah produksi dan publikasi, evaluasi performa konten.

Prioritaskan:

1. Artikel yang mendapatkan impressions tetapi CTR rendah.
2. Artikel dengan ranking yang berpotensi ditingkatkan.
3. Artikel dengan internal linking yang masih lemah.
4. Artikel yang mengalami perubahan regulasi.
5. Artikel dengan informasi yang sudah tidak aktual.
6. Artikel yang memiliki cover image kurang representatif.
7. Artikel yang memiliki search intent tidak sesuai.

Jangan membuat artikel baru untuk keyword yang sebenarnya lebih tepat ditangani dengan memperbarui artikel lama.

Gunakan data aktual untuk memperbaiki:

- Judul.
- Meta description.
- Struktur konten.
- Internal linking.
- Cover image.
- Informasi faktual.
- Search intent alignment.

---

# FINAL EXECUTION RULES

1. Kerjakan seluruh tahapan secara berurutan.
2. Jangan berhenti pada tahap riset atau laporan.
3. Jangan membuat data, sumber, statistik, dan studi kasus palsu.
4. Jangan membuat tanggal publikasi historis yang menyesatkan.
5. Jangan memaksakan 40 artikel jika tidak tersedia 40 topik berkualitas.
6. Jangan membuat artikel yang hanya mengganti susunan kata dari artikel lama.
7. Jangan menggunakan clickbait palsu.
8. Jangan melakukan hard-selling COOCA.
9. Jangan menggunakan gambar yang tidak memiliki hak penggunaan.
10. Jangan menggunakan cover image yang tidak sesuai keyword.
11. Jangan menggunakan gambar usang untuk topik yang membutuhkan representasi kondisi terbaru.
12. Jika gambar relevan tidak ditemukan, WAJIB gunakan AI Image Generator.
13. Jangan melewati image generation hanya karena proses pencarian gambar gagal.
14. Jangan menyatakan gambar hasil AI sebagai foto dokumentasi nyata.
15. Jangan mengklaim kepemilikan hak cipta eksklusif atas gambar pihak ketiga.
16. Jangan mengubah file di luar ruang lingkup pekerjaan.
17. Jangan melakukan deployment jika validasi belum berhasil.
18. Jangan mengklaim keberhasilan tanpa bukti hasil eksekusi.
19. Jangan mengorbankan kualitas artikel, gambar, atau akurasi informasi demi kuota produksi.
20. Pastikan setiap aset gambar benar-benar masuk ke Git dan tersedia di server produksi.

---

# DEFINITION OF DONE

Satu artikel baru dianggap selesai jika:

- Keyword sudah tervalidasi.
- Tidak mengalami cannibalization.
- Judul clickbait lolos evaluasi editorial.
- Konten evergreen memenuhi standar kualitas.
- Artikel memiliki minimal 1.500 kata.
- Gambar terbaru dan relevan tersedia.
- Jika gambar relevan tidak ditemukan, gambar berhasil dibuat dengan AI Image Generator.
- Gambar memenuhi standar kualitas visual.
- Hak penggunaan gambar telah diperiksa.
- Gambar tersimpan sebagai aset lokal COOCA.
- Internal linking valid.
- JSON-LD valid.
- Linting 0 FAIL dan 0 WARN.
- Database import berhasil.
- Deployment berhasil diverifikasi.
- URL artikel dan cover image dapat diakses pada production.

**HARD STOP:** Artikel yang tidak memiliki gambar relevan dan terverifikasi tidak boleh dinyatakan selesai atau dipublikasikan.

---

# FINAL OBJECTIVE

Bangun perpustakaan edukasi bisnis COOCA yang tidak hanya memiliki banyak artikel, tetapi juga:

1. Memiliki topical authority yang kuat.
2. Memiliki judul dengan daya tarik klik tinggi.
3. Memiliki konten evergreen yang terus relevan.
4. Memiliki kualitas editorial yang layak menjadi referensi pemilik UMKM Indonesia.
5. Memiliki visual berkualitas tinggi yang relevan dengan setiap topik.
6. Memiliki sistem pengadaan gambar otomatis melalui pencarian aset aktual dan AI Image Generation.
7. Memiliki aset visual mandiri yang terorganisasi dalam repositori COOCA.
8. Memiliki proses produksi, validasi, import, dan deployment yang dapat diulang dan diaudit.

**QUALITY OVER QUANTITY. RELEVANCE OVER GENERIC CONTENT. VERIFIED VISUALS OVER PLACEHOLDER IMAGES.**