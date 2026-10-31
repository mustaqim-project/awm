import os
import re
import json

base_dir = r"resources/views/app"
pattern = re.compile(r'class="[^"]*\bfixed\s+inset-0\b[^"]*\bflex\b[^"]*\bbg-black/(?:[0-9]+)\b')

views_to_scan = [
    "pos/terminal.blade.php",
    "products/index.blade.php",
    "materials/index.blade.php",
    "finance/cash-bank/index.blade.php",
    "settings/index.blade.php",
    "products/modifiers.blade.php",
    "finance/accounting/coa.blade.php",
    "master-data/index.blade.php",
    "warehouse/index.blade.php",
    "warehouse/show.blade.php",
    "calculator.blade.php",
    "ai-studio/show.blade.php",
    "products/bom.blade.php",
    "ai-studio/index.blade.php",
    "ai-studio/prompt-preview.blade.php",
    "finance/expenses.blade.php",
    "finance/payables.blade.php",
    "finance/receivables.blade.php",
    "finance/accounting/reconciliation.blade.php",
    "whatsapp/index.blade.php",
    "whatsapp/logs.blade.php",
]

fb_results = []

for rel in views_to_scan:
    full_path = os.path.join(base_dir, rel)
    if not os.path.exists(full_path):
        print(f"ERROR: File not found: {full_path}")
        continue
    
    with open(full_path, "r", encoding="utf-8", errors="ignore") as fp:
        lines = fp.readlines()
        
    file_content = "".join(lines)
    
    matches = list(pattern.finditer(file_content))
    print(f"{rel}: {len(matches)} instances")
    
    for idx_m, match in enumerate(matches, 1):
        start_char = match.start()
        line_no = file_content[:start_char].count("\n") + 1
        class_str = match.group(0)
        
        # Scan upwards from line_no to find opening tag of this element
        # usually <div ... class="..." ...>
        tag_start_line = line_no
        # check if line_no contains '<'
        cur_l = line_no
        while cur_l >= 1 and "<" not in lines[cur_l-1]:
            cur_l -= 1
        tag_start_line = cur_l
        
        # Grab lines from tag_start_line to line_no + 15
        chunk_lines = lines[tag_start_line-1:min(len(lines), line_no+20)]
        chunk = "".join(chunk_lines)
        
        # Find x-show
        # search in surrounding 10 lines
        surrounding = "".join(lines[max(0, tag_start_line-3):min(len(lines), line_no+10)])
        x_show_match = re.search(r'x-show="([^"]+)"', surrounding)
        x_show = x_show_match.group(1) if x_show_match else None
        
        # If not x-show, check for :class="{ 'hidden': !open }" or similar
        if not x_show:
            x_show_match2 = re.search(r':class="[^"]*hidden[^"]*"', surrounding)
            if x_show_match2:
                x_show = x_show_match2.group(0)
                
        # Also check x-cloak, x-teleport
        has_cloak = "x-cloak" in surrounding
        has_teleport = "x-teleport" in "".join(lines[max(0, tag_start_line-5):tag_start_line])
        
        # Check inner dialog max-w
        # Find the first child div after the backdrop wrapper
        inner_max_w = re.findall(r'max-w-[a-zA-Z0-9\[\]\-]+', chunk)
        
        # Check modal title or purpose
        h_match = re.search(r'<h[1-6][^>]*>(.*?)</h[1-6]>', chunk, re.DOTALL)
        modal_title = h_match.group(1).strip() if h_match else None
        if modal_title:
            # clean up tags
            modal_title = re.sub(r'<[^>]+>', '', modal_title).strip()
            
        fb_results.append({
            "file": rel,
            "instance_index": idx_m,
            "line_no": line_no,
            "tag_start_line": tag_start_line,
            "class": class_str,
            "x_show": x_show,
            "has_cloak": has_cloak,
            "has_teleport": has_teleport,
            "inner_max_w": inner_max_w[0] if inner_max_w else None,
            "modal_title": modal_title,
            "chunk_snippet": chunk[:300]
        })

print(f"\nTotal instances extracted: {len(fb_results)}")

with open(".agents/teamwork/explorer_rem_1/fb_detailed.json", "w", encoding="utf-8") as fp:
    json.dump(fb_results, fp, indent=2)
