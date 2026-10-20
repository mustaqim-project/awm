---
name: "cooca-blog-writer"
description: Generate artikel blog SEO lengkap untuk website cooca.id (Business Operating System all-in-one untuk UMKM Indonesia). Skill ini wajib scraping untuk memahami Cooca, riset keyword dan trend dari internet, menulis konten human friendly tanpa em dash, dan menyisipkan internal link hanya dari URL /blog/(slug) di https://cooca.id/sitemap_blog.xml. Gunakan setiap kali user menyebut blog cooca, artikel cooca.id, konten SEO untuk Cooca, ide artikel UMKM, riset keyword blog, atau minta "buatkan artikel/konten blog", walaupun user tidak menyebut kata skill.
---

# Cooca Blog Writer

Menghasilkan artikel blog untuk cooca.id yang enak dibaca manusia, kuat di SEO, dan aman dari aturan keras milik user. Selalu jalankan 5 fase di bawah secara berurutan. Jangan menulis artikel sebelum Fase 1 sampai 3 selesai, karena kualitas artikel bergantung pada fakta produk yang benar, data keyword yang nyata, dan daftar internal link yang valid.

## Aturan keras (tidak boleh dilanggar)

1. **Tanpa em dash.** Karakter `—` (U+2014) tidak boleh muncul di bagian mana pun: judul, meta, isi, FAQ, schema. Jangan juga mengganti dengan en dash `–`. Gunakan koma, titik, titik dua, atau kurung. Aturan ini ada karena user menganggap em dash sebagai tanda teks buatan AI.
2. **Internal link hanya dari `/blog/(slug)`** hasil scraping `https://cooca.id/sitemap_blog.xml`. Halaman non-blog di situs (kalkulator, solusi, template, halaman tenant, kontak, dan seterusnya) boleh dibaca untuk riset, tetapi **tidak boleh dijadikan link**. Tidak ada link eksternal sama sekali. Jangan pernah menebak atau mengarang slug.
3. **Setiap artikel harus punya internal link** selama sitemap memuat artikel blog. Jika sitemap belum memuat satu pun `/blog/(slug)`, jangan mengarang URL. Lihat bagian "Jika belum ada artikel blog".
4. **Fakta produk hanya dari hasil scraping dan deskripsi resmi** di bagian "Fakta dasar Cooca". Jangan mengarang fitur, harga, angka pengguna, testimoni, atau statistik.

## Fakta dasar Cooca (deskripsi dari pemilik)

Cooca adalah Business Operating System all-in-one untuk UMKM Indonesia. Cakupan: kalkulasi Harga Pokok Penjualan (HPP) berbasis aktivitas (ABC), Point of Sale (POS), manajemen inventori multi-gudang, purchase order dan sales order, invoicing, CRM dan loyalty, landing page builder publik, integrasi WhatsApp Gateway, serta Omnichannel Media Sosial otomatis.

Fakta ini adalah titik awal. Fase 1 wajib memperkaya dan memverifikasinya dari situs.

## Fase 1: Scraping Cooca (wajib)

Tujuan: paham produk, audiens, bahasa brand, dan topik yang sudah dibahas.

1. `web_fetch` halaman berikut dan catat: value proposition, fitur, segmen pengguna, istilah yang dipakai Cooca, nada bahasa.
   - `https://cooca.id/`
   - `https://cooca.id/kalkulator` dan sub halamannya yang relevan dengan topik
   - `https://cooca.id/jelajah`
   - `https://cooca.id/template-pembukuan-gratis`
   - 1 sampai 3 halaman `/solusi/...` yang paling dekat dengan topik artikel
2. Simpan ringkasan sebagai **Product Brief** (maksimal 15 baris): fitur yang relevan dengan topik, masalah UMKM yang diselesaikan, istilah brand, hal yang tidak boleh diklaim.
3. Jika sebuah halaman gagal dimuat, lanjutkan dengan halaman lain dan sebutkan kegagalannya di laporan akhir. Jangan menutupi dengan karangan.

## Fase 2: Ambil daftar internal link

1. `web_fetch https://cooca.id/sitemap_blog.xml` (sitemap khusus blog, satu-satunya sumber daftar artikel). Jika ternyata berupa sitemap index, fetch juga sitemap anak.
2. Simpan XML ke file lalu jalankan:
   ```bash
   python scripts/fetch_blog_links.py --from-file sitemap_blog.xml --out blog_links.json
   ```
   Jika lingkungan punya akses jaringan ke cooca.id, cukup `python scripts/fetch_blog_links.py --out blog_links.json` (tambah `--with-titles` untuk mengambil judul halaman sebagai bahan anchor text).
3. Script hanya meloloskan URL dengan path `/blog/(slug)` dan selalu menyusun ulang ke `https://cooca.id/blog/(slug)`. Ini penting karena `<loc>` di sitemap_blog.xml bisa memuat host lokal seperti `http://127.0.0.1:9879/blog/...`; host itu tidak boleh dipakai sebagai link, selalu ganti ke domain `cooca.id`. Halaman `/blog` (indeks) tidak termasuk. Jika script tidak tersedia, saring manual dengan aturan yang sama.
4. Untuk tiap slug, tentukan topiknya dari slug (atau judul jika ada). Daftar ini adalah satu-satunya sumber internal link.

