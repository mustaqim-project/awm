import os
import re
import json
import glob

ROOT_DIR = r"C:\laragon\www\cooca_core"
SIDECARS_DIR = r"C:\Users\Ulfah Ramadhani\.gemini\config\sidecars"

print("--- 1. Enforcing White & Blue Theme, Dark Logo, Zero Green on deep_research_viral_engine.py ---")
viral_engine_path = os.path.join(ROOT_DIR, "scripts", "social-automation", "deep_research_viral_engine.py")
if os.path.exists(viral_engine_path):
    with open(viral_engine_path, "r", encoding="utf-8") as f:
        content = f.read()

    # Logo
    content = content.replace("1785229034_logo_light.png", "1785229034_logo_dark.png")
    # Replace green in accent colors and rules
    content = content.replace('"#10B981"', '"#0284C7"')
    content = content.replace("Emerald Green (#10B981)", "Cooca Blue (#2563EB)")
    content = content.replace("Emerald (#10B981)", "Cyan/Cooca Blue (#38BDF8)")
    content = content.replace("dan Emerald (#10B981)", "dan Sky Blue (#38BDF8)")
    
    # Update brand theme rules
    old_rules_pattern = re.compile(r'"rules":\s*\[\s*"[^"]+",\s*"[^"]+",\s*"[^"]+",\s*"[^"]+",\s*"[^"]+",\s*"[^"]+"\s*\]', re.DOTALL)
    new_rules = '''"rules": [
                "Identitas Brand Wajib: Kombinasi Putih (#FFFFFF) dan Biru (#2563EB, #1E40AF, #38BDF8, #0A1128)",
                "DILARANG KERAS MENGGUNAKAN WARNA HIJAU (Strictly Zero Green / No Emerald)",
                "Tipografi dan teks utama wajib kontras tajam menggunakan Plus Jakarta Sans di seluruh elemen",
                "Wajib menggunakan Logo Resmi COOCA Dark (public/assets/image/1785229034_logo_dark.png)",
                "Aksen visual hanya menggunakan spektrum Biru: Cooca Blue (#2563EB), Cyan Blue (#38BDF8), Navy Blue (#1E3A8A)",
                "Dilarang background atau aksen warna-warni non-brand di luar putih dan biru"
            ]'''
    content = old_rules_pattern.sub(new_rules, content)

    # Prompt brief
    content = content.replace(
        "Theme: Dark Blue background (#070B19), crisp White typography (#FFFFFF)",
        "Theme: White & Blue identity (Pure White #FFFFFF & Cooca Blue #2563EB, #0A1128, strictly NO green)"
    )
    content = content.replace("Official COOCA Logo included at top", "Official COOCA Dark Logo (1785229034_logo_dark.png) included at top")

    with open(viral_engine_path, "w", encoding="utf-8") as f:
        f.write(content)
    print("  [OK] deep_research_viral_engine.py updated successfully.")

print("--- 2. Updating DeployStandaloneSocialPostCommand.php ---")
deploy_cmd_path = os.path.join(ROOT_DIR, "app", "Console", "Commands", "DeployStandaloneSocialPostCommand.php")
if os.path.exists(deploy_cmd_path):
    with open(deploy_cmd_path, "r", encoding="utf-8") as f:
        cmd_content = f.read()
    cmd_content = cmd_content.replace("1785229034_logo_light.png", "1785229034_logo_dark.png")
    with open(deploy_cmd_path, "w", encoding="utf-8") as f:
        f.write(cmd_content)
    print("  [OK] DeployStandaloneSocialPostCommand.php updated.")

print("--- 3. Updating All Sidecar JSONs in .gemini/config/sidecars/ ---")
sidecar_files = glob.glob(os.path.join(SIDECARS_DIR, "*", "sidecar.json"))
for sc_file in sidecar_files:
    try:
        with open(sc_file, "r", encoding="utf-8") as f:
            sc_data = json.load(f)
        
        changed = False
        # Update description if needed
        if "description" in sc_data and isinstance(sc_data["description"], str):
            orig = sc_data["description"]
            sc_data["description"] = sc_data["description"].replace("1785229034_logo_light.png", "1785229034_logo_dark.png")
            sc_data["description"] = sc_data["description"].replace("Dark Blue/White", "White & Blue (No Green)")
            if sc_data["description"] != orig:
                changed = True

        # Update args prompt
        if "args" in sc_data and isinstance(sc_data["args"], list):
            for i in range(len(sc_data["args"])):
                if isinstance(sc_data["args"][i], str):
                    orig = sc_data["args"][i]
                    sc_data["args"][i] = sc_data["args"][i].replace("1785229034_logo_light.png", "1785229034_logo_dark.png")
                    sc_data["args"][i] = sc_data["args"][i].replace(
                        "Emerald Green (`#10B981`) untuk badge solusi/sukses,",
                        "Royal/Cyan Blue (`#2563EB`, `#38BDF8`) untuk badge solusi/sukses (DILARANG HIJAU),"
                    )
                    sc_data["args"][i] = sc_data["args"][i].replace(
                        "Latar belakang **Dark Blue / Deep Navy** (`#070B19` atau `#0A1128`) dan teks utama **White** (`#FFFFFF`). Dilarang background putih polos atau warna generik non-brand.",
                        "Identitas Brand COOCA: Kombinasi **Putih** (`#FFFFFF`) dan **Biru** (`#2563EB`, `#0A1128`, `#38BDF8`). **DILARANG MENGGUNAKAN WARNA HIJAU**."
                    )
                    sc_data["args"][i] = sc_data["args"][i].replace(
                        "Wajib mencantumkan Logo Resmi COOCA (`public/assets/image/1785229034_logo_light.png`)",
                        "Wajib mencantumkan Logo Resmi COOCA Dark (`public/assets/image/1785229034_logo_dark.png`)"
                    )
                    sc_data["args"][i] = sc_data["args"][i].replace(
                        "Logo Resmi COOCA (`public/assets/image/1785229034_logo_light.png`)",
                        "Logo Resmi COOCA Dark (`public/assets/image/1785229034_logo_dark.png`)"
                    )
                    if sc_data["args"][i] != orig:
                        changed = True

        if changed:
            with open(sc_file, "w", encoding="utf-8") as f:
                json.dump(sc_data, f, indent=2, ensure_ascii=False)
            print(f"  [OK] Sidecar updated: {os.path.basename(os.path.dirname(sc_file))}")
    except Exception as e:
        print(f"  [ERR] Failed updating {sc_file}: {e}")

