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
en_admin = parse_php_lang('lang/en/admin.php')

print(f"Total keys in lang/id/admin.php: {len(id_admin)}")
print(f"Total keys in lang/en/admin.php: {len(en_admin)}")

# Check key overlap
id_keys = set(id_admin.keys())
en_keys = set(en_admin.keys())

diff_id_only = id_keys - en_keys
diff_en_only = en_keys - id_keys

print(f"Keys in id only: {len(diff_id_only)}")
print(f"Keys in en only: {len(diff_en_only)}")

# Analyze key naming patterns
prefixes = {}
for k in id_keys:
    if '_' in k:
        prefix = k.split('_')[0]
    else:
        prefix = 'single_word'
    prefixes[prefix] = prefixes.get(prefix, 0) + 1

print("\nTop 20 key prefixes:")
for p, c in sorted(prefixes.items(), key=lambda x: x[1], reverse=True)[:20]:
    print(f"  {p}: {c}")

# Sample keys
print("\nSample keys:")
for k in list(id_keys)[:15]:
    print(f"  {k} => '{id_admin[k]}'")
