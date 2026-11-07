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

const LOGO_PATH = path.resolve('public/assets/image/1785229034_logo_light.png');
const PINTEREST_ASSETS_DIR = path.resolve('public/admin/social-media-assets/pinterest-showcase');
const SCREENSHOTS_DIR = path.resolve('storage/app/public/content-assets/screenshots');

// Load Assets
const logoBase64 = getBase64Image(LOGO_PATH);
const cutoutGrowthBase64 = getBase64Image(path.join(PINTEREST_ASSETS_DIR, 'cutout_3d_growth.png'));
const cutoutShopBase64 = getBase64Image(path.join(PINTEREST_ASSETS_DIR, 'cutout_3d_shop.png'));
const cutoutWomanBase64 = getBase64Image(path.join(PINTEREST_ASSETS_DIR, 'cutout_woman.png'));
const cutoutManBase64 = getBase64Image(path.join(PINTEREST_ASSETS_DIR, 'cutout_man.png'));
const cutoutFemale2Base64 = getBase64Image(path.join(PINTEREST_ASSETS_DIR, 'cutout_female2.png'));

// 3D Icons
const iconBankBase64 = getBase64Image(path.join(PINTEREST_ASSETS_DIR, '3d_bank_building.png'));
const iconStarBase64 = getBase64Image(path.join(PINTEREST_ASSETS_DIR, '3d_gold_star.png'));
const iconGrowthBase64 = getBase64Image(path.join(PINTEREST_ASSETS_DIR, '3d_growth_arrow.png'));
const iconPieBase64 = getBase64Image(path.join(PINTEREST_ASSETS_DIR, '3d_pie_chart.png'));
const iconRocketBase64 = getBase64Image(path.join(PINTEREST_ASSETS_DIR, '3d_rocket.png'));
const iconBagBase64 = getBase64Image(path.join(PINTEREST_ASSETS_DIR, '3d_shopping_bag.png'));
const iconCartBase64 = getBase64Image(path.join(PINTEREST_ASSETS_DIR, '3d_shopping_cart.png'));
const iconShopBase64 = getBase64Image(path.join(PINTEREST_ASSETS_DIR, '3d_shop_store.png'));
const iconTargetBase64 = getBase64Image(path.join(PINTEREST_ASSETS_DIR, '3d_target.png'));
const iconTrophyBase64 = getBase64Image(path.join(PINTEREST_ASSETS_DIR, '3d_trophy.png'));

// Real Screen Captures
const screenPosBase64 = getBase64Image(path.join(SCREENSHOTS_DIR, 'pos-desktop.png'));
const screenFinanceBase64 = getBase64Image(path.join(SCREENSHOTS_DIR, 'finance-desktop.png'));
const screenFraudBase64 = getBase64Image(path.join(SCREENSHOTS_DIR, 'fraud_prevention-desktop.png'));

