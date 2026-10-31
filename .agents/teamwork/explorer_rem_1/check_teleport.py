import json
import os

with open(".agents/teamwork/explorer_rem_1/fb_detailed.json", "r", encoding="utf-8") as f:
    fb_list = json.load(f)

base_dir = r"resources/views/app"

for fb in fb_list:
    file_path = os.path.join(base_dir, fb["file"])
    with open(file_path, "r", encoding="utf-8", errors="ignore") as fp:
        lines = fp.readlines()
    
    line_no = fb["line_no"]
    # Look at 5 lines before and 5 lines after
    pre_lines = lines[max(0, line_no - 6):line_no - 1]
    cur_line = lines[line_no - 1]
    post_lines = lines[line_no:min(len(lines), line_no + 5)]
    
    # Check if enclosed in <template x-teleport="body">
    pre_str = "".join(pre_lines)
    is_teleported = "<template x-teleport" in pre_str
    
    fb["is_teleported"] = is_teleported
    fb["full_opening_line"] = cur_line.strip()

with open(".agents/teamwork/explorer_rem_1/fb_teleport_check.json", "w", encoding="utf-8") as f:
    json.dump(fb_list, f, indent=2)

teleported_count = sum(1 for fb in fb_list if fb["is_teleported"])
print(f"Total instances: {len(fb_list)}")
print(f"Already teleported: {teleported_count}")
print(f"Not teleported: {len(fb_list) - teleported_count}")
