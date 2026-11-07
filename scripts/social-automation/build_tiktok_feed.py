#!/usr/bin/env python3
"""
BUILDER & RENDERER: COOCA TIKTOK FEED (1080 x 1080 px)
Format: 1:1 Square Feed Photo
Standard: cooca-feed-design & cooca-carousel-engine
Strict Rule: Zero Overlap, Zero Cut-Off, Zero Emoji, Zero Em/En Dash
"""
import os
import base64
from pathlib import Path

def get_base64_data_uri(file_path):
    with open(file_path, "rb") as f:
        data = f.read()
    ext = Path(file_path).suffix.replace(".", "").lower()
    if ext == "jpg":
        ext = "jpeg"
    b64 = base64.b64encode(data).decode("utf-8")
    return f"data:image/{ext};base64,{b64}"

def build_tiktok_feed_html():
    root = Path(r"c:\laragon\www\cooca_core")
    logo_path = root / "public" / "assets" / "image" / "1785229034_logo_light.png"
    ss_path = root / "storage" / "app" / "public" / "content-assets" / "screenshots" / "fraud_prevention-desktop.png"
    cutout_path = root / "public" / "storage" / "admin" / "social-media-assets" / "pinterest-showcase" / "cutout_3d_shop.png"

    logo_uri = get_base64_data_uri(logo_path)
    ss_uri = get_base64_data_uri(ss_path)
    cutout_uri = get_base64_data_uri(cutout_path)

    html = f"""<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Cooca TikTok Feed 1:1 Square (1080x1080) - Anti Tumpang Tindih</title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Bricolage+Grotesque:opsz,wght@12..96,700;12..96,800&display=swap');

:root {{
  --navy-950: #050B1F;
  --navy-900: #0A1433;
  --navy-800: #0E1F4D;
  --navy-700: #162B68;
  --blue-600: #1E3A8A;
  --blue-500: #2563EB;
  --cyan-400: #38BDF8;
  --cyan-300: #7DD3FC;
  --emerald-400: #10B981;
  --amber-400: #F59E0B;
  --tiktok-red: #FE2C55;
  --paper: #FFFFFF;
  --mist: #EEF3FF;
  --pad-x: 56px;
}}

* {{ box-sizing: border-box; margin: 0; padding: 0; }}

body {{
  background: #090b12;
  font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  -webkit-font-smoothing: antialiased;
}}

/* ========================================================
   1:1 SQUARE TIKTOK FEED CANVAS (1080 x 1080 px)
   ======================================================== */
.feed-canvas {{
  position: relative;
  width: 1080px;
  height: 1080px;
  overflow: hidden;
  color: #FFFFFF;
  background: linear-gradient(150deg, #050B1F 0%, #0A1433 45%, #162B68 100%);
  flex: none;
}}

.grain {{
  position: absolute;
  inset: 0;
  opacity: 0.045;
  pointer-events: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='200' height='200'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.85' numOctaves='2' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
  z-index: 1;
}}

.ambient-orb-1 {{
  position: absolute;
  width: 600px;
  height: 600px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(37, 99, 235, 0.28) 0%, rgba(37, 99, 235, 0) 70%);
  top: -150px;
  right: -100px;
  pointer-events: none;
  filter: blur(40px);
  z-index: 1;
}}

.ambient-orb-2 {{
  position: absolute;
  width: 500px;
  height: 500px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(56, 189, 248, 0.20) 0%, rgba(56, 189, 248, 0) 70%);
  bottom: 50px;
  left: -120px;
  pointer-events: none;
  filter: blur(50px);
  z-index: 1;
}}

/* Top Navigation Bar */
.top-bar {{
  position: absolute;
  left: var(--pad-x);
  right: var(--pad-x);
  top: 36px;
  height: 42px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  z-index: 10;
}}

.logo {{
  height: 38px;
  width: auto;
  object-fit: contain;
}}

.top-badges {{
  display: flex;
  align-items: center;
  gap: 10px;
}}

.tag-tiktok {{
  font-size: 13px;
  font-weight: 800;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  padding: 6px 14px;
  border-radius: 999px;
  color: #FFFFFF;
  background: var(--tiktok-red);
  box-shadow: 0 4px 15px rgba(254, 44, 85, 0.4);
}}

.tag-viral {{
  font-size: 13px;
  font-weight: 700;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  padding: 6px 14px;
  border-radius: 999px;
  border: 1.5px solid rgba(56, 189, 248, 0.4);
  color: var(--cyan-300);
  background: rgba(14, 31, 77, 0.6);
  backdrop-filter: blur(10px);
}}

/* Header Area (y = 80 - 240px) */
.header-area {{
  position: absolute;
  left: var(--pad-x);
  right: var(--pad-x);
  top: 92px;
  z-index: 10;
}}

.overline {{
  font-size: 14px;
  font-weight: 800;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: var(--cyan-400);
  margin-bottom: 8px;
  display: flex;
  align-items: center;
  gap: 8px;
}}

.overline-dot {{
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: var(--cyan-400);
  box-shadow: 0 0 10px var(--cyan-400);
}}

.headline {{
  font-family: 'Bricolage Grotesque', sans-serif;
  font-size: 38px;
  font-weight: 800;
  line-height: 1.18;
  letter-spacing: -0.02em;
  color: #FFFFFF;
  margin-bottom: 8px;
}}

.headline span {{
  background: linear-gradient(135deg, #FFFFFF 30%, #7DD3FC 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}}

.subtitle {{
  font-size: 20px;
  font-weight: 500;
  line-height: 1.35;
  color: #94A3B8;
  max-width: 960px;
}}

/* ========================================================
   HERO STAGE AREA (top: 260px s/d 760px)
   ======================================================== */
.hero-stage {{
  position: absolute;
  left: var(--pad-x);
  right: var(--pad-x);
  top: 260px;
  height: 490px;
  display: grid;
  grid-template-columns: 590px 1fr;
  gap: 22px;
  z-index: 10;
}}

/* Left Window Mockup */
.mockup-window {{
  position: relative;
  background: #0D1630;
  border: 1.5px solid rgba(255, 255, 255, 0.16);
  border-radius: 24px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  box-shadow: 0 24px 60px rgba(0, 0, 0, 0.45);
}}

.window-header {{
  height: 40px;
  background: rgba(10, 20, 51, 0.95);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  display: flex;
  align-items: center;
  padding: 0 16px;
  gap: 8px;
}}

.dot {{ width: 10px; height: 10px; border-radius: 50%; }}
.dot-red {{ background: #EF4444; }}
.dot-yellow {{ background: #F59E0B; }}
.dot-green {{ background: #10B981; }}

.window-url-bar {{
  margin-left: 12px;
  height: 24px;
  background: rgba(255, 255, 255, 0.07);
  border-radius: 6px;
  display: flex;
  align-items: center;
  padding: 0 12px;
  font-size: 11px;
  font-weight: 600;
  color: #94A3B8;
  letter-spacing: 0.02em;
}}

.screenshot-container {{
  position: relative;
  flex: 1;
  overflow: hidden;
  background: #0F172A;
}}

.screenshot-img {{
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: top left;
}}

.screenshot-floating-badge {{
  position: absolute;
  bottom: 16px;
  left: 16px;
  background: rgba(10, 20, 51, 0.92);
  border: 1.5px solid rgba(56, 189, 248, 0.5);
  border-radius: 999px;
  padding: 8px 18px;
  display: flex;
  align-items: center;
  gap: 10px;
  backdrop-filter: blur(12px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
}}

.badge-pulse {{
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: #10B981;
  box-shadow: 0 0 10px #10B981;
}}

.badge-text {{
  font-size: 13px;
  font-weight: 700;
  color: #FFFFFF;
}}

/* Right Bento Column */
.right-bento {{
  display: flex;
  flex-direction: column;
  gap: 16px;
}}

/* 3D Visual Box */
.card-3d-box {{
  position: relative;
  height: 215px;
  background: linear-gradient(135deg, rgba(22, 43, 104, 0.6) 0%, rgba(10, 20, 51, 0.8) 100%);
  border: 1.5px solid rgba(255, 255, 255, 0.16);
  border-radius: 24px;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
  box-shadow: 0 20px 45px rgba(0, 0, 0, 0.35);
}}

.img-3d-cutout {{
  height: 175px;
  width: auto;
  object-fit: contain;
  filter: drop-shadow(0 15px 25px rgba(0, 0, 0, 0.55));
}}

.card-3d-badge {{
  position: absolute;
  top: 14px;
  left: 16px;
  background: rgba(15, 23, 42, 0.85);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 8px;
  padding: 4px 10px;
  font-size: 11px;
  font-weight: 700;
  color: var(--cyan-300);
  text-transform: uppercase;
}}

/* Feature Metrics Grid */
.metrics-row {{
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
}}

.metric-pill-card {{
  background: rgba(14, 31, 77, 0.7);
  border: 1.5px solid rgba(255, 255, 255, 0.14);
  border-radius: 20px;
  padding: 16px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  backdrop-filter: blur(10px);
}}

.metric-val {{
  font-family: 'Bricolage Grotesque', sans-serif;
  font-size: 26px;
  font-weight: 800;
  line-height: 1.1;
  color: #FFFFFF;
}}

.metric-val.emerald {{ color: #34D399; }}
.metric-val.cyan {{ color: #38BDF8; }}

.metric-lbl {{
  font-size: 12px;
  font-weight: 600;
  color: #94A3B8;
  margin-top: 6px;
  line-height: 1.3;
}}

/* Status Alert Bar */
.status-alert-bar {{
  background: rgba(16, 185, 129, 0.12);
  border: 1.5px solid rgba(16, 185, 129, 0.35);
  border-radius: 16px;
  padding: 14px 18px;
  display: flex;
  align-items: center;
  gap: 12px;
}}

.alert-dot {{
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: #10B981;
  box-shadow: 0 0 10px #10B981;
}}

.alert-text {{
  font-size: 13px;
  font-weight: 700;
  color: #E2E8F0;
  line-height: 1.35;
}}

/* ========================================================
   BOTTOM ACTION / CTA BAR (top: 775px s/d 860px)
   ======================================================== */
.action-bar {{
  position: absolute;
  left: var(--pad-x);
  right: var(--pad-x);
  top: 775px;
  height: 72px;
  background: linear-gradient(135deg, rgba(22, 43, 104, 0.7) 0%, rgba(10, 20, 51, 0.85) 100%);
  border: 1.5px solid rgba(56, 189, 248, 0.3);
  border-radius: 20px;
  padding: 0 24px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  backdrop-filter: blur(15px);
  z-index: 10;
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.35);
}}

.action-text {{
  font-size: 17px;
  font-weight: 700;
  color: #F8FAFC;
}}

.action-button {{
  background: linear-gradient(135deg, #38BDF8 0%, #2563EB 100%);
  color: #FFFFFF;
  font-size: 14px;
  font-weight: 800;
  letter-spacing: 0.04em;
  padding: 12px 24px;
  border-radius: 12px;
  text-transform: uppercase;
  box-shadow: 0 4px 15px rgba(37, 99, 235, 0.4);
}}

/* ========================================================
   FOOTER AREA (top: 1030px s/d 1050px)
   ======================================================== */
.footer-bar {{
  position: absolute;
  left: var(--pad-x);
  right: var(--pad-x);
  top: 1030px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-top: 1px solid rgba(255, 255, 255, 0.12);
  padding-top: 12px;
  font-size: 13px;
  font-weight: 600;
  color: #94A3B8;
  letter-spacing: 0.04em;
  z-index: 10;
}}

.footer-brand {{
  color: #FFFFFF;
  font-weight: 700;
}}
</style>
</head>
<body>

<div class="feed-canvas" id="tiktok-feed">
  <div class="grain"></div>
  <div class="ambient-orb-1"></div>
  <div class="ambient-orb-2"></div>

  <!-- Top Bar -->
  <div class="top-bar">
    <img src="{logo_uri}" class="logo" alt="Cooca Logo">
    <div class="top-badges">
      <div class="tag-tiktok">PRIME TIME FYP HOOK 19:45 WIB</div>
      <div class="tag-viral">VIRAL SCORE: 96.7%</div>
    </div>
  </div>

  <!-- Header Area (y = 80 - 240px) -->
  <div class="header-area">
    <div class="overline">
      <div class="overline-dot"></div>
      KONTROL KASIR &amp; ANTI-FRAUD TOKO
    </div>
    <h1 class="headline">
      3 DETIK LANGSUNG BERES: <span>KASIR ANTI-BOCOR KAS</span>
    </h1>
    <p class="subtitle">
      Kunci otorisasi transaksi sensitif kasir dengan PIN Supervisor dan otomatisasi rekap kas pergantian shift.
    </p>
  </div>

  <!-- Hero Stage Area (y = 260 - 760px) -->
  <div class="hero-stage">
    <!-- Left Column: Real Live App Screenshot Framing -->
    <div class="mockup-window">
      <div class="window-header">
        <div class="dot dot-red"></div>
        <div class="dot dot-yellow"></div>
        <div class="dot dot-green"></div>
        <div class="window-url-bar">cooca.test/roles (Hak Akses Kasir &amp; Supervisor)</div>
      </div>
      <div class="screenshot-container">
        <img src="{ss_uri}" class="screenshot-img" alt="Hak Akses Kasir Cooca Real Screenshot">
        <div class="screenshot-floating-badge">
          <div class="badge-pulse"></div>
          <div class="badge-text">PIN SUPERVISOR: AKTIF</div>
        </div>
      </div>
    </div>

    <!-- Right Column: 3D Visual + Feature Metrics -->
    <div class="right-bento">
      <div class="card-3d-box">
        <div class="card-3d-badge">SISTEM KASIR PINTAR</div>
        <img src="{cutout_uri}" class="img-3d-cutout" alt="3D Retail Shop">
      </div>

      <div class="metrics-row">
        <div class="metric-pill-card">
          <div class="metric-val emerald">&lt; 2.8 Detik</div>
          <div class="metric-lbl">Waktu Transaksi Kasir Anti Lemot</div>
        </div>
        <div class="metric-pill-card">
          <div class="metric-val cyan">100% Aman</div>
          <div class="metric-lbl">Bebas Manipulasi &amp; Void Liar</div>
        </div>
      </div>

      <div class="status-alert-bar">
        <div class="alert-dot"></div>
        <div class="alert-text">
          Rekap shift otomatis terkirim ke WhatsApp owner saat kasir ganti shift.
        </div>
      </div>
    </div>
  </div>

  <!-- Bottom Action / CTA Bar (y = 775 - 860px) -->
  <div class="action-bar">
    <div class="action-text">
      Coba Gratis 14 Hari di cooca.id - Solusi Kasir &amp; Laporan Ritel
    </div>
    <div class="action-button">
      KLIK LINK DI BIO
    </div>
  </div>

  <!-- Footer Area (y = 1030 - 1050px) -->
  <div class="footer-bar">
    <div>Nama Usaha: <span class="footer-brand">Toko Kamu</span></div>
    <div>Didukung oleh <span class="footer-brand">Cooca Indonesia</span></div>
  </div>
</div>

</body>
</html>
"""
    return html

def main():
    html_content = build_tiktok_feed_html()
    out_file = Path(r"c:\laragon\www\cooca_core\scripts\social-automation\tiktok_feed_template.html")
    out_file.write_text(html_content, encoding="utf-8")
    print(f"[SUCCESS] Berhasil menghasilkan HTML TikTok Feed di: {out_file}")

if __name__ == "__main__":
    main()
