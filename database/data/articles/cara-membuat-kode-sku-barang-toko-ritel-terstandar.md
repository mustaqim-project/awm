---
title: "Cara Membuat Kode SKU Barang Toko Ritel agar Kasir Tidak Keliru"
meta_title: "Cara Membuat Kode SKU Barang Toko Ritel"
meta_description: "Pelajari cara membuat kode SKU barang toko ritel yang terstandar, struktur penomoran kategori merek dan varian, serta integrasi dengan barcode kasir."
slug: "cara-membuat-kode-sku-barang-toko-ritel-terstandar"
primary_keyword: "cara membuat kode sku barang toko"
secondary_keywords: "format stock keeping unit ritel, struktur kode barang inventori, cetak barcode sku produk, sistem penomoran barang dagangan, kelola varian toko"
search_intent: "Informational"
category: "Inventori & Stok"
reading_time: "9 menit"
cover_image: "https://cooca.id/images/blog/cara-membuat-kode-sku-barang-toko-ritel-terstandar.jpg"
published_at: "2026-11-19T08:00:00+07:00"
author: Tim Edukasi COOCA
---

# Cara Membuat Kode SKU Barang Toko Ritel agar Kasir Tidak Keliru

Pernahkah kasir di toko Anda salah memindai atau salah menginput barang penjualan: seorang pembeli membeli Kaus Polos Ukuran XL seharga Rp95.000, namun di layar kasir terinput sebagai Kaus Polos Ukuran S seharga Rp65.000? Atau saat pelanggan menanyakan stok Keripik Singkong Rasa Pedas Level 3, staf gudang bingung mencarinya di komputer karena seluruh varian rasa dicampuradukkan ke dalam satu nama generik yang sama? Masalah kekacauan data stok, selisih harga kasir, dan laporan penjualan yang keliru ini bermula dari satu akar persoalan sederhana: toko tidak memiliki sistem penomoran kode barang yang baku. Memahami cara membuat kode SKU barang toko yang terstandar adalah fondasi mutlak untuk mendigitalisasi operasional retail Anda agar transaksi kasir berjalan kilat dan akurasi persediaan terjamin 100%.

SKU atau Stock Keeping Unit adalah kode alfanumerik (kombinasi huruf dan angka) unik yang diberikan kepada setiap varian produk dagangan untuk membedakannya secara spesifik berdasarkan kategori, merek, model, ukuran, warna, dan rasa.

## Mengapa Barcode Bawaan Pabrik Saja Tidak Cukup? dan Penerapan Cara Membuat Kode Sku Barang Toko

Banyak pedagang pemula bertanya: Mengapa saya harus repot membuat kode SKU sendiri jika setiap produk kemasan pabrik sudah memiliki barcode EAN-13 (garis-garis hitam putih) di kemasannya?

Jawabannya adalah karena barcode bawaan pabrik memiliki keterbatasan operasional:
1. Tidak Memuat Makna Manusiawi: Barcode bawaan pabrik (misal: 8992753120045) hanyalah deretan angka acak yang tidak bisa dibaca sekilas oleh mata manusia untuk mengetahui jenis barangnya.
2. Banyak Produk Lokal Tanpa Barcode: Produk UMKM curah, telur ayam, sayuran segar, makanan olahan rumahan, atau pakaian konveksi lokal sering kali tidak memiliki kode barcode pabrikan resmi.
3. Kerumitan Mengelola Varian Bundling: Saat toko membuat paket promo bundling 2 barang gratis 1, paket tersebut membutuhkan identitas SKU baru yang tidak ada pada kemasan pabrik individu.
4. Duplikasi Kode Saat Ganti Kemasan: Terkadang pabrik mengganti kemasan dan mengubah nomor barcode, padahal produk fisik yang dijual di toko Anda tetap sama.

