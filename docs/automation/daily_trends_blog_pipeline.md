# MEGA PROMPT: GOOGLE TRENDS BUSINESS RESEARCH, SEO BLOG PRODUCTION, AI IMAGE GENERATION & AUTOMATED PRODUCTION DEPLOYMENT FOR COOCA

**PROJECT:** COOCA.ID  
**TASK TYPE:** Research, Content Engineering, SEO, Image Generation, Database Import & Production Deployment  
**PRIORITY:** HIGH  
**EXECUTION MODE:** End-to-End Autonomous Execution  
**LANGUAGE:** Bahasa Indonesia  
**TARGET MARKET:** UMKM Indonesia  
**PROJECT DIRECTORY:** `C:\laragon\www\cooca_core\`

---

# 1. OBJECTIVE & EXECUTION REQUIREMENTS

Lakukan proses riset, produksi, validasi, dan publikasi artikel blog COOCA secara menyeluruh berdasarkan data tren bisnis Indonesia yang aktual.

Tugas ini bukan sekadar membuat artikel. Anda harus menjalankan seluruh pipeline produksi konten hingga artikel berhasil masuk ke database lokal, ter-push ke Git, dan terverifikasi pada server produksi.

## 1.1. Target Utama

1. Melakukan riset Google Trends Indonesia kategori Bisnis secara real-time.
2. Mengidentifikasi topik bisnis yang sedang berkembang dan relevan dengan UMKM.
3. Melakukan keyword research dan menentukan search intent.
4. Menghasilkan judul clickbait berkualitas tinggi dengan standar SEO profesional.
5. Menulis artikel edukatif, mendalam, solutif, dan berbasis fakta.
6. Mencari cover image terbaru yang relevan dengan primary keyword.
7. Jika gambar yang memenuhi standar tidak ditemukan, WAJIB menggunakan AI Image Generator.
8. Memastikan seluruh gambar tersedia sebagai aset lokal COOCA dengan lisensi atau hak penggunaan yang jelas.
9. Melakukan SEO optimization dan internal linking.
10. Menjalankan content linting sampai mencapai 0 FAIL dan 0 WARN.
11. Mengimpor artikel ke database lokal.
12. Melakukan commit dan push ke Git.
13. Memverifikasi proses deployment dan publikasi pada production.

## 1.2. Direktori dan Target

| Kebutuhan | Lokasi |
|---|---|
| Root project | `C:\laragon\www\cooca_core\` |
| Artikel Markdown | `database/data/articles/{slug}.md` |
| Metadata riset | `database/data/articles/trending-research-{YYYY-MM-DD}.json` |
| Cover image | `public/images/blog/{slug}.jpg` |
| Internal link database | `database/data/blog_links.json` |
| Content linter | `scripts/lint_content.py` |
| Production admin | https://cooca.id/admin/posts |
| Production blog | https://cooca.id/blog |
| Production image | `https://cooca.id/images/blog/{slug}.jpg` |

## 1.3. Prinsip Eksekusi

- Jalankan seluruh proses secara berurutan.
- Jangan berhenti hanya setelah membuat artikel.
- Jangan mengarang data tren, statistik, regulasi, maupun sumber referensi.
- Jangan menggunakan gambar generik yang tidak relevan dengan artikel.
- Jangan melewati validasi linting.
- Jangan mengimpor artikel yang belum lolos validasi.
- Jangan mengklaim deployment berhasil tanpa verifikasi.
- Jangan mengubah file proyek yang tidak berhubungan dengan tugas.
- Jangan menghapus artikel lama atau mengubah data produksi secara tidak diminta.
- Pertahankan arsitektur, struktur database, dan mekanisme deployment yang sudah berjalan.

Target produksi adalah artikel berkualitas tinggi, bukan sekadar memenuhi kuota jumlah artikel.

---

# FASE 0: PROJECT DISCOVERY & PRE-EXECUTION AUDIT

Sebelum memulai produksi, lakukan pemeriksaan terhadap struktur proyek.

## 0.1. Periksa Struktur Direktori

Periksa keberadaan:

```text
C:\laragon\www\cooca_core\
│
├── database/
│   └── data/
│       ├── articles/
│       └── blog_links.json
│
├── public/
│   └── images/
│       └── blog/
│
├── scripts/
│   └── lint_content.py
│
├── resources/
│   └── views/
│
├── routes/
├── app/
├── config/
├── storage/
└── artisan
```

Jangan mengasumsikan struktur di atas pasti identik dengan proyek aktual.

Temukan struktur sebenarnya sebelum melakukan perubahan.

## 0.2. Audit Sistem Blog

Periksa:

1. Mekanisme pembacaan artikel Markdown.
2. Format frontmatter artikel.
3. Database table yang menyimpan artikel.
4. Kolom status publikasi.
5. Mekanisme import artikel.
6. Mekanisme pembaruan artikel yang sudah ada.
7. Mekanisme pengelolaan kategori.
8. Mekanisme SEO metadata.
9. Mekanisme pembacaan cover image.
10. Mekanisme JSON-LD.
11. Mekanisme scheduler production.
12. Mekanisme Git deployment.

Periksa command:

```bash
php artisan list
php artisan blog:import-articles --help
```

Periksa source code command import sebelum menjalankannya.

## 0.3. Audit Linter

Jalankan:

```bash
python scripts/lint_content.py --help
```

Jika tidak tersedia, periksa source code `scripts/lint_content.py`.

Identifikasi seluruh aturan validasi yang sebenarnya.

Jangan memodifikasi linter hanya untuk membuat artikel terlihat lolos.

## 0.4. Audit Artikel Sebelumnya

Periksa artikel yang sudah ada untuk menghindari:

- Judul duplikat.
- Primary keyword duplikat.
- Artikel dengan search intent identik.
- Konten yang terlalu mirip.
- Topik yang sudah dibahas secara mendalam.
- Slug yang sudah digunakan.
- Cover image yang sudah digunakan berulang kali.

Buat daftar editorial agar setiap artikel memiliki nilai informasi tambahan.

## 0.5. Pre-Execution Report

Sebelum produksi, catat:

- Status direktori.
- Status linter.
- Status database.
- Status command import.
- Status internal linking.
- Status repository Git.
- Status branch dan remote.
- Status deployment pipeline.

Jika terdapat masalah, perbaiki hanya jika aman dan relevan. Jangan merusak mekanisme aplikasi yang sudah berjalan.

---

# FASE 1: REAL-TIME GOOGLE TRENDS RESEARCH & KEYWORD STRATEGY

## 1.1. Sumber Utama Google Trends

Akses:

https://trends.google.co.id/trending?geo=ID&category=3

