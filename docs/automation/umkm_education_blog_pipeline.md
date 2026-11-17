# MASTER TASK: COOCA EVERGREEN SEO CONTENT ENGINE
## 40 High-Quality Articles Daily | Topical Authority | High-CTR Headlines | SEO | Automated Publishing

## MISSION & OBJECTIVES

Bangun topical authority COOCA sebagai platform Business Operating System dan pusat edukasi operasional UMKM Indonesia melalui sistem riset keyword, produksi konten evergreen, optimasi SEO, dan publikasi otomatis.

Target produksi adalah maksimal 40 artikel per hari, dengan distribusi 8 artikel pada masing-masing dari 5 pilar utama.

Seluruh artikel wajib memenuhi standar berikut:

1. Evergreen content yang relevan dalam jangka panjang.
2. Judul clickbait berkualitas tinggi dengan daya tarik CTR kuat.
3. Primary keyword terintegrasi secara natural dalam judul dan konten.
4. Konten edukatif, faktual, mendalam, dan memberikan solusi praktis.
5. Tidak memiliki keyword cannibalization.
6. Menggunakan aset gambar mandiri yang tersimpan di repositori COOCA.
7. Lulus seluruh validasi otomatis dengan target 0 FAIL dan 0 WARN.
8. Memiliki struktur internal linking yang relevan.
9. Tidak mengandung AI slop, repetisi, keyword stuffing, atau konten tipis.
10. Memiliki nilai informasi yang lebih baik daripada artikel kompetitor pada search intent yang sama.

**Prinsip utama:** Prioritaskan kualitas, search intent, manfaat pembaca, dan kredibilitas. Target 40 artikel bukan alasan untuk menurunkan standar editorial.

---

## PROJECT CONFIGURATION

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
9. Jalankan `git status` dan pastikan tidak ada perubahan pengguna yang akan tertimpa.
10. Sinkronkan branch dengan `git pull --rebase origin main` hanya setelah memastikan perubahan lokal aman.

### Duplicate Prevention

Jangan membuat artikel baru jika:

- Primary keyword sudah terdaftar dan memiliki search intent identik.
- Artikel sebelumnya sudah menjawab kebutuhan pencarian yang sama.
- Sudut pandang hanya berbeda pada susunan kata.
- Artikel baru tidak memberikan informasi atau manfaat tambahan.
- Artikel berpotensi bersaing dengan artikel COOCA yang sudah ada.

Jika ditemukan topik serupa, evaluasi kemungkinan memperbarui artikel lama daripada membuat artikel baru.

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

**Pilar 1: Manajemen Keuangan & Pembukuan (8 Artikel)**

Topik:
- Perhitungan HPP kuliner, retail, dan jasa.
- Food cost ratio.
- Break Even Point.
- Margin dan markup.
- Arus kas harian.
- Pemisahan rekening pribadi dan bisnis.
- Pencatatan kasbon karyawan.
- Laporan laba rugi UMKM.

**Pilar 2: Inventori, Stok & Manajemen Gudang (8 Artikel)**

Topik:
- SOP stock opname.
- Selisih stok kasir dan gudang.
- FIFO dan FEFO.
- Safety stock.
- Reorder point.
- Pengelolaan multi gudang.
- Barang rusak dan kedaluwarsa.
- Pengendalian persediaan.

**Pilar 3: Kasir, POS & Kontrol Fraud (8 Artikel)**

Topik:
- SOP buka dan tutup shift.
- Rekonsiliasi kas.
- Blind cash drop.
- Role-Based Access Control.
- Pencegahan fraud void dan retur.
- Integrasi QRIS.
- Sistem kasir offline.
- Audit transaksi kasir.

**Pilar 4: Legalitas, Perizinan & Kepatuhan Pajak (8 Artikel)**

Topik:
- NIB dan OSS RBA.
- Sertifikasi halal.
- PIRT dan BPOM.
- SAK EMKM.
- PPh Final UMKM.
- Faktur pajak dan Coretax.
- Legalitas usaha patungan.
- Administrasi dan kepatuhan usaha.

**Pilar 5: CRM, Retensi Pelanggan & Omnichannel Marketing (8 Artikel)**

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

Setiap kandidat topik harus melewati penilaian berikut.

