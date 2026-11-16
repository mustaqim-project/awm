---
title: "Panduan Cara Rekonsiliasi Arus Kas QRIS EDC Transfer Bank Toko Ritel"
meta_title: "Cara Rekonsiliasi Arus Kas QRIS EDC Transfer Bank"
meta_description: "Panduan cara rekonsiliasi arus kas qris edc transfer bank toko ritel, mengatasi selisih settlement T+1, potongan fee MDR perbankan, dan jurnal kas otomatis."
slug: "cara-rekonsiliasi-arus-kas-qris-edc-transfer-bank-toko-ritel"
primary_keyword: "cara rekonsiliasi arus kas qris edc transfer bank toko ritel"
secondary_keywords: "mengatasi selisih pencatatan kasir dan mutasi rekening bank, settlement qris h plus 1 dan potongan fee mdr, pencatatan otomatis transaksi nontunai pembukuan, rekonsiliasi harian kas bank usaha ritel kafe, aplikasi pembukuan sinkronisasi kasir pos"
search_intent: "informational"
category: "Keuangan & Pembukuan"
reading_time: "9 menit"
cover_image: "https://images.unsplash.com/photo-1554224154-26032ffc0d07?auto=format&fit=crop&w=1200&q=80"
published_at: "2026-11-16T08:33:00+07:00"
---
# Panduan Cara Rekonsiliasi Arus Kas QRIS EDC Transfer Bank Toko Ritel

Mempelajari langkah terstruktur cara rekonsiliasi arus kas qris edc transfer bank toko ritel secara disiplin adalah kunci utama menjaga kesehatan likuiditas toko modern di era pembayaran digital. Semakin banyak konsumen yang meninggalkan uang tunai dan beralih menggunakan pembayaran QRIS, gesek kartu debit mesin EDC, serta transfer mobile banking. Bagi pemilik toko, kemudahan ini sering kali berubah menjadi mimpi buruk saat tiba waktu pencocokan pembukuan di akhir hari.

Laporan penjualan di komputer kasir menunjukkan total pendapatan Rp 15.000.000, namun saldo mutasi rekening bank hanya bertambah Rp 12.300.000. Selisih jutaan rupiah tersebut bukan berarti uang Anda hilang dicuri, melainkan akibat adanya jeda pencairan perbankan (settlement timing T+1) dan potongan biaya administrasi Merchant Discount Rate (MDR). Pembahasan ini mengupas alur rekonsiliasi tiga titik, formula pencatatan biaya transaksi non-tunai, serta teknik otomasi pencocokan buku kas bank.

## Langkah Praktis Cara Rekonsiliasi Arus Kas QRIS EDC Transfer Bank Toko Ritel

Dalam menerapkan cara rekonsiliasi arus kas qris edc transfer bank toko ritel, Anda wajib memahami tiga penyebab utama selisih pembukuan kasir dengan mutasi bank:

1. **Jeda Waktu Pencairan Dana (Settlement Lag H+1 atau H+2):** Transaksi QRIS dan mesin EDC kartu debit yang terjadi pada sore atau malam hari umumnya baru dikliringkan oleh bank penyedia pembayaran (PJP) ke rekening Anda pada keesokan harinya pukul 10:00 atau 14:00 WIB. Bahkan untuk transaksi di akhir pekan, dana baru masuk pada hari Senin.
2. **Potongan Biaya Layanan MDR Otomatis:** Bank persepsi langsung memotong persentase biaya MDR (misal 0,7 persen untuk kartu debit atau 0,3 persen untuk QRIS nominal besar) sebelum dana bersih disetorkan ke rekening Anda. Jika tidak dicatat sebagai akun Beban Administrasi Bank, saldo buku besar Anda akan selalu selisih minus.
3. **Salah Input Metode Pembayaran oleh Kasir:** Kasir salah menekan tombol pembayaran di komputer kasir, misalnya pembeli membayar menggunakan transfer QRIS BCA tetapi kasir menekan tombol Kartu Debit Mandiri.

