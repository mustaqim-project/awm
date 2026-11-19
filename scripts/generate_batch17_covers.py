# scripts/generate_batch17_covers.py
import base64
import os
from playwright.sync_api import sync_playwright

ICON_PATH = os.path.join('public', 'assets', 'image', 'cooca-icon.png')
if os.path.exists(ICON_PATH):
    with open(ICON_PATH, 'rb') as f:
        COOCA_ICON_B64 = base64.b64encode(f.read()).decode('utf-8')
else:
    COOCA_ICON_B64 = ''

CARDS_DATA = {
    762: {
        'pillar': 'Pilar: Legalitas Usaha & Ekosistem E-Commerce',
        'pillar_badge': 'PERMENDAG 19/2026 • KEWAJIBAN NIB SELLER PMSE',
        'theme': 'legal',
        'title': 'Kewajiban NIB Seller Marketplace Online Permendag',
        'subtitle': 'Ancaman pembekuan akun toko, batas masa transisi perizinan berusaha OSS RBA, dan panduan integrasi.',
        'card_title': 'Ketentuan Verifikasi Toko PMSE',
        'badge_status': 'WAJIB NIB RESMI',
        'rows': [
            ('Regulasi Acuan', 'Permendag No. 19 Tahun 2026'),
            ('Subjek Kewajiban', 'Seluruh Seller Shopee, Tokopedia, TikTok'),
            ('Klasifikasi Usaha', 'KBLI 4791 (Perdagangan Eceran Online)'),
            ('Masa Transisi Existing', '18 Bulan Evaluasi Platform'),
            ('Sanksi Tanpa NIB', 'Takedown Toko & Pembekuan Saldo'),
            ('Biaya Pendaftaran', 'Gratis 100% di OSS RBA')
        ]
    },
    763: {
        'pillar': 'Pilar: Pemasaran Digital & Finansial Toko Online',
        'pillar_badge': 'KEMENKOP UKM • DISKON 50% FEE MARKETPLACE',
        'theme': 'discount',
        'title': 'Diskon 50 Persen Biaya Layanan E-Commerce UMKM',
        'subtitle': 'Pemangkasan biaya admin dan komisi bagi seller produk lokal melalui integrasi SAPA UMKM.',
        'card_title': 'Skema Insentif Biaya Seller UMK',
        'badge_status': 'HEMAT KOMISI 50%',
        'rows': [
            ('Regulasi Acuan', 'Permen UMKM No. 3 & Kepmen 398/2026'),
            ('Besaran Potongan', 'Minimal 50% Admin Fee & Komisi'),
            ('Kriteria Usaha', 'Usaha Mikro & Kecil Produk Lokal'),
            ('Kanal Pendaftaran', 'Portal Resmi SAPA UMKM'),
            ('Durasi Verifikasi', 'Maksimal 10 Hari Kerja Platform'),
            ('Dampak Margin', 'Laba Bersih Toko Naik 5-9%')
        ]
    },
    764: {
        'pillar': 'Pilar: Solusi F&B & Standardisasi Produk Kuliner',
        'pillar_badge': 'BPOM RI • SISTEM LABEL NUTRI-LEVEL GGL',
        'theme': 'nutrition',
        'title': 'Aturan Label Nutri-Level BPOM Makanan Minuman',
        'subtitle': 'Klasifikasi Grade A sampai D kandungan gula garam lemak, ancaman penarikan produk, dan trik reformulasi resep.',
        'card_title': 'Klasifikasi Nutri-Level GGL BPOM',
        'badge_status': 'GRADE A-D TRANSPARAN',
        'rows': [
            ('Regulasi Acuan', 'Peraturan BPOM No. 10 Tahun 2026'),
            ('Sasaran Tahap Awal', 'Minuman Botol, Bubuk & Susu Kemasan'),
            ('Sistem Penilaian', 'Grade A (Terbaik) s.d Grade D (Tinggi GGL)'),
            ('Kadar Gula Grade A', 'Maksimal 1 Gram Per 100 ml'),
            ('Risiko Pelanggaran', 'Peringatan Keras & Penarikan Produk'),
            ('Masa Penyesuaian', 'Transisi Bertahap Industri & UMK')
        ]
    }
}

THEME_CONFIG = {
    'legal': {
        'glow1': 'rgba(56, 189, 248, 0.25)',
        'glow2': 'rgba(99, 102, 241, 0.18)',
        'accent': '#38bdf8',
        'pill_bg': 'rgba(56, 189, 248, 0.15)',
        'pill_border': 'rgba(56, 189, 248, 0.35)',
        'pill_text': '#38bdf8',
        'card_badge_bg': 'rgba(56, 189, 248, 0.2)',
        'card_badge_border': 'rgba(56, 189, 248, 0.4)',
        'card_badge_text': '#38bdf8',
        'card_val_color': '#38bdf8',
        'icon': '🛡️'
    },
    'discount': {
        'glow1': 'rgba(16, 185, 129, 0.25)',
        'glow2': 'rgba(245, 158, 11, 0.18)',
        'accent': '#10b981',
        'pill_bg': 'rgba(16, 185, 129, 0.15)',
        'pill_border': 'rgba(16, 185, 129, 0.35)',
        'pill_text': '#34d399',
        'card_badge_bg': 'rgba(16, 185, 129, 0.2)',
        'card_badge_border': 'rgba(16, 185, 129, 0.4)',
        'card_badge_text': '#34d399',
        'card_val_color': '#34d399',
        'icon': '💰'
    },
    'nutrition': {
        'glow1': 'rgba(245, 158, 11, 0.25)',
        'glow2': 'rgba(239, 68, 68, 0.18)',
        'accent': '#f59e0b',
        'pill_bg': 'rgba(245, 158, 11, 0.15)',
        'pill_border': 'rgba(245, 158, 11, 0.35)',
        'pill_text': '#fbbf24',
        'card_badge_bg': 'rgba(245, 158, 11, 0.2)',
        'card_badge_border': 'rgba(245, 158, 11, 0.4)',
        'card_badge_text': '#fbbf24',
        'card_val_color': '#fbbf24',
        'icon': '🏷️'
    }
}

