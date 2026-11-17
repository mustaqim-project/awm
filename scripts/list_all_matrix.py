import os
import csv

BASE_DIR = r"C:\laragon\www\cooca_core"
matrix_path = os.path.join(BASE_DIR, "documentation", "feature-readiness-audit", "feature-readiness-matrix.csv")
with open(matrix_path, "r", encoding="utf-8") as f:
    reader = csv.DictReader(f)
    matrix = list(reader)

for r in matrix:
    print(f"{r['feature_id']:>2}. [{r['category']}] {r['feature_name']} -> {r['blade_view']} ({r['route_name']})")
