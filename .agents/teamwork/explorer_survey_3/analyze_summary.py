import json

with open(r'c:\laragon\www\cooca_core\.agents\teamwork\explorer_survey_3\survey_data.json', 'r', encoding='utf-8') as f:
    data = json.load(f)

trans = data['translations']
views_issues = data['views_issues']
patterns = data['patterns']

print("=== TRANSLATION FILES OVERVIEW ===")
print(f"Total modular PHP files: {trans['total_files']}")
print(f"Total keys in id: {trans['total_id_keys']}")
print(f"Total keys in en: {trans['total_en_keys']}")
print(f"Total common keys: {trans['total_common_keys']}")
print(f"Missing in EN: {trans['total_missing_in_en']}")
print(f"Missing in ID: {trans['total_missing_in_id']}")
print(f"Bilingual parity: {trans['overall_parity_pct']:.2f}%")

print("\n=== TOP 20 TRANSLATION FILES BY KEY COUNT ===")
top_trans = sorted(trans['file_stats'], key=lambda x: x['id_keys'], reverse=True)[:20]
for t in top_trans:
    print(f"  {t['file']}: {t['id_keys']} keys, max_depth: {t['max_depth']}, parity: {t['parity_pct']:.1f}%")

print("\n=== DOMAIN BREAKDOWN OF 762 ISSUES IN APP VIEWS ===")
for dom, count in sorted(views_issues['total_domain_counts'].items(), key=lambda x: x[1], reverse=True):
    print(f"  {dom}: {count} issues ({count/762*100:.1f}%)")

print("\n=== ISSUE TYPES BREAKDOWN ===")
for itype, count in sorted(views_issues['total_type_counts'].items(), key=lambda x: x[1], reverse=True):
    print(f"  {itype}: {count} issues")

print("\n=== TOP SUBMODULES BY ISSUES ===")
submods = sorted(views_issues['submodule_summary'].items(), key=lambda x: x[1]['total_issues'], reverse=True)
for sub, s in submods:
    if s['total_issues'] > 0:
        print(f"  {sub}: {s['total_issues']} issues across {s['files_with_issues']}/{s['total_files']} files (clean: {s['clean_files']}, lines: {s['total_lines']}, i18n_calls: {s['total_i18n_calls']})")

print("\n=== SUBMODULES WITH 0 ISSUES (100% CLEAN) ===")
clean_submods = [sub for sub, s in submods if s['total_issues'] == 0]
print(f"  Clean submodules ({len(clean_submods)}): {', '.join(clean_submods)}")

print("\n=== TOP 20 FILES WITH MOST ISSUES ===")
all_file_issues = []
for sub, s in views_issues['submodule_summary'].items():
    for f in s['file_details']:
        if f['issues'] > 0:
            all_file_issues.append(f)
all_file_issues.sort(key=lambda x: x['issues'], reverse=True)
for fi in all_file_issues[:20]:
    print(f"  {fi['file']}: {fi['issues']} issues (lines: {fi['lines']}, i18n_calls: {fi['i18n_calls']}) -> {fi['issue_types']}")

print("\n=== TOP TRANSLATION PREFIXES USED IN APP VIEWS ===")
for pref, count in patterns['translation_prefixes_top30']:
    print(f"  {pref}: {count} calls")

print("\n=== FORMATTING HELPERS ===")
for k, v in patterns['formatting_helpers'].items():
    print(f"  {k}: {v} occurrences")

print(f"\nTotal files with Alpine x-data: {patterns['files_with_alpine']} / 185 files")
print(f"Total x-data instances: {patterns['total_alpine_x_data']}")
