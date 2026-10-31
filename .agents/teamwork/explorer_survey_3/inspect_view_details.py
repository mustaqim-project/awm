import sys, os, re
sys.stdout.reconfigure(encoding='utf-8')

views_dir = r'c:\laragon\www\cooca_core\resources\views\app'

files_to_inspect = [
    'tax/index.blade.php',
    'community/index.blade.php',
    'community/events.blade.php',
    'hrm/index.blade.php',
    'settings/index.blade.php',
    'settings/integrations/mcp.blade.php',
    'labor-machines/index.blade.php',
    'master-data/index.blade.php',
    'simulator/index.blade.php',
    'reports/index.blade.php',
    'profitability/index.blade.php',
]

for rel in files_to_inspect:
    fp = os.path.join(views_dir, rel.replace('/', os.sep))
    print(f"\n=======================================================")
    print(f"DEEP INSPECT: {rel}")
    print(f"=======================================================")
    with open(fp, 'r', encoding='utf-8', errors='ignore') as f:
        lines = f.readlines()

    print(f"Total lines: {len(lines)}")
    
    # Check top 30 lines
    print("--- Top 20 lines preview ---")
    for l in lines[:20]:
        if l.strip():
            print("  " + l.strip()[:100])

    # Check buttons and CTAs
    buttons = []
    for i, l in enumerate(lines, 1):
        if '<button' in l or '<a ' in l:
            # check for primary colors or large classes
            if any(c in l for c in ['bg-blue-', 'bg-indigo-', 'bg-[#007AFF]', 'bg-emerald-', 'bg-green-', 'btn-']):
                buttons.append((i, l.strip()[:120]))
    print(f"\nSample Styled Buttons ({len(buttons)} total styled buttons found):")
    for b in buttons[:8]:
        print(f"  Line {b[0]}: {b[1]}")

    # Check cards / containers
    cards = []
    for i, l in enumerate(lines, 1):
        if any(c in l for c in ['shadow-', 'rounded-xl', 'rounded-2xl', 'rounded-[20px]', 'rounded-[24px]', 'border-gray-', 'border-slate-']):
            cards.append((i, l.strip()[:120]))
    print(f"\nSample Card Styling ({len(cards)} total card style instances):")
    for c in cards[:8]:
        print(f"  Line {c[0]}: {c[1]}")