| Kriteria | Bobot |
|---|---:|
| Relevansi jangka panjang | 25% |
| Kebutuhan nyata pelaku UMKM | 25% |
| Search intent yang jelas | 20% |
| Potensi pengembangan topical authority | 15% |
| Potensi solusi praktis | 15% |

Gunakan skor 1-5.

<badge color="success">Minimal 4/5</badge> untuk setiap artikel yang dipilih.

## 1.3. Evergreen Content Classification

Klasifikasikan setiap topik menjadi:

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

Konten harus tetap bermanfaat meskipun periode musimannya telah berakhir.

**C. Regulatory Evergreen**

Topik regulasi yang mempunyai kebutuhan pencarian berulang tetapi berpotensi mengalami perubahan.

Contoh:
- Cara Mengurus NIB.
- Cara Menghitung Pajak UMKM.
- Persyaratan Sertifikasi Halal.

Wajib menyertakan tanggal pemeriksaan informasi, sumber resmi, cakupan aturan, dan mekanisme pembaruan.

**D. Trend-Driven Content**

Konten berdasarkan fenomena atau tren aktual.

Jenis ini tidak otomatis dikategorikan evergreen. Buat artikel hanya jika memiliki nilai edukasi jangka panjang atau relevansi aktual yang cukup kuat.

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

Contoh judul generik:

- Cara Menghitung HPP UMKM.
- Tips Mengelola Stok Barang.
- Pengertian Arus Kas Bisnis.
- Panduan Menggunakan Aplikasi Kasir.

Ubah menjadi judul dengan daya tarik lebih tinggi:

- HPP UMKM Sering Salah Hitung? Ini Penyebab Keuntungan Bisnis Terus Menipis
- Stok Barang Sering Hilang Tanpa Disadari? Periksa Kesalahan Pengelolaan Ini
- Omzet Terus Naik tetapi Uang Tidak Terkumpul? Kenali Masalah Arus Kas Bisnis
- Aplikasi Kasir UMKM: Kesalahan Memilih Sistem yang Bisa Mengganggu Operasional Toko

## 2.2. Psikologi Clickbait yang Wajib Digunakan

Variasikan trigger psikologis berikut:

1. Curiosity Gap: membuat pembaca penasaran terhadap penyebab atau fakta tersembunyi.
2. Loss Aversion: menunjukkan potensi kerugian finansial atau operasional.
3. Fear of Missing Out: menunjukkan peluang yang mungkin terlewat.
4. Unexpected Facts: menyampaikan fakta yang bertentangan dengan asumsi umum.
5. Contrarian Angle: menantang kebiasaan bisnis yang dianggap benar.
6. Specificity: menggunakan nominal, angka, atau kondisi konkret yang terverifikasi.
7. Urgency: memanfaatkan batas waktu dan perubahan kebijakan yang benar-benar berlaku.
8. Problem Recognition: menggambarkan situasi yang sering dialami pembaca.
9. Transformation: menjanjikan pemahaman atau perbaikan proses yang realistis.
10. Comparison: memperlihatkan perbedaan pilihan dan konsekuensinya.

## 2.3. Bank Formula Clickbait

Gunakan variasi formula berikut.

**A. Curiosity Gap**

- Ternyata Ini Penyebab [Primary Keyword] Sering Menimbulkan Masalah pada Bisnis
- Mengapa [Primary Keyword] Tidak Berjalan Sesuai Harapan? Ini Faktanya
- Ada yang Salah dengan [Primary Keyword]? Periksa Hal yang Sering Terlewat Ini

**B. Financial Loss**

- Omzet Besar tetapi Keuntungan Tipis? Periksa [Primary Keyword] Ini
- Modal Terus Berkurang? Bisa Jadi [Primary Keyword] Belum Dikelola dengan Benar
- Jangan Sampai Boncos! Kesalahan [Primary Keyword] yang Merugikan UMKM

**C. Hidden Mistakes**

- Kesalahan [Primary Keyword] Ini Sering Tidak Disadari Pemilik Usaha
- Banyak Pengusaha Keliru Mengelola [Primary Keyword], Ini Dampaknya
- Jangan Ulangi Kesalahan Ini Saat Menggunakan [Primary Keyword]

**D. Unexpected Facts**

- Ternyata [Primary Keyword] Tidak Selalu Menguntungkan, Ini Alasannya
- Banyak yang Salah Paham tentang [Primary Keyword], Begini Faktanya
- [Primary Keyword] Tidak Sesederhana yang Dibayangkan, Ini Penjelasannya