function buildCarouselHtml() {
  return `<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Cooca Multi-Slide Carousel 1:1 Square (1080x1080) - Anti Tumpang Tindih</title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Bricolage+Grotesque:opsz,wght@12..96,700;12..96,800&display=swap');

:root {
  --navy-950: #050B1F;
  --navy-900: #0A1433;
  --navy-800: #0E1F4D;
  --navy-700: #162B68;
  --blue-600: #1E3A8A;
  --blue-500: #2563EB;
  --cyan-400: #38BDF8;
  --cyan-300: #7DD3FC;
  --paper: #FFFFFF;
  --mist: #EEF3FF;
  --ink: #0A1433;
  --radius-lg: 32px;
  --radius-md: 22px;
  --radius-sm: 16px;
  --pad-x: 56px;
}

* { box-sizing: border-box; margin: 0; padding: 0; }

body {
  background: #090b12;
  font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
  display: flex;
  flex-direction: column;
  gap: 60px;
  align-items: center;
  padding: 60px 0;
  -webkit-font-smoothing: antialiased;
}

/* ========================================================
   1:1 SQUARE SLIDE (1080 x 1080 px)
   ======================================================== */
.slide {
  position: relative;
  width: 1080px;
  height: 1080px;
  overflow: hidden;
  color: #FFFFFF;
  background: linear-gradient(150deg, #050B1F 0%, #0A1433 45%, #162B68 100%);
  flex: none;
}

.grain {
  position: absolute;
  inset: 0;
  opacity: 0.045;
  pointer-events: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='200' height='200'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.85' numOctaves='2' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
  z-index: 1;
}

.icon {
  width: 100%;
  height: 100%;
  fill: none;
  stroke: currentColor;
  stroke-width: 2.1;
  stroke-linecap: round;
  stroke-linejoin: round;
  display: inline-block;
}

/* Top Navigation Bar */
.top-bar {
  position: absolute;
  left: var(--pad-x);
  right: var(--pad-x);
  top: 36px;
  height: 42px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  z-index: 10;
}

.logo {
  height: 40px;
  width: auto;
  object-fit: contain;
}

.tag {
  font-size: 21px;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  padding: 6px 20px;
  border-radius: 999px;
  border: 1.5px solid rgba(255, 255, 255, 0.28);
  color: #FFFFFF;
  background: rgba(255, 255, 255, 0.08);
  backdrop-filter: blur(16px);
}

/* Header Area (y = 80 - 240px) */
.header-box {
  position: absolute;
  left: var(--pad-x);
  right: var(--pad-x);
  top: 96px;
  height: 140px;
  z-index: 5;
}

.header-box .over {
  font-size: 21px;
  font-weight: 800;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: #38BDF8;
  margin-bottom: 4px;
}

.header-box h1 {
  font-family: 'Bricolage Grotesque', 'Plus Jakarta Sans', sans-serif;
  font-size: 40px;
  font-weight: 800;
  line-height: 1.15;
  letter-spacing: -0.035em;
  margin-bottom: 6px;
}

.header-box p {
  font-size: 19px;
  line-height: 1.35;
  color: rgba(255, 255, 255, 0.88);
  max-width: 960px;
}

.hl-cyan {
  color: #38BDF8;
  background: linear-gradient(135deg, #38BDF8 0%, #93C5FD 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  display: inline-block;
  font-weight: 800;
}

.hl-red {
  color: #F87171;
  background: linear-gradient(135deg, #F87171 0%, #FCA5A5 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  display: inline-block;
  font-weight: 800;
}

/* Bottom Action Bar (y = 775 - 860px) */
.cta-action-bar {
  position: absolute;
  left: var(--pad-x);
  right: var(--pad-x);
  top: 775px;
  height: 76px;
  background: linear-gradient(135deg, #FFFFFF 0%, #EEF3FF 100%);
  border-radius: var(--radius-md);
  color: var(--ink);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 32px;
  box-shadow: 0 30px 60px -20px rgba(0, 0, 0, 0.5);
  z-index: 10;
}

.cta-action-bar .bar-left {
  display: flex;
  align-items: center;
  gap: 14px;
}

.cta-action-bar .bar-left img {
  width: 36px;
  height: 36px;
  object-fit: contain;
}

.cta-action-bar .bar-text {
  font-size: 21px;
  font-weight: 800;
  color: var(--ink);
}

.cta-action-bar .bar-btn {
  display: flex;
  align-items: center;
  gap: 10px;
  background: #2563EB;
  color: #FFFFFF;
  padding: 10px 22px;
  border-radius: 999px;
  font-size: 20px;
  font-weight: 700;
  white-space: nowrap;
  line-height: 1;
}

.cta-action-bar .bar-btn svg { width: 20px; height: 20px; }

/* 3-Pill Action Row */
.pills-action-row {
  position: absolute;
  left: var(--pad-x);
  right: var(--pad-x);
  top: 775px;
  height: 74px;
  display: flex;
  gap: 16px;
  z-index: 10;
}

.feature-pill {
  flex: 1;
  background: rgba(255, 255, 255, 0.08);
  border: 1.5px solid rgba(255, 255, 255, 0.18);
  border-radius: var(--radius-sm);
  padding: 12px 18px;
  display: flex;
  align-items: center;
  gap: 14px;
  backdrop-filter: blur(14px);
  box-shadow: 0 15px 35px -10px rgba(0, 0, 0, 0.4);
}

.feature-pill-thumb {
  width: 40px;
  height: 40px;
  flex: none;
  display: grid;
  place-items: center;
}

.feature-pill-thumb img {
  width: 100%;
  height: 100%;
  object-fit: contain;
  filter: drop-shadow(0 6px 12px rgba(0, 0, 0, 0.4));
}

.feature-pill span {
  font-size: 21px;
  font-weight: 700;
  color: #FFFFFF;
  line-height: 1.25;
}

/* Footer Area (y = 1030 - 1050px) */
.foot {
  position: absolute;
  left: var(--pad-x);
  right: var(--pad-x);
  bottom: 34px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  z-index: 10;
  font-size: 21px;
  font-weight: 600;
  color: rgba(255, 255, 255, 0.75);
}

.foot .identity {
  color: #FFFFFF;
  font-weight: 700;
}

.foot .pg {
  color: #38BDF8;
  font-variant-numeric: tabular-nums;
  font-weight: 700;
}

/* Ambient Glow Orbs */
.orb {
  position: absolute;
  border-radius: 50%;
  pointer-events: none;
  filter: blur(85px);
  z-index: 1;
}
.orb-cyan {
  background: radial-gradient(circle, rgba(56, 189, 248, 0.32) 0%, rgba(30, 58, 138, 0) 70%);
}
.orb-blue {
  background: radial-gradient(circle, rgba(37, 99, 235, 0.38) 0%, rgba(10, 20, 51, 0) 70%);
}
.orb-red {
  background: radial-gradient(circle, rgba(239, 68, 68, 0.28) 0%, rgba(10, 20, 51, 0) 70%);
}

/* ========================================================
   SLIDE 1 STYLES: THE HOOK & FINANCIAL LEAK WARNING
   ======================================================== */
.slide-1-stage {
  position: absolute;
  left: var(--pad-x);
  right: var(--pad-x);
  top: 260px;
  height: 490px;
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  z-index: 5;
}

.s1-human-wrap {
  position: absolute;
  left: 0;
  bottom: 0;
  width: 440px;
  height: 490px;
  z-index: 7;
}

.s1-human-wrap > img.human-photo {
  width: 100%;
  height: 100%;
  object-fit: contain;
  object-position: bottom left;
  filter: drop-shadow(0 25px 50px rgba(0, 0, 0, 0.85));
}

.s1-persona-tag {
  position: absolute;
  left: 20px;
  bottom: 18px;
  background: rgba(10, 20, 51, 0.92);
  border: 1.5px solid rgba(56, 189, 248, 0.55);
  backdrop-filter: blur(20px);
  border-radius: 999px;
  padding: 8px 18px;
  display: flex;
  align-items: center;
  gap: 10px;
  box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
  z-index: 8;
}

.s1-persona-tag img.badge-icon {
  width: 24px !important;
  height: 24px !important;
  max-width: 24px !important;
  max-height: 24px !important;
  object-fit: contain !important;
  flex: none;
}

.s1-persona-tag span {
  font-size: 21px;
  font-weight: 800;
  color: #FFFFFF;
}

.s1-alert-panel {
  position: absolute;
  right: 0;
  top: 15px;
  width: 480px;
  background: linear-gradient(145deg, rgba(255, 255, 255, 0.08) 0%, rgba(10, 20, 51, 0.88) 100%);
  border: 1.8px solid rgba(239, 68, 68, 0.55);
  border-radius: var(--radius-md);
  backdrop-filter: blur(28px);
  padding: 24px 28px;
  box-shadow: 0 35px 75px -20px rgba(0, 0, 0, 0.7);
  z-index: 6;
}

.s1-alert-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.s1-alert-top .tag-danger {
  background: rgba(239, 68, 68, 0.22);
  border: 1.5px solid rgba(239, 68, 68, 0.6);
  color: #F87171;
  border-radius: 999px;
  padding: 4px 14px;
  font-size: 20px;
  font-weight: 800;
  text-transform: uppercase;
}

.s1-alert-num {
  font-family: 'Bricolage Grotesque', sans-serif;
  font-size: 48px;
  font-weight: 800;
  color: #F87171;
  margin-bottom: 8px;
  letter-spacing: -0.03em;
}

.s1-alert-desc {
  font-size: 20px;
  color: rgba(255, 255, 255, 0.88);
  line-height: 1.34;
  margin-bottom: 16px;
}

.s1-growth-thumb-box {
  width: 100%;
  height: 130px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.04);
  border: 1px dashed rgba(255, 255, 255, 0.15);
  border-radius: var(--radius-sm);
  padding: 10px;
}

.s1-growth-thumb-box img {
  height: 100%;
  width: auto;
  object-fit: contain;
  filter: drop-shadow(0 10px 20px rgba(0,0,0,0.5));
}

/* ========================================================
   SLIDE 2 STYLES: ANATOMY OF CASH LEAKS (ROOT CAUSES)
   ======================================================== */
.s2-stage {
  position: absolute;
  left: var(--pad-x);
  right: var(--pad-x);
  top: 260px;
  height: 490px;
  z-index: 5;
}

.s2-3d-shop-wrap {
  position: absolute;
  left: -15px;
  top: 25px;
  width: 440px;
  height: 440px;
  z-index: 6;
}

.s2-3d-shop-wrap img {
  width: 100%;
  height: 100%;
  object-fit: contain;
  filter: drop-shadow(0 30px 50px rgba(0, 0, 0, 0.8));
}

.s2-stacked-causes {
  position: absolute;
  right: 0;
  top: 0;
  width: 480px;
  display: flex;
  flex-direction: column;
  gap: 14px;
  z-index: 7;
}

.s2-cause-card {
  background: linear-gradient(145deg, rgba(255, 255, 255, 0.09) 0%, rgba(10, 20, 51, 0.92) 100%);
  border: 1.5px solid rgba(255, 255, 255, 0.16);
  border-radius: var(--radius-sm);
  padding: 16px 20px;
  display: flex;
  align-items: flex-start;
  gap: 14px;
  box-shadow: 0 20px 45px -10px rgba(0, 0, 0, 0.6);
}

.s2-cause-card .card-icon {
  width: 44px;
  height: 44px;
  flex: none;
  display: grid;
  place-items: center;
}

.s2-cause-card .card-icon img {
  width: 100%;
  height: 100%;
  object-fit: contain;
  filter: drop-shadow(0 6px 12px rgba(0,0,0,0.5));
}

.s2-cause-card .card-body h4 {
  font-size: 21px;
  font-weight: 800;
  color: #FFFFFF;
  margin-bottom: 4px;
}

.s2-cause-card .card-body p {
  font-size: 19px;
  color: rgba(255, 255, 255, 0.8);
  line-height: 1.3;
}

/* ========================================================
   SLIDE 3 STYLES: REAL LIVE SCREEN UI KASIR POS
   ======================================================== */
.s3-stage {
  position: absolute;
  left: var(--pad-x);
  right: var(--pad-x);
  top: 260px;
  height: 490px;
  z-index: 5;
}

.s3-mockup-window {
  width: 100%;
  height: 400px;
  background: #0F172A;
  border-radius: var(--radius-md);
  border: 1.8px solid rgba(56, 189, 248, 0.4);
  box-shadow: 0 35px 80px -20px rgba(0, 0, 0, 0.8);
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.s3-window-header {
  height: 38px;
  background: #1E293B;
  display: flex;
  align-items: center;
  padding: 0 16px;
  gap: 8px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.s3-dot {
  width: 12px;
  height: 12px;
  border-radius: 50%;
}
.s3-dot-red { background: #EF4444; }
.s3-dot-yellow { background: #F59E0B; }
.s3-dot-green { background: #10B981; }

.s3-window-title {
  margin-left: 12px;
  font-size: 19px;
  color: rgba(255, 255, 255, 0.7);
  font-weight: 600;
}

.s3-window-body {
  flex: 1;
  position: relative;
  overflow: hidden;
  background: #000;
}

.s3-window-body img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: top center;
}

/* Floating Live Badges on Screenshot */
.s3-badge-floating {
  position: absolute;
  background: rgba(10, 20, 51, 0.9);
  border: 1.5px solid rgba(56, 189, 248, 0.6);
  border-radius: 999px;
  padding: 8px 18px;
  display: flex;
  align-items: center;
  gap: 10px;
  backdrop-filter: blur(16px);
  box-shadow: 0 15px 30px rgba(0, 0, 0, 0.6);
  z-index: 10;
}

.s3-badge-floating img {
  width: 28px;
  height: 28px;
  object-fit: contain;
}

.s3-badge-floating span {
  font-size: 20px;
  font-weight: 800;
  color: #FFFFFF;
}

/* ========================================================
   SLIDE 4 STYLES: AUDIT KEUANGAN & ROLE-BASED ACCESS
   ======================================================== */
.s4-stage {
  position: absolute;
  left: var(--pad-x);
  right: var(--pad-x);
  top: 260px;
  height: 490px;
  display: flex;
  gap: 24px;
  z-index: 5;
}

.s4-left-preview {
  flex: 1.15;
  height: 480px;
  background: #0F172A;
  border-radius: var(--radius-md);
  border: 1.8px solid rgba(56, 189, 248, 0.35);
  box-shadow: 0 35px 80px -20px rgba(0, 0, 0, 0.75);
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.s4-right-features {
  flex: 0.85;
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.s4-feature-box {
  background: linear-gradient(145deg, rgba(255, 255, 255, 0.08) 0%, rgba(10, 20, 51, 0.9) 100%);
  border: 1.5px solid rgba(56, 189, 248, 0.3);
  border-radius: var(--radius-sm);
  padding: 16px 18px;
  backdrop-filter: blur(20px);
}

.s4-feature-box .box-head {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 6px;
}

.s4-feature-box .box-head img {
  width: 32px;
  height: 32px;
  object-fit: contain;
}

.s4-feature-box .box-head h4 {
  font-size: 21px;
  font-weight: 800;
  color: #FFFFFF;
}

.s4-feature-box p {
  font-size: 19px;
  color: rgba(255, 255, 255, 0.82);
  line-height: 1.32;
}

/* ========================================================
   SLIDE 5 STYLES: HIGH-CONVERTING CTA (TAKE ACTION)
   ======================================================== */
.s5-stage {
  position: absolute;
  left: var(--pad-x);
  right: var(--pad-x);
  top: 260px;
  height: 490px;
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  z-index: 5;
}

.s5-human-wrap {
  position: absolute;
  left: 0;
  bottom: 0;
  width: 430px;
  height: 490px;
  z-index: 7;
}

.s5-human-wrap > img.human-photo {
  width: 100% !important;
  height: 100% !important;
  max-width: 430px !important;
  max-height: 490px !important;
  object-fit: contain !important;
  object-position: bottom left !important;
  filter: drop-shadow(0 25px 50px rgba(0, 0, 0, 0.85));
}

.s5-persona-tag {
  position: absolute;
  left: 15px;
  bottom: 18px;
  background: rgba(10, 20, 51, 0.94);
  border: 1.5px solid rgba(56, 189, 248, 0.55);
  backdrop-filter: blur(20px);
  border-radius: 999px;
  padding: 8px 18px;
  display: flex;
  align-items: center;
  gap: 10px;
  box-shadow: 0 15px 35px rgba(0, 0, 0, 0.6);
  z-index: 8;
}

.s5-persona-tag img.badge-icon {
  width: 24px !important;
  height: 24px !important;
  max-width: 24px !important;
  max-height: 24px !important;
  object-fit: contain !important;
  flex: none;
}

.s5-persona-tag span {
  font-size: 21px;
  font-weight: 800;
  color: #FFFFFF;
}

.s5-offer-card {
  position: absolute;
  right: 0;
  top: 10px;
  width: 490px;
  background: linear-gradient(145deg, rgba(255, 255, 255, 0.09) 0%, rgba(10, 20, 51, 0.92) 100%);
  border: 1.8px solid rgba(56, 189, 248, 0.5);
  border-radius: var(--radius-md);
  backdrop-filter: blur(28px);
  padding: 24px 28px;
  box-shadow: 0 35px 75px -20px rgba(0, 0, 0, 0.7);
  z-index: 6;
}

.s5-offer-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 14px;
}

.s5-offer-badge {
  background: rgba(16, 185, 129, 0.22);
  border: 1.5px solid rgba(16, 185, 129, 0.6);
  color: #34D399;
  border-radius: 999px;
  padding: 6px 16px;
  font-size: 20px;
  font-weight: 800;
  text-transform: uppercase;
}

.s5-offer-top img.trophy-badge {
  width: 52px !important;
  height: 52px !important;
  max-width: 52px !important;
  max-height: 52px !important;
  object-fit: contain !important;
  filter: drop-shadow(0 10px 20px rgba(245, 158, 11, 0.45));
}

.s5-offer-title {
  font-family: 'Bricolage Grotesque', sans-serif;
  font-size: 36px;
  font-weight: 800;
  color: #FFFFFF;
  margin-bottom: 14px;
  line-height: 1.15;
}

.s5-checklist {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.s5-check-item {
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 20px;
  color: rgba(255, 255, 255, 0.9);
  font-weight: 600;
}

.s5-check-item img.check-icon {
  width: 22px !important;
  height: 22px !important;
  max-width: 22px !important;
  max-height: 22px !important;
  object-fit: contain !important;
  flex: none;
}

</style>
</head>
<body>

<svg style="display:none;" viewBox="0 0 24 24">
  <defs>
    <g id="i-arrow">
      <path d="M5 12h14M12 5l7 7-7 7" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
    </g>
    <g id="i-check">
      <polyline points="20 6 9 17 4 12" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
    </g>
  </defs>
</svg>

<!-- ========================================================
     SLIDE 1: 1:1 SQUARE (1080 x 1080) - THE HOOK & LEAK WARNING
     ======================================================== -->
<section class="slide" id="slide-01">
  <div class="grain"></div>
  <div class="orb orb-red" style="left: -80px; top: 120px; width: 500px; height: 500px;"></div>
  <div class="orb orb-blue" style="right: -60px; bottom: 80px; width: 500px; height: 500px;"></div>
  
  <div class="top-bar">
    <img class="logo" src="${logoBase64}" alt="Cooca">
    <div class="tag">AUDIT OPERASIONAL</div>
  </div>

  <div class="header-box">
    <div class="over">PERINGATAN BISNIS RITEL</div>
    <h1>OMZET RAME TAPI UANG KAS <span class="hl-red">SERING SELISIH?</span></h1>
    <p>Jangan anggap wajar selisih kas harian. 82% kebocoran toko bermula dari kasir manual tanpa sistem rekonsiliasi otomatis.</p>
  </div>

  <div class="slide-1-stage">
    <!-- Real Human Cutout Photograph -->
    <div class="s1-human-wrap">
      <img class="human-photo" src="${cutoutWomanBase64}" alt="Pemilik Toko Kamu">
      <div class="s1-persona-tag">
        <img class="badge-icon" src="${iconStarBase64}" alt="Verified">
        <span>Pemilik Toko Kamu</span>
      </div>
    </div>

    <!-- Alert Panel -->
    <div class="s1-alert-panel">
      <div class="s1-alert-top">
        <span style="font-size:20px; font-weight:700; color:rgba(255,255,255,0.75);">RISIKO KEBOCORAN</span>
        <span class="tag-danger">WASPADA</span>
      </div>
      <div class="s1-alert-num">-Rp 1.450.000</div>
      <div class="s1-alert-desc">Rata-rata akumulasi selisih kas fisik per bulan pada toko ritel yang masih mengandalkan nota manual.</div>
      <div class="s1-growth-thumb-box">
        <img src="${cutoutGrowthBase64}" alt="3D Loss Analysis">
      </div>
    </div>
  </div>

  <!-- 3-Pill Bottom Action Row -->
  <div class="pills-action-row">
    <div class="feature-pill">
      <div class="feature-pill-thumb"><img src="${iconShopBase64}" alt="Shop"></div>
      <span>Kasir POS Pintar Kilat</span>
    </div>
    <div class="feature-pill">
      <div class="feature-pill-thumb"><img src="${iconGrowthBase64}" alt="Growth"></div>
      <span>Audit Selisih Real-Time</span>
    </div>
    <div class="feature-pill">
      <div class="feature-pill-thumb"><img src="${iconCartBase64}" alt="Cart"></div>
      <span>Cegah Kebocoran Kasir</span>
    </div>
  </div>

  <div class="foot">
    <div class="identity">Toko Kamu &bull; Didukung oleh Cooca Indonesia</div>
    <div class="pg">01 / 05</div>
  </div>
</section>

<!-- ========================================================
     SLIDE 2: 1:1 SQUARE (1080 x 1080) - ANATOMY OF CASH LEAKS
     ======================================================== -->
<section class="slide" id="slide-02">
  <div class="grain"></div>
  <div class="orb orb-blue" style="left: 40px; top: 120px; width: 520px; height: 520px;"></div>
  <div class="orb orb-cyan" style="right: -60px; bottom: 100px; width: 480px; height: 480px;"></div>

  <div class="top-bar">
    <img class="logo" src="${logoBase64}" alt="Cooca">
    <div class="tag">ANATOMI MASALAH</div>
  </div>

  <div class="header-box">
    <div class="over">DIAGNOSA OPERASIONAL TOKO</div>
    <h1>3 TITIK RAWAN KEBOCORAN <span class="hl-cyan">DI MEJA KASIR MANUAL</span></h1>
    <p>Ketahui di mana uang tunai dan barang modal toko Anda hilang setiap hari tanpa disadari.</p>
  </div>

  <div class="s2-stage">
    <!-- Left 3D Retail Miniature Asset -->
    <div class="s2-3d-shop-wrap">
      <img src="${cutoutShopBase64}" alt="3D Retail Boutique">
    </div>

    <!-- Right Stacked Cause Cards (Zero Overlap) -->
    <div class="s2-stacked-causes">
      <div class="s2-cause-card">
        <div class="card-icon"><img src="${iconPieBase64}" alt="Pie"></div>
        <div class="card-body">
          <h4>1. Transaksi Tunai Terlewat</h4>
          <p>Kasir melayani pembeli saat toko ramai tanpa input sistem. Uang kembalian rawan salah hitung.</p>
        </div>
      </div>
      <div class="s2-cause-card">
        <div class="card-icon"><img src="${iconTargetBase64}" alt="Target"></div>
        <div class="card-body">
          <h4>2. Void &amp; Hapus Menu Tanpa Izin</h4>
          <p>Transaksi dibatalkan setelah pelanggan keluar toko, uang kas tidak masuk ke pembukuan.</p>
        </div>
      </div>
      <div class="s2-cause-card">
        <div class="card-icon"><img src="${iconBagBase64}" alt="Bag"></div>
        <div class="card-body">
          <h4>3. Selisih Stok Fisik vs Catatan</h4>
          <p>Barang keluar tanpa mutasi otomatis sehingga opname akhir bulan selalu minus modal.</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Action CTA Bar -->
  <div class="cta-action-bar">
    <div class="bar-left">
      <img src="${iconGrowthBase64}" alt="Audit">
      <div class="bar-text">Solusi: Ganti catatan manual dengan Kasir POS Pintar</div>
    </div>
    <div class="bar-btn">
      <span>Pelajari Solusinya</span>
      <svg class="icon"><use href="#i-arrow"/></svg>
    </div>
  </div>

  <div class="foot">
    <div class="identity">Toko Kamu &bull; Didukung oleh Cooca Indonesia</div>
    <div class="pg">02 / 05</div>
  </div>
</section>

<!-- ========================================================
     SLIDE 3: 1:1 SQUARE (1080 x 1080) - REAL LIVE SCREEN UI POS
     ======================================================== -->
<section class="slide" id="slide-03">
  <div class="grain"></div>
  <div class="orb orb-cyan" style="left: -80px; top: 120px; width: 520px; height: 520px;"></div>
  <div class="orb orb-blue" style="right: -60px; bottom: 100px; width: 480px; height: 480px;"></div>

  <div class="top-bar">
    <img class="logo" src="${logoBase64}" alt="Cooca">
    <div class="tag">SOLUSI TEKNOLOGI</div>
  </div>

  <div class="header-box">
    <div class="over">KASIR POS PINTAR KILAT</div>
    <h1>TRANSAKSI KILAT &lt; 3 DETIK, <span class="hl-cyan">ANTI-SALAH HITUNG</span></h1>
    <p>Layar kasir intuitif Cooca mendukung scan barcode, split payment QRIS, dan struk WhatsApp otomatis.</p>
  </div>

  <div class="s3-stage">
    <!-- Real Live Screenshot in Browser Mockup Window -->
    <div class="s3-mockup-window">
      <div class="s3-window-header">
        <div class="s3-dot s3-dot-red"></div>
        <div class="s3-dot s3-dot-yellow"></div>
        <div class="s3-dot s3-dot-green"></div>
        <span class="s3-window-title">cooca.id/pos - Kasir POS Toko Kamu (Real Live Application)</span>
      </div>
      <div class="s3-window-body">
        <img src="${screenPosBase64}" alt="Real Live Cooca POS UI">
      </div>
    </div>

    <!-- Floating Metric Badges -->
    <div class="s3-badge-floating" style="left: 24px; top: 72px;">
      <img src="${iconCartBase64}" alt="Cart">
      <span>Kecepatan Checkout: 2.8 Detik</span>
    </div>

    <div class="s3-badge-floating" style="right: 24px; bottom: 120px;">
      <img src="${iconStarBase64}" alt="Star">
      <span>QRIS, Debit &amp; Tunai Auto-Rekon</span>
    </div>
  </div>

  <!-- Action CTA Bar -->
  <div class="cta-action-bar">
    <div class="bar-left">
      <img src="${iconShopBase64}" alt="Shop">
      <div class="bar-text">Bisa dijalankan di Tablet, HP Android, &amp; Laptop Toko Kamu</div>
    </div>
    <div class="bar-btn">
      <span>Coba Kasir Gratis</span>
      <svg class="icon"><use href="#i-arrow"/></svg>
    </div>
  </div>

  <div class="foot">
    <div class="identity">Toko Kamu &bull; Didukung oleh Cooca Indonesia</div>
    <div class="pg">03 / 05</div>
  </div>
</section>

<!-- ========================================================
     SLIDE 4: 1:1 SQUARE (1080 x 1080) - FINANCIAL AUDIT & ROLES
     ======================================================== -->
<section class="slide" id="slide-04">
  <div class="grain"></div>
  <div class="orb orb-blue" style="left: -60px; top: 120px; width: 500px; height: 500px;"></div>
  <div class="orb orb-cyan" style="right: 20px; bottom: 80px; width: 480px; height: 480px;"></div>

  <div class="top-bar">
    <img class="logo" src="${logoBase64}" alt="Cooca">
    <div class="tag">TATA KELOLA BISNIS</div>
  </div>

  <div class="header-box">
    <div class="over">HAK AKSES &amp; AUDIT LOG</div>
    <h1>KASIR FOKUS MELAYANI, <span class="hl-cyan">OWNER TENANG MEMANTAU</span></h1>
    <p>Kunci fitur sensitif dengan Role-Based Access Control. Pembatalan struk wajib PIN persetujuan pemilik.</p>
  </div>

  <div class="s4-stage">
    <!-- Real Live Screenshot Financial Reports -->
    <div class="s4-left-preview">
      <div class="s3-window-header">
        <div class="s3-dot s3-dot-red"></div>
        <div class="s3-dot s3-dot-yellow"></div>
        <div class="s3-dot s3-dot-green"></div>
        <span class="s3-window-title">cooca.id/reports - Laba Rugi Real-Time</span>
      </div>
      <div class="s3-window-body">
        <img src="${screenFinanceBase64}" alt="Real Live Cooca Reports UI">
      </div>
    </div>

    <!-- Right Security Feature Cards -->
    <div class="s4-right-features">
      <div class="s4-feature-box">
        <div class="box-head">
          <img src="${iconBankBase64}" alt="Bank">
          <h4>Rekap Kasir Per Shift</h4>
        </div>
        <p>Uang modal kasir dan uang masuk tunai/non-tunai terkunci otomatis setiap pergantian jam kerja.</p>
      </div>

      <div class="s4-feature-box">
        <div class="box-head">
          <img src="${iconTargetBase64}" alt="Target">
          <h4>Proteksi Anti-Void Kasir</h4>
        </div>
        <p>Kasir dilarang membatalkan transaksi tanpa otorisasi PIN Supervisor. Setiap klik tercatat rapi di audit log.</p>
      </div>

      <div class="s4-feature-box">
        <div class="box-head">
          <img src="${iconPieBase64}" alt="Pie">
          <h4>Laba Bersih Otomatis</h4>
        </div>
        <p>HPP barang langsung dihitung otomatis saat produk terjual. Pantau margin bersih toko langsung dari HP.</p>
      </div>
    </div>
  </div>

  <!-- Action CTA Bar -->
  <div class="cta-action-bar">
    <div class="bar-left">
      <img src="${iconTrophyBase64}" alt="Trophy">
      <div class="bar-text">100% Bebas Selisih Kas &amp; Transparan Setiap Hari</div>
    </div>
    <div class="bar-btn">
      <span>Lihat Fitur Keamanan</span>
      <svg class="icon"><use href="#i-arrow"/></svg>
    </div>
  </div>

  <div class="foot">
    <div class="identity">Toko Kamu &bull; Didukung oleh Cooca Indonesia</div>
    <div class="pg">04 / 05</div>
  </div>
</section>

<!-- ========================================================
     SLIDE 5: 1:1 SQUARE (1080 x 1080) - HIGH-CONVERTING CTA
     ======================================================== -->
<section class="slide" id="slide-05">
  <div class="grain"></div>
  <div class="orb orb-cyan" style="left: -100px; top: 120px; width: 550px; height: 550px;"></div>
  <div class="orb orb-blue" style="right: -60px; bottom: 80px; width: 500px; height: 500px;"></div>

  <div class="top-bar">
    <img class="logo" src="${logoBase64}" alt="Cooca">
    <div class="tag">MULAI SEKARANG</div>
  </div>

  <div class="header-box">
    <div class="over">TRANSFORMASI DIGITAL TOKO KAMU</div>
    <h1>SAATNYA AMANKAN KAS &amp; OMZET <span class="hl-cyan">TOKO KAMU HARI INI</span></h1>
    <p>Bergabunglah dengan ribuan pemilik usaha ritel yang mengelola operasional rapi bersama Cooca Indonesia.</p>
  </div>

  <div class="s5-stage">
    <!-- Real Human Cutout Businessman -->
    <div class="s5-human-wrap">
      <img class="human-photo" src="${cutoutManBase64}" alt="Pebisnis Sukses Toko Kamu">
      <div class="s5-persona-tag">
        <img class="badge-icon" src="${iconStarBase64}" alt="Verified">
        <span>Pengusaha Ritel Maju</span>
      </div>
    </div>

    <!-- High-Converting Offer Card -->
    <div class="s5-offer-card">
      <div class="s5-offer-top">
        <span class="s5-offer-badge">UJI coba gratis</span>
        <img class="trophy-badge" src="${iconTrophyBase64}" alt="Trophy">
      </div>
      <div class="s5-offer-title">Siap Bikin Toko Kamu Naik Kelas?</div>
      
      <div class="s5-checklist">
        <div class="s5-check-item">
          <img class="check-icon" src="${iconStarBase64}" alt="Check">
          <span>Setup kilat dalam 5 menit langsung siap jualan</span>
        </div>
        <div class="s5-check-item">
          <img class="check-icon" src="${iconStarBase64}" alt="Check">
          <span>Tanpa perlu kartu kredit &amp; tanpa biaya tersembunyi</span>
        </div>
        <div class="s5-check-item">
          <img class="check-icon" src="${iconStarBase64}" alt="Check">
          <span>Bisa dipakai di HP, Tablet, Komputer kasir toko</span>
        </div>
        <div class="s5-check-item">
          <img class="check-icon" src="${iconStarBase64}" alt="Check">
          <span>Didukung penuh tim customer success Cooca Indonesia</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Grand CTA Action Bar -->
  <div class="cta-action-bar" style="background: linear-gradient(135deg, #2563EB 0%, #1E3A8A 100%); color: #FFFFFF;">
    <div class="bar-left">
      <img src="${iconRocketBase64}" alt="Rocket" style="width:38px; height:38px; object-fit:contain;">
      <div class="bar-text" style="color:#FFFFFF;">Daftar Akun Gratis Sekarang di cooca.id</div>
    </div>
    <div class="bar-btn" style="background:#FFFFFF; color:#1E3A8A;">
      <span>Mulai Gratis</span>
      <svg class="icon"><use href="#i-arrow"/></svg>
    </div>
  </div>

  <div class="foot">
    <div class="identity">Toko Kamu &bull; Didukung oleh Cooca Indonesia</div>
    <div class="pg">05 / 05</div>
  </div>
</section>

</body>
</html>
`;
}

