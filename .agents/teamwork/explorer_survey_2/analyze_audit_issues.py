import json
import subprocess

res = subprocess.run(['python', 'scratch/deep_audit_v2.py', 'resources/views/admin'], capture_output=True, text=True, encoding='utf-8')
data = json.loads(res.stdout)

admin_data = data['resources/views/admin']
files = admin_data['files']

issue_types = {}
issue_domains = {}
file_issue_counts = []

for f in files:
    file_issue_counts.append((f['filepath'], f['issue_count']))
    for iss in f['issues']:
        t = iss['type']
        d = iss['domain']
        issue_types[t] = issue_types.get(t, 0) + 1
        issue_domains[d] = issue_domains.get(d, 0) + 1

print(f"Total issues detected by deep_audit_v2: {admin_data['total_issues']}")
print("\nIssue count by domain:")
for d, c in sorted(issue_domains.items(), key=lambda x: x[1], reverse=True):
    print(f"  {d}: {c}")

print("\nIssue count by type:")
for t, c in sorted(issue_types.items(), key=lambda x: x[1], reverse=True):
    print(f"  {t}: {c}")

print("\nTop 15 files with most issues:")
for fp, c in sorted(file_issue_counts, key=lambda x: x[1], reverse=True)[:15]:
    print(f"  {c:>3} issues: {fp}")
