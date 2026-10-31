import os
import re
import json

admin_dir = os.path.abspath('resources/views/admin')

complex_patterns = {
    'alpine_x_data_objects': [],      # x-data with inline string literals
    'alpine_dynamic_bindings': [],    # :title="...", :placeholder="...", :aria-label="..."
    'alpine_x_text_raw': [],          # x-text="'...'"
    'js_confirm_alert': [],           # confirm('...'), alert('...')
    'ternary_strings': [],            # {{ $var ? 'Text ID' : 'Text ID' }}
    'dynamic_interpolation': [],      # text containing {{ $var }} inside strings
    'modal_structures': [],           # modal components and backdrops
    'dropdown_actions': [],           # action dropdown menus
    'raw_attrs': []                   # title="...", placeholder="...", aria-label="..."
}

file_reports = {}

for root, dirs, files in os.walk(admin_dir):
    for f in sorted(files):
        if not f.endswith('.blade.php'):
            continue
        fp = os.path.join(root, f)
        rel_path = os.path.relpath(fp, admin_dir).replace('\\', '/')
        
        with open(fp, 'r', encoding='utf-8', errors='ignore') as file_obj:
            content = file_obj.read()
            lines = content.splitlines()

        file_info = {
            'file': rel_path,
            'lines': len(lines),
            'x_data_count': len(re.findall(r'x-data\s*=', content)),
            'x_data_snippets': [],
            'js_confirms': [],
            'ternary_count': 0,
            'ternary_samples': [],
            'bound_attrs': [],
            'raw_attrs': [],
            'modals': [],
            'tables': len(re.findall(r'<table\b', content, re.I))
        }

        # 1. x-data snippets
        for m in re.finditer(r'x-data\s*=\s*(["\'])(.*?)\1', content, re.DOTALL):
            snip = m.group(2).strip()
            if len(snip) > 10:
                file_info['x_data_snippets'].append(snip[:120])

        # 2. JS confirm/alert/prompt in Blade
        for m in re.finditer(r'(confirm|alert|prompt)\s*\(\s*(["\'])(.*?)\2\s*\)', content):
            msg = m.group(3).strip()
            line_no = content[:m.start()].count('\n') + 1
            file_info['js_confirms'].append({
                'line': line_no,
                'func': m.group(1),
                'msg': msg
            })

        # 3. Ternary string expressions: {{ ... ? '...' : '...' }}
        for m in re.finditer(r'\{\{\s*([^?]+?)\s*\?\s*([\'"][^\'"]+[\'"])\s*:\s*([\'"][^\'"]+[\'"])\s*\}\}', content):
            file_info['ternary_count'] += 1
            if len(file_info['ternary_samples']) < 5:
                line_no = content[:m.start()].count('\n') + 1
                file_info['ternary_samples'].append({
                    'line': line_no,
                    'snippet': m.group(0)
                })

        # 4. Bound attributes: :title, :placeholder, :aria-label
        for m in re.finditer(r':(title|placeholder|aria-label|alt)\s*=\s*(["\'])(.*?)\2', content):
            line_no = content[:m.start()].count('\n') + 1
            file_info['bound_attrs'].append({
                'line': line_no,
                'attr': m.group(1),
                'expr': m.group(3)
            })

        # 5. Raw attributes with Indonesian words: placeholder, title, aria-label
        for m in re.finditer(r'(?<!:)\b(placeholder|title|aria-label|alt)\s*=\s*(["\'])(.*?)\2', content):
            val = m.group(3).strip()
            if val and not val.startswith('{{') and not val.startswith('$'):
                line_no = content[:m.start()].count('\n') + 1
                file_info['raw_attrs'].append({
                    'line': line_no,
                    'attr': m.group(1),
                    'val': val
                })

        # 6. Modals
        for m in re.finditer(r'(x-show\s*=\s*["\'][^"\']*(?:modal|dialog|detail|create|edit)[^"\']*["\']|role\s*=\s*["\']dialog["\'])', content, re.I):
            line_no = content[:m.start()].count('\n') + 1
            file_info['modals'].append({
                'line': line_no,
                'match': m.group(0)
            })

        file_reports[rel_path] = file_info

out_path = os.path.abspath('.agents/teamwork/explorer_survey_2/complex_ui_analysis.json')
with open(out_path, 'w', encoding='utf-8') as f:
    json.dump(file_reports, f, indent=2)

print("Complex UI Analysis complete.")
print(f"Files analyzed: {len(file_reports)}")

# High-level summary of complex UI
total_x_data = sum(f['x_data_count'] for f in file_reports.values())
total_confirms = sum(len(f['js_confirms']) for f in file_reports.values())
total_ternaries = sum(f['ternary_count'] for f in file_reports.values())
total_bound_attrs = sum(len(f['bound_attrs']) for f in file_reports.values())
total_raw_attrs = sum(len(f['raw_attrs']) for f in file_reports.values())
total_modals = sum(len(f['modals']) for f in file_reports.values())

print(f"Total x-data declarations: {total_x_data}")
print(f"Total JS confirm/alert calls: {total_confirms}")
print(f"Total Blade ternary string expressions: {total_ternaries}")
print(f"Total Bound attributes (:attr): {total_bound_attrs}")
print(f"Total Raw translatable attributes (attr=): {total_raw_attrs}")
print(f"Total Modal dialog triggers/backdrops: {total_modals}")
