const { chromium } = require('c:/laragon/www/cooca_core/node_modules/@playwright/test');
const fs = require('fs');
const path = require('path');

function getBase64Image(filePath) {
  if (!fs.existsSync(filePath)) {
    console.warn(`[Asset Warning] File not found: ${filePath}`);
    return '';
  }
  const ext = path.extname(filePath).replace('.', '') || 'png';
  const buf = fs.readFileSync(filePath);
  return `data:image/${ext};base64,${buf.toString('base64')}`;
}

const ROOT_DIR = 'C:/laragon/www/cooca_core';
const LOGO_PATH = path.join(ROOT_DIR, 'public/assets/image/1785229034_logo_dark.png');
const ASSETS_DIR = path.join(ROOT_DIR, 'public/admin/social-media-assets/pinterest-showcase');

const logoBase64 = getBase64Image(LOGO_PATH);
const iconShopBase64 = getBase64Image(path.join(ASSETS_DIR, '3d_shop_store.png'));
const iconTargetBase64 = getBase64Image(path.join(ASSETS_DIR, '3d_target.png'));
const iconBankBase64 = getBase64Image(path.join(ASSETS_DIR, '3d_bank_building.png'));

const htmlContent = `<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Cooca Instagram Feed Infographic</title>
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
  background-color: #070B14;
  background-image: 
    radial-gradient(circle at 10% 15%, rgba(239, 68, 68, 0.18) 0%, transparent 45%),
    radial-gradient(circle at 90% 20%, rgba(37, 99, 235, 0.20) 0%, transparent 45%),
    radial-gradient(circle at 50% 90%, rgba(59, 130, 246, 0.16) 0%, transparent 50%);
  font-family: 'Plus Jakarta Sans', -apple-system, sans-serif;
  color: #FFFFFF;
  overflow: hidden;
  position: relative;
  padding: 44px 50px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

/* Background grid */
.grid-bg {
  position: absolute;
  inset: 0;
  background-size: 36px 36px;
  background-image: 
    linear-gradient(to right, rgba(255, 255, 255, 0.028) 1px, transparent 1px),
    linear-gradient(to bottom, rgba(255, 255, 255, 0.028) 1px, transparent 1px);
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

/* Header */
.header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 1px solid rgba(255, 255, 255, 0.09);
  padding-bottom: 20px;
}

.brand-wrapper {
  display: flex;
  align-items: center;
  gap: 16px;
}

.brand-logo {
  background: #FFFFFF;
  padding: 4px 10px;
  border-radius: 8px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.12);
  height: 38px;
  width: auto;
  object-fit: contain;
}

.tag-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: rgba(239, 68, 68, 0.15);
  border: 1px solid rgba(239, 68, 68, 0.35);
  color: #FCA5A5;
  font-size: 13px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1px;
  padding: 6px 14px;
  border-radius: 999px;
}

.tag-pulse {
  width: 8px;
  height: 8px;
  background: #EF4444;
  border-radius: 50%;
  box-shadow: 0 0 10px #EF4444;
}

.edition-label {
  font-size: 13px;
  font-weight: 600;
  color: #94A3B8;
  letter-spacing: 0.5px;
}

/* Title Section */
.title-section {
  margin-top: 14px;
  margin-bottom: 12px;
}

.pre-title {
  color: #F87171;
  font-size: 14px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 1.5px;
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 8px;
}

.main-title {
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-size: 34px;
  font-weight: 800;
  line-height: 1.22;
  letter-spacing: -0.5px;
  color: #FFFFFF;
}

.main-title span.highlight-alert {
  background: linear-gradient(135deg, #F87171 0%, #EF4444 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.main-title span.highlight-solution {
  background: linear-gradient(135deg, #38BDF8 0%, #2563EB 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.subtitle {
  color: #94A3B8;
  font-size: 15px;
  font-weight: 500;
  margin-top: 6px;
  line-height: 1.45;
}

/* 3 Problem Cards Grid */
.cards-grid {
  display: flex;
  flex-direction: column;
  gap: 14px;
  margin: 6px 0;
}

.item-card {
  background: rgba(15, 23, 42, 0.72);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 18px;
  padding: 16px 22px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  backdrop-filter: blur(14px);
  position: relative;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.35);
}

.item-card::before {
  content: '';
  position: absolute;
  left: 0;
  top: 14px;
  bottom: 14px;
  width: 4px;
  border-radius: 0 4px 4px 0;
}

.item-card.danger::before {
  background: linear-gradient(to bottom, #EF4444, #DC2626);
}

.item-card.warning::before {
  background: linear-gradient(to bottom, #F59E0B, #D97706);
}

.item-card.caution::before {
  background: linear-gradient(to bottom, #EC4899, #BE185D);
}

.card-left {
  display: flex;
  align-items: center;
  gap: 18px;
  flex: 1;
}

.card-num-badge {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  font-weight: 800;
  font-size: 15px;
  flex-shrink: 0;
}

.item-card.danger .card-num-badge {
  background: rgba(239, 68, 68, 0.16);
  border: 1px solid rgba(239, 68, 68, 0.32);
  color: #F87171;
}

.item-card.warning .card-num-badge {
  background: rgba(245, 158, 11, 0.16);
  border: 1px solid rgba(245, 158, 11, 0.32);
  color: #FBBF24;
}

.item-card.caution .card-num-badge {
  background: rgba(236, 72, 153, 0.16);
  border: 1px solid rgba(236, 72, 153, 0.32);
  color: #F472B6;
}

.card-text {
  flex: 1;
}

.card-title-row {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 4px;
}

.card-point-title {
  font-size: 17px;
  font-weight: 700;
  color: #F1F5F9;
}

.risk-tag {
  font-size: 11px;
  font-weight: 700;
  padding: 3px 8px;
  border-radius: 6px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.danger .risk-tag {
  background: rgba(239, 68, 68, 0.2);
  color: #FCA5A5;
}

.warning .risk-tag {
  background: rgba(245, 158, 11, 0.2);
  color: #FDE68A;
}

.caution .risk-tag {
  background: rgba(236, 72, 153, 0.2);
  color: #FBCFE8;
}

.card-desc {
  font-size: 13.5px;
  color: #94A3B8;
  line-height: 1.42;
}

.card-icon-right {
  width: 58px;
  height: 58px;
  flex-shrink: 0;
  margin-left: 14px;
  object-fit: contain;
  filter: drop-shadow(0 6px 12px rgba(0, 0, 0, 0.45));
}

/* Solution Banner */
.solution-banner {
  background: linear-gradient(135deg, rgba(37, 99, 235, 0.15) 0%, rgba(14, 165, 233, 0.16) 100%);
  border: 1px solid rgba(37, 99, 235, 0.35);
  border-radius: 18px;
  padding: 16px 24px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  box-shadow: 0 10px 30px rgba(37, 99, 235, 0.12);
  backdrop-filter: blur(12px);
  margin-top: 4px;
}

.sol-left {
  display: flex;
  align-items: center;
  gap: 16px;
}

.sol-icon-box {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  background: rgba(37, 99, 235, 0.22);
  border: 1px solid rgba(37, 99, 235, 0.4);
  display: flex;
  align-items: center;
  justify-content: center;
  color: #38BDF8;
  font-size: 22px;
  flex-shrink: 0;
}

.sol-title {
  font-size: 16px;
  font-weight: 800;
  color: #38BDF8;
  letter-spacing: 0.3px;
  margin-bottom: 3px;
}

.sol-desc {
  font-size: 13px;
  color: #BAE6FD;
  font-weight: 500;
  line-height: 1.35;
}

.sol-badge {
  background: #2563EB;
  color: #FFFFFF;
  font-weight: 800;
  font-size: 12px;
  padding: 8px 16px;
  border-radius: 10px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  flex-shrink: 0;
  box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4);
}

/* Footer */
.footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-top: 1px solid rgba(255, 255, 255, 0.09);
  padding-top: 18px;
}

.footer-route {
  display: flex;
  align-items: center;
  gap: 10px;
}

.route-pill {
  background: rgba(59, 130, 246, 0.16);
  border: 1px solid rgba(59, 130, 246, 0.35);
  color: #93C5FD;
  font-weight: 700;
  font-size: 13px;
  padding: 5px 14px;
  border-radius: 8px;
  font-family: monospace;
}

.route-note {
  font-size: 13px;
  color: #94A3B8;
  font-weight: 500;
}

.cta-box {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  font-weight: 700;
  color: #E2E8F0;
}

.cta-highlight {
  color: #38BDF8;
  font-weight: 800;
}
</style>
</head>
<body>
<div class="grid-bg"></div>

<div class="content">
  <!-- Header -->
  <div class="header">
    <div class="brand-wrapper">
      <img src="${logoBase64}" alt="COOCA" class="brand-logo" />
      <div class="tag-badge">
        <span class="tag-pulse"></span>
        <span>Anti-Fraud Kasir</span>
      </div>
    </div>
    <div class="edition-label">Edisi Edukasi Operasional Ritel</div>
  </div>

  <!-- Title & Pre-title -->
  <div class="title-section">
    <div class="pre-title">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
      Audit Operasional Meja Kasir
    </div>
    <h1 class="main-title">
      Omzet Ramai Tapi Laba Tipis? <br/>
      Waspadai <span class="highlight-alert">3 Celah Bocor Halus</span> Meja Kasir
    </h1>
    <p class="subtitle">
      Banyak pemilik usaha mengira laba turun karena sepi pembeli, padahal celah selisih fisik kas dan pembukuan sering terjadi di meja kasir.
    </p>
  </div>

  <!-- 3 Problem Cards -->
  <div class="cards-grid">
    <!-- Card 1 -->
    <div class="item-card danger">
      <div class="card-left">
        <div class="card-num-badge">
          <span>01</span>
        </div>
        <div class="card-text">
          <div class="card-title-row">
            <span class="card-point-title">Transaksi Lewat Tanpa Input Struk Resmi</span>
            <span class="risk-tag">Celah Terbesar</span>
          </div>
          <p class="card-desc">
            Saat toko sedang antre panjang, transaksi tunai dilayani tanpa cetak struk POS. Uang masuk laci kasir tetapi stok barang dan omzet tidak tercatat di sistem.
          </p>
        </div>
      </div>
      <img src="${iconShopBase64}" class="card-icon-right" alt="Store Icon" />
    </div>

    <!-- Card 2 -->
    <div class="item-card warning">
      <div class="card-left">
        <div class="card-num-badge">
          <span>02</span>
        </div>
        <div class="card-text">
          <div class="card-title-row">
            <span class="card-point-title">Ambil Uang Laci Kasir Buat Operasional Dadakan</span>
            <span class="risk-tag">Kas Bon Lepas</span>
          </div>
          <p class="card-desc">
            Beli galon, gas, atau bayar kurir langsung comot dari laci kasir tanpa voucher petty cash. Di akhir shift, kasir bingung kenapa uang fisik selalu tekor.
          </p>
        </div>
      </div>
      <img src="${iconBankBase64}" class="card-icon-right" alt="Bank Icon" />
    </div>

    <!-- Card 3 -->
    <div class="item-card caution">
      <div class="card-left">
        <div class="card-num-badge">
          <span>03</span>
        </div>
        <div class="card-text">
          <div class="card-title-row">
            <span class="card-point-title">Batal Transaksi (Void) & Diskon Tanpa Otorisasi</span>
            <span class="risk-tag">Rawan Disalahgunakan</span>
          </div>
          <p class="card-desc">
            Kasir memiliki wewenang bebas membatalkan item setelah pelanggan bayar tunai, atau memberi potongan manual tanpa persetujuan supervisor.
          </p>
        </div>
      </div>
      <img src="${iconTargetBase64}" class="card-icon-right" alt="Target Icon" />
    </div>
  </div>

  <!-- Solution Banner -->
  <div class="solution-banner">
    <div class="sol-left">
      <div class="sol-icon-box">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#38BDF8" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
        </svg>
      </div>
      <div>
        <div class="sol-title">Solusi Sistematis: Otorisasi Bertingkat & Blind Balancing</div>
        <div class="sol-desc">Kunci akses kasir dengan PIN Supervisor, log pembatalan real-time, dan hitung kas tutup shift tanpa bocor saldo sistem.</div>
      </div>
    </div>
    <div class="sol-badge">Aman & Teruji</div>
  </div>

  <!-- Footer -->
  <div class="footer">
    <div class="footer-route">
      <span class="route-pill">cooca.id/roles</span>
      <span class="route-note">Fitur Hak Akses Kasir & Audit Anti-Fraud</span>
    </div>
    <div class="cta-box">
      <span>Simpan & bagikan tips ini ke tim operasional Anda!</span>
    </div>
  </div>
</div>
</body>
</html>`;

