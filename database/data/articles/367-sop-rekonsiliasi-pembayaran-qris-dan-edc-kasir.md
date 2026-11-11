---
title: "SOP Rekonsiliasi Pembayaran QRIS dan EDC Kasir Toko Ritel Bebas Selisih"
meta_title: "SOP Rekonsiliasi Pembayaran QRIS dan EDC Kasir"
meta_description: "Pelajari SOP rekonsiliasi pembayaran QRIS dan EDC kasir toko ritel, alur settlement harian mesin EDC, pencocokan mutasi bank, dan audit kas register."
slug: "sop-rekonsiliasi-pembayaran-qris-dan-edc-kasir"
primary_keyword: "sop rekonsiliasi pembayaran qris dan edc kasir"
secondary_keywords: "pencocokan settlement harian qris bank, cara atasi struk edc keluar tapi saldo belum masuk, pencegahan fraud qr statis kasir toko, format lembar tutup kasir closing pos, waktu pencairan dana merchant mdr"
search_intent: "informational"
category: "Kasir & POS"
reading_time: "9 menit"
cover_image: "https://cooca.id/images/blog/photo-1556740738-b6a63e27c4df.jpg"
published_at: "2026-11-11T08:00:00+07:00"
---
# SOP Rekonsiliasi Pembayaran QRIS dan EDC Kasir Toko Ritel Bebas Selisih

Pergeseran perilaku transaksi masyarakat Indonesia menuju pembayaran nirsentuh (cashless) telah mengubah meja kasir toko ritel, restoran, dan gerai jasa secara masif. Berdasarkan catatan Bank Indonesia, lebih dari 70 persen transaksi di gerai perkotaan kini diselesaikan menggunakan kode QRIS dinamis, kartu debit, dan kartu kredit melalui mesin Electronic Data Capture (EDC). Menguasai sop rekonsiliasi pembayaran qris dan edc kasir adalah prosedur wajib setiap pergantian shift dan penutupan toko agar tidak ada sepeser pun uang hasil penjualan yang hilang atau tersangkut di jaringan perbankan.

Tingginya variasi metode pembayaran nontunai menghadirkan tantangan rekonsiliasi yang rumit bagi supervisor toko. Sering kali terjadi situasi di mana struk mesin EDC berhasil dicetak, namun dana belum masuk ke mutasi rekening bank merchant. Atau sebaliknya, kasir keliru memilih tombol metode pembayaran di aplikasi kasir POS, sehingga laporan akhir kasir mencatat selisih antara saldo fisik di laci dengan rekap sistem. Tanpa SOP pencocokan harian yang disiplin, akumulasi selisih transaksi nontunai dapat membengkak hingga jutaan rupiah dan sulit ditelusuri riwayatnya di kemudian hari.

