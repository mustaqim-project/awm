import os, re
txt = open('docs/UI_UX_AUDIT_REPORT.md', encoding='utf-8').read()
lines = re.findall(r'^\d+\.\s+`([^`]+)`', txt, re.MULTILINE)
missing = [p for p in lines if not os.path.exists(os.path.join('resources/views/app', p.replace('/', os.sep)))]
print(f'Total manifest entries: {len(lines)} | Missing files: {len(missing)}')
if missing:
    print('Still missing:', missing)