### Jika belum ada artikel blog

Sitemap blog bisa saja belum memuat `/blog/(slug)` (ini terjadi pada pengecekan awal skill ini dibuat). Dalam kasus itu:
- Tulis artikel tanpa link apa pun dan **jangan** mengisi dengan URL karangan atau halaman non-blog.
- Di akhir file, tambahkan bagian catatan editor `## Rencana internal link` berisi 3 sampai 5 topik artikel blog lain yang sebaiknya dibuat berikutnya, plus anchor text yang cocok. Setelah artikel itu terbit dan masuk sitemap, link bisa ditambahkan.
- Beri tahu user di respons bahwa artikel belum memiliki internal link karena sitemap belum memuat artikel blog.

## Fase 3: Riset keyword dan trend (wajib)

Baca `references/research-workflow.md` untuk langkah detail. Ringkasnya:

1. Tentukan topik. Jika user tidak memberi topik, usulkan 5 ide berdasarkan Product Brief lalu pilih yang paling kuat, atau tanyakan satu pertanyaan singkat jika benar-benar ambigu.
2. Jalankan `web_search` (bahasa Indonesia, gunakan tanggal hari ini untuk kueri berwaktu) minimal 6 kali dan `web_fetch` 3 sampai 5 halaman peringkat teratas:
   - kueri utama dan variasinya
   - pertanyaan yang sering diajukan orang (gaya "People Also Ask")
   - saran pencarian terkait dan long tail
   - tren terbaru topik tersebut (regulasi, musim, perilaku UMKM, platform)
   - struktur heading dan celah konten pada artikel pesaing
3. Hasilkan **Keyword Brief**: primary keyword, 5 sampai 10 secondary keyword, search intent, 5 sampai 8 pertanyaan untuk FAQ, angle unik yang belum dibahas pesaing, dan tren yang akan disebut. Jangan mengklaim volume pencarian angka pasti jika tidak ada sumbernya; gunakan label relatif (tinggi, sedang, niche).
4. Angka atau fakta dari riset boleh dipakai hanya jika terverifikasi. Sebut sumbernya dengan nama (misalnya "menurut data BPS") tanpa membuat link.

## Fase 4: Menulis artikel

Baca `references/writing-style.md` (gaya human friendly) dan `references/seo-checklist.md` (struktur dan on-page SEO) sebelum menulis.

Struktur wajib:
- Frontmatter: `title`, `meta_title` (maks 60 karakter), `meta_description` (120 sampai 160 karakter), `slug`, `primary_keyword`, `secondary_keywords`, `search_intent`, `category`, `reading_time`
- Satu `# H1` yang memuat primary keyword
- Pembuka yang langsung menyentuh masalah pembaca dan memuat primary keyword dalam 100 kata pertama
- 4 sampai 8 `## H2` berurutan logis, `### H3` bila perlu, daftar atau tabel di tempat yang membantu
- 3 sampai 5 internal link kontekstual dari daftar Fase 2 (kurang jika daftar lebih pendek), anchor text deskriptif dan bervariasi, tersebar di isi artikel, bukan menumpuk di akhir
- Bagian peran Cooca yang jujur dan relevan, hanya memakai fakta dari Product Brief, ditaruh natural (bukan iklan di setiap paragraf)
- `## FAQ` berisi 4 sampai 6 pertanyaan dari riset
- Penutup dengan ringkasan dan satu langkah berikutnya yang jelas
- Blok JSON-LD `Article` dan `FAQPage` di akhir (tanpa URL eksternal; gunakan URL kanonis `https://cooca.id/blog/(slug)` untuk artikel ini saja)

Panjang: 1.200 sampai 2.000 kata untuk topik umum, lebih panjang hanya jika topiknya memang menuntut.

## Fase 5: Verifikasi sebelum menyerahkan

Simpan artikel sebagai `.md` lalu jalankan:
```bash
python scripts/lint_content.py artikel.md --links blog_links.json
```
Script memeriksa em dash, link yang tidak diizinkan, jumlah internal link, panjang meta, keyword di posisi penting, struktur heading, dan FAQ. Perbaiki semua `FAIL` lalu jalankan ulang sampai bersih. `WARN` boleh dipertimbangkan, tetapi jelaskan jika diabaikan.

Baca ulang artikel sekali untuk hal yang tidak bisa dicek script: apakah kalimatnya terdengar seperti manusia, apakah ada klaim produk yang tidak ada di Product Brief, apakah angka punya sumber.

## Format respons ke user

Serahkan: (1) file artikel `.md` lewat `present_files`, (2) ringkasan singkat: primary keyword, intent, internal link yang dipakai (atau alasan tidak ada), tren yang dipakai, hasil lint, dan halaman yang gagal di-scrape bila ada. Jangan menempel seluruh artikel lagi di chat jika file sudah disajikan.