print("--- 4. Updating All Instructions Markdown files ---")
instr_files = glob.glob(os.path.join(ROOT_DIR, "scripts", "social-automation", "instructions", "*.md"))
for ifile in instr_files:
    try:
        with open(ifile, "r", encoding="utf-8") as f:
            icontent = f.read()

        icontent = icontent.replace("1785229034_logo_light.png", "1785229034_logo_dark.png")
        icontent = re.sub(r'-\s*\*\*Emerald Green\*\*\s*\(`#[a-fA-F0-9]+`,\s*`#[a-fA-F0-9]+`\)[^\n]*',
                          '- **Cooca Blue / Electric Blue** (`#2563EB`, `#38BDF8`) untuk badge solusi terverifikasi, checklist keberhasilan, dan ROI positif (DILARANG HIJAU).', icontent)
        icontent = icontent.replace("Emerald Green (`#10B981`)", "Cooca Blue (`#2563EB`)")
        icontent = icontent.replace("`#10B981`", "`#2563EB`")
        icontent = icontent.replace(
            "Latar belakang **Dark Blue / Deep Navy** (`#070B19` atau `#0A1128`) dan teks utama **White** (`#FFFFFF`). Dilarang background putih polos atau warna generik non-brand.",
            "Identitas Brand COOCA: **Putih** (`#FFFFFF`) dan **Biru** (`#2563EB`, `#0A1128`, `#38BDF8`). **DILARANG KERAS MENGGUNAKAN WARNA HIJAU**."
        )

        with open(ifile, "w", encoding="utf-8") as f:
            f.write(icontent)
        print(f"  [OK] Instruction updated: {os.path.basename(ifile)}")
    except Exception as e:
        print(f"  [ERR] Failed updating {ifile}: {e}")

print("--- 5. Updating Render CJS Scripts ---")
cjs_files = [
    "render_facebook_feed_infographic.cjs",
    "render_instagram_feed_infographic.cjs",
    "render_linkedin_feed_infographic.cjs",
    "render-feed-image.cjs",
    "generate_umkm_carousel.cjs",
    "generate_linkedin_retail_governance.cjs"
]
for cjs in cjs_files:
    cpath = os.path.join(ROOT_DIR, "scripts", "social-automation", cjs)
    if os.path.exists(cpath):
        with open(cpath, "r", encoding="utf-8") as f:
            ccontent = f.read()

        # Update logo
        ccontent = ccontent.replace("1785229034_logo_light.png", "1785229034_logo_dark.png")
        # Replace green colors with Cooca Blue / Sky Blue
        ccontent = ccontent.replace("#10B981", "#2563EB")
        ccontent = ccontent.replace("#10b981", "#2563eb")
        ccontent = ccontent.replace("#34D399", "#38BDF8")
        ccontent = ccontent.replace("#34d399", "#38bdf8")
        ccontent = ccontent.replace("rgba(16, 185, 129", "rgba(37, 99, 235")
        
        # Ensure logo-dark has a clean white pill container if placed in header on dark bg
        if ".brand-logo {" in ccontent and "background:" not in ccontent.split(".brand-logo {")[1][:100]:
            ccontent = ccontent.replace(
                ".brand-logo {",
                ".brand-logo {\n  background: #FFFFFF;\n  padding: 4px 10px;\n  border-radius: 8px;\n  box-shadow: 0 2px 6px rgba(0,0,0,0.12);"
            )

        with open(cpath, "w", encoding="utf-8") as f:
            f.write(ccontent)
        print(f"  [OK] Render script updated: {cjs}")

print("--- 6. Updating sidecar automation generator scripts ---")
gen_scripts = [
    "optimize_sidecars_commandline_length.py",
    "add_typography_rules_to_sidecars.py",
    "fix_sidecars_structure.py",
    "update_all_feed_carousel_automations.py",
    "build_tiktok_feed.py"
]
for gs in gen_scripts:
    gpath = os.path.join(ROOT_DIR, "scripts", "social-automation", gs)
    if os.path.exists(gpath):
        with open(gpath, "r", encoding="utf-8") as f:
            gcontent = f.read()
        gcontent = gcontent.replace("1785229034_logo_light.png", "1785229034_logo_dark.png")
        gcontent = gcontent.replace("#10B981", "#2563EB")
        gcontent = gcontent.replace("Emerald Green (`#10B981`)", "Cooca Blue (`#2563EB`)")
        with open(gpath, "w", encoding="utf-8") as f:
            f.write(gcontent)
        print(f"  [OK] Generator script updated: {gs}")

print("\nALL FILES SUCCESSFULLY UPDATED TO WHITE & BLUE (ZERO GREEN) AND LOGO_DARK.PNG!")
