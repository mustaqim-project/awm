import os, glob, re

root = r'c:\laragon\www\cooca_core'
base = os.path.join(root, r'resources\views\app')

print("=" * 70)
print("PHASE 2 & 3 COMPREHENSIVE VERIFICATION SUITE")
print("=" * 70)

# Check 1: Hardcoded Multi-Column Grids
grid_pat = re.compile(r'(?<![a-zA-Z0-9:])grid-cols-(?:[3-9]|1[0-2])\b')
grid_views = [f for f in glob.glob(os.path.join(base, '**', '*.blade.php'), recursive=True) 
              if grid_pat.search(open(f, encoding='utf-8', errors='ignore').read())]
print(f"[TEST 1] Hardcoded Unresponsive Grids: {len(grid_views)} views (PASS if 0)")
assert len(grid_views) == 0, f"Failed: found {grid_views}"

# Check 2: Raw Unicode Emojis
emoji_pat = re.compile(r'[\U0001F300-\U0001FAFF\U00002702-\U000027B0\U000024C2-\U0001F251]|★|⭐|●|🚗|🧺|💊|🏷|✓|✕')
emoji_views = [f for f in glob.glob(os.path.join(base, '**', '*.blade.php'), recursive=True) 
               if emoji_pat.search(open(f, encoding='utf-8', errors='ignore').read())]
print(f"[TEST 2] Raw Unicode Emojis: {len(emoji_views)} views (PASS if 0)")
assert len(emoji_views) == 0, f"Failed: found {emoji_views}"

# Check 3: Mobile Bottom Padding in 32 target views
padding_views = [
    'resources/views/app/ai/actions.blade.php',
    'resources/views/app/ai/history.blade.php',
    'resources/views/app/ai/index.blade.php',
    'resources/views/app/ai/lobby.blade.php',
    'resources/views/app/ai/office.blade.php',
    'resources/views/app/ai/providers.blade.php',
    'resources/views/app/ai/offices/executive.blade.php',
    'resources/views/app/ai/offices/growth.blade.php',
    'resources/views/app/ai/offices/operations.blade.php',
    'resources/views/app/community/events.blade.php',
    'resources/views/app/community/event_payment.blade.php',
    'resources/views/app/community/index.blade.php',
    'resources/views/app/crm/vouchers.blade.php',
    'resources/views/app/feedback/create.blade.php',
    'resources/views/app/feedback/index.blade.php',
    'resources/views/app/feedback/show.blade.php',
    'resources/views/app/finance/accounting/balance-sheet.blade.php',
    'resources/views/app/finance/accounting/coa.blade.php',
    'resources/views/app/finance/accounting/general-ledger.blade.php',
    'resources/views/app/finance/accounting/trial-balance.blade.php',
    'resources/views/app/finance/cash-bank/index.blade.php',
    'resources/views/app/finance/external-reconciliation/index.blade.php',
    'resources/views/app/landing_page/edit.blade.php',
    'resources/views/app/landing_page/popup.blade.php',
    'resources/views/app/portal/index.blade.php',
    'resources/views/app/quotations/index.blade.php',
    'resources/views/app/social_media/posts.blade.php',
    'resources/views/app/pos/kitchen.blade.php',
    'resources/views/app/pos/orders.blade.php',
    'resources/views/app/pos/reports.blade.php',
    'resources/views/app/pos/shifts.blade.php',
    'resources/views/app/sales-orders/index.blade.php'
]
missing_padding = []
for pv in padding_views:
    full_path = os.path.join(root, pv.replace('/', os.sep))
    content = open(full_path, encoding='utf-8', errors='ignore').read()
    if 'pb-28' not in content:
        missing_padding.append(pv)

print(f"[TEST 3] Mobile Bottom Safe-Area Padding: {len(padding_views) - len(missing_padding)}/{len(padding_views)} compliant (PASS if 32/32)")
assert len(missing_padding) == 0, f"Missing padding in {missing_padding}"

# Check 4: Table Action Consolidation in CRUD index views
table_views = [
    'resources/views/app/sales-orders/index.blade.php',
    'resources/views/app/sales/returns/index.blade.php',
    'resources/views/app/quotations/index.blade.php',
    'resources/views/app/invoices/index.blade.php',
    'resources/views/app/materials/index.blade.php'
]
missing_kebab = []
for tv in table_views:
    content = open(os.path.join(root, tv.replace('/', os.sep)), encoding='utf-8', errors='ignore').read()
    if 'more-horizontal' not in content or 'open = !open' not in content:
        missing_kebab.append(tv)

print(f"[TEST 4] Table Action Kebab Consolidation: {len(table_views) - len(missing_kebab)}/{len(table_views)} compliant (PASS if 5/5)")
assert len(missing_kebab) == 0, f"Missing kebab dropdown in {missing_kebab}"

# Check 5: Bento XXL Modal Canvas in Cashier Shift Close
close_modal_path = os.path.join(root, r'resources\views\app\pos\modals\close-shift-modal.blade.php')
if os.path.exists(close_modal_path):
    term_content = open(close_modal_path, encoding='utf-8', errors='ignore').read()
else:
    term_content = open(os.path.join(root, r'resources\views\app\pos\terminal.blade.php'), encoding='utf-8', errors='ignore').read()
assert 'lg:max-w-4xl xl:max-w-5xl' in term_content, "Close shift bento XXL modal missing"
assert 'grid grid-cols-1 lg:grid-cols-2' in term_content, "2-column bento grid missing in Close shift modal"
print("[TEST 5] Cashier Close Shift 2-Column Bento XXL Canvas: PASS")

# Check 6: Touch target elevation (min-h-[44px]) in channel quick toggles and forms
prod_content = open(os.path.join(root, r'resources\views\app\products\index.blade.php'), encoding='utf-8', errors='ignore').read()
assert 'min-h-[44px] min-w-[44px]' in prod_content, "Channel toggles touch target elevation missing"
print("[TEST 6] Minimum 44px Touch Target Elevation: PASS")

print("\nALL 6 ACCEPTANCE CRITERIA VERIFIED WITH 100% INTEGRITY!")
print("=" * 70)