Parameter:

- Country: Indonesia.
- Category: Business.
- Time: Data aktual hari ini.
- Search region: Indonesia.

Gunakan tanggal aktual saat eksekusi, bukan tanggal yang dikarang atau diambil dari cache lama.

Jika halaman Google Trends tidak dapat diakses secara langsung, gunakan sumber resmi atau alternatif yang kredibel dan laporkan keterbatasannya.

Jangan mengklaim melakukan riset Google Trends apabila data sebenarnya tidak berhasil diperoleh.

## 1.2. Identifikasi Topik Tren

Identifikasi topik yang berhubungan dengan:

1. UMKM dan kewirausahaan.
2. Bisnis kuliner dan F&B.
3. Retail dan perdagangan.
4. Marketplace dan social commerce.
5. Digital marketing.
6. Teknologi dan AI untuk bisnis.
7. Keuangan dan akuntansi bisnis.
8. Manajemen stok dan supply chain.
9. Distribusi dan perdagangan grosir.
10. Perubahan perilaku konsumen.
11. Regulasi bisnis.
12. Produktivitas dan operasional perusahaan kecil.

Prioritaskan tren yang memiliki dampak nyata terhadap pengusaha Indonesia.

Jangan memilih topik hanya karena sedang populer jika tidak mempunyai nilai edukasi bagi pembaca COOCA.

## 1.3. Verifikasi Tren

Untuk setiap topik:

- Verifikasi waktu tren.
- Periksa konteks pencarian.
- Periksa sumber berita atau informasi primer.
- Identifikasi apakah tren bersifat sementara atau berkelanjutan.
- Bedakan tren aktual dengan topik evergreen.
- Periksa apakah tren memiliki hubungan dengan aktivitas bisnis Indonesia.

Jika tersedia, catat:

- Search interest.
- Relative search interest.
- Related queries.
- Related topics.
- Perubahan minat pencarian.
- Periode pengamatan.
- Wilayah pengamatan.

Jangan menyebut relative search interest sebagai volume pencarian absolut.

Jika volume pencarian tidak tersedia, gunakan penilaian kualitatif dan tandai bahwa angka volume tidak tersedia.

## 1.4. Keyword Research

Untuk setiap topik, tentukan:

**Primary Keyword**
- Satu keyword utama.
- Memiliki hubungan langsung dengan isi artikel.
- Mencerminkan search intent.

**Secondary Keywords**
- Keyword pendukung.
- Long-tail keywords.
- Related search terms.
- Semantic keywords.

**Search Intent**
- Informational.
- Commercial investigation.
- Transactional jika relevan.

**Audience Problem**
- Masalah yang benar-benar dialami pelaku usaha.
- Dampak terhadap omzet, margin, arus kas, stok, atau operasional.

**Educational Angle**
- Solusi praktis.
- Penjelasan konsep.
- Simulasi bisnis.
- Langkah implementasi.
- Kesalahan yang harus dihindari.

**COOCA Relevance**
Hubungkan secara natural dengan kemampuan COOCA yang relevan:

- Point of Sale.
- Inventory Management.
- Warehouse Management.
- Manufacturing dan BOM.
- Finance dan Accounting.
- CRM.
- Employee Management.
- Omnichannel.
- Marketplace Integration.
- AI Business Assistant.
- Business Analytics.

Jangan memaksakan promosi produk apabila tidak relevan dengan pembahasan.

## 1.5. Pemisahan Trending dan Evergreen

Kategorikan artikel menjadi:

**Trending Content**

Membahas:
- Peristiwa aktual.
- Perubahan kebijakan.
- Tren marketplace.
- Teknologi baru.
- Fenomena konsumen.
- Momentum bisnis.

**Evergreen Content**

Membahas:
- Cara menghitung HPP.
- Pengelolaan stok.
- Cash flow.
- Manajemen karyawan.
- SOP operasional.
- Akuntansi UMKM.
- Pengelolaan usaha.

Artikel evergreen tetap harus memiliki keyword yang relevan dan informasi yang diperbarui.

Gunakan kombinasi konten trending dan evergreen untuk menjaga pertumbuhan trafik jangka panjang.

## 1.6. Penilaian Prioritas Topik

Gunakan sistem penilaian internal:

| Kriteria | Bobot |
|---|---:|
| Relevansi tren aktual | 20% |
| Relevansi terhadap UMKM | 20% |
| Search intent | 15% |
| Potensi pencarian | 15% |
| Kedalaman materi edukasi | 15% |
| Relevansi dengan COOCA | 10% |
| Peluang diferensiasi konten | 5% |

Nilai tidak boleh dianggap sebagai prediksi trafik aktual.

## 1.7. Penyimpanan Metadata Riset

Simpan hasil penelitian:

`database/data/articles/trending-research-{YYYY-MM-DD}.json`

Contoh struktur:

```json
{
  "research_date": "YYYY-MM-DD",
  "country": "Indonesia",
  "category": "Business",
  "source": "Google Trends",
  "topics": [
    {
      "topic": "Contoh topik",
      "trend_status": "verified",
      "trend_period": "actual observation period",
      "primary_keyword": "keyword utama",
      "secondary_keywords": [
        "keyword pendukung"
      ],
      "search_intent": "informational",
      "audience_problem": "Masalah yang dihadapi UMKM",
      "educational_angle": "Solusi yang dibahas",
      "cooca_relevance": [
        "Inventory Management"
      ],
      "sources": [],
      "priority_score": 0
    }
  ]
}
```

Isi seluruh field berdasarkan hasil penelitian nyata.

---

# FASE 2: ADVANCED CLICKBAIT HEADLINE ENGINE

Seluruh judul artikel WAJIB memiliki daya tarik klik tinggi, mengandung primary keyword secara utuh, dan tetap sesuai dengan materi artikel.

Targetnya adalah meningkatkan potensi CTR tanpa mengorbankan kredibilitas COOCA.

## 2.1. Fundamental Headline Psychology

Gunakan kombinasi:

### A. Curiosity Gap

Membangkitkan rasa penasaran.

Contoh:

- Banyak Pemilik UMKM Tidak Menyadari Kesalahan Ini Saat Menghitung Keuntungan.
- Mengapa Penjualan Ramai Belum Tentu Membuat Bisnis Menghasilkan Uang?

### B. Fear of Missing Out

Menunjukkan peluang atau perubahan yang perlu diantisipasi.

Contoh:

- Peluang Bisnis Digital Ini Mulai Dilirik UMKM, Sudah Siap Memanfaatkannya?
- Perubahan Marketplace Ini Perlu Diperhatikan Penjual Sebelum Mengubah Strategi.

