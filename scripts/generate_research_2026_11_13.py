# scripts/generate_research_2026_11_13.py
import json, os

def generate_research():
    out_dir = os.path.join("database", "data", "articles")
    os.makedirs(out_dir, exist_ok=True)
    
    trending = {
        "date": "2026-11-13",
        "batch": 10,
        "geo": "ID",
        "category": "3",
        "category_name": "Tren Bisnis, Logistik Musim Hujan, Kepatuhan Tutup Buku & Persiapan Nataru UMKM",
        "source": "Google Trends Indonesia 13 November 2026, Evaluasi Pasca 11.11, Persiapan 12.12 & Nataru 2027, Regulasi Coretax DJP & SAK EMKM",
        "total_topics": 20,
        "topics": [
            {
                "id": 512,
                "topic": "Tata Cara Validasi NIK Menjadi NPWP 16 Digit untuk Transaksi B2B Toko Ritel dan Grosir",
                "trending_query": "validasi nik npwp 16 digit transaksi b2b toko ritel",
                "primary_keyword": "validasi nik npwp 16 digit transaksi b2b",
                "secondary_keywords": "format npwp 16 digit djp, cara cek nik terdaftar npwp coretax, faktur pajak b2b toko kelontong, validasi npwp badan usaha rekanan, aturan faktur pajak elektronik 2026",
                "search_intent": "informational",
                "search_interest": "Sangat Tinggi",
                "trending_reason": "Menjelang tenggat implementasi penuh sistem Coretax DJP, pelaku usaha toko ritel dan grosir wajib memvalidasi identitas NIK dan NPWP 16 digit seluruh rekanan B2B agar faktur pajak elektronik tidak ditolak sistem perpajakan nasional.",
                "credible_sources": [
                    "Direktorat Jenderal Pajak Kementerian Keuangan Republik Indonesia",
                    "Peraturan Menteri Keuangan Tata Cara Penggunaan NIK sebagai NPWP"
                ],
                "msme_opportunity": "Menghindari reject faktur pajak masukan dan keluaran, mempermudah pelaporan SPT Masa, serta meningkatkan kredibilitas di mata pembeli institusional.",
                "article_angle": "Panduan langkah demi langkah verifikasi identitas pajak klien korporat, pencatatan otomatis di master data pelanggan, dan integrasi penomoran faktur.",
                "slug": "tata-cara-validasi-nik-npwp-16-digit-transaksi-b2b",
                "assigned_category": "Pajak & Legalitas"
            },
            {
                "id": 513,
                "topic": "Langkah Praktis Persiapan Tutup Buku Tahun Buku 2026 bagi UMKM Berdasarkan Standar SAK EMKM",
                "trending_query": "persiapan tutup buku tahun 2026 umkm sak emkm",
                "primary_keyword": "persiapan tutup buku tahun 2026 umkm",
                "secondary_keywords": "cara tutup buku akhir tahun usaha kecil, laporan keuangan sak emkm sederhana, neraca laba rugi tahunan umkm, rekonsiliasi akun kas bank akhir tahun, checklist tutup buku akuntansi",
                "search_intent": "informational",
                "search_interest": "Sangat Tinggi",
                "trending_reason": "Memasuki pertengahan November, pemilik usaha mulai mempersiapkan tahapan tutup buku tahunan untuk memastikan laporan laba rugi, neraca, dan arus kas tercatat rapi sebelum pergantian tahun buku 2027.",
                "credible_sources": [
                    "Ikatan Akuntan Indonesia (IAI) - Standar Akuntansi Keuangan Entitas Mikro, Kecil, dan Menengah (SAK EMKM)",
                    "Panduan Tata Kelola Keuangan UMKM Kemenkop UKM"
                ],
                "msme_opportunity": "Menghasilkan laporan keuangan tahunan yang valid untuk pengajuan kredit usaha perbankan dan dasar penghitungan SPT Tahunan yang akurat.",
                "article_angle": "Checklist penyesuaian jurnal akrual, rekonsiliasi saldo kas opname fisik, dan rekapitulasi penjualan lintas saluran toko.",
                "slug": "persiapan-tutup-buku-tahun-2026-umkm-sak-emkm",
                "assigned_category": "Keuangan & Pembukuan"
            },
            {
                "id": 514,
                "topic": "Strategi Penagihan dan Manajemen Piutang Dagang Net-30 Menjelang Akhir Tahun Buku",
                "trending_query": "manajemen piutang dagang akhir tahun umkm tempo net 30",
                "primary_keyword": "manajemen piutang dagang akhir tahun umkm",
                "secondary_keywords": "cara menagih invoice jatuh tempo elegan, sop penagihan piutang usaha grosir, aging schedule piutang umkm, contoh surat penagihan tempo net 30, mitigasi kredit macet pelanggan",
                "search_intent": "informational",
                "search_interest": "Tinggi",
                "trending_reason": "Banyak pemasok dan distributor grosir UMKM menghadapi risiko piutang macet di akhir tahun jika tidak segera menertibkan tagihan invoice tempo 30 hari sebelum libur panjang Nataru.",
                "credible_sources": [
                    "Kamar Dagang dan Industri Indonesia (KADIN) Bidang UMKM",
                    "Jurnal Manajemen Risiko Keuangan Ritel Indonesia"
                ],
                "msme_opportunity": "Mengamankan likuiditas kas operasional, mencairkan dana yang tertahan di pelanggan, dan meminimalkan pencadangan piutang tak tertagih.",
                "article_angle": "Pembuatan tabel aging schedule umur piutang, otomasi reminder WhatsApp sopan, dan pemberian diskon pelunasan lebih awal (early payment discount).",
                "slug": "manajemen-piutang-dagang-akhir-tahun-umkm-net-30",
                "assigned_category": "Invoice, PO & SO"
            },
            {
                "id": 515,
                "topic": "SOP Inventarisasi Aset Fisik Toko dan Perhitungan Penyusutan Mesin Kasir Peralatan Usaha",
                "trending_query": "sop inventarisasi aset fisik toko umkm depresiasi mesin kasir",
                "primary_keyword": "sop inventarisasi aset fisik toko umkm",
                "secondary_keywords": "cara menghitung penyusutan peralatan toko, daftar inventaris aset tetap warung, metode garis lurus depresiasi aset, perawatan mesin kasir pos barcode, berita acara inventarisasi fisik",
                "search_intent": "informational",
                "search_interest": "Sedang",
                "trending_reason": "Penghitungan nilai aset tetap seperti chiller, mesin POS, rak etalase, dan kendaraan operasional wajib dicatat depresiasinya untuk mencerminkan nilai wajar neraca akhir tahun.",
                "credible_sources": [
                    "Pedoman Penilaian Aset Usaha Mikro Kecil Indonesia",
                    "Standar Pelaporan Aset Tetap SAK EMKM"
                ],
                "msme_opportunity": "Mengetahui nilai riil aset usaha, merencanakan anggaran peremajaan alat, dan mencatatkan biaya penyusutan sebagai pengurang laba kena pajak yang sah.",
                "article_angle": "Panduan pembuatan kartu identitas aset, penomoran kode barcode stiker aset, dan simulasi rumus depresiasi garis lurus sederhana.",
                "slug": "sop-inventarisasi-aset-fisik-toko-dan-depresiasi-alat",
                "assigned_category": "Keuangan & Pembukuan"
            },
            {
                "id": 516,
                "topic": "SOP Pengelolaan Gudang Ritel Saat Musim Hujan: Mencegah Kerusakan Barang Lembap dan Jamur",
                "trending_query": "sop penyimpanan stok gudang musim hujan anti lembab",
                "primary_keyword": "sop penyimpanan stok gudang musim hujan",
                "secondary_keywords": "cara mencegah jamur kardus gudang ritel, palet kayu standar penyimpanan barang, sirkulasi udara gudang cuaca hujan, proteksi kemasan plastik produk kering, tata letak gudang anti banjir",
                "search_intent": "informational",
                "search_interest": "Sangat Tinggi",
                "trending_reason": "Tingginya curah hujan di pertengahan November meningkatkan kelembapan udara gudang, menyebabkan kardus lembek, kemasan rusak, dan potensi timbulnya jamur pada bahan baku maupun barang ritel.",
                "credible_sources": [
                    "Asosiasi Logistik Indonesia (ALI)",
                    "Panduan Higiene Pergudangan dan Logistik Pangan Nasional"
                ],
                "msme_opportunity": "Menekan rasio kerugian stok akibat kerusakan fisik (shrinkage/spoilage) dan memastikan produk tetap dalam kondisi prima saat dikirim ke konsumen.",
                "article_angle": "Standar penataan palet berjarak 15 cm dari lantai dan 30 cm dari dinding, pemanfaatan desiccant gel silika, dan jadwal inspeksi atap bocor.",
                "slug": "sop-penyimpanan-stok-gudang-musim-hujan-anti-lembab",
                "assigned_category": "Inventori & Stok"
            },
            {
                "id": 517,
                "topic": "Manajemen Efisiensi Rute dan Keamanan Pengiriman Pesanan Toko saat Cuaca Ekstrem",
                "trending_query": "manajemen rute delivery toko musim hujan cuaca ekstrem",
                "primary_keyword": "manajemen rute delivery toko musim hujan",
                "secondary_keywords": "pengiriman pesanan toko saat hujan, packing anti air kurir toko, mitigasi keterlambatan paket cuaca buruk, optimasi rute kurir pengantaran mandiri, asuransi paket kurir instan",
                "search_intent": "informational",
                "search_interest": "Tinggi",
                "trending_reason": "Genangan air dan hujan lebat kerap melumpuhkan pengiriman pesanan lokal; toko yang memiliki armada kurir mandiri membutuhkan strategi routing fleksibel dan proteksi ganda pada paket.",
                "credible_sources": [
                    "Masyarakat Transportasi Indonesia (MTI) Logistik Perkotaan",
                    "Pedoman Manajemen Keselamatan Kurir Pengantaran Barang"
                ],
                "msme_opportunity": "Mempertahankan kepuasan pelanggan lewat ketepatan waktu pengantaran dan kondisi paket yang tetap kering 100 persen.",
                "article_angle": "Standar packing kantong plastik berlapis, pemetaan titik banjir lokal via Google Maps, dan pesan update pengiriman otomatis via WhatsApp.",
                "slug": "manajemen-armada-dan-rute-delivery-toko-musim-hujan",
                "assigned_category": "Strategi & Operasional"
            },
            {
                "id": 518,
                "topic": "Cara Menghitung Safety Stock Bahan Baku F&B saat Fluktuasi Pasokan Musim Penghujan",
                "trending_query": "strategi safety stock kuliner musim hujan bahan baku",
                "primary_keyword": "strategi safety stock kuliner musim hujan",
                "secondary_keywords": "rumus safety stock bahan segar kuliner, cara mengatasi harga cabai sayur naik hujan, lead time pesanan supplier resto, reorder point bumbu dapur kafe, food waste manajemen f&b",
                "search_intent": "informational",
                "search_interest": "Tinggi",
                "trending_reason": "Hujan deras sering memicu gagal panen dan keterlambatan distribusi sayuran segar, cabai, dan telur, membuat pelaku usaha kuliner terjepit antara kelangkaan stok dan lonjakan harga modal.",
                "credible_sources": [
                    "Badan Pangan Nasional (Bapanas) Pemantauan Pasokan Pasar",
                    "Perhimpunan Hotel dan Restoran Indonesia (PHRI) Kuliner Divisi Operasional"
                ],
                "msme_opportunity": "Mencegah terjadinya kehabisan menu andalan (stockout) tanpa harus menanggung risiko pembusukan bahan baku yang berlebihan.",
                "article_angle": "Rumus perhitungan buffer stock berbasis lead time supplier, diversifikasi menu alternatif musiman, dan teknik pengawetan bahan secara higienis.",
                "slug": "strategi-safety-stock-bahan-baku-kuliner-musim-hujan",
                "assigned_category": "HPP & Harga Jual"
            },
            {
                "id": 519,
                "topic": "Strategi Pengadaan dan Manajemen Stok Fast-Moving Toko Menghadapi Harbolnas 12.12",
                "trending_query": "strategi persiapan stok harbolnas 12 12 toko ritel online",
                "primary_keyword": "strategi persiapan stok harbolnas 12 12",
                "secondary_keywords": "cara kulakan barang promo 12 12, daftar barang fast moving akhir tahun, perhitungan reorder point harbolnas, manajemen pergudangan flash sale, alokasi stok omnichannel marketplace",
                "search_intent": "informational",
                "search_interest": "Sangat Tinggi",
                "trending_reason": "Promo puncak Harbolnas 12.12 menuntut persiapan stok sejak pertengahan November; keterlambatan kulakan dapat menyebabkan pesanan dibatalkan akibat kekosongan barang.",
                "credible_sources": [
                    "Asosiasi E-Commerce Indonesia (idEA) Riset Harbolnas",
                    "Laporan Tren Belanja Akhir Tahun Konsumen Digital Indonesia"
                ],
                "msme_opportunity": "Meraih omzet maksimal pada festival belanja terbesar tanpa terjebak dalam perangkap dead stock pasca promo selesai.",
                "article_angle": "Metode analisis ABC Pareto untuk memilah 20 persen produk penyumbang 80 persen omzet, serta penetapan batas minimum pemesanan ulang (ROP).",
                "slug": "strategi-persiapan-stok-toko-menjelang-harbolnas-12-12",
                "assigned_category": "Inventori & Stok"
            },
            {
                "id": 520,
                "topic": "SOP Kasir dan Manajemen Antrean Cepat di Toko Ritel Saat Puncak Belanja Akhir Tahun",
                "trending_query": "sop kasir penanganan antrean peak season ritel ramai",
                "primary_keyword": "sop kasir penanganan antrean peak season",
                "secondary_keywords": "cara mempercepat transaksi kasir pos, manajemen antrean pembayaran qris, split antrean kasir tunai non tunai, sop sapaan kasir toko ramai, mitigasi kasir lambat antrean panjang",
                "search_intent": "informational",
                "search_interest": "Tinggi",
                "trending_reason": "Lonjakan jumlah pengunjung toko fisik di akhir pekan menjelang libur akhir tahun kerap memicu antrean mengular di meja kasir, menyebabkan pembeli membatalkan pembelian jika proses scan lambat.",
                "credible_sources": [
                    "Asosiasi Pengusaha Ritel Indonesia (APRINDO)",
                    "Pedoman Layanan Frontliner Ritel Modern Indonesia"
                ],
                "msme_opportunity": "Meningkatkan throughput transaksi per jam, mempersingkat waktu tunggu pembeli, dan menciptakan pengalaman belanja yang menyenangkan.",
                "article_angle": "Penerapan kasir bayangan menggunakan tablet POS portabel, pemisahan kasir tunai dan QRIS instan, serta teknik packing kilat kasir.",
                "slug": "sop-kasir-dan-penanganan-antrean-panjang-peak-season",
                "assigned_category": "Kasir & POS"
            },
            {
                "id": 521,
                "topic": "Strategi Perhitungan HPP dan Bundling Produk Paket Hampers Natal dan Tahun Baru 2027",
                "trending_query": "strategi bundling hampers natal tahun baru untung maksimal",
                "primary_keyword": "strategi bundling hampers natal tahun baru",
                "secondary_keywords": "cara hitung hpp hampers kue kering, harga jual paket parsel natal umkm, biaya kemasan hardbox pita kartu ucapan, trik bundling produk laris dan kurang laris, margin keuntungan bisnis hampers",
                "search_intent": "informational",
                "search_interest": "Sangat Tinggi",
                "trending_reason": "Pemesanan hampers corporate dan personal untuk perayaan Natal dan Tahun Baru mulai dibuka di bulan November; kesalahan perhitungan HPP kemasan dapat menggerus margin laba secara drastis.",
                "credible_sources": [
                    "Komunitas Pengusaha Industri Kreatif dan Kuliner Indonesia",
                    "Riset Tren Desain Kemasan dan Hadiah Musiman"
                ],
                "msme_opportunity": "Menjual paket hadiah bernilai tambah tinggi dengan margin keuntungan bersih di atas 40 persen melalui kemasan yang memikat.",
                "article_angle": "Kalkulasi Activity-Based Costing untuk biaya ornamen pita, kartu personal, dan kemasan boks, serta rumus penetapan tiering harga hampers.",
                "slug": "strategi-bundling-hampers-natal-tahun-baru-untung-maksimal",
                "assigned_category": "HPP & Harga Jual"
            },
            {
                "id": 522,
                "topic": "Panduan Manajemen Purchase Order dan Sales Order Paket Bingkisan Perusahaan Akhir Tahun",
                "trending_query": "manajemen order corporate gift akhir tahun po b2b",
                "primary_keyword": "manajemen order corporate gift akhir tahun",
                "secondary_keywords": "alur sales order b2b hampers kantor, contoh invoice uang muka dp 50 persen, surat jalan pengiriman corporate gift, penagihan pelunasan invoice perusahaan, kontrak kerja sama bingkisan karyawan",
                "search_intent": "informational",
                "search_interest": "Tinggi",
                "trending_reason": "Order pengadaan bingkisan perusahaan berskala ratusan paket memerlukan tata kelola PO, pencatatan termin pembayaran uang muka (DP), dan surat jalan resmi agar pesanan tidak kacau saat produksi massal.",
                "credible_sources": [
                    "Pedoman Tata Kelola Transaksi Pengadaan Korporat Indonesia",
                    "Standar Administrasi Penjualan B2B UMKM"
                ],
                "msme_opportunity": "Mengamankan kontrak pesanan bervolume besar dari instansi swasta dan pemerintah dengan alur dokumen legal yang tertata profesional.",
                "article_angle": "Penerbitan Sales Order mengikat, mekanisme uang muka bertermin, dan pencocokan fisik barang dengan surat jalan saat serah terima di kantor klien.",
                "slug": "manajemen-order-b2b-corporate-gift-akhir-tahun",
                "assigned_category": "Invoice, PO & SO"
            },
            {
                "id": 523,
                "topic": "Cara Kirim Pesan Siaran WhatsApp Promo Akhir Tahun Aman Tanpa Diblokir Meta",
                "trending_query": "broadcast whatsapp akhir tahun anti banned meta toko",
                "primary_keyword": "broadcast whatsapp akhir tahun anti banned",
                "secondary_keywords": "cara blast wa promo tidak diblokir, whatsapp business api gateway resmi, jadwal interval kirim pesan wa massal, personalisasi pesan whatsapp broadcast, sop copywriting promo wa aman",
                "search_intent": "informational",
                "search_interest": "Sangat Tinggi",
                "trending_reason": "Meta semakin memperketat algoritma deteksi spam WhatsApp di kuartal akhir; pemilik toko yang mengirim pesan broadcast secara serampangan terancam pemblokiran permanen nomor usaha.",
                "credible_sources": [
                    "Panduan Resmi Kebijakan Pengiriman Pesan WhatsApp Business Platform",
                    "Riset Konversi Pesan Instan Asosiasi Pemasaran Digital Indonesia"
                ],
                "msme_opportunity": "Menjangkau ribuan kontak pelanggan lama dengan tingkat keterbacaan di atas 90 persen tanpa membahayakan aset nomor WhatsApp toko.",
                "article_angle": "Penerapan jeda waktu dinamis, penyertaan opsi opt-out berhenti menerima info promo, dan pemanfaatan integrasi WhatsApp Gateway resmi.",
                "slug": "cara-broadcast-whatsapp-akhir-tahun-anti-banned",
                "assigned_category": "Marketing & WhatsApp"
            },
            {
                "id": 524,
                "topic": "Cara Merancang Program Poin Member dan Penukaran Hadiah Akhir Tahun untuk Retensi Pembeli",
                "trending_query": "program poin loyalitas reward akhir tahun retensi pelanggan",
                "primary_keyword": "program poin loyalitas reward akhir tahun",
                "secondary_keywords": "cara buat program membership toko ritel, reward poin belanja akhir tahun, trik meningkatkan repeat order pelanggan, kedaluwarsa poin belanja toko, sistem loyalitas pelanggan kasir pos",
                "search_intent": "informational",
                "search_interest": "Sedang",
                "trending_reason": "Akhir tahun adalah momentum terbaik untuk mengajak pelanggan menukarkan sisa poin loyalitas mereka sebelum masa berlaku hangus, sekaligus memicu transaksi pembelian baru di toko.",
                "credible_sources": [
                    "Riset Perilaku Konsumen Loyalty Program Asosiasi Ritel",
                    "Studi Retensi Pelanggan Bisnis Mikro Indonesia"
                ],
                "msme_opportunity": "Mendongkrak omzet toko fisik hingga 30 persen melalui kunjungan ulang anggota member setia dan memperpanjang customer lifetime value.",
                "article_angle": "Struktur konversi poin ke voucher diskon belanja, penawaran hadiah fisik edisi terbatas, dan notifikasi pengingat saldo poin via WhatsApp.",
                "slug": "program-poin-loyalitas-tukar-reward-akhir-tahun-ritel",
                "assigned_category": "CRM & Loyalty"
            },
            {
                "id": 525,
                "topic": "Strategi Menyapa Ulang Database Pelanggan Pasif Melalui Penawaran Eksklusif WhatsApp",
                "trending_query": "strategi reaktivasi pelanggan lama whatsapp akhir tahun",
                "primary_keyword": "strategi reaktivasi pelanggan lama whatsapp",
                "secondary_keywords": "cara menyapa konsumen pasif tidak pernah beli, template wa reaktivasi pembeli lama, diskon khusus pelanggan setia lama, segmentasi database kontak wa pelanggan, teknik follow up sopan",
                "search_intent": "informational",
                "search_interest": "Tinggi",
                "trending_reason": "Biaya mengakuisisi pelanggan baru 5 kali lebih mahal daripada mempertahankan pelanggan lama; menyapa kembali database kontak pelanggan pasif yang tidak belanja 3 sampai 6 bulan terakhir terbukti menghasilkan penjualan cepat.",
                "credible_sources": [
                    "Harvard Business Review Analisis Retensi Pelanggan",
                    "Laporan Konversi Pemasaran Berbasis Basis Data Lokal"
                ],
                "msme_opportunity": "Menghidupkan kembali omzet dari database kontak telepon yang selama ini menganggur tanpa mengeluarkan biaya iklan berbayar.",
                "article_angle": "Teknik segmentasi pelanggan berdasarkan riwayat pembelian terakhir, template pesan sapaan hangat personal, dan tawaran insentif belanja menarik.",
                "slug": "strategi-reaktivasi-database-pelanggan-lama-lewat-wa",
                "assigned_category": "Marketing & WhatsApp"
            },
            {
                "id": 526,
                "topic": "Cara Mengelola Retur Paket COD Bisnis Online dan Rekonsiliasi Gudang",
                "trending_query": "cara mengelola retur paket cod bisnis online rekonsiliasi gudang",
                "primary_keyword": "cara mengelola retur paket cod bisnis online",
                "secondary_keywords": "sop penanganan barang retur toko online, rekonsiliasi paket cod gagal kirim rts, klaim ongkir paket retur ekspedisi, pemilahan stok retur rusak dan layak jual, pencatatan inventori retur",
                "search_intent": "informational",
                "search_interest": "Tinggi",
                "trending_reason": "Lonjakan pesanan pasca promo kembar selalu diiringi peningkatan retur paket Return to Sender (RTS) akibat alamat tidak lengkap atau pembeli menolak membayar saat kurir tiba di tempat.",
                "credible_sources": [
                    "Asosiasi Logistik dan Pengiriman Ritel Indonesia",
                    "Pedoman Manajemen Retur Penjual Online idEA"
                ],
                "msme_opportunity": "Mencegah hilangnya barang retur di tangan ekspedisi, menjaga akurasi stok nyata di gudang, dan memulihkan modal barang yang masih layak jual.",
                "article_angle": "SOP unboxing paket retur di bawah kamera pengawas, pengkategorian barang grade A dan grade B, serta penyesuaian kartu stok di sistem.",
                "slug": "cara-mengelola-retur-paket-cod-dan-rekonsiliasi-stok",
                "assigned_category": "Inventori & Stok"
            },
            {
                "id": 527,
                "topic": "Strategi Cash Flow Pacing Kulakan Grosir Menghadapi Belanja Akhir Tahun",
                "trending_query": "strategi cash flow pacing kulakan grosir belanja akhir tahun",
                "primary_keyword": "strategi cash flow pacing kulakan grosir",
                "secondary_keywords": "cara mengatur arus kas kulakan toko, manajemen likuiditas kas operasional ritel, jadwal pembayaran tempo supplier pabrik, alokasi modal kerja musim liburan, simulasi cash flow harian toko",
                "search_intent": "informational",
                "search_interest": "Tinggi",
                "trending_reason": "Banyak toko ritel mengalami krisis likuiditas kas operasional di bulan Desember karena seluruh uang tunai habis terpakai untuk kulakan stok barang tanpa menyisakan cadangan biaya operasional harian.",
                "credible_sources": [
                    "Bank Indonesia Petunjuk Manajemen Likuiditas Usaha Kecil",
                    "Pedoman Arus Kas Usaha Mikro dan Menengah"
                ],
                "msme_opportunity": "Menjaga keseimbangan antara kecukupan persediaan barang dagang dan ketersediaan uang kas untuk membayar gaji, sewa, serta kewajiban operasional.",
                "article_angle": "Penerapan formula alokasi kas bertahap (pacing), negosiasi perpanjangan termin pembayaran supplier grosir, dan proyeksi penerimaan kas harian kasir.",
                "slug": "strategi-cash-flow-pacing-alokasi-dana-kulakan-grosir",
                "assigned_category": "Keuangan & Pembukuan"
            },
            {
                "id": 528,
                "topic": "SOP Pengecekan Rutin dan Kalibrasi Scanner Barcode serta Printer Struk Kasir Ritel",
                "trending_query": "sop kalibrasi hardware pos toko ritel scanner printer thermal",
                "primary_keyword": "sop kalibrasi hardware pos toko ritel",
                "secondary_keywords": "cara bersihkan print head printer struk kasir, kalibrasi barcode scanner kasir lambat, pemeliharaan laci uang kasir pos drawer, kertas struk thermal macet toko ritel, checklist teknis hardware kasir",
                "search_intent": "informational",
                "search_interest": "Sedang",
                "trending_reason": "Perangkat keras kasir yang bermasalah seperti printer thermal macet atau barcode scanner lambat membaca kode barang dapat memicu kekacauan saat toko sedang dipadati pembeli di akhir tahun.",
                "credible_sources": [
                    "Standar Teknis Pemeliharaan Perangkat Kasir Elektronik",
                    "Panduan Perawatan Hardware POS Asosiasi Ritel"
                ],
                "msme_opportunity": "Menghilangkan risiko downtime transaksi, memperpanjang usia pakai printer dan scanner, serta menjaga kelancaran operasional toko 100 persen.",
                "article_angle": "Langkah pembersihan sensor debu printer menggunakan alkohol isopropil, setting sensitivitas scanner barcode 2D QR, dan pengujian kabel konektor cadangan.",
                "slug": "sop-pengecekan-dan-kalibrasi-hardware-pos-toko-ritel",
                "assigned_category": "Kasir & POS"
            },
            {
                "id": 529,
                "topic": "Panduan Pengaturan Shift Kerja Lembur dan Pembagian Insentif Karyawan Libur Nataru",
                "trending_query": "manajemen shift lembur karyawan toko nataru insentif",
                "primary_keyword": "manajemen shift lembur karyawan toko nataru",
                "secondary_keywords": "aturan upah lembur tanggal merah kemnaker, jadwal kerja shift karyawan toko libur akhir tahun, bonus insentif penjualan kasir pramuniaga, cara mencegah kelelahan staf toko ritel, roster kerja libur natal tahun baru",
                "search_intent": "informational",
                "search_interest": "Tinggi",
                "trending_reason": "Operasional toko yang buka penuh selama libur Natal dan Tahun Baru membutuhkan roster jadwal shift yang adil serta perhitungan insentif lembur yang transparan sesuai aturan ketenagakerjaan.",
                "credible_sources": [
                    "Kementerian Ketenagakerjaan Republik Indonesia Regulasi Jam Kerja dan Lembur",
                    "Pedoman Manajemen Sumber Daya Manusia Ritel Modern"
                ],
                "msme_opportunity": "Menjaga motivasi dan loyalitas karyawan garda depan, mencegah kejenuhan kerja (burnout), serta mematuhi aturan legal ketenagakerjaan secara tepat.",
                "article_angle": "Simulasi penyusunan matriks shift 3 kelompok kerja, formula kompensasi lembur hari libur nasional, dan penyediaan fasilitas penunjang stamina tim.",
                "slug": "manajemen-shift-kerja-lembur-karyawan-toko-nataru",
                "assigned_category": "Strategi & Operasional"
            },
            {
                "id": 530,
                "topic": "SOP Serah Terima Kasir dan Rekonsiliasi Saldo Kas Fisik Laci Saat Pergantian Shift",
                "trending_query": "sop serah terima kasir pergantian shift toko ritel tanpa selisih",
                "primary_keyword": "sop serah terima kasir pergantian shift",
                "secondary_keywords": "cara hitung modal awal laci kasir float, rekonsiliasi uang fisik laporan z kasir pos, berita acara serah terima kasir ritel, mengatasi selisih kas kasir toko, prosedur setor uang penjualan harian",
                "search_intent": "informational",
                "search_interest": "Tinggi",
                "trending_reason": "Pergantian staf kasir di tengah keramaian toko rawan memicu kebocoran uang atau selisih kas jika proses serah terima saldo fisik laci tidak diverifikasi dengan ketat.",
                "credible_sources": [
                    "Asosiasi Manajemen Kas dan Pengendalian Internal Ritel",
                    "Standar Prosedur Operasional Keamanan Kasir Minimarket"
                ],
                "msme_opportunity": "Menutup celah kecurangan (fraud) kasir, menjamin akurasi saldo uang tunai fisik laci, dan menetapkan tanggung jawab yang jelas antar staf yang bertugas.",
                "article_angle": "Prosedur blind cash count (penghitungan kas buta), pencetakan laporan ringkasan shift di aplikasi POS, dan penandatanganan rangkap dua form serah terima.",
                "slug": "sop-serah-terima-kasir-pergantian-shift-toko-ritel",
                "assigned_category": "Kasir & POS"
            },
            {
                "id": 531,
                "topic": "Cara Evaluasi Kinerja Supplier Bahan Baku Berdasarkan Ketepatan Waktu dan Kualitas Pasokan",
                "trending_query": "evaluasi kinerja supplier bahan baku umkm akhir tahun kontrak",
                "primary_keyword": "evaluasi kinerja supplier bahan baku umkm",
                "secondary_keywords": "scorecard penilaian vendor pemasok bahan baku, kriteria evaluasi supplier toko resto, negosiasi harga kontrak supplier 2027, cara mengatasi keterlambatan pengiriman bahan, surat teguran vendor tidak sesuai spesifikasi",
                "search_intent": "informational",
                "search_interest": "Sedang",
                "trending_reason": "Menjelang pembaruan kontrak pasokan tahun 2027, pelaku usaha UMKM perlu mengevaluasi rekam jejak setiap pemasok bahan baku untuk memilah mitra terbaik dan menegosiasikan harga yang lebih kompetitif.",
                "credible_sources": [
                    "Ikatan Ahli Pengadaan Indonesia (IAPI) Panduan Pengadaan Swasta",
                    "Pedoman Manajemen Rantai Pasok Usaha Mikro Kecil"
                ],
                "msme_opportunity": "Mendapatkan jaminan pasokan berkualitas tinggi dengan harga termurah, termin pembayaran lebih longgar, dan ketepatan waktu pengiriman terjamin.",
                "article_angle": "Penyusunan tabel Vendor Performance Scorecard dengan bobot 4 pilar (kualitas, lead time pengiriman, kestabilan harga, dan respon komunikasi).",
                "slug": "cara-evaluasi-kinerja-supplier-bahan-baku-akhir-tahun",
                "assigned_category": "Invoice, PO & SO"
            }
        ]
    }
    
    keywords = {
        "date": "2026-11-13",
        "batch": 10,
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
    
    trending_path = os.path.join(out_dir, "trending-research-2026-11-13.json")
    keyword_path = os.path.join(out_dir, "keyword-research-2026-11-13.json")
    
    with open(trending_path, "w", encoding="utf-8") as f:
        json.dump(trending, f, indent=2, ensure_ascii=False)
        
    with open(keyword_path, "w", encoding="utf-8") as f:
        json.dump(keywords, f, indent=2, ensure_ascii=False)
        
    print(f"Generated {trending_path} and {keyword_path} successfully!")

if __name__ == "__main__":
    generate_research()
