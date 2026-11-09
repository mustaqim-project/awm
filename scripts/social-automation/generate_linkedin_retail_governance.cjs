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

const LOGO_PATH = path.resolve('public/assets/image/1785229034_logo_dark.png');
const PINTEREST_ASSETS_DIR = path.resolve('public/admin/social-media-assets/pinterest-showcase');
const SCREENSHOTS_DIR = path.resolve('storage/app/public/content-assets/screenshots');

// Load Assets
const logoBase64 = getBase64Image(LOGO_PATH);
const cutoutShopBase64 = getBase64Image(path.join(PINTEREST_ASSETS_DIR, 'cutout_3d_shop.png'));
const cutoutGrowthBase64 = getBase64Image(path.join(PINTEREST_ASSETS_DIR, 'cutout_3d_growth.png'));

// 3D Icons
const iconBankBase64 = getBase64Image(path.join(PINTEREST_ASSETS_DIR, '3d_bank_building.png'));
const iconGrowthBase64 = getBase64Image(path.join(PINTEREST_ASSETS_DIR, '3d_growth_arrow.png'));
const iconPieBase64 = getBase64Image(path.join(PINTEREST_ASSETS_DIR, '3d_pie_chart.png'));
const iconShopBase64 = getBase64Image(path.join(PINTEREST_ASSETS_DIR, '3d_shop_store.png'));
const iconTargetBase64 = getBase64Image(path.join(PINTEREST_ASSETS_DIR, '3d_target.png'));
const iconTrophyBase64 = getBase64Image(path.join(PINTEREST_ASSETS_DIR, '3d_trophy.png'));

// Real Screen Captures
const screenMultiOutletBase64 = getBase64Image(path.join(SCREENSHOTS_DIR, 'multi_outlet-desktop.png'));
const screenFraudBase64 = getBase64Image(path.join(SCREENSHOTS_DIR, 'fraud_prevention-desktop.png'));
const screenFinanceBase64 = getBase64Image(path.join(SCREENSHOTS_DIR, 'finance-desktop.png'));
const screenPosBase64 = getBase64Image(path.join(SCREENSHOTS_DIR, 'pos-desktop.png'));

