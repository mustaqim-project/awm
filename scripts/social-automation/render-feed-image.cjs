const { chromium } = require('c:/laragon/www/cooca_core/node_modules/@playwright/test');
const fs = require('fs');
const path = require('path');

function getBase64Image(filePath) {
  if (!fs.existsSync(filePath)) return '';
  const ext = path.extname(filePath).replace('.', '') || 'png';
  const buf = fs.readFileSync(filePath);
  return `data:image/${ext};base64,${buf.toString('base64')}`;
}

async function renderFeedGraphic({ feature, screenshotPath, outputPath, persona }) {
  const logoPath = path.resolve('public/assets/image/cooca-logo-landscape_inv.png');
  const logoDataUri = getBase64Image(logoPath);
  const screenshotDataUri = getBase64Image(screenshotPath);

  const businessName = (persona && persona.business_name) || feature.business_name || 'Danastri Fashion & Apparel';
  const ownerName = (persona && persona.owner_name) || feature.owner_name || 'Danang Danastri';
  const location = (persona && persona.location) || feature.location || 'Solo, Jawa Tengah';
  const categoryTag = (persona && persona.category) || feature.category || 'INVENTORI & GUDANG';

  const html = `<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Bricolage+Grotesque:opsz,wght@12..96,700;12..96,800&display=swap');
    
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }
    
    body {
      width: 1080px;
      height: 1350px;
      background-color: #0A0D14;
      background-image: 
        radial-gradient(circle at 15% 15%, rgba(0, 122, 255, 0.22) 0%, transparent 45%),
        radial-gradient(circle at 85% 35%, rgba(225, 48, 108, 0.18) 0%, transparent 45%),
        radial-gradient(circle at 50% 90%, rgba(52, 199, 89, 0.15) 0%, transparent 50%);
      font-family: 'Plus Jakarta Sans', -apple-system, sans-serif;
      color: #FFFFFF;
      overflow: hidden;
      position: relative;
      padding: 54px 56px 48px 56px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }

    /* Subtle grid overlay */
    .grid-bg {
      position: absolute;
      inset: 0;
      background-size: 40px 40px;
      background-image: linear-gradient(to right, rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                        linear-gradient(to bottom, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
      pointer-events: none;
      z-index: 1;
    }

    .content-wrapper {
      position: relative;
      z-index: 2;
      display: flex;
      flex-direction: column;
      height: 100%;
      justify-content: space-between;
    }

    /* Header */
    .header {
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .brand-box {
      display: flex;
      align-items: center;
      gap: 16px;
    }

    .brand-logo {
      height: 46px;
      width: auto;
      object-fit: contain;
    }

    .tag-pill {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 8px 18px;
      border-radius: 9999px;
      background: rgba(255, 255, 255, 0.08);
      border: 1px solid rgba(255, 255, 255, 0.15);
      backdrop-filter: blur(12px);
      font-size: 13px;
      font-weight: 700;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      color: #38BDF8;
    }

    .tag-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: #38BDF8;
      box-shadow: 0 0 10px #38BDF8;
    }

    .verified-pill {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-size: 13.5px;
      font-weight: 600;
      color: rgba(255, 255, 255, 0.7);
      background: rgba(0, 0, 0, 0.4);
      padding: 6px 14px;
      border-radius: 9999px;
      border: 1px solid rgba(255, 255, 255, 0.08);
    }

    .business-persona-pill {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      margin-top: 12px;
      padding: 6px 16px;
      border-radius: 9999px;
      background: rgba(255, 255, 255, 0.06);
      border: 1px solid rgba(255, 255, 255, 0.12);
      font-size: 13px;
      color: rgba(255, 255, 255, 0.9);
      font-weight: 600;
      max-width: fit-content;
    }

    .persona-biz {
      color: #38BDF8;
      font-weight: 700;
      letter-spacing: 0.02em;
    }

    .persona-sep {
      color: rgba(255, 255, 255, 0.35);
    }

    .persona-owner {
      color: rgba(255, 255, 255, 0.85);
    }

    .persona-loc {
      color: rgba(255, 255, 255, 0.65);
    }

    /* Headline Section */
    .headline-section {
      margin-top: 32px;
      margin-bottom: 24px;
    }

    .category-label {
      font-size: 13px;
      font-weight: 800;
      letter-spacing: 0.15em;
      text-transform: uppercase;
      color: #F59E0B;
      margin-bottom: 10px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .main-title {
      font-family: 'Bricolage Grotesque', 'Plus Jakarta Sans', sans-serif;
      font-size: 46px;
      line-height: 1.15;
      font-weight: 800;
      letter-spacing: -0.03em;
      color: #FFFFFF;
      max-width: 960px;
    }

    .main-title .highlight {
      background: linear-gradient(90deg, #38BDF8 0%, #818CF8 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .sub-title {
      font-size: 18px;
      line-height: 1.5;
      color: rgba(255, 255, 255, 0.75);
      margin-top: 14px;
      max-width: 860px;
      font-weight: 500;
    }

    /* Device Mockup */
    .mockup-container {
      position: relative;
      flex: 1;
      min-height: 520px;
      display: flex;
      align-items: center;
      justify-content: center;
      perspective: 1000px;
    }

    .window-card {
      width: 100%;
      height: 520px;
      background: #161B26;
      border-radius: 20px;
      border: 1px solid rgba(255, 255, 255, 0.15);
      box-shadow: 
        0 25px 60px -15px rgba(0, 0, 0, 0.8),
        0 0 0 1px rgba(255, 255, 255, 0.08),
        0 0 40px rgba(0, 122, 255, 0.15);
      overflow: hidden;
      display: flex;
      flex-direction: column;
      position: relative;
    }

    /* Browser Window Controls */
    .window-header {
      height: 42px;
      background: #11151F;
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
      display: flex;
      align-items: center;
      padding: 0 16px;
      gap: 12px;
    }

    .window-dots {
      display: flex;
      gap: 7px;
    }

    .dot {
      width: 11px;
      height: 11px;
      border-radius: 50%;
    }
    .dot.red { background: #FF5F56; }
    .dot.yellow { background: #FFBD2E; }
    .dot.green { background: #27C93F; }

    .window-url-bar {
      flex: 1;
      max-width: 420px;
      margin: 0 auto;
      height: 26px;
      background: rgba(255, 255, 255, 0.06);
      border-radius: 6px;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      font-family: ui-monospace, monospace;
      font-size: 12px;
      font-weight: 600;
      color: rgba(255, 255, 255, 0.85);
      border: 1px solid rgba(255, 255, 255, 0.08);
    }

    .window-store-badge {
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: 11.5px;
      font-weight: 700;
      color: #38BDF8;
      background: rgba(56, 189, 248, 0.12);
      border: 1px solid rgba(56, 189, 248, 0.25);
      padding: 3px 10px;
      border-radius: 9999px;
    }

    .window-store-badge .store-dot {
      width: 6px;
      height: 6px;
      border-radius: 50%;
      background: #38BDF8;
      box-shadow: 0 0 6px #38BDF8;
    }

    .window-body {
      flex: 1;
      overflow: hidden;
      background: #0D1117;
      position: relative;
    }

    .window-body img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      object-position: top center;
    }

    /* Floating Badges */
    .floating-badges {
      position: absolute;
      bottom: -16px;
      right: 24px;
      display: flex;
      gap: 12px;
      z-index: 10;
    }

    .chip-badge {
      padding: 10px 18px;
      border-radius: 14px;
      background: rgba(13, 17, 23, 0.88);
      border: 1px solid rgba(56, 189, 248, 0.35);
      backdrop-filter: blur(16px);
      box-shadow: 0 12px 30px rgba(0, 0, 0, 0.6);
      font-size: 13.5px;
      font-weight: 700;
      color: #FFFFFF;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    /* Footer */
    .footer {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding-top: 30px;
      border-top: 1px solid rgba(255, 255, 255, 0.1);
      margin-top: 24px;
    }

    .footer-left {
      display: flex;
      flex-direction: column;
      gap: 4px;
    }

    .footer-left .slogan {
      font-size: 15px;
      font-weight: 700;
      color: #FFFFFF;
    }

    .footer-left .desc {
      font-size: 12.5px;
      color: rgba(255, 255, 255, 0.5);
    }

    .cta-button {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      padding: 14px 28px;
      border-radius: 14px;
      background: linear-gradient(135deg, #007AFF 0%, #0051C7 100%);
      color: #FFFFFF;
      font-size: 15px;
      font-weight: 800;
      box-shadow: 0 8px 24px rgba(0, 122, 255, 0.35);
      border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .cta-button svg {
      width: 18px;
      height: 18px;
      fill: none;
      stroke: currentColor;
      stroke-width: 2.5;
    }
  </style>
</head>
<body>
  <div class="grid-bg"></div>
  <div class="content-wrapper">
    
    <!-- 1. Header -->
    <div class="header">
      <div class="brand-box">
        <img class="brand-logo" src="${logoDataUri}" alt="COOCA Logo">
      </div>
      <div class="tag-pill">
        <span class="tag-dot"></span>
        <span>${categoryTag}</span>
      </div>
      <div class="verified-pill">
        <span>cooca.id</span>
      </div>
    </div>

    <!-- 2. Headline & Hook -->
    <div class="headline-section">
      <div class="category-label">
        <span>FITUR RESMI COOCA</span> &bull; <span>${feature.name}</span>
      </div>
      <h1 class="main-title">${feature.headline.replace('?', '? <span class="highlight">').concat(feature.headline.includes('?') ? '</span>' : '')}</h1>
      <p class="sub-title">${feature.subheadline}</p>
      <div class="business-persona-pill">
        <span class="persona-biz">${businessName}</span>
        <span class="persona-sep">&bull;</span>
        <span class="persona-owner">Dikelola oleh ${ownerName}</span>
        <span class="persona-sep">&bull;</span>
        <span class="persona-loc">${location}</span>
      </div>
    </div>

    <!-- 3. Central Window Mockup with Actual Screen -->
    <div class="mockup-container">
      <div class="window-card">
        <div class="window-header">
          <div class="window-dots">
            <div class="dot red"></div>
            <div class="dot yellow"></div>
            <div class="dot green"></div>
          </div>
          <div class="window-url-bar">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
            <span>cooca.id${feature.route}</span>
          </div>
          <div class="window-store-badge">
            <span class="store-dot"></span>
            <span>${businessName}</span>
          </div>
        </div>
        <div class="window-body">
          <img src="${screenshotDataUri}" alt="${feature.name}">
        </div>
      </div>

      <div class="floating-badges">
        ${feature.badges.map(b => `<div class="chip-badge">${b}</div>`).join('')}
      </div>
    </div>

    <!-- 4. Footer CTA -->
    <div class="footer">
      <div class="footer-left">
        <span class="slogan">Kelola Bisnis Lebih Mudah dengan COOCA</span>
        <span class="desc">#1 Operating System Bisnis All-In-One untuk UMKM Indonesia</span>
      </div>
      <div class="cta-button">
        <span>Coba Gratis Sekarang</span>
        <svg viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      </div>
    </div>

  </div>
</body>
</html>`;

  const browser = await chromium.launch({
    executablePath: 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
    headless: true
  });

  const page = await browser.newPage({
    viewport: { width: 1080, height: 1350 },
    deviceScaleFactor: 2 // High retina crispness
  });

  await page.setContent(html, { waitUntil: 'networkidle' });
  await page.waitForTimeout(500); // ensure fonts & images render

  const outDir = path.dirname(outputPath);
  if (!fs.existsSync(outDir)) {
    fs.mkdirSync(outDir, { recursive: true });
  }

  await page.screenshot({ path: outputPath, type: 'png' });
  await browser.close();
  console.log('Rendered feed graphic saved to:', outputPath);
}

// Standalone runner test
if (require.main === module) {
  const catalog = JSON.parse(fs.readFileSync(path.resolve('scripts/social-automation/features-catalog.json'), 'utf8'));
  const feat = catalog[0]; // POS
  const ss = path.resolve('storage/app/public/content-assets/screenshots/pos-desktop.png');
  const out = path.resolve('storage/app/public/social-media-assets/platform/demo-feed-pos.png');

  renderFeedGraphic({ feature: feat, screenshotPath: ss, outputPath: out })
    .then(() => console.log('Render test complete.'))
    .catch(console.error);
}

module.exports = { renderFeedGraphic };
