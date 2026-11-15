// scripts/render_clickbait_covers.cjs
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

const CARDS = [
  {
    "slug": "bohong-besar-aplikasi-pos-kasir-multi-platform-umkm",
    "category": "POS Kasir",
    "title": "Bohong Besar Kasir Murahan",
    "subtitle": "Mengapa Aplikasi POS Kasir Multi Platform Wajib Dimiliki Sebelum Bangkrut",
    "icon3d": "3d_shop_store.png",
    "statBadge": "COOCA BOS",
    "statSub": "APLIKASI POS KASIR MULTI",
    "kicker": "INVESTIGASI BISNIS UMKM",
    "features": [
      "Aplikasi Pos Kasir Multi Pla",
      "Real-Time Cloud Sync",
      "Zero Em-Dash Verified",
      "Standar Apple Bento Grid"
    ],
    "gradientAccent": "rgba(37, 99, 235, 0.45)",
    "accentColor": "#38BDF8"
  },
  {
    "slug": "bahaya-toko-tanpa-aplikasi-kasir-offline-tanpa-internet",
    "category": "POS Kasir",
    "title": "Internet Mati Toko Mati",
    "subtitle": "Bahaya Nyata Tanpa Aplikasi Kasir Offline Tanpa Internet di Jam Sibuk",
    "icon3d": "3d_rocket.png",
    "statBadge": "COOCA BOS",
    "statSub": "APLIKASI KASIR OFFLINE T",
    "kicker": "INVESTIGASI BISNIS UMKM",
    "features": [
      "Aplikasi Kasir Offline Tanpa",
      "Real-Time Cloud Sync",
      "Zero Em-Dash Verified",
      "Standar Apple Bento Grid"
    ],
    "gradientAccent": "rgba(37, 99, 235, 0.45)",
    "accentColor": "#38BDF8"
  },
  {
    "slug": "skandal-tiket-kertas-kitchen-display-system-restoran-kafe",
    "category": "POS Kasir",
    "title": "Kertas Struk Dapur Bikin Rugi",
    "subtitle": "Skandal Makanan Nyasar dan Solusi Kitchen Display System Restoran Kafe",
    "icon3d": "3d_growth_arrow.png",
    "statBadge": "COOCA BOS",
    "statSub": "KITCHEN DISPLAY SYSTEM R",
    "kicker": "INVESTIGASI BISNIS UMKM",
    "features": [
      "Kitchen Display System Resto",
      "Real-Time Cloud Sync",
      "Zero Em-Dash Verified",
      "Standar Apple Bento Grid"
    ],
    "gradientAccent": "rgba(37, 99, 235, 0.45)",
    "accentColor": "#38BDF8"
  },
  {
    "slug": "bongkar-modus-curang-sop-penutupan-shift-kasir-toko",
    "category": "POS Kasir",
    "title": "Uang Laci Kasir Selalu Selisih",
    "subtitle": "Bongkar Modus Staf Nakal Lewat SOP Penutupan Shift Kasir Toko",
    "icon3d": "3d_trophy.png",
    "statBadge": "COOCA BOS",
    "statSub": "SOP PENUTUPAN SHIFT KASI",
    "kicker": "INVESTIGASI BISNIS UMKM",
    "features": [
      "Sop Penutupan Shift Kasir To",
      "Real-Time Cloud Sync",
      "Zero Em-Dash Verified",
      "Standar Apple Bento Grid"
    ],
    "gradientAccent": "rgba(37, 99, 235, 0.45)",
    "accentColor": "#38BDF8"
  },
  {
    "slug": "malapetaka-buta-stok-software-inventori-multi-gudang",
    "category": "Manajemen Inventori",
    "title": "Kematian Perlahan Toko Cabang",
    "subtitle": "Malapetaka Buta Stok dan Solusi Software Inventori Multi Gudang",
    "icon3d": "3d_shopping_cart.png",
    "statBadge": "COOCA BOS",
    "statSub": "SOFTWARE INVENTORI MULTI",
    "kicker": "INVESTIGASI BISNIS UMKM",
    "features": [
      "Software Inventori Multi Gud",
      "Real-Time Cloud Sync",
      "Zero Em-Dash Verified",
      "Standar Apple Bento Grid"
    ],
    "gradientAccent": "rgba(37, 99, 235, 0.45)",
    "accentColor": "#38BDF8"
  },
  {
    "slug": "jebakan-resep-tekor-bill-of-materials-resep-kuliner-umkm",
    "category": "Manajemen Inventori",
    "title": "Restoran Ramai Tapi Pemilik Tekor",
    "subtitle": "Jebakan Resep Asal-asalan Tanpa Bill of Materials Resep Kuliner UMKM",
    "icon3d": "3d_target.png",
    "statBadge": "COOCA BOS",
    "statSub": "BILL OF MATERIALS RESEP ",
    "kicker": "INVESTIGASI BISNIS UMKM",
    "features": [
      "Bill Of Materials Resep Kuli",
      "Real-Time Cloud Sync",
      "Zero Em-Dash Verified",
      "Standar Apple Bento Grid"
    ],
    "gradientAccent": "rgba(37, 99, 235, 0.45)",
    "accentColor": "#38BDF8"
  },
  {
    "slug": "audit-manual-konyol-aplikasi-stock-opname-digital-barcode",
    "category": "Manajemen Inventori",
    "title": "Audit Manual Itu Konyol",
    "subtitle": "Hentikan Tutup Toko Berhari-hari dengan Aplikasi Stock Opname Digital Barcode",
    "icon3d": "3d_gold_star.png",
    "statBadge": "COOCA BOS",
    "statSub": "APLIKASI STOCK OPNAME DI",
    "kicker": "INVESTIGASI BISNIS UMKM",
    "features": [
      "Aplikasi Stock Opname Digita",
      "Real-Time Cloud Sync",
      "Zero Em-Dash Verified",
      "Standar Apple Bento Grid"
    ],
    "gradientAccent": "rgba(37, 99, 235, 0.45)",
    "accentColor": "#38BDF8"
  },
  {
    "slug": "gudang-kuburan-modal-metode-fefo-pelacakan-batch-gudang",
    "category": "Manajemen Inventori",
    "title": "Gudang Jadi Kuburan Modal",
    "subtitle": "Mengapa Metode FEFO Pelacakan Batch Gudang Haram Diabaikan Pebisnis",
    "icon3d": "3d_pie_chart.png",
    "statBadge": "COOCA BOS",
    "statSub": "METODE FEFO PELACAKAN BA",
    "kicker": "INVESTIGASI BISNIS UMKM",
    "features": [
      "Metode Fefo Pelacakan Batch ",
      "Real-Time Cloud Sync",
      "Zero Em-Dash Verified",
      "Standar Apple Bento Grid"
    ],
    "gradientAccent": "rgba(37, 99, 235, 0.45)",
    "accentColor": "#38BDF8"
  },
  {
    "slug": "order-lewat-chat-bahaya-aplikasi-purchase-order-supplier-umkm",
    "category": "Pengadaan & Pemasok",
    "title": "Beli Barang Lewat Chat WA Itu Bunuh Diri",
    "subtitle": "Disiplinkan Pengadaan Lewat Aplikasi Purchase Order Supplier UMKM",
    "icon3d": "3d_bank_building.png",
    "statBadge": "COOCA BOS",
    "statSub": "APLIKASI PURCHASE ORDER ",
    "kicker": "INVESTIGASI BISNIS UMKM",
    "features": [
      "Aplikasi Purchase Order Supp",
      "Real-Time Cloud Sync",
      "Zero Em-Dash Verified",
      "Standar Apple Bento Grid"
    ],
    "gradientAccent": "rgba(37, 99, 235, 0.45)",
    "accentColor": "#38BDF8"
  },
  {
    "slug": "gudang-dibobol-sop-penerimaan-barang-gudang-supplier",
    "category": "Pengadaan & Pemasok",
    "title": "Gudang Anda Dibobol Pemasok",
    "subtitle": "Ketatnya SOP Penerimaan Barang Gudang Supplier Sebelum Tanda Tangan",
    "icon3d": "3d_shopping_bag.png",
    "statBadge": "COOCA BOS",
    "statSub": "SOP PENERIMAAN BARANG GU",
    "kicker": "INVESTIGASI BISNIS UMKM",
    "features": [
      "Sop Penerimaan Barang Gudang",
      "Real-Time Cloud Sync",
      "Zero Em-Dash Verified",
      "Standar Apple Bento Grid"
    ],
    "gradientAccent": "rgba(37, 99, 235, 0.45)",
    "accentColor": "#38BDF8"
  },
  {
    "slug": "modal-nyangkut-manajemen-retur-pembelian-barang-rusak",
    "category": "Pengadaan & Pemasok",
    "title": "Modal Nyangkut di Barang Cacat",
    "subtitle": "Rahasia Eksekusi Manajemen Retur Pembelian Barang Rusak Tanpa Ribut",
    "icon3d": "3d_shop_store.png",
    "statBadge": "COOCA BOS",
    "statSub": "MANAJEMEN RETUR PEMBELIA",
    "kicker": "INVESTIGASI BISNIS UMKM",
    "features": [
      "Manajemen Retur Pembelian Ba",
      "Real-Time Cloud Sync",
      "Zero Em-Dash Verified",
      "Standar Apple Bento Grid"
    ],
    "gradientAccent": "rgba(37, 99, 235, 0.45)",
    "accentColor": "#38BDF8"
  },
  {
    "slug": "tolak-pembeli-dosa-rumus-reorder-point-safety-stock-otomatis",
    "category": "Pengadaan & Pemasok",
    "title": "Tolak Pembeli Karena Stok Habis Itu Dosa",
    "subtitle": "Terapkan Rumus Reorder Point Safety Stock Otomatis Sekarang",
    "icon3d": "3d_rocket.png",
    "statBadge": "COOCA BOS",
    "statSub": "RUMUS REORDER POINT SAFE",
    "kicker": "INVESTIGASI BISNIS UMKM",
    "features": [
      "Rumus Reorder Point Safety S",
      "Real-Time Cloud Sync",
      "Zero Em-Dash Verified",
      "Standar Apple Bento Grid"
    ],
    "gradientAccent": "rgba(37, 99, 235, 0.45)",
    "accentColor": "#38BDF8"
  },
  {
    "slug": "gagal-deal-karena-pdf-aplikasi-surat-penawaran-harga-b2b",
    "category": "Penjualan B2B",
    "title": "Gagal Deal Milyaran Karena PDF Berantakan",
    "subtitle": "Rahasia Cepat Aplikasi Surat Penawaran Harga B2B Profesional",
    "icon3d": "3d_growth_arrow.png",
    "statBadge": "COOCA BOS",
    "statSub": "APLIKASI SURAT PENAWARAN",
    "kicker": "INVESTIGASI BISNIS UMKM",
    "features": [
      "Aplikasi Surat Penawaran Har",
      "Real-Time Cloud Sync",
      "Zero Em-Dash Verified",
      "Standar Apple Bento Grid"
    ],
    "gradientAccent": "rgba(37, 99, 235, 0.45)",
    "accentColor": "#38BDF8"
  },
  {
    "slug": "grosir-bangkrut-piutang-manajemen-sales-order-b2b-grosir",
    "category": "Penjualan B2B",
    "title": "Toko Grosir Bangkrut Digantung Langganan",
    "subtitle": "Pasang Rem Lewat Manajemen Sales Order B2B Grosir Terpadu",
    "icon3d": "3d_trophy.png",
    "statBadge": "COOCA BOS",
    "statSub": "MANAJEMEN SALES ORDER B2",
    "kicker": "INVESTIGASI BISNIS UMKM",
    "features": [
      "Manajemen Sales Order B2B Gr",
      "Real-Time Cloud Sync",
      "Zero Em-Dash Verified",
      "Standar Apple Bento Grid"
    ],
    "gradientAccent": "rgba(37, 99, 235, 0.45)",
    "accentColor": "#38BDF8"
  },
  {
    "slug": "amankan-distribusi-cetak-surat-jalan-pengiriman-barang-online",
    "category": "Penjualan B2B",
    "title": "Sopir Ekspedisi Dituduh Maling",
    "subtitle": "Amankan Pengiriman dengan Cetak Surat Jalan Pengiriman Barang Online Sah",
    "icon3d": "3d_shopping_cart.png",
    "statBadge": "COOCA BOS",
    "statSub": "CETAK SURAT JALAN PENGIR",
    "kicker": "INVESTIGASI BISNIS UMKM",
    "features": [
      "Cetak Surat Jalan Pengiriman",
      "Real-Time Cloud Sync",
      "Zero Em-Dash Verified",
      "Standar Apple Bento Grid"
    ],
    "gradientAccent": "rgba(37, 99, 235, 0.45)",
    "accentColor": "#38BDF8"
  },
  {
    "slug": "omzet-bon-kosong-laporan-aging-schedule-piutang-usaha-b2b",
    "category": "Penjualan B2B",
    "title": "Omzet Besar Berakhir Bon Kosong",
    "subtitle": "Cara Menyelamatkan Uang dengan Laporan Aging Schedule Piutang Usaha B2B",
    "icon3d": "3d_target.png",
    "statBadge": "COOCA BOS",
    "statSub": "LAPORAN AGING SCHEDULE P",
    "kicker": "INVESTIGASI BISNIS UMKM",
    "features": [
      "Laporan Aging Schedule Piuta",
      "Real-Time Cloud Sync",
      "Zero Em-Dash Verified",
      "Standar Apple Bento Grid"
    ],
    "gradientAccent": "rgba(37, 99, 235, 0.45)",
    "accentColor": "#38BDF8"
  },
  {
    "slug": "uang-menguap-software-laporan-laba-rugi-otomatis-umkm",
    "category": "Keuangan & Akuntansi",
    "title": "Mati-matian Jualan Tapi Uang Menguap",
    "subtitle": "Mengapa Software Laporan Laba Rugi Otomatis UMKM Wajib Terpasang",
    "icon3d": "3d_gold_star.png",
    "statBadge": "COOCA BOS",
    "statSub": "SOFTWARE LAPORAN LABA RU",
    "kicker": "INVESTIGASI BISNIS UMKM",
    "features": [
      "Software Laporan Laba Rugi O",
      "Real-Time Cloud Sync",
      "Zero Em-Dash Verified",
      "Standar Apple Bento Grid"
    ],
    "gradientAccent": "rgba(37, 99, 235, 0.45)",
    "accentColor": "#38BDF8"
  },
  {
    "slug": "ditolak-kredit-bank-neraca-keuangan-sak-emkm-otomatis",
    "category": "Keuangan & Akuntansi",
    "title": "Ditolak Mentah-mentah Oleh Bank",
    "subtitle": "Rahasia Lolos Pinjaman Modal Lewat Neraca Keuangan SAK EMKM Otomatis",
    "icon3d": "3d_pie_chart.png",
    "statBadge": "COOCA BOS",
    "statSub": "NERACA KEUANGAN SAK EMKM",
    "kicker": "INVESTIGASI BISNIS UMKM",
    "features": [
      "Neraca Keuangan Sak Emkm Oto",
      "Real-Time Cloud Sync",
      "Zero Em-Dash Verified",
      "Standar Apple Bento Grid"
    ],
    "gradientAccent": "rgba(37, 99, 235, 0.45)",
    "accentColor": "#38BDF8"
  },
  {
    "slug": "rekening-kering-aplikasi-arus-kas-dan-rekonsiliasi-bank",
    "category": "Keuangan & Akuntansi",
    "title": "Catatan Kasir Untung Tapi Rekening Kering",
    "subtitle": "Selamatkan Saldo Lewat Aplikasi Arus Kas dan Rekonsiliasi Bank",
    "icon3d": "3d_bank_building.png",
    "statBadge": "COOCA BOS",
    "statSub": "APLIKASI ARUS KAS DAN RE",
    "kicker": "INVESTIGASI BISNIS UMKM",
    "features": [
      "Aplikasi Arus Kas Dan Rekons",
      "Real-Time Cloud Sync",
      "Zero Em-Dash Verified",
      "Standar Apple Bento Grid"
    ],
    "gradientAccent": "rgba(37, 99, 235, 0.45)",
    "accentColor": "#38BDF8"
  },
  {
    "slug": "rayap-finansial-aplikasi-kas-kecil-petty-cash-kantor-toko",
    "category": "Keuangan & Akuntansi",
    "title": "Rayap Finansial Gerogoti Bisnis",
    "subtitle": "Hentikan Bon Siluman Lewat Aplikasi Kas Kecil Petty Cash Kantor Toko",
    "icon3d": "3d_shopping_bag.png",
    "statBadge": "COOCA BOS",
    "statSub": "APLIKASI KAS KECIL PETTY",
    "kicker": "INVESTIGASI BISNIS UMKM",
    "features": [
      "Aplikasi Kas Kecil Petty Cas",
      "Real-Time Cloud Sync",
      "Zero Em-Dash Verified",
      "Standar Apple Bento Grid"
    ],
    "gradientAccent": "rgba(37, 99, 235, 0.45)",
    "accentColor": "#38BDF8"
  },
  {
    "slug": "klien-kabur-software-invoice-penjualan-tempo-b2b",
    "category": "Invoicing & Billing",
    "title": "Klien Menghilang Saat Ditagih",
    "subtitle": "Rahasia Mengunci Pembayaran Lewat Software Invoice Penjualan Tempo B2B",
    "icon3d": "3d_shop_store.png",
    "statBadge": "COOCA BOS",
    "statSub": "SOFTWARE INVOICE PENJUAL",
    "kicker": "INVESTIGASI BISNIS UMKM",
    "features": [
      "Software Invoice Penjualan T",
      "Real-Time Cloud Sync",
      "Zero Em-Dash Verified",
      "Standar Apple Bento Grid"
    ],
    "gradientAccent": "rgba(37, 99, 235, 0.45)",
    "accentColor": "#38BDF8"
  },
  {
    "slug": "email-masuk-spam-aplikasi-invoice-wa-kirim-tagihan-otomatis",
    "category": "Invoicing & Billing",
    "title": "Email Tagihan Anda Cuma Masuk Spam",
    "subtitle": "Tagih Piutang Kilat Pakai Aplikasi Invoice WA Kirim Tagihan Otomatis",
    "icon3d": "3d_rocket.png",
    "statBadge": "COOCA BOS",
    "statSub": "APLIKASI INVOICE WA KIRI",
    "kicker": "INVESTIGASI BISNIS UMKM",
    "features": [
      "Aplikasi Invoice Wa Kirim Ta",
      "Real-Time Cloud Sync",
      "Zero Em-Dash Verified",
      "Standar Apple Bento Grid"
    ],
    "gradientAccent": "rgba(37, 99, 235, 0.45)",
    "accentColor": "#38BDF8"
  },
  {
    "slug": "jebakan-struk-palsu-qris-dinamis-rekonsiliasi-otomatis-kasir",
    "category": "Invoicing & Billing",
    "title": "Jebakan Screenshot Struk Palsu Kasir",
    "subtitle": "Lindungi Meja Bayar Lewat QRIS Dinamis Rekonsiliasi Otomatis Kasir",
    "icon3d": "3d_growth_arrow.png",
    "statBadge": "COOCA BOS",
    "statSub": "QRIS DINAMIS REKONSILIAS",
    "kicker": "INVESTIGASI BISNIS UMKM",
    "features": [
      "Qris Dinamis Rekonsiliasi Ot",
      "Real-Time Cloud Sync",
      "Zero Em-Dash Verified",
      "Standar Apple Bento Grid"
    ],
    "gradientAccent": "rgba(37, 99, 235, 0.45)",
    "accentColor": "#38BDF8"
  },
  {
    "slug": "hindari-denda-pajak-perhitungan-pph-final-umkm-otomatis",
    "category": "Perpajakan UMKM",
    "title": "Surat Cinta Kantor Pajak Datang",
    "subtitle": "Hindari Denda Berlipat dengan Perhitungan PPh Final UMKM Otomatis Sah",
    "icon3d": "3d_trophy.png",
    "statBadge": "COOCA BOS",
    "statSub": "PERHITUNGAN PPH FINAL UM",
    "kicker": "INVESTIGASI BISNIS UMKM",
    "features": [
      "Perhitungan Pph Final Umkm O",
      "Real-Time Cloud Sync",
      "Zero Em-Dash Verified",
      "Standar Apple Bento Grid"
    ],
    "gradientAccent": "rgba(37, 99, 235, 0.45)",
    "accentColor": "#38BDF8"
  },
  {
    "slug": "nomor-terblokir-whatsapp-gateway-resmi-sistem-kasir-umkm",
    "category": "WhatsApp & Komunikasi",
    "title": "Nomor Toko Diblokir Massal",
    "subtitle": "Mengapa Wajib Beralih ke WhatsApp Gateway Resmi Sistem Kasir UMKM",
    "icon3d": "3d_shopping_cart.png",
    "statBadge": "COOCA BOS",
    "statSub": "WHATSAPP GATEWAY RESMI S",
    "kicker": "INVESTIGASI BISNIS UMKM",
    "features": [
      "Whatsapp Gateway Resmi Siste",
      "Real-Time Cloud Sync",
      "Zero Em-Dash Verified",
      "Standar Apple Bento Grid"
    ],
    "gradientAccent": "rgba(37, 99, 235, 0.45)",
    "accentColor": "#38BDF8"
  },
  {
    "slug": "buang-uang-kertas-struk-kasir-digital-wa-tanpa-kertas-thermal",
    "category": "WhatsApp & Komunikasi",
    "title": "Buang Uang Juta Rupiah ke Kertas Sampah",
    "subtitle": "Beralih ke Struk Kasir Digital WA Tanpa Kertas Thermal Sekarang",
    "icon3d": "3d_target.png",
    "statBadge": "COOCA BOS",
    "statSub": "STRUK KASIR DIGITAL WA T",
    "kicker": "INVESTIGASI BISNIS UMKM",
    "features": [
      "Struk Kasir Digital Wa Tanpa",
      "Real-Time Cloud Sync",
      "Zero Em-Dash Verified",
      "Standar Apple Bento Grid"
    ],
    "gradientAccent": "rgba(37, 99, 235, 0.45)",
    "accentColor": "#38BDF8"
  },
  {
    "slug": "admin-diteror-chat-notifikasi-pesanan-otomatis-via-whatsapp",
    "category": "WhatsApp & Komunikasi",
    "title": "Admin Toko Diteror Chat Pelanggan",
    "subtitle": "Matikan Kepanikan Pembeli Lewat Notifikasi Pesanan Otomatis via WhatsApp",
    "icon3d": "3d_gold_star.png",
    "statBadge": "COOCA BOS",
    "statSub": "NOTIFIKASI PESANAN OTOMA",
    "kicker": "INVESTIGASI BISNIS UMKM",
    "features": [
      "Notifikasi Pesanan Otomatis ",
      "Real-Time Cloud Sync",
      "Zero Em-Dash Verified",
      "Standar Apple Bento Grid"
    ],
    "gradientAccent": "rgba(37, 99, 235, 0.45)",
    "accentColor": "#38BDF8"
  },
  {
    "slug": "promo-diblokir-broadcast-whatsapp-resmi-anti-banned-toko",
    "category": "WhatsApp & Komunikasi",
    "title": "Promo Disebut Spam dan Diblokir",
    "subtitle": "Rahasia Ledakkan Penjualan Lewat Broadcast WhatsApp Resmi Anti Banned Toko",
    "icon3d": "3d_pie_chart.png",
    "statBadge": "COOCA BOS",
    "statSub": "BROADCAST WHATSAPP RESMI",
    "kicker": "INVESTIGASI BISNIS UMKM",
    "features": [
      "Broadcast Whatsapp Resmi Ant",
      "Real-Time Cloud Sync",
      "Zero Em-Dash Verified",
      "Standar Apple Bento Grid"
    ],
    "gradientAccent": "rgba(37, 99, 235, 0.45)",
    "accentColor": "#38BDF8"
  },
  {
    "slug": "pelanggan-hilang-sistem-poin-loyalitas-pelanggan-toko-ritel",
    "category": "CRM & Loyalitas",
    "title": "Pelanggan Cuma Beli Sekali Lalu Hilang",
    "subtitle": "Kunci Repeat Order Pakai Sistem Poin Loyalitas Pelanggan Toko Ritel",
    "icon3d": "3d_bank_building.png",
    "statBadge": "COOCA BOS",
    "statSub": "SISTEM POIN LOYALITAS PE",
    "kicker": "INVESTIGASI BISNIS UMKM",
    "features": [
      "Sistem Poin Loyalitas Pelang",
      "Real-Time Cloud Sync",
      "Zero Em-Dash Verified",
      "Standar Apple Bento Grid"
    ],
    "gradientAccent": "rgba(37, 99, 235, 0.45)",
    "accentColor": "#38BDF8"
  },
  {
    "slug": "promosi-buta-segmentasi-pelanggan-rfm-usaha-ritel",
    "category": "CRM & Loyalitas",
    "title": "Semua Pembeli Dianggap Sama",
    "subtitle": "Bahaya Promosi Buta dan Kekuatan Segmentasi Pelanggan RFM Usaha Ritel",
    "icon3d": "3d_shopping_bag.png",
    "statBadge": "COOCA BOS",
    "statSub": "SEGMENTASI PELANGGAN RFM",
    "kicker": "INVESTIGASI BISNIS UMKM",
    "features": [
      "Segmentasi Pelanggan Rfm Usa",
      "Real-Time Cloud Sync",
      "Zero Em-Dash Verified",
      "Standar Apple Bento Grid"
    ],
    "gradientAccent": "rgba(37, 99, 235, 0.45)",
    "accentColor": "#38BDF8"
  },
  {
    "slug": "buta-selera-database-riwayat-transaksi-pelanggan-crm",
    "category": "CRM & Loyalitas",
    "title": "Buta Selera Konsumen Bikin Boncos",
    "subtitle": "Bongkar Rahasia Penjualan Lewat Database Riwayat Transaksi Pelanggan CRM",
    "icon3d": "3d_shop_store.png",
    "statBadge": "COOCA BOS",
    "statSub": "DATABASE RIWAYAT TRANSAK",
    "kicker": "INVESTIGASI BISNIS UMKM",
    "features": [
      "Database Riwayat Transaksi P",
      "Real-Time Cloud Sync",
      "Zero Em-Dash Verified",
      "Standar Apple Bento Grid"
    ],
    "gradientAccent": "rgba(37, 99, 235, 0.45)",
    "accentColor": "#38BDF8"
  },
  {
    "slug": "sentuh-hati-program-promo-diskon-ulang-tahun-member-crm",
    "category": "CRM & Loyalitas",
    "title": "Bisnis Kaku Ditinggal Konsumen",
    "subtitle": "Sentuh Hati Pelanggan Lewat Program Promo Diskon Ulang Tahun Member CRM",
    "icon3d": "3d_rocket.png",
    "statBadge": "COOCA BOS",
    "statSub": "PROGRAM PROMO DISKON ULA",
    "kicker": "INVESTIGASI BISNIS UMKM",
    "features": [
      "Program Promo Diskon Ulang T",
      "Real-Time Cloud Sync",
      "Zero Em-Dash Verified",
      "Standar Apple Bento Grid"
    ],
    "gradientAccent": "rgba(37, 99, 235, 0.45)",
    "accentColor": "#38BDF8"
  },
  {
    "slug": "skandal-absen-palsu-aplikasi-absensi-gps-geofencing-karyawan-toko",
    "category": "SDM & Penggajian",
    "title": "Gaji Dibayar Penuh Staf Titip Absen",
    "subtitle": "Skandal Kehadiran Palsu Dibasmi Aplikasi Absensi GPS Geofencing Karyawan Toko",
    "icon3d": "3d_growth_arrow.png",
    "statBadge": "COOCA BOS",
    "statSub": "APLIKASI ABSENSI GPS GEO",
    "kicker": "INVESTIGASI BISNIS UMKM",
    "features": [
      "Aplikasi Absensi Gps Geofenc",
      "Real-Time Cloud Sync",
      "Zero Em-Dash Verified",
      "Standar Apple Bento Grid"
    ],
    "gradientAccent": "rgba(37, 99, 235, 0.45)",
    "accentColor": "#38BDF8"
  },
  {
    "slug": "jadwal-bentrok-aplikasi-jadwal-shift-kerja-karyawan-kafe",
    "category": "SDM & Penggajian",
    "title": "Kekacauan Jam Sibuk Tanpa Pramusaji",
    "subtitle": "Bereskan Jadwal Bentrok Lewat Aplikasi Jadwal Shift Kerja Karyawan Kafe",
    "icon3d": "3d_trophy.png",
    "statBadge": "COOCA BOS",
    "statSub": "APLIKASI JADWAL SHIFT KE",
    "kicker": "INVESTIGASI BISNIS UMKM",
    "features": [
      "Aplikasi Jadwal Shift Kerja ",
      "Real-Time Cloud Sync",
      "Zero Em-Dash Verified",
      "Standar Apple Bento Grid"
    ],
    "gradientAccent": "rgba(37, 99, 235, 0.45)",
    "accentColor": "#38BDF8"
  },
  {
    "slug": "drama-salah-gaji-aplikasi-payroll-slip-gaji-digital-umkm",
    "category": "SDM & Penggajian",
    "title": "Karyawan Demo Salah Hitung Lembur",
    "subtitle": "Hentikan Drama Gaji Manual Pakai Aplikasi Payroll Slip Gaji Digital UMKM",
    "icon3d": "3d_shopping_cart.png",
    "statBadge": "COOCA BOS",
    "statSub": "APLIKASI PAYROLL SLIP GA",
    "kicker": "INVESTIGASI BISNIS UMKM",
    "features": [
      "Aplikasi Payroll Slip Gaji D",
      "Real-Time Cloud Sync",
      "Zero Em-Dash Verified",
      "Standar Apple Bento Grid"
    ],
    "gradientAccent": "rgba(37, 99, 235, 0.45)",
    "accentColor": "#38BDF8"
  },
  {
    "slug": "pemilik-jadi-rentenir-manajemen-kasbon-potong-gaji-karyawan",
    "category": "SDM & Penggajian",
    "title": "Pemilik Usaha Jadi Rentenir Sukarela",
    "subtitle": "Disiplinkan Utang Staf Lewat Manajemen Kasbon Potong Gaji Karyawan",
    "icon3d": "3d_target.png",
    "statBadge": "COOCA BOS",
    "statSub": "MANAJEMEN KASBON POTONG ",
    "kicker": "INVESTIGASI BISNIS UMKM",
    "features": [
      "Manajemen Kasbon Potong Gaji",
      "Real-Time Cloud Sync",
      "Zero Em-Dash Verified",
      "Standar Apple Bento Grid"
    ],
    "gradientAccent": "rgba(37, 99, 235, 0.45)",
    "accentColor": "#38BDF8"
  },
  {
    "slug": "tercekik-komisi-katalog-online-toko-sendiri-tanpa-potongan-komisi",
    "category": "Omnichannel & Storefront",
    "title": "Diperas Biaya Admin Marketplace 15%",
    "subtitle": "Merdekakan Margin dengan Katalog Online Toko Sendiri Tanpa Potongan Komisi",
    "icon3d": "3d_gold_star.png",
    "statBadge": "COOCA BOS",
    "statSub": "KATALOG ONLINE TOKO SEND",
    "kicker": "INVESTIGASI BISNIS UMKM",
    "features": [
      "Katalog Online Toko Sendiri ",
      "Real-Time Cloud Sync",
      "Zero Em-Dash Verified",
      "Standar Apple Bento Grid"
    ],
    "gradientAccent": "rgba(37, 99, 235, 0.45)",
    "accentColor": "#38BDF8"
  },
  {
    "slug": "pelanggan-kabur-antre-aplikasi-menu-qr-code-pesan-di-meja-kafe",
    "category": "Omnichannel & Storefront",
    "title": "Pelanggan Kabur Karena Antre Berdiri",
    "subtitle": "Revolusi Layanan Meja Pakai Aplikasi Menu QR Code Pesan di Meja Kafe",
    "icon3d": "3d_pie_chart.png",
    "statBadge": "COOCA BOS",
    "statSub": "APLIKASI MENU QR CODE PE",
    "kicker": "INVESTIGASI BISNIS UMKM",
    "features": [
      "Aplikasi Menu Qr Code Pesan ",
      "Real-Time Cloud Sync",
      "Zero Em-Dash Verified",
      "Standar Apple Bento Grid"
    ],
    "gradientAccent": "rgba(37, 99, 235, 0.45)",
    "accentColor": "#38BDF8"
  },
  {
    "slug": "toko-dibekukan-sinkronisasi-stok-marketplace-shopee-tokopedia",
    "category": "Omnichannel & Storefront",
    "title": "Toko Dibekukan Karena Batalkan Pesanan",
    "subtitle": "Selamatkan Rating Toko Lewat Sinkronisasi Stok Marketplace Shopee Tokopedia",
    "icon3d": "3d_bank_building.png",
    "statBadge": "COOCA BOS",
    "statSub": "SINKRONISASI STOK MARKET",
    "kicker": "INVESTIGASI BISNIS UMKM",
    "features": [
      "Sinkronisasi Stok Marketplac",
      "Real-Time Cloud Sync",
      "Zero Em-Dash Verified",
      "Standar Apple Bento Grid"
    ],
    "gradientAccent": "rgba(37, 99, 235, 0.45)",
    "accentColor": "#38BDF8"
  },
  {
    "slug": "kunci-pengkhianat-hak-akses-rbac-kasir-supervisor-cegah-fraud",
    "category": "Keamanan & Akses",
    "title": "Uang Ditilep Data Bisnis Dibocorkan",
    "subtitle": "Kunci Pintu Pengkhianatan Pakai Hak Akses RBAC Kasir Supervisor Cegah Fraud",
    "icon3d": "3d_shopping_bag.png",
    "statBadge": "COOCA BOS",
    "statSub": "HAK AKSES RBAC KASIR SUP",
    "kicker": "INVESTIGASI BISNIS UMKM",
    "features": [
      "Hak Akses Rbac Kasir Supervi",
      "Real-Time Cloud Sync",
      "Zero Em-Dash Verified",
      "Standar Apple Bento Grid"
    ],
    "gradientAccent": "rgba(37, 99, 235, 0.45)",
    "accentColor": "#38BDF8"
  }
];

