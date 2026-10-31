import json
from collections import Counter

with open(r'.agents/teamwork/explorer_survey_3/survey_data.json', 'r', encoding='utf-8') as f:
    d = json.load(f)

# Let's inspect all issues from deep_audit_v2
import subprocess
cmd = ['python', r'scratch/deep_audit_v2.py', r'resources/views/app']
p = subprocess.run(cmd, capture_output=True, text=True, encoding='utf-8')
data = json.loads(p.stdout)['resources/views/app']

issues = []
for f in data['files']:
    for iss in f['issues']:
        issues.append({
            'file': f['filepath'].replace('\\', '/').replace('resources/views/app/', ''),
            'domain': iss['domain'],
            'type': iss['type'],
            'line': iss['line'],
            'text': iss['text'],
            'snippet': iss['snippet']
        })

print(f"Total extracted issues: {len(issues)}")

# Frequency of raw texts
text_freq = Counter([i['text'].strip() for i in issues])
print("\n=== TOP 30 MOST FREQUENT HARDCODED STRINGS ===")
for text, cnt in text_freq.most_common(30):
    print(f"  [{cnt}x] {text}")

# Group by type
type_samples = {}
for i in issues:
    itype = i['type']
    if itype not in type_samples:
        type_samples[itype] = []
    if len(type_samples[itype]) < 3:
        type_samples[itype].append(f"{i['file']}:{i['line']} -> {i['text']}")

print("\n=== SAMPLES PER ISSUE TYPE ===")
for itype, samples in type_samples.items():
    print(f"\nType: {itype} ({len([i for i in issues if i['type'] == itype])} total)")
    for s in samples:
        print(f"   {s}")