Untuk menjaga ketersediaan uang tunai selama libur panjang akhir tahun, pelajari strategi [strategi menjaga arus kas toko umkm libur akhir tahun](https://cooca.id/blog/strategi-menjaga-arus-kas-toko-umkm-libur-akhir-tahun) agar cadangan kas toko tetap aman.

## Alur Kerja Rekonsiliasi Tiga Titik (Three-Way Reconciliation)

Untuk membuktikan bahwa seluruh transaksi non-tunai telah masuk tuntas ke rekening bank Anda, terapkan metode rekonsiliasi tiga titik harian:

- **Titik 1: Laporan Tutup Shift Kasir (POS Report):** Rekapitulasi nilai penjualan per kanal pembayaran yang dicetak dari software kasir pada akhir operasional toko.
- **Titik 2: Laporan Portal Penyedia Pembayaran (Settlement Report PJP):** Laporan transaksi harian yang diunduh dari dashboard merchant QRIS atau aplikasi EDC (misal merchant portal BCA, Mandiri, atau Midtrans) yang merinci nilai kotor, potongan MDR, dan nilai bersih yang ditransfer.
- **Titik 3: Rekening Koran Bank (Bank Statement Mutasi):** Catatan kredit dana masuk riil di buku tabungan atau internet banking bisnis Anda.

Langkah pencocokan: Cocokkan nilai kotor di Titik 1 dengan Titik 2 untuk memastikan tidak ada nota kasir yang fiktif. Selanjutnya, cocokkan nilai bersih di Titik 2 dengan angka kredit mutasi di Titik 3. Jika ketiga titik cocok, transaksi dinyatakan terekonsiliasi sempurna.

| Saluran Pembayaran | Waktu Dana Masuk Rekening (Settlement) | Potongan Biaya MDR | Rekomendasi Akun Buku Besar |
| --- | --- | --- | --- |
| Pembayaran Tunai (Cash) | Seketika di Laci Kasir | Rp 0 (0%) | Kas di Tangan Kasir (Cash on Hand) |
| QRIS Usaha Mikro (UMI) | Hari yang Sama atau H+1 (Senin jika Libur) | 0% (Hingga Rp 500.000) | Piutang Pembayaran QRIS / Kas Bank |
| QRIS Kategori UKE / UME | Hari Kerja Berikutnya (H+1 Pagi) | 0,7% dari Total Transaksi | Kas Bank & Beban Administrasi MDR |
| Kartu Debit Mesin EDC | Hari Kerja Berikutnya (H+1 Siang) | 0,15% hingga 1% Sesuai Bank | Piutang Kliring EDC & Beban Bank |
| Transfer Bank Manual | Seketika di Rekening Bisnis | Sesuai Biaya BI-FAST (Rp 2.500) | Kas Bank Terkait Langsung |

Bagi gerai yang juga mengelola saldo toko online lokapasar, baca ulasan [mengelola jeda pencairan saldo marketplace arus kas](https://cooca.id/blog/mengelola-jeda-pencairan-saldo-marketplace-arus-kas) untuk sinkronisasi dompet digital penjual.

## Menyiapkan Akun Penampungan Kliring (Clearing Account)

Dalam ilmu akuntansi modern, uang transaksi digital yang belum masuk ke rekening bank tidak boleh langsung dicatat ke akun Kas Bank, melainkan dimasukkan ke dalam akun sementara bernama **Piutang Pembayaran Digital (Clearing Account)**.

Contoh pencatatan jurnal akuntansi otomatis:
1. Saat transaksi terjadi di kasir (misal QRIS Rp 1.000.000):
   - Debit: Piutang Transaksi QRIS Rp 1.000.000
   - Kredit: Pendapatan Penjualan Toko Rp 1.000.000
2. Saat dana cair ke rekening bank keesokan harinya (dipotong MDR Rp 7.000):
   - Debit: Kas Bank Mandiri Rp 993.000
   - Debit: Beban Administrasi MDR QRIS Rp 7.000
   - Kredit: Piutang Transaksi QRIS Rp 1.000.000

Dengan metode akun penampungan ini, saldo akun Piutang Transaksi QRIS akan kembali menjadi nol rupiah setiap kali bank melakukan transfer dana bersih. Jika saldo akun piutang ini masih bernilai positif setelah melewati hari kerja kedua, Anda dapat langsung mengidentifikasi transaksi mana yang tertahan di pihak bank penyedia pembayaran.

Untuk panduan pembukuan kas kecil di akhir tahun, pelajari artikel [rekonsiliasi bank kas kecil tutup buku tahunan 2026](https://cooca.id/blog/rekonsiliasi-bank-kas-kecil-tutup-buku-tahunan-2026) untuk audit tutup tahun.

Modul Akuntansi dan Kas Bank COOCA terintegrasi langsung dengan mesin kasir POS, sehingga pencatatan akun piutang kliring, perhitungan beban MDR perbankan, dan rekonsiliasi mutasi bank berlangsung secara otomatis tanpa perlu staf Anda menginput jurnal debit kredit secara manual.

## FAQ

### Mengapa saldo uang masuk di rekening bank lebih sedikit dari total penjualan kasir di struk?
Perbedaan nominal tersebut umumnya disebabkan oleh dua hal: pemotongan biaya Merchant Discount Rate (MDR) otomatis oleh bank penyedia pembayaran dan jadwal settlement dana yang baru cair pada hari kerja berikutnya.

### Kapan uang transaksi QRIS yang dibayar hari Sabtu cair ke rekening pedagang?
Sebagian besar bank persepsi dan penyedia dompet digital memproses kliring transaksi akhir pekan pada hari kerja berikutnya, yaitu hari Senin antara pukul 08:00 hingga 14:00 WIB.

### Bagaimana cara mendeteksi pembayaran QRIS yang gagal masuk padahal pembeli menunjukkan bukti bayar?
Cek langsung notifikasi di aplikasi merchant resmi Anda atau gunakan alat QRIS Soundbox yang menyuarakan nominal transaksi secara real-time. Jangan pernah hanya percaya pada tangkapan layar ponsel pembeli.

### Berapa kali sebaiknya rekonsiliasi kas bank dilakukan dalam toko ritel?
Rekonsiliasi pencocokan penjualan harian wajib dilakukan setiap malam saat tutup shift kasir. Sedangkan rekonsiliasi rekening koran bank dengan laporan portal settlement sebaiknya dilakukan setiap pagi hari kerja.

```json
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "Article",
      "headline": "Panduan Cara Rekonsiliasi Arus Kas QRIS EDC Transfer Bank Toko Ritel",
      "description": "Panduan cara rekonsiliasi arus kas qris edc transfer bank toko ritel, mengatasi selisih settlement T+1, potongan fee MDR perbankan, dan jurnal kas otomatis.",
      "image": "https://images.unsplash.com/photo-1554224154-26032ffc0d07?auto=format&fit=crop&w=1200&q=80",
      "datePublished": "2026-11-16T08:33:00+07:00",
      "dateModified": "2026-11-16T08:33:00+07:00",
      "url": "https://cooca.id/blog/cara-rekonsiliasi-arus-kas-qris-edc-transfer-bank-toko-ritel",
      "mainEntityOfPage": "https://cooca.id/blog/cara-rekonsiliasi-arus-kas-qris-edc-transfer-bank-toko-ritel",
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
          "name": "Mengapa saldo uang masuk di rekening bank lebih sedikit dari total penjualan kasir di struk?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Perbedaan nominal tersebut umumnya disebabkan oleh dua hal: pemotongan biaya Merchant Discount Rate (MDR) otomatis oleh bank penyedia pembayaran dan jadwal settlement dana yang baru cair pada hari kerja berikutnya."
          }
        },
        {
          "@type": "Question",
          "name": "Kapan uang transaksi QRIS yang dibayar hari Sabtu cair ke rekening pedagang?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Sebagian besar bank persepsi dan penyedia dompet digital memproses kliring transaksi akhir pekan pada hari kerja berikutnya, yaitu hari Senin antara pukul 08:00 hingga 14:00 WIB."
          }
        },
        {
          "@type": "Question",
          "name": "Bagaimana cara mendeteksi pembayaran QRIS yang gagal masuk padahal pembeli menunjukkan bukti bayar?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Cek langsung notifikasi di aplikasi merchant resmi Anda atau gunakan alat QRIS Soundbox yang menyuarakan nominal transaksi secara real-time. Jangan pernah hanya percaya pada tangkapan layar ponsel pembeli."
          }
        },
        {
          "@type": "Question",
          "name": "Berapa kali sebaiknya rekonsiliasi kas bank dilakukan dalam toko ritel?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Rekonsiliasi pencocokan penjualan harian wajib dilakukan setiap malam saat tutup shift kasir. Sedangkan rekonsiliasi rekening koran bank dengan laporan portal settlement sebaiknya dilakukan setiap pagi hari kerja."
          }
        }
      ]
    }
  ]
}
```
