import os
import json

BASE_DIR = r"C:\laragon\www\cooca_core"

try:
    routes_data = json.load(open(os.path.join(BASE_DIR, "routes.json"), encoding="utf-8"))
except UnicodeDecodeError:
    routes_data = json.load(open(os.path.join(BASE_DIR, "routes.json"), encoding="utf-16"))

print("=== WhatsApp Routes ===")
for r in routes_data:
    if "whatsapp" in r.get("uri", "") or "whatsapp" in (r.get("name") or ""):
        print(f"  {r.get('method'):<10} /{r.get('uri'):<35} -> name: {r.get('name')}")

print("\n=== HRM Routes ===")
for r in routes_data:
    if "hrm" in r.get("uri", "") or "hrm" in (r.get("name") or "") or "employees" in r.get("uri", ""):
        print(f"  {r.get('method'):<10} /{r.get('uri'):<35} -> name: {r.get('name')}")