### C. Loss Aversion

Menunjukkan risiko kerugian nyata.

Contoh:

- Omzet Besar tetapi Uang Selalu Habis? Ini Kesalahan Arus Kas yang Sering Terjadi.
- Stok Menumpuk dan Modal Tertahan? Periksa Cara Mengelola Persediaan Anda.

### D. Surprise & Contrarian Angle

Menentang persepsi umum berdasarkan fakta.

Contoh:

- Penjualan Naik Tidak Selalu Berarti Bisnis Makin Untung, Ini Alasannya.
- Diskon Besar Belum Tentu Membuat Bisnis Lebih Menguntungkan.

### E. Specificity & Concrete Value

Gunakan angka yang memiliki sumber atau merupakan simulasi yang diberi label.

Contoh:

- Modal Rp5 Juta untuk Memulai Usaha: Ini Rincian Biaya yang Sering Terlewat.
- Margin 20 Persen Belum Tentu Menguntungkan, Begini Cara Menghitungnya.

### F. Urgency & Timeliness

Gunakan konteks waktu aktual.

Contoh:

- Aturan Pajak UMKM Terbaru: Perubahan yang Perlu Dipahami Pengusaha.
- Biaya Marketplace Berubah? Ini Dampaknya terhadap Margin Penjual.

## 2.2. Bank Formula Headline

Gunakan setidaknya 12 variasi formula:

**A. Curiosity Gap**
- Ternyata Ini Penyebab [Masalah] yang Sering Tidak Disadari Pengusaha.
- Mengapa [Fenomena Bisnis]? Ini Fakta yang Perlu Diketahui UMKM.
- Ada yang Salah dengan [Aktivitas Bisnis]? Periksa Hal Ini.

**B. Hidden Mistakes**
- Kesalahan [Aktivitas] Ini Bisa Membuat [Dampak].
- Banyak Pengusaha Melakukan Hal Ini Tanpa Menyadari Risikonya.
- Jangan Ulangi Kesalahan Ini Saat [Aktivitas].

**C. Warning & Risk**
- Awas, [Primary Keyword] Bisa Berdampak pada [Risiko].
- Sebelum [Tindakan], Pahami Risiko [Primary Keyword].
- [Primary Keyword]: Risiko yang Sering Terlewat.

**D. Unexpected Facts**
- Ternyata [Primary Keyword] Tidak Selalu Menguntungkan.
- Banyak yang Salah Paham tentang [Primary Keyword].
- Fakta di Balik [Fenomena Bisnis].

**E. Financial Loss**
- Omzet Besar tetapi Keuntungan Tipis? Periksa [Primary Keyword].
- Modal Terus Berkurang? Bisa Jadi [Primary Keyword] Belum Dikelola dengan Benar.
- Jangan Sampai Boncos! Cara Menghitung [Primary Keyword].

**F. Opportunity & FOMO**
- Peluang [Primary Keyword] Ini Mulai Dilirik UMKM.
- Ingin Bisnis Lebih Efisien? Kenali [Primary Keyword].
- [Primary Keyword] Bisa Membuka Peluang Baru bagi UMKM.

**G. Deadline & Regulation**
- [Primary Keyword] Terbaru: Perubahan yang Wajib Dipahami Pengusaha.
- Batas Waktu [Primary Keyword] Semakin Dekat.
- Jangan Abaikan Perubahan [Primary Keyword].

**H. Comparison & Versus**
- [Pilihan A] vs [Pilihan B]: Mana yang Lebih Menguntungkan?
- Lebih Baik [Metode A] atau [Metode B]?
- Banyak Pengusaha Salah Memilih [Pilihan].

**I. Numbers & Practical Guide**
- [Angka] Cara Mengelola [Primary Keyword].
- Begini Cara Menghitung [Primary Keyword].
- Panduan [Primary Keyword]: Langkah Praktis untuk UMKM.

**J. Myth Busting**
- Benarkah [Mitos Bisnis]? Ini Faktanya.
- Jangan Mudah Percaya Anggapan tentang [Primary Keyword].
- [Primary Keyword] Tidak Sesederhana yang Dibayangkan.

**K. Case Study & Reality Check**
- Belajar dari [Fenomena]: Mengapa Bisnis Mengalami [Masalah]?
- Simulasi [Primary Keyword]: Berapa Keuntungan Sebenarnya?
- Dari Omzet hingga Laba Bersih: Begini Perhitungannya.

**L. Trend Hijacking**
- [Primary Keyword] Sedang Menjadi Perhatian, Ini Dampaknya bagi UMKM.
- Mengapa [Topik Tren] Mendadak Ramai Dibicarakan?
- [Fenomena Aktual] Mengubah Cara Pengusaha Berbisnis.

## 2.3. Primary Keyword Requirement

1. Primary keyword wajib muncul secara utuh dalam H1.
2. Tempatkan keyword sedekat mungkin dengan awal judul.
3. Jangan memaksakan keyword.
4. Jangan melakukan keyword stuffing.
5. Judul harus menjawab kebutuhan pencarian.
6. Hindari keyword yang tidak menggambarkan materi utama.

## 2.4. Anti-Fake Clickbait

DILARANG:

- Mengarang ancaman denda.
- Mengarang regulasi terbaru.
- Mengarang deadline.
- Menggunakan statistik tanpa sumber.
- Menjanjikan keuntungan pasti.
- Mengklaim bisnis pasti sukses.
- Membuat seolah-olah terjadi krisis nasional.
- Menggunakan kata viral atau terbongkar tanpa bukti.
- Menggunakan angka tanpa dasar.
- Menampilkan informasi lama sebagai informasi terbaru.

Setiap janji dalam headline wajib dijawab dalam artikel.

## 2.5. Headline SEO Standards

| Elemen | Standar |
|---|---|
| H1 | 65-95 karakter |
| Target H1 | 70-85 karakter |
| Meta Title | Maksimal 60 karakter |
| Meta Description | 140-160 karakter |
| Primary Keyword | Wajib utuh dalam H1 |
| Slug | Lowercase, ringkas, keyword utama |
| CTR Appeal | Curiosity, risk, opportunity, surprise |
| Editorial Quality | Natural dan kredibel |

Kualitas bahasa lebih penting daripada pemenuhan panjang secara kaku.

## 2.6. Headline Scoring

Buat minimal 10 alternatif judul untuk setiap topik.

Gunakan bobot:

| Kriteria | Bobot |
|---|---:|
| Relevansi search intent | 25% |
| Curiosity dan daya tarik klik | 25% |
| Kekuatan primary keyword | 20% |
| Kejelasan manfaat | 15% |
| Akurasi dan kredibilitas | 15% |

