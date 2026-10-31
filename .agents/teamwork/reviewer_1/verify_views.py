import os, glob, re

report = open('docs/UI_UX_AUDIT_REPORT.md', encoding='utf-8').read()

# Find all lines like: - **Core Views**: ... or view citations in Section 4
submod_sections = re.split(r'#### \d+\.\s+', report)[1:]

print(f"Total sections parsed: {len(submod_sections)}")

for sec in submod_sections:
    header = sec.split('\n')[0]
    m = re.search(r'\(`resources/views/app(?:/([^`]+))?`\)', header)
    if not m:
        print('Could not parse dir from:', header)
        continue
    rel_dir = m.group(1) or ''

    cv_match = re.search(r'-\s+\*\*Core Views?\*\*:\s+([^\n]+)', sec)
    if cv_match:
        cv_text = cv_match.group(1)
        items = re.findall(r'`([^`]+)`', cv_text)
        for item in items:
            clean_item = item.split(' ')[0]
            if clean_item.endswith('.blade.php') or clean_item.endswith('.php'):
                expected_path = os.path.join('resources', 'views', 'app', rel_dir, clean_item)
                alt_path = os.path.join('resources', 'views', 'app', clean_item)
                if not os.path.exists(expected_path) and not os.path.exists(alt_path):
                    print(f'NONEXISTENT FILE in [{header}]: item="{item}" -> looked for "{expected_path}" & "{alt_path}"')
