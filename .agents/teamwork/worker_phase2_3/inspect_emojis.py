import os, glob, re, sys

sys.stdout.reconfigure(encoding='utf-8')

base = r'resources/views/app'
pattern = re.compile(r'[\U0001F300-\U0001FAFF\U00002702-\U000027B0\U000024C2-\U0001F251]|★|⭐|●|🚗|🧺|💊|🏷|✓|✕')

views = set()
for f in glob.glob(os.path.join(base, '**', '*.blade.php'), recursive=True):
    content = open(f, encoding='utf-8', errors='ignore').readlines()
    matches = []
    for idx, line in enumerate(content):
        m = pattern.findall(line)
        if m:
            matches.append((idx + 1, list(set(m)), line.strip()))
    if matches:
        views.add(f)
        print(f"FILE: {f} ({len(matches)} lines)")
        for lno, chars, text in matches:
            # print repr to be safe
            print(f"  [{lno}] {[repr(c) for c in chars]} -> {text[:100]}")

print(f"\nTotal Views with matches: {len(views)}")
