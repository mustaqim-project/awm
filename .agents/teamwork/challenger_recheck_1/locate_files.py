import os
import glob

base = r"c:\laragon\www\cooca_core\resources\views"
queries = [
    'tab-features.blade.php', 'tab-fulfillment.blade.php', 'tab-general.blade.php',
    'tab-payment-settlement.blade.php', 'tab-schedule.blade.php',
    'expenses', 'journals', 'reconciliations', 'reconciliation',
    'balance-sheet', 'virtual_office_canvas', 'canvas',
    'sidebar-blocks', 'theme-settings', 'product-form-modal',
    'security', 'billing'
]

for q in queries:
    matches = glob.glob(os.path.join(base, "**", f"*{q}*"), recursive=True)
    clean_matches = [os.path.relpath(m, base).replace(os.sep, "/") for m in matches if os.path.isfile(m)]
    print(f"Query '{q}': found {len(clean_matches)} files: {clean_matches[:5]}")