Dengan memiliki sistem penomoran SKU internal toko yang rapi, Anda memiliki kendali penuh atas database produk usaha Anda. Pelajari pula [cara membuat kartu stok barang manual dan digital](https://cooca.id/blog/cara-membuat-kartu-stok-barang-manual-dan-digital) untuk menautkan nomor SKU ke kartu mutasi persediaan fisik gudang.

## Rumus Struktur Standar Kode SKU Retail Modern

Kode SKU yang baik dirancang dengan prinsip hierarki dari kategori umum menuju spesifikasi varian khusus, menggunakan 3 sampai 4 segmen karakter yang dipisahkan oleh tanda hubung strip:

Struktur Ideal: [KATEGORI] - [MEREK/SUBKATEGORI] - [MODEL/TIPE] - [VARIAN/UKURAN]

Aturan Keras Penamaan Kode SKU:
1. Huruf Kapital dan Angka: Gunakan hanya huruf besar (A-Z) dan angka (0-9).
2. Hindari Karakter Khusus: Dilarang keras menggunakan spasi, garis miring (/), koma, tanda petik, atau simbol (&, #, %) karena dapat merusak sistem database dan pemindai barcode.
3. Hindari Huruf yang Mirip Angka: Jangan gunakan huruf O (bisa tertukar dengan angka 0) atau huruf I besar (bisa tertukar dengan angka 1 atau huruf L kecil).
4. Panjang Karakter Ideal: Buat panjang kode berkisar antara 8 hingga 12 karakter agar mudah diketik secara manual saat mesin scanner mengalami kendala teknis.

Pelajari pula tata letak penataan barang di rak pada [cara menata stok barang toko sembako kelontong rapi](https://cooca.id/blog/cara-menata-stok-barang-toko-sembako-kelontong-rapi) agar kode SKU dapat dicetak pada bibir rak etalase.

## Simulasi Penerapan Kode SKU pada Tiga Sektor Bisnis Berbeda

Perhatikan contoh tabel rancangan kode SKU terstandar yang sangat mudah dibaca oleh kasir dan staf gudang:

| Sektor Usaha Retail | Nama Produk Spesifik | Struktur Segmen SKU | Kode SKU Resmi Toko | Arti dan Keterbacaan Kode |
| :--- | :--- | :--- | :--- | :--- |
| **Toko Fashion & Pakaian** | Kemeja Pria Polos Warna Putih Ukuran L | Kategori: Kemeja Pria (KMJ-P)<br>Warna: Putih (WHT)<br>Ukuran: Large (L) | **KMJP-WHT-L** | Kemeja Pria, Warna Putih, Ukuran Large |
| **Toko Fashion & Pakaian** | Celana Jeans Denim Hitam Ukuran 32 | Kategori: Jeans Denim (JNS-D)<br>Warna: Hitam (BLK)<br>Ukuran: 32 (32) | **JNSD-BLK-32** | Jeans Denim, Warna Hitam, Ukuran 32 |
| **Kafe & Bahan Minuman** | Kopi Arabika Gayo Bubuk Kemasan 250g | Kategori: Biji Kopi (KOP)<br>Origin: Gayo (GYO)<br>Bentuk: Bubuk (BBK)<br>Berat: 250g (250) | **KOP-GYO-BBK-250** | Kopi Gayo, Bentuk Bubuk, Kemasan 250 Gram |
| **Toko Kelontong & Minimarket** | Minyak Goreng Merek Barco Kemasan 2 Liter | Kategori: Minyak Goreng (MYK)<br>Merek: Barco (BRC)<br>Ukuran: 2 Liter (02L) | **MYK-BRC-02L** | Minyak Goreng, Merek Barco, Ukuran 2 Liter |
| **Toko Suku Cadang Motor** | Kampas Rem Depan Motor Honda Vario 125 | Kategori: Kampas Rem (KMP)<br>Posisi: Depan (DPN)<br>Tipe: Vario 125 (VR125) | **KMP-DPN-VR125** | Kampas Rem Depan, Motor Vario 125 |

Menganalisis tabel kode di atas:
1. Saat kasir melihat kode KMJP-WHT-L di layar komputer kasir, kasir langsung tahu dalam 1 detik bahwa barang tersebut adalah Kemeja Pria Putih Ukuran L tanpa perlu membuka kardus baju.
2. Begitu pula saat stok KOP-GYO-BBK-250 menipis, staf gudang langsung tahu bahwa yang harus dipesan ke roastery adalah Kopi Arabika Gayo varian Bubuk 250 gram, bukan varian biji sangrai.
3. Selisih harga dan barang tertukar dapat dieliminasi hingga 0% karena setiap varian memiliki identitas kode yang berdiri sendiri.

Anda juga dapat membaca [cara mengatasi dead stock barang lambat laku toko](https://cooca.id/blog/cara-mengatasi-dead-stock-barang-lambat-laku-toko) untuk mengevaluasi kode-kode SKU mana yang perputarannya sangat lambat di toko.

## Langkah Demi Langkah Menerapkan Sistem SKU di Toko Anda

Ikuti 4 langkah praktis berikut untuk memulai penomoran inventori toko retail Anda:

### Langkah 1: Buat Kamus Singkatan Kategori Baku (SKU Cheat Sheet)
Tuliskan daftar singkatan resmi untuk kategori barang toko Anda:
- MIN = Minuman Ringan
- SNK = Makanan Ringan / Snack
- ATK = Alat Tulis Kantor
- SBN = Sabun dan Pembersih
Kamus singkatan ini wajib dibagikan ke staf admin agar tidak ada yang membuat singkatan sembarangan (misal staf A membuat MNM sedangkan staf B membuat MNK).

### Langkah 2: Buat Master Spreadsheet Database Produk
Kumpulkan seluruh nama produk yang ada di toko ke dalam file master data Excel. Pisahkan kolom Kategori, Merek, Tipe, Varian Ukuran, Warna, Harga Pokok (HPP), dan Harga Jual Kasir sebelum menggabungkannya menjadi kode SKU.

### Langkah 3: Cetak Label Barcode SKU Mandiri
Gunakan printer label thermal barcode untuk mencetak kode SKU tersebut dalam bentuk stiker barcode (tipe Code-128). Tempelkan stiker barcode pada kemasan produk yang belum memiliki barcode dari pabrik.

### Langkah 4: Uji Pemindaian di Layar Kasir POS
Pindai stiker barcode baru menggunakan alat scanner barcode kasir. Pastikan nama produk, harga jual, dan foto produk muncul secara akurat di layar kasir dalam waktu kurang dari 1 detik.

## Integrasi Auto SKU Generator di Platform COOCA

Menyusun ribuan kode SKU secara manual satu per satu memakan waktu berhari-hari dan rawan kesalahan duplikasi kode. COOCA Business Operating System menyederhanakan proses ini melalui fitur Smart SKU Generator:

1. Saat Anda menambahkan produk baru di aplikasi COOCA, sistem otomatis menyusun kode SKU yang rapi berdasarkan kategori dan varian yang Anda centang.
2. Fitur Duplication Guard COOCA otomatis memblokir pembuatan kode jika ada kode SKU yang sama persis pernah didaftarkan sebelumnya di database toko.
3. Modul Barcode Designer COOCA memungkinkan Anda mencetak ribuan label barcode harga dan SKU langsung ke printer thermal tanpa software desain tambahan.
4. Pencarian produk di kasir POS COOCA mendukung pencarian cerdas: kasir dapat memindai barcode fisik, mengetik kode SKU, atau cukup mengetik penggalan nama produk.

## Kesimpulan

Kode SKU yang terstandar adalah bahasa universal yang menghubungkan kasir, staf gudang, laporan akuntansi, dan pelanggan dalam satu rantai informasi yang akurat. Menguasai cara membuat kode SKU barang toko ritel adalah langkah awal menuju manajemen toko yang modern, terukur, dan siap bertransformasi menjadi bisnis multi-cabang. Buat kamus singkatan kategori Anda hari ini, susun kode produk yang rapi, cetak label barcode mandiri, dan nikmati kemudahan operasional toko yang bebas dari kesalahan harga dan selisih persediaan.

## Daftar Periksa SOP Manajemen Gudang dan Pengendalian Persediaan Fisik

Tata kelola gudang yang rapi adalah kunci utama untuk menekan potensi penyusutan stok dan menjaga kecepatan layanan toko. Terapkan daftar periksa operasional gudang terpadu berikut:

1. Pemeriksaan Label dan Nomor Rak: Pastikan setiap lorong dan baris rak penyimpanan memiliki papan penanda lokasi (bin location) yang jelas serta kode SKU yang terbaca jelas oleh barcode scanner.
2. Pengendalian Suhu dan Kelembapan Ruang: Pantau suhu udara di ruang penyimpanan makanan olahan dan bahan baku segar agar kualitas mutu barang tidak rusak sebelum tanggal kedaluwarsa tiba.
3. Rotasi Stok Berbasis Waktu Masuk: Terapkan penataan barang dengan metode First-In First-Out (FIFO) secara disiplin, di mana barang dengan tanggal kedaluwarsa terdekat diletakkan di baris paling depan.
4. Audit Fisik Mendadak (Cycle Counting): Lakukan penghitungan fisik acak terhadap 10 hingga 15 jenis barang bernilai tinggi setiap pagi sebelum toko dibuka untuk mencocokkan stok fisik dengan data sistem.
5. Jalur Evakuasi dan Keamanan Kerja: Pastikan lorong antar-rak penyimpanan bebas dari tumpukan kardus kosong yang dapat menghalangi jalur pergerakan troli barang dan membahayakan keselamatan staf gudang.
6. Prosedur Karantina Barang Cacat: Sediakan area karantina khusus bersekat untuk menampung barang retur atau kemasan rusak agar tidak tercampur kembali ke rak penjualan aktif.
7. Dokumentasi Berita Acara Kerusakan: Setiap barang yang dinyatakan susut atau rusak wajib dibuatkan berita acara tertulis yang disetujui supervisor gudang sebelum dihapus dari kartu stok sistem.
8. Evaluasi Buffer Stock Berkala: Tinjau ulang batas persediaan pengaman minimum setiap akhir bulan untuk mengantisipasi keterlambatan kirim suplier saat musim ramai.

## FAQ

### Berapa jumlah digit karakter yang paling ideal untuk kode SKU toko kecil?
Panjang yang paling ideal berkisar antara 8 hingga 12 karakter. Kode yang terlalu pendek rawan mengalami duplikasi, sedangkan kode yang lebih dari 15 karakter sulit diingat dan memakan tempat saat dicetak di label kecil.

### Apakah kode SKU boleh menyertakan informasi harga jual barang?
Sangat tidak disarankan. Harga jual produk bisa berubah sewaktu-waktu akibat inflasi atau promosi diskon. Jika harga dicantumkan di kode SKU, Anda terpaksa mengganti kode SKU dan mencetak ulang seluruh label setiap kali harga berubah.

### Apa perbedaan antara kode SKU dan nomor barcode UPC atau EAN?
Barcode UPC atau EAN adalah kode standar internasional yang diterbitkan lembaga resmi (GS1) untuk produk pabrikan global. Sedangkan SKU adalah kode internal toko yang bebas dirancang sendiri oleh pemilik bisnis untuk memudahkan pelacakan inventori internal toko.

### Bagaimana cara membuat kode SKU untuk produk segar yang dijual berdasarkan timbangan kiloan?
Gunakan awalan kode timbangan khusus (misal: CUR-TEL-01K untuk Telur Curah 1 Kg) dan cetak label barcode timbangan digital yang memuat data berat dan harga terkini secara otomatis.

```json
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "Article",
      "headline": "Cara Membuat Kode SKU Barang Toko Ritel agar Kasir Tidak Keliru",
      "description": "Pelajari cara membuat kode SKU barang toko ritel yang terstandar, struktur penomoran kategori merek dan varian, serta integrasi dengan barcode kasir.",
      "image": "https://cooca.id/images/blog/cara-membuat-kode-sku-barang-toko-ritel-terstandar.jpg",
      "datePublished": "2026-11-19T08:00:00+07:00",
      "dateModified": "2026-11-19T08:00:00+07:00",
      "url": "https://cooca.id/blog/cara-membuat-kode-sku-barang-toko-ritel-terstandar",
      "mainEntityOfPage": "https://cooca.id/blog/cara-membuat-kode-sku-barang-toko-ritel-terstandar",
      "author": {
        "@type": "Person",
        "name": "Tim Edukasi COOCA"
      },
      "publisher": {
        "@type": "Organization",
        "name": "COOCA",
        "logo": {
          "@type": "ImageObject",
          "url": "https://cooca.id/images/logo.png"
        }
      }
    },
    {
      "@type": "FAQPage",
      "mainEntity": [
        {
          "@type": "Question",
          "name": "Berapa jumlah digit karakter yang paling ideal untuk kode SKU toko kecil?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Panjang yang paling ideal berkisar antara 8 hingga 12 karakter. Kode yang terlalu pendek rawan mengalami duplikasi, sedangkan kode yang lebih dari 15 karakter sulit diingat dan memakan tempat saat dicetak di label kecil."
          }
        },
        {
          "@type": "Question",
          "name": "Apakah kode SKU boleh menyertakan informasi harga jual barang?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Sangat tidak disarankan. Harga jual produk bisa berubah sewaktu-waktu akibat inflasi atau promosi diskon. Jika harga dicantumkan di kode SKU, Anda terpaksa mengganti kode SKU dan mencetak ulang seluruh label setiap kali harga berubah."
          }
        },
        {
          "@type": "Question",
          "name": "Apa perbedaan antara kode SKU dan nomor barcode UPC atau EAN?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Barcode UPC atau EAN adalah kode standar internasional yang diterbitkan lembaga resmi (GS1) untuk produk pabrikan global. Sedangkan SKU adalah kode internal toko yang bebas dirancang sendiri oleh pemilik bisnis untuk memudahkan pelacakan inventori internal toko."
          }
        },
        {
          "@type": "Question",
          "name": "Bagaimana cara membuat kode SKU untuk produk segar yang dijual berdasarkan timbangan kiloan?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Gunakan awalan kode timbangan khusus (misal: CUR-TEL-01K untuk Telur Curah 1 Kg) dan cetak label barcode timbangan digital yang memuat data berat dan harga terkini secara otomatis."
          }
        }
      ]
    }
  ]
}
```
