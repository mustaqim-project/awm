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

TYPOGRAPHY_PROMPT_SECTION = """

---

# ATURAN TIPOGRAFI & FONT RESMI COOCA (PLUS JAKARTA SANS)

Seluruh pembuatan konten visual (baik via Canva MCP maupun canvas visual/CSS) WAJIB menggunakan font keluarga **Plus Jakarta Sans** sebagai tipografi resmi tunggal COOCA:
1. **Karakter & Alasan Pemilihan:**
   - Modern dan profesional.
   - Sangat nyaman dibaca dalam Bahasa Indonesia.
   - Memiliki karakter teknologi tanpa terlihat terlalu kaku.
   - Sangat cocok untuk headline, isi carousel, hingga angka nominal harga.
2. **Hierarki Tipografi Wajib:**
   - **Headline utama:** Plus Jakarta Sans (Bold / ExtraBold) - tegas, kuat, dan eye-catching.
   - **Subheadline:** Plus Jakarta Sans (SemiBold) - memperjelas konteks dengan anggun.
   - **Isi konten / body text:** Plus Jakarta Sans (Regular / Medium) - jarak baris proporsional (1.4-1.5) untuk kenyamanan baca di layar mobile.
   - **Angka & statistik:** Plus Jakarta Sans (Bold / ExtraBold) - menonjolkan nominal rupiah, persentase margin, atau poin angka.
   - **CTA (Call to Action):** Plus Jakarta Sans (Bold) - jelas dan mengarahkan aksi.
3. **Ketentuan Larangan:** Dilarang menggunakan font dekoratif murahan, script/handwriting, serif klasik kusam, atau font default generik yang tidak konsisten dengan brand identity COOCA.
"""

def update_sidecar_typography(sidecar_name):
    sidecar_path = os.path.join(SIDECARS_DIR, sidecar_name, "sidecar.json")
    if not os.path.exists(sidecar_path):
        print(f"[SKIP] {sidecar_name} not found")
        return

    with open(sidecar_path, "r", encoding="utf-8") as f:
        data = json.load(f)

    args = data.get("args", [])
    if len(args) == 6:
        prompt = args[5]
    else:
        prompt = args[-1]

    # Clean existing typography section if present
    prompt = re.sub(r"\n---\n\n# ATURAN TIPOGRAFI & FONT RESMI COOCA[\s\S]*?(?=\n---\n\n#|\Z)", "", prompt)

    # Insert right after COOCA BRAND IDENTITY & COLOR THEME RULES
    brand_header = "# COOCA BRAND IDENTITY & COLOR THEME RULES"
    if brand_header in prompt:
        # Find the next section boundary after brand_header
        idx = prompt.find(brand_header)
        next_boundary = prompt.find("\n---\n\n#", idx + len(brand_header))
        if next_boundary != -1:
            prompt = prompt[:next_boundary] + TYPOGRAPHY_PROMPT_SECTION + prompt[next_boundary:]
        else:
            prompt = prompt.rstrip() + TYPOGRAPHY_PROMPT_SECTION
    else:
        prompt = prompt.rstrip() + TYPOGRAPHY_PROMPT_SECTION

    if len(args) == 6:
        args[5] = prompt
    else:
        args[-1] = prompt

    data["args"] = args

    # Update description to include font rule
    desc = data.get("description", "")
    desc = re.sub(r"\s*\[Canva MCP \+ Tema Dark Blue & White \+ Logo COOCA[^\]]*\]", "", desc)
    desc = desc.rstrip(".") + " [Canva MCP + Dark Blue/White + Plus Jakarta Sans + Logo COOCA]."
    data["description"] = desc

    with open(sidecar_path, "w", encoding="utf-8") as f:
        json.dump(data, f, indent=2, ensure_ascii=False)

    print(f"[TYPOGRAPHY ADDED] {sidecar_name} successfully updated with Plus Jakarta Sans rules.")

for s in TARGET_SIDECARS:
    update_sidecar_typography(s)

print("\nDone adding typography rules to all 10 sidecars!")
