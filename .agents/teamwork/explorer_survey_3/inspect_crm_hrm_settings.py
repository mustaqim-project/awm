import sys, os, re
sys.stdout.reconfigure(encoding='utf-8')

views_dir = r'c:\laragon\www\cooca_core\resources\views\app'

crm_hrm_settings = [
    # CRM & Customers
    'customers/index.blade.php',
    'crm/members.blade.php',
    'crm/vouchers.blade.php',
    'feedback/index.blade.php',
    'feedback/create.blade.php',
    'feedback/show.blade.php',
    'community/index.blade.php',
    'community/events.blade.php',
    'community/event_payment.blade.php',
    # HRM & Workforce
    'hrm/index.blade.php',
    'hrm/payroll/create.blade.php',
    'hrm/payroll/payslip.blade.php',
    'hrm/payroll/show.blade.php',
    'labor-machines/index.blade.php',
    # Settings & Governance
    'settings/index.blade.php',
    'settings/roles.blade.php',
    'settings/integrations/mcp.blade.php',
    'settings/integrations/social_media.blade.php',
    'settings/integrations/whatsapp.blade.php',
    'profile/edit.blade.php',
    'profile/verify-contact.blade.php',
    'roles/index.blade.php',
    'security/audit-logs/index.blade.php',
    'security/audit-logs/show.blade.php',
    'approvals/history.blade.php',
    'approvals/inbox.blade.php',
    'approvals/rules.blade.php',
    'master-data/index.blade.php',
]

for rel in crm_hrm_settings:
    fp = os.path.join(views_dir, rel.replace('/', os.sep))
    if not os.path.exists(fp):
        continue
    with open(fp, 'r', encoding='utf-8', errors='ignore') as f:
        content = f.read()
        lines = content.splitlines()

    bento = len(re.findall(r'rounded-\[(?:20|24|28)px\]', content))
    legacy_r = len(re.findall(r'\brounded-(?:lg|xl|2xl)\b', content))
    hairline = len(re.findall(r'border-black/\[0\.06\]|border-white/\[0\.08\]', content))
    legacy_b = len(re.findall(r'\bborder-gray-(?:200|300)\b|\bborder-slate-(?:200|300)\b', content))
    
    has_modal = bool(re.search(r'x-show="[^"]*modal|role="dialog"|fixed inset-0', content, re.IGNORECASE))
    modal_blur = len(re.findall(r'backdrop-blur', content))
    
    has_empty = bool(re.search(r'@empty|belum ada|tidak ada|no data', content, re.IGNORECASE))
    has_err = bool(re.search(r'\$errors->any\(\)|@error\(|session\(\'(?:error|status|success)\'\)', content))
    
    tables = len(re.findall(r'<table\b', content))
    overflow = len(re.findall(r'overflow-x-auto', content))
    
    gating = re.findall(r'(?:hasDineInFeature|isWorkshop|isLaundry|isPharmacy|isFoodIndustry|isModuleEnabled)\b', content)
    
    print(f"{rel:<44} | L:{len(lines):<4} | Bento:{bento:<2} LegR:{legacy_r:<2} | Hairline:{hairline:<2} LegB:{legacy_b:<2} | Modal:{has_modal}(blur:{modal_blur}) | Empty:{has_empty} | Err:{has_err} | Tbl:{tables}(ovf:{overflow}) | Gating:{len(gating)}")
