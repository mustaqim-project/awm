import json

with open('tools/flagged_details.json', 'r', encoding='utf-8') as f:
    data = json.load(f)

items = data.get('admin.php', [])
print(f'=== admin.php ({len(items)}) ===')
for it in items:
    print(f"  [{it['key']}]")
    print(f"    ID: {it['id']}")
    print(f"    EN: {it['en']}")
