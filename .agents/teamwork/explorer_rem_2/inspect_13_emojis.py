import os
import re
import sys

sys.stdout.reconfigure(encoding='utf-8')

base = r"c:\laragon\www\cooca_core\resources\views\app"

emoji_files = [
    "pos/reports/tabs/transactions.blade.php",
    "pos/reports/partials/order_detail_modal.blade.php",
    "pos/reports/print_summary.blade.php",
    "social_media/inbox.blade.php",
    "community/index.blade.php",
    "ai/partials/virtual_office_canvas.blade.php",
    "ai/partials/agent_inspector_hud.blade.php",
    "landing_page/edit.blade.php",
    "master-data/index.blade.php",
    "billing/invoice.blade.php",
    "sales/returns/show.blade.php",
    "mcp/oauth/consent.blade.php",
    "social_media/posts.blade.php"
]

pattern_emoji = re.compile(r'[\U0001F300-\U0001FAFF\U00002702-\U000027B0\U000024C2-\U0001F251]|★|⭐|●|🚗|🧺|💊|🏷|✓|✕')

for ef in emoji_files:
    fp = os.path.join(base, ef.replace('/', os.sep))
    if not os.path.exists(fp):
        print(f"NOT FOUND: {ef}")
        continue
    with open(fp, 'r', encoding='utf-8', errors='ignore') as f:
        lines = f.readlines()
    print(f"\n=== {ef} ===")
    for idx, l in enumerate(lines, 1):
        found = pattern_emoji.findall(l)
        if found:
            print(f"  Line {idx:4d}: {found} -> {l.strip()[:110]}")