async function generateAllClickbaitCovers() {
  console.log(`[Clickbait Bento Engine] Starting generation of ${CARDS.length} custom editorial covers (1200x675)...`);
  
  if (!fs.existsSync(OUTPUT_DIR)) {
    fs.mkdirSync(OUTPUT_DIR, { recursive: true });
  }

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
<meta charset="UTF-8">
<style>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@600;700&display=swap');

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  width: 1200px;
  height: 675px;
  background-color: #070B19;
  font-family: 'Plus Jakarta Sans', sans-serif;
  color: #FFFFFF;
  position: relative;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

/* Ambient Background Lights */
body::before {
  content: '';
  position: absolute;
  top: -150px;
  right: -100px;
  width: 650px;
  height: 650px;
  background: radial-gradient(circle, ${card.gradientAccent} 0%, rgba(37, 99, 235, 0) 70%);
  filter: blur(60px);
  z-index: 1;
}

body::after {
  content: '';
  position: absolute;
  bottom: -150px;
  left: -100px;
  width: 550px;
  height: 550px;
  background: radial-gradient(circle, rgba(56, 189, 248, 0.25) 0%, rgba(7, 11, 25, 0) 70%);
  filter: blur(80px);
  z-index: 1;
}

/* Fine Grid Pattern */
.grid-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-image: 
    linear-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
  background-size: 40px 40px;
  z-index: 2;
  pointer-events: none;
}

