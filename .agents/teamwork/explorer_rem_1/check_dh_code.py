import json

with open(".agents/teamwork/explorer_rem_1/dh_views.json", "r", encoding="utf-8") as f:
    dh_views = json.load(f)

for v in sorted(dh_views, key=lambda x: x["file"]):
    print("--------------------------------------------------")
    print(f"File: resources/views/app/{v['file']}")
    print(f"Extends lines {v['ext_start']}-{v['ext_end']}:")
    print(v['ext_code'])
    print(f"Module header at line {v['mod_hdr_line']}")