async function renderCarouselSlides() {
  const htmlContent = buildCarouselHtml();
  const htmlPath = path.resolve('scripts/social-automation/carousel_showcase.html');
  fs.writeFileSync(htmlPath, htmlContent, 'utf8');
  console.log(`[HTML] Carousel template written to ${htmlPath}`);

  // Create target directories
  const targetDir1 = path.resolve('storage/app/public/admin/social-media-assets/carousel');
  const targetDir2 = path.resolve('public/admin/social-media-assets/carousel');
  if (!fs.existsSync(targetDir1)) fs.mkdirSync(targetDir1, { recursive: true });
  if (!fs.existsSync(targetDir2)) fs.mkdirSync(targetDir2, { recursive: true });

  const browser = await chromium.launch({
    executablePath: 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
    headless: true
  });

  const page = await browser.newPage({
    viewport: { width: 1200, height: 1200 },
    deviceScaleFactor: 1
  });

  console.log('[Playwright] Loading carousel HTML...');
  await page.goto(`file://${htmlPath}`, { waitUntil: 'networkidle', timeout: 35000 });
  await page.waitForTimeout(2000); // Wait for fonts and drop shadows

  const slideIds = ['slide-01', 'slide-02', 'slide-03', 'slide-04', 'slide-05'];
  const renderedFiles = [];

  for (let i = 0; i < slideIds.length; i++) {
    const id = slideIds[i];
    const fileName = `${id}.png`;
    const outPath1 = path.join(targetDir1, fileName);
    const outPath2 = path.join(targetDir2, fileName);

    const slideElement = await page.$(`#${id}`);
    if (!slideElement) {
      console.error(`[Playwright Error] Slide element #${id} not found!`);
      continue;
    }

    console.log(`[Playwright] Capturing Slide ${i + 1}/${slideIds.length} (#${id})...`);
    await slideElement.screenshot({
      path: outPath1,
      type: 'png'
    });

    // Copy to public target directory for instant web preview
    fs.copyFileSync(outPath1, outPath2);
    renderedFiles.push({
      slide_index: i + 1,
      id,
      file_name: fileName,
      storage_path: outPath1,
      public_path: outPath2,
      relative_path: `admin/social-media-assets/carousel/${fileName}`
    });
  }

  await browser.close();
  console.log(`[Playwright] Successfully rendered ${renderedFiles.length} carousel slides!`);

  // Write catalog metadata for production deployment
  const metaPath = path.resolve('storage/app/public/admin/social-media-assets/carousel/metadata.json');
  fs.writeFileSync(metaPath, JSON.stringify(renderedFiles, null, 2), 'utf8');
  console.log(`[Metadata] Saved carousel metadata to ${metaPath}`);

  return renderedFiles;
}

if (require.main === module) {
  renderCarouselSlides().catch(err => {
    console.error('[Error] Rendering carousel failed:', err);
    process.exit(1);
  });
}

module.exports = { renderCarouselSlides, buildCarouselHtml };
