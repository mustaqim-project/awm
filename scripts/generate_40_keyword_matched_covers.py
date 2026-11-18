import json
import os
import time
from playwright.sync_api import sync_playwright

CARDS_DATA = {
    1: {
        "pillar": "Pilar 1: Keuangan & Pembukuan",
        "pillar_badge": "F&B COSTING • KALKULASI HPP MENU",
        "theme": "finance",
        "title": "Cara Menghitung Food Cost Ideal Kafe dan Restoran",
        "subtitle": "Standar persentase 28-32 persen, rumus food cost riil vs potensial, dan strategi menekan limbah dapur.",
        "card_title": "Kalkulasi Menu Restoran (Contoh)",
        "badge_status": "TARGET 28% - 32%",
        "rows": [
            ("Harga Jual Menu", "Rp 45.000"),
            ("Total Biaya Bahan Baku", "Rp 13.500"),
            ("Persentase Food Cost", "30.0% (Ideal)"),
            ("Gross Profit Margin", "70.0% (Rp 31.500)")
        ]
    },
    2: {
        "pillar": "Pilar 1: Keuangan & Pembukuan",
        "pillar_badge": "CASH FLOW • WORKING CAPITAL",
        "theme": "finance",
        "title": "Cara Mengelola Siklus Konversi Kas Usaha Ritel UMKM",
        "subtitle": "Formula Cash Conversion Cycle (DIO + DSO - DPO) untuk mempercepat perputaran modal usaha.",
        "card_title": "Indikator Perputaran Kas Ritel",
        "badge_status": "SIKLUS SEHAT: 26 HARI",
        "rows": [
            ("Days Inventory Outstanding (DIO)", "42 Hari (Stok)"),
            ("Days Sales Outstanding (DSO)", "14 Hari (Piutang)"),
            ("Days Payable Outstanding (DPO)", "30 Hari (Hutang Suplier)"),
            ("Net Cash Conversion Cycle (CCC)", "26 Hari Modal Berputar")
        ]
    },
    3: {
        "pillar": "Pilar 1: Keuangan & Pembukuan",
        "pillar_badge": "BREAK EVEN POINT • TARGET SALES",
        "theme": "finance",
        "title": "Cara Menghitung Target Omzet Harian Toko dari BEP",
        "subtitle": "Konversi biaya tetap bulanan dan margin laba kotor menjadi target penjualan harian kasir.",
        "card_title": "Simulasi Target Penjualan Harian",
        "badge_status": "TARGET MINIMAL BEP",
        "rows": [
            ("Biaya Operasional Tetap / Bln", "Rp 15.000.000"),
            ("Rata-rata Margin Laba Kotor", "40% (Gross Margin)"),
            ("Target Omzet Bulanan BEP", "Rp 37.500.000"),
            ("Target Omzet Harian (30 Hari)", "Rp 1.250.000 / Hari")
        ]
    },
    4: {
        "pillar": "Pilar 1: Keuangan & Pembukuan",
        "pillar_badge": "PAYROLL MANAGEMENT • SOP KASBON",
        "theme": "finance",
        "title": "Cara Mengelola Kasbon Karyawan Toko dan Pembukuannya",
        "subtitle": "Aturan plafon maksimal, tenor cicilan gaji, formulir persetujuan, dan pencatatan akuntansi toko.",
        "card_title": "Ketentuan Kasbon & Potong Gaji",
        "badge_status": "PLAFON TERKONTROL",
        "rows": [
            ("Batas Maksimal Kasbon", "Maks 30% Gaji Pokok"),
            ("Jangka Waktu Pengembalian", "Maksimal 2x Periode Gaji"),
            ("Mekanisme Pemotongan", "Auto Deduct Payroll"),
            ("Persetujuan Wajib", "Tanda Tangan Tertulis")
        ]
    },
    5: {
        "pillar": "Pilar 1: Keuangan & Pembukuan",
        "pillar_badge": "COST ACCOUNTING • OVERHEAD CONTROL",
        "theme": "finance",
        "title": "Analisis Biaya Overhead Tetap dan Variabel Usaha Kecil",
        "subtitle": "Klasifikasi beban sewa, listrik, dan gaji untuk menentukan titik efisiensi operasional toko.",
        "card_title": "Struktur Beban Usaha Toko",
        "badge_status": "EFISIENSI OPERASIONAL",
        "rows": [
            ("Biaya Overhead Tetap (Fixed)", "Sewa Ruko, Gaji Pokok"),
            ("Biaya Overhead Variabel", "Listrik, Kantong Plastik, Bonus"),
            ("Rasio Beban terhadap Omzet", "Ideal Di Bawah 22%"),
            ("Dampak ke Titik Impas", "Menurunkan Nilai BEP Toko")
        ]
    },
    6: {
        "pillar": "Pilar 1: Keuangan & Pembukuan",
        "pillar_badge": "PRICING STRATEGY • MARKUP VS MARGIN",
        "theme": "finance",
        "title": "Cara Menghitung Markup Harga Jual Barang Grosir dan Eceran",
        "subtitle": "Perbedaan rumus markup dan margin laba kotor agar toko tidak keliru menentukan harga jual.",
        "card_title": "Kalkulator Markup vs Margin",
        "badge_status": "AKURASI PRICING",
        "rows": [
            ("Harga Pokok Pembelian (HPP)", "Rp 50.000 / Unit"),
            ("Target Markup Penjualan", "60% dari Harga Pokok"),
            ("Harga Jual ke Konsumen", "Rp 80.000 / Unit"),
            ("Margin Laba Kotor Riil", "37.5% (Rp 30.000)")
        ]
    },
    7: {
        "pillar": "Pilar 1: Keuangan & Pembukuan",
        "pillar_badge": "INTERNAL AUDIT • ANTI-FRAUD KASIR",
        "theme": "finance",
        "title": "SOP Audit Kas Mendadak Toko untuk Mencegah Kebocoran",
        "subtitle": "Prosedur surprise cash count, pencocokan saldo laci kasir vs sistem POS, dan investigasi selisih.",
        "card_title": "Berita Acara Surprise Cash Count",
        "badge_status": "HASIL AUDIT SEIMBANG",
        "rows": [
            ("Uang Modal Awal (Float Kasir)", "Rp 500.000 (Sesuai)"),
            ("Total Fisik Uang di Laci", "Rp 4.750.000"),
            ("Total Sales di Sistem POS", "Rp 4.250.000"),
            ("Status Selisih Akhir Shift", "Rp 0 (Balance Sempurna)")
        ]
    },
    8: {
        "pillar": "Pilar 1: Keuangan & Pembukuan",
        "pillar_badge": "FINANCIAL REPORT • P&L ANALYSIS",
        "theme": "finance",
        "title": "Cara Membaca Laporan Laba Rugi Komparatif Bulanan UMKM",
        "subtitle": "Menganalisis tren pendapatan, fluktuasi beban pokok, dan margin laba bersih antar periode usaha.",
        "card_title": "Komparasi Laba Rugi Toko (MoM)",
        "badge_status": "PERTUMBUHAN POSITIF",
        "rows": [
            ("Pertumbuhan Pendapatan (Sales)", "+18.4% Bulan Ini"),
            ("Rasio Beban Pokok (HPP)", "-2.8% (Lebih Efisien)"),
            ("Laba Operasional (EBITDA)", "+24.2% dari Periode Lalu"),
            ("Net Profit Margin Bersih", "15.8% (Target Tercapai)")
        ]
    },
    9: {
        "pillar": "Pilar 2: Manajemen Inventori & Gudang",
        "pillar_badge": "INVENTORY CONTROL • BUFFER STOCK",
        "theme": "inventory",
        "title": "Cara Menghitung Safety Stock Barang Dagangan Toko",
        "subtitle": "Rumus stok pengaman persediaan untuk menghindari out-of-stock saat terjadi lonjakan permintaan.",
        "card_title": "Parameter Stok Pengaman (Safety Stock)",
        "badge_status": "PROTEKSI STOK AMAN",
        "rows": [
            ("Penjualan Harian Maksimal", "85 Unit / Hari"),
            ("Lead Time Pengiriman Maks", "10 Hari Kalender"),
            ("Rata-rata Konsumsi Harian", "50 Unit (Lead Time 6 Hari)"),
            ("Cadangan Safety Stock Wajib", "150 Unit di Rak Gudang")
        ]
    },
    10: {
        "pillar": "Pilar 2: Manajemen Inventori & Gudang",
        "pillar_badge": "RESTOCK SYSTEM • REORDER POINT",
        "theme": "inventory",
        "title": "Cara Menentukan Reorder Point Stok Barang Toko Ritel",
        "subtitle": "Menghitung titik pemesanan ulang otomatis berdasarkan lead time supplier dan cadangan pengaman.",
        "card_title": "Ambang Batas Pemesanan Ulang (ROP)",
        "badge_status": "NOTIFIKASI RESTOCK OTOMATIS",
        "rows": [
            ("Lead Time Demand Penjualan", "80 Unit Barang"),
            ("Buffer Safety Stock", "40 Unit Cadangan"),
            ("Ambang Batas Reorder Point", "120 Unit Minimal"),
            ("Aksi Sistem Cooca", "Auto Alert Purchase Order")
        ]
    },
    11: {
        "pillar": "Pilar 2: Manajemen Inventori & Gudang",
        "pillar_badge": "INVENTORY ROTATION • METODE FEFO",
        "theme": "inventory",
        "title": "Penerapan Metode FEFO Toko Makanan dan Kosmetik UMKM",
        "subtitle": "Manajemen First Expired First Out untuk meniadakan kerugian akibat barang rusak dan basi.",
        "card_title": "Rotasi Barang Berdasarkan Kadaluarsa",
        "badge_status": "ZERO BARANG KADALUARSA",
        "rows": [
            ("Batch 01 (Exp. Nov 2026)", "Pajang Paling Depan (Display)"),
            ("Batch 02 (Exp. Jan 2027)", "Susun di Belakang Rak"),
            ("Batch 03 (Exp. Mar 2027)", "Simpan di Gudang Penyangga"),
            ("Aturan Rotasi Etalase", "Wajib Sortir Tanggal EXP")
        ]
    },
    12: {
        "pillar": "Pilar 2: Manajemen Inventori & Gudang",
        "pillar_badge": "WAREHOUSE LOGISTICS • CARRYING COST",
        "theme": "inventory",
        "title": "Cara Menghitung Holding Cost Persediaan Barang Gudang",
        "subtitle": "Menghitung biaya sewa ruang, asuransi, risiko susut, dan opportunity cost barang menumpuk.",
        "card_title": "Komponen Biaya Simpan Gudang",
        "badge_status": "KONTROL BIAYA LOGISTIK",
        "rows": [
            ("Biaya Ruang & Sewa Gudang", "9.0% per Tahun"),
            ("Asuransi & Proteksi Barang", "2.5% per Tahun"),
            ("Penyusutan & Kerusakan Fisik", "4.5% per Tahun"),
            ("Total Holding Cost Persediaan", "22.0% dari Nilai Stok")
        ]
    },
    13: {
        "pillar": "Pilar 2: Manajemen Inventori & Gudang",
        "pillar_badge": "STOCK OPNAME • CYCLE COUNTING",
        "theme": "inventory",
        "title": "SOP Cycle Counting Stock Opname Bergilir Tanpa Tutup Toko",
        "subtitle": "Pemeriksaan stok harian terjadwal berbasis klasifikasi ABC tanpa menghentikan aktivitas kasir.",
        "card_title": "Jadwal Audit Fisik Bergilir Toko",
        "badge_status": "TOKO TETAP BEROPERASI",
        "rows": [
            ("Kategori A (Fast Moving)", "Dihitung Setiap Minggu"),
            ("Kategori B (Medium Moving)", "Dihitung Dua Minggu Sekali"),
            ("Kategori C (Slow Moving)", "Dihitung Setiap Bulan"),
            ("Tingkat Akurasi Saldo Stok", "99.2% Cocok dengan Sistem")
        ]
    },
    14: {
        "pillar": "Pilar 2: Manajemen Inventori & Gudang",
        "pillar_badge": "MULTI-LOCATION • GUDANG PUSAT & CABANG",
        "theme": "inventory",
        "title": "Manajemen Multi Gudang Toko Retail dan Sinkronisasi Stok",
        "subtitle": "Standardisasi surat jalan, transfer antar cabang toko, dan monitoring stok terintegrasi.",
        "card_title": "Status Transfer Stok Antar Gudang",
        "badge_status": "SINKRONISASI REAL-TIME",
        "rows": [
            ("Gudang Pusat (Distribution)", "1.200 Unit Tersedia"),
            ("Toko Cabang 01 (Retail Store)", "250 Unit (Stok Aman)"),
            ("Toko Cabang 02 (Retail Store)", "180 Unit (Stok Aman)"),
            ("Dokumen Pengiriman Barang", "Surat Jalan & Barcode Transfer")
        ]
    },
    15: {
        "pillar": "Pilar 2: Manajemen Inventori & Gudang",
        "pillar_badge": "INVENTORY RECOVERY • DEAD STOCK",
        "theme": "inventory",
        "title": "Cara Mengatasi Dead Stock Barang Lambat Laku di Toko",
        "subtitle": "Strategi clearance sale, bundling promo, dan konversi stok macet menjadi likuiditas kas.",
        "card_title": "Penanganan Stok Tidak Bergerak (>90 Hari)",
        "badge_status": "PEMULIHAN KAS USAHA",
        "rows": [
            ("Identifikasi Umur Persediaan", "Aging Lebih dari 90 Hari"),
            ("Strategi Penjualan Cepat", "Bundling Produk Fast Moving"),
            ("Program Diskon Likuidasi", "Clearance Promo Khusus Member"),
            ("Target Pemulihan Modal", "Konversi Kembali Jadi Uang Tunai")
        ]
    },
    16: {
        "pillar": "Pilar 2: Manajemen Inventori & Gudang",
        "pillar_badge": "BIN CARD • INVENTORY TRACKING",
        "theme": "inventory",
        "title": "Cara Membuat Kartu Stok Barang Manual dan Digital",
        "subtitle": "Format tabel mutasi masuk, keluar, retur suplier, dan saldo fisik barang di gudang retail.",
        "card_title": "Buku Mutasi Kartu Stok Barang",
        "badge_status": "KONTROL FISIK RAK",
        "rows": [
            ("Saldo Awal Persediaan", "150 Pcs (Awal Periode)"),
            ("Penerimaan Barang Suplier", "+100 Pcs (Faktur Pembelian)"),
            ("Pengeluaran Kasir Penjualan", "-85 Pcs (Transaksi POS)"),
            ("Saldo Akhir Fisik Rak", "165 Pcs (Cocok dengan Sistem)")
        ]
    },
    17: {
        "pillar": "Pilar 3: Sistem Kasir, POS & Anti-Fraud",
        "pillar_badge": "CASH REGISTER SOP • SERAH TERIMA SHIFT",
        "theme": "cashier",
        "title": "SOP Buka dan Tutup Shift Kasir Format Serah Terima",
        "subtitle": "Prosedur hitung float modal laci, rekonsiliasi non-tunai QRIS/EDC, dan berita acara shift.",
        "card_title": "Rekonsiliasi Pergantian Shift Kasir",
        "badge_status": "BERITA ACARA SELESAI",
        "rows": [
            ("Uang Modal Awal Shift (Float)", "Rp 500.000"),
            ("Total Kas Tunai Masuk", "Rp 3.850.000"),
            ("Total Non-Tunai (QRIS & EDC)", "Rp 2.450.000"),
            ("Status Selisih Kasir", "Rp 0 (Serah Terima Sah)")
        ]
    },
    18: {
        "pillar": "Pilar 3: Sistem Kasir, POS & Anti-Fraud",
        "pillar_badge": "CASH SECURITY • DETEKSI UANG PALSU",
        "theme": "cashier",
        "title": "SOP Deteksi Uang Palsu di Kasir Toko dan Warung",
        "subtitle": "Pemeriksaan metode 3D Bank Indonesia, penggunaan lampu UV, dan langkah mitigasi kasir.",
        "card_title": "Prosedur Verifikasi Keaslian Uang BI",
        "badge_status": "STANDAR 3D BANK INDONESIA",
        "rows": [
            ("Dilihat (Visual Mata)", "Warna Cerah & Logo BI Berubah"),
            ("Diraba (Tekstur Fisik)", "Kasar pada Angka & Gambar Tokoh"),
            ("Diterawang (Tanda Air)", "Watermark & Electrotype Jelas"),
            ("Verifikasi Tambahan", "Sinar UV (Pendaran Tinta Khusus)")
        ]
    },
    19: {
        "pillar": "Pilar 3: Sistem Kasir, POS & Anti-Fraud",
        "pillar_badge": "SECURITY & RBAC • OTORISASI POS",
        "theme": "cashier",
        "title": "Role Based Access Control Kasir POS Mencegah Fraud",
        "subtitle": "Pembatasan wewenang void nota, diskon khusus, dan akses laporan finansial kasir toko.",
        "card_title": "Matriks Hak Akses Karyawan POS",
        "badge_status": "HAK AKSES TERBATASI",
        "rows": [
            ("Level Staf Kasir Biasa", "Input Transaksi & Terima Bayar"),
            ("Level Supervisor Toko", "Otorisasi Void & Batalkan Item"),
            ("Level Store Manager", "Persetujuan Diskon di Atas 10%"),
            ("Level Pemilik Usaha (Owner)", "Akses Penuh Laporan & Finansial")
        ]
    },
    20: {
        "pillar": "Pilar 3: Sistem Kasir, POS & Anti-Fraud",
        "pillar_badge": "LOSS PREVENTION • ANTI-SWEETHEARTING",
        "theme": "cashier",
        "title": "Pencegahan Fraud Kasir Sweethearting dan Diskon Ilegal",
        "subtitle": "Mendeteksi modus lolos scan untuk rekanan, pengawasan CCTV kasir, dan audit nota pembatalan.",
        "card_title": "Monitoring Kecurangan Area Kasir",
        "badge_status": "PENGAWASAN KETAT",
        "rows": [
            ("Modus Sweethearting", "Bypass Barcode Scanner Teman"),
            ("Pengawasan Titik Kasir", "CCTV Sudut Pandang Laci & Meja"),
            ("Pemeriksaan Log Void", "Pencatatan Alasan Hapus Transaksi"),
            ("Sanksi Pelanggaran Fraud", "SP & Penggantian Kerugian Fisik")
        ]
    },
    21: {
        "pillar": "Pilar 3: Sistem Kasir, POS & Anti-Fraud",
        "pillar_badge": "BUSINESS CONTINUITY • OFFLINE POS",
        "theme": "cashier",
        "title": "SOP Transaksi Kasir Offline Saat Gangguan Internet Toko",
        "subtitle": "Prosedur transisi otomatis mode offline, antrean nota lokal, dan sinkronisasi data cloud.",
        "card_title": "Status Operasional Kasir Offline",
        "badge_status": "MODE OFFLINE AKTIF",
        "rows": [
            ("Koneksi Jaringan Internet", "Terputus (Disconnected)"),
            ("Penyimpanan Nota Transaksi", "Database Lokal Browser / Cache"),
            ("Pencetakan Bukti Bayar", "Printer Thermal Berfungsi Normal"),
            ("Sinkronisasi Data Kembali", "Auto-Sync Saat Internet Pulih")
        ]
    },
    22: {
        "pillar": "Pilar 3: Sistem Kasir, POS & Anti-Fraud",
        "pillar_badge": "CUSTOMER CARE • SOP RETUR & REFUND",
        "theme": "cashier",
        "title": "SOP Retur Penjualan dan Refund Kasir Toko Ritel",
        "subtitle": "Syarat nota pembelian, validasi fisik barang, formulir retur, dan penyesuaian stok sistem.",
        "card_title": "Verifikasi Klaim Pengembalian Barang",
        "badge_status": "VALIDASI DOKUMEN SAH",
        "rows": [
            ("Bukti Transaksi Pembelian", "Struk Asli Pembayaran Kasir"),
            ("Batas Waktu Pengajuan", "Maksimal 3 Hari Setelah Beli"),
            ("Kondisi Fisik Barang", "Segel Utuh & Belum Terpakai"),
            ("Pencatatan di Sistem POS", "Nota Retur & Penyesuaian Saldo Stok")
        ]
    },
    23: {
        "pillar": "Pilar 3: Sistem Kasir, POS & Anti-Fraud",
        "pillar_badge": "PROMO AUDIT • PENGENDALIAN DISKON",
        "theme": "cashier",
        "title": "Pengendalian Diskon Transaksi Kasir Mencegah Manipulasi",
        "subtitle": "Sistem voucher digital, kuota potongan harga otomatis, dan pembatasan input manual kasir.",
        "card_title": "Aturan Pemberian Diskon Penjualan",
        "badge_status": "SISTEM OTOMATIS TERCATAT",
        "rows": [
            ("Diskon Otomatis POS", "Kode Promo & Barcode Terdaftar"),
            ("Batas Potongan Kasir Manual", "Dinonaktifkan Tanpa PIN Manajer"),
            ("Maksimal Nominal Diskon", "15% dari Total Transaksi"),
            ("Audit Riwayat Promo", "Tercatat Lengkap di Laporan Toko")
        ]
    },
    24: {
        "pillar": "Pilar 3: Sistem Kasir, POS & Anti-Fraud",
        "pillar_badge": "EFFICIENCY METRICS • QUEUE MANAGEMENT",
        "theme": "cashier",
        "title": "Cara Mengukur Kecepatan Transaksi Kasir pada Jam Ramai",
        "subtitle": "Metrik waktu layanan per pelanggan, tata letak antrean cepat, dan integrasi scan barcode kilat.",
        "card_title": "Metrik Kecepatan Layanan Kasir (SLA)",
        "badge_status": "TARGET WAKTU TERCAPAI",
        "rows": [
            ("Waktu Layanan per Pelanggan", "Rata-rata 42 Detik (Cepat)"),
            ("Metode Pembayaran Utama", "QRIS Dinamis & Uang Pas"),
            ("Kapasitas Antrean Meja Kasir", "Maksimal 3 Antrean Menunggu"),
            ("Indeks Kepuasan Pembeli", "96.8% Tidak Menunggu Lama")
        ]
    },
    25: {
        "pillar": "Pilar 4: Legalitas, Perizinan & Perpajakan UMKM",
        "pillar_badge": "OSS-RBA INDONESIA • NIB ELEKTRONIK",
        "theme": "legal",
        "title": "Panduan Mengurus NIB Usaha Perorangan di OSS-RBA",
        "subtitle": "Syarat KTP & NPWP, pemilihan KBLI 5 digit risiko rendah, validasi izin usaha, dan cetak NIB resmi.",
        "card_title": "Sistem OSS-RBA BKPM RI",
        "badge_status": "TERBIT OTOMATIS",
        "rows": [
            ("Jenis Pelaku Usaha", "Orang Perseorangan (UMK)"),
            ("Tingkat Risiko Usaha", "Rendah (Low Risk)"),
            ("Legalitas Terbit Resmi", "NIB + Sertifikat Standar"),
            ("Biaya Pengurusan Izin", "Rp 0 (Gratis Resmi Pemerintah)")
        ]
    },
    26: {
        "pillar": "Pilar 4: Legalitas, Perizinan & Perpajakan UMKM",
        "pillar_badge": "PERPAJAKAN UMKM • PP 55 TAHUN 2022",
        "theme": "legal",
        "title": "Cara Menghitung PPh Final UMKM Sesuai PP 55/2022",
        "subtitle": "Pembebasan pajak omzet sampai Rp 500 juta untuk orang pribadi, tarif 0.5%, dan kode setor billing.",
        "card_title": "Simulasi Perhitungan PPh Final 0.5%",
        "badge_status": "FASILITAS UMKM BERLAKU",
        "rows": [
            ("Total Omzet Bruto Tahunan", "Rp 800.000.000"),
            ("Fasilitas Bebas Pajak (PTKP)", "Rp 500.000.000 (0% Pajak)"),
            ("Penghasilan Kena Pajak (PKP)", "Rp 300.000.000"),
            ("PPh Final Wajib Disetor (0.5%)", "Rp 1.500.000 / Tahun")
        ]
    },
    27: {
        "pillar": "Pilar 4: Legalitas, Perizinan & Perpajakan UMKM",
        "pillar_badge": "LEGALITAS BISNIS • AKAD BAGI HASIL",
        "theme": "legal",
        "title": "Cara Membuat Surat Perjanjian Kerjasama Bagi Hasil UMKM",
        "subtitle": "Klausul porsi nisbah, transparansi laporan buku, jangka waktu modal, dan penyelesaian sengketa.",
        "card_title": "Poin Krusial Perjanjian Kemitraan",
        "badge_status": "DRAF PERJANJIAN SAH",
        "rows": [
            ("Porsi Pembagian Hasil (Nisbah)", "60% Pengelola : 40% Pemodal"),
            ("Periode Distribusi Laba", "Dihitung Setiap Akhir Bulan"),
            ("Validitas Kekuatan Hukum", "Materai 10.000 & Saksi Ahli"),
            ("Transparansi Pembukuan", "Wajib Buka Dashboard Laporan")
        ]
    },
    28: {
        "pillar": "Pilar 4: Legalitas, Perizinan & Perpajakan UMKM",
        "pillar_badge": "HAK KEKAYAAN INTELEKTUAL • DJKI KEMENKUMHAM",
        "theme": "legal",
        "title": "Prosedur Pendaftaran Merek Dagang UMKM di DJKI",
        "subtitle": "Pengecekan database PDKI, penentuan kelas Nice Classification, tarif UMK, dan proteksi merek 10 tahun.",
        "card_title": "Proses Pendaftaran Merek Dagang",
        "badge_status": "PROTEKSI HUKUM 10 TAHUN",
        "rows": [
            ("Penelusuran Nama di PDKI", "Lolos Cek Kemiripan Merek"),
            ("Pemilihan Kelas Merek", "Sesuai Barang & Jasa (Nice Class)"),
            ("Tarif Resmi UMKM (Rekomendasi)", "Rp 500.000 (Subsidi Dinas)"),
            ("Masa Berlaku Perlindungan", "10 Tahun Sejak Pendaftaran")
        ]
    },
    29: {
        "pillar": "Pilar 4: Legalitas, Perizinan & Perpajakan UMKM",
        "pillar_badge": "BPJPH KEMENAG • SERTIFIKASI HALAL SEHATI",
        "theme": "legal",
        "title": "Syarat dan Alur Sertifikasi Halal Gratis (SEHATI) BPJPH",
        "subtitle": "Mekanisme self declare makanan dan minuman UMKM, pendamping PPH, dan verifikasi bahan baku.",
        "card_title": "Program Sertifikasi Halal Self-Declare",
        "badge_status": "BIAYA SUBSIDI RP 0",
        "rows": [
            ("Target Pelaku Usaha", "UMKM Makanan & Minuman Olahan"),
            ("Persyaratan Bahan Baku", "Sudah Bersertifikat Halal / Positif"),
            ("Peran Pendamping PPH", "Verifikasi & Validasi Lapangan"),
            ("Penerbitan Ketetapan Halal", "Fatwa MUI & Terbit Sertifikat BPJPH")
        ]
    },
    30: {
        "pillar": "Pilar 4: Legalitas, Perizinan & Perpajakan UMKM",
        "pillar_badge": "DITJEN PAJAK RI • NPWP BADAN ELEKTRONIK",
        "theme": "legal",
        "title": "Tata Cara Membuat NPWP Badan Online untuk CV dan PT",
        "subtitle": "Syarat Akta Notaris, SK Kemenkumham, pendaftaran di ereg.pajak.go.id, dan validasi data direksi.",
        "card_title": "Dokumen Pengajuan NPWP Badan",
        "badge_status": "PENDAFTARAN EREG ONLINE",
        "rows": [
            ("Bentuk Badan Usaha", "Persekutuan Komanditer (CV) & PT"),
            ("Dokumen Legalitas Inti", "Akta Pendirian & SK Kemenkumham"),
            ("Kewajiban Pengurus", "NPWP & SPT Tahunan Direktur Valid"),
            ("Waktu Penerbitan Kartu", "Instan Berformat 16 Digit Elektronik")
        ]
    },
    31: {
        "pillar": "Pilar 4: Legalitas, Perizinan & Perpajakan UMKM",
        "pillar_badge": "CORETAX DJP • E-FAKTUR ELEKTRONIK",
        "theme": "legal",
        "title": "Aturan Penggunaan Faktur Pajak Elektronik PKP Usaha Kecil",
        "subtitle": "Ketentuan pembuatan e-Faktur, nomor seri faktur pajak (NSFP), validasi QR code, dan batas waktu upload.",
        "card_title": "Ketentuan Faktur Pajak PKP",
        "badge_status": "TERVALIDASI CORETAX DJP",
        "rows": [
            ("Batas Waktu Upload e-Faktur", "Maksimal Tgl 15 Bulan Berikutnya"),
            ("Verifikasi Dokumen Pajak", "QR Code Otomatis Tervalidasi"),
            ("Pajak Pertambahan Nilai (PPN)", "Tarif Sesuai Regulasi Berlaku"),
            ("Kredit Pajak Masukan", "Dapat Dikreditkan Lawan Transaksi")
        ]
    },
    32: {
        "pillar": "Pilar 4: Legalitas, Perizinan & Perpajakan UMKM",
        "pillar_badge": "PROPERTY LEASE • SURAT PERJANJIAN SEWA",
        "theme": "legal",
        "title": "Poin Krusial Surat Perjanjian Sewa Tempat Usaha Ruko",
        "subtitle": "Klausul beban PBB, pemeliharaan fisik bangunan, deposit jaminan sewa, dan hak prioritas perpanjangan.",
        "card_title": "Pasal Krusial Kontrak Sewa Ruko",
        "badge_status": "PERLINDUNGAN PENYEWA",
        "rows": [
            ("Jangka Waktu Sewa Usaha", "Minimal 1 hingga 3 Tahun Sewa"),
            ("Tanggung Jawab Pajak PBB", "Ditanggung Pemilik Bangunan"),
            ("Uang Jaminan (Deposit Sewa)", "Dikembalikan di Akhir Masa Sewa"),
            ("Izin Modifikasi & Renovasi", "Diperbolehkan Sesuai Perjanjian")
        ]
    },
    33: {
        "pillar": "Pilar 5: CRM, Loyalitas Pelanggan & Omnichannel",
        "pillar_badge": "RETENTION MARKETING • REPEAT ORDER",
        "theme": "crm",
        "title": "Strategi Meningkatkan Repeat Order Tanpa Perang Harga",
        "subtitle": "Membangun customer lifetime value, follow-up otomatis WhatsApp, unboxing berkesan, dan membership.",
        "card_title": "Metrik Retensi & Pembelian Ulang",
        "badge_status": "LOYALITAS PELANGGAN NAIK",
        "rows": [
            ("Repeat Purchase Rate (RPR)", "Meningkat ke 38.5% Pembeli"),
            ("Customer Lifetime Value (LTV)", "Naik 2.4x Dibandingkan Pelanggan Baru"),
            ("Follow-up Pasca Pembelian", "Pesan Otomatis H+7 Penggunaan"),
            ("Strategi Diferensiasi Toko", "Kualitas Layanan & Garansi Pasti")
        ]
    },
    34: {
        "pillar": "Pilar 5: CRM, Loyalitas Pelanggan & Omnichannel",
        "pillar_badge": "LOYALTY PROGRAM • POIN REWARD TOKO",
        "theme": "crm",
        "title": "Cara Membangun Program Poin Loyalitas Pelanggan Toko",
        "subtitle": "Formula konversi belanja menjadi poin reward, katalog penukaran hadiah, dan integrasi kasir POS.",
        "card_title": "Skema Hadiah Program Loyalitas",
        "badge_status": "INTEGRASI SISTEM KASIR",
        "rows": [
            ("Rasio Perolehan Poin Member", "Belanja Rp 10.000 = Diberi 1 Poin"),
            ("Nilai Tukar Poin Hadiah", "100 Poin = Voucher Diskon Rp 10.000"),
            ("Identifikasi Data Pelanggan", "Cukup Masukkan Nomor WhatsApp"),
            ("Dampak ke Nilai Keranjang", "Average Order Value Naik 28%")
        ]
    },
    35: {
        "pillar": "Pilar 5: CRM, Loyalitas Pelanggan & Omnichannel",
        "pillar_badge": "WHATSAPP AUTOMATION • CUSTOMER SERVICE",
        "theme": "crm",
        "title": "Otomasi Layanan Pelanggan WhatsApp Business Toko Online",
        "subtitle": "Pemanfaatan pesan sambutan instan, balas cepat (quick replies), katalog produk, dan template orderan.",
        "card_title": "Alur Otomasi Chat WhatsApp",
        "badge_status": "RESPON OTOMATIS 24/7",
        "rows": [
            ("Pesan Sambutan (Greeting)", "Auto Reply Dalam 5 Detik Pertama"),
            ("Tombol Quick Replies", "Info Rekening, Ongkir, & Format Order"),
            ("Katalog Produk Langsung", "Pilih Menu & Checkout Sekali Klik"),
            ("Tingkat Konversi Chat Toko", "Closing Transaksi Meningkat 34%")
        ]
    },
    36: {
        "pillar": "Pilar 5: CRM, Loyalitas Pelanggan & Omnichannel",
        "pillar_badge": "B2B CREDIT MANAGEMENT • PELANGGAN GROSIR",
        "theme": "crm",
        "title": "Manajemen Pelanggan VIP dan Grosir: Plafon Kredit Aman",
        "subtitle": "Menilai creditworthiness toko mitra, syarat Term of Payment (TOP), penagihan jatuh tempo, dan garansi.",
        "card_title": "Ketentuan Fasilitas Kredit Grosir",
        "badge_status": "RISIKO PIUTANG TERKENDALI",
        "rows": [
            ("Batas Plafon Kredit Pembelian", "Rp 25.000.000 per Mitra Terdaftar"),
            ("Term of Payment (TOP Tenor)", "Jatuh Tempo 14 hingga 30 Hari"),
            ("Syarat Pengajuan Fasilitas", "Historis Minimal 5x Transaksi Lunas"),
            ("Peringatan Jatuh Tempo", "Auto Reminder WhatsApp H-3 Tagihan")
        ]
    },
    37: {
        "pillar": "Pilar 5: CRM, Loyalitas Pelanggan & Omnichannel",
        "pillar_badge": "DIGITAL CATALOG • ORDER VIA WHATSAPP",
        "theme": "crm",
        "title": "Cara Membuat Katalog Online Toko Terintegrasi WhatsApp",
        "subtitle": "Penyusunan etalase mobile friendly, pembaruan stok real-time, dan direct-checkout ke nomor kasir.",
        "card_title": "Fitur Katalog Belanja Digital",
        "badge_status": "DIRECT ORDER KE WHATSAPP",
        "rows": [
            ("Tampilan Etalase Produk", "Mobile Friendly & Cepat Diakses"),
            ("Sinkronisasi Stok Barang", "Otomatis Update dari Kasir POS"),
            ("Mekanisme Keranjang Belanja", "Checkout Menjadi Format Teks Rapi"),
            ("Biaya Tambahan Transaksi", "0% Komisi Marketplace (Milik Sendiri)")
        ]
    },
    38: {
        "pillar": "Pilar 5: CRM, Loyalitas Pelanggan & Omnichannel",
        "pillar_badge": "SERVICE EXCELLENCE • PENANGANAN KOMPLAIN",
        "theme": "crm",
        "title": "SOP Penanganan Komplain Pelanggan Toko agar Tetap Loyal",
        "subtitle": "Penerapan metode LAST (Listen, Apologize, Solve, Thank), batas waktu respon, dan kompensasi pembeli.",
        "card_title": "Standar Layanan Komplain Pelanggan",
        "badge_status": "SLA PENYELESAIAN 1X24 JAM",
        "rows": [
            ("Metode Komunikasi Utama", "Metode LAST (Dengar, Maaf, Solusi)"),
            ("Kecepatan Respon Awal", "Maksimal 15 Menit di Jam Kerja"),
            ("Opsi Solusi Penyelesaian", "Ganti Produk Baru atau Refund Utuh"),
            ("Tingkat Retensi Pembeli Komplain", "88% Pelanggan Bertahan dan Puas")
        ]
    },
    39: {
        "pillar": "Pilar 5: CRM, Loyalitas Pelanggan & Omnichannel",
        "pillar_badge": "OMNICHANNEL SYNC • INTEGRASI MARKETPLACE",
        "theme": "crm",
        "title": "Sinkronisasi Pesanan Marketplace dan Toko Offline UMKM",
        "subtitle": "Integrasi inventori multi-channel Shopee, Tokopedia, dan toko fisik agar tidak terjadi double-selling.",
        "card_title": "Pusat Kontrol Omnichannel Toko",
        "badge_status": "STOK TERSINKRONISASI",
        "rows": [
            ("Kanal Penjualan Toko Offline", "Kasir Toko Fisik (Update Instan)"),
            ("Kanal Online Marketplace", "Shopee, Tokopedia, TikTok Shop"),
            ("Dampak Pemotongan Stok", "Satu Stok untuk Semua Kanal Jual"),
            ("Resiko Double Selling Barang", "0% (Terhindar dari Penalti Toko)")
        ]
    },
    40: {
        "pillar": "Pilar 5: CRM, Loyalitas Pelanggan & Omnichannel",
        "pillar_badge": "CUSTOMER SATISFACTION • METRIK NPS",
        "theme": "crm",
        "title": "Cara Mengukur Net Promoter Score (NPS) Kepuasan Pelanggan",
        "subtitle": "Survei skala 0-10, rumus menghitung Promoters vs Detractors, dan evaluasi loyalitas pelanggan toko.",
        "card_title": "Klasifikasi Skor Loyalitas (NPS)",
        "badge_status": "SKOR NPS SEHAT: +58",
        "rows": [
            ("Promoters (Skor 9 hingga 10)", "72% Pelanggan Sangat Merekomendasikan"),
            ("Passives (Skor 7 hingga 8)", "14% Pelanggan Cukup Puas"),
            ("Detractors (Skor 0 hingga 6)", "14% Pelanggan Perlu Diberi Solusi"),
            ("Net Promoter Score Akhir", "+58 (Kategori Loyalitas Prima)")
        ]
    }
}

