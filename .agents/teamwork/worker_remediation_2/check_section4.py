import os
import re

base = r'resources/views/app'

report = open('docs/UI_UX_AUDIT_REPORT.md', encoding='utf-8').read()

sec4_match = re.search(r'## 4\. Submodule-by-Submodule Detailed Evaluation.*?(?=## 5\.)', report, re.DOTALL)
if not sec4_match:
    print("Section 4 not found!")
    exit(1)

sec4 = sec4_match.group(0)
headings = re.findall(r'#### \d+\.\s+([^\n]+)', sec4)
print(f"Total Section 4 headings: {len(headings)}")

for h in headings:
    m = re.search(r'`resources/views/app(?:/([^`]+))?`', h)
    if m:
        sub = m.group(1)
        if sub is None:
            files = [f for f in os.listdir(base) if os.path.isfile(os.path.join(base, f)) and f.endswith('.blade.php')]
            print(f"Heading: {h[:40]}... -> Root: {len(files)} files")
        else:
            p = os.path.join(base, sub)
            if not os.path.exists(p):
                print(f"DIRECTORY DOES NOT EXIST: {p}")
            else:
                files = [os.path.relpath(os.path.join(r, f), p).replace('\\', '/') for r, d, fs in os.walk(p) for f in fs if f.endswith('.blade.php')]
                # print(f"Heading: {sub} -> {len(files)} views")
    else:
        print(f"Could not parse path from heading: {h}")
