import os
import base64
import asyncio
from playwright.async_api import async_playwright

BASE_DIR = r"C:\laragon\www\cooca_core"

def get_base64_img(rel_path):
    full_path = os.path.join(BASE_DIR, rel_path)
    if not os.path.exists(full_path):
        print(f"Warning: File not found: {full_path}")
        return ""
    ext = os.path.splitext(full_path)[1].replace(".", "") or "png"
    with open(full_path, "rb") as f:
        data = f.read()
    return f"data:image/{ext};base64,{base64.b64encode(data).decode('utf-8')}"

# Assets
logo_base64 = get_base64_img("public/assets/image/1785229034_logo_light.png")
if not logo_base64:
    logo_base64 = get_base64_img("public/assets/image/1785229034_logo_dark.png")

screen_inventory = get_base64_img("storage/app/public/content-assets/screenshots/inventory-desktop.png")
screen_finance = get_base64_img("storage/app/public/content-assets/screenshots/finance-desktop.png")
screen_pos = get_base64_img("storage/app/public/content-assets/screenshots/pos-desktop.png")

icon_pie = get_base64_img("public/admin/social-media-assets/pinterest-showcase/3d_pie_chart.png")
icon_growth = get_base64_img("public/admin/social-media-assets/pinterest-showcase/3d_growth_arrow.png")
icon_target = get_base64_img("public/admin/social-media-assets/pinterest-showcase/3d_target.png")
icon_shop = get_base64_img("public/admin/social-media-assets/pinterest-showcase/3d_shop_store.png")
icon_trophy = get_base64_img("public/admin/social-media-assets/pinterest-showcase/3d_trophy.png")
cutout_shop = get_base64_img("public/admin/social-media-assets/pinterest-showcase/cutout_3d_shop.png")

