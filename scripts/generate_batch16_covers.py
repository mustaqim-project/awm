import json
import os
import time
from playwright.sync_api import sync_playwright

CARDS_DATA = {
    759: {
        "pillar": "Pilar: HPP & Penetapan Harga Kuliner",
        "pillar_badge": "F&B COSTING • ARTISAN TEA & LIGHT MILK TEA",
        "theme": "culinary",
        "title": "Cara Menghitung HPP Teh Artisan Light Milk Tea",
        "subtitle": "Formulasi food cost 25-28 persen, pemilihan daun teh loose leaf lokal, dan simulasi margin keuntungan tebal.",
        "card_title": "Kalkulasi HPP Per Botol 500ml",
        "badge_status": "MARGIN BERSIH 65%",
        "rows": [
            ("Daun Teh Loose Leaf (8g)", "Rp 1.400"),
            ("Susu Rendah Lemak (180ml)", "Rp 3.200"),
            ("Pemanis Alami & Es Batu", "Rp 900"),
            ("Botol Kale & Segel Label", "Rp 1.800"),
            ("Total HPP Pokok Produksi", "Rp 7.300 / Porsi"),
            ("Harga Jual Rekomendasi", "Rp 22.000 (Untung 67%)")
        ]
    },
    760: {
        "pillar": "Pilar: Kasir, POS & Regulasi Pembayaran",
        "pillar_badge": "BANK INDONESIA • REGULASI QRIS TANPA SURCHARGE",
        "theme": "cashier",
        "title": "Aturan Biaya MDR QRIS Dilarang Dibebankan Konsumen",
        "subtitle": "Ketentuan batas gratis MDR 0 persen Usaha Mikro Rp500 ribu, risiko sanksi blokir merchant, dan SOP kasir toko.",
        "card_title": "Standar Tarif MDR QRIS Bank Indonesia",
        "badge_status": "ANTI-SURCHARGE KONSUMEN",
        "rows": [
            ("Usaha Mikro (s.d Rp500.000)", "Tarif 0% (Bebas Biaya)"),
            ("Usaha Mikro (> Rp500.000)", "MDR 0,3% Potong Merchant"),
            ("Usaha Kecil & Menengah (UKE/UME)", "0% s.d 100rb, 0,7% di atasnya"),
            ("Beban Biaya ke Pembeli", "Dilarang Keras (Rp 0 Tambahan)"),
            ("Sanksi Pelanggaran Surcharge", "Penghentian Kerja Sama PJP"),
            ("Pencatatan Akuntansi", "Beban Operasional Resmi Toko")
        ]
    },
    761: {
        "pillar": "Pilar: Strategi Operasional & Payroll Karyawan",
        "pillar_badge": "PP PENGUPAHAN • PENGECUALIAN UMP USAHA KECIL",
        "theme": "payroll",
        "title": "Aturan Upah UMKM Pasca Penetapan UMP 2027",
        "subtitle": "Dasar hukum pengecualian UMP bagi usaha mikro kecil, batas ambang 50 persen konsumsi, dan draf kesepakatan tertulis.",
        "card_title": "Ketentuan Pengupahan Usaha Mikro",
        "badge_status": "KESEPAKATAN KERJA SAH",
        "rows": [
            ("Status Kewajiban UMP 2027", "Dikecualikan Khusus Mikro & Kecil"),
            ("Batas Minimal Upah Kesepakatan", "50% Rata-rata Konsumsi Provinsi"),
            ("Batas Alternatif Wilayah", "25% Di Atas Garis Kemiskinan"),
            ("Bentuk Perjanjian Kerja", "Wajib Tertulis & Ditandatangani"),
            ("Skema Bonus Toko", "Insentif Berbasis Omzet Harian"),
            ("Perlindungan Hukum Usaha", "Aman Audit Ketenagakerjaan")
        ]
    }
}

