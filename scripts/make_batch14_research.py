# scripts/make_batch14_research.py
import json
import os

data = {
    "date": "2026-11-17",
    "batch": 14,
    "geo": "ID",
    "category": "3",
    "category_name": "Tren Bisnis UMKM, Penetapan UMP 2027 Pasca Putusan MK, Kesiapan Harbolnas 12.12, Tutup Buku SAK EMKM Akhir Tahun, Mitigasi Cuaca Ekstrem BMKG & Invoicing B2B",
    "source": "Google Trends Indonesia 17 November 2026, Putusan Mahkamah Konstitusi No 168/PUU-XXI/2023, PP No 49 Tahun 2025 Kemenaker, SAK EMKM Ikatan Akuntan Indonesia, BMKG Peringatan Cuaca Ekstrem",
    "total_topics": 20,
    "topics": [
        {
            "id": 719,
            "topic": "Penetapan Upah Minimum Sektor Usaha Mikro dan Kecil (UMK) Pasca Putusan Mahkamah Konstitusi dan PP 49 Tahun 2025: Aturan Kesepakatan Upah di Atas Garis Kemiskinan",
            "trending_query": "aturan upah minimum umkm pasca putusan mk kemnaker pp 49 2025",
            "primary_keyword": "aturan upah minimum umkm pasca putusan mk",
            "secondary_keywords": "pengecualian upah minimum usaha mikro kecil, kesepakatan upah pekerja ukm di atas garis kemiskinan, sanksi uwr usaha menengah jika langgar ump, putusan mk 168 puu xxi 2023 pengupahan, permenaker pengupahan terbaru dewan pengupahan daerah",
            "search_intent": "informational",
            "search_interest": "Sangat Tinggi",
            "trending_reason": "Menjelang pengumuman resmi UMP 2026-2027 oleh para gubernur pada akhir November menyusul Putusan MK No. 168/PUU-XXI/2023 dan penerbitan PP No. 49 Tahun 2025, jutaan pemilik UMKM mencari kepastian batas hukum kewajiban gaji karyawan toko dan staf operasional mereka.",
            "credible_sources": [
                "Mahkamah Konstitusi Republik Indonesia Putusan No 168/PUU-XXI/2023",
                "Kementerian Ketenagakerjaan Republik Indonesia",
                "Peraturan Pemerintah Nomor 49 Tahun 2025 tentang Pengupahan"
            ],
            "msme_opportunity": "Memahami batasan pengecualian UMP bagi skala mikro dan kecil sehingga tidak panik, menyusun kesepakatan tertulis yang sah di mata Disnaker, dan merancang skema insentif produktivitas yang adil.",
            "article_angle": "Kupas tuntas klausul pengecualian UMP untuk usaha dengan modal di bawah Rp1 miliar dan Rp5 miliar, batas bawah berbasis persentase konsumsi rata-rata BPS, format surat kesepakatan upah, dan perhitungan lembur resmi.",
            "slug": "aturan-upah-minimum-umkm-pasca-putusan-mk-pp-49-2025",
            "assigned_category": "Pajak & Legalitas"
        },
        {
            "id": 720,
            "topic": "Strategi Capacity Planning Pengemasan Harbolnas 12.12: Tata Letak Meja Packing U-Shape dan Alur Batch Shipping",
            "trending_query": "strategi packing gudang harbolnas 12 12 alur batch shipping u shape",
            "primary_keyword": "strategi packing gudang harbolnas 12 12",
            "secondary_keywords": "tata letak meja packing u shape toko online, alur batch shipping hindari antrean ekspedisi, mitigasi salah tempel resi flash sale, manajemen stok bahan packing lakban bubble wrap, sop tim packing part time borongan harbolnas",
            "search_intent": "informational",
            "search_interest": "Sangat Tinggi",
            "trending_reason": "Kurang dari sebulan menjelang festival belanja terbesar Harbolnas 12.12, penjual online marketplace menghadapi risiko bottleneck pengemasan dan keterlambatan penyerahan paket ke kurir ekspedisi.",
            "credible_sources": [
                "Asosiasi Logistik Indonesia (ALI)",
                "Pedoman Efisiensi Pergudangan Ritel Modern",
                "Kementerian Perdagangan Republik Indonesia - Harbolnas 2026"
            ],
            "msme_opportunity": "Mencegah penalti keterlambatan pengiriman toko, menaikkan kecepatan packing hingga 300 persen, dan menghemat biaya tenaga kerja tambahan.",
            "article_angle": "Panduan rancang bangun meja packing U-Shape, pengelompokan batch resi per kurir (J&T, SiCepat, JNE, Shopee Xpress), teknik barcode scan verification sebelum segel paket, dan buffer stok kemasan minimal 3 kali lipat.",
            "slug": "strategi-packing-gudang-harbolnas-12-12-alur-batch-shipping",
            "assigned_category": "Inventori & Stok"
        },
        {
            "id": 721,
            "topic": "SOP Stock Opname Tutup Buku Akhir Tahun Standar SAK EMKM: Menghitung Persediaan Akhir dan Penyusutan Aset",
            "trending_query": "sop stock opname tutup buku akhir tahun sak emkm hitung persediaan",
            "primary_keyword": "sop stock opname tutup buku akhir tahun sak emkm",
            "secondary_keywords": "cara hitung nilai persediaan akhir fifo toko, penyesuaian susut barang rusak kedaluwarsa opname, jurnal penyesuaian selisih stok gudang, perhitungan penyusutan aset tetap metode garis lurus, berita acara stock opname tutup buku",
            "search_intent": "informational",
            "search_interest": "Sangat Tinggi",
            "trending_reason": "Menjelang penutupan buku tahunan per 31 Desember, akuntan dan pemilik usaha wajib mencocokkan fisik stok di gudang dan toko dengan catatan kartu stok untuk menyajikan nilai persediaan akhir yang valid di neraca.",
            "credible_sources": [
                "Ikatan Akuntan Indonesia (IAI) - Standar Akuntansi Keuangan Entitas Mikro Kecil dan Menengah (SAK EMKM)",
                "Direktorat Jenderal Pajak - Pedoman Penilaian Persediaan Usaha",
                "Kementerian Koperasi dan UKM RI"
            ],
            "msme_opportunity": "Menghasilkan laporan keuangan yang akurat untuk pengajuan kredit perbankan, mendeteksi kebocoran stok internal, dan mengklaim biaya kerugian barang rusak secara legal di pembukuan.",
            "article_angle": "Tahapan pembagian tim penghitung independen, lembar kerja blind counting, penentuan nilai persediaan metode FIFO, pembuatan jurnal penyesuaian selisih stok, dan perhitungan depresiasi peralatan kasir/mesin.",
            "slug": "sop-stock-opname-tutup-buku-akhir-tahun-sak-emkm",
            "assigned_category": "Inventori & Stok"
        },
        {
            "id": 722,
            "topic": "Cara Menyusun 3 Komponen Laporan Keuangan SAK EMKM Akhir Tahun: Neraca, Laba Rugi, dan Catatan Atas Laporan Keuangan",
            "trending_query": "cara menyusun laporan keuangan sak emkm akhir tahun neraca laba rugi calk",
            "primary_keyword": "cara menyusun laporan keuangan sak emkm akhir tahun",
            "secondary_keywords": "format neraca posisi keuangan sak emkm, laporan laba rugi usaha kecil akhir periode, contoh catatan atas laporan keuangan calk umkm, cara pisahkan prive modal pemilik dari laba, syarat laporan keuangan bank kur dan perizinan",
            "search_intent": "informational",
            "search_interest": "Sangat Tinggi",
            "trending_reason": "Banyak pemilik UMKM belum memahami bahwa SAK EMKM hanya mewajibkan 3 komponen laporan sederhana tanpa perlu laporan arus kas rumit, sehingga kerap menunda tutup buku tahunan.",
            "credible_sources": [
                "Dewan Standar Akuntansi Keuangan Ikatan Akuntan Indonesia (DSAK IAI)",
                "Otoritas Jasa Keuangan (OJK) Literasi Keuangan UMKM",
                "Kementerian Keuangan RI"
            ],
            "msme_opportunity": "Merapikan rekam jejak keuangan usaha sehingga bankable, mempermudah pelaporan SPT Tahunan Badan di Coretax DJP, dan mengetahui laba bersih riil yang sebenarnya.",
            "article_angle": "Panduan langkah demi langkah memindahkan saldo buku besar ke Laporan Laba Rugi dan Neraca Posisi Keuangan, rumus keseimbangan Aset = Liabilitas + Ekuitas, serta draf narasi CaLK sederhana.",
            "slug": "cara-menyusun-laporan-keuangan-sak-emkm-akhir-tahun",
            "assigned_category": "Keuangan & Pembukuan"
        },
        {
            "id": 723,
            "topic": "Manajemen Penagihan Piutang Usaha B2B Akhir Tahun: Strategi Early Bird Discount dan SOP Somasi Ramah",
            "trending_query": "manajemen penagihan piutang b2b akhir tahun early bird tempo net 30",
            "primary_keyword": "manajemen penagihan piutang b2b akhir tahun",
            "secondary_keywords": "diskon pelunasan cepat 2 per 10 net 30 b2b, surat peringatan jatuh tempo ramah via whatsapp, cara atasi klien korporat tunda pembayaran akhir tahun, mitigasi risiko piutang macet bad debt tutup buku, rekonsiliasi faktur pajak dan invoice b2b",
            "search_intent": "informational",
            "search_interest": "Tinggi",
            "trending_reason": "Bulan November dan Desember adalah periode kritis di mana banyak mitra bisnis B2B sengaja menunda pembayaran faktur demi menjaga likuiditas mereka sendiri, mengancam arus kas UMKM supplier.",
            "credible_sources": [
                "Kementerian Perindustrian Republik Indonesia",
                "Asosiasi Manajemen Kredit dan Keuangan Usaha",
                "Pedoman Pengelolaan Piutang Dagang UMKM"
            ],
            "msme_opportunity": "Mencairkan dana tertahan sebelum pergantian tahun, menjaga modal kerja pengadaan stok awal tahun, dan meminimalkan kerugian piutang tak tertagih.",
            "article_angle": "Terapkan skema potongan 2/10 Net 30, jadwal penagihan berjenjang H-7, H-3, Hari H, dan H+3 via WhatsApp terotomasi, serta draf surat konfirmasi saldo piutang resmi.",
            "slug": "manajemen-penagihan-piutang-b2b-akhir-tahun-anti-macet",
            "assigned_category": "Invoice, PO & SO"
        },
        {
            "id": 724,
            "topic": "Mitigasi Risiko Lonjakan Retur COD Marketplace Musim Hujan: Screening Nomor HP WhatsApp dan Blacklist Pembeli Nakal",
            "trending_query": "mitigasi retur cod marketplace musim hujan screening whatsapp pembeli",
            "primary_keyword": "mitigasi retur cod marketplace musim hujan",
            "secondary_keywords": "cara verifikasi pesanan cod fiktif via whatsapp, cek riwayat retur pembeli shopee lazada tiktok, ongkir retur barang rusak akibat hujan siapa yang tanggung, sop klaim barang basah ke kurir ekspedisi, batasi nominal cod maksimal 200 ribu",
            "search_intent": "informational",
            "search_interest": "Sangat Tinggi",
            "trending_reason": "Kombinasi cuaca ekstrem hujan lebat dan lonjakan transaksi Harbolnas meningkatkan angka Return to Origin (RTO) hingga 25 persen karena kurir gagal antar dan pembeli menolak paket di tempat.",
            "credible_sources": [
                "Asosiasi E-Commerce Indonesia (idEA)",
                "Badan Perlindungan Konsumen Nasional (BPKN)",
                "Data Operasional Ekspedisi Logistik Terpadu 2026"
            ],
            "msme_opportunity": "Menekan tingkat retur COD dari 18 persen ke bawah 4 persen, menyelamatkan margin laba kotor, dan melindungi stok dari kerusakan fisik akibat perjalanan bolak-balik.",
            "article_angle": "Alur otomatisasi konfirmasi pesanan COD melalui WhatsApp resmi sebelum packing, cara menganalisis skor keberhasilan kirim pembeli, dan SOP video unboxing klaim asuransi ekspedisi.",
            "slug": "mitigasi-retur-cod-marketplace-musim-hujan-cek-whatsapp",
            "assigned_category": "Marketing & WhatsApp"
        },
        {
            "id": 725,
            "topic": "Kalkulasi Biaya Kemasan Extra Protection Musim Hujan: Plastik Polymailer, Bubble Wrap Ganda, dan Silica Gel ke HPP Produk",
            "trending_query": "kalkulasi biaya kemasan musim hujan ke hpp produk polymailer silica gel",
            "primary_keyword": "kalkulasi biaya kemasan musim hujan ke hpp produk",
            "secondary_keywords": "biaya packaging tambahan anti air musim hujan, cara alokasi harga bubble wrap dan lakban ke hpp, penggunaan silica gel food grade cegah jamur, penyesuaian harga jual toko online saat musim hujan, margin aman biaya kemasan 5 persen",
            "search_intent": "informational",
            "search_interest": "Tinggi",
            "trending_reason": "Banyak penjual makanan kering, pakaian, dan aksesoris mendapati produk mereka basah saat diterima pelanggan di musim hujan, namun belum memperhitungkan biaya proteksi kemasan ekstra ke dalam HPP produk.",
            "credible_sources": [
                "Indonesian Packaging Federation (IPF)",
                "Pedoman Standarisasi Pengemasan Produk Ekspor dan Domestik",
                "Kementerian Koperasi dan UKM RI"
            ],
            "msme_opportunity": "Mencegah komplain barang rusak atau berjamur tanpa mengorbankan margin laba bersih karena seluruh biaya bahan pelindung sudah dialokasikan presisi.",
            "article_angle": "Simulasi matematis pemakaian plastik polymailer kedap air, bubble wrap 3 lapis, dan sachet silica gel per paket pengiriman, serta formula memasukkannya ke dalam Activity-Based Costing HPP.",
            "slug": "kalkulasi-biaya-kemasan-musim-hujan-ke-hpp-produk",
            "assigned_category": "HPP & Harga Jual"
        },
        {
            "id": 726,
            "topic": "Sistem Sinkronisasi Stok Real-Time Kasir POS dan Multi-Marketplace Harbolnas 12.12 untuk Mencegah Penalti Toko",
            "trending_query": "sinkronisasi stok kasir pos dan marketplace harbolnas 12 12 real time",
            "primary_keyword": "sinkronisasi stok kasir pos dan marketplace harbolnas 12 12",
            "secondary_keywords": "cara atasi overselling flash sale shopee tokopedia, software omnichannel stok terpusat toko offline online, integrasi api order management system kasir pos, penalti pembatalan pesanan otomatis marketplace, alokasi safety stock per saluran penjualan",
            "search_intent": "informational",
            "search_interest": "Sangat Tinggi",
            "trending_reason": "Penjualan serentak di toko fisik dan berbagai platform marketplace saat Harbolnas berisiko tinggi memicu overselling (barang fisik habis di toko tetapi masih terjual online), yang berujung poin penalti dan pembekuan toko.",
            "credible_sources": [
                "Pusat Riset Perdagangan Digital Indonesia",
                "Ketentuan Operasional Seller Marketplace Nasional 2026",
                "Studi Kasus Efisiensi Omnichannel Ritel Indonesia"
            ],
            "msme_opportunity": "Mengamankan skor performa toko di atas 95 persen, mengunci predikat Star Seller / Mall, dan memangkas waktu input stok manual yang melelahkan.",
            "article_angle": "Mekanisme kerja sinkronisasi stok otomatis di bawah 1 detik, strategi penetapan safety stock cadangan kasir 10 persen, dan SOP locking inventori saat jam sibuk flash sale.",
            "slug": "sinkronisasi-stok-kasir-pos-dan-marketplace-harbolnas-12-12",
            "assigned_category": "Kasir & POS"
        },
        {
            "id": 727,
            "topic": "Evaluasi Biaya Operasional dan Efisiensi Listrik Mesin Produksi Menjelang Akhir Tahun untuk Tekan Biaya Overhead",
            "trending_query": "evaluasi biaya operasional dan overhead akhir tahun umkm efisiensi listrik",
            "primary_keyword": "evaluasi biaya operasional dan overhead akhir tahun",
            "secondary_keywords": "cara audit biaya overhead pabrikasi dan kafe, tips hemat tarif listrik industri golongan bisnis umkm, efisiensi konsumsi daya kulkas chiller dan oven, eliminasi pengeluaran operasional boros akhir tahun, perhitungan opex terhadap omzet bulanan",
            "search_intent": "informational",
            "search_interest": "Tinggi",
            "trending_reason": "Menjelang tahun 2027, pemilik usaha manufaktur kecil, laundry, dan kuliner menghadapi kenaikan tarif dasar listrik dan biaya pemeliharaan mesin, sehingga membutuhkan audit overhead menyeluruh.",
            "credible_sources": [
                "PT PLN (Persero) Tarif Bisnis dan Industri Menengah",
                "Kementerian Energi dan Sumber Daya Mineral RI",
                "Panduan Pengendalian Biaya Operasional UMKM"
            ],
            "msme_opportunity": "Menekan rasio biaya operasional terhadap omzet hingga 5-8 persen, memperpanjang usia pakai mesin produksi, dan menambah margin laba bersih usaha.",
            "article_angle": "Audit beban daya listrik peralatan, teknik penjadwalan pemakaian mesin pada jam luar beban puncak (WBP), SOP perawatan berkala evaporator chiller, dan pembukuan akun beban overhead pabrik.",
            "slug": "evaluasi-biaya-operasional-dan-overhead-akhir-tahun-umkm",
            "assigned_category": "HPP & Harga Jual"
        },
        {
            "id": 728,
            "topic": "Panduan Rekonsiliasi Settlement Marketplace Akhir Tahun: Mencocokkan Saldo Dompet Seller, Potongan Admin, dan Biaya Iklan",
            "trending_query": "rekonsiliasi settlement marketplace akhir tahun toko online saldo dompet",
            "primary_keyword": "rekonsiliasi settlement marketplace akhir tahun",
            "secondary_keywords": "cara mencocokkan saldo escrow shopee tokopedia ke rekening bank, audit potongan fee admin komisi transaksi marketplace, pencatatan biaya iklan saldo promosi di laba rugi, penanganan dana tertahan atau pending settlement, format excel rekonsiliasi kas e-commerce",
            "search_intent": "informational",
            "search_interest": "Sangat Tinggi",
            "trending_reason": "Banyak seller online mengalami selisih antara angka omzet kotor di dashboard seller center dengan uang riil yang masuk ke rekening bank karena tumpukan biaya admin, komisi gratis ongkir, dan biaya iklan.",
            "credible_sources": [
                "Pedoman Pelaporan Pajak dan Akuntansi E-Commerce DJP",
                "Ketentuan Pembayaran Finansial Marketplace Indonesia 2026",
                "Ikatan Akuntan Indonesia (IAI)"
            ],
            "msme_opportunity": "Mengetahui secara transparan apakah toko benar-benar untung bersih setelah dipotong komisi platform hingga 8-12 persen, serta mengklaim hak dana pesanan selesai yang tersangkut.",
            "article_angle": "Langkah download laporan settlement escrow, pemisahan akun potongan admin dan iklan di jurnal pengeluaran, penelusuran status dana pending di atas 14 hari, dan rekonsiliasi ke mutasi bank.",
            "slug": "rekonsiliasi-settlement-marketplace-akhir-tahun-toko-online",
            "assigned_category": "Keuangan & Pembukuan"
        },
        {
            "id": 729,
            "topic": "SOP Serah Terima Kasir Saat Pergantian Shift Malam dan Pengamanan Brankas Uang Tunai Selama Musim Libur Nataru",
            "trending_query": "sop serah terima kasir shift malam libur nataru brankas uang tunai",
            "primary_keyword": "sop serah terima kasir shift malam libur nataru",
            "secondary_keywords": "berita acara serah terima shift kasir laci kas, prosedur setoran modal awal laci kasir pagi, kunci ganda brankas toko dan akses cctv kasir, pencegahan pencurian uang toko musim libur panjang, mitigasi uang palsu pecahan 50 ribu dan 100 ribu",
            "search_intent": "informational",
            "search_interest": "Tinggi",
            "trending_reason": "Musim liburan Natal dan Tahun Baru membawa lonjakan transaksi tunai di toko fisik dan kafe hingga larut malam, meningkatkan risiko salah hitung kasir maupun tindak kejahatan pencurian kas.",
            "credible_sources": [
                "Pedoman Keamanan Retail dan Toko Modern",
                "Bank Indonesia - Pedoman Keaslian Uang Rupiah (3D)",
                "Asosiasi Pengusaha Ritel Indonesia (APRINDO)"
            ],
            "msme_opportunity": "Mengeliminasi 100 persen selisih uang kas saat pergantian shift, mengamankan aset kas tunai di brankas penyimpanan, dan melindungi kasir dari intimidasi atau fitnah kehilangan uang.",
            "article_angle": "Formulir checklist serah terima shift malam, pembatasan uang standby di laci kas maksimal Rp500.000 dengan cash drop periodik, SOP verifikasi uang palsu sinar UV, dan protokol kunci brankas dua orang saksi.",
            "slug": "sop-serah-terima-kasir-shift-malam-dan-brankas-libur-nataru",
            "assigned_category": "Kasir & POS"
        },
        {
            "id": 730,
            "topic": "Strategi Promo Tukar Poin Member Loyalitas Akhir Tahun untuk Mendorong Belanja Ulang dan Menghabiskan Saldo Poin Pelanggan",
            "trending_query": "strategi promo tukar poin loyalitas member akhir tahun belanja ulang",
            "primary_keyword": "strategi promo tukar poin loyalitas member akhir tahun",
            "secondary_keywords": "kebijakan poin member kedaluwarsa 31 desember, cara kirim notifikasi sisa poin via whatsapp kasir, reward hadiah menarik tukar poin pelanggan setia, meningkatkan basket size belanja dengan voucher poin, sistem tier membership loyalitas toko",
            "search_intent": "informational",
            "search_interest": "Tinggi",
            "trending_reason": "Akhir tahun adalah waktu paling efektif untuk mengaktifkan kembali database pelanggan pasif melalui momentum penukaran poin reward sebelum saldo hangus pada 31 Desember.",
            "credible_sources": [
                "Customer Loyalty Institute Indonesia",
                "Asosiasi CRM dan Pemasaran Berkelanjutan",
                "Data Retensi Pelanggan Ritel Indonesia"
            ],
            "msme_opportunity": "Menciptakan lonjakan omzet di bulan Desember tanpa biaya iklan baru, mengosongkan liabilitas saldo poin di neraca, dan mempererat hubungan emosional dengan pembeli setia.",
            "article_angle": "Desain katalog reward penukaran poin berjenjang, automasi blast WhatsApp pengingat saldo poin H-30 dan H-7, penetapan nilai tukar 1 poin setara Rp100, dan syarat minimal belanja tambahan saat klaim hadiah.",
            "slug": "strategi-promo-tukar-poin-loyalitas-member-akhir-tahun",
            "assigned_category": "CRM & Loyalty"
        },
        {
            "id": 731,
            "topic": "Standar SOP Penerimaan Barang Retur dan Barang Cacat dari Pelanggan: Karantina Stok, Klaim Supplier, dan Jurnal Akuntansi",
            "trending_query": "sop penerimaan barang retur dan klaim supplier toko karantina cacat",
            "primary_keyword": "sop penerimaan barang retur dan klaim supplier",
            "secondary_keywords": "cara isolasi stok karantina barang reject gudang, nota retur debit memo pengembalian ke pemasok, verifikasi bukti nota struk pembelian pelanggan, jurnal akuntansi retur penjualan dan pengembalian kas, penggantian barang garansi toko resmi",
            "search_intent": "informational",
            "search_interest": "Tinggi",
            "trending_reason": "Lonjakan volume penjualan akhir tahun berbanding lurus dengan peningkatan barang cacat produksi atau retur konsumen yang jika tidak dikelola dengan SOP ketat akan mencemari stok siap jual.",
            "credible_sources": [
                "Pedoman Manajemen Mutu Pergudangan dan Ritel",
                "Ikatan Akuntan Indonesia (IAI) - Akuntansi Retur Penjualan",
                "Undang-Undang Perlindungan Konsumen No 8 Tahun 1999"
            ],
            "msme_opportunity": "Menjaga kepuasan pembeli yang kecewa, memastikan barang cacat berhasil diklaim ganti rugi 100 persen ke pabrik/supplier, dan mencegah salah jual barang rusak.",
            "article_angle": "Alur 4 tahap penanganan retur (cek nota, periksa fisik di area karantina, penerbitan voucher ganti rugi atau uang kembali), penerbitan Nota Retur Pembelian ke supplier, dan pencatatan debitur/kreditur.",
            "slug": "sop-penerimaan-barang-retur-dan-klaim-supplier-toko",
            "assigned_category": "Inventori & Stok"
        },
        {
            "id": 732,
            "topic": "Cara Menghitung Target Omzet dan Break Even Point (BEP) Cabang Baru untuk Rencana Ekspansi Bisnis 2027",
            "trending_query": "cara menghitung target omzet dan bep cabang baru 2027 ekspansi toko",
            "primary_keyword": "cara menghitung target omzet dan bep cabang baru",
            "secondary_keywords": "rumus bep unit dan bep rupiah usaha ritel kafe, estimasi biaya sewa tempat ruko dan renovasi cabang, kalkulasi modal kerja minimum 6 bulan operasional, penentuan target omzet harian karyawan cabang, payback period investasi buka cabang baru",
            "search_intent": "informational",
            "search_interest": "Sangat Tinggi",
            "trending_reason": "Menjelang perencanaan anggaran bisnis tahun 2027, banyak pengusaha UMKM berencana membuka gerai atau cabang kedua namun kebingungan menghitung batas aman titik impas dan target penjualan harian.",
            "credible_sources": [
                "Kementerian Koperasi dan Usaha Kecil Menengah RI",
                "Asosiasi Franchise dan Lisensi Indonesia (AFI)",
                "Pedoman Studi Kelayakan Bisnis Ritel Modern"
            ],
            "msme_opportunity": "Menghindari kegagalan fatal bangkrut di cabang baru, menentukan lokasi sewa yang sesuai dengan kemampuan cash flow, dan menetapkan target penjualan realistis per staf.",
            "article_angle": "Simulasi penghitungan biaya tetap (sewa, gaji, listrik) vs biaya variabel (HPP bahan), rumus BEP Rupiah dan BEP Porsi, perhitungan dana darurat 6 bulan, dan rasio Payback Period di bawah 18 bulan.",
            "slug": "cara-menghitung-target-omzet-dan-bep-cabang-baru-2027",
            "assigned_category": "HPP & Harga Jual"
        },
        {
            "id": 733,
            "topic": "Panduan Pembuatan Surat Jalan Resmi dan Delivery Order untuk Pengiriman Barang Pesanan Grosir B2B",
            "trending_query": "panduan surat jalan dan delivery order grosir b2b bukti serah terima",
            "primary_keyword": "panduan surat jalan dan delivery order grosir b2b",
            "secondary_keywords": "format surat jalan resmi 3 rangkap toko grosir, beda purchase order delivery order surat jalan faktur, tanda tangan stempel penerima barang bast sah, mitigasi barang hilang atau kurang saat bongkar muat, lampiran surat jalan untuk penagihan invoice",
            "search_intent": "informational",
            "search_interest": "Tinggi",
            "trending_reason": "Pengiriman pesanan grosir bernilai puluhan juta rupiah ke toko langganan sering memicu sengketa penagihan jika surat jalan tidak memuat tanda tangan penerima yang sah dan rincian fisik yang jelas.",
            "credible_sources": [
                "Kitab Undang-Undang Hukum Dagang (KUHD) tentang Bukti Pengangkutan",
                "Kementerian Perdagangan RI Pedoman Distribusi Barang",
                "Asosiasi Distributor Indonesia"
            ],
            "msme_opportunity": "Menjamin penagihan invoice cair tepat waktu tanpa sanggahan barang kurang, memudahkan pelacakan armada supir logistik, dan mengamankan kepastian hukum transaksi B2B.",
            "article_angle": "Anatomi dokumen Surat Jalan yang sah (nomor PO, rincian kuantitas, nomor polisi kendaraan, jam tiba), SOP alur 3 rangkap (gudang, supir, customer), dan checklist pemeriksaan fisik saat bongkar muat.",
            "slug": "panduan-surat-jalan-dan-delivery-order-pengiriman-grosir-b2b",
            "assigned_category": "Invoice, PO & SO"
        },
        {
            "id": 734,
            "topic": "Strategi Menu Engineering Kafe dan Resto Akhir Tahun: Mengeliminasi Menu Dog dan Mengoptimalkan Menu Star",
            "trending_query": "strategi menu engineering kafe resto akhir tahun eliminasi menu dog star",
            "primary_keyword": "strategi menu engineering kafe resto akhir tahun",
            "secondary_keywords": "matriks boston consulting group menu kafe kuliner, 4 kuadran menu star plowhorse puzzle dog, cara menaikkan margin laba kotor menu kuliner, penataan posisi tata letak daftar menu golden triangle, evaluasi food cost bahan baku kadaluarsa",
            "search_intent": "informational",
            "search_interest": "Sangat Tinggi",
            "trending_reason": "Evaluasi tahunan kinerja daftar menu makanan dan minuman sangat penting untuk membuang menu yang tidak laku tetapi membebani stok kulkas, serta memfokuskan penjualan pada menu paling menghasilkan untung.",
            "credible_sources": [
                "Perhimpunan Hotel dan Restoran Indonesia (PHRI)",
                "Pedoman Manajemen Food and Beverage Indonesia",
                "Cornell University School of Hotel Administration Research"
            ],
            "msme_opportunity": "Menaikkan keuntungan kotor dapur kafe sebesar 15-25 persen tanpa menaikkan harga jual secara drastis, mengurangi sampah makanan (food waste), dan mempercepat waktu penyajian dapur.",
            "article_angle": "Pemetaan 4 kuadran menu kuliner berdasarkan popularitas dan margin laba, strategi merombak menu Dog menjadi bundling, memindahkan posisi menu Star ke area Golden Triangle buku menu, dan kalibrasi resep HPP.",
            "slug": "strategi-menu-engineering-kafe-resto-evaluasi-akhir-tahun",
            "assigned_category": "Solusi Per Industri"
        },
        {
            "id": 735,
            "topic": "SOP Penanganan Antrean Panjang Kasir Jam Sibuk Menggunakan Fitur Split Bill dan Cetak Struk Dapur Cepat",
            "trending_query": "sop antrean kasir jam sibuk split bill cetak struk dapur cepat pos",
            "primary_keyword": "sop antrean kasir jam sibuk split bill",
            "secondary_keywords": "cara kasir layani rombongan bayar pisah split bill, integrasi printer dapur kds kafe tanpa antre, tata cara kasir antrian cepat rush hour resto, metode pembayaran nontunai qris percepat transaksi meja, pembagian tugas kasir kasir input kasir packing",
            "search_intent": "informational",
            "search_interest": "Tinggi",
            "trending_reason": "Selama musim libur akhir tahun, kafe dan gerai makanan dipadati pelanggan rombongan yang sering meminta pembayaran terpisah (split bill), menyebabkan antrean mengular dan pelanggan lain membatalkan pesanan.",
            "credible_sources": [
                "Pedoman Pelayanan Prima Kasir Ritel dan F&B",
                "Asosiasi Kasir dan Manajemen Kas Indonesia",
                "Standar Operasional Prosedur Restoran Modern"
            ],
            "msme_opportunity": "Memangkas waktu transaksi dari 4 menit menjadi di bawah 45 detik per pelanggan, menghilangkan salah hitung pembayaran patungan, dan meningkatkan kepuasan pengunjung.",
            "article_angle": "SOP operasional jam sibuk (rush hour), pemanfaatan fitur split bill otomatis per item atau per nominal di aplikasi kasir POS, routing pesanan otomatis ke printer dapur bar, dan signage jalur antrean mandiri.",
            "slug": "sop-antrean-kasir-jam-sibuk-split-bill-struk-dapur-cepat",
            "assigned_category": "Kasir & POS"
        },
        {
            "id": 736,
            "topic": "Panduan Perpanjangan Sertifikat Halal dan Izin P-IRT Produk Makanan Minuman UMKM Menyambut Regulasi 2027",
            "trending_query": "panduan perpanjangan sertifikat halal dan izin pirt umkm bpjph dinkes",
            "primary_keyword": "panduan perpanjangan sertifikat halal dan izin pirt",
            "secondary_keywords": "masa berlaku sertifikat halal bpjph seumur hidup atau perpanjang, alur perpanjangan spp pirt dinas kesehatan dinkes, syarat uji laboratorium pangan dan label nutrisi nutrition facts, cek status kedaluwarsa izin edar di oss rba, mitigasi pencabutan izin usaha kuliner ukm",
            "search_intent": "informational",
            "search_interest": "Sangat Tinggi",
            "trending_reason": "Banyak pelaku usaha kuliner kemasan mengira izin P-IRT dan sertifikasi halal berlaku otomatis selamanya tanpa pembaruan, padahal komitmen bahan baku dan perubahan komposisi wajib dilaporkan berkala.",
            "credible_sources": [
                "Badan Penyelenggara Jaminan Produk Halal (BPJPH) Kemenag RI",
                "Badan Pengawas Obat dan Makanan (BPOM) RI",
                "Kementerian Kesehatan RI - Direktorat Penyehatan Lingkungan"
            ],
            "msme_opportunity": "Mempertahankan izin edar produk di rak minimarket modern dan toko oleh-oleh, melindungi brand dari tuntutan hukum, dan meningkatkan kepercayaan konsumen muslim secara berkelanjutan.",
            "article_angle": "Klarifikasi regulasi masa berlaku sertifikat halal UU Cipta Kerja vs ketentuan audit bahan baku berkala, tahapan pembaruan SPP P-IRT di portal OSS-RBA, checklist label wajib (informasi nilai gizi, masa kadaluarsa), dan sanksi penarikan produk.",
            "slug": "panduan-perpanjangan-sertifikat-halal-dan-izin-pirt-umkm",
            "assigned_category": "Pajak & Legalitas"
        },
        {
            "id": 737,
            "topic": "Strategi Pesan WhatsApp Broadcast Tersegmentasi Menjelang Gajian Akhir Bulan dan Promo Harbolnas 12.12",
            "trending_query": "strategi whatsapp broadcast tersegmentasi promo 12 12 promo gajian",
            "primary_keyword": "strategi whatsapp broadcast tersegmentasi promo 12 12",
            "secondary_keywords": "copywriting wa bisnis promo payday akhir bulan, segmentasi pelanggan berdasarkan riwayat belanja kasir pos, cara broadcast whatsapp resmi anti banned centang hijau, kupon diskon eksklusif pelanggan loyal vip, jam tayang terbaik kirim pesan promosi whatsapp",
            "search_intent": "informational",
            "search_interest": "Sangat Tinggi",
            "trending_reason": "Mendekati hari gajian akhir November (payday) yang menyambung langsung ke promo Harbolnas 12.12, pemilik bisnis butuh strategi pemasaran langsung via WhatsApp tanpa terblokir sistem anti-spam Meta.",
            "credible_sources": [
                "Meta for Business - WhatsApp Business Platform Guidelines",
                "Asosiasi Digital Marketing Indonesia",
                "Riset Perilaku Belanja Konsumen Mobile Indonesia"
            ],
            "msme_opportunity": "Mencapai Open Rate pesan promosi hingga 90 persen, menghasilkan konversi penjualan instan dalam 24 jam, dan menghemat anggaran iklan berbayar.",
            "article_angle": "Segmentasi database kontak menjadi 3 kelompok (pembeli VIP, pembeli pasif 30 hari, pembeli baru), contoh naskah pesan copywriting persuasif tanpa kata spam, jam kirim prima (pukul 11.30 dan 19.30), dan integrasi link katalog online.",
            "slug": "strategi-whatsapp-broadcast-tersegmentasi-promo-gajian-12-12",
            "assigned_category": "Marketing & WhatsApp"
        },
        {
            "id": 738,
            "topic": "Manajemen Kasbon Karyawan dan Pemotongan Payroll Otomatis di Slip Gaji Digital Akhir Periode Kerja",
            "trending_query": "manajemen kasbon karyawan dan potong payroll slip gaji digital umkm",
            "primary_keyword": "manajemen kasbon karyawan dan potong payroll slip gaji",
            "secondary_keywords": "aturan plafon pinjaman kasbon maksimal 30 persen gaji, sop formulir pengajuan kasbon karyawan toko, cara hitung potongan kasbon di payroll excel dan aplikasi, slip gaji digital transparan via whatsapp, mitigasi sengketa salah potong gaji karyawan",
            "search_intent": "informational",
            "search_interest": "Tinggi",
            "trending_reason": "Kebutuhan finansial karyawan toko dan staf gudang sering meningkat tajam menjelang akhir tahun, membuat antrean kasbon melonjak yang rawan memicu kekacauan pembukuan dan perselisihan kerja jika dihitung manual.",
            "credible_sources": [
                "Undang-Undang Ketenagakerjaan dan Peraturan Pengupahan Kemenaker",
                "Pedoman Human Resource Management UMKM",
                "Asosiasi Pengusaha Indonesia (APINDO)"
            ],
            "msme_opportunity": "Membantu kebutuhan mendesak karyawan secara terstruktur tanpa mengganggu kas operasional toko, mengeliminasi tuduhan potongan liar, dan menjaga moral loyalitas tim kerja.",
            "article_angle": "Pembuatan aturan baku kasbon (plafon maksimal 30 persen dari take home pay, tenor cicilan maksimal 2 bulan), formulir persetujuan pemotongan upah resmi, otomatisasi perhitungan di slip gaji digital, dan distribusi slip gaji via WhatsApp terenkripsi.",
            "slug": "manajemen-kasbon-karyawan-dan-potong-payroll-slip-gaji-digital",
            "assigned_category": "Strategi & Operasional"
        }
    ]
}

out_path = os.path.join("database", "data", "articles", "trending-research-2026-11-17.json")
with open(out_path, "w", encoding="utf-8") as f:
    json.dump(data, f, indent=2, ensure_ascii=False)

print(f"[SUCCESS] Saved Google Trends Business Research to {out_path} ({len(data['topics'])} topics)")
