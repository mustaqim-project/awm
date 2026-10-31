import json

with open(".agents/teamwork/explorer_rem_1/fb_detailed.json", "r", encoding="utf-8") as f:
    fb_list = json.load(f)

for fb in fb_list:
    print(f"[{fb['file']}] #{fb['instance_index']} @ Line {fb['line_no']}: x-show={fb['x_show']} | title={fb['modal_title']} | max-w={fb['inner_max_w']}")
