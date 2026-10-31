import os
import json
import re

admin_dir = os.path.abspath('resources/views/admin')
files_data = []

for root, dirs, files in os.walk(admin_dir):
    for file in files:
        if file.endswith('.blade.php'):
            full_path = os.path.join(root, file)
            rel_path = os.path.relpath(full_path, admin_dir).replace('\\', '/')
            with open(full_path, 'r', encoding='utf-8', errors='ignore') as f:
                content = f.read()
            lines = content.splitlines()
            line_count = len(lines)
            
            # Submodule (first directory or root)
            parts = rel_path.split('/')
            submodule = parts[0] if len(parts) > 1 else '_root'
            
            # i18n detection
            has_i18n = bool(re.search(r'__\(|@lang\(|trans\(', content))
            i18n_matches = len(re.findall(r'__\(|@lang\(|trans\(', content))
            
            # Alpine detection
            has_alpine = bool(re.search(r'x-data|x-show|x-if|x-model|x-text|x-html|x-on:|@click', content))
            alpine_x_data = len(re.findall(r'x-data', content))
            
            # Table detection
            has_table = '<table' in content.lower()
            table_count = len(re.findall(r'<table', content, re.I))
            
            # Modal detection
            has_modal = bool(re.search(r'modal|role=["\']dialog["\']|x-show=.*modal|openModal|showModal', content, re.I))
            
            # Dropdown detection
            has_dropdown = bool(re.search(r'dropdown|openDropdown|menuOpen|actionOpen', content, re.I))
            
            # Form detection
            has_form = '<form' in content.lower()
            form_count = len(re.findall(r'<form', content, re.I))
            
            # Input count
            inputs = len(re.findall(r'<input\b|<textarea\b|<select\b', content, re.I))
            
            files_data.append({
                'file': rel_path,
                'full_path': full_path,
                'submodule': submodule,
                'lines': line_count,
                'has_i18n': has_i18n,
                'i18n_matches': i18n_matches,
                'has_alpine': has_alpine,
                'alpine_x_data': alpine_x_data,
                'has_table': has_table,
                'table_count': table_count,
                'has_modal': has_modal,
                'has_dropdown': has_dropdown,
                'has_form': has_form,
                'form_count': form_count,
                'inputs': inputs
            })

# Sort files alphabetically
files_data.sort(key=lambda x: x['file'])

# Summary stats
total_files = len(files_data)
total_lines = sum(x['lines'] for x in files_data)
files_with_i18n = sum(1 for x in files_data if x['has_i18n'])
files_with_alpine = sum(1 for x in files_data if x['has_alpine'])
files_with_tables = sum(1 for x in files_data if x['has_table'])
files_with_modals = sum(1 for x in files_data if x['has_modal'])
files_with_forms = sum(1 for x in files_data if x['has_form'])

# Submodule groupings
submodules = {}
for item in files_data:
    sub = item['submodule']
    if sub not in submodules:
        submodules[sub] = {
            'file_count': 0,
            'total_lines': 0,
            'files': []
        }
    submodules[sub]['file_count'] += 1
    submodules[sub]['total_lines'] += item['lines']
    submodules[sub]['files'].append(item['file'])

out_data = {
    'summary': {
        'total_files': total_files,
        'total_lines': total_lines,
        'files_with_i18n': files_with_i18n,
        'files_without_i18n': total_files - files_with_i18n,
        'files_with_alpine': files_with_alpine,
        'files_with_tables': files_with_tables,
        'files_with_modals': files_with_modals,
        'files_with_forms': files_with_forms,
        'total_submodules': len(submodules)
    },
    'submodules': submodules,
    'files': files_data
}

out_path = os.path.abspath('.agents/teamwork/explorer_survey_2/inventory.json')
with open(out_path, 'w', encoding='utf-8') as f:
    json.dump(out_data, f, indent=2)

print(f"Total files: {total_files}")
print(f"Total lines: {total_lines}")
print(f"Submodules: {len(submodules)}")
print(f"Files with i18n: {files_with_i18n}")
print(f"Files with Alpine.js: {files_with_alpine}")
print(f"Files with tables: {files_with_tables}")
print(f"Files with modals: {files_with_modals}")
print(f"Files with forms: {files_with_forms}")
