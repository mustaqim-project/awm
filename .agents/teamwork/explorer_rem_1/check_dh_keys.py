import json
import re

with open(".agents/teamwork/explorer_rem_1/dh_views.json", "r", encoding="utf-8") as f:
    dh_views = json.load(f)

for v in dh_views:
    code = v["ext_code"]
    keys = re.findall(r"'([a-zA-Z0-9_]+)'\s*=>", code)
    non_standard = [k for k in keys if k not in ["title", "headerTitle", "headerSubtitle"]]
    if non_standard:
        print(v["file"], "OTHER KEYS:", non_standard)
print("Finished checking keys.")
