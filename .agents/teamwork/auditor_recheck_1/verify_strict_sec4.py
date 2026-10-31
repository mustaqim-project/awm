import os, glob, re, sys
sys.stdout.reconfigure(encoding='utf-8')

with open('docs/UI_UX_AUDIT_REPORT.md', encoding='utf-8') as f:
    text = f.read()

# Isolate Section 4
sec4_start = text.find('## 4. Submodule-by-Submodule Detailed Evaluation')
sec4_end = text.find('## 5. Systematic UI/UX Defect Catalog', sec4_start)
sec4_text = text[sec4_start:sec4_end]

# Split by submodule heading: '#### <number>. <Name> (`resources/views/app/<subpath>`)'
submodule_blocks = re.split(r'(?=####\s+\d+\.)', sec4_text)

print(f"Total submodule blocks found: {len(submodule_blocks)-1}")

strict_missing = []
strict_found = []

for block in submodule_blocks[1:]:
    heading_match = re.search(r'####\s+(\d+)\.\s+([^\n`]+)(?:`([^`]+)`)?', block)
    if not heading_match:
        continue
    num = heading_match.group(1)
    name = heading_match.group(2).strip()
    subpath = heading_match.group(3) if heading_match.group(3) else ""
    
    # subpath is e.g. 'resources/views/app/pos' or 'resources/views/app'
    sub_dir = subpath.strip()
    if not sub_dir:
        sub_dir = 'resources/views/app'
    
    # Find all `.blade.php` in this block
    blade_mentions = re.findall(r'`([^`]+\.blade\.php)`', block)
    
    for bm in blade_mentions:
        # Check candidate paths:
        # 1. Directly in workspace: bm (if full path)
        # 2. Relative to sub_dir: os.path.join(sub_dir, bm)
        # 3. Relative to resources/views/app: os.path.join('resources/views/app', bm)
        candidates = [
            bm,
            os.path.join(sub_dir, bm),
            os.path.join('resources/views/app', bm),
        ]
        
        found_path = None
        for cand in candidates:
            if os.path.isfile(cand):
                found_path = cand
                break
        
        if found_path:
            strict_found.append((num, name, bm, found_path))
        else:
            strict_missing.append((num, name, sub_dir, bm))

print(f"\nStrictly verified valid files: {len(strict_found)}")
print(f"Strictly missing / inaccurate paths: {len(strict_missing)}")

if strict_missing:
    print("\nDETAILS OF INACCURATE / NON-EXISTENT FILE PATHS IN SECTION 4:")
    for num, name, sdir, bm in strict_missing:
        print(f"  [Submodule {num}: {name}] (Dir: {sdir}) -> Cited: `{bm}` DOES NOT EXIST!")
