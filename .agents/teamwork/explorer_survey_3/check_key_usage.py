import os
import re
from collections import Counter, defaultdict

APP_DIR = r"c:\laragon\www\cooca_core\resources\views\app"

key_patterns = Counter()
module_to_prefixes = defaultdict(Counter)
raw_keys = Counter()

# Regex to find translation calls: __('key') or @lang('key')
pattern = re.compile(r"(?:__|@lang)\s*\(\s*['\"]([^'\"]+)['\"]")

for root, dirs, files in os.walk(APP_DIR):
    for f in files:
        if not f.endswith('.blade.php'):
            continue
        filepath = os.path.join(root, f)
        rel = os.path.relpath(filepath, APP_DIR).replace('\\', '/')
        submod = 'root' if '/' not in rel else rel.split('/')[0]
        
        with open(filepath, 'r', encoding='utf-8', errors='ignore') as fp:
            content = fp.read()
            
        matches = pattern.findall(content)
        for k in matches:
            raw_keys[k] += 1
            parts = k.split('.')
            prefix = parts[0]
            module_to_prefixes[submod][prefix] += 1
            if len(parts) > 1:
                key_patterns[f"{prefix}.*"] += 1
            else:
                key_patterns["single_word_or_root"] += 1

print("=== TRANSLATION CALLS SUMMARY ===")
print(f"Total translation calls found: {sum(raw_keys.values())}")
print(f"Total unique translation keys: {len(raw_keys)}")

print("\n=== TOP 25 UNIQUE TRANSLATION KEYS ===")
for k, count in raw_keys.most_common(25):
    print(f"  {k}: {count} calls")

print("\n=== CROSS-MODULE TRANSLATION PREFIX USAGE (Top 10 submodules) ===")
for submod in ['pos', 'finance', 'ai', 'inventory', 'sales', 'settings', 'storefront', 'hrm', 'billing', 'social_media']:
    prefs = module_to_prefixes[submod].most_common(5)
    print(f"  [{submod}] -> {prefs}")
