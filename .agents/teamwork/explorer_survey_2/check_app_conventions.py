import os
import re

pattern = re.compile(r"__\(\s*['\"]([a-zA-Z0-9_\.\-]+)['\"]")
modules = {}
total_calls = 0

for root, dirs, files in os.walk('resources/views/app'):
    for file in files:
        if file.endswith('.blade.php'):
            with open(os.path.join(root, file), 'r', encoding='utf-8', errors='ignore') as f:
                content = f.read()
            for m in pattern.finditer(content):
                key = m.group(1)
                total_calls += 1
                prefix = key.split('.')[0] if '.' in key else 'none'
                modules[prefix] = modules.get(prefix, 0) + 1

print(f"Total i18n calls in resources/views/app: {total_calls}")
print("Distribution by file prefix:")
for k, v in sorted(modules.items(), key=lambda x: x[1], reverse=True)[:25]:
    print(f"  {k}: {v}")
