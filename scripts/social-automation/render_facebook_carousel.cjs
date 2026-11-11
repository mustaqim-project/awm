const { chromium } = require('c:/laragon/www/cooca_core/node_modules/@playwright/test');
const fs = require('fs');
const path = require('path');

const ROOT_DIR = 'C:/laragon/www/cooca_core';
const LOGO_DARK_PATH = path.join(ROOT_DIR, 'public/assets/image/1785229034_logo_dark.png');
const PINTEREST_ASSETS_DIR = path.join(ROOT_DIR, 'public/admin/social-media-assets/pinterest-showcase');
const OUTPUT_DIR = path.join(ROOT_DIR, 'storage/app/public/admin/social-media-assets/carousel/facebook');
const ARTIFACT_DIR = path.join('C:/Users/Ulfah Ramadhani/.gemini/antigravity/brain/10bf7635-4a75-4bd6-8bf3-485feaf5d3f6');

fs.mkdirSync(OUTPUT_DIR, { recursive: true });
fs.mkdirSync(ARTIFACT_DIR, { recursive: true });

function getBase64Image(filePath) {
  if (!fs.existsSync(filePath)) {
    console.warn(`[Asset Warning] File not found: ${filePath}`);
    return '';
  }
  const ext = path.extname(filePath).replace('.', '') || 'png';
  const buf = fs.readFileSync(filePath);
  return `data:image/${ext};base64,${buf.toString('base64')}`;
}

const logoDarkBase64 = getBase64Image(LOGO_DARK_PATH);
const iconShopBase64 = getBase64Image(path.join(PINTEREST_ASSETS_DIR, '3d_shop_store.png'));
const iconTargetBase64 = getBase64Image(path.join(PINTEREST_ASSETS_DIR, '3d_target.png'));
const iconGrowthBase64 = getBase64Image(path.join(PINTEREST_ASSETS_DIR, '3d_growth_arrow.png'));
const iconPieBase64 = getBase64Image(path.join(PINTEREST_ASSETS_DIR, '3d_pie_chart.png'));
const iconTrophyBase64 = getBase64Image(path.join(PINTEREST_ASSETS_DIR, '3d_trophy.png'));

