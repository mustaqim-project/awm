// scripts/render_cooca_blog_cover.cjs
const { chromium } = require('c:/laragon/www/cooca_core/node_modules/@playwright/test');
const fs = require('fs');
const path = require('path');

const ROOT_DIR = 'C:/laragon/www/cooca_core';
const LOGO_PATH = path.join(ROOT_DIR, 'public/assets/image/cooca-logo-landscape.png');
const ASSETS_3D_DIR = path.join(ROOT_DIR, 'public/admin/social-media-assets/pinterest-showcase');
const OUTPUT_DIR = path.join(ROOT_DIR, 'public/images/blog');

function getBase64Image(filePath) {
  if (!fs.existsSync(filePath)) return '';
  const ext = path.extname(filePath).replace('.', '') || 'png';
  const buf = fs.readFileSync(filePath);
  return `data:image/${ext};base64,${buf.toString('base64')}`;
}

const logoBase64 = getBase64Image(LOGO_PATH);

// 40 Blog Card Design Specifications
const CARDS = [
  // 1. Apa Itu Cooca
  {
    slug: 'apa-itu-cooca-business-operating-system-umkm',
    category: 'Solusi & Platform',
    title: 'Apa Itu COOCA?',
    subtitle: 'Business Operating System All-in-One untuk Seluruh Bisnis UMKM Indonesia',
    icon3d: '3d_rocket.png',
    statBadge: 'BOS UMKM #1',
    statSub: 'Platform Terintegrasi',
    kicker: 'MODUL RESMI COOCA',
    features: ['POS Kasir Penjualan', 'Stok Multi-Gudang', 'Akuntansi SAK EMKM', 'WhatsApp Gateway'],
    gradientAccent: 'rgba(37, 99, 235, 0.45)',
    accentColor: '#38BDF8'
  },
  // 2. Kenapa Pilih Cooca
  {
    slug: 'kenapa-pilih-cooca-untuk-operasional-bisnis-umkm',
    category: 'Solusi & Platform',
    title: 'Kenapa Pilih COOCA?',
    subtitle: 'Solusi Anti-Fragmentasi Aplikasi: Kendalikan Kasir, Stok, dan Keuangan dalam Satu Layar',
    icon3d: '3d_trophy.png',
    statBadge: '0 Rupiah',
    statSub: 'Mulai Solo Owner',
    kicker: 'KEUNGGULAN KOMPETITIF',
    features: ['Tanpa Biaya Langganan', 'Tanpa Kartu Kredit', 'Siap Pakai 2 Menit', 'Audit Log Lengkap'],
    gradientAccent: 'rgba(56, 189, 248, 0.45)',
    accentColor: '#38BDF8'
  },
  // 3. Cooca Adalah
  {
    slug: 'cooca-adalah-software-erp-kasir-terpadu-indonesia',
    category: 'Solusi & Platform',
    title: 'COOCA Adalah',
    subtitle: 'Software ERP & POS Kasir Terpadu Buatan Indonesia untuk Pertumbuhan Usaha Mandiri',
    icon3d: '3d_shop_store.png',
    statBadge: 'All-in-One',
    statSub: 'ERP & POS Cloud',
    kicker: 'DEFINISI PLATFORM',
    features: ['Multi-Cabang & Multi-User', 'Koneksi Cloud Real-Time', 'Desain Apple Bento Grid', 'Multi-Industri Ritel & FnB'],
    gradientAccent: 'rgba(99, 102, 241, 0.45)',
    accentColor: '#818CF8'
  },
  // 4. Keunggulan Aplikasi Cooca
  {
    slug: 'keunggulan-aplikasi-cooca-dibandingkan-software-lain',
    category: 'Solusi & Platform',
    title: 'Keunggulan Aplikasi COOCA',
    subtitle: 'Dibangun dari Sudut Pandang Nyata Pemilik Usaha: Ringan, Cepat, dan Kedaulatan Data 100%',
    icon3d: '3d_target.png',
    statBadge: '100% Milikmu',
    statSub: 'Kedaulatan Data',
    kicker: 'ARSITEKTUR MODERN',
    features: ['Isolasi Multi-Tenant', 'Zero Data Monetization', 'Apple HIG Interface', 'Kalkulator Bisnis Gratis'],
    gradientAccent: 'rgba(37, 99, 235, 0.45)',
    accentColor: '#60A5FA'
  },
  // 5. Business Operating System Cooca
  {
    slug: 'business-operating-system-cooca-solusi-manajemen-umkm',
    category: 'Solusi & Platform',
    title: 'Business Operating System',
    subtitle: 'Transformasi Toko Tradisional Menjadi Gerai Ritel Mandiri yang Rapi dan Terukur',
    icon3d: '3d_growth_arrow.png',
    statBadge: '360° Kendali',
    statSub: 'Operasional Toko',
    kicker: 'STANDARD OPERATIONAL SYSTEM',
    features: ['Standardisasi Alur Kerja', 'Hak Akses Berjenjang', 'Monitoring Real-Time', 'Single Source of Truth'],
    gradientAccent: 'rgba(56, 189, 248, 0.45)',
    accentColor: '#38BDF8'
  },
  // 6. Ekosistem Terintegrasi Cooca
  {
    slug: 'ekosistem-terintegrasi-cooca-hapus-fragmentasi-aplikasi',
    category: 'Solusi & Platform',
    title: 'Ekosistem Terintegrasi',
    subtitle: 'Akhiri Fragmentasi 5 Aplikasi Berbeda: Transaksi Kasir Langsung Memotong Jurnal dan Stok',
    icon3d: '3d_shopping_cart.png',
    statBadge: 'Tanpa CSV',
    statSub: 'Sinkronisasi Otomatis',
    kicker: 'INTEGRASI NATIVE',
    features: ['Bebas Salin Manual', 'Zero Selisih Buku', 'Dashboard Keuangan', 'Kartu Stok Real-Time'],
    gradientAccent: 'rgba(37, 99, 235, 0.45)',
    accentColor: '#38BDF8'
  },
  // 7. Paket Solo Owner Cooca
  {
    slug: 'paket-solo-owner-cooca-gratis-selamanya-usaha-mikro',
    category: 'Solusi & Platform',
    title: 'Paket Solo Owner Rp 0',
    subtitle: 'Gratis Selamanya untuk Usaha Mikro: Dukung UMKM Indonesia Mandiri Tanpa Beban Biaya',
    icon3d: '3d_gold_star.png',
    statBadge: 'Rp 0 / Bulan',
    statSub: 'Gratis Selamanya',
    kicker: 'KOMITMEN UMKM',
    features: ['POS Kasir Lengkap', 'Produk Tak Terbatas', 'Struk Digital WhatsApp', 'Cetak Bluetooth Thermal'],
    gradientAccent: 'rgba(234, 179, 8, 0.40)',
    accentColor: '#FACC15'
  },
  // 8. Keamanan Data Multi Tenant Cooca
  {
    slug: 'keamanan-data-multi-tenant-cooca-dan-kedaulatan-bisnis',
    category: 'Solusi & Platform',
    title: 'Keamanan Data Multi-Tenant',
    subtitle: 'Formula Resep, Margin, dan Database Pelanggan Adalah Rahasia Dapur Bisnis Anda',
    icon3d: '3d_bank_building.png',
    statBadge: 'SSL / TLS',
    statSub: 'Enkripsi Perbankan',
    kicker: 'DATA PRIVACY GUARANTEE',
    features: ['Isolasi Tenant Ketat', 'Audit Trail Aktivitas', 'Kunci Otorisasi PIN', 'Backup Otomatis Cloud'],
    gradientAccent: 'rgba(99, 102, 241, 0.45)',
    accentColor: '#818CF8'
  },
  // 9. Fitur Kasir POS Cooca
  {
    slug: 'fitur-kasir-pos-cooca-aplikasi-kasir-multi-platform',
    category: 'Kasir & POS',
    title: 'Fitur Kasir POS Multi-Platform',
    subtitle: 'Transaksi Cepat di Tablet, HP Android, dan PC dengan Printer Thermal & Barcode Scanner',
    icon3d: '3d_shopping_cart.png',
    statBadge: '< 2 Detik',
    statSub: 'Kecepatan Checkout',
    kicker: 'MODUL KASIR PENJUALAN',
    features: ['Printer Thermal Bluetooth', 'Barcode Scanner Cepat', 'Diskon & Pajak Otomatis', 'Mode Layar Responsif'],
    gradientAccent: 'rgba(37, 99, 235, 0.45)',
    accentColor: '#38BDF8'
  },
  // 10. Cooca Offline POS
  {
    slug: 'cooca-offline-pos-solusi-transaksi-tanpa-koneksi-internet',
    category: 'Kasir & POS',
    title: 'COOCA Offline POS',
    subtitle: 'Transaksi Tetap Lancar Tanpa Sinyal Internet: Sinkronisasi Cloud Otomatis Saat Online',
    icon3d: '3d_target.png',
    statBadge: '100% Offline',
    statSub: 'IndexedDB Caching',
    kicker: 'KASIR TAHAN MATI LISTRIK',
    features: ['Penyimpanan Lokal Aman', 'Cetak Nota Bluetooth', 'Antrean Kasir Berjalan', 'Auto-Sync ke Server'],
    gradientAccent: 'rgba(56, 189, 248, 0.45)',
    accentColor: '#38BDF8'
  },
  // 11. Pembayaran QRIS Dinamis Cooca
  {
    slug: 'pembayaran-qris-dinamis-cooca-rekonsiliasi-otomatis-kasir',
    category: 'Kasir & POS',
    title: 'Pembayaran QRIS Dinamis',
    subtitle: 'Nominal Terisi Otomatis di Layar Kasir: Cegah Bukti Transfer Palsu & Rekonsiliasi Otomatis',
    icon3d: '3d_shop_store.png',
    statBadge: '0% Selisih',
    statSub: 'Verifikasi Real-Time',
    kicker: 'METODE PEMBAYARAN',
    features: ['QRIS Dinamis Otomatis', 'Anti Nota Palsu', 'Konfirmasi Pembayaran', 'Pencatatan Rekening Jurnal'],
    gradientAccent: 'rgba(37, 99, 235, 0.45)',
    accentColor: '#60A5FA'
  },
  // 12. SOP Penutupan Shift Kasir Cooca
  {
    slug: 'sop-penutupan-shift-kasir-cooca-cegah-selisih-uang-laci',
    category: 'Kasir & POS',
    title: 'SOP Penutupan Shift Kasir',
    subtitle: 'Blind Cash Count & Cetak Rekap Serah Terima Kasir: Cegah Selisih Uang Laci Tiap Shift',
    icon3d: '3d_bank_building.png',
    statBadge: 'Blind Count',
    statSub: 'Akurat Tanpa Manipulasi',
    kicker: 'STANDAR OPERASIONAL',
    features: ['Penghitungan Kas Fisik', 'Laporan Serah Terima', 'Kunci Laci Otomatis', 'Rekonsiliasi Tunai & QRIS'],
    gradientAccent: 'rgba(99, 102, 241, 0.45)',
    accentColor: '#818CF8'
  },
  // 13. Integrasi Printer Dapur Cooca
  {
    slug: 'integrasi-printer-dapur-cooca-kelola-antrean-kafe-resto',
    category: 'Kasir & POS',
    title: 'Integrasi Printer Dapur Resto',
    subtitle: 'Pemisahan Tiket Pesanan Bar Minuman & Dapur Makanan Otomatis untuk Kafe dan Restoran',
    icon3d: '3d_rocket.png',
    statBadge: 'Kitchen Sync',
    statSub: 'Multi-Printer Lan/Wifi',
    kicker: 'SOLUSI F&B KULINER',
    features: ['Split Order Dapur & Bar', 'Nomor Meja Pelanggan', 'Status Tiket Pesanan', 'Antrean Tanpa Terlewat'],
    gradientAccent: 'rgba(234, 88, 12, 0.45)',
    accentColor: '#FB923C'
  },
  // 14. Fitur Void dan Pembatalan Nota Cooca
  {
    slug: 'fitur-void-dan-pembatalan-nota-cooca-otorisasi-supervisor',
    category: 'Kasir & POS',
    title: 'Fitur Void & Otorisasi PIN',
    subtitle: 'Proteksi Transaksi Kasir Sensitif: Pembatalan Nota Wajib Persetujuan Supervisor Toko',
    icon3d: '3d_target.png',
    statBadge: 'PIN Guard',
    statSub: 'Otorisasi Supervisor',
    kicker: 'PENCEGAHAN FRAUD',
    features: ['Proteksi PIN Khusus', 'Audit Trail Transaksi', 'Alasan Pembatalan Nota', 'Pengembalian Stok Otomatis'],
    gradientAccent: 'rgba(239, 68, 68, 0.40)',
    accentColor: '#F87171'
  },
  // 15. Struk Digital WhatsApp Cooca
  {
    slug: 'struk-digital-whatsapp-cooca-hemat-kertas-thermal-kasir',
    category: 'Kasir & POS',
    title: 'Struk Digital WhatsApp',
    subtitle: 'Kirim Nota Belanja Langsung ke Chat Pelanggan: Hemat Biaya Kertas & Kumpulkan Database',
    icon3d: '3d_shopping_bag.png',
    statBadge: 'Hemat Biaya',
    statSub: 'Paperless Receipt',
    kicker: 'KASIR RAMAH LINGKUNGAN',
    features: ['Kirim Otomatis ke WA', 'Katalog Promo Toko', 'Hemat Kertas Thermal', 'Database Pelanggan Sukarela'],
    gradientAccent: 'rgba(34, 197, 94, 0.40)',
    accentColor: '#4ADE80'
  },
  // 16. Menu QR Code Meja Cooca
  {
    slug: 'menu-qr-code-meja-cooca-pesan-mandiri-restoran-kafe',
    category: 'Kasir & POS',
    title: 'Menu QR Code Pesan Meja',
    subtitle: 'Pelanggan Scan Barcode Meja, Pilih Menu Makanan, dan Bayar Mandiri Tanpa Antre di Meja Kasir',
    icon3d: '3d_shop_store.png',
    statBadge: 'Self-Order',
    statSub: 'QR Meja Restoran',
    kicker: 'MODERNISASI RESTORAN',
    features: ['Scan QR Meja Mandiri', 'Foto Menu HD & Varian', 'Checkout Pembayaran QRIS', 'Terhubung ke Dapur'],
    gradientAccent: 'rgba(56, 189, 248, 0.45)',
    accentColor: '#38BDF8'
  },
  // 17. Manajemen Inventori Cooca
  {
    slug: 'manajemen-inventori-cooca-pantau-stok-real-time',
    category: 'Inventori & Gudang',
    title: 'Manajemen Inventori Real-Time',
    subtitle: 'Pantau Saldo Persediaan Barang Otomatis: Setiap Penjualan Kasir Langsung Potong Kartu Stok',
    icon3d: '3d_pie_chart.png',
    statBadge: 'Real-Time',
    statSub: 'Kartu Stok Digital',
    kicker: 'MODUL GUDANG & INVENTORI',
    features: ['Kartu Stok Otomatis', 'Lacak Masuk & Keluar', 'Valuasi Nilai Persediaan', 'Peringatan Selisih'],
    gradientAccent: 'rgba(37, 99, 235, 0.45)',
    accentColor: '#38BDF8'
  },
  // 18. Fitur Multi Gudang Cooca
  {
    slug: 'fitur-multi-gudang-cooca-transfer-stok-antar-cabang',
    category: 'Inventori & Gudang',
    title: 'Fitur Multi-Gudang & Cabang',
    subtitle: 'Kelola Pergerakan Barang Antar Outlet dengan Surat Jalan Digital dan Alokasi Stok Presisi',
    icon3d: '3d_shop_store.png',
    statBadge: 'Multi-Outlet',
    statSub: 'Transfer Antar Gudang',
    kicker: 'LOGISTIK CABANG',
    features: ['Surat Jalan Digital', 'Approval Terima Barang', 'Stok Transit Terpantau', 'Cegah Kehilangan Barang'],
    gradientAccent: 'rgba(99, 102, 241, 0.45)',
    accentColor: '#818CF8'
  },
  // 19. Resep dan Bill of Materials Cooca
  {
    slug: 'resep-dan-bill-of-materials-cooca-kalkulasi-bahan-baku',
    category: 'Inventori & Gudang',
    title: 'Resep & Bill of Materials (BOM)',
    subtitle: 'Kalkulasi Otomatis Bahan Baku Kuliner & Produksi: Potong Stok per Gram Saat Menu Terjual',
    icon3d: '3d_rocket.png',
    statBadge: 'Gram/Mililiter',
    statSub: 'Pemotongan Presisi',
    kicker: 'FORMULA PRODUKSI',
    features: ['Resep Takaran Bahan', 'Auto-Deduct Kasir', 'Pencegahan Kebocoran Dapur', 'HPP Makanan Real-Time'],
    gradientAccent: 'rgba(234, 179, 8, 0.40)',
    accentColor: '#FACC15'
  },
  // 20. Stok Opname Berkala Cooca
  {
    slug: 'stok-opname-berkala-cooca-cocokkan-stok-fisik-sistem',
    category: 'Inventori & Gudang',
    title: 'Stok Opname Tanpa Tutup Toko',
    subtitle: 'Pencocokan Stok Fisik vs Sistem Menggunakan Barcode Scanner dengan Jurnal Penyesuaian Otomatis',
    icon3d: '3d_target.png',
    statBadge: 'Cepat & Rapi',
    statSub: 'Audit Fisik Gudang',
    kicker: 'AUDIT PERSEDIAAN',
    features: ['Scan Barcode Fisik', 'Jurnal Penyesuaian Otomatis', 'Laporan Selisih Stok', 'Toko Tetap Beroperasi'],
    gradientAccent: 'rgba(37, 99, 235, 0.45)',
    accentColor: '#38BDF8'
  },
  // 21. Peringatan Stok Menipis Cooca
  {
    slug: 'peringatan-stok-menipis-cooca-reorder-point-otomatis',
    category: 'Inventori & Gudang',
    title: 'Peringatan Stok & Reorder Point',
    subtitle: 'Notifikasi Otomatis Sebelum Barang Habis: Cegah Kehilangan Pelanggan Akibat Stok Kosong',
    icon3d: '3d_trophy.png',
    statBadge: 'Anti Out-of-Stock',
    statSub: 'Notifikasi Otomatis',
    kicker: 'MANAJEMEN PENGADAAN',
    features: ['Batas Minimum Stok', 'Draft Purchase Order', 'Lead Time Pengiriman', 'Prioritas Fast Moving'],
    gradientAccent: 'rgba(56, 189, 248, 0.45)',
    accentColor: '#38BDF8'
  },
  // 22. Barcode Scanner dan SKU Cooca
  {
    slug: 'barcode-scanner-dan-sku-cooca-kelola-ribuan-barang-ritel',
    category: 'Inventori & Gudang',
    title: 'Barcode Scanner & SKU Barang',
    subtitle: 'Kelola Ribuan Barang Ritel dan Minimarket dengan Cetak Label Barcode & Pencarian 1 Detik',
    icon3d: '3d_shopping_cart.png',
    statBadge: '< 1 Detik',
    statSub: 'Scan Barcode Kasir',
    kicker: 'EFISIENSI RITEL',
    features: ['Generate SKU Otomatis', 'Cetak Label Barcode', 'Support Scanner USB/BT', 'Pencarian Instan'],
    gradientAccent: 'rgba(37, 99, 235, 0.45)',
    accentColor: '#60A5FA'
  },
  // 23. Lacak Batch dan Kedaluwarsa Cooca
  {
    slug: 'lacak-batch-dan-kedaluwarsa-cooca-metode-fefo-gudang',
    category: 'Inventori & Gudang',
    title: 'Lacak Batch & Metode FEFO',
    subtitle: 'Manajemen Kedaluwarsa First Expired First Out untuk Apotek, Kosmetik, dan Toko Makanan',
    icon3d: '3d_target.png',
    statBadge: 'FEFO Ready',
    statSub: 'Anti Barang Basi',
    kicker: 'KEAMANAN PRODUK',
    features: ['Nomor Batch Produksi', 'Tanggal Kedaluwarsa', 'Peringatan Expired', 'Rotasi Barang Prioritas'],
    gradientAccent: 'rgba(234, 88, 12, 0.45)',
    accentColor: '#FB923C'
  },
  // 24. Manajemen Retur Pemasok Cooca
  {
    slug: 'manajemen-retur-pemasok-cooca-kembalikan-barang-rusak',
    category: 'Inventori & Gudang',
    title: 'Manajemen Retur Pemasok',
    subtitle: 'Dokumentasi Pengembalian Barang Rusak: Otomatis Potong Utang Dagang & Perbarui Stok Gudang',
    icon3d: '3d_bank_building.png',
    statBadge: 'Potong Utang',
    statSub: 'Retur Dagang Otomatis',
    kicker: 'KENDALI SUPPLIER',
    features: ['Formulir Retur Sah', 'Verifikasi Nota Beli', 'Koreksi Saldo Utang', 'Perbarui Kartu Stok'],
    gradientAccent: 'rgba(99, 102, 241, 0.45)',
    accentColor: '#818CF8'
  },
  // 25. Laporan Laba Rugi Otomatis Cooca
  {
    slug: 'laporan-laba-rugi-otomatis-cooca-evaluasi-keuntungan',
    category: 'Keuangan & Akuntansi',
    title: 'Laporan Laba Rugi Otomatis',
    subtitle: 'Evaluasi Keuntungan Riil Usaha Anda: Pendapatan Kotor, Beban Operasional, dan Net Profit Margin',
    icon3d: '3d_pie_chart.png',
    statBadge: 'Real Net Profit',
    statSub: 'Laba Bersih Akurat',
    kicker: 'LAPORAN KEUANGAN BISNIS',
    features: ['Pendapatan Penjualan', 'HPP Riil Terhitung', 'Biaya Operasional Toko', 'Grafik Margin Bersih'],
    gradientAccent: 'rgba(37, 99, 235, 0.45)',
    accentColor: '#38BDF8'
  },
  // 26. Neraca Keuangan SAK EMKM Cooca
  {
    slug: 'neraca-keuangan-sak-emkm-cooca-pantau-posisi-aset-usaha',
    category: 'Keuangan & Akuntansi',
    title: 'Neraca Keuangan SAK EMKM',
    subtitle: 'Pantau Posisi Aset Lancar, Kas Toko, Piutang, Persediaan Barang, dan Liabilitas Utang Usaha',
    icon3d: '3d_bank_building.png',
    statBadge: 'SAK EMKM',
    statSub: 'Standar Akuntansi Resmi',
    kicker: 'KESEHATAN MODAL',
    features: ['Aset Lancar & Tetap', 'Kewajiban Utang Dagang', 'Kekayaan Bersih Usaha', 'Format Pengajuan Bank'],
    gradientAccent: 'rgba(99, 102, 241, 0.45)',
    accentColor: '#818CF8'
  },
  // 27. Kalkulator HPP Cooca
  {
    slug: 'kalkulator-hpp-cooca-activity-based-costing-kuliner',
    category: 'Keuangan & Akuntansi',
    title: 'Kalkulator HPP & ABC Costing',
    subtitle: 'Hitung Biaya Bahan Baku Langsung, Tenaga Kerja, Kemasan Paket, dan Tentukan Margin Laba Sehat',
    icon3d: '3d_target.png',
    statBadge: 'Presisi HPP',
    statSub: 'Activity-Based Costing',
    kicker: 'SIMULASI HARGA JUAL',
    features: ['Bahan Baku Langsung', 'Biaya Tenaga Kerja', 'Biaya Kemasan Produk', 'Markup Margin Laba'],
    gradientAccent: 'rgba(234, 179, 8, 0.40)',
    accentColor: '#FACC15'
  },
  // 28. Kalkulator BEP Cooca
  {
    slug: 'kalkulator-bep-cooca-hitung-titik-impas-usaha-baru',
    category: 'Keuangan & Akuntansi',
    title: 'Kalkulator Titik Impas (BEP)',
    subtitle: 'Ketahui Berapa Porsi Makanan atau Unit Barang yang Harus Terjual Setiap Hari untuk Balik Modal',
    icon3d: '3d_growth_arrow.png',
    statBadge: 'Target Harian',
    statSub: 'Break Even Point Unit',
    kicker: 'STRATEGI PERMODALAN',
    features: ['Biaya Tetap Bulanan', 'Biaya Variabel per Unit', 'Harga Jual Produk', 'BEP Unit & Rupiah'],
    gradientAccent: 'rgba(56, 189, 248, 0.45)',
    accentColor: '#38BDF8'
  },
  // 29. Arus Kas dan Rekonsiliasi Bank Cooca
  {
    slug: 'arus-kas-dan-rekonsiliasi-bank-cooca-pantau-likuiditas',
    category: 'Keuangan & Akuntansi',
    title: 'Arus Kas & Rekonsiliasi Bank',
    subtitle: 'Cocokkan Rekening Koran Bank dengan Transaksi Kas Masuk dan Keluar untuk Menjaga Likuiditas',
    icon3d: '3d_bank_building.png',
    statBadge: 'Likuiditas Aman',
    statSub: 'Cash Flow Real-Time',
    kicker: 'MANAJEMEN KAS TOKO',
    features: ['Pencatatan Kas Masuk/Keluar', 'Rekonsiliasi Rekening Bank', 'Monitoring Saldo Kasir', 'Pencegahan Cash Drag'],
    gradientAccent: 'rgba(37, 99, 235, 0.45)',
    accentColor: '#38BDF8'
  },
  // 30. Manajemen Piutang dan Invoice Tempo Cooca
  {
    slug: 'manajemen-piutang-dan-invoice-tempo-cooca-b2b',
    category: 'Keuangan & Akuntansi',
    title: 'Manajemen Piutang & Invoice Tempo',
    subtitle: 'Kelola Batas Kredit (Plafon), Tanggal Jatuh Tempo Transaksi B2B, dan Notifikasi Tagihan Otomatis',
    icon3d: '3d_shopping_bag.png',
    statBadge: 'Jatuh Tempo',
    statSub: 'Aging Piutang Dagang',
    kicker: 'TRANSAKSI GROSIR & B2B',
    features: ['Faktur Penjualan Tempo', 'Plafon Kredit Pelanggan', 'Pengingat Otomatis WA', 'Pencatatan Pelunasan'],
    gradientAccent: 'rgba(99, 102, 241, 0.45)',
    accentColor: '#818CF8'
  },
  // 31. Pencatatan Pajak PPh Final Cooca
  {
    slug: 'pencatatan-pajak-pph-final-cooca-tarif-setengah-persen',
    category: 'Keuangan & Akuntansi',
    title: 'Pajak PPh Final 0,5% PP 55',
    subtitle: 'Rekap Omzet Bruto Otomatis, Pembebasan Batas 500 Juta Orang Pribadi, dan Pembuatan Billing Pajak',
    icon3d: '3d_pie_chart.png',
    statBadge: 'PPh Final 0.5%',
    statSub: 'Kepatuhan PP 55/2022',
    kicker: 'PERPAJAKAN UMKM',
    features: ['Rekap Omzet Bulanan', 'Threshold 500 Juta', 'Perhitungan Pajak Akurat', 'Kesiapan SPT Tahunan'],
    gradientAccent: 'rgba(234, 179, 8, 0.40)',
    accentColor: '#FACC15'
  },
  // 32. Manajemen Kasbon Karyawan Cooca
  {
    slug: 'manajemen-kasbon-karyawan-cooca-potong-gaji-disiplin',
    category: 'Keuangan & Akuntansi',
    title: 'Manajemen Kasbon Karyawan',
    subtitle: 'Tata Kelola Pinjaman Staf yang Rapi: Penetapan Plafon, Approval Manajer, dan Auto-Potong Gaji',
    icon3d: '3d_trophy.png',
    statBadge: 'Auto-Deduct',
    statSub: 'Potong Gaji Disiplin',
    kicker: 'PENGELOLAAN STAF',
    features: ['Plafon Kasbon Disiplin', 'Persetujuan Manajer Toko', 'Riwayat Cicilan Pinjaman', 'Pemotongan Slip Gaji'],
    gradientAccent: 'rgba(37, 99, 235, 0.45)',
    accentColor: '#38BDF8'
  },
  // 33. WhatsApp Gateway Resmi Cooca
  {
    slug: 'whatsapp-gateway-resmi-cooca-broadcast-anti-blokir',
    category: 'CRM & Komunikasi',
    title: 'WhatsApp Gateway Resmi Meta',
    subtitle: 'Broadcast Promosi & Notifikasi Pesanan Tanpa Takut Nomor Diblokir Berbasis Cloud API Resmi',
    icon3d: '3d_rocket.png',
    statBadge: 'Anti-Banned',
    statSub: 'Official Cloud API Meta',
    kicker: 'KOMUNIKASI PELANGGAN',
    features: ['Kirim Struk Digital', 'Broadcast Promo Resmi', 'Multi-Admin Customer Service', 'Status Terkirim & Dibaca'],
    gradientAccent: 'rgba(34, 197, 94, 0.45)',
    accentColor: '#4ADE80'
  },
  // 34. CRM dan Loyalitas Pelanggan Cooca
  {
    slug: 'crm-dan-loyalitas-pelanggan-cooca-tingkatkan-repeat-order',
    category: 'CRM & Komunikasi',
    title: 'CRM & Poin Loyalitas Toko',
    subtitle: 'Kumpulkan Database Pembeli di Kasir POS, Akumulasi Poin Belanja, dan Berikan Voucher Hadiah',
    icon3d: '3d_gold_star.png',
    statBadge: 'Repeat Order',
    statSub: 'Program Loyalitas Poin',
    kicker: 'RETENSI PEMBELI',
    features: ['Poin Belanja Kasir POS', 'Voucher Diskon Eksklusif', 'Tingkat Tier Membership', 'Pemberitahuan Ulang Tahun'],
    gradientAccent: 'rgba(234, 179, 8, 0.45)',
    accentColor: '#FACC15'
  },
  // 35. Segmentasi Pelanggan Otomatis Cooca
  {
    slug: 'segmentasi-pelanggan-otomatis-cooca-promosi-tepat-sasaran',
    category: 'CRM & Komunikasi',
    title: 'Segmentasi Pelanggan Otomatis',
    subtitle: 'Kelompokkan Pembeli Baru, Pelanggan Aktif, dan Pelanggan Pasif 60 Hari untuk Promosi Personal',
    icon3d: '3d_target.png',
    statBadge: 'Targeted CRM',
    statSub: 'Promosi Tepat Sasaran',
    kicker: 'DATABASE ANALYTICS',
    features: ['Pelanggan Baru vs Loyal', 'Daftar Pembeli Pasif', 'Broadcast Promo Reaktivasi', 'Tingkat Konversi Chat'],
    gradientAccent: 'rgba(56, 189, 248, 0.45)',
    accentColor: '#38BDF8'
  },
  // 36. Modul HRM dan Absensi GPS Cooca
  {
    slug: 'modul-hrm-dan-absensi-gps-cooca-kelola-shift-karyawan',
    category: 'HRM & Operasional',
    title: 'Modul HRM & Absensi GPS Toko',
    subtitle: 'Jadwal Shift Fleksibel & Absensi Selfie Radius Toko: Cegah Titip Absen dan Disiplinkan Karyawan',
    icon3d: '3d_shop_store.png',
    statBadge: 'Radius GPS',
    statSub: 'Absensi Toko Akurat',
    kicker: 'MANAJEMEN SDM TOKO',
    features: ['Jadwal Shift Bergilir', 'Radius Lokasi Toko GPS', 'Rekap Kehadiran Real-Time', 'Pengajuan Cuti Online'],
    gradientAccent: 'rgba(37, 99, 235, 0.45)',
    accentColor: '#38BDF8'
  },
  // 37. Penggajian dan Payroll Otomatis Cooca
  {
    slug: 'penggajian-dan-payroll-otomatis-cooca-slip-gaji-digital',
    category: 'HRM & Operasional',
    title: 'Penggajian & Slip Gaji Digital',
    subtitle: 'Hitung Take Home Pay Staf Lengkap dengan Upah Lembur, Komisi Penjualan Kasir, dan Potongan Kasbon',
    icon3d: '3d_bank_building.png',
    statBadge: 'Auto-Payroll',
    statSub: 'Kirim Slip via WhatsApp',
    kicker: 'PENGGAJIAN KARYAWAN',
    features: ['Gaji Pokok & Tunjangan', 'Hitung Lembur Depnaker', 'Komisi Omzet Penjualan', 'Slip Gaji Digital PDF/WA'],
    gradientAccent: 'rgba(99, 102, 241, 0.45)',
    accentColor: '#818CF8'
  },
  // 38. Hak Akses Karyawan Berbasis Role Cooca
  {
    slug: 'hak-akses-karyawan-berbasis-role-cooca-cegah-kebocoran',
    category: 'HRM & Operasional',
    title: 'Hak Akses Berbasis Role (RBAC)',
    subtitle: 'Batasi Wewenang Kasir, Supervisor, dan Staf Gudang: Cegah Kebocoran Data Finansial Toko',
    icon3d: '3d_target.png',
    statBadge: 'Role Guard',
    statSub: 'Proteksi Wewenang',
    kicker: 'KEAMANAN OPERASIONAL',
    features: ['Role Kasir Operasional', 'Role Supervisor Toko', 'Role Staf Gudang Logistik', 'Role Akuntan & Owner'],
    gradientAccent: 'rgba(37, 99, 235, 0.45)',
    accentColor: '#60A5FA'
  },
  // 39. Storefront Online dan Katalog Digital Cooca
  {
    slug: 'storefront-online-dan-katalog-digital-cooca-jual-mandiri',
    category: 'Storefront & Omnichannel',
    title: 'Storefront & Katalog Online',
    subtitle: 'Buka Toko Online Mandiri dalam 5 Menit: Terhubung Langsung ke Kasir POS & Ekspedisi Pengiriman',
    icon3d: '3d_shopping_bag.png',
    statBadge: 'Toko Mandiri',
    statSub: 'Bebas Komisi Ekstra',
    kicker: 'PENJUALAN ONLINE',
    features: ['Katalog Produk Digital', 'Checkout Pengiriman Kurir', 'Notifikasi Pesanan Kasir', 'Bebas Potongan Komisi'],
    gradientAccent: 'rgba(56, 189, 248, 0.45)',
    accentColor: '#38BDF8'
  },
  // 40. Integrasi Omnichannel Marketplace Cooca
  {
    slug: 'integrasi-omnichannel-marketplace-cooca-sinkronkan-toko',
    category: 'Storefront & Omnichannel',
    title: 'Integrasi Omnichannel Marketplace',
    subtitle: 'Sinkronisasi Stok Otomatis Shopee, Tokopedia, dan Toko Fisik: Selamat Tinggal Overselling Barang',
    icon3d: '3d_shopping_cart.png',
    statBadge: 'Omnichannel',
    statSub: 'Stok Terhubung Real-Time',
    kicker: 'MULTI-CHANNEL RETAIL',
    features: ['Sinkronisasi Stok Otomatis', 'Integrasi Multi-Marketplace', 'Laporan Omzet Konsolidasi', 'Cegah Penalti Pembatalan'],
    gradientAccent: 'rgba(37, 99, 235, 0.45)',
    accentColor: '#38BDF8'
  }
];