**E. Business Warning**

- Awas, Kesalahan [Primary Keyword] Bisa Membuat Operasional Bisnis Terganggu
- Sebelum Menggunakan [Primary Keyword], Pahami Risiko yang Sering Terlewat
- Jangan Abaikan [Primary Keyword] Jika Tidak Ingin Mengalami Masalah Ini

**F. Opportunity & FOMO**

- Peluang [Primary Keyword] Ini Bisa Membantu UMKM Mengembangkan Bisnis
- Ingin Bisnis Lebih Efisien? Kenali Cara Memanfaatkan [Primary Keyword]
- [Primary Keyword] Bisa Membuka Peluang Baru, Ini Cara Memulainya

**G. Comparison**

- [Metode A] vs [Metode B]: Mana yang Lebih Menguntungkan untuk UMKM?
- Lebih Baik [Pilihan A] atau [Pilihan B]? Perhatikan Perbedaan Ini
- Jangan Salah Memilih [Primary Keyword], Ini Perbandingan yang Perlu Diketahui

**H. Myth Busting**

- Benarkah [Mitos Bisnis]? Ini Fakta yang Sering Terlewat Pengusaha
- Jangan Mudah Percaya Anggapan tentang [Primary Keyword] Ini
- [Primary Keyword] Tidak Selalu Seperti yang Dibayangkan, Ini Faktanya

**I. Practical Guide**

- Cara Mengelola [Primary Keyword] agar Bisnis Tidak Terus Mengalami Kerugian
- [Primary Keyword]: Langkah Penting yang Sering Terlewat Pemilik UMKM
- Begini Cara Menghitung [Primary Keyword] agar Tidak Salah Mengambil Keputusan

**J. Case Study**

- Simulasi [Primary Keyword]: Berapa Sebenarnya Keuntungan yang Bisa Didapat?
- Dari Omzet hingga Laba Bersih: Begini Perhitungan [Primary Keyword]
- Belajar dari Kesalahan Bisnis: Cara Memperbaiki [Primary Keyword]

**K. Operational Problem**

- Toko Ramai tetapi Pelayanan Kacau? Periksa Sistem Operasional Ini
- Stok Sering Tidak Sesuai? Begini Cara Memperbaiki Pengelolaan Persediaan
- Kasir Sering Selisih? Ini Bagian SOP yang Wajib Diperiksa

**L. Evergreen Authority**

- Panduan [Primary Keyword]: Kesalahan Penting yang Perlu Dihindari UMKM
- Memahami [Primary Keyword]: Hal yang Sering Menentukan Keuntungan Bisnis
- [Primary Keyword] untuk UMKM: Cara Kerja, Risiko, dan Solusi Praktis

## 2.4. Ketentuan Khusus Judul Evergreen

Untuk artikel evergreen:

- Hindari ketergantungan pada kata hari ini, minggu ini, terbaru, viral, dan sekarang jika tidak diperlukan.
- Jangan memasukkan tahun ke dalam judul jika tidak memberikan manfaat pencarian.
- Utamakan keyword yang tetap relevan dalam jangka panjang.
- Gunakan situasi bisnis yang nyata dan berulang.
- Judul boleh menggunakan angka jika isi artikel benar-benar mendukungnya.
- Hindari klaim bahwa suatu metode merupakan solusi terbaik untuk seluruh jenis bisnis.
- Gunakan konteks UMKM, F&B, retail, online shop, atau jasa jika memperjelas relevansi.

## 2.5. Primary Keyword Placement

Setiap H1 wajib memiliki primary keyword secara utuh dan natural.

Aturan:
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
4. Berikan skor untuk setiap alternatif.

| Penilaian | Bobot |
|---|---:|
| Search intent relevance | 25% |
| Curiosity dan click appeal | 25% |
| Primary keyword relevance | 20% |
| Value proposition | 15% |
| Accuracy dan credibility | 15% |

Pilih judul dengan nilai terbaik dan lakukan pemeriksaan editorial.

Skor internal bukan jaminan CTR aktual. Evaluasi CTR sesungguhnya menggunakan data Search Console apabila tersedia.

## 2.8. Anti-Repetitive Headline

Periksa judul artikel sebelumnya.

