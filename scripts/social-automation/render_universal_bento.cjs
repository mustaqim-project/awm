const { chromium } = require('c:/laragon/www/cooca_core/node_modules/@playwright/test');
const fs = require('fs');
const path = require('path');

const ROOT_DIR = 'C:/laragon/www/cooca_core';
const LOGO_PATH = path.join(ROOT_DIR, 'public/assets/image/1785229034_logo_dark.png');
const PINTEREST_ASSETS_DIR = path.join(ROOT_DIR, 'public/admin/social-media-assets/pinterest-showcase');

function getBase64Image(filePath) {
  if (!fs.existsSync(filePath)) return '';
  const ext = path.extname(filePath).replace('.', '') || 'png';
  const buf = fs.readFileSync(filePath);
  return `data:image/${ext};base64,${buf.toString('base64')}`;
}

const logoBase64 = getBase64Image(LOGO_PATH);
const iconShopBase64 = getBase64Image(path.join(PINTEREST_ASSETS_DIR, '3d_shop_store.png'));
const iconBankBase64 = getBase64Image(path.join(PINTEREST_ASSETS_DIR, '3d_bank_building.png'));
const iconTargetBase64 = getBase64Image(path.join(PINTEREST_ASSETS_DIR, '3d_target.png'));
const iconGrowthBase64 = getBase64Image(path.join(PINTEREST_ASSETS_DIR, '3d_growth_arrow.png'));

function parseArgs() {
  const args = process.argv.slice(2);
  let specPath = '';
  for (let i = 0; i < args.length; i++) {
    if (args[i] === '--spec-json' && args[i + 1]) {
      specPath = args[i + 1];
    }
  }
  return { specPath };
}

function getDimensions(spec) {
  const dimStr = spec.dimensions || '1080x1080';
  const parts = dimStr.split('x').map(Number);
  return {
    width: parts[0] || 1080,
    height: parts[1] || 1080
  };
}

