import sys, os, re
sys.stdout.reconfigure(encoding='utf-8')

views_dir = r'c:\laragon\www\cooca_core\resources\views\app'

files_to_inspect = [
    'hrm/index.blade.php',
    'settings/index.blade.php',
    'settings/integrations/mcp.blade.php',
    'labor-machines/index.blade.php',
    'master-data/index.blade.php',
]

for rel in files_to_inspect:
    fp = os.path.join(views_dir, rel.replace('/', os.sep))
    print(f"\n=======================================================")
    print(f"DEEP INSPECT: {rel}")
    print(f"=======================================================")
    with open(fp, 'r', encoding='utf-8', errors='ignore') as f:
        lines = f.readlines()

    print(f"Total lines: {len(lines)}")
    
    # Check top 15 lines
    for l in lines[:15]:
        if l.strip():
            print("  " + l.strip()[:100])

    # Check sections / tabs
    tabs = []
    modals = []
    tables = []
    for i, l in enumerate(lines, 1):
        if 'activeTab' in l or 'x-show="tab' in l or 'data-tab' in l:
            tabs.append((i, l.strip()[:100]))
        if 'x-show="modal' in l or 'role="dialog"' in l or 'x-modal' in l:
            modals.append((i, l.strip()[:100]))
        if '<table' in l:
            tables.append((i, l.strip()[:100]))

    print(f"\nTabs detected ({len(tabs)} lines):")
    for t in tabs[:5]:
        print(f"  Line {t[0]}: {t[1]}")
    print(f"Modals detected ({len(modals)} lines):")
    for m in modals[:5]:
        print(f"  Line {m[0]}: {m[1]}")
    print(f"Tables detected ({len(tables)} lines):")
    for tb in tables[:5]:
        print(f"  Line {tb[0]}: {tb[1]}")
