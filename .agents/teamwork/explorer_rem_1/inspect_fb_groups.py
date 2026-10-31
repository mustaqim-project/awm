import json

with open(".agents/teamwork/explorer_rem_1/fb_full_mapping.json", "r", encoding="utf-8") as f:
    mapping = json.load(f)

# Group by file
files = {}
for item in mapping:
    files.setdefault(item["file"], []).append(item)

for fname, items in sorted(files.items()):
    print(f"==================================================")
    print(f"FILE: resources/views/app/{fname} ({len(items)} instances)")
    for it in items:
        print(f"  Instance #{it['instance_index']} (Line {it['line_no']}):")
        print(f"    x-show: {it['x_show']}")
        print(f"    teleported: {it['is_teleported']}")
        print(f"    modal_title: {it['modal_title']}")
        print(f"    max-w: {it['inner_max_w']}")
        print(f"    close_action: {it['close_action']}")
        print(f"    current wrapper: {it['wrapper_snippet']}")
        print(f"    current inner: {it['inner_snippet']}")
        print()