Pilih skor tertinggi setelah pemeriksaan editorial.

Skor bukan prediksi CTR.

## 2.7. Editorial Style Distribution

Gunakan variasi:

- 25% curiosity dan hidden facts.
- 20% financial loss dan business mistakes.
- 15% warning dan risk.
- 15% opportunity dan FOMO.
- 10% comparison dan unexpected facts.
- 10% practical guide dan numbers.
- 5% regulation dan deadline.

Distribusi bersifat fleksibel.

## 2.8. Final Headline Checklist

- [ ] Primary keyword tercantum secara natural.
- [ ] Judul memiliki daya tarik.
- [ ] Ada curiosity atau urgensi yang relevan.
- [ ] Manfaat pembaca jelas.
- [ ] Tidak generik.
- [ ] Tidak terasa seperti AI slop.
- [ ] Tidak duplikat dengan artikel sebelumnya.
- [ ] Tidak mengandung klaim tanpa bukti.
- [ ] Isi artikel menjawab janji judul.
- [ ] Tidak menggunakan em dash atau en dash.

Simpan seluruh alternatif, skor, dan alasan pemilihan di metadata riset.

---

# FASE 3: SEO BLOG ARTICLE PRODUCTION

## 3.1. Jumlah Artikel

Target ideal:

- 3-5 artikel prioritas per sesi harian.
- Maksimal 10 artikel apabila kualitas riset dan produksi tetap terjaga.

Jangan memaksakan jumlah artikel apabila sumber dan materi belum memadai.

Prioritaskan kualitas dan diferensiasi konten.

## 3.2. Article Specifications

| Parameter | Standar |
|---|---|
| Word count | 1.200-1.500 kata |
| H1 | Tepat 1 |
| H2 | 4-8 |
| H3 | Sesuai kebutuhan |
| Bahasa | Indonesia profesional |
| Primary keyword | Natural |
| Internal links | 3-5 |
| FAQ | 4-6 |
| JSON-LD | Article + FAQPage |
| Cover image | Wajib |
| Meta title | Maksimal 60 karakter |
| Meta description | 140-160 karakter |

Word count mengikuti definisi validator proyek.

## 3.3. Editorial Writing Standards

Gunakan gaya:

- Profesional.
- Edukatif.
- Bersahaja.
- Solutif.
- Mudah dipahami pemilik UMKM.
- Berdasarkan fakta.
- Memiliki contoh bisnis Indonesia.
- Tidak terlalu akademis.
- Tidak terlalu promosi.

Target pembaca:

- Pemilik UMKM.
- Pedagang.
- Pengusaha F&B.
- Retailer.
- Reseller.
- Distributor.
- Pemilik toko online.
- Pengelola bisnis.

## 3.4. Struktur Wajib Artikel

### 1. Pendahuluan

Jelaskan:

- Konteks tren.
- Mengapa topik penting.
- Masalah yang dihadapi pengusaha.
- Manfaat membaca artikel.

### 2. Pembahasan Inti

Jelaskan:

- Definisi.
- Konsep.
- Penyebab.
- Mekanisme.
- Dampak bisnis.
- Contoh nyata jika tersedia.

### 3. Panduan Praktis

Berikan langkah implementasi secara berurutan.

Contoh:

1. Identifikasi kondisi usaha.
2. Kumpulkan data.
3. Hitung kebutuhan.
4. Terapkan metode.
5. Evaluasi hasil.

### 4. Studi Kasus atau Simulasi

Gunakan angka realistis.

Jika menggunakan angka ilustrasi, beri label:

**Catatan: Angka berikut merupakan simulasi untuk menjelaskan metode perhitungan, bukan data statistik aktual.**

Jangan menyamarkan angka simulasi sebagai hasil penelitian.

### 5. Kesalahan Umum

Bahas kesalahan yang berpotensi merugikan usaha.

### 6. Relevansi Teknologi dan COOCA

Jelaskan bagaimana teknologi dapat membantu menyelesaikan masalah.

Contoh:

- Inventory untuk mengontrol stok.
- POS untuk mencatat transaksi.
- Finance untuk mengawasi arus kas.
- BOM untuk mengelola konsumsi bahan baku.
- AI Assistant untuk membantu analisis bisnis.

Jangan menggunakan promosi agresif.

### 7. Kesimpulan

Rangkum insight utama dan rekomendasi implementasi.

### 8. FAQ

Buat 4-6 pertanyaan berdasarkan kebutuhan pembaca dan search intent.

Jawaban harus substantif, bukan sekadar pengulangan isi artikel.

### 9. JSON-LD

Tambahkan schema Article dan FAQPage yang valid.

Pastikan seluruh informasi schema sesuai dengan konten yang terlihat.

Jangan memasukkan FAQ yang tidak ditampilkan kepada pembaca.

## 3.5. Writing Restrictions

DILARANG:

- AI slop.
- Kalimat berulang.
- Paragraf tanpa substansi.
- Keyword stuffing.
- Klaim tanpa sumber.
- Statistik palsu.
- Promosi berlebihan.
- Kesimpulan yang mengulang seluruh artikel.
- Menggunakan em dash (—).
- Menggunakan en dash (–).

Gunakan tanda hubung biasa (-), koma, atau titik dua.

## 3.6. Source Verification

Gunakan referensi yang relevan:

1. Website pemerintah.
2. Dokumentasi resmi perusahaan.
3. Laporan penelitian.
4. Publikasi institusi.
5. Media bisnis kredibel.
6. Dokumentasi resmi marketplace.
7. Sumber akademik.

Untuk topik aktual, prioritaskan sumber terbaru.

Simpan:

- Judul sumber.
- URL.
- Penerbit.
- Tanggal publikasi.
- Tanggal akses.
- Klaim yang didukung sumber.

Jangan mengutip sumber yang tidak benar-benar dibaca atau diverifikasi.

## 3.7. Frontmatter Markdown

Sesuaikan dengan format yang digunakan sistem blog COOCA.

Contoh konseptual:

```yaml
---
title: "Judul Artikel"
slug: "slug-artikel"
category: "Bisnis UMKM"
primary_keyword: "keyword utama"
meta_title: "Judul SEO"
meta_description: "Deskripsi SEO"
cover_image: "https://cooca.id/images/blog/slug-artikel.jpg"
image_alt: "Deskripsi gambar sesuai keyword"
status: "published"
---
```

Jangan mengubah nama field atau menambahkan field yang tidak didukung parser tanpa melakukan pemeriksaan terlebih dahulu.

---

