import re

with open('routes/public.php', 'r', encoding='utf-8') as f:
    text = f.read()

targets = ['pricing', 'privacy', 'terms', 'security', 'sitemap', 'support', 'about', 'demo', 'landing']
for t in targets:
    for line in text.splitlines():
        if f"'{t}'" in line or f"'/{t}'" in line or f'"{t}"' in line or f'"/{t}"' in line:
            print(f"{t}: {line.strip()}")
