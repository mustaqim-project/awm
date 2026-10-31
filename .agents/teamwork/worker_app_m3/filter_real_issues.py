import json
import re

with open('.agents/teamwork/worker_app_m3/potential_unlocalized.json', 'r', encoding='utf-8') as f:
    data = json.load(f)

for filepath, items in data.items():
    real = []
    for item in items:
        raw = item['raw']
        # If line contains __() or @lang() and no unlocalized text outside, check:
        # e.g., if the raw line already has {{ __('...') }} wrapping the text, skip
        if "__(" in raw or "@lang(" in raw:
            # Let's see if the line still has raw text outside __()
            # Remove all __() and @lang()
            temp = re.sub(r"__\s*\([^)]*\)", "", raw)
            temp = re.sub(r"@lang\s*\([^)]*\)", "", temp)
            # Remove HTML tags and attributes
            temp = re.sub(r"<[^>]+>", "", temp)
            # Remove JS / blade variables
            temp = re.sub(r"\{\{.*?\}\}", "", temp)
            temp = re.sub(r"\{!!.*?!!\}", "", temp)
            temp = re.sub(r"\$[a-zA-Z0-9_]+", "", temp)
            temp = re.sub(r"[\s\d\.,:;=+\-*\/\\\"\'\(\)\[\]\{\}><!?|&]+", " ", temp).strip()
            # If no meaningful words left, skip
            words = [w for w in temp.split() if len(w) > 2]
            if not words or not any(w in item['words'] for w in words):
                continue

        # Ignore lines that are comments
        stripped = raw.strip()
        if stripped.startswith('//') or stripped.startswith('/*') or stripped.startswith('*') or stripped.startswith('<!--'):
            continue
        # Ignore JS variables, e.g. let total = 0
        if re.search(r'^(?:let|var|const)\s+[a-zA-Z0-9_]+\s*=', stripped):
            continue
        if re.search(r'^\.pos-[a-zA-Z0-9_-]+', stripped): # CSS class
            continue
        if stripped.startswith('total +=') or stripped.startswith('return total'):
            continue
        if re.search(r'^[a-zA-Z0-9_]+\s*:\s*(?:true|false|null|\d+|\[\]|\{\}|[\'"][a-zA-Z0-9_\-\.]+[\'"]),?$', stripped):
            continue
        real.append((item['line'], raw))

    if real:
        print(f"\n=======================================================")
        print(f"{filepath} ({len(real)} real issues)")
        print(f"=======================================================")
        for line_no, raw in real:
            print(f"  L{line_no}: {raw[:110]}")