THEME_CONFIG = {
    "culinary": {
        "glow1": "rgba(16, 185, 129, 0.25)",
        "glow2": "rgba(245, 158, 11, 0.18)",
        "accent": "#10b981",
        "pill_bg": "rgba(16, 185, 129, 0.15)",
        "pill_border": "rgba(16, 185, 129, 0.35)",
        "pill_text": "#34d399",
        "card_badge_bg": "rgba(16, 185, 129, 0.2)",
        "card_badge_border": "rgba(16, 185, 129, 0.4)",
        "card_badge_text": "#34d399",
        "card_val_color": "#34d399",
        "icon": "🍵"
    },
    "cashier": {
        "glow1": "rgba(59, 130, 246, 0.25)",
        "glow2": "rgba(14, 165, 233, 0.18)",
        "accent": "#38bdf8",
        "pill_bg": "rgba(56, 189, 248, 0.15)",
        "pill_border": "rgba(56, 189, 248, 0.35)",
        "pill_text": "#38bdf8",
        "card_badge_bg": "rgba(56, 189, 248, 0.2)",
        "card_badge_border": "rgba(56, 189, 248, 0.4)",
        "card_badge_text": "#38bdf8",
        "card_val_color": "#38bdf8",
        "icon": "📱"
    },
    "payroll": {
        "glow1": "rgba(99, 102, 241, 0.25)",
        "glow2": "rgba(168, 85, 247, 0.18)",
        "accent": "#818cf8",
        "pill_bg": "rgba(99, 102, 241, 0.15)",
        "pill_border": "rgba(99, 102, 241, 0.35)",
        "pill_text": "#a5b4fc",
        "card_badge_bg": "rgba(99, 102, 241, 0.2)",
        "card_badge_border": "rgba(99, 102, 241, 0.4)",
        "card_badge_text": "#a5b4fc",
        "card_val_color": "#a5b4fc",
        "icon": "📋"
    }
}
THEME_CONFIG["payroll"] = THEME_CONFIG["payroll"]

