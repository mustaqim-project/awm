---
title: "Rumus Economic Order Quantity EOQ untuk Efisiensi Kulakan Toko"
meta_title: "Rumus Economic Order Quantity EOQ Kulakan Toko"
meta_description: "Pahami rumus economic order quantity EOQ untuk menentukan jumlah order paling efisien, menghemat biaya simpan dan biaya pesan barang."
slug: "rumus-economic-order-quantity-eoq-efisiensi-kulakan-toko"
primary_keyword: "rumus economic order quantity"
secondary_keywords: "holding cost biaya simpan, ordering cost biaya pemesanan, frekuensi pembelian barang, optimasi persediaan toko, manajemen gudang ritel"
search_intent: "Informational"
category: "Inventori & Stok"
reading_time: "10 menit"
cover_image: "https://cooca.id/images/blog/rumus-economic-order-quantity-eoq-efisiensi-kulakan-toko.jpg"
published_at: "2026-07-30T09:15:00+07:00"
---
# Rumus Economic Order Quantity EOQ untuk Efisiensi Kulakan Toko

Banyak pemilik toko kelontong, minimarket mandiri, dan pelaku usaha grosir menghadapi dilema klasik saat kulakan barang dagangan ke distributor. Jika memesan barang dalam jumlah yang terlalu sedikit, mereka harus berulang kali membayar ongkos kirim ekspedisi dan biaya administrasi pemesanan, belum lagi risiko kehilangan pelanggan saat barang cepat habis di rak. Sebaliknya, jika membeli barang dalam jumlah yang terlalu masif demi mengejar diskon kuantiti, modal kas operasional menjadi mati tertimbun di gudang, risiko barang rusak atau kedaluwarsa meningkat pesat, serta tagihan listrik pendingin dan biaya sewa ruang penyimpanan melonjak tinggi. Menguasai rumus economic order quantity adalah solusi matematis yang terbukti efektif untuk menemukan titik keseimbangan paling ekonomis antara biaya pemesanan dan biaya penyimpanan stok barang.

Model Economic Order Quantity (EOQ) pertama kali diperkenalkan oleh Ford Whitman Harris dan telah menjadi pilar utama dalam manajemen rantai pasok modern. Bagi pemilik UMKM Indonesia, konsep ini tidak harus rumit seperti rumus kalkulus di bangku kuliah. Pendekatan ini dapat diadaptasikan ke dalam tabel hitung sederhana untuk memandu keputusan pembelian bulanan secara cerdas dan terukur.

## Komponen Biaya dalam Manajemen Persediaan Toko

Sebelum menghitung angka pesanan paling efisien, pemilik toko harus mengidentifikasi dua kelompok biaya persediaan yang saling berlawanan:

### 1. Biaya Pemesanan (Ordering Cost atau Setup Cost)
Biaya pemesanan adalah seluruh pengeluaran yang muncul setiap kali Anda menerbitkan Purchase Order (PO) atau memesan barang ke distributor, tanpa memandang berapa banyak unit barang yang Anda beli. Komponen biaya pemesanan meliputi:
- Ongkos kirim truk ekspedisi atau kargo dari gudang supplier ke toko Anda.
- Pulsa telepon dan kuota internet untuk koordinasi bagian pengadaan dengan sales distributor.
- Biaya administrasi transfer antarbank atau biaya materai kontrak pemesanan.
- Biaya tenaga kerja staf toko saat melakukan bongkar muat dan pengecekan fisik barang datang.

Semakin sering Anda memesan dalam setahun (frekuensi tinggi), total biaya pemesanan tahunan Anda akan semakin membengkak.

### 2. Biaya Penyimpanan (Carrying Cost atau Holding Cost)
Biaya penyimpanan adalah biaya yang dikeluarkan untuk menyimpan dan merawat satu unit barang di dalam gudang selama periode waktu tertentu (biasanya satu tahun). Komponen biaya penyimpanan mencakup:
- Biaya sewa ruang gudang per meter persegi atau penyusutan bangunan toko.
- Tagihan listrik untuk lampu penerangan, pendingin ruangan (AC), atau freezer penyimpanan.
- Risiko kerusakan fisik barang, kemasan penyok, debu, atau tanggal kedaluwarsa terlampaui.
- Asuransi kebakaran gudang dan biaya keamanan petugas ronda malam.
- Biaya modal (cost of capital), yaitu bunga atau potensi keuntungan investasi yang hilang karena uang tunai Anda berubah menjadi tumpukan kardus barang di rak.

Semakin banyak unit barang yang Anda timbun di gudang, total biaya penyimpanan tahunan Anda akan semakin tinggi.

