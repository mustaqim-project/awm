import os, glob, re

base = r'resources/views/app'
pattern = re.compile(r'class="[^"]*\bfixed\s+inset-0\b[^"]*\bflex\b[^"]*\bbg-black/(?:[0-9]+)\b')
matches = [f for f in glob.glob(os.path.join(base, '**', '*.blade.php'), recursive=True) if pattern.search(open(f, encoding='utf-8', errors='ignore').read())]
total_instances = sum(len(pattern.findall(open(f, encoding='utf-8', errors='ignore').read())) for f in matches)

print(f"Affected Views: {len(matches)} | Total Flawed Backdrop Instances: {total_instances}")

# Also check individual views breakdown
breakdown = {}
for f in matches:
    rel = os.path.relpath(f, base).replace('\\', '/')
    count = len(pattern.findall(open(f, encoding='utf-8', errors='ignore').read()))
    breakdown[rel] = count

sorted_breakdown = sorted(breakdown.items(), key=lambda x: x[1], reverse=True)
print("Breakdown per view:")
for rel, count in sorted_breakdown:
    print(f"  {count} in {rel}")
