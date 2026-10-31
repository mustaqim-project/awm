import json
import sys

if hasattr(sys.stdout, 'reconfigure'):
    sys.stdout.reconfigure(encoding='utf-8', errors='replace')

with open('.agents/teamwork/explorer_survey_2/complex_ui_analysis.json', 'r', encoding='utf-8') as f:
    data = json.load(f)

print("=== SAMPLE TERNARY EXPRESSIONS ===")
count = 0
for file, info in data.items():
    if info['ternary_samples']:
        print(f"\nFile: {file}")
        for t in info['ternary_samples']:
            print(f"  Line {t['line']}: {t['snippet']}")
            count += 1
            if count >= 20:
                break
    if count >= 20:
        break
