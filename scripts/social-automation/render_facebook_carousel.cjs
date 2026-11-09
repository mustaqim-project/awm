const { chromium } = require('c:/laragon/www/cooca_core/node_modules/@playwright/test');
const fs = require('fs');
const path = require('path');

const ROOT_DIR = 'C:/laragon/www/cooca_core';
const LOGO_DARK_PATH = path.join(ROOT_DIR, 'public/assets/image/1785229034_logo_dark.png');
const SPEC_PATH = path.join(ROOT_DIR, 'storage/app/public/content-assets/spec-cooca-carousel-facebook.json');
const OUTPUT_DIR = path.join(ROOT_DIR, 'storage/app/public/admin/social-media-assets/carousel/facebook');
const ARTIFACT_DIR = path.join('C:/Users/Ulfah Ramadhani/.gemini/antigravity/brain/6ea5ac5e-f251-4d66-af99-f1c9e937920e');

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

const slidesData = [
  {
    slideNumber: 1,
    tag: "STUDI KASUS RITEL",
    badge: "KASUS LAPANGAN",
    title: "Stok di Buku 50 Pcs, Tapi di Rak Cuma Ada 38 Pcs?",
    subtitle: "Ke mana selisih 12 pcs barang menguap tanpa jejak transaksi kasir?",
    highlightNumber: "12 PCS",
    highlightLabel: "Selisih Barang Fiktif",
    points: [
      "Catatan di buku / sistem menunjukkan 50 pcs siap jual",
      "Fisik nyata di rak display hanya tersisa 38 pcs",
      "Potensi kerugian modal tersembunyi ratusan ribu rupiah",
      "Pembeli kecewa karena stok tertera ada tapi barang kosong"
    ],
    footerPill: "Geser ke Slide 2: Akar Masalah →"
  },
  {
    slideNumber: 2,
    tag: "ANALISIS PENYEBAB",
    badge: "3 AKAR MASALAH",
    title: "Akar Masalah: Mutasi Barang yang Tidak Terekam",
    subtitle: "Bukan takdir, selisih selalu berawal dari kelalaian proses harian:",
    points: [
      "1. Kelalaian Kasir: Barang terjual saat toko ramai lupa di-scan atau salah input kode barcode mirip.",
      "2. Salah Letak Rak: Barang terselip di sudut gudang tanpa kode rak terstandarisasi.",
      "3. Penerimaan Supplier: Pasokan barang masuk diterima terburu-buru tanpa pencocokan PO sistem."
    ],
    footerPill: "Geser ke Slide 3: Dampak Finansial →"
  },
  {
    slideNumber: 3,
    tag: "RISIKO FINANSIAL",
    badge: "DAMPAK NYATA",
    title: "Dampak Fatal Terhadap Arus Kas & Reputasi Toko",
    subtitle: "Jika selisih stok dibiarkan berulang setiap bulan:",
    points: [
      "Modal Usaha Bocor: Uang tertanam pada barang yang hilang atau rusak tanpa pertanggungjawaban.",
      "Hilang Peluang Penjualan: Pembeli berpindah ke kompetitor akibat stok kosong semu (false stock).",
      "Laporan Laba Rugi Palsu: Pembukuan mencatat aset barang yang sebenarnya sudah lenyap."
    ],
    footerPill: "Geser ke Slide 4: Solusi SOP Barcode →"
  },
  {
    slideNumber: 4,
    tag: "LANGKAH MITIGASI",
    badge: "SOP TERUJI",
    title: "SOP Standarisasi: Wajib Scan Barcode Masuk & Keluar",
    subtitle: "Eliminasi human error dengan sistem barcode scanner otomatis:",
    points: [
      "Inbound Supplier: Pindai barcode setiap barang datang, otomatis masuk ke stok sistem.",
      "Outbound Kasir: Integrasi barcode scanner langsung memotong kartu stok real-time.",
      "Cycle Count Mingguan: Audit opname 15 menit untuk 20 produk terlaris tanpa tutup toko."
    ],
    footerPill: "Geser ke Slide 5: Otomasi COOCA →"
  },
  {
    slideNumber: 5,
    tag: "SOLUSI AKURAT",
    badge: "FITUR RESMI COOCA",
    title: "Otomatisasi Stok Real-Time COOCA (/inventory/stocks)",
    subtitle: "Kendalikan stok toko Anda dengan akurasi 99% tanpa ribet:",
    points: [
      "Kartu Stok Digital Otomatis: Ter-update setiap kali ada transaksi kasir dan mutasi gudang.",
      "Low-Stock Alert Cerdas: Notifikasi otomatis ke ponsel sebelum barang benar-benar habis.",
      "Audit Cepat Berbasis Barcode: Selisih terdeteksi dalam hitungan menit, bukan bulanan."
    ],
    footerPill: "Pelajari di https://cooca.id/inventory/stocks"
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
  background-color: #FFFFFF;
  background-image: 
    radial-gradient(circle at 10% 10%, rgba(37, 99, 235, 0.08) 0%, transparent 50%),
    radial-gradient(circle at 90% 90%, rgba(56, 189, 248, 0.08) 0%, transparent 50%);
  font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
  color: #0A1128;
  overflow: hidden;
  position: relative;
  padding: 48px 56px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

/* Subtle modern blue grid background */
body::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-size: 40px 40px;
  background-image: 
    linear-gradient(to right, rgba(37, 99, 235, 0.04) 1px, transparent 1px),
    linear-gradient(to bottom, rgba(37, 99, 235, 0.04) 1px, transparent 1px);
  pointer-events: none;
}

/* Header */
.header {
  position: relative;
  z-index: 2;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 2px solid #E2E8F0;
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
  background: #FFFFFF;
  border: 1px solid #E2E8F0;
  box-shadow: 0 2px 8px rgba(10, 17, 40, 0.06);
}

.brand-logo {
  height: 38px;
  width: auto;
  object-fit: contain;
}

.tag-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 18px;
  border-radius: 999px;
  background: #EFF6FF;
  border: 1.5px solid #BFDBFE;
  font-size: 13px;
  font-weight: 700;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  color: #2563EB;
}

