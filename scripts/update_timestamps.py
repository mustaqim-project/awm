# scripts/update_timestamps.py
updates = {
    'scripts/generate_batch13_part1.py': [
        ('"published_at": "2026-11-16T08:15:00+07:00"', '"published_at": "2026-11-16T08:03:00+07:00"'),
        ('"published_at": "2026-11-16T08:30:00+07:00"', '"published_at": "2026-11-16T08:06:00+07:00"'),
        ('"published_at": "2026-11-16T08:45:00+07:00"', '"published_at": "2026-11-16T08:09:00+07:00"'),
        ('"published_at": "2026-11-16T09:00:00+07:00"', '"published_at": "2026-11-16T08:12:00+07:00"'),
    ],
    'scripts/generate_batch13_part2.py': [
        ('"published_at": "2026-11-16T09:15:00+07:00"', '"published_at": "2026-11-16T08:15:00+07:00"'),
        ('"published_at": "2026-11-16T09:30:00+07:00"', '"published_at": "2026-11-16T08:18:00+07:00"'),
        ('"published_at": "2026-11-16T09:45:00+07:00"', '"published_at": "2026-11-16T08:21:00+07:00"'),
        ('"published_at": "2026-11-16T10:00:00+07:00"', '"published_at": "2026-11-16T08:24:00+07:00"'),
        ('"published_at": "2026-11-16T10:15:00+07:00"', '"published_at": "2026-11-16T08:27:00+07:00"'),
    ],
    'scripts/generate_batch13_part3.py': [
        ('"published_at": "2026-11-16T10:30:00+07:00"', '"published_at": "2026-11-16T08:30:00+07:00"'),
        ('"published_at": "2026-11-16T10:45:00+07:00"', '"published_at": "2026-11-16T08:33:00+07:00"'),
        ('"published_at": "2026-11-16T11:00:00+07:00"', '"published_at": "2026-11-16T08:36:00+07:00"'),
        ('"published_at": "2026-11-16T11:15:00+07:00"', '"published_at": "2026-11-16T08:39:00+07:00"'),
        ('"published_at": "2026-11-16T11:30:00+07:00"', '"published_at": "2026-11-16T08:42:00+07:00"'),
    ],
    'scripts/generate_batch13_part4.py': [
        ('"published_at": "2026-11-16T11:45:00+07:00"', '"published_at": "2026-11-16T08:45:00+07:00"'),
        ('"published_at": "2026-11-16T12:00:00+07:00"', '"published_at": "2026-11-16T08:48:00+07:00"'),
        ('"published_at": "2026-11-16T12:15:00+07:00"', '"published_at": "2026-11-16T08:51:00+07:00"'),
        ('"published_at": "2026-11-16T12:30:00+07:00"', '"published_at": "2026-11-16T08:54:00+07:00"'),
        ('"published_at": "2026-11-16T12:45:00+07:00"', '"published_at": "2026-11-16T08:57:00+07:00"'),
    ],
}
for fpath, pairs in updates.items():
    with open(fpath, 'r', encoding='utf-8') as f:
        content = f.read()
    for old, new in pairs:
        assert old in content, f'Missing {old} in {fpath}'
        content = content.replace(old, new)
    with open(fpath, 'w', encoding='utf-8') as f:
        f.write(content)
print('All updated successfully')