async function main() {
  console.log('[Instagram Infographic] Launching Chromium via Playwright...');
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({
    viewport: { width: 1080, height: 1080 },
    deviceScaleFactor: 2
  });
  const page = await context.newPage();

  console.log('[Instagram Infographic] Setting HTML content...');
  await page.setContent(htmlContent, { waitUntil: 'networkidle' });

  // Ensure fonts loaded
  await page.evaluate(async () => {
    await document.fonts.ready;
  });

  const outputDir = path.join(ROOT_DIR, 'storage/app/public/content-assets');
  if (!fs.existsSync(outputDir)) {
    fs.mkdirSync(outputDir, { recursive: true });
  }

  const outputPath = path.join(outputDir, 'instagram-feed-fraud-prevention-1080x1080.png');
  console.log(`[Instagram Infographic] Rendering screenshot to: ${outputPath}`);
  await page.screenshot({ path: outputPath, type: 'png' });

  const publicOutputDir = path.join(ROOT_DIR, 'public/storage/content-assets');
  if (!fs.existsSync(publicOutputDir)) fs.mkdirSync(publicOutputDir, { recursive: true });
  fs.copyFileSync(outputPath, path.join(publicOutputDir, 'instagram-feed-fraud-prevention-1080x1080.png'));

  const artifactDir = 'C:/Users/Ulfah Ramadhani/.gemini/antigravity/brain/129c79f1-b78e-4b6b-a604-690a7bf322a9';
  if (fs.existsSync(artifactDir)) {
    fs.copyFileSync(outputPath, path.join(artifactDir, 'instagram-feed-fraud-prevention-1080x1080.png'));
    console.log(`[Success] Copied to artifact directory: ${artifactDir}`);
  }

  await browser.close();
  console.log('[Instagram Infographic] Render completed successfully! 1080x1080 px generated.');
}

main().catch(err => {
  console.error('[Render Error]:', err);
  process.exit(1);
});