html_content = f"""<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Cooca B2B Carousel: Dead Stock & Erosi Modal Kerja</title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Bricolage+Grotesque:opsz,wght@12..96,700;12..96,800&display=swap');

:root {{
  --navy-950: #050B1F;
  --navy-900: #0A1433;
  --navy-850: #0D1A3F;
  --navy-800: #102252;
  --navy-700: #162B68;
  --blue-600: #1E3A8A;
  --blue-500: #2563EB;
  --cyan-400: #38BDF8;
  --cyan-300: #7DD3FC;
  --crimson-400: #EF4444;
  --crimson-500: #DC2626;
  --amber-400: #F59E0B;
  --white: #FFFFFF;
  --mist: #EEF3FF;
  --pad-x: 64px;
}}

* {{ box-sizing: border-box; margin: 0; padding: 0; }}

body {{
  background: #090B12;
  font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
  display: flex;
  flex-direction: column;
  gap: 40px;
  align-items: center;
  padding: 40px 0;
  -webkit-font-smoothing: antialiased;
}}

/* 1080x1080 Square Exact Canvas */
.slide {{
  position: relative;
  width: 1080px;
  height: 1080px;
  overflow: hidden;
  color: #FFFFFF;
  background: radial-gradient(circle at 85% 15%, #162B68 0%, #0A1433 55%, #050B1F 100%);
  flex: none;
}}

.ambient-glow {{
  position: absolute;
  border-radius: 50%;
  filter: blur(120px);
  pointer-events: none;
}}

/* Top Bar: y = 48px, height = 44px */
.top-bar {{
  position: absolute;
  left: var(--pad-x);
  right: var(--pad-x);
  top: 48px;
  height: 48px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  z-index: 20;
}}

.logo {{
  height: 40px;
  width: auto;
  object-fit: contain;
}}

.tag-badge {{
  font-size: 13px;
  font-weight: 700;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  padding: 8px 20px;
  border-radius: 999px;
  border: 1px solid rgba(255, 255, 255, 0.2);
  background: rgba(255, 255, 255, 0.06);
  backdrop-filter: blur(16px);
  color: #FFFFFF;
}}

/* Header Area: y = 120px, max-height = 140px */
.header-area {{
  position: absolute;
  left: var(--pad-x);
  right: var(--pad-x);
  top: 120px;
  z-index: 20;
}}

.overline {{
  font-size: 13px;
  font-weight: 800;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: var(--cyan-400);
  margin-bottom: 8px;
}}

.headline {{
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-size: 36px;
  font-weight: 800;
  line-height: 1.22;
  color: #FFFFFF;
  letter-spacing: -0.02em;
  margin-bottom: 8px;
  max-width: 950px;
}}

.subtitle {{
  font-size: 16px;
  font-weight: 500;
  line-height: 1.45;
  color: rgba(255, 255, 255, 0.72);
  max-width: 920px;
}}

/* Hero Stage Area: y = 290px to 840px (Height 550px) */
.stage-area {{
  position: absolute;
  left: var(--pad-x);
  right: var(--pad-x);
  top: 290px;
  height: 550px;
  z-index: 20;
}}

/* Bottom Action Bar: y = 870px to 950px */
.bottom-bar {{
  position: absolute;
  left: var(--pad-x);
  right: var(--pad-x);
  top: 870px;
  height: 80px;
  z-index: 20;
}}

/* Footer: y = 1010px */
.footer-bar {{
  position: absolute;
  left: var(--pad-x);
  right: var(--pad-x);
  bottom: 36px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 13px;
  font-weight: 600;
  color: rgba(255, 255, 255, 0.45);
  letter-spacing: 0.05em;
  z-index: 20;
}}

/* Bento Card Component */
.bento-card {{
  background: rgba(13, 26, 63, 0.65);
  border: 1px solid rgba(255, 255, 255, 0.14);
  backdrop-filter: blur(24px);
  border-radius: 24px;
  padding: 30px;
  box-shadow: 0 20px 40px -10px rgba(5, 11, 31, 0.7);
}}

/* Screenshot Frame */
.screenshot-frame {{
  width: 100%;
  height: 100%;
  border-radius: 20px;
  overflow: hidden;
  border: 1px solid rgba(255, 255, 255, 0.16);
  box-shadow: 0 24px 48px -12px rgba(0, 0, 0, 0.6);
  background: #0A1128;
  display: flex;
  flex-direction: column;
}}

.window-header {{
  height: 32px;
  background: rgba(255, 255, 255, 0.06);
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  display: flex;
  align-items: center;
  padding: 0 14px;
  gap: 6px;
}}

.dot {{ width: 8px; height: 8px; border-radius: 50%; }}
.dot-red {{ background: #EF4444; }}
.dot-yellow {{ background: #F59E0B; }}
.dot-green {{ background: #38BDF8; }}

.screen-img {{
  width: 100%;
  height: calc(100% - 32px);
  object-fit: cover;
  object-position: top center;
}}
</style>
</head>
<body>

<!-- =========================================================================
     SLIDE 1: THE CONTRARIAN HOOK (PAPER PROFIT ILLUSION)
     ========================================================================= -->
<section class="slide" id="slide-01">
  <div class="ambient-glow" style="width: 500px; height: 500px; background: #2563EB; left: -100px; top: -100px; opacity: 0.3;"></div>
  <div class="ambient-glow" style="width: 400px; height: 400px; background: #38BDF8; right: -80px; bottom: 80px; opacity: 0.25;"></div>

  <div class="top-bar">
    <img class="logo" src="{logo_base64}" alt="Cooca Logo">
    <div class="tag-badge">B2B Financial &amp; Inventory Audit</div>
  </div>

  <div class="header-area">
    <div class="overline">Executive Operational Insight</div>
    <div class="headline">Laba di Pembukuan Naik, Kenapa Kas Operasional Kering?</div>
    <div class="subtitle">Anatomi dead stock dan mengapa 30% modal kerja bisnis ritel sering membeku di tumpukan kardus rak gudang.</div>
  </div>

  <div class="stage-area" style="display: grid; grid-template-columns: 1.15fr 0.85fr; gap: 24px;">
    <div class="bento-card" style="display: flex; flex-direction: column; justify-content: space-between;">
      <div>
        <div style="display: inline-block; padding: 4px 14px; background: rgba(239,68,68,0.15); border: 1px solid var(--crimson-400); border-radius: 999px; font-size: 12px; font-weight: 800; color: var(--crimson-400); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 14px;">The Paper Profit Illusion</div>
        <div style="font-size: 22px; font-weight: 800; color: #FFFFFF; line-height: 1.35; margin-bottom: 12px;">Keuntungan di atas kertas tidak berbentuk uang tunai cair.</div>
        <div style="font-size: 15px; color: rgba(255,255,255,0.75); line-height: 1.55;">Banyak pemilik bisnis ritel merasa penjualan ramai, namun saat harus membayar supplier dan gaji, saldo bank selalu pas-pasan. Uang kas operasional terserap membeli barang yang tidak bergerak.</div>
      </div>

      <div style="background: rgba(255,255,255,0.05); border-radius: 18px; padding: 20px 24px; display: grid; grid-template-columns: 1fr 1fr; gap: 20px; border: 1px solid rgba(255,255,255,0.1);">
        <div>
          <div style="font-size: 12px; color: rgba(255,255,255,0.6); text-transform: uppercase; font-weight: 700; margin-bottom: 4px;">Laba Bersih di Kertas</div>
          <div style="font-size: 32px; font-weight: 800; color: var(--cyan-400);">Rp 120 Jt</div>
          <div style="font-size: 12px; color: var(--cyan-400); font-weight: 600; margin-top: 2px;">Tercatat Pembukuan</div>
        </div>
        <div>
          <div style="font-size: 12px; color: rgba(255,255,255,0.6); text-transform: uppercase; font-weight: 700; margin-bottom: 4px;">Kas Fisik Riil Bank</div>
          <div style="font-size: 32px; font-weight: 800; color: var(--crimson-400);">Rp 14 Jt</div>
          <div style="font-size: 12px; color: var(--crimson-400); font-weight: 600; margin-top: 2px;">88% Terjebak di Stok</div>
        </div>
      </div>
    </div>

    <div class="bento-card" style="display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; background: radial-gradient(circle at center, rgba(37,99,235,0.35) 0%, rgba(13,26,63,0.7) 100%);">
      <img src="{cutout_shop}" alt="Retail Warehouse" style="width: 220px; height: 220px; object-fit: contain; margin-bottom: 16px; filter: drop-shadow(0 20px 30px rgba(0,0,0,0.6));">
      <div style="font-size: 18px; font-weight: 800; color: #FFFFFF; margin-bottom: 6px;">3 Titik Kritis Penumpukan Stok</div>
      <div style="font-size: 14px; color: rgba(255,255,255,0.72); max-width: 320px; line-height: 1.45;">Geser untuk melihat audit kesalahan pengadaan dan framework percepatan arus kas.</div>
    </div>
  </div>

  <div class="bottom-bar">
    <div style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); border-radius: 18px; padding: 18px 28px; display: flex; align-items: center; justify-content: space-between;">
      <div style="display: flex; align-items: center; gap: 16px;">
        <img src="{icon_target}" alt="Target" style="width: 44px; height: 44px; object-fit: contain;">
        <div>
          <div style="font-size: 15px; font-weight: 700; color: #FFFFFF;">Target: Zero Dead Stock &amp; Fast Cash Conversion Cycle</div>
          <div style="font-size: 13px; color: rgba(255,255,255,0.65);">Kunci perputaran kasir dan stok otomatis di ekosistem terpadu COOCA.</div>
        </div>
      </div>
      <div style="font-size: 14px; font-weight: 800; color: var(--cyan-400); letter-spacing: 0.04em;">Geser Slide &rarr;</div>
    </div>
  </div>

  <div class="footer-bar">
    <div>Dokumen Tata Kelola Inventori Ritel Cooca</div>
    <div>Slide 1 dari 5</div>
  </div>
</section>

<!-- =========================================================================
     SLIDE 2: 3 KESALAHAN FATAL PENGADAAN (FRICTION POINTS)
     ========================================================================= -->
<section class="slide" id="slide-02">
  <div class="ambient-glow" style="width: 450px; height: 450px; background: #EF4444; left: -80px; top: 120px; opacity: 0.2;"></div>

  <div class="top-bar">
    <img class="logo" src="{logo_base64}" alt="Cooca Logo">
    <div class="tag-badge">Friction Points</div>
  </div>

  <div class="header-area">
    <div class="overline" style="color: var(--crimson-400);">Diagnosa Titik Bocor Likuiditas</div>
    <div class="headline">3 Titik Kritis Pengadaan Barang yang Membekukan Modal</div>
    <div class="subtitle">Ketika pembelian barang didasarkan pada perkiraan manual, bukan kecepatan perputaran kasir riil.</div>
  </div>

  <div class="stage-area" style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
    <div style="display: flex; flex-direction: column; gap: 14px;">
      <!-- Item 1 -->
      <div class="bento-card" style="padding: 20px 24px;">
        <div style="display: flex; align-items: flex-start; gap: 16px;">
          <div style="width: 36px; height: 36px; border-radius: 12px; background: rgba(239,68,68,0.2); border: 1px solid var(--crimson-400); color: var(--crimson-400); font-weight: 800; font-size: 15px; display: flex; align-items: center; justify-content: center; flex: none;">01</div>
          <div>
            <div style="font-size: 16px; font-weight: 800; color: #FFFFFF; margin-bottom: 4px;">Jebakan Diskon Borongan Supplier</div>
            <div style="font-size: 13px; color: rgba(255,255,255,0.72); line-height: 1.45;">Beli 500 unit demi diskon 5%, padahal penjualan per bulan hanya 20 unit. Modal Rp 40 Juta terkunci 2 tahun di rak.</div>
          </div>
        </div>
      </div>

      <!-- Item 2 -->
      <div class="bento-card" style="padding: 20px 24px;">
        <div style="display: flex; align-items: flex-start; gap: 16px;">
          <div style="width: 36px; height: 36px; border-radius: 12px; background: rgba(245,158,11,0.2); border: 1px solid var(--amber-400); color: var(--amber-400); font-weight: 800; font-size: 15px; display: flex; align-items: center; justify-content: center; flex: none;">02</div>
          <div>
            <div style="font-size: 16px; font-weight: 800; color: #FFFFFF; margin-bottom: 4px;">Tanpa Klasifikasi Fast vs Slow Moving</div>
            <div style="font-size: 13px; color: rgba(255,255,255,0.72); line-height: 1.45;">Barang lambat laku terus dipesan ulang karena staf gudang tidak memiliki data perputaran barang yang akurat.</div>
          </div>
        </div>
      </div>

      <!-- Item 3 -->
      <div class="bento-card" style="padding: 20px 24px;">
        <div style="display: flex; align-items: flex-start; gap: 16px;">
          <div style="width: 36px; height: 36px; border-radius: 12px; background: rgba(56,189,248,0.2); border: 1px solid var(--cyan-400); color: var(--cyan-400); font-weight: 800; font-size: 15px; display: flex; align-items: center; justify-content: center; flex: none;">03</div>
          <div>
            <div style="font-size: 16px; font-weight: 800; color: #FFFFFF; margin-bottom: 4px;">Stock Opname Terlambat Mendeteksi Usang</div>
            <div style="font-size: 13px; color: rgba(255,255,255,0.72); line-height: 1.45;">Audit hanya dilakukan per semester. Saat disadari, barang sudah rusak, kedaluwarsa, atau terpaksa diobral rugi.</div>
          </div>
        </div>
      </div>
    </div>

    <div class="bento-card" style="display: flex; flex-direction: column; justify-content: space-between; background: linear-gradient(145deg, rgba(239,68,68,0.12) 0%, rgba(13,26,63,0.75) 100%);">
      <div>
        <div style="font-size: 13px; font-weight: 800; color: var(--crimson-400); text-transform: uppercase; margin-bottom: 10px;">Dampak Finansial Nyata</div>
        <div style="font-size: 20px; font-weight: 800; color: #FFFFFF; line-height: 1.35; margin-bottom: 14px;">Biaya simpan (holding cost) mengikis 18% sampai 25% nilai barang per tahun.</div>
        <div style="font-size: 14px; color: rgba(255,255,255,0.75); line-height: 1.5;">Setiap meter persegi gudang yang ditempati barang macet menyedot biaya sewa, risiko kerusakan, dan bunga modal yang hilang.</div>
      </div>

      <div style="background: rgba(255,255,255,0.06); border: 1px dashed rgba(255,255,255,0.2); border-radius: 16px; padding: 18px 20px;">
        <div style="font-size: 12px; font-weight: 700; color: var(--cyan-300); text-transform: uppercase; margin-bottom: 4px;">Solusi Fundamental:</div>
        <div style="font-size: 13px; color: rgba(255,255,255,0.85); line-height: 1.45;">Terapkan Prinsip Analisis Portofolio SKU ABC untuk mengontrol pengadaan berbasis data penjualan riil.</div>
      </div>
    </div>
  </div>

  <div class="bottom-bar">
    <div style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); border-radius: 18px; padding: 18px 28px; display: flex; align-items: center; justify-content: space-between;">
      <div style="font-size: 15px; color: rgba(255,255,255,0.85);"><strong>Aturan Likuiditas:</strong> Jangan tukar uang kas yang cair dengan tumpukan barang yang tidak pasti terjual.</div>
      <div style="font-size: 13px; color: var(--cyan-400); font-weight: 700;">Audit Kas &amp; Stok</div>
    </div>
  </div>

  <div class="footer-bar">
    <div>Titik Kritis Pengadaan Barang</div>
    <div>Slide 2 dari 5</div>
  </div>
</section>

<!-- =========================================================================
     SLIDE 3: FRAMEWORK ANALISIS ABC & DIO
     ========================================================================= -->
<section class="slide" id="slide-03">
  <div class="ambient-glow" style="width: 500px; height: 500px; background: #2563EB; right: -50px; top: -50px; opacity: 0.25;"></div>

  <div class="top-bar">
    <img class="logo" src="{logo_base64}" alt="Cooca Logo">
    <div class="tag-badge">Strategic Framework</div>
  </div>

  <div class="header-area">
    <div class="overline">Kerangka Kerja Efisiensi Modal</div>
    <div class="headline">Audit Portofolio SKU dengan Prinsip Analisis ABC</div>
    <div class="subtitle">Petakan seluruh katalog barang ke dalam 3 kategori likuiditas untuk mempercepat Days Inventory Outstanding (DIO).</div>
  </div>

  <div class="stage-area" style="display: flex; flex-direction: column; gap: 16px;">
    <!-- Kategori A -->
    <div class="bento-card" style="padding: 22px 28px; display: grid; grid-template-columns: 190px 1fr 140px; align-items: center; gap: 20px; border-color: rgba(56,189,248,0.4); background: rgba(37,99,235,0.12);">
      <div>
        <div style="font-size: 12px; color: var(--cyan-400); font-weight: 800; text-transform: uppercase;">Kategori A (Fast-Moving)</div>
        <div style="font-size: 20px; font-weight: 800; color: #FFFFFF;">80% Omzet Toko</div>
      </div>
      <div style="font-size: 14px; color: rgba(255,255,255,0.8); line-height: 1.45;">
        Hanya mencakup 20% total SKU. <strong>SOP Pengadaan:</strong> Reorder ramping berkala (lean replenishment), pasokan jangan sampai kosong, buffer stok rendah.
      </div>
      <div style="text-align: right;">
        <span style="display: inline-block; padding: 6px 14px; background: rgba(56,189,248,0.15); border: 1px solid var(--cyan-400); border-radius: 999px; font-size: 12px; font-weight: 800; color: var(--cyan-400);">Prioritas Kas</span>
      </div>
    </div>

    <!-- Kategori B -->
    <div class="bento-card" style="padding: 22px 28px; display: grid; grid-template-columns: 190px 1fr 140px; align-items: center; gap: 20px;">
      <div>
        <div style="font-size: 12px; color: var(--amber-400); font-weight: 800; text-transform: uppercase;">Kategori B (Medium-Pace)</div>
        <div style="font-size: 20px; font-weight: 800; color: #FFFFFF;">15% Omzet Toko</div>
      </div>
      <div style="font-size: 14px; color: rgba(255,255,255,0.8); line-height: 1.45;">
        Mencakup 30% total SKU. <strong>SOP Pengadaan:</strong> Evaluasi mingguan, jaga stok aman secukupnya, pantau fluktuasi musiman.
      </div>
      <div style="text-align: right;">
        <span style="display: inline-block; padding: 6px 14px; background: rgba(245,158,11,0.15); border: 1px solid var(--amber-400); border-radius: 999px; font-size: 12px; font-weight: 800; color: var(--amber-400);">Evaluasi Rutin</span>
      </div>
    </div>

    <!-- Kategori C -->
    <div class="bento-card" style="padding: 22px 28px; display: grid; grid-template-columns: 190px 1fr 140px; align-items: center; gap: 20px; border-color: rgba(239,68,68,0.4); background: rgba(239,68,68,0.08);">
      <div>
        <div style="font-size: 12px; color: var(--crimson-400); font-weight: 800; text-transform: uppercase;">Kategori C (Slow/Dead)</div>
        <div style="font-size: 20px; font-weight: 800; color: #FFFFFF;">Hanya 5% Omzet</div>
      </div>
      <div style="font-size: 14px; color: rgba(255,255,255,0.8); line-height: 1.45;">
        Mencakup 50% SKU di gudang! <strong>SOP Pengadaan:</strong> Hentikan PO jika usia barang &gt; 60 hari. Segera cairkan modal lewat bundling atau flash sale.
      </div>
      <div style="text-align: right;">
        <span style="display: inline-block; padding: 6px 14px; background: rgba(239,68,68,0.15); border: 1px solid var(--crimson-400); border-radius: 999px; font-size: 12px; font-weight: 800; color: var(--crimson-400);">Stop Reorder</span>
      </div>
    </div>
  </div>

  <div class="bottom-bar">
    <div style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); border-radius: 18px; padding: 18px 28px; display: flex; align-items: center; justify-content: space-between;">
      <div style="font-size: 15px; color: rgba(255,255,255,0.85);"><strong>Kunci Manajemen:</strong> 80% kas operasional Anda wajib dialokasikan khusus produk Kategori A.</div>
      <div style="font-size: 13px; color: var(--cyan-400); font-weight: 700;">Standar ABC / DIO</div>
    </div>
  </div>

  <div class="footer-bar">
    <div>Kerangka Kerja Analisis Portofolio SKU</div>
    <div>Slide 3 dari 5</div>
  </div>
</section>

<!-- =========================================================================
     SLIDE 4: SYSTEM SOLUTION (COOCA INVENTORY & POS INTEGRATION)
     ========================================================================= -->
<section class="slide" id="slide-04">
  <div class="ambient-glow" style="width: 450px; height: 450px; background: #2563EB; left: -60px; top: 100px; opacity: 0.25;"></div>

  <div class="top-bar">
    <img class="logo" src="{logo_base64}" alt="Cooca Logo">
    <div class="tag-badge">Cloud System Solution</div>
  </div>

  <div class="header-area">
    <div class="overline" style="color: var(--cyan-400);">Otomasi Kontrol Gudang &amp; Kasir</div>
    <div class="headline">Kunci Mutasi Stok Otomatis dari Setiap Transaksi Kasir</div>
    <div class="subtitle">Hubungkan meja kasir POS langsung ke kartu stok gudang secara real-time tanpa rekonsiliasi manual.</div>
  </div>

  <div class="stage-area" style="display: grid; grid-template-columns: 1.15fr 0.85fr; gap: 24px; align-items: center;">
    <div class="screenshot-frame">
      <div class="window-header">
        <div class="dot dot-red"></div>
        <div class="dot dot-yellow"></div>
        <div class="dot dot-green"></div>
        <div style="font-size: 11px; color: rgba(255,255,255,0.5); margin-left: 8px;">cooca.id/inventory/stocks - Live Stock Valuation &amp; Movement</div>
      </div>
      <img class="screen-img" src="{screen_inventory}" alt="Cooca Inventory Stocks">
    </div>

    <div class="bento-card" style="height: 100%; display: flex; flex-direction: column; justify-content: space-between;">
      <div>
        <div style="font-size: 13px; font-weight: 800; color: var(--cyan-400); text-transform: uppercase; margin-bottom: 8px;">Otomasi Tanpa Celah</div>
        <div style="font-size: 20px; font-weight: 800; color: #FFFFFF; line-height: 1.35; margin-bottom: 14px;">Setiap scan barcode kasir langsung memotong stok gudang detik itu juga.</div>
        <div style="font-size: 14px; color: rgba(255,255,255,0.75); line-height: 1.5;">Tidak ada lagi jeda waktu antara barang keluar dan catatan pembukuan. Audit trail transparan mencatat barang masuk, keluar, retur, hingga sampel promosi.</div>
      </div>

      <div style="display: flex; flex-direction: column; gap: 10px;">
        <div style="background: rgba(255,255,255,0.06); padding: 14px 18px; border-radius: 14px; display: flex; justify-content: space-between; align-items: center;">
          <span style="font-size: 13px; color: rgba(255,255,255,0.75);">Potong Stok Kasir</span>
          <span style="font-size: 15px; font-weight: 800; color: var(--cyan-400);">0 Detik Real-Time</span>
        </div>
        <div style="background: rgba(255,255,255,0.06); padding: 14px 18px; border-radius: 14px; display: flex; justify-content: space-between; align-items: center;">
          <span style="font-size: 13px; color: rgba(255,255,255,0.75);">Peringatan Stok Menipis</span>
          <span style="font-size: 15px; font-weight: 800; color: var(--amber-400);">Auto-Alert Aktif</span>
        </div>
        <div style="background: rgba(255,255,255,0.06); padding: 14px 18px; border-radius: 14px; display: flex; justify-content: space-between; align-items: center;">
          <span style="font-size: 13px; color: rgba(255,255,255,0.75);">Potensi Reduksi Dead Stock</span>
          <span style="font-size: 15px; font-weight: 800; color: var(--cyan-300);">Hingga 40%</span>
        </div>
      </div>
    </div>
  </div>

  <div class="bottom-bar">
    <div style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); border-radius: 18px; padding: 18px 28px; display: flex; align-items: center; justify-content: space-between;">
      <div style="font-size: 15px; color: rgba(255,255,255,0.85);"><strong>Dampak Nyata:</strong> Ketahui persis HPP dan nilai valuasi stok toko Anda setiap hari.</div>
      <div style="font-size: 13px; color: var(--cyan-400); font-weight: 700;">Fitur: /inventory/stocks</div>
    </div>
  </div>

  <div class="footer-bar">
    <div>Solusi Manajemen Stok &amp; Kasir Terintegrasi</div>
    <div>Slide 4 dari 5</div>
  </div>
</section>

<!-- =========================================================================
     SLIDE 5: ROADMAP IMPLEMENTASI & CALL TO ACTION
     ========================================================================= -->
<section class="slide" id="slide-05">
  <div class="ambient-glow" style="width: 500px; height: 500px; background: #2563EB; left: 150px; top: 100px; opacity: 0.3;"></div>

  <div class="top-bar">
    <img class="logo" src="{logo_base64}" alt="Cooca Logo">
    <div class="tag-badge">Implementation Roadmap</div>
  </div>

  <div class="header-area">
    <div class="overline">Langkah Implementasi Manajemen</div>
    <div class="headline">Mulai Audit Stok dan Bebaskan Modal Kerja Bisnis Anda</div>
    <div class="subtitle">Jangan biarkan keuntungan operasional toko menguap menjadi tumpukan kardus berdebu di rak gudang.</div>
  </div>

  <div class="stage-area" style="display: grid; grid-template-columns: 1.05fr 0.95fr; gap: 24px; align-items: center;">
    <div class="bento-card" style="height: 100%; display: flex; flex-direction: column; justify-content: space-between;">
      <div>
        <div style="font-size: 13px; font-weight: 800; color: var(--cyan-400); text-transform: uppercase; margin-bottom: 14px;">3 Langkah Cepat Likuidasi Stok</div>
        
        <div style="display: flex; gap: 14px; margin-bottom: 18px;">
          <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--blue-500); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px; flex: none;">1</div>
          <div>
            <div style="font-size: 16px; font-weight: 700; color: #FFFFFF;">Audit Penuaan Stok (DIO)</div>
            <div style="font-size: 13px; color: rgba(255,255,255,0.72); line-height: 1.4;">Identifikasi SKU yang tidak bergerak lebih dari 60 hari dari data riil.</div>
          </div>
        </div>

        <div style="display: flex; gap: 14px; margin-bottom: 18px;">
          <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--blue-500); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px; flex: none;">2</div>
          <div>
            <div style="font-size: 16px; font-weight: 700; color: #FFFFFF;">Kunci Titik Reorder Otomatis</div>
            <div style="font-size: 13px; color: rgba(255,255,255,0.72); line-height: 1.4;">Hanya lakukan PO jika stok menyentuh batas minimum aman.</div>
          </div>
        </div>

        <div style="display: flex; gap: 14px;">
          <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--blue-500); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px; flex: none;">3</div>
          <div>
            <div style="font-size: 16px; font-weight: 700; color: #FFFFFF;">Integrasikan Kasir &amp; Gudang Terpadu</div>
            <div style="font-size: 13px; color: rgba(255,255,255,0.72); line-height: 1.4;">Gunakan sistem modern di cooca.id untuk monitoring multi-outlet.</div>
          </div>
        </div>
      </div>

      <div style="font-size: 13px; color: rgba(255,255,255,0.5); line-height: 1.4;">Arus kas yang sehat memberi Anda keleluasaan berinvestasi dan membuka peluang baru.</div>
    </div>

    <div class="bento-card" style="height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; background: radial-gradient(circle at center, rgba(37,99,235,0.4) 0%, rgba(13,26,63,0.75) 100%);">
      <img src="{icon_trophy}" alt="Trophy" style="width: 140px; height: 140px; object-fit: contain; margin-bottom: 16px; filter: drop-shadow(0 16px 28px rgba(0,0,0,0.5));">
      <div style="font-size: 22px; font-weight: 800; color: #FFFFFF; margin-bottom: 8px;">Kendalikan Likuiditas Toko</div>
      <div style="font-size: 14px; color: rgba(255,255,255,0.75); max-width: 320px; margin-bottom: 22px; line-height: 1.45;">Platform ERP dan kasir pintar modern yang dirancang untuk kebutuhan nyata bisnis Indonesia.</div>
      
      <div style="background: var(--blue-500); border-radius: 999px; padding: 14px 34px; font-size: 16px; font-weight: 800; color: #FFFFFF; letter-spacing: 0.04em; box-shadow: 0 10px 25px rgba(37,99,235,0.5);">
        Mulai di cooca.id
      </div>
      <div style="font-size: 13px; color: rgba(255,255,255,0.6); margin-top: 12px; font-weight: 600;">Paket Solo Owner Gratis Selamanya (Rp 0)</div>
    </div>
  </div>

  <div class="bottom-bar">
    <div style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); border-radius: 18px; padding: 18px 28px; display: flex; align-items: center; justify-content: space-between;">
      <div style="font-size: 15px; color: rgba(255,255,255,0.85);">Simpan dokumen ini dan bagikan ke tim operasional dan keuangan toko Anda.</div>
      <div style="font-size: 14px; color: var(--cyan-400); font-weight: 800;">cooca.id</div>
    </div>
  </div>

  <div class="footer-bar">
    <div>Cooca Enterprise Retail Solution</div>
    <div>Slide 5 dari 5</div>
  </div>
</section>

</body>
</html>
"""

