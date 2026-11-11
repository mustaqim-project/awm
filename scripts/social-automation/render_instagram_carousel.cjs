const { chromium } = require('c:/laragon/www/cooca_core/node_modules/@playwright/test');
const fs = require('fs');
const path = require('path');

const ROOT_DIR = 'C:/laragon/www/cooca_core';
const LOGO_DARK_PATH = path.join(ROOT_DIR, 'public/assets/image/1785229034_logo_dark.png');
const PINTEREST_ASSETS_DIR = path.join(ROOT_DIR, 'public/admin/social-media-assets/pinterest-showcase');
const OUTPUT_DIR = path.join(ROOT_DIR, 'storage/app/public/admin/social-media-assets/carousel/instagram');
const ARTIFACT_DIR = path.join('C:/Users/Ulfah Ramadhani/.gemini/antigravity/brain/298fb398-76d9-464f-a599-d4c72850d8a5');

fs.mkdirSync(OUTPUT_DIR, { recursive: true });
fs.mkdirSync(ARTIFACT_DIR, { recursive: true });

function getBase64Image(filePath) {
  if (!fs.existsSync(filePath)) {
    console.warn(`[Asset Warning] File not found: ${filePath}`);
    return '';
  }
  const ext = path.extname(filePath).replace('.', '') || 'png';
  const buf = fs.readFileSync(filePath);
  return `data:image/${ext};base64,${buf.toString('base64')}`;
}

const logoDarkBase64 = getBase64Image(LOGO_DARK_PATH);
const iconBankBase64 = getBase64Image(path.join(PINTEREST_ASSETS_DIR, '3d_bank_building.png'));
const iconShopBase64 = getBase64Image(path.join(PINTEREST_ASSETS_DIR, '3d_shop_store.png'));
const iconTargetBase64 = getBase64Image(path.join(PINTEREST_ASSETS_DIR, '3d_target.png'));
const iconGrowthBase64 = getBase64Image(path.join(PINTEREST_ASSETS_DIR, '3d_growth_arrow.png'));
const iconTrophyBase64 = getBase64Image(path.join(PINTEREST_ASSETS_DIR, '3d_trophy.png'));

