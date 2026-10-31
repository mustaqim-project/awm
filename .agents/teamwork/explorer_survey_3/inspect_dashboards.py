import sys, os, re
sys.stdout.reconfigure(encoding='utf-8')

views_dir = r'c:\laragon\www\cooca_core\resources\views\app'

files = [
    'dashboard.blade.php',
    'analytics/index.blade.php',
    'reports/index.blade.php',
    'profitability/index.blade.php',
    'calculator.blade.php',
    'simulator/index.blade.php'
]

for rel in files:
    fp = os.path.join(views_dir, rel.replace('/', os.sep))
    print(f"\n=======================================================")
    print(f"FILE: {rel}")
    print(f"=======================================================")
    if not os.path.exists(fp):
        print("NOT FOUND")
        continue
    with open(fp, 'r', encoding='utf-8', errors='ignore') as f:
        content = f.read()
        lines = content.splitlines()

    print(f"Lines: {len(lines)}")
    
    # 1. Layout extension
    layout_match = re.search(r"@extends\(['\"]([^'\"]+)['\"]\)", content)
    layout = layout_match.group(1) if layout_match else "No @extends"
    print(f"Layout: {layout}")

    # 2. Main headings
    headings = []
    for i, l in enumerate(lines, 1):
        h_match = re.search(r'<(h[1-3])\b[^>]*>(.*?)</\1>', l, re.IGNORECASE)
        if h_match:
            headings.append((i, h_match.group(1), re.sub(r'<[^>]+>', '', h_match.group(2)).strip()))
    print(f"Headings ({len(headings)}):")
    for h in headings[:10]:
        print(f"  Line {h[0]} [{h[1]}]: {h[2]}")

    # 3. Card styling & rounded borders
    bento_cards = len(re.findall(r'rounded-\[(?:20|24)px\]', content))
    legacy_cards = len(re.findall(r'\brounded-(?:xl|lg)\b', content))
    hairline = len(re.findall(r'border-black/\[0\.06\]|border-white/\[0\.08\]', content))
    legacy_borders = len(re.findall(r'\bborder-gray-200\b|\bborder-slate-200\b', content))
    print(f"Card styling: Bento [20/24px]={bento_cards}, Legacy rounded={legacy_cards}, Hairline border={hairline}, Legacy border={legacy_borders}")

    # 4. Metric cards & KPIs
    tabular_nums = len(re.findall(r'\btabular-nums\b', content))
    delta_indicators = len(re.findall(r'text-emerald-600|text-rose-600|text-green-600|text-red-600', content))
    print(f"KPIs: tabular-nums={tabular_nums}, delta indicators={delta_indicators}")

    # 5. Charts / Visualizations
    charts = re.findall(r'<canvas[^>]*id=[\'"]([^\'"]+)[\'"]|new Chart\(|ApexCharts', content)
    print(f"Charts found: {charts}")

    # 6. Multi-industry gating
    gating = re.findall(r'(?:hasDineInFeature|isWorkshop|isLaundry|isPharmacy|isFoodIndustry|isModuleEnabled)\([^)]*\)', content)
    print(f"Multi-industry gating ({len(gating)}): {set(gating)}")

    # 7. Empty states
    empty_states = re.findall(r'@empty|empty-state|belum ada|tidak ada data', content, re.IGNORECASE)
    print(f"Empty states keywords: {len(empty_states)}")
