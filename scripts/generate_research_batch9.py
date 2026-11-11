# scripts/generate_research_batch9.py
import json, os

def generate_research():
    out_dir = os.path.join("database", "data", "articles")
    os.makedirs(out_dir, exist_ok=True)
    
    trending = {
        "date": "2026-11-12",
        "batch": 9,
        "geo": "ID",
        "category": "3",
        "category_name": "Bisnis, Keuangan, Kepatuhan Pajak & Logistik Tutup Tahun UMKM",
        "source": "Google Trends Indonesia 12 November 2026, Evaluasi Pasca 11.11, Regulasi Coretax DJP & SAK EMKM",
        "total_topics": 20,
        "topics": [
            {
                "id": 392,
                "topic": "Evaluasi Net Profit Margin Usaha Pasca 11.11: Cara Membedakan Omzet Semu dan Laba Riil",
                "trending_query": "evaluasi net profit margin pasca 11 11 umkm",
                "primary_keyword": "evaluasi net profit margin pasca 11 11",
                "secondary_keywords": "cara membedakan omzet dan laba bersih umkm, margin laba bersih toko pasca promo harbolnas, rumus net profit margin ritel, biaya tersembunyi admin marketplace, kalkulasi laba riil penjualan",
                "search_intent": "informational",
                "search_interest": "Sangat Tinggi",
                "trending_reason": "Sehari setelah festival belanja 11.11, pemilik usaha mulai mendapati saldo rekening tidak sebanding dengan tingginya omzet kotor akibat potongan komisi, subsidi diskon, dan retur barang.",
                "credible_sources": [
                    "Laporan Bank Indonesia tentang Efisiensi Margin Ritel",
                    "Pedoman Standar Akuntansi Keuangan Entitas Mikro Kecil Menengah (SAK EMKM)"
                ],
                "msme_opportunity": "Menghitung margin keuntungan bersih riil agar pelaku usaha tidak terjebak dalam ilusi omzet besar namun arus kas defisit.",
                "article_angle": "Audit terperinci komponen HPP, biaya komisi marketplace, biaya kemasan, dan pemanfaatan sistem akuntansi terpadu.",
                "slug": "evaluasi-net-profit-margin-pasca-11-11-umkm",
                "assigned_category": "Keuangan & Pembukuan"
            },
            {
                "id": 393,
                "topic": "SOP Audit Potongan Komisi dan Biaya Layanan Marketplace Ritel Pasca Promo Puncak",
                "trending_query": "sop audit potongan komisi biaya layanan marketplace pasca promo",
                "primary_keyword": "sop audit potongan komisi dan biaya marketplace",
                "secondary_keywords": "cara cek rincian potongan shopee tokopedia tiktok shop, biaya administrasi seller marketplace, selisih saldo escrow dan penarikan dana, audit komisi program gratis ongkir xtra, rekonsiliasi biaya layanan penjual",
                "search_intent": "informational",
                "search_interest": "Tinggi",
                "trending_reason": "Banyak seller kaget melihat potongan platform mencapai 8 sampai 15 persen dari nilai transaksi selama kampanye 11.11 tanpa pencatatan yang sistematis.",
                "credible_sources": [
                    "Asosiasi E-Commerce Indonesia (idEA)",
                    "Panduan Kebijakan Biaya Layanan Merchant Digital 2026"
                ],
                "msme_opportunity": "Menemukan selisih potongan yang tidak sesuai kesepakatan dan mengajukan klaim refund biaya layanan ke pihak platform.",
                "article_angle": "Langkah demi langkah mencocokkan laporan settlement marketplace dengan nilai tagihan pesanan asli.",
                "slug": "sop-audit-potongan-komisi-dan-biaya-marketplace",
                "assigned_category": "Keuangan & Pembukuan"
            },
            {
                "id": 394,
                "topic": "Manajemen Klaim Paket Rusak dan Asuransi Pengiriman Ekspedisi bagi Seller Ritel",
                "trending_query": "manajemen klaim paket rusak asuransi ekspedisi pengiriman kurir",
                "primary_keyword": "manajemen klaim paket rusak asuransi ekspedisi",
                "secondary_keywords": "cara klaim ganti rugi paket pecah kurir, syarat klaim asuransi barang marketplace, dokumen berita acara kerusakan paket, batas waktu pengajuan klaim ekspedisi, bukti rekaman video unboxing packing",
                "search_intent": "informational",
                "search_interest": "Tinggi",
                "trending_reason": "Tingginya volume logistik saat promo kembar meningkatkan insiden paket tertindih, basah terkena hujan, dan rusak dalam perjalanan ekspedisi.",
                "credible_sources": [
                    "Asosiasi Perusahaan Jasa Pengiriman Ekspres Indonesia (Asperindo)",
                    "Regulasi Perlindungan Konsumen Logistik Ritel"
                ],
                "msme_opportunity": "Mendapatkan penggantian modal produk 100 persen tanpa perlu menanggung kerugian kerusakan secara mandiri.",
                "article_angle": "Standar operasional dokumentasi penyerahan paket, bukti video packing CCTV, dan draft surat klaim resmi ke customer service logistik.",
                "slug": "manajemen-klaim-paket-rusak-asuransi-ekspedisi",
                "assigned_category": "Inventori & Stok"
            },
            {
                "id": 395,
                "topic": "Batas Akhir Setor PPh Final 0,5 Persen 15 November 2026: Panduan Kepatuhan Pajak UMKM",
                "trending_query": "batas akhir setor pph final 15 november 2026 umkm",
                "primary_keyword": "batas akhir setor pph final 15 november 2026",
                "secondary_keywords": "cara bayar pph final umkm 0 koma 5 persen, kode billing pajak e billing djp, sanksi denda terlambat setor pajak 15 november, batas omzet 500 juta bebas pajak pp 55, panduan ebilling pajak online",
                "search_intent": "informational",
                "search_interest": "Sangat Tinggi",
                "trending_reason": "Tanggal 15 November 2026 adalah batas waktu resmi penyetoran PPh Final Masa Pajak Oktober bagi pelaku UMKM sebelum terkena sanksi bunga administratif.",
                "credible_sources": [
                    "Direktorat Jenderal Pajak (DJP) Kementerian Keuangan RI",
                    "Peraturan Pemerintah Nomor 55 Tahun 2022"
                ],
                "msme_opportunity": "Menjaga riwayat kepatuhan pajak usaha agar tetap bersih dan memenuhi kualifikasi syarat perpanjangan izin serta pembiayaan bank.",
                "article_angle": "Simulasi penghitungan omzet bruto bulanan, pembuatan kode billing KAP KJS 411128-420, dan mekanisme setor instan.",
                "slug": "batas-akhir-setor-pph-final-15-november-2026-umkm",
                "assigned_category": "Pajak & Legalitas"
            },
            {
                "id": 396,
                "topic": "Panduan Penerbitan Faktur Pajak Elektronik Coretax DJP bagi Pelaku Usaha Toko Ritel",
                "trending_query": "faktur pajak elektronik coretax djp toko ritel umkm",
                "primary_keyword": "faktur pajak elektronik coretax djp ritel",
                "secondary_keywords": "cara buat faktur pajak di portal coretax, format faktur pajak gabungan ritel, validasi nik npwp 16 digit pembeli, ketentuan faktur pajak pedagang eceran, integrasi sistem kasir pos coretax",
                "search_intent": "informational",
                "search_interest": "Tinggi",
                "trending_reason": "Transisi menyeluruh ke portal Coretax DJP mewajibkan pedagang eceran dan toko ritel memahami alur faktur pajak elektronik terbaru.",
                "credible_sources": [
                    "Direktorat Jenderal Pajak Coretax System Guideline",
                    "Peraturan Menteri Keuangan Tata Cara Pembuatan Faktur Pajak"
                ],
                "msme_opportunity": "Menerbitkan faktur pajak yang sah secara cepat untuk pembeli korporasi atau instansi tanpa risiko penolakan administrasi.",
                "article_angle": "Panduan login portal Coretax, input data faktur eceran, dan sinkronisasi otomatis dari ringkasan transaksi kasir POS.",
                "slug": "panduan-penerbitan-faktur-pajak-coretax-djp-ritel",
                "assigned_category": "Pajak & Legalitas"
            },
            {
                "id": 397,
                "topic": "Cara Memperpanjang Sertifikat Standar Usaha dan Izin Edar PIRT Sebelum Tutup Tahun",
                "trending_query": "cara memperpanjang izin edar pirt sertifikat standar usaha tutup tahun",
                "primary_keyword": "cara memperpanjang izin edar pirt tutup tahun",
                "secondary_keywords": "syarat perpanjangan sp pirt dinkes, masa berlaku izin pirt makanan olahan, pendaftaran sertifikat standar oss rba, audit higienis sanitasi dapur produksi, integrasi nib dan pirt online",
                "search_intent": "informational",
                "search_interest": "Tinggi",
                "trending_reason": "Banyak izin PIRT produsen makanan dan minuman rumahan yang habis masa berlakunya pada akhir tahun sehingga butuh perpanjangan sebelum audit inspeksi pasar.",
                "credible_sources": [
                    "Badan Pengawas Obat dan Makanan (BPOM)",
                    "Kementerian Kesehatan dan Dinas Kesehatan Kabupaten Kota"
                ],
                "msme_opportunity": "Menjaga kelancaran distribusi produk ke minimarket, supermarket, dan toko oleh-oleh menjelang lonjakan wisata akhir tahun.",
                "article_angle": "Cek masa berlaku nomor izin 15 digit, syarat uji laboratorium pangan, dan alur permohonan digital via SPPIRT BPOM terhubung OSS.",
                "slug": "cara-memperpanjang-izin-edar-pirt-tutup-tahun",
                "assigned_category": "Pajak & Legalitas"
            },
            {
                "id": 398,
                "topic": "Strategi Perencanaan Cash Flow 4 Pekan Menuju Puncak Harbolnas 12.12",
                "trending_query": "strategi perencanaan cash flow 4 pekan harbolnas 12 12 umkm",
                "primary_keyword": "strategi perencanaan cash flow harbolnas 12 12",
                "secondary_keywords": "manajemen arus kas persiapan harbolnas 12 12, dana cadangan modal kerja kulakan barang, alokasi kas stok bahan baku akhir tahun, proyeksi penerimaan kas tempo invoice, menjaga likuiditas usaha ritel",
                "search_intent": "informational",
                "search_interest": "Tinggi",
                "trending_reason": "Tepat 4 pekan menjelang 12.12, pemilik usaha harus menyiapkan pembayaran ke supplier bahan baku tanpa mengganggu pengeluaran rutin operasional.",
                "credible_sources": [
                    "Studi Manajemen Keuangan Ritel Indonesia 2026",
                    "Panduan Arus Kas Likuiditas Usaha Mikro"
                ],
                "msme_opportunity": "Membeli stok dalam jumlah besar dengan harga diskon volume supplier tanpa risiko krisis likuiditas kas.",
                "article_angle": "Matriks arus kas 4 pekan, penentuan batas minimum saldo aman, dan mitigasi penahanan dana payout marketplace.",
                "slug": "strategi-perencanaan-cash-flow-menuju-harbolnas-12-12",
                "assigned_category": "Keuangan & Pembukuan"
            },
            {
                "id": 399,
                "topic": "SOP Penataan Ulang Tata Letak Etalase Toko Ritel Menyambut Belanja Akhir Tahun",
                "trending_query": "sop penataan tata letak etalase toko ritel visual merchandising",
                "primary_keyword": "sop penataan ulang tata letak etalase ritel",
                "secondary_keywords": "visual merchandising toko ritel akhir tahun, jalur lalu lintas pembeli toko fisik, penempatan produk fast moving eye level, display rak kasir impulse buying, penataan pencahayaan etalase produk",
                "search_intent": "informational",
                "search_interest": "Tinggi",
                "trending_reason": "Pergeseran musim belanja liburan akhir tahun menuntut pemilik toko ritel mendesain ulang tata letak barang agar meningkatkan konversi dan kenyamanan belanja.",
                "credible_sources": [
                    "Asosiasi Pengusaha Ritel Indonesia (Aprindo)",
                    "Prinsip Desain Visual Merchandising Modern"
                ],
                "msme_opportunity": "Menaikkan penjualan produk bernilai margin tinggi dan mempercepat putaran stok cuci gudang melalui jalur lalu lintas belanja terarah.",
                "article_angle": "Panduan golden zone rak pajang, penataan meja display promo depan pintu masuk, dan zonasi area antrean kasir.",
                "slug": "sop-penataan-ulang-tata-letak-etalase-toko-ritel",
                "assigned_category": "Strategi & Operasional"
            },
            {
                "id": 400,
                "topic": "Cara Menentukan Anggaran Bonus Prestasi dan Insentif Karyawan Toko UMKM",
                "trending_query": "cara menentukan anggaran bonus insentif karyawan toko ritel umkm",
                "primary_keyword": "cara menentukan anggaran bonus insentif karyawan toko",
                "secondary_keywords": "skema bonus penjualan staf kasir pramuniaga, rumus bagi hasil omzet toko untuk karyawan, insentif target akhir tahun toko ritel, alokasi dana apresiasi tim operasional, motivasi produktivitas kerja karyawan",
                "search_intent": "informational",
                "search_interest": "Sedang Menuju Tinggi",
                "trending_reason": "Menjelang akhir tahun, pemilik usaha perlu merancang skema insentif yang adil untuk memacu semangat tim kasir dan gudang tanpa menggerus laba bersih.",
                "credible_sources": [
                    "Kementerian Ketenagakerjaan RI Regulasi Pengupahan",
                    "Standar Skema Komisi Ritel Modern 2026"
                ],
                "msme_opportunity": "Menekan tingkat turnover staf saat musim ramai dan mendorong karyawan proaktif menawarkan produk upselling.",
                "article_angle": "Tiga model perhitungan insentif berbasis KPI, simulasi persentase laba kotor, dan transparansi laporan dashboard kasir.",
                "slug": "cara-menentukan-anggaran-bonus-insentif-karyawan-toko",
                "assigned_category": "Strategi & Operasional"
            },
            {
                "id": 401,
                "topic": "SOP Kasir Pencegahan Modus Bukti Transfer Palsu dan Penipuan QRIS Statis",
                "trending_query": "sop kasir cegah bukti transfer palsu stiker qris palsu penipuan",
                "primary_keyword": "sop kasir pencegahan bukti transfer palsu qris",
                "secondary_keywords": "modus penipuan bukti struk transfer m banking palsu, cek stiker barcode qris meja kasir, verifikasi notifikasi real time pembayaran pos, sop kasir verifikasi dana masuk rekening, mitigasi kerugian toko kasir ritel",
                "search_intent": "informational",
                "search_interest": "Sangat Tinggi",
                "trending_reason": "Peningkatan transaksi non-tunai saat toko ramai sering dimanfaatkan oknum penipu dengan mengedit screenshot bukti transfer atau menimpa stiker QRIS.",
                "credible_sources": [
                    "Satgas Pemberantasan Aktivitas Keuangan Ilegal",
                    "Panduan Keamanan Transaksi Pedagang Bank Indonesia"
                ],
                "msme_opportunity": "Menghilangkan total potensi kerugian uang hilang atau barang keluar tanpa pembayaran valid di meja kasir.",
                "article_angle": "Aturan pantang lepas barang sebelum notifikasi kasir berbunyi, penggunaan QRIS dinamis di layar POS, dan inspeksi fisik barcode harian.",
                "slug": "sop-kasir-pencegahan-bukti-transfer-palsu-qris",
                "assigned_category": "Kasir & POS"
            },
            {
                "id": 402,
                "topic": "Cara Integrasi Pembayaran QRIS Cross Border Wisatawan Mancanegara bagi UMKM",
                "trending_query": "cara integrasi pembayaran qris cross border wisatawan mancanegara",
                "primary_keyword": "cara integrasi pembayaran qris cross border umkm",
                "secondary_keywords": "terima pembayaran turis singapura malaysia thailand, qris antarnegara merchant umkm, biaya kurs dan fee transaksi qris luar negeri, aplikasi kasir support qris cross border, meningkatkan omzet toko wisata oleh oleh",
                "search_intent": "informational",
                "search_interest": "Tinggi",
                "trending_reason": "Menjelang musim liburan akhir tahun, arus kedatangan wisatawan mancanegara ke kota-kota wisata menuntut merchant UMKM menerima pembayaran QR langsung dari e-wallet asal mereka.",
                "credible_sources": [
                    "Inisiatif Regional Payment Connectivity Bank Indonesia",
                    "Kementerian Pariwisata dan Ekonomi Kreatif RI"
                ],
                "msme_opportunity": "Menjangkau pasar belanja turis tanpa repot menyediakan mesin EDC internasional atau menukar uang tunai valuta asing.",
                "article_angle": "Aktivasi fitur cross-border di aplikasi merchant, transparansi kurs valas otomatis, dan panduan edukasi staf kasir saat melayani turis.",
                "slug": "cara-integrasi-pembayaran-qris-cross-border-umkm",
                "assigned_category": "Kasir & POS"
            },
            {
                "id": 403,
                "topic": "Panduan SOP Penutupan Kasir Shift Malam dan Serah Terima Uang Modal Toko",
                "trending_query": "panduan sop penutupan kasir shift malam serah terima uang toko",
                "primary_keyword": "sop penutupan kasir shift malam toko",
                "secondary_keywords": "cara closing mesin kasir pos, rekonsiliasi uang laci kasir dan laporan transaksi, formulir serah terima shift kasir, penanganan selisih kas kasir malam, penyimpanan uang tunai brankas aman",
                "search_intent": "informational",
                "search_interest": "Tinggi",
                "trending_reason": "Jam operasional toko yang diperpanjang saat musim belanja membuat proses tutup kasir shift malam rentan salah hitung karena kelelahan staf.",
                "credible_sources": [
                    "Standar Audit Operasional Kas Ritel Modern",
                    "Panduan Manajemen Toko Praktis UMKM"
                ],
                "msme_opportunity": "Menjaga akurasi saldo uang kas 100 persen dan mencegah kebocoran dana antar-pergantian petugas kasir.",
                "article_angle": "Checklist 7 langkah closing POS, pemisahan uang modal kembalian esok hari, dan pencetakan bukti rekap harian.",
                "slug": "panduan-sop-penutupan-kasir-shift-malam-toko",
                "assigned_category": "Kasir & POS"
            },
            {
                "id": 404,
                "topic": "Strategi Manajemen Stok Bahan Baku Kuliner Mengantisipasi Lonjakan Harga Akhir Tahun",
                "trending_query": "strategi stok bahan baku kuliner lonjakan harga akhir tahun",
                "primary_keyword": "strategi stok bahan baku kuliner akhir tahun",
                "secondary_keywords": "antisipasi kenaikan harga cabai telur daging minyak, kontrak pasokan bahan baku supplier kuliner, metode penyimpanan frozen bahan segar, pengendalian food cost resto kafe, menjaga margin keuntungan kuliner",
                "search_intent": "informational",
                "search_interest": "Sangat Tinggi",
                "trending_reason": "Faktor musim hujan dan tingginya permintaan liburan selalu memicu lonjakan harga komoditas pangan pokok pada pertengahan November hingga Desember.",
                "credible_sources": [
                    "Badan Pangan Nasional (Bapanas)",
                    "Data Pusat Informasi Harga Pangan Strategis (PIHPS) Nasional"
                ],
                "msme_opportunity": "Mengunci harga beli grosir lebih awal sehingga harga jual menu tetap stabil dan margin laba kotor terlindungi.",
                "article_angle": "Analisis buffer stock bahan kering, negosiasi PO tempo berjangka dengan distributor, dan penyesuaian resep standar tanpa menurunkan kualitas rasa.",
                "slug": "strategi-manajemen-stok-bahan-baku-kuliner-akhir-tahun",
                "assigned_category": "HPP & Harga Jual"
            },
            {
                "id": 405,
                "topic": "Cara Menghitung Biaya Penyimpanan Gudang Holding Cost dan Mengeliminasi Dead Stock",
                "trending_query": "cara menghitung biaya penyimpanan gudang holding cost dead stock",
                "primary_keyword": "cara menghitung biaya penyimpanan gudang holding cost",
                "secondary_keywords": "rumus carrying cost persediaan barang gudang, dampak dead stock terhadap modal kerja, biaya sewa listrik asuransi susut barang, strategi perputaran persediaan inventory turnover, mengosongkan rak gudang untuk barang laku",
                "search_intent": "informational",
                "search_interest": "Tinggi",
                "trending_reason": "Banyak gudang UMKM penuh sesak dengan barang lama yang tidak bergerak sehingga memboroskan biaya listrik, sewa tempat, dan risiko kerusakan.",
                "credible_sources": [
                    "Supply Chain Management Handbook",
                    "Prinsip Manajemen Logistik Pergudangan Efisien"
                ],
                "msme_opportunity": "Membebaskan ruang gudang dan mencairkan uang tunai yang tertidur menjadi modal produktif barang fast-moving.",
                "article_angle": "Rumus praktis menghitung holding cost per SKU per bulan, identifikasi barang non-aktif lebih dari 60 hari, dan program likuidasi cepat.",
                "slug": "cara-menghitung-biaya-penyimpanan-gudang-holding-cost",
                "assigned_category": "Inventori & Stok"
            },
            {
                "id": 406,
                "topic": "SOP Pengadaan Barang Konsinyasi dengan Pemasok Lokal Tanpa Risiko Modal Mengendap",
                "trending_query": "sop pengadaan barang konsinyasi pemasok supplier lokal toko",
                "primary_keyword": "sop pengadaan barang konsinyasi pemasok lokal",
                "secondary_keywords": "sistem bagi hasil konsinyasi toko oleh oleh, pencatatan barang titipan di kasir pos, periode pembayaran retur barang konsinyasi, surat bukti tanda terima penitipan barang, variasi produk tanpa belanja modal",
                "search_intent": "informational",
                "search_interest": "Tinggi",
                "trending_reason": "Menjelang musim belanja ramai, toko ritel dan oleh-oleh ingin memperkaya variasi etalase tanpa harus mengeluarkan modal belanja tunai dalam jumlah besar.",
                "credible_sources": [
                    "Asosiasi Pengusaha Toko Oleh-Oleh dan Cendera Mata Indonesia",
                    "Pedoman Manajemen Hubungan Pemasok Ritel"
                ],
                "msme_opportunity": "Menambah ratusan pilihan produk baru di toko secara instan dengan modal awal nol rupiah dan pembagian hasil penjualan yang terukur.",
                "article_angle": "Alur pencatatan terpisah barang milik sendiri vs titipan, sistem bagi hasil otomatis saat kasir scan barcode, dan jadwal settlement mingguan.",
                "slug": "sop-pengadaan-barang-konsinyasi-pemasok-lokal",
                "assigned_category": "Inventori & Stok"
            },
            {
                "id": 407,
                "topic": "Strategi Menagih Piutang Usaha Macet Menjelang Tutup Buku Akhir Tahun 2026",
                "trending_query": "strategi menagih piutang usaha macet menjelang tutup buku tahunan",
                "primary_keyword": "strategi menagih piutang usaha macet tutup buku",
                "secondary_keywords": "cara menagih invoice jatuh tempo pelanggan b2b, surat peringatan somasi tagihan usaha sopan, diskon pelunasan cepat early payment, mitigasi bad debts piutang tak tertagih, penagihan lewat whatsapp resmi profesional",
                "search_intent": "informational",
                "search_interest": "Sangat Tinggi",
                "trending_reason": "Menjelang tutup buku tahun 2026, arus kas bisnis terancam terganggu jika piutang pelanggan korporasi atau toko mitra belum tertagih tuntas.",
                "credible_sources": [
                    "Praktik Manajemen Kredit dan Piutang B2B Indonesia",
                    "Panduan Hukum Perjanjian Dagang dan Penagihan Usaha"
                ],
                "msme_opportunity": "Mengamankan likuiditas kas tunai maksimal sebelum akhir tahun dan menghindarkan pembukuan dari kerugian piutang tak tertagih.",
                "article_angle": "Jadwal penagihan bertahap H-7 sebelum tempo hingga H+14, tawaran insentif diskon pelunasan cepat 2 persen, dan pengiriman invoice digital berterima sah.",
                "slug": "strategi-menagih-piutang-usaha-macet-tutup-buku",
                "assigned_category": "Invoice, PO & SO"
            },
            {
                "id": 408,
                "topic": "SOP Pembuatan Surat Perjanjian Kerjasama Konsinyasi Ritel Legal bagi UMKM",
                "trending_query": "sop surat perjanjian kerjasama konsinyasi ritel toko draft legal",
                "primary_keyword": "sop surat perjanjian kerjasama konsinyasi ritel",
                "secondary_keywords": "contoh draf surat kontrak konsinyasi toko, klausul ganti rugi barang hilang rusak konsinyasi, penetapan margin bagi hasil pemilik toko dan supplier, batas waktu penarikan produk kedaluwarsa, kepastian hukum mitra dagang",
                "search_intent": "informational",
                "search_interest": "Sedang",
                "trending_reason": "Banyak sengketa antara pemilik toko dan supplier titip barang terjadi karena tidak adanya nota kesepahaman tertulis yang jelas mengenai risiko barang rusak atau hilang.",
                "credible_sources": [
                    "Kementerian Koperasi dan UKM RI Bidang Kemitraan Usaha",
                    "Pedoman Kontrak Hukum Dagang Sederhana"
                ],
                "msme_opportunity": "Melindungi kedua belah pihak dengan kepastian hukum yang jelas sehingga kemitraan dagang dapat berkembang ke skala multi-cabang.",
                "article_angle": "Klausul wajib hak dan kewajiban, ketentuan tanggung jawab susut barang, jangka waktu pembayaran, dan contoh template perjanjian.",
                "slug": "sop-surat-perjanjian-kerjasama-konsinyasi-ritel",
                "assigned_category": "Invoice, PO & SO"
            },
            {
                "id": 409,
                "topic": "Strategi Kampanye Reaktivasi Member Toko Pasca 11.11 Melalui WhatsApp Broadcast",
                "trending_query": "strategi reaktivasi member toko whatsapp broadcast crm pasca 11 11",
                "primary_keyword": "strategi reaktivasi member toko whatsapp broadcast",
                "secondary_keywords": "copywriting pesan whatsapp broadcast pelanggan loyal, promosi eksklusif voucher terima kasih pembeli 11 11, segmentasi database pelanggan kasir pos, menghindari blokir nomor whatsapp bisnis, penawaran early bird harbolnas 12 12",
                "search_intent": "informational",
                "search_interest": "Tinggi",
                "trending_reason": "Ribuan nomor pembeli yang terkumpul selama promo 11.11 harus segera dikonversi menjadi pelanggan loyal sebelum perhatian mereka teralihkan ke pesaing.",
                "credible_sources": [
                    "Studi Perilaku Konsumen WhatsApp Commerce Indonesia 2026",
                    "Panduan Kebijakan Meta WhatsApp Business Official"
                ],
                "msme_opportunity": "Menciptakan repeat order berulang dengan biaya iklan Rp 0 melalui jalur komunikasi personal WhatsApp resmi.",
                "article_angle": "Pesan ucapan terima kasih personal 24 jam pasca transaksi, penawaran voucher belanja khusus repeat order, dan integrasi WhatsApp Gateway resmi.",
                "slug": "strategi-reaktivasi-member-toko-whatsapp-broadcast",
                "assigned_category": "Marketing & WhatsApp"
            },
            {
                "id": 410,
                "topic": "Cara Menyusun Paket Hampers Akhir Tahun dengan Kalkulasi HPP dan Margin Sehat",
                "trending_query": "cara menyusun paket hampers akhir tahun hpp margin untung",
                "primary_keyword": "cara menyusun paket hampers akhir tahun hpp margin",
                "secondary_keywords": "hitung modal paket parcel natal tahun baru, biaya kemasan box pita kartu ucapan hampers, strategi bundling produk laris dan slow moving, penetapan harga jual paket kado akhir tahun, target omzet bisnis hampers rumahan",
                "search_intent": "informational",
                "search_interest": "Sangat Tinggi",
                "trending_reason": "Pemesanan hampers corporate dan personal untuk perayaan Natal dan Tahun Baru mulai dibuka pada pertengahan November dengan potensi margin sangat tinggi.",
                "credible_sources": [
                    "Asosiasi Industri Kreatif dan Kerajinan Indonesia",
                    "Benchmark Bisnis Hampers dan Souvenir 2026"
                ],
                "msme_opportunity": "Meraih omzet ratusan juta dalam tempo singkat dengan mengemas produk UMKM menjadi bingkisan bernilai estetika premium.",
                "article_angle": "Perhitungan HPP berbasis aktivitas mencakup aksesoris dan tenaga perakitan, teknik penetapan tiering harga (Ekonomis, Premium, Eksklusif), dan pre-order terpadu.",
                "slug": "cara-menyusun-paket-hampers-akhir-tahun-hpp-margin",
                "assigned_category": "HPP & Harga Jual"
            },
            {
                "id": 411,
                "topic": "Panduan Penyusunan Laporan Neraca Sederhana SAK EMKM untuk Evaluasi Bisnis Tahunan",
                "trending_query": "laporan neraca sederhana sak emkm evaluasi tahunan bisnis umkm",
                "primary_keyword": "laporan neraca sederhana sak emkm evaluasi tahunan",
                "secondary_keywords": "cara membuat neraca keuangan usaha kecil, format neraca aset kewajiban modal sak emkm, membaca posisi kas piutang persediaan utang toko, laporan keuangan syarat pengajuan pinjaman kur bank, tutup buku tahunan bisnis",
                "search_intent": "informational",
                "search_interest": "Tinggi",
                "trending_reason": "Mendekati tutup tahun 2026, pelaku usaha membutuhkan laporan neraca posisi keuangan yang rapi untuk mengevaluasi pertumbuhan kekayaan bersih usaha dan syarat pelaporan.",
                "credible_sources": [
                    "Ikatan Akuntan Indonesia (IAI) Dewan Standar Akuntansi Keuangan",
                    "Pedoman Penyusunan Laporan Keuangan UMKM Kemenkop UKM"
                ],
                "msme_opportunity": "Mengetahui secara pasti nilai aset riil usaha, total utang yang harus dilunasi, dan modal bersih tanpa kebingungan istilah rumit.",
                "article_angle": "Tiga elemen neraca (Aset, Liabilitas, Ekuitas), contoh angka riil warung dan toko ritel, serta otomasi pembentukan neraca dari sistem pembukuan terpadu.",
                "slug": "panduan-penyusunan-laporan-neraca-sak-emkm-tahunan",
                "assigned_category": "Keuangan & Pembukuan"
            }
        ]
    }
    
    keyword_research = {
        "date": "2026-11-12",
        "batch": 9,
        "market": "Indonesia (UMKM / Ritel / F&B / Grosir)",
        "total_keywords": 20,
        "keywords": [
            {
                "id": t["id"],
                "topic": t["topic"],
                "primary_keyword": t["primary_keyword"],
                "slug": t["slug"],
                "search_intent": t["search_intent"],
                "estimated_search_volume_tier": t["search_interest"],
                "secondary_keywords": [s.strip() for s in t["secondary_keywords"].split(",")],
                "target_audience": "Pemilik usaha ritel, toko kelontong, kafe kuliner, distributor grosir, produsen rumahan",
                "unique_angle": t["article_angle"],
                "cooca_feature_alignment": t["assigned_category"]
            }
            for t in trending["topics"]
        ]
    }
    
    trending_file = os.path.join(out_dir, "trending-research-2026-11-12-batch9.json")
    keyword_file = os.path.join(out_dir, "keyword-research-2026-11-12-batch9.json")
    
    with open(trending_file, "w", encoding="utf-8") as f:
        json.dump(trending, f, indent=2, ensure_ascii=False)
        
    with open(keyword_file, "w", encoding="utf-8") as f:
        json.dump(keyword_research, f, indent=2, ensure_ascii=False)
        
    print(f"Generated {trending_file} and {keyword_file} successfully.")

if __name__ == "__main__":
    generate_research()