Keseimbangan antara kedua pos pengeluaran ini sangat menentukan apakah arus kas toko Anda sehat atau terjepit, sebagaimana dijelaskan dalam panduan [cara menghitung reorder point rop kulakan barang otomatis](https://cooca.id/blog/cara-menghitung-reorder-point-rop-kulakan-barang-otomatis).

## Rumus Economic Order Quantity Klasik dan Variabelnya

Secara matematis, titik terendah dari total biaya persediaan terjadi ketika total biaya pemesanan tahunan sama persis dengan total biaya penyimpanan tahunan. Rumus economic order quantity standar dinyatakan sebagai berikut:

EOQ = Akar Kuadrat dari [ (2 x D x S) / H ]

Keterangan variabel:
- D (Demand): Total kebutuhan atau volume penjualan barang dalam satu tahun (satuan unit).
- S (Setup/Ordering Cost): Rata-rata biaya yang dikeluarkan setiap kali melakukan satu kali pemesanan (satuan rupiah per order).
- H (Holding/Carrying Cost): Biaya penyimpanan per unit barang selama satu tahun (satuan rupiah per unit per tahun). Biaya ini sering kali dihitung sebagai persentase tertentu (misalnya 15% hingga 25%) dari harga beli barang per unit.

Dengan memasukkan data historis penjualan toko Anda ke dalam rumus ini, Anda tidak perlu lagi menebak-nebak apakah harus kulakan setiap minggu atau sebulan sekali.

## Simulasi Kasus Perhitungan EOQ pada Toko Ritel Sembako

Untuk memberikan gambaran nyata, mari kita ambil contoh Toko Berkah Mandiri yang menjual Beras Premium kemasan 5 kg. Pemilik toko ingin mengetahui berapa sak beras yang harus dipesan setiap kali kulakan agar total pengeluarannya paling hemat.

Data operasional toko:
- Permintaan tahunan (D) = 2.400 sak (rata-rata penjualan 200 sak per bulan).
- Biaya pemesanan per transaksi (S) = Rp150.000 (mencakup ongkos sewa mobil pick-up dan biaya bongkar muat kuli).
- Harga beli beras dari penggilingan = Rp70.000 per sak.
- Biaya simpan tahunan per sak (H) = 20% dari harga beli = 20% x Rp70.000 = Rp14.000 per sak per tahun (mencakup biaya sewa gudang, obat anti kutu beras, dan susut bobot timbangan).

Langkah perhitungan:
1. Kalikan 2 x D x S: 2 x 2.400 x Rp150.000 = Rp720.000.000.
2. Bagi dengan H: Rp720.000.000 / Rp14.000 = 51.428,57.
3. Tarik akar kuadrat dari 51.428,57: Hasilnya adalah 226,77 sak, atau dibulatkan menjadi 227 sak per pemesanan.

Tabel komparasi berbagai skenario jumlah pesanan Beras Premium 5 kg:

| Parameter Evaluasi | Skenario A (Order Kecil: 100 Sak) | Skenario B (EOQ: 227 Sak) | Skenario C (Order Besar: 500 Sak) |
| :--- | :--- | :--- | :--- |
| Frekuensi Order per Tahun | 24 kali (setiap 2 minggu) | 10,6 kali (setiap 34 hari) | 4,8 kali (setiap 2,5 bulan) |
| Rata-rata Stok di Gudang (Q/2) | 50 sak | 113,5 sak | 250 sak |
| Total Biaya Pemesanan Tahunan | Rp3.600.000 | Rp1.586.000 | Rp720.000 |
| Total Biaya Penyimpanan Tahunan | Rp700.000 | Rp1.589.000 | Rp3.500.000 |
| Total Biaya Persediaan Tahunan | Rp4.300.000 | Rp3.175.000 | Rp4.220.000 |

Dari tabel komparasi di atas, terlihat jelas bahwa:
- Pada Skenario A, biaya simpan sangat murah (Rp700.000) namun biaya pesan membengkak (Rp3.600.000) karena sopir pick-up harus bolak-balik 24 kali dalam setahun.
- Pada Skenario C, biaya pesan sangat hemat (Rp720.000) namun biaya simpan meroket (Rp3.500.000) karena gudang penuh sesak dan beras berisiko berkutu.
- Pada Skenario B (EOQ 227 sak), total biaya persediaan mencapai titik paling efisien (Rp3.175.000 per tahun), menghemat lebih dari Rp1.125.000 per tahun hanya dari satu jenis SKU beras saja.

Keputusan jumlah pesanan ini juga harus disinkronkan dengan metode pencatatan arus keluar masuk fisik menggunakan [perbedaan metode fifo dan fefo manajemen stok toko](https://cooca.id/blog/perbedaan-metode-fifo-dan-fefo-manajemen-stok-toko) agar beras yang pertama kali datang adalah yang pertama kali dipajang di kasir.

## Kapan Model EOQ Perlu Disesuaikan di Lapangan?

Meskipun rumus EOQ sangat membantu, dalam praktik bisnis sehari-hari terdapat beberapa faktor situasional yang membuat angka EOQ murni harus disesuaikan:

### 1. Penawaran Diskon Kuantiti (Quantity Discount)
Sering kali distributor memberikan potongan harga khusus jika toko membeli dalam jumlah kelipatan tertentu, misalnya diskon 5% untuk pembelian minimal 300 sak. Dalam situasi ini, pemilik toko harus menghitung apakah total penghematan dari potongan harga beli lebih besar daripada tambahan biaya simpan yang muncul akibat menampung kelebihan stok.

### 2. Kapasitas Fisik Ruang Gudang
Jika hasil perhitungan EOQ merekomendasikan pembelian 500 karton minyak goreng, namun kapasitas lantai gudang Anda hanya mampu menampung 200 karton tanpa menutupi jalur evakuasi keselamatan kerja, maka batas kapasitas fisik gudang adalah batasan absolut yang tidak boleh dilanggar.

### 3. Waktu Kedaluwarsa Produk (Shelf Life)
Untuk produk makanan segar, susu pasteurisasi, atau roti basah, umur simpan barang sangat pendek. Jumlah pemesanan tidak boleh melebihi perkiraan konsumsi harian sebelum tanggal kedaluwarsa tiba, meskipun biaya pemesanannya menjadi sedikit lebih tinggi.

### 4. Minimum Order Quantity (MOQ) dari Pabrik
Banyak pabrik atau importir mensyaratkan batas minimum pembelian, misalnya minimal 1 palet (50 karton). Jika angka EOQ Anda hanya 35 karton, Anda terpaksa menaikkan order ke angka 50 karton sesuai aturan pemasok.

## Langkah Praktis Menerapkan EOQ untuk Toko Mandiri

Bagi pemilik bisnis yang ingin mulai menerapkan sistem pemesanan ekonomis, berikut adalah tahapan praktis yang dapat dijalankan mulai minggu ini:

1. Kelompokkan Produk Berdasarkan Kategori Prioritas: Mulailah menerapkan EOQ pada 20% produk terlaris yang menghasilkan 80% perputaran uang toko Anda.
2. Hitung Rata-rata Biaya Pemesanan: Kumpulkan nota ongkos kirim dan bukti biaya tenaga kerja bongkar barang selama tiga bulan terakhir untuk mendapatkan rata-rata biaya per pemesanan yang akurat.
3. Estimasi Biaya Simpan Realistis: Sebagai patokan umum UMKM ritel di Indonesia, tetapkan biaya simpan tahunan sebesar 15% hingga 20% dari harga perolehan barang.
4. Buat Lembar Kerja Perhitungan: Masukkan rumus akar kuadrat EOQ ke dalam lembar kerja Google Sheets atau Excel agar staf pengadaan dapat langsung melihat jumlah order optimal saat memasukkan target penjualan.
5. Lakukan Review Berkala: Lakukan peninjauan ulang angka EOQ setiap enam bulan sekali, terutama saat tarif ongkos kirim ekspedisi mengalami kenaikan atau harga beli bahan baku berubah.

Kedisiplinan dalam menerapkan kuantiti order yang terukur ini akan sangat mendukung kelancaran [sop stock opname siklus bergilir toko ritel](https://cooca.id/blog/sop-stock-opname-siklus-bergilir-toko-ritel) karena jumlah barang di gudang selalu berada pada volume yang terkendali rapi.

## Otomatisasi Perhitungan Restock Bersama Cooca

Menghitung rumus EOQ satu per satu secara manual untuk ratusan atau ribuan SKU barang dagangan tentu menyita banyak waktu berharga pemilik usaha. Cooca Business Operating System hadir dengan modul manajemen inventori cerdas yang otomatis menganalisis laju penjualan harian dari mesin kasir POS. Sistem Cooca memproyeksikan estimasi kebutuhan stok mendatang, menghitung Reorder Point secara dinamis, dan menerbitkan rekomendasi Purchase Order yang paling efisien ke pemasok dengan satu kali klik. Dengan Cooca, toko ritel Anda terbebas dari jebakan overstock modal mati sekaligus terhindar dari risiko kehilangan omzet akibat rak barang kosong.

## Kesimpulan

Menerapkan rumus economic order quantity bukan hanya tentang angka dan rumus matematika, melainkan tentang membangun disiplin manajemen persediaan yang menjaga kelangsungan arus kas toko Anda. Dengan menyeimbangkan biaya pemesanan dan biaya penyimpanan, setiap rupiah modal kerja yang Anda miliki dapat berputar menghasilkan keuntungan maksimal tanpa ada dana yang terbuang sia-sia di lorong gudang.

## FAQ

### Apa perbedaan mendasar antara EOQ dan Reorder Point (ROP)?
EOQ menjawab pertanyaan berapa banyak jumlah unit barang yang harus dipesan setiap kali kulakan, sedangkan ROP menjawab pertanyaan kapan atau pada level sisa stok berapa pemesanan tersebut harus segera dikirimkan ke supplier.

### Apakah rumus EOQ cocok diterapkan untuk produk kuliner yang cepat basi?
Rumus EOQ klasik kurang tepat jika diterapkan langsung pada bahan mentah yang cepat rusak tanpa modifikasi batasan shelf life. Untuk bahan kuliner segar, batasan masa kedaluwarsa dan kapasitas pendingin kulkas menjadi faktor penentu utama dibanding biaya simpan tahunan.

### Bagaimana cara menentukan biaya penyimpanan jika toko berada di ruko milik sendiri?
Meskipun ruko milik sendiri, biaya penyimpanan tetap harus dihitung berdasarkan nilai sewa pasar wajar (opportunity cost) ruko tersebut per tahun serta biaya penyusutan bangunan, pemeliharaan fisik, dan tagihan listrik penerangan gudang.

### Apa yang harus dilakukan jika hasil EOQ menghasilkan angka pecahan?
Selalu bulatkan angka EOQ ke satuan kemasan terkecil yang dijual oleh distributor, misalnya dibulatkan ke karton, pak, atau lusin terdekat untuk mempermudah pemrosesan faktur pembelian.

### Apakah kenaikan biaya bahan bakar bensin memengaruhi angka pesanan EOQ?
Sangat memengaruhi. Kenaikan harga bensin akan menaikkan biaya pemesanan (ordering cost) per pengiriman. Akibatnya, nilai matematis EOQ akan membesar, yang berarti toko dianjurkan memesan dalam jumlah lebih banyak dengan frekuensi pengiriman yang lebih jarang agar ongkos transportasi tetap efisien.

```json
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "Article",
      "headline": "Rumus Economic Order Quantity EOQ untuk Efisiensi Kulakan Toko",
      "description": "Pahami rumus economic order quantity EOQ untuk menentukan jumlah order paling efisien, menghemat biaya simpan dan biaya pesan barang.",
      "image": "https://cooca.id/images/blog/rumus-economic-order-quantity-eoq-efisiensi-kulakan-toko.jpg",
      "datePublished": "2026-07-30T09:15:00+07:00",
      "dateModified": "2026-07-30T09:15:00+07:00",
      "url": "https://cooca.id/blog/rumus-economic-order-quantity-eoq-efisiensi-kulakan-toko",
      "mainEntityOfPage": "https://cooca.id/blog/rumus-economic-order-quantity-eoq-efisiensi-kulakan-toko",
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
          "name": "Apa perbedaan mendasar antara EOQ dan Reorder Point (ROP)?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "EOQ menjawab pertanyaan berapa banyak jumlah unit barang yang harus dipesan setiap kali kulakan, sedangkan ROP menjawab pertanyaan kapan atau pada level sisa stok berapa pemesanan tersebut harus segera dikirimkan ke supplier."
          }
        },
        {
          "@type": "Question",
          "name": "Apakah rumus EOQ cocok diterapkan untuk produk kuliner yang cepat basi?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Rumus EOQ klasik kurang tepat jika diterapkan langsung pada bahan mentah yang cepat rusak tanpa modifikasi batasan shelf life. Untuk bahan kuliner segar, batasan masa kedaluwarsa dan kapasitas pendingin kulkas menjadi faktor penentu utama dibanding biaya simpan tahunan."
          }
        },
        {
          "@type": "Question",
          "name": "Bagaimana cara menentukan biaya penyimpanan jika toko berada di ruko milik sendiri?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Meskipun ruko milik sendiri, biaya penyimpanan tetap harus dihitung berdasarkan nilai sewa pasar wajar (opportunity cost) ruko tersebut per tahun serta biaya penyusutan bangunan, pemeliharaan fisik, dan tagihan listrik penerangan gudang."
          }
        },
        {
          "@type": "Question",
          "name": "Apa yang harus dilakukan jika hasil EOQ menghasilkan angka pecahan?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Selalu bulatkan angka EOQ ke satuan kemasan terkecil yang dijual oleh distributor, misalnya dibulatkan ke karton, pak, atau lusin terdekat untuk mempermudah pemrosesan faktur pembelian."
          }
        },
        {
          "@type": "Question",
          "name": "Apakah kenaikan biaya bahan bakar bensin memengaruhi angka pesanan EOQ?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Sangat memengaruhi. Kenaikan harga bensin akan menaikkan biaya pemesanan (ordering cost) per pengiriman. Akibatnya, nilai matematis EOQ akan membesar, yang berarti toko dianjurkan memesan dalam jumlah lebih banyak dengan frekuensi pengiriman yang lebih jarang agar ongkos transportasi tetap efisien."
          }
        }
      ]
    }
  ]
}
```
