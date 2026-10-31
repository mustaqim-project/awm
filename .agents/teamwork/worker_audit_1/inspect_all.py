import json

with open(r'c:\laragon\www\cooca_core\.agents\teamwork\explorer_survey_1\survey_summary_v2.json', 'r', encoding='utf-8') as f:
    s1 = json.load(f)

for k, v in s1.get('architectural_patterns', {}).items():
    if isinstance(v, dict):
        print(f"  {k}: {v.get('count', len(v.get('views', [])))}")
    elif isinstance(v, list):
        print(f"  {k}: {len(v)}")
    else:
        print(f"  {k}: {v}")
