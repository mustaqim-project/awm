import json

with open('.agents/teamwork/explorer_survey_2/inventory.json', 'r', encoding='utf-8') as f:
    data = json.load(f)

print(f"{'Submodule':<25} | {'Files':<5} | {'Lines':<7} | {'Alpine':<6} | {'Modals':<6} | {'Tables':<6}")
print("-" * 65)
sorted_subs = sorted(data['submodules'].items(), key=lambda x: x[1]['total_lines'], reverse=True)
for sub, info in sorted_subs:
    files = [f for f in data['files'] if f['submodule'] == sub]
    alp = sum(1 for f in files if f['has_alpine'])
    mod = sum(1 for f in files if f['has_modal'])
    tbl = sum(1 for f in files if f['has_table'])
    print(f"{sub:<25} | {info['file_count']:>5} | {info['total_lines']:>7} | {alp:>6} | {mod:>6} | {tbl:>6}")
