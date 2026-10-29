import json

with open('routes.json', 'r', encoding='utf-8') as f:
    routes = json.load(f)

print(f"Total routes in routes.json: {len(routes)}")
for r in routes:
    uri = r.get('uri', '')
    act = r.get('action', '')
    mw = r.get('middleware', [])
    if 'pos' in uri.lower() or 'Pos' in act or 'table' in uri.lower():
        print(f"{r.get('method'):<6} {uri:<50} -> {act}")
