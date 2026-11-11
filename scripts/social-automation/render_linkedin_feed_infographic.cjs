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
const iconGrowthBase64 = getBase64Image(path.join(ASSETS_DIR, '3d_growth_arrow.png'));
const iconPieBase64 = getBase64Image(path.join(ASSETS_DIR, '3d_pie_chart.png'));
const iconShopBase64 = getBase64Image(path.join(ASSETS_DIR, '3d_shop_store.png'));

const htmlContent = `<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Cooca LinkedIn Feed Infographic - GMROI Analysis</title>
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
  background-color: #070B19;
  background-image: 
    radial-gradient(circle at 10% 10%, rgba(37, 99, 235, 0.22) 0%, transparent 45%),
    radial-gradient(circle at 90% 20%, rgba(14, 165, 233, 0.18) 0%, transparent 45%),
    radial-gradient(circle at 50% 90%, rgba(37, 99, 235, 0.15) 0%, transparent 50%);
  font-family: 'Plus Jakarta Sans', -apple-system, sans-serif;
  color: #FFFFFF;
  overflow: hidden;
  position: relative;
  padding: 44px 52px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

/* Subtle architectural grid */
.grid-bg {
  position: absolute;
  inset: 0;
  background-size: 36px 36px;
  background-image: 
    linear-gradient(to right, rgba(255, 255, 255, 0.025) 1px, transparent 1px),
    linear-gradient(to bottom, rgba(255, 255, 255, 0.025) 1px, transparent 1px);
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
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  padding-bottom: 22px;
}

.brand-wrapper {
  display: flex;
  align-items: center;
  gap: 16px;
}

.brand-logo {
  background: transparent;
  
  
  
  height: 40px;
  width: auto;
  object-fit: contain;
}

.tag-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 7px 18px;
  border-radius: 999px;
  background: rgba(37, 99, 235, 0.16);
  border: 1px solid rgba(59, 130, 246, 0.35);
  font-size: 13px;
  font-weight: 700;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  color: #60A5FA;
}

.tag-badge::before {
  content: '';
  display: inline-block;
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: #38BDF8;
  box-shadow: 0 0 10px #38BDF8;
}

/* Title & Hook */
.title-section {
  margin-top: 16px;
}

.overline {
  font-size: 13px;
  font-weight: 700;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: #94A3B8;
  margin-bottom: 8px;
}

.headline {
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-size: 35px;
  font-weight: 800;
  line-height: 1.2;
  color: #F8FAFC;
  letter-spacing: -0.02em;
}

.headline span {
  background: linear-gradient(135deg, #60A5FA 0%, #38BDF8 60%, #38BDF8 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

/* Main Comparison Bento */
.comparison-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 22px;
  margin: 18px 0;
}

.card {
  border-radius: 22px;
  padding: 24px 26px;
  position: relative;
  backdrop-filter: blur(16px);
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.card-manual {
  background: rgba(239, 68, 68, 0.05);
  border: 1px solid rgba(239, 68, 68, 0.22);
}

.card-cooca {
  background: rgba(37, 99, 235, 0.06);
  border: 1px solid rgba(37, 99, 235, 0.35);
  box-shadow: 0 20px 40px -15px rgba(37, 99, 235, 0.15);
}

.card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 16px;
}

.card-label {
  font-size: 12px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  padding: 5px 14px;
  border-radius: 999px;
}

.card-manual .card-label {
  background: rgba(239, 68, 68, 0.18);
  color: #F87171;
  border: 1px solid rgba(239, 68, 68, 0.3);
}

.card-cooca .card-label {
  background: rgba(37, 99, 235, 0.18);
  color: #38BDF8;
  border: 1px solid rgba(37, 99, 235, 0.35);
}

.metric-group {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.metric-item {
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.06);
  border-radius: 14px;
  padding: 12px 16px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.metric-info {
  display: flex;
  flex-direction: column;
}

.metric-name {
  font-size: 13px;
  color: #94A3B8;
  font-weight: 500;
  margin-bottom: 2px;
}

.metric-val {
  font-size: 22px;
  font-weight: 800;
  font-family: 'Plus Jakarta Sans', sans-serif;
  letter-spacing: -0.01em;
}

.val-danger {
  color: #F87171;
}

.val-success {
  color: #38BDF8;
}

/* Key Highlight Banner */
.highlight-banner {
  background: linear-gradient(135deg, rgba(30, 58, 138, 0.35) 0%, rgba(15, 23, 42, 0.6) 100%);
  border: 1px solid rgba(59, 130, 246, 0.3);
  border-radius: 20px;
  padding: 18px 24px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 12px;
}

.banner-left {
  display: flex;
  align-items: center;
  gap: 18px;
}

.banner-icon {
  width: 50px;
  height: 50px;
  object-fit: contain;
}

.banner-text-title {
  font-size: 15px;
  font-weight: 700;
  color: #F8FAFC;
  margin-bottom: 2px;
}

.banner-text-desc {
  font-size: 13px;
  color: #94A3B8;
}

.banner-pill {
  padding: 8px 18px;
  border-radius: 12px;
  background: #2563EB;
  color: #FFFFFF;
  font-size: 13px;
  font-weight: 700;
  white-space: nowrap;
}

/* Footer */
.footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
  padding-top: 18px;
  font-size: 13px;
  color: #64748B;
}

.footer-left {
  display: flex;
  align-items: center;
  gap: 12px;
}

.footer-bullet {
  width: 4px;
  height: 4px;
  background: #64748B;
  border-radius: 50%;
}

.footer-url {
  font-weight: 700;
  color: #38BDF8;
  letter-spacing: 0.02em;
}
</style>
</head>
<body>
<div class="grid-bg"></div>
<div class="content">

  <!-- Header -->
  <div class="header">
    <div class="brand-wrapper">
      <img src="${logoBase64}" alt="COOCA" class="brand-logo">
    </div>
    <div class="tag-badge">Riset Unit Economics 2026</div>
  </div>

  <!-- Title & Hook -->
  <div class="title-section">
    <div class="overline">Evaluasi Profitabilitas Persediaan &amp; Margin Kotor</div>
    <h1 class="headline">GMROI: Mengapa Produk Terlaris <span>Belum Tentu Paling Menguntungkan?</span></h1>
  </div>

  <!-- Comparison Bento -->
  <div class="comparison-grid">
    
    <!-- Fast-Moving Low Margin -->
    <div class="card card-manual">
      <div>
        <div class="card-header">
          <span class="card-label">Produk Terlaris Margin Tipis</span>
          <span style="font-size: 12px; color: #F87171; font-weight: 600;">Ilusi Omzet Kasir</span>
        </div>
        <div class="metric-group">
          <div class="metric-item">
            <div class="metric-info">
              <span class="metric-name">Gross Profit Margin</span>
              <span class="metric-val val-danger">8% s/d 12%</span>
            </div>
          </div>
          <div class="metric-item">
            <div class="metric-info">
              <span class="metric-name">Hari Persediaan (DIO)</span>
              <span class="metric-val val-danger">45 - 60 Hari</span>
            </div>
          </div>
          <div class="metric-item">
            <div class="metric-info">
              <span class="metric-name">Skor Efisiensi Modal (GMROI)</span>
              <span class="metric-val val-danger">0.75x s/d 0.95x</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Healthy GMROI Portfolio -->
    <div class="card card-cooca">
      <div>
        <div class="card-header">
          <span class="card-label">Portofolio Optimal COOCA</span>
          <span style="font-size: 12px; color: #38BDF8; font-weight: 600;">Arus Kas Sehat</span>
        </div>
        <div class="metric-group">
          <div class="metric-item">
            <div class="metric-info">
              <span class="metric-name">Gross Profit Margin</span>
              <span class="metric-val val-success">28% s/d 42%</span>
            </div>
          </div>
          <div class="metric-item">
            <div class="metric-info">
              <span class="metric-name">Hari Persediaan (DIO)</span>
              <span class="metric-val val-success">12 - 18 Hari</span>
            </div>
          </div>
          <div class="metric-item">
            <div class="metric-info">
              <span class="metric-name">Skor Efisiensi Modal (GMROI)</span>
              <span class="metric-val val-success">2.6x s/d 3.4x</span>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>

  <!-- Highlight Banner -->
  <div class="highlight-banner">
    <div class="banner-left">
      <img src="${iconGrowthBase64}" alt="Growth" class="banner-icon">
      <div>
        <div class="banner-text-title">Rumus GMROI = Margin Kotor Tahunan (Rp) / Rata-rata Biaya Persediaan (Rp)</div>
        <div class="banner-text-desc">Prioritaskan restock pada SKU dengan laba kotor riil tertinggi, bukan sekadar volume transaksi.</div>
      </div>
    </div>
    <div class="banner-pill">cooca.id/reports</div>
  </div>

  <!-- Footer -->
  <div class="footer">
    <div class="footer-left">
      <span>COOCA Business Operating System</span>
      <span class="footer-bullet"></span>
      <span>Omnichannel ERP UMKM Indonesia</span>
    </div>
    <div class="footer-url">cooca.id/reports</div>
  </div>

</div>
</body>
</html>`;

