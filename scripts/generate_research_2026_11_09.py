# scripts/generate_research_2026_11_09.py
import json, os

trending_data = {
  "date": "2026-11-09",
  "batch": 4,
  "geo": "ID",
  "category": 3,
  "category_name": "Bisnis (Business & Industrial / Finance / Retail Operations)",
  "source": "Google Trends Indonesia & Regulatory Analysis November 2026",
  "total_topics": 20,
  "topics": [
    {
      "id": 291,
      "topic": "Cara Hitung Diskon Flash Sale Harbolnas Agar Tidak Boncos Tergerus Potongan Marketplace",
      "trending_query": "cara hitung diskon flash sale harbolnas",
      "primary_keyword": "cara hitung diskon flash sale harbolnas",
      "secondary_keywords": [
        "potongan biaya admin marketplace shopee tiktok",
        "cara hitung margin bersih promo harbolnas",
        "simulasi diskon kilat produk umkm",
        "strategi promo 11.11 anti rugi",
        "biaya iklan ads dan subsidi ongkir"
      ],
      "search_intent": "Informational",
      "search_interest": "Sangat Tinggi (Lonjakan drastis pencarian seller menjelang Harbolnas 11.11 per 9 November 2026 mengenai skema perang harga vs margin bersih)",
      "trending_reason": "Menjelang festival belanja 11.11, ribuan seller UMKM dihadapkan pada potongan admin marketplace 8-12%, program gratis ongkir ekstra, dan biaya kampanye. Banyak seller mengalami lonjakan omzet besar tetapi merugi (boncos) karena tidak memperhitungkan net margin pasca komisi dan packing.",
      "credible_sources": [
        "Kementerian Koperasi dan UKM RI (kemenkopukm.go.id)",
        "Pusat Edukasi Seller Marketplace Nasional",
        "Kontan Keuangan & Bisnis Indonesia",
        "Asosiasi E-Commerce Indonesia (idEA)"
      ],
      "msme_opportunity": "Seller online dapat mengunci batas diskon aman (floor price), menyusun bundling produk margin tinggi, dan mempertahankan laba bersih minimal 15-20 persen selama kampanye.",
      "article_angle": "Panduan kalkulasi matematis harga promo 11.11, simulasi bedah komponen potongan admin marketplace, penentuan safety margin, dan strategi bundling.",
      "slug": "cara-hitung-diskon-flash-sale-11-11-agar-tidak-boncos",
      "assigned_category": "HPP & Harga Jual"
    },
    {
      "id": 292,
      "topic": "Mencegah Overselling dan Pembatalan Order Saat Puncak Promo Harbolnas 11.11",
      "trending_query": "mencegah overselling dan pembatalan order harbolnas",
      "primary_keyword": "mencegah overselling dan pembatalan order harbolnas",
      "secondary_keywords": [
        "alokasi buffer stock flash sale marketplace",
        "sinkronisasi stok toko fisik dan online",
        "sanksi penalti pembatalan pesanan seller",
        "manajemen stok multi channel toko ritel",
        "software inventori cloud real time"
      ],
      "search_intent": "Informational",
      "search_interest": "Tinggi (Kekhawatiran seller atas lonjakan pesanan 11.11 yang memicu stok habis semu atau phantom stockout dan sanksi poin penalti)",
      "trending_reason": "Lonjakan pesanan multi-kanal (TikTok Shop, Shopee, Tokopedia, dan toko fisik) saat flash sale kerap menimbulkan selisih stok fisik vs sistem. Pembatalan sepihak berakibat penalti akun seller, penurunan rating toko, dan hilangnya kepercayaan pelanggan.",
      "credible_sources": [
        "Kebijakan Penalti dan Performa Toko Marketplace",
        "Supply Chain Indonesia (SCI)",
        "Koran Jakarta Industri & Ritel",
        "Pusat Studi Logistik dan Persediaan Nasional"
      ],
      "msme_opportunity": "Pemilik toko dapat memisahkan stok penyangga (buffer stock), menerapkan sinkronisasi otomatis, dan menjaga rasio pemenuhan pesanan 100 persen bebas penalti.",
      "article_angle": "Langkah mitigasi teknis inventori pra-kampanye, rumus penentuan safety stock per kanal, serta integrasi pemotongan stok otomatis real time.",
      "slug": "mencegah-overselling-dan-pembatalan-order-harbolnas",
      "assigned_category": "Inventori & Stok"
    },
    {
      "id": 293,
      "topic": "Mengelola Jeda Pencairan Saldo Marketplace untuk Menjaga Arus Kas Toko",
      "trending_query": "mengelola jeda pencairan saldo marketplace",
      "primary_keyword": "mengelola jeda pencairan saldo marketplace",
      "secondary_keywords": [
        "payout lag saldo escrow shopee tokopedia tiktok",
        "manajemen likuiditas kas toko online",
        "dana talangan belanja modal bahan baku",
        "mitigasi penundaan pencairan penjualan",
        "pengelolaan cash flow pesanan online"
      ],
      "search_intent": "Informational",
      "search_interest": "Tinggi (Tren kendala arus kas seller online akibat jeda pencairan dana penjualan escrow marketplace 3-7 hari setelah pesanan tiba)",
      "trending_reason": "Omzet besar saat Harbolnas tertahan dalam rekening penampung (escrow) platform sampai pesanan diterima pembeli. Seller yang kehabisan uang kas tunai kesulitan membeli bahan baku atau stok tambahan untuk memenuhi pesanan hari-hari berikutnya.",
      "credible_sources": [
        "Bank Indonesia - Sistem Pembayaran dan Kliring",
        "Otoritas Jasa Keuangan (ojk.go.id)",
        "Fintech Indonesia & Asosiasi Modal Kerja",
        "CNBC Indonesia Riset Bisnis"
      ],
      "msme_opportunity": "Owner toko online memahami siklus cash conversion cycle (CCC), menyiapkan pos kas operasional darurat, dan menjaga kelancaran pasokan tanpa ketergantungan saldo cair seketika.",
      "article_angle": "Panduan mitigasi payout lag, simulasi arus kas bergulir 14 hari, pemisahan pos kas belanja modal, dan tata kelola likuiditas harian toko online.",
      "slug": "mengelola-jeda-pencairan-saldo-marketplace-arus-kas",
      "assigned_category": "Keuangan & Pembukuan"
    },
    {
      "id": 294,
      "topic": "Relaksasi Bertahap Wajib Halal UMK Menurut PP 42 Tahun 2024 dan SE BPJPH",
      "trending_query": "relaksasi bertahap wajib halal umk",
      "primary_keyword": "relaksasi bertahap wajib halal umk",
      "secondary_keywords": [
        "tenggat waktu sertifikasi halal usaha mikro kecil",
        "pendaftaran program sehati bpjph kemenag gratis",
        "syarat sertifikasi halal self declare produk pangan",
        "pengawasan tim jph terpadu kementerian agama",
        "sanksi administratif pelaku usaha kuliner"
      ],
      "search_intent": "Informational",
      "search_interest": "Sangat Tinggi (Pencarian kejelasan payung hukum pasca tenggat 18 Oktober 2026 dan masa transisi penyesuaian bagi pelaku usaha mikro)",
      "trending_reason": "Pemerintah memberikan ruang penyesuaian dan relaksasi bertahap hingga 2028 bagi Usaha Mikro dan Kecil (UMK) sektor makanan dan minuman, seraya memperketat sosialisasi program Sertifikasi Halal Gratis (SEHATI) melalui skema self-declare bersama Pendamping Proses Produk Halal (P3H).",
      "credible_sources": [
        "Badan Penyelenggara Jaminan Produk Halal (bpjph.halal.go.id)",
        "Kementerian Agama RI (kemenag.go.id)",
        "PP No. 42 Tahun 2024 jo. Regulasi Transisi Halal UMK 2026",
        "Media Indonesia & Detik Finance"
      ],
      "msme_opportunity": "Pelaku usaha kuliner dan produsen pangan rumahan dapat memanfaatkan masa relaksasi ini untuk mendaftar sertifikasi halal gratis tanpa rasa takut ditindak sanksi penutupan.",
      "article_angle": "Ulasan yuridis masa relaksasi halal, syarat berkas self-declare SEHATI, verifikasi bahan baku halal, dan langkah pendampingan P3H.",
      "slug": "relaksasi-bertahap-wajib-halal-umk-dan-syarat-sehati",
      "assigned_category": "Pajak & Legalitas"
    },
    {
      "id": 295,
      "topic": "SOP Pengemasan Kilat dan Mitigasi Selisih Ongkir Kurir Peak Season Harbolnas",
      "trending_query": "sop pengemasan kilat dan mitigasi selisih ongkir",
      "primary_keyword": "sop pengemasan kilat dan mitigasi selisih ongkir",
      "secondary_keywords": [
        "standar packing aman ekspedisi bubble wrap kardus",
        "sengketa selisih berat timbangan paket kurir",
        "persiapan perlengkapan packing promo akhir tahun",
        "penjemputan paket kurir pickup ekspedisi",
        "kecepatan proses pesanan sla seller marketplace"
      ],
      "search_intent": "Informational",
      "search_interest": "Tinggi (Lonjakan kebutuhan alur kerja packing cepat 24 jam dan pencegahan pemotongan saldo akibat sengketa volume berat kurir)",
      "trending_reason": "Selama pesta diskon 11.11, keterlambatan kirim memicu pembatalan otomatis (SLA breach). Selain itu, perbedaan timbangan volumetrik antara seller dan ekspedisi sering kali memotong saldo penghasilan toko hingga jutaan rupiah.",
      "credible_sources": [
        "Asosiasi Perusahaan Jasa Pengiriman Ekspres Indonesia (ASPERINDO)",
        "Panduan Standar Operasional Logistik E-Commerce",
        "Pusat Bantuan Logistik Seller",
        "Bisnis Logistik & Transportasi Indonesia"
      ],
      "msme_opportunity": "Seller membangun workstation packing ergonomis, standardisasi ukuran kardus, dan akurasi timbangan paket untuk menekan biaya operasional logistik.",
      "article_angle": "Panduan tata letak meja packing efisien, teknik volumetrik kardus anti selisih ongkir, kalibrasi timbangan digital, dan SOP serah terima resi kurir.",
      "slug": "sop-pengemasan-kilat-dan-mitigasi-selisih-ongkir-kurir",
      "assigned_category": "Strategi & Operasional"
    },
    {
      "id": 296,
      "topic": "SOP Penanganan Retur Barang dan Pencadangan Dana Klaim Garansi Harbolnas",
      "trending_query": "sop penanganan retur barang dan cadangan klaim garansi",
      "primary_keyword": "sop penanganan retur barang dan cadangan klaim garansi",
      "secondary_keywords": [
        "kebijakan komplain pembeli shopee tokopedia tiktok",
        "video unboxing bukti kerusakan barang retur",
        "alokasi pos cadangan retur laporan laba rugi",
        "pemulihan inventori barang retur toko online",
        "prosedur klaim asuransi pengiriman ekspedisi"
      ],
      "search_intent": "Informational",
      "search_interest": "Tinggi (Peningkatan pencarian prosedur pengelolaan retur barang rusak/salah kirim pasca lonjakan penjualan kampanye online)",
      "trending_reason": "Rata-rata retur barang melonjak 3-5 kali lipat pasca festival belanja online. Tanpa SOP pemeriksaan barang masuk dan pos cadangan retur pada laporan laba rugi, modal kerja usaha bisa bocor akibat barang hilang atau rusak di perjalanan.",
      "credible_sources": [
        "Standar Akuntansi Keuangan Entitas Mikro Kecil Menengah (SAK EMKM)",
        "Badan Perlindungan Konsumen Nasional (BPKN)",
        "Seller Protection Guidelines Marketplace Nasional",
        "Jurnal Akuntansi dan Manajemen Ritel Indonesia"
      ],
      "msme_opportunity": "Seller memiliki mekanisme pembuktian video unboxing, alokasi pos pencadangan retur yang terukur (2-3%), dan re-stocking barang layak jual secara cepat.",
      "article_angle": "Prosedur penanganan komplain pembeli secara sopan, audit barang kembali, tata kelola akun retur dalam pembukuan, dan pengajuan klaim asuransi kurir.",
      "slug": "sop-penanganan-retur-barang-dan-cadangan-klaim-garansi",
      "assigned_category": "Keuangan & Pembukuan"
    },
    {
      "id": 297,
      "topic": "Strategi Invoice Financing dan PO Discounting untuk Modal Kerja Usaha B2B",
      "trending_query": "strategi invoice financing dan po discounting",
      "primary_keyword": "strategi invoice financing dan po discounting",
      "secondary_keywords": [
        "talangan dana purchase order resmi b2b",
        "alternatif pinjaman modal kerja selain kur bank",
        "pembiayaan anjak piutang dagang terdaftar ojk",
        "syarat pengajuan modal talangan faktur invoice",
        "manajemen tempo pembayaran vendor klien korporat"
      ],
      "search_intent": "Informational",
      "search_interest": "Tinggi (Minat pelaku UMKM B2B mencari talangan likuiditas modal produksi untuk memenuhi pesanan akhir tahun korporasi)",
      "trending_reason": "Banyak UMKM produsen, konveksi, dan katering mendapatkan pesanan akhir tahun dalam jumlah masif dari korporat/instansi dengan sistem pembayaran tempo 30-60 hari. Invoice financing dan PO financing menjadi solusi likuiditas tanpa harus menjaminkan aset tanah atau bangunan.",
      "credible_sources": [
        "Otoritas Jasa Keuangan - Regulasi Fintech P2P Lending & Multifinance",
        "Kementerian Koordinator Bidang Perekonomian RI",
        "Asosiasi Fintech Pendanaan Bersama Indonesia (AFPI)",
        "Investor Daily & Kontan Pembiayaan"
      ],
      "msme_opportunity": "Pengusaha B2B dapat menerima pesanan besar tanpa khawatir kehabisan modal beli bahan baku dengan meminjam berbasis validitas Purchase Order (PO).",
      "article_angle": "Perbedaan invoice financing vs PO financing, syarat administrasi invoice resmi, simulasi biaya bunga/ujrah diskonto, dan kiat memilih platform berizin OJK.",
      "slug": "strategi-invoice-financing-dan-po-discounting-umkm",
      "assigned_category": "Invoice, PO & SO"
    },
    {
      "id": 298,
      "topic": "Cara Menghitung Rendemen dan Yield Bahan Baku Kuliner Saat Harga Melonjak",
      "trending_query": "cara menghitung rendemen dan yield bahan baku",
      "primary_keyword": "cara menghitung rendemen dan yield bahan baku",
      "secondary_keywords": [
        "rumus yield percentage susut daging ayam sayuran",
        "kalkulasi waste bahan pangan mentah restoran",
        "resep standar bill of materials hpp kafe",
        "portion control porsi dapur rumah makan",
        "strategi hadapi kenaikan harga bahan pokok cabai telur"
      ],
      "search_intent": "Informational",
      "search_interest": "Tinggi (Lonjakan harga komoditas pangan menjelang Natal dan Tahun Baru mendorong pengusaha kuliner memperketat perhitungan porsi)",
      "trending_reason": "Harga cabai, minyak goreng, dan daging ayam mengalami fluktuasi musiman di bulan November. Pengusaha kuliner yang tidak menghitung persentase rendemen (yield bersih pasca pembersihan/pemasakan) mengalami selisih HPP riil yang membuat bisnis merugi.",
      "credible_sources": [
        "Badan Pangan Nasional (Bapanas)",
        "Persatuan Hotel dan Restoran Indonesia (PHRI)",
        "Kementerian Pertanian RI (kementan.go.id)",
        "Food Service and Culinary Management Research"
      ],
      "msme_opportunity": "Owner kafe dan restoran dapat menentukan takaran gramasi porsi presisi, mengendalikan limbah dapur (waste), dan mengamankan margin kotor di atas 65 persen.",
      "article_angle": "Rumus praktis edible portion dan yield percentage, tabel susut komoditas populer dapur nusantara, dan pembuatan resep Bill of Materials (BOM) otomatis.",
      "slug": "cara-menghitung-rendemen-dan-yield-bahan-baku-kuliner",
      "assigned_category": "HPP & Harga Jual"
    },
    {
      "id": 299,
      "topic": "SOP Blind Cash Count dan Pencegahan Fraud Kasir Shift Malam Toko Ritel",
      "trending_query": "sop blind cash count dan pencegahan fraud kasir",
      "primary_keyword": "sop blind cash count dan pencegahan fraud kasir",
      "secondary_keywords": [
        "prosedur serah terima shift kasir pos toko ritel",
        "berita acara selisih fisik uang kas kasir",
        "metode hitung uang buta tanpa lihat sistem pos",
        "pengawasan void nota dan diskon tidak wajar",
        "audit keamanan register kasir supermarket kafe"
      ],
      "search_intent": "Informational",
      "search_interest": "Tinggi (Meningkatnya kesadaran pemilik toko ritel, minimarket, dan kafe atas celah manipulasi kasir saat jam sibuk dan shift malam)",
      "trending_reason": "Toko yang ramai transaksi saat promo akhir tahun rentan terhadap manipulasi nota, void fiktif, atau selisih uang kas laci. Metode blind cash count (menghitung uang fisik tanpa mengetahui total saldo di sistem kasir) terbukti ampuh mencegah fraud.",
      "credible_sources": [
        "Asosiasi Pengusaha Ritel Indonesia (APRINDO)",
        "Audit Keamanan Ritel dan Fraud Prevention Institute",
        "Pusat Edukasi Kasir dan Manajemen Kas POS",
        "Warta Ekonomi & Bisnis Ritel"
      ],
      "msme_opportunity": "Pemilik usaha menciptakan sistem kasir yang adil dan transparan, meminimalkan selisih kas tanpa konflik internal, dan mengamankan pendapatan harian.",
      "article_angle": "Prinsip kerja blind count kasir, alur serah terima shift bertingkat, formulir berita acara selisih kas, dan pembatasan wewenang void kasir.",
      "slug": "sop-blind-cash-count-dan-pencegahan-fraud-kasir-malam",
      "assigned_category": "Kasir & POS"
    },
    {
      "id": 300,
      "topic": "Prosedur Rekonsiliasi Bank dan Tutup Buku Keuangan UMKM Akhir Tahun 2026",
      "trending_query": "prosedur rekonsiliasi bank dan tutup buku umkm",
      "primary_keyword": "prosedur rekonsiliasi bank dan tutup buku umkm",
      "secondary_keywords": [
        "langkah tutup buku tahunan pembukuan usaha kecil",
        "pencocokan mutasi rekening koran dan catatan kasir",
        "inventarisasi aset dan depresiasi barang modal toko",
        "pemutihan piutang macet laporan keuangan",
        "persiapan pelaporan spt tahunan coretax djp"
      ],
      "search_intent": "Informational",
      "search_interest": "Sangat Tinggi (Persiapan tutup buku tahun buku 2026 dan integrasi data keuangan menuju sistem Coretax DJP)",
      "trending_reason": "Memasuki pertengahan November, pelaku usaha kecil perlu mencocokkan catatan penjualan harian, mutasi rekening bank usaha, kas fisik, persediaan barang dagang, serta penyusutan alat toko sebelum menutup buku tahunan dan menyusun laporan laba rugi.",
      "credible_sources": [
        "Ikatan Akuntan Indonesia (IAI) - Kompartemen SAK EMKM",
        "Direktorat Jenderal Pajak - Tata Kelola Pembukuan Wajib Pajak",
        "Kementerian Koperasi dan UKM RI",
        "Pakar Keuangan dan Praktisi Perpajakan Indonesia"
      ],
      "msme_opportunity": "Owner usaha memiliki laporan laba rugi dan neraca yang rapi, mengetahui posisi keuntungan bersih riil setahun penuh, dan siap menghadapi musim pelaporan pajak tahunan tanpa kepanikan.",
      "article_angle": "Checklist langkah demi langkah tutup buku UMKM, metode rekonsiliasi saldo bank vs buku kas, penghitungan depresiasi aset toko, dan penyesuaian persediaan akhir.",
      "slug": "prosedur-rekonsiliasi-bank-dan-tutup-buku-umkm-2026",
      "assigned_category": "Keuangan & Pembukuan"
    },
    {
      "id": 301,
      "topic": "Cara Mengendalikan Food Waste Dapur Kafe Restoran agar HPP Aman",
      "trending_query": "cara mengendalikan food waste dapur kafe restoran",
      "primary_keyword": "cara mengendalikan food waste dapur kafe restoran",
      "secondary_keywords": [
        "audit limbah makanan dapur restoran",
        "metode fifo fefo bahan baku kuliner",
        "standar resep dan yield management masakan",
        "batch cooking jam sibuk resto",
        "cara mengatasi pembengkakan food cost kafe"
      ],
      "search_intent": "Informational",
      "search_interest": "Tinggi (Edukasi pengendalian biaya sisa bahan pangan dapur kafe resto)",
      "trending_reason": "Kebocoran terbesar operasional kuliner sering terjadi di bak cuci dan tempat sampah akibat bahan membusuk atau porsi tidak terstandar.",
      "credible_sources": ["PHRI", "Badan Pangan Nasional", "Kemenparekraf"],
      "msme_opportunity": "Menekan pemborosan makanan hingga 15-20% dan menjaga margin laba bersih restoran.",
      "article_angle": "Audit limbah harian 3 wadah, penyimpanan FIFO/FEFO, kartu resep baku, dan pemanfaatan bahan sisa olahan.",
      "slug": "cara-mengendalikan-food-waste-dapur-kafe-restoran",
      "assigned_category": "HPP & Harga Jual"
    },
    {
      "id": 302,
      "topic": "Perbedaan Izin Edar PIRT dan BPOM Olahan Pangan UMKM",
      "trending_query": "perbedaan izin edar pirt dan bpom olahan pangan umkm",
      "primary_keyword": "perbedaan izin edar pirt dan bpom olahan pangan umkm",
      "secondary_keywords": [
        "syarat pengajuan spp pirt dinkes oss",
        "kategori pangan wajib bpom md",
        "biaya sertifikasi izin edar makanan kemasan",
        "aturan label kemasan pangan umkm",
        "sanksi jualan makanan kemasan tanpa izin"
      ],
      "search_intent": "Informational",
      "search_interest": "Sangat Tinggi (Pencarian kepatuhan izin edar produk olahan pangan kemasan UMK)",
      "trending_reason": "Penertiban produk pangan olahan di supermarket dan marketplace mendorong produsen pangan rumahan memastikan legalitas izin edar PIRT vs BPOM MD.",
      "credible_sources": ["BPOM RI (pom.go.id)", "Kementerian Kesehatan RI", "Kementerian Investasi / BKPM"],
      "msme_opportunity": "Memperluas jangkauan distribusi produk ke jaringan ritel modern secara legal dan aman.",
      "article_angle": "Tabel perbandingan PIRT vs BPOM MD, alur izin OSS, syarat label kemasan, dan sanksi hukum.",
      "slug": "perbedaan-izin-edar-pirt-dan-bpom-olahan-pangan-umkm",
      "assigned_category": "Pajak & Legalitas"
    },
    {
      "id": 303,
      "topic": "Panduan Pelaporan SPT Tahunan UMKM Melalui Coretax DJP",
      "trending_query": "panduan pelaporan spt tahunan umkm coretax djp",
      "primary_keyword": "panduan pelaporan spt tahunan umkm coretax djp",
      "secondary_keywords": [
        "cara lapor spt tahunan orang pribadi pp 55 2022",
        "format npwp 16 digit nik login coretax",
        "pencatatan peredaran bruto bulanan umkm",
        "kode otorisasi djp tanda tangan elektronik spt",
        "solusi kendala validasi data pajak coretax"
      ],
      "search_intent": "Informational",
      "search_interest": "Sangat Tinggi (Persiapan implementasi sistem perpajakan baru Coretax untuk pelaporan SPT tahunan 2026)",
      "trending_reason": "DJP mengintegrasikan seluruh sistem administrasi ke portal Coretax yang mensyaratkan validasi NPWP 16 digit dan tanda tangan digital kode otorisasi.",
      "credible_sources": ["Direktorat Jenderal Pajak (pajak.go.id)", "Pusat Penyuluhan Pajak DJP", "Ortax"],
      "msme_opportunity": "Wajib pajak UMKM terhindar dari sanksi denda keterlambatan pelaporan dan memahami pembebasan omzet s/d 500 juta rupiah.",
      "article_angle": "Panduan teknis langkah lapor SPT di Coretax, rekapitulasi peredaran bruto, dan pengiriman bukti tanda terima elektronik.",
      "slug": "panduan-pelaporan-spt-tahunan-umkm-coretax-djp",
      "assigned_category": "Pajak & Legalitas"
    },
    {
      "id": 304,
      "topic": "SOP Stok Opname Akhir Tahun Toko Ritel Tanpa Selisih",
      "trending_query": "sop stok opname akhir tahun toko ritel",
      "primary_keyword": "sop stok opname akhir tahun toko ritel",
      "secondary_keywords": [
        "cara menghitung selisih fisik barang dan kasir pos",
        "metode stock count berkelanjutan ritel",
        "formulir berita acara penyesuaian inventori",
        "pencegahan kehilangan barang shrinkage toko",
        "teknik freeze stok saat penghitungan barang"
      ],
      "search_intent": "Informational",
      "search_interest": "Tinggi (Persiapan audit fisik persediaan barang dagang tutup tahun)",
      "trending_reason": "Toko ritel dan minimarket wajib mencocokkan nilai persediaan fisik per 31 Desember untuk pelaporan neraca dan pencegahan inventory shrinkage.",
      "credible_sources": ["APRINDO", "Ikatan Akuntan Indonesia (SAK EMKM)", "Jurnal Ritel Indonesia"],
      "msme_opportunity": "Menemukan selisih stok secara cepat, mengidentifikasi dead stock, dan menyajikan nilai persediaan akurat.",
      "article_angle": "Tahapan H-7 pra opname, pembagian tim penghitung berpasangan, barcode scanner, dan formulir berita acara selisih.",
      "slug": "sop-stok-opname-akhir-tahun-toko-ritel",
      "assigned_category": "Inventori & Stok"
    },
    {
      "id": 305,
      "topic": "Strategi Menjaga Arus Kas Toko UMKM Menghadapi Libur Akhir Tahun",
      "trending_query": "strategi menjaga arus kas toko umkm libur akhir tahun",
      "primary_keyword": "strategi menjaga arus kas toko umkm libur akhir tahun",
      "secondary_keywords": [
        "manajemen likuiditas kas operasional libur panjang",
        "dana cadangan belanja modal awal tahun",
        "penjadwalan pembayaran supplier sebelum tutup bank",
        "pengelolaan uang tunai kasir libur natal tahun baru",
        "simulasi cash flow harian toko ritel kafe"
      ],
      "search_intent": "Informational",
      "search_interest": "Tinggi (Kesiapan likuiditas menghadapi libur operasional bank dan lonjakan transaksi liburan)",
      "trending_reason": "Libur panjang akhir tahun menyebabkan kliring perbankan tertunda sementara kebutuhan kas belanja stok dan pembayaran gaji meningkat.",
      "credible_sources": ["Bank Indonesia", "Otoritas Jasa Keuangan", "Asosiasi Pengusaha Indonesia (APINDO)"],
      "msme_opportunity": "Menjaga kelancaran kas harian dan mengamankan dana belanja modal awal tahun tanpa krisis likuiditas.",
      "article_angle": "Perkiraan arus kas 30 hari liburan, pelunasan utang supplier H-5, alokasi cash holding kasir, dan penarikan saldo digital.",
      "slug": "strategi-menjaga-arus-kas-toko-umkm-libur-akhir-tahun",
      "assigned_category": "Keuangan & Pembukuan"
    },
    {
      "id": 306,
      "topic": "Manajemen Pesanan Omnichannel Marketplace dan Toko Fisik",
      "trending_query": "manajemen pesanan omnichannel marketplace dan toko fisik",
      "primary_keyword": "manajemen pesanan omnichannel marketplace dan toko fisik",
      "secondary_keywords": [
        "integrasi stok pesanan multi channel shopee tiktok pos",
        "sistem sinkronisasi inventori terpusat cloud",
        "alur picking packing order online dari toko fisik",
        "pencegahan dobel booking stok produk promo",
        "software order management system umkm"
      ],
      "search_intent": "Informational",
      "search_interest": "Tinggi (Peningkatan adopsi penjualan hybrid toko fisik dan e-commerce)",
      "trending_reason": "Banyak peritel mengoperasikan toko fisik sekaligus live TikTok Shop dan Shopee, menimbulkan kerumitan pemenuhan order dari satu stok bersama.",
      "credible_sources": ["idEA", "Supply Chain Indonesia", "McKinsey Digital Indonesia Report"],
      "msme_opportunity": "Toko fisik berfungsi sebagai mini distribution center dengan waktu pemenuhan pesanan lebih cepat dan stok sinkron 100%.",
      "article_angle": "Arsitektur stok omnichannel, SOP pengambilan barang terpadu, pencegahan pembatalan order, dan software cloud terpusat.",
      "slug": "manajemen-pesanan-omnichannel-marketplace-dan-toko-fisik",
      "assigned_category": "Strategi & Operasional"
    },
    {
      "id": 307,
      "topic": "SOP Penerimaan Pembayaran QRIS Dinamis pada Kasir POS",
      "trending_query": "sop penerimaan pembayaran qris dinamis kasir pos",
      "primary_keyword": "sop penerimaan pembayaran qris dinamis kasir pos",
      "secondary_keywords": [
        "perbedaan qris statis dan qris dinamis nominal otomatis",
        "pencegahan bukti transfer palsu struk kasir",
        "kecepatan transaksi antrean kasir jam sibuk",
        "settlement harian dana qris ke rekening toko",
        "integrasi layar kasir customer display qris"
      ],
      "search_intent": "Informational",
      "search_interest": "Tinggi (Migrasi kasir ritel dan F&B dari QRIS stiker statis ke QRIS dinamis layar)",
      "trending_reason": "Maraknya penipuan bukti transfer palsu dan salah ketik nominal oleh pembeli mendorong toko ritel menerapkan QRIS dinamis berlayar kasir.",
      "credible_sources": ["Bank Indonesia - Departemen Kebijakan Sistem Pembayaran", "ASPI", "Fintech Association"],
      "msme_opportunity": "Transaksi kasir 3 kali lebih cepat, zero salah hitung nominal, dan bebas risiko struk transfer palsu.",
      "article_angle": "Kelebihan QRIS dinamis, SOP verifikasi visual kasir, customer display monitor, dan rekonsiliasi settlement.",
      "slug": "sop-penerimaan-pembayaran-qris-dinamis-kasir-pos",
      "assigned_category": "Kasir & POS"
    },
    {
      "id": 308,
      "topic": "Cara Menyusun Struktur dan Skala Upah Karyawan Toko UMKM",
      "trending_query": "cara menyusun struktur dan skala upah karyawan toko umkm",
      "primary_keyword": "cara menyusun struktur dan skala upah karyawan toko umkm",
      "secondary_keywords": [
        "aturan upah usaha mikro kecil 50 persen umk",
        "rumus penentuan gaji pokok tunjangan kasir pramuniaga",
        "komponen insentif penjualan komisi toko ritel",
        "permenaker nomor 1 tahun 2017 struktur skala upah",
        "evaluasi jabatan staf toko berdasarkan masa kerja"
      ],
      "search_intent": "Informational",
      "search_interest": "Tinggi (Penetapan upah minimum 2027 dan kepatuhan struktur upah proporsional)",
      "trending_reason": "Kementerian Ketenagakerjaan mewajibkan perusahaan menyusun struktur skala upah yang adil berdasarkan golongan jabatan dan masa kerja.",
      "credible_sources": ["Kementerian Ketenagakerjaan RI (kemnaker.go.id)", "Depenas", "BPS"],
      "msme_opportunity": "Menyusun skema gaji yang memotivasi karyawan tanpa membebani keuangan toko secara berlebihan.",
      "article_angle": "Ketentuan upah usaha mikro (50% rata-rata konsumsi / 25% di atas garis kemiskinan), tabel skala gaji kasir/staf, dan skema insentif performa.",
      "slug": "cara-menyusun-struktur-dan-skala-upah-karyawan-toko-umkm",
      "assigned_category": "Strategi & Operasional"
    },
    {
      "id": 309,
      "topic": "Panduan Pengajuan KUR Syariah untuk Modal Kerja Usaha Mikro",
      "trending_query": "panduan pengajuan kur syariah modal kerja usaha mikro",
      "primary_keyword": "panduan pengajuan kur syariah modal kerja usaha mikro",
      "secondary_keywords": [
        "syarat pinjaman kur syariah bsi tanpa riba",
        "margin ujrah subsidi pemerintah setara 3 persen",
        "plafon kur super mikro sampai 10 juta tanpa agunan",
        "dokumen sku nib ktp syarat kur syariah",
        "tata cara pengajuan online pembiayaan syariah umkm"
      ],
      "search_intent": "Informational",
      "search_interest": "Sangat Tinggi (Pencarian pembiayaan modal kerja syariah bebas riba menjelang awal tahun)",
      "trending_reason": "Banyak pengusaha muslim mencari pembiayaan modal kerja syariah dengan subsidi margin pemerintah untuk memperluas kapasitas usaha.",
      "credible_sources": ["Bank Syariah Indonesia (BSI)", "Kemenko Perekonomian - Komite Kebijakan KUR", "DSN MUI"],
      "msme_opportunity": "Mengakses pembiayaan modal kerja hingga 10-50 juta tanpa agunan fisik dengan angsuran ringan dan akad halal.",
      "article_angle": "Akad murabahah/ijarah KUR syariah, syarat dokumen NIB/KTP, simulasi tabel angsuran bulanan, dan tips lolos verifikasi bank.",
      "slug": "panduan-pengajuan-kur-syariah-modal-kerja-usaha-mikro",
      "assigned_category": "Modal"
    },
    {
      "id": 310,
      "topic": "Strategi Follow-up Keranjang Belanja Terbengkalai via WhatsApp UMKM",
      "trending_query": "strategi follow up keranjang belanja terbengkalai whatsapp umkm",
      "primary_keyword": "strategi follow up keranjang belanja terbengkalai whatsapp",
      "secondary_keywords": [
        "cara mengatasi abandoned cart checkout toko online",
        "template pesan wa pengingat pesanan belum bayar",
        "pemberian voucher diskon terbatas pengingat keranjang",
        "otomasi broadcast whatsapp gateway toko",
        "menaikkan tingkat konversi checkout pesanan"
      ],
      "search_intent": "Informational",
      "search_interest": "Tinggi (Optimalisasi pemulihan transaksi gagal bayar pasca festival belanja)",
      "trending_reason": "Rata-rata 60-70% pembeli memasukkan produk ke keranjang belanja namun tidak melanjutkan pembayaran karena lupa atau ragu.",
      "credible_sources": ["Baymard Institute E-Commerce Research", "WhatsApp Business Solutions", "Asosiasi Ritel Digital"],
      "msme_opportunity": "Memulihkan 15-25% pesanan yang batal menjadi penjualan berhasil dengan pesan WhatsApp otomatis yang personal.",
      "article_angle": "Waktu terbaik kirim pengingat (menit 30, jam 6, jam 24), skrip pesan santun, penawaran gratis ongkir, dan integrasi WhatsApp Gateway.",
      "slug": "strategi-follow-up-keranjang-belanja-terbengkalai-whatsapp-umkm",
      "assigned_category": "Marketing & WhatsApp"
    }
  ]
}

