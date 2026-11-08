#!/usr/bin/env python3
import json

sidecars = [
    "cooca-carousel-linkedin",
    "cooca-feed-linkedin",
    "cooca-carousel-instagram",
    "cooca-feed-instagram",
    "cooca-daily-umkm-carousel",
    "cooca-carousel-facebook",
    "cooca-feed-facebook",
    "cooca-carousel-tiktok",
    "cooca-feed-tiktok",
    "cooca-umkm-educational-social-media"
]

rule_text = "6. **Format Media Statis (Anti-Video):** HANYA menghasilkan gambar statis (Single Static Image untuk Feed, Multi-Slide Static Images untuk Carousel). DILARANG generate video motion atau animasi apapun."

for sid in sidecars:
    path = f"C:/Users/Ulfah Ramadhani/.gemini/config/sidecars/{sid}/sidecar.json"
    with open(path, "r", encoding="utf-8-sig") as f:
        data = json.load(f)
    prompt = data["args"][-1]

    # Clean up artifacts
    prompt = prompt.replace("â€”", "-").replace("â€“", "-").replace("—", "-").replace("–", "-")
    prompt = prompt.replace("TikTok TikTok", "TikTok")
    prompt = prompt.replace("Single Feed Video/Image", "Single Static Image Feed")
    prompt = prompt.replace("Short-Form Retail FYP Producer", "TikTok Static Image Feed Designer")

    if "Format Media Statis" not in prompt:
        target = "5. **Bebas Em-dash:** Dilarang menggunakan karakter em-dash (-) maupun en-dash (-)."
        if target in prompt:
            prompt = prompt.replace(target, target + "\n" + rule_text)
        else:
            prompt = prompt.replace("## ATURAN KETAT KONTEN:\n", "## ATURAN KETAT KONTEN:\n" + rule_text + "\n")

    data["args"][-1] = prompt
    data["description"] = data["description"].replace("â€”", "-").replace("—", "-").replace("–", "-")

    with open(path, "w", encoding="utf-8") as f:
        json.dump(data, f, indent=2, ensure_ascii=False)

    print(f"Cleaned and updated: {sid}")
