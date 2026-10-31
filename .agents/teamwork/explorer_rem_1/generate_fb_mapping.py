import json
import os
import re

base_dir = r"resources/views/app"

with open(".agents/teamwork/explorer_rem_1/fb_teleport_check.json", "r", encoding="utf-8") as f:
    fb_list = json.load(f)

detailed_mapping = []

for fb in fb_list:
    file_path = os.path.join(base_dir, fb["file"])
    with open(file_path, "r", encoding="utf-8", errors="ignore") as fp:
        lines = fp.readlines()
        
    line_no = fb["line_no"]
    
    # Grab the outer tag lines
    # From line_no - 2 to line_no + 8
    # Let's find the closing > of the wrapper tag
    tag_lines = []
    idx = line_no - 1
    # if line_no - 1 is not the start of the tag, walk backwards
    while idx >= 0 and "<div" not in lines[idx] and "<template" not in lines[idx]:
        idx -= 1
    
    wrapper_start = idx + 1
    # find where wrapper tag definition ends (contains '>')
    wrapper_tag_lines = []
    w_idx = idx
    while w_idx < len(lines):
        wrapper_tag_lines.append(lines[w_idx].rstrip())
        if ">" in lines[w_idx]:
            break
        w_idx += 1
    wrapper_end = w_idx + 1
    
    # Look for the immediate inner dialog opening tag
    inner_tag_lines = []
    i_idx = wrapper_end
    while i_idx < len(lines):
        line_content = lines[i_idx].strip()
        if line_content.startswith("<div"):
            # collect dialog opening tag
            cur_i = i_idx
            while cur_i < len(lines):
                inner_tag_lines.append(lines[cur_i].rstrip())
                if ">" in lines[cur_i]:
                    break
                cur_i += 1
            break
        i_idx += 1
        
    wrapper_snippet = "\n".join(wrapper_tag_lines)
    inner_snippet = "\n".join(inner_tag_lines) if inner_tag_lines else "(dialog div follows)"
    
    # Extract Alpine close action if possible, e.g. @click="showCustomerModal = false"
    # or look for close buttons
    close_actions = re.findall(r'@click="([^"]+=\s*false[^"]*)"', "".join(lines[line_no-1:min(len(lines), line_no+40)]))
    close_action = close_actions[0] if close_actions else f"{fb['x_show']} = false"
    
    detailed_mapping.append({
        "file": fb["file"],
        "instance_index": fb["instance_index"],
        "line_no": line_no,
        "wrapper_start": wrapper_start,
        "wrapper_end": wrapper_end,
        "x_show": fb["x_show"],
        "is_teleported": fb["is_teleported"],
        "modal_title": fb["modal_title"],
        "inner_max_w": fb["inner_max_w"],
        "wrapper_snippet": wrapper_snippet,
        "inner_snippet": inner_snippet,
        "close_action": close_action
    })

with open(".agents/teamwork/explorer_rem_1/fb_full_mapping.json", "w", encoding="utf-8") as f:
    json.dump(detailed_mapping, f, indent=2)

print(f"Generated full mapping for {len(detailed_mapping)} instances.")