function escapeHtml(str) {
  if (!str) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

async function renderUniversalBento() {
  const { specPath } = parseArgs();
  if (!specPath) {
    console.error('Usage: node render_universal_bento.cjs --spec-json <path>');
    process.exit(1);
  }

  const resolvedSpecPath = path.isAbsolute(specPath) ? specPath : path.join(ROOT_DIR, specPath);
  if (!fs.existsSync(resolvedSpecPath)) {
    console.error(`Spec JSON file not found: ${resolvedSpecPath}`);
    process.exit(1);
  }

  const spec = JSON.parse(fs.readFileSync(resolvedSpecPath, 'utf8'));
  const { width, height } = getDimensions(spec);
  const channel = (spec.channel || 'instagram').toLowerCase();
  const format = (spec.format || 'feed').toLowerCase();
  const sectorName = spec.sector || 'UMKM Indonesia';
  const clusterName = spec.cluster || 'Multi-Industri';
  const topicName = spec.topic_name || spec.hook_title || 'Tips Operasional UMKM';
  const hookTitle = spec.hook_title || 'Kelola Usaha Lebih Tenang Bersama COOCA';
  const slug = spec.slug || 'cooca-content';

  console.log(`[Universal Bento] Rendering ${format} for ${channel} (${width}x${height}) - Sektor: ${sectorName}`);

  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({
    viewport: { width, height },
    deviceScaleFactor: 2
  });
  const page = await context.newPage();

  const isCarousel = format === 'carousel' && Array.isArray(spec.slides) && spec.slides.length > 0;
  const slides = isCarousel ? spec.slides : [
    {
      slide: 1,
      title: hookTitle,
      tag: sectorName,
      description: spec.solution_summary || 'Kelola operasional dan keuangan usaha lebih rapi di cooca.id',
      points: spec.key_points || [
        'Pencatatan transaksi dan stok otomatis',
        'Laporan laba rugi dan keuangan real-time',
        'Mulai gratis selamanya untuk Solo Owner di cooca.id'
      ]
    }
  ];

  let outputPaths = [];
  let outputRelPaths = [];

  for (let idx = 0; idx < slides.length; idx++) {
    const s = slides[idx];
    const slideNumber = s.slide || (idx + 1);
    const totalSlides = slides.length;
    const slideTitle = s.title || hookTitle;
    const slideTag = s.tag || sectorName;
    const slideDesc = s.description || (idx === 0 ? hookTitle : (spec.solution_summary || ''));
    const points = Array.isArray(s.points) && s.points.length > 0 
      ? s.points 
      : (spec.key_points && spec.key_points.length > 0 ? spec.key_points : [
          'Catat otomatis tanpa pusing hitung manual',
          'Pantau performa penjualan lewat HP',
          'Daftar gratis selamanya di cooca.id'
        ]);

    const isFirstSlide = idx === 0;
    const isLastSlide = idx === totalSlides - 1;

    const htmlContent = `<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Cooca Bento Slide ${slideNumber}</title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap');

* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

body {
  width: ${width}px;
  height: ${height}px;
  background-color: #070B19;
  background-image: 
    radial-gradient(circle at 15% 15%, rgba(37, 99, 235, 0.28) 0%, transparent 45%),
    radial-gradient(circle at 85% 25%, rgba(56, 189, 248, 0.22) 0%, transparent 45%),
    radial-gradient(circle at 50% 90%, rgba(30, 58, 138, 0.35) 0%, transparent 55%);
  font-family: 'Plus Jakarta Sans', -apple-system, sans-serif;
  color: #FFFFFF;
  overflow: hidden;
  position: relative;
  padding: ${height > 1300 ? '60px 56px' : '48px 52px'};
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

/* Background grid */
.grid-bg {
  position: absolute;
  inset: 0;
  background-size: 40px 40px;
  background-image: 
    linear-gradient(to right, rgba(255, 255, 255, 0.035) 1px, transparent 1px),
    linear-gradient(to bottom, rgba(255, 255, 255, 0.035) 1px, transparent 1px);
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
  border-bottom: 1px solid rgba(255, 255, 255, 0.12);
  padding-bottom: 22px;
}

.brand-wrapper {
  display: flex;
  align-items: center;
  gap: 16px;
}

.brand-logo-container {
  background: #FFFFFF;
  padding: 6px 14px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 14px rgba(0,0,0,0.3);
}

.brand-logo {
  height: 32px;
  width: auto;
  object-fit: contain;
}

.brand-pill {
  background: rgba(37, 99, 235, 0.18);
  border: 1px solid rgba(56, 189, 248, 0.4);
  color: #38BDF8;
  font-size: 13px;
  font-weight: 700;
  letter-spacing: 0.8px;
  text-transform: uppercase;
  padding: 6px 14px;
  border-radius: 20px;
}

.slide-counter {
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.15);
  color: #FFFFFF;
  font-size: 14px;
  font-weight: 700;
  padding: 6px 16px;
  border-radius: 20px;
}

/* Main Bento Grid */
.main-bento {
  display: flex;
  flex-direction: column;
  gap: 20px;
  margin-top: 15px;
  margin-bottom: 15px;
  flex: 1;
  justify-content: center;
}

.title-card {
  background: rgba(15, 23, 42, 0.75);
  border: 1px solid rgba(56, 189, 248, 0.25);
  border-radius: 22px;
  padding: 30px 34px;
  backdrop-filter: blur(12px);
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.35);
}

.tag-badge {
  display: inline-block;
  color: #38BDF8;
  font-size: 13px;
  font-weight: 800;
  letter-spacing: 1px;
  text-transform: uppercase;
  margin-bottom: 10px;
}

.hook-title {
  font-size: ${height > 1300 ? '42px' : '36px'};
  font-weight: 800;
  line-height: 1.25;
  color: #FFFFFF;
  letter-spacing: -0.5px;
}

.hook-desc {
  font-size: 18px;
  color: rgba(255, 255, 255, 0.8);
  margin-top: 12px;
  line-height: 1.5;
}

/* Bento Points Cards */
.points-grid {
  display: grid;
  grid-template-columns: ${points.length > 2 ? '1fr 1fr' : '1fr'};
  gap: 16px;
}

.point-card {
  background: rgba(15, 23, 42, 0.65);
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 18px;
  padding: 20px 22px;
  display: flex;
  align-items: flex-start;
  gap: 14px;
  backdrop-filter: blur(10px);
}

.point-number {
  background: #2563EB;
  color: #FFFFFF;
  font-size: 13px;
  font-weight: 800;
  min-width: 28px;
  height: 28px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  box-shadow: 0 0 12px rgba(37, 99, 235, 0.6);
}

.point-text {
  font-size: 16px;
  color: #F8FAFC;
  font-weight: 600;
  line-height: 1.45;
}

/* Footer */
.footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-top: 1px solid rgba(255, 255, 255, 0.12);
  padding-top: 20px;
}

.cta-badge {
  background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%);
  border: 1px solid rgba(56, 189, 248, 0.6);
  box-shadow: 0 4px 18px rgba(37, 99, 235, 0.4);
  color: #FFFFFF;
  font-size: 15px;
  font-weight: 800;
  padding: 12px 26px;
  border-radius: 30px;
  letter-spacing: 0.3px;
}

.footer-meta {
  color: rgba(255, 255, 255, 0.65);
  font-size: 14px;
  font-weight: 600;
}
</style>
</head>
<body>
  <div class="grid-bg"></div>
  <div class="content">
    <div class="header">
      <div class="brand-wrapper">
        <div class="brand-logo-container">
          <img class="brand-logo" src="${logoBase64}" alt="COOCA">
        </div>
        <div class="brand-pill">${escapeHtml(clusterName)} - ${escapeHtml(sectorName)}</div>
      </div>
      ${isCarousel ? `<div class="slide-counter">Slide ${slideNumber} / ${totalSlides}</div>` : ''}
    </div>

    <div class="main-bento">
      <div class="title-card">
        <div class="tag-badge">${escapeHtml(slideTag)}</div>
        <h1 class="hook-title">${escapeHtml(slideTitle)}</h1>
        ${slideDesc && slideDesc !== slideTitle ? `<p class="hook-desc">${escapeHtml(slideDesc)}</p>` : ''}
      </div>

      <div class="points-grid">
        ${points.map((pt, pIdx) => `
          <div class="point-card">
            <div class="point-number">${pIdx + 1}</div>
            <div class="point-text">${escapeHtml(pt)}</div>
          </div>
        `).join('')}
      </div>
    </div>

    <div class="footer">
      <div class="cta-badge">Mulai Gratis Selamanya di cooca.id</div>
      <div class="footer-meta">${isCarousel && !isLastSlide ? 'Geser untuk info lengkap →' : 'cooca.id'}</div>
    </div>
  </div>
</body>
</html>`;

    await page.setContent(htmlContent, { waitUntil: 'networkidle' });

    let destDir = '';
    let fileName = '';

    if (isCarousel) {
      destDir = path.join(ROOT_DIR, `storage/app/public/admin/social-media-assets/carousel/${channel}`);
      fileName = `slide-${String(slideNumber).padStart(2, '0')}.png`;
    } else {
      destDir = path.join(ROOT_DIR, 'storage/app/public/content-assets');
      fileName = `${channel}-feed-${slug}-${width}x${height}.png`;
    }

    fs.mkdirSync(destDir, { recursive: true });
    const fullOutPath = path.join(destDir, fileName);
    await page.screenshot({ path: fullOutPath, type: 'png' });

    const relPath = fullOutPath.replace(path.join(ROOT_DIR, 'storage/app/public/'), '').replace(/\\/g, '/');
    outputPaths.push(fullOutPath);
    outputRelPaths.push(relPath);
    console.log(`[Universal Bento] Successfully saved slide ${slideNumber} to: ${fullOutPath}`);
  }

  await browser.close();

  // Update spec JSON with the exact media_path and local_media_paths so deployer picks it up
  if (isCarousel) {
    spec.media_paths = outputRelPaths;
    spec.local_media_paths = outputRelPaths;
  } else {
    spec.media_path = outputRelPaths[0];
    spec.local_media_path = outputRelPaths[0];
  }

  fs.writeFileSync(resolvedSpecPath, JSON.stringify(spec, null, 2), 'utf8');
  console.log(`[Universal Bento] Updated spec JSON media paths: ${resolvedSpecPath}`);
  return outputPaths;
}

renderUniversalBento().catch(err => {
  console.error('[Universal Bento Error]', err);
  process.exit(1);
});