async function generateAllCoocaBentoCovers() {
  console.log(`[Cooca Bento Engine] Starting generation of ${CARDS.length} custom editorial covers (1200x675)...`);

  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({
    viewport: { width: 1200, height: 675 },
    deviceScaleFactor: 1
  });
  const page = await context.newPage();

  for (let i = 0; i < CARDS.length; i++) {
    const card = CARDS[i];
    const icon3dPath = path.join(ASSETS_3D_DIR, card.icon3d);
    const icon3dBase64 = getBase64Image(icon3dPath);

    const html = `<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<style>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900;1,700&family=Space+Grotesk:wght@600;700&display=swap');

* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

body {
  width: 1200px;
  height: 675px;
  background-color: #070B19;
  background-image: 
    radial-gradient(circle at 88% 18%, ${card.gradientAccent} 0%, transparent 45%),
    radial-gradient(circle at 12% 82%, rgba(56, 189, 248, 0.18) 0%, transparent 42%),
    radial-gradient(circle at 50% 50%, rgba(15, 23, 42, 0.95) 0%, #070B19 100%);
  font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
  color: #FFFFFF;
  overflow: hidden;
  position: relative;
  padding: 44px 56px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

/* Precision Geometric Mesh Grid */
.grid-overlay {
  position: absolute;
  inset: 0;
  background-size: 36px 36px;
  background-image: 
    linear-gradient(to right, rgba(255, 255, 255, 0.024) 1px, transparent 1px),
    linear-gradient(to bottom, rgba(255, 255, 255, 0.024) 1px, transparent 1px);
  pointer-events: none;
  z-index: 1;
}

.content {
  position: relative;
  z-index: 2;
  height: 100%;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

/* Header: Logo and Official Platform Pill */
.header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  padding-bottom: 18px;
}

.brand-wrapper {
  display: flex;
  align-items: center;
  gap: 14px;
}

.brand-logo {
  height: 38px;
  width: auto;
  object-fit: contain;
}

.platform-pill {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: rgba(37, 99, 235, 0.15);
  border: 1px solid rgba(56, 189, 248, 0.35);
  padding: 6px 14px;
  border-radius: 999px;
  font-family: 'Space Grotesk', sans-serif;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: #38BDF8;
}

.status-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: #38BDF8;
  box-shadow: 0 0 10px #38BDF8;
}

/* Main Bento Stage */
.main-stage {
  display: grid;
  grid-template-columns: 1.35fr 0.85fr;
  gap: 36px;
  align-items: center;
  margin: auto 0;
}

.text-col {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.kicker {
  font-family: 'Space Grotesk', sans-serif;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.15em;
  text-transform: uppercase;
  color: ${card.accentColor};
}

.main-title {
  font-size: 40px;
  font-weight: 900;
  line-height: 1.15;
  letter-spacing: -0.025em;
  color: #FFFFFF;
}

.subtitle {
  font-size: 15px;
  font-weight: 500;
  line-height: 1.5;
  color: rgba(226, 232, 240, 0.85);
  max-width: 580px;
}

/* Feature Check Pills */
.features-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
  margin-top: 6px;
}

.feat-item {
  display: flex;
  align-items: center;
  gap: 8px;
  background: rgba(15, 23, 42, 0.7);
  border: 1px solid rgba(255, 255, 255, 0.08);
  padding: 8px 12px;
  border-radius: 10px;
  font-size: 12px;
  font-weight: 600;
  color: #F1F5F9;
}

.check-icon {
  width: 14px;
  height: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(56, 189, 248, 0.2);
  color: #38BDF8;
  border-radius: 50%;
  font-size: 9px;
  font-weight: 900;
}

/* Right Bento Cockpit */
.visual-col {
  position: relative;
  background: linear-gradient(145deg, rgba(30, 41, 59, 0.5), rgba(15, 23, 42, 0.75));
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 24px;
  padding: 28px 24px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.6), inset 0 1px 0 rgba(255, 255, 255, 0.1);
  min-height: 290px;
}

.visual-3d-img {
  width: 140px;
  height: 140px;
  object-fit: contain;
  margin-bottom: 14px;
  filter: drop-shadow(0 15px 25px rgba(0, 0, 0, 0.5));
}

.stat-badge {
  font-family: 'Space Grotesk', sans-serif;
  font-size: 26px;
  font-weight: 800;
  letter-spacing: -0.02em;
  color: #FFFFFF;
}

.stat-sub {
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: ${card.accentColor};
  margin-top: 2px;
}

/* Footer: Domain and Guarantee */
.footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
  padding-top: 18px;
}

.footer-domain {
  font-family: 'Space Grotesk', sans-serif;
  font-size: 14px;
  font-weight: 700;
  letter-spacing: 0.08em;
  color: #38BDF8;
}

.footer-trust {
  font-size: 12px;
  font-weight: 500;
  color: rgba(148, 163, 184, 0.8);
}
</style>
</head>
<body>
<div class="grid-overlay"></div>
<div class="content">
  <div class="header">
    <div class="brand-wrapper">
      <img class="brand-logo" src="${logoBase64}" alt="COOCA Logo">
    </div>
    <div class="platform-pill">
      <div class="status-dot"></div>
      ${card.category}
    </div>
  </div>

  <div class="main-stage">
    <div class="text-col">
      <div class="kicker">${card.kicker}</div>
      <h1 class="main-title">${card.title}</h1>
      <p class="subtitle">${card.subtitle}</p>
      <div class="features-grid">
        ${card.features.map(f => `
          <div class="feat-item">
            <span class="check-icon">&#10003;</span>
            <span>${f}</span>
          </div>
        `).join('')}
      </div>
    </div>

    <div class="visual-col">
      <img class="visual-3d-img" src="${icon3dBase64}" alt="${card.title}">
      <div class="stat-badge">${card.statBadge}</div>
      <div class="stat-sub">${card.statSub}</div>
    </div>
  </div>

  <div class="footer">
    <div class="footer-domain">cooca.id</div>
    <div class="footer-trust">Business Operating System Terpadu &bull; Dibuat untuk UMKM Indonesia</div>
  </div>
</div>
</body>
</html>`;

    await page.setContent(html, { waitUntil: 'networkidle' });
    const outputFilename = `cooca-${card.slug}.jpg`;
    const outputPath = path.join(OUTPUT_DIR, outputFilename);

    await page.screenshot({
      path: outputPath,
      type: 'jpeg',
      quality: 90
    });

    console.log(`[${i + 1}/40] Rendered: ${outputFilename}`);
  }

  await browser.close();
  console.log(`\nAll 40 custom editorial covers successfully rendered into public/images/blog/!`);
}

generateAllCoocaBentoCovers().catch(err => {
  console.error('Fatal rendering error:', err);
  process.exit(1);
});