def render_html(item):
    theme = THEME_CONFIG[item['theme']]
    rows_html = ''
    for label, val in item['rows']:
        rows_html += f"""
        <div class="data-row">
          <span class="data-label">{label}</span>
          <span class="data-val">{val}</span>
        </div>
        """

    logo_tag = f'<img src="data:image/png;base64,{COOCA_ICON_B64}" class="brand-logo-img" />' if COOCA_ICON_B64 else '<div class="brand-logo-img"></div>'

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
  .brand-logo-img {{
    width: 48px;
    height: 48px;
    border-radius: 12px;
    object-fit: contain;
    box-shadow: 0 0 24px rgba(56, 189, 248, 0.45);
    display: block;
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
  }}
  .pillar-pill {{
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.12);
    padding: 8px 18px;
    border-radius: 100px;
    font-size: 14px;
    font-weight: 600;
    color: #cbd5e1;
    backdrop-filter: blur(10px);
  }}

  .main-content {{
    position: relative;
    z-index: 10;
    display: grid;
    grid-template-columns: 1.15fr 0.85fr;
    gap: 60px;
    align-items: center;
  }}
  .title-area {{
    display: flex;
    flex-direction: column;
    gap: 20px;
  }}
  .keyword-tag {{
    display: inline-flex;
    align-items: center;
    align-self: flex-start;
    gap: 8px;
    background: {theme['pill_bg']};
    border: 1px solid {theme['pill_border']};
    color: {theme['pill_text']};
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    padding: 6px 14px;
    border-radius: 8px;
    font-family: 'JetBrains Mono', monospace;
  }}
  .title {{
    font-size: 46px;
    font-weight: 800;
    line-height: 1.15;
    letter-spacing: -1px;
    color: #ffffff;
    text-shadow: 0 4px 20px rgba(0,0,0,0.5);
  }}
  .desc {{
    font-size: 20px;
    color: #94a3b8;
    line-height: 1.5;
    font-weight: 400;
  }}

  .visual-card {{
    background: rgba(15, 23, 42, 0.75);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 24px;
    padding: 32px 36px;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(20px);
    display: flex;
    flex-direction: column;
    gap: 20px;
  }}
  .card-header {{
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    padding-bottom: 16px;
  }}
  .card-title {{
    font-size: 18px;
    font-weight: 700;
    color: #f1f5f9;
    display: flex;
    align-items: center;
    gap: 10px;
  }}
  .badge-status {{
    background: {theme['card_badge_bg']};
    border: 1px solid {theme['card_badge_border']};
    color: {theme['card_badge_text']};
    font-size: 11px;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 6px;
    font-family: 'JetBrains Mono', monospace;
  }}
  .card-body {{
    display: flex;
    flex-direction: column;
    gap: 12px;
  }}
  .data-row {{
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.04);
  }}
  .data-row:last-child {{
    border-bottom: none;
    padding-bottom: 0;
  }}
  .data-label {{
    font-size: 15px;
    color: #94a3b8;
    font-weight: 500;
  }}
  .data-val {{
    font-size: 15px;
    font-weight: 700;
    color: {theme['card_val_color']};
    font-family: 'JetBrains Mono', monospace;
    text-align: right;
  }}

  .footer {{
    position: relative;
    z-index: 10;
    display: flex;
    justify-content: space-between;
    align-items: center;
    color: #64748b;
    font-size: 14px;
    font-weight: 500;
    border-top: 1px solid rgba(255, 255, 255, 0.06);
    padding-top: 24px;
  }}
  .footer-meta {{
    display: flex;
    gap: 24px;
  }}
</style>
</head>
<body>
  <div class="bg-glow-1"></div>
  <div class="bg-glow-2"></div>
  <div class="bg-grid"></div>

  <div class="header">
    <div class="brand-badge">
      {logo_tag}
      <div>
        <div class="brand-text">COOCA</div>
        <div class="brand-tag">Business Operating System for UMKM</div>
      </div>
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
      <span>📅 20 November 2026</span>
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
        762: 'kewajiban-nib-seller-marketplace-online-permendag',
        763: 'diskon-50-persen-biaya-layanan-e-commerce-umkm',
        764: 'aturan-label-nutri-level-bpom-makanan-minuman'
    }

    out_dir = os.path.join('public', 'images', 'blog')
    os.makedirs(out_dir, exist_ok=True)

    with sync_playwright() as p:
        browser = p.chromium.launch()
        page = browser.new_page(viewport={'width': 1536, 'height': 864})
        for id_, slug in slugs.items():
            out_path = os.path.join(out_dir, f'{slug}.jpg')
            card_info = CARDS_DATA[id_]
            html = render_html(card_info)
            page.set_content(html)
            page.wait_for_timeout(400)
            page.screenshot(path=out_path, quality=92, type='jpeg')
            size_kb = os.path.getsize(out_path) / 1024
            print(f'[{id_}] Rendered: {slug}.jpg ({size_kb:.1f} KB, 1536x864 px)')
        browser.close()

    print('All 3 Batch 17 cover images rendered successfully!')

if __name__ == '__main__':
    main()