# FASE 4: ADVANCED COVER IMAGE RESEARCH, AI IMAGE GENERATION & COOCA ASSET OWNERSHIP

**MANDATORY: SETIAP ARTIKEL WAJIB MEMILIKI COVER IMAGE YANG RELEVAN, TERBARU, DAN DAPAT DIGUNAKAN SECARA LEGAL.**

Cover image merupakan bagian penting dari SEO, visual storytelling, editorial quality, dan identitas COOCA.

Tidak boleh menggunakan gambar generik hanya untuk memenuhi persyaratan cover image.

## 4.1. Image Research Berdasarkan Keyword

Sebelum mengambil atau membuat gambar, lakukan riset berdasarkan:

1. Primary keyword.
2. Secondary keywords.
3. Judul H1.
4. Konteks pembahasan.
5. Objek utama.
6. Aktivitas bisnis.
7. Industri terkait.
8. Periode tren.

Gunakan kombinasi keyword spesifik.

Contoh:

| Primary Keyword | Keyword Pencarian Gambar |
|---|---|
| Manajemen stok UMKM | Retail inventory stock counting small business warehouse |
| Biaya admin marketplace | Online seller marketplace transaction fee analysis |
| AI untuk bisnis UMKM | Small business owner using AI business analytics |
| HPP minuman | Beverage production cost calculation ingredients |
| Laporan keuangan UMKM | Small business financial statement analysis |
| Manufaktur UMKM | Small scale manufacturing production workflow |
| Omnichannel retail | Retail store marketplace order management |

Jangan hanya mencari menggunakan kata business, office, entrepreneur, atau technology.

## 4.2. Ketentuan Gambar Terbaru

Untuk setiap artikel:

- Cari gambar terbaru yang relevan.
- Prioritaskan gambar yang sesuai dengan periode tren.
- Periksa tanggal publikasi sumber.
- Periksa tanggal pengambilan gambar jika tersedia.
- Verifikasi konteks visual.
- Pastikan gambar tidak menggambarkan versi teknologi yang sudah usang.
- Pastikan gambar sesuai dengan kondisi aktual apabila membahas peristiwa tertentu.

**Penting:**

Tanggal gambar diunggah bukan selalu tanggal gambar diambil.

Jika tanggal pengambilan tidak diketahui, tandai sebagai tidak diketahui. Jangan mengarang tanggal aktualitas gambar.

Untuk artikel evergreen, gambar tidak harus berasal dari tahun berjalan. Yang wajib adalah relevansi dan kebenaran konteks visualnya.

## 4.3. Image Source Priority

Urutan sumber:

**PRIORITAS 1: Official Sources**

- Website resmi perusahaan.
- Press release.
- Media kit.
- Dokumentasi resmi produk.
- Dokumentasi pemerintah.
- Sumber visual institusi.

**PRIORITAS 2: Licensed Stock Images**

- Unsplash.
- Pexels.
- Pixabay.
- Wikimedia Commons.
- Penyedia stock image legal lainnya.

Periksa ketentuan lisensi setiap gambar.

Jangan menganggap seluruh gambar pada sebuah platform otomatis boleh digunakan secara komersial tanpa batas.

**PRIORITAS 3: AI IMAGE GENERATION**

Jika gambar yang ditemukan tidak memenuhi standar, WAJIB membuat gambar baru dengan AI Image Generator.

## 4.4. Mandatory AI Image Generation Fallback

Gunakan AI Image Generator apabila:

1. Tidak ditemukan gambar yang sesuai primary keyword.
2. Gambar terlalu generik.
3. Gambar tidak menggambarkan objek utama.
4. Gambar sudah usang.
5. Resolusi tidak mencukupi.
6. Gambar memiliki watermark.
7. Hak penggunaan tidak jelas.
8. Gambar tidak dapat digunakan secara legal.
9. Gambar tidak sesuai dengan visual storytelling artikel.
10. Gambar hasil pencarian tidak dapat diverifikasi.

**Jangan menggunakan gambar yang tidak relevan hanya karena tidak ingin melakukan generate.**

Jika AI Image Generator tersedia, gunakan langsung untuk menghasilkan gambar pengganti.

Jika tool image generator tidak tersedia atau gagal, jangan berpura-pura telah menghasilkan gambar. Laporkan kegagalan dan hentikan publikasi artikel yang belum memiliki cover image valid.

## 4.5. AI Image Generation Standards

Setiap gambar harus memenuhi:

- Photorealistic.
- Professional editorial photography.
- High resolution.
- Natural lighting.
- Realistic objects.
- Natural human anatomy.
- Professional composition.
- Clear visual focal point.
- Relevant business context.
- Authentic Indonesian business environment jika relevan.
- No watermark.
- No random text.
- No unrelated objects.
- No misleading logos.

Hindari visual yang terlalu sintetis atau terlihat seperti template AI generik.

## 4.6. Dynamic Image Prompt Engineering

Jangan menggunakan prompt yang sama untuk semua artikel.

Buat prompt berdasarkan konten aktual.

Template:

```text
Create a premium photorealistic editorial cover image.

ARTICLE TITLE:
[Actual Article Title]

PRIMARY KEYWORD:
[Actual Primary Keyword]

SECONDARY CONTEXT:
[Supporting Article Context]

MAIN SUBJECT:
[Specific Object or Activity]

BUSINESS ENVIRONMENT:
[Specific Business Setting]

VISUAL STORYTELLING:
[Describe the activity or business problem being illustrated]

COMPOSITION:
16:9 landscape editorial photography.
Professional commercial photography.
Clear focal point.
Balanced composition.
Natural lighting.
Realistic materials and textures.
High visual clarity.

VISUAL STYLE:
Modern Indonesian business editorial photography.
Premium business magazine aesthetic.
Authentic and believable environment.
Professional yet natural.

COLOR DIRECTION:
Natural photography colors.
Subtle navy and cyan accents when appropriate.
No excessive color effects.

RESTRICTIONS:
No watermark.
No random typography.
No fake logos.
No unrelated business objects.
No distorted human anatomy.
No unrealistic equipment.
No generic corporate stock photography.
No visual elements unrelated to the article.
```

Semua placeholder harus diisi berdasarkan isi artikel.

## 4.7. COOCA Visual Identity

Seluruh cover image mengikuti karakter visual COOCA:

- Navy.
- Cyan.
- White.
- Natural photography colors.
- Minimalist.
- Modern.
- Professional.
- Editorial.

DILARANG:

- Gradient berlebihan.
- Glassmorphism.
- Glow.
- Blob decoration.
- Watermark.
- Logo palsu.
- Teks acak.
- Elemen dekoratif yang tidak berkaitan.

