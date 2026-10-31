import sys, os, re
sys.stdout.reconfigure(encoding='utf-8')

views_dir = r'c:\laragon\www\cooca_core\resources\views\app'

finance_files = [
    'finance/expenses.blade.php',
    'finance/journals.blade.php',
    'finance/payables.blade.php',
    'finance/receivables.blade.php',
    'finance/accounting/balance-sheet.blade.php',
    'finance/accounting/coa.blade.php',
    'finance/accounting/general-ledger.blade.php',
    'finance/accounting/reconciliation.blade.php',
    'finance/accounting/trial-balance.blade.php',
    'finance/cash-bank/index.blade.php',
    'finance/cash-bank/ledger.blade.php',
    'finance/edc-terminals/index.blade.php',
    'finance/external-reconciliation/index.blade.php',
    'finance/payout-accounts/index.blade.php',
    'finance/settlements/index.blade.php',
    'finance/settlements/show.blade.php',
    'invoices/index.blade.php',
    'invoices/create.blade.php',
    'invoices/edit.blade.php',
    'invoices/show.blade.php',
    'invoices/print.blade.php',
    'tax/index.blade.php',
    'billing/checkout.blade.php',
    'billing/history.blade.php',
    'billing/invoice.blade.php',
    'billing/limits.blade.php',
    'billing/payment.blade.php',
]

for rel in finance_files:
    fp = os.path.join(views_dir, rel.replace('/', os.sep))
    if not os.path.exists(fp):
        continue
    with open(fp, 'r', encoding='utf-8', errors='ignore') as f:
        content = f.read()
        lines = content.splitlines()

    # Look for layout, headings, modals, empty states, error handling, tables
    bento = len(re.findall(r'rounded-\[(?:20|24|28)px\]', content))
    legacy_r = len(re.findall(r'\brounded-(?:lg|xl|2xl)\b', content))
    hairline = len(re.findall(r'border-black/\[0\.06\]|border-white/\[0\.08\]', content))
    legacy_b = len(re.findall(r'\bborder-gray-(?:200|300)\b|\bborder-slate-(?:200|300)\b', content))
    
    # Check modals
    has_modal = bool(re.search(r'x-show="[^"]*modal|role="dialog"|fixed inset-0', content, re.IGNORECASE))
    modal_blur = len(re.findall(r'backdrop-blur', content))
    
    # Check empty state
    has_empty = bool(re.search(r'@empty|belum ada|tidak ada|no data', content, re.IGNORECASE))
    
    # Check errors
    has_err = bool(re.search(r'\$errors->any\(\)|@error\(|session\(\'(?:error|status|success)\'\)', content))
    
    # Check tables & overflow
    tables = len(re.findall(r'<table\b', content))
    overflow = len(re.findall(r'overflow-x-auto', content))
    
    # Multi-industry checks
    gating = re.findall(r'(?:hasDineInFeature|isWorkshop|isLaundry|isPharmacy|isFoodIndustry|isModuleEnabled)\b', content)
    
    print(f"{rel:<42} | L:{len(lines):<4} | Bento:{bento:<2} LegR:{legacy_r:<2} | Hairline:{hairline:<2} LegB:{legacy_b:<2} | Modal:{has_modal}(blur:{modal_blur}) | Empty:{has_empty} | Err:{has_err} | Tbl:{tables}(ovf:{overflow}) | Gating:{len(gating)}")
