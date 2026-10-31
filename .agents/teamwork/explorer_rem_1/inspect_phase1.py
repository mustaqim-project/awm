import os
import glob
import re
import json

base_dir = r"resources/views/app"

# 1. Double Header Analysis
print("=== DOUBLE HEADER ANALYSIS (44 Views) ===")
fl = glob.glob(os.path.join(base_dir, "**", "*.blade.php"), recursive=True)
dh_views = []

for f in fl:
    rel = os.path.relpath(f, base_dir).replace("\\", "/")
    with open(f, "r", encoding="utf-8", errors="ignore") as fp:
        content = fp.read()
    if "headerTitle" in content and "<x-module-header" in content:
        lines = content.splitlines()
        # Find @extends start and end
        ext_start = None
        ext_end = None
        ext_code = []
        for i, l in enumerate(lines, 1):
            if "@extends" in l:
                ext_start = i
                # check if it closes on same line or subsequent lines
                cur = i
                buf = [l]
                while cur <= len(lines):
                    full_str = "\n".join(buf)
                    # count parentheses or check if @extends(...) is balanced
                    # or ends with )
                    if ")" in lines[cur-1] and ("headerTitle" in full_str or cur > i):
                        # check if closing paren of @extends
                        if full_str.count("(") <= full_str.count(")"):
                            ext_end = cur
                            ext_code = buf
                            break
                    cur += 1
                    if cur <= len(lines):
                        buf.append(lines[cur-1])
                break
        
        # Find <x-module-header line
        mod_hdr_line = None
        for i, l in enumerate(lines, 1):
            if "<x-module-header" in l:
                mod_hdr_line = i
                break
                
        dh_views.append({
            "file": rel,
            "ext_start": ext_start,
            "ext_end": ext_end,
            "ext_code": "\n".join(ext_code) if ext_code else "",
            "mod_hdr_line": mod_hdr_line,
        })

print(f"Total Double Header views found: {len(dh_views)}")
for v in sorted(dh_views, key=lambda x: x["file"]):
    print(f"File: {v['file']} (Lines {v['ext_start']}-{v['ext_end']}) Module Header at Line {v['mod_hdr_line']}")

# 2. Flawed Backdrop Analysis (76 instances across 21 views)
print("\n=== FLAWED BACKDROP ANALYSIS ===")
pattern = re.compile(r'class="[^"]*\bfixed\s+inset-0\b[^"]*\bflex\b[^"]*\bbg-black/(?:[0-9]+)\b')

fb_views = {}
fb_instances = []

for f in fl:
    rel = os.path.relpath(f, base_dir).replace("\\", "/")
    with open(f, "r", encoding="utf-8", errors="ignore") as fp:
        lines = fp.readlines()
    
    file_content = "".join(lines)
    for match in pattern.finditer(file_content):
        start_char = match.start()
        # find line number
        line_no = file_content[:start_char].count("\n") + 1
        matched_str = match.group(0)
        
        # let's find the enclosing or nearby tag and x-show / x-data
        # inspect 5 lines before and 10 lines after
        ctx_start = max(1, line_no - 4)
        ctx_end = min(len(lines), line_no + 8)
        ctx = "".join(lines[ctx_start-1:ctx_end])
        
        # look for x-show or x-data or modal name
        x_show = re.findall(r'x-show="([^"]+)"', ctx)
        x_data = re.findall(r'x-data="([^"]+)"', ctx)
        
        fb_instances.append({
            "file": rel,
            "line": line_no,
            "class": matched_str,
            "x_show": x_show[0] if x_show else None,
            "x_data": x_data[0] if x_data else None,
            "context": "".join(lines[line_no-1:min(len(lines), line_no+3)]).strip()
        })
        fb_views[rel] = fb_views.get(rel, 0) + 1

print(f"Total views with flawed backdrops: {len(fb_views)}")
print(f"Total flawed backdrop instances: {len(fb_instances)}")
for k, v in sorted(fb_views.items()):
    print(f"  {k}: {v}")

with open(".agents/teamwork/explorer_rem_1/dh_views.json", "w", encoding="utf-8") as fp:
    json.dump(dh_views, fp, indent=2)

with open(".agents/teamwork/explorer_rem_1/fb_instances.json", "w", encoding="utf-8") as fp:
    json.dump(fb_instances, fp, indent=2)
