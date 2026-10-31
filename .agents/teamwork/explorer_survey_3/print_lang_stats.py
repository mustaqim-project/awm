import json

with open(r'.agents/teamwork/explorer_survey_3/survey_data.json', 'r', encoding='utf-8') as f:
    d = json.load(f)

stats = d['translations']['file_stats']
stats.sort(key=lambda x: x['file'])

print(f"{'File':<30} {'ID Keys':<10} {'EN Keys':<10} {'Max Depth':<10} {'Parity':<8}")
print("-" * 72)
for s in stats:
    print(f"{s['file']:<30} {s['id_keys']:<10} {s['en_keys']:<10} {s['max_depth']:<10} {s['parity_pct']:.0f}%")
