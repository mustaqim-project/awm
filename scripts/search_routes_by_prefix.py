import os
import json

BASE_DIR = r"C:\laragon\www\cooca_core"

try:
    routes_data = json.load(open(os.path.join(BASE_DIR, "routes.json"), encoding="utf-8"))
except UnicodeDecodeError:
    routes_data = json.load(open(os.path.join(BASE_DIR, "routes.json"), encoding="utf-16"))

def search_routes(prefix):
    matches = [r for r in routes_data if r.get("uri", "").startswith(prefix) or (r.get("name") and r.get("name").startswith(prefix))]
    print(f"\n=== Routes matching '{prefix}' ({len(matches)}) ===")
    for m in matches:
        print(f"  {m.get('method'):<10} /{m.get('uri'):<35} -> name: {m.get('name')}")

search_routes("customer")
search_routes("storefront")
search_routes("whatsapp")
search_routes("hrm")
search_routes("finance")
