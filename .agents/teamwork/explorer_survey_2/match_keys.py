import re
import json

def parse_php_lang(filepath):
    with open(filepath, 'r', encoding='utf-8', errors='ignore') as f:
        content = f.read()
    pattern = re.compile(r"['\"]([a-zA-Z0-9_\.\-]+)['\"]\s*=>\s*(['\"])(.*?)\2", re.DOTALL)
    keys = {}
    for m in pattern.finditer(content):
        keys[m.group(1)] = m.group(3)
    return keys

id_admin = parse_php_lang('lang/id/admin.php')

# Load all admin view contents
import os
admin_dir = 'resources/views/admin'
view_texts = {}
for root, dirs, files in os.walk(admin_dir):
    for file in files:
        if file.endswith('.blade.php'):
            fp = os.path.join(root, file)
            with open(fp, 'r', encoding='utf-8', errors='ignore') as f:
                view_texts[fp] = f.read()

# For each key in id_admin, see if its Indonesian value appears in any view file!
matched_keys = 0
key_matches = {}

for k, val in id_admin.items():
    val_clean = val.strip()
    if not val_clean or len(val_clean) < 3:
        continue
    # check if val_clean appears in any view file
    found_in = []
    for fp, content in view_texts.items():
        if val_clean in content:
            found_in.append(fp)
    if found_in:
        matched_keys += 1
        key_matches[k] = {
            'value': val_clean,
            'files': found_in
        }

print(f"Total keys in id_admin: {len(id_admin)}")
print(f"Keys whose exact Indonesian string exists in admin views: {matched_keys} ({matched_keys/len(id_admin)*100:.1f}%)")

# Let's see some samples of matched keys
print("\nSample matched keys:")
for k in list(key_matches.keys())[:10]:
    print(f"  {k} => '{key_matches[k]['value']}' (in {len(key_matches[k]['files'])} files)")