THEME_CONFIG = {
    "finance": {
        "glow1": "rgba(16, 185, 129, 0.22)",
        "glow2": "rgba(14, 165, 233, 0.15)",
        "accent": "#10b981",
        "pill_bg": "rgba(16, 185, 129, 0.15)",
        "pill_border": "rgba(16, 185, 129, 0.35)",
        "pill_text": "#34d399",
        "card_badge_bg": "rgba(16, 185, 129, 0.2)",
        "card_badge_border": "rgba(16, 185, 129, 0.4)",
        "card_badge_text": "#34d399",
        "logo_grad": "linear-gradient(135deg, #10b981, #0ea5e9)",
        "card_val_color": "#34d399",
        "icon": "💰"
    },
    "inventory": {
        "glow1": "rgba(245, 158, 11, 0.22)",
        "glow2": "rgba(249, 115, 22, 0.15)",
        "accent": "#f59e0b",
        "pill_bg": "rgba(245, 158, 11, 0.15)",
        "pill_border": "rgba(245, 158, 11, 0.35)",
        "pill_text": "#fbbf24",
        "card_badge_bg": "rgba(245, 158, 11, 0.2)",
        "card_badge_border": "rgba(245, 158, 11, 0.4)",
        "card_badge_text": "#fbbf24",
        "logo_grad": "linear-gradient(135deg, #f59e0b, #ea580c)",
        "card_val_color": "#fbbf24",
        "icon": "📦"
    },
    "cashier": {
        "glow1": "rgba(244, 63, 94, 0.22)",
        "glow2": "rgba(99, 102, 241, 0.15)",
        "accent": "#f43f5e",
        "pill_bg": "rgba(244, 63, 94, 0.15)",
        "pill_border": "rgba(244, 63, 94, 0.35)",
        "pill_text": "#fb7185",
        "card_badge_bg": "rgba(244, 63, 94, 0.2)",
        "card_badge_border": "rgba(244, 63, 94, 0.4)",
        "card_badge_text": "#fb7185",
        "logo_grad": "linear-gradient(135deg, #f43f5e, #8b5cf6)",
        "card_val_color": "#fb7185",
        "icon": "🖥️"
    },
    "legal": {
        "glow1": "rgba(59, 130, 246, 0.22)",
        "glow2": "rgba(14, 165, 233, 0.15)",
        "accent": "#3b82f6",
        "pill_bg": "rgba(59, 130, 246, 0.15)",
        "pill_border": "rgba(59, 130, 246, 0.35)",
        "pill_text": "#60a5fa",
        "card_badge_bg": "rgba(59, 130, 246, 0.2)",
        "card_badge_border": "rgba(59, 130, 246, 0.4)",
        "card_badge_text": "#60a5fa",
        "logo_grad": "linear-gradient(135deg, #3b82f6, #06b6d4)",
        "card_val_color": "#60a5fa",
        "icon": "⚖️"
    },
    "crm": {
        "glow1": "rgba(168, 85, 247, 0.22)",
        "glow2": "rgba(236, 72, 153, 0.15)",
        "accent": "#a855f7",
        "pill_bg": "rgba(168, 85, 247, 0.15)",
        "pill_border": "rgba(168, 85, 247, 0.35)",
        "pill_text": "#c084fc",
        "card_badge_bg": "rgba(168, 85, 247, 0.2)",
        "card_badge_border": "rgba(168, 85, 247, 0.4)",
        "card_badge_text": "#c084fc",
        "logo_grad": "linear-gradient(135deg, #a855f7, #ec4899)",
        "card_val_color": "#c084fc",
        "icon": "👥"
    }
}

