import re

with open("resources/views/layouts/partials/sidebar.blade.php", "r", encoding="utf-8") as f:
    text = f.read()

route_calls = sorted(set(re.findall(r"route\(['\"]([^'\"]+)['\"]", text)))
print(f"Total unique route() calls in sidebar: {len(route_calls)}")
for i, r in enumerate(route_calls, 1):
    print(f"  {i:>2}. {r}")
