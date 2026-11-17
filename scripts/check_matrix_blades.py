import os
import json
import csv

BASE_DIR = r"C:\laragon\www\cooca_core"

# Load matrix
matrix_path = os.path.join(BASE_DIR, "documentation", "feature-readiness-audit", "feature-readiness-matrix.csv")
with open(matrix_path, "r", encoding="utf-8") as f:
    reader = csv.DictReader(f)
    matrix = list(reader)

# Load routes.json
try:
    routes_data = json.load(open(os.path.join(BASE_DIR, "routes.json"), encoding="utf-8"))
except UnicodeDecodeError:
    routes_data = json.load(open(os.path.join(BASE_DIR, "routes.json"), encoding="utf-16"))

routes_by_name = {r.get("name"): r for r in routes_data if r.get("name")}

# Check each matrix row blade file
missing_blades = []
existing_blades = []
for r in matrix:
    b = r["blade_view"]
    full_path = os.path.join(BASE_DIR, b)
    if not os.path.exists(full_path):
        missing_blades.append((r["feature_id"], r["feature_name"], b))
    else:
        existing_blades.append((r["feature_id"], r["feature_name"], b))

print(f"Total matrix items: {len(matrix)}")
print(f"Existing Blade files: {len(existing_blades)}")
print(f"Missing Blade files: {len(missing_blades)}")
if missing_blades:
    for m in missing_blades:
        print("  Missing:", m)
