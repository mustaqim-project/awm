# scripts/generate_100_articles_engine.py
import os, sys, json, re, subprocess

def get_keywords_spec():
    # 100 keywords specs with cluster, unique slug, and target metadata
    return [
        # Cluster 1: Kasir & POS (15)
        {
            "id": 392,
            "kw": "aplikasi kasir toko kecil",
            "slug": "aplikasi-kasir-toko-kecil-modern",
            "cat": "Kasir & POS",
            "title": "Aplikasi Kasir Toko Kecil: Panduan Memilih dan Mengelola Transaksi",
            "mt": "Aplikasi Kasir Toko Kecil: Panduan Memilih dan Kelola",
            "md": "Pelajari panduan memilih aplikasi kasir toko kecil, integrasi printer thermal, metode pembayaran qris, dan pembukuan stok otomatis anti selisih kas.",
            "cluster": "pos",
            "angle": "Kriteria penting memilih aplikasi kasir untuk toko kelontong, minimarket, dan butik pakaian rintisan.",
            "subtopics": [
                "Mengapa Toko Kecil Wajib Beralih dari Nota Kertas ke Kasir Digital",
                "Kriteria Penting Memilih Software POS Toko Mikro",
                "Tabel Perbandingan Kasir Konvensional vs Aplikasi Kasir Berbasis Cloud",
                "Langkah Penerapan Kasir Digital dalam Waktu 3 Hari",
                "Pencegahan Selisih Uang Laci Kasir pada Pergantian Shift",
                "Peran COOCA POS Membantu Toko Kecil Tumbuh Mandiri"
            ],
            "links": [
                "https://cooca.id/blog/printer-struk-kasir",
                "https://cooca.id/blog/sop-buka-tutup-kasir",
                "https://cooca.id/blog/kasir-offline-tanpa-internet",
                "https://cooca.id/blog/barcode-scanner-toko"
            ]
        },
        {
            "id": 393,
            "kw": "aplikasi kasir offline gratis",
            "slug": "aplikasi-kasir-offline-gratis-umkm",
            "cat": "Kasir & POS",
            "title": "Aplikasi Kasir Offline Gratis: Solusi Transaksi Toko Tanpa Internet",
            "mt": "Aplikasi Kasir Offline Gratis: Solusi Transaksi Toko",
            "md": "Panduan memilih aplikasi kasir offline gratis, mekanisme penyimpanan database lokal, sinkronisasi otomatis saat online, dan tips cetak struk belanja.",
            "cluster": "pos",
            "angle": "Keandalan transaksi kasir saat listrik padam atau jaringan internet terputus di daerah pelosok.",
            "subtopics": [
                "Tantangan Konektivitas Internet pada Operasional Toko Fisik",
                "Cara Kerja Sinkronisasi Data Kasir Mode Offline ke Cloud",
                "Tabel Evaluasi Risiko Kasir Berbasis Web Murni vs Hybrid Offline",
                "SOP Penanganan Transaksi Kasir Saat Jaringan Offline Tiba-Tiba",
                "Keamanan Data Transaksi Lokal agar Tidak Hilang",
                "Keunggulan Paket Solo Owner COOCA Mode Offline Rp 0"
            ],
            "links": [
                "https://cooca.id/blog/kasir-offline-tanpa-internet",
                "https://cooca.id/blog/cara-pindah-ke-aplikasi-kasir",
                "https://cooca.id/blog/printer-struk-kasir",
                "https://cooca.id/blog/sop-buka-tutup-kasir"
            ]
        },
        {
            "id": 394,
            "kw": "software pos minimarket",
            "slug": "software-pos-minimarket-dan-barcode",
            "cat": "Kasir & POS",
            "title": "Software POS Minimarket: Standarisasi Scan Barcode dan Inventori",
            "mt": "Software POS Minimarket: Standarisasi Barcode dan Stok",
            "md": "Rekomendasi software pos minimarket lengkap, manajemen ribuan SKU barcode, multi-kasir kas kecil, integrasi laci kasir otomatis, dan laporan harian.",
            "cluster": "pos",
            "angle": "Menangani kecepatan pemindaian ratusan barang per menit di meja kasir swalayan.",
            "subtopics": [
                "Tuntutan Kecepatan Transaksi pada Bisnis Minimarket Modern",
                "Fitur Wajib Software POS untuk Mengelola Puluhan Ribu SKU",
                "Tabel Perangkat Keras Kasir Minimarket dan Standar Fungsinya",
                "SOP Penerimaan Barang Masuk dan Pembuatan Label Barcode Rak",
                "Manajemen Kas Kecil dan Petty Cash Laci Kasir",
                "Otomasi Inventori Minimarket Terpadu Bersama COOCA"
            ],
            "links": [
                "https://cooca.id/blog/barcode-scanner-toko",
                "https://cooca.id/blog/cara-stok-opname",
                "https://cooca.id/blog/cara-membuat-kode-sku",
                "https://cooca.id/blog/sop-audit-kasir-harian-dan-prosedur-void-pos"
            ]
        },
        {
            "id": 395,
            "kw": "mesin kasir android portable",
            "slug": "mesin-kasir-android-portable-pos",
            "cat": "Kasir & POS",
            "title": "Mesin Kasir Android Portable: Praktis untuk Usaha Gerobak dan Kafe",
            "mt": "Mesin Kasir Android Portable: Solusi Kafe dan Booth",
            "md": "Panduan mesin kasir android portable dengan printer thermal terpasang, daya tahan baterai, kemudahan transaksi outdoor, dan integrasi scan QRIS.",
            "cluster": "pos",
            "angle": "Fleksibilitas operasional bisnis makanan keliling, bazar akhir pekan, dan kedai tenda kopi.",
            "subtopics": [
                "Kebutuhan Mobilitas Tinggi bagi Pelaku Usaha Kuliner Rintisan",
                "Spesifikasi Mesin Kasir Genggam Android yang Layak Beli",
                "Tabel Kelebihan Mesin Kasir All-in-One Android vs Tablet Terpisah",
                "SOP Perawatan Baterai dan Kertas Struk Mesin Portable Outdoor",
                "Kemudahan Pembayaran Nontunai Dinamis di Meja Pelanggan",
                "Kompatibilitas Fleksibel COOCA POS di Semua Perangkat Android"
            ],
            "links": [
                "https://cooca.id/blog/printer-struk-kasir",
                "https://cooca.id/blog/rekonsiliasi-pembayaran-qris",
                "https://cooca.id/blog/cara-pindah-ke-aplikasi-kasir",
                "https://cooca.id/blog/menu-qr-code-restoran"
            ]
        },
        {
            "id": 396,
            "kw": "printer thermal bluetooth kasir",
            "slug": "printer-thermal-bluetooth-kasir-toko",
            "cat": "Kasir & POS",
            "title": "Printer Thermal Bluetooth Kasir: Cara Memilih Ukuran 58mm dan 80mm",
            "mt": "Printer Thermal Bluetooth Kasir: Panduan 58mm dan 80mm",
            "md": "Panduan lengkap memilih printer thermal bluetooth kasir, beda ukuran lebar kertas 58mm vs 80mm, koneksi nirkabel Android iOS, dan perawatan print head.",
            "cluster": "pos",
            "angle": "Menghindari masalah nota struk buram, kertas macet, dan baterai printer cepat drop.",
            "subtopics": [
                "Prinsip Kerja Cetak Tanpa Tinta pada Kertas Thermal",
                "Perbedaan Kebutuhan Kertas Struk 58mm dan 80mm",
                "Tabel Komparasi Koneksi Bluetooth vs Kabel USB Kasir",
                "Cara Mengatasi Kendala Pairing Bluetooth yang Sering Terputus",
                "Tips Memilih Gulungan Kertas Thermal Kualitas Arsip Tinggi",
                "Dukungan Integrasi Cetak Struk Cepat dari Sistem COOCA POS"
            ],
            "links": [
                "https://cooca.id/blog/printer-struk-kasir",
                "https://cooca.id/blog/sop-buka-tutup-kasir",
                "https://cooca.id/blog/sop-pemeliharaan-mesin-kasir-pos-printer-thermal",
                "https://cooca.id/blog/barcode-scanner-toko"
            ]
        },
        {
            "id": 397,
            "kw": "cara menggunakan barcode scanner di kasir",
            "slug": "cara-menggunakan-barcode-scanner-di-kasir-toko",
            "cat": "Kasir & POS",
            "title": "Cara Menggunakan Barcode Scanner di Kasir Toko Ritel Cepat Akurat",
            "mt": "Cara Menggunakan Barcode Scanner di Kasir Toko Ritel",
            "md": "Langkah cara menggunakan barcode scanner di kasir toko ritel, panduan konfigurasi mode auto-scan, mengatasi barcode rusak, dan integrasi master produk.",
            "cluster": "pos",
            "angle": "Menghilangkan kesalahan salah ketik harga kasir dan mempercepat antrean belanja.",
            "subtopics": [
                "Pentingnya Pemindaian Optik untuk Menghindari Human Error",
                "Jenis Barcode Scanner: Laser 1D vs Imager 2D QR Code",
                "Tabel Perbandingan Scanner Genggam USB vs Scanner Omnidirectional Meja",
                "SOP Kasir Saat Menghadapi Barcode Kemasan yang Robek atau Pudar",
                "Penataan Posisi Scanner Ergonomis agar Kasir Tidak Cepat Lelah",
                "Otomasi Pencarian Data Produk dengan Barcode Scanner di COOCA"
            ],
            "links": [
                "https://cooca.id/blog/barcode-scanner-toko",
                "https://cooca.id/blog/cara-membuat-kode-sku",
                "https://cooca.id/blog/cara-stok-opname",
                "https://cooca.id/blog/sop-buka-tutup-kasir"
            ]
        },
        {
            "id": 398,
            "kw": "sop buka tutup kasir harian",
            "slug": "sop-buka-tutup-kasir-harian-ritel",
            "cat": "Kasir & POS",
            "title": "SOP Buka Tutup Kasir Harian: Prosedur Wajib Staf Kasir Ritel",
            "mt": "SOP Buka Tutup Kasir Harian: Prosedur Staf Kasir Ritel",
            "md": "Contoh SOP buka tutup kasir harian toko ritel dan kafe, penghitungan modal awal laci, pencocokan kas fisik akhir shift, dan audit laporan penerimaan.",
            "cluster": "pos",
            "angle": "Mencegah selisih uang kas fisik kasir dan menjaga disiplin penyerahan shift harian.",
            "subtopics": [
                "Pentingnya Batasan Tanggung Jawab Keuangan Antar Shift Kasir",
                "Checklist Persiapan Buka Kasir dan Verifikasi Uang Kembalian",
                "Tabel Format Berita Acara Tutup Kasir Harian dan Serah Terima Kas",
                "Penanganan Uang Palsu dan Kebijakan Pembayaran Nontunai",
                "Langkah Tindakan Jika Terjadi Selisih Kurang atau Selisih Lebih Kas",
                "Otomasi Laporan Tutup Kasir Multi-Shift dalam Ekosistem COOCA"
            ],
            "links": [
                "https://cooca.id/blog/sop-buka-tutup-kasir",
                "https://cooca.id/blog/cara-mencegah-kecurangan-kasir",
                "https://cooca.id/blog/rekonsiliasi-pembayaran-qris",
                "https://cooca.id/blog/sop-audit-kasir-harian-dan-prosedur-void-pos"
            ]
        },
        {
            "id": 399,
            "kw": "cara mencegah kecurangan kasir",
            "slug": "cara-mencegah-kecurangan-kasir-ritel-modern",
            "cat": "Kasir & POS",
            "title": "Cara Mencegah Kecurangan Kasir: Kontrol Akses dan Audit Penjualan",
            "mt": "Cara Mencegah Kecurangan Kasir: Kontrol dan Audit Toko",
            "md": "Strategi cara mencegah kecurangan kasir toko ritel, modus manipulasi struk void, penyalahgunaan diskon manual, pengawasan CCTV, dan pembagian hak akses.",
            "cluster": "pos",
            "angle": "Melindungi keuntungan bersih toko dari kebocoran internal tanpa menciptakan kecurigaan berlebihan.",
            "subtopics": [
                "Mengenali Titik Lemah Operasional di Meja Kasir Konvensional",
                "Berbagai Modus Kecurangan Kasir yang Paling Sering Terjadi",
                "Tabel Matriks Kontrol Pembagian Peran Kasir vs Manajer Toko",
                "Penerapan Aturan Keras Otorisasi PIN untuk Pembatalan Transaksi",
                "Pemeriksaan Silang Rekaman CCTV dengan Waktu Struk Pembelian",
                "Sistem Keamanan Akses Bertingkat Berbasis Peran di COOCA POS"
            ],
            "links": [
                "https://cooca.id/blog/cara-mencegah-kecurangan-kasir",
                "https://cooca.id/blog/sop-buka-tutup-kasir",
                "https://cooca.id/blog/sop-audit-kasir-harian-dan-prosedur-void-pos",
                "https://cooca.id/blog/selisih-stok-sistem-dan-fisik"
            ]
        },
        {
            "id": 400,
            "kw": "rekonsiliasi pembayaran qris kasir",
            "slug": "rekonsiliasi-pembayaran-qris-kasir-toko",
            "cat": "Kasir & POS",
            "title": "Rekonsiliasi Pembayaran QRIS Kasir: Mengatasi Selisih MDR dan Dana Masuk",
            "mt": "Rekonsiliasi Pembayaran QRIS Kasir: Atasi Selisih Dana",
            "md": "Langkah rekonsiliasi pembayaran qris kasir toko, perhitungan potongan mdr bank, siklus settlement dana h+1, dan pencocokan mutasi rekening operasional.",
            "cluster": "pos",
            "angle": "Memastikan setiap transaksi kode QR benar-benar cair ke rekening bank tanpa selisih.",
            "subtopics": [
                "Ledakan Penggunaan QRIS dan Dampaknya pada Administrasi Kasir",
                "Memahami Biaya Merchant Discount Rate dan Jadwal Pencairan",
                "Tabel Simulasi Potongan Biaya MDR QRIS Reguler vs Usaha Mikro",
                "Langkah Pencocokan Laporan Settlement QRIS dengan Rekening Koran",
                "Pencegahan Modus Penipuan Struk Transfer Palsu di Meja Kasir",
                "Otomasi Pencatatan Kliring Nontunai Menggunakan COOCA"
            ],
            "links": [
                "https://cooca.id/blog/rekonsiliasi-pembayaran-qris",
                "https://cooca.id/blog/sop-buka-tutup-kasir",
                "https://cooca.id/blog/cara-mencegah-kecurangan-kasir",
                "https://cooca.id/blog/sop-rekonsiliasi-pembayaran-qris-dan-edc-kasir"
            ]
        },
        {
            "id": 401,
            "kw": "kasir manual vs kasir digital",
            "slug": "kasir-manual-vs-kasir-digital-umkm",
            "cat": "Kasir & POS",
            "title": "Kasir Manual vs Kasir Digital: Analisis Biaya dan Efisiensi Bisnis",
            "mt": "Kasir Manual vs Kasir Digital: Analisis Biaya UMKM",
            "md": "Perbandingan kasir manual vs kasir digital, risiko kertas nota hilang, kecepatan layan pelanggan, biaya langganan, dan akurasi laporan keuangan toko.",
            "cluster": "pos",
            "angle": "Studi komparasi riil biaya tenaga kerja dan potensi kerugian salah hitung manual.",
            "subtopics": [
                "Keterbatasan Sistem Pembukuan Nota Kertas di Era Bisnis Cepat",
                "Perhitungan Waktu yang Terbuang untuk Merekap Penjualan Manual",
                "Tabel Komparasi Komprehensif Biaya Operasional Kasir Kertas vs POS",
                "Dampak Kecepatan Layanan Meja Kasir Terhadap Kepuasan Pembeli",
                "Cara Melakukan Migrasi Data Produk Tanpa Menghentikan Toko",
                "Kemudahan Memulai Digitalisasi Kasir Rp 0 Bersama Paket Solo COOCA"
            ],
            "links": [
                "https://cooca.id/blog/kasir-manual-vs-kasir-digital",
                "https://cooca.id/blog/cara-pindah-ke-aplikasi-kasir",
                "https://cooca.id/blog/aplikasi-kasir-toko-kecil",
                "https://cooca.id/blog/printer-struk-kasir"
            ]
        },
        {
            "id": 402,
            "kw": "cara setting printer thermal struk",
            "slug": "cara-setting-printer-thermal-struk-usb-lan",
            "cat": "Kasir & POS",
            "title": "Cara Setting Printer Thermal Struk: Panduan Driver USB LAN Bluetooth",
            "mt": "Cara Setting Printer Thermal Struk: Driver USB dan LAN",
            "md": "Panduan teknis cara setting printer thermal struk belanja, instalasi driver ESC POS, konfigurasi IP address LAN, dan pengujian cetak laci kas otomatis.",
            "cluster": "pos",
            "angle": "Memandu pemilik toko memasang perangkat cetak struk mandiri tanpa memanggil teknisi.",
            "subtopics": [
                "Memahami Bahasa Perintah Standar ESC POS pada Printer Kasir",
                "Langkah Pengaturan Port Koneksi USB dan IP Address LAN",
                "Tabel Kode Pemecahan Masalah Error Printer Kasir yang Umum Terjadi",
                "Cara Menghubungkan Laci Kas RJ11 agar Terbuka Otomatis Saat Print",
                "Pengaturan Kerapatan Cetak dan Ukuran Margin Nota Belanja",
                "Kemudahan Konfigurasi Perangkat Keras Cetak di Platform COOCA"
            ],
            "links": [
                "https://cooca.id/blog/printer-struk-kasir",
                "https://cooca.id/blog/sop-pemeliharaan-mesin-kasir-pos-printer-thermal",
                "https://cooca.id/blog/kasir-offline-tanpa-internet",
                "https://cooca.id/blog/barcode-scanner-toko"
            ]
        },
        {
            "id": 403,
            "kw": "menu qr code restoran kafe",
            "slug": "menu-qr-code-restoran-kafe-modern",
            "cat": "Kasir & POS",
            "title": "Menu QR Code Restoran Kafe: Cara Kerja dan Efisiensi Biaya Cetak",
            "mt": "Menu QR Code Restoran Kafe: Solusi Hemat Biaya Cetak",
            "md": "Pelajari implementasi menu qr code restoran kafe, penghematan biaya cetak buku menu, pembaruan harga realtime, dan peningkatan kecepatan pelayanan meja.",
            "cluster": "pos",
            "angle": "Mengurangi beban kerja pramusaji dan mempercepat perputaran meja pada jam makan sibuk.",
            "subtopics": [
                "Mengapa Buku Menu Konvensional Menghabiskan Anggaran Operasional",
                "Cara Kerja Pemesanan Mandiri Berbasis Kode QR di Meja Tamu",
                "Tabel Simulasi Penghematan Biaya Cetak Menu Fisik vs Menu Digital",
                "Teknik Menata Foto Makanan Menggugah Selera di Menu Digital",
                "Integrasi Pesanan Meja Langsung ke Layar Dapur dan Kasir",
                "Solusi Menu Digital Interaktif Terintegrasi dari COOCA F&B"
            ],
            "links": [
                "https://cooca.id/blog/menu-qr-code-restoran",
                "https://cooca.id/blog/cara-menghitung-hpp-kopi",
                "https://cooca.id/blog/sop-buka-tutup-kasir",
                "https://cooca.id/blog/strategi-menu-engineering-kafe-matriks-bcg"
            ]
        },
        {
            "id": 404,
            "kw": "kasir self checkout qris dinamis",
            "slug": "kasir-self-checkout-qris-dinamis-ritel",
            "cat": "Kasir & POS",
            "title": "Kasir Self Checkout QRIS Dinamis: Solusi Ritel Masa Kini Tanpa Antre",
            "mt": "Kasir Self Checkout QRIS Dinamis: Solusi Ritel Cepat",
            "md": "Inovasi kasir self checkout qris dinamis, belanja mandiri konsumen di minimarket dan butik, kode pembayaran unik per nota, dan sistem keamanan anti pencurian.",
            "cluster": "pos",
            "angle": "Mengurangi antrean panjang di jam sibuk dengan memberdayakan pelanggan melakukan scan mandiri.",
            "subtopics": [
                "Pergeseran Perilaku Belanja Konsumen Menuju Kemandirian Transaksi",
                "Perbedaan QRIS Statis Stiker vs QRIS Dinamis Layar Kasir",
                "Tabel Biaya Investasi Self-Checkout Tablet vs Penambahan Tenaga Kasir",
                "Mekanisme Keamanan Timbangan dan Gerbang Validasi Pembayaran",
                "Pencegahan Barang Belanjaan Lolos Tanpa Terpindai di Mesin Mandiri",
                "Teknologi Self-Service POS Ramah UMKM Bersama COOCA"
            ],
            "links": [
                "https://cooca.id/blog/panduan-kasir-self-checkout-qris-dinamis-umkm",
                "https://cooca.id/blog/rekonsiliasi-pembayaran-qris",
                "https://cooca.id/blog/barcode-scanner-toko",
                "https://cooca.id/blog/cara-mencegah-kecurangan-kasir"
            ]
        },
        {
            "id": 405,
            "kw": "sop audit kasir dan prosedur void",
            "slug": "sop-audit-kasir-dan-prosedur-void-pos",
            "cat": "Kasir & POS",
            "title": "SOP Audit Kasir dan Prosedur Void: Pengendalian Pembatalan Struk Belanja",
            "mt": "SOP Audit Kasir dan Prosedur Void: Pengendalian Struk",
            "md": "Langkah SOP audit kasir dan prosedur void transaksi POS, pencegahan nota fiktif, validasi paraf manajer, pengarsipan struk rusak, dan pencatatan buku besar.",
            "cluster": "pos",
            "angle": "Memperketat integritas pencatatan kasir tanpa memperlambat ritme pelayanan pembeli.",
            "subtopics": [
                "Mengapa Fitur Void Transaksi Menjadi Celah Rawan Penggelapan Kas",
                "Perbedaan Prosedur Void Item Satuan vs Cancel Seluruh Transaksi",
                "Tabel Batasan Kuota Pembatalan Transaksi Harian yang Wajar",
                "Alur Otorisasi Kasir Wajib Menghadirkan Supervisor Toko",
                "Audit Berkala Pencocokan Log Aktivitas Sistem POS dengan Fisik Struk",
                "Pencatatan Audit Trail Transparan dan Tidak Dapat Dihapus di COOCA"
            ],
            "links": [
                "https://cooca.id/blog/sop-audit-kasir-harian-dan-prosedur-void-pos",
                "https://cooca.id/blog/cara-mencegah-kecurangan-kasir",
                "https://cooca.id/blog/sop-buka-tutup-kasir",
                "https://cooca.id/blog/rekonsiliasi-pembayaran-qris"
            ]
        },
        {
            "id": 406,
            "kw": "cara integrasi printer dapur dan kasir",
            "slug": "cara-integrasi-printer-dapur-dan-kasir-kafe",
            "cat": "Kasir & POS",
            "title": "Cara Integrasi Printer Dapur dan Kasir Kafe untuk Kecepatan Sajian",
            "mt": "Cara Integrasi Printer Dapur dan Kasir Kafe Akurat",
            "md": "Panduan cara integrasi printer dapur dan kasir kafe resto, pengaturan auto routing pesanan makanan dan minuman, kabel LAN WiFi, dan buzzer notifikasi koki.",
            "cluster": "pos",
            "angle": "Menghilangkan teriakan pesanan di dapur dan memastikan hidangan keluar berurutan tanpa salah meja.",
            "subtopics": [
                "Masalah Keterlambatan Pesanan Akibat Alur Komunikasi Dapur Manual",
                "Konsep Order Routing: Tiket Makanan ke Dapur dan Tiket Minuman ke Bar",
                "Tabel Perbandingan Printer Kabel LAN Ethernet vs Printer Bluetooth Dapur",
                "Pengaturan Buzzer Alarm Suara Kencang di Area Dapur yang Bising",
                "SOP Juru Masak Menandai Tiket Pesanan yang Telah Selesai Dimasak",
                "Sistem Pengaturan Routing Pesanan Multi-Printer Otomatis di COOCA"
            ],
            "links": [
                "https://cooca.id/blog/cara-integrasi-printer-dapur-dan-kasir-pos-kafe",
                "https://cooca.id/blog/printer-struk-kasir",
                "https://cooca.id/blog/menu-qr-code-restoran",
                "https://cooca.id/blog/sop-buka-tutup-kasir"
            ]
        }
    ]

print("Script specs loaded successfully.")
