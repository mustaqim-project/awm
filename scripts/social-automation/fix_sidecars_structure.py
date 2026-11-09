import os
import json
import re

SIDECARS_DIR = r"C:\Users\Ulfah Ramadhani\.gemini\config\sidecars"

TARGET_SIDECARS = [
    "cooca-feed-facebook",
    "cooca-feed-instagram",
    "cooca-feed-linkedin",
    "cooca-feed-tiktok",
    "cooca-carousel-facebook",
    "cooca-carousel-instagram",
    "cooca-carousel-linkedin",
    "cooca-carousel-tiktok",
    "cooca-daily-umkm-carousel",
    "cooca-umkm-educational-social-media"
]

CANVA_AND_BRAND_PROMPT_SECTION = """

---

# MANDATORY CANVA MCP DESIGN WORKFLOW

Untuk menghasilkan aset visual grafis (baik Single Static Feed 1080x1080 px maupun Multi-Slide Carousel 1080x1080 / 1080x1350 px), agent **WAJIB menggunakan integrasi resmi Canva MCP (`canva_canva`)**:
1. **Panggil Tool `create-design` Canva MCP:** Kirimkan brief visual komprehensif mencakup judul, subjudul, poin edukasi per slide, serta panduan tata letak. Sertakan outline terstruktur (`sections`) untuk format carousel multi-halaman.
2. **Polling Status Pembuatan Desain:** Pantau status job menggunakan tool `get-create-design-async-job` bersama `continuation_token` hingga desain Canva berstatus `completed` dan menghasilkan `design_id` serta tautan edit/view.
3. **Ekspor Resolusi Tinggi:** Gunakan tool `export-design` untuk mengekspor halaman desain ke berkas format PNG lossless beresolusi tinggi tanpa penurunan kualitas gambar.
4. **Unduh Aset ke Path Penyimpanan Proyek:**
   - **Feed (1 Gambar):** Simpan ke direktori `storage/app/public/content-assets/{channel}-feed-...-1080x1080.png`
   - **Carousel (Multi-Slide):** Simpan secara berurutan ke `storage/app/public/admin/social-media-assets/carousel/{channel}/slide-0{n}.png`

---

# COOCA BRAND IDENTITY & COLOR THEME RULES

Setiap desain grafis yang dibuat melalui Canva MCP atau canvas visual **WAJIB mengikuti standar identitas brand resmi COOCA**:
1. **Palet Warna Wajib (Dark Blue & White):**
   - **Latar Belakang / Background:** Wajib **Dark Blue / Deep Navy** (`#070B19`, `#0A1128`, atau `#0F172A`). DILARANG KERAS menggunakan latar belakang putih polos murahan, abu-abu kusam, atau warna-warni yang merusak estetika premium dark-tech Cooca.
   - **Tipografi & Konten Utama:** Wajib **White** (`#FFFFFF`, `#F8FAFC`). Kontras tinggi, tegas, tajam, dan mudah dibaca secara nyaman pada layar smartphone.
   - **Aksen Visual Sekunder Resmi COOCA:**
     - **Electric / Cyan Blue** (`#38BDF8`, `#2563EB`) untuk tombol, pill, dan penyorot konteks.
     - **Emerald Green** (`#10B981`, `#34D399`) untuk badge solusi terverifikasi, checklist keberhasilan, dan ROI positif.
     - **Crimson Alert** (`#EF4444`, `#F87171`) untuk indikator bahaya, titik celah kebocoran meja kasir, atau selisih stok.
2. **Penggunaan Logo Resmi COOCA (Wajib):**
   - Setiap aset visual (Feed 1 gambar atau setiap slide Carousel) **WAJIB mencantumkan Logo Resmi COOCA** (`public/assets/image/1785229034_logo_light.png` / versi putih transparan untuk background gelap) di bagian header atau sudut atas visual.
   - Logo harus tampil bersih, proporsional, dan memiliki kontras tinggi di atas latar belakang Dark Blue.
"""

def fix_sidecar(sidecar_name):
    sidecar_path = os.path.join(SIDECARS_DIR, sidecar_name, "sidecar.json")
    if not os.path.exists(sidecar_path):
        print(f"[SKIP] {sidecar_name} not found")
        return

    with open(sidecar_path, "r", encoding="utf-8") as f:
        data = json.load(f)

    # 1. Fix workspace_uris
    data["workspace_uris"] = ["file:///C:/laragon/www/cooca_core"]

    # 2. Fix args
    args = data.get("args", [])
    if len(args) == 6:
        # Restore delimiter
        args[4] = "--"
        prompt = args[5]
    elif len(args) == 5:
        # args[4] is prompt
        prompt = args[4]
    else:
        prompt = args[-1]

    # Clean existing canva and brand sections from prompt to avoid duplication
    prompt = re.sub(r"\n---\n\n# MANDATORY CANVA MCP DESIGN WORKFLOW[\s\S]*?(?=\n---\n\n#|\Z)", "", prompt)
    prompt = re.sub(r"\n---\n\n# COOCA BRAND IDENTITY & COLOR THEME RULES[\s\S]*?(?=\n---\n\n#|\Z)", "", prompt)

    # Insert section right before # FINAL DECISION or append to prompt
    if "\n---\n\n# FINAL DECISION" in prompt:
        prompt = prompt.replace("\n---\n\n# FINAL DECISION", CANVA_AND_BRAND_PROMPT_SECTION + "\n\n---\n\n# FINAL DECISION")
    elif "\n---\n\n## FINAL DECISION" in prompt:
        prompt = prompt.replace("\n---\n\n## FINAL DECISION", CANVA_AND_BRAND_PROMPT_SECTION + "\n\n---\n\n## FINAL DECISION")
    elif "\n---\n\n# 12. STRICT CONTENT RULES" in prompt:
        prompt = prompt.replace("\n---\n\n# 12. STRICT CONTENT RULES", CANVA_AND_BRAND_PROMPT_SECTION + "\n\n---\n\n# 12. STRICT CONTENT RULES")
    elif "\n---\n\n# 15. STRICT RULES" in prompt:
        prompt = prompt.replace("\n---\n\n# 15. STRICT RULES", CANVA_AND_BRAND_PROMPT_SECTION + "\n\n---\n\n# 15. STRICT RULES")
    else:
        prompt = prompt.rstrip() + CANVA_AND_BRAND_PROMPT_SECTION

    if len(args) == 6:
        args[5] = prompt
    else:
        args[-1] = prompt

    data["args"] = args

    # 3. Update description
    desc = data.get("description", "")
    desc = re.sub(r"\s*\[Canva MCP \+ Tema Dark Blue & White \+ Logo COOCA\]", "", desc)
    desc = desc.rstrip(".") + " [Canva MCP + Tema Dark Blue & White + Logo COOCA]."
    data["description"] = desc

    with open(sidecar_path, "w", encoding="utf-8") as f:
        json.dump(data, f, indent=2, ensure_ascii=False)

    print(f"[FIXED] {sidecar_name} args and prompt verified.")

for s in TARGET_SIDECARS:
    fix_sidecar(s)

print("\nDone fixing all 10 sidecars!")
