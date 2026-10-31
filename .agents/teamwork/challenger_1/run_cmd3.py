import os, glob, re

base = r'resources/views/app'
pattern = re.compile(r'class="[^"]*\bfixed\s+inset-0\b[^"]*\bflex\b[^"]*\bbg-black/(?:[0-9]+)\b')
matches = [f for f in glob.glob(os.path.join(base, '**', '*.blade.php'), recursive=True) if pattern.search(open(f, encoding='utf-8', errors='ignore').read())]
total_instances = sum(len(pattern.findall(open(f, encoding='utf-8', errors='ignore').read())) for f in matches)
print(f'Affected Views: {len(matches)} | Total Flawed Backdrop Instances: {total_instances}')