.slide-number-pill {
  font-size: 14px;
  font-weight: 800;
  color: #1E40AF;
  background: #DBEAFE;
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
  gap: 22px;
  margin-top: 10px;
}

.overline {
  font-size: 13px;
  font-weight: 800;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: #2563EB;
}

.headline {
  font-size: 38px;
  font-weight: 800;
  line-height: 1.25;
  color: #0A1128;
  letter-spacing: -0.02em;
}

.subtitle {
  font-size: 18px;
  font-weight: 500;
  line-height: 1.5;
  color: #475569;
}

/* Cards & Points Container */
.cards-container {
  display: flex;
  flex-direction: column;
  gap: 14px;
  margin-top: 6px;
}

.point-card {
  display: flex;
  align-items: flex-start;
  gap: 16px;
  padding: 18px 22px;
  border-radius: 16px;
  background: #F8FAFC;
  border: 1.5px solid #E2E8F0;
  box-shadow: 0 4px 12px rgba(10, 17, 40, 0.03);
}

.point-icon {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  background: #2563EB;
  color: #FFFFFF;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  font-weight: 800;
  flex-shrink: 0;
  margin-top: 2px;
}

.point-text {
  font-size: 17px;
  font-weight: 600;
  line-height: 1.45;
  color: #1E293B;
}

/* Featured Stat Card (for Slide 1) */
.stat-highlight-box {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 22px 28px;
  border-radius: 18px;
  background: linear-gradient(135deg, #1E40AF 0%, #2563EB 100%);
  color: #FFFFFF;
  box-shadow: 0 10px 25px rgba(37, 99, 235, 0.25);
  margin-bottom: 4px;
}

.stat-number {
  font-size: 46px;
  font-weight: 800;
  letter-spacing: -0.02em;
  color: #FFFFFF;
}

.stat-desc {
  font-size: 18px;
  font-weight: 600;
  color: #BFDBFE;
}

/* Footer */
.footer {
  position: relative;
  z-index: 2;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-top: 2px solid #E2E8F0;
  padding-top: 20px;
}

.footer-brand {
  font-size: 14px;
  font-weight: 700;
  color: #64748B;
  display: flex;
  align-items: center;
  gap: 8px;
}

.footer-brand span {
  color: #2563EB;
}

.footer-pill {
  padding: 10px 24px;
  border-radius: 12px;
  background: #2563EB;
  color: #FFFFFF;
  font-size: 15px;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 8px;
  box-shadow: 0 4px 14px rgba(37, 99, 235, 0.3);
}

</style>
</head>
<body>

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
    <div class="overline">${slide.badge}</div>
    <div class="headline">${slide.title}</div>
    <div class="subtitle">${slide.subtitle}</div>

    ${slide.highlightNumber ? `
    <div class="stat-highlight-box">
      <div>
        <div class="stat-desc">${slide.highlightLabel}</div>
        <div style="font-size: 14px; color: #DBEAFE; margin-top: 2px;">Terdeteksi saat stok fisik dihitung langsung</div>
      </div>
      <div class="stat-number">${slide.highlightNumber}</div>
    </div>
    ` : ''}

    <div class="cards-container">
      ${slide.points.map((pt, idx) => `
        <div class="point-card">
          <div class="point-icon">${idx + 1}</div>
          <div class="point-text">${pt}</div>
        </div>
      `).join('')}
    </div>
  </div>

  <!-- Footer -->
  <div class="footer">
    <div class="footer-brand">
      Sistem Operasional Ritel Modern • <span>cooca.id</span>
    </div>
    <div class="footer-pill">
      ${slide.footerPill}
    </div>
  </div>

</body>
</html>`;
}

async function renderSlides() {
  console.log('Launching Chromium for 5-slide White & Blue Facebook Carousel...');
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
    const artifactPath = path.join(ARTIFACT_DIR, `canva-facebook-carousel-slide-0${slide.slideNumber}.png`);

    await page.screenshot({ path: targetPath, type: 'png' });
    fs.copyFileSync(targetPath, artifactPath);
    console.log(`[Slide ${slide.slideNumber}] Rendered successfully -> ${targetPath} (${fs.statSync(targetPath).size} bytes)`);
  }

  await browser.close();
  console.log('ALL 5 SLIDES GENERATED WITH WHITE & BLUE IDENTITY AND LOGO_DARK.PNG!');
}

renderSlides().catch(err => {
  console.error('Rendering error:', err);
  process.exit(1);
});