Melalui artikel ini, kita akan membahas alur proses penyelesaian transaksi (settlement) pada mesin EDC, mekanisme audit mutasi QRIS dinamis, tata cara pengarsipan struk gesek bank, serta prosedur penanganan transaksi gantung, selaras dengan rujukan [sop rekonsiliasi kas harian toko kasir pos](https://cooca.id/blog/sop-rekonsiliasi-kas-harian-toko-kasir-pos).

## Mengapa Rekonsiliasi Transaksi Nontunai Harian Sangat Penting?

Banyak kasir beranggapan bahwa pembayaran nontunai selalu aman karena tidak melibatkan uang kertas fisik. Faktanya, transaksi digital memiliki titik kritis kebocoran operasional tersendiri:
1. Kasir Lupa Melakukan Settlement Mesin EDC di Akhir Shift. Mesin EDC dari bank persepsi membutuhkan proses settlement harian (tutup batch) agar data transaksi terkirim ke server perbankan. Jika kasir lupa menekan tombol settlement, dana penjualan pada hari tersebut tidak akan diproses pencairannya ke rekening usaha Anda.
2. Kesalahan Input Nominal di Mesin EDC Manual. Jika mesin EDC belum terintegrasi otomatis dengan software kasir, kasir bisa salah mengetik angka nominal tagihan (misalnya belanja Rp150.000 hanya diketik Rp15.000), memicu kerugian langsung bagi toko.
3. Potongan Biaya Merchant Discount Rate (MDR) Bank. Setiap transaksi QRIS dan kartu gesek dikenakan biaya layanan MDR resmi (berkisar 0,3% untuk QRIS hingga 1,5% untuk kartu kredit). Rekonsiliasi diperlukan untuk mencocokkan dana bersih yang diterima dengan potongan biaya transaksi bank.
4. Ancaman Penipuan Bukti Transfer dan QRIS Statis Palsu. Kasir yang terburu-buru saat jam sibuk rentan tertipu oleh tangkapan layar bukti transfer palsu di ponsel pembeli jika tidak memeriksa notifikasi pembayaran masuk di layar kasir.

Menerapkan prosedur verifikasi yang terstandarisasi melindungi kas toko dari seluruh risiko teknis dan operasional tersebut.

## 4 Tahapan Standar SOP Rekonsiliasi Pembayaran QRIS dan EDC Kasir

Jalankan empat langkah sistematis berikut setiap kali proses penutupan shift kasir (closing register) dilakukan:

### Tahap 1: Ekspor Laporan Rekap Penjualan dari Aplikasi Kasir POS
Sebelum menyentuh mesin perbankan, kasir wajib mengunci transaksi shift dan mencetak lembar Ringkasan Penjualan Shift (Shift Summary Report) dari software kasir POS. Lembar ini memuat rincian total transaksi berdasarkan metode pembayaran: total pembayaran uang tunai, total pembayaran QRIS, dan total pembayaran kartu debit/kredit per mesin EDC.

### Tahap 2: Eksekusi Proses Settlement pada Setiap Mesin EDC Bank
Ambil seluruh mesin EDC yang digunakan di gerai (misalnya mesin EDC Bank Mandiri, BCA, atau BRI):
- Tekan menu "Settlement" atau "Tutup Batch" pada layar mesin EDC.
- Masukkan kode sandi supervisor toko yang berwenang.
- Mesin EDC akan mencetak struk panjang (Settlement Receipt) yang memuat total transaksi sukses dan akumulasi nominal rupiah pada hari tersebut.
- Cocokkan angka total pada struk settlement EDC dengan angka metode pembayaran kartu pada laporan POS kasir.

### Tahap 3: Pencocokan Riwayat Mutasi QRIS Dinamis Real-Time
Untuk transaksi QRIS dinamis yang terhubung ke aplikasi kasir, cocokkan jumlah transaksi sukses di portal dashboard penyedia pembayaran (Payment Gateway) dengan laporan POS kasir. Pastikan seluruh transaksi berstatus "Paid" dan memiliki kode referensi unik (RRN / Retrieval Reference Number) yang sama, sesuai standar [sop penerimaan pembayaran qris dinamis kasir pos](https://cooca.id/blog/sop-penerimaan-pembayaran-qris-dinamis-kasir-pos).

### Tahap 4: Pengarsipan Struk Gesek dan Penandatanganan Lembar Rekonsiliasi
Jepit seluruh struk gesek nasabah (merchant copy) di belakang struk settlement mesin EDC. Tuliskan keterangan selisih jika ditemukan anomali transaksi. Kasir yang bertugas dan supervisor toko wajib menandatangani formulir Berita Acara Rekonsiliasi Shift sebelum kasir meninggalkan area kasir toko.

## Formulir Standar Berita Acara Rekonsiliasi Kasir Toko

Berikut adalah contoh format tabel lembar rekonsiliasi harian yang digunakan oleh gerai ritel modern:

Keterangan: Seluruh angka pada tabel di bawah ini merupakan data simulasi perhitungan keuangan untuk membedah pencocokan transaksi tunai dan nontunai.

| Metode Pembayaran Tagihan | Nilai Menurut Sistem POS Kasir | Nilai Fisik / Settlement Mesin | Selisih Nominal (+/-) | Keterangan Verifikasi Kasir |
| :--- | :--- | :--- | :--- | :--- |
| Uang Tunai di Laci Kasir | Rp3.450.000 | Rp3.450.000 (Hitung fisik) | Rp0 (Seimbang) | Uang fisik dihitung bersama supervisor |
| Pembayaran QRIS Dinamis | Rp5.820.000 | Rp5.820.000 (Mutasi portal) | Rp0 (Seimbang) | 42 Transaksi sukses status Paid |
| Kartu Debit EDC Bank A | Rp4.150.000 | Rp4.150.000 (Struk batch) | Rp0 (Seimbang) | Struk settlement EDC berhasil dicetak |
| Kartu Debit EDC Bank B | Rp2.300.000 | Rp2.150.000 (Struk batch) | Minus Rp150.000 | 1 Transaksi salah tekan tombol ke EDC A |
| Kartu Kredit EDC Bank B | Rp1.250.000 | Rp1.250.000 (Struk batch) | Rp0 (Seimbang) | MDR resmi terpotong 1,5% oleh bank |
| Total Peredaran Kasir Shift | Rp16.970.000 | Rp16.820.000 | Selisih Teknis Rp150.000 | Tertukar antar-tombol EDC, kas aman |

Melalui tabel formulir di atas, supervisor dapat langsung mengetahui bahwa selisih Rp150.000 pada EDC Bank B bukan merupakan uang yang hilang, melainkan kasir keliru memilih tombol nama bank di aplikasi POS saat menggesek kartu di mesin EDC Bank A.

## Prosedur Penanganan Transaksi Gantung dan Komplain Pelanggan

Salah satu kendala paling umum di meja kasir adalah transaksi gantung: saldo rekening pelanggan sudah terpotong di aplikasi ponsel mereka, namun mesin EDC atau layar kasir POS menyatakan transaksi gagal (declined / timeout).

Tiga langkah penanganan etis dan profesional sesuai aturan Bank Indonesia:
1. Periksa Status Mutasi di Layar Merchant Portal. Buka portal merchant QRIS atau cetak laporan audit transaksi terakhir di mesin EDC. Jika status transaksi menyatakan "Gagal / Reversal", berarti dana pelanggan akan dikembalikan secara otomatis oleh jaringan perbankan (refund) dalam waktu 1 kali 24 jam.
2. Berikan Penjelasan Ramah dan Tunjukkan Bukti Struk Gagal. Tunjukkan struk keterangan gagal dari mesin EDC kepada pelanggan secara transparan, dan minta pelanggan melakukan transaksi ulang atau membayar menggunakan metode pembayaran alternatif.
3. Catat Nomor Referensi RRN dan Nomor Kontak Pelanggan. Catat nomor rekening, nama bank, dan nomor telepon pelanggan pada buku log transaksi gantung kasir. Jika dana ternyata tetap masuk ke rekening toko keesokan harinya, pihak toko proaktif menghubungi pelanggan untuk mengembalikan dana secara jujur.

Anda dapat memperkuat pengawasan laci uang tunai saat pergantian shift malam menggunakan panduan [sop blind cash count dan pencegahan fraud kasir malam](https://cooca.id/blog/sop-blind-cash-count-dan-pencegahan-fraud-kasir-malam).

## Otomasi Rekonsiliasi Nontunai Terpadu Bersama Platform Cooca

Merekonsiliasi puluhan struk kertas mesin EDC secara manual setiap malam sangat menyita waktu dan rawan kekeliruan perhitungan staf toko yang sudah kelelahan.

Cooca hadir sebagai Business Operating System all-in-one yang menyederhanakan alur transaksi kasir UMKM Indonesia. Aplikasi kasir POS cloud Cooca mendukung integrasi kode QRIS dinamis langsung di layar kasir, di mana setiap pembayaran yang berhasil diverifikasi bank otomatis mengubah status nota transaksi menjadi lunas tanpa perlu konfirmasi manual. Cooca menyajikan laporan ringkasan metode pembayaran harian secara otomatis, memisahkan rincian penerimaan tunai, QRIS, dan kartu bank, serta mencocokkan potongan biaya MDR secara transparan dalam satu dashboard terpadu.

## Pertanyaan Seputar Rekonsiliasi Pembayaran QRIS dan EDC (FAQ)

Berikut adalah beberapa pertanyaan praktis seputar penanganan transaksi nontunai kasir:

### Berapa lama batas waktu dana transaksi QRIS masuk ke rekening bank merchant?
Sesuai standar Bank Indonesia, dana transaksi QRIS dinamis akan disalurkan (settlement) ke rekening bank merchant pada hari kerja berikutnya (H+1), atau pada hari yang sama (H+0) jika merchant berlangganan fitur pencairan dana instan pada payment gateway resmi.

### Apa yang terjadi jika kasir lupa melakukan settlement mesin EDC sebelum mematikan mesin?
Transaksi hari tersebut akan tetap tersimpan di memori mesin EDC, namun dana belum akan ditransfer oleh bank ke rekening toko Anda. Begitu kasir menyalakan mesin keesokan harinya dan mengeksekusi settlement, seluruh transaksi yang tertunda akan langsung diproses bersamaan.

### Mengapa struk mesin EDC kertas thermal tidak boleh terkena panas atau cairan alkohol?
Kertas thermal menggunakan lapisan kimia sensitif panas. Kontak dengan suhu panas, sinar matahari langsung, atau cairan pembersih tangan (hand sanitizer) akan membuat tinta tulisan struk memudar dan hilang, sehingga struk tidak bisa dibaca saat audit keuangan.

### Bagaimana cara mencegah kasir menempelkan kode QRIS statis palsu milik pribadi di meja kasir?
Gunakan sistem QRIS dinamis yang tercetak otomatis di layar kasir POS untuk setiap transaksi nominal tertentu, atau gunakan dudukan akrilik QRIS statis yang disegel dan diperiksa secara berkala oleh supervisor toko setiap awal shift.

### Apakah potongan biaya MDR QRIS boleh dibebankan tambahan ke total belanja pembeli?
Sesuai Peraturan Bank Indonesia Nomor 23/6/PBI/2021, merchant dilarang keras mengenakan biaya tambahan (surcharge) kepada konsumen atas penggunaan pembayaran QRIS. Biaya MDR wajib diperlakukan sebagai beban operasional toko yang diperhitungkan dalam HPP harga jual.

## Kesimpulan: Ketelitian Digital Penjaga Kredibilitas Usaha

Kemudahan bertransaksi secara nontunai adalah magnet penarik pelanggan modern, namun ketertiban administrasi rekonsiliasi adalah penjaga keselamatan kas usaha Anda. Dengan menerapkan SOP rekonsiliasi pembayaran QRIS dan EDC kasir secara disiplin setiap penutupan shift, mengarsipkan bukti transaksi secara rapi, dan memanfaatkan software kasir terintegrasi, gerai toko Anda terlindungi dari kebocoran finansial dan siap melayani ribuan transaksi nontunai dengan rasa aman dan percaya diri setiap hari.

```json
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "Article",
      "headline": "SOP Rekonsiliasi Pembayaran QRIS dan EDC Kasir Toko Ritel Bebas Selisih",
      "description": "Pelajari SOP rekonsiliasi pembayaran QRIS dan EDC kasir toko ritel, alur settlement harian mesin EDC, pencocokan mutasi bank, dan audit kas register.",
      "image": "https://cooca.id/images/blog/photo-1556740738-b6a63e27c4df.jpg",
      "datePublished": "2026-11-11T08:00:00+07:00",
      "dateModified": "2026-11-11T08:00:00+07:00",
      "url": "https://cooca.id/blog/sop-rekonsiliasi-pembayaran-qris-dan-edc-kasir",
      "mainEntityOfPage": "https://cooca.id/blog/sop-rekonsiliasi-pembayaran-qris-dan-edc-kasir",
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
          "name": "Berapa lama batas waktu dana transaksi QRIS masuk ke rekening bank merchant?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Sesuai standar Bank Indonesia, dana transaksi QRIS dinamis akan disalurkan (settlement) ke rekening bank merchant pada hari kerja berikutnya (H+1), atau pada hari yang sama (H+0) jika merchant berlangganan fitur pencairan dana instan pada payment gateway resmi."
          }
        },
        {
          "@type": "Question",
          "name": "Apa yang terjadi jika kasir lupa melakukan settlement mesin EDC sebelum mematikan mesin?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Transaksi hari tersebut akan tetap tersimpan di memori mesin EDC, namun dana belum akan ditransfer oleh bank ke rekening toko Anda. Begitu kasir menyalakan mesin keesokan harinya dan mengeksekusi settlement, seluruh transaksi yang tertunda akan langsung diproses bersamaan."
          }
        },
        {
          "@type": "Question",
          "name": "Mengapa struk mesin EDC kertas thermal tidak boleh terkena panas atau cairan alkohol?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Kertas thermal menggunakan lapisan kimia sensitif panas. Kontak dengan suhu panas, sinar matahari langsung, atau cairan pembersih tangan (hand sanitizer) akan membuat tinta tulisan struk memudar dan hilang, sehingga struk tidak bisa dibaca saat audit keuangan."
          }
        },
        {
          "@type": "Question",
          "name": "Bagaimana cara mencegah kasir menempelkan kode QRIS statis palsu milik pribadi di meja kasir?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Gunakan sistem QRIS dinamis yang tercetak otomatis di layar kasir POS untuk setiap transaksi nominal tertentu, atau gunakan dudukan akrilik QRIS statis yang disegel dan diperiksa secara berkala oleh supervisor toko setiap awal shift."
          }
        },
        {
          "@type": "Question",
          "name": "Apakah potongan biaya MDR QRIS boleh dibebankan tambahan ke total belanja pembeli?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Sesuai Peraturan Bank Indonesia Nomor 23/6/PBI/2021, merchant dilarang keras mengenakan biaya tambahan (surcharge) kepada konsumen atas penggunaan pembayaran QRIS. Biaya MDR wajib diperlakukan sebagai beban operasional toko yang diperhitungkan dalam HPP harga jual. ## Kesimpulan: Ketelitian Digital Penjaga Kredibilitas Usaha Kemudahan bertransaksi secara nontunai adalah magnet penarik pelanggan modern, namun ketertiban administrasi rekonsiliasi adalah penjaga keselamatan kas usaha Anda. Dengan menerapkan SOP rekonsiliasi pembayaran QRIS dan EDC kasir secara disiplin setiap penutupan shift, mengarsipkan bukti transaksi secara rapi, dan memanfaatkan software kasir terintegrasi, gerai toko Anda terlindungi dari kebocoran finansial dan siap melayani ribuan transaksi nontunai dengan rasa aman dan percaya diri setiap hari."
          }
        }
      ]
    }
  ]
}
```
