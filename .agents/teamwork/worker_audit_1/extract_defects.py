import json

with open(r'c:\laragon\www\cooca_core\.agents\teamwork\explorer_survey_1\survey_summary_v2.json', 'r', encoding='utf-8') as f:
    s1 = json.load(f)

for k, v in s1['architectural_patterns'].items():
    print(k, type(v), len(v) if hasattr(v, '__len__') else v)
    if isinstance(v, list) and len(v) > 0:
        print("   sample:", v[0])
