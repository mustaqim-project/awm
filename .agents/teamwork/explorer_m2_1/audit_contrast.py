import re

target_files = [
    'resources/views/landing.blade.php',
    'resources/views/public/business_landing.blade.php',
    'resources/views/public/about.blade.php',
    'resources/views/public/demo.blade.php',
    'resources/views/public/pricing.blade.php',
    'resources/views/public/privacy.blade.php',
    'resources/views/public/security.blade.php',
    'resources/views/public/sitemap.blade.php',
    'resources/views/public/support.blade.php',
    'resources/views/public/terms.blade.php',
]

def scan_contrast(filepath):
    print("=" * 70)
    print(f"CONTRAST SCAN: {filepath}")
    print("=" * 70)

    with open(filepath, 'r', encoding='utf-8', errors='ignore') as f:
        lines = f.readlines()

    findings = []
    for idx, line in enumerate(lines):
        lno = idx + 1

        # Check for bg-white without dark:bg
        if re.search(r'\bbg-white\b', line) and not re.search(r'\bdark:bg-', line) and not re.search(r'\bbg-white\/(?:10|20|30|40|50|60|70|80|90|95)\b', line) and not 'text-white' in line:
            # check if inside dark-only section (e.g. hero with bg-[#060B1E])
            findings.append((lno, "bg-white without dark:bg-*", line.strip()[:100]))

        # Check for text-black without dark:text
        if re.search(r'\btext-black\b', line) and not re.search(r'\bdark:text-', line) and not re.search(r'\btext-black\/(?:50|60|70|80)\b', line):
            findings.append((lno, "text-black without dark:text-*", line.strip()[:100]))

        # Check for text-slate-900 / text-slate-800 without dark:text
        if re.search(r'\btext-slate-[89]00\b', line) and not re.search(r'\bdark:text-', line):
            findings.append((lno, "text-slate-800/900 without dark:text-*", line.strip()[:100]))

        # Check for border-slate-200 without dark:border
        if re.search(r'\bborder-slate-200\b', line) and not re.search(r'\bdark:border-', line):
            findings.append((lno, "border-slate-200 without dark:border-*", line.strip()[:100]))

    print(f"Total potential contrast issues found: {len(findings)}")
    for lno, issue_type, snippet in findings[:10]:
        print(f"  Line {lno:4d} [{issue_type}]: {snippet}")

for f in target_files:
    scan_contrast(f)
