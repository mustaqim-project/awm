# scripts/generate_40_keyword_matched_covers_2026_11_19.py
# -*- coding: utf-8 -*-
import base64
import json
import os
import time
from playwright.sync_api import sync_playwright

ICON_PATH = os.path.join('public', 'assets', 'image', 'cooca-icon.png')
if os.path.exists(ICON_PATH):
    with open(ICON_PATH, 'rb') as f:
        COOCA_ICON_B64 = base64.b64encode(f.read()).decode('utf-8')
else:
    COOCA_ICON_B64 = ""

CARDS_DATA = {
    # -------------------------------------------------------------
    # PILAR 1: KEUANGAN & PEMBUKUAN (1-8)
    # -------------------------------------------------------------
    1: {
        "pillar": "Pilar 1: Manajemen Keuangan & Pembukuan",
        "pillar_badge": "COST ACCOUNTING • BTKL KALKULASI",
        "theme": "finance",
        "title": "Cara Menghitung Biaya Tenaga Kerja Langsung UMKM",
        "subtitle": "Kalkulasi upah per unit produk, alokasi jam kerja efektif, dan pencegahan distorsi HPP.",
        "card_title": "Simulasi BTKL Bakery Roti",
        "badge_status": "ALOKASI TEPAT",
        "rows": [
            ("Gaji Staf Produksi (2 Orang)", "Rp 6.000.000 / Bln"),
            ("Jam Kerja Efektif Bulanan", "400 Jam Kerja"),
            ("Tarif Upah Per Jam Kerja", "Rp 15.000 / Jam"),
            ("Alokasi BTKL Per Loyang Roti", "Rp 1.200 / Unit")
        ]
    },
    2: {
        "pillar": "Pilar 1: Manajemen Keuangan & Pembukuan",
        "pillar_badge": "CASH RESERVE • RISK MANAGEMENT",
        "theme": "finance",
        "title": "Cara Mengelola Dana Darurat Usaha Kecil",
        "subtitle": "Panduan alokasi 3-6 bulan biaya operasional tetap toko dan instrumen likuid bebas risiko.",
        "card_title": "Struktur Dana Darurat Toko",
        "badge_status": "CADANGAN AMAN",
        "rows": [
            ("Beban Tetap Bulanan (OPEX)", "Rp 12.000.000 / Bln"),
            ("Target Rasio Ketahanan", "3 Bulan Pengeluaran"),
            ("Target Nominal Dana Darurat", "Rp 36.000.000"),
            ("Alokasi Penyisihan Laba", "10% Laba Bersih / Bln")
        ]
    },
    3: {
        "pillar": "Pilar 1: Manajemen Keuangan & Pembukuan",
        "pillar_badge": "WORKING CAPITAL • ROTASI MODAL",
        "theme": "finance",
        "title": "Cara Menghitung Rasio Perputaran Modal Kerja Toko",
        "subtitle": "Rumus Working Capital Turnover (WCT) untuk mengukur efisiensi perputaran modal kerja.",
        "card_title": "Kalkulasi Perputaran Modal Kerja",
        "badge_status": "WCT IDEAL: 7,5 KALI",
        "rows": [
            ("Penjualan Bersih Tahunan", "Rp 450.000.000"),
            ("Rata-rata Modal Kerja Bersih", "Rp 60.000.000"),
            ("Rasio Perputaran Modal (WCT)", "7,50 Kali / Tahun"),
            ("Durasi Siklus Modal Berputar", "48 Hari Sekali")
        ]
    },
    4: {
        "pillar": "Pilar 1: Manajemen Keuangan & Pembukuan",
        "pillar_badge": "FINANCIAL DISCIPLINE • SEPARASI KAS",
        "theme": "finance",
        "title": "SOP Pemisahan Uang Pribadi dan Bisnis UMKM",
        "subtitle": "Panduan buka rekening terpisah, penetapan gaji pemilik toko, dan kontrol penarikan prive.",
        "card_title": "Tata Kelola Arus Kas Pemilik",
        "badge_status": "BEBAS CAMPUR ADUK",
        "rows": [
            ("Rekening Operasional Toko", "Khusus Arus Kas Bisnis"),
            ("Gaji Tetap Pemilik Toko", "Rp 5.000.000 / Bln"),
            ("Jadwal Transfer Gaji Pribadi", "Tepat Tanggal 25"),
            ("Penarikan Prive Pemilik", "Wajib Catat di Jurnal")
        ]
    },
    5: {
        "pillar": "Pilar 1: Manajemen Keuangan & Pembukuan",
        "pillar_badge": "PROMOTIONAL PRICING • TIERED DISCOUNT",
        "theme": "finance",
        "title": "Cara Menghitung Diskon Bertingkat agar Tidak Boncos",
        "subtitle": "Formula diskon bertahap 20% + 10% vs diskon langsung dan proteksi margin laba kotor.",
        "card_title": "Simulasi Diskon Bertingkat",
        "badge_status": "MARGIN TERLINDUNGI",
        "rows": [
            ("Harga Label Ritel Normal", "Rp 100.000"),
            ("Diskon Pertama 20%", "Rp 80.000"),
            ("Diskon Kedua 10% dari 80k", "Rp 72.000 (Bukan Rp70k)"),
            ("Diskon Riil Efektif", "28,0% (Bukan 30%)")
        ]
    },
    6: {
        "pillar": "Pilar 1: Manajemen Keuangan & Pembukuan",
        "pillar_badge": "CASH FLOW STATEMENT • METODE LANGSUNG",
        "theme": "finance",
        "title": "Cara Menyusun Laporan Arus Kas Metode Langsung",
        "subtitle": "Pencatatan penerimaan kas pelanggan dan pengeluaran operasional toko jasa secara transparan.",
        "card_title": "Arus Kas Masuk & Keluar Bersih",
        "badge_status": "NET CASH POSITIF",
        "rows": [
            ("Penerimaan Kas Konsumen", "Rp 42.500.000"),
            ("Pembayaran Kas ke Suplier", "(Rp 21.000.000)"),
            ("Beban Operasional & Gaji", "(Rp 12.800.000)"),
            ("Arus Kas Operasional Bersih", "+ Rp 8.700.000")
        ]
    },
    7: {
        "pillar": "Pilar 1: Manajemen Keuangan & Pembukuan",
        "pillar_badge": "OPERATING EXPENSES • RASIO BEBAN",
        "theme": "finance",
        "title": "Cara Menghitung Rasio Biaya Operasional Toko Ritel",
        "subtitle": "Operating Expense Ratio (OER), batas toleransi industri ritel, dan strategi pemangkasan pos boros.",
        "card_title": "Evaluasi Efisiensi Beban Toko",
        "badge_status": "OER IDEAL < 25%",
        "rows": [
            ("Total Penjualan Bersih Toko", "Rp 80.000.000"),
            ("Beban Gaji, Sewa & Listrik", "Rp 17.600.000"),
            ("Operating Expense Ratio", "22,0% (Sangat Sehat)"),
            ("Target Rasio Industri Ritel", "Di Bawah 25,0%")
        ]
    },
    8: {
        "pillar": "Pilar 1: Manajemen Keuangan & Pembukuan",
        "pillar_badge": "PROFIT TARGETING • LIVING COST",
        "theme": "finance",
        "title": "Cara Menghitung Target Laba Bersih Usaha Toko",
        "subtitle": "Kalkulasi kebutuhan biaya hidup keluarga pemilik, tabungan darurat, dan target omzet harian.",
        "card_title": "Kalkulasi Target Omzet Toko",
        "badge_status": "TARGET REALISTIS",
        "rows": [
            ("Biaya Hidup Keluarga / Bln", "Rp 8.000.000"),
            ("Tabungan & Investasi Toko", "Rp 3.000.000"),
            ("Target Laba Bersih Bulanan", "Rp 11.000.000"),
            ("Target Omzet Jual (Margin 30%)", "Rp 36.670.000 / Bln")
        ]
    },

    # -------------------------------------------------------------
    # PILAR 2: INVENTORI, STOK & MANAJEMEN GUDANG (9-16)
    # -------------------------------------------------------------
    9: {
        "pillar": "Pilar 2: Inventori, Stok & Manajemen Gudang",
        "pillar_badge": "RECEIVING SOP • TIGA TITIK COCOK",
        "theme": "inventory",
        "title": "SOP Penerimaan Barang Suplier Bebas Selisih",
        "subtitle": "Prosedur three-way matching antara PO, surat jalan, dan fisik barang saat bongkar muat.",
        "card_title": "Checklist Bongkar Muat Barang",
        "badge_status": "BEBAS SELISIH GUDANG",
        "rows": [
            ("Pencocokan Faktur & PO", "Purchase Order Valid"),
            ("Pemeriksaan Fisik Dus", "Kondisi Segel Utuh"),
            ("Uji Sampel Tanggal ED", "Sisa ED Minimal 80%"),
            ("Pengesahan Surat Jalan", "Cap Stempel & TTD Staf")
        ]
    },
    10: {
        "pillar": "Pilar 2: Inventori, Stok & Manajemen Gudang",
        "pillar_badge": "SHRINKAGE CONTROL • CEGAH SUSUT",
        "theme": "inventory",
        "title": "Cara Mencegah Penyusutan Barang Dagangan Toko",
        "subtitle": "Strategi memangkas susut barang akibat pengutilan, kemasan bocor, dan barang kedaluwarsa.",
        "card_title": "Indikator Penurunan Shrinkage",
        "badge_status": "SUSUT < 0,5% TOKO",
        "rows": [
            ("Tingkat Susut Toko Awal", "2,1% dari Omzet Ritel"),
            ("Penyebab Terbesar Susut", "Barang Bocor & Rusak"),
            ("Hasil Audit Fisik Rutin", "Turun Menjadi 0,35%"),
            ("Penyelamatan Margin Laba", "+ Rp 3.500.000 / Bln")
        ]
    },
    11: {
        "pillar": "Pilar 2: Inventori, Stok & Manajemen Gudang",
        "pillar_badge": "SUPPLY CHAIN • LEAD TIME ACCURACY",
        "theme": "inventory",
        "title": "Cara Menentukan Lead Time Pemesanan Barang Distributor",
        "subtitle": "Kalkulasi waktu tunggu pengiriman, durasi pemrosesan PO, dan penentuan safety stock.",
        "card_title": "Analisis Waktu Tunggu Pesanan",
        "badge_status": "TOTAL LEAD TIME 5 HARI",
        "rows": [
            ("Penyusunan & Kirim PO", "1 Hari Kerja"),
            ("Proses Siap Gudang Suplier", "1 Hari Kerja"),
            ("Durasi Ekspedisi Pengiriman", "2 Hari di Perjalanan"),
            ("Bongkar & Verifikasi Toko", "1 Hari (Siap Jual)")
        ]
    },
    12: {
        "pillar": "Pilar 2: Inventori, Stok & Manajemen Gudang",
        "pillar_badge": "WAREHOUSE FIFO • ROTASI MUTU",
        "theme": "inventory",
        "title": "Penerapan Sistem FIFO Gudang Makanan dan Bahan Baku",
        "subtitle": "Metode First-In First-Out, kode warna stiker mingguan, dan pencegahan limbah basi.",
        "card_title": "Rotasi Stok Bahan Baku Dapur",
        "badge_status": "ZERO FOOD WASTE",
        "rows": [
            ("Aturan Peletakan Barang", "Barang Baru di Belakang"),
            ("Stiker Masuk Barang", "Warna Khusus Per Minggu"),
            ("Inspeksi Fisik Harian", "Cek Stok Paling Depan"),
            ("Dampak ke Food Cost", "Limbah Turun Menjadi 0%")
        ]
    },
    13: {
        "pillar": "Pilar 2: Inventori, Stok & Manajemen Gudang",
        "pillar_badge": "INVENTORY COSTING • BIAYA PESAN",
        "theme": "inventory",
        "title": "Cara Menghitung Biaya Pemesanan Barang Toko",
        "subtitle": "Kalkulasi pulsa komunikasi, biaya administrasi PO, ongkos bongkar muat, dan rumus EOQ.",
        "card_title": "Kalkulasi Biaya Per Pesanan",
        "badge_status": "BIAYA TERIDENTIFIKASI",
        "rows": [
            ("Administrasi PO & Input Data", "Rp 15.000 / Transaksi"),
            ("Bongkar Muat Kuli Gudang", "Rp 25.000 / Pengiriman"),
            ("Biaya Verifikasi Kualitas", "Rp 10.000 / Batch"),
            ("Total Ordering Cost (S)", "Rp 50.000 Per Pesanan")
        ]
    },
    14: {
        "pillar": "Pilar 2: Inventori, Stok & Manajemen Gudang",
        "pillar_badge": "VENDOR RETURN • KLAIM RETUR",
        "theme": "inventory",
        "title": "SOP Retur Barang Rusak ke Suplier dan Faktur Pengganti",
        "subtitle": "Prosedur pembuatan Surat Berita Acara Retur, nota retur pajak, dan pemotongan saldo utang.",
        "card_title": "Alur Klaim Barang Cacat",
        "badge_status": "KLAIM RETUR RESMI",
        "rows": [
            ("Pemisahan ke Area Karantina", "Segel Khusus Retur"),
            ("Foto Bukti Cacat Produksi", "Kirim via Email & WA"),
            ("Surat Jalan Retur Barang", "3 Rangkap Bersegel"),
            ("Kompensasi Pengganti", "Potong Tagihan Faktur")
        ]
    },
    15: {
        "pillar": "Pilar 2: Inventori, Stok & Manajemen Gudang",
        "pillar_badge": "STORE MERCHANDISING • LAYOUT RAK",
        "theme": "inventory",
        "title": "Cara Menata Stok Barang Toko Sembako agar Rapi",
        "subtitle": "Penataan golden visual shelf, pemisahan bahan pangan dan sabun detergen, dan zonasi lorong.",
        "card_title": "Standar Zonasi Rak Sembako",
        "badge_status": "LAYOUT ERGONOMIS",
        "rows": [
            ("Tinggi Sejajar Mata (120-150cm)", "Barang Margin Tinggi"),
            ("Tingkat Bawah Rak (0-60cm)", "Barang Berat & Karungan"),
            ("Pemisahan Zona Kimiawi", "Sabun Terpisah dari Beras"),
            ("Papan Label Harga Rak", "Wajib Terpasang Jelas")
        ]
    },
    16: {
        "pillar": "Pilar 2: Inventori, Stok & Manajemen Gudang",
        "pillar_badge": "SKU STANDARDIZATION • KODE BARANG",
        "theme": "inventory",
        "title": "Cara Membuat Kode SKU Barang Toko Ritel Terstandar",
        "subtitle": "Struktur kode alfanumerik cerdas (Kategori-Subkategori-Merk-Varian) tanpa spasi dan tanda baca.",
        "card_title": "Contoh Struktur Format SKU",
        "badge_status": "STRUKTUR BAKU",
        "rows": [
            ("Produk: Minyak Goreng 2 Liter", "Bahan Pokok Sembako"),
            ("Kode Kategori & Subkategori", "SMB-MYK (Sembako Minyak)"),
            ("Kode Merk & Kemasan", "BCO-2L (Bimoli Pouch 2L)"),
            ("Format Kode SKU Lengkap", "SMB-MYK-BCO-2L")
        ]
    },

    # -------------------------------------------------------------
    # PILAR 3: KASIR, POS & KONTROL FRAUD (17-24)
    # -------------------------------------------------------------
    17: {
        "pillar": "Pilar 3: Kasir, POS & Kontrol Fraud",
        "pillar_badge": "REKONSILIASI NON TUNAI • CLOSING KASIR",
        "theme": "cashier",
        "title": "SOP Kasir Mengatasi Selisih Pembayaran QRIS dan EDC",
        "subtitle": "Rekonsiliasi transaksi digital, pencocokan nomor referensi RRN perbankan, dan settlement harian.",
        "card_title": "Rekonsiliasi Non Tunai Harian",
        "badge_status": "AUDIT MATCHING 100%",
        "rows": [
            ("Total Transaksi POS Digital", "Rp 6.250.000"),
            ("Mutasi Dashboard QRIS", "Rp 4.300.000"),
            ("Settlement Struk EDC Bank", "Rp 1.950.000"),
            ("Status Varian Selisih", "Rp 0 (Sempurna Cocok)")
        ]
    },
    18: {
        "pillar": "Pilar 3: Kasir, POS & Kontrol Fraud",
        "pillar_badge": "FRAUD AUDIT • CEGAH STRUK GANTUNG",
        "theme": "cashier",
        "title": "Cara Mencegah Modus Pencurian Kasir Struk Gantung",
        "subtitle": "Deteksi transaksi hold order pending tanpa cetak struk dan penguncian hak otorisasi supervisor.",
        "card_title": "Indikator Red Flags Kasir",
        "badge_status": "SISTEM TERPROTEKSI",
        "rows": [
            ("Batas Maksimal Hold Order", "Maksimal 10 Menit"),
            ("Otorisasi Hapus / Void Item", "Wajib PIN Supervisor"),
            ("Buka Laci Tanpa Jual (No Sale)", "Terekam Otomatis di Log"),
            ("Stiker Kampanye Konsumen", "Struk Gratis Jika Tak Ada")
        ]
    },
    19: {
        "pillar": "Pilar 3: Kasir, POS & Kontrol Fraud",
        "pillar_badge": "CUSTOMER SERVICE • SPEED CHECKOUT",
        "theme": "cashier",
        "title": "SOP Pelayanan Kasir Toko Ritel yang Ramah dan Cepat",
        "subtitle": "Standar keramahan 3S, pemindaian ergonomis 15 item/menit, dan penanganan antrean jam sibuk.",
        "card_title": "SLA Waktu Layanan Checkout",
        "badge_status": "CHECKOUT < 60 DETIK",
        "rows": [
            ("Greeting & Tanya Kartu Member", "Maksimal 3 Detik"),
            ("Scan Barcode Produk Per Item", "2 Detik Per Unit"),
            ("Penyelesaian Pembayaran QRIS", "15 Detik di Kasir"),
            ("Pengemasan & Packing Rapi", "15 Detik Selesai")
        ]
    },
    20: {
        "pillar": "Pilar 3: Kasir, POS & Kontrol Fraud",
        "pillar_badge": "SALES REPORT • Z-REPORT ANALYSIS",
        "theme": "cashier",
        "title": "Cara Membaca Laporan Penjualan Kasir Harian",
        "subtitle": "Analisis gross sales vs net sales, rata-rata basket size, void rate, dan margin per shift kerja.",
        "card_title": "Ringkasan Laporan Sales POS",
        "badge_status": "PERFORMA SEHAT",
        "rows": [
            ("Penjualan Kotor (Gross)", "Rp 14.250.000"),
            ("Diskon & Promo Resmi", "(Rp 850.000)"),
            ("Penjualan Bersih (Net Sales)", "Rp 13.400.000"),
            ("Average Basket Size (Ticket)", "Rp 60.900 / Konsumen")
        ]
    },
    21: {
        "pillar": "Pilar 3: Kasir, POS & Kontrol Fraud",
        "pillar_badge": "CASH DRAWER SECURITY • CASH LIMIT",
        "theme": "cashier",
        "title": "SOP Keamanan Laci Kasir POS dan Batasan Saldo",
        "subtitle": "Batas maksimal saldo uang tunai cash drawer, prosedur cash drop ke brankas, dan kunci darurat.",
        "card_title": "Tata Tertib Laci Kas Toko",
        "badge_status": "DRAWER TERKONTROL",
        "rows": [
            ("Modal Awal Laci (Float)", "Rp 400.000 Pecahan Kecil"),
            ("Batas Maksimal Saldo Laci", "Rp 2.000.000 di Meja"),
            ("Penyetoran Berkala (Cash Drop)", "Setor ke Drop Safe"),
            ("Kunci Brankas Utama", "Eksklusif Supervisor")
        ]
    },
    22: {
        "pillar": "Pilar 3: Kasir, POS & Kontrol Fraud",
        "pillar_badge": "DIGITAL PAYMENT ISSUES • QRIS TIMEOUT",
        "theme": "cashier",
        "title": "Solusi Transaksi QRIS Gagal Saldo Terpotong",
        "subtitle": "Verifikasi RRN di dashboard merchant, alur refund otomatis perbankan, dan tiket klaim BI.",
        "card_title": "Protokol Kasir Transaksi QRIS",
        "badge_status": "VERIFIKASI RRN BANK",
        "rows": [
            ("Pengecekan Pertama", "Buka Portal Merchant QRIS"),
            ("Status Settled di Portal", "Barang Boleh Diberikan"),
            ("Status Pending / Tidak Ada", "Minta Foto RRN 12 Digit"),
            ("Waktu Auto-Reversal Bank", "1x24 Jam Kerja Rekening")
        ]
    },
    23: {
        "pillar": "Pilar 3: Kasir, POS & Kontrol Fraud",
        "pillar_badge": "POS EXCEPTION AUDIT • VOID MONITORING",
        "theme": "cashier",
        "title": "Cara Mengaudit Transaksi Void Kasir Toko",
        "subtitle": "Mengenali anomali pembatalan struk jam sepi, korelasi CCTV meja kasir, dan proteksi PIN supervisor.",
        "card_title": "Parameter Audit Forensik POS",
        "badge_status": "VOID TOLERANSI < 1%",
        "rows": [
            ("Batas Wajar Rasio Void Harian", "Di Bawah 1% Total Jual"),
            ("Indikasi Red Flags Kasir", "Void Melonjak Saat Sepi"),
            ("Rekonsiliasi Video CCTV", "Cek Kehadiran Pembeli"),
            ("Struk Void Fisik", "Wajib Tanda Tangan Saksi")
        ]
    },
    24: {
        "pillar": "Pilar 3: Kasir, POS & Kontrol Fraud",
        "pillar_badge": "HARDWARE INTEGRATION • BARCODE SCAN",
        "theme": "cashier",
        "title": "SOP Penggunaan Barcode Scanner Kasir Cepat",
        "subtitle": "Teknik tembak ergonomis, penanganan kemasan basah/rusak, dan pencegahan double scan.",
        "card_title": "Standar Operasional Scanner",
        "badge_status": "AKURASI SCAN TINGGI",
        "rows": [
            ("Jarak Tembak Ideal Laser", "10 cm - 20 cm dari Produk"),
            ("Sudut Kemiringan Sensor", "15° - 30° Bebas Pantulan"),
            ("Penanganan Barcode Rusak", "Ketik Manual SKU 4 Digit"),
            ("Pencegahan Double Scan", "Auto-Block Delay 0,5 Detik")
        ]
    },

    # -------------------------------------------------------------
    # PILAR 4: LEGALITAS, PERIZINAN & KEPATUHAN PAJAK (25-32)
    # -------------------------------------------------------------
    25: {
        "pillar": "Pilar 4: Legalitas, Perizinan & Kepatuhan Pajak",
        "pillar_badge": "FOOD SAFETY • IZIN EDAR SPP-PIRT",
        "theme": "legal",
        "title": "Cara Mengurus Izin Edar PIRT Makanan Olahan",
        "subtitle": "Panduan pendaftaran online via OSS RBA dan BPOM, syarat lab uji, dan label pangan wajib.",
        "card_title": "Kelengkapan Dokumen PIRT",
        "badge_status": "MASA BERLAKU 5 TAHUN",
        "rows": [
            ("NIB Berbasis Risiko", "KBLI Industri Makanan"),
            ("Sertifikat PKP Dinas", "Penyuluhan Keamanan"),
            ("Uji Sanitasi Dapur Rumah", "Inspeksi Lapangan"),
            ("Nomor Izin Terbit", "15 Digit Resmi SPP-PIRT")
        ]
    },
    26: {
        "pillar": "Pilar 4: Legalitas, Perizinan & Kepatuhan Pajak",
        "pillar_badge": "LABOR LAW • UU CIPTA KERJA",
        "theme": "legal",
        "title": "Hak dan Kewajiban Pemilik Usaha terhadap Karyawan",
        "subtitle": "Regulasi ketenagakerjaan UMKM, aturan jam kerja lembur, kesepakatan upah, dan kompensasi PKWT.",
        "card_title": "Matriks Hubungan Industrial Toko",
        "badge_status": "PATUH UU CIPTA KERJA",
        "rows": [
            ("Standar Jam Kerja Toko", "40 Jam Kerja Per Minggu"),
            ("Upah Minimum Skala UMK", "Sesuai Kesepakatan Toko"),
            ("Hak Istirahat & Cuti Tahunan", "12 Hari Kerja Setahun"),
            ("Kompensasi Kontrak PKWT", "(Bulan / 12) x 1 Bln Gaji")
        ]
    },
    27: {
        "pillar": "Pilar 4: Legalitas, Perizinan & Kepatuhan Pajak",
        "pillar_badge": "SOCIAL SECURITY • BPJS KETENAGAKERJAAN",
        "theme": "legal",
        "title": "Cara Mendaftarkan BPJS Ketenagakerjaan Karyawan",
        "subtitle": "Panduan pendaftaran SIPP online, iuran terjangkau JKK dan JKM, serta perlindungan kecelakaan.",
        "card_title": "Simulasi Iuran BPJS Staf Toko",
        "badge_status": "IURAN SANGAT MURAH",
        "rows": [
            ("Gaji Pokok Karyawan Toko", "Rp 2.500.000 / Bulan"),
            ("Iuran JKK (Risiko Rendah)", "0,24% (Rp 6.000 / Bln)"),
            ("Iuran JKM (Kematian)", "0,30% (Rp 7.500 / Bln)"),
            ("Total Iuran Perlindungan", "Rp 13.500 / Staf / Bln")
        ]
    },
    28: {
        "pillar": "Pilar 4: Legalitas, Perizinan & Kepatuhan Pajak",
        "pillar_badge": "BUSINESS ENTITY • UD VS CV VS PT",
        "theme": "legal",
        "title": "Panduan Memilih Bentuk Usaha UD CV PT Perorangan",
        "subtitle": "Komparasi pemisahan harta pribadi, batas tanggung jawab utang, biaya akta, dan pajak penghasilan.",
        "card_title": "Perbandingan Badan Usaha UMKM",
        "badge_status": "PILIHAN TEPAT",
        "rows": [
            ("Usaha Dagang (UD)", "Tanggung Jawab Tak Terbatas"),
            ("Persekutuan CV", "Minimal 2 Orang, Akta Notaris"),
            ("PT Perorangan (UMK)", "1 Orang, Resmi Berbadan Hukum"),
            ("Pemisahan Harta Pribadi", "Terlindungi Penuh pada PT")
        ]
    },
    29: {
        "pillar": "Pilar 4: Legalitas, Perizinan & Kepatuhan Pajak",
        "pillar_badge": "TAX INCENTIVE • BEBAS PPH 500 JUTA",
        "theme": "legal",
        "title": "Syarat Bebas PPh Final Omzet di Bawah 500 Juta",
        "subtitle": "Fasilitas UU HPP PP 55/2022 bagi Wajib Pajak Orang Pribadi, cara lapor SPT 1770, dan pencatatan omzet.",
        "card_title": "Simulasi Ambang Batas Pajak",
        "badge_status": "0% PAJAK S.D. 500 JUTA",
        "rows": [
            ("Subjek Pajak yang Berhak", "Wajib Pajak Orang Pribadi"),
            ("Akumulasi Omzet s.d. 500 Juta", "Bebas PPh Final (Rp 0)"),
            ("Omzet di Atas 500 Juta", "Hanya Kena 0,5% Selisih"),
            ("Kewajiban Lapor SPT 1770", "Wajib Nihil Sebelum 31 Maret")
        ]
    },
    30: {
        "pillar": "Pilar 4: Legalitas, Perizinan & Kepatuhan Pajak",
        "pillar_badge": "B2B QUOTATION • SURAT PENAWARAN",
        "theme": "legal",
        "title": "Format Surat Penawaran Harga Resmi B2B",
        "subtitle": "Klausul termin pembayaran DP, batas masa berlaku harga 14 hari, dan ketentuan PPN pengadaan.",
        "card_title": "Komponen Wajib Dokumen B2B",
        "badge_status": "KREDIBILITAS TINGGI",
        "rows": [
            ("Nomor Surat & Kop Resmi", "Penomoran Arsip Otentik"),
            ("Masa Berlaku Penawaran", "Terkunci 14 Hari Kalender"),
            ("Term of Payment (TOP)", "DP 40%, Pelunasan TOP 14"),
            ("Status Perpajakan Faktur", "Tercantum Status PPN 11%")
        ]
    },
    31: {
        "pillar": "Pilar 4: Legalitas, Perizinan & Kepatuhan Pajak",
        "pillar_badge": "PAYROLL TAX • TARIF EFEKTIF TER",
        "theme": "legal",
        "title": "Cara Menghitung PPh Pasal 21 Karyawan Toko",
        "subtitle": "Simulasi skema Tarif Efektif Rata-Rata (TER) PP 58/2023 Kategori A, B, C dan rekonsiliasi Desember.",
        "card_title": "Tabel Potongan Pajak Payroll",
        "badge_status": "FORMULA TER PP 58/2023",
        "rows": [
            ("Status PTKP Karyawan", "TK/0 (Lajang Tanpa Anak)"),
            ("Gaji Bruto Bulanan", "Rp 6.000.000 / Bulan"),
            ("Tarif Efektif TER Kategori A", "0,75% Bulanan"),
            ("Nilai PPh 21 Dipotong Toko", "Rp 45.000 Per Bulan")
        ]
    },
    32: {
        "pillar": "Pilar 4: Legalitas, Perizinan & Kepatuhan Pajak",
        "pillar_badge": "TRADE SECRET • PERJANJIAN NDA",
        "theme": "legal",
        "title": "SOP dan Surat Perjanjian Kerahasiaan Resep Kuliner",
        "subtitle": "Perlindungan Rahasia Dagang UU No 30/2000, klausul non-compete koki, dan denda pembocoran resep.",
        "card_title": "Proteksi Rahasia Dapur Kuliner",
        "badge_status": "DILINDUNGI HUKUM",
        "rows": [
            ("Dasar Payung Regulasi", "UU No 30/2000 Rahasia Dagang"),
            ("Pengikatan Dokumen Hukum", "NDA Ditandatangani di Meterai"),
            ("Metode Pengamanan Dapur", "Sistem Bumbu Biang Premix"),
            ("Sanksi Pidana Pembocoran", "Penjara Maks 2 Thn & Denda")
        ]
    },

    # -------------------------------------------------------------
    # PILAR 5: CRM, RETENSI PELANGGAN & OMNICHANNEL (33-40)
    # -------------------------------------------------------------
    33: {
        "pillar": "Pilar 5: CRM, Retensi Pelanggan & Pemasaran Omnichannel",
        "pillar_badge": "WHATSAPP CRM • DATABASE PELANGGAN",
        "theme": "crm",
        "title": "Cara Mengumpulkan Database WhatsApp Pelanggan Kasir",
        "subtitle": "Strategi meminta kontak ramah di meja kasir, insentif poin member, dan privasi data UU PDP.",
        "card_title": "Protokol Kasir Input Nomor",
        "badge_status": "OPT-IN CONSENT 85%",
        "rows": [
            ("Waktu Input di Layar POS", "Hanya 5 Detik Saat Checkout"),
            ("Insentif Poin Pelanggan", "Diskon Member Langsung"),
            ("Penerbitan Struk Digital", "Terkirim Otomatis ke WA"),
            ("Kepatuhan Privasi Data", "Izin Sesuai UU PDP No 27/2022")
        ]
    },
    34: {
        "pillar": "Pilar 5: CRM, Retensi Pelanggan & Pemasaran Omnichannel",
        "pillar_badge": "TIERED PRICING • GROSIR & RESELLER",
        "theme": "crm",
        "title": "Strategi Harga Khusus Pelanggan Grosir Volume Belanja",
        "subtitle": "Penetapan harga bertingkat, batas minimum order (MOQ), dan proteksi margin laba bersih toko.",
        "card_title": "Tingkatan Harga Grosir Toko",
        "badge_status": "MARGIN TERKUNCI",
        "rows": [
            ("Konsumen Eceran (1-5 Pcs)", "Rp 50.000 / Pcs (Normal)"),
            ("Semi-Grosir (1 Karton 24 Pcs)", "Rp 42.000 / Pcs (Margin 28%)"),
            ("Reseller Toko (25-99 Pcs)", "Rp 38.000 / Pcs (Margin 21%)"),
            ("Distributor VIP (500+ Pcs)", "Rp 33.000 / Pcs (Margin 9%)")
        ]
    },
    35: {
        "pillar": "Pilar 5: CRM, Retensi Pelanggan & Pemasaran Omnichannel",
        "pillar_badge": "RETENTION RATE • REPEAT ORDER METRIC",
        "theme": "crm",
        "title": "Cara Menghitung Rasio Retensi Pelanggan Toko Ritel",
        "subtitle": "Formula Customer Retention Rate (CRR), penurunan churn rate, dan peningkatan Customer Lifetime Value.",
        "card_title": "Kalkulasi Loyalitas Pelanggan",
        "badge_status": "CRR SEHAT: 80%",
        "rows": [
            ("Pelanggan Awal Kuartal (S)", "800 Orang Terdaftar"),
            ("Pelanggan Baru Kuartal (N)", "250 Orang Pembeli Baru"),
            ("Pelanggan Akhir Kuartal (E)", "890 Orang Konsumen Aktif"),
            ("Customer Retention Rate", "80,0% Bertahan Belanja")
        ]
    },
    36: {
        "pillar": "Pilar 5: CRM, Retensi Pelanggan & Pemasaran Omnichannel",
        "pillar_badge": "E-COMMERCE PACKING • SOP PENGEMASAN",
        "theme": "crm",
        "title": "SOP Pengemasan Paket Pesanan Online Ekspedisi",
        "subtitle": "Teknik perlindungan tiga lapis, bubble wrap tebal, penyegelan pola H, dan lolos uji banting kurir.",
        "card_title": "Standar Perlindungan Paket",
        "badge_status": "ZERO CLAIM PECAH",
        "rows": [
            ("Lapisan Primer (Dalam)", "Plastik Kedap Segel Klip"),
            ("Lapisan Bantalan (Tengah)", "Bubble Wrap Tebal 4 Lapis"),
            ("Lapisan Luar (Outer Box)", "Kardus Karton Corrugated"),
            ("Penyegelan & Penanda", "Lakban Pola H & Stiker Fragile")
        ]
    },
    37: {
        "pillar": "Pilar 5: CRM, Retensi Pelanggan & Pemasaran Omnichannel",
        "pillar_badge": "DUNNING CADENCE • INVOICE REMINDER",
        "theme": "crm",
        "title": "Pesan Pengingat Pembayaran Invoice WhatsApp",
        "subtitle": "Template penagihan piutang sopan tapi tegas, jadwal kirim H-3, hari H tempo, dan over-due H+7.",
        "card_title": "Alur Penagihan Piutang WA",
        "badge_status": "PIUTANG TERTAGIH CEPAT",
        "rows": [
            ("Tahap 1: H-3 Hari Sebelum Tempo", "Pengingat Ramah Bersahabat"),
            ("Tahap 2: Hari H Jatuh Tempo", "Konfirmasi Eksekusi Transfer"),
            ("Tahap 3: H+3 Hari Overdue", "Panggilan Langsung Tim Finance"),
            ("Tahap 4: H+14 Hari Menunggak", "Surat Peringatan & Kunci Order")
        ]
    },
    38: {
        "pillar": "Pilar 5: CRM, Retensi Pelanggan & Pemasaran Omnichannel",
        "pillar_badge": "OMNICHANNEL SYNC • TIKTOK LIVE & POS",
        "theme": "crm",
        "title": "Strategi Omnichannel Toko Offline dan TikTok Live",
        "subtitle": "Sinkronisasi inventori real-time via API cloud, pencegahan overselling pesanan, dan fulfillment kilat.",
        "card_title": "Sinkronisasi Multi Saluran",
        "badge_status": "STOK SINKRON < 2 DETIK",
        "rows": [
            ("Kasir Toko Fisik Offline", "Penjualan Potong Stok Seketika"),
            ("TikTok Live Shopping", "Stok Online Ter-Update Instan"),
            ("Risiko Overselling", "Turun Menjadi 0% Terproteksi"),
            ("SLA Fulfillment Gudang", "Kirim Paket Dalam 24 Jam")
        ]
    },
    39: {
        "pillar": "Pilar 5: CRM, Retensi Pelanggan & Pemasaran Omnichannel",
        "pillar_badge": "DIGITAL BUSINESS CARD • NFC & QR",
        "theme": "crm",
        "title": "Cara Membuat Kartu Nama Bisnis Digital QR Code",
        "subtitle": "Format vCard satu ketuk simpan kontak, integrasi etalase katalog WhatsApp, dan kartu pintar NFC.",
        "card_title": "Spesifikasi Kartu Bisnis Cerdas",
        "badge_status": "SATU KETUK SIMPAN",
        "rows": [
            ("Teknologi Pindai Ganda", "Dynamic QR Code + Chip NFC"),
            ("Fitur One-Click Save", "Otomatis Simpan ke Kontak HP"),
            ("Tautan Etalase Terpadu", "Katalog Produk & Google Maps"),
            ("Biaya Cetak Jangka Panjang", "100% Bebas Limbah Kertas")
        ]
    },
    40: {
        "pillar": "Pilar 5: CRM, Retensi Pelanggan & Pemasaran Omnichannel",
        "pillar_badge": "NEGOTIATION DIPLOMACY • DISKON KASIR",
        "theme": "crm",
        "title": "Strategi Menangani Pelanggan Minta Diskon",
        "subtitle": "Teknik diplomasi kasir tanpa bikin tersinggung, tawaran kuantitas grosir, dan proteksi margin laba.",
        "card_title": "Matriks Diplomasi Meja Kasir",
        "badge_status": "MARGIN AMAN & RAMAH",
        "rows": [
            ("Prinsip Utama Jawaban", "Validasi Keinginan Pembeli"),
            ("Penolakan Anggun Kasir", "Jelaskan Keunggulan Mutu Bahan"),
            ("Solusi Alternatif Menguntungkan", "Tawarkan Paket Kuantitas / Bonus"),
            ("Fitur Diskon Kasir POS", "Terkunci Otorisasi Supervisor")
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

    brand_img_html = f'<img src="data:image/png;base64,{COOCA_ICON_B64}" class="brand-logo-img" alt="COOCA Logo">' if COOCA_ICON_B64 else '<div class="brand-logo-fallback">C</div>'

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
  .brand-logo-img {{
    width: 38px;
    height: 38px;
    border-radius: 9px;
    object-fit: contain;
    box-shadow: 0 4px 14px rgba(0,0,0,0.4);
    display: block;
  }}
  .brand-logo-fallback {{
    width: 38px;
    height: 38px;
    border-radius: 9px;
    background: {theme['logo_grad']};
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 20px;
    color: white;
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
      {brand_img_html}
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
      <span>⏱️ 10 Menit Baca</span>
      <span>📅 19 November 2026</span>
      <span>🎯 Panduan Aplikatif UMKM</span>
    </div>
    <div>cooca.id/blog</div>
  </div>
</body>
</html>
"""
    return html

def main():
    research_file = os.path.join('database', 'data', 'articles', 'keyword-research-2026-11-19.json')
    with open(research_file, 'r', encoding='utf-8') as f:
        data = json.load(f)

    topics = data['topics']
    print(f"Loaded {len(topics)} topics from {research_file}")

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

    print("\n[SUCCESS] All 40 Bento cover images generated successfully in public/images/blog/!")

if __name__ == "__main__":
    main()
