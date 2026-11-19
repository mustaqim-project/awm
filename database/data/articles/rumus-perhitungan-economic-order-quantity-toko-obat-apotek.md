---
title: "Sering Kehabisan Obat atau Kadaluwarsa di Rak? Gunakan Perhitungan Economic Order Quantity Toko Obat"
meta_title: "Perhitungan Economic Order Quantity Toko Obat Apotek"
meta_description: "Langkah mudah perhitungan economic order quantity toko obat dan apotek, cara menyeimbangkan biaya pemesanan dan penyimpanan, serta optimasi modal kerja."
slug: "rumus-perhitungan-economic-order-quantity-toko-obat-apotek"
primary_keyword: "perhitungan economic order quantity toko obat"
secondary_keywords: "rumus eoq apotek rakyat, biaya pemesanan obat ke pbf, biaya simpan gudang farmasi, jumlah pesanan ekonomis apotek, manajemen inventori obat kadaluwarsa"
search_intent: "Informational"
category: "Inventori & Stok"
reading_time: "10 menit"
cover_image: "https://cooca.id/images/blog/rumus-perhitungan-economic-order-quantity-toko-obat-apotek.jpg"
published_at: "2026-08-27T16:40:00+07:00"
author: Tim Edukasi COOCA
---

# Sering Kehabisan Obat atau Kadaluwarsa di Rak? Gunakan Perhitungan Economic Order Quantity Toko Obat

Mengelola persediaan pada toko obat mandiri dan apotek rakyat adalah salah satu seni manajemen logistik paling rumit di dunia usaha kecil. Tidak seperti toko kelontong atau pakaian di mana kelebihan stok hanya menimbulkan risiko barang lambat laku, di bisnis farmasi kesalahan takaran persediaan memiliki konsekuensi legal dan finansial yang sangat fatal. Jika Anda memesan obat terlalu sedikit, rak etalase apotek akan sering kosong (stockout) sehingga pasien yang membutuhkan obat mendesak lari ke apotek berjaringan. Sebaliknya, jika Anda tergiur diskon kuantitas dari distributor Pedagang Besar Farmasi (PBF) dan membeli ratusan boks obat sekaligus, obat-obatan tersebut akan menumpuk di rak hingga melewati tanggal kedaluwarsa (expired date). Berdasarkan undang-undang kesehatan, obat kedaluwarsa dilarang keras diperjualbelikan, wajib dimusnahkan secara resmi, dan menjadi kerugian modal permanen bagi apotek. Solusi ilmiah untuk mengakhiri dilema ini adalah menerapkan prosedur operasional terstandarisasi secara presisi.

Economic Order Quantity (EOQ) atau Jumlah Pemesanan Ekonomis adalah formula matematis klasik yang bertugas menemukan titik keseimbangan optimal antara Biaya Pemesanan Barang (Ordering Cost) dengan Biaya Penyimpanan Barang (Holding/Carrying Cost). Dengan rumus ini, Anda akan mengetahui secara pasti berapa boks obat yang wajib Anda beli setiap kali menerbitkan Surat Pesanan (SP) agar total biaya persediaan apotek Anda berada di titik paling minimal.

## Dua Kutub Biaya yang Saling Bertentangan dalam Inventori Farmasi

Mengapa penentuan jumlah order obat tidak boleh ditebak-tebak menggunakan perasaan? Karena ada dua kekuatan biaya yang saling berlawanan:

### 1. Biaya Pemesanan Barang (Ordering Cost / S)
Setiap kali Anda memesan obat ke PBF, apotek mengeluarkan biaya administrasi dan logistik:
- Biaya telepon dan pulsa komunikasi asisten apoteker ke sales PBF.
- Waktu kerja tenaga apoteker untuk menerbitkan Surat Pesanan (SP) resmi dan memeriksa faktur.
- Ongkos kirim ekspedisi atau ongkos kirim minimum order dari distributor PBF.
- Biaya administrasi kliring perbankan untuk transfer pembayaran faktur.
- Jika Anda memesan dalam jumlah sedikit-sedikit namun sangat sering (misal seminggu 4 kali pesan), total biaya pemesanan tahunan apotek Anda akan melonjak tinggi.

