import os
import json
import csv

BASE_DIR = r"C:\laragon\www\cooca_core"

# Load routes.json
try:
    routes_data = json.load(open(os.path.join(BASE_DIR, "routes.json"), encoding="utf-8"))
except UnicodeDecodeError:
    routes_data = json.load(open(os.path.join(BASE_DIR, "routes.json"), encoding="utf-16"))

routes_by_name = {r.get("name"): r for r in routes_data if r.get("name")}
routes_by_uri = {r.get("uri"): r for r in routes_data if r.get("uri")}

print(f"Loaded {len(routes_data)} routes ({len(routes_by_name)} named)")

# Test lookup helper
def find_route(route_name, uri_fragment=None):
    if route_name in routes_by_name:
        return routes_by_name[route_name]
    if uri_fragment:
        for uri, r in routes_by_uri.items():
            if uri_fragment in uri:
                return r
    return None

def check_blade(blade_path):
    full_path = os.path.join(BASE_DIR, blade_path)
    exists = os.path.exists(full_path)
    line_count = 0
    if exists:
        with open(full_path, "r", encoding="utf-8", errors="ignore") as f:
            lines = f.readlines()
            line_count = len(lines)
    return exists, line_count

print("Route and blade checker initialized.")
