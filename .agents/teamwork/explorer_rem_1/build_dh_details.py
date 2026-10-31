import os
import json
import re

base_dir = r"resources/views/app"

with open(".agents/teamwork/explorer_rem_1/dh_views.json", "r", encoding="utf-8") as f:
    dh_views = json.load(f)

dh_detailed = []

for v in sorted(dh_views, key=lambda x: x["file"]):
    file_path = os.path.join(base_dir, v["file"])
    with open(file_path, "r", encoding="utf-8", errors="ignore") as fp:
        lines = fp.readlines()
        
    ext_start = v["ext_start"]
    ext_end = v["ext_end"]
    original_ext = "".join(lines[ext_start-1:ext_end])
    
    # Generate proposed replacement:
    # We want to keep 'title' => ..., and remove 'headerTitle' and 'headerSubtitle'
    # Let's see the lines of @extends
    ext_lines = lines[ext_start-1:ext_end]
    new_ext_lines = []
    for l in ext_lines:
        if "'headerTitle'" in l or '"headerTitle"' in l:
            continue
        if "'headerSubtitle'" in l or '"headerSubtitle"' in l:
            continue
        new_ext_lines.append(l)
    
    # Clean up trailing commas or array formatting if needed
    cleaned_ext = "".join(new_ext_lines)
    
    # Also find module header snippet
    mod_line = v["mod_hdr_line"]
    mod_snippet = "".join(lines[mod_line-1:min(len(lines), mod_line+6)]).strip()
    
    dh_detailed.append({
        "file": v["file"],
        "ext_lines": f"{ext_start}-{ext_end}",
        "original_ext": original_ext.strip(),
        "cleaned_ext": cleaned_ext.strip(),
        "mod_hdr_line": mod_line,
        "mod_snippet": mod_snippet
    })

with open(".agents/teamwork/explorer_rem_1/dh_detailed.json", "w", encoding="utf-8") as fp:
    json.dump(dh_detailed, fp, indent=2)

print(f"Detailed analysis completed for {len(dh_detailed)} views.")
for d in dh_detailed[:5]:
    print("--------------------------------------------------")
    print(d["file"])
    print("BEFORE:\n", d["original_ext"])
    print("AFTER:\n", d["cleaned_ext"])
    print("MODULE HEADER:\n", d["mod_snippet"][:100], "...")
