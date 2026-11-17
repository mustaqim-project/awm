import os
import re
import csv
import json

BASE_DIR = r"C:\laragon\www\cooca_core"

# 1. Parse the 90 features from ORIGINAL_REQUEST.md
orig_path = r"C:\Users\Ulfah Ramadhani\.gemini\antigravity\brain\e896df33-9277-4c7c-926c-4120819508e3\scratch\.agents\teamwork\ORIGINAL_REQUEST.md"
with open(orig_path, "r", encoding="utf-8") as f:
    orig_text = f.read()

categories_block = orig_text.split("### R3. Output Organization & Manifest")[1].split("### R4. Technical Reports Generation")[0]

cat_map = {}
bt = chr(96)
all_requested_features = []
for line in categories_block.strip().split("\n"):
    line = line.strip()
    if line.startswith("- " + bt) and "images:" in line:
        parts = line.split(bt)
        cat_dir = parts[1].strip("/")
        files = [p.strip() for p in parts[3:] if p.endswith(".png")]
        cat_map[cat_dir] = files
        for f in files:
            all_requested_features.append((cat_dir, f))

print(f"Total requested features: {len(all_requested_features)}")

# 2. Check matrix
matrix_path = os.path.join(BASE_DIR, "documentation", "feature-readiness-audit", "feature-readiness-matrix.csv")
with open(matrix_path, "r", encoding="utf-8") as f:
    reader = csv.DictReader(f)
    matrix_rows = list(reader)

print(f"Matrix rows: {len(matrix_rows)}")

# Print sample mapping check
print("\n--- Requested Features per category ---")
for cat, files in cat_map.items():
    print(f"\nCategory: {cat} ({len(files)} features)")
    for i, fn in enumerate(files, 1):
        print(f"  {i:>2}. {fn}")

# Check build_ui_renders.py output filenames
with open(os.path.join(BASE_DIR, "scripts", "build_ui_renders.py"), "r", encoding="utf-8") as f:
    bu_text = f.read()

saved_files = re.findall(r"OUTPUT_DIR,\s*[\"']([^\"']+)[\"']", bu_text)
print("\nFiles mentioned in build_ui_renders.py:", set(saved_files))