const slidesData = [
  {
    slideNumber: 1,
    tag: "STUDI KASUS RITEL",
    badge: "KASUS LAPANGAN",
    title: "Stok di Buku 50 Pcs, Tapi di Rak Cuma Ada 38 Pcs?",
    subtitle: "Ke mana selisih 12 pcs barang menguap tanpa jejak transaksi kasir?",
    iconBase64: iconShopBase64,
    highlightNumber: "-12 PCS",
    highlightLabel: "Selisih Stok Fisik vs Buku Catatan",
    highlightDesc: "Terdeteksi saat stok fisik dihitung langsung di rak display",
    points: [
      { num: "01", title: "Catatan di buku / sistem menunjukkan 50 pcs barang siap jual" },
      { num: "02", title: "Fisik nyata di rak display toko hanya tersisa 38 pcs" },
      { num: "03", title: "Potensi kerugian modal tersembunyi ratusan ribu rupiah per produk" },
      { num: "04", title: "Pembeli kecewa karena stok tertera ada tapi fisik barang kosong" }
    ],
    footerPill: "Geser ke Slide 2: Akar Masalah →"
  },
  {
    slideNumber: 2,
    tag: "ANALISIS PENYEBAB",
    badge: "3 TITIK RAWAN KEBOCORAN",
    title: "Akar Masalah: Mutasi Barang yang Tidak Terekam",
    subtitle: "Bukan takdir bisnis, selisih selalu berawal dari kelalaian proses harian:",
    iconBase64: iconTargetBase64,
    points: [
      {
        num: "01",
        label: "Kelalaian Meja Kasir",
        desc: "Barang terjual saat toko sedang ramai pembeli lupa di-scan kasir atau salah ketik barcode produk yang mirip."
      },
      {
        num: "02",
        label: "Salah Penempatan Rak Gudang",
        desc: "Barang terselip di sudut gudang tanpa standarisasi pengelompokan kode rak yang teratur."
      },
      {
        num: "03",
        label: "Penerimaan Pasokan Supplier",
        desc: "Barang masuk dari supplier diterima terburu-buru tanpa pencocokan Purchase Order dan nota digital."
      }
    ],
    footerPill: "Geser ke Slide 3: Dampak Finansial →"
  },
  {
    slideNumber: 3,
    tag: "RISIKO FINANSIAL",
    badge: "DAMPAK NYATA",
    title: "Dampak Fatal Terhadap Arus Kas & Reputasi Usaha",
    subtitle: "Jika selisih stok dibiarkan berulang setiap bulan tanpa tindakan korektif:",
    iconBase64: iconPieBase64,
    points: [
      {
        num: "01",
        label: "Modal Usaha Bocor Halus",
        desc: "Dana kas modal tertahan pada barang yang hilang atau rusak tanpa pertanggungjawaban yang jelas."
      },
      {
        num: "02",
        label: "Hilang Peluang Penjualan",
        desc: "Pelanggan beralih ke toko kompetitor akibat stok kosong semu (tercatat ada di sistem, kosong di rak)."
      },
      {
        num: "03",
        label: "Laporan Laba Rugi Menyesatkan",
        desc: "Pembukuan kas mencatat nilai aset barang yang sebenarnya sudah lenyap dari peredaran toko."
      }
    ],
    footerPill: "Geser ke Slide 4: Solusi SOP Barcode →"
  },
  {
    slideNumber: 4,
    tag: "LANGKAH MITIGASI",
    badge: "SOP OPERASIONAL TERUJI",
    title: "SOP Standarisasi: Wajib Scan Barcode Masuk & Keluar",
    subtitle: "Eliminasi human error dengan sistem barcode scanner otomatis terintegrasi:",
    iconBase64: iconGrowthBase64,
    points: [
      {
        num: "01",
        label: "Inbound Supplier Otomatis",
        desc: "Pindai kode barcode setiap kali barang datang, jumlah stok langsung bertambah akurat di sistem."
      },
      {
        num: "02",
        label: "Outbound Kasir Real-Time",
        desc: "Integrasi barcode scanner kasir seketika memotong kartu stok digital secara otomatis."
      },
      {
        num: "03",
        label: "Cycle Count Mingguan",
        desc: "Lakukan audit opname cepat 15 menit untuk 20 produk terlaris tanpa harus menutup toko."
      }
    ],
    footerPill: "Geser ke Slide 5: Otomasi COOCA →"
  },
  {
    slideNumber: 5,
    tag: "SOLUSI STRATEGIS",
    badge: "KENDALI BISNIS TERPADU",
    title: "Otomatisasi Stok Real-Time Bersama Ekosistem COOCA",
    subtitle: "Kendalikan mutasi stok toko Anda dengan akurasi 99% tanpa ribet:",
    iconBase64: iconTrophyBase64,
    points: [
      {
        num: "01",
        label: "Kartu Stok Digital Otomatis",
        desc: "Ter-update seketika setiap kali ada transaksi kasir, mutasi rak, dan retur barang masuk."
      },
      {
        num: "02",
        label: "Low-Stock Alert Cerdas",
        desc: "Notifikasi otomatis ke smartphone pemilik usaha sebelum stok produk benar-benar habis."
      },
      {
        num: "03",
        label: "Audit Barcode Cepat",
        desc: "Selisih fisik vs catatan terdeteksi dalam hitungan menit, bukan saat tutup buku bulanan."
      }
    ],
    ctaBadge: "Mulai Gratis Selamanya di cooca.id",
    footerPill: "Daftar Gratis di cooca.id →"
  }
];

