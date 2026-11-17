# TASK: GOOGLE TRENDS BUSINESS RESEARCH, BLOG ARTICLE PRODUCTION & PRODUCTION DEPLOYMENT FOR COOCA

Lakukan riset real-time Google Trends Indonesia (Kategori Bisnis), identifikasi topik tren bisnis terhangat hari ini, buat artikel blog SEO edukatif dan solutif untuk Cooca, **seluruh judul wajib clickbait berdaya pikat klik tinggi namun tetap mengandung keyword dan relevan dengan materi**, **pastikan seluruh cover image sesuai keyword dan tersimpan sebagai aset resmi milik Cooca**, validasi linting hingga 0 FAIL 0 WARN, import ke database lokal, dan lakukan push ke Git agar otomatis terpublikasi di server produksi (https://cooca.id/admin/posts).

**Direktori Proyek:** `C:\laragon\www\cooca_core`  
**Direktori Artikel:** `database/data/articles/{slug}.md`  
**Direktori Gambar (Aset Resmi Cooca):** `public/images/blog/{slug}.jpg`  
**Target Produksi:** `https://cooca.id/admin/posts` & `https://cooca.id/blog`  

---

## FASE 1: RISET GOOGLE TRENDS REAL-TIME & KEYWORD STRATEGY
1. Akses Google Trends Indonesia kategori Bisnis:
   `https://trends.google.co.id/trending?geo=ID&category=3`
2. Ekstrak topik tren bisnis aktual hari ini yang relevan dengan ekosistem UMKM Indonesia (kuliner/F&B, retail, jasa, online shop, distributor).
3. Untuk setiap topik, tentukan:
   - Primary Keyword & Secondary Keywords (long-tail & related).
   - Search Intent (informational / commercial investigation).
   - Problem Statement: Permasalahan nyata yang dihadapi pelaku usaha.
   - Solusi & Sudut Pandang Edukasi: Solusi praktis dan modul Cooca yang relevan (tanpa hard-selling).
4. Simpan metadata riset ke:
   `database/data/articles/trending-research-{YYYY-MM-DD}.json`

*Catatan: Jangan mengarang data tren atau menggunakan tren usang. Jika volume tren spesifik tidak tersedia, gunakan indikator kualitatif pencarian.*

---

## FASE 2: ADVANCED CLICKBAIT HEADLINE ENGINE (HIGH CTR, SEO & HUMAN PSYCHOLOGY)

Bangun sistem pembuatan judul artikel dengan standar editorial media bisnis profesional. Setiap judul harus memiliki daya tarik klik tinggi, memanfaatkan psikologi pembaca, mengandung keyword SEO, membangkitkan rasa penasaran, dan tetap sepenuhnya relevan dengan isi artikel.

**Target utama:**
- Meningkatkan potensi CTR dari Google Search, Google Discover, dan media sosial.
- Menarik perhatian pemilik UMKM, pengusaha, pedagang, reseller, distributor, dan pelaku bisnis Indonesia.
- Membuat pembaca merasa artikel tersebut penting untuk dibaca sekarang.
- Menghindari judul generik, monoton, kaku, dan terasa seperti hasil generasi AI.
- Mempertahankan kredibilitas COOCA sebagai media edukasi bisnis dan teknologi untuk UMKM.

### 2.1. Prinsip Fundamental Pembuatan Judul

Setiap judul WAJIB memenuhi seluruh prinsip berikut:

1. **Curiosity Gap (Memancing Rasa Penasaran)**

   Buat pembaca merasa ada informasi penting, fakta tersembunyi, penyebab, atau kesalahan yang belum mereka ketahui.

   Contoh:
   - Banyak Pemilik UMKM Tidak Menyadari Kesalahan Ini Saat Menghitung Keuntungan
   - Mengapa Penjualan Ramai Belum Tentu Membuat Bisnis Menghasilkan Uang?

2. **Fear of Missing Out (FOMO)**

   Bangun kesan bahwa pembaca berpotensi kehilangan peluang, keuntungan, atau momentum apabila mengabaikan informasi tersebut.

   Contoh:
   - Peluang Bisnis Digital Ini Mulai Dilirik UMKM, Sudah Siap Memanfaatkannya?
   - Jangan Sampai Terlambat, Ini Perubahan Marketplace yang Perlu Diantisipasi Penjual

3. **Loss Aversion (Ketakutan Mengalami Kerugian)**

   Tunjukkan konsekuensi nyata dari kesalahan operasional, pengelolaan keuangan, stok, pemasaran, dan keputusan bisnis.

   Contoh:
   - Omzet Besar tetapi Uang Selalu Habis? Ini Kesalahan Arus Kas yang Sering Terjadi
   - Stok Menumpuk dan Modal Tertahan? Bisa Jadi Cara Mengelola Persediaan Anda Salah

4. **Surprise & Contrarian Angle (Fakta yang Berlawanan dengan Persepsi)**

   Gunakan sudut pandang yang mengejutkan dan menantang asumsi umum selama dapat dibuktikan dalam artikel.

   Contoh:
   - Penjualan Naik Tidak Selalu Berarti Bisnis Makin Untung, Ini Alasannya
   - Diskon Besar Belum Tentu Menarik Lebih Banyak Pembeli, Hitung Dulu Dampaknya

5. **Specificity & Concrete Value (Nilai yang Spesifik)**

   Utamakan angka, periode, nominal, batas waktu, persentase, dan contoh konkret jika tersedia dari sumber yang dapat diverifikasi.

   Contoh:
   - Modal Rp5 Juta untuk Memulai Usaha: Ini Rincian Biaya yang Sering Terlewat
   - Margin 20 Persen Belum Tentu Menguntungkan, Begini Cara Menghitungnya

6. **Urgency & Timeliness (Relevansi Waktu)**

   Manfaatkan perubahan regulasi, kebijakan marketplace, tren konsumen, momentum musiman, dan peristiwa aktual.

   Contoh:
   - Aturan Pajak UMKM Terbaru: Hal yang Perlu Disiapkan Pengusaha Sebelum Terlambat
   - Perubahan Biaya Marketplace Mulai Diperhatikan Penjual, Begini Dampaknya pada Margin

### 2.2. Bank Formula Judul Clickbait

Gunakan minimal 12 variasi formula berikut. Sistem tidak boleh menggunakan satu pola secara terus-menerus.

**A. Formula Curiosity Gap**
- Ternyata Ini Penyebab [Masalah Bisnis] yang Sering Tidak Disadari Pengusaha
- Mengapa [Fenomena Bisnis]? Ini Fakta yang Perlu Diketahui Pelaku UMKM
- Ada yang Salah dengan [Aktivitas Bisnis]? Periksa Hal Ini Sebelum Terlambat

**B. Formula Hidden Mistakes**
- Kesalahan [Aktivitas Bisnis] Ini Bisa Membuat [Dampak Kerugian]
- Banyak Pengusaha Melakukan Hal Ini Tanpa Menyadari Risikonya
- Jangan Ulangi Kesalahan Ini Saat [Aktivitas atau Keputusan Bisnis]

**C. Formula Warning & Risk**
- Awas, [Primary Keyword] Bisa Berdampak pada [Risiko Nyata]
- Sebelum [Tindakan Bisnis], Pahami Dulu Risiko [Primary Keyword] Ini
- [Primary Keyword]: Risiko yang Sering Terlewat dan Cara Menghindarinya

**D. Formula Unexpected Facts**
- Ternyata [Primary Keyword] Tidak Selalu Menguntungkan, Ini Alasannya
- Banyak yang Salah Paham tentang [Primary Keyword], Begini Faktanya
- Fakta di Balik [Fenomena Bisnis] yang Jarang Diperhitungkan Pengusaha

**E. Formula Financial Loss**
- Omzet Besar tetapi Keuntungan Tipis? Periksa [Primary Keyword] Ini
- Modal Terus Berkurang? Bisa Jadi [Primary Keyword] Belum Dikelola dengan Benar
- Jangan Sampai Boncos! Cara Menghitung [Primary Keyword] dengan Tepat

**F. Formula Opportunity & FOMO**
- Peluang [Primary Keyword] Ini Mulai Dilirik UMKM, Bagaimana Cara Memulainya?
- Ingin Bisnis Lebih Efisien? Kenali [Primary Keyword] Sebelum Kompetitor
- [Primary Keyword] Bisa Membuka Peluang Baru bagi UMKM, Ini Cara Memanfaatkannya

**G. Formula Deadline & Regulation**
- [Primary Keyword] Terbaru: Ini Perubahan yang Wajib Dipahami Pengusaha
- Batas Waktu [Primary Keyword] Semakin Dekat, Apa yang Harus Disiapkan?
- Jangan Abaikan Perubahan [Primary Keyword], Ini Dampaknya bagi UMKM

**H. Formula Comparison & Versus**
- [Pilihan A] vs [Pilihan B]: Mana yang Lebih Menguntungkan untuk UMKM?
- Lebih Baik [Metode A] atau [Metode B]? Ini Perbandingan Biaya dan Risikonya
- Banyak Pengusaha Salah Memilih [Pilihan], Padahal Dampaknya Cukup Besar

**I. Formula Numbers & Practical Guide**
- [Angka] Cara Mengelola [Primary Keyword] agar Bisnis Tidak Merugi
- Begini Cara Menghitung [Primary Keyword] agar Tidak Salah Mengambil Keputusan
- Panduan [Primary Keyword]: Langkah Praktis yang Bisa Langsung Diterapkan UMKM

**J. Formula Myth Busting**
- Benarkah [Mitos Bisnis]? Ini Fakta yang Perlu Diketahui Pengusaha
- Jangan Mudah Percaya Anggapan tentang [Primary Keyword] Ini
- [Primary Keyword] Tidak Sesederhana yang Dibayangkan, Ini Penjelasannya

**K. Formula Case Study & Reality Check**
- Belajar dari [Fenomena atau Kasus]: Mengapa Banyak Bisnis Mengalami [Masalah]?
- Simulasi [Primary Keyword]: Berapa Sebenarnya Keuntungan yang Bisa Didapat?
- Dari Omzet hingga Laba Bersih: Begini Gambaran Sebenarnya [Primary Keyword]

**L. Formula Trend Hijacking**
- [Primary Keyword] Sedang Menjadi Perhatian, Ini Dampaknya bagi Pelaku UMKM
- Mengapa [Topik Tren] Mendadak Ramai Dibicarakan Pelaku Bisnis?
- [Fenomena Aktual] Mengubah Cara Pengusaha Berbisnis, Ini yang Perlu Diantisipasi

### 2.3. Aturan Wajib Penggunaan Primary Keyword

1. Setiap judul H1 wajib mengandung primary keyword secara utuh dan natural.
2. Jangan memaksakan keyword sehingga menghasilkan kalimat tidak wajar.
3. Tempatkan primary keyword sedekat mungkin dengan bagian awal judul apabila tetap menghasilkan kalimat yang menarik.
4. Judul harus mencerminkan search intent dan isi artikel.
5. Jangan menggunakan keyword yang hanya memiliki kemiripan semantik tetapi tidak menjelaskan topik utama.
6. Hindari pengulangan keyword yang sama secara berlebihan.
7. Setiap artikel harus memiliki sudut pandang berbeda meskipun berasal dari kategori tren yang sama.

### 2.4. Aturan Anti-Clickbait Palsu

Clickbait harus kuat, tetapi tidak boleh menipu pembaca.

DILARANG:
- Mengarang ancaman denda, sanksi, perubahan regulasi, atau deadline.
- Menggunakan kata terbaru jika informasi sebenarnya sudah usang.
- Mengklaim kebijakan berlaku untuk seluruh UMKM jika hanya berlaku pada kelompok tertentu.
- Menggunakan nominal, persentase, atau statistik tanpa sumber.
- Menggunakan kata viral, terungkap, mengejutkan, rahasia, atau terbongkar tanpa substansi yang mendukung.
- Menjanjikan keuntungan pasti, omzet meningkat, atau bisnis sukses.
- Membuat judul seolah-olah terdapat krisis nasional padahal hanya fenomena terbatas.
- Menggunakan pertanyaan sensasional yang tidak dijawab oleh isi artikel.

**Prinsip:** Setiap janji yang disampaikan dalam judul harus dijawab secara eksplisit di dalam artikel.

### 2.5. Standar Panjang dan SEO

| Elemen | Standar |
|---|---|
| H1 / Article Title | 65-95 karakter, target ideal 70-85 |
| Meta Title | Maksimal 60 karakter |
| Meta Description | 140-160 karakter |
| Primary Keyword | Wajib tercantum utuh dalam H1 |
| Slug | Ringkas, lowercase, keyword utama |
| Search Intent | Sesuai kebutuhan pencarian |
| CTR Appeal | Memiliki curiosity, urgency, risk, opportunity, atau surprise |
| Editorial Quality | Natural, informatif, tidak manipulatif |

Apabila judul H1 terlalu panjang, prioritaskan kejelasan dan daya tarik. Jangan mengorbankan kualitas bahasa hanya untuk memenuhi jumlah karakter.

Meta Title boleh berbeda dari H1. Gunakan versi yang lebih singkat, kuat, dan tetap mengandung primary keyword.

### 2.6. Sistem Pembuatan dan Seleksi Judul

Untuk setiap topik tren:

1. Buat minimal 10 alternatif judul dengan formula berbeda.
2. Pastikan setiap alternatif mengandung primary keyword.
3. Nilai seluruh kandidat berdasarkan kriteria berikut:

| Kriteria | Bobot |
|---|---:|
| Relevansi dengan search intent | 25% |
| Daya tarik klik dan curiosity gap | 25% |
| Kekuatan primary keyword | 20% |
| Kejelasan manfaat bagi pembaca | 15% |
| Akurasi dan kredibilitas | 15% |

4. Pilih satu judul dengan skor tertinggi.
5. Pastikan tidak ada judul duplikat atau terlalu mirip dengan artikel sebelumnya.
6. Lakukan pemeriksaan editorial manual sebelum artikel ditulis.
7. Simpan alternatif judul dan skor penilaiannya dalam metadata riset.

**Ketentuan penting:** Skor merupakan alat evaluasi editorial internal, bukan prediksi CTR aktual. Jangan mengklaim judul akan menghasilkan CTR tertentu tanpa data pengujian.

### 2.7. Variasi Gaya Editorial

Distribusikan gaya judul agar seluruh blog COOCA tidak terlihat monoton:

- 25% curiosity dan hidden facts.
- 20% financial loss dan business mistakes.
- 15% warning dan risk awareness.
- 15% opportunity dan FOMO.
- 10% comparison dan unexpected facts.
- 10% practical guide dan numbers.
- 5% regulation dan deadline.

Distribusi ini merupakan pedoman, bukan kewajiban kaku. Sesuaikan dengan karakter topik dan fakta tren yang ditemukan.

### 2.8. Final Quality Gate

Sebelum judul digunakan, pastikan:

- [ ] Primary keyword tercantum secara natural.
- [ ] Memiliki daya tarik klik yang kuat.
- [ ] Menimbulkan rasa penasaran atau urgensi yang relevan.
- [ ] Menawarkan manfaat atau informasi yang jelas.
- [ ] Tidak generik dan tidak terasa seperti judul AI.
- [ ] Tidak menggunakan formula yang sama secara berulang.
- [ ] Tidak membuat klaim tanpa bukti.
- [ ] Seluruh isi artikel menjawab janji judul.
- [ ] Tidak menggunakan em dash (—) maupun en dash (–).
- [ ] Tidak menggunakan kata-kata sensasional tanpa dasar faktual.

**OUTPUT WAJIB:** Simpan H1 terpilih, Meta Title, Meta Description, Primary Keyword, alternatif judul, skor evaluasi, dan alasan pemilihan judul dalam metadata riset masing-masing topik.


---

## FASE 3: PENULISAN ARTIKEL BLOG SEO BERKUALITAS TINGGI
Tulis artikel blog SEO lengkap berdasarkan hasil riset tren terhangat (rekomendasi: 3 hingga 5 artikel prioritas tren terbaik per sesi harian agar kedalaman konten maksimal, atau tepat 10 topik sesuai target harian).

**Spesifikasi Wajib Setiap Artikel:**
- **Panjang:** 1.200 - 1.500 kata per artikel.
- **Heading:** Tepat 1 H1 (clickbait + primary keyword), 4 - 8 H2 terstruktur logis, H3 jika memperjelas alur.
- **Gaya Bahasa:** Bahasa Indonesia profesional, edukatif, bersahaja, solutif, berbasis studi kasus/simulasi bisnis Indonesia.
- **DILARANG:** AI slop, kalimat berulang, keyword stuffing, promosi agresif, dan **DILARANG KERAS menggunakan em dash (—) maupun en dash (–)**. Gunakan tanda hubung biasa (-), koma, atau titik dua.
- **Struktur Isi:**
  1. Pendahuluan (Konteks tren & problem nyata UMKM).
  2. Pembahasan Inti (Konsep, penyebab, dan dampak bagi usaha).
  3. Panduan Langkah Demi Langkah / Tips Praktis.
  4. Studi Kasus / Simulasi Angka (Beri label ilustrasi/simulasi jika memakai angka contoh).
  5. Kesalahan Umum yang Harus Dihindari.
  6. Ringkasan & Kesimpulan.
  7. Bagian FAQ (4 - 6 pertanyaan & jawaban informatif).
  8. Schema JSON-LD (FAQPage & Article) yang valid di akhir artikel.

---

## FASE 4: COVER IMAGE SESUAI KEYWORD & JADIKAN ASET MILIK COOCA
1. **Relevansi Visual 100% Sesuai Keyword:**
   - Objek gambar wajib mencerminkan secara langsung konteks bisnis primary keyword (bukan ilustrasi abstrak).
2. **Wajib Menjadi Aset Mandiri Cooca:**
   - Unduh foto resolusi tinggi dan simpan langsung ke repositori lokal Cooca:
     `public/images/blog/{slug}.jpg`
   - URL pada frontmatter markdown dan JSON-LD WAJIB domain internal Cooca:
     `cover_image: "https://cooca.id/images/blog/{slug}.jpg"`
     `"image": "https://cooca.id/images/blog/{slug}.jpg"`
   - DILARANG KERAS menggunakan link eksternal Unsplash (`https://images.unsplash.com/...`) di dalam berkas artikel.
   - File gambar di-commit ke Git agar berstatus permanen sebagai aset milik Cooca.
3. **Internal Linking:**
   - Wajib menyertakan minimal 3 - 5 internal link unik per artikel, HANYA dari `database/data/blog_links.json`.

---

## FASE 5: VALIDASI KONTEN (STRICT LINTING)
Jalankan skrip validator pada setiap file artikel:
`python scripts/lint_content.py database/data/articles/{slug}.md --links database/data/blog_links.json`

Target Wajib:
- **0 FAIL** (Tidak ada em dash/en dash, tidak ada broken/unauthorized link, cover image wajib aset Cooca, judul mengandung keyword).
- **0 WARN** (Word count >= 1.200 kata, internal links >= 3, max H2 <= 8).
Jika ditemukan peringatan atau error, perbaiki langsung file markdown hingga lolos 100%.

---

## FASE 6: IMPORT KE DATABASE LOKAL
1. Setelah seluruh artikel lolos linting, jalankan perintah import lokal:
   `php artisan blog:import-articles --force`
2. Verifikasi status artikel `published` pada database lokal.

---

## FASE 7: PUSH KE GIT & PUBLIKASI OTOMATIS KE PRODUCTION (COOCA.ID)
Setelah import lokal berhasil, publikasikan artikel ke server produksi melalui Git:
1. Periksa status perubahan file:
   `git status`
2. Stage file artikel dan aset gambar milik Cooca:
   `git add database/data/articles/ public/images/blog/`
3. Lakukan commit dengan pesan terstandar:
   `git commit -m "feat(blog): publish daily trending articles with clickbait titles and cooca owned images [YYYY-MM-DD]"`
4. Push commit ke repository utama:
   `git push origin main`
5. **Mekanisme Otomatis Server Produksi:**
   Server produksi `cooca.id` menjalankan scheduler cron (`cron.sh`) setiap menit yang secara otomatis melakukan `git pull origin main` dan mengeksekusi `php artisan blog:import-articles`.

---

## FASE 8: LAPORAN EKSEKUSI
Tampilkan ringkasan eksekutif:
- Daftar Topik Tren & Keyword yang diriset.
- Tabel Artikel (Judul Clickbait, Primary Keyword, Slug, Word Count, Kategori, Verifikasi Aset Gambar Cooca, Status Linting, Status Git Push).
- Verifikasi Cover Image lokal dan URL publikasi di `https://cooca.id/admin/posts`.