def render_html(item):
    theme = THEME_CONFIG[item["theme"]]
    rows_html = ""
    for label, val in item["rows"]:
        rows_html += f"""
        <div class="data-row">
          <span class="data-label">{label}</span>
          <span class="data-val">{val}</span>
        </div>
        """

    html = f"""<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
  @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap');
  
  * {{ box-sizing: border-box; margin: 0; padding: 0; }}
  body {{
    width: 1200px;
    height: 675px;
    background: #090d16;
    font-family: 'Plus Jakarta Sans', 'Segoe UI', -apple-system, Roboto, sans-serif;
    color: #f8fafc;
    overflow: hidden;
    position: relative;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 60px 70px;
  }}
  
  .bg-glow-1 {{
    position: absolute;
    top: -120px;
    right: -100px;
    width: 650px;
    height: 650px;
    background: radial-gradient(circle, {theme['glow1']} 0%, rgba(0,0,0,0) 70%);
    border-radius: 50%;
    z-index: 1;
  }}
  .bg-glow-2 {{
    position: absolute;
    bottom: -150px;
    left: 150px;
    width: 550px;
    height: 550px;
    background: radial-gradient(circle, {theme['glow2']} 0%, rgba(0,0,0,0) 70%);
    border-radius: 50%;
    z-index: 1;
  }}
  .bg-grid {{
    position: absolute;
    inset: 0;
    background-image: linear-gradient(to right, rgba(255,255,255,0.035) 1px, transparent 1px),
                      linear-gradient(to bottom, rgba(255,255,255,0.035) 1px, transparent 1px);
    background-size: 40px 40px;
    z-index: 1;
  }}

  .header {{
    position: relative;
    z-index: 10;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }}
  .brand-badge {{
    display: flex;
    align-items: center;
    gap: 12px;
  }}
  .brand-logo {{
    width: 38px;
    height: 38px;
    background: {theme['logo_grad']};
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 20px;
    color: white;
    box-shadow: 0 4px 14px rgba(0,0,0,0.4);
  }}
  .brand-text {{
    font-size: 20px;
    font-weight: 800;
    letter-spacing: -0.5px;
    color: #ffffff;
  }}
  .brand-tag {{
    font-size: 13px;
    font-weight: 600;
    color: #94a3b8;
    background: rgba(255,255,255,0.06);
    padding: 6px 14px;
    border-radius: 999px;
    border: 1px solid rgba(255,255,255,0.08);
  }}
  .pillar-pill {{
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 16px;
    border-radius: 999px;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    background: {theme['pill_bg']};
    color: {theme['pill_text']};
    border: 1px solid {theme['pill_border']};
  }}

  .main-content {{
    position: relative;
    z-index: 10;
    display: grid;
    grid-template-columns: 1.25fr 0.95fr;
    gap: 45px;
    align-items: center;
  }}

  .title-area {{
    display: flex;
    flex-direction: column;
    gap: 16px;
  }}
  .keyword-tag {{
    font-family: 'JetBrains Mono', monospace;
    font-size: 13px;
    color: {theme['accent']};
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 8px;
    letter-spacing: 0.5px;
  }}
  .keyword-tag::before {{
    content: '';
    display: inline-block;
    width: 8px;
    height: 8px;
    background: {theme['accent']};
    border-radius: 50%;
    box-shadow: 0 0 10px {theme['accent']};
  }}
  .title {{
    font-size: 36px;
    font-weight: 800;
    line-height: 1.22;
    letter-spacing: -0.8px;
    color: #ffffff;
  }}
  .desc {{
    font-size: 16px;
    line-height: 1.55;
    color: #94a3b8;
    font-weight: 400;
  }}

  .visual-card {{
    background: rgba(15, 23, 42, 0.78);
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 20px;
    padding: 22px 24px;
    backdrop-filter: blur(16px);
    box-shadow: 0 20px 40px -15px rgba(0,0,0,0.65), inset 0 1px 0 rgba(255,255,255,0.1);
    display: flex;
    flex-direction: column;
    gap: 14px;
  }}
  .card-header {{
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid rgba(255,255,255,0.08);
    padding-bottom: 12px;
  }}
  .card-title {{
    font-size: 14px;
    font-weight: 700;
    color: #e2e8f0;
    display: flex;
    align-items: center;
    gap: 8px;
  }}
  .badge-status {{
    font-size: 11px;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 6px;
    background: {theme['card_badge_bg']};
    color: {theme['card_badge_text']};
    border: 1px solid {theme['card_badge_border']};
  }}
  .card-body {{
    display: flex;
    flex-direction: column;
    gap: 10px;
  }}
  .data-row {{
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: rgba(255,255,255,0.035);
    padding: 9px 14px;
    border-radius: 10px;
    font-size: 13px;
  }}
  .data-label {{ color: #94a3b8; font-weight: 500; }}
  .data-val {{ color: #f8fafc; font-weight: 700; font-family: 'JetBrains Mono', monospace; }}

  .footer {{
    position: relative;
    z-index: 10;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-top: 1px solid rgba(255,255,255,0.08);
    padding-top: 18px;
    font-size: 13px;
    color: #64748b;
  }}
  .footer-meta {{
    display: flex;
    gap: 20px;
  }}
  .footer-meta span {{
    display: flex;
    align-items: center;
    gap: 6px;
  }}
</style>
</head>
<body>
  <div class="bg-glow-1"></div>
  <div class="bg-glow-2"></div>
  <div class="bg-grid"></div>

  <div class="header">
    <div class="brand-badge">
      <div class="brand-logo">C</div>
      <div class="brand-text">COOCA</div>
      <div class="brand-tag">Edukasi Bisnis UMKM</div>
    </div>
    <div class="pillar-pill">{item['pillar']}</div>
  </div>

  <div class="main-content">
    <div class="title-area">
      <div class="keyword-tag">{item['pillar_badge']}</div>
      <h1 class="title">{item['title']}</h1>
      <p class="desc">{item['subtitle']}</p>
    </div>

    <div class="visual-card">
      <div class="card-header">
        <div class="card-title">
          <span>{theme['icon']}</span> {item['card_title']}
        </div>
        <span class="badge-status">{item['badge_status']}</span>
      </div>
      <div class="card-body">
        {rows_html}
      </div>
    </div>
  </div>

  <div class="footer">
    <div class="footer-meta">
      <span>⏱️ 9 Menit Baca</span>
      <span>📅 18 November 2026</span>
      <span>🎯 Panduan Aplikatif UMKM</span>
    </div>
    <div>cooca.id/blog</div>
  </div>
</body>
</html>
"""
    return html

def main():
    with open('database/data/articles/keyword-research-2026-11-18.json', 'r', encoding='utf-8') as f:
        data = json.load(f)

    topics = data['topics']
    print(f"Loaded {len(topics)} topics from keyword-research-2026-11-18.json")

    out_dir = os.path.join('public', 'images', 'blog')
    os.makedirs(out_dir, exist_ok=True)

    with sync_playwright() as p:
        browser = p.chromium.launch()
        page = browser.new_page(viewport={'width': 1200, 'height': 675})

        for t in topics:
            id_ = t['id']
            slug = t['slug']
            out_path = os.path.join(out_dir, f"{slug}.jpg")

            if id_ not in CARDS_DATA:
                print(f"Warning: ID {id_} not in CARDS_DATA, skipping")
                continue

            card_info = CARDS_DATA[id_]
            html = render_html(card_info)
            page.set_content(html)
            page.wait_for_timeout(300)
            page.screenshot(path=out_path, quality=90, type="jpeg")

            size_kb = os.path.getsize(out_path) / 1024
            print(f"[{id_:02d}/40] Rendered: {slug}.jpg ({size_kb:.1f} KB)")

        browser.close()

    print("All 40 images generated successfully!")

if __name__ == "__main__":
    main()
