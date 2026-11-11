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
  const isPortrait = height > width * 1.2;
  const channel = (spec.channel || 'instagram').toLowerCase();
  const format = (spec.format || 'feed').toLowerCase();
  const sectorId = spec.sector_id || 'umkm';
  const sectorName = spec.sector || 'UMKM Indonesia';
  const clusterName = spec.cluster || 'Multi-Industri';
  const rawHook = spec.hook_title || 'Kelola Usaha Lebih Tenang Bersama COOCA';
  const slug = spec.slug || 'cooca-content';
  const metrics = (spec.content_strategy && spec.content_strategy.metrics) || 'Laba kotor operasional terkunci stabil dan pesanan selesai tepat waktu.';

  // Split hook into punchy headline + highlight question
  let mainHook = rawHook;
  let subHook = '';
  if (rawHook.includes('?')) {
    const parts = rawHook.split('?');
    mainHook = parts[0].trim() + '?';
    subHook = parts.slice(1).join('?').trim();
  }

  // Check if a generated sector photo exists strictly for this sector
  const photoCandidates = [
    path.join(GENERATED_ASSETS_DIR, `photo-${sectorId.replace(/_/g, '-')}.jpg`),
    path.join(GENERATED_ASSETS_DIR, `photo-${sectorId.replace(/_/g, '-')}.png`)
  ];

  let photoBase64 = '';
  for (const p of photoCandidates) {
    if (fs.existsSync(p)) {
      photoBase64 = getBase64Image(p);
      console.log(`[Universal Bento] Found sector photograph: ${p}`);
      break;
    }
  }

  console.log(`[Universal Bento] High-End Graphic Engine: ${format} for ${channel} (${width}x${height}) - Sektor: ${sectorName}`);

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
      title: rawHook,
      tag: `${clusterName} • ${sectorName}`,
      description: spec.solution_summary || '',
      points: spec.key_points || [
        'Kunci hitungan modal dan bahan baku sebelum produksi',
        'Terbitkan Surat Perintah Kerja (SPK) digital agar tepat waktu',
        'Pantau stok dan laporan keuangan real-time dari HP'
      ]
    }
  ];

  let outputPaths = [];
  let outputRelPaths = [];

  const PINTEREST_DIR = path.join(ROOT_DIR, 'public/admin/social-media-assets/pinterest-showcase');
  const UI_DIR = path.join(ROOT_DIR, 'storage/app/public/admin/social-media-assets/ui-screenshots');
  const carousel3DIcons = [
    '3d_target.png',        // Slide 1: Focus & Hook
    '3d_shop_store.png',     // Slide 2: Real Operations / Problem
    '3d_rocket.png',         // Slide 3: Cooca Digital Solution
    '3d_bank_building.png',  // Slide 4: Finance / Margin (ZERO GREEN)
    '3d_trophy.png'          // Slide 5: Victory & CTA
  ];

  for (let idx = 0; idx < slides.length; idx++) {
    const s = slides[idx];
    const slideNumber = s.slide || (idx + 1);
    const totalSlides = slides.length;
    const slideTitle = s.title || rawHook;
    const slideTag = s.tag || `${clusterName} • ${sectorName}`;
    const slideDesc = s.description || '';
    const points = Array.isArray(s.points) && s.points.length > 0 ? s.points : (spec.key_points || []);

    const isFirstSlide = idx === 0;
    const isLastSlide = idx === totalSlides - 1;

    // Slide-specific image resolution: strictly AI-generated photographic assets (NO website screenshots)
    let slidePhotoBase64 = '';
    const liveUiLabel = isCarousel ? `SLIDE 0${slideNumber}` : 'FOKUS OPERASIONAL NYATA';

    if (isCarousel) {
      const slideNumPad = String(slideNumber).padStart(2, '0');
      const slidePhotoCandidates = [
        path.join(ROOT_DIR, `storage/app/public/admin/social-media-assets/carousel/${channel}/photo-slide-${slideNumPad}.jpg`),
        path.join(ROOT_DIR, `storage/app/public/admin/social-media-assets/carousel/${channel}/photo-slide-${slideNumPad}.png`),
        path.join(GENERATED_ASSETS_DIR, `photo-${sectorId.replace(/_/g, '-')}-slide-${slideNumPad}.jpg`),
        path.join(GENERATED_ASSETS_DIR, `photo-${sectorId.replace(/_/g, '-')}-slide-${slideNumPad}.png`)
      ];
      for (const sp of slidePhotoCandidates) {
        if (fs.existsSync(sp)) {
          slidePhotoBase64 = getBase64Image(sp);
          console.log(`[Universal Bento] Found AI sector photo for slide ${slideNumber}: ${sp}`);
          break;
        }
      }
    } else {
      slidePhotoBase64 = photoBase64;
    }

    // Dynamic 3D asset per slide (strictly zero green icons)
    const current3DFileName = isCarousel 
      ? carousel3DIcons[idx % carousel3DIcons.length]
      : '3d_shop_store.png';
    const current3DBase64 = getBase64Image(path.join(PINTEREST_DIR, current3DFileName));

    // Build award-winning editorial graphic HTML
    const htmlContent = `<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Cooca Editorial Poster</title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,700;1,800&family=Space+Grotesk:wght@500;700&display=swap');

* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

body {
  width: ${width}px;
  height: ${height}px;
  background-color: #050814;
  background-image: 
    radial-gradient(circle at 85% 12%, rgba(37, 99, 235, 0.38) 0%, transparent 48%),
    radial-gradient(circle at 10% 88%, rgba(56, 189, 248, 0.24) 0%, transparent 45%),
    radial-gradient(circle at 50% 50%, rgba(15, 23, 42, 0.95) 0%, #050814 100%);
  font-family: 'Plus Jakarta Sans', -apple-system, sans-serif;
  color: #FFFFFF;
  overflow: hidden;
  position: relative;
  padding: ${isPortrait ? '56px 48px' : '48px 52px'};
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

/* Precision Geometric Mesh Grid */
.grid-overlay {
  position: absolute;
  inset: 0;
  background-size: 44px 44px;
  background-image: 
    linear-gradient(to right, rgba(255, 255, 255, 0.028) 1px, transparent 1px),
    linear-gradient(to bottom, rgba(255, 255, 255, 0.028) 1px, transparent 1px);
  pointer-events: none;
  z-index: 1;
}

/* Ambient Edge Vignette */
.ambient-glow {
  position: absolute;
  top: -120px;
  left: 20%;
  width: 600px;
  height: 250px;
  background: radial-gradient(ellipse, rgba(56, 189, 248, 0.18), transparent 70%);
  filter: blur(50px);
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

/* Header: Clean Agency Bar */
.header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  padding-bottom: 22px;
}

.brand-section {
  display: flex;
  align-items: center;
  gap: 18px;
}

.brand-logo {
  height: 38px;
  width: auto;
  object-fit: contain;
  background: transparent;
  filter: drop-shadow(0 2px 8px rgba(37, 99, 235, 0.3));
}

.header-divider {
  width: 1px;
  height: 24px;
  background: rgba(255, 255, 255, 0.15);
}

.category-tag {
  display: flex;
  align-items: center;
  gap: 8px;
  font-family: 'Space Grotesk', sans-serif;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.18em;
  text-transform: uppercase;
  color: #38BDF8;
  background: rgba(37, 99, 235, 0.12);
  border: 1px solid rgba(56, 189, 248, 0.3);
  padding: 5px 14px;
  border-radius: 100px;
}

.category-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #38BDF8;
  box-shadow: 0 0 8px #38BDF8;
}

.live-badge {
  font-family: 'Space Grotesk', sans-serif;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: rgba(255, 255, 255, 0.85);
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  padding: 6px 16px;
  border-radius: 100px;
}

/* Master Hero Zone */
.hero-zone {
  margin-top: 18px;
  margin-bottom: 8px;
}

.eyebrow-alert {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-family: 'Space Grotesk', sans-serif;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.16em;
  text-transform: uppercase;
  color: #EF4444;
  background: rgba(239, 68, 68, 0.10);
  border: 1px solid rgba(239, 68, 68, 0.35);
  padding: 5px 12px;
  border-radius: 6px;
  margin-bottom: 14px;
}

.eyebrow-alert-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #EF4444;
  box-shadow: 0 0 6px #EF4444;
}

.master-headline {
  font-size: ${isPortrait ? '44px' : '36px'};
  font-weight: 900;
  line-height: 1.15;
  letter-spacing: -0.035em;
  color: #FFFFFF;
  text-wrap: balance;
  max-width: 980px;
}

.gradient-accent {
  background: linear-gradient(135deg, #38BDF8 0%, #2563EB 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  display: inline;
}

/* Asymmetric Bento Body (Responsive to Aspect Ratio) */
.bento-body {
  display: grid;
  grid-template-columns: ${isPortrait ? '1fr' : '1.05fr 1.15fr'};
  ${isPortrait ? 'grid-template-rows: 560px 1fr;' : ''}
  gap: ${isPortrait ? '26px' : '22px'};
  flex: 1;
  margin-top: 14px;
  margin-bottom: 18px;
  align-items: stretch;
}

/* Left: The Double-Bezel Real Visual Card */
.visual-bezel-outer {
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 26px;
  padding: 8px;
  display: flex;
  flex-direction: column;
  position: relative;
  box-shadow: 0 16px 36px rgba(0, 0, 0, 0.45);
}

.visual-bezel-inner {
  position: relative;
  width: 100%;
  height: 100%;
  border-radius: 20px;
  overflow: hidden;
  border: 1px solid rgba(56, 189, 248, 0.25);
  background: #0A0F24;
}

.hero-photo {
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center;
  display: block;
}

.hero-photo.ui-screenshot {
  object-fit: cover;
  object-position: top left;
  filter: contrast(1.05) brightness(1.02);
}

.window-topbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 8px 14px;
  background: rgba(15, 23, 42, 0.95);
  border-bottom: 1px solid rgba(56, 189, 248, 0.25);
  font-family: 'Space Grotesk', sans-serif;
  font-size: 11px;
  position: relative;
  z-index: 3;
}

.window-dots {
  display: flex;
  gap: 6px;
}

.w-dot {
  width: 9px;
  height: 9px;
  border-radius: 50%;
}

.w-dot.red { background: #EF4444; }
.w-dot.yellow { background: #F59E0B; }
.w-dot.blue { background: #38BDF8; }

.window-title {
  color: #94A3B8;
  font-weight: 600;
  letter-spacing: 0.05em;
}

.window-badge {
  background: rgba(37, 99, 235, 0.3);
  border: 1px solid rgba(56, 189, 248, 0.4);
  color: #38BDF8;
  font-weight: 800;
  font-size: 9px;
  padding: 2px 8px;
  border-radius: 100px;
}

.photo-vignette {
  position: absolute;
  inset: 0;
  background: linear-gradient(to top, rgba(5, 8, 20, 0.95) 0%, rgba(5, 8, 20, 0.2) 55%, transparent 100%);
}

.photo-vignette.ui-mode {
  background: linear-gradient(to top, rgba(5, 8, 20, 0.92) 0%, rgba(5, 8, 20, 0.05) 45%, transparent 100%);
}

.photo-badge-top {
  position: absolute;
  top: 16px;
  left: 16px;
  background: rgba(7, 11, 25, 0.85);
  border: 1px solid rgba(56, 189, 248, 0.4);
  backdrop-filter: blur(10px);
  color: #38BDF8;
  font-family: 'Space Grotesk', sans-serif;
  font-size: 10px;
  font-weight: 700;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  padding: 5px 12px;
  border-radius: 100px;
}

.photo-content-bottom {
  position: absolute;
  bottom: 18px;
  left: 18px;
  right: 18px;
}

.stat-island {
  background: rgba(15, 23, 42, 0.90);
  border: 1px solid rgba(56, 189, 248, 0.4);
  backdrop-filter: blur(16px);
  border-radius: 16px;
  padding: 14px 18px;
  display: flex;
  align-items: center;
  gap: 16px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.5);
}

.stat-number {
  font-family: 'Space Grotesk', sans-serif;
  font-size: 32px;
  font-weight: 800;
  color: #38BDF8;
  line-height: 1;
  letter-spacing: -0.03em;
}

.stat-text {
  font-size: 12px;
  color: #FFFFFF;
  font-weight: 700;
  line-height: 1.35;
}

/* Right: Strategic Solution Bento Stack */
.solutions-stack {
  display: flex;
  flex-direction: column;
  gap: 12px;
  justify-content: space-between;
}

.solution-bezel {
  background: linear-gradient(135deg, rgba(15, 23, 42, 0.78) 0%, rgba(30, 41, 59, 0.45) 100%);
  border: 1px solid rgba(56, 189, 248, 0.20);
  border-radius: 18px;
  padding: 16px 20px;
  display: flex;
  align-items: flex-start;
  gap: 16px;
  backdrop-filter: blur(16px);
  position: relative;
  overflow: hidden;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);
}

.solution-bezel::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 4px;
  height: 100%;
  background: #2563EB;
}

.solution-bezel:first-child::before {
  background: #38BDF8;
}

.step-pill {
  font-family: 'Space Grotesk', sans-serif;
  font-size: 12px;
  font-weight: 800;
  color: #38BDF8;
  background: rgba(37, 99, 235, 0.2);
  border: 1px solid rgba(56, 189, 248, 0.35);
  width: 34px;
  height: 34px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  box-shadow: 0 0 12px rgba(37, 99, 235, 0.4);
}

.solution-content {
  flex: 1;
}

.solution-header {
  font-family: 'Space Grotesk', sans-serif;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: #94A3B8;
  margin-bottom: 4px;
}

.solution-desc {
  font-size: 14px;
  font-weight: 600;
  color: #F8FAFC;
  line-height: 1.45;
}

/* Callout Box on Bottom Right */
.feature-callout-card {
  background: linear-gradient(135deg, rgba(37, 99, 235, 0.15) 0%, rgba(15, 23, 42, 0.8) 100%);
  border: 1px solid rgba(56, 189, 248, 0.35);
  border-radius: 18px;
  padding: 16px 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  backdrop-filter: blur(12px);
}

.callout-title {
  font-size: 13px;
  font-weight: 800;
  color: #FFFFFF;
}

.callout-sub {
  font-size: 12px;
  font-weight: 500;
  color: #94A3B8;
  margin-top: 2px;
}

.callout-pill {
  background: #2563EB;
  color: #FFFFFF;
  font-family: 'Space Grotesk', sans-serif;
  font-size: 11px;
  font-weight: 700;
  padding: 6px 14px;
  border-radius: 100px;
  letter-spacing: 0.05em;
  box-shadow: 0 2px 10px rgba(37, 99, 235, 0.5);
}

/* Master Footer Island */
.footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
  padding-top: 20px;
}

/* Button-in-Button Island CTA Architecture */
.cta-island-btn {
  display: inline-flex;
  align-items: center;
  gap: 14px;
  background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%);
  border: 1px solid rgba(56, 189, 248, 0.6);
  box-shadow: 0 6px 24px rgba(37, 99, 235, 0.5);
  color: #FFFFFF;
  padding: 8px 10px 8px 24px;
  border-radius: 100px;
  text-decoration: none;
}

.cta-btn-text {
  font-size: 14px;
  font-weight: 800;
  letter-spacing: 0.02em;
}

.cta-nested-circle {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.2);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  font-weight: 900;
  color: #FFFFFF;
  border: 1px solid rgba(255, 255, 255, 0.3);
}

.footer-meta-block {
  text-align: right;
}

.meta-primary {
  font-family: 'Space Grotesk', sans-serif;
  font-size: 12px;
  font-weight: 700;
  color: #F8FAFC;
  letter-spacing: 0.04em;
}

.meta-secondary {
  font-size: 11px;
  font-weight: 500;
  color: #64748B;
  margin-top: 2px;
}
</style>
</head>
<body>
  <div class="grid-overlay"></div>
  <div class="ambient-glow"></div>
  <div class="content">
    
    <!-- Top Agency Header Bar -->
    <div class="header">
      <div class="brand-section">
        <img class="brand-logo" src="${logoBase64}" alt="COOCA">
        <div class="header-divider"></div>
        <div class="category-tag">
          <div class="category-dot"></div>
          ${escapeHtml(clusterName)} • ${escapeHtml(sectorName)}
        </div>
      </div>
      <div class="live-badge">${isCarousel ? `SLIDE ${slideNumber} / ${totalSlides}` : 'STANDAR OPERASIONAL UMKM'}</div>
    </div>

    <!-- Master Hero Headline Zone -->
    <div class="hero-zone">
      <div class="eyebrow-alert">
        <div class="eyebrow-alert-dot"></div>
        AUDIT BOCOR HALUS PRODUKSI
      </div>
      <h1 class="master-headline">
        ${escapeHtml(mainHook)}
        ${subHook ? `<br><span class="gradient-accent">${escapeHtml(subHook)}</span>` : ''}
      </h1>
    </div>

    <!-- Dynamic Asymmetric Bento Body -->
    <div class="bento-body">
      
      <!-- Left: Double-Bezel Real Visual (Unique per slide in carousel) -->
      <div class="visual-bezel-outer">
                <div class="visual-bezel-inner">
          ${slidePhotoBase64 ? `
            <img class="hero-photo" src="${slidePhotoBase64}" alt="${escapeHtml(sectorName)}">
            <div class="photo-vignette"></div>
          ` : `
            <div style="width:100%; height:100%; background: radial-gradient(circle at 50% 35%, rgba(37,99,235,0.35) 0%, #070B19 80%); display:flex; flex-direction:column; align-items:center; justify-content:center; padding: 24px; position:relative;">
              ${current3DBase64 ? `
                <img src="${current3DBase64}" style="width: 170px; height: 170px; object-fit: contain; filter: drop-shadow(0 14px 28px rgba(37,99,235,0.45)); margin-bottom: 16px;">
              ` : ''}
              <div style="font-family: 'Space Grotesk', sans-serif; font-size: 12px; font-weight: 700; color: #38BDF8; letter-spacing: 0.15em; text-transform: uppercase;">${escapeHtml(slideTag)}</div>
              <div style="font-size: 20px; font-weight: 900; color: #FFFFFF; margin-top: 6px; text-align: center;">${isCarousel ? `Langkah ${slideNumber} dari ${totalSlides}` : escapeHtml(sectorName)}</div>
            </div>
            <div class="photo-vignette"></div>
          `}
          <div class="photo-badge-top">${escapeHtml(liveUiLabel)}</div>
          
          <div class="photo-content-bottom">
            <div class="stat-island">
              <div class="stat-number">100%</div>
              <div class="stat-text">
                Kontrol Operasional ${escapeHtml(sectorName)}<br>
                <span style="color:#94A3B8; font-weight:500;">${escapeHtml(metrics)}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right: Solutions & Workflow Cards -->
      <div class="solutions-stack">
        ${points.slice(0, 3).map((pt, pIdx) => {
          const stepCategories = [
            '01 / KONTROL MODAL & HPP',
            '02 / ALUR KERJA & SPK DIGITAL',
            '03 / PENGAWASAN & STOK REAL-TIME'
          ];
          const cat = stepCategories[pIdx] || `LANGKAH 0${pIdx + 1}`;
          
          let title = '';
          let desc = pt;
          if (pt.includes(':')) {
            const parts = pt.split(':');
            title = parts[0].trim();
            desc = parts.slice(1).join(':').trim();
          } else {
            const words = pt.split(' ');
            if (words.length > 5) {
              title = words.slice(0, 4).join(' ');
              desc = words.slice(4).join(' ');
            } else {
              title = pt;
              desc = `Optimalkan ${sectorName} bersama sistem COOCA`;
            }
          }
          return `
            <div class="solution-bezel">
              <div class="step-pill">0${pIdx + 1}</div>
              <div class="solution-content">
                <div class="solution-header">${cat}</div>
                <div style="font-size: 15px; font-weight: 800; color: #FFFFFF; margin-bottom: 4px; letter-spacing: -0.01em; text-transform: capitalize;">${escapeHtml(title)}</div>
                <div class="solution-desc">${escapeHtml(desc)}</div>
              </div>
            </div>
          `;
        }).join('')}

        <div class="feature-callout-card">
          <div>
            <div class="callout-title">Fitur ${escapeHtml(spec.feature_name || 'Sistem Operasi COOCA')}</div>
            <div class="callout-sub">${escapeHtml(spec.solution_summary || 'Otomatisasi pencatatan usaha & keuangan rapi')}</div>
          </div>
          <div class="callout-pill">AKTIF</div>
        </div>
      </div>

    </div>

    <!-- Master Footer Island -->
    <div class="footer">
      <div class="cta-island-btn">
        <span class="cta-btn-text">Mulai Gratis Selamanya di cooca.id</span>
        <div class="cta-nested-circle">↗</div>
      </div>
      <div class="footer-meta-block">
        <div class="meta-primary">PAKET FREE RP 0 • SOLO OWNER</div>
        <div class="meta-secondary">Daftar Instan Tanpa Kartu Kredit • cooca.id</div>
      </div>
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
    console.log(`[Universal Bento] High-End Graphic saved to: ${fullOutPath}`);
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
  return outputPaths;
}

renderUniversalBento().catch(err => {
  console.error('[Universal Bento Error]', err);
  process.exit(1);
});
