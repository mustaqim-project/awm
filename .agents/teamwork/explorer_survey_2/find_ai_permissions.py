import os
import re

patterns = [
    ('hasPermission', re.compile(r'hasPermission\(\s*[\'\"]([^\'\"]+)[\'\"]\s*\)')),
    ('require.permission', re.compile(r'require\.permission:([a-zA-Z0-9_\.]+)')),
    ('@can', re.compile(r'@can\(\s*[\'\"]([^\'\"]+)[\'\"]\s*\)')),
    ('@cannot', re.compile(r'@cannot\(\s*[\'\"]([^\'\"]+)[\'\"]\s*\)')),
]

found = set()
for root, dirs, files in os.walk(r'c:\laragon\www\cooca_core'):
    if any(x in root for x in ['vendor', 'node_modules', '.git', 'storage', '.agents']):
        continue
    for file in files:
        if file.endswith(('.php', '.blade.php')):
            path = os.path.join(root, file)
            try:
                with open(path, 'r', encoding='utf-8', errors='ignore') as f:
                    content = f.read()
                for p_type, p in patterns:
                    for m in p.finditer(content):
                        perm = m.group(1)
                        if 'ai' in perm.lower():
                            found.add((perm, p_type, os.path.relpath(path, r'c:\laragon\www\cooca_core')))
            except Exception as e:
                pass

for perm, p_type, path in sorted(found):
    print(f'{perm:<30} [{p_type:<18}] {path}')
