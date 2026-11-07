import os
import re

view_dirs = [
    r"c:\laragon\www\cooca_core\resources\views\app\ai",
    r"c:\laragon\www\cooca_core\resources\views\app\ai-studio"
]

files = []
for d in view_dirs:
    for root, dirs, fnames in os.walk(d):
        for f in fnames:
            if f.endswith('.blade.php'):
                files.append(os.path.join(root, f))

print(f"Total view files: {len(files)}")

auth_patterns = [
    re.compile(r'@can\([^\)]+\)'),
    re.compile(r'@cannot\([^\)]+\)'),
    re.compile(r'@role\([^\)]+\)'),
    re.compile(r'Context::hasPermission\([^\)]+\)'),
    re.compile(r'Context::isOwner\([^\)]*\)'),
    re.compile(r'Context::isAdminOrOwner\([^\)]*\)'),
    re.compile(r'hasPermission\([^\)]+\)'),
]

button_pattern = re.compile(r'(<button\b[^>]*>|<form\b[^>]*>|<a\b[^>]*class=[\'"][^\'"]*btn[^\'"]*[\'"][^>]*>)', re.IGNORECASE)

results = {}

for path in sorted(files):
    rel = os.path.relpath(path, r"c:\laragon\www\cooca_core")
    with open(path, 'r', encoding='utf-8', errors='ignore') as f:
        content = f.read()
    
    # Find auth guards
    guards = []
    for ap in auth_patterns:
        guards.extend(ap.findall(content))
    
    # Find action elements (buttons, forms, btn links)
    actions = button_pattern.findall(content)
    
    results[rel] = {
        'guards': guards,
        'action_count': len(actions),
        'sample_actions': actions[:5]
    }
    
    print(f"File: {rel}")
    print(f"  Auth guards found ({len(guards)}): {guards}")
    print(f"  Action elements found: {len(actions)}")
    if actions:
        print(f"  First 3 actions:")
        for a in actions[:3]:
            # truncate action for display
            clean_a = ' '.join(a.split())[:100]
            print(f"    - {clean_a}")
    print("-" * 50)
