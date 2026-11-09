import os
import json

SIDECARS_DIR = r"C:\Users\Ulfah Ramadhani\.gemini\config\sidecars"
INSTRUCTIONS_DIR = r"C:\laragon\www\cooca_core\scripts\social-automation\instructions"
os.makedirs(INSTRUCTIONS_DIR, exist_ok=True)

CONFIGS = [
    {
        "id": "cooca-feed-linkedin",
        "channel": "LinkedIn",
        "format": "Feed",
        "dimensions": "1080x1080",
        "commit_tag": "feed-linkedin",
        "asset_save_path": "storage/app/public/content-assets/linkedin-feed-...-1080x1080.png",
        "format_desc": "Single Static Image (1080 × 1080 px)",
    },
    {
        "id": "cooca-feed-facebook",
        "channel": "Facebook",
        "format": "Feed",
        "dimensions": "1080x1080",
        "commit_tag": "feed-facebook",
        "asset_save_path": "storage/app/public/content-assets/facebook-feed-...-1080x1080.png",
        "format_desc": "Single Static Image (1080 × 1080 px)",
    },
    {
        "id": "cooca-feed-instagram",
        "channel": "Instagram",
        "format": "Feed",
        "dimensions": "1080x1080",
        "commit_tag": "feed-instagram",
        "asset_save_path": "storage/app/public/content-assets/instagram-feed-...-1080x1080.png",
        "format_desc": "Single Static Image (1080 × 1080 px)",
    },
    {
        "id": "cooca-feed-tiktok",
        "channel": "TikTok",
        "format": "Feed",
        "dimensions": "1080x1080",
        "commit_tag": "feed-tiktok",
        "asset_save_path": "storage/app/public/content-assets/tiktok-feed-...-1080x1080.png",
        "format_desc": "Single Static Image (1080 × 1080 px)",
    },
    {
        "id": "cooca-carousel-facebook",
        "channel": "Facebook",
        "format": "Carousel",
        "dimensions": "1080x1080",
        "commit_tag": "carousel-facebook",
        "asset_save_path": "storage/app/public/admin/social-media-assets/carousel/facebook/slide-0{n}.png",
        "format_desc": "Multi-Slide Static Carousel (5 Slide, 1080 × 1080 px)",
    },
    {
        "id": "cooca-carousel-instagram",
        "channel": "Instagram",
        "format": "Carousel",
        "dimensions": "1080x1080",
        "commit_tag": "carousel-instagram",
        "asset_save_path": "storage/app/public/admin/social-media-assets/carousel/instagram/slide-0{n}.png",
        "format_desc": "Multi-Slide Static Carousel (5 Slide, 1080 × 1080 px)",
    },
    {
        "id": "cooca-carousel-linkedin",
        "channel": "LinkedIn",
        "format": "Carousel",
        "dimensions": "1080x1080",
        "commit_tag": "carousel-linkedin",
        "asset_save_path": "storage/app/public/admin/social-media-assets/carousel/linkedin/slide-0{n}.png",
        "format_desc": "Multi-Slide Static Carousel (5 Slide, 1080 × 1080 px)",
    },
    {
        "id": "cooca-carousel-tiktok",
        "channel": "TikTok",
        "format": "Carousel",
        "dimensions": "1080x1080",
        "commit_tag": "carousel-tiktok",
        "asset_save_path": "storage/app/public/admin/social-media-assets/carousel/tiktok/slide-0{n}.png",
        "format_desc": "Multi-Slide Static Carousel (5 Slide, 1080 × 1080 px)",
    },
    {
        "id": "cooca-daily-umkm-carousel",
        "channel": "Instagram",
        "format": "Portrait Carousel",
        "dimensions": "1080x1350",
        "commit_tag": "daily-umkm-carousel",
        "asset_save_path": "storage/app/public/admin/social-media-assets/carousel/instagram/slide-0{n}.png",
        "format_desc": "Multi-Slide Portrait Carousel (Maksimal 6 Slide, 1080 × 1350 px)",
    },
    {
        "id": "cooca-umkm-educational-social-media",
        "channel": "Instagram",
        "format": "Portrait Feed",
        "dimensions": "1080x1350",
        "commit_tag": "umkm-educational",
        "asset_save_path": "storage/app/public/content-assets/instagram-feed-...-1080x1350.png",
        "format_desc": "Single Static Portrait Image (1080 × 1350 px)",
    },
]