function buildCarouselHtml() {
  return `<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Cooca LinkedIn Executive Carousel: B2B Retail Governance & Multi-Cabang</title>
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
  --amber-400: #F59E0B;
  --emerald-400: #2563EB;
  --rose-400: #F43F5E;
  --paper: #FFFFFF;
  --mist: #EEF3FF;
  --ink: #0A1433;
  --radius-lg: 32px;
  --radius-md: 20px;
  --radius-sm: 14px;
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

/* 1080x1080 Square Carousel Slide Standard */
.slide {
  position: relative;
  width: 1080px;
  height: 1080px;
  overflow: hidden;
  color: #FFFFFF;
  background: linear-gradient(150deg, #050B1F 0%, #0A1433 45%, #162B68 100%);
  flex: none;
}

.ambient-glow {
  position: absolute;
  border-radius: 50%;
  filter: blur(100px);
  pointer-events: none;
  opacity: 0.25;
}

.top-bar {
  position: absolute;
  left: var(--pad-x);
  right: var(--pad-x);
  top: 36px;
  height: 44px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  z-index: 10;
}

.logo {
  height: 38px;
  width: auto;
  object-fit: contain;
}

.tag-badge {
  font-size: 13px;
  font-weight: 700;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  padding: 6px 18px;
  border-radius: 999px;
  border: 1px solid rgba(255, 255, 255, 0.22);
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(12px);
  color: #FFFFFF;
}

/* Header Area (y = 80-240px) */
.header-area {
  position: absolute;
  left: var(--pad-x);
  right: var(--pad-x);
  top: 96px;
  z-index: 10;
}

.overline {
  font-size: 13px;
  font-weight: 800;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: var(--cyan-400);
  margin-bottom: 8px;
}

.headline {
  font-family: 'Bricolage Grotesque', sans-serif;
  font-size: 38px;
  font-weight: 800;
  line-height: 1.15;
  color: #FFFFFF;
  letter-spacing: -0.02em;
  margin-bottom: 8px;
  max-width: 950px;
}

.subtitle {
  font-size: 17px;
  font-weight: 500;
  line-height: 1.45;
  color: rgba(255, 255, 255, 0.72);
  max-width: 900px;
}

/* Stage Area (y = 260-760px) */
.stage-area {
  position: absolute;
  left: var(--pad-x);
  right: var(--pad-x);
  top: 260px;
  height: 500px;
  z-index: 10;
}

/* Bottom Bar (y = 780-870px) */
.bottom-bar {
  position: absolute;
  left: var(--pad-x);
  right: var(--pad-x);
  top: 785px;
  height: 85px;
  z-index: 10;
}

/* Footer Area (y = 1030-1050px) */
.footer-bar {
  position: absolute;
  left: var(--pad-x);
  right: var(--pad-x);
  bottom: 30px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 14px;
  font-weight: 600;
  color: rgba(255, 255, 255, 0.45);
  letter-spacing: 0.04em;
  z-index: 10;
}

/* Glassmorphism Cards */
.bento-card {
  background: rgba(14, 31, 77, 0.55);
  border: 1px solid rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(20px);
  border-radius: var(--radius-lg);
  padding: 32px;
  box-shadow: 0 24px 48px -12px rgba(5, 11, 31, 0.6);
}

.screenshot-frame {
  width: 100%;
  height: 100%;
  border-radius: var(--radius-md);
  overflow: hidden;
  border: 1px solid rgba(255, 255, 255, 0.2);
  box-shadow: 0 20px 40px -10px rgba(0,0,0,0.5);
  background: #0D1527;
  display: flex;
  flex-direction: column;
}

.window-header {
  height: 28px;
  background: rgba(255, 255, 255, 0.06);
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  display: flex;
  align-items: center;
  padding: 0 12px;
  gap: 6px;
}

.dot { width: 8px; height: 8px; border-radius: 50%; }
.dot-red { background: #FF5F56; }
.dot-yellow { background: #FFBD2E; }
.dot-green { background: #27C93F; }

.screen-img {
  width: 100%;
  height: calc(100% - 28px);
  object-fit: cover;
  object-position: top center;
}
</style>
</head>
<body>

<!-- =========================================================================
     SLIDE 1: THE CONTRARIAN HOOK (THE EXPANSION PARADOX)
     ========================================================================= -->
<section class="slide" id="slide-01">
  <div class="ambient-glow" style="width: 500px; height: 500px; background: #2563EB; left: -100px; top: -100px;"></div>
  <div class="ambient-glow" style="width: 400px; height: 400px; background: #38BDF8; right: -50px; bottom: 100px;"></div>
  
  <div class="top-bar">
    <img class="logo" src="${logoBase64}" alt="Cooca Logo">
    <div class="tag-badge">B2B Retail Playbook</div>
  </div>

  <div class="header-area">
    <div class="overline">Executive Operational Insight</div>
    <div class="headline">Buka Cabang Makin Banyak, Kenapa Profit Malah Menipis?</div>
    <div class="subtitle">Anatomi 3 kebocoran tersembunyi pada ritel multi-outlet dan cara menguncinya dengan tata kelola terpusat.</div>
  </div>

  <div class="stage-area" style="display: grid; grid-template-columns: 1.1fr 0.9fr; gap: 24px; align-items: center;">
    <div class="bento-card" style="height: 100%; display: flex; flex-direction: column; justify-content: space-between;">
      <div>
        <div style="font-size: 14px; font-weight: 700; color: var(--rose-400); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 8px;">The Multi-Branch Paradox</div>
        <div style="font-size: 24px; font-weight: 800; color: #FFFFFF; line-height: 1.3; margin-bottom: 16px;">Ekspansi gerai fisik tanpa sistem tata kelola adalah jebakan arus kas.</div>
        <div style="font-size: 16px; color: rgba(255,255,255,0.75); line-height: 1.5;">Di gerai pertama, pemilik bisnis mengawasi meja kasir dan rak gudang langsung. Saat gerai bertambah 3 hingga 5 outlet, kontrol personal menjadi mustahil.</div>
      </div>
      
      <div style="background: rgba(255,255,255,0.06); border-radius: var(--radius-md); padding: 18px 24px; display: grid; grid-template-columns: 1fr 1fr; gap: 16px; border: 1px solid rgba(255,255,255,0.1);">
        <div>
          <div style="font-size: 12px; color: rgba(255,255,255,0.6); text-transform: uppercase;">Pertumbuhan Cabang</div>
          <div style="font-size: 32px; font-weight: 800; color: var(--cyan-400);">5 Outlet</div>
          <div style="font-size: 12px; color: var(--cyan-400);">+400% Omzet Kasar</div>
        </div>
        <div>
          <div style="font-size: 12px; color: rgba(255,255,255,0.6); text-transform: uppercase;">Margin Bersih</div>
          <div style="font-size: 32px; font-weight: 800; color: var(--rose-400);">-42%</div>
          <div style="font-size: 12px; color: var(--rose-400);">Bocor Stok & Kas</div>
        </div>
      </div>
    </div>

    <div class="bento-card" style="height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; background: radial-gradient(circle at center, rgba(37,99,235,0.3) 0%, rgba(14,31,77,0.5) 100%);">
      <img src="${cutoutShopBase64}" alt="Retail Store" style="width: 240px; height: 240px; object-fit: contain; margin-bottom: 16px; filter: drop-shadow(0 20px 30px rgba(0,0,0,0.5));">
      <div style="font-size: 18px; font-weight: 800; color: #FFFFFF; margin-bottom: 6px;">3 Titik Buta Operasional Ritel</div>
      <div style="font-size: 14px; color: rgba(255,255,255,0.7); max-width: 320px;">Geser untuk melihat audit kebocoran kasir, transfer stok, dan rekonsiliasi.</div>
    </div>
  </div>

  <div class="bottom-bar">
    <div style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); border-radius: var(--radius-md); padding: 18px 28px; display: flex; align-items: center; justify-content: space-between;">
      <div style="display: flex; align-items: center; gap: 16px;">
        <img src="${iconTargetBase64}" alt="Target" style="width: 44px; height: 44px; object-fit: contain;">
        <div>
          <div style="font-size: 16px; font-weight: 700; color: #FFFFFF;">Target Utama: Zero Financial & Stock Leakage</div>
          <div style="font-size: 13px; color: rgba(255,255,255,0.65);">Membangun benteng tata kelola otomatis dengan sistem Cooca Enterprise.</div>
        </div>
      </div>
      <div style="font-size: 14px; font-weight: 700; color: var(--cyan-400);">Geser Slide &rarr;</div>
    </div>
  </div>

  <div class="footer-bar">
    <div>Dokumen Tata Kelola Ritel Cooca</div>
    <div>Slide 1 dari 7</div>
  </div>
</section>

<!-- =========================================================================
     SLIDE 2: BLIND SPOT 1 - OTORISASI KASIR & VOID LIAR
     ========================================================================= -->
<section class="slide" id="slide-02">
  <div class="ambient-glow" style="width: 450px; height: 450px; background: #F43F5E; left: -50px; top: 100px; opacity: 0.15;"></div>
  
  <div class="top-bar">
    <img class="logo" src="${logoBase64}" alt="Cooca Logo">
    <div class="tag-badge">Titik Buta 1: Kasir</div>
  </div>

  <div class="header-area">
    <div class="overline" style="color: var(--rose-400);">Resiko Fraud Internal Meja Kasir</div>
    <div class="headline">Ketika Kasir Memiliki Wewenang Setara Supervisor</div>
    <div class="subtitle">Pembatalan struk (void) dan diskon manual tanpa approval PIN memicu kebocoran kas harian.</div>
  </div>

  <div class="stage-area" style="display: grid; grid-template-columns: 1fr 1fr; gap: 28px; align-items: center;">
    <div class="bento-card" style="height: 100%; display: flex; flex-direction: column; justify-content: space-between;">
      <div>
        <div style="display: inline-block; padding: 4px 12px; background: rgba(244,63,94,0.15); border: 1px solid var(--rose-400); border-radius: 999px; font-size: 12px; font-weight: 700; color: var(--rose-400); margin-bottom: 14px;">Modus Fraud Kasir Klasik</div>
        <div style="font-size: 20px; font-weight: 700; color: #FFFFFF; line-height: 1.4; margin-bottom: 14px;">Kasir menerima pembayaran tunai dari pelanggan, lalu membatalkan item dari struk sebelum cetak.</div>
        <div style="font-size: 15px; color: rgba(255,255,255,0.75); line-height: 1.5;">Uang fisik masuk ke saku pribadi, sementara catatan sistem menyatakan transaksi dibatalkan. Tanpa kunci PIN supervisor, audit akhir bulan akan mencatatnya sebagai selisih misterius.</div>
      </div>

      <div style="background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3); border-radius: var(--radius-md); padding: 16px 20px;">
        <div style="font-size: 13px; font-weight: 800; color: var(--emerald-400); text-transform: uppercase; margin-bottom: 4px;">Solusi Cooca RBAC (/roles):</div>
        <div style="font-size: 14px; color: rgba(255,255,255,0.85); line-height: 1.4;">Wewenang kasir dikunci khusus transaksi. Tombol Void dan Diskon wajib meminta PIN otorisasi Supervisor.</div>
      </div>
    </div>

    <div class="screenshot-frame" style="height: 100%; display: flex; flex-direction: column; background: #0E1726; border-radius: 12px; overflow: hidden; border: 1px solid rgba(255,255,255,0.14);">
      <div class="window-header" style="background: #1B2E4B; padding: 12px 16px; display: flex; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.08);">
        <div class="dot dot-red"></div>
        <div class="dot dot-yellow"></div>
        <div class="dot dot-green"></div>
        <div style="font-size: 11px; color: rgba(255,255,255,0.6); margin-left: 10px; font-family: monospace;">cooca.id/roles - Matriks Otorisasi Hak Akses Kasir</div>
      </div>
      <div style="padding: 20px; background: #0E1726; flex: 1; display: flex; flex-direction: column; justify-content: space-between; font-family: 'Plus Jakarta Sans', sans-serif;">
        <div>
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <div>
              <div style="font-size: 13px; font-weight: 700; color: #FFFFFF;">Role Aktif: Kasir Outlet (Cashier)</div>
              <div style="font-size: 11px; color: rgba(255,255,255,0.5);">5 Cabang Terkoneksi &bull; Kebijakan Keamanan Kasir v2.4</div>
            </div>
            <div style="font-size: 10px; font-weight: 700; background: rgba(244,63,94,0.15); color: var(--rose-400); border: 1px solid rgba(244,63,94,0.3); padding: 4px 10px; border-radius: 6px;">RESTRICTED MODE</div>
          </div>

          <div style="display: flex; flex-direction: column; gap: 8px;">
            <div style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.07); border-radius: 8px; padding: 10px 14px; display: flex; align-items: center; justify-content: space-between;">
              <div style="display: flex; align-items: center; gap: 10px;">
                <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: var(--emerald-400);"></span>
                <span style="font-size: 12px; font-weight: 600; color: #FFFFFF;">Input Transaksi Penjualan</span>
              </div>
              <span style="font-size: 10px; font-weight: 700; color: var(--emerald-400); background: rgba(16,185,129,0.12); padding: 2px 8px; border-radius: 4px;">DIIZINKAN</span>
            </div>

            <div style="background: rgba(244,63,94,0.05); border: 1px solid rgba(244,63,94,0.25); border-radius: 8px; padding: 10px 14px; display: flex; align-items: center; justify-content: space-between;">
              <div style="display: flex; align-items: center; gap: 10px;">
                <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: var(--rose-400);"></span>
                <div>
                  <div style="font-size: 12px; font-weight: 600; color: #FFFFFF;">Pembatalan Struk (Void Transaksi)</div>
                  <div style="font-size: 10px; color: rgba(255,255,255,0.5);">Memerlukan verifikasi PIN Supervisor fisik</div>
                </div>
              </div>
              <span style="font-size: 10px; font-weight: 700; color: var(--rose-400); background: rgba(244,63,94,0.15); padding: 2px 8px; border-radius: 4px; display: flex; align-items: center; gap: 4px;">LOCK PIN</span>
            </div>

            <div style="background: rgba(245,158,11,0.05); border: 1px solid rgba(245,158,11,0.25); border-radius: 8px; padding: 10px 14px; display: flex; align-items: center; justify-content: space-between;">
              <div style="display: flex; align-items: center; gap: 10px;">
                <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: var(--amber-400);"></span>
                <div>
                  <div style="font-size: 12px; font-weight: 600; color: #FFFFFF;">Diskon Manual Kasir</div>
                  <div style="font-size: 10px; color: rgba(255,255,255,0.5);">Maksimal limit 5%, selebihnya butuh PIN</div>
                </div>
              </div>
              <span style="font-size: 10px; font-weight: 700; color: var(--amber-400); background: rgba(245,158,11,0.15); padding: 2px 8px; border-radius: 4px;">LIMIT 5%</span>
            </div>

            <div style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.07); border-radius: 8px; padding: 10px 14px; display: flex; align-items: center; justify-content: space-between;">
              <div style="display: flex; align-items: center; gap: 10px;">
                <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: var(--rose-400);"></span>
                <span style="font-size: 12px; font-weight: 600; color: #FFFFFF;">Ubah Harga Jual Satuan</span>
              </div>
              <span style="font-size: 10px; font-weight: 700; color: #94A3B8; background: rgba(255,255,255,0.08); padding: 2px 8px; border-radius: 4px;">DITOLAK</span>
            </div>
          </div>
        </div>

        <div style="background: rgba(37,99,235,0.1); border: 1px dashed rgba(56,189,248,0.3); border-radius: 8px; padding: 10px 14px; display: flex; align-items: center; gap: 10px;">
          <span style="font-size: 16px;">🛡️</span>
          <div style="font-size: 11px; color: var(--cyan-300); line-height: 1.4;"><strong>Audit Log Aktif:</strong> Setiap percobaan void dicatat dengan timestamp cabang &amp; identitas supervisor.</div>
        </div>
      </div>
    </div>
  </div>

  <div class="bottom-bar">
    <div style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); border-radius: var(--radius-md); padding: 18px 28px; display: flex; align-items: center; justify-content: space-between;">
      <div style="font-size: 15px; color: rgba(255,255,255,0.8);"><strong>Dampak Nyata:</strong> Selisih kasir Rp 35.000 per shift di 5 cabang = <span style="color: var(--rose-400); font-weight: 800;">Rp 78 Juta per tahun</span> uang hilang.</div>
      <div style="font-size: 13px; color: var(--cyan-400); font-weight: 700;">Fitur: /roles</div>
    </div>
  </div>

  <div class="footer-bar">
    <div>Titik Buta 1: Hak Akses Kasir</div>
    <div>Slide 2 dari 7</div>
  </div>
</section>

<!-- =========================================================================
     SLIDE 3: BLIND SPOT 2 - PHANTOM INVENTORY TRANSFER GUDANG
     ========================================================================= -->
<section class="slide" id="slide-03">
  <div class="ambient-glow" style="width: 450px; height: 450px; background: #38BDF8; right: -50px; top: 120px; opacity: 0.18;"></div>
  
  <div class="top-bar">
    <img class="logo" src="${logoBase64}" alt="Cooca Logo">
    <div class="tag-badge">Titik Buta 2: Logistik</div>
  </div>

  <div class="header-area">
    <div class="overline" style="color: var(--cyan-400);">Distribusi Antar-Cabang</div>
    <div class="headline">Misteri "Phantom Inventory" Antara Gudang & Cabang</div>
    <div class="subtitle">Stok di sistem tertera 50 unit, namun fisik di rak cabang kosong. Ke mana barang menguap?</div>
  </div>

  <div class="stage-area" style="display: grid; grid-template-columns: 1fr 1fr; gap: 28px; align-items: center;">
    <div class="screenshot-frame" style="height: 100%;">
      <div class="window-header">
        <div class="dot dot-red"></div>
        <div class="dot dot-yellow"></div>
        <div class="dot dot-green"></div>
        <div style="font-size: 11px; color: rgba(255,255,255,0.5); margin-left: 8px;">cooca.id/warehouse - Transfer Multi-Gudang Terverifikasi</div>
      </div>
      <img class="screen-img" src="${screenMultiOutletBase64}" alt="Cooca Multi Outlet">
    </div>

    <div class="bento-card" style="height: 100%; display: flex; flex-direction: column; justify-content: space-between;">
      <div>
        <div style="display: inline-block; padding: 4px 12px; background: rgba(56,189,248,0.15); border: 1px solid var(--cyan-400); border-radius: 999px; font-size: 12px; font-weight: 700; color: var(--cyan-400); margin-bottom: 14px;">Akar Masalah: Transfer Sepihak</div>
        <div style="font-size: 20px; font-weight: 700; color: #FFFFFF; line-height: 1.4; margin-bottom: 14px;">Gudang pusat mengurangi stok 100 unit, tetapi cabang tujuan hanya menerima 92 unit fisik.</div>
        <div style="font-size: 15px; color: rgba(255,255,255,0.75); line-height: 1.5;">Tanpa status transit dan tanda tangan digital penerimaan dua arah, selisih 8 unit hilang tanpa jejak pertanggungjawaban antara sopir pengantar dan staf toko.</div>
      </div>

      <div style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); border-radius: var(--radius-md); padding: 16px 20px;">
        <div style="font-size: 13px; font-weight: 800; color: var(--cyan-400); text-transform: uppercase; margin-bottom: 4px;">Alur Handshake Cooca (/warehouse):</div>
        <div style="font-size: 14px; color: rgba(255,255,255,0.85); line-height: 1.4;">Kirim Gudang &rarr; Status Dalam Perjalanan &rarr; Konfirmasi Fisik Terima Cabang. Selisih langsung memicu alert audit otomatis.</div>
      </div>
    </div>
  </div>

  <div class="bottom-bar">
    <div style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); border-radius: var(--radius-md); padding: 18px 28px; display: flex; align-items: center; justify-content: space-between;">
      <div style="font-size: 15px; color: rgba(255,255,255,0.8);"><strong>Prinsip Tata Kelola:</strong> Jangan anggap transfer selesai sebelum cabang tujuan menandatangani sistem.</div>
      <div style="font-size: 13px; color: var(--cyan-400); font-weight: 700;">Fitur: /warehouse</div>
    </div>
  </div>

  <div class="footer-bar">
    <div>Titik Buta 2: Distribusi Logistik</div>
    <div>Slide 3 dari 7</div>
  </div>
</section>

<!-- =========================================================================
     SLIDE 4: BLIND SPOT 3 - TUTUP SHIFT SALDO TERBUKA
     ========================================================================= -->
<section class="slide" id="slide-04">
  <div class="ambient-glow" style="width: 450px; height: 450px; background: #F59E0B; left: 100px; bottom: 80px; opacity: 0.15;"></div>
  
  <div class="top-bar">
    <img class="logo" src="${logoBase64}" alt="Cooca Logo">
    <div class="tag-badge">Titik Buta 3: Kas Fisik</div>
  </div>

  <div class="header-area">
    <div class="overline" style="color: var(--amber-400);">Rekonsiliasi Kas Shift</div>
    <div class="headline">Menghitung Kasir dengan Saldo Terbuka Mengundang Manipulasi</div>
    <div class="subtitle">Jika staf kasir mengetahui saldo sistem sebelum menghitung uang fisik, audit menjadi tidak independen.</div>
  </div>

  <div class="stage-area" style="display: grid; grid-template-columns: 1fr 1fr; gap: 28px; align-items: center;">
    <div class="bento-card" style="height: 100%; display: flex; flex-direction: column; justify-content: space-between;">
      <div>
        <div style="display: inline-block; padding: 4px 12px; background: rgba(245,158,11,0.15); border: 1px solid var(--amber-400); border-radius: 999px; font-size: 12px; font-weight: 700; color: var(--amber-400); margin-bottom: 14px;">The Open Settlement Risk</div>
        <div style="font-size: 20px; font-weight: 700; color: #FFFFFF; line-height: 1.4; margin-bottom: 14px;">Layar kasir menampilkan: "Total Uang Masuk: Rp 3.450.000".</div>
        <div style="font-size: 15px; color: rgba(255,255,255,0.75); line-height: 1.5;">Kasir yang memiliki niat tidak baik cukup menyetorkan angka yang sama, sementara kelebihan kas tidak dilaporkan atau kekurangan kas ditutup sementara dari transaksi tanpa struk.</div>
      </div>

      <div style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); border-radius: var(--radius-md); padding: 16px 20px;">
        <div style="font-size: 13px; font-weight: 800; color: var(--amber-400); text-transform: uppercase; margin-bottom: 4px;">SOP Blind Cash Settlement Cooca (/pos):</div>
        <div style="font-size: 14px; color: rgba(255,255,255,0.85); line-height: 1.4;">Kasir wajib memasukkan hitungan fisik tanpa melihat saldo sistem. Selisih lebih atau kurang dihitung otomatis oleh server pusat.</div>
      </div>
    </div>

    <div class="screenshot-frame" style="height: 100%;">
      <div class="window-header">
        <div class="dot dot-red"></div>
        <div class="dot dot-yellow"></div>
        <div class="dot dot-green"></div>
        <div style="font-size: 11px; color: rgba(255,255,255,0.5); margin-left: 8px;">cooca.id/pos - Blind Settlement Kasir Shift</div>
      </div>
      <img class="screen-img" src="${screenPosBase64}" alt="Cooca POS Cashier">
    </div>
  </div>

  <div class="bottom-bar">
    <div style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); border-radius: var(--radius-md); padding: 18px 28px; display: flex; align-items: center; justify-content: space-between;">
      <div style="font-size: 15px; color: rgba(255,255,255,0.8);"><strong>Aturan Emas:</strong> Integritas kas dimulai saat kasir tidak bisa melihat angka target sebelum menghitung fisik.</div>
      <div style="font-size: 13px; color: var(--amber-400); font-weight: 700;">Fitur: /pos</div>
    </div>
  </div>

  <div class="footer-bar">
    <div>Titik Buta 3: Rekonsiliasi Kas</div>
    <div>Slide 4 dari 7</div>
  </div>
</section>

<!-- =========================================================================
     SLIDE 5: FRAMEWORK MATRIKS RBAC
     ========================================================================= -->
<section class="slide" id="slide-05">
  <div class="ambient-glow" style="width: 500px; height: 500px; background: #2563EB; right: 0; top: 0; opacity: 0.2;"></div>
  
  <div class="top-bar">
    <img class="logo" src="${logoBase64}" alt="Cooca Logo">
    <div class="tag-badge">Governance Matrix</div>
  </div>

  <div class="header-area">
    <div class="overline">Matriks Pemisahan Wewenang</div>
    <div class="headline">Standar Matriks Wewenang (RBAC) Ritel Modern</div>
    <div class="subtitle">Sentralisasi kebijakan harga dan stok di pusat, desentralisasi pelayanan cepat di cabang.</div>
  </div>

  <div class="stage-area" style="display: flex; flex-direction: column; gap: 16px;">
    <!-- Level 1 -->
    <div class="bento-card" style="padding: 22px 28px; display: grid; grid-template-columns: 180px 1fr 140px; align-items: center; gap: 20px;">
      <div>
        <div style="font-size: 12px; color: var(--cyan-400); font-weight: 800; text-transform: uppercase;">Peran Kasir Toko</div>
        <div style="font-size: 18px; font-weight: 800; color: #FFFFFF;">Frontline POS</div>
      </div>
      <div style="font-size: 14px; color: rgba(255,255,255,0.75); line-height: 1.4;">
        Input scan barcode penjualan, cetak struk resmi, terima pembayaran tunai/QRIS. <strong>Terkunci mutlak:</strong> ubah harga jual, void transaksi sepihak, edit stok gudang.
      </div>
      <div style="text-align: right;">
        <span style="display: inline-block; padding: 6px 14px; background: rgba(56,189,248,0.15); border: 1px solid var(--cyan-400); border-radius: 999px; font-size: 12px; font-weight: 700; color: var(--cyan-400);">Operasional</span>
      </div>
    </div>

    <!-- Level 2 -->
    <div class="bento-card" style="padding: 22px 28px; display: grid; grid-template-columns: 180px 1fr 140px; align-items: center; gap: 20px;">
      <div>
        <div style="font-size: 12px; color: var(--amber-400); font-weight: 800; text-transform: uppercase;">Peran Kepala Toko</div>
        <div style="font-size: 18px; font-weight: 800; color: #FFFFFF;">Store Supervisor</div>
      </div>
      <div style="font-size: 14px; color: rgba(255,255,255,0.75); line-height: 1.4;">
        Otorisasi PIN untuk retur & void kasir, verifikasi serah-terima kiriman stok masuk, verifikasi setoran blind cash settlement tiap pergantian shift.
      </div>
      <div style="text-align: right;">
        <span style="display: inline-block; padding: 6px 14px; background: rgba(245,158,11,0.15); border: 1px solid var(--amber-400); border-radius: 999px; font-size: 12px; font-weight: 700; color: var(--amber-400);">Verifikasi</span>
      </div>
    </div>

    <!-- Level 3 -->
    <div class="bento-card" style="padding: 22px 28px; display: grid; grid-template-columns: 180px 1fr 140px; align-items: center; gap: 20px; border-color: rgba(16,185,129,0.3); background: rgba(16,185,129,0.06);">
      <div>
        <div style="font-size: 12px; color: var(--emerald-400); font-weight: 800; text-transform: uppercase;">Pemilik / Manajemen</div>
        <div style="font-size: 18px; font-weight: 800; color: #FFFFFF;">Headquarters HQ</div>
      </div>
      <div style="font-size: 14px; color: rgba(255,255,255,0.75); line-height: 1.4;">
        Kendalikan master harga katalog nasional, alokasi pasokan multi-gudang, buka/tutup akses gerai, dan pantau laba rugi konsolidasian seluruh cabang.
      </div>
      <div style="text-align: right;">
        <span style="display: inline-block; padding: 6px 14px; background: rgba(16,185,129,0.15); border: 1px solid var(--emerald-400); border-radius: 999px; font-size: 12px; font-weight: 700; color: var(--emerald-400);">Kendali Penuh</span>
      </div>
    </div>
  </div>

  <div class="bottom-bar">
    <div style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); border-radius: var(--radius-md); padding: 18px 28px; display: flex; align-items: center; justify-content: space-between;">
      <div style="font-size: 15px; color: rgba(255,255,255,0.8);"><strong>Hasil Implementasi:</strong> Mengeliminasi 100% peluang fraud kasir tanpa memperlambat antrean toko.</div>
      <div style="font-size: 13px; color: var(--cyan-400); font-weight: 700;">Standar Cooca RBAC</div>
    </div>
  </div>

  <div class="footer-bar">
    <div>Matriks Tata Kelola Ritel</div>
    <div>Slide 5 dari 7</div>
  </div>
</section>

<!-- =========================================================================
     SLIDE 6: KONSOLIDASI LAPORAN MULTI-CABANG REAL-TIME
     ========================================================================= -->
<section class="slide" id="slide-06">
  <div class="ambient-glow" style="width: 450px; height: 450px; background: #2563EB; left: -50px; top: 120px; opacity: 0.18;"></div>
  
  <div class="top-bar">
    <img class="logo" src="${logoBase64}" alt="Cooca Logo">
    <div class="tag-badge">Executive Control</div>
  </div>

  <div class="header-area">
    <div class="overline" style="color: var(--emerald-400);">Laporan Keuangan Konsolidasian</div>
    <div class="headline">Pantau Seluruh Gerai dari Satu Pintu Layar Terpusat</div>
    <div class="subtitle">Tinggalkan rekap manual spreadsheet yang memakan waktu berhari-hari tiap akhir bulan.</div>
  </div>

  <div class="stage-area" style="display: grid; grid-template-columns: 1.15fr 0.85fr; gap: 28px; align-items: center;">
    <div class="screenshot-frame" style="height: 100%;">
      <div class="window-header">
        <div class="dot dot-red"></div>
        <div class="dot dot-yellow"></div>
        <div class="dot dot-green"></div>
        <div style="font-size: 11px; color: rgba(255,255,255,0.5); margin-left: 8px;">cooca.id/reports - Laporan Konsolidasi Multi-Outlet</div>
      </div>
      <img class="screen-img" src="${screenFinanceBase64}" alt="Cooca Finance Reports">
    </div>

    <div class="bento-card" style="height: 100%; display: flex; flex-direction: column; justify-content: space-between;">
      <div>
        <div style="font-size: 13px; font-weight: 800; color: var(--emerald-400); text-transform: uppercase; margin-bottom: 8px;">Visibilitas Tanpa Batas</div>
        <div style="font-size: 22px; font-weight: 800; color: #FFFFFF; line-height: 1.3; margin-bottom: 16px;">Ambil keputusan pengadaan stok berbasis data perputaran riil.</div>
        <div style="font-size: 14px; color: rgba(255,255,255,0.75); line-height: 1.5;">Anda dapat melihat cabang mana yang menghasilkan margin tertinggi, produk mana yang mengalami dead stock di cabang tertentu, dan berapa kas bersih riil hari ini.</div>
      </div>

      <div style="display: flex; flex-direction: column; gap: 10px;">
        <div style="background: rgba(255,255,255,0.06); padding: 12px 16px; border-radius: var(--radius-sm); display: flex; justify-content: space-between; align-items: center;">
          <span style="font-size: 13px; color: rgba(255,255,255,0.7);">Sinkronisasi Omzet</span>
          <span style="font-size: 14px; font-weight: 800; color: var(--cyan-400);">Real-Time Otomatis</span>
        </div>
        <div style="background: rgba(255,255,255,0.06); padding: 12px 16px; border-radius: var(--radius-sm); display: flex; justify-content: space-between; align-items: center;">
          <span style="font-size: 13px; color: rgba(255,255,255,0.7);">Valuasi Stok Multi-Gudang</span>
          <span style="font-size: 14px; font-weight: 800; color: var(--emerald-400);">Akurasi HPP Terpadu</span>
        </div>
      </div>
    </div>
  </div>

  <div class="bottom-bar">
    <div style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); border-radius: var(--radius-md); padding: 18px 28px; display: flex; align-items: center; justify-content: space-between;">
      <div style="font-size: 15px; color: rgba(255,255,255,0.8);"><strong>Efisiensi Kerja:</strong> Hemat puluhan jam kerja admin pembukuan tiap akhir bulan.</div>
      <div style="font-size: 13px; color: var(--emerald-400); font-weight: 700;">Fitur: /reports</div>
    </div>
  </div>

  <div class="footer-bar">
    <div>Laporan Konsolidasian Eksekutif</div>
    <div>Slide 6 dari 7</div>
  </div>
</section>

<!-- =========================================================================
     SLIDE 7: ROADMAP IMPLEMENTASI & CALL TO ACTION
     ========================================================================= -->
<section class="slide" id="slide-07">
  <div class="ambient-glow" style="width: 500px; height: 500px; background: #2563EB; left: 200px; top: 100px; opacity: 0.25;"></div>
  
  <div class="top-bar">
    <img class="logo" src="${logoBase64}" alt="Cooca Logo">
    <div class="tag-badge">Next Steps</div>
  </div>

  <div class="header-area">
    <div class="overline">Roadmap Implementasi</div>
    <div class="headline">Mulai Tata Kelola Ritel Anda Tanpa Menunggu Cabang Rumit</div>
    <div class="subtitle">Bangun fondasi sistem yang kokoh selagi bisnis Anda bertumbuh.</div>
  </div>

  <div class="stage-area" style="display: grid; grid-template-columns: 1fr 1fr; gap: 28px; align-items: center;">
    <div class="bento-card" style="height: 100%; display: flex; flex-direction: column; justify-content: space-between;">
      <div>
        <div style="font-size: 14px; font-weight: 800; color: var(--cyan-400); text-transform: uppercase; margin-bottom: 12px;">3 Langkah Standarisasi Toko</div>
        
        <div style="display: flex; gap: 14px; margin-bottom: 16px;">
          <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--blue-500); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px; flex: none;">1</div>
          <div>
            <div style="font-size: 16px; font-weight: 700; color: #FFFFFF;">Kunci Wewenang Kasir (/roles)</div>
            <div style="font-size: 13px; color: rgba(255,255,255,0.7);">Batasi wewenang void dan ubah harga hanya untuk supervisor.</div>
          </div>
        </div>

        <div style="display: flex; gap: 14px; margin-bottom: 16px;">
          <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--blue-500); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px; flex: none;">2</div>
          <div>
            <div style="font-size: 16px; font-weight: 700; color: #FFFFFF;">Wajibkan Serah-Terima Stok (/warehouse)</div>
            <div style="font-size: 13px; color: rgba(255,255,255,0.7);">Terapkan konfirmasi digital dua arah saat transfer antar gerai.</div>
          </div>
        </div>

        <div style="display: flex; gap: 14px;">
          <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--blue-500); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px; flex: none;">3</div>
          <div>
            <div style="font-size: 16px; font-weight: 700; color: #FFFFFF;">Pantau Konsolidasi Otomatis (/reports)</div>
            <div style="font-size: 13px; color: rgba(255,255,255,0.7);">Monitor profitabilitas dan arus kas seluruh gerai secara terpusat.</div>
          </div>
        </div>
      </div>

      <div style="font-size: 13px; color: rgba(255,255,255,0.5);">Pondasi yang rapi memberi Anda kebebasan untuk berekspansi ke kota-kota baru.</div>
    </div>

    <div class="bento-card" style="height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; background: radial-gradient(circle at center, rgba(37,99,235,0.35) 0%, rgba(14,31,77,0.65) 100%);">
      <img src="${iconTrophyBase64}" alt="Trophy" style="width: 140px; height: 140px; object-fit: contain; margin-bottom: 16px; filter: drop-shadow(0 16px 24px rgba(0,0,0,0.4));">
      <div style="font-size: 24px; font-weight: 800; color: #FFFFFF; margin-bottom: 8px;">Kendalikan Bisnis Anda</div>
      <div style="font-size: 15px; color: rgba(255,255,255,0.75); max-width: 340px; margin-bottom: 24px;">Gunakan platform ERP multi-cabang yang dirancang untuk kebutuhan nyata ritel Indonesia.</div>
      
      <div style="background: var(--blue-500); border-radius: 999px; padding: 14px 32px; font-size: 16px; font-weight: 800; color: #FFFFFF; letter-spacing: 0.04em; box-shadow: 0 10px 25px rgba(37,99,235,0.5);">
        Mulai di cooca.id
      </div>
      <div style="font-size: 13px; color: rgba(255,255,255,0.6); margin-top: 12px;">Paket Solo Owner Gratis Selamanya (Rp 0)</div>
    </div>
  </div>

  <div class="bottom-bar">
    <div style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); border-radius: var(--radius-md); padding: 18px 28px; display: flex; align-items: center; justify-content: space-between;">
      <div style="font-size: 15px; color: rgba(255,255,255,0.85);">Simpan dokumen ini dan bagikan ke tim manajer operasional gerai Anda.</div>
      <div style="font-size: 14px; color: var(--cyan-400); font-weight: 800;">cooca.id</div>
    </div>
  </div>

  <div class="footer-bar">
    <div>Cooca Enterprise Retail Solution</div>
    <div>Slide 7 dari 7</div>
  </div>
</section>

</body>
</html>`;
}

