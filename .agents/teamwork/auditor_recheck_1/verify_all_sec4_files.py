import os, glob, re, sys
sys.stdout.reconfigure(encoding='utf-8')

with open('docs/UI_UX_AUDIT_REPORT.md', encoding='utf-8') as f:
    text = f.read()

# Isolate Section 4
sec4_start = text.find('## 4. Submodule-by-Submodule Detailed Evaluation')
sec4_end = text.find('## 5. Systematic UI/UX Defect Catalog', sec4_start)
sec4_text = text[sec4_start:sec4_end]

base_dir = 'resources/views/app'

# Find all occurrences of something ending in .blade.php
all_blade_mentions = re.findall(r'`([^`]+\.blade\.php)`', sec4_text)

print(f"Total .blade.php mentions in Section 4: {len(all_blade_mentions)}")

missing_files = []
valid_files = []

for mention in all_blade_mentions:
    # Handle possible paths:
    # 1. mention is directly relative to resources/views/app (e.g. `billing/checkout.blade.php`)
    # 2. mention is relative to the workspace (e.g. `resources/views/app/pos/terminal.blade.php`)
    # 3. mention is a filename inside a submodule (e.g. `checkout.blade.php`)
    
    candidate_paths = [
        os.path.join(base_dir, mention),
        mention,
        os.path.join(base_dir, os.path.basename(mention)),
    ]
    
    # Also search globally in resources/views/app
    found = False
    for cp in candidate_paths:
        if os.path.isfile(cp):
            found = True
            valid_files.append((mention, cp))
            break
            
    if not found:
        # try find by name in resources/views/app
        matches = glob.glob(f"resources/views/app/**/{os.path.basename(mention)}", recursive=True)
        if matches:
            found = True
            valid_files.append((mention, matches[0]))
        else:
            missing_files.append(mention)

print(f"Valid files verified on disk: {len(valid_files)}")
print(f"Missing/Fabricated files: {len(missing_files)}")
if missing_files:
    print("MISSING FILES DETAILS:")
    for mf in set(missing_files):
        print(f"  - {mf}")
else:
    print("ALL files cited in Section 4 exist on disk! 0 fabricated files.")