PROMPT_TEMPLATE = """# TASK: COOCA {channel} {format} Publisher ({automation_id})

Bertindak sebagai Senior Social Media Strategist, B2B/B2C Retail Analyst, dan Visual Content Creator untuk COOCA.
Panduan riset dan kepatuhan lengkap tersedia di: `scripts/social-automation/instructions/{automation_id}.md`

## 1. WORKFLOW EKSEKUSI
1. **Jalankan Dynamic Viral Research Engine:**
   ```bash
   python scripts/social-automation/deep_research_viral_engine.py --automation-id {automation_id} --output-json storage/app/public/content-assets/spec-{automation_id}.json
   ```
   Baca file spec JSON yang dihasilkan untuk mendapatkan topik riset, hook title, skor viral (wajib >= 85.0), dan copywriting caption.

2. **Generate Visual via Canva MCP (`canva_canva`):**
   - Panggil tool `create-design` Canva MCP menggunakan brief dan outline konten dari spec JSON.
   - Pantau status menggunakan tool `get-create-design-async-job` sampai status `completed`.
   - Ekspor halaman ke format PNG resolusi tinggi menggunakan tool `export-design`.
   - Unduh berkas PNG ke direktori penyimpanan proyek:
     `{asset_save_path}`

3. **Deploy Postingan ke Database:**
   ```bash
   php artisan cooca:deploy-standalone-post --automation-id {automation_id} --spec-json storage/app/public/content-assets/spec-{automation_id}.json
   ```

4. **Git Commit & Push ke Production:**
   ```bash
   git add storage/app/public/content-assets/ storage/app/public/admin/social-media-assets/ database/data/social_content/
   git commit -m "feat({commit_tag}): publish audience-driven viral content [$(date +%Y-%m-%d)]"
   git push origin main
   ```

## 2. ATURAN BRAND & VISUAL COOCA (WAJIB)
- **Logo Resmi COOCA (Wajib):** Wajib mencantumkan Logo Resmi COOCA (`public/assets/image/1785229034_logo_dark.png`) di bagian header atau sudut atas visual.

## 3. STRICT COMPLIANCE
- **Bebas Nama Usaha & Bebas Nama User:** 100% netral, dilarang mencantumkan nama bisnis fiktif seperti 'Toko Kamu' atau nama user tertentu.
- **Strict Zero Em-Dash:** Dilarang menggunakan karakter em-dash (—) atau en-dash (–). Gunakan tanda minus (-) atau titik dua (:).
- **Hanya Gambar Statis:** Dilarang video, animasi, atau motion graphic. Format: {format_desc}.
"""

for cfg in CONFIGS:
    sid = cfg["id"]
    sp = os.path.join(SIDECARS_DIR, sid, "sidecar.json")
    if not os.path.exists(sp):
        print(f"[SKIP] {sid} not found")
        continue

    with open(sp, "r", encoding="utf-8") as f:
        data = json.load(f)

    # 1. Preserve original full instruction
    old_prompt = data["args"][-1]
    inst_file = os.path.join(INSTRUCTIONS_DIR, f"{sid}.md")
    with open(inst_file, "w", encoding="utf-8") as f:
        f.write(old_prompt)

    # 2. Build concise prompt
    new_prompt = PROMPT_TEMPLATE.format(
        channel=cfg["channel"],
        format=cfg["format"],
        automation_id=sid,
        commit_tag=cfg["commit_tag"],
        asset_save_path=cfg["asset_save_path"],
        format_desc=cfg["format_desc"]
    ).strip()

    cron_expr = data["args"][0]
    
    # 3. Standardize args array: [cron, "agentapi", "new-conversation", "--model=pro", "--", new_prompt]
    data["args"] = [
        cron_expr,
        "agentapi",
        "new-conversation",
        "--model=pro",
        "--",
        new_prompt
    ]

    data["workspace_uris"] = ["file:///C:/laragon/www/cooca_core"]
    data["restart_policy"] = "always"

    with open(sp, "w", encoding="utf-8") as f:
        json.dump(data, f, indent=2, ensure_ascii=False)

    print(f"[SUCCESS] {sid}: Preserved {len(old_prompt)} chars -> New prompt {len(new_prompt)} chars (CommandLine Safe)")

print("\nAll sidecars successfully optimized to prevent Windows command line length overflow!")