Logo COOCA tidak wajib dimasukkan ke dalam gambar.

Prioritaskan relevansi visual daripada penempatan logo.

## 4.8. Image Output Specification

| Parameter | Standar |
|---|---|
| Aspect ratio | 16:9 |
| Target dimensions | 1536 x 864 px atau lebih |
| Format | JPG/JPEG |
| Color profile | sRGB |
| Quality | High |
| Composition | Landscape |
| File naming | `{slug}.jpg` |
| Storage | Local repository |
| Optimization | Compressed without visible quality loss |

Jika hasil generator memiliki dimensi berbeda:

1. Resize secara proporsional.
2. Crop ke 16:9.
3. Jangan mendistorsi objek.
4. Jangan memotong objek utama.
5. Optimalkan ukuran file.
6. Verifikasi hasil akhir.

## 4.9. COOCA Asset Storage

Seluruh gambar wajib disimpan pada:

```text
public/
└── images/
    └── blog/
        ├── slug-artikel-1.jpg
        ├── slug-artikel-2.jpg
        └── slug-artikel-3.jpg
```

Jangan hanya menyimpan URL eksternal.

Jangan menggunakan hotlink.

Jangan menghapus atribusi atau mengubah status lisensi gambar pihak ketiga.

**Penting mengenai kepemilikan aset:**

- Gambar yang dihasilkan AI dicatat sebagai AI-generated.
- Gambar stock dicatat sebagai third-party licensed asset.
- Gambar resmi perusahaan dicatat sebagai official source asset.
- Hak penggunaan mengikuti lisensi dan ketentuan yang berlaku.
- Jangan menyebut seluruh gambar sebagai karya asli COOCA apabila sumbernya pihak ketiga.

## 4.10. Image Metadata

Simpan metadata sumber gambar di dalam metadata riset artikel.

Contoh:

```json
{
  "image": {
    "filename": "slug-artikel.jpg",
    "local_path": "public/images/blog/slug-artikel.jpg",
    "public_url": "https://cooca.id/images/blog/slug-artikel.jpg",
    "source_type": "ai_generated",
    "provider": "Image Generator",
    "source_url": null,
    "license": "AI-generated, subject to provider terms",
    "created_at": "YYYY-MM-DD",
    "keyword_match": "verified",
    "dimensions": "1536x864",
    "format": "jpg"
  }
}
```

Untuk stock image, isi `source_type`, `source_url`, dan informasi lisensi yang benar.

Jangan membuat klaim kepemilikan yang tidak didukung.

## 4.11. Frontmatter Image

Contoh:

```yaml
cover_image: "https://cooca.id/images/blog/{slug}.jpg"
image_alt: "Deskripsi visual sesuai primary keyword"
```

JSON-LD:

```json
{
  "@type": "Article",
  "image": [
    "https://cooca.id/images/blog/{slug}.jpg"
  ]
}
```

Pastikan:

- URL absolut.
- Domain internal COOCA.
- Nama file sesuai slug.
- File benar-benar tersedia.
- Tidak menggunakan Unsplash atau URL eksternal pada frontmatter final.
- Tidak menggunakan URL eksternal pada JSON-LD final.

## 4.12. Image SEO Optimization

Setiap gambar wajib:

1. Memiliki nama file SEO-friendly.
2. Memiliki alt text deskriptif.
3. Mengandung konteks keyword secara natural.
4. Memiliki ukuran optimal.
5. Tidak mengalami distorsi.
6. Menggunakan resolusi yang cukup.
7. Dapat dibaca browser.
8. Dapat diakses melalui URL produksi.
9. Tidak menghasilkan HTTP 404.
10. Tidak mengandung watermark.

## 4.13. Visual Quality Assurance

Lakukan pemeriksaan visual menggunakan image viewer.

Jangan hanya memeriksa apakah file berhasil dibuat.

Checklist:

- [ ] Objek utama sesuai keyword.
- [ ] Visual sesuai isi artikel.
- [ ] Gambar aktual atau relevan dengan konteks.
- [ ] Tidak ada objek yang menyesatkan.
- [ ] Tidak ada anatomi manusia yang rusak.
- [ ] Tidak ada teks acak.
- [ ] Tidak ada watermark.
- [ ] Tidak ada logo palsu.
- [ ] Tidak terlihat generik.
- [ ] Resolusi memenuhi standar.
- [ ] Format JPG valid.
- [ ] File tersimpan secara lokal.
- [ ] Lisensi atau metode pembuatan tercatat.
- [ ] Alt text tersedia.
- [ ] Frontmatter benar.
- [ ] JSON-LD benar.

**GATE:** Artikel tidak boleh dilanjutkan ke import jika cover image belum lolos seluruh pemeriksaan wajib.

---

# FASE 5: INTERNAL LINKING & SEO ARCHITECTURE

## 5.1. Internal Link Source

Satu-satunya sumber internal link yang boleh digunakan:

`database/data/blog_links.json`

Baca file tersebut sebelum menulis artikel.

Jangan membuat URL internal berdasarkan asumsi.

## 5.2. Internal Linking Requirement

Setiap artikel wajib memiliki minimal 3-5 internal link unik.

Ketentuan:

- Link relevan dengan topik.
- Anchor text natural.
- Mengarah ke artikel yang benar-benar tersedia.
- Tidak menggunakan URL yang tidak ditemukan dalam database.
- Tidak mengulang anchor text secara berlebihan.
- Tidak memaksakan link yang tidak relevan.

## 5.3. SEO Architecture

Pastikan:

- Satu primary keyword utama.
- Secondary keyword tersebar natural.
- H1 hanya satu.
- H2 maksimal delapan.
- H3 digunakan seperlunya.
- Meta title unik.
- Meta description unik.
- Slug unik.
- Canonical URL sesuai sistem.
- Internal linking valid.
- Schema valid.

Hindari keyword cannibalization.

---

# FASE 6: STRICT CONTENT LINTING & QUALITY ASSURANCE

Semua artikel wajib lolos validasi.

## 6.1. Jalankan Linter

Untuk setiap artikel:

```bash
python scripts/lint_content.py database/data/articles/{slug}.md --links database/data/blog_links.json
```

## 6.2. Target Wajib

<target>
0 FAIL
0 WARN
</target>

Persyaratan:

- Tidak ada em dash.
- Tidak ada en dash.
- Tidak ada broken links.
- Tidak ada unauthorized links.
- Cover image tersedia.
- Cover image sesuai keyword.
- Judul mengandung primary keyword.
- Word count minimal 1.200 kata.
- Internal link minimal tiga.
- H2 maksimal delapan.
- Metadata valid.
- Artikel memiliki struktur yang benar.

