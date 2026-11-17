import os
import csv
import json

BASE_DIR = r"C:\laragon\www\cooca_core"

matrix_path = os.path.join(BASE_DIR, "documentation", "feature-readiness-audit", "feature-readiness-matrix.csv")
with open(matrix_path, "r", encoding="utf-8") as f:
    reader = csv.DictReader(f)
    matrix_rows = list(reader)

print(f"Matrix rows count: {len(matrix_rows)}")

for r in matrix_rows:
    print(f"ID: {r['feature_id']:>2} | Cat: {r['category']:<20} | Name: {r['feature_name']:<40} | Blade: {r['blade_view']:<45} | Route: {r['route_name']:<30} | Status: {r['readiness_status']}")