function generateSlideHtml(slide) {
  return `<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<style>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

body {
  width: 1080px;
  height: 1080px;
  background-color: #050B1F;
  background-image: 
    radial-gradient(circle at 10% 10%, rgba(37, 99, 235, 0.22) 0%, transparent 45%),
    radial-gradient(circle at 90% 90%, rgba(56, 189, 248, 0.16) 0%, transparent 45%),
    linear-gradient(150deg, #050B1F 0%, #0A1433 45%, #162B68 100%);
  font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
  color: #FFFFFF;
  overflow: hidden;
  position: relative;
  padding: 48px 56px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

/* Subtle architectural grid */
body::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-size: 40px 40px;
  background-image: 
    linear-gradient(to right, rgba(255, 255, 255, 0.03) 1px, transparent 1px),
    linear-gradient(to bottom, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
  pointer-events: none;
}

/* Ambient glow orb */
.ambient-glow {
  position: absolute;
  top: 20%;
  right: -5%;
  width: 480px;
  height: 480px;
  background: radial-gradient(circle, rgba(56, 189, 248, 0.15) 0%, rgba(37, 99, 235, 0.05) 50%, transparent 70%);
  border-radius: 50%;
  filter: blur(60px);
  pointer-events: none;
}

/* Header */
.header {
  position: relative;
  z-index: 2;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 1px solid rgba(255, 255, 255, 0.12);
  padding-bottom: 20px;
}

.brand-wrapper {
  display: flex;
  align-items: center;
  gap: 16px;
}

.brand-logo-container {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 6px 14px;
  border-radius: 12px;
  background: transparent;
  
  
}

.brand-logo {
  height: 36px;
  width: auto;
  object-fit: contain;
}

.tag-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 18px;
  border-radius: 999px;
  background: rgba(37, 99, 235, 0.2);
  border: 1.5px solid rgba(56, 189, 248, 0.4);
  font-size: 13px;
  font-weight: 700;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  color: #38BDF8;
}

.slide-number-pill {
  font-size: 13px;
  font-weight: 800;
  color: #FFFFFF;
  background: rgba(37, 99, 235, 0.5);
  border: 1px solid rgba(56, 189, 248, 0.4);
  padding: 6px 16px;
  border-radius: 20px;
  letter-spacing: 0.05em;
}

/* Main Body Content */
.main-content {
  position: relative;
  z-index: 2;
  display: flex;
  flex-direction: column;
  gap: 18px;
  margin-top: 6px;
}

.title-row {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 24px;
}

.title-text-col {
  flex: 1;
}

.overline {
  font-size: 13px;
  font-weight: 800;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: #38BDF8;
  margin-bottom: 8px;
}

.headline {
  font-size: 38px;
  font-weight: 800;
  line-height: 1.25;
  color: #FFFFFF;
  letter-spacing: -0.02em;
  text-shadow: 0 2px 10px rgba(0, 0, 0, 0.4);
}

.subtitle {
  font-size: 18px;
  font-weight: 500;
  line-height: 1.5;
  color: #94A3B8;
  margin-top: 8px;
}

.corner-3d-icon {
  width: 90px;
  height: 90px;
  object-fit: contain;
  filter: drop-shadow(0 10px 20px rgba(0, 0, 0, 0.45));
  flex-shrink: 0;
}

/* Featured Stat Card (for Slide 1) */
.stat-highlight-box {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 18px 26px;
  border-radius: 18px;
  background: linear-gradient(135deg, rgba(239, 68, 68, 0.25) 0%, rgba(220, 38, 38, 0.4) 100%);
  border: 1.5px solid rgba(248, 113, 113, 0.5);
  box-shadow: 0 10px 30px rgba(239, 68, 68, 0.25);
  backdrop-filter: blur(12px);
  margin-top: 2px;
}

.stat-desc {
  font-size: 18px;
  font-weight: 700;
  color: #FFFFFF;
}

.stat-subdesc {
  font-size: 13px;
  color: #FECACA;
  margin-top: 4px;
  font-weight: 500;
}

.stat-number {
  font-size: 44px;
  font-weight: 800;
  letter-spacing: -0.02em;
  color: #FCA5A5;
  text-shadow: 0 0 20px rgba(239, 68, 68, 0.5);
}

/* Cards & Points Container */
.cards-container {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-top: 4px;
}

.point-card {
  display: flex;
  align-items: flex-start;
  gap: 16px;
  padding: 16px 20px;
  border-radius: 16px;
  background: rgba(15, 23, 42, 0.7);
  border: 1px solid rgba(255, 255, 255, 0.1);
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.25);
  backdrop-filter: blur(10px);
}

.point-icon {
  width: 32px;
  height: 32px;
  border-radius: 10px;
  background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%);
  color: #FFFFFF;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 13px;
  font-weight: 800;
  flex-shrink: 0;
  margin-top: 2px;
  box-shadow: 0 2px 8px rgba(37, 99, 235, 0.4);
}

.point-content {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.point-label {
  font-size: 16px;
  font-weight: 700;
  color: #38BDF8;
}

.point-text {
  font-size: 15px;
  font-weight: 500;
  line-height: 1.45;
  color: #E2E8F0;
}

/* CTA Hero Banner on Slide 5 */
.cta-banner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 24px;
  border-radius: 16px;
  background: linear-gradient(135deg, rgba(37, 99, 235, 0.35) 0%, rgba(56, 189, 248, 0.25) 100%);
  border: 1.5px solid rgba(56, 189, 248, 0.5);
  box-shadow: 0 8px 24px rgba(37, 99, 235, 0.3);
  margin-top: 4px;
}

.cta-banner-text {
  font-size: 15px;
  font-weight: 700;
  color: #FFFFFF;
}

.cta-banner-badge {
  padding: 8px 18px;
  border-radius: 999px;
  background: #38BDF8;
  color: #050B1F;
  font-size: 13px;
  font-weight: 800;
  letter-spacing: 0.04em;
  text-transform: uppercase;
}

/* Footer */
.footer {
  position: relative;
  z-index: 2;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-top: 1px solid rgba(255, 255, 255, 0.12);
  padding-top: 18px;
}

.footer-brand {
  font-size: 14px;
  font-weight: 700;
  color: #94A3B8;
  display: flex;
  align-items: center;
  gap: 8px;
}

.footer-brand span {
  color: #38BDF8;
}

.footer-pill {
  padding: 10px 24px;
  border-radius: 12px;
  background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%);
  border: 1px solid rgba(56, 189, 248, 0.5);
  color: #FFFFFF;
  font-size: 14px;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 8px;
  box-shadow: 0 4px 16px rgba(37, 99, 235, 0.4);
}

</style>
</head>
<body>

  <div class="ambient-glow"></div>

  <!-- Header -->
  <div class="header">
    <div class="brand-wrapper">
      <div class="brand-logo-container">
        <img class="brand-logo" src="${logoDarkBase64}" alt="COOCA Logo">
      </div>
      <div class="tag-badge">
        <span>●</span> ${slide.tag}
      </div>
    </div>
    <div class="slide-number-pill">
      SLIDE 0${slide.slideNumber} / 05
    </div>
  </div>

  <!-- Main Body Content -->
  <div class="main-content">
    <div class="title-row">
      <div class="title-text-col">
        <div class="overline">${slide.badge}</div>
        <div class="headline">${slide.title}</div>
        <div class="subtitle">${slide.subtitle}</div>
      </div>
      ${slide.iconBase64 ? `<img class="corner-3d-icon" src="${slide.iconBase64}" alt="Icon 3D">` : ''}
    </div>

    ${slide.highlightNumber ? `
    <div class="stat-highlight-box">
      <div>
        <div class="stat-desc">${slide.highlightLabel}</div>
        <div class="stat-subdesc">${slide.highlightDesc}</div>
      </div>
      <div class="stat-number">${slide.highlightNumber}</div>
    </div>
    ` : ''}

    <div class="cards-container">
      ${slide.points.map(pt => `
        <div class="point-card">
          <div class="point-icon">${pt.num}</div>
          <div class="point-content">
            ${pt.label ? `<div class="point-label">${pt.label}</div>` : ''}
            <div class="point-text">${pt.desc || pt.title}</div>
          </div>
        </div>
      `).join('')}
    </div>

    ${slide.ctaBadge ? `
    <div class="cta-banner">
      <div class="cta-banner-text">Mulai kendalikan stok dan operasional usaha tanpa biaya lisensi mahal:</div>
      <div class="cta-banner-badge">${slide.ctaBadge}</div>
    </div>
    ` : ''}
  </div>

  <!-- Footer -->
  <div class="footer">
    <div class="footer-brand">
      Sistem Operasional UMKM Modern • <span>cooca.id</span>
    </div>
    <div class="footer-pill">
      ${slide.footerPill}
    </div>
  </div>

</body>
</html>`;
}

async function renderSlides() {
  console.log('Launching Chromium for 5-slide Dark-Tech COOCA Facebook Carousel...');
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({
    viewport: { width: 1080, height: 1080 },
    deviceScaleFactor: 2
  });
  const page = await context.newPage();

  for (const slide of slidesData) {
    const html = generateSlideHtml(slide);
    await page.setContent(html, { waitUntil: 'networkidle' });

    const outName = `slide-0${slide.slideNumber}.png`;
    const targetPath = path.join(OUTPUT_DIR, outName);
    const artifactPath = path.join(ARTIFACT_DIR, `cooca-carousel-facebook-slide-0${slide.slideNumber}.png`);

    await page.screenshot({ path: targetPath, type: 'png' });
    fs.copyFileSync(targetPath, artifactPath);
    console.log(`[Slide ${slide.slideNumber}] Rendered successfully -> ${targetPath} (${fs.statSync(targetPath).size} bytes)`);
  }

  await browser.close();
  console.log('ALL 5 SLIDES GENERATED WITH DARK NAVY & WHITE IDENTITY AND LOGO_DARK.PNG!');
}

renderSlides().catch(err => {
  console.error('Rendering error:', err);
  process.exit(1);
});
