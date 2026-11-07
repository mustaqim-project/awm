import os
import re

view_dirs = [
    r"c:\laragon\www\cooca_core\resources\views\app\ai",
    r"c:\laragon\www\cooca_core\resources\views\app\ai-studio"
]

route_pattern = re.compile(r"route\(\s*['\"]([^'\"]+)['\"]")

view_routes = {}
for d in view_dirs:
    for root, dirs, fnames in os.walk(d):
        for f in fnames:
            if f.endswith('.blade.php'):
                p = os.path.join(root, f)
                rel = os.path.relpath(p, r"c:\laragon\www\cooca_core")
                with open(p, 'r', encoding='utf-8', errors='ignore') as fh:
                    c = fh.read()
                routes = route_pattern.findall(c)
                if routes:
                    view_routes[rel] = sorted(set(routes))

for k, v in sorted(view_routes.items()):
    print(f"=== {k} ===")
    for r in v:
        print(f"  - route: {r}")