keyword_data = {
  "research_date": "2026-11-09",
  "batch": 4,
  "timezone": "Asia/Jakarta",
  "platform": "Cooca (cooca.id)",
  "total_articles": 20,
  "target_audience": [
    "Seller marketplace Shopee, Tokopedia, dan TikTok Shop yang bersiap kampanye Harbolnas 11.11 dan 12.12",
    "Pemilik toko ritel fisik dan online yang mengelola stok persediaan multi-channel",
    "Pengusaha online yang menghadapi jeda pencairan dana penjualan escrow platform",
    "Pelaku usaha mikro makanan minuman yang memerlukan kepastian regulasi relaksasi wajib halal SEHATI",
    "Manajer operasional dan supervisor gudang e-commerce yang menangani proses packing kilat ekspedisi",
    "Staf keuangan dan pemilik toko yang menyusun SOP retur barang dan komplain pembeli",
    "Pengusaha B2B, vendor, dan supplier yang membutuhkan pembiayaan talangan purchase order",
    "Owner kafe, resto, dan katering yang mengelola kalkulasi rendemen yield bahan pangan mentah",
    "Supervisor kasir dan pengelola toko ritel yang mengawasi keamanan register kas shift malam",
    "Pemilik usaha yang bersiap melakukan rekonsiliasi bank dan tutup buku keuangan tahunan 2026"
  ]
}

def main():
    out_dir = os.path.join("database", "data", "articles")
    os.makedirs(out_dir, exist_ok=True)
    
    trending_file = os.path.join(out_dir, "trending-research-2026-11-09.json")
    keyword_file = os.path.join(out_dir, "keyword-research-2026-11-09.json")
    
    with open(trending_file, "w", encoding="utf-8") as f:
        json.dump(trending_data, f, indent=2, ensure_ascii=False)
    print(f"Generated {trending_file}")
    
    with open(keyword_file, "w", encoding="utf-8") as f:
        json.dump(keyword_data, f, indent=2, ensure_ascii=False)
    print(f"Generated {keyword_file}")

if __name__ == "__main__":
    main()
