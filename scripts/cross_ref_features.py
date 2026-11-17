import os
import csv
import json

BASE_DIR = r"C:\laragon\www\cooca_core"

# 1. Parse categories A to L from ORIGINAL_REQUEST.md
orig_path = r"C:\Users\Ulfah Ramadhani\.gemini\antigravity\brain\e896df33-9277-4c7c-926c-4120819508e3\scratch\.agents\teamwork\ORIGINAL_REQUEST.md"
with open(orig_path, "r", encoding="utf-8") as f:
    orig_text = f.read()

categories_block = orig_text.split("### R3. Output Organization & Manifest")[1].split("### R4. Technical Reports Generation")[0]

cat_map = {}
bt = chr(96)
all_req = []
for line in categories_block.strip().split("\n"):
    line = line.strip()
    if line.startswith("- " + bt) and "images:" in line:
        parts = line.split(bt)
        cat_dir = parts[1].strip("/")
        files = [p.strip() for p in parts[3:] if p.endswith(".png")]
        cat_map[cat_dir] = files
        for f in files:
            all_req.append((cat_dir, f))

# 2. Parse feature-readiness-matrix.csv
matrix_path = os.path.join(BASE_DIR, "documentation", "feature-readiness-audit", "feature-readiness-matrix.csv")
with open(matrix_path, "r", encoding="utf-8") as f:
    reader = csv.DictReader(f)
    matrix = list(reader)

print(f"Total requested filenames: {len(all_req)}")
print(f"Total matrix items: {len(matrix)}")

# Let's inspect the names and filenames in both
for cat, files in cat_map.items():
    print(f"\n--- Category {cat} ({len(files)} files) ---")
    for f in files:
        print(f"  {f}")
