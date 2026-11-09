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
const ARTIFACT_DIR = path.join('C:/Users/Ulfah Ramadhani/.gemini/antigravity/brain/54c940fa-5f8a-47fa-9d77-cc6245059f93');

if (!fs.existsSync(ARTIFACT_DIR)) {
  fs.mkdirSync(ARTIFACT_DIR, { recursive: true });
}

const logoBase64 = getBase64Image(LOGO_PATH);
const iconShopBase64 = getBase64Image(path.join(ASSETS_DIR, '3d_shop_store.png'));
const iconBankBase64 = getBase64Image(path.join(ASSETS_DIR, '3d_bank_building.png'));
const iconTargetBase64 = getBase64Image(path.join(ASSETS_DIR, '3d_target.png'));
const iconRocketBase64 = getBase64Image(path.join(ASSETS_DIR, '3d_rocket.png'));

const htmlContent = `<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Cooca Facebook Feed Infographic</title>
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
    radial-gradient(circle at 12% 15%, rgba(37, 99, 235, 0.28) 0%, transparent 45%),
    radial-gradient(circle at 88% 22%, rgba(56, 189, 248, 0.22) 0%, transparent 45%),
    radial-gradient(circle at 50% 88%, rgba(30, 58, 138, 0.30) 0%, transparent 50%);
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

.ambient-glow {
  position: absolute;
  width: 500px;
  height: 500px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(56, 189, 248, 0.12) 0%, transparent 70%);
  top: 30%;
  left: 30%;
  pointer-events: none;
  filter: blur(60px);
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
  padding-bottom: 20px;
}

.brand-wrapper {
  display: flex;
  align-items: center;
  padding: 6px 14px;
  border-radius: 12px;
  background: #FFFFFF;
  border: 1px solid rgba(255, 255, 255, 0.9);
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.25);
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
  margin-top: 10px;
  display: flex;
  align-items: center;
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
  font-size: 34px;
  font-weight: 800;
  line-height: 1.25;
  color: #FFFFFF;
  letter-spacing: -0.02em;
}

.headline span {
  background: linear-gradient(135deg, #38BDF8 0%, #60A5FA 60%, #93C5FD 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.corner-3d-icon {
  width: 86px;
  height: 86px;
  object-fit: contain;
  filter: drop-shadow(0 10px 20px rgba(0, 0, 0, 0.45));
  flex-shrink: 0;
}

/* 3 Strategy Pillar Cards */
.pillar-grid {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 18px;
  margin: 14px 0;
}

.pillar-card {
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 20px;
  padding: 24px 20px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  backdrop-filter: blur(12px);
  position: relative;
  overflow: hidden;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
}

.pillar-card.card-alert {
  border-color: rgba(248, 113, 113, 0.35);
  background: linear-gradient(180deg, rgba(239, 68, 68, 0.08) 0%, rgba(255, 255, 255, 0.03) 100%);
}

.pillar-num {
  font-size: 26px;
  font-weight: 800;
  color: #38BDF8;
  margin-bottom: 12px;
}

.pillar-num.alert-num {
  color: #F87171;
}

.pillar-title {
  font-size: 17px;
  font-weight: 700;
  color: #FFFFFF;
  margin-bottom: 8px;
  line-height: 1.35;
}

.pillar-desc {
  font-size: 13px;
  font-weight: 400;
  color: #94A3B8;
  line-height: 1.5;
}

.pillar-badge {
  display: inline-block;
  margin-top: 14px;
  padding: 5px 12px;
  border-radius: 8px;
  font-size: 11px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.badge-alert {
  background: rgba(239, 68, 68, 0.2);
  color: #FCA5A5;
  border: 1px solid rgba(248, 113, 113, 0.4);
}

.badge-info {
  background: rgba(37, 99, 235, 0.22);
  color: #93C5FD;
  border: 1px solid rgba(59, 130, 246, 0.4);
}

.badge-success {
  background: rgba(14, 165, 233, 0.2);
  color: #38BDF8;
  border: 1px solid rgba(56, 189, 248, 0.4);
}

/* Highlight Banner */
.highlight-banner {
  background: linear-gradient(135deg, rgba(37, 99, 235, 0.2) 0%, rgba(15, 23, 42, 0.75) 100%);
  border: 1.5px solid rgba(56, 189, 248, 0.35);
  border-radius: 20px;
  padding: 18px 24px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 10px;
  box-shadow: 0 8px 24px rgba(37, 99, 235, 0.2);
}

.banner-left {
  display: flex;
  align-items: center;
  gap: 16px;
}

.banner-icon {
  width: 50px;
  height: 50px;
  object-fit: contain;
  filter: drop-shadow(0 6px 12px rgba(0, 0, 0, 0.35));
}

.banner-text-title {
  font-size: 16px;
  font-weight: 700;
  color: #FFFFFF;
  margin-bottom: 2px;
}

.banner-text-desc {
  font-size: 13px;
  color: #94A3B8;
}

.banner-pill {
  padding: 8px 18px;
  border-radius: 12px;
  background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%);
  border: 1px solid rgba(56, 189, 248, 0.5);
  color: #FFFFFF;
  font-size: 13px;
  font-weight: 700;
  white-space: nowrap;
  box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35);
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
<div class="ambient-glow"></div>
<div class="content">

  <!-- Header -->
  <div class="header">
    <div class="brand-wrapper">
      <img src="${logoBase64}" alt="COOCA" class="brand-logo">
    </div>
    <div class="tag-badge">Diskusi Komunitas UMKM 2026</div>
  </div>

  <!-- Title & Hook -->
  <div class="title-section">
    <div class="title-text-col">
      <div class="overline">Manajemen Arus Kas &amp; Buku Bon Toko</div>
      <h1 class="headline">Pusing Menagih Bon Hutang Pelanggan? <span>Atur Piutang Digital Tanpa Ribut</span></h1>
    </div>
    ${iconShopBase64 ? `<img src="${iconShopBase64}" alt="3D Shop" class="corner-3d-icon">` : ''}
  </div>

  <!-- 3 Strategy Cards -->
  <div class="pillar-grid">
    
    <div class="pillar-card card-alert">
      <div>
        <div class="pillar-num alert-num">01</div>
        <div class="pillar-title">Tentukan Plafon Hutang</div>
        <div class="pillar-desc">Tetapkan batas maksimal bon per pelanggan tetap secara terukur agar tidak membengkak tanpa kendali.</div>
      </div>
      <div>
        <span class="pillar-badge badge-alert">Kontrol Risiko</span>
      </div>
    </div>

    <div class="pillar-card">
      <div>
        <div class="pillar-num">02</div>
        <div class="pillar-title">Pencatatan Otomatis di Kasir</div>
        <div class="pillar-desc">Setiap bon dicatat langsung di layar POS kasir dan terhubung ke profil pelanggan tanpa buku manual.</div>
      </div>
      <div>
        <span class="pillar-badge badge-info">Sistem POS Kilat</span>
      </div>
    </div>

    <div class="pillar-card">
      <div>
        <div class="pillar-num">03</div>
        <div class="pillar-title">Notifikasi Sopan via WhatsApp</div>
        <div class="pillar-desc">Kirim struk digital dan pengingat jatuh tempo otomatis tanpa rasa sungkan atau mengganggu hubungan.</div>
      </div>
      <div>
        <span class="pillar-badge badge-success">WhatsApp Otomatis</span>
      </div>
    </div>

  </div>

  <!-- Highlight Banner -->
  <div class="highlight-banner">
    <div class="banner-left">
      <img src="${iconBankBase64}" alt="Finance" class="banner-icon">
      <div>
        <div class="banner-text-title">Arus Kas Toko Terjaga Sehat, Hubungan Pelanggan Tetap Harmonis</div>
        <div class="banner-text-desc">Solusi pembukuan bon digital tanpa khawatir buku hilang, tercecer, atau salah hitung.</div>
      </div>
    </div>
    <div class="banner-pill">cooca.id/whatsapp</div>
  </div>

  <!-- Footer -->
  <div class="footer">
    <div class="footer-left">
      <span>COOCA Business Operating System</span>
      <span class="footer-bullet"></span>
      <span>Omnichannel ERP UMKM Indonesia</span>
    </div>
    <div class="footer-url">cooca.id/whatsapp</div>
  </div>

</div>
</body>
</html>`;

async function run() {
  console.log('[Playwright] Launching browser to render 1080x1080 Facebook Feed Infographic (Dark-Tech COOCA)...');
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
  
  const outputPath = path.join(outputDir, 'facebook-feed-piutang-bon-1080x1080.png');
  await page.screenshot({ path: outputPath, type: 'png' });
  console.log(`[Success] Facebook Infographic rendered to: ${outputPath}`);

  const artifactPath = path.join(ARTIFACT_DIR, 'cooca-feed-facebook-1080x1080.png');
  fs.copyFileSync(outputPath, artifactPath);
  console.log(`[Artifact] Copied rendered asset to: ${artifactPath}`);
  
  await browser.close();
}

run().catch(err => {
  console.error('[Error rendering Facebook infographic]:', err);
  process.exit(1);
});
