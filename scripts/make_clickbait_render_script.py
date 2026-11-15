# scripts/make_clickbait_render_script.py
import json

from gen_clickbait_part1 import get_articles_part1
from gen_clickbait_part2 import get_articles_part2
from gen_clickbait_part3 import get_articles_part3
from gen_clickbait_part4 import get_articles_part4

all_articles = (
    get_articles_part1() +
    get_articles_part2() +
    get_articles_part3() +
    get_articles_part4()
)

icons = [
    '3d_shop_store.png', '3d_rocket.png', '3d_growth_arrow.png', '3d_trophy.png',
    '3d_shopping_cart.png', '3d_target.png', '3d_gold_star.png', '3d_pie_chart.png',
    '3d_bank_building.png', '3d_shopping_bag.png'
]

cards = []
for idx, a in enumerate(all_articles):
    icon = icons[idx % len(icons)]
    
    # generate punchy clickbait title for the cover
    slug = a['slug']
    cat = a['category']
    pk = a['primary_keyword'].upper()
    
    cards.append({
        'slug': slug,
        'category': cat,
        'title': a['title'].split(':')[0],  # Punchy Clickbait Kicker/Title
        'subtitle': a['title'].split(':')[1].strip() if ':' in a['title'] else a['meta_description'][:90],
        'icon3d': icon,
        'statBadge': 'COOCA BOS',
        'statSub': a['primary_keyword'][:24].upper(),
        'kicker': 'INVESTIGASI BISNIS UMKM',
        'features': [
            a['primary_keyword'].title()[:28],
            'Real-Time Cloud Sync',
            'Zero Em-Dash Verified',
            'Standar Apple Bento Grid'
        ],
        'gradientAccent': 'rgba(37, 99, 235, 0.45)',
        'accentColor': '#38BDF8'
    })

cards_json = json.dumps(cards, indent=2, ensure_ascii=False)

