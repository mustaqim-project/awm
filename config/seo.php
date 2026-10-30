<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Core Public Pages SEO Metadata Configuration (Single Source of Truth)
    |--------------------------------------------------------------------------
    |
    | Strict SEO Rules:
    | - Title: Maximum 60 characters
    | - Meta Description: 120 - 160 characters
    | - H1: Single H1 containing target commercial keywords
    | - OG Title & Description: Automatically synchronized
    |
    */

    'pages' => [
        '/' => [
            'title' => 'COOCA: Aplikasi Kasir, Stok & Pembukuan UMKM Gratis',
            'h1' => 'Aplikasi Kasir UMKM: Kasir, Stok & Pembukuan dalam Satu Sistem Cepat',
            'description' => 'Satu aplikasi untuk kasir POS, stok, pembukuan, WhatsApp & toko online UMKM. Gratis selamanya untuk mulai, tanpa kartu kredit. Siap dipakai 2 menit.',
            'og_title' => 'COOCA: Aplikasi Kasir, Stok & Pembukuan UMKM Gratis',
            'og_description' => 'Satu aplikasi untuk kasir POS, stok, pembukuan, WhatsApp & toko online UMKM. Gratis selamanya untuk mulai, tanpa kartu kredit. Siap dipakai 2 menit.',
        ],

        '/pricing' => [
            'title' => 'Harga Aplikasi Kasir & ERP UMKM Mulai Rp0 | COOCA',
            'h1' => 'Harga Aplikasi Kasir COOCA: Mulai Gratis, Tanpa Kontrak',
            'description' => 'Paket COOCA mulai Rp0 selamanya. Standard Rp39 rb, Premium Rp89 rb, Prestige Rp199 rb/bln. Tanpa biaya pasang, tanpa kontrak, bebas upgrade kapan saja.',
            'og_title' => 'Harga Aplikasi Kasir & ERP UMKM Mulai Rp0 | COOCA',
            'og_description' => 'Paket COOCA mulai Rp0 selamanya. Standard Rp39 rb, Premium Rp89 rb, Prestige Rp199 rb/bln. Tanpa biaya pasang, tanpa kontrak, bebas upgrade kapan saja.',
        ],

        '/omnichannel-erp/pos' => [
            'title' => 'Aplikasi POS Kasir Online dengan QRIS & Nota WA | COOCA',
            'h1' => 'Aplikasi POS Kasir Online yang Terhubung ke Stok & Pembukuan',
            'description' => 'Kasir POS di HP, tablet, atau PC. Terima QRIS, cetak struk printer thermal, kirim nota ke WhatsApp, dan tetap jalan saat offline. Gratis untuk mulai.',
            'og_title' => 'Aplikasi POS Kasir Online dengan QRIS & Nota WA | COOCA',
            'og_description' => 'Kasir POS di HP, tablet, atau PC. Terima QRIS, cetak struk printer thermal, kirim nota ke WhatsApp, dan tetap jalan saat offline. Gratis untuk mulai.',
        ],

        '/omnichannel-erp/inventory' => [
            'title' => 'Aplikasi Stok Barang & Gudang Multi-Cabang | COOCA',
            'h1' => 'Aplikasi Stok Barang Real-Time untuk Semua Cabang & Gudang',
            'description' => 'Pantau stok real-time di semua cabang dan gudang, purchasing ke supplier, stok opname, dan peringatan stok menipis. Terhubung langsung ke kasir.',
            'og_title' => 'Aplikasi Stok Barang & Gudang Multi-Cabang | COOCA',
            'og_description' => 'Pantau stok real-time di semua cabang dan gudang, purchasing ke supplier, stok opname, dan peringatan stok menipis. Terhubung langsung ke kasir.',
        ],

        '/omnichannel-erp/finance' => [
            'title' => 'Aplikasi Pembukuan & Laporan Keuangan UMKM | COOCA',
            'h1' => 'Aplikasi Pembukuan UMKM: Laporan Keuangan Otomatis dari Kasir',
            'description' => 'Setiap transaksi kasir otomatis jadi jurnal. Lihat laba rugi, arus kas, dan neraca real-time tanpa input ulang ke Excel. Coba gratis.',
            'og_title' => 'Aplikasi Pembukuan & Laporan Keuangan UMKM | COOCA',
            'og_description' => 'Setiap transaksi kasir otomatis jadi jurnal. Lihat laba rugi, arus kas, dan neraca real-time tanpa input ulang ke Excel. Coba gratis.',
        ],

        '/omnichannel-erp/erp' => [
            'title' => 'ERP UMKM Terintegrasi: Kasir, Stok, Keuangan | COOCA',
            'h1' => 'ERP UMKM yang Ringan, Tanpa Kerumitan Korporasi',
            'description' => 'ERP ringan untuk UMKM Indonesia. Penjualan, inventory, purchasing, produksi, dan akuntansi dalam satu sistem cloud. Mulai gratis, upgrade kapan saja.',
            'og_title' => 'ERP UMKM Terintegrasi: Kasir, Stok, Keuangan | COOCA',
            'og_description' => 'ERP ringan untuk UMKM Indonesia. Penjualan, inventory, purchasing, produksi, dan akuntansi dalam satu sistem cloud. Mulai gratis, upgrade kapan saja.',
        ],

        '/omnichannel-erp/analytics' => [
            'title' => 'Asisten AI & Analisa Penjualan untuk UMKM | COOCA',
            'h1' => 'Asisten AI & Dashboard Penjualan untuk Pemilik Usaha',
            'description' => 'Tanya laba, produk terlaris, dan tren stok lewat chat. Dashboard analitik COOCA mengubah data penjualan jadi keputusan bisnis yang cepat.',
            'og_title' => 'Asisten AI & Analisa Penjualan untuk UMKM | COOCA',
            'og_description' => 'Tanya laba, produk terlaris, dan tren stok lewat chat. Dashboard analitik COOCA mengubah data penjualan jadi keputusan bisnis yang cepat.',
        ],

        '/solutions/fnb' => [
            'title' => 'Aplikasi Kasir Restoran & Cafe dengan Stok Resep | COOCA',
            'h1' => 'Aplikasi Kasir Restoran: Meja, Dapur & Stok Resep Terhubung',
            'description' => 'POS restoran dan cafe yang memotong stok bahan per resep, cetak tiket dapur (KOT), kelola meja, dan hitung HPP otomatis. Coba gratis sekarang.',
            'og_title' => 'Aplikasi Kasir Restoran & Cafe dengan Stok Resep | COOCA',
            'og_description' => 'POS restoran dan cafe yang memotong stok bahan per resep, cetak tiket dapur (KOT), kelola meja, dan hitung HPP otomatis. Coba gratis sekarang.',
        ],

        '/solutions/retail' => [
            'title' => 'Aplikasi Kasir Toko & Minimarket dengan Barcode | COOCA',
            'h1' => 'Aplikasi Kasir Toko: Scan Barcode, Stok Rapi, Laba Jelas',
            'description' => 'Kasir toko dengan scan barcode, stok multi-satuan dus/pcs, catatan kasbon pelanggan, dan laporan laba harian. Cocok untuk kelontong dan minimarket.',
            'og_title' => 'Aplikasi Kasir Toko & Minimarket dengan Barcode | COOCA',
            'og_description' => 'Kasir toko dengan scan barcode, stok multi-satuan dus/pcs, catatan kasbon pelanggan, dan laporan laba harian. Cocok untuk kelontong dan minimarket.',
        ],

        '/solutions/workshop' => [
            'title' => 'Aplikasi Bengkel Motor & Mobil: SPK, Sparepart | COOCA',
            'h1' => 'Aplikasi Bengkel Motor: SPK Digital, Stok Sparepart & Komisi Montir',
            'description' => 'Kelola bengkel dengan SPK digital per nomor polisi, stok oli dan sparepart, riwayat servis pelanggan, dan komisi montir otomatis. Mulai gratis.',
            'og_title' => 'Aplikasi Bengkel Motor & Mobil: SPK, Sparepart | COOCA',
            'og_description' => 'Kelola bengkel dengan SPK digital per nomor polisi, stok oli dan sparepart, riwayat servis pelanggan, dan komisi montir otomatis. Mulai gratis.',
        ],

        '/solutions/laundry' => [
            'title' => 'Aplikasi Kasir Laundry Kiloan & Satuan | COOCA',
            'h1' => 'Aplikasi Kasir Laundry: Timbangan, Rak & Notifikasi WhatsApp',
            'description' => 'Aplikasi laundry dengan timbangan desimal, label barcode per kantong, nomor rak, dan notifikasi WhatsApp saat cucian selesai. Coba gratis.',
            'og_title' => 'Aplikasi Kasir Laundry Kiloan & Satuan | COOCA',
            'og_description' => 'Aplikasi laundry dengan timbangan desimal, label barcode per kantong, nomor rak, dan notifikasi WhatsApp saat cucian selesai. Coba gratis.',
        ],

        '/solutions/manufacturing' => [
            'title' => 'Software Produksi & BOM untuk UMKM Manufaktur | COOCA',
            'h1' => 'Software Produksi UMKM: BOM, Work Order & HPP Akurat',
            'description' => 'Kelola bahan baku, Bill of Materials (BOM), produksi, dan HPP riil untuk konveksi, makanan olahan, dan mebel. Satu sistem dengan stok dan keuangan.',
            'og_title' => 'Software Produksi & BOM untuk UMKM Manufaktur | COOCA',
            'og_description' => 'Kelola bahan baku, Bill of Materials (BOM), produksi, dan HPP riil untuk konveksi, makanan olahan, dan mebel. Satu sistem dengan stok dan keuangan.',
        ],

        '/solutions/services' => [
            'title' => 'Aplikasi Booking & Invoice untuk Bisnis Jasa | COOCA',
            'h1' => 'Aplikasi Booking & Invoice untuk Bisnis Jasa',
            'description' => 'Booking kalender, manajemen proyek, invoice termin DP, dan data pelanggan untuk salon, servis AC, studio foto, dan konsultan. Mulai gratis.',
            'og_title' => 'Aplikasi Booking & Invoice untuk Bisnis Jasa | COOCA',
            'og_description' => 'Booking kalender, manajemen proyek, invoice termin DP, dan data pelanggan untuk salon, servis AC, studio foto, dan konsultan. Mulai gratis.',
        ],

        '/omnichannel/whatsapp' => [
            'title' => 'Kirim Struk Kasir & Nota Digital via WhatsApp | COOCA',
            'h1' => 'Kirim Struk Kasir Digital & Notifikasi Pesanan via WhatsApp',
            'description' => 'Kirim nota belanja digital dan pengingat tagihan invoice resmi langsung ke WhatsApp pelanggan tanpa perlu simpan nomor di HP staf. Coba gratis.',
            'og_title' => 'Kirim Struk Kasir & Nota Digital via WhatsApp | COOCA',
            'og_description' => 'Kirim nota belanja digital dan pengingat tagihan invoice resmi langsung ke WhatsApp pelanggan tanpa perlu simpan nomor di HP staf. Coba gratis.',
        ],

        '/omnichannel/customer' => [
            'title' => 'CRM & Program Loyalitas Pelanggan UMKM | COOCA',
            'h1' => 'CRM & Program Poin untuk Pelanggan Setia',
            'description' => 'Simpan data dan riwayat belanja pelanggan, buat program poin, dan kirim promo terarah via WhatsApp. Pelanggan sekali beli jadi langganan.',
            'og_title' => 'CRM & Program Loyalitas Pelanggan UMKM | COOCA',
            'og_description' => 'Simpan data dan riwayat belanja pelanggan, buat program poin, dan kirim promo terarah via WhatsApp. Pelanggan sekali beli jadi langganan.',
        ],

        '/omnichannel/orders' => [
            'title' => 'Manajemen Pesanan Kasir, Meja & Online | COOCA',
            'h1' => 'Kelola Pesanan dari Kasir, Meja Kafe, dan WhatsApp Terpadu',
            'description' => 'Satukan antrean pesanan dari kasir toko, QR meja kafe, dan pesanan WhatsApp ke dalam satu layar pemrosesan teratur tanpa order terlewat.',
            'og_title' => 'Manajemen Pesanan Kasir, Meja & Online | COOCA',
            'og_description' => 'Satukan antrean pesanan dari kasir toko, QR meja kafe, dan pesanan WhatsApp ke dalam satu layar pemrosesan teratur tanpa order terlewat.',
        ],

        '/omnichannel/social-media' => [
            'title' => 'Kelola Akun Sosmed Bisnis Toko Terpadu | COOCA',
            'h1' => 'Kelola Seluruh Kanal Media Sosial Bisnis dari Satu Ruang Kerja',
            'description' => 'Kelola materi promosi di Instagram, Facebook, TikTok, dan Threads dari satu ruang kerja terpadu yang terhubung langsung ke katalog produk toko.',
            'og_title' => 'Kelola Akun Sosmed Bisnis Toko Terpadu | COOCA',
            'og_description' => 'Kelola materi promosi di Instagram, Facebook, TikTok, dan Threads dari satu ruang kerja terpadu yang terhubung langsung ke katalog produk toko.',
        ],

        '/content-automation/content-creation' => [
            'title' => 'Buat Konten Promosi Produk dari Kasir Toko | COOCA',
            'h1' => 'Buat Konten Promosi Produk Langsung dari Kasir & Stok',
            'description' => 'Tarik foto produk, harga promo, dan stok langsung dari kasir POS untuk membuat materi postingan media sosial dalam hitungan menit.',
            'og_title' => 'Buat Konten Promosi Produk dari Kasir Toko | COOCA',
            'og_description' => 'Tarik foto produk, harga promo, dan stok langsung dari kasir POS untuk membuat materi postingan media sosial dalam hitungan menit.',
        ],

        '/content-automation/content-calendar' => [
            'title' => 'Jadwal & Kalender Promosi Bisnis Toko | COOCA',
            'h1' => 'Rencanakan Pemasaran Sebulan Penuh dalam Satu Tampilan Visual',
            'description' => 'Rencanakan jadwal posting promosi toko sebulan penuh dalam satu tampilan kalender visual. Siapkan draf materi dan review sebelum tayang.',
            'og_title' => 'Jadwal & Kalender Promosi Bisnis Toko | COOCA',
            'og_description' => 'Rencanakan jadwal posting promosi toko sebulan penuh dalam satu tampilan kalender visual. Siapkan draf materi dan review sebelum tayang.',
        ],

        '/content-automation/publishing' => [
            'title' => 'Publikasi Otomatis Sosmed Toko Tanpa Upload | COOCA',
            'h1' => 'Tayangkan Konten Promosi Tepat Waktu Tanpa Perlu Unggah Manual',
            'description' => 'Tayangkan konten promosi produk tepat waktu ke Instagram, TikTok, dan Facebook secara otomatis tanpa repot unggah manual setiap hari.',
            'og_title' => 'Publikasi Otomatis Sosmed Toko Tanpa Upload | COOCA',
            'og_description' => 'Tayangkan konten promosi produk tepat waktu ke Instagram, TikTok, dan Facebook secara otomatis tanpa repot unggah manual setiap hari.',
        ],

        '/content-automation/analytics' => [
            'title' => 'Analisa Konten yang Menghasilkan Penjualan | COOCA',
            'h1' => 'Ketahui Konten Mana yang Benar-Benar Menghasilkan Pembeli',
            'description' => 'Ketahui postingan media sosial mana yang benar-benar menghasilkan transaksi penjualan di kasir toko dengan pelacakan konversi otomatis.',
            'og_title' => 'Analisa Konten yang Menghasilkan Penjualan | COOCA',
            'og_description' => 'Ketahui postingan media sosial mana yang benar-benar menghasilkan transaksi penjualan di kasir toko dengan pelacakan konversi otomatis.',
        ],

        '/business-operating-system/overview' => [
            'title' => 'Business Operating System UMKM: Kasir & Stok | COOCA',
            'h1' => 'Kendalikan Kasir, Stok, dan Keuangan dalam Satu Sistem Terpadu',
            'description' => 'BOS terpadu UMKM. Hubungkan kasir POS, persediaan gudang, arus kas, dan laporan laba pemilik toko dalam satu sistem otomatis seketika.',
            'og_title' => 'Business Operating System UMKM: Kasir & Stok | COOCA',
            'og_description' => 'BOS terpadu UMKM. Hubungkan kasir POS, persediaan gudang, arus kas, dan laporan laba pemilik toko dalam satu sistem otomatis seketika.',
        ],

        '/business-operating-system/how-it-works' => [
            'title' => 'Cara Kerja Sistem Terpadu Toko & Pembukuan | COOCA',
            'h1' => 'Bagaimana COOCA Mengotomasi Operasional Toko Anda',
            'description' => 'Pelajari alur data otomatis COOCA: dari transaksi kasir, pemotongan stok bahan baku, pembukuan jurnal otomatis, hingga analitik pemilik toko.',
            'og_title' => 'Cara Kerja Sistem Terpadu Toko & Pembukuan | COOCA',
            'og_description' => 'Pelajari alur data otomatis COOCA: dari transaksi kasir, pemotongan stok bahan baku, pembukuan jurnal otomatis, hingga analitik pemilik toko.',
        ],

        '/business-operating-system/why-cooca' => [
            'title' => 'Kenapa Pilih COOCA: Sistem Terpadu vs Aplikasi Terpisah',
            'h1' => 'Berhenti Membayar Banyak Software Terpisah yang Tidak Terhubung',
            'description' => 'Berhenti bayar banyak software terpisah. COOCA satukan kasir POS, stok resep, pembukuan riil, dan nota WhatsApp dalam satu ekosistem hemat.',
            'og_title' => 'Kenapa Pilih COOCA: Sistem Terpadu vs Aplikasi Terpisah',
            'og_description' => 'Berhenti bayar banyak software terpisah. COOCA satukan kasir POS, stok resep, pembukuan riil, dan nota WhatsApp dalam satu ekosistem hemat.',
        ],

        '/kalkulator/hpp' => [
            'title' => 'Kalkulator HPP Online Gratis & Cara Menghitungnya | COOCA',
            'h1' => 'Kalkulator HPP & Cara Menghitung HPP Produk',
            'description' => 'Hitung HPP produk dan harga jual ideal secara gratis. Lengkap dengan rumus HPP, contoh perhitungan makanan dan minuman, serta template Excel.',
            'og_title' => 'Kalkulator HPP Online Gratis & Cara Menghitungnya | COOCA',
            'og_description' => 'Hitung HPP produk dan harga jual ideal secara gratis. Lengkap dengan rumus HPP, contoh perhitungan makanan dan minuman, serta template Excel.',
        ],

        '/kalkulator/bep' => [
            'title' => 'Kalkulator BEP Online Gratis & Rumusnya | COOCA',
            'h1' => 'Kalkulator BEP & Cara Menghitung Titik Impas',
            'description' => 'Hitung titik impas (BEP) usaha dalam unit dan rupiah secara gratis. Lengkap dengan rumus BEP, contoh perhitungan, dan cara membaca hasilnya.',
            'og_title' => 'Kalkulator BEP Online Gratis & Rumusnya | COOCA',
            'og_description' => 'Hitung titik impas (BEP) usaha dalam unit dan rupiah secara gratis. Lengkap dengan rumus BEP, contoh perhitungan, dan cara membaca hasilnya.',
        ],

        // 6 Vertical Solutions (Prompt 5 Part B)
        '/solusi/kasir-warung' => [
            'title' => 'Aplikasi Kasir Warung & Toko Kelontong | COOCA',
            'h1' => 'Aplikasi Kasir Warung: Kelola Stok, Bon & Kasir HP',
            'description' => 'Aplikasi kasir warung dan toko kelontong gratis di HP. Scan barcode produk, catat bon kasbon hutang pelanggan via WhatsApp, dan pantau stok kulakan otomatis.',
            'og_title' => 'Aplikasi Kasir Warung & Toko Kelontong | COOCA',
            'og_description' => 'Aplikasi kasir warung dan toko kelontong gratis di HP. Scan barcode produk, catat bon kasbon hutang pelanggan via WhatsApp, dan pantau stok kulakan otomatis.',
        ],

        '/solusi/kasir-cafe-kecil' => [
            'title' => 'Aplikasi Kasir Cafe & Kedai Kopi | COOCA',
            'h1' => 'Aplikasi Kasir Cafe: Resep Kopi, Meja & QRIS Dinamis',
            'description' => 'Aplikasi kasir cafe dan warkop modern. Kelola resep bahan baku per cup, meja & split bill, cetak tiket dapur, dan pantau HPP riil otomatis. Coba gratis.',
            'og_title' => 'Aplikasi Kasir Cafe & Kedai Kopi | COOCA',
            'og_description' => 'Aplikasi kasir cafe dan warkop modern. Kelola resep bahan baku per cup, meja & split bill, cetak tiket dapur, dan pantau HPP riil otomatis. Coba gratis.',
        ],

        '/solusi/kasir-salon' => [
            'title' => 'Aplikasi Kasir Salon & Spa Kecantikan | COOCA',
            'h1' => 'Aplikasi Kasir Salon: Komisi Kapster & Stok Treatment',
            'description' => 'Aplikasi kasir salon kecantikan dan spa wanita. Hitung komisi kapster otomatis, rekam riwayat treatment pelanggan, dan kelola stok produk perawatan.',
            'og_title' => 'Aplikasi Kasir Salon & Spa Kecantikan | COOCA',
            'og_description' => 'Aplikasi kasir salon kecantikan dan spa wanita. Hitung komisi kapster otomatis, rekam riwayat treatment pelanggan, dan kelola stok produk perawatan.',
        ],

        '/solusi/kasir-barbershop' => [
            'title' => 'Aplikasi Kasir Barbershop & Pangkas Rambut | COOCA',
            'h1' => 'Aplikasi Kasir Barbershop: Komisi Barberman & Antrean',
            'description' => 'Aplikasi kasir barbershop dan pangkas rambut pria. Layar kasir cepat, hitung komisi per kepala potong otomatis, antrean kursi, dan penjualan pomade.',
            'og_title' => 'Aplikasi Kasir Barbershop & Pangkas Rambut | COOCA',
            'og_description' => 'Aplikasi kasir barbershop dan pangkas rambut pria. Layar kasir cepat, hitung komisi per kepala potong otomatis, antrean kursi, dan penjualan pomade.',
        ],

        '/solusi/kasir-kios' => [
            'title' => 'Aplikasi Kasir Konter HP & Pulsa | COOCA',
            'h1' => 'Aplikasi Kasir Konter HP: Aksesoris, Pulsa & Servis',
            'description' => 'Aplikasi kasir konter HP dan kios aksesoris. Cari ribuan tipe casing tanpa salah harga, modul tanda terima servis HP, dan rekap kas harian tanpa selisih.',
            'og_title' => 'Aplikasi Kasir Konter HP & Pulsa | COOCA',
            'og_description' => 'Aplikasi kasir konter HP dan kios aksesoris. Cari ribuan tipe casing tanpa salah harga, modul tanda terima servis HP, dan rekap kas harian tanpa selisih.',
        ],

        '/solusi/kasir-irt' => [
            'title' => 'Aplikasi Usaha Rumahan & Produsen Snack | COOCA',
            'h1' => 'Aplikasi Usaha Rumahan: HPP Produksi & Konsinyasi',
            'description' => 'Aplikasi usaha rumahan dan produsen snack IRT. Hitung HPP modal bahan baku per kemasan, kelola barang titip jual konsinyasi di warung mitra, dan pantau laba.',
            'og_title' => 'Aplikasi Usaha Rumahan & Produsen Snack | COOCA',
            'og_description' => 'Aplikasi usaha rumahan dan produsen snack IRT. Hitung HPP modal bahan baku per kemasan, kelola barang titip jual konsinyasi di warung mitra, dan pantau laba.',
        ],

        // 5 New Commercial Landing Pages (Prompt 8)
        '/aplikasi-kasir-gratis' => [
            'title' => 'Aplikasi Kasir Gratis Selamanya untuk UMKM | COOCA',
            'h1' => 'Aplikasi Kasir Gratis Selamanya untuk Usaha UMKM',
            'description' => 'Aplikasi kasir gratis selamanya untuk UMKM. Kelola 1 toko, 50 produk, dan 100 transaksi/bulan tanpa kartu kredit. Bisa cetak struk dan terima QRIS.',
            'og_title' => 'Aplikasi Kasir Gratis Selamanya untuk UMKM | COOCA',
            'og_description' => 'Aplikasi kasir gratis selamanya untuk UMKM. Kelola 1 toko, 50 produk, dan 100 transaksi/bulan tanpa kartu kredit. Bisa cetak struk dan terima QRIS.',
        ],

        '/aplikasi-kasir-android' => [
            'title' => 'Aplikasi Kasir Android untuk Toko & Warung | COOCA',
            'h1' => 'Aplikasi Kasir Android untuk Toko, Warung & Cafe',
            'description' => 'Aplikasi kasir Android ringan untuk HP dan tablet. Download APK gratis, hubungkan printer Bluetooth thermal, dan catat transaksi kasir secara cepat.',
            'og_title' => 'Aplikasi Kasir Android untuk Toko & Warung | COOCA',
            'og_description' => 'Aplikasi kasir Android ringan untuk HP dan tablet. Download APK gratis, hubungkan printer Bluetooth thermal, dan catat transaksi kasir secara cepat.',
        ],

        '/aplikasi-kasir-offline' => [
            'title' => 'Aplikasi Kasir Offline, Jalan Tanpa Internet | COOCA',
            'h1' => 'Aplikasi Kasir Offline: Tetap Jalan Tanpa Sinyal Internet',
            'description' => 'Aplikasi kasir offline yang tetap berfungsi saat internet mati. Transaksi kasir tersimpan aman di perangkat dan otomatis sinkron saat kembali online.',
            'og_title' => 'Aplikasi Kasir Offline, Jalan Tanpa Internet | COOCA',
            'og_description' => 'Aplikasi kasir offline yang tetap berfungsi saat internet mati. Transaksi kasir tersimpan aman di perangkat dan otomatis sinkron saat kembali online.',
        ],

        '/solusi/kasir-apotek' => [
            'title' => 'Aplikasi Kasir Apotek: Stok Obat & Expired | COOCA',
            'h1' => 'Aplikasi Kasir Apotek: Kelola Stok Obat & Kedaluwarsa',
            'description' => 'Aplikasi kasir apotek dan toko obat. Pantau nomor batch dan tanggal kedaluwarsa obat, scan barcode, cetak etiket aturan pakai, dan laporan laba obat.',
            'og_title' => 'Aplikasi Kasir Apotek: Stok Obat & Expired | COOCA',
            'og_description' => 'Aplikasi kasir apotek dan toko obat. Pantau nomor batch dan tanggal kedaluwarsa obat, scan barcode, cetak etiket aturan pakai, dan laporan laba obat.',
        ],

        '/solusi/aplikasi-catering' => [
            'title' => 'Aplikasi Catering: Pesanan, HPP Porsi & DP | COOCA',
            'h1' => 'Aplikasi Catering: Kelola Pesanan, DP & HPP Porsi',
            'description' => 'Aplikasi usaha catering dan prasmanan. Hitung HPP bahan baku per porsi/kotak, jadwal pengiriman pesanan, invoice DP bertahap, dan rekap laba katering.',
            'og_title' => 'Aplikasi Catering: Pesanan, HPP Porsi & DP | COOCA',
            'og_description' => 'Aplikasi usaha catering dan prasmanan. Hitung HPP bahan baku per porsi/kotak, jadwal pengiriman pesanan, invoice DP bertahap, dan rekap laba katering.',
        ],

        '/blog' => [
            'title' => 'Blog & Edukasi Bisnis UMKM Indonesia | COOCA',
            'h1' => 'Blog & Panduan Bisnis UMKM Indonesia',
            'description' => 'Pusat edukasi dan panduan praktis UMKM Indonesia: tutorial cara hitung HPP, BEP titik impas, pembukuan kasir, dan strategi operasional bisnis.',
            'og_title' => 'Blog & Edukasi Bisnis UMKM Indonesia | COOCA',
            'og_description' => 'Pusat edukasi dan panduan praktis UMKM Indonesia: tutorial cara hitung HPP, BEP titik impas, pembukuan kasir, dan strategi operasional bisnis.',
        ],

        '/jelajah' => [
            'title' => 'Direktori & Jelajah Bisnis UMKM Indonesia | COOCA',
            'h1' => 'Jelajahi Direktori Toko & Produk UMKM Indonesia',
            'description' => 'Temukan dan dukung ribuan bisnis UMKM lokal di seluruh Indonesia: kuliner F&B, toko retail kelontong, bengkel kendaraan, dan jasa profesional.',
            'og_title' => 'Direktori & Jelajah Bisnis UMKM Indonesia | COOCA',
            'og_description' => 'Temukan dan dukung ribuan bisnis UMKM lokal di seluruh Indonesia: kuliner F&B, toko retail kelontong, bengkel kendaraan, dan jasa profesional.',
        ],

        '/kalkulator' => [
            'title' => 'Kumpulan Kalkulator Bisnis & Keuangan UMKM | COOCA',
            'h1' => 'Koleksi Kalkulator Bisnis & Finansial UMKM Gratis',
            'description' => 'Koleksi 8 kalkulator bisnis gratis untuk UMKM: hitung HPP, BEP titik impas, margin harga jual, laba bersih, gaji karyawan, dan simulasi skenario.',
            'og_title' => 'Kumpulan Kalkulator Bisnis & Keuangan UMKM | COOCA',
            'og_description' => 'Koleksi 8 kalkulator bisnis gratis untuk UMKM: hitung HPP, BEP titik impas, margin harga jual, laba bersih, gaji karyawan, dan simulasi skenario.',
        ],

        '/kalkulator/harga-jual' => [
            'title' => 'Kalkulator Harga Jual: Markup vs Margin | COOCA',
            'h1' => 'Kalkulator Harga Jual & Rumus Penetapan Harga',
            'description' => 'Simulasikan penetapan harga jual produk online dengan perbandingan rumus markup dan margin keuntungan untuk memaksimalkan profit bisnis UMKM.',
            'og_title' => 'Kalkulator Harga Jual: Markup vs Margin | COOCA',
            'og_description' => 'Simulasikan penetapan harga jual produk online dengan perbandingan rumus markup dan margin keuntungan untuk memaksimalkan profit bisnis UMKM.',
        ],

        '/kalkulator/laba-bersih' => [
            'title' => 'Kalkulator Laba Bersih & Arus Kas UMKM | COOCA',
            'h1' => 'Kalkulator Laba Bersih & Proyeksi Keuangan Bisnis',
            'description' => 'Hitung proyeksi laba bersih usaha Anda setelah dikurangi seluruh biaya operasional, gaji, dan sewa secara akurat tanpa rumus Excel rumit.',
            'og_title' => 'Kalkulator Laba Bersih & Arus Kas UMKM | COOCA',
            'og_description' => 'Hitung proyeksi laba bersih usaha Anda setelah dikurangi seluruh biaya operasional, gaji, dan sewa secara akurat tanpa rumus Excel rumit.',
        ],

        '/kalkulator/gaji-karyawan' => [
            'title' => 'Kalkulator Gaji Karyawan & Lembur Toko | COOCA',
            'h1' => 'Kalkulator Gaji Karyawan & Hitung Upah Lembur',
            'description' => 'Hitung gaji pokok karyawan, tunjangan harian, upah lembur, dan potongan kasir secara transparan dan otomatis untuk bisnis UMKM Indonesia.',
            'og_title' => 'Kalkulator Gaji Karyawan & Lembur Toko | COOCA',
            'og_description' => 'Hitung gaji pokok karyawan, tunjangan harian, upah lembur, dan potongan kasir secara transparan dan otomatis untuk bisnis UMKM Indonesia.',
        ],

        '/kalkulator/pph-final' => [
            'title' => 'Kalkulator PPh Final UMKM 0,5% PP 55/2022 | COOCA',
            'h1' => 'Kalkulator PPh Final UMKM 0,5% Sesuai Aturan Resmi',
            'description' => 'Simulasikan perhitungan pajak PPh final UMKM tarif 0,5% dengan fasilitas batas omzet tidak kena pajak Rp500 juta sesuai aturan PP 55 Tahun 2022.',
            'og_title' => 'Kalkulator PPh Final UMKM 0,5% PP 55/2022 | COOCA',
            'og_description' => 'Simulasikan perhitungan pajak PPh final UMKM tarif 0,5% dengan fasilitas batas omzet tidak kena pajak Rp500 juta sesuai aturan PP 55 Tahun 2022.',
        ],

        '/kalkulator/omzet-harian' => [
            'title' => 'Kalkulator Omzet Harian & Target Penjualan | COOCA',
            'h1' => 'Kalkulator Target Omzet Harian & Bulanan Toko',
            'description' => 'Tentukan berapa target penjualan harian dan jumlah transaksi yang harus dicapai kasir toko agar target omzet bulanan bisnis Anda tercapai.',
            'og_title' => 'Kalkulator Omzet Harian & Target Penjualan | COOCA',
            'og_description' => 'Tentukan berapa target penjualan harian dan jumlah transaksi yang harus dicapai kasir toko agar target omzet bulanan bisnis Anda tercapai.',
        ],

        '/kalkulator/simulasi-what-if' => [
            'title' => 'Simulasi Skenario Bisnis What-If UMKM | COOCA',
            'h1' => 'Simulasi Skenario Bisnis & Analisa What-If Finansial',
            'description' => 'Uji ketahanan bisnis terhadap kenaikan harga bahan baku, penurunan daya beli pelanggan, atau kenaikan sewa tempat dengan kalkulator skenario.',
            'og_title' => 'Simulasi Skenario Bisnis What-If UMKM | COOCA',
            'og_description' => 'Uji ketahanan bisnis terhadap kenaikan harga bahan baku, penurunan daya beli pelanggan, atau kenaikan sewa tempat dengan kalkulator skenario.',
        ],

        '/template/pembukuan-warung-excel' => [
            'title' => 'Template Pembukuan Warung Excel Gratis | COOCA',
            'h1' => 'Download Template Pembukuan Warung Excel Gratis',
            'description' => 'Download spreadsheet Excel pembukuan warung dan toko kelontong gratis: catat arus kas harian, utang piutang, dan stok barang secara rapi.',
            'og_title' => 'Template Pembukuan Warung Excel Gratis | COOCA',
            'og_description' => 'Download spreadsheet Excel pembukuan warung dan toko kelontong gratis: catat arus kas harian, utang piutang, dan stok barang secara rapi.',
        ],

        '/template/laporan-keuangan-sederhana' => [
            'title' => 'Template Laporan Keuangan Sederhana Excel | COOCA',
            'h1' => 'Template Laporan Keuangan Sederhana untuk UMKM',
            'description' => 'Unduh template Excel laporan laba rugi, neraca sederhana, dan arus kas siap pakai untuk keperluan administrasi bisnis dan pengajuan modal.',
            'og_title' => 'Template Laporan Keuangan Sederhana Excel | COOCA',
            'og_description' => 'Unduh template Excel laporan laba rugi, neraca sederhana, dan arus kas siap pakai untuk keperluan administrasi bisnis dan pengajuan modal.',
        ],

        '/template/stok-opname-excel' => [
            'title' => 'Template Form Stok Opname Excel Gratis | COOCA',
            'h1' => 'Download Template Form Stok Opname Excel Gratis',
            'description' => 'Download format spreadsheet stok opname gudang toko gratis: bandingkan stok fisik vs sistem, deteksi selisih barang, dan cegah kebocoran.',
            'og_title' => 'Template Form Stok Opname Excel Gratis | COOCA',
            'og_description' => 'Download format spreadsheet stok opname gudang toko gratis: bandingkan stok fisik vs sistem, deteksi selisih barang, dan cegah kebocoran.',
        ],

        '/template/invoice-sederhana' => [
            'title' => 'Template Invoice & Surat Tagihan Excel | COOCA',
            'h1' => 'Template Invoice Penjualan & Surat Tagihan Gratis',
            'description' => 'Download format invoice penjualan dan surat tagihan profesional siap cetak dalam format Excel untuk mempermudah transaksi bisnis UMKM Anda.',
            'og_title' => 'Template Invoice & Surat Tagihan Excel | COOCA',
            'og_description' => 'Download format invoice penjualan dan surat tagihan profesional siap cetak dalam format Excel untuk mempermudah transaksi bisnis UMKM Anda.',
        ],

        '/about' => [
            'title' => 'Tentang COOCA: Visi Sistem Operasi Bisnis UMKM',
            'h1' => 'Tentang COOCA: Visi Business Operating System untuk UMKM Indonesia',
            'description' => 'Misi COOCA menyatukan kasir POS, stok inventori, dan akuntansi riil dalam satu ekosistem terintegrasi untuk jutaan UMKM Indonesia.',
            'og_title' => 'Tentang COOCA: Visi Sistem Operasi Bisnis UMKM',
            'og_description' => 'Misi COOCA menyatukan kasir POS, stok inventori, dan akuntansi riil dalam satu ekosistem terintegrasi untuk jutaan UMKM Indonesia.',
        ],

        '/demo' => [
            'title' => 'Demo Interaktif COOCA: Coba Kasir & Stok Gratis',
            'h1' => 'Coba Demo Interaktif COOCA: Simulasi Kasir & Ekosistem Bisnis',
            'description' => 'Uji coba simulasi kasir POS, barcode scanner, potong stok resep, dan laporan laba bersih otomatis tanpa perlu instalasi aplikasi.',
            'og_title' => 'Demo Interaktif COOCA: Coba Kasir & Stok Gratis',
            'og_description' => 'Uji coba simulasi kasir POS, barcode scanner, potong stok resep, dan laporan laba bersih otomatis tanpa perlu instalasi aplikasi.',
        ],

        '/support' => [
            'title' => 'Pusat Bantuan & Layanan Konsumen Resmi | COOCA',
            'h1' => 'Pusat Bantuan & Panduan Resmi COOCA: Solusi Cepat untuk Bisnis Anda',
            'description' => 'Pusat panduan teknis, tutorial printer Bluetooth, konfigurasi kasir toko, dan kontak bantuan WhatsApp resmi untuk pengguna COOCA.',
            'og_title' => 'Pusat Bantuan & Layanan Konsumen Resmi | COOCA',
            'og_description' => 'Pusat panduan teknis, tutorial printer Bluetooth, konfigurasi kasir toko, dan kontak bantuan WhatsApp resmi untuk pengguna COOCA.',
        ],

        '/omnichannel/marketplace' => [
            'title' => 'Integrasi Marketplace Shopee & Tokopedia | COOCA',
            'h1' => 'Integrasi Marketplace Shopee & Tokopedia',
            'description' => 'Sinkronkan stok produk, pesanan penjualan, dan laporan keuangan dari marketplace Shopee dan Tokopedia langsung ke kasir toko.',
            'og_title' => 'Integrasi Marketplace Shopee & Tokopedia | COOCA',
            'og_description' => 'Sinkronkan stok produk, pesanan penjualan, dan laporan keuangan dari marketplace Shopee dan Tokopedia langsung ke kasir toko.',
        ],

        '/resources/case-studies' => [
            'title' => 'Contoh Skenario UMKM & Studi Kasus | COOCA',
            'h1' => 'Contoh Skenario & Studi Kasus UMKM: Efisiensi Operasional Nyata',
            'description' => 'Pelajari contoh skenario bagaimana pelaku UMKM F&B, retail, dan jasa berhasil mengeliminasi selisih kasir dan kebocoran stok harian.',
            'og_title' => 'Contoh Skenario UMKM & Studi Kasus | COOCA',
            'og_description' => 'Pelajari contoh skenario bagaimana pelaku UMKM F&B, retail, dan jasa berhasil mengeliminasi selisih kasir dan kebocoran stok harian.',
        ],

        '/resources/faq' => [
            'title' => 'Tanya Jawab (FAQ) Solusi Kasir & Stok | COOCA',
            'h1' => 'Pusat Bantuan & Tanya Jawab (FAQ) Lengkap',
            'description' => 'Jawaban lengkap seputar paket gratis, printer thermal Bluetooth, kasir offline fallback, dan keamanan data cloud multi-tenant COOCA.',
            'og_title' => 'Tanya Jawab (FAQ) Solusi Kasir & Stok | COOCA',
            'og_description' => 'Jawaban lengkap seputar paket gratis, printer thermal Bluetooth, kasir offline fallback, dan keamanan data cloud multi-tenant COOCA.',
        ],

        '/marketplace/businesses' => [
            'title' => 'Daftar Merchant & Toko UMKM Terpercaya | COOCA',
            'h1' => 'Daftar Toko & Merchant UMKM Terpercaya',
            'description' => 'Temukan direktori merchant dan toko online UMKM terpercaya di Indonesia dari berbagai sektor ritel, kuliner, dan aneka jasa.',
            'og_title' => 'Daftar Merchant & Toko UMKM Terpercaya | COOCA',
            'og_description' => 'Temukan direktori merchant dan toko online UMKM terpercaya di Indonesia dari berbagai sektor ritel, kuliner, dan aneka jasa.',
        ],

        '/marketplace/products' => [
            'title' => 'Katalog Produk & Promo UMKM Terkini | COOCA',
            'h1' => 'Katalog Produk & Penawaran UMKM Terkini',
            'description' => 'Jelajahi penawaran produk unggulan UMKM lokal berkualitas dari berbagai kategori makanan, fashion, kerajinan, hingga kebutuhan rumah.',
            'og_title' => 'Katalog Produk & Promo UMKM Terkini | COOCA',
            'og_description' => 'Jelajahi penawaran produk unggulan UMKM lokal berkualitas dari berbagai kategori makanan, fashion, kerajinan, hingga kebutuhan rumah.',
        ],

        '/marketplace/locations' => [
            'title' => 'Direktori Toko UMKM per Kota di Indonesia | COOCA',
            'h1' => 'Daftar Lokasi & Merchant UMKM per Kota',
            'description' => 'Cari dan dukung bisnis UMKM lokal di kota Anda. Dari toko kelontong, resto cafe, bengkel, hingga penyedia aneka jasa terdekat.',
            'og_title' => 'Direktori Toko UMKM per Kota di Indonesia | COOCA',
            'og_description' => 'Cari dan dukung bisnis UMKM lokal di kota Anda. Dari toko kelontong, resto cafe, bengkel, hingga penyedia aneka jasa terdekat.',
        ],
    ],
];
