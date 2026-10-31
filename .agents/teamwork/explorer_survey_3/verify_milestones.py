import json

with open(r'.agents/teamwork/explorer_survey_3/survey_data.json', 'r', encoding='utf-8') as f:
    d = json.load(f)

views_issues = d['views_issues']
summary = views_issues['submodule_summary']

m1_subs = ['pos', 'inventory', 'products', 'warehouse', 'storefront', 'marketplace']
m2_subs = ['sales', 'sales-orders', 'quotations', 'purchasing', 'purchase-orders', 'invoices', 'suppliers', 'customers', 'services', 'crm']
m3_subs = ['finance', 'billing', 'tax', 'settings', 'profile', 'master-data', 'labor-machines', 'profitability', 'reports', 'portal']
m4_subs = ['ai', 'ai-studio', 'social_media', 'whatsapp', 'mcp']
m5_subs = ['landing_page', 'docs', 'import', 'community']

milestones = {
    'Milestone 1: Retail & Omnichannel Commerce': m1_subs,
    'Milestone 2: B2B Trade, Purchasing & CRM': m2_subs,
    'Milestone 3: Finance, Tax, Settings & Foundation': m3_subs,
    'Milestone 4: AI Suite, Studio & Social Omnichannel': m4_subs,
    'Milestone 5: Heavy Builders, Docs, Import & Community': m5_subs
}

total_mapped_files = 0
total_mapped_issues = 0
total_dirty_files = 0

for m_name, subs in milestones.items():
    m_files = 0
    m_dirty_files = 0
    m_issues = 0
    m_lines = 0
    file_list = []
    for sub in subs:
        s = summary[sub]
        m_files += s['total_files']
        m_dirty_files += s['files_with_issues']
        m_issues += s['total_issues']
        m_lines += s['total_lines']
        for fd in s['file_details']:
            if fd['issues'] > 0:
                file_list.append((fd['file'], fd['issues'], fd['lines']))
                
    total_mapped_files += m_files
    total_dirty_files += m_dirty_files
    total_mapped_issues += m_issues
    print(f"\n{m_name}")
    print(f"  Submodules: {', '.join(subs)}")
    print(f"  Total Files: {m_files} | Dirty Files: {m_dirty_files} | Issues: {m_issues} | Lines: {m_lines}")
    print("  Dirty files breakdown:")
    for f, iss, l in sorted(file_list, key=lambda x: x[1], reverse=True):
        print(f"    - {f} ({iss} issues, {l} lines)")

print("\n=== VERIFICATION SUMMARY ===")
print(f"Total dirty files across 5 milestones: {total_dirty_files} (expected: 78)")
print(f"Total issues across 5 milestones: {total_mapped_issues} (expected: 762)")

# Also check clean submodules
clean_subs = [k for k, v in summary.items() if v['total_issues'] == 0]
print(f"Clean submodules not in any milestone (already 100% clean): {clean_subs}")
clean_files = sum(summary[k]['total_files'] for k in clean_subs)
print(f"Total files in clean submodules: {clean_files}")
print(f"Grand Total files (mapped + clean): {total_mapped_files + clean_files} (expected: 185)")