## 6.3. Perbaikan Otomatis

Jika terdapat error:

1. Baca pesan error.
2. Identifikasi penyebab.
3. Perbaiki artikel.
4. Jalankan ulang linter.
5. Ulangi hingga 0 FAIL dan 0 WARN.

Jangan menghapus aturan linter untuk menghilangkan error.

Jangan mengurangi kualitas konten demi mendapatkan status PASS.

## 6.4. Additional Editorial QA

Lakukan pemeriksaan tambahan:

- Fakta sesuai sumber.
- Judul sesuai isi.
- Tidak ada pengulangan paragraf.
- Tidak ada klaim tanpa bukti.
- Angka simulasi diberi label.
- FAQ menjawab kebutuhan pembaca.
- Schema JSON-LD valid.
- Gambar relevan.
- Internal link berfungsi.
- Tidak ada artikel duplikat.

---

# FASE 7: IMPORT KE DATABASE LOKAL

Import hanya artikel yang telah lolos seluruh validasi.

## 7.1. Pre-Import

Periksa:

- File Markdown.
- Frontmatter.
- Slug.
- Kategori.
- Meta title.
- Meta description.
- Cover image.
- Schema.
- Word count.
- Status linting.

## 7.2. Jalankan Import

```bash
php artisan blog:import-articles --force
```

Gunakan `--force` hanya setelah memastikan perilaku command aman.

Periksa source command untuk memahami apakah proses tersebut:

- Membuat artikel baru.
- Memperbarui artikel yang sudah ada.
- Mengubah status publikasi.
- Menghapus atau menimpa field.
- Melakukan sinkronisasi data.

Jangan sampai artikel lama tertimpa secara tidak sengaja.

## 7.3. Database Verification

Verifikasi setiap artikel:

- Record tersedia.
- Slug benar.
- Title benar.
- Category benar.
- Meta title benar.
- Meta description benar.
- Cover image benar.
- Status `published`.
- Tidak ada duplikasi record.

Jangan hanya mengandalkan output terminal.

Gunakan database atau mekanisme pemeriksaan aplikasi yang sesuai.

---

# FASE 8: GIT COMMIT & PRODUCTION DEPLOYMENT

Setelah import lokal berhasil, lanjutkan deployment.

## 8.1. Git Preflight

Periksa:

```bash
git status
git branch --show-current
git remote -v
git log -5 --oneline
```

Pastikan:

- Repository benar.
- Branch benar.
- Remote benar.
- Tidak ada perubahan pengguna yang tidak berkaitan.
- Tidak ada credential.
- Tidak ada file `.env`.
- Tidak ada file cache.
- Tidak ada file sensitif.

Jangan menimpa pekerjaan yang sudah ada.

## 8.2. Stage Files

Stage hanya file yang berkaitan dengan artikel dan aset gambar.

Contoh:

```bash
git add database/data/articles/{slug}.md
git add database/data/articles/trending-research-{YYYY-MM-DD}.json
git add public/images/blog/{slug}.jpg
```

Untuk banyak artikel, gunakan daftar file yang sudah diverifikasi.

Jangan langsung menggunakan `git add .`.

## 8.3. Commit

Format:

```bash
git commit -m "feat(blog): publish daily trending articles with verified images [YYYY-MM-DD]"
```

Pastikan commit hanya memuat perubahan yang dimaksud.

## 8.4. Push

```bash
git push origin main
```

Jangan melakukan force push.

Jika push gagal:

- Periksa penyebab.
- Periksa remote.
- Periksa konflik.
- Jangan menghapus commit atau perubahan pengguna secara sembarangan.
- Laporkan status gagal jika belum berhasil.

## 8.5. Production Deployment Mechanism

Server COOCA menggunakan scheduler cron (`cron.sh`) yang menjalankan proses sinkronisasi repository dan import artikel secara berkala.

Mekanisme yang diharapkan:

1. Git push berhasil.
2. Scheduler production berjalan.
3. Server melakukan `git pull origin main`.
4. Server menjalankan import artikel.
5. Database production diperbarui.
6. Artikel tampil pada halaman admin.
7. Artikel tersedia pada halaman blog publik.

Jangan menganggap scheduler pasti berhasil hanya karena Git push sukses.

## 8.6. Production Verification

Verifikasi:

**Admin:**

https://cooca.id/admin/posts

**Public Blog:**

https://cooca.id/blog

Untuk setiap artikel:

- Cari berdasarkan judul.
- Cari berdasarkan slug.
- Pastikan status published.
- Periksa isi artikel.
- Periksa meta title.
- Periksa meta description.
- Periksa cover image.
- Periksa internal links.
- Periksa JSON-LD.
- Pastikan halaman tidak menghasilkan error.

## 8.7. Public Image Verification

Untuk setiap gambar:

```text
https://cooca.id/images/blog/{slug}.jpg
```

Verifikasi:

- HTTP 200.
- Content-Type image/jpeg.
- Gambar dapat dibuka.
- Dimensi sesuai.
- Tidak broken.
- Tidak menggunakan URL eksternal.

Jika production belum selesai melakukan sinkronisasi, tunggu proses yang memang tersedia atau laporkan status pending.

Jangan mengklaim artikel sudah live apabila belum terverifikasi.

---

# FASE 9: EXECUTION REPORT & FINAL DASHBOARD

Setelah seluruh proses selesai, buat laporan eksekusi yang dapat diaudit.

## 9.1. Executive Summary

Tampilkan:

- Tanggal riset.
- Jumlah topik tren ditemukan.
- Jumlah topik tervalidasi.
- Jumlah artikel diproduksi.
- Jumlah artikel lolos linting.
- Jumlah gambar ditemukan.
- Jumlah gambar AI-generated.
- Jumlah gambar gagal validasi.
- Jumlah artikel berhasil di-import.
- Status Git commit.
- Status Git push.
- Status production deployment.

## 9.2. Trending Research Report

| Topik | Primary Keyword | Trend Status | Search Intent | Priority |
|---|---|---|---|---|
| Topik 1 | Keyword 1 | Verified | Informational | High |
| Topik 2 | Keyword 2 | Verified | Commercial | High |

Gunakan data hasil riset nyata.

## 9.3. Article Production Report

| Judul Artikel | Primary Keyword | Slug | Word Count | Category | Lint |
|---|---|---|---:|---|---|
| Judul 1 | Keyword 1 | slug-1 | 1.350 | Bisnis | PASS |
| Judul 2 | Keyword 2 | slug-2 | 1.420 | Teknologi | PASS |

## 9.4. Image Asset Report