Dilarang:
- Menggunakan hook identik pada artikel yang berdekatan.
- Mengulang kata pembuka secara berlebihan.
- Menggunakan pola "Awas", "Jangan Sampai", "Bongkar", atau "Ternyata" pada hampir seluruh artikel.
- Menggunakan judul yang hanya mengganti nama keyword.
- Menggunakan gaya judul sensasional untuk topik sederhana yang tidak membutuhkan urgensi.

Target variasi editorial:
- 25% Curiosity dan hidden facts.
- 20% Financial loss dan mistakes.
- 15% Unexpected facts dan myth busting.
- 15% Practical guide dan transformation.
- 10% Comparison.
- 10% Opportunity dan FOMO.
- 5% Warning dan urgency.

Distribusi dapat berubah sesuai karakter topik.

## 2.9. Honest Clickbait Policy

DILARANG KERAS:

- Mengarang skandal, penipuan, atau modus kejahatan.
- Mengklaim bisnis pasti bangkrut akibat satu kesalahan.
- Menggunakan ancaman denda tanpa dasar hukum.
- Mengarang angka kerugian dan persentase.
- Menggunakan kata rahasia, terbongkar, atau mengejutkan tanpa substansi.
- Mengklaim suatu fitur wajib dimiliki seluruh UMKM jika sebenarnya hanya opsional.
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

Gunakan pendekatan masalah nyata, ilustrasi singkat, atau pertanyaan yang relevan.

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

Pastikan informasi schema sama dengan konten yang terlihat oleh pembaca.

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

DILARANG:
- Pembuka artikel yang berulang.
- Kesimpulan yang hanya mengulang pendahuluan.
- Pengulangan kata transisi secara berlebihan.
- Paragraf yang hanya memperpanjang jumlah kata.
- Kalimat generik tanpa informasi.
- Daftar poin yang tidak memberikan penjelasan.
- Klaim tanpa bukti.
- Penggunaan kata-kata promosi berlebihan.
- Pola struktur yang identik pada seluruh 40 artikel.

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

Untuk artikel yang bergantung pada regulasi atau teknologi, tentukan jadwal pemeriksaan ulang.

Contoh:
- Core Evergreen: review setiap 12 bulan.
- Seasonal Evergreen: review sebelum musim terkait.
- Regulatory Evergreen: review setiap kali ada perubahan aturan dan secara berkala.
- Technology Evergreen: review ketika terjadi perubahan signifikan pada layanan atau teknologi.

Artikel evergreen tidak berarti artikel tidak perlu diperbarui.

---

# FASE 5: COVER IMAGE DAN ASET RESMI COOCA

Setiap artikel wajib memiliki gambar yang relevan dengan primary keyword.

## 5.1. Relevansi Gambar

Contoh:
- HPP: bahan baku, timbangan dapur, aktivitas produksi.
- Kasir: mesin POS, barcode scanner, transaksi.
- Gudang: rak persediaan, barcode scanner, proses stock opname.
- Keuangan: pembukuan, kalkulator, laporan keuangan.
- CRM: interaksi pelanggan, loyalty program, smartphone bisnis.

Hindari gambar abstrak yang tidak menjelaskan topik.

## 5.2. Standar Teknis

- Resolusi minimal 1200 px.
- Format JPG.
- Maksimal 300 KB setelah kompresi.
- Rasio gambar konsisten dan cocok untuk blog.
- Tidak menggunakan watermark pihak ketiga.
- Tidak menggunakan gambar yang tidak memiliki hak penggunaan.
- Jangan menggunakan gambar yang tidak relevan hanya demi memenuhi jumlah artikel.

## 5.3. Aset Self-Hosted

Simpan pada:

`public/images/blog/{slug}.jpg`

Frontmatter:

`cover_image: "https://cooca.id/images/blog/{slug}.jpg"`

JSON-LD:

`"image": "https://cooca.id/images/blog/{slug}.jpg"`

Dilarang menggunakan URL gambar eksternal pada artikel final.

Pastikan sumber gambar memiliki lisensi atau izin penggunaan yang sesuai. Self-hosting tidak otomatis menjadikan COOCA pemilik hak cipta gambar pihak ketiga.

Untuk gambar yang dibuat khusus atau dimiliki COOCA, simpan informasi asal dan lisensinya jika diperlukan.

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
- Jangan mengklaim artikel sudah tersedia sejak Juli 2026 apabila baru dibuat pada November 2026.
- Jangan memundurkan `published_at` hanya untuk membuat histori website terlihat natural.
- Simpan tanggal pembuatan, tanggal publikasi, dan tanggal pembaruan secara terpisah.

