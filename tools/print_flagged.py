import json

with open('tools/flagged_details.json', 'r', encoding='utf-8') as f:
    data = json.load(f)

for fname, items in data.items():
    if fname != 'admin.php':
        print(f'=== {fname} ({len(items)}) ===')
        for it in items:
            print(f"  [{it['key']}]")
            print(f"    ID: {it['id']}")
            print(f"    EN: {it['en']}")