cjs_content = f"""// scripts/render_clickbait_covers.cjs
const {{ chromium }} = require('c:/laragon/www/cooca_core/node_modules/@playwright/test');
const fs = require('fs');
const path = require('path');

const ROOT_DIR = 'C:/laragon/www/cooca_core';
const LOGO_PATH = path.join(ROOT_DIR, 'public/assets/image/cooca-logo-landscape.png');
const ASSETS_3D_DIR = path.join(ROOT_DIR, 'public/admin/social-media-assets/pinterest-showcase');
const OUTPUT_DIR = path.join(ROOT_DIR, 'public/images/blog');

function getBase64Image(filePath) {{
  if (!fs.existsSync(filePath)) return '';
  const ext = path.extname(filePath).replace('.', '') || 'png';
  const buf = fs.readFileSync(filePath);
  return `data:image/${{ext}};base64,${{buf.toString('base64')}}`;
}}

const logoBase64 = getBase64Image(LOGO_PATH);

const CARDS = {cards_json};

async function generateAllClickbaitCovers() {{
  console.log(`[Clickbait Bento Engine] Starting generation of ${{CARDS.length}} custom editorial covers (1200x675)...`);
  
  if (!fs.existsSync(OUTPUT_DIR)) {{
    fs.mkdirSync(OUTPUT_DIR, {{ recursive: true }});
  }}

  const browser = await chromium.launch({{ headless: true }});
  const context = await browser.newContext({{
    viewport: {{ width: 1200, height: 675 }},
    deviceScaleFactor: 1
  }});
  const page = await context.newPage();

  for (let i = 0; i < CARDS.length; i++) {{
    const card = CARDS[i];
    const icon3dPath = path.join(ASSETS_3D_DIR, card.icon3d);
    const icon3dBase64 = getBase64Image(icon3dPath);

    const html = `<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@600;700&display=swap');

* {{
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}}

body {{
  width: 1200px;
  height: 675px;
  background-color: #070B19;
  font-family: 'Plus Jakarta Sans', sans-serif;
  color: #FFFFFF;
  position: relative;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}}

/* Ambient Background Lights */
body::before {{
  content: '';
  position: absolute;
  top: -150px;
  right: -100px;
  width: 650px;
  height: 650px;
  background: radial-gradient(circle, ${{card.gradientAccent}} 0%, rgba(37, 99, 235, 0) 70%);
  filter: blur(60px);
  z-index: 1;
}}

body::after {{
  content: '';
  position: absolute;
  bottom: -150px;
  left: -100px;
  width: 550px;
  height: 550px;
  background: radial-gradient(circle, rgba(56, 189, 248, 0.25) 0%, rgba(7, 11, 25, 0) 70%);
  filter: blur(80px);
  z-index: 1;
}}

/* Fine Grid Pattern */
.grid-overlay {{
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-image: 
    linear-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
  background-size: 40px 40px;
  z-index: 2;
  pointer-events: none;
}}

/* Main Content Wrapper */
.content {{
  position: relative;
  z-index: 3;
  width: 100%;
  height: 100%;
  padding: 48px 56px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}}

/* Header */
.header {{
  display: flex;
  align-items: center;
  justify-content: space-between;
}}

.brand-wrapper {{
  display: flex;
  align-items: center;
  gap: 16px;
}}

.brand-logo {{
  height: 44px;
  width: auto;
  object-fit: contain;
  filter: drop-shadow(0 4px 12px rgba(0, 0, 0, 0.4));
}}

.platform-pill {{
  display: flex;
  align-items: center;
  gap: 8px;
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.12);
  backdrop-filter: blur(12px);
  padding: 8px 16px;
  border-radius: 9999px;
  font-size: 13px;
  font-weight: 600;
  letter-spacing: 0.04em;
  color: #E2E8F0;
}}

.status-dot {{
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background-color: #10B981;
  box-shadow: 0 0 10px #10B981;
}}

/* Main Hero Stage */
.main-stage {{
  display: grid;
  grid-template-columns: 1fr 340px;
  gap: 40px;
  align-items: center;
  margin-top: 10px;
}}

.text-col {{
  display: flex;
  flex-direction: column;
  gap: 14px;
}}

.kicker {{
  font-family: 'Space Grotesk', sans-serif;
  font-size: 13px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.16em;
  color: #38BDF8;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}}

.kicker::before {{
  content: '';
  display: inline-block;
  width: 20px;
  height: 2px;
  background: #38BDF8;
}}

.main-title {{
  font-size: 40px;
  font-weight: 800;
  line-height: 1.15;
  letter-spacing: -0.025em;
  color: #FFFFFF;
  text-shadow: 0 2px 20px rgba(0, 0, 0, 0.6);
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}}

.subtitle {{
  font-size: 17px;
  font-weight: 500;
  line-height: 1.45;
  color: #94A3B8;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  max-width: 680px;
}}

/* Features Grid */
.features-grid {{
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 10px 16px;
  margin-top: 6px;
  max-width: 660px;
}}

.feat-item {{
  display: flex;
  align-items: center;
  gap: 10px;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 10px;
  padding: 8px 14px;
  font-size: 13px;
  font-weight: 600;
  color: #E2E8F0;
}}

.check-icon {{
  color: #38BDF8;
  font-weight: 800;
}}

/* Visual 3D Showcase Column */
.visual-col {{
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  position: relative;
}}

.visual-3d-img {{
  width: 220px;
  height: 220px;
  object-fit: contain;
  filter: drop-shadow(0 20px 35px rgba(0, 0, 0, 0.6));
  animation: float 4s ease-in-out infinite;
}}

.stat-badge {{
  margin-top: 10px;
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.14);
  backdrop-filter: blur(16px);
  padding: 8px 18px;
  border-radius: 12px;
  font-family: 'Space Grotesk', sans-serif;
  font-size: 20px;
  font-weight: 800;
  color: #FFFFFF;
  text-align: center;
  letter-spacing: -0.01em;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
}}

.stat-sub {{
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: ${{card.accentColor}};
  margin-top: 2px;
}}

/* Footer */
.footer {{
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
  padding-top: 18px;
}}

.footer-domain {{
  font-family: 'Space Grotesk', sans-serif;
  font-size: 14px;
  font-weight: 700;
  letter-spacing: 0.08em;
  color: #38BDF8;
}}

.footer-trust {{
  font-size: 12px;
  font-weight: 500;
  color: rgba(148, 163, 184, 0.8);
}}
</style>
</head>
<body>
<div class="grid-overlay"></div>
<div class="content">
  <div class="header">
    <div class="brand-wrapper">
      <img class="brand-logo" src="${{logoBase64}}" alt="COOCA Logo">
    </div>
    <div class="platform-pill">
      <div class="status-dot"></div>
      ${{card.category}}
    </div>
  </div>

  <div class="main-stage">
    <div class="text-col">
      <div class="kicker">${{card.kicker}}</div>
      <h1 class="main-title">${{card.title}}</h1>
      <p class="subtitle">${{card.subtitle}}</p>
      <div class="features-grid">
        ${{card.features.map(f => `
          <div class="feat-item">
            <span class="check-icon">&#10003;</span>
            <span>${{f}}</span>
          </div>
        `).join('')}}
      </div>
    </div>

    <div class="visual-col">
      <img class="visual-3d-img" src="${{icon3dBase64}}" alt="${{card.title}}">
      <div class="stat-badge">${{card.statBadge}}</div>
      <div class="stat-sub">${{card.statSub}}</div>
    </div>
  </div>

  <div class="footer">
    <div class="footer-domain">cooca.id</div>
    <div class="footer-trust">Business Operating System Terpadu &bull; Dibuat untuk UMKM Indonesia</div>
  </div>
</div>
</body>
</html>`;

    await page.setContent(html, {{ waitUntil: 'networkidle' }});
    const outputFilename = `cooca-${{card.slug}}.jpg`;
    const outputPath = path.join(OUTPUT_DIR, outputFilename);

    await page.screenshot({{
      path: outputPath,
      type: 'jpeg',
      quality: 90
    }});

    console.log(`[${{i + 1}}/${{CARDS.length}}] Rendered: ${{outputFilename}}`);
  }}

  await browser.close();
  console.log(`\\nAll ${{CARDS.length}} custom editorial covers successfully rendered into public/images/blog/!`);
}}

generateAllClickbaitCovers().catch(err => {{
  console.error('Fatal rendering error:', err);
  process.exit(1);
}});
"""

with open('scripts/render_clickbait_covers.cjs', 'w', encoding='utf-8') as f:
    f.write(cjs_content)

print("scripts/render_clickbait_covers.cjs generated successfully!")
