---
title: "Panduan Alur Transaksi Split Payment Kasir POS antara Tunai dan QRIS"
meta_title: "Alur Transaksi Split Payment Kasir POS Tunai QRIS"
meta_description: "Panduan lengkap alur transaksi split payment kasir POS saat pelanggan membayar gabungan tunai dan QRIS atau kartu debit agar rekonsiliasi tetap seimbang."
slug: "alur-transaksi-split-payment-kasir-pos-tunai-dan-qris"
primary_keyword: "alur transaksi split payment kasir pos"
secondary_keywords: "pembayaran gabungan kasir pos, split bill transaksi toko, pencatatan tunai dan nontunai, rekonsiliasi settlement kasir, struk pembayaran ganda"
search_intent: "Informational"
category: "Kasir & POS"
reading_time: "10 menit"
cover_image: "https://cooca.id/images/blog/alur-transaksi-split-payment-kasir-pos-tunai-dan-qris.jpg"
published_at: "2026-09-06T14:15:00+07:00"
---
# Panduan Alur Transaksi Split Payment Kasir POS antara Tunai dan QRIS

Dalam era transformasi transaksi nontunai di Indonesia saat ini, perilaku belanja konsumen telah berubah secara dramatis. Pelanggan toko ritel, kafe kekinian, dan butik pakaian tidak lagi terpaku pada satu metode pembayaran tunggal saat berdiri di depan meja kasir. Sering kali seorang pelanggan ingin membayar sebagian total belanjanya menggunakan sisa uang tunai kertas yang ada di dompetnya, dan sisanya dilunasi dengan memindai kode QRIS melalui dompet digital atau aplikasi mobile banking di ponsel mereka. Skenario lain yang sangat umum terjadi di restoran adalah sekelompok anak muda yang ingin membagi pembayaran satu meja makan (split bill), di mana sebagian membayar tunai dan sebagian lainnya membayar dengan gesek kartu debit bank. Menghadapi situasi transaksi multi-metode bayar ini, staf kasir yang menggunakan mesin kasir konvensional sering kali panik, salah mengetik nominal, atau bahkan menolak permintaan pelanggan karena sistem komputernya tidak mendukung pembayaran ganda. Menguasai alur transaksi split payment kasir pos adalah kunci penting untuk melayani fleksibilitas pembayaran konsumen modern tanpa mengacaukan rekonsiliasi pembukuan kas dan bank toko Anda.

Split payment (pembayaran terpisah atau multi-tender payment) adalah kemampuan sistem kasir POS untuk membagi total tagihan dari satu nomor faktur transaksi ke dalam dua atau lebih metode pembayaran yang berbeda. Ketika sistem kasir Anda mampu memproses split payment dengan mulus, antrean kasir berjalan cepat, pelanggan merasa nyaman, dan laporan keuangan toko tetap seimbang sempurna antara uang fisik di laci dan saldo digital di rekening bank.

## Panduan dan Alur Transaksi Split Payment Kasir POS

Meskipun terlihat sederhana di mata pembeli, memproses pembayaran gabungan menimbulkan tantangan pembukuan yang cukup rumit bagi staf kasir jika tidak memiliki standar operasional yang baku:

### 1. Risiko Ketidakcocokan Kas Laci vs Bank (Reconciliation Discrepancy)
Jika total belanja adalah Rp150.000 dan pelanggan membayar Rp50.000 tunai serta Rp100.000 via QRIS, kasir yang ceroboh bisa saja menekan tombol Bayar Penuh QRIS Rp150.000. Akibatnya, pada saat tutup shift malam, saldo rekening bank toko berselisih kurang Rp50.000 sementara uang fisik di laci kasir berlebih Rp50.000. Memperbaiki kesalahan entri silang ini membutuhkan waktu investigasi yang melelahkan.

### 2. Celah Pemalsuan Bukti Transfer QRIS Statis
Saat transaksi dipecah, oknum pembeli yang berniat curang dapat memanfaatkan kelengahan kasir dengan menunjukkan tangkapan layar (screenshot) bukti pembayaran QRIS palsu untuk nominal yang dipecah tersebut. Kasir harus memastikan dana benar-benar masuk sebelum menyerahkan barang belanjaan.

### 3. Kebingungan Pembeli Terhadap Rincian Struk Belanja
Pelanggan membutuhkan struk belanja yang mencantumkan secara transparan berapa nominal yang dipotong dari uang tunai mereka dan berapa nominal yang didebit dari saldo QRIS mereka sebagai bukti pengeluaran pribadi yang sah.