/* Main Content Wrapper */
.content {
  position: relative;
  z-index: 3;
  width: 100%;
  height: 100%;
  padding: 48px 56px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

/* Header */
.header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.brand-wrapper {
  display: flex;
  align-items: center;
  gap: 16px;
}

.brand-logo {
  height: 44px;
  width: auto;
  object-fit: contain;
  filter: drop-shadow(0 4px 12px rgba(0, 0, 0, 0.4));
}

.platform-pill {
  display: flex;
  align-items: center;
  gap: 8px;
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.12);
  backdrop-filter: blur(12px);
  padding: 8px 16px;
  border-radius: 9999px;
  font-size: 13px;
  font-weight: 600;
  letter-spacing: 0.04em;
  color: #E2E8F0;
}

.status-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background-color: #10B981;
  box-shadow: 0 0 10px #10B981;
}

/* Main Hero Stage */
.main-stage {
  display: grid;
  grid-template-columns: 1fr 340px;
  gap: 40px;
  align-items: center;
  margin-top: 10px;
}

.text-col {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.kicker {
  font-family: 'Space Grotesk', sans-serif;
  font-size: 13px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.16em;
  color: #38BDF8;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.kicker::before {
  content: '';
  display: inline-block;
  width: 20px;
  height: 2px;
  background: #38BDF8;
}

.main-title {
  font-size: 40px;
  font-weight: 800;
  line-height: 1.15;
  letter-spacing: -0.025em;
  color: #FFFFFF;
  text-shadow: 0 2px 20px rgba(0, 0, 0, 0.6);
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.subtitle {
  font-size: 17px;
  font-weight: 500;
  line-height: 1.45;
  color: #94A3B8;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  max-width: 680px;
}

/* Features Grid */
.features-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 10px 16px;
  margin-top: 6px;
  max-width: 660px;
}

.feat-item {
  display: flex;
  align-items: center;
  gap: 10px;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 10px;
  padding: 8px 14px;
  font-size: 13px;
  font-weight: 600;
  color: #E2E8F0;
}

.check-icon {
  color: #38BDF8;
  font-weight: 800;
}

/* Visual 3D Showcase Column */
.visual-col {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  position: relative;
}

.visual-3d-img {
  width: 220px;
  height: 220px;
  object-fit: contain;
  filter: drop-shadow(0 20px 35px rgba(0, 0, 0, 0.6));
  animation: float 4s ease-in-out infinite;
}

.stat-badge {
  margin-top: 10px;
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.14);
  backdrop-filter: blur(16px);
  padding: 8px 18px;
  border-radius: 12px;
  font-family: 'Space Grotesk', sans-serif;
  font-size: 20px;
  font-weight: 800;
  color: #FFFFFF;
  text-align: center;
  letter-spacing: -0.01em;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
}

.stat-sub {
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: ${card.accentColor};
  margin-top: 2px;
}

/* Footer */
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

    console.log(`[${i + 1}/${CARDS.length}] Rendered: ${outputFilename}`);
  }

  await browser.close();
  console.log(`\nAll ${CARDS.length} custom editorial covers successfully rendered into public/images/blog/!`);
}

generateAllClickbaitCovers().catch(err => {
  console.error('Fatal rendering error:', err);
  process.exit(1);
});
