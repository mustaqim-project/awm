<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

final class PublicSolutionController extends Controller
{
    /**
     * Vertical solution metadata and copy.
     */
    private array $solutions = [
        'kasir-warung' => [
            'slug' => 'kasir-warung',
            'title' => 'Aplikasi Kasir Warung & Toko Kelontong',
            'badge' => 'Warung Kelontong',
            'headline' => 'Aplikasi Kasir Warung: Kelola Stok, Bon & Kasir HP',
            'subheadline' => 'Aplikasi kasir warung dan toko kelontong gratis di HP. Scan barcode produk, catat bon kasbon hutang pelanggan via WhatsApp, dan pantau stok kulakan otomatis tanpa kalkulator manual.',
            'pain_points' => [
                'Uang kas warung sering tekor karena catatan bon kasbon hutang tetangga tercecer di buku tulis kucel.',
                'Bingung menentukan harga jual saat harga beras, minyak goreng, atau gula pasir naik dari agen distributor.',
                'Stok barang dagangan sering selisih, kadaluarsa, atau hilang di rak tanpa terdeteksi sejak awal.'
            ],
            'features' => [
                ['title' => 'Pencatatan Bon / Kasbon Digital', 'desc' => 'Kirim tagihan dan rincian belanja hutang pelanggan langsung ke WhatsApp dalam 1 klik tanpa rasa canggung.'],
                ['title' => 'Multi-Satuan Otomatis (Dus, Renceng, Pcs)', 'desc' => 'Beli 1 dus dari distributor grosir, jual eceran per bungkus atau renceng secara otomatis tanpa hitung manual.'],
                ['title' => 'Scan Barcode Kamera Smartphone', 'desc' => 'Scan barcode kemasan sembako pabrikan cukup menggunakan kamera HP Anda tanpa harus membeli alat scanner mahal.'],
                ['title' => 'Kalkulator Kulakan & Margin Laba', 'desc' => 'Otomatis hitung margin keuntungan setiap kali harga kulakan naik sehingga harga jual selalu menghasilkan profit.'],
            ],
            'how_it_works' => [
                ['step' => '1', 'title' => 'Daftar & Masukkan Produk Warung', 'desc' => 'Cukup daftar akun gratis 2 menit, masukkan daftar sembako atau scan barcode produk langsung dari kamera HP Anda.'],
                ['step' => '2', 'title' => 'Catat Penjualan & Kasbon di Kasir', 'desc' => 'Pilih barang belanjaan pembeli dalam hitungan detik. Terima bayar tunai, QRIS dinamis, atau catat sebagai kasbon digital.'],
                ['step' => '3', 'title' => 'Laporan Laba Harian Otomatis', 'desc' => 'Pantau omzet bersih, sisa uang kasbon pelanggan, dan daftar stok yang harus segera dikulak kembali tanpa kalkulator.'],
            ],
            'faqs' => [
                ['q' => 'Apakah aplikasi kasir warung ini bisa digunakan secara gratis selamanya?', 'a' => 'Ya, paket Gratis COOCA tersedia selamanya tanpa biaya tersembunyi untuk warung kelontong dan toko sembako mikro di seluruh Indonesia.'],
                ['q' => 'Bagaimana jika sinyal internet di warung sedang gangguan atau lambat?', 'a' => 'Kasir POS COOCA tetap dapat mencatat transaksi secara offline tanpa koneksi internet. Semua data otomatis sinkron saat sinyal kembali normal.'],
                ['q' => 'Apakah pemilik warung harus membeli scanner barcode dan komputer khusus?', 'a' => 'Sama sekali tidak perlu. Anda cukup memakai smartphone Android yang sudah ada untuk scan barcode kemasan dan mencatat penjualan.'],
                ['q' => 'Bagaimana cara menagih kasbon hutang pelanggan agar tidak canggung?', 'a' => 'Pilih transaksi hutang pelanggan, klik tombol kirim rincian nota ke WhatsApp. Rekap belanjaan tersaji rapi dan profesional sehingga penagihan lebih tepat waktu.'],
                ['q' => 'Bisakah menjual barang dalam satuan dus, pak, dan eceran sekaligus?', 'a' => 'Bisa. Fitur konversi satuan multi-tier COOCA otomatis mengurangi stok dus saat Anda menjual per bungkus atau per renceng secara fleksibel.'],
            ],
            'parent_solution' => [
                'url' => '/solutions/retail',
                'title' => 'Aplikasi Kasir Toko & Minimarket',
                'desc' => 'Butuh fitur inventori multi-gudang, purchase order supplier, dan multi-kasir untuk toko minimarket skala menengah? Pelajari solusi retail lengkap.',
            ],
            'testimonial' => [
                'quote' => 'Dulu sering pusing nagih hutang tetangga karena catatannya di buku tulis kucel. Sekarang pakai Cooca, tinggal kirim rekap nota WA, bayarnya jadi tepat waktu.',
                'author' => 'Pak Budi Waluyo',
                'business' => 'Toko Sembako Berkah, Klaten'
            ]
        ],
        'kasir-cafe-kecil' => [
            'slug' => 'kasir-cafe-kecil',
            'title' => 'Aplikasi Kasir Cafe & Kedai Kopi',
            'badge' => 'Kedai Kopi & Cafe',
            'headline' => 'Aplikasi Kasir Cafe: Resep Kopi, Meja & QRIS Dinamis',
            'subheadline' => 'Aplikasi kasir cafe dan warkop modern. Kelola resep bahan baku per cup, meja & split bill, cetak tiket dapur, dan pantau HPP riil otomatis tanpa nota kertas manual.',
            'pain_points' => [
                'Bahan baku susu segar, sirup, dan biji kopi sering bocor atau terbuang sia-sia karena tidak ada takaran standar.',
                'Barista kewalahan saat jam sibuk (rush hour) sehingga pesanan minuman pelanggan sering tertukar atau lambat keluar.',
                'Tidak mengetahui pasti keuntungan bersih per cup kopi setelah dipotong biaya cup take-away, sedotan, dan es batu.'
            ],
            'features' => [
                ['title' => 'HPP Resep & Food Costing Otomatis', 'desc' => 'Setiap cup kopi espresso terjual, stok beans dan susu terpotong otomatis sesuai gramatur resep takaran standar.'],
                ['title' => 'Manajemen Meja & Split Bill Fleksibel', 'desc' => 'Dukung pesan antar meja, simpan pesanan sementara, dan pisah tagihan bayar patungan antar pengunjung dalam satu meja.'],
                ['title' => 'Dukungan Printer Thermal Bluetooth 58/80mm', 'desc' => 'Cetak struk kasir dan order tiket dapur (Kitchen Slip) langsung dari tablet barista atau smartphone Android.'],
                ['title' => 'Laporan Jam Sibuk & Menu Terlaris', 'desc' => 'Ketahui jam berapa kedai Anda paling ramai untuk mengatur jadwal shift barista dan restock bahan baku dengan tepat.'],
            ],
            'how_it_works' => [
                ['step' => '1', 'title' => 'Input Menu & Takaran Bahan Baku', 'desc' => 'Daftarkan varian kopi, non-kopi, dan camilan. Hubungkan setiap menu dengan resep gramatur beans, susu, sirup, hingga cup.'],
                ['step' => '2', 'title' => 'Catat Pesanan & Meja Pelanggan', 'desc' => 'Barista memilih nomor meja, mencatat request khusus (less sugar/oat milk), dan menerima bayar QRIS dinamis langsung.'],
                ['step' => '3', 'title' => 'Stok Terpotong & Laporan Siap', 'desc' => 'Sistem otomatis memotong stok bahan baku dan menghitung margin laba bersih harian tanpa rekonsiliasi manual.'],
            ],
            'faqs' => [
                ['q' => 'Apakah stok bahan baku seperti bubuk kopi dan susu otomatis terpotong saat pesanan dibuat?', 'a' => 'Ya, dengan fitur HPP Resep COOCA, setiap minuman yang terjual akan memotong stok bahan baku di gudang sesuai takaran standar gramatur.'],
                ['q' => 'Bisakah menggunakan printer Bluetooth mini tanpa kabel komputer di meja barista?', 'a' => 'Bisa. COOCA terhubung lancar ke semua printer thermal Bluetooth 58mm maupun 80mm langsung dari smartphone atau tablet Android.'],
                ['q' => 'Bagaimana cara menerima pembayaran non-tunai QRIS dari pelanggan cafe?', 'a' => 'QRIS dinamis langsung muncul di layar kasir atau tercetak pada struk belanja pelanggan sehingga verifikasi pembayaran berlangsung instan.'],
                ['q' => 'Apakah kasir cafe COOCA mendukung pesanan pisah tagihan (split bill)?', 'a' => 'Ya, kasir dapat memisahkan pesanan per orang atau per kelompok menu saat pelanggan ingin membayar patungan.'],
                ['q' => 'Bisakah tiket pesanan otomatis tercetak di area dapur atau bar belakang?', 'a' => 'Bisa. Sistem mendukung pencetakan tiket pesanan dapur (Kitchen Slip) sehingga juru masak atau barista langsung menerima instruksi order.'],
            ],
            'parent_solution' => [
                'url' => '/solutions/fnb',
                'title' => 'Aplikasi Kasir Restoran & F&B',
                'desc' => 'Untuk restoran skala besar dengan Kitchen Display System (KDS), banyak station dapur, dan reservasi meja kompleks. Lihat solusi F&B komprehensif.',
            ],
            'testimonial' => [
                'quote' => 'Food cost kafe kami akhirnya terkontrol rapi di angka 27%. Susu dan beans tidak pernah lagi bocor misterius di akhir shift.',
                'author' => 'Rian Hidayat',
                'business' => 'Kopi Sudut Santai, Bandung'
            ]
        ],
        'kasir-kios' => [
            'slug' => 'kasir-kios',
            'title' => 'Aplikasi Kasir Konter HP & Pulsa',
            'badge' => 'Konter HP & Kios',
            'headline' => 'Aplikasi Kasir Konter HP: Aksesoris, Pulsa & Servis',
            'subheadline' => 'Aplikasi kasir konter HP dan kios aksesoris. Cari ribuan tipe casing tanpa salah harga, modul tanda terima servis HP, dan rekap kas harian tanpa selisih uang.',
            'pain_points' => [
                'Karyawan kasir sering salah memberi harga karena tipe tempered glass dan casing handphone terlalu banyak.',
                'Status penerimaan servis HP berantakan dan rawan tertukar sparepart atau nota klaim pelanggan hilang.',
                'Stok barang kecil seperti kartu perdana, kabel data, dan konektor sering selisih di laci kasir.'
            ],
            'features' => [
                ['title' => 'Pencarian Cepat Tipe HP & SKU', 'desc' => 'Ketik 2 huruf langsung muncul daftar tipe smartphone yang cocok beserta harga eceran maupun harga grosir.'],
                ['title' => 'Modul Tanda Terima Servis HP', 'desc' => 'Cetak nota penerimaan servis resmi lengkap dengan nomor IMEI, keluhan kerusakan, dan estimasi biaya perbaikan.'],
                ['title' => 'Kunci Diskon & Proteksi Kasir', 'desc' => 'Cegah karyawan kasir memanipulasi diskon atau melihat laporan profit rahasia tanpa izin supervisor.'],
                ['title' => 'Rekap Kas Harian Tanpa Selisih', 'desc' => 'Manajemen pergantian shift kasir yang otomatis menghitung selisih uang fisik vs mutasi penjualan non-tunai.'],
            ],
            'how_it_works' => [
                ['step' => '1', 'title' => 'Input Master Aksesoris & Servis', 'desc' => 'Unggah atau masukkan daftar ratusan aksesoris handphone beserta harga modal dan harga jual eceran/grosir.'],
                ['step' => '2', 'title' => 'Kasir Transaksi Kilat & Tiket Servis', 'desc' => 'Proses transaksi belanja aksesoris dalam 5 detik dan cetak surat tanda terima servis untuk pelanggan.'],
                ['step' => '3', 'title' => 'Tutup Shift Kasir & Cek Profit Bersih', 'desc' => 'Hitung saldo laci kasir di akhir giliran kerja. Laporan laba penjualan barang dan jasa servis terangkum transparan.'],
            ],
            'faqs' => [
                ['q' => 'Apakah sistem ini bisa mencetak tanda terima servis handphone yang masuk?', 'a' => 'Ya, modul servis mencatat nama pemilik, nomor WA, merk & tipe HP, nomor IMEI, gejala kerusakan, estimasi biaya, dan status pengerjaan.'],
                ['q' => 'Bagaimana cara mencegah kasir salah memberikan harga casing atau tempered glass?', 'a' => 'Pencarian cerdas COOCA mengelompokkan aksesoris per tipe HP secara akurat sehingga kasir tidak perlu menghafal harga ratusan barang.'],
                ['q' => 'Bisakah mengatur harga grosir untuk pelanggan sesama konter?', 'a' => 'Bisa, Anda dapat membuat skema harga grosir khusus untuk pelanggan mitra atau rekanan konter pulsa lainnya.'],
                ['q' => 'Bagaimana cara memeriksa kejujuran kasir saat pergantian jam kerja?', 'a' => 'Fitur buka/tutup kasir mengharuskan staf memasukkan jumlah uang fisik di laci kasir. Sistem akan otomatis membandingkan dengan total penjualan sistem.'],
                ['q' => 'Apakah aplikasi kasir konter HP ini bisa dijalankan di smartphone?', 'a' => 'Bisa. Aplikasi COOCA sangat ringan dan dapat dijalankan langsung di smartphone Android tanpa instalasi yang rumit.'],
            ],
            'parent_solution' => [
                'url' => '/solutions/retail',
                'title' => 'Aplikasi Kasir Retail & Toko',
                'desc' => 'Untuk toko retail dengan rantai distribusi cabang dan inventori ribuan barcode. Telusuri sistem retail terpadu.',
            ],
            'testimonial' => [
                'quote' => 'Penjualan aksesoris dan servis HP sekarang terpantau rapi. Karyawan baru tidak perlu lagi menghafal harga ratusan tipe casing.',
                'author' => 'Deni Pratama',
                'business' => 'Galaxy Cell & Accessories, Surabaya'
            ]
        ],
        'kasir-salon' => [
            'slug' => 'kasir-salon',
            'title' => 'Aplikasi Kasir Salon & Spa Kecantikan',
            'badge' => 'Salon & Spa',
            'headline' => 'Aplikasi Kasir Salon: Komisi Kapster & Stok Treatment',
            'subheadline' => 'Aplikasi kasir salon kecantikan dan spa wanita. Hitung komisi kapster otomatis, rekam riwayat treatment pelanggan, dan kelola stok produk perawatan tanpa sengketa akhir bulan.',
            'pain_points' => [
                'Penghitungan komisi terapis dan kapster di akhir bulan memakan waktu berhari-hari dan sering memicu perdebatan.',
                'Lupa riwayat formula warna cat rambut atau jenis facial yang cocok dengan kulit pelanggan tetap.',
                'Stok produk perawatan rambut dan botol serum kecantikan sering terpakai tanpa tercatat pemakaiannya.'
            ],
            'features' => [
                ['title' => 'Bagi Hasil & Komisi Kapster Otomatis', 'desc' => 'Atur komisi berbasis persentase atau nominal rupiah per jenis treatment. Laporan upah kapster terbit otomatis setiap akhir bulan.'],
                ['title' => 'Rekam Riwayat Perawatan Pelanggan', 'desc' => 'Simpan catatan riwayat treatment, preferensi kapster favorit, dan formula warna cat rambut terakhir pelanggan.'],
                ['title' => 'Paket Treatment & Bundling Promo', 'desc' => 'Buat paket layanan hemat (contoh: Creambath + Manicure Pedicure) dengan harga bundling khusus promosi.'],
                ['title' => 'Pengurangan Bahan Treatment Otomatis', 'desc' => 'Stok produk perawatan seperti krim bleaching atau vitamin rambut terpotong otomatis setiap kali layanan selesai.'],
            ],
            'how_it_works' => [
                ['step' => '1', 'title' => 'Daftarkan Menu Jasa & Kapster', 'desc' => 'Masukkan jenis perawatan salon, tarif jasa, bahan yang dibutuhkan, dan aturan komisi bagi hasil masing-masing terapis.'],
                ['step' => '2', 'title' => 'Input Treatment & Pilih Terapis di Kasir', 'desc' => 'Saat pelanggan selesai treatment, kasir memilih jenis layanan dan nama terapis yang mengerjakan untuk cetak nota.'],
                ['step' => '3', 'title' => 'Laporan Komisi & Stok Otomatis Keluar', 'desc' => 'Komisi terapis langsung masuk ke buku upah dan stok bahan terpotong rapi tanpa hitungan manual di akhir bulan.'],
            ],
            'faqs' => [
                ['q' => 'Bagaimana sistem COOCA menghitung komisi kapster atau terapis salon?', 'a' => 'Komisi dihitung otomatis saat kasir memilih nama terapis yang menangani pelanggan, baik berdasarkan persentase jasa maupun nominal tetap per kepala.'],
                ['q' => 'Apakah pemilik salon bisa mencatat formula khusus perawatan pelanggan?', 'a' => 'Bisa, riwayat pelanggan menyimpan riwayat perawatan sebelumnya, termasuk nomor formula cat rambut atau catatan alergi kulit.'],
                ['q' => 'Bisakah menjual produk retail kecantikan bersamaan dengan jasa perawatan?', 'a' => 'Bisa. Kasir dapat menggabungkan pembayaran jasa potong rambut, creambath, dan pembelian botol sampo dalam satu lembar struk nota.'],
                ['q' => 'Apakah pemakaian obat rebonding atau bleaching bisa memotong stok?', 'a' => 'Ya, layanan jasa dapat dihubungkan ke bahan baku sehingga stok obat di gudang salon otomatis berkurang setiap pengerjaan treatment.'],
                ['q' => 'Apakah terapis bisa mengecek komisi mereka sendiri secara mandiri?', 'a' => 'Ya, Anda dapat memberikan akses pantauan komisi harian kepada staf agar pembagian upah transparan dan bebas kecurigaan.'],
            ],
            'parent_solution' => [
                'url' => '/solutions/services',
                'title' => 'Aplikasi Booking & Invoice Jasa',
                'desc' => 'Untuk klinik estetika, jasa konsultasi, dan bisnis jasa dengan kalender booking online dan termin DP. Lihat solusi jasa.',
            ],
            'testimonial' => [
                'quote' => 'Dulu tiap akhir bulan pusing hitung buku komisi 8 kapster. Sekarang laporan komisi langsung keluar dalam satu klik.',
                'author' => 'Sisca Wijaya',
                'business' => 'Glow Beauty Salon, Tangerang'
            ]
        ],
        'kasir-barbershop' => [
            'slug' => 'kasir-barbershop',
            'title' => 'Aplikasi Kasir Barbershop & Pangkas Rambut',
            'badge' => 'Barbershop',
            'headline' => 'Aplikasi Kasir Barbershop: Komisi Barberman & Antrean',
            'subheadline' => 'Aplikasi kasir barbershop dan pangkas rambut pria. Layar kasir cepat, hitung komisi per kepala potong otomatis, antrean kursi, dan penjualan pomade lebih terpantau.',
            'pain_points' => [
                'Barberman sering mencurigai pencatatan manual kasir saat rekap bagi hasil mingguan atau bulanan.',
                'Pelanggan bosan menunggu antrean panjang tanpa kejelasan urutan kursi pangkas rambut.',
                'Produk grooming pria seperti pomade, hair tonic, dan clay sering tidak terpantau sisa stok dan margin keuntungannya.'
            ],
            'features' => [
                ['title' => 'Laporan Komisi Barberman Transparan', 'desc' => 'Setiap barberman dapat memeriksa jumlah kepala potong yang mereka selesaikan hari ini secara transparan tanpa manipulasi.'],
                ['title' => 'Antrean Kursi & Pemilihan Barberman', 'desc' => 'Pelanggan dapat memilih barberman favorit mereka di layar kasir dengan nomor antrean yang tertib.'],
                ['title' => 'Penjualan Produk Retail Grooming', 'desc' => 'Gabungkan tagihan jasa potong rambut dan pembelian pomade/hair powder dalam satu struk transaksi rapi.'],
                ['title' => 'Program Loyalitas & Member Poin', 'desc' => 'Berikan reward potong rambut gratis ke-10 atau diskon membership untuk meningkatkan kunjungan pelanggan tetap.'],
            ],
            'how_it_works' => [
                ['step' => '1', 'title' => 'Masukkan Layanan Grooming & Barberman', 'desc' => 'Daftarkan paket potong rambut, shaving, coloring, serta nama barberman dan formula bagi hasil masing-masing.'],
                ['step' => '2', 'title' => 'Input Transaksi Kursi Pangkas Cepat', 'desc' => 'Pilih barberman yang melayani, tambahkan produk grooming tambahan, dan terima bayar tunai atau QRIS dinamis.'],
                ['step' => '3', 'title' => 'Bagi Hasil Terhitung Tanpa Sengketa', 'desc' => 'Laporan komisi per kapster langsung tercatat dan siap dicairkan secara mingguan atau bulanan dengan presisi.'],
            ],
            'faqs' => [
                ['q' => 'Apakah sistem ini menghitung komisi pangkas rambut per kepala potong secara otomatis?', 'a' => 'Ya, setiap transaksi yang diasosiasikan dengan nama barberman akan langsung menghitung nominal komisi bagi hasil secara otomatis.'],
                ['q' => 'Bagaimana cara barberman mengecek hasil pangkas rambut mereka setiap hari?', 'a' => 'Sistem menyediakan rekap ringkas harian yang menampilkan jumlah transaksi per barberman sehingga tidak ada salah paham pembagian hasil.'],
                ['q' => 'Apakah kasir bisa menggabungkan pembayaran jasa potong dan pembelian minyak rambut pomade?', 'a' => 'Tentu bisa. Produk retail grooming dan jasa pangkas langsung terangkum dalam satu nomor struk kasir yang sama.'],
                ['q' => 'Bisakah mencetak struk kasir dengan logo barbershop kami?', 'a' => 'Bisa. Struk thermal Bluetooth dapat memuat nama barbershop, alamat, logo, nomor WhatsApp, hingga pesan terima kasih.'],
                ['q' => 'Apakah COOCA bisa digunakan di tablet yang ditaruh di meja resepsionis?', 'a' => 'Sangat cocok. Antarmuka kasir dirancang dengan tombol besar dan modern yang sangat nyaman dioperasikan di tablet Android maupun iPad.'],
            ],
            'parent_solution' => [
                'url' => '/solutions/services',
                'title' => 'Aplikasi Booking & Invoice Jasa',
                'desc' => 'Untuk studio grooming pria dengan jadwal appointment booking dan multi-cabang. Lihat solusi bisnis jasa.',
            ],
            'testimonial' => [
                'quote' => 'Semua barberman puas karena sistemnya transparan. Tidak ada lagi saling tuduh salah hitung komisi harian.',
                'author' => 'Aldo Pramono',
                'business' => 'The Gentleman Barbershop, Semarang'
            ]
        ],
        'kasir-irt' => [
            'slug' => 'kasir-irt',
            'title' => 'Aplikasi Usaha Rumahan & Produsen Snack',
            'badge' => 'Produsen IRT',
            'headline' => 'Aplikasi Usaha Rumahan: HPP Produksi & Konsinyasi',
            'subheadline' => 'Aplikasi usaha rumahan dan produsen snack IRT. Hitung HPP modal bahan baku per kemasan, kelola barang titip jual konsinyasi di warung mitra, dan pantau laba riil tanpa tekor.',
            'pain_points' => [
                'Merasa jualan camilan laku keras ratusan bungkus, tapi uang modal selalu habis tidak tahu lari ke mana.',
                'Barang titip jual (konsinyasi) di toko oleh-oleh dan warung mitra sering tidak jelas rekapan uang dan retur barangnya.',
                'Kenaikan harga tepung, minyak goreng, dan kemasan standing pouch membuat harga jual rugi tanpa disadari.'
            ],
            'features' => [
                ['title' => 'Kalkulator HPP Produksi Resep Makanan', 'desc' => 'Hitung modal bersih per toples atau bungkus snack hingga detail bahan baku, bumbu, plastik standing pouch, dan stiker label.'],
                ['title' => 'Manajemen Titip Jual (Konsinyasi)', 'desc' => 'Catat berapa bungkus produk yang dititipkan ke warung mitra, berapa yang laku tertagih, dan sisa produk yang diretur.'],
                ['title' => 'Tier Harga Reseller & Agen Grosir', 'desc' => 'Atur skema harga bertingkat otomatis: harga beli eceran konsumen, harga reseller, dan harga agen distributor.'],
                ['title' => 'Laporan Laba Kotor per Varian Rasa', 'desc' => 'Ketahui varian snack mana yang mendatangkan keuntungan terbesar bagi dapur usaha rumahan Anda.'],
            ],
            'how_it_works' => [
                ['step' => '1', 'title' => 'Rinci Resep Bahan Baku & Kemasan', 'desc' => 'Masukkan gramatur bahan (tepung, gula, minyak) beserta biaya kemasan pouch dan stiker untuk membentuk HPP standar.'],
                ['step' => '2', 'title' => 'Catat Pengiriman Konsinyasi ke Mitra', 'desc' => 'Buat surat jalan titip jual barang ke toko oleh-oleh atau warung rekanan lengkap dengan jatuh tempo tagihan.'],
                ['step' => '3', 'title' => 'Rekonsiliasi Retur & Laba Bersih', 'desc' => 'Input jumlah barang laku dan barang retur saat kunjungan penagihan. Laba bersih per mitra langsung terhitung otomatis.'],
            ],
            'faqs' => [
                ['q' => 'Bagaimana cara menghitung HPP snack rumahan hingga ke kemasan dan gas?', 'a' => 'Fitur resep produksi COOCA memungkinkan Anda memasukkan bahan baku, kemasan standing pouch, stiker label, hingga alokasi biaya gas per batch masak.'],
                ['q' => 'Apakah ada fitur untuk melacak barang konsinyasi yang dititipkan di toko mitra?', 'a' => 'Ya, Anda dapat mencatat jumlah stok titipan di setiap toko mitra, memantau produk yang sudah laku, dan mencatat retur barang kadaluarsa.'],
                ['q' => 'Bagaimana jika harga bahan baku di pasar naik secara mendadak?', 'a' => 'Cukup perbarui harga beli bahan baku di sistem, HPP otomatis terhitung ulang sehingga Anda dapat menyesuaikan harga jual sebelum mengalami kerugian.'],
                ['q' => 'Bisakah membedakan harga eceran dan harga khusus reseller?', 'a' => 'Bisa, Anda dapat membuat tingkatan harga: harga konsumen langsung, harga reseller, dan harga agen distributor skala grosir.'],
                ['q' => 'Apakah aplikasi ini cocok untuk usaha skala dapur rumahan yang belum berbadan usaha?', 'a' => 'Sangat cocok. Bahasa sistem dibuat sederhana tanpa istilah akuntansi rumit, siap dipakai siapa saja yang ingin usahanya naik kelas.'],
            ],
            'parent_solution' => [
                'url' => '/solutions/manufacturing',
                'title' => 'Software Produksi & Manufaktur UMKM',
                'desc' => 'Untuk konveksi, garmen, dan pabrik olahan makanan dengan work order dan alokasi mesin. Pelajari software manufaktur.',
            ],
            'testimonial' => [
                'quote' => 'Dulu asal tembak harga jual kue kering Rp 35.000 per toples. Setelah hitung HPP di Cooca, ternyata modalnya Rp 28.000! Untung cepat sadar sebelum rugi besar.',
                'author' => 'Ibu Ratna Dewi',
                'business' => 'Dapur Kue Mama Ratna, Solo'
            ]
        ],
        'kasir-apotek' => [
            'slug' => 'kasir-apotek',
            'title' => 'Aplikasi Kasir Apotek & Toko Obat',
            'badge' => 'Apotek & Toko Obat',
            'headline' => 'Aplikasi Kasir Apotek: Kelola Stok Obat & Kedaluwarsa',
            'confirmation_note' => 'Integrasi langsung API SATUSEHAT Kemenkes dan pelaporan BPOM e-Prescription saat ini belum tersedia secara native di codebase, hubungi tim kami untuk konsultasi integrasi khusus enterprise',
            'subheadline' => 'Aplikasi kasir apotek dan toko obat modern. Kelola nomor batch obat, pantau tanggal kedaluwarsa secara otomatis dengan FEFO, scan barcode BPOM, dan konversi multi-satuan tablet, strip, botol, hingga box.',
            'pain_points' => [
                'Kerugian besar akibat obat kadaluarsa yang menumpuk di rak tanpa terdeteksi sebelum tanggal jatuh tempo.',
                'Kerepotan mengelola konversi satuan obat eceran: kulakan 1 box isi 10 strip, tiap strip isi 10 tablet, sering selisih fisik saat stok opname.',
                'Pencatatan resep dokter dan riwayat obat langganan pasien masih manual di kartu stok kertas yang mudah robek atau hilang.',
                'Sulit memisahkan persediaan obat bebas (OTC), obat keras (Daftar G), dan obat generik secara sistematis.'
            ],
            'features' => [
                ['title' => 'Manajemen Batch & Kedaluwarsa (FEFO)', 'desc' => 'Setiap penerimaan barang mencatat nomor batch dan expired date. Sistem otomatis merekomendasikan penjualan obat dengan First Expired First Out (FEFO) agar tidak kedaluwarsa di rak.'],
                ['title' => 'Konversi Satuan Obat Multi-Tier', 'desc' => 'Beli per box/karton dari Pedagang Besar Farmasi (PBF), jual ke pasien dalam satuan botol, strip, keping, atau butir tablet secara otomatis tanpa selisih hitung.'],
                ['title' => 'Scan Barcode Cepat & Cetak Etiket', 'desc' => 'Dukung barcode scanner thermal USB/Bluetooth dan cetak etiket aturan pakai obat pasien (sehari 3x setelah makan) langsung dari printer kasir.'],
                ['title' => 'Peringatan Dini Stok Kritis & Kadaluwarsa', 'desc' => 'Dashboard pintar memberikan notifikasi visual warna kuning (kurang dari 90 hari) dan merah (kurang dari 30 hari) sebelum obat memasuki masa expired.'],
            ],
            'how_it_works' => [
                ['step' => '1', 'title' => 'Input Master Obat & Batch PBF', 'desc' => 'Daftarkan nama obat generik/paten, tetapkan satuan terkecil (tablet/strip), dan catat nomor batch serta tanggal kedaluwarsa dari faktur PBF.'],
                ['step' => '2', 'title' => 'Transaksi Kasir & Cetak Etiket', 'desc' => 'Scan barcode obat di kasir, pilih aturan pakai obat pasien, dan terima pembayaran tunai, debit, atau QRIS dinamis secara instan.'],
                ['step' => '3', 'title' => 'Kartu Stok & Laporan Laba Bersih', 'desc' => 'Sistem otomatis memperbarui kartu stok digital obat dan mencatat margin laba kotor harian apotek tanpa rekap pembukuan manual.'],
            ],
            'faqs' => [
                ['q' => 'Apakah sistem kasir apotek COOCA mendukung metode FEFO (First Expired First Out)?', 'a' => 'Ya, sistem otomatis memprioritaskan penjualan obat dari batch dengan tanggal kedaluwarsa paling dekat untuk meminimalkan risiko obat expired di apotek Anda.'],
                ['q' => 'Bisakah apotek menjual obat eceran per butir atau per strip dari kemasan box?', 'a' => 'Bisa. Fitur multi-satuan COOCA otomatis mengonversi stok dari box ke strip dan tablet butiran secara matematis tanpa selisih fisik.'],
                ['q' => 'Bagaimana sistem memberi tahu jika ada obat yang mendekati masa expired?', 'a' => 'Sistem menyediakan filter laporan stok kedaluwarsa 30, 60, dan 90 hari ke depan, serta indikator visual pada master data obat.'],
                ['q' => 'Apakah bisa mencetak label etiket obat untuk pasien?', 'a' => 'Bisa, etiket nama pasien, tanggal peresepan, dan dosis aturan minum dapat langsung dicetak ke printer thermal 58mm atau 80mm.'],
                ['q' => 'Apakah aplikasi kasir apotek ini terintegrasi dengan SATUSEHAT atau BPOM?', 'a' => '<!-- KONFIRMASI: Integrasi langsung API SATUSEHAT Kemenkes dan pelaporan BPOM e-Prescription saat ini dalam roadmap rilis lanjutan, hubungi tim kami untuk konsultasi integrasi khusus enterprise --> COOCA fokus pada manajemen kasir, inventori batch FEFO, dan pembukuan apotek mandiri.'],
            ],
            'parent_solution' => [
                'url' => '/solutions/retail',
                'title' => 'Aplikasi Kasir Retail & Minimarket',
                'desc' => 'Pelajari fitur lengkap barcode scanner multi-kasir dan stok multi-gudang pada solusi retail.',
            ],
            'testimonial' => [
                'quote' => 'Dulu sering tekor puluhan juta karena obat sirup dan antibiotik kedaluwarsa di pojok gudang. Sejak pakai batch FEFO Cooca, stok yang hampir expired selalu terdeteksi 3 bulan sebelumnya.',
                'author' => 'apt. Hendra Kusuma, S.Farm',
                'business' => 'Apotek Sehat Sentosa, Surabaya'
            ]
        ],
        'aplikasi-catering' => [
            'slug' => 'aplikasi-catering',
            'title' => 'Aplikasi Catering & Usaha Prasmanan',
            'badge' => 'Catering & Prasmanan',
            'headline' => 'Aplikasi Catering: Kelola Pesanan, DP & HPP Porsi',
            'subheadline' => 'Aplikasi usaha catering rumahan hingga prasmanan event skala besar. Hitung HPP bahan baku per porsi/kotak, atur jadwal pengiriman pesanan, kirim invoice termin DP bertahap ke WhatsApp, dan pantau laba riil per acara.',
            'pain_points' => [
                'Pesanan pesanan nasi box bentrok atau jadwal kirim terlewat karena pencatatan masih di buku kalender dinding manual.',
                'Penetapan harga per porsi sering tebak-tebakan sehingga saat harga ayam, cabai, atau minyak goreng melonjak, catering justru nombok.',
                'Pelanggan sering menunda pelunasan sisa tagihan pesta pernikahan atau arisan karena penagihan tidak rapi.',
                'Kesulitan mengalokasikan stok bahan baku dapur untuk 3 pesanan acara berbeda yang jatuh pada hari yang sama.'
            ],
            'features' => [
                ['title' => 'Kalkulator HPP Porsi & Resep Masakan', 'desc' => 'Hitung modal riil per kotak nasi atau porsi prasmanan hingga ke bumbu dapur, gas, dus kemasan, sendok, dan biaya tenaga masak. Pelajari juga panduan kami tentang [HPP Catering per Porsi](/blog/hpp-catering-per-porsi-vs-laba).'],
                ['title' => 'Jadwal Pesanan & Kalender Pengiriman Event', 'desc' => 'Pantau timeline pesanan catering berdasarkan tanggal dan jam acara untuk memastikan tim dapur tidak kewalahan atau salah waktu pengiriman.'],
                ['title' => 'Invoice DP & Pelunasan Bertahap via WhatsApp', 'desc' => 'Terbitkan invoice profesional dengan skema termin pembayaran uang muka (DP 30%, DP 50%, dan pelunasan H-1) dengan kirim nota WhatsApp otomatis.'],
                ['title' => 'Rekap Kebutuhan Belanja Bahan Baku Otomatis', 'desc' => 'Punya 3 orderan dengan total 500 porsi? Sistem otomatis mengakumulasi total belanja beras, ayam, dan sayur yang harus dibeli ke pasar.'],
            ],
            'how_it_works' => [
                ['step' => '1', 'title' => 'Susun Paket Menu & Formula Resep', 'desc' => 'Input paket catering (Paket Nasi Kuning, Prasmanan Silver, Coffee Break) beserta takaran gramatur bahan baku per porsi.'],
                ['step' => '2', 'title' => 'Catat Order Acara & Terima DP', 'desc' => 'Masukkan nama pemesan, lokasi acara, tanggal & jam pengiriman, jumlah porsi, serta terima pembayaran termin DP pertama.'],
                ['step' => '3', 'title' => 'Eksekusi Dapur & Pelunasan Tagihan', 'desc' => 'Cetak daftar belanja pasar per batch event. Kirim pengingat pelunasan via WhatsApp dan catat laba bersih acara secara instan.'],
            ],
            'faqs' => [
                ['q' => 'Bagaimana cara menghitung HPP per porsi catering agar tidak rugi?', 'a' => 'COOCA menyediakan fitur Bill of Materials (BOM) resep. Anda cukup memasukkan komponen beras, lauk utama, sayur, bumbu, kemasan kotak, dan biaya gas untuk menghasilkan HPP otomatis.'],
                ['q' => 'Bisakah mengatur sistem pembayaran DP bertahap (termin)?', 'a' => 'Bisa. Anda dapat mencatat pembayaran DP pertama (misalnya 30%), mencatat termin kedua, hingga sisa pelunasan saat pesanan diserahkan ke pelanggan.'],
                ['q' => 'Apakah invoice catering bisa dikirimkan langsung ke nomor WhatsApp pelanggan?', 'a' => 'Ya, invoice PDF dan ringkasan nota digital dapat dikirimkan ke WhatsApp pemesan dengan format resmi berlogo bisnis catering Anda.'],
                ['q' => 'Bagaimana jika ada pesanan catering yang membutuhkan kustomisasi menu dari pelanggan?', 'a' => 'Sistem sangat fleksibel. Anda dapat menambahkan menu tambahan (add-on), mengganti lauk, atau menyesuaikan harga per porsi secara khusus per pesanan.'],
                ['q' => 'Apakah aplikasi catering ini terhubung ke laporan laba rugi bulanan?', 'a' => 'Ya, seluruh pendapatan pesanan dan pengeluaran belanja bahan dapur otomatis masuk ke laporan laba rugi dan arus kas usaha Anda.'],
            ],
            'parent_solution' => [
                'url' => '/solutions/fnb',
                'title' => 'Aplikasi Kasir Restoran & F&B',
                'desc' => 'Untuk cafe, resto, dan kuliner dine-in dengan fitur meja dan tiket dapur KOT. Lihat solusi F&B.',
            ],
            'testimonial' => [
                'quote' => 'Waktu dapat order 1.200 box seminar, dulu kami stres hitung belanja ayam dan cabai di kertas. Pakai Cooca, total gramatur bahan langsung keluar dan invoice DP termin rapi banget.',
                'author' => 'Ibu Hj. Nurjanah',
                'business' => 'Nurjanah Berkah Catering, Bekasi'
            ]
        ],
    ];

    /**
     * Show specific vertical solution page.
     */
    public function show(string $slug): \Illuminate\View\View|\Illuminate\Http\RedirectResponse
    {
        if ($slug === 'kasir-laundry') {
            return redirect('/solutions/laundry', 301);
        }
        if ($slug === 'kasir-bengkel-kecil') {
            return redirect('/solutions/workshop', 301);
        }

        abort_unless(isset($this->solutions[$slug]), 404);

        return view('public.solutions.show', [
            'solution' => $this->solutions[$slug],
            'otherSolutions' => collect($this->solutions)->where('slug', '!=', $slug)->take(4),
        ]);
    }
}