async function run() {
  console.log('[Playwright] Launching browser to render 1080x1080 LinkedIn Feed Infographic (GMROI Analysis)...');
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({
    viewport: { width: 1080, height: 1080 },
    deviceScaleFactor: 2,
  });
  const page = await context.newPage();
  
  await page.setContent(htmlContent, { waitUntil: 'networkidle' });
  await page.waitForTimeout(1000);
  
  const outputDir = path.join(ROOT_DIR, 'storage/app/public/content-assets');
  if (!fs.existsSync(outputDir)) fs.mkdirSync(outputDir, { recursive: true });
  
  const outputPath = path.join(outputDir, 'linkedin-feed-gmroi-analysis-1080x1080.png');
  await page.screenshot({ path: outputPath, type: 'png' });
  console.log(`[Success] Infographic rendered to: ${outputPath}`);

  // Also maintain the compatibility filename
  const legacyOutputPath = path.join(outputDir, 'linkedin-feed-roi-cashier-1080x1080.png');
  fs.copyFileSync(outputPath, legacyOutputPath);

  const publicOutputDir = path.join(ROOT_DIR, 'public/storage/content-assets');
  if (!fs.existsSync(publicOutputDir)) fs.mkdirSync(publicOutputDir, { recursive: true });
  fs.copyFileSync(outputPath, path.join(publicOutputDir, 'linkedin-feed-gmroi-analysis-1080x1080.png'));
  fs.copyFileSync(outputPath, path.join(publicOutputDir, 'linkedin-feed-roi-cashier-1080x1080.png'));

  const artifactDir = 'C:/Users/Ulfah Ramadhani/.gemini/antigravity/brain/31b087dd-1d13-45ba-bc91-4ed9c249aed1';
  if (fs.existsSync(artifactDir)) {
    fs.copyFileSync(outputPath, path.join(artifactDir, 'linkedin-feed-gmroi-analysis-1080x1080.png'));
    console.log(`[Success] Copied to artifact directory: ${artifactDir}`);
  }
  
  await browser.close();
}

run().catch(err => {
  console.error('[Error rendering infographic]:', err);
  process.exit(1);
});