async function renderCarouselSlides() {
  console.log('[Playwright] Initializing browser for LinkedIn Retail Governance Carousel...');
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({
    viewport: { width: 1080, height: 1080 },
    deviceScaleFactor: 2 // High retina crispness
  });

  const page = await context.newPage();
  const htmlContent = buildCarouselHtml();

  // Save standalone HTML showcase for browser preview
  const showcasePath = path.resolve('public/admin/social-media-assets/carousel/linkedin_retail_governance.html');
  const targetDir1 = path.resolve('storage/app/public/admin/social-media-assets/carousel');
  const targetDir2 = path.resolve('public/admin/social-media-assets/carousel');

  if (!fs.existsSync(targetDir1)) fs.mkdirSync(targetDir1, { recursive: true });
  if (!fs.existsSync(targetDir2)) fs.mkdirSync(targetDir2, { recursive: true });

  fs.writeFileSync(showcasePath, htmlContent, 'utf8');
  console.log(`[Showcase] Saved standalone showcase HTML to ${showcasePath}`);

  await page.setContent(htmlContent, { waitUntil: 'networkidle' });

  const slideIds = [
    'slide-01',
    'slide-02',
    'slide-03',
    'slide-04',
    'slide-05',
    'slide-06',
    'slide-07'
  ];

  const renderedFiles = [];

  for (let i = 0; i < slideIds.length; i++) {
    const id = slideIds[i];
    const fileName = `linkedin_retail_${id}.png`;
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
  console.log(`[Playwright] Successfully rendered ${renderedFiles.length} slides!`);

  // Write catalog metadata
  const metaPath = path.resolve('storage/app/public/admin/social-media-assets/carousel/linkedin_retail_governance_meta.json');
  fs.writeFileSync(metaPath, JSON.stringify(renderedFiles, null, 2), 'utf8');
  console.log(`[Metadata] Saved metadata to ${metaPath}`);

  return renderedFiles;
}

if (require.main === module) {
  renderCarouselSlides().catch(err => {
    console.error('[Error] Rendering carousel failed:', err);
    process.exit(1);
  });
}

module.exports = { renderCarouselSlides, buildCarouselHtml };
