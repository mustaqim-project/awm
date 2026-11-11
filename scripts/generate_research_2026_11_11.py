# scripts/generate_research_2026_11_11.py
import json, os

def generate_research():
    research_data = {
        "date": "2026-11-11",
        "batch": 7,
        "geo": "ID",
        "category": "3",
        "category_name": "Bisnis, Keuangan, Operasional Ritel & E-Commerce Harbolnas",
        "source": "Google Trends Indonesia 11 November 2026, DJP CoreTax & PPh Final 15 Nov, Harbolnas 11.11 Dynamics",
        "total_topics": 20,
        "topics": [
            {
                "id": 352,
                "topic": "Strategi Rekonsiliasi Keuangan Pasca 11.11: Menghindari Jebakan Omzet Semu dan Saldo Tertahan Marketplace",
                "trending_query": "strategi rekonsiliasi keuangan pasca 11.11 toko online",
                "primary_keyword": "strategi rekonsiliasi keuangan pasca 11.11",
                "secondary_keywords": "cara rekonsiliasi saldo marketplace, jebakan omzet semu pesta diskon, audit biaya admin iklan marketplace, mitigasi kerugian retur cod paket, cara menjaga arus kas setelah harbolnas",
                "search_intent": "informational",
                "search_interest": "Sangat Tinggi",
                "trending_reason": "Puncak belanja 11.11 menghasilkan lonjakan omzet di dashboard toko online, namun seller menghadapi jeda pencairan saldo (cash drag) 3-7 hari, potongan komisi platform, beban iklan, dan ancaman lonjakan retur COD.",
                "credible_sources": [
                    "Asosiasi E-Commerce Indonesia (idEA)",
                    "Ikatan Akuntan Indonesia (IAI)",
                    "Data Transaksi Harbolnas Nasional",
                    "Pedoman Manajemen Keuangan Ritel Digital"
                ],
                "msme_opportunity": "Menyelamatkan margin keuntungan bersih toko, mencegah kehabisan kas operasional saat kulakan ulang, dan mendeteksi selisih saldo tertahan sejak hari pertama.",
                "article_angle": "Formula laba bersih riil pasca diskon, 4 tahapan audit saldo dompet marketplace versus rekening bank, mitigasi biaya retur COD, dan pengelolaan kas darurat.",
                "slug": "strategi-rekonsiliasi-keuangan-pasca-11-11-umkm",
                "assigned_category": "Keuangan & Pembukuan"
            },
            {
                "id": 353,
                "topic": "Cara Mengaudit Biaya Admin dan Komisi Layanan Marketplace yang Membengkak Saat Flash Sale",
                "trending_query": "cara audit biaya admin komisi marketplace seller",
                "primary_keyword": "cara audit biaya admin marketplace",
                "secondary_keywords": "biaya layanan transaksi platform e-commerce, komisi program gratis ongkir ekstra, potongan pph 22 marketplace, audit selisih saldo penghasilan toko, simulasi potongan biaya penjualan online",
                "search_intent": "informational",
                "search_interest": "Tinggi",
                "trending_reason": "Banyak seller kaget melihat potongan komisi marketplace mencapai 12 hingga 18 persen per pesanan akibat akumulasi biaya admin reguler, gratis ongkir ekstra, dan biaya layanan flash sale.",
                "credible_sources": ["Ketentuan Layanan Seller Marketplace Nasional", "Riset Biaya Operasional E-Commerce"],
                "msme_opportunity": "Mengetahui persentase potongan riil per kategori produk agar penetapan harga jual promo tidak menghasilkan kerugian terselubung.",
                "article_angle": "Daftar rincian biaya potongan marketplace, rumus audit laba bersih per pesanan, dan teknik menetapkan batas minimal margin produk diskon.",
                "slug": "cara-audit-biaya-admin-marketplace-flash-sale",
                "assigned_category": "Keuangan"
            },
            {
                "id": 354,
                "topic": "SOP Penanganan Retur COD Massal dan Barang Rusak Ekspedisi Pasca Kampanye 11.11",
                "trending_query": "sop penanganan retur cod massal seller",
                "primary_keyword": "sop penanganan retur cod massal",
                "secondary_keywords": "mitigasi return to seller rts kurir, sop unboxing video klaim ekspedisi, pencatatan stok barang retur rusak, cara verifikasi pesanan cod fiktif, kompensasi ganti rugi barang hilang logistik",
                "search_intent": "informational",
                "search_interest": "Sangat Tinggi",
                "trending_reason": "Gelombang pesanan COD yang gagal antar (RTS) mulai kembali ke gudang penjual 3 hingga 10 hari pasca kampanye belanja, dengan risiko kemasan sobek, barang hilang, atau produk ditukar oknum kurir.",
                "credible_sources": ["Asosiasi Logistik Indonesia (ALI)", "Standar Perlindungan Penjual Online"],
                "msme_opportunity": "Menekan kerugian akibat barang rusak hingga di bawah 1 persen dengan rekaman unboxing komprehensif dan klaim asuransi ekspedisi tepat waktu.",
                "article_angle": "Alur 5 langkah verifikasi barang retur masuk, template berita acara klaim ke logistik, dan cara mengembalikan status stok yang masih layak jual ke inventori sistem.",
                "slug": "sop-penanganan-retur-cod-massal-dan-barang-rusak",
                "assigned_category": "Operasional"
            },
            {
                "id": 355,
                "topic": "Strategi Mengubah Pembeli Promo 11.11 Menjadi Pelanggan Loyal via Broadcast WhatsApp",
                "trending_query": "cara ubah pembeli promo 11.11 jadi pelanggan loyal whatsapp",
                "primary_keyword": "strategi mengubah pembeli promo jadi pelanggan loyal",
                "secondary_keywords": "re-engagement database pembeli harbolnas, copywriting pesan terima kasih whatsapp, voucher repeat order belanja berikutnya, segmentasi pembeli first-time buyer toko, etika broadcast whatsapp bisnis",
                "search_intent": "informational",
                "search_interest": "Tinggi",
                "trending_reason": "Biaya akuisisi pembeli saat kampanye promo sangat mahal. Tanpa tindak lanjut yang tepat, ribuan pembeli 11.11 hanya menjadi pembeli satu kali (one-time buyer) yang hilang tanpa repeat order.",
                "credible_sources": ["Riset Conversational Commerce Indonesia", "Pedoman Kebijakan WhatsApp Business"],
                "msme_opportunity": "Menaikkan retensi pelanggan hingga 25 persen dalam 30 hari pasca 11.11 dengan biaya pemasaran hampir nol rupiah.",
                "article_angle": "Rangkaian pesan pesan otomatis (follow-up sequence) setelah pesanan diterima, penawaran kupon sambut kembali, dan segmentasi nomor WhatsApp kasir POS.",
                "slug": "strategi-mengubah-pembeli-promo-jadi-pelanggan-loyal-whatsapp",
                "assigned_category": "Marketing & WhatsApp"
            },
            {
                "id": 356,
                "topic": "Kepatuhan Setor dan Lapor SPT Masa PPh Final 0,5 Persen Batas Waktu 15 November 2026",
                "trending_query": "batas waktu setor pph final umkm 15 november 2026",
                "primary_keyword": "batas waktu setor pph final 15 november 2026",
                "secondary_keywords": "cara buat kode billing pph final 0.5 persen, denda telat bayar pajak pasal 7 uu kup, rekap omzet bruto bulanan toko, batas omzet tidak kena pajak 500 juta, panduan bayar pajak lewat m-banking",
                "search_intent": "informational",
                "search_interest": "Tinggi",
                "trending_reason": "Batas akhir penyetoran dan pelaporan PPh Final 0,5% masa pajak Oktober jatuh pada tanggal 15 November 2026. Keterlambatan setor dikenai sanksi bunga per bulan dan denda keterlambatan SPT Masa.",
                "credible_sources": ["Direktorat Jenderal Pajak RI", "PP No. 55 Tahun 2022", "UU Harmonisasi Peraturan Perpajakan"],
                "msme_opportunity": "Menjaga reputasi kepatuhan fiskal usaha agar mudah memperoleh fasilitas kredit perbankan dan pembiayaan usaha.",
                "article_angle": "Panduan langkah pembuatan kode billing mandiri melalui DJP Online atau bank persepsi, cara menghitung omzet kena pajak bagi yang melampaui Rp500 juta, dan tips arsip bukti bayar.",
                "slug": "kepatuhan-setor-pph-final-15-november-2026-umkm",
                "assigned_category": "Pajak & Legalitas"
            },
            {
                "id": 357,
                "topic": "Panduan Validasi Data NIK dan NPWP 16 Digit untuk Persiapan Migrasi CoreTax DJP",
                "trending_query": "validasi nik jadi npwp 16 digit coretax djp umkm",
                "primary_keyword": "panduan validasi nik npwp coretax djp",
                "secondary_keywords": "pemadanan data identitas wajib pajak, login coretax akun djp online, npwp 16 digit badan usaha dan cabang, syarat administrasi perbankan dan izin usaha oss, verifikasi data kependudukan dukcapil",
                "search_intent": "informational",
                "search_interest": "Tinggi",
                "trending_reason": "DJP terus mengimbau pelaku usaha untuk segera menyelesaikan pemadanan NIK menjadi NPWP 16 digit sebelum implementasi penuh CoreTax System agar tidak terhambat dalam mengakses layanan publik dan perbankan.",
                "credible_sources": ["Direktorat Jenderal Pajak", "PMK No. 112/PMK.03/2022", "Kementerian Keuangan RI"],
                "msme_opportunity": "Menghindari kendala pemotongan pajak lebih tinggi atau pembekuan akses faktur pajak komersial.",
                "article_angle": "Langkah pemadanan mandiri secara online, pengecekan status validitas NIK, penyesuaian NPWP cabang (NITKU), dan integrasi ke profil bisnis.",
                "slug": "panduan-validasi-nik-npwp-coretax-djp-umkm",
                "assigned_category": "Pajak & Legalitas"
            },
            {
                "id": 358,
                "topic": "Cara Mengatur Alokasi Stok dan Restock Cepat Menjelang Puncak Harbolnas 12.12",
                "trending_query": "cara restock cepat menjelang harbolnas 12.12",
                "primary_keyword": "cara restock cepat menjelang harbolnas 12.12",
                "secondary_keywords": "lead time pengiriman supplier bahan baku, perhitungan reorder point rop gudang, alokasi stok etalase marketplace dan toko offline, mitigasi stockout produk best seller, negosiasi kuota pengiriman pemasok",
                "search_intent": "informational",
                "search_interest": "Tinggi",
                "trending_reason": "Waktu antara 11.11 dan 12.12 hanya berjarak 4 minggu. Keterlambatan melakukan pemesanan ulang (restock) ke pemasok berisiko membuat toko kehabisan produk unggulan saat festival belanja puncak 12.12.",
                "credible_sources": ["Supply Chain Management Institute", "Pedoman Manajemen Inventori E-Commerce"],
                "msme_opportunity": "Memastikan ketersediaan barang seratus persen saat lonjakan permintaan tanpa membebani modal kerja dengan dead stock.",
                "article_angle": "Rumus Reorder Point (ROP) dan Safety Stock berbasis data riil penjualan 11.11, manajemen lead time pengiriman, dan alokasi inventori multi-channel.",
                "slug": "cara-restock-cepat-menjelang-harbolnas-12-12-umkm",
                "assigned_category": "Inventori & Stok"
            },
            {
                "id": 359,
                "topic": "SOP Stock Opname Parsial Siklus Bergilir Tanpa Menutup Operasional Toko Ritel",
                "trending_query": "sop stock opname siklus bergilir toko ritel",
                "primary_keyword": "sop stock opname siklus bergilir toko ritel",
                "secondary_keywords": "metode cycle counting persediaan barang, audit stok harian tanpa tutup toko, barcode scanner inventori rak toko, pencocokan stok fisik kasir pos, pencegahan selisih barang hilang",
                "search_intent": "informational",
                "search_interest": "Tinggi",
                "trending_reason": "Menutup toko seharian untuk stok opname akhir tahun berarti kehilangan omzet penjualan. Metode cycle counting (stok opname siklus harian per rak) memungkinkan audit stok berjalan terus tanpa mengganggu kasir.",
                "credible_sources": ["Asosiasi Pengusaha Ritel Indonesia (Aprindo)", "Standar Pengendalian Internal Persediaan"],
                "msme_opportunity": "Menjaga akurasi data stok di atas 98 persen dan menemukan ketidaksesuaian barang seketika tanpa menutup pintu toko.",
                "article_angle": "Panduan membagi rak toko ke dalam jadwal audit 15 menit setiap pagi, formulir blind count kasir, dan penyesuaian selisih ke sistem POS.",
                "slug": "sop-stock-opname-siklus-bergilir-toko-ritel",
                "assigned_category": "Inventori & Stok"
            },
            {
                "id": 360,
                "topic": "Cara Menghitung Dana Cadangan THR dan Kompensasi Akhir Tahun Karyawan Toko",
                "trending_query": "cara menghitung dana cadangan thr kompensasi karyawan toko",
                "primary_keyword": "cara menghitung dana cadangan thr karyawan toko",
                "secondary_keywords": "provisi beban thr pembukuan bulanan, perhitungan kompensasi pwt permenaker nomor 35 tahun 2021, alokasi kas darurat beban ketenagakerjaan, bonus performa omzet akhir tahun staf toko, manajemen arus kas penggajian",
                "search_intent": "informational",
                "search_interest": "Menengah",
                "trending_reason": "Banyak pemilik toko kelabakan membayar kompensasi kontrak atau persiapan THR karena tidak menyisihkan dana cadangan secara bulanan, yang akhirnya memukul modal kerja usaha.",
                "credible_sources": ["Kementerian Ketenagakerjaan RI", "PP No. 35 Tahun 2021", "Standar Akuntansi Keuangan Entitas Mikro"],
                "msme_opportunity": "Menjaga stabilitas arus kas akhir tahun dan menciptakan hubungan kerja yang harmonis tanpa guncangan keuangan.",
                "article_angle": "Rumus provisi bulanan (1/12 dari gaji bulanan), pencatatan pos kewajiban di neraca toko, dan skema bonus performa berbasis pencapaian omzet.",
                "slug": "cara-menghitung-dana-cadangan-thr-karyawan-toko",
                "assigned_category": "Keuangan & Pembukuan"
            },
            {
                "id": 361,
                "topic": "Strategi Negosiasi Biaya Pengiriman Kargo untuk Lonjakan Pesanan Grosir Akhir Tahun",
                "trending_query": "strategi negosiasi ongkos kirim kargo grosir b2b",
                "primary_keyword": "strategi negosiasi ongkos kirim kargo grosir",
                "secondary_keywords": "tarif kargo per kilogram volume besar, kontrak pengiriman logistik b2b berkala, klaim asuransi muatan kargo darat laut, syarat free ongkir pembelian grosir, efisiensi rantai pasok distributor",
                "search_intent": "informational",
                "search_interest": "Sedang",
                "trending_reason": "Biaya logistik kargo memakan porsi 15 hingga 25 persen dari transaksi grosir B2B luar pulau. Menegosiasikan kontrak khusus dengan ekspedisi kargo memberikan keunggulan kompetitif pada penawaran harga ke mitra.",
                "credible_sources": ["Asosiasi Logistik dan Forwarder Indonesia (ALFI)", "Pusat Riset Rantai Pasok Nasional"],
                "msme_opportunity": "Menawarkan harga jual grosir yang lebih murah ke konsumen daerah tanpa memangkas margin keuntungan bersih usaha.",
                "article_angle": "Taktik negosiasi berbasis komitmen volume bulanan, pemanfaatan layanan kargo consolidated, dan penyusunan klausul ongkir pada surat penawaran harga.",
                "slug": "strategi-negosiasi-ongkos-kirim-kargo-grosir",
                "assigned_category": "Invoice, PO & SO"
            },
            {
                "id": 362,
                "topic": "Cara Mengatasi Cash Drag Akibat Keterlambatan Settlement Saldo E-Commerce",
                "trending_query": "cara mengatasi cash drag saldo marketplace toko",
                "primary_keyword": "cara mengatasi cash drag saldo marketplace",
                "secondary_keywords": "jeda pencairan dompet penjual e-commerce, manajemen likuiditas modal kerja toko online, talangan kas operasional pembelian stok, strategi negosiasi tempo bayar supplier bahan, proyeksi kas masuk harian",
                "search_intent": "informational",
                "search_interest": "Tinggi",
                "trending_reason": "Waktu penyelesaian dana (settlement) yang tertahan hingga status pesanan dinyatakan selesai oleh pembeli atau sistem sering kali memicu krisis likuiditas tunai bagi seller online.",
                "credible_sources": ["Asosiasi Fintech Indonesia", "Pedoman Manajemen Modal Kerja Ritel Digital"],
                "msme_opportunity": "Menjaga kelancaran produksi dan pengiriman harian tanpa bergantung pada pinjaman kilat berbunga tinggi.",
                "article_angle": "Langkah mitigasi cash drag, pemanfaatan fitur penarikan otomatis harian, integrasi catatan kas keluar-masuk, dan pembentukan dana talangan operasional.",
                "slug": "cara-mengatasi-cash-drag-saldo-marketplace",
                "assigned_category": "Keuangan"
            },
            {
                "id": 363,
                "topic": "SOP Pencegahan Selisih Stok Etalase Fisik dan Online saat Jam Sibuk Flash Sale",
                "trending_query": "sop cegah selisih stok toko fisik dan online marketplace",
                "primary_keyword": "sop integrasi stok toko fisik dan online flash sale",
                "secondary_keywords": "mencegah overselling barang etalase toko, sinkronisasi inventori multi channel real time, safety stock kuota pesanan online, alur pemindahan barang pajangan ke meja packing, penalti pembatalan pesanan marketplace",
                "search_intent": "informational",
                "search_interest": "Tinggi",
                "trending_reason": "Seller yang memiliki toko fisik sekaligus berjualan di marketplace kerap mengalami overselling (barang terjual di kasir fisik, namun terlanjur dibeli pelanggan online), memicu penalti berat dari e-commerce.",
                "credible_sources": ["Benchmark Operasional Ritel Omnichannel", "Panduan Seller Perlindungan Performa Toko"],
                "msme_opportunity": "Menghilangkan pembatalan pesanan akibat stok kosong dan memaksimalkan perputaran barang dari semua saluran penjualan.",
                "article_angle": "Teknik pembagian kuota stok (buffer stock allocation), pemanfaatan barcode scanner kasir terpusat, dan SOP komunikasi staf kasir dengan tim admin online.",
                "slug": "sop-integrasi-stok-toko-fisik-dan-online-flash-sale",
                "assigned_category": "Inventori & Stok"
            },
            {
                "id": 364,
                "topic": "Cara Mengukur Return on Ad Spend (ROAS) Riil Kampanye Iklan Marketplace Pasca Diskon",
                "trending_query": "cara hitung roas riil iklan marketplace",
                "primary_keyword": "cara mengukur roas riil iklan marketplace",
                "secondary_keywords": "rumus roas riil bersih potongan biaya, evaluasi efektivitas iklan pencarian toko, perbandingan biaya iklan versus margin produk, jebakan acos advertising cost of sales, optimasi kata kunci iklan berbayar",
                "search_intent": "informational",
                "search_interest": "Tinggi",
                "trending_reason": "Dashboard iklan marketplace sering melaporkan ROAS impresif (misal 8x atau 10x), namun setelah dikurangi diskon produk, fee platform, dan HPP, seller justru mengalami kerugian operasional.",
                "credible_sources": ["Riset Kinerja Pemasaran Digital E-Commerce", "Pedoman Audit Keuangan Periklanan UMKM"],
                "msme_opportunity": "Menghentikan pemborosan saldo iklan pada kata kunci yang tidak menguntungkan dan mengalokasikan anggaran pada produk berkontribusi margin tertinggi.",
                "article_angle": "Rumus perhitungan Net ROAS (laba kotor riil dibagi biaya iklan), perbandingan metrik ROAS vs ACOS, dan checklist evaluasi efisiensi kata kunci iklan berbayar.",
                "slug": "cara-mengukur-roas-riil-iklan-marketplace",
                "assigned_category": "Marketing"
            },
            {
                "id": 365,
                "topic": "Panduan Membuat Promo Bundling Cuci Gudang untuk Habiskan Stok Sebelum Tutup Tahun",
                "trending_query": "ide promo bundling cuci gudang akhir tahun",
                "primary_keyword": "panduan promo bundling cuci gudang akhir tahun",
                "secondary_keywords": "teknik bundling produk cepat dan lambat, formula diskon paket hemat pembeli, strategi likuidasi persediaan dead stock toko, copywriting penawaran clearance sale, pencatatan hpp paket bundling",
                "search_intent": "informational",
                "search_interest": "Sangat Tinggi",
                "trending_reason": "Menjelang Desember, toko ritel dan fesyen wajib mengosongkan stok lama agar tidak menjadi beban pajak dan biaya sewa ruang simpan gudang di tahun depan.",
                "credible_sources": ["Asosiasi Ritel Indonesia", "Prinsip Merchandise Management Ritel Modern"],
                "msme_opportunity": "Mengubah stok barang yang mengendap berbulan-bulan menjadi uang kas segar dengan mempertahankan persepsi nilai produk.",
                "article_angle": "Strategi memasangkan produk unggulan dengan produk lambat laku, kalkulasi margin gabungan, dan pembuatan materi promosi cuci gudang yang elegan tanpa kesan murahan.",
                "slug": "panduan-promo-bundling-cuci-gudang-akhir-tahun",
                "assigned_category": "Marketing"
            },
            {
                "id": 366,
                "topic": "Cara Menghitung HPP Menu Spesial Musim Liburan Natal dan Tahun Baru (Nataru)",
                "trending_query": "cara menghitung hpp menu seasonal nataru kafe",
                "primary_keyword": "cara menghitung hpp menu spesial akhir tahun",
                "secondary_keywords": "kalkulasi food cost menu seasonal kafe resto, kenaikan harga bahan baku daging dan keju impor, penentuan porsi gramasi kue kering hampers, margin keuntungan paket hampers nataru, strategi pre-order kuliner akhir tahun",
                "search_intent": "informational",
                "search_interest": "Tinggi",
                "trending_reason": "Menu musiman akhir tahun (seperti hampers, kue kering, steak, atau minuman tematik) memiliki biaya bahan baku khusus dan fluktuasi harga yang tinggi sehingga rentan merugi jika HPP tidak dihitung detail.",
                "credible_sources": ["Asosiasi Pengusaha Kafe dan Restoran Indonesia (Apkrindo)", "Standar Food Costing Perhotelan"],
                "msme_opportunity": "Menangkap lonjakan belanja perayaan akhir tahun dengan paket menu eksklusif yang menghasilkan margin laba bersih di atas 40 persen.",
                "article_angle": "Komponen biaya resep (bahan baku utama, bumbu, kemasan toples/box hampers, kartu ucapan), simulasi kenaikan harga bahan, dan penetapan sistem pembayaran uang muka (DP).",
                "slug": "cara-menghitung-hpp-menu-spesial-akhir-tahun",
                "assigned_category": "HPP & Harga Jual"
            },
            {
                "id": 367,
                "topic": "SOP Rekonsiliasi Pembayaran QRIS Dinamis dan EDC Bank untuk Kasir Toko Ritel",
                "trending_query": "sop rekonsiliasi qris dinamis edc kasir toko",
                "primary_keyword": "sop rekonsiliasi pembayaran qris dan edc kasir",
                "secondary_keywords": "pencocokan settlement harian qris bank, cara atasi struk edc keluar tapi saldo belum masuk, pencegahan fraud qr statis kasir toko, format lembar tutup kasir closing pos, waktu pencairan dana merchant mdr",
                "search_intent": "informational",
                "search_interest": "Tinggi",
                "trending_reason": "Tingginya transaksi non-tunai di meja kasir saat musim belanja menuntut pencocokan harian antara struk mesin EDC/QRIS dengan mutasi rekening bank merchant guna mencegah dana tersangkut atau penipuan bukti transfer.",
                "credible_sources": ["Bank Indonesia (bi.go.id)", "Asosiasi Sistem Pembayaran Indonesia (ASPI)"],
                "msme_opportunity": "Memastikan seratus persen dana transaksi non-tunai masuk tepat waktu ke rekening usaha tanpa ada selisih yang terlewat.",
                "article_angle": "Langkah proses settlement pada mesin EDC saat closing shift, rekonsiliasi mutasi QRIS dinamis di aplikasi kasir POS, dan tata cara eskalasi jika terjadi transaksi gantung.",
                "slug": "sop-rekonsiliasi-pembayaran-qris-dan-edc-kasir",
                "assigned_category": "Kasir & POS"
            },
            {
                "id": 368,
                "topic": "Strategi Mengamankan Pasokan Bahan Baku Kuliner Menghadapi Kenaikan Harga Musim Hujan",
                "trending_query": "strategi pasokan bahan baku kuliner musim hujan",
                "primary_keyword": "strategi pasokan bahan baku kuliner musim hujan",
                "secondary_keywords": "kenaikan harga cabai bawang sayuran musim hujan, cara simpan bahan pangan segar cold storage, kontrak harga grosir pemasok bahan baku, substitusi bahan baku resep tanpa ubah rasa, manajemen stok penyangga kuliner",
                "search_intent": "informational",
                "search_interest": "Tinggi",
                "trending_reason": "Curah hujan tinggi di bulan November dan Desember secara historis memicu gagal panen komoditas bumbu dapur (cabai, bawang, sayur segar), yang berakibat pada lonjakan harga bahan baku hingga 50 persen.",
                "credible_sources": ["Badan Pangan Nasional (Bapanas)", "Kementerian Pertanian RI", "Asosiasi Pedagang Pasar Tradisional"],
                "msme_opportunity": "Menjaga stabilitas harga jual menu kuliner dan mencegah kelangkaan bahan baku di tengah gejolak pasar musiman.",
                "article_angle": "Metode kontrak pasokan tetap dengan petani/distributor lokal, teknik pengolahan pasta bumbu untuk penyimpanan beku, dan penyesuaian porsi resep secara ilmiah.",
                "slug": "strategi-pasokan-bahan-baku-kuliner-musim-hujan",
                "assigned_category": "HPP & Harga Jual"
            },
            {
                "id": 369,
                "topic": "Cara Mengelola Order B2B Tempo Net-30 Agar Tidak Macet di Periode Tutup Buku",
                "trending_query": "cara kelola invoice order b2b tempo net-30 tutup buku",
                "primary_keyword": "cara mengelola invoice tempo b2b tutup buku",
                "secondary_keywords": "syarat pemberian tempo pembayaran pelanggan korporat, batas kredit limit mitra bisnis b2b, klausul sanksi keterlambatan pelunasan faktur, penagihan terjadwal sebelum cut-off keuangan desember, surat perjanjian kerja sama dagang",
                "search_intent": "informational",
                "search_interest": "Sedang",
                "trending_reason": "Banyak perusahaan dan klien korporat mempercepat tanggal cut-off pembayaran tagihan di pertengahan Desember untuk tutup buku tahunan, sehingga faktur UMKM yang terlambat diajukan berisiko tertunda hingga Januari atau Februari.",
                "credible_sources": ["Kamar Dagang dan Industri Indonesia (KADIN)", "Prinsip Manajemen Kredit Komersial B2B"],
                "msme_opportunity": "Mempercepat pencairan piutang dagang hingga 100 persen sebelum tanggal penutupan buku tahunan klien korporat.",
                "article_angle": "Jadwal penagihan proaktif November-Desember, syarat kelengkapan dokumen BAST dan faktur pajak, serta taktik insentif pelunasan cepat (early payment discount).",
                "slug": "cara-mengelola-invoice-tempo-b2b-tutup-buku",
                "assigned_category": "Invoice, PO & SO"
            },
            {
                "id": 370,
                "topic": "SOP Mitigasi Paket Tertukar dan Salah Resi Kurir pada Gelombang Pengiriman Besar",
                "trending_query": "sop mitigasi paket tertukar salah resi kurir e-commerce",
                "primary_keyword": "sop mitigasi paket tertukar salah resi kurir",
                "secondary_keywords": "verifikasi barcode ganda scanning resi ekspedisi, pemisahan zona ekspedisi j&t si cepat jne anteraja, pencegahan komplain pembeli terima barang salah, pelatihan staf gudang packing seasonal, sistem penomoran keranjang pesanan",
                "search_intent": "informational",
                "search_interest": "Sangat Tinggi",
                "trending_reason": "Di tengah tumpukan ribuan paket saat kampanye belanja, salah menempelkan label resi atau kurir salah mengambil karung pengiriman merupakan sumber komplain bintang satu terbesar bagi toko online.",
                "credible_sources": ["Standar Operasional Fulfillment Pergudangan", "Data Asosiasi Perusahaan Jasa Pengiriman Ekspres"],
                "msme_opportunity": "Mempertahankan tingkat akurasi pengiriman 99,9 persen dan menjaga reputasi toko bintang lima di marketplace.",
                "article_angle": "Alur sistem scan barcode ganda sebelum paket masuk karung, tata letak area penyerahan logistik per kurir ekspedisi, dan prosedur investigasi paket tertukar.",
                "slug": "sop-mitigasi-paket-tertukar-salah-resi-kurir",
                "assigned_category": "Operasional"
            },
            {
                "id": 371,
                "topic": "Cara Menyusun Laporan Laba Rugi Sederhana Usaha Mikro untuk Evaluasi Akhir Tahun",
                "trending_query": "cara buat laporan laba rugi sederhana usaha mikro akhir tahun",
                "primary_keyword": "cara menyusun laporan laba rugi usaha mikro",
                "secondary_keywords": "format laporan keuangan sederhana sak emkm, pemisahan pendapatan operasional dan non-operasional, penghitungan beban pokok penjualan barang dagang, evaluasi persentase margin laba bersih toko, persiapan laporan tahunan spt pajak",
                "search_intent": "informational",
                "search_interest": "Tinggi",
                "trending_reason": "Menjelang akhir tahun, pengusaha mikro membutuhkan potret nyata apakah usaha mereka benar-benar menghasilkan keuntungan bersih setelah memperhitungkan seluruh biaya riil dan depresiasi alat.",
                "credible_sources": ["Ikatan Akuntan Indonesia (SAK EMKM)", "Kementerian Koperasi dan UKM RI"],
                "msme_opportunity": "Mengetahui secara transparan pos biaya yang boros, menetapkan target omzet tahun baru secara realistis, dan mempermudah pengisian SPT Tahunan Badan atau Orang Pribadi.",
                "article_angle": "Struktur 4 bagian laporan laba rugi sederhana, contoh tabel angka rupiah toko kelontong atau kafe, cara menghitung margin kotor vs bersih, dan tips evaluasi performa bisnis.",
                "slug": "cara-menyusun-laporan-laba-rugi-usaha-mikro",
                "assigned_category": "Keuangan & Pembukuan"
            }
        ]
    }

    # Save to database/data/articles/trending-research-2026-11-11.json
    out_dir = os.path.join(os.path.dirname(__file__), '..', 'database', 'data', 'articles')
    os.makedirs(out_dir, exist_ok=True)
    out_path = os.path.join(out_dir, 'trending-research-2026-11-11.json')
    with open(out_path, 'w', encoding='utf-8') as f:
        json.dump(research_data, f, ensure_ascii=False, indent=2)
    print(f"Generated {out_path} with {len(research_data['topics'])} topics.")

    # Save keyword-research-2026-11-11.json
    keyword_data = {
        "research_date": "2026-11-11",
        "batch": 7,
        "timezone": "Asia/Jakarta",
        "platform": "Cooca (cooca.id)",
        "total_articles": len(research_data['topics']),
        "target_audience": [
            "Seller online dan toko ritel yang mengelola lonjakan omzet 11.11 dan butuh rekonsiliasi kas riil pasca diskon",
            "Pelaku UMKM yang ingin mengaudit potongan komisi platform dan biaya iklan tersembunyi agar tidak boncos",
            "Manajer gudang toko e-commerce yang menata SOP retur COD massal dan klaim barang rusak ekspedisi",
            "Pebisnis digital yang mengonversi pembeli diskon musiman menjadi pelanggan setia melalui WhatsApp sequence",
            "Pengusaha mikro yang bersiap memenuhi batas akhir setor PPh Final 0,5% tanggal 15 November 2026 secara tertib",
            "Wajib pajak UMKM yang memvalidasi NIK menjadi NPWP 16 digit untuk integrasi sistem CoreTax DJP",
            "Pengelola toko online yang menyusun jadwal pemesanan ulang (restock) cepat menyambut pesta belanja 12.12",
            "Pemilik toko fisik yang menjalankan audit inventori bergilir (cycle counting) tanpa harus menutup gerai",
            "Founder UMKM yang mencadangkan dana kewajiban kompensasi dan THR karyawan secara proporsional",
            "Distributor B2B yang menegosiasikan kontrak pengiriman kargo murah untuk pesanan grosir akhir tahun",
            "Pebisnis ritel online yang mengatasi masalah dana tertahan (cash drag) di dompet digital e-commerce",
            "Toko omnichannel yang menyinkronkan stok etalase toko fisik dan keranjang marketplace secara real-time",
            "Digital marketer toko online yang mengukur rasio pengembalian modal iklan bersih (Net ROAS) sebenarnya",
            "Pengusaha ritel fesyen dan pernak-pernik yang merancang program cuci gudang kreatif sebelum tutup buku",
            "Pebisnis kuliner dan kafe yang mengkalkulasi HPP menu seasonal liburan akhir tahun dan paket hampers",
            "Kasir dan supervisor toko modern yang merekonsiliasi pembayaran QRIS dinamis dan struk EDC bank harian",
            "Pengusaha resto dan katering yang mengamankan pasokan dan mengunci harga bahan baku di musim penghujan",
            "Penyedia barang dan jasa B2B yang memperketat penagihan invoice tempo sebelum penutupan buku korporat",
            "Tim operasional pergudangan yang memverifikasi resi dan mencegah paket tertukar pada lonjakan pengiriman",
            "Pemilik usaha mikro yang menyusun laporan laba rugi tahunan sederhana berbasis standar SAK EMKM"
        ]
    }
    kw_path = os.path.join(out_dir, 'keyword-research-2026-11-11.json')
    with open(kw_path, 'w', encoding='utf-8') as f:
        json.dump(keyword_data, f, ensure_ascii=False, indent=2)
    print(f"Generated {kw_path} successfully.")

if __name__ == '__main__':
    generate_research()
