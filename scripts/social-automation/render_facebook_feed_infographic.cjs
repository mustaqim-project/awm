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
const LOGO_PATH = path.join(ROOT_DIR, 'public/assets/image/1785229034_logo_light.png');
const ASSETS_DIR = path.join(ROOT_DIR, 'public/admin/social-media-assets/pinterest-showcase');

const logoBase64 = getBase64Image(LOGO_PATH);
const iconTargetBase64 = getBase64Image(path.join(ASSETS_DIR, '3d_target.png'));
const iconBankBase64 = getBase64Image(path.join(ASSETS_DIR, '3d_bank_building.png'));
const iconRocketBase64 = getBase64Image(path.join(ASSETS_DIR, '3d_rocket.png'));

const htmlContent = `<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Cooca Facebook Feed Infographic</title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Bricolage+Grotesque:opsz,wght@12..96,700;12..96,800&display=swap');

* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

body {
  width: 1080px;
  height: 1080px;
  background-color: #0A0E1A;
  background-image: 
    radial-gradient(circle at 15% 15%, rgba(59, 130, 246, 0.22) 0%, transparent 45%),
    radial-gradient(circle at 85% 25%, rgba(16, 185, 129, 0.18) 0%, transparent 45%),
    radial-gradient(circle at 50% 90%, rgba(245, 158, 11, 0.15) 0%, transparent 50%);
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
  padding-bottom: 20px;
}

.brand-wrapper {
  display: flex;
  align-items: center;
  gap: 16px;
}

.brand-logo {
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
  background: rgba(245, 158, 11, 0.15);
  border: 1px solid rgba(245, 158, 11, 0.35);
  font-size: 13px;
  font-weight: 700;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  color: #FBBF24;
}

.tag-badge::before {
  content: '';
  display: inline-block;
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: #F59E0B;
  box-shadow: 0 0 10px #F59E0B;
}

/* Title & Hook */
.title-section {
  margin-top: 14px;
}

.overline {
  font-size: 14px;
  font-weight: 700;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: #94A3B8;
  margin-bottom: 8px;
}

.headline {
  font-family: 'Bricolage Grotesque', sans-serif;
  font-size: 35px;
  font-weight: 800;
  line-height: 1.2;
  color: #F8FAFC;
  letter-spacing: -0.02em;
}

.headline span {
  background: linear-gradient(135deg, #FBBF24 0%, #34D399 60%, #60A5FA 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

/* 3 Strategy Pillar Cards */
.pillar-grid {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 20px;
  margin: 18px 0;
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
}

.pillar-num {
  font-family: 'Bricolage Grotesque', sans-serif;
  font-size: 28px;
  font-weight: 800;
  color: #38BDF8;
  margin-bottom: 12px;
}

.pillar-title {
  font-size: 17px;
  font-weight: 700;
  color: #F8FAFC;
  margin-bottom: 8px;
  line-height: 1.3;
}

.pillar-desc {
  font-size: 13px;
  color: #94A3B8;
  line-height: 1.5;
}

.pillar-badge {
  display: inline-block;
  margin-top: 14px;
  padding: 4px 10px;
  border-radius: 8px;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.badge-warning {
  background: rgba(245, 158, 11, 0.18);
  color: #FBBF24;
  border: 1px solid rgba(245, 158, 11, 0.3);
}

.badge-info {
  background: rgba(59, 130, 246, 0.18);
  color: #60A5FA;
  border: 1px solid rgba(59, 130, 246, 0.3);
}

.badge-success {
  background: rgba(16, 185, 129, 0.18);
  color: #34D399;
  border: 1px solid rgba(16, 185, 129, 0.3);
}

/* Highlight Banner */
.highlight-banner {
  background: linear-gradient(135deg, rgba(16, 185, 129, 0.18) 0%, rgba(15, 23, 42, 0.65) 100%);
  border: 1px solid rgba(16, 185, 129, 0.35);
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
  gap: 16px;
}

.banner-icon {
  width: 50px;
  height: 50px;
  object-fit: contain;
}

.banner-text-title {
  font-size: 16px;
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
  background: #10B981;
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
    <div class="tag-badge">Diskusi Komunitas Ritel 2026</div>
  </div>

  <!-- Title & Hook -->
  <div class="title-section">
    <div class="overline">Manajemen Arus Kas &amp; Buku Bon Toko</div>
    <h1 class="headline">Pusing Menagih Bon Hutang Pelanggan? <span>Atur Piutang Digital Tanpa Ribut</span></h1>
  </div>

  <!-- 3 Strategy Cards -->
  <div class="pillar-grid">
    
    <div class="pillar-card">
      <div>
        <div class="pillar-num">01</div>
        <div class="pillar-title">Tentukan Plafon Hutang</div>
        <div class="pillar-desc">Tetapkan batas maksimal bon per pelanggan tetap secara terukur agar tidak membengkak tanpa kendali.</div>
      </div>
      <div>
        <span class="pillar-badge badge-warning">Kontrol Risiko</span>
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
        <div class="banner-text-title">Arus Kas Toko Terjaga Sehat, Pelanggan Tetap Nyaman</div>
        <div class="banner-text-desc">Solusi pembukuan bon digital tanpa khawatir buku hilang atau salah hitung.</div>
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
  console.log('[Playwright] Launching browser to render 1080x1080 Facebook Feed Infographic...');
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
  
  await browser.close();
}

run().catch(err => {
  console.error('[Error rendering Facebook infographic]:', err);
  process.exit(1);
});