### 2. Biaya Penyimpanan Barang (Holding Cost / H)
Setiap hari obat mengendap di rak dan gudang apotek, timbul biaya pemeliharaan modal:
- Biaya modal kerja yang terkunci (uang terparkir di rak yang seharusnya bisa didepositokan atau diputar).
- Biaya listrik pendingin ruangan AC 24 jam untuk menjaga suhu ruang obat di bawah 25°C.
- Biaya sewa etalase kaca dan ruangan apotek.
- Risiko kerusakan fisik kemasan, kelembapan, dan yang paling menakutkan: risiko kedaluwarsa obat (expired risk).
- Jika Anda memesan obat dalam jumlah sangat besar sekaligus agar jarang memesan, biaya penyimpanan dan risiko obat kadaluwarsa apotek Anda yang akan membengkak drastis.

Pelajari pula [cara membuat kartu stok barang manual dan digital](https://cooca.id/blog/cara-membuat-kartu-stok-barang-manual-dan-digital) untuk memastikan kartu stok farmasi fisik dan digital selalu sinkron.

## Rumus Baku Perhitungan Economic Order Quantity Toko Obat

Formula universal EOQ yang telah teruji selama puluhan tahun adalah:
`EOQ = Akar Kuadrat dari [ (2 x D x S) / H ]`

Keterangan Variabel:
- D (Annual Demand): Total kebutuhan atau perkiraan penjualan obat tersebut dalam kurun waktu satu tahun (unit/boks).
- S (Ordering Cost per Order): Biaya yang timbul setiap kali apotek melakukan satu kali pemesanan ke distributor PBF (Rupiah per pesanan).
- H (Holding Cost per Unit per Year): Biaya penyimpanan satu unit obat di gudang selama satu tahun penuh (Rupiah per unit per tahun). Biasanya diestimasikan sebesar 10% hingga 20% dari harga beli obat.

## Simulasi Penghitungan EOQ pada Apotek Sehat Farma

Mari kita simulasikan pada Apotek Sehat Farma untuk produk obat Parasetamol Sirup Anak 60ml dengan parameter operasional berikut:
- Permintaan Pasien Tahunan (D): 2.400 botol per tahun (rata-rata 200 botol per bulan).
- Biaya Pemesanan ke PBF (S): Rp50.000 setiap kali terbit Surat Pesanan (ongkos administrasi & verifikasi).
- Harga Beli Obat dari PBF: Rp15.000 per botol.
- Biaya Simpan Tahunan (H): Diestimasikan 15% dari harga obat = `15% x Rp15.000 = Rp2.250 per botol per tahun`.

Mari kita masukkan angka-angka tersebut ke dalam rumus EOQ:
1. Pembilang: `2 x D x S = 2 x 2.400 x Rp50.000 = 240.000.000`.
2. Penyebut: `H = 2.250`.
3. Pembagian: `240.000.000 / 2.250 = 106.666,67`.
4. Akar Kuadrat: `Akar dari 106.666,67 = 326,59 botol (dibulatkan menjadi 327 botol)`.

Hasil perhitungan di atas memberikan jawaban yang sangat presisi:
Jumlah pemesanan paling ekonomis untuk Parasetamol Sirup adalah sekitar 327 botol per pesanan.

Berikut adalah tabel analisis komparasi jika apotek memesan dengan volume yang berbeda:

| Parameter Evaluasi Pesanan | Skenario A (Pesan Sedikit-Sedikit) | Skenario B (Formula EOQ Optimal) | Skenario C (Pesan Borongan Besar) |
| :--- | :--- | :--- | :--- |
| Jumlah Botol Sekali Pesan (Q) | 100 Botol (Terlalu Sedikit) | **327 Botol (Titik Ekonomis)** | 1.200 Botol (Terlalu Banyak) |
| Frekuensi Pemesanan Per Tahun | 24 Kali Order (2x Sebulan) | **7,3 Kali Order (Tiap 1,6 Bln)**| 2 Kali Order Saja (Tiap 6 Bln) |
| Total Biaya Pemesanan Tahunan | 24 x Rp50.000 = Rp1.200.000 | **7,3 x Rp50.000 = Rp365.000** | 2 x Rp50.000 = Rp100.000 |
| Rata-rata Stok di Apotek (Q/2) | 50 Botol | **163,5 Botol** | 600 Botol |
| Total Biaya Simpan Tahunan | 50 x Rp2.250 = Rp112.500 | **163,5 x Rp2.250 = Rp367.875** | 600 x Rp2.250 = Rp1.350.000 |
| **Total Biaya Persediaan Tahunan**| **Rp1.312.500** | **Rp732.875 (BIAYA TERMURAH)** | **Rp1.450.000** |
| Risiko Kadaluwarsa Obat di Rak | Sangat Rendah | **Nol (Aman Terjual 1,6 Bln)** | **Sangat Tinggi (Obat Numpuk)** |

Perhatikan analisis tabel di atas:
Skenario B (Formula EOQ) menghasilkan Total Biaya Persediaan terendah, yaitu hanya Rp732.875 per tahun (menghemat biaya hampir 50% dibandingkan skenario A dan skenario C). Yang paling krusial, memesan 327 botol menjamin bahwa stok akan habis terjual dalam waktu 1,6 bulan, sehingga risiko obat kedaluwarsa di rak apotek menjadi nol mutlak. Sebaliknya, Skenario C yang menimbun 1.200 botol sekaligus membuat modal apotek mati sebesar Rp18.000.000 dan obat terancam kedaluwarsa sebelum sempat terjual. Pelajari juga [penerapan metode fefo toko makanan dan kosmetik umkm](https://cooca.id/blog/penerapan-metode-fefo-toko-makanan-dan-kosmetik-umkm) agar penataan stok obat di etalase selalu mendahulukan obat dengan masa expired terdekat.

## Menyesuaikan Formula EOQ dengan Karakteristik Obat Khusus

Dalam praktik nyata di apotek, perhitungan EOQ harus disesuaikan dengan regulasi dan sifat fisik obat:

### 1. Obat Golongan Narkotika dan Psikotropika
Tidak boleh menerapkan EOQ dalam jumlah besar. Pengadaan obat golongan ini diawasi ketat oleh BPOM dan Dinas Kesehatan, serta dibatasi oleh kuota Surat Pesanan khusus ber-SIPZA, sehingga pengadaannya harus disesuaikan persis dengan rata-rata lembar resep dokter bulanan.

### 2. Obat Suhu Dingin (Cold Chain / Termolabil)
Vaksin, insulin, dan suppositoria yang wajib disimpan di kulkas khusus farmasi (suhu 2°C - 8°C) memiliki keterbatasan volume ruang chiller. Nilai EOQ tidak boleh melebihi kapasitas volume chiller yang tersedia di apotek Anda.

### 3. Fasilitas Diskon Kuantitas Distributor (Quantity Discount)
Jika PBF menawarkan potongan harga 5% jika membeli 500 botol, bandingkan apakah penghematan diskon pembelian tersebut lebih besar daripada kenaikan biaya simpan dan risiko kedaluwarsa obat. Jika selisihnya menguntungkan dan obat tersebut terbukti fast-moving, menaikkan volume di atas angka EOQ dapat dibenarkan.

## Otomasi Pengadaan Farmasi bersama COOCA

Menghitung rumus EOQ satu per satu untuk ribuan jenis obat paten dan generik secara manual sangat tidak efisien. COOCA menghadirkan Sistem Manajemen Farmasi Terpadu:

1. Auto-Calculated EOQ & ROP: Sistem COOCA menganalisis riwayat penjualan obat harian dan secara otomatis menyajikan rekomendasi jumlah pemesanan ekonomis untuk setiap SKU obat.
2. Surat Pesanan (SP) Generator: Menghasilkan lembar Surat Pesanan resmi ke PBF lengkap dengan nomor registrasi apoteker (SIPA) dan nama distributor hanya dengan satu klik.
3. Expiry Date Color Coding: Mengelompokkan obat di layar kasir dengan kode warna: Hijau (> 1 tahun), Kuning (3 - 6 bulan), dan Merah (< 3 bulan) yang mengunci transaksi kasir secara otomatis untuk mencegah penjualan obat kedaluwarsa ke pasien.

## Kesimpulan

Keberhasilan pengelolaan toko obat dan apotek tidak terletak pada seberapa banyak obat yang Anda pajang di lemari kaca, melainkan pada seberapa presisi Anda mengatur perputaran modal kerja. Dengan menguasai mekanisme pengendalian ini, apotek Anda akan selalu terbebas dari kepanikan kehabisan obat penting bagi pasien, terlindungi dari kerugian obat kedaluwarsa yang dimusnahkan, dan memiliki arus kas operasional yang sehat dan terpercaya.

Pelajari pula panduan mendalam mengenai [manajemen multi gudang toko retail sinkronisasi stok](https://cooca.id/blog/manajemen-multi-gudang-toko-retail-sinkronisasi-stok) untuk memperkuat strategi operasional bisnis Anda.

## FAQ Seputar Pengadaan Obat Apotek

### Berapa batas waktu minimal kedaluwarsa obat yang boleh diterima dari PBF?
Sesuai standar operasional apotek yang baik, tolak kiriman obat dari distributor PBF jika tanggal kedaluwarsanya kurang dari 12 hingga 18 bulan, kecuali untuk obat-obat life-saving langka tertentu yang perputarannya sangat cepat dan telah disetujui oleh Apoteker Penanggung Jawab (APJ).

### Apa yang harus dilakukan jika obat di rak sudah memasuki masa kedaluwarsa 3 bulan?
Segera pisahkan dari etalase display dan pindahkan ke Lemari Karantina Obat Kadaluwarsa berlabel merah. Hubungi distributor PBF untuk mengajukan proses retur atau pemusnahan obat sesuai berita acara resmi yang disaksikan oleh Balai POM setempat.

### Apakah rumus EOQ bisa diterapkan untuk apotek baru yang belum memiliki riwayat penjualan?
Bisa, dengan menggunakan data estimasi permintaan (demand forecast) berdasarkan studi kelayakan lingkungan sekitar apotek (jumlah klinik dokter terdekat, kepadatan penduduk perumahan, dan jenis penyakit musiman di wilayah tersebut).

### Apa perbedaan antara Lead Time pengadaan obat generik vs obat paten impor?
Obat generik lokal umumnya memiliki lead time pengiriman sangat cepat (1 hingga 2 hari kerja dari PBF lokal). Sedangkan obat paten impor atau obat kanker khusus bisa memiliki lead time berminggu-minggu, sehingga membutuhkan persediaan pengaman (safety stock) yang lebih tebal di apotek.

```json
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "Article",
      "headline": "Sering Kehabisan Obat atau Kadaluwarsa di Rak? Gunakan Perhitungan Economic Order Quantity Toko Obat",
      "description": "Langkah mudah perhitungan economic order quantity toko obat dan apotek, cara menyeimbangkan biaya pemesanan dan penyimpanan, serta optimasi modal kerja.",
      "image": "https://cooca.id/images/blog/rumus-perhitungan-economic-order-quantity-toko-obat-apotek.jpg",
      "datePublished": "2026-08-27T16:40:00+07:00",
      "dateModified": "2026-08-27T16:40:00+07:00",
      "url": "https://cooca.id/blog/rumus-perhitungan-economic-order-quantity-toko-obat-apotek",
      "mainEntityOfPage": "https://cooca.id/blog/rumus-perhitungan-economic-order-quantity-toko-obat-apotek",
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
          "name": "Berapa batas waktu minimal kedaluwarsa obat yang boleh diterima dari PBF?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Sesuai standar operasional apotek yang baik, tolak kiriman obat dari distributor PBF jika tanggal kedaluwarsanya kurang dari 12 hingga 18 bulan, kecuali untuk obat-obat life-saving langka tertentu yang perputarannya sangat cepat dan telah disetujui oleh Apoteker Penanggung Jawab (APJ)."
          }
        },
        {
          "@type": "Question",
          "name": "Apa yang harus dilakukan jika obat di rak sudah memasuki masa kedaluwarsa 3 bulan?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Segera pisahkan dari etalase display dan pindahkan ke Lemari Karantina Obat Kadaluwarsa berlabel merah. Hubungi distributor PBF untuk mengajukan proses retur atau pemusnahan obat sesuai berita acara resmi yang disaksikan oleh Balai POM setempat."
          }
        },
        {
          "@type": "Question",
          "name": "Apakah rumus EOQ bisa diterapkan untuk apotek baru yang belum memiliki riwayat penjualan?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Bisa, dengan menggunakan data estimasi permintaan (demand forecast) berdasarkan studi kelayakan lingkungan sekitar apotek (jumlah klinik dokter terdekat, kepadatan penduduk perumahan, dan jenis penyakit musiman di wilayah tersebut)."
          }
        },
        {
          "@type": "Question",
          "name": "Apa perbedaan antara Lead Time pengadaan obat generik vs obat paten impor?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Obat generik lokal umumnya memiliki lead time pengiriman sangat cepat (1 hingga 2 hari kerja dari PBF lokal). Sedangkan obat paten impor atau obat kanker khusus bisa memiliki lead time berminggu-minggu, sehingga membutuhkan persediaan pengaman (safety stock) yang lebih tebal di apotek."
          }
        }
      ]
    }
  ]
}
```
