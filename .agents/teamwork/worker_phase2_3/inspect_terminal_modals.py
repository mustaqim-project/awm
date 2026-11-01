import os

lines = open('resources/views/app/pos/terminal.blade.php', encoding='utf-8', errors='ignore').readlines()
lnos = [2161, 2552, 2675, 3196, 3331, 3390, 3436, 3576, 3704, 3779, 3923, 4170, 4456, 4801]

for l in lnos:
    idx = l - 1
    title = ""
    for j in range(idx, min(idx + 10, len(lines))):
        if '<h' in lines[j] or 'font-semibold' in lines[j] or 'font-bold' in lines[j]:
            title = lines[j].strip()
            break
    print(f"Line {l}: {lines[idx].strip()[:80]}")
    print(f"   Title: {title[:80]}")
