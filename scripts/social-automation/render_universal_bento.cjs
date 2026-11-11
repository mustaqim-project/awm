const { chromium } = require('c:/laragon/www/cooca_core/node_modules/@playwright/test');
const fs = require('fs');
const path = require('path');

const ROOT_DIR = 'C:/laragon/www/cooca_core';
const LOGO_PATH = path.join(ROOT_DIR, 'public/assets/image/1785229034_logo_dark.png');
const GENERATED_ASSETS_DIR = path.join(ROOT_DIR, 'storage/app/public/admin/social-media-assets/generated');

function getBase64Image(filePath) {
  if (!fs.existsSync(filePath)) return '';
  const ext = path.extname(filePath).replace('.', '') || 'png';
  const buf = fs.readFileSync(filePath);
  return `data:image/${ext};base64,${buf.toString('base64')}`;
}

const logoBase64 = getBase64Image(LOGO_PATH);

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
  const sectorId = spec.sector_id || 'umkm';
  const sectorName = spec.sector || 'UMKM Indonesia';
  const clusterName = spec.cluster || 'Multi-Industri';
  const hookTitle = spec.hook_title || 'Kelola Usaha Lebih Tenang Bersama COOCA';
  const slug = spec.slug || 'cooca-content';
  const metrics = (spec.content_strategy && spec.content_strategy.metrics) || 'Margin operasional terkunci stabil dan pesanan selesai tepat waktu.';

  // Check if a generated sector photo exists
  const photoCandidates = [
    path.join(GENERATED_ASSETS_DIR, `photo-${sectorId.replace(/_/g, '-')}.jpg`),
    path.join(GENERATED_ASSETS_DIR, `photo-${sectorId.replace(/_/g, '-')}.png`),
    path.join(GENERATED_ASSETS_DIR, `photo-konveksi-garment.jpg`),
    path.join(GENERATED_ASSETS_DIR, `photo-konveksi-garment.png`)
  ];

  let photoBase64 = '';
  for (const p of photoCandidates) {
    if (fs.existsSync(p)) {
      photoBase64 = getBase64Image(p);
      console.log(`[Universal Bento] Found sector photograph: ${p}`);
      break;
    }
  }

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
      tag: `${clusterName} - ${sectorName}`,
      description: spec.solution_summary || 'Kelola operasional dan keuangan usaha lebih rapi di cooca.id',
      points: spec.key_points || [
        'Hitung biaya modal kain, aksesoris, dan upah jahit per potong baju',
        'Terbitkan Surat Perintah Kerja (SPK) digital agar produksi tepat jadwal',
        'Pantau sisa stok bahan kain di gudang tanpa perlu hitung gulungan manual'
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
    const slideTag = s.tag || `${clusterName} - ${sectorName}`;
    const slideDesc = s.description || '';
    const points = Array.isArray(s.points) && s.points.length > 0 ? s.points : (spec.key_points || []);

    const isFirstSlide = idx === 0;
    const isLastSlide = idx === totalSlides - 1;

    // Build modern HTML
    const htmlContent = `<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Cooca Bento</title>
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
    radial-gradient(circle at 10% 15%, rgba(37, 99, 235, 0.32) 0%, transparent 45%),
    radial-gradient(circle at 90% 25%, rgba(56, 189, 248, 0.22) 0%, transparent 45%),
    radial-gradient(circle at 50% 90%, rgba(30, 58, 138, 0.38) 0%, transparent 55%);
  font-family: 'Plus Jakarta Sans', -apple-system, sans-serif;
  color: #FFFFFF;
  overflow: hidden;
  position: relative;
  padding: 44px 48px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.grid-bg {
  position: absolute;
  inset: 0;
  background-size: 38px 38px;
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
  padding-bottom: 20px;
}

.brand-wrapper {
  display: flex;
  align-items: center;
  gap: 16px;
}

.brand-logo {
  height: 38px;
  width: auto;
  object-fit: contain;
  background: transparent;
}

.brand-pill {
  background: rgba(37, 99, 235, 0.18);
  border: 1px solid rgba(56, 189, 248, 0.45);
  color: #38BDF8;
  font-size: 13px;
  font-weight: 700;
  letter-spacing: 0.8px;
  text-transform: uppercase;
  padding: 6px 14px;
  border-radius: 20px;
}

.meta-pill {
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.14);
  color: #FFFFFF;
  font-size: 13px;
  font-weight: 700;
  padding: 6px 16px;
  border-radius: 20px;
}

/* Bento Layout */
.bento-feed-grid {
  display: grid;
  grid-template-columns: 1.15fr 1fr;
  gap: 20px;
  margin-top: 18px;
  margin-bottom: 18px;
  flex: 1;
  align-items: stretch;
}

.bento-left {
  display: flex;
  flex-direction: column;
  gap: 18px;
  justify-content: space-between;
}

.bento-right {
  display: flex;
  flex-direction: column;
  gap: 18px;
  justify-content: space-between;
}

/* Title Bento Card */
.title-card {
  background: rgba(15, 23, 42, 0.80);
  border: 1px solid rgba(56, 189, 248, 0.35);
  border-radius: 22px;
  padding: 26px 28px;
  backdrop-filter: blur(14px);
  box-shadow: 0 12px 32px rgba(0, 0, 0, 0.4);
}

.tag-badge {
  display: inline-block;
  color: #38BDF8;
  font-size: 12px;
  font-weight: 800;
  letter-spacing: 1px;
  text-transform: uppercase;
  margin-bottom: 10px;
}

.hook-title {
  font-size: 30px;
  font-weight: 800;
  line-height: 1.28;
  color: #FFFFFF;
  letter-spacing: -0.5px;
}

/* Points List */
.points-card {
  background: rgba(15, 23, 42, 0.70);
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 22px;
  padding: 24px 26px;
  display: flex;
  flex-direction: column;
  gap: 14px;
  flex: 1;
  justify-content: center;
  backdrop-filter: blur(12px);
}

.point-row {
  display: flex;
  align-items: flex-start;
  gap: 14px;
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
  box-shadow: 0 0 12px rgba(37, 99, 235, 0.65);
}

.point-text {
  font-size: 15px;
  color: #F8FAFC;
  font-weight: 600;
  line-height: 1.45;
}

/* Photo Card */
.photo-card {
  position: relative;
  border-radius: 22px;
  overflow: hidden;
  border: 1px solid rgba(56, 189, 248, 0.35);
  box-shadow: 0 12px 32px rgba(0, 0, 0, 0.45);
  flex: 1.3;
  min-height: 250px;
  display: flex;
  align-items: flex-end;
}

.photo-img {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center;
}

.photo-overlay {
  position: relative;
  z-index: 2;
  width: 100%;
  background: linear-gradient(to top, rgba(7, 11, 25, 0.95) 0%, rgba(7, 11, 25, 0.3) 70%, transparent 100%);
  padding: 18px 22px;
}

.photo-label {
  color: #38BDF8;
  font-size: 12px;
  font-weight: 800;
  letter-spacing: 0.8px;
  text-transform: uppercase;
}

.photo-caption {
  color: #FFFFFF;
  font-size: 14px;
  font-weight: 700;
  margin-top: 4px;
}

/* Metric Card */
.metric-card {
  background: rgba(15, 23, 42, 0.85);
  border: 1px solid rgba(56, 189, 248, 0.4);
  border-radius: 20px;
  padding: 20px 24px;
  backdrop-filter: blur(14px);
  display: flex;
  flex-direction: column;
  justify-content: center;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
}

.metric-number {
  font-size: 28px;
  font-weight: 900;
  color: #38BDF8;
  letter-spacing: -0.5px;
  line-height: 1.15;
}

.metric-label {
  font-size: 13px;
  color: #FFFFFF;
  font-weight: 700;
  margin-top: 4px;
}

.metric-desc {
  font-size: 12px;
  color: rgba(255, 255, 255, 0.7);
  font-weight: 500;
  margin-top: 4px;
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
  box-shadow: 0 4px 18px rgba(37, 99, 235, 0.45);
  color: #FFFFFF;
  font-size: 14px;
  font-weight: 800;
  padding: 11px 24px;
  border-radius: 30px;
  letter-spacing: 0.3px;
}

.footer-meta {
  color: rgba(255, 255, 255, 0.7);
  font-size: 13px;
  font-weight: 600;
}
</style>
</head>
<body>
  <div class="grid-bg"></div>
  <div class="content">
    <div class="header">
      <div class="brand-wrapper">
        <img class="brand-logo" src="${logoBase64}" alt="COOCA">
        <div class="brand-pill">${escapeHtml(clusterName)} - ${escapeHtml(sectorName)}</div>
      </div>
      <div class="meta-pill">${isCarousel ? `Slide ${slideNumber} / ${totalSlides}` : 'Solusi Operasional UMKM'}</div>
    </div>

    ${!isCarousel ? `
      <!-- Single Feed Bento Layout -->
      <div class="bento-feed-grid">
        <div class="bento-left">
          <div class="title-card">
            <div class="tag-badge">TITIK BOCOR HALUS PRODUKSI</div>
            <h1 class="hook-title">${escapeHtml(hookTitle)}</h1>
          </div>
          <div class="points-card">
            ${points.map((pt, pIdx) => `
              <div class="point-row">
                <div class="point-number">${pIdx + 1}</div>
                <div class="point-text">${escapeHtml(pt)}</div>
              </div>
            `).join('')}
          </div>
        </div>

        <div class="bento-right">
          ${photoBase64 ? `
            <div class="photo-card">
              <img class="photo-img" src="${photoBase64}" alt="${escapeHtml(sectorName)}">
              <div class="photo-overlay">
                <div class="photo-label">FOKUS PRODUKSI NYATA</div>
                <div class="photo-caption">Workshop ${escapeHtml(sectorName)}</div>
              </div>
            </div>
          ` : ''}
          <div class="metric-card">
            <div class="metric-number">25% LABA KOTOR</div>
            <div class="metric-label">Terkunci Pasti per Potong Baju</div>
            <div class="metric-desc">${escapeHtml(metrics)}</div>
          </div>
        </div>
      </div>
    ` : `
      <!-- Carousel Layout -->
      <div class="bento-feed-grid" style="grid-template-columns: 1fr;">
        <div class="title-card">
          <div class="tag-badge">${escapeHtml(slideTag)}</div>
          <h1 class="hook-title">${escapeHtml(slideTitle)}</h1>
          ${slideDesc && slideDesc !== slideTitle ? `<p style="font-size: 16px; color: rgba(255,255,255,0.8); margin-top: 10px;">${escapeHtml(slideDesc)}</p>` : ''}
        </div>
        <div class="points-card">
          ${points.map((pt, pIdx) => `
            <div class="point-row">
              <div class="point-number">${pIdx + 1}</div>
              <div class="point-text">${escapeHtml(pt)}</div>
            </div>
          `).join('')}
        </div>
      </div>
    `}

    <div class="footer">
      <div class="cta-badge">Mulai Gratis Selamanya di cooca.id</div>
      <div class="footer-meta">Paket Free Rp 0 Solo Owner • cooca.id</div>
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