Jika melakukan migrasi artikel lama yang benar-benar pernah dipublikasikan, gunakan tanggal publikasi historis hanya jika dapat diverifikasi.

Format:

`published_at: "2026-11-17T09:00:00+07:00"`

Gunakan tanggal aktual sesuai waktu eksekusi.

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
published_at: "2026-11-17T09:00:00+07:00"
last_verified_at: "2026-11-17"
next_review_at: "2027-11-17"
---
```

Sesuaikan field dengan schema importer yang benar-benar digunakan proyek. Jangan mengubah format produksi sebelum memeriksa implementasi importer.

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
- Status linting.
- Status import.
- Status Git.
- Error dan tindakan perbaikan.

Pastikan proses dapat dilanjutkan tanpa mengulang artikel yang sudah selesai.

Jangan menandai artikel selesai sebelum seluruh validasi berhasil.

---

# FASE 10: STRICT CONTENT VALIDATION

Jalankan:

```bash
python scripts/lint_content.py database/data/articles/{slug}.md --links database/data/blog_links.json
```

Target:

<badge color="success">0 FAIL</badge> <badge color="success">0 WARN</badge>

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

Tambahkan pemeriksaan editorial manual untuk:

- Akurasi informasi.
- Relevansi judul.
- Kualitas clickbait.
- Kualitas solusi.
- Keaslian konten.
- Validitas sumber.
- Kelayakan evergreen.

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

Stage file yang memang menjadi bagian dari pekerjaan:

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

Push:

```bash
git pull --rebase origin main
git push origin main
```

Jika terjadi konflik, periksa dan selesaikan secara hati-hati. Jangan melakukan force push.

Server produksi COOCA menggunakan mekanisme deployment otomatis melalui scheduler yang menjalankan Git pull dan importer.

Setelah push:
1. Verifikasi commit berada pada remote branch.
2. Verifikasi scheduler berjalan.
3. Periksa log deployment jika tersedia.
4. Periksa hasil import di server.
5. Verifikasi halaman artikel publik.
6. Verifikasi cover image melalui URL publik.
7. Verifikasi sitemap.

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
- Artikel berhasil diimpor.
- Artikel berhasil dipublikasikan.
- Total keyword terdaftar.
- Total aset gambar.
- Status deployment.

## B. Article Production Table

| No | Judul Clickbait | Primary Keyword | Pilar | Evergreen Score | Word Count | Image | Lint | Production |
|---|---|---|---|---|---:|---|---|---|
| 1 | Judul artikel | Keyword | Keuangan | 5 | 1.750 | Verified | PASS | Published |

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

Pastikan distribusi:
- Keuangan: 8 artikel.
- Inventori: 8 artikel.
- Kasir dan POS: 8 artikel.
- Legalitas dan Pajak: 8 artikel.
- CRM dan Omnichannel: 8 artikel.

## F. Quality Assurance

Tampilkan:
- Hasil linting.
- Jumlah FAIL.
- Jumlah WARN.
- Pemeriksaan editorial.
- Validasi internal link.
- Validasi gambar.
- Validasi JSON-LD.
- Validasi keyword cannibalization.

## G. Deployment Verification

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
10. Jangan mengubah file di luar ruang lingkup pekerjaan.
11. Jangan melakukan deployment jika validasi belum berhasil.
12. Jangan mengklaim keberhasilan tanpa bukti hasil eksekusi.

**DEFINITION OF DONE:**

Satu artikel baru dianggap selesai jika:
- Keyword sudah tervalidasi.
- Tidak mengalami cannibalization.
- Judul clickbait lolos evaluasi editorial.
- Konten evergreen memenuhi standar kualitas.
- Artikel memiliki minimal 1.500 kata.
- Gambar tersedia sebagai aset internal COOCA.
- Internal linking valid.
- JSON-LD valid.
- Linting 0 FAIL dan 0 WARN.
- Database import berhasil.
- Deployment berhasil diverifikasi.

**FINAL OBJECTIVE:**

Bangun perpustakaan edukasi bisnis COOCA yang tidak hanya memiliki banyak artikel, tetapi juga memiliki topical authority yang kuat, judul dengan daya tarik klik tinggi, konten evergreen yang terus relevan, dan kualitas editorial yang layak menjadi referensi pemilik UMKM Indonesia.
