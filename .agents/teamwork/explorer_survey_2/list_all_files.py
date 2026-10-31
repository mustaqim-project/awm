import json

with open('.agents/teamwork/explorer_survey_2/inventory.json', 'r', encoding='utf-8') as f:
    data = json.load(f)

for sub, info in sorted(data['submodules'].items()):
    print(f"\n### Submodule: `{sub}` ({info['file_count']} files, {info['total_lines']} lines)")
    sub_files = [f for f in data['files'] if f['submodule'] == sub]
    for f in sub_files:
        flags = []
        if f['has_alpine']: flags.append(f"Alpine(x-data={f['alpine_x_data']})")
        if f['has_modal']: flags.append("Modal")
        if f['has_table']: flags.append(f"Table({f['table_count']})")
        if f['has_form']: flags.append(f"Form({f['form_count']})")
        flag_str = ", ".join(flags) if flags else "Static"
        print(f"  - `{f['file']}` ({f['lines']} lines) [{flag_str}]")