Pencegahan penipuan struk digital ini dibahas tuntas dalam panduan [sop kasir anti nota qris palsu soundbox toko ritel](https://cooca.id/blog/sop-kasir-anti-nota-qris-palsu-soundbox-toko-ritel).

## Alur Prosedur Kerja Standar (Step-by-Step SOP Split Payment)

Berikut adalah alur operasional baku yang harus dipatuhi oleh seluruh kasir saat memproses transaksi pembayaran gabungan antara tunai dan QRIS:

### Tahap 1: Konfirmasi Keinginan Split Payment Pelanggan
Setelah kasir memindai seluruh item belanjaan dan menyebutkan total tagihan (misalnya: Total belanja Rp250.000), tanyakan kepada pembeli: Apakah ingin melakukan pembayaran tunai atau nontunai?. Jika pembeli menjawab: Saya bayar tunai Rp100.000 ya mbak, sisanya Rp150.000 saya scan QRIS, kasir mengonfirmasi ulang: Baik kak, tunai Rp100.000 dan scan QRIS Rp150.000.

### Tahap 2: Input Nominal Pembayaran Pertama (Uang Tunai)
Di aplikasi kasir POS, kasir memilih opsi Pembayaran Ganda / Multi-Payment:
1. Pilih metode pertama: **Tunai / Cash**.
2. Ketik nominal uang tunai yang diserahkan pembeli: **Rp100.000**.
3. Terima uang fisik dari pembeli, periksa keaslian lembaran uang dari risiko uang palsu, dan masukkan uang ke laci kasir.
4. Tekan tombol Proses Pembayaran 1. Sistem kasir akan mencatat penerimaan tunai Rp100.000 dan secara otomatis menampilkan sisa tagihan yang belum terbayar: **Sisa Tagihan Rp150.000**.

### Tahap 3: Penerbitan dan Pemindaian Kode QRIS Dinamis
1. Pilih metode pembayaran kedua: **QRIS Dinamis**.
2. Sistem POS secara otomatis menghasilkan kode QRIS di layar monitor yang khusus memuat tagihan sisa nominal pas sebesar **Rp150.000**.
3. Arahkan pembeli untuk memindai kode QRIS di layar pelanggan menggunakan aplikasi mobile banking atau dompet digital pilihan mereka.

### Tahap 4: Verifikasi Notifikasi Masuk (Soundbox atau Layar POS)
Kasir menunggu konfirmasi transaksi berhasil. Verifikasi dapat dilakukan melalui:
- Notifikasi audio dari perangkat Soundbox QRIS kasir yang bersuara: Pembayaran QRIS Berhasil Sebesar Seratus Lima Puluh Ribu Rupiah.
- Atau notifikasi popup hijau Transaksi Sukses di layar aplikasi POS Cooca yang terintegrasi langsung dengan payment gateway.

Jangan pernah menyelesaikan transaksi hanya dengan melihat sekilas layar HP pembeli dari kejauhan tanpa verifikasi sistem kasir toko Anda sendiri.

### Tahap 5: Pencetakan Struk Pembayaran Ganda (Dual-Payment Receipt)
Setelah kedua pembayaran terkonfirmasi lunas, sistem POS akan mencetak struk belanja resmi yang merinci kedua metode pembayaran tersebut secara jelas. Serahkan struk belanja beserta barang kepada pembeli dengan ucapan terima kasih ramah.

Tata kelola pencatatan ini memudahkan proses [cara rekonsiliasi arus kas qris edc transfer bank toko ritel](https://cooca.id/blog/cara-rekonsiliasi-arus-kas-qris-edc-transfer-bank-toko-ritel).

## Contoh Format Struk Belanja Split Payment

Berikut adalah format rincian informasi yang wajib tercetak pada struk belanja pembayaran terpisah:

| Komponen Struk Belanja | Rincian Informasi Tercetak di Kertas Struk | Status Pembukuan Sistem |
| :--- | :--- | :--- |
| Nomor Faktur Penjualan | **INV-2026-09-06-0042** | Transaksi POS Meja 01 |
| Waktu Transaksi | 06 September 2026, 14:28 WIB | Timestamp Sinkron Server |
| Rincian Pembelian Barang | 1x Kemeja Batik Pria Katun (Rp180.000)<br>1x Celana Formal Hitam (Rp70.000) | Total Subtotal: **Rp250.000** |
| Pembayaran 1: TUNAI | **Rp100.000** (Diterima Uang Tunai Lembar) | Masuk ke Saldo Kas Laci |
| Pembayaran 2: QRIS | **Rp150.000** (Ref ID: QRIS-9928174621) | Masuk ke Saldo Bank Giro |
| Sisa Pembayaran | **Rp0 (LUNAS / PAID)** | Faktur Selesai Terbit |

Struk dengan rincian ganda ini memberikan bukti hukum yang sah bagi pembeli dan bagian akuntansi toko, sekaligus mencegah kesalahpahaman saat audit tutup buku.

Informasi ini juga harus dipertimbangkan dengan kebijakan biaya transaksi melalui [kebijakan mdr qris terbaru usaha mikro batas 500 ribu](https://cooca.id/blog/kebijakan-mdr-qris-terbaru-usaha-mikro-batas-500-ribu).

## 4 Kesalahan Fatal Kasir dalam Memproses Split Payment

Hindari empat kebiasaan operasional yang keliru saat melayani pembayaran gabungan di toko:

1. Membuat Dua Nomor Faktur Terpisah: Membagi satu keranjang belanjaan menjadi dua nota terpisah (misalnya baju dibuat nota 1 bayar tunai, celana dibuat nota 2 bayar QRIS). Hal ini merusak data analitik Average Order Value (AOV) toko dan merepotkan pembeli yang harus membawa dua lembar struk belanja.
2. Tidak Memverifikasi Pembayaran Pertama Sebelum Membuka QRIS: Mengizinkan pembeli scan QRIS terlebih dahulu padahal uang tunainya belum dihitung atau belum diserahkan. Selalu selesaikan pembayaran tunai terlebih dahulu baru memproses pembayaran non-tunai.
3. Mengabaikan Selisih Pembulatan Receh: Memaksakan pembagian nominal ganjil yang tidak bisa dibayar dengan uang tunai (misalnya sisa tunai Rp15.350). Bulatkan bagian pembayaran tunai ke pecahan yang tersedia di laci dan bebankan sisanya ke pembayaran digital QRIS yang fleksibel hingga nominal rupiah satuan.
4. Lupa Mencocokkan Slip Saat Closing Shift: Menggabungkan seluruh setoran kas tanpa mencocokkan laporan rincian multi-tender. Pastikan modul tutup shift kasir memiliki kolom rekonsiliasi split payment tersendiri.

Standar penanganan uang laci ini berjalan selaras dengan prosedur [sop penanganan selisih uang kasir kurang dan lebih closing](https://cooca.id/blog/sop-penanganan-selisih-uang-kasir-kurang-dan-lebih-closing).

## Kemudahan Fitur Multi-Tender Payment di Aplikasi Kasir Cooca

Memproses split payment pada mesin kasir lama sering kali membingungkan staf dan membutuhkan banyak langkah penekanan tombol. Cooca Business Operating System menghadirkan antarmuka pembayaran multi-metode (Multi-Tender Payment) yang sangat intuitif di aplikasi kasir POS. Melalui Cooca, kasir cukup memilih metode Tunai, mengetik nominal uang tunai yang diserahkan, dan sistem Cooca secara otomatis mengkalkulasi sisa tagihan dan langsung memunculkan kode QRIS Dinamis terintegrasi di layar sekunder pembeli (Customer Facing Display). Begitu pembayaran QRIS terkonfirmasi sukses oleh gateway perbankan, transaksi otomatis tertutup lunas dan struk tercetak seketika tanpa risiko salah input manual oleh kasir. Seluruh data pembayaran tunai dan digital otomatis terpilah ke pos akun buku besar yang tepat di dashboard keuangan Cooca.

## Kesimpulan

Menguasai alur transaksi split payment kasir pos memberikan keunggulan kompetitif bagi toko ritel dan restoran Anda dalam melayani ekspektasi pelanggan modern yang menginginkan kemudahan bertransaksi. Dengan alur kerja yang terstandarisasi, teknologi POS yang terintegrasi, dan disiplin verifikasi notifikasi, bisnis Anda dapat memberikan pengalaman berbelanja yang menyenangkan tanpa mengorbankan ketepatan pembukuan kas toko Anda.

## FAQ

### Apakah split payment bisa dilakukan antara kartu debit EDC dan QRIS tanpa uang tunai?
Sangat bisa. Sistem kasir modern mendukung kombinasi metode non-tunai, misalnya sebagian dibayar dengan kartu debit bank Mandiri via mesin EDC dan sisanya dibayar menggunakan QRIS GoPay atau ShopeePay.

### Apa yang harus dilakukan jika pembayaran tunai sudah diterima namun pembayaran QRIS kedua gagal atau saldo pembeli tidak cukup?
Batalkan proses pembayaran QRIS kedua di sistem POS, lalu tanyakan kepada pembeli apakah ingin melunasi sisa tagihan menggunakan kartu debit lain atau menambah uang tunai. Jika pembeli membatalkan transaksi sama sekali, kembalikan uang tunai pertama yang tadi sudah diserahkan dengan bukti tanda terima pembatalan.

### Bagaimana pencatatan biaya potongan MDR (Merchant Discount Rate) pada transaksi split payment?
Potongan MDR hanya dikenakan pada porsi nominal yang dibayar melalui QRIS atau kartu EDC. Porsi uang tunai yang dibayarkan tidak terkena potongan MDR sama sekali, sehingga toko tetap menerima uang tunai utuh tanpa potongan biaya perbankan.

### Apakah pembeli boleh membagi pembayaran menjadi tiga metode berbeda dalam satu transaksi?
Tergantung pada kapabilitas software kasir POS Anda. Software kasir canggih seperti Cooca mendukung pembayaran hingga tiga atau empat metode sekaligus (misalnya: Tunai + Voucher Belanja + QRIS), asalkan seluruh nominal terbayar lunas.

### Mengapa kasir sangat disarankan menggunakan QRIS Dinamis untuk split payment dibanding QRIS Statis stiker meja?
Karena QRIS Dinamis otomatis memuat nominal sisa tagihan yang harus dibayar pembeli, sehingga pembeli tidak perlu mengetik nominal secara manual di HP mereka dan mengeliminasi risiko pembeli salah transfer angka nominal.

```json
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "Article",
      "headline": "Panduan Alur Transaksi Split Payment Kasir POS antara Tunai dan QRIS",
      "description": "Panduan lengkap alur transaksi split payment kasir POS saat pelanggan membayar gabungan tunai dan QRIS atau kartu debit agar rekonsiliasi tetap seimbang.",
      "image": "https://cooca.id/images/blog/alur-transaksi-split-payment-kasir-pos-tunai-dan-qris.jpg",
      "datePublished": "2026-09-06T14:15:00+07:00",
      "dateModified": "2026-09-06T14:15:00+07:00",
      "url": "https://cooca.id/blog/alur-transaksi-split-payment-kasir-pos-tunai-dan-qris",
      "mainEntityOfPage": "https://cooca.id/blog/alur-transaksi-split-payment-kasir-pos-tunai-dan-qris",
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
          "name": "Apakah split payment bisa dilakukan antara kartu debit EDC dan QRIS tanpa uang tunai?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Sangat bisa. Sistem kasir modern mendukung kombinasi metode non-tunai, misalnya sebagian dibayar dengan kartu debit bank Mandiri via mesin EDC dan sisanya dibayar menggunakan QRIS GoPay atau ShopeePay."
          }
        },
        {
          "@type": "Question",
          "name": "Apa yang harus dilakukan jika pembayaran tunai sudah diterima namun pembayaran QRIS kedua gagal atau saldo pembeli tidak cukup?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Batalkan proses pembayaran QRIS kedua di sistem POS, lalu tanyakan kepada pembeli apakah ingin melunasi sisa tagihan menggunakan kartu debit lain atau menambah uang tunai. Jika pembeli membatalkan transaksi sama sekali, kembalikan uang tunai pertama yang tadi sudah diserahkan dengan bukti tanda terima pembatalan."
          }
        },
        {
          "@type": "Question",
          "name": "Bagaimana pencatatan biaya potongan MDR (Merchant Discount Rate) pada transaksi split payment?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Potongan MDR hanya dikenakan pada porsi nominal yang dibayar melalui QRIS atau kartu EDC. Porsi uang tunai yang dibayarkan tidak terkena potongan MDR sama sekali, sehingga toko tetap menerima uang tunai utuh tanpa potongan biaya perbankan."
          }
        },
        {
          "@type": "Question",
          "name": "Apakah pembeli boleh membagi pembayaran menjadi tiga metode berbeda dalam satu transaksi?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Tergantung pada kapabilitas software kasir POS Anda. Software kasir canggih seperti Cooca mendukung pembayaran hingga tiga atau empat metode sekaligus (misalnya: Tunai + Voucher Belanja + QRIS), asalkan seluruh nominal terbayar lunas."
          }
        },
        {
          "@type": "Question",
          "name": "Mengapa kasir sangat disarankan menggunakan QRIS Dinamis untuk split payment dibanding QRIS Statis stiker meja?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Karena QRIS Dinamis otomatis memuat nominal sisa tagihan yang harus dibayar pembeli, sehingga pembeli tidak perlu mengetik nominal secara manual di HP mereka dan mengeliminasi risiko pembeli salah transfer angka nominal."
          }
        }
      ]
    }
  ]
}
```