const slidesData = [
  {
    slideNumber: 1,
    tag: "SOP KASIR & KEUANGAN",
    badge: "PANDUAN OPERASIONAL RITEL",
    title: "5 Langkah Tutup Kasir Toko Tanpa Selisih Rp 1 Pun",
    subtitle: "SOP Rekonsiliasi Kas Harian 3 Menit untuk Pengusaha Ritel & UMKM",
    iconBase64: iconShopBase64,
    highlightNumber: "Rp 0 SELISIH",
    highlightLabel: "Target Akurasi Kas Harian",
    highlightDesc: "Standarisasi rekonsiliasi kasir untuk mencegah kebocoran halus di meja kasir",
    points: [
      { num: "01", title: "Input dan kunci saldo awal kasir saat toko pertama kali buka" },
      { num: "02", title: "Catat seketika semua pengeluaran kas kecil operasional (petty cash)" },
      { num: "03", title: "Pastikan tidak ada transaksi menggantung saat pergantian shift kasir" },
      { num: "04", title: "Hitung uang fisik dan bandingkan langsung dengan ringkasan sistem kasir" }
    ],
    footerPill: "Geser ke Slide 2: Langkah 1 →"
  },
  {
    slideNumber: 2,
    tag: "LANGKAH 1: SALDO AWAL",
    badge: "MODAL KASIR ANTI-CAMPUR",
    title: "Kunci Saldo Kas Awal Sebelum Transaksi Pertama Dimulai",
    subtitle: "Disiplin modal uang kembalian adalah pondasi pertama akurasi kas toko:",
    iconBase64: iconBankBase64,
    points: [
      {
        num: "01",
        label: "Tetapkan Plafon Modal Tetap",
        desc: "Sediakan nominal modal uang kembalian tetap (misal Rp 200.000) dalam pecahan kecil sebelum toko buka."
      },
      {
        num: "02",
        label: "Kunci Saldo di Aplikasi Kasir",
        desc: "Input saldo kas awal di aplikasi sehingga sistem otomatis memisahkan uang modal dari omzet penjualan harian."
      },
      {
        num: "03",
        label: "Dilarang Satukan dengan Uang Pribadi",
        desc: "Hindari mencampur uang modal kasir dengan dompet pribadi pemilik atau titipan belanja non-toko."
      }
    ],
    footerPill: "Geser ke Slide 3: Langkah 2 →"
  },
  {
    slideNumber: 3,
    tag: "LANGKAH 2: PETTY CASH",
    badge: "CATAT PENGELUARAN KECIL",
    title: "Rekam Seluruh Pengeluaran Operasional Harian Seketika",
    subtitle: "Kebocoran halus sering terjadi dari uang receh yang diambil dari laci kasir tanpa nota:",
    iconBase64: iconTargetBase64,
    points: [
      {
        num: "01",
        label: "Catat Pengeluaran Tak Terduga",
        desc: "Biaya kantong kresek, es batu, parkir supplier, atau kebersihan wajib diinput ke menu kas keluar kasir."
      },
      {
        num: "02",
        label: "Wajib Lampiran Nota Fisik",
        desc: "Setiap pengeluaran kas kecil wajib disertai bon nota fisik yang dimasukkan ke amplop petty cash kasir."
      },
      {
        num: "03",
        label: "Hindari Izin Pengambilan Lisan",
        desc: "Pengambilan uang laci tanpa pencatatan digital seketika otomatis memicu selisih saat tutup shift malam."
      }
    ],
    footerPill: "Geser ke Slide 4: Langkah 3 →"
  },
  {
    slideNumber: 4,
    tag: "LANGKAH 3: DISIPLIN SHIFT",
    badge: "VALIDASI SEBELUM TUKAR SHIFT",
    title: "Selesaikan Transaksi Tertunda Sebelum Shift Berakhir",
    subtitle: "Pastikan meja kasir dalam kondisi bersih dari nota menggantung sebelum diserahkan:",
    iconBase64: iconGrowthBase64,
    points: [
      {
        num: "01",
        label: "Cek Transaksi Hold dan Pending",
        desc: "Selesaikan atau batalkan pesanan yang masih tertunda agar tidak membebani staf shift kasir berikutnya."
      },
      {
        num: "02",
        label: "Rekonsiliasi Non-Tunai dan QRIS",
        desc: "Cocokkan total pembayaran digital (QRIS, kartu debit, transfer) dengan riwayat mutasi transaksi di sistem."
      },
      {
        num: "03",
        label: "Hitung Uang Fisik Bersama",
        desc: "Kasir shift keluar dan kasir shift masuk wajib menghitung uang fisik bersama di depan meja kasir."
      }
    ],
    footerPill: "Geser ke Slide 5: Rekap Otomatis →"
  },
  {
    slideNumber: 5,
    tag: "LANGKAH 4-5: OTOMASI SISTEM",
    badge: "CLOSING KASIR 3 MENIT",
    title: "Cetak Rekap Shift Otomatis Tanpa Kalkulator Manual",
    subtitle: "Selesaikan verifikasi tutup toko dalam hitungan menit bersama ekosistem COOCA:",
    iconBase64: iconTrophyBase64,
    points: [
      {
        num: "01",
        label: "Pencocokan Otomatis 1 Klik",
        desc: "Sistem langsung membandingkan total penjualan tunai, QRIS, dan saldo awal dengan uang fisik kasir."
      },
      {
        num: "02",
        label: "Struk Rekap Shift Instan",
        desc: "Cetak bukti serah terima kasir otomatis berisi rincian kas masuk, kas keluar, dan omzet per shift."
      },
      {
        num: "03",
        label: "Sinkron ke Laporan Laba Rugi",
        desc: "Seluruh data penjualan harian otomatis terhubung ke Laporan Keuangan Real-Time tanpa input manual ulang."
      }
    ],
    ctaBadge: "Mulai Gratis Selamanya di cooca.id",
    footerPill: "Daftar Akun Gratis di cooca.id →"
  }
];