| Artikel | Image Source | Keyword Match | Resolution | Local Asset | Public URL |
|---|---|---|---|---|---|
| Artikel 1 | AI Generated | Verified | 1536x864 | PASS | Verified |
| Artikel 2 | Licensed Stock | Verified | 1536x864 | PASS | Verified |

Tambahkan informasi lisensi dan tanggal pembuatan/pengambilan jika tersedia.

Keyword match merupakan evaluasi editorial, bukan metrik SEO resmi.

## 9.5. Deployment Report

| Item | Status |
|---|---|
| Local linting | PASS / FAIL |
| Database import | PASS / FAIL |
| Local published status | PASS / FAIL |
| Git commit | PASS / FAIL |
| Git push | PASS / FAIL |
| Production pull | VERIFIED / PENDING / FAILED |
| Production import | VERIFIED / PENDING / FAILED |
| Admin publication | VERIFIED / PENDING / FAILED |
| Public blog | VERIFIED / PENDING / FAILED |
| Public image | VERIFIED / PENDING / FAILED |

## 9.6. Final Deliverables

Pastikan seluruh file tersedia:

```text
database/data/articles/
├── trending-research-YYYY-MM-DD.json
├── artikel-1.md
├── artikel-2.md
└── artikel-3.md

public/images/blog/
├── artikel-1.jpg
├── artikel-2.jpg
└── artikel-3.jpg
```

---

# FASE 10: FINAL EXECUTION RULES & QUALITY GATES

Gunakan urutan berikut tanpa melewati tahapan:

```text
PROJECT DISCOVERY
       |
       v
SYSTEM & DATABASE AUDIT
       |
       v
GOOGLE TRENDS RESEARCH
       |
       v
KEYWORD RESEARCH
       |
       v
HEADLINE GENERATION
       |
       v
HEADLINE SELECTION
       |
       v
ARTICLE PRODUCTION
       |
       v
IMAGE RESEARCH
       |
       v
IMAGE VALIDATION
       |
       +-------------------------+
       |                         |
       v                         v
 IMAGE FOUND                IMAGE NOT FOUND
       |                         |
       v                         v
 LICENSE CHECK             AI IMAGE GENERATION
       |                         |
       v                         v
 IMAGE DOWNLOAD            IMAGE VALIDATION
       |                         |
       +------------+------------+
                    |
                    v
             LOCAL IMAGE ASSET
                    |
                    v
             SEO OPTIMIZATION
                    |
                    v
             INTERNAL LINKING
                    |
                    v
              CONTENT LINTING
                    |
              0 FAIL / 0 WARN
                    |
                    v
              DATABASE IMPORT
                    |
                    v
             LOCAL VERIFICATION
                    |
                    v
               GIT COMMIT
                    |
                    v
                GIT PUSH
                    |
                    v
           PRODUCTION DEPLOYMENT
                    |
                    v
           PRODUCTION VERIFICATION
                    |
                    v
             EXECUTION REPORT
```

## 10.1. Non-Negotiable Rules

**CONTENT**

- Tidak mengarang tren.
- Tidak mengarang statistik.
- Tidak mengarang regulasi.
- Tidak membuat artikel duplikat.
- Tidak melakukan keyword stuffing.
- Tidak menggunakan AI slop.
- Tidak menggunakan em dash atau en dash.

**IMAGE**

- Setiap artikel wajib mempunyai gambar.
- Gambar harus sesuai primary keyword.
- Gambar harus sesuai konteks artikel.
- Prioritaskan referensi visual terbaru.
- Jika gambar tidak ditemukan atau tidak memenuhi standar, WAJIB generate menggunakan AI Image Generator.
- Gambar wajib disimpan secara lokal.
- Gambar wajib memiliki metadata sumber.
- Lisensi wajib jelas.
- Tidak boleh menggunakan hotlink.
- Tidak boleh menggunakan gambar generik sebagai pengganti visual yang relevan.

**SEO**

- Primary keyword wajib dalam H1.
- Meta title unik.
- Meta description unik.
- Slug unik.
- Minimal 3 internal links.
- Schema JSON-LD valid.
- Cover image menggunakan URL internal COOCA.

**VALIDATION**

- 0 FAIL.
- 0 WARN.
- Word count minimal 1.200 kata.
- Maksimal 8 H2.
- Tidak ada broken links.
- Tidak ada broken images.

**DEPLOYMENT**

- Import hanya setelah validasi berhasil.
- Tidak melakukan force push.
- Tidak memasukkan file sensitif.
- Tidak menimpa pekerjaan yang tidak terkait.
- Verifikasi production sebelum menyatakan selesai.

## 10.2. Stop Conditions

Hentikan tahapan publikasi apabila:

1. Data tren tidak dapat diverifikasi.
2. Artikel mengandung klaim penting tanpa sumber.
3. Gambar tidak tersedia.
4. AI Image Generator gagal dan tidak ada gambar legal alternatif.
5. Gambar tidak sesuai keyword.
6. Linter gagal.
7. Import database gagal.
8. Terdapat konflik Git yang belum diselesaikan.
9. Push gagal.
10. Production verification belum berhasil.

Jika satu tahapan gagal, selesaikan masalah atau laporkan statusnya. Jangan melewati quality gate.

---

# FINAL INSTRUCTION

Mulai dengan melakukan audit proyek COOCA pada:

`C:\laragon\www\cooca_core\`

Kemudian jalankan seluruh tahapan dari FASE 0 hingga FASE 10.

**Jangan hanya memberikan rekomendasi, rencana, contoh kode, atau simulasi. Lakukan pekerjaan nyata pada repository apabila akses dan izin tersedia.**

Prioritas tertinggi:

1. Akurasi riset.
2. Kualitas editorial.
3. Headline dengan CTR appeal tinggi.
4. Relevansi dan aktualitas cover image.
5. AI Image Generation apabila gambar yang memenuhi standar tidak ditemukan.
6. Kepemilikan dan pengelolaan aset gambar secara legal.
7. SEO dan internal linking.
8. 0 FAIL dan 0 WARN.
9. Integritas database.
10. Keberhasilan deployment production.

**HASIL AKHIR YANG DIHARAPKAN:**

COOCA memiliki sistem produksi konten blog yang menghasilkan artikel bisnis berkualitas tinggi, berbasis tren aktual, memiliki headline yang menarik, visual yang relevan dan profesional, aset gambar tersimpan secara lokal, SEO tervalidasi, serta terpublikasi secara otomatis melalui Git ke https://cooca.id/blog.

Setiap hasil harus dapat diverifikasi. Jangan pernah menyatakan suatu tahapan berhasil apabila belum benar-benar dieksekusi dan dibuktikan.
