import json

with open("scripts/survey_audit_results.json", "r") as f:
    res = json.load(f)

print("--- Missing Features Inspection ---")
for r in res:
    if r["status"] == "Missing":
        print(f"ID: {r['id']:>2} | {r['filename']:<45} | Blade: {r['blade']}")