async def render():
    print("[Playwright] Rendering professional pixel-perfect carousel slides...")
    
    # Save standalone showcase HTML
    showcase_path = os.path.join(BASE_DIR, "public/admin/social-media-assets/carousel/linkedin/showcase.html")
    with open(showcase_path, "w", encoding="utf-8") as f:
        f.write(html_content)
    print(f"Saved standalone showcase to {showcase_path}")

    target_dir_storage = os.path.join(BASE_DIR, "storage/app/public/admin/social-media-assets/carousel/linkedin")
    target_dir_public = os.path.join(BASE_DIR, "public/admin/social-media-assets/carousel/linkedin")
    os.makedirs(target_dir_storage, exist_ok=True)
    os.makedirs(target_dir_public, exist_ok=True)

    async with async_playwright() as p:
        browser = await p.chromium.launch(headless=True)
        # Device scale factor 2 = 2160x2160 retina crispness
        page = await browser.new_page(viewport={"width": 1080, "height": 1080}, device_scale_factor=2)
        await page.set_content(html_content, wait_until="networkidle")

        slide_ids = ["slide-01", "slide-02", "slide-03", "slide-04", "slide-05"]
        
        for idx, s_id in enumerate(slide_ids, 1):
            file_name = f"slide-0{idx}.png"
            p_storage = os.path.join(target_dir_storage, file_name)
            p_public = os.path.join(target_dir_public, file_name)

            elem = await page.query_selector(f"#{s_id}")
            if elem:
                await elem.screenshot(path=p_storage)
                with open(p_storage, "rb") as src, open(p_public, "wb") as dst:
                    dst.write(src.read())
                print(f"[Slide {idx}] Rendered {file_name} -> {os.path.getsize(p_storage):,} bytes")
            else:
                print(f"Error: Element #{s_id} not found!")

        # Also generate PDF document for LinkedIn
        pdf_path_storage = os.path.join(target_dir_storage, "carousel-linkedin.pdf")
        pdf_path_public = os.path.join(target_dir_public, "carousel-linkedin.pdf")
        await page.pdf(
            path=pdf_path_storage,
            width="1080px",
            height="1080px",
            print_background=True,
            margin={"top": "0px", "right": "0px", "bottom": "0px", "left": "0px"}
        )
        with open(pdf_path_storage, "rb") as src, open(pdf_path_public, "wb") as dst:
            dst.write(src.read())
        print(f"[PDF] Rendered carousel-linkedin.pdf -> {os.path.getsize(pdf_path_storage):,} bytes")

        await browser.close()

if __name__ == "__main__":
    asyncio.run(render())