def render_html(item):
    theme_key = "payroll" if item["theme"] not in THEME_CONFIG else item["theme"]
    theme = THEME_CONFIG[theme_key]
    rows_html = ""
    for label, val in item["rows"]:
        rows_html += f"""
        <div class="data-row">
          <span class="data-label">{label}</span>
          <span class="data-val">{val}</span>
        </div>
        """

    html = f"""<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
  @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap');
  
  * {{ box-sizing: border-box; margin: 0; padding: 0; }}
  body {{
    width: 1536px;
    height: 864px;
    background: #090d16;
    font-family: 'Plus Jakarta Sans', 'Segoe UI', -apple-system, Roboto, sans-serif;
    color: #f8fafc;
    overflow: hidden;
    position: relative;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 70px 85px;
  }}
  
  .bg-glow-1 {{
    position: absolute;
    top: -140px;
    right: -120px;
    width: 800px;
    height: 800px;
    background: radial-gradient(circle, {theme['glow1']} 0%, rgba(0,0,0,0) 70%);
    border-radius: 50%;
    z-index: 1;
  }}
  .bg-glow-2 {{
    position: absolute;
    bottom: -180px;
    left: 200px;
    width: 700px;
    height: 700px;
    background: radial-gradient(circle, {theme['glow2']} 0%, rgba(0,0,0,0) 70%);
    border-radius: 50%;
    z-index: 1;
  }}
  .bg-grid {{
    position: absolute;
    inset: 0;
    background-image: linear-gradient(to right, rgba(255,255,255,0.035) 1px, transparent 1px),
                      linear-gradient(to bottom, rgba(255,255,255,0.035) 1px, transparent 1px);
    background-size: 48px 48px;
    z-index: 1;
  }}

  .header {{
    position: relative;
    z-index: 10;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }}
  .brand-badge {{
    display: flex;
    align-items: center;
    gap: 16px;
  }}
  .brand-logo {{
    width: 48px;
    height: 48px;
    border-radius: 14px;
    background: linear-gradient(135deg, #0284c7, #38bdf8);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 26px;
    color: white;
    box-shadow: 0 0 24px rgba(56, 189, 248, 0.4);
  }}
  .brand-text {{
    font-weight: 800;
    font-size: 24px;
    letter-spacing: -0.5px;
    color: #ffffff;
  }}
  .brand-tag {{
    font-size: 14px;
    color: #64748b;
    font-weight: 500;
    padding-left: 12px;
    border-left: 1px solid rgba(255,255,255,0.15);
  }}

  .pillar-pill {{
    display: inline-flex;
    align-items: center;
    padding: 8px 18px;
    border-radius: 9999px;
    font-size: 14px;
    font-weight: 700;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    background: {theme['pill_bg']};
    color: {theme['pill_text']};
    border: 1px solid {theme['pill_border']};
  }}

  .main-content {{
    position: relative;
    z-index: 10;
    display: grid;
    grid-template-columns: 1.25fr 0.95fr;
    gap: 60px;
    align-items: center;
  }}

  .title-area {{
    display: flex;
    flex-direction: column;
    gap: 20px;
  }}
  .keyword-tag {{
    font-family: 'JetBrains Mono', monospace;
    font-size: 15px;
    color: {theme['accent']};
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 10px;
    letter-spacing: 0.5px;
  }}
  .keyword-tag::before {{
    content: '';
    display: inline-block;
    width: 10px;
    height: 10px;
    background: {theme['accent']};
    border-radius: 50%;
    box-shadow: 0 0 12px {theme['accent']};
  }}
  .title {{
    font-size: 44px;
    font-weight: 800;
    line-height: 1.22;
    letter-spacing: -1px;
    color: #ffffff;
  }}
  .desc {{
    font-size: 19px;
    line-height: 1.55;
    color: #94a3b8;
    font-weight: 400;
  }}

  .visual-card {{
    background: rgba(15, 23, 42, 0.82);
    border: 1px solid rgba(255, 255, 255, 0.14);
    border-radius: 24px;
    padding: 28px 30px;
    backdrop-filter: blur(16px);
    box-shadow: 0 24px 60px -12px rgba(0, 0, 0, 0.6), inset 0 1px 0 rgba(255, 255, 255, 0.1);
  }}
  .card-header {{
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid rgba(255, 255, 255, 0.09);
    padding-bottom: 16px;
    margin-bottom: 18px;
  }}
  .card-title {{
    font-size: 17px;
    font-weight: 700;
    color: #ffffff;
    display: flex;
    align-items: center;
    gap: 10px;
  }}
  .badge-status {{
    font-size: 13px;
    font-weight: 700;
    padding: 5px 12px;
    border-radius: 8px;
    background: {theme['card_badge_bg']};
    color: {theme['card_badge_text']};
    border: 1px solid {theme['card_badge_border']};
    font-family: 'JetBrains Mono', monospace;
  }}

  .card-body {{
    display: flex;
    flex-direction: column;
    gap: 11px;
  }}
  .data-row {{
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: rgba(255,255,255,0.035);
    padding: 11px 16px;
    border-radius: 12px;
    font-size: 15px;
  }}
  .data-label {{ color: #94a3b8; font-weight: 500; }}
  .data-val {{ color: #f8fafc; font-weight: 700; font-family: 'JetBrains Mono', monospace; }}

  .footer {{
    position: relative;
    z-index: 10;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-top: 1px solid rgba(255,255,255,0.08);
    padding-top: 22px;
    font-size: 15px;
    color: #64748b;
  }}
  .footer-meta {{
    display: flex;
    gap: 25px;
  }}
  .footer-meta span {{
    display: flex;
    align-items: center;
    gap: 8px;
  }}
</style>
</head>
<body>
  <div class="bg-glow-1"></div>
  <div class="bg-glow-2"></div>
  <div class="bg-grid"></div>

  <div class="header">
    <div class="brand-badge">
      <div class="brand-logo">C</div>
      <div class="brand-text">COOCA</div>
      <div class="brand-tag">Edukasi Bisnis UMKM</div>
    </div>
    <div class="pillar-pill">{item['pillar']}</div>
  </div>

  <div class="main-content">
    <div class="title-area">
      <div class="keyword-tag">{item['pillar_badge']}</div>
      <h1 class="title">{item['title']}</h1>
      <p class="desc">{item['subtitle']}</p>
    </div>

    <div class="visual-card">
      <div class="card-header">
        <div class="card-title">
          <span>{theme['icon']}</span> {item['card_title']}
        </div>
        <span class="badge-status">{item['badge_status']}</span>
      </div>
      <div class="card-body">
        {rows_html}
      </div>
    </div>
  </div>

  <div class="footer">
    <div class="footer-meta">
      <span>⏱️ 10 Menit Baca</span>
      <span>📅 19 November 2026</span>
      <span>🎯 Panduan Aplikatif UMKM</span>
    </div>
    <div>cooca.id/blog</div>
  </div>
</body>
</html>
"""
    return html

def main():
    slugs = {
        759: "cara-menghitung-hpp-teh-artisan-light-milk-tea",
        760: "aturan-biaya-mdr-qris-dilarang-dibebankan-konsumen",
        761: "aturan-upah-umkm-pasca-penetapan-ump-2027"
    }

    out_dir = os.path.join('public', 'images', 'blog')
    os.makedirs(out_dir, exist_ok=True)

    with sync_playwright() as p:
        browser = p.chromium.launch()
        page = browser.new_page(viewport={'width': 1536, 'height': 864})

        for id_, slug in slugs.items():
            out_path = os.path.join(out_dir, f"{slug}.jpg")
            card_info = CARDS_DATA[id_]
            html = render_html(card_info)
            page.set_content(html)
            page.wait_for_timeout(400)
            page.screenshot(path=out_path, quality=92, type="jpeg")

            size_kb = os.path.getsize(out_path) / 1024
            print(f"[{id_}] Rendered: {slug}.jpg ({size_kb:.1f} KB, 1536x864 px)")

        browser.close()

    print("All Batch 16 images generated successfully!")

if __name__ == "__main__":
    main()
