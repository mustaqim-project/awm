import os, re

target_files = [
    ('resources/views/admin/subscriptions/show.blade.php', [36, 50, 51, 107]),
    ('resources/views/admin/templates/create.blade.php', [23, 61, 66, 78, 102, 146]),
    ('resources/views/admin/businesses/index.blade.php', [362, 404]),
    ('resources/views/admin/word_filters/index.blade.php', [292, 412]),
    ('resources/views/app/ai-studio/create.blade.php', [41, 85, 249, 263, 277, 316, 352, 449, 459, 469]),
    ('resources/views/app/ai-studio/index.blade.php', [25, 70, 114, 153, 176]),
    ('resources/views/app/ai-studio/credit-history.blade.php', [33, 44, 81, 90]),
]

for fp, lines in target_files:
    print(f"=== {fp} ===")
    with open(fp, 'r', encoding='utf-8') as f:
        file_lines = f.readlines()
    for lno in lines:
        if lno <= len(file_lines):
            print(f"  Line {lno}: {file_lines[lno-1].strip()}")