function generateSlideHtml(slide) {
  return `<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
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
  background-color: #050B1F;
  background-image: 
    radial-gradient(circle at 10% 10%, rgba(37, 99, 235, 0.22) 0%, transparent 45%),
    radial-gradient(circle at 90% 90%, rgba(56, 189, 248, 0.16) 0%, transparent 45%),
    linear-gradient(150deg, #050B1F 0%, #0A1433 45%, #162B68 100%);
  font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
  color: #FFFFFF;
  overflow: hidden;
  position: relative;
  padding: 48px 56px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

/* Subtle architectural grid */
body::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-size: 40px 40px;
  background-image: 
    linear-gradient(to right, rgba(255, 255, 255, 0.03) 1px, transparent 1px),
    linear-gradient(to bottom, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
  pointer-events: none;
}

/* Ambient glow orb */
.ambient-glow {
  position: absolute;
  top: 20%;
  right: -5%;
  width: 480px;
  height: 480px;
  background: radial-gradient(circle, rgba(56, 189, 248, 0.15) 0%, rgba(37, 99, 235, 0.05) 50%, transparent 70%);
  border-radius: 50%;
  filter: blur(60px);
  pointer-events: none;
}

/* Header */
.header {
  position: relative;
  z-index: 2;
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

.brand-logo-container {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 6px 14px;
  border-radius: 12px;
  background: transparent;
  
  
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

.slide-number-pill {
  font-size: 13px;
  font-weight: 800;
  color: #FFFFFF;
  background: rgba(37, 99, 235, 0.5);
  border: 1px solid rgba(56, 189, 248, 0.4);
  padding: 6px 16px;
  border-radius: 20px;
  letter-spacing: 0.05em;
}

/* Main Body Content */
.main-content {
  position: relative;
  z-index: 2;
  display: flex;
  flex-direction: column;
  gap: 18px;
  margin-top: 6px;
}

.title-row {
  display: flex;
  align-items: flex-start;
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
  font-size: 38px;
  font-weight: 800;
  line-height: 1.25;
  color: #FFFFFF;
  letter-spacing: -0.02em;
  text-shadow: 0 2px 10px rgba(0, 0, 0, 0.4);
}

.subtitle {
  font-size: 18px;
  font-weight: 500;
  line-height: 1.5;
  color: #94A3B8;
  margin-top: 8px;
}

.corner-3d-icon {
  width: 90px;
  height: 90px;
  object-fit: contain;
  filter: drop-shadow(0 10px 20px rgba(0, 0, 0, 0.45));
  flex-shrink: 0;
}

/* Featured Stat Card (for Slide 1) */
.stat-highlight-box {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 18px 26px;
  border-radius: 18px;
  background: linear-gradient(135deg, rgba(37, 99, 235, 0.25) 0%, rgba(56, 189, 248, 0.2) 100%);
  border: 1.5px solid rgba(56, 189, 248, 0.5);
  box-shadow: 0 10px 30px rgba(37, 99, 235, 0.25);
  backdrop-filter: blur(12px);
  margin-top: 2px;
}

.stat-desc {
  font-size: 18px;
  font-weight: 700;
  color: #FFFFFF;
}

.stat-subdesc {
  font-size: 13px;
  color: #BAE6FD;
  margin-top: 4px;
  font-weight: 500;
}

.stat-number {
  font-size: 44px;
  font-weight: 800;
  letter-spacing: -0.02em;
  color: #38BDF8;
  text-shadow: 0 0 20px rgba(56, 189, 248, 0.5);
}

/* Cards & Points Container */
.cards-container {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-top: 4px;
}

.point-card {
  display: flex;
  align-items: flex-start;
  gap: 16px;
  padding: 16px 20px;
  border-radius: 16px;
  background: rgba(15, 23, 42, 0.7);
  border: 1px solid rgba(255, 255, 255, 0.1);
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.25);
  backdrop-filter: blur(10px);
}

.point-icon {
  width: 32px;
  height: 32px;
  border-radius: 10px;
  background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%);
  color: #FFFFFF;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 13px;
  font-weight: 800;
  flex-shrink: 0;
  margin-top: 2px;
  box-shadow: 0 2px 8px rgba(37, 99, 235, 0.4);
}

.point-content {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.point-label {
  font-size: 16px;
  font-weight: 700;
  color: #38BDF8;
}

.point-text {
  font-size: 15px;
  font-weight: 500;
  line-height: 1.45;
  color: #E2E8F0;
}

/* CTA Hero Banner on Slide 5 */
.cta-banner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 24px;
  border-radius: 16px;
  background: linear-gradient(135deg, rgba(37, 99, 235, 0.35) 0%, rgba(56, 189, 248, 0.25) 100%);
  border: 1.5px solid rgba(56, 189, 248, 0.5);
  box-shadow: 0 8px 24px rgba(37, 99, 235, 0.3);
  margin-top: 4px;
}

.cta-banner-text {
  font-size: 15px;
  font-weight: 700;
  color: #FFFFFF;
}

.cta-banner-badge {
  padding: 8px 18px;
  border-radius: 999px;
  background: #38BDF8;
  color: #050B1F;
  font-size: 13px;
  font-weight: 800;
  letter-spacing: 0.04em;
  text-transform: uppercase;
}

/* Footer */
.footer {
  position: relative;
  z-index: 2;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-top: 1px solid rgba(255, 255, 255, 0.12);
  padding-top: 18px;
}

.footer-brand {
  font-size: 14px;
  font-weight: 700;
  color: #94A3B8;
  display: flex;
  align-items: center;
  gap: 8px;
}

.footer-brand span {
  color: #38BDF8;
}

.footer-pill {
  padding: 10px 24px;
  border-radius: 12px;
  background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%);
  border: 1px solid rgba(56, 189, 248, 0.5);
  color: #FFFFFF;
  font-size: 14px;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 8px;
  box-shadow: 0 4px 16px rgba(37, 99, 235, 0.4);
}

</style>
</head>
<body>

  <div class="ambient-glow"></div>

  <!-- Header -->
  <div class="header">
    <div class="brand-wrapper">
      <div class="brand-logo-container">
        <img class="brand-logo" src="${logoDarkBase64}" alt="COOCA Logo">
      </div>
      <div class="tag-badge">
        <span>●</span> ${slide.tag}
      </div>
    </div>
    <div class="slide-number-pill">
      SLIDE 0${slide.slideNumber} / 05
    </div>
  </div>

  <!-- Main Body Content -->
  <div class="main-content">
    <div class="title-row">
      <div class="title-text-col">
        <div class="overline">${slide.badge}</div>
        <div class="headline">${slide.title}</div>
        <div class="subtitle">${slide.subtitle}</div>
      </div>
      ${slide.iconBase64 ? `<img class="corner-3d-icon" src="${slide.iconBase64}" alt="Icon 3D">` : ''}
    </div>

    ${slide.highlightNumber ? `
    <div class="stat-highlight-box">
      <div>
        <div class="stat-desc">${slide.highlightLabel}</div>
        <div class="stat-subdesc">${slide.highlightDesc}</div>
      </div>
      <div class="stat-number">${slide.highlightNumber}</div>
    </div>
    ` : ''}

    <div class="cards-container">
      ${slide.points.map(pt => `
        <div class="point-card">
          <div class="point-icon">${pt.num}</div>
          <div class="point-content">
            ${pt.label ? `<div class="point-label">${pt.label}</div>` : ''}
            <div class="point-text">${pt.desc || pt.title}</div>
          </div>
        </div>
      `).join('')}
    </div>

    ${slide.ctaBadge ? `
    <div class="cta-banner">
      <div class="cta-banner-text">Kelola kasir toko lebih rapi, tenang, dan anti-selisih bersama COOCA:</div>
      <div class="cta-banner-badge">${slide.ctaBadge}</div>
    </div>
    ` : ''}
  </div>

  <!-- Footer -->
  <div class="footer">
    <div class="footer-brand">
      Sistem Operasional UMKM Modern • <span>cooca.id</span>
    </div>
    <div class="footer-pill">
      ${slide.footerPill}
    </div>
  </div>

</body>
</html>`;
}

async function renderSlides() {
  console.log('Launching Chromium for 5-slide Dark-Tech COOCA Instagram Carousel...');
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({
    viewport: { width: 1080, height: 1080 },
    deviceScaleFactor: 2
  });
  const page = await context.newPage();

  for (const slide of slidesData) {
    const html = generateSlideHtml(slide);
    await page.setContent(html, { waitUntil: 'networkidle' });

    const outName = `slide-0${slide.slideNumber}.png`;
    const targetPath = path.join(OUTPUT_DIR, outName);
    const artifactPath = path.join(ARTIFACT_DIR, `cooca-carousel-instagram-slide-0${slide.slideNumber}.png`);

    await page.screenshot({ path: targetPath, type: 'png' });
    fs.copyFileSync(targetPath, artifactPath);
    console.log(`[Slide ${slide.slideNumber}] Rendered successfully -> ${targetPath} (${fs.statSync(targetPath).size} bytes)`);
  }

  await browser.close();
  console.log('ALL 5 INSTAGRAM CAROUSEL SLIDES GENERATED WITH DARK NAVY & WHITE IDENTITY AND LOGO_DARK.PNG!');
}

renderSlides().catch(err => {
  console.error('Rendering error:', err);
  process.exit(1);
});
