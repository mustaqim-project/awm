import json

try:
    data = json.load(open('routes.json', encoding='utf-8'))
except UnicodeDecodeError:
    data = json.load(open('routes.json', encoding='utf-16'))

public_routes = [
    r for r in data 
    if 'GET' in r.get('method', '') 
    and not r.get('uri', '').startswith('admin') 
    and not r.get('uri', '').startswith('api') 
    and not r.get('uri', '').startswith('_')
    and not r.get('uri', '').startswith('sanctum')
    and not r.get('uri', '').startswith('livewire')
    and not r.get('uri', '').startswith('telescope')
    and not r.get('uri', '').startswith('pulse')
    and not r.get('uri', '').startswith('horizon')
]

print(f"Total public GET routes: {len(public_routes)}")
for r in public_routes:
    print(f"{r.get('uri')} | {r.get('name')} | {r.get('action')}")
