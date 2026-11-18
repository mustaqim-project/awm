# scripts/make_batch15_research.py
import json
import os

trending_data = {
    "date": "2026-11-18",
    "batch": 15,
    "geo": "ID",
    "category": "3",
    "category_name": "Tren Bisnis UMKM, Implementasi Coretax DJP, HPP Hampers Nataru, Proyeksi Arus Kas Q1 2027, Harbolnas 12.12 Bundling, Cuaca Ekstrem BMKG & Perpres 38/2026",
    "source": "Google Trends Indonesia 18 November 2026, Direktorat Jenderal Pajak (Coretax DJP), Perpres No 38 Tahun 2026, SAK EMKM Ikatan Akuntan Indonesia, BMKG Peringatan Cuaca Ekstrem, Asosiasi Logistik Indonesia",
    "total_topics": 20,
    "topics": [
        {
            "id": 739,
            "topic": "Implementasi Coretax DJP bagi UMKM: Tata Cara Pembuatan Kode Billing PPh Final 0,5 Persen dan Peniadaan SPT Masa Bulanan",
            "trending_query": "panduan coretax djp umkm kode billing pph final spt masa",
            "primary_keyword": "panduan coretax djp umkm pph final",
            "secondary_keywords": "cara buat kode billing coretax djp, batas omzet 500 juta bebas pajak umkm, hapus spt masa pph final di coretax, aktivasi akun coretax npwp 16 digit, rekapitulasi omzet bulanan spt tahunan umkm",
            "search_intent": "informational",
            "search_interest": "Sangat Tinggi",
            "trending_reason": "Menjelang implementasi penuh sistem perpajakan terintegrasi Coretax DJP, jutaan wajib pajak UMKM mencari kepastian tata cara penyetoran PPh Final 0,5 persen, pembuatan kode billing mandiri, dan integrasi NPWP 16 digit.",
            "credible_sources": [
                "Direktorat Jenderal Pajak Kementerian Keuangan Republik Indonesia",
                "Peraturan Pemerintah Nomor 55 Tahun 2022 tentang Penyesuaian Pengaturan di Bidang Pajak Penghasilan",
                "Keputusan Direktur Jenderal Pajak tentang Pedoman Teknis Coretax"
            ],
            "msme_opportunity": "Menghilangkan kerumitan pelaporan SPT bulanan, memanfaatkan fasilitas bebas pajak omzet hingga Rp500 juta bagi orang pribadi, dan mengamankan status kepatuhan perpajakan secara digital.",
            "article_angle": "Panduan langkah demi langkah aktivasi akun Coretax dengan NIK 16 digit, pembuatan kode billing mandiri tanpa antre di kantor pajak, pembebasan kewajiban lapor SPT Masa, dan cara menyusun rekapitulasi omzet bulanan untuk SPT Tahunan.",
            "slug": "panduan-coretax-djp-umkm-kode-billing-pph-final",
            "assigned_category": "Pajak & Legalitas"
        },
        {
            "id": 740,
            "topic": "Kalkulasi HPP Parsel dan Hampers Natal Tahun Baru: Menghitung Biaya Kemasan Hardbox, Kartu Ucapan, dan Margin Profit",
            "trending_query": "cara menghitung hpp hampers natal tahun baru kuliner parsel modal",
            "primary_keyword": "cara menghitung hpp hampers natal tahun baru",
            "secondary_keywords": "simulasi hitung modal parsel kue kering nataru, rumus markup harga jual hampers kuliner, biaya kemasan hardbox pita kartu ucapan, open po hampers natal anti rugi, margin keuntungan parsel lebaran nataru umkm",
            "search_intent": "informational",
            "search_interest": "Sangat Tinggi",
            "trending_reason": "Memasuki pertengahan November, pelaku usaha kuliner, bakery, dan artisan hampers mulai membuka sistem pre-order (PO) Natal dan Tahun Baru 2026-2027 namun kerap keliru menetapkan harga jual akibat tidak menghitung biaya kemasan dan perakitan.",
            "credible_sources": [
                "Asosiasi Pengusaha Jasaboga Indonesia (APJI)",
                "Pedoman Manajemen HPP Usaha Kuliner Indonesia",
                "Kementerian Koperasi dan UKM RI"
            ],
            "msme_opportunity": "Mengamankan margin bersih 35 sampai 50 persen dari lonjakan pesanan korporat dan keluarga tanpa risiko boncos di biaya kemasan dan tenaga kerja lembur.",
            "article_angle": "Bedah komponen HPP riil paket hampers (bahan baku, hardbox, pita, hangtag, bubble wrap pelindung, overhead), simulasi hitung markup 40 persen, dan strategi membagi tiering harga (Budget, Medium, Corporate).",
            "slug": "cara-menghitung-hpp-hampers-natal-tahun-baru-kuliner",
            "assigned_category": "Kuliner & F&B"
        },
        {
            "id": 741,
            "topic": "Proyeksi Arus Kas Kuartal Pertama (Q1) 2027: Antisipasi Penurunan Daya Beli Pasca Libur Nataru dan Biaya Operasional Awal Tahun",
            "trending_query": "cara menyusun proyeksi arus kas q1 awal tahun umkm cash flow",
            "primary_keyword": "cara menyusun proyeksi arus kas q1 umkm",
            "secondary_keywords": "mitigasi penurunan penjualan pasca tahun baru, anggaran kas darurat bisnis awal tahun, kelola modal kerja januari februari toko, cara menekan biaya operasional low season, cash flow forecast usaha kecil",
            "search_intent": "informational",
            "search_interest": "Tinggi",
            "trending_reason": "Periode Januari dan Februari (Q1) secara historis menjadi musim sepi (low season) bagi sektor ritel dan makanan karena konsumen menahan belanja pasca liburan panjang akhir tahun, sementara pengeluaran tetap seperti sewa dan gaji staf tetap berjalan.",
            "credible_sources": [
                "Bank Indonesia - Survei Penjualan Eceran",
                "Ikatan Akuntan Indonesia (IAI) - Manajemen Kas Usaha Kecil",
                "Kementerian Keuangan RI"
            ],
            "msme_opportunity": "Mempersiapkan bantalan kas operasional (buffer fund) minimal dua bulan pengeluaran tetap agar bisnis tidak mengalami krisis likuiditas saat omzet melandai di awal tahun.",
            "article_angle": "Panduan simulasi arus kas masuk dan keluar kuartal pertama, rumus menghitung dana cadangan darurat kas toko, strategi negosiasi tempo pembayaran supplier, dan pengalihan fokus ke produk kebutuhan pokok harian.",
            "slug": "cara-menyusun-proyeksi-arus-kas-q1-awal-tahun-umkm",
            "assigned_category": "Keuangan & Akuntansi"
        },
        {
            "id": 742,
            "topic": "Strategi Bundling dan Flash Sale Harbolnas 12.12: Menghitung Potongan Diskon Marketplace Tanpa Mengorbankan Margin Bersih",
            "trending_query": "strategi bundling flash sale harbolnas 12 12 diskon toko online margin",
            "primary_keyword": "strategi bundling flash sale harbolnas 12 12",
            "secondary_keywords": "cara hitung potongan komisi admin marketplace 12 12, rumus diskon bundling produk slow moving, batas aman margin keuntungan promo gratis ongkir, strategi flash sale toko online anti tekor, simulasi penetapan harga harbolnas",
            "search_intent": "informational",
            "search_interest": "Sangat Tinggi",
            "trending_reason": "Menjelang festival belanja online nasional Harbolnas 12.12, seller marketplace berlomba memasang diskon besar namun banyak yang merugi akibat potongan komisi admin dan biaya gratis ongkir platform yang mencapai 8 sampai 12 persen.",
            "credible_sources": [
                "Asosiasi E-Commerce Indonesia (idEA)",
                "Kementerian Perdagangan RI - Harbolnas 2026",
                "Data Riset Perilaku Konsumen E-Commerce Indonesia"
            ],
            "msme_opportunity": "Mengawinkan produk laris (fast-moving) dengan produk lambat laku (slow-moving) dalam satu paket bundling bernilai tambah tinggi sekaligus menjaga margin keuntungan bersih di atas 20 persen.",
            "article_angle": "Kalkulator pemotongan fee marketplace (biaya layanan, gratis ongkir ekstra, cashback ekstra), teknik cross-product bundling, dan penetapan batas diskon maksimal agar toko tetap profit tebal.",
            "slug": "strategi-bundling-dan-flash-sale-harbolnas-12-12-umkm",
            "assigned_category": "Pemasaran & Penjualan"
        },
        {
            "id": 743,
            "topic": "SOP Gudang dan Pengiriman Logistik Menghadapi Cuaca Ekstrem BMKG: Standar Bubble Wrap Tebal, Plastik Polimailer Waterproof, dan Asuransi Paket",
            "trending_query": "sop pengemasan paket musim hujan ekspedisi retur basah rusak bmkg",
            "primary_keyword": "sop pengemasan paket musim hujan ekspedisi",
            "secondary_keywords": "standar packing barang pecah belah musim hujan, plastik polimailer waterproof toko online, cara klaim asuransi paket rusak basah ekspedisi, perlindungan kardus double wall musim hujan, mitigasi retur barang rusak di jalan",
            "search_intent": "informational",
            "search_interest": "Tinggi",
            "trending_reason": "Peringatan dini cuaca ekstrem dan curah hujan tinggi dari BMKG di berbagai wilayah Indonesia meningkatkan angka klaim paket rusak, kemasan basah rembes, dan retur barang pembeli di toko online.",
            "credible_sources": [
                "Badan Meteorologi, Klimatologi, dan Geofisika (BMKG)",
                "Asosiasi Perusahaan Jasa Pengiriman Ekspres Indonesia (ASPERINDO)",
                "Pedoman Pengemasan Logistik Standar Nasional Indonesia (SNI)"
            ],
            "msme_opportunity": "Menekan tingkat retur paket akibat kerusakan fisik di bawah 0,5 persen dan melindungi reputasi toko online dengan rating ulasan bintang lima di marketplace.",
            "article_angle": "Checklist standar kemasan anti-air (polimailer lapis ganda, lakban silang keliling, kardus double wall), SOP foto dan video sebelum paket diserahkan ke kurir, serta prosedur klaim ganti rugi asuransi ekspedisi.",
            "slug": "sop-pengemasan-paket-musim-hujan-ekspedisi-anti-rusak",
            "assigned_category": "Inventori & Stok"
        },
        {
            "id": 744,
            "topic": "Panduan Evaluasi KPI Karyawan Toko dan Staf Gudang Akhir Tahun: Format Penilaian Produktivitas, Kehadiran, dan Skema Bonus Performa",
            "trending_query": "evaluasi kpi karyawan toko akhir tahun format penilaian staf bonus",
            "primary_keyword": "evaluasi kpi karyawan toko akhir tahun",
            "secondary_keywords": "cara hitung bonus akhir tahun staf kasir gudang, indikator penilaian kinerja pramuniaga ritel, form evaluasi kerja karyawan usaha kecil, rumus insentif omzet toko tutup tahun, sop penilaian kedisiplinan dan absensi staf",
            "search_intent": "informational",
            "search_interest": "Tinggi",
            "trending_reason": "Menjelang penutupan tahun buku 2026, pemilik bisnis UMKM mencari format penilaian objektif untuk mengukur performa staf kasir, pramuniaga, dan operator gudang sebelum membagikan bonus tahunan atau promosi kerja.",
            "credible_sources": [
                "Kementerian Ketenagakerjaan RI",
                "Asosiasi Pengusaha Indonesia (APINDO) Sektor UMKM",
                "Praktik Manajemen Sumber Daya Manusia Ritel Modern"
            ],
            "msme_opportunity": "Membangun loyalitas tim kerja, mengurangi tingkat pergantian karyawan (turnover) pasca tahun baru, dan memberikan penghargaan berbasis data nyata bukan rasa suka atau tidak suka subjektif.",
            "article_angle": "Format matriks KPI 4 pilar (kedisiplinan absensi, ketelitian transaksi kasir/stok, keramahan layanan, inisiatif kerja), bobot persentase nilai, dan simulasi pembagian bonus proporsional dari alokasi laba bersih toko.",
            "slug": "format-evaluasi-kpi-karyawan-toko-dan-bonus-akhir-tahun",
            "assigned_category": "Karyawan & Payroll"
        },
        {
            "id": 745,
            "topic": "Perpres 38 Tahun 2026 tentang Pengembangan Kewirausahaan Nasional: Manfaat Formalisasi Usaha, Akses Inkubasi, dan Pembaruan NIB OSS RBA",
            "trending_query": "perpres 38 2026 kewirausahaan nasional nib oss rba umkm",
            "primary_keyword": "perpres 38 2026 kewirausahaan nasional nib oss",
            "secondary_keywords": "transformasi usaha informal ke formal umkm, kemudahan legalitas usaha mikro perpres 38, syarat daftar nib oss rba risiko rendah, akses inkubasi bisnis pemerintah kemenkop umkm, fasilitas pembiayaan wirausaha mapan",
            "search_intent": "informational",
            "search_interest": "Sangat Tinggi",
            "trending_reason": "Laporan resmi pemerintah mencatat rasio kewirausahaan nasional 2026 melampaui target mencapai 3,3 persen berkat akselerasi Perpres No. 38 Tahun 2026 yang memberikan insentif legalitas kilat dan inkubasi usaha.",
            "credible_sources": [
                "Peraturan Presiden Republik Indonesia Nomor 38 Tahun 2026 tentang Pengembangan Kewirausahaan Nasional",
                "Kementerian Usaha Mikro, Kecil, dan Menengah (Kemenkop UMKM)",
                "Kementerian Investasi / BKPM Republik Indonesia"
            ],
            "msme_opportunity": "Mendapatkan nomor izin berusaha (NIB) gratis dalam hitungan menit, sertifikasi standar halal gratis, hak prioritas sertifikasi SNI bina UMK, dan kemudahan akses pembiayaan perbankan formal.",
            "article_angle": "Kupas tuntas hak istimewa wirausaha formal dalam Perpres 38/2026, panduan pembaruan data KBLI di portal OSS RBA risiko rendah, dan peta jalan transformasi dari warung rumahan menuju entitas bisnis terdaftar.",
            "slug": "manfaat-perpres-38-2026-kewirausahaan-nasional-dan-nib-oss",
            "assigned_category": "Pajak & Legalitas"
        },
        {
            "id": 746,
            "topic": "SOP Manajemen Meja dan Reservasi Resto Kafe Menjelang Libur Nataru: Kebijakan Uang Muka (DP), Durasi Santap, dan Minimal Order",
            "trending_query": "sop reservasi meja restoran libur nataru dp minimal order kafe",
            "primary_keyword": "sop reservasi meja restoran libur nataru",
            "secondary_keywords": "aturan uang muka booking meja kafe natal tahun baru, kebijakan batasan waktu makan rush hour, minimal order paket makan malam keluarga resto, alur split bill rombongan pelanggan pos, manajemen antrean walk in tamu reservasi",
            "search_intent": "informational",
            "search_interest": "Sangat Tinggi",
            "trending_reason": "Restoran dan kafe menghadapi lonjakan reservasi makan malam keluarga dan pesta akhir tahun, namun rawan dirugikan oleh tamu yang tidak hadir (no-show) tanpa kabar atau menduduki meja berjam-jam tanpa pesanan tambahan.",
            "credible_sources": [
                "Perhimpunan Hotel dan Restoran Indonesia (PHRI)",
                "Pedoman Standar Operasional Restoran Modern",
                "Kementerian Pariwisata dan Ekonomi Kreatif RI"
            ],
            "msme_opportunity": "Memastikan tingkat keterisian meja (seat occupancy rate) 100 persen, mengunci uang muka non-refundable, dan melipatgandakan perputaran meja (table turnover) pada jam puncak kunjungan.",
            "article_angle": "Draft aturan reservasi tertulis (DP 30-50 persen via QRIS/transfer), penentuan durasi santap 90 sampai 120 menit, penetapan minimum spend per meja, dan alokasi rasio meja reservasi versus tamu langsung (walk-in).",
            "slug": "sop-reservasi-meja-restoran-dan-kafe-libur-nataru",
            "assigned_category": "Kasir & Operasional POS"
        },
        {
            "id": 747,
            "topic": "Cara Mempercepat Stock Opname Akhir Tahun Menggunakan Barcode Scanner Nirkabel: Eliminasi Kesalahan Catat Manual Kertas",
            "trending_query": "cara stock opname cepat dengan barcode scanner toko fisik pos",
            "primary_keyword": "cara stock opname cepat dengan barcode scanner",
            "secondary_keywords": "aplikasi stock opname scan barcode hp, selisih stok fisik vs catatan sistem kasir pos, cara cetak barcode sku produk toko sendiri, sop opname barang ribuan sku tanpa tutup toko, barcode scanner wireless bluetooth gudang",
            "search_intent": "informational",
            "search_interest": "Tinggi",
            "trending_reason": "Stock opname manual menggunakan lembaran kertas fotokopi memakan waktu berhari-hari, membuat mata lelah, dan menghasilkan tingkat salah hitung hingga 15 persen pada toko ritel dengan ribuan jenis barang dagangan.",
            "credible_sources": [
                "Asosiasi Pengusaha Ritel Indonesia (APRINDO)",
                "Pedoman Manajemen Inventori Berbasis Barcode Ritel",
                "Kementerian Koperasi dan UKM RI"
            ],
            "msme_opportunity": "Memangkas waktu pelaksanaan stock opname tahunan dari 3 hari menjadi 4 jam saja tanpa perlu menutup operasional toko, dengan akurasi pencatatan mencapai 99,8 persen.",
            "article_angle": "Panduan implementasi barcode scanner wireless bluetooth, teknik pembagian zona rak toko, pencocokan otomatis hasil scan dengan data stok sistem POS, dan pencetakan berita acara selisih persediaan.",
            "slug": "cara-stock-opname-cepat-dengan-barcode-scanner-toko",
            "assigned_category": "Inventori & Stok"
        },
        {
            "id": 748,
            "topic": "Manajemen Alur Stok Toko Fisik dan Live Shopping Video Commerce: Mencegah Kerugian Akibat Overselling dan Pembatalan Otomatis",
            "trending_query": "manajemen stok live shopping video commerce toko fisik tiktok shopee",
            "primary_keyword": "manajemen stok live shopping video commerce",
            "secondary_keywords": "cara cegah overselling live streaming tiktok shopee, alokasi kuota stok toko offline dan live broadcast, sinkronisasi stok otomatis kasir omnichannel, penanganan pesanan belum bayar live sale, sop packing orderan live shopping kilat",
            "search_intent": "informational",
            "search_interest": "Sangat Tinggi",
            "trending_reason": "Tren live shopping video commerce melonjak tajam dengan tingkat konversi 3 kali lipat lebih tinggi dari foto statis, namun pemilik toko sering terkena penalti reputasi buruk dan pembatalan pesanan akibat stok barang di etalase fisik sudah habis terjual.",
            "credible_sources": [
                "Riset Asosiasi Digital E-Commerce Indonesia",
                "Pedoman Seller Multisaluran Ritel Indonesia",
                "Kementerian Perdagangan Republik Indonesia"
            ],
            "msme_opportunity": "Mengamankan omzet besar dari siaran langsung tanpa khawatir kekurangan barang, mencegah penalti pemblokiran akun live seller, dan memberikan kepastian ketersediaan barang kepada pelanggan toko fisik.",
            "article_angle": "Teknik pembagian kuota stok (virtual stock pooling), SOP isolasi barang fisik saat live streaming berlangsung, aturan sinkronisasi inventori terpusat, dan alur penanganan pesanan belum bayar (unpaid orders).",
            "slug": "manajemen-stok-toko-fisik-dan-live-shopping-video-commerce",
            "assigned_category": "Pemasaran & Penjualan"
        },
        {
            "id": 749,
            "topic": "Cara Mencatat Biaya Kerusakan Barang, Kedaluwarsa, dan Susut Stok dalam Laporan Laba Rugi SAK EMKM Tutup Buku",
            "trending_query": "cara mencatat biaya barang rusak sak emkm susut kedaluwarsa laba rugi",
            "primary_keyword": "cara mencatat biaya barang rusak sak emkm",
            "secondary_keywords": "jurnal penyesuaian barang kedaluwarsa toko, pembebanan susut stok ke hpp usaha kecil, berita acara pemusnahan barang rusak gudang, pengaruh persediaan rusak terhadap laba kotor, perlakuan akuntansi barang hilang stock opname",
            "search_intent": "informational",
            "search_interest": "Tinggi",
            "trending_reason": "Banyak pedagang toko kelontong, frozen food, dan kosmetik membiarkan barang rusak atau kedaluwarsa menumpuk tanpa dicatat di pembukuan, sehingga nilai laba usaha di atas kertas tampak tinggi semu padahal modal tergerus.",
            "credible_sources": [
                "Ikatan Akuntan Indonesia (IAI) - Dewan Standar Akuntansi Keuangan SAK EMKM",
                "Pedoman Pencatatan Akuntansi Usaha Dagang Kementerian Koperasi dan UKM",
                "Direktorat Jenderal Pajak - Perlakuan Biaya Kerugian Persediaan"
            ],
            "msme_opportunity": "Menghitung beban kerugian riil sebagai pengurang laba kotor yang sah secara akuntansi dan pajak, serta mencegah pembayaran pajak berlebih atas keuntungan semu yang tidak pernah ada.",
            "article_angle": "Format Berita Acara Pemusnahan Barang Rusak, pembuatan jurnal penyesuaian (debit Beban Persediaan Rusak/Kedaluwarsa, kredit Persediaan Barang Dagang), dan penyajian nilainya pada laporan laba rugi SAK EMKM.",
            "slug": "cara-mencatat-biaya-barang-rusak-dan-kedaluwarsa-sak-emkm",
            "assigned_category": "Keuangan & Akuntansi"
        },
        {
            "id": 750,
            "topic": "Strategi Reaktivasi Pelanggan Tidak Aktif (Dormant Customer) Akhir Tahun: Formula Pesan WhatsApp Personal dan Voucher Eksklusif",
            "trending_query": "strategi reaktivasi pelanggan whatsapp akhir tahun pesan voucher rfm",
            "primary_keyword": "strategi reaktivasi pelanggan whatsapp akhir tahun",
            "secondary_keywords": "cara menghubungi pelanggan lama lewat wa resmi, segmentasi rfm pelanggan tidak belanja 90 hari, template broadcast wa sapa pelanggan lama promo, voucher eksklusif comeback belanja toko offline online, tips broadcast whatsapp tanpa diblokir",
            "search_intent": "informational",
            "search_interest": "Sangat Tinggi",
            "trending_reason": "Biaya mengakuisisi pelanggan baru 5 kali lipat lebih mahal daripada mempertahankan pelanggan lama. Akhir tahun menjadi momen emas menyapa kembali pelanggan yang tidak berbelanja lebih dari 90 hari.",
            "credible_sources": [
                "Riset Customer Relationship Management Ritel Indonesia",
                "Pedoman Pemasaran Digital Berbasis Percakapan (Conversational Commerce)",
                "Kementerian Koperasi dan UKM RI"
            ],
            "msme_opportunity": "Mendongkrak omzet hingga 30 persen tanpa mengeluarkan biaya iklan berbayar (ads), cukup dengan memanfaatkan basis data kontak pelanggan yang sudah tersimpan di riwayat transaksi kasir.",
            "article_angle": "Segmentasi data pelanggan model Recency Frequency Monetary (RFM), formula copywriting sapaan personal anti-spam, penawaran kupon diskon sambutan kembali (welcome back voucher), dan jam kirim pesan terbaik.",
            "slug": "strategi-reaktivasi-pelanggan-dormant-whatsapp-akhir-tahun",
            "assigned_category": "Pemasaran & CRM"
        },
        {
            "id": 751,
            "topic": "Panduan Masuk Pengadaan Barang dan Jasa Pemerintah via Padisera (PaDi UMKM) dan e-Katalog LKPP V6: Syarat NIB, NPWP, dan KBLI",
            "trending_query": "panduan padisera padi umkm ekatalog lkpp v6 syarat daftar bumn",
            "primary_keyword": "panduan padisera padi umkm ekatalog lkpp",
            "secondary_keywords": "syarat daftar padisera pasar digital bumn, cara tayang produk di ekatalog lkpp versi 6, peluang tender belanja barang pemerintah umkm, faktur pajak dan bukti potong pph pasal 22, dokumen legalitas kbli usaha pengadaan",
            "search_intent": "informational",
            "search_interest": "Sangat Tinggi",
            "trending_reason": "Transformasi PaDi UMKM menjadi Padisera (Pasar Digital Serba Ada) serta implementasi penuh e-Katalog LKPP Versi 6 membuka peluang alokasi belanja pengadaan pemerintah dan BUMN minimal 40 persen khusus untuk produk lokal UMKM.",
            "credible_sources": [
                "Lembaga Kebijakan Pengadaan Barang/Jasa Pemerintah (LKPP)",
                "Kementerian Badan Usaha Milik Negara (BUMN) - Ekosistem Padisera",
                "Kementerian Koperasi dan UKM Republik Indonesia"
            ],
            "msme_opportunity": "Meraih pesanan volume besar dari kementerian, lembaga, dinas pemerintah daerah, dan BUMN dengan kepastian pembayaran langsung melalui rekening giro usaha resmi.",
            "article_angle": "Langkah pendaftaran akun vendor Padisera dan e-Katalog V6, pemenuhan syarat kelengkapan NIB risiko rendah, sinkronisasi NPWP 16 digit, pembuatan katalog etalase produk, dan tata cara pembuatan invoice B2G.",
            "slug": "panduan-padisera-padi-umkm-dan-ekatalog-lkpp-v6",
            "assigned_category": "Pajak & Legalitas"
        },
        {
            "id": 752,
            "topic": "SOP Pengelolaan Uang Pecahan Kecil (Kas Kembalian) dan Modal Awal Laci Kasir Jam Sibuk Liburan",
            "trending_query": "sop kas kembalian modal awal laci kasir pecahan uang receh",
            "primary_keyword": "sop kas kembalian modal awal laci kasir",
            "secondary_keywords": "aturan penukaran uang receh pecahan kecil toko, nominal ideal modal awal laci kasir shift pagi, cegah selisih uang kasir karena tidak ada kembalian, integrasi qris dinamis hindari masalah uang receh, sop serah terima kas kecil laci kasir",
            "search_intent": "informational",
            "search_interest": "Tinggi",
            "trending_reason": "Jam sibuk akhir pekan dan musim liburan kerap diwarnai kepanikan kasir yang kehabisan uang kembalian pecahan dua ribu hingga dua puluh ribu rupiah, yang memicu antrean panjang dan komplain pembeli.",
            "credible_sources": [
                "Bank Indonesia - Pedoman Pengedaran Uang Rupiah",
                "Standar Operasional Prosedur Kasir Toko Modern",
                "Asosiasi Pengusaha Ritel Indonesia (APRINDO)"
            ],
            "msme_opportunity": "Menghilangkan friksi antrean pembayaran tunai di meja kasir, mencegah pemberian permen sebagai pengganti kembalian yang dilarang undang-undang, dan meminimalkan risiko selisih hitung laci.",
            "article_angle": "Formula penetapan besaran modal dasar laci kasir (float fund) per shift, jadwal penukaran uang receh ke bank mitra seminggu sekali, teknik tata letak laci kasir, dan edukasi beralih ke pembayaran QRIS dinamis.",
            "slug": "sop-pengelolaan-kas-kembalian-dan-modal-awal-laci-kasir",
            "assigned_category": "Kasir & Operasional POS"
        },
        {
            "id": 753,
            "topic": "Strategi Penerbitan Purchase Order (PO) Bahan Baku Akhir Tahun: Antisipasi Libur Pabrik Supplier dan Lonjakan Harga Musiman",
            "trending_query": "strategi purchase order bahan baku libur supplier tutup akhir tahun",
            "primary_keyword": "strategi purchase order bahan baku libur supplier",
            "secondary_keywords": "cara hitung safety stock bahan baku jelang natal tahun baru, negosiasi termin pembayaran po supplier b2b, format surat purchase order resmi usaha kecil, jadwal cut off pemesanan pabrik distributor akhir tahun, manajemen lead time pengiriman bahan baku kuliner",
            "search_intent": "informational",
            "search_interest": "Sangat Tinggi",
            "trending_reason": "Banyak pabrik, importir, dan distributor bahan baku mengumumkan jadwal cut-off pesanan pada pertengahan Desember serta libur operasional hingga awal Januari, yang mengancam terhentinya produksi bagi UMKM yang telat memesan.",
            "credible_sources": [
                "Asosiasi Logistik dan Rantai Pasok Indonesia (ALFI)",
                "Pedoman Manajemen Rantai Pasok Usaha Manufaktur dan Kuliner",
                "Kementerian Perindustrian Republik Indonesia"
            ],
            "msme_opportunity": "Mengamankan stok bahan baku krusial pada harga normal sebelum terjadi lonjakan harga musiman, mencegah mesin produksi menganggur, dan memenuhi seluruh komitmen pesanan akhir tahun.",
            "article_angle": "Rumus penghitungan buffer stock dan reorder point menghadapi lead time libur panjang, pembuatan surat Purchase Order (PO) resmi bertanda tangan, serta taktik pembayaran termin bertahap (DP 30 persen dan pelunasan Net 30).",
            "slug": "strategi-purchase-order-bahan-baku-antisipasi-libur-supplier",
            "assigned_category": "Inventori & Stok"
        },
        {
            "id": 754,
            "topic": "Menu Engineering Musim Hujan untuk Resto dan Kafe: Substitusi Bahan Baku Basah ke Bahan Kering Tahan Simpan Tanpa Ubah Rasa",
            "trending_query": "menu engineering musim hujan bahan baku kafe kuliner sayur cabai",
            "primary_keyword": "menu engineering musim hujan bahan baku kafe",
            "secondary_keywords": "cara atasi lonjakan harga cabai sayur musim hujan, teknik penyimpanan cold storage sayuran restoran, resep minuman hangat musim hujan margin tinggi, hitung hpp menu musiman fnb akhir tahun, strategi cegah bahan baku membusuk di dapur resto",
            "search_intent": "informational",
            "search_interest": "Tinggi",
            "trending_reason": "Curah hujan lebat di sentra pertanian kerap memicu lonjakan harga cabai rawit, sayuran daun, dan tomat hingga 100 persen, serta menurunkan daya simpan sayur segar di dapur restoran hingga mudah membusuk.",
            "credible_sources": [
                "Badan Pangan Nasional (BAPANAS)",
                "Asosiasi Pengusaha Jasaboga Indonesia (APJI)",
                "Kementerian Pertanian Republik Indonesia"
            ],
            "msme_opportunity": "Menjaga stabilitas margin laba kotor kuliner di atas 60 persen saat harga pasar bergejolak, tanpa harus menaikkan harga jual di menu yang bisa membuat pelanggan kabur.",
            "article_angle": "Teknik rekayasa menu (Menu Engineering), strategi menggunakan cabai giling/pasta atau bumbu dasar terstandar, teknik blanching dan vacuum sealing sayuran, serta peluncuran menu hangat musiman berkeuntungan tebal.",
            "slug": "menu-engineering-musim-hujan-substitusi-bahan-baku-kafe",
            "assigned_category": "Kuliner & F&B"
        },
        {
            "id": 755,
            "topic": "Tata Cara Pembagian Dividen Usaha dan Penentuan Laba Ditahan Akhir Tahun untuk CV dan PT Perorangan Sesuai Aturan Pajak",
            "trending_query": "tata cara pembagian dividen laba ditahan akhir tahun cv pt perorangan",
            "primary_keyword": "pembagian dividen dan laba ditahan cv pt akhir tahun",
            "secondary_keywords": "syarat pembagian laba prive pemilik cv bebas pajak, dividen pt perorangan bebas pph jika diinvestasikan, cara hitung laba bersih setelah pajak tutup buku, rapat umum pemegang saham rups sirkuler sederhana, porsi ideal laba ditahan untuk ekspansi bisnis",
            "search_intent": "informational",
            "search_interest": "Tinggi",
            "trending_reason": "Setelah setahun bekerja keras, para pendiri badan usaha CV dan PT Perorangan ingin menarik keuntungan usaha ke kantong pribadi namun sering ragu mengenai aspek perpajakan dan porsi kas yang wajib disisakan untuk modal kerja.",
            "credible_sources": [
                "Undang-Undang Harmonisasi Peraturan Perpajakan (UU HPP)",
                "Direktorat Jenderal Pajak Kementerian Keuangan RI",
                "Kementerian Hukum dan Hak Asasi Manusia - Administrasi Hukum Umum"
            ],
            "msme_opportunity": "Menikmati hasil jerih payah usaha secara legal dan bebas pajak penghasilan dividen sesuai ketentuan investasi kembali, sekaligus menjaga kesehatan finansial perusahaan untuk ekspansi tahun depan.",
            "article_angle": "Perbedaan mendasar pembagian laba prive pada CV (bukan objek pajak) versus dividen pada PT Perorangan (bebas PPh bila diinvestasikan kembali dalam 3 tahun), format Berita Acara RUPS sederhana, dan penentuan porsi laba ditahan (retained earnings) 40 sampai 60 persen.",
            "slug": "tata-cara-pembagian-dividen-dan-laba-ditahan-akhir-tahun-cv-pt",
            "assigned_category": "Keuangan & Akuntansi"
        },
        {
            "id": 756,
            "topic": "Kampanye Pengingat Kedaluwarsa Poin Member Akhir Tahun: Mendorong Transaksi Berulang (Repeat Order) Sebelum Hangus",
            "trending_query": "kampanye kedaluwarsa poin member repeat order akhir tahun broadcast wa",
            "primary_keyword": "kampanye kedaluwarsa poin member repeat order",
            "secondary_keywords": "broadcast whatsapp pengingat poin belanja hangus 31 desember, strategi redeem poin belanja akhir tahun toko ritel, cara hitung liabilitas poin belum ditukar di neraca, penawaran merchandise eksklusif tukar poin member, meningkatkan retensi pelanggan setia toko offline",
            "search_intent": "informational",
            "search_interest": "Sangat Tinggi",
            "trending_reason": "Banyak toko ritel dan kafe memiliki saldo poin pelanggan bernilai jutaan rupiah yang mengendap sebagai liabilitas utang di pembukuan dan akan hangus otomatis per 31 Desember jika tidak ditukarkan.",
            "credible_sources": [
                "Riset Customer Loyalty Program Indonesia",
                "Asosiasi Pengusaha Ritel Indonesia (APRINDO)",
                "Pedoman Akuntansi Liabilitas Program Loyalitas Pelanggan"
            ],
            "msme_opportunity": "Menciptakan lonjakan kunjungan toko dan pembelian ulang di penghujung tahun dengan memanfaatkan urgensi kedaluwarsa poin, sekaligus membersihkan beban liabilitas di neraca keuangan.",
            "article_angle": "Strategi pesan broadcast WhatsApp berjenjang (H-30, H-14, H-3), penawaran paket merchandise atau diskon tambahan khusus penukaran poin, dan cara menghapus sisa saldo poin hangus dari neraca keuangan secara akurat.",
            "slug": "kampanye-kedaluwarsa-poin-loyalitas-member-dorong-repeat-order",
            "assigned_category": "Pemasaran & CRM"
        },
        {
            "id": 757,
            "topic": "Audit Keamanan Kasir POS Menjelang Puncak Transaksi Akhir Tahun: Mencegah Modus Pencurian Transaksi Tanpa Struk dan Pembatalan Palsu",
            "trending_query": "audit keamanan kasir pos cegah modus void pembatalan palsu pencurian",
            "primary_keyword": "audit keamanan kasir pos cegah modus void",
            "secondary_keywords": "modus kasir tidak cetak struk kantongi uang tunai, pentingnya otorisasi pin supervisor untuk void transaksi, rekonsiliasi total penjualan kasir vs uang di laci, audit log riwayat aktivitas aplikasi kasir pos, sop blind cash drop saat laci kasir penuh",
            "search_intent": "informational",
            "search_interest": "Sangat Tinggi",
            "trending_reason": "Pada musim ramai pengunjung, kasir yang tidak diawasi rawan tergoda melakukan manipulasi seperti membatalkan pesanan (void fiktif) setelah pelanggan membayar tunai atau sengaja tidak mencetak struk belanja.",
            "credible_sources": [
                "Association of Certified Fraud Examiners (ACFE) Indonesia Chapter",
                "Standar Pengendalian Internal Ritel dan Kasir Modern",
                "Kementerian Perdagangan RI"
            ],
            "msme_opportunity": "Menyelamatkan kebocoran omzet hingga puluhan juta rupiah, menciptakan lingkungan kerja kasir yang jujur dan disiplin, serta memastikan setiap rupiah penjualan masuk utuh ke rekening usaha.",
            "article_angle": "Kupas 3 modus fraud kasir paling sering (void pasca bayar, no-sale open drawer, nota fiktif), proteksi hak akses berbasis PIN supervisor, pelaksanaan blind cash drop, dan rekonsiliasi tutup shift harian.",
            "slug": "audit-keamanan-kasir-pos-cegah-modus-void-pembatalan-palsu",
            "assigned_category": "Kasir & Operasional POS"
        },
        {
            "id": 758,
            "topic": "Panduan Merekrut Staf Tambahan dan Kasir Part-Time Musiman Harbolnas dan Liburan: Contoh Surat Perjanjian Kerja Waktu Tertentu (PKWT)",
            "trending_query": "panduan merekrut karyawan part time musiman harbolnas liburan pkwt",
            "primary_keyword": "panduan merekrut karyawan part time musiman",
            "secondary_keywords": "contoh surat perjanjian pkwt pekerja musiman umkm, sistem gaji harian borongan tim packing toko online, pelatihan cepat kasir part time dalam satu hari, sop kerahasiaan data pelanggan dan anti pencurian staf lepas, hak uang makan dan lembur tenaga kerja musiman",
            "search_intent": "informational",
            "search_interest": "Tinggi",
            "trending_reason": "Menghadapi lonjakan pembeli Harbolnas 12.12 dan libur panjang akhir tahun, pemilik usaha membutuhkan tambahan tenaga kerja harian lepas untuk bagian kasir dan gudang packing namun khawatir terikat sengketa perburuhan.",
            "credible_sources": [
                "Kementerian Ketenagakerjaan RI - Pedoman Perjanjian Kerja Waktu Tertentu (PKWT)",
                "Peraturan Pemerintah Nomor 35 Tahun 2021 tentang PKWT dan Alih Daya",
                "Asosiasi Pengusaha Ritel Indonesia (APRINDO)"
            ],
            "msme_opportunity": "Mendapatkan tenaga kerja cadangan yang cekatan tanpa risiko hukum, mengatasi antrean dan tumpukan packing tepat waktu, dengan struktur biaya fleksibel sesuai kebutuhan musim ramai.",
            "article_angle": "Klausul penting dalam surat kontrak PKWT musiman (durasi kerja tertentu, besaran upah harian/per jam, batasan tanggung jawab), metode briefing kilat satu hari, dan SOP pengawasan staf lepas di area kasir dan inventori.",
            "slug": "panduan-merekrut-karyawan-part-time-musiman-harbolnas-liburan",
            "assigned_category": "Karyawan & Payroll"
        }
    ]
}

# Write trending-research-2026-11-18.json
out_dir = os.path.join("database", "data", "articles")
os.makedirs(out_dir, exist_ok=True)
trending_path = os.path.join(out_dir, "trending-research-2026-11-18.json")
with open(trending_path, "w", encoding="utf-8") as f:
    json.dump(trending_data, f, indent=2, ensure_ascii=False)
print(f"[OK] Written trending research to {trending_path}")

# Write keyword-research-2026-11-18.json
keyword_data = {
    "date": "2026-11-18",
    "batch": 15,
    "scope": "20 Artikel Edukasi Riset Tren Bisnis UMKM Indonesia",
    "total_articles": 20,
    "topics": trending_data["topics"]
}
keyword_path = os.path.join(out_dir, "keyword-research-2026-11-18.json")
with open(keyword_path, "w", encoding="utf-8") as f:
    json.dump(keyword_data, f, indent=2, ensure_ascii=False)
print(f"[OK] Written keyword research to {keyword_path}")
